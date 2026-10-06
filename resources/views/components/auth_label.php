<?php
/** Vertical heritage label at the edge of the auth screens (Tamrna Foundation) */
$isRtlLabel = \App\Core\I18n::isRtl();
?>
<div class="auth-heritage-label">
    <?= fnd_icon('tree-palm', 24, '', 1.2) ?>
    <span><?= $isRtlLabel ? 'تمور<br>بأصالة<br>سعودية' : 'Authentically Saudi' ?></span>
    <i></i>
</div>
