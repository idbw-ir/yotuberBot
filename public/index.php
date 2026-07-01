<?php
/**
 * ============================================
 * Front Controller - Ù†Ù‚Ø·Ù‡ ÙˆØ±ÙˆØ¯ Ø§ØµÙ„ÛŒ
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø§ÛŒÙ† ÙØ§ÛŒÙ„ ØªÙ…Ø§Ù… Ø¯Ø±Ø®ÙˆØ§Ø³Øªâ€ŒÙ‡Ø§ÛŒ HTTP Ø±Ùˆ Ù…Ø¯ÛŒØ±ÛŒØª Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 * Ùˆ Ø¨Ù‡ Controller Ù‡Ø§ÛŒ Ù…Ù†Ø§Ø³Ø¨ Ù‡Ø¯Ø§ÛŒØª Ù…ÛŒâ€ŒÚ©Ù†Ù‡
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Ú¯Ø²Ø§Ø±Ø´ Ø®Ø·Ø§ (ÙÙ‚Ø· Ø¯Ø± Ø­Ø§Ù„Øª ØªÙˆØ³Ø¹Ù‡)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ø¯Ø± Production Ø®Ø§Ù…ÙˆØ´ Ø¨Ø§Ø´Ù‡
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Tehran');

// Encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. ØªØ¹Ø±ÛŒÙ Ø«Ø§Ø¨Øªâ€ŒÙ‡Ø§ÛŒ Ù¾Ø§ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('APP_VERSION', '2.1.0');
define('START_TIME', microtime(true));
define('START_MEMORY', memory_get_usage());

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. Ø¨Ø±Ø±Ø³ÛŒ Ù†ØµØ¨ Ø¨ÙˆØ¯Ù† Ù¾Ø±ÙˆÚ˜Ù‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (!file_exists(CONFIG_PATH . '/config.php')) {
    // Ù¾Ø±ÙˆÚ˜Ù‡ Ù†ØµØ¨ Ù†Ø´Ø¯Ù‡ - Ù‡Ø¯Ø§ÛŒØª Ø¨Ù‡ Ù†ØµØ¨â€ŒÚ©Ù†Ù†Ø¯Ù‡
    if (file_exists(BASE_PATH . '/install.php')) {
        header('Location: /install.php');
        exit;
    }
    
    // Ù†ØµØ¨â€ŒÚ©Ù†Ù†Ø¯Ù‡ Ù‡Ù… Ù†ÛŒØ³Øª - Ø®Ø·Ø§
    http_response_code(500);
    die('<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">
        <title>Ø®Ø·Ø§</title><script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-gray-900 min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md text-center border border-gray-700">
            <div class="text-5xl mb-4">âŒ</div>
            <h1 class="text-2xl font-bold text-white mb-3">Ù¾Ø±ÙˆÚ˜Ù‡ Ù†ØµØ¨ Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª</h1>
            <p class="text-gray-400">ÙØ§ÛŒÙ„ config.php ÛŒØ§ÙØª Ù†Ø´Ø¯. Ù„Ø·ÙØ§Ù‹ Ø§Ø¨ØªØ¯Ø§ Ù†ØµØ¨â€ŒÚ©Ù†Ù†Ø¯Ù‡ Ø±Ø§ Ø§Ø¬Ø±Ø§ Ú©Ù†ÛŒØ¯.</p>
        </div></body></html>');
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Composer Autoloader (Ø§Ú¯Ø± ÙˆØ¬ÙˆØ¯ Ø¯Ø§Ø´ØªÙ‡ Ø¨Ø§Ø´Ù‡)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Custom Autoloader
spl_autoload_register(function ($class) {
    // ØªØ¨Ø¯ÛŒÙ„ namespace Ø¨Ù‡ Ù…Ø³ÛŒØ± ÙØ§ÛŒÙ„
    // App\Core\Database -> app/Core/Database.php
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
// 5. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ Ú©Ù…Ú©ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Config
$config = require CONFIG_PATH . '/config.php';

// Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Helper Functions
if (file_exists(BASE_PATH . '/app/helpers.php')) {
    require_once BASE_PATH . '/app/helpers.php';
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 6. Ø´Ø±ÙˆØ¹ Session
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $session = \App\Core\Session::getInstance();
    $session->start();
} catch (Exception $e) {
    // Ù„Ø§Ú¯ Ø®Ø·Ø§ ÙˆÙ„ÛŒ Ø§Ø¯Ø§Ù…Ù‡ Ø¨Ø¯Ù‡
    error_log('Session Error: ' . $e->getMessage());
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 7. ØªÙ†Ø¸ÛŒÙ… Security Headers
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    \App\Helpers\Security::setSecurityHeaders();
    \App\Helpers\Security::hidePhpVersion();
} catch (Exception $e) {
    // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 8. Ù„Ø§Ú¯ Ø¯Ø±Ø®ÙˆØ§Ø³Øª
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $logger = \App\Core\Logger::getInstance();
    $logger->debug('Request received', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
} catch (Exception $e) {
    // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 9. Ù…Ø³ÛŒØ±ÛŒØ§Ø¨ÛŒ (Routing)
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($uri, PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Ø­Ø°Ù /public Ø§Ø² URI (Ø§Ú¯Ù‡ ÙˆØ¬ÙˆØ¯ Ø¯Ø§Ø´Øª)
$uri = preg_replace('#^/public#', '', $uri);

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 10. Route Ù‡Ø§ÛŒ Ø«Ø§Ø¨Øª (Static Routes)
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// ØµÙØ­Ù‡ Ø§ØµÙ„ÛŒ
if ($uri === '/' || $uri === '') {
    require PUBLIC_PATH . '/pages/home.php';
    exit;
}

// Webhook ØªÙ„Ú¯Ø±Ø§Ù…
if ($uri === '/webhook' || $uri === '/webhook.php') {
    require PUBLIC_PATH . '/webhook.php';
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 11. Route Ù‡Ø§ÛŒ Admin
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (strpos($uri, '/admin') === 0) {
    // Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ù…Ø³ÛŒØ± admin
    $adminPath = substr($uri, 6); // Ø­Ø°Ù /admin
    $adminPath = $adminPath ?: '/';
    
    // Ù‡Ø¯Ø§ÛŒØª Ø¨Ù‡ ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ admin
    $adminFile = PUBLIC_PATH . '/admin' . $adminPath;
    
    // Ø§Ú¯Ù‡ ÙØ§ÛŒÙ„ ÙˆØ¬ÙˆØ¯ Ø¯Ø§Ø´ØªØŒ Ø§Ø¬Ø±Ø§Ø´ Ú©Ù†
    if (file_exists($adminFile) && is_file($adminFile)) {
        require $adminFile;
        exit;
    }
    
    // Ø§Ú¯Ù‡ Ø¯Ø§ÛŒØ±Ú©ØªÙˆØ±ÛŒ Ø¨ÙˆØ¯ØŒ index.php Ø±Ùˆ Ø§Ø¬Ø±Ø§ Ú©Ù†
    if (is_dir($adminFile) && file_exists($adminFile . '/index.php')) {
        require $adminFile . '/index.php';
        exit;
    }
    
    // Ø¯Ø± ØºÛŒØ± Ø§ÛŒÙ† ØµÙˆØ±ØªØŒ admin/index.php Ø±Ùˆ Ø§Ø¬Ø±Ø§ Ú©Ù†
    require PUBLIC_PATH . '/admin/index.php';
    exit;
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 12. Route Ù‡Ø§ÛŒ API
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (strpos($uri, '/api/') === 0) {
    // Ø§Ø³ØªØ®Ø±Ø§Ø¬ Ù…Ø³ÛŒØ± API
    $apiPath = substr($uri, 5); // Ø­Ø°Ù /api/
    
    // Ù…Ø³ÛŒØ±ÛŒØ§Ø¨ÛŒ API
    $apiParts = explode('/', trim($apiPath, '/'));
    $apiResource = $apiParts[0] ?? '';
    $apiAction = $apiParts[1] ?? 'index';
    
    switch ($apiResource) {
        case 'statistics':
        case 'stats':
            require BASE_PATH . '/app/Api/StatisticsApi.php';
            $api = new \App\Api\StatisticsApi();
            $api->route(implode('/', array_slice($apiParts, 1)));
            exit;
            
        case 'donation':
            if ($apiAction === 'callback' && isset($apiParts[2])) {
                require BASE_PATH . '/app/Api/DonationCallback.php';
                $callback = new \App\Api\DonationCallback();
                $callback->handle($apiParts[2]);
                exit;
            }
            break;
            
        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 404,
                    'message' => 'API endpoint ÛŒØ§ÙØª Ù†Ø´Ø¯'
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 13. ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ Ø§Ø³ØªØ§ØªÛŒÚ©
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Ø¨Ø±Ø±Ø³ÛŒ ÙØ§ÛŒÙ„â€ŒÙ‡Ø§ÛŒ Ø§Ø³ØªØ§ØªÛŒÚ© (CSS, JS, Images)
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
$fileExtension = pathinfo($uri, PATHINFO_EXTENSION);

if (in_array(strtolower($fileExtension), $staticExtensions)) {
    $filePath = PUBLIC_PATH . $uri;
    
    if (file_exists($filePath) && is_file($filePath)) {
        // ØªÙ†Ø¸ÛŒÙ… MIME Type
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        $mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        
        // Cache Control
        header('Cache-Control: public, max-age=31536000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        readfile($filePath);
        exit;
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 14. Dynamic Routing Ø¨Ø§ Router
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $router = \App\Core\Router::getInstance();
    
    // Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ ÙØ§ÛŒÙ„ routes
    if (file_exists(CONFIG_PATH . '/routes.php')) {
        require CONFIG_PATH . '/routes.php';
    }
    
    // Ø§Ø¬Ø±Ø§ÛŒ Router
    $router->dispatch();
    
} catch (Exception $e) {
    // Ù„Ø§Ú¯ Ø®Ø·Ø§
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->error('Routing Error', [
            'error' => $e->getMessage(),
            'uri' => $uri,
            'trace' => $e->getTraceAsString()
        ]);
    } catch (Exception $logError) {
        error_log('Logger Error: ' . $logError->getMessage());
    }
    
    // Ù†Ù…Ø§ÛŒØ´ ØµÙØ­Ù‡ 404
    http_response_code(404);
    if (file_exists(BASE_PATH . '/resources/views/errors/404.php')) {
        require BASE_PATH . '/resources/views/errors/404.php';
    } else {
        echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">
            <title>404</title><script src="https://cdn.tailwindcss.com"></script></head>
            <body class="bg-gray-900 min-h-screen flex items-center justify-center">
            <div class="text-center">
                <div class="text-6xl mb-4">ðŸ”</div>
                <h1 class="text-3xl font-bold text-white mb-2">404</h1>
                <p class="text-gray-400 mb-6">ØµÙØ­Ù‡ Ù…ÙˆØ±Ø¯ Ù†Ø¸Ø± ÛŒØ§ÙØª Ù†Ø´Ø¯</p>
                <a href="/" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Ø¨Ø§Ø²Ú¯Ø´Øª Ø¨Ù‡ Ø®Ø§Ù†Ù‡</a>
            </div></body></html>';
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 15. Performance Stats (ÙÙ‚Ø· Ø¯Ø± Debug Mode)
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (isset($config['app']['debug']) && $config['app']['debug']) {
    $executionTime = round((microtime(true) - START_TIME) * 1000, 2);
    $memoryUsage = round((memory_get_usage() - START_MEMORY) / 1024 / 1024, 2);
    
    // Ø§Ø¶Ø§ÙÙ‡ Ú©Ø±Ø¯Ù† Ø¨Ù‡ response (Ø§Ú¯Ù‡ HTML Ø¨Ø§Ø´Ù‡)
    if (strpos(header('Content-Type'), 'text/html') !== false) {
        echo "\n<!-- Execution Time: {$executionTime}ms | Memory: {$memoryUsage}MB -->";
    }
}