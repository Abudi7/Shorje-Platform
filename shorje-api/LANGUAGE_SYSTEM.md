# نظام اللغات المتعددة - Shorje Platform

## نظرة عامة

تم تطبيق نظام لغات متعدد في منصة شورجي يدعم **العربية** (اللغة الأساسية) و **الإنجليزية**.

## الميزات

### 1. اللغة الافتراضية
- **العربية** هي اللغة الافتراضية للمنصة
- يتم تطبيق اتجاه RTL تلقائياً للعربية و LTR للإنجليزية

### 2. تبديل اللغة
- زر تبديل اللغة يظهر في الزاوية العلوية اليمنى (للعربية) أو اليسرى (للإنجليزية)
- يتم حفظ اللغة المختارة في:
  - الجلسة (Session) للزوار
  - قاعدة البيانات للمستخدمين المسجلين

### 3. أولوية تحديد اللغة
1. لغة المستخدم المفضلة (من قاعدة البيانات) للمستخدمين المسجلين
2. اللغة المحفوظة في الجلسة
3. اللغة الافتراضية (العربية)

## الملفات المتأثرة

### Controllers
- `src/Controller/LanguageController.php` - معالج تبديل اللغة

### Event Listeners
- `src/EventListener/LocaleListener.php` - مستمع لتطبيق اللغة على كل طلب

### Entities
- `src/Entity/User.php` - إضافة حقل `preferredLanguage`

### Templates
- `templates/base.html.twig` - القالب الأساسي مع دعم اللغات

### Translations
- `translations/ar/messages.ar.yaml` - الترجمات العربية
- `translations/en/messages.en.yaml` - الترجمات الإنجليزية

### Configuration
- `config/packages/framework.yaml` - إعدادات اللغة
- `config/packages/translation.yaml` - إعدادات الترجمة

## كيفية الاستخدام في القوالب

### استخدام الترجمة في Twig
```twig
{# ترجمة نص بسيط #}
{{ 'navigation.home'|trans }}

{# ترجمة مع متغيرات #}
{{ 'app.copyright'|trans({'%year%': "now"|date("Y")}) }}

{# تحديد اللغة الحالية #}
{{ app.request.locale }}

{# تحديد الاتجاه (RTL/LTR) #}
{{ app.request.locale == 'ar' ? 'rtl' : 'ltr' }}
```

## Routes

### تغيير اللغة
```
GET /change-language/{locale}
```
- `{locale}`: اللغة المطلوبة (`ar` أو `en`)
- يعيد التوجيه إلى الصفحة السابقة

## Database Schema

### User Table
تم إضافة حقل جديد:
```sql
preferred_language VARCHAR(5) NULL DEFAULT 'ar'
```

## إضافة ترجمات جديدة

### 1. إضافة مفاتيح الترجمة

في `translations/ar/messages.ar.yaml`:
```yaml
my_section:
  my_key: "النص بالعربية"
```

في `translations/en/messages.en.yaml`:
```yaml
my_section:
  my_key: "Text in English"
```

### 2. استخدام الترجمة في القالب
```twig
{{ 'my_section.my_key'|trans }}
```

## أفضل الممارسات

1. **دائماً استخدم مفاتيح الترجمة** بدلاً من كتابة النصوص مباشرة
2. **نظّم مفاتيح الترجمة** في أقسام منطقية (navigation, auth, products, etc.)
3. **استخدم أسماء واضحة** للمفاتيح
4. **حافظ على التزامن** بين ملفات اللغات المختلفة

## Testing

### تغيير اللغة يدوياً
```bash
# في المتصفح
http://localhost:8000/change-language/en  # للإنجليزية
http://localhost:8000/change-language/ar  # للعربية
```

### مسح الـ Cache
```bash
php bin/console cache:clear
```

## Troubleshooting

### المشكلة: الترجمات لا تظهر
**الحل:**
```bash
php bin/console cache:clear
php bin/console translation:extract --force ar
php bin/console translation:extract --force en
```

### المشكلة: اللغة لا تتغير
**الحل:**
1. تحقق من أن الجلسة تعمل بشكل صحيح
2. تحقق من route `app_change_language`
3. تأكد من وجود `LocaleListener`

## Future Enhancements

- [ ] إضافة لغات إضافية (Kurdish, Turkish, etc.)
- [ ] واجهة إدارة الترجمات من Dashboard
- [ ] API endpoints لتغيير اللغة
- [ ] رسائل البريد الإلكتروني متعددة اللغات
- [ ] محتوى قاعدة البيانات متعدد اللغات (للمنتجات، الفئات، إلخ)

---

**تم التطوير بواسطة:** فريق Shorje  
**التاريخ:** نوفمبر 2025

