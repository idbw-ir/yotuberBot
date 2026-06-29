<?php
/**
 * ============================================
 * Admin Logout Handler
 * ============================================
 * نسخه: 2.0.0
 * 
 * خروج امن ادمین از سیستم
 * پاک کردن Session، Cookie و Cache
 */

// ──────────────────────────────────────
// 1. تنظیمات اولیه
// ──────────────────────────────────────

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', dirname(__DIR__));

error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// ──────────────────────────────────────
// 2. بارگذاری Autoloader
// ──────────────────────────────────────

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

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
// 3. بررسی CSRF Token (برای امنیت بیشتر)
// ──────────────────────────────────────

// اگر درخواست POST بود، CSRF رو بررسی کن
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    if (!isset($_POST['_token']) || !hash_equals($_SESSION['_csrf_token'] ?? '', $_POST['_token'])) {
        http_response_code(403);
        die('خطای امنیتی: توکن CSRF نامعتبر است');
    }
}

// ──────────────────────────────────────
// 4. انجام عملیات خروج
// ──────────────────────────────────────

try {
    $auth = \App\Admin\Auth::getInstance();
    
    // دریافت اطلاعات ادمین قبل از خروج (برای لاگ)
    $adminId = $auth->id();
    $adminUsername = $auth->username();
    
    // انجام عملیات خروج
    $auth->logout();
    
    // لاگ خروج موفق
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->info('Admin logout successful', [
            'admin_id' => $adminId,
            'username' => $adminUsername,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // نادیده بگیر
    }
    
    // پاک کردن کش‌های مربوط به ادمین
    try {
        $cache = \App\Core\Cache::getInstance();
        
        // پاک کردن کش‌های خاص ادمین (اختیاری)
        // $cache->delete("admin_dashboard_{$adminId}");
        // $cache->delete("admin_stats_{$adminId}");
        
    } catch (Exception $e) {
        // نادیده بگیر
    }
    
    // هدایت به صفحه لاگین با پیام موفقیت
    header('Location: /admin/login.php?error=logout');
    exit;
    
} catch (Exception $e) {
    // در صورت خطا، تلاش برای خروج دستی
    
    // پاک کردن Session
    session_start();
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
    
    // پاک کردن Remember Me Cookie
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // لاگ خطا
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->error('Logout error', [
            'error' => $e->getMessage(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $logError) {
        // نادیده بگیر
    }
    
    // هدایت به صفحه لاگین
    header('Location: /admin/login.php?error=logout');
    exit;
}