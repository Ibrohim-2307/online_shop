<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'suborder_id',
        'user_id',
        'refund_number',
        'amount',
        'reason',
        'status',
        'moderator_id',
        'moderator_notes',
        'is_inventory_restocked',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_inventory_restocked' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function suborder(): BelongsTo
    {
        return $this->belongsTo(Suborder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
