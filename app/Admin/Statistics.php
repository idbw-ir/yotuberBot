<?php
/**
 * ============================================
 * کلاس آمار و گزارش‌گیری پیشرفته (Statistics)
 * ============================================
 * آمار کلی و تفکیکی
 * نمودارهای متنوع (خطی، دایره‌ای، میله‌ای)
 * مقایسه دوره‌ها (Month over Month, Year over Year)
 * KPI ها و شاخص‌های کلیدی
 * گزارش‌های سفارشی
 * Export به فرمت‌های مختلف
 * Cache برای Performance
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;

class Statistics {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $cacheTtl = 300; // 5 دقیقه
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->logger = Logger::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ══════════════════════════════════════
    // آمار کلی (Overview)
    // ══════════════════════════════════════
    
    /**
     * آمار کلی سیستم
     */
    public function getOverview() {
        $cacheKey = 'stats_overview';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            return [
                'users' => $this->getUserOverview(),
                'messages' => $this->getMessageOverview(),
                'donations' => $this->getDonationOverview(),
                'keywords' => $this->getKeywordOverview(),
                'system' => $this->getSystemOverview()
            ];
        });
    }
    
    /**
     * آمار کلی کاربران
     */
    private function getUserOverview() {
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
    }
    
    /**
     * آمار کلی پیام‌ها
     */
    private function getMessageOverview() {
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
    }
    
    /**
     * آمار کلی دونیت‌ها
     */
    private function getDonationOverview() {
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
            'average' => (int)$this->db->fetchColumn(
                "SELECT COALESCE(AVG(amount), 0) FROM donations WHERE status = 'success'"
            ),
            'max' => (int)$this->db->fetchColumn(
                "SELECT COALESCE(MAX(amount), 0) FROM donations WHERE status = 'success'"
            ),
            'donors_count' => (int)$this->db->fetchColumn(
                "SELECT COUNT(DISTINCT user_id) FROM donations WHERE status = 'success'"
            )
        ];
    }
    
    /**
     * آمار کلی کلمات کلیدی
     */
    private function getKeywordOverview() {
        return [
            'total' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords"),
            'active' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 1"),
            'total_matches' => (int)$this->db->fetchColumn(
                "SELECT COALESCE(COUNT(*), 0) FROM keyword_matches"
            )
        ];
    }
    
    /**
     * آمار کلی سیستم
     */
    private function getSystemOverview() {
        return [
            'db_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed(),
            'uptime_days' => $this->getUptimeDays(),
            'php_version' => PHP_VERSION,
            'server_os' => PHP_OS
        ];
    }
    
    // ══════════════════════════════════════
    // نمودارها
    // ══════════════════════════════════════
    
    /**
     * نمودار رشد کاربران
     */
    public function getUserGrowthChart($days = 30, $groupBy = 'day') {
        $cacheKey = "stats_user_growth_{$days}_{$groupBy}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days, $groupBy) {
            $dateFormat = $this->getDateFormat($groupBy);
            
            $sql = "SELECT 
                        DATE_FORMAT(joined_at, ?) as period,
                        COUNT(*) as count
                    FROM users 
                    WHERE joined_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY period
                    ORDER BY period ASC";
            
            $results = $this->db->fetchAll($sql, [$dateFormat, $days]);
            
            // پر کردن دوره‌های خالی
            $filled = $this->fillPeriodGaps($results, $days, $groupBy, ['count' => 0]);
            
            return [
                'type' => 'line',
                'labels' => array_column($filled, 'period'),
                'datasets' => [
                    [
                        'label' => 'کاربران جدید',
                        'data' => array_column($filled, 'count'),
                        'borderColor' => '#8b5cf6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ];
        });
    }
    
    /**
     * نمودار درآمد (دونیت‌ها)
     */
    public function getRevenueChart($days = 30, $groupBy = 'day') {
        $cacheKey = "stats_revenue_{$days}_{$groupBy}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days, $groupBy) {
            $dateFormat = $this->getDateFormat($groupBy);
            
            $sql = "SELECT 
                        DATE_FORMAT(created_at, ?) as period,
                        SUM(amount) as total_amount,
                        COUNT(*) as count,
                        AVG(amount) as average
                    FROM donations 
                    WHERE status = 'success'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY period
                    ORDER BY period ASC";
            
            $results = $this->db->fetchAll($sql, [$dateFormat, $days]);
            
            $filled = $this->fillPeriodGaps($results, $days, $groupBy, [
                'total_amount' => 0,
                'count' => 0,
                'average' => 0
            ]);
            
            return [
                'type' => 'bar',
                'labels' => array_column($filled, 'period'),
                'datasets' => [
                    [
                        'label' => 'مبلغ دونیت (تومان)',
                        'data' => array_column($filled, 'total_amount'),
                        'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                        'borderColor' => '#10b981',
                        'borderWidth' => 1,
                        'yAxisID' => 'y'
                    ],
                    [
                        'label' => 'تعداد دونیت',
                        'data' => array_column($filled, 'count'),
                        'type' => 'line',
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'yAxisID' => 'y1'
                    ]
                ]
            ];
        });
    }
    
    /**
     * نمودار فعالیت پیام‌ها
     */
    public function getMessageActivityChart($days = 30, $groupBy = 'day') {
        $cacheKey = "stats_message_activity_{$days}_{$groupBy}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days, $groupBy) {
            $dateFormat = $this->getDateFormat($groupBy);
            
            $sql = "SELECT 
                        DATE_FORMAT(created_at, ?) as period,
                        direction,
                        COUNT(*) as count
                    FROM messages 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY period, direction
                    ORDER BY period ASC";
            
            $results = $this->db->fetchAll($sql, [$dateFormat, $days]);
            
            // گروه‌بندی
            $incoming = [];
            $outgoing = [];
            
            foreach ($results as $row) {
                if ($row['direction'] === 'in') {
                    $incoming[$row['period']] = $row['count'];
                } else {
                    $outgoing[$row['period']] = $row['count'];
                }
            }
            
            // پر کردن دوره‌های خالی
            $periods = $this->generatePeriods($days, $groupBy);
            $incomingData = [];
            $outgoingData = [];
            
            foreach ($periods as $period) {
                $incomingData[] = $incoming[$period] ?? 0;
                $outgoingData[] = $outgoing[$period] ?? 0;
            }
            
            return [
                'type' => 'line',
                'labels' => $periods,
                'datasets' => [
                    [
                        'label' => 'پیام‌های دریافتی',
                        'data' => $incomingData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ],
                    [
                        'label' => 'پیام‌های ارسالی',
                        'data' => $outgoingData,
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ];
        });
    }
    
    /**
     * نمودار دایره‌ای وضعیت کاربران
     */
    public function getUserStatusPieChart() {
        $cacheKey = 'stats_user_status_pie';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users");
            $vip = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_vip = 1 AND blocked = 0");
            $blocked = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE blocked = 1");
            $active = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE is_vip = 0 AND blocked = 0 AND last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $inactive = $total - $vip - $blocked - $active;
            
            return [
                'type' => 'doughnut',
                'labels' => ['VIP', 'فعال', 'غیرفعال', 'بلاک شده'],
                'datasets' => [
                    [
                        'data' => [$vip, max(0, $active), max(0, $inactive), $blocked],
                        'backgroundColor' => [
                            '#fbbf24',
                            '#10b981',
                            '#6b7280',
                            '#ef4444'
                        ],
                        'borderWidth' => 2,
                        'borderColor' => '#1f2937'
                    ]
                ]
            ];
        });
    }
    
    /**
     * نمودار دایره‌ای درگاه‌های پرداخت
     */
    public function getGatewayPieChart($days = 30) {
        $cacheKey = "stats_gateway_pie_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            $sql = "SELECT 
                        COALESCE(gateway, 'نامشخص') as gateway,
                        COUNT(*) as count,
                        SUM(amount) as total_amount
                    FROM donations 
                    WHERE status = 'success'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY gateway
                    ORDER BY total_amount DESC";
            
            $results = $this->db->fetchAll($sql, [$days]);
            
            $colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899'];
            
            return [
                'type' => 'pie',
                'labels' => array_column($results, 'gateway'),
                'datasets' => [
                    [
                        'data' => array_column($results, 'total_amount'),
                        'backgroundColor' => array_slice($colors, 0, count($results)),
                        'borderWidth' => 2,
                        'borderColor' => '#1f2937'
                    ]
                ],
                'metadata' => $results
            ];
        });
    }
    
    /**
     * نمودار ساعتی فعالیت
     */
    public function getHourlyActivityChart($days = 7) {
        $cacheKey = "stats_hourly_activity_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            $sql = "SELECT 
                        HOUR(created_at) as hour,
                        COUNT(*) as count
                    FROM messages 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY HOUR(created_at)
                    ORDER BY hour ASC";
            
            $results = $this->db->fetchAll($sql, [$days]);
            
            // پر کردن ساعت‌های خالی
            $hours = array_fill(0, 24, 0);
            foreach ($results as $row) {
                $hours[(int)$row['hour']] = $row['count'];
            }
            
            $labels = array_map(function($h) {
                return sprintf('%02d:00', $h);
            }, range(0, 23));
            
            return [
                'type' => 'bar',
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'تعداد پیام',
                        'data' => $hours,
                        'backgroundColor' => 'rgba(139, 92, 246, 0.7)',
                        'borderColor' => '#8b5cf6',
                        'borderWidth' => 1
                    ]
                ]
            ];
        });
    }
    
    // ══════════════════════════════════════
    // مقایسه دوره‌ها
    // ══════════════════════════════════════
    
    /**
     * مقایسه این ماه با ماه گذشته
     */
    public function compareWithLastMonth() {
        $cacheKey = 'stats_compare_month';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            $thisMonth = [
                'start' => date('Y-m-01'),
                'end' => date('Y-m-d')
            ];
            
            $lastMonth = [
                'start' => date('Y-m-01', strtotime('-1 month')),
                'end' => date('Y-m-t', strtotime('-1 month'))
            ];
            
            return [
                'users' => $this->compareMetric('users', $thisMonth, $lastMonth),
                'donations' => $this->compareMetric('donations', $thisMonth, $lastMonth),
                'messages' => $this->compareMetric('messages', $thisMonth, $lastMonth)
            ];
        });
    }
    
    /**
     * مقایسه یک متریک در دو دوره
     */
    private function compareMetric($metric, $current, $previous) {
        switch ($metric) {
            case 'users':
                $currentValue = (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE joined_at BETWEEN ? AND ?",
                    [$current['start'], $current['end'] . ' 23:59:59']
                );
                $previousValue = (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE joined_at BETWEEN ? AND ?",
                    [$previous['start'], $previous['end'] . ' 23:59:59']
                );
                break;
                
            case 'donations':
                $currentValue = (int)$this->db->fetchColumn(
                    "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at BETWEEN ? AND ?",
                    [$current['start'], $current['end'] . ' 23:59:59']
                );
                $previousValue = (int)$this->db->fetchColumn(
                    "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at BETWEEN ? AND ?",
                    [$previous['start'], $previous['end'] . ' 23:59:59']
                );
                break;
                
            case 'messages':
                $currentValue = (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM messages WHERE created_at BETWEEN ? AND ?",
                    [$current['start'], $current['end'] . ' 23:59:59']
                );
                $previousValue = (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM messages WHERE created_at BETWEEN ? AND ?",
                    [$previous['start'], $previous['end'] . ' 23:59:59']
                );
                break;
                
            default:
                return null;
        }
        
        return [
            'current' => $currentValue,
            'previous' => $previousValue,
            'change' => $currentValue - $previousValue,
            'change_percent' => $previousValue > 0 
                ? round((($currentValue - $previousValue) / $previousValue) * 100, 2)
                : ($currentValue > 0 ? 100 : 0),
            'trend' => $currentValue > $previousValue ? 'up' : ($currentValue < $previousValue ? 'down' : 'neutral')
        ];
    }
    
    /**
     * مقایسه امروز با دیروز
     */
    public function compareWithYesterday() {
        $cacheKey = 'stats_compare_yesterday';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            $today = [
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d')
            ];
            
            $yesterday = [
                'start' => date('Y-m-d', strtotime('-1 day')),
                'end' => date('Y-m-d', strtotime('-1 day'))
            ];
            
            return [
                'users' => $this->compareMetric('users', $today, $yesterday),
                'donations' => $this->compareMetric('donations', $today, $yesterday),
                'messages' => $this->compareMetric('messages', $today, $yesterday)
            ];
        });
    }
    
    // ══════════════════════════════════════
    // KPI ها (شاخص‌های کلیدی عملکرد)
    // ══════════════════════════════════════
    
    /**
     * KPI های اصلی
     */
    public function getKPIs() {
        $cacheKey = 'stats_kpis';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            $overview = $this->getOverview();
            
            // Conversion Rate (درصد کاربرانی که دونیت کردن)
            $totalUsers = $overview['users']['total'];
            $donorsCount = $overview['donations']['donors_count'];
            $conversionRate = $totalUsers > 0 ? ($donorsCount / $totalUsers) * 100 : 0;
            
            // Average Revenue Per User (ARPU)
            $arpu = $totalUsers > 0 ? $overview['donations']['total_amount'] / $totalUsers : 0;
            
            // Average Revenue Per Paying User (ARPPU)
            $arppu = $donorsCount > 0 ? $overview['donations']['total_amount'] / $donorsCount : 0;
            
            // Engagement Rate (کاربران فعال / کل کاربران)
            $engagementRate = $totalUsers > 0 
                ? ($overview['users']['active_week'] / $totalUsers) * 100 
                : 0;
            
            // Response Rate (درصد پیام‌هایی که پاسخ گرفتن)
            $totalIncoming = $overview['messages']['incoming'];
            $totalOutgoing = $overview['messages']['outgoing'];
            $responseRate = $totalIncoming > 0 ? ($totalOutgoing / $totalIncoming) * 100 : 0;
            
            // VIP Conversion Rate
            $vipRate = $totalUsers > 0 ? ($overview['users']['vip'] / $totalUsers) * 100 : 0;
            
            // Block Rate
            $blockRate = $totalUsers > 0 ? ($overview['users']['blocked'] / $totalUsers) * 100 : 0;
            
            // Churn Rate (کاربران غیرفعال / کل کاربران)
            $inactiveCount = $totalUsers - $overview['users']['active_week'] - $overview['users']['blocked'];
            $churnRate = $totalUsers > 0 ? ($inactiveCount / $totalUsers) * 100 : 0;
            
            return [
                'conversion_rate' => [
                    'value' => round($conversionRate, 2),
                    'label' => 'نرخ تبدیل',
                    'description' => 'درصد کاربرانی که دونیت کردن',
                    'icon' => '💰',
                    'color' => 'green'
                ],
                'arpu' => [
                    'value' => round($arpu),
                    'label' => 'ARPU',
                    'description' => 'میانگین درآمد به ازای هر کاربر',
                    'icon' => '💵',
                    'color' => 'blue',
                    'unit' => 'تومان'
                ],
                'arppu' => [
                    'value' => round($arppu),
                    'label' => 'ARPPU',
                    'description' => 'میانگین درآمد به ازای هر کاربر پرداخت‌کننده',
                    'icon' => '💎',
                    'color' => 'purple',
                    'unit' => 'تومان'
                ],
                'engagement_rate' => [
                    'value' => round($engagementRate, 2),
                    'label' => 'نرخ تعامل',
                    'description' => 'درصد کاربران فعال هفتگی',
                    'icon' => '🔥',
                    'color' => 'orange'
                ],
                'response_rate' => [
                    'value' => round($responseRate, 2),
                    'label' => 'نرخ پاسخ‌دهی',
                    'description' => 'درصد پیام‌هایی که پاسخ گرفتن',
                    'icon' => '💬',
                    'color' => 'cyan'
                ],
                'vip_rate' => [
                    'value' => round($vipRate, 2),
                    'label' => 'نرخ VIP',
                    'description' => 'درصد کاربران VIP',
                    'icon' => '👑',
                    'color' => 'yellow'
                ],
                'block_rate' => [
                    'value' => round($blockRate, 2),
                    'label' => 'نرخ بلاک',
                    'description' => 'درصد کاربرانی که ربات رو بلاک کردن',
                    'icon' => '🚫',
                    'color' => 'red'
                ],
                'churn_rate' => [
                    'value' => round($churnRate, 2),
                    'label' => 'نرخ ریزش',
                    'description' => 'درصد کاربران غیرفعال',
                    'icon' => '📉',
                    'color' => 'gray'
                ]
            ];
        });
    }
    
    // ══════════════════════════════════════
    // گزارش‌های سفارشی
    // ══════════════════════════════════════
    
    /**
     * گزارش سفارشی با فیلترهای دلخواه
     */
    public function customReport(array $options) {
        $from = $options['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $to = $options['to'] ?? date('Y-m-d');
        $metrics = $options['metrics'] ?? ['users', 'donations', 'messages'];
        $groupBy = $options['group_by'] ?? 'day';
        
        $report = [
            'title' => $options['title'] ?? 'گزارش سفارشی',
            'from' => $from,
            'to' => $to,
            'generated_at' => date('Y-m-d H:i:s'),
            'metrics' => []
        ];
        
        foreach ($metrics as $metric) {
            switch ($metric) {
                case 'users':
                    $report['metrics']['users'] = $this->getMetricReport('users', $from, $to, $groupBy);
                    break;
                case 'donations':
                    $report['metrics']['donations'] = $this->getMetricReport('donations', $from, $to, $groupBy);
                    break;
                case 'messages':
                    $report['metrics']['messages'] = $this->getMetricReport('messages', $from, $to, $groupBy);
                    break;
            }
        }
        
        return $report;
    }
    
    /**
     * گزارش یک متریک خاص
     */
    private function getMetricReport($metric, $from, $to, $groupBy) {
        $dateFormat = $this->getDateFormat($groupBy);
        
        switch ($metric) {
            case 'users':
                $sql = "SELECT 
                            DATE_FORMAT(joined_at, ?) as period,
                            COUNT(*) as count
                        FROM users 
                        WHERE joined_at BETWEEN ? AND ?
                        GROUP BY period
                        ORDER BY period ASC";
                break;
                
            case 'donations':
                $sql = "SELECT 
                            DATE_FORMAT(created_at, ?) as period,
                            COUNT(*) as count,
                            SUM(amount) as total_amount,
                            AVG(amount) as average
                        FROM donations 
                        WHERE status = 'success'
                        AND created_at BETWEEN ? AND ?
                        GROUP BY period
                        ORDER BY period ASC";
                break;
                
            case 'messages':
                $sql = "SELECT 
                            DATE_FORMAT(created_at, ?) as period,
                            COUNT(*) as count,
                            SUM(CASE WHEN direction = 'in' THEN 1 ELSE 0 END) as incoming,
                            SUM(CASE WHEN direction = 'out' THEN 1 ELSE 0 END) as outgoing
                        FROM messages 
                        WHERE created_at BETWEEN ? AND ?
                        GROUP BY period
                        ORDER BY period ASC";
                break;
                
            default:
                return null;
        }
        
        $results = $this->db->fetchAll($sql, [$dateFormat, $from, $to . ' 23:59:59']);
        
        // محاسبه مجموع‌ها
        $summary = [];
        if (!empty($results)) {
            foreach ($results[0] as $key => $value) {
                if ($key !== 'period' && is_numeric($value)) {
                    $summary[$key . '_total'] = array_sum(array_column($results, $key));
                    $summary[$key . '_avg'] = count($results) > 0 
                        ? array_sum(array_column($results, $key)) / count($results) 
                        : 0;
                    $summary[$key . '_max'] = max(array_column($results, $key));
                    $summary[$key . '_min'] = min(array_column($results, $key));
                }
            }
        }
        
        return [
            'data' => $results,
            'summary' => $summary,
            'periods' => count($results)
        ];
    }
    
    // ══════════════════════════════════════
    // برترین‌ها (Top Lists)
    // ══════════════════════════════════════
    
    /**
     * برترین حامیان
     */
    public function getTopDonors($limit = 10, $days = null) {
        $cacheKey = "stats_top_donors_{$limit}_" . ($days ?? 'all');
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($limit, $days) {
            $where = "d.status = 'success'";
            $params = [];
            
            if ($days) {
                $where .= " AND d.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
                $params[] = $days;
            }
            
            $sql = "SELECT 
                        u.id,
                        u.first_name,
                        u.last_name,
                        u.username,
                        COUNT(d.id) as donation_count,
                        SUM(d.amount) as total_amount,
                        AVG(d.amount) as average_amount,
                        MAX(d.amount) as max_donation,
                        MIN(d.created_at) as first_donation,
                        MAX(d.created_at) as last_donation
                    FROM users u
                    INNER JOIN donations d ON u.id = d.user_id
                    WHERE {$where}
                    GROUP BY u.id
                    ORDER BY total_amount DESC
                    LIMIT ?";
            
            $donors = $this->db->fetchAll($sql, $params);
            
            foreach ($donors as $i => &$donor) {
                $donor['rank'] = $i + 1;
                $donor['display_name'] = $this->formatDisplayName($donor);
            }
            
            return $donors;
        });
    }
    
    /**
     * فعال‌ترین کاربران
     */
    public function getMostActiveUsers($limit = 10, $days = 30) {
        $cacheKey = "stats_active_users_{$limit}_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($limit, $days) {
            $sql = "SELECT 
                        u.id,
                        u.first_name,
                        u.last_name,
                        u.username,
                        COUNT(m.id) as message_count,
                        MAX(m.created_at) as last_message
                    FROM users u
                    INNER JOIN messages m ON u.id = m.user_id
                    WHERE m.direction = 'in'
                    AND m.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY u.id
                    ORDER BY message_count DESC
                    LIMIT ?";
            
            $users = $this->db->fetchAll($sql, [$days, $limit]);
            
            foreach ($users as $i => &$user) {
                $user['rank'] = $i + 1;
                $user['display_name'] = $this->formatDisplayName($user);
            }
            
            return $users;
        });
    }
    
    /**
     * پرکاربردترین کلمات کلیدی
     */
    public function getTopKeywords($limit = 10) {
        $cacheKey = "stats_top_keywords_{$limit}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($limit) {
            $sql = "SELECT 
                        k.id,
                        k.keyword,
                        COUNT(m.id) as match_count
                    FROM keywords k
                    INNER JOIN keyword_matches m ON k.id = m.keyword_id
                    GROUP BY k.id
                    ORDER BY match_count DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
        });
    }
    
    // ══════════════════════════════════════
    // Export گزارش‌ها
    // ══════════════════════════════════════
    
    /**
     * Export گزارش به CSV
     */
    public function exportReportCsv(array $report) {
        $filename = 'report_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        $fp = fopen($filepath, 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
        
        // هدر گزارش
        fputcsv($fp, ['عنوان گزارش', $report['title']]);
        fputcsv($fp, ['از تاریخ', $report['from']]);
        fputcsv($fp, ['تا تاریخ', $report['to']]);
        fputcsv($fp, ['تاریخ تولید', $report['generated_at']]);
        fputcsv($fp, []);
        
        // داده‌های هر متریک
        foreach ($report['metrics'] as $metricName => $metricData) {
            fputcsv($fp, ["متریک: {$metricName}"]);
            
            if (!empty($metricData['data'])) {
                // هدر ستون‌ها
                fputcsv($fp, array_keys($metricData['data'][0]));
                
                // داده‌ها
                foreach ($metricData['data'] as $row) {
                    fputcsv($fp, array_values($row));
                }
            }
            
            // خلاصه
            if (!empty($metricData['summary'])) {
                fputcsv($fp, []);
                fputcsv($fp, ['خلاصه آماری']);
                foreach ($metricData['summary'] as $key => $value) {
                    fputcsv($fp, [$key, is_float($value) ? round($value, 2) : $value]);
                }
            }
            
            fputcsv($fp, []);
            fputcsv($fp, []);
        }
        
        fclose($fp);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
    
    /**
     * Export گزارش به JSON
     */
    public function exportReportJson(array $report) {
        $filename = 'report_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        file_put_contents(
            $filepath,
            json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * دریافت فرمت تاریخ بر اساس groupBy
     */
    private function getDateFormat($groupBy) {
        return match($groupBy) {
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%x-W%v',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };
    }
    
    /**
     * تولید لیست دوره‌ها
     */
    private function generatePeriods($days, $groupBy) {
        $periods = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = strtotime("-{$i} days");
            
            switch ($groupBy) {
                case 'hour':
                    // پیچیده‌تر - فعلاً نادیده می‌گیریم
                    break;
                case 'day':
                    $periods[] = date('Y-m-d', $date);
                    break;
                case 'week':
                    $periods[] = date('o-\WW', $date);
                    break;
                case 'month':
                    $periods[] = date('Y-m', $date);
                    break;
                case 'year':
                    $periods[] = date('Y', $date);
                    break;
            }
        }
        
        return array_unique($periods);
    }
    
    /**
     * پر کردن دوره‌های خالی
     */
    private function fillPeriodGaps($results, $days, $groupBy, $defaultValues) {
        $indexed = [];
        foreach ($results as $row) {
            $indexed[$row['period']] = $row;
        }
        
        $periods = $this->generatePeriods($days, $groupBy);
        $filled = [];
        
        foreach ($periods as $period) {
            if (isset($indexed[$period])) {
                $filled[] = $indexed[$period];
            } else {
                $filled[] = array_merge(['period' => $period], $defaultValues);
            }
        }
        
        return $filled;
    }
    
    /**
     * فرمت نام نمایشی
     */
    private function formatDisplayName($user) {
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
        
        return 'کاربر #' . ($user['id'] ?? '?');
    }
    
    /**
     * اندازه دیتابیس
     */
    private function getDatabaseSize() {
        try {
            $dbName = \App\Core\Config::getInstance()->database('name');
            $size = $this->db->fetchColumn(
                "SELECT SUM(data_length + index_length) / 1024 / 1024 as size_mb 
                 FROM information_schema.tables 
                 WHERE table_schema = ?",
                [$dbName]
            );
            
            return round($size, 2) . ' MB';
        } catch (\Exception $e) {
            return 'نامشخص';
        }
    }
    
    /**
     * فضای استفاده شده storage
     */
    private function getStorageUsed() {
        $path = dirname(__DIR__, 2) . '/storage';
        
        if (!is_dir($path)) {
            return '0 MB';
        }
        
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $size += $file->getSize();
        }
        
        return round($size / 1024 / 1024, 2) . ' MB';
    }
    
    /**
     * روزهای فعالیت سیستم
     */
    private function getUptimeDays() {
        $firstUser = $this->db->fetchColumn(
            "SELECT MIN(joined_at) FROM users"
        );
        
        if (!$firstUser) {
            return 0;
        }
        
        $days = (time() - strtotime($firstUser)) / 86400;
        return round($days);
    }
    
    /**
     * پاک کردن کش
     */
    public function clearCache() {
        $this->cache->clear();
        $this->logger->info('Statistics cache cleared');
        return true;
    }
}