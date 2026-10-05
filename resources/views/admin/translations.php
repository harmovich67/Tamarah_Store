<?php
use App\Core\I18n;

$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();

$catIcons = [
    'all' => 'layers',
    'storefront' => 'globe',
    'catalog' => 'sparkles',
    'checkout' => 'shopping-cart',
    'orders' => 'package',
    'profile_otp' => 'user-check',
    'acf' => 'boxes',
    'admin' => 'shield-check',
    'general' => 'message-square',
    'missing' => 'alert-circle',
];

$catLabelsAr = [
    'all' => 'كافة العبارات',
    'storefront' => 'واجهة المتجر وتمرنا',
    'catalog' => 'أصناف التمور والخيارات',
    'checkout' => 'السلة والدفع ومدى والشحن',
    'orders' => 'الطلبات والشحن المبرد',
    'profile_otp' => 'حساب العميل والعناوين',
    'acf' => 'الحقول المخصصة (ACF)',
    'security_cache' => 'الكاش والأمان واللوجز',
    'admin' => 'لوحة الإدارة والتحكم',
    'general' => 'رسائل وتنبيهات عامة',
    'missing' => 'بحاجة لترجمة',
];

$catLabelsEn = [
    'all' => 'All Translations',
    'storefront' => 'Storefront & Tamrna',
    'catalog' => 'Dates Varieties & Packaging',
    'checkout' => 'Cart, Checkout & Payments',
    'orders' => 'Orders & Cold Shipping',
    'profile_otp' => 'Profile & National Address',
    'acf' => 'ACF Custom Fields',
    'security_cache' => 'Cache, Logs & Security',
    'admin' => 'Admin Dashboard',
    'general' => 'General & System',
    'missing' => 'Missing Translations',
];

$categoryBadges = [
    'storefront' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200', 'label_ar' => '🌴 واجهة تمرنا', 'label_en' => '🌴 Storefront'],
    'catalog' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200', 'label_ar' => '✨ أصناف التمور', 'label_en' => '✨ Dates & Catalog'],
    'checkout' => ['bg' => 'bg-green-50', 'text' => 'text-green-800', 'border' => 'border-green-200', 'label_ar' => '💳 السلة ومدى والدفع', 'label_en' => '💳 Checkout & Pay'],
    'orders' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-800', 'border' => 'border-cyan-200', 'label_ar' => '📦 الطلبات والشحن المبرد', 'label_en' => '📦 Orders & Shipping'],
    'profile_otp' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-800', 'border' => 'border-purple-200', 'label_ar' => '👤 حساب العميل والعناوين', 'label_en' => '👤 Profile & Addresses'],
    'acf' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-800', 'border' => 'border-teal-200', 'label_ar' => '🧩 الحقول المخصصة ACF', 'label_en' => '🧩 ACF Builder'],
    'security_cache' => ['bg' => 'bg-stone-100', 'text' => 'text-stone-800', 'border' => 'border-stone-200', 'label_ar' => '⚡ الكاش والأمان واللوجز', 'label_en' => '⚡ Cache & Security'],
    'admin' => ['bg' => 'bg-[#edf4e8]', 'text' => 'text-[#315b2b]', 'border' => 'border-[#568d43]/30', 'label_ar' => '🛡️ لوحة التحكم', 'label_en' => '🛡️ Admin Panel'],
    'general' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label_ar' => '💬 رسائل عامة', 'label_en' => '💬 General'],
];
?>

<div class="w-full space-y-6 pb-16">
    
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-[#1e331c] to-slate-900 text-white p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-[#568d43]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 bg-[#568d43]/20 text-[#c8e6b8] px-3 py-1 rounded-full text-xs font-bold border border-[#568d43]/30">
                <i data-lucide="languages" class="w-3.5 h-3.5 text-amber-300"></i>
                <span><?= $locale === 'ar' ? 'التحكم الذكي بنصوص وترجمات متجر تمرنا' : 'Tamrna Dynamic Translations Engine' ?></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                <span><?= __('translations_management') ?></span>
                <span class="text-xs font-extrabold bg-amber-400 text-slate-950 px-3 py-1 rounded-full shadow-sm">
                    <?= $totalCount ?> <?= $locale === 'ar' ? 'مفتاح معتمد' : 'Keys' ?>
                </span>
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-slate-300 max-w-2xl leading-relaxed">
                <?= $locale === 'ar' 
                    ? 'تحكم احترافي وذكي ومباشر في كافة نصوص متجر تمرنا: واجهة المتجر، أصناف التمور، الحجز المسبق للحصاد، بطاقات الإهداء، السلة والشحن المبرد، بوابات الدفع (مدى وأبل باي وتمارا وتابي)، حساب العميل ولوحة الإدارة باللغتين العربية والإنجليزية.'
                    : 'Smart and instant live control over all Tamrna store text: storefront, date varieties, harvest pre-orders, gift cards, cart, cold shipping, Saudi payment gateways (Mada, Apple Pay, Tamara, Tabby), profile, and admin dashboard in Arabic & English.' ?>
            </p>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-3">
            <button onclick="openNewTranslationModal()" class="px-5 py-3.5 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black rounded-2xl text-xs sm:text-sm flex items-center gap-2 shadow-lg shadow-amber-400/20 transition transform active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span><?= $locale === 'ar' ? 'إضافة مفتاح ترجمة جديد' : 'Add New Translation Key' ?></span>
            </button>
            <a href="<?= url('/') ?>" target="_blank" class="px-4 py-3.5 bg-white/10 hover:bg-white/20 text-white rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 border border-white/15 transition">
                <i data-lucide="external-link" class="w-4 h-4 text-cyan-300"></i>
                <span><?= $locale === 'ar' ? 'معاينة المتجر الخارجي' : 'Live Storefront' ?></span>
            </a>
        </div>
    </div>

    <!-- Smart Categories Navigation Tabs -->
    <div class="bg-white p-3 sm:p-4 rounded-3xl border border-slate-200 shadow-sm overflow-x-auto">
        <div class="flex items-center gap-2 min-w-max">
            <?php foreach (['all', 'storefront', 'catalog', 'checkout', 'orders', 'profile_otp', 'acf', 'security_cache', 'admin', 'general', 'missing'] as $cat): ?>
                <?php 
                    $isActive = ($currentCat === $cat) || ($cat === 'missing' && $missingOnly);
                    $count = $categoryCounts[$cat] ?? 0;
                    $url = $cat === 'all' 
                        ? url('/admin/translations') 
                        : ($cat === 'missing' ? url('/admin/translations?missing=1') : url('/admin/translations?cat=' . $cat));
                ?>
                <a href="<?= $url ?>" 
                   class="px-4 py-2.5 rounded-2xl text-xs font-black flex items-center gap-2 transition <?= $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200/70' ?>">
                    <i data-lucide="<?= $catIcons[$cat] ?? 'folder' ?>" class="w-3.5 h-3.5 <?= $isActive ? 'text-amber-300' : 'text-slate-400' ?>"></i>
                    <span><?= $locale === 'ar' ? ($catLabelsAr[$cat] ?? $cat) : ($catLabelsEn[$cat] ?? $cat) ?></span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black <?= $isActive ? 'bg-white/20 text-white' : ($cat === 'missing' && $count > 0 ? 'bg-red-100 text-red-700' : 'bg-slate-200 text-slate-600') ?>">
                        <?= $count ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Live Real-Time Interactive Filter & Search Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-center">
            <!-- Client-Side Instant Live Search -->
            <div class="relative flex-1 w-full">
                <input type="text" id="liveSearchInput" oninput="onLiveSearch(this.value)" 
                       placeholder="<?= $locale === 'ar' ? 'بحث لحظي فوري: اكتب أي مفتاح، كلمة بالعربية، أو بالإنجليزية للتصفية الفورية...' : 'Live instant search: type any key, Arabic, or English text to filter dynamically...' ?>"
                       value="<?= htmlspecialchars($search) ?>"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl <?= $isRtl ? 'pr-11 pl-4' : 'pl-11 pr-4' ?> py-3 text-xs sm:text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white focus:outline-none transition">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute <?= $isRtl ? 'right-4' : 'left-4' ?> top-3.5"></i>
                <button type="button" id="clearSearchBtn" onclick="clearLiveSearch()" class="<?= empty($search) ? 'hidden' : '' ?> absolute <?= $isRtl ? 'left-3' : 'right-3' ?> top-3 text-slate-400 hover:text-slate-700 text-xs p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
                <span id="filteredCounterBadge" class="text-xs font-bold text-slate-500 bg-slate-100 px-3.5 py-2.5 rounded-2xl whitespace-nowrap">
                    <?= $locale === 'ar' ? 'عرض' : 'Showing' ?> <b id="visibleCount" class="text-indigo-600"><?= count($translations) ?></b> <?= $locale === 'ar' ? 'من' : 'of' ?> <?= count($translations) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Global Toast Alert For AJAX Save -->
    <div id="transToast" class="fixed bottom-6 <?= $isRtl ? 'left-6' : 'right-6' ?> z-50 transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 bg-slate-950 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700">
        <div id="transToastIcon" class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
            <i data-lucide="check" class="w-4 h-4"></i>
        </div>
        <span id="transToastText" class="text-xs font-bold"></span>
    </div>

    <!-- Translations List (Modern High-Performance Cards) -->
    <div id="translationsListContainer" class="space-y-4">
        <?php foreach ($translations as $item): ?>
            <?php 
                $catMeta = $categoryBadges[$item['category']] ?? $categoryBadges['general'];
                $isMiss = $item['is_missing'];
            ?>
            <div class="translation-row bg-white rounded-3xl border <?= $isMiss ? 'border-amber-300 bg-amber-50/10' : 'border-slate-200/80' ?> p-5 sm:p-6 shadow-sm hover:border-indigo-300 hover:shadow-md transition space-y-4"
                 data-key="<?= htmlspecialchars(strtolower($item['key'])) ?>"
                 data-ar="<?= htmlspecialchars(strtolower($item['ar'])) ?>"
                 data-en="<?= htmlspecialchars(strtolower($item['en'])) ?>"
                 data-cat="<?= $item['category'] ?>"
                 id="row-<?= htmlspecialchars($item['key']) ?>">
                
                <!-- Card Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Key Pill with Copy -->
                        <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-xl cursor-pointer select-all border border-slate-200/60 inline-flex items-center gap-1.5 transition"
                              onclick="copyKeyName('<?= htmlspecialchars($item['key']) ?>')" title="انقر للنسخ">
                            <i data-lucide="code" class="w-3 h-3 text-indigo-500"></i>
                            <span><?= htmlspecialchars($item['key']) ?></span>
                            <i data-lucide="copy" class="w-2.5 h-2.5 text-slate-400"></i>
                        </span>

                        <!-- Category Tag -->
                        <span class="text-[11px] font-black px-2.5 py-0.5 rounded-lg border <?= $catMeta['bg'] ?> <?= $catMeta['text'] ?> <?= $catMeta['border'] ?>">
                            <?= $locale === 'ar' ? $catMeta['label_ar'] : $catMeta['label_en'] ?>
                        </span>

                        <!-- Missing Alert Badge -->
                        <?php if ($isMiss): ?>
                            <span class="text-[10px] font-black text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md flex items-center gap-1">
                                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                <span><?= $locale === 'ar' ? 'يحتاج إكمال ترجمة' : 'Incomplete' ?></span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Row Actions -->
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="quickSaveTranslation('<?= htmlspecialchars($item['key']) ?>')" 
                                id="btn-save-<?= htmlspecialchars($item['key']) ?>"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm transition transform active:scale-95">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span><?= $locale === 'ar' ? 'حفظ سريع' : 'Quick Save' ?></span>
                        </button>
                        
                        <form action="<?= url('/admin/translations/delete') ?>" method="POST" onsubmit="return confirm('<?= $locale === 'ar' ? 'هل أنت متأكد من رغبتك في حذف هذا المفتاح نهائياً؟' : 'Are you sure you want to delete this key permanently?' ?>');" class="inline">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($item['key']) ?>">
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition" title="حذف">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Dual Language Editable Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Arabic Box -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black text-slate-700 flex items-center justify-between">
                            <span class="flex items-center gap-1 text-emerald-700">
                                <span>🇪🇬</span>
                                <span><?= $locale === 'ar' ? 'النص باللغة العربية (Arabic)' : 'Arabic Text' ?></span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-normal">يظهر للمستخدمين بالعربية</span>
                        </label>
                        <textarea id="ar-<?= htmlspecialchars($item['key']) ?>" rows="2" dir="rtl"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition leading-relaxed resize-y"
                                  placeholder="اكتب النص بالعربية..."><?= htmlspecialchars($item['ar']) ?></textarea>
                    </div>

                    <!-- English Box -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black text-slate-700 flex items-center justify-between">
                            <span class="flex items-center gap-1 text-indigo-700">
                                <span>🇬🇧</span>
                                <span><?= $locale === 'ar' ? 'النص باللغة الإنجليزية (English)' : 'English Text' ?></span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-normal">Displays for English users</span>
                        </label>
                        <textarea id="en-<?= htmlspecialchars($item['key']) ?>" rows="2" dir="ltr"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs sm:text-sm font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition leading-relaxed resize-y"
                                  placeholder="Type English text..."><?= htmlspecialchars($item['en']) ?></textarea>
                    </div>

                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty Filter Notice -->
    <div id="noResultsNotice" class="hidden bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-3 shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto">
            <i data-lucide="search-x" class="w-8 h-8"></i>
        </div>
        <h3 class="text-base font-bold text-slate-900"><?= $locale === 'ar' ? 'لم يتم العثور على أي عبارات مطابقة' : 'No matching translations found' ?></h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto"><?= $locale === 'ar' ? 'جرب البحث بكلمة أخرى، أو اضغط أدناه لإلغاء التصفية وإظهار كافة العبارات.' : 'Try different keywords or clear filter.' ?></p>
        <button type="button" onclick="clearLiveSearch()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition">
            <?= $locale === 'ar' ? 'إعادة ضبط البحث' : 'Reset Search' ?>
        </button>
    </div>

</div>

<!-- Add New Translation Key Modal -->
<div id="newTranslationModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <i data-lucide="key" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base"><?= $locale === 'ar' ? 'إضافة مفتاح ترجمة جديد' : 'Add New Translation Key' ?></h3>
                    <p class="text-xs text-slate-400"><?= $locale === 'ar' ? 'سيتم تضمين المفتاح تلقائياً في ملفات اللغات ar.php و en.php' : 'Key will be registered in both ar.php & en.php' ?></p>
                </div>
            </div>
            <button onclick="closeNewTranslationModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/translations/update') ?>" method="POST" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5"><?= $locale === 'ar' ? 'مفتاح الترجمة (Key Slug) *' : 'Key Slug *' ?></label>
                <div class="relative">
                    <input type="text" id="newKeyInput" name="key" required placeholder="e.g. hero_summer_sale_title"
                           class="w-full font-mono bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">يُفضل استخدام حروف إنجليزية صغيرة وشرطة سفلية (snake_case).</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5"><?= $locale === 'ar' ? 'النص بالعربية *' : 'Arabic Translation *' ?></label>
                <textarea name="ar_val" rows="3" dir="rtl" required placeholder="اكتب النص باللغة العربية..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1.5"><?= $locale === 'ar' ? 'النص بالإنجليزية *' : 'English Translation *' ?></label>
                <textarea name="en_val" rows="3" dir="ltr" required placeholder="Type the text in English..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeNewTranslationModal()" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                    <?= $locale === 'ar' ? 'إلغاء' : 'Cancel' ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-md shadow-indigo-600/20 transition">
                    <?= $locale === 'ar' ? 'حفظ وإضافة المفتاح' : 'Save & Register Key' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Copy Key Name
    function copyKeyName(key) {
        navigator.clipboard.writeText(key).then(() => {
            showTransToast('تم نسخ المفتاح: ' + key, false);
        });
    }

    // Modal Controls
    function openNewTranslationModal() {
        document.getElementById('newTranslationModal').classList.remove('hidden');
        document.getElementById('newKeyInput').focus();
    }

    function closeNewTranslationModal() {
        document.getElementById('newTranslationModal').classList.add('hidden');
    }

    // Real-Time Dynamic Search & Client Filter
    function onLiveSearch(query) {
        const q = query.trim().toLowerCase();
        const clearBtn = document.getElementById('clearSearchBtn');
        if (q.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        const rows = document.querySelectorAll('.translation-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const key = row.getAttribute('data-key') || '';
            const ar = row.getAttribute('data-ar') || '';
            const en = row.getAttribute('data-en') || '';

            // Also check current live textarea values
            const curAr = (row.querySelector('textarea[id^="ar-"]')?.value || '').toLowerCase();
            const curEn = (row.querySelector('textarea[id^="en-"]')?.value || '').toLowerCase();

            if (!q || key.includes(q) || ar.includes(q) || en.includes(q) || curAr.includes(q) || curEn.includes(q)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').textContent = visibleCount;
        const noResults = document.getElementById('noResultsNotice');
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }

    function clearLiveSearch() {
        const input = document.getElementById('liveSearchInput');
        input.value = '';
        onLiveSearch('');
    }

    // Quick AJAX Save for Instant In-place Updates
    async function quickSaveTranslation(key) {
        const btn = document.getElementById('btn-save-' + key);
        const arVal = document.getElementById('ar-' + key).value;
        const enVal = document.getElementById('en-' + key).value;

        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i><span>جاري الحفظ...</span>';
        btn.disabled = true;
        lucide.createIcons();

        try {
            const res = await fetch(appUrl('/admin/translations/update'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    key: key,
                    ar_val: arVal,
                    en_val: enVal
                })
            });

            const data = await res.json();
            if (data.success) {
                // Update row attributes for live search
                const row = document.getElementById('row-' + key);
                if (row) {
                    row.setAttribute('data-ar', arVal.toLowerCase());
                    row.setAttribute('data-en', enVal.toLowerCase());
                }

                btn.className = 'px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm transition';
                btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5"></i><span>تم الحفظ بنجاح!</span>';
                lucide.createIcons();

                showTransToast('تم حفظ المفتاح [' + key + '] بنجاح في اللغتين!', false);

                setTimeout(() => {
                    btn.className = 'px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm transition transform active:scale-95';
                    btn.innerHTML = '<i data-lucide="save" class="w-3.5 h-3.5"></i><span>حفظ سريع</span>';
                    btn.disabled = false;
                    lucide.createIcons();
                }, 2200);
            } else {
                throw new Error(data.error || 'Failed to save');
            }
        } catch (err) {
            console.error(err);
            btn.className = 'px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm transition';
            btn.innerHTML = '<i data-lucide="alert-circle" class="w-3.5 h-3.5"></i><span>خطأ في الحفظ</span>';
            lucide.createIcons();
            btn.disabled = false;
            showTransToast('حدث خطأ أثناء الحفظ، يرجى المحاولة مرة أخرى', true);
        }
    }

    // Toast Alert Helper
    function showTransToast(msg, isError) {
        const toast = document.getElementById('transToast');
        const text = document.getElementById('transToastText');
        const icon = document.getElementById('transToastIcon');

        text.textContent = msg;
        if (isError) {
            icon.className = 'w-7 h-7 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center';
            icon.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4"></i>';
        } else {
            icon.className = 'w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center';
            icon.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
        }
        lucide.createIcons();

        toast.classList.remove('translate-y-24', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-24', 'opacity-0');
        }, 3000);
    }

    // Initialize Lucide Icons
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
