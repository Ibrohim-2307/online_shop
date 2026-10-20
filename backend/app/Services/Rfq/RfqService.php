<?php

namespace App\Services\Rfq;

use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Rfq;
use App\Models\RfqQuotation;
use App\Models\Seller;
use App\Models\Suborder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RfqService
{
    /**
     * Customer creates a new RFQ (Request For Quotation).
     */
    public function createRfq(User $user, array $data): Rfq
    {
        $product = Product::findOrFail($data['product_id']);
        $variant = ProductVariant::findOrFail($data['product_variant_id']);

        if ($variant->product_id !== $product->id) {
            throw new InvalidArgumentException("Tanlangan SKU varianti ushbu mahsulotga tegishli emas.");
        }

        $quantity = (float) $data['quantity'];
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Miqdor 0 dan katta bo'lishi shart.");
        }

        return Rfq::create([
            'user_id' => $user->id,
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit' => $data['unit'] ?? $product->unit,
            'target_price' => $data['target_price'] ?? null,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'valid_until' => now()->addDays(7),
        ]);
    }

    /**
     * Seller submits a commercial quotation with customized pricing, shipping, and expiry.
     */
    public function createQuotation(Seller $seller, Rfq $rfq, array $data): RfqQuotation
    {
        if ($rfq->seller_id !== $seller->id) {
            throw new InvalidArgumentException("Sotuvchi faqat o'ziga yuborilgan RFQ bo'yicha kotirovka bera oladi.");
        }

        if ($rfq->status !== 'pending' && $rfq->status !== 'quoted') {
            throw new InvalidArgumentException("Ushbu RFQ yangi kotirovka qabul qilmaydi (Joriy holat: {$rfq->status}).");
        }

        $unitPrice = (int) $data['unit_price'];
        $shippingCost = (int) ($data['shipping_cost'] ?? 0);
        $isShippingNegotiated = (bool) ($data['is_shipping_negotiated'] ?? false);

        // Ton delivery freight requirement
        if ($rfq->unit === 'ton' && !$isShippingNegotiated && $shippingCost <= 0) {
            throw new InvalidArgumentException("Tonnalik yukda yetkazish xarajati va shartlari aniq kelishilishi shart.");
        }

        $itemsTotal = (int) round(bcmul((string) $unitPrice, (string) $rfq->quantity, 0));
        $totalPrice = $itemsTotal + $shippingCost;

        $expiresAt = isset($data['expires_at']) ? \Carbon\Carbon::parse($data['expires_at']) : now()->addDays(3);

        $quotation = RfqQuotation::create([
            'rfq_id' => $rfq->id,
            'seller_id' => $seller->id,
            'unit_price' => $unitPrice,
            'shipping_cost' => $shippingCost,
            'is_shipping_negotiated' => $isShippingNegotiated,
            'total_price' => $totalPrice,
            'status' => 'offered',
            'expires_at' => $expiresAt,
            'notes' => $data['notes'] ?? null,
        ]);

        $rfq->update(['status' => 'quoted']);

        return $quotation;
    }

    /**
     * Customer accepts quotation: re-verifies stock atomically and generates order.
     */
    public function acceptQuotation(User $user, RfqQuotation $quotation, array $shippingAddress): Order
    {
        $rfq = $quotation->rfq;

        if ($rfq->user_id !== $user->id) {
            throw new InvalidArgumentException("Faqat so'rov egasi kotirovkani qabul qila oladi.");
        }

        if ($quotation->status !== 'offered') {
            throw new InvalidArgumentException("Kotirovka qabul qilish holatida emas (Joriy holat: {$quotation->status}).");
        }

        if (now()->isAfter($quotation->expires_at)) {
            $quotation->update(['status' => 'expired']);
            throw new InvalidArgumentException("Kotirovkaning amal qilish muddati tugagan.");
        }

        // Atomic stock verification and Order generation
        return DB::transaction(function () use ($user, $quotation, $rfq, $shippingAddress) {
            $warehouse = Warehouse::where('seller_id', $quotation->seller_id)
                ->where('is_active', true)
                ->first();

            if (!$warehouse) {
                throw new InvalidArgumentException("Sotuvchining faol ombori topilmadi.");
            }

            // Lock inventory balance row
            $balance = InventoryBalance::where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $rfq->product_variant_id)
                ->lockForUpdate()
                ->first();

            $availableQty = $balance ? (float) $balance->quantity : 0.0;
            if ($availableQty < (float) $rfq->quantity) {
                throw new InvalidArgumentException(
                    "Omborda so'ralgan miqdorda mahsulot yetarli emas. Mavjud: {$availableQty} {$rfq->unit}, So'ralgan: {$rfq->quantity} {$rfq->unit}."
                );
            }

            $orderNumber = 'ORD-RFQ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $suborderNumber = 'SUB-RFQ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $itemsTotal = (int) round(bcmul((string) $quotation->unit_price, (string) $rfq->quantity, 0));
            $grandTotal = $itemsTotal + $quotation->shipping_cost;

            $seller = Seller::findOrFail($quotation->seller_id);
            $commissionRate = (float) $seller->commission_rate;
            $commissionAmount = (int) round(($itemsTotal * $commissionRate) / 100);

            // 1. Create Parent Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending_payment',
                'currency' => 'UZS',
                'items_total' => $itemsTotal,
                'shipping_total' => $quotation->shipping_cost,
                'grand_total' => $grandTotal,
                'shipping_address' => $shippingAddress,
                'notes' => "RFQ #{$rfq->id} orqali yaratilgan buyurtma",
            ]);

            // 2. Create Suborder
            $suborder = Suborder::create([
                'order_id' => $order->id,
                'seller_id' => $quotation->seller_id,
                'suborder_number' => $suborderNumber,
                'status' => 'pending',
                'items_subtotal' => $itemsTotal,
                'shipping_cost' => $quotation->shipping_cost,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'total_amount' => $grandTotal,
                'notes' => "RFQ Quotation #{$quotation->id}",
            ]);

            // 3. Create Order Item Snapshot
            $product = $rfq->product;
            $variant = $rfq->variant;

            OrderItem::create([
                'suborder_id' => $suborder->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'variant_sku' => $variant->sku,
                'unit' => $rfq->unit,
                'quantity' => $rfq->quantity,
                'unit_price' => $quotation->unit_price,
                'subtotal' => $itemsTotal,
            ]);

            // Mark Quotation and RFQ accepted
            $quotation->update(['status' => 'accepted']);
            $rfq->update(['status' => 'accepted']);

            return $order;
        });
    }

    /**
     * Guard: checks if order has unnegotiated ton freight shipping.
     * Blocks payment initiation if unit is 'ton' and shipping has not been negotiated.
     */
    public function validateFreightShippingForPayment(Order $order): void
    {
        foreach ($order->suborders as $suborder) {
            foreach ($suborder->items as $item) {
                if ($item->unit === 'ton') {
                    // Check if there is an accepted quotation with negotiated shipping
                    $hasNegotiatedShipping = RfqQuotation::where('seller_id', $suborder->seller_id)
                        ->where('status', 'accepted')
                        ->where('is_shipping_negotiated', true)
                        ->exists();

                    if (!$hasNegotiatedShipping && (int) $suborder->shipping_cost === 0) {
                        throw new InvalidArgumentException(
                            "Tonnaviy yuk yetkazish narxi kelishilmagan. Avval yetkazish kotirovkasi tasdiqlanishi kerak."
                        );
                    }
                }
            }
        }
    }
}
