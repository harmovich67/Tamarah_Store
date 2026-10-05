<?php
use App\Core\I18n;
use App\Core\CustomFields;
$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
$isEdit = $isEdit ?? false;
$post = $post ?? null;
?>

<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/posts') ?>" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="<?= __('back_to_posts') ?>">
                <i data-lucide="<?= $isRtl ? 'arrow-right' : 'arrow-left' ?>" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    <?= $isEdit ? ($locale === 'ar' ? 'تعديل المقال' : 'Edit Article') : ($locale === 'ar' ? 'إضافة مقال جديد' : 'Add New Article') ?>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= $locale === 'ar' ? 'تحرير مقال مدونة عالم التمور وربطه بالنوع المناسب وحقول ACF' : 'Edit date blog article and configure custom fields' ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form method="POST" action="<?= $isEdit ? url('/admin/posts/' . $post['id'] . '/update') : url('/admin/posts/store') ?>" enctype="multipart/form-data" class="space-y-8">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
        <?php endif; ?>

        <!-- Basic Data -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('post_type_label') ?> *</label>
                    <select name="post_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700">
                        <option value="post" <?= (($post['post_type'] ?? '') === 'post') ? 'selected' : '' ?>><?= $locale === 'ar' ? 'مقال في مدونة عالم التمور' : 'Date Blog Article' ?></option>
                        <option value="announcement" <?= (($post['post_type'] ?? '') === 'announcement') ? 'selected' : '' ?>><?= $locale === 'ar' ? 'إعلان موسم الحصاد' : 'Harvest Announcement' ?></option>
                        <option value="faq" <?= (($post['post_type'] ?? '') === 'faq') ? 'selected' : '' ?>><?= __('post_type_faq') ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_status') ?></label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700">
                        <option value="published" <?= (($post['status'] ?? 'published') === 'published') ? 'selected' : '' ?>><?= __('status_published') ?></option>
                        <option value="draft" <?= (($post['status'] ?? '') === 'draft') ? 'selected' : '' ?>><?= __('status_draft') ?></option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('title') ?> (بالعربية) *</label>
                    <input type="text" name="title_ar" value="<?= htmlspecialchars((string)($post['title_ar'] ?? '')) ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('title') ?> (English) *</label>
                    <input type="text" name="title_en" value="<?= htmlspecialchars((string)($post['title_en'] ?? '')) ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_slug') ?></label>
                <input type="text" name="slug" value="<?= htmlspecialchars((string)($post['slug'] ?? '')) ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs font-mono" placeholder="auto-generated-slug">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_content') ?> (بالعربية)</label>
                <textarea name="content_ar" rows="6" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm leading-relaxed"><?= htmlspecialchars((string)($post['content_ar'] ?? '')) ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_content') ?> (English)</label>
                <textarea name="content_en" rows="6" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm leading-relaxed"><?= htmlspecialchars((string)($post['content_en'] ?? '')) ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('featured_image') ?></label>
                <input type="file" name="featured_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700">
                <input type="text" name="featured_image" value="<?= htmlspecialchars((string)($post['featured_image'] ?? '')) ?>" placeholder="URL" class="mt-2 w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-mono">
            </div>
        </div>

        <!-- ACF Meta Box for Posts -->
        <?= CustomFields::renderMetaBox('post', $post ? (int)$post['id'] : null, $post['post_type'] ?? null) ?>

        <!-- Submit Bar -->
        <div class="flex items-center justify-between pt-6 border-t border-slate-200">
            <a href="<?= url('/admin/posts') ?>" class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">
                <?= __('cancel') ?>
            </a>

            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-xl shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span><?= $locale === 'ar' ? 'حفظ المنشور' : 'Save Post' ?></span>
            </button>
        </div>
    </form>
</div>
