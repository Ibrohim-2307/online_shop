<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'image_url',
        'trade_mode',
        'unit',
        'items_per_box',
        'unit_step',
        'min_order_quantity',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'items_per_box' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->oldestOfMany();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true)->latest();
    }

    public function getAverageRatingAttribute(): float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round((float) $avg, 1) : 5.0;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getRatingBreakdownAttribute(): array
    {
        $total = $this->reviews_count;
        $breakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $this->reviews()->where('rating', $i)->count();
            $percentage = $total > 0 ? round(($count / $total) * 100) : ($i === 5 ? 100 : 0);
            $breakdown[$i] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }
        return $breakdown;
    }
}
