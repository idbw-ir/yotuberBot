<?php
/**
 * ============================================
 * کلاس مدیریت دیتابیس (مخصوص نصب‌کننده)
 * ============================================
 */

class Database {
    private $pdo;
    private $host;
    private $name;
    private $user;
    private $pass;
    
    public function __construct($host, $name, $user, $pass) {
        $this->host = $host;
        $this->name = $name;
        $this->user = $user;
        $this->pass = $pass;
    }
    
    // ──────────────────────────────────────
    // اتصال به دیتابیس
    // ──────────────────────────────────────
    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // ──────────────────────────────────────
    // ساخت دیتابیس
    // ──────────────────────────────────────
    public function createDatabase() {
        try {
            $pdo = new PDO("mysql:host={$this->host}", $this->user, $this->pass);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return true;
        } catch (PDOException $e) {
            return false;
        }
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
    // درج داده
    // ──────────────────────────────────────
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->query($sql, $data);
    }
    
    // ──────────────────────────────────────
    // بروزرسانی داده
    // ──────────────────────────────────────
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params);
    }
    
    // ──────────────────────────────────────
    // حذف داده
    // ──────────────────────────────────────
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    // ──────────────────────────────────────
    // شمارش ردیف‌ها
    // ──────────────────────────────────────
    public function count($table, $where = '1', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return $result ? $result['count'] : 0;
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
    // دریافت PDO
    // ──────────────────────────────────────
    public function getPdo() {
        return $this->pdo;
    }
}