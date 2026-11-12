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

    /**
     * Promocode discount carried by this seller's part of the order
     * (goods + delivery - what the customer is charged for this part).
     */
    public function discountShare(): int
    {
        return max(0, (int) $this->items_subtotal + (int) $this->shipping_cost - (int) $this->total_amount);
    }

    /**
     * What the customer actually paid for the goods in this part, i.e. the most they can get back.
     * A 3 000 000 item bought with a 3 000 000 promocode refunds 0, not 3 000 000.
     */
    public function refundableGoodsAmount(): int
    {
        return max(0, (int) $this->items_subtotal - $this->discountShare());
    }
}
