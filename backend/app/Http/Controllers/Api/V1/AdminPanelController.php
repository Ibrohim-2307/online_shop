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

        return DB::transaction(function () use ($payout, $validated, $user) {
            $payout->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $payout->notes,
            ]);

            // If completed, record ledger debit
            if ($validated['status'] === 'completed') {
                $credits = SellerLedger::where('seller_id', $payout->seller_id)->where('type', 'credit')->sum('amount');
                $debits = SellerLedger::where('seller_id', $payout->seller_id)->where('type', 'debit')->sum('amount');
                $balance = $credits - $debits;

                SellerLedger::create([
                    'seller_id' => $payout->seller_id,
                    'type' => 'debit',
                    'entry_type' => 'payout',
                    'amount' => $payout->amount,
                    'currency' => 'UZS',
                    'running_balance' => $balance - $payout->amount,
                    'reference' => 'PAYOUT-' . $payout->id,
                    'notes' => "Sotuvchiga mablag' to'lab berildi: Payout #{$payout->id}",
                    'created_at' => now(),
                ]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'payout.reviewed',
                'entity_type' => 'SellerPayout',
                'entity_id' => $payout->id,
                'old_values' => ['status' => 'requested'],
                'new_values' => ['status' => $validated['status']],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return ApiResponse::success($payout, "Payout holati muvaffaqiyatli yangilandi.");
        });
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
