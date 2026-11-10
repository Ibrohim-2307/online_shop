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
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'hold_until' => 'datetime',
        'reviewed_at' => 'datetime',
        'has_dispute_block' => 'boolean',
        'bank_details' => 'array',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Card or account number with only the last four digits readable.
     */
    public function maskedAccount(): string
    {
        $number = (string) ($this->bank_details['number'] ?? $this->bank_details['bank_account'] ?? '');
        if (strlen($number) <= 4) {
            return $number;
        }

        return str_repeat('•', 4) . ' ' . substr($number, -4);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['requested', 'held', 'processing'], true);
    }
}
