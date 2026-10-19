<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'type',
        'file_path',
        'status',
        'rejection_reason',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
