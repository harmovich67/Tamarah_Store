<?php

/**
 * Tumurna Luxury Dates E-Commerce Platform (متجر تمرنا للتمور الفاخرة)
 * Luxury Saudi Dates / Multi-Zone Shipping / Coupons & Guest Checkout
 */

declare(strict_types=1);

// Keep server errors out of the response until environment settings are loaded.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Start session if not started with persistent cookie
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 86400 * 30; // 30 days session
    @ini_set('session.gc_maxlifetime', (string)$lifetime);
    $secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    @session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Autoloader function
spl_autoload_register(function ($class) {
    $prefixMap = [
        'Database\\' => __DIR__ . '/database/',
        'App\\Core\\' => __DIR__ . '/app/Core/',
        'App\\Services\\' => __DIR__ . '/app/Services/',
        'App\\Controllers\\Admin\\' => __DIR__ . '/app/Controllers/Admin/',
        'App\\Controllers\\' => __DIR__ . '/app/Controllers/',
    ];

    foreach ($prefixMap as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require_once __DIR__ . '/app/Core/helpers.php';

if (env('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

use App\Core\I18n;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Cache;
use App\Core\Logger;
use App\Core\Security;
use Database\Migrator;

// Initialize Core Engines
Cache::init();
Logger::init();
Security::init();
Security::applyHeaders();

// Check IP Firewall
if (Security::isIpBlocked()) {
    http_response_code(403);
    die("Access Denied: Your IP address has been blocked by the system security firewall.");
}

// Global Exception & Uncaught Error Handler
set_exception_handler(function (\Throwable $e) {
    Logger::error("Uncaught Exception: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    if (ini_get('display_errors') === '1') {
        throw $e;
    } else {
        Response::error("حدث خطأ غير متوقع في النظام. تم تسجيل تفاصيل الخطأ للمراجعة.", 500);
    }
});

// Initialize translations & session auth
I18n::init();
Auth::init();

// Auto-run migrations & seed if not yet initialized
try {
    Migrator::run();
    Migrator::seed();
} catch (\Throwable $e) {
    error_log("Database initialization notice: " . $e->getMessage());
}

// Initialize Router & Request
$router = new Router();
$request = new Request();

// ==========================================
// 1. Storefront Routes
// ==========================================
$router->get('/', [\App\Controllers\HomeController::class, 'index']);
$router->get('/catalog', [\App\Controllers\CatalogController::class, 'catalog']);
$router->get('/product/{slug}', [\App\Controllers\CatalogController::class, 'productDetail']);
$router->get('/gift-cards', [\App\Controllers\PageController::class, 'giftCards']);
$router->get('/wishlist', [\App\Controllers\PageController::class, 'wishlist']);
$router->get('/about', [\App\Controllers\PageController::class, 'about']);
$router->get('/contact', [\App\Controllers\PageController::class, 'contact']);
$router->get('/page/{slug}', [\App\Controllers\PageController::class, 'show']);
$router->get('/post/{slug}', [\App\Controllers\PageController::class, 'showPost']);

$router->get('/cart', [\App\Controllers\CartController::class, 'index']);
$router->get('/checkout', [\App\Controllers\CheckoutController::class, 'index']);
$router->post('/checkout', [\App\Controllers\CheckoutController::class, 'process']);
$router->post('/checkout/process', [\App\Controllers\CheckoutController::class, 'process']);
$router->get('/order/{order_number}', [\App\Controllers\CheckoutController::class, 'orderConfirmation']);
$router->get('/checkout/success/{order_number}', [\App\Controllers\CheckoutController::class, 'orderConfirmation']);
$router->get('/checkout/payment/callback/{provider}', [\App\Controllers\CheckoutController::class, 'paymentCallback']);
$router->post('/checkout/payment/callback/{provider}', [\App\Controllers\CheckoutController::class, 'paymentCallback']);
$router->get('/checkout/payment/webhook/{provider}', [\App\Controllers\CheckoutController::class, 'paymentWebhook']);
$router->post('/checkout/payment/webhook/{provider}', [\App\Controllers\CheckoutController::class, 'paymentWebhook']);
$router->get('/checkout/payment/cancel', [\App\Controllers\CheckoutController::class, 'paymentCancel']);
$router->get('/checkout/simulator', [\App\Controllers\CheckoutController::class, 'paymentSimulator']);
$router->post('/api/admin/payment/test-connection', [\App\Controllers\CheckoutController::class, 'apiTestConnection'], ['super_admin']);

// Storefront Language Switcher Route (Only affects storefront)
$router->get('/lang/{locale}', function(Request $request, string $locale = 'ar') {
    $locale = strtolower(trim($locale));
    if (in_array($locale, ['ar', 'en'])) {
        \App\Core\I18n::setStoreLocale($locale);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $referer = preg_replace('/([?&])lang=[a-z]{2}(&|$)/i', '$1', $referer);
    $referer = rtrim($referer, '?&');
    \App\Core\Response::redirect($referer ?: '/');
});

// Dedicated Admin Language Switcher Route (Completely Isolated from Storefront)
$router->get('/admin/lang/{locale}', function(Request $request, string $locale = 'ar') {
    $locale = strtolower(trim($locale));
    if (in_array($locale, ['ar', 'en'])) {
        \App\Core\I18n::setAdminLocale($locale);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? '/admin';
    $referer = preg_replace('/([?&])lang=[a-z]{2}(&|$)/i', '$1', $referer);
    $referer = rtrim($referer, '?&');
    \App\Core\Response::redirect($referer ?: '/admin');
}, ['auth']);

// Backward compatibility redirects
$router->get('/schools', fn() => \App\Core\Response::redirect('/catalog'));
$router->get('/schools/{slug}', fn() => \App\Core\Response::redirect('/catalog'));

// ==========================================
// 2. Storefront AJAX API Routes
// ==========================================
$router->get('/api/products', [\App\Controllers\CatalogController::class, 'apiProducts']);
$router->get('/api/variant', [\App\Controllers\CatalogController::class, 'apiVariant']);

$router->get('/api/cart', [\App\Controllers\CartController::class, 'apiGet']);
$router->post('/api/cart/add', [\App\Controllers\CartController::class, 'apiAdd']);
$router->post('/api/cart/update', [\App\Controllers\CartController::class, 'apiUpdate']);
$router->post('/api/cart/remove', [\App\Controllers\CartController::class, 'apiRemove']);
$router->post('/api/cart/clear', [\App\Controllers\CartController::class, 'apiClear']);
$router->post('/api/cart/coupon', [\App\Controllers\CartController::class, 'apiApplyCoupon']);
$router->post('/api/cart/coupon/remove', [\App\Controllers\CartController::class, 'apiRemoveCoupon']);
$router->post('/api/gift-cards/add', [\App\Controllers\CartController::class, 'apiAddGiftCard']);
$router->post('/api/wishlist/toggle', [\App\Controllers\PageController::class, 'apiWishlistToggle']);

$router->post('/api/checkout/calculate-shipping', [\App\Controllers\CheckoutController::class, 'apiCalculateShipping']);

// ==========================================
// 3. Authentication & Profile Routes
// ==========================================
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/admin/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/admin/login', [\App\Controllers\AuthController::class, 'login']);

$router->get('/register', [\App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register', [\App\Controllers\AuthController::class, 'register']);

$router->get('/forgot-password', [\App\Controllers\AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [\App\Controllers\AuthController::class, 'forgotPassword']);

$router->get('/verify-otp', [\App\Controllers\AuthController::class, 'showVerifyOtp']);
$router->post('/verify-otp', [\App\Controllers\AuthController::class, 'verifyOtp']);
$router->post('/resend-otp', [\App\Controllers\AuthController::class, 'resendOtp']);

$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// ==========================================
// 3.1 Customer Profile Portal Routes
// ==========================================
$router->get('/profile', [\App\Controllers\ProfileController::class, 'index']);
$router->post('/profile/update-info', [\App\Controllers\ProfileController::class, 'updateInfo']);
$router->post('/profile/change-password', [\App\Controllers\ProfileController::class, 'changePassword']);
$router->get('/profile/addresses', [\App\Controllers\ProfileController::class, 'addresses']);
$router->post('/profile/addresses/store', [\App\Controllers\ProfileController::class, 'storeAddress']);
$router->post('/profile/addresses/delete', [\App\Controllers\ProfileController::class, 'deleteAddress']);
$router->post('/profile/addresses/default', [\App\Controllers\ProfileController::class, 'setDefaultAddress']);
$router->get('/profile/orders', [\App\Controllers\ProfileController::class, 'orders']);
$router->get('/profile/order/{order_number}', [\App\Controllers\ProfileController::class, 'orderDetail']);
$router->post('/profile/order/{order_number}/cancel', [\App\Controllers\ProfileController::class, 'cancelOrder']);
$router->get('/profile/gift-cards', [\App\Controllers\ProfileController::class, 'giftCards']);
$router->get('/profile/notifications', [\App\Controllers\ProfileController::class, 'notifications']);
$router->get('/profile/settings', [\App\Controllers\ProfileController::class, 'settings']);
$router->post('/profile/settings/save', [\App\Controllers\ProfileController::class, 'saveSettings']);

// Account route aliases for frontend compatibility
$router->get('/account', [\App\Controllers\ProfileController::class, 'index']);
$router->get('/account/profile', [\App\Controllers\ProfileController::class, 'index']);
$router->get('/account/orders', [\App\Controllers\ProfileController::class, 'orders']);
$router->get('/account/addresses', [\App\Controllers\ProfileController::class, 'addresses']);
$router->get('/account/gift-cards', [\App\Controllers\ProfileController::class, 'giftCards']);
$router->get('/account/notifications', [\App\Controllers\ProfileController::class, 'notifications']);
$router->get('/account/settings', [\App\Controllers\ProfileController::class, 'settings']);

// ==========================================
// 4. Admin Dashboard & Management Routes
// ==========================================
$router->get('/admin', [\App\Controllers\Admin\DashboardController::class, 'index'], ['store_manager']);

// Warehouses & Categories Management
$router->get('/admin/warehouses', [\App\Controllers\Admin\StructureController::class, 'warehouses'], ['store_manager']);
$router->post('/admin/warehouses/store', [\App\Controllers\Admin\StructureController::class, 'storeWarehouse'], ['store_manager']);
$router->post('/admin/warehouses/update', [\App\Controllers\Admin\StructureController::class, 'updateWarehouse'], ['store_manager']);
$router->post('/admin/warehouses/delete', [\App\Controllers\Admin\StructureController::class, 'deleteWarehouse'], ['super_admin']);

$router->get('/admin/categories', [\App\Controllers\Admin\StructureController::class, 'categories'], ['store_manager']);
$router->post('/admin/categories/store', [\App\Controllers\Admin\StructureController::class, 'storeCategory'], ['store_manager']);
$router->post('/admin/categories/update', [\App\Controllers\Admin\StructureController::class, 'updateCategory'], ['store_manager']);
$router->post('/admin/categories/delete', [\App\Controllers\Admin\StructureController::class, 'deleteCategory'], ['super_admin']);

// Users & Roles Management (Super Admin)
$router->get('/admin/users', [\App\Controllers\Admin\UsersController::class, 'index'], ['super_admin']);
$router->post('/admin/users/store', [\App\Controllers\Admin\UsersController::class, 'store'], ['super_admin']);
$router->post('/admin/users/update', [\App\Controllers\Admin\UsersController::class, 'update'], ['super_admin']);
$router->post('/admin/users/delete', [\App\Controllers\Admin\UsersController::class, 'delete'], ['super_admin']);

// Products & Date Inventory Management (Super Admin & Store Manager)
$router->get('/admin/products', [\App\Controllers\Admin\ProductsController::class, 'index'], ['store_manager']);
$router->get('/admin/products/create', [\App\Controllers\Admin\ProductsController::class, 'create'], ['store_manager']);
$router->post('/admin/products/store', [\App\Controllers\Admin\ProductsController::class, 'store'], ['store_manager']);
$router->get('/admin/products/{id}/edit', [\App\Controllers\Admin\ProductsController::class, 'edit'], ['store_manager']);
$router->post('/admin/products/update', [\App\Controllers\Admin\ProductsController::class, 'update'], ['store_manager']);
$router->post('/admin/products/delete', [\App\Controllers\Admin\ProductsController::class, 'delete'], ['store_manager']);
$router->post('/api/admin/stock/update', [\App\Controllers\Admin\ProductsController::class, 'apiUpdateStock'], ['store_manager']);

// Orders & Cold Chain Shipments Management
$router->get('/admin/orders', [\App\Controllers\Admin\OrdersController::class, 'index'], ['store_manager']);
$router->get('/admin/orders/{id}', [\App\Controllers\Admin\OrdersController::class, 'show'], ['store_manager']);
$router->post('/admin/orders/update-status', [\App\Controllers\Admin\OrdersController::class, 'updateStatus'], ['store_manager']);
$router->post('/admin/orders/generate-waybill', [\App\Controllers\Admin\OrdersController::class, 'generateBostaWaybill'], ['store_manager']);

// Gateways & Policies Settings (Super Admin)
$router->get('/admin/settings', [\App\Controllers\Admin\SettingsController::class, 'index'], ['super_admin']);
$router->post('/admin/settings/update', [\App\Controllers\Admin\SettingsController::class, 'update'], ['super_admin']);

// Coupons Management (Super Admin)
$router->get('/admin/coupons', [\App\Controllers\Admin\CouponsController::class, 'index'], ['super_admin']);
$router->post('/admin/coupons/store', [\App\Controllers\Admin\CouponsController::class, 'store'], ['super_admin']);
$router->post('/admin/coupons/update', [\App\Controllers\Admin\CouponsController::class, 'update'], ['super_admin']);
$router->post('/admin/coupons/delete', [\App\Controllers\Admin\CouponsController::class, 'delete'], ['super_admin']);
$router->post('/admin/coupons/toggle', [\App\Controllers\Admin\CouponsController::class, 'toggle'], ['super_admin']);

// Saudi Shipping Zones Management (Super Admin)
$router->get('/admin/shipping-zones', [\App\Controllers\Admin\ShippingZonesController::class, 'index'], ['super_admin']);
$router->post('/admin/shipping-zones/store', [\App\Controllers\Admin\ShippingZonesController::class, 'store'], ['super_admin']);
$router->post('/admin/shipping-zones/update', [\App\Controllers\Admin\ShippingZonesController::class, 'update'], ['super_admin']);
$router->post('/admin/shipping-zones/delete', [\App\Controllers\Admin\ShippingZonesController::class, 'delete'], ['super_admin']);
$router->post('/admin/shipping-zones/toggle', [\App\Controllers\Admin\ShippingZonesController::class, 'toggle'], ['super_admin']);

// Site Identity & Contact Settings (Super Admin)
$router->get('/admin/site-settings', [\App\Controllers\Admin\SettingsController::class, 'siteSettings'], ['super_admin']);
$router->post('/admin/site-settings/update', [\App\Controllers\Admin\SettingsController::class, 'updateSiteSettings'], ['super_admin']);

// Live Translations Editor (Super Admin)
$router->get('/admin/translations', [\App\Controllers\Admin\TranslationsController::class, 'index'], ['super_admin']);
$router->post('/admin/translations/update', [\App\Controllers\Admin\TranslationsController::class, 'update'], ['super_admin']);
$router->post('/admin/translations/delete', [\App\Controllers\Admin\TranslationsController::class, 'delete'], ['super_admin']);

// Pages Management (Super Admin)
$router->get('/admin/pages', [\App\Controllers\Admin\PagesController::class, 'index'], ['super_admin']);
$router->get('/admin/pages/create', [\App\Controllers\Admin\PagesController::class, 'create'], ['super_admin']);
$router->post('/admin/pages/store', [\App\Controllers\Admin\PagesController::class, 'store'], ['super_admin']);
$router->get('/admin/pages/{id}/edit', [\App\Controllers\Admin\PagesController::class, 'edit'], ['super_admin']);
$router->post('/admin/pages/{id}/update', [\App\Controllers\Admin\PagesController::class, 'update'], ['super_admin']);
$router->post('/admin/pages/delete', [\App\Controllers\Admin\PagesController::class, 'delete'], ['super_admin']);
$router->post('/api/admin/pages/toggle-status', [\App\Controllers\Admin\PagesController::class, 'toggleStatus'], ['super_admin']);

// Custom Fields ACF Pro Engine (Super Admin)
$router->get('/api/admin/acf-targets', [\App\Controllers\Admin\FieldGroupsController::class, 'apiTargets'], ['super_admin']);
$router->get('/admin/custom-fields', [\App\Controllers\Admin\FieldGroupsController::class, 'index'], ['super_admin']);
$router->get('/admin/custom-fields/create', [\App\Controllers\Admin\FieldGroupsController::class, 'create'], ['super_admin']);
$router->post('/admin/custom-fields/store', [\App\Controllers\Admin\FieldGroupsController::class, 'store'], ['super_admin']);
$router->get('/admin/custom-fields/{id}/edit', [\App\Controllers\Admin\FieldGroupsController::class, 'edit'], ['super_admin']);
$router->post('/admin/custom-fields/{id}/update', [\App\Controllers\Admin\FieldGroupsController::class, 'update'], ['super_admin']);
$router->post('/admin/custom-fields/delete', [\App\Controllers\Admin\FieldGroupsController::class, 'delete'], ['super_admin']);

// Custom Posts & Types Management (Super Admin)
$router->get('/admin/posts', [\App\Controllers\Admin\PostsController::class, 'index'], ['super_admin']);
$router->get('/admin/posts/create', [\App\Controllers\Admin\PostsController::class, 'create'], ['super_admin']);
$router->post('/admin/posts/store', [\App\Controllers\Admin\PostsController::class, 'store'], ['super_admin']);
$router->get('/admin/posts/{id}/edit', [\App\Controllers\Admin\PostsController::class, 'edit'], ['super_admin']);
$router->post('/admin/posts/{id}/update', [\App\Controllers\Admin\PostsController::class, 'update'], ['super_admin']);
$router->post('/admin/posts/delete', [\App\Controllers\Admin\PostsController::class, 'delete'], ['super_admin']);

// Cache Management (Super Admin)
$router->get('/admin/cache', [\App\Controllers\Admin\CacheController::class, 'index'], ['super_admin']);
$router->post('/admin/cache/clear', [\App\Controllers\Admin\CacheController::class, 'clear'], ['super_admin']);
$router->post('/admin/cache/preload', [\App\Controllers\Admin\CacheController::class, 'preload'], ['super_admin']);

// Logs & Activity Management (Super Admin)
$router->get('/admin/logs', [\App\Controllers\Admin\LogsController::class, 'index'], ['super_admin']);
$router->post('/admin/logs/clear', [\App\Controllers\Admin\LogsController::class, 'clear'], ['super_admin']);
$router->get('/admin/logs/download', [\App\Controllers\Admin\LogsController::class, 'download'], ['super_admin']);

// Security & Hardening Suite (Super Admin)
$router->get('/admin/security', [\App\Controllers\Admin\SecurityController::class, 'index'], ['super_admin']);
$router->post('/admin/security/settings', [\App\Controllers\Admin\SecurityController::class, 'updateSettings'], ['super_admin']);
$router->post('/admin/security/block-ip', [\App\Controllers\Admin\SecurityController::class, 'blockIp'], ['super_admin']);
$router->post('/admin/security/unblock-ip', [\App\Controllers\Admin\SecurityController::class, 'unblockIp'], ['super_admin']);

// Header & Footer Menu Management (Super Admin)
$router->get('/admin/menus', [\App\Controllers\Admin\MenuController::class, 'index'], ['super_admin']);
$router->post('/admin/menus/store', [\App\Controllers\Admin\MenuController::class, 'store'], ['super_admin']);
$router->post('/admin/menus/update', [\App\Controllers\Admin\MenuController::class, 'update'], ['super_admin']);
$router->post('/admin/menus/delete', [\App\Controllers\Admin\MenuController::class, 'delete'], ['super_admin']);

// Hero Slider / Banners Management (Super Admin)
$router->get('/admin/banners', [\App\Controllers\Admin\BannersController::class, 'index'], ['super_admin']);
$router->post('/admin/banners/store', [\App\Controllers\Admin\BannersController::class, 'store'], ['super_admin']);
$router->post('/admin/banners/update', [\App\Controllers\Admin\BannersController::class, 'update'], ['super_admin']);
$router->post('/admin/banners/delete', [\App\Controllers\Admin\BannersController::class, 'delete'], ['super_admin']);
$router->post('/admin/banners/toggle', [\App\Controllers\Admin\BannersController::class, 'toggle'], ['super_admin']);
$router->post('/admin/banners/move-up', [\App\Controllers\Admin\BannersController::class, 'moveUp'], ['super_admin']);
$router->post('/admin/banners/move-down', [\App\Controllers\Admin\BannersController::class, 'moveDown'], ['super_admin']);

// Homepage Sections Management (Super Admin)
$router->get('/admin/home-sections', [\App\Controllers\Admin\HomeSectionsController::class, 'index'], ['super_admin']);
$router->post('/admin/home-sections/update-section', [\App\Controllers\Admin\HomeSectionsController::class, 'updateSection'], ['super_admin']);
$router->post('/admin/home-sections/update-features', [\App\Controllers\Admin\HomeSectionsController::class, 'updateFeatures'], ['super_admin']);
$router->post('/admin/home-sections/update-testimonials', [\App\Controllers\Admin\HomeSectionsController::class, 'updateTestimonials'], ['super_admin']);
$router->post('/admin/home-sections/update-newsletter', [\App\Controllers\Admin\HomeSectionsController::class, 'updateNewsletter'], ['super_admin']);
$router->post('/admin/home-sections/move-up', [\App\Controllers\Admin\HomeSectionsController::class, 'moveUp'], ['super_admin']);
$router->post('/admin/home-sections/move-down', [\App\Controllers\Admin\HomeSectionsController::class, 'moveDown'], ['super_admin']);
$router->post('/admin/home-sections/toggle', [\App\Controllers\Admin\HomeSectionsController::class, 'toggleStatus'], ['super_admin']);
$router->post('/admin/home-sections/store', [\App\Controllers\Admin\HomeSectionsController::class, 'storeCustom'], ['super_admin']);
$router->post('/admin/home-sections/delete', [\App\Controllers\Admin\HomeSectionsController::class, 'deleteCustom'], ['super_admin']);

// Full Backup & Restore (Super Admin)
$router->get('/admin/backup', [\App\Controllers\Admin\BackupController::class, 'index'], ['super_admin']);
$router->post('/admin/backup/generate', [\App\Controllers\Admin\BackupController::class, 'generate'], ['super_admin']);
$router->get('/admin/backup/download', [\App\Controllers\Admin\BackupController::class, 'download'], ['super_admin']);
$router->post('/admin/backup/delete', [\App\Controllers\Admin\BackupController::class, 'delete'], ['super_admin']);
$router->post('/admin/backup/restore', [\App\Controllers\Admin\BackupController::class, 'restore'], ['super_admin']);
$router->post('/admin/backup/auto-settings', [\App\Controllers\Admin\BackupController::class, 'updateAutoSettings'], ['super_admin']);

// Dispatch the Request
$router->dispatch($request);

