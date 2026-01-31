<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentQRService;
use App\Services\WompiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected $wompiService;
    protected $qrService;
    
    public function __construct(WompiService $wompiService, PaymentQRService $qrService)
    {
        $this->wompiService = $wompiService;
        $this->qrService = $qrService;
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
     * Procesar formulario de inscripción - NUEVO CON QR
     */
    public function process(Request $request)
    {
        // Validación completa
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:15',
            'pais' => 'required|string|size:2',
            'profesion' => 'required|string|max:255',
            'expectations' => 'required|string|min:10|max:1000',
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
            
            // Crear registro de inscripción (sin referencia aún)
            $payment = Payment::create([
                'reference' => 'TEMP_' . time() . '_' . Str::random(4),
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
                'payment_method' => 'QR',
                'observations' => 'Formulario completado - Pendiente de pago QR',
            ]);
            
            Log::info('Inscripción procesada para QR', [
                'payment_id' => $payment->id,
                'customer' => $request->email,
                'country' => $request->pais,
            ]);
            
            // Redirigir a la página de pago con QR
            return redirect()->route('payment.qr', $payment->id);
            
        } catch (\Exception $e) {
            Log::error('Error procesando inscripción: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Mostrar página de pago con QR
     */
    public function showQR($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            // Si el pago ya está aprobado, redirigir a éxito
            if ($payment->isApproved()) {
                return redirect()->route('payment.success', $payment->reference);
            }
            
            // Si el pago está fallido, mostrar error
            if ($payment->isFailed()) {
                return redirect()->route('payment.error', $payment->reference);
            }
            
            // Generar URL del QR
            $qrUrl = $this->qrService->processPayment($payment);
            
            return view('payment-qr', compact('payment', 'qrUrl'));
            
        } catch (\Exception $e) {
            Log::error('Error mostrando QR: ' . $e->getMessage());
            return redirect('/')->with('error', 'Error al generar el código QR');
        }
    }
    
    /**
     * Webhook para recibir notificaciones de Wompi QR
     */
    public function webhook(Request $request)
    {
        Log::info('=== WOMPI WEBHOOK QR RECEIVED ===');
        Log::info('Headers:', $request->headers->all());
        Log::info('Payload:', $request->all());
        
        try {
            // Procesar el webhook con el servicio QR
            $success = $this->qrService->processWebhook($request->all());
            
            if ($success) {
                Log::info('=== WOMPI WEBHOOK QR PROCESSED SUCCESSFULLY ===');
                return response()->json(['success' => true]);
            } else {
                Log::warning('=== WOMPI WEBHOOK QR PROCESSING FAILED ===');
                return response()->json(['error' => 'Processing failed'], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Página de verificación después del pago QR
     */
    public function verifyQR($reference)
    {
        try {
            // Verificar estado del pago
            $result = $this->qrService->verifyPayment($reference);
            
            if ($result['success'] && isset($result['payment'])) {
                $payment = $result['payment'];
                
                if ($payment->isApproved()) {
                    return view('success', compact('payment'));
                }
            }
            
            // Si no está aprobado, mostrar página de espera o error
            $payment = Payment::where('reference', $reference)->first();
            
            if ($payment && $payment->isFailed()) {
                return view('error', compact('payment'));
            }
            
            // Si sigue pendiente, mostrar página de espera
            return view('payment-pending', compact('payment'));
            
        } catch (\Exception $e) {
            Log::error('Error verificando pago QR: ' . $e->getMessage());
            return redirect('/')->with('error', 'Error verificando el pago');
        }
    }
    
    /**
     * Página de éxito
     */
    public function success($reference)
    {
        $payment = Payment::where('reference', $reference)->first();
        
        if (!$payment) {
            return redirect('/')->with('error', 'Pago no encontrado');
        }
        
        if (!$payment->isApproved()) {
            return redirect()->route('payment.error', $reference);
        }
        
        return view('success', compact('payment'));
    }
    
    /**
     * Página de error
     */
    public function error($reference)
    {
        $payment = Payment::where('reference', $reference)->first();
        
        if (!$payment) {
            return redirect('/')->with('error', 'Pago no encontrado');
        }
        
        return view('error', compact('payment'));
    }
    
    /**
     * Verificar estado de pago (AJAX)
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
            'is_failed' => $payment->isFailed(),
            'is_pending' => $payment->isPending(),
        ]);
    }
    
    /**
     * Página de pago pendiente (para refresco automático)
     */
    public function pending($reference)
    {
        $payment = Payment::where('reference', $reference)->first();
        
        if (!$payment) {
            return redirect('/')->with('error', 'Pago no encontrado');
        }
        
        return view('payment-pending', compact('payment'));
    }
}