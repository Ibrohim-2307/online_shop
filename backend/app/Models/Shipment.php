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
        'delivery_otp',
        'delivery_otp_expires_at',
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

    /**
     * The attributes that should be hidden for serialization (API JSON).
     * Protects the buyer's delivery OTP from leaking to couriers or unauthorized callers.
     */
    protected $hidden = [
        'delivery_otp',
        'delivery_otp_expires_at',
    ];

    public function suborder(): BelongsTo
    {
        return $this->belongsTo(Suborder::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Order::class, Suborder::class, 'id', 'id', 'suborder_id', 'order_id');
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

    /**
     * Ensure a 4-digit delivery confirmation code (OTP) exists for the buyer.
     */
    public function ensureDeliveryOtp(): string
    {
        if (empty($this->delivery_otp)) {
            $code = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $this->update(['delivery_otp' => $code]);
            $this->delivery_otp = $code;
        }

        return $this->delivery_otp;
    }
}
