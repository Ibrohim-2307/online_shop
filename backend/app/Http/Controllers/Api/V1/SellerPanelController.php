<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\Seller;
use App\Models\SellerLedger;
use App\Models\SellerPayout;
use App\Models\Suborder;
use App\Services\Ledger\SellerLedgerService;
use App\Services\Rfq\RfqService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SellerPanelController extends Controller
{
    public function __construct(
        protected SellerLedgerService $ledgerService,
        protected RfqService $rfqService
    ) {}

    /**
     * Get orders/suborders for the authenticated seller.
     */
    public function orders(Request $request): JsonResponse
    {
        $sellerId = $request->user()->seller_id;
        if (!$sellerId) {
            return ApiResponse::error("Foydalanuvchi hech qanday do'konga biriktirilmagan.", 'NOT_A_SELLER', [], 403);
        }

        $suborders = Suborder::where('seller_id', $sellerId)
            ->with(['items.variant', 'order.user', 'shipments'])
            ->latest()
            ->paginate(15);

        return ApiResponse::success($suborders);
    }

    /**
     * Update suborder status (e.g. preparing, confirmed).
     */
    public function updateOrderStatus(Request $request, int $suborderId): JsonResponse
    {
        $sellerId = $request->user()->seller_id;
        $suborder = Suborder::where('id', $suborderId)->where('seller_id', $sellerId)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,preparing,shipped,cancelled',
        ]);

        $suborder->update(['status' => $validated['status']]);

        return ApiResponse::success($suborder, "Buyurtma holati yangilandi.");
    }

    /**
     * View RFQs sent to this seller store.
     */
    public function rfqs(Request $request): JsonResponse
    {
        $sellerId = $request->user()->seller_id;
        $rfqs = Rfq::where('seller_id', $sellerId)
            ->with(['user', 'product', 'variant', 'quotations'])
            ->latest()
            ->paginate(15);

        return ApiResponse::success($rfqs);
    }

    /**
     * Submit commercial quotation for an RFQ.
     */
    public function submitQuotation(Request $request, int $rfqId): JsonResponse
    {
        $sellerId = $request->user()->seller_id;
        $seller = Seller::findOrFail($sellerId);
        $rfq = Rfq::findOrFail($rfqId);

        if ($rfq->seller_id !== $seller->id) {
            return ApiResponse::error("Begona sotuvchiga yuborilgan RFQ ga taklif kiritib bo'lmaydi.", 'UNAUTHORIZED_RFQ_ACCESS', [], 403);
        }

        $validated = $request->validate([
            'unit_price' => 'required|integer|min:1',
            'shipping_cost' => 'nullable|integer|min:0',
            'is_shipping_negotiated' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $quotation = $this->rfqService->createQuotation($seller, $rfq, $validated);
            return ApiResponse::success($quotation, "Kotirovka muvaffaqiyatli taqdim etildi.", 201);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'QUOTATION_FAILED', [], 422);
        }
    }

    /**
     * View immutable double-entry ledger entries & net balance.
     */
    public function ledger(Request $request): JsonResponse
    {
        $sellerId = $request->user()->seller_id;
        $balance = $this->ledgerService->getSellerBalance($sellerId);

        $entries = SellerLedger::where('seller_id', $sellerId)
            ->latest()
            ->paginate(20);

        return ApiResponse::success([
            'net_balance' => $balance,
            'currency' => 'UZS',
            'entries' => $entries,
        ]);
    }

    /**
     * Request payout from seller balance.
     */
    public function requestPayout(Request $request): JsonResponse
    {
        $sellerId = $request->user()->seller_id;
        $seller = Seller::findOrFail($sellerId);

        $validated = $request->validate([
            'amount' => 'required|integer|min:10000',
            'bank_account' => 'required|string|min:10|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $eligibility = $this->ledgerService->canRequestPayout($seller, $validated['amount']);
        if (!$eligibility['allowed']) {
            return ApiResponse::error($eligibility['reason'], 'PAYOUT_NOT_ALLOWED', [], 422);
        }

        $payout = SellerPayout::create([
            'seller_id' => $seller->id,
            'amount' => $validated['amount'],
            'status' => 'requested',
            'bank_details' => ['bank_account' => $validated['bank_account']],
            'notes' => $validated['notes'] ?? null,
        ]);

        return ApiResponse::success($payout, "Mablag'ni yechib olish arizasi qabul qilindi.", 201);
    }

    /**
     * Multi-tenant export of products or orders.
     * Guaranteed: Seller A CANNOT export or see Seller B's data under any condition.
     */
    public function export(Request $request, string $type): JsonResponse
    {
        $authenticatedSellerId = $request->user()->seller_id;

        // If client sends explicit seller_id query param and it does not match, reject with 403
        $requestedSellerId = $request->query('seller_id');
        if ($requestedSellerId && (int) $requestedSellerId !== (int) $authenticatedSellerId && !$request->user()->hasRole('admin')) {
            return ApiResponse::error(
                "Siz boshqa sotuvchining ma'lumotlarini eksport qila olmaysiz.",
                'TENANT_ISOLATION_VIOLATION',
                [],
                403
            );
        }

        if ($type === 'products') {
            $data = Product::where('seller_id', $authenticatedSellerId)
                ->with(['variants.priceTiers', 'category'])
                ->get();
        } elseif ($type === 'orders') {
            $data = Suborder::where('seller_id', $authenticatedSellerId)
                ->with(['items', 'order'])
                ->get();
        } else {
            return ApiResponse::error("Noto'g'ri eksport turi.", 'INVALID_EXPORT_TYPE', [], 400);
        }

        return ApiResponse::success([
            'seller_id' => $authenticatedSellerId,
            'type' => $type,
            'count' => $data->count(),
            'records' => $data,
        ], "Ma'lumotlar muvaffaqiyatli eksport qilindi.");
    }
}
