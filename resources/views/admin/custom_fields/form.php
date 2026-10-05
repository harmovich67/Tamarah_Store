<?php
use App\Core\I18n;
$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
$isEdit = $isEdit ?? false;
$group = $group ?? null;
$fields = $fields ?? [];
?>

<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/custom-fields') ?>" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="<?= __('back_to_field_groups') ?>">
                <i data-lucide="<?= $isRtl ? 'arrow-right' : 'arrow-left' ?>" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    <?= $isEdit ? __('edit_field_group') : __('add_field_group') ?>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= $locale === 'ar' ? 'تحديد قواعد العرض، وإنشاء الحقول والمكررات التفاعلية المخصصة (Native ACF Pro)' : 'Configure location rules and construct interactive custom fields' ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form method="POST" id="acfGroupForm" action="<?= $isEdit ? url('/admin/custom-fields/' . $group['id'] . '/update') : url('/admin/custom-fields/store') ?>" class="space-y-8">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $group['id'] ?>">
        <?php endif; ?>

        <!-- Section 1: Group Settings & Location Rules -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="settings" class="w-5 h-5 text-indigo-600"></i>
                <span><?= $locale === 'ar' ? 'بيانات وقواعد استهداف المجموعة' : 'Group Settings & Target Rules' ?></span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('group_title_ar') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title_ar" id="group_title_ar" value="<?= htmlspecialchars((string)($group['title_ar'] ?? '')) ?>" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 transition" placeholder="مثال: بيانات البانر والمميزات">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('group_title_en') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title_en" id="group_title_en" value="<?= htmlspecialchars((string)($group['title_en'] ?? '')) ?>" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 transition" placeholder="e.g. Page Banner & Highlights">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('group_key') ?>
                    </label>
                    <input type="text" name="key_name" id="group_key" value="<?= htmlspecialchars((string)($group['key_name'] ?? '')) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500" placeholder="group_page_banner">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('target_type') ?> <span class="text-red-500">*</span>
                    </label>
                    <select name="target_type" id="targetTypeSelect" onchange="loadTargetFilterOptions()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                        <option value="page" <?= (($group['target_type'] ?? '') === 'page') ? 'selected' : '' ?>><?= __('target_page') ?></option>
                        <option value="post" <?= (($group['target_type'] ?? '') === 'post') ? 'selected' : '' ?>><?= __('target_post') ?></option>
                        <option value="product" <?= (($group['target_type'] ?? '') === 'product') ? 'selected' : '' ?>><?= __('target_product') ?></option>
                        <option value="category" <?= (($group['target_type'] ?? '') === 'category') ? 'selected' : '' ?>><?= $locale === 'ar' ? 'صنف تمور (Category)' : 'Date Category' ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('target_filter') ?>
                    </label>
                    <select name="target_filter" id="targetFilterSelect" data-current-value="<?= htmlspecialchars((string)($group['target_filter'] ?? 'all')) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                        <option value="all"><?= $locale === 'ar' ? 'جاري التحميل...' : 'Loading...' ?></option>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1.5"><?= $locale === 'ar' ? 'اختر عنصراً محدداً لعرض هذه الحقول عليه فقط، أو اترك "الكل" لتظهر في كل الصفحات/المنشورات من هذا النوع.' : 'Pick a specific item to show these fields only there, or leave "All" to show them everywhere of this type.' ?></p>
                </div>
            </div>
        </div>

        <!-- Section 2: Visual Fields Builder -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-black text-slate-900"><?= __('fields_builder') ?></h2>
                        <p class="text-xs text-slate-400"><?= $locale === 'ar' ? 'أضف الحقول المخصصة والمكررات (Repeater) بواجهة سهلة بدون كتابة أي كود' : 'Add custom fields and visual repeaters without writing JSON code' ?></p>
                    </div>
                </div>

                <button type="button" onclick="addNewFieldCard()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span><?= __('add_field') ?></span>
                </button>
            </div>

            <!-- Fields Container -->
            <div id="fields-container" class="space-y-6">
                <?php if (empty($fields)): ?>
                    <div id="no-fields-notice" class="p-8 text-center text-slate-400 border-2 border-dashed border-slate-200 rounded-2xl">
                        <i data-lucide="layers" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs font-bold"><?= $locale === 'ar' ? 'لا توجد حقول حتى الآن. اضغط على "إضافة حقل جديد" للبدء.' : 'No fields added yet. Click "Add New Field" to start.' ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($fields as $idx => $f): 
                        $subFields = [];
                        if ($f['type'] === 'repeater' && !empty($f['options'])) {
                            $decoded = is_array($f['options']) ? $f['options'] : json_decode($f['options'], true);
                            if (is_array($decoded)) $subFields = $decoded;
                        }
                    ?>
                        <div class="field-item bg-slate-50/90 border border-slate-200 rounded-3xl p-5 sm:p-6 space-y-4 relative shadow-sm" data-field-index="<?= $idx ?>">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black field-badge-num">
                                        #<?= $idx + 1 ?>
                                    </span>
                                    <span class="text-sm font-bold text-slate-900 field-label-preview">
                                        <?= htmlspecialchars($locale === 'en' ? $f['label_en'] : $f['label_ar']) ?>
                                    </span>
                                    <code class="text-[11px] font-mono font-bold text-indigo-600 bg-white px-2 py-0.5 rounded-lg border border-slate-200 field-name-preview">
                                        <?= htmlspecialchars($f['name']) ?>
                                    </code>
                                    <?php if ($f['type'] === 'repeater'): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                            <?= __('repeater') ?> (مكرر)
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button type="button" onclick="removeFieldItem(this)" class="p-1.5 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="<?= __('delete') ?>">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('field_label_ar') ?> *</label>
                                    <input type="text" name="fields[<?= $idx ?>][label_ar]" value="<?= htmlspecialchars($f['label_ar']) ?>" required oninput="updateFieldPreview(this, 'label')" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500 bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('field_label_en') ?> *</label>
                                    <input type="text" name="fields[<?= $idx ?>][label_en]" value="<?= htmlspecialchars($f['label_en']) ?>" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500 bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('field_name_key') ?> *</label>
                                    <input type="text" name="fields[<?= $idx ?>][name]" value="<?= htmlspecialchars($f['name']) ?>" required oninput="updateFieldPreview(this, 'name')" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-indigo-700 focus:outline-none focus:border-indigo-500 bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('field_type') ?></label>
                                    <select name="fields[<?= $idx ?>][type]" onchange="handleTypeChange(this)" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500 bg-white">
                                        <option value="text" <?= $f['type'] === 'text' ? 'selected' : '' ?>>نص عادي (Text)</option>
                                        <option value="textarea" <?= $f['type'] === 'textarea' ? 'selected' : '' ?>>نص متعدد الأسطر (Textarea)</option>
                                        <option value="wysiwyg" <?= $f['type'] === 'wysiwyg' ? 'selected' : '' ?>>محرر غني (WYSIWYG HTML)</option>
                                        <option value="number" <?= $f['type'] === 'number' ? 'selected' : '' ?>>رقمي (Number)</option>
                                        <option value="image" <?= $f['type'] === 'image' ? 'selected' : '' ?>>رفع صورة (Image)</option>
                                        <option value="color" <?= $f['type'] === 'color' ? 'selected' : '' ?>>منتقي ألوان (Color)</option>
                                        <option value="icon" <?= $f['type'] === 'icon' ? 'selected' : '' ?>>🎨 أيقونة (Font Awesome Icon)</option>
                                        <option value="boolean" <?= $f['type'] === 'boolean' ? 'selected' : '' ?>>مفتاح نعم/لا (Boolean Switch)</option>
                                        <option value="select" <?= $f['type'] === 'select' ? 'selected' : '' ?>>قائمة خيارات (Select)</option>
                                        <option value="repeater" <?= $f['type'] === 'repeater' ? 'selected' : '' ?>>🌟 حقل مكرر تفاعلي (Repeater)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('field_default_value') ?></label>
                                    <input type="text" name="fields[<?= $idx ?>][default_value]" value="<?= htmlspecialchars((string)($f['default_value'] ?? '')) ?>" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500 bg-white">
                                </div>
                                <div class="flex items-center pt-5">
                                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="fields[<?= $idx ?>][required]" value="1" <?= !empty($f['required']) ? 'checked' : '' ?> class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span><?= __('field_required') ?></span>
                                    </label>
                                </div>
                            </div>

                            <!-- VISUAL ACF REPEATER SUB-FIELDS BUILDER (No JSON!) -->
                            <div class="repeater-subfields-builder pt-2 <?= $f['type'] === 'repeater' ? '' : 'hidden' ?>">
                                <div class="bg-indigo-50/70 border border-indigo-200/80 rounded-2xl p-4 sm:p-5 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-200/60">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs shadow-sm">
                                                <i data-lucide="list-plus" class="w-4 h-4"></i>
                                            </span>
                                            <div>
                                                <h4 class="text-xs font-black text-slate-900">الحقول الفرعية لهذا المكرر (Repeater Sub-fields)</h4>
                                                <p class="text-[11px] text-slate-500">حدد الحقول التي ستتكرر داخل كل صف من هذا المكرر (مثل العنوان، الصورة، الرابط، السعر... إلخ)</p>
                                            </div>
                                        </div>

                                        <button type="button" onclick="addSubFieldRow(<?= $idx ?>)" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-sm flex items-center gap-1.5 transition self-start sm:self-auto">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>+ إضافة حقل فرعي</span>
                                        </button>
                                    </div>

                                    <!-- Sub-fields List -->
                                    <div class="subfields-container space-y-3" id="subfields-container-<?= $idx ?>">
                                        <?php if (empty($subFields)): ?>
                                            <div class="no-subfields-notice p-4 text-center text-slate-400 border border-dashed border-indigo-300/80 rounded-xl text-xs">
                                                <p class="font-bold text-slate-600">لا توجد حقول فرعية مضافة بعد.</p>
                                                <span class="text-[11px]">اضغط على زر "+ إضافة حقل فرعي" أعلاه لتعريف عناصر الصف في المكرر.</span>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($subFields as $sfIdx => $sf): ?>
                                                <div class="subfield-row bg-white border border-indigo-100 rounded-xl p-3.5 shadow-sm space-y-2.5 relative">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[11px] font-mono font-bold text-indigo-700 flex items-center gap-1">
                                                            <span class="w-5 h-5 rounded-md bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-[10px] font-black">#<?= $sfIdx + 1 ?></span>
                                                            <span class="sf-preview-label font-bold"><?= htmlspecialchars($sf['label'] ?? $sf['name']) ?></span>
                                                        </span>
                                                        <button type="button" onclick="removeSubFieldRow(this)" class="text-slate-400 hover:text-red-600 p-1 transition" title="حذف الحقل الفرعي">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-600 mb-1">التسمية (عربي) *</label>
                                                            <input type="text" name="fields[<?= $idx ?>][sub_fields][<?= $sfIdx ?>][label_ar]" value="<?= htmlspecialchars($sf['label_ar'] ?? ($sf['label'] ?? '')) ?>" required oninput="updateSubFieldPreview(this)" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-600 mb-1">التسمية (إنجليزي)</label>
                                                            <input type="text" name="fields[<?= $idx ?>][sub_fields][<?= $sfIdx ?>][label_en]" value="<?= htmlspecialchars($sf['label_en'] ?? '') ?>" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-600 mb-1">المفتاح البرمجي (Key) *</label>
                                                            <input type="text" name="fields[<?= $idx ?>][sub_fields][<?= $sfIdx ?>][name]" value="<?= htmlspecialchars($sf['name']) ?>" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold text-indigo-700 focus:outline-none focus:border-indigo-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-600 mb-1">نوع الحقل الفرعي</label>
                                                            <select name="fields[<?= $idx ?>][sub_fields][<?= $sfIdx ?>][type]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500">
                                                                <option value="text" <?= ($sf['type'] ?? '') === 'text' ? 'selected' : '' ?>>نص عادي (Text)</option>
                                                                <option value="textarea" <?= ($sf['type'] ?? '') === 'textarea' ? 'selected' : '' ?>>نص متعدد الأسطر (Textarea)</option>
                                                                <option value="image" <?= ($sf['type'] ?? '') === 'image' ? 'selected' : '' ?>>صورة (Image)</option>
                                                                <option value="wysiwyg" <?= ($sf['type'] ?? '') === 'wysiwyg' ? 'selected' : '' ?>>محرر غني (HTML)</option>
                                                                <option value="number" <?= ($sf['type'] ?? '') === 'number' ? 'selected' : '' ?>>رقم (Number)</option>
                                                                <option value="color" <?= ($sf['type'] ?? '') === 'color' ? 'selected' : '' ?>>منتقي لون (Color)</option>
                                                                <option value="boolean" <?= ($sf['type'] ?? '') === 'boolean' ? 'selected' : '' ?>>مفتاح نعم/لا (Switch)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Options (Only for select type) -->
                            <div class="options-container pt-1 <?= ($f['type'] === 'select') ? '' : 'hidden' ?>">
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    خيارات القائمة المنسدلة (سطر لكل خيار: المفتاح : التسمية أو بصيغة JSON)
                                </label>
                                <textarea name="fields[<?= $idx ?>][options]" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs font-medium focus:outline-none focus:border-indigo-500 bg-white" placeholder='{"opt1": "الخيار الأول", "opt2": "الخيار الثاني"}'><?= htmlspecialchars((string)($f['options'] ?? '')) ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-between pt-6 border-t border-slate-200">
            <a href="<?= url('/admin/custom-fields') ?>" class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">
                <?= __('cancel') ?>
            </a>

            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-xl shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span><?= $locale === 'ar' ? 'حفظ مجموعة الحقول' : 'Save Field Group' ?></span>
            </button>
        </div>
    </form>
</div>

<script>
let fieldCounter = <?= count($fields) ?>;

// Dynamic target_filter dropdown: load actual pages/posts/products/categories based on target_type
async function loadTargetFilterOptions(preserveValue = 'all') {
    const typeSelect = document.getElementById('targetTypeSelect');
    const filterSelect = document.getElementById('targetFilterSelect');
    if (!typeSelect || !filterSelect) return;

    const currentValue = preserveValue;
    filterSelect.innerHTML = `<option value="all">${document.documentElement.lang === 'ar' ? 'جاري التحميل...' : 'Loading...'}</option>`;

    try {
        const res = await fetch(appUrl(`/api/admin/acf-targets?type=${typeSelect.value}`));
        const data = await res.json();
        if (!data.success) return;

        const groups = {};
        const ungrouped = [];
        data.options.forEach(opt => {
            if (opt.group) {
                if (!groups[opt.group]) groups[opt.group] = [];
                groups[opt.group].push(opt);
            } else {
                ungrouped.push(opt);
            }
        });

        let html = '';
        ungrouped.forEach(opt => {
            html += `<option value="${opt.value}">${opt.label}</option>`;
        });
        Object.entries(groups).forEach(([groupLabel, opts]) => {
            html += `<optgroup label="${groupLabel}">`;
            opts.forEach(opt => {
                html += `<option value="${opt.value}">${opt.label}</option>`;
            });
            html += `</optgroup>`;
        });

        filterSelect.innerHTML = html;
        // Restore previous selection if it still exists in the new list
        if ([...filterSelect.options].some(o => o.value === currentValue)) {
            filterSelect.value = currentValue;
        } else {
            filterSelect.value = 'all';
        }
    } catch (e) {
        console.error(e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const filterSelect = document.getElementById('targetFilterSelect');
    loadTargetFilterOptions(filterSelect ? filterSelect.dataset.currentValue : 'all');
});

function addNewFieldCard() {
    const container = document.getElementById('fields-container');
    const notice = document.getElementById('no-fields-notice');
    if (notice) notice.remove();

    const idx = fieldCounter++;
    const card = document.createElement('div');
    card.className = 'field-item bg-slate-50/90 border border-slate-200 rounded-3xl p-5 sm:p-6 space-y-4 relative shadow-sm';
    card.dataset.fieldIndex = idx;
    card.innerHTML = `
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black field-badge-num">
                    #${container.querySelectorAll('.field-item').length + 1}
                </span>
                <span class="text-sm font-bold text-slate-900 field-label-preview">حقل جديد</span>
                <code class="text-[11px] font-mono font-bold text-indigo-600 bg-white px-2 py-0.5 rounded-lg border border-slate-200 field-name-preview">field_${idx}</code>
            </div>
            <button type="button" onclick="removeFieldItem(this)" class="p-1.5 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="حذف">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">تسمية الحقل (بالعربية) *</label>
                <input type="text" name="fields[${idx}][label_ar]" required oninput="updateFieldPreview(this, 'label')" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500 bg-white" placeholder="مثال: صور المعرض">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">تسمية الحقل (بالإنجليزية) *</label>
                <input type="text" name="fields[${idx}][label_en]" required oninput="autoSlugName(this, ${idx})" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500 bg-white" placeholder="e.g. Gallery Photos">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">المفتاح البرمجي get_field *</label>
                <input type="text" name="fields[${idx}][name]" id="field_name_${idx}" required oninput="updateFieldPreview(this, 'name')" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-indigo-700 focus:outline-none focus:border-indigo-500 bg-white" placeholder="gallery_photos">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">نوع الحقل</label>
                <select name="fields[${idx}][type]" onchange="handleTypeChange(this)" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500 bg-white">
                    <option value="text">نص عادي (Text)</option>
                    <option value="textarea">نص متعدد الأسطر (Textarea)</option>
                    <option value="wysiwyg">محرر غني (WYSIWYG HTML)</option>
                    <option value="number">رقمي (Number)</option>
                    <option value="image">رفع صورة (Image)</option>
                    <option value="color">منتقي ألوان (Color)</option>
                    <option value="icon">🎨 أيقونة (Font Awesome Icon)</option>
                    <option value="boolean">مفتاح نعم/لا (Boolean Switch)</option>
                    <option value="select">قائمة خيارات (Select)</option>
                    <option value="repeater">🌟 حقل مكرر تفاعلي (Repeater)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">القيمة الافتراضية</label>
                <input type="text" name="fields[${idx}][default_value]" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500 bg-white">
            </div>
            <div class="flex items-center pt-5">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="fields[${idx}][required]" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>حقل إلزامي؟</span>
                </label>
            </div>
        </div>

        <!-- VISUAL ACF REPEATER SUB-FIELDS BUILDER -->
        <div class="repeater-subfields-builder pt-2 hidden">
            <div class="bg-indigo-50/70 border border-indigo-200/80 rounded-2xl p-4 sm:p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-200/60">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                        </span>
                        <div>
                            <h4 class="text-xs font-black text-slate-900">الحقول الفرعية لهذا المكرر (Repeater Sub-fields)</h4>
                            <p class="text-[11px] text-slate-500">حدد الحقول التي ستتكرر داخل كل صف من هذا المكرر (مثل العنوان، الصورة، الرابط، السعر... إلخ)</p>
                        </div>
                    </div>

                    <button type="button" onclick="addSubFieldRow(${idx})" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-sm flex items-center gap-1.5 transition self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>+ إضافة حقل فرعي</span>
                    </button>
                </div>

                <div class="subfields-container space-y-3" id="subfields-container-${idx}">
                    <div class="no-subfields-notice p-4 text-center text-slate-400 border border-dashed border-indigo-300/80 rounded-xl text-xs">
                        <p class="font-bold text-slate-600">لا توجد حقول فرعية مضافة بعد.</p>
                        <span class="text-[11px]">اضغط على زر "+ إضافة حقل فرعي" أعلاه لتعريف عناصر الصف في المكرر.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="options-container pt-1 hidden">
            <label class="block text-xs font-bold text-slate-700 mb-1">خيارات القائمة المنسدلة (Select)</label>
            <textarea name="fields[${idx}][options]" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs font-medium focus:outline-none focus:border-indigo-500 bg-white" placeholder='{"opt1": "الخيار الأول", "opt2": "الخيار الثاني"}'></textarea>
        </div>
    `;

    container.appendChild(card);
    if (window.lucide) lucide.createIcons();
}

function removeFieldItem(btn) {
    const card = btn.closest('.field-item');
    card.remove();
    document.querySelectorAll('.field-item').forEach((c, i) => {
        const b = c.querySelector('.field-badge-num');
        if (b) b.textContent = '#' + (i + 1);
    });
}

function updateFieldPreview(input, type) {
    const card = input.closest('.field-item');
    if (type === 'label') {
        const prev = card.querySelector('.field-label-preview');
        if (prev) prev.textContent = input.value || 'بدون تسمية';
    } else if (type === 'name') {
        const prev = card.querySelector('.field-name-preview');
        if (prev) prev.textContent = input.value || '';
    }
}

function autoSlugName(input, idx) {
    const nameInput = document.getElementById(`field_name_${idx}`);
    if (nameInput && !nameInput.dataset.manual) {
        nameInput.value = input.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        updateFieldPreview(nameInput, 'name');
    }
}

function handleTypeChange(select) {
    const card = select.closest('.field-item');
    const fIdx = card.dataset.fieldIndex;
    const subfieldsBuilder = card.querySelector('.repeater-subfields-builder');
    const optContainer = card.querySelector('.options-container');
    const val = select.value;

    if (val === 'repeater') {
        subfieldsBuilder.classList.remove('hidden');
        optContainer.classList.add('hidden');
        // Add sample initial subfields if empty
        const sfContainer = document.getElementById(`subfields-container-${fIdx}`);
        if (sfContainer && sfContainer.querySelectorAll('.subfield-row').length === 0) {
            addSubFieldRow(fIdx, 'العنوان', 'Title', 'title', 'text');
            addSubFieldRow(fIdx, 'الوصف', 'Description', 'desc', 'textarea');
        }
    } else if (val === 'select') {
        subfieldsBuilder.classList.add('hidden');
        optContainer.classList.remove('hidden');
        const txt = optContainer.querySelector('textarea');
        if (!txt.value || txt.value === '') {
            txt.value = JSON.stringify({"opt1": "الخيار الأول", "opt2": "الخيار الثاني"}, null, 2);
        }
    } else {
        subfieldsBuilder.classList.add('hidden');
        optContainer.classList.add('hidden');
    }
}

// Visual Sub-field Row Manager
function addSubFieldRow(fIdx, defaultLabelAr = '', defaultLabelEn = '', defaultName = '', defaultType = 'text') {
    const sfContainer = document.getElementById(`subfields-container-${fIdx}`);
    if (!sfContainer) return;

    const notice = sfContainer.querySelector('.no-subfields-notice');
    if (notice) notice.remove();

    const sfIdx = sfContainer.querySelectorAll('.subfield-row').length;
    const row = document.createElement('div');
    row.className = 'subfield-row bg-white border border-indigo-100 rounded-xl p-3.5 shadow-sm space-y-2.5 relative';
    row.innerHTML = `
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-mono font-bold text-indigo-700 flex items-center gap-1">
                <span class="w-5 h-5 rounded-md bg-indigo-100 text-indigo-700 inline-flex items-center justify-center text-[10px] font-black sf-badge-num">#${sfIdx + 1}</span>
                <span class="sf-preview-label font-bold">${defaultLabelAr || defaultName || 'حقل فرعي جديد'}</span>
            </span>
            <button type="button" onclick="removeSubFieldRow(this)" class="text-slate-400 hover:text-red-600 p-1 transition" title="حذف الحقل الفرعي">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
            <div>
                <label class="block text-[10px] font-bold text-slate-600 mb-1">التسمية (عربي) *</label>
                <input type="text" name="fields[${fIdx}][sub_fields][${sfIdx}][label_ar]" value="${defaultLabelAr}" required oninput="updateSubFieldPreview(this)" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500" placeholder="مثال: الصورة">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 mb-1">التسمية (إنجليزي)</label>
                <input type="text" name="fields[${fIdx}][sub_fields][${sfIdx}][label_en]" value="${defaultLabelEn}" oninput="autoSlugSubFieldName(this)" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:outline-none focus:border-indigo-500" placeholder="e.g. Photo">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 mb-1">المفتاح البرمجي (Key) *</label>
                <input type="text" name="fields[${fIdx}][sub_fields][${sfIdx}][name]" value="${defaultName}" required class="sf-key-input w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold text-indigo-700 focus:outline-none focus:border-indigo-500" placeholder="photo">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-600 mb-1">نوع الحقل الفرعي</label>
                <select name="fields[${fIdx}][sub_fields][${sfIdx}][type]" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500">
                    <option value="text" ${defaultType === 'text' ? 'selected' : ''}>نص عادي (Text)</option>
                    <option value="textarea" ${defaultType === 'textarea' ? 'selected' : ''}>نص متعدد الأسطر (Textarea)</option>
                    <option value="image" ${defaultType === 'image' ? 'selected' : ''}>صورة (Image)</option>
                    <option value="wysiwyg" ${defaultType === 'wysiwyg' ? 'selected' : ''}>محرر غني (HTML)</option>
                    <option value="number" ${defaultType === 'number' ? 'selected' : ''}>رقم (Number)</option>
                    <option value="color" ${defaultType === 'color' ? 'selected' : ''}>منتقي لون (Color)</option>
                    <option value="boolean" ${defaultType === 'boolean' ? 'selected' : ''}>مفتاح نعم/لا (Switch)</option>
                </select>
            </div>
        </div>
    `;

    sfContainer.appendChild(row);
    if (window.lucide) lucide.createIcons();
}

function removeSubFieldRow(btn) {
    const row = btn.closest('.subfield-row');
    const container = row.parentElement;
    row.remove();
    const rows = container.querySelectorAll('.subfield-row');
    if (rows.length === 0) {
        container.innerHTML = `
            <div class="no-subfields-notice p-4 text-center text-slate-400 border border-dashed border-indigo-300/80 rounded-xl text-xs">
                <p class="font-bold text-slate-600">لا توجد حقول فرعية مضافة بعد.</p>
                <span class="text-[11px]">اضغط على زر "+ إضافة حقل فرعي" أعلاه لتعريف عناصر الصف في المكرر.</span>
            </div>
        `;
    } else {
        rows.forEach((r, idx) => {
            const num = r.querySelector('.sf-badge-num');
            if (num) num.textContent = '#' + (idx + 1);
        });
    }
}

function updateSubFieldPreview(input) {
    const row = input.closest('.subfield-row');
    const prev = row.querySelector('.sf-preview-label');
    if (prev) prev.textContent = input.value || 'حقل فرعي';
}

function autoSlugSubFieldName(input) {
    const row = input.closest('.subfield-row');
    const keyInput = row.querySelector('.sf-key-input');
    if (keyInput && !keyInput.dataset.manual) {
        keyInput.value = input.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    }
}
</script>

<?php include __DIR__ . '/_help_modal.php'; ?>
