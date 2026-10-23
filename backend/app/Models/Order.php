<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'currency',
        'items_total',
        'shipping_total',
        'grand_total',
        'shipping_address',
        'idempotency_key',
        'notes',
    ];

    protected $casts = [
        'items_total' => 'integer',
        'shipping_total' => 'integer',
        'grand_total' => 'integer',
        'shipping_address' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function suborders(): HasMany
    {
        return $this->hasMany(Suborder::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getLatestPaymentAttribute(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function getItemsAttribute()
    {
        return $this->suborders->flatMap->items;
    }

    public function getShipmentsAttribute()
    {
        return $this->suborders->flatMap->shipments;
    }
}
