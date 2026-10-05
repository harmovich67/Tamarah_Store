<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="w-full space-y-6 pb-12">
    
    <!-- Top Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#f4f7f2] text-[#315b2b] border border-[#d6e5d2] flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="tag" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight"><?= $locale === 'en' ? 'Dates Categories & Taxonomy' : 'تصنيفات التمور والمنتجات' ?></h1>
                    <p class="text-sm font-semibold text-stone-500 mt-1"><?= $locale === 'en' ? 'Manage luxury date varieties, royal gift boxes, and authentic harvests' : 'إدارة أصناف تمور فاخرة، بوكسات الهدايا، والمشتقات الطبيعية' ?></p>
                </div>
            </div>
        </div>

        <button onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" class="px-6 py-3.5 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-2xl text-sm font-black flex items-center gap-2.5 shadow-lg shadow-emerald-950/20 transition transform active:scale-98">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            <span><?= $locale === 'en' ? 'Add New Category' : 'إضافة تصنيف جديد' ?></span>
        </button>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-300 rounded-3xl text-sm font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span>تم حفظ بيانات التصنيف بنجاح!</span>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-5 bg-red-50 border-2 border-red-300 rounded-3xl text-sm font-bold text-red-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <span>تم حذف التصنيف بنجاح.</span>
        </div>
    <?php endif; ?>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
        <?php foreach ($categories as $cat): ?>
            <div class="bg-white rounded-3xl border border-stone-200/90 shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-[#f8f5ed] border border-[#e8dfc8] p-1.5 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-inner">
                            <?php if (!empty($cat['image'])): ?>
                                <img src="<?= asset($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name_ar']) ?>" class="w-full h-full object-cover rounded-xl">
                            <?php else: ?>
                                <i data-lucide="<?= htmlspecialchars($cat['icon'] ?? 'tag') ?>" class="w-7 h-7 text-[#315b2b]"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-stone-900 text-lg leading-tight truncate"><?= htmlspecialchars($cat['name_ar']) ?></h3>
                            <p class="text-xs text-[#c49a52] font-medium tracking-wide mt-0.5 truncate"><?= htmlspecialchars($cat['name_en'] ?? '') ?></p>
                        </div>
                    </div>

                    <div class="bg-[#faf8f3] p-4 rounded-2xl border border-stone-100 space-y-2.5 text-xs font-semibold text-stone-600">
                        <div class="flex justify-between items-center">
                            <span class="text-stone-400">عدد المنتجات النشطة:</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#eef4eb] text-[#315b2b] font-black text-xs">
                                <?= $cat['products_count'] ?> منتج
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-stone-400">الرابط الدائم (Slug):</span>
                            <span class="font-mono text-stone-700 dir-ltr text-[11px] bg-white px-2 py-0.5 rounded border border-stone-200"><?= htmlspecialchars($cat['slug']) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-stone-400">أيقونة العرض:</span>
                            <span class="font-mono text-stone-500 text-xs"><?= htmlspecialchars($cat['icon'] ?? 'tag') ?></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-stone-100 flex items-center justify-between gap-2">
                    <a href="<?= url('/catalog?category=' . $cat['slug']) ?>" target="_blank" class="px-3 py-2.5 bg-stone-50 hover:bg-stone-100 text-stone-600 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5" title="معاينة في المتجر">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                    </a>
                    <button onclick="openEditCategoryModal(<?= htmlspecialchars(json_encode($cat)) ?>)" class="flex-1 py-2.5 bg-[#f4f7f2] hover:bg-[#315b2b] hover:text-white text-[#315b2b] rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 shadow-sm">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        <span>تعديل الصنف</span>
                    </button>
                    <?php if ($isSuper): ?>
                        <form action="<?= url('/admin/categories/delete') ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا التصنيف؟');">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="p-2.5 bg-red-50 hover:bg-red-600 hover:text-white text-red-600 rounded-xl transition" title="حذف التصنيف">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 border border-stone-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <h3 class="font-black text-stone-900 text-lg flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-[#315b2b]"></i>
                <span>إضافة صنف تمور جديد</span>
            </h3>
            <button onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('/admin/categories/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1.5">اسم التصنيف (بالعربية) *</label>
                <input type="text" name="name_ar" required placeholder="مثال: عجوة فاخرة، سكري القصيم..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-[#315b2b]/20 focus:border-[#315b2b]">
            </div>

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1.5">اسم التصنيف (بالإنجليزية)</label>
                <input type="text" name="name_en" placeholder="e.g. Madinah Ajwa Luxury" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm text-left focus:ring-2 focus:ring-[#315b2b]/20 focus:border-[#315b2b]">
            </div>

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1.5">صورة التصنيف</label>
                <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-stone-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-[#315b2b] file:text-white hover:file:bg-[#24451f] transition cursor-pointer">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1.5">أيقونة Lucide</label>
                    <input type="text" name="icon" value="tag" placeholder="tag, gift, box, star, award..." dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm font-mono text-left">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1.5">الرابط اللطيف (Slug)</label>
                    <input type="text" name="slug" placeholder="ajwa-dates" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm font-mono text-left">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-5 py-3 bg-stone-100 text-stone-700 rounded-xl font-bold text-sm">إلغاء</button>
                <button type="submit" class="px-6 py-3 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-xl font-black text-sm shadow-md transition">حفظ الصنف</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 border border-stone-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <h3 class="font-black text-stone-900 text-lg flex items-center gap-2">
                <i data-lucide="edit-3" class="w-5 h-5 text-[#315b2b]"></i>
                <span>تعديل تصنيف التمور</span>
            </h3>
            <button onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('/admin/categories/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" id="editCatId" name="id">

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1.5">اسم التصنيف (بالعربية) *</label>
                <input type="text" id="editCatNameAr" name="name_ar" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-[#315b2b]/20 focus:border-[#315b2b]">
            </div>

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1.5">اسم التصنيف (بالإنجليزية)</label>
                <input type="text" id="editCatNameEn" name="name_en" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm text-left focus:ring-2 focus:ring-[#315b2b]/20 focus:border-[#315b2b]">
            </div>

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1.5">تحديث صورة التصنيف</label>
                <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-stone-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-[#315b2b] file:text-white hover:file:bg-[#24451f] transition cursor-pointer">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1.5">أيقونة Lucide</label>
                    <input type="text" id="editCatIcon" name="icon" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm font-mono text-left">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1.5">الرابط اللطيف (Slug)</label>
                    <input type="text" id="editCatSlug" name="slug" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-3 text-sm font-mono text-left">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" onclick="document.getElementById('editCategoryModal').classList.add('hidden')" class="px-5 py-3 bg-stone-100 text-stone-700 rounded-xl font-bold text-sm">إلغاء</button>
                <button type="submit" class="px-6 py-3 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-xl font-black text-sm shadow-md transition">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditCategoryModal(cat) {
        document.getElementById('editCatId').value = cat.id;
        document.getElementById('editCatNameAr').value = cat.name_ar;
        document.getElementById('editCatNameEn').value = cat.name_en || '';
        document.getElementById('editCatSlug').value = cat.slug || '';
        document.getElementById('editCatIcon').value = cat.icon || 'tag';

        document.getElementById('editCategoryModal').classList.remove('hidden');
        lucide.createIcons();
    }
</script>
