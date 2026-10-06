<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$currency = $en ? 'SAR' : 'ر.س';
$arrow = 'arrow-left'; // mirrored for LTR by .directional-arrow
$orderItems = $orderItems ?? [];

$accountActive = 'orders';
$accountHeading = $en ? 'My Orders' : 'طلباتي';
$accountSub = $en ? 'Review your order history and follow the status of every shipment' : 'اطّلع على سجل طلباتك وتابع حالة كل طلب';
$accountAction = '<a class="button button--primary" href="' . url('/catalog') . '">' . fnd_icon('sparkles', 18) . ($en ? 'Order Fresh Dates' : 'طلب تمور ملكية جديدة') . '</a>';
include __DIR__ . '/../../components/account_shell_open.php';

// Order state -> tab. Anything not delivered or cancelled is still in progress.
$statusLabels = [
    'pending' => $en ? 'Awaiting confirmation' : 'بانتظار التأكيد',
    'processing' => $en ? 'Preparing' : 'قيد التجهيز',
    'shipped' => $en ? 'Shipped' : 'تم الشحن وجاري التوصيل',
    'delivered' => $en ? 'Delivered' : 'مكتمل / تم التوصيل',
    'cancelled' => $en ? 'Cancelled' : 'ملغي',
];
$tabOf = function (string $status): string {
    if (in_array($status, ['delivered', 'completed'], true)) return 'completed';
    if (in_array($status, ['cancelled', 'canceled', 'failed', 'refunded'], true)) return 'cancelled';
    return 'current';
};
$tabs = [
    'current' => $en ? 'Current orders' : 'الطلبات الحالية',
    'completed' => $en ? 'Completed orders' : 'الطلبات المكتملة',
    'cancelled' => $en ? 'Cancelled orders' : 'الطلبات الملغاة',
];
$counts = ['current' => 0, 'completed' => 0, 'cancelled' => 0];
foreach ($orders as $o) $counts[$tabOf(strtolower((string)($o['shipping_status'] ?: 'processing')))]++;
?>
<?php if (empty($orders)): ?>
    <section class="account-card account-wide-card account-orders-page">
        <header><span><?= fnd_icon('package', 21) ?></span><h2><?= $en ? 'My Orders' : 'طلباتي' ?></h2></header>
        <div class="account-empty">
            <p><?= $en ? 'You have not placed any orders yet.' : 'لم تقم بإنشاء أي طلبات حتى الآن' ?></p>
            <a href="<?= url('/catalog') ?>" class="button button--primary" style="margin-top:12px"><?= $en ? 'Browse Dates Catalog' : 'تصفح تشكيلات التمور' ?></a>
        </div>
    </section>
<?php else: ?>
    <section class="account-card account-wide-card account-orders-page" id="orders-board">
        <header><span><?= fnd_icon('package', 21) ?></span><h2><?= $en ? 'My Orders' : 'طلباتي' ?></h2></header>
        <p class="account-orders-intro"><?= $en ? 'Review your order history and status, then select a product image to view its details.' : 'اطّلع على سجل طلباتك وتابع حالة كل طلب، واضغط على صورة أي منتج لعرض تفاصيله.' ?></p>

        <div class="account-order-tabs" role="tablist" aria-label="<?= $en ? 'Order categories' : 'تصنيف الطلبات' ?>">
            <?php foreach ($tabs as $key => $label): ?>
                <button type="button" role="tab" data-orders-tab="<?= $key ?>" aria-selected="<?= $key === 'current' ? 'true' : 'false' ?>" class="<?= $key === 'current' ? 'is-active' : '' ?>"><span><?= $label ?></span><b><?= $counts[$key] ?></b></button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($tabs as $key => $label): ?>
            <?php $tabOrders = array_values(array_filter($orders, fn($o) => $tabOf(strtolower((string)($o['shipping_status'] ?: 'processing'))) === $key)); ?>
            <div data-orders-panel="<?= $key ?>" role="tabpanel"<?= $key === 'current' ? '' : ' hidden' ?>>
                <?php if (!$tabOrders): ?>
                    <p class="account-empty"><?= $en ? 'No orders in this section.' : 'لا توجد طلبات في هذا القسم.' ?></p>
                <?php else: ?>
                    <div class="account-order-cards">
                        <?php foreach ($tabOrders as $order):
                            $status = strtolower((string)($order['shipping_status'] ?: 'processing'));
                            $items = $orderItems[(int)$order['id']] ?? [];
                            ?>
                            <article class="account-order-card">
                                <header>
                                    <div><small><?= $en ? 'Order number' : 'رقم الطلب' ?></small><b dir="ltr"><?= htmlspecialchars($order['order_number']) ?></b></div>
                                    <em data-status="<?= esc_attr($status) ?>"><?= htmlspecialchars($statusLabels[$status] ?? $status) ?></em>
                                </header>
                                <?php if ($items): ?>
                                    <div class="account-order-products">
                                        <?php foreach ($items as $item):
                                            $thumb = !empty($item['featured_image']) ? asset($item['featured_image']) : asset('assets/images/home/ajwa.webp');
                                            ?>
                                            <?php if (!empty($item['slug'])): ?>
                                                <a class="account-order-product" href="<?= url('/product/' . $item['slug']) ?>" aria-label="<?= esc_attr(($en ? 'View details for ' : 'عرض تفاصيل ') . $item['product_name']) ?>"><img src="<?= esc_attr($thumb) ?>" alt="" loading="lazy"><i><?= (int)$item['quantity'] ?></i></a>
                                            <?php else: ?>
                                                <div class="account-order-product"><img src="<?= esc_attr($thumb) ?>" alt="" loading="lazy"><i><?= (int)$item['quantity'] ?></i></div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <dl>
                                    <div><dt><?= $en ? 'Date' : 'التاريخ' ?></dt><dd><?= date('Y-m-d', strtotime($order['created_at'])) ?></dd></div>
                                    <div><dt><?= $en ? 'Order total' : 'إجمالي الطلب' ?></dt><dd><?= number_format((float)$order['total'], 2) ?> <?= $currency ?></dd></div>
                                </dl>
                                <?php if (!empty($order['tracking_number'])): ?>
                                    <div class="account-order-tracking"><span><?= $en ? 'Tracking number' : 'رقم التتبع' ?></span><b dir="ltr"><?= htmlspecialchars($order['tracking_number']) ?></b></div>
                                <?php endif; ?>
                                <div class="account-order-tracking">
                                    <span><?= fnd_icon('snowflake', 14) ?> <?= $en ? 'Cold Express Delivery' : 'شحن مبرد فاخر' ?></span>
                                    <a class="button button--outline button--sm" href="<?= url('/profile/order/' . $order['order_number']) ?>"><?= $en ? 'Order Details' : 'تفاصيل وتتبع الطلب' ?><?= fnd_icon($arrow, 15, 'directional-arrow') ?></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <script>
    (function () {
        var board = document.getElementById('orders-board'); if (!board) return;
        board.addEventListener('click', function (e) {
            var tab = e.target.closest('[data-orders-tab]'); if (!tab) return;
            var key = tab.getAttribute('data-orders-tab');
            board.querySelectorAll('[data-orders-tab]').forEach(function (b) { var on = b === tab; b.classList.toggle('is-active', on); b.setAttribute('aria-selected', on ? 'true' : 'false'); });
            board.querySelectorAll('[data-orders-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-orders-panel') !== key; });
        });
    })();
    </script>
<?php endif; ?>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>
