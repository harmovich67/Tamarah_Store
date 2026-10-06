<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$currency = $en ? 'SAR' : 'ر.س';
$arrow = 'arrow-left'; // mirrored for LTR by .directional-arrow

$accountActive = 'profile';
$accountHeading = $en ? 'Profile Overview' : 'الملف الشخصي';
$accountSub = $en ? 'Manage your details, addresses and recent orders' : 'إدارة بياناتك وعناوينك وأحدث طلباتك';
include __DIR__ . '/../../components/account_shell_open.php';
?>
<?php if (!empty($success)): ?>
    <div class="flash flash--success" role="status"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
<?php endif; ?>

<div class="account-profile-grid">
    <!-- Personal info -->
    <section class="account-card">
        <header><span><?= fnd_icon('user-round', 21) ?></span><h2><?= $en ? 'Personal Details' : 'البيانات الشخصية والحساب' ?></h2></header>
        <form action="<?= url('/profile/update-info') ?>" method="POST" class="account-form">
            <div class="form-field">
                <label for="pf-name"><?= $en ? 'Full Name' : 'الاسم الكامل' ?> *</label>
                <input id="pf-name" class="input" type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-field">
                <label for="pf-phone"><?= $en ? 'Saudi Mobile Number' : 'رقم الجوال السعودي' ?></label>
                <input id="pf-phone" class="input" type="text" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" readonly dir="ltr" style="text-align:start">
                <span class="field-hint"><?= $en ? 'Linked to OTP authentication' : 'رقم الجوال موثق برمز التحقق OTP' ?></span>
            </div>
            <div class="form-field">
                <label for="pf-email"><?= $en ? 'Email Address' : 'البريد الإلكتروني' ?></label>
                <input id="pf-email" class="input" type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" dir="ltr" style="text-align:start">
            </div>
            <div class="form-field">
                <label for="pf-pass"><?= $en ? 'New Password (Optional)' : 'كلمة مرور جديدة (اختياري)' ?></label>
                <input id="pf-pass" class="input" type="password" name="password" placeholder="••••••••" dir="ltr" style="text-align:start" autocomplete="new-password">
                <span class="field-hint"><?= $en ? 'Leave blank if you do not wish to change password' : 'اتركه فارغاً إذا كنت لا ترغب بتغيير كلمة المرور' ?></span>
            </div>
            <button type="submit" class="button button--primary"><?= fnd_icon('check', 18) ?><?= $en ? 'Save Changes' : 'حفظ التغييرات' ?></button>
        </form>
    </section>

    <!-- Default address -->
    <section class="account-card">
        <header><span><?= fnd_icon('map-pin', 21) ?></span><h2><?= $en ? 'Default Delivery Address' : 'عنوان التوصيل المعتمد' ?></h2></header>
        <?php if ($defaultAddress): ?>
            <div class="account-address-grid" style="grid-template-columns:1fr">
                <article class="is-default">
                    <span><?= $en ? 'Default' : 'الافتراضي' ?></span>
                    <h3><?= htmlspecialchars($defaultAddress['title'] ?: ($en ? 'Home' : 'المنزل')) ?></h3>
                    <p dir="ltr" style="text-align:start"><?= htmlspecialchars($defaultAddress['phone']) ?></p>
                    <p><?= htmlspecialchars($defaultAddress['street_address'] ?: ($defaultAddress['address'] ?? '')) ?><?= !empty($defaultAddress['building_floor']) ? ' - ' . htmlspecialchars($defaultAddress['building_floor']) : '' ?></p>
                    <small><?= htmlspecialchars($defaultAddress['city'] ?: 'الرياض') ?><?= !empty($defaultAddress['landmark']) ? ' · ' . htmlspecialchars($defaultAddress['landmark']) : '' ?></small>
                </article>
            </div>
            <a class="button button--outline account-section-action" href="<?= url('/profile/addresses') ?>"><?= $en ? 'Manage Addresses' : 'إدارة العناوين' ?> (<?= (int)$addressesCount ?>)</a>
        <?php else: ?>
            <div class="account-empty">
                <?= fnd_icon('map-pin', 30, '', 1.4) ?>
                <p><?= $en ? 'No delivery addresses saved yet' : 'لم تقم بحفظ أي عنوان وطني بعد' ?></p>
                <a href="<?= url('/profile/addresses') ?>" class="button button--primary button--sm" style="margin-top:10px"><?= fnd_icon('plus', 16) ?><?= $en ? 'Add Delivery Address' : 'إضافة عنوان توصيل' ?></a>
            </div>
        <?php endif; ?>
        <div class="account-review-note" style="margin-top:14px;margin-bottom:0">
            <?= fnd_icon('snowflake', 14) ?> <?= $en ? 'Cold express shipping guarantees freshness across KSA.' : 'شحن مبرد فاخر وسريع يضمن وصول التمور الملكية طازجة إلى بابك.' ?>
        </div>
    </section>

    <!-- Recent orders -->
    <section class="account-card account-wide-card">
        <header><span><?= fnd_icon('package', 21) ?></span><h2><?= $en ? 'Recent Orders' : 'أحدث الطلبات' ?></h2></header>
        <div class="account-card-actions"><a class="button button--outline" href="<?= url('/profile/orders') ?>"><?= $en ? 'View All Orders' : 'عرض كافة الطلبات' ?><?= fnd_icon($arrow, 15, 'directional-arrow') ?></a></div>

        <?php if (empty($recentOrders)): ?>
            <div class="account-empty">
                <p><?= $en ? 'No orders yet' : 'لم تقم بإنشاء أي طلبات حتى الآن' ?></p>
                <a href="<?= url('/catalog') ?>" class="button button--primary button--sm" style="margin-top:10px"><?= $en ? 'Explore Luxury Dates' : 'تصفح أصناف التمور الفاخرة' ?></a>
            </div>
        <?php else: ?>
            <div class="account-orders-table">
                <?php foreach ($recentOrders as $ro): ?>
                    <a class="order-row" href="<?= url('/profile/order/' . $ro['order_number']) ?>">
                        <b dir="ltr"><?= htmlspecialchars($ro['order_number']) ?></b>
                        <span><?= date('Y-m-d', strtotime($ro['created_at'])) ?></span>
                        <span><?= (int)$ro['items_count'] ?> <?= $en ? 'items' : 'أصناف' ?></span>
                        <strong><?= number_format((float)$ro['total'], 2) ?> <?= $currency ?></strong>
                        <em class="status-pill" data-status="<?= esc_attr(strtolower((string)($ro['shipping_status'] ?? 'pending'))) ?>"><?= htmlspecialchars($ro['shipping_status'] ?: ($en ? 'Processing' : 'قيد التجهيز')) ?></em>
                        <i style="font-style:normal;text-decoration:underline;text-underline-offset:4px"><?= $en ? 'Details' : 'التفاصيل' ?></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
