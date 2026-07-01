<?php

declare(strict_types=1);

/**
 * ============================================
 * کلاس مدیریت Session
 * ============================================
 * مدیریت امن Session ها
 * پشتیبانی از Flash Messages
 * تولید و بررسی CSRF Token
 * Singleton Pattern
 */

namespace App\Core;

use Exception;

class Session {
    private static $instance = null;
    private $started = false;
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {}
    
    // ──────────────────────────────────────
    // دریافت Instance (Singleton)
    // ──────────────────────────────────────
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ──────────────────────────────────────
    // شروع Session با تنظیمات امن
    // ──────────────────────────────────────
    public function start() {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }
        
        // تنظیمات امنیتی Session
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        
        session_set_cookie_params([
            'lifetime' => 86400,     // 24 ساعت
            'path' => '/',
            'domain' => '',
            'secure' => $secure,     // فقط روی HTTPS
            'httponly' => true,      // جلوگیری از دسترسی JavaScript
            'samesite' => 'Lax'      // جلوگیری از CSRF
        ]);
        
        // نام Session
        if (!session_name()) {
            session_name('YOUTUBER_BOT_SESSION');
        }
        
        // شروع Session
        if (!session_start()) {
            throw new Exception('خطا در شروع Session');
        }
        
        $this->started = true;
        
        // بررسی انقضای Session
        $this->checkExpiration();
        
        // بازیابی Flash Messages قدیمی
        $this->ageFlashData();
    }
    
    // ──────────────────────────────────────
    // بررسی انقضای Session
    // ──────────────────────────────────────
    private function checkExpiration() {
        $timeout = 3600; // 1 ساعت
        
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > $timeout) {
                $this->destroy();
                $this->start();
                return;
            }
        }
        
        $_SESSION['_last_activity'] = time();
    }
    
    // ──────────────────────────────────────
    // دریافت مقدار
    // ──────────────────────────────────────
    public function get($key, $default = null) {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }
    
    // ──────────────────────────────────────
    // تنظیم مقدار
    // ──────────────────────────────────────
    public function set($key, $value) {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }
    
    // ──────────────────────────────────────
    // بررسی وجود
    // ──────────────────────────────────────
    public function has($key) {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }
    
    // ──────────────────────────────────────
    // حذف مقدار
    // ──────────────────────────────────────
    public function remove($key) {
        $this->ensureStarted();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    // ──────────────────────────────────────
    // دریافت همه مقادیر
    // ──────────────────────────────────────
    public function all() {
        $this->ensureStarted();
        return $_SESSION;
    }
    
    // ──────────────────────────────────────
    // پاک کردن همه مقادیر (بدون نابودی Session)
    // ──────────────────────────────────────
    public function clear() {
        $this->ensureStarted();
        $_SESSION = [];
    }
    
    // ──────────────────────────────────────
    // Flash Message - تنظیم
    // ──────────────────────────────────────
    public function flash($key, $value) {
        $this->ensureStarted();
        $_SESSION['_flash_new'][$key] = $value;
    }
    
    // ──────────────────────────────────────
    // Flash Message - دریافت
    // ──────────────────────────────────────
    public function getFlash($key, $default = null) {
        $this->ensureStarted();
        return $_SESSION['_flash_old'][$key] ?? $default;
    }
    
    // ──────────────────────────────────────
    // Flash Message - بررسی وجود
    // ──────────────────────────────────────
    public function hasFlash($key) {
        $this->ensureStarted();
        return isset($_SESSION['_flash_old'][$key]);
    }
    
    // ──────────────────────────────────────
    // Flash Message - دریافت همه
    // ──────────────────────────────────────
    public function allFlash() {
        $this->ensureStarted();
        return $_SESSION['_flash_old'] ?? [];
    }
    
    // ──────────────────────────────────────
    // قدیمی کردن Flash Messages
    // ──────────────────────────────────────
    private function ageFlashData() {
        // حذف flash های قدیمی
        unset($_SESSION['_flash_old']);
        
        // انتقال flash های جدید به قدیمی
        if (isset($_SESSION['_flash_new'])) {
            $_SESSION['_flash_old'] = $_SESSION['_flash_new'];
            unset($_SESSION['_flash_new']);
        } else {
            $_SESSION['_flash_old'] = [];
        }
    }
    
    // ──────────────────────────────────────
    // تولید CSRF Token
    // ──────────────────────────────────────
    public function generateCsrfToken() {
        $this->ensureStarted();
        
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_csrf_token'];
    }
    
    // ──────────────────────────────────────
    // دریافت CSRF Token
    // ──────────────────────────────────────
    public function getCsrfToken() {
        return $this->generateCsrfToken();
    }
    
    // ──────────────────────────────────────
    // بررسی CSRF Token
    // ──────────────────────────────────────
    public function verifyCsrfToken($token) {
        $this->ensureStarted();
        
        if (!isset($_SESSION['_csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
    
    // ──────────────────────────────────────
    // بررسی CSRF از Request
    // ──────────────────────────────────────
    public function checkCsrfFromRequest() {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !$this->verifyCsrfToken($token)) {
            http_response_code(403);
            throw new Exception('CSRF token mismatch');
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // تولید Hidden Input برای CSRF
    // ──────────────────────────────────────
    public function csrfField() {
        $token = htmlspecialchars($this->getCsrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
    
    // ──────────────────────────────────────
    // Regenerate Session ID
    // ──────────────────────────────────────
    public function regenerate($deleteOld = true) {
        $this->ensureStarted();
        
        if (!session_regenerate_id($deleteOld)) {
            throw new Exception('خطا در تغییر Session ID');
        }
        
        return session_id();
    }
    
    // ──────────────────────────────────────
    // نابودی Session
    // ──────────────────────────────────────
    public function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            // حذف Cookie Session
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
        }
        
        $this->started = false;
    }
    
    // ──────────────────────────────────────
    // دریافت Session ID
    // ──────────────────────────────────────
    public function getId() {
        $this->ensureStarted();
        return session_id();
    }
    
    // ──────────────────────────────────────
    // تنظیم Session ID
    // ──────────────────────────────────────
    public function setId($id) {
        session_id($id);
    }
    
    // ──────────────────────────────────────
    // پیام موفقیت (Flash)
    // ──────────────────────────────────────
    public function success($message) {
        $this->flash('success', $message);
    }
    
    // ──────────────────────────────────────
    // پیام خطا (Flash)
    // ──────────────────────────────────────
    public function error($message) {
        $this->flash('error', $message);
    }
    
    // ──────────────────────────────────────
    // پیام هشدار (Flash)
    // ──────────────────────────────────────
    public function warning($message) {
        $this->flash('warning', $message);
    }
    
    // ──────────────────────────────────────
    // پیام اطلاعات (Flash)
    // ──────────────────────────────────────
    public function info($message) {
        $this->flash('info', $message);
    }
    
    // ──────────────────────────────────────
    // نمایش Flash Messages به صورت HTML
    // ──────────────────────────────────────
    public function displayFlashMessages() {
        $messages = $this->allFlash();
        
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
                $text = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
                $html .= "<div class='{$color} border rounded-lg p-4 mb-3 flex items-center gap-2'>";
                $html .= "<span class='text-xl'>{$icon}</span>";
                $html .= "<span>{$text}</span>";
                $html .= "</div>";
            }
        }
        
        return $html;
    }
    
    // ──────────────────────────────────────
    // اطمینان از شروع Session
    // ──────────────────────────────────────
    private function ensureStarted() {
        if (!$this->started) {
            $this->start();
        }
    }
    
    // ──────────────────────────────────────
    // جلوگیری از Clone
    // ──────────────────────────────────────
    private function __clone() {}
    
    // ──────────────────────────────────────
    // جلوگیری از Unserialize
    // ──────────────────────────────────────
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}