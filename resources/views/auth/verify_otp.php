<?php
use App\Core\I18n;
$locale = I18n::getLocale();
$isRtl = I18n::isRtl();
$immersiveHeader = true;
$authLayout = true;
$isAr = $locale === 'ar';
?>
<div class="auth-page">
    <div class="auth-scene"><img src="<?= asset('assets/images/home/hero.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%"></div>
    <section class="auth-main">
        <div class="auth-card-column">
            <div class="auth-card">
                <header class="auth-card-heading">
                    <?= fnd_icon('shield-check', 45, '', 1.2) ?>
                    <h1><?= $isAr ? 'تأكيد رقم الجوال' : 'Verify Mobile Number' ?></h1>
                    <p><?= $isAr ? 'تم إرسال رمز التحقق المكون من 6 أرقام إلى جوالك:' : 'A 6-digit verification code was sent to:' ?></p>
                    <p><span class="badge" dir="ltr" style="margin-top:6px"><?= htmlspecialchars((string)($phone ?? '')) ?></span></p>
                </header>

                <p class="auth-demo-code"><?= $isAr ? 'رمز الديمو التجريبي السريع:' : 'Demo OTP code:' ?> <bdi>123456</bdi><br><?= $isAr ? 'يمكنك إدخال 123456 مباشرة لتأكيد رقمك والدخول فوراً.' : 'You can enter 123456 directly to verify your phone and sign in.' ?></p>

                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert" style="margin-top:12px"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="flash flash--success auth-flash" role="status" style="margin-top:12px"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
                <?php endif; ?>

                <form action="<?= url('/verify-otp') ?>" method="POST" id="otpForm" class="auth-form" style="margin-top:16px">
                    <input type="hidden" name="phone" value="<?= htmlspecialchars((string)($phone ?? '')) ?>">
                    <p class="auth-otp-label"><?= $isAr ? 'أدخل رمز التحقق (6 أرقام)' : 'Enter 6-digit code' ?></p>
                    <div class="auth-otp-digits" dir="ltr">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <input class="input otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" id="digit-<?= $i ?>" data-index="<?= $i ?>" autocomplete="off" aria-label="<?= $i ?>">
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="otp" id="combinedOtpInput" value="">
                    <button type="submit" id="submitOtpBtn" class="button button--primary auth-submit" style="margin-top:18px">
                        <?= fnd_icon('check', 19) ?><?= $isAr ? 'تأكيد الحساب والدخول' : 'Verify & Continue' ?>
                    </button>
                </form>

                <div class="auth-verification-actions" style="margin-top:16px;gap:8px">
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:center">
                        <span><?= $isAr ? 'لم يصلك الرمز؟' : "Didn't get code?" ?></span>
                        <form action="<?= url('/resend-otp') ?>" method="POST" style="display:inline">
                            <input type="hidden" name="phone" value="<?= htmlspecialchars((string)($phone ?? '')) ?>">
                            <button type="submit" id="resendBtn" class="auth-link-button"><?= $isAr ? 'إعادة الإرسال' : 'Resend' ?> (<span id="resendTimer">60</span>s)</button>
                        </form>
                    </div>
                    <a class="auth-back" href="<?= url('/register') ?>"><?= $isAr ? 'تغيير رقم الجوال' : 'Change Phone' ?></a>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../components/auth_story.php'; ?>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const digits = document.querySelectorAll('.otp-digit');
    const combinedInput = document.getElementById('combinedOtpInput');
    const otpForm = document.getElementById('otpForm');
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');

    if (digits[0]) digits[0].focus();

    function updateCombined() {
        let val = '';
        digits.forEach(d => val += d.value);
        combinedInput.value = val;
    }

    digits.forEach((digit, idx) => {
        digit.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val ? val.slice(-1) : '';
            updateCombined();
            if (e.target.value && idx < digits.length - 1) {
                digits[idx + 1].focus();
            }
            if (combinedInput.value.length === 6) {
                otpForm.submit();
            }
        });

        digit.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !digit.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });

        digit.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            if (pasteData) {
                pasteData.split('').forEach((char, i) => {
                    if (digits[i]) digits[i].value = char;
                });
                updateCombined();
                if (digits[Math.min(pasteData.length, digits.length - 1)]) {
                    digits[Math.min(pasteData.length, digits.length - 1)].focus();
                }
                if (combinedInput.value.length === 6) {
                    otpForm.submit();
                }
            }
        });
    });

    otpForm.addEventListener('submit', (e) => {
        updateCombined();
        if (combinedInput.value.length < 6) {
            e.preventDefault();
            showTumurnaToast('<?= $isAr ? "يرجى إدخال الرمز المكون من 6 أرقام (123456)" : "Please enter the full 6-digit code (123456)" ?>', 'error');
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
