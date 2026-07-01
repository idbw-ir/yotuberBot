<?php
/**
 * ============================================
 * Front Controller - نقطه ورود اصلی
 * ============================================
 * نسخه: 2.1.2
 * 
 * این فایل تمام درخواست‌های HTTP رو مدیریت می‌کنه
 * و به Controller های مناسب هدایت می‌کنه
 */

// ──────────────────────────────────────
// 1. تنظیمات اولیه
// ──────────────────────────────────────

// گزارش خطا (فقط در حالت توسعه)
error_reporting(E_ALL);
ini_set('display_errors', 0); // در Production خاموش باشه
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Tehran');

// Encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// ──────────────────────────────────────
// 2. تعریف ثابت‌های پایه
// ──────────────────────────────────────

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('APP_VERSION', '2.1.2');
define('START_TIME', microtime(true));
define('START_MEMORY', memory_get_usage());

// ──────────────────────────────────────
// 3. بررسی نصب بودن پروژه
// ──────────────────────────────────────

if (!file_exists(CONFIG_PATH . '/config.php')) {
    // پروژه نصب نشده - هدایت به نصب‌کننده
    if (file_exists(BASE_PATH . '/install.php')) {
        header('Location: /install.php');
        exit;
    }
    
    // نصب‌کننده هم نیست - خطا
    http_response_code(500);
    die('<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">
        <title>خطا</title><script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-gray-900 min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 rounded-2xl p-8 max-w-md text-center border border-gray-700">
            <div class="text-5xl mb-4">❌</div>
            <h1 class="text-2xl font-bold text-white mb-3">پروژه نصب نشده است</h1>
            <p class="text-gray-400">فایل config.php یافت نشد. لطفاً ابتدا نصب‌کننده را اجرا کنید.</p>
        </div></body></html>');
}

// ──────────────────────────────────────
// 4. بارگذاری Autoloader
// ──────────────────────────────────────

// Composer Autoloader (اگر وجود داشته باشه)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Custom Autoloader
spl_autoload_register(function ($class) {
    // تبدیل namespace به مسیر فایل
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

// ──────────────────────────────────────
// 5. بارگذاری فایل‌های کمکی
// ──────────────────────────────────────

// بارگذاری Config
$config = require CONFIG_PATH . '/config.php';

// بارگذاری Helper Functions
if (file_exists(BASE_PATH . '/app/helpers.php')) {
    require_once BASE_PATH . '/app/helpers.php';
}

// ──────────────────────────────────────
// 6. شروع Session
// ──────────────────────────────────────

try {
    $session = \App\Core\Session::getInstance();
    $session->start();
} catch (Exception $e) {
    // لاگ خطا ولی ادامه بده
    error_log('Session Error: ' . $e->getMessage());
}

// ──────────────────────────────────────
// 7. تنظیم Security Headers
// ──────────────────────────────────────

try {
    \App\Helpers\Security::setSecurityHeaders();
    \App\Helpers\Security::hidePhpVersion();
} catch (Exception $e) {
    // نادیده بگیر
}

// ──────────────────────────────────────
// 8. لاگ درخواست
// ──────────────────────────────────────

try {
    $logger = \App\Core\Logger::getInstance();
    $logger->debug('Request received', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
} catch (Exception $e) {
    // نادیده بگیر
}

// ──────────────────────────────────────
// 9. مسیریابی (Routing)
// ──────────────────────────────────────

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($uri, PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// حذف /public از URI (اگه وجود داشت)
$uri = preg_replace('#^/public#', '', $uri);

// ──────────────────────────────────────
// 10. Route های ثابت (Static Routes)
// ──────────────────────────────────────

// صفحه اصلی
if ($uri === '/' || $uri === '') {
    require PUBLIC_PATH . '/pages/home.php';
    exit;
}

// Webhook تلگرام
if ($uri === '/webhook' || $uri === '/webhook.php') {
    require PUBLIC_PATH . '/webhook.php';
    exit;
}

// ──────────────────────────────────────
// 11. Route های Admin
// ──────────────────────────────────────

if (strpos($uri, '/admin') === 0) {
    // استخراج مسیر admin
    $adminPath = substr($uri, 6); // حذف /admin
    $adminPath = $adminPath ?: '/';
    
    // هدایت به فایل‌های admin
    $adminFile = PUBLIC_PATH . '/admin' . $adminPath;
    
    // اگه فایل وجود داشت، اجراش کن
    if (file_exists($adminFile) && is_file($adminFile)) {
        require $adminFile;
        exit;
    }
    
    // اگه دایرکتوری بود، index.php رو اجرا کن
    if (is_dir($adminFile) && file_exists($adminFile . '/index.php')) {
        require $adminFile . '/index.php';
        exit;
    }
    
    // در غیر این صورت، admin/index.php رو اجرا کن
    require PUBLIC_PATH . '/admin/index.php';
    exit;
}

// ──────────────────────────────────────
// 12. Route های API
// ──────────────────────────────────────

if (strpos($uri, '/api/') === 0) {
    // استخراج مسیر API
    $apiPath = substr($uri, 5); // حذف /api/
    
    // مسیریابی API
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
                    'message' => 'API endpoint یافت نشد'
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
    }
}

// ──────────────────────────────────────
// 13. فایل‌های استاتیک
// ──────────────────────────────────────

// بررسی فایل‌های استاتیک (CSS, JS, Images)
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot'];
$fileExtension = pathinfo($uri, PATHINFO_EXTENSION);

if (in_array(strtolower($fileExtension), $staticExtensions)) {
    $filePath = PUBLIC_PATH . $uri;
    
    if (file_exists($filePath) && is_file($filePath)) {
        // تنظیم MIME Type
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

// ──────────────────────────────────────
// 14. Dynamic Routing با Router
// ──────────────────────────────────────

try {
    $router = \App\Core\Router::getInstance();
    
    // بارگذاری فایل routes
    if (file_exists(CONFIG_PATH . '/routes.php')) {
        require CONFIG_PATH . '/routes.php';
    }
    
    // اجرای Router
    $router->dispatch();
    
} catch (Exception $e) {
    // لاگ خطا
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
    
    // نمایش صفحه 404
    http_response_code(404);
    if (file_exists(BASE_PATH . '/resources/views/errors/404.php')) {
        require BASE_PATH . '/resources/views/errors/404.php';
    } else {
        echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">
            <title>404</title><script src="https://cdn.tailwindcss.com"></script></head>
            <body class="bg-gray-900 min-h-screen flex items-center justify-center">
            <div class="text-center">
                <div class="text-6xl mb-4">🔍</div>
                <h1 class="text-3xl font-bold text-white mb-2">404</h1>
                <p class="text-gray-400 mb-6">صفحه مورد نظر یافت نشد</p>
                <a href="/" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">بازگشت به خانه</a>
            </div></body></html>';
    }
}

// ──────────────────────────────────────
// 15. Performance Stats (فقط در Debug Mode)
// ──────────────────────────────────────

if (isset($config['app']['debug']) && $config['app']['debug']) {
    $executionTime = round((microtime(true) - START_TIME) * 1000, 2);
    $memoryUsage = round((memory_get_usage() - START_MEMORY) / 1024 / 1024, 2);
    
    // اضافه کردن به response (اگه HTML باشه)
    if (strpos(header('Content-Type'), 'text/html') !== false) {
        echo "\n<!-- Execution Time: {$executionTime}ms | Memory: {$memoryUsage}MB -->";
    }
}