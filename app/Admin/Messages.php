<?php
/**
 * ============================================
 * کلاس مدیریت پیام‌ها (Messages)
 * ============================================
 * لیست و فیلتر پیام‌ها
 * آرشیو پیام‌های هر کاربر
 * ارسال پیام به کاربر
 * آمار پیام‌ها
 * Export (CSV/JSON)
 * Pagination
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Telegram\Bot;

class Messages {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $bot;
    private $cacheTtl = 300;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->logger = Logger::getInstance();
        $this->bot = new Bot();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ══════════════════════════════════════
    // دریافت لیست پیام‌ها
    // ══════════════════════════════════════
    
    public function getAll(array $filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];
        
        // فیلتر بر اساس کاربر
        if (!empty($filters['user_id'])) {
            $where[] = 'm.user_id = ?';
            $params[] = (int)$filters['user_id'];
        }
        
        // فیلتر بر اساس جهت
        if (!empty($filters['direction']) && in_array($filters['direction'], ['in', 'out'])) {
            $where[] = 'm.direction = ?';
            $params[] = $filters['direction'];
        }
        
        // فیلتر بر اساس نوع
        if (!empty($filters['type'])) {
            $where[] = 'm.message_type = ?';
            $params[] = $filters['type'];
        }
        
        // فیلتر تاریخ
        if (!empty($filters['date_from'])) {
            $where[] = 'm.created_at >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'm.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // جستجو در متن
        if (!empty($filters['search'])) {
            $where[] = 'm.text LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        // مرتب‌سازی
        $sortField = $this->validateSortField($filters['sort'] ?? 'created_at');
        $sortOrder = strtoupper($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        
        $whereStr = implode(' AND ', $where);
        
        // شمارش
        $countSql = "SELECT COUNT(*) FROM messages m WHERE {$whereStr}";
        $total = (int)$this->db->fetchColumn($countSql, $params);
        
        // Pagination
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // دریافت پیام‌ها
        $sql = "SELECT 
                    m.*,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.is_vip
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE {$whereStr}
                ORDER BY m.{$sortField} {$sortOrder}
                LIMIT {$perPage} OFFSET {$offset}";
        
        $messages = $this->db->fetchAll($sql, $params);
        
        foreach ($messages as &$msg) {
            $msg = $this->formatMessage($msg);
        }
        
        return [
            'data' => $messages,
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
    
    public function getById($messageId) {
        $sql = "SELECT 
                    m.*,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.is_vip
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.id = ?";
        
        $msg = $this->db->fetch($sql, [$messageId]);
        return $msg ? $this->formatMessage($msg) : null;
    }
    
    // ══════════════════════════════════════
    // آرشیو پیام‌های یک کاربر
    // ══════════════════════════════════════
    
    public function getUserMessages($userId, $page = 1, $perPage = 50) {
        $where = 'm.user_id = ?';
        $params = [$userId];
        
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages m WHERE {$where}",
            $params
        );
        
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    m.*,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE {$where}
                ORDER BY m.created_at ASC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $messages = $this->db->fetchAll($sql, $params);
        
        foreach ($messages as &$msg) {
            $msg = $this->formatMessage($msg);
        }
        
        return [
            'data' => $messages,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    public function getUserConversation($userId, $limit = 100) {
        $sql = "SELECT 
                    m.*,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.user_id = ?
                ORDER BY m.created_at DESC
                LIMIT ?";
        
        $messages = $this->db->fetchAll($sql, [$userId, $limit]);
        
        foreach ($messages as &$msg) {
            $msg = $this->formatMessage($msg);
        }
        
        return array_reverse($messages);
    }
    
    // ══════════════════════════════════════
    // ارسال پیام
    // ══════════════════════════════════════
    
    public function sendToUser($userId, $text, $messageType = 'text') {
        // بررسی وجود کاربر
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        if ($user['blocked']) {
            return ['success' => false, 'error' => 'کاربر بلاک شده است'];
        }
        
        // ارسال به تلگرام
        try {
            $result = $this->bot->sendMessage($userId, $text);
            
            if (!$result) {
                $error = $this->bot->getLastError();
                
                // اگر کاربر ربات رو بلاک کرده
                if (strpos($error, 'bot was blocked') !== false || strpos($error, 'chat not found') !== false) {
                    $this->db->update('users', ['blocked' => 1], 'id = ?', [$userId]);
                }
                
                return ['success' => false, 'error' => $error];
            }
            
            // ذخیره در دیتابیس
            $messageId = $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $text,
                'direction' => 'out',
                'message_type' => $messageType
            ]);
            
            $this->logger->info('Message sent to user', [
                'user_id' => $userId,
                'message_id' => $messageId,
                'length' => mb_strlen($text)
            ]);
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'telegram_result' => $result
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Send message error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function sendToMultipleUsers(array $userIds, $text, $delay = 50) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'blocked' => 0,
            'errors' => []
        ];
        
        foreach ($userIds as $userId) {
            $result = $this->sendToUser($userId, $text);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                
                if (strpos($result['error'], 'blocked') !== false) {
                    $results['blocked']++;
                } else {
                    $results['errors'][] = [
                        'user_id' => $userId,
                        'error' => $result['error']
                    ];
                }
            }
            
            if ($delay > 0) {
                usleep($delay * 1000);
            }
        }
        
        $this->logger->info('Bulk message sent', [
            'user_count' => count($userIds),
            'results' => $results
        ]);
        
        return $results;
    }
    
    // ══════════════════════════════════════
    // حذف پیام
    // ══════════════════════════════════════
    
    public function delete($messageId) {
        $message = $this->db->fetch("SELECT * FROM messages WHERE id = ?", [$messageId]);
        
        if (!$message) {
            return ['success' => false, 'error' => 'پیام یافت نشد'];
        }
        
        $this->db->delete('messages', 'id = ?', [$messageId]);
        
        $this->logger->info('Message deleted', [
            'message_id' => $messageId,
            'user_id' => $message['user_id']
        ]);
        
        return ['success' => true];
    }
    
    public function deleteUserMessages($userId) {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE user_id = ?",
            [$userId]
        );
        
        $this->db->delete('messages', 'user_id = ?', [$userId]);
        
        $this->logger->warning('All user messages deleted', [
            'user_id' => $userId,
            'count' => $count
        ]);
        
        return ['success' => true, 'deleted' => $count];
    }
    
    // ══════════════════════════════════════
    // آمار پیام‌ها
    // ══════════════════════════════════════
    
    public function getStatistics($days = 30) {
        $cacheKey = "messages_stats_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            // آمار کلی
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM messages");
            $incoming = $this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = 'in'");
            $outgoing = $this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = 'out'");
            $today = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()"
            );
            
            // آمار روزانه
            $daily = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    direction,
                    COUNT(*) as count
                FROM messages 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), direction
                ORDER BY date ASC",
                [$days]
            );
            
            // آمار ساعتی (امروز)
            $hourly = $this->db->fetchAll(
                "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as count
                FROM messages 
                WHERE DATE(created_at) = CURDATE()
                GROUP BY HOUR(created_at)
                ORDER BY hour ASC"
            );
            
            // پرکاربردترین کاربران
            $topUsers = $this->db->fetchAll(
                "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.username,
                    COUNT(m.id) as message_count
                FROM users u
                INNER JOIN messages m ON u.id = m.user_id
                WHERE m.direction = 'in'
                GROUP BY u.id
                ORDER BY message_count DESC
                LIMIT 10"
            );
            
            // آمار بر اساس نوع
            $byType = $this->db->fetchAll(
                "SELECT 
                    message_type,
                    COUNT(*) as count
                FROM messages
                GROUP BY message_type
                ORDER BY count DESC"
            );
            
            return [
                'total' => (int)$total,
                'incoming' => (int)$incoming,
                'outgoing' => (int)$outgoing,
                'today' => (int)$today,
                'daily' => $daily,
                'hourly' => $hourly,
                'top_users' => $topUsers,
                'by_type' => $byType
            ];
        });
    }
    
    // ══════════════════════════════════════
    // جستجو
    // ══════════════════════════════════════
    
    public function search($query, $limit = 50) {
        $search = '%' . $query . '%';
        
        $sql = "SELECT 
                    m.*,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.text LIKE ?
                ORDER BY m.created_at DESC
                LIMIT ?";
        
        $messages = $this->db->fetchAll($sql, [$search, $limit]);
        
        foreach ($messages as &$msg) {
            $msg = $this->formatMessage($msg);
        }
        
        return $messages;
    }
    
    // ══════════════════════════════════════
    // Export
    // ══════════════════════════════════════
    
    public function exportCsv(array $filters = []) {
        $result = $this->getAll($filters, 1, 100000);
        $messages = $result['data'];
        
        $filename = 'messages_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        $fp = fopen($filepath, 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        fputcsv($fp, ['ID', 'کاربر', 'یوزرنیم', 'جهت', 'نوع', 'متن', 'تاریخ']);
        
        foreach ($messages as $msg) {
            fputcsv($fp, [
                $msg['id'],
                $msg['user_display_name'] ?? '',
                $msg['username'] ?? '',
                $msg['direction'] === 'in' ? 'دریافتی' : 'ارسالی',
                $msg['message_type'],
                $msg['text'],
                $msg['created_at']
            ]);
        }
        
        fclose($fp);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($messages)
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    private function formatMessage($msg) {
        // نام نمایشی کاربر
        if (!empty($msg['first_name'])) {
            $name = $msg['first_name'];
            if (!empty($msg['last_name'])) {
                $name .= ' ' . $msg['last_name'];
            }
            $msg['user_display_name'] = $name;
        } elseif (!empty($msg['username'])) {
            $msg['user_display_name'] = '@' . $msg['username'];
        } else {
            $msg['user_display_name'] = 'کاربر #' . ($msg['user_id'] ?? '?');
        }
        
        // پیش‌نمایش متن
        if (!empty($msg['text'])) {
            $msg['text_preview'] = mb_substr($msg['text'], 0, 100);
            $msg['text_highlighted'] = $msg['text_preview'];
        }
        
        // زمان نسبی
        if (!empty($msg['created_at'])) {
            $msg['time_ago'] = $this->timeAgo($msg['created_at']);
        }
        
        // آیکون جهت
        $msg['direction_icon'] = $msg['direction'] === 'in' ? '📥' : '📤';
        $msg['direction_text'] = $msg['direction'] === 'in' ? 'دریافتی' : 'ارسالی';
        
        // آیکون نوع
        $typeIcons = [
            'text' => '💬',
            'photo' => '🖼️',
            'video' => '🎥',
            'document' => '📄',
            'audio' => '🎵',
            'voice' => '🎤',
            'location' => '📍',
            'contact' => '📱',
            'sticker' => '🎭',
            'ai' => '🤖'
        ];
        $msg['type_icon'] = $typeIcons[$msg['message_type']] ?? '📝';
        
        return $msg;
    }
    
    private function validateSortField($field) {
        $allowed = ['id', 'created_at', 'user_id', 'direction', 'message_type'];
        return in_array($field, $allowed) ? $field : 'created_at';
    }
    
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'لحظاتی پیش';
        if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
        if ($diff < 604800) return floor($diff / 86400) . ' روز پیش';
        if ($diff < 2592000) return floor($diff / 604800) . ' هفته پیش';
        return date('Y-m-d', $time);
    }
    
    public function clearCache() {
        for ($i = 7; $i <= 90; $i += 7) {
            $this->cache->delete("messages_stats_{$i}");
        }
        $this->logger->info('Messages cache cleared');
        return true;
    }
}