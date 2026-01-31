<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaymentController::class, 'index'])->name('home');
Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
Route::post('/payment/card/{reference}', [PaymentController::class, 'processCardPayment'])->name('payment.card');

// Webhook sin CSRF
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware(['csrf']);

Route::get('/payment/return', [PaymentController::class, 'return'])->name('payment.return');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/error', [PaymentController::class, 'error'])->name('payment.error');

Route::get('/health', function () {
    return response(file_get_contents(base_path('scripts/healthcheck.php')))
        ->header('Content-Type', 'text/plain');
});