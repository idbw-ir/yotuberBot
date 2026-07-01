<?php

declare(strict_types=1);

namespace App\Bale;

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

    public function __construct(Bot $bot = null) {
        $this->bot = $bot ?? new Bot();
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function handle() {
        $rawInput = file_get_contents('php://input');
        $this->update = json_decode($rawInput, true);

        if (!$this->update) {
            $this->logger->warning('Empty or invalid Bale update received');
            http_response_code(400);
            return;
        }

        $this->logger->telegram('Bale update received', [
            'update_id' => $this->update['update_id'] ?? 'unknown'
        ]);

        if (isset($this->update['message'])) {
            $this->handleMessage($this->update['message']);
        } elseif (isset($this->update['callback_query'])) {
            $this->handleCallbackQuery($this->update['callback_query']);
        } elseif (isset($this->update['edited_message'])) {
            $this->handleEditedMessage($this->update['edited_message']);
        }

        http_response_code(200);
    }

    private function handleMessage($message) {
        $this->chatId = $message['chat']['id'];
        $this->userId = $message['from']['id'] ?? null;
        $this->username = $message['from']['username'] ?? '';
        $this->firstName = $message['from']['first_name'] ?? '';
        $this->text = $message['text'] ?? '';

        $this->messageType = $this->detectMessageType($message);

        $this->saveUser($message['from'] ?? []);

        if ($this->text) {
            $this->saveMessage($this->userId, $this->text, 'in', 'text');
        }

        if ($this->isBlocked($this->userId)) {
            $this->logger->telegram('Blocked Bale user tried to message', ['user_id' => $this->userId]);
            return;
        }

        if ($this->isCommand($this->text)) {
            $this->processCommand($this->text);
            return;
        }

        if ($this->checkKeyword($this->text)) {
            return;
        }

        if ($this->config->ai('enabled')) {
            $this->processWithAI($this->text);
            return;
        }

        $this->sendDefaultResponse();
    }

    private function handleCallbackQuery($callback) {
        $this->chatId = $callback['message']['chat']['id'];
        $this->userId = $callback['from']['id'];
        $this->username = $callback['from']['username'] ?? '';
        $this->firstName = $callback['from']['first_name'] ?? '';
        $data = $callback['data'];

        $this->bot->answerCallbackQuery($callback['id']);

        $this->logger->telegram('Bale callback query', [
            'user_id' => $this->userId,
            'data' => $data
        ]);

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
                if (strpos($data, 'keyword_') === 0) {
                    $keywordId = str_replace('keyword_', '', $data);
                    $this->sendKeywordResponse($keywordId);
                } else {
                    $this->bot->sendMessage($this->chatId, '❌ دستور ناشناخته');
                }
                break;
        }
    }

    private function handleEditedMessage($message) {
        $this->logger->telegram('Bale message edited', [
            'user_id' => $message['from']['id'] ?? 'unknown',
            'text' => mb_substr($message['text'] ?? '', 0, 100)
        ]);
    }

    private function processCommand($text) {
        $command = strtolower(explode(' ', $text)[0]);
        $command = preg_replace('/@[\w_]+$/', '', $command);

        $this->logger->telegram('Bale command received', [
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

    private function sendWelcome() {
        $welcomeText = $this->getSetting('welcome_text') ??
            "سلام {$this->firstName} عزیز! 👋\n\nبه ربات ما در بله خوش اومدی 🎬\n\nاز منوی زیر استفاده کن:";

        $welcomePhoto = $this->getSetting('welcome_photo');
        $keyboard = Keyboard::mainMenu();

        if ($welcomePhoto) {
            $this->bot->sendPhotoWithKeyboard($this->chatId, $welcomePhoto, $welcomeText, $keyboard);
        } else {
            $this->bot->sendMessageWithKeyboard($this->chatId, $welcomeText, $keyboard);
        }
    }

    private function sendMainMenu() {
        $text = "🏠 <b>منوی اصلی</b>\n\nیک گزینه انتخاب کنید:";
        $keyboard = Keyboard::mainMenu();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }

    private function sendDonateMenu() {
        $donateLink = $this->getSetting('donate_link') ?? '#';
        $donateText = $this->getSetting('donate_text') ??
            "💰 <b>حمایت مالی</b>\n\nبا حمایت مالی از ما، به تولید محتوای بهتر کمک می‌کنید! 🙏\n\nهر مبلغی که دوست دارید دونیت کنید ❤️";

        $keyboard = Keyboard::donateMenu($donateLink);
        $this->bot->sendMessageWithKeyboard($this->chatId, $donateText, $keyboard);
    }

    private function sendYoutubeLink() {
        $ytUrl = $this->getSetting('youtube_url') ?? '#';
        $text = "🎬 <b>کانال یوتیوب ما</b>\n\nحتماً سابسکرایب کنید و زنگوله 🔔 رو بزنید!";
        $keyboard = Keyboard::youtubeMenu($ytUrl);
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }

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

    private function sendContactInfo() {
        $text = "📞 <b>تماس با ما</b>\n\nاگه سوالی دارید یا مشکلی پیش اومده، از راه‌های زیر با ما در ارتباط باشید:\n\n📧 ایمیل: support@example.com\n💬 بله: @yourusername\n🎬 یوتیوب: youtube.com/@yourchannel";

        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }

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

    private function sendAbout() {
        $text = "ℹ️ <b>درباره ما</b>\n\nما یک تیم تولید محتوای ویدئویی هستیم که در یوتیوب فعالیت می‌کنیم.\n\nهدف ما ارائه محتوای آموزشی و سرگرم‌کننده با کیفیت بالا است.\n\nبا حمایت شما، ما می‌تونیم محتوای بهتری تولید کنیم! ❤️";

        $keyboard = Keyboard::backButton();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }

    private function sendUserStats() {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ? AND platform = 'bale'", [$this->userId]);

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

    private function checkKeyword($text) {
        if (empty($text)) return false;

        $stmt = $this->db->query(
            "SELECT * FROM keywords WHERE active = 1 ORDER BY LENGTH(keyword) DESC"
        );

        if (!$stmt) return false;

        $keywords = $stmt->fetchAll();

        foreach ($keywords as $kw) {
            if (mb_stripos($text, $kw['keyword']) !== false) {
                $this->logger->telegram('Bale keyword matched', [
                    'keyword' => $kw['keyword'],
                    'user_id' => $this->userId
                ]);

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

    private function processWithAI($text) {
        try {
            $this->bot->sendTyping($this->chatId);

            $history = $this->db->fetchAll(
                "SELECT direction, text FROM messages WHERE user_id = ? ORDER BY id DESC LIMIT 10",
                [$this->userId]
            );
            $history = array_reverse($history);

            $user = $this->db->fetch("SELECT * FROM users WHERE id = ? AND platform = 'bale'", [$this->userId]);

            $donations = $this->db->fetch(
                "SELECT COUNT(*) as count, COALESCE(SUM(amount),0) as total FROM donations WHERE user_id = ? AND status = 'success'",
                [$this->userId]
            );

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
                $this->saveMessage($this->userId, $aiMessage, 'out', 'ai');
            } else {
                $this->sendDefaultResponse();
            }

        } catch (\Exception $e) {
            $this->logger->error('Bale AI Error', ['error' => $e->getMessage()]);
            $this->sendDefaultResponse();
        }
    }

    private function sendDefaultResponse() {
        $text = "✅ پیام شما دریافت شد.\n\nاگه سوالی دارید، از /help استفاده کنید یا با ادمین تماس بگیرید.";
        $keyboard = Keyboard::mainMenu();
        $this->bot->sendMessageWithKeyboard($this->chatId, $text, $keyboard);
    }

    private function saveUser($from) {
        if (empty($from['id'])) return;

        try {
            $existing = $this->db->fetch("SELECT id FROM users WHERE id = ?", [$from['id']]);

            if ($existing) {
                $this->db->query(
                    "UPDATE users SET username = ?, first_name = ?, last_name = ?, last_seen = NOW() WHERE id = ?",
                    [$from['username'] ?? '', $from['first_name'] ?? '', $from['last_name'] ?? '', $from['id']]
                );
            } else {
                $this->db->query(
                    "INSERT INTO users (id, username, first_name, last_name, last_seen, platform) 
                     VALUES (?, ?, ?, ?, NOW(), 'bale')",
                    [$from['id'], $from['username'] ?? '', $from['first_name'] ?? '', $from['last_name'] ?? '']
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Bale save user error', ['error' => $e->getMessage()]);
        }
    }

    private function saveMessage($userId, $text, $direction, $type = 'text') {
        try {
            $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $text,
                'direction' => $direction,
                'message_type' => $type
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Bale save message error', ['error' => $e->getMessage()]);
        }
    }

    private function isBlocked($userId) {
        $user = $this->db->fetch("SELECT blocked FROM users WHERE id = ?", [$userId]);
        return $user && $user['blocked'] == 1;
    }

    private function isVip($userId) {
        $user = $this->db->fetch("SELECT is_vip FROM users WHERE id = ? AND platform = 'bale'", [$userId]);
        return $user && $user['is_vip'] == 1;
    }

    private function isCommand($text) {
        return !empty($text) && strpos($text, '/') === 0;
    }

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

    private function sendKeywordResponse($keywordId) {
        $kw = $this->db->fetch("SELECT * FROM keywords WHERE id = ?", [$keywordId]);
        if ($kw) {
            if ($kw['answer_type'] === 'text') {
                $this->bot->sendMessage($this->chatId, $kw['answer']);
            } elseif (!empty($kw['file_id'])) {
                $method = 'send' . ucfirst($kw['answer_type']);
                if (method_exists($this->bot, $method)) {
                    $this->bot->$method($this->chatId, $kw['file_id'], ['caption' => $kw['answer']]);
                }
            }
        }
    }

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
