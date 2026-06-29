<?php
/**
 * ============================================
 * کلاس مدیریت کلمات کلیدی (Keywords)
 * ============================================
 * CRUD کلمات کلیدی
 * جستجو و فیلتر
 * آمار تطابق‌ها
 * تست کلمه کلیدی
 * Import/Export
 * اولویت‌بندی بر اساس طول
 * پشتیبانی از انواع پاسخ
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;

class Keywords {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $cacheTtl = 300;
    
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
    // دریافت لیست کلمات کلیدی
    // ══════════════════════════════════════
    
    public function getAll(array $filters = [], $page = 1, $perPage = 20) {
        $where = ['1=1'];
        $params = [];
        
        // فیلتر وضعیت
        if (isset($filters['active']) && $filters['active'] !== '') {
            $where[] = 'k.active = ?';
            $params[] = (int)$filters['active'];
        }
        
        // فیلتر نوع پاسخ
        if (!empty($filters['answer_type'])) {
            $where[] = 'k.answer_type = ?';
            $params[] = $filters['answer_type'];
        }
        
        // جستجو
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = '(k.keyword LIKE ? OR k.answer LIKE ?)';
            $params[] = $search;
            $params[] = $search;
        }
        
        // مرتب‌سازی
        $sortField = $this->validateSortField($filters['sort'] ?? 'priority');
        $sortOrder = strtoupper($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        
        $whereStr = implode(' AND ', $where);
        
        // شمارش
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM keywords k WHERE {$whereStr}",
            $params
        );
        
        // Pagination
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // دریافت
        $sql = "SELECT 
                    k.*,
                    COALESCE(m.match_count, 0) as match_count,
                    m.last_matched
                FROM keywords k
                LEFT JOIN (
                    SELECT keyword_id, COUNT(*) as match_count, MAX(matched_at) as last_matched
                    FROM keyword_matches
                    GROUP BY keyword_id
                ) m ON k.id = m.keyword_id
                WHERE {$whereStr}
                ORDER BY k.{$sortField} {$sortOrder}, k.id DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $keywords = $this->db->fetchAll($sql, $params);
        
        foreach ($keywords as &$kw) {
            $kw = $this->formatKeyword($kw);
        }
        
        return [
            'data' => $keywords,
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
    
    public function getById($id) {
        $sql = "SELECT 
                    k.*,
                    COALESCE(m.match_count, 0) as match_count,
                    m.last_matched
                FROM keywords k
                LEFT JOIN (
                    SELECT keyword_id, COUNT(*) as match_count, MAX(matched_at) as last_matched
                    FROM keyword_matches
                    GROUP BY keyword_id
                ) m ON k.id = m.keyword_id
                WHERE k.id = ?";
        
        $kw = $this->db->fetch($sql, [$id]);
        return $kw ? $this->formatKeyword($kw) : null;
    }
    
    // ══════════════════════════════════════
    // CRUD
    // ══════════════════════════════════════
    
    public function create(array $data) {
        // اعتبارسنجی
        if (empty($data['keyword'])) {
            return ['success' => false, 'error' => 'کلمه کلیدی الزامی است'];
        }
        
        if (empty($data['answer'])) {
            return ['success' => false, 'error' => 'پاسخ الزامی است'];
        }
        
        // بررسی تکراری نبودن
        $exists = $this->db->fetch(
            "SELECT id FROM keywords WHERE keyword = ?",
            [$data['keyword']]
        );
        
        if ($exists) {
            return ['success' => false, 'error' => 'این کلمه کلیدی قبلاً ثبت شده است'];
        }
        
        // تنظیم priority بر اساس طول کلمه (کلمات طولانی‌تر = اولویت بالاتر)
        $priority = $data['priority'] ?? mb_strlen($data['keyword']) * 10;
        
        $id = $this->db->insert('keywords', [
            'keyword' => trim($data['keyword']),
            'answer' => trim($data['answer']),
            'answer_type' => $data['answer_type'] ?? 'text',
            'file_id' => $data['file_id'] ?? null,
            'priority' => (int)$priority,
            'active' => isset($data['active']) ? (int)$data['active'] : 1,
            'case_sensitive' => isset($data['case_sensitive']) ? (int)$data['case_sensitive'] : 0,
            'exact_match' => isset($data['exact_match']) ? (int)$data['exact_match'] : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($id) {
            $this->clearKeywordsCache();
            
            $this->logger->info('Keyword created', [
                'keyword_id' => $id,
                'keyword' => $data['keyword']
            ]);
            
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'خطا در ایجاد کلمه کلیدی'];
    }
    
    public function update($id, array $data) {
        $keyword = $this->db->fetch("SELECT * FROM keywords WHERE id = ?", [$id]);
        
        if (!$keyword) {
            return ['success' => false, 'error' => 'کلمه کلیدی یافت نشد'];
        }
        
        $allowedFields = [
            'keyword', 'answer', 'answer_type', 'file_id', 
            'priority', 'active', 'case_sensitive', 'exact_match'
        ];
        
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateData[$key] = $value;
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'هیچ فیلدی برای بروزرسانی وجود ندارد'];
        }
        
        // بررسی تکراری نبودن کلمه کلیدی جدید
        if (isset($updateData['keyword']) && $updateData['keyword'] !== $keyword['keyword']) {
            $exists = $this->db->fetch(
                "SELECT id FROM keywords WHERE keyword = ? AND id != ?",
                [$updateData['keyword'], $id]
            );
            
            if ($exists) {
                return ['success' => false, 'error' => 'این کلمه کلیدی قبلاً ثبت شده است'];
            }
        }
        
        $this->db->update('keywords', $updateData, 'id = ?', [$id]);
        
        $this->clearKeywordsCache();
        
        $this->logger->info('Keyword updated', [
            'keyword_id' => $id,
            'fields' => array_keys($updateData)
        ]);
        
        return ['success' => true];
    }
    
    public function delete($id) {
        $keyword = $this->db->fetch("SELECT * FROM keywords WHERE id = ?", [$id]);
        
        if (!$keyword) {
            return ['success' => false, 'error' => 'کلمه کلیدی یافت نشد'];
        }
        
        // حذف آمار تطابق‌ها
        $this->db->delete('keyword_matches', 'keyword_id = ?', [$id]);
        
        // حذف کلمه کلیدی
        $this->db->delete('keywords', 'id = ?', [$id]);
        
        $this->clearKeywordsCache();
        
        $this->logger->warning('Keyword deleted', [
            'keyword_id' => $id,
            'keyword' => $keyword['keyword']
        ]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // تغییر وضعیت
    // ══════════════════════════════════════
    
    public function toggle($id) {
        $keyword = $this->db->fetch("SELECT active FROM keywords WHERE id = ?", [$id]);
        
        if (!$keyword) {
            return ['success' => false, 'error' => 'کلمه کلیدی یافت نشد'];
        }
        
        $newStatus = $keyword['active'] ? 0 : 1;
        
        $this->db->update('keywords', ['active' => $newStatus], 'id = ?', [$id]);
        
        $this->clearKeywordsCache();
        
        return ['success' => true, 'active' => $newStatus];
    }
    
    public function activate($id) {
        $this->db->update('keywords', ['active' => 1], 'id = ?', [$id]);
        $this->clearKeywordsCache();
        return ['success' => true];
    }
    
    public function deactivate($id) {
        $this->db->update('keywords', ['active' => 0], 'id = ?', [$id]);
        $this->clearKeywordsCache();
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // عملیات دسته‌جمعی
    // ══════════════════════════════════════
    
    public function bulkAction(array $ids, $action) {
        if (empty($ids)) {
            return ['success' => false, 'error' => 'هیچ کلمه‌ای انتخاب نشده'];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        switch ($action) {
            case 'activate':
                $sql = "UPDATE keywords SET active = 1 WHERE id IN ({$placeholders})";
                break;
            case 'deactivate':
                $sql = "UPDATE keywords SET active = 0 WHERE id IN ({$placeholders})";
                break;
            case 'delete':
                // حذف آمار تطابق‌ها
                $this->db->query(
                    "DELETE FROM keyword_matches WHERE keyword_id IN ({$placeholders})",
                    $ids
                );
                $sql = "DELETE FROM keywords WHERE id IN ({$placeholders})";
                break;
            default:
                return ['success' => false, 'error' => 'عملیات نامعتبر'];
        }
        
        $result = $this->db->query($sql, $ids);
        $affected = $result ? $result->rowCount() : 0;
        
        $this->clearKeywordsCache();
        
        $this->logger->info('Keywords bulk action', [
            'action' => $action,
            'count' => count($ids),
            'affected' => $affected
        ]);
        
        return ['success' => true, 'affected' => $affected];
    }
    
    // ══════════════════════════════════════
    // تطابق کلمه کلیدی (برای ربات)
    // ══════════════════════════════════════
    
    /**
     * پیدا کردن کلمه کلیدی منطبق در متن
     */
    public function findMatch($text) {
        if (empty($text)) {
            return null;
        }
        
        // استفاده از کش برای performance
        $cacheKey = 'active_keywords_list';
        
        $keywords = $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            return $this->db->fetchAll(
                "SELECT * FROM keywords WHERE active = 1 ORDER BY priority DESC, LENGTH(keyword) DESC"
            );
        });
        
        foreach ($keywords as $kw) {
            $matched = false;
            
            if ($kw['exact_match']) {
                // تطابق دقیق
                if ($kw['case_sensitive']) {
                    $matched = ($text === $kw['keyword']);
                } else {
                    $matched = (mb_strtolower($text) === mb_strtolower($kw['keyword']));
                }
            } else {
                // تطابق بخشی
                if ($kw['case_sensitive']) {
                    $matched = (mb_strpos($text, $kw['keyword']) !== false);
                } else {
                    $matched = (mb_stripos($text, $kw['keyword']) !== false);
                }
            }
            
            if ($matched) {
                // ثبت آمار تطابق
                $this->logMatch($kw['id']);
                
                return $kw;
            }
        }
        
        return null;
    }
    
    /**
     * ثبت آمار تطابق
     */
    private function logMatch($keywordId) {
        try {
            $this->db->insert('keyword_matches', [
                'keyword_id' => $keywordId,
                'matched_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // اگر جدول keyword_matches وجود نداشت، نادیده بگیر
            $this->logger->debug('Keyword match log failed', [
                'keyword_id' => $keywordId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // ══════════════════════════════════════
    // تست کلمه کلیدی
    // ══════════════════════════════════════
    
    /**
     * تست یک متن با کلمات کلیدی (بدون ثبت آمار)
     */
    public function testMatch($text) {
        if (empty($text)) {
            return ['matched' => false, 'keyword' => null];
        }
        
        $keywords = $this->db->fetchAll(
            "SELECT * FROM keywords WHERE active = 1 ORDER BY priority DESC, LENGTH(keyword) DESC"
        );
        
        $matches = [];
        
        foreach ($keywords as $kw) {
            $matched = false;
            
            if ($kw['exact_match']) {
                if ($kw['case_sensitive']) {
                    $matched = ($text === $kw['keyword']);
                } else {
                    $matched = (mb_strtolower($text) === mb_strtolower($kw['keyword']));
                }
            } else {
                if ($kw['case_sensitive']) {
                    $matched = (mb_strpos($text, $kw['keyword']) !== false);
                } else {
                    $matched = (mb_stripos($text, $kw['keyword']) !== false);
                }
            }
            
            if ($matched) {
                $matches[] = $kw;
            }
        }
        
        return [
            'matched' => !empty($matches),
            'first_match' => $matches[0] ?? null,
            'all_matches' => $matches,
            'count' => count($matches)
        ];
    }
    
    // ══════════════════════════════════════
    // آمار
    // ══════════════════════════════════════
    
    public function getStatistics() {
        $cacheKey = 'keywords_statistics';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            $total = $this->db->fetchColumn("SELECT COUNT(*) FROM keywords");
            $active = $this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 1");
            $inactive = $this->db->fetchColumn("SELECT COUNT(*) FROM keywords WHERE active = 0");
            
            // آمار بر اساس نوع پاسخ
            $byType = $this->db->fetchAll(
                "SELECT answer_type, COUNT(*) as count FROM keywords GROUP BY answer_type ORDER BY count DESC"
            );
            
            // پرکاربردترین کلمات
            $topKeywords = $this->db->fetchAll(
                "SELECT 
                    k.id,
                    k.keyword,
                    k.answer,
                    COUNT(m.id) as match_count,
                    MAX(m.matched_at) as last_matched
                FROM keywords k
                LEFT JOIN keyword_matches m ON k.id = m.keyword_id
                GROUP BY k.id
                ORDER BY match_count DESC
                LIMIT 10"
            );
            
            // کل تطابق‌ها
            $totalMatches = 0;
            try {
                $totalMatches = $this->db->fetchColumn("SELECT COUNT(*) FROM keyword_matches");
            } catch (\Exception $e) {
                // جدول وجود ندارد
            }
            
            return [
                'total' => (int)$total,
                'active' => (int)$active,
                'inactive' => (int)$inactive,
                'by_type' => $byType,
                'top_keywords' => $topKeywords,
                'total_matches' => (int)$totalMatches
            ];
        });
    }
    
    /**
     * آمار یک کلمه کلیدی خاص
     */
    public function getKeywordStats($keywordId) {
        $keyword = $this->db->fetch("SELECT * FROM keywords WHERE id = ?", [$keywordId]);
        
        if (!$keyword) {
            return null;
        }
        
        try {
            $matchCount = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM keyword_matches WHERE keyword_id = ?",
                [$keywordId]
            );
            
            $lastMatch = $this->db->fetchColumn(
                "SELECT MAX(matched_at) FROM keyword_matches WHERE keyword_id = ?",
                [$keywordId]
            );
            
            // آمار روزانه (30 روز اخیر)
            $dailyMatches = $this->db->fetchAll(
                "SELECT 
                    DATE(matched_at) as date,
                    COUNT(*) as count
                FROM keyword_matches
                WHERE keyword_id = ?
                AND matched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(matched_at)
                ORDER BY date ASC",
                [$keywordId]
            );
        } catch (\Exception $e) {
            $matchCount = 0;
            $lastMatch = null;
            $dailyMatches = [];
        }
        
        return [
            'keyword' => $keyword,
            'match_count' => (int)$matchCount,
            'last_match' => $lastMatch,
            'daily_matches' => $dailyMatches
        ];
    }
    
    // ══════════════════════════════════════
    // Import / Export
    // ══════════════════════════════════════
    
    public function exportCsv() {
        $keywords = $this->db->fetchAll("SELECT * FROM keywords ORDER BY priority DESC");
        
        $filename = 'keywords_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        $fp = fopen($filepath, 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        fputcsv($fp, [
            'ID', 'کلمه کلیدی', 'پاسخ', 'نوع پاسخ', 'File ID', 
            'اولویت', 'فعال', 'Case Sensitive', 'Exact Match', 'تاریخ ایجاد'
        ]);
        
        foreach ($keywords as $kw) {
            fputcsv($fp, [
                $kw['id'],
                $kw['keyword'],
                $kw['answer'],
                $kw['answer_type'],
                $kw['file_id'] ?? '',
                $kw['priority'],
                $kw['active'] ? 'بله' : 'خیر',
                $kw['case_sensitive'] ? 'بله' : 'خیر',
                $kw['exact_match'] ? 'بله' : 'خیر',
                $kw['created_at']
            ]);
        }
        
        fclose($fp);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($keywords)
        ];
    }
    
    public function exportJson() {
        $keywords = $this->db->fetchAll("SELECT * FROM keywords ORDER BY priority DESC");
        
        $filename = 'keywords_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = dirname(__DIR__, 2) . '/storage/exports/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        file_put_contents(
            $filepath,
            json_encode([
                'exported_at' => date('Y-m-d H:i:s'),
                'total' => count($keywords),
                'keywords' => $keywords
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($keywords)
        ];
    }
    
    /**
     * Import از JSON
     */
    public function importJson($filepath, $overwrite = false) {
        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'فایل یافت نشد'];
        }
        
        $content = file_get_contents($filepath);
        $data = json_decode($content, true);
        
        if (!$data) {
            return ['success' => false, 'error' => 'فایل JSON معتبر نیست'];
        }
        
        $keywords = $data['keywords'] ?? $data;
        
        if (!is_array($keywords)) {
            return ['success' => false, 'error' => 'فرمت فایل نامعتبر است'];
        }
        
        $imported = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($keywords as $kw) {
            if (empty($kw['keyword']) || empty($kw['answer'])) {
                $skipped++;
                continue;
            }
            
            // بررسی تکراری
            $exists = $this->db->fetch(
                "SELECT id FROM keywords WHERE keyword = ?",
                [$kw['keyword']]
            );
            
            if ($exists) {
                if ($overwrite) {
                    $this->update($exists['id'], $kw);
                    $imported++;
                } else {
                    $skipped++;
                }
                continue;
            }
            
            $result = $this->create($kw);
            
            if ($result['success']) {
                $imported++;
            } else {
                $errors[] = $kw['keyword'] . ': ' . $result['error'];
            }
        }
        
        $this->clearKeywordsCache();
        
        $this->logger->info('Keywords imported', [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => count($errors)
        ]);
        
        return [
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    private function formatKeyword($kw) {
        // پیش‌نمایش پاسخ
        if (!empty($kw['answer'])) {
            $kw['answer_preview'] = mb_substr($kw['answer'], 0, 100);
        }
        
        // آیکون نوع پاسخ
        $typeIcons = [
            'text' => '💬',
            'photo' => '🖼️',
            'video' => '🎥',
            'document' => '📄',
            'audio' => '🎵',
            'voice' => '🎤',
            'sticker' => '🎭'
        ];
        $kw['type_icon'] = $typeIcons[$kw['answer_type'] ?? 'text'] ?? '💬';
        $kw['type_text'] = $this->getTypeText($kw['answer_type'] ?? 'text');
        
        // وضعیت
        $kw['status_text'] = $kw['active'] ? 'فعال' : 'غیرفعال';
        $kw['status_color'] = $kw['active'] ? 'green' : 'gray';
        $kw['status_icon'] = $kw['active'] ? '✅' : '⏸️';
        
        // زمان نسبی
        if (!empty($kw['last_matched'])) {
            $kw['last_matched_ago'] = $this->timeAgo($kw['last_matched']);
        }
        
        // فرمت تعداد تطابق
        $kw['match_count_formatted'] = number_format($kw['match_count'] ?? 0);
        
        // ویژگی‌های خاص
        $features = [];
        if (!empty($kw['case_sensitive'])) $features[] = 'حساس به حروف';
        if (!empty($kw['exact_match'])) $features[] = 'تطابق دقیق';
        $kw['features'] = $features;
        
        return $kw;
    }
    
    private function getTypeText($type) {
        return match($type) {
            'text' => 'متن',
            'photo' => 'عکس',
            'video' => 'ویدئو',
            'document' => 'فایل',
            'audio' => 'صدا',
            'voice' => 'ویس',
            'sticker' => 'استیکر',
            default => 'نامشخص'
        };
    }
    
    private function validateSortField($field) {
        $allowed = ['id', 'keyword', 'priority', 'active', 'created_at'];
        return in_array($field, $allowed) ? $field : 'priority';
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
    
    private function clearKeywordsCache() {
        $this->cache->delete('active_keywords_list');
        $this->cache->delete('keywords_statistics');
    }
    
    public function clearCache() {
        $this->clearKeywordsCache();
        $this->logger->info('Keywords cache cleared');
        return true;
    }
}