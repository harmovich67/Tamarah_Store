# نشر متجر تمرنا

المشروع تطبيق PHP يعمل على Apache مع `mod_rewrite` وقاعدة MySQL أو MariaDB. يتطلب PHP 8.1 أو أحدث مع امتدادات `pdo_mysql` و`mbstring` و`curl` و`json` و`openssl`. ملف `assets/css/admin.css` جاهز ضمن المستودع، فلا يلزم Node.js لتشغيل الموقع.

## التثبيت على السيرفر

1. اسحب فرع `main` بالأمر `git clone https://github.com/harmovich67/Tamarah_Store.git`، واجعل جذر الموقع يشير إلى مجلد المشروع الذي يحتوي على `index.php` و`.htaccess`.
2. أنشئ قاعدة بيانات ومستخدمًا بصلاحيات إنشاء الجداول وتعديلها.
3. انسخ `.env.example` إلى `.env` على السيرفر، واضبط `APP_URL` وبيانات `DB_*` وكلمة مرور قوية وفريدة في `ADMIN_PASSWORD` **قبل أول طلب للموقع**. لا ترفع `.env` إلى Git.
4. امنح مستخدم خادم الويب صلاحية الكتابة في `storage/` و`uploads/`. ينشئ التطبيق مجلدات التخزين المؤقت والسجلات تلقائيًا.
5. افتح الموقع مرة واحدة لتشغيل تهيئة الجداول والبيانات الأولية. حساب الإدارة الأول: `admin@tumurna.com` وكلمة المرور هي قيمة `ADMIN_PASSWORD` التي وضعتها على السيرفر.

إذا كانت روابط مثل `/login` ترجع 404 من Apache، استخدم `APP_ROUTE_MODE=path_info` في `.env` (وهو الوضع الافتراضي). ستصبح الروابط مثل `/index.php/login` وتعمل دون `mod_rewrite`. بعد تفعيل إعادة كتابة الروابط في Apache يمكن اختيار `APP_ROUTE_MODE=rewrite` للحصول على الروابط المختصرة.

### فتح الروابط مباشرة مثل `/admin/login`

يجب أن يشير `DocumentRoot` للدومين إلى المجلد الذي يحتوي على `index.php` و`.htaccess`، وأن يسمح Apache بقراءة `.htaccess`. مثال داخل إعدادات الـ VirtualHost (استبدل المسار بمسار المشروع الحقيقي):

```apache
DocumentRoot /home/USER/Tamarah_Store
<Directory /home/USER/Tamarah_Store>
    AllowOverride All
    Require all granted
</Directory>
```

فعّل `mod_rewrite` ثم أعد تحميل Apache إن كانت لديك صلاحية إدارة الخادم. يحتوي `.htaccess` أيضًا على `FallbackResource /index.php` لتوجيه الروابط عندما تكون وحدة `mod_dir` متاحة ولا تعمل `mod_rewrite`. بعد التأكد من أن `https://tamarah.wordpress.aait-d.com/admin/login` يعرض صفحة الدخول، اضبط `APP_ROUTE_MODE=rewrite` في `.env` على السيرفر حتى تستخدم الروابط والنماذج وطلبات API العناوين المباشرة. إذا استمر Apache في إرجاع 404 فلا يمكن إصلاح ذلك من PHP وحده؛ اطلب من مسؤول الاستضافة تفعيل `AllowOverride All` للدومين أو إضافة توجيه الطلبات إلى `index.php` في إعدادات الـ VirtualHost.

الدخول برمز الهاتف التجريبي معطل في الإنتاج لأن إرسال الرسائل الفعلي لم يُضبط بعد. استخدم بريد الإدارة وكلمة المرور التي حددتها في `ADMIN_PASSWORD`؛ تسجيل العملاء برمز الهاتف يحتاج ربط مزود رسائل حقيقي قبل تفعيله.

إذا لم تكن كلمة مرور المدير معروفة، شغّل أداة إعادة التعيين من تيرمنال السيرفر داخل المشروع. الأداة تقرأ كلمة المرور من الإدخال القياسي ولا تعرضها:

```bash
read -rsp 'New admin password: ' TAMARAH_ADMIN_PASS; echo
printf '%s\n' "$TAMARAH_ADMIN_PASS" | php scripts/reset_admin_password.php
unset TAMARAH_ADMIN_PASS
```

مثال لأوامر التحديث التالية على السيرفر من داخل مجلد المشروع:

```bash
git pull origin main
```

الصور الحالية في `uploads/` مرفوعة مع الكود لظهور الموقع. بعد بدء استقبال ملفات جديدة على السيرفر، احتفظ بنسخة احتياطية من `uploads/` وقاعدة البيانات قبل أي تحديث، فهما بيانات تشغيلية مستقلة عن Git. نسخ قواعد البيانات المحلية وملفات `.env` والسجلات وملفات التجارب غير موجودة في المستودع.

ملف `install.php` القديم غير مستخدم في هذه النسخة؛ تهيئة القاعدة تتم من `index.php` بعد ضبط `.env`.
