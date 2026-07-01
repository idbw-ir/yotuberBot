<?php
/**
 * ============================================
 * Admin Logout Handler
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ø®Ø±ÙˆØ¬ Ø§Ù…Ù† Ø§Ø¯Ù…ÛŒÙ† Ø§Ø² Ø³ÛŒØ³ØªÙ…
 * Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† SessionØŒ Cookie Ùˆ Cache
 */

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 1. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', dirname(__DIR__));

error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Tehran');
mb_internal_encoding('UTF-8');

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 2. Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ Autoloader
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 3. Ø¨Ø±Ø±Ø³ÛŒ CSRF Token (Ø¨Ø±Ø§ÛŒ Ø§Ù…Ù†ÛŒØª Ø¨ÛŒØ´ØªØ±)
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Ø§Ú¯Ø± Ø¯Ø±Ø®ÙˆØ§Ø³Øª POST Ø¨ÙˆØ¯ØŒ CSRF Ø±Ùˆ Ø¨Ø±Ø±Ø³ÛŒ Ú©Ù†
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    if (!isset($_POST['_token']) || !hash_equals($_SESSION['_csrf_token'] ?? '', $_POST['_token'])) {
        http_response_code(403);
        die('Ø®Ø·Ø§ÛŒ Ø§Ù…Ù†ÛŒØªÛŒ: ØªÙˆÚ©Ù† CSRF Ù†Ø§Ù…Ø¹ØªØ¨Ø± Ø§Ø³Øª');
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// 4. Ø§Ù†Ø¬Ø§Ù… Ø¹Ù…Ù„ÛŒØ§Øª Ø®Ø±ÙˆØ¬
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

try {
    $auth = \App\Admin\Auth::getInstance();
    
    // Ø¯Ø±ÛŒØ§ÙØª Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¯Ù…ÛŒÙ† Ù‚Ø¨Ù„ Ø§Ø² Ø®Ø±ÙˆØ¬ (Ø¨Ø±Ø§ÛŒ Ù„Ø§Ú¯)
    $adminId = $auth->id();
    $adminUsername = $auth->username();
    
    // Ø§Ù†Ø¬Ø§Ù… Ø¹Ù…Ù„ÛŒØ§Øª Ø®Ø±ÙˆØ¬
    $auth->logout();
    
    // Ù„Ø§Ú¯ Ø®Ø±ÙˆØ¬ Ù…ÙˆÙÙ‚
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->info('Admin logout successful', [
            'admin_id' => $adminId,
            'username' => $adminUsername,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
    }
    
    // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú©Ø´â€ŒÙ‡Ø§ÛŒ Ù…Ø±Ø¨ÙˆØ· Ø¨Ù‡ Ø§Ø¯Ù…ÛŒÙ†
    try {
        $cache = \App\Core\Cache::getInstance();
        
        // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú©Ø´â€ŒÙ‡Ø§ÛŒ Ø®Ø§Øµ Ø§Ø¯Ù…ÛŒÙ† (Ø§Ø®ØªÛŒØ§Ø±ÛŒ)
        // $cache->delete("admin_dashboard_{$adminId}");
        // $cache->delete("admin_stats_{$adminId}");
        
    } catch (Exception $e) {
        // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
    }
    
    // Ù‡Ø¯Ø§ÛŒØª Ø¨Ù‡ ØµÙØ­Ù‡ Ù„Ø§Ú¯ÛŒÙ† Ø¨Ø§ Ù¾ÛŒØ§Ù… Ù…ÙˆÙÙ‚ÛŒØª
    header('Location: /admin/login.php?error=logout');
    exit;
    
} catch (Exception $e) {
    // Ø¯Ø± ØµÙˆØ±Øª Ø®Ø·Ø§ØŒ ØªÙ„Ø§Ø´ Ø¨Ø±Ø§ÛŒ Ø®Ø±ÙˆØ¬ Ø¯Ø³ØªÛŒ
    
    // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Session
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
    
    // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Remember Me Cookie
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Ù„Ø§Ú¯ Ø®Ø·Ø§
    try {
        $logger = \App\Core\Logger::getInstance();
        $logger->error('Logout error', [
            'error' => $e->getMessage(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $logError) {
        // Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
    }
    
    // Ù‡Ø¯Ø§ÛŒØª Ø¨Ù‡ ØµÙØ­Ù‡ Ù„Ø§Ú¯ÛŒÙ†
    header('Location: /admin/login.php?error=logout');
    exit;
}