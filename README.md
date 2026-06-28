# 📄 فایل README.md (نسخه بازنویسی شده)

```markdown
<div align="center">

# 🎬 ربات تلگرام یوتیوبر

### یک ربات تلگرام حرفه‌ای برای یوتیوبرها با پنل مدیریت کامل

![Version](https://img.shields.io/badge/version-2.0.0-blue?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)
![Stars](https://img.shields.io/github/stars/yourusername/youtuber-bot?style=for-the-badge)
![Forks](https://img.shields.io/github/forks/yourusername/youtuber-bot?style=for-the-badge)

[🚀 نصب سریع](#-نصب-سریع) • [📖 مستندات](#-مستندات) • [🎨 دمو](#-دمو) • [🤝 مشارکت](#-مشارکت) • [📞 پشتیبانی](#-پشتیبانی)

</div>

---

## 📋 فهرست مطالب

- [معرفی](#-معرفی)
- [ویژگی‌ها](#-ویژگی‌ها)
- [پیش‌نیازها](#-پیش‌نیازها)
- [نصب سریع](#-نصب-سریع)
- [تنظیمات](#️-تنظیمات)
- [استفاده](#-استفاده)
- [مستندات](#-مستندات)
- [ساختار پروژه](#-ساختار-پروژه)
- [دمو](#-دمو)
- [تکنولوژی‌ها](#-تکنولوژی‌ها)
- [مشارکت](#-مشارکت)
- [نقشه راه](#-نقشه-راه)
- [لایسنس](#-لایسنس)
- [قدردانی](#-قدردانی)
- [نویسنده](#-نویسنده)

---

## 🎯 معرفی

**ربات تلگرام یوتیوبر** یک راه‌حل کامل و حرفه‌ای برای یوتیوبرهاست که می‌خوان ارتباط بهتری با مخاطبانشون داشته باشن و درآمد کسب کنن.

این پروژه شامل یک ربات تلگرام پیشرفته با پنل مدیریت تحت وب هست که تمام نیازهای یک یوتیوبر رو پوشش می‌ده:

- 💰 سیستم دریافت دونیت مالی
- 👥 مدیریت کاربران و باشگاه مشتریان
- 🤖 پاسخ خودکار با هوش مصنوعی
- 📊 آمار و گزارش‌های کامل
- 📢 ارسال پیام دسته‌جمعی

<div align="center">

![Bot Demo](https://via.placeholder.com/800x400/6366f1/ffffff?text=Demo+Screenshot)

*نمایی از ربات تلگرام در حال اجرا*

</div>

---

## ✨ ویژگی‌ها

### 🤖 ربات تلگرام

<table>
<tr>
<td width="50%">

#### ویژگی‌های اصلی
- ✅ پیام خوش‌آمدگویی سفارشی
- ✅ دکمه‌های شیشه‌ای (Inline Keyboard)
- ✅ لینک به کانال یوتیوب
- ✅ سیستم دونیت با درگاه ثالث
- ✅ منوی تعاملی پیشرفته

</td>
<td width="50%">

#### ویژگی‌های پیشرفته
- 🧠 اتصال به هوش مصنوعی (OpenAI/Claude)
- 🔑 پاسخ خودکار به کلمات کلیدی
- 👑 باشگاه مشتریان VIP
- 📝 ذخیره تاریخچه چت
- 🎯 سیستم آمارگیری کاربران

</td>
</tr>
</table>

### 📊 پنل مدیریت

<table>
<tr>
<td width="50%">

#### داشبورد و آمار
- 📈 نمودارهای تعاملی Chart.js
- 💰 آمار لحظه‌ای دونیت‌ها
- 👥 آمار کاربران و رشد
- 🏆 برترین حامیان
- 📅 گزارش‌های روزانه/ماهانه

</td>
<td width="50%">

#### مدیریت کاربران
- 💬 چت زنده با کاربران
- 📦 آرشیو کامل پیام‌ها
- 🎯 فیلتر و جستجوی پیشرفته
- 📤 ارسال پیام دسته‌جمعی
- ⚙️ تنظیمات پویا

</td>
</tr>
</table>

### 🔒 امنیت

| ویژگی | توضیحات |
|-------|---------|
| 🔐 **احراز هویت** | رمزنگاری bcrypt، Session Management |
| 🛡️ **CSRF Protection** | محافظت در تمام فرم‌ها |
| 🚦 **Rate Limiting** | جلوگیری از اسپم و Brute Force |
| 🔍 **Input Validation** | اعتبارسنجی تمام ورودی‌ها |
| 💉 **SQL Injection** | استفاده از Prepared Statements |
| 🌐 **XSS Protection** | محافظت در برابر حملات XSS |
| 📍 **IP Whitelist** | محدودسازی دسترسی به پنل |
| 🔒 **SSL/TLS** | الزامی برای تلگرام Webhook |

### 🚀 عملکرد

- ⚡ **سرعت بالا**: بهینه‌سازی کوئری‌ها و کش‌گذاری
- 📦 **Lazy Loading**: بارگذاری تنبل تصاویر
- 🔄 **Async Processing**: پردازش غیرهمزمان برای ارسال دسته‌جمعی
- 💾 **Database Indexing**: ایندکس‌گذاری بهینه
- 🌐 **CDN Ready**: سازگار با CDN

---

## 📋 پیش‌نیازها

### سرور

| نیازمندی | حداقل | پیشنهادی |
|----------|-------|----------|
| **OS** | Ubuntu 20.04 | Ubuntu 22.04 LTS |
| **RAM** | 1 GB | 2 GB+ |
| **Disk** | 10 GB | 20 GB+ |
| **CPU** | 1 Core | 2 Cores+ |

### نرم‌افزار

- ✅ **PHP** >= 8.1 (با اکستنشن‌های: `mysql`, `curl`, `mbstring`, `xml`, `zip`, `gd`)
- ✅ **MySQL** >= 5.7 یا **MariaDB** >= 10.3
- ✅ **Nginx** >= 1.18 یا **Apache** >= 2.4
- ✅ **SSL Certificate** (Let's Encrypt)
- ✅ **Composer** (اختیاری برای توسعه)
- ✅ **Git** (برای آپدیت آسان)

### سرویس‌های خارجی

- 🤖 **Telegram Bot Token** از [@BotFather](https://t.me/BotFather)
- 🧠 **OpenAI API Key** (اختیاری برای هوش مصنوعی)
- 💳 **درگاه پرداخت** (زرین‌پال، IDPay و...)

---

## 🚀 نصب سریع

### روش 1: نصب خودکار (پیشنهادی)

```bash
# دانلود و اجرای اسکریپت نصب
curl -fsSL https://raw.githubusercontent.com/yourusername/youtuber-bot/main/install.sh | bash
```

### روش 2: نصب دستی

#### 1️⃣ دانلود پروژه

```bash
# رفتن به پوشه وب
cd /var/www

# کلون کردن پروژه
sudo git clone https://github.com/yourusername/youtuber-bot.git
cd youtuber-bot

# یا دانلود ZIP
sudo wget https://github.com/yourusername/youtuber-bot/archive/refs/heads/main.zip
sudo unzip main.zip
sudo mv youtuber-bot-main youtuber-bot
cd youtuber-bot
```

#### 2️⃣ نصب وابستگی‌ها (اختیاری)

```bash
composer install --no-dev --optimize-autoloader
```

#### 3️⃣ تنظیم دیتابیس

```bash
# ورود به MySQL
sudo mysql -u root -p

# اجرای دستورات SQL
CREATE DATABASE youtuber_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'youtuber_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON youtuber_bot.* TO 'youtuber_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# ایمپورت اسکریپت
sudo mysql -u youtuber_user -p youtuber_bot < database/migrations/001_initial_schema.sql
```

#### 4️⃣ تنظیم فایل کانفیگ

```bash
# کپی فایل نمونه
cp config/config.example.php config/config.php

# ویرایش فایل
nano config/config.php
```

مقادیر زیر رو تغییر بدید:

```php
'database' => [
    'host' => 'localhost',
    'name' => 'youtuber_bot',
    'user' => 'youtuber_user',
    'pass' => 'YourStrongPassword123!',
],

'telegram' => [
    'bot_token' => 'YOUR_BOT_TOKEN_HERE',
    'admin_id' => 123456789, // آیدی عددی شما
],
```

#### 5️⃣ ساخت ادمین اولیه

```bash
# روش 1: استفاده از CLI
php cli/create-admin.php

# روش 2: ساخت هش رمز
php -r "echo password_hash('YourPassword123!', PASSWORD_DEFAULT);"

# سپس در phpMyAdmin:
INSERT INTO admins (username, password_hash) 
VALUES ('admin', '$2y$10$...');
```

#### 6️⃣ تنظیم مجوزها

```bash
sudo chown -R www-data:www-data /var/www/youtuber-bot
sudo find /var/www/youtuber-bot -type d -exec chmod 755 {} \;
sudo find /var/www/youtuber-bot -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/youtuber-bot/storage
```

#### 7️⃣ تنظیم Nginx

```bash
# ساخت فایل کانفیگ
sudo nano /etc/nginx/sites-available/youtuber-bot.conf
```

محتوای فایل:

```nginx
server {
    listen 80;
    server_name bot.yourdomain.com;
    root /var/www/youtuber-bot/public;
    index index.php;
    
    location ~ /\.(config|storage|database|app|resources) {
        deny all;
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    client_max_body_size 20M;
}
```

فعال‌سازی:

```bash
sudo ln -s /etc/nginx/sites-available/youtuber-bot.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 8️⃣ تنظیم SSL

```bash
sudo certbot --nginx -d bot.yourdomain.com
```

#### 9️⃣ تنظیم Webhook تلگرام

```bash
curl -F "url=https://bot.yourdomain.com/webhook.php" \
     https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook
```

---

## ⚙️ تنظیمات

### فایل کانفیگ اصلی

فایل `config/config.php` شامل تمام تنظیمات پروژه هست:

```php
<?php
return [
    // تنظیمات دیتابیس
    'database' => [
        'host' => 'localhost',
        'name' => 'youtuber_bot',
        'user' => 'youtuber_user',
        'pass' => 'YourStrongPassword123!',
        'charset' => 'utf8mb4',
    ],
    
    // تنظیمات تلگرام
    'telegram' => [
        'bot_token' => 'YOUR_BOT_TOKEN',
        'admin_id' => 123456789,
        'webhook_secret' => 'your_secret_string',
    ],
    
    // تنظیمات برنامه
    'app' => [
        'name' => 'یوتیوبر بات',
        'url' => 'https://bot.yourdomain.com',
        'timezone' => 'Asia/Tehran',
        'debug' => false,
    ],
    
    // تنظیمات هوش مصنوعی
    'ai' => [
        'enabled' => false,
        'provider' => 'openai', // openai یا claude
        'api_key' => '',
        'model' => 'gpt-4o-mini',
    ],
    
    // تنظیمات امنیتی
    'security' => [
        'csrf_enabled' => true,
        'rate_limit' => 60,
        'admin_ip_whitelist' => [],
    ],
];
```

### تنظیمات از پنل مدیریت

بعضی تنظیمات رو می‌تونید از پنل مدیریت تغییر بدید:

- 📝 متن خوش‌آمدگویی
- 🖼️ عکس خوش‌آمدگویی
- 🔗 لینک دونیت
- 🎬 لینک کانال یوتیوب
- 🔑 کلمات کلیدی و پاسخ‌ها

---

## 📖 استفاده

### شروع کار با ربات

1. در تلگرام ربات خودتون رو پیدا کنید
2. دستور `/start` رو بفرستید
3. پیام خوش‌آمدگویی رو دریافت می‌کنید
4. از منو گزینه‌های مختلف رو انتخاب کنید

### ورود به پنل مدیریت

1. به `https://bot.yourdomain.com/admin/login.php` برید
2. با نام کاربری و رمز عبور وارد بشید
3. از داشبورد استفاده کنید

### دستورات ربات

| دستور | توضیحات |
|-------|---------|
| `/start` | شروع و نمایش منوی اصلی |
| `/help` | نمایش راهنما |
| `/donate` | لینک دونیت |
| `/vip` | اطلاعات باشگاه مشتریان |
| `/contact` | اطلاعات تماس |

### دکمه‌های شیشه‌ای

- 💰 **حمایت مالی**: لینک به درگاه پرداخت
- 🎬 **کانال یوتیوب**: لینک به کانال شما
- 👥 **باشگاه مشتریان**: اطلاعات VIP
- 📞 **تماس با ما**: اطلاعات تماس

---

## 📚 مستندات

| سند | توضیحات | لینک |
|-----|---------|------|
| **راهنمای نصب** | نصب گام‌به‌گام | [INSTALL.md](docs/INSTALL.md) |
| **مستندات API** | API های ربات و پنل | [API.md](docs/API.md) |
| **امنیت** | نکات امنیتی | [SECURITY.md](docs/SECURITY.md) |
| **تغییرات** | تاریخچه نسخه‌ها | [CHANGELOG.md](CHANGELOG.md) |
| **مشارکت** | راهنمای مشارکت | [CONTRIBUTING.md](CONTRIBUTING.md) |

---

## 🏗️ ساختار پروژه

```
youtuber-bot/
├── 📁 app/                    # کدهای اصلی برنامه
│   ├── Core/                  # کلاس‌های هسته
│   │   ├── Database.php
│   │   ├── Router.php
│   │   └── Logger.php
│   ├── Telegram/              # کلاس‌های تلگرام
│   │   ├── Bot.php
│   │   └── Webhook.php
│   ├── Admin/                 # کنترلرهای پنل
│   │   ├── Dashboard.php
│   │   ├── Users.php
│   │   └── ...
│   └── AI/                    # اتصال به هوش مصنوعی
│       └── OpenAI.php
│
├── 📁 config/                 # فایل‌های تنظیمات
│   ├── config.php
│   └── routes.php
│
├── 📁 database/               # دیتابیس
│   ├── migrations/
│   └── seeds/
│
├── 📁 public/                 # وب‌روت
│   ├── index.php
│   ├── webhook.php
│   └── admin/
│
├── 📁 resources/              # ویوها و Assets
│   ├── views/
│   └── assets/
│
├── 📁 storage/                # فایل‌های ذخیره‌سازی
│   ├── logs/
│   ├── cache/
│   └── uploads/
│
├── 📁 tests/                  # تست‌ها
├── 📁 docs/                   # مستندات
├── 📄 README.md
├── 📄 LICENSE
└── 📄 composer.json
```

---

## 🎨 دمو

### داشبورد مدیریت

<div align="center">

![Dashboard](https://via.placeholder.com/800x450/6366f1/ffffff?text=Dashboard+Preview)

*نمایی از داشبورد با نمودارهای تعاملی*

</div>

### چت زنده با کاربران

<div align="center">

![Chat](https://via.placeholder.com/800x450/10b981/ffffff?text=Live+Chat+Preview)

*چت زنده با کاربران ربات*

</div>

### ربات تلگرام

<div align="center">

![Bot](https://via.placeholder.com/400x600/8b5cf6/ffffff?text=Telegram+Bot)

*نمایی از ربات تلگرام*

</div>

---

## 🛠️ تکنولوژی‌ها

### Backend

- **PHP 8.1+** - زبان برنامه‌نویسی
- **MySQL/MariaDB** - دیتابیس
- **PDO** - اتصال به دیتابیس
- **cURL** - درخواست‌های HTTP

### Frontend

- **TailwindCSS 3** - فریمورک CSS
- **Chart.js 4** - نمودارها
- **Alpine.js** - تعاملات JavaScript
- **Font Awesome** - آیکون‌ها

### ابزارها

- **Composer** - مدیریت وابستگی‌ها
- **Git** - کنترل نسخه
- **Nginx** - وب سرور
- **Let's Encrypt** - SSL Certificate

### API های خارجی

- **Telegram Bot API** - ربات تلگرام
- **OpenAI API** - هوش مصنوعی
- **ZarinPal/IDPay** - درگاه پرداخت

---

## 🤝 مشارکت

مشارکت شما باعث خوشحالیه! 🎉

### مراحل مشارکت

1. **Fork** کنید
2. **Branch** بسازید: `git checkout -b feature/AmazingFeature`
3. **Commit** کنید: `git commit -m 'Add AmazingFeature'`
4. **Push** کنید: `git push origin feature/AmazingFeature`
5. **Pull Request** باز کنید

### راهنمای کدنویسی

- از **PSR-12** پیروی کنید
- کامنت‌های واضح بنویسید
- تست بنویسید
- مستندات رو آپدیت کنید

📖 **راهنمای کامل**: [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 🗺️ نقشه راه

### نسخه 2.1.0 (در حال توسعه)

- [ ] سیستم چند زبانه کامل
- [ ] اپلیکیشن موبایل ادمین
- [ ] سیستم تیکتینگ
- [ ] خروجی Excel/PDF

### نسخه 2.2.0 (برنامه‌ریزی شده)

- [ ] اتصال مستقیم به درگاه‌های پرداخت
- [ ] سیستم اشتراک ماهانه
- [ ] API عمومی
- [ ] WebSocket برای چت Real-time

### نسخه 3.0.0 (آینده)

- [ ] بازنویسی Frontend با React/Vue
- [ ] سیستم Plugin
- [ ] Marketplace
- [ ] White Label Solution

📋 **نقشه راه کامل**: [ROADMAP.md](ROADMAP.md)

---

## 📝 لایسنس

این پروژه تحت لایسنس **MIT** منتشر شده.

```
MIT License

Copyright (c) 2024 Your Name

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction...
```

📄 **متن کامل لایسنس**: [LICENSE](LICENSE)

---

## 🙏 قدردانی

این پروژه با الهام از پروژه‌های زیر ساخته شده:

- [Telegram Bot API](https://core.telegram.org/bots/api)
- [Laravel](https://laravel.com) - برای الهام در معماری
- [TailwindCSS](https://tailwindcss.com)
- [Chart.js](https://www.chartjs.org)

### مشارکت‌کنندگان

با تشکر از تمام کسانی که در توسعه این پروژه مشارکت کردن:

<a href="https://github.com/yourusername/youtuber-bot/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=yourusername/youtuber-bot" />
</a>

---

## 👨‍💻 نویسنده

<div align="center">

### ساخته شده با ❤️ توسط [نام شما]

[![Website](https://img.shields.io/badge/Website-YourSite-blue?style=for-the-badge&logo=google-chrome)](https://yourwebsite.com)
[![Twitter](https://img.shields.io/badge/Twitter-@YourTwitter-1DA1F2?style=for-the-badge&logo=twitter)](https://twitter.com/yourtwitter)
[![YouTube](https://img.shields.io/badge/YouTube-YourChannel-FF0000?style=for-the-badge&logo=youtube)](https://youtube.com/@yourchannel)
[![Email](https://img.shields.io/badge/Email-your@email.com-D14836?style=for-the-badge&logo=gmail)](mailto:your@email.com)

</div>

---

## 📞 پشتیبانی

اگه سوال یا مشکلی دارید:

1. 📖 **مستندات** رو مطالعه کنید
2. 🔍 **Issues** رو جستجو کنید
3. 🆕 یک **Issue جدید** باز کنید
4. 💬 در **Discord** عضو بشید
5. 📧 **ایمیل** بزنید

### لینک‌های مفید

- 🐛 [گزارش باگ](https://github.com/yourusername/youtuber-bot/issues/new?template=bug_report.md)
- 💡 [درخواست ویژگی](https://github.com/yourusername/youtuber-bot/issues/new?template=feature_request.md)
- ❓ [پرسش و پاسخ](https://github.com/yourusername/youtuber-bot/discussions)
- 💬 [Discord Server](https://discord.gg/yourserver)

---

## ⭐ آمار پروژه

<div align="center">

![GitHub stars](https://img.shields.io/github/stars/yourusername/youtuber-bot?style=social)
![GitHub forks](https://img.shields.io/github/forks/yourusername/youtuber-bot?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/yourusername/youtuber-bot?style=social)
![GitHub followers](https://img.shields.io/github/followers/yourusername?style=social)

</div>

---

## 📌 نکات مهم

> ⚠️ **توجه**: این پروژه برای اهداف آموزشی و تجاری ساخته شده. قبل از استفاده در محیط Production، حتماً:
> 
> - ✅ تنظیمات امنیتی رو بررسی کنید
> - ✅ Backup منظم داشته باشید
> - ✅ SSL Certificate فعال کنید
> - ✅ رمزهای قوی استفاده کنید
> - ✅ لاگ‌ها رو مانیتور کنید

---

<div align="center">

## 🌟 اگه این پروژه برات مفید بود، یه ستاره بده! ⭐

[⬆ بازگشت به بالا](#-ربات-تلگرام-یوتیوبر)

**ساخته شده با ❤️ برای یوتیوبرهای فارسی‌زبان**

</div>
```
