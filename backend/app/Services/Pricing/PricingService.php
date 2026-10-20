<?php

namespace App\Services\Pricing;

use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use InvalidArgumentException;

class PricingService
{
    /**
     * Validate requested quantity against product unit rules, step, and min order quantity.
     *
     * @return array{valid: bool, error: ?string, effective_units: string}
     */
    public function validateQuantity(Product $product, string|int|float $quantity): array
    {
        $qtyStr = (string) $quantity;

        // Must be positive number
        if (bccomp($qtyStr, '0', 3) <= 0) {
            return [
                'valid' => false,
                'error' => "Miqdor noldan katta bo'lishi shart",
                'effective_units' => '0',
            ];
        }

        $minQty = (string) ($product->min_order_quantity ?? '1.000');
        if (bccomp($qtyStr, $minQty, 3) < 0) {
            return [
                'valid' => false,
                'error' => "Minimal buyurtma miqdori: {$minQty} {$product->unit}",
                'effective_units' => $qtyStr,
            ];
        }

        // Unit-specific constraints
        switch ($product->unit) {
            case 'piece':
                // Pieces must be strictly whole integers (no floating fractions like 1.5)
                if (floor((float) $quantity) != (float) $quantity) {
                    return [
                        'valid' => false,
                        'error' => "Dona birligidagi mahsulot faqat butun son bo'lishi shart",
                        'effective_units' => $qtyStr,
                    ];
                }
                $effectiveUnits = (string) (int) $quantity;
                break;

            case 'box':
                // Box count must be a whole integer
                if (floor((float) $quantity) != (float) $quantity) {
                    return [
                        'valid' => false,
                        'error' => "Quti birligidagi mahsulot soni butun bo'lishi shart",
                        'effective_units' => $qtyStr,
                    ];
                }
                $itemsPerBox = $product->items_per_box ?? 1;
                // Effective pieces = boxes * items_per_box (calculated without floats)
                $effectiveUnits = bcmul($qtyStr, (string) $itemsPerBox, 0);
                break;

            case 'kg':
                // Decimal is permitted for kilograms
                $effectiveUnits = number_format((float) $quantity, 3, '.', '');
                break;

            case 'ton':
                // Decimal permitted; 1 ton = 1,000 kg
                $effectiveUnits = bcmul($qtyStr, '1000', 3);
                break;

            default:
                $effectiveUnits = $qtyStr;
        }

        // Step validation (miqdor qadami)
        $step = (string) ($product->unit_step ?? '1.000');
        if (bccomp($step, '0', 3) > 0) {
            // (Quantity - MinQuantity) must be a multiple of step
            $diff = bcsub($qtyStr, $minQty, 3);
            $quotient = bcdiv($diff, $step, 6);
            $roundedQuotient = round((float) $quotient);
            $recalculated = bcmul((string) $roundedQuotient, $step, 3);

            if (bccomp($diff, $recalculated, 3) !== 0) {
                return [
                    'valid' => false,
                    'error' => "Buyurtma miqdori {$step} qadamiga mos bo'lishi kerak",
                    'effective_units' => $effectiveUnits,
                ];
            }
        }

        return [
            'valid' => true,
            'error' => null,
            'effective_units' => $effectiveUnits,
        ];
    }

    /**
     * Match the active price tier for the given variant and quantity using all-units pricing.
     */
    public function matchTier(ProductVariant $variant, string|int|float $quantity): ?PriceTier
    {
        $qtyStr = (string) $quantity;
        $tiers = $variant->relationLoaded('priceTiers')
            ? $variant->priceTiers->sortBy('min_quantity')
            : $variant->priceTiers()->orderBy('min_quantity')->get();

        foreach ($tiers as $tier) {
            $minMatch = bccomp($qtyStr, (string) $tier->min_quantity, 3) >= 0;
            $maxMatch = $tier->max_quantity === null || bccomp($qtyStr, (string) $tier->max_quantity, 3) <= 0;

            if ($minMatch && $maxMatch) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * Calculate price quote strictly avoiding PHP native floating-point math.
     * All monetary values are calculated using BCMath arbitrary precision strings/integers.
     *
     * @return array{
     *   variant_id: int,
     *   sku: string,
     *   quantity: string,
     *   unit: string,
     *   base_unit_price: string,
     *   unit_price: string,
     *   total_price: string,
     *   currency: string,
     *   tier_id: int,
     *   min_quantity: string,
     *   max_quantity: ?string
     * }
     */
    public function calculateQuote(ProductVariant $variant, string|int|float $quantity): array
    {
        $product = $variant->product;

        $validation = $this->validateQuantity($product, $quantity);
        if (!$validation['valid']) {
            throw new InvalidArgumentException($validation['error']);
        }

        $tier = $this->matchTier($variant, $quantity);

        if (!$tier) {
            if ($variant->price !== null && (float) $variant->price > 0) {
                $unitPrice = (string) (int) $variant->price;
                $totalPrice = bcmul((string) $quantity, $unitPrice, 0);

                return [
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'quantity' => (string) $quantity,
                    'unit' => $product->unit,
                    'base_unit_price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'currency' => $variant->currency ?? 'UZS',
                    'tier_id' => null,
                    'min_quantity' => (string) ($product->min_order_quantity ?? '1'),
                    'max_quantity' => null,
                ];
            }

            throw new InvalidArgumentException("Ushbu miqdor ({$quantity}) uchun narx pog'onasi belgilanmagan");
        }

        $tiers = $variant->relationLoaded('priceTiers')
            ? $variant->priceTiers->sortBy('min_quantity')
            : $variant->priceTiers()->orderBy('min_quantity')->get();
        $baseTier = $tiers->first();
        $baseUnitPrice = $baseTier ? (string) $baseTier->unit_price : (string) $tier->unit_price;

        // All-units calculation: total_price = quantity * unit_price (Zero floating-point arithmetic!)
        $totalPrice = bcmul((string) $quantity, (string) $tier->unit_price, 0);

        return [
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'quantity' => (string) $quantity,
            'unit' => $product->unit,
            'base_unit_price' => $baseUnitPrice,
            'unit_price' => (string) $tier->unit_price,
            'total_price' => $totalPrice,
            'currency' => $tier->currency,
            'tier_id' => $tier->id,
            'min_quantity' => (string) $tier->min_quantity,
            'max_quantity' => $tier->max_quantity ? (string) $tier->max_quantity : null,
        ];
    }
}
