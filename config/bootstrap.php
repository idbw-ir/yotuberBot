<?php
/**
 * ============================================
 * Bootstrap File - Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Ø§ÙˆÙ„ÛŒÙ‡ Ù¾Ø±ÙˆÚ˜Ù‡
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø§ÛŒÙ† ÙØ§ÛŒÙ„ ØªÙ…Ø§Ù… ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡ Ù¾Ø±ÙˆÚ˜Ù‡ Ø±Ùˆ Ø§Ù†Ø¬Ø§Ù… Ù…ÛŒâ€ŒØ¯Ù‡
 * Ø´Ø§Ù…Ù„:
 * - ØªØ¹Ø±ÛŒÙ Ø«Ø§Ø¨Øªâ€ŒÙ‡Ø§
 * - Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
 * - Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Config
 * - Ø´Ø±ÙˆØ¹ Session
 * - ØªÙ†Ø¸ÛŒÙ… Timezone
 * - Error Handling
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. Ø¬Ù„ÙˆÚ¯ÛŒØ±ÛŒ Ø§Ø² Ø§Ø¬Ø±Ø§ÛŒ Ù…Ø³ØªÙ‚ÛŒÙ…
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. ØªÙ†Ø¸ÛŒÙ…Ø§Øª PHP
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Ú¯Ø²Ø§Ø±Ø´ Ø®Ø·Ø§
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ø¯Ø± Production Ø®Ø§Ù…ÙˆØ´
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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. ØªØ¹Ø±ÛŒÙ Ø«Ø§Ø¨Øªâ€ŒÙ‡Ø§ÛŒ Ù¾Ø§ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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
    define('APP_VERSION', '2.1.0');
}

if (!defined('START_TIME')) {
    define('START_TIME', microtime(true));
}

if (!defined('START_MEMORY')) {
    define('START_MEMORY', memory_get_usage());
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ø¨Ø±Ø±Ø³ÛŒ Ù†ØµØ¨ Ø¨ÙˆØ¯Ù† Ù¾Ø±ÙˆÚ˜Ù‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (!file_exists(CONFIG_PATH . '/config.php')) {
    // Ù¾Ø±ÙˆÚ˜Ù‡ Ù†ØµØ¨ Ù†Ø´Ø¯Ù‡
    if (file_exists(BASE_PATH . '/install.php')) {
        if (php_sapi_name() !== 'cli') {
            header('Location: /install.php');
            exit;
        }
    }
    
    die('Error: config.php not found. Please run the installer first.');
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 5. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Composer Autoloader
if (file_exists(APP_PATH . '/Core/Autoloader.php')) {
    require_once APP_PATH . '/Core/Autoloader.php';
}

// Custom Autoloader
spl_autoload_register(function ($class) {
    // ØªØ¨Ø¯ÛŒÙ„ namespace Ø¨Ù‡ Ù…Ø³ÛŒØ± ÙØ§ÛŒÙ„
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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 6. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Config
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $config = require CONFIG_PATH . '/config.php';
    
    if (!is_array($config)) {
        throw new Exception('Config file must return an array');
    }
    
    // Ø°Ø®ÛŒØ±Ù‡ Ø¯Ø± global scope
    $GLOBALS['config'] = $config;
    
} catch (Exception $e) {
    die('Error loading config: ' . $e->getMessage());
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 7. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Helper Functions
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (file_exists(APP_PATH . '/helpers.php')) {
    require_once APP_PATH . '/helpers.php';
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 8. Ø´Ø±ÙˆØ¹ Session
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (php_sapi_name() !== 'cli') {
    try {
        $session = \App\Core\Session::getInstance();
        $session->start();
    } catch (Exception $e) {
        error_log('Session Error: ' . $e->getMessage());
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 9. ØªÙ†Ø¸ÛŒÙ… Security Headers
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (php_sapi_name() !== 'cli') {
    try {
        \App\Helpers\Security::setSecurityHeaders();
        \App\Helpers\Security::hidePhpVersion();
    } catch (Exception $e) {
        // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 10. Ø¨Ø±Ø±Ø³ÛŒ Ù¾ÙˆØ´Ù‡â€ŒÙ‡Ø§ÛŒ Ø¶Ø±ÙˆØ±ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$requiredDirs = [
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/exports',
    STORAGE_PATH . '/backups',
    STORAGE_PATH . '/uploads'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 11. Error Handler Ø³ÙØ§Ø±Ø´ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Ù„Ø§Ú¯ Ø®Ø·Ø§
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
    
    // Ø¯Ø± Development mode Ù†Ù…Ø§ÛŒØ´ Ø¨Ø¯Ù‡
    if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug']) {
        return false; // Ø¨Ú¯Ø°Ø§Ø± PHP Ø®Ø·Ø§ Ø±Ùˆ Ù†Ù…Ø§ÛŒØ´ Ø¨Ø¯Ù‡
    }
    
    return true;
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 12. Exception Handler Ø³ÙØ§Ø±Ø´ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

set_exception_handler(function($exception) {
    // Ù„Ø§Ú¯ Ø®Ø·Ø§
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
    
    // Ù†Ù…Ø§ÛŒØ´ Ø®Ø·Ø§
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        
        if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug']) {
            // Ø¯Ø± Development mode Ù†Ù…Ø§ÛŒØ´ Ú©Ø§Ù…Ù„
            echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
            echo '<title>Ø®Ø·Ø§</title><style>body{font-family:Tahoma;background:#1f2937;color:#fff;padding:40px;}';
            echo '.error{background:#ef4444;padding:20px;border-radius:8px;margin:20px 0;}';
            echo 'pre{background:#111827;padding:15px;border-radius:8px;overflow-x:auto;}</style></head>';
            echo '<body><h1>âŒ Ø®Ø·Ø§ÛŒ Ø³ÛŒØ³ØªÙ…</h1>';
            echo '<div class="error"><strong>Ù¾ÛŒØ§Ù…:</strong> ' . htmlspecialchars($exception->getMessage()) . '</div>';
            echo '<h2>Ø¬Ø²Ø¦ÛŒØ§Øª:</h2>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
            echo '</body></html>';
        } else {
            // Ø¯Ø± Production mode Ù¾ÛŒØ§Ù… Ø¹Ù…ÙˆÙ…ÛŒ
            echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8">';
            echo '<title>Ø®Ø·Ø§</title><script src="https://cdn.tailwindcss.com"></script></head>';
            echo '<body class="bg-gray-900 min-h-screen flex items-center justify-center">';
            echo '<div class="bg-gray-800 rounded-2xl p-8 max-w-md text-center border border-gray-700">';
            echo '<div class="text-5xl mb-4">âŒ</div>';
            echo '<h1 class="text-2xl font-bold text-white mb-3">Ø®Ø·Ø§ÛŒ Ø³ÛŒØ³ØªÙ…</h1>';
            echo '<p class="text-gray-400">Ù…ØªØ£Ø³ÙØ§Ù†Ù‡ Ø®Ø·Ø§ÛŒÛŒ Ø±Ø® Ø¯Ø§Ø¯Ù‡ Ø§Ø³Øª. Ù„Ø·ÙØ§Ù‹ Ø¨Ø¹Ø¯Ø§Ù‹ Ø¯ÙˆØ¨Ø§Ø±Ù‡ ØªÙ„Ø§Ø´ Ú©Ù†ÛŒØ¯.</p>';
            echo '</div></body></html>';
        }
    } else {
        // Ø¯Ø± CLI mode
        echo "Error: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . ":" . $exception->getLine() . "\n";
    }
    
    exit(1);
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 13. Shutdown Handler
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Ù„Ø§Ú¯ Ø®Ø·Ø§ÛŒ fatal
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
    
    // Performance Stats (ÙÙ‚Ø· Ø¯Ø± Debug Mode)
    if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug']) {
        $executionTime = round((microtime(true) - START_TIME) * 1000, 2);
        $memoryUsage = round((memory_get_usage() - START_MEMORY) / 1024 / 1024, 2);
        $peakMemory = round(memory_get_peak_usage() / 1024 / 1024, 2);
        
        error_log("Performance: {$executionTime}ms | Memory: {$memoryUsage}MB | Peak: {$peakMemory}MB");
    }
});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 14. Ø¨Ø±Ø±Ø³ÛŒ Ù†Ø³Ø®Ù‡ PHP
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    die('Error: This application requires PHP 8.0 or higher. Current version: ' . PHP_VERSION);
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 15. Ø¨Ø±Ø±Ø³ÛŒ Extension Ù‡Ø§ÛŒ Ø¶Ø±ÙˆØ±ÛŒ
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 16. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Database
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    // ØªØ³Øª Ø§ØªØµØ§Ù„ Ø¨Ù‡ Ø¯ÛŒØªØ§Ø¨ÛŒØ³
    $db = \App\Core\Database::getInstance();
    $db->getPdo();
    
} catch (Exception $e) {
    // Ø§Ú¯Ø± Ø¯ÛŒØªØ§Ø¨ÛŒØ³ Ø¯Ø± Ø¯Ø³ØªØ±Ø³ Ù†Ø¨ÙˆØ¯ØŒ Ù„Ø§Ú¯ Ú©Ù† ÙˆÙ„ÛŒ Ø§Ø¯Ø§Ù…Ù‡ Ø¨Ø¯Ù‡
    error_log('Database Connection Error: ' . $e->getMessage());
    
    // Ø§Ú¯Ø± Ø¯Ø± CLI mode Ù†ÛŒØ³ØªÛŒÙ… Ùˆ ØµÙØ­Ù‡ Ù†ØµØ¨ Ù†ÛŒØ³ØªØŒ Ø®Ø·Ø§ Ø¨Ø¯Ù‡
    if (php_sapi_name() !== 'cli' && !isset($_GET['install'])) {
        // ÙÙ‚Ø· Ø¯Ø± ØµÙØ­Ø§ØªÛŒ Ú©Ù‡ Ù†ÛŒØ§Ø² Ø¨Ù‡ Ø¯ÛŒØªØ§Ø¨ÛŒØ³ Ø¯Ø§Ø±Ù† Ø®Ø·Ø§ Ø¨Ø¯Ù‡
        if (!in_array(basename($_SERVER['SCRIPT_NAME']), ['install.php', 'webhook.php'])) {
            // Ø®Ø·Ø§ Ø±Ùˆ Ù„Ø§Ú¯ Ú©Ù† ÙˆÙ„ÛŒ ØµÙØ­Ù‡ Ø±Ùˆ Ù†Ù…Ø§ÛŒØ´ Ø¨Ø¯Ù‡
        }
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 17. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Cache
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $cache = \App\Core\Cache::getInstance();
    
    // Ù¾Ø§Ú©Ø³Ø§Ø²ÛŒ Ø®ÙˆØ¯Ú©Ø§Ø± Ú©Ø´â€ŒÙ‡Ø§ÛŒ Ù…Ù†Ù‚Ø¶ÛŒ Ø´Ø¯Ù‡ (Ù‡Ø± 100 Ø¯Ø±Ø®ÙˆØ§Ø³Øª)
    if (rand(1, 100) === 1) {
        $cache->gc();
    }
    
} catch (Exception $e) {
    error_log('Cache Error: ' . $e->getMessage());
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 18. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Logger
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $logger = \App\Core\Logger::getInstance();
    
    // Ù„Ø§Ú¯ Ø´Ø±ÙˆØ¹ Ø¯Ø±Ø®ÙˆØ§Ø³Øª (ÙÙ‚Ø· Ø¯Ø± Debug Mode)
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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 19. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Timezone
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

$timezone = $GLOBALS['config']['app']['timezone'] ?? 'Asia/Tehran';
date_default_timezone_set($timezone);

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 20. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Locale
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

setlocale(LC_ALL, 'fa_IR.UTF-8', 'fa_IR', 'fa');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 21. Helper Functions
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

/**
 * Ø¯Ø±ÛŒØ§ÙØª Ù…Ù‚Ø¯Ø§Ø± Ø§Ø² Config
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
 * Ø¯Ø±ÛŒØ§ÙØª URL Ú©Ø§Ù…Ù„
 */
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

/**
 * Ø¯Ø±ÛŒØ§ÙØª Ù…Ø³ÛŒØ± ÙØ§ÛŒÙ„
 */
if (!function_exists('base_path')) {
    function base_path($path = '') {
        return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

/**
 * Ø¯Ø±ÛŒØ§ÙØª Ù…Ø³ÛŒØ± Ù¾ÙˆØ´Ù‡ storage
 */
if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return STORAGE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

/**
 * Ø¯Ø±ÛŒØ§ÙØª Ù…Ø³ÛŒØ± Ù¾ÙˆØ´Ù‡ resources
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
 * Dump Ùˆ Die
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
 * Ø¨Ø±Ø±Ø³ÛŒ AJAX Request
 */
if (!function_exists('is_ajax')) {
    function is_ajax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

/**
 * Ø¨Ø±Ø±Ø³ÛŒ CLI Mode
 */
if (!function_exists('is_cli')) {
    function is_cli() {
        return php_sapi_name() === 'cli';
    }
}

/**
 * Ø¯Ø±ÛŒØ§ÙØª IP Ú©Ø§Ø±Ø¨Ø±
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
 * Back Ø¨Ù‡ ØµÙØ­Ù‡ Ù‚Ø¨Ù„ÛŒ
 */
if (!function_exists('back')) {
    function back($fallback = '/') {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        redirect($referer);
    }
}

/**
 * Ø¯Ø±ÛŒØ§ÙØª Flash Message
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
 * ØªÙ†Ø¸ÛŒÙ… Flash Message
 */
if (!function_exists('set_flash')) {
    function set_flash($key, $value) {
        $session = \App\Core\Session::getInstance();
        $session->flash($key, $value);
    }
}

/**
 * Ù†Ù…Ø§ÛŒØ´ Flash Messages
 */
if (!function_exists('display_flash_messages')) {
    function display_flash_messages() {
        $messages = flash();
        
        if (empty($messages)) {
            return '';
        }
        
        $html = '';
        $icons = [
            'success' => 'âœ…',
            'error' => 'âŒ',
            'warning' => 'âš ï¸',
            'info' => 'â„¹ï¸'
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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 22. Ù¾Ø§ÛŒØ§Ù† Bootstrap
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Ù„Ø§Ú¯ Ù…ÙˆÙÙ‚ÛŒØª Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ
if (isset($GLOBALS['config']['app']['debug']) && $GLOBALS['config']['app']['debug'] && php_sapi_name() !== 'cli') {
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->debug('Bootstrap completed', [
            'execution_time' => round((microtime(true) - START_TIME) * 1000, 2) . 'ms',
            'memory_usage' => round((memory_get_usage() - START_MEMORY) / 1024 / 1024, 2) . 'MB'
        ]);
    } catch (Exception $e) {
        // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
    }
}