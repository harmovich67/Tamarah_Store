<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$currency = $en ? 'SAR' : 'ر.س';
$statusLabels = [
    'unused' => $en ? 'Available' : 'صالحة للاستخدام',
    'active' => $en ? 'Available' : 'صالحة للاستخدام',
    'used' => $en ? 'Used' : 'مستخدمة',
    'expired' => $en ? 'Expired' : 'منتهية',
];

$accountActive = 'gift-cards';
$accountHeading = $en ? 'Gift cards' : 'بطاقات الهدايا';
$accountSub = $en ? 'Review your gift cards, status and redemption rules' : 'بطاقات الهدايا الخاصة بي وتفاصيلها وقواعد استخدامها';
include __DIR__ . '/../../components/account_shell_open.php';
?>
<section class="account-card account-wide-card">
    <header>
        <span><?= fnd_icon('gift', 21) ?></span>
        <h2><?= $en ? 'My gift cards' : 'بطاقات الهدايا الخاصة بي' ?></h2>
    </header>
    <p class="account-review-note">
        <?= $en ? 'Review fixtures for gift-card states. Codes cannot be redeemed.' : 'بطاقات تجريبية لمراجعة تصميم حالات البطاقة. الأكواد غير قابلة للاستخدام الفعلي.' ?>
    </p>

    <div class="account-gift-grid">
        <?php foreach ($cards as $card): 
            $st = in_array($card['status'], ['used', 'expired'], true) ? $card['status'] : 'unused';
            $isDigital = ($card['card_type'] ?? 'digital') === 'digital';
        ?>
            <article data-status="<?= esc_attr($st) ?>">
                <div class="account-gift-visual">
                    <?= fnd_icon('gift', 30) ?>
                    <span><?= $isDigital ? ($en ? 'Digital' : 'رقمية') : ($en ? 'Printed' : 'مطبوعة') ?></span>
                    <strong><?= number_format((float)$card['amount'], 0) ?> <?= $currency ?></strong>
                </div>
                <div class="account-gift-details">
                    <header>
                        <h3><?= esc_html($card['recipient_name'] ?: ($en ? 'Gift Recipient' : 'مستلم الهدية')) ?></h3>
                        <em><?= esc_html($statusLabels[$card['status']] ?? $statusLabels[$st]) ?></em>
                    </header>
                    <dl>
                        <div>
                            <dt><?= $en ? 'Review code' : 'الكود التجريبي' ?></dt>
                            <dd dir="ltr"><?= esc_html($card['code']) ?></dd>
                        </div>
                        <div>
                            <dt><?= $en ? 'Order number' : 'رقم الطلب' ?></dt>
                            <dd dir="ltr">#<?= esc_html($card['order_number'] ?? '1024') ?></dd>
                        </div>
                        <div>
                            <dt><?= $en ? 'Issued' : 'تاريخ الإصدار' ?></dt>
                            <dd><?= esc_html(date('Y-m-d', strtotime($card['created_at'] ?? 'now'))) ?></dd>
                        </div>
                        <div>
                            <dt><?= $en ? 'Validity' : 'الصلاحية' ?></dt>
                            <dd>365 <?= $en ? 'days' : 'يومًا' ?></dd>
                        </div>
                    </dl>
                    <?php if (!empty($card['message'])): ?>
                        <p><?= esc_html($card['message']) ?></p>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="account-gift-policy">
        <?= fnd_icon('shield-check', 22) ?>
        <div>
            <strong><?= $en ? 'Redemption rule' : 'قاعدة الاستخدام' ?></strong>
            <p><?= $en ? 'The code is issued after successful payment, is single-use, and its full value must be redeemed in one transaction. Digital and printed types are enabled.' : 'يُنشأ الكود بعد نجاح الدفع، ويُستخدم مرة واحدة، ويجب استخدام كامل قيمة البطاقة في معاملة واحدة. الأنواع المفعلة حاليًا: رقمية ومطبوعة.' ?></p>
        </div>
    </div>

    <a class="button button--primary account-section-action" href="<?= url('/gift-cards') ?>">
        <?= $en ? 'Buy a gift card' : 'شراء بطاقة إهداء' ?><?= fnd_icon($isRtl ? 'chevron-left' : 'chevron-right', 16) ?>
    </a>
</section>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
