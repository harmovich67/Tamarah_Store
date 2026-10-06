<?php
$isRtlErr = \App\Core\I18n::isRtl();
$errCode = (int)($code ?? 400);

// Friendly, localized copy per status code; custom messages are kept when they match the page language.
$errCopy = [
    403 => [$isRtlErr ? 'لا تملك صلاحية الوصول' : 'Access denied', $isRtlErr ? 'ليست لديك صلاحية لفتح هذه الصفحة.' : 'You do not have permission to open this page.'],
    404 => [$isRtlErr ? 'الصفحة غير موجودة' : 'Page not found', $isRtlErr ? 'ربما تغيّر الرابط أو لم تعد الصفحة متاحة.' : 'The link may have changed or the page is no longer available.'],
    500 => [$isRtlErr ? 'حدث خطأ غير متوقع' : 'Something went wrong', $isRtlErr ? 'نعمل على إصلاح المشكلة، يرجى المحاولة لاحقًا.' : 'We are looking into it. Please try again shortly.'],
];
$errTitle = $errCopy[$errCode][0] ?? ($isRtlErr ? 'حدث خطأ' : 'Something went wrong');
$errText = $errCopy[$errCode][1] ?? '';
$customMsg = trim(preg_replace('/\s*\([^)]*Not Found[^)]*\)/i', '', (string)($message ?? '')));
$hasArabic = (bool)preg_match('/\p{Arabic}/u', $customMsg);
if ($customMsg !== '' && !isset($errCopy[$errCode]) && $hasArabic === $isRtlErr) {
    $errTitle = $customMsg;
} elseif ($customMsg !== '' && $hasArabic === $isRtlErr && !in_array($errCode, [403, 404, 500], true)) {
    $errText = $customMsg;
}
?>
<section class="commerce-page error-page">
    <div class="container">
        <div class="account-gate error-card" role="alert">
            <span class="error-card-icon"><?= fnd_icon('shield-alert', 48, '', 1.4) ?></span>
            <p class="error-card-code" dir="ltr"><?= $errCode ?></p>
            <h1><?= htmlspecialchars($errTitle) ?></h1>
            <?php if ($errText !== ''): ?><p><?= htmlspecialchars($errText) ?></p><?php endif; ?>
            <div class="error-card-actions">
                <a href="<?= url('/') ?>" class="button button--primary"><?= $isRtlErr ? 'العودة للرئيسية' : 'Back to home' ?></a>
                <a href="<?= url('/catalog') ?>" class="button button--outline"><?= $isRtlErr ? 'تصفح المنتجات' : 'Browse products' ?></a>
            </div>
        </div>
    </div>
</section>
