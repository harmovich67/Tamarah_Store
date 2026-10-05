<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
$currentTab = $currentTab ?? 'sections';
?>

<div class="w-full space-y-6 pb-16">

    <!-- Top Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-13 h-13 rounded-2xl bg-[#edf4e8] text-[#315b2b] border border-[#568d43]/30 flex items-center justify-center font-bold shadow-xs">
                <?= tumurna_icon('store', 'w-7 h-7 text-[#315b2b]') ?>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight"><?= __('home_sections_management') ?></h1>
                <p class="text-xs sm:text-sm font-semibold text-stone-500 mt-1"><?= __('home_sections_subtitle') ?></p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openAddBannerModal()" class="px-4 py-2.5 bg-[#8c5d25] hover:bg-[#6f431b] text-white rounded-xl text-xs font-black flex items-center gap-2 shadow-sm transition">
                <?= tumurna_icon('sparkles', 'w-4 h-4 text-amber-200') ?>
                <span><?= __('hs_add_banner_btn') ?></span>
            </button>
            <button type="button" onclick="openAddCustomModal()" class="px-4 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black flex items-center gap-2 shadow-sm transition">
                <?= tumurna_icon('plus', 'w-4 h-4 text-amber-200') ?>
                <span><?= __('hs_add_custom_section_btn') ?></span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if (!empty($saved)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-bold text-emerald-800 flex items-center gap-3 shadow-xs">
            <?= tumurna_icon('check-circle', 'w-5 h-5 text-emerald-600') ?>
            <span><?= __('home_section_saved_success') ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($deleted)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-bold text-rose-800 flex items-center gap-3 shadow-xs">
            <?= tumurna_icon('trash-2', 'w-5 h-5 text-rose-600') ?>
            <span><?= __('home_section_deleted_success') ?></span>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs: Sections vs Banners -->
    <div class="flex items-center gap-2 border-b border-stone-200 pb-2">
        <a href="<?= url('/admin/home-sections?tab=sections') ?>" 
           class="px-5 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 <?= $currentTab === 'sections' ? 'bg-[#315b2b] text-white shadow-sm shadow-[#315b2b]/20' : 'bg-white text-stone-600 hover:text-stone-900 border border-stone-200' ?>">
            <?= tumurna_icon('boxes', 'w-4 h-4 ' . ($currentTab === 'sections' ? 'text-amber-200' : 'text-stone-400')) ?>
            <span><?= __('hs_tab_sections') ?> (<?= count($sections) ?>)</span>
        </a>
        <a href="<?= url('/admin/home-sections?tab=banners') ?>" 
           class="px-5 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 <?= $currentTab === 'banners' ? 'bg-[#315b2b] text-white shadow-sm shadow-[#315b2b]/20' : 'bg-white text-stone-600 hover:text-stone-900 border border-stone-200' ?>">
            <?= tumurna_icon('sparkles', 'w-4 h-4 ' . ($currentTab === 'banners' ? 'text-amber-200' : 'text-stone-400')) ?>
            <span><?= __('hs_tab_banners') ?> (<?= count($banners) ?>)</span>
        </a>
    </div>

    <?php if ($currentTab === 'banners'): ?>
        <!-- ==================================================================== -->
        <!-- TAB 1: BANNERS (HERO SLIDER) -->
        <!-- ==================================================================== -->
        <div class="space-y-4">
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-900 flex items-center gap-2.5">
                <?= tumurna_icon('info', 'w-4 h-4 text-amber-700 shrink-0') ?>
                <span><?= __('hs_banners_info') ?></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($banners as $i => $b): ?>
                    <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-xs flex flex-col justify-between group hover:border-[#8c5d25]/50 transition">
                        <div>
                            <!-- Slide Image Preview -->
                            <div class="relative h-44 w-full bg-stone-900 overflow-hidden">
                                <img src="<?= asset($b['image']) ?>" alt="<?= esc_attr($b['title_ar']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                
                                <div class="absolute top-3 start-3 flex items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-black/60 text-white backdrop-blur-sm border border-white/20">
                                        #<?= $i + 1 ?>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black <?= $b['status'] === 'active' ? 'bg-emerald-500 text-white' : 'bg-stone-500 text-stone-200' ?>">
                                        <?= $b['status'] === 'active' ? __('hs_active') : __('hs_inactive') ?>
                                    </span>
                                </div>

                                <?php if (!empty($b['badge_ar'])): ?>
                                    <div class="absolute bottom-3 start-3 end-3">
                                        <span class="text-[10px] font-bold text-[#c49a52] bg-stone-950/80 px-2 py-0.5 rounded-md backdrop-blur-xs border border-[#c49a52]/30">
                                            <?= htmlspecialchars($b['badge_ar']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Slide Content -->
                            <div class="p-5 space-y-2 text-xs">
                                <h3 class="font-black text-stone-900 text-sm line-clamp-2"><?= htmlspecialchars($b['title_ar']) ?></h3>
                                <p class="text-stone-500 text-[11px] line-clamp-2"><?= htmlspecialchars($b['subtitle_ar'] ?? '') ?></p>
                                <div class="pt-2 flex items-center justify-between text-[11px] font-bold text-stone-600 border-t border-stone-100">
                                    <span class="text-[#8c5d25]"><?= __('hs_button_label_prefix') ?>: <?= htmlspecialchars($b['cta_ar'] ?? 'تسوق الآن') ?></span>
                                    <span class="font-mono text-stone-400 truncate max-w-[120px]" dir="ltr"><?= htmlspecialchars($b['link']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Slide Actions Footer -->
                        <div class="p-4 bg-stone-50 border-t border-stone-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1">
                                <!-- Order buttons -->
                                <form action="<?= url('/admin/banners/move-up') ?>" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" <?= $i === 0 ? 'disabled' : '' ?> class="w-7 h-7 rounded-lg border border-stone-200 bg-white flex items-center justify-center text-stone-600 hover:bg-stone-100 disabled:opacity-30 disabled:cursor-not-allowed transition" title="<?= __('hs_move_up') ?>">
                                        <?= tumurna_icon('chevron-left', 'w-3.5 h-3.5 rotate-90') ?>
                                    </button>
                                </form>
                                <form action="<?= url('/admin/banners/move-down') ?>" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" <?= $i === count($banners) - 1 ? 'disabled' : '' ?> class="w-7 h-7 rounded-lg border border-stone-200 bg-white flex items-center justify-center text-stone-600 hover:bg-stone-100 disabled:opacity-30 disabled:cursor-not-allowed transition" title="<?= __('hs_move_down') ?>">
                                        <?= tumurna_icon('chevron-right', 'w-3.5 h-3.5 rotate-90') ?>
                                    </button>
                                </form>

                                <!-- Toggle Active -->
                                <form action="<?= url('/admin/banners/toggle') ?>" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition <?= $b['status'] === 'active' ? 'text-emerald-700 hover:bg-emerald-100' : 'text-stone-500 hover:bg-stone-200' ?>">
                                        <?= $b['status'] === 'active' ? __('hs_disable_action') : __('hs_enable_action') ?>
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center gap-1">
                                <button type="button" onclick='openEditBannerModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)' class="p-1.5 rounded-lg bg-white border border-stone-200 text-stone-600 hover:text-[#315b2b] hover:border-[#315b2b] transition" title="<?= __('hs_edit_slide') ?>">
                                    <?= tumurna_icon('pencil', 'w-3.5 h-3.5') ?>
                                </button>
                                <form action="<?= url('/admin/banners/delete') ?>" method="POST" onsubmit="return confirm('<?= __('hs_confirm_delete_slide') ?>');" class="inline">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="p-1.5 rounded-lg bg-white border border-stone-200 text-stone-400 hover:text-rose-600 hover:border-rose-300 transition" title="<?= __('hs_delete') ?>">
                                        <?= tumurna_icon('trash-2', 'w-3.5 h-3.5') ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- ==================================================================== -->
        <!-- TAB 2: SECTIONS MANAGEMENT (CORE & CUSTOM) -->
        <!-- ==================================================================== -->
        <div class="space-y-4">
            <div class="bg-indigo-50/70 border border-indigo-200/80 rounded-2xl p-4 text-xs text-indigo-950 flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-2.5">
                    <?= tumurna_icon('info', 'w-4 h-4 text-indigo-700 shrink-0') ?>
                    <span><?= __('hs_sections_info') ?></span>
                </div>
                <span class="font-bold text-[11px] text-indigo-800 bg-white/80 px-3 py-1 rounded-xl border border-indigo-200">
                    <?= __('hs_order_live_note') ?>
                </span>
            </div>

            <!-- Sections List -->
            <div class="bg-white rounded-3xl border border-stone-200 shadow-xs divide-y divide-stone-100 overflow-hidden">
                <?php foreach ($sections as $i => $s): ?>
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-stone-50/60 transition group">
                        
                        <!-- Left Info -->
                        <div class="flex items-center gap-4 min-w-0">
                            <!-- Reorder buttons -->
                            <div class="flex flex-col gap-1 shrink-0">
                                <form action="<?= url('/admin/home-sections/move-up') ?>" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" <?= $i === 0 ? 'disabled' : '' ?> class="w-7 h-7 rounded-lg border border-stone-200 bg-white flex items-center justify-center text-stone-500 hover:bg-stone-100 disabled:opacity-20 disabled:cursor-not-allowed transition" title="<?= __('hs_move_up') ?>">
                                        <?= tumurna_icon('chevron-left', 'w-3.5 h-3.5 rotate-90') ?>
                                    </button>
                                </form>
                                <form action="<?= url('/admin/home-sections/move-down') ?>" method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" <?= $i === count($sections) - 1 ? 'disabled' : '' ?> class="w-7 h-7 rounded-lg border border-stone-200 bg-white flex items-center justify-center text-stone-500 hover:bg-stone-100 disabled:opacity-20 disabled:cursor-not-allowed transition" title="<?= __('hs_move_down') ?>">
                                        <?= tumurna_icon('chevron-right', 'w-3.5 h-3.5 rotate-90') ?>
                                    </button>
                                </form>
                            </div>

                            <!-- Order Badge -->
                            <div class="w-10 h-10 rounded-2xl bg-[#fbf5e9] text-[#8c5d25] border border-[#c49a52]/20 flex items-center justify-center font-black text-sm shrink-0">
                                <?= $i + 1 ?>
                            </div>

                            <!-- Title & Key -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-black text-stone-900 text-sm">
                                        <?= htmlspecialchars($s['title_ar'] ?: $s['name_ar']) ?>
                                    </h3>
                                    
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-stone-100 text-stone-600 border border-stone-200">
                                        <?= htmlspecialchars($s['section_key']) ?>
                                    </span>

                                    <?php if (!empty($s['is_custom'])): ?>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 border border-amber-200">
                                            <?= __('hs_custom_section_badge') ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md <?= $s['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                        <?= $s['status'] === 'active' ? __('hs_status_active_home') : __('hs_status_inactive') ?>
                                    </span>
                                </div>
                                <p class="text-xs text-stone-400 mt-1 truncate">
                                    <?= htmlspecialchars($s['subtitle_ar'] ?: ($s['badge_ar'] ?: __('hs_default_section_desc'))) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                            <!-- Toggle Active/Inactive -->
                            <form action="<?= url('/admin/home-sections/toggle') ?>" method="POST" class="inline">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $s['status'] === 'active' ? 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' : 'bg-stone-100 text-stone-600 hover:bg-stone-200 border border-stone-200' ?>">
                                    <?= tumurna_icon($s['status'] === 'active' ? 'check' : 'x', 'w-3.5 h-3.5') ?>
                                    <span><?= $s['status'] === 'active' ? __('hs_enable_action') : __('hs_inactive') ?></span>
                                </button>
                            </form>

                            <!-- Edit Content Button -->
                            <?php if ($s['section_key'] === 'hero'): ?>
                                <a href="<?= url('/admin/home-sections?tab=banners') ?>" class="px-4 py-1.5 bg-[#8c5d25] hover:bg-[#6f431b] text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                    <?= tumurna_icon('sparkles', 'w-3.5 h-3.5 text-amber-200') ?>
                                    <span><?= __('hs_manage_slides_link') ?></span>
                                </a>
                            <?php elseif ($s['section_key'] === 'features'): ?>
                                <button type="button" onclick='openEditFeaturesModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)' class="px-4 py-1.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                    <?= tumurna_icon('pencil', 'w-3.5 h-3.5 text-amber-200') ?>
                                    <span><?= __('hs_edit_features_link') ?></span>
                                </button>
                            <?php elseif ($s['section_key'] === 'testimonials'): ?>
                                <button type="button" onclick='openEditTestimonialsModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)' class="px-4 py-1.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                    <?= tumurna_icon('pencil', 'w-3.5 h-3.5 text-amber-200') ?>
                                    <span><?= __('hs_edit_testimonials_link') ?></span>
                                </button>
                            <?php elseif ($s['section_key'] === 'newsletter'): ?>
                                <button type="button" onclick='openEditNewsletterModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)' class="px-4 py-1.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                    <?= tumurna_icon('pencil', 'w-3.5 h-3.5 text-amber-200') ?>
                                    <span><?= __('hs_edit_newsletter_link') ?></span>
                                </button>
                            <?php else: ?>
                                <button type="button" onclick='openEditGenericModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)' class="px-4 py-1.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                    <?= tumurna_icon('pencil', 'w-3.5 h-3.5 text-amber-200') ?>
                                    <span><?= __('hs_edit_generic_link') ?></span>
                                </button>
                            <?php endif; ?>

                            <?php if (!empty($s['is_custom'])): ?>
                                <form action="<?= url('/admin/home-sections/delete') ?>" method="POST" onsubmit="return confirm('<?= __('hs_confirm_delete_custom_section') ?>');" class="inline">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="p-2 rounded-xl text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white transition" title="<?= __('hs_delete_section_title') ?>">
                                        <?= tumurna_icon('trash-2', 'w-3.5 h-3.5') ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD / EDIT BANNER (HERO SLIDE) -->
<!-- ========================================================================= -->
<div id="bannerModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#fbf5e9] text-[#8c5d25] flex items-center justify-center border border-[#c49a52]/20">
                    <?= tumurna_icon('sparkles', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 id="bannerModalTitle" class="font-black text-stone-900 text-base"><?= __('hs_add_banner_title') ?></h3>
                    <p class="text-[11px] text-stone-400"><?= __('hs_banner_modal_hint') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeBannerModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center transition">
                <?= tumurna_icon('x', 'w-4 h-4') ?>
            </button>
        </div>

        <form id="bannerForm" action="<?= url('/admin/banners/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            <input type="hidden" id="bannerId" name="id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_badge_ar_label') ?></label>
                    <input type="text" id="bannerBadgeAr" name="badge_ar" placeholder="مثال: تشكيلة التمور السعودية الملكية" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_badge_en_label') ?></label>
                    <input type="text" id="bannerBadgeEn" name="badge_en" dir="ltr" placeholder="e.g. Royal Saudi Dates" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_main_title_ar_label') ?> <span class="text-rose-500">*</span></label>
                    <input type="text" id="bannerTitleAr" name="title_ar" required placeholder="مثال: تمور سعودية مختارة بعناية" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-black focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_title_en_label') ?></label>
                    <input type="text" id="bannerTitleEn" name="title_en" dir="ltr" placeholder="Title in English" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subtitle_ar_label') ?></label>
                    <textarea id="bannerSubtitleAr" name="subtitle_ar" rows="2" placeholder="وصف موجز يظهر تحت العنوان الرئيسي..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subtitle_desc_label') ?> (English)</label>
                    <textarea id="bannerSubtitleEn" name="subtitle_en" dir="ltr" rows="2" placeholder="Brief subtitle in English..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_cta_text_label') ?> (عربي)</label>
                    <input type="text" id="bannerCtaAr" name="cta_ar" value="تسوق الآن" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_cta_text_label') ?> (EN)</label>
                    <input type="text" id="bannerCtaEn" name="cta_en" dir="ltr" value="Shop Now" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_button_link_label') ?></label>
                    <input type="text" id="bannerLink" name="link" value="/catalog" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-mono text-stone-700 focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <!-- Image Upload & Preview -->
            <div class="p-4 bg-[#fbf5e9] border border-[#c49a52]/30 rounded-2xl space-y-3">
                <label class="block font-bold text-stone-800"><?= __('hs_slide_image_label') ?></label>
                <div class="flex items-center gap-4">
                    <div class="w-24 h-16 rounded-xl bg-stone-200 border border-stone-300 overflow-hidden shrink-0 flex items-center justify-center">
                        <img id="bannerImgPreview" src="" class="w-full h-full object-cover hidden">
                        <span id="bannerImgPlaceholder" class="text-[10px] text-stone-400 font-bold"><?= __('hs_preview_placeholder') ?></span>
                    </div>
                    <div class="flex-1 space-y-1">
                        <input type="file" name="image_file" accept="image/*" onchange="previewBannerImg(this)" class="w-full text-xs file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#8c5d25] file:text-white hover:file:bg-[#6f431b] file:cursor-pointer">
                        <input type="text" id="bannerImgUrl" name="image_url" placeholder="أو مسار الصورة: assets/images/..." class="w-full bg-white border border-stone-200 rounded-lg px-3 py-1.5 text-[11px] font-mono text-stone-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeBannerModal()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition">
                    <?= __('hs_cancel') ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl font-black shadow-md shadow-[#315b2b]/20 transition flex items-center gap-2">
                    <?= tumurna_icon('save', 'w-4 h-4 text-white') ?>
                    <span><?= __('hs_save_slide') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT GENERIC SECTION (Categories, Featured, Preorder, Custom) -->
<!-- ========================================================================= -->
<div id="genericModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#edf4e8] text-[#315b2b] flex items-center justify-center border border-[#315b2b]/20">
                    <?= tumurna_icon('pencil', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 id="genericModalTitle" class="font-black text-stone-900 text-base"><?= __('hs_edit_section_content_title') ?></h3>
                    <p id="genericModalSubtitle" class="text-[11px] text-stone-400"><?= __('hs_edit_section_content_hint') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeGenericModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center transition">
                <?= tumurna_icon('x', 'w-4 h-4') ?>
            </button>
        </div>

        <form id="genericForm" action="<?= url('/admin/home-sections/update-section') ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            <input type="hidden" id="genericId" name="id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_badge_label') ?> (عربي)</label>
                    <input type="text" id="genericBadgeAr" name="badge_ar" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_badge_label') ?> (English)</label>
                    <input type="text" id="genericBadgeEn" name="badge_en" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_main_title_label') ?> (عربي) <span class="text-rose-500">*</span></label>
                    <input type="text" id="genericTitleAr" name="title_ar" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-black focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_main_title_label') ?> (English)</label>
                    <input type="text" id="genericTitleEn" name="title_en" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subtitle_desc_label') ?> (عربي)</label>
                    <textarea id="genericSubtitleAr" name="subtitle_ar" rows="3" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b] leading-relaxed"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subtitle_desc_label') ?> (English)</label>
                    <textarea id="genericSubtitleEn" name="subtitle_en" dir="ltr" rows="3" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b] leading-relaxed"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_button_text_label') ?> (عربي)</label>
                    <input type="text" id="genericButtonTextAr" name="button_text_ar" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_button_text_label') ?> (English)</label>
                    <input type="text" id="genericButtonTextEn" name="button_text_en" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_button_url_label') ?></label>
                    <input type="text" id="genericButtonUrl" name="button_url" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <!-- Optional Image Upload for Banners or Custom Sections -->
            <div id="genericImgBlock" class="p-4 bg-[#fbf5e9] border border-[#c49a52]/30 rounded-2xl space-y-3">
                <label class="block font-bold text-stone-800"><?= __('hs_section_image_label') ?></label>
                <div class="flex items-center gap-4">
                    <div class="w-24 h-16 rounded-xl bg-stone-200 border border-stone-300 overflow-hidden shrink-0 flex items-center justify-center">
                        <img id="genericImgPreview" src="" class="w-full h-full object-cover hidden">
                        <span id="genericImgPlaceholder" class="text-[10px] text-stone-400 font-bold"><?= __('hs_no_image_placeholder') ?></span>
                    </div>
                    <div class="flex-1 space-y-1">
                        <input type="file" name="image_file" accept="image/*" onchange="previewGenericImg(this)" class="w-full text-xs file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#8c5d25] file:text-white hover:file:bg-[#6f431b] file:cursor-pointer">
                        <input type="text" id="genericImgUrl" name="image_url" placeholder="أو مسار الصورة: assets/images/..." class="w-full bg-white border border-stone-200 rounded-lg px-3 py-1.5 text-[11px] font-mono text-stone-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeGenericModal()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition">
                    <?= __('hs_cancel') ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl font-black shadow-md shadow-[#315b2b]/20 transition flex items-center gap-2">
                    <?= tumurna_icon('save', 'w-4 h-4 text-white') ?>
                    <span><?= __('hs_save_changes') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT FEATURES (4 CARDS) -->
<!-- ========================================================================= -->
<div id="featuresModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#edf4e8] text-[#315b2b] flex items-center justify-center border border-[#315b2b]/20">
                    <?= tumurna_icon('award', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-base"><?= __('hs_edit_features_title') ?></h3>
                    <p class="text-[11px] text-stone-400"><?= __('hs_edit_features_hint') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeFeaturesModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center transition">
                <?= tumurna_icon('x', 'w-4 h-4') ?>
            </button>
        </div>

        <form action="<?= url('/admin/home-sections/update-features') ?>" method="POST" class="space-y-5 text-xs">
            <input type="hidden" id="featuresId" name="id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-stone-100">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_section_badge_label') ?> (عربي)</label>
                    <input type="text" id="featuresBadgeAr" name="badge_ar" value="الجودة السعودية الخالصة" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_section_badge_label') ?> (English)</label>
                    <input type="text" id="featuresBadgeEn" name="badge_en" dir="ltr" value="Pure Saudi Quality" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-stone-100">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_section_main_title_label') ?> (عربي)</label>
                    <input type="text" id="featuresTitleAr" name="title_ar" value="لماذا يختار عملاؤنا تمرنا؟" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-black focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_section_main_title_label') ?> (English)</label>
                    <input type="text" id="featuresTitleEn" name="title_en" dir="ltr" value="Why Our Customers Choose Tumurna" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <!-- 4 Cards -->
            <div id="featuresCardsContainer" class="space-y-4">
                <!-- Cards will be populated dynamically by JS -->
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeFeaturesModal()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition">
                    <?= __('hs_cancel') ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl font-black shadow-md shadow-[#315b2b]/20 transition flex items-center gap-2">
                    <?= tumurna_icon('save', 'w-4 h-4 text-white') ?>
                    <span><?= __('hs_save_features') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT TESTIMONIALS (REVIEWS) -->
<!-- ========================================================================= -->
<div id="testimonialsModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#edf4e8] text-[#315b2b] flex items-center justify-center border border-[#315b2b]/20">
                    <?= tumurna_icon('heart', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-base"><?= __('hs_edit_testimonials_title') ?></h3>
                    <p class="text-[11px] text-stone-400"><?= __('hs_edit_testimonials_hint') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeTestimonialsModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center transition">
                <?= tumurna_icon('x', 'w-4 h-4') ?>
            </button>
        </div>

        <form action="<?= url('/admin/home-sections/update-testimonials') ?>" method="POST" class="space-y-5 text-xs">
            <input type="hidden" id="testimonialsId" name="id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-stone-100">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_promo_badge_label') ?> (عربي)</label>
                    <input type="text" id="testimonialsBadgeAr" name="badge_ar" value="تجارب حقيقية" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_promo_badge_label') ?> (English)</label>
                    <input type="text" id="testimonialsBadgeEn" name="badge_en" dir="ltr" value="Real Experiences" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-stone-100">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_section_main_title_label') ?> (عربي)</label>
                    <input type="text" id="testimonialsTitleAr" name="title_ar" value="ماذا يقول عملاؤنا عن تمورنا؟" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-black focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_section_main_title_label') ?> (English)</label>
                    <input type="text" id="testimonialsTitleEn" name="title_en" dir="ltr" value="What Customers Say About Our Dates" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <!-- Reviews List -->
            <div id="reviewsContainer" class="space-y-4">
                <!-- Reviews populated dynamically -->
            </div>

            <button type="button" onclick="addNewReviewRow()" class="w-full py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 rounded-xl font-bold transition flex items-center justify-center gap-2">
                <?= tumurna_icon('plus', 'w-4 h-4 text-[#8c5d25]') ?>
                <span>+ <?= __('hs_add_new_review_btn') ?></span>
            </button>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeTestimonialsModal()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition">
                    <?= __('hs_cancel') ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#315b2b] hover:bg-[#254721] text-white rounded-xl font-black shadow-md shadow-[#315b2b]/20 transition flex items-center gap-2">
                    <?= tumurna_icon('save', 'w-4 h-4 text-white') ?>
                    <span><?= __('hs_save_reviews') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT VIP NEWSLETTER CLUB -->
<!-- ========================================================================= -->
<div id="newsletterModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#fbf5e9] text-[#8c5d25] flex items-center justify-center border border-[#c49a52]/20">
                    <?= tumurna_icon('gift', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-base"><?= __('hs_edit_newsletter_title') ?></h3>
                    <p class="text-[11px] text-stone-400"><?= __('hs_edit_newsletter_hint') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeNewsletterModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center transition">
                <?= tumurna_icon('x', 'w-4 h-4') ?>
            </button>
        </div>

        <form action="<?= url('/admin/home-sections/update-newsletter') ?>" method="POST" class="space-y-4 text-xs">
            <input type="hidden" id="newsletterId" name="id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_promo_badge_label') ?> (عربي)</label>
                    <input type="text" id="newsletterBadgeAr" name="badge_ar" value="نادي كبار الشخصيات ومحبي التمور" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_promo_badge_label') ?> (English)</label>
                    <input type="text" id="newsletterBadgeEn" name="badge_en" dir="ltr" value="VIP Date Connoisseurs Club" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_main_title_label') ?> (عربي) <span class="text-rose-500">*</span></label>
                    <input type="text" id="newsletterTitleAr" name="title_ar" required value="انضم إلى نادي تمرنا واحصل على خصم ١٠٪" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-black focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_main_title_label') ?> (English)</label>
                    <input type="text" id="newsletterTitleEn" name="title_en" dir="ltr" value="Join Tumurna VIP Club & Enjoy 10% Off" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_intro_desc_label') ?> (عربي)</label>
                    <textarea id="newsletterSubtitleAr" name="subtitle_ar" rows="3" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_intro_desc_label') ?> (English)</label>
                    <textarea id="newsletterSubtitleEn" name="subtitle_en" dir="ltr" rows="3" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_coupon_code_label') ?></label>
                    <input type="text" id="newsletterCouponCode" name="coupon_code" value="TAMRNA10" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-mono font-bold text-[#8c5d25] focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_discount_percent_label') ?></label>
                    <input type="text" id="newsletterDiscountPercent" name="discount_percent" value="10" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-mono font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subscribe_btn_text_label') ?> (عربي)</label>
                    <input type="text" id="newsletterButtonTextAr" name="button_text_ar" value="انضمام فوري" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#8c5d25]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subscribe_btn_text_label') ?> (English)</label>
                    <input type="text" id="newsletterButtonTextEn" name="button_text_en" dir="ltr" value="Join Now" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#8c5d25]">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeNewsletterModal()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition">
                    <?= __('hs_cancel') ?>
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#8c5d25] hover:bg-[#6f431b] text-white rounded-xl font-black shadow-md transition flex items-center gap-2">
                    <?= tumurna_icon('save', 'w-4 h-4 text-white') ?>
                    <span><?= __('hs_save_club') ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD CUSTOM SECTION -->
<!-- ========================================================================= -->
<div id="customModal" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-stone-200 max-h-[92vh] overflow-y-auto space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#edf4e8] text-[#315b2b] flex items-center justify-center border border-[#315b2b]/20">
                    <?= tumurna_icon('plus', 'w-5 h-5') ?>
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-base"><?= __('hs_add_custom_section_title') ?></h3>
                    <p class="text-[11px] text-stone-400"><?= __('hs_add_custom_section_hint') ?></p>
                </div>
            </div>
            <button type="button" onclick="closeCustomModal()" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 flex items-center justify-center transition">
                <?= tumurna_icon('x', 'w-4 h-4') ?>
            </button>
        </div>

        <form action="<?= url('/admin/home-sections/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_display_style_label') ?></label>
                <select name="style" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                    <option value="centered"><?= __('hs_style_centered') ?></option>
                    <option value="image_first"><?= __('hs_style_image_first') ?></option>
                    <option value="image_last"><?= __('hs_style_image_last') ?></option>
                    <option value="banner_cta"><?= __('hs_style_banner_cta') ?></option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_main_title_label') ?> <span class="text-rose-500">*</span></label>
                <input type="text" name="title_ar" required placeholder="مثال: قصة حصاد النخيل الملكي" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-black focus:outline-none focus:border-[#315b2b]">
            </div>

            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_subtitle_label_short') ?></label>
                <input type="text" name="subtitle_ar" placeholder="مثال: أصالة تمتد لعقود في رعاية النخيل المبارك" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b]">
            </div>

            <div>
                <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_content_body_label') ?></label>
                <textarea name="content_ar" rows="3" placeholder="اكتب نبذة أو تفاصيل هذا القسم..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#315b2b]"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_button_text_label') ?></label>
                    <input type="text" name="button_text_ar" placeholder="مثال: اكتشف المزيد" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-bold focus:outline-none focus:border-[#315b2b]">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 mb-1.5"><?= __('hs_button_url_label') ?></label>
                    <input type="text" name="button_url" placeholder="/about" dir="ltr" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-4 py-2.5 font-mono focus:outline-none focus:border-[#315b2b]">
                </div>
            </div>

            <div class="p-4 bg-stone-50 border border-stone-200 rounded-2xl space-y-2">
                <label class="block font-bold text-stone-800"><?= __('hs_custom_section_image_label') ?></label>
                <input type="file" name="image_file" accept="image/*" class="w-full text-xs file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#315b2b] file:text-white hover:file:bg-[#254721] file:cursor-pointer">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                <button type="button" onclick="closeCustomModal()" class="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl font-bold transition">
                    <?= __('hs_cancel') ?>
                </button>
           <script>
    // 1. Banner Modal Handlers
    function openAddBannerModal() {
        document.getElementById('bannerModalTitle').innerText = '<?= __('hs_add_banner_title') ?>';
        document.getElementById('bannerForm').action = '<?= url('/admin/banners/store') ?>';
        document.getElementById('bannerId').value = '';
        document.getElementById('bannerBadgeAr').value = '';
        document.getElementById('bannerBadgeEn').value = '';
        document.getElementById('bannerTitleAr').value = '';
        document.getElementById('bannerTitleEn').value = '';
        document.getElementById('bannerSubtitleAr').value = '';
        document.getElementById('bannerSubtitleEn').value = '';
        document.getElementById('bannerCtaAr').value = 'تسوق الآن';
        document.getElementById('bannerCtaEn').value = 'Shop Now';
        document.getElementById('bannerLink').value = '/catalog';
        document.getElementById('bannerImgUrl').value = '';
        document.getElementById('bannerImgPreview').classList.add('hidden');
        document.getElementById('bannerImgPlaceholder').classList.remove('hidden');

        const modal = document.getElementById('bannerModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditBannerModal(b) {
        document.getElementById('bannerModalTitle').innerText = '<?= __('hs_edit_slide_title') ?>';
        document.getElementById('bannerForm').action = '<?= url('/admin/banners/update') ?>';
        document.getElementById('bannerId').value = b.id;
        document.getElementById('bannerBadgeAr').value = b.badge_ar || '';
        document.getElementById('bannerBadgeEn').value = b.badge_en || '';
        document.getElementById('bannerTitleAr').value = b.title_ar || '';
        document.getElementById('bannerTitleEn').value = b.title_en || '';
        document.getElementById('bannerSubtitleAr').value = b.subtitle_ar || '';
        document.getElementById('bannerSubtitleEn').value = b.subtitle_en || '';
        document.getElementById('bannerCtaAr').value = b.cta_ar || 'تسوق الآن';
        document.getElementById('bannerCtaEn').value = b.cta_en || 'Shop Now';
        document.getElementById('bannerLink').value = b.link || '/catalog';
        document.getElementById('bannerImgUrl').value = b.image || '';

        const preview = document.getElementById('bannerImgPreview');
        const placeholder = document.getElementById('bannerImgPlaceholder');
        if (b.image) {
            preview.src = b.image.startsWith('http') ? b.image : (window.APP_URL ? window.APP_URL + '/' + b.image.replace(/^\//, '') : '/' + b.image.replace(/^\//, ''));
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        const modal = document.getElementById('bannerModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeBannerModal() {
        const modal = document.getElementById('bannerModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function previewBannerImg(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('bannerImgPreview');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                document.getElementById('bannerImgPlaceholder').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 2. Generic Section Modal (Categories, Featured, Preorder, Custom)
    function openEditGenericModal(s) {
        document.getElementById('genericId').value = s.id;
        document.getElementById('genericBadgeAr').value = s.badge_ar || '';
        document.getElementById('genericBadgeEn').value = s.badge_en || '';
        document.getElementById('genericTitleAr').value = s.title_ar || s.name_ar || '';
        document.getElementById('genericTitleEn').value = s.title_en || s.name_en || '';
        document.getElementById('genericSubtitleAr').value = s.subtitle_ar || '';
        document.getElementById('genericSubtitleEn').value = s.subtitle_en || '';
        document.getElementById('genericButtonTextAr').value = s.button_text_ar || '';
        document.getElementById('genericButtonTextEn').value = s.button_text_en || '';
        document.getElementById('genericButtonUrl').value = s.button_url || '';
        document.getElementById('genericImgUrl').value = s.image || '';

        document.getElementById('genericModalTitle').innerText = '<?= __('hs_edit_section_prefix') ?>: ' + (s.name_ar || s.title_ar);
        
        const preview = document.getElementById('genericImgPreview');
        const placeholder = document.getElementById('genericImgPlaceholder');
        if (s.image) {
            preview.src = s.image.startsWith('http') ? s.image : (window.APP_URL ? window.APP_URL + '/' + s.image.replace(/^\//, '') : '/' + s.image.replace(/^\//, ''));
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        const modal = document.getElementById('genericModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeGenericModal() {
        const modal = document.getElementById('genericModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function previewGenericImg(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('genericImgPreview');
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                document.getElementById('genericImgPlaceholder').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 3. Features Modal
    function openEditFeaturesModal(s) {
        document.getElementById('featuresId').value = s.id;
        document.getElementById('featuresBadgeAr').value = s.badge_ar || 'الجودة السعودية الخالصة';
        document.getElementById('featuresBadgeEn').value = s.badge_en || 'Pure Saudi Quality';
        document.getElementById('featuresTitleAr').value = s.title_ar || 'لماذا يختار عملاؤنا تمرنا؟';
        document.getElementById('featuresTitleEn').value = s.title_en || 'Why Our Customers Choose Tumurna';

        const container = document.getElementById('featuresCardsContainer');
        container.innerHTML = '';

        const defaultCards = [
            { icon: 'truck', title_ar: 'شحن مبرد فائق السرعة', title_en: 'Cold Express Delivery', desc_ar: 'أسطول سيارات شحن مبردة مجهزة بدرجات حرارة مثالية لحفظ قوام ورطوبة وجودة التمور الطازجة.', desc_en: 'Temperature-controlled refrigerated fleet preserving the exact freshness of your dates.' },
            { icon: 'award', title_ar: 'ضمان الجودة الملكية ١٠٠٪', title_en: '100% Royal Quality', desc_ar: 'تمور نخب أول منتقاة حبة بحبة ومفحوصة مخبرياً وفق معايير الهيئة العامة للغذاء والدواء.', desc_en: 'First-grade dates inspected piece by piece according to highest SFDA standards.' },
            { icon: 'gift', title_ar: 'تغليف إهداء فاخر', title_en: 'Luxury Gift Packaging', desc_ar: 'صناديق خشبية ومخملية مصممة خصيصاً للمناسبات الملكية، والأعياد، وحفلات الاستقبال.', desc_en: 'Custom wooden & velvet boxes designed for royal occasions, Eid, and VIP receptions.' },
            { icon: 'store', title_ar: 'فروع في الرياض وجدة والمدينة', title_en: 'Branches in Riyadh, Jeddah & Madinah', desc_ar: 'إمكانية الاستلام المباشر أو تذوق التمور الفاخرة عبر صالات عرضنا الراقية المنتشرة بالمملكة.', desc_en: 'Direct pickup and complimentary tasting across our luxury date showrooms.' }
        ];

        let cards = (s.settings && s.settings.cards && s.settings.cards.length > 0) ? s.settings.cards : defaultCards;

        cards.forEach((c, idx) => {
            const cardHtml = `
                <div class="p-4 bg-stone-50 border border-stone-200 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-stone-800 text-xs">كرت الميزة #${idx + 1}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-stone-500 font-bold">رمز الأيقونة:</span>
                            <select name="cards[${idx}][icon]" class="bg-white border border-stone-200 rounded-lg px-2.5 py-1 text-[11px] font-bold">
                                <option value="truck" ${c.icon === 'truck' ? 'selected' : ''}>شاحنة (truck)</option>
                                <option value="award" ${c.icon === 'award' ? 'selected' : ''}>وسام وجائزة (award)</option>
                                <option value="gift" ${c.icon === 'gift' ? 'selected' : ''}>هدية (gift)</option>
                                <option value="store" ${c.icon === 'store' ? 'selected' : ''}>متجر وفرع (store)</option>
                                <option value="snowflake" ${c.icon === 'snowflake' ? 'selected' : ''}>تبريد وثلج (snowflake)</option>
                                <option value="shield-check" ${c.icon === 'shield-check' ? 'selected' : ''}>حماية وضمان (shield-check)</option>
                                <option value="star" ${c.icon === 'star' ? 'selected' : ''}>نجمة ملكية (star)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-stone-600 mb-1">عنوان الميزة (عربي)</label>
                            <input type="text" name="cards[${idx}][title_ar]" value="${c.title_ar || ''}" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 font-bold text-xs focus:outline-none focus:border-[#315b2b]">
                        </div>
                        <div>
                            <label class="block font-bold text-stone-600 mb-1">Feature Title (EN)</label>
                            <input type="text" name="cards[${idx}][title_en]" value="${c.title_en || ''}" dir="ltr" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-[#315b2b]">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-stone-600 mb-1">شرح وتفاصيل الميزة (عربي)</label>
                            <textarea name="cards[${idx}][desc_ar]" rows="2" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:border-[#315b2b]">${c.desc_ar || ''}</textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-stone-600 mb-1">Feature Description (EN)</label>
                            <textarea name="cards[${idx}][desc_en]" rows="2" dir="ltr" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:border-[#315b2b]">${c.desc_en || ''}</textarea>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', cardHtml);
        });

        const modal = document.getElementById('featuresModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeFeaturesModal() {
        const modal = document.getElementById('featuresModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 4. Testimonials Modal
    function openEditTestimonialsModal(s) {
        document.getElementById('testimonialsId').value = s.id;
        document.getElementById('testimonialsBadgeAr').value = s.badge_ar || 'تجارب حقيقية';
        document.getElementById('testimonialsBadgeEn').value = s.badge_en || 'Real Experiences';
        document.getElementById('testimonialsTitleAr').value = s.title_ar || 'ماذا يقول عملاؤنا عن تمورنا؟';
        document.getElementById('testimonialsTitleEn').value = s.title_en || 'What Customers Say About Our Dates';

        const container = document.getElementById('reviewsContainer');
        container.innerHTML = '';

        const defaultReviews = [
            { name: 'د. خالد السعدون', name_en: 'Dr. Khaled Al-Saadoun', city: 'الرياض', city_en: 'Riyadh', rating: 5, comment: 'عجوة العالية من أروع ما تذوقت، طرية وحبات منتقاة ونظافة تامة. تغليف الشحن المبرد ممتاز ووصلت في نفس اليوم.', comment_en: 'The royal Madinah Ajwa is exceptionally tender, premium hand-picked, and remarkably fresh. Cold delivery was fast and flawless.', product_name: 'عجوة المدينة المنورة العالية الملكية', product_name_en: 'Royal Madinah Premium Ajwa' },
            { name: 'أ. منيرة القحطاني', name_en: 'Ms. Muneera Al-Qahtani', city: 'جدة', city_en: 'Jeddah', rating: 5, comment: 'صندوق تمرنا الخشبي فخم جداً ومناسب للإهداء الراقي. أهديته لوالدي في العيد وكان مبهوراً بدقة التنسيق وجودة التمور.', comment_en: 'The luxury wooden gift box is exquisite and ideal for prestigious gifting. My father was deeply impressed by the royal packaging.', product_name: 'صندوق تمرنا الملكي الخشبي الفاخر', product_name_en: 'Tumurna Royal Wooden Gift Trunk' },
            { name: 'م. سلطان الشمري', name_en: 'Eng. Sultan Al-Shammari', city: 'الدمام', city_en: 'Dammam', rating: 5, comment: 'السكري المفتل مقرمش شقار ولذاذة لا توصف. خدمة العملاء تعاملهم راقي ومحترف، أصبح متجر تمرنا خياري الدائم.', comment_en: 'The crispy Muftal Sukkari dates are delicious and golden. Customer care is highly professional. Tumurna is my go-to dates boutique.', product_name: 'سكري القصيم مفتل درجة أولى', product_name_en: 'First-Grade Qassim Muftal Sukkari' }
        ];

        let reviews = (s.settings && s.settings.reviews && s.settings.reviews.length > 0) ? s.settings.reviews : defaultReviews;

        reviews.forEach((r, idx) => {
            renderReviewRow(idx, r);
        });

        const modal = document.getElementById('testimonialsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function renderReviewRow(idx, r) {
        const container = document.getElementById('reviewsContainer');
        const rowHtml = `
            <div class="review-row p-4 bg-stone-50 border border-stone-200 rounded-2xl space-y-3 relative">
                <button type="button" onclick="this.closest('.review-row').remove()" class="absolute top-3 end-3 text-stone-400 hover:text-rose-600 transition" title="<?= __('hs_delete_this_review') ?>">
                    ✕
                </button>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_customer_name_label') ?> (عربي)</label>
                        <input type="text" name="reviews[${idx}][name]" value="${r.name || ''}" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 font-bold text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_customer_name_label') ?> (EN)</label>
                        <input type="text" name="reviews[${idx}][name_en]" value="${r.name_en || ''}" dir="ltr" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_rating_label') ?></label>
                        <select name="reviews[${idx}][rating]" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 font-bold text-xs text-amber-500">
                            <option value="5" ${r.rating == 5 ? 'selected' : ''}>★★★★★ (5 نجوم)</option>
                            <option value="4" ${r.rating == 4 ? 'selected' : ''}>★★★★☆ (4 نجوم)</option>
                            <option value="3" ${r.rating == 3 ? 'selected' : ''}>★★★☆☆ (3 نجوم)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_city_label') ?> (عربي)</label>
                        <input type="text" name="reviews[${idx}][city]" value="${r.city || 'الرياض'}" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_city_label') ?> (EN)</label>
                        <input type="text" name="reviews[${idx}][city_en]" value="${r.city_en || 'Riyadh'}" dir="ltr" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-2 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_related_product_label') ?> (عربي)</label>
                        <input type="text" name="reviews[${idx}][product_name]" value="${r.product_name || ''}" placeholder="عجوة المدينة الملكية" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_related_product_label') ?> (EN)</label>
                        <input type="text" name="reviews[${idx}][product_name_en]" value="${r.product_name_en || ''}" dir="ltr" placeholder="Royal Madinah Ajwa" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_review_comment_label') ?> (عربي)</label>
                        <textarea name="reviews[${idx}][comment]" rows="2" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs">${r.comment || ''}</textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-stone-600 mb-1"><?= __('hs_review_comment_label') ?> (EN)</label>
                        <textarea name="reviews[${idx}][comment_en]" rows="2" dir="ltr" class="w-full bg-white border border-stone-200 rounded-xl px-3 py-1.5 text-xs">${r.comment_en || ''}</textarea>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', rowHtml);
    }

    function addNewReviewRow() {
        const container = document.getElementById('reviewsContainer');
        const count = container.querySelectorAll('.review-row').length;
        renderReviewRow(count, { name: '', name_en: '', city: 'الرياض', city_en: 'Riyadh', rating: 5, comment: '', comment_en: '', product_name: 'تمور ملكية فاخرة', product_name_en: 'Royal Dates' });
    }

    function closeTestimonialsModal() {
        const modal = document.getElementById('testimonialsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 5. Newsletter Modal
    function openEditNewsletterModal(s) {
        document.getElementById('newsletterId').value = s.id;
        document.getElementById('newsletterBadgeAr').value = s.badge_ar || 'نادي كبار الشخصيات ومحبي التمور';
        document.getElementById('newsletterBadgeEn').value = s.badge_en || 'VIP Date Connoisseurs Club';
        document.getElementById('newsletterTitleAr').value = s.title_ar || 'انضم إلى نادي تمرنا واحصل على خصم ١٠٪';
        document.getElementById('newsletterTitleEn').value = s.title_en || 'Join Tumurna VIP Club & Enjoy 10% Off';
        document.getElementById('newsletterSubtitleAr').value = s.subtitle_ar || '';
        document.getElementById('newsletterSubtitleEn').value = s.subtitle_en || '';
        document.getElementById('newsletterButtonTextAr').value = s.button_text_ar || 'انضمام فوري';
        document.getElementById('newsletterButtonTextEn').value = s.button_text_en || 'Join Now';

        const settings = s.settings || {};
        document.getElementById('newsletterCouponCode').value = settings.coupon_code || 'TAMRNA10';
        document.getElementById('newsletterDiscountPercent').value = settings.discount_percent || '10';

        const modal = document.getElementById('newsletterModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeNewsletterModal() {
        const modal = document.getElementById('newsletterModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 6. Custom Section Modal
    function openAddCustomModal() {
        const modal = document.getElementById('customModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCustomModal() {
        const modal = document.getElementById('customModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
