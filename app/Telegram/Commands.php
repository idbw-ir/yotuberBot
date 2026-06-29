<?php
/**
 * ============================================
 * کلاس مدیریت دستورات ربات تلگرام (Commands)
 * ============================================
 * ثبت و پردازش دستورات
 * پشتیبانی از آرگومان‌ها
 * دستورات ادمین
 * سیستم Help خودکار
 * Command Pattern
 */

namespace App\Telegram;

use App\Core\Database;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Cache;

class Commands {
    private $bot;
    private $db;
    private $config;
    private $logger;
    private $commands = [];
    private $adminCommands = [];
    private $chatId;
    private $userId;
    private $username;
    private $firstName;
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct(Bot $bot) {
        $this->bot = $bot;
        $this->db = Database::getInstance();
        $this->config = Config::getInstance();
        $this->logger = Logger::getInstance();
        
        // ثبت دستورات پیش‌فرض
        $this->registerDefaultCommands();
    }
    
    // ──────────────────────────────────────
    // تنظیم اطلاعات کاربر
    // ──────────────────────────────────────
    public function setUserInfo($chatId, $userId, $username = '', $firstName = '') {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->username = $username;
        $this->firstName = $firstName;
        return $this;
    }
    
    // ──────────────────────────────────────
    // ثبت دستور
    // ──────────────────────────────────────
    public function register($command, $callback, $description = '', $isAdmin = false) {
        $command = strtolower($command);
        
        $data = [
            'command' => $command,
            'callback' => $callback,
            'description' => $description,
            'is_admin' => $isAdmin
        ];
        
        if ($isAdmin) {
            $this->adminCommands[$command] = $data;
        } else {
            $this->commands[$command] = $data;
        }
        
        return $this;
    }
    
    // ──────────────────────────────────────
    // ثبت دستورات پیش‌فرض
    // ──────────────────────────────────────
    private function registerDefaultCommands() {
        // دستورات عمومی
        $this->register('/start', [$this, 'handleStart'], 'شروع و منوی اصلی');
        $this->register('/help', [$this, 'handleHelp'], 'نمایش راهنما');
        $this->register('/about', [$this, 'handleAbout'], 'درباره ما');
        $this->register('/contact', [$this, 'handleContact'], 'تماس با ما');
        $this->register('/donate', [$this, 'handleDonate'], 'حمایت مالی');
        $this->register('/vip', [$this, 'handleVip'], 'باشگاه مشتریان');
        $this->register('/stats', [$this, 'handleStats'], 'آمار شما');
        $this->register('/settings', [$this, 'handleSettings'], 'تنظیمات');
        
        // دستورات ادمین
        $this->register('/admin', [$this, 'handleAdmin'], 'پنل ادمین', true);
        $this->register('/broadcast', [$this, 'handleBroadcast'], 'ارسال دسته‌جمعی', true);
        $this->register('/users', [$this, 'handleUsers'], 'لیست کاربران', true);
        $this->register('/logs', [$this, 'handleLogs'], 'مشاهده لاگ‌ها', true);
        $this->register('/clearcache', [$this, 'handleClearCache'], 'پاک کردن کش', true);
    }
    
    // ──────────────────────────────────────
    // پردازش دستور
    // ──────────────────────────────────────
    public function handle($text) {
        if (empty($text) || strpos($text, '/') !== 0) {
            return false;
        }
        
        // Parse دستور و آرگومان‌ها
        $parsed = $this->parseCommand($text);
        $command = $parsed['command'];
        $args = $parsed['args'];
        
        $this->logger->telegram('Processing command', [
            'command' => $command,
            'args' => $args,
            'user_id' => $this->userId
        ]);
        
        // بررسی در دستورات عمومی
        if (isset($this->commands[$command])) {
            return $this->executeCommand($this->commands[$command], $args);
        }
        
        // بررسی در دستورات ادمین
        if (isset($this->adminCommands[$command])) {
            if (!$this->isAdmin()) {
                $this->bot->sendMessage($this->chatId, '❌ شما دسترسی به این دستور را ندارید');
                return true;
            }
            return $this->executeCommand($this->adminCommands[$command], $args);
        }
        
        // دستور ناشناخته
        $this->bot->sendMessage($this->chatId, "❓ دستور <code>{$command}</code> شناسایی نشد.\n\nاز /help برای دیدن دستورات استفاده کنید.");
        return true;
    }
    
    // ──────────────────────────────────────
    // Parse دستور و آرگومان‌ها
    // ──────────────────────────────────────
    private function parseCommand($text) {
        $parts = explode(' ', trim($text), 2);
        $command = strtolower($parts[0]);
        
        // حذف @botname از دستور
        $command = preg_replace('/@[\w_]+$/', '', $command);
        
        $args = isset($parts[1]) ? $this->parseArgs($parts[1]) : [];
        
        return [
            'command' => $command,
            'args' => $args,
            'raw_args' => $parts[1] ?? ''
        ];
    }
    
    // ──────────────────────────────────────
    // Parse آرگومان‌ها
    // ──────────────────────────────────────
    private function parseArgs($argsString) {
        $args = [];
        
        // بررسی برای flag ها (--flag=value یا -f value)
        preg_match_all('/--?([a-zA-Z0-9_]+)(?:=([^\s]+))?/', $argsString, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2] ?? true;
            $args[$key] = $value;
            
            // حذف flag از argsString
            $argsString = str_replace($match[0], '', $argsString);
        }
        
        // بقیه آرگومان‌ها positional هستند
        $positional = array_filter(explode(' ', trim($argsString)));
        $args['_positional'] = array_values($positional);
        
        return $args;
    }
    
    // ──────────────────────────────────────
    // اجرای دستور
    // ──────────────────────────────────────
    private function executeCommand($commandData, $args) {
        $callback = $commandData['callback'];
        
        try {
            if (is_callable($callback)) {
                call_user_func($callback, $args);
            } elseif (is_array($callback) && count($callback) === 2) {
                call_user_func($callback, $args);
            } else {
                throw new \Exception('Callback نامعتبر است');
            }
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Command execution error', [
                'command' => $commandData['command'],
                'error' => $e->getMessage()
            ]);
            $this->bot->sendMessage($this->chatId, '❌ خطا در اجرای دستور');
            return false;
        }
    }
    
    // ──────────────────────────────────────
    // بررسی ادمین بودن
    // ──────────────────────────────────────
    private function isAdmin() {
        $adminId = $this->config->telegram('admin_id');
        return $this->userId == $adminId;
    }
    
    // ══════════════════════════════════════
    // دستورات پیش‌فرض - عمومی
    // ══════════════════════════════════════
    
    /**
     * دستور /start
     */
    private function handleStart($args) {
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
     * دستور /help
     */
    private function handleHelp($args) {
        $text = "❓ <b>راهنما</b>\n\nدستورات موجود:\n\n";
        
        // دستورات عمومی
        foreach ($this->commands as $cmd => $data) {
            if (!empty($data['description'])) {
                $text .= "{$cmd} - {$data['description']}\n";
            }
        }
        
        // دستورات ادمین (فقط برای ادمین)
        if ($this->isAdmin() && !empty($this->adminCommands)) {
            $text .= "\n<b>دستورات ادمین:</b>\n";
            foreach ($this->adminCommands as $cmd => $data) {
                if (!empty($data['description'])) {
                    $text .= "{$cmd} - {$data['description']}\n";
                }
            }
        }
        
        $text .= "\nهمچنین می‌تونید هر سوالی بپرسید! 🤖";
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * دستور /about
     */
    private function handleAbout($args) {
        $text = "ℹ️ <b>درباره ما</b>\n\nما یک تیم تولید محتوای ویدئویی هستیم که در یوتیوب فعالیت می‌کنیم.\n\nهدف ما ارائه محتوای آموزشی و سرگرم‌کننده با کیفیت بالا است.\n\nبا حمایت شما، ما می‌تونیم محتوای بهتری تولید کنیم! ❤️";
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * دستور /contact
     */
    private function handleContact($args) {
        $text = "📞 <b>تماس با ما</b>\n\nاگه سوالی دارید یا مشکلی پیش اومده، از راه‌های زیر با ما در ارتباط باشید:\n\n📧 ایمیل: support@example.com\n💬 تلگرام: @yourusername\n🎬 یوتیوب: youtube.com/@yourchannel";
        
        $keyboard = Keyboard::contactButtons(
            $phone = null,
            $username = 'yourusername',
            $email = 'support@example.com'
        );
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * دستور /donate
     */
    private function handleDonate($args) {
        $donateLink = $this->getSetting('donate_link') ?? '#';
        $donateText = $this->getSetting('donate_text') ?? 
            "💰 <b>حمایت مالی</b>\n\nبا حمایت مالی از ما، به تولید محتوای بهتر کمک می‌کنید! 🙏\n\nهر مبلغی که دوست دارید دونیت کنید ❤️";
        
        $keyboard = Keyboard::donateMenu($donateLink);
        $this->bot->sendMessageWithKeyboard($this->chatId, $donateText, $keyboard);
    }
    
    /**
     * دستور /vip
     */
    private function handleVip($args) {
        $isVip = $this->isVip();
        
        if ($isVip) {
            $text = "👑 <b>شما عضو VIP هستید!</b>\n\nاز مزایای ویژه خود لذت ببرید:\n✅ دسترسی زودتر به ویدئوها\n✅ محتوای اختصاصی\n✅ پشتیبانی ویژه\n✅ نشان VIP در چت";
        } else {
            $text = "👥 <b>باشگاه مشتریان VIP</b>\n\nبا عضویت در باشگاه VIP از مزایای ویژه بهره‌مند شوید:\n\n✅ دسترسی زودتر به ویدئوها\n✅ محتوای اختصاصی\n✅ پشتیبانی ویژه\n✅ نشان VIP در چت\n\nبرای عضویت، از دکمه حمایت مالی استفاده کنید.";
        }
        
        $keyboard = Keyboard::vipMenu();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * دستور /stats
     */
    private function handleStats($args) {
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
    
    /**
     * دستور /settings
     */
    private function handleSettings($args) {
        $text = "⚙️ <b>تنظیمات</b>\n\nتنظیمات ربات شما:\n\n" .
                "🔔 اعلان‌ها: فعال\n" .
                "🌐 زبان: فارسی\n" .
                "🎨 تم: پیش‌فرض\n\n" .
                "برای تغییر تنظیمات، به پنل مدیریت مراجعه کنید.";
        
        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    // ══════════════════════════════════════
    // دستورات پیش‌فرض - ادمین
    // ══════════════════════════════════════
    
    /**
     * دستور /admin
     */
    private function handleAdmin($args) {
        $adminUrl = $this->config->app('url') . '/admin/';
        
        $text = "🔐 <b>پنل مدیریت</b>\n\nبرای ورود به پنل مدیریت، روی دکمه زیر کلیک کنید:\n\n{$adminUrl}";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    Keyboard::urlButton('🔐 ورود به پنل مدیریت', $adminUrl)
                ],
                [
                    Keyboard::callbackButton('🏠 منوی اصلی', 'home')
                ]
            ]
        ];
        
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * دستور /broadcast
     */
    private function handleBroadcast($args) {
        if (empty($args['_positional'][0])) {
            $text = "📢 <b>ارسال دسته‌جمعی</b>\n\nنحوه استفاده:\n<code>/broadcast متن پیام</code>\n\nمثال:\n<code>/broadcast 🎬 ویدئوی جدید منتشر شد!</code>";
            $this->bot->sendMessage($this->chatId, $text);
            return;
        }
        
        $message = $args['_positional'][0];
        
        // دریافت همه کاربران
        $users = $this->db->fetchAll("SELECT id FROM users WHERE blocked = 0");
        $chatIds = array_column($users, 'id');
        
        $this->bot->sendMessage($this->chatId, "📤 در حال ارسال پیام به " . count($chatIds) . " کاربر...");
        
        // ارسال با تأخیر
        $results = $this->bot->broadcast($chatIds, $message, [], 50);
        
        $text = "✅ <b>ارسال دسته‌جمعی انجام شد</b>\n\n" .
                "✅ موفق: {$results['success']}\n" .
                "❌ خطا: {$results['failed']}\n" .
                "🚫 بلاک شده: {$results['blocked']}";
        
        $this->bot->sendMessage($this->chatId, $text);
    }
    
    /**
     * دستور /users
     */
    private function handleUsers($args) {
        $page = isset($args['_positional'][0]) ? (int)$args['_positional'][0] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $totalUsers = $this->db->fetchColumn("SELECT COUNT(*) FROM users");
        $totalPages = ceil($totalUsers / $perPage);
        
        $users = $this->db->fetchAll(
            "SELECT * FROM users ORDER BY joined_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        
        $text = "👥 <b>لیست کاربران</b> (صفحه {$page} از {$totalPages})\n\n";
        
        foreach ($users as $user) {
            $name = $user['first_name'] ?? $user['username'] ?? 'بدون نام';
            $vip = $user['is_vip'] ? ' 👑' : '';
            $blocked = $user['blocked'] ? ' 🚫' : '';
            $text .= "• {$name}{$vip}{$blocked}\n";
        }
        
        $keyboard = Keyboard::userListKeyboard($users, $page, $perPage);
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }
    
    /**
     * دستور /logs
     */
    private function handleLogs($args) {
        $channel = $args['_positional'][0] ?? 'app';
        $lines = isset($args['lines']) ? (int)$args['lines'] : 20;
        
        $logs = $this->logger->readRecent($channel, $lines);
        
        if (empty($logs)) {
            $this->bot->sendMessage($this->chatId, "📝 لاگی برای کانال '{$channel}' یافت نشد");
            return;
        }
        
        $text = "📝 <b>لاگ‌های اخیر</b> (کانال: {$channel})\n\n<pre>";
        foreach (array_slice($logs, -10) as $log) {
            $text .= htmlspecialchars($log) . "\n";
        }
        $text .= "</pre>";
        
        $this->bot->sendMessage($this->chatId, $text);
    }
    
    /**
     * دستور /clearcache
     */
    private function handleClearCache($args) {
        $cache = Cache::getInstance();
        $cache->clear();
        
        $this->bot->sendMessage($this->chatId, "✅ کش با موفقیت پاک شد");
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * دریافت تنظیمات
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
    
    /**
     * بررسی VIP بودن
     */
    private function isVip() {
        $user = $this->db->fetch("SELECT is_vip FROM users WHERE id = ?", [$this->userId]);
        return $user && $user['is_vip'] == 1;
    }
    
    /**
     * دریافت لیست دستورات
     */
    public function getCommands() {
        return [
            'public' => $this->commands,
            'admin' => $this->adminCommands
        ];
    }
    
    /**
     * ثبت دستور سفارشی
     */
    public function addCommand($command, $callback, $description = '', $isAdmin = false) {
        return $this->register($command, $callback, $description, $isAdmin);
    }
}