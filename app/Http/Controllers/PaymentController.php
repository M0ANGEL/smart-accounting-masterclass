<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmation;
use App\Mail\PaymentError;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // Lista de países soportados
    private $countries = [
        'CO' => ['name' => 'Colombia', 'code' => '+57'],
        'EC' => ['name' => 'Ecuador', 'code' => '+593'],
        'PE' => ['name' => 'Perú', 'code' => '+51'],
        'MX' => ['name' => 'México', 'code' => '+52'],
        'CL' => ['name' => 'Chile', 'code' => '+56'],
    ];

    /**
     * Página principal del curso
     */
    public function index()
    {
        return view('course', ['countries' => $this->countries]);
    }

    /**
     * Procesar formulario de inscripción
     */
    public function process(Request $request)
    {
        Log::info('=== INICIANDO PROCESO DE INSCRIPCIÓN ===');
        Log::info('Datos recibidos:', $request->except(['_token', 'terminos']));

        // Validación
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'telefono' => 'required|string|max:15',
                'pais' => 'required|string|size:2|in:CO,EC,PE,MX,CL',
                'profesion' => 'required|string|max:100',
                'expectations' => 'required|string|min:10|max:500',
                'terminos' => 'required|accepted',
            ]);

            Log::info('✅ Formulario validado correctamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Error de validación:', $e->errors());
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Por favor corrige los errores en el formulario.');
        }

        try {
            Log::info('🔄 Creando registro en la base de datos...');
            
            // Crear registro de pago (la referencia se genera automáticamente en el modelo)
            $payment = Payment::create([
                'customer_name' => $request->nombre,
                'customer_email' => $request->email,
                'customer_phone' => $request->telefono,
                'country' => $request->pais,
                'profession' => $request->profesion,
                'expectations' => $request->expectations,
                'amount' => 15000000, // 150,000 COP en centavos
                'currency' => 'COP',
                'status' => 'pending',
                'email_sent' => false,
                'observations' => 'Inscripción creada - Pendiente de pago',
            ]);

            Log::info('✅ Registro creado exitosamente', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'email' => $payment->customer_email,
            ]);

            // Redirigir a la página de pago con QR
            Log::info('🔄 Redirigiendo a página de QR...');
            return redirect()->route('payment.qr', $payment->id);

        } catch (\Exception $e) {
            Log::error('❌ Error al procesar inscripción:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->with('error', 'Error al procesar la solicitud. Por favor, intenta nuevamente. ' . 
                       (config('app.debug') ? $e->getMessage() : ''))
                ->withInput();
        }
    }

    /**
     * Página de pago con QR
     */
    public function showQR($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            Log::info('Mostrando QR para pago', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'status' => $payment->status,
            ]);
            
            // Si ya está aprobado, redirigir a éxito
            if ($payment->isApproved()) {
                Log::info('Pago ya aprobado, redirigiendo a éxito');
                return redirect()->route('payment.success', $payment->reference);
            }
            
            // Si falló, redirigir a error
            if ($payment->isFailed()) {
                Log::info('Pago fallido, redirigiendo a error');
                return redirect()->route('payment.error', $payment->reference);
            }

            // Generar enlace de pago para Wompi
            $paymentUrl = $this->generateWompiPaymentLink($payment);
            
            Log::info('URL de pago generada', [
                'url_length' => strlen($paymentUrl),
                'reference' => $payment->reference,
            ]);
            
            return view('payment-qr', compact('payment', 'paymentUrl'));
            
        } catch (\Exception $e) {
            Log::error('Error mostrando QR: ' . $e->getMessage());
            return redirect('/')
                ->with('error', 'No se pudo cargar la página de pago. ' . $e->getMessage());
        }
    }

    /**
     * Generar enlace de pago para Wompi
     */
    private function generateWompiPaymentLink(Payment $payment)
    {
        $baseUrl = env('WOMPI_QR_LINK', 'https://checkout.wompi.co/l/etHnm3');
        
        // Parámetros para Wompi
        $params = [
            'public-key' => env('WOMPI_PUBLIC_KEY', 'pub_prod_5r4Bl04to4qWHV3dRsaITn4Oz45ocbR7'),
            'currency' => 'COP',
            'amount-in-cents' => $payment->amount,
            'reference' => $payment->reference,
            'redirect-url' => route('payment.webhook-redirect', $payment->reference),
            'customer-data' => json_encode([
                'email' => $payment->customer_email,
                'full-name' => $payment->customer_name,
                'phone-number' => $payment->customer_phone,
            ]),
        ];

        // Construir URL
        $url = $baseUrl . '?' . http_build_query($params);
        
        Log::info('URL generada para Wompi', [
            'base_url' => $baseUrl,
            'params' => $params,
            'final_url_length' => strlen($url),
        ]);
        
        return $url;
    }

    /**
     * Verificar estado del pago (para AJAX)
     */
    public function checkStatus($reference)
    {
        try {
            Log::info('Verificando estado del pago', ['reference' => $reference]);
            
            $payment = Payment::where('reference', $reference)->first();
            
            if (!$payment) {
                Log::warning('Pago no encontrado', ['reference' => $reference]);
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado',
                ], 404);
            }
            
            $response = [
                'success' => true,
                'is_approved' => $payment->isApproved(),
                'is_failed' => $payment->isFailed(),
                'is_pending' => $payment->isPending(),
                'status' => $payment->status,
                'message' => $this->getStatusMessage($payment->status),
                'minutes_elapsed' => $payment->created_at->diffInMinutes(now()),
                'reference' => $payment->reference,
            ];
            
            Log::info('Estado del pago verificado', $response);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error verificando estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el estado del pago',
            ], 500);
        }
    }

    /**
     * Mensaje según estado
     */
    private function getStatusMessage($status)
    {
        $messages = [
            'pending' => 'Pago pendiente de confirmación',
            'approved' => '¡Pago confirmado!',
            'declined' => 'Pago rechazado',
            'failed' => 'Pago fallido',
            'error' => 'Error en el pago',
            'voided' => 'Pago anulado',
            'expired' => 'Pago expirado',
        ];
        
        return $messages[$status] ?? 'Estado desconocido';
    }

    /**
     * Página de éxito
     */
    public function success($reference)
    {
        try {
            $payment = Payment::where('reference', $reference)->firstOrFail();
            
            Log::info('Mostrando página de éxito', [
                'reference' => $reference,
                'status' => $payment->status,
                'email_sent' => $payment->email_sent,
            ]);
            
            // Si no está aprobado, redirigir al QR
            if (!$payment->isApproved()) {
                Log::info('Pago no aprobado, redirigiendo a QR');
                return redirect()->route('payment.qr', $payment->id);
            }
            
            // Enviar email si no se ha enviado
            if (!$payment->email_sent) {
                try {
                    Log::info('Enviando email de confirmación...');
                    
                    $courseData = [
                        'name' => 'Masterclass Auditoría Analítica y Power BI',
                        'start_date' => '7 de Febrero 2024',
                        'support_email' => 'soporte@smartaccounting.com',
                    ];
                    
                    Mail::to($payment->customer_email)
                        ->send(new PaymentConfirmation($payment, $courseData));
                    
                    $payment->update(['email_sent' => true]);
                    
                    Log::info('✅ Email de confirmación enviado', [
                        'email' => $payment->customer_email,
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('❌ Error enviando email: ' . $e->getMessage());
                    // Continuamos aunque falle el email
                }
            }
            
            return view('success', compact('payment'));
            
        } catch (\Exception $e) {
            Log::error('Error en página de éxito: ' . $e->getMessage());
            return redirect('/')->with('error', 'No se pudo cargar la página de éxito.');
        }
    }

    /**
     * Página de error
     */
    public function error($reference)
    {
        try {
            $payment = Payment::where('reference', $reference)->firstOrFail();
            
            Log::info('Mostrando página de error', [
                'reference' => $reference,
                'status' => $payment->status,
            ]);
            
            // Si está aprobado, redirigir a éxito
            if ($payment->isApproved()) {
                Log::info('Pago aprobado, redirigiendo a éxito');
                return redirect()->route('payment.success', $payment->reference);
            }
            
            // Enviar email de error si es un estado fallido
            if ($payment->isFailed() && !$payment->email_sent) {
                try {
                    Mail::to($payment->customer_email)
                        ->send(new PaymentError($payment));
                    
                    $payment->update(['email_sent' => true]);
                    
                    Log::info('Email de error enviado', [
                        'email' => $payment->customer_email,
                        'status' => $payment->status,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error enviando email de error: ' . $e->getMessage());
                }
            }
            
            return view('error', compact('payment'));
            
        } catch (\Exception $e) {
            Log::error('Error en página de error: ' . $e->getMessage());
            return redirect('/')->with('error', 'No se pudo cargar la página de error.');
        }
    }

    /**
     * Redirección después del pago (cuando Wompi redirige)
     */
    public function webhookRedirect($reference)
    {
        Log::info('Redirección desde Wompi', ['reference' => $reference]);
        
        try {
            $payment = Payment::where('reference', $reference)->firstOrFail();
            
            // Redirigir según estado
            if ($payment->isApproved()) {
                return redirect()->route('payment.success', $reference);
            } elseif ($payment->isFailed()) {
                return redirect()->route('payment.error', $reference);
            } else {
                // Si sigue pendiente, mostrar página de espera
                return redirect()->route('payment.pending', $reference);
            }
            
        } catch (\Exception $e) {
            Log::error('Error en redirección: ' . $e->getMessage());
            return redirect('/')->with('error', 'No se pudo procesar la redirección.');
        }
    }

    /**
     * Página de pago pendiente
     */
    public function pending($reference)
    {
        try {
            $payment = Payment::where('reference', $reference)->firstOrFail();
            
            Log::info('Mostrando página de espera', [
                'reference' => $reference,
                'status' => $payment->status,
            ]);
            
            // Verificar si ya cambió el estado
            if ($payment->isApproved()) {
                return redirect()->route('payment.success', $reference);
            } elseif ($payment->isFailed()) {
                return redirect()->route('payment.error', $reference);
            }
            
            return view('payment-pending', compact('payment'));
            
        } catch (\Exception $e) {
            Log::error('Error en página pendiente: ' . $e->getMessage());
            return redirect('/')->with('error', 'No se pudo cargar la página de espera.');
        }
    }

    /**
     * Webhook para recibir notificaciones de Wompi
     */
    public function webhook(Request $request)
    {
        Log::info('=== WEBHOOK WOMPI RECIBIDO ===');
        Log::info('Headers:', $request->headers->all());
        Log::info('Body:', $request->all());

        try {
            $data = $request->json()->all();
            
            // Verificar estructura básica
            if (!isset($data['event']) || $data['event'] !== 'transaction.updated') {
                Log::info('Webhook ignorado - evento no relevante', ['event' => $data['event'] ?? 'none']);
                return response()->json(['status' => 'ignored'], 200);
            }

            if (!isset($data['data']['transaction'])) {
                Log::warning('Webhook ignorado - sin transacción', $data);
                return response()->json(['error' => 'Invalid data'], 400);
            }

            $transaction = $data['data']['transaction'];
            $reference = $transaction['reference'] ?? null;

            if (!$reference) {
                Log::warning('Webhook sin referencia', $transaction);
                return response()->json(['error' => 'No reference'], 400);
            }

            Log::info('Procesando webhook para referencia:', ['reference' => $reference]);

            // Buscar el pago
            $payment = Payment::where('reference', $reference)->first();

            if (!$payment) {
                Log::warning('Pago no encontrado para webhook', ['reference' => $reference]);
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Guardar estado anterior
            $oldStatus = $payment->status;
            $newStatus = strtolower($transaction['status'] ?? 'pending');
            
            // Actualizar pago
            $payment->update([
                'status' => $newStatus,
                'wompi_id' => $transaction['id'] ?? null,
                'payment_method' => $transaction['payment_method_type'] ?? null,
                'payment_method_type' => $transaction['payment_method']['type'] ?? null,
                'wompi_response' => $transaction,
                'observations' => "Actualizado por webhook: {$newStatus}",
            ]);

            Log::info('✅ Pago actualizado por webhook', [
                'reference' => $reference,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'transaction_id' => $transaction['id'] ?? null,
            ]);

            // Enviar emails según el nuevo estado
            $this->handlePaymentStatusChange($payment, $oldStatus, $newStatus);

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error procesando webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Manejar cambio de estado del pago
     */
    private function handlePaymentStatusChange(Payment $payment, string $oldStatus, string $newStatus): void
    {
        try {
            Log::info('Manejando cambio de estado', [
                'reference' => $payment->reference,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // Solo procesar si el estado cambió
            if ($oldStatus === $newStatus) {
                Log::info('Estado sin cambios, ignorando');
                return;
            }

            // Estados de éxito
            if ($newStatus === 'approved') {
                Log::info('Enviando email de confirmación para pago aprobado');
                
                $courseData = [
                    'name' => 'Masterclass Auditoría Analítica y Power BI',
                    'start_date' => '7 de Febrero 2024',
                    'support_email' => 'soporte@smartaccounting.com',
                ];
                
                Mail::to($payment->customer_email)
                    ->send(new PaymentConfirmation($payment, $courseData));
                
                $payment->update(['email_sent' => true]);
                
                Log::info('✅ Email de confirmación enviado', [
                    'email' => $payment->customer_email,
                ]);
            }
            
            // Estados de error
            elseif (in_array($newStatus, ['declined', 'failed', 'error', 'voided'])) {
                Log::info('Enviando email de error', [
                    'status' => $newStatus,
                ]);
                
                Mail::to($payment->customer_email)
                    ->send(new PaymentError($payment));
                
                $payment->update(['email_sent' => true]);
                
                Log::info('✅ Email de error enviado', [
                    'email' => $payment->customer_email,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Error manejando cambio de estado: ' . $e->getMessage());
        }
    }

    /**
     * Verificar pagos expirados (para comando)
     */
    public function checkExpiredPayments()
    {
        try {
            Log::info('Verificando pagos expirados...');
            
            $expiredPayments = Payment::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
            
            Log::info('Pagos expirados encontrados: ' . $expiredPayments->count());
            
            foreach ($expiredPayments as $payment) {
                Log::info('Marcando pago como expirado', [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'created_at' => $payment->created_at,
                ]);
                
                // Marcar como expirado
                $payment->update([
                    'status' => 'expired',
                    'observations' => 'Expirado automáticamente - Sin pago en 24 horas',
                ]);
                
                // Enviar email de expiración
                try {
                    Mail::to($payment->customer_email)
                        ->send(new PaymentError($payment));
                    
                    $payment->update(['email_sent' => true]);
                    
                    Log::info('Email de expiración enviado', [
                        'email' => $payment->customer_email,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error enviando email de expiración: ' . $e->getMessage());
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error verificando pagos expirados: ' . $e->getMessage());
            return false;
        }
    }
}