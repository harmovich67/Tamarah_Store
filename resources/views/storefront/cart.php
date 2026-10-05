<?php
/**
 * Tamrna Foundation - Shopping Cart View
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();

$cart = $cart ?? \App\Controllers\CartController::getCartSummary();
$items = $cart['items'] ?? [];
$isFreeShipping = !empty($cart['is_free_shipping']);
$freeShippingProgress = $cart['free_shipping_progress'] ?? 0;
$freeShippingRemaining = $cart['free_shipping_remaining'] ?? 0;
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
// Standard shipping fee from the dashboard (Settings > Shipping); the city-specific fee is applied at checkout
$feeRow = \Database\Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'shipping_standard_fee'");
$standardFee = $feeRow ? max(0.0, (float)$feeRow['value']) : 25.0;
?>
<section class="commerce-page cart-page">
    <div class="container">
        <header class="commerce-heading">
            <span class="commerce-kicker"><?= fnd_icon('shopping-bag', 16) ?><?= $isRtl ? 'سلتك' : 'Your bag' ?></span>
            <h1><?= $isRtl ? 'سلة المشتريات' : 'Shopping Cart' ?></h1>
            <p><?= $isRtl ? 'راجع أصناف التمور المختارة وأكمل طلبك' : 'Review your selected dates and complete your order' ?></p>
            <a class="commerce-back" href="<?= url('/catalog') ?>"><?= $isRtl ? 'متابعة التسوق' : 'Continue shopping' ?></a>
        </header>

        <?php if (!empty($items)): ?>
            <div class="free-shipping-meter <?= $isFreeShipping ? 'is-complete' : '' ?>">
                <div>
                    <span>
                        <?= fnd_icon('truck', 18) ?>
                        <?php if ($isFreeShipping): ?>
                            <strong><?= $isRtl ? 'تهانينا! لقد حصلت على شحن مبرد مجاني لكافة مدن المملكة 🎉' : 'Congratulations! You unlocked free refrigerated shipping across KSA 🎉' ?></strong>
                        <?php else: ?>
                            <span><?= $isRtl
                                ? 'أضف تمور بقيمة <strong>' . number_format($freeShippingRemaining, 2) . ' ' . currency() . '</strong> لتحصل على شحن مبرد مجاناً!'
                                : 'Add dates worth <strong>' . number_format($freeShippingRemaining, 2) . ' ' . currency() . '</strong> to unlock free cold freight shipping!' ?></span>
                        <?php endif; ?>
                    </span>
                    <small><?= $freeShippingProgress ?>%</small>
                </div>
                <div class="free-shipping-bar" role="progressbar" aria-valuenow="<?= (int)$freeShippingProgress ?>" aria-valuemin="0" aria-valuemax="100"><i style="width: <?= (float)$freeShippingProgress ?>%"></i></div>
            </div>

            <div class="cart-page-layout">
                <div>
                    <ul class="cart-page-items">
                        <?php foreach ($items as $item): ?>
                            <li class="cart-item">
                                <img src="<?= asset($item['image'] ?? 'assets/images/home/ajwa.webp') ?>" alt="<?= esc_attr($item['product_name']) ?>">
                                <div class="cart-item-info">
                                    <h3><?= esc_html($item['product_name']) ?></h3>
                                    <span class="muted">
                                        <?= esc_html($item['size_name']) ?>
                                        <?php if (!empty($item['is_preorder'])): ?>
                                            <span class="badge badge--preorder" style="margin-inline-start:.4rem"><?= $isRtl ? 'حجز مسبق' : 'Pre-Order' ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <div class="product-price"><span><?= $item['unit_price_formatted'] ?></span></div>
                                    <div class="quantity-stepper" role="group" aria-label="<?= $isRtl ? 'الكمية' : 'Quantity' ?>">
                                        <button type="button" class="button button--icon" onclick="updateCartItemQty('<?= esc_attr($item['key']) ?>', <?= $item['quantity'] - 1 ?>)" aria-label="<?= $isRtl ? 'تقليل الكمية' : 'Decrease' ?>"><?= fnd_icon('minus', 16) ?></button>
                                        <output><?= $item['quantity'] ?></output>
                                        <button type="button" class="button button--icon" onclick="updateCartItemQty('<?= esc_attr($item['key']) ?>', <?= $item['quantity'] + 1 ?>)" aria-label="<?= $isRtl ? 'زيادة الكمية' : 'Increase' ?>"><?= fnd_icon('plus', 16) ?></button>
                                    </div>
                                </div>
                                <div class="cart-line-total"><?= $item['subtotal_formatted'] ?></div>
                                <button type="button" class="button button--icon cart-item-remove" onclick="removeCartItem('<?= esc_attr($item['key']) ?>')" title="<?= $isRtl ? 'حذف من السلة' : 'Remove from cart' ?>" aria-label="<?= $isRtl ? 'حذف من السلة' : 'Remove from cart' ?>"><?= fnd_icon('x', 18) ?></button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="cart-page-actions">
                        <button type="button" class="button button--danger button--sm" onclick="clearCart()"><?= $isRtl ? 'تفريغ سلة المشتريات' : 'Clear entire cart' ?></button>
                        <a href="<?= url('/catalog') ?>" class="button button--outline button--sm"><?= $isRtl ? '+ إضافة أصناف أخرى' : '+ Add more items' ?></a>
                    </div>
                </div>

                <aside class="commerce-summary">
                    <h2><?= $isRtl ? 'ملخص الفاتورة' : 'Order Summary' ?></h2>

                    <label class="muted" for="couponInput" style="display:block;margin-bottom:.5rem"><?= $isRtl ? 'كوبون الخصم أو قسيمة الإهداء' : 'Promo code / gift voucher' ?></label>
                    <?php if (!empty($cart['coupon_code'])): ?>
                        <div class="coupon-applied">
                            <div>
                                <strong dir="ltr"><?= esc_html($cart['coupon_code']) ?></strong><br>
                                <small><?= $isRtl ? 'تم الخصم: ' : 'Discount: ' ?><?= $cart['discount_formatted'] ?></small>
                            </div>
                            <button type="button" class="button button--danger button--sm" onclick="removeCoupon()"><?= $isRtl ? 'إلغاء' : 'Remove' ?></button>
                        </div>
                    <?php else: ?>
                        <div class="coupon-form">
                            <input type="text" id="couponInput" class="input" placeholder="<?= $isRtl ? 'مثال: TAMRNA10' : 'e.g. TAMRNA10' ?>" autocomplete="off">
                            <button type="button" class="button button--outline" onclick="applyCoupon()"><?= $isRtl ? 'تطبيق' : 'Apply' ?></button>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row"><span><?= $isRtl ? 'إجمالي المنتجات' : 'Products subtotal' ?></span><strong><?= $cart['subtotal_formatted'] ?></strong></div>
                    <?php if (!empty($cart['discount']) && $cart['discount'] > 0): ?>
                        <div class="summary-row summary-row--discount"><span><?= $isRtl ? 'خصم الكوبون' : 'Coupon discount' ?></span><strong>- <?= $cart['discount_formatted'] ?></strong></div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span><?= $isRtl ? 'الشحن والتوصيل المبرد' : 'Refrigerated shipping' ?></span>
                        <strong><?= $isFreeShipping ? ($isRtl ? 'مجاناً' : 'FREE') : (number_format($standardFee, 2) . ' ' . currency()) ?></strong>
                    </div>
                    <div class="summary-row summary-row--total">
                        <span><?= $isRtl ? 'المبلغ الإجمالي' : 'Estimated total' ?><br><small class="muted"><?= $isRtl ? 'شامل الضريبة والشحن' : 'Includes 15% VAT & shipping' ?></small></span>
                        <strong><?= number_format((float)$cart['total'] + ($isFreeShipping ? 0 : $standardFee), 2) ?> <?= currency() ?></strong>
                    </div>

                    <a href="<?= url('/checkout') ?>" class="button button--primary commerce-primary-action">
                        <?= $isRtl ? 'متابعة الشحن والدفع' : 'Proceed to checkout' ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?>
                    </a>
                    <p class="commerce-security-note"><?= $isRtl ? 'دفع مشفر ١٠٠٪ ومتوافق مع البنك المركزي السعودي' : '100% encrypted checkout, SAMA compliant' ?></p>
                </aside>
            </div>
        <?php else: ?>
            <div class="commerce-empty">
                <div class="cart-empty">
                    <?= fnd_icon('shopping-bag', 56, '', 1.3) ?>
                    <h2><?= $isRtl ? 'سلة المشتريات فارغة حالياً' : 'Your shopping cart is empty' ?></h2>
                    <p><?= $isRtl
                        ? 'لم تقم بإضافة أي أصناف من التمور الفاخرة بعد. تصفح تشكيلتنا وأضف ما يحلو لك إلى سلتك.'
                        : 'You haven’t added any dates yet. Discover our collections and add your favorites.' ?></p>
                    <a href="<?= url('/catalog') ?>" class="button button--primary"><?= $isRtl ? 'استعراض أقسام التمور' : 'Explore date catalog' ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function updateCartItemQty(key, newQty) {
        const formData = new FormData();
        formData.append('key', key);
        formData.append('quantity', newQty);

        fetch(window.appUrl('/api/cart/update'), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function removeCartItem(key) {
        const formData = new FormData();
        formData.append('key', key);

        fetch(window.appUrl('/api/cart/remove'), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function clearCart() {
        const confirmMsg = <?= json_encode($isRtl ? 'هل أنت متأكد من تفريغ سلة المشتريات بالكامل؟' : 'Are you sure you want to clear your entire cart?') ?>;
        if (confirm(confirmMsg)) {
            fetch(window.appUrl('/api/cart/clear'), {
                method: 'POST'
            })
            .then(r => r.json())
            .then(() => location.reload());
        }
    }

    function applyCoupon() {
        const input = document.getElementById('couponInput');
        if (!input) return;
        const code = input.value.trim();
        if (!code) {
            showTumurnaToast(<?= json_encode($isRtl ? 'يرجى إدخال رمز الكوبون' : 'Please enter a coupon code') ?>, 'error');
            return;
        }
        const formData = new FormData();
        formData.append('code', code);

        fetch(window.appUrl('/api/cart/coupon'), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showTumurnaToast(data.message || <?= json_encode($isRtl ? 'الكوبون غير صحيح' : 'Invalid coupon code') ?>, 'error');
            }
        });
    }

    function removeCoupon() {
        fetch(window.appUrl('/api/cart/coupon/remove'), {
            method: 'POST'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
