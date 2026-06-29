<?php
/**
 * ============================================
 * کلاس مدیریت کاربران (Users)
 * ============================================
 * لیست، جستجو، فیلتر کاربران
 * مدیریت VIP و بلاک
 * آمار کاربران
 * عملیات دسته‌جمعی
 * Pagination
 * Export (CSV/JSON)
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Helpers\Security;

class Users {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $cacheTtl = 300; // 5 دقیقه
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->logger = Logger::getInstance();
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
    
    // ══════════════════════════════════════
    // دریافت لیست کاربران
    // ══════════════════════════════════════
    
    /**
     * دریافت لیست کاربران با فیلتر و جستجو
     */
    public function getAll(array $filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];
        
        // فیلتر بر اساس وضعیت
        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'vip':
                    $where[] = 'is_vip = 1';
                    break;
                case 'blocked':
                    $where[] = 'blocked = 1';
                    break;
                case 'active':
                    $where[] = 'blocked = 0 AND last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'inactive':
                    $where[] = 'last_seen < DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }
        }
        
        // فیلتر بر اساس تاریخ عضویت
        if (!empty($filters['date_from'])) {
            $where[] = 'joined_at >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'joined_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // فیلتر بر اساس حداقل دونیت
        if (!empty($filters['min_donation'])) {
            $where[] = 'id IN (SELECT user_id FROM donations WHERE status = "success" GROUP BY user_id HAVING SUM(amount) >= ?)';
            $params[] = (int)$filters['min_donation'];
        }
        
        // جستجو
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = '(first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR CAST(id AS CHAR) LIKE ?)';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        
        // مرتب‌سازی
        $sortField = $this->validateSortField($filters['sort'] ?? 'joined_at');
        $sortOrder = strtoupper($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        
        // ساخت کوئری
        $whereStr = implode(' AND ', $where);
        
        // شمارش کل
        $countSql = "SELECT COUNT(*) FROM users WHERE {$whereStr}";
        $total = (int)$this->db->fetchColumn($countSql, $params);
        
        // Pagination
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // دریافت کاربران
        $sql = "SELECT 
                    u.*,
                    COALESCE(d.total_amount, 0) as total_donations,
                    COALESCE(d.donation_count, 0) as donation_count,
                    COALESCE(m.message_count, 0) as message_count
                FROM users u
                LEFT JOIN (
                    SELECT user_id, SUM(amount) as total_amount, COUNT(*) as donation_count
                    FROM donations WHERE status = 'success'
                    GROUP BY user_id
                ) d ON u.id = d.user_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as message_count
                    FROM messages WHERE direction = 'in'
                    GROUP BY user_id
                ) m ON u.id = m.user_id
                WHERE {$whereStr}
                ORDER BY {$sortField} {$sortOrder}
                LIMIT {$perPage} OFFSET {$offset}";
        
        $users = $this->db->fetchAll($sql, $params);
        
        // فرمت‌بندی
        foreach ($users as &$user) {
            $user = $this->formatUser($user);
        }
        
        return [
            'data' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
    
    /**
     * دریافت یک کاربر با آیدی
     */
    public function getById($userId) {
        $cacheKey = "user_{$userId}_full";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($userId) {
            $sql = "SELECT 
                        u.*,
                        COALESCE(d.total_amount, 0) as total_donations,
                        COALESCE(d.donation_count, 0) as donation_count,
                        COALESCE(m.message_count, 0) as message_count,
                        d.last_donation
                    FROM users u
                    LEFT JOIN (
                        SELECT user_id, SUM(amount) as total_amount, 
                               COUNT(*) as donation_count,
                               MAX(created_at) as last_donation
                        FROM donations WHERE status = 'success'
                        GROUP BY user_id
                    ) d ON u.id = d.user_id
                    LEFT JOIN (
                        SELECT user_id, COUNT(*) as message_count
                        FROM messages WHERE direction = 'in'
                        GROUP BY user_id
                    ) m ON u.id = m.user_id
                    WHERE u.id = ?";
            
            $user = $this->db->fetch($sql, [$userId]);
            
            if ($user) {
                return $this->formatUser($user);
            }
            
            return null;
        });
    }
    
    /**
     * دریافت کاربر با یوزرنیم
     */
    public function getByUsername($username) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
        
        return $user ? $this->formatUser($user) : null;
    }
    
    // ══════════════════════════════════════
    // عملیات CRUD
    // ══════════════════════════════════════
    
    /**
     * بروزرسانی کاربر
     */
    public function update($userId, array $data) {
        $allowedFields = ['first_name', 'last_name', 'username', 'phone', 'is_vip', 'blocked', 'notes'];
        $updateData = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'هیچ فیلد مجازی برای بروزرسانی وجود ندارد'];
        }
        
        $result = $this->db->update('users', $updateData, 'id = ?', [$userId]);
        
        if ($result !== false) {
            // پاک کردن کش
            $this->cache->delete("user_{$userId}_full");
            $this->cache->delete("user_{$userId}_stats");
            
            $this->logger->info('User updated', [
                'user_id' => $userId,
                'fields' => array_keys($updateData)
            ]);
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'خطا در بروزرسانی کاربر'];
    }
    
    /**
     * حذف کاربر
     */
    public function delete($userId) {
        // بررسی وجود
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        // حذف پیام‌ها
        $this->db->delete('messages', 'user_id = ?', [$userId]);
        
        // حذف دونیت‌ها
        $this->db->delete('donations', 'user_id = ?', [$userId]);
        
        // حذف کاربر
        $this->db->delete('users', 'id = ?', [$userId]);
        
        // پاک کردن کش
        $this->cache->delete("user_{$userId}_full");
        $this->cache->delete("user_{$userId}_stats");
        
        $this->logger->warning('User deleted', [
            'user_id' => $userId,
            'username' => $user['username']
        ]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // مدیریت VIP
    // ══════════════════════════════════════
    
    /**
     * تنظیم وضعیت VIP
     */
    public function setVip($userId, $isVip = true) {
        $result = $this->db->update(
            'users',
            ['is_vip' => $isVip ? 1 : 0],
            'id = ?',
            [$userId]
        );
        
        if ($result !== false) {
            $this->cache->delete("user_{$userId}_full");
            
            $this->logger->info('User VIP status changed', [
                'user_id' => $userId,
                'is_vip' => $isVip
            ]);
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'خطا در تغییر وضعیت VIP'];
    }
    
    /**
     * دریافت لیست کاربران VIP
     */
    public function getVipUsers($page = 1, $perPage = 20) {
        return $this->getAll(['status' => 'vip'], $page, $perPage);
    }
    
    // ══════════════════════════════════════
    // مدیریت بلاک
    // ══════════════════════════════════════
    
    /**
     * بلاک کاربر
     */
    public function block($userId) {
        $result = $this->db->update(
            'users',
            ['blocked' => 1],
            'id = ?',
            [$userId]
        );
        
        if ($result !== false) {
            $this->cache->delete("user_{$userId}_full");
            
            $this->logger->warning('User blocked', ['user_id' => $userId]);
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'خطا در بلاک کاربر'];
    }
    
    /**
     * آن‌بلاک کاربر
     */
    public function unblock($userId) {
        $result = $this->db->update(
            'users',
            ['blocked' => 0],
            'id = ?',
            [$userId]
        );
        
        if ($result !== false) {
            $this->cache->delete("user_{$userId}_full");
            
            $this->logger->info('User unblocked', ['user_id' => $userId]);
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'خطا در آن‌بلاک کاربر'];
    }
    
    /**
     * تغییر وضعیت بلاک (Toggle)
     */
    public function toggleBlock($userId) {
        $user = $this->db->fetch("SELECT blocked FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        return $user['blocked'] ? $this->unblock($userId) : $this->block($userId);
    }
    
    // ══════════════════════════════════════
    // جستجو
    // ══════════════════════════════════════
    
    /**
     * جستجوی سریع
     */
    public function search($query, $limit = 20) {
        $search = '%' . $query . '%';
        
        $sql = "SELECT * FROM users 
                WHERE first_name LIKE ? 
                OR last_name LIKE ? 
                OR username LIKE ? 
                OR CAST(id AS CHAR) LIKE ?
                ORDER BY last_seen DESC
                LIMIT ?";
        
        $users = $this->db->fetchAll($sql, [$search, $search, $search, $search, $limit]);
        
        foreach ($users as &$user) {
            $user = $this->formatUser($user);
        }
        
        return $users;
    }
    
    // ══════════════════════════════════════
    // آمار کاربر
    // ══════════════════════════════════════
    
    /**
     * آمار کامل یک کاربر
     */
    public function getStatistics($userId) {
        $cacheKey = "user_{$userId}_stats";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($userId) {
            // آمار پیام‌ها
            $messageStats = $this->db->fetch(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN direction = 'in' THEN 1 ELSE 0 END) as incoming,
                    SUM(CASE WHEN direction = 'out' THEN 1 ELSE 0 END) as outgoing,
                    MAX(created_at) as last_message
                FROM messages WHERE user_id = ?",
                [$userId]
            );
            
            // آمار دونیت‌ها
            $donationStats = $this->db->fetch(
                "SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COALESCE(AVG(amount), 0) as average_amount,
                    COALESCE(MAX(amount), 0) as max_amount,
                    MAX(created_at) as last_donation
                FROM donations 
                WHERE user_id = ? AND status = 'success'",
                [$userId]
            );
            
            // فعالیت هفتگی
            $weeklyActivity = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM messages 
                WHERE user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                [$userId]
            );
            
            // دونیت‌های اخیر
            $recentDonations = $this->db->fetchAll(
                "SELECT * FROM donations 
                WHERE user_id = ? AND status = 'success'
                ORDER BY created_at DESC
                LIMIT 5",
                [$userId]
            );
            
            // پیام‌های اخیر
            $recentMessages = $this->db->fetchAll(
                "SELECT * FROM messages 
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 10",
                [$userId]
            );
            
            return [
                'messages' => $messageStats,
                'donations' => $donationStats,
                'weekly_activity' => $weeklyActivity,
                'recent_donations' => $recentDonations,
                'recent_messages' => $recentMessages
            ];
        });
    }
    
    // ══════════════════════════════════════
    // عملیات دسته‌جمعی
    // ══════════════════════════════════════
    
    /**
     * عملیات دسته‌جمعی
     */
    public function bulkAction(array $userIds, $action, $params = []) {
        if (empty($userIds)) {
            return ['success' => false, 'error' => 'هیچ کاربری انتخاب نشده'];
        }
        
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $affected = 0;
        
        switch ($action) {
            case 'block':
                $sql = "UPDATE users SET blocked = 1 WHERE id IN ({$placeholders})";
                $affected = $this->db->query($sql, $userIds)->rowCount();
                break;
                
            case 'unblock':
                $sql = "UPDATE users SET blocked = 0 WHERE id IN ({$placeholders})";
                $affected = $this->db->query($sql, $userIds)->rowCount();
                break;
                
            case 'make_vip':
                $sql = "UPDATE users SET is_vip = 1 WHERE id IN ({$placeholders})";
                $affected = $this->db->query($sql, $userIds)->rowCount();
                break;
                
            case 'remove_vip':
                $sql = "UPDATE users SET is_vip = 0 WHERE id IN ({$placeholders})";
                $affected = $this->db->query($sql, $userIds)->rowCount();
                break;
                
            case 'delete':
                foreach ($userIds as $userId) {
                    $result = $this->delete($userId);
                    if ($result['success']) $affected++;
                }
                break;
                
            case 'add_note':
                if (empty($params['note'])) {
                    return ['success' => false, 'error' => 'یادداشت الزامی است'];
                }
                $sql = "UPDATE users SET notes = CONCAT(COALESCE(notes, ''), '\n', ?) WHERE id IN ({$placeholders})";
                $allParams = array_merge([$params['note']], $userIds);
                $affected = $this->db->query($sql, $allParams)->rowCount();
                break;
                
            default:
                return ['success' => false, 'error' => 'عملیات نامعتبر'];
        }
        
        // پاک کردن کش
        foreach ($userIds as $userId) {
            $this->cache->delete("user_{$userId}_full");
            $this->cache->delete("user_{$userId}_stats");
        }
        
        $this->logger->info('Bulk action performed', [
            'action' => $action,
            'user_count' => count($userIds),
            'affected' => $affected
        ]);
        
        return [
            'success' => true,
            'affected' => $affected
        ];
    }
    
    // ══════════════════════════════════════
    // Export
    // ══════════════════════════════════════
    
    /**
     * خروجی CSV
     */
    public function exportCsv(array $filters = []) {
        $result = $this->getAll($filters, 1, 100000); // حداکثر 100K
        $users = $result['data'];
        
        $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        // ساخت پوشه در صورت عدم وجود
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        $fp = fopen($filepath, 'w');
        
        // BOM برای UTF-8
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // هدر
        fputcsv($fp, [
            'ID',
            'نام',
            'نام خانوادگی',
            'یوزرنیم',
            'تلفن',
            'VIP',
            'بلاک',
            'تعداد پیام',
            'مجموع دونیت',
            'تعداد دونیت',
            'تاریخ عضویت',
            'آخرین بازدید'
        ]);
        
        // داده‌ها
        foreach ($users as $user) {
            fputcsv($fp, [
                $user['id'],
                $user['first_name'] ?? '',
                $user['last_name'] ?? '',
                $user['username'] ?? '',
                $user['phone'] ?? '',
                $user['is_vip'] ? 'بله' : 'خیر',
                $user['blocked'] ? 'بله' : 'خیر',
                $user['message_count'] ?? 0,
                $user['total_donations'] ?? 0,
                $user['donation_count'] ?? 0,
                $user['joined_at'] ?? '',
                $user['last_seen'] ?? ''
            ]);
        }
        
        fclose($fp);
        
        $this->logger->info('Users exported to CSV', [
            'count' => count($users),
            'file' => $filename
        ]);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($users)
        ];
    }
    
    /**
     * خروجی JSON
     */
    public function exportJson(array $filters = []) {
        $result = $this->getAll($filters, 1, 100000);
        
        $json = json_encode([
            'exported_at' => date('Y-m-d H:i:s'),
            'total' => $result['pagination']['total'],
            'users' => $result['data']
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        $filename = 'users_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        file_put_contents($filepath, $json);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($result['data'])
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * فرمت‌بندی کاربر
     */
    private function formatUser($user) {
        // نام نمایشی
        $user['display_name'] = $this->formatDisplayName($user);
        
        // لینک پروفایل تلگرام
        if (!empty($user['username'])) {
            $user['telegram_url'] = 'https://t.me/' . $user['username'];
        }
        
        // زمان‌های نسبی
        if (!empty($user['joined_at'])) {
            $user['joined_ago'] = $this->timeAgo($user['joined_at']);
        }
        
        if (!empty($user['last_seen'])) {
            $user['last_seen_ago'] = $this->timeAgo($user['last_seen']);
            $user['is_online'] = (time() - strtotime($user['last_seen'])) < 300; // 5 دقیقه
        }
        
        // فرمت اعداد
        if (isset($user['total_donations'])) {
            $user['total_donations_formatted'] = number_format($user['total_donations']);
        }
        
        // وضعیت متنی
        $user['status_text'] = $this->getStatusText($user);
        $user['status_color'] = $this->getStatusColor($user);
        
        return $user;
    }
    
    /**
     * فرمت نام نمایشی
     */
    private function formatDisplayName($user) {
        if (!empty($user['first_name'])) {
            $name = $user['first_name'];
            if (!empty($user['last_name'])) {
                $name .= ' ' . $user['last_name'];
            }
            return $name;
        }
        
        if (!empty($user['username'])) {
            return '@' . $user['username'];
        }
        
        return 'کاربر #' . $user['id'];
    }
    
    /**
     * متن وضعیت
     */
    private function getStatusText($user) {
        if (!empty($user['blocked'])) {
            return 'بلاک شده';
        }
        
        if (!empty($user['is_vip'])) {
            return 'VIP';
        }
        
        if (!empty($user['last_seen']) && (time() - strtotime($user['last_seen'])) < 86400) {
            return 'فعال';
        }
        
        return 'عادی';
    }
    
    /**
     * رنگ وضعیت
     */
    private function getStatusColor($user) {
        if (!empty($user['blocked'])) {
            return 'red';
        }
        
        if (!empty($user['is_vip'])) {
            return 'yellow';
        }
        
        if (!empty($user['last_seen']) && (time() - strtotime($user['last_seen'])) < 86400) {
            return 'green';
        }
        
        return 'gray';
    }
    
    /**
     * اعتبارسنجی فیلد مرتب‌سازی
     */
    private function validateSortField($field) {
        $allowed = ['id', 'first_name', 'username', 'joined_at', 'last_seen', 'is_vip', 'blocked'];
        return in_array($field, $allowed) ? $field : 'joined_at';
    }
    
    /**
     * تبدیل زمان به "x دقیقه پیش"
     */
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'لحظاتی پیش';
        if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
        if ($diff < 604800) return floor($diff / 86400) . ' روز پیش';
        if ($diff < 2592000) return floor($diff / 604800) . ' هفته پیش';
        if ($diff < 31536000) return floor($diff / 2592000) . ' ماه پیش';
        return floor($diff / 31536000) . ' سال پیش';
    }
    
    /**
     * پاک کردن کش
     */
    public function clearCache() {
        $this->cache->clear();
        $this->logger->info('Users cache cleared');
        return true;
    }
}