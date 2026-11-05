<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Only stores approved by an admin (and not suspended) may show products to shoppers.
     */
    public function scopeFromActiveSeller($query)
    {
        return $query->whereHas('seller', fn($q) => $q->where('status', 'active'));
    }

    /**
     * "Frozen" products (nothing left to sell) are kept out of listings automatically
     * and come back on their own as soon as the seller restocks.
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('variants', fn($vq) => $vq->where('is_active', true)
            ->whereHas('inventoryBalances', fn($bq) => $bq->whereColumn('quantity', '>', 'reserved_quantity')));
    }

    /**
     * Units that can still be sold across all active variants (on hand minus reserved).
     */
    public function availableStock(): float
    {
        $this->loadMissing('variants.inventoryBalances');

        return (float) $this->variants->where('is_active', true)->flatMap->inventoryBalances
            ->sum(fn($b) => max(0, (float) $b->quantity - (float) $b->reserved_quantity));
    }

    public function isFrozen(): bool
    {
        return $this->availableStock() <= 0;
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
