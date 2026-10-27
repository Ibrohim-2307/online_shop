<?php

use App\Http\Controllers\Web\AuthController as WebAuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/catalog', [StorefrontController::class, 'catalog'])->name('catalog');
Route::get('/search/live', [StorefrontController::class, 'liveSearch'])->name('search.live');
Route::get('/products/{slug}', [StorefrontController::class, 'product'])->name('product.show');
Route::post('/products/{slug}/reviews', [StorefrontController::class, 'storeReview'])->name('product.reviews.store');
Route::get('/sellers/{slug}', [StorefrontController::class, 'seller'])->name('seller.show');
Route::get('/wishlist', [\App\Http\Controllers\Web\WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist/toggle/{productId}', [\App\Http\Controllers\Web\WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/sync', [\App\Http\Controllers\Web\WishlistController::class, 'sync'])->name('wishlist.sync');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart');
Route::post('/cart/items', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/items/{id}', [StorefrontController::class, 'updateCartItem'])->name('cart.update');
Route::delete('/cart/items/{id}', [StorefrontController::class, 'removeCartItem'])->name('cart.remove');
Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [StorefrontController::class, 'processCheckout'])->name('checkout.post');
Route::get('/orders/{orderNumber}/success', [StorefrontController::class, 'orderSuccess'])->name('order.success');
Route::get('/orders', [\App\Http\Controllers\Web\OrderHubController::class, 'index'])->name('orders.hub');
Route::get('/orders/{orderNumber}', [\App\Http\Controllers\Web\OrderHubController::class, 'show'])->name('orders.detail');
Route::get('/tracking/{trackingNumber?}', [StorefrontController::class, 'tracking'])->name('tracking');

// Customer Service, Legal & Policy Pages
Route::view('/delivery', 'storefront.delivery')->name('delivery');
Route::view('/returns', 'storefront.returns')->name('returns');
Route::view('/privacy', 'storefront.privacy')->name('privacy');
Route::view('/contact', 'storefront.contact')->name('contact');

// Payments (Click, Payme, Cash on Delivery, Uzum)
Route::get('/payments/checkout/{orderNumber}', [\App\Http\Controllers\Web\PaymentWebController::class, 'checkout'])->name('payment.checkout');
Route::post('/payments/pay/{orderNumber}', [\App\Http\Controllers\Web\PaymentWebController::class, 'pay'])->name('payment.pay');

// Unified Customer, Seller, and Courier Profile & Control Center
Route::get('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'index'])->name('profile');
Route::post('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/password', [\App\Http\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.password');
Route::post('/profile/addresses', [\App\Http\Controllers\Web\ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
Route::delete('/profile/addresses/{id}', [\App\Http\Controllers\Web\ProfileController::class, 'deleteAddress'])->name('profile.addresses.delete');
Route::post('/profile/addresses/{id}/default', [\App\Http\Controllers\Web\ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');

// Seller actions inside Profile
Route::post('/profile/seller/products', [\App\Http\Controllers\Web\ProfileController::class, 'storeSellerProduct'])->name('profile.seller.products.store');
Route::post('/profile/seller/products/{id}/status', [\App\Http\Controllers\Web\ProfileController::class, 'updateSellerProductStatus'])->name('profile.seller.products.status');
Route::post('/profile/seller/orders/{id}/status', [\App\Http\Controllers\Web\ProfileController::class, 'updateSellerOrderStatus'])->name('profile.seller.orders.status');

// Courier actions inside Profile
Route::post('/profile/courier/shipments/{id}/status', [\App\Http\Controllers\Web\ProfileController::class, 'updateCourierShipmentStatus'])->name('profile.courier.shipments.status');

Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Installation routes
Route::get('/install', [\App\Http\Controllers\Install\InstallerController::class, 'show'])->name('install.show');
Route::post('/install', [\App\Http\Controllers\Install\InstallerController::class, 'install'])->name('install.process');

// Superadmin & Admin Web Dashboard
Route::get('/admin', [\App\Http\Controllers\Web\AdminDashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/superadmin', [\App\Http\Controllers\Web\AdminDashboardController::class, 'index'])->name('superadmin.dashboard');
Route::post('/admin/sellers/{id}/status', [\App\Http\Controllers\Web\AdminDashboardController::class, 'updateSellerStatus'])->name('admin.sellers.status');
Route::post('/admin/users/{id}/status', [\App\Http\Controllers\Web\AdminDashboardController::class, 'updateUserStatus'])->name('admin.users.status');
Route::post('/admin/users/{id}/role', [\App\Http\Controllers\Web\AdminDashboardController::class, 'updateUserRole'])->name('admin.users.role');
Route::post('/admin/products/{id}/status', [\App\Http\Controllers\Web\AdminDashboardController::class, 'updateProductStatus'])->name('admin.products.status');
Route::post('/admin/shipments/{id}/assign', [\App\Http\Controllers\Web\AdminDashboardController::class, 'assignCourier'])->name('admin.shipments.assign');

// Seller Web Dashboard
Route::get('/seller', [\App\Http\Controllers\Web\SellerDashboardController::class, 'index'])->name('seller.dashboard');
Route::post('/seller/products', [\App\Http\Controllers\Web\SellerDashboardController::class, 'storeProduct'])->name('seller.products.store');

Route::get('/health', function () {
    $dbStatus = 'Ulangan (' . config('database.default') . ')';
    $isHealthy = true;
    try {
        DB::select('SELECT 1');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("Web Health check DB error: " . $e->getMessage());
        $isHealthy = false;
        $dbStatus = app()->environment('local', 'testing')
            ? ('Ulanishda xatolik: ' . $e->getMessage())
            : "Ma'lumotlar bazasiga ulanib bo'lmadi (Aloqa uzilgan)";
    }

    $healthData = [
        'status' => $isHealthy ? 'healthy' : 'degraded',
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
