<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suborder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'suborder_number',
        'status',
        'items_subtotal',
        'shipping_cost',
        'total_amount',
        'commission_rate',
        'commission_amount',
        'notes',
    ];

    protected $casts = [
        'items_subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'total_amount' => 'integer',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
