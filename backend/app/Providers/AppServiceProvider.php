<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Seller;
use App\Policies\AddressPolicy;
use App\Policies\SellerPolicy;
use App\Services\Auth\DemoOtpService;
use App\Services\Auth\OtpServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        Gate::policy(\App\Models\Warehouse::class, \App\Policies\WarehousePolicy::class);

        // Security requirement: Rate limiters for API, Auth and OTP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('phone') ?: $request->ip());
        });
    }
}
