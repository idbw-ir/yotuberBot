<?php
/**
 * ============================================
 * Custom Autoloader - بدون Composer
 * ============================================
 */

class Autoloader
{
    private static $instance = null;
    private $namespace = 'App\\';
    private $baseDir;
    
    private function __construct()
    {
        $this->baseDir = dirname(__DIR__, 2);
        spl_autoload_register([$this, 'loadClass']);
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadClass($class)
    {
        // بررسی namespace
        $prefix = strlen($this->namespace);
        if (strncmp($this->namespace, $class, $prefix) !== 0) {
            return;
        }
        
        // تبدیل namespace به مسیر فایل
        $relativeClass = substr($class, $prefix);
        $file = $this->baseDir . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
    
    // بارگذاری helpers.php
    public function loadHelpers()
    {
        $helpersFile = $this->baseDir . '/app/helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }
}

// راه‌اندازی Autoloader
Autoloader::getInstance()->loadHelpers();