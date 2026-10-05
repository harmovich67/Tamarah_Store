<?php
// Shared floating "How to use ACF" help button + modal, included from
// admin/custom_fields/index.php and admin/custom_fields/form.php.
// Expects $locale / $isRtl to already be defined by the including view.
?>
<!-- Floating ACF Help Button -->
<button type="button" onclick="document.getElementById('acfHelpModal').classList.remove('hidden')"
        class="fixed bottom-6 <?= $isRtl ? 'left-6' : 'right-6' ?> z-40 w-14 h-14 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white shadow-2xl shadow-indigo-600/40 flex items-center justify-center transition transform hover:scale-105 active:scale-95"
        title="<?= $locale === 'ar' ? 'شرح استخدام الحقول المخصصة (ACF)' : 'How to use Custom Fields (ACF)' ?>">
    <i data-lucide="graduation-cap" class="w-6 h-6"></i>
</button>

<!-- ACF Help Modal -->
<div id="acfHelpModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-3xl w-full shadow-2xl border border-slate-100 max-h-[88vh] flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-100 p-6 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-lg"><?= $locale === 'ar' ? 'دليل استخدام الحقول المخصصة (ACF)' : 'Custom Fields (ACF) Usage Guide' ?></h3>
                    <p class="text-xs text-slate-400"><?= $locale === 'ar' ? 'كيف تبني صفحة مخصصة وتستدعي الحقول في الكود' : 'How to build a custom page and call fields in code' ?></p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('acfHelpModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center flex-shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-6 space-y-7 overflow-y-auto text-sm leading-relaxed text-slate-700">

            <?php if ($locale === 'ar'): ?>

                <!-- Step 1 -->
                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">1</span>
                        أنشئ مجموعة حقول واربطها بمكان محدد
                    </h4>
                    <p class="ps-8">
                        اضغط <b>"إضافة مجموعة حقول جديدة"</b>، اختر <b>"نوع الكائن المستهدف"</b> (صفحة / مقال / منتج تمور / صنف)،
                        ثم من <b>"قاعدة التعيين والظهور"</b> اختر إما <b>"الكل"</b> لتظهر الحقول في كل عناصر هذا النوع،
                        أو عنصراً بعينه (صفحة معينة، منتج معين، أو حتى نوع منشور كامل مثل "كل الأسئلة الشائعة") لتظهر الحقول هناك فقط.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">2</span>
                        أضف الحقول وحدد "المفتاح البرمجي" لكل حقل
                    </h4>
                    <p class="ps-8">
                        لكل حقل تضيفه (نص، صورة، أيقونة، مكرر...) يوجد <b>"المفتاح البرمجي (get_field)"</b> — هذا هو الاسم الذي ستستخدمه
                        لاحقاً في الكود لاستدعاء القيمة، مثال: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-mono text-xs" dir="ltr">hero_badge_text</code>.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">3</span>
                        املأ القيم من داخل الصفحة/المنتج نفسه
                    </h4>
                    <p class="ps-8">
                        بعد ربط المجموعة، افتح تعديل أي صفحة أو منتج مطابق لقاعدة التعيين — ستجد الحقول المخصصة تلقائياً
                        أسفل نموذج التعديل جاهزة للتعبئة والحفظ.
                    </p>
                </div>

                <!-- Step 4: code -->
                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">4</span>
                        استدعِ القيمة في القالب (Template) بالكود
                    </h4>
                    <p class="ps-8 mb-2">
                        هذا مثال حقيقي مأخوذ من <code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-mono text-xs" dir="ltr">resources/views/storefront/page.php</code>
                        (نفس القالب الذي تُعرض به صفحاتك الثابتة الآن):
                    </p>
                    <pre class="ps-8 bg-slate-900 text-slate-100 text-xs rounded-2xl p-4 overflow-x-auto font-mono leading-relaxed" dir="ltr"><code>// قيمة نصية بسيطة (يعرض الصفحة/المنتج الحالي تلقائياً)
$heroBadge = get_field('hero_badge_text') ?: 'نص افتراضي';

// قيمة لوني بها احتمال أن تكون فارغة
$accentColor = get_field('accent_color') ?: '#4f46e5';

// طباعة مباشرة بدون تخزين في متغير
&lt;?= the_field('hero_badge_text') ?&gt;

// حقل من نوع Repeater (مكرر) - مثل قائمة مميزات
&lt;?php if (have_rows('key_features')): ?&gt;
    &lt;?php while (have_rows('key_features')): the_row('key_features'); ?&gt;
        &lt;i class="&lt;?= get_sub_field('icon') ?&gt;"&gt;&lt;/i&gt;
        &lt;h4&gt;&lt;?= get_sub_field('title') ?&gt;&lt;/h4&gt;
        &lt;p&gt;&lt;?= get_sub_field('desc') ?&gt;&lt;/p&gt;
    &lt;?php endwhile; ?&gt;
&lt;?php endif; ?&gt;</code></pre>
                    <p class="ps-8 mt-2 text-xs text-slate-500">
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono" dir="ltr">get_field()</code> تكتشف تلقائياً الصفحة/المنشور/المنتج الحالي أثناء العرض
                        على المتجر، لكن يمكنك أيضاً تمرير معرّف محدد يدوياً: <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">get_field('key', $entityId, 'product')</code>.
                    </p>
                </div>

                <!-- Translations -->
                <div class="space-y-2 bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="languages" class="w-4 h-4 text-amber-600"></i>
                        الترجمة (عربي/إنجليزي) لقيم الحقول
                    </h4>
                    <p>
                        تسمية الحقل (Label) في لوحة التحكم لها نسخة عربية وإنجليزية للعرض على الأدمن فقط، لكن <b>قيمة</b> الحقل نفسها
                        تُخزَّن مرة واحدة بدون ترجمة تلقائية. إذا احتجت نصاً مختلفاً بكل لغة، أضف حقلين منفصلين بنفس الأسلوب
                        المستخدم في باقي المنصة (مثل <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">description_ar</code> /
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">description_en</code>)، مثال:
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">feature_title_ar</code> و
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">feature_title_en</code>،
                        ثم في الكود اختر المناسب حسب اللغة الحالية:
                    </p>
                    <pre class="bg-slate-900 text-slate-100 text-xs rounded-xl p-3 overflow-x-auto font-mono" dir="ltr"><code>$title = $locale === 'ar' ? get_field('feature_title_ar') : get_field('feature_title_en');</code></pre>
                </div>

                <!-- Field types reference -->
                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="list-checks" class="w-4 h-4 text-indigo-600"></i>
                        أنواع الحقول المتاحة
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">نص عادي</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">نص متعدد الأسطر</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">محرر HTML</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">رقمي</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">صورة</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">منتقي ألوان</span>
                        <span class="bg-indigo-50 text-indigo-700 rounded-lg px-3 py-2 font-bold">أيقونة (Font Awesome) 🎨</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">مفتاح نعم/لا</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">قائمة خيارات</span>
                        <span class="bg-amber-100 text-amber-800 rounded-lg px-3 py-2 font-bold">مكرر (Repeater) 🌟</span>
                    </div>
                </div>

            <?php else: ?>

                <!-- English version -->
                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">1</span>
                        Create a field group and attach it to a target
                    </h4>
                    <p class="ps-8">
                        Click <b>"Add New Field Group"</b>, pick a <b>"Target Object Type"</b> (Page / Post / Product / Date Category),
                        then under <b>"Assignment Rule"</b> choose <b>"All"</b> to show the fields everywhere of that type,
                        or a specific item (one page, one product, or even a whole post-type like "All FAQs") to scope it there only.
                    </p>
                </div>

                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">2</span>
                        Add fields and note each field's key
                    </h4>
                    <p class="ps-8">
                        Every field (text, image, icon, repeater...) has a <b>"get_field key"</b> — that's the name you'll use in
                        code, e.g. <code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-mono text-xs" dir="ltr">hero_badge_text</code>.
                    </p>
                </div>

                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">3</span>
                        Fill in values from the page/product itself
                    </h4>
                    <p class="ps-8">
                        Once attached, open any matching page/product for editing — the custom fields appear automatically
                        below the normal edit form, ready to fill in and save.
                    </p>
                </div>

                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black flex items-center justify-center flex-shrink-0">4</span>
                        Call the value in your template code
                    </h4>
                    <p class="ps-8 mb-2">
                        A real example from <code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-mono text-xs" dir="ltr">resources/views/storefront/page.php</code>
                        (the same template rendering your static pages right now):
                    </p>
                    <pre class="ps-8 bg-slate-900 text-slate-100 text-xs rounded-2xl p-4 overflow-x-auto font-mono leading-relaxed" dir="ltr"><code>// Simple value (auto-detects the current page/product being viewed)
$heroBadge = get_field('hero_badge_text') ?: 'Default text';

// A value that might be empty
$accentColor = get_field('accent_color') ?: '#4f46e5';

// Echo directly without storing in a variable
&lt;?= the_field('hero_badge_text') ?&gt;

// A Repeater field - e.g. a list of features
&lt;?php if (have_rows('key_features')): ?&gt;
    &lt;?php while (have_rows('key_features')): the_row('key_features'); ?&gt;
        &lt;i class="&lt;?= get_sub_field('icon') ?&gt;"&gt;&lt;/i&gt;
        &lt;h4&gt;&lt;?= get_sub_field('title') ?&gt;&lt;/h4&gt;
        &lt;p&gt;&lt;?= get_sub_field('desc') ?&gt;&lt;/p&gt;
    &lt;?php endwhile; ?&gt;
&lt;?php endif; ?&gt;</code></pre>
                    <p class="ps-8 mt-2 text-xs text-slate-500">
                        <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono" dir="ltr">get_field()</code> auto-detects the current page/post/product while rendering
                        the storefront, but you can also pass an id explicitly: <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">get_field('key', $entityId, 'product')</code>.
                    </p>
                </div>

                <div class="space-y-2 bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="languages" class="w-4 h-4 text-amber-600"></i>
                        Arabic/English translation of field values
                    </h4>
                    <p>
                        A field's Label has an Arabic and English version for the admin UI only, but the field's stored
                        <b>value</b> itself is saved once, with no automatic translation. If you need different text per
                        language, add two separate fields the same way the rest of the platform does it (like
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">description_ar</code> /
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">description_en</code>), e.g.
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">feature_title_ar</code> and
                        <code class="bg-white px-1.5 py-0.5 rounded font-mono text-[11px]" dir="ltr">feature_title_en</code>,
                        then pick the right one in code based on the current locale:
                    </p>
                    <pre class="bg-slate-900 text-slate-100 text-xs rounded-xl p-3 overflow-x-auto font-mono" dir="ltr"><code>$title = $locale === 'ar' ? get_field('feature_title_ar') : get_field('feature_title_en');</code></pre>
                </div>

                <div class="space-y-2">
                    <h4 class="font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="list-checks" class="w-4 h-4 text-indigo-600"></i>
                        Available field types
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Text</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Textarea</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">WYSIWYG HTML</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Number</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Image</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Color picker</span>
                        <span class="bg-indigo-50 text-indigo-700 rounded-lg px-3 py-2 font-bold">Icon (Font Awesome) 🎨</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Boolean switch</span>
                        <span class="bg-slate-100 rounded-lg px-3 py-2 font-bold">Select</span>
                        <span class="bg-amber-100 text-amber-800 rounded-lg px-3 py-2 font-bold">Repeater 🌟</span>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>
