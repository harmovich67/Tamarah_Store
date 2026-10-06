<?php
/**
 * Tamrna Foundation - Product Details View
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();

$product = $product ?? [];
$variants = $variants ?? [];
$relatedProducts = $relatedProducts ?? [];

$isPreorder = !empty($product['is_preorder']);
$isFragile = !empty($product['is_fragile']);
$stockQty = (int)($product['stock_quantity'] ?? 50);
$isOutOfStock = $stockQty <= 0;

$effectivePrice = ($product['sale_price'] !== null && $product['sale_price'] > 0) ? (float)$product['sale_price'] : (float)$product['price'];
$oldPrice = ($product['sale_price'] !== null && $product['sale_price'] > 0) ? (float)$product['price'] : null;

// With variants, the first (cheapest) variant is the selected default
$firstVariant = $variants[0] ?? null;
if ($firstVariant) {
    $vp = ($firstVariant['sale_price'] ?? 0) > 0 ? (float)$firstVariant['sale_price'] : (float)$firstVariant['price'];
    $effectivePrice = $vp;
    $oldPrice = ($firstVariant['sale_price'] ?? 0) > 0 ? (float)$firstVariant['price'] : null;
}

$productName = lang_get($product, 'name');
$productDesc = lang_get($product, 'description');
$categoryName = lang_get($product, 'category_name', ($isRtl ? 'تمور ملكية فاخرة' : 'Royal Luxury Dates'));
$badgeText = lang_get($product, 'badge');

// Gallery: JSON array or comma separated list, falling back to the featured image
$mainImage = $product['featured_image'] ?? 'assets/images/home/ajwa.webp';
$gallery = [$mainImage];
if (!empty($product['gallery'])) {
    $decoded = json_decode((string)$product['gallery'], true);
    if (!is_array($decoded)) {
        $decoded = array_filter(array_map('trim', explode(',', (string)$product['gallery'])));
    }
    foreach ($decoded as $g) {
        if (is_string($g) && $g !== '' && !in_array($g, $gallery, true)) $gallery[] = $g;
    }
}

// WhatsApp number from site settings
$waRow = \Database\Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'contact_whatsapp'");
$whatsappNumber = preg_replace('/\D+/', '', (string)($waRow['value'] ?? '')) ?: '966500000000';
$whatsappMsg = urlencode(($isRtl ? 'مرحباً متجر تمرنا، أود الاستفسار عن وحجز منتج: ' : 'Hello Tumurna, I would like to inquire about: ') . $productName);
$whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$whatsappMsg}";

$inWishlist = in_array((int)$product['id'], $_SESSION['wishlist'] ?? []);
$ratingVal = (float)($product['rating'] ?? 0);
$ratingCount = (int)($product['rating_count'] ?? 0);
$salesCount = (int)($product['sales_count'] ?? 0);
$starSvg = str_replace('fill="none"', 'fill="currentColor"', fnd_icon('star', 17, '', 1.4));
$ratingLabel = $isRtl
    ? 'التقييم ' . number_format($ratingVal, 1) . ' من 5' . ($ratingCount ? "، بناءً على {$ratingCount} تقييم" : '')
    : 'Rated ' . number_format($ratingVal, 1) . ' out of 5' . ($ratingCount ? ", based on {$ratingCount} reviews" : '');
$shareUrl = full_url('/product/' . ($product['slug'] ?? $product['id']));
$shareText = $productName . ' — ' . ($isRtl ? 'تمرنا' : 'Tamrna');
$arrowBack = 'arrow-left'; // mirrored for LTR by .directional-arrow
$sepChar = $isRtl ? '‹' : '›';
$stockState = $isOutOfStock ? 'stock-out' : ($stockQty <= 10 ? 'stock-low' : 'stock-ok');
$stockLabel = $isOutOfStock
    ? ($isRtl ? 'غير متوفر حالياً' : 'Currently unavailable')
    : ($stockQty <= 10 ? ($isRtl ? "كمية محدودة ({$stockQty})" : "Limited stock ({$stockQty})") : ($isRtl ? 'متوفر في المخزون' : 'In stock'));
?>
<div class="product-details-shell">
    <section class="product-detail-main">
        <div class="container">
            <nav class="product-breadcrumb" aria-label="<?= $isRtl ? 'مسار التنقل' : 'Breadcrumb' ?>">
                <a href="<?= url('/') ?>"><?= $isRtl ? 'الرئيسية' : 'Home' ?></a>
                <span><?= $sepChar ?></span>
                <a href="<?= url('/catalog') ?>"><?= $isRtl ? 'المنتجات' : 'Products' ?></a>
                <span><?= $sepChar ?></span>
                <a href="<?= url('/catalog?cat=' . ($product['category_slug'] ?? '')) ?>"><?= esc_html($categoryName) ?></a>
                <span><?= $sepChar ?></span>
                <span><?= esc_html($productName) ?></span>
            </nav>

            <div class="product-detail-layout">
                <!-- Gallery -->
                <div class="product-gallery">
                    <div class="product-gallery-stage">
                        <button type="button" class="product-gallery-zoom" id="gallery-zoom" aria-label="<?= $isRtl ? 'اضغط لتكبير الصورة' : 'Click to enlarge' ?>">
                            <img id="main-product-image" src="<?= asset($mainImage) ?>" alt="<?= esc_attr($productName) ?>">
                            <span><?= fnd_icon('maximize2', 16) ?><?= $isRtl ? 'اضغط لتكبير الصورة' : 'Click to enlarge' ?></span>
                        </button>
                        <?php if (count($gallery) > 1): ?>
                            <span class="product-gallery-count" dir="ltr" id="gallery-count">1 / <?= count($gallery) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($product['is_bestseller'])): ?>
                            <span class="product-bestseller"><?= $isRtl ? 'الأكثر طلباً' : 'Best seller' ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (count($gallery) > 1): ?>
                        <div class="product-thumbnails">
                            <div>
                                <?php foreach ($gallery as $gi => $gImg): ?>
                                    <button type="button" class="product-thumb-item <?= $gi === 0 ? 'is-current' : '' ?>" data-img-src="<?= asset($gImg) ?>" data-index="<?= $gi + 1 ?>" aria-label="<?= $gi + 1 ?>" <?= $gi === 0 ? 'aria-current="true"' : '' ?>>
                                        <img src="<?= asset($gImg) ?>" alt="" loading="lazy">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <article class="product-detail-info">
                    <div class="product-title-row">
                        <?= fnd_icon('tree-palm', 43, 'palm-decoration', 1.1) ?>
                        <div>
                            <span class="product-category"><?= esc_html($categoryName) ?></span>
                            <div class="product-heading">
                                <h1><?= esc_html($productName) ?></h1>
                                <?php if ($ratingVal > 0): ?>
                                    <span class="product-rating" title="<?= esc_attr($ratingLabel) ?>">
                                        <span class="product-rating-stars" style="--rating-fill:<?= min(100, round($ratingVal / 5 * 100)) ?>%" aria-hidden="true">
                                            <span class="product-rating-stars-row"><?= str_repeat($starSvg, 5) ?></span>
                                            <span class="product-rating-stars-clip"><span class="product-rating-stars-row"><?= str_repeat($starSvg, 5) ?></span></span>
                                        </span>
                                        <span><?= number_format($ratingVal, 1) ?></span>
                                        <span class="sr-only"><?= esc_html($ratingLabel) ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="button" class="button button--icon product-share-button" id="product-share-open" aria-label="<?= $isRtl ? 'مشاركة المنتج' : 'Share product' ?>" title="<?= $isRtl ? 'مشاركة المنتج' : 'Share product' ?>"><?= fnd_icon('share2', 19) ?></button>
                    </div>

                    <?php if ($productDesc): ?>
                        <p class="product-description"><?= esc_html($productDesc) ?></p>
                    <?php endif; ?>

                    <?php if ($salesCount > 0 || (!$isOutOfStock && $stockQty <= 50)): ?>
                        <p class="product-signals">
                            <?php if ($salesCount > 0): ?><span><?= fnd_icon('repeat2', 16) ?><?= number_format($salesCount) ?> <?= $isRtl ? 'مرة شراء' : 'purchases' ?></span><?php endif; ?>
                            <?php if (!$isOutOfStock && $stockQty <= 50): ?><span><?= fnd_icon('boxes', 16) ?><?= $isRtl ? 'الكمية المتاحة: ' . $stockQty . ' قطعة' : 'Available: ' . $stockQty . ' pcs' ?></span><?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <div class="product-meta-tags">
                        <?php if ($isPreorder): ?><span class="badge badge--preorder"><?= $isRtl ? 'حجز مسبق للحصاد' : 'Harvest Pre-Order' ?></span><?php endif; ?>
                        <?php if ($isFragile): ?><span class="badge badge--preorder"><?= $isRtl ? 'منتج حساس / زجاجي' : 'Fragile / Artisanal' ?></span><?php endif; ?>
                        <?php if (!empty($badgeText)): ?><span class="badge"><?= esc_html($badgeText) ?></span><?php endif; ?>
                    </div>

                    <div class="product-commerce-row">
                        <div class="product-detail-price">
                            <div class="product-price">
                                <span id="product-display-price"><?= fnd_money($effectivePrice) ?></span>
                                <del id="product-old-price" <?= $oldPrice ? '' : 'class="hidden"' ?>><?= $oldPrice ? fnd_money($oldPrice) : '' ?></del>
                            </div>
                        </div>
                        <div class="product-stock">
                            <span class="<?= $stockState ?>"><i></i><?= $stockLabel ?></span>
                        </div>
                    </div>
                    <span class="muted"><?= $isRtl ? 'شامل ضريبة القيمة المضافة' : 'Includes 15% VAT' ?></span>

                    <?php if ($isPreorder): ?>
                        <div class="product-preorder">
                            <strong><?= $isRtl ? 'معلومات الحجز المسبق لموسم الحصاد' : 'Harvest Season Pre-Order Information' ?></strong>
                            <p><?= $isRtl
                                ? 'هذا الصنف يتم جنيه طازجاً في موسم الحصاد. التاريخ التقديري لبدء الشحن المبرد: <strong>' . esc_html($product['preorder_date'] ?? 'سبتمبر 2026') . '</strong>.'
                                : 'This selection is freshly picked during harvest season. Estimated refrigerated shipping date: <strong>' . esc_html($product['preorder_date'] ?? 'September 2026') . '</strong>.' ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($variants)): ?>
                        <fieldset class="product-weight">
                            <legend><?= $isRtl ? 'اختر حجم العبوة أو التغليف' : 'Select packaging / weight' ?></legend>
                            <div>
                                <?php foreach ($variants as $idx => $v):
                                    $vName = $isRtl ? $v['size_name'] : ($v['size_name_en'] ?? $v['size_name']);
                                    if (!$isRtl) $vName = str_replace(['كجم', 'كيلو'], 'kg', (string)$vName);
                                    $vEff = ($v['sale_price'] ?? 0) > 0 ? (float)$v['sale_price'] : (float)$v['price'];
                                    ?>
                                    <button type="button" class="variant-btn <?= $idx === 0 ? 'is-selected' : '' ?>" aria-pressed="<?= $idx === 0 ? 'true' : 'false' ?>"
                                            onclick="selectVariant(<?= (int)$v['id'] ?>, <?= (float)$v['price'] ?>, <?= ($v['sale_price'] ?? 0) > 0 ? (float)$v['sale_price'] : 'null' ?>, this)"
                                            data-variant-id="<?= (int)$v['id'] ?>">
                                        <strong class="variant-name"><?= esc_html($vName) ?></strong>
                                        <span><?= fnd_money($vEff) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    <?php endif; ?>

                    <section class="product-facts" aria-label="<?= $isRtl ? 'تفاصيل المنتج' : 'Product details' ?>">
                        <?php if (!empty($product['sku'])): ?>
                            <div><?= fnd_icon('barcode', 21) ?><span><?= $isRtl ? 'رمز المنتج' : 'SKU' ?><strong dir="ltr"><?= esc_html($product['sku']) ?></strong></span></div>
                        <?php endif; ?>
                        <?php if (!empty($product['weight'])): ?>
                            <div><?= fnd_icon('package-check', 21) ?><span><?= $isRtl ? 'الوزن' : 'Weight' ?><strong><?= esc_html($isRtl ? $product['weight'] : str_replace(['كجم', 'كيلو'], 'kg', (string)$product['weight'])) ?></strong></span></div>
                        <?php endif; ?>
                        <div><?= fnd_icon('truck', 21) ?><span><?= $isRtl ? 'التوصيل' : 'Delivery' ?><strong><?= $isRtl ? 'شحن مبرد متاح لجميع مناطق المملكة' : 'Chilled shipping across the Kingdom' ?></strong></span></div>
                    </section>

                    <div class="product-buy-row">
                        <div class="quantity-stepper" role="group" aria-label="<?= $isRtl ? 'الكمية' : 'Quantity' ?>">
                            <button type="button" class="button button--icon" onclick="decrementQty()" aria-label="<?= $isRtl ? 'تقليل الكمية' : 'Decrease' ?>"><?= fnd_icon('minus', 18) ?></button>
                            <input type="number" id="product-qty" value="1" min="1" max="<?= max(1, $stockQty) ?>" aria-live="polite">
                            <button type="button" class="button button--icon" onclick="incrementQty()" aria-label="<?= $isRtl ? 'زيادة الكمية' : 'Increase' ?>"><?= fnd_icon('plus', 18) ?></button>
                        </div>
                        <button type="button" id="addToCartBtn" class="button button--primary product-add-button" onclick="triggerAddToCart()" <?= $isOutOfStock ? 'disabled' : '' ?>>
                            <?= fnd_icon('shopping-cart', 20) ?>
                            <?php if ($isOutOfStock): ?>
                                <?= $isRtl ? 'نفد من المخزون' : 'Out of Stock' ?>
                            <?php elseif ($isPreorder): ?>
                                <?= $isRtl ? 'حجز مسبق وإضافة للسلة' : 'Pre-order & add to cart' ?>
                            <?php else: ?>
                                <?= $isRtl ? 'أضف إلى السلة' : 'Add to cart' ?>
                            <?php endif; ?>
                        </button>
                        <div class="product-secondary-actions">
                            <button type="button" class="button button--outline wishlist-button <?= $inWishlist ? 'is-saved' : '' ?>" data-wishlist-id="<?= (int)$product['id'] ?>" aria-pressed="<?= $inWishlist ? 'true' : 'false' ?>"
                                    onclick="tumurnaToggleWishlist(<?= (int)$product['id'] ?>, this)">
                                <?= fnd_icon('heart', 18) ?><span data-wishlist-label><?= $isRtl ? ($inWishlist ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة') : ($inWishlist ? 'Remove from wishlist' : 'Add to wishlist') ?></span>
                            </button>
                            <a class="button button--outline" href="<?= $whatsappUrl ?>" target="_blank" rel="noreferrer">
                                <?= fnd_icon('message-circle', 18) ?><?= $isRtl ? 'طلب فوري واستفسار عبر واتساب' : 'Order / inquire via WhatsApp' ?>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div class="benefit-strip container">
            <ul>
                <li><?= fnd_icon('tree-palm', 35, '', 1.35) ?><div><strong><?= $isRtl ? 'تمور سعودية أصيلة' : 'Authentic Saudi dates' ?></strong><p><?= $isRtl ? 'من خير أرضنا' : 'From our land' ?></p></div></li>
                <li><?= fnd_icon('shield-check', 35, '', 1.35) ?><div><strong><?= $isRtl ? 'جودة نعتني بها' : 'Quality we care for' ?></strong><p><?= $isRtl ? 'اختيارات لمذاق مميز' : 'Choices for a distinct taste' ?></p></div></li>
                <li><?= fnd_icon('truck', 35, '', 1.35) ?><div><strong><?= $isRtl ? 'خيارات توصيل مرنة' : 'Flexible delivery' ?></strong><p><?= $isRtl ? 'وفق منطقتك وطلبك' : 'By region and order' ?></p></div></li>
                <li><?= fnd_icon('gift', 35, '', 1.35) ?><div><strong><?= $isRtl ? 'بطاقات إهداء مميزة' : 'Special gift cards' ?></strong><p><?= $isRtl ? 'شاركوا لحظات الكرم' : 'Share generous moments' ?></p></div></li>
                <li><?= fnd_icon('headphones', 35, '', 1.35) ?><div><strong><?= $isRtl ? 'خدمة عملاء متعاونة' : 'Helpful customer care' ?></strong><p><?= $isRtl ? 'نسعد بتواصلكم' : 'We love hearing from you' ?></p></div></li>
            </ul>
        </div>

        <?php if (!empty($relatedProducts)): ?>
            <section class="product-related container">
                <div class="product-related-heading">
                    <h2><?= $isRtl ? 'منتجات مشابهة' : 'You may also like' ?></h2>
                    <a href="<?= url('/catalog') ?>"><?= $isRtl ? 'عرض الكل' : 'View all' ?><?= fnd_icon($arrowBack, 16, 'directional-arrow') ?></a>
                </div>
                <div class="product-grid">
                    <?php foreach ($relatedProducts as $p): ?>
                        <?php include __DIR__ . '/../components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </section>
</div>

<div class="global-overlay hidden" id="share-overlay"></div>
<div class="dialog hidden" id="share-dialog" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" role="dialog" aria-modal="true" aria-labelledby="share-title">
    <div class="dialog-heading">
        <h2 id="share-title"><?= $isRtl ? 'مشاركة المنتج' : 'Share this product' ?></h2>
        <button type="button" class="button button--icon" data-share-close aria-label="<?= $isRtl ? 'إغلاق' : 'Close' ?>"><?= fnd_icon('x', 20) ?></button>
    </div>
    <p class="dialog-description"><?= esc_html($productName) ?></p>
    <div class="share-targets">
        <a class="share-target" target="_blank" rel="noopener" href="https://wa.me/?text=<?= rawurlencode($shareText . ' ' . $shareUrl) ?>"><?= fnd_icon('message-circle', 20) ?>WhatsApp</a>
        <a class="share-target" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text=<?= rawurlencode($shareText) ?>&url=<?= rawurlencode($shareUrl) ?>"><?= fnd_icon('send', 20) ?>X</a>
        <a class="share-target" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>"><?= fnd_icon('globe', 20) ?>Facebook</a>
        <a class="share-target" href="mailto:?subject=<?= rawurlencode($shareText) ?>&body=<?= rawurlencode($shareUrl) ?>"><?= fnd_icon('mail', 20) ?><?= $isRtl ? 'البريد الإلكتروني' : 'Email' ?></a>
    </div>
    <button type="button" class="share-link" id="share-copy" style="width:100%;border:0;cursor:pointer;text-align:start"><?= fnd_icon('copy', 16) ?><span dir="ltr"><?= esc_html($shareUrl) ?></span></button>
    <button type="button" class="button button--primary share-native hidden" id="share-native"><?= fnd_icon('share2', 18) ?><?= $isRtl ? 'مشاركة عبر الجهاز' : 'Share via device' ?></button>
    <p class="share-feedback" id="share-feedback" role="status"></p>
</div>

<div class="product-lightbox hidden" id="product-lightbox" role="dialog" aria-modal="true">
    <button type="button" class="button button--icon" id="lightbox-close" aria-label="<?= $isRtl ? 'إغلاق' : 'Close' ?>"><?= fnd_icon('x', 22) ?></button>
    <img id="lightbox-image" src="" alt="<?= esc_attr($productName) ?>">
</div>

<script>
    const CURRENCY = <?= json_encode(currency()) ?>;
    let currentSelectedVariantId = <?= !empty($variants) ? (int)$variants[0]['id'] : 'null' ?>;

    function fmtMoney(n) {
        n = Number(n);
        return n.toLocaleString('en-US', { minimumFractionDigits: Number.isInteger(n) ? 0 : 2, maximumFractionDigits: 2 }) + ' ' + CURRENCY;
    }

    function selectVariant(id, price, salePrice, btn) {
        currentSelectedVariantId = id;
        const effective = salePrice !== null ? salePrice : price;
        const displayPrice = document.getElementById('product-display-price');
        const oldPrice = document.getElementById('product-old-price');
        if (displayPrice) displayPrice.innerText = fmtMoney(effective);
        if (oldPrice) {
            if (salePrice !== null) { oldPrice.innerText = fmtMoney(price); oldPrice.classList.remove('hidden'); }
            else { oldPrice.classList.add('hidden'); }
        }
        document.querySelectorAll('.variant-btn').forEach(b => { b.classList.remove('is-selected'); b.setAttribute('aria-pressed', 'false'); });
        btn.classList.add('is-selected');
        btn.setAttribute('aria-pressed', 'true');
    }

    function incrementQty() {
        const input = document.getElementById('product-qty');
        if (!input) return;
        const max = parseInt(input.getAttribute('max') || '999', 10);
        input.value = Math.min(max, parseInt(input.value || 1) + 1);
    }

    function decrementQty() {
        const input = document.getElementById('product-qty');
        if (input && parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
    }

    function triggerAddToCart() {
        const qty = parseInt(document.getElementById('product-qty').value || 1);
        const productId = <?= (int)$product['id'] ?>;
        window.tumurnaAddToCart(productId, currentSelectedVariantId, qty, document.getElementById('addToCartBtn'));
    }

    // Share dialog
    (function () {
        const overlay = document.getElementById('share-overlay');
        const dialog = document.getElementById('share-dialog');
        const feedback = document.getElementById('share-feedback');
        const url = <?= json_encode($shareUrl) ?>;
        const title = <?= json_encode($shareText, JSON_UNESCAPED_UNICODE) ?>;
        const open = () => { overlay.classList.remove('hidden'); dialog.classList.remove('hidden'); feedback.textContent = ''; };
        const close = () => { overlay.classList.add('hidden'); dialog.classList.add('hidden'); };
        document.getElementById('product-share-open').addEventListener('click', open);
        overlay.addEventListener('click', close);
        dialog.querySelector('[data-share-close]').addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        document.getElementById('share-copy').addEventListener('click', () => {
            const done = () => { feedback.textContent = <?= json_encode($isRtl ? 'تم نسخ الرابط' : 'Link copied') ?>; };
            if (navigator.clipboard) navigator.clipboard.writeText(url).then(done).catch(done); else done();
        });
        const nativeBtn = document.getElementById('share-native');
        if (navigator.share) {
            nativeBtn.classList.remove('hidden');
            nativeBtn.addEventListener('click', () => navigator.share({ title, url }).catch(() => {}));
        }
    })();

    // Gallery thumbnails + lightbox
    (function () {
        const main = document.getElementById('main-product-image');
        const count = document.getElementById('gallery-count');
        const total = document.querySelectorAll('.product-thumb-item').length;
        document.querySelectorAll('.product-thumb-item').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                main.src = thumb.getAttribute('data-img-src');
                document.querySelectorAll('.product-thumb-item').forEach(function (t) { t.classList.remove('is-current'); t.removeAttribute('aria-current'); });
                thumb.classList.add('is-current');
                thumb.setAttribute('aria-current', 'true');
                if (count) count.textContent = thumb.getAttribute('data-index') + ' / ' + total;
            });
        });
        const box = document.getElementById('product-lightbox');
        const boxImg = document.getElementById('lightbox-image');
        document.getElementById('gallery-zoom').addEventListener('click', function () {
            boxImg.src = main.src; box.classList.remove('hidden');
        });
        function closeBox() { box.classList.add('hidden'); }
        document.getElementById('lightbox-close').addEventListener('click', closeBox);
        box.addEventListener('click', function (e) { if (e.target === box) closeBox(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeBox(); });
    })();
</script>
