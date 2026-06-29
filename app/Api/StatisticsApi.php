<?php
/**
 * ============================================
 * کلاس API آمار (Statistics API)
 * ============================================
 * ارائه آمار از طریق REST API
 * احراز هویت با API Token
 * Rate Limiting
 * Cache
 * فیلترهای زمانی
 * فرمت JSON
 * CORS Support
 */

namespace App\Api;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Core\Config;
use App\Helpers\Security;

class StatisticsApi {
    private $db;
    private $cache;
    private $logger;
    private $config;
    
    // احراز هویت
    private $apiToken;
    private $authenticated = false;
    
    // Rate Limiting
    private $rateLimitKey;
    private $maxRequestsPerMinute = 60;
    
    // Response
    private $responseCode = 200;
    private $responseData = [];
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        
        // تنظیم CORS
        $this->setCorsHeaders();
        
        // دریافت API Token
        $this->apiToken = $this->getBearerToken();
    }
    
    // ══════════════════════════════════════
    // CORS Headers
    // ══════════════════════════════════════
    
    /**
     * تنظیم CORS Headers
     */
    private function setCorsHeaders() {
        $allowedOrigins = $this->config->get('api_allowed_origins', ['*']);
        
        if (in_array('*', $allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
        } else {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if (in_array($origin, $allowedOrigins)) {
                header("Access-Control-Allow-Origin: {$origin}");
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Access-Control-Max-Age: 86400');
        
        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    // ══════════════════════════════════════
    // احراز هویت
    // ══════════════════════════════════════
    
    /**
     * دریافت Bearer Token
     */
    private function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        // بررسی X-API-Key header
        return $_SERVER['HTTP_X_API_KEY'] ?? null;
    }
    
    /**
     * دریافت Authorization Header
     */
    private function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    /**
     * احراز هویت
     */
    public function authenticate() {
        if (empty($this->apiToken)) {
            $this->sendError(401, 'API Token الزامی است');
            return false;
        }
        
        // بررسی Token در دیتابیس
        $tokenHash = hash('sha256', $this->apiToken);
        
        $token = $this->db->fetch(
            "SELECT * FROM api_tokens WHERE token_hash = ? AND active = 1 AND (expires_at IS NULL OR expires_at > NOW())",
            [$tokenHash]
        );
        
        if (!$token) {
            $this->logger->security('Invalid API token attempt', [
                'ip' => Security::getClientIp(),
                'token_prefix' => substr($this->apiToken, 0, 10) . '...'
            ]);
            
            $this->sendError(401, 'API Token نامعتبر یا منقضی شده است');
            return false;
        }
        
        // بررسی Rate Limit
        if (!$this->checkRateLimit($token['id'])) {
            $this->sendError(429, 'محدودیت نرخ درخواست. لطفاً بعداً تلاش کنید.');
            return false;
        }
        
        // بروزرسانی آخرین استفاده
        $this->db->update('api_tokens', [
            'last_used_at' => date('Y-m-d H:i:s'),
            'last_used_ip' => Security::getClientIp()
        ], 'id = ?', [$token['id']]);
        
        $this->authenticated = true;
        
        $this->logger->info('API authentication successful', [
            'token_id' => $token['id'],
            'ip' => Security::getClientIp()
        ]);
        
        return true;
    }
    
    // ══════════════════════════════════════
    // Rate Limiting
    // ══════════════════════════════════════
    
    /**
     * بررسی Rate Limit
     */
    private function checkRateLimit($tokenId) {
        $cacheKey = "api_rate_limit_{$tokenId}";
        
        $data = $this->cache->get($cacheKey, ['count' => 0, 'first_request' => time()]);
        
        // اگر یک دقیقه گذشته، ریست کن
        if (time() - $data['first_request'] > 60) {
            $data = ['count' => 0, 'first_request' => time()];
        }
        
        $data['count']++;
        
        // ذخیره در کش
        $this->cache->set($cacheKey, $data, 60);
        
        // تنظیم Headers
        header('X-RateLimit-Limit: ' . $this->maxRequestsPerMinute);
        header('X-RateLimit-Remaining: ' . max(0, $this->maxRequestsPerMinute - $data['count']));
        header('X-RateLimit-Reset: ' . ($data['first_request'] + 60));
        
        return $data['count'] <= $this->maxRequestsPerMinute;
    }
    
    // ══════════════════════════════════════
    // Router
    // ══════════════════════════════════════
    
    /**
     * مسیریابی درخواست
     */
    public function route($endpoint) {
        // احراز هویت
        if (!$this->authenticate()) {
            return;
        }
        
        // Parse endpoint
        $parts = explode('/', trim($endpoint, '/'));
        $resource = $parts[0] ?? '';
        $action = $parts[1] ?? 'index';
        
        // Routing
        switch ($resource) {
            case 'overview':
                $this->getOverview();
                break;
                
            case 'users':
                $this->handleUsers($action);
                break;
                
            case 'donations':
                $this->handleDonations($action);
                break;
                
            case 'messages':
                $this->handleMessages($action);
                break;
                
            case 'keywords':
                $this->handleKeywords($action);
                break;
                
            case 'charts':
                $this->handleCharts($action);
                break;
                
            case 'kpis':
                $this->getKPIs();
                break;
                
            case 'top':
                $this->handleTop($action);
                break;
                
            default:
                $this->sendError(404, 'Endpoint یافت نشد');
        }
    }
    
    // ══════════════════════════════════════
    // Overview
    // ══════════════════════════════════════
    
    /**
     * آمار کلی
     */
    private function getOverview() {
        $cacheKey = 'api_overview';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            return [
                'users' => [
                    'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users"),
                    'vip' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_vip = 1"),
                    'blocked' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE blocked = 1"),
                    'active_today' => (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM users WHERE DATE(last_seen) = CURDATE()"
                    ),
                    'new_today' => (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM users WHERE DATE(joined_at) = CURDATE()"
                    )
                ],
                'donations' => [
                    'total_amount' => (int)$this->db->fetchColumn(
                        "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success'"
                    ),
                    'total_count' => (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM donations WHERE status = 'success'"
                    ),
                    'today_amount' => (int)$this->db->fetchColumn(
                        "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND DATE(created_at) = CURDATE()"
                    ),
                    'today_count' => (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM donations WHERE status = 'success' AND DATE(created_at) = CURDATE()"
                    )
                ],
                'messages' => [
                    'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM messages"),
                    'today' => (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()"
                    ),
                    'unread' => (int)$this->db->fetchColumn(
                        "SELECT COUNT(*) FROM messages WHERE direction = 'in' AND is_read = 0"
                    )
                ],
                'keywords' => [
                    'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords"),
                    'active' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 1")
                ],
                'generated_at' => date('Y-m-d H:i:s')
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Users
    // ══════════════════════════════════════
    
    /**
     * مدیریت Users endpoint
     */
    private function handleUsers($action) {
        switch ($action) {
            case 'index':
            case 'list':
                $this->getUsersList();
                break;
                
            case 'stats':
                $this->getUserStats();
                break;
                
            case 'growth':
                $this->getUserGrowth();
                break;
                
            default:
                $this->sendError(404, 'Action یافت نشد');
        }
    }
    
    /**
     * لیست کاربران
     */
    private function getUsersList() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        
        $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users");
        
        $users = $this->db->fetchAll(
            "SELECT id, first_name, last_name, username, is_vip, blocked, joined_at, last_seen
             FROM users
             ORDER BY joined_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        
        $this->sendSuccess([
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * آمار کاربران
     */
    private function getUserStats() {
        $cacheKey = 'api_user_stats';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            return [
                'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users"),
                'vip' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_vip = 1"),
                'blocked' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE blocked = 1"),
                'active_today' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE DATE(last_seen) = CURDATE()"
                ),
                'active_week' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
                ),
                'active_month' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                ),
                'new_today' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE DATE(joined_at) = CURDATE()"
                ),
                'new_week' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
                ),
                'new_month' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                )
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * رشد کاربران
     */
    private function getUserGrowth() {
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_user_growth_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($days) {
            $growth = $this->db->fetchAll(
                "SELECT 
                    DATE(joined_at) as date,
                    COUNT(*) as count
                FROM users
                WHERE joined_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(joined_at)
                ORDER BY date ASC",
                [$days]
            );
            
            return [
                'period_days' => $days,
                'data' => $growth,
                'total_new_users' => array_sum(array_column($growth, 'count'))
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Donations
    // ══════════════════════════════════════
    
    /**
     * مدیریت Donations endpoint
     */
    private function handleDonations($action) {
        switch ($action) {
            case 'index':
            case 'list':
                $this->getDonationsList();
                break;
                
            case 'stats':
                $this->getDonationStats();
                break;
                
            case 'revenue':
                $this->getRevenueData();
                break;
                
            default:
                $this->sendError(404, 'Action یافت نشد');
        }
    }
    
    /**
     * لیست دونیت‌ها
     */
    private function getDonationsList() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        
        $status = $_GET['status'] ?? null;
        
        $where = ['1=1'];
        $params = [];
        
        if ($status && in_array($status, ['pending', 'success', 'failed'])) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        $whereStr = implode(' AND ', $where);
        
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM donations WHERE {$whereStr}",
            $params
        );
        
        $donations = $this->db->fetchAll(
            "SELECT d.*, u.first_name, u.last_name, u.username
             FROM donations d
             LEFT JOIN users u ON d.user_id = u.id
             WHERE {$whereStr}
             ORDER BY d.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->sendSuccess([
            'donations' => $donations,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * آمار دونیت‌ها
     */
    private function getDonationStats() {
        $cacheKey = 'api_donation_stats';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            return [
                'total_amount' => (int)$this->db->fetchColumn(
                    "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success'"
                ),
                'total_count' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE status = 'success'"
                ),
                'today_amount' => (int)$this->db->fetchColumn(
                    "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND DATE(created_at) = CURDATE()"
                ),
                'today_count' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE status = 'success' AND DATE(created_at) = CURDATE()"
                ),
                'month_amount' => (int)$this->db->fetchColumn(
                    "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                ),
                'month_count' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                ),
                'average' => (int)$this->db->fetchColumn(
                    "SELECT COALESCE(AVG(amount), 0) FROM donations WHERE status = 'success'"
                ),
                'max' => (int)$this->db->fetchColumn(
                    "SELECT COALESCE(MAX(amount), 0) FROM donations WHERE status = 'success'"
                ),
                'donors_count' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(DISTINCT user_id) FROM donations WHERE status = 'success'"
                ),
                'pending_count' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE status = 'pending'"
                ),
                'failed_count' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE status = 'failed'"
                )
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * داده‌های درآمد
     */
    private function getRevenueData() {
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_revenue_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($days) {
            $revenue = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(amount) as total_amount,
                    COUNT(*) as count,
                    AVG(amount) as average
                FROM donations
                WHERE status = 'success'
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                [$days]
            );
            
            return [
                'period_days' => $days,
                'data' => $revenue,
                'total_revenue' => array_sum(array_column($revenue, 'total_amount')),
                'total_donations' => array_sum(array_column($revenue, 'count'))
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Messages
    // ══════════════════════════════════════
    
    /**
     * مدیریت Messages endpoint
     */
    private function handleMessages($action) {
        switch ($action) {
            case 'index':
            case 'list':
                $this->getMessagesList();
                break;
                
            case 'stats':
                $this->getMessageStats();
                break;
                
            case 'activity':
                $this->getMessageActivity();
                break;
                
            default:
                $this->sendError(404, 'Action یافت نشد');
        }
    }
    
    /**
     * لیست پیام‌ها
     */
    private function getMessagesList() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        
        $direction = $_GET['direction'] ?? null;
        
        $where = ['1=1'];
        $params = [];
        
        if ($direction && in_array($direction, ['in', 'out'])) {
            $where[] = 'direction = ?';
            $params[] = $direction;
        }
        
        $whereStr = implode(' AND ', $where);
        
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE {$whereStr}",
            $params
        );
        
        $messages = $this->db->fetchAll(
            "SELECT m.*, u.first_name, u.last_name, u.username
             FROM messages m
             LEFT JOIN users u ON m.user_id = u.id
             WHERE {$whereStr}
             ORDER BY m.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->sendSuccess([
            'messages' => $messages,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * آمار پیام‌ها
     */
    private function getMessageStats() {
        $cacheKey = 'api_message_stats';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            return [
                'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM messages"),
                'incoming' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = 'in'"),
                'outgoing' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = 'out'"),
                'today' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()"
                ),
                'unread' => (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM messages WHERE direction = 'in' AND is_read = 0"
                ),
                'avg_per_user' => (float)$this->db->fetchColumn(
                    "SELECT AVG(msg_count) FROM (SELECT user_id, COUNT(*) as msg_count FROM messages WHERE direction = 'in' GROUP BY user_id) t"
                )
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * فعالیت پیام‌ها
     */
    private function getMessageActivity() {
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_message_activity_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($days) {
            $activity = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    direction,
                    COUNT(*) as count
                FROM messages
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), direction
                ORDER BY date ASC",
                [$days]
            );
            
            return [
                'period_days' => $days,
                'data' => $activity
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Keywords
    // ══════════════════════════════════════
    
    /**
     * مدیریت Keywords endpoint
     */
    private function handleKeywords($action) {
        switch ($action) {
            case 'index':
            case 'list':
                $this->getKeywordsList();
                break;
                
            case 'stats':
                $this->getKeywordStats();
                break;
                
            default:
                $this->sendError(404, 'Action یافت نشد');
        }
    }
    
    /**
     * لیست کلمات کلیدی
     */
    private function getKeywordsList() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        
        $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords");
        
        $keywords = $this->db->fetchAll(
            "SELECT * FROM keywords ORDER BY priority DESC, id DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        
        $this->sendSuccess([
            'keywords' => $keywords,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }
    
    /**
     * آمار کلمات کلیدی
     */
    private function getKeywordStats() {
        $cacheKey = 'api_keyword_stats';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            return [
                'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords"),
                'active' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 1"),
                'inactive' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 0"),
                'total_matches' => (int)$this->db->fetchColumn(
                    "SELECT COALESCE(COUNT(*), 0) FROM keyword_matches"
                )
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Charts
    // ══════════════════════════════════════
    
    /**
     * مدیریت Charts endpoint
     */
    private function handleCharts($action) {
        switch ($action) {
            case 'users':
                $this->getUserChart();
                break;
                
            case 'revenue':
                $this->getRevenueChart();
                break;
                
            case 'messages':
                $this->getMessageChart();
                break;
                
            case 'user-status':
                $this->getUserStatusChart();
                break;
                
            default:
                $this->sendError(404, 'Chart type یافت نشد');
        }
    }
    
    /**
     * نمودار کاربران
     */
    private function getUserChart() {
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_chart_users_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($days) {
            $growth = $this->db->fetchAll(
                "SELECT 
                    DATE(joined_at) as date,
                    COUNT(*) as count
                FROM users
                WHERE joined_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(joined_at)
                ORDER BY date ASC",
                [$days]
            );
            
            return [
                'type' => 'line',
                'labels' => array_column($growth, 'date'),
                'datasets' => [
                    [
                        'label' => 'کاربران جدید',
                        'data' => array_column($growth, 'count'),
                        'borderColor' => '#8b5cf6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'tension' => 0.4
                    ]
                ]
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * نمودار درآمد
     */
    private function getRevenueChart() {
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_chart_revenue_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($days) {
            $revenue = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(amount) as total_amount,
                    COUNT(*) as count
                FROM donations
                WHERE status = 'success'
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC",
                [$days]
            );
            
            return [
                'type' => 'bar',
                'labels' => array_column($revenue, 'date'),
                'datasets' => [
                    [
                        'label' => 'مبلغ دونیت (تومان)',
                        'data' => array_column($revenue, 'total_amount'),
                        'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                        'borderColor' => '#10b981'
                    ]
                ]
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * نمودار پیام‌ها
     */
    private function getMessageChart() {
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_chart_messages_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($days) {
            $activity = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    direction,
                    COUNT(*) as count
                FROM messages
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), direction
                ORDER BY date ASC",
                [$days]
            );
            
            $incoming = [];
            $outgoing = [];
            
            foreach ($activity as $row) {
                if ($row['direction'] === 'in') {
                    $incoming[$row['date']] = $row['count'];
                } else {
                    $outgoing[$row['date']] = $row['count'];
                }
            }
            
            $dates = array_unique(array_column($activity, 'date'));
            
            return [
                'type' => 'line',
                'labels' => array_values($dates),
                'datasets' => [
                    [
                        'label' => 'پیام‌های دریافتی',
                        'data' => array_map(function($d) use ($incoming) {
                            return $incoming[$d] ?? 0;
                        }, $dates),
                        'borderColor' => '#10b981'
                    ],
                    [
                        'label' => 'پیام‌های ارسالی',
                        'data' => array_map(function($d) use ($outgoing) {
                            return $outgoing[$d] ?? 0;
                        }, $dates),
                        'borderColor' => '#f59e0b'
                    ]
                ]
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * نمودار وضعیت کاربران
     */
    private function getUserStatusChart() {
        $cacheKey = 'api_chart_user_status';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users");
            $vip = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_vip = 1 AND blocked = 0");
            $blocked = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE blocked = 1");
            $active = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE is_vip = 0 AND blocked = 0 AND last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $inactive = max(0, $total - $vip - $blocked - $active);
            
            return [
                'type' => 'doughnut',
                'labels' => ['VIP', 'فعال', 'غیرفعال', 'بلاک شده'],
                'datasets' => [
                    [
                        'data' => [$vip, $active, $inactive, $blocked],
                        'backgroundColor' => ['#fbbf24', '#10b981', '#6b7280', '#ef4444']
                    ]
                ]
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // KPIs
    // ══════════════════════════════════════
    
    /**
     * شاخص‌های کلیدی عملکرد
     */
    private function getKPIs() {
        $cacheKey = 'api_kpis';
        
        $data = $this->cache->remember($cacheKey, 300, function() {
            $totalUsers = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users");
            $donorsCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(DISTINCT user_id) FROM donations WHERE status = 'success'"
            );
            $totalRevenue = (int)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success'"
            );
            $activeWeek = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $vipCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_vip = 1");
            $blockedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE blocked = 1");
            $incoming = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = 'in'");
            $outgoing = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE direction = 'out'");
            
            return [
                'conversion_rate' => $totalUsers > 0 ? round(($donorsCount / $totalUsers) * 100, 2) : 0,
                'arpu' => $totalUsers > 0 ? round($totalRevenue / $totalUsers) : 0,
                'arppu' => $donorsCount > 0 ? round($totalRevenue / $donorsCount) : 0,
                'engagement_rate' => $totalUsers > 0 ? round(($activeWeek / $totalUsers) * 100, 2) : 0,
                'response_rate' => $incoming > 0 ? round(($outgoing / $incoming) * 100, 2) : 0,
                'vip_rate' => $totalUsers > 0 ? round(($vipCount / $totalUsers) * 100, 2) : 0,
                'block_rate' => $totalUsers > 0 ? round(($blockedCount / $totalUsers) * 100, 2) : 0
            ];
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Top Lists
    // ══════════════════════════════════════
    
    /**
     * مدیریت Top endpoint
     */
    private function handleTop($action) {
        switch ($action) {
            case 'donors':
                $this->getTopDonors();
                break;
                
            case 'users':
                $this->getTopUsers();
                break;
                
            case 'keywords':
                $this->getTopKeywords();
                break;
                
            default:
                $this->sendError(404, 'Top type یافت نشد');
        }
    }
    
    /**
     * برترین حامیان
     */
    private function getTopDonors() {
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
        $days = isset($_GET['days']) ? (int)$_GET['days'] : null;
        
        $cacheKey = "api_top_donors_{$limit}_" . ($days ?? 'all');
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($limit, $days) {
            $where = "d.status = 'success'";
            $params = [];
            
            if ($days) {
                $where .= " AND d.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
                $params[] = $days;
            }
            
            $donors = $this->db->fetchAll(
                "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.username,
                    COUNT(d.id) as donation_count,
                    SUM(d.amount) as total_amount
                FROM users u
                INNER JOIN donations d ON u.id = d.user_id
                WHERE {$where}
                GROUP BY u.id
                ORDER BY total_amount DESC
                LIMIT ?",
                array_merge($params, [$limit])
            );
            
            return $donors;
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * فعال‌ترین کاربران
     */
    private function getTopUsers() {
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
        $days = min(365, max(1, (int)($_GET['days'] ?? 30)));
        
        $cacheKey = "api_top_users_{$limit}_{$days}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($limit, $days) {
            $users = $this->db->fetchAll(
                "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.username,
                    COUNT(m.id) as message_count
                FROM users u
                INNER JOIN messages m ON u.id = m.user_id
                WHERE m.direction = 'in'
                AND m.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY u.id
                ORDER BY message_count DESC
                LIMIT ?",
                [$days, $limit]
            );
            
            return $users;
        });
        
        $this->sendSuccess($data);
    }
    
    /**
     * پرکاربردترین کلمات کلیدی
     */
    private function getTopKeywords() {
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
        
        $cacheKey = "api_top_keywords_{$limit}";
        
        $data = $this->cache->remember($cacheKey, 300, function() use ($limit) {
            $keywords = $this->db->fetchAll(
                "SELECT 
                    k.id,
                    k.keyword,
                    COUNT(m.id) as match_count
                FROM keywords k
                INNER JOIN keyword_matches m ON k.id = m.keyword_id
                GROUP BY k.id
                ORDER BY match_count DESC
                LIMIT ?",
                [$limit]
            );
            
            return $keywords;
        });
        
        $this->sendSuccess($data);
    }
    
    // ══════════════════════════════════════
    // Response Helpers
    // ══════════════════════════════════════
    
    /**
     * ارسال پاسخ موفق
     */
    private function sendSuccess($data) {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
        exit;
    }
    
    /**
     * ارسال پاسخ خطا
     */
    private function sendError($code, $message) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
        exit;
    }
    
    // ══════════════════════════════════════
    // API Token Management
    // ══════════════════════════════════════
    
    /**
     * ساخت API Token جدید
     */
    public static function createToken($name, $permissions = [], $expiresAt = null) {
        $db = Database::getInstance();
        $logger = Logger::getInstance();
        
        // تولید توکن
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // ذخیره در دیتابیس
        $id = $db->insert('api_tokens', [
            'name' => $name,
            'token_hash' => $tokenHash,
            'permissions' => json_encode($permissions),
            'expires_at' => $expiresAt,
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($id) {
            $logger->info('API token created', [
                'token_id' => $id,
                'name' => $name
            ]);
            
            return [
                'success' => true,
                'token_id' => $id,
                'token' => $token // فقط یکبار نمایش داده می‌شه
            ];
        }
        
        return ['success' => false, 'error' => 'خطا در ساخت توکن'];
    }
    
    /**
     * لغو API Token
     */
    public static function revokeToken($tokenId) {
        $db = Database::getInstance();
        $logger = Logger::getInstance();
        
        $db->update('api_tokens', ['active' => 0], 'id = ?', [$tokenId]);
        
        $logger->warning('API token revoked', ['token_id' => $tokenId]);
        
        return ['success' => true];
    }
    
    /**
     * لیست API Tokens
     */
    public static function listTokens() {
        $db = Database::getInstance();
        
        return $db->fetchAll(
            "SELECT id, name, permissions, active, created_at, last_used_at, last_used_ip, expires_at
             FROM api_tokens
             ORDER BY created_at DESC"
        );
    }
}