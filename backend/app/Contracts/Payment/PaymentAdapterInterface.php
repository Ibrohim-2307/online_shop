<?php

namespace App\Contracts\Payment;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentAdapterInterface
{
    /**
     * Get unique payment provider code (e.g., payme, click, uzum, demo).
     */
    public function getProviderName(): string;

    /**
     * Initialize payment session or invoice for an order.
     */
    public function initializePayment(Order $order, array $options = []): array;

    /**
     * Verify payment status or incoming webhook notification.
     */
    public function verifyPayment(Request $request): array;

    /**
     * Process full or partial refund.
     */
    public function processRefund(Order $order, int $amount, string $reason): array;
}
