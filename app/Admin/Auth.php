<?php
/**
 * ============================================
 * کلاس احراز هویت ادمین (Auth)
 * ============================================
 * مدیریت لاگین/لاگ‌اوت
 * بررسی دسترسی
 * Rate Limiting برای لاگین
 * Session Management
 * Security Features
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Session;
use App\Core\Logger;
use App\Core\Config;
use App\Helpers\Security;

class Auth {
    private static $instance = null;
    private $db;
    private $session;
    private $logger;
    private $config;
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->db = Database::getInstance();
        $this->session = Session::getInstance();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        
        // شروع Session
        $this->session->start();
    }
    
    // ──────────────────────────────────────
    // دریافت Instance (Singleton)
    // ──────────────────────────────────────
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ══════════════════════════════════════
    // احراز هویت
    // ══════════════════════════════════════
    
    /**
     * تلاش برای لاگین
     */
    public function attempt($username, $password, $remember = false) {
        // Rate Limiting
        $rateLimit = $this->checkRateLimit();
        if (!$rateLimit['allowed']) {
            $this->logger->security('Login rate limit exceeded', [
                'username' => $username,
                'ip' => Security::getClientIp()
            ]);
            
            return [
                'success' => false,
                'error' => 'تعداد تلاش‌ها بیش از حد مجاز است. لطفاً بعداً تلاش کنید.',
                'retry_after' => $rateLimit['reset_at'] - time()
            ];
        }
        
        // اعتبارسنجی ورودی‌ها
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'error' => 'نام کاربری و رمز عبور الزامی است'
            ];
        }
        
        // جستجوی کاربر
        $admin = $this->db->fetch(
            "SELECT * FROM admins WHERE username = ? AND active = 1",
            [$username]
        );
        
        if (!$admin) {
            $this->logFailedLogin($username, 'User not found');
            return [
                'success' => false,
                'error' => 'نام کاربری یا رمز عبور اشتباه است'
            ];
        }
        
        // بررسی رمز عبور
        if (!Security::verifyPassword($password, $admin['password_hash'])) {
            $this->logFailedLogin($username, 'Wrong password');
            $this->incrementLoginAttempts();
            
            return [
                'success' => false,
                'error' => 'نام کاربری یا رمز عبور اشتباه است'
            ];
        }
        
        // لاگین موفق
        $this->login($admin, $remember);
        
        return [
            'success' => true,
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'name' => $admin['name'] ?? $admin['username']
            ]
        ];
    }
    
    /**
     * لاگین کردن کاربر
     */
    public function login($admin, $remember = false) {
        // Regenerate Session ID (جلوگیری از Session Fixation)
        $this->session->regenerate();
        
        // ذخیره اطلاعات در Session
        $this->session->set('admin_id', $admin['id']);
        $this->session->set('admin_username', $admin['username']);
        $this->session->set('admin_name', $admin['name'] ?? $admin['username']);
        $this->session->set('admin_role', $admin['role'] ?? 'admin');
        $this->session->set('login_time', time());
        $this->session->set('ip_address', Security::getClientIp());
        
        // Remember Me (اگر فعال باشد)
        if ($remember) {
            $this->setRememberToken($admin['id']);
        }
        
        // بروزرسانی آخرین لاگین
        $this->db->update('admins', [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip' => Security::getClientIp()
        ], 'id = ?', [$admin['id']]);
        
        // پاک کردن تلاش‌های ناموفق
        $this->clearLoginAttempts();
        
        // لاگ موفقیت
        $this->logger->info('Admin login successful', [
            'admin_id' => $admin['id'],
            'username' => $admin['username'],
            'ip' => Security::getClientIp()
        ]);
        
        // Flash Message
        $this->session->success('ورود با موفقیت انجام شد');
    }
    
    /**
     * لاگ‌اوت
     */
    public function logout() {
        $adminId = $this->session->get('admin_id');
        $username = $this->session->get('admin_username');
        
        // پاک کردن Remember Token
        $this->clearRememberToken();
        
        // نابودی Session
        $this->session->destroy();
        
        // لاگ
        if ($adminId) {
            $this->logger->info('Admin logout', [
                'admin_id' => $adminId,
                'username' => $username
            ]);
        }
        
        return true;
    }
    
    /**
     * بررسی لاگین بودن
     */
    public function check() {
        // بررسی Session
        if ($this->session->has('admin_id')) {
            return true;
        }
        
        // بررسی Remember Me
        if ($this->checkRememberToken()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * الزام لاگین (Middleware)
     */
    public function requireLogin($redirectUrl = '/admin/login.php') {
        if (!$this->check()) {
            // ذخیره URL فعلی برای بازگشت
            $this->session->set('intended_url', $_SERVER['REQUEST_URI']);
            
            header("Location: {$redirectUrl}");
            exit;
        }
    }
    
    /**
     * الزام ادمین بودن (Middleware)
     */
    public function requireAdmin($redirectUrl = '/admin/') {
        $this->requireLogin();
        
        if (!$this->isAdmin()) {
            http_response_code(403);
            die('شما دسترسی به این بخش را ندارید');
        }
    }
    
    /**
     * الزام نقش خاص (Middleware)
     */
    public function requireRole($role, $redirectUrl = '/admin/') {
        $this->requireLogin();
        
        if (!$this->hasRole($role)) {
            http_response_code(403);
            die('شما دسترسی به این بخش را ندارید');
        }
    }
    
    // ══════════════════════════════════════
    // دریافت اطلاعات کاربر
    // ══════════════════════════════════════
    
    /**
     * دریافت آیدی ادمین فعلی
     */
    public function id() {
        return $this->session->get('admin_id');
    }
    
    /**
     * دریافت نام کاربری
     */
    public function username() {
        return $this->session->get('admin_username');
    }
    
    /**
     * دریافت نام
     */
    public function name() {
        return $this->session->get('admin_name');
    }
    
    /**
     * دریافت نقش
     */
    public function role() {
        return $this->session->get('admin_role', 'admin');
    }
    
    /**
     * بررسی ادمین بودن
     */
    public function isAdmin() {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }
    
    /**
     * بررسی super admin بودن
     */
    public function isSuperAdmin() {
        return $this->hasRole('super_admin');
    }
    
    /**
     * بررسی داشتن نقش
     */
    public function hasRole($role) {
        $currentRole = $this->role();
        
        if (is_array($role)) {
            return in_array($currentRole, $role);
        }
        
        return $currentRole === $role;
    }
    
    /**
     * دریافت اطلاعات کامل ادمین
     */
    public function user() {
        $adminId = $this->id();
        
        if (!$adminId) {
            return null;
        }
        
        return $this->db->fetch("SELECT * FROM admins WHERE id = ?", [$adminId]);
    }
    
    // ══════════════════════════════════════
    // Remember Me
    // ══════════════════════════════════════
    
    /**
     * تنظیم Remember Token
     */
    private function setRememberToken($adminId) {
        $token = Security::generateRandomString(64);
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // ذخیره در دیتابیس
        $this->db->update('admins', [
            'remember_token' => hash('sha256', $token),
            'remember_expiry' => $expiry
        ], 'id = ?', [$adminId]);
        
        // تنظیم Cookie
        $secure = Security::isHttps();
        setcookie('remember_token', $token, [
            'expires' => strtotime('+30 days'),
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
    /**
     * بررسی Remember Token
     */
    private function checkRememberToken() {
        $token = $_COOKIE['remember_token'] ?? null;
        
        if (!$token) {
            return false;
        }
        
        $hashedToken = hash('sha256', $token);
        
        $admin = $this->db->fetch(
            "SELECT * FROM admins WHERE remember_token = ? AND remember_expiry > NOW() AND active = 1",
            [$hashedToken]
        );
        
        if (!$admin) {
            return false;
        }
        
        // لاگین با Remember Token
        $this->login($admin, false);
        
        return true;
    }
    
    /**
     * پاک کردن Remember Token
     */
    private function clearRememberToken() {
        $adminId = $this->session->get('admin_id');
        
        if ($adminId) {
            $this->db->update('admins', [
                'remember_token' => null,
                'remember_expiry' => null
            ], 'id = ?', [$adminId]);
        }
        
        // حذف Cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => Security::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
    // ══════════════════════════════════════
    // Rate Limiting
    // ══════════════════════════════════════
    
    /**
     * بررسی Rate Limit برای لاگین
     */
    private function checkRateLimit() {
        $ip = Security::getClientIp();
        $maxAttempts = 5; // 5 تلاش
        $windowSeconds = 300; // در 5 دقیقه
        
        return Security::checkRateLimit("login_{$ip}", $maxAttempts, $windowSeconds);
    }
    
    /**
     * افزایش تعداد تلاش‌های ناموفق
     */
    private function incrementLoginAttempts() {
        $ip = Security::getClientIp();
        $cache = \App\Core\Cache::getInstance();
        
        $key = "login_attempts_{$ip}";
        $attempts = $cache->get($key, 0);
        $cache->set($key, $attempts + 1, 300);
    }
    
    /**
     * پاک کردن تلاش‌های ناموفق
     */
    private function clearLoginAttempts() {
        $ip = Security::getClientIp();
        $cache = \App\Core\Cache::getInstance();
        
        $cache->delete("login_attempts_{$ip}");
        $cache->delete("rate_limit_login_{$ip}");
    }
    
    /**
     * لاگ تلاش ناموفق
     */
    private function logFailedLogin($username, $reason) {
        $this->logger->security('Failed login attempt', [
            'username' => $username,
            'reason' => $reason,
            'ip' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    // ══════════════════════════════════════
    // مدیریت ادمین‌ها
    // ══════════════════════════════════════
    
    /**
     * ساخت ادمین جدید
     */
    public function createAdmin($username, $password, $name = '', $role = 'admin', $email = '') {
        // اعتبارسنجی
        $validation = Security::validate([
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'role' => $role,
            'email' => $email
        ], [
            'username' => 'required|min:3|max:50|alpha_dash|unique:admins,username',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,super_admin,editor,moderator',
            'email' => 'email'
        ]);
        
        if ($validation['fails']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }
        
        // ساخت ادمین
        $adminId = $this->db->insert('admins', [
            'username' => $username,
            'password_hash' => Security::hashPassword($password),
            'name' => $name ?: $username,
            'role' => $role,
            'email' => $email,
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($adminId) {
            $this->logger->info('Admin created', [
                'admin_id' => $adminId,
                'username' => $username,
                'created_by' => $this->id()
            ]);
            
            return [
                'success' => true,
                'admin_id' => $adminId
            ];
        }
        
        return [
            'success' => false,
            'error' => 'خطا در ساخت ادمین'
        ];
    }
    
    /**
     * بروزرسانی رمز عبور
     */
    public function updatePassword($adminId, $newPassword, $currentPassword = null) {
        // اگر ادمین فعلی است، رمز فعلی را بررسی کن
        if ($adminId == $this->id() && $currentPassword) {
            $admin = $this->db->fetch("SELECT password_hash FROM admins WHERE id = ?", [$adminId]);
            
            if (!$admin || !Security::verifyPassword($currentPassword, $admin['password_hash'])) {
                return [
                    'success' => false,
                    'error' => 'رمز عبور فعلی اشتباه است'
                ];
            }
        }
        
        // اعتبارسنجی رمز جدید
        if (strlen($newPassword) < 8) {
            return [
                'success' => false,
                'error' => 'رمز عبور باید حداقل 8 کاراکتر باشد'
            ];
        }
        
        // بروزرسانی
        $this->db->update('admins', [
            'password_hash' => Security::hashPassword($newPassword)
        ], 'id = ?', [$adminId]);
        
        $this->logger->info('Password updated', [
            'admin_id' => $adminId,
            'updated_by' => $this->id()
        ]);
        
        return ['success' => true];
    }
    
    /**
     * غیرفعال کردن ادمین
     */
    public function deactivateAdmin($adminId) {
        // جلوگیری از غیرفعال کردن خود
        if ($adminId == $this->id()) {
            return [
                'success' => false,
                'error' => 'نمی‌توانید حساب خود را غیرفعال کنید'
            ];
        }
        
        $this->db->update('admins', ['active' => 0], 'id = ?', [$adminId]);
        
        $this->logger->warning('Admin deactivated', [
            'admin_id' => $adminId,
            'deactivated_by' => $this->id()
        ]);
        
        return ['success' => true];
    }
    
    /**
     * فعال کردن ادمین
     */
    public function activateAdmin($adminId) {
        $this->db->update('admins', ['active' => 1], 'id = ?', [$adminId]);
        
        $this->logger->info('Admin activated', [
            'admin_id' => $adminId,
            'activated_by' => $this->id()
        ]);
        
        return ['success' => true];
    }
    
    /**
     * حذف ادمین
     */
    public function deleteAdmin($adminId) {
        // جلوگیری از حذف خود
        if ($adminId == $this->id()) {
            return [
                'success' => false,
                'error' => 'نمی‌توانید حساب خود را حذف کنید'
            ];
        }
        
        // فقط super_admin می‌تواند حذف کند
        if (!$this->isSuperAdmin()) {
            return [
                'success' => false,
                'error' => 'فقط super_admin می‌تواند ادمین حذف کند'
            ];
        }
        
        $this->db->delete('admins', 'id = ?', [$adminId]);
        
        $this->logger->warning('Admin deleted', [
            'admin_id' => $adminId,
            'deleted_by' => $this->id()
        ]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // گزارش‌گیری
    // ══════════════════════════════════════
    
    /**
     * دریافت لیست ادمین‌ها
     */
    public function getAdmins($active = null) {
        $sql = "SELECT id, username, name, email, role, active, last_login, last_ip, created_at FROM admins";
        $params = [];
        
        if ($active !== null) {
            $sql .= " WHERE active = ?";
            $params[] = $active;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * دریافت آمار لاگین‌ها
     */
    public function getLoginStats($days = 30) {
        $sql = "SELECT DATE(last_login) as date, COUNT(*) as count 
                FROM admins 
                WHERE last_login >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(last_login)
                ORDER BY date";
        
        return $this->db->fetchAll($sql, [$days]);
    }
    
    /**
     * دریافت تلاش‌های ناموفق اخیر
     */
    public function getFailedLoginAttempts($limit = 50) {
        $logs = $this->logger->search('Failed login attempt', 'security', $limit);
        return $logs;
    }
}