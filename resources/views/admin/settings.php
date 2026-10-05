<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="w-full space-y-8 pb-16">
    
    <!-- Top Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#eff5ee] text-[#244c20] border border-[#d2e4d0] flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="credit-card" class="w-6 h-6 text-[#244c20]"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight"><?= __('payment_gateway_settings') ?></h1>
                    <p class="text-xs sm:text-sm font-semibold text-stone-600 mt-1"><?= $locale === 'en' ? 'Configure electronic payment gateways, verify API keys, and test live connections' : 'ربط بوابات الدفع الإلكتروني المعتمدة، فحص صلاحية المفاتيح، وتأكيد الربط الفعلي' ?></p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/checkout') ?>" target="_blank" class="px-5 py-3 bg-[#faf7f0] hover:bg-[#f2ecdd] text-[#8c5d25] border border-[#e5d9bf] rounded-2xl text-xs sm:text-sm font-black flex items-center gap-2 transition w-fit shadow-xs">
                <i data-lucide="external-link" class="w-4 h-4 text-[#c49a52]"></i>
                <span><?= $locale === 'en' ? 'Preview Checkout' : 'معاينة صفحة الدفع' ?></span>
            </a>
        </div>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-400 rounded-3xl text-sm font-black text-emerald-900 flex items-center gap-3 shadow-sm animate-fade-in">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span><?= $locale === 'en' ? 'Payment gateway settings and credentials have been successfully updated!' : 'تم تحديث وحفظ كافة إعدادات ومفاتيح بوابات الدفع والشحن بنجاح!' ?></span>
        </div>
    <?php endif; ?>

    <form id="paymentSettingsForm" action="<?= url('/admin/settings/update') ?>" method="POST" class="space-y-8 text-sm">
        <?= csrf_field() ?>

        <!-- ============================================================================== -->
        <!-- 1. PAYMOB GATEWAY (باي موب - مصر والشرق الأوسط) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6 hover:border-[#1a365d]/40 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#0b2545] text-white flex items-center justify-center font-black shadow-sm shrink-0">
                        <span class="text-sm font-bold tracking-wider">PAYMOB</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-stone-900"><?= __('paymob_title') ?></h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-[10px] font-black">Cards & Wallets</span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5"><?= __('paymob_desc') ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <select name="enable_paymob" class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2 text-xs font-black text-stone-900 focus:ring-2 focus:ring-blue-800">
                        <option value="1" <?= ($settings['enable_paymob'] ?? '1') == '1' ? 'selected' : '' ?>>مفعّلة (Active)</option>
                        <option value="0" <?= ($settings['enable_paymob'] ?? '1') == '0' ? 'selected' : '' ?>>معطّلة (Disabled)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">بيئة العمل (Mode)</label>
                    <select name="paymob_mode" id="paymob_mode" class="w-full bg-stone-50 border border-stone-200 focus:border-blue-900 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="test" <?= ($settings['paymob_mode'] ?? 'test') === 'test' ? 'selected' : '' ?>>بيئة تجريبية (Sandbox / Test Mode)</option>
                        <option value="live" <?= ($settings['paymob_mode'] ?? '') === 'live' ? 'selected' : '' ?>>بيئة حية إنتاجية (Live Production)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-black text-stone-800 text-xs mb-1.5"><?= __('paymob_api_key_label') ?> <span class="text-rose-500">*</span></label>
                    <input type="password" name="paymob_api_key" id="paymob_api_key" value="<?= htmlspecialchars($settings['paymob_api_key'] ?? '') ?>" dir="ltr" placeholder="ZXlKaGJHY2lPaUpTVXpVe..."
                           class="w-full bg-stone-50 border border-stone-200 focus:border-blue-900 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5"><?= __('paymob_integration_id_label') ?> <span class="text-rose-500">*</span></label>
                    <input type="text" name="paymob_integration_id" id="paymob_integration_id" value="<?= htmlspecialchars($settings['paymob_integration_id'] ?? '') ?>" dir="ltr" placeholder="e.g. 123456"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-blue-900 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5"><?= __('paymob_iframe_id_label') ?> <span class="text-rose-500">*</span></label>
                    <input type="text" name="paymob_iframe_id" id="paymob_iframe_id" value="<?= htmlspecialchars($settings['paymob_iframe_id'] ?? '') ?>" dir="ltr" placeholder="e.g. 789012"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-blue-900 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5"><?= __('paymob_hmac_label') ?></label>
                    <input type="password" name="paymob_hmac" id="paymob_hmac" value="<?= htmlspecialchars($settings['paymob_hmac'] ?? '') ?>" dir="ltr" placeholder="HMAC Secret Key"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-blue-900 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>
            </div>

            <!-- Test Connection Button & Status Box -->
            <div class="pt-3 border-t border-stone-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <button type="button" onclick="testGatewayConnection('paymob')" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-black text-xs flex items-center gap-2 transition shadow-xs cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>فحص اتصال Paymob الفعلي (Test Connection)</span>
                </button>
                <div id="testStatus_paymob" class="text-xs font-bold hidden px-4 py-2 rounded-xl flex items-center gap-2"></div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 2. MOYASAR & CARD GATEWAY (ميسر - مدى و Apple Pay والبطاقات) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6 hover:border-[#107c41]/40 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#0f5132] text-white flex items-center justify-center font-black shadow-sm shrink-0">
                        <i data-lucide="shield-check" class="w-6 h-6 text-emerald-300"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-stone-900"><?= __('moyasar_title') ?></h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-black">SAMA Licensed</span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5"><?= __('moyasar_desc') ?></p>
                    </div>
                </div>

                <span class="px-3 py-1 bg-emerald-50 text-emerald-800 font-black text-xs rounded-full border border-emerald-200">سداد فوري مباشر</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">مزود بوابة الدفع المباشر (Primary Direct Gateway)</label>
                    <select name="gateway_provider" id="gateway_provider" class="w-full bg-stone-50 border border-stone-200 focus:border-emerald-800 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="moyasar" <?= ($settings['gateway_provider'] ?? 'moyasar') === 'moyasar' ? 'selected' : '' ?>>ميسر (Moyasar - معتمد من البنك المركزي السعودي مدى / أبل باي)</option>
                        <option value="stripe" <?= ($settings['gateway_provider'] ?? '') === 'stripe' ? 'selected' : '' ?>>سترايب (Stripe Payments - بطاقات دولية وعالمية)</option>
                        <option value="paymob" <?= ($settings['gateway_provider'] ?? '') === 'paymob' ? 'selected' : '' ?>>باي موب (Paymob - تحصيل بطاقات ومحافظ)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">بيئة العمل (Environment Mode)</label>
                    <select name="gateway_mode" id="gateway_mode" class="w-full bg-stone-50 border border-stone-200 focus:border-emerald-800 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="test" <?= ($settings['gateway_mode'] ?? 'test') === 'test' ? 'selected' : '' ?>>بيئة تجريبية واختبار (Sandbox / Test Mode)</option>
                        <option value="live" <?= ($settings['gateway_mode'] ?? '') === 'live' ? 'selected' : '' ?>>بيئة حية ومباشرة (Live Production)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">المفتاح العام (Publishable Key)</label>
                    <input type="text" name="gateway_publishable_key" id="gateway_publishable_key" value="<?= htmlspecialchars($settings['gateway_publishable_key'] ?? '') ?>" dir="ltr" placeholder="pk_test_... or pk_live_..."
                           class="w-full bg-stone-50 border border-stone-200 focus:border-emerald-800 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">المفتاح السري (Secret Key) <span class="text-rose-500">*</span></label>
                    <input type="password" name="gateway_secret_key" id="gateway_secret_key" value="<?= htmlspecialchars($settings['gateway_secret_key'] ?? '') ?>" dir="ltr" placeholder="sk_test_... or sk_live_..."
                           class="w-full bg-stone-50 border border-stone-200 focus:border-emerald-800 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>
            </div>

            <!-- Channels Activation -->
            <div class="pt-3 border-t border-stone-100">
                <label class="block font-black text-stone-800 text-xs mb-3">طرق الدفع المفعلة عبر البوابة المباشرة:</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl border border-stone-200 bg-[#faf8f3] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-white border border-stone-300 text-xs font-black text-[#315b2b]">mada</span>
                            <span class="font-black text-xs text-stone-900">بطاقات مدى (Mada)</span>
                        </div>
                        <select name="enable_mada" class="bg-white border border-stone-200 rounded-lg px-2.5 py-1 text-xs font-bold">
                            <option value="1" <?= ($settings['enable_mada'] ?? '1') == '1' ? 'selected' : '' ?>>مفعل</option>
                            <option value="0" <?= ($settings['enable_mada'] ?? '1') == '0' ? 'selected' : '' ?>>معطل</option>
                        </select>
                    </div>

                    <div class="p-4 rounded-2xl border border-stone-200 bg-[#faf8f3] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-black text-white text-xs font-bold">Pay</span>
                            <span class="font-black text-xs text-stone-900">Apple Pay</span>
                        </div>
                        <select name="enable_apple_pay" class="bg-white border border-stone-200 rounded-lg px-2.5 py-1 text-xs font-bold">
                            <option value="1" <?= ($settings['enable_apple_pay'] ?? '1') == '1' ? 'selected' : '' ?>>مفعل</option>
                            <option value="0" <?= ($settings['enable_apple_pay'] ?? '1') == '0' ? 'selected' : '' ?>>معطل</option>
                        </select>
                    </div>

                    <div class="p-4 rounded-2xl border border-stone-200 bg-[#faf8f3] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-blue-700 text-white text-[11px] font-bold">VISA</span>
                            <span class="font-black text-xs text-stone-900">فيزا / ماستركارد</span>
                        </div>
                        <select name="enable_credit_card" class="bg-white border border-stone-200 rounded-lg px-2.5 py-1 text-xs font-bold">
                            <option value="1" <?= ($settings['enable_credit_card'] ?? '1') == '1' ? 'selected' : '' ?>>مفعل</option>
                            <option value="0" <?= ($settings['enable_credit_card'] ?? '1') == '0' ? 'selected' : '' ?>>معطل</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Test Connection Button & Status Box -->
            <div class="pt-3 border-t border-stone-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <button type="button" onclick="testGatewayConnection('moyasar')" class="px-5 py-2.5 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-black text-xs flex items-center gap-2 transition shadow-xs cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>فحص اتصال Moyasar الفعلي (Test Connection)</span>
                </button>
                <div id="testStatus_moyasar" class="text-xs font-bold hidden px-4 py-2 rounded-xl flex items-center gap-2"></div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 3. STRIPE GATEWAY (سترايب - دفع عالمي بالبطاقات) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6 hover:border-[#635bff]/40 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#635bff] text-white flex items-center justify-center font-black shadow-sm shrink-0">
                        <span class="text-base font-black">stripe</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-stone-900"><?= __('stripe_title') ?></h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-violet-50 text-violet-800 border border-violet-200 text-[10px] font-black">Global Checkout</span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5"><?= __('stripe_desc') ?></p>
                    </div>
                </div>
                <span class="text-xs text-stone-400 font-semibold">استخدم المفاتيح أعلاه مع اختيار Stripe كمزود</span>
            </div>

            <!-- Test Connection Button & Status Box -->
            <div class="pt-2 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <button type="button" onclick="testGatewayConnection('stripe')" class="px-5 py-2.5 rounded-xl bg-[#635bff] hover:bg-[#5248db] text-white font-black text-xs flex items-center gap-2 transition shadow-xs cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>فحص اتصال Stripe الفعلي (Test Connection)</span>
                </button>
                <div id="testStatus_stripe" class="text-xs font-bold hidden px-4 py-2 rounded-xl flex items-center gap-2"></div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 4. TAMARA BNPL (تمارا - قسّمها على 4 دفعات) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6 hover:border-[#ff6a6a]/40 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#ff6a6a] text-white flex items-center justify-center font-black text-sm shadow-sm shrink-0">
                        تمارا
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-stone-900"><?= __('tamara_title') ?></h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-800 border border-rose-200 text-[10px] font-black">Sharia Compliant</span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5"><?= __('tamara_desc') ?></p>
                    </div>
                </div>
                <select name="enable_tamara" class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2 text-xs font-black text-stone-900 focus:ring-2 focus:ring-rose-500">
                    <option value="1" <?= ($settings['enable_tamara'] ?? '1') == '1' ? 'selected' : '' ?>>تمارا: مفعّلة في المتجر</option>
                    <option value="0" <?= ($settings['enable_tamara'] ?? '1') == '0' ? 'selected' : '' ?>>تمارا: معطّلة</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">بيئة العمل (Environment)</label>
                    <select name="tamara_mode" id="tamara_mode" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="sandbox" <?= ($settings['tamara_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>بيئة تجريبية (Sandbox)</option>
                        <option value="live" <?= ($settings['tamara_mode'] ?? '') === 'live' ? 'selected' : '' ?>>بيئة إنتاجية حية (Live)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رمز التاجر (Merchant API Token) <span class="text-rose-500">*</span></label>
                    <input type="password" name="tamara_merchant_token" id="tamara_merchant_token" value="<?= htmlspecialchars($settings['tamara_merchant_token'] ?? '') ?>" dir="ltr" placeholder="Bearer Token..."
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>
            </div>

            <!-- Test Connection Button & Status Box -->
            <div class="pt-3 border-t border-stone-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <button type="button" onclick="testGatewayConnection('tamara')" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs flex items-center gap-2 transition shadow-xs cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>فحص اتصال Tamara الفعلي (Test Connection)</span>
                </button>
                <div id="testStatus_tamara" class="text-xs font-bold hidden px-4 py-2 rounded-xl flex items-center gap-2"></div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 5. TABBY BNPL (تابي - قسّمها على 4 دفعات) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6 hover:border-[#29e7cd]/50 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#29e7cd] text-stone-950 flex items-center justify-center font-black text-sm shadow-sm shrink-0">
                        tabby
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-stone-900"><?= __('tabby_title') ?></h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-teal-50 text-teal-800 border border-teal-200 text-[10px] font-black">Zero Interest</span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5"><?= __('tabby_desc') ?></p>
                    </div>
                </div>
                <select name="enable_tabby" class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2 text-xs font-black text-stone-900 focus:ring-2 focus:ring-teal-500">
                    <option value="1" <?= ($settings['enable_tabby'] ?? '1') == '1' ? 'selected' : '' ?>>تابي: مفعّلة في المتجر</option>
                    <option value="0" <?= ($settings['enable_tabby'] ?? '1') == '0' ? 'selected' : '' ?>>تابي: معطّلة</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">بيئة العمل (Environment)</label>
                    <select name="tabby_mode" id="tabby_mode" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="sandbox" <?= ($settings['tabby_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>بيئة تجريبية (Sandbox)</option>
                        <option value="live" <?= ($settings['tabby_mode'] ?? '') === 'live' ? 'selected' : '' ?>>بيئة إنتاجية حية (Live)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">المفتاح العام (Public Key)</label>
                    <input type="text" name="tabby_public_key" id="tabby_public_key" value="<?= htmlspecialchars($settings['tabby_public_key'] ?? '') ?>" dir="ltr" placeholder="pk_test_..."
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">المفتاح السري (Secret Key) <span class="text-rose-500">*</span></label>
                    <input type="password" name="tabby_secret_key" id="tabby_secret_key" value="<?= htmlspecialchars($settings['tabby_secret_key'] ?? '') ?>" dir="ltr" placeholder="sk_test_..."
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-mono text-left">
                </div>
            </div>

            <!-- Test Connection Button & Status Box -->
            <div class="pt-3 border-t border-stone-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <button type="button" onclick="testGatewayConnection('tabby')" class="px-5 py-2.5 rounded-xl bg-teal-800 hover:bg-teal-900 text-white font-black text-xs flex items-center gap-2 transition shadow-xs cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>فحص اتصال Tabby الفعلي (Test Connection)</span>
                </button>
                <div id="testStatus_tabby" class="text-xs font-bold hidden px-4 py-2 rounded-xl flex items-center gap-2"></div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 6. CASH ON DELIVERY (الدفع عند الاستلام) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center font-bold shadow-sm shrink-0">
                        <i data-lucide="banknote" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-stone-900">الدفع عند الاستلام (Cash On Delivery - COD)</h3>
                        <p class="text-xs text-stone-500">تحصيل قيمة الطلب نقداً أو عبر جهاز الشبكة عند وصول المندوب</p>
                    </div>
                </div>
                <select name="enable_cod" class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2 text-xs font-black text-stone-900">
                    <option value="1" <?= ($settings['enable_cod'] ?? '1') == '1' ? 'selected' : '' ?>>الدفع عند الاستلام: مفعّل</option>
                    <option value="0" <?= ($settings['enable_cod'] ?? '1') == '0' ? 'selected' : '' ?>>معطّل</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رسوم خدمة الدفع عند الاستلام الإضافية (<?= currency() ?>)</label>
                    <input type="number" step="0.5" name="cod_fee" value="<?= htmlspecialchars($settings['cod_fee'] ?? '0.00') ?>" placeholder="0.00"
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                    <p class="text-[11px] text-stone-500 mt-1">ضع 0 إذا كانت الخدمة مجانية بدون رسوم تحصيل إضافية</p>
                </div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 7. DIRECT BANK TRANSFER (التحويل البنكي المباشر) -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-[#eff5ee] text-[#244c20] border border-[#d2e4d0] flex items-center justify-center font-bold shadow-sm shrink-0">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-stone-900">التحويل البنكي المباشر (Bank Wire Transfer)</h3>
                        <p class="text-xs text-stone-500">عرض بيانات الحساب البنكي والآيبان للعميل عند إتمام الطلب</p>
                    </div>
                </div>
                <select name="enable_bank_transfer" class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2 text-xs font-black text-stone-900">
                    <option value="1" <?= ($settings['enable_bank_transfer'] ?? '1') == '1' ? 'selected' : '' ?>>التحويل البنكي: مفعّل</option>
                    <option value="0" <?= ($settings['enable_bank_transfer'] ?? '1') == '0' ? 'selected' : '' ?>>معطّل</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">اسم البنك</label>
                    <input type="text" name="bank_name" value="<?= htmlspecialchars($settings['bank_name'] ?? 'مصرف الراجحي') ?>"
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">اسم صاحب الحساب (المستفيد)</label>
                    <input type="text" name="bank_account_name" value="<?= htmlspecialchars($settings['bank_account_name'] ?? 'مؤسسة المتجر الرسمية') ?>"
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رقم الآيبان الدولي (IBAN)</label>
                    <input type="text" name="bank_iban" value="<?= htmlspecialchars($settings['bank_iban'] ?? '') ?>" dir="ltr" placeholder="SA..."
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-mono font-bold text-left text-stone-900">
                </div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- 8. SHIPPING & GUEST CHECKOUT POLICIES -->
        <!-- ============================================================================== -->
        <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3.5 border-b border-stone-100 pb-4">
                <div class="w-12 h-12 rounded-2xl bg-[#faf3e8] text-[#8c5d25] flex items-center justify-center font-bold shadow-sm shrink-0">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">سياسات الشحن والشراء كزائر (Checkout Policies)</h3>
                    <p class="text-xs text-stone-500">تسعيرة الشحن القياسية، حد الشحن المجاني، والتحكم في إمكانية الشراء السريع</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رسوم الشحن القياسية (<?= currency() ?>)</label>
                    <input type="number" step="0.5" name="shipping_standard_fee" value="<?= htmlspecialchars($settings['shipping_standard_fee'] ?? '25.00') ?>"
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">حد الشحن المجاني للطلب (<?= currency() ?>)</label>
                    <input type="number" step="1" name="shipping_free_threshold" value="<?= htmlspecialchars($settings['shipping_free_threshold'] ?? '300.00') ?>"
                           class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                    <p class="text-[11px] text-stone-500 mt-1">الطلبات التي تتجاوز هذا المبلغ تحصل على شحن مجاني تلقائياً</p>
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">تفعيل خيار الاستلام من الفرع</label>
                    <select name="enable_branch_pickup" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="1" <?= ($settings['enable_branch_pickup'] ?? '1') == '1' ? 'selected' : '' ?>>مفعل (استلام مجاني من الفروع)</option>
                        <option value="0" <?= ($settings['enable_branch_pickup'] ?? '1') == '0' ? 'selected' : '' ?>>معطل (توصيل فقط)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-stone-100 grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">سياسة حسابات العملاء وإتمام الشراء:</label>
                    <select name="allow_guest_checkout" class="w-full bg-stone-50 border border-stone-200 focus:border-[#244c20] rounded-xl px-4 py-3 text-xs font-bold text-stone-900">
                        <option value="1" <?= ($settings['allow_guest_checkout'] ?? '1') == '1' ? 'selected' : '' ?>>
                            ✅ مسموح بالشراء كزائر سريع (بدون إلزام تسجيل حساب)
                        </option>
                        <option value="0" <?= ($settings['allow_guest_checkout'] ?? '1') == '0' ? 'selected' : '' ?>>
                            🔒 إلزام تسجيل الدخول / إنشاء حساب لإتمام الشراء
                        </option>
                    </select>
                </div>

                <div class="p-4 rounded-2xl border border-stone-200 bg-[#faf8f3] text-xs text-stone-600 leading-relaxed">
                    <span class="font-bold text-stone-900 block mb-0.5">ملاحظة سياسة الزائر:</span>
                    عند اختيار "إلزام تسجيل الدخول"، يتم تحويل أي زائر لصفحة الدخول/التسجيل مع الاحتفاظ بسلته وحقوله تلقائياً ليعود فوراً للدفع.
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end pt-4">
            <button type="submit" class="px-8 py-4 bg-[#23481f] hover:bg-[#183415] text-white rounded-2xl font-black text-base shadow-xl shadow-emerald-950/20 transition transform active:scale-98 flex items-center gap-3 cursor-pointer">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>حفظ كافة إعدادات ومفاتيح بوابات الدفع</span>
            </button>
        </div>

    </form>

</div>

<script>
function testGatewayConnection(provider) {
    const statusBox = document.getElementById('testStatus_' + provider);
    if (!statusBox) return;

    statusBox.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-300', 'bg-rose-50', 'text-rose-800', 'border-rose-300', 'border');
    statusBox.classList.add('bg-stone-100', 'text-stone-700', 'border', 'border-stone-200');
    statusBox.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> <?= $isRtl ? 'جاري فحص الاتصال بالخادم...' : 'Testing connection...' ?>';

    const form = document.getElementById('paymentSettingsForm');
    const formData = new FormData(form);
    formData.append('provider', provider);

    fetch(window.appUrl('/api/admin/payment/test-connection'), {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        statusBox.classList.remove('bg-stone-100', 'text-stone-700');
        if (data.success) {
            statusBox.classList.add('bg-emerald-50', 'text-emerald-900', 'border-emerald-300');
            statusBox.innerHTML = '<span class="text-emerald-600 font-black">✓</span> ' + data.message;
        } else {
            statusBox.classList.add('bg-rose-50', 'text-rose-900', 'border-rose-300');
            statusBox.innerHTML = '<span class="text-rose-600 font-black">✕</span> ' + data.message;
        }
    })
    .catch(err => {
        statusBox.classList.remove('bg-stone-100', 'text-stone-700');
        statusBox.classList.add('bg-rose-50', 'text-rose-900', 'border-rose-300');
        statusBox.innerHTML = '<span class="text-rose-600 font-black">✕</span> <?= $isRtl ? 'حدث خطأ بالشبكة أثناء الاتصال' : 'Network error occurred' ?>: ' + err.message;
    });
}
</script>
