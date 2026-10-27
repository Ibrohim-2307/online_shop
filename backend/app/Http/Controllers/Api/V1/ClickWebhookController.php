<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payment\ClickPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickWebhookController extends Controller
{
    public function __construct(
        protected ClickPaymentService $clickService
    ) {}

    /**
     * Official Click Webhook endpoint (Prepare & Complete).
     */
    public function handle(Request $request): JsonResponse
    {
        $response = $this->clickService->handleCallback($request);

        return response()->json($response);
    }
}
