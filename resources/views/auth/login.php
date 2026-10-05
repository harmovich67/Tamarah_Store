<?php
use App\Core\I18n;

$locale = I18n::getLocale();
$isRtl = I18n::isRtl();
$isEn = $locale === 'en';
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
$otpEnabled = \App\Services\OtpService::isEnabled();
$immersiveHeader = true;
$authLayout = true;
$reqPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
$adminLogin = str_ends_with($reqPath, '/admin/login');
$defaultTab = $adminLogin ? 'password' : ($otpEnabled ? 'otp' : 'password');

$headingText = $adminLogin
    ? ($isEn ? 'Administration Portal' : 'تسجيل دخول الإدارة')
    : ($isEn ? 'Customer Sign In' : __('customer_login', default: 'تسجيل دخول العملاء'));

$subheadingText = $adminLogin
    ? ($isEn ? 'Enter your credentials to access the store management dashboard' : 'أدخل بيانات حساب الإدارة للوصول إلى لوحة تحكم المتجر')
    : ($isEn ? 'Sign in to track luxury date orders, cold delivery, and addresses' : 'سجل دخولك لمتابعة طلبات التمور الفاخرة، الشحن وعناوين التوصيل');
?>
<div class="auth-page">
    <div class="auth-scene"><img src="<?= asset('assets/images/home/hero.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%"></div>
    <section class="auth-main">
        <div class="auth-card-column">
            <div class="auth-card">
                <header class="auth-card-heading">
                    <?= fnd_icon('tree-palm', 45, '', 1.2) ?>
                    <h1><?= htmlspecialchars($headingText) ?></h1>
                    <p><?= htmlspecialchars($subheadingText) ?></p>
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

                <div class="auth-tabs" id="loginTabs" role="tablist">
                    <?php if ($otpEnabled): ?>
                    <button type="button" role="tab" onclick="switchLoginTab('otp')" id="tabBtnOtp" class="<?= $defaultTab === 'otp' ? 'is-active' : '' ?>">
                        <?= fnd_icon('phone', 16) ?><span><?= $isEn ? 'Mobile & OTP' : 'الجوال و OTP' ?></span>
                    </button>
                    <?php endif; ?>
                    <button type="button" role="tab" onclick="switchLoginTab('password')" id="tabBtnPass" class="<?= $defaultTab === 'password' ? 'is-active' : '' ?>">
                        <?= fnd_icon('lock-keyhole', 16) ?><span><?= $isEn ? 'Password' : 'كلمة المرور' ?></span>
                    </button>
                </div>

                <?php if ($otpEnabled): ?>
                <!-- OTP phone login -->
                <form id="formOtpLogin" action="<?= url('/login') ?>" method="POST" class="auth-form<?= $defaultTab === 'otp' ? '' : ' hidden' ?>">
                    <input type="hidden" name="login_type" value="otp">
                    <fieldset>
                        <p class="auth-hint"><?= $isEn ? 'Enter your Saudi mobile number to receive an instant verification code (OTP).' : 'أدخل رقم جوالك السعودي وسنرسل لك رمز التحقق (OTP) للدخول الفوري السريع.' ?></p>
                        <div class="form-field">
                            <label for="loginPhone"><?= $isEn ? 'Saudi Mobile Number' : 'رقم الجوال السعودي' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('phone', 18) ?>
                                <input class="input" type="tel" name="phone" id="loginPhone" required dir="ltr" placeholder="05XXXXXXXX" autocomplete="tel">
                            </div>
                        </div>
                    </fieldset>
                    <button type="submit" class="button button--primary auth-submit" style="margin-top:14px">
                        <?= $isEn ? 'Send OTP & Continue' : 'إرسال كود التحقق ومتابعة الدخول' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                    </button>
                </form>
                <?php endif; ?>

                <!-- Password login -->
                <form id="formPassLogin" action="<?= url('/login') ?>" method="POST" class="auth-form<?= $defaultTab === 'password' ? '' : ' hidden' ?>">
                    <input type="hidden" name="login_type" value="password">
                    <fieldset>
                        <div class="form-field">
                            <label for="loginEmail"><?= $isEn ? 'Email or Mobile Number' : 'البريد الإلكتروني أو رقم الجوال' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('user-round', 18) ?>
                                <input class="input" type="text" id="loginEmail" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required dir="ltr" placeholder="<?= $isEn ? 'name@example.com or 05XXXXXXXX' : 'البريد الإلكتروني أو 05XXXXXXXX' ?>" autocomplete="username">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="loginPass"><?= $isEn ? 'Password' : 'كلمة المرور' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 18) ?>
                                <div class="password-input">
                                    <input class="input" type="password" id="loginPass" name="password" required dir="ltr" placeholder="<?= $isEn ? 'Enter your password' : 'أدخل كلمة المرور' ?>" autocomplete="current-password">
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $isEn ? 'Show password' : 'إظهار كلمة المرور' ?>" aria-pressed="false"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <button type="submit" class="button button--primary auth-submit" style="margin-top:14px">
                        <?= $isEn ? 'Sign In' : 'تسجيل الدخول' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                    </button>
                </form>

                <p class="auth-switch">
                    <?= $isEn ? "Don't have a customer account yet?" : 'ليس لديك حساب عميل حتى الآن؟' ?>
                    <a href="<?= url('/register') ?>"><?= $isEn ? 'Create new account via mobile' : 'إنشاء حساب جديد بالهاتف' ?></a>
                </p>
                <a class="auth-back" href="<?= url('/') ?>">
                    <?= $isEn ? 'Back to store home' : 'العودة للصفحة الرئيسية للمتجر' ?>
                </a>
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
        if (formOtp) formOtp.classList.toggle('hidden', !otp);
        if (formPass) formPass.classList.toggle('hidden', otp);
        if (tabBtnOtp) tabBtnOtp.classList.toggle('is-active', otp);
        if (tabBtnPass) tabBtnPass.classList.toggle('is-active', !otp);
    }
</script>
