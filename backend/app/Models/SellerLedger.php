<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerLedger extends Model
{
    use HasFactory;

    // Immutable append-only ledger: no updated_at column
    public $timestamps = false;

    protected $fillable = [
        'seller_id',
        'suborder_id',
        'order_id',
        'type',
        'entry_type',
        'amount',
        'currency',
        'running_balance',
        'reference',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'running_balance' => 'integer',
        'created_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function suborder(): BelongsTo
    {
        return $this->belongsTo(Suborder::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
