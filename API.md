<div align="center">

# 📘 مستندات API

### راهنمای کامل API های ربات تلگرام یوتیوبر

![API Version](https://img.shields.io/badge/API%20Version-2.0.0-blue?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Stable-green?style=for-the-badge)
![Auth](https://img.shields.io/badge/Auth-Session%20%7C%20Token-orange?style=for-the-badge)

[📖 README](README.md) • [📦 INSTALL](INSTALL.md) • [🔒 SECURITY](SECURITY.md) • [📝 CHANGELOG](CHANGELOG.md)

</div>

---

## 📋 فهرست مطالب

- [معرفی](#-معرفی)
- [معماری API](#-معماری-api)
- [احراز هویت](#-احراز-هویت)
- [Telegram Webhook API](#-telegram-webhook-api)
- [Admin Panel API](#-admin-panel-api)
- [Donation Callback API](#-donation-callback-api)
- [AI Integration API](#-ai-integration-api)
- [Statistics API](#-statistics-api)
- [مدیریت خطا](#-مدیریت-خطا)
- [Rate Limiting](#-rate-limiting)
- [Webhooks](#-webhooks)
- [نمونه کدها](#-نمونه-کدها)
- [Best Practices](#-best-practices)
- [تغییرات نسخه](#-تغییرات-نسخه)

---

## 🎯 معرفی

این سند مستندات کامل تمام API های پروژه **ربات تلگرام یوتیوبر** رو پوشش می‌ده.

### انواع API

| نوع API | توضیحات | احراز هویت |
|---------|---------|------------|
| 🤖 **Telegram Webhook** | دریافت پیام‌های تلگرام | Secret Token |
| 🔐 **Admin Panel** | مدیریت پنل ادمین | Session + CSRF |
| 💳 **Donation Callback** | کال‌بک درگاه پرداخت | Signature |
| 🧠 **AI Integration** | اتصال به هوش مصنوعی | API Key |
| 📊 **Statistics** | دریافت آمار و گزارشات | Session |

### Base URLs

```
Production: https://bot.yourdomain.com
Development: http://localhost:8000
```

---

## 🏗️ معماری API

### ساختار درخواست

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Nginx     │ ← SSL Termination
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Router    │ ← Route Matching
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Middleware  │ ← Auth, CSRF, Rate Limit
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Controller  │ ← Business Logic
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Model     │ ← Database Query
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Response   │ ← JSON/HTML
└─────────────┘
```

### فرمت پاسخ

تمام API ها (به جز پنل ادمین) از فرمت JSON استفاده می‌کنن:

```json
{
  "success": true,
  "data": {
    // داده‌های پاسخ
  },
  "message": "عملیات با موفقیت انجام شد",
  "meta": {
    "timestamp": 1234567890,
    "version": "2.0.0"
  }
}
```

---

## 🔐 احراز هویت

### 1. Telegram Webhook Authentication

```http
POST /webhook.php
X-Telegram-Bot-Api-Secret-Token: your_secret_token
Content-Type: application/json
```

**بررسی Secret:**

```php
$secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
if ($secret !== config('telegram.webhook_secret')) {
    http_response_code(403);
    exit('Forbidden');
}
```

### 2. Admin Panel Authentication

#### Session-Based Auth

```php
// بررسی لاگین
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// بررسی CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_token']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        exit('CSRF token mismatch');
    }
}
```

#### دریافت CSRF Token

```php
// تولید توکن
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// استفاده در فرم
<form method="POST">
    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?>">
    <!-- فیلدهای دیگه -->
</form>
```

### 3. API Token Authentication (برای API های خارجی)

```http
GET /api/v1/stats
Authorization: Bearer your_api_token_here
```

**تولید API Token:**

```php
// تولید توکن
$token = bin2hex(random_bytes(32));

// ذخیره در دیتابیس
$pdo->prepare("INSERT INTO api_tokens (admin_id, token, created_at) VALUES (?, ?, NOW())")
    ->execute([$admin_id, $token]);
```

---

## 🤖 Telegram Webhook API

### Endpoint

```
POST /webhook.php
```

### Headers

```http
Content-Type: application/json
X-Telegram-Bot-Api-Secret-Token: your_secret_token
```

### Request Structure

#### Message Update

```json
{
  "update_id": 123456789,
  "message": {
    "message_id": 1,
    "from": {
      "id": 123456789,
      "is_bot": false,
      "first_name": "علی",
      "last_name": "محمدی",
      "username": "ali_m",
      "language_code": "fa"
    },
    "chat": {
      "id": 123456789,
      "first_name": "علی",
      "last_name": "محمدی",
      "username": "ali_m",
      "type": "private"
    },
    "date": 1234567890,
    "text": "/start"
  }
}
```

#### Callback Query Update

```json
{
  "update_id": 123456789,
  "callback_query": {
    "id": "1234567890123456789",
    "from": {
      "id": 123456789,
      "is_bot": false,
      "first_name": "علی",
      "username": "ali_m"
    },
    "message": {
      "message_id": 1,
      "chat": {
        "id": 123456789,
        "type": "private"
      },
      "date": 1234567890,
      "text": "منوی اصلی"
    },
    "chat_instance": "1234567890123456789",
    "data": "donate"
  }
}
```

### Handled Commands

| Command | Description | Example Response |
|---------|-------------|------------------|
| `/start` | شروع و نمایش منوی اصلی | پیام خوش‌آمدگویی با دکمه‌ها |
| `/help` | نمایش راهنما | لیست دستورات |
| `/donate` | نمایش لینک دونیت | دکمه دونیت |
| `/vip` | اطلاعات باشگاه مشتریان | توضیحات VIP |
| `/contact` | اطلاعات تماس | لینک‌های تماس |
| `/stats` | آمار کاربر (فقط ادمین) | آمار شخصی |

### Callback Data

| Data | Action | Response |
|------|--------|----------|
| `donate` | نمایش صفحه دونیت | دکمه لینک درگاه |
| `youtube` | لینک کانال یوتیوب | دکمه لینک یوتیوب |
| `vip` | اطلاعات VIP | توضیحات و دکمه عضویت |
| `contact` | اطلاعات تماس | لینک‌های تماس |
| `home` | بازگشت به منوی اصلی | منوی اصلی |
| `about` | درباره ما | توضیحات |
| `faq` | سوالات متداول | لیست سوالات |

### Response Examples

#### Send Message

```php
// ارسال پیام متنی
$response = telegram_request('sendMessage', [
    'chat_id' => $chat_id,
    'text' => 'سلام! خوش آمدید 👋',
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode([
        'inline_keyboard' => [
            [
                ['text' => '💰 حمایت مالی', 'callback_data' => 'donate'],
                ['text' => '🎬 یوتیوب', 'callback_data' => 'youtube']
            ]
        ]
    ])
]);
```

#### Send Photo

```php
// ارسال عکس با کپشن
$response = telegram_request('sendPhoto', [
    'chat_id' => $chat_id,
    'photo' => $file_id, // یا URL
    'caption' => 'توضیحات عکس',
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($keyboard)
]);
```

#### Send Document

```php
// ارسال فایل
$response = telegram_request('sendDocument', [
    'chat_id' => $chat_id,
    'document' => $file_id,
    'caption' => 'فایل شما',
    'parse_mode' => 'HTML'
]);
```

### Webhook Setup

#### Set Webhook

```bash
curl -F "url=https://bot.yourdomain.com/webhook.php" \
     -F "secret_token=your_secret_token" \
     -F "allowed_updates=[\"message\",\"callback_query\"]" \
     -F "max_connections=40" \
     https://api.telegram.org/bot<TOKEN>/setWebhook
```

#### Get Webhook Info

```bash
curl https://api.telegram.org/bot<TOKEN>/getWebhookInfo
```

**Response:**

```json
{
  "ok": true,
  "result": {
    "url": "https://bot.yourdomain.com/webhook.php",
    "has_custom_certificate": false,
    "pending_update_count": 0,
    "max_connections": 40,
    "ip_address": "149.154.160.0"
  }
}
```

#### Delete Webhook

```bash
curl -F "drop_pending_updates=true" \
     https://api.telegram.org/bot<TOKEN>/deleteWebhook
```

---

## 🔐 Admin Panel API

### Authentication

تمام endpoint های پنل ادمین نیاز به **Session Authentication** و **CSRF Token** دارن.

### Login

#### Endpoint

```
POST /admin/login.php
```

#### Request

```http
Content-Type: application/x-www-form-urlencoded

username=admin&password=YourPassword123&_token=csrf_token
```

#### Response

**Success:**
```http
HTTP/1.1 302 Found
Location: /admin/index.php
Set-Cookie: PHPSESSID=abc123...
```

**Failed:**
```http
HTTP/1.1 302 Found
Location: /admin/login.php?error=invalid_credentials
```

### Logout

#### Endpoint

```
GET /admin/logout.php
```

#### Response

```http
HTTP/1.1 302 Found
Location: /admin/login.php
Set-Cookie: PHPSESSID=deleted; expires=Thu, 01 Jan 1970 00:00:00 GMT
```

---

### Users Management

#### Get All Users

##### Endpoint

```
GET /admin/users.php
```

##### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | int | No | شماره صفحه (پیش‌فرض: 1) |
| `per_page` | int | No | تعداد در هر صفحه (پیش‌فرض: 20) |
| `search` | string | No | جستجو در نام و یوزرنیم |
| `filter` | string | No | all, vip, blocked |
| `sort` | string | No | joined_at, last_seen, donations |
| `order` | string | No | asc, desc (پیش‌فرض: desc) |

##### Example Request

```http
GET /admin/users.php?page=1&per_page=20&search=ali&filter=vip&sort=donations&order=desc
```

##### Response (HTML)

```html
<table>
  <tr>
    <td>علی محمدی</td>
    <td>@ali_m</td>
    <td>VIP</td>
    <td>1,500,000 ت</td>
    <td>2024-01-15</td>
  </tr>
</table>
```

#### Get User Details

##### Endpoint

```
GET /admin/users.php?id={user_id}
```

##### Example Request

```http
GET /admin/users.php?id=123456789
```

##### Response (JSON - اگر header Accept: application/json)

```json
{
  "success": true,
  "data": {
    "id": 123456789,
    "username": "ali_m",
    "first_name": "علی",
    "last_name": "محمدی",
    "phone": null,
    "is_vip": true,
    "joined_at": "2024-01-15 10:30:00",
    "last_seen": "2024-01-20 15:45:00",
    "blocked": false,
    "notes": "کاربر فعال",
    "donations": {
      "total_amount": 1500000,
      "total_count": 5
    },
    "messages": {
      "total_count": 25
    }
  }
}
```

#### Update User

##### Endpoint

```
POST /admin/users.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

action=update&id=123456789&is_vip=1&notes=کاربر+ویژه&_token=csrf_token
```

##### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | باید `update` باشه |
| `id` | int | Yes | آیدی کاربر |
| `is_vip` | int | No | 0 یا 1 |
| `notes` | string | No | یادداشت |
| `_token` | string | Yes | CSRF Token |

##### Response

```http
HTTP/1.1 302 Found
Location: /admin/users.php?id=123456789&success=updated
```

#### Block/Unblock User

##### Endpoint

```
POST /admin/users.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

action=block&id=123456789&_token=csrf_token
```

##### Response

```http
HTTP/1.1 302 Found
Location: /admin/users.php?success=blocked
```

---

### Messages Management

#### Get Messages

##### Endpoint

```
GET /admin/messages.php
```

##### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | int | No | فیلتر بر اساس کاربر |
| `date_from` | date | No | از تاریخ (Y-m-d) |
| `date_to` | date | No | تا تاریخ (Y-m-d) |
| `direction` | string | No | in, out, all (پیش‌فرض: all) |
| `search` | string | No | جستجو در متن |
| `page` | int | No | شماره صفحه |

##### Example Request

```http
GET /admin/messages.php?user_id=123456789&date_from=2024-01-01&date_to=2024-01-31&direction=in
```

#### Send Message to User

##### Endpoint

```
POST /admin/chat.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

id=123456789&reply=سلام!+چطور+می‌تونم+کمکت+کنم؟&_token=csrf_token
```

##### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | آیدی کاربر تلگرام |
| `reply` | string | Yes | متن پیام |
| `_token` | string | Yes | CSRF Token |

##### Response

**Success:**
```http
HTTP/1.1 302 Found
Location: /admin/chat.php?id=123456789&success=sent
```

**Failed:**
```http
HTTP/1.1 302 Found
Location: /admin/chat.php?id=123456789&error=send_failed
```

---

### Donations Management

#### Get Donations

##### Endpoint

```
GET /admin/donations.php
```

##### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | pending, success, failed |
| `date_from` | date | No | از تاریخ |
| `date_to` | date | No | تا تاریخ |
| `user_id` | int | No | فیلتر بر اساس کاربر |
| `min_amount` | int | No | حداقل مبلغ |
| `max_amount` | int | No | حداکثر مبلغ |
| `page` | int | No | شماره صفحه |

##### Example Request

```http
GET /admin/donations.php?status=success&date_from=2024-01-01&min_amount=100000
```

#### Get Donation Statistics

##### Endpoint

```
GET /admin/api/donations/stats.php
```

##### Headers

```http
Accept: application/json
```

##### Response

```json
{
  "success": true,
  "data": {
    "total_amount": 50000000,
    "total_count": 150,
    "today_amount": 1500000,
    "today_count": 5,
    "month_amount": 15000000,
    "month_count": 45,
    "average_amount": 333333,
    "top_donors": [
      {
        "user_id": 123456789,
        "username": "ali_m",
        "first_name": "علی",
        "total_amount": 5000000,
        "donation_count": 10
      }
    ],
    "chart_data": {
      "labels": ["2024-01-01", "2024-01-02", "2024-01-03"],
      "datasets": [
        {
          "label": "مبلغ دونیت",
          "data": [1000000, 1500000, 2000000]
        },
        {
          "label": "تعداد دونیت",
          "data": [3, 5, 7]
        }
      ]
    }
  }
}
```

---

### Keywords Management

#### Get Keywords

##### Endpoint

```
GET /admin/keywords.php
```

##### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | جستجو در کلمه کلیدی |
| `active` | int | No | 0 یا 1 |
| `page` | int | No | شماره صفحه |

#### Add Keyword

##### Endpoint

```
POST /admin/keywords.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

action=add&keyword=سلام&answer=سلام!+چطور+می‌تونم+کمکت+کنم؟&answer_type=text&_token=csrf_token
```

##### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | باید `add` باشه |
| `keyword` | string | Yes | کلمه کلیدی |
| `answer` | string | Yes | پاسخ |
| `answer_type` | string | No | text, photo, video, document (پیش‌فرض: text) |
| `file_id` | string | No | آیدی فایل (برای photo/video/document) |
| `_token` | string | Yes | CSRF Token |

##### Response

```http
HTTP/1.1 302 Found
Location: /admin/keywords.php?success=added
```

#### Update Keyword

##### Endpoint

```
POST /admin/keywords.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

action=update&id=1&keyword=سلام&answer=سلام+دوست+عزیز!&_token=csrf_token
```

#### Delete Keyword

##### Endpoint

```
POST /admin/keywords.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

action=delete&id=1&_token=csrf_token
```

#### Toggle Keyword Status

##### Endpoint

```
POST /admin/keywords.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

action=toggle&id=1&_token=csrf_token
```

---

### Broadcast

#### Send Broadcast

##### Endpoint

```
POST /admin/broadcast.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

text=🎬+ویدئوی+جدید+منتشر+شد!&target=all&delay=50&_token=csrf_token
```

##### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `text` | string | Yes | متن پیام |
| `target` | string | No | all, vip (پیش‌فرض: all) |
| `delay` | int | No | تأخیر بین پیام‌ها (میلی‌ثانیه، پیش‌فرض: 50) |
| `parse_mode` | string | No | HTML, Markdown (پیش‌فرض: HTML) |
| `_token` | string | Yes | CSRF Token |

##### Response (JSON - اگر header Accept: application/json)

```json
{
  "success": true,
  "data": {
    "total": 152,
    "sent": 150,
    "failed": 2,
    "blocked_users": 2,
    "duration": 15.5
  },
  "message": "ارسال با موفقیت انجام شد"
}
```

**Response (HTML - بدون header JSON)**

```http
HTTP/1.1 302 Found
Location: /admin/broadcast.php?success=1&sent=150&failed=2
```

---

### Settings

#### Get Settings

##### Endpoint

```
GET /admin/settings.php
```

##### Response (HTML)

فرم تنظیمات با مقادیر فعلی

#### Update Settings

##### Endpoint

```
POST /admin/settings.php
```

##### Request

```http
Content-Type: application/x-www-form-urlencoded

bot_token=123456:ABC-DEF&welcome_text=سلام!&donate_link=https://donate.com&_token=csrf_token
```

##### Available Settings

| Setting | Type | Description |
|---------|------|-------------|
| `bot_token` | string | توکن ربات تلگرام |
| `admin_id` | int | آیدی عددی ادمین |
| `welcome_text` | string | متن خوش‌آمدگویی |
| `welcome_photo` | string | آیدی عکس خوش‌آمدگویی |
| `donate_link` | string | لینک درگاه دونیت |
| `donate_text` | string | متن دونیت |
| `youtube_url` | string | لینک کانال یوتیوب |
| `ai_enabled` | int | 0 یا 1 |
| `ai_api_key` | string | کلید API هوش مصنوعی |
| `ai_model` | string | مدل هوش مصنوعی |

##### Response

```http
HTTP/1.1 302 Found
Location: /admin/settings.php?success=updated
```

---

## 💳 Donation Callback API

### ZarinPal Callback

#### Endpoint

```
POST /api/donation/zarinpal/callback.php
```

#### Request

```json
{
  "Status": "OK",
  "Authority": "A00000000000000000000000000123456789",
  "ref_id": "123456789"
}
```

#### Verification Process

```php
// 1. دریافت داده‌ها
$authority = $_POST['Authority'];
$status = $_POST['Status'];

// 2. بررسی وضعیت
if ($status !== 'OK') {
    // پرداخت ناموفق
    updateDonationStatus($authority, 'failed');
    redirect('/payment/failed');
}

// 3. تأیید پرداخت
$verification = zarinpal_verify($authority, $amount);

if ($verification->data->code === 100) {
    // پرداخت موفق
    updateDonationStatus($authority, 'success', $verification->data->ref_id);
    
    // ارسال پیام به کاربر
    send_message($user_id, "✅ پرداخت شما با موفقیت انجام شد\nمبلغ: " . number_format($amount) . " تومان");
    
    redirect('/payment/success');
} else {
    // پرداخت ناموفق
    updateDonationStatus($authority, 'failed');
    redirect('/payment/failed');
}
```

#### Response

```json
{
  "status": "success",
  "message": "پرداخت با موفقیت تأیید شد",
  "ref_id": "123456789"
}
```

---

### IDPay Callback

#### Endpoint

```
POST /api/donation/idpay/callback.php
```

#### Request

```json
{
  "id": "123456789",
  "order_id": "987654321",
  "status": 100,
  "track_id": "123456789",
  "amount": 1000000,
  "date": "2024-01-20T15:30:00Z"
}
```

#### Status Codes

| Status | Description |
|--------|-------------|
| 1 | پرداخت در انتظار تأیید |
| 2 | پرداخت ناموفق |
| 10 | پرداخت در انتظار تأیید |
| 100 | پرداخت موفق |
| 101 | پرداخت قبلاً تأیید شده |
| 200 | پرداخت به گیرنده واریز شد |

#### Verification Process

```php
// 1. دریافت داده‌ها
$id = $_POST['id'];
$status = $_POST['status'];
$track_id = $_POST['track_id'];

// 2. بررسی وضعیت
if ($status !== 100) {
    updateDonationStatus($id, 'failed');
    redirect('/payment/failed');
}

// 3. تأیید پرداخت
$verification = idpay_verify($id, $order_id);

if ($verification->status === 100) {
    updateDonationStatus($id, 'success', $track_id);
    send_message($user_id, "✅ پرداخت موفق");
    redirect('/payment/success');
} else {
    updateDonationStatus($id, 'failed');
    redirect('/payment/failed');
}
```

---

## 🧠 AI Integration API

### OpenAI Integration

#### Class Usage

```php
use App\AI\OpenAI;

$ai = new OpenAI([
    'api_key' => config('ai.api_key'),
    'model' => config('ai.model'), // gpt-4o-mini
    'max_tokens' => 500,
    'temperature' => 0.7
]);

// چت ساده
$response = $ai->chat($user_id, $message);

// چت با context
$response = $ai->chat($user_id, $message, [
    'system' => 'تو دستیار یک یوتیوبر فارسی‌زبان هستی',
    'history' => $chat_history
]);
```

#### Method: chat()

##### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `$user_id` | int | Yes | آیدی کاربر تلگرام |
| `$message` | string | Yes | پیام کاربر |
| `$context` | array | No | Context اضافی |

##### Context Structure

```php
$context = [
    'system' => 'تو دستیار یک یوتیوبر فارسی‌زبان هستی. دوستانه و کوتاه جواب بده.',
    'history' => [
        ['role' => 'user', 'content' => 'سلام'],
        ['role' => 'assistant', 'content' => 'سلام! چطور می‌تونم کمکت کنم؟']
    ],
    'user_info' => [
        'name' => 'علی',
        'is_vip' => true,
        'total_donations' => 1500000
    ]
];
```

##### Response

```php
[
    'success' => true,
    'message' => 'سلام علی جان! خوشحالم که VIP هستی 🎉',
    'tokens_used' => 150,
    'model' => 'gpt-4o-mini',
    'finish_reason' => 'stop'
]
```

##### Error Response

```php
[
    'success' => false,
    'error' => 'Rate limit exceeded',
    'code' => 429
]
```

---

### Claude Integration

#### Class Usage

```php
use App\AI\Claude;

$ai = new Claude([
    'api_key' => config('ai.api_key'),
    'model' => 'claude-3-5-sonnet-20241022',
    'max_tokens' => 500
]);

$response = $ai->chat($user_id, $message);
```

---

## 📊 Statistics API

### Get Dashboard Stats

#### Endpoint

```
GET /admin/api/stats.php
```

#### Headers

```http
Accept: application/json
```

#### Response

```json
{
  "success": true,
  "data": {
    "users": {
      "total": 1500,
      "today": 10,
      "week": 50,
      "month": 200,
      "vip": 50,
      "blocked": 5
    },
    "messages": {
      "total": 5000,
      "today": 100,
      "incoming": 4000,
      "outgoing": 1000
    },
    "donations": {
      "total_amount": 50000000,
      "total_count": 500,
      "today_amount": 1000000,
      "today_count": 10,
      "month_amount": 15000000,
      "month_count": 150,
      "average_amount": 100000
    },
    "keywords": {
      "total": 25,
      "active": 20,
      "inactive": 5
    },
    "chart_data": {
      "labels": ["2024-01-01", "2024-01-02", "2024-01-03", "..."],
      "datasets": [
        {
          "label": "کاربران جدید",
          "data": [10, 15, 12, "..."],
          "color": "#3b82f6"
        },
        {
          "label": "دونیت‌ها (تومان)",
          "data": [100000, 150000, 200000, "..."],
          "color": "#10b981"
        }
      ]
    }
  },
  "meta": {
    "generated_at": "2024-01-20T15:30:00Z",
    "cache_ttl": 300
  }
}
```

### Get User Statistics

#### Endpoint

```
GET /admin/api/stats/user/{user_id}.php
```

#### Response

```json
{
  "success": true,
  "data": {
    "user_id": 123456789,
    "username": "ali_m",
    "first_name": "علی",
    "is_vip": true,
    "joined_at": "2024-01-15T10:30:00Z",
    "last_seen": "2024-01-20T15:45:00Z",
    "activity": {
      "total_messages": 25,
      "messages_today": 3,
      "last_message_at": "2024-01-20T15:45:00Z"
    },
    "donations": {
      "total_amount": 1500000,
      "total_count": 5,
      "last_donation_at": "2024-01-18T12:00:00Z",
      "average_amount": 300000
    },
    "engagement": {
      "score": 85,
      "level": "Active",
      "days_since_last_visit": 2
    }
  }
}
```

---

## ❌ مدیریت خطا

### Error Response Format

```json
{
  "success": false,
  "error": {
    "code": 400,
    "type": "ValidationError",
    "message": "فیلدهای اجباری پر نشده‌اند",
    "details": {
      "username": "نام کاربری الزامی است",
      "password": "رمز عبور باید حداقل 8 کاراکتر باشد"
    }
  },
  "meta": {
    "timestamp": 1234567890,
    "request_id": "abc123"
  }
}
```

### Error Codes

| Code | Type | Description |
|------|------|-------------|
| 400 | BadRequest | درخواست نامعتبر |
| 401 | Unauthorized | احراز هویت ناموفق |
| 403 | Forbidden | دسترسی غیرمجاز |
| 404 | NotFound | منبع یافت نشد |
| 405 | MethodNotAllowed | متد HTTP نامعتبر |
| 422 | ValidationError | خطای اعتبارسنجی |
| 429 | TooManyRequests | محدودیت نرخ درخواست |
| 500 | InternalServerError | خطای داخلی سرور |
| 503 | ServiceUnavailable | سرویس در دسترس نیست |

### Telegram API Errors

| Error Code | Description | Solution |
|------------|-------------|----------|
| 400 | Bad Request | بررسی پارامترها |
| 401 | Unauthorized | بررسی Bot Token |
| 403 | Forbidden | ربات بلاک شده |
| 404 | Not Found | چت یافت نشد |
| 429 | Too Many Requests | صبر کنید و دوباره تلاش کنید |

### Error Handling Example

```php
try {
    $response = telegram_request('sendMessage', $params);
    
    if (!$response['ok']) {
        throw new TelegramException(
            $response['description'],
            $response['error_code']
        );
    }
} catch (TelegramException $e) {
    Logger::error('Telegram API Error', [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'params' => $params
    ]);
    
    // مدیریت خطا
    if ($e->getCode() === 429) {
        sleep($e->getRetryAfter());
        // تلاش مجدد
    }
}
```

---

## 🚦 Rate Limiting

### Default Limits

| Endpoint | Limit | Window |
|----------|-------|--------|
| Telegram Webhook | 60 requests | 1 minute |
| Admin Login | 5 attempts | 1 minute |
| Admin API | 100 requests | 1 minute |
| Public API | 30 requests | 1 minute |

### Rate Limit Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1234567890
```

### Rate Limit Response

```json
{
  "success": false,
  "error": {
    "code": 429,
    "type": "TooManyRequests",
    "message": "تعداد درخواست‌ها بیش از حد مجاز است",
    "retry_after": 30
  }
}
```

### Configuration

```php
// config/config.php
'security' => [
    'rate_limit' => [
        'webhook' => [
            'limit' => 60,
            'window' => 60 // seconds
        ],
        'admin_login' => [
            'limit' => 5,
            'window' => 60
        ],
        'admin_api' => [
            'limit' => 100,
            'window' => 60
        ]
    ]
]
```

---

## 🔔 Webhooks

### Telegram Webhook Events

| Event | Description | Data Structure |
|-------|-------------|----------------|
| `message` | پیام متنی دریافت شد | `Update.message` |
| `callback_query` | کلیک روی دکمه | `Update.callback_query` |
| `photo` | عکس دریافت شد | `Update.message.photo` |
| `document` | فایل دریافت شد | `Update.message.document` |
| `video` | ویدئو دریافت شد | `Update.message.video` |
| `audio` | صدا دریافت شد | `Update.message.audio` |
| `location` | موقعیت مکانی | `Update.message.location` |
| `contact` | اطلاعات تماس | `Update.message.contact` |

### Donation Webhook Events

| Event | Description | Payload |
|-------|-------------|---------|
| `payment.initiated` | پرداخت شروع شد | `{order_id, amount, user_id}` |
| `payment.success` | پرداخت موفق | `{order_id, ref_id, amount}` |
| `payment.failed` | پرداخت ناموفق | `{order_id, error}` |
| `payment.pending` | پرداخت در انتظار | `{order_id, status}` |

### Webhook Security

```php
// بررسی امضا
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');
$expected = hash_hmac('sha256', $payload, config('webhook.secret'));

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}
```

---

## 💻 نمونه کدها

### PHP Examples

#### Send Message to User

```php
<?php
require_once 'vendor/autoload.php';

use App\Telegram\Bot;

$bot = new Bot(config('telegram.bot_token'));

// ارسال پیام ساده
$response = $bot->sendMessage(123456789, 'سلام! 👋');

// ارسال پیام با دکمه
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '💰 حمایت', 'callback_data' => 'donate'],
            ['text' => '🎬 یوتیوب', 'url' => 'https://youtube.com/...']
        ]
    ]
];

$response = $bot->sendMessage(123456789, 'منوی اصلی:', $keyboard);

// ارسال عکس
$response = $bot->sendPhoto(123456789, $file_id, 'توضیحات عکس');
```

#### Get User Statistics

```php
<?php
use App\Admin\Users;

$users = new Users();
$stats = $users->getStatistics(123456789);

echo "تعداد پیام‌ها: " . $stats['messages']['total'];
echo "مجموع دونیت: " . number_format($stats['donations']['total_amount']);
```

#### Send Broadcast

```php
<?php
use App\Admin\Broadcast;

$broadcast = new Broadcast();
$result = $broadcast->send([
    'text' => '🎬 ویدئوی جدید منتشر شد!',
    'target' => 'all', // all یا vip
    'delay' => 50, // میلی‌ثانیه
    'parse_mode' => 'HTML'
]);

echo "ارسال شد: {$result['sent']}\n";
echo "خطا: {$result['failed']}\n";
```

### cURL Examples

#### Login

```bash
curl -X POST https://bot.yourdomain.com/admin/login.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin&password=YourPassword&_token=csrf_token" \
  -c cookies.txt
```

#### Get Stats

```bash
curl -X GET https://bot.yourdomain.com/admin/api/stats.php \
  -H "Accept: application/json" \
  -b cookies.txt
```

#### Send Broadcast

```bash
curl -X POST https://bot.yourdomain.com/admin/broadcast.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -b cookies.txt \
  -d "text=پیام+شما&target=all&_token=csrf_token"
```

### JavaScript Examples

#### Fetch Stats

```javascript
// دریافت آمار
fetch('/admin/api/stats.php', {
  headers: {
    'Accept': 'application/json'
  },
  credentials: 'same-origin'
})
.then(response => response.json())
.then(data => {
  console.log('کاربران:', data.data.users.total);
  console.log('دونیت‌ها:', data.data.donations.total_amount);
})
.catch(error => console.error('Error:', error));
```

#### Send Message

```javascript
// ارسال پیام به کاربر
fetch('/admin/chat.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
  },
  credentials: 'same-origin',
  body: new URLSearchParams({
    'id': 123456789,
    'reply': 'سلام!',
    '_token': csrfToken
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    alert('پیام ارسال شد');
  }
});
```

---

## 🎯 Best Practices

### 1. امنیت

```php
// ✅ درست
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// ❌ غلط
$stmt = $pdo->query("SELECT * FROM users WHERE id = $user_id");
```

```php
// ✅ درست - CSRF Protection
if ($_POST['_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token mismatch');
}

// ❌ غلط - بدون CSRF
// هیچ بررسی‌ای نشده
```

### 2. اعتبارسنجی

```php
// ✅ درست
$validator = new Validator($_POST);
$validator->required('username')
          ->minLength('username', 3)
          ->maxLength('username', 50)
          ->email('email');

if ($validator->fails()) {
    return response()->json([
        'success' => false,
        'errors' => $validator->errors()
    ], 422);
}

// ❌ غلط - بدون اعتبارسنجی
$username = $_POST['username']; // مستقیم استفاده شده
```

### 3. مدیریت خطا

```php
// ✅ درست
try {
    $response = telegram_request('sendMessage', $params);
    
    if (!$response['ok']) {
        throw new Exception($response['description']);
    }
} catch (Exception $e) {
    Logger::error('Telegram Error', [
        'message' => $e->getMessage(),
        'params' => $params
    ]);
    
    // مدیریت خطا
}

// ❌ غلط - بدون مدیریت خطا
$response = telegram_request('sendMessage', $params);
// هیچ بررسی‌ای نشده
```

### 4. بهینه‌سازی کوئری

```php
// ✅ درست - با Index
$users = $pdo->query("
    SELECT id, username, first_name 
    FROM users 
    WHERE is_vip = 1 
    AND last_seen > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY last_seen DESC 
    LIMIT 20
")->fetchAll();

// ❌ غلط - بدون Index و SELECT *
$users = $pdo->query("SELECT * FROM users WHERE is_vip = 1")->fetchAll();
```

### 5. کش‌گذاری

```php
// ✅ درست - با Cache
$cache_key = "stats_dashboard";
$stats = Cache::get($cache_key);

if (!$stats) {
    $stats = calculateStats();
    Cache::set($cache_key, $stats, 300); // 5 دقیقه
}

// ❌ غلط - بدون Cache
$stats = calculateStats(); // هر بار محاسبه می‌شه
```

---

## 📝 تغییرات نسخه

### Version 2.0.0 (Current)

#### Added
- ✅ CSRF Protection
- ✅ Rate Limiting
- ✅ AI Integration (OpenAI/Claude)
- ✅ Advanced Statistics API
- ✅ Webhook Signature Verification

#### Changed
- ⚡ Improved error handling
- ⚡ Better response format
- ⚡ Enhanced security

#### Deprecated
- ⚠️ Old authentication method (will be removed in 3.0)

### Version 1.0.0

#### Initial Release
- ✅ Basic Telegram Webhook
- ✅ Admin Panel API
- ✅ Donation Callback
- ✅ User Management

---

<div align="center">

## 📚 مستندات مرتبط

[📖 README](README.md) • [📦 INSTALL](INSTALL.md) • [🔒 SECURITY](SECURITY.md) • [📝 CHANGELOG](CHANGELOG.md)

---

**📌 آخرین بروزرسانی: 2024-01-20**

**📧 سوالات؟ ایمیل بزنید: support@yourdomain.com**

[⬆ بازگشت به بالا](#-مستندات-api)

</div>
```