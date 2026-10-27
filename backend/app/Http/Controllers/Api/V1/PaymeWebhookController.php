<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymeWebhookController extends Controller
{
    public function __construct(
        protected PaymePaymentService $paymeService
    ) {}

    /**
     * Official Payme JSON-RPC 2.0 Webhook endpoint.
     */
    public function handle(Request $request): JsonResponse
    {
        $response = $this->paymeService->handleJsonRpc($request);

        return response()->json($response);
    }
}
