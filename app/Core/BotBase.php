<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

abstract class BotBase {
    protected $token;
    protected $apiUrl;
    protected $fileBaseUrl;
    protected $logger;
    protected $lastResponse;
    protected $lastError;
    protected $verifySsl;
    protected $platformName;

    abstract protected function getDefaultApiBaseUrl(): string;
    abstract protected function getDefaultFileBaseUrl(): string;
    abstract protected function getPlatformName(): string;

    public function __construct($token = null, $apiUrl = null, $fileBaseUrl = null) {
        $this->token = $token ?? $this->resolveToken();
        $this->apiUrl = $apiUrl ?: $this->getDefaultApiBaseUrl() . $this->token;
        $this->fileBaseUrl = $fileBaseUrl ?: $this->getDefaultFileBaseUrl();
        $this->logger = Logger::getInstance();
        $this->verifySsl = Config::getInstance()->get($this->getPlatformName() . '.verify_ssl', true);
        $this->platformName = $this->getPlatformName();

        if (empty($this->token)) {
            throw new Exception('توکن ربات ' . $this->getPlatformDisplayName() . ' تنظیم نشده است');
        }
    }

    protected function resolveToken() {
        return Config::getInstance()->get($this->getPlatformName() . '.bot_token');
    }

    protected function getPlatformDisplayName() {
        $names = ['telegram' => 'تلگرام', 'bale' => 'بله'];
        return $names[$this->platformName] ?? $this->platformName;
    }

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

        Proxy::getInstance()->applyToCurl($ch);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->lastError = "cURL Error: {$curlError}";
            $this->logger->error($this->getPlatformDisplayName() . ' API cURL Error', [
                'method' => $method,
                'error' => $curlError
            ]);
            return false;
        }

        $result = json_decode($response, true);
        $this->lastResponse = $result;

        if (!$result || !isset($result['ok'])) {
            $this->lastError = "Invalid Response: {$response}";
            $this->logger->error($this->getPlatformDisplayName() . ' API Invalid Response', [
                'method' => $method,
                'response' => $response
            ]);
            return false;
        }

        if (!$result['ok']) {
            $errorCode = $result['error_code'] ?? 'Unknown';
            $description = $result['description'] ?? 'Unknown Error';
            $this->lastError = "{$this->getPlatformDisplayName()} Error {$errorCode}: {$description}";

            $this->logger->error($this->getPlatformDisplayName() . ' API Error', [
                'method' => $method,
                'error_code' => $errorCode,
                'description' => $description,
                'params' => $params
            ]);

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

    public function sendPhoto($chatId, $photo, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);

        return $this->request('sendPhoto', $params);
    }

    public function sendVideo($chatId, $video, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'video' => $video,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);

        return $this->request('sendVideo', $params);
    }

    public function sendDocument($chatId, $document, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'document' => $document,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);

        return $this->request('sendDocument', $params);
    }

    public function sendAudio($chatId, $audio, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'audio' => $audio,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);

        return $this->request('sendAudio', $params);
    }

    public function sendVoice($chatId, $voice, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'voice' => $voice,
            'parse_mode' => 'HTML',
            'disable_notification' => false
        ], $options);

        return $this->request('sendVoice', $params);
    }

    public function sendLocation($chatId, $latitude, $longitude, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'disable_notification' => false
        ], $options);

        return $this->request('sendLocation', $params);
    }

    public function sendContact($chatId, $phoneNumber, $firstName, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
            'disable_notification' => false
        ], $options);

        return $this->request('sendContact', $params);
    }

    public function sendSticker($chatId, $sticker, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'sticker' => $sticker,
            'disable_notification' => false
        ], $options);

        return $this->request('sendSticker', $params);
    }

    public function sendPoll($chatId, $question, $options, $pollOptions = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => json_encode($options),
            'disable_notification' => false
        ], $pollOptions);

        return $this->request('sendPoll', $params);
    }

    public function editMessageText($chatId, $messageId, $text, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $options);

        return $this->request('editMessageText', $params);
    }

    public function editMessageCaption($chatId, $messageId, $caption, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ], $options);

        return $this->request('editMessageCaption', $params);
    }

    public function editMessageReplyMarkup($chatId, $messageId, $replyMarkup, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => json_encode($replyMarkup)
        ], $options);

        return $this->request('editMessageReplyMarkup', $params);
    }

    public function deleteMessage($chatId, $messageId) {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    }

    public function forwardMessage($chatId, $fromChatId, $messageId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
            'disable_notification' => false
        ], $options);

        return $this->request('forwardMessage', $params);
    }

    public function copyMessage($chatId, $fromChatId, $messageId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
            'disable_notification' => false
        ], $options);

        return $this->request('copyMessage', $params);
    }

    public function answerCallbackQuery($callbackQueryId, $options = []) {
        $params = array_merge([
            'callback_query_id' => $callbackQueryId
        ], $options);

        return $this->request('answerCallbackQuery', $params);
    }

    public function answerCallbackAlert($callbackQueryId, $text, $showAlert = true) {
        return $this->answerCallbackQuery($callbackQueryId, [
            'text' => $text,
            'show_alert' => $showAlert
        ]);
    }

    public function sendChatAction($chatId, $action) {
        return $this->request('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action
        ]);
    }

    public function sendTyping($chatId) {
        return $this->sendChatAction($chatId, 'typing');
    }

    public function sendUploadPhoto($chatId) {
        return $this->sendChatAction($chatId, 'upload_photo');
    }

    public function sendUploadVideo($chatId) {
        return $this->sendChatAction($chatId, 'upload_video');
    }

    public function sendUploadDocument($chatId) {
        return $this->sendChatAction($chatId, 'upload_document');
    }

    public function sendRecordVoice($chatId) {
        return $this->sendChatAction($chatId, 'record_voice');
    }

    public function getChat($chatId) {
        return $this->request('getChat', ['chat_id' => $chatId]);
    }

    public function getChatAdministrators($chatId) {
        return $this->request('getChatAdministrators', ['chat_id' => $chatId]);
    }

    public function getChatMembersCount($chatId) {
        return $this->request('getChatMembersCount', ['chat_id' => $chatId]);
    }

    public function getChatMember($chatId, $userId) {
        return $this->request('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]);
    }

    public function banChatMember($chatId, $userId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId
        ], $options);

        return $this->request('banChatMember', $params);
    }

    public function unbanChatMember($chatId, $userId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => true
        ], $options);

        return $this->request('unbanChatMember', $params);
    }

    public function restrictChatMember($chatId, $userId, $permissions, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => json_encode($permissions)
        ], $options);

        return $this->request('restrictChatMember', $params);
    }

    public function promoteChatMember($chatId, $userId, $permissions, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId
        ], $permissions, $options);

        return $this->request('promoteChatMember', $params);
    }

    public function setChatTitle($chatId, $title) {
        return $this->request('setChatTitle', [
            'chat_id' => $chatId,
            'title' => $title
        ]);
    }

    public function setChatDescription($chatId, $description) {
        return $this->request('setChatDescription', [
            'chat_id' => $chatId,
            'description' => $description
        ]);
    }

    public function setChatPhoto($chatId, $photo) {
        return $this->request('setChatPhoto', [
            'chat_id' => $chatId,
            'photo' => $photo
        ]);
    }

    public function deleteChatPhoto($chatId) {
        return $this->request('deleteChatPhoto', ['chat_id' => $chatId]);
    }

    public function pinChatMessage($chatId, $messageId, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => false
        ], $options);

        return $this->request('pinChatMessage', $params);
    }

    public function unpinChatMessage($chatId, $messageId = null) {
        $params = ['chat_id' => $chatId];
        if ($messageId) {
            $params['message_id'] = $messageId;
        }

        return $this->request('unpinChatMessage', $params);
    }

    public function unpinAllChatMessages($chatId) {
        return $this->request('unpinAllChatMessages', ['chat_id' => $chatId]);
    }

    public function leaveChat($chatId) {
        return $this->request('leaveChat', ['chat_id' => $chatId]);
    }

    public function setWebhook($url, $options = []) {
        $params = array_merge([
            'url' => $url
        ], $options);

        return $this->request('setWebhook', $params);
    }

    public function getWebhookInfo() {
        return $this->request('getWebhookInfo');
    }

    public function deleteWebhook($dropPendingUpdates = false) {
        return $this->request('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates
        ]);
    }

    public function getMe() {
        return $this->request('getMe');
    }

    public function getUpdates($options = []) {
        $params = array_merge([
            'offset' => 0,
            'limit' => 100,
            'timeout' => 0,
            'allowed_updates' => []
        ], $options);

        return $this->request('getUpdates', $params);
    }

    public function getFile($fileId) {
        return $this->request('getFile', ['file_id' => $fileId]);
    }

    public function getFileUrl($fileId) {
        $file = $this->getFile($fileId);

        if ($file && isset($file['file_path'])) {
            return $this->fileBaseUrl . $this->token . '/' . $file['file_path'];
        }

        return false;
    }

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

        Proxy::getInstance()->applyToCurl($ch);

        $success = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $success;
    }

    public function getLastResponse() {
        return $this->lastResponse;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function isSuccess() {
        return $this->lastResponse && isset($this->lastResponse['ok']) && $this->lastResponse['ok'];
    }

    public function sendMessageWithKeyboard($chatId, $text, $keyboard, $options = []) {
        $options['reply_markup'] = json_encode($keyboard);
        return $this->sendMessage($chatId, $text, $options);
    }

    public function sendPhotoWithKeyboard($chatId, $photo, $caption, $keyboard, $options = []) {
        $options['caption'] = $caption;
        $options['reply_markup'] = json_encode($keyboard);
        return $this->sendPhoto($chatId, $photo, $options);
    }

    public function sendToAdmin($text, $options = []) {
        $adminId = Config::getInstance()->get($this->platformName . '.admin_id');
        if (empty($adminId)) {
            $adminId = Config::getInstance()->get('telegram.admin_id');
        }
        return $this->sendMessage($adminId, $text, $options);
    }

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

                if (strpos($this->lastError, 'bot was blocked') !== false) {
                    $results['blocked']++;
                }
            }

            if ($delay > 0) {
                usleep($delay * 1000);
            }
        }

        return $results;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
        $this->apiUrl = $this->getDefaultApiBaseUrl() . $this->token;
        return $this;
    }

    public function getApiUrl() {
        return $this->apiUrl;
    }
}
