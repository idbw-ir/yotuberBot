<?php
/**
 * Youtuber Bot Configuration
 * این فایل توسط نصب‌کننده ساخته می‌شود
 */

return [
    // تنظیمات دیتابیس
    'database' => [
        'host' => '{{DB_HOST}}',
        'name' => '{{DB_NAME}}',
        'user' => '{{DB_USER}}',
        'pass' => '{{DB_PASS}}',
        'charset' => 'utf8mb4',
    ],
    
    // تنظیمات تلگرام
    'telegram' => [
        'bot_token' => '{{BOT_TOKEN}}',
        'admin_id' => {{ADMIN_ID}},
        'webhook_secret' => '{{WEBHOOK_SECRET}}',
    ],
    
    // تنظیمات سایت
    'app' => [
        'name' => '{{SITE_NAME}}',
        'url' => '{{SITE_URL}}',
        'timezone' => 'Asia/Tehran',
        'debug' => false,
        'version' => '2.0.0',
    ],
    
    // هوش مصنوعی
    'ai' => [
        'enabled' => {{AI_ENABLED}},
        'provider' => 'openai',
        'api_key' => '{{AI_API_KEY}}',
        'model' => 'gpt-4o-mini',
    ],
    
    // امنیت
    'security' => [
        'csrf_enabled' => true,
        'rate_limit' => 60,
        'admin_ip_whitelist' => [],
    ],
];