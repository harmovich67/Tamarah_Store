<?php
/** Decorative storytelling side of the auth screens (Tamrna Foundation) */
$isRtlStory = \App\Core\I18n::isRtl();
?>
<aside class="auth-story">
    <div class="auth-story-copy">
        <h2><?= $isRtlStory ? 'أكثر من تمر ..' : 'More than dates ..' ?><br><span><?= $isRtlStory ? 'هي قصة أرض وهُويّة' : 'a story of land and identity' ?></span></h2>
        <p><?= $isRtlStory
            ? 'في تمرنا نحيي أصالة التمور السعودية،<br>ونختار لك أجود الأنواع من مزارع المملكة<br>لنقدم لك تجربة فاخرة بكل تفاصيلها'
            : 'At Tamrna we celebrate the authenticity of Saudi dates,<br>choosing the finest varieties from the Kingdom’s farms<br>for a luxurious experience in every detail' ?></p>
    </div>
    <div class="auth-side-story">
        <?= fnd_icon('tree-palm', 42, '', 1.2) ?>
        <p><?= $isRtlStory ? 'من أرض<br>السعودية<br>إلى كل العالم' : 'From the land<br>of Saudi Arabia<br>to the world' ?></p>
        <i></i>
    </div>
    <div class="auth-trust">
        <div><?= fnd_icon('badge-check', 39, '', 1.3) ?><span><?= $isRtlStory ? 'جودة نعتني بها' : 'Quality we care for' ?></span></div>
        <div><?= fnd_icon('tree-palm', 39, '', 1.3) ?><span><?= $isRtlStory ? 'من مزارع سعودية مختارة' : 'From selected Saudi farms' ?></span></div>
        <div><?= fnd_icon('leaf', 39, '', 1.3) ?><span><?= $isRtlStory ? 'تمور بطعم أصيل' : 'Dates with authentic taste' ?></span></div>
    </div>
    <p class="auth-signature"><?= $isRtlStory ? 'تمرنا .. أصالة سعودية بطعم المستقبل' : 'Tamrna .. Saudi authenticity with a taste of the future' ?><i></i></p>
</aside>
