<?php

declare(strict_types=1);

/**
 * ============================================
 * کلاس اصلی ربات تلگرام (Bot)
 * ============================================
 * ارتباط با Telegram Bot API
 * ارسال پیام، عکس، ویدئو، فایل
 * مدیریت Keyboard ها
 * پشتیبانی از Callback Query
 * Error Handling و Rate Limit
 */

namespace App\Telegram;

use Exception;
use App\Core\Config;
use App\Core\Logger;

class Bot {
    private $token;
    private $apiUrl;
    private $logger;
    private $lastResponse;
    private $lastError;
    private $verifySsl;
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct($token = null) {
        $this->token = $token ?? Config::getInstance()->telegram('bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
        $this->logger = Logger::getInstance();
        $this->verifySsl = Config::getInstance()->telegram('verify_ssl', true);

        if (empty($this->token)) {
            throw new Exception('توکن ربات تلگرام تنظیم نشده است');
        }
    }
    
    // ──────────────────────────────────────
    // ارسال درخواست به API
    // ──────────────────────────────────────
    public function request($method, array $params = [], $timeout = 30) {
        $url = "{$this->apiUrl}/{$method}";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $this->lastError = "cURL Error: {$curlError}";
            $this->logger->error('Telegram API cURL Error', [
                'method' => $method,
                'error' => $curlError
            ]);
            return false;
        }
        
        $result = json_decode($response, true);
        $this->lastResponse = $result;
        
        if (!$result || !isset($result['ok'])) {
            $this->lastError = "Invalid Response: {$response}";
            $this->logger->error('Telegram API Invalid Response', [
                'method' => $method,
                'response' => $response
            ]);
            return false;
        }
        
        if (!$result['ok']) {
            $errorCode = $result['error_code'] ?? 'Unknown';
            $description = $result['description'] ?? 'Unknown Error';
            $this->lastError = "Telegram Error {$errorCode}: {$description}";
            
            $this->logger->error('Telegram API Error', [
                'method' => $method,
                'error_code' => $errorCode,
                'description' => $description,
                'params' => $params
            ]);
            
            // Rate Limit - صبر کن و دوباره تلاش کن
            if ($errorCode === 429) {
                $retryAfter = $result['parameters']['retry_after'] ?? 5;
                $this->logger->warning("Rate limit hit. Retrying after {$retryAfter} seconds");
                sleep($retryAfter);
                return $this->request($method, $params, $timeout);
            }
            
            return false;
        }
        
        return $result['result'] ?? true;
    }
    
    // ══════════════════════════════════════
    // متدهای ارسال پیام
    // ══════════════════════════════════════
    
    /**
     * ارسال پیام متنی
     */
    public function sendMessage($chatId, $text, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false,
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * ارسال عکس
     */
    public function sendPhoto($chatId, $photo, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendPhoto', $params);
    }
    
    /**
     * ارسال ویدئو
     */
    public function sendVideo($chatId, $video, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'video' => $video,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendVideo', $params);
    }
    
    /**
     * ارسال فایل
     */
    public function sendDocument($chatId, $document, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'document' => $document,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendDocument', $params);
    }
    
    /**
     * ارسال صدا
     */
    public function sendAudio($chatId, $audio, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'audio' => $audio,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendAudio', $params);
    }
    
    /**
     * ارسال Voice
     */
    public function sendVoice($chatId, $voice, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'voice' => $voice,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendVoice', $params);
    }
    
    /**
     * ارسال موقعیت مکانی
     */
    public function sendLocation($chatId, $latitude, $longitude, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendLocation', $params);
    }
    
    /**
     * ارسال Contact
     */
    public function sendContact($chatId, $phoneNumber, $firstName, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendContact', $params);
    }
    
    /**
     * ارسال Sticker
     */
    public function sendSticker($chatId, $sticker, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'sticker' => $sticker,
            'disable_notification' => false
        ], $options);
        
        return $this->request('sendSticker', $params);
    }
    
    /**
     * ارسال Poll
     */
    public function sendPoll($chatId, $question, $options, $pollOptions = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => json_encode($options),
            'disable_notification' => false
        ], $pollOptions);
        
        return $this->request('sendPoll', $params);
    }
    
    // ══════════════════════════════════════
    // متدهای ویرایش و حذف
    // ══════════════════════════════════════
    
    /**
     * ویرایش پیام متنی
     */
    public function editMessageText($chatId, $messageId, $text, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $options);
        
        return $this->request('editMessageText', $params);
    }
    
    /**
     * ویرایش Caption
     */
    public function editMessageCaption($chatId, $messageId, $caption, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ], $options);
        
        return $this->request('editMessageCaption', $params);
    }
    
    /**
     * ویرایش Reply Markup
     */
    public function editMessageReplyMarkup($chatId, $messageId, $replyMarkup, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => json_encode($replyMarkup)
        ], $options);
        
        return $this->request('editMessageReplyMarkup', $params);
    }
    
    /**
     * حذف پیام
     */
    public function deleteMessage($chatId, $messageId) {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    }
    
    /**
     * Forward پیام
     */
    public function forwardMessage($chatId, $fromChatId, $messageId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
            'disable_notification' => false
        ], $options);
        
        return $this->request('forwardMessage', $params);
    }
    
    /**
     * Copy پیام
     */
    public function copyMessage($chatId, $fromChatId, $messageId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
            'disable_notification' => false
        ], $options);
        
        return $this->request('copyMessage', $params);
    }
    
    // ══════════════════════════════════════
    // متدهای Callback Query
    // ══════════════════════════════════════
    
    /**
     * پاسخ به Callback Query
     */
    public function answerCallbackQuery($callbackQueryId, $options = []) {
        $params = array_merge([
            'callback_query_id' => $callbackQueryId
        ], $options);
        
        return $this->request('answerCallbackQuery', $params);
    }
    
    /**
     * نمایش Alert به کاربر
     */
    public function answerCallbackAlert($callbackQueryId, $text, $showAlert = true) {
        return $this->answerCallbackQuery($callbackQueryId, [
            'text' => $text,
            'show_alert' => $showAlert
        ]);
    }
    
    // ══════════════════════════════════════
    // متدهای Chat Actions
    // ══════════════════════════════════════
    
    /**
     * ارسال Chat Action
     */
    public function sendChatAction($chatId, $action) {
        return $this->request('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action
        ]);
    }
    
    /**
     * نمایش Typing
     */
    public function sendTyping($chatId) {
        return $this->sendChatAction($chatId, 'typing');
    }
    
    /**
     * نمایش Upload Photo
     */
    public function sendUploadPhoto($chatId) {
        return $this->sendChatAction($chatId, 'upload_photo');
    }
    
    /**
     * نمایش Upload Video
     */
    public function sendUploadVideo($chatId) {
        return $this->sendChatAction($chatId, 'upload_video');
    }
    
    /**
     * نمایش Upload Document
     */
    public function sendUploadDocument($chatId) {
        return $this->sendChatAction($chatId, 'upload_document');
    }
    
    /**
     * نمایش Record Voice
     */
    public function sendRecordVoice($chatId) {
        return $this->sendChatAction($chatId, 'record_voice');
    }
    
    // ══════════════════════════════════════
    // متدهای مدیریت Chat
    // ══════════════════════════════════════
    
    /**
     * دریافت اطلاعات Chat
     */
    public function getChat($chatId) {
        return $this->request('getChat', ['chat_id' => $chatId]);
    }
    
    /**
     * دریافت اعضای Chat
     */
    public function getChatAdministrators($chatId) {
        return $this->request('getChatAdministrators', ['chat_id' => $chatId]);
    }
    
    /**
     * دریافت تعداد اعضای Chat
     */
    public function getChatMembersCount($chatId) {
        return $this->request('getChatMembersCount', ['chat_id' => $chatId]);
    }
    
    /**
     * دریافت اطلاعات کاربر در Chat
     */
    public function getChatMember($chatId, $userId) {
        return $this->request('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * بن کاربر
     */
    public function banChatMember($chatId, $userId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId
        ], $options);
        
        return $this->request('banChatMember', $params);
    }
    
    /**
     * آنبن کاربر
     */
    public function unbanChatMember($chatId, $userId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => true
        ], $options);
        
        return $this->request('unbanChatMember', $params);
    }
    
    /**
     * محدود کردن کاربر
     */
    public function restrictChatMember($chatId, $userId, $permissions, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => json_encode($permissions)
        ], $options);
        
        return $this->request('restrictChatMember', $params);
    }
    
    /**
     * ارتقا کاربر به ادمین
     */
    public function promoteChatMember($chatId, $userId, $permissions, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId
        ], $permissions, $options);
        
        return $this->request('promoteChatMember', $params);
    }
    
    /**
     * تنظیم عنوان Chat
     */
    public function setChatTitle($chatId, $title) {
        return $this->request('setChatTitle', [
            'chat_id' => $chatId,
            'title' => $title
        ]);
    }
    
    /**
     * تنظیم توضیحات Chat
     */
    public function setChatDescription($chatId, $description) {
        return $this->request('setChatDescription', [
            'chat_id' => $chatId,
            'description' => $description
        ]);
    }
    
    /**
     * تنظیم عکس Chat
     */
    public function setChatPhoto($chatId, $photo) {
        return $this->request('setChatPhoto', [
            'chat_id' => $chatId,
            'photo' => $photo
        ]);
    }
    
    /**
     * حذف عکس Chat
     */
    public function deleteChatPhoto($chatId) {
        return $this->request('deleteChatPhoto', ['chat_id' => $chatId]);
    }
    
    /**
     * پین پیام
     */
    public function pinChatMessage($chatId, $messageId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => false
        ], $options);
        
        return $this->request('pinChatMessage', $params);
    }
    
    /**
     * آنپین پیام
     */
    public function unpinChatMessage($chatId, $messageId = null) {
        $params = ['chat_id' => $chatId];
        if ($messageId) {
            $params['message_id'] = $messageId;
        }
        
        return $this->request('unpinChatMessage', $params);
    }
    
    /**
     * آنپین همه پیام‌ها
     */
    public function unpinAllChatMessages($chatId) {
        return $this->request('unpinAllChatMessages', ['chat_id' => $chatId]);
    }
    
    /**
     * ترک Chat
     */
    public function leaveChat($chatId) {
        return $this->request('leaveChat', ['chat_id' => $chatId]);
    }
    
    // ══════════════════════════════════════
    // متدهای مدیریت Webhook
    // ══════════════════════════════════════
    
    /**
     * تنظیم Webhook
     */
    public function setWebhook($url, $options = []) {
        $params = array_merge([
            'url' => $url
        ], $options);
        
        return $this->request('setWebhook', $params);
    }
    
    /**
     * دریافت اطلاعات Webhook
     */
    public function getWebhookInfo() {
        return $this->request('getWebhookInfo');
    }
    
    /**
     * حذف Webhook
     */
    public function deleteWebhook($dropPendingUpdates = false) {
        return $this->request('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates
        ]);
    }
    
    // ══════════════════════════════════════
    // متدهای دریافت اطلاعات
    // ══════════════════════════════════════
    
    /**
     * دریافت اطلاعات Bot
     */
    public function getMe() {
        return $this->request('getMe');
    }
    
    /**
     * دریافت Updates (برای Long Polling)
     */
    public function getUpdates($options = []) {
        $params = array_merge([
            'offset' => 0,
            'limit' => 100,
            'timeout' => 0,
            'allowed_updates' => []
        ], $options);
        
        return $this->request('getUpdates', $params);
    }
    
    /**
     * دریافت فایل
     */
    public function getFile($fileId) {
        return $this->request('getFile', ['file_id' => $fileId]);
    }
    
    /**
     * دریافت لینک دانلود فایل
     */
    public function getFileUrl($fileId) {
        $file = $this->getFile($fileId);
        
        if ($file && isset($file['file_path'])) {
            return "https://api.telegram.org/file/bot{$this->token}/{$file['file_path']}";
        }
        
        return false;
    }
    
    /**
     * دانلود فایل
     */
    public function downloadFile($fileId, $savePath) {
        $fileUrl = $this->getFileUrl($fileId);
        
        if (!$fileUrl) {
            return false;
        }
        
        $ch = curl_init($fileUrl);
        $fp = fopen($savePath, 'w+');
        
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl
        ]);
        
        $success = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        return $success;
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * دریافت آخرین پاسخ
     */
    public function getLastResponse() {
        return $this->lastResponse;
    }
    
    /**
     * دریافت آخرین خطا
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * بررسی موفقیت آخرین درخواست
     */
    public function isSuccess() {
        return $this->lastResponse && isset($this->lastResponse['ok']) && $this->lastResponse['ok'];
    }
    
    /**
     * ارسال پیام با Reply Markup
     */
    public function sendMessageWithKeyboard($chatId, $text, $keyboard, $options = []) {
        $options['reply_markup'] = json_encode($keyboard);
        return $this->sendMessage($chatId, $text, $options);
    }
    
    /**
     * ارسال عکس با Reply Markup
     */
    public function sendPhotoWithKeyboard($chatId, $photo, $caption, $keyboard, $options = []) {
        $options['caption'] = $caption;
        $options['reply_markup'] = json_encode($keyboard);
        return $this->sendPhoto($chatId, $photo, $options);
    }
    
    /**
     * ارسال پیام به ادمین
     */
    public function sendToAdmin($text, $options = []) {
        $adminId = Config::getInstance()->telegram('admin_id');
        return $this->sendMessage($adminId, $text, $options);
    }
    
    /**
     * ارسال پیام به همه کاربران (Broadcast)
     */
    public function broadcast($chatIds, $text, $options = [], $delay = 50) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'blocked' => 0
        ];
        
        foreach ($chatIds as $chatId) {
            $result = $this->sendMessage($chatId, $text, $options);
            
            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
                
                // اگر کاربر ربات رو بلاک کرده
                if (strpos($this->lastError, 'bot was blocked') !== false) {
                    $results['blocked']++;
                }
            }
            
            // تأخیر برای جلوگیری از Rate Limit
            if ($delay > 0) {
                usleep($delay * 1000);
            }
        }
        
        return $results;
    }
    
    /**
     * دریافت توکن
     */
    public function getToken() {
        return $this->token;
    }
    
    /**
     * تنظیم توکن
     */
    public function setToken($token) {
        $this->token = $token;
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
        return $this;
    }
}