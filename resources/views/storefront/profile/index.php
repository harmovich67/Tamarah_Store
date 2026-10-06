<?php
use App\Core\I18n;

$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$currency = $en ? 'SAR' : 'ر.س';

$accountActive = 'profile';
$accountHeading = $en ? 'Profile' : 'الملف الشخصي';
$accountSub = $en ? 'Manage your details, addresses and orders in one place.' : 'يسعدنا وجودك معنا. راجع بياناتك وعناوينك وطلباتك من مكان واحد.';
include __DIR__ . '/../../components/account_shell_open.php';
?>
<?php if (!empty($success)): ?>
    <div class="flash flash--success" role="status" style="margin-bottom:14px"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="flash flash--error" role="alert" style="margin-bottom:14px"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
<?php endif; ?>

<div class="account-profile-grid">
    <!-- Card 1: Personal Information -->
    <section class="account-card">
        <header>
            <span><?= fnd_icon('user-round', 21) ?></span>
            <h2><?= $en ? 'Personal information' : 'المعلومات الشخصية' ?></h2>
        </header>
        <form action="<?= url('/profile/update-info') ?>" method="POST" class="account-form" id="profileInfoForm">
            <div class="form-field">
                <label for="account-name"><?= $en ? 'Full name' : 'الاسم الكامل' ?> *</label>
                <input id="account-name" class="input" type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required readonly>
            </div>
            <div class="form-field">
                <label for="account-email"><?= $en ? 'Email (optional)' : 'البريد الإلكتروني (اختياري)' ?></label>
                <input id="account-email" class="input" type="email" name="email" dir="ltr" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
            </div>
            <div class="form-field">
                <label for="account-phone"><?= $en ? 'Mobile number' : 'رقم الجوال' ?> *</label>
                <input id="account-phone" class="input" type="tel" name="phone" dir="ltr" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required readonly>
            </div>
            <div id="profile-save-container" style="display:none;margin-top:6px">
                <button type="submit" class="button button--primary" style="width:100%">
                    <?= fnd_icon('check', 18) ?><?= $en ? 'Save changes' : 'حفظ التعديلات' ?>
                </button>
            </div>
        </form>
    </section>

    <!-- Card 2: Change Password -->
    <section class="account-card">
        <header>
            <span><?= fnd_icon('lock-keyhole', 21) ?></span>
            <h2><?= $en ? 'Change password' : 'تغيير كلمة المرور' ?></h2>
        </header>
        <?php if (!empty($passError)): ?>
            <div class="field-error" role="alert" style="margin-bottom:10px"><?= htmlspecialchars($passError) ?></div>
        <?php endif; ?>
        <form action="<?= url('/profile/change-password') ?>" method="POST" class="account-form">
            <div class="form-field">
                <label for="account-current"><?= $en ? 'Current password' : 'كلمة المرور الحالية' ?> *</label>
                <div class="password-input">
                    <input id="account-current" class="input" type="password" name="currentPassword" autocomplete="current-password" required placeholder="••••••••">
                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $en ? 'Show password' : 'إظهار كلمة المرور' ?>"><?= fnd_icon('eye', 18) ?></button>
                </div>
            </div>
            <div class="form-field">
                <label for="account-new"><?= $en ? 'New password' : 'كلمة المرور الجديدة' ?> *</label>
                <div class="password-input">
                    <input id="account-new" class="input" type="password" name="password" autocomplete="new-password" required placeholder="••••••••">
                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $en ? 'Show password' : 'إظهار كلمة المرور' ?>"><?= fnd_icon('eye', 18) ?></button>
                </div>
            </div>
            <div class="form-field">
                <label for="account-confirm"><?= $en ? 'Confirm new password' : 'تأكيد كلمة المرور الجديدة' ?> *</label>
                <div class="password-input">
                    <input id="account-confirm" class="input" type="password" name="confirm" autocomplete="new-password" required placeholder="••••••••">
                    <button type="button" class="button button--icon" data-password-toggle aria-label="<?= $en ? 'Show password' : 'إظهار كلمة المرور' ?>"><?= fnd_icon('eye', 18) ?></button>
                </div>
            </div>
            <button type="submit" class="button button--primary" style="margin-top:6px;width:100%">
                <?= fnd_icon('lock', 16) ?><?= $en ? 'Update password' : 'تحديث كلمة المرور' ?>
            </button>
        </form>
    </section>
</div>

<!-- Section: My Addresses -->
<section class="account-card account-wide-card">
    <header>
        <span><?= fnd_icon('map-pin', 21) ?></span>
        <h2><?= $en ? 'My addresses' : 'عناويني' ?></h2>
    </header>
    <div class="account-card-actions">
        <a class="button button--outline" href="<?= url('/profile/addresses') ?>">
            <?= fnd_icon('plus', 16) ?><?= $en ? 'Add new address' : 'إضافة عنوان جديد' ?>
        </a>
    </div>

    <?php if (!empty($addresses)): ?>
        <div class="account-address-grid">
            <?php foreach ($addresses as $index => $address): 
                $isDefault = !empty($address['is_default']) || $index === 0;
            ?>
                <article class="<?= $isDefault ? 'is-default' : '' ?>">
                    <span><?= $isDefault ? ($en ? 'Default address' : 'العنوان الافتراضي') : ($en ? 'Saved address' : 'عنوان محفوظ') ?></span>
                    <h3><?= esc_html($address['city'] ?: 'الرياض') ?></h3>
                    <p><?= esc_html($address['title'] ?: ($en ? 'Home' : 'المنزل')) ?> · <?= esc_html($address['street_address'] ?: ($address['address'] ?? '')) ?></p>
                    <?php if (!empty($address['landmark'])): ?>
                        <small><?= esc_html($address['landmark']) ?></small>
                    <?php endif; ?>
                    <div>
                        <a href="<?= url('/profile/addresses') ?>" class="button button--ghost"><?= fnd_icon('pencil', 14) ?><?= $en ? 'Edit' : 'تعديل' ?></a>
                        <?php if (!$isDefault): ?>
                            <form action="<?= url('/profile/addresses/default') ?>" method="POST" style="display:inline">
                                <input type="hidden" name="id" value="<?= $address['id'] ?>">
                                <button type="submit" class="button button--ghost"><?= fnd_icon('check', 14) ?><?= $en ? 'Set as default' : 'تعيين كافتراضي' ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="account-empty"><?= $en ? 'No saved addresses yet.' : 'لا توجد عناوين محفوظة بعد.' ?></p>
    <?php endif; ?>
</section>

<!-- Section: Recent Orders -->
<section class="account-card account-wide-card">
    <header>
        <span><?= fnd_icon('package', 21) ?></span>
        <h2><?= $en ? 'Recent orders' : 'أحدث طلباتي' ?></h2>
    </header>
    <div class="account-card-actions">
        <a class="button button--outline" href="<?= url('/profile/orders') ?>">
            <?= $en ? 'View all orders' : 'عرض جميع الطلبات' ?><?= fnd_icon($isRtl ? 'chevron-left' : 'chevron-right', 15) ?>
        </a>
    </div>

    <?php if (!empty($recentOrders)): ?>
        <div class="account-orders-table">
            <?php foreach ($recentOrders as $ro): 
                $statusKey = strtolower((string)($ro['shipping_status'] ?: 'confirmed'));
                $lines = $recentOrderItems[(int)$ro['id']] ?? [];
            ?>
                <details>
                    <summary>
                        <b dir="ltr">#<?= esc_html($ro['order_number']) ?></b>
                        <span><?= date('Y-m-d', strtotime($ro['created_at'])) ?></span>
                        <span><?= (int)$ro['items_count'] ?> <?= $en ? 'items' : 'منتج' ?></span>
                        <strong><?= number_format((float)$ro['total'], 2) ?> <?= $currency ?></strong>
                        <em data-status="<?= esc_attr($statusKey) ?>"><?= esc_html($ro['shipping_status'] ?: ($en ? 'Order confirmed' : 'الطلب مؤكد')) ?></em>
                        <i><?= $en ? 'View details' : 'عرض التفاصيل' ?></i>
                    </summary>
                    <div class="account-order-details">
                        <p><?= $en ? 'Refrigerated express shipping across Saudi Arabia.' : 'شحن مبرد فاخر وسريع لحفظ جودة التمور.' ?></p>
                        <?php if (!empty($lines)): ?>
                            <ul>
                                <?php foreach ($lines as $line): ?>
                                    <li>
                                        <span><?= (int)$line['quantity'] ?> × <?= esc_html($line['product_name']) ?></span>
                                        <strong><?= number_format((float)$line['price'] * (int)$line['quantity'], 2) ?> <?= $currency ?></strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="account-empty"><?= $en ? 'No orders yet.' : 'لا توجد طلبات مسجلة بعد.' ?></p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>

<script>
let isProfileEditing = false;
function toggleProfileEdit() {
    isProfileEditing = !isProfileEditing;
    const form = document.getElementById('profileInfoForm');
    const saveContainer = document.getElementById('profile-save-container');
    const textSpan = document.getElementById('account-toggle-edit-text');
    
    if (form) {
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            if (isProfileEditing) {
                input.removeAttribute('readonly');
                input.style.background = '#fff';
            } else {
                input.setAttribute('readonly', 'true');
                input.style.background = '';
            }
        });
    }
    
    if (saveContainer) {
        saveContainer.style.display = isProfileEditing ? 'block' : 'none';
    }
    
    if (textSpan) {
        textSpan.textContent = isProfileEditing ? '<?= $en ? "Cancel edit" : "إلغاء التعديل" ?>' : '<?= $en ? "Edit profile" : "تعديل البيانات" ?>';
    }
}
</script>
