# 📦 راهنمای نصب کامل

این راهنما شما رو قدم‌به‌قدم تا نصب کامل پروژه همراهی می‌کنه.

---

## 📋 فهرست

1. [پیش‌نیازها](#پیش‌نیازها)
2. [دانلود پروژه](#دانلود-پروژه)
3. [تنظیم دیتابیس](#تنظیم-دیتابیس)
4. [تنظیم فایل کانفیگ](#تنظیم-فایل-کانفیگ)
5. [تنظیم Nginx](#تنظیم-nginx)
6. [تنظیم SSL](#تنظیم-ssl)
7. [تنظیم وب‌هوک تلگرام](#تنظیم-وب‌هوک-تلگرام)
8. [تست نصب](#تست-نصب)
9. [عیب‌یابی](#عیب‌یابی)

---

## 🔧 پیش‌نیازها

### سرور
- Ubuntu 20.04/22.04 یا Debian 11/12 (پیشنهادی)
- حداقل 1GB RAM
- حداقل 10GB فضای دیسک
- PHP 8.1 یا بالاتر
- MySQL 5.7+ یا MariaDB 10.3+
- Nginx یا Apache
- SSL Certificate

### نصب پکیج‌های لازم (Ubuntu/Debian)

```bash
# آپدیت سیستم
sudo apt update && sudo apt upgrade -y

# نصب Nginx
sudo apt install nginx -y

# نصب PHP و اکستنشن‌ها
sudo apt install php8.1-fpm php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip php8.1-gd -y

# نصب MySQL
sudo apt install mysql-server -y

# نصب Certbot برای SSL
sudo apt install certbot python3-certbot-nginx -y

# نصب Git
sudo apt install git -y