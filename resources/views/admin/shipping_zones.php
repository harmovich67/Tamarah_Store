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
                <div class="w-12 h-12 rounded-2xl bg-[#faf3e8] text-[#8c5d25] border border-[#e8dfc8] flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">مناطق ومدن الشحن بالمملكة العربية السعودية</h1>
                    <p class="text-sm font-semibold text-stone-500 mt-1">تحديد مدن التوصيل، رسوم الشحن لكل منطقة، مدة التوصيل التقديرية والشحن المبرد</p>
                </div>
            </div>
        </div>
        <button type="button" onclick="openAddZoneModal()" class="px-6 py-3.5 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-2xl text-sm font-black flex items-center gap-2.5 transition shadow-lg shadow-emerald-950/20 active:scale-95 cursor-pointer">
            <i data-lucide="plus-circle" class="w-5 h-5 text-[#c49a52]"></i>
            <span>إضافة مدينة سعودية جديدة</span>
        </button>
    </div>

    <!-- Alert Notices -->
    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-300 rounded-3xl text-sm font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span>تم حفظ وتحديث بيانات مناطق الشحن بنجاح!</span>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-5 bg-amber-50 border-2 border-amber-300 rounded-3xl text-sm font-bold text-amber-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <span>تم حذف المدينة من قائمة التغطية بنجاح.</span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-5 bg-rose-50 border-2 border-rose-300 rounded-3xl text-sm font-bold text-rose-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- KPI Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#faf3e8] text-[#8c5d25] border border-amber-200 flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="map" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">إجمالي مدن التغطية</span>
                <span class="text-2xl font-black text-stone-900"><?= number_format($stats['total']) ?></span>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#f4f7f2] text-[#315b2b] border border-[#d6e5d2] flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="truck" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">مدن التوصيل النشطة</span>
                <span class="text-2xl font-black text-[#315b2b]"><?= number_format($stats['active']) ?></span>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-700 border border-cyan-200 flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="snowflake" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">توصيل مبرد مدعوم</span>
                <span class="text-2xl font-black text-cyan-800"><?= number_format($stats['cold_count']) ?> مدينة</span>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="banknote" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">متوسط سعر الشحن</span>
                <span class="text-xl font-black text-stone-900"><?= number_format($stats['avg_fee'], 2) ?> ر.س</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="<?= url('/admin/shipping-zones') ?>" method="GET" class="flex-1 w-full flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="بحث بالمدينة (الرياض، جدة، الدمام، Riyadh)..."
                       class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 text-xs font-bold text-stone-900 focus:outline-none focus:border-[#315b2b]">
                <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute <?= $isRtl ? 'left-3' : 'right-3' ?> top-3"></i>
            </div>
            <select name="status" onchange="this.form.submit()" class="bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 text-xs font-bold text-stone-900 focus:outline-none">
                <option value="all" <?= ($status ?? '') === 'all' ? 'selected' : '' ?>>كافة الحالات</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>المفعّلة فقط</option>
                <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>المعطّلة</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-[#8c5d25] hover:bg-[#6f431b] text-white rounded-xl text-xs font-bold transition">بحث</button>
            <?php if (!empty($search) || ($status ?? 'all') !== 'all'): ?>
                <a href="<?= url('/admin/shipping-zones') ?>" class="px-3 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-600 rounded-xl text-xs font-bold transition">إلغاء الفلتر</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Zones Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-100 text-stone-600 font-black">
                        <th class="py-4 px-6">المدينة (بالعربية / English)</th>
                        <th class="py-4 px-4">تكلفة الشحن والتوصيل</th>
                        <th class="py-4 px-4">المدة التقديرية للتوصيل</th>
                        <th class="py-4 px-4 text-center">نوع الشحن</th>
                        <th class="py-4 px-4 text-center">الحالة</th>
                        <th class="py-4 px-6 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-semibold text-stone-700">
                    <?php if (empty($zones)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-stone-400">
                                <i data-lucide="map-pin-off" class="w-12 h-12 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm font-bold">لا توجد مدن شحن مسجلة تطابق بحثك حالياً.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($zones as $z): ?>
                            <tr class="hover:bg-stone-50/70 transition">
                                <!-- City Name -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-stone-100 text-stone-600 flex items-center justify-center font-bold">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-[#8c5d25]"></i>
                                        </div>
                                        <div>
                                            <span class="font-black text-sm text-stone-900 block"><?= htmlspecialchars($z['city_name_ar']) ?></span>
                                            <span class="text-[11px] text-stone-400 font-normal block"><?= htmlspecialchars($z['city_name_en']) ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Fee -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center gap-1 font-black text-sm text-[#315b2b] bg-[#f4f7f2] px-3 py-1 rounded-xl border border-[#d6e5d2]">
                                        <?= number_format((float)$z['shipping_fee'], 2) ?> ر.س
                                    </span>
                                </td>

                                <!-- Delivery Estimate -->
                                <td class="py-4 px-4">
                                    <span class="text-stone-700 font-bold flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-stone-400"></i>
                                        <?= htmlspecialchars($z['estimated_delivery'] ?: 'توصيل مبرد خلال 24-48 ساعة') ?>
                                    </span>
                                </td>

                                <!-- Cold shipping -->
                                <td class="py-4 px-4 text-center">
                                    <?php if ($z['is_cold_shipping']): ?>
                                        <span class="inline-flex items-center gap-1 font-black text-[11px] text-cyan-800 bg-cyan-50 px-2.5 py-1 rounded-full border border-cyan-200">
                                            <i data-lucide="snowflake" class="w-3 h-3"></i>
                                            شحن مبرد ❄️
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 font-bold text-[11px] text-stone-500 bg-stone-100 px-2.5 py-1 rounded-full">
                                            شحن قياسي
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4 text-center">
                                    <button type="button" onclick="toggleZoneStatus(<?= $z['id'] ?>, this)" 
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer <?= $z['is_active'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-stone-100 text-stone-500 hover:bg-stone-200' ?>">
                                        <span class="w-2 h-2 rounded-full <?= $z['is_active'] ? 'bg-emerald-600' : 'bg-stone-400' ?>"></span>
                                        <span><?= $z['is_active'] ? 'متاح للتوصيل' : 'معطّل' ?></span>
                                    </button>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick='openEditZoneModal(<?= json_encode($z) ?>)' 
                                                class="w-8 h-8 rounded-xl bg-stone-100 hover:bg-[#f4f7f2] text-stone-600 hover:text-[#315b2b] flex items-center justify-center transition" title="تعديل">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <form action="<?= url('/admin/shipping-zones/delete') ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه المدينة من مناطق الشحن؟');" class="inline">
                                            <input type="hidden" name="id" value="<?= $z['id'] ?>">
                                            <button type="submit" class="w-8 h-8 rounded-xl bg-stone-100 hover:bg-rose-50 text-stone-600 hover:text-rose-600 flex items-center justify-center transition" title="حذف">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: Add New Saudi Shipping Zone -->
<div id="addZoneModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-stone-200 my-8">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#faf3e8] text-[#8c5d25] flex items-center justify-center font-bold">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">إضافة مدينة سعودية جديدة للشحن</h3>
                    <p class="text-xs text-stone-500">حدد اسم المدينة، رسوم الشحن ومدة التسليم</p>
                </div>
            </div>
            <button type="button" onclick="closeAddZoneModal()" class="w-8 h-8 rounded-xl bg-stone-100 text-stone-400 hover:text-stone-800 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/shipping-zones/store') ?>" method="POST" class="space-y-4 text-xs font-bold text-stone-800">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">اسم المدينة (بالعربية) <span class="text-rose-500">*</span></label>
                    <input type="text" name="city_name_ar" required placeholder="مثال: الباحة"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-xs">
                </div>

                <div>
                    <label class="block mb-1.5">اسم المدينة (بالإنجليزية)</label>
                    <input type="text" name="city_name_en" placeholder="Al Baha" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-xs text-left">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">رسوم الشحن (ر.س) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.5" name="shipping_fee" value="25.00" required
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>

                <div>
                    <label class="block mb-1.5">تفعيل الشحن المبرد</label>
                    <select name="is_cold_shipping" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                        <option value="1">نعم (شحن مبرد ❄️)</option>
                        <option value="0">لا (شحن عادي)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block mb-1.5">المدة التقديرية للتوصيل</label>
                <input type="text" name="estimated_delivery" value="توصيل مبرد خلال 24-48 ساعة"
                       class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
            </div>

            <div>
                <label class="block mb-1.5">حالة التفعيل</label>
                <select name="is_active" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                    <option value="1">مفعل (يظهر في خيارات الشحن للعملاء)</option>
                    <option value="0">معطل مؤقتاً</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeAddZoneModal()" class="px-5 py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition">إلغاء</button>
                <button type="submit" class="px-7 py-3 rounded-xl bg-[#315b2b] hover:bg-[#24451f] text-white font-black transition shadow-md shadow-emerald-950/20">إضافة المدينة</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Saudi Shipping Zone -->
<div id="editZoneModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-stone-200 my-8">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#f4f7f2] text-[#315b2b] flex items-center justify-center font-bold">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">تعديل بيانات مدينة الشحن</h3>
                    <p class="text-xs text-stone-500">تحديث تكلفة الشحن ومواعيد التوصيل</p>
                </div>
            </div>
            <button type="button" onclick="closeEditZoneModal()" class="w-8 h-8 rounded-xl bg-stone-100 text-stone-400 hover:text-stone-800 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/shipping-zones/update') ?>" method="POST" class="space-y-4 text-xs font-bold text-stone-800">
            <input type="hidden" name="id" id="edit_zone_id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">اسم المدينة (بالعربية) <span class="text-rose-500">*</span></label>
                    <input type="text" name="city_name_ar" id="edit_zone_name_ar" required
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-xs">
                </div>

                <div>
                    <label class="block mb-1.5">اسم المدينة (بالإنجليزية)</label>
                    <input type="text" name="city_name_en" id="edit_zone_name_en" dir="ltr"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 text-xs text-left">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">رسوم الشحن (ر.س) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.5" name="shipping_fee" id="edit_zone_shipping_fee" required
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>

                <div>
                    <label class="block mb-1.5">الشحن المبرد</label>
                    <select name="is_cold_shipping" id="edit_zone_is_cold" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                        <option value="1">نعم (شحن مبرد ❄️)</option>
                        <option value="0">لا (شحن عادي)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block mb-1.5">المدة التقديرية للتوصيل</label>
                <input type="text" name="estimated_delivery" id="edit_zone_delivery"
                       class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
            </div>

            <div>
                <label class="block mb-1.5">حالة التفعيل</label>
                <select name="is_active" id="edit_zone_is_active" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                    <option value="1">مفعل (يظهر في خيارات الشحن)</option>
                    <option value="0">معطل</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeEditZoneModal()" class="px-5 py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition">إلغاء</button>
                <button type="submit" class="px-7 py-3 rounded-xl bg-[#8c5d25] hover:bg-[#6f431b] text-white font-black transition shadow-md shadow-amber-950/20">تحديث المدينة</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddZoneModal() {
        document.getElementById('addZoneModal').classList.remove('hidden');
    }
    function closeAddZoneModal() {
        document.getElementById('addZoneModal').classList.add('hidden');
    }

    function openEditZoneModal(z) {
        document.getElementById('edit_zone_id').value = z.id;
        document.getElementById('edit_zone_name_ar').value = z.city_name_ar;
        document.getElementById('edit_zone_name_en').value = z.city_name_en;
        document.getElementById('edit_zone_shipping_fee').value = z.shipping_fee;
        document.getElementById('edit_zone_is_cold').value = z.is_cold_shipping;
        document.getElementById('edit_zone_delivery').value = z.estimated_delivery;
        document.getElementById('edit_zone_is_active').value = z.is_active;
        document.getElementById('editZoneModal').classList.remove('hidden');
    }
    function closeEditZoneModal() {
        document.getElementById('editZoneModal').classList.add('hidden');
    }

    function toggleZoneStatus(id, btn) {
        const formData = new FormData();
        formData.append('id', id);

        fetch(window.appUrl('/admin/shipping-zones/toggle'), {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message || 'حدث خطأ');
            }
        });
    }
</script>
