<?php
/**
 * ============================================
 * کلاس مدیریت چت زنده (Chat)
 * ============================================
 * چت زنده ادمین با کاربران
 * حالت چت (Chat Mode) - اختصاص یک ادمین به یک کاربر
 * آرشیو پیام‌های هر کاربر
 * ارسال انواع محتوا (متن، عکس، ویدئو، فایل)
 * نوتیفیکیشن پیام جدید
 * وضعیت آنلاین کاربر
 * جستجو در آرشیو چت
 * Export چت
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Telegram\Bot;
use App\Helpers\Security;

class Chat {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $bot;
    
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
    // حالت چت (Chat Mode)
    // ══════════════════════════════════════
    
    /**
     * فعال کردن حالت چت با یک کاربر
     * وقتی فعال باشد، پیام‌های کاربر به ادمین فوروارد می‌شود
     */
    public function startChat($userId, $adminId) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        if ($user['blocked']) {
            return ['success' => false, 'error' => 'کاربر بلاک شده است'];
        }
        
        // بررسی اینکه ادمین دیگری در حال چت نباشد
        $existingChat = $this->db->fetch(
            "SELECT * FROM chat_sessions WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
        
        if ($existingChat && $existingChat['admin_id'] != $adminId) {
            return [
                'success' => false,
                'error' => 'ادمین دیگری در حال چت با این کاربر است',
                'admin_id' => $existingChat['admin_id'],
                'started_at' => $existingChat['started_at']
            ];
        }
        
        // ایجاد یا بروزرسانی session
        if ($existingChat) {
            $this->db->update('chat_sessions', [
                'admin_id' => $adminId,
                'started_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ], 'id = ?', [$existingChat['id']]);
            
            $sessionId = $existingChat['id'];
        } else {
            $sessionId = $this->db->insert('chat_sessions', [
                'user_id' => $userId,
                'admin_id' => $adminId,
                'status' => 'active',
                'started_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // تنظیم در کش برای دسترسی سریع
        $this->cache->set("chat_active_{$userId}", $adminId, 86400);
        
        $this->logger->info('Chat session started', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'admin_id' => $adminId
        ]);
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'user' => $this->formatUser($user)
        ];
    }
    
    /**
     * پایان دادن به حالت چت
     */
    public function endChat($userId, $adminId = null) {
        $where = 'user_id = ? AND status = "active"';
        $params = [$userId];
        
        if ($adminId) {
            $where .= ' AND admin_id = ?';
            $params[] = $adminId;
        }
        
        $this->db->update('chat_sessions', [
            'status' => 'closed',
            'ended_at' => date('Y-m-d H:i:s')
        ], $where, $params);
        
        // پاک کردن کش
        $this->cache->delete("chat_active_{$userId}");
        
        $this->logger->info('Chat session ended', [
            'user_id' => $userId,
            'admin_id' => $adminId
        ]);
        
        return ['success' => true];
    }
    
    /**
     * بررسی فعال بودن چت
     */
    public function isChatActive($userId) {
        $active = $this->cache->get("chat_active_{$userId}");
        
        if ($active !== null) {
            return $active; // admin_id
        }
        
        $session = $this->db->fetch(
            "SELECT admin_id FROM chat_sessions WHERE user_id = ? AND status = 'active' LIMIT 1",
            [$userId]
        );
        
        if ($session) {
            $this->cache->set("chat_active_{$userId}", $session['admin_id'], 86400);
            return $session['admin_id'];
        }
        
        return false;
    }
    
    /**
     * دریافت لیست چت‌های فعال
     */
    public function getActiveChats($adminId = null) {
        $where = "cs.status = 'active'";
        $params = [];
        
        if ($adminId) {
            $where .= ' AND cs.admin_id = ?';
            $params[] = $adminId;
        }
        
        $sql = "SELECT 
                    cs.*,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.is_vip,
                    u.last_seen,
                    COALESCE(m.last_message, '') as last_message,
                    m.last_message_at,
                    COALESCE(uc.unread_count, 0) as unread_count
                FROM chat_sessions cs
                LEFT JOIN users u ON cs.user_id = u.id
                LEFT JOIN (
                    SELECT user_id, text as last_message, created_at as last_message_at
                    FROM messages
                    WHERE (user_id, id) IN (
                        SELECT user_id, MAX(id) FROM messages GROUP BY user_id
                    )
                ) m ON cs.user_id = m.user_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as unread_count
                    FROM messages
                    WHERE direction = 'in' AND is_read = 0
                    GROUP BY user_id
                ) uc ON cs.user_id = uc.user_id
                WHERE {$where}
                ORDER BY m.last_message_at DESC, cs.started_at DESC";
        
        $chats = $this->db->fetchAll($sql, $params);
        
        foreach ($chats as &$chat) {
            $chat = $this->formatChatSession($chat);
        }
        
        return $chats;
    }
    
    // ══════════════════════════════════════
    // ارسال پیام
    // ══════════════════════════════════════
    
    /**
     * ارسال پیام از طرف ادمین به کاربر
     */
    public function sendMessage($userId, $text, $adminId = null, $options = []) {
        // بررسی وجود کاربر
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        if ($user['blocked']) {
            return ['success' => false, 'error' => 'کاربر بلاک شده است'];
        }
        
        // پاکسازی XSS
        $text = Security::cleanXss($text);
        
        // ارسال به تلگرام
        try {
            $result = $this->bot->sendMessage($userId, $text, [
                'parse_mode' => $options['parse_mode'] ?? 'HTML',
                'disable_web_page_preview' => $options['disable_preview'] ?? false
            ]);
            
            if (!$result) {
                $error = $this->bot->getLastError();
                
                // بررسی بلاک شدن
                if (strpos($error, 'bot was blocked') !== false || 
                    strpos($error, 'chat not found') !== false) {
                    $this->db->update('users', ['blocked' => 1], 'id = ?', [$userId]);
                    $this->endChat($userId);
                }
                
                return ['success' => false, 'error' => $error];
            }
            
            // ذخیره در دیتابیس
            $messageId = $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $text,
                'direction' => 'out',
                'message_type' => 'text',
                'admin_id' => $adminId,
                'is_read' => 1 // پیام ادمین خوانده شده
            ]);
            
            $this->logger->info('Chat message sent', [
                'user_id' => $userId,
                'admin_id' => $adminId,
                'length' => mb_strlen($text)
            ]);
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'telegram_result' => $result
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * ارسال عکس از طرف ادمین
     */
    public function sendPhoto($userId, $photo, $caption = '', $adminId = null) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user || $user['blocked']) {
            return ['success' => false, 'error' => 'کاربر در دسترس نیست'];
        }
        
        try {
            $result = $this->bot->sendPhoto($userId, $photo, [
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ]);
            
            if (!$result) {
                return ['success' => false, 'error' => $this->bot->getLastError()];
            }
            
            $messageId = $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $caption ?: '[عکس]',
                'direction' => 'out',
                'message_type' => 'photo',
                'admin_id' => $adminId,
                'is_read' => 1
            ]);
            
            return ['success' => true, 'message_id' => $messageId];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * ارسال ویدئو از طرف ادمین
     */
    public function sendVideo($userId, $video, $caption = '', $adminId = null) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user || $user['blocked']) {
            return ['success' => false, 'error' => 'کاربر در دسترس نیست'];
        }
        
        try {
            $result = $this->bot->sendVideo($userId, $video, [
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ]);
            
            if (!$result) {
                return ['success' => false, 'error' => $this->bot->getLastError()];
            }
            
            $messageId = $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $caption ?: '[ویدئو]',
                'direction' => 'out',
                'message_type' => 'video',
                'admin_id' => $adminId,
                'is_read' => 1
            ]);
            
            return ['success' => true, 'message_id' => $messageId];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * ارسال فایل از طرف ادمین
     */
    public function sendDocument($userId, $document, $caption = '', $adminId = null) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user || $user['blocked']) {
            return ['success' => false, 'error' => 'کاربر در دسترس نیست'];
        }
        
        try {
            $result = $this->bot->sendDocument($userId, $document, [
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ]);
            
            if (!$result) {
                return ['success' => false, 'error' => $this->bot->getLastError()];
            }
            
            $messageId = $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $caption ?: '[فایل]',
                'direction' => 'out',
                'message_type' => 'document',
                'admin_id' => $adminId,
                'is_read' => 1
            ]);
            
            return ['success' => true, 'message_id' => $messageId];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * پاسخ سریع (Quick Reply)
     */
    public function quickReply($userId, $replyToMessageId, $text, $adminId = null) {
        // دریافت پیام اصلی
        $originalMessage = $this->db->fetch(
            "SELECT * FROM messages WHERE id = ?",
            [$replyToMessageId]
        );
        
        // ارسال پیام
        $result = $this->sendMessage($userId, $text, $adminId);
        
        if ($result['success'] && $originalMessage) {
            $result['reply_to'] = [
                'id' => $originalMessage['id'],
                'text' => mb_substr($originalMessage['text'], 0, 100),
                'direction' => $originalMessage['direction']
            ];
        }
        
        return $result;
    }
    
    // ══════════════════════════════════════
    // آرشیو پیام‌ها
    // ══════════════════════════════════════
    
    /**
     * دریافت آرشیو چت یک کاربر
     */
    public function getConversation($userId, $page = 1, $perPage = 50, $direction = 'all') {
        $where = 'm.user_id = ?';
        $params = [$userId];
        
        if ($direction === 'in') {
            $where .= " AND m.direction = 'in'";
        } elseif ($direction === 'out') {
            $where .= " AND m.direction = 'out'";
        }
        
        // شمارش
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages m WHERE {$where}",
            $params
        );
        
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // دریافت پیام‌ها (به ترتیب زمانی)
        $sql = "SELECT m.* FROM messages m WHERE {$where} ORDER BY m.created_at ASC LIMIT {$perPage} OFFSET {$offset}";
        
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
    
    /**
     * دریافت آخرین پیام‌های کاربر (برای نمایش در صفحه چت)
     */
    public function getLatestMessages($userId, $limit = 50) {
        $sql = "SELECT m.* FROM messages m WHERE m.user_id = ? ORDER BY m.created_at DESC LIMIT ?";
        
        $messages = $this->db->fetchAll($sql, [$userId, $limit]);
        
        // معکوس کردن برای نمایش به ترتیب زمانی
        $messages = array_reverse($messages);
        
        foreach ($messages as &$msg) {
            $msg = $this->formatMessage($msg);
        }
        
        // علامت‌گذاری پیام‌های خوانده نشده به عنوان خوانده شده
        $this->markAsRead($userId);
        
        return $messages;
    }
    
    /**
     * جستجو در آرشیو چت
     */
    public function searchInChat($userId, $query, $limit = 50) {
        $search = '%' . $query . '%';
        
        $sql = "SELECT m.* FROM messages m 
                WHERE m.user_id = ? AND m.text LIKE ?
                ORDER BY m.created_at DESC
                LIMIT ?";
        
        $messages = $this->db->fetchAll($sql, [$userId, $search, $limit]);
        
        foreach ($messages as &$msg) {
            $msg = $this->formatMessage($msg);
            // هایلایت کلمه جستجو شده
            $msg['text_highlighted'] = str_ireplace(
                $query,
                '<mark>' . $query . '</mark>',
                $msg['text_preview']
            );
        }
        
        return $messages;
    }
    
    /**
     * جستجو در همه چت‌ها
     */
    public function searchAllChats($query, $limit = 50) {
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
            $msg['user_display_name'] = $this->formatUserDisplayName($msg);
        }
        
        return $messages;
    }
    
    // ══════════════════════════════════════
    // وضعیت پیام‌ها
    // ══════════════════════════════════════
    
    /**
     * علامت‌گذاری پیام‌ها به عنوان خوانده شده
     */
    public function markAsRead($userId, $messageId = null) {
        $where = "user_id = ? AND direction = 'in' AND is_read = 0";
        $params = [$userId];
        
        if ($messageId) {
            $where .= ' AND id <= ?';
            $params[] = $messageId;
        }
        
        $this->db->update('messages', ['is_read' => 1], $where, $params);
        
        // پاک کردن کش تعداد پیام‌های خوانده نشده
        $this->cache->delete("unread_count_{$userId}");
        $this->cache->delete('total_unread_count');
        
        return true;
    }
    
    /**
     * دریافت تعداد پیام‌های خوانده نشده
     */
    public function getUnreadCount($userId = null) {
        if ($userId) {
            $cacheKey = "unread_count_{$userId}";
            
            return $this->cache->remember($cacheKey, 60, function() use ($userId) {
                return (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM messages WHERE user_id = ? AND direction = 'in' AND is_read = 0",
                    [$userId]
                );
            });
        }
        
        // کل پیام‌های خوانده نشده
        $cacheKey = 'total_unread_count';
        
        return $this->cache->remember($cacheKey, 60, function() {
            return (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE direction = 'in' AND is_read = 0"
            );
        });
    }
    
    /**
     * لیست کاربرانی که پیام خوانده نشده دارند
     */
    public function getUnreadUsers() {
        $sql = "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.is_vip,
                    u.last_seen,
                    COUNT(m.id) as unread_count,
                    MAX(m.created_at) as last_unread_at
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.direction = 'in' AND m.is_read = 0
                GROUP BY u.id
                ORDER BY last_unread_at DESC";
        
        $users = $this->db->fetchAll($sql);
        
        foreach ($users as &$user) {
            $user['display_name'] = $this->formatUserDisplayName($user);
            $user['is_online'] = $this->isOnline($user['last_seen'] ?? null);
            $user['last_unread_ago'] = $this->timeAgo($user['last_unread_at']);
        }
        
        return $users;
    }
    
    // ══════════════════════════════════════
    // وضعیت آنلاین
    // ══════════════════════════════════════
    
    /**
     * بررسی آنلاین بودن کاربر
     */
    public function isOnline($lastSeen = null) {
        if (!$lastSeen) return false;
        
        $lastSeenTime = is_string($lastSeen) ? strtotime($lastSeen) : $lastSeen;
        
        return (time() - $lastSeenTime) < 300; // 5 دقیقه
    }
    
    /**
     * دریافت وضعیت کاربر
     */
    public function getUserStatus($userId) {
        $user = $this->db->fetch("SELECT last_seen, blocked, is_vip FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return null;
        }
        
        $lastSeenTime = strtotime($user['last_seen'] ?? '2020-01-01');
        $diff = time() - $lastSeenTime;
        
        if ($user['blocked']) {
            return ['status' => 'blocked', 'text' => '🚫 بلاک شده', 'color' => 'red'];
        }
        
        if ($diff < 300) {
            return ['status' => 'online', 'text' => '🟢 آنلاین', 'color' => 'green'];
        }
        
        if ($diff < 3600) {
            return ['status' => 'recently', 'text' => '🟡 اخیراً آنلاین', 'color' => 'yellow'];
        }
        
        if ($diff < 86400) {
            return ['status' => 'offline', 'text' => '⚪ آفلاین', 'color' => 'gray'];
        }
        
        return [
            'status' => 'long_ago',
            'text' => '⚫ آخرین بازدید: ' . $this->timeAgo($user['last_seen']),
            'color' => 'gray'
        ];
    }
    
    // ══════════════════════════════════════
    // مدیریت چت
    // ══════════════════════════════════════
    
    /**
     * حذف یک پیام
     */
    public function deleteMessage($messageId) {
        $message = $this->db->fetch("SELECT * FROM messages WHERE id = ?", [$messageId]);
        
        if (!$message) {
            return ['success' => false, 'error' => 'پیام یافت نشد'];
        }
        
        $this->db->delete('messages', 'id = ?', [$messageId]);
        
        $this->logger->info('Chat message deleted', [
            'message_id' => $messageId,
            'user_id' => $message['user_id']
        ]);
        
        return ['success' => true];
    }
    
    /**
     * حذف تمام پیام‌های یک کاربر
     */
    public function clearConversation($userId) {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE user_id = ?",
            [$userId]
        );
        
        $this->db->delete('messages', 'user_id = ?', [$userId]);
        
        $this->cache->delete("unread_count_{$userId}");
        
        $this->logger->warning('Conversation cleared', [
            'user_id' => $userId,
            'deleted_count' => $count
        ]);
        
        return ['success' => true, 'deleted' => $count];
    }
    
    /**
     * افزودن یادداشت به چت
     */
    public function addNote($userId, $note, $adminId = null) {
        $noteId = $this->db->insert('messages', [
            'user_id' => $userId,
            'text' => "📝 یادداشت ادمین: " . $note,
            'direction' => 'note',
            'message_type' => 'note',
            'admin_id' => $adminId
        ]);
        
        return ['success' => true, 'note_id' => $noteId];
    }
    
    // ══════════════════════════════════════
    // Export چت
    // ══════════════════════════════════════
    
    /**
     * Export چت یک کاربر به فرمت متنی
     */
    public function exportChatText($userId) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        $messages = $this->db->fetchAll(
            "SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC",
            [$userId]
        );
        
        $userName = $this->formatUserDisplayName($user);
        $content = "═══════════════════════════════════════\n";
        $content .= "📋 آرشیو چت: {$userName}\n";
        $content .= "🆔 آیدی: {$userId}\n";
        $content .= "📅 تاریخ Export: " . date('Y-m-d H:i:s') . "\n";
        $content .= "💬 تعداد پیام‌ها: " . count($messages) . "\n";
        $content .= "═══════════════════════════════════════\n\n";
        
        foreach ($messages as $msg) {
            $time = $msg['created_at'] ?? '';
            $direction = $msg['direction'] === 'in' ? '👤 کاربر' : ($msg['direction'] === 'note' ? '📝 یادداشت' : '🤖 ادمین');
            $text = $msg['text'] ?? '';
            
            $content .= "[{$time}] {$direction}:\n{$text}\n\n";
        }
        
        $filename = 'chat_' . $userId . '_' . date('Y-m-d_H-i-s') . '.txt';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        file_put_contents($filepath, $content);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'message_count' => count($messages)
        ];
    }
    
    /**
     * Export چت به JSON
     */
    public function exportChatJson($userId) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        $messages = $this->db->fetchAll(
            "SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC",
            [$userId]
        );
        
        $data = [
            'exported_at' => date('Y-m-d H:i:s'),
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ],
            'total_messages' => count($messages),
            'messages' => $messages
        ];
        
        $filename = 'chat_' . $userId . '_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        file_put_contents(
            $filepath,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'message_count' => count($messages)
        ];
    }
    
    // ══════════════════════════════════════
    // آمار چت
    // ══════════════════════════════════════
    
    /**
     * آمار چت یک کاربر
     */
    public function getUserChatStats($userId) {
        $totalMessages = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE user_id = ?",
            [$userId]
        );
        
        $incoming = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE user_id = ? AND direction = 'in'",
            [$userId]
        );
        
        $outgoing = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE user_id = ? AND direction = 'out'",
            [$userId]
        );
        
        $firstMessage = $this->db->fetchColumn(
            "SELECT MIN(created_at) FROM messages WHERE user_id = ?",
            [$userId]
        );
        
        $lastMessage = $this->db->fetchColumn(
            "SELECT MAX(created_at) FROM messages WHERE user_id = ?",
            [$userId]
        );
        
        // فعالیت روزانه (7 روز اخیر)
        $dailyActivity = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                direction,
                COUNT(*) as count
            FROM messages
            WHERE user_id = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at), direction
            ORDER BY date ASC",
            [$userId]
        );
        
        return [
            'total' => (int)$totalMessages,
            'incoming' => (int)$incoming,
            'outgoing' => (int)$outgoing,
            'first_message' => $firstMessage,
            'last_message' => $lastMessage,
            'daily_activity' => $dailyActivity
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    private function formatMessage($msg) {
        // آیکون جهت
        $directionIcons = [
            'in' => '📥',
            'out' => '📤',
            'note' => '📝',
            'ai' => '🤖'
        ];
        $msg['direction_icon'] = $directionIcons[$msg['direction']] ?? '📝';
        
        // آیکون نوع
        $typeIcons = [
            'text' => '💬',
            'photo' => '🖼️',
            'video' => '🎥',
            'document' => '📄',
            'audio' => '🎵',
            'voice' => '🎤',
            'note' => '📝',
            'ai' => '🤖'
        ];
        $msg['type_icon'] = $typeIcons[$msg['message_type']] ?? '💬';
        
        // پیش‌نمایش متن
        if (!empty($msg['text'])) {
            $msg['text_preview'] = mb_substr($msg['text'], 0, 200);
        }
        
        // زمان نسبی
        if (!empty($msg['created_at'])) {
            $msg['time_ago'] = $this->timeAgo($msg['created_at']);
        }
        
        // وضعیت خوانده شده
        $msg['is_read'] = (bool)($msg['is_read'] ?? false);
        $msg['read_icon'] = $msg['is_read'] ? '✓✓' : '✓';
        
        return $msg;
    }
    
    private function formatUserDisplayName($user) {
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
        
        return 'کاربر #' . ($user['user_id'] ?? $user['id'] ?? '?');
    }
    
    private function formatUser($user) {
        $user['display_name'] = $this->formatUserDisplayName($user);
        $user['is_online'] = $this->isOnline($user['last_seen'] ?? null);
        $user['status'] = $this->getUserStatus($user['id']);
        return $user;
    }
    
    private function formatChatSession($chat) {
        $chat['display_name'] = $this->formatUserDisplayName($chat);
        $chat['is_online'] = $this->isOnline($chat['last_seen'] ?? null);
        
        if (!empty($chat['last_message'])) {
            $chat['last_message_preview'] = mb_substr($chat['last_message'], 0, 50);
        }
        
        if (!empty($chat['last_message_at'])) {
            $chat['last_message_ago'] = $this->timeAgo($chat['last_message_at']);
        }
        
        return $chat;
    }
    
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'الان';
        if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
        if ($diff < 604800) return floor($diff / 86400) . ' روز پیش';
        return date('Y-m-d', $time);
    }
}