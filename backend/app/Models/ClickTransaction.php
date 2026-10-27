<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClickTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'click_trans_id',
        'service_id',
        'click_paydoc_id',
        'merchant_trans_id',
        'amount',
        'action',
        'error',
        'error_note',
        'sign_time',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
        'action' => 'integer',
        'error' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
