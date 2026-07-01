<?php
/**
 * ============================================
 * Telegram Webhook Handler
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø§ÛŒÙ† ÙØ§ÛŒÙ„ Update Ù‡Ø§ÛŒ ØªÙ„Ú¯Ø±Ø§Ù… Ø±Ùˆ Ø¯Ø±ÛŒØ§ÙØª Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 * Ùˆ Ø¨Ù‡ Ù¾Ø±Ø¯Ø§Ø²Ø´Ú¯Ø± Ù…Ù†Ø§Ø³Ø¨ Ù‡Ø¯Ø§ÛŒØª Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. ÙÙ‚Ø· POST Ù…Ø¬Ø§Ø²
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed. Only POST is accepted.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

error_reporting(0); // Ø¯Ø± Production Ù‡ÛŒÚ† Ø®Ø·Ø§ÛŒÛŒ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù†Ø´Ù‡
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('Asia/Tehran');

// Encoding
mb_internal_encoding('UTF-8');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. ØªØ¹Ø±ÛŒÙ Ø«Ø§Ø¨Øªâ€ŒÙ‡Ø§
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('START_TIME', microtime(true));

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ø¨Ø±Ø±Ø³ÛŒ Ù†ØµØ¨ Ø¨ÙˆØ¯Ù†
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (!file_exists(CONFIG_PATH . '/config.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Project not installed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 5. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 6. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Config
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$config = require CONFIG_PATH . '/config.php';

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 7. Ø¨Ø±Ø±Ø³ÛŒ Secret Token
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$secretToken = $config['telegram']['webhook_secret'] ?? null;

if ($secretToken) {
    $receivedSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    
    if (empty($receivedSecret) || !hash_equals($secretToken, $receivedSecret)) {
        // Ù„Ø§Ú¯ ØªÙ„Ø§Ø´ Ù†Ø§Ù…ÙˆÙÙ‚
        try {
            $logger = \App\Core\Logger::getInstance();
            $logger->security('Webhook secret verification failed', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'received_secret' => substr($receivedSecret, 0, 10) . '...'
            ]);
        } catch (Exception $e) {
            // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
        }
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Forbidden: Invalid secret token'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 8. Ø¯Ø±ÛŒØ§ÙØª Raw Input
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$rawInput = file_get_contents('php://input');

if (empty($rawInput)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Empty request body'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 9. Parse JSON
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$update = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON: ' . json_last_error_msg()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 10. Ù„Ø§Ú¯ Ø¯Ø±ÛŒØ§ÙØª Update
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $logger = \App\Core\Logger::getInstance();
    $logger->telegram('Webhook update received', [
        'update_id' => $update['update_id'] ?? 'unknown',
        'type' => getUpdateType($update),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
} catch (Exception $e) {
    // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 11. Ù¾Ø±Ø¯Ø§Ø²Ø´ Update
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    // Ø³Ø§Ø®Øª Bot Instance
    $bot = new \App\Telegram\Bot();
    
    // Ø³Ø§Ø®Øª Webhook Handler
    $webhook = new \App\Telegram\Webhook($bot);
    
    // Ù¾Ø±Ø¯Ø§Ø²Ø´
    $webhook->handle();
    
    // Ù¾Ø§Ø³Ø® Ù…ÙˆÙÙ‚
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
    // Ø®Ø·Ø§ÛŒ Ø¹Ù…ÙˆÙ…ÛŒ
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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 12. Helper Functions
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

/**
 * ØªØ´Ø®ÛŒØµ Ù†ÙˆØ¹ Update
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