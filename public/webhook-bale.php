<?php
/**
 * Bale Webhook Handler
 * 
 * دریافت و پردازش Update های پلتفرم بله
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed. Only POST is accepted.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('START_TIME', microtime(true));

if (!file_exists(CONFIG_PATH . '/config.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Project not installed'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

$config = require CONFIG_PATH . '/config.php';

$rawInput = file_get_contents('php://input');

if (empty($rawInput)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Empty request body'], JSON_UNESCAPED_UNICODE);
    exit;
}

$update = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $logger = \App\Core\Logger::getInstance();
    $logger->telegram('Bale webhook update received', [
        'update_id' => $update['update_id'] ?? 'unknown',
        'type' => getBaleUpdateType($update),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
} catch (Exception $e) {}

try {
    $bot = new \App\Bale\Bot();
    $webhook = new \App\Bale\Webhook($bot);
    $webhook->handle();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Update processed',
        'execution_time' => round((microtime(true) - START_TIME) * 1000, 2) . 'ms'
    ], JSON_UNESCAPED_UNICODE);

} catch (\Exception $e) {
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->error('Bale webhook processing error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    } catch (Exception $logError) {
        error_log('Bale Webhook Error: ' . $e->getMessage());
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ], JSON_UNESCAPED_UNICODE);
}

function getBaleUpdateType($update) {
    if (isset($update['message'])) {
        if (isset($update['message']['text'])) return 'message_text';
        if (isset($update['message']['photo'])) return 'message_photo';
        if (isset($update['message']['video'])) return 'message_video';
        if (isset($update['message']['document'])) return 'message_document';
        if (isset($update['message']['audio'])) return 'message_audio';
        if (isset($update['message']['voice'])) return 'message_voice';
        if (isset($update['message']['location'])) return 'message_location';
        if (isset($update['message']['contact'])) return 'message_contact';
        if (isset($update['message']['sticker'])) return 'message_sticker';
        return 'message_other';
    }
    if (isset($update['callback_query'])) return 'callback_query';
    if (isset($update['edited_message'])) return 'edited_message';
    if (isset($update['pre_checkout_query'])) return 'pre_checkout_query';
    return 'unknown';
}
