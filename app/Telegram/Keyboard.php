<?php
/**
 * ============================================
 * کلاس ساخت Inline Keyboard تلگرام
 * ============================================
 * ساخت Keyboard های حرفه‌ای
 * دکمه‌های URL و Callback
 * دکمه‌های ترکیبی
 * Pagination برای لیست‌ها
 * Static Methods برای استفاده آسان
 */

namespace App\Telegram;

class Keyboard {
    
    // ══════════════════════════════════════
    // Keyboard های اصلی
    // ══════════════════════════════════════
    
    /**
     * منوی اصلی
     */
    public static function mainMenu() {
        return [
            'inline_keyboard' => [
                [
                    self::callbackButton('💰 حمایت مالی', 'donate'),
                    self::callbackButton('🎬 کانال یوتیوب', 'youtube')
                ],
                [
                    self::callbackButton('👑 باشگاه VIP', 'vip'),
                    self::callbackButton('📞 تماس با ما', 'contact')
                ],
                [
                    self::callbackButton('❓ راهنما', 'help'),
                    self::callbackButton('ℹ️ درباره ما', 'about')
                ]
            ]
        ];
    }
    
    /**
     * منوی دونیت
     */
    public static function donateMenu($donateUrl) {
        return [
            'inline_keyboard' => [
                [
                    self::urlButton('💳 ورود به درگاه حمایت', $donateUrl)
                ],
                [
                    self::callbackButton('🏠 بازگشت به منوی اصلی', 'home')
                ]
            ]
        ];
    }
    
    /**
     * منوی یوتیوب
     */
    public static function youtubeMenu($youtubeUrl) {
        return [
            'inline_keyboard' => [
                [
                    self::urlButton('🎬 باز کردن کانال یوتیوب', $youtubeUrl)
                ],
                [
                    self::callbackButton('🔔 سابسکرایب کردم', 'subscribed')
                ],
                [
                    self::callbackButton('🏠 بازگشت به منوی اصلی', 'home')
                ]
            ]
        ];
    }
    
    /**
     * منوی VIP
     */
    public static function vipMenu() {
        return [
            'inline_keyboard' => [
                [
                    self::callbackButton('💰 عضویت از طریق حمایت مالی', 'donate')
                ],
                [
                    self::callbackButton('🏠 بازگشت به منوی اصلی', 'home')
                ]
            ]
        ];
    }
    
    /**
     * دکمه بازگشت
     */
    public static function backButton($callbackData = 'home') {
        return [
            'inline_keyboard' => [
                [
                    self::callbackButton('🔙 بازگشت', $callbackData)
                ]
            ]
        ];
    }
    
    /**
     * منوی خالی (فقط حذف کیبورد)
     */
    public static function removeKeyboard() {
        return [
            'remove_keyboard' => true
        ];
    }
    
    // ══════════════════════════════════════
    // Keyboard های پیشرفته
    // ══════════════════════════════════════
    
    /**
     * Keyboard با لیست آیتم‌ها (Pagination)
     */
    public static function paginationList(array $items, $currentPage = 1, $perPage = 5, $callbackPrefix = 'item_', $totalPages = null) {
        $keyboard = [];
        
        // محاسبه totalPages اگر داده نشده
        if ($totalPages === null) {
            $totalPages = ceil(count($items) / $perPage);
        }
        
        // برش آیتم‌ها برای صفحه فعلی
        $offset = ($currentPage - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);
        
        // افزودن آیتم‌ها
        foreach ($pageItems as $item) {
            $keyboard[] = [
                self::callbackButton($item['title'], $callbackPrefix . $item['id'])
            ];
        }
        
        // افزودن دکمه‌های Pagination
        $paginationRow = [];
        
        if ($currentPage > 1) {
            $paginationRow[] = self::callbackButton('⬅️ قبلی', "page_" . ($currentPage - 1));
        }
        
        $paginationRow[] = self::textButton("📄 {$currentPage}/{$totalPages}");
        
        if ($currentPage < $totalPages) {
            $paginationRow[] = self::callbackButton('بعدی ➡️', "page_" . ($currentPage + 1));
        }
        
        if (!empty($paginationRow)) {
            $keyboard[] = $paginationRow;
        }
        
        // دکمه بازگشت
        $keyboard[] = [
            self::callbackButton('🏠 منوی اصلی', 'home')
        ];
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard با دکمه‌های ترکیبی (URL + Callback)
     */
    public static function mixedButtons(array $buttons) {
        $keyboard = [];
        
        foreach ($buttons as $row) {
            $keyboardRow = [];
            
            foreach ($row as $button) {
                if (isset($button['url'])) {
                    $keyboardRow[] = self::urlButton($button['text'], $button['url']);
                } elseif (isset($button['callback'])) {
                    $keyboardRow[] = self::callbackButton($button['text'], $button['callback']);
                } elseif (isset($button['switch_query'])) {
                    $keyboardRow[] = self::switchInlineButton($button['text'], $button['switch_query']);
                }
            }
            
            if (!empty($keyboardRow)) {
                $keyboard[] = $keyboardRow;
            }
        }
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard برای تأیید عملیات
     */
    public static function confirmButtons($confirmCallback, $cancelCallback = 'home') {
        return [
            'inline_keyboard' => [
                [
                    self::callbackButton('✅ بله، تأیید می‌کنم', $confirmCallback),
                    self::callbackButton('❌ انصراف', $cancelCallback)
                ]
            ]
        ];
    }
    
    /**
     * Keyboard برای انتخاب مقدار
     */
    public static function selectionButtons(array $options, $callbackPrefix = 'select_') {
        $keyboard = [];
        
        foreach ($options as $option) {
            $keyboard[] = [
                self::callbackButton($option['title'], $callbackPrefix . $option['value'])
            ];
        }
        
        $keyboard[] = [
            self::callbackButton('🔙 بازگشت', 'home')
        ];
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard برای تماس سریع
     */
    public static function contactButtons($phone = null, $username = null, $email = null) {
        $keyboard = [];
        
        if ($phone) {
            $keyboard[] = [
                self::urlButton('📞 تماس تلفنی', "tel:{$phone}")
            ];
        }
        
        if ($username) {
            $keyboard[] = [
                self::urlButton('💬 پیام در تلگرام', "https://t.me/{$username}")
            ];
        }
        
        if ($email) {
            $keyboard[] = [
                self::urlButton('📧 ارسال ایمیل', "mailto:{$email}")
            ];
        }
        
        $keyboard[] = [
            self::callbackButton('🏠 منوی اصلی', 'home')
        ];
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard برای شبکه‌های اجتماعی
     */
    public static function socialMediaButtons($instagram = null, $twitter = null, $youtube = null, $telegram = null) {
        $keyboard = [];
        $row = [];
        
        if ($instagram) {
            $row[] = self::urlButton('📸 اینستاگرام', "https://instagram.com/{$instagram}");
        }
        
        if ($twitter) {
            $row[] = self::urlButton('🐦 توییتر', "https://twitter.com/{$twitter}");
        }
        
        if (!empty($row)) {
            $keyboard[] = $row;
        }
        
        $row = [];
        
        if ($youtube) {
            $row[] = self::urlButton('🎬 یوتیوب', $youtube);
        }
        
        if ($telegram) {
            $row[] = self::urlButton('✈️ تلگرام', "https://t.me/{$telegram}");
        }
        
        if (!empty($row)) {
            $keyboard[] = $row;
        }
        
        $keyboard[] = [
            self::callbackButton('🏠 منوی اصلی', 'home')
        ];
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    // ══════════════════════════════════════
    // ساخت دکمه‌های تکی
    // ══════════════════════════════════════
    
    /**
     * دکمه Callback
     */
    public static function callbackButton($text, $callbackData) {
        return [
            'text' => $text,
            'callback_data' => $callbackData
        ];
    }
    
    /**
     * دکمه URL
     */
    public static function urlButton($text, $url) {
        return [
            'text' => $text,
            'url' => $url
        ];
    }
    
    /**
     * دکمه متنی (غیرفعال)
     */
    public static function textButton($text) {
        return [
            'text' => $text,
            'callback_data' => 'noop'
        ];
    }
    
    /**
     * دکمه Switch Inline
     */
    public static function switchInlineButton($text, $query = '') {
        return [
            'text' => $text,
            'switch_inline_query_current_chat' => $query
        ];
    }
    
    /**
     * دکمه Switch Inline (چت دیگر)
     */
    public static function switchInlineButtonOther($text, $query = '') {
        return [
            'text' => $text,
            'switch_inline_query' => $query
        ];
    }
    
    /**
     * دکمه پرداخت
     */
    public static function payButton($text = '💳 پرداخت') {
        return [
            'text' => $text,
            'pay' => true
        ];
    }
    
    /**
     * دکمه درخواست Contact
     */
    public static function requestContactButton($text = '📱 ارسال شماره تماس') {
        return [
            'text' => $text,
            'request_contact' => true
        ];
    }
    
    /**
     * دکمه درخواست Location
     */
    public static function requestLocationButton($text = '📍 ارسال موقعیت مکانی') {
        return [
            'text' => $text,
            'request_location' => true
        ];
    }
    
    // ══════════════════════════════════════
    // Keyboard های سفارشی
    // ══════════════════════════════════════
    
    /**
     * ساخت Keyboard سفارشی از آرایه
     */
    public static function custom(array $buttons) {
        $keyboard = [];
        
        foreach ($buttons as $row) {
            if (is_array($row)) {
                $keyboard[] = $row;
            } else {
                $keyboard[] = [$row];
            }
        }
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard با تعداد ستون مشخص
     */
    public static function grid(array $buttons, $columns = 2) {
        $keyboard = [];
        $row = [];
        
        foreach ($buttons as $button) {
            $row[] = $button;
            
            if (count($row) === $columns) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        if (!empty($row)) {
            $keyboard[] = $row;
        }
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard برای نمایش اطلاعات
     */
    public static function infoKeyboard(array $info, $backCallback = 'home') {
        $keyboard = [];
        
        // افزودن اطلاعات به صورت دکمه‌های متنی
        foreach ($info as $label => $value) {
            $keyboard[] = [
                self::textButton("{$label}: {$value}")
            ];
        }
        
        // دکمه بازگشت
        $keyboard[] = [
            self::callbackButton('🔙 بازگشت', $backCallback)
        ];
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard برای لیست کاربران (ادمین)
     */
    public static function userListKeyboard(array $users, $page = 1, $perPage = 5) {
        $keyboard = [];
        
        $offset = ($page - 1) * $perPage;
        $pageUsers = array_slice($users, $offset, $perPage);
        
        foreach ($pageUsers as $user) {
            $userName = $user['first_name'] ?? $user['username'] ?? 'کاربر';
            $vipBadge = ($user['is_vip'] ?? false) ? ' 👑' : '';
            $keyboard[] = [
                self::callbackButton("{$userName}{$vipBadge}", "user_{$user['id']}")
            ];
        }
        
        // Pagination
        $totalPages = ceil(count($users) / $perPage);
        $paginationRow = [];
        
        if ($page > 1) {
            $paginationRow[] = self::callbackButton('⬅️', "users_page_" . ($page - 1));
        }
        
        $paginationRow[] = self::textButton("📄 {$page}/{$totalPages}");
        
        if ($page < $totalPages) {
            $paginationRow[] = self::callbackButton('➡️', "users_page_" . ($page + 1));
        }
        
        if (!empty($paginationRow)) {
            $keyboard[] = $paginationRow;
        }
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    /**
     * Keyboard برای لیست پیام‌ها (ادمین)
     */
    public static function messageListKeyboard(array $messages, $page = 1, $perPage = 5) {
        $keyboard = [];
        
        $offset = ($page - 1) * $perPage;
        $pageMessages = array_slice($messages, $offset, $perPage);
        
        foreach ($pageMessages as $message) {
            $preview = mb_substr($message['text'] ?? '', 0, 30);
            $time = date('H:i', strtotime($message['created_at']));
            $keyboard[] = [
                self::callbackButton("💬 {$preview}... ({$time})", "msg_{$message['id']}")
            ];
        }
        
        // Pagination
        $totalPages = ceil(count($messages) / $perPage);
        $paginationRow = [];
        
        if ($page > 1) {
            $paginationRow[] = self::callbackButton('⬅️', "messages_page_" . ($page - 1));
        }
        
        $paginationRow[] = self::textButton("📄 {$page}/{$totalPages}");
        
        if ($page < $totalPages) {
            $paginationRow[] = self::callbackButton('➡️', "messages_page_" . ($page + 1));
        }
        
        if (!empty($paginationRow)) {
            $keyboard[] = $paginationRow;
        }
        
        return [
            'inline_keyboard' => $keyboard
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * تبدیل به JSON
     */
    public static function toJson(array $keyboard) {
        return json_encode($keyboard, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * افزودن ردیف به Keyboard
     */
    public static function addRow(array $keyboard, array $buttons) {
        $keyboard['inline_keyboard'][] = $buttons;
        return $keyboard;
    }
    
    /**
     * افزودن دکمه به ردیف آخر
     */
    public static function addButton(array $keyboard, array $button) {
        if (empty($keyboard['inline_keyboard'])) {
            $keyboard['inline_keyboard'] = [[]];
        }
        
        $lastIndex = count($keyboard['inline_keyboard']) - 1;
        $keyboard['inline_keyboard'][$lastIndex][] = $button;
        
        return $keyboard;
    }
    
    /**
     * خالی کردن Keyboard
     */
    public static function empty() {
        return [
            'inline_keyboard' => []
        ];
    }
}