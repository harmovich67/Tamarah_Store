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
                    <h1 id="auth-title"><?= $isEn ? 'Create an account' : 'إنشاء حساب جديد' ?></h1>
                    <p><?= $isEn ? 'Begin your journey with a unique shopping experience.' : 'ابدأ رحلتك معنا واستمتع بتجربة تسوق فريدة' ?></p>
                </header>

                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>

                <div id="authNotice" class="auth-feedback" style="display:none;margin-bottom:12px"></div>

                <form novalidate data-form="auth" class="auth-form" method="POST" action="<?= url('/register') ?>">
                    <fieldset>
                        <div class="auth-name-row">
                            <div class="form-field">
                                <label for="auth-first"><?= $isEn ? 'First name' : 'الاسم الأول' ?><span> *</span></label>
                                <div class="auth-input-wrap">
                                    <?= fnd_icon('user-round', 19) ?>
                                    <input id="auth-first" name="first" class="input" value="<?= htmlspecialchars($first ?? '') ?>" placeholder="<?= $isEn ? 'First name' : 'الاسم الأول' ?>" autocomplete="given-name" maxlength="50" required>
                                </div>
                            </div>
                            <div class="form-field">
                                <label for="auth-last"><?= $isEn ? 'Family name' : 'اسم العائلة' ?><span> *</span></label>
                                <div class="auth-input-wrap">
                                    <?= fnd_icon('user-round', 19) ?>
                                    <input id="auth-last" name="last" class="input" value="<?= htmlspecialchars($last ?? '') ?>" placeholder="<?= $isEn ? 'Family name' : 'اسم العائلة' ?>" autocomplete="family-name" maxlength="49" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="auth-phone"><?= $isEn ? 'Mobile number' : 'رقم الجوال' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('phone', 19) ?>
                                <input id="auth-phone" name="phone" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" class="input" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="05xxxxxxxx" maxlength="20" required>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="auth-email"><?= $isEn ? 'Email address (optional)' : 'البريد الإلكتروني (اختياري)' ?></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('mail', 19) ?>
                                <input id="auth-email" name="email" type="email" class="input" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="<?= $isEn ? 'Email address (optional)' : 'البريد الإلكتروني (اختياري)' ?>" autocomplete="email" maxlength="254">
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="auth-password"><?= $isEn ? 'Password' : 'كلمة المرور' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 19) ?>
                                <div class="password-input">
                                    <input id="auth-password" name="password" type="password" class="input" placeholder="<?= $isEn ? 'Password' : 'كلمة المرور' ?>" autocomplete="new-password" maxlength="128" required>
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $isEn ? 'Show password' : 'إظهار كلمة المرور' ?>"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                        </div>
                        <p class="auth-password-hint"><?= $isEn ? 'Use 8–128 characters, including a letter and a number.' : 'استخدم ٨ أحرف على الأقل، تشمل حرفًا ورقمًا (حتى ١٢٨ حرفًا).' ?></p>

                        <div class="form-field">
                            <label for="auth-confirm"><?= $isEn ? 'Confirm password' : 'تأكيد كلمة المرور' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 19) ?>
                                <div class="password-input">
                                    <input id="auth-confirm" name="confirm" type="password" class="input" placeholder="<?= $isEn ? 'Confirm password' : 'تأكيد كلمة المرور' ?>" autocomplete="new-password" maxlength="128" required>
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $isEn ? 'Show password' : 'إظهار كلمة المرور' ?>"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                        </div>

                        <label class="choice">
                            <input type="checkbox" name="terms" id="auth-terms" required checked>
                            <span><?= $isEn ? 'I agree to the terms and conditions and usage policy' : 'أوافق على الشروط والأحكام وسياسة الاستخدام' ?></span>
                        </label>

                        <details class="auth-policy">
                            <summary><?= $isEn ? 'Legal documents' : 'الوثائق القانونية' ?></summary>
                            <p><?= $isEn ? 'Review the terms and conditions and usage policy from the links in the site footer.' : 'يمكنك مراجعة الشروط والأحكام وسياسة الاستخدام من الروابط الموجودة أسفل الموقع.' ?></p>
                        </details>

                        <button type="button" class="button button--outline auth-demo-fill" onclick="fillRegisterDemo()">
                            <?= $isEn ? 'Fill demo details' : 'تعبئة بيانات تجريبية' ?>
                        </button>

                        <button type="submit" class="button button--primary auth-submit">
                            <?= $isEn ? 'Create account' : 'إنشاء الحساب' ?><?= fnd_icon($arrow, 20, 'directional-arrow') ?>
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
                    <?= $isEn ? 'Already have an account?' : 'لديك حساب بالفعل؟' ?>
                    <a href="<?= url('/login') ?>"><?= $isEn ? 'Sign in' : 'تسجيل الدخول' ?></a>
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
function fillRegisterDemo() {
    const rnd = String(Math.floor(10000000 + Math.random() * 90000000));
    document.getElementById('auth-first').value = '<?= $isEn ? "Sarah" : "سارة" ?>';
    document.getElementById('auth-last').value = '<?= $isEn ? "Al-Tamimi" : "التميمي" ?>';
    document.getElementById('auth-phone').value = '05' + rnd.slice(0, 8);
    document.getElementById('auth-email').value = 'demo+' + rnd.slice(0, 4) + '@tamrna.test';
    document.getElementById('auth-password').value = 'Tmrna2026';
    document.getElementById('auth-confirm').value = 'Tmrna2026';
    document.getElementById('auth-terms').checked = true;
}

function showAuthNotice(msg) {
    const box = document.getElementById('authNotice');
    if (box) {
        box.textContent = msg;
        box.style.display = 'block';
    }
}
</script>
