<?php

declare(strict_types=1);

namespace App\Helpers;

use Exception;

class Security {

    public static function cleanXss($input, $allowedTags = '') {
        if (is_array($input)) {
            return array_map(function($item) use ($allowedTags) {
                return self::cleanXss($item, $allowedTags);
            }, $input);
        }

        $input = strip_tags((string)$input, $allowedTags);
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

        return $input;
    }

    public static function escape($input, $flags = ENT_QUOTES | ENT_HTML5) {
        if (is_array($input)) {
            return array_map([self::class, 'escape'], $input);
        }

        return htmlspecialchars((string)$input, $flags, 'UTF-8');
    }

    public static function escapeJs($input) {
        return json_encode($input, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

    public static function cleanSql($input) {
        if (is_array($input)) {
            return array_map([self::class, 'cleanSql'], $input);
        }

        $input = str_replace(['\\', "\0", "'", '"', ';', '--', '/*', '*/'], '', $input);

        return trim($input);
    }

    public static function isNumeric($input) {
        return is_numeric($input) && $input == (int)$input;
    }

    public static function toInt($input, $default = 0) {
        if (is_numeric($input)) {
            return (int)$input;
        }
        return $default;
    }

    public static function generateCsrfToken() {
        return bin2hex(random_bytes(32));
    }

    public static function verifyCsrfToken($token, $sessionToken) {
        if (empty($token) || empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function csrfField($token) {
        $token = self::escape($token);
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generatePassword($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }

    public static function hashSha256($data) {
        return hash('sha256', $data);
    }

    public static function hmac($data, $key, $algo = 'sha256') {
        return hash_hmac($algo, $data, $key);
    }

    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isValidUrl($url, $requireHttps = false) {
        $valid = filter_var($url, FILTER_VALIDATE_URL) !== false;

        if ($valid && $requireHttps) {
            return strpos($url, 'https://') === 0;
        }

        return $valid;
    }

    public static function isValidIp($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public static function isValidTelegramToken($token) {
        $pattern = '/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/';
        return preg_match($pattern, $token) === 1;
    }

    public static function isValidTelegramId($id) {
        return is_numeric($id) && $id >= 100000 && $id <= 9999999999;
    }

    public static function isValidBaleToken($token) {
        $pattern = '/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/';
        return preg_match($pattern, $token) === 1;
    }

    public static function isValidUsername($username, $minLength = 3, $maxLength = 50) {
        $length = strlen($username);

        if ($length < $minLength || $length > $maxLength) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9_]+$/', $username) === 1;
    }

    public static function isAllowedFileType($file, array $allowedTypes) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        return in_array($mimeType, $allowedTypes);
    }

    public static function isAllowedFileSize($file, $maxSize) {
        return $file['size'] <= $maxSize;
    }

    public static function generateSafeFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = bin2hex(random_bytes(16)) . '.' . strtolower($extension);
        return $safeName;
    }

    public static function isAllowedExtension($filename, array $allowedExtensions) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions);
    }

    public static function checkRateLimit($key, $maxAttempts, $windowSeconds) {
        $cacheKey = 'rate_limit_' . md5($key);
        $cache = \App\Core\Cache::getInstance();

        $data = $cache->get($cacheKey, ['attempts' => 0, 'first_attempt' => time()]);

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

    public static function getClientIp() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];

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

    public static function isValidUserAgent($userAgent = null) {
        $userAgent = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');

        if (empty($userAgent) || strlen($userAgent) < 10) {
            return false;
        }

        $suspicious = ['sqlmap', 'nikto', 'nmap', 'masscan', 'dirbuster'];

        foreach ($suspicious as $tool) {
            if (stripos($userAgent, $tool) !== false) {
                return false;
            }
        }

        return true;
    }

    public static function isValidReferrer($allowedDomains = []) {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        if (empty($referrer)) {
            return false;
        }

        $referrerHost = parse_url($referrer, PHP_URL_HOST);

        if (empty($allowedDomains)) {
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            return $referrerHost === $currentHost;
        }

        return in_array($referrerHost, $allowedDomains);
    }

    public static function sanitizePath($path) {
        $path = str_replace(['../', '..\\', '..'], '', $path);
        $path = preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $path);
        return $path;
    }

    public static function sanitizeCommand($input) {
        $dangerous = [';', '|', '&', '$', '`', '(', ')', '{', '}', '<', '>', '\\', "\n", "\r"];
        $input = str_replace($dangerous, '', $input);
        return escapeshellarg($input);
    }

    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    public static function generateUuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    public static function forceHttps() {
        if (!self::isHttps()) {
            $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
            header("Location: {$url}", true, 301);
            exit;
        }
    }

    public static function setSecurityHeaders() {
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    public static function hidePhpVersion() {
        header_remove('X-Powered-By');
        ini_set('expose_php', 'Off');
    }

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
