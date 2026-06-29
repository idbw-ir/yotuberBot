<?php
/**
 * ============================================
 * کلاس ارسال پیام دسته‌جمعی (Broadcast)
 * ============================================
 * ارسال پیام به گروه‌های مختلف کاربران
 * پشتیبانی از انواع محتوا (متن، عکس، ویدئو، فایل)
 * Queue-based processing
 * قابلیت Pause/Resume
 * زمان‌بندی ارسال
 * متغیرهای قالب (Template Variables)
 * آمار و گزارش ارسال
 * Rate Limiting هوشمند
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Telegram\Bot;

class Broadcast {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $bot;
    
    // تنظیمات پیش‌فرض
    private $defaultDelay = 50;       // میلی‌ثانیه بین هر پیام
    private $batchSize = 30;          // تعداد پیام در هر batch
    private $batchDelay = 1000;       // میلی‌ثانیه تأخیر بین batch ها
    private $maxRetries = 3;          // حداکثر تلاش مجدد
    private $rateLimitBuffer = 10;    // حاشیه امن برای Rate Limit
    
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
    // ایجاد Broadcast جدید
    // ══════════════════════════════════════
    
    /**
     * ایجاد یک کمپین Broadcast جدید
     */
    public function create(array $data) {
        // اعتبارسنجی
        if (empty($data['content'])) {
            return ['success' => false, 'error' => 'محتوای پیام الزامی است'];
        }
        
        if (empty($data['target'])) {
            return ['success' => false, 'error' => 'گروه هدف الزامی است'];
        }
        
        // شمارش کاربران هدف
        $targetCount = $this->countTargetUsers($data['target'], $data['target_options'] ?? []);
        
        if ($targetCount === 0) {
            return ['success' => false, 'error' => 'هیچ کاربری در گروه هدف یافت نشد'];
        }
        
        // ایجاد رکورد Broadcast
        $broadcastId = $this->db->insert('broadcasts', [
            'title' => $data['title'] ?? 'بدون عنوان',
            'content' => $data['content'],
            'content_type' => $data['content_type'] ?? 'text',
            'file_id' => $data['file_id'] ?? null,
            'target' => $data['target'],
            'target_options' => json_encode($data['target_options'] ?? []),
            'target_count' => $targetCount,
            'status' => 'pending',
            'delay' => $data['delay'] ?? $this->defaultDelay,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($broadcastId) {
            $this->logger->info('Broadcast created', [
                'broadcast_id' => $broadcastId,
                'target' => $data['target'],
                'target_count' => $targetCount
            ]);
            
            return [
                'success' => true,
                'broadcast_id' => $broadcastId,
                'target_count' => $targetCount
            ];
        }
        
        return ['success' => false, 'error' => 'خطا در ایجاد Broadcast'];
    }
    
    // ══════════════════════════════════════
    // شمارش کاربران هدف
    // ══════════════════════════════════════
    
    /**
     * شمارش تعداد کاربران در گروه هدف
     */
    public function countTargetUsers($target, array $options = []) {
        $where = ['blocked = 0'];
        $params = [];
        
        switch ($target) {
            case 'all':
                // همه کاربران غیر بلاک شده
                break;
                
            case 'vip':
                $where[] = 'is_vip = 1';
                break;
                
            case 'non_vip':
                $where[] = 'is_vip = 0';
                break;
                
            case 'active':
                $days = $options['active_days'] ?? 7;
                $where[] = 'last_seen >= DATE_SUB(NOW(), INTERVAL ? DAY)';
                $params[] = (int)$days;
                break;
                
            case 'inactive':
                $days = $options['inactive_days'] ?? 30;
                $where[] = 'last_seen < DATE_SUB(NOW(), INTERVAL ? DAY)';
                $params[] = (int)$days;
                break;
                
            case 'new':
                $days = $options['new_days'] ?? 7;
                $where[] = 'joined_at >= DATE_SUB(NOW(), INTERVAL ? DAY)';
                $params[] = (int)$days;
                break;
                
            case 'donors':
                $minAmount = $options['min_donation'] ?? 1;
                $where[] = 'id IN (SELECT user_id FROM donations WHERE status = "success" GROUP BY user_id HAVING SUM(amount) >= ?)';
                $params[] = (int)$minAmount;
                break;
                
            case 'non_donors':
                $where[] = 'id NOT IN (SELECT user_id FROM donations WHERE status = "success")';
                break;
                
            case 'custom':
                // کاربران خاص با آیدی
                if (!empty($options['user_ids']) && is_array($options['user_ids'])) {
                    $ids = array_map('intval', $options['user_ids']);
                    $placeholders = implode(',', $ids);
                    $where[] = "id IN ({$placeholders})";
                }
                break;
                
            case 'filtered':
                // فیلترهای سفارشی
                if (!empty($options['date_from'])) {
                    $where[] = 'joined_at >= ?';
                    $params[] = $options['date_from'];
                }
                if (!empty($options['date_to'])) {
                    $where[] = 'joined_at <= ?';
                    $params[] = $options['date_to'] . ' 23:59:59';
                }
                if (!empty($options['search'])) {
                    $search = '%' . $options['search'] . '%';
                    $where[] = '(first_name LIKE ? OR last_name LIKE ? OR username LIKE ?)';
                    $params = array_merge($params, [$search, $search, $search]);
                }
                break;
                
            default:
                return 0;
        }
        
        $whereStr = implode(' AND ', $where);
        
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE {$whereStr}",
            $params
        );
    }
    
    /**
     * دریافت لیست کاربران هدف
     */
    public function getTargetUsers($target, array $options = [], $limit = null, $offset = 0) {
        $where = ['blocked = 0'];
        $params = [];
        
        switch ($target) {
            case 'all':
                break;
            case 'vip':
                $where[] = 'is_vip = 1';
                break;
            case 'non_vip':
                $where[] = 'is_vip = 0';
                break;
            case 'active':
                $days = $options['active_days'] ?? 7;
                $where[] = 'last_seen >= DATE_SUB(NOW(), INTERVAL ? DAY)';
                $params[] = (int)$days;
                break;
            case 'inactive':
                $days = $options['inactive_days'] ?? 30;
                $where[] = 'last_seen < DATE_SUB(NOW(), INTERVAL ? DAY)';
                $params[] = (int)$days;
                break;
            case 'new':
                $days = $options['new_days'] ?? 7;
                $where[] = 'joined_at >= DATE_SUB(NOW(), INTERVAL ? DAY)';
                $params[] = (int)$days;
                break;
            case 'donors':
                $minAmount = $options['min_donation'] ?? 1;
                $where[] = 'id IN (SELECT user_id FROM donations WHERE status = "success" GROUP BY user_id HAVING SUM(amount) >= ?)';
                $params[] = (int)$minAmount;
                break;
            case 'non_donors':
                $where[] = 'id NOT IN (SELECT user_id FROM donations WHERE status = "success")';
                break;
            case 'custom':
                if (!empty($options['user_ids']) && is_array($options['user_ids'])) {
                    $ids = array_map('intval', $options['user_ids']);
                    $placeholders = implode(',', $ids);
                    $where[] = "id IN ({$placeholders})";
                }
                break;
        }
        
        $whereStr = implode(' AND ', $where);
        $sql = "SELECT id, first_name, last_name, username FROM users WHERE {$whereStr} ORDER BY id ASC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // ══════════════════════════════════════
    // اجرای Broadcast
    // ══════════════════════════════════════
    
    /**
     * شروع اجرای Broadcast
     */
    public function start($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        if ($broadcast['status'] === 'running') {
            return ['success' => false, 'error' => 'Broadcast در حال اجراست'];
        }
        
        if ($broadcast['status'] === 'completed') {
            return ['success' => false, 'error' => 'Broadcast قبلاً تکمیل شده'];
        }
        
        // بررسی زمان‌بندی
        if (!empty($broadcast['scheduled_at']) && strtotime($broadcast['scheduled_at']) > time()) {
            return ['success' => false, 'error' => 'زمان اجرا نرسیده است'];
        }
        
        // تغییر وضعیت به running
        $this->db->update('broadcasts', [
            'status' => 'running',
            'started_at' => $broadcast['started_at'] ?? date('Y-m-d H:i:s'),
            'current_offset' => $broadcast['current_offset'] ?? 0
        ], 'id = ?', [$broadcastId]);
        
        $this->logger->info('Broadcast started', [
            'broadcast_id' => $broadcastId,
            'target_count' => $broadcast['target_count']
        ]);
        
        // اجرای ارسال
        return $this->process($broadcastId);
    }
    
    /**
     * پردازش و ارسال پیام‌ها
     */
    private function process($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        $offset = (int)($broadcast['current_offset'] ?? 0);
        $totalSent = (int)($broadcast['sent_count'] ?? 0);
        $totalFailed = (int)($broadcast['failed_count'] ?? 0);
        $totalBlocked = (int)($broadcast['blocked_count'] ?? 0);
        
        $targetOptions = json_decode($broadcast['target_options'] ?? '{}', true) ?: [];
        $delay = (int)($broadcast['delay'] ?? $this->defaultDelay);
        
        // حلقه ارسال
        while (true) {
            // بررسی وضعیت (برای Pause)
            $current = $this->getById($broadcastId);
            if ($current['status'] === 'paused') {
                $this->logger->info('Broadcast paused', ['broadcast_id' => $broadcastId]);
                return ['success' => true, 'status' => 'paused', 'message' => 'Broadcast متوقف شد'];
            }
            
            if ($current['status'] === 'cancelled') {
                $this->logger->warning('Broadcast cancelled', ['broadcast_id' => $broadcastId]);
                return ['success' => true, 'status' => 'cancelled'];
            }
            
            // دریافت batch بعدی کاربران
            $users = $this->getTargetUsers(
                $broadcast['target'],
                $targetOptions,
                $this->batchSize,
                $offset
            );
            
            if (empty($users)) {
                break; // تمام کاربران پردازش شدن
            }
            
            $batchSent = 0;
            $batchFailed = 0;
            $batchBlocked = 0;
            
            foreach ($users as $user) {
                // پردازش متغیرهای قالب
                $content = $this->processTemplate($broadcast['content'], $user);
                
                // ارسال پیام
                $result = $this->sendContent($user['id'], $broadcast['content_type'], $content, $broadcast['file_id']);
                
                if ($result['success']) {
                    $batchSent++;
                    $totalSent++;
                    
                    // ثبت در جدول broadcast_recipients
                    $this->logRecipient($broadcastId, $user['id'], 'success');
                    
                } else {
                    $error = $result['error'] ?? '';
                    
                    if (strpos($error, 'bot was blocked') !== false || 
                        strpos($error, 'chat not found') !== false ||
                        strpos($error, 'user is deactivated') !== false) {
                        $batchBlocked++;
                        $totalBlocked++;
                        
                        // بلاک کردن کاربر
                        $this->db->update('users', ['blocked' => 1], 'id = ?', [$user['id']]);
                        
                        $this->logRecipient($broadcastId, $user['id'], 'blocked', $error);
                    } else {
                        $batchFailed++;
                        $totalFailed++;
                        $this->logRecipient($broadcastId, $user['id'], 'failed', $error);
                    }
                }
                
                // تأخیر بین پیام‌ها
                if ($delay > 0) {
                    usleep($delay * 1000);
                }
            }
            
            // بروزرسانی پیشرفت
            $offset += count($users);
            
            $this->db->update('broadcasts', [
                'current_offset' => $offset,
                'sent_count' => $totalSent,
                'failed_count' => $totalFailed,
                'blocked_count' => $totalBlocked,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$broadcastId]);
            
            // تأخیر بین batch ها
            if ($this->batchDelay > 0) {
                usleep($this->batchDelay * 1000);
            }
        }
        
        // تکمیل Broadcast
        $this->db->update('broadcasts', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'current_offset' => $offset,
            'sent_count' => $totalSent,
            'failed_count' => $totalFailed,
            'blocked_count' => $totalBlocked
        ], 'id = ?', [$broadcastId]);
        
        $this->logger->info('Broadcast completed', [
            'broadcast_id' => $broadcastId,
            'sent' => $totalSent,
            'failed' => $totalFailed,
            'blocked' => $totalBlocked
        ]);
        
        return [
            'success' => true,
            'status' => 'completed',
            'sent' => $totalSent,
            'failed' => $totalFailed,
            'blocked' => $totalBlocked
        ];
    }
    
    /**
     * ارسال محتوا بر اساس نوع
     */
    private function sendContent($userId, $contentType, $content, $fileId = null) {
        try {
            switch ($contentType) {
                case 'text':
                    $result = $this->bot->sendMessage($userId, $content);
                    break;
                    
                case 'photo':
                    $result = $this->bot->sendPhoto($userId, $fileId, [
                        'caption' => $content
                    ]);
                    break;
                    
                case 'video':
                    $result = $this->bot->sendVideo($userId, $fileId, [
                        'caption' => $content
                    ]);
                    break;
                    
                case 'document':
                    $result = $this->bot->sendDocument($userId, $fileId, [
                        'caption' => $content
                    ]);
                    break;
                    
                case 'audio':
                    $result = $this->bot->sendAudio($userId, $fileId, [
                        'caption' => $content
                    ]);
                    break;
                    
                case 'voice':
                    $result = $this->bot->sendVoice($userId, $fileId, [
                        'caption' => $content
                    ]);
                    break;
                    
                case 'sticker':
                    $result = $this->bot->sendSticker($userId, $fileId);
                    break;
                    
                default:
                    $result = $this->bot->sendMessage($userId, $content);
            }
            
            if ($result) {
                return ['success' => true];
            }
            
            return [
                'success' => false,
                'error' => $this->bot->getLastError() ?? 'Unknown error'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * پردازش متغیرهای قالب
     */
    private function processTemplate($content, $user) {
        $replacements = [
            '{first_name}' => $user['first_name'] ?? '',
            '{last_name}' => $user['last_name'] ?? '',
            '{username}' => $user['username'] ?? '',
            '{user_id}' => $user['id'] ?? '',
            '{full_name}' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            '{date}' => date('Y-m-d'),
            '{time}' => date('H:i'),
            '{datetime}' => date('Y-m-d H:i:s')
        ];
        
        // اگر نام خالی بود، از یوزرنیم یا آیدی استفاده کن
        if (empty($replacements['{full_name}'])) {
            $replacements['{full_name}'] = $replacements['{username}'] 
                ? '@' . $replacements['{username}'] 
                : 'کاربر عزیز';
        }
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
    
    /**
     * ثبت وضعیت ارسال برای هر کاربر
     */
    private function logRecipient($broadcastId, $userId, $status, $error = null) {
        try {
            $this->db->insert('broadcast_recipients', [
                'broadcast_id' => $broadcastId,
                'user_id' => $userId,
                'status' => $status,
                'error' => $error,
                'sent_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // اگر جدول وجود نداشت، نادیده بگیر
        }
    }
    
    // ══════════════════════════════════════
    // مدیریت وضعیت
    // ══════════════════════════════════════
    
    /**
     * توقف موقت Broadcast
     */
    public function pause($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        if ($broadcast['status'] !== 'running') {
            return ['success' => false, 'error' => 'Broadcast در حال اجرا نیست'];
        }
        
        $this->db->update('broadcasts', ['status' => 'paused'], 'id = ?', [$broadcastId]);
        
        $this->logger->info('Broadcast paused', ['broadcast_id' => $broadcastId]);
        
        return ['success' => true];
    }
    
    /**
     * ادامه Broadcast متوقف شده
     */
    public function resume($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        if ($broadcast['status'] !== 'paused') {
            return ['success' => false, 'error' => 'Broadcast متوقف نشده'];
        }
        
        $this->db->update('broadcasts', ['status' => 'running'], 'id = ?', [$broadcastId]);
        
        $this->logger->info('Broadcast resumed', ['broadcast_id' => $broadcastId]);
        
        return $this->process($broadcastId);
    }
    
    /**
     * لغو Broadcast
     */
    public function cancel($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        if (in_array($broadcast['status'], ['completed', 'cancelled'])) {
            return ['success' => false, 'error' => 'Broadcast قبلاً تکمیل یا لغو شده'];
        }
        
        $this->db->update('broadcasts', [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$broadcastId]);
        
        $this->logger->warning('Broadcast cancelled', ['broadcast_id' => $broadcastId]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // دریافت اطلاعات
    // ══════════════════════════════════════
    
    /**
     * دریافت یک Broadcast با آیدی
     */
    public function getById($broadcastId) {
        return $this->db->fetch("SELECT * FROM broadcasts WHERE id = ?", [$broadcastId]);
    }
    
    /**
     * دریافت لیست Broadcast ها
     */
    public function getAll($page = 1, $perPage = 20, $status = null) {
        $where = ['1=1'];
        $params = [];
        
        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        $whereStr = implode(' AND ', $where);
        
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM broadcasts WHERE {$whereStr}",
            $params
        );
        
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        $broadcasts = $this->db->fetchAll(
            "SELECT * FROM broadcasts WHERE {$whereStr} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        foreach ($broadcasts as &$b) {
            $b = $this->formatBroadcast($b);
        }
        
        return [
            'data' => $broadcasts,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    /**
     * دریافت وضعیت لحظه‌ای
     */
    public function getProgress($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return null;
        }
        
        $total = (int)$broadcast['target_count'];
        $sent = (int)($broadcast['sent_count'] ?? 0);
        $failed = (int)($broadcast['failed_count'] ?? 0);
        $blocked = (int)($broadcast['blocked_count'] ?? 0);
        $processed = $sent + $failed + $blocked;
        
        $progress = $total > 0 ? round(($processed / $total) * 100, 2) : 0;
        
        // محاسبه زمان باقی‌مانده
        $remaining = null;
        if ($broadcast['status'] === 'running' && $processed > 0) {
            $startTime = strtotime($broadcast['started_at']);
            $elapsed = time() - $startTime;
            $rate = $processed / $elapsed; // پیام در ثانیه
            $remainingCount = $total - $processed;
            $remaining = $rate > 0 ? round($remainingCount / $rate) : null;
        }
        
        return [
            'broadcast_id' => $broadcastId,
            'status' => $broadcast['status'],
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'blocked' => $blocked,
            'processed' => $processed,
            'remaining' => $total - $processed,
            'progress_percent' => $progress,
            'remaining_seconds' => $remaining,
            'started_at' => $broadcast['started_at'],
            'current_offset' => $broadcast['current_offset'] ?? 0
        ];
    }
    
    // ══════════════════════════════════════
    // پیش‌نمایش
    // ══════════════════════════════════════
    
    /**
     * پیش‌نمایش پیام با متغیرهای واقعی
     */
    public function preview($content, $target, array $targetOptions = []) {
        // دریافت یک کاربر نمونه
        $users = $this->getTargetUsers($target, $targetOptions, 3);
        
        $previews = [];
        foreach ($users as $user) {
            $previews[] = [
                'user' => $user,
                'content' => $this->processTemplate($content, $user)
            ];
        }
        
        return [
            'success' => true,
            'previews' => $previews,
            'count' => count($previews)
        ];
    }
    
    // ══════════════════════════════════════
    // آمار و گزارش
    // ══════════════════════════════════════
    
    /**
     * آمار کلی Broadcast ها
     */
    public function getStatistics() {
        $cacheKey = 'broadcast_statistics';
        
        return $this->cache->remember($cacheKey, 300, function() {
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM broadcasts");
            $totalSent = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(sent_count), 0) FROM broadcasts WHERE status = 'completed'"
            );
            $totalFailed = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(failed_count), 0) FROM broadcasts WHERE status = 'completed'"
            );
            $totalBlocked = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(blocked_count), 0) FROM broadcasts WHERE status = 'completed'"
            );
            
            $byStatus = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM broadcasts GROUP BY status"
            );
            
            $recentBroadcasts = $this->db->fetchAll(
                "SELECT * FROM broadcasts ORDER BY created_at DESC LIMIT 5"
            );
            
            foreach ($recentBroadcasts as &$b) {
                $b = $this->formatBroadcast($b);
            }
            
            return [
                'total_broadcasts' => (int)$total,
                'total_sent' => (int)$totalSent,
                'total_failed' => (int)$totalFailed,
                'total_blocked' => (int)$totalBlocked,
                'success_rate' => ($totalSent + $totalFailed) > 0 
                    ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 2) 
                    : 0,
                'by_status' => $byStatus,
                'recent' => $recentBroadcasts
            ];
        });
    }
    
    /**
     * دریافت لیست گیرندگان یک Broadcast
     */
    public function getRecipients($broadcastId, $status = null, $page = 1, $perPage = 50) {
        $where = ['broadcast_id = ?'];
        $params = [$broadcastId];
        
        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        $whereStr = implode(' AND ', $where);
        
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM broadcast_recipients WHERE {$whereStr}",
            $params
        );
        
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    br.*,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM broadcast_recipients br
                LEFT JOIN users u ON br.user_id = u.id
                WHERE {$whereStr}
                ORDER BY br.sent_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $recipients = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $recipients,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    // ══════════════════════════════════════
    // حذف
    // ══════════════════════════════════════
    
    /**
     * حذف Broadcast
     */
    public function delete($broadcastId) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        if ($broadcast['status'] === 'running') {
            return ['success' => false, 'error' => 'نمی‌توان Broadcast در حال اجرا را حذف کرد'];
        }
        
        // حذف گیرندگان
        $this->db->delete('broadcast_recipients', 'broadcast_id = ?', [$broadcastId]);
        
        // حذف Broadcast
        $this->db->delete('broadcasts', 'id = ?', [$broadcastId]);
        
        $this->logger->warning('Broadcast deleted', ['broadcast_id' => $broadcastId]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // تکرار Broadcast
    // ══════════════════════════════════════
    
    /**
     * تکرار یک Broadcast قبلی
     */
    public function duplicate($broadcastId, $newTitle = null) {
        $broadcast = $this->getById($broadcastId);
        
        if (!$broadcast) {
            return ['success' => false, 'error' => 'Broadcast یافت نشد'];
        }
        
        return $this->create([
            'title' => $newTitle ?? ($broadcast['title'] . ' (کپی)'),
            'content' => $broadcast['content'],
            'content_type' => $broadcast['content_type'],
            'file_id' => $broadcast['file_id'],
            'target' => $broadcast['target'],
            'target_options' => json_decode($broadcast['target_options'] ?? '{}', true),
            'delay' => $broadcast['delay']
        ]);
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    private function formatBroadcast($broadcast) {
        // وضعیت
        $statusConfig = [
            'pending' => ['text' => 'در انتظار', 'color' => 'gray', 'icon' => '⏳'],
            'running' => ['text' => 'در حال اجرا', 'color' => 'blue', 'icon' => '▶️'],
            'paused' => ['text' => 'متوقف', 'color' => 'yellow', 'icon' => '⏸️'],
            'completed' => ['text' => 'تکمیل شده', 'color' => 'green', 'icon' => '✅'],
            'cancelled' => ['text' => 'لغو شده', 'color' => 'red', 'icon' => '❌'],
            'scheduled' => ['text' => 'زمان‌بندی شده', 'color' => 'purple', 'icon' => '📅']
        ];
        
        $status = $broadcast['status'] ?? 'pending';
        $config = $statusConfig[$status] ?? $statusConfig['pending'];
        
        $broadcast['status_text'] = $config['text'];
        $broadcast['status_color'] = $config['color'];
        $broadcast['status_icon'] = $config['icon'];
        
        // درصد پیشرفت
        $total = (int)($broadcast['target_count'] ?? 0);
        $sent = (int)($broadcast['sent_count'] ?? 0);
        $failed = (int)($broadcast['failed_count'] ?? 0);
        $blocked = (int)($broadcast['blocked_count'] ?? 0);
        $processed = $sent + $failed + $blocked;
        
        $broadcast['progress_percent'] = $total > 0 ? round(($processed / $total) * 100, 2) : 0;
        $broadcast['processed_count'] = $processed;
        
        // پیش‌نمایش محتوا
        if (!empty($broadcast['content'])) {
            $broadcast['content_preview'] = mb_substr(strip_tags($broadcast['content']), 0, 100);
        }
        
        // نوع محتوا
        $typeIcons = [
            'text' => '💬',
            'photo' => '🖼️',
            'video' => '🎥',
            'document' => '📄',
            'audio' => '🎵',
            'voice' => '🎤',
            'sticker' => '🎭'
        ];
        $broadcast['type_icon'] = $typeIcons[$broadcast['content_type'] ?? 'text'] ?? '💬';
        
        // گروه هدف
        $targetTexts = [
            'all' => 'همه کاربران',
            'vip' => 'کاربران VIP',
            'non_vip' => 'کاربران عادی',
            'active' => 'کاربران فعال',
            'inactive' => 'کاربران غیرفعال',
            'new' => 'کاربران جدید',
            'donors' => 'حامیان مالی',
            'non_donors' => 'غیر حامی',
            'custom' => 'کاربران خاص',
            'filtered' => 'فیلتر شده'
        ];
        $broadcast['target_text'] = $targetTexts[$broadcast['target'] ?? 'all'] ?? 'نامشخص';
        
        // زمان نسبی
        if (!empty($broadcast['created_at'])) {
            $broadcast['created_ago'] = $this->timeAgo($broadcast['created_at']);
        }
        
        return $broadcast;
    }
    
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'لحظاتی پیش';
        if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
        if ($diff < 604800) return floor($diff / 86400) . ' روز پیش';
        return date('Y-m-d', $time);
    }
    
    /**
     * تنظیمات سفارشی
     */
    public function setDelay($milliseconds) {
        $this->defaultDelay = max(0, (int)$milliseconds);
        return $this;
    }
    
    public function setBatchSize($size) {
        $this->batchSize = max(1, min(100, (int)$size));
        return $this;
    }
    
    public function clearCache() {
        $this->cache->delete('broadcast_statistics');
        return true;
    }
}