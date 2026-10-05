<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
$otpEnabled = \App\Services\OtpService::isEnabled();
$immersiveHeader = true;
$authLayout = true;
?>
<div class="auth-page">
    <div class="auth-scene"><img src="<?= asset('assets/images/home/hero.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%"></div>
    <section class="auth-main">
        <div class="auth-card-column">
            <div class="auth-card">
                <header class="auth-card-heading">
                    <?= fnd_icon('tree-palm', 45, '', 1.2) ?>
                    <h1><?= __('customer_login') ?></h1>
                    <p>سجل دخولك لمتابعة طلبات التمور الفاخرة، الشحن وعناوين التوصيل</p>
                </header>

                <?php if (!empty($notice)): ?>
                    <div class="flash flash--info auth-flash" role="status"><?= fnd_icon('lock', 18) ?><div><?= htmlspecialchars($notice) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="flash flash--success auth-flash" role="status"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
                <?php endif; ?>

                <?php if ($otpEnabled): ?>
                <div class="auth-tabs" id="loginTabs" role="tablist">
                    <button type="button" role="tab" onclick="switchLoginTab('otp')" id="tabBtnOtp" class="is-active"><?= fnd_icon('phone', 16) ?><span>الجوال و OTP</span></button>
                    <button type="button" role="tab" onclick="switchLoginTab('password')" id="tabBtnPass"><?= fnd_icon('lock-keyhole', 16) ?><span>كلمة المرور</span></button>
                </div>

                <!-- OTP phone login -->
                <form id="formOtpLogin" action="<?= url('/login') ?>" method="POST" class="auth-form">
                    <input type="hidden" name="login_type" value="otp">
                    <fieldset>
                        <p class="auth-hint">أدخل رقم جوالك السعودي وسنرسل لك رمز OTP للدخول الفوري السريع. <strong>كود التجربة السريع: 123456</strong></p>
                        <div class="form-field">
                            <label for="loginPhone">رقم الجوال السعودي<span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('phone', 18) ?>
                                <input class="input" type="tel" name="phone" id="loginPhone" required dir="ltr" placeholder="0555123456" autocomplete="tel">
                            </div>
                        </div>
                    </fieldset>
                    <button type="submit" class="button button--primary auth-submit" style="margin-top:14px">إرسال كود التحقق ومتابعة الدخول<?= fnd_icon($arrow, 19, 'directional-arrow') ?></button>
                </form>
                <?php endif; ?>

                <!-- Password login -->
                <form id="formPassLogin" action="<?= url('/login') ?>" method="POST" class="auth-form<?= $otpEnabled ? ' hidden' : '' ?>">
                    <input type="hidden" name="login_type" value="password">
                    <fieldset>
                        <div class="form-field">
                            <label for="loginEmail">البريد الإلكتروني أو رقم الجوال<span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('user-round', 18) ?>
                                <input class="input" type="text" id="loginEmail" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required dir="ltr" placeholder="admin@tumurna.com أو 0555123456" autocomplete="username">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="loginPass">كلمة المرور<span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 18) ?>
                                <div class="password-input">
                                    <input class="input" type="password" id="loginPass" name="password" required dir="ltr" placeholder="كلمة المرور" autocomplete="current-password">
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= \App\Core\I18n::isRtl() ? 'إظهار كلمة المرور' : 'Show password' ?>" aria-pressed="false"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <button type="submit" class="button button--primary auth-submit" style="margin-top:14px">تسجيل الدخول<?= fnd_icon($arrow, 19, 'directional-arrow') ?></button>
                </form>

                <?php if ($otpEnabled): ?>
                <p class="auth-switch">ليس لديك حساب عميل حتى الآن؟ <a href="<?= url('/register') ?>">إنشاء حساب جديد بالهاتف</a></p>
                <?php endif; ?>
                <a class="auth-back" href="<?= url('/') ?>">العودة للصفحة الرئيسية للمتجر</a>
            </div>
        </div>
        <?php include __DIR__ . '/../components/auth_story.php'; ?>
    </section>
</div>

<script>
    function switchLoginTab(tab) {
        const formOtp = document.getElementById('formOtpLogin');
        const formPass = document.getElementById('formPassLogin');
        const tabBtnOtp = document.getElementById('tabBtnOtp');
        const tabBtnPass = document.getElementById('tabBtnPass');
        const otp = tab === 'otp';
        formOtp.classList.toggle('hidden', !otp);
        formPass.classList.toggle('hidden', otp);
        tabBtnOtp.classList.toggle('is-active', otp);
        tabBtnPass.classList.toggle('is-active', !otp);
    }

</script>
