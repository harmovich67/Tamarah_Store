<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
?>
<section class="commerce-page">
    <div class="container" style="max-width:520px">
        <div class="checkout-card">
            <header style="text-align:center">
                <?= fnd_icon('shield-check', 40, '', 1.4) ?>
                <h2>Paymob Secure Payment Gateway</h2>
                <p class="muted">بوابة الدفع الإلكتروني المعتمدة - بيئة الاختبار التجريبية الآمنة</p>
            </header>

            <div class="checkout-delivery-summary" style="margin-top:0">
                <small>رقم طلب المنصة</small><strong dir="ltr"><?= htmlspecialchars($order['order_number']) ?></strong>
                <small>المبلغ المطلوب</small><strong><?= I18n::formatPrice((float)$order['total']) ?></strong>
            </div>

            <form action="<?= url('/checkout/paymob-complete') ?>" method="POST" class="checkout-card" style="padding:0;border:0;background:none">
                <input type="hidden" name="order_number" value="<?= htmlspecialchars($order['order_number']) ?>">

                <?php if ($method === 'paymob_wallet'): ?>
                    <div class="form-field">
                        <label for="pm-wallet">رقم المحفظة الإلكترونية (فودافون كاش / أورنج / اتصالات / وي)</label>
                        <input id="pm-wallet" class="input" type="text" value="<?= htmlspecialchars($order['customer_phone']) ?>" required>
                    </div>
                    <div class="checkout-notice">سيتم إرسال إشعار تأكيد الخصم وطلب الرقم السري للمحفظة على هاتفك.</div>
                <?php else: ?>
                    <div class="form-field">
                        <label for="pm-card">رقم البطاقة الائتمانية / ميزة</label>
                        <input id="pm-card" class="input" type="text" value="5123 4567 8901 2026" required dir="ltr">
                    </div>
                    <div class="checkout-fields">
                        <div class="form-field">
                            <label for="pm-exp">تاريخ الانتهاء</label>
                            <input id="pm-exp" class="input" type="text" value="12/28" required dir="ltr">
                        </div>
                        <div class="form-field">
                            <label for="pm-cvv">رمز الأمان (CVV)</label>
                            <input id="pm-cvv" class="input" type="password" value="888" maxlength="4" required dir="ltr">
                        </div>
                    </div>
                    <div class="form-field">
                        <label for="pm-name">اسم حامل البطاقة</label>
                        <input id="pm-name" class="input" type="text" value="<?= htmlspecialchars($order['customer_name']) ?>" required>
                    </div>
                <?php endif; ?>

                <button type="submit" class="button button--primary commerce-primary-action"><?= fnd_icon('check-circle', 18) ?>تأكيد السداد والخصم الآن (Paymob Live Mock)</button>
            </form>

            <a class="commerce-back" style="justify-self:center" href="<?= url('/checkout') ?>">إلغاء والعودة لصفحة الطلب</a>
        </div>
    </div>
</section>
