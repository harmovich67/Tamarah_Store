<?php $isRtlErr = \App\Core\I18n::isRtl(); ?>
<section class="commerce-page">
    <div class="container">
        <div class="account-gate">
            <?= fnd_icon('shield-alert', 48, '', 1.4) ?>
            <h1><?= htmlspecialchars($message ?? ($isRtlErr ? 'حدث خطأ' : 'Something went wrong')) ?></h1>
            <p><?= $isRtlErr ? 'كود الخطأ:' : 'Error code:' ?> <?= (int)($code ?? 400) ?></p>
            <a href="<?= url('/') ?>" class="button button--primary"><?= $isRtlErr ? 'العودة للرئيسية' : 'Back to home' ?></a>
        </div>
    </div>
</section>
