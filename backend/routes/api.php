<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SellerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    // Public authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/otp/send', [AuthController::class, 'otpSend']);
        Route::post('/otp/verify', [AuthController::class, 'otpVerify']);

        // Authenticated auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Authenticated API resources
    Route::middleware('auth:sanctum')->group(function () {

        // Addresses CRUD (ownership strictly enforced by AddressPolicy)
        Route::apiResource('addresses', AddressController::class);

        // Multi-tenant Seller routes (strictly isolated per seller store)
        Route::prefix('seller')->middleware('role:seller_owner,seller_manager,seller_operator,admin')->group(function () {
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
