<?php
use App\Core\I18n;

$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();
?>

<div class="w-full space-y-6 pb-12">
    
    <!-- Top Bar with Back Link & Invoice Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm">
        <div>
            <div class="flex flex-wrap items-center gap-4">
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900 tracking-tight">تفاصيل الطلب: #<?= htmlspecialchars($order['order_number']) ?></h1>
                <span class="text-xs font-black px-3.5 py-1.5 rounded-full inline-flex items-center gap-2 <?= $order['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' ?>">
                    <span class="w-2 h-2 rounded-full <?= $order['payment_status'] === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
                    <?= $order['payment_status'] === 'paid' ? 'تم السداد بنجاح' : 'في انتظار التحصيل' ?>
                </span>
            </div>
            <p class="text-sm font-semibold text-stone-500 mt-2 flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-[#315b2b]"></i>
                <span>تاريخ وتوقيت الطلب: <?= date('Y/m/d - h:i A', strtotime($order['created_at'])) ?></span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-2xl text-sm font-bold flex items-center gap-2.5 shadow-md transition">
                <i data-lucide="printer" class="w-4 h-4 text-[#c49a52]"></i>
                <span>طباعة الفاتورة</span>
            </button>
            <a href="<?= url('/admin/orders') ?>" class="px-5 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-2xl text-sm font-bold flex items-center gap-2 transition">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                <span>العودة للطلبات</span>
            </a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Order Items & Financials (Col-8) -->
        <div class="lg:col-span-8 space-y-6">
            
            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between border-b border-stone-100 pb-5 mb-6">
                    <div>
                        <h3 class="text-lg font-black text-stone-900">أصناف التمور والبوكسات المطلوبة</h3>
                        <p class="text-xs text-stone-500 mt-0.5">تفاصيل الأوزان والتعبئة والكميات</p>
                    </div>
                    <span class="text-xs font-black text-[#315b2b] bg-[#eef4eb] border border-[#d6e5d2] px-3.5 py-1 rounded-xl">
                        <?= count($items) ?> أصناف
                    </span>
                </div>

                <div class="divide-y divide-stone-100 space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="pt-4 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                            
                            <!-- Product Image & Info -->
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl border border-stone-200 bg-[#f8f5ed] overflow-hidden flex-shrink-0">
                                    <img src="<?= asset($item['featured_image']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div class="space-y-1 min-w-0">
                                    <h4 class="font-black text-stone-900 text-sm sm:text-base leading-snug">
                                        <?= htmlspecialchars($item['product_name_ar'] ?? $item['product_name'] ?? 'صنف تمور فاخر') ?>
                                    </h4>
                                    <?php if (!empty($item['category_name_ar'])): ?>
                                        <span class="inline-flex text-[11px] font-bold text-[#8c5d25] bg-[#faf3e8] px-2 py-0.5 rounded-md">
                                            <?= htmlspecialchars($item['category_name_ar']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <div class="text-xs text-stone-400">
                                        سعر الوحدة: <b class="text-stone-700 font-bold"><?= number_format((float)$item['unit_price'], 2) ?> ر.س</b>
                                    </div>
                                </div>
                            </div>

                            <!-- Variant & Quantity -->
                            <div class="flex items-center sm:flex-col sm:items-center justify-between sm:justify-center gap-2 flex-shrink-0">
                                <span class="inline-flex items-center gap-1.5 font-bold text-xs text-[#315b2b] bg-[#eef4eb] border border-[#d6e5d2] px-3 py-1.5 rounded-xl whitespace-nowrap">
                                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                    <span><?= htmlspecialchars($item['size_name'] ?: 'العبوة القياسية') ?></span>
                                </span>
                                <span class="text-xs font-bold text-stone-500">
                                    الكمية: <b class="text-stone-900 font-black text-sm"><?= $item['quantity'] ?></b>
                                </span>
                            </div>

                            <!-- Subtotal for this item -->
                            <div class="text-left flex-shrink-0 min-w-[90px]">
                                <span class="text-[11px] text-stone-400 block sm:text-left">المجموع</span>
                                <span class="text-base sm:text-lg font-black text-stone-900">
                                    <?= number_format((float)$item['subtotal'], 2) ?> ر.س
                                </span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Financial Breakdown -->
                <div class="border-t-2 border-stone-100 mt-8 pt-6 space-y-3">
                    <div class="flex justify-between items-center text-sm font-semibold text-stone-600">
                        <span>المجموع الفرعي:</span>
                        <span class="text-base font-bold text-stone-900"><?= number_format((float)$order['subtotal'], 2) ?> ر.س</span>
                    </div>
                    <div class="flex justify-between items-center text-sm font-semibold text-stone-600">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-4 h-4 text-[#8c5d25]"></i>
                            <span>رسوم الشحن والتوصيل (<?= htmlspecialchars($order['city'] ?: 'الرياض') ?>):</span>
                        </span>
                        <span class="text-base font-black text-[#8c5d25]">
                            <?= (float)$order['shipping_fee'] > 0 ? number_format((float)$order['shipping_fee'], 2) . ' ر.س' : 'شحن مجاني' ?>
                        </span>
                    </div>
                    <?php if ((float)$order['discount'] > 0): ?>
                        <div class="flex justify-between items-center text-sm font-semibold text-emerald-600">
                            <span>الخصم المطبق:</span>
                            <span class="text-base font-bold">- <?= number_format((float)$order['discount'], 2) ?> ر.س</span>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between items-center text-lg font-black text-stone-900 border-t border-stone-200 pt-4 bg-[#faf8f3] -mx-6 -mb-6 p-6 rounded-b-3xl">
                        <span>الإجمالي الكلي (شامل ضريبة القيمة المضافة 15%):</span>
                        <span class="text-2xl font-black text-[#315b2b]"><?= number_format((float)$order['total'], 2) ?> ر.س</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right Column: Customer & Status (Col-4) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Customer & Delivery Card -->
            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-7 space-y-4">
                <div class="flex items-center gap-3 border-b border-stone-100 pb-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#f4f7f2] text-[#315b2b] flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-stone-900 text-base">بيانات العميل والتوصيل</h3>
                        <p class="text-xs text-stone-400">العنوان المسجل وطريقة الاستلام</p>
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs font-bold text-stone-400 block mb-1">اسم المستلم:</span>
                        <b class="text-stone-900 font-extrabold text-base block"><?= htmlspecialchars($order['customer_name']) ?></b>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-stone-400 block mb-1">رقم الجوال للتواصل والمندوب:</span>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" class="inline-flex items-center gap-2 text-[#315b2b] hover:underline font-mono font-black text-base" dir="ltr">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <span><?= htmlspecialchars($order['customer_phone']) ?></span>
                        </a>
                    </div>

                    <?php if (!empty($order['customer_email'])): ?>
                        <div>
                            <span class="text-xs font-bold text-stone-400 block mb-1">البريد الإلكتروني:</span>
                            <span class="text-stone-700 font-medium"><?= htmlspecialchars($order['customer_email']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="pt-2 border-t border-stone-100">
                        <span class="text-xs font-bold text-stone-400 block mb-1">طريقة الاستلام والعنوان:</span>
                        <div class="bg-[#faf8f3] p-3.5 rounded-2xl border border-stone-200 text-stone-800 leading-relaxed font-medium">
                            <p class="text-[#315b2b] font-black mb-1 flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                <span><?= htmlspecialchars($order['city'] ?: 'المملكة العربية السعودية') ?></span>
                                <span class="text-xs text-[#8c5d25] font-normal">(<?= ($order['delivery_type'] ?? '') === 'pickup' ? 'استلام فرع' : 'توصيل مبرد' ?>)</span>
                            </p>
                            <p class="text-xs text-stone-600"><?= htmlspecialchars($order['shipping_address'] ?: 'العنوان الوطني المسجل') ?></p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-stone-100">
                        <span class="text-xs font-bold text-stone-400 block mb-1">طريقة الدفع:</span>
                        <div class="font-bold text-stone-900 text-xs flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-stone-100 text-stone-700 uppercase font-mono">
                                <?= htmlspecialchars($order['payment_method'] ?? 'COD') ?>
                            </span>
                            <span>
                                <?php 
                                $pm = strtolower($order['payment_method'] ?? 'cod');
                                if ($pm === 'mada') echo 'مدى (Mada)';
                                elseif ($pm === 'apple_pay') echo 'Apple Pay';
                                elseif ($pm === 'tabby') echo 'تقسيط تابي (4 دفعات)';
                                elseif ($pm === 'tamara') echo 'تقسيط تمارا (4 دفعات)';
                                else echo 'الدفع عند الاستلام (COD)';
                                ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($order['notes'])): ?>
                        <div class="pt-2 border-t border-stone-100">
                            <span class="text-xs font-bold text-stone-400 block mb-1">ملاحظات العميل الخاصة:</span>
                            <p class="text-xs text-stone-600 bg-amber-50/70 p-3 rounded-xl border border-amber-200/70"><?= htmlspecialchars($order['notes']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Control Card -->
            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-7 space-y-5">
                <div class="flex items-center gap-3 border-b border-stone-100 pb-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#f4f7f2] text-[#315b2b] flex items-center justify-center">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-stone-900 text-base">تحديث حالة الطلب</h3>
                        <p class="text-xs text-stone-400">الشحن والتجهيز والتحصيل</p>
                    </div>
                </div>

                <form action="<?= url('/admin/orders/update-status') ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">

                    <div>
                        <label class="block text-xs font-black text-stone-700 mb-1.5">حالة الشحن والتجهيز</label>
                        <select name="shipping_status" class="w-full bg-stone-50 border border-stone-300 focus:border-[#315b2b] rounded-2xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                            <option value="pending" <?= $order['shipping_status'] === 'pending' ? 'selected' : '' ?>>قيد التجهيز بالمستودع</option>
                            <option value="processing" <?= $order['shipping_status'] === 'processing' ? 'selected' : '' ?>>تم تغليف الصندوق وجاهز للشحن</option>
                            <option value="shipped" <?= $order['shipping_status'] === 'shipped' ? 'selected' : '' ?>>تم التسليم لسيارة التبريد / الشاحن</option>
                            <option value="delivered" <?= $order['shipping_status'] === 'delivered' ? 'selected' : '' ?>>تم التوصيل بنجاح للعميل</option>
                            <option value="cancelled" <?= $order['shipping_status'] === 'cancelled' ? 'selected' : '' ?>>ملغي</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-stone-700 mb-1.5">حالة السداد والتحصيل</label>
                        <select name="payment_status" class="w-full bg-stone-50 border border-stone-300 focus:border-[#315b2b] rounded-2xl px-4 py-3 text-sm font-bold text-stone-900 focus:outline-none transition">
                            <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>معلق (بانتظار الدفع أو التحصيل عند الاستلام)</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>مدفوع ومحصل بنجاح</option>
                            <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>فشل في السداد</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-[#315b2b] hover:bg-[#24451f] text-white rounded-2xl font-black text-sm shadow-lg shadow-emerald-950/20 hover:shadow-emerald-950/30 flex items-center justify-center gap-2 transition transform active:scale-98">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>حفظ التحديثات</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
