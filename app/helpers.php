<?php
/**
 * ============================================
 * Helper Functions - توابع کمکی Global
 * ============================================
 * نسخه: 2.1.2
 * 
 * مجموعه‌ای از توابع کمکی پرکاربرد که
 * در تمام پروژه قابل استفاده هستن
 * 
 * دسته‌بندی:
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

// ═══════════════════════════════════════════
// 1. String Helpers
// ═══════════════════════════════════════════

if (!function_exists('str_limit')) {
    /**
     * محدود کردن طول رشته
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
     * تبدیل رشته به slug
     */
    function str_slug($title, $separator = '-') {
        $title = mb_strtolower($title);
        
        // تبدیل حروف فارسی به انگلیسی (اختیاری)
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $title = str_replace($persian, $english, $title);
        
        // حذف کاراکترهای غیر مجاز
        $title = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $title);
        
        // جایگزینی فاصله با separator
        $title = preg_replace('/[\s-]+/', $separator, $title);
        
        return trim($title, $separator);
    }
}

if (!function_exists('str_random')) {
    /**
     * تولید رشته تصادفی
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
     * بررسی وجود یکی از کلمات در رشته
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
     * بررسی شروع رشته با یک مقدار
     */
    function str_starts_with($haystack, $needle) {
        return mb_strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * بررسی پایان رشته با یک مقدار
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
     * افزودن کاراکتر به سمت چپ
     */
    function str_pad_left($input, $padLength, $padString = '0') {
        return str_pad($input, $padLength, $padString, STR_PAD_LEFT);
    }
}

if (!function_exists('str_word_count_fa')) {
    /**
     * شمارش کلمات فارسی
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
     * پاکسازی HTML (حذف تگ‌ها)
     */
    function clean_html($html, $allowedTags = '') {
        return strip_tags($html, $allowedTags);
    }
}

if (!function_exists('nl2p')) {
    /**
     * تبدیل خطوط جدید به پاراگراف HTML
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

// ═══════════════════════════════════════════
// 2. Array Helpers
// ═══════════════════════════════════════════

if (!function_exists('array_get')) {
    /**
     * دریافت مقدار از آرایه تو در تو با Dot Notation
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
     * تنظیم مقدار در آرایه تو در تو با Dot Notation
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
     * بررسی وجود کلید در آرایه تو در تو
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
     * حذف کلید از آرایه تو در تو
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
     * دریافت فقط کلیدهای مشخص از آرایه
     */
    function array_only($array, array $keys) {
        return array_intersect_key($array, array_flip($keys));
    }
}

if (!function_exists('array_except')) {
    /**
     * حذف کلیدهای مشخص از آرایه
     */
    function array_except($array, array $keys) {
        return array_diff_key($array, array_flip($keys));
    }
}

if (!function_exists('array_flatten')) {
    /**
     * تخت کردن آرایه چند بعدی
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
     * استخراج یک کلید خاص از آرایه چند بعدی
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
     * گروه‌بندی آرایه بر اساس یک کلید
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
     * مرتب‌سازی آرایه بر اساس یک کلید
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

// ═══════════════════════════════════════════
// 3. Date/Time Helpers
// ═══════════════════════════════════════════

if (!function_exists('now')) {
    /**
     * زمان فعلی
     */
    function now() {
        return new DateTime();
    }
}

if (!function_exists('today')) {
    /**
     * تاریخ امروز
     */
    function today() {
        return date('Y-m-d');
    }
}

if (!function_exists('carbon')) {
    /**
     * ساخت DateTime از رشته
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
     * فرمت تاریخ به فارسی
     */
    function format_date($date, $format = 'Y/m/d') {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * فرمت تاریخ و ساعت به فارسی
     */
    function format_datetime($date, $format = 'Y/m/d H:i:s') {
        return format_date($date, $format);
    }
}

if (!function_exists('time_ago')) {
    /**
     * تبدیل زمان به "x دقیقه پیش"
     */
    function time_ago($datetime) {
        $time = is_numeric($datetime) ? $datetime : strtotime($datetime);
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
}

if (!function_exists('days_ago')) {
    /**
     * تعداد روزهای گذشته
     */
    function days_ago($date) {
        $time = is_numeric($date) ? $date : strtotime($date);
        return floor((time() - $time) / 86400);
    }
}

if (!function_exists('is_today')) {
    /**
     * بررسی اینکه تاریخ امروز است
     */
    function is_today($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }
}

if (!function_exists('is_yesterday')) {
    /**
     * بررسی اینکه تاریخ دیروز است
     */
    function is_yesterday($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d', strtotime('-1 day'));
    }
}

if (!function_exists('is_past')) {
    /**
     * بررسی گذشته بودن تاریخ
     */
    function is_past($date) {
        return strtotime($date) < time();
    }
}

if (!function_exists('is_future')) {
    /**
     * بررسی آینده بودن تاریخ
     */
    function is_future($date) {
        return strtotime($date) > time();
    }
}

if (!function_exists('diff_for_humans')) {
    /**
     * تفاوت زمانی به زبان انسانی
     */
    function diff_for_humans($date1, $date2 = null) {
        $time1 = is_numeric($date1) ? $date1 : strtotime($date1);
        $time2 = $date2 ? (is_numeric($date2) ? $date2 : strtotime($date2)) : time();
        
        $diff = abs($time2 - $time1);
        
        if ($diff < 60) {
            return "{$diff} ثانیه";
        } elseif ($diff < 3600) {
            return floor($diff / 60) . " دقیقه";
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . " ساعت";
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . " روز";
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . " ماه";
        } else {
            return floor($diff / 31536000) . " سال";
        }
    }
}

// ═══════════════════════════════════════════
// 4. File Helpers
// ═══════════════════════════════════════════

if (!function_exists('file_exists_safe')) {
    /**
     * بررسی وجود فایل با مدیریت خطا
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
     * فرمت اندازه فایل
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
     * دریافت پسوند فایل
     */
    function file_extension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}

if (!function_exists('file_name')) {
    /**
     * دریافت نام فایل بدون پسوند
     */
    function file_name($filename) {
        return pathinfo($filename, PATHINFO_FILENAME);
    }
}

if (!function_exists('file_mime')) {
    /**
     * دریافت MIME type فایل
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
     * اطمینان از وجود پوشه
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
     * نوشتن در فایل
     */
    function write_file($path, $content, $append = false) {
        ensure_directory(dirname($path));
        
        $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
        
        return file_put_contents($path, $content, $flags) !== false;
    }
}

if (!function_exists('read_file')) {
    /**
     * خواندن محتوای فایل
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
     * حذف فایل
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
     * کپی فایل
     */
    function copy_file($source, $destination) {
        ensure_directory(dirname($destination));
        return copy($source, $destination);
    }
}

if (!function_exists('move_file')) {
    /**
     * جابجایی فایل
     */
    function move_file($source, $destination) {
        ensure_directory(dirname($destination));
        return rename($source, $destination);
    }
}

// ═══════════════════════════════════════════
// 5. URL Helpers
// ═══════════════════════════════════════════

if (!function_exists('asset')) {
    /**
     * دریافت URL فایل asset
     */
    function asset($path) {
        $baseUrl = config('app.url', '');
        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * دریافت مسیر فایل عمومی
     */
    function public_path($path = '') {
        return PUBLIC_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('current_url')) {
    /**
     * دریافت URL فعلی
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
     * دریافت URL قبلی
     */
    function previous_url($default = '/') {
        return $_SERVER['HTTP_REFERER'] ?? $default;
    }
}

if (!function_exists('query_string')) {
    /**
     * ساخت Query String
     */
    function query_string(array $params) {
        return http_build_query($params);
    }
}

if (!function_exists('merge_query')) {
    /**
     * ادغام پارامترهای URL
     */
    function merge_query(array $params) {
        $current = $_GET ?? [];
        return http_build_query(array_merge($current, $params));
    }
}

if (!function_exists('is_url')) {
    /**
     * بررسی URL معتبر
     */
    function is_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

// ═══════════════════════════════════════════
// 6. HTML Helpers
// ═══════════════════════════════════════════

if (!function_exists('link_to')) {
    /**
     * ساخت لینک HTML
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
     * ساخت hidden input برای CSRF
     */
    function csrf_field() {
        $session = \App\Core\Session::getInstance();
        $token = htmlspecialchars($session->getCsrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * ساخت hidden input برای HTTP Method
     */
    function method_field($method) {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * دریافت مقدار قبلی input
     */
    function old($key, $default = '') {
        $session = \App\Core\Session::getInstance();
        $oldInput = $session->getFlash('_old_input', []);
        
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('active_class')) {
    /**
     * دریافت کلاس active برای منو
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
     * ساخت HTML Pagination
     */
    function pagination($pagination, $baseUrl = '') {
        if ($pagination['total_pages'] <= 1) {
            return '';
        }
        
        $html = '<div class="pagination">';
        
        // Previous
        if ($pagination['current_page'] > 1) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '" class="pagination-link">قبلی</a>';
        }
        
        // Pages
        for ($i = 1; $i <= $pagination['total_pages']; $i++) {
            $active = $i === $pagination['current_page'] ? 'active' : '';
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link ' . $active . '">' . $i . '</a>';
        }
        
        // Next
        if ($pagination['current_page'] < $pagination['total_pages']) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '" class="pagination-link">بعدی</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// ═══════════════════════════════════════════
// 7. Number Helpers
// ═══════════════════════════════════════════

if (!function_exists('format_number')) {
    /**
     * فرمت عدد با جداکننده
     */
    function format_number($number, $decimals = 0) {
        return number_format((float)$number, $decimals, '.', ',');
    }
}

if (!function_exists('format_money')) {
    /**
     * فرمت پول (تومان)
     */
    function format_money($amount, $currency = 'تومان') {
        return format_number($amount) . ' ' . $currency;
    }
}

if (!function_exists('format_toman')) {
    /**
     * فرمت تومان
     */
    function format_toman($amount) {
        return format_money($amount, 'تومان');
    }
}

if (!function_exists('to_english_numbers')) {
    /**
     * تبدیل اعداد فارسی به انگلیسی
     */
    function to_english_numbers($string) {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        
        $string = str_replace($persian, $english, $string);
        return str_replace($arabic, $english, $string);
    }
}

if (!function_exists('to_persian_numbers')) {
    /**
     * تبدیل اعداد انگلیسی به فارسی
     */
    function to_persian_numbers($string) {
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        
        return str_replace($english, $persian, $string);
    }
}

if (!function_exists('percentage')) {
    /**
     * محاسبه درصد
     */
    function percentage($part, $total, $decimals = 2) {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, $decimals);
    }
}

if (!function_exists('clamp')) {
    /**
     * محدود کردن عدد بین حداقل و حداکثر
     */
    function clamp($value, $min, $max) {
        return max($min, min($max, $value));
    }
}

// ═══════════════════════════════════════════
// 8. Debug Helpers
// ═══════════════════════════════════════════

if (!function_exists('pre')) {
    /**
     * نمایش در تگ pre
     */
    function pre($data) {
        echo '<pre style="background:#1f2937;color:#fff;padding:15px;border-radius:8px;direction:ltr;text-align:left;overflow-x:auto;">';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('log_debug')) {
    /**
     * لاگ پیام Debug
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
     * لاگ پیام Info
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
     * لاگ پیام Error
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
     * اندازه‌گیری زمان اجرای کد
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
     * دریافت میزان استفاده حافظه
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

// ═══════════════════════════════════════════
// 9. Validation Helpers
// ═══════════════════════════════════════════

if (!function_exists('is_email')) {
    /**
     * بررسی ایمیل معتبر
     */
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('is_numeric_value')) {
    /**
     * بررسی عدد بودن
     */
    function is_numeric_value($value) {
        return is_numeric($value);
    }
}

if (!function_exists('is_json')) {
    /**
     * بررسی JSON معتبر
     */
    function is_json($string) {
        if (!is_string($string)) return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('is_serialized')) {
    /**
     * بررسی serialized بودن
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
     * بررسی خالی بودن مقدار
     */
    function is_empty_value($value) {
        if (is_null($value)) return true;
        if (is_string($value) && trim($value) === '') return true;
        if (is_array($value) && empty($value)) return true;
        return false;
    }
}

// ═══════════════════════════════════════════
// 10. Telegram Helpers
// ═══════════════════════════════════════════

if (!function_exists('is_telegram_id')) {
    /**
     * بررسی آیدی تلگرام معتبر
     */
    function is_telegram_id($id) {
        return is_numeric($id) && $id >= 10000 && $id <= 9999999999;
    }
}

if (!function_exists('is_bot_token')) {
    /**
     * بررسی توکن ربات تلگرام
     */
    function is_bot_token($token) {
        return preg_match('/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/', $token) === 1;
    }
}

if (!function_exists('telegram_link')) {
    /**
     * ساخت لینک تلگرام
     */
    function telegram_link($username) {
        $username = ltrim($username, '@');
        return "https://t.me/{$username}";
    }
}

if (!function_exists('format_telegram_html')) {
    /**
     * فرمت متن برای HTML تلگرام
     */
    function format_telegram_html($text) {
        // تبدیل newline به <br>
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
     * Escape برای HTML تلگرام
     */
    function escape_telegram_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_user_mention')) {
    /**
     * ساخت mention کاربر
     */
    function get_user_mention($userId, $name = null) {
        if ($name) {
            return "<a href=\"tg://user?id={$userId}\">" . htmlspecialchars($name) . "</a>";
        }
        return "<code>{$userId}</code>";
    }
}

// ═══════════════════════════════════════════
// 11. Misc Helpers
// ═══════════════════════════════════════════

if (!function_exists('env')) {
    /**
     * دریافت متغیر محیطی
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
     * تلاش مجدد در صورت خطا
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
     * اجرای callback روی مقدار و برگرداندن مقدار
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
     * دریافت مقدار (اگر callable باشد، اجرا می‌شود)
     */
    function value($value, ...$args) {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('class_basename')) {
    /**
     * دریافت نام کلاس بدون namespace
     */
    function class_basename($class) {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('humanize')) {
    /**
     * تبدیل snake_case به Human Readable
     */
    function humanize($value) {
        return ucwords(str_replace(['_', '-'], ' ', $value));
    }
}

if (!function_exists('title_case')) {
    /**
     * تبدیل به Title Case
     */
    function title_case($value) {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('camel_case')) {
    /**
     * تبدیل به camelCase
     */
    function camel_case($value) {
        $value = ucwords(str_replace(['_', '-'], ' ', $value));
        $value = str_replace(' ', '', $value);
        return lcfirst($value);
    }
}

if (!function_exists('snake_case')) {
    /**
     * تبدیل به snake_case
     */
    function snake_case($value, $delimiter = '_') {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        return $value;
    }
}

if (!function_exists('kebab_case')) {
    /**
     * تبدیل به kebab-case
     */
    function kebab_case($value) {
        return snake_case($value, '-');
    }
}

// ═══════════════════════════════════════════
// 12. Cache Helpers
// ═══════════════════════════════════════════

if (!function_exists('cache_get')) {
    /**
     * دریافت از کش
     */
    function cache_get($key, $default = null) {
        return \App\Core\Cache::getInstance()->get($key, $default);
    }
}

if (!function_exists('cache_set')) {
    /**
     * ذخیره در کش
     */
    function cache_set($key, $value, $ttl = 3600) {
        return \App\Core\Cache::getInstance()->set($key, $value, $ttl);
    }
}

if (!function_exists('cache_remember')) {
    /**
     * دریافت از کش یا ساخت و ذخیره
     */
    function cache_remember($key, $ttl, callable $callback) {
        return \App\Core\Cache::getInstance()->remember($key, $ttl, $callback);
    }
}

if (!function_exists('cache_forget')) {
    /**
     * حذف از کش
     */
    function cache_forget($key) {
        return \App\Core\Cache::getInstance()->delete($key);
    }
}

// ═══════════════════════════════════════════
// 13. Session Helpers
// ═══════════════════════════════════════════

if (!function_exists('session_get')) {
    /**
     * دریافت از Session
     */
    function session_get($key, $default = null) {
        return \App\Core\Session::getInstance()->get($key, $default);
    }
}

if (!function_exists('session_set')) {
    /**
     * ذخیره در Session
     */
    function session_set($key, $value) {
        return \App\Core\Session::getInstance()->set($key, $value);
    }
}

if (!function_exists('session_has')) {
    /**
     * بررسی وجود در Session
     */
    function session_has($key) {
        return \App\Core\Session::getInstance()->has($key);
    }
}

if (!function_exists('session_forget')) {
    /**
     * حذف از Session
     */
    function session_forget($key) {
        return \App\Core\Session::getInstance()->remove($key);
    }
}

// ═══════════════════════════════════════════
// 14. Database Helpers
// ═══════════════════════════════════════════

if (!function_exists('db')) {
    /**
     * دریافت Database Instance
     */
    function db() {
        return \App\Core\Database::getInstance();
    }
}

// ═══════════════════════════════════════════
// 15. JSON Helpers
// ═══════════════════════════════════════════

if (!function_exists('json_response')) {
    /**
     * ارسال پاسخ JSON
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
     * پاسخ JSON موفق
     */
    function json_success($data = [], $message = 'موفق') {
        json_response([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
}

if (!function_exists('json_error')) {
    /**
     * پاسخ JSON خطا
     */
    function json_error($message = 'خطا', $statusCode = 400, $errors = []) {
        json_response([
            'success' => false,
            'error' => [
                'message' => $message,
                'errors' => $errors
            ]
        ], $statusCode);
    }
}

// ═══════════════════════════════════════════
// 16. View Helpers
// ═══════════════════════════════════════════

if (!function_exists('view')) {
    /**
     * بارگذاری View
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
     * رندر و نمایش View
     */
    function render_view($path, $data = []) {
        echo view($path, $data);
    }
}

// ═══════════════════════════════════════════
// 17. End of Helpers
// ═══════════════════════════════════════════

/**
 * پایان فایل helpers.php
 * 
 * برای اضافه کردن تابع جدید:
 * 1. از if (!function_exists('...')) استفاده کنید
 * 2. مستندات کامل بنویسید
 * 3. مثال استفاده اضافه کنید
 */