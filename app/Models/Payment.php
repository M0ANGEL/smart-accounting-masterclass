<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    protected $table = 'payments';
    
    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'country_code',
        'country',
        'profession',
        'expectations',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_method_type',
        'wompi_id',
        'wompi_response',
        'observations',
    ];

    protected $casts = [
        'wompi_response' => 'array',
        'amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->currency === 'COP') {
                    return '$' . number_format($this->amount / 100, 0, ',', '.') . ' COP';
                }
                return '$' . number_format($this->amount / 100, 2);
            }
        );
    }

    protected function fullPhone(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->country_code . ' ' . $this->customer_phone;
            }
        );
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['declined', 'error', 'failed', 'voided']);
    }
}