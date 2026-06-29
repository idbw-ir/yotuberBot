<?php
/**
 * ============================================
 * Telegram Webhook Handler
 * ============================================
 * نسخه: 2.0.0
 * 
 * این فایل Update های تلگرام رو دریافت می‌کنه
 * و به پردازشگر مناسب هدایت می‌کنه
 */

// ──────────────────────────────────────
// 1. فقط POST مجاز
// ──────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed. Only POST is accepted.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ──────────────────────────────────────
// 2. تنظیمات اولیه
// ──────────────────────────────────────

error_reporting(0); // در Production هیچ خطایی نمایش داده نشه
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('Asia/Tehran');

// Encoding
mb_internal_encoding('UTF-8');

// ──────────────────────────────────────
// 3. تعریف ثابت‌ها
// ──────────────────────────────────────

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('START_TIME', microtime(true));

// ──────────────────────────────────────
// 4. بررسی نصب بودن
// ──────────────────────────────────────

if (!file_exists(CONFIG_PATH . '/config.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Project not installed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ──────────────────────────────────────
// 5. بارگذاری Autoloader
// ──────────────────────────────────────

// Composer
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Custom
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// ──────────────────────────────────────
// 6. بارگذاری Config
// ──────────────────────────────────────

$config = require CONFIG_PATH . '/config.php';

// ──────────────────────────────────────
// 7. بررسی Secret Token
// ──────────────────────────────────────

$secretToken = $config['telegram']['webhook_secret'] ?? null;

if ($secretToken) {
    $receivedSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    
    if (empty($receivedSecret) || !hash_equals($secretToken, $receivedSecret)) {
        // لاگ تلاش ناموفق
        try {
            $logger = \App\Core\Logger::getInstance();
            $logger->security('Webhook secret verification failed', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'received_secret' => substr($receivedSecret, 0, 10) . '...'
            ]);
        } catch (Exception $e) {
            // نادیده بگیر
        }
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Forbidden: Invalid secret token'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ──────────────────────────────────────
// 8. دریافت Raw Input
// ──────────────────────────────────────

$rawInput = file_get_contents('php://input');

if (empty($rawInput)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Empty request body'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ──────────────────────────────────────
// 9. Parse JSON
// ──────────────────────────────────────

$update = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON: ' . json_last_error_msg()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ──────────────────────────────────────
// 10. لاگ دریافت Update
// ──────────────────────────────────────

try {
    $logger = \App\Core\Logger::getInstance();
    $logger->telegram('Webhook update received', [
        'update_id' => $update['update_id'] ?? 'unknown',
        'type' => getUpdateType($update),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
} catch (Exception $e) {
    // نادیده بگیر
}

// ──────────────────────────────────────
// 11. پردازش Update
// ──────────────────────────────────────

try {
    // ساخت Bot Instance
    $bot = new \App\Telegram\Bot();
    
    // ساخت Webhook Handler
    $webhook = new \App\Telegram\Webhook($bot);
    
    // پردازش
    $webhook->handle();
    
    // پاسخ موفق
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Update processed',
        'execution_time' => round((microtime(true) - START_TIME) * 1000, 2) . 'ms'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (\App\Telegram\RateLimitException $e) {
    // Rate Limit
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Rate limit exceeded',
        'retry_after' => $e->getRetryAfter() ?? 5
    ], JSON_UNESCAPED_UNICODE);
    
} catch (\Exception $e) {
    // خطای عمومی
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->error('Webhook processing error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } catch (Exception $logError) {
        error_log('Webhook Error: ' . $e->getMessage());
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ], JSON_UNESCAPED_UNICODE);
}

// ──────────────────────────────────────
// 12. Helper Functions
// ──────────────────────────────────────

/**
 * تشخیص نوع Update
 */
function getUpdateType($update) {
    if (isset($update['message'])) {
        if (isset($update['message']['text'])) {
            return 'message_text';
        } elseif (isset($update['message']['photo'])) {
            return 'message_photo';
        } elseif (isset($update['message']['video'])) {
            return 'message_video';
        } elseif (isset($update['message']['document'])) {
            return 'message_document';
        } elseif (isset($update['message']['audio'])) {
            return 'message_audio';
        } elseif (isset($update['message']['voice'])) {
            return 'message_voice';
        } elseif (isset($update['message']['location'])) {
            return 'message_location';
        } elseif (isset($update['message']['contact'])) {
            return 'message_contact';
        } elseif (isset($update['message']['sticker'])) {
            return 'message_sticker';
        }
        return 'message_other';
    }
    
    if (isset($update['callback_query'])) {
        return 'callback_query';
    }
    
    if (isset($update['edited_message'])) {
        return 'edited_message';
    }
    
    if (isset($update['channel_post'])) {
        return 'channel_post';
    }
    
    if (isset($update['edited_channel_post'])) {
        return 'edited_channel_post';
    }
    
    if (isset($update['inline_query'])) {
        return 'inline_query';
    }
    
    if (isset($update['chosen_inline_result'])) {
        return 'chosen_inline_result';
    }
    
    if (isset($update['shipping_query'])) {
        return 'shipping_query';
    }
    
    if (isset($update['pre_checkout_query'])) {
        return 'pre_checkout_query';
    }
    
    if (isset($update['poll'])) {
        return 'poll';
    }
    
    if (isset($update['poll_answer'])) {
        return 'poll_answer';
    }
    
    if (isset($update['my_chat_member'])) {
        return 'my_chat_member';
    }
    
    if (isset($update['chat_member'])) {
        return 'chat_member';
    }
    
    if (isset($update['chat_join_request'])) {
        return 'chat_join_request';
    }
    
    return 'unknown';
}