<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';

$accountActive = 'settings';
$accountHeading = $en ? 'Settings' : 'الإعدادات';
$accountSub = $en ? 'Manage notification preferences and interface language' : 'تحكم في تفضيلات الإشعارات ولغة الواجهة';
include __DIR__ . '/../../components/account_shell_open.php';
?>
<section class="account-card account-wide-card">
    <header>
        <span><?= fnd_icon('settings', 21) ?></span>
        <h2><?= $en ? 'Settings' : 'الإعدادات' ?></h2>
    </header>

    <form action="<?= url('/profile/settings/save') ?>" method="POST">
        <div class="account-settings">
            <section>
                <h3><?= $en ? 'Notification preferences' : 'تفضيلات الإشعارات' ?></h3>
                <p><?= $en ? 'Choose the alerts shown in your account.' : 'تحكم في أنواع التنبيهات التي ترغب في رؤيتها داخل حسابك.' ?></p>
                <label class="choice">
                    <input type="checkbox" name="notif_orders" value="1" <?= !empty($settings['orders']) ? 'checked' : '' ?>>
                    <span><?= $en ? 'Order and shipping updates' : 'تحديثات الطلب والشحن' ?></span>
                </label>
                <label class="choice">
                    <input type="checkbox" name="notif_gifts" value="1" <?= !empty($settings['gifts']) ? 'checked' : '' ?>>
                    <span><?= $en ? 'Gift-card updates' : 'تحديثات بطاقات الهدايا' ?></span>
                </label>
                <label class="choice">
                    <input type="checkbox" name="notif_marketing" value="1" <?= !empty($settings['marketing']) ? 'checked' : '' ?>>
                    <span><?= $en ? 'Offers and marketing messages' : 'رسائل العروض والتسويق' ?></span>
                </label>
            </section>

            <section>
                <h3><?= $en ? 'Language' : 'اللغة' ?></h3>
                <p><?= $en ? 'Change the account interface language.' : 'يمكن تغيير لغة واجهة الحساب من الرابط التالي.' ?></p>
                <a href="<?= url('/lang/' . ($en ? 'ar' : 'en')) ?>" class="button button--outline">
                    <?= $en ? 'العربية' : 'English' ?>
                </a>
            </section>
        </div>

        <button type="submit" class="button button--primary account-section-action">
            <?= $en ? 'Save settings' : 'حفظ الإعدادات' ?>
        </button>

        <?php if (!empty($success)): ?>
            <p class="field-success" role="status" style="margin-top:12px"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
    </form>
</section>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
