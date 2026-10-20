<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\RfqQuotation;
use App\Services\Rfq\RfqService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RfqController extends Controller
{
    public function __construct(
        protected RfqService $rfqService
    ) {}

    /**
     * Customer submits a Request For Quotation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'nullable|string',
            'target_price' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $rfq = $this->rfqService->createRfq($request->user(), $validated);
            return ApiResponse::success($rfq, "RFQ so'rovi muvaffaqiyatli yuborildi.", 201);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'RFQ_CREATION_FAILED', [], 422);
        }
    }

    /**
     * Customer accepts commercial quotation.
     */
    public function acceptQuotation(Request $request, int $quotationId): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|array',
            'shipping_address.recipient_name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address' => 'required|string',
        ]);

        $quotation = RfqQuotation::with(['rfq', 'seller'])->findOrFail($quotationId);

        try {
            $order = $this->rfqService->acceptQuotation($request->user(), $quotation, $validated['shipping_address']);
            return ApiResponse::success($order, "Kotirovka qabul qilindi va buyurtma shakllantirildi.", 201);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'QUOTATION_ACCEPTANCE_FAILED', [], 422);
        }
    }
}
