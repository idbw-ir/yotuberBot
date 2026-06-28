# 🎬 ربات تلگرام یوتیوبر - نسخه 2.0

<div align="center">

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-purple)
![License](https://img.shields.io/badge/license-MIT-green)

**یک ربات تلگرام حرفه‌ای برای یوتیوبرها با پنل مدیریت کامل**

[نصب](#-نصب-سریع) • [مستندات](#-مستندات) • [دمو](#-دمو) • [پشتیبانی](#-پشتیبانی)

</div>

---

## ✨ ویژگی‌ها

### 🤖 ربات تلگرام
- ✅ ارسال پیام خوش‌آمدگویی با عکس و دکمه‌های شیشه‌ای
- ✅ سیستم دونیت با لینک به درگاه ثالث
- ✅ پاسخ خودکار به کلمات کلیدی
- ✅ اتصال به هوش مصنوعی (OpenAI/Claude)
- ✅ باشگاه مشتریان VIP
- ✅ منوی تعاملی با Inline Keyboard

### 📊 پنل مدیریت
- ✅ داشبورد آماری با چارت‌های زنده
- ✅ مدیریت کامل کاربران
- ✅ چت زنده با کاربران
- ✅ آرشیو کامل پیام‌ها
- ✅ گزارش دونیت‌ها با نمودار
- ✅ مدیریت کلمات کلیدی
- ✅ ارسال پیام دسته‌جمعی
- ✅ تنظیمات پویا بدون نیاز به کد

### 🔒 امنیت
- ✅ احراز هویت دو مرحله‌ای (قابل فعال‌سازی)
- ✅ محافظت CSRF در تمام فرم‌ها
- ✅ Rate Limiting برای جلوگیری از اسپم
- ✅ رمزنگاری رمز عبور با bcrypt
- ✅ محافظت در برابر SQL Injection
- ✅ XSS Protection
- ✅ IP Whitelist برای پنل ادمین

### 🚀 عملکرد
- ✅ کش‌گذاری هوشمند
- ✅ Lazy Loading برای تصاویر
- ✅ بهینه‌سازی کوئری‌های دیتابیس
- ✅ Async Processing برای ارسال دسته‌جمعی

---

## 📋 پیش‌نیازها

- **PHP** >= 8.1
- **MySQL** >= 5.7 یا **MariaDB** >= 10.3
- **Nginx** یا **Apache**
- **SSL Certificate** (الزامی برای تلگرام)
- **Composer** (اختیاری برای توسعه)
- **Bot Token** از [@BotFather](https://t.me/BotFather)

---

## 🚀 نصب سریع

```bash
# 1. کلون کردن پروژه
cd /var/www
git clone https://github.com/yourusername/youtuber-bot.git
cd youtuber-bot

# 2. نصب وابستگی‌ها (اختیاری)
composer install

# 3. تنظیم فایل کانفیگ
cp config/config.example.php config/config.php
nano config/config.php

# 4. ساخت دیتابیس
mysql -u root -p < database/migrations/001_initial_schema.sql

# 5. ساخت ادمین اولیه
php cli/create-admin.php

# 6. تنظیم مجوزها
chmod -R 775 storage/
chown -R www-data:www-data storage/

# 7. تنظیم وب‌هوک تلگرام
curl "https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://yourdomain.com/webhook.php"