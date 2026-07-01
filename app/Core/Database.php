<?php

declare(strict_types=1);

/**
 * ============================================
 * کلاس مدیریت دیتابیس
 * ============================================
 * اتصال به MySQL با PDO
 * پشتیبانی از Prepared Statements
 * Singleton Pattern
 */

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;
    private $pdo;
    private $config;
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->loadConfig();
        $this->connect();
    }
    
    // ──────────────────────────────────────
    // بارگذاری تنظیمات
    // ──────────────────────────────────────
    private function loadConfig() {
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        
        if (!file_exists($configPath)) {
            throw new Exception('فایل config.php یافت نشد. لطفاً نصب‌کننده را اجرا کنید.');
        }
        
        $config = require $configPath;
        $this->config = $config['database'] ?? [];
        
        if (empty($this->config)) {
            throw new Exception('تنظیمات دیتابیس در config.php وجود ندارد.');
        }
    }
    
    // ──────────────────────────────────────
    // اتصال به دیتابیس
    // ──────────────────────────────────────
    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['name'],
                $this->config['charset'] ?? 'utf8mb4'
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . ($this->config['charset'] ?? 'utf8mb4')
            ];
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['pass'],
                $options
            );
            
        } catch (PDOException $e) {
            // لاگ خطا
            $this->logError('Database Connection Error: ' . $e->getMessage());
            throw new Exception('خطا در اتصال به دیتابیس. لطفاً تنظیمات را بررسی کنید.');
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
    // دریافت PDO
    // ──────────────────────────────────────
    public function getPdo() {
        return $this->pdo;
    }
    
    // ──────────────────────────────────────
    // اجرای کوئری
    // ──────────────────────────────────────
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return false;
        }
    }
    
    // ──────────────────────────────────────
    // دریافت یک ردیف
    // ──────────────────────────────────────
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }
    
    // ──────────────────────────────────────
    // دریافت همه ردیف‌ها
    // ──────────────────────────────────────
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    // ──────────────────────────────────────
    // دریافت یک مقدار
    // ──────────────────────────────────────
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchColumn() : false;
    }
    
    // ──────────────────────────────────────
    // درج داده
    // ──────────────────────────────────────
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->query($sql, $data);
        
        if ($stmt) {
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }
    
    // ──────────────────────────────────────
    // بروزرسانی داده
    // ──────────────────────────────────────
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :set_{$key}";
        }
        $setStr = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        
        // تغییر کلیدها برای جلوگیری از تداخل
        $params = [];
        foreach ($data as $key => $value) {
            $params["set_{$key}"] = $value;
        }
        $params = array_merge($params, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }
    
    // ──────────────────────────────────────
    // حذف داده
    // ──────────────────────────────────────
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }
    
    // ──────────────────────────────────────
    // شمارش ردیف‌ها
    // ──────────────────────────────────────
    public function count($table, $where = '1', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    // ──────────────────────────────────────
    // بررسی وجود
    // ──────────────────────────────────────
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    // ──────────────────────────────────────
    // شروع Transaction
    // ──────────────────────────────────────
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    // ──────────────────────────────────────
    // Commit Transaction
    // ──────────────────────────────────────
    public function commit() {
        return $this->pdo->commit();
    }
    
    // ──────────────────────────────────────
    // Rollback Transaction
    // ──────────────────────────────────────
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    // ──────────────────────────────────────
    // اجرای چند کوئری در Transaction
    // ──────────────────────────────────────
    public function transaction($callback) {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            $this->logError('Transaction Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // ──────────────────────────────────────
    // بررسی وجود جدول
    // ──────────────────────────────────────
    public function tableExists($table) {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->fetch($sql, [$table]);
        return $result !== false;
    }
    
    // ──────────────────────────────────────
    // دریافت ساختار جدول
    // ──────────────────────────────────────
    public function describeTable($table) {
        $sql = "DESCRIBE {$table}";
        return $this->fetchAll($sql);
    }
    
    // ──────────────────────────────────────
    // لاگ خطا
    // ──────────────────────────────────────
    private function logError($message) {
        $logPath = dirname(__DIR__, 2) . '/storage/logs/database.log';
        $logDir = dirname($logPath);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
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