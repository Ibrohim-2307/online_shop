<?php

use App\Http\Controllers\Web\AuthController as WebAuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/catalog', [StorefrontController::class, 'catalog'])->name('catalog');
Route::get('/search/live', [StorefrontController::class, 'liveSearch'])->name('search.live');
Route::get('/products/{slug}', [StorefrontController::class, 'product'])->name('product.show');
Route::post('/products/{slug}/reviews', [StorefrontController::class, 'storeReview'])->middleware('throttle:10,1')->name('product.reviews.store');
Route::get('/sellers/{slug}', [StorefrontController::class, 'seller'])->name('seller.show');
Route::get('/wishlist', [\App\Http\Controllers\Web\WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist/toggle/{productId}', [\App\Http\Controllers\Web\WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/sync', [\App\Http\Controllers\Web\WishlistController::class, 'sync'])->name('wishlist.sync');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('cart');
Route::post('/cart/items', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/items/{id}', [StorefrontController::class, 'updateCartItem'])->name('cart.update');
Route::delete('/cart/items/{id}', [StorefrontController::class, 'removeCartItem'])->name('cart.remove');
Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [StorefrontController::class, 'processCheckout'])->middleware('throttle:10,1')->name('checkout.post');
Route::get('/orders/{orderNumber}/success', [StorefrontController::class, 'orderSuccess'])->name('order.success');

Route::middleware('auth')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Web\OrderHubController::class, 'index'])->name('orders.hub');
    Route::get('/orders/{orderNumber}', [\App\Http\Controllers\Web\OrderHubController::class, 'show'])->name('orders.detail');
    Route::post('/orders/{orderNumber}/cancel', [\App\Http\Controllers\Web\OrderHubController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{orderNumber}/reorder', [\App\Http\Controllers\Web\OrderHubController::class, 'reorder'])->name('orders.reorder');
    Route::post('/orders/{orderNumber}/returns', [\App\Http\Controllers\Web\OrderHubController::class, 'requestReturn'])->middleware('throttle:5,1')->name('orders.returns.store');
});
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
Route::post('/profile/seller/products/{id}', [\App\Http\Controllers\Web\ProfileController::class, 'updateSellerProduct'])->name('profile.seller.products.update');
Route::post('/profile/seller/products/{id}/status', [\App\Http\Controllers\Web\ProfileController::class, 'updateSellerProductStatus'])->name('profile.seller.products.status');
Route::post('/profile/seller/orders/{id}/status', [\App\Http\Controllers\Web\ProfileController::class, 'updateSellerOrderStatus'])->name('profile.seller.orders.status');
Route::post('/profile/seller/payouts', [\App\Http\Controllers\Web\ProfileController::class, 'requestSellerPayout'])->middleware('throttle:5,1')->name('profile.seller.payouts.store');

// Courier actions inside Profile
Route::post('/profile/courier/shipments/{id}/status', [\App\Http\Controllers\Web\ProfileController::class, 'updateCourierShipmentStatus'])->name('profile.courier.shipments.status');

Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
Route::match(['get', 'post'], '/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'showRequestForm'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'sendCode'])->middleware('throttle:3,1')->name('password.email');
Route::get('/reset-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');

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
Route::post('/admin/refunds/{id}/review', [\App\Http\Controllers\Web\AdminDashboardController::class, 'reviewRefund'])->name('admin.refunds.review');
Route::post('/admin/refunds/{id}/complete', [\App\Http\Controllers\Web\AdminDashboardController::class, 'completeRefund'])->name('admin.refunds.complete');
Route::post('/admin/payouts/{id}/review', [\App\Http\Controllers\Web\AdminDashboardController::class, 'reviewPayout'])->name('admin.payouts.review');
Route::post('/admin/backups',[\App\Http\Controllers\Web\AdminDashboardController::class, 'createBackup'])->middleware('throttle:3,1')->name('admin.backups.create');
Route::get('/admin/backups/{filename}/download', [\App\Http\Controllers\Web\AdminDashboardController::class, 'downloadBackup'])->where('filename', \App\Services\Backup\DatabaseBackupService::FILENAME_PATTERN)->middleware('throttle:10,1')->name('admin.backups.download');

// Categories Management
Route::post('/admin/categories', [\App\Http\Controllers\Web\AdminDashboardController::class, 'storeCategory'])->name('admin.categories.store');
Route::post('/admin/categories/{id}/status', [\App\Http\Controllers\Web\AdminDashboardController::class, 'updateCategoryStatus'])->name('admin.categories.status');
Route::delete('/admin/categories/{id}', [\App\Http\Controllers\Web\AdminDashboardController::class, 'deleteCategory'])->name('admin.categories.delete');

// Promocodes & Banners Management
Route::post('/admin/promocodes', [\App\Http\Controllers\Web\AdminDashboardController::class, 'storePromocode'])->name('admin.promocodes.store');
Route::post('/admin/promocodes/{id}/toggle', [\App\Http\Controllers\Web\AdminDashboardController::class, 'togglePromocode'])->name('admin.promocodes.toggle');
Route::delete('/admin/promocodes/{id}', [\App\Http\Controllers\Web\AdminDashboardController::class, 'deletePromocode'])->name('admin.promocodes.delete');

Route::post('/admin/banners', [\App\Http\Controllers\Web\AdminDashboardController::class, 'storeBanner'])->name('admin.banners.store');
Route::post('/admin/banners/{id}/status', [\App\Http\Controllers\Web\AdminDashboardController::class, 'updateBannerStatus'])->name('admin.banners.status');
Route::delete('/admin/banners/{id}', [\App\Http\Controllers\Web\AdminDashboardController::class, 'deleteBanner'])->name('admin.banners.delete');

// Excel / CSV Export
Route::get('/admin/export/orders', [\App\Http\Controllers\Web\AdminDashboardController::class, 'exportOrdersCsv'])->name('admin.export.orders');
Route::get('/admin/export/sellers', [\App\Http\Controllers\Web\AdminDashboardController::class, 'exportSellersCsv'])->name('admin.export.sellers');

// Checkout Promocode Verification
Route::post('/checkout/promocode/check', [\App\Http\Controllers\Web\StorefrontController::class, 'checkPromocode'])->name('checkout.promocode.check');

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
