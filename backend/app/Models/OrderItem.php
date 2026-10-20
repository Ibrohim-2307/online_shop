<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'suborder_id',
        'product_variant_id',
        'product_id',
        'product_name',
        'variant_sku',
        'unit',
        'quantity',
        'unit_price',
        'subtotal',
        'price_tier_id',
        'attributes_snapshot',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
        'attributes_snapshot' => 'array',
    ];

    public function suborder(): BelongsTo
    {
        return $this->belongsTo(Suborder::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
