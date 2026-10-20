<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session_id');

        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
        $quote = $this->cartService->getCartQuote($cart);

        return ApiResponse::success([
            'cart_id' => $cart->id,
            'session_id' => $cart->session_id,
            'quote' => $quote,
        ]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session_id');

        try {
            $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
            $item = $this->cartService->addItem($cart, $validated['variant_id'], $validated['quantity']);
            $quote = $this->cartService->getCartQuote($cart);

            return ApiResponse::success([
                'cart_item_id' => $item->id,
                'quote' => $quote,
            ], 'Mahsulot savatchaga qo\'shildi.', 201);
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'CART_ADD_ERROR', null, 422);
        }
    }

    public function updateItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session_id');

        try {
            $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
            $item = $this->cartService->updateItem($cart, $id, $validated['quantity']);
            $quote = $this->cartService->getCartQuote($cart);

            return ApiResponse::success([
                'cart_item_id' => $item->id,
                'quote' => $quote,
            ], 'Savatcha yangilandi.');
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage(), 'CART_UPDATE_ERROR', null, 422);
        }
    }

    public function removeItem(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session_id');

        $cart = $this->cartService->getCartForUserOrSession($user, $sessionId);
        $this->cartService->removeItem($cart, $id);
        $quote = $this->cartService->getCartQuote($cart);

        return ApiResponse::success([
            'quote' => $quote,
        ], 'Mahsulot savatchadan o\'chirildi.');
    }
}
