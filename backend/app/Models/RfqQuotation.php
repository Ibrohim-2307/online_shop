<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'seller_id',
        'unit_price',
        'shipping_cost',
        'is_shipping_negotiated',
        'total_price',
        'status',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'shipping_cost' => 'integer',
        'total_price' => 'integer',
        'is_shipping_negotiated' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
