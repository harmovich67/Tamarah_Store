<?php
use App\Core\I18n;

$locale = I18n::getLocale();
$isRtl = I18n::isRtl();
$isEn = $locale === 'en';
$immersiveHeader = true;
$pageCss = ['auth'];
$authLayout = true;
$isAr = $locale === 'ar';
?>
<div class="auth-page">
    <div class="auth-scene"><img src="<?= asset('assets/images/home/hero.webp') ?>" alt=""></div>
    <div class="auth-cream"></div>
    <section class="auth-main">
        <div class="auth-card-column">
            <div class="auth-card">
                <header class="auth-card-heading">
                    <?= fnd_icon('shield-check', 45, '', 1.2) ?>
                    <h1><?= $isAr ? 'تأكيد رقم الجوال' : 'Verify Mobile Number' ?></h1>
                    <p><?= $isAr ? 'تم إرسال رمز التحقق المكون من 6 أرقام إلى جوالك:' : 'A 6-digit verification code was sent to your phone:' ?></p>
                    <p><span class="badge" dir="ltr" style="margin-top:6px"><?= htmlspecialchars((string)($phone ?? '')) ?></span></p>
                </header>

                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert" style="margin-top:12px"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="flash flash--success auth-flash" role="status" style="margin-top:12px"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
                <?php endif; ?>

                <form action="<?= url('/verify-otp') ?>" method="POST" id="otpForm" class="auth-form" style="margin-top:16px">
                    <input type="hidden" name="phone" value="<?= htmlspecialchars((string)($phone ?? '')) ?>">
                    <div class="form-field">
                        <label for="combinedOtpInput"><?= $isAr ? 'رمز التحقق' : 'Verification code' ?><span aria-hidden="true"> *</span></label>
                        <input class="input otp-input" type="text" id="combinedOtpInput" name="otp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9٠-٩۰-۹]{6}" dir="ltr" placeholder="······" required>
                        <p class="field-hint"><?= $isAr ? 'أدخل الرمز المكوّن من ٦ أرقام. صلاحيته ٥ دقائق.' : 'Enter the 6-digit code. It expires in 5 minutes.' ?></p>
                    </div>
                    <button type="submit" id="submitOtpBtn" class="button button--primary auth-submit" style="margin-top:18px">
                        <?= fnd_icon('check', 19) ?><?= $isAr ? 'تأكيد الحساب والدخول' : 'Verify & Continue' ?>
                    </button>
                </form>

                <div class="auth-verification-actions" style="margin-top:16px;gap:8px">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:center">
                        <span><?= $isAr ? 'لم يصلك الرمز؟' : "Didn't receive the code?" ?></span>
                        <form action="<?= url('/resend-otp') ?>" method="POST" style="display:inline">
                            <input type="hidden" name="phone" value="<?= htmlspecialchars((string)($phone ?? '')) ?>">
                            <button type="submit" id="resendBtn" class="auth-link-button"><?= $isAr ? 'إعادة الإرسال' : 'Resend' ?> (<span id="resendTimer">60</span>s)</button>
                        </form>
                    </div>
                    <a class="auth-back" href="<?= url('/register') ?>"><?= $isAr ? 'تغيير رقم الجوال' : 'Change Phone Number' ?></a>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../components/auth_story.php'; ?>
    </section>
    <?php include __DIR__ . '/../components/auth_label.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const combinedInput = document.getElementById('combinedOtpInput');
    const otpForm = document.getElementById('otpForm');
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');
    const arabicDigits = '٠١٢٣٤٥٦٧٨٩', persianDigits = '۰۱۲۳۴۵۶۷۸۹';

    if (combinedInput) combinedInput.focus();

    // Accept Arabic-Indic digits, keep digits only, submit as soon as the code is complete
    combinedInput.addEventListener('input', () => {
        const val = combinedInput.value
            .replace(/[٠-٩]/g, d => String(arabicDigits.indexOf(d)))
            .replace(/[۰-۹]/g, d => String(persianDigits.indexOf(d)))
            .replace(/\D/g, '')
            .slice(0, 6);
        combinedInput.value = val;
        if (val.length === 6) otpForm.submit();
    });

    otpForm.addEventListener('submit', (e) => {
        if (combinedInput.value.length < 6) {
            e.preventDefault();
            if (typeof showTumurnaToast === 'function') {
                showTumurnaToast('<?= $isAr ? "يرجى إدخال الرمز المكون من 6 أرقام" : "Please enter the full 6-digit code" ?>', 'error');
            } else {
                alert('<?= $isAr ? "يرجى إدخال الرمز المكون من 6 أرقام" : "Please enter the full 6-digit code" ?>');
            }
        }
    });

    // Resend timer
    let timeLeft = 60;
    resendBtn.disabled = true;
    const timerInterval = setInterval(() => {
        timeLeft--;
        if (resendTimer) resendTimer.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            resendBtn.disabled = false;
            resendBtn.innerHTML = '<?= $isAr ? "إعادة إرسال الرمز الآن" : "Resend Code Now" ?>';
        }
    }, 1000);
});
</script>
