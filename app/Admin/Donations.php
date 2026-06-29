<?php
/**
 * ============================================
 * کلاس مدیریت دونیت‌ها (Donations)
 * ============================================
 * لیست و فیلتر دونیت‌ها
 * آمار و گزارش‌های مالی
 * تأیید/رد دونیت‌ها
 * برترین حامیان
 * نمودارهای مالی
 * Export (CSV/JSON)
 * مدیریت درگاه‌ها
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Telegram\Bot;

class Donations {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $bot;
    private $cacheTtl = 300;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
        $this->logger = Logger::getInstance();
        $this->bot = new Bot();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // ══════════════════════════════════════
    // دریافت لیست دونیت‌ها
    // ══════════════════════════════════════
    
    public function getAll(array $filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];
        
        // فیلتر بر اساس وضعیت
        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'success', 'failed'])) {
            $where[] = 'd.status = ?';
            $params[] = $filters['status'];
        }
        
        // فیلتر بر اساس کاربر
        if (!empty($filters['user_id'])) {
            $where[] = 'd.user_id = ?';
            $params[] = (int)$filters['user_id'];
        }
        
        // فیلتر بر اساس درگاه
        if (!empty($filters['gateway'])) {
            $where[] = 'd.gateway = ?';
            $params[] = $filters['gateway'];
        }
        
        // فیلتر مبلغ
        if (!empty($filters['min_amount'])) {
            $where[] = 'd.amount >= ?';
            $params[] = (int)$filters['min_amount'];
        }
        
        if (!empty($filters['max_amount'])) {
            $where[] = 'd.amount <= ?';
            $params[] = (int)$filters['max_amount'];
        }
        
        // فیلتر تاریخ
        if (!empty($filters['date_from'])) {
            $where[] = 'd.created_at >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'd.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // جستجو بر اساس ref_id
        if (!empty($filters['search'])) {
            $where[] = '(d.ref_id LIKE ? OR d.transaction_id LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        // مرتب‌سازی
        $sortField = $this->validateSortField($filters['sort'] ?? 'created_at');
        $sortOrder = strtoupper($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        
        $whereStr = implode(' AND ', $where);
        
        // شمارش
        $countSql = "SELECT COUNT(*) FROM donations d WHERE {$whereStr}";
        $total = (int)$this->db->fetchColumn($countSql, $params);
        
        // Pagination
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // دریافت دونیت‌ها
        $sql = "SELECT 
                    d.*,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.is_vip
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE {$whereStr}
                ORDER BY d.{$sortField} {$sortOrder}
                LIMIT {$perPage} OFFSET {$offset}";
        
        $donations = $this->db->fetchAll($sql, $params);
        
        foreach ($donations as &$donation) {
            $donation = $this->formatDonation($donation);
        }
        
        return [
            'data' => $donations,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
    
    public function getById($donationId) {
        $sql = "SELECT 
                    d.*,
                    u.first_name,
                    u.last_name,
                    u.username,
                    u.is_vip,
                    u.phone
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = ?";
        
        $donation = $this->db->fetch($sql, [$donationId]);
        return $donation ? $this->formatDonation($donation) : null;
    }
    
    public function getByRefId($refId) {
        $sql = "SELECT 
                    d.*,
                    u.first_name,
                    u.last_name,
                    u.username
                FROM donations d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.ref_id = ?";
        
        $donation = $this->db->fetch($sql, [$refId]);
        return $donation ? $this->formatDonation($donation) : null;
    }
    
    // ══════════════════════════════════════
    // مدیریت وضعیت
    // ══════════════════════════════════════
    
    public function approve($donationId, $transactionId = null) {
        $donation = $this->db->fetch("SELECT * FROM donations WHERE id = ?", [$donationId]);
        
        if (!$donation) {
            return ['success' => false, 'error' => 'دونیت یافت نشد'];
        }
        
        if ($donation['status'] === 'success') {
            return ['success' => false, 'error' => 'این دونیت قبلاً تأیید شده'];
        }
        
        $updateData = [
            'status' => 'success',
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($transactionId) {
            $updateData['transaction_id'] = $transactionId;
        }
        
        $this->db->update('donations', $updateData, 'id = ?', [$donationId]);
        
        // بررسی VIP شدن خودکار
        $this->checkAutoVip($donation['user_id']);
        
        // ارسال پیام تبریک به کاربر
        $this->sendThankYouMessage($donation['user_id'], $donation['amount']);
        
        // پاک کردن کش
        $this->clearRelatedCache($donation['user_id']);
        
        $this->logger->info('Donation approved', [
            'donation_id' => $donationId,
            'user_id' => $donation['user_id'],
            'amount' => $donation['amount']
        ]);
        
        return ['success' => true];
    }
    
    public function reject($donationId, $reason = '') {
        $donation = $this->db->fetch("SELECT * FROM donations WHERE id = ?", [$donationId]);
        
        if (!$donation) {
            return ['success' => false, 'error' => 'دونیت یافت نشد'];
        }
        
        $this->db->update('donations', [
            'status' => 'failed',
            'reject_reason' => $reason,
            'rejected_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$donationId]);
        
        $this->clearRelatedCache($donation['user_id']);
        
        $this->logger->warning('Donation rejected', [
            'donation_id' => $donationId,
            'reason' => $reason
        ]);
        
        return ['success' => true];
    }
    
    public function markAsPending($donationId) {
        $this->db->update('donations', [
            'status' => 'pending'
        ], 'id = ?', [$donationId]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // آمار دونیت‌ها
    // ══════════════════════════════════════
    
    public function getStatistics($days = 30) {
        $cacheKey = "donations_stats_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            // آمار کلی
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
            
            // وضعیت‌ها
            $pending = $this->db->fetchColumn("SELECT COUNT(*) FROM donations WHERE status = 'pending'");
            $failed = $this->db->fetchColumn("SELECT COUNT(*) FROM donations WHERE status = 'failed'");
            
            // میانگین و بیشترین
            $average = $totalCount > 0 ? $totalAmount / $totalCount : 0;
            $maxDonation = $this->db->fetchColumn(
                "SELECT COALESCE(MAX(amount), 0) FROM donations WHERE status = 'success'"
            );
            $minDonation = $this->db->fetchColumn(
                "SELECT COALESCE(MIN(amount), 0) FROM donations WHERE status = 'success' AND amount > 0"
            );
            
            // آمار روزانه
            $daily = $this->db->fetchAll(
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
            
            // آمار ساعتی (امروز)
            $hourly = $this->db->fetchAll(
                "SELECT 
                    HOUR(created_at) as hour,
                    SUM(amount) as total_amount,
                    COUNT(*) as count
                FROM donations 
                WHERE status = 'success'
                AND DATE(created_at) = CURDATE()
                GROUP BY HOUR(created_at)
                ORDER BY hour ASC"
            );
            
            // آمار بر اساس درگاه
            $byGateway = $this->db->fetchAll(
                "SELECT 
                    gateway,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM donations
                WHERE status = 'success'
                GROUP BY gateway
                ORDER BY total_amount DESC"
            );
            
            // آمار مبالغ (توزیع)
            $amountRanges = [
                ['label' => 'زیر ۱۰ هزار', 'min' => 0, 'max' => 10000],
                ['label' => '۱۰ تا ۵۰ هزار', 'min' => 10000, 'max' => 50000],
                ['label' => '۵۰ تا ۱۰۰ هزار', 'min' => 50000, 'max' => 100000],
                ['label' => '۱۰۰ تا ۵۰۰ هزار', 'min' => 100000, 'max' => 500000],
                ['label' => 'بالای ۵۰۰ هزار', 'min' => 500000, 'max' => 999999999]
            ];
            
            foreach ($amountRanges as &$range) {
                $range['count'] = $this->db->fetchColumn(
                    "SELECT COUNT(*) FROM donations WHERE status = 'success' AND amount >= ? AND amount < ?",
                    [$range['min'], $range['max']]
                );
            }
            
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
                'max_donation' => (int)$maxDonation,
                'min_donation' => (int)$minDonation,
                'daily' => $daily,
                'hourly' => $hourly,
                'by_gateway' => $byGateway,
                'amount_ranges' => $amountRanges
            ];
        });
    }
    
    // ══════════════════════════════════════
    // برترین حامیان
    // ══════════════════════════════════════
    
    public function getTopDonors($limit = 10, $days = null) {
        $cacheKey = "top_donors_{$limit}_" . ($days ?? 'all');
        
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
                        u.is_vip,
                        COUNT(d.id) as donation_count,
                        SUM(d.amount) as total_amount,
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
                $donor['total_amount_formatted'] = number_format($donor['total_amount']);
                $donor['max_donation_formatted'] = number_format($donor['max_donation']);
                $donor['average'] = $donor['donation_count'] > 0 
                    ? (int)($donor['total_amount'] / $donor['donation_count']) 
                    : 0;
            }
            
            return $donors;
        });
    }
    
    // ══════════════════════════════════════
    // نمودارها
    // ══════════════════════════════════════
    
    public function getChartData($days = 30) {
        $cacheKey = "donations_chart_{$days}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($days) {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        SUM(amount) as total_amount,
                        COUNT(*) as count,
                        AVG(amount) as average
                    FROM donations 
                    WHERE status = 'success'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $results = $this->db->fetchAll($sql, [$days]);
            
            // پر کردن روزهای خالی
            $chartData = $this->fillDateGaps($results, $days);
            
            return [
                'labels' => array_column($chartData, 'date'),
                'datasets' => [
                    [
                        'label' => 'مبلغ دونیت (تومان)',
                        'data' => array_column($chartData, 'total_amount'),
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
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
    
    private function fillDateGaps($results, $days) {
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
                $filled[] = [
                    'date' => $date,
                    'total_amount' => 0,
                    'count' => 0,
                    'average' => 0
                ];
            }
        }
        
        return $filled;
    }
    
    // ══════════════════════════════════════
    // ثبت دونیت جدید
    // ══════════════════════════════════════
    
    public function create($userId, $amount, $gateway = 'manual', $refId = null) {
        // اعتبارسنجی
        if ($amount <= 0) {
            return ['success' => false, 'error' => 'مبلغ باید بیشتر از صفر باشد'];
        }
        
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            return ['success' => false, 'error' => 'کاربر یافت نشد'];
        }
        
        // تولید ref_id اگر داده نشده
        if (!$refId) {
            $refId = 'DON-' . time() . '-' . rand(1000, 9999);
        }
        
        $donationId = $this->db->insert('donations', [
            'user_id' => $userId,
            'amount' => (int)$amount,
            'gateway' => $gateway,
            'ref_id' => $refId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($donationId) {
            $this->logger->info('Donation created', [
                'donation_id' => $donationId,
                'user_id' => $userId,
                'amount' => $amount,
                'gateway' => $gateway
            ]);
            
            return [
                'success' => true,
                'donation_id' => $donationId,
                'ref_id' => $refId
            ];
        }
        
        return ['success' => false, 'error' => 'خطا در ثبت دونیت'];
    }
    
    // ══════════════════════════════════════
    // VIP خودکار
    // ══════════════════════════════════════
    
    private function checkAutoVip($userId) {
        // محاسبه مجموع دونیت‌های کاربر
        $totalDonations = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE user_id = ? AND status = 'success'",
            [$userId]
        );
        
        // آستانه VIP (مثلاً ۱۰۰ هزار تومان)
        $vipThreshold = 100000;
        
        if ($totalDonations >= $vipThreshold) {
            $user = $this->db->fetch("SELECT is_vip FROM users WHERE id = ?", [$userId]);
            
            if ($user && !$user['is_vip']) {
                $this->db->update('users', ['is_vip' => 1], 'id = ?', [$userId]);
                
                // ارسال پیام تبریک VIP
                $this->bot->sendMessage($userId, 
                    "🎉 <b>تبریک!</b>\n\n" .
                    "شما به خاطر حمایت‌های ارزشمندتون، به باشگاه <b>VIP</b> ما پیوستید! 👑\n\n" .
                    "از مزایای ویژه VIP لذت ببرید:\n" .
                    "✅ دسترسی زودتر به ویدئوها\n" .
                    "✅ محتوای اختصاصی\n" .
                    "✅ پشتیبانی ویژه\n\n" .
                    "💖 ممنون از حمایت شما!"
                );
                
                $this->logger->info('User auto-upgraded to VIP', [
                    'user_id' => $userId,
                    'total_donations' => $totalDonations
                ]);
            }
        }
    }
    
    private function sendThankYouMessage($userId, $amount) {
        $formattedAmount = number_format($amount);
        
        $this->bot->sendMessage($userId, 
            "💖 <b>ممنون از حمایت شما!</b>\n\n" .
            "دونیت <b>{$formattedAmount} تومان</b> شما با موفقیت دریافت شد ✅\n\n" .
            "حمایت شما به ما کمک می‌کنه تا محتوای بهتری تولید کنیم 🙏\n\n" .
            "🎬 منتظر ویدئوهای جدید ما باشید!"
        );
    }
    
    // ══════════════════════════════════════
    // Export
    // ══════════════════════════════════════
    
    public function exportCsv(array $filters = []) {
        $result = $this->getAll($filters, 1, 100000);
        $donations = $result['data'];
        
        $filename = 'donations_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        $fp = fopen($filepath, 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        fputcsv($fp, [
            'ID', 'کاربر', 'یوزرنیم', 'مبلغ (تومان)', 'درگاه', 
            'وضعیت', 'Ref ID', 'Transaction ID', 'تاریخ'
        ]);
        
        foreach ($donations as $d) {
            fputcsv($fp, [
                $d['id'],
                $d['user_display_name'] ?? '',
                $d['username'] ?? '',
                $d['amount'],
                $d['gateway'] ?? '',
                $this->getStatusText($d['status']),
                $d['ref_id'] ?? '',
                $d['transaction_id'] ?? '',
                $d['created_at']
            ]);
        }
        
        fclose($fp);
        
        $this->logger->info('Donations exported', [
            'count' => count($donations),
            'file' => $filename
        ]);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($donations)
        ];
    }
    
    public function exportFinancialReport($from, $to) {
        $donations = $this->db->fetchAll(
            "SELECT d.*, u.first_name, u.last_name, u.username
             FROM donations d
             LEFT JOIN users u ON d.user_id = u.id
             WHERE d.status = 'success'
             AND d.created_at BETWEEN ? AND ?
             ORDER BY d.created_at ASC",
            [$from, $to . ' 23:59:59']
        );
        
        $totalAmount = array_sum(array_column($donations, 'amount'));
        $totalCount = count($donations);
        $average = $totalCount > 0 ? $totalAmount / $totalCount : 0;
        
        $report = [
            'report_title' => 'گزارش مالی دونیت‌ها',
            'from_date' => $from,
            'to_date' => $to,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_amount' => $totalAmount,
                'total_count' => $totalCount,
                'average_amount' => (int)$average,
                'max_amount' => !empty($donations) ? max(array_column($donations, 'amount')) : 0,
                'min_amount' => !empty($donations) ? min(array_column($donations, 'amount')) : 0
            ],
            'donations' => $donations
        ];
        
        $filename = 'financial_report_' . $from . '_to_' . $to . '.json';
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
            'filepath' => $filepath,
            'summary' => $report['summary']
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    private function formatDonation($donation) {
        // نام نمایشی کاربر
        $donation['user_display_name'] = $this->formatDisplayName($donation);
        
        // فرمت مبلغ
        $donation['amount_formatted'] = number_format($donation['amount']);
        
        // وضعیت
        $donation['status_text'] = $this->getStatusText($donation['status']);
        $donation['status_color'] = $this->getStatusColor($donation['status']);
        $donation['status_icon'] = $this->getStatusIcon($donation['status']);
        
        // زمان نسبی
        if (!empty($donation['created_at'])) {
            $donation['time_ago'] = $this->timeAgo($donation['created_at']);
        }
        
        // آیکون درگاه
        $gatewayIcons = [
            'zarinpal' => '💳',
            'idpay' => '💰',
            'nextpay' => '🏦',
            'manual' => '✋',
            'crypto' => '₿'
        ];
        $donation['gateway_icon'] = $gatewayIcons[$donation['gateway'] ?? 'manual'] ?? '💵';
        
        return $donation;
    }
    
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
        
        return 'کاربر #' . ($user['user_id'] ?? $user['id'] ?? '?');
    }
    
    private function getStatusText($status) {
        return match($status) {
            'success' => 'موفق',
            'pending' => 'در انتظار',
            'failed' => 'ناموفق',
            default => 'نامشخص'
        };
    }
    
    private function getStatusColor($status) {
        return match($status) {
            'success' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            default => 'gray'
        };
    }
    
    private function getStatusIcon($status) {
        return match($status) {
            'success' => '✅',
            'pending' => '⏳',
            'failed' => '❌',
            default => '❓'
        };
    }
    
    private function validateSortField($field) {
        $allowed = ['id', 'amount', 'created_at', 'status', 'gateway'];
        return in_array($field, $allowed) ? $field : 'created_at';
    }
    
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) return 'لحظاتی پیش';
        if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
        if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
        if ($diff < 604800) return floor($diff / 86400) . ' روز پیش';
        return date('Y-m-d', $time);
    }
    
    private function clearRelatedCache($userId) {
        $this->cache->delete("user_{$userId}_full");
        $this->cache->delete("user_{$userId}_stats");
        
        for ($i = 7; $i <= 90; $i += 7) {
            $this->cache->delete("donations_stats_{$i}");
            $this->cache->delete("donations_chart_{$i}");
        }
        
        for ($i = 5; $i <= 20; $i += 5) {
            $this->cache->delete("top_donors_{$i}_all");
            $this->cache->delete("top_donors_{$i}_30");
        }
        
        $this->cache->delete('dashboard_full_stats');
        $this->cache->delete('dashboard_donation_stats');
    }
    
    public function clearCache() {
        $this->cache->clear();
        $this->logger->info('Donations cache cleared');
        return true;
    }
}