<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'status',
        'hold_until',
        'has_dispute_block',
        'bank_details',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'hold_until' => 'datetime',
        'has_dispute_block' => 'boolean',
        'bank_details' => 'array',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
