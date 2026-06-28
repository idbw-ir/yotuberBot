<?php
/**
 * ============================================
 * کلاس اعتبارسنجی ورودی‌ها
 * ============================================
 */

class Validator {
    private $data;
    private $errors = [];
    private $rules = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    // ──────────────────────────────────────
    // فیلد اجباری
    // ──────────────────────────────────────
    public function required($field, $message = null) {
        $value = trim($this->data[$field] ?? '');
        if (empty($value)) {
            $this->addError($field, $message ?? 'این فیلد الزامی است');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // حداقل طول
    // ──────────────────────────────────────
    public function minLength($field, $length, $message = null) {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) < $length) {
            $this->addError($field, $message ?? "حداقل {$length} کاراکتر لازم است");
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // حداکثر طول
    // ──────────────────────────────────────
    public function maxLength($field, $length, $message = null) {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) > $length) {
            $this->addError($field, $message ?? "حداکثر {$length} کاراکتر مجاز است");
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // ایمیل معتبر
    // ──────────────────────────────────────
    public function email($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? 'ایمیل معتبر نیست');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // URL معتبر
    // ──────────────────────────────────────
    public function url($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $message ?? 'آدرس URL معتبر نیست');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // عدد صحیح
    // ──────────────────────────────────────
    public function numeric($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, $message ?? 'باید عدد باشد');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // تطابق دو فیلد
    // ──────────────────────────────────────
    public function match($field1, $field2, $message = null) {
        $value1 = $this->data[$field1] ?? '';
        $value2 = $this->data[$field2] ?? '';
        if ($value1 !== $value2) {
            $this->addError($field1, $message ?? 'مقدار دو فیلد یکسان نیست');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // الگوی Regex
    // ──────────────────────────────────────
    public function regex($field, $pattern, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, $message ?? 'فرمت وارد شده معتبر نیست');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // توکن تلگرام
    // ──────────────────────────────────────
    public function telegramToken($field, $message = null) {
        $value = $this->data[$field] ?? '';
        $pattern = '/^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$/';
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, $message ?? 'توکن ربات تلگرام معتبر نیست');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // آیدی عددی
    // ──────────────────────────────────────
    public function telegramId($field, $message = null) {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && (!is_numeric($value) || $value < 100000)) {
            $this->addError($field, $message ?? 'آیدی عددی تلگرام معتبر نیست');
        }
        return $this;
    }
    
    // ──────────────────────────────────────
    // افزودن خطا
    // ──────────────────────────────────────
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    // ──────────────────────────────────────
    // بررسی خطا
    // ──────────────────────────────────────
    public function fails() {
        return !empty($this->errors);
    }
    
    public function passes() {
        return empty($this->errors);
    }
    
    // ──────────────────────────────────────
    // دریافت خطاها
    // ──────────────────────────────────────
    public function errors() {
        return $this->errors;
    }
    
    public function firstError($field) {
        return $this->errors[$field][0] ?? null;
    }
    
    public function allErrors() {
        $messages = [];
        foreach ($this->errors as $field => $errors) {
            foreach ($errors as $error) {
                $messages[] = $error;
            }
        }
        return $messages;
    }
}