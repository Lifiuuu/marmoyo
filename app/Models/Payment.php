<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_code',
        'transaction_id',
        'method',
        'amount',
        'cash_received',
        'cash_change',
        'qris_payload',
        'qris_url',
        'payload_meta',
        'status',
        'expires_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'payload_meta' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast() && $this->status === 'pending');
    }
}

