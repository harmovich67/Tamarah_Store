<?php
use App\Core\I18n;

$locale = I18n::getLocale();
$isRtl = I18n::isRtl();
$isEn = $locale === 'en';
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
                    <h1><?= $isEn ? 'Create Customer Account' : __('customer_register', default: 'إنشاء حساب عميل جديد') ?></h1>
                    <p><?= $isEn ? 'Register with your Saudi mobile number to verify via OTP and track orders' : 'سجل برقم جوالك لتأكيد حسابك عبر رمز OTP ومتابعة شحناتك' ?></p>
                </header>

                <?php if (!empty($error)): ?>
                    <div class="flash flash--error auth-flash" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
                <?php endif; ?>

                <form action="<?= url('/register') ?>" method="POST" class="auth-form">
                    <fieldset>
                        <div class="form-field">
                            <label for="regName"><?= $isEn ? 'Full Name' : __('full_name', default: 'الاسم الكامل') ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('user-round', 18) ?>
                                <input class="input" type="text" id="regName" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required placeholder="<?= $isEn ? 'Full Name — e.g. Fahad Al-Tamimi' : 'الاسم الكامل — مثال: فهد بن ناصر التميمي' ?>" autocomplete="name">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="regPhone"><?= $isEn ? 'Saudi Mobile Number' : __('phone_number', default: 'رقم الجوال السعودي') ?><span> *</span></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('phone', 18) ?>
                                <input class="input" type="tel" id="regPhone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required dir="ltr" placeholder="05XXXXXXXX" autocomplete="tel">
                            </div>
                            <span class="auth-password-hint" style="margin-top:2px"><?= $isEn ? 'Starts with 05 (10 digits) - used for delivery and OTP verification' : 'يبدأ بـ 05 ويتكون من 10 أرقام (يستخدم لتأكيد الشحن والـ OTP)' ?></span>
                        </div>
                        <div class="form-field">
                            <label for="regEmail"><?= $isEn ? 'Email Address (Optional)' : __('email_address', default: 'البريد الإلكتروني') . ' (اختياري)' ?></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('mail', 18) ?>
                                <input class="input" type="email" id="regEmail" name="email" value="<?= htmlspecialchars($email ?? '') ?>" dir="ltr" placeholder="<?= $isEn ? 'name@example.com (optional)' : 'البريد الإلكتروني (اختياري)' ?>" autocomplete="email">
                            </div>
                        </div>
                        <div class="form-field">
                            <label for="regPass"><?= $isEn ? 'Account Password (Optional)' : 'كلمة المرور للحساب (اختياري)' ?></label>
                            <div class="auth-input-wrap">
                                <?= fnd_icon('lock-keyhole', 18) ?>
                                <div class="password-input">
                                    <input class="input" type="password" id="regPass" name="password" dir="ltr" placeholder="<?= $isEn ? 'Create a password (optional)' : 'كلمة المرور (اختياري)' ?>" autocomplete="new-password">
                                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $isEn ? 'Show password' : 'إظهار كلمة المرور' ?>" aria-pressed="false"><?= fnd_icon('eye', 18) ?></button>
                                </div>
                            </div>
                            <span class="auth-password-hint" style="margin-top:2px"><?= $isEn ? 'If omitted, you can always sign in with your phone via OTP' : 'إذا لم تحدد كلمة مرور يمكنك دائماً الدخول برقم جوالك عبر رمز OTP' ?></span>
                        </div>
                    </fieldset>
                    <button type="submit" class="button button--primary auth-submit" style="margin-top:14px">
                        <?= $isEn ? 'Send OTP Code & Create Account' : 'إرسال رمز التحقق OTP وتأكيد الحساب' ?><?= fnd_icon($arrow, 19, 'directional-arrow') ?>
                    </button>
                </form>

                <p class="auth-switch"><?= $isEn ? 'Already have an account?' : 'لديك حساب مسجل بالفعل؟' ?> <a href="<?= url('/login') ?>"><?= $isEn ? 'Sign In' : __('login', default: 'تسجيل الدخول') ?></a></p>
                <a class="auth-back" href="<?= url('/') ?>"><?= $isEn ? 'Back to store home' : 'العودة للصفحة الرئيسية للمتجر' ?></a>
            </div>
        </div>
        <?php include __DIR__ . '/../components/auth_story.php'; ?>
    </section>
</div>
