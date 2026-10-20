<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Suborder;
use App\Services\Refund\RefundService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RefundController extends Controller
{
    public function __construct(
        protected RefundService $refundService
    ) {}

    /**
     * List refunds based on caller's role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Refund::with(['order', 'suborder.seller', 'user']);

        if ($user->hasRole('admin') || $user->hasRole('moderator')) {
            // Full platform access
        } elseif ($user->seller_id) {
            // Seller sees refunds for their suborders
            $query->whereHas('suborder', fn($q) => $q->where('seller_id', $user->seller_id));
        } else {
            // Regular customer sees only their own refunds
            $query->where('user_id', $user->id);
        }

        $refunds = $query->latest()->paginate(15);

        return ApiResponse::success($refunds);
    }

    /**
     * Customer requests a refund.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'suborder_id' => 'nullable|exists:suborders,id',
            'amount' => 'required|integer|min:1',
            'reason' => 'required|string|min:5|max:500',
        ]);

        $user = $request->user();
        $order = Order::with('suborders')->findOrFail($validated['order_id']);

        // Only the order owner or admin can request
        if ($order->user_id && $order->user_id !== $user->id && !$user->hasRole('admin')) {
            return ApiResponse::error("Ushbu buyurtmaga qaytarish so'rash huquqi yo'q.", 'UNAUTHORIZED_REFUND_REQUEST', [], 403);
        }

        $suborder = null;
        if (!empty($validated['suborder_id'])) {
            $suborder = Suborder::where('id', $validated['suborder_id'])
                ->where('order_id', $order->id)
                ->firstOrFail();
        }

        try {
            $refund = $this->refundService->requestRefund(
                $user,
                $order,
                $validated['amount'],
                $validated['reason'],
                $suborder
            );

            return ApiResponse::success($refund, "Qaytarish arizasi muvaffaqiyatli topshirildi.", 201);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'REFUND_REQUEST_FAILED', [], 422);
        }
    }

    /**
     * Moderator or Admin reviews a refund request.
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('admin') && !$user->hasRole('moderator')) {
            return ApiResponse::error("Faqat moderator yoki admin arizani ko'rib chiqishi mumkin.", 'FORBIDDEN', [], 403);
        }

        $validated = $request->validate([
            'approve' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $refund = Refund::findOrFail($id);

        try {
            $reviewed = $this->refundService->reviewRefund(
                $refund,
                $user,
                (bool) $validated['approve'],
                $validated['notes'] ?? null
            );

            return ApiResponse::success($reviewed, "Qaytarish arizasi holati yangilandi.");
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'REFUND_REVIEW_FAILED', [], 422);
        }
    }

    /**
     * Warehouse operator or seller confirms returned physical goods and restocks inventory.
     */
    public function restock(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $refund = Refund::findOrFail($id);

        // Check if user is admin, moderator, or seller belonging to the suborder
        $suborder = Suborder::findOrFail($refund->suborder_id);
        $isSeller = $user->seller_id && $user->seller_id === $suborder->seller_id;
        $isStaff = $user->hasRole('admin') || $user->hasRole('warehouse_operator');

        if (!$isSeller && !$isStaff) {
            return ApiResponse::error("Ombor qabulini tasdiqlash uchun vakolat yetarli emas.", 'FORBIDDEN', [], 403);
        }

        try {
            $result = $this->refundService->completeRefundWithGoodsRestock($refund, $user);
            return ApiResponse::success($result, "Qaytarilgan tovar omborga qabul qilindi va zaxiraga qo'shildi.");
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'REFUND_RESTOCK_FAILED', [], 422);
        }
    }
}
