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
            if (!is_dir($path)) mkdir($path, 0775, true);
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
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return ['success' => true, 'pdo' => $pdo, 'driver' => 'mysql'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ──────────────────────────────────────
    // تست اتصال Bunny Database
    // ──────────────────────────────────────
    public function testBunnyConnection($url, $token) {
        try {
            require_once $this->basePath . '/app/Core/DatabaseBunny.php';
            
            $bunny = new App\Core\DatabaseBunny($url, $token);
            $bunny->execute("SELECT 1");
            
            return ['success' => true, 'bunny' => $bunny, 'driver' => 'bunny'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ──────────────────────────────────────
    // ایمپورت اسکریپت دیتابیس
    // ──────────────────────────────────────
    public function importDatabase($pdoOrBunny, $dbName, $driver = 'mysql') {
        if ($driver === 'bunny') {
            return $this->importSqliteDatabase($pdoOrBunny);
        }
        return $this->importMysqlDatabase($pdoOrBunny, $dbName);
    }
    
    private function importMysqlDatabase($pdo, $dbName) {
        $schemaFiles = [
            'full' => $this->basePath . '/database/schema.sql',
            'lite' => $this->basePath . '/database/schema.lite.sql',
        ];
        
        $schemaFile = $schemaFiles['full'];
        if (!file_exists($schemaFile)) {
            $schemaFile = $schemaFiles['lite'];
        }
        if (!file_exists($schemaFile)) {
            return ['success' => false, 'error' => 'فایل schema.sql یافت نشد'];
        }
        
        $mode = ($schemaFile === $schemaFiles['full']) ? 'full' : 'lite';
        
        try {
            $pdo->exec("USE `{$dbName}`");
            
            $sql = file_get_contents($schemaFile);
            $sql = preg_replace('/--.*$/m', '', $sql);
            $statements = $this->splitSqlStatements($sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) continue;
                $pdo->exec($statement);
            }
            
            $result = ['success' => true, 'mode' => $mode];
            if ($mode === 'full') {
                return $result;
            }
            
            return array_merge($result, [
                'warning' => 'برخی featureها (Trigger, Event, Procedure) به دلیل محدودیت سرور نصب نشدند'
            ]);
            
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            
            if ($mode === 'full' && $this->isPrivilegeError($errorMsg)) {
                if (file_exists($schemaFiles['lite'])) {
                    $pdo->exec("USE `{$dbName}`");
                    
                    $sql = file_get_contents($schemaFiles['lite']);
                    $sql = preg_replace('/--.*$/m', '', $sql);
                    $statements = $this->splitSqlStatements($sql);
                    
                    foreach ($statements as $statement) {
                        $statement = trim($statement);
                        if (empty($statement)) continue;
                        $pdo->exec($statement);
                    }
                    
                    return [
                        'success' => true,
                        'mode' => 'lite',
                        'warning' => 'برخی featureها (Trigger, Event, Procedure) به دلیل محدودیت سرور نصب نشدند'
                    ];
                }
            }
            
            return ['success' => false, 'error' => $errorMsg];
        }
    }
    
    private function importSqliteDatabase($bunny) {
        $schemaFile = $this->basePath . '/database/schema.sqlite.sql';
        if (!file_exists($schemaFile)) {
            return ['success' => false, 'error' => 'فایل schema.sqlite.sql یافت نشد'];
        }
        
        try {
            $sql = file_get_contents($schemaFile);
            $sql = preg_replace('/--.*$/m', '', $sql);
            
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement)) continue;
                
                // رد کردن دستورات PRAGMA (بعضی پلتفرم‌ها مثل Turso پشتیبانی نمی‌کنند)
                if (stripos($statement, 'PRAGMA') === 0) continue;
                
                $bunny->execute($statement);
            }
            
            return ['success' => true, 'mode' => 'sqlite'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function isPrivilegeError($error) {
        $patterns = ['TRIGGER command denied', 'EVENT command denied', 'PROCEDURE command denied', 'SUPER command denied', 'Access denied'];
        foreach ($patterns as $pattern) {
            if (stripos($error, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function splitSqlStatements($sql) {
        $statements = [];
        $current = '';
        $depth = 0;
        $len = strlen($sql);
        $i = 0;
        
        while ($i < $len) {
            $char = $sql[$i];
            
            // رد کردن string literalهای ' و "
            if ($char === "'" || $char === '"') {
                $quote = $char;
                $current .= $char;
                $i++;
                while ($i < $len && $sql[$i] !== $quote) {
                    if ($sql[$i] === '\\' && $i + 1 < $len) {
                        $current .= $sql[$i];
                        $i++;
                    }
                    $current .= $sql[$i];
                    $i++;
                }
                if ($i < $len) {
                    $current .= $sql[$i];
                }
                $i++;
                continue;
            }
            
            // افزایش عمق در BEGIN
            if (strtoupper(substr($sql, $i, 5)) === 'BEGIN' && !preg_match('/[a-z_]/i', $sql[$i + 5] ?? '')) {
                $depth++;
            }
            
            // کاهش عمق در END
            if (strtoupper(substr($sql, $i, 3)) === 'END' && !preg_match('/[a-z_]/i', $sql[$i + 3] ?? '')) {
                $depth--;
            }
            
            // فقط در عمق صفر روی ; split کن
            if ($char === ';' && $depth <= 0) {
                $st = trim($current);
                if (!empty($st)) {
                    $statements[] = $st;
                }
                $current = '';
            } else {
                $current .= $char;
            }
            $i++;
        }
        
        $st = trim($current);
        if (!empty($st)) {
            $statements[] = $st;
        }
        
        return $statements;
    }
    
    // ──────────────────────────────────────
    // ساخت ادمین
    // ──────────────────────────────────────
    public function createAdmin($pdoOrBunny, $dbName, $username, $password, $driver = 'mysql') {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            
            if ($driver === 'bunny') {
                $existing = $pdoOrBunny->fetch(
                    "SELECT COUNT(*) as count FROM admins WHERE username = ?",
                    [$username]
                );
                if ($existing && $existing['count'] > 0) {
                    return ['success' => true];
                }
                $pdoOrBunny->execute(
                    "INSERT INTO admins (username, password_hash, created_at) VALUES (?, ?, datetime('now'))",
                    [$username, $hash]
                );
            } else {
                $pdoOrBunny->exec("USE `{$dbName}`");
                $stmt = $pdoOrBunny->prepare("SELECT COUNT(*) as count FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing && $existing['count'] > 0) {
                    return ['success' => true];
                }
                $stmt = $pdoOrBunny->prepare("INSERT INTO admins (username, password_hash, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$username, $hash]);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // ──────────────────────────────────────
    // نوشتن فایل کانفیگ
    // ──────────────────────────────────────
    public function writeConfig($data) {
        $template = file_get_contents($this->basePath . '/config/config.example.php');
        
        $driver = $data['db_driver'] ?? 'mysql';
        $proxyEnabled = !empty($data['proxy_enabled']) && ($data['proxy_enabled'] === '1' || $data['proxy_enabled'] === 'on');
        
        // تعیین پلتفرم‌های فعال
        $platforms = [];
        if (!empty($data['telegram_enabled'])) {
            $platforms[] = "'telegram'";
        }
        if (!empty($data['bale_enabled'])) {
            $platforms[] = "'bale'";
        }
        if (empty($platforms)) {
            // برای backward compatibility
            $platforms[] = "'telegram'";
        }
        $platformsStr = '[' . implode(', ', $platforms) . ']';
        
        $replacements = [
            '{{PLATFORMS}}' => $platformsStr,
            '{{DB_DRIVER}}' => $driver,
            '{{DB_HOST}}' => $data['db_host'] ?? 'localhost',
            '{{DB_NAME}}' => $data['db_name'] ?? 'youtuber_bot',
            '{{DB_USER}}' => $data['db_user'] ?? 'root',
            '{{DB_PASS}}' => $data['db_pass'] ?? '',
            '{{BUNNY_URL}}' => $data['bunny_url'] ?? '',
            '{{BUNNY_TOKEN}}' => $data['bunny_token'] ?? '',
            '{{TELEGRAM_BOT_TOKEN}}' => $data['bot_token'] ?? '',
            '{{TELEGRAM_ADMIN_ID}}' => $data['admin_id'] ?? '',
            '{{TELEGRAM_WEBHOOK_SECRET}}' => bin2hex(random_bytes(32)),
            '{{BALE_BOT_TOKEN}}' => $data['bale_bot_token'] ?? '',
            '{{BALE_ADMIN_ID}}' => $data['bale_admin_id'] ?? '',
            '{{SITE_URL}}' => rtrim($data['site_url'], '/'),
            '{{SITE_NAME}}' => $data['site_name'],
            '{{PROXY_ENABLED}}' => $proxyEnabled ? 'true' : 'false',
            '{{PROXY_TYPE}}' => $data['proxy_type'] ?? 'http',
            '{{PROXY_HOST}}' => $data['proxy_host'] ?? '',
            '{{PROXY_PORT}}' => (int)($data['proxy_port'] ?? 0),
            '{{PROXY_USER}}' => $data['proxy_username'] ?? '',
            '{{PROXY_PASS}}' => $data['proxy_password'] ?? '',
            '{{PROXY_DNS}}' => $data['proxy_dns'] ?? '',
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
    public function setWebhook($botToken, $webhookUrl, $secret = '') {
        $url = "https://api.telegram.org/bot{$botToken}/setWebhook";
        $params = [
            'url' => $webhookUrl,
            'allowed_updates' => ['message', 'callback_query'],
            'max_connections' => 40
        ];
        
        if (!empty($secret)) {
            $params['secret_token'] = $secret;
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $this->applyProxyToCurl($ch);
        
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
        
        $this->applyProxyToCurl($ch);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['ok']) && $data['ok']) {
            return ['success' => true, 'bot' => $data['result']];
        }
        
        return ['success' => false, 'error' => 'توکن نامعتبر است'];
    }
    
    // ──────────────────────────────────────
    // تست توکن ربات بله
    // ──────────────────────────────────────
    public function testBaleBotToken($token) {
        $ch = curl_init("https://tapi.bale.ai/bot{$token}/getMe");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $this->applyProxyToCurl($ch);
        
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
    // ──────────────────────────────────────
    // تنظیم وب‌هوک بله
    // ──────────────────────────────────────
    public function setBaleWebhook($botToken, $webhookUrl) {
        $url = "https://tapi.bale.ai/bot{$botToken}/setWebhook";
        $params = [
            'url' => $webhookUrl,
            'allowed_updates' => ['message', 'callback_query'],
            'max_connections' => 40
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $this->applyProxyToCurl($ch);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['ok']) && $data['ok']) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => $data['description'] ?? 'خطای نامشخص'];
    }
    
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
        // حذف پوشه installer (مهمترین بخش امنیتی)
        $installerDir = $this->basePath . '/installer';
        if (is_dir($installerDir)) {
            $this->deleteDirectory($installerDir);
        }
        
        // تلاش برای حذف install.php (ممکن است در ویندوز موفق نباشد چون خودش در حال اجراست)
        $installFile = $this->basePath . '/install.php';
        if (file_exists($installFile)) {
            @unlink($installFile);
        }
        
        // success اگر پوشه installer حذف شده باشد
        return !is_dir($installerDir);
    }
    
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    // ──────────────────────────────────────
    // اعمال پروکسی روی درخواست curl
    // ──────────────────────────────────────
    private function applyProxyToCurl($ch) {
        $data = $_SESSION['installer_data'] ?? [];
        
        $enabled = !empty($data['proxy_enabled']) && ($data['proxy_enabled'] === '1' || $data['proxy_enabled'] === 'on');
        if (!$enabled) {
            return;
        }
        
        $host = $data['proxy_host'] ?? '';
        $port = (int)($data['proxy_port'] ?? 0);
        
        if (empty($host) || $port <= 0) {
            return;
        }
        
        $typeMap = [
            'http' => CURLPROXY_HTTP,
            'https' => CURLPROXY_HTTPS,
            'socks4' => CURLPROXY_SOCKS4,
            'socks5' => CURLPROXY_SOCKS5,
        ];
        
        $type = $typeMap[$data['proxy_type'] ?? 'http'] ?? CURLPROXY_HTTP;
        
        curl_setopt($ch, CURLOPT_PROXY, $host);
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($ch, CURLOPT_PROXYTYPE, $type);
        
        $username = $data['proxy_username'] ?? '';
        if (!empty($username)) {
            $auth = $username;
            $password = $data['proxy_password'] ?? '';
            if (!empty($password)) {
                $auth .= ":{$password}";
            }
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
        }
        
        $dns = $data['proxy_dns'] ?? '';
        if (!empty($dns)) {
            curl_setopt($ch, CURLOPT_DNS_SERVERS, $dns);
        }
    }
}