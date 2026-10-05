<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = I18n::getLocale();
?>

<div class="w-full space-y-6 pb-12">
    
    <!-- Header -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#f4f7f2] text-[#315b2b] border border-[#d6e5d2] flex items-center justify-center font-bold shadow-sm">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight"><?= __('admin_orders_title') ?></h1>
                    <p class="text-sm font-semibold text-stone-500 mt-1"><?= __('admin_orders_sub') ?></p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-4 py-2 bg-[#f8f5ed] border border-[#e8dfc8] rounded-xl text-xs font-black text-[#8c5d25]">
                <?= $locale === 'en' ? 'Total Orders: ' : 'إجمالي الطلبات: ' ?><?= count($orders) ?>
            </span>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-xs text-start">
                <thead>
                    <tr class="bg-[#faf8f3] border-b border-stone-200 text-stone-600 font-black">
                        <th class="p-4 whitespace-nowrap text-start"><?= __('admin_col_order_num') ?></th>
                        <th class="p-4 whitespace-nowrap text-start"><?= __('admin_col_customer') ?></th>
                        <th class="p-4 whitespace-nowrap text-start"><?= __('admin_col_items') ?></th>
                        <th class="p-4 whitespace-nowrap text-start"><?= $locale === 'en' ? 'City / Fulfillment' : 'المدينة / طريقة الاستلام' ?></th>
                        <th class="p-4 whitespace-nowrap text-start"><?= __('admin_col_total') ?></th>
                        <th class="p-4 text-center whitespace-nowrap"><?= $locale === 'en' ? 'Payment Status' : 'حالة الدفع' ?></th>
                        <th class="p-4 text-center whitespace-nowrap"><?= $locale === 'en' ? 'Shipping Status' : 'حالة الشحن' ?></th>
                        <th class="p-4 text-center whitespace-nowrap"><?= __('admin_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center text-stone-400">
                                <i data-lucide="package-open" class="w-10 h-10 mx-auto mb-2 text-stone-300"></i>
                                <?= $locale === 'en' ? 'No orders recorded yet.' : 'لا توجد طلبات مسجلة حالياً' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-[#faf8f3]/60 transition">
                                <td class="p-4 font-mono font-bold text-stone-900 whitespace-nowrap">
                                    <span class="text-[#315b2b] font-black">#<?= htmlspecialchars($o['order_number']) ?></span>
                                    <span class="block text-[10px] text-stone-400 font-sans mt-0.5"><?= date('Y/m/d - H:i', strtotime($o['created_at'])) ?></span>
                                </td>
                                <td class="p-4 text-stone-700 whitespace-nowrap">
                                    <span class="font-black text-stone-900 block"><?= htmlspecialchars($o['customer_name']) ?></span>
                                    <span class="text-[11px] text-stone-400 font-mono" dir="ltr"><?= htmlspecialchars($o['customer_phone']) ?></span>
                                </td>
                                <td class="p-4 text-stone-600 max-w-xs truncate">
                                    <span class="font-bold text-stone-800"><?= htmlspecialchars($o['items_summary'] ?: ($locale === 'en' ? 'Luxury Date Products' : 'منتجات تمور فاخرة')) ?></span>
                                    <span class="text-[10px] text-stone-400 block"><?= (int)($o['items_count'] ?? 1) ?> <?= $locale === 'en' ? 'items' : 'قطع' ?></span>
                                </td>
                                <td class="p-4 text-stone-600 whitespace-nowrap">
                                    <div class="font-bold text-stone-900"><?= htmlspecialchars($o['city'] ?: ($locale === 'en' ? 'Riyadh' : 'الرياض')) ?></div>
                                    <span class="text-[11px] text-[#8c5d25] font-medium">
                                        <?php if (($o['delivery_type'] ?? '') === 'pickup'): ?>
                                            <?= $locale === 'en' ? 'Branch Pickup' : 'استلام من الفرع' ?>
                                        <?php else: ?>
                                            <?= $locale === 'en' ? 'Cold Express Freight' : 'شحن مبرد للمنزل' ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="p-4 font-black text-[#315b2b] whitespace-nowrap text-sm">
                                    <?= number_format((float)$o['total'], 2) ?> <?= currency() ?>
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-black px-3 py-1 rounded-full whitespace-nowrap <?= $o['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $o['payment_status'] === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
                                        <span><?= $o['payment_status'] === 'paid' ? ($locale === 'en' ? 'Paid' : 'مدفوع') : ($locale === 'en' ? 'Pending' : 'في الانتظار') ?></span>
                                    </span>
                                    <span class="block text-[10px] text-stone-400 mt-0.5"><?= strtoupper($o['payment_method'] ?? 'COD') ?></span>
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <?php 
                                    $ship = $o['shipping_status'] ?? 'pending';
                                    $shipClasses = [
                                        'pending' => 'bg-stone-100 text-stone-700',
                                        'shipped' => 'bg-blue-100 text-blue-800',
                                        'delivered' => 'bg-emerald-100 text-emerald-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    $shipLabels = [
                                        'pending' => $locale === 'en' ? 'Processing' : 'قيد التجهيز',
                                        'shipped' => $locale === 'en' ? 'Shipped' : 'تم الشحن',
                                        'delivered' => $locale === 'en' ? 'Delivered' : 'تم التوصيل',
                                        'cancelled' => $locale === 'en' ? 'Cancelled' : 'ملغي',
                                    ];
                                    ?>
                                    <span class="inline-flex text-[11px] font-black px-2.5 py-0.5 rounded-lg <?= $shipClasses[$ship] ?? 'bg-stone-100 text-stone-700' ?>">
                                        <?= $shipLabels[$ship] ?? $ship ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <a href="<?= url('/admin/orders/' . $o['id']) ?>" class="px-3.5 py-1.5 bg-[#f4f7f2] hover:bg-[#315b2b] hover:text-white text-[#315b2b] font-black rounded-xl transition inline-flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span><?= $locale === 'en' ? 'Details' : 'تفاصيل الطلب' ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
