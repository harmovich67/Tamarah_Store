<?php
/**
 * Tamrna Foundation - Catalog / Categories listing
 * All filters are server-side GET params (cat, filter, search, sort, price_min, price_max, availability[]).
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();

$products = $products ?? [];
$categories = $categories ?? [];
$selectedCat = $selected_cat ?? null;
$filterPreorder = !empty($filter_preorder);
$searchQuery = $search_query ?? '';
$sort = $sort ?? 'featured';
$priceMin = $price_min ?? null;
$priceMax = $price_max ?? null;
$availability = $availability ?? [];
$bounds = $price_bounds ?? ['min' => 0, 'max' => 0];

// Resolve active category for the heading
$activeCategory = null;
foreach ($categories as $cat) {
    if ($selectedCat !== null && $selectedCat !== '' && ($selectedCat == $cat['slug'] || $selectedCat == $cat['id'])) {
        $activeCategory = $cat;
        break;
    }
}

if ($filterPreorder) {
    $heroTitle = $isRtl ? 'تشكيلة الحجز المسبق للحصاد' : 'Harvest Pre-Orders Collection';
    $heroText = $isRtl ? 'احجز حصتك من أجود حصاد الموسم قبل وصوله.' : 'Reserve your share of the season’s finest harvest before it arrives.';
    $resultsTitle = $heroTitle;
} elseif ($activeCategory) {
    $heroTitle = lang_get($activeCategory, 'name');
    $heroText = lang_get($activeCategory, 'description') ?: ($isRtl ? 'تصفح تشكيلة هذا القسم من التمور الفاخرة.' : 'Browse this collection of luxury dates.');
    $resultsTitle = $heroTitle;
} else {
    $heroTitle = $isRtl ? 'اكتشف أقسامنا' : 'Discover our categories';
    $heroText = $isRtl ? 'مجموعة مختارة من أجود أنواع التمور السعودية لكل الأذواق والمناسبات.' : 'A curated selection of the finest Saudi dates for every taste and occasion.';
    $resultsTitle = $isRtl ? 'جميع المنتجات' : 'All products';
}
if ($searchQuery !== '') {
    $resultsTitle = ($isRtl ? 'نتائج البحث: ' : 'Search results: ') . $searchQuery;
}

$baseParams = [];
if ($filterPreorder) $baseParams['filter'] = 'preorder';
$catUrl = function (?string $slug) use ($baseParams) {
    $q = $baseParams;
    if ($slug) $q['cat'] = $slug;
    return url('/catalog' . ($q ? '?' . http_build_query($q) : ''));
};

$sortOptions = [
    'featured' => $isRtl ? 'مقترحاتنا' : 'Recommended',
    'bestseller' => $isRtl ? 'الأكثر مبيعًا' : 'Best sellers',
    'rating' => $isRtl ? 'الأعلى تقييمًا' : 'Top rated',
    'price_desc' => $isRtl ? 'السعر: من الأعلى إلى الأقل' : 'Price: high to low',
    'price_asc' => $isRtl ? 'السعر: من الأقل إلى الأعلى' : 'Price: low to high',
];
$availabilityOptions = [
    'available' => $isRtl ? 'متوفر' : 'In stock',
    'preorder' => $isRtl ? 'حجز مسبق' : 'Pre-order',
    'out_of_stock' => $isRtl ? 'غير متوفر' : 'Out of stock',
];
$sliderMin = max(0, (int)$bounds['min']);
$sliderMax = max($sliderMin + 1, (int)$bounds['max']);
$curMin = $priceMin !== null ? (int)$priceMin : $sliderMin;
$curMax = $priceMax !== null ? (int)$priceMax : $sliderMax;
$hasFilters = $priceMin !== null || $priceMax !== null || $availability || $sort !== 'featured' || $searchQuery !== '';
?>
<div class="category-listing-page">
    <section class="category-listing-hero">
        <img src="<?= asset($activeCategory['image'] ?? 'assets/images/home/hero.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%">
        <div class="category-listing-hero-shade"></div>
        <div class="container category-listing-hero-copy">
            <span><?= $isRtl ? 'أصالة في كل قضمة' : 'Authenticity in every bite' ?></span>
            <h1><?= esc_html($heroTitle) ?></h1>
            <p><?= esc_html($heroText) ?></p>
        </div>
    </section>

    <nav class="container category-tabs" aria-label="<?= $isRtl ? 'أقسام المنتجات' : 'Product categories' ?>">
        <a href="<?= $catUrl(null) ?>" class="<?= empty($selectedCat) ? 'is-active' : '' ?>" <?= empty($selectedCat) ? 'aria-current="page"' : '' ?>>
            <?= fnd_icon('layout-grid', 26, '', 1.6) ?>
            <span><?= $isRtl ? 'جميع المنتجات' : 'All products' ?></span>
        </a>
        <?php foreach ($categories as $cat):
            $isActive = ($selectedCat == $cat['slug'] || $selectedCat == $cat['id']);
            ?>
            <a href="<?= $catUrl($cat['slug']) ?>" class="<?= $isActive ? 'is-active' : '' ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
                <span class="category-tab-icon"><img src="<?= asset($cat['image'] ?? 'assets/images/home/ajwa.webp') ?>" alt="" loading="lazy"></span>
                <span><?= esc_html(lang_get($cat, 'name')) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="container category-listing-layout">
        <aside class="category-filter-panel" id="catalog-filters" aria-labelledby="category-filter-title">
            <div class="category-filter-heading">
                <?= fnd_icon('sliders-horizontal', 20) ?>
                <h2 id="category-filter-title"><?= $isRtl ? 'تصفية النتائج' : 'Filter results' ?></h2>
                <button type="button" class="button button--icon catalog-filter-close" id="catalog-filter-close" aria-label="<?= $isRtl ? 'إغلاق' : 'Close' ?>"><?= fnd_icon('x', 18) ?></button>
            </div>
            <form class="category-filter-controls" id="catalog-filter-form" method="get" action="<?= url('/catalog') ?>">
                <?php if ($selectedCat): ?><input type="hidden" name="cat" value="<?= esc_attr($selectedCat) ?>"><?php endif; ?>
                <?php if ($filterPreorder): ?><input type="hidden" name="filter" value="preorder"><?php endif; ?>
                <?php if ($searchQuery !== ''): ?><input type="hidden" name="search" value="<?= esc_attr($searchQuery) ?>"><?php endif; ?>

                <section class="category-filter-section category-filter-sort">
                    <fieldset class="radio-group">
                        <legend><?= $isRtl ? 'ظهور حسب' : 'Sort by' ?></legend>
                        <?php foreach ($sortOptions as $val => $label): ?>
                            <label class="choice"><input type="radio" name="sort" value="<?= $val ?>" <?= $sort === $val ? 'checked' : '' ?> data-autosubmit><?= $label ?></label>
                        <?php endforeach; ?>
                    </fieldset>
                </section>

                <section class="category-filter-section">
                    <h3><?= $isRtl ? 'السعر (' . currency() . ')' : 'Price (' . currency() . ')' ?></h3>
                    <input class="category-price-range" id="price-range" type="range" min="<?= $sliderMin ?>" max="<?= $sliderMax ?>" value="<?= min($sliderMax, $curMax) ?>" aria-label="<?= $isRtl ? 'إلى' : 'To' ?>">
                    <div class="category-price-inputs">
                        <label for="price-min"><span><?= $isRtl ? 'من' : 'From' ?></span>
                            <input id="price-min" type="number" min="0" step="1" name="price_min" class="input" value="<?= $priceMin !== null ? (int)$priceMin : '' ?>" placeholder="<?= $sliderMin ?>">
                        </label>
                        <label for="price-max"><span><?= $isRtl ? 'إلى' : 'To' ?></span>
                            <input id="price-max" type="number" min="0" step="1" name="price_max" class="input" value="<?= $priceMax !== null ? (int)$priceMax : '' ?>" placeholder="<?= $sliderMax ?>">
                        </label>
                    </div>
                </section>

                <section class="category-filter-section">
                    <h3><?= $isRtl ? 'توفر المنتج' : 'Availability' ?></h3>
                    <div class="category-filter-options">
                        <?php foreach ($availabilityOptions as $val => $label): ?>
                            <label class="choice" for="av-<?= $val ?>">
                                <input id="av-<?= $val ?>" type="checkbox" name="availability[]" value="<?= $val ?>" <?= in_array($val, $availability, true) ? 'checked' : '' ?> data-autosubmit>
                                <span><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <noscript><button type="submit" class="button button--primary category-filter-reset"><?= $isRtl ? 'تطبيق الفلاتر' : 'Apply filters' ?></button></noscript>
                <a href="<?= $catUrl($selectedCat ?: null) ?>" class="button button--outline category-filter-reset <?= $hasFilters ? '' : 'is-idle' ?>">
                    <?= fnd_icon('rotate-ccw', 16) ?><?= $isRtl ? 'إعادة ضبط الفلاتر' : 'Reset filters' ?>
                </a>
            </form>
        </aside>

        <div class="category-results">
            <div class="category-results-heading">
                <div>
                    <span><?= count($products) ?> <?= $isRtl ? 'منتج' : 'products' ?></span>
                    <h2><?= esc_html($resultsTitle) ?></h2>
                    <p><?= $isRtl ? 'استكشف جميع منتجات تمرنا المختارة للضيافة والإهداء.' : 'Explore Tamrna’s selection for hospitality and gifting.' ?></p>
                </div>
                <div class="category-results-actions">
                    <button type="button" class="button button--outline category-mobile-filter" id="catalog-filter-toggle">
                        <?= fnd_icon('sliders-horizontal', 18) ?><?= $isRtl ? 'الفلاتر' : 'Filters' ?>
                    </button>
                    <label>
                        <span class="sr-only"><?= $isRtl ? 'ترتيب المنتجات' : 'Sort products' ?></span>
                        <select class="input select" id="catalog-sort-select" aria-label="<?= $isRtl ? 'ترتيب المنتجات' : 'Sort products' ?>">
                            <?php foreach ($sortOptions as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="category-view-toggle" role="group" aria-label="<?= $isRtl ? 'طريقة العرض' : 'View mode' ?>">
                        <button type="button" class="button button--icon is-active" data-view="grid" aria-pressed="true" aria-label="<?= $isRtl ? 'عرض شبكي' : 'Grid view' ?>"><?= fnd_icon('grid2x2', 18) ?></button>
                        <button type="button" class="button button--icon" data-view="list" aria-pressed="false" aria-label="<?= $isRtl ? 'عرض قائمة' : 'List view' ?>"><?= fnd_icon('list', 18) ?></button>
                    </div>
                </div>
            </div>

            <?php if (!empty($products)): ?>
                <div class="product-grid category-products category-products--grid" id="catalog-products">
                    <?php foreach ($products as $p): ?>
                        <?php include __DIR__ . '/../components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="catalog-empty">
                    <?= fnd_icon('search', 40, '', 1.4) ?>
                    <h3><?= $isRtl ? 'لا توجد أصناف تطابق معايير البحث الحالية' : 'No products matched your search' ?></h3>
                    <p><?= $isRtl ? 'جرّب إزالة بعض الفلاتر أو تصفح كافة الأقسام لاكتشاف تشكيلتنا.' : 'Try removing some filters or browse all categories to discover our selection.' ?></p>
                    <a href="<?= url('/catalog') ?>" class="button button--primary" style="margin-top:1rem"><?= $isRtl ? 'عرض كافة التمور' : 'View all dates' ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
(function () {
    var form = document.getElementById('catalog-filter-form');
    var submit = function () { if (form.requestSubmit) form.requestSubmit(); else form.submit(); };

    // Filters apply immediately (radios / checkboxes), price after a short pause
    form.querySelectorAll('[data-autosubmit]').forEach(function (el) { el.addEventListener('change', submit); });
    var priceTimer = null;
    var minIn = document.getElementById('price-min'), maxIn = document.getElementById('price-max'), range = document.getElementById('price-range');
    function schedule() { clearTimeout(priceTimer); priceTimer = setTimeout(submit, 700); }
    [minIn, maxIn].forEach(function (el) { el.addEventListener('change', submit); });
    range.addEventListener('input', function () { maxIn.value = range.value; });
    range.addEventListener('change', schedule);

    // The results-bar select mirrors the "sort by" radios
    document.getElementById('catalog-sort-select').addEventListener('change', function (e) {
        var radio = form.querySelector('input[name="sort"][value="' + e.target.value + '"]');
        if (radio) { radio.checked = true; submit(); }
    });

    // Grid / list view (remembered per browser)
    var grid = document.getElementById('catalog-products');
    function setView(view) {
        if (grid) {
            grid.classList.toggle('category-products--list', view === 'list');
            grid.classList.toggle('category-products--grid', view !== 'list');
        }
        document.querySelectorAll('.category-view-toggle [data-view]').forEach(function (b) {
            var on = b.getAttribute('data-view') === view;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        try { localStorage.setItem('tamrna_catalog_view', view); } catch (e) {}
    }
    document.querySelectorAll('.category-view-toggle [data-view]').forEach(function (btn) {
        btn.addEventListener('click', function () { setView(btn.getAttribute('data-view')); });
    });
    try { var saved = localStorage.getItem('tamrna_catalog_view'); if (saved) setView(saved); } catch (e) {}

    // Mobile filters panel
    var panel = document.getElementById('catalog-filters');
    var open = function () { panel.classList.add('is-open'); document.body.style.overflow = 'hidden'; };
    var close = function () { panel.classList.remove('is-open'); document.body.style.overflow = ''; };
    document.getElementById('catalog-filter-toggle').addEventListener('click', open);
    document.getElementById('catalog-filter-close').addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
