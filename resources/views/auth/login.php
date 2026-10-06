<?php
use App\Core\I18n;

$locale = I18n::getLocale();
$isRtl = I18n::isRtl();
$isEn = $locale === 'en';
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
$pageCss = ['auth'];
$authLayout = true;
$immersiveHeader = true;

$reqPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
$adminLogin = str_ends_with($reqPath, '/admin/login');

$headingText = $adminLogin
    ? ($isEn ? 'Administration Portal' : 'تسجيل دخول الإدارة')
    : ($isEn ? 'Sign in' : 'تسجيل الدخول');

$subheadingText = $adminLogin
    ? ($isEn ? 'Enter your credentials to access the store management dashboard' : 'أدخل بيانات حساب الإدارة للوصول إلى لوحة تحكم المتجر')
    : ($isEn ? 'Welcome back. Sign in to continue your journey.' : 'مرحبًا بعودتك، سجّل الدخول لمتابعة رحلتك.');
?>
<div class="auth-page">
    <div class="auth-scene"><img src="<?= asset('assets/images/home/hero.webp') ?>" alt="" loading="eager"></div>
    <div class="auth-cream"></div>
    <main id="main-content" class="auth-main">
        <div class="auth-card-column">
            <section class="auth-card" aria-labelledby="auth-title">
                <header class="auth-card-heading">
                    <?= fnd_icon('tree-palm', 24, '', 1.4) ?>
                    <h1 id="auth-title"><?= htmlspecialchars($headingText) ?></h1>
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

                <div id="authNotice" class="auth-feedback" style="display:none;margin-bottom:12px"></div>

                <form novalidate data-form="auth" class="auth-form" method="POST" action="<?= url($adminLogin ? '/admin/login' : '/login') ?>">
                    <fieldset>
                        <div class="form-field">
                            <label for="auth-identity"><?= $isEn ? 'Mobile number or email' : 'رقم الجوال أو البريد الإلكتروني' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('user-round', 19) ?>
                                <input id="auth-identity" name="identity" class="input" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="<?= $isEn ? 'Mobile number or email' : 'رقم الجوال أو البريد الإلكتروني' ?>" autocomplete="username" maxlength="254" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="auth-password"><?= $isEn ? 'Password' : 'كلمة المرور' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 19) ?>
                                <div class="password-input">
                                    <input id="auth-password" name="password" type="password" class="input" placeholder="<?= $isEn ? 'Password' : 'كلمة المرور' ?>" autocomplete="current-password" maxlength="128" required>
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $isEn ? 'Show password' : 'إظهار كلمة المرور' ?>"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                        </div>

                        <a class="auth-forgot-link" href="<?= url('/forgot-password') ?>">
                            <?= $isEn ? 'Forgot password?' : 'نسيت كلمة المرور؟' ?>
                        </a>

                        <button type="button" class="button button--outline auth-demo-fill" onclick="fillLoginDemo()">
                            <?= $isEn ? 'Fill demo details' : 'تعبئة بيانات تجريبية' ?>
                        </button>

                        <button type="submit" class="button button--primary auth-submit">
                            <?= $isEn ? 'Sign in' : 'تسجيل الدخول' ?><?= fnd_icon($arrow, 20, 'directional-arrow') ?>
                        </button>
                    </fieldset>
                </form>

                <div class="auth-social-divider">
                    <span><?= $isEn ? 'Or continue with' : 'أو المتابعة عبر' ?></span>
                </div>

                <div class="auth-social">
                    <button type="button" class="button button--outline" onclick="showAuthNotice('<?= $isEn ? 'Social sign-in is not connected yet.' : 'تسجيل الدخول الاجتماعي غير مفعّل حاليًا.' ?>')">
                        <?= fnd_icon('apple', 22) ?><?= $isEn ? 'Continue with Apple' : 'متابعة عبر Apple' ?>
                    </button>
                    <button type="button" class="button button--outline" onclick="showAuthNotice('<?= $isEn ? 'Social sign-in is not connected yet.' : 'تسجيل الدخول الاجتماعي غير مفعّل حاليًا.' ?>')">
                        <span class="google-letter" aria-hidden="true">G</span><?= $isEn ? 'Continue with Google' : 'متابعة عبر Google' ?>
                    </button>
                </div>

                <p class="auth-switch">
                    <?= $isEn ? 'New to Tamrna?' : 'ليس لديك حساب؟' ?>
                    <a href="<?= url('/register') ?>"><?= $isEn ? 'Create account' : 'إنشاء حساب جديد' ?></a>
                </p>

                <a class="auth-back" href="<?= url('/') ?>">
                    <?= $isEn ? 'Return to the store' : 'العودة للمتجر' ?>
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
function fillLoginDemo() {
    const identInput = document.getElementById('auth-identity');
    const passInput = document.getElementById('auth-password');
    if (identInput) identInput.value = 'demo@tamrna.test';
    if (passInput) passInput.value = 'Tmrna2026';
}

function showAuthNotice(msg) {
    const box = document.getElementById('authNotice');
    if (box) {
        box.textContent = msg;
        box.style.display = 'block';
    }
}
</script>
