<?php
use App\Core\I18n;
$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
?>

<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                <i data-lucide="sliders" class="w-6 h-6"></i>
            </span>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight"><?= __('custom_fields_acf') ?></h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= $locale === 'ar' ? 'محرك الحقول المخصصة المتقدم (Native ACF Pro) - بناء مجموعات الحقول وربطها بالصفحات والمقالات ومنتجات التمور' : 'Native ACF Pro Engine - Build field groups and assign to pages, posts, date products & categories' ?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/pages') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">
                <i data-lucide="file-text" class="w-4 h-4 text-indigo-600"></i>
                <span><?= __('pages_management') ?></span>
            </a>
            <a href="<?= url('/admin/custom-fields/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span><?= __('add_field_group') ?></span>
            </a>
        </div>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($groups)): ?>
            <div class="col-span-full bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400">
                <i data-lucide="layout-grid" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                <p class="text-sm font-bold text-slate-700"><?= $locale === 'ar' ? 'لا توجد مجموعات حقول مخصصة حتى الآن' : 'No field groups created yet' ?></p>
                <a href="<?= url('/admin/custom-fields/create') ?>" class="inline-block mt-3 text-xs font-bold text-indigo-600 hover:underline">
                    <?= __('add_field_group') ?> &rarr;
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($groups as $g): ?>
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between group">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-500/20">
                                <i data-lucide="folder-code" class="w-5 h-5"></i>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 font-mono">
                                <?= $g['fields_count'] ?> <?= __('fields_count') ?>
                            </span>
                        </div>

                        <div>
                            <h3 class="text-base font-black text-slate-900 group-hover:text-indigo-600 transition">
                                <?= htmlspecialchars($locale === 'en' ? $g['title_en'] : $g['title_ar']) ?>
                            </h3>
                            <p class="text-xs text-slate-400 font-semibold mt-0.5">
                                <?= htmlspecialchars($locale === 'en' ? $g['title_ar'] : $g['title_en']) ?>
                            </p>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5 text-xs font-semibold">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400"><?= __('group_key') ?>:</span>
                                <code class="text-indigo-600 font-mono text-[11px] bg-white px-2 py-0.5 rounded border border-slate-200"><?= htmlspecialchars($g['key_name']) ?></code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400"><?= __('target_type') ?>:</span>
                                <span class="text-slate-700 font-bold uppercase text-[10px] bg-slate-200 px-2 py-0.5 rounded">
                                    <?= htmlspecialchars($g['target_type']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between">
                        <a href="<?= url('/admin/custom-fields/' . $g['id'] . '/edit') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span><?= __('edit_field_group') ?></span>
                        </a>

                        <form method="POST" action="<?= url('/admin/custom-fields/delete') ?>" onsubmit="return confirm('<?= $locale === 'ar' ? 'هل أنت متأكد من حذف مجموعة الحقول هذه؟' : 'Are you sure you want to delete this field group?' ?>');" class="inline">
                            <input type="hidden" name="id" value="<?= $g['id'] ?>">
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="<?= __('delete') ?>">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_help_modal.php'; ?>
