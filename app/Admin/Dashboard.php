<?php
/**
 * ============================================
 * کلاس مدیریت داشبورد (Dashboard)
 * ============================================
 * محاسبه آمار کلی
 * داده‌های نمودارها
 * برترین حامیان
 * آخرین فعالیت‌ها
 * Cache برای Performance
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;

class Dashboard {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $cacheTtl = 300; // 5 دقیقه
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->logger = Logger::getInstance();
    }
    
    // ──────────────────────────────────────
    // دریافت Instance (Singleton)
    // ──────────────────────────────────────
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ══════════════════════════════════════
    // آمار کلی
    // ══════════════════════════════════════
    
    /**
     * دریافت تمام آمار داشبورد
     */
    public function getFullStats() {
        return $this->cache->remember('dashboard_full_stats', $this->cacheTtl, function() {
            return [
                'users' => $this->getUserStats(),
                'messages' => $this->getMessageStats(),
                'donations' => $this->getDonationStats(),
                'keywords' => $this->getKeywordStats(),
                'growth' => $this->getGrowthStats()
            ];
        });
    }
    
    /**
     * آمار کاربران
     */
    public function getUserStats() {
        return $this->cache->remember('dashboard_user_stats', $this->cacheTtl, function() {
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM users");
            $today = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE DATE(joined_at) = CURDATE()"
            );
            $week = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $month = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $vip = $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_vip = 1");
            $blocked = $this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE blocked = 1");
            $active = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            
            return [
                'total' => (int)$total,
                'today' => (int)$today,
                'week' => (int)$week,
                'month' => (int)$month,
                'vip' => (int)$vip,
                'blocked' => (int)$blocked,
                'active' => (int)$active
            ];
        });
    }
    
    /**
     * آمار پیام‌ها
     */
    public function getMessageStats() {
        return $this->cache->remember('dashboard_message_stats', $this->cacheTtl, function() {
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM messages");
            $today = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()"
            );
            $incoming = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE direction = 'in'"
            );
            $outgoing = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE direction = 'out'"
            );
            $week = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            
            return [
                'total' => (int)$total,
                'today' => (int)$today,
                'week' => (int)$week,
                'incoming' => (int)$incoming,
                'outgoing' => (int)$outgoing
            ];
        });
    }
    
    /**
     * آمار دونیت‌ها
     */
    public function getDonationStats() {
        return $this->cache->remember('dashboard_donation_stats', $this->cacheTtl, function() {
            $totalAmount = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success'"
            );
            $totalCount = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM donations WHERE status = 'success'"
            );
            $todayAmount = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND DATE(created_at) = CURDATE()"
            );
            $todayCount = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM donations WHERE status = 'success' AND DATE(created_at) = CURDATE()"
            );
            $monthAmount = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $monthCount = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM donations WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $pending = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM donations WHERE status = 'pending'"
            );
            $failed = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM donations WHERE status = 'failed'"
            );
            
            // میانگین
            $average = $totalCount > 0 ? $totalAmount / $totalCount : 0;
            
            // بزرگترین دونیت
            $maxDonation = $this->db->fetchColumn(
                "SELECT COALESCE(MAX(amount), 0) FROM donations WHERE status = 'success'"
            );
            
            return [
                'total_amount' => (int)$totalAmount,
                'total_count' => (int)$totalCount,
                'today_amount' => (int)$todayAmount,
                'today_count' => (int)$todayCount,
                'month_amount' => (int)$monthAmount,
                'month_count' => (int)$monthCount,
                'pending' => (int)$pending,
                'failed' => (int)$failed,
                'average' => (int)$average,
                'max_donation' => (int)$maxDonation
            ];
        });
    }
    
    /**
     * آمار کلمات کلیدی
     */
    public function getKeywordStats() {
        return $this->cache->remember('dashboard_keyword_stats', $this->cacheTtl, function() {
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM keywords");
            $active = $this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 1");
            $inactive = $this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 0");
            
            return [
                'total' => (int)$total,
                'active' => (int)$active,
                'inactive' => (int)$inactive
            ];
        });
    }
    
    /**
     * آمار رشد (درصد تغییرات)
     */
    public function getGrowthStats() {
        return $this->cache->remember('dashboard_growth_stats', $this->cacheTtl, function() {
            // رشد کاربران
            $usersThisMonth = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $usersLastMonth = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE joined_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $userGrowth = $this->calculateGrowth($usersThisMonth, $usersLastMonth);
            
            // رشد دونیت‌ها
            $donationsThisMonth = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $donationsLastMonth = $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success' AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $donationGrowth = $this->calculateGrowth($donationsThisMonth, $donationsLastMonth);
            
            // رشد پیام‌ها
            $messagesThisMonth = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $messagesLastMonth = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $messageGrowth = $this->calculateGrowth($messagesThisMonth, $messagesLastMonth);
            
            return [
                'users' => $userGrowth,
                'donations' => $donationGrowth,
                'messages' => $messageGrowth
            ];
        });
    }
    
    /**
     * محاسبه درصد رشد
     */
    private function calculateGrowth($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        $growth = (($current - $previous) / $previous) * 100;
        return round($growth, 2);
    }
    
    // ══════════════════════════════════════
    // داده‌های نمودارها
    // ══════════════════════════════════════
    
    /**
     * داده‌های نمودار دونیت‌ها
     */
    public function getDonationChartData($days = 30) {
        $cacheKey = "dashboard_donation_chart_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        SUM(amount) as total_amount,
                        COUNT(*) as count
                    FROM donations 
                    WHERE status = 'success' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $results = $this->db->fetchAll($sql, [$days]);
            
            // پر کردن روزهای خالی
            $chartData = $this->fillDateGaps($results, $days, [
                'total_amount' => 0,
                'count' => 0
            ]);
            
            return [
                'labels' => array_column($chartData, 'date'),
                'datasets' => [
                    [
                        'label' => 'مبلغ دونیت (تومان)',
                        'data' => array_column($chartData, 'total_amount'),
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'تعداد دونیت',
                        'data' => array_column($chartData, 'count'),
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
     * داده‌های نمودار کاربران
     */
    public function getUserChartData($days = 30) {
        $cacheKey = "dashboard_user_chart_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            $sql = "SELECT 
                        DATE(joined_at) as date,
                        COUNT(*) as count
                    FROM users 
                    WHERE joined_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(joined_at)
                    ORDER BY date ASC";
            
            $results = $this->db->fetchAll($sql, [$days]);
            
            // پر کردن روزهای خالی
            $chartData = $this->fillDateGaps($results, $days, ['count' => 0]);
            
            return [
                'labels' => array_column($chartData, 'date'),
                'datasets' => [
                    [
                        'label' => 'کاربران جدید',
                        'data' => array_column($chartData, 'count'),
                        'borderColor' => '#8b5cf6',
                        'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ];
        });
    }
    
    /**
     * داده‌های نمودار پیام‌ها
     */
    public function getMessageChartData($days = 30) {
        $cacheKey = "dashboard_message_chart_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        direction,
                        COUNT(*) as count
                    FROM messages 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at), direction
                    ORDER BY date ASC";
            
            $results = $this->db->fetchAll($sql, [$days]);
            
            // گروه‌بندی بر اساس direction
            $incoming = [];
            $outgoing = [];
            
            foreach ($results as $row) {
                if ($row['direction'] === 'in') {
                    $incoming[$row['date']] = $row['count'];
                } else {
                    $outgoing[$row['date']] = $row['count'];
                }
            }
            
            // پر کردن روزهای خالی
            $allDates = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $allDates[] = $date;
            }
            
            $incomingData = [];
            $outgoingData = [];
            
            foreach ($allDates as $date) {
                $incomingData[] = $incoming[$date] ?? 0;
                $outgoingData[] = $outgoing[$date] ?? 0;
            }
            
            return [
                'labels' => $allDates,
                'datasets' => [
                    [
                        'label' => 'پیام‌های دریافتی',
                        'data' => $incomingData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'پیام‌های ارسالی',
                        'data' => $outgoingData,
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'tension' => 0.4
                    ]
                ]
            ];
        });
    }
    
    /**
     * پر کردن روزهای خالی در داده‌های نمودار
     */
    private function fillDateGaps($results, $days, $defaultValues) {
        $indexed = [];
        foreach ($results as $row) {
            $indexed[$row['date']] = $row;
        }
        
        $filled = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            
            if (isset($indexed[$date])) {
                $filled[] = $indexed[$date];
            } else {
                $filled[] = array_merge(['date' => $date], $defaultValues);
            }
        }
        
        return $filled;
    }
    
    // ══════════════════════════════════════
    // برترین‌ها
    // ══════════════════════════════════════
    
    /**
     * برترین حامیان (Top Donors)
     */
    public function getTopDonors($limit = 10) {
        $cacheKey = "dashboard_top_donors_{$limit}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($limit) {
            $sql = "SELECT 
                        u.id,
                        u.first_name,
                        u.last_name,
                        u.username,
                        COUNT(d.id) as donation_count,
                        SUM(d.amount) as total_amount,
                        MAX(d.created_at) as last_donation
                    FROM users u
                    INNER JOIN donations d ON u.id = d.user_id
                    WHERE d.status = 'success'
                    GROUP BY u.id
                    ORDER BY total_amount DESC
                    LIMIT ?";
            
            $donors = $this->db->fetchAll($sql, [$limit]);
            
            // فرمت‌بندی
            foreach ($donors as &$donor) {
                $donor['display_name'] = $this->formatDisplayName($donor);
                $donor['total_amount_formatted'] = $this->formatNumber($donor['total_amount']);
                $donor['rank'] = array_search($donor, $donors) + 1;
            }
            
            return $donors;
        });
    }
    
    /**
     * فعال‌ترین کاربران
     */
    public function getMostActiveUsers($limit = 10) {
        $cacheKey = "dashboard_active_users_{$limit}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($limit) {
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
                    GROUP BY u.id
                    ORDER BY message_count DESC
                    LIMIT ?";
            
            $users = $this->db->fetchAll($sql, [$limit]);
            
            foreach ($users as &$user) {
                $user['display_name'] = $this->formatDisplayName($user);
            }
            
            return $users;
        });
    }
    
    /**
     * پرکاربردترین کلمات کلیدی
     */
    public function getTopKeywords($limit = 10) {
        $cacheKey = "dashboard_top_keywords_{$limit}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($limit) {
            $sql = "SELECT 
                        k.id,
                        k.keyword,
                        k.answer,
                        k.active,
                        COUNT(*) as match_count
                    FROM keywords k
                    INNER JOIN messages m ON LOWER(m.text) LIKE CONCAT('%', LOWER(k.keyword), '%')
                    WHERE m.direction = 'in'
                    AND k.active = 1
                    GROUP BY k.id
                    ORDER BY match_count DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
        });
    }
    
    // ══════════════════════════════════════
    // آخرین فعالیت‌ها
    // ══════════════════════════════════════
    
    /**
     * آخرین پیام‌های دریافتی
     */
    public function getRecentMessages($limit = 10) {
        $sql = "SELECT 
                    m.id,
                    m.user_id,
                    m.text,
                    m.message_type,
                    m.created_at,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.direction = 'in'
                ORDER BY m.created_at DESC
                LIMIT ?";
        
        $messages = $this->db->fetchAll($sql, [$limit]);
        
        foreach ($messages as &$msg) {
            $msg['user_display_name'] = $this->formatDisplayName($msg);
            $msg['text_preview'] = mb_substr($msg['text'], 0, 50);
            $msg['time_ago'] = $this->timeAgo($msg['created_at']);
        }
        
        return $messages;
    }
    
    /**
     * آخرین دونیت‌ها
     */
    public function getRecentDonations($limit = 10) {
        $sql = "SELECT 
                    d.id,
                    d.user_id,
                    d.amount,
                    d.gateway,
                    d.status,
                    d.created_at,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.status = 'success'
                ORDER BY d.created_at DESC
                LIMIT ?";
        
        $donations = $this->db->fetchAll($sql, [$limit]);
        
        foreach ($donations as &$donation) {
            $donation['user_display_name'] = $this->formatDisplayName($donation);
            $donation['amount_formatted'] = $this->formatNumber($donation['amount']);
            $donation['time_ago'] = $this->timeAgo($donation['created_at']);
        }
        
        return $donations;
    }
    
    /**
     * آخرین کاربران ثبت‌نام شده
     */
    public function getRecentUsers($limit = 10) {
        $sql = "SELECT 
                    id,
                    first_name,
                    last_name,
                    username,
                    is_vip,
                    joined_at,
                    last_seen
                FROM users
                ORDER BY joined_at DESC
                LIMIT ?";
        
        $users = $this->db->fetchAll($sql, [$limit]);
        
        foreach ($users as &$user) {
            $user['display_name'] = $this->formatDisplayName($user);
            $user['joined_ago'] = $this->timeAgo($user['joined_at']);
        }
        
        return $users;
    }
    
    // ══════════════════════════════════════
    // آمار سریع (برای کارت‌های داشبورد)
    // ══════════════════════════════════════
    
    /**
     * آمار کارت‌های داشبورد
     */
    public function getDashboardCards() {
        $stats = $this->getFullStats();
        $growth = $stats['growth'];
        
        return [
            [
                'title' => 'کل کاربران',
                'value' => $stats['users']['total'],
                'icon' => '👥',
                'color' => 'from-blue-500 to-blue-600',
                'change' => $growth['users'],
                'subtitle' => "+{$stats['users']['today']} امروز"
            ],
            [
                'title' => 'پیام‌ها',
                'value' => $stats['messages']['total'],
                'icon' => '💬',
                'color' => 'from-green-500 to-green-600',
                'change' => $growth['messages'],
                'subtitle' => "+{$stats['messages']['today']} امروز"
            ],
            [
                'title' => 'تعداد دونیت',
                'value' => $stats['donations']['total_count'],
                'icon' => '💳',
                'color' => 'from-yellow-500 to-orange-500',
                'change' => $growth['donations'],
                'subtitle' => "+{$stats['donations']['today_count']} امروز"
            ],
            [
                'title' => 'مجموع دونیت',
                'value' => $this->formatNumber($stats['donations']['total_amount']),
                'icon' => '💰',
                'color' => 'from-purple-500 to-pink-500',
                'change' => $growth['donations'],
                'subtitle' => "+{$this->formatNumber($stats['donations']['today_amount'])} امروز"
            ]
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
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
        
        return 'کاربر #' . $user['id'];
    }
    
    /**
     * فرمت اعداد با جداکننده
     */
    private function formatNumber($number) {
        return number_format($number, 0, '.', ',');
    }
    
    /**
     * تبدیل زمان به "x دقیقه پیش"
     */
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'لحظاتی پیش';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "{$minutes} دقیقه پیش";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} ساعت پیش";
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "{$days} روز پیش";
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "{$weeks} هفته پیش";
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "{$months} ماه پیش";
        } else {
            $years = floor($diff / 31536000);
            return "{$years} سال پیش";
        }
    }
    
    /**
     * پاک کردن کش داشبورد
     */
    public function clearCache() {
        $cacheKeys = [
            'dashboard_full_stats',
            'dashboard_user_stats',
            'dashboard_message_stats',
            'dashboard_donation_stats',
            'dashboard_keyword_stats',
            'dashboard_growth_stats'
        ];
        
        foreach ($cacheKeys as $key) {
            $this->cache->delete($key);
        }
        
        // پاک کردن کش نمودارها
        for ($i = 7; $i <= 90; $i += 7) {
            $this->cache->delete("dashboard_donation_chart_{$i}");
            $this->cache->delete("dashboard_user_chart_{$i}");
            $this->cache->delete("dashboard_message_chart_{$i}");
        }
        
        // پاک کردن کش برترین‌ها
        for ($i = 5; $i <= 20; $i += 5) {
            $this->cache->delete("dashboard_top_donors_{$i}");
            $this->cache->delete("dashboard_active_users_{$i}");
            $this->cache->delete("dashboard_top_keywords_{$i}");
        }
        
        $this->logger->info('Dashboard cache cleared');
        
        return true;
    }
    
    /**
     * تنظیم TTL کش
     */
    public function setCacheTtl($seconds) {
        $this->cacheTtl = $seconds;
        return $this;
    }
}