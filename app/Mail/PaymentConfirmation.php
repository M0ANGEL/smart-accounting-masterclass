<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject('✅ Confirmación de Inscripción - Masterclass Smart Accounting')
                    ->view('emails.confirmation')
                    ->with([
                        'payment' => $this->payment,
                        'course' => [
                            'name' => 'Masterclass Auditoría Analítica y Power BI',
                            'start_date' => '7 de Febrero 2024',
                        ],
                    ]);
    }
}