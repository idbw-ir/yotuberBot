# 🎬 Youtuber Bot - ربات تلگرام یوتیوبر

<div align="center">

![Version](https://img.shields.io/badge/version-2.1.0-purple)
![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![SQLite](https://img.shields.io/badge/SQLite-BunnyDB-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Status](https://img.shields.io/badge/status-stable-brightgreen)

**یک ربات تلگرام حرفه‌ای و کامل برای یوتیوبرها با پنل مدیریت پیشرفته**

[ویژگی‌ها](#-ویژگیها) • [نصب](#-نصب-و-راهاندازی) • [مستندات](#-مستندات) • [API](#-api-documentation)

</div>

---

## 📖 درباره پروژه

**Youtuber Bot** یک ربات تلگرام کامل و حرفه‌ای است که مخصوص یوتیوبرها و تولیدکنندگان محتوا طراحی شده. این ربات با پنل مدیریت پیشرفته، سیستم پرداخت، هوش مصنوعی و امکانات متنوع، تمام نیازهای یک یوتیوبر را برطرف می‌کند.

### 🎯 اهداف پروژه

- ✅ مدیریت آسان کاربران و پیام‌ها
- ✅ سیستم پرداخت یکپارچه با درگاه‌های ایرانی
- ✅ چت زنده با کاربران
- ✅ سیستم کلمات کلیدی هوشمند
- ✅ ارسال پیام دسته‌جمعی
- ✅ آمار و گزارشات پیشرفته
- ✅ پشتیبانی از هوش مصنوعی (OpenAI, Claude)

---

## ✨ ویژگی‌ها

### 🗄️ دیتابیس دوگانه (جدید در v2.1)
- [x] پشتیبانی از MySQL/MariaDB
- [x] پشتیبانی از Bunny Database (Turso/libSQL)
- [x] اسکیماهای مجزا برای MySQL, MySQL Lite, SQLite
- [x] سوییچ خودکار بین درایورها
- [x] نصب کاملاً خودکار بدون نیاز به SQL دستی

### 🎛️ نصب‌کننده تحت وب (جدید در v2.1)
- [x] نصب در ۶ مرحله با راهنمای کامل
- [x] بررسی خودکار پیش‌نیازها
- [x] پشتیبانی از MySQL و Bunny Database
- [x] تنظیم خودکار Webhook تلگرام
- [x] ساخت خودکار حساب ادمین
- [x] قفل امنیتی پس از نصب (install.lock)
- [x] حذف خودکار فایل‌های نصب‌کننده

### 🤖 ربات تلگرام
- [x] پاسخ خودکار با کلمات کلیدی
- [x] چت با هوش مصنوعی (OpenAI & Claude)
- [x] پشتیبانی از تمام انواع پیام (متن، عکس، ویدئو، فایل)
- [x] Inline Keyboard های حرفه‌ای
- [x] Callback Query مدیریت
- [x] تشخیص خودکار بلاک شدن کاربر
- [x] سیستم دستورات پیشرفته
- [x] تأیید امنیتی Webhook با Secret Token

### 👥 مدیریت کاربران
- [x] ثبت خودکار کاربران
- [x] سیستم VIP با آستانه دلخواه
- [x] بلاک/آن‌بلاک کاربران
- [x] جستجو و فیلتر پیشرفته
- [x] عملیات دسته‌جمعی
- [x] Export به CSV/JSON

### 💰 سیستم پرداخت
- [x] پشتیبانی از زرین‌پال
- [x] پشتیبانی از IDPay
- [x] پشتیبانی از NextPay
- [x] پشتیبانی از NowPayments (Crypto)
- [x] تأیید/رد خودکار
- [x] گزارشات مالی
- [x] ارتقای خودکار به VIP

### 💬 چت زنده
- [x] چت Real-time با کاربران
- [x] نمایش وضعیت آنلاین
- [x] Quick Reply ها
- [x] Emoji Picker
- [x] Export چت
- [x] یادداشت‌گذاری

### 📢 ارسال دسته‌جمعی
- [x] ۹ گروه هدف مختلف
- [x] ۷ نوع محتوا
- [x] کنترل کامل (Start/Pause/Resume/Cancel)
- [x] متغیرهای قالب
- [x] پیش‌نمایش پیام
- [x] Rate Limiting هوشمند
- [x] ارسال زمان‌بندی شده (Scheduled)

### ⚙️ تنظیمات
- [x] مدیریت تنظیمات از پنل
- [x] Backup/Restore با نسخه‌بندی
- [x] تاریخچه تغییرات
- [x] Reset به پیش‌فرض
- [x] اعتبارسنجی هوشمند

### 📊 آمار و گزارشات
- [x] داشبورد کامل
- [x] نمودارهای تعاملی (Chart.js)
- [x] KPI های کلیدی
- [x] مقایسه دوره‌ها
- [x] برترین‌ها
- [x] گزارشات سفارشی

### 🔒 امنیت
- [x] CSRF Protection
- [x] XSS Protection
- [x] SQL Injection Prevention
- [x] Rate Limiting
- [x] Session Security
- [x] Password Hashing (bcrypt)
- [x] Security Headers
- [x] IP Whitelist
- [x] Webhook Secret Token Verification
- [x] Remember Me / Persistent Login
- [x] Activity Logging (Audit Trail)
- [x] سطوح دسترسی (super_admin, admin, editor, moderator)

### 🎨 رابط کاربری
- [x] طراحی مدرن و زیبا
- [x] Dark Theme
- [x] Glass Morphism
- [x] Responsive Design
- [x] انیمیشن‌های نرم
- [x] فونت وزیر
- [x] TailwindCSS

---

## 📋 پیش‌نیازها

### سرور
- **PHP** >= 8.0 (توصیه: 8.1 یا بالاتر)
- **MySQL** >= 5.7 (توصیه: 8.0) یا **Bunny Database** (Turso/libSQL)
- **Apache** با mod_rewrite یا **Nginx**
- **SSL Certificate** (الزامی برای تلگرام)
- **Composer** (برای مدیریت وابستگی‌ها)

### PHP Extensions (MySQL)
```bash
pdo
pdo_mysql
curl
json
mbstring
openssl
fileinfo
```

### PHP Extensions (Bunny Database / SQLite)
```bash
curl
json
mbstring
openssl
fileinfo
```
برای Bunny Database نیاز به PDO و MySQL نیست، فقط需要有 cURL و JSON.

### بررسی پیش‌نیازها
```bash
# بررسی نسخه PHP
php -v

# بررسی Extensions
php -m | grep -E 'pdo|curl|json|mbstring|openssl|fileinfo'

# بررسی Composer
composer --version
```

---

## 🚀 نصب و راه‌اندازی

> **روش پیشنهادی:** از نصب‌کننده تحت وب استفاده کنید (۶ مرحله، ساده و سریع)

---

### روش ۱: نصب خودکار با Web Installer ✅ (پیشنهادی)

این روش جدید در v2.1 اضافه شده و تمام مراحل را به صورت خودکار انجام می‌دهد.

#### 1. آپلود فایل‌ها
فایل‌های پروژه را در هاست خود آپلود کنید (دایرکتوری `public/` به عنوان Web Root).

#### 2. اجرای نصب‌کننده
در مرورگر به آدرس زیر بروید:
```
https://yourdomain.com/install.php
```

#### 3. طی کردن ۶ مرحله
| مرحله | عنوان | توضیح |
|-------|-------|-------|
| ۱ | پیش‌نیازها | بررسی خودکار PHP, Extensions, دسترسی‌ها |
| ۲ | دیتابیس | انتخاب MySQL یا Bunny Database + اطلاعات اتصال |
| ۳ | ربات تلگرام | وارد کردن توکن ربات و آیدی ادمین |
| ۴ | حساب ادمین | ساخت نام کاربری و رمز عبور ادمین |
| ۵ | تنظیمات | نام سایت، URL، کلید هوش مصنوعی |
| ۶ | اتمام | نصب نهایی، تنظیم Webhook و قفل‌سازی |

#### 4. پس از نصب
- پیام **"تبریک! نصب با موفقیت انجام شد"** نمایش داده می‌شود
- روی **"حذف فایل‌های نصب"** کلیک کنید (امنیت)
- سپس وارد **پنل مدیریت** شوید

---

### روش ۲: نصب دستی (پیشرفته)

#### 1. Clone پروژه

```bash
# Clone از Git
git clone https://github.com/idbw-ir/youtuber-bot.git
cd youtuber-bot

# یا دانلود ZIP
wget https://github.com/idbw-ir/youtuber-bot/archive/refs/heads/main.zip
unzip main.zip
cd youtuber-bot-main
```

#### 2. نصب وابستگی‌ها

```bash
# نصب Composer dependencies
composer install

# تنظیم permissions
chmod -R 775 storage/
chmod -R 775 public/
chown -R www-data:www-data storage/
chown -R www-data:www-data public/
```

#### 3. ساخت دیتابیس

##### MySQL:
```bash
# ورود به MySQL
mysql -u root -p

# اجرای دستورات SQL
CREATE DATABASE youtuber_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'youtuber_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON youtuber_bot.* TO 'youtuber_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# اجرای Schema (نسخه کامل)
mysql -u youtuber_user -p youtuber_bot < database/schema.sql

# یا نسخه سبک (بدون Trigger, Event, Procedure)
mysql -u youtuber_user -p youtuber_bot < database/schema.lite.sql
```

##### Bunny Database (Turso/libSQL):
از اسکیما SQLite-ready استفاده کنید:
```bash
# فایل database/schema.sqlite.sql برای Bunny/Turso سازگار است
```

#### 4. تنظیم Config

فایل `config/config.php` را از روی `config/config.example.php` بسازید و ویرایش کنید:

```php
<?php
return [
    // تنظیمات برنامه
    'app' => [
        'name' => 'یوتیوبر بات',
        'url' => 'https://yourdomain.com',
        'timezone' => 'Asia/Tehran',
        'debug' => false,
        'version' => '2.1.0',
    ],
    
    // تنظیمات دیتابیس
    'database' => [
        'driver' => 'mysql', // یا 'bunny' برای Bunny Database
        'host' => 'localhost',
        'name' => 'youtuber_bot',
        'user' => 'youtuber_user',
        'pass' => 'YourStrongPassword123!',
        'charset' => 'utf8mb4',
        // اگر driver = bunny:
        // 'bunny_url' => 'https://your-db.turso.io',
        // 'bunny_token' => 'your-bunny-database-token',
    ],
    
    // تنظیمات تلگرام
    'telegram' => [
        'bot_token' => '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz',
        'admin_id' => 123456789, // آیدی عددی ادمین
        'webhook_secret' => 'your_random_secret_string',
        'verify_ssl' => true,
    ],
    
    // تنظیمات هوش مصنوعی
    'ai' => [
        'enabled' => false,
        'provider' => 'openai', // یا 'claude'
        'api_key' => '',
        'model' => 'gpt-4o-mini',
    ],
    
    // تنظیمات درگاه پرداخت
    'zarinpal_merchant_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    'idpay_api_key' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
];
```

#### 5. تنظیم Webhook

```bash
# تنظیم Webhook تلگرام
curl -F "url=https://yourdomain.com/webhook.php" \
     -F "secret_token=your_random_secret_string" \
     -F "allowed_updates=[\"message\",\"callback_query\",\"my_chat_member\"]" \
     https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook

# بررسی وضعیت
curl https://api.telegram.org/botYOUR_BOT_TOKEN/getWebhookInfo
```

#### 6. بررسی نصب

```bash
# تست اتصال به دیتابیس
php -r "
require 'config/bootstrap.php';
\$db = App\Core\Database::getInstance();
echo '✅ Database connection successful!\n';
"

# تست ارسال پیام
php -r "
require 'config/bootstrap.php';
\$bot = new App\Telegram\Bot();
\$result = \$bot->sendMessage(YOUR_ADMIN_ID, '✅ Bot is working!');
echo \$result ? '✅ Message sent!' : '❌ Failed!';
"
```

---

## 📁 ساختار پروژه

```
youtuber-bot/
│
├── 📁 app/                          # کدهای اصلی برنامه
│   ├── 📁 Admin/                    # کلاس‌های پنل ادمین
│   │   ├── Auth.php                 # احراز هویت
│   │   ├── Dashboard.php            # داشبورد
│   │   ├── Users.php                # مدیریت کاربران
│   │   ├── Messages.php             # مدیریت پیام‌ها
│   │   ├── Donations.php            # مدیریت دونیت‌ها
│   │   ├── Keywords.php             # کلمات کلیدی
│   │   ├── Broadcast.php            # ارسال دسته‌جمعی
│   │   ├── Settings.php             # تنظیمات
│   │   ├── Chat.php                 # چت زنده
│   │   └── Statistics.php           # آمار
│   │
│   ├── 📁 Api/                      # API های عمومی
│   │   ├── DonationCallback.php     # کال‌بک درگاه
│   │   └── StatisticsApi.php        # API آمار
│   │
│   ├── 📁 AI/                       # هوش مصنوعی
│   │   ├── OpenAI.php               # اتصال به OpenAI
│   │   └── Claude.php               # اتصال به Claude
│   │
│   ├── 📁 Core/                     # هسته سیستم
│   │   ├── Autoloader.php           # بارگذاری خودکار کلاس‌ها
│   │   ├── Cache.php                # سیستم کش
│   │   ├── Config.php               # مدیریت تنظیمات
│   │   ├── Database.php             # مدیریت دیتابیس (MySQL + Bunny)
│   │   ├── DatabaseBunny.php        # درایور Bunny Database (Turso/libSQL)
│   │   ├── Logger.php               # سیستم لاگ
│   │   ├── Router.php               # مسیریابی
│   │   └── Session.php              # مدیریت Session
│   │
│   ├── 📁 Helpers/                  # توابع کمکی
│   │   ├── Security.php             # امنیت
│   │   └── Validator.php            # اعتبارسنجی
│   │
│   ├── 📁 Telegram/                 # ربات تلگرام
│   │   ├── Bot.php                  # کلاس اصلی ربات
│   │   ├── Webhook.php              # پردازش Webhook
│   │   ├── Keyboard.php             # Inline Keyboard
│   │   └── Commands.php             # مدیریت دستورات
│   │
│   └── helpers.php                  # توابع کمکی Global
│
├── 📁 config/                       # فایل‌های پیکربندی
│   ├── bootstrap.php                # بارگذاری اولیه
│   ├── config.php                   # تنظیمات اصلی
│   ├── config.example.php           # نمونه تنظیمات (با placeholders)
│   └── routes.php                   # تعریف Route ها
│
├── 📁 database/                     # فایل‌های دیتابیس
│   ├── schema.sql                   # ساختار کامل MySQL (با Trigger, Event)
│   ├── schema.lite.sql              # نسخه سبک MySQL (بدون Trigger, Event)
│   └── schema.sqlite.sql            # ساختار SQLite (برای Bunny/Turso)
│
├── 📁 installer/                    # نصب‌کننده تحت Web (جدید v2.1)
│   ├── 📁 assets/
│   │   ├── style.css                # استایل نصب‌کننده
│   │   └── script.js                # اسکریپت‌های نصب‌کننده
│   ├── 📁 classes/
│   │   ├── Database.php             # کلاس دیتابیس نصب‌کننده
│   │   ├── Installer.php            # کلاس اصلی نصب
│   │   └── Validator.php            # اعتبارسنجی فرم‌ها
│   └── 📁 steps/
│       ├── 1-requirements.php       # بررسی پیش‌نیازها
│       ├── 2-database.php           # تنظیم دیتابیس
│       ├── 3-telegram.php           # تنظیم ربات تلگرام
│       ├── 4-admin.php              # ساخت حساب ادمین
│       ├── 5-settings.php           # تنظیمات سایت
│       └── 6-finish.php             # اتمام و پاک‌سازی
│
├── 📁 public/                       # فایل‌های عمومی (Web Root)
│   ├── 📁 admin/                    # پنل ادمین
│   │   ├── index.php                # داشبورد
│   │   ├── login.php                # لاگین
│   │   ├── logout.php               # خروج
│   │   ├── 📁 partials/
│   │   │   └── sidebar.php          # سایدبار
│   │   └── .htaccess                # تنظیمات Apache ادمین
│   │
│   ├── index.php                    # Front Controller
│   └── webhook.php                  # Webhook تلگرام
│
├── 📁 resources/                    # منابع
│   ├── 📁 views/                    # قالب‌ها
│   │   ├── 📁 layouts/
│   │   │   ├── admin.php            # Layout اصلی
│   │   │   └── sidebar.php          # سایدبار
│   │   └── 📁 admin/
│   │       ├── dashboard.php
│   │       ├── users.php
│   │       ├── chat.php
│   │       ├── messages.php
│   │       ├── donations.php
│   │       ├── keywords.php
│   │       ├── broadcast.php
│   │       ├── settings.php
│   │       ├── login.php
│   │       └── profile.php
│   │
│   └── 📁 assets/                   # Assets اصلی
│       ├── 📁 css/
│       │   └── app.css
│       └── 📁 js/
│           ├── app.js
│           └── chart.js
│
├── 📁 storage/                      # فایل‌های ذخیره‌سازی
│   ├── 📁 logs/                     # لاگ‌ها
│   ├── 📁 cache/                    # کش
│   ├── 📁 exports/                  # فایل‌های خروجی
│   ├── 📁 backups/                  # Backup ها
│   └── 📁 uploads/                  # فایل‌های آپلود
│
├── 📁 vendor/                       # وابستگی‌های Composer
│
├── install.php                      # فایل اصلی نصب‌کننده تحت وب
├── install.lock                     # قفل امنیتی نصب (پس از نصب ایجاد می‌شود)
├── composer.json                    # تنظیمات Composer
├── .htaccess                        # تنظیمات Apache اصلی
├── .gitignore                       # فایل‌های نادیده Git
├── LICENSE                          # لایسنس
└── README.md                        # مستندات (همین فایل)
```

---

## 🔧 تنظیمات

### تنظیمات Apache (.htaccess)

فایل `.htaccess` در روت پروژه (در صورت عدم وجود، ایجاد کنید):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # هدایت تمام درخواست‌ها به public/index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php [QSA,L]
</IfModule>

# امنیت - جلوگیری از دسترسی به فایل‌های حساس
<FilesMatch "\.(env|log|sql|md|lock|example)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# جلوگیری از دسترسی به پوشه‌های حساس
RewriteRule ^(config|app|database|installer|resources|vendor|storage)(/|$) - [F,L]
```

### تنظیمات Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/youtuber-bot/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    location ~ /\.(config|storage|app|database|resources) {
        deny all;
    }

    # جلوگیری از دسترسی به پوشه‌های داخلی
    location ~ ^/(config|app|database|installer|resources|vendor|storage) {
        deny all;
    }

    # محافظت از install.lock
    location = /install.lock {
        deny all;
    }
}
```

### تنظیمات PHP (php.ini)

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
date.timezone = Asia/Tehran
```

---

## 📚 مستندات

### ورود به پنل ادمین

1. به آدرس `https://yourdomain.com/admin/` بروید
2. با اطلاعات پیش‌فرض وارد شوید:
   - **نام کاربری:** `admin`
   - **رمز عبور:** `Admin@12345`
3. ⚠️ **مهم:** فوراً رمز را تغییر دهید!

### تنظیم ربات

1. **تنظیم Webhook:**
   ```bash
   curl -F "url=https://yourdomain.com/webhook.php" \
        https://api.telegram.org/botTOKEN/setWebhook
   ```

2. **تنظیم توکن ربات:**
   - در پنل ادمین به بخش **تنظیمات > تلگرام** بروید
   - توکن ربات را وارد کنید

3. **تنظیم ادمین:**
   - آیدی عددی خود را در تنظیمات وارد کنید

### دستورات ربات

| دستور | توضیح |
|-------|-------|
| `/start` | شروع و منوی اصلی |
| `/help` | نمایش راهنما |
| `/donate` | حمایت مالی |
| `/vip` | باشگاه مشتریان |
| `/contact` | تماس با ما |
| `/about` | درباره ما |
| `/stats` | آمار شما |

### کلمات کلیدی

برای اضافه کردن کلمه کلیدی:

1. به پنل ادمین > **کلمات کلیدی** بروید
2. روی **افزودن کلمه کلیدی** کلیک کنید
3. کلمه و پاسخ را وارد کنید
4. ذخیره کنید

### ارسال دسته‌جمعی

1. به پنل ادمین > **ارسال دسته‌جمعی** بروید
2. روی **ایجاد Broadcast جدید** کلیک کنید
3. محتوا و گروه هدف را انتخاب کنید
4. پیش‌نمایش را بررسی کنید
5. ارسال را شروع کنید

---

## 🔌 API Documentation

### آمار با API Token

```bash
# دریافت Overview
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://yourdomain.com/api/statistics/overview

# لیست کاربران
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://yourdomain.com/api/statistics/users/list?page=1

# آمار دونیت‌ها
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://yourdomain.com/api/statistics/donations/stats

# نمودار درآمد
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://yourdomain.com/api/statistics/charts/revenue?days=30

# KPI ها
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://yourdomain.com/api/statistics/kpis
```

### ساخت API Token

```php
use App\Api\StatisticsApi;

$result = StatisticsApi::createToken(
    $name = 'Mobile App',
    $permissions = ['read:overview', 'read:users'],
    $expiresAt = '2027-12-31 23:59:59'
);

echo $result['token']; // فقط یکبار نمایش داده می‌شه
```

---

## 🐛 عیب‌یابی

### خطاهای رایج

#### 1. خطای اتصال به دیتابیس

```
Error: SQLSTATE[HY000] [1045] Access denied
```

**راه‌حل:**
- بررسی اطلاعات دیتابیس در `config/config.php`
- اطمینان از وجود کاربر و دسترسی‌ها
- بررسی رمز عبور

#### 2. Webhook کار نمی‌کند

```
Update های تلگرام دریافت نمی‌شن
```

**راه‌حل:**
```bash
# بررسی وضعیت Webhook
curl https://api.telegram.org/botTOKEN/getWebhookInfo

# تنظیم مجدد Webhook
curl -F "url=https://yourdomain.com/webhook.php" \
     https://api.telegram.org/botTOKEN/setWebhook

# بررسی لاگ‌ها
tail -f storage/logs/telegram.log
```

#### 3. خطای SSL

```
SSL certificate problem: unable to get local issuer certificate
```

**راه‌حل:**
- نصب CA certificates
- یا غیرفعال کردن SSL verification (فقط در Development)

#### 4. خطای Permission

```
Permission denied: storage/logs/
```

**راه‌حل:**
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

#### 5. خطای Memory Limit

```
Fatal error: Allowed memory size exhausted
```

**راه‌حل:**
- افزایش `memory_limit` در `php.ini`
- بهینه‌سازی کوئری‌ها
- استفاده از Pagination

### لاگ‌ها

```bash
# لاگ‌های PHP
tail -f storage/logs/php_errors.log

# لاگ‌های برنامه
tail -f storage/logs/app-*.log

# لاگ‌های تلگرام
tail -f storage/logs/telegram-*.log

# لاگ‌های امنیتی
tail -f storage/logs/security-*.log
```

---

## 🤝 مشارکت

مشارکت شما باعث خوشحالی ماست! 🎉

### نحوه مشارکت

1. **Fork** پروژه را Fork کنید
2. **Branch** جدید بسازید: `git checkout -b feature/amazing-feature`
3. **Commit** تغییرات: `git commit -m 'Add amazing feature'`
4. **Push** به Branch: `git push origin feature/amazing-feature`
5. **Pull Request** باز کنید

### استانداردهای کد

- **PSR-12** برای PHP
- ** indent** با 4 فاصله
- **نام‌گذاری** camelCase برای متغیرها و متدها
- **نام‌گذاری** PascalCase برای کلاس‌ها
- **مستندات** PHPDoc برای تمام متدها

### گزارش باگ

اگر باگی پیدا کردید، لطفاً در بخش **Issues** گزارش دهید:

1. عنوان واضح
2. مراحل بازتولید
3. رفتار مورد انتظار
4. رفتار واقعی
5. اسکرین‌شات (در صورت امکان)
6. محیط (PHP version, OS, ...)

---

## 📝 TODO List

### ویژگی‌های آینده

- [x] **v2.1** پشتیبانی از Bunny Database (Turso/libSQL)
- [x] **v2.1** نصب‌کننده تحت وب (Web Installer)
- [ ] پشتیبانی از Telegram Payments
- [ ] سیستم چند زبانه
- [ ] اپلیکیشن موبایل
- [ ] سیستم تیکت پشتیبانی
- [ ] ربات چند کاربره
- [ ] سیستم احراز هویت دو مرحله‌ای
- [ ] پشتیبانی از Redis Cache
- [ ] سیستم Queue پیشرفته
- [ ] Webhook های سفارشی
- [ ] API Rate Limiting پیشرفته
- [ ] Admin API endpoints (UsersApi, ChatApi, etc.)

---

## 📄 لایسنس

این پروژه تحت لایسنس **MIT** منتشر شده است.

```
MIT License

Copyright (c) 2026 Youtuber Bot

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 🙏 تشکر

این پروژه با الهام از پروژه‌های زیر ساخته شده:

- [Laravel](https://laravel.com/) - معماری و الگوها
- [Telegram Bot API](https://core.telegram.org/bots/api) - API تلگرام
- [TailwindCSS](https://tailwindcss.com/) - استایل‌دهی
- [Chart.js](https://www.chartjs.org/) - نمودارها
- [Vazirmatn](https://github.com/rastikerdar/vazirmatn) - فونت فارسی

---

## 📞 تماس

- **Email:** support@yourdomain.com
- **Telegram:** [@idbw-ir](https://t.me/idbw-ir)
- **Website:** https://yourdomain.com
- **GitHub:** https://github.com/idbw-ir/youtuber-bot

---

## ⭐ حمایت از پروژه

اگر این پروژه براتون مفید بود، لطفاً:

1. ⭐ به پروژه **Star** بدید
2. 🍴 پروژه رو **Fork** کنید
3. 🐛 باگ‌ها رو **Report** کنید
4. 💡 **Feature Request** بدید
5. 📝 **Pull Request** بفرستید
6. 💰 **حمایت مالی** کنید

---

## 📊 آمار پروژه

```
📁 تعداد فایل‌ها:       64 فایل
📝 مجموع خطوط کد:       ~25,000 خط
🎨 تعداد صفحات ادمین:   10 صفحه
🔌 تعداد API endpoints: 50+ endpoint
📊 تعداد جداول:         17 جدول
🎯 تعداد ویژگی‌ها:      100+ ویژگی
```

---

<div align="center">

**ساخته شده با ❤️ برای یوتیوبرهای فارسی‌زبان**

🎬 **Youtuber Bot v2.1.0** | 2026

[⬆ برگشت به بالا](#-youtuber-bot---ربات-تلگرام-یوتیوبر)

</div>

