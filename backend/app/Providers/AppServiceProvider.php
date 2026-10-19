<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Seller;
use App\Policies\AddressPolicy;
use App\Policies\SellerPolicy;
use App\Services\Auth\DemoOtpService;
use App\Services\Auth\OtpServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OtpServiceInterface::class, function () {
            return new DemoOtpService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Address::class, AddressPolicy::class);
        Gate::policy(Seller::class, SellerPolicy::class);
    }
}
