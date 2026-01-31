<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'country',
        'profession',
        'expectations',
        'amount',
        'currency',
        'status',
        'wompi_id',
        'payment_method',
        'payment_method_type',
        'wompi_response',
        'email_sent',
        'observations',
    ];

    protected $casts = [
        'amount' => 'integer',
        'email_sent' => 'boolean',
        'wompi_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Accesor para el monto formateado
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount / 100, 0, ',', '.') . ' COP';
    }

    // Accesor para teléfono completo
    public function getFullPhoneAttribute()
    {
        $countries = config('wompi.countries', []);
        $countryCode = $countries[$this->country]['code'] ?? '+57';
        return $countryCode . ' ' . $this->customer_phone;
    }

    // Método para verificar si está aprobado
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    // Método para verificar si está pendiente
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Método para verificar si falló
    public function isFailed()
    {
        return in_array($this->status, ['declined', 'failed', 'error', 'voided', 'expired']);
    }

    // Scope para pagos pendientes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope para pagos aprobados
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Generar referencia automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->reference)) {
                $timestamp = time();
                $random = strtoupper(substr(md5(uniqid()), 0, 6));
                $emailPrefix = substr(str_replace(['@', '.', '-'], '', $payment->customer_email), 0, 3);
                $payment->reference = "MC_{$timestamp}_{$random}_{$emailPrefix}";
            }
        });
    }
}