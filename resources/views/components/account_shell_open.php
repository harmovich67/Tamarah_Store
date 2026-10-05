<?php
/**
 * Tamrna Foundation account hub (opening shell)
 * Expects: $accountActive (profile|orders|addresses), $accountHeading, $accountSub, optional $accountAction (html)
 */
$_loc = \App\Core\I18n::getLocale();
$_en = $_loc === 'en';
$_isRtl = \App\Core\I18n::isRtl();
$_user = $user ?? \App\Core\Auth::user();
$_nav = [
    'profile' => [url('/profile'), $_en ? 'Profile Overview' : 'الملف الشخصي', 'user-round', null],
    'orders' => [url('/profile/orders'), $_en ? 'My Orders' : 'طلباتي', 'package', $ordersCount ?? (isset($orders) ? count($orders) : null)],
    'addresses' => [url('/profile/addresses'), $_en ? 'Saved Addresses' : 'العناوين المحفوظة', 'map-pin', $addressesCount ?? (isset($addresses) ? count($addresses) : null)],
    'wishlist' => [url('/wishlist'), $_en ? 'Wishlist' : 'المفضلة', 'heart', null],
];
?>
<section class="account-hub">
    <div class="account-ornament"></div>
    <div class="container">
        <nav class="account-breadcrumb" aria-label="<?= $_en ? 'Breadcrumb' : 'مسار الصفحة' ?>">
            <a href="<?= url('/') ?>"><?= $_en ? 'Home' : 'الرئيسية' ?></a>
            <?= fnd_icon($_isRtl ? 'chevron-left' : 'chevron-right', 14) ?>
            <a href="<?= url('/profile') ?>"><?= $_en ? 'My Account' : 'حسابي' ?></a>
            <?php if (($accountActive ?? 'profile') !== 'profile'): ?>
                <?= fnd_icon($_isRtl ? 'chevron-left' : 'chevron-right', 14) ?>
                <span><?= esc_html($accountHeading ?? '') ?></span>
            <?php endif; ?>
        </nav>

        <header class="account-welcome" style="max-width:none;display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap">
            <div>
                <span><?= $_en ? 'My account' : 'حسابي' ?></span>
                <h1><?= esc_html($accountHeading ?? '') ?></h1>
                <?php if (!empty($accountSub)): ?><p><?= esc_html($accountSub) ?></p><?php endif; ?>
            </div>
            <?php if (!empty($accountAction)) echo $accountAction; ?>
        </header>

        <div class="account-shell">
            <aside class="account-sidebar">
                <nav aria-label="<?= $_en ? 'Account menu' : 'قائمة الحساب' ?>">
                    <?php foreach ($_nav as $key => [$href, $label, $icon, $count]): ?>
                        <a href="<?= $href ?>" class="<?= ($accountActive ?? '') === $key ? 'is-active' : '' ?>">
                            <?= fnd_icon($icon, 20, '', 1.6) ?>
                            <span><?= $label ?><?= $count !== null ? ' (' . (int)$count . ')' : '' ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <a class="button button--outline account-logout" href="<?= url('/logout') ?>">
                    <?= fnd_icon('log-out', 18) ?><?= $_en ? 'Sign out' : 'تسجيل الخروج' ?>
                </a>
                <a class="account-gift-promo" href="<?= url('/gift-cards') ?>">
                    <img src="<?= asset('assets/images/home/gifting.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%" loading="lazy">
                    <span><?= fnd_icon('gift', 22) ?><?= $_en ? 'Gift Cards' : 'بطاقات الإهداء' ?></span>
                    <b><?= $_en ? 'Discover now' : 'اكتشف الآن' ?></b>
                </a>
            </aside>

            <div class="account-content">
