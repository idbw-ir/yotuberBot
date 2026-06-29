<?php
/**
 * ============================================
 * کلاس توابع امنیتی (Security Helper)
 * ============================================
 * محافظت در برابر حملات رایج
 * XSS, SQL Injection, CSRF
 * اعتبارسنجی ورودی‌ها
 * رمزنگاری و Hash
 * Static Methods
 */

namespace App\Helpers;

use Exception;

class Security {
    
    // ──────────────────────────────────────
    // 1. محافظت XSS
    // ──────────────────────────────────────
    
    /**
     * پاکسازی ورودی از XSS
     */
    public static function cleanXss($input) {
        if (is_array($input)) {
            return array_map([self::class, 'cleanXss'], $input);
        }
        
        // حذف تگ‌های خطرناک
        $input = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $input);
        $input = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $input);
        $input = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $input);
        $input = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $input);
        
        // حذف event handlers
        $input = preg_replace('#on\w+\s*=\s*["\'](.*?)["\']#is', '', $input);
        
        // حذف javascript: URLs
        $input = preg_replace('#javascript\s*:#is', '', $input);
        $input = preg_replace('#vbscript\s*:#is', '', $input);
        
        // حذف expression()
        $input = preg_replace('#expression\s*\((.*?)\)#is', '', $input);
        
        return $input;
    }
    
    /**
     * Escape کردن HTML
     */
    public static function escape($input, $flags = ENT_QUOTES | ENT_HTML5) {
        if (is_array($input)) {
            return array_map([self::class, 'escape'], $input);
        }
        
        return htmlspecialchars((string)$input, $flags, 'UTF-8');
    }
    
    /**
     * Escape برای استفاده در JavaScript
     */
    public static function escapeJs($input) {
        return json_encode($input, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    
    // ──────────────────────────────────────
    // 2. محافظت SQL Injection
    // ──────────────────────────────────────
    
    /**
     * پاکسازی ورودی برای SQL
     * توجه: همیشه از Prepared Statements استفاده کنید
     */
    public static function cleanSql($input) {
        if (is_array($input)) {
            return array_map([self::class, 'cleanSql'], $input);
        }
        
        // حذف کاراکترهای خطرناک
        $input = str_replace(['\\', "\0", "'", '"', ';', '--', '/*', '*/'], '', $input);
        
        return trim($input);
    }
    
    /**
     * بررسی آیا ورودی عددی است
     */
    public static function isNumeric($input) {
        return is_numeric($input) && $input == (int)$input;
    }
    
    /**
     * تبدیل به عدد صحیح امن
     */
    public static function toInt($input, $default = 0) {
        if (is_numeric($input)) {
            return (int)$input;
        }
        return $default;
    }
    
    // ──────────────────────────────────────
    // 3. CSRF Protection
    // ──────────────────────────────────────
    
    /**
     * تولید توکن CSRF
     */
    public static function generateCsrfToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * بررسی توکن CSRF
     */
    public static function verifyCsrfToken($token, $sessionToken) {
        if (empty($token) || empty($sessionToken)) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * تولید Hidden Input برای CSRF
     */
    public static function csrfField($token) {
        $token = self::escape($token);
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
    
    // ──────────────────────────────────────
    // 4. رمزنگاری و Hash
    // ──────────────────────────────────────
    
    /**
     * Hash کردن رمز عبور
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * بررسی رمز عبور
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * تولید رمز عبور تصادفی قوی
     */
    public static function generatePassword($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        
        return $password;
    }
    
    /**
     * Hash کردن داده با SHA256
     */
    public static function hashSha256($data) {
        return hash('sha256', $data);
    }
    
    /**
     * Hash کردن داده با HMAC
     */
    public static function hmac($data, $key, $algo = 'sha256') {
        return hash_hmac($algo, $data, $key);
    }
    
    // ──────────────────────────────────────
    // 5. اعتبارسنجی ورودی‌ها
    // ──────────────────────────────────────
    
    /**
     * بررسی ایمیل معتبر
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * بررسی URL معتبر
     */
    public static function isValidUrl($url, $requireHttps = false) {
        $valid = filter_var($url, FILTER_VALIDATE_URL) !== false;
        
        if ($valid && $requireHttps) {
            return strpos($url, 'https://') === 0;
        }
        
        return $valid;
    }
    
    /**
     * بررسی IP معتبر
     */
    public static function isValidIp($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * بررسی توکن تلگرام معتبر
     */
    public static function isValidTelegramToken($token) {
        $pattern = '/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/';
        return preg_match($pattern, $token) === 1;
    }
    
    /**
     * بررسی آیدی عددی تلگرام
     */
    public static function isValidTelegramId($id) {
        return is_numeric($id) && $id >= 100000 && $id <= 9999999999;
    }
    
    /**
     * بررسی نام کاربری معتبر
     */
    public static function isValidUsername($username, $minLength = 3, $maxLength = 50) {
        $length = strlen($username);
        
        if ($length < $minLength || $length > $maxLength) {
            return false;
        }
        
        return preg_match('/^[a-zA-Z0-9_]+$/', $username) === 1;
    }
    
    // ──────────────────────────────────────
    // 6. محافظت فایل آپلود
    // ──────────────────────────────────────
    
    /**
     * بررسی نوع فایل مجاز
     */
    public static function isAllowedFileType($file, array $allowedTypes) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }
    
    /**
     * بررسی اندازه فایل
     */
    public static function isAllowedFileSize($file, $maxSize) {
        return $file['size'] <= $maxSize;
    }
    
    /**
     * تولید نام فایل امن
     */
    public static function generateSafeFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
        return $safeName;
    }
    
    /**
     * بررسی پسوند فایل مجاز
     */
    public static function isAllowedExtension($filename, array $allowedExtensions) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions);
    }
    
    // ──────────────────────────────────────
    // 7. Rate Limiting
    // ──────────────────────────────────────
    
    /**
     * بررسی Rate Limit
     */
    public static function checkRateLimit($key, $maxAttempts, $windowSeconds) {
        $cacheKey = 'rate_limit_' . md5($key);
        $cache = \App\Core\Cache::getInstance();
        
        $data = $cache->get($cacheKey, ['attempts' => 0, 'first_attempt' => time()]);
        
        // اگر پنجره زمانی گذشته، ریست کن
        if (time() - $data['first_attempt'] > $windowSeconds) {
            $data = ['attempts' => 0, 'first_attempt' => time()];
        }
        
        $data['attempts']++;
        $cache->set($cacheKey, $data, $windowSeconds);
        
        return [
            'allowed' => $data['attempts'] <= $maxAttempts,
            'attempts' => $data['attempts'],
            'remaining' => max(0, $maxAttempts - $data['attempts']),
            'reset_at' => $data['first_attempt'] + $windowSeconds
        ];
    }
    
    /**
     * دریافت IP کاربر
     */
    public static function getClientIp() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // اگر چند IP بود، اولین IP رو بگیر
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (self::isValidIp($ip)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    // ──────────────────────────────────────
    // 8. محافظت در برابر حملات
    // ──────────────────────────────────────
    
    /**
     * بررسی User-Agent معتبر
     */
    public static function isValidUserAgent($userAgent = null) {
        $userAgent = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return false;
        }
        
        // لیست User-Agent های مشکوک
        $suspicious = ['sqlmap', 'nikto', 'nmap', 'masscan', 'dirbuster'];
        
        foreach ($suspicious as $tool) {
            if (stripos($userAgent, $tool) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * بررسی Referrer معتبر
     */
    public static function isValidReferrer($allowedDomains = []) {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (empty($referrer)) {
            return false;
        }
        
        $referrerHost = parse_url($referrer, PHP_URL_HOST);
        
        if (empty($allowedDomains)) {
            // اگر لیست داده نشده، دامنه فعلی رو قبول کن
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            return $referrerHost === $currentHost;
        }
        
        return in_array($referrerHost, $allowedDomains);
    }
    
    /**
     * محافظت در برابر Directory Traversal
     */
    public static function sanitizePath($path) {
        // حذف ../ و ..\
        $path = str_replace(['../', '..\\', '..'], '', $path);
        
        // فقط کاراکترهای مجاز
        $path = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $path);
        
        return $path;
    }
    
    /**
     * محافظت در برابر Command Injection
     */
    public static function sanitizeCommand($input) {
        // حذف کاراکترهای خطرناک
        $dangerous = [';', '|', '&', '$', '`', '(', ')', '{', '}', '<', '>', '\\', "\n", "\r"];
        $input = str_replace($dangerous, '', $input);
        
        return escapeshellarg($input);
    }
    
    // ──────────────────────────────────────
    // 9. توابع کمکی
    // ──────────────────────────────────────
    
    /**
     * تولید رشته تصادفی امن
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * تولید UUID
     */
    public static function generateUuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * بررسی HTTPS
     */
    public static function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * اجبار HTTPS
     */
    public static function forceHttps() {
        if (!self::isHttps()) {
            $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
            header("Location: {$url}", true, 301);
            exit;
        }
    }
    
    /**
     * تنظیم Security Headers
     */
    public static function setSecurityHeaders() {
        // جلوگیری از Clickjacking
        header('X-Frame-Options: DENY');
        
        // محافظت XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // جلوگیری از MIME Sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // HTTPS فقط (اگر HTTPS فعاله)
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * مخفی کردن نسخه PHP
     */
    public static function hidePhpVersion() {
        header_remove('X-Powered-By');
        ini_set('expose_php', 'Off');
    }
    
    // ──────────────────────────────────────
    // 10. Validation Helper
    // ──────────────────────────────────────
    
    /**
     * اعتبارسنجی سریع
     */
    public static function validate(array $data, array $rules) {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $rulesArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            
            foreach ($rulesArray as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;
                
                $error = self::applyRule($field, $value, $ruleName, $ruleParam);
                
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        
        return [
            'passes' => empty($errors),
            'fails' => !empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * اعمال یک Rule
     */
    private static function applyRule($field, $value, $rule, $param) {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return 'این فیلد الزامی است';
                }
                break;
                
            case 'email':
                if (!empty($value) && !self::isValidEmail($value)) {
                    return 'ایمیل معتبر نیست';
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return 'باید عدد باشد';
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $param) {
                    return "حداقل {$param} کاراکتر لازم است";
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $param) {
                    return "حداکثر {$param} کاراکتر مجاز است";
                }
                break;
                
            case 'url':
                if (!empty($value) && !self::isValidUrl($value)) {
                    return 'URL معتبر نیست';
                }
                break;
                
            case 'ip':
                if (!empty($value) && !self::isValidIp($value)) {
                    return 'IP معتبر نیست';
                }
                break;
        }
        
        return null;
    }
}