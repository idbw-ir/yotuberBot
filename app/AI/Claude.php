<?php
/**
 * ============================================
 * کلاس اتصال به Claude API (Anthropic)
 * ============================================
 * چت با مدل‌های Claude
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

class Claude {
    private $apiKey;
    private $apiUrl = 'https://api.anthropic.com/v1';
    private $model;
    private $maxTokens;
    private $temperature;
    private $topP;
    private $topK;
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
        $this->apiKey = $options['api_key'] ?? $config->ai('claude_api_key');
        $this->model = $options['model'] ?? $config->ai('claude_model', 'claude-3-5-sonnet-20241022');
        $this->maxTokens = $options['max_tokens'] ?? 1024;
        $this->temperature = $options['temperature'] ?? 0.7;
        $this->topP = $options['top_p'] ?? 0.9;
        $this->topK = $options['top_k'] ?? null;
        $this->systemPrompt = $options['system_prompt'] ?? $config->ai('claude_system_prompt', $this->getDefaultSystemPrompt());
        
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API Key تنظیم نشده است');
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
        
        // System Prompt (در Claude جداگانه است)
        $systemContent = $this->systemPrompt;
        
        // افزودن اطلاعات کاربر به system prompt
        if (isset($options['user_info'])) {
            $systemContent .= "\n\nاطلاعات کاربر فعلی:\n";
            $systemContent .= $this->formatUserInfo($options['user_info']);
        }
        
        // پارامترهای درخواست
        $params = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? $this->temperature,
            'top_p' => $options['top_p'] ?? $this->topP,
            'system' => $systemContent
        ];
        
        // افزودن top_k اگر تنظیم شده
        if ($this->topK !== null) {
            $params['top_k'] = $this->topK;
        }
        
        // ارسال درخواست
        $response = $this->sendRequest('/messages', $params);
        
        if (!$response['success']) {
            return $response;
        }
        
        // استخراج پاسخ
        $aiMessage = $response['data']['content'][0]['text'] ?? '';
        $inputTokens = $response['data']['usage']['input_tokens'] ?? 0;
        $outputTokens = $response['data']['usage']['output_tokens'] ?? 0;
        $totalTokens = $inputTokens + $outputTokens;
        
        // ذخیره در تاریخچه
        $this->saveToHistory($userId, $message, $aiMessage);
        
        // لاگ
        $this->logger->info('Claude chat completed', [
            'user_id' => $userId,
            'model' => $this->model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'message_length' => mb_strlen($message),
            'response_length' => mb_strlen($aiMessage)
        ]);
        
        return [
            'success' => true,
            'message' => $aiMessage,
            'tokens_used' => $totalTokens,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'model' => $this->model,
            'stop_reason' => $response['data']['stop_reason'] ?? 'end_turn'
        ];
    }
    
    /**
     * چت ساده (بدون تاریخچه)
     */
    public function simpleChat($message, $systemPrompt = null) {
        $messages = [
            [
                'role' => 'user',
                'content' => $message
            ]
        ];
        
        $params = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'system' => $systemPrompt ?? $this->systemPrompt
        ];
        
        $response = $this->sendRequest('/messages', $params);
        
        if (!$response['success']) {
            return $response;
        }
        
        return [
            'success' => true,
            'message' => $response['data']['content'][0]['text'] ?? '',
            'tokens_used' => ($response['data']['usage']['input_tokens'] ?? 0) + 
                            ($response['data']['usage']['output_tokens'] ?? 0)
        ];
    }
    
    // ══════════════════════════════════════
    // ساخت پیام‌ها
    // ══════════════════════════════════════
    
    /**
     * ساخت آرایه پیام‌ها با تاریخچه
     */
    private function buildMessages($userId, $currentMessage, array $options) {
        $messages = [];
        
        // 1. تاریخچه چت
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
        
        // 2. پیام فعلی
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
                 AND message_type IN ('text', 'ai', 'claude')
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
                'message_type' => 'claude'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save Claude response', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // ══════════════════════════════════════
    // ارسال درخواست به API
    // ══════════════════════════════════════
    
    /**
     * ارسال درخواست به Claude API
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
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $this->logger->error('Claude cURL Error', [
                'endpoint' => $endpoint,
                'error' => $curlError
            ]);
            
            return [
                'success' => false,
                'error' => 'خطا در اتصال به Claude: ' . $curlError,
                'code' => 500
            ];
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $errorMessage = $data['error']['message'] ?? 'Unknown error';
            $errorType = $data['error']['type'] ?? 'unknown';
            
            $this->logger->error('Claude API Error', [
                'endpoint' => $endpoint,
                'http_code' => $httpCode,
                'error_type' => $errorType,
                'error' => $errorMessage,
                'params' => $params
            ]);
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'error_type' => $errorType,
                'code' => $httpCode
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
        $cacheKey = "claude_rate_limit_{$userId}";
        
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
        // قیمت‌های Claude (به ازای 1M توکن)
        $prices = [
            'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00],
            'claude-3-opus-20240229' => ['input' => 15.00, 'output' => 75.00],
            'claude-3-sonnet-20240229' => ['input' => 3.00, 'output' => 15.00],
            'claude-3-haiku-20240307' => ['input' => 0.25, 'output' => 1.25]
        ];
        
        $modelPrices = $prices[$this->model] ?? $prices['claude-3-5-sonnet-20241022'];
        
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
        $this->temperature = max(0, min(1, (float)$temperature));
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
     * تنظیم Top P
     */
    public function setTopP($topP) {
        $this->topP = max(0, min(1, (float)$topP));
        return $this;
    }
    
    /**
     * تنظیم Top K
     */
    public function setTopK($topK) {
        $this->topK = $topK !== null ? max(1, (int)$topK) : null;
        return $this;
    }
    
    /**
     * دریافت مدل فعلی
     */
    public function getModel() {
        return $this->model;
    }
    
    // ══════════════════════════════════════
    // آمار استفاده
    // ══════════════════════════════════════
    
    /**
     * دریافت آمار استفاده از Claude
     */
    public function getUsageStats($days = 30) {
        try {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as request_count,
                        SUM(CHAR_LENGTH(text)) as total_chars
                    FROM messages
                    WHERE message_type = 'claude'
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
    
    // ══════════════════════════════════════
    // مقایسه با OpenAI
    // ══════════════════════════════════════
    
    /**
     * دریافت مقایسه هزینه با OpenAI
     */
    public function compareWithOpenAI($inputTokens, $outputTokens) {
        $claudeCost = $this->estimateCost($inputTokens, $outputTokens);
        
        // هزینه OpenAI (GPT-4o-mini)
        $openaiInputCost = ($inputTokens / 1000000) * 0.15;
        $openaiOutputCost = ($outputTokens / 1000000) * 0.60;
        
        return [
            'claude' => $claudeCost,
            'openai_gpt4o_mini' => [
                'input_cost' => $openaiInputCost,
                'output_cost' => $openaiOutputCost,
                'total_cost' => $openaiInputCost + $openaiOutputCost,
                'currency' => 'USD'
            ],
            'difference' => $claudeCost['total_cost'] - ($openaiInputCost + $openaiOutputCost)
        ];
    }
}