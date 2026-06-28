<?php
/**
 * ============================================
 * کلاس اصلی نصب‌کننده
 * ============================================
 */

class Installer {
    private $basePath;
    private $errors = [];
    
    public function __construct($basePath) {
        $this->basePath = rtrim($basePath, '/');
    }
    
    // ──────────────────────────────────────
    // دریافت اطلاعات مرحله
    // ──────────────────────────────────────
    public function getStep($step) {
        $steps = [
            1 => ['file' => 'requirements', 'title' => 'بررسی پیش‌نیازها'],
            2 => ['file' => 'database', 'title' => 'تنظیمات دیتابیس'],
            3 => ['file' => 'telegram', 'title' => 'تنظیمات ربات'],
            4 => ['file' => 'admin', 'title' => 'ساخت ادمین'],
            5 => ['file' => 'settings', 'title' => 'تنظیمات سایت'],
            6 => ['file' => 'finish', 'title' => 'اتمام نصب'],
        ];
        return $steps[$step] ?? $steps[1];
    }
    
    // ──────────────────────────────────────
    // بررسی پیش‌نیازها
    // ──────────────────────────────────────
    public function checkRequirements() {
        $requirements = [];
        
        // PHP Version
        $requirements['php_version'] = [
            'title' => 'PHP >= 8.1',
            'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'current' => PHP_VERSION
        ];
        
        // Extensions
        $extensions = ['pdo', 'pdo_mysql', 'curl', 'mbstring', 'json', 'openssl'];
        foreach ($extensions as $ext) {
            $requirements["ext_{$ext}"] = [
                'title' => "اکستنشن {$ext}",
                'status' => extension_loaded($ext),
                'current' => extension_loaded($ext) ? 'فعال' : 'غیرفعال'
            ];
        }
        
        // Writable directories
        $dirs = ['config', 'storage', 'storage/logs', 'storage/uploads', 'storage/cache'];
        foreach ($dirs as $dir) {
            $path = $this->basePath . '/' . $dir;
            if (!is_dir($path)) @mkdir($path, 0775, true);
            $requirements["dir_{$dir}"] = [
                'title' => "پوشه {$dir}",
                'status' => is_writable($path),
                'current' => is_writable($path) ? 'Writable ✓' : 'Not Writable ✗'
            ];
        }
        
        return $requirements;
    }
    
    // ──────────────────────────────────────
    // تست اتصال دیتابیس
    // ──────────────────────────────────────
    public function testDatabaseConnection($host, $name, $user, $pass) {
        try {
            $pdo = new PDO(
                "mysql:host={$host};charset=utf8mb4",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // ساخت دیتابیس
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return ['success' => true, 'pdo' => $pdo];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ──────────────────────────────────────
    // ایمپورت اسکریپت دیتابیس
    // ──────────────────────────────────────
    public function importDatabase($pdo, $dbName) {
        $schemaFile = $this->basePath . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            return ['success' => false, 'error' => 'فایل schema.sql یافت نشد'];
        }
        
        try {
            $pdo->exec("USE `{$dbName}`");
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ──────────────────────────────────────
    // ساخت ادمین
    // ──────────────────────────────────────
    public function createAdmin($pdo, $dbName, $username, $password) {
        try {
            $pdo->exec("USE `{$dbName}`");
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$username, $hash]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ──────────────────────────────────────
    // نوشتن فایل کانفیگ
    // ──────────────────────────────────────
    public function writeConfig($data) {
        $template = file_get_contents($this->basePath . '/config/config.example.php');
        
        $replacements = [
            '{{DB_HOST}}' => $data['db_host'],
            '{{DB_NAME}}' => $data['db_name'],
            '{{DB_USER}}' => $data['db_user'],
            '{{DB_PASS}}' => $data['db_pass'],
            '{{BOT_TOKEN}}' => $data['bot_token'],
            '{{ADMIN_ID}}' => $data['admin_id'],
            '{{SITE_URL}}' => rtrim($data['site_url'], '/'),
            '{{SITE_NAME}}' => $data['site_name'],
            '{{WEBHOOK_SECRET}}' => bin2hex(random_bytes(32)),
            '{{AI_ENABLED}}' => isset($data['ai_enabled']) ? 'true' : 'false',
            '{{AI_API_KEY}}' => $data['ai_api_key'] ?? '',
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        $configPath = $this->basePath . '/config/config.php';
        if (file_put_contents($configPath, $content) === false) {
            return ['success' => false, 'error' => 'خطا در نوشتن config.php'];
        }
        
        return ['success' => true];
    }
    
    // ──────────────────────────────────────
    // تنظیم وب‌هوک تلگرام
    // ──────────────────────────────────────
    public function setWebhook($botToken, $webhookUrl, $secret) {
        $url = "https://api.telegram.org/bot{$botToken}/setWebhook";
        $params = [
            'url' => $webhookUrl,
            'secret_token' => $secret,
            'allowed_updates' => ['message', 'callback_query'],
            'max_connections' => 40
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['ok']) && $data['ok']) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => $data['description'] ?? 'خطای نامشخص'];
    }
    
    // ──────────────────────────────────────
    // تست توکن ربات
    // ──────────────────────────────────────
    public function testBotToken($token) {
        $ch = curl_init("https://api.telegram.org/bot{$token}/getMe");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['ok']) && $data['ok']) {
            return ['success' => true, 'bot' => $data['result']];
        }
        
        return ['success' => false, 'error' => 'توکن نامعتبر است'];
    }
    
    // ──────────────────────────────────────
    // قفل کردن نصب
    // ──────────────────────────────────────
    public function lockInstallation() {
        $lockFile = $this->basePath . '/install.lock';
        $content = json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return file_put_contents($lockFile, $content) !== false;
    }
    
    // ──────────────────────────────────────
    // حذف فایل‌های نصب
    // ──────────────────────────────────────
    public function removeInstaller() {
        $files = [
            $this->basePath . '/install.php',
            $this->basePath . '/installer'
        ];
        
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDirectory($file);
            } elseif (file_exists($file)) {
                @unlink($file);
            }
        }
        
        return !file_exists($this->basePath . '/install.php');
    }
    
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}