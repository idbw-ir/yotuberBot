<?php
/**
 * ============================================
 * Ú©Ù„Ø§Ø³ Ù…Ø¯ÛŒØ±ÛŒØª ØªÙ†Ø¸ÛŒÙ…Ø§Øª (Settings)
 * ============================================
 * Ø®ÙˆØ§Ù†Ø¯Ù†/Ù†ÙˆØ´ØªÙ† ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§Ø² Ø¯ÛŒØªØ§Ø¨ÛŒØ³
 * Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ ØªÙ†Ø¸ÛŒÙ…Ø§Øª (Ø¹Ù…ÙˆÙ…ÛŒØŒ ØªÙ„Ú¯Ø±Ø§Ù…ØŒ AIØŒ Ø§Ù…Ù†ÛŒØªÛŒ)
 * Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ Ù‡ÙˆØ´Ù…Ù†Ø¯ Ø¨Ø± Ø§Ø³Ø§Ø³ Ù†ÙˆØ¹
 * Ú©Ø´ ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø¨Ø±Ø§ÛŒ Performance
 * Backup/Restore ØªÙ†Ø¸ÛŒÙ…Ø§Øª
 * Ù„Ø§Ú¯ ØªØºÛŒÛŒØ±Ø§Øª
 * ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
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
    private $cacheTtl = 3600; // 1 Ø³Ø§Ø¹Øª
    private $localCache = [];
    
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // Constructor (Private - Singleton)
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Ø¯Ø±ÛŒØ§ÙØª ØªÙ†Ø¸ÛŒÙ…Ø§Øª
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Ø¯Ø±ÛŒØ§ÙØª ÛŒÚ© ØªÙ†Ø¸ÛŒÙ…
     */
    public function get($key, $default = null) {
        // Ø¨Ø±Ø±Ø³ÛŒ Local Cache
        if (isset($this->localCache[$key])) {
            return $this->localCache[$key];
        }
        
        // Ø¨Ø±Ø±Ø³ÛŒ Database Cache
        $cacheKey = "setting_{$key}";
        $value = $this->cache->get($cacheKey);
        
        if ($value !== null) {
            $this->localCache[$key] = $value;
            return $value;
        }
        
        // Ø¯Ø±ÛŒØ§ÙØª Ø§Ø² Ø¯ÛŒØªØ§Ø¨ÛŒØ³
        $row = $this->db->fetch(
            "SELECT value, type FROM settings WHERE key_name = ?",
            [$key]
        );
        
        if (!$row) {
            // Ø¨Ø±Ø±Ø³ÛŒ Ø¯Ø± ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
            $defaults = $this->getDefaults();
            if (isset($defaults[$key])) {
                $value = $defaults[$key]['default'];
                $this->localCache[$key] = $value;
                return $value;
            }
            
            return $default;
        }
        
        // ØªØ¨Ø¯ÛŒÙ„ Ù†ÙˆØ¹
        $value = $this->castValue($row['value'], $row['type'] ?? 'string');
        
        // Ø°Ø®ÛŒØ±Ù‡ Ø¯Ø± Ú©Ø´
        $this->cache->set($cacheKey, $value, $this->cacheTtl);
        $this->localCache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ú†Ù†Ø¯ ØªÙ†Ø¸ÛŒÙ…
     */
    public function getMany(array $keys) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }
    
    /**
     * Ø¯Ø±ÛŒØ§ÙØª ØªÙ…Ø§Ù… ØªÙ†Ø¸ÛŒÙ…Ø§Øª ÛŒÚ© Ø¯Ø³ØªÙ‡
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
     * Ø¯Ø±ÛŒØ§ÙØª ØªÙ…Ø§Ù… ØªÙ†Ø¸ÛŒÙ…Ø§Øª
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
     * Ø¯Ø±ÛŒØ§ÙØª ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ú¯Ø±ÙˆÙ‡â€ŒØ¨Ù†Ø¯ÛŒ Ø´Ø¯Ù‡
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
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ØªÙ†Ø¸ÛŒÙ… Ù…Ù‚Ø§Ø¯ÛŒØ±
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * ØªÙ†Ø¸ÛŒÙ… ÛŒÚ© Ù…Ù‚Ø¯Ø§Ø±
     */
    public function set($key, $value, $options = []) {
        // Ø¯Ø±ÛŒØ§ÙØª Ø§Ø·Ù„Ø§Ø¹Ø§Øª ÙØ¹Ù„ÛŒ
        $current = $this->db->fetch(
            "SELECT * FROM settings WHERE key_name = ?",
            [$key]
        );
        
        // Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ
        $type = $options['type'] ?? ($current['type'] ?? 'string');
        $validation = $this->validateValue($key, $value, $type, $options);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['error']
            ];
        }
        
        // ØªØ¨Ø¯ÛŒÙ„ Ù…Ù‚Ø¯Ø§Ø± Ø¨Ù‡ string Ø¨Ø±Ø§ÛŒ Ø°Ø®ÛŒØ±Ù‡
        $storedValue = $this->serializeValue($value, $type);
        
        // Ø°Ø®ÛŒØ±Ù‡ Ù…Ù‚Ø¯Ø§Ø± Ù‚Ø¨Ù„ÛŒ Ø¨Ø±Ø§ÛŒ Ù„Ø§Ú¯
        $oldValue = $current ? $this->castValue($current['value'], $current['type']) : null;
        
        if ($current) {
            // Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ
            $this->db->update('settings', [
                'value' => $storedValue,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'key_name = ?', [$key]);
        } else {
            // Ø§ÛŒØ¬Ø§Ø¯ Ø¬Ø¯ÛŒØ¯
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
        
        // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú©Ø´
        $this->clearKeyCache($key);
        $this->localCache[$key] = $value;
        
        // Ù„Ø§Ú¯ ØªØºÛŒÛŒØ±
        $this->logChange($key, $oldValue, $value);
        
        return ['success' => true];
    }
    
    /**
     * ØªÙ†Ø¸ÛŒÙ… Ú†Ù†Ø¯ Ù…Ù‚Ø¯Ø§Ø± Ù‡Ù…Ø²Ù…Ø§Ù†
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
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ Ù…Ù‚Ø¯Ø§Ø± Ø¨Ø± Ø§Ø³Ø§Ø³ Ù†ÙˆØ¹
     */
    private function validateValue($key, $value, $type, $options = []) {
        switch ($type) {
            case 'string':
                if (!is_string($value) && !is_numeric($value)) {
                    return ['valid' => false, 'error' => 'Ù…Ù‚Ø¯Ø§Ø± Ø¨Ø§ÛŒØ¯ Ù…ØªÙ†ÛŒ Ø¨Ø§Ø´Ø¯'];
                }
                
                $min = $options['min_length'] ?? null;
                $max = $options['max_length'] ?? null;
                
                if ($min && mb_strlen($value) < $min) {
                    return ['valid' => false, 'error' => "Ø­Ø¯Ø§Ù‚Ù„ {$min} Ú©Ø§Ø±Ø§Ú©ØªØ± Ù„Ø§Ø²Ù… Ø§Ø³Øª"];
                }
                if ($max && mb_strlen($value) > $max) {
                    return ['valid' => false, 'error' => "Ø­Ø¯Ø§Ú©Ø«Ø± {$max} Ú©Ø§Ø±Ø§Ú©ØªØ± Ù…Ø¬Ø§Ø² Ø§Ø³Øª"];
                }
                break;
                
            case 'integer':
                if (!is_numeric($value) || (int)$value != $value) {
                    return ['valid' => false, 'error' => 'Ù…Ù‚Ø¯Ø§Ø± Ø¨Ø§ÛŒØ¯ Ø¹Ø¯Ø¯ ØµØ­ÛŒØ­ Ø¨Ø§Ø´Ø¯'];
                }
                
                $min = $options['min'] ?? null;
                $max = $options['max'] ?? null;
                
                if ($min !== null && $value < $min) {
                    return ['valid' => false, 'error' => "Ø­Ø¯Ø§Ù‚Ù„ Ù…Ù‚Ø¯Ø§Ø±: {$min}"];
                }
                if ($max !== null && $value > $max) {
                    return ['valid' => false, 'error' => "Ø­Ø¯Ø§Ú©Ø«Ø± Ù…Ù‚Ø¯Ø§Ø±: {$max}"];
                }
                break;
                
            case 'float':
            case 'number':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'error' => 'Ù…Ù‚Ø¯Ø§Ø± Ø¨Ø§ÛŒØ¯ Ø¹Ø¯Ø¯ Ø¨Ø§Ø´Ø¯'];
                }
                break;
                
            case 'boolean':
                if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                    return ['valid' => false, 'error' => 'Ù…Ù‚Ø¯Ø§Ø± Ø¨Ø§ÛŒØ¯ boolean Ø¨Ø§Ø´Ø¯'];
                }
                break;
                
            case 'email':
                if (!Security::isValidEmail($value)) {
                    return ['valid' => false, 'error' => 'Ø§ÛŒÙ…ÛŒÙ„ Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª'];
                }
                break;
                
            case 'url':
                if (!empty($value) && !Security::isValidUrl($value)) {
                    return ['valid' => false, 'error' => 'URL Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª'];
                }
                break;
                
            case 'telegram_token':
                if (!empty($value) && !Security::isValidTelegramToken($value)) {
                    return ['valid' => false, 'error' => 'ØªÙˆÚ©Ù† ØªÙ„Ú¯Ø±Ø§Ù… Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª'];
                }
                break;
                
            case 'telegram_id':
                if (!empty($value) && !Security::isValidTelegramId($value)) {
                    return ['valid' => false, 'error' => 'Ø¢ÛŒØ¯ÛŒ ØªÙ„Ú¯Ø±Ø§Ù… Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª'];
                }
                break;
                
            case 'json':
                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return ['valid' => false, 'error' => 'JSON Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª'];
                    }
                }
                break;
                
            case 'color':
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                    return ['valid' => false, 'error' => 'Ø±Ù†Ú¯ Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª (Ù…Ø«Ø§Ù„: #FF5733)'];
                }
                break;
                
            case 'array':
                if (!is_array($value)) {
                    return ['valid' => false, 'error' => 'Ù…Ù‚Ø¯Ø§Ø± Ø¨Ø§ÛŒØ¯ Ø¢Ø±Ø§ÛŒÙ‡ Ø¨Ø§Ø´Ø¯'];
                }
                break;
        }
        
        // Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ Ø³ÙØ§Ø±Ø´ÛŒ
        if (isset($options['validation_callback']) && is_callable($options['validation_callback'])) {
            $customResult = call_user_func($options['validation_callback'], $value, $key);
            if ($customResult !== true) {
                return ['valid' => false, 'error' => $customResult];
            }
        }
        
        return ['valid' => true];
    }
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ØªØ¨Ø¯ÛŒÙ„ Ù†ÙˆØ¹
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ù…Ù‚Ø¯Ø§Ø± Ø°Ø®ÛŒØ±Ù‡ Ø´Ø¯Ù‡ Ø¨Ù‡ Ù†ÙˆØ¹ ØµØ­ÛŒØ­
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
     * ØªØ¨Ø¯ÛŒÙ„ Ù…Ù‚Ø¯Ø§Ø± Ø¨Ù‡ string Ø¨Ø±Ø§ÛŒ Ø°Ø®ÛŒØ±Ù‡
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
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Ø­Ø°Ù ØªÙ†Ø¸ÛŒÙ…
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Ø­Ø°Ù ÛŒÚ© ØªÙ†Ø¸ÛŒÙ…
     */
    public function delete($key) {
        $setting = $this->db->fetch("SELECT * FROM settings WHERE key_name = ?", [$key]);
        
        if (!$setting) {
            return ['success' => false, 'error' => 'ØªÙ†Ø¸ÛŒÙ… ÛŒØ§ÙØª Ù†Ø´Ø¯'];
        }
        
        $this->db->delete('settings', 'key_name = ?', [$key]);
        
        $this->clearKeyCache($key);
        unset($this->localCache[$key]);
        
        $this->logger->warning('Setting deleted', ['key' => $key]);
        
        return ['success' => true];
    }
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Ø¯Ø±ÛŒØ§ÙØª ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
     */
    public function getDefaults() {
        return [
            // Ø¹Ù…ÙˆÙ…ÛŒ
            'site_name' => [
                'default' => 'Ø±Ø¨Ø§Øª ÛŒÙˆØªÛŒÙˆØ¨Ø±',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Ù†Ø§Ù… Ø³Ø§ÛŒØª'
            ],
            'site_url' => [
                'default' => '',
                'type' => 'url',
                'category' => 'general',
                'description' => 'Ø¢Ø¯Ø±Ø³ Ø³Ø§ÛŒØª'
            ],
            'timezone' => [
                'default' => 'Asia/Tehran',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Ù…Ù†Ø·Ù‚Ù‡ Ø²Ù…Ø§Ù†ÛŒ'
            ],
            
            // ØªÙ„Ú¯Ø±Ø§Ù…
            'welcome_text' => [
                'default' => "Ø³Ù„Ø§Ù… {first_name} Ø¹Ø²ÛŒØ²! ðŸ‘‹\n\nØ¨Ù‡ Ø±Ø¨Ø§Øª Ù…Ø§ Ø®ÙˆØ´ Ø§ÙˆÙ…Ø¯ÛŒ ðŸŽ¬",
                'type' => 'string',
                'category' => 'telegram',
                'description' => 'Ù…ØªÙ† Ø®ÙˆØ´â€ŒØ¢Ù…Ø¯Ú¯ÙˆÛŒÛŒ'
            ],
            'welcome_photo' => [
                'default' => '',
                'type' => 'string',
                'category' => 'telegram',
                'description' => 'File ID Ø¹Ú©Ø³ Ø®ÙˆØ´â€ŒØ¢Ù…Ø¯Ú¯ÙˆÛŒÛŒ'
            ],
            'donate_link' => [
                'default' => '',
                'type' => 'url',
                'category' => 'telegram',
                'description' => 'Ù„ÛŒÙ†Ú© Ø¯Ø±Ú¯Ø§Ù‡ Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒ'
            ],
            'donate_text' => [
                'default' => "ðŸ’° Ø¨Ø§ Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒ Ø§Ø² Ù…Ø§ØŒ Ø¨Ù‡ ØªÙˆÙ„ÛŒØ¯ Ù…Ø­ØªÙˆØ§ÛŒ Ø¨Ù‡ØªØ± Ú©Ù…Ú© Ù…ÛŒâ€ŒÚ©Ù†ÛŒØ¯!",
                'type' => 'string',
                'category' => 'telegram',
                'description' => 'Ù…ØªÙ† ØµÙØ­Ù‡ Ø­Ù…Ø§ÛŒØª'
            ],
            'youtube_url' => [
                'default' => '',
                'type' => 'url',
                'category' => 'telegram',
                'description' => 'Ù„ÛŒÙ†Ú© Ú©Ø§Ù†Ø§Ù„ ÛŒÙˆØªÛŒÙˆØ¨'
            ],
            
            // Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ
            'ai_enabled' => [
                'default' => false,
                'type' => 'boolean',
                'category' => 'ai',
                'description' => 'ÙØ¹Ø§Ù„â€ŒØ³Ø§Ø²ÛŒ Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ'
            ],
            'ai_provider' => [
                'default' => 'openai',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'Ø§Ø±Ø§Ø¦Ù‡â€ŒØ¯Ù‡Ù†Ø¯Ù‡ AI'
            ],
            'ai_api_key' => [
                'default' => '',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'API Key Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ'
            ],
            'ai_model' => [
                'default' => 'gpt-4o-mini',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'Ù…Ø¯Ù„ AI'
            ],
            'ai_system_prompt' => [
                'default' => 'ØªÙˆ Ø¯Ø³ØªÛŒØ§Ø± ÛŒÚ© ÛŒÙˆØªÛŒÙˆØ¨Ø± ÙØ§Ø±Ø³ÛŒâ€ŒØ²Ø¨Ø§Ù† Ù‡Ø³ØªÛŒ. Ø¯ÙˆØ³ØªØ§Ù†Ù‡ Ùˆ Ú©ÙˆØªØ§Ù‡ Ø¬ÙˆØ§Ø¨ Ø¨Ø¯Ù‡.',
                'type' => 'string',
                'category' => 'ai',
                'description' => 'System Prompt Ø¨Ø±Ø§ÛŒ AI'
            ],
            
            // VIP
            'vip_threshold' => [
                'default' => 100000,
                'type' => 'integer',
                'category' => 'vip',
                'description' => 'Ø¢Ø³ØªØ§Ù†Ù‡ VIP (ØªÙˆÙ…Ø§Ù†)'
            ],
            'vip_badge' => [
                'default' => 'ðŸ‘‘',
                'type' => 'string',
                'category' => 'vip',
                'description' => 'Ù†Ø´Ø§Ù† VIP'
            ],
            
            // Ø§Ù…Ù†ÛŒØªÛŒ
            'admin_ip_whitelist' => [
                'default' => [],
                'type' => 'array',
                'category' => 'security',
                'description' => 'Ù„ÛŒØ³Øª IP Ù‡Ø§ÛŒ Ù…Ø¬Ø§Ø² Ø¨Ø±Ø§ÛŒ Ù¾Ù†Ù„ Ø§Ø¯Ù…ÛŒÙ†'
            ],
            'login_max_attempts' => [
                'default' => 5,
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Ø­Ø¯Ø§Ú©Ø«Ø± ØªÙ„Ø§Ø´ Ù†Ø§Ù…ÙˆÙÙ‚ Ø¨Ø±Ø§ÛŒ ÙˆØ±ÙˆØ¯'
            ],
            'session_timeout' => [
                'default' => 3600,
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Ø²Ù…Ø§Ù† Ø§Ù†Ù‚Ø¶Ø§ÛŒ Session (Ø«Ø§Ù†ÛŒÙ‡)'
            ],
            
            // Ø¹Ù…Ù„Ú©Ø±Ø¯
            'cache_ttl' => [
                'default' => 3600,
                'type' => 'integer',
                'category' => 'performance',
                'description' => 'TTL Ú©Ø´ Ù¾ÛŒØ´â€ŒÙØ±Ø¶ (Ø«Ø§Ù†ÛŒÙ‡)'
            ],
            'broadcast_delay' => [
                'default' => 50,
                'type' => 'integer',
                'category' => 'performance',
                'description' => 'ØªØ£Ø®ÛŒØ± Ø¨ÛŒÙ† Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Broadcast (Ù…ÛŒÙ„ÛŒâ€ŒØ«Ø§Ù†ÛŒÙ‡)'
            ],
        ];
    }
    
    /**
     * Ù†ØµØ¨ ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
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
     * Ø±ÛŒØ³Øª ÛŒÚ© ØªÙ†Ø¸ÛŒÙ… Ø¨Ù‡ Ù¾ÛŒØ´â€ŒÙØ±Ø¶
     */
    public function resetToDefault($key) {
        $defaults = $this->getDefaults();
        
        if (!isset($defaults[$key])) {
            return ['success' => false, 'error' => 'ØªÙ†Ø¸ÛŒÙ… Ù¾ÛŒØ´â€ŒÙØ±Ø¶ÛŒ Ø¨Ø±Ø§ÛŒ Ø§ÛŒÙ† Ú©Ù„ÛŒØ¯ ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø±Ø¯'];
        }
        
        return $this->set($key, $defaults[$key]['default']);
    }
    
    /**
     * Ø±ÛŒØ³Øª Ù‡Ù…Ù‡ ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø¨Ù‡ Ù¾ÛŒØ´â€ŒÙØ±Ø¶
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
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Backup / Restore
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Backup ØªÙ†Ø¸ÛŒÙ…Ø§Øª
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
     * Restore ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§Ø² Backup
     */
    public function restore($filepath, $overwrite = false) {
        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'ÙØ§ÛŒÙ„ Backup ÛŒØ§ÙØª Ù†Ø´Ø¯'];
        }
        
        $content = file_get_contents($filepath);
        $backup = json_decode($content, true);
        
        if (!$backup || !isset($backup['settings'])) {
            return ['success' => false, 'error' => 'ÙØ§ÛŒÙ„ Backup Ù…Ø¹ØªØ¨Ø± Ù†ÛŒØ³Øª'];
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
     * Ù„ÛŒØ³Øª Backup Ù‡Ø§ÛŒ Ù…ÙˆØ¬ÙˆØ¯
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
        
        // Ù…Ø±ØªØ¨â€ŒØ³Ø§Ø²ÛŒ Ø¨Ø± Ø§Ø³Ø§Ø³ Ø²Ù…Ø§Ù† (Ø¬Ø¯ÛŒØ¯ØªØ±ÛŒÙ† Ø§ÙˆÙ„)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Ù„Ø§Ú¯ ØªØºÛŒÛŒØ±Ø§Øª
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Ø«Ø¨Øª Ù„Ø§Ú¯ ØªØºÛŒÛŒØ± ØªÙ†Ø¸ÛŒÙ…
     */
    private function logChange($key, $oldValue, $newValue) {
        // Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø­Ø³Ø§Ø³ Ø±Ùˆ Ù…Ø®ÙÛŒ Ú©Ù†
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
            // Ø§Ú¯Ø± Ø¬Ø¯ÙˆÙ„ Ù„Ø§Ú¯ ÙˆØ¬ÙˆØ¯ Ù†Ø¯Ø§Ø´ØªØŒ Ù†Ø§Ø¯ÛŒØ¯Ù‡ Ø¨Ú¯ÛŒØ±
        }
        
        $this->logger->info('Setting changed', [
            'key' => $key,
            'old' => $loggedOld,
            'new' => $loggedNew
        ]);
    }
    
    /**
     * Ø¯Ø±ÛŒØ§ÙØª ØªØ§Ø±ÛŒØ®Ú†Ù‡ ØªØºÛŒÛŒØ±Ø§Øª
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
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Ù…Ø¯ÛŒØ±ÛŒØª Ú©Ø´
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú©Ø´ ÛŒÚ© Ú©Ù„ÛŒØ¯
     */
    private function clearKeyCache($key) {
        $this->cache->delete("setting_{$key}");
        
        // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú©Ø´ Ø¯Ø³ØªÙ‡â€ŒÙ‡Ø§
        $categories = ['general', 'telegram', 'ai', 'vip', 'security', 'performance'];
        foreach ($categories as $cat) {
            $this->cache->delete("settings_category_{$cat}");
        }
        
        $this->cache->delete('settings_all');
    }
    
    /**
     * Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ú©Ù„ Ú©Ø´ ØªÙ†Ø¸ÛŒÙ…Ø§Øª
     */
    public function clearCache() {
        $this->localCache = [];
        
        // Ù¾Ø§Ú© Ú©Ø±Ø¯Ù† Ù‡Ù…Ù‡ Ú©Ø´â€ŒÙ‡Ø§ÛŒ Ù…Ø±Ø¨ÙˆØ· Ø¨Ù‡ ØªÙ†Ø¸ÛŒÙ…Ø§Øª
        $this->cache->clear();
        
        $this->logger->info('Settings cache cleared');
        
        return true;
    }
    
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // Ù…ØªØ¯Ù‡Ø§ÛŒ Ú©Ù…Ú©ÛŒ
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    
    /**
     * ÙØ±Ù…Øª Ø§Ù†Ø¯Ø§Ø²Ù‡ ÙØ§ÛŒÙ„
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
     * Ø¯Ø±ÛŒØ§ÙØª Ù„ÛŒØ³Øª Ø¯Ø³ØªÙ‡â€ŒÙ‡Ø§
     */
    public function getCategories() {
        return [
            'general' => ['name' => 'Ø¹Ù…ÙˆÙ…ÛŒ', 'icon' => 'âš™ï¸'],
            'telegram' => ['name' => 'ØªÙ„Ú¯Ø±Ø§Ù…', 'icon' => 'ðŸ¤–'],
            'ai' => ['name' => 'Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ', 'icon' => 'ðŸ§ '],
            'vip' => ['name' => 'Ø¨Ø§Ø´Ú¯Ø§Ù‡ VIP', 'icon' => 'ðŸ‘‘'],
            'security' => ['name' => 'Ø§Ù…Ù†ÛŒØªÛŒ', 'icon' => 'ðŸ”’'],
            'performance' => ['name' => 'Ø¹Ù…Ù„Ú©Ø±Ø¯', 'icon' => 'âš¡'],
            'appearance' => ['name' => 'Ø¸Ø§Ù‡Ø±', 'icon' => 'ðŸŽ¨'],
            'notifications' => ['name' => 'Ø§Ø¹Ù„Ø§Ù†â€ŒÙ‡Ø§', 'icon' => 'ðŸ””']
        ];
    }
    
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ ÙˆØ¬ÙˆØ¯ ÛŒÚ© ØªÙ†Ø¸ÛŒÙ…
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Ø¯Ø±ÛŒØ§ÙØª ØªÙ†Ø¸ÛŒÙ… Ø¨Ø§ fallback Ø¨Ù‡ config.php
     */
    public function getOrConfig($key, $configKey = null) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        // fallback Ø¨Ù‡ config.php
        if ($configKey) {
            return \App\Core\Config::getInstance()->get($configKey);
        }
        
        return null;
    }
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ØªØ§Ø¨Ø¹ Ú©Ù…Ú©ÛŒ global
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
if (!function_exists('setting')) {
    function setting($key, $default = null) {
        return \App\Admin\Settings::getInstance()->get($key, $default);
    }
}