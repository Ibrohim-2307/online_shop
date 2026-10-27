<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payme_trans_id',
        'amount',
        'state',
        'reason',
        'payme_time',
        'create_time',
        'perform_time',
        'cancel_time',
    ];

    protected $casts = [
        'amount' => 'integer',
        'state' => 'integer',
        'reason' => 'integer',
        'payme_time' => 'integer',
        'create_time' => 'integer',
        'perform_time' => 'integer',
        'cancel_time' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
