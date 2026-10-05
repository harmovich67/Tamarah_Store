<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$currency = $en ? 'SAR' : 'ر.س';

// Stepper status calculation
$shippingStatus = strtolower($order['shipping_status'] ?? 'pending');
$step = 1;
if (in_array($shippingStatus, ['processing', 'ready_for_pickup', 'confirmed', 'قيد التجهيز', 'تم التأكيد'])) {
    $step = 2;
} elseif (in_array($shippingStatus, ['shipped', 'out_for_delivery', 'in_transit', 'تم الشحن', 'خرج للتوصيل'])) {
    $step = 3;
} elseif (in_array($shippingStatus, ['delivered', 'completed', 'تم التسليم'])) {
    $step = 4;
}

$showStepper = ($settings['enable_order_tracking_stepper'] ?? '1') == '1';
$allowCancel = (($settings['allow_customer_order_cancellation'] ?? '1') == '1') && in_array($shippingStatus, ['pending', 'قيد المراجعة', '']) && ($order['payment_status'] !== 'paid');

$accountActive = 'orders';
$accountHeading = ($en ? 'Order' : 'طلب رقم') . ': ' . $order['order_number'];
$accountSub = ($en ? 'Order Date' : 'تاريخ الطلب') . ': ' . date('d M Y - H:i', strtotime($order['created_at']));
$accountAction = '<div style="display:flex;gap:8px;flex-wrap:wrap">';
if ($allowCancel) {
    $accountAction .= '<form action="' . url('/profile/order/' . $order['order_number'] . '/cancel') . '" method="POST" onsubmit="return confirm(\'' . addslashes($en ? 'Are you sure you want to cancel this order?' : 'هل أنت متأكد من رغبتك في إلغاء هذا الطلب؟') . '\');"><button type="submit" class="button button--danger">' . fnd_icon('x-circle', 18) . ($en ? 'Cancel Order' : 'إلغاء الطلب') . '</button></form>';
}
$accountAction .= '<button type="button" onclick="window.print()" class="button button--outline">' . fnd_icon('file-text', 18) . ($en ? 'Print Invoice' : 'طباعة الفاتورة') . '</button></div>';
include __DIR__ . '/../../components/account_shell_open.php';

$steps = [
    ['check', $en ? 'Order Confirmed' : 'تم استلام الطلب', $en ? 'Order recorded in system' : 'تم تسجيل وتأكيد الطلب'],
    ['package', $en ? 'Cold Packaging' : 'التجهيز والتغليف المبرد', $en ? 'Fresh sorting and sealing' : 'فرز وتغليف التمور الفاخرة'],
    ['truck', $en ? 'Out for Delivery' : 'خرج للشحن المبرد', $en ? 'In refrigerated transit' : 'مع مندوب الشحن المبرد'],
    ['tree-palm', $en ? 'Delivered' : 'تم التسليم', $en ? 'Enjoy your royal dates' : 'بالصحة والعافية'],
];
?>
<?php if (!empty($cancelled)): ?>
    <div class="flash flash--success" role="status"><?= fnd_icon('check-circle', 18) ?><div><?= $en ? 'Order was successfully cancelled.' : 'تم إلغاء الطلب بنجاح.' ?></div></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="flash flash--error" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
<?php endif; ?>

<?php if ($showStepper && $shippingStatus !== 'cancelled'): ?>
    <section class="account-card">
        <header>
            <span><?= fnd_icon('snowflake', 21) ?></span>
            <h2><?= $en ? 'Cold Freight Tracking Timeline' : 'مراحل تجهيز وشحن الطلب المبرد' ?></h2>
        </header>
        <ol class="order-steps">
            <?php foreach ($steps as $i => [$sIcon, $sTitle, $sDesc]): $n = $i + 1; ?>
                <li class="<?= $step >= $n ? 'is-done' : '' ?> <?= $step === $n ? 'is-current' : '' ?>">
                    <span><?= fnd_icon($sIcon, 22) ?></span>
                    <strong><?= $sTitle ?></strong>
                    <small><?= $sDesc ?></small>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
<?php elseif ($shippingStatus === 'cancelled'): ?>
    <div class="flash flash--error" role="alert"><?= fnd_icon('alert-octagon', 20) ?><div><strong><?= $locale === 'ar' ? 'تم إلغاء هذا الطلب بناء على طلب العميل أو الإدارة.' : 'This order has been cancelled.' ?></strong></div></div>
<?php endif; ?>

<div class="account-order-detail-grid">
    <section class="account-card">
        <header><span><?= fnd_icon('package', 21) ?></span><h2><?= $en ? 'Order Items' : 'أصناف التمور المطلوبة' ?> (<?= count($items) ?>)</h2></header>
        <ul class="account-lines">
            <?php foreach ($items as $item): ?>
                <li>
                    <?php if (!empty($item['product_image'])): ?>
                        <img src="<?= htmlspecialchars(asset($item['product_image'])) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                    <?php else: ?>
                        <span style="display:grid;place-items:center;width:64px;height:72px;border-radius:8px;background:#eee5d8;color:#174a37"><?= fnd_icon('package', 26) ?></span>
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                        <small><?= !empty($item['size_name']) ? htmlspecialchars($item['size_name']) . ' · ' : '' ?><?= $en ? 'Quantity' : 'الكمية' ?>: <?= (int)$item['quantity'] ?></small>
                    </div>
                    <div style="text-align:end">
                        <strong><?= number_format($item['subtotal'], 2) ?> <?= $currency ?></strong>
                        <small><?= number_format($item['unit_price'], 2) ?> &times; <?= (int)$item['quantity'] ?></small>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <div style="display:grid;gap:14px;align-content:start">
        <section class="account-card">
            <header><span><?= fnd_icon('map-pin', 21) ?></span><h2><?= $en ? 'Delivery Address' : 'عنوان التوصيل الوطني' ?></h2></header>
            <div class="account-address-grid" style="grid-template-columns:1fr">
                <article style="min-height:0">
                    <h3><?= htmlspecialchars($order['customer_name']) ?></h3>
                    <p dir="ltr" style="text-align:start"><?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p><?= htmlspecialchars($order['shipping_address']) ?></p>
                    <small><?= htmlspecialchars($order['city'] ?: 'المملكة العربية السعودية') ?></small>
                </article>
            </div>
            <?php if (!empty($order['notes'])): ?>
                <div class="account-review-note" style="margin:12px 0 0"><b><?= $en ? 'Delivery Notes' : 'ملاحظات التوصيل' ?>:</b> <?= htmlspecialchars($order['notes']) ?></div>
            <?php endif; ?>
        </section>

        <section class="account-card">
            <header><span><?= fnd_icon('file-text', 21) ?></span><h2><?= $en ? 'Payment Summary' : 'ملخص الدفع والحساب' ?></h2></header>
            <div class="summary-row"><span><?= $en ? 'Subtotal' : 'مجموع المنتجات' ?></span><strong><?= number_format($order['subtotal'], 2) ?> <?= $currency ?></strong></div>
            <div class="summary-row"><span><?= $en ? 'Cold Express Delivery' : 'رسوم الشحن المبرد' ?></span><strong><?= (float)$order['shipping_fee'] == 0 ? ($en ? 'Free' : 'مجاناً') : number_format($order['shipping_fee'], 2) . ' ' . $currency ?></strong></div>
            <?php if ((float)$order['discount'] > 0): ?>
                <div class="summary-row summary-row--discount"><span><?= $en ? 'Special Discount' : 'خصم خاص' ?></span><strong>-<?= number_format($order['discount'], 2) ?> <?= $currency ?></strong></div>
            <?php endif; ?>
            <div class="summary-row summary-row--total"><span><?= $en ? 'Grand Total' : 'المبلغ الإجمالي الكلي' ?></span><strong><?= number_format($order['total'], 2) ?> <?= $currency ?></strong></div>

            <div class="checkout-delivery-summary">
                <small><?= $en ? 'Payment Method' : 'طريقة الدفع' ?></small>
                <strong>
                    <?= in_array($order['payment_method'], ['mada', 'apple_pay', 'tamara', 'tabby', 'card', 'paymob'])
                        ? ($order['payment_method'] === 'apple_pay' ? 'أبل باي Apple Pay' : ($order['payment_method'] === 'tamara' ? 'تمارا Tamara' : ($order['payment_method'] === 'tabby' ? 'تابي Tabby' : 'مدى / بطاقة بنكية')))
                        : ($locale === 'en' ? 'Cash On Delivery' : 'الدفع عند الاستلام') ?>
                </strong>
                <small><?= $en ? 'Payment Status' : 'حالة السداد' ?></small>
                <strong style="color:<?= $order['payment_status'] === 'paid' ? '#176044' : '#71501c' ?>">
                    <?= $order['payment_status'] === 'paid' ? ($locale === 'en' ? 'Paid Online' : 'تم السداد بنجاح') : ($locale === 'en' ? 'Awaiting Payment' : 'بانتظار التحصيل عند الاستلام') ?>
                </strong>
            </div>
        </section>
    </div>
</div>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
