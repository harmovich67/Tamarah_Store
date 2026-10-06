<?php
/**
 * Tamrna Foundation account hub (opening shell)
 * Expects: $accountActive (profile|orders|addresses), $accountHeading, $accountSub, optional $accountAction (html)
 */
$_loc = \App\Core\I18n::getLocale();
$_en = $_loc === 'en';
$_isRtl = \App\Core\I18n::isRtl();
$_user = $user ?? \App\Core\Auth::user();
$_nameParts = preg_split('/\s+/u', trim((string)($_user['name'] ?? '')));
$_first = $_nameParts[0] ?? '';
$_initials = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(array_filter($_nameParts), 0, 2)));
$_nav = [
    'profile' => [url('/profile'), $_en ? 'Profile' : 'الملف الشخصي', 'user-round', null],
    'orders' => [url('/profile/orders'), $_en ? 'My Orders' : 'طلباتي', 'package', $ordersCount ?? (isset($orders) ? count($orders) : null)],
    'addresses' => [url('/profile/addresses'), $_en ? 'My addresses' : 'عناويني', 'map-pin', $addressesCount ?? (isset($addresses) ? count($addresses) : null)],
    'wishlist' => [url('/wishlist'), $_en ? 'Wishlist' : 'المفضلة', 'heart', null],
];
?>
<section class="account-hub">
    <div class="account-ornament"></div>
    <div class="container">
        <nav class="account-breadcrumb" aria-label="<?= $_en ? 'Breadcrumb' : 'مسار الصفحة' ?>">
            <a href="<?= url('/') ?>"><?= $_en ? 'Home' : 'الرئيسية' ?></a>
            <?= fnd_icon($_isRtl ? 'chevron-left' : 'chevron-right', 24) ?>
            <a href="<?= url('/profile') ?>"><?= $_en ? 'My Account' : 'حسابي' ?></a>
            <?= fnd_icon($_isRtl ? 'chevron-left' : 'chevron-right', 24) ?>
            <span><?= esc_html($accountHeading ?? '') ?></span>
        </nav>

        <header class="account-welcome">
            <span><?= $_en ? 'Tamrna account' : 'حساب تمرنا' ?></span>
            <h1><?= $_en ? 'Welcome, ' : 'مرحبًا ' ?><?= esc_html($_first) ?></h1>
            <p><?= $_en ? 'We are glad you are here. Manage your details, addresses and orders in one place.' : 'يسعدنا وجودك معنا. راجع بياناتك وعناوينك وطلباتك من مكان واحد.' ?></p>
        </header>

        <div class="account-shell">
            <aside class="account-sidebar">
                <nav aria-label="<?= $_en ? 'Account menu' : 'قائمة الحساب' ?>">
                    <?php foreach ($_nav as $key => [$href, $label, $icon, $count]): ?>
                        <a href="<?= $href ?>" class="<?= ($accountActive ?? '') === $key ? 'is-active' : '' ?>">
                            <?= fnd_icon($icon, 20, '', 1.6) ?>
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <a class="button button--outline account-logout" href="<?= url('/logout') ?>">
                    <?= fnd_icon('log-out', 18) ?><?= $_en ? 'Sign out' : 'تسجيل الخروج' ?>
                </a>
                <a class="account-gift-promo" href="<?= url('/gift-cards') ?>">
                    <img src="<?= asset('assets/images/home/gifting.webp') ?>" alt="" loading="lazy">
                    <span><?= fnd_icon('gift', 22) ?><?= $_en ? 'Share Tamrna gifting' : 'أهدِ من تحب أصالة تمرنا' ?></span>
                    <b><?= $_en ? 'Browse gift cards' : 'تصفح بطاقات الهدايا' ?></b>
                </a>
            </aside>

            <div class="account-content">
                <section class="account-summary-card">
                    <div class="account-avatar" aria-hidden="true"><?= esc_html($_initials ?: mb_substr((string)($_user['name'] ?? 'U'), 0, 1)) ?></div>
                    <div>
                        <h2><?= esc_html($_user['name'] ?? '') ?></h2>
                        <?php if (!empty($_user['email'])): ?><a href="mailto:<?= esc_attr($_user['email']) ?>"><?= esc_html($_user['email']) ?></a><?php endif; ?>
                        <?php if (!empty($_user['phone'])): ?><a dir="ltr" href="tel:<?= esc_attr($_user['phone']) ?>"><?= esc_html($_user['phone']) ?></a><?php endif; ?>
                        <small><?= fnd_icon('shield-check', 14) ?><?= $_en ? 'Verified account' : 'حساب موثق' ?></small>
                    </div>
                </section>
