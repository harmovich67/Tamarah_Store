<?php
/**
 * Tamrna Foundation product card
 * Expected variable: $p (product array)
 * Optional: $cardExtraClass (string)
 * Rendered inside a closure so its locals never leak into the including view.
 */
(function (array $p, string $cardExtraClass = '', int $cardRank = 0) {

$isRtl = \App\Core\I18n::isRtl();
$locale = \App\Core\I18n::getLocale();
$isPreorder = !empty($p['is_preorder']);
$isFragile = !empty($p['is_fragile']);
$effectivePrice = ($p['sale_price'] !== null && $p['sale_price'] > 0) ? (float)$p['sale_price'] : (float)$p['price'];
$oldPrice = ($p['sale_price'] !== null && $p['sale_price'] > 0) ? (float)$p['price'] : null;
$discountPercent = ($oldPrice && $oldPrice > $effectivePrice) ? round((($oldPrice - $effectivePrice) / $oldPrice) * 100) : 0;
$productUrl = url('/product/' . ($p['slug'] ?? $p['id']));
$stockQty = (int)($p['stock_quantity'] ?? $p['stock_qty'] ?? 50);
$isOutOfStock = $stockQty <= 0;

$productName = lang_get($p, 'name');
$badgeText = lang_get($p, 'badge');
$cardCategory = (string)lang_get($p, 'category_name', '');
$weightText = (string)($p['weight'] ?? ($isRtl ? '1 كجم' : '1 kg'));
if (!$isRtl) {
    $weightText = strtr($weightText, [
        'كرتون مبرد' => 'chilled carton', 'كرتون' => 'carton', 'مبرد' => 'chilled', 'علبة' => 'box',
        'كجم' => 'kg', 'كيلو' => 'kg', 'جرام' => 'g', 'غرام' => 'g',
    ]);
}
$inWishlist = in_array((int)$p['id'], $_SESSION['wishlist'] ?? []);
$wishTitle = $isRtl ? ($inWishlist ? 'إزالة من المفضلة' : 'إضافة للمفضلة') : ($inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist');
?>
<article class="product-card product-card--storefront <?= esc_attr($cardExtraClass ?? '') ?>" data-product-id="<?= (int)$p['id'] ?>">
    <div class="product-card-media">
        <a class="product-image-link" href="<?= $productUrl ?>">
            <img
                src="<?= asset($p['featured_image'] ?? $p['image'] ?? 'assets/images/home/ajwa.webp') ?>"
                alt="<?= esc_attr($productName) ?>"
                loading="lazy"
            />
        </a>

        <div class="product-badges">
            <?php if ($cardRank > 0): ?>
                <span class="badge rank-badge"><bdi>#<?= $cardRank ?></bdi></span>
            <?php endif; ?>
            <?php if ($isOutOfStock): ?>
                <span class="badge badge--out_of_stock"><?= $isRtl ? 'نفد من المخزون' : 'Out of Stock' ?></span>
            <?php elseif ($isPreorder): ?>
                <span class="badge badge--preorder"><?= $isRtl ? 'حجز مسبق' : 'Pre-Order' ?></span>
            <?php endif; ?>
            <?php if ($discountPercent > 0): ?>
                <span class="badge"><?= $isRtl ? "خصم {$discountPercent}%" : "{$discountPercent}% OFF" ?></span>
            <?php elseif (!empty($badgeText) && !($isPreorder && preg_match('/pre-?\s?order|حجز\s*مسبق/iu', (string)$badgeText))): ?>
                <span class="badge"><?= esc_html($badgeText) ?></span>
            <?php endif; ?>
            <?php if ($isFragile): ?>
                <span class="badge badge--preorder"><?= $isRtl ? 'قابل للكسر' : 'Fragile' ?></span>
            <?php endif; ?>
        </div>

        <button
            type="button"
            class="button button--icon wishlist-button <?= $inWishlist ? 'is-saved' : '' ?>"
            data-wishlist-id="<?= (int)$p['id'] ?>"
            aria-pressed="<?= $inWishlist ? 'true' : 'false' ?>"
            onclick="event.stopPropagation(); tumurnaToggleWishlist(<?= (int)$p['id'] ?>, this);"
            title="<?= $wishTitle ?>"
            aria-label="<?= $wishTitle ?>: <?= esc_attr($productName) ?>"
        >
            <?= fnd_icon('heart', 18) ?>
        </button>
    </div>

    <div class="product-card-body">
        <div class="product-meta">
            <?php if ($cardCategory !== ''): ?><span><?= esc_html($cardCategory) ?></span><?php endif; ?>
            <span><?= esc_html($weightText) ?></span>
        </div>
        <?php
        $ratingVal = (float)($p['rating'] ?? 0);
        $ratingCount = (int)($p['rating_count'] ?? 0);
        $starSvg = str_replace('fill="none"', 'fill="currentColor"', fnd_icon('star', 15, '', 1.4));
        $ratingLabel = $isRtl
            ? 'التقييم ' . number_format($ratingVal, 1) . ' من 5' . ($ratingCount ? "، بناءً على {$ratingCount} تقييم" : '')
            : 'Rated ' . number_format($ratingVal, 1) . ' out of 5' . ($ratingCount ? ", based on {$ratingCount} reviews" : '');
        ?>
        <div class="product-card-title">
            <h3><a href="<?= $productUrl ?>"><?= esc_html($productName) ?></a></h3>
            <?php if ($ratingVal > 0): ?>
                <span class="product-rating" title="<?= esc_attr($ratingLabel) ?>">
                    <span class="product-rating-stars" style="--rating-fill:<?= min(100, round($ratingVal / 5 * 100)) ?>%" aria-hidden="true">
                        <span class="product-rating-stars-row"><?= str_repeat($starSvg, 5) ?></span>
                        <span class="product-rating-stars-clip"><span class="product-rating-stars-row"><?= str_repeat($starSvg, 5) ?></span></span>
                    </span>
                    <span class="sr-only"><?= esc_html($ratingLabel) ?></span>
                </span>
            <?php endif; ?>
        </div>
        <div class="product-price">
            <span><?= fnd_money($effectivePrice) ?></span>
            <?php if ($oldPrice): ?>
                <del><?= fnd_money($oldPrice) ?></del>
            <?php endif; ?>
        </div>
        <?php if ($isOutOfStock): ?>
            <button type="button" class="button button--primary add-to-cart" disabled>
                <span><?= $isRtl ? 'غير متوفر' : 'Unavailable' ?></span>
                <?= fnd_icon('shopping-bag', 18) ?>
            </button>
        <?php else: ?>
            <button
                type="button"
                class="button button--primary add-to-cart"
                onclick="event.preventDefault(); tumurnaAddToCart(<?= (int)$p['id'] ?>, null, 1, this);"
            >
                <span><?= $isPreorder ? ($isRtl ? 'احجز مسبقًا' : 'Pre-order') : ($isRtl ? 'أضف للسلة' : 'Add to cart') ?></span>
                <?= fnd_icon('shopping-bag', 18) ?>
            </button>
        <?php endif; ?>
    </div>
</article>
<?php
})($p, (string)($cardExtraClass ?? ''), (int)($cardRank ?? 0));
