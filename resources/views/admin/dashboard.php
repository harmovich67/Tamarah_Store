<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="space-y-8 w-full pb-12">
    
    <!-- Welcome Banner -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-[#1e331c] via-[#294215] to-[#142213] text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-md border border-[#c49a52]/30">
        <div class="space-y-1 text-center sm:text-start">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 rounded-full text-xs font-bold text-[#ead9c1] border border-white/15">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#c49a52]"></i>
                <span><?= __('admin_welcome_title') ?></span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-black font-serif text-white">
                <?= __('admin_welcome_heading') ?>
            </h1>
            <p class="text-xs sm:text-sm text-stone-300">
                <?= __('admin_welcome_sub') ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/products') ?>" class="px-4 py-2.5 rounded-xl bg-[#c49a52] hover:bg-[#b0873e] text-stone-900 text-xs font-black transition-all shadow-md flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span><?= __('admin_add_product') ?></span>
            </a>
            <a href="<?= url('/') ?>" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all border border-white/20 flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span><?= __('admin_live_store') ?></span>
            </a>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
        
        <!-- Sales Card -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-2 hover:border-[#8c5d25]/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider"><?= __('admin_kpi_sales') ?></span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-stone-900">
                <?= number_format($totalSales, 2) ?> <span class="text-xs font-bold text-stone-500"><?= currency() ?></span>
            </div>
            <p class="text-[11px] text-stone-400"><?= __('admin_kpi_sales_hint') ?></p>
        </div>

        <!-- Orders Card -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-2 hover:border-[#8c5d25]/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider"><?= __('admin_kpi_orders') ?></span>
                <div class="w-10 h-10 rounded-2xl bg-[#fbf5e9] text-[#6f431b] flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-stone-900">
                <?= $totalOrders ?> <span class="text-xs font-bold text-stone-500"><?= $locale === 'en' ? 'orders' : 'طلب' ?></span>
            </div>
            <p class="text-[11px] text-stone-400"><?= __('admin_kpi_orders_hint') ?></p>
        </div>

        <!-- Products Card -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-2 hover:border-[#8c5d25]/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider"><?= __('admin_kpi_products') ?></span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <i data-lucide="box" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-stone-900">
                <?= $totalProducts ?> <span class="text-xs font-bold text-stone-500"><?= $locale === 'en' ? 'varieties' : 'صنف نشط' ?></span>
            </div>
            <p class="text-[11px] text-stone-400"><?= __('admin_kpi_products_hint') ?></p>
        </div>

        <!-- Customers Card -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-xs space-y-2 hover:border-[#8c5d25]/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider"><?= __('admin_kpi_customers') ?></span>
                <div class="w-10 h-10 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-stone-900">
                <?= $totalCustomers ?> <span class="text-xs font-bold text-stone-500"><?= $locale === 'en' ? 'clients' : 'عميل' ?></span>
            </div>
            <p class="text-[11px] text-stone-400"><?= __('admin_kpi_customers_hint') ?></p>
        </div>

    </div>

    <!-- Middle Grid: Recent Orders & Low Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Recent Orders (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-stone-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#fbf5e9] text-[#8c5d25] flex items-center justify-center">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-base font-bold text-stone-900"><?= __('admin_recent_orders') ?></h2>
                </div>
                <a href="<?= url('/admin/orders') ?>" class="text-xs font-bold text-[#8c5d25] hover:text-[#6f431b] flex items-center gap-1">
                    <span><?= __('admin_view_all_orders') ?></span>
                    <i data-lucide="<?= $isRtl ? 'arrow-left' : 'arrow-right' ?>" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <?php if (!empty($recentOrders)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-start">
                        <thead>
                            <tr class="border-b border-stone-100 text-stone-400 font-bold uppercase tracking-wider">
                                <th class="pb-3 text-start"><?= __('admin_col_order_num') ?></th>
                                <th class="pb-3 text-start"><?= __('admin_col_customer') ?></th>
                                <th class="pb-3 text-start"><?= __('admin_col_city') ?></th>
                                <th class="pb-3 text-start"><?= __('admin_col_total') ?></th>
                                <th class="pb-3 text-start"><?= __('admin_col_payment') ?></th>
                                <th class="pb-3 text-start"><?= __('admin_col_status') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-50">
                            <?php foreach ($recentOrders as $ord): ?>
                                <tr class="hover:bg-stone-50/80 transition-colors">
                                    <td class="py-3 font-mono font-black text-stone-900">
                                        <a href="<?= url('/admin/orders/' . $ord['id']) ?>" class="hover:underline">
                                            <?= esc_html($ord['order_number']) ?>
                                        </a>
                                    </td>
                                    <td class="py-3 font-bold text-stone-800">
                                        <?= esc_html($ord['customer_name']) ?>
                                    </td>
                                    <td class="py-3 text-stone-500">
                                        <?= esc_html($ord['city'] ?? ($locale === 'en' ? 'Riyadh' : 'الرياض')) ?>
                                    </td>
                                    <td class="py-3 font-black text-[#315b2b]">
                                        <?= number_format($ord['total'], 2) ?> <?= currency() ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase <?= $ord['payment_status'] === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>">
                                            <?= esc_html($ord['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#fbf5e9] text-[#6f431b]">
                                            <?= esc_html($ord['shipping_status'] ?? 'processing') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-stone-400 text-xs">
                    <?= $locale === 'en' ? 'No orders registered yet.' : 'لا توجد طلبات مسجلة بعد.' ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Alerts (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-stone-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </div>
                    <h2 class="text-base font-bold text-stone-900"><?= __('admin_low_stock_title') ?></h2>
                </div>
                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md"><?= $locale === 'en' ? '< 20 units' : 'أقل من 20 عبوة' ?></span>
            </div>

            <?php if (!empty($lowStockVariants)): ?>
                <div class="space-y-3">
                    <?php foreach ($lowStockVariants as $var): ?>
                        <div class="p-3 rounded-2xl bg-stone-50 border border-stone-100 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <img src="<?= asset($var['featured_image'] ?? 'assets/images/ajwa_luxury_box_1787053900509.jpg') ?>" class="w-10 h-10 rounded-xl object-cover bg-white border border-stone-200 shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-stone-900 truncate"><?= esc_html($locale === 'en' && !empty($var['product_name_en']) ? $var['product_name_en'] : $var['product_name_ar']) ?></h4>
                                    <span class="text-[10px] text-stone-500"><?= esc_html($var['size_name']) ?></span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl text-xs font-black <?= $var['stock_quantity'] <= 5 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' ?> shrink-0">
                                <?= $var['stock_quantity'] ?> <?= $locale === 'en' ? 'units' : 'عبوة' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-stone-400 text-xs">
                    <?= $locale === 'en' ? 'Stock levels are optimal across all branches.' : 'مستويات المخزون ممتازة في جميع الفروع.' ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Bottom Section: Categories Breakdown -->
    <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-xs space-y-4">
        <h2 class="text-base font-bold text-stone-900 flex items-center gap-2">
            <i data-lucide="tags" class="w-4 h-4 text-[#8c5d25]"></i>
            <span><?= $locale === 'en' ? 'Royal Date Categories & Available Catalog' : 'أقسام التمور الملكية والمنتجات المتوفرة' ?></span>
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <?php foreach ($categoriesCount as $cat): ?>
                <a href="<?= url('/admin/products?cat=' . $cat['slug']) ?>" class="p-4 rounded-2xl bg-stone-50 hover:bg-[#fbf5e9] border border-stone-200 hover:border-[#8c5d25]/40 transition-all text-center space-y-1">
                    <h4 class="text-xs font-bold text-stone-900"><?= esc_html($locale === 'en' && !empty($cat['name_en']) ? $cat['name_en'] : $cat['name_ar']) ?></h4>
                    <div class="text-lg font-black text-[#315b2b]"><?= (int)$cat['products_count'] ?></div>
                    <span class="text-[10px] text-stone-400"><?= $locale === 'en' ? 'varieties' : 'أصناف متوفرة' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>
