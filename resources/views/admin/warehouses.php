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
                <div class="w-12 h-12 rounded-2xl bg-[#315b2b]/10 text-[#315b2b] border border-[#315b2b]/20 flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="boxes" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-[#2c241e] tracking-tight"><?= $locale === 'ar' ? 'مستودعات التمور والمخازن المبردة' : 'Date Cold Storages & Fulfillment Hubs' ?></h1>
                    <p class="text-sm font-semibold text-stone-500 mt-1"><?= $locale === 'ar' ? 'إدارة مراكز التبريد وتخزين محصول التمور في مختلف مناطق المملكة' : 'Manage cold storage facilities and date inventory centers across KSA' ?></p>
                </div>
            </div>
        </div>

        <button onclick="document.getElementById('addWarehouseModal').classList.remove('hidden')" class="px-6 py-3.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-2xl text-sm font-black flex items-center gap-2.5 shadow-lg shadow-[#315b2b]/20 transition transform active:scale-98">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            <span><?= $locale === 'ar' ? 'إضافة مستودع مبرد جديد' : 'Add New Cold Storage' ?></span>
        </button>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-300 rounded-3xl text-sm font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span><?= $locale === 'ar' ? 'تم حفظ بيانات المستودع بنجاح' : 'Warehouse saved successfully' ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-5 bg-red-50 border-2 border-red-300 rounded-3xl text-sm font-bold text-red-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <span><?= $locale === 'ar' ? 'تم حذف المستودع بنجاح' : 'Warehouse deleted successfully' ?></span>
        </div>
    <?php endif; ?>

    <!-- Warehouses Grid / Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
        <?php foreach ($warehouses as $w): ?>
            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 space-y-4 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-[#315b2b]/10 p-2 border border-[#315b2b]/20 flex items-center justify-center flex-shrink-0 text-[#315b2b]">
                            <i data-lucide="snowflake" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-stone-900 text-base"><?= htmlspecialchars($locale === 'en' ? (($w['name_en'] ?? '') ?: ($w['name_ar'] ?? '')) : ($w['name_ar'] ?? '')) ?></h3>
                            <span class="text-xs font-mono font-bold text-[#8c5d25] bg-[#8c5d25]/10 px-2 py-0.5 rounded-md"><?= htmlspecialchars($w['code'] ?? 'WH-DEFAULT') ?></span>
                        </div>
                    </div>
                    <span class="text-xs font-black px-3 py-1 rounded-full <?= $w['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-700' ?>">
                        <?= $w['status'] === 'active' ? ($locale === 'ar' ? 'نشط / جاهز للشحن' : 'Active') : ($locale === 'ar' ? 'معطل' : 'Inactive') ?>
                    </span>
                </div>

                <div class="bg-[#f8f5ed] p-4 rounded-2xl border border-stone-200/60 space-y-2 text-xs font-semibold text-stone-600">
                    <div class="flex justify-between items-center">
                        <span class="text-stone-400"><?= $locale === 'ar' ? 'نوع الحفظ:' : 'Storage Type:' ?></span>
                        <b class="text-[#315b2b] flex items-center gap-1"><i data-lucide="thermometer-snowflake" class="w-3.5 h-3.5"></i> <?= $locale === 'ar' ? 'تبريد متحكم به (-18° إلى 4°)' : 'Controlled Cold Storage' ?></b>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-stone-400"><?= $locale === 'ar' ? 'الموقع الجغرافي:' : 'Location:' ?></span>
                        <b class="text-stone-800"><?= htmlspecialchars($w['location'] ?? ($locale === 'ar' ? 'المملكة العربية السعودية' : 'KSA')) ?></b>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-stone-400"><?= $locale === 'ar' ? 'هاتف التواصل:' : 'Contact Phone:' ?></span>
                        <b class="text-stone-800 font-mono" dir="ltr"><?= htmlspecialchars($w['phone'] ?? '-') ?></b>
                    </div>
                </div>

                <div class="pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
                    <button onclick="openEditWarehouseModal(<?= htmlspecialchars(json_encode($w)) ?>)" class="flex-1 py-2.5 bg-[#315b2b]/10 hover:bg-[#315b2b] hover:text-white text-[#315b2b] rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        <span><?= $locale === 'ar' ? 'تعديل المستودع' : 'Edit' ?></span>
                    </button>
                    <?php if ($isSuper): ?>
                        <form action="<?= url('/admin/warehouses/delete') ?>" method="POST" onsubmit="return confirm('<?= $locale === 'ar' ? 'هل أنت متأكد من حذف هذا المستودع نهائياً؟' : 'Confirm deleting this warehouse?' ?>');">
                            <input type="hidden" name="id" value="<?= $w['id'] ?>">
                            <button type="submit" class="p-2.5 bg-red-50 hover:bg-red-600 hover:text-white text-red-600 rounded-xl transition" title="<?= $locale === 'ar' ? 'حذف' : 'Delete' ?>">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Add Warehouse Modal -->
<div id="addWarehouseModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl space-y-6 border border-stone-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <h3 class="font-black text-stone-900 text-lg"><?= $locale === 'ar' ? 'إضافة مستودع تمور مبرد جديد' : 'Add New Date Cold Storage' ?></h3>
            <button onclick="document.getElementById('addWarehouseModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('/admin/warehouses/store') ?>" method="POST" class="space-y-4">
            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'اسم المستودع (بالعربية) *' : 'Warehouse Name (Arabic) *' ?></label>
                <input type="text" name="name_ar" required placeholder="مستودع القصيم المركزي للتمور" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm font-bold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'اسم المستودع (بالانجليزية)' : 'Warehouse Name (English)' ?></label>
                    <input type="text" name="name_en" placeholder="Qassim Date Hub" dir="ltr" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'رمز المستودع (الكود)' : 'Warehouse Code' ?></label>
                    <input type="text" name="code" placeholder="WH-QAS-01" dir="ltr" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm font-mono font-bold text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
            </div>

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'الموقع الجغرافي والمدينة' : 'Location & City' ?></label>
                <input type="text" name="location" placeholder="القصيم - بريدة" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'رقم الهاتف للتنسيق' : 'Contact Phone' ?></label>
                    <input type="text" name="phone" placeholder="0114000000" dir="ltr" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'الحالة التشغيلية' : 'Status' ?></label>
                    <select name="status" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm font-bold">
                        <option value="active"><?= $locale === 'ar' ? 'نشط / يستقبل ويشحن الطلبات' : 'Active' ?></option>
                        <option value="inactive"><?= $locale === 'ar' ? 'معطل مؤقتاً' : 'Inactive' ?></option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="document.getElementById('addWarehouseModal').classList.add('hidden')" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl text-xs font-bold transition">
                    <?= $locale === 'ar' ? 'إلغاء' : 'Cancel' ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black transition shadow-md shadow-[#315b2b]/20">
                    <?= $locale === 'ar' ? 'حفظ المستودع' : 'Save Warehouse' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Warehouse Modal -->
<div id="editWarehouseModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl space-y-6 border border-stone-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <h3 class="font-black text-stone-900 text-lg"><?= $locale === 'ar' ? 'تعديل بيانات المستودع المبرد' : 'Edit Cold Storage' ?></h3>
            <button onclick="document.getElementById('editWarehouseModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('/admin/warehouses/update') ?>" method="POST" class="space-y-4">
            <input type="hidden" id="editWarehouseId" name="id">

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'اسم المستودع (بالعربية) *' : 'Warehouse Name (Arabic) *' ?></label>
                <input type="text" id="editWarehouseNameAr" name="name_ar" required class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm font-bold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'اسم المستودع (بالانجليزية)' : 'Warehouse Name (English)' ?></label>
                    <input type="text" id="editWarehouseNameEn" name="name_en" dir="ltr" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'رمز المستودع (الكود)' : 'Warehouse Code' ?></label>
                    <input type="text" id="editWarehouseCode" name="code" dir="ltr" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm font-mono font-bold text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
            </div>

            <div>
                <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'الموقع الجغرافي والمدينة' : 'Location & City' ?></label>
                <input type="text" id="editWarehouseLocation" name="location" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'رقم الهاتف للتنسيق' : 'Contact Phone' ?></label>
                    <input type="text" id="editWarehousePhone" name="phone" dir="ltr" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 text-xs mb-1"><?= $locale === 'ar' ? 'الحالة التشغيلية' : 'Status' ?></label>
                    <select id="editWarehouseStatus" name="status" class="w-full bg-stone-50 border border-stone-300 rounded-xl px-4 py-3 text-sm font-bold">
                        <option value="active"><?= $locale === 'ar' ? 'نشط / يستقبل ويشحن الطلبات' : 'Active' ?></option>
                        <option value="inactive"><?= $locale === 'ar' ? 'معطل مؤقتاً' : 'Inactive' ?></option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="document.getElementById('editWarehouseModal').classList.add('hidden')" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl text-xs font-bold transition">
                    <?= $locale === 'ar' ? 'إلغاء' : 'Cancel' ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black transition shadow-md shadow-[#315b2b]/20">
                    <?= $locale === 'ar' ? 'تحديث المستودع' : 'Update Warehouse' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditWarehouseModal(w) {
    document.getElementById('editWarehouseId').value = w.id || '';
    document.getElementById('editWarehouseNameAr').value = w.name_ar || '';
    document.getElementById('editWarehouseNameEn').value = w.name_en || '';
    document.getElementById('editWarehouseCode').value = w.code || '';
    document.getElementById('editWarehouseLocation').value = w.location || '';
    document.getElementById('editWarehousePhone').value = w.phone || '';
    document.getElementById('editWarehouseStatus').value = w.status || 'active';
    document.getElementById('editWarehouseModal').classList.remove('hidden');
}
</script>
