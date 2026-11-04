<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'order_type',
        'table_id',
        'customer_name',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_fee',
        'packaging_fee',
        'final_amount',
        'status',
        'payment_status',
        'payment_method',
        'cashier_id',
        'kitchen_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'packaging_fee' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id')->withDefault([
            'table_number' => 'TA',
        ]);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function isTakeAway(): bool
    {
        return $this->order_type === 'take_away' || empty($this->table_id);
    }

    public function isDineIn(): bool
    {
        return !$this->isTakeAway();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPendingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }
}

