<?php
/**
 * ============================================
 * کلاس پردازش Webhook تلگرام
 * ============================================
 * دریافت و پردازش Update ها
 * مدیریت دستورات
 * بررسی کلمات کلیدی
 * اتصال به هوش مصنوعی
 * ذخیره پیام‌ها و کاربران
 */

namespace App\Telegram;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;
use App\Core\Cache;
use App\AI\OpenAI;

class Webhook {
    private $bot;
    private $db;
    private $logger;
    private $config;
    private $update;
    private $chatId;
    private $userId;
    private $username;
    private $firstName;
    private $text;
    private $messageType;
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct(Bot $bot = null) {
        $this->bot = $bot ?? new Bot();
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }
    
    // ──────────────────────────────────────
    // پردازش اصلی Webhook
    // ──────────────────────────────────────
    public function handle() {
        // دریافت Update
        $rawInput = file_get_contents('php://input');
        $this->update = json_decode($rawInput, true);
        
        if (!$this->update) {
            $this->logger->warning('Empty or invalid update received');
            http_response_code(400);
            return;
        }
        
        // بررسی Secret Token
        if (!$this->verifySecret()) {
            $this->logger->security('Invalid secret token', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            http_response_code(403);
            return;
        }
        
        // لاگ دریافت Update
        $this->logger->telegram('Update received', [
            'update_id' => $this->update['update_id'] ?? 'unknown'
        ]);
        
        // پردازش بر اساس نوع Update
        if (isset($this->update['message'])) {
            $this->handleMessage($this->update['message']);
        } elseif (isset($this->update['callback_query'])) {
            $this->handleCallbackQuery($this->update['callback_query']);
        } elseif (isset($this->update['edited_message'])) {
            $this->handleEditedMessage($this->update['edited_message']);
        } elseif (isset($this->update['my_chat_member'])) {
            $this->handleChatMemberUpdate($this->update['my_chat_member']);
        }
        
        http_response_code(200);
    }
    
    // ──────────────────────────────────────
    // بررسی Secret Token
    // ──────────────────────────────────────
    private function verifySecret() {
        $secret = $this->config->telegram('webhook_secret');
        
        if (empty($secret)) {
            return true; // اگر secret تنظیم نشده، رد نکن
        }
        
        $receivedSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
        
        return hash_equals($secret, $receivedSecret);
    }
    
    // ══════════════════════════════════════
    // پردازش پیام متنی
    // ══════════════════════════════════════
    private function handleMessage($message) {
        $this->chatId = $message['chat']['id'];
        $this->userId = $message['from']['id'] ?? null;
        $this->username = $message['from']['username'] ?? '';
        $this->firstName = $message['from']['first_name'] ?? '';
        $this->text = $message['text'] ?? '';
        
        // تشخیص نوع پیام
        $this->messageType = $this->detectMessageType($message);
        
        // ذخیره/آپدیت کاربر
        $this->saveUser($message['from'] ?? []);
        
        // ذخیره پیام
        if ($this->text) {
            $this->saveMessage($this->userId, $this->text, 'in', 'text');
        }
        
        // بررسی بلاک بودن کاربر
        if ($this->isBlocked($this->userId)) {
            $this->logger->telegram('Blocked user tried to message', ['user_id' => $this->userId]);
            return;
        }
        
        // 1. بررسی دستورات (Commands)
        if ($this->isCommand($this->text)) {
            $this->processCommand($this->text);
            return;
        }
        
        // 2. بررسی کلمات کلیدی
        if ($this->checkKeyword($this->text)) {
            return;
        }
        
        // 3. اتصال به هوش مصنوعی
        if ($this->config->ai('enabled')) {
            $this->processWithAI($this->text);
            return;
        }
        
        // 4. پاسخ پیش‌فرض
        $this->sendDefaultResponse();
    }
    
    // ══════════════════════════════════════
    // پردازش Callback Query (دکمه‌ها)
    // ══════════════════════════════════════
    private function handleCallbackQuery($callback) {
        $this->chatId = $callback['message']['chat']['id'];
        $this->userId = $callback['from']['id'];
        $this->username = $callback['from']['username'] ?? '';
        $this->firstName = $callback['from']['first_name'] ?? '';
        $data = $callback['data'];
        
        // پاسخ به Callback (حذف Loading)
        $this->bot->answerCallbackQuery($callback['id']);
        
        $this->logger->telegram('Callback query', [
            'user_id' => $this->userId,
            'data' => $data
        ]);
        
        // پردازش بر اساس Data
        switch ($data) {
            case 'home':
                $this->sendMainMenu();
                break;
                
            case 'donate':
                $this->sendDonateMenu();
                break;
                
            case 'youtube':
                $this->sendYoutubeLink();
                break;
                
            case 'vip':
                $this->sendVipInfo();
                break;
                
            case 'contact':
                $this->sendContactInfo();
                break;
                
            case 'help':
                $this->sendHelp();
                break;
                
            case 'about':
                $this->sendAbout();
                break;
                
            default:
                // بررسی Callback های سفارشی
                if (strpos($data, 'keyword_') === 0) {
                    $keywordId = str_replace('keyword_', '', $data);
                    $this->sendKeywordResponse($keywordId);
                } else {
                    $this->bot->sendMessage($this->chatId, '❌ دستور ناشناخته');
                }
                break;
        }
    }
    
    // ══════════════════════════════════════
    // پردازش پیام ویرایش شده
    // ══════════════════════════════════════
    private function handleEditedMessage($message) {
        $this->logger->telegram('Message edited', [
            'user_id' => $message['from']['id'] ?? 'unknown',
            'text' => mb_substr($message['text'] ?? '', 0, 100)
        ]);
    }
    
    // ══════════════════════════════════════
    // پردازش تغییر وضعیت عضو
    // ══════════════════════════════════════
    private function handleChatMemberUpdate($chatMember) {
        $newStatus = $chatMember['new_chat_member']['status'] ?? '';
        $userId = $chatMember['from']['id'] ?? '';
        
        if ($newStatus === 'kicked' || $newStatus === 'left') {
            $this->db->update('users', ['blocked' => 1], 'id = ?', [$userId]);
            $this->logger->telegram('User blocked/left bot', ['user_id' => $userId]);
        } elseif ($newStatus === 'member') {
            $this->db->update('users', ['blocked' => 0], 'id = ?', [$userId]);
            $this->logger->telegram('User unblocked bot', ['user_id' => $userId]);
        }
    }
    
    // ══════════════════════════════════════
    // پردازش دستورات
    // ══════════════════════════════════════
    private function processCommand($text) {
        // حذف @botname از دستور
        $command = strtolower(explode(' ', $text)[0]);
        $command = preg_replace('/@[\w_]+$/', '', $command);
        $args = trim(str_replace(explode(' ', $text)[0], '', $text));
        
        $this->logger->telegram('Command received', [
            'command' => $command,
            'user_id' => $this->userId
        ]);
        
        switch ($command) {
            case '/start':
                $this->sendWelcome();
                break;
                
            case '/help':
                $this->sendHelp();
                break;
                
            case '/donate':
                $this->sendDonateMenu();
                break;
                
            case '/vip':
                $this->sendVipInfo();
                break;
                
            case '/contact':
                $this->sendContactInfo();
                break;
                
            case '/about':
                $this->sendAbout();
                break;
                
            case '/stats':
                $this->sendUserStats();
                break;
                
            default:
                $this->bot->sendMessage($this->chatId, "❓ دستور <code>{$command}</code> شناسایی نشد.\n\nاز /help برای دیدن دستورات استفاده کنید.");
                break;
        }
    }
    
    // ══════════════════════════════════════
    // ارسال پیام‌ها
    // ══════════════════════════════════════
    
    /**
     * پیام خوش‌آمدگویی
     */
    private function sendWelcome() {
        $welcomeText = $this->getSetting('welcome_text') ?? 
            "سلام {$this->firstName} عزیز! 👋\n\nبه ربات ما خوش اومدی 🎬\n\nاز منوی زیر استفاده کن:";
        
        $welcomePhoto = $this->getSetting('welcome_photo');
        $keyboard = Keyboard::mainMenu();
        
        if ($welcomePhoto) {
            $this->bot->sendPhotoWithKeyboard($this->chatId, $welcomePhoto, $welcomeText, $keyboard);
        } else {
            $this->bot->sendMessageWithKeyboard($this->chatId, $welcomeText, $keyboard);
        }
    }
    
    /**
     * منوی اصلی
     */
    private function sendMainMenu() {
        $text = "🏠 <b>منوی اصلی</b>\n\nیک گزینه انتخاب کنید:";
        $keyboard = Keyboard::mainMenu();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * منوی دونیت
     */
    private function sendDonateMenu() {
        $donateLink = $this->getSetting('donate_link') ?? '#';
        $donateText = $this->getSetting('donate_text') ?? 
            "💰 <b>حمایت مالی</b>\n\nبا حمایت مالی از ما، به تولید محتوای بهتر کمک می‌کنید! 🙏\n\nهر مبلغی که دوست دارید دونیت کنید ❤️";
        
        $keyboard = Keyboard::donateMenu($donateLink);
        $this->bot->sendMessageWithKeyboard($this->chatId, $donateText, $keyboard);
    }
    
    /**
     * لینک یوتیوب
     */
    private function sendYoutubeLink() {
        $ytUrl = $this->getSetting('youtube_url') ?? '#';
        $text = "🎬 <b>کانال یوتیوب ما</b>\n\nحتماً سابسکرایب کنید و زنگوله 🔔 رو بزنید!";
        $keyboard = Keyboard::youtubeMenu($ytUrl);
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * اطلاعات VIP
     */
    private function sendVipInfo() {
        $isVip = $this->isVip($this->userId);
        
        if ($isVip) {
            $text = "👑 <b>شما عضو VIP هستید!</b>\n\nاز مزایای ویژه خود لذت ببرید:\n✅ دسترسی زودتر به ویدئوها\n✅ محتوای اختصاصی\n✅ پشتیبانی ویژه\n✅ نشان VIP در چت";
        } else {
            $text = "👥 <b>باشگاه مشتریان VIP</b>\n\nبا عضویت در باشگاه VIP از مزایای ویژه بهره‌مند شوید:\n\n✅ دسترسی زودتر به ویدئوها\n✅ محتوای اختصاصی\n✅ پشتیبانی ویژه\n✅ نشان VIP در چت\n\nبرای عضویت، از دکمه حمایت مالی استفاده کنید و مبلغ مورد نظر رو دونیت کنید.";
        }
        
        $keyboard = Keyboard::vipMenu();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * اطلاعات تماس
     */
    private function sendContactInfo() {
        $text = "📞 <b>تماس با ما</b>\n\nاگه سوالی دارید یا مشکلی پیش اومده، از راه‌های زیر با ما در ارتباط باشید:\n\n📧 ایمیل: support@example.com\n💬 تلگرام: @yourusername\n🎬 یوتیوب: youtube.com/@yourchannel";
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * راهنما
     */
    private function sendHelp() {
        $text = "❓ <b>راهنما</b>\n\nدستورات موجود:\n\n" .
                "/start - شروع و منوی اصلی\n" .
                "/help - نمایش این راهنما\n" .
                "/donate - حمایت مالی\n" .
                "/vip - باشگاه مشتریان\n" .
                "/contact - تماس با ما\n" .
                "/about - درباره ما\n" .
                "/stats - آمار شما\n\n" .
                "همچنین می‌تونید هر سوالی بپرسید و من جواب می‌دم! 🤖";
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * درباره ما
     */
    private function sendAbout() {
        $text = "ℹ️ <b>درباره ما</b>\n\nما یک تیم تولید محتوای ویدئویی هستیم که در یوتیوب فعالیت می‌کنیم.\n\nهدف ما ارائه محتوای آموزشی و سرگرم‌کننده با کیفیت بالا است.\n\nبا حمایت شما، ما می‌تونیم محتوای بهتری تولید کنیم! ❤️";
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * آمار کاربر
     */
    private function sendUserStats() {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$this->userId]);
        
        if (!$user) {
            $this->bot->sendMessage($this->chatId, '❌ اطلاعات شما یافت نشد');
            return;
        }
        
        $donations = $this->db->fetch(
            "SELECT COUNT(*) as count, COALESCE(SUM(amount),0) as total FROM donations WHERE user_id = ? AND status = 'success'",
            [$this->userId]
        );
        
        $messageCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE user_id = ?",
            [$this->userId]
        );
        
        $text = "📊 <b>آمار شما</b>\n\n" .
                "👤 نام: {$this->firstName}\n" .
                "🆔 آیدی: {$this->userId}\n" .
                "📅 عضویت: " . ($user['joined_at'] ?? 'نامشخص') . "\n" .
                "💬 تعداد پیام‌ها: " . number_format($messageCount) . "\n" .
                "💰 تعداد دونیت‌ها: " . ($donations['count'] ?? 0) . "\n" .
                "💵 مجموع دونیت: " . number_format($donations['total'] ?? 0) . " تومان\n" .
                ($user['is_vip'] ? "👑 وضعیت: VIP" : "👥 وضعیت: کاربر عادی");
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    // ══════════════════════════════════════
    // بررسی کلمات کلیدی
    // ══════════════════════════════════════
    private function checkKeyword($text) {
        if (empty($text)) return false;
        
        $stmt = $this->db->query(
            "SELECT * FROM keywords WHERE active = 1 ORDER BY LENGTH(keyword) DESC"
        );
        
        if (!$stmt) return false;
        
        $keywords = $stmt->fetchAll();
        
        foreach ($keywords as $kw) {
            if (mb_stripos($text, $kw['keyword']) !== false) {
                $this->logger->telegram('Keyword matched', [
                    'keyword' => $kw['keyword'],
                    'user_id' => $this->userId
                ]);
                
                // ارسال پاسخ
                if ($kw['answer_type'] === 'text' || empty($kw['answer_type'])) {
                    $this->bot->sendMessage($this->chatId, $kw['answer']);
                } elseif (!empty($kw['file_id'])) {
                    $method = 'send' . ucfirst($kw['answer_type']);
                    if (method_exists($this->bot, $method)) {
                        $this->bot->$method($this->chatId, $kw['file_id'], [
                            'caption' => $kw['answer']
                        ]);
                    }
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    // ══════════════════════════════════════
    // اتصال به هوش مصنوعی
    // ══════════════════════════════════════
    private function processWithAI($text) {
        try {
            $this->bot->sendTyping($this->chatId);
            
            // گرفتن تاریخچه چت
            $history = $this->db->fetchAll(
                "SELECT direction, text FROM messages WHERE user_id = ? ORDER BY id DESC LIMIT 10",
                [$this->userId]
            );
            $history = array_reverse($history);
            
            // اطلاعات کاربر
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$this->userId]);
            
            // اطلاعات دونیت‌ها
            $donations = $this->db->fetch(
                "SELECT COUNT(*) as count, COALESCE(SUM(amount),0) as total FROM donations WHERE user_id = ? AND status = 'success'",
                [$this->userId]
            );
            
            // ساخت AI Instance
            $ai = new OpenAI();
            $response = $ai->chat($this->userId, $text, [
                'history' => $history,
                'user_info' => [
                    'name' => $this->firstName,
                    'is_vip' => $user['is_vip'] ?? false,
                    'total_donations' => $donations['total'] ?? 0,
                    'donation_count' => $donations['count'] ?? 0
                ]
            ]);
            
            if ($response['success']) {
                $aiMessage = $response['message'];
                $this->bot->sendMessage($this->chatId, $aiMessage);
                
                // ذخیره پاسخ AI
                $this->saveMessage($this->userId, $aiMessage, 'out', 'ai');
                
            } else {
                $this->sendDefaultResponse();
            }
            
        } catch (\Exception $e) {
            $this->logger->error('AI Error', ['error' => $e->getMessage()]);
            $this->sendDefaultResponse();
        }
    }
    
    // ══════════════════════════════════════
    // پاسخ پیش‌فرض
    // ══════════════════════════════════════
    private function sendDefaultResponse() {
        $text = "✅ پیام شما دریافت شد.\n\nاگه سوالی دارید، از /help استفاده کنید یا با ادمین تماس بگیرید.";
        $keyboard = Keyboard::mainMenu();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * ذخیره/آپدیت کاربر
     */
    private function saveUser($from) {
        if (empty($from['id'])) return;
        
        try {
            $this->db->query(
                "INSERT INTO users (id, username, first_name, last_name, last_seen) 
                 VALUES (?, ?, ?, ?, NOW()) 
                 ON DUPLICATE KEY UPDATE 
                 username = VALUES(username), 
                 first_name = VALUES(first_name), 
                 last_name = VALUES(last_name), 
                 last_seen = NOW()",
                [
                    $from['id'],
                    $from['username'] ?? '',
                    $from['first_name'] ?? '',
                    $from['last_name'] ?? ''
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error('Save user error', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * ذخیره پیام
     */
    private function saveMessage($userId, $text, $direction, $type = 'text') {
        try {
            $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $text,
                'direction' => $direction,
                'message_type' => $type
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Save message error', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * بررسی بلاک بودن کاربر
     */
    private function isBlocked($userId) {
        $user = $this->db->fetch("SELECT blocked FROM users WHERE id = ?", [$userId]);
        return $user && $user['blocked'] == 1;
    }
    
    /**
     * بررسی VIP بودن کاربر
     */
    private function isVip($userId) {
        $user = $this->db->fetch("SELECT is_vip FROM users WHERE id = ?", [$userId]);
        return $user && $user['is_vip'] == 1;
    }
    
    /**
     * بررسی دستور بودن متن
     */
    private function isCommand($text) {
        return !empty($text) && strpos($text, '/') === 0;
    }
    
    /**
     * تشخیص نوع پیام
     */
    private function detectMessageType($message) {
        if (isset($message['text'])) return 'text';
        if (isset($message['photo'])) return 'photo';
        if (isset($message['video'])) return 'video';
        if (isset($message['document'])) return 'document';
        if (isset($message['audio'])) return 'audio';
        if (isset($message['voice'])) return 'voice';
        if (isset($message['location'])) return 'location';
        if (isset($message['contact'])) return 'contact';
        if (isset($message['sticker'])) return 'sticker';
        return 'unknown';
    }
    
    /**
     * دریافت تنظیمات از دیتابیس
     */
    private function getSetting($key) {
        $cache = Cache::getInstance();
        
        return $cache->remember("setting_{$key}", 3600, function() use ($key) {
            $result = $this->db->fetch(
                "SELECT value FROM settings WHERE key_name = ?",
                [$key]
            );
            return $result ? $result['value'] : null;
        });
    }
}