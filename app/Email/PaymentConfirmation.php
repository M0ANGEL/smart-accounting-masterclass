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
    public $courseDetails;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->courseDetails = [
            'name' => 'Masterclass Auditoría Analítica y Power BI',
            'start_date' => '7 de Febrero 2024',
            'schedule' => 'Horario por confirmar',
            'duration' => '8 horas en vivo',
            'access' => 'Acceso a grabaciones por 6 meses',
            'materials' => 'Plantillas y materiales descargables',
            'certificate' => 'Certificado digital incluido',
        ];
    }

    public function build()
    {
        return $this->subject('✅ Confirmación de Inscripción - Masterclass Smart Accounting')
                    ->view('emails.payment-confirmation')
                    ->with([
                        'payment' => $this->payment,
                        'course' => $this->courseDetails,
                        'supportEmail' => config('wompi.email.support'),
                    ]);
    }
}