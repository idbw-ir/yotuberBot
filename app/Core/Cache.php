<?php
/**
 * ============================================
 * کلاس سیستم کش (Cache)
 * ============================================
 * کش فایل‌محور برای بهبود Performance
 * پشتیبانی از TTL (Time To Live)
 * پاکسازی خودکار کش منقضی شده
 * Singleton Pattern
 */

namespace App\Core;

use Exception;

class Cache {
    private static $instance = null;
    private $cachePath;
    private $defaultTtl;
    private $prefix;
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->cachePath = dirname(__DIR__, 2) . '/storage/cache';
        $this->defaultTtl = 3600; // 1 ساعت
        $this->prefix = 'youtuber_';
        
        // ساخت پوشه کش در صورت عدم وجود
        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0775, true);
        }
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
    
    // ──────────────────────────────────────
    // ذخیره در کش
    // ──────────────────────────────────────
    public function set($key, $value, $ttl = null) {
        try {
            $ttl = $ttl ?? $this->defaultTtl;
            $filename = $this->getFilename($key);
            
            $data = [
                'key' => $key,
                'value' => $value,
                'created_at' => time(),
                'expires_at' => time() + $ttl,
                'ttl' => $ttl
            ];
            
            $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            if (file_put_contents($filename, $content, LOCK_EX) === false) {
                throw new Exception("خطا در نوشتن فایل کش: {$filename}");
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logError('Cache Set Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // ──────────────────────────────────────
    // دریافت از کش
    // ──────────────────────────────────────
    public function get($key, $default = null) {
        try {
            $filename = $this->getFilename($key);
            
            if (!file_exists($filename)) {
                return $default;
            }
            
            $content = file_get_contents($filename);
            $data = json_decode($content, true);
            
            if ($data === null) {
                return $default;
            }
            
            // بررسی انقضا
            if (isset($data['expires_at']) && $data['expires_at'] < time()) {
                // کش منقضی شده - حذف کن
                @unlink($filename);
                return $default;
            }
            
            return $data['value'] ?? $default;
            
        } catch (Exception $e) {
            $this->logError('Cache Get Error: ' . $e->getMessage());
            return $default;
        }
    }
    
    // ──────────────────────────────────────
    // دریافت با Callback (Remember)
    // ──────────────────────────────────────
    public function remember($key, $ttl, $callback) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    // ──────────────────────────────────────
    // بررسی وجود
    // ──────────────────────────────────────
    public function has($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        
        if ($data === null) {
            return false;
        }
        
        // بررسی انقضا
        if (isset($data['expires_at']) && $data['expires_at'] < time()) {
            @unlink($filename);
            return false;
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // حذف از کش
    // ──────────────────────────────────────
    public function delete($key) {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return @unlink($filename);
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // پاک کردن همه کش‌ها
    // ──────────────────────────────────────
    public function clear() {
        $files = glob($this->cachePath . '/' . $this->prefix . '*.cache');
        
        foreach ($files as $file) {
            @unlink($file);
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // پاکسازی کش‌های منقضی شده
    // ──────────────────────────────────────
    public function gc() {
        $files = glob($this->cachePath . '/' . $this->prefix . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            $content = @file_get_contents($file);
            $data = @json_decode($content, true);
            
            if ($data === null) {
                @unlink($file);
                $deleted++;
                continue;
            }
            
            if (isset($data['expires_at']) && $data['expires_at'] < time()) {
                @unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    // ──────────────────────────────────────
    // افزایش مقدار عددی
    // ──────────────────────────────────────
    public function increment($key, $value = 1, $ttl = null) {
        $current = $this->get($key, 0);
        $newValue = $current + $value;
        $this->set($key, $newValue, $ttl);
        return $newValue;
    }
    
    // ──────────────────────────────────────
    // کاهش مقدار عددی
    // ──────────────────────────────────────
    public function decrement($key, $value = 1, $ttl = null) {
        $current = $this->get($key, 0);
        $newValue = max(0, $current - $value);
        $this->set($key, $newValue, $ttl);
        return $newValue;
    }
    
    // ──────────────────────────────────────
    // دریافت چند مقدار
    // ──────────────────────────────────────
    public function getMany(array $keys) {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        
        return $result;
    }
    
    // ──────────────────────────────────────
    // ذخیره چند مقدار
    // ──────────────────────────────────────
    public function setMany(array $data, $ttl = null) {
        foreach ($data as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // دریافت اطلاعات کش
    // ──────────────────────────────────────
    public function info() {
        $files = glob($this->cachePath . '/' . $this->prefix . '*.cache');
        
        $totalSize = 0;
        $validCount = 0;
        $expiredCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $content = @file_get_contents($file);
            $data = @json_decode($content, true);
            
            if ($data && isset($data['expires_at'])) {
                if ($data['expires_at'] < time()) {
                    $expiredCount++;
                } else {
                    $validCount++;
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_count' => $validCount,
            'expired_count' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_human' => $this->formatSize($totalSize),
            'cache_path' => $this->cachePath
        ];
    }
    
    // ──────────────────────────────────────
    // دریافت نام فایل
    // ──────────────────────────────────────
    private function getFilename($key) {
        $hash = md5($this->prefix . $key);
        return $this->cachePath . '/' . $this->prefix . $hash . '.cache';
    }
    
    // ──────────────────────────────────────
    // فرمت اندازه فایل
    // ──────────────────────────────────────
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    // ──────────────────────────────────────
    // تنظیم مسیر کش
    // ──────────────────────────────────────
    public function setCachePath($path) {
        $this->cachePath = rtrim($path, '/');
        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0775, true);
        }
    }
    
    // ──────────────────────────────────────
    // تنظیم TTL پیش‌فرض
    // ──────────────────────────────────────
    public function setDefaultTtl($seconds) {
        $this->defaultTtl = $seconds;
    }
    
    // ──────────────────────────────────────
    // تنظیم Prefix
    // ──────────────────────────────────────
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }
    
    // ──────────────────────────────────────
    // لاگ خطا
    // ──────────────────────────────────────
    private function logError($message) {
        $logPath = dirname(__DIR__, 2) . '/storage/logs/cache.log';
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logPath, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
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

// ──────────────────────────────────────
// تابع کمکی global
// ──────────────────────────────────────
if (!function_exists('cache')) {
    function cache($key = null, $default = null) {
        $cache = Cache::getInstance();
        
        if ($key === null) {
            return $cache;
        }
        
        return $cache->get($key, $default);
    }
}