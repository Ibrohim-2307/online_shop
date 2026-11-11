<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'min_order_amount' => 'integer',
        'max_discount_amount' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if promocode is currently valid and usable for given amount.
     */
    public function validateForSubtotal(int $subtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Ushbu promokod faol emas.'];
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return ['valid' => false, 'message' => 'Ushbu promokodning amal qilish muddati hali boshlanmadi.'];
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return ['valid' => false, 'message' => 'Ushbu promokodning amal qilish muddati tugagan.'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Ushbu promokoddan foydalanish limiti tugagan.'];
        }

        if ($subtotal < $this->min_order_amount) {
            $minFormatted = number_format($this->min_order_amount, 0, '.', ' ');
            return [
                'valid' => false,
                'message' => "Ushbu promokod faqat kamida {$minFormatted} UZS lik xaridlar uchun amal qiladi."
            ];
        }

        return ['valid' => true, 'message' => 'Promokod qabul qilindi.'];
    }

    /**
     * Calculate exact discount amount in UZS for given subtotal.
     */
    public function calculateDiscount(int $subtotal): int
    {
        if ($this->type === 'percentage') {
            $discount = (int) round(($subtotal * $this->value) / 100);
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } else {
            $discount = (int) $this->value;
        }

        return min($discount, $subtotal);
    }
}
