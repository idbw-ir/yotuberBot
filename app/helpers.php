<?php
/**
 * ============================================
 * Helper Functions - ØªÙˆØ§Ø¨Ø¹ Ú©Ù…Ú©ÛŒ Global
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ù…Ø¬Ù…ÙˆØ¹Ù‡â€ŒØ§ÛŒ Ø§Ø² ØªÙˆØ§Ø¨Ø¹ Ú©Ù…Ú©ÛŒ Ù¾Ø±Ú©Ø§Ø±Ø¨Ø±Ø¯ Ú©Ù‡
 * Ø¯Ø± ØªÙ…Ø§Ù… Ù¾Ø±ÙˆÚ˜Ù‡ Ù‚Ø§Ø¨Ù„ Ø§Ø³ØªÙØ§Ø¯Ù‡ Ù‡Ø³ØªÙ†
 * 
 * Ø¯Ø³ØªÙ‡â€ŒØ¨Ù†Ø¯ÛŒ:
 * - String Helpers
 * - Array Helpers
 * - Date/Time Helpers
 * - File Helpers
 * - URL Helpers
 * - HTML Helpers
 * - Number Helpers
 * - Debug Helpers
 * - Validation Helpers
 * - Telegram Helpers
 */

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 1. String Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('str_limit')) {
    /**
     * Ù…Ø­Ø¯ÙˆØ¯ Ú©Ø±Ø¯Ù† Ø·ÙˆÙ„ Ø±Ø´ØªÙ‡
     */
    function str_limit($value, $limit = 100, $end = '...') {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit) . $end;
    }
}

if (!function_exists('str_slug')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø±Ø´ØªÙ‡ Ø¨Ù‡ slug
     */
    function str_slug($title, $separator = '-') {
        $title = mb_strtolower($title);
        
        // ØªØ¨Ø¯ÛŒÙ„ Ø­Ø±ÙˆÙ ÙØ§Ø±Ø³ÛŒ Ø¨Ù‡ Ø§Ù†Ú¯Ù„ÛŒØ³ÛŒ (Ø§Ø®ØªÛŒØ§Ø±ÛŒ)
        $persian = ['Û°','Û±','Û²','Û³','Û´','Ûµ','Û¶','Û·','Û¸','Û¹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $title = str_replace($persian, $english, $title);
        
        // Ø­Ø°Ù Ú©Ø§Ø±Ø§Ú©ØªØ±Ù‡Ø§ÛŒ ØºÛŒØ± Ù…Ø¬Ø§Ø²
        $title = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $title);
        
        // Ø¬Ø§ÛŒÚ¯Ø²ÛŒÙ†ÛŒ ÙØ§ØµÙ„Ù‡ Ø¨Ø§ separator
        $title = preg_replace('/[\s-]+/', $separator, $title);
        
        return trim($title, $separator);
    }
}

if (!function_exists('str_random')) {
    /**
     * ØªÙˆÙ„ÛŒØ¯ Ø±Ø´ØªÙ‡ ØªØµØ§Ø¯ÙÛŒ
     */
    function str_random($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        
        return $result;
    }
}

if (!function_exists('str_contains_any')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ ÙˆØ¬ÙˆØ¯ ÛŒÚ©ÛŒ Ø§Ø² Ú©Ù„Ù…Ø§Øª Ø¯Ø± Ø±Ø´ØªÙ‡
     */
    function str_contains_any($haystack, array $needles) {
        foreach ($needles as $needle) {
            if (mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø´Ø±ÙˆØ¹ Ø±Ø´ØªÙ‡ Ø¨Ø§ ÛŒÚ© Ù…Ù‚Ø¯Ø§Ø±
     */
    function str_starts_with($haystack, $needle) {
        return mb_strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ù¾Ø§ÛŒØ§Ù† Ø±Ø´ØªÙ‡ Ø¨Ø§ ÛŒÚ© Ù…Ù‚Ø¯Ø§Ø±
     */
    function str_ends_with($haystack, $needle) {
        $length = mb_strlen($needle);
        if ($length === 0) {
            return true;
        }
        return mb_substr($haystack, -$length) === $needle;
    }
}

if (!function_exists('str_pad_left')) {
    /**
     * Ø§ÙØ²ÙˆØ¯Ù† Ú©Ø§Ø±Ø§Ú©ØªØ± Ø¨Ù‡ Ø³Ù…Øª Ú†Ù¾
     */
    function str_pad_left($input, $padLength, $padString = '0') {
        return str_pad($input, $padLength, $padString, STR_PAD_LEFT);
    }
}

if (!function_exists('str_word_count_fa')) {
    /**
     * Ø´Ù…Ø§Ø±Ø´ Ú©Ù„Ù…Ø§Øª ÙØ§Ø±Ø³ÛŒ
     */
    function str_word_count_fa($text) {
        $text = trim($text);
        if (empty($text)) return 0;
        
        $words = preg_split('/\s+/u', $text);
        return count(array_filter($words));
    }
}

if (!function_exists('clean_html')) {
    /**
     * Ù¾Ø§Ú©Ø³Ø§Ø²ÛŒ HTML (Ø­Ø°Ù ØªÚ¯â€ŒÙ‡Ø§)
     */
    function clean_html($html, $allowedTags = '') {
        return strip_tags($html, $allowedTags);
    }
}

if (!function_exists('nl2p')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø®Ø·ÙˆØ· Ø¬Ø¯ÛŒØ¯ Ø¨Ù‡ Ù¾Ø§Ø±Ø§Ú¯Ø±Ø§Ù HTML
     */
    function nl2p($text) {
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $result = '';
        
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if (!empty($p)) {
                $result .= '<p>' . nl2br(htmlspecialchars($p)) . '</p>';
            }
        }
        
        return $result;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 2. Array Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('array_get')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù…Ù‚Ø¯Ø§Ø± Ø§Ø² Ø¢Ø±Ø§ÛŒÙ‡ ØªÙˆ Ø¯Ø± ØªÙˆ Ø¨Ø§ Dot Notation
     */
    function array_get($array, $key, $default = null) {
        if (is_null($key)) {
            return $array;
        }
        
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        
        return $array;
    }
}

if (!function_exists('array_set')) {
    /**
     * ØªÙ†Ø¸ÛŒÙ… Ù…Ù‚Ø¯Ø§Ø± Ø¯Ø± Ø¢Ø±Ø§ÛŒÙ‡ ØªÙˆ Ø¯Ø± ØªÙˆ Ø¨Ø§ Dot Notation
     */
    function array_set(&$array, $key, $value) {
        $keys = explode('.', $key);
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            
            $array = &$array[$key];
        }
        
        $array[array_shift($keys)] = $value;
        
        return $array;
    }
}

if (!function_exists('array_has')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ ÙˆØ¬ÙˆØ¯ Ú©Ù„ÛŒØ¯ Ø¯Ø± Ø¢Ø±Ø§ÛŒÙ‡ ØªÙˆ Ø¯Ø± ØªÙˆ
     */
    function array_has($array, $key) {
        if (empty($array) || is_null($key)) {
            return false;
        }
        
        if (array_key_exists($key, $array)) {
            return true;
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        
        return true;
    }
}

if (!function_exists('array_forget')) {
    /**
     * Ø­Ø°Ù Ú©Ù„ÛŒØ¯ Ø§Ø² Ø¢Ø±Ø§ÛŒÙ‡ ØªÙˆ Ø¯Ø± ØªÙˆ
     */
    function array_forget(&$array, $key) {
        $keys = explode('.', $key);
        
        while (count($keys) > 1) {
            $key = array_shift($keys);
            
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return;
            }
            
            $array = &$array[$key];
        }
        
        unset($array[array_shift($keys)]);
    }
}

if (!function_exists('array_only')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª ÙÙ‚Ø· Ú©Ù„ÛŒØ¯Ù‡Ø§ÛŒ Ù…Ø´Ø®Øµ Ø§Ø² Ø¢Ø±Ø§ÛŒÙ‡
     */
    function array_only($array, array $keys) {
        return array_intersect_key($array, array_flip($keys));
    }
}

if (!function_exists('array_except')) {
    /**
     * Ø­Ø°Ù Ú©Ù„ÛŒØ¯Ù‡Ø§ÛŒ Ù…Ø´Ø®Øµ Ø§Ø² Ø¢Ø±Ø§ÛŒÙ‡
     */
    function array_except($array, array $keys) {
        return array_diff_key($array, array_flip($keys));
    }
}

if (!function_exists('array_flatten')) {
    /**
     * ØªØ®Øª Ú©Ø±Ø¯Ù† Ø¢Ø±Ø§ÛŒÙ‡ Ú†Ù†Ø¯ Ø¨Ø¹Ø¯ÛŒ
     */
    function array_flatten($array, $depth = INF) {
        $result = [];
        
        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, array_flatten($item, $depth - 1));
            }
        }
        
        return $result;
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Ø§Ø³ØªØ®Ø±Ø§Ø¬ ÛŒÚ© Ú©Ù„ÛŒØ¯ Ø®Ø§Øµ Ø§Ø² Ø¢Ø±Ø§ÛŒÙ‡ Ú†Ù†Ø¯ Ø¨Ø¹Ø¯ÛŒ
     */
    function array_pluck($array, $value, $key = null) {
        $results = [];
        
        foreach ($array as $item) {
            $itemValue = is_object($item) ? $item->$value : $item[$value];
            
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) ? $item->$key : $item[$key];
                $results[$itemKey] = $itemValue;
            }
        }
        
        return $results;
    }
}

if (!function_exists('array_group_by')) {
    /**
     * Ú¯Ø±ÙˆÙ‡â€ŒØ¨Ù†Ø¯ÛŒ Ø¢Ø±Ø§ÛŒÙ‡ Ø¨Ø± Ø§Ø³Ø§Ø³ ÛŒÚ© Ú©Ù„ÛŒØ¯
     */
    function array_group_by($array, $key) {
        $result = [];
        
        foreach ($array as $item) {
            $value = is_object($item) ? $item->$key : $item[$key];
            $result[$value][] = $item;
        }
        
        return $result;
    }
}

if (!function_exists('array_sort_by')) {
    /**
     * Ù…Ø±ØªØ¨â€ŒØ³Ø§Ø²ÛŒ Ø¢Ø±Ø§ÛŒÙ‡ Ø¨Ø± Ø§Ø³Ø§Ø³ ÛŒÚ© Ú©Ù„ÛŒØ¯
     */
    function array_sort_by($array, $key, $direction = 'asc') {
        usort($array, function($a, $b) use ($key, $direction) {
            $valueA = is_object($a) ? $a->$key : $a[$key];
            $valueB = is_object($b) ? $b->$key : $b[$key];
            
            $result = $valueA <=> $valueB;
            
            return $direction === 'desc' ? -$result : $result;
        });
        
        return $array;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 3. Date/Time Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('now')) {
    /**
     * Ø²Ù…Ø§Ù† ÙØ¹Ù„ÛŒ
     */
    function now() {
        return new DateTime();
    }
}

if (!function_exists('today')) {
    /**
     * ØªØ§Ø±ÛŒØ® Ø§Ù…Ø±ÙˆØ²
     */
    function today() {
        return date('Y-m-d');
    }
}

if (!function_exists('carbon')) {
    /**
     * Ø³Ø§Ø®Øª DateTime Ø§Ø² Ø±Ø´ØªÙ‡
     */
    function carbon($date = null) {
        if (is_null($date)) {
            return new DateTime();
        }
        
        if ($date instanceof DateTime) {
            return $date;
        }
        
        return new DateTime($date);
    }
}

if (!function_exists('format_date')) {
    /**
     * ÙØ±Ù…Øª ØªØ§Ø±ÛŒØ® Ø¨Ù‡ ÙØ§Ø±Ø³ÛŒ
     */
    function format_date($date, $format = 'Y/m/d') {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * ÙØ±Ù…Øª ØªØ§Ø±ÛŒØ® Ùˆ Ø³Ø§Ø¹Øª Ø¨Ù‡ ÙØ§Ø±Ø³ÛŒ
     */
    function format_datetime($date, $format = 'Y/m/d H:i:s') {
        return format_date($date, $format);
    }
}

if (!function_exists('time_ago')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø²Ù…Ø§Ù† Ø¨Ù‡ "x Ø¯Ù‚ÛŒÙ‚Ù‡ Ù¾ÛŒØ´"
     */
    function time_ago($datetime) {
        $time = is_numeric($datetime) ? $datetime : strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Ù„Ø­Ø¸Ø§ØªÛŒ Ù¾ÛŒØ´';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "{$minutes} Ø¯Ù‚ÛŒÙ‚Ù‡ Ù¾ÛŒØ´";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} Ø³Ø§Ø¹Øª Ù¾ÛŒØ´";
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "{$days} Ø±ÙˆØ² Ù¾ÛŒØ´";
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "{$weeks} Ù‡ÙØªÙ‡ Ù¾ÛŒØ´";
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "{$months} Ù…Ø§Ù‡ Ù¾ÛŒØ´";
        } else {
            $years = floor($diff / 31536000);
            return "{$years} Ø³Ø§Ù„ Ù¾ÛŒØ´";
        }
    }
}

if (!function_exists('days_ago')) {
    /**
     * ØªØ¹Ø¯Ø§Ø¯ Ø±ÙˆØ²Ù‡Ø§ÛŒ Ú¯Ø°Ø´ØªÙ‡
     */
    function days_ago($date) {
        $time = is_numeric($date) ? $date : strtotime($date);
        return floor((time() - $time) / 86400);
    }
}

if (!function_exists('is_today')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø§ÛŒÙ†Ú©Ù‡ ØªØ§Ø±ÛŒØ® Ø§Ù…Ø±ÙˆØ² Ø§Ø³Øª
     */
    function is_today($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }
}

if (!function_exists('is_yesterday')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø§ÛŒÙ†Ú©Ù‡ ØªØ§Ø±ÛŒØ® Ø¯ÛŒØ±ÙˆØ² Ø§Ø³Øª
     */
    function is_yesterday($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d', strtotime('-1 day'));
    }
}

if (!function_exists('is_past')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ú¯Ø°Ø´ØªÙ‡ Ø¨ÙˆØ¯Ù† ØªØ§Ø±ÛŒØ®
     */
    function is_past($date) {
        return strtotime($date) < time();
    }
}

if (!function_exists('is_future')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø¢ÛŒÙ†Ø¯Ù‡ Ø¨ÙˆØ¯Ù† ØªØ§Ø±ÛŒØ®
     */
    function is_future($date) {
        return strtotime($date) > time();
    }
}

if (!function_exists('diff_for_humans')) {
    /**
     * ØªÙØ§ÙˆØª Ø²Ù…Ø§Ù†ÛŒ Ø¨Ù‡ Ø²Ø¨Ø§Ù† Ø§Ù†Ø³Ø§Ù†ÛŒ
     */
    function diff_for_humans($date1, $date2 = null) {
        $time1 = is_numeric($date1) ? $date1 : strtotime($date1);
        $time2 = $date2 ? (is_numeric($date2) ? $date2 : strtotime($date2)) : time();
        
        $diff = abs($time2 - $time1);
        
        if ($diff < 60) {
            return "{$diff} Ø«Ø§Ù†ÛŒÙ‡";
        } elseif ($diff < 3600) {
            return floor($diff / 60) . " Ø¯Ù‚ÛŒÙ‚Ù‡";
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . " Ø³Ø§Ø¹Øª";
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . " Ø±ÙˆØ²";
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . " Ù…Ø§Ù‡";
        } else {
            return floor($diff / 31536000) . " Ø³Ø§Ù„";
        }
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 4. File Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('file_exists_safe')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ ÙˆØ¬ÙˆØ¯ ÙØ§ÛŒÙ„ Ø¨Ø§ Ù…Ø¯ÛŒØ±ÛŒØª Ø®Ø·Ø§
     */
    function file_exists_safe($path) {
        try {
            return file_exists($path);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('file_size')) {
    /**
     * ÙØ±Ù…Øª Ø§Ù†Ø¯Ø§Ø²Ù‡ ÙØ§ÛŒÙ„
     */
    function file_size($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('file_extension')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù¾Ø³ÙˆÙ†Ø¯ ÙØ§ÛŒÙ„
     */
    function file_extension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}

if (!function_exists('file_name')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù†Ø§Ù… ÙØ§ÛŒÙ„ Ø¨Ø¯ÙˆÙ† Ù¾Ø³ÙˆÙ†Ø¯
     */
    function file_name($filename) {
        return pathinfo($filename, PATHINFO_FILENAME);
    }
}

if (!function_exists('file_mime')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª MIME type ÙØ§ÛŒÙ„
     */
    function file_mime($filepath) {
        if (!file_exists($filepath)) {
            return null;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        return $mime;
    }
}

if (!function_exists('ensure_directory')) {
    /**
     * Ø§Ø·Ù…ÛŒÙ†Ø§Ù† Ø§Ø² ÙˆØ¬ÙˆØ¯ Ù¾ÙˆØ´Ù‡
     */
    function ensure_directory($path, $permissions = 0775) {
        if (!is_dir($path)) {
            return mkdir($path, $permissions, true);
        }
        return true;
    }
}

if (!function_exists('write_file')) {
    /**
     * Ù†ÙˆØ´ØªÙ† Ø¯Ø± ÙØ§ÛŒÙ„
     */
    function write_file($path, $content, $append = false) {
        ensure_directory(dirname($path));
        
        $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
        
        return file_put_contents($path, $content, $flags) !== false;
    }
}

if (!function_exists('read_file')) {
    /**
     * Ø®ÙˆØ§Ù†Ø¯Ù† Ù…Ø­ØªÙˆØ§ÛŒ ÙØ§ÛŒÙ„
     */
    function read_file($path, $default = null) {
        if (!file_exists($path)) {
            return $default;
        }
        
        return file_get_contents($path);
    }
}

if (!function_exists('delete_file')) {
    /**
     * Ø­Ø°Ù ÙØ§ÛŒÙ„
     */
    function delete_file($path) {
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }
}

if (!function_exists('copy_file')) {
    /**
     * Ú©Ù¾ÛŒ ÙØ§ÛŒÙ„
     */
    function copy_file($source, $destination) {
        ensure_directory(dirname($destination));
        return copy($source, $destination);
    }
}

if (!function_exists('move_file')) {
    /**
     * Ø¬Ø§Ø¨Ø¬Ø§ÛŒÛŒ ÙØ§ÛŒÙ„
     */
    function move_file($source, $destination) {
        ensure_directory(dirname($destination));
        return rename($source, $destination);
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 5. URL Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('asset')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª URL ÙØ§ÛŒÙ„ asset
     */
    function asset($path) {
        $baseUrl = config('app.url', '');
        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù…Ø³ÛŒØ± ÙØ§ÛŒÙ„ Ø¹Ù…ÙˆÙ…ÛŒ
     */
    function public_path($path = '') {
        return PUBLIC_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('current_url')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª URL ÙØ¹Ù„ÛŒ
     */
    function current_url() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return "{$protocol}://{$host}{$uri}";
    }
}

if (!function_exists('previous_url')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª URL Ù‚Ø¨Ù„ÛŒ
     */
    function previous_url($default = '/') {
        return $_SERVER['HTTP_REFERER'] ?? $default;
    }
}

if (!function_exists('query_string')) {
    /**
     * Ø³Ø§Ø®Øª Query String
     */
    function query_string(array $params) {
        return http_build_query($params);
    }
}

if (!function_exists('merge_query')) {
    /**
     * Ø§Ø¯ØºØ§Ù… Ù¾Ø§Ø±Ø§Ù…ØªØ±Ù‡Ø§ÛŒ URL
     */
    function merge_query(array $params) {
        $current = $_GET ?? [];
        return http_build_query(array_merge($current, $params));
    }
}

if (!function_exists('is_url')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ URL Ù…Ø¹ØªØ¨Ø±
     */
    function is_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 6. HTML Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('link_to')) {
    /**
     * Ø³Ø§Ø®Øª Ù„ÛŒÙ†Ú© HTML
     */
    function link_to($url, $text, $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<a href="' . htmlspecialchars($url) . '"' . $attrs . '>' . $text . '</a>';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Ø³Ø§Ø®Øª hidden input Ø¨Ø±Ø§ÛŒ CSRF
     */
    function csrf_field() {
        $session = \App\Core\Session::getInstance();
        $token = htmlspecialchars($session->getCsrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Ø³Ø§Ø®Øª hidden input Ø¨Ø±Ø§ÛŒ HTTP Method
     */
    function method_field($method) {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù…Ù‚Ø¯Ø§Ø± Ù‚Ø¨Ù„ÛŒ input
     */
    function old($key, $default = '') {
        $session = \App\Core\Session::getInstance();
        $oldInput = $session->getFlash('_old_input', []);
        
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('active_class')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ú©Ù„Ø§Ø³ active Ø¨Ø±Ø§ÛŒ Ù…Ù†Ùˆ
     */
    function active_class($paths, $class = 'active') {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        $currentPath = parse_url($currentPath, PHP_URL_PATH);
        
        $paths = is_array($paths) ? $paths : [$paths];
        
        foreach ($paths as $path) {
            if ($currentPath === $path || str_starts_with($currentPath, $path)) {
                return $class;
            }
        }
        
        return '';
    }
}

if (!function_exists('pagination')) {
    /**
     * Ø³Ø§Ø®Øª HTML Pagination
     */
    function pagination($pagination, $baseUrl = '') {
        if ($pagination['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<div class="pagination">';
        
        // Previous
        if ($pagination['current_page'] > 1) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '" class="pagination-link">Ù‚Ø¨Ù„ÛŒ</a>';
        }
        
        // Pages
        for ($i = 1; $i <= $pagination['total_pages']; $i++) {
            $active = $i === $pagination['current_page'] ? 'active' : '';
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link ' . $active . '">' . $i . '</a>';
        }
        
        // Next
        if ($pagination['current_page'] < $pagination['total_pages']) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '" class="pagination-link">Ø¨Ø¹Ø¯ÛŒ</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 7. Number Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('format_number')) {
    /**
     * ÙØ±Ù…Øª Ø¹Ø¯Ø¯ Ø¨Ø§ Ø¬Ø¯Ø§Ú©Ù†Ù†Ø¯Ù‡
     */
    function format_number($number, $decimals = 0) {
        return number_format((float)$number, $decimals, '.', ',');
    }
}

if (!function_exists('format_money')) {
    /**
     * ÙØ±Ù…Øª Ù¾ÙˆÙ„ (ØªÙˆÙ…Ø§Ù†)
     */
    function format_money($amount, $currency = 'ØªÙˆÙ…Ø§Ù†') {
        return format_number($amount) . ' ' . $currency;
    }
}

if (!function_exists('format_toman')) {
    /**
     * ÙØ±Ù…Øª ØªÙˆÙ…Ø§Ù†
     */
    function format_toman($amount) {
        return format_money($amount, 'ØªÙˆÙ…Ø§Ù†');
    }
}

if (!function_exists('to_english_numbers')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø§Ø¹Ø¯Ø§Ø¯ ÙØ§Ø±Ø³ÛŒ Ø¨Ù‡ Ø§Ù†Ú¯Ù„ÛŒØ³ÛŒ
     */
    function to_english_numbers($string) {
        $persian = ['Û°','Û±','Û²','Û³','Û´','Ûµ','Û¶','Û·','Û¸','Û¹'];
        $arabic = ['Ù ','Ù¡','Ù¢','Ù£','Ù¤','Ù¥','Ù¦','Ù§','Ù¨','Ù©'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        
        $string = str_replace($persian, $english, $string);
        return str_replace($arabic, $english, $string);
    }
}

if (!function_exists('to_persian_numbers')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø§Ø¹Ø¯Ø§Ø¯ Ø§Ù†Ú¯Ù„ÛŒØ³ÛŒ Ø¨Ù‡ ÙØ§Ø±Ø³ÛŒ
     */
    function to_persian_numbers($string) {
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $persian = ['Û°','Û±','Û²','Û³','Û´','Ûµ','Û¶','Û·','Û¸','Û¹'];
        
        return str_replace($english, $persian, $string);
    }
}

if (!function_exists('percentage')) {
    /**
     * Ù…Ø­Ø§Ø³Ø¨Ù‡ Ø¯Ø±ØµØ¯
     */
    function percentage($part, $total, $decimals = 2) {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, $decimals);
    }
}

if (!function_exists('clamp')) {
    /**
     * Ù…Ø­Ø¯ÙˆØ¯ Ú©Ø±Ø¯Ù† Ø¹Ø¯Ø¯ Ø¨ÛŒÙ† Ø­Ø¯Ø§Ù‚Ù„ Ùˆ Ø­Ø¯Ø§Ú©Ø«Ø±
     */
    function clamp($value, $min, $max) {
        return max($min, min($max, $value));
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 8. Debug Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('pre')) {
    /**
     * Ù†Ù…Ø§ÛŒØ´ Ø¯Ø± ØªÚ¯ pre
     */
    function pre($data) {
        echo '<pre style="background:#1f2937;color:#fff;padding:15px;border-radius:8px;direction:ltr;text-align:left;overflow-x:auto;">';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('log_debug')) {
    /**
     * Ù„Ø§Ú¯ Ù¾ÛŒØ§Ù… Debug
     */
    function log_debug($message, array $context = []) {
        try {
            $logger = \App\Core\Logger::getInstance();
            $logger->debug($message, $context);
        } catch (Exception $e) {
            error_log("Debug: {$message}");
        }
    }
}

if (!function_exists('log_info')) {
    /**
     * Ù„Ø§Ú¯ Ù¾ÛŒØ§Ù… Info
     */
    function log_info($message, array $context = []) {
        try {
            $logger = \App\Core\Logger::getInstance();
            $logger->info($message, $context);
        } catch (Exception $e) {
            error_log("Info: {$message}");
        }
    }
}

if (!function_exists('log_error')) {
    /**
     * Ù„Ø§Ú¯ Ù¾ÛŒØ§Ù… Error
     */
    function log_error($message, array $context = []) {
        try {
            $logger = \App\Core\Logger::getInstance();
            $logger->error($message, $context);
        } catch (Exception $e) {
            error_log("Error: {$message}");
        }
    }
}

if (!function_exists('benchmark')) {
    /**
     * Ø§Ù†Ø¯Ø§Ø²Ù‡â€ŒÚ¯ÛŒØ±ÛŒ Ø²Ù…Ø§Ù† Ø§Ø¬Ø±Ø§ÛŒ Ú©Ø¯
     */
    function benchmark($name, callable $callback) {
        $start = microtime(true);
        $result = $callback();
        $end = microtime(true);
        
        $time = round(($end - $start) * 1000, 2);
        
        log_debug("Benchmark [{$name}]: {$time}ms");
        
        return $result;
    }
}

if (!function_exists('memory_usage')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù…ÛŒØ²Ø§Ù† Ø§Ø³ØªÙØ§Ø¯Ù‡ Ø­Ø§ÙØ¸Ù‡
     */
    function memory_usage($format = true) {
        $bytes = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        
        if ($format) {
            return [
                'current' => file_size($bytes),
                'peak' => file_size($peak)
            ];
        }
        
        return [
            'current' => $bytes,
            'peak' => $peak
        ];
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 9. Validation Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('is_email')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø§ÛŒÙ…ÛŒÙ„ Ù…Ø¹ØªØ¨Ø±
     */
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('is_numeric_value')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø¹Ø¯Ø¯ Ø¨ÙˆØ¯Ù†
     */
    function is_numeric_value($value) {
        return is_numeric($value);
    }
}

if (!function_exists('is_json')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ JSON Ù…Ø¹ØªØ¨Ø±
     */
    function is_json($string) {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('is_serialized')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ serialized Ø¨ÙˆØ¯Ù†
     */
    function is_serialized($data) {
        if (!is_string($data)) return false;
        $data = trim($data);
        if ($data === 'N;') return true;
        if (!preg_match('/^([adObis]):/', $data, $matches)) return false;
        return @unserialize($data) !== false || $data === 'b:0;';
    }
}

if (!function_exists('is_empty_value')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø®Ø§Ù„ÛŒ Ø¨ÙˆØ¯Ù† Ù…Ù‚Ø¯Ø§Ø±
     */
    function is_empty_value($value) {
        if (is_null($value)) return true;
        if (is_string($value) && trim($value) === '') return true;
        if (is_array($value) && empty($value)) return true;
        return false;
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 10. Telegram Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('is_telegram_id')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ Ø¢ÛŒØ¯ÛŒ ØªÙ„Ú¯Ø±Ø§Ù… Ù…Ø¹ØªØ¨Ø±
     */
    function is_telegram_id($id) {
        return is_numeric($id) && $id >= 10000 && $id <= 9999999999;
    }
}

if (!function_exists('is_bot_token')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ ØªÙˆÚ©Ù† Ø±Ø¨Ø§Øª ØªÙ„Ú¯Ø±Ø§Ù…
     */
    function is_bot_token($token) {
        return preg_match('/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/', $token) === 1;
    }
}

if (!function_exists('telegram_link')) {
    /**
     * Ø³Ø§Ø®Øª Ù„ÛŒÙ†Ú© ØªÙ„Ú¯Ø±Ø§Ù…
     */
    function telegram_link($username) {
        $username = ltrim($username, '@');
        return "https://t.me/{$username}";
    }
}

if (!function_exists('format_telegram_html')) {
    /**
     * ÙØ±Ù…Øª Ù…ØªÙ† Ø¨Ø±Ø§ÛŒ HTML ØªÙ„Ú¯Ø±Ø§Ù…
     */
    function format_telegram_html($text) {
        // ØªØ¨Ø¯ÛŒÙ„ newline Ø¨Ù‡ <br>
        $text = nl2br($text);
        
        // Bold
        $text = preg_replace('/\*([^*]+)\*/', '<b>$1</b>', $text);
        
        // Italic
        $text = preg_replace('/_([^_]+)_/', '<i>$1</i>', $text);
        
        // Code
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        
        return $text;
    }
}

if (!function_exists('escape_telegram_html')) {
    /**
     * Escape Ø¨Ø±Ø§ÛŒ HTML ØªÙ„Ú¯Ø±Ø§Ù…
     */
    function escape_telegram_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_user_mention')) {
    /**
     * Ø³Ø§Ø®Øª mention Ú©Ø§Ø±Ø¨Ø±
     */
    function get_user_mention($userId, $name = null) {
        if ($name) {
            return "<a href=\"tg://user?id={$userId}\">" . htmlspecialchars($name) . "</a>";
        }
        return "<code>{$userId}</code>";
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 11. Misc Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('env')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù…ØªØºÛŒØ± Ù…Ø­ÛŒØ·ÛŒ
     */
    function env($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if (!function_exists('retry')) {
    /**
     * ØªÙ„Ø§Ø´ Ù…Ø¬Ø¯Ø¯ Ø¯Ø± ØµÙˆØ±Øª Ø®Ø·Ø§
     */
    function retry($times, callable $callback, $sleep = 0) {
        $attempts = 0;
        
        beginning:
        $attempts++;
        
        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if ($attempts < $times) {
                if ($sleep > 0) {
                    usleep($sleep * 1000);
                }
                goto beginning;
            }
            throw $e;
        }
    }
}

if (!function_exists('tap')) {
    /**
     * Ø§Ø¬Ø±Ø§ÛŒ callback Ø±ÙˆÛŒ Ù…Ù‚Ø¯Ø§Ø± Ùˆ Ø¨Ø±Ú¯Ø±Ø¯Ø§Ù†Ø¯Ù† Ù…Ù‚Ø¯Ø§Ø±
     */
    function tap($value, callable $callback = null) {
        if ($callback) {
            $callback($value);
        }
        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù…Ù‚Ø¯Ø§Ø± (Ø§Ú¯Ø± callable Ø¨Ø§Ø´Ø¯ØŒ Ø§Ø¬Ø±Ø§ Ù…ÛŒâ€ŒØ´ÙˆØ¯)
     */
    function value($value, ...$args) {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('class_basename')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ù†Ø§Ù… Ú©Ù„Ø§Ø³ Ø¨Ø¯ÙˆÙ† namespace
     */
    function class_basename($class) {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('humanize')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ snake_case Ø¨Ù‡ Human Readable
     */
    function humanize($value) {
        return ucwords(str_replace(['_', '-'], ' ', $value));
    }
}

if (!function_exists('title_case')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø¨Ù‡ Title Case
     */
    function title_case($value) {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('camel_case')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø¨Ù‡ camelCase
     */
    function camel_case($value) {
        $value = ucwords(str_replace(['_', '-'], ' ', $value));
        $value = str_replace(' ', '', $value);
        return lcfirst($value);
    }
}

if (!function_exists('snake_case')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø¨Ù‡ snake_case
     */
    function snake_case($value, $delimiter = '_') {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        return $value;
    }
}

if (!function_exists('kebab_case')) {
    /**
     * ØªØ¨Ø¯ÛŒÙ„ Ø¨Ù‡ kebab-case
     */
    function kebab_case($value) {
        return snake_case($value, '-');
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 12. Cache Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('cache_get')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ø§Ø² Ú©Ø´
     */
    function cache_get($key, $default = null) {
        return \App\Core\Cache::getInstance()->get($key, $default);
    }
}

if (!function_exists('cache_set')) {
    /**
     * Ø°Ø®ÛŒØ±Ù‡ Ø¯Ø± Ú©Ø´
     */
    function cache_set($key, $value, $ttl = 3600) {
        return \App\Core\Cache::getInstance()->set($key, $value, $ttl);
    }
}

if (!function_exists('cache_remember')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ø§Ø² Ú©Ø´ ÛŒØ§ Ø³Ø§Ø®Øª Ùˆ Ø°Ø®ÛŒØ±Ù‡
     */
    function cache_remember($key, $ttl, callable $callback) {
        return \App\Core\Cache::getInstance()->remember($key, $ttl, $callback);
    }
}

if (!function_exists('cache_forget')) {
    /**
     * Ø­Ø°Ù Ø§Ø² Ú©Ø´
     */
    function cache_forget($key) {
        return \App\Core\Cache::getInstance()->delete($key);
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 13. Session Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('session_get')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Ø§Ø² Session
     */
    function session_get($key, $default = null) {
        return \App\Core\Session::getInstance()->get($key, $default);
    }
}

if (!function_exists('session_set')) {
    /**
     * Ø°Ø®ÛŒØ±Ù‡ Ø¯Ø± Session
     */
    function session_set($key, $value) {
        return \App\Core\Session::getInstance()->set($key, $value);
    }
}

if (!function_exists('session_has')) {
    /**
     * Ø¨Ø±Ø±Ø³ÛŒ ÙˆØ¬ÙˆØ¯ Ø¯Ø± Session
     */
    function session_has($key) {
        return \App\Core\Session::getInstance()->has($key);
    }
}

if (!function_exists('session_forget')) {
    /**
     * Ø­Ø°Ù Ø§Ø² Session
     */
    function session_forget($key) {
        return \App\Core\Session::getInstance()->remove($key);
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 14. Database Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('db')) {
    /**
     * Ø¯Ø±ÛŒØ§ÙØª Database Instance
     */
    function db() {
        return \App\Core\Database::getInstance();
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 15. JSON Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('json_response')) {
    /**
     * Ø§Ø±Ø³Ø§Ù„ Ù¾Ø§Ø³Ø® JSON
     */
    function json_response($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('json_success')) {
    /**
     * Ù¾Ø§Ø³Ø® JSON Ù…ÙˆÙÙ‚
     */
    function json_success($data = [], $message = 'Ù…ÙˆÙÙ‚') {
        json_response([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
}

if (!function_exists('json_error')) {
    /**
     * Ù¾Ø§Ø³Ø® JSON Ø®Ø·Ø§
     */
    function json_error($message = 'Ø®Ø·Ø§', $statusCode = 400, $errors = []) {
        json_response([
            'success' => false,
            'error' => [
                'message' => $message,
                'errors' => $errors
            ]
        ], $statusCode);
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 16. View Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

if (!function_exists('view')) {
    /**
     * Ø¨Ø§Ø±Ú¯Ø°Ø§Ø±ÛŒ View
     */
    function view($path, $data = []) {
        extract($data);
        
        $viewPath = RESOURCES_PATH . '/views/' . $path . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View [{$path}] not found at: {$viewPath}");
        }
        
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('render_view')) {
    /**
     * Ø±Ù†Ø¯Ø± Ùˆ Ù†Ù…Ø§ÛŒØ´ View
     */
    function render_view($path, $data = []) {
        echo view($path, $data);
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 17. End of Helpers
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ù¾Ø§ÛŒØ§Ù† ÙØ§ÛŒÙ„ helpers.php
 * 
 * Ø¨Ø±Ø§ÛŒ Ø§Ø¶Ø§ÙÙ‡ Ú©Ø±Ø¯Ù† ØªØ§Ø¨Ø¹ Ø¬Ø¯ÛŒØ¯:
 * 1. Ø§Ø² if (!function_exists('...')) Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯
 * 2. Ù…Ø³ØªÙ†Ø¯Ø§Øª Ú©Ø§Ù…Ù„ Ø¨Ù†ÙˆÛŒØ³ÛŒØ¯
 * 3. Ù…Ø«Ø§Ù„ Ø§Ø³ØªÙØ§Ø¯Ù‡ Ø§Ø¶Ø§ÙÙ‡ Ú©Ù†ÛŒØ¯
 */