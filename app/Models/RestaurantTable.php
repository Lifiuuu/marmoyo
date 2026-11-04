<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'qr_token',
        'status',
    ];

    public static function generateTokenForTable(string $tableNumber): string
    {
        return hash_hmac('sha256', $tableNumber . '-' . config('app.key'), config('app.key'));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}

