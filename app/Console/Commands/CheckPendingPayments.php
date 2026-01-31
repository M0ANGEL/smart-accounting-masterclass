<?php

namespace App\Console\Commands;

use App\Services\PaymentQRService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPendingPayments extends Command
{
    protected $signature = 'payments:check-pending';
    protected $description = 'Verificar pagos pendientes y marcar como expirados';

    protected $paymentService;

    public function __construct(PaymentQRService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    public function handle()
    {
        $this->info('Iniciando verificación de pagos pendientes...');
        
        $this->paymentService->checkPendingPayments();
        
        $this->info('Verificación completada.');
        
        return Command::SUCCESS;
    }
}