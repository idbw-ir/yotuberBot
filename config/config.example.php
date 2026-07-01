<?php
// ============================================
// تنظیمات اصلی ربات یوتیوبر
// ============================================

return [

    // ──────────────────────────────────────
    // پلتفرم‌های فعال: 'telegram', 'bale' یا ['telegram', 'bale']
    // ──────────────────────────────────────
    'platforms' => {{PLATFORMS}},

    // ──────────────────────────────────────
    // نوع دیتابیس: 'mysql' یا 'bunny'
    // mysql = MySQL/MariaDB (PDO)
    // bunny = Bunny Database (Turso/libSQL via HTTP)
    // ──────────────────────────────────────
    'database' => [
        'driver' => '{{DB_DRIVER}}',

        // MySQL settings
        'host' => '{{DB_HOST}}',
        'name' => '{{DB_NAME}}',
        'user' => '{{DB_USER}}',
        'pass' => '{{DB_PASS}}',
        'charset' => 'utf8mb4',

        // Bunny Database (Turso/libSQL) settings
        'bunny_url' => '{{BUNNY_URL}}',
        'bunny_token' => '{{BUNNY_TOKEN}}',
    ],

    // ──────────────────────────────────────
    // تنظیمات ربات تلگرام
    // ──────────────────────────────────────
    'telegram' => [
        'bot_token' => '{{TELEGRAM_BOT_TOKEN}}',
        'admin_id' => '{{TELEGRAM_ADMIN_ID}}',
        'webhook_secret' => '{{TELEGRAM_WEBHOOK_SECRET}}',
        'verify_ssl' => true,
    ],

    // ──────────────────────────────────────
    // تنظیمات ربات بله
    // ──────────────────────────────────────
    'bale' => [
        'bot_token' => '{{BALE_BOT_TOKEN}}',
        'admin_id' => '{{BALE_ADMIN_ID}}',
        'verify_ssl' => true,
    ],

    // ──────────────────────────────────────
    // تنظیمات عمومی سایت
    // ──────────────────────────────────────
    'app' => [
        'url' => '{{SITE_URL}}',
        'name' => '{{SITE_NAME}}',
        'timezone' => 'Asia/Tehran',
        'debug' => false,
    ],

    // ──────────────────────────────────────
    // تنظیمات پروکسی (برای دور زدن تحریم‌ها)
    // ──────────────────────────────────────
    'proxy' => [
        'enabled' => {{PROXY_ENABLED}},
        'type' => '{{PROXY_TYPE}}',
        'host' => '{{PROXY_HOST}}',
        'port' => {{PROXY_PORT}},
        'username' => '{{PROXY_USER}}',
        'password' => '{{PROXY_PASS}}',
        'dns' => '{{PROXY_DNS}}',
    ],

    // ──────────────────────────────────────
    // هوش مصنوعی
    // ──────────────────────────────────────
    'ai' => [
        'enabled' => {{AI_ENABLED}},
        'api_key' => '{{AI_API_KEY}}',
        'provider' => 'openai',
        'model' => 'gpt-4o-mini',
        'max_tokens' => 500,
        'temperature' => 0.7,
    ],
];
