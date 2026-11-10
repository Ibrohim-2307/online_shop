<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Refund;
use App\Models\SellerLedger;
use App\Models\SellerPayout;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdminPanelController extends Controller
{
    /**
     * Admin reviews and approves/rejects seller payout request.
     * STRICT SECURITY RULE: Support or moderator role CANNOT approve payouts. ONLY admin can.
     */
    public function reviewPayout(Request $request, int $payoutId): JsonResponse
    {
        $user = $request->user();

        // Strict role check: Only admin can approve/process financial payouts
        if (!$user->hasRole('admin')) {
            return ApiResponse::error(
                "Mablag' yechishni tasdiqlash uchun administrator (admin) huquqi talab etiladi. Support yoki moderator buni bajara olmaydi.",
                'INSUFFICIENT_PAYOUT_PERMISSION',
                [],
                403
            );
        }

        $validated = $request->validate([
            'status' => 'required|string|in:processing,completed,rejected',
            'notes' => 'nullable|string|max:255',
        ]);

        $payout = SellerPayout::findOrFail($payoutId);

        try {
            $payout = app(\App\Services\Ledger\SellerLedgerService::class)
                ->reviewPayout($payout, $validated['status'], $validated['notes'] ?? null, $user);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'PAYOUT_REVIEW_FAILED', [], 422);
        }

        return ApiResponse::success($payout, "Payout holati muvaffaqiyatli yangilandi.");
    }

    /**
     * List all marketing banners.
     */
    public function banners(Request $request): JsonResponse
    {
        $banners = Banner::orderBy('sort_order')->latest()->get();
        return ApiResponse::success($banners);
    }

    /**
     * Store new marketing banner.
     */
    public function storeBanner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'image_url' => 'required|url',
            'link_url' => 'nullable|string',
            'position' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $banner = Banner::create($validated);
        return ApiResponse::success($banner, "Banner muvaffaqiyatli saqlandi.", 201);
    }

    /**
     * List active disputes and refund requests.
     */
    public function disputes(Request $request): JsonResponse
    {
        $disputes = Refund::with(['order.user', 'suborder.seller', 'moderator'])
            ->latest()
            ->paginate(20);

        return ApiResponse::success($disputes);
    }

    /**
     * View audit log trail.
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $logs = AuditLog::with('user:id,name,email')
            ->latest()
            ->paginate(30);

        return ApiResponse::success($logs);
    }
}
