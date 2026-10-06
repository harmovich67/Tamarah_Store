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
                    <h1 id="auth-title"><?= $isEn ? 'Forgot password?' : 'نسيت كلمة المرور؟' ?></h1>
                    <p><?= $isEn ? 'Enter your registered mobile. If the account exists, a verification code will be sent.' : 'أدخل جوالك المسجّل. إذا كان الحساب موجودًا، سيصلك رمز التحقق.' ?></p>
                </header>

                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>

                <form novalidate data-form="auth" class="auth-form" method="POST" action="<?= url('/forgot-password') ?>">
                    <fieldset>
                        <div class="form-field">
                            <label for="auth-phone"><?= $isEn ? 'Mobile number' : 'رقم الجوال' ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('phone', 19) ?>
                                <input id="auth-phone" name="phone" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" class="input" value="<?= htmlspecialchars($phone ?? '') ?>" placeholder="05xxxxxxxx" maxlength="20" required>
                            </div>
                        </div>

                        <button type="submit" class="button button--primary auth-submit">
                            <?= $isEn ? 'Send verification code' : 'إرسال رمز التحقق' ?><?= fnd_icon($arrow, 20, 'directional-arrow') ?>
                        </button>
                    </fieldset>
                </form>

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
