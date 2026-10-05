<?php
/**
 * Tamrna Foundation - Wishlist View
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();
$products = $products ?? [];
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
?>
<section class="commerce-page wishlist-page">
    <div class="container">
        <header class="commerce-heading">
            <span class="commerce-kicker"><?= fnd_icon('heart', 16) ?><?= $isRtl ? 'مختاراتك' : 'Your picks' ?></span>
            <h1><?= $isRtl ? 'قائمة المفضلة' : 'My Wishlist' ?></h1>
            <p><?= $isRtl ? 'الأصناف التي قمت بحفظها للرجوع إليها أو إهدائها لاحقاً' : 'Dates and gift selections you saved for later' ?></p>
            <a class="commerce-back" href="<?= url('/catalog') ?>"><?= $isRtl ? 'تصفح الأقسام' : 'Browse dates' ?></a>
        </header>

        <?php if (!empty($products)): ?>
            <div class="product-grid catalog-grid">
                <?php foreach ($products as $p): ?>
                    <?php include __DIR__ . '/../components/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="account-gate">
                <?= fnd_icon('heart', 48, '', 1.4) ?>
                <h1><?= $isRtl ? 'قائمة المفضلة فارغة حالياً' : 'Your wishlist is currently empty' ?></h1>
                <p><?= $isRtl ? 'يمكنك الضغط على رمز القلب الموجود على أي صنف من التمور لحفظه في قائمتك المفضلة هنا.' : 'Tap the heart icon on any product to save it to your wishlist.' ?></p>
                <a href="<?= url('/catalog') ?>" class="button button--primary"><?= $isRtl ? 'استكشاف التمور' : 'Explore dates' ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
