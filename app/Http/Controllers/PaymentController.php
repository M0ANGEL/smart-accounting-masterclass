<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\WompiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected $wompiService;
    
    public function __construct(WompiService $wompiService)
    {
        $this->wompiService = $wompiService;
    }
    
    /**
     * Página principal del curso
     */
    public function index()
    {
        $countries = $this->wompiService->getSupportedCountries();
        return view('course', compact('countries'));
    }
    
    /**
     * Procesar formulario de inscripción
     */
    public function process(Request $request)
    {
        // Validación completa según requerimientos Wompi
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:15',
            'pais' => 'required|string|size:2',
            'profesion' => 'required|string|max:255',
            'expectations' => 'required|string|min:10|max:1000',
            'payment_method' => 'required|in:CARD,PSE',
            'terminos' => 'required|accepted',
        ], [
            'nombre.required' => 'El nombre completo es requerido',
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'Ingresa un correo electrónico válido',
            'telefono.required' => 'El número de teléfono es requerido',
            'pais.required' => 'El país es requerido',
            'profesion.required' => 'La profesión u oficio es requerida',
            'expectations.required' => 'Por favor comparte tus expectativas del curso',
            'terminos.accepted' => 'Debes aceptar los términos y condiciones',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Por favor corrige los errores en el formulario');
        }
        
        try {
            // Obtener información del país seleccionado
            $countries = $this->wompiService->getSupportedCountries();
            $selectedCountry = $countries[$request->pais] ?? $countries['CO'];
            
            // Crear referencia única para Wompi
            $reference = 'MC_' . Str::upper(Str::random(8)) . '_' . time();
            
            // Crear registro de pago
            $payment = Payment::create([
                'reference' => $reference,
                'customer_name' => $request->nombre,
                'customer_email' => $request->email,
                'customer_phone' => preg_replace('/[^0-9]/', '', $request->telefono),
                'country_code' => $selectedCountry['code'],
                'country' => $request->pais,
                'profession' => $request->profesion,
                'expectations' => $request->expectations,
                'amount' => config('wompi.amounts.masterclass'),
                'currency' => config('wompi.currency'),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'observations' => 'Registro creado - Pendiente de pago',
            ]);
            
            Log::info('Payment process iniciado', [
                'reference' => $reference,
                'customer' => $request->email,
                'country' => $request->pais,
            ]);
            
            // Obtener acceptance token (REQUERIDO por Wompi)
            $acceptanceToken = $this->wompiService->getAcceptanceToken();
            
            // Generar firma de integridad (REQUERIDO por Wompi Widget)
            $signatureIntegrity = $this->wompiService->generateSignatureIntegrity(
                $reference,
                $payment->amount,
                $payment->currency,
                $payment->customer_email
            );
            
            // Formatear datos del cliente para Wompi
            $customerData = $this->wompiService->formatCustomerData($payment);
            
            // Redirigir a la página de confirmación
            return view('confirmacion', compact('payment', 'acceptanceToken', 'signatureIntegrity', 'customerData'));
            
        } catch (\Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Procesar pago con tarjeta (AJAX)
     */
    public function processCardPayment($reference, Request $request)
    {
        try {
            Log::info('Processing card payment', ['reference' => $reference]);
            
            // Validar token
            $validator = Validator::make($request->all(), [
                'token' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Token de tarjeta inválido'
                ], 400);
            }
            
            // Buscar pago
            $payment = Payment::where('reference', $reference)->firstOrFail();
            
            // Datos para la transacción
            $transactionData = [
                'acceptance_token' => $this->wompiService->getAcceptanceToken(),
                'amount_in_cents' => (int) $payment->amount,
                'currency' => $payment->currency,
                'customer_email' => $payment->customer_email,
                'payment_method' => [
                    'type' => 'CARD',
                    'token' => $request->token,
                    'installments' => 1
                ],
                'reference' => $payment->reference,
                'customer_data' => $this->wompiService->formatCustomerData($payment),
                'signature' => $this->wompiService->generateSignatureIntegrity(
                    $payment->reference,
                    $payment->amount,
                    $payment->currency,
                    $payment->customer_email
                )
            ];
            
            // Crear transacción en Wompi
            $result = $this->wompiService->createTransaction($transactionData);
            
            // Verificar resultado
            if (isset($result['data']) && $result['data']['id']) {
                $transaction = $result['data'];
                
                // Actualizar pago
                $payment->update([
                    'status' => strtolower($transaction['status']),
                    'wompi_id' => $transaction['id'],
                    'payment_method_type' => 'CARD',
                    'wompi_response' => $transaction,
                    'observations' => 'Pago con tarjeta procesado'
                ]);
                
                // Retornar URL de redirección
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('payment.return', ['id' => $transaction['id']])
                ]);
            }
            
            throw new \Exception('No se pudo procesar la transacción');
            
        } catch (\Exception $e) {
            Log::error('Error in processCardPayment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Webhook para recibir notificaciones de Wompi
     */
    public function webhook(Request $request)
    {
        Log::info('=== WOMPI WEBHOOK RECEIVED ===');
        Log::info('Headers:', $request->headers->all());
        Log::info('Payload:', $request->all());
        
        try {
            // En producción, validar firma del webhook
            // $signature = $request->header('X-Signature');
            // $payload = $request->getContent();
            
            // if (!$this->wompiService->validateWebhookSignature($signature, $payload)) {
            //     Log::warning('Invalid webhook signature');
            //     return response()->json(['error' => 'Invalid signature'], 400);
            // }
            
            $event = $request->input('event');
            $data = $request->input('data');
            
            // Solo procesar eventos de transacción
            if ($event === 'transaction.updated' && isset($data['transaction'])) {
                $transaction = $data['transaction'];
                $reference = $transaction['reference'];
                
                Log::info('Processing transaction update', [
                    'reference' => $reference,
                    'status' => $transaction['status'],
                    'id' => $transaction['id'],
                ]);
                
                // Buscar pago por referencia
                $payment = Payment::where('reference', $reference)->first();
                
                if ($payment) {
                    $oldStatus = $payment->status;
                    $newStatus = strtolower($transaction['status']);
                    
                    $updateData = [
                        'status' => $newStatus,
                        'wompi_id' => $transaction['id'],
                        'payment_method' => $transaction['payment_method_type'] ?? null,
                        'payment_method_type' => $transaction['payment_method']['type'] ?? null,
                        'wompi_response' => $transaction,
                        'observations' => "Actualizado por webhook: {$newStatus}",
                    ];
                    
                    $payment->update($updateData);
                    
                    Log::info("Payment {$reference} updated", [
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                    
                    // Enviar email si el pago es aprobado
                    if ($newStatus === 'approved') {
                        $this->sendConfirmationEmail($payment);
                        Log::info("Confirmation email sent for: {$payment->customer_email}");
                    }
                    
                } else {
                    Log::warning("Payment not found for reference: {$reference}");
                    
                    // Crear registro si no existe (solo para debugging)
                    if (config('app.debug')) {
                        Payment::create([
                            'reference' => $reference,
                            'customer_name' => 'Cliente Webhook',
                            'customer_email' => 'webhook@email.com',
                            'customer_phone' => '3001234567',
                            'country_code' => '+57',
                            'country' => 'CO',
                            'amount' => $transaction['amount_in_cents'],
                            'currency' => $transaction['currency'],
                            'status' => strtolower($transaction['status']),
                            'wompi_id' => $transaction['id'],
                            'wompi_response' => $transaction,
                            'observations' => 'Creado desde webhook',
                        ]);
                    }
                }
            }
            
            Log::info('=== WOMPI WEBHOOK PROCESSED SUCCESSFULLY ===');
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Página de retorno después del pago
     */
    public function return(Request $request)
    {
        Log::info('Payment return callback', $request->all());
        
        $transactionId = $request->query('id');
        $reference = $request->query('reference');
        $status = $request->query('status');
        
        try {
            // Si tenemos ID de transacción, obtener datos de Wompi
            if ($transactionId) {
                $transactionData = $this->wompiService->getTransaction($transactionId);
                
                if (isset($transactionData['data'])) {
                    $transaction = $transactionData['data'];
                    $reference = $transaction['reference'];
                    $status = strtolower($transaction['status']);
                    
                    // Buscar o actualizar pago
                    $payment = Payment::where('reference', $reference)->first();
                    
                    if ($payment) {
                        $payment->update([
                            'status' => $status,
                            'wompi_id' => $transactionId,
                            'payment_method' => $transaction['payment_method_type'] ?? null,
                            'payment_method_type' => $transaction['payment_method']['type'] ?? null,
                            'wompi_response' => $transaction,
                            'observations' => 'Actualizado desde retorno',
                        ]);
                        
                        // Enviar email si es aprobado
                        if ($status === 'approved') {
                            $this->sendConfirmationEmail($payment);
                        }
                        
                        // Mostrar vista según estado
                        if ($status === 'approved') {
                            return view('success', compact('payment'));
                        } else {
                            return view('error', compact('payment'));
                        }
                    }
                }
            }
            
            // Si tenemos referencia, buscar pago
            if ($reference) {
                $payment = Payment::where('reference', $reference)->first();
                
                if ($payment) {
                    if ($payment->isApproved()) {
                        return view('success', compact('payment'));
                    } else {
                        return view('error', compact('payment'));
                    }
                }
            }
            
            // Si no se encontró información suficiente
            return redirect('/')->with('error', 'No se pudo verificar el estado del pago.');
            
        } catch (\Exception $e) {
            Log::error('Error in payment return: ' . $e->getMessage());
            return redirect('/')->with('error', 'Error al verificar el pago.');
        }
    }
    
    /**
     * Página de éxito (fallback)
     */
    public function success()
    {
        return view('success');
    }
    
    /**
     * Página de error (fallback)
     */
    public function error()
    {
        return view('error');
    }
    
    /**
     * Verificar estado de pago (para AJAX)
     */
    public function checkStatus($reference)
    {
        $payment = Payment::where('reference', $reference)->first();
        
        if (!$payment) {
            return response()->json([
                'error' => true,
                'message' => 'Pago no encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'formatted_amount' => $payment->formatted_amount,
            'reference' => $payment->reference,
            'customer_name' => $payment->customer_name,
            'is_approved' => $payment->isApproved(),
        ]);
    }
    
    /**
     * Probar conexión con Wompi (solo desarrollo)
     */
    public function testConnection()
    {
        if (!app()->environment('local')) {
            abort(404);
        }
        
        $result = $this->wompiService->testConnection();
        return response()->json($result);
    }
    
    /**
     * Enviar email de confirmación
     */
    private function sendConfirmationEmail(Payment $payment)
    {
        try {
            // Datos para el email
            $emailData = [
                'customer_name' => $payment->customer_name,
                'reference' => $payment->reference,
                'amount' => $payment->formatted_amount,
                'course_name' => 'Masterclass Auditoría Analítica y Power BI',
                'start_date' => '7 de Febrero 2024',
                'support_email' => 'soporte@smartaccounting.com',
            ];
            
            // Aquí implementarías el envío real de email
            Log::info("Email de confirmación listo para: {$payment->customer_email}", $emailData);
            
            // Ejemplo con Mail facade (descomentar cuando configures email):
            // Mail::to($payment->customer_email)->send(new PaymentConfirmation($emailData));
            
        } catch (\Exception $e) {
            Log::error("Error enviando email: " . $e->getMessage());
        }
    }
}