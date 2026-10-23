<?php

namespace App\Services\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\IdempotencyKey;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OutboxEvent;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\Suborder;
use App\Models\User;
use App\Services\Pricing\PricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        protected PricingService $pricingService,
        protected InventoryReservationService $reservationService
    ) {}

    /**
     * Process reliable, atomic checkout with idempotency, multi-vendor order split,
     * server pricing verification, stock reservation, and transactional outbox.
     */
    public function processCheckout(
        User $user,
        array $payload,
        ?string $idempotencyKey = null
    ): array {
        // 1. Idempotency Check
        $requestHash = hash('sha256', json_encode($payload));

        if ($idempotencyKey) {
            $existingIdempotency = IdempotencyKey::where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->first();

            if ($existingIdempotency) {
                if ($existingIdempotency->request_hash !== $requestHash) {
                    throw ValidationException::withMessages([
                        'idempotency' => ["Idempotency-Key avval boshqa parametrlar bilan ishlatilgan (Payload mismatch)."]
                    ]);
                }

                // Return cached response
                return [
                    'order' => Order::with(['suborders.items', 'suborders.shipments'])->find($existingIdempotency->response_body['order_id']),
                    'is_replay' => true,
                    'response_data' => $existingIdempotency->response_body,
                ];
            }
        }

        // 2. Validate Items & Cart
        $itemsData = $payload['items'] ?? [];
        if (empty($itemsData)) {
            // Check if user has active cart items
            $cart = Cart::where('user_id', $user->id)->where('status', 'active')->latest()->first();
            if ($cart) {
                $cartItems = CartItem::where('cart_id', $cart->id)->get();
                foreach ($cartItems as $ci) {
                    $itemsData[] = [
                        'variant_id' => $ci->product_variant_id,
                        'quantity' => (float) $ci->quantity,
                    ];
                }
            }
        }

        if (empty($itemsData)) {
            throw new InvalidArgumentException("Buyurtma uchun mahsulotlar tanlanmagan.");
        }

        $shippingAddress = $payload['shipping_address'] ?? null;
        if (!$shippingAddress || empty($shippingAddress['address_line'])) {
            throw new InvalidArgumentException("Yetkazib berish manzili to'liq kiritilmagan.");
        }

        // 3. Server-side quote re-calculation (NEVER trust client prices!)
        $sellersGrouped = [];
        $overallItemsTotal = '0';
        $overallShippingTotal = '0';
        $variantsToReserve = [];

        foreach ($itemsData as $row) {
            $variantId = (int) $row['variant_id'];
            $qty = (string) $row['quantity'];

            $variant = ProductVariant::with(['product.seller', 'priceTiers'])->findOrFail($variantId);
            $product = $variant->product;
            $seller = $product->seller;

            // Recalculate price via PricingService
            $quote = $this->pricingService->calculateQuote($variant, $qty);

            $sellerId = $seller->id;
            if (!isset($sellersGrouped[$sellerId])) {
                $sellersGrouped[$sellerId] = [
                    'seller' => $seller,
                    'items' => [],
                    'items_subtotal' => '0',
                    'shipping_cost' => '30000', // 30,000 UZS standard base shipping
                ];
            }

            $sellersGrouped[$sellerId]['items'][] = [
                'variant' => $variant,
                'product' => $product,
                'quantity' => $qty,
                'unit_price' => $quote['unit_price'],
                'subtotal' => $quote['total_price'],
                'tier_id' => $quote['tier_id'],
            ];

            $sellersGrouped[$sellerId]['items_subtotal'] = bcadd(
                $sellersGrouped[$sellerId]['items_subtotal'],
                $quote['total_price'],
                0
            );

            $variantsToReserve[] = [
                'variant' => $variant,
                'quantity' => $qty,
            ];
        }

        // Calculate delivery per seller
        foreach ($sellersGrouped as &$group) {
            if (bccomp($group['items_subtotal'], '1000000', 0) >= 0) {
                $group['shipping_cost'] = '0'; // Free shipping above 1M UZS
            }
            $overallItemsTotal = bcadd($overallItemsTotal, $group['items_subtotal'], 0);
            $overallShippingTotal = bcadd($overallShippingTotal, $group['shipping_cost'], 0);
        }
        unset($group);

        $grandTotal = bcadd($overallItemsTotal, $overallShippingTotal, 0);

        // 4. Atomic Execution inside Database Transaction
        $createdOrder = DB::transaction(function () use (
            $user,
            $payload,
            $idempotencyKey,
            $shippingAddress,
            $sellersGrouped,
            $overallItemsTotal,
            $overallShippingTotal,
            $grandTotal,
            $variantsToReserve,
            $requestHash
        ) {
            // Generate unique Order Number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

            // A. Create Parent Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'status' => 'pending_payment',
                'currency' => 'UZS',
                'items_total' => (int) $overallItemsTotal,
                'shipping_total' => (int) $overallShippingTotal,
                'grand_total' => (int) $grandTotal,
                'shipping_address' => $shippingAddress,
                'idempotency_key' => $idempotencyKey,
                'notes' => $payload['notes'] ?? null,
            ]);

            // B. Reserve Stock with row locks for all items
            foreach ($variantsToReserve as $reserveItem) {
                $this->reservationService->reserveVariantStock(
                    $reserveItem['variant'],
                    $reserveItem['quantity'],
                    $order,
                    15 // 15-minute TTL
                );
            }

            // C. Create Suborders, Items, and Shipments
            foreach ($sellersGrouped as $sellerId => $sellerData) {
                $seller = $sellerData['seller'];
                $suborderNumber = 'SUB-' . $order->id . '-' . $sellerId . '-' . strtoupper(bin2hex(random_bytes(3)));

                $commRate = (float) ($seller->commission_rate ?? 5.0);
                $commAmount = bcmul(
                    $sellerData['items_subtotal'],
                    bcdiv((string)$commRate, '100', 4),
                    0
                );

                $suborderTotal = bcadd($sellerData['items_subtotal'], $sellerData['shipping_cost'], 0);

                $suborder = Suborder::create([
                    'order_id' => $order->id,
                    'seller_id' => $seller->id,
                    'suborder_number' => $suborderNumber,
                    'status' => 'pending',
                    'items_subtotal' => (int) $sellerData['items_subtotal'],
                    'shipping_cost' => (int) $sellerData['shipping_cost'],
                    'total_amount' => (int) $suborderTotal,
                    'commission_rate' => $commRate,
                    'commission_amount' => (int) $commAmount,
                    'notes' => null,
                ]);

                // Create Order Items snapshot
                foreach ($sellerData['items'] as $it) {
                    OrderItem::create([
                        'suborder_id' => $suborder->id,
                        'product_variant_id' => $it['variant']->id,
                        'product_id' => $it['product']->id,
                        'product_name' => $it['product']->name,
                        'variant_sku' => $it['variant']->sku,
                        'unit' => $it['product']->unit,
                        'quantity' => $it['quantity'],
                        'unit_price' => (int) $it['unit_price'],
                        'subtotal' => (int) $it['subtotal'],
                        'price_tier_id' => $it['tier_id'],
                        'attributes_snapshot' => $it['variant']->attributes ?? [],
                    ]);
                }

                // Create Shipment for this suborder
                $trackingNumber = 'TRK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
                Shipment::create([
                    'suborder_id' => $suborder->id,
                    'tracking_number' => $trackingNumber,
                    'courier_id' => null,
                    'status' => 'pending',
                    'shipping_method' => 'standard',
                    'shipping_cost' => (int) $sellerData['shipping_cost'],
                    'pickup_address' => [
                        'seller_id' => $seller->id,
                        'seller_name' => $seller->name,
                    ],
                    'delivery_address' => $shippingAddress,
                    'estimated_delivery_at' => now()->addDays(2),
                ]);
            }

            // Create initial Payment record
            $paymentMethod = $payload['payment_method'] ?? 'cash_on_delivery';
            $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

            \App\Models\Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'amount' => $order->grand_total,
                'currency' => $order->currency,
                'status' => 'pending',
                'payload_snapshot' => [
                    'source' => 'web_checkout',
                    'shipping_address' => $shippingAddress,
                ],
            ]);

            // If cash on delivery: confirm order immediately for packaging and courier delivery
            if ($paymentMethod === 'cash_on_delivery') {
                $order->update(['status' => 'processing']);
                foreach ($order->suborders as $sub) {
                    $sub->update(['status' => 'confirmed']);
                }
                \App\Models\InventoryReservation::where('order_id', $order->id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'committed']);
            }

            // D. Transactional Outbox Event
            OutboxEvent::create([
                'event_type' => 'OrderCreated',
                'aggregate_type' => 'Order',
                'aggregate_id' => $order->id,
                'payload' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                    'grand_total' => $order->grand_total,
                    'suborders_count' => count($sellersGrouped),
                ],
                'status' => 'pending',
            ]);

            // E. Convert active cart if applicable
            $cart = Cart::where('user_id', $user->id)->where('status', 'active')->first();
            if ($cart) {
                $cart->update(['status' => 'converted']);
            }

            // F. Save Idempotency Key cache if provided
            if ($idempotencyKey) {
                IdempotencyKey::create([
                    'key' => $idempotencyKey,
                    'user_id' => $user->id,
                    'request_hash' => $requestHash,
                    'response_code' => 201,
                    'response_body' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'grand_total' => $order->grand_total,
                    ],
                    'expires_at' => now()->addHours(24),
                ]);
            }

            return $order;
        });

        return [
            'order' => $createdOrder->load(['suborders.items', 'suborders.shipments']),
            'is_replay' => false,
            'response_data' => [
                'order_id' => $createdOrder->id,
                'order_number' => $createdOrder->order_number,
                'grand_total' => $createdOrder->grand_total,
            ],
        ];
    }
}
