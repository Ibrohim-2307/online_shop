<?php

use App\Http\Controllers\Web\AuthController as WebAuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/catalog', [StorefrontController::class, 'catalog'])->name('catalog');
Route::get('/products/{slug}', [StorefrontController::class, 'product'])->name('product.show');
Route::get('/sellers/{slug}', [StorefrontController::class, 'seller'])->name('seller.show');
Route::get('/wishlist', [StorefrontController::class, 'wishlist'])->name('wishlist');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart');
Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [StorefrontController::class, 'processCheckout'])->name('checkout.post');
Route::get('/orders/{orderNumber}/success', [StorefrontController::class, 'orderSuccess'])->name('order.success');

Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::get('/health', function () {
    $dbStatus = 'Ulangan (MySQL 8.0)';
    try {
        DB::select('SELECT 1');
    } catch (\Throwable $e) {
        $dbStatus = 'Ulanishda xatolik: ' . $e->getMessage();
    }

    $healthData = [
        'status' => 'healthy',
        'service' => 'Marketplace Web & API',
        'framework' => 'Laravel ' . app()->version(),
        'php' => PHP_VERSION,
        'database' => $dbStatus,
        'environment' => app()->environment(),
        'timezone' => config('app.timezone'),
        'timestamp' => now()->toIso8601String(),
    ];

    return view('health', compact('dbStatus', 'healthData'));
})->name('web.health');
