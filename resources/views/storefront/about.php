<?php
/**
 * Tamrna Foundation - About Us View
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
?>
<div class="pattern-background about-page">
    <section class="about-hero">
        <img src="<?= asset('assets/images/home/heritage.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%">
        <div class="about-hero-shade"></div>
        <div class="container about-hero-content">
            <div class="about-hero-copy">
                <nav aria-label="<?= $isRtl ? 'مسار الصفحة' : 'Breadcrumb' ?>">
                    <a href="<?= url('/') ?>"><?= $isRtl ? 'الرئيسية' : 'Home' ?></a>
                    <span>/</span>
                    <span><?= $isRtl ? 'من نحن' : 'About' ?></span>
                </nav>
                <span class="about-eyebrow"><?= $isRtl ? 'أصالة النخيل وكرم الضيافة السعودية' : 'Authentic Palms & Saudi Hospitality' ?></span>
                <h1><?= $isRtl ? "قصة تَـمْرُنـا ..\nتراث سعودي عريق بجودة ملكية" : "The Tumurna story —\nheritage meets distinction" ?></h1>
                <p><?= $isRtl
                    ? 'نحن في "تمرنا" نؤمن بأن التمور السعودية ليست مجرد غذاء، بل هي رمز عريق للأصالة والكرم والضيافة الملكية المتوارثة عبر الأجيال.'
                    : 'At Tumurna, we believe Saudi dates are far more than fruit—they are a cherished emblem of generosity, heritage, and royal hospitality celebrated across generations.' ?></p>
                <a class="button button--secondary" href="<?= url('/catalog') ?>"><?= $isRtl ? 'تسوق التمور' : 'Shop dates' ?><?= fnd_icon($arrow, 18, 'directional-arrow') ?></a>
            </div>
            <aside>
                <?= fnd_icon('tree-palm', 48, '', 1.2) ?>
                <p><?= $isRtl ? 'من أرض<br>السعودية<br>إلى كل العالم' : 'From the land<br>of Saudi Arabia<br>to the world' ?></p>
            </aside>
        </div>
    </section>

    <main>
        <section class="container about-story" id="story">
            <div class="about-story-media">
                <img src="<?= asset('assets/images/saudi_dates_hero_1787053884267.jpg') ?>" alt="<?= $isRtl ? 'حصاد تمور تمرنا' : 'Tamrna dates harvest' ?>" style="position:absolute;inset:0;width:100%;height:100%">
            </div>
            <div class="about-story-copy">
                <span class="about-eyebrow"><?= $isRtl ? 'قصتنا' : 'Our story' ?></span>
                <h2><?= $isRtl ? 'من الواحات الخضراء إلى مائدتكم الملكية' : 'From golden oases to your prestigious table' ?></h2>
                <p><?= $isRtl
                    ? 'انطلقت مسيرتنا من شغف عميق باختيار أرقى أصناف التمور من بساتين المدينة المنورة، واحات القصيم، ومزارع الأحساء التاريخية، لنقدمها وفق أعلى معايير الجودة العالمية والتغليف الملكي الفاخر.'
                    : 'Our journey began with a profound dedication to curating the finest dates from historic groves across Madinah, Qassim, and Al-Ahsa, presented with immaculate craftsmanship and bespoke packaging.' ?></p>
                <p style="margin-top:12px"><?= $isRtl
                    ? 'نحرص في كل مرحلة - من القطف اليدوي والفرز الدقيق وصولاً إلى التعبئة والتوصيل المبرد - على حفظ النكهة الأصلية والقيمة الغذائية العالية لكل حبة، لنصل بها إلى مائدتكم أو لتكون هديتكم الأبهى لمن تحبون.'
                    : 'At every milestone—from hand harvest and rigorous sorting to refrigerated logistics—we protect the natural moisture, rich nutrients, and sublime texture of each date.' ?></p>
                <ul class="about-pillars">
                    <li><p><strong><?= $isRtl ? 'جودة أصيلة' : 'Authentic quality' ?></strong><span><?= $isRtl ? 'اختيار يليق بالضيافة' : 'Selected for hospitality' ?></span></p></li>
                    <li><p><strong><?= $isRtl ? 'ثقة مستمرة' : 'Lasting trust' ?></strong><span><?= $isRtl ? 'عناية في كل تجربة' : 'Care in every experience' ?></span></p></li>
                    <li><p><strong><?= $isRtl ? 'مجتمع أكبر' : 'A wider community' ?></strong><span><?= $isRtl ? 'روابط تبدأ من الأرض' : 'Bonds that start from the land' ?></span></p></li>
                    <li><p><strong><?= $isRtl ? 'مصدر محلي' : 'Local sourcing' ?></strong><span><?= $isRtl ? 'من مزارع المملكة' : 'From the Kingdom’s farms' ?></span></p></li>
                </ul>
            </div>
        </section>

        <section class="container about-stats" aria-label="<?= $isRtl ? 'مؤشرات تمرنا' : 'Tamrna highlights' ?>">
            <article>
                <?= fnd_icon('sparkles', 35, '', 1.4) ?>
                <span><?= $isRtl ? 'طبيعي ونخب أول' : 'Natural prime grade' ?></span>
                <strong dir="ltr">100%</strong>
            </article>
            <article>
                <?= fnd_icon('map-pin', 35, '', 1.4) ?>
                <span><?= $isRtl ? 'مناطق حصاد تاريخية' : 'Historic regions' ?></span>
                <strong dir="ltr">3</strong>
            </article>
            <article>
                <?= fnd_icon('truck', 35, '', 1.4) ?>
                <span><?= $isRtl ? 'عناية فائقة بالجودة' : 'Cold chain care' ?></span>
                <strong dir="ltr">24/7</strong>
            </article>
            <article>
                <?= fnd_icon('gift', 35, '', 1.4) ?>
                <span><?= $isRtl ? 'مناسبات وإهداءات' : 'Occasions & gifting' ?></span>
                <strong dir="ltr">∞</strong>
            </article>
        </section>

        <section class="container about-values">
            <div class="section-title section-title--center section-title--divider">
                <span class="eyebrow"><?= $isRtl ? 'قيمنا' : 'Our values' ?></span>
                <div class="section-heading-line"><h2><?= $isRtl ? 'قيمنا الجوهرية' : 'Our Core Values' ?></h2></div>
                <p><?= $isRtl ? 'ركائز نبني عليها ثقة ضيوفنا وشركائنا كل يوم' : 'The timeless principles defining the trust of our patrons every day' ?></p>
            </div>
            <div class="about-values-grid">
                <article>
                    <?= fnd_icon('award', 43, '', 1.3) ?>
                    <h3><?= $isRtl ? 'الأصالة والموثوقية' : 'Authenticity & Purity' ?></h3>
                    <p><?= $isRtl
                        ? 'نضمن مصادر تمورنا من مزارع معتمدة وموثقة تتبع ممارسات زراعية مستدامة وخالية من الكيماويات.'
                        : 'Ethically sourced exclusively from certified organic groves following sustainable ancestral practices.' ?></p>
                </article>
                <article>
                    <?= fnd_icon('sparkles', 43, '', 1.3) ?>
                    <h3><?= $isRtl ? 'الضيافة الملكية' : 'Royal Hospitality' ?></h3>
                    <p><?= $isRtl
                        ? 'نصمم عبواتنا وبوكساتنا بعناية حرفية لتكون تحفة تتباهى بها في استقبال كبار ضيوفك وفي مناسباتك السعيدة.'
                        : 'Handcrafted luxury trunks and velvet presentations tailored for dignitary receptions and celebrations.' ?></p>
                </article>
                <article>
                    <?= fnd_icon('truck', 43, '', 1.3) ?>
                    <h3><?= $isRtl ? 'سلسلة تبريد متكاملة' : 'Refrigerated Cold Fleet' ?></h3>
                    <p><?= $isRtl
                        ? 'من المستودع المبرد حتى باب المستلم للحفاظ على الطراوة واللون الذهبي الساحر.'
                        : 'Unbroken refrigerated distribution maintaining peak softness, moisture, and golden brilliance.' ?></p>
                </article>
            </div>
        </section>

        <section class="container about-farms" id="farms">
            <img src="<?= asset('assets/images/home/hero.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%">
            <div></div>
            <article>
                <span class="about-eyebrow"><?= $isRtl ? 'مزارعنا' : 'Our farms' ?></span>
                <h2><?= $isRtl ? 'من قلب أرض السعودية' : 'From the heart of Saudi Arabia' ?></h2>
                <p><?= $isRtl
                    ? 'بساتين النخيل المباركة في مزارع العالية بالمدينة المنورة وواحات القصيم ومزارع الأحساء.'
                    : 'Blessed palm groves of Al-Aliyah in Madinah, the fertile oases of Qassim and the farms of Al-Ahsa.' ?></p>
                <a class="button button--secondary" href="<?= url('/catalog') ?>"><?= $isRtl ? 'اكتشف التمور' : 'Discover our dates' ?></a>
            </article>
        </section>

        <section class="container about-sustainability" id="journey">
            <div class="about-sustainability-media">
                <img src="<?= asset('assets/images/home/heritage.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%">
            </div>
            <article>
                <span class="about-eyebrow"><?= $isRtl ? 'التزامنا' : 'Our commitment' ?></span>
                <h2><?= $isRtl ? 'جودة في كل التفاصيل' : 'Quality in every detail' ?></h2>
                <p><?= $isRtl ? 'من الاختيار إلى التقديم، نهتم بكل مرحلة لتصل إليكم تجربة متسقة تعبّر عن أصالة تمرنا.' : 'From selection to presentation, we care for every stage so you receive a consistent experience that reflects Tamrna’s authenticity.' ?></p>
                <div class="about-sustainability-principles">
                    <span><?= fnd_icon('leaf', 32, '', 1.4) ?><strong><?= $isRtl ? 'القطف اليدوي' : 'Hand harvest' ?></strong><small><?= $isRtl ? 'فرز دقيق' : 'Careful sorting' ?></small></span>
                    <span><?= fnd_icon('package-check', 32, '', 1.4) ?><strong><?= $isRtl ? 'تغليف فاخر' : 'Luxury packaging' ?></strong><small><?= $isRtl ? 'يليق بالإهداء' : 'Fit for gifting' ?></small></span>
                    <span><?= fnd_icon('snowflake', 32, '', 1.4) ?><strong><?= $isRtl ? 'شحن مبرد' : 'Chilled shipping' ?></strong><small><?= $isRtl ? 'حتى باب المستلم' : 'To the doorstep' ?></small></span>
                </div>
            </article>
        </section>

        <section class="about-gift" style="margin-top:48px">
            <img src="<?= asset('assets/images/home/gifting.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%">
            <div></div>
            <article class="container">
                <h2><?= $isRtl ? 'أهدِ أصالة التمر لمن تحب' : 'Gift the authenticity of dates to those you love' ?></h2>
                <p><?= $isRtl ? 'بطاقات إهداء تحمل معنى الضيافة.' : 'Gift cards that carry the meaning of hospitality.' ?></p>
                <a class="button button--secondary" href="<?= url('/gift-cards') ?>"><?= $isRtl ? 'اكتشف بطاقات الإهداء' : 'Discover gift cards' ?></a>
            </article>
        </section>
    </main>
</div>
