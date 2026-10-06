<?php
/**
 * Tamrna Foundation - Gift card builder
 * Submits to /api/gift-cards/add: the card becomes a cart line, a gift card record is issued
 * at checkout and activated once the order is paid (see App\Services\GiftCardService).
 */
use App\Services\GiftCardService;

$locale = \App\Core\I18n::getLocale();
$pageCss = ['gift']; // page layer: foundation-gift.css
$isRtl = \App\Core\I18n::isRtl();
$en = $locale === 'en';
$cur = currency();
$authUser = \App\Core\Auth::user();
$amounts = GiftCardService::PRESET_AMOUNTS;
$cardFace = asset('assets/images/gift/gift-card-face.webp');
?>
<div class="gift-builder gift-builder--approved">
    <section class="gift-builder-hero" aria-labelledby="gift-builder-title">
        <img class="gift-builder-hero-bg" src="<?= asset('assets/images/home/hero.webp') ?>" alt="">
        <div class="gift-builder-hero-shade"></div>
        <div class="gift-builder-hero-art">
            <div class="gift-approved-card-stack">
                <div class="gift-approved-card gift-approved-card--compact">
                    <img src="<?= $cardFace ?>" alt="">
                </div>
            </div>
        </div>
        <div class="gift-builder-hero-copy">
            <?= fnd_icon('gift', 40, '', 1.5) ?>
            <span><?= $en ? 'Gift cards' : 'بطاقات الهدايا' ?></span>
            <h1 id="gift-builder-title"><?= $en ? "A gift from the land of goodness\nfor the ones you love" : "هدية من أرض الخير\nتصل لمن تحب" ?></h1>
            <p><?= $en ? "Choose the card value and add your personal touch.\nSend it to your loved ones any time." : "اختر قيمة البطاقة وأضف لمستك الخاصة.\nلتصل بها إلى من تحب في أي وقت." ?></p>
        </div>
    </section>

    <div class="gift-builder-layout container">
        <div class="gift-builder-main">
            <form id="gift-builder-form" novalidate>
                <input type="hidden" name="card_type" id="gb-card-type" value="digital">
                <input type="hidden" name="amount" id="gb-amount" value="<?= (int)$amounts[0] ?>">

                <!-- 1. Delivery -->
                <section class="gift-builder-section">
                    <header class="gift-builder-section-heading">
                        <span>1</span>
                        <div>
                            <h2><?= $en ? 'How should the gift arrive?' : 'كيف تريد أن تصل الهدية؟' ?></h2>
                            <p><?= $en ? 'Choose what suits the occasion' : 'اختر الطريقة الأنسب للمناسبة' ?></p>
                        </div>
                    </header>
                    <div class="gift-builder-delivery-options" role="radiogroup">
                        <button type="button" class="is-selected" data-card-type="digital" role="radio" aria-checked="true">
                            <?= fnd_icon('check', 14) ?>
                            <span><?= fnd_icon('smartphone', 22) ?></span>
                            <div>
                                <strong><?= $en ? 'Digital card' : 'بطاقة رقمية' ?></strong>
                                <p><?= $en ? 'A unique code with an elegant card by email or WhatsApp.' : 'كود فريد مع بطاقة أنيقة عبر البريد الإلكتروني أو الواتساب.' ?></p>
                            </div>
                        </button>
                        <button type="button" data-card-type="printed" role="radio" aria-checked="false">
                            <?= fnd_icon('check', 14) ?>
                            <span><?= fnd_icon('package', 22) ?></span>
                            <div>
                                <strong><?= $en ? 'Luxury printed card' : 'بطاقة مطبوعة فاخرة' ?></strong>
                                <p><?= $en ? 'Fine paper and elegant wrapping, delivered to the recipient.' : 'ورق فاخر وتغليف أنيق، تصل مباشرة إلى عنوان المستلم.' ?></p>
                            </div>
                        </button>
                    </div>
                </section>

                <!-- 2. Value -->
                <section class="gift-builder-section">
                    <header class="gift-builder-section-heading">
                        <span>2</span>
                        <div>
                            <h2><?= $en ? 'Choose the card value' : 'اختر قيمة البطاقة' ?></h2>
                            <p><?= $en ? 'Pick the right value for your loved one' : 'اختر القيمة المناسبة لمن تحب' ?></p>
                        </div>
                    </header>
                    <div class="gift-builder-values">
                        <?php foreach ($amounts as $i => $amt): ?>
                            <button type="button" class="<?= $i === 0 ? 'is-selected' : '' ?>" data-amount="<?= (int)$amt ?>"><b><?= (int)$amt ?></b><span><?= $cur ?></span></button>
                        <?php endforeach; ?>
                        <label id="gb-custom-label" tabindex="0">
                            <span><?= $en ? 'Other value' : 'قيمة أخرى' ?></span>
                            <?= fnd_icon('pencil', 16) ?>
                            <input id="gb-custom" type="number" min="<?= GiftCardService::MIN_AMOUNT ?>" max="<?= GiftCardService::MAX_AMOUNT ?>" placeholder="<?= $en ? 'Amount' : 'المبلغ' ?>" aria-label="<?= $en ? 'Other value' : 'قيمة أخرى' ?>">
                        </label>
                    </div>
                </section>

                <!-- 3. People -->
                <section class="gift-builder-section gift-builder-people-step">
                    <header class="gift-builder-section-heading">
                        <span>03</span>
                        <div>
                            <h2><?= $en ? 'From whom and to whom?' : 'ممن وإلى من؟' ?></h2>
                            <p><?= $en ? 'Add the sender and recipient details shown on the card' : 'أضف بيانات المرسل والمُهدى إليه التي ستظهر على البطاقة' ?></p>
                        </div>
                    </header>
                    <div class="gift-builder-details">
                        <fieldset class="gift-builder-sender">
                            <legend><?= fnd_icon('user-round', 17) ?> <?= $en ? 'Sender details' : 'بيانات المرسل' ?><?php if ($authUser): ?><span><?= $en ? 'From your account' : 'من حسابك' ?></span><?php endif; ?></legend>
                            <div class="form-field">
                                <label for="gb-sender"><?= $en ? 'Sender name' : 'اسم المرسل' ?><span> *</span></label>
                                <input id="gb-sender" name="sender_name" class="input" autocomplete="name" required value="<?= esc_attr($authUser['name'] ?? '') ?>">
                            </div>
                            <div class="form-field">
                                <label for="gb-sender-phone"><?= $en ? 'Sender mobile' : 'رقم جوال المرسل' ?></label>
                                <input id="gb-sender-phone" name="sender_phone" class="input" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="05xxxxxxxx" value="<?= esc_attr($authUser['phone'] ?? '') ?>">
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend><?= fnd_icon('gift', 17) ?> <?= $en ? 'Recipient details' : 'بيانات المُهدى إليه' ?></legend>
                            <div class="form-field">
                                <label for="gb-recipient"><?= $en ? 'Recipient name' : 'اسم المُهدى إليه' ?><span> *</span></label>
                                <input id="gb-recipient" name="recipient_name" class="input" autocomplete="off" required>
                            </div>
                            <div class="form-field">
                                <label for="gb-recipient-phone"><?= $en ? 'Recipient mobile' : 'رقم جوال المستلم' ?><span> *</span></label>
                                <input id="gb-recipient-phone" name="recipient_phone" class="input" type="tel" dir="ltr" inputmode="tel" autocomplete="off" placeholder="05xxxxxxxx" required>
                            </div>
                            <div class="form-field">
                                <label for="gb-recipient-email"><?= $en ? 'Recipient email (optional)' : 'البريد الإلكتروني للمستلم (اختياري)' ?></label>
                                <input id="gb-recipient-email" name="recipient_email" class="input" type="email" dir="ltr" autocomplete="off">
                            </div>
                        </fieldset>
                    </div>
                </section>

                <!-- 4. Message -->
                <section class="gift-builder-section gift-builder-message-step">
                    <header class="gift-builder-section-heading">
                        <span>4</span>
                        <div>
                            <h2><?= $en ? 'Gift message (optional)' : 'رسالة الإهداء (اختياري)' ?></h2>
                            <p><?= $en ? 'Add a personal message shown with the card' : 'أضف رسالة شخصية لتظهر مع البطاقة' ?></p>
                        </div>
                    </header>
                    <div class="gift-builder-message">
                        <textarea id="gb-message" name="message" maxlength="180" class="input textarea" aria-label="<?= $en ? 'Gift message' : 'رسالة الإهداء' ?>"></textarea>
                        <span id="gb-message-count">0/180</span>
                    </div>
                    <p class="field-error hidden" id="gb-error" role="alert"></p>
                    <button type="submit" class="button button--primary" id="gb-submit"><?= $en ? 'Send the gift card' : 'إرسال بطاقة الهدية' ?><?= fnd_icon('gift', 18) ?></button>
                </section>
            </form>
        </div>

        <aside class="gift-builder-preview">
            <header><h2><?= $en ? 'Card preview' : 'معاينة البطاقة' ?></h2></header>
            <div class="gift-builder-preview-stage">
                <div class="gift-approved-card"><img src="<?= $cardFace ?>" alt="<?= $en ? 'Tamrna gift card' : 'بطاقة إهداء تمرنا' ?>"></div>
            </div>
            <section class="gift-builder-summary">
                <h3><?= $en ? 'Card details' : 'تفاصيل البطاقة' ?></h3>
                <dl>
                    <div><dt><?= $en ? 'Card value' : 'قيمة البطاقة' ?></dt><dd id="pv-amount"><?= fnd_money($amounts[0]) ?></dd></div>
                    <div><dt><?= $en ? 'Card type' : 'نوع البطاقة' ?></dt><dd id="pv-type"><?= $en ? 'Digital card' : 'بطاقة رقمية' ?></dd></div>
                    <div><dt><?= $en ? 'From' : 'من المرسل' ?></dt><dd id="pv-from"><?= esc_html($authUser['name'] ?? '—') ?></dd></div>
                    <div><dt><?= $en ? 'To' : 'إلى المُهدى إليه' ?></dt><dd id="pv-to">—</dd></div>
                    <div><dt><?= $en ? 'Gift message (optional)' : 'رسالة الإهداء (اختياري)' ?></dt><dd id="pv-message"><?= $en ? 'Add your personal message' : 'أضف رسالتك الخاصة' ?></dd></div>
                </dl>
                <button type="button" id="gb-full-preview"><?= fnd_icon('eye', 18) ?><?= $en ? 'Full preview' : 'معاينة بشكل كامل' ?></button>
                <p class="gift-builder-policy"><?= $en
                    ? 'The code is issued after payment and can be redeemed in the cart promo-code field.'
                    : 'يصدر الكود بعد إتمام الدفع، ويُستخدم من خانة كوبون الخصم في سلة المشتريات.' ?></p>
            </section>
        </aside>
    </div>
</div>

<!-- Full preview -->
<div class="global-overlay hidden" id="gb-overlay"></div>
<div class="dialog hidden" id="gb-dialog" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" role="dialog" aria-modal="true" aria-labelledby="gb-dialog-title" style="max-width:40rem">
    <div class="dialog-heading">
        <h2 id="gb-dialog-title"><?= $en ? 'Card preview' : 'معاينة البطاقة' ?></h2>
        <button type="button" class="button button--icon" data-gb-close aria-label="<?= $en ? 'Close' : 'إغلاق' ?>"><?= fnd_icon('x', 20) ?></button>
    </div>
    <div class="gift-approved-card" style="margin-top:16px"><img src="<?= $cardFace ?>" alt=""></div>
    <dl class="gift-builder-review-list" id="gb-dialog-details"></dl>
</div>

<script>
(function () {
    const T = <?= json_encode([
        'digital' => $en ? 'Digital card' : 'بطاقة رقمية',
        'printed' => $en ? 'Luxury printed card' : 'بطاقة مطبوعة فاخرة',
        'messagePlaceholder' => $en ? 'Add your personal message' : 'أضف رسالتك الخاصة',
        'invalidAmount' => ($en ? 'Card value must be between ' : 'قيمة البطاقة يجب أن تكون بين ') . GiftCardService::MIN_AMOUNT . ($en ? ' and ' : ' و') . GiftCardService::MAX_AMOUNT . ' ' . $cur,
        'required' => $en ? 'Please complete the sender name, recipient name and recipient mobile.' : 'يرجى إكمال اسم المرسل واسم المُهدى إليه ورقم جوال المستلم.',
        'phone' => $en ? 'Recipient mobile must look like 05xxxxxxxx' : 'رقم جوال المستلم يجب أن يكون بالصيغة 05xxxxxxxx',
        'labels' => $en ? ['Card value', 'Card type', 'From', 'To', 'Message'] : ['قيمة البطاقة', 'نوع البطاقة', 'من', 'إلى', 'الرسالة'],
        'currency' => $cur,
        'min' => GiftCardService::MIN_AMOUNT,
        'max' => GiftCardService::MAX_AMOUNT,
    ], JSON_UNESCAPED_UNICODE) ?>;

    const form = document.getElementById('gift-builder-form');
    const typeInput = document.getElementById('gb-card-type');
    const amountInput = document.getElementById('gb-amount');
    const custom = document.getElementById('gb-custom');
    const customLabel = document.getElementById('gb-custom-label');
    const err = document.getElementById('gb-error');
    const $ = (id) => document.getElementById(id);
    const money = (n) => Number(n).toLocaleString('en-US') + ' ' + T.currency;

    function refresh() {
        $('pv-amount').textContent = amountInput.value ? money(amountInput.value) : '—';
        $('pv-type').textContent = T[typeInput.value];
        $('pv-from').textContent = $('gb-sender').value.trim() || '—';
        $('pv-to').textContent = $('gb-recipient').value.trim() || '—';
        const msg = $('gb-message').value.trim();
        $('pv-message').textContent = msg || T.messagePlaceholder;
        $('gb-message-count').textContent = $('gb-message').value.length + '/180';
    }

    document.querySelectorAll('[data-card-type]').forEach((btn) => btn.addEventListener('click', () => {
        document.querySelectorAll('[data-card-type]').forEach((b) => { b.classList.toggle('is-selected', b === btn); b.setAttribute('aria-checked', b === btn ? 'true' : 'false'); });
        typeInput.value = btn.dataset.cardType;
        refresh();
    }));

    document.querySelectorAll('[data-amount]').forEach((btn) => btn.addEventListener('click', () => {
        document.querySelectorAll('[data-amount]').forEach((b) => b.classList.toggle('is-selected', b === btn));
        customLabel.classList.remove('is-selected');
        custom.value = '';
        amountInput.value = btn.dataset.amount;
        refresh();
    }));

    function selectCustomAmount() {
        customLabel.classList.add('is-selected');
        document.querySelectorAll('[data-amount]').forEach((b) => b.classList.remove('is-selected'));
        amountInput.value = custom.value;
        custom.focus();
        refresh();
    }

    customLabel.addEventListener('click', () => selectCustomAmount());
    customLabel.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectCustomAmount();
        }
    });
    custom.addEventListener('input', () => {
        customLabel.classList.add('is-selected');
        document.querySelectorAll('[data-amount]').forEach((b) => b.classList.remove('is-selected'));
        amountInput.value = custom.value;
        refresh();
    });

    ['gb-sender', 'gb-recipient', 'gb-message'].forEach((id) => $(id).addEventListener('input', refresh));

    function showError(msg) { err.textContent = msg; err.classList.remove('hidden'); }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        err.classList.add('hidden');
        const amount = parseFloat(amountInput.value);
        if (!(amount >= T.min && amount <= T.max)) return showError(T.invalidAmount);
        if (!$('gb-sender').value.trim() || !$('gb-recipient').value.trim() || !$('gb-recipient-phone').value.trim()) return showError(T.required);
        if (!/^(\+?966|0)?5\d{8}$/.test($('gb-recipient-phone').value.replace(/[\s-]+/g, ''))) return showError(T.phone);

        const btn = $('gb-submit');
        btn.disabled = true;
        fetch(window.appUrl('/api/gift-cards/add'), { method: 'POST', body: new FormData(form) })
            .then((r) => r.json())
            .then((data) => {
                if (!data.success) return showError(data.message || 'Error');
                window.tumurnaSyncCartBadge && window.tumurnaSyncCartBadge(data.cart);
                window.showTumurnaToast(data.message, 'success');
                if (window.tumurnaOpenOverlay) window.tumurnaOpenOverlay('cart-drawer');
            })
            .catch(() => showError('Error'))
            .finally(() => { btn.disabled = false; });
    });

    // full preview dialog
    const overlay = $('gb-overlay'), dialog = $('gb-dialog');
    function openPreview() {
        const rows = [money(amountInput.value || 0), T[typeInput.value], $('gb-sender').value.trim() || '—', $('gb-recipient').value.trim() || '—', $('gb-message').value.trim() || '—'];
        $('gb-dialog-details').innerHTML = rows.map((v, i) => '<div><dt>' + T.labels[i] + '</dt><dd></dd></div>').join('');
        $('gb-dialog-details').querySelectorAll('dd').forEach((dd, i) => { dd.textContent = rows[i]; });
        overlay.classList.remove('hidden'); dialog.classList.remove('hidden');
    }
    function closePreview() { overlay.classList.add('hidden'); dialog.classList.add('hidden'); }
    $('gb-full-preview').addEventListener('click', openPreview);
    overlay.addEventListener('click', closePreview);
    dialog.querySelector('[data-gb-close]').addEventListener('click', closePreview);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePreview(); });

    refresh();
})();
</script>
