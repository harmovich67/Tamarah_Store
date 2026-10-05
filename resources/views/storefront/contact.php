<?php
/**
 * Tamrna Foundation - Contact Us & Branch Locator
 */
$locale = \App\Core\I18n::getLocale();
$isRtl = \App\Core\I18n::isRtl();

// Official channels come from the admin site settings
$cRows = \Database\Database::fetchAll("SELECT `key`, `value` FROM settings WHERE `key` IN ('contact_phone','contact_email','contact_whatsapp','whatsapp_number')");
$cSet = [];
foreach ($cRows as $r) $cSet[$r['key']] = $r['value'];
$contactPhone = $cSet['contact_phone'] ?? '8001248888';
$contactEmail = $cSet['contact_email'] ?? 'care@tumurna.com';
$waDigits = preg_replace('/\D+/', '', (string)($cSet['contact_whatsapp'] ?? ($cSet['whatsapp_number'] ?? '966500000000')));
$phoneHref = preg_replace('/[^\d+]/', '', (string)$contactPhone);

$branchesAr = [
    ['city' => 'الرياض', 'name' => 'فرع الرياض الرئيسي - طريق الملك فهد', 'address' => 'طريق الملك فهد، حي الملقا (مقابل البرج)، الرياض', 'phone' => '+966 11 400 2026', 'hours' => 'يومياً: ٨:٣٠ ص - ١١:٣٠ م', 'badge' => 'الفرع الرئيسي'],
    ['city' => 'جدة', 'name' => 'فرع جدة - الكورنيش الشمالي', 'address' => 'طريق الكورنيش الشمالي، حي الشاطئ، جدة', 'phone' => '+966 12 600 2026', 'hours' => 'يومياً: ٩:٠٠ ص - ١١:٣٠ م', 'badge' => 'صالة الضيافة'],
    ['city' => 'المدينة المنورة', 'name' => 'فرع المدينة المنورة - طريق سلطانة', 'address' => 'طريق سلطانة التجاري (قرب مسجد القبلتين)، المدينة المنورة', 'phone' => '+966 14 800 2026', 'hours' => 'يومياً: ٨:٠٠ ص - ١٢:٠٠ منتصف الليل', 'badge' => 'فرع الواحات والمزارع'],
];
$branchesEn = [
    ['city' => 'Riyadh', 'name' => 'Riyadh Flagship - King Fahd Road', 'address' => 'King Fahd Road, Al-Malqa (opposite Kingdom Tower), Riyadh', 'phone' => '+966 11 400 2026', 'hours' => 'Daily: 8:30 AM - 11:30 PM', 'badge' => 'Flagship boutique'],
    ['city' => 'Jeddah', 'name' => 'Jeddah - North Corniche', 'address' => 'North Corniche Road, Al-Shati District, Jeddah', 'phone' => '+966 12 600 2026', 'hours' => 'Daily: 9:00 AM - 11:30 PM', 'badge' => 'Tasting lounge'],
    ['city' => 'Madinah', 'name' => 'Madinah - Sultana Road', 'address' => 'Sultana Commercial Road (near Qiblatain Mosque), Madinah', 'phone' => '+966 14 800 2026', 'hours' => 'Daily: 8:00 AM - Midnight', 'badge' => 'Palm grove hub'],
];
$branches = $isRtl ? $branchesAr : $branchesEn;
foreach ($branches as $i => $b) {
    $branches[$i]['map'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(($isRtl ? 'تمرنا، ' : 'Tamrna, ') . $b['address']);
}

$alertMsg = $isRtl
    ? 'شكراً لتواصلك مع متجر تمرنا للتمور الفاخرة! تم استلام رسالتك بنجاح.'
    : 'Thank you for reaching out to Tumurna Luxury Dates! Your message has been received.';
?>
<div class="pattern-background contact-page">
    <section class="contact-hero">
        <img src="<?= asset('assets/images/home/heritage.webp') ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%">
        <div class="contact-hero-overlay"></div>
        <div class="container contact-hero-copy">
            <span class="commerce-kicker"><?= $isRtl ? 'خدمة تليق بضيافتكم' : 'Service worthy of your hospitality' ?></span>
            <h1><?= $isRtl ? 'نسعد بخدمتكم، بكل عناية' : 'We are happy to serve you, with care' ?></h1>
            <p><?= $isRtl
                ? 'للاستفسارات عن المنتجات والطلبات والتوصيل، تواصلوا معنا عبر القنوات الرسمية المعتمدة.'
                : 'For questions about products, orders and delivery, reach us through our official channels.' ?></p>
        </div>
    </section>

    <div class="container contact-content">
        <section class="contact-channels" aria-labelledby="contact-channels-title">
            <span><?= $isRtl ? 'قنوات التواصل الرسمية' : 'Official channels' ?></span>
            <h2 id="contact-channels-title"><?= $isRtl ? 'نحن بالقرب منكم' : 'We are close to you' ?></h2>
            <p><?= $isRtl ? 'تواصل معنا مباشرة عبر الهاتف أو البريد الإلكتروني، ويسعدنا متابعة استفسارك عبر واتساب.' : 'Reach us directly by phone or email, and we are happy to follow up on WhatsApp.' ?></p>

            <div class="contact-channel-grid">
                <article>
                    <?= fnd_icon('mail', 26, '', 1.6) ?>
                    <div>
                        <small><?= $isRtl ? 'البريد الإلكتروني الرسمي' : 'Official email' ?></small>
                        <a href="mailto:<?= esc_attr($contactEmail) ?>"><?= esc_html($contactEmail) ?></a>
                    </div>
                </article>
                <article>
                    <?= fnd_icon('phone', 26, '', 1.6) ?>
                    <div>
                        <small><?= $isRtl ? 'رقم التواصل الرسمي' : 'Official phone' ?></small>
                        <a dir="ltr" href="tel:<?= esc_attr($phoneHref) ?>"><?= esc_html($contactPhone) ?></a>
                    </div>
                </article>
                <article>
                    <?= fnd_icon('message-circle', 26, '', 1.6) ?>
                    <div>
                        <small><?= $isRtl ? 'متابعة الاستفسارات' : 'Follow-up on inquiries' ?></small>
                        <a dir="ltr" href="https://wa.me/<?= esc_attr($waDigits) ?>" target="_blank" rel="noopener noreferrer"><?= $isRtl ? 'واتساب' : 'WhatsApp' ?></a>
                    </div>
                </article>
            </div>
        </section>

        <div class="contact-lower">
            <section class="branch-locator" aria-labelledby="branch-locator-title">
                <header class="branch-locator-head">
                    <span class="commerce-kicker"><?= fnd_icon('map-pin', 15) ?><?= $isRtl ? 'شبكة فروع تمرنا في المملكة' : 'Tamrna branches across the Kingdom' ?></span>
                    <h2 id="branch-locator-title"><?= $isRtl ? 'تفضل بزيارة أقرب فرع إليك' : 'Visit the branch nearest to you' ?></h2>
                </header>

                <div class="branch-locator-body">
                    <ul class="branch-list">
                        <?php foreach ($branches as $i => $b): ?>
                            <li>
                                <button type="button" class="branch-item <?= $i === 0 ? 'branch-item--active' : '' ?>" data-branch="<?= $i ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                                    <strong><?= esc_html($b['name']) ?></strong>
                                    <em><?= esc_html($b['city']) ?></em>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="branch-map-col">
                        <div class="branch-map">
                            <article class="branch-map-card">
                                <span class="branch-map-badge" id="branch-badge"><?= $isRtl ? 'الفرع المحدد' : 'Selected branch' ?></span>
                                <h3 id="branch-name"><?= esc_html($branches[0]['name']) ?></h3>
                                <a class="button button--primary branch-map-link" id="branch-map-link" href="<?= esc_attr($branches[0]['map']) ?>" target="_blank" rel="noopener noreferrer">
                                    <?= fnd_icon('map-pin', 17) ?><?= $isRtl ? 'فتح الموقع في Google Maps' : 'Open in Google Maps' ?><?= fnd_icon('external-link', 15) ?>
                                </a>
                            </article>
                        </div>
                        <dl class="branch-details" aria-live="polite">
                            <div>
                                <?= fnd_icon('map-pin', 20) ?>
                                <div><dt><?= $isRtl ? 'العنوان التفصيلي' : 'Full address' ?></dt><dd id="branch-address"><?= esc_html($branches[0]['address']) ?></dd></div>
                            </div>
                            <div>
                                <?= fnd_icon('clock3', 20) ?>
                                <div><dt><?= $isRtl ? 'مواعيد العمل' : 'Opening hours' ?></dt><dd id="branch-hours"><?= esc_html($branches[0]['hours']) ?></dd></div>
                            </div>
                            <div>
                                <?= fnd_icon('phone', 20) ?>
                                <div><dt><?= $isRtl ? 'هاتف الفرع' : 'Branch phone' ?></dt><dd dir="ltr" style="text-align:start" id="branch-phone"><?= esc_html($branches[0]['phone']) ?></dd></div>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <form class="checkout-card contact-form" onsubmit="event.preventDefault(); showTumurnaToast(<?= esc_attr(json_encode($alertMsg)) ?>, 'success'); this.reset();">
                <div>
                    <span class="commerce-kicker"><?= $isRtl ? 'أرسل استفسارك' : 'Send your inquiry' ?></span>
                    <h2><?= $isRtl ? 'كيف يمكننا مساعدتك؟' : 'How can we help you?' ?></h2>
                </div>
                <div class="form-field">
                    <label for="ct-name"><?= $isRtl ? 'الاسم' : 'Name' ?><span> *</span></label>
                    <input id="ct-name" class="input" type="text" required autocomplete="name">
                </div>
                <div class="form-field">
                    <label for="ct-email"><?= $isRtl ? 'البريد الإلكتروني' : 'Email' ?><span> *</span></label>
                    <input id="ct-email" class="input" type="email" required autocomplete="email">
                </div>
                <div class="form-field">
                    <label for="ct-msg"><?= $isRtl ? 'رسالتك' : 'Your message' ?><span> *</span></label>
                    <textarea id="ct-msg" class="input textarea" required rows="4"></textarea>
                </div>
                <button type="submit" class="button button--primary"><?= $isRtl ? 'إرسال الاستفسار' : 'Send inquiry' ?></button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var branches = <?= json_encode($branches, JSON_UNESCAPED_UNICODE) ?>;
    var items = document.querySelectorAll('.branch-item');
    items.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var b = branches[parseInt(btn.getAttribute('data-branch'), 10)];
            if (!b) return;
            items.forEach(function (o) {
                var on = o === btn;
                o.classList.toggle('branch-item--active', on);
                o.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            document.getElementById('branch-name').textContent = b.name;
            document.getElementById('branch-address').textContent = b.address;
            document.getElementById('branch-hours').textContent = b.hours;
            document.getElementById('branch-phone').textContent = b.phone;
            document.getElementById('branch-map-link').href = b.map;
        });
    });
})();
</script>
