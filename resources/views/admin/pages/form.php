<?php
use App\Core\I18n;
use App\Core\CustomFields;
$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
$isEdit = $isEdit ?? false;
$page = $page ?? null;
?>

<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/pages') ?>" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="<?= __('back_to_pages') ?>">
                <i data-lucide="<?= $isRtl ? 'arrow-right' : 'arrow-left' ?>" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    <?= $isEdit ? __('edit_page') : __('add_new_page') ?>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= $isEdit ? ($locale === 'en' ? $page['title_en'] : $page['title_ar']) : ($locale === 'ar' ? 'إنشاء صفحة جديدة في المنصة وربطها بحقول ACF' : 'Create new page with native ACF fields') ?>
                </p>
            </div>
        </div>
        <?php if ($isEdit && !empty($page['slug'])): ?>
            <a href="<?= url('/page/' . htmlspecialchars($page['slug'])) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <i data-lucide="external-link" class="w-4 h-4 text-indigo-600"></i>
                <span><?= __('view_page') ?></span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Main Form -->
    <form method="POST" action="<?= $isEdit ? url('/admin/pages/' . $page['id'] . '/update') : url('/admin/pages/store') ?>" enctype="multipart/form-data" class="space-y-8">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $page['id'] ?>">
        <?php endif; ?>

        <!-- Section 1: Basic Information -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="type" class="w-5 h-5 text-indigo-600"></i>
                <span><?= $locale === 'ar' ? 'العناوين والروابط الدائمة' : 'Titles & Permalinks' ?></span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('page_title_ar') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title_ar" id="title_ar" value="<?= htmlspecialchars((string)($page['title_ar'] ?? '')) ?>" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="مثال: من نحن - منصة الأز">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('page_title_en') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title_en" id="title_en" value="<?= htmlspecialchars((string)($page['title_en'] ?? '')) ?>" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="e.g. About Us - Al-Az">
                </div>
            </div>

            <!-- Slug & Template -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-2">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        <?= __('page_slug') ?>
                        <span class="text-[11px] font-normal text-slate-400 ms-2 font-mono">/page/{slug}</span>
                    </label>
                    <div class="relative">
                        <span class="absolute <?= $isRtl ? 'right-4' : 'left-4' ?> top-3.5 text-xs font-mono text-slate-400">/page/</span>
                        <input type="text" name="slug" id="slug_input" value="<?= htmlspecialchars((string)($page['slug'] ?? '')) ?>" class="w-full <?= $isRtl ? 'pr-16 pl-4' : 'pl-16 pr-4' ?> py-3 rounded-2xl border border-slate-200 text-xs font-mono font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="about-us">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_template') ?></label>
                    <select name="template" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
                        <option value="default" <?= (($page['template'] ?? '') === 'default') ? 'selected' : '' ?>><?= __('template_default') ?></option>
                        <option value="full_width" <?= (($page['template'] ?? '') === 'full_width') ? 'selected' : '' ?>><?= __('template_full_width') ?></option>
                        <option value="blank" <?= (($page['template'] ?? '') === 'blank') ? 'selected' : '' ?>><?= __('template_blank') ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Page Body & Excerpt -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="align-left" class="w-5 h-5 text-indigo-600"></i>
                <span><?= $locale === 'ar' ? 'المحتوى الرئيسي والنصوص' : 'Main Content & Body' ?></span>
            </h2>

            <!-- Language Content Tabs -->
            <div class="space-y-4">
                <div class="flex border-b border-slate-200">
                    <button type="button" onclick="switchContentLang('ar')" id="tab-btn-ar" class="px-5 py-2.5 text-xs font-black border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2">
                        <span>العربية</span>
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    </button>
                    <button type="button" onclick="switchContentLang('en')" id="tab-btn-en" class="px-5 py-2.5 text-xs font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition flex items-center gap-2">
                        <span>English</span>
                    </button>
                </div>

                <!-- Arabic Content Box -->
                <div id="content-pane-ar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_excerpt_ar') ?></label>
                        <textarea name="excerpt_ar" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition" placeholder="نبذة مختصرة تظهر في بطاقات المشاركة والبحث..."><?= htmlspecialchars((string)($page['excerpt_ar'] ?? '')) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_content_ar') ?></label>
                        <textarea name="content_ar" rows="10" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm leading-relaxed font-normal focus:ring-2 focus:ring-indigo-500 transition" placeholder="اكتب محتوى الصفحة هنا أو كود HTML منسق..."><?= htmlspecialchars((string)($page['content_ar'] ?? '')) ?></textarea>
                    </div>
                </div>

                <!-- English Content Box -->
                <div id="content-pane-en" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_excerpt_en') ?></label>
                        <textarea name="excerpt_en" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 transition" placeholder="Short summary for social and search cards..."><?= htmlspecialchars((string)($page['excerpt_en'] ?? '')) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_content_en') ?></label>
                        <textarea name="content_en" rows="10" class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm leading-relaxed font-normal focus:ring-2 focus:ring-indigo-500 transition" placeholder="Write page content in English or formatted HTML..."><?= htmlspecialchars((string)($page['content_en'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Featured Banner Image & Publishing Status -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="image" class="w-5 h-5 text-indigo-600"></i>
                <span><?= $locale === 'ar' ? 'الصورة البارزة وحالة النشر' : 'Featured Image & Publishing' ?></span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Image Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('featured_image') ?></label>
                    <?php if (!empty($page['featured_image'])): ?>
                        <div class="mb-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-4">
                            <img src="<?= asset($page['featured_image']) ?>" class="w-16 h-16 object-cover rounded-xl border shadow-sm">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($page['featured_image']) ?></p>
                                <span class="text-[11px] text-emerald-600 font-bold"><?= __('current_image') ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="featured_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <input type="text" name="featured_image" value="<?= htmlspecialchars((string)($page['featured_image'] ?? '')) ?>" placeholder="<?= $locale === 'ar' ? 'أو أدخل رابط صورة مباشر (URL)...' : 'Or direct image URL...' ?>" class="mt-2 w-full px-4 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                </div>

                <!-- Status & SEO -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2"><?= __('page_status') ?></label>
                        <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                            <option value="published" <?= (($page['status'] ?? 'published') === 'published') ? 'selected' : '' ?>><?= __('status_published') ?></option>
                            <option value="draft" <?= (($page['status'] ?? '') === 'draft') ? 'selected' : '' ?>><?= __('status_draft') ?></option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('seo_meta_title') ?></label>
                        <input type="text" name="meta_title_ar" value="<?= htmlspecialchars((string)($page['meta_title_ar'] ?? '')) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" placeholder="عنوان يظهر في نتائج بحث جوجل">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1"><?= __('seo_meta_desc') ?></label>
                        <textarea name="meta_desc_ar" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs" placeholder="وصف ملخص لمحركات البحث"><?= htmlspecialchars((string)($page['meta_desc_ar'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- Dynamic ACF Custom Fields Meta Box         -->
        <!-- ========================================== -->
        <?= CustomFields::renderMetaBox('page', $page ? (int)$page['id'] : null) ?>

        <!-- Submit Buttons Bar -->
        <div class="flex items-center justify-between pt-6 border-t border-slate-200">
            <a href="<?= url('/admin/pages') ?>" class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition">
                <?= __('cancel') ?>
            </a>

            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-xl shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span><?= __('save_page') ?></span>
            </button>
        </div>
    </form>
</div>

<script>
function switchContentLang(lang) {
    const paneAr = document.getElementById('content-pane-ar');
    const paneEn = document.getElementById('content-pane-en');
    const tabAr = document.getElementById('tab-btn-ar');
    const tabEn = document.getElementById('tab-btn-en');

    if (lang === 'ar') {
        paneAr.classList.remove('hidden');
        paneEn.classList.add('hidden');
        tabAr.className = "px-5 py-2.5 text-xs font-black border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2";
        tabEn.className = "px-5 py-2.5 text-xs font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition flex items-center gap-2";
    } else {
        paneEn.classList.remove('hidden');
        paneAr.classList.add('hidden');
        tabEn.className = "px-5 py-2.5 text-xs font-black border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2";
        tabAr.className = "px-5 py-2.5 text-xs font-black border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition flex items-center gap-2";
    }
}

// Auto-generate slug from English or Arabic title if creating
document.addEventListener('DOMContentLoaded', () => {
    const titleEn = document.getElementById('title_en');
    const titleAr = document.getElementById('title_ar');
    const slugInput = document.getElementById('slug_input');

    if (!slugInput.value) {
        titleEn?.addEventListener('input', () => {
            if (!slugInput.dataset.manual) {
                slugInput.value = titleEn.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }
        });
        titleAr?.addEventListener('input', () => {
            if (!slugInput.value && !slugInput.dataset.manual) {
                slugInput.value = titleAr.value.toLowerCase().replace(/[^a-z0-9\u0621-\u064A]+/g, '-').replace(/^-+|-+$/g, '');
            }
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.manual = 'true';
        });
    }
});
</script>
