/* Arabic pages show Arabic-Indic digits (ar-SA), exactly like the Tamrna Foundation reference.
   Only visible text is converted: attributes, form values, scripts and anything that must stay
   machine-readable (phone numbers, e-mails, order numbers, links to mailto:/tel:) are left alone. */
(function () {
    'use strict';
    var MAP = '٠١٢٣٤٥٦٧٨٩';
    var SKIP_TAG = /^(SCRIPT|STYLE|TEXTAREA|INPUT|SELECT|OPTION|CODE|PRE|BDI|BDO|NOSCRIPT)$/;

    function skipped(node) {
        for (var el = node.parentNode; el && el.nodeType === 1; el = el.parentNode) {
            if (SKIP_TAG.test(el.tagName)) return true;
            if (el.getAttribute('dir') === 'ltr' || el.hasAttribute('data-keep-digits')) return true;
            if (el.tagName === 'A' && /^(mailto|tel):/i.test(el.getAttribute('href') || '')) return true;
        }
        return false;
    }

    var DATE = /^(\s*)(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::\d{2})?)?(\s*)$/;
    var dateOnly, dateTime;
    try {
        dateOnly = new Intl.DateTimeFormat('ar-SA', { dateStyle: 'medium', calendar: 'gregory' });
        dateTime = new Intl.DateTimeFormat('ar-SA', { dateStyle: 'medium', timeStyle: 'short', calendar: 'gregory' });
    } catch (e) { /* older browsers keep the plain digit conversion below */ }

    // Same formats as the reference: 2026-10-05 -> "٥ أكتوبر ٢٠٢٦", 95.00 -> "٩٥", 4.9 -> "٤٫٩".
    function localise(text) {
        var m = DATE.exec(text);
        if (m && dateOnly) {
            var d = new Date(+m[2], +m[3] - 1, +m[4], m[5] ? +m[5] : 0, m[6] ? +m[6] : 0);
            if (!isNaN(d)) return m[1] + (m[5] ? dateTime : dateOnly).format(d) + m[7];
        }
        return text
            .replace(/(\d)\.00(?!\d)/g, '$1')
            .replace(/(\d)\.(\d)/g, '$1٫$2')
            .replace(/[0-9]/g, function (d) { return MAP.charAt(+d); });
    }

    function convert(node) {
        var text = node.nodeValue;
        // Codes such as ORD-2026-8406 or addresses with latin words stay as typed.
        if (!/[0-9]/.test(text) || /[A-Za-z]/.test(text) || skipped(node)) return;
        var out = localise(text);
        if (out !== text) node.nodeValue = out;
    }

    function walk(root) {
        if (root.nodeType === 3) { convert(root); return; }
        if (root.nodeType !== 1 || SKIP_TAG.test(root.tagName)) return;
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        var batch = [], n;
        while ((n = walker.nextNode())) batch.push(n);
        batch.forEach(convert);
    }

    walk(document.body);

    // Cart drawer, toasts, badges and other client-rendered text.
    new MutationObserver(function (records) {
        records.forEach(function (r) {
            if (r.type === 'characterData') convert(r.target);
            else r.addedNodes.forEach(walk);
        });
    }).observe(document.body, { childList: true, subtree: true, characterData: true });
})();
