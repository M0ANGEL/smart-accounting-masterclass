<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Página principal
Route::get('/', [PaymentController::class, 'index'])->name('home');

// Procesar formulario
Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');

// Página de pago con QR
Route::get('/payment/qr/{id}', [PaymentController::class, 'showQR'])->name('payment.qr');

// Verificar estado del pago (AJAX)
Route::get('/payment/check-status/{reference}', [PaymentController::class, 'checkStatus'])
    ->name('payment.check-status');

// Página de pago pendiente
Route::get('/payment/pending/{reference}', [PaymentController::class, 'pending'])
    ->name('payment.pending');

// Redirección desde Wompi
Route::get('/payment/redirect/{reference}', [PaymentController::class, 'webhookRedirect'])
    ->name('payment.webhook-redirect');

// Páginas de resultado
Route::get('/payment/success/{reference}', [PaymentController::class, 'success'])
    ->name('payment.success');

Route::get('/payment/error/{reference}', [PaymentController::class, 'error'])
    ->name('payment.error');

// Webhook para Wompi (sin protección CSRF)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->withoutMiddleware(['csrf']);

// Ruta para verificar pagos expirados (usar en comando o manual)
Route::get('/payment/check-expired', function() {
    $controller = app(PaymentController::class);
    $result = $controller->checkExpiredPayments();
    return response()->json(['success' => $result]);
});