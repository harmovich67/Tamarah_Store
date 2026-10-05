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
                    <i data-lucide="ticket" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">إدارة كوبونات وقسائم التخفيض</h1>
                    <p class="text-sm font-semibold text-stone-500 mt-1">إنشاء ومتابعة أكواد الخصم، نسب التخفيض، صلاحية الكوبونات، ومرات الاستخدام</p>
                </div>
            </div>
        </div>
        <button type="button" onclick="openAddModal()" class="px-6 py-3.5 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-2xl text-sm font-black flex items-center gap-2.5 transition shadow-lg shadow-emerald-950/20 active:scale-95 cursor-pointer">
            <i data-lucide="plus-circle" class="w-5 h-5 text-[#c49a52]"></i>
            <span>إضافة كود خصم جديد</span>
        </button>
    </div>

    <!-- Alert Notices -->
    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-300 rounded-3xl text-sm font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span>تم حفظ وتحديث بيانات الكوبون بنجاح!</span>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-5 bg-amber-50 border-2 border-amber-300 rounded-3xl text-sm font-bold text-amber-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <span>تم حذف الكوبون بنجاح.</span>
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
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[#8c5d25] border border-amber-200 flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="tags" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">إجمالي الكوبونات</span>
                <span class="text-2xl font-black text-stone-900"><?= number_format($stats['total']) ?></span>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#f4f7f2] text-[#315b2b] border border-[#d6e5d2] flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">كوبونات مفعّلة</span>
                <span class="text-2xl font-black text-[#315b2b]"><?= number_format($stats['active']) ?></span>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="repeat" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">مرات الاستخدام الفعلية</span>
                <span class="text-2xl font-black text-blue-800"><?= number_format($stats['total_uses']) ?></span>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center font-black text-xl shrink-0">
                <i data-lucide="percent" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-stone-400 block">خصم مئوي / ثابت</span>
                <span class="text-lg font-black text-purple-900"><?= $stats['percentage_count'] ?> مئوي | <?= $stats['fixed_count'] ?> ثابت</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="<?= url('/admin/coupons') ?>" method="GET" class="flex-1 w-full flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="بحث برمز الكوبون (مثال: TAMRNA10)..."
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
                <a href="<?= url('/admin/coupons') ?>" class="px-3 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-600 rounded-xl text-xs font-bold transition">إلغاء الفلتر</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-100 text-stone-600 font-black">
                        <th class="py-4 px-6">رمز الكوبون</th>
                        <th class="py-4 px-4">نوع وقيمة الخصم</th>
                        <th class="py-4 px-4">الحد الأدنى للطلب</th>
                        <th class="py-4 px-4">مرات الاستخدام</th>
                        <th class="py-4 px-4">فترة الصلاحية</th>
                        <th class="py-4 px-4 text-center">الحالة</th>
                        <th class="py-4 px-6 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-semibold text-stone-700">
                    <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-stone-400">
                                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 opacity-40"></i>
                                <p class="text-sm font-bold">لا توجد كوبونات مسجلة تطابق بحثك حالياً.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <?php
                            $isExpired = !empty($c['end_date']) && strtotime($c['end_date']) < time();
                            $limitReached = !empty($c['usage_limit']) && $c['times_used'] >= $c['usage_limit'];
                            ?>
                            <tr class="hover:bg-stone-50/70 transition">
                                <!-- Code -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-black text-sm px-3 py-1 rounded-xl bg-[#fbf5e9] text-[#8c5d25] border border-[#e8dfc8]">
                                            <?= htmlspecialchars($c['code']) ?>
                                        </span>
                                        <button type="button" onclick="copyCoupon('<?= htmlspecialchars($c['code']) ?>')" title="نسخ الكود" class="text-stone-400 hover:text-[#315b2b] transition">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Discount -->
                                <td class="py-4 px-4">
                                    <?php if ($c['discount_type'] === 'percentage'): ?>
                                        <span class="inline-flex items-center gap-1 font-black text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                            <i data-lucide="percent" class="w-3.5 h-3.5"></i>
                                            <?= (float)$c['discount_value'] ?>% خصم
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 font-black text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                                            <?= number_format((float)$c['discount_value'], 2) ?> ر.س خصم
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Min Order -->
                                <td class="py-4 px-4">
                                    <?php if ((float)$c['min_order_amount'] > 0): ?>
                                        <span class="font-bold text-stone-900"><?= number_format((float)$c['min_order_amount'], 2) ?> ر.س</span>
                                    <?php else: ?>
                                        <span class="text-stone-400">بدون حد أدنى</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Times used -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        <span class="text-stone-900"><?= (int)$c['times_used'] ?></span>
                                        <span class="text-stone-400 text-[11px]">/ <?= !empty($c['usage_limit']) ? (int)$c['usage_limit'] : '∞' ?></span>
                                    </div>
                                    <?php if ($limitReached): ?>
                                        <span class="text-[10px] font-bold text-rose-600 block">تم استنفاد الحد</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Validity -->
                                <td class="py-4 px-4 text-xs">
                                    <?php if (!empty($c['start_date']) || !empty($c['end_date'])): ?>
                                        <div>
                                            <span class="text-stone-500 text-[11px]">من:</span> <?= $c['start_date'] ?: 'غير محدد' ?>
                                        </div>
                                        <div>
                                            <span class="text-stone-500 text-[11px]">إلى:</span> 
                                            <span class="<?= $isExpired ? 'text-rose-600 font-bold' : '' ?>"><?= $c['end_date'] ?: 'مفتوح' ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-emerald-700 font-bold">صالح دائماً</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4 text-center">
                                    <button type="button" onclick="toggleCouponStatus(<?= $c['id'] ?>, this)" 
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer <?= $c['is_active'] ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-stone-100 text-stone-500 hover:bg-stone-200' ?>">
                                        <span class="w-2 h-2 rounded-full <?= $c['is_active'] ? 'bg-emerald-600' : 'bg-stone-400' ?>"></span>
                                        <span><?= $c['is_active'] ? 'فعّال' : 'متوقف' ?></span>
                                    </button>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick='openEditModal(<?= json_encode($c) ?>)' 
                                                class="w-8 h-8 rounded-xl bg-stone-100 hover:bg-[#f4f7f2] text-stone-600 hover:text-[#315b2b] flex items-center justify-center transition" title="تعديل">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <form action="<?= url('/admin/coupons/delete') ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكوبون نهائياً؟');" class="inline">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
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

<!-- Modal: Add New Coupon -->
<div id="addCouponModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-stone-200 my-8">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#f4f7f2] text-[#315b2b] flex items-center justify-center font-bold">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">إضافة كود خصم جديد</h3>
                    <p class="text-xs text-stone-500">حدد رمز الكوبون، نوع الخصم وشروط الاستخدام</p>
                </div>
            </div>
            <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-xl bg-stone-100 text-stone-400 hover:text-stone-800 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/coupons/store') ?>" method="POST" class="space-y-4 text-xs font-bold text-stone-800">
            <div>
                <label class="block mb-1.5">رمز الكوبون (Coupon Code) <span class="text-rose-500">*</span></label>
                <input type="text" name="code" required placeholder="مثال: TAMRNA20" uppercase dir="ltr"
                       class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 font-mono text-sm font-black text-left uppercase">
                <p class="text-[10px] text-stone-400 mt-1">حروف وأرقام إنجليزية فقط بدون مسافات</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">نوع الخصم</label>
                    <select name="discount_type" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                        <option value="percentage">نسبة مئوية (%)</option>
                        <option value="fixed">مبلغ ثابت (ر.س)</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">قيمة الخصم <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.5" name="discount_value" required placeholder="10"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">الحد الأدنى للطلب (ر.س)</label>
                    <input type="number" step="1" name="min_order_amount" value="0" placeholder="0"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>

                <div>
                    <label class="block mb-1.5">أقصى عدد مرات استخدام</label>
                    <input type="number" name="usage_limit" placeholder="اتركه فارغاً لعدد غير محدود"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">تاريخ البداية (اختياري)</label>
                    <input type="date" name="start_date" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-2.5">
                </div>

                <div>
                    <label class="block mb-1.5">تاريخ الانتهاء (اختياري)</label>
                    <input type="date" name="end_date" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-2.5">
                </div>
            </div>

            <div>
                <label class="block mb-1.5">حالة التفعيل</label>
                <select name="is_active" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                    <option value="1">مفعّل وجاهز للاستخدام</option>
                    <option value="0">معطّل مؤقتاً</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeAddModal()" class="px-5 py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition">إلغاء</button>
                <button type="submit" class="px-7 py-3 rounded-xl bg-[#315b2b] hover:bg-[#24451f] text-white font-black transition shadow-md shadow-emerald-950/20">حفظ الكوبون</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Coupon -->
<div id="editCouponModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-stone-200 my-8">
        <div class="flex items-center justify-between border-b border-stone-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#faf3e8] text-[#8c5d25] flex items-center justify-center font-bold">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-stone-900">تعديل بيانات الكوبون</h3>
                    <p class="text-xs text-stone-500">تعديل نسبة الخصم والحدود وتاريخ الانتهاء</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-xl bg-stone-100 text-stone-400 hover:text-stone-800 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('/admin/coupons/update') ?>" method="POST" class="space-y-4 text-xs font-bold text-stone-800">
            <input type="hidden" name="id" id="edit_id">

            <div>
                <label class="block mb-1.5">رمز الكوبون <span class="text-rose-500">*</span></label>
                <input type="text" name="code" id="edit_code" required uppercase dir="ltr"
                       class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3 font-mono text-sm font-black text-left uppercase">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">نوع الخصم</label>
                    <select name="discount_type" id="edit_discount_type" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                        <option value="percentage">نسبة مئوية (%)</option>
                        <option value="fixed">مبلغ ثابت (ر.س)</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5">قيمة الخصم <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.5" name="discount_value" id="edit_discount_value" required
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">الحد الأدنى للطلب (ر.س)</label>
                    <input type="number" step="1" name="min_order_amount" id="edit_min_order_amount"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>

                <div>
                    <label class="block mb-1.5">أقصى عدد مرات استخدام</label>
                    <input type="number" name="usage_limit" id="edit_usage_limit" placeholder="غير محدود"
                           class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5">تاريخ البداية</label>
                    <input type="date" name="start_date" id="edit_start_date" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-2.5">
                </div>

                <div>
                    <label class="block mb-1.5">تاريخ الانتهاء</label>
                    <input type="date" name="end_date" id="edit_end_date" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-2.5">
                </div>
            </div>

            <div>
                <label class="block mb-1.5">حالة التفعيل</label>
                <select name="is_active" id="edit_is_active" class="w-full bg-stone-50 border border-stone-200 focus:border-[#315b2b] rounded-xl px-4 py-3">
                    <option value="1">مفعّل وجاهز للاستخدام</option>
                    <option value="0">معطّل مؤقتاً</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 transition">إلغاء</button>
                <button type="submit" class="px-7 py-3 rounded-xl bg-[#8c5d25] hover:bg-[#6f431b] text-white font-black transition shadow-md shadow-amber-950/20">تحديث الكوبون</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addCouponModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addCouponModal').classList.add('hidden');
    }

    function openEditModal(c) {
        document.getElementById('edit_id').value = c.id;
        document.getElementById('edit_code').value = c.code;
        document.getElementById('edit_discount_type').value = c.discount_type;
        document.getElementById('edit_discount_value').value = c.discount_value;
        document.getElementById('edit_min_order_amount').value = c.min_order_amount || 0;
        document.getElementById('edit_usage_limit').value = c.usage_limit || '';
        document.getElementById('edit_start_date').value = c.start_date || '';
        document.getElementById('edit_end_date').value = c.end_date || '';
        document.getElementById('edit_is_active').value = c.is_active;
        document.getElementById('editCouponModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editCouponModal').classList.add('hidden');
    }

    function copyCoupon(code) {
        navigator.clipboard.writeText(code).then(() => {
            alert('تم نسخ كود الكوبون: ' + code);
        });
    }

    function toggleCouponStatus(id, btn) {
        const formData = new FormData();
        formData.append('id', id);

        fetch(window.appUrl('/admin/coupons/toggle'), {
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
