<?php
/**
 * Payment Gateway Sandbox Simulator View (Tamrna Foundation)
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();
?>
<section class="commerce-page">
    <div class="container" style="max-width:680px">
        <div class="checkout-card">
            <header class="checkout-review-heading">
                <div>
                    <span class="commerce-kicker"><?= fnd_icon('shield-check', 16) ?>Sandbox Payment Simulator</span>
                    <h2><?= strtoupper(htmlspecialchars($provider)) ?> Gateway</h2>
                </div>
                <span class="badge badge--preorder"><?= $isRtl ? 'بيئة تجريبية واختبار' : 'Test Mode' ?></span>
            </header>

            <div class="flash flash--info">
                <?= fnd_icon('info', 20) ?>
                <div>
                    <strong><?= $isRtl ? 'ملاحظة للمطور والمدير:' : 'Developer Notice:' ?></strong><br>
                    <?= $isRtl
                        ? "أنت الآن في محاكي الدفع التجريبي لأن مفاتيح بوابة <b>" . strtoupper(htmlspecialchars($provider)) . "</b> المدخلة في لوحة التحكم تجريبية أو لم يتم وضع مفاتيح حية بعد. يمكنك تجربة إتمام الدفع أو إلغائه، وبمجرد وضع المفاتيح الحية في لوحة التحكم سيتم التوجيه تلقائياً لموقع البوابة الرسمي الحقيقي."
                        : "You are in the test simulator because the credentials for <b>" . strtoupper(htmlspecialchars($provider)) . "</b> are in test mode. Placing real keys in Admin Settings will automatically route to the real live gateway." ?>
                </div>
            </div>

            <div class="checkout-review">
                <dl>
                    <div><dt><?= $isRtl ? 'رقم الطلب' : 'Order Number' ?></dt><dd dir="ltr"><?= htmlspecialchars($order['order_number']) ?></dd></div>
                    <div><dt><?= $isRtl ? 'العميل' : 'Customer' ?></dt><dd><?= htmlspecialchars($order['customer_name']) ?></dd></div>
                    <div><dt><?= $isRtl ? 'طريقة الدفع المختارة' : 'Payment Method' ?></dt><dd><?= htmlspecialchars($paymentMethod) ?></dd></div>
                    <div><dt><?= $isRtl ? 'المبلغ المطلوب سداده' : 'Total Amount to Pay' ?></dt><dd><strong><?= number_format((float)$order['total'], 2) ?> <?= currency() ?></strong></dd></div>
                </dl>
            </div>

            <a href="<?= $successUrl ?>" class="button button--primary commerce-primary-action"><?= fnd_icon('check-circle', 20) ?><?= $isRtl ? 'محاكاة: تأكيد الدفع بنجاح (Success)' : 'Simulate: Payment Successful' ?></a>
            <a href="<?= $cancelUrl ?>" class="button button--danger" style="width:100%"><?= fnd_icon('x-circle', 18) ?><?= $isRtl ? 'محاكاة: إلغاء عملية الدفع (Cancel / Decline)' : 'Simulate: Cancel Payment' ?></a>
        </div>
    </div>
</section>
