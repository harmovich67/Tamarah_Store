<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$currency = $en ? 'SAR' : 'ر.س';
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';

$accountActive = 'orders';
$accountHeading = $en ? 'My Orders' : 'سجل طلباتي';
$accountSub = $en ? 'Track your luxury dates shipments and view past invoices' : 'متابعة شحنات التمور الملكية والاطلاع على فواتيرك السابقة';
$accountAction = '<a class="button button--primary" href="' . url('/catalog') . '">' . fnd_icon('sparkles', 18) . ($en ? 'Order Fresh Dates' : 'طلب تمور ملكية جديدة') . '</a>';
include __DIR__ . '/../../components/account_shell_open.php';
?>
<?php if (empty($orders)): ?>
    <div class="account-card" style="text-align:center;padding:40px 20px">
        <?= fnd_icon('package', 44, '', 1.3) ?>
        <h2 style="margin-top:10px"><?= $en ? 'No orders yet' : 'لم تقم بإنشاء أي طلبات حتى الآن' ?></h2>
        <p class="muted" style="margin:8px auto 18px;max-width:30rem;line-height:1.8"><?= $en ? 'Discover our royal dates collections, Medina Ajwa, and special gift boxes.' : 'استكشف تشكيلات التمور الملكية وحصاد القصيم والمدينة المنورة وبوكسات الإهداء الفاخرة.' ?></p>
        <a href="<?= url('/catalog') ?>" class="button button--primary"><?= $en ? 'Browse Dates Catalog' : 'تصفح تشكيلات التمور' ?></a>
    </div>
<?php else: ?>
    <div class="account-order-cards" style="grid-template-columns:1fr">
        <?php foreach ($orders as $order):
            $shippingStatus = strtolower($order['shipping_status'] ?? 'pending');
            $paymentMethod = $order['payment_method'] ?? 'cod';
            $isPaid = ($order['payment_status'] ?? '') === 'paid';
            $paymentLabel = $isPaid
                ? ($en ? 'Paid via ' . strtoupper(str_replace('_', ' ', $paymentMethod)) : 'مدفوع إلكترونياً (' . ($paymentMethod === 'apple_pay' ? 'أبل باي' : ($paymentMethod === 'tamara' ? 'تمارا' : ($paymentMethod === 'tabby' ? 'تابي' : 'مدى / بطاقة'))) . ')')
                : ($en ? 'Cash On Delivery' : 'الدفع عند الاستلام');
            ?>
            <article class="account-order-card">
                <header>
                    <div>
                        <b dir="ltr"><?= htmlspecialchars($order['order_number']) ?></b>
                        <small><?= date('d M Y - H:i', strtotime($order['created_at'])) ?></small>
                    </div>
                    <em data-status="<?= esc_attr($shippingStatus) ?>"><?= htmlspecialchars($order['shipping_status'] ?: ($en ? 'Processing' : 'قيد التجهيز')) ?></em>
                </header>
                <dl>
                    <div><dt><?= $en ? 'Total Amount' : 'المبلغ الإجمالي' ?></dt><dd><?= number_format($order['total'], 2) ?> <?= $currency ?></dd></div>
                    <div><dt><?= $en ? 'Payment' : 'الدفع' ?></dt><dd><?= $paymentLabel ?></dd></div>
                </dl>
                <?php if (!empty($order['products_snippet'])): ?>
                    <p><b><?= $en ? 'Items' : 'محتويات الطلب' ?>:</b> <?= htmlspecialchars($order['products_snippet']) ?></p>
                <?php endif; ?>
                <div class="account-order-tracking">
                    <span><?= fnd_icon('snowflake', 14) ?> <?= $en ? 'Cold Express Delivery' : 'شحن مبرد فاخر' ?></span>
                    <a class="button button--outline button--sm" href="<?= url('/profile/order/' . $order['order_number']) ?>"><?= $en ? 'Order Details' : 'تفاصيل وتتبع الطلب' ?><?= fnd_icon($arrow, 15, 'directional-arrow') ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
