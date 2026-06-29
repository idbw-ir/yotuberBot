<?php
/**
 * ============================================
 * کلاس مدیریت تنظیمات (Config)
 * ============================================
 * بارگذاری و مدیریت تنظیمات
 * پشتیبانی از Dot Notation
 * Cache برای Performance
 * Singleton Pattern
 */

namespace App\Core;

use Exception;

class Config {
    private static $instance = null;
    private $config = [];
    private $loaded = false;
    private $configPath;
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->configPath = dirname(__DIR__, 2) . '/config';
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
    // بارگذاری تنظیمات
    // ──────────────────────────────────────
    public function load($forceReload = false) {
        if ($this->loaded && !$forceReload) {
            return;
        }
        
        $configFile = $this->configPath . '/config.php';
        
        if (!file_exists($configFile)) {
            throw new Exception('فایل config.php یافت نشد. لطفاً نصب‌کننده را اجرا کنید.');
        }
        
        $config = require $configFile;
        
        if (!is_array($config)) {
            throw new Exception('فایل config.php باید یک آرایه برگرداند.');
        }
        
        $this->config = $config;
        $this->loaded = true;
    }
    
    // ──────────────────────────────────────
    // دریافت مقدار با Dot Notation
    // ──────────────────────────────────────
    public function get($key, $default = null) {
        $this->ensureLoaded();
        
        // اگر key شامل نقطه بود، به صورت تو در تو جستجو کن
        if (strpos($key, '.') !== false) {
            return $this->getNestedValue($key, $default);
        }
        
        return $this->config[$key] ?? $default;
    }
    
    // ──────────────────────────────────────
    // دریافت مقدار تو در تو
    // ──────────────────────────────────────
    private function getNestedValue($key, $default) {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    // ──────────────────────────────────────
    // تنظیم مقدار
    // ──────────────────────────────────────
    public function set($key, $value) {
        $this->ensureLoaded();
        
        // اگر key شامل نقطه بود، به صورت تو در تو تنظیم کن
        if (strpos($key, '.') !== false) {
            $this->setNestedValue($key, $value);
            return;
        }
        
        $this->config[$key] = $value;
    }
    
    // ──────────────────────────────────────
    // تنظیم مقدار تو در تو
    // ──────────────────────────────────────
    private function setNestedValue($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }
    
    // ──────────────────────────────────────
    // بررسی وجود
    // ──────────────────────────────────────
    public function has($key) {
        $this->ensureLoaded();
        
        if (strpos($key, '.') !== false) {
            return $this->hasNestedValue($key);
        }
        
        return array_key_exists($key, $this->config);
    }
    
    // ──────────────────────────────────────
    // بررسی وجود مقدار تو در تو
    // ──────────────────────────────────────
    private function hasNestedValue($key) {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return false;
            }
            $value = $value[$k];
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // حذف مقدار
    // ──────────────────────────────────────
    public function remove($key) {
        $this->ensureLoaded();
        
        if (strpos($key, '.') !== false) {
            $this->removeNestedValue($key);
            return;
        }
        
        unset($this->config[$key]);
    }
    
    // ──────────────────────────────────────
    // حذف مقدار تو در تو
    // ──────────────────────────────────────
    private function removeNestedValue($key) {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                unset($config[$k]);
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    return;
                }
                $config = &$config[$k];
            }
        }
    }
    
    // ──────────────────────────────────────
    // دریافت همه تنظیمات
    // ──────────────────────────────────────
    public function all() {
        $this->ensureLoaded();
        return $this->config;
    }
    
    // ──────────────────────────────────────
    // دریافت یک بخش خاص
    // ──────────────────────────────────────
    public function getSection($section) {
        return $this->get($section, []);
    }
    
    // ──────────────────────────────────────
    // ادغام تنظیمات
    // ──────────────────────────────────────
    public function merge(array $newConfig) {
        $this->ensureLoaded();
        $this->config = array_replace_recursive($this->config, $newConfig);
    }
    
    // ──────────────────────────────────────
    // بارگذاری فایل تنظیمات اضافی
    // ──────────────────────────────────────
    public function loadFile($filename) {
        $filepath = $this->configPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception("فایل {$filename} یافت نشد");
        }
        
        $config = require $filepath;
        
        if (!is_array($config)) {
            throw new Exception("فایل {$filename} باید یک آرایه برگرداند");
        }
        
        $this->merge($config);
    }
    
    // ──────────────────────────────────────
    // ذخیره تنظیمات در فایل
    // ──────────────────────────────────────
    public function save($filename = 'config.php') {
        $filepath = $this->configPath . '/' . $filename;
        
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * تنظیمات پروژه\n";
        $content .= " * تولید شده در: " . date('Y-m-d H:i:s') . "\n";
        $content .= " */\n\n";
        $content .= "return " . $this->arrayToString($this->config, 0) . ";\n";
        
        if (file_put_contents($filepath, $content) === false) {
            throw new Exception("خطا در نوشتن فایل {$filename}");
        }
        
        return true;
    }
    
    // ──────────────────────────────────────
    // تبدیل آرایه به string
    // ──────────────────────────────────────
    private function arrayToString(array $array, $indent) {
        $result = "[\n";
        $indentStr = str_repeat('    ', $indent + 1);
        
        foreach ($array as $key => $value) {
            $result .= $indentStr . "'" . addslashes($key) . "' => ";
            
            if (is_array($value)) {
                $result .= $this->arrayToString($value, $indent + 1);
            } elseif (is_string($value)) {
                $result .= "'" . addslashes($value) . "'";
            } elseif (is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $result .= 'null';
            } else {
                $result .= $value;
            }
            
            $result .= ",\n";
        }
        
        $result .= str_repeat('    ', $indent) . "]";
        
        return $result;
    }
    
    // ──────────────────────────────────────
    // متدهای کمکی برای بخش‌های خاص
    // ──────────────────────────────────────
    
    // تنظیمات دیتابیس
    public function database($key = null, $default = null) {
        if ($key === null) {
            return $this->get('database', []);
        }
        return $this->get("database.{$key}", $default);
    }
    
    // تنظیمات تلگرام
    public function telegram($key = null, $default = null) {
        if ($key === null) {
            return $this->get('telegram', []);
        }
        return $this->get("telegram.{$key}", $default);
    }
    
    // تنظیمات برنامه
    public function app($key = null, $default = null) {
        if ($key === null) {
            return $this->get('app', []);
        }
        return $this->get("app.{$key}", $default);
    }
    
    // تنظیمات هوش مصنوعی
    public function ai($key = null, $default = null) {
        if ($key === null) {
            return $this->get('ai', []);
        }
        return $this->get("ai.{$key}", $default);
    }
    
    // تنظیمات امنیتی
    public function security($key = null, $default = null) {
        if ($key === null) {
            return $this->get('security', []);
        }
        return $this->get("security.{$key}", $default);
    }
    
    // ──────────────────────────────────────
    // اطمینان از بارگذاری
    // ──────────────────────────────────────
    private function ensureLoaded() {
        if (!$this->loaded) {
            $this->load();
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

// ──────────────────────────────────────
// تابع کمکی global
// ──────────────────────────────────────
if (!function_exists('config')) {
    function config($key = null, $default = null) {
        $config = Config::getInstance();
        
        if ($key === null) {
            return $config->all();
        }
        
        return $config->get($key, $default);
    }
}