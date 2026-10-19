<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'min_quantity',
        'max_quantity',
        'unit_price',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
