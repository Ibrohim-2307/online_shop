<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RefundController;
use App\Http\Controllers\Api\V1\SellerCatalogController;
use App\Http\Controllers\Api\V1\SellerController;
use App\Http\Controllers\Api\V1\SellerInventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    // Public catalog & product endpoints
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/products', [CatalogController::class, 'products']);
    Route::get('/products/{slugOrId}', [CatalogController::class, 'show']);
    Route::post('/products/{variantId}/quote', [CatalogController::class, 'calculateQuote']);

    // Public / Session-based Cart & Quote
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::post('/checkout/quote', [CheckoutController::class, 'quote']);

    // Payments public webhook (HMAC signature validated inside controller/adapter)
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // Public authentication routes (with rate limiting)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
        Route::post('/mfa/verify', [AuthController::class, 'verifyAdminMfa'])->middleware('throttle:auth');
        Route::post('/otp/send', [AuthController::class, 'otpSend'])->middleware('throttle:otp');
        Route::post('/otp/verify', [AuthController::class, 'otpVerify'])->middleware('throttle:otp');

        // Password reset routes (time-limited, single-use token)
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth');
        Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:auth');

        // Authenticated auth routes
        Route::middleware(['auth:sanctum', 'active'])->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Authenticated API resources
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        // Addresses CRUD (ownership strictly enforced by AddressPolicy)
        Route::apiResource('addresses', AddressController::class);

        // Checkout & Orders
        Route::post('/checkout', [CheckoutController::class, 'checkout']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);

        // Payments
        Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
        Route::get('/payments/status/{orderId}', [PaymentController::class, 'status']);

        // Refunds
        Route::get('/refunds', [RefundController::class, 'index']);
        Route::post('/refunds', [RefundController::class, 'store']);
        Route::post('/refunds/{id}/review', [RefundController::class, 'review']);
        Route::post('/refunds/{id}/restock', [RefundController::class, 'restock']);

        // Multi-tenant Seller routes (strictly isolated per seller store)
        Route::prefix('seller')->middleware('role:seller_owner,seller_manager,seller_operator,admin')->group(function () {
            // Profile & members
            Route::get('/profile', [SellerController::class, 'profile']);
            Route::put('/profile', [SellerController::class, 'updateProfile']);
            Route::get('/members', [SellerController::class, 'members']);
            Route::post('/members', [SellerController::class, 'addMember']);
            Route::delete('/members/{memberId}', [SellerController::class, 'removeMember']);

            // Explicit seller tenant routes
            Route::get('/{sellerId}/profile', [SellerController::class, 'profile']);
            Route::put('/{sellerId}/profile', [SellerController::class, 'updateProfile']);
            Route::get('/{sellerId}/members', [SellerController::class, 'members']);
            Route::post('/{sellerId}/members', [SellerController::class, 'addMember']);
            Route::delete('/{sellerId}/members/{memberId}', [SellerController::class, 'removeMember']);

            // Catalog & Products management (strictly isolated per seller)
            Route::get('/products', [SellerCatalogController::class, 'index']);
            Route::post('/products', [SellerCatalogController::class, 'store']);
            Route::get('/products/{id}', [SellerCatalogController::class, 'show']);
            Route::put('/products/{id}', [SellerCatalogController::class, 'update']);
            Route::delete('/products/{id}', [SellerCatalogController::class, 'destroy']);
            Route::post('/products/{productId}/variants', [SellerCatalogController::class, 'storeVariant']);
            Route::post('/variants/{variantId}/price-tiers', [SellerCatalogController::class, 'setPriceTiers']);

            // Warehouses & Inventory management (strictly isolated per seller)
            Route::get('/warehouses', [SellerInventoryController::class, 'warehouses']);
            Route::post('/warehouses', [SellerInventoryController::class, 'storeWarehouse']);
            Route::get('/warehouses/{id}', [SellerInventoryController::class, 'showWarehouse']);
            Route::put('/warehouses/{id}', [SellerInventoryController::class, 'updateWarehouse']);
            Route::post('/inventory/adjust', [SellerInventoryController::class, 'adjustStock']);
            Route::get('/inventory/movements', [SellerInventoryController::class, 'movements']);
        });

        // Administrator and Moderator routes
        Route::prefix('admin')->middleware('role:admin,moderator')->group(function () {
            Route::get('/users', [AdminController::class, 'users']);
            Route::put('/users/{id}/status', [AdminController::class, 'updateUserStatus'])->middleware('role:admin');
            Route::get('/sellers', [AdminController::class, 'sellers']);
            Route::put('/sellers/{id}/status', [AdminController::class, 'updateSellerStatus'])->middleware('role:admin');
        });
    });
});
