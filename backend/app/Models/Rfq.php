<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfq extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit',
        'target_price',
        'status',
        'notes',
        'valid_until',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'target_price' => 'integer',
        'valid_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(RfqQuotation::class);
    }
}
