<?php
use App\Core\I18n;
$isRtl = I18n::isRtl();
$arrow = $isRtl ? 'arrow-left' : 'arrow-right';
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
                    <h1><?= __('customer_register') ?></h1>
                    <p>سجل برقم جوالك لتأكيد حسابك عبر رمز OTP ومتابعة شحناتك</p>
                </header>

                <p class="auth-hint" style="margin-bottom:12px"><strong>نظام التحقق التلقائي السريع:</strong> رمز التأكيد الموحد لجميع الأرقام هو <bdi dir="ltr"><strong>123456</strong></bdi></p>

                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>

                <form action="<?= url('/register') ?>" method="POST" class="auth-form">
                    <fieldset>
                        <div class="form-field">
                            <label for="regName"><?= __('full_name') ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('user-round', 18) ?>
                                <input class="input" type="text" id="regName" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required placeholder="<?= __('full_name') ?> — مثال: فهد بن ناصر التميمي" autocomplete="name">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="regPhone"><?= __('phone_number') ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('phone', 18) ?>
                                <input class="input" type="tel" id="regPhone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required dir="ltr" placeholder="0555123456" autocomplete="tel">
                            </div>
                            <span class="auth-password-hint" style="margin-top:2px">يبدأ بـ 05 ويتكون من 10 أرقام (يستخدم لتأكيد الشحن والـ OTP)</span>
                        </div>
                        <div class="form-field">
                            <label for="regEmail"><?= __('email_address') ?> (اختياري)</label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('mail', 18) ?>
                                <input class="input" type="email" id="regEmail" name="email" value="<?= htmlspecialchars($email ?? '') ?>" dir="ltr" placeholder="<?= __('email_address') ?> (اختياري)" autocomplete="email">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="regPass">كلمة المرور للحساب (اختياري)</label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 18) ?>
                                <div class="password-input">
                                    <input class="input" type="password" id="regPass" name="password" dir="ltr" placeholder="كلمة المرور (اختياري)" autocomplete="new-password">
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= \App\Core\I18n::isRtl() ? 'إظهار كلمة المرور' : 'Show password' ?>" aria-pressed="false"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                            <span class="auth-password-hint" style="margin-top:2px">إذا لم تحدد كلمة مرور يمكنك دائماً الدخول برقم جوالك عبر رمز OTP</span>
                        </div>
                    </fieldset>
                    <button type="submit" class="button button--primary auth-submit" style="margin-top:14px">إرسال رمز التحقق OTP وتأكيد الحساب<?= fnd_icon($arrow, 19, 'directional-arrow') ?></button>
                </form>

                <p class="auth-switch">لديك حساب مسجل بالفعل؟ <a href="<?= url('/login') ?>"><?= __('login') ?></a></p>
            </div>
        </div>
        <?php include __DIR__ . '/../components/auth_story.php'; ?>
    </section>
</div>
