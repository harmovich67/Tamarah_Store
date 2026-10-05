<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="w-full space-y-6 pb-12">
    
    <!-- Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#f4f7f2] text-[#315b2b] border border-[#d6e5d2] flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="sliders" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">إعدادات هوية المتجر والمنصة</h1>
                    <p class="text-sm font-semibold text-stone-500 mt-1">تخصيص هوية تَـمْرُنـا، شريط الإعلانات، قنوات التواصل، وعناوين الفروع</p>
                </div>
            </div>
        </div>
        <a href="<?= url('/') ?>" target="_blank" class="px-5 py-3 bg-[#f8f5ed] hover:bg-[#efe8d8] text-[#8c5d25] border border-[#e8dfc8] rounded-2xl text-sm font-black flex items-center gap-2 transition w-fit">
            <i data-lucide="external-link" class="w-4 h-4 text-[#c49a52]"></i>
            <span>معاينة واجهة المتجر</span>
        </a>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-300 rounded-3xl text-sm font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span>تم حفظ وتطبيق إعدادات المتجر بنجاح!</span>
        </div>
    <?php endif; ?>

    <form action="<?= url('/admin/site-settings/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- 1. Brand Identity & Logo -->
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-[#f4f7f2] text-[#315b2b] flex items-center justify-center font-bold">
                    <i data-lucide="layout" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">هوية المتجر والشعار (Logo)</h3>
                    <p class="text-xs text-stone-500">اسم العلامة التجارية وشعار المتجر الرسمي</p>
                </div>
            </div>

            <!-- Logo Upload Field -->
            <div class="bg-[#faf8f3] p-6 rounded-2xl border border-stone-200">
                <label class="block font-black text-stone-900 text-sm mb-3">شعار المتجر (Logo)</label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    <div class="w-24 h-24 rounded-2xl bg-white p-3 border-2 border-[#e8dfc8] shadow-sm flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img id="siteLogoPreview" src="<?= asset($settings['site_logo']) ?>" alt="Site Logo" class="max-w-full max-h-full object-contain">
                        <?php else: ?>
                            <div class="text-[#315b2b] font-serif font-black text-xl">تَـمْرُنـا</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 space-y-2 w-full">
                        <input type="file" id="logoFileInput" name="logo_file" accept="image/*" class="block w-full text-xs text-stone-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-[#315b2b] file:text-white hover:file:bg-[#24451f] transition cursor-pointer">
                        <p class="text-xs font-medium text-stone-500">يُفضل رفع شعار بصيغة PNG أو SVG بخلفية شفافة</p>
                        
                        <div class="pt-2">
                            <label class="block text-xs font-bold text-stone-500 mb-1">أو رابط صورة الشعار:</label>
                            <input type="url" name="site_logo" value="<?= htmlspecialchars($settings['site_logo'] ?? '') ?>" placeholder="https://..." dir="ltr"
                                   class="w-full bg-white border border-stone-200 rounded-xl px-4 py-2.5 text-xs font-mono text-left">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">اسم المتجر (بالعربية)</label>
                    <input type="text" name="site_name_ar" value="<?= htmlspecialchars($settings['site_name_ar'] ?? 'تَـمْرُنـا | أفخر أنواع التمور السعودية') ?>" required
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">اسم المتجر (بالإنجليزية)</label>
                    <input type="text" name="site_name_en" value="<?= htmlspecialchars($settings['site_name_en'] ?? 'Tumurna | Saudi Luxury Dates') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">وصف المتجر / الشعار اللفظي (بالعربية)</label>
                    <input type="text" name="site_tagline_ar" value="<?= htmlspecialchars($settings['site_tagline_ar'] ?? 'أصالة المذاق وجودة الحصاد من بساتين المدينة والقصيم والأحساء') ?>"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">الشعار اللفظي (بالإنجليزية)</label>
                    <input type="text" name="site_tagline_en" value="<?= htmlspecialchars($settings['site_tagline_en'] ?? 'Authentic taste & finest harvest from Saudi palm groves') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>
        </div>

        <!-- 2. Top Announcement Bar -->
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-[#faf3e8] text-[#8c5d25] flex items-center justify-center font-bold">
                    <i data-lucide="megaphone" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">شريط الإعلانات العلوي للمتجر</h3>
                    <p class="text-xs text-stone-500">يظهر في أعلى كل صفحات المتجر للترويج للعروض وخدمات الشحن</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">نص الإعلان (بالعربية)</label>
                    <input type="text" name="announcement_text_ar" value="<?= htmlspecialchars($settings['announcement_text_ar'] ?? '🌴 موسم الحصاد الجديد متاح الآن | 🚚 شحن مبرد مجاني للطلبات فوق 300 ر.س لكافة مناطق المملكة') ?>"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">نص الإعلان (بالإنجليزية)</label>
                    <input type="text" name="announcement_text_en" value="<?= htmlspecialchars($settings['announcement_text_en'] ?? 'Fresh Harvest Season Available | Free Cold Freight on orders over 300 SAR') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>
        </div>

        <!-- 3. Official Contact & Support -->
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-[#f4f7f2] text-[#315b2b] flex items-center justify-center font-bold">
                    <i data-lucide="phone-call" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">بيانات التواصل وخدمة عملاء تَـمْرُنـا</h3>
                    <p class="text-xs text-stone-500">أرقام الواتساب وخدمة كبار العملاء والبريد الرسمي</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">هاتف خدمة العملاء</label>
                    <input type="text" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '+966 50 123 4567') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-mono font-bold text-stone-900 focus:outline-none transition text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رقم الواتساب للطلبات</label>
                    <input type="text" name="contact_whatsapp" value="<?= htmlspecialchars($settings['contact_whatsapp'] ?? '966501234567') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-mono font-bold text-stone-900 focus:outline-none transition text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">البريد الإلكتروني الرسمي</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? 'care@tumurna.com') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">عنوان المقر والفروع (بالعربية)</label>
                    <input type="text" name="contact_address_ar" value="<?= htmlspecialchars($settings['contact_address_ar'] ?? $settings['contact_address'] ?? 'المملكة العربية السعودية - الرياض (طريق الملك فهد) | الفروع: جدة والمدينة المنورة') ?>"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">عنوان المقر والفروع (بالإنجليزية)</label>
                    <input type="text" name="contact_address_en" value="<?= htmlspecialchars($settings['contact_address_en'] ?? 'Saudi Arabia - Riyadh (King Fahd Rd) | Branches: Jeddah & Madinah') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>
        </div>

        <!-- 4. Social Links & Footer -->
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-[#faf3e8] text-[#8c5d25] flex items-center justify-center font-bold">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">وسائل التواصل وحقوق التذييل (Footer)</h3>
                    <p class="text-xs text-stone-500">حسابات التواصل الاجتماعي وتوثيق المتجر (سجل تجاري / ضريبة)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رابط إنستغرام (Instagram)</label>
                    <input type="url" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? 'https://instagram.com/tumurna') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">رابط إكس / تويتر (X)</label>
                    <input type="url" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? 'https://x.com/tumurna') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">حقوق التذييل (بالعربية)</label>
                    <input type="text" name="footer_text_ar" value="<?= htmlspecialchars($settings['footer_text_ar'] ?? 'جميع الحقوق محفوظة © تَـمْرُنـا لتجارة التمور الفاخرة | س.ت: 1010892341 | الرقم الضريبي: 310294857100003') ?>"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                </div>

                <div>
                    <label class="block font-black text-stone-800 text-xs mb-1.5">حقوق التذييل (بالإنجليزية)</label>
                    <input type="text" name="footer_text_en" value="<?= htmlspecialchars($settings['footer_text_en'] ?? 'All rights reserved © Tumurna Luxury Dates Co.') ?>" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition text-left">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-4">
            <button type="submit" class="px-8 py-4 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-2xl font-black text-base shadow-xl shadow-emerald-950/20 transition transform active:scale-98 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>حفظ كافة الإعدادات</span>
            </button>
        </div>

    </form>

</div>

<script>
    const logoFileInput = document.getElementById('logoFileInput');
    const siteLogoPreview = document.getElementById('siteLogoPreview');
    if (logoFileInput && siteLogoPreview) {
        logoFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    siteLogoPreview.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
</script>
