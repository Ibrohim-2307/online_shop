<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'suborder_id',
        'tracking_number',
        'courier_id',
        'status',
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
}
