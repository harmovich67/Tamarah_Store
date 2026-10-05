<?php
use App\Core\I18n;
$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
?>

<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight"><?= __('pages_management') ?></h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <?= $locale === 'ar' ? 'إدارة الصفحات الثابتة، السياسات، الأسئلة الشائعة، وربطها بالحقول المخصصة ACF' : 'Manage static pages, policies, FAQ, and connect with native ACF fields' ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/custom-fields') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">
                <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                <span><?= __('custom_fields_acf') ?></span>
            </a>
            <a href="<?= url('/admin/pages/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span><?= __('add_new_page') ?></span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="<?= url('/admin/pages') ?>" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i data-lucide="search" class="w-4 h-4 absolute <?= $isRtl ? 'right-3.5' : 'left-3.5' ?> top-3.5 text-slate-400"></i>
                <input type="text" name="q" value="<?= htmlspecialchars((string)($search ?? '')) ?>" placeholder="<?= $locale === 'ar' ? 'ابحث بعنوان الصفحة أو الرابط...' : 'Search by title or slug...' ?>" class="w-full <?= $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' ?> py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value=""><?= $locale === 'ar' ? 'جميع الحالات' : 'All Statuses' ?></option>
                    <option value="published" <?= ($status ?? '') === 'published' ? 'selected' : '' ?>><?= __('status_published') ?></option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>><?= __('status_draft') ?></option>
                </select>

                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="<?= url('/admin/pages') ?>" class="text-xs font-bold text-slate-400 hover:text-slate-600 underline">
                        <?= $locale === 'ar' ? 'إعادة ضبط' : 'Reset' ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Pages List Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs font-medium">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-black text-[11px] tracking-wider">
                        <th class="px-6 py-4 text-start"><?= __('page_title') ?></th>
                        <th class="px-6 py-4 text-start"><?= __('page_slug') ?></th>
                        <th class="px-6 py-4 text-center"><?= __('page_template') ?></th>
                        <th class="px-6 py-4 text-center"><?= __('page_status') ?></th>
                        <th class="px-6 py-4 text-center"><?= $locale === 'ar' ? 'تاريخ التحديث' : 'Updated' ?></th>
                        <th class="px-6 py-4 text-end"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($pages)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <i data-lucide="file-x" class="w-8 h-8"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-700"><?= $locale === 'ar' ? 'لا توجد صفحات مضافة حتى الآن' : 'No pages found' ?></p>
                                <a href="<?= url('/admin/pages/create') ?>" class="inline-block mt-3 text-xs font-bold text-indigo-600 hover:underline">
                                    <?= __('add_new_page') ?> &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pages as $p): ?>
                            <tr class="hover:bg-slate-50/60 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold overflow-hidden flex-shrink-0 border border-slate-200/60">
                                            <?php if (!empty($p['featured_image'])): ?>
                                                <img src="<?= asset($p['featured_image']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i data-lucide="layout" class="w-5 h-5 text-slate-400"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('/admin/pages/' . $p['id'] . '/edit') ?>" class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition">
                                                <?= htmlspecialchars($locale === 'en' && !empty($p['title_en']) ? $p['title_en'] : $p['title_ar']) ?>
                                            </a>
                                            <p class="text-[11px] text-slate-400 mt-0.5 font-semibold">
                                                <?= htmlspecialchars($locale === 'en' ? $p['title_ar'] : $p['title_en']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 font-mono text-[11px] text-indigo-600 bg-indigo-50/60 px-2.5 py-1 rounded-lg border border-indigo-100/50 w-max">
                                        <span>/page/<?= htmlspecialchars($p['slug']) ?></span>
                                        <a href="<?= url('/page/' . htmlspecialchars($p['slug'])) ?>" target="_blank" class="text-slate-400 hover:text-indigo-600 transition" title="<?= __('view_page') ?>">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200/50">
                                        <?= htmlspecialchars($p['template'] ?: 'default') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($p['status'] === 'published'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <?= __('status_published') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <?= __('status_draft') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-slate-400 text-[11px] font-mono">
                                    <?= date('Y/m/d H:i', strtotime($p['updated_at'] ?? $p['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('/page/' . htmlspecialchars($p['slug'])) ?>" target="_blank" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="<?= __('view_page') ?>">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= url('/admin/pages/' . $p['id'] . '/edit') ?>" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="<?= __('edit_page') ?>">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form method="POST" action="<?= url('/admin/pages/delete') ?>" onsubmit="return confirm('<?= $locale === 'ar' ? 'هل أنت متأكد من حذف هذه الصفحة نهائياً؟' : 'Are you sure you want to permanently delete this page?' ?>');" class="inline">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="<?= __('delete') ?>">
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
