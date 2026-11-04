<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'store_tagline',
        'store_address',
        'store_phone',
        'receipt_footer',
        'packaging_fee',
        'tax_pb1_percent',
        'promo_active',
        'promo_discount',
    ];

    protected $casts = [
        'packaging_fee' => 'decimal:2',
        'tax_pb1_percent' => 'decimal:2',
        'promo_active' => 'boolean',
        'promo_discount' => 'decimal:2',
    ];

    /**
     * Get or initialize the singleton store settings row.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'store_name' => 'Marmoyo Smart-Dine',
            'store_tagline' => 'Authentic Spicy Noodles & Dimsum',
            'store_address' => 'Jl. Marmoyo No. 12, Surabaya, Jawa Timur',
            'store_phone' => '0812-3456-7890',
            'receipt_footer' => "Terima kasih atas kunjungan Anda!\nFollow IG: @miemarmoyo",
            'packaging_fee' => 2000.00,
            'tax_pb1_percent' => 10.00,
            'promo_active' => true,
            'promo_discount' => 3000.00,
        ]);
    }
}

