<?php
/**
 * Tumurna Luxury Dates - Dynamic Homepage
 * Controlled via Admin Home CMS & Slider Manager (/admin/home-sections)
 */
$locale = \App\Core\I18n::getLocale();
$pageCss = ['home']; // page layer: foundation-home.css
$isRtl = \App\Core\I18n::isRtl();

$banners = $banners ?? [];
$categories = $categories ?? [];
$products = $products ?? [];
$preorderProducts = $preorderProducts ?? [];
$bestsellers = $bestsellers ?? [];
$homeSections = $homeSections ?? [];

// Default fallback reviews if not configured in CMS
$defaultReviewsAr = [
    [
        'name' => 'د. خالد السعدون',
        'city' => 'الرياض',
        'rating' => 5,
        'comment' => 'عجوة العالية من أروع ما تذوقت، طرية وحبات منتقاة ونظافة تامة. تغليف الشحن المبرد ممتاز ووصلت في نفس اليوم.',
        'product_name' => 'عجوة المدينة المنورة العالية الملكية'
    ],
    [
        'name' => 'أ. منيرة القحطاني',
        'city' => 'جدة',
        'rating' => 5,
        'comment' => 'صندوق تمرنا الخشبي فخم جداً ومناسب للإهداء الراقي. أهديته لوالدي في العيد وكان مبهوراً بدقة التنسيق وجودة التمور.',
        'product_name' => 'صندوق تمرنا الملكي الخشبي الفاخر'
    ],
    [
        'name' => 'م. سلطان الشمري',
        'city' => 'الدمام',
        'rating' => 5,
        'comment' => 'السكري المفتل مقرمش شقار ولذاذة لا توصف. خدمة العملاء تعاملهم راقي ومحترف، أصبح متجر تمرنا خياري الدائم.',
        'product_name' => 'سكري القصيم مفتل درجة أولى'
    ]
];

$defaultReviewsEn = [
    [
        'name' => 'Dr. Khaled Al-Saadoun',
        'city' => 'Riyadh',
        'rating' => 5,
        'comment' => 'Al-Aliyah Ajwa is the finest I have tasted—exceptionally soft, hand-selected, and pristine. The cold shipping packaging arrived same-day in perfect condition.',
        'product_name' => 'Royal Madinah Al-Aliyah Ajwa'
    ],
    [
        'name' => 'Mounira Al-Qahtani',
        'city' => 'Jeddah',
        'rating' => 5,
        'comment' => 'The wooden gift trunk is truly prestigious and perfect for royal gifting. Presented it to my father on Eid and he was amazed by the quality and curation.',
        'product_name' => 'Tumurna Royal Wooden Gift Trunk'
    ],
    [
        'name' => 'Eng. Sultan Al-Shammari',
        'city' => 'Dammam',
        'rating' => 5,
        'comment' => 'The Muftal Sukari is wonderfully crisp with golden sweetness. Exceptional customer care and prompt service. Tumurna is now our family favorite.',
        'product_name' => 'Qassim Muftal Sukari - Luxury Box'
    ]
];

$defaultFeaturesAr = [
    [
        'icon' => 'truck',
        'title' => 'شحن مبرد فائق السرعة',
        'desc' => 'أسطول سيارات شحن مبردة مجهزة بدرجات حرارة مثالية لحفظ قوام ورطوبة وجودة التمور الطازجة.'
    ],
    [
        'icon' => 'award',
        'title' => 'ضمان الجودة الملكية ١٠٠٪',
        'desc' => 'تمور نخب أول منتقاة حبة بحبة ومفحوصة مخبرياً وفق معايير الهيئة العامة للغذاء والدواء.'
    ],
    [
        'icon' => 'gift',
        'title' => 'تغليف إهداء فاخر',
        'desc' => 'صناديق خشبية ومخملية مصممة خصيصاً للمناسبات الملكية، والأعياد، وحفلات الاستقبال.'
    ],
    [
        'icon' => 'store',
        'title' => 'فروع في الرياض وجدة والمدينة',
        'desc' => 'إمكانية الاستلام المباشر أو تذوق التمور الفاخرة عبر صالات عرضنا الراقية المنتشرة بالمملكة.'
    ]
];

$defaultFeaturesEn = [
    [
        'icon' => 'truck',
        'title' => 'Express Refrigerated Fleet',
        'desc' => 'Temperature-controlled refrigerated vehicles preserving moisture, texture, and prime freshness across KSA.'
    ],
    [
        'icon' => 'award',
        'title' => '100% Royal Grade Certified',
        'desc' => 'Prime hand-picked dates, lab tested and certified under Saudi Food & Drug Authority standards.'
    ],
    [
        'icon' => 'gift',
        'title' => 'Luxury Royal Gift Packaging',
        'desc' => 'Artisanal wooden and velvet presentation boxes crafted for VIP gifting, Eid, and prestigious receptions.'
    ],
    [
        'icon' => 'store',
        'title' => 'Boutiques in Riyadh, Jeddah & Madinah',
        'desc' => 'Complimentary in-boutique tasting lounges and express local branch pickup throughout Saudi Arabia.'
    ]
];

// ---- Layout state -------------------------------------------------------
$heroSection = null;
$featuresSection = null;
$bodySections = [];
foreach ($homeSections as $sec) {
    if ($sec['section_key'] === 'hero') {
        if (!empty($banners)) $heroSection = $sec;
        continue;
    }
    if ($sec['section_key'] === 'features' && $featuresSection === null) {
        $featuresSection = $sec;
        continue;
    }
    $bodySections[] = $sec;
}
// The transparent header only makes sense when a hero is rendered
$immersiveHeader = $heroSection !== null;

$renderFeatureStrip = function (array $sec) use ($isRtl, $defaultFeaturesAr, $defaultFeaturesEn): void {
    $secSettings = $sec['settings'] ?? [];
    $featuresList = (!empty($secSettings['cards']) && is_array($secSettings['cards']))
        ? $secSettings['cards']
        : ($isRtl ? $defaultFeaturesAr : $defaultFeaturesEn);
    $iconMap = ['truck' => 'truck', 'award' => 'shield-check', 'gift' => 'gift', 'store' => 'tree-palm'];
    ?>
    <div class="benefit-strip container">
        <ul style="grid-template-columns: repeat(<?= max(1, min(5, count($featuresList))) ?>, 1fr)">
            <?php foreach ($featuresList as $feat):
                $fTitle = lang_get($feat, 'title', ($feat['title'] ?? ($feat['title_ar'] ?? '')));
                $fDesc = lang_get($feat, 'desc', ($feat['desc'] ?? ($feat['desc_ar'] ?? '')));
                $iconName = $feat['icon'] ?? 'award';
                $iconName = $iconMap[$iconName] ?? $iconName;
                ?>
                <li>
                    <?= fnd_icon($iconName, 40, '', 1.35) ?>
                    <div>
                        <strong><?= esc_html($fTitle) ?></strong>
                        <p class="clamp-2"><?= esc_html($fDesc) ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
};

$palm = fn() => fnd_icon('tree-palm', 24, 'palm-decoration', 1.1);
$arrow = 'arrow-left'; // mirrored for LTR by .directional-arrow
?>

<?php /* ===================== 1. HERO (CMS banners) ===================== */ ?>
<?php if ($heroSection !== null): ?>
    <section id="tumurna-hero-slider" data-header-hero="true" class="home-hero <?= count($banners) < 2 ? 'home-hero--single' : '' ?>" aria-labelledby="home-title">
        <div class="home-hero-slides">
            <?php foreach ($banners as $index => $banner):
                $bTitle = lang_get($banner, 'title');
                $bSubtitle = lang_get($banner, 'subtitle');
                $bBadge = lang_get($banner, 'badge');
                $bCta = lang_get($banner, 'cta', ($isRtl ? 'تسوق الآن' : 'Shop Now'));
                ?>
                <div class="home-hero-slide tumurna-hero-slide <?= $index === 0 ? 'is-active' : '' ?>">
                    <img class="home-hero-image" src="<?= asset($banner['image']) ?>" alt="<?= esc_attr($bTitle) ?>" <?= $index === 0 ? '' : 'loading="lazy"' ?>>
                    <div class="home-hero-shade"></div>
                    <div class="container hero-content">
                        <div class="hero-copy">
                            <?php if (!empty($bBadge)): ?>
                                <span class="hero-slide-badge"><?= fnd_icon('sparkles', 14) ?><?= esc_html($bBadge) ?></span>
                            <?php endif; ?>
                            <?= $index === 0 ? '<h1 id="home-title">' : '<h2 class="hero-h">' ?><?= esc_html($bTitle) ?><?= $index === 0 ? '</h1>' : '</h2>' ?>
                            <?php if (!empty($bSubtitle)): ?>
                                <p class="hero-description"><?= esc_html($bSubtitle) ?></p>
                            <?php endif; ?>
                            <a class="button button--secondary home-cta" href="<?= url($banner['link'] ?? '/catalog') ?>">
                                <?= esc_html($bCta) ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a class="hero-story" href="#our-story">
            <span class="story-circle"><?= fnd_icon('arrow-up-right', 24) ?></span>
            <span>
                <strong><?= $isRtl ? 'اكتشف الأصالة' : 'Discover authenticity' ?></strong>
                <span><?= $isRtl ? 'رحلة التمر من أرضنا' : 'The journey of dates from our land' ?></span>
            </span>
        </a>

        <div class="home-hero-controls">
            <button type="button" class="button button--icon home-hero-nav tumurna-hero-prev" aria-label="<?= $isRtl ? 'الشريحة السابقة' : 'Previous Slide' ?>"><?= fnd_icon($isRtl ? 'chevron-right' : 'chevron-left', 20) ?></button>
            <?php foreach ($banners as $i => $banner): ?>
                <button type="button" class="home-hero-dot tumurna-hero-dot <?= $i === 0 ? 'is-active' : '' ?>" aria-label="<?= $i + 1 ?>"></button>
            <?php endforeach; ?>
            <button type="button" class="button button--icon home-hero-nav tumurna-hero-next" aria-label="<?= $isRtl ? 'الشريحة التالية' : 'Next Slide' ?>"><?= fnd_icon($isRtl ? 'chevron-left' : 'chevron-right', 20) ?></button>
        </div>
    </section>
    <?php if ($featuresSection !== null) $renderFeatureStrip($featuresSection); ?>
<?php endif; ?>

<?php if ($heroSection === null && $featuresSection !== null) $renderFeatureStrip($featuresSection); ?>

<div class="home-content container">

    <?php /* ===================== static gift banner ===================== */ ?>
    <section class="home-gift-section">
        <article class="promo-banner promo-banner--full-background home-gift-banner">
            <div class="promo-copy">
                <span class="eyebrow"><?= $isRtl ? 'هدية من تراثنا العريق' : 'A gift from our rich heritage' ?></span>
                <h3><?= $isRtl ? 'بطاقات إهداء ومطبوعة' : 'Gift cards, digital & printed' ?></h3>
                <p><?= $isRtl ? 'لأن بعض المشاعر تُهدى .. تمرنا خياركم الأمثل' : 'Because some feelings are meant to be gifted — Tamrna is your best choice' ?></p>
                <a class="button button--secondary" href="<?= url('/gift-cards') ?>"><?= $isRtl ? 'اكتشف بطاقات الإهداء' : 'Discover gift cards' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?></a>
            </div>
            <div class="promo-image">
                <img src="<?= asset('assets/images/home/gifting.webp') ?>" alt="<?= $isRtl ? 'علبة تمور فاخرة ودلة عربية' : 'Luxury dates box and Arabic coffee pot' ?>" loading="lazy" width="1536" height="1024">
            </div>
        </article>
    </section>

    <?php foreach ($bodySections as $sec): ?>
        <?php
        $secKey = $sec['section_key'];
        $secSettings = $sec['settings'] ?? [];
        $secTitle = lang_get($sec, 'title');
        $secSubtitle = lang_get($sec, 'subtitle');
        $secBadge = lang_get($sec, 'badge');
        $secBtnText = lang_get($sec, 'button_text');
        ?>

        <?php /* ===================== 2. CATEGORIES ===================== */ ?>
        <?php if ($secKey === 'categories' && !empty($categories)): ?>
            <section class="home-section" id="categories">
                <div class="section-title section-title--center section-title--palms">
                    <div class="section-heading-line">
                        <?= $palm() ?>
                        <h2><?= esc_html($secTitle ?: ($isRtl ? 'الأقسام الرئيسية للتمور' : 'Main Date Categories')) ?></h2>
                        <?= $palm() ?>
                    </div>
                    <p><?= esc_html($secSubtitle ?: ($isRtl ? 'اكتشف عالم التمور الفاخرة' : 'Discover the world of luxury dates')) ?></p>
                </div>

                <div class="category-collection">
                    <div class="home-categories">
                        <?php foreach ($categories as $cat):
                            $cName = lang_get($cat, 'name');
                            $cCount = (int)($cat['count'] ?? 0);
                            ?>
                            <a class="category-card category-card--portrait" href="<?= url('/catalog?cat=' . $cat['slug']) ?>">
                                <img src="<?= asset($cat['image'] ?? 'assets/images/home/ajwa.webp') ?>" alt="<?= esc_attr($cName) ?>" loading="lazy">
                                <div>
                                    <span>
                                        <h3><?= esc_html($cName) ?></h3>
                                        <span class="category-count"><?= $cCount ?> <?= $isRtl ? 'منتج' : 'items' ?></span>
                                    </span>
                                    <?= fnd_icon($arrow, 23, 'directional-arrow') ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="category-scroll-controls">
                        <button type="button" class="button button--icon" data-cat-scroll="-1" disabled aria-label="<?= $isRtl ? 'الأقسام السابقة' : 'Previous categories' ?>"><?= fnd_icon($isRtl ? 'chevron-right' : 'chevron-left', 24) ?></button>
                        <button type="button" class="button button--icon" data-cat-scroll="1" aria-label="<?= $isRtl ? 'الأقسام التالية' : 'Next categories' ?>"><?= fnd_icon($isRtl ? 'chevron-left' : 'chevron-right', 24) ?></button>
                    </div>
                </div>

                <div class="home-section-action">
                    <a class="button button--outline home-cta" href="<?= url($sec['button_url'] ?: '/catalog') ?>">
                        <?= esc_html($secBtnText ?: ($isRtl ? 'عرض كافة الأقسام' : 'View All Categories')) ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                    </a>
                </div>
            </section>

            <?php /* static heritage banner */ ?>
            <section id="our-story" class="home-story-section">
                <article class="promo-banner promo-banner--full-background home-heritage-banner">
                    <div class="promo-copy">
                        <h3><?= $isRtl ? "من عمق التاريخ\nإلى حاضرنا" : "From the depth of history\nto our present" ?></h3>
                        <p><?= $isRtl ? 'ليس مجرد غذاء .. بل هو إرث وثقافة وهوية متجذرة في أرض المملكة منذ آلاف السنين' : 'Not just food — an heritage, a culture and an identity rooted in the Kingdom for thousands of years' ?></p>
                        <a class="button button--secondary" href="<?= url('/about') ?>"><?= $isRtl ? 'تعرّف على قصتنا' : 'Learn our story' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?></a>
                    </div>
                    <div class="promo-image">
                        <img src="<?= asset('assets/images/home/heritage.webp') ?>" alt="<?= $isRtl ? 'عمارة نجدية وواحة نخيل سعودية' : 'Najdi architecture and a Saudi palm oasis' ?>" loading="lazy">
                    </div>
                </article>
            </section>

        <script>
(function () {
    var scroller = document.querySelector('.category-collection .home-categories');
    var buttons = document.querySelectorAll('[data-cat-scroll]');
    if (!scroller || !buttons.length) return;
    var rtl = document.documentElement.dir === 'rtl';
    var prev = document.querySelector('[data-cat-scroll="-1"]');
    scroller.addEventListener('scroll', function () { prev.disabled = Math.abs(scroller.scrollLeft) < 1; }, { passive: true });
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var step = parseInt(btn.getAttribute('data-cat-scroll'), 10);
            var max = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
            var next = Math.max(0, Math.min(max, Math.abs(scroller.scrollLeft) + step * scroller.clientWidth * 0.75));
            scroller.scrollTo({ left: rtl ? -next : next, behavior: 'smooth' });
        });
    });
})();
</script>

<?php /* ===================== 3. FEATURED PRODUCTS ===================== */ ?>
        <?php elseif ($secKey === 'featured_products' && !empty($products)): ?>
            <section class="home-section" id="featured">
                <div class="section-title section-title--center section-title--palms">
                    <div class="section-heading-line">
                        <?= $palm() ?>
                        <h2><?= esc_html($secTitle ?: ($isRtl ? 'منتجات مميزة ومقترحة' : 'Featured Products')) ?></h2>
                        <?= $palm() ?>
                    </div>
                    <p><?= esc_html($secBadge ?: ($isRtl ? 'مختارة بعناية' : 'Carefully selected')) ?></p>
                </div>

                <div class="home-product-grid">
                    <?php foreach ($products as $p): ?>
                        <?php include __DIR__ . '/../components/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <div class="home-section-action">
                    <a class="button button--outline home-cta" href="<?= url($sec['button_url'] ?: '/catalog') ?>">
                        <?= esc_html($secBtnText ?: ($isRtl ? 'تصفح كافة الأصناف' : 'Explore All Dates')) ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                    </a>
                </div>
            </section>

            <?php /* ---- editorial blocks that follow the featured products (design sections) ---- */ ?>
            <section class="home-quality" aria-label="<?= $isRtl ? 'جودة تعتز بها مزارعنا السعودية' : 'Quality our Saudi farms are proud of' ?>">
                <div class="quality-scene">
                    <article class="promo-banner promo-banner--full-background home-farm-banner">
                        <div class="promo-copy">
                            <h3><?= $isRtl ? "جودة تعتز بها\nمزارعنا السعودية" : "Quality our Saudi farms\nare proud of" ?></h3>
                            <p><?= $isRtl ? 'نحرص على اختيار أجود التمور من مزارع محلية، بعناية تمنح كل حبة مذاقها الأصيل.' : 'We carefully choose the finest dates from local farms, giving every date its authentic taste.' ?></p>
                            <a class="button button--secondary" href="<?= url('/about#farms') ?>"><?= $isRtl ? 'اكتشف مزارعنا' : 'Discover our farms' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?></a>
                        </div>
                        <div class="promo-image"><img src="<?= asset('assets/images/home/heritage.webp') ?>" alt="" loading="lazy"></div>
                    </article>
                    <img class="quality-date-image" src="<?= asset('assets/images/home/medjool.webp') ?>" alt="" loading="lazy">
                </div>
            </section>

            <?php if (!empty($bestsellers)): ?>
                <section class="home-bestsellers home-section">
                    <aside class="home-editorial">
                        <?= fnd_icon('tree-palm', 24, 'palm-decoration', 1.1) ?>
                        <h2><?= $isRtl ? "الأصالة\nفي كل حبة" : "Authenticity\nin every date" ?></h2>
                        <p><?= $isRtl ? 'تمور نختارها للضيافة، تجمع المذاق الأصيل وعناية الاختيار.' : 'Dates chosen for hospitality, combining authentic taste and careful selection.' ?></p>
                        <a href="#featured" class="button button--secondary home-cta"><?= $isRtl ? 'تسوق الآن' : 'Shop now' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?></a>
                    </aside>
                    <div class="home-bestsellers-products">
                        <div class="section-title section-title--center section-title--palms">
                            <div class="section-heading-line">
                                <?= $palm() ?>
                                <h2><?= $isRtl ? 'الأكثر طلبًا' : 'Best sellers' ?></h2>
                                <?= $palm() ?>
                            </div>
                            <p><?= $isRtl ? 'اختيارات ضيافتنا المفضلة' : 'Our favourite hospitality picks' ?></p>
                        </div>
                        <div class="home-compact-grid">
                            <?php foreach ($bestsellers as $bi => $p): ?>
                                <?php $cardExtraClass = 'product-card--compact'; $cardRank = $bi + 1; include __DIR__ . '/../components/product_card.php'; ?>
                            <?php endforeach; unset($cardExtraClass, $cardRank); ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php
            $giftBoxCat = null;
            foreach ($categories as $c) { if (($c['slug'] ?? '') === 'gift-boxes') { $giftBoxCat = $c; break; } }
            ?>
            <section class="home-packaging-section">
                <article class="promo-banner promo-banner--full-background home-packaging-banner">
                    <div class="promo-copy">
                        <h3><?= $isRtl ? "بوكسات التمور الفاخرة\nمناسبة لكل لحظاتكم" : "Luxury date boxes\nfor every moment" ?></h3>
                        <p><?= $isRtl ? 'بوكسات مصممة بعناية، تعكس الهوية السعودية في جميع المناسبات.' : 'Carefully designed boxes that reflect Saudi identity for every occasion.' ?></p>
                        <a class="button button--secondary" href="<?= url($giftBoxCat ? '/catalog?cat=gift-boxes' : '/catalog') ?>"><?= $isRtl ? 'تسوق البوكسات' : 'Shop the boxes' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?></a>
                    </div>
                    <div class="promo-image"><img src="<?= asset('assets/images/home/gifting.webp') ?>" alt="" loading="lazy"></div>
                </article>
                <div class="packaging-wordmark">
                    <span class="brand-wordmark brand-wordmark--light" role="img" aria-label="تمرنا Tamrna"><img alt="" src="<?= asset('assets/brand/tamrna-logo-light.svg') ?>"></span>
                </div>
            </section>

        <?php /* ===================== 4. PRE-ORDER BANNER ===================== */ ?>
        <?php elseif ($secKey === 'preorder'): ?>
            <section class="home-section">
                <article class="promo-banner promo-banner--full-background home-preorder-banner">
                    <?php if (!empty($sec['image'])): ?>
                        <div class="promo-image"><img src="<?= asset($sec['image']) ?>" alt="" loading="lazy"></div>
                    <?php endif; ?>
                    <div class="promo-copy">
                        <span class="eyebrow"><?= esc_html($secBadge ?: ($isRtl ? 'حجز حصاد موسم ٢٠٢٦ 🌴' : 'Season 2026 Harvest Pre-Order 🌴')) ?></span>
                        <h3><?= esc_html($secTitle ?: ($isRtl ? 'احجز رطب المدينة والقصيم الطازج قبل اكتمال الحصاد' : 'Reserve Fresh Madinah & Qassim Ruthab Before Harvest Ends')) ?></h3>
                        <p><?= esc_html($secSubtitle ?: ($isRtl ? 'نضمن لك أولى قطاف النخيل المبارك، مفرز ومعبأ مبرداً ليصل إلى بابك فور الجني بنفس اليوم بنضارته الفائقة.' : 'Guaranteed first pick of blessed palm groves, packaged and delivered chilled right to your doorstep on harvest day.')) ?></p>
                        <a class="button button--secondary" href="<?= url($sec['button_url'] ?: '/catalog?filter=preorder') ?>">
                            <?= esc_html($secBtnText ?: ($isRtl ? 'استعراض أصناف الحجز المسبق' : 'Browse Pre-Order Items')) ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                        </a>
                    </div>
                    <?php if (!empty($preorderProducts)): ?>
                        <div class="home-preorder-products">
                            <?php foreach (array_slice($preorderProducts, 0, 2) as $pp):
                                $ppName = lang_get($pp, 'name');
                                ?>
                                <div class="home-preorder-card">
                                    <div>
                                        <span class="badge badge--preorder"><?= $isRtl ? 'حجز مسبق' : 'Pre-Order' ?></span>
                                        <strong><?= fnd_money($pp['price']) ?></strong>
                                    </div>
                                    <h3><?= esc_html($ppName) ?></h3>
                                    <a class="button button--secondary" href="<?= url('/product/' . $pp['slug']) ?>"><?= $isRtl ? 'تفاصيل الحجز' : 'Details' ?></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

        <?php /* ===================== 5. FEATURES (when no hero strip) ===================== */ ?>
        <?php elseif ($secKey === 'features'): ?>
            <?php $renderFeatureStrip($sec); ?>

        <?php /* ===================== 6. TESTIMONIALS ===================== */ ?>
        <?php elseif ($secKey === 'testimonials'): ?>
            <?php
            $reviewsList = (!empty($secSettings['reviews']) && is_array($secSettings['reviews']))
                ? $secSettings['reviews']
                : ($isRtl ? $defaultReviewsAr : $defaultReviewsEn);
            $starFill = str_replace('fill="none"', 'fill="currentColor"', fnd_icon('star', 16, '', 1.4));
            $starEmpty = fnd_icon('star', 16, '', 1.4);
            ?>
            <section class="home-reviews home-section" id="reviews">
                <div class="section-title section-title--center section-title--palms">
                    <div class="section-heading-line">
                        <?= $palm() ?>
                        <h2><?= esc_html($secTitle ?: ($isRtl ? 'آراء عملائنا' : 'What our customers say')) ?></h2>
                        <?= $palm() ?>
                    </div>
                    <p><?= esc_html($secSubtitle ?: ($secBadge ?: ($isRtl ? 'ثقة تُبنى مع كل طلب' : 'Trust built with every order'))) ?></p>
                </div>
                <div class="home-review-slider" role="group" aria-roledescription="<?= $isRtl ? 'شريط آراء' : 'reviews carousel' ?>" aria-label="<?= esc_attr($secTitle ?: ($isRtl ? 'آراء عملائنا' : 'Customer reviews')) ?>">
                    <ul class="home-review-track" id="home-review-track">
                        <?php foreach ($reviewsList as $rev):
                            $rComment = lang_get($rev, 'comment', ($rev['comment'] ?? ''));
                            $rName = lang_get($rev, 'name', ($rev['name'] ?? ''));
                            $rCity = lang_get($rev, 'city', ($rev['city'] ?? ''));
                            $rating = max(0, min(5, (int)($rev['rating'] ?? 5)));
                            // Initials: first letter of the first two words, ignoring honorifics like "د." / "أ."
                            $words = array_values(array_filter(preg_split('/\s+/u', trim((string)$rName)), fn($w) => mb_strlen(rtrim($w, '.')) > 1));
                            $second = $words[1] ?? '';
                            if (mb_substr($second, 0, 2) === 'ال' && mb_strlen($second) > 3) $second = mb_substr($second, 2);
                            $initials = mb_substr($words[0] ?? $rName, 0, 1) . mb_substr($second, 0, 1);
                            ?>
                            <li class="review-card">
                                <?= fnd_icon('quote', 30, 'review-mark', 1.4) ?>
                                <p class="review-rating">
                                    <span class="sr-only"><?= $isRtl ? "التقييم {$rating} من 5" : "Rated {$rating} out of 5" ?></span>
                                    <?= str_repeat($starFill, $rating) . str_repeat($starEmpty, 5 - $rating) ?>
                                </p>
                                <blockquote><?= esc_html($rComment) ?></blockquote>
                                <footer>
                                    <span class="review-avatar" aria-hidden="true"><?= esc_html($initials) ?></span>
                                    <span>
                                        <strong><?= esc_html($rName) ?></strong>
                                        <?php if ($rCity): ?><small><?= esc_html($rCity) ?></small><?php endif; ?>
                                    </span>
                                </footer>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="review-pager">
                        <button type="button" class="button button--icon" data-review-step="-1" aria-label="<?= $isRtl ? 'الآراء السابقة' : 'Previous reviews' ?>"><?= fnd_icon($isRtl ? 'chevron-right' : 'chevron-left', 18) ?></button>
                        <div class="review-dots" id="home-review-dots"></div>
                        <button type="button" class="button button--icon" data-review-step="1" aria-label="<?= $isRtl ? 'الآراء التالية' : 'Next reviews' ?>"><?= fnd_icon($isRtl ? 'chevron-left' : 'chevron-right', 18) ?></button>
                    </div>
                </div>
            </section>
            <script>
            (function () {
                var track = document.getElementById('home-review-track');
                var dotsBox = document.getElementById('home-review-dots');
                if (!track) return;
                var cards = Array.prototype.slice.call(track.children);
                var rtl = document.documentElement.dir === 'rtl';
                function perView() { return Math.max(1, Math.round(track.clientWidth / (cards[0] ? cards[0].offsetWidth : track.clientWidth))); }
                function pages() { return Math.max(1, Math.ceil(cards.length / perView())); }
                function current() { var w = cards[0] ? cards[0].offsetWidth + 22 : 1; return Math.round(Math.abs(track.scrollLeft) / (w * perView())); }
                function go(page) {
                    page = Math.max(0, Math.min(pages() - 1, page));
                    var card = cards[page * perView()];
                    if (card) track.scrollTo({ left: rtl ? -(Math.abs(card.offsetLeft - cards[0].offsetLeft)) : card.offsetLeft - cards[0].offsetLeft, behavior: 'smooth' });
                }
                function renderDots() {
                    var n = pages(), cur = current();
                    dotsBox.innerHTML = '';
                    for (var i = 0; i < n; i++) {
                        var b = document.createElement('button');
                        b.type = 'button'; b.className = 'review-dot';
                        b.setAttribute('aria-label', (rtl ? 'الانتقال إلى الرأي ' : 'Go to review ') + (i + 1));
                        if (i === cur) b.setAttribute('aria-current', 'true');
                        (function (i) { b.addEventListener('click', function () { go(i); }); })(i);
                        dotsBox.appendChild(b);
                    }
                    dotsBox.parentNode.style.display = n > 1 ? '' : 'none';
                }
                document.querySelectorAll('[data-review-step]').forEach(function (btn) {
                    btn.addEventListener('click', function () { go(current() + parseInt(btn.getAttribute('data-review-step'), 10)); });
                });
                var t; track.addEventListener('scroll', function () { clearTimeout(t); t = setTimeout(renderDots, 80); }, { passive: true });
                window.addEventListener('resize', renderDots);
                renderDots();
            })();
            </script>

        <?php /* ===================== 7. NEWSLETTER ===================== */ ?>
        <?php elseif ($secKey === 'newsletter'): ?>
            <?php
            $couponCode = $secSettings['coupon_code'] ?? 'TAMRNA10';
            $alertMsg = $isRtl ? "تم اشتراكك بنجاح في نادي تمرنا الملكي! استخدم كود الخصم: {$couponCode}" : "You have successfully joined the Tumurna VIP Club! Use promo code: {$couponCode}";
            ?>
            <section class="home-newsletter">
                <?= fnd_icon('tree-palm', 220, 'palm-decoration newsletter-palm newsletter-palm-start', 1.1) ?>
                <form class="newsletter home-newsletter-form" onsubmit="event.preventDefault(); showTumurnaToast('<?= esc_attr(addslashes($alertMsg)) ?>', 'success'); this.reset();">
                    <h3><?= esc_html($secTitle ?: ($isRtl ? 'كن دائمًا على اطلاع' : 'Always stay in the loop')) ?></h3>
                    <p class="newsletter-description"><?= esc_html($secSubtitle ?: ($isRtl ? 'اشترك في نشرتنا البريدية ليصلك كل جديد من منتجاتنا وعروضنا' : 'Subscribe to our newsletter for everything new in our products and offers')) ?></p>
                    <div class="newsletter-fields">
                        <div class="form-field">
                            <label for="home-news-email"><?= $isRtl ? 'البريد الإلكتروني' : 'Email' ?><span> *</span></label>
                            <input id="home-news-email" type="email" required autocomplete="email" class="input" placeholder="<?= $isRtl ? 'أدخل بريدك الإلكتروني' : 'Enter your email' ?>">
                        </div>
                        <button type="submit" class="button button--primary"><?= esc_html($secBtnText ?: ($isRtl ? 'اشتراك' : 'Subscribe')) ?></button>
                    </div>
                </form>
                <?= fnd_icon('tree-palm', 220, 'palm-decoration newsletter-palm newsletter-palm-end', 1.1) ?>
            </section>

        <?php /* ===================== 8. CUSTOM SECTIONS ===================== */ ?>
        <?php elseif (!empty($sec['is_custom'])):
            $customContent = lang_get($sec, 'custom_content');
            $cStyle = $sec['style'] ?? 'default';
            ?>
            <section class="home-section">
                <?php if ($cStyle === 'banner_cta'): ?>
                    <div class="home-custom-section home-custom-section--banner">
                        <h2><?= esc_html($secTitle) ?></h2>
                        <?php if ($secSubtitle): ?><p class="rich"><?= esc_html($secSubtitle) ?></p><?php endif; ?>
                        <?php if (!empty($customContent)): ?><div class="rich"><?= nl2br(esc_html($customContent)) ?></div><?php endif; ?>
                        <?php if (!empty($secBtnText)): ?>
                            <a class="button button--secondary" href="<?= url($sec['button_url'] ?: '/') ?>"><?= esc_html($secBtnText) ?></a>
                        <?php endif; ?>
                    </div>
                <?php elseif ($cStyle === 'image_first' && !empty($sec['image'])): ?>
                    <div class="home-custom-section home-custom-section--split">
                        <img src="<?= asset($sec['image']) ?>" alt="<?= esc_attr($secTitle) ?>" loading="lazy">
                        <div>
                            <h2><?= esc_html($secTitle) ?></h2>
                            <p class="eyebrow" style="margin-block:.5rem"><?= esc_html($secSubtitle) ?></p>
                            <div class="rich"><?= nl2br(esc_html($customContent)) ?></div>
                            <?php if (!empty($secBtnText)): ?>
                                <a class="button button--primary" style="margin-top:1rem" href="<?= url($sec['button_url'] ?: '/') ?>"><?= esc_html($secBtnText) ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="home-custom-section">
                        <?php if (!empty($sec['image'])): ?>
                            <div class="home-custom-media"><img src="<?= asset($sec['image']) ?>" alt="<?= esc_attr($secTitle) ?>" style="width:100%;height:100%;object-fit:cover"></div>
                        <?php endif; ?>
                        <h2><?= esc_html($secTitle) ?></h2>
                        <?php if ($secSubtitle): ?><p class="muted"><?= esc_html($secSubtitle) ?></p><?php endif; ?>
                        <?php if (!empty($customContent)): ?><div class="rich"><?= nl2br(esc_html($customContent)) ?></div><?php endif; ?>
                        <?php if (!empty($secBtnText)): ?>
                            <a class="button button--primary" href="<?= url($sec['button_url'] ?: '/') ?>"><?= esc_html($secBtnText) ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

    <?php endforeach; ?>

    <?php /* features strip when the page has no hero to hang it on */ ?>
    <?php if ($heroSection === null && $featuresSection !== null): ?>
        <?php $renderFeatureStrip($featuresSection); ?>
    <?php endif; ?>

    <div style="height:var(--space-16)"></div>
</div>
