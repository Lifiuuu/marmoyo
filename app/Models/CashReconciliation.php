<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id',
        'shift_date',
        'starting_cash',
        'total_cash_sales',
        'total_qris_sales',
        'actual_physical_cash',
        'cash_difference',
        'notes',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'starting_cash' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_qris_sales' => 'decimal:2',
        'actual_physical_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}

