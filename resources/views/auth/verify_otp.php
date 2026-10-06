<?php
use App\Core\I18n;

$locale = I18n::getLocale();
$isRtl = I18n::isRtl();
$isEn = $locale === 'en';
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
$pageCss = ['auth'];
$authLayout = true;
$immersiveHeader = true;
?>
<div class="auth-page">
    <div class="auth-scene"><img src="<?= asset('assets/images/home/hero.webp') ?>" alt="" loading="eager"></div>
    <div class="auth-cream"></div>
    <main id="main-content" class="auth-main">
        <div class="auth-card-column">
            <section class="auth-card" aria-labelledby="auth-title">
                <header class="auth-card-heading">
                    <?= fnd_icon('tree-palm', 24, '', 1.4) ?>
                    <h1 id="auth-title"><?= $isEn ? 'Verify your mobile' : 'تأكيد رقم الجوال' ?></h1>
                    <p><?= $isEn ? 'Enter the 6-digit code. It expires in 5 minutes.' : 'أدخل الرمز المكوّن من ٦ أرقام. صلاحيته ٥ دقائق.' ?></p>
                </header>

                <?php if (!empty($notice)): ?>
                    <div class="flash flash--info auth-flash" role="status"><?= fnd_icon('info', 18) ?><div><?= htmlspecialchars($notice) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="flash flash--success auth-flash" role="status"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
                <?php endif; ?>

                <form novalidate data-form="auth" class="auth-form" method="POST" action="<?= url('/verify-otp') ?>" id="otpForm">
                    <fieldset>
                        <input type="hidden" name="phone" value="<?= htmlspecialchars((string)($phone ?? '')) ?>">

                        <div class="form-field">
                            <label for="auth-code"><?= $isEn ? 'Verification code' : 'رمز التحقق' ?><span> *</span></label>
                            <input id="auth-code" name="otp" class="input otp-input" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9٠-٩۰-۹]{6}" dir="ltr" placeholder="······" required autofocus>
                        </div>

                        <p class="auth-demo-code">
                            <?= $isEn ? 'Preview code' : 'رمز التجربة' ?>: <bdi>123456</bdi>
                        </p>

                        <button type="submit" class="button button--primary auth-submit">
                            <?= $isEn ? 'Verify code' : 'تأكيد الرمز' ?><?= fnd_icon($arrow, 20, 'directional-arrow') ?>
                        </button>
                    </fieldset>
                </form>

                <div class="auth-verification-actions">
                    <form action="<?= url('/resend-otp') ?>" method="POST" style="display:inline">
                        <input type="hidden" name="phone" value="<?= htmlspecialchars((string)($phone ?? '')) ?>">
                        <button type="submit" id="auth-resend" class="button button--ghost" disabled>
                            <?= $isEn ? 'Resend code' : 'إعادة إرسال الرمز' ?><span id="auth-countdown"> (60)</span>
                        </button>
                    </form>
                    <a href="<?= url('/register') ?>"><?= $isEn ? 'Change mobile number' : 'تعديل رقم الجوال' ?></a>
                </div>

                <a class="auth-back" href="<?= url('/login') ?>">
                    <?= $isEn ? 'Back to sign in' : 'العودة لتسجيل الدخول' ?>
                </a>

                <p class="auth-demo-notice">
                    <?= $isEn ? 'Local preview: use test data only. No SMS is sent. Accounts are stored in this browser only.' : 'وضع تجربة محلي: استخدم بيانات اختبار فقط. لا تُرسل رسائل SMS، وتُحفظ الحسابات في هذا المتصفح فقط.' ?>
                </p>
            </section>
        </div>

        <?php include __DIR__ . '/../components/auth_story.php'; ?>
    </main>
    <?php include __DIR__ . '/../components/auth_label.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('auth-code');
    const form = document.getElementById('otpForm');
    const resendBtn = document.getElementById('auth-resend');
    const countdownSpan = document.getElementById('auth-countdown');
    const arabicDigits = '٠١٢٣٤٥٦٧٨٩', persianDigits = '۰۱۲۳۴۵۶۷۸۹';

    if (input) {
        input.addEventListener('input', () => {
            const val = input.value
                .replace(/[٠-٩]/g, d => String(arabicDigits.indexOf(d)))
                .replace(/[۰-۹]/g, d => String(persianDigits.indexOf(d)))
                .replace(/\D/g, '')
                .slice(0, 6);
            input.value = val;
            if (val.length === 6 && form) {
                form.submit();
            }
        });
    }

    let left = 60;
    const timer = setInterval(() => {
        left--;
        if (countdownSpan) countdownSpan.textContent = left > 0 ? ` (${left})` : '';
        if (left <= 0) {
            clearInterval(timer);
            if (resendBtn) resendBtn.disabled = false;
        }
    }, 1000);
});
</script>
