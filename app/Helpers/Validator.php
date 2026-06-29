<?php
/**
 * ============================================
 * کلاس اعتبارسنجی پیشرفته (Validator)
 * ============================================
 * اعتبارسنجی داده‌ها با Rule های متنوع
 * پشتیبانی از زنجیره‌ای (Fluent Interface)
 * پیام‌های خطای سفارشی
 * Rule های سفارشی با Closure
 * پشتیبانی از آرایه‌ها و فایل‌ها
 */

namespace App\Helpers;

use Exception;

class Validator {
    private $data;
    private $rules = [];
    private $errors = [];
    private $customMessages = [];
    private $customAttributes = [];
    private $customRules = [];
    private $failedRules = [];
    
    // ──────────────────────────────────────
    // Constructor
    // ──────────────────────────────────────
    public function __construct(array $data, array $rules = [], array $messages = [], array $attributes = []) {
        $this->data = $data;
        $this->customMessages = $messages;
        $this->customAttributes = $attributes;
        
        if (!empty($rules)) {
            $this->setRules($rules);
        }
    }
    
    // ──────────────────────────────────────
    // ساخت سریع (Static Factory)
    // ──────────────────────────────────────
    public static function make(array $data, array $rules, array $messages = [], array $attributes = []) {
        return new self($data, $rules, $messages, $attributes);
    }
    
    // ──────────────────────────────────────
    // تنظیم Rule ها
    // ──────────────────────────────────────
    public function setRules(array $rules) {
        $this->rules = [];
        
        foreach ($rules as $field => $ruleSet) {
            $this->rules[$field] = $this->parseRules($ruleSet);
        }
        
        return $this;
    }
    
    // ──────────────────────────────────────
    // تجزیه Rule ها
    // ──────────────────────────────────────
    private function parseRules($rules) {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        if (!is_array($rules)) {
            $rules = [$rules];
        }
        
        $parsed = [];
        foreach ($rules as $rule) {
            if ($rule instanceof \Closure) {
                $parsed[] = ['name' => 'closure', 'params' => [$rule]];
                continue;
            }
            
            if (is_string($rule)) {
                $parts = explode(':', $rule, 2);
                $name = $parts[0];
                $params = isset($parts[1]) ? explode(',', $parts[1]) : [];
                $parsed[] = ['name' => $name, 'params' => $params];
            }
        }
        
        return $parsed;
    }
    
    // ──────────────────────────────────────
    // افزودن Rule سفارشی
    // ──────────────────────────────────────
    public function addCustomRule($name, \Closure $callback) {
        $this->customRules[$name] = $callback;
        return $this;
    }
    
    // ──────────────────────────────────────
    // اجرای اعتبارسنجی
    // ──────────────────────────────────────
    public function validate() {
        $this->errors = [];
        $this->failedRules = [];
        
        foreach ($this->rules as $field => $rules) {
            $value = $this->getValue($field);
            
            foreach ($rules as $rule) {
                $ruleName = $rule['name'];
                $params = $rule['params'];
                
                // اگر فیلد optional است و مقدار خالی است، بقیه rule ها رو رد کن
                if ($ruleName !== 'required' && $ruleName !== 'required_if' && !$this->hasValue($field)) {
                    continue;
                }
                
                $passed = $this->validateRule($field, $value, $ruleName, $params);
                
                if (!$passed) {
                    break; // بعد از اولین خطا، بقیه rule ها رو برای این فیلد بررسی نکن
                }
            }
        }
        
        return empty($this->errors);
    }
    
    // ──────────────────────────────────────
    // بررسی یک Rule
    // ──────────────────────────────────────
    private function validateRule($field, $value, $ruleName, $params) {
        $method = 'rule' . str_replace('_', '', ucwords($ruleName, '_'));
        
        // Rule سفارشی
        if (isset($this->customRules[$ruleName])) {
            $passed = call_user_func($this->customRules[$ruleName], $value, $params, $field, $this->data);
            
            if ($passed !== true) {
                $message = is_string($passed) ? $passed : $this->getMessage($field, $ruleName, $params);
                $this->addError($field, $message, $ruleName);
                return false;
            }
            
            return true;
        }
        
        // Closure
        if ($ruleName === 'closure' && isset($params[0]) && $params[0] instanceof \Closure) {
            $passed = call_user_func($params[0], $value, $field, $this->data);
            
            if ($passed !== true) {
                $message = is_string($passed) ? $passed : 'مقدار فیلد معتبر نیست';
                $this->addError($field, $message, $ruleName);
                return false;
            }
            
            return true;
        }
        
        // متد داخلی
        if (method_exists($this, $method)) {
            $passed = $this->$method($field, $value, $params);
            
            if (!$passed) {
                $message = $this->getMessage($field, $ruleName, $params);
                $this->addError($field, $message, $ruleName);
                return false;
            }
            
            return true;
        }
        
        throw new Exception("Rule '{$ruleName}' تعریف نشده است");
    }
    
    // ──────────────────────────────────────
    // دریافت مقدار (پشتیبانی از Dot Notation)
    // ──────────────────────────────────────
    private function getValue($field) {
        if (strpos($field, '.') === false) {
            return $this->data[$field] ?? null;
        }
        
        $keys = explode('.', $field);
        $value = $this->data;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    // ──────────────────────────────────────
    // بررسی وجود مقدار
    // ──────────────────────────────────────
    private function hasValue($field) {
        $value = $this->getValue($field);
        return $value !== null && $value !== '' && $value !== [];
    }
    
    // ──────────────────────────────────────
    // افزودن خطا
    // ──────────────────────────────────────
    private function addError($field, $message, $rule) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
        $this->failedRules[$field][] = $rule;
    }
    
    // ──────────────────────────────────────
    // دریافت پیام خطا
    // ──────────────────────────────────────
    private function getMessage($field, $rule, $params) {
        // پیام سفارشی برای فیلد و rule خاص
        $customKey = "{$field}.{$rule}";
        if (isset($this->customMessages[$customKey])) {
            return $this->replacePlaceholders($this->customMessages[$customKey], $field, $params);
        }
        
        // پیام سفارشی برای فیلد
        if (isset($this->customMessages[$field])) {
            return $this->replacePlaceholders($this->customMessages[$field], $field, $params);
        }
        
        // پیام پیش‌فرض
        $defaultMessages = $this->getDefaultMessages();
        $message = $defaultMessages[$rule] ?? 'مقدار فیلد معتبر نیست';
        
        return $this->replacePlaceholders($message, $field, $params);
    }
    
    // ──────────────────────────────────────
    // جایگذاری Placeholder ها
    // ──────────────────────────────────────
    private function replacePlaceholders($message, $field, $params) {
        $attribute = $this->customAttributes[$field] ?? $field;
        
        $replacements = [
            ':field' => $attribute,
            ':param' => $params[0] ?? '',
            ':param2' => $params[1] ?? '',
        ];
        
        // افزودن params به صورت :param0, :param1, ...
        foreach ($params as $i => $param) {
            $replacements[":param{$i}"] = $param;
        }
        
        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
    
    // ──────────────────────────────────────
    // پیام‌های پیش‌فرض
    // ──────────────────────────────────────
    private function getDefaultMessages() {
        return [
            'required' => ':field الزامی است',
            'email' => ':field باید یک ایمیل معتبر باشد',
            'numeric' => ':field باید عدد باشد',
            'integer' => ':field باید عدد صحیح باشد',
            'min' => ':field باید حداقل :param کاراکتر باشد',
            'max' => ':field نباید بیشتر از :param کاراکتر باشد',
            'between' => ':field باید بین :param و :param2 باشد',
            'url' => ':field باید یک URL معتبر باشد',
            'ip' => ':field باید یک IP معتبر باشد',
            'regex' => 'فرمت :field معتبر نیست',
            'in' => ':field باید یکی از مقادیر مجاز باشد',
            'not_in' => ':field نامعتبر است',
            'confirmed' => 'تأیید :field مطابقت ندارد',
            'same' => ':field باید با :param یکسان باشد',
            'different' => ':field باید با :param متفاوت باشد',
            'alpha' => ':field فقط می‌تواند شامل حروف باشد',
            'alpha_num' => ':field فقط می‌تواند شامل حروف و اعداد باشد',
            'alpha_dash' => ':field فقط می‌تواند شامل حروف، اعداد، - و _ باشد',
            'date' => ':field باید یک تاریخ معتبر باشد',
            'before' => ':field باید قبل از :param باشد',
            'after' => ':field باید بعد از :param باشد',
            'unique' => ':field قبلاً استفاده شده است',
            'exists' => ':field یافت نشد',
            'telegram_token' => ':field یک توکن تلگرام معتبر نیست',
            'telegram_id' => ':field یک آیدی تلگرام معتبر نیست',
            'file' => ':field باید یک فایل باشد',
            'image' => ':field باید یک تصویر باشد',
            'mimes' => ':field باید یکی از فرمت‌های :param باشد',
            'max_file' => 'حجم :field نباید بیشتر از :param کیلوبایت باشد',
            'boolean' => ':field باید true یا false باشد',
            'array' => ':field باید یک آرایه باشد',
            'json' => ':field باید یک JSON معتبر باشد',
        ];
    }
    
    // ══════════════════════════════════════
    // Rule های داخلی
    // ══════════════════════════════════════
    
    protected function ruleRequired($field, $value, $params) {
        return $this->hasValue($field);
    }
    
    protected function ruleEmail($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    protected function ruleNumeric($field, $value, $params) {
        return is_numeric($value);
    }
    
    protected function ruleInteger($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    protected function ruleMin($field, $value, $params) {
        $min = (int)$params[0];
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        
        return mb_strlen((string)$value) >= $min;
    }
    
    protected function ruleMax($field, $value, $params) {
        $max = (int)$params[0];
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        
        return mb_strlen((string)$value) <= $max;
    }
    
    protected function ruleBetween($field, $value, $params) {
        $min = (float)$params[0];
        $max = (float)$params[1];
        $size = is_numeric($value) ? (float)$value : mb_strlen((string)$value);
        
        return $size >= $min && $size <= $max;
    }
    
    protected function ruleUrl($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    protected function ruleIp($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }
    
    protected function ruleRegex($field, $value, $params) {
        $pattern = $params[0] ?? '';
        return preg_match($pattern, (string)$value) === 1;
    }
    
    protected function ruleIn($field, $value, $params) {
        return in_array((string)$value, $params);
    }
    
    protected function ruleNotIn($field, $value, $params) {
        return !in_array((string)$value, $params);
    }
    
    protected function ruleConfirmed($field, $value, $params) {
        $confirmField = "{$field}_confirmation";
        $confirmValue = $this->getValue($confirmField);
        return $value === $confirmValue;
    }
    
    protected function ruleSame($field, $value, $params) {
        $otherField = $params[0];
        $otherValue = $this->getValue($otherField);
        return $value === $otherValue;
    }
    
    protected function ruleDifferent($field, $value, $params) {
        $otherField = $params[0];
        $otherValue = $this->getValue($otherField);
        return $value !== $otherValue;
    }
    
    protected function ruleAlpha($field, $value, $params) {
        return preg_match('/^[\pL\pM]+$/u', (string)$value) === 1;
    }
    
    protected function ruleAlphaNum($field, $value, $params) {
        return preg_match('/^[\pL\pM\pN]+$/u', (string)$value) === 1;
    }
    
    protected function ruleAlphaDash($field, $value, $params) {
        return preg_match('/^[\pL\pM\pN_-]+$/u', (string)$value) === 1;
    }
    
    protected function ruleDate($field, $value, $params) {
        if (empty($value)) return false;
        $format = $params[0] ?? 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }
    
    protected function ruleBefore($field, $value, $params) {
        $date = $params[0];
        return strtotime($value) < strtotime($date);
    }
    
    protected function ruleAfter($field, $value, $params) {
        $date = $params[0];
        return strtotime($value) > strtotime($date);
    }
    
    protected function ruleBoolean($field, $value, $params) {
        return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
    }
    
    protected function ruleArray($field, $value, $params) {
        return is_array($value);
    }
    
    protected function ruleJson($field, $value, $params) {
        if (!is_string($value)) return false;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    protected function ruleUnique($field, $value, $params) {
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;
        $idColumn = $params[3] ?? 'id';
        
        if (empty($table)) return true;
        
        try {
            $db = \App\Core\Database::getInstance();
            $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
            $sqlParams = [$value];
            
            if ($exceptId !== null) {
                $sql .= " AND {$idColumn} != ?";
                $sqlParams[] = $exceptId;
            }
            
            $count = $db->fetchColumn($sql, $sqlParams);
            return $count == 0;
        } catch (Exception $e) {
            return true;
        }
    }
    
    protected function ruleExists($field, $value, $params) {
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        
        if (empty($table)) return true;
        
        try {
            $db = \App\Core\Database::getInstance();
            $count = $db->fetchColumn(
                "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?",
                [$value]
            );
            return $count > 0;
        } catch (Exception $e) {
            return true;
        }
    }
    
    protected function ruleTelegramToken($field, $value, $params) {
        return preg_match('/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/', (string)$value) === 1;
    }
    
    protected function ruleTelegramId($field, $value, $params) {
        return is_numeric($value) && $value >= 10000 && $value <= 9999999999;
    }
    
    protected function ruleFile($field, $value, $params) {
        return is_array($value) && isset($value['tmp_name']) && isset($value['error']);
    }
    
    protected function ruleImage($field, $value, $params) {
        if (!$this->ruleFile($field, $value, $params)) return false;
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $value['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mime, $allowedMimes);
    }
    
    protected function ruleMimes($field, $value, $params) {
        if (!$this->ruleFile($field, $value, $params)) return false;
        
        $extension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        return in_array($extension, $params);
    }
    
    protected function ruleMaxFile($field, $value, $params) {
        if (!$this->ruleFile($field, $value, $params)) return false;
        
        $maxKb = (int)$params[0];
        return ($value['size'] / 1024) <= $maxKb;
    }
    
    protected function ruleRequiredIf($field, $value, $params) {
        $otherField = $params[0] ?? '';
        $otherValue = $params[1] ?? null;
        $actualValue = $this->getValue($otherField);
        
        if ((string)$actualValue === (string)$otherValue) {
            return $this->hasValue($field);
        }
        
        return true;
    }
    
    // ══════════════════════════════════════
    // متدهای دریافت نتیجه
    // ══════════════════════════════════════
    
    public function fails() {
        return !empty($this->errors);
    }
    
    public function passes() {
        return empty($this->errors);
    }
    
    public function errors() {
        return $this->errors;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function firstError($field = null) {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        
        return null;
    }
    
    public function allErrors() {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        }
        return $messages;
    }
    
    public function failedRules() {
        return $this->failedRules;
    }
    
    // ──────────────────────────────────────
    // دریافت داده‌های اعتبارسنجی شده
    // ──────────────────────────────────────
    public function validated() {
        $validated = [];
        
        foreach (array_keys($this->rules) as $field) {
            $value = $this->getValue($field);
            
            if (strpos($field, '.') !== false) {
                $this->setNestedValue($validated, $field, $value);
            } else {
                $validated[$field] = $value;
            }
        }
        
        return $validated;
    }
    
    private function setNestedValue(&$array, $key, $value) {
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }
    }
    
    // ──────────────────────────────────────
    // تبدیل خطاها به JSON
    // ──────────────────────────────────────
    public function toJson() {
        return json_encode([
            'success' => false,
            'errors' => $this->errors
        ], JSON_UNESCAPED_UNICODE);
    }
    
    // ──────────────────────────────────────
    // ارسال پاسخ JSON و خروج
    // ──────────────────────────────────────
    public function respondJson($statusCode = 422) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo $this->toJson();
        exit;
    }
}