<?php
use App\Core\I18n;
use App\Core\CustomFields;

$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
$categories = $categories ?? [];
?>

<div class="w-full space-y-6 pb-12">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 font-serif">إضافة صنف تمور ملكي جديد</h1>
            <p class="text-xs sm:text-sm font-semibold text-stone-500 mt-1">إدخال تفاصيل الصنف، تصنيف التمور، الأسعار بالريال السعودي، وخيارات التعبئة والحصاد</p>
        </div>
        <a href="<?= url('/admin/products') ?>" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 transition">
            &larr; العودة لقائمة التمور
        </a>
    </div>

    <form action="<?= url('/admin/products/store') ?>" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl border border-stone-200 shadow-xs p-6 sm:p-8 space-y-8 text-sm">
        <?= csrf_field() ?>

        <!-- Basic Category & Classification -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-stone-900 border-b border-stone-100 pb-2 flex items-center gap-2">
                <i data-lucide="tags" class="w-4 h-4 text-[#8c5d25]"></i>
                <span>تصنيف التمور</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">قسم التمور *</label>
                    <select name="category_id" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc_html($c['name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">الوزن / نوع العبوة الافتراضية</label>
                    <input type="text" name="weight" value="1 كجم" placeholder="مثال: 1 كجم / عبوة 500 جرام / كرتون 3 كجم" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>
            </div>
        </div>

        <!-- Names & Descriptions -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-stone-900 border-b border-stone-100 pb-2 flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#8c5d25]"></i>
                <span>بيانات الصنف والوصف</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">اسم صنف التمر (بالعربية) *</label>
                    <input type="text" name="name_ar" required placeholder="مثال: عجوة المدينة المنورة العالية الملكية" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">اسم الصنف (بالإنجليزية)</label>
                    <input type="text" name="name_en" placeholder="e.g. Royal Madinah Al-Aliyah Ajwa" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">الوصف التفصيلي (بالعربية)</label>
                    <textarea name="description_ar" rows="3" placeholder="وصف الحبة، مصدر المزرعة، ومواصفات الطراوة..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">الوصف التفصيلي (بالإنجليزية)</label>
                    <textarea name="description_en" rows="3" placeholder="English detailed description..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]"></textarea>
                </div>
            </div>
        </div>

        <!-- Pricing, Inventory, & Flags -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-stone-900 border-b border-stone-100 pb-2 flex items-center gap-2">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-[#8c5d25]"></i>
                <span>الأسعار والمخزون والشارات</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">السعر الأساسي (ر.س) *</label>
                    <input type="number" step="0.5" name="price" required value="120" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 font-black text-[#315b2b] focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">سعر العرض / الخصم (ر.س)</label>
                    <input type="number" step="0.5" name="sale_price" placeholder="اختياري" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">الكمية بالمخزون *</label>
                    <input type="number" name="stock_quantity" required value="50" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">رمز التتبع (SKU)</label>
                    <input type="text" name="sku" placeholder="TUM-DATE-01" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 font-mono text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>
            </div>

            <!-- Preorder, Fragile, Badge -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-2">
                    <label class="flex items-center gap-2 font-bold text-stone-900 cursor-pointer">
                        <input type="checkbox" name="is_preorder" value="1" onchange="document.getElementById('preorderDateBox').classList.toggle('hidden', !this.checked)" class="text-[#315b2b] rounded">
                        <span>صنف متاح للحجز المسبق للحصاد 🌴</span>
                    </label>
                    <div id="preorderDateBox" class="hidden pt-1">
                        <label class="block text-[11px] text-stone-500 mb-1">موعد بدء الشحن التقديري</label>
                        <input type="text" name="preorder_date" placeholder="مثال: 15 سبتمبر 2026" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs">
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-2">
                    <label class="flex items-center gap-2 font-bold text-stone-900 cursor-pointer">
                        <input type="checkbox" name="is_fragile" value="1" class="text-[#8c5d25] rounded">
                        <span>منتج حساس أو زجاجي (قابل للكسر)</span>
                    </label>
                    <p class="text-[10px] text-stone-500">يظهر تنبيه للمشتري بضرورة الشحن المبرد أو الاستلام من الفرع.</p>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">الشارة المميزة (Badge)</label>
                    <input type="text" name="badge" placeholder="مثال: الأكثر طلباً / نخب أول" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-stone-900 focus:outline-none focus:ring-2 focus:ring-[#8c5d25]">
                </div>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-stone-900 border-b border-stone-100 pb-2 flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4 text-[#8c5d25]"></i>
                <span>صورة الصنف</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">رفع صورة من الجهاز</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5">أو مسار الصورة الافتراضية</label>
                    <input type="text" name="featured_image" value="assets/images/ajwa_luxury_box_1787053900509.jpg" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 text-xs text-stone-900">
                </div>
            </div>
        </div>

        <!-- Packaging & Weight Variants Repeater -->
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                <h3 class="text-sm font-black text-stone-900 flex items-center gap-2">
                    <i data-lucide="boxes" class="w-4 h-4 text-[#8c5d25]"></i>
                    <span>خيارات الأوزان والعبوات المتوفرة (Variants)</span>
                </h3>
                <button type="button" onclick="addVariantRow()" class="text-xs font-bold text-[#568d43] hover:underline flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>+ إضافة حجم أو عبوة أخرى</span>
                </button>
            </div>

            <div id="variantsContainer" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 p-4 bg-stone-50 rounded-2xl border border-stone-200 variant-row">
                    <div>
                        <label class="block text-[11px] font-bold text-stone-600 mb-1">اسم العبوة / الوزن</label>
                        <input type="text" name="sizes[]" value="صندوق 1 كجم فاخر" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-stone-600 mb-1">السعر (ر.س)</label>
                        <input type="number" step="0.5" name="prices[]" value="120" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs font-bold text-[#315b2b]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-stone-600 mb-1">الكمية</label>
                        <input type="number" name="stocks[]" value="50" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs">
                    </div>
                    <div class="flex items-end">
                        <span class="text-[10px] text-stone-400">العبوة الرئيسية</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
            <a href="<?= url('/admin/products') ?>" class="px-6 py-3 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold transition">إلغاء</a>
            <button type="submit" class="px-8 py-3 rounded-2xl bg-[#568d43] hover:bg-[#315b2b] text-white text-xs font-black shadow-md transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-[#c49a52]"></i>
                <span>حفظ ونشر صنف التمور</span>
            </button>
        </div>

    </form>

</div>

<script>
    function addVariantRow() {
        const container = document.getElementById('variantsContainer');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 sm:grid-cols-4 gap-3 p-4 bg-stone-50 rounded-2xl border border-stone-200 variant-row';
        div.innerHTML = `
            <div>
                <label class="block text-[11px] font-bold text-stone-600 mb-1">اسم العبوة / الوزن</label>
                <input type="text" name="sizes[]" placeholder="مثال: كرتون 3 كجم" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-stone-600 mb-1">السعر (ر.س)</label>
                <input type="number" step="0.5" name="prices[]" placeholder="320" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs font-bold text-[#315b2b]">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-stone-600 mb-1">الكمية</label>
                <input type="number" name="stocks[]" placeholder="20" class="w-full bg-white border border-stone-200 rounded-lg p-2 text-xs">
            </div>
            <div class="flex items-end">
                <button type="button" onclick="this.closest('.variant-row').remove()" class="p-2 text-rose-500 hover:text-rose-700 text-xs font-bold">حذف الخيار</button>
            </div>
        `;
        container.appendChild(div);
    }
</script>
