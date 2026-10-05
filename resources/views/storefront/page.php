<?php
use App\Core\I18n;
$locale = $locale ?? I18n::getLocale();
$isRtl = $isRtl ?? I18n::isRtl();

$title = $locale === 'en' && !empty($page['title_en']) ? $page['title_en'] : $page['title_ar'];
$content = $locale === 'en' && !empty($page['content_en']) ? $page['content_en'] : ($page['content_ar'] ?? '');
$excerpt = $locale === 'en' && !empty($page['excerpt_en']) ? $page['excerpt_en'] : ($page['excerpt_ar'] ?? '');

// ACF Pro Native Fields
$heroBadge = get_field('hero_badge_text') ?: ($locale === 'ar' ? 'متجر تَـمْرُنـا للتمور الفاخرة' : 'Tumurna Luxury Dates Store');
$showSupportCard = get_field('show_support_card', null, 'page', '1');
$updated = date('Y/m/d', strtotime($page['updated_at'] ?? $page['created_at']));
?>
<div class="pattern-background legal-page">
    <div class="container legal-layout">
        <header class="legal-hero">
            <?= fnd_icon('file-text', 42) ?>
            <?php if (!empty($heroBadge)): ?><span><?= htmlspecialchars($heroBadge) ?></span><?php endif; ?>
            <h1><?= htmlspecialchars($title) ?></h1>
            <?php if (!empty($excerpt)): ?><p><?= htmlspecialchars($excerpt) ?></p><?php endif; ?>
            <small><?= $locale === 'ar' ? 'آخر تحديث: ' : 'Last updated: ' ?><?= $updated ?></small>
        </header>

        <div class="legal-sections">
            <?php if (!empty($page['featured_image'])): ?>
                <img src="<?= htmlspecialchars(asset($page['featured_image'])) ?>" alt="<?= htmlspecialchars($title) ?>" style="width:100%;max-height:360px;object-fit:cover;border-radius:14px">
            <?php endif; ?>

            <section>
                <article class="rich"><?= $content ?></article>
            </section>

            <?php if (have_rows('key_features')): ?>
                <section>
                    <h2><?= $locale === 'ar' ? 'المميزات والضمانات المعتمدة' : 'Official Guarantees & Features' ?></h2>
                    <div class="home-features" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-top:16px">
                        <?php while (have_rows('key_features')): the_row('key_features'); ?>
                            <div class="home-feature-card">
                                <?= fnd_icon((string)(get_sub_field('icon') ?: 'check-circle'), 38, '', 1.35) ?>
                                <h3><?= htmlspecialchars((string)get_sub_field('title')) ?></h3>
                                <p><?= htmlspecialchars((string)get_sub_field('desc')) ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($showSupportCard && $showSupportCard !== '0'): ?>
                <section>
                    <h2><?= $locale === 'ar' ? 'هل لديك أي استفسار حول أصناف التمور أو طلبات الإهداء؟' : 'Have questions regarding our date varieties or luxury gifting?' ?></h2>
                    <p><?= $locale === 'ar' ? 'فريق خدمة عملاء تَـمْرُنـا متواجد دائماً لمساعدتك في اختيار أفخر أصناف التمور الملكية وطلبات المناسبات.' : 'Our connoisseur support team is ready to assist with custom packaging and premium gifting.' ?></p>
                    <a href="https://wa.me/966500000000" target="_blank" rel="noopener noreferrer" class="button button--primary" style="margin-top:14px"><?= fnd_icon('message-circle', 18) ?><?= $locale === 'ar' ? 'تواصل عبر واتساب' : 'Chat via WhatsApp' ?></a>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
