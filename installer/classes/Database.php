<?php
/**
 * ============================================
 * کلاس مدیریت دیتابیس (مخصوص نصب‌کننده)
 * ============================================
 * پشتیبانی از MySQL (PDO) و Bunny Database (Turso/libSQL)
 */

class InstallerDatabase {
    private $pdo = null;
    private $bunny = null;
    private $driver = 'mysql';
    private $host;
    private $name;
    private $user;
    private $pass;
    private $bunnyUrl;
    private $bunnyToken;
    
    public function __construct($driver, $params) {
        $this->driver = $driver;
        if ($driver === 'bunny') {
            $this->bunnyUrl = $params['bunny_url'] ?? '';
            $this->bunnyToken = $params['bunny_token'] ?? '';
            $this->name = basename(parse_url($this->bunnyUrl, PHP_URL_PATH)) ?: 'app';
        } else {
            $this->host = $params['host'] ?? '';
            $this->name = $params['name'] ?? '';
            $this->user = $params['user'] ?? '';
            $this->pass = $params['pass'] ?? '';
        }
    }
    
    // ──────────────────────────────────────
    // دریافت نام دیتابیس
    // ──────────────────────────────────────
    public function getDbName() {
        return $this->name;
    }
    
    // ──────────────────────────────────────
    // اتصال به دیتابیس
    // ──────────────────────────────────────
    public function connect() {
        if ($this->driver === 'bunny') {
            return $this->connectBunny();
        }
        return $this->connectMysql();
    }
    
    private function connectMysql() {
        try {
            $dsn = "mysql:host={$this->host};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
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
    
    private function connectBunny() {
        try {
            $this->bunny = new App\Core\DatabaseBunny($this->bunnyUrl, $this->bunnyToken);
            $this->bunny->execute("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // ──────────────────────────────────────
    // ایمپورت اسکریپت دیتابیس
    // ──────────────────────────────────────
    public function importSchema($schemaPath) {
        if (!file_exists($schemaPath)) {
            return "فایل schema یافت نشد: {$schemaPath}";
        }
        
        try {
            $sql = file_get_contents($schemaPath);
            
            // حذف کامنت‌ها
            $sql = preg_replace('/--.*$/m', '', $sql);
            
            if ($this->driver === 'bunny') {
                // Bunny: هر دستور را جداگانه ارسال کن
                $statements = explode(';', $sql);
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt)) {
                        $this->bunny->execute($stmt);
                    }
                }
            } else {
                // MySQL: استفاده از splitter هوشمند
                $statements = $this->splitSqlStatements($sql);
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if (empty($stmt)) continue;
                    $this->pdo->exec($stmt);
                }
            }
            
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    private function splitSqlStatements($sql) {
        $statements = [];
        $current = '';
        $depth = 0;
        $len = strlen($sql);
        $i = 0;
        
        while ($i < $len) {
            $char = $sql[$i];
            
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
                if ($i < $len) $current .= $sql[$i];
                $i++;
                continue;
            }
            
            if (strtoupper(substr($sql, $i, 5)) === 'BEGIN' && !preg_match('/[a-z_]/i', $sql[$i + 5] ?? '')) {
                $depth++;
            }
            if (strtoupper(substr($sql, $i, 3)) === 'END' && !preg_match('/[a-z_]/i', $sql[$i + 3] ?? '')) {
                $depth--;
            }
            
            if ($char === ';' && $depth <= 0) {
                $st = trim($current);
                if (!empty($st)) $statements[] = $st;
                $current = '';
            } else {
                $current .= $char;
            }
            $i++;
        }
        
        $st = trim($current);
        if (!empty($st)) $statements[] = $st;
        
        return $statements;
    }
    
    // ──────────────────────────────────────
    // اجرای کوئری
    // ──────────────────────────────────────
    public function query($sql, $params = []) {
        if ($this->driver === 'bunny') {
            return $this->bunny->query($sql, $params);
        }
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function fetch($sql, $params = []) {
        if ($this->driver === 'bunny') {
            return $this->bunny->fetch($sql, $params);
        }
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }
    
    public function fetchAll($sql, $params = []) {
        if ($this->driver === 'bunny') {
            return $this->bunny->fetchAll($sql, $params);
        }
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    public function insert($table, $data) {
        if ($this->driver === 'bunny') {
            return $this->bunny->insert($table, $data);
        }
        $cols = implode(', ', array_keys($data));
        $phs = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$phs})";
        return $this->query($sql, $data);
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        if ($this->driver === 'bunny') {
            return $this->bunny->update($table, $data, $where, $whereParams);
        }
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params);
    }
    
    public function delete($table, $where, $params = []) {
        if ($this->driver === 'bunny') {
            return $this->bunny->delete($table, $where, $params);
        }
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    public function count($table, $where = '1', $params = []) {
        if ($this->driver === 'bunny') {
            return $this->bunny->count($table, $where, $params);
        }
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return $result ? $result['count'] : 0;
    }
    
    public function tableExists($table) {
        if ($this->driver === 'bunny') {
            return $this->bunny->tableExists($table);
        }
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->fetch($sql, [$table]);
        return $result !== false;
    }
    
    public function getDriver() {
        return $this->driver;
    }
    
    public function getPdo() {
        return $this->pdo;
    }
    
    public function getBunny() {
        return $this->bunny;
    }
}