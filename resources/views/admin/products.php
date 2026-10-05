<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="w-full space-y-6 pb-12">
    
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-stone-900 font-serif"><?= __('admin_all_products_title') ?></h1>
            <p class="text-xs sm:text-sm font-semibold text-stone-500 mt-1"><?= __('admin_all_products_sub') ?></p>
        </div>

        <a href="<?= url('/admin/products/create') ?>" class="px-5 py-3 bg-[#568d43] hover:bg-[#315b2b] text-white rounded-2xl text-xs sm:text-sm font-black flex items-center gap-2 shadow-md transition">
            <i data-lucide="plus-circle" class="w-4 h-4 text-[#c49a52]"></i>
            <span><?= __('admin_add_new_product') ?></span>
        </a>
    </div>

    <!-- Feedback alerts -->
    <?php if (isset($_GET['created'])): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span><?= $locale === 'en' ? 'Product variety created successfully!' : 'تمت إضافة صنف التمور بنجاح إلى المتجر!' ?></span>
        </div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
            <span><?= $locale === 'en' ? 'Product details updated successfully.' : 'تم تحديث بيانات الصنف بنجاح.' ?></span>
        </div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-2">
            <i data-lucide="trash-2" class="w-4 h-4 text-amber-600"></i>
            <span><?= $locale === 'en' ? 'Product removed from store.' : 'تم حذف صنف التمور من المتجر.' ?></span>
        </div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-xs text-start">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                        <th class="p-4 text-start"><?= __('admin_col_product') ?></th>
                        <th class="p-4 text-start"><?= __('admin_col_category') ?></th>
                        <th class="p-4 text-start"><?= __('admin_col_weight') ?></th>
                        <th class="p-4 text-start"><?= __('admin_col_price') ?></th>
                        <th class="p-4 text-center"><?= __('admin_col_stock') ?></th>
                        <th class="p-4 text-center"><?= __('admin_col_product_type') ?></th>
                        <th class="p-4 text-center"><?= __('admin_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-stone-50/80 transition">
                            <!-- Title & Image -->
                            <td class="p-4 font-bold text-stone-900">
                                <div class="flex items-center gap-3">
                                    <img src="<?= asset($p['featured_image']) ?>" class="w-12 h-12 rounded-xl object-cover border border-stone-200 bg-stone-50 shrink-0">
                                    <div>
                                        <a href="<?= url('/product/' . $p['slug']) ?>" target="_blank" class="hover:text-[#6f431b] transition font-black text-sm block">
                                            <?= esc_html($locale === 'en' && !empty($p['name_en']) ? $p['name_en'] : $p['name_ar']) ?>
                                        </a>
                                        <?php if (!empty($p['badge'])): ?>
                                            <span class="inline-block text-[10px] font-bold text-[#8c5d25] bg-[#fbf5e9] px-2 py-0.5 rounded mt-0.5">
                                                <?= esc_html($p['badge']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="p-4 font-bold text-stone-700 whitespace-nowrap">
                                <span class="bg-stone-100 px-2.5 py-1 rounded-lg text-stone-800">
                                    <?= esc_html($locale === 'en' && !empty($p['category_name_en']) ? $p['category_name_en'] : ($p['category_name_ar'] ?? ($locale === 'en' ? 'General' : 'عام'))) ?>
                                </span>
                            </td>

                            <!-- Weight -->
                            <td class="p-4 text-stone-600 whitespace-nowrap">
                                <span class="font-medium text-xs"><?= esc_html($p['weight'] ?? ($locale === 'en' ? '1 kg' : '1 كجم')) ?></span>
                            </td>

                            <!-- Price -->
                            <td class="p-4 font-black text-[#315b2b] whitespace-nowrap text-sm">
                                <?= number_format($p['min_price'], 2) ?> <?= currency() ?>
                                <?php if ($p['max_price'] > $p['min_price']): ?>
                                    - <?= number_format($p['max_price'], 2) ?> <?= currency() ?>
                                <?php endif; ?>
                            </td>

                            <!-- Stock -->
                            <td class="p-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-xl font-black text-xs <?= $p['total_stock'] <= 15 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' ?>">
                                    <?= (int)$p['total_stock'] ?> <?= $locale === 'en' ? 'units' : 'عبوة' ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="p-4 text-center whitespace-nowrap">
                                <?php if (!empty($p['is_preorder'])): ?>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#315b2b] text-white"><?= $locale === 'en' ? 'Pre-order' : 'حجز مسبق' ?></span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800"><?= $locale === 'en' ? 'In Stock' : 'متوفر فوري' ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="p-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="<?= url('/product/' . $p['slug']) ?>" target="_blank" class="p-2 text-stone-400 hover:text-stone-800 hover:bg-stone-100 rounded-lg transition" title="<?= $locale === 'en' ? 'Preview' : 'معاينة بالمتجر' ?>">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>" class="p-2 text-[#8c5d25] hover:text-[#6f431b] hover:bg-[#fbf5e9] rounded-lg transition font-bold" title="<?= $locale === 'en' ? 'Edit' : 'تعديل' ?>">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form method="post" action="<?= url('/admin/products/delete') ?>" onsubmit="return confirm('<?= $locale === 'en' ? 'Are you sure you want to delete this product?' : 'هل أنت متأكد من حذف هذا الصنف؟' ?>');" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="<?= $locale === 'en' ? 'Delete' : 'حذف' ?>">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
