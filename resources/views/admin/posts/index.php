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
                <i data-lucide="newspaper" class="w-6 h-6"></i>
            </span>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight"><?= __('posts_management') ?></h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= $locale === 'ar' ? 'إدارة مقالات مدونة عالم التمور، إعلانات مواسم الحصاد، والأسئلة الشائعة مع حقول ACF' : 'Manage date blog articles, harvest news, and FAQs with custom ACF fields' ?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/posts/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span><?= __('add_new_post') ?></span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="<?= url('/admin/posts') ?>" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i data-lucide="search" class="w-4 h-4 absolute <?= $isRtl ? 'right-3.5' : 'left-3.5' ?> top-3.5 text-slate-400"></i>
                <input type="text" name="q" value="<?= htmlspecialchars((string)($search ?? '')) ?>" placeholder="<?= $locale === 'ar' ? 'ابحث بالمقالات...' : 'Search posts...' ?>" class="w-full <?= $isRtl ? 'pr-10 pl-4' : 'pl-10 pr-4' ?> py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="type" onchange="this.form.submit()" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value=""><?= $locale === 'ar' ? 'جميع الأنواع' : 'All Post Types' ?></option>
                    <option value="post" <?= ($currentType ?? '') === 'post' ? 'selected' : '' ?>><?= $locale === 'ar' ? 'مدونة عالم التمور' : 'Date Blog' ?></option>
                    <option value="announcement" <?= ($currentType ?? '') === 'announcement' ? 'selected' : '' ?>><?= $locale === 'ar' ? 'إعلانات الحصاد والمواسم' : 'Harvest Announcements' ?></option>
                    <option value="faq" <?= ($currentType ?? '') === 'faq' ? 'selected' : '' ?>><?= __('post_type_faq') ?></option>
                </select>

                <?php if (!empty($search) || !empty($currentType)): ?>
                    <a href="<?= url('/admin/posts') ?>" class="text-xs font-bold text-slate-400 hover:text-slate-600 underline">
                        <?= $locale === 'ar' ? 'إعادة ضبط' : 'Reset' ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs font-medium">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase font-black text-[11px] tracking-wider">
                        <th class="px-6 py-4 text-start"><?= __('title') ?></th>
                        <th class="px-6 py-4 text-start"><?= __('post_type_label') ?></th>
                        <th class="px-6 py-4 text-center"><?= __('page_status') ?></th>
                        <th class="px-6 py-4 text-center"><?= $locale === 'ar' ? 'التاريخ' : 'Date' ?></th>
                        <th class="px-6 py-4 text-end"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="newspaper" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                                <p class="text-sm font-bold text-slate-700"><?= $locale === 'ar' ? 'لا توجد مقالات مضافة حتى الآن' : 'No posts found' ?></p>
                                <a href="<?= url('/admin/posts/create') ?>" class="inline-block mt-3 text-xs font-bold text-indigo-600 hover:underline">
                                    <?= __('add_new_post') ?> &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <tr class="hover:bg-slate-50/60 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold overflow-hidden flex-shrink-0 border border-slate-200/60">
                                            <?php if (!empty($p['featured_image'])): ?>
                                                <img src="<?= asset($p['featured_image']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i data-lucide="file" class="w-5 h-5 text-slate-400"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('/admin/posts/' . $p['id'] . '/edit') ?>" class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition">
                                                <?= htmlspecialchars($locale === 'en' && !empty($p['title_en']) ? $p['title_en'] : $p['title_ar']) ?>
                                            </a>
                                            <p class="text-[11px] text-slate-400 mt-0.5 font-mono">
                                                /<?= htmlspecialchars($p['post_type']) ?>/<?= htmlspecialchars($p['slug']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono">
                                        <?= htmlspecialchars($p['post_type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $p['status'] === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60' ?>">
                                        <?= $p['status'] === 'published' ? __('status_published') : __('status_draft') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-slate-400 text-[11px] font-mono">
                                    <?= date('Y/m/d', strtotime($p['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('/admin/posts/' . $p['id'] . '/edit') ?>" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="<?= __('edit') ?>">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form method="POST" action="<?= url('/admin/posts/delete') ?>" onsubmit="return confirm('<?= $locale === 'ar' ? 'هل أنت متأكد من حذف هذا المنشور؟' : 'Are you sure you want to delete this post?' ?>');" class="inline">
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
