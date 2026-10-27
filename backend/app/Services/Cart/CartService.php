<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Pricing\PricingService;
use InvalidArgumentException;

class CartService
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    /**
     * Get active cart for user or guest session, or create a new one.
     */
    public function getCartForUserOrSession(?User $user, ?string $sessionId): Cart
    {
        if ($user) {
            $userCart = Cart::where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            // Check if there is a guest cart for this session that needs merging
            if ($sessionId) {
                $guestCart = Cart::where('session_id', $sessionId)
                    ->where('status', 'active')
                    ->whereNull('user_id')
                    ->with('items')
                    ->latest()
                    ->first();

                if ($guestCart) {
                    if (!$userCart) {
                        // User has no active cart, so directly associate the guest cart with this user
                        $guestCart->update(['user_id' => $user->id]);
                        return $guestCart;
                    }

                    // User already has an active cart: safely merge items from guestCart into userCart
                    foreach ($guestCart->items as $guestItem) {
                        $existingUserItem = CartItem::where('cart_id', $userCart->id)
                            ->where('product_variant_id', $guestItem->product_variant_id)
                            ->first();

                        if ($existingUserItem) {
                            $existingUserItem->update([
                                'quantity' => (float) $existingUserItem->quantity + (float) $guestItem->quantity,
                            ]);
                            $guestItem->delete();
                        } else {
                            $guestItem->update(['cart_id' => $userCart->id]);
                        }
                    }

                    $guestCart->update(['status' => 'abandoned']);
                }
            }

            if (!$userCart) {
                $userCart = Cart::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'status' => 'active',
                ]);
            }

            return $userCart;
        }

        if (!$sessionId) {
            $sessionId = bin2hex(random_bytes(16));
        }

        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'status' => 'active'],
            ['user_id' => null]
        );
    }

    /**
     * Add an item to the cart, validating MOQ and unit step.
     */
    public function addItem(Cart $cart, int $variantId, float|string $quantity): CartItem
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);
        $product = $variant->product;

        $qtyFloat = (float) $quantity;
        $minQty = (float) ($product->min_order_quantity ?? 1.0);
        $step = (float) ($product->unit_step ?? 1.0);

        if ($qtyFloat < $minQty) {
            throw new InvalidArgumentException(
                "Minimal buyurtma miqdori: {$minQty} {$product->unit}."
            );
        }

        // Validate step
        $diff = fmod($qtyFloat - $minQty, $step);
        if (abs($diff) > 0.0001 && abs($diff - $step) > 0.0001) {
            throw new InvalidArgumentException(
                "Miqdor qadami noto'g'ri. Minimal: {$minQty}, qadam: {$step} {$product->unit}."
            );
        }

        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existing) {
            $newQty = (float) $existing->quantity + $qtyFloat;
            $existing->update(['quantity' => $newQty]);
            return $existing;
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variantId,
            'quantity' => $qtyFloat,
        ]);
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function updateItem(Cart $cart, int $itemId, float|string $quantity): CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);
        $variant = ProductVariant::with('product')->findOrFail($item->product_variant_id);
        $product = $variant->product;

        $qtyFloat = (float) $quantity;
        $minQty = (float) ($product->min_order_quantity ?? 1.0);
        $step = (float) ($product->unit_step ?? 1.0);

        if ($qtyFloat < $minQty) {
            throw new InvalidArgumentException(
                "Minimal buyurtma miqdori: {$minQty} {$product->unit}."
            );
        }

        $diff = fmod($qtyFloat - $minQty, $step);
        if (abs($diff) > 0.0001 && abs($diff - $step) > 0.0001) {
            throw new InvalidArgumentException(
                "Miqdor qadami noto'g'ri. Minimal: {$minQty}, qadam: {$step} {$product->unit}."
            );
        }

        $item->update(['quantity' => $qtyFloat]);
        return $item;
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Cart $cart, int $itemId): void
    {
        CartItem::where('cart_id', $cart->id)->where('id', $itemId)->delete();
    }

    /**
     * Calculate multi-vendor quote with live server pricing and delivery fees.
     */
    public function getCartQuote(Cart $cart): array
    {
        $items = CartItem::where('cart_id', $cart->id)
            ->with(['variant.product.category', 'variant.product.seller', 'variant.priceTiers'])
            ->get();

        if ($items->isEmpty()) {
            return [
                'is_empty' => true,
                'items_count' => 0,
                'sellers' => [],
                'items_total' => 0,
                'shipping_total' => 0,
                'grand_total' => 0,
                'currency' => 'UZS',
                'expires_at' => now()->addMinutes(15)->toIso8601String(),
            ];
        }

        $sellersGrouped = [];
        $overallItemsTotal = '0';
        $overallShippingTotal = '0';

        foreach ($items as $item) {
            $variant = $item->variant;
            $product = $variant->product;
            $seller = $product->seller;

            $pricingQuote = $this->pricingService->calculateQuote($variant, (string) $item->quantity);

            $sellerId = $seller->id;
            if (!isset($sellersGrouped[$sellerId])) {
                $sellersGrouped[$sellerId] = [
                    'seller_id' => $seller->id,
                    'seller_name' => $seller->name,
                    'seller_slug' => $seller->slug,
                    'commission_rate' => (float) ($seller->commission_rate ?? 5.0),
                    'items' => [],
                    'items_subtotal' => '0',
                    'shipping_cost' => '30000', // Standard 30,000 UZS shipping per seller
                ];
            }

            $sellersGrouped[$sellerId]['items'][] = [
                'cart_item_id' => $item->id,
                'variant_id' => $variant->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image_url' => $product->image_url,
                'sku' => $variant->sku,
                'unit' => $product->unit,
                'quantity' => (float) $item->quantity,
                'unit_price' => (int) $pricingQuote['unit_price'],
                'subtotal' => (int) $pricingQuote['total_price'],
                'tier_id' => $pricingQuote['tier_id'],
                'attributes' => $variant->attributes ?? [],
            ];

            $sellersGrouped[$sellerId]['items_subtotal'] = bcadd(
                $sellersGrouped[$sellerId]['items_subtotal'],
                $pricingQuote['total_price'],
                0
            );
        }

        // Calculate delivery totals
        foreach ($sellersGrouped as &$sellerGroup) {
            // Free shipping if seller subtotal is 1,000,000 UZS or more
            if (bccomp($sellerGroup['items_subtotal'], '1000000', 0) >= 0) {
                $sellerGroup['shipping_cost'] = '0';
            }

            $sellerGroup['total_amount'] = bcadd(
                $sellerGroup['items_subtotal'],
                $sellerGroup['shipping_cost'],
                0
            );

            $overallItemsTotal = bcadd($overallItemsTotal, $sellerGroup['items_subtotal'], 0);
            $overallShippingTotal = bcadd($overallShippingTotal, $sellerGroup['shipping_cost'], 0);
        }
        unset($sellerGroup);

        $grandTotal = bcadd($overallItemsTotal, $overallShippingTotal, 0);

        return [
            'is_empty' => false,
            'items_count' => $items->count(),
            'sellers' => array_values($sellersGrouped),
            'items_total' => (int) $overallItemsTotal,
            'shipping_total' => (int) $overallShippingTotal,
            'grand_total' => (int) $grandTotal,
            'currency' => 'UZS',
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ];
    }
}
