<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();

if (!function_exists('menu_item_destination')) {
    function menu_item_destination($item, $locale)
    {
        if ($item['link_type'] === 'page') {
            if (empty($item['page_slug'])) {
                return $locale === 'ar' ? '(الصفحة المرتبطة محذوفة)' : '(linked page was deleted)';
            }
            return '/page/' . $item['page_slug'];
        }
        return $item['custom_url'] ?: '#';
    }
}
?>

<div class="w-full space-y-6 pb-12">

    <!-- Top Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center font-bold shadow-sm">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight"><?= __('menus_management') ?></h1>
                <p class="text-sm font-semibold text-slate-500 mt-1"><?= __('menus_management_subtitle') ?></p>
            </div>
        </div>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="p-5 bg-emerald-50 border-2 border-emerald-300 rounded-3xl text-sm font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <span><?= __('menu_item_saved_success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($deleted)): ?>
        <div class="p-5 bg-red-50 border-2 border-red-300 rounded-3xl text-sm font-bold text-red-800 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <span><?= __('menu_item_deleted_success') ?></span>
        </div>
    <?php endif; ?>

    <?php
    $sections = [
        'header' => ['items' => $headerItems, 'title' => __('header_menu_title'), 'subtitle' => __('header_menu_subtitle'), 'icon' => 'panel-top'],
        'footer' => ['items' => $footerItems, 'title' => __('footer_menu_title'), 'subtitle' => __('footer_menu_subtitle'), 'icon' => 'panel-bottom'],
    ];
    ?>

    <?php foreach ($sections as $locKey => $section): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i data-lucide="<?= $section['icon'] ?>" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900"><?= $section['title'] ?></h3>
                        <p class="text-xs text-slate-400"><?= $section['subtitle'] ?></p>
                    </div>
                </div>
                <button onclick="openAddModal('<?= $locKey ?>')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black flex items-center gap-2 shadow-md shadow-indigo-600/20 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span><?= __('add_menu_item') ?></span>
                </button>
            </div>

            <?php if (empty($section['items'])): ?>
                <div class="p-10 text-center text-slate-400">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="link-2-off" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <h4 class="font-bold text-slate-700 mb-1"><?= __('no_menu_items') ?></h4>
                    <p class="text-xs text-slate-400"><?= __('no_menu_items_hint') ?></p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100">
                    <?php foreach ($section['items'] as $item): ?>
                        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-black flex-shrink-0">
                                    <?= (int)$item['order_index'] ?>
                                </span>
                                <?php if (!empty($item['icon'])): ?>
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="<?= htmlspecialchars($item['icon']) ?>" class="w-4 h-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">
                                        <?= htmlspecialchars($locale === 'en' ? (($item['label_en'] ?: $item['label_ar'])) : $item['label_ar']) ?>
                                        <?php if ($item['status'] !== 'active'): ?>
                                            <span class="ms-1.5 text-[10px] font-black text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded"><?= __('menu_status_inactive') ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-slate-400 font-mono truncate" dir="ltr"><?= htmlspecialchars(menu_item_destination($item, $locale)) ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button type="button" onclick='openEditModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)' class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span><?= __('edit_menu_item') ?></span>
                                </button>
                                <form action="<?= url('/admin/menus/delete') ?>" method="POST" onsubmit="return confirm('<?= __('confirm_delete_menu_item') ?>');">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="p-2 rounded-xl text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white transition" title="<?= __('delete') ?>">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>

<!-- Add/Edit Menu Item Modal (shared) -->
<div id="menuItemModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-5 border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h3 id="menuModalTitle" class="font-black text-slate-900 text-lg"><?= __('add_menu_item') ?></h3>
            <button type="button" onclick="closeMenuModal()" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="menuItemForm" method="POST" class="space-y-4">
            <input type="hidden" id="miId" name="id" value="">
            <input type="hidden" id="miLocation" name="location" value="header">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_label_ar') ?> *</label>
                    <input type="text" id="miLabelAr" name="label_ar" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_label_en') ?></label>
                    <input type="text" id="miLabelEn" name="label_en" dir="ltr" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_link_type') ?></label>
                <select id="miLinkType" name="link_type" onchange="handleLinkTypeChange()" class="w-full bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3 text-sm font-bold text-indigo-900">
                    <option value="page"><?= __('link_type_page') ?></option>
                    <option value="custom"><?= __('link_type_custom') ?></option>
                </select>
            </div>

            <div id="miPageWrap">
                <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_target_page') ?></label>
                <select id="miPageId" name="page_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold">
                    <?php foreach ($pages as $pg): ?>
                        <option value="<?= $pg['id'] ?>"><?= htmlspecialchars($locale === 'en' ? (($pg['title_en'] ?: $pg['title_ar'])) : $pg['title_ar']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="miCustomWrap" class="hidden">
                <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_custom_url') ?></label>
                <input type="text" id="miCustomUrl" name="custom_url" dir="ltr" placeholder="/catalog" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-mono text-<?= $isRtl ? 'right' : 'left' ?>">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_icon_optional') ?></label>
                    <input type="text" id="miIcon" name="icon" dir="ltr" placeholder="link, star, info" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-mono text-<?= $isRtl ? 'right' : 'left' ?>">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1"><?= __('menu_order_hint') ?></label>
                    <input type="number" id="miOrderIndex" name="order_index" value="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="miOpenNewTab" name="open_new_tab" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span><?= __('menu_open_new_tab') ?></span>
                </label>
                <select id="miStatus" name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-bold">
                    <option value="active"><?= __('menu_status_active') ?></option>
                    <option value="inactive"><?= __('menu_status_inactive') ?></option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeMenuModal()" class="px-5 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold text-sm"><?= __('cancel') ?></button>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-sm shadow-md transition"><?= __('save_menu_item') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleLinkTypeChange() {
        const type = document.getElementById('miLinkType').value;
        document.getElementById('miPageWrap').classList.toggle('hidden', type !== 'page');
        document.getElementById('miCustomWrap').classList.toggle('hidden', type !== 'custom');
    }

    function resetMenuForm() {
        document.getElementById('menuItemForm').reset();
        document.getElementById('miId').value = '';
        handleLinkTypeChange();
    }

    function openAddModal(location) {
        resetMenuForm();
        document.getElementById('miLocation').value = location;
        document.getElementById('menuModalTitle').textContent = <?= json_encode(__('add_menu_item')) ?>;
        document.getElementById('menuItemForm').action = <?= json_encode(url('/admin/menus/store')) ?>;
        document.getElementById('menuItemModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditModal(item) {
        resetMenuForm();
        document.getElementById('miId').value = item.id;
        document.getElementById('miLocation').value = item.location;
        document.getElementById('miLabelAr').value = item.label_ar || '';
        document.getElementById('miLabelEn').value = item.label_en || '';
        document.getElementById('miLinkType').value = item.link_type || 'custom';
        document.getElementById('miPageId').value = item.page_id || '';
        document.getElementById('miCustomUrl').value = item.custom_url || '';
        document.getElementById('miIcon').value = item.icon || '';
        document.getElementById('miOrderIndex').value = item.order_index || 0;
        document.getElementById('miOpenNewTab').checked = !!(item.open_new_tab && item.open_new_tab !== '0');
        document.getElementById('miStatus').value = item.status || 'active';
        handleLinkTypeChange();

        document.getElementById('menuModalTitle').textContent = <?= json_encode(__('edit_menu_item')) ?>;
        document.getElementById('menuItemForm').action = <?= json_encode(url('/admin/menus/update')) ?>;
        document.getElementById('menuItemModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeMenuModal() {
        document.getElementById('menuItemModal').classList.add('hidden');
    }
</script>
