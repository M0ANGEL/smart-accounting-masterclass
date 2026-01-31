<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmation;
use App\Mail\PaymentError;

class PaymentQRService
{
    protected $qrLink;
    
    public function __construct()
    {
        $this->qrLink = config('wompi.qr_link');
    }
    
    /**
     * Procesar el pago vía QR
     */
    public function processPayment(Payment $payment): string
    {
        try {
            // Generar referencia única para el QR
            $reference = $this->generateQRReference($payment);
            
            // Actualizar la referencia en el pago
            $payment->update([
                'reference' => $reference,
                'observations' => 'QR generado - Esperando pago',
            ]);
            
            Log::info('QR generado para pago', [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'email' => $payment->customer_email,
            ]);
            
            // Retornar el link del QR con la referencia
            return $this->getQRUrl($reference);
            
        } catch (\Exception $e) {
            Log::error('Error generando QR: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generar referencia única para el QR
     */
    private function generateQRReference(Payment $payment): string
    {
        // Formato: MC_QR_{timestamp}_{random}_{email_hash}
        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 6);
        $emailHash = substr(md5($payment->customer_email), 0, 4);
        
        return "MC_QR_{$timestamp}_{$random}_{$emailHash}";
    }
    
    /**
     * Obtener URL del QR con referencia
     */
    private function getQRUrl(string $reference): string
    {
        $baseUrl = rtrim($this->qrLink, '/');
        return "{$baseUrl}?reference={$reference}";
    }
    
    /**
     * Verificar estado del pago después del QR
     */
    public function verifyPayment(string $reference): array
    {
        try {
            // Buscar el pago por referencia
            $payment = Payment::where('reference', $reference)->first();
            
            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pago no encontrado',
                ];
            }
            
            // Aquí normalmente harías una llamada a la API de Wompi
            // para verificar el estado real del pago
            // Por ahora, simularemos que el pago fue exitoso si el estado es approved
            
            if ($payment->status === 'approved') {
                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Pago confirmado',
                ];
            }
            
            return [
                'success' => false,
                'payment' => $payment,
                'message' => 'Pago pendiente o rechazado',
            ];
            
        } catch (\Exception $e) {
            Log::error('Error verificando pago QR: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Procesar webhook de Wompi para QR
     */
    public function processWebhook(array $data): bool
    {
        try {
            Log::info('Procesando webhook para QR', $data);
            
            if (!isset($data['reference'])) {
                Log::warning('Webhook sin referencia', $data);
                return false;
            }
            
            $reference = $data['reference'];
            $status = strtolower($data['status'] ?? 'pending');
            
            // Buscar pago por referencia
            $payment = Payment::where('reference', $reference)->first();
            
            if (!$payment) {
                Log::warning('Pago no encontrado en webhook', ['reference' => $reference]);
                return false;
            }
            
            // Actualizar estado
            $payment->update([
                'status' => $status,
                'wompi_response' => $data,
                'observations' => 'Actualizado por webhook QR: ' . $status,
            ]);
            
            // Enviar email según estado
            if ($status === 'approved') {
                $this->sendConfirmationEmail($payment);
                Log::info('Email de confirmación enviado', [
                    'email' => $payment->customer_email,
                    'reference' => $reference,
                ]);
            } elseif (in_array($status, ['declined', 'failed', 'error'])) {
                $this->sendErrorEmail($payment);
                Log::info('Email de error enviado', [
                    'email' => $payment->customer_email,
                    'status' => $status,
                ]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error procesando webhook QR: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de confirmación
     */
    private function sendConfirmationEmail(Payment $payment): void
    {
        try {
            Mail::to($payment->customer_email)->send(new PaymentConfirmation($payment));
            Log::info('Email de confirmación enviado exitosamente', [
                'to' => $payment->customer_email,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de confirmación: ' . $e->getMessage());
        }
    }
    
    /**
     * Enviar email de error
     */
    private function sendErrorEmail(Payment $payment): void
    {
        try {
            Mail::to($payment->customer_email)->send(new PaymentError($payment));
            Log::info('Email de error enviado exitosamente', [
                'to' => $payment->customer_email,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando email de error: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar si un pago está pendiente por mucho tiempo
     */
    public function checkPendingPayments(): void
    {
        try {
            $pendingPayments = Payment::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
            
            foreach ($pendingPayments as $payment) {
                // Marcar como expirado
                $payment->update([
                    'status' => 'failed',
                    'observations' => 'Expirado - Sin pago en 24 horas',
                ]);
                
                // Enviar email de expiración
                $this->sendErrorEmail($payment);
                
                Log::info('Pago marcado como expirado', [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error verificando pagos pendientes: ' . $e->getMessage());
        }
    }
}