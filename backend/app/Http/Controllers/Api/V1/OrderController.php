<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->with(['suborders.items', 'suborders.seller', 'suborders.shipments'])
            ->latest()
            ->paginate(15);

        return ApiResponse::success($orders);
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->with(['suborders.items.variant', 'suborders.seller', 'suborders.shipments'])
            ->firstOrFail();

        return ApiResponse::success($order);
    }
}
