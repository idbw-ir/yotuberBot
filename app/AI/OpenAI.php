<?php
/**
 * ============================================
 * کلاس اتصال به OpenAI API
 * ============================================
 * چت با مدل‌های GPT
 * مدیریت تاریخچه چت
 * System Prompt سفارشی
 * Token Counting
 * Rate Limiting
 * Error Handling
 * پشتیبانی از Context (اطلاعات کاربر)
 */

namespace App\AI;

use App\Core\Config;
use App\Core\Cache;
use App\Core\Logger;
use App\Core\Database;

class OpenAI {
    private $apiKey;
    private $apiUrl = 'https://api.openai.com/v1';
    private $model;
    private $maxTokens;
    private $temperature;
    private $topP;
    private $frequencyPenalty;
    private $presencePenalty;
    private $systemPrompt;
    private $logger;
    private $cache;
    private $db;
    
    // Rate Limiting
    private $rateLimitKey;
    private $maxRequestsPerMinute = 20;
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct(array $options = []) {
        $config = Config::getInstance();
        $this->logger = Logger::getInstance();
        $this->cache = Cache::getInstance();
        $this->db = Database::getInstance();
        
        // تنظیمات از config یا options
        $this->apiKey = $options['api_key'] ?? $config->ai('api_key');
        $this->model = $options['model'] ?? $config->ai('model', 'gpt-4o-mini');
        $this->maxTokens = $options['max_tokens'] ?? 500;
        $this->temperature = $options['temperature'] ?? 0.7;
        $this->topP = $options['top_p'] ?? 1.0;
        $this->frequencyPenalty = $options['frequency_penalty'] ?? 0.0;
        $this->presencePenalty = $options['presence_penalty'] ?? 0.0;
        $this->systemPrompt = $options['system_prompt'] ?? $config->ai('system_prompt', $this->getDefaultSystemPrompt());
        
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API Key تنظیم نشده است');
        }
    }
    
    // ──────────────────────────────────────
    // System Prompt پیش‌فرض
    // ──────────────────────────────────────
    private function getDefaultSystemPrompt() {
        return "تو دستیار یک یوتیوبر فارسی‌زبان هستی. وظایف تو:
1. پاسخ به سوالات کاربران درباره کانال یوتیوب
2. راهنمایی درباره حمایت مالی و عضویت VIP
3. ارائه اطلاعات درباره ویدئوها و محتوای کانال
4. پاسخ دوستانه و کوتاه به سوالات عمومی

قوانین:
- همیشه به فارسی پاسخ بده
- کوتاه و مفید جواب بده (حداکثر 3-4 جمله)
- از ایموجی استفاده کن تا پاسخ صمیمی‌تر باشه
- اگه سوالی خارج از حیطه کاریت بود، مؤدبانه بگو که نمی‌تونی کمک کنی
- اطلاعات شخصی کاربران رو فاش نکن
- اگه کاربر درباره دونیت پرسید، لینک حمایت رو بده";
    }
    
    // ══════════════════════════════════════
    // چت اصلی
    // ══════════════════════════════════════
    
    /**
     * چت با کاربر (با تاریخچه و context)
     */
    public function chat($userId, $message, array $options = []) {
        // Rate Limiting
        if (!$this->checkRateLimit($userId)) {
            return [
                'success' => false,
                'error' => 'محدودیت نرخ درخواست. لطفاً چند دقیقه صبر کنید.',
                'code' => 429
            ];
        }
        
        // ساخت پیام‌ها
        $messages = $this->buildMessages($userId, $message, $options);
        
        // پارامترهای درخواست
        $params = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? $this->temperature,
            'top_p' => $options['top_p'] ?? $this->topP,
            'frequency_penalty' => $options['frequency_penalty'] ?? $this->frequencyPenalty,
            'presence_penalty' => $options['presence_penalty'] ?? $this->presencePenalty,
            'user' => (string)$userId // برای Rate Limiting سمت OpenAI
        ];
        
        // ارسال درخواست
        $response = $this->sendRequest('/chat/completions', $params);
        
        if (!$response['success']) {
            return $response;
        }
        
        // استخراج پاسخ
        $aiMessage = $response['data']['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $response['data']['usage']['total_tokens'] ?? 0;
        
        // ذخیره در تاریخچه
        $this->saveToHistory($userId, $message, $aiMessage);
        
        // لاگ
        $this->logger->info('OpenAI chat completed', [
            'user_id' => $userId,
            'model' => $this->model,
            'tokens_used' => $tokensUsed,
            'message_length' => mb_strlen($message),
            'response_length' => mb_strlen($aiMessage)
        ]);
        
        return [
            'success' => true,
            'message' => $aiMessage,
            'tokens_used' => $tokensUsed,
            'model' => $this->model,
            'finish_reason' => $response['data']['choices'][0]['finish_reason'] ?? 'stop'
        ];
    }
    
    /**
     * چت ساده (بدون تاریخچه)
     */
    public function simpleChat($message, $systemPrompt = null) {
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt ?? $this->systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ];
        
        $params = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature
        ];
        
        $response = $this->sendRequest('/chat/completions', $params);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'message' => $response['data']['choices'][0]['message']['content'] ?? '',
            'tokens_used' => $response['data']['usage']['total_tokens'] ?? 0
        ];
    }
    
    // ══════════════════════════════════════
    // ساخت پیام‌ها
    // ══════════════════════════════════════
    
    /**
     * ساخت آرایه پیام‌ها با تاریخچه و context
     */
    private function buildMessages($userId, $currentMessage, array $options) {
        $messages = [];
        
        // 1. System Prompt
        $systemContent = $this->systemPrompt;
        
        // افزودن اطلاعات کاربر به system prompt
        if (isset($options['user_info'])) {
            $systemContent .= "\n\nاطلاعات کاربر فعلی:\n";
            $systemContent .= $this->formatUserInfo($options['user_info']);
        }
        
        $messages[] = [
            'role' => 'system',
            'content' => $systemContent
        ];
        
        // 2. تاریخچه چت
        if (isset($options['history']) && is_array($options['history'])) {
            foreach ($options['history'] as $msg) {
                $messages[] = [
                    'role' => $msg['direction'] === 'in' ? 'user' : 'assistant',
                    'content' => $msg['text']
                ];
            }
        } else {
            // دریافت تاریخچه از دیتابیس
            $history = $this->getChatHistory($userId, 10);
            foreach ($history as $msg) {
                $messages[] = [
                    'role' => $msg['direction'] === 'in' ? 'user' : 'assistant',
                    'content' => $msg['text']
                ];
            }
        }
        
        // 3. پیام فعلی
        $messages[] = [
            'role' => 'user',
            'content' => $currentMessage
        ];
        
        return $messages;
    }
    
    /**
     * فرمت اطلاعات کاربر برای System Prompt
     */
    private function formatUserInfo($userInfo) {
        $info = [];
        
        if (!empty($userInfo['name'])) {
            $info[] = "- نام: {$userInfo['name']}";
        }
        
        if (isset($userInfo['is_vip'])) {
            $info[] = "- وضعیت VIP: " . ($userInfo['is_vip'] ? 'بله 👑' : 'خیر');
        }
        
        if (isset($userInfo['total_donations'])) {
            $info[] = "- مجموع دونیت: " . number_format($userInfo['total_donations']) . " تومان";
        }
        
        if (isset($userInfo['donation_count'])) {
            $info[] = "- تعداد دونیت: {$userInfo['donation_count']}";
        }
        
        if (isset($userInfo['joined_at'])) {
            $info[] = "- تاریخ عضویت: {$userInfo['joined_at']}";
        }
        
        return implode("\n", $info);
    }
    
    /**
     * دریافت تاریخچه چت از دیتابیس
     */
    private function getChatHistory($userId, $limit = 10) {
        try {
            $messages = $this->db->fetchAll(
                "SELECT text, direction FROM messages 
                 WHERE user_id = ? 
                 AND message_type IN ('text', 'ai')
                 ORDER BY id DESC 
                 LIMIT ?",
                [$userId, $limit]
            );
            
            return array_reverse($messages);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get chat history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * ذخیره در تاریخچه
     */
    private function saveToHistory($userId, $userMessage, $aiMessage) {
        try {
            // پیام کاربر قبلاً ذخیره شده (در Webhook)
            // فقط پاسخ AI رو ذخیره می‌کنیم
            $this->db->insert('messages', [
                'user_id' => $userId,
                'text' => $aiMessage,
                'direction' => 'out',
                'message_type' => 'ai'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save AI response', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // ══════════════════════════════════════
    // تکمیل متن (Completion)
    // ══════════════════════════════════════
    
    /**
     * تکمیل متن (برای مدل‌های قدیمی)
     */
    public function completion($prompt, array $options = []) {
        $params = [
            'model' => $options['model'] ?? $this->model,
            'prompt' => $prompt,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? $this->temperature,
            'top_p' => $options['top_p'] ?? $this->topP
        ];
        
        $response = $this->sendRequest('/completions', $params);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'text' => $response['data']['choices'][0]['text'] ?? '',
            'tokens_used' => $response['data']['usage']['total_tokens'] ?? 0
        ];
    }
    
    // ══════════════════════════════════════
    // Embeddings
    // ══════════════════════════════════════
    
    /**
     * تولید Embedding برای متن
     */
    public function embedding($text, $model = 'text-embedding-3-small') {
        $params = [
            'model' => $model,
            'input' => $text
        ];
        
        $response = $this->sendRequest('/embeddings', $params);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'embedding' => $response['data']['data'][0]['embedding'] ?? [],
            'tokens_used' => $response['data']['usage']['total_tokens'] ?? 0
        ];
    }
    
    // ══════════════════════════════════════
    // ارسال درخواست به API
    // ══════════════════════════════════════
    
    /**
     * ارسال درخواست به OpenAI API
     */
    private function sendRequest($endpoint, array $params) {
        $url = $this->apiUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $this->logger->error('OpenAI cURL Error', [
                'endpoint' => $endpoint,
                'error' => $curlError
            ]);
            
            return [
                'success' => false,
                'error' => 'خطا در اتصال به OpenAI: ' . $curlError,
                'code' => 500
            ];
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $errorMessage = $data['error']['message'] ?? 'Unknown error';
            $errorCode = $data['error']['code'] ?? $httpCode;
            
            $this->logger->error('OpenAI API Error', [
                'endpoint' => $endpoint,
                'http_code' => $httpCode,
                'error' => $errorMessage,
                'params' => $params
            ]);
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'code' => $errorCode
            ];
        }
        
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    // ══════════════════════════════════════
    // Rate Limiting
    // ══════════════════════════════════════
    
    /**
     * بررسی Rate Limit
     */
    private function checkRateLimit($userId) {
        $cacheKey = "openai_rate_limit_{$userId}";
        
        $data = $this->cache->get($cacheKey, ['count' => 0, 'first_request' => time()]);
        
        // اگر یک دقیقه گذشته، ریست کن
        if (time() - $data['first_request'] > 60) {
            $data = ['count' => 0, 'first_request' => time()];
        }
        
        $data['count']++;
        
        // ذخیره در کش
        $this->cache->set($cacheKey, $data, 60);
        
        return $data['count'] <= $this->maxRequestsPerMinute;
    }
    
    /**
     * تنظیم حداکثر درخواست در دقیقه
     */
    public function setMaxRequestsPerMinute($limit) {
        $this->maxRequestsPerMinute = max(1, (int)$limit);
        return $this;
    }
    
    // ══════════════════════════════════════
    // Token Counting
    // ══════════════════════════════════════
    
    /**
     * تخمین تعداد توکن‌ها (تقریبی)
     */
    public function estimateTokens($text) {
        // تقریب: هر 4 کاراکتر = 1 توکن (برای فارسی کمی بیشتر)
        $charCount = mb_strlen($text);
        return (int)($charCount / 3); // برای فارسی
    }
    
    /**
     * محاسبه هزینه تقریبی
     */
    public function estimateCost($inputTokens, $outputTokens) {
        // قیمت‌های GPT-4o-mini (به ازای 1M توکن)
        $prices = [
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
            'gpt-4o' => ['input' => 5.00, 'output' => 15.00],
            'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
            'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50]
        ];
        
        $modelPrices = $prices[$this->model] ?? $prices['gpt-4o-mini'];
        
        $inputCost = ($inputTokens / 1000000) * $modelPrices['input'];
        $outputCost = ($outputTokens / 1000000) * $modelPrices['output'];
        
        return [
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $inputCost + $outputCost,
            'currency' => 'USD'
        ];
    }
    
    // ══════════════════════════════════════
    // تنظیمات
    // ══════════════════════════════════════
    
    /**
     * تنظیم مدل
     */
    public function setModel($model) {
        $this->model = $model;
        return $this;
    }
    
    /**
     * تنظیم System Prompt
     */
    public function setSystemPrompt($prompt) {
        $this->systemPrompt = $prompt;
        return $this;
    }
    
    /**
     * تنظیم Temperature
     */
    public function setTemperature($temperature) {
        $this->temperature = max(0, min(2, (float)$temperature));
        return $this;
    }
    
    /**
     * تنظیم Max Tokens
     */
    public function setMaxTokens($maxTokens) {
        $this->maxTokens = max(1, (int)$maxTokens);
        return $this;
    }
    
    /**
     * دریافت مدل فعلی
     */
    public function getModel() {
        return $this->model;
    }
    
    /**
     * دریافت لیست مدل‌های موجود
     */
    public function listModels() {
        $response = $this->sendRequest('/models', []);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'models' => $response['data']['data'] ?? []
        ];
    }
    
    // ══════════════════════════════════════
    // آمار استفاده
    // ══════════════════════════════════════
    
    /**
     * دریافت آمار استفاده از AI
     */
    public function getUsageStats($days = 30) {
        try {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as request_count,
                        SUM(CHAR_LENGTH(text)) as total_chars
                    FROM messages
                    WHERE message_type = 'ai'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $stats = $this->db->fetchAll($sql, [$days]);
            
            return [
                'success' => true,
                'stats' => $stats,
                'total_requests' => array_sum(array_column($stats, 'request_count')),
                'total_chars' => array_sum(array_column($stats, 'total_chars'))
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}