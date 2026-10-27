<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentAdapterInterface;
use InvalidArgumentException;

class PaymentManager
{
    public function __construct(
        protected ClickPaymentService $clickService,
        protected PaymePaymentService $paymeService,
        protected DemoPaymentAdapter $demoAdapter
    ) {}

    public function driver(?string $name = null): PaymentAdapterInterface
    {
        $name = strtolower($name ?: config('services.payment.default', 'click'));

        return match ($name) {
            'click' => $this->clickService,
            'payme' => $this->paymeService,
            'demo', 'demo_sandbox' => $this->demoAdapter,
            default => throw new InvalidArgumentException("Noma'lum to'lov provayderi: {$name}"),
        };
    }

    public function isProviderConfigured(string $provider): bool
    {
        return match (strtolower($provider)) {
            'click' => $this->clickService->isConfigured(),
            'payme' => $this->paymeService->isConfigured(),
            'demo', 'demo_sandbox' => !app()->environment('production') && (app()->environment('testing', 'local') || config('services.payment.allow_demo_settlement', false)),
            'cash_on_delivery' => true,
            default => false,
        };
    }

    public function click(): ClickPaymentService
    {
        return $this->clickService;
    }

    public function payme(): PaymePaymentService
    {
        return $this->paymeService;
    }

    public function demo(): DemoPaymentAdapter
    {
        return $this->demoAdapter;
    }
}
