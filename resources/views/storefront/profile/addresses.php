<?php
use App\Core\I18n;
$isRtl = I18n::isRtl();
$locale = $locale ?? I18n::getLocale();
$en = $locale === 'en';
$allowMultiple = ($settings['allow_multiple_addresses'] ?? '1') == '1';
$maxAddresses = (int)($settings['max_addresses_per_user'] ?? 10);
$canAdd = ($allowMultiple || count($addresses) === 0) && (count($addresses) < $maxAddresses);
$cities = $cities ?? \App\Controllers\CheckoutController::saudiCities();

$accountActive = 'addresses';
$accountHeading = $en ? 'National Addresses Book' : 'دفتر العناوين الوطنية للتوصيل';
$accountSub = $en ? 'Manage your saved delivery addresses for fast refrigerated shipping across Saudi Arabia' : 'إدارة عناوين التوصيل المبرد السريع لتمورك الملكية داخل كافة مدن المملكة';
if ($canAdd) {
    $accountAction = '<button type="button" onclick="openAddAddressModal()" class="button button--primary">' . fnd_icon('plus', 18) . ($en ? 'Add New Address' : 'إضافة عنوان وطني جديد') . '</button>';
} else {
    $accountAction = '<span class="badge badge--preorder">' . (!$allowMultiple ? ($en ? 'Single address limit' : 'مسموح بعنوان واحد فقط') : ($en ? 'Maximum addresses reached' : 'تم الوصول للحد الأقصى للعناوين')) . '</span>';
}
include __DIR__ . '/../../components/account_shell_open.php';

$titleIcons = ['العمل' => 'store', 'Work' => 'store', 'استراحة' => 'tree-palm', 'مزرعة' => 'tree-palm', 'Lounge' => 'tree-palm', 'إهداء' => 'gift', 'Gift' => 'gift'];
?>
<?php if (!empty($success)): ?>
    <div class="flash flash--success" role="status"><?= fnd_icon('check-circle', 18) ?><div><?= htmlspecialchars($success) ?></div></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="flash flash--error" role="alert"><?= fnd_icon('alert-circle', 18) ?><div><?= htmlspecialchars($error) ?></div></div>
<?php endif; ?>

<?php if (empty($addresses)): ?>
    <div class="account-card" style="text-align:center;padding:40px 20px">
        <?= fnd_icon('map-pin', 44, '', 1.3) ?>
        <h2 style="margin-top:10px"><?= $en ? 'No addresses saved yet' : 'لم تقم بحفظ أي عنوان وطني بعد' ?></h2>
        <p class="muted" style="margin:8px auto 18px;max-width:30rem;line-height:1.8"><?= $en ? 'Add your delivery addresses in Saudi cities for faster cold shipping checkout.' : 'أضف عنوانك الوطني لإنهاء طلباتك بشكل فوري مع خدمة الشحن المبرد حتى باب منزلك.' ?></p>
        <button type="button" onclick="openAddAddressModal()" class="button button--primary"><?= fnd_icon('plus', 18) ?><?= $en ? 'Add First Address' : 'إضافة عنوانك الأول' ?></button>
    </div>
<?php else: ?>
    <div class="account-address-grid">
        <?php foreach ($addresses as $addr): ?>
            <article class="<?= $addr['is_default'] ? 'is-default' : '' ?>">
                <?php if ($addr['is_default']): ?><span><?= $en ? 'Default' : 'الافتراضي' ?></span><?php endif; ?>
                <h3><?= fnd_icon($titleIcons[$addr['title']] ?? 'home', 16) ?> <?= htmlspecialchars($addr['title'] ?: ($en ? 'Home' : 'المنزل')) ?> · <small><?= htmlspecialchars($addr['city'] ?: 'الرياض') ?></small></h3>
                <p><b><?= htmlspecialchars($addr['recipient_name']) ?></b></p>
                <p dir="ltr" style="text-align:start"><?= htmlspecialchars($addr['phone']) ?></p>
                <p><?= htmlspecialchars($addr['street_address']) ?><?= !empty($addr['building_floor']) ? ' - ' . htmlspecialchars($addr['building_floor']) : '' ?></p>
                <?php if (!empty($addr['landmark'])): ?>
                    <small><?= fnd_icon('map-pin', 12) ?> <b><?= $en ? 'Landmark' : 'علامة مميزة' ?>:</b> <?= htmlspecialchars($addr['landmark']) ?></small>
                <?php endif; ?>
                <div>
                    <?php if (!$addr['is_default']): ?>
                        <form action="<?= url('/profile/addresses/default') ?>" method="POST" style="display:inline">
                            <input type="hidden" name="id" value="<?= $addr['id'] ?>">
                            <button type="submit" class="button button--ghost button--sm"><?= fnd_icon('check-circle', 14) ?><?= $en ? 'Set as Default' : 'تعيين كافتراضي' ?></button>
                        </form>
                    <?php else: ?>
                        <small style="color:#176044"><?= fnd_icon('shield-check', 13) ?> <?= $en ? 'Main Default' : 'العنوان المعتمد' ?></small>
                    <?php endif; ?>
                    <button type="button" class="button button--ghost button--sm" onclick="openEditAddressModal(<?= htmlspecialchars(json_encode($addr)) ?>)" title="<?= $en ? 'Edit' : 'تعديل' ?>"><?= fnd_icon('pencil', 14) ?><?= $en ? 'Edit' : 'تعديل' ?></button>
                    <form action="<?= url('/profile/addresses/delete') ?>" method="POST" style="display:inline" onsubmit="return confirm('<?= addslashes($en ? 'Are you sure you want to delete this address?' : 'هل أنت متأكد من رغبتك في حذف هذا العنوان؟') ?>');">
                        <input type="hidden" name="id" value="<?= $addr['id'] ?>">
                        <button type="submit" class="button button--ghost button--sm" style="color:var(--color-error)" title="<?= $en ? 'Delete' : 'حذف' ?>"><?= fnd_icon('trash-2', 14) ?><?= $en ? 'Delete' : 'حذف' ?></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../../components/account_shell_close.php'; ?>

<!-- Modal: Add / Edit Saudi National Address -->
<div class="global-overlay hidden" id="addressOverlay" onclick="closeAddressModal()"></div>
<div class="dialog hidden" id="addressModal" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" role="dialog" aria-modal="true" aria-labelledby="modalAddressTitle" style="max-width:36rem">
    <div class="dialog-heading">
        <div>
            <h2 id="modalAddressTitle"><?= $en ? 'Add Saudi National Address' : 'إضافة عنوان وطني للتوصيل' ?></h2>
            <p class="muted"><?= $en ? 'Shipping with temperature control across KSA' : 'توصيل مبرد لحفظ نضارة وجودة التمور' ?></p>
        </div>
        <button type="button" class="button button--icon" onclick="closeAddressModal()" aria-label="<?= $en ? 'Close' : 'إغلاق' ?>"><?= fnd_icon('x', 20) ?></button>
    </div>

    <form id="addressForm" action="<?= url('/profile/addresses/store') ?>" method="POST" class="account-form" style="margin-top:18px" novalidate onsubmit="return validateAddressForm(event)">
        <input type="hidden" id="addressId" name="id" value="">

        <fieldset class="radio-group address-labels">
            <legend><?= $en ? 'Address Label' : 'نوع ومسمى العنوان' ?></legend>
            <label class="choice"><input type="radio" name="title" value="المنزل" checked><span><?= $en ? 'Home' : 'المنزل' ?></span></label>
            <label class="choice"><input type="radio" name="title" value="العمل"><span><?= $en ? 'Office' : 'العمل' ?></span></label>
            <label class="choice"><input type="radio" name="title" value="استراحة"><span><?= $en ? 'Farm / Rest' : 'استراحة' ?></span></label>
            <label class="choice"><input type="radio" name="title" value="إهداء"><span><?= $en ? 'Gift' : 'إهداء' ?></span></label>
        </fieldset>

        <div class="form-field">
            <label for="recipientNameInput"><?= $en ? 'Recipient Full Name' : 'اسم المستلم الثلاثي' ?> *</label>
            <input type="text" id="recipientNameInput" name="recipient_name" class="input" value="<?= htmlspecialchars($user['name']) ?>" required
                   placeholder="<?= $en ? 'e.g. Fahad Al-Otaibi' : 'مثال: فهد بن عبد العزيز الناصر' ?>" oninput="validateRecipientName()">
            <span id="nameError" class="field-error hidden"></span>
        </div>

        <div class="form-field">
            <label for="phoneInput"><?= $en ? 'Saudi Mobile Number' : 'رقم الجوال السعودي' ?> * <span id="phoneBadge" class="field-hint">05XXXXXXXX</span></label>
            <input type="tel" id="phoneInput" name="phone" class="input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required dir="ltr" style="text-align:start"
                   placeholder="05XXXXXXXX" maxlength="14" oninput="validateSaudiPhone()">
            <span id="phoneError" class="field-error hidden"></span>
        </div>

        <div class="form-field">
            <label for="citySelect"><?= $en ? 'Saudi City / Region' : 'المدينة السعودية' ?> *</label>
            <select id="citySelect" name="city" class="input select" required onchange="validateCity()">
                <option value="">-- <?= $en ? 'Choose City' : 'اختر المدينة' ?> --</option>
                <?php foreach ($cities as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= $c === 'الرياض' ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="field-hint"><?= fnd_icon('snowflake', 12) ?> <?= $en ? 'Cold express delivery available for this city' : 'الشحن المبرد متوفر لكافة أحياء هذه المدينة' ?></span>
        </div>

        <div class="form-field">
            <label for="streetInput"><?= $en ? 'District & Street Name' : 'الحي واسم الشارع' ?> *</label>
            <input type="text" id="streetInput" name="street_address" class="input" required
                   placeholder="<?= $en ? 'e.g. Al Narjis District, Othman Bin Affan Rd' : 'مثال: حي النرجس، طريق عثمان بن عفان' ?>" oninput="validateStreet()">
            <span id="streetError" class="field-error hidden"></span>
        </div>

        <div class="checkout-fields" style="gap:10px">
            <div class="form-field">
                <label for="buildingInput"><?= $en ? 'Building / Villa No.' : 'رقم المبنى / الفيلا (اختياري)' ?></label>
                <input type="text" id="buildingInput" name="building_floor" class="input" placeholder="<?= $en ? 'e.g. Villa 14' : 'مثال: فيلا 14، الدور الثاني' ?>">
            </div>
            <div class="form-field">
                <label for="landmarkInput"><?= $en ? 'Nearest Landmark' : 'علامة مميزة (اختياري)' ?></label>
                <input type="text" id="landmarkInput" name="landmark" class="input" placeholder="<?= $en ? 'Near mosque or park' : 'بجوار جامع التقوى أو الحديقة' ?>">
            </div>
        </div>

        <label class="choice">
            <input type="checkbox" id="isDefaultCheck" name="is_default" value="1" <?= empty($addresses) ? 'checked' : '' ?>>
            <span><?= $en ? 'Set as default address for future orders' : 'تعيين كعنوان وطني رئيسي ومعتمد لجميع الطلبات القادمة' ?></span>
        </label>

        <div class="address-form-actions">
            <button type="button" class="button button--outline" onclick="closeAddressModal()"><?= $en ? 'Cancel' : 'إلغاء' ?></button>
            <button type="submit" id="submitAddressBtn" class="button button--primary"><?= fnd_icon('check', 18) ?><span id="submitAddressText"><?= $en ? 'Save National Address' : 'حفظ العنوان الوطني' ?></span></button>
        </div>
    </form>
</div>

<script>
    function showAddressModal() {
        document.getElementById('addressOverlay').classList.remove('hidden');
        document.getElementById('addressModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openAddAddressModal() {
        document.getElementById('modalAddressTitle').innerText = '<?= addslashes($en ? 'Add Saudi National Address' : 'إضافة عنوان وطني للتوصيل') ?>';
        document.getElementById('submitAddressText').innerText = '<?= addslashes($en ? 'Save National Address' : 'حفظ العنوان الوطني') ?>';
        document.getElementById('addressId').value = '';
        document.getElementById('recipientNameInput').value = '<?= addslashes($user['name'] ?? '') ?>';
        document.getElementById('phoneInput').value = '<?= addslashes($user['phone'] ?? '') ?>';
        document.getElementById('citySelect').value = 'الرياض';
        document.getElementById('streetInput').value = '';
        document.getElementById('buildingInput').value = '';
        document.getElementById('landmarkInput').value = '';
        document.getElementById('isDefaultCheck').checked = <?= empty($addresses) ? 'true' : 'false' ?>;

        resetValidationStates();
        validateRecipientName();
        validateSaudiPhone();
        showAddressModal();
    }

    function openEditAddressModal(addr) {
        document.getElementById('modalAddressTitle').innerText = '<?= addslashes($en ? 'Edit National Address' : 'تعديل العنوان الوطني') ?>';
        document.getElementById('submitAddressText').innerText = '<?= addslashes($en ? 'Update Address' : 'تحديث العنوان الوطني') ?>';
        document.getElementById('addressId').value = addr.id;
        document.getElementById('recipientNameInput').value = addr.recipient_name || '';
        document.getElementById('phoneInput').value = addr.phone || '';
        document.getElementById('citySelect').value = addr.city || 'الرياض';
        document.getElementById('streetInput').value = addr.street_address || '';
        document.getElementById('buildingInput').value = addr.building_floor || '';
        document.getElementById('landmarkInput').value = addr.landmark || '';
        document.getElementById('isDefaultCheck').checked = (addr.is_default == 1);

        const radios = document.getElementsByName('title');
        for (let r of radios) {
            if (r.value === addr.title) r.checked = true;
        }

        resetValidationStates();
        validateRecipientName();
        validateSaudiPhone();
        validateStreet();
        showAddressModal();
    }

    function closeAddressModal() {
        document.getElementById('addressOverlay').classList.add('hidden');
        document.getElementById('addressModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAddressModal(); });

    function setFieldState(input, err, ok, message) {
        if (ok) {
            input.removeAttribute('aria-invalid');
            input.setAttribute('data-success', 'true');
            if (err) err.classList.add('hidden');
        } else {
            input.setAttribute('aria-invalid', 'true');
            input.removeAttribute('data-success');
            if (err) { err.innerText = message; err.classList.remove('hidden'); }
        }
    }

    function resetValidationStates() {
        ['recipientNameInput', 'phoneInput', 'streetInput', 'citySelect'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.removeAttribute('aria-invalid'); el.removeAttribute('data-success'); }
        });
        ['nameError', 'phoneError', 'streetError'].forEach(id => {
            const err = document.getElementById(id);
            if (err) err.classList.add('hidden');
        });
    }

    function validateRecipientName() {
        const input = document.getElementById('recipientNameInput');
        const ok = input.value.trim().length >= 3;
        setFieldState(input, document.getElementById('nameError'), ok, '<?= addslashes($en ? 'Please enter at least 3 characters' : 'يرجى كتابة اسم المستلم الثلاثي (3 أحرف على الأقل)') ?>');
        return ok;
    }

    function validateSaudiPhone() {
        const input = document.getElementById('phoneInput');
        const badge = document.getElementById('phoneBadge');
        const val = input.value.replace(/[^\d+]/g, '').trim();

        // Saudi mobile pattern (05XXXXXXXX or +9665XXXXXXXX or 5XXXXXXXX)
        const ok = /^(05|5|\+9665|009665)[0-9]{8}$/.test(val);
        setFieldState(input, document.getElementById('phoneError'), ok, '<?= addslashes($en ? 'Must be a valid Saudi mobile starting with 05 (10 digits)' : 'يجب أن يبدأ الرقم بـ 05 ويتكون من 10 أرقام (مثال: 0555000000)') ?>');
        badge.innerText = ok ? '✓ رقم جوال سعودي معتمد' : 'صيغة غير مكتملة';
        badge.style.color = ok ? 'var(--color-success)' : 'var(--color-error)';
        return ok;
    }

    function validateCity() {
        const select = document.getElementById('citySelect');
        if (!select.value) { select.setAttribute('aria-invalid', 'true'); return false; }
        select.removeAttribute('aria-invalid');
        return true;
    }

    function validateStreet() {
        const input = document.getElementById('streetInput');
        const ok = input.value.trim().length >= 5;
        setFieldState(input, document.getElementById('streetError'), ok, '<?= addslashes($en ? 'Please provide detailed street and district' : 'يرجى كتابة اسم الحي والشارع بالتفصيل (مثال: حي الملقا، شارع أنس بن مالك)') ?>');
        return ok;
    }

    function validateAddressForm(e) {
        const isNameOk = validateRecipientName();
        const isPhoneOk = validateSaudiPhone();
        const isCityOk = validateCity();
        const isStreetOk = validateStreet();

        if (!isNameOk || !isPhoneOk || !isCityOk || !isStreetOk) {
            e.preventDefault();
            return false;
        }
        return true;
    }
</script>
