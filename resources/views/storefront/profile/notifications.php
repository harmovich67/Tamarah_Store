<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';

$accountActive = 'notifications';
$accountHeading = $en ? 'Notifications' : 'الإشعارات';
$accountSub = $en ? 'Stay updated with your orders and fresh harvest announcements' : 'ابقَ على اطلاع بأحدث حالات طلباتك وتنبيهات المواسم والتمور الفاخرة';
include __DIR__ . '/../../components/account_shell_open.php';
?>
<section class="account-card account-wide-card">
    <header>
        <span><?= fnd_icon('bell', 21) ?></span>
        <h2><?= $en ? 'Notifications' : 'الإشعارات' ?></h2>
    </header>
    <div class="account-card-actions">
        <a class="button button--outline" href="<?= url('/profile/notifications?mark_all=1') ?>">
            <?= fnd_icon('check', 16) ?><?= $en ? 'Mark all read' : 'تحديد الكل كمقروء' ?>
        </a>
    </div>

    <div class="account-notification-list">
        <?php foreach ($notifications as $item): ?>
            <a href="<?= url('/profile/notifications?read=' . urlencode($item['id'])) ?>" class="account-notification-item <?= !empty($item['is_read']) ? 'is-read' : '' ?>" style="text-decoration:none">
                <i></i>
                <div>
                    <h3><?= esc_html($en ? ($item['title_en'] ?? $item['title']) : $item['title']) ?></h3>
                    <p><?= esc_html($en ? ($item['body_en'] ?? $item['body']) : $item['body']) ?></p>
                    <time><?= esc_html($item['created_at']) ?></time>
                </div>
                <span><?= !empty($item['is_read']) ? ($en ? 'Read' : 'مقروء') : ($en ? 'New' : 'جديد') ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
