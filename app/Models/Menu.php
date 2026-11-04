<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image_url',
        'is_spicy',
        'default_spiciness_level',
        'is_available',
        'is_bestseller',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_spicy' => 'boolean',
        'is_available' => 'boolean',
        'is_bestseller' => 'boolean',
        'default_spiciness_level' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuVariant::class);
    }

    public function spicinessVariants(): HasMany
    {
        return $this->hasMany(MenuVariant::class)->where('group_name', 'Tingkat Kepedasan');
    }

    public function noodleVariants(): HasMany
    {
        return $this->hasMany(MenuVariant::class)->where('group_name', 'Jenis Mie');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Compute bestseller status dynamically from real sales volume & category ranking.
     */
    public function getIsBestsellerAttribute($value): bool
    {
        $bestsellerIds = \App\Services\Ai\AiMenuRecommendationService::getBestsellerMenuIds();
        if (!empty($bestsellerIds)) {
            return in_array($this->id, $bestsellerIds);
        }

        return (bool) $value;
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}

