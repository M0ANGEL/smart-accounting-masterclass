<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmation;
use App\Mail\PaymentError;
use Illuminate\Support\Str;

class PaymentQRService
{
    protected $checkoutLink;
    
    public function __construct()
    {
        $this->checkoutLink = config('wompi.checkout_link');
    }
    
    /**
     * Generar link de pago para Wompi QR
     */
    public function generatePaymentLink(Payment $payment): string
    {
        try {
            // Generar referencia única
            $reference = $this->generateReference($payment);
            
            // Actualizar referencia en el pago
            $payment->update([
                'reference' => $reference,
                'observations' => 'Link de pago generado - Esperando confirmación',
            ]);
            
            Log::info('Link de pago generado', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'email' => $payment->customer_email,
            ]);
            
            // Generar link de checkout de Wompi
            return $this->generateWompiCheckoutLink($reference, $payment);
            
        } catch (\Exception $e) {
            Log::error('Error generando link de pago: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generar referencia única para Wompi
     */
    private function generateReference(Payment $payment): string
    {
        // Formato recomendado por Wompi: alfanumérico, único, sin caracteres especiales
        $timestamp = time();
        $random = Str::upper(Str::random(8));
        $emailPrefix = substr(str_replace(['@', '.', '-'], '', $payment->customer_email), 0, 4);
        
        return "MC_{$timestamp}_{$random}_{$emailPrefix}";
    }
    
    /**
     * Generar link de checkout de Wompi con los parámetros correctos
     */
    private function generateWompiCheckoutLink(string $reference, Payment $payment): string
    {
        $baseUrl = rtrim($this->checkoutLink, '/');
        
        // Parámetros para Wompi Checkout Link
        $params = [
            'public-key' => config('wompi.keys.public'),
            'currency' => 'COP',
            'amount-in-cents' => $payment->amount,
            'reference' => $reference,
            'redirect-url' => route('payment.verify', $reference),
            'customer-data' => json_encode([
                'email' => $payment->customer_email,
                'full-name' => $payment->customer_name,
                'phone-number' => $payment->customer_phone,
            ]),
        ];
        
        // Construir URL con parámetros
        $queryString = http_build_query($params);
        
        return "{$baseUrl}?{$queryString}";
    }
    
    /**
     * Verificar estado del pago
     */
    public function verifyPayment(string $reference): array
    {
        try {
            $payment = Payment::where('reference', $reference)->first();
            
            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pago no encontrado',
                ];
            }
            
            // Si el pago ya está aprobado, retornar éxito
            if ($payment->isApproved()) {
                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Pago confirmado',
                ];
            }
            
            // Si el pago está fallido
            if ($payment->isFailed()) {
                return [
                    'success' => false,
                    'payment' => $payment,
                    'message' => 'Pago fallido o rechazado',
                ];
            }
            
            // Si sigue pendiente
            return [
                'success' => false,
                'payment' => $payment,
                'pending' => true,
                'message' => 'Pago pendiente de confirmación',
            ];
            
        } catch (\Exception $e) {
            Log::error('Error verificando pago: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Procesar webhook de Wompi
     */
    public function processWebhook(array $data): bool
    {
        try {
            Log::info('=== PROCESANDO WEBHOOK WOMPI ===', $data);
            
            // Verificar estructura del webhook
            if (!isset($data['event']) || !isset($data['data'])) {
                Log::warning('Estructura de webhook inválida', $data);
                return false;
            }
            
            $event = $data['event'];
            $transaction = $data['data']['transaction'] ?? null;
            
            if ($event !== 'transaction.updated' || !$transaction) {
                Log::info('Webhook no relevante', ['event' => $event]);
                return false;
            }
            
            $reference = $transaction['reference'] ?? null;
            $status = strtolower($transaction['status'] ?? 'pending');
            
            if (!$reference) {
                Log::warning('Webhook sin referencia', $transaction);
                return false;
            }
            
            // Buscar pago por referencia
            $payment = Payment::where('reference', $reference)->first();
            
            if (!$payment) {
                Log::warning('Pago no encontrado en webhook', ['reference' => $reference]);
                return false;
            }
            
            // Guardar estado anterior
            $oldStatus = $payment->status;
            
            // Actualizar pago
            $payment->update([
                'status' => $status,
                'wompi_id' => $transaction['id'] ?? null,
                'payment_method' => $transaction['payment_method_type'] ?? null,
                'payment_method_type' => $transaction['payment_method']['type'] ?? null,
                'wompi_response' => $transaction,
                'observations' => "Actualizado por webhook: {$status}",
            ]);
            
            Log::info('Pago actualizado por webhook', [
                'reference' => $reference,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'transaction_id' => $transaction['id'] ?? null,
            ]);
            
            // Enviar email según estado
            $this->handlePaymentStatusChange($payment, $oldStatus, $status);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error procesando webhook: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    
    /**
     * Manejar cambio de estado del pago
     */
    private function handlePaymentStatusChange(Payment $payment, string $oldStatus, string $newStatus): void
    {
        try {
            // Solo procesar si el estado cambió
            if ($oldStatus === $newStatus) {
                return;
            }
            
            // Estados de éxito
            if ($newStatus === 'approved') {
                $this->sendConfirmationEmail($payment);
                Log::info('Email de confirmación enviado', [
                    'email' => $payment->customer_email,
                    'reference' => $payment->reference,
                ]);
            }
            
            // Estados de error
            elseif (in_array($newStatus, ['declined', 'failed', 'error', 'voided'])) {
                $this->sendErrorEmail($payment, $newStatus);
                Log::info('Email de error enviado', [
                    'email' => $payment->customer_email,
                    'status' => $newStatus,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error manejando cambio de estado: ' . $e->getMessage());
        }
    }
    
    /**
     * Enviar email de confirmación
     */
    public function sendConfirmationEmail(Payment $payment): void
    {
        try {
            Mail::to($payment->customer_email)->send(new PaymentConfirmation($payment));
            Log::info('✅ Email de confirmación enviado exitosamente', [
                'to' => $payment->customer_email,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error enviando email de confirmación: ' . $e->getMessage());
        }
    }
    
    /**
     * Enviar email de error
     */
    public function sendErrorEmail(Payment $payment, string $status = 'failed'): void
    {
        try {
            Mail::to($payment->customer_email)->send(new PaymentError($payment, $status));
            Log::info('✅ Email de error enviado exitosamente', [
                'to' => $payment->customer_email,
                'payment_id' => $payment->id,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error enviando email de error: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar pagos pendientes (para cron job)
     */
    public function checkPendingPayments(): void
    {
        try {
            $pendingPayments = Payment::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
            
            Log::info('Verificando pagos pendientes', ['count' => $pendingPayments->count()]);
            
            foreach ($pendingPayments as $payment) {
                // Marcar como expirado
                $payment->update([
                    'status' => 'failed',
                    'observations' => 'Expirado automáticamente - Sin pago en 24 horas',
                ]);
                
                // Enviar email de expiración
                $this->sendErrorEmail($payment, 'expired');
                
                Log::info('Pago marcado como expirado', [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error verificando pagos pendientes: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar pago específico con API de Wompi (opcional)
     */
    public function checkPaymentWithWompi(string $reference): ?array
    {
        try {
            // Buscar pago
            $payment = Payment::where('reference', $reference)->first();
            
            if (!$payment || !$payment->wompi_id) {
                return null;
            }
            
            // Aquí podrías implementar una llamada a la API de Wompi
            // para verificar el estado real del pago
            // $wompiService = app(WompiService::class);
            // $transaction = $wompiService->getTransaction($payment->wompi_id);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error verificando pago con Wompi: ' . $e->getMessage());
            return null;
        }
    }
}