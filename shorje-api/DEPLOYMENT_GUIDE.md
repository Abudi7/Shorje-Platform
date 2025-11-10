# دليل نشر منصة شورجي 🚀

## خيارات الاستضافة المجانية

### 1. Railway.app (موصى به) ⭐

**المميزات:**
- ✅ استضافة مجانية لـ PHP/Symfony
- ✅ قاعدة بيانات MySQL/PostgreSQL مجانية
- ✅ SSL مجاني
- ✅ نشر تلقائي من GitHub
- ✅ 500 ساعة مجانية شهرياً

**خطوات النشر:**

#### أ. إنشاء حساب على Railway
1. اذهب إلى: https://railway.app
2. سجل الدخول باستخدام GitHub
3. وافق على الأذونات المطلوبة

#### ب. نشر المشروع
1. انقر على **"New Project"**
2. اختر **"Deploy from GitHub repo"**
3. اختر **"Abudi7/Shorje-Platform"**
4. Railway سيكتشف تلقائياً أن المشروع Symfony

#### ج. إضافة قاعدة البيانات
1. في dashboard المشروع، انقر **"New"**
2. اختر **"Database"** → **"Add MySQL"**
3. Railway سيضيف قاعدة البيانات تلقائياً

#### د. إعداد المتغيرات البيئية (Environment Variables)
في إعدادات المشروع، أضف المتغيرات التالية:

```bash
# App Settings
APP_ENV=prod
APP_SECRET=your-secret-key-here-change-this-to-random-string

# Database (سيتم ملؤها تلقائياً من MySQL plugin)
DATABASE_URL=mysql://user:password@host:3306/railway

# Mailer
MAILER_DSN=smtp://user:pass@smtp.mailtrap.io:2525

# JWT Authentication
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your-jwt-passphrase

# Mercure
MERCURE_URL=https://demo.mercure.rocks/.well-known/mercure
MERCURE_PUBLIC_URL=https://demo.mercure.rocks/.well-known/mercure
MERCURE_JWT_SECRET=!ChangeThisMercureHubJWTSecretKey!

# Language Settings (already configured)
DEFAULT_LOCALE=ar
```

#### هـ. توليد مفاتيح JWT
1. في terminal الخاص بك (local):
```bash
cd shorje-api
php bin/console lexik:jwt:generate-keypair
```

2. انسخ محتوى الملفات:
- `config/jwt/private.pem`
- `config/jwt/public.pem`

3. في Railway، أضف هذه المتغيرات:
```bash
JWT_PRIVATE_KEY=-----BEGIN PRIVATE KEY-----
(paste private key content here)
-----END PRIVATE KEY-----

JWT_PUBLIC_KEY=-----BEGIN PUBLIC KEY-----
(paste public key content here)
-----END PUBLIC KEY-----
```

#### و. النشر
1. Railway سيبدأ النشر تلقائياً
2. انتظر حتى يكتمل النشر (2-5 دقائق)
3. سيظهر لك رابط التطبيق مثل: `https://shorje-platform-production.up.railway.app`

---

### 2. Render.com (بديل ممتاز)

**المميزات:**
- ✅ استضافة مجانية لـ PHP
- ✅ قاعدة بيانات PostgreSQL مجانية
- ✅ SSL مجاني
- ✅ نشر تلقائي من GitHub

**خطوات النشر:**

#### أ. إنشاء حساب
1. اذهب إلى: https://render.com
2. سجل الدخول باستخدام GitHub

#### ب. إنشاء Web Service
1. انقر **"New +"** → **"Web Service"**
2. اربط GitHub repository: **Abudi7/Shorje-Platform**
3. اختر:
   - **Name:** shorje-platform
   - **Environment:** Docker (أو PHP إذا متوفر)
   - **Branch:** main
   - **Root Directory:** shorje-api

#### ج. إعداد Build & Start Commands
```bash
# Build Command:
composer install --no-dev --optimize-autoloader && php bin/console cache:clear --env=prod && php bin/console doctrine:migrations:migrate --no-interaction

# Start Command:
php -S 0.0.0.0:$PORT -t public
```

#### د. إضافة قاعدة البيانات
1. انقر **"New +"** → **"PostgreSQL"**
2. اختر **"Free"**
3. انسخ رابط الاتصال (Connection String)

#### هـ. إضافة Environment Variables
نفس المتغيرات المذكورة أعلاه في Railway.

---

### 3. InfinityFree (استضافة PHP تقليدية مجانية)

**المميزات:**
- ✅ استضافة مجانية تماماً
- ✅ PHP 8.x مدعوم
- ✅ MySQL مجاني
- ✅ لوحة تحكم cPanel
- ⚠️ محدودية في الموارد

**الرابط:** https://infinityfree.net

---

## بعد النشر

### 1. تشغيل Migrations
```bash
# على Railway أو Render
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. إنشاء مستخدم admin
```bash
php bin/console app:create-admin admin@shorje.iq password123
```

### 3. التحقق من اللغات
- العربية (الافتراضية): `https://your-app.com/?locale=ar`
- الإنجليزية: `https://your-app.com/?locale=en`

---

## روابط مفيدة

- **Railway Dashboard:** https://railway.app/dashboard
- **Render Dashboard:** https://dashboard.render.com
- **توثيق Symfony Deployment:** https://symfony.com/doc/current/deployment.html

---

## المشاكل الشائعة وحلولها

### مشكلة: خطأ في Database Connection
**الحل:** تأكد من أن `DATABASE_URL` صحيح في Environment Variables

### مشكلة: 500 Internal Server Error
**الحل:** 
1. تحقق من logs في Railway/Render
2. تأكد من أن `APP_ENV=prod`
3. نفذ: `php bin/console cache:clear --env=prod`

### مشكلة: JWT Token لا يعمل
**الحل:** تأكد من وجود مفاتيح JWT في Environment Variables

---

## الخطوة التالية: مشاركة الرابط

بعد النشر بنجاح، سيكون لديك رابط مثل:
- **Railway:** `https://shorje-platform-production.up.railway.app`
- **Render:** `https://shorje-platform.onrender.com`

شارك هذا الرابط مع أصدقائك للاختبار! 🎉

---

**ملاحظة:** تذكر أن الخطة المجانية لها حدود:
- Railway: 500 ساعة/شهر
- Render: النوم بعد 15 دقيقة من عدم النشاط

للإنتاج الفعلي، يُنصح بالترقية إلى خطة مدفوعة.

