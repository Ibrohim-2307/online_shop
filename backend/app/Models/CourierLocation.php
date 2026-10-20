<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'shipment_id',
        'event_id',
        'latitude',
        'longitude',
        'accuracy',
        'heading',
        'speed',
        'recorded_at',
        'received_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'float',
        'heading' => 'float',
        'speed' => 'float',
        'recorded_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
