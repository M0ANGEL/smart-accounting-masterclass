<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaymentController::class, 'index'])->name('home');
Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
Route::get('/payment/qr/{id}', [PaymentController::class, 'showQR'])->name('payment.qr');
Route::get('/payment/verify/{reference}', [PaymentController::class, 'verifyQR'])->name('payment.verify');
Route::get('/payment/pending/{reference}', [PaymentController::class, 'pending'])->name('payment.pending');
Route::get('/payment/success/{reference}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/error/{reference}', [PaymentController::class, 'error'])->name('payment.error');
Route::get('/payment/check-status/{reference}', [PaymentController::class, 'checkStatus'])->name('payment.check-status');

// Webhook para Wompi
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware(['csrf']);

// Comando para limpiar pagos expirados (ejecutar vía cron)
Route::get('/payment/cleanup', function() {
    // Este endpoint puede ser llamado por un cron job
    // Para limpiar pagos pendientes de más de 24 horas
    \App\Models\Payment::where('status', 'pending')
        ->where('created_at', '<', now()->subHours(24))
        ->update(['status' => 'failed']);
    
    return response()->json(['cleaned' => true]);
});

Route::get('/health', function () {
    return response()->json(['status' => 'healthy']);
});