<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'suborder_id',
        'tracking_number',
        'courier_id',
        'status',
        'delay_reason',
        'shipping_method',
        'shipping_cost',
        'pickup_address',
        'delivery_address',
        'estimated_delivery_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_cost' => 'integer',
        'pickup_address' => 'array',
        'delivery_address' => 'array',
        'estimated_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function suborder(): BelongsTo
    {
        return $this->belongsTo(Suborder::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function courierLocations(): HasMany
    {
        return $this->hasMany(CourierLocation::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}
