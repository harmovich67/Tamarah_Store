<?php
/**
 * Tamrna Foundation - Saudi Checkout View
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();

$cart = $cart ?? \App\Controllers\CartController::getCartSummary();
$cities = $cities ?? \App\Controllers\CheckoutController::saudiCities();
$shippingFee = $shippingFee ?? (($cart['subtotal'] >= 300) ? 0.0 : 25.0);
$total = $total ?? max(0.0, (float)$cart['total'] + $shippingFee);
$defaultAddress = $defaultAddress ?? null;
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
$checkoutUser = \App\Core\Auth::user();
$checkoutName = preg_split('/\s+/u', trim((string)($checkoutUser['name'] ?? '')), 2);
$checkoutFirstName = $checkoutName[0] ?? '';
$checkoutLastName = $checkoutName[1] ?? '';

$checkoutError = $error_message ?? $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Payment methods enabled from settings
$enableMada = ($settings['enable_mada'] ?? '1') == '1';
$enableApplePay = ($settings['enable_apple_pay'] ?? '1') == '1';
$enableCreditCard = ($settings['enable_credit_card'] ?? '1') == '1';
$enablePaymob = ($settings['enable_paymob'] ?? '1') == '1';
$enableTabby = ($settings['enable_tabby'] ?? '1') == '1';
$enableTamara = ($settings['enable_tamara'] ?? '1') == '1';
$enableCod = ($settings['enable_cod'] ?? '1') == '1';
$enableBankTransfer = ($settings['enable_bank_transfer'] ?? '0') == '1';
$codFee = (float)($settings['cod_fee'] ?? 0.0);

$paymentMethods = [];
if ($enableMada) $paymentMethods[] = ['mada', $isRtl ? 'بطاقة مدى البنكية (Mada)' : 'Mada Debit Card', $isRtl ? 'دفع فوري سريع وآمن عبر الشبكة السعودية' : 'Instant and secure payment via Saudi national payment network', 'mada'];
if ($enableApplePay) $paymentMethods[] = ['apple_pay', 'Apple Pay', $isRtl ? 'إتمام الشراء بنقرة واحدة لأجهزة آبل' : 'One-tap checkout for Apple devices', 'Apple Pay'];
if ($enableCreditCard) $paymentMethods[] = ['credit_card', $isRtl ? 'البطاقات الائتمانية (Visa / Mastercard)' : 'Credit Cards (Visa / Mastercard)', $isRtl ? 'فيزا، ماستركارد، بطاقات الائتمان الخليجية والدولية' : 'Visa, Mastercard, GCC and international credit cards', 'VISA'];
if ($enablePaymob) $paymentMethods[] = ['paymob', $isRtl ? 'باي موب (Paymob)' : 'Paymob', $isRtl ? 'بوابة الدفع الإلكتروني المعتمدة للبطاقات والمحافظ' : 'Approved payment gateway for cards and digital wallets', 'paymob'];
if ($enableTabby) $paymentMethods[] = ['tabby', $isRtl ? 'تابي (Tabby)' : 'Tabby', $isRtl ? 'قسّم فاتورتك على 4 دفعات شهرية بدون أي فوائد' : 'Split in 4 interest-free monthly installments', 'tabby'];
if ($enableTamara) $paymentMethods[] = ['tamara', $isRtl ? 'تمارا (Tamara)' : 'Tamara', $isRtl ? 'قسّمها على 4 دفعات مريحة ومتوافقة مع الشريعة' : 'Split in 4 flexible Sharia-compliant payments', 'tamara'];
if ($enableCod) $paymentMethods[] = ['cod', $isRtl ? 'الدفع عند الاستلام' : 'Cash on Delivery', $isRtl ? 'سداد القيمة نقداً أو عبر بطاقة مدى لمندوب التوصيل' : 'Pay cash or mada card upon delivery', ($codFee > 0 ? '+' . number_format($codFee, 2) . ' ' . currency() : ($isRtl ? 'كاش / مدى' : 'Cash/Mada'))];
?>
<section class="commerce-page checkout-page">
    <div class="container">
        <header class="commerce-heading">
            <span class="commerce-kicker"><?= fnd_icon('shield-check', 16) ?><?= $isRtl ? 'دفع آمن' : 'Secure checkout' ?></span>
            <h1><?= $isRtl ? 'إنهاء الطلب والدفع' : 'Checkout & Payment' ?></h1>
            <p><?= $isRtl ? 'أدخل بيانات العنوان الوطني السعودي للتوصيل المبرد واختر طريقة الدفع المناسبة' : 'Enter your Saudi national address details for refrigerated delivery and select payment method' ?></p>
            <a class="commerce-back" href="<?= url('/cart') ?>"><?= $isRtl ? 'العودة إلى السلة' : 'Back to cart' ?></a>
        </header>

        <?php if (!empty($checkoutError)): ?>
            <div class="flash flash--error" role="alert"><?= fnd_icon('alert-circle', 20) ?><div><?= htmlspecialchars($checkoutError) ?></div></div>
        <?php endif; ?>

        <form method="post" action="<?= url('/checkout/process') ?>" class="checkout-layout">
            <?= csrf_field() ?>

            <div class="checkout-main" style="display:grid;gap:24px">

                <!-- STEP 1: Customer contact -->
                <div class="checkout-card">
                    <div class="checkout-section-heading">
                        <h2><?= $isRtl ? '١. بيانات العميل والتواصل' : '1. Customer & Contact Details' ?></h2>
                    </div>
                    <div class="checkout-fields">
                        <div class="form-field">
                            <label for="co-first"><?= $isRtl ? 'الاسم الأول' : 'First Name' ?><span class="req"> *</span></label>
                            <input id="co-first" class="input" type="text" name="first_name" required placeholder="<?= $isRtl ? 'فهد' : 'Fahad' ?>" value="<?= esc_attr($checkoutFirstName) ?>">
                        </div>
                        <div class="form-field">
                            <label for="co-last"><?= $isRtl ? 'اسم العائلة' : 'Last Name' ?><span class="req"> *</span></label>
                            <input id="co-last" class="input" type="text" name="last_name" required placeholder="<?= $isRtl ? 'الناصر' : 'Al-Nasser' ?>" value="<?= esc_attr($checkoutLastName) ?>">
                        </div>
                        <div class="form-field">
                            <label for="co-phone"><?= $isRtl ? 'رقم الجوال السعودي' : 'Saudi Mobile Number' ?><span class="req"> *</span></label>
                            <input id="co-phone" class="input" type="tel" name="phone" required placeholder="0555123456" value="<?= esc_attr($checkoutUser['phone'] ?? '') ?>" dir="ltr" style="text-align:start">
                        </div>
                        <div class="form-field">
                            <label for="co-email"><?= $isRtl ? 'البريد الإلكتروني' : 'Email Address' ?><span class="req"> *</span></label>
                            <input id="co-email" class="input" type="email" name="email" required placeholder="customer@example.com" value="<?= esc_attr($checkoutUser['email'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Delivery -->
                <div class="checkout-card">
                    <div class="checkout-section-heading">
                        <h2><?= $isRtl ? '٢. طريقة الاستلام والعنوان' : '2. Delivery Method & Address' ?></h2>
                    </div>

                    <fieldset class="radio-group radio-group--2">
                        <legend><?= $isRtl ? 'طريقة الاستلام' : 'Delivery method' ?></legend>
                        <label class="choice" id="label-shipping-home">
                            <input type="radio" name="shipping_type" value="home_delivery" checked onchange="toggleShippingFields('home')">
                            <span>
                                <strong><?= $isRtl ? 'توصيل للمنزل (شحن مبرد)' : 'Home Delivery (Refrigerated Fleet)' ?></strong>
                                <small><?= $isRtl ? 'العنوان الوطني لجميع مدن المملكة' : 'National Address for all KSA cities' ?></small>
                            </span>
                        </label>
                        <label class="choice" id="label-shipping-branch">
                            <input type="radio" name="shipping_type" value="branch_pickup" onchange="toggleShippingFields('branch')">
                            <span>
                                <strong><?= $isRtl ? 'استلام من الفرع (مجاناً)' : 'Boutique Pickup (Free)' ?></strong>
                                <small><?= $isRtl ? 'فروع الرياض، جدة، المدينة المنورة' : 'Riyadh, Jeddah & Madinah lounges' ?></small>
                            </span>
                        </label>
                    </fieldset>

                    <!-- National address fields -->
                    <div id="homeDeliveryFields" class="checkout-address">
                        <div class="checkout-fields">
                            <div class="form-field">
                                <label for="citySelect"><?= $isRtl ? 'المدينة (المملكة العربية السعودية)' : 'City (Saudi Arabia)' ?><span class="req"> *</span></label>
                                <select name="city" id="citySelect" class="input select" onchange="recalculateShipping()">
                                    <?php foreach ($cities as $cityItem):
                                        $cNameAr = is_array($cityItem) ? ($cityItem['city_name_ar'] ?? '') : (string)$cityItem;
                                        $cNameEn = is_array($cityItem) ? ($cityItem['city_name_en'] ?? $cNameAr) : $cNameAr;
                                        $cDisplayName = $isRtl ? $cNameAr : $cNameEn;
                                        $cFee = is_array($cityItem) ? (float)($cityItem['shipping_fee'] ?? 25.0) : 25.0;
                                        $cEstimate = is_array($cityItem) ? ($cityItem['estimated_delivery'] ?? '') : '';
                                        $freeShippingText = $isRtl ? 'شحن مجاني' : 'Free Shipping';
                                        ?>
                                        <option value="<?= esc_attr($cNameAr) ?>" data-delivery="<?= esc_attr($cEstimate) ?>" <?= $cNameAr === 'الرياض' ? 'selected' : '' ?>>
                                            <?= esc_html($cDisplayName) ?> - <?= $cart['subtotal'] >= 300 ? $freeShippingText : (number_format($cFee, 0) . ' ' . currency()) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="field-hint" id="cityDeliveryNote"><?= fnd_icon('snowflake', 14) ?> <span id="cityDeliveryNoteText"><?= esc_html($shippingDelivery ?? ($isRtl ? 'توصيل مبرد سريع ومضمون' : 'Fast refrigerated delivery')) ?></span></span>
                            </div>
                            <div class="form-field">
                                <label for="co-district"><?= $isRtl ? 'الحي' : 'District / Neighborhood' ?><span class="req"> *</span></label>
                                <input id="co-district" class="input" type="text" name="district" placeholder="<?= $isRtl ? 'مثال: حي النرجس / حي الروضة' : 'e.g. Al-Narjis / Al-Rawdah' ?>" value="<?= $isRtl ? 'حي النرجس' : 'Al-Narjis' ?>">
                            </div>
                            <div class="form-field checkout-full">
                                <label for="co-address"><?= $isRtl ? 'العنوان الوطني / الشارع ورقم المبنى' : 'National Address / Street & Building' ?><span class="req"> *</span></label>
                                <input id="co-address" class="input" type="text" name="address" placeholder="<?= $isRtl ? 'اسم الشارع، رقم العمارة، أي علامة مميزة' : 'Street name, building number, landmark' ?>" value="<?= $isRtl ? 'شارع الأمير فيصل بن بندر، فيلا 24' : 'Prince Faisal Bin Bandar St, Villa 24' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Branch pickup -->
                    <fieldset id="branchPickupFields" class="radio-group hidden">
                        <legend><?= $isRtl ? 'اختر الفرع الأقرب للاستلام' : 'Select nearest boutique for pickup' ?></legend>
                        <label class="choice"><input type="radio" name="pickup_branch" value="riyadh" checked><span><strong><?= $isRtl ? 'فرع الرياض' : 'Riyadh Boutique' ?></strong><small><?= $isRtl ? 'طريق الملك فهد (مقابل البرج)' : 'King Fahd Road' ?></small></span></label>
                        <label class="choice"><input type="radio" name="pickup_branch" value="jeddah"><span><strong><?= $isRtl ? 'فرع جدة' : 'Jeddah Boutique' ?></strong><small><?= $isRtl ? 'طريق الكورنيش الشمالي' : 'North Corniche Road' ?></small></span></label>
                        <label class="choice"><input type="radio" name="pickup_branch" value="madinah"><span><strong><?= $isRtl ? 'فرع المدينة' : 'Madinah Boutique' ?></strong><small><?= $isRtl ? 'طريق سلطانة التجاري' : 'Sultana Commercial Road' ?></small></span></label>
                    </fieldset>

                    <div class="form-field">
                        <label for="co-notes"><?= $isRtl ? 'ملاحظات التوصيل أو وقت الاستلام (اختياري)' : 'Delivery Notes or Preferred Timing (Optional)' ?></label>
                        <textarea id="co-notes" class="input textarea" name="notes" rows="2" placeholder="<?= $isRtl ? 'يرجى الاتصال قبل الوصول، تغليف هدايا إضافي...' : 'Please call before arrival, gift wrapping notes...' ?>"></textarea>
                    </div>
                </div>

                <!-- STEP 3: Payment -->
                <div class="checkout-card">
                    <div class="checkout-section-heading">
                        <h2><?= $isRtl ? '٣. طريقة الدفع' : '3. Payment Method' ?></h2>
                    </div>
                    <fieldset class="radio-group radio-group--1">
                        <legend class="sr-only"><?= $isRtl ? 'طريقة الدفع' : 'Payment method' ?></legend>
                        <?php foreach ($paymentMethods as $pi => [$pVal, $pName, $pDesc, $pMark]): ?>
                            <label class="choice payment-method-opt">
                                <input type="radio" name="payment_method" value="<?= $pVal ?>" <?= $pi === 0 ? 'checked' : '' ?>>
                                <span><strong><?= $pName ?></strong><small><?= $pDesc ?></small></span>
                                <span class="choice-mark"><?= esc_html($pMark) ?></span>
                            </label>
                        <?php endforeach; ?>

                        <?php if ($enableBankTransfer): ?>
                            <label class="choice payment-method-opt" style="flex-wrap:wrap">
                                <input type="radio" name="payment_method" value="bank_transfer" <?= empty($paymentMethods) ? 'checked' : '' ?>>
                                <span><strong><?= $isRtl ? 'التحويل البنكي المباشر' : 'Direct Bank Transfer' ?></strong><small><?= htmlspecialchars($settings['bank_name'] ?? 'Al Rajhi Bank') ?></small></span>
                                <span class="choice-mark"><?= $isRtl ? 'تحويل بنكي' : 'Bank Transfer' ?></span>
                                <div class="checkout-notice" style="flex-basis:100%">
                                    <div><b><?= $isRtl ? 'المستفيد:' : 'Beneficiary:' ?></b> <?= htmlspecialchars($settings['bank_account_name'] ?? 'Tumurna Trading Establishment') ?></div>
                                    <div><b><?= $isRtl ? 'الآيبان (IBAN):' : 'IBAN:' ?></b> <bdi dir="ltr"><?= htmlspecialchars($settings['bank_iban'] ?? '') ?></bdi></div>
                                </div>
                            </label>
                        <?php endif; ?>
                    </fieldset>
                </div>
            </div>

            <!-- Summary -->
            <aside class="commerce-summary checkout-summary">
                <h2><?= $isRtl ? 'ملخص الفاتورة' : 'Order Summary' ?></h2>

                <div class="checkout-summary-items">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="checkout-summary-item">
                            <img src="<?= asset($item['image'] ?? 'assets/images/home/ajwa.webp') ?>" alt="">
                            <div>
                                <strong><?= esc_html($item['product_name']) ?></strong>
                                <small><?= esc_html($item['size_name']) ?> × <?= $item['quantity'] ?></small>
                            </div>
                            <strong><?= $item['subtotal_formatted'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row"><span><?= $isRtl ? 'قيمة المنتجات' : 'Products subtotal' ?></span><strong><?= $cart['subtotal_formatted'] ?></strong></div>
                <?php if (!empty($cart['discount']) && $cart['discount'] > 0): ?>
                    <div class="summary-row summary-row--discount">
                        <span><?= $isRtl ? 'خصم الكوبون (' . esc_html($cart['coupon_code']) . ')' : 'Promo discount (' . esc_html($cart['coupon_code']) . ')' ?></span>
                        <strong>- <?= $cart['discount_formatted'] ?></strong>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span><?= $isRtl ? 'رسوم الشحن والتوصيل' : 'Refrigerated shipping' ?></span>
                    <strong id="summaryShippingFee"><?= $shippingFee == 0 ? ($isRtl ? 'مجاناً' : 'FREE') : (number_format($shippingFee, 2) . ' ' . currency()) ?></strong>
                </div>
                <div class="summary-row summary-row--total">
                    <span><?= $isRtl ? 'المجموع النهائي' : 'Grand total' ?><br><small class="muted"><?= $isRtl ? 'شامل ضريبة القيمة المضافة' : 'Includes 15% VAT' ?></small></span>
                    <strong><span id="summaryGrandTotal"><?= number_format($total, 2) ?></span> <?= currency() ?></strong>
                </div>

                <button type="submit" class="button button--primary commerce-primary-action">
                    <?= $isRtl ? 'تأكيد الطلب الآن' : 'Place Order Now' ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?>
                </button>
                <p class="commerce-security-note"><?= $isRtl
                    ? 'بالضغط على تأكيد الطلب، فإنك توافق على شروط وسياسة الشحن في متجر تمرنا للتمور الفاخرة.'
                    : 'By placing this order, you agree to Tumurna Luxury Dates shipping and quality terms.' ?></p>
            </aside>
        </form>
    </div>
</section>

<script>
    function toggleShippingFields(type) {
        const homeDiv = document.getElementById('homeDeliveryFields');
        const branchDiv = document.getElementById('branchPickupFields');
        if (type === 'branch') {
            homeDiv.classList.add('hidden');
            branchDiv.classList.remove('hidden');
        } else {
            homeDiv.classList.remove('hidden');
            branchDiv.classList.add('hidden');
        }
        recalculateShipping();
    }

    function recalculateShipping() {
        const isBranch = document.querySelector('input[name="shipping_type"]:checked')?.value === 'branch_pickup';
        const citySelect = document.getElementById('citySelect');
        const city = citySelect ? citySelect.value : 'الرياض';

        const formData = new FormData();
        formData.append('shipping_type', isBranch ? 'branch_pickup' : 'home_delivery');
        formData.append('city', city);

        fetch(window.appUrl('/api/checkout/calculate-shipping'), { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const feeSpan = document.getElementById('summaryShippingFee');
                if (feeSpan) {
                    const isFree = data.shipping_fee === 0;
                    feeSpan.innerText = isFree ? (<?= json_encode($isRtl ? 'مجاناً' : 'FREE') ?>) : data.shipping_fee_formatted;
                }
                const noteText = document.getElementById('cityDeliveryNoteText');
                if (noteText && data.delivery_estimate) noteText.innerText = data.delivery_estimate;
                const totalSpan = document.getElementById('summaryGrandTotal');
                if (totalSpan) totalSpan.innerText = Number(data.total).toFixed(2);
            }
        });
    }
</script>
