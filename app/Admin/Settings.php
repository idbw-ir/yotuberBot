<?php
/**
 * ============================================
 * کلاس مدیریت تنظیمات (Settings)
 * ============================================
 * خواندن/نوشتن تنظیمات از دیتابیس
 * دسته‌بندی تنظیمات (عمومی، تلگرام، AI، امنیتی)
 * اعتبارسنجی هوشمند بر اساس نوع
 * کش تنظیمات برای Performance
 * Backup/Restore تنظیمات
 * لاگ تغییرات
 * تنظیمات پیش‌فرض
 */

namespace App\Admin;

use App\Core\Database;
use App\Core\Cache;
use App\Core\Logger;
use App\Helpers\Security;

class Settings {
    private static $instance = null;
    private $db;
    private $cache;
    private $logger;
    private $cacheTtl = 3600; // 1 ساعت
    private $localCache = [];
    
    // ──────────────────────────────────────
    // Constructor (Private - Singleton)
    // ──────────────────────────────────────
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
    // دریافت تنظیمات
    // ══════════════════════════════════════
    
    /**
     * دریافت یک تنظیم
     */
    public function get($key, $default = null) {
        // بررسی Local Cache
        if (isset($this->localCache[$key])) {
            return $this->localCache[$key];
        }
        
        // بررسی Database Cache
        $cacheKey = "setting_{$key}";
        $value = $this->cache->get($cacheKey);
        
        if ($value !== null) {
            $this->localCache[$key] = $value;
            return $value;
        }
        
        // دریافت از دیتابیس
        $row = $this->db->fetch(
            "SELECT value, type FROM settings WHERE key_name = ?",
            [$key]
        );
        
        if (!$row) {
            // بررسی در تنظیمات پیش‌فرض
            $defaults = $this->getDefaults();
            if (isset($defaults[$key])) {
                $value = $defaults[$key]['default'];
                $this->localCache[$key] = $value;
                return $value;
            }
            
            return $default;
        }
        
        // تبدیل نوع
        $value = $this->castValue($row['value'], $row['type'] ?? 'string');
        
        // ذخیره در کش
        $this->cache->set($cacheKey, $value, $this->cacheTtl);
        $this->localCache[$key] = $value;
        
        return $value;
    }
    
    /**
     * دریافت چند تنظیم
     */
    public function getMany(array $keys) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }
    
    /**
     * دریافت تمام تنظیمات یک دسته
     */
    public function getByCategory($category) {
        $cacheKey = "settings_category_{$category}";
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() use ($category) {
            $rows = $this->db->fetchAll(
                "SELECT key_name, value, type, description FROM settings WHERE category = ? ORDER BY sort_order ASC, key_name ASC",
                [$category]
            );
            
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['key_name']] = [
                    'value' => $this->castValue($row['value'], $row['type'] ?? 'string'),
                    'type' => $row['type'],
                    'description' => $row['description']
                ];
            }
            
            return $settings;
        });
    }
    
    /**
     * دریافت تمام تنظیمات
     */
    public function getAll() {
        $cacheKey = 'settings_all';
        
        return $this->cache->remember($cacheKey, $this->cacheTtl, function() {
            $rows = $this->db->fetchAll(
                "SELECT key_name, value, type, category, description, sort_order FROM settings ORDER BY category, sort_order ASC"
            );
            
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['key_name']] = [
                    'value' => $this->castValue($row['value'], $row['type'] ?? 'string'),
                    'type' => $row['type'],
                    'category' => $row['category'],
                    'description' => $row['description']
                ];
            }
            
            return $settings;
        });
    }
    
    /**
     * دریافت تنظیمات گروه‌بندی شده
     */
    public function getGrouped() {
        $all = $this->getAll();
        $grouped = [];
        
        foreach ($all as $key => $data) {
            $category = $data['category'] ?? 'general';
            $grouped[$category][$key] = $data;
        }
        
        return $grouped;
    }
    
    // ══════════════════════════════════════
    // تنظیم مقادیر
    // ══════════════════════════════════════
    
    /**
     * تنظیم یک مقدار
     */
    public function set($key, $value, $options = []) {
        // دریافت اطلاعات فعلی
        $current = $this->db->fetch(
            "SELECT * FROM settings WHERE key_name = ?",
            [$key]
        );
        
        // اعتبارسنجی
        $type = $options['type'] ?? ($current['type'] ?? 'string');
        $validation = $this->validateValue($key, $value, $type, $options);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['error']
            ];
        }
        
        // تبدیل مقدار به string برای ذخیره
        $storedValue = $this->serializeValue($value, $type);
        
        // ذخیره مقدار قبلی برای لاگ
        $oldValue = $current ? $this->castValue($current['value'], $current['type']) : null;
        
        if ($current) {
            // بروزرسانی
            $this->db->update('settings', [
                'value' => $storedValue,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'key_name = ?', [$key]);
        } else {
            // ایجاد جدید
            $this->db->insert('settings', [
                'key_name' => $key,
                'value' => $storedValue,
                'type' => $type,
                'category' => $options['category'] ?? 'general',
                'description' => $options['description'] ?? '',
                'sort_order' => $options['sort_order'] ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // پاک کردن کش
        $this->clearKeyCache($key);
        $this->localCache[$key] = $value;
        
        // لاگ تغییر
        $this->logChange($key, $oldValue, $value);
        
        return ['success' => true];
    }
    
    /**
     * تنظیم چند مقدار همزمان
     */
    public function setMany(array $data) {
        $results = [];
        $errors = [];
        
        $this->db->beginTransaction();
        
        try {
            foreach ($data as $key => $value) {
                $result = $this->set($key, $value);
                
                if ($result['success']) {
                    $results[] = $key;
                } else {
                    $errors[$key] = $result['error'];
                }
            }
            
            if (empty($errors)) {
                $this->db->commit();
                return [
                    'success' => true,
                    'updated' => $results
                ];
            } else {
                $this->db->rollback();
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // ══════════════════════════════════════
    // اعتبارسنجی
    // ══════════════════════════════════════
    
    /**
     * اعتبارسنجی مقدار بر اساس نوع
     */
    private function validateValue($key, $value, $type, $options = []) {
        switch ($type) {
            case 'string':
                if (!is_string($value) && !is_numeric($value)) {
                    return ['valid' => false, 'error' => 'مقدار باید متنی باشد'];
                }
                
                $min = $options['min_length'] ?? null;
                $max = $options['max_length'] ?? null;
                
                if ($min && mb_strlen($value) < $min) {
                    return ['valid' => false, 'error' => "حداقل {$min} کاراکتر لازم است"];
                }
                if ($max && mb_strlen($value) > $max) {
                    return ['valid' => false, 'error' => "حداکثر {$max} کاراکتر مجاز است"];
                }
                break;
                
            case 'integer':
                if (!is_numeric($value) || (int)$value != $value) {
                    return ['valid' => false, 'error' => 'مقدار باید عدد صحیح باشد'];
                }
                
                $min = $options['min'] ?? null;
                $max = $options['max'] ?? null;
                
                if ($min !== null && $value < $min) {
                    return ['valid' => false, 'error' => "حداقل مقدار: {$min}"];
                }
                if ($max !== null && $value > $max) {
                    return ['valid' => false, 'error' => "حداکثر مقدار: {$max}"];
                }
                break;
                
            case 'float':
            case 'number':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'error' => 'مقدار باید عدد باشد'];
                }
                break;
                
            case 'boolean':
                if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                    return ['valid' => false, 'error' => 'مقدار باید boolean باشد'];
                }
                break;
                
            case 'email':
                if (!Security::isValidEmail($value)) {
                    return ['valid' => false, 'error' => 'ایمیل معتبر نیست'];
                }
                break;
                
            case 'url':
                if (!empty($value) && !Security::isValidUrl($value)) {
                    return ['valid' => false, 'error' => 'URL معتبر نیست'];
                }
                break;
                
            case 'telegram_token':
                if (!empty($value) && !Security::isValidTelegramToken($value)) {
                    return ['valid' => false, 'error' => 'توکن تلگرام معتبر نیست'];
                }
                break;
                
            case 'telegram_id':
                if (!empty($value) && !Security::isValidTelegramId($value)) {
                    return ['valid' => false, 'error' => 'آیدی تلگرام معتبر نیست'];
                }
                break;
                
            case 'json':
                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return ['valid' => false, 'error' => 'JSON معتبر نیست'];
                    }
                }
                break;
                
            case 'color':
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                    return ['valid' => false, 'error' => 'رنگ معتبر نیست (مثال: #FF5733)'];
                }
                break;
                
            case 'array':
                if (!is_array($value)) {
                    return ['valid' => false, 'error' => 'مقدار باید آرایه باشد'];
                }
                break;
        }
        
        // اعتبارسنجی سفارشی
        if (isset($options['validation_callback']) && is_callable($options['validation_callback'])) {
            $customResult = call_user_func($options['validation_callback'], $value, $key);
            if ($customResult !== true) {
                return ['valid' => false, 'error' => $customResult];
            }
        }
        
        return ['valid' => true];
    }
    
    // ══════════════════════════════════════
    // تبدیل نوع
    // ══════════════════════════════════════
    
    /**
     * تبدیل مقدار ذخیره شده به نوع صحیح
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'integer':
                return (int)$value;
                
            case 'float':
            case 'number':
                return (float)$value;
                
            case 'boolean':
                return in_array($value, [true, 1, '1', 'true', 'yes'], true);
                
            case 'json':
            case 'array':
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return $decoded !== null ? $decoded : [];
                }
                return is_array($value) ? $value : [];
                
            case 'string':
            default:
                return (string)$value;
        }
    }
    
    /**
     * تبدیل مقدار به string برای ذخیره
     */
    private function serializeValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
                
            case 'json':
            case 'array':
                return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                
            case 'integer':
            case 'float':
            case 'number':
                return (string)$value;
                
            default:
                return (string)$value;
        }
    }
    
    // ══════════════════════════════════════
    // حذف تنظیم
    // ══════════════════════════════════════
    
    /**
     * حذف یک تنظیم
     */
    public function delete($key) {
        $setting = $this->db->fetch("SELECT * FROM settings WHERE key_name = ?", [$key]);
        
        if (!$setting) {
            return ['success' => false, 'error' => 'تنظیم یافت نشد'];
        }
        
        $this->db->delete('settings', 'key_name = ?', [$key]);
        
        $this->clearKeyCache($key);
        unset($this->localCache[$key]);
        
        $this->logger->warning('Setting deleted', ['key' => $key]);
        
        return ['success' => true];
    }
    
    // ══════════════════════════════════════
    // تنظیمات پیش‌فرض
    // ══════════════════════════════════════
    
    /**
     * دریافت تنظیمات پیش‌فرض
     */
    public function getDefaults() {
        return [
            // عمومی
            'site_name' => [
                'default' => 'ربات یوتیوبر',
                'type' => 'string',
                'category' => 'general',
                'description' => 'نام سایت'
            ],
            'site_url' => [
                'default' => '',
                'type' => 'url',
                'category' => 'general',
                'description' => 'آدرس سایت'
            ],
            'timezone' => [
                'default' => 'Asia/Tehran',
                'type' => 'string',
                'category' => 'general',
                'description' => 'منطقه زمانی'
            ],
            
            // تلگرام
            'welcome_text' => [
                'default' => "سلام {first_name} عزیز! 👋\n\nبه ربات ما خوش اومدی 🎬",
                'type' => 'string',
                'category' => 'telegram',
                'description' => 'متن خوش‌آمدگویی'
            ],
            'welcome_photo' => [
                'default' => '',
                'type' => 'string',
                'category' => 'telegram',
                'description' => 'File ID عکس خوش‌آمدگویی'
            ],
            'donate_link' => [
                'default' => '',
                'type' => 'url',
                'category' => 'telegram',
                'description' => 'لینک درگاه حمایت مالی'
            ],
            'donate_text' => [
                'default' => "💰 با حمایت مالی از ما، به تولید محتوای بهتر کمک می‌کنید!",
                'type' => 'string',
                'category' => 'telegram',
                'description' => 'متن صفحه حمایت'
            ],
            'youtube_url' => [
                'default' => '',
                'type' => 'url',
                'category' => 'telegram',
                'description' => 'لینک کانال یوتیوب'
            ],
            
            // هوش مصنوعی
            'ai_enabled' => [
                'default' => false,
                'type' => 'boolean',
                'category' => 'ai',
                'description' => 'فعال‌سازی هوش مصنوعی'
            ],
            'ai_provider' => [
                'default' => 'openai',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'ارائه‌دهنده AI'
            ],
            'ai_api_key' => [
                'default' => '',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'API Key هوش مصنوعی'
            ],
            'ai_model' => [
                'default' => 'gpt-4o-mini',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'مدل AI'
            ],
            'ai_system_prompt' => [
                'default' => 'تو دستیار یک یوتیوبر فارسی‌زبان هستی. دوستانه و کوتاه جواب بده.',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'System Prompt برای AI'
            ],
            
            // VIP
            'vip_threshold' => [
                'default' => 100000,
                'type' => 'integer',
                'category' => 'vip',
                'description' => 'آستانه VIP (تومان)'
            ],
            'vip_badge' => [
                'default' => '👑',
                'type' => 'string',
                'category' => 'vip',
                'description' => 'نشان VIP'
            ],
            
            // امنیتی
            'admin_ip_whitelist' => [
                'default' => [],
                'type' => 'array',
                'category' => 'security',
                'description' => 'لیست IP های مجاز برای پنل ادمین'
            ],
            'login_max_attempts' => [
                'default' => 5,
                'type' => 'integer',
                'category' => 'security',
                'description' => 'حداکثر تلاش ناموفق برای ورود'
            ],
            'session_timeout' => [
                'default' => 3600,
                'type' => 'integer',
                'category' => 'security',
                'description' => 'زمان انقضای Session (ثانیه)'
            ],
            
            // عملکرد
            'cache_ttl' => [
                'default' => 3600,
                'type' => 'integer',
                'category' => 'performance',
                'description' => 'TTL کش پیش‌فرض (ثانیه)'
            ],
            'broadcast_delay' => [
                'default' => 50,
                'type' => 'integer',
                'category' => 'performance',
                'description' => 'تأخیر بین پیام‌های Broadcast (میلی‌ثانیه)'
            ],
        ];
    }
    
    /**
     * نصب تنظیمات پیش‌فرض
     */
    public function installDefaults() {
        $defaults = $this->getDefaults();
        $installed = 0;
        
        foreach ($defaults as $key => $data) {
            $exists = $this->db->fetch(
                "SELECT key_name FROM settings WHERE key_name = ?",
                [$key]
            );
            
            if (!$exists) {
                $this->db->insert('settings', [
                    'key_name' => $key,
                    'value' => $this->serializeValue($data['default'], $data['type']),
                    'type' => $data['type'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $installed++;
            }
        }
        
        $this->clearCache();
        
        $this->logger->info('Default settings installed', ['count' => $installed]);
        
        return [
            'success' => true,
            'installed' => $installed
        ];
    }
    
    /**
     * ریست یک تنظیم به پیش‌فرض
     */
    public function resetToDefault($key) {
        $defaults = $this->getDefaults();
        
        if (!isset($defaults[$key])) {
            return ['success' => false, 'error' => 'تنظیم پیش‌فرضی برای این کلید وجود ندارد'];
        }
        
        return $this->set($key, $defaults[$key]['default']);
    }
    
    /**
     * ریست همه تنظیمات به پیش‌فرض
     */
    public function resetAllToDefault() {
        $defaults = $this->getDefaults();
        $reset = 0;
        
        foreach ($defaults as $key => $data) {
            $result = $this->set($key, $data['default']);
            if ($result['success']) {
                $reset++;
            }
        }
        
        return [
            'success' => true,
            'reset' => $reset
        ];
    }
    
    // ══════════════════════════════════════
    // Backup / Restore
    // ══════════════════════════════════════
    
    /**
     * Backup تنظیمات
     */
    public function backup() {
        $settings = $this->db->fetchAll("SELECT * FROM settings ORDER BY category, key_name");
        
        $backup = [
            'version' => '2.1.0',
            'created_at' => date('Y-m-d H:i:s'),
            'total_settings' => count($settings),
            'settings' => $settings
        ];
        
        $filename = 'settings_backup_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = dirname(__DIR__, 2) . '/storage/backups/' . $filename;
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        file_put_contents(
            $filepath,
            json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        
        $this->logger->info('Settings backup created', [
            'file' => $filename,
            'count' => count($settings)
        ]);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'count' => count($settings)
        ];
    }
    
    /**
     * Restore تنظیمات از Backup
     */
    public function restore($filepath, $overwrite = false) {
        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'فایل Backup یافت نشد'];
        }
        
        $content = file_get_contents($filepath);
        $backup = json_decode($content, true);
        
        if (!$backup || !isset($backup['settings'])) {
            return ['success' => false, 'error' => 'فایل Backup معتبر نیست'];
        }
        
        $restored = 0;
        $skipped = 0;
        
        $this->db->beginTransaction();
        
        try {
            foreach ($backup['settings'] as $setting) {
                $exists = $this->db->fetch(
                    "SELECT key_name FROM settings WHERE key_name = ?",
                    [$setting['key_name']]
                );
                
                if ($exists && !$overwrite) {
                    $skipped++;
                    continue;
                }
                
                if ($exists) {
                    $this->db->update('settings', [
                        'value' => $setting['value'],
                        'type' => $setting['type'],
                        'category' => $setting['category'],
                        'description' => $setting['description'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'key_name = ?', [$setting['key_name']]);
                } else {
                    $this->db->insert('settings', [
                        'key_name' => $setting['key_name'],
                        'value' => $setting['value'],
                        'type' => $setting['type'],
                        'category' => $setting['category'],
                        'description' => $setting['description'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
                
                $restored++;
            }
            
            $this->db->commit();
            $this->clearCache();
            
            $this->logger->info('Settings restored from backup', [
                'file' => basename($filepath),
                'restored' => $restored,
                'skipped' => $skipped
            ]);
            
            return [
                'success' => true,
                'restored' => $restored,
                'skipped' => $skipped
            ];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * لیست Backup های موجود
     */
    public function listBackups() {
        $dir = dirname(__DIR__, 2) . '/storage/backups';
        
        if (!is_dir($dir)) {
            return [];
        }
        
        $files = glob($dir . '/settings_backup_*.json');
        $backups = [];
        
        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true);
            
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'created_at' => $content['created_at'] ?? date('Y-m-d H:i:s', filemtime($file)),
                'total_settings' => $content['total_settings'] ?? 0,
                'size' => filesize($file),
                'size_human' => $this->formatSize(filesize($file))
            ];
        }
        
        // مرتب‌سازی بر اساس زمان (جدیدترین اول)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    // ══════════════════════════════════════
    // لاگ تغییرات
    // ══════════════════════════════════════
    
    /**
     * ثبت لاگ تغییر تنظیم
     */
    private function logChange($key, $oldValue, $newValue) {
        // اطلاعات حساس رو مخفی کن
        $sensitiveKeys = ['ai_api_key', 'bot_token', 'webhook_secret'];
        
        $loggedOld = in_array($key, $sensitiveKeys) ? '***' : $oldValue;
        $loggedNew = in_array($key, $sensitiveKeys) ? '***' : $newValue;
        
        try {
            $this->db->insert('settings_log', [
                'key_name' => $key,
                'old_value' => is_array($loggedOld) ? json_encode($loggedOld) : (string)$loggedOld,
                'new_value' => is_array($loggedNew) ? json_encode($loggedNew) : (string)$loggedNew,
                'changed_by' => \App\Admin\Auth::getInstance()->id(),
                'changed_at' => date('Y-m-d H:i:s'),
                'ip_address' => Security::getClientIp()
            ]);
        } catch (\Exception $e) {
            // اگر جدول لاگ وجود نداشت، نادیده بگیر
        }
        
        $this->logger->info('Setting changed', [
            'key' => $key,
            'old' => $loggedOld,
            'new' => $loggedNew
        ]);
    }
    
    /**
     * دریافت تاریخچه تغییرات
     */
    public function getChangeLog($key = null, $limit = 50) {
        $where = ['1=1'];
        $params = [];
        
        if ($key) {
            $where[] = 'key_name = ?';
            $params[] = $key;
        }
        
        $whereStr = implode(' AND ', $where);
        
        try {
            return $this->db->fetchAll(
                "SELECT 
                    sl.*,
                    a.username as changed_by_username
                FROM settings_log sl
                LEFT JOIN admins a ON sl.changed_by = a.id
                WHERE {$whereStr}
                ORDER BY sl.changed_at DESC
                LIMIT ?",
                array_merge($params, [$limit])
            );
        } catch (\Exception $e) {
            return [];
        }
    }
    
    // ══════════════════════════════════════
    // مدیریت کش
    // ══════════════════════════════════════
    
    /**
     * پاک کردن کش یک کلید
     */
    private function clearKeyCache($key) {
        $this->cache->delete("setting_{$key}");
        
        // پاک کردن کش دسته‌ها
        $categories = ['general', 'telegram', 'ai', 'vip', 'security', 'performance'];
        foreach ($categories as $cat) {
            $this->cache->delete("settings_category_{$cat}");
        }
        
        $this->cache->delete('settings_all');
    }
    
    /**
     * پاک کردن کل کش تنظیمات
     */
    public function clearCache() {
        $this->localCache = [];
        
        // پاک کردن همه کش‌های مربوط به تنظیمات
        $this->cache->clear();
        
        $this->logger->info('Settings cache cleared');
        
        return true;
    }
    
    // ══════════════════════════════════════
    // متدهای کمکی
    // ══════════════════════════════════════
    
    /**
     * فرمت اندازه فایل
     */
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * دریافت لیست دسته‌ها
     */
    public function getCategories() {
        return [
            'general' => ['name' => 'عمومی', 'icon' => '⚙️'],
            'telegram' => ['name' => 'تلگرام', 'icon' => '🤖'],
            'ai' => ['name' => 'هوش مصنوعی', 'icon' => '🧠'],
            'vip' => ['name' => 'باشگاه VIP', 'icon' => '👑'],
            'security' => ['name' => 'امنیتی', 'icon' => '🔒'],
            'performance' => ['name' => 'عملکرد', 'icon' => '⚡'],
            'appearance' => ['name' => 'ظاهر', 'icon' => '🎨'],
            'notifications' => ['name' => 'اعلان‌ها', 'icon' => '🔔']
        ];
    }
    
    /**
     * بررسی وجود یک تنظیم
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * دریافت تنظیم با fallback به config.php
     */
    public function getOrConfig($key, $configKey = null) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        // fallback به config.php
        if ($configKey) {
            return \App\Core\Config::getInstance()->get($configKey);
        }
        
        return null;
    }
}

// ──────────────────────────────────────
// تابع کمکی global
// ──────────────────────────────────────
if (!function_exists('setting')) {
    function setting($key, $default = null) {
        return \App\Admin\Settings::getInstance()->get($key, $default);
    }
}