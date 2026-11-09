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

        $query = Order::where('user_id', $user->id)
            ->with(['suborders.items', 'suborders.seller', 'suborders.shipments'])
            ->latest();

        if ($request->filled('status') && $request->query('status') !== 'all') {
            $status = $request->query('status');
            if ($status === 'in_transit') {
                $query->where(function ($q) {
                    $q->whereIn('status', ['processing', 'pending', 'pending_payment', 'in_transit'])
                      ->orWhereHas('suborders.shipments', fn($sq) => $sq->where('status', 'in_transit'));
                });
            } elseif ($status === 'completed') {
                $query->whereIn('status', ['completed', 'delivered']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $term = trim($request->query('search'));
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                  ->orWhereHas('suborders.items', fn($iq) => $iq->where('product_name', 'like', "%{$term}%"))
                  ->orWhereHas('suborders.seller', fn($sq) => $sq->where('name', 'like', "%{$term}%"));
            });
        }

        $orders = $query->paginate($request->integer('per_page', 15));

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
