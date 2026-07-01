<?php
/**
 * ============================================
 * Bootstrap File - بارگذاری اولیه پروژه
 * ============================================
 * نسخه: 2.0.0
 * 
 * این فایل تمام تنظیمات اولیه پروژه رو انجام می‌ده
 * شامل:
 * - تعریف ثابت‌ها
 * - بارگذاری Autoloader
 * - بارگذاری Config
 * - شروع Session
 * - تنظیم Timezone
 * - Error Handling
 */

// ──────────────────────────────────────
// 1. جلوگیری از اجرای مستقیم
// ──────────────────────────────────────

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// ──────────────────────────────────────
// 2. تنظیمات PHP
// ──────────────────────────────────────

// گزارش خطا
error_reporting(E_ALL);
ini_set('display_errors', 0); // در Production خاموش
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/storage/logs/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Tehran');

// Encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600);

// Memory Limit
ini_set('memory_limit', '256M');

// Execution Time
ini_set('max_execution_time', 300);

// Upload Limits
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '25M');

// ──────────────────────────────────────
// 3. تعریف ثابت‌های پایه
// ──────────────────────────────────────

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . '/public');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', BASE_PATH . '/config');
}

if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}

if (!defined('RESOURCES_PATH')) {
    define('RESOURCES_PATH', BASE_PATH . '/resources');
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '2.0.0');
}

if (!defined('START_TIME')) {
    define('START_TIME', microtime(true));
}

if (!defined('START_MEMORY')) {
    define('START_MEMORY', memory_get_usage());
}

// ──────────────────────────────────────
// 4. بررسی نصب بودن پروژه
// ──────────────────────────────────────

if (!file_exists(CONFIG_PATH . '/config.php')) {
    // پروژه نصب نشده
    if (file_exists(BASE_PATH . '/install.php')) {
        if (php_sapi_name() !== 'cli') {
            header('Location: /install.php');
            exit;
        }
    }
    
    die('Error: config.php not found. Please run the installer first.');
}

// ──────────────────────────────────────
// 5. بارگذاری Autoloader
// ──────────────────────────────────────

// Composer Autoloader
if (file_exists(APP_PATH . '/Core/Autoloader.php')) {
    require_once APP_PATH . '/Core/Autoloader.php';
}

// Custom Autoloader
spl_autoload_register(function ($class) {
    // تبدیل namespace به مسیر فایل
    // App\Core\Database -> app/Core/Database.php
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    
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

try {
    $config = require CONFIG_PATH . '/config.php';
    
    if (!is_array($config)) {
        throw new Exception('Config file must return an array');
    }
    
    // ذخیره در global scope
    $GLOBALS['config'] = $config;
    
} catch (Exception $e) {
    die('Error loading config: ' . $e->getMessage());
}

// ──────────────────────────────────────
// 7. بارگذاری Helper Functions
// ──────────────────────────────────────

if (file_exists(APP_PATH . '/helpers.php')) {
    require_once APP_PATH . '/helpers.php';
}

// ──────────────────────────────────────
// 8. شروع Session
// ──────────────────────────────────────

if (php_sapi_name() !== 'cli') {
    try {
        $session = \App\Core\Session::getInstance();
        $session->start();
    } catch (Exception $e) {
        error_log('Session Error: ' . $e->getMessage());
    }
}

// ──────────────────────────────────────
// 9. تنظیم Security Headers
// ──────────────────────────────────────

if (php_sapi_name() !== 'cli') {
    try {
        \App\Helpers\Security::setSecurityHeaders();
        \App\Helpers\Security::hidePhpVersion();
    } catch (Exception $e) {
        // نادیده بگیر
    }
}

// ──────────────────────────────────────
// 10. بررسی پوشه‌های ضروری
// ──────────────────────────────────────

$requiredDirs = [
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/exports',
    STORAGE_PATH . '/backups',
    STORAGE_PATH . '/uploads'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// ──────────────────────────────────────
// 11. Error Handler سفارشی
// ──────────────────────────────────────

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // لاگ خطا
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->error('PHP Error', [
            'errno' => $errno,
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]);
    } catch (Exception $e) {
        error_log("Error Handler Failed: {$e->getMessage()}");
    }
    
    // در Development mode نمایش بده
    if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug']) {
        return false; // بگذار PHP خطا رو نمایش بده
    }
    
    return true;
});

// ──────────────────────────────────────
// 12. Exception Handler سفارشی
// ──────────────────────────────────────

set_exception_handler(function($exception) {
    // لاگ خطا
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->critical('Uncaught Exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    } catch (Exception $e) {
        error_log("Exception Handler Failed: {$e->getMessage()}");
    }
    
    // نمایش خطا
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        
        if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug']) {
            // در Development mode نمایش کامل
            echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
            echo '<title>خطا</title><style>body{font-family:Tahoma;background:#1f2937;color:#fff;padding:40px;}';
            echo '.error{background:#ef4444;padding:20px;border-radius:8px;margin:20px 0;}';
            echo 'pre{background:#111827;padding:15px;border-radius:8px;overflow-x:auto;}</style></head>';
            echo '<body><h1>❌ خطای سیستم</h1>';
            echo '<div class="error"><strong>پیام:</strong> ' . htmlspecialchars($exception->getMessage()) . '</div>';
            echo '<h2>جزئیات:</h2>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
            echo '</body></html>';
        } else {
            // در Production mode پیام عمومی
            echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
            echo '<title>خطا</title><script src="https://cdn.tailwindcss.com"></script></head>';
            echo '<body class="bg-gray-900 min-h-screen flex items-center justify-center">';
            echo '<div class="bg-gray-800 rounded-2xl p-8 max-w-md text-center border border-gray-700">';
            echo '<div class="text-5xl mb-4">❌</div>';
            echo '<h1 class="text-2xl font-bold text-white mb-3">خطای سیستم</h1>';
            echo '<p class="text-gray-400">متأسفانه خطایی رخ داده است. لطفاً بعداً دوباره تلاش کنید.</p>';
            echo '</div></body></html>';
        }
    } else {
        // در CLI mode
        echo "Error: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
    }
    
    exit(1);
});

// ──────────────────────────────────────
// 13. Shutdown Handler
// ──────────────────────────────────────

register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // لاگ خطای fatal
        try {
            $logger = \App\Core\Logger::getInstance();
            $logger->critical('Fatal Error', [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        } catch (Exception $e) {
            error_log("Shutdown Handler Failed: {$e->getMessage()}");
        }
    }
    
    // Performance Stats (فقط در Debug Mode)
    if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug']) {
        $executionTime = round((microtime(true) - START_TIME) * 1000, 2);
        $memoryUsage = round((memory_get_usage() - START_MEMORY) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage() / 1024 / 1024, 2);
        
        error_log("Performance: {$executionTime}ms | Memory: {$memoryUsage}MB | Peak: {$peakMemory}MB");
    }
});

// ──────────────────────────────────────
// 14. بررسی نسخه PHP
// ──────────────────────────────────────

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die('Error: This application requires PHP 8.0 or higher. Current version: ' . PHP_VERSION);
}

// ──────────────────────────────────────
// 15. بررسی Extension های ضروری
// ──────────────────────────────────────

$requiredExtensions = [
    'pdo',
    'pdo_mysql',
    'curl',
    'json',
    'mbstring',
    'openssl',
    'fileinfo'
];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        die("Error: Required PHP extension '{$ext}' is not loaded.");
    }
}

// ──────────────────────────────────────
// 16. تنظیمات Database
// ──────────────────────────────────────

try {
    // تست اتصال به دیتابیس
    $db = \App\Core\Database::getInstance();
    $db->getPdo();
    
} catch (Exception $e) {
    // اگر دیتابیس در دسترس نبود، لاگ کن ولی ادامه بده
    error_log('Database Connection Error: ' . $e->getMessage());
    
    // اگر در CLI mode نیستیم و صفحه نصب نیست، خطا بده
    if (php_sapi_name() !== 'cli' && !isset($_GET['install'])) {
        // فقط در صفحاتی که نیاز به دیتابیس دارن خطا بده
        if (!in_array(basename($_SERVER['SCRIPT_NAME']), ['install.php', 'webhook.php'])) {
            // خطا رو لاگ کن ولی صفحه رو نمایش بده
        }
    }
}

// ──────────────────────────────────────
// 17. تنظیمات Cache
// ──────────────────────────────────────

try {
    $cache = \App\Core\Cache::getInstance();
    
    // پاکسازی خودکار کش‌های منقضی شده (هر 100 درخواست)
    if (rand(1, 100) === 1) {
        $cache->gc();
    }
    
} catch (Exception $e) {
    error_log('Cache Error: ' . $e->getMessage());
}

// ──────────────────────────────────────
// 18. تنظیمات Logger
// ──────────────────────────────────────

try {
    $logger = \App\Core\Logger::getInstance();
    
    // لاگ شروع درخواست (فقط در Debug Mode)
    if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug'] && php_sapi_name() !== 'cli') {
        $logger->debug('Request started', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Logger Error: ' . $e->getMessage());
}

// ──────────────────────────────────────
// 19. تنظیمات Timezone
// ──────────────────────────────────────

$timezone = $GLOBALS['config']['app']['timezone'] ?? 'Asia/Tehran';
date_default_timezone_set($timezone);

// ──────────────────────────────────────
// 20. تنظیمات Locale
// ──────────────────────────────────────

setlocale(LC_ALL, 'fa_IR.UTF-8', 'fa_IR', 'fa');

// ──────────────────────────────────────
// 21. Helper Functions
// ──────────────────────────────────────

/**
 * دریافت مقدار از Config
 */
if (!function_exists('config')) {
    function config($key = null, $default = null) {
        if ($key === null) {
            return $GLOBALS['config'] ?? [];
        }
        
        $keys = explode('.', $key);
        $value = $GLOBALS['config'] ?? [];
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

/**
 * دریافت URL کامل
 */
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

/**
 * دریافت مسیر فایل
 */
if (!function_exists('base_path')) {
    function base_path($path = '') {
        return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

/**
 * دریافت مسیر پوشه storage
 */
if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return STORAGE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

/**
 * دریافت مسیر پوشه resources
 */
if (!function_exists('resource_path')) {
    function resource_path($path = '') {
        return RESOURCES_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

/**
 * Escape HTML
 */
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Dump و Die
 */
if (!function_exists('dd')) {
    function dd(...$vars) {
        echo '<pre style="background:#1f2937;color:#fff;padding:20px;border-radius:8px;direction:ltr;text-align:left;">';
        foreach ($vars as $var) {
            var_dump($var);
            echo "\n---\n";
        }
        echo '</pre>';
        die();
    }
}

/**
 * Dump
 */
if (!function_exists('dump')) {
    function dump(...$vars) {
        echo '<pre style="background:#1f2937;color:#fff;padding:20px;border-radius:8px;direction:ltr;text-align:left;">';
        foreach ($vars as $var) {
            var_dump($var);
            echo "\n---\n";
        }
        echo '</pre>';
    }
}

/**
 * بررسی AJAX Request
 */
if (!function_exists('is_ajax')) {
    function is_ajax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * بررسی CLI Mode
 */
if (!function_exists('is_cli')) {
    function is_cli() {
        return php_sapi_name() === 'cli';
    }
}

/**
 * دریافت IP کاربر
 */
if (!function_exists('client_ip')) {
    function client_ip() {
        return \App\Helpers\Security::getClientIp();
    }
}

/**
 * Redirect
 */
if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
}

/**
 * Back به صفحه قبلی
 */
if (!function_exists('back')) {
    function back($fallback = '/') {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        redirect($referer);
    }
}

/**
 * دریافت Flash Message
 */
if (!function_exists('flash')) {
    function flash($key = null, $default = null) {
        $session = \App\Core\Session::getInstance();
        
        if ($key === null) {
            return $session->allFlash();
        }
        
        return $session->getFlash($key, $default);
    }
}

/**
 * تنظیم Flash Message
 */
if (!function_exists('set_flash')) {
    function set_flash($key, $value) {
        $session = \App\Core\Session::getInstance();
        $session->flash($key, $value);
    }
}

/**
 * نمایش Flash Messages
 */
if (!function_exists('display_flash_messages')) {
    function display_flash_messages() {
        $messages = flash();
        
        if (empty($messages)) {
            return '';
        }
        
        $html = '';
        $icons = [
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️'
        ];
        $colors = [
            'success' => 'bg-green-500/20 border-green-500/50 text-green-300',
            'error' => 'bg-red-500/20 border-red-500/50 text-red-300',
            'warning' => 'bg-yellow-500/20 border-yellow-500/50 text-yellow-300',
            'info' => 'bg-blue-500/20 border-blue-500/50 text-blue-300'
        ];
        
        foreach ($messages as $type => $message) {
            if (isset($colors[$type])) {
                $icon = $icons[$type] ?? '';
                $color = $colors[$type];
                $text = e($message);
                $html .= "<div class='{$color} border rounded-lg p-4 mb-3 flex items-center gap-2 flash-message'>";
                $html .= "<span class='text-xl'>{$icon}</span>";
                $html .= "<span>{$text}</span>";
                $html .= "</div>";
            }
        }
        
        return $html;
    }
}

// ──────────────────────────────────────
// 22. پایان Bootstrap
// ──────────────────────────────────────

// لاگ موفقیت بارگذاری
if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug'] && php_sapi_name() !== 'cli') {
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->debug('Bootstrap completed', [
            'execution_time' => round((microtime(true) - START_TIME) * 1000, 2) . 'ms',
            'memory_usage' => round((memory_get_usage() - START_MEMORY) / 1024 / 1024, 2) . 'MB'
        ]);
    } catch (Exception $e) {
        // نادیده بگیر
    }
}