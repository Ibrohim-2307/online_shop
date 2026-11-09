<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected CartService $cartService
    ) {}

    /**
     * Get live server quote before ordering.
     */
    public function quote(Request $request): JsonResponse
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session_id');

        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
        $quote = $this->cartService->getCartQuote($cart);

        return ApiResponse::success([
            'quote' => $quote,
        ]);
    }

    /**
     * Process reliable atomic checkout with Idempotency-Key support.
     */
    public function checkout(Request $request): JsonResponse
    {
        // Normalize payload for mobile / Flutter app compatibility
        if (!$request->has('shipping_address') && ($request->filled('address') || $request->filled('address_line'))) {
            $addrLine = $request->input('address') ?: $request->input('address_line');
            $request->merge([
                'shipping_address' => [
                    'recipient_name' => $request->input('recipient_name', $request->user()?->name ?? 'Xaridor'),
                    'phone' => $request->input('phone', $request->user()?->phone ?? '+998901234567'),
                    'region' => $request->input('region', 'Toshkent'),
                    'city' => $request->input('city', 'Toshkent shahri'),
                    'address_line' => $addrLine,
                ],
            ]);
        }

        if ($request->has('items') && is_array($request->input('items'))) {
            $items = array_map(function ($item) {
                if (isset($item['product_variant_id']) && !isset($item['variant_id'])) {
                    $item['variant_id'] = $item['product_variant_id'];
                }
                return $item;
            }, $request->input('items'));
            $request->merge(['items' => $items]);
        }

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.variant_id' => 'required_with:items|integer|exists:product_variants,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.001',
            'shipping_address' => 'required|array',
            'shipping_address.recipient_name' => 'required|string|max:100',
            'shipping_address.phone' => 'required|string|max:20',
            'shipping_address.region' => 'nullable|string|max:50',
            'shipping_address.city' => 'nullable|string|max:50',
            'shipping_address.address_line' => 'required|string|max:255',
            'payment_method' => 'nullable|string|in:cash_on_delivery,nasiya,click,payme,card,wallet',
            'nasiya_months' => 'nullable|integer|in:3,6,12,24',
            'notes' => 'nullable|string|max:500',
        ]);

        if (empty($validated['shipping_address']['region'])) {
            $validated['shipping_address']['region'] = 'Toshkent';
        }
        if (empty($validated['shipping_address']['city'])) {
            $validated['shipping_address']['city'] = 'Toshkent shahri';
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');

        try {
            $result = $this->checkoutService->processCheckout(
                $request->user(),
                $validated,
                $idempotencyKey
            );

            $order = $result['order'];

            return ApiResponse::success([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'currency' => $order->currency,
                'items_total' => $order->items_total,
                'shipping_total' => $order->shipping_total,
                'grand_total' => $order->grand_total,
                'suborders' => $order->suborders->map(function ($s) {
                    return [
                        'suborder_id' => $s->id,
                        'suborder_number' => $s->suborder_number,
                        'seller_id' => $s->seller_id,
                        'status' => $s->status,
                        'items_subtotal' => $s->items_subtotal,
                        'shipping_cost' => $s->shipping_cost,
                        'total_amount' => $s->total_amount,
                        'items_count' => $s->items->count(),
                        'shipments' => $s->shipments->map(fn($sh) => [
                            'tracking_number' => $sh->tracking_number,
                            'status' => $sh->status,
                        ]),
                    ];
                }),
                'is_replay' => $result['is_replay'],
            ], $result['is_replay'] ? 'Avval yaratilgan buyurtma qaytarildi.' : 'Buyurtma muvaffaqiyatli qabul qilindi.', $result['is_replay'] ? 200 : 201);
        } catch (ValidationException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                'IDEMPOTENCY_PAYLOAD_MISMATCH',
                $e->errors(),
                422
            );
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'CHECKOUT_ERROR', null, 422);
        }
    }
}
