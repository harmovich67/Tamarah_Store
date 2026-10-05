<?php
/**
 * Tamrna Foundation - Order Success & Confirmation
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();

$order = $order ?? [];
$items = $items ?? [];
?>
<section class="commerce-page checkout-success">
    <div class="container">
        <div class="checkout-success-card">
            <div class="checkout-success-icon"><?= fnd_icon('check', 42) ?></div>
            <p class="commerce-kicker" style="justify-content:center"><?= fnd_icon('sparkles', 14) ?><?= $isRtl ? 'تم استلام وتأكيد طلبك بنجاح' : 'Order received & confirmed' ?></p>
            <h1><?= $isRtl ? 'شكراً لثقتكم باختيار تمرنا' : 'Thank you for choosing Tamrna' ?></h1>
            <p><?= $isRtl
                ? 'تم تسجيل طلبك رقم <strong dir="ltr">' . esc_html($order['order_number'] ?? '') . '</strong> وسيتم تجهيز التمور بعناية فائقة ونقلها مبردة إلى عنوانك المعتمد.'
                : 'Your order <strong dir="ltr">' . esc_html($order['order_number'] ?? '') . '</strong> has been placed. Our packers are preparing your dates for chilled delivery.' ?></p>

            <dl>
                <div><dt><?= $isRtl ? 'رقم الطلب' : 'Order #' ?></dt><dd dir="ltr"><?= esc_html($order['order_number'] ?? '') ?></dd></div>
                <div><dt><?= $isRtl ? 'تاريخ الطلب' : 'Order date' ?></dt><dd><?= esc_html(substr($order['created_at'] ?? date('Y-m-d'), 0, 10)) ?></dd></div>
                <div><dt><?= $isRtl ? 'طريقة الدفع' : 'Payment method' ?></dt><dd><?= esc_html(strtoupper((string)($order['payment_method'] ?? ''))) ?></dd></div>
                <div><dt><?= $isRtl ? 'حالة الطلب' : 'Order status' ?></dt><dd><?= $isRtl ? 'جاري التجهيز والشحن المبرد' : 'Processing & cold shipping' ?></dd></div>
            </dl>

            <div class="checkout-success-actions">
                <button type="button" class="button button--outline" onclick="window.print()"><?= $isRtl ? 'طباعة الفاتورة' : 'Print invoice' ?></button>
                <a href="<?= url('/catalog') ?>" class="button button--primary"><?= $isRtl ? 'متابعة التسوق' : 'Continue shopping' ?></a>
            </div>
        </div>

        <div class="checkout-card order-details-card" style="max-width:850px;margin:28px auto 0">
            <h2><?= $isRtl ? 'تفاصيل الطلب والشحن' : 'Order & shipping details' ?></h2>

            <div class="checkout-delivery-summary" style="margin-top:0">
                <small><?= $isRtl ? 'بيانات المستلم والعنوان' : 'Recipient & shipping address' ?></small>
                <strong><?= esc_html($order['customer_name'] ?? '') ?> — <span dir="ltr"><?= esc_html($order['customer_phone'] ?? '') ?></span></strong>
                <span><?= esc_html($order['shipping_address'] ?? '') ?></span>
            </div>

            <div class="checkout-items" style="border-top:0;margin-top:0;padding-top:0">
                <h3><?= $isRtl ? 'الأصناف المطلوبة' : 'Ordered items' ?></h3>
                <ul class="account-lines">
                    <?php foreach ($items as $item):
                        $itemName = lang_get($item, 'product_name', ($item['product_name'] ?? ''));
                        $sizeName = lang_get($item, 'size_name', ($item['size_name'] ?? ''));
                        if (!$isRtl) $sizeName = str_replace(['كجم', 'كيلو'], 'kg', (string)$sizeName);
                        ?>
                        <li>
                            <img src="<?= asset($item['featured_image'] ?? 'assets/images/home/ajwa.webp') ?>" alt="">
                            <div><strong><?= esc_html($itemName) ?></strong><small><?= esc_html($sizeName) ?> × <?= $item['quantity'] ?></small></div>
                            <strong><?= number_format($item['subtotal'], 2) ?> <?= currency() ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div style="max-width:20rem;margin-inline-start:auto;width:100%">
                <div class="summary-row"><span><?= $isRtl ? 'المجموع الفرعي' : 'Subtotal' ?></span><strong><?= number_format($order['subtotal'], 2) ?> <?= currency() ?></strong></div>
                <?php if (!empty($order['discount']) && $order['discount'] > 0): ?>
                    <div class="summary-row summary-row--discount"><span><?= $isRtl ? 'الخصم' : 'Discount' ?></span><strong>- <?= number_format($order['discount'], 2) ?> <?= currency() ?></strong></div>
                <?php endif; ?>
                <div class="summary-row"><span><?= $isRtl ? 'رسوم الشحن والتوصيل' : 'Shipping fee' ?></span><strong><?= $order['shipping_fee'] == 0 ? ($isRtl ? 'مجاناً' : 'FREE') : (number_format($order['shipping_fee'], 2) . ' ' . currency()) ?></strong></div>
                <div class="summary-row summary-row--total"><span><?= $isRtl ? 'الإجمالي المدفوع' : 'Total amount' ?></span><strong><?= number_format($order['total'], 2) ?> <?= currency() ?></strong></div>
            </div>
        </div>
    </div>
</section>
