<?php
use App\Core\I18n;
use App\Core\Auth;
use App\Controllers\CartController;
use Database\Database;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
$isEn = $locale === 'en';
$cart = CartController::getCartSummary();
$user = Auth::user();

// Fetch site configuration dynamically
$siteSettingsRaw = Database::fetchAll("SELECT `key`, `value` FROM settings");
$siteSettings = [];
foreach ($siteSettingsRaw as $row) {
    $siteSettings[$row['key']] = $row['value'];
}

$siteName = $siteSettings['site_name_' . $locale] ?? ($locale === 'ar' ? 'تَـمْرُنـا للتمور الفاخرة' : 'Tumurna Luxury Dates');
$siteTagline = $siteSettings['site_tagline_' . $locale] ?? ($locale === 'ar' ? 'أفخر أنواع التمور الملكية والضيافة السعودية' : 'Finest Saudi Dates & Royal Hospitality');
$announcementText = $siteSettings['announcement_text_' . $locale] ?? ($locale === 'ar' ? 'شحن مبرد فاخر وسريع مجاناً للطلبات فوق ٣٠٠ ر.س داخل كافة مدن المملكة 🌴' : 'Free fast refrigerated shipping across KSA on orders over 300 SAR 🌴');
$contactPhone = $siteSettings['contact_phone'] ?? '8001248888';
$contactEmail = $siteSettings['contact_email'] ?? 'care@tumurna.com';
$whatsappNumber = $siteSettings['contact_whatsapp'] ?? ($siteSettings['whatsapp_number'] ?? '966500000000');
$whatsappDigits = preg_replace('/\D+/', '', (string)$whatsappNumber);
$phoneDigits = preg_replace('/[^\d+]/', '', (string)$contactPhone);
$footerText = $siteSettings['footer_text_' . $locale] ?? ($locale === 'ar' ? 'جميع الحقوق محفوظة لمتجر تَـمْرُنـا للتمور الفاخرة © 2026' : 'All rights reserved for Tumurna Luxury Dates © 2026');
$setting = function (array $keys, string $default = '') use ($siteSettings): string {
    foreach ($keys as $k) {
        if (isset($siteSettings[$k]) && trim((string)$siteSettings[$k]) !== '') return trim((string)$siteSettings[$k]);
    }
    return $default;
};
$socialLinks = array_filter([
    ['Instagram', 'instagram', $setting(['social_instagram', 'instagram_url'])],
    ['Snapchat', 'ghost', $setting(['social_snapchat', 'snapchat_url'])],
    ['TikTok', 'music', $setting(['social_tiktok', 'tiktok_url'])],
    ['X', 'send', $setting(['social_facebook', 'social_x', 'x_url'])],
], fn($s) => $s[2] !== '');
$taxNumber = $setting(['tax_number', 'vat_number']);
$crNumber = $setting(['cr_number', 'commercial_register']);
$contactAddress = $setting(['contact_address_' . $locale, 'contact_address'], $isEn ? 'Kingdom of Saudi Arabia' : 'المملكة العربية السعودية');
$payOn = fn(string $k) => ($siteSettings[$k] ?? '1') == '1';
// Default brand marks unless the admin uploaded a different logo in Site Settings
$customLogo = $setting(['site_logo']);
$customLogo = ($customLogo !== '' && !in_array(ltrim($customLogo, '/'), ['assets/images/logo.png'], true)) ? asset($customLogo) : '';

// Session wishlist count (only for logged-in users)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$wishlistCount = \App\Core\Auth::check() ? count($_SESSION['wishlist'] ?? []) : 0;
$cartCount = (int)($cart['count'] ?? 0);

// Views can opt into the transparent header that overlays a hero (home page)
$immersiveHeader = !empty($immersiveHeader);
$shellClass = $immersiveHeader ? 'storefront-shell storefront-shell--immersive tamrna-home' : 'storefront-shell storefront-shell--standard';
$homeUrl = url('/');
$currentLocale = $locale;
$otherLocale = $currentLocale === 'ar' ? 'en' : 'ar';

// Is a menu link the current page? Query strings (e.g. ?filter=preorder) must match too.
$menuActive = function (string $href): bool {
    if (preg_match('#^(https?:)?//#', $href)) return false;
    $parts = parse_url($href);
    $path = $parts['path'] ?? '/';
    parse_str($parts['query'] ?? '', $q);
    if ($path === '/' || $path === '') return is_active_url('/', true);
    if (!is_active_url($path)) return false;
    if ($q) {
        foreach ($q as $k => $v) { if ((string)($_GET[$k] ?? '') !== (string)$v) return false; }
        return true;
    }
    return empty($_GET['filter']);
};
$menuRows = [];
try {
    $menuRows = Database::fetchAll("
        SELECT m.*, p.slug AS page_slug
        FROM menu_items m
        LEFT JOIN pages p ON m.link_type = 'page' AND m.page_id = p.id
        WHERE m.status = 'active'
        ORDER BY m.order_index ASC, m.id ASC
    ");
} catch (\Throwable $e) {
    $menuRows = [];
}
$buildMenu = function (string $location) use ($menuRows, $isEn, $menuActive): array {
    $items = [];
    foreach ($menuRows as $m) {
        if (($m['location'] ?? '') !== $location) continue;
        $order = $isEn ? ['label_en', 'title_en', 'label_ar', 'title_ar'] : ['label_ar', 'title_ar', 'label_en', 'title_en'];
        $label = '';
        foreach ($order as $col) {
            if (trim((string)($m[$col] ?? '')) !== '') { $label = trim((string)$m[$col]); break; }
        }
        $href = ($m['link_type'] ?? 'custom') === 'page'
            ? (!empty($m['page_slug']) ? '/page/' . $m['page_slug'] : '')
            : (string)($m['custom_url'] ?? '');
        if ($href === '' || $label === '') continue;
        $items[] = [$href, $label, $menuActive($href), !empty($m['open_new_tab'])];
    }
    return $items;
};
$navItems = $buildMenu('header');
if (!$navItems) {
    $navItems = [
        ['/', $isEn ? 'Home' : 'الرئيسية', $menuActive('/'), false],
        ['/catalog', $isEn ? 'Categories' : 'الأقسام', $menuActive('/catalog'), false],
        ['/catalog?filter=preorder', $isEn ? 'Pre-orders' : 'الحجز المسبق', $menuActive('/catalog?filter=preorder'), false],
        ['/gift-cards', $isEn ? 'Gift Cards' : 'بطاقات الإهداء', $menuActive('/gift-cards'), false],
        ['/about', $isEn ? 'Our Story' : 'من نحن', $menuActive('/about'), false],
        ['/contact', $isEn ? 'Contact' : 'تواصل معنا', $menuActive('/contact'), false],
    ];
}
$footerMenu = $buildMenu('footer');

// Auth screens use the minimal immersive header (logo + back link) and no footer
$authLayout = !empty($authLayout);
if ($authLayout) $immersiveHeader = true;
$userInitial = $user ? mb_substr(trim((string)($user['name'] ?? 'U')), 0, 1) : '';
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? esc_html($page_title) . ' | ' : '' ?><?= htmlspecialchars($siteName) ?> - <?= htmlspecialchars($siteTagline) ?></title>

    <link rel="icon" type="image/png" href="<?= asset('assets/images/logo.png') ?>" />

    <!-- Tamrna Foundation design system (fonts are bundled in assets/fonts) -->
    <link rel="preload" href="<?= asset('assets/fonts/thmanyah-sans-400.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('assets/css/foundation.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/foundation.css') ?>">
    <?php // Page layers (home / gift / auth) load only where the design uses them, as in the reference app. ?>
    <?php foreach ((array)($pageCss ?? []) as $pageCssName): ?>
    <link rel="stylesheet" href="<?= asset('assets/css/foundation-' . $pageCssName . '.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/foundation-' . $pageCssName . '.css') ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= asset('assets/css/foundation-ext.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/foundation-ext.css') ?>">
    <?php if (!empty($extra_head)) echo $extra_head; ?>

    <script>
        window.APP_URL = <?= json_encode(rtrim(url('/'), '/')) ?>;
        window.ASSET_URL = <?= json_encode(base_path_url()) ?>;
        window.appUrl = function(path) {
            path = path || '';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('//')) return path;
            return window.APP_URL + (path.startsWith('/') ? path : '/' + path);
        };
        window.appAsset = function(path) {
            if (/^(https?:)?\/\//.test(path)) return path;
            return window.ASSET_URL + '/' + path.replace(/^\//, '');
        };
    </script>
</head>
<body class="storefront-page">

<div class="<?= $shellClass ?>">
    <a class="skip-link" href="#main-content"><?= $isEn ? 'Skip to content' : 'انتقل إلى المحتوى' ?></a>

    <!-- HEADER -->
    <div class="header-system <?= $immersiveHeader ? 'header-system--immersive' : 'header-system--standard' ?>" id="header-system">
        <?php if (!empty($announcementText) && !$authLayout): ?>
            <div class="announcement-bar"><span><?= htmlspecialchars($announcementText) ?></span></div>
        <?php endif; ?>
        <header class="site-header <?= $immersiveHeader ? 'site-header--immersive' : 'site-header--standard' ?>">
            <div class="container header-inner">
                <a class="logo-link" href="<?= $homeUrl ?>" aria-label="<?= $isEn ? 'Tamrna - Home' : 'تمرنا - الرئيسية' ?>">
                    <span class="brand-wordmark" role="img" aria-label="تمرنا Tamrna">
                        <?php if ($customLogo): ?>
                            <img alt="<?= esc_attr($siteName) ?>" src="<?= esc_attr($customLogo) ?>">
                        <?php else: ?>
                            <img class="brand-logo-dark" alt="" width="174" height="246" src="<?= asset('assets/brand/tamrna-logo.svg') ?>">
                            <img class="brand-logo-light" alt="" width="174" height="246" src="<?= asset('assets/brand/tamrna-logo-light.svg') ?>">
                        <?php endif; ?>
                    </span>
                </a>

                <?php if ($authLayout): ?>
                <div class="header-actions">
                    <a class="button button--ghost header-back-link" href="<?= $homeUrl ?>"><?= $isEn ? 'Back to home' : 'العودة للرئيسية' ?><?= fnd_icon('arrow-left', 18, 'directional-arrow') ?></a>
                    <a class="language-link" lang="<?= $otherLocale ?>" hreflang="<?= $otherLocale ?>" href="<?= url('/lang/' . $otherLocale) ?>"><?= $currentLocale === 'ar' ? 'English' : 'العربية' ?></a>
                </div>
                <?php else: ?>
                <nav class="desktop-nav" aria-label="<?= $isEn ? 'Main menu' : 'القائمة' ?>">
                    <?php foreach ($navItems as [$href, $label, $active, $newTab]): ?>
                        <a href="<?= esc_attr(url($href)) ?>" <?= $active ? 'aria-current="page"' : '' ?> <?= $newTab ? 'target="_blank" rel="noopener"' : '' ?>><?= esc_html($label) ?></a>
                    <?php endforeach; ?>
                </nav>

                <div class="header-actions">
                    <button type="button" class="button button--icon mobile-menu-button" data-open="menu-drawer" aria-label="<?= $isEn ? 'Menu' : 'القائمة' ?>">
                        <?= fnd_icon('menu', 21) ?>
                    </button>
                    <button type="button" class="button button--ghost header-search-trigger" data-open="search-dialog" aria-label="<?= $isEn ? 'Search' : 'بحث' ?>">
                        <?= fnd_icon('search', 19) ?>
                        <span><?= $isEn ? 'Search products' : 'ابحث عن منتج' ?></span>
                    </button>
                    <?php if ($user): ?>
                        <a class="button button--icon account-button" href="<?= url(($user['role_id'] ?? 0) === 1 ? '/admin' : '/profile') ?>" aria-label="<?= $isEn ? 'My account' : 'حسابي' ?>" title="<?= esc_attr($user['name'] ?? '') ?>">
                            <?= fnd_icon('user-round', 21) ?>
                        </a>
                    <?php else: ?>
                        <a class="button button--icon account-button" href="<?= url('/login') ?>" aria-label="<?= $isEn ? 'Sign in' : 'تسجيل الدخول' ?>">
                            <?= fnd_icon('user-round', 21) ?>
                        </a>
                    <?php endif; ?>
                    <a class="button button--icon" href="<?= url('/wishlist') ?>" aria-label="<?= $isEn ? 'Wishlist' : 'المفضلة' ?>">
                        <?= fnd_icon('heart', 21) ?>
                        <span id="headerWishlistBadge" class="count-badge tumurna-wishlist-count <?= $wishlistCount === 0 ? 'hidden' : '' ?>"><?= $wishlistCount ?></span>
                    </a>
                    <button type="button" class="button button--icon" data-open="cart-drawer" aria-haspopup="dialog" aria-label="<?= $isEn ? 'Shopping cart' : 'سلة التسوق' ?> (<?= $cartCount ?>)">
                        <?= fnd_icon('shopping-bag', 21) ?>
                        <span id="headerCartBadge" class="count-badge tumurna-cart-badge <?= $cartCount === 0 ? 'hidden' : '' ?>"><?= $cartCount ?></span>
                    </button>
                    <a class="language-link" lang="<?= $otherLocale ?>" hreflang="<?= $otherLocale ?>" href="<?= url('/lang/' . $otherLocale) ?>"><?= $currentLocale === 'ar' ? 'English' : 'العربية' ?></a>
                </div>
                <?php endif; ?>
            </div>
        </header>
    </div>

    <!-- MAIN CONTENT -->
    <main id="main-content">
        <?= $content ?? '' ?>
    </main>

    <!-- FOOTER -->
    <?php if (!$authLayout): ?>
    <footer class="site-footer">
        <div class="pattern-background">
            <div class="container">
                <div class="footer-main">
                    <div class="footer-brand">
                        <a class="logo-link" href="<?= $homeUrl ?>">
                            <span class="brand-wordmark" role="img" aria-label="تمرنا Tamrna">
                                <img alt="" width="174" height="246" loading="lazy" src="<?= $customLogo ? esc_attr($customLogo) : asset('assets/brand/tamrna-logo-light.svg') ?>">
                            </span>
                        </a>
                        <p><?= $isEn
                            ? 'Saudi dates with an authentic taste you will never forget.'
                            : 'تمور سعودية .. بطعم أصيل لا يُنسى' ?></p>
                    </div>

                    <nav class="footer-group" aria-label="<?= $isEn ? 'About Tamrna' : 'عن تمرنا' ?>">
                        <h3><?= $isEn ? 'About Tamrna' : 'عن تمرنا' ?></h3>
                        <ul>
                            <li><a href="<?= url('/about') ?>"><?= $isEn ? 'Our Story' : 'من نحن' ?></a></li>
                            <li><a href="<?= url('/about#farms') ?>"><?= $isEn ? 'Our Farms' : 'مزارعنا' ?></a></li>
                            <li><a href="<?= url('/about#journey') ?>"><?= $isEn ? 'Our Journey' : 'رحلتنا' ?></a></li>
                            <li><a href="<?= url('/contact') ?>"><?= $isEn ? 'Contact Us' : 'تواصل معنا' ?></a></li>
                        </ul>
                    </nav>

                    <nav class="footer-group" aria-label="<?= $isEn ? 'Customer service' : 'خدمة العملاء' ?>">
                        <h3><?= $isEn ? 'Customer Service' : 'خدمة العملاء' ?></h3>
                        <ul>
                            <li><a href="<?= url('/contact') ?>"><?= $isEn ? 'Help Center' : 'مركز المساعدة' ?></a></li>
                            <li><a href="<?= url('/profile/orders') ?>"><?= $isEn ? 'My Orders' : 'طلباتي' ?></a></li>
                            <li><a href="<?= url('/profile') ?>"><?= $isEn ? 'My Account' : 'حسابي' ?></a></li>
                            <li><a href="<?= url('/wishlist') ?>"><?= $isEn ? 'Wishlist' : 'قائمة المفضلة' ?></a></li>
                            <li><a href="<?= url('/contact') ?>"><?= $isEn ? 'Shipping Inquiries' : 'الاستفسار عن الشحن' ?></a></li>
                        </ul>
                    </nav>

                    <nav class="footer-group" aria-label="<?= $isEn ? 'Quick links' : 'روابط سريعة' ?>">
                        <h3><?= $isEn ? 'Quick Links' : 'روابط سريعة' ?></h3>
                        <ul>
                            <?php foreach (($footerMenu ?: $navItems) as [$fHref, $fLabel, $fActive, $fNewTab]): ?>
                                <li><a href="<?= esc_attr(url($fHref)) ?>" <?= $fNewTab ? 'target="_blank" rel="noopener"' : '' ?>><?= esc_html($fLabel) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>

                    <aside class="footer-newsletter">
                        <h3><?= $isEn ? 'Accepted Payment Methods' : 'طرق الدفع المعتمدة' ?></h3>
                        <p class="footer-payment-note"><?= $isEn
                            ? 'Encrypted Saudi payment gateways licensed under SAMA standards.'
                            : 'بوابات دفع إلكترونية سعودية مشفرة ومصرحة وفق معايير البنك المركزي السعودي (ساما).' ?></p>
                        <div class="footer-payment" aria-label="<?= $isEn ? 'Payment methods' : 'وسائل الدفع' ?>">
                            <?php if ($payOn('enable_mada')): ?><span class="payment-mark payment-mark--mada">mada</span><?php endif; ?>
                            <?php if ($payOn('enable_credit_card')): ?>
                                <span class="payment-mark payment-mark--visa">VISA</span>
                                <span class="payment-mark payment-mark--mastercard"><i></i><i></i><span class="sr-only">Mastercard</span></span>
                            <?php endif; ?>
                            <?php if ($payOn('enable_apple_pay')): ?><span class="payment-mark payment-mark--text">Apple Pay</span><?php endif; ?>
                            <?php if ($payOn('enable_tabby')): ?><span class="payment-mark payment-mark--text">tabby</span><?php endif; ?>
                            <?php if ($payOn('enable_tamara')): ?><span class="payment-mark payment-mark--text">tamara</span><?php endif; ?>
                        </div>
                        <?php if ($taxNumber || $crNumber): ?>
                            <div class="footer-legal-numbers">
                                <?php if ($taxNumber): ?><span><?= $isEn ? 'VAT Number:' : 'الرقم الضريبي:' ?> <bdi><?= esc_html($taxNumber) ?></bdi></span><?php endif; ?>
                                <?php if ($crNumber): ?><span><?= $isEn ? 'Commercial Reg:' : 'السجل التجاري:' ?> <bdi><?= esc_html($crNumber) ?></bdi></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>

                <div class="footer-details">
                    <address class="footer-contact-actions" aria-label="<?= $isEn ? 'Contact Tamrna' : 'تواصل مع تمرنا' ?>">
                        <a class="footer-action" href="tel:<?= esc_attr($phoneDigits) ?>">
                            <?= fnd_icon('phone', 16) ?>
                            <bdi><?= htmlspecialchars($contactPhone) ?></bdi>
                        </a>
                        <a class="footer-action" href="mailto:<?= esc_attr($contactEmail) ?>">
                            <?= fnd_icon('mail', 16) ?><?= htmlspecialchars($contactEmail) ?>
                        </a>
                        <?php if ($whatsappDigits): ?>
                            <a class="footer-action" href="https://wa.me/<?= esc_attr($whatsappDigits) ?>" rel="noopener noreferrer" target="_blank">
                                <?= fnd_icon('message-circle', 16) ?><?= $isEn ? 'WhatsApp' : 'واتساب' ?>
                            </a>
                        <?php endif; ?>
                    </address>

                    <?php if ($socialLinks): ?>
                    <nav class="footer-social-actions" aria-label="<?= $isEn ? 'Follow Tamrna' : 'تابع تمرنا' ?>">
                        <?php foreach ($socialLinks as [$sName, $sIcon, $sUrl]): ?>
                            <a class="footer-icon-action" href="<?= esc_url($sUrl) ?>" rel="noopener noreferrer" target="_blank" aria-label="<?= $sName ?>" title="<?= $sName ?>">
                                <?= fnd_icon($sIcon, 16) ?><span class="sr-only"><?= $sName ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>

                </div>

                <div class="footer-bottom">
                    <small><?= htmlspecialchars($footerText) ?></small>
                    <span class="footer-location">
                        <?= fnd_icon('map-pin', 16) ?><?= esc_html($contactAddress) ?>
                    </span>
                    <span class="footer-signature">TAMRNA — SAUDI ARABIA</span>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <div class="cart-feedback" role="status" aria-live="polite" aria-atomic="true"><span id="tumurna-toast"></span></div>
</div>

<!-- MOBILE MENU DRAWER -->
<div class="global-overlay hidden" data-overlay></div>
<aside class="drawer menu-drawer hidden" id="menu-drawer" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" role="dialog" aria-modal="true" aria-label="<?= $isEn ? 'Menu' : 'القائمة' ?>">
    <div class="dialog-heading">
        <h2><?= $isEn ? 'Menu' : 'القائمة' ?></h2>
        <button type="button" class="button button--icon" data-close aria-label="<?= $isEn ? 'Close' : 'إغلاق' ?>"><?= fnd_icon('x', 21) ?></button>
    </div>
    <div class="drawer-scroll">
        <div class="drawer-account">
            <?php if ($user): ?>
                <div class="drawer-account-user">
                    <span class="account-avatar"><?= esc_html($userInitial) ?></span>
                    <div>
                        <strong><?= esc_html($user['name'] ?? '') ?></strong><br>
                        <small dir="ltr"><?= esc_html($user['phone'] ?? '') ?></small>
                    </div>
                </div>
                <div class="drawer-account-links">
                    <a class="button button--outline button--sm" href="<?= url('/profile') ?>"><?= $isEn ? 'My Profile' : 'الملف الشخصي' ?></a>
                    <a class="button button--outline button--sm" href="<?= url('/profile/orders') ?>"><?= $isEn ? 'My Orders' : 'طلباتي' ?></a>
                    <?php if (($user['role_id'] ?? 0) === 1): ?>
                        <a class="button button--primary button--sm" style="grid-column:1/-1" href="<?= url('/admin') ?>"><?= $isEn ? 'Dashboard' : 'لوحة الإدارة' ?></a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="muted"><?= $isEn ? 'Welcome to Tamrna' : 'أهلاً بك في تمرنا' ?></span>
                <a class="button button--primary" href="<?= url('/login') ?>"><?= fnd_icon('user-round', 18) ?><?= $isEn ? 'Sign In / Register' : 'تسجيل الدخول / إنشاء حساب' ?></a>
            <?php endif; ?>
        </div>
        <nav class="mobile-nav" aria-label="<?= $isEn ? 'Main menu' : 'القائمة' ?>">
            <?php foreach ($navItems as [$href, $label, $active, $newTab]): ?>
                <a href="<?= esc_attr(url($href)) ?>" <?= $active ? 'aria-current="page"' : '' ?> <?= $newTab ? 'target="_blank" rel="noopener"' : '' ?>><span><?= esc_html($label) ?></span><?= fnd_icon('arrow-left', 18, 'directional-arrow') ?></a>
            <?php endforeach; ?>
            <a href="<?= url('/wishlist') ?>"><span><?= $isEn ? 'My Wishlist' : 'قائمة المفضلة' ?></span><?= fnd_icon('heart', 18) ?></a>
            <a href="<?= url('/cart') ?>"><span><?= $isEn ? 'Shopping Cart' : 'سلة المشتريات' ?></span><?= fnd_icon('shopping-bag', 18) ?></a>
        </nav>
    </div>
    <div class="drawer-bottom">
        <a class="button button--outline" href="<?= url('/lang/' . $otherLocale) ?>"><?= fnd_icon('globe', 18) ?><?= $currentLocale === 'ar' ? 'English (EN)' : 'عربي (AR)' ?></a>
    </div>
</aside>

<!-- CART DRAWER -->
<aside class="drawer cart-drawer hidden" id="cart-drawer" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title">
    <div class="dialog-heading">
        <h2 id="cart-drawer-title"><?= $isEn ? 'Shopping cart' : 'سلة التسوق' ?> (<span id="cart-drawer-count"><?= $cartCount ?></span>)</h2>
        <button type="button" class="button button--icon" data-close aria-label="<?= $isEn ? 'Close' : 'إغلاق' ?>"><?= fnd_icon('x', 21) ?></button>
    </div>
    <div class="drawer-scroll" id="cart-drawer-body"></div>
    <div class="drawer-bottom" id="cart-drawer-bottom"></div>
</aside>

<!-- SEARCH DIALOG -->
<div class="dialog search-dialog hidden" id="search-dialog" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" role="dialog" aria-modal="true" aria-labelledby="search-dialog-title">
    <div class="dialog-heading">
        <h2 id="search-dialog-title"><?= $isEn ? 'Search the store' : 'ابحث في المتجر' ?></h2>
        <button type="button" class="button button--icon" data-close aria-label="<?= $isEn ? 'Close' : 'إغلاق' ?>"><?= fnd_icon('x', 21) ?></button>
    </div>
    <form action="<?= url('/catalog') ?>" method="GET">
        <div class="catalog-search">
            <?= fnd_icon('search', 18) ?>
            <input class="input" type="search" name="search" autocomplete="off" placeholder="<?= $isEn ? 'Search dates (Ajwa, Sukkari, Gift Boxes...)' : 'ابحث عن نوع التمر (عجوة، سكري، بوكسات...)' ?>">
        </div>
        <div class="search-suggestions">
            <a href="<?= url('/catalog?search=' . rawurlencode($isEn ? 'Ajwa' : 'عجوة')) ?>"><?= $isEn ? 'Ajwa' : 'عجوة' ?></a>
            <a href="<?= url('/catalog?search=' . rawurlencode($isEn ? 'Sukkari' : 'سكري')) ?>"><?= $isEn ? 'Sukkari' : 'سكري' ?></a>
            <a href="<?= url('/catalog?search=' . rawurlencode($isEn ? 'Medjool' : 'مجدول')) ?>"><?= $isEn ? 'Medjool' : 'مجدول' ?></a>
            <a href="<?= url('/catalog?filter=preorder') ?>"><?= $isEn ? 'Pre-orders' : 'الحجز المسبق' ?></a>
        </div>
        <button type="submit" class="button button--primary"><?= $isEn ? 'Search' : 'بحث' ?></button>
    </form>
</div>

<!-- JavaScript Assets -->
<script src="<?= asset('assets/js/tumurna.js') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/js/tumurna.js') ?>"></script>
<script>
    // Header: switch immersive header to its solid state on scroll
    (function () {
        var header = document.getElementById('header-system');
        if (!header || !header.classList.contains('header-system--immersive')) return;
        var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 40); };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    })();

    // Drawer / dialog open-close
    (function () {
        var overlay = document.querySelector('[data-overlay]');
        var active = null;
        function open(id) {
            var el = document.getElementById(id);
            if (!el) return;
            active = el;
            el.classList.remove('hidden');
            if (overlay) overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (id === 'cart-drawer' && window.tumurnaRefreshCart) window.tumurnaRefreshCart();
            if (id === 'search-dialog') { var i = el.querySelector('input'); if (i) setTimeout(function () { i.focus(); }, 30); }
        }
        function close() {
            if (!active) return;
            active.classList.add('hidden');
            if (overlay) overlay.classList.add('hidden');
            document.body.style.overflow = '';
            active = null;
        }
        document.addEventListener('click', function (e) {
            var openBtn = e.target.closest('[data-open]');
            if (openBtn) { e.preventDefault(); open(openBtn.getAttribute('data-open')); return; }
            if (e.target.closest('[data-close]') || e.target === overlay) { close(); return; }
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
        window.tumurnaCloseOverlay = close;
        window.tumurnaOpenOverlay = open;
    })();

    // Live Add to Cart AJAX Helper
    window.tumurnaAddToCart = function(productId, variantId, qty, btn) {
        qty = qty || 1;
        const formData = new FormData();
        if (variantId) formData.append('variant_id', variantId);
        if (productId) formData.append('product_id', productId);
        formData.append('quantity', qty);
        if (btn) btn.disabled = true;

        return fetch(window.appUrl('/api/cart/add'), { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.tumurnaSyncCartBadge(data.cart);
                window.showTumurnaToast(data.message || 'تمت الإضافة للسلة بنجاح', 'success');
                var drawer = document.getElementById('cart-drawer');
                if (window.tumurnaOpenOverlay && drawer && drawer.classList.contains('hidden')) window.tumurnaOpenOverlay('cart-drawer');
            } else {
                window.showTumurnaToast(data.message || 'حدث خطأ أثناء الإضافة', 'error');
            }
            return data;
        })
        .catch(err => {
            console.error(err);
            window.showTumurnaToast('تعذر الاتصال بالخادم', 'error');
        })
        .finally(() => { if (btn) btn.disabled = false; });
    };


    // ---- Cart drawer ------------------------------------------------------
    (function () {
        var T = <?= json_encode([
            'empty' => $isEn ? 'Your cart is empty' : 'السلة فارغة',
            'emptyText' => $isEn ? 'Add products to start your order.' : 'أضف منتجات من المتجر لتبدأ طلبك.',
            'browse' => $isEn ? 'Browse products' : 'تصفح المنتجات',
            'subtotal' => $isEn ? 'Subtotal' : 'المجموع الفرعي',
            'discount' => $isEn ? 'Discount' : 'الخصم',
            'note' => $isEn ? 'Shipping is calculated at checkout based on your address and delivery method.' : 'يُحدَّد الشحن عند إتمام الطلب وفق العنوان وطريقة التوصيل.',
            'checkout' => $isEn ? 'Checkout' : 'إتمام الطلب',
            'viewCart' => $isEn ? 'View full cart' : 'عرض السلة كاملة',
            'remove' => $isEn ? 'Remove item' : 'حذف المنتج',
            'dec' => $isEn ? 'Decrease quantity' : 'تقليل الكمية',
            'inc' => $isEn ? 'Increase quantity' : 'زيادة الكمية',
            'preorder' => $isEn ? 'Pre-order' : 'حجز مسبق',
            'cartUrl' => url('/cart'),
            'checkoutUrl' => url('/checkout'),
            'catalogUrl' => url('/catalog'),
            'fallbackImg' => asset('assets/images/home/ajwa.webp'),
        ], JSON_UNESCAPED_UNICODE) ?>;
        var ICONS = {
            minus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>',
            plus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
            trash: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>',
            bag: '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>'
        };
        function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; }); }
        function img(src) {
            if (!src) return T.fallbackImg;
            if (/^(https?:)?\/\//.test(src)) return src;
            return window.appAsset(String(src));
        }

        window.tumurnaSyncCartBadge = function (cart) {
            var count = cart ? (cart.count || 0) : 0;
            var badge = document.getElementById('headerCartBadge');
            if (badge) { badge.innerText = count; badge.classList.toggle('hidden', !(count > 0)); }
            var c = document.getElementById('cart-drawer-count');
            if (c) c.innerText = count;
        };

        function render(cart) {
            window.tumurnaSyncCartBadge(cart);
            var body = document.getElementById('cart-drawer-body');
            var bottom = document.getElementById('cart-drawer-bottom');
            if (!body || !bottom) return;
            var items = (cart && cart.items) || [];

            if (!items.length) {
                body.innerHTML = '<div class="cart-empty">' + ICONS.bag + '<h3>' + esc(T.empty) + '</h3><p>' + esc(T.emptyText) + '</p><a class="button button--primary" href="' + esc(T.catalogUrl) + '">' + esc(T.browse) + '</a></div>';
                bottom.innerHTML = '';
                bottom.classList.add('hidden');
                return;
            }
            bottom.classList.remove('hidden');

            body.innerHTML = '<ul class="cart-items">' + items.map(function (it) {
                var key = esc(it.key);
                return '<li class="cart-item" data-key="' + key + '">' +
                    '<img src="' + esc(img(it.image)) + '" alt="' + esc(it.product_name) + '">' +
                    '<div class="cart-item-info">' +
                        '<h3>' + esc(it.product_name) + '</h3>' +
                        '<span class="muted">' + esc(it.size_name || '') + (it.is_preorder ? ' · ' + esc(T.preorder) : '') + '</span>' +
                        '<div class="product-price"><span>' + esc(it.unit_price_formatted) + '</span></div>' +
                        '<div class="quantity-stepper" role="group">' +
                            '<button type="button" class="button button--icon" data-cart-qty="' + (it.quantity - 1) + '" aria-label="' + esc(T.dec) + '">' + ICONS.minus + '</button>' +
                            '<output>' + esc(it.quantity) + '</output>' +
                            '<button type="button" class="button button--icon" data-cart-qty="' + (it.quantity + 1) + '" aria-label="' + esc(T.inc) + '">' + ICONS.plus + '</button>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="button button--icon cart-item-remove" data-cart-remove aria-label="' + esc(T.remove) + '" title="' + esc(T.remove) + '">' + ICONS.trash + '</button>' +
                '</li>';
            }).join('') + '</ul>';

            var html = '<div class="subtotal"><span>' + esc(T.subtotal) + '</span><strong>' + esc(cart.subtotal_formatted) + '</strong></div>';
            if (cart.discount && cart.discount > 0) {
                html += '<div class="subtotal" style="color:var(--color-success)"><span>' + esc(T.discount) + '</span><strong>- ' + esc(cart.discount_formatted) + '</strong></div>';
            }
            html += '<p class="muted">' + esc(T.note) + '</p>' +
                '<a class="button button--primary" href="' + esc(T.checkoutUrl) + '">' + esc(T.checkout) + '</a>' +
                '<a class="button button--outline" href="' + esc(T.cartUrl) + '">' + esc(T.viewCart) + '</a>';
            bottom.innerHTML = html;
        }

        window.tumurnaRefreshCart = function () {
            return fetch(window.appUrl('/api/cart'))
                .then(function (r) {
                    if (!r.ok || !(r.headers.get('content-type') || '').includes('application/json')) {
                        throw new Error('Cart API returned HTTP ' + r.status);
                    }
                    return r.json();
                })
                .then(function (d) { if (d.success) render(d.cart); })
                .catch(function (e) { console.error(e); });
        };

        function post(path, data) {
            var fd = new FormData();
            Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
            return fetch(window.appUrl(path), { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d.success) render(d.cart); else window.showTumurnaToast(d.message || 'Error', 'error'); })
                .catch(function (e) { console.error(e); window.showTumurnaToast('Error', 'error'); });
        }

        document.addEventListener('click', function (e) {
            var li = e.target.closest('#cart-drawer .cart-item');
            if (!li) return;
            var key = li.getAttribute('data-key');
            var qtyBtn = e.target.closest('[data-cart-qty]');
            if (qtyBtn) { post('/api/cart/update', { key: key, quantity: qtyBtn.getAttribute('data-cart-qty') }); return; }
            if (e.target.closest('[data-cart-remove]')) { post('/api/cart/remove', { key: key }); }
        });
    })();

    // Show / hide password fields
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-password-toggle]');
        if (!btn) return;
        var input = btn.parentNode.querySelector('input');
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    });

    // Floating toast (Foundation "cart-feedback")
    window.showTumurnaToast = function(message, type = 'success') {
        const span = document.getElementById('tumurna-toast');
        if (!span) return;
        span.textContent = '';
        span.removeAttribute('data-type');
        void span.offsetWidth;
        span.setAttribute('data-type', type);
        span.textContent = message;
    };
    window.showToast = window.showTumurnaToast;

    // Live Wishlist Toggle
    window.tumurnaToggleWishlist = function(productId, btn) {
        const formData = new FormData();
        formData.append('product_id', productId);

        fetch(window.appUrl('/api/wishlist/toggle'), { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.requires_login) {
                window.showTumurnaToast(data.message, 'info');
                setTimeout(() => { window.location.href = data.redirect || window.appUrl('/login'); }, 1000);
                return;
            }

            if (data.success) {
                document.querySelectorAll('#headerWishlistBadge, .tumurna-wishlist-count').forEach(badge => {
                    badge.innerText = data.count;
                    badge.classList.toggle('hidden', !(data.count > 0));
                });

                document.querySelectorAll(`[data-wishlist-id="${productId}"]`).forEach(b => {
                    const isEn = document.documentElement.lang === 'en';
                    b.classList.toggle('is-saved', !!data.in_wishlist);
                    b.setAttribute('aria-pressed', data.in_wishlist ? 'true' : 'false');
                    b.setAttribute('title', data.in_wishlist ? (isEn ? 'Remove from Wishlist' : 'إزالة من المفضلة') : (isEn ? 'Add to Wishlist' : 'إضافة للمفضلة'));
                    const label = b.querySelector('[data-wishlist-label]');
                    if (label) label.textContent = data.in_wishlist ? (isEn ? 'Remove from wishlist' : 'إزالة من المفضلة') : (isEn ? 'Add to wishlist' : 'إضافة إلى المفضلة');
                });

                if (window.location.pathname.includes('/wishlist') && !data.in_wishlist) {
                    const card = btn ? btn.closest('.product-card') : null;
                    if (card) {
                        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => { card.remove(); if (data.count === 0) location.reload(); }, 300);
                    }
                }

                window.showTumurnaToast(data.message, data.in_wishlist ? 'success' : 'info');
            } else {
                window.showTumurnaToast(data.message || 'حدث خطأ', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            window.showTumurnaToast('تعذر تحديث المفضلة', 'error');
        });
    };
</script>
</body>
</html>
