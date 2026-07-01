<?php

declare(strict_types=1);

/**
 * ============================================
 * کلاس سیستم لاگ‌گیری (Logger)
 * ============================================
 * ثبت رویدادها با سطوح مختلف
 * چرخش خودکار فایل‌ها (Rotation)
 * فرمت زیبا و قابل خواندن
 * Singleton Pattern
 */

namespace App\Core;

use Exception;

class Logger {
    private static $instance = null;
    private $logPath;
    private $maxFileSize;
    private $maxFiles;
    private $dateFormat;
    
    // سطوح لاگ
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->logPath = dirname(__DIR__, 2) . '/storage/logs';
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->maxFiles = 10;
        $this->dateFormat = 'Y-m-d H:i:s';
        
        // ساخت پوشه لاگ در صورت عدم وجود
        if (!is_dir($this->logPath)) {
            if (!mkdir($this->logPath, 0775, true) && !is_dir($this->logPath)) {
                throw new Exception("خطا در ایجاد پوشه لاگ: {$this->logPath}");
            }
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
    // ثبت لاگ عمومی
    // ──────────────────────────────────────
    public function log($level, $message, array $context = [], $channel = 'app') {
        try {
            // پردازش message با context
            $message = $this->interpolate($message, $context);
            
            // ساخت خط لاگ
            $logLine = $this->formatLine($level, $message, $context);
            
            // نوشتن در فایل
            $this->writeToFile($channel, $logLine);
            
            // اگر سطح ERROR یا بالاتر بود، در کانال error هم ثبت کن
            if (in_array($level, [self::ERROR, self::CRITICAL])) {
                $this->writeToFile('error', $logLine);
            }
            
        } catch (Exception $e) {
            // جلوگیری از حلقه بی‌نهایت در صورت خطای لاگر
            error_log("Logger Error: " . $e->getMessage());
        }
    }
    
    // ──────────────────────────────────────
    // متدهای کمکی برای هر سطح
    // ──────────────────────────────────────
    public function debug($message, array $context = [], $channel = 'app') {
        $this->log(self::DEBUG, $message, $context, $channel);
    }
    
    public function info($message, array $context = [], $channel = 'app') {
        $this->log(self::INFO, $message, $context, $channel);
    }
    
    public function warning($message, array $context = [], $channel = 'app') {
        $this->log(self::WARNING, $message, $context, $channel);
    }
    
    public function error($message, array $context = [], $channel = 'app') {
        $this->log(self::ERROR, $message, $context, $channel);
    }
    
    public function critical($message, array $context = [], $channel = 'app') {
        $this->log(self::CRITICAL, $message, $context, $channel);
    }
    
    // ──────────────────────────────────────
    // لاگ مخصوص تلگرام
    // ──────────────────────────────────────
    public function telegram($message, array $context = []) {
        $this->log(self::INFO, $message, $context, 'telegram');
    }
    
    // ──────────────────────────────────────
    // لاگ مخصوص دیتابیس
    // ──────────────────────────────────────
    public function database($message, array $context = []) {
        $this->log(self::DEBUG, $message, $context, 'database');
    }
    
    // ──────────────────────────────────────
    // لاگ مخصوص امنیت
    // ──────────────────────────────────────
    public function security($message, array $context = []) {
        $this->log(self::WARNING, $message, $context, 'security');
    }
    
    // ──────────────────────────────────────
    // لاگ مخصوص دونیت
    // ──────────────────────────────────────
    public function donation($message, array $context = []) {
        $this->log(self::INFO, $message, $context, 'donation');
    }
    
    // ──────────────────────────────────────
    // جایگذاری متغیرها در message
    // ──────────────────────────────────────
    private function interpolate($message, array $context) {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
    
    // ──────────────────────────────────────
    // فرمت خط لاگ
    // ──────────────────────────────────────
    private function formatLine($level, $message, array $context) {
        $timestamp = date($this->dateFormat);
        $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        
        // رنگ‌های ANSI برای ترمینال
        $colors = [
            self::DEBUG => '🔍',
            self::INFO => 'ℹ️',
            self::WARNING => '⚠️',
            self::ERROR => '❌',
            self::CRITICAL => '🔥'
        ];
        
        $icon = $colors[$level] ?? '•';
        
        // ساخت خط
        $line = "[{$timestamp}] {$icon} [{$level}] [IP:{$clientIp}] [Mem:{$memory}MB] {$message}";
        
        // افزودن context اگر وجود داشت
        if (!empty($context)) {
            $contextStr = $this->formatContext($context);
            $line .= " | Context: {$contextStr}";
        }
        
        return $line . PHP_EOL;
    }
    
    // ──────────────────────────────────────
    // فرمت Context
    // ──────────────────────────────────────
    private function formatContext(array $context) {
        // حذف اطلاعات حساس
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card'];
        $safeContext = [];
        
        foreach ($context as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $safeContext[$key] = '***REDACTED***';
            } elseif (is_array($value) || is_object($value)) {
                $safeContext[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $safeContext[$key] = (string)$value;
            }
        }
        
        return json_encode($safeContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    
    // ──────────────────────────────────────
    // نوشتن در فایل
    // ──────────────────────────────────────
    private function writeToFile($channel, $logLine) {
        $filename = $this->getLogFilename($channel);
        $filepath = $this->logPath . '/' . $filename;
        
        // بررسی اندازه فایل و چرخش
        $this->rotateIfNeeded($filepath);
        
        // نوشتن با قفل
        file_put_contents($filepath, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    // ──────────────────────────────────────
    // دریافت نام فایل لاگ
    // ──────────────────────────────────────
    private function getLogFilename($channel) {
        $date = date('Y-m-d');
        return "{$channel}-{$date}.log";
    }
    
    // ──────────────────────────────────────
    // چرخش فایل لاگ (Rotation)
    // ──────────────────────────────────────
    private function rotateIfNeeded($filepath) {
        if (!file_exists($filepath)) {
            return;
        }
        
        $size = filesize($filepath);
        if ($size < $this->maxFileSize) {
            return;
        }
        
        // تغییر نام فایل فعلی
        $backupName = $filepath . '.' . date('His') . '.bak';
        rename($filepath, $backupName);
        
        // حذف فایل‌های قدیمی
        $this->cleanupOldFiles($filepath);
    }
    
    // ──────────────────────────────────────
    // پاکسازی فایل‌های قدیمی
    // ──────────────────────────────────────
    private function cleanupOldFiles($baseFile) {
        $dir = dirname($baseFile);
        $baseName = basename($baseFile);
        
        $files = glob($dir . '/' . $baseName . '*.bak');
        
        if (count($files) > $this->maxFiles) {
            // مرتب‌سازی بر اساس زمان
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // حذف فایل‌های قدیمی
            $toDelete = array_slice($files, 0, count($files) - $this->maxFiles);
            foreach ($toDelete as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    // ──────────────────────────────────────
    // خواندن لاگ‌های اخیر
    // ──────────────────────────────────────
    public function readRecent($channel = 'app', $lines = 100) {
        $filename = $this->getLogFilename($channel);
        $filepath = $this->logPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $file = new \SplFileObject($filepath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $result = [];
        
        $file->seek($startLine);
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $result[] = $line;
            }
            $file->next();
        }
        
        return $result;
    }
    
    // ──────────────────────────────────────
    // جستجو در لاگ‌ها
    // ──────────────────────────────────────
    public function search($keyword, $channel = 'app', $limit = 50) {
        $filename = $this->getLogFilename($channel);
        $filepath = $this->logPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $results = [];
        $handle = fopen($filepath, 'r');
        
        while (($line = fgets($handle)) !== false && count($results) < $limit) {
            if (stripos($line, $keyword) !== false) {
                $results[] = trim($line);
            }
        }
        
        fclose($handle);
        return $results;
    }
    
    // ──────────────────────────────────────
    // پاک کردن لاگ‌ها
    // ──────────────────────────────────────
    public function clear($channel = null) {
        if ($channel) {
            $filename = $this->getLogFilename($channel);
            $filepath = $this->logPath . '/' . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        } else {
            // پاک کردن همه
            $files = glob($this->logPath . '/*.log');
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    // ──────────────────────────────────────
    // دریافت لیست فایل‌های لاگ
    // ──────────────────────────────────────
    public function getLogFiles() {
        $files = glob($this->logPath . '/*.log');
        $result = [];
        
        foreach ($files as $file) {
            $result[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'size_human' => $this->formatSize(filesize($file)),
                'modified' => filemtime($file),
                'modified_human' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // مرتب‌سازی بر اساس زمان (جدیدترین اول)
        usort($result, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        return $result;
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
    // تنظیم مسیر لاگ
    // ──────────────────────────────────────
    public function setLogPath($path) {
        $this->logPath = rtrim($path, '/');
        if (!is_dir($this->logPath)) {
            if (!mkdir($this->logPath, 0775, true) && !is_dir($this->logPath)) {
                $this->logPath = dirname(__DIR__, 2) . '/storage/logs';
            }
        }
    }
    
    // ──────────────────────────────────────
    // تنظیم حداکثر اندازه فایل
    // ──────────────────────────────────────
    public function setMaxFileSize($bytes) {
        $this->maxFileSize = $bytes;
    }
    
    // ──────────────────────────────────────
    // تنظیم تعداد فایل‌های نگهداری
    // ──────────────────────────────────────
    public function setMaxFiles($count) {
        $this->maxFiles = $count;
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