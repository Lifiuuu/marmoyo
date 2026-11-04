<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_id',
        'menu_name',
        'unit_price',
        'quantity',
        'subtotal_price',
        'spice_level',
        'noodle_type',
        'toppings_selected',
        'special_notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal_price' => 'decimal:2',
        'toppings_selected' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}

