-- ============================================
-- Youtuber Bot - Database Schema
-- ============================================
-- نسخه: 2.0.0
-- تاریخ: 2026-07-01
-- 
-- این فایل ساختار کامل دیتابیس رو تعریف می‌کنه
-- شامل:
-- - تمام جداول
-- - ایندکس‌ها
-- - Foreign Keys
-- - Triggers
-- - Views
-- - Stored Procedures
-- - داده‌های پیش‌فرض
--
-- نحوه اجرا:
-- mysql -u username -p database_name < schema.sql
-- ============================================

-- تنظیمات اولیه
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ═══════════════════════════════════════════
-- 1. جدول کاربران (Users)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL COMMENT 'آیدی عددی تلگرام',
  `username` VARCHAR(100) DEFAULT NULL COMMENT 'یوزرنیم تلگرام',
  `first_name` VARCHAR(100) DEFAULT NULL COMMENT 'نام',
  `last_name` VARCHAR(100) DEFAULT NULL COMMENT 'نام خانوادگی',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT 'شماره تماس',
  `bio` TEXT DEFAULT NULL COMMENT 'بیوگرافی',
  `language_code` VARCHAR(10) DEFAULT 'fa' COMMENT 'کد زبان',
  `is_vip` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'وضعیت VIP',
  `vip_expires_at` DATETIME DEFAULT NULL COMMENT 'تاریخ انقضای VIP',
  `blocked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'بلاک شده',
  `notes` TEXT DEFAULT NULL COMMENT 'یادداشت ادمین',
  `total_donations` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'مجموع دونیت‌ها',
  `donation_count` INT UNSIGNED DEFAULT 0 COMMENT 'تعداد دونیت‌ها',
  `message_count` INT UNSIGNED DEFAULT 0 COMMENT 'تعداد پیام‌ها',
  `last_seen` DATETIME DEFAULT NULL COMMENT 'آخرین بازدید',
  `last_seen_ip` VARCHAR(45) DEFAULT NULL COMMENT 'IP آخرین بازدید',
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ عضویت',
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_is_vip` (`is_vip`),
  KEY `idx_blocked` (`blocked`),
  KEY `idx_last_seen` (`last_seen`),
  KEY `idx_joined_at` (`joined_at`),
  KEY `idx_total_donations` (`total_donations`),
  FULLTEXT KEY `ft_search` (`first_name`, `last_name`, `username`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='کاربران ربات تلگرام';

-- ═══════════════════════════════════════════
-- 2. جدول ادمین‌ها (Admins)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL COMMENT 'نام کاربری',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'رمز عبور Hash شده',
  `name` VARCHAR(100) NOT NULL COMMENT 'نام کامل',
  `email` VARCHAR(100) DEFAULT NULL COMMENT 'ایمیل',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT 'شماره تماس',
  `role` ENUM('super_admin','admin','editor','moderator') NOT NULL DEFAULT 'admin' COMMENT 'نقش',
  `bio` TEXT DEFAULT NULL COMMENT 'بیوگرافی',
  `timezone` VARCHAR(50) DEFAULT 'Asia/Tehran' COMMENT 'منطقه زمانی',
  `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'آواتار',
  `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'فعال',
  `email_verified_at` DATETIME DEFAULT NULL,
  `remember_token` VARCHAR(64) DEFAULT NULL COMMENT 'توکن Remember Me',
  `remember_expiry` DATETIME DEFAULT NULL COMMENT 'انقضای Remember Me',
  `last_login` DATETIME DEFAULT NULL COMMENT 'آخرین ورود',
  `last_ip` VARCHAR(45) DEFAULT NULL COMMENT 'IP آخرین ورود',
  `last_user_agent` TEXT DEFAULT NULL COMMENT 'User Agent آخرین ورود',
  `login_count` INT UNSIGNED DEFAULT 0 COMMENT 'تعداد ورودها',
  `failed_login_attempts` INT UNSIGNED DEFAULT 0 COMMENT 'تعداد تلاش‌های ناموفق',
  `locked_until` DATETIME DEFAULT NULL COMMENT 'قفل تا',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_remember_token` (`remember_token`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`active`),
  KEY `idx_last_login` (`last_login`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ادمین‌های سیستم';

-- ═══════════════════════════════════════════
-- 3. جدول پیام‌ها (Messages)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'آیدی کاربر',
  `admin_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'آیدی ادمین (اگه ادمین فرستاده)',
  `text` TEXT DEFAULT NULL COMMENT 'متن پیام',
  `direction` ENUM('in','out','note','ai') NOT NULL DEFAULT 'in' COMMENT 'جهت پیام',
  `message_type` ENUM('text','photo','video','document','audio','voice','location','contact','sticker','ai','note') NOT NULL DEFAULT 'text' COMMENT 'نوع پیام',
  `file_id` VARCHAR(255) DEFAULT NULL COMMENT 'File ID تلگرام',
  `file_size` INT UNSIGNED DEFAULT NULL COMMENT 'اندازه فایل',
  `file_name` VARCHAR(255) DEFAULT NULL COMMENT 'نام فایل',
  `mime_type` VARCHAR(100) DEFAULT NULL COMMENT 'MIME type',
  `telegram_message_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Message ID تلگرام',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'خوانده شده',
  `read_at` DATETIME DEFAULT NULL COMMENT 'زمان خوانده شدن',
  `reply_to_message_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'پاسخ به پیام',
  `metadata` JSON DEFAULT NULL COMMENT 'اطلاعات اضافی',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_direction` (`direction`),
  KEY `idx_message_type` (`message_type`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_direction` (`user_id`, `direction`),
  KEY `idx_user_created` (`user_id`, `created_at`),
  FULLTEXT KEY `ft_text` (`text`),
  
  CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='پیام‌های کاربران';

-- ═══════════════════════════════════════════
-- 4. جدول دونیت‌ها (Donations)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `donations`;

CREATE TABLE `donations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'آیدی کاربر',
  `amount` DECIMAL(15,2) NOT NULL COMMENT 'مبلغ (تومان)',
  `currency` VARCHAR(10) NOT NULL DEFAULT 'IRT' COMMENT 'واحد پول',
  `gateway` ENUM('zarinpal','idpay','nextpay','nowpayments','manual','crypto','other') NOT NULL DEFAULT 'manual' COMMENT 'درگاه پرداخت',
  `status` ENUM('pending','success','failed','refunded','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'وضعیت',
  `ref_id` VARCHAR(100) DEFAULT NULL COMMENT 'شناسه مرجع',
  `transaction_id` VARCHAR(100) DEFAULT NULL COMMENT 'شناسه تراکنش',
  `authority` VARCHAR(100) DEFAULT NULL COMMENT 'Authority زرین‌پال',
  `track_id` VARCHAR(100) DEFAULT NULL COMMENT 'Track ID',
  `card_number` VARCHAR(20) DEFAULT NULL COMMENT 'شماره کارت',
  `card_holder` VARCHAR(100) DEFAULT NULL COMMENT 'نام دارنده کارت',
  `description` TEXT DEFAULT NULL COMMENT 'توضیحات',
  `reject_reason` TEXT DEFAULT NULL COMMENT 'دلیل رد',
  `payment_url` VARCHAR(500) DEFAULT NULL COMMENT 'لینک پرداخت',
  `verify_data` JSON DEFAULT NULL COMMENT 'داده‌های تأیید',
  `approved_at` DATETIME DEFAULT NULL COMMENT 'زمان تأیید',
  `rejected_at` DATETIME DEFAULT NULL COMMENT 'زمان رد',
  `paid_at` DATETIME DEFAULT NULL COMMENT 'زمان پرداخت',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_gateway` (`gateway`),
  KEY `idx_ref_id` (`ref_id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_status` (`user_id`, `status`),
  KEY `idx_amount` (`amount`),
  
  CONSTRAINT `fk_donations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='دونیت‌ها و پرداخت‌ها';

-- ═══════════════════════════════════════════
-- 5. جدول کلمات کلیدی (Keywords)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `keywords`;

CREATE TABLE `keywords` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` VARCHAR(200) NOT NULL COMMENT 'کلمه کلیدی',
  `answer` TEXT NOT NULL COMMENT 'پاسخ',
  `answer_type` ENUM('text','photo','video','document','audio','voice','sticker') NOT NULL DEFAULT 'text' COMMENT 'نوع پاسخ',
  `file_id` VARCHAR(255) DEFAULT NULL COMMENT 'File ID تلگرام',
  `priority` INT NOT NULL DEFAULT 0 COMMENT 'اولویت (بالاتر = مهم‌تر)',
  `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'فعال',
  `case_sensitive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'حساس به حروف',
  `exact_match` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'تطابق دقیق',
  `regex_mode` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'حالت Regex',
  `match_count` INT UNSIGNED DEFAULT 0 COMMENT 'تعداد تطابق‌ها',
  `last_matched_at` DATETIME DEFAULT NULL COMMENT 'آخرین تطابق',
  `created_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'ایجاد کننده',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_keyword` (`keyword`),
  KEY `idx_active` (`active`),
  KEY `idx_priority` (`priority`),
  KEY `idx_answer_type` (`answer_type`),
  KEY `idx_match_count` (`match_count`),
  FULLTEXT KEY `ft_keyword_answer` (`keyword`, `answer`),
  
  CONSTRAINT `fk_keywords_creator` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='کلمات کلیدی و پاسخ‌های خودکار';

-- ═══════════════════════════════════════════
-- 6. جدول تطابق‌های کلمات کلیدی
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `keyword_matches`;

CREATE TABLE `keyword_matches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword_id` INT UNSIGNED NOT NULL COMMENT 'آیدی کلمه کلیدی',
  `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'آیدی کاربر',
  `matched_text` TEXT DEFAULT NULL COMMENT 'متن تطابق یافته',
  `matched_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'زمان تطابق',
  
  PRIMARY KEY (`id`),
  KEY `idx_keyword_id` (`keyword_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_matched_at` (`matched_at`),
  KEY `idx_keyword_date` (`keyword_id`, `matched_at`),
  
  CONSTRAINT `fk_matches_keyword` FOREIGN KEY (`keyword_id`) REFERENCES `keywords` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تاریخچه تطابق کلمات کلیدی';

-- ═══════════════════════════════════════════
-- 7. جدول تنظیمات (Settings)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `key_name` VARCHAR(100) NOT NULL COMMENT 'نام کلید',
  `value` TEXT DEFAULT NULL COMMENT 'مقدار',
  `type` ENUM('string','integer','float','boolean','email','url','telegram_token','telegram_id','json','array','color') NOT NULL DEFAULT 'string' COMMENT 'نوع داده',
  `category` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'دسته',
  `description` VARCHAR(255) DEFAULT NULL COMMENT 'توضیحات',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'ترتیب نمایش',
  `is_public` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'عمومی',
  `is_sensitive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'حساس',
  `validation_rules` JSON DEFAULT NULL COMMENT 'قوانین اعتبارسنجی',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`key_name`),
  KEY `idx_category` (`category`),
  KEY `idx_sort_order` (`sort_order`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تنظیمات سیستم';

-- ═══════════════════════════════════════════
-- 8. جدول تاریخچه تغییرات تنظیمات
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `settings_log`;

CREATE TABLE `settings_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `changed_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'تغییر دهنده',
  `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_key_name` (`key_name`),
  KEY `idx_changed_by` (`changed_by`),
  KEY `idx_changed_at` (`changed_at`),
  
  CONSTRAINT `fk_settings_log_admin` FOREIGN KEY (`changed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تاریخچه تغییرات تنظیمات';

-- ═══════════════════════════════════════════
-- 9. جدول Broadcast ها
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `broadcasts`;

CREATE TABLE `broadcasts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'عنوان',
  `content` TEXT NOT NULL COMMENT 'محتوای پیام',
  `content_type` ENUM('text','photo','video','document','audio','voice','sticker') NOT NULL DEFAULT 'text',
  `file_id` VARCHAR(255) DEFAULT NULL,
  `target` VARCHAR(50) NOT NULL COMMENT 'گروه هدف',
  `target_options` JSON DEFAULT NULL COMMENT 'گزینه‌های هدف',
  `target_count` INT UNSIGNED DEFAULT 0 COMMENT 'تعداد هدف',
  `status` ENUM('pending','running','paused','completed','cancelled','scheduled') NOT NULL DEFAULT 'pending',
  `delay` INT UNSIGNED DEFAULT 50 COMMENT 'تأخیر بین پیام (ms)',
  `sent_count` INT UNSIGNED DEFAULT 0,
  `failed_count` INT UNSIGNED DEFAULT 0,
  `blocked_count` INT UNSIGNED DEFAULT 0,
  `current_offset` INT UNSIGNED DEFAULT 0,
  `scheduled_at` DATETIME DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_target` (`target`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_created_by` (`created_by`),
  
  CONSTRAINT `fk_broadcasts_creator` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ارسال‌های دسته‌جمعی';

-- ═══════════════════════════════════════════
-- 10. جدول گیرندگان Broadcast
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `broadcast_recipients`;

CREATE TABLE `broadcast_recipients` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `broadcast_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('pending','success','failed','blocked') NOT NULL DEFAULT 'pending',
  `error` TEXT DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_broadcast_id` (`broadcast_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `uk_broadcast_user` (`broadcast_id`, `user_id`),
  
  CONSTRAINT `fk_recipients_broadcast` FOREIGN KEY (`broadcast_id`) REFERENCES `broadcasts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recipients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='گیرندگان Broadcast';

-- ═══════════════════════════════════════════
-- 11. جدول Session های چت
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `chat_sessions`;

CREATE TABLE `chat_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `admin_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('active','closed','transferred') NOT NULL DEFAULT 'active',
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` DATETIME DEFAULT NULL,
  `transferred_from` BIGINT UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_status` (`status`),
  KEY `idx_started_at` (`started_at`),
  
  CONSTRAINT `fk_chat_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_sessions_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Session های چت زنده';

-- ═══════════════════════════════════════════
-- 12. جدول توکن‌های API
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `api_tokens`;

CREATE TABLE `api_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'نام توکن',
  `token_hash` VARCHAR(64) NOT NULL COMMENT 'SHA256 Hash توکن',
  `permissions` JSON DEFAULT NULL COMMENT 'دسترسی‌ها',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `expires_at` DATETIME DEFAULT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `last_used_ip` VARCHAR(45) DEFAULT NULL,
  `last_used_user_agent` TEXT DEFAULT NULL,
  `usage_count` INT UNSIGNED DEFAULT 0,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token_hash` (`token_hash`),
  KEY `idx_active` (`active`),
  KEY `idx_expires_at` (`expires_at`),
  
  CONSTRAINT `fk_api_tokens_creator` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='توکن‌های API';

-- ═══════════════════════════════════════════
-- 13. جدول لاگ فعالیت‌ها
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `activity_logs`;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'نوع عملیات',
  `description` TEXT DEFAULT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'نوع موجودیت',
  `entity_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'آیدی موجودیت',
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_created_at` (`created_at`),
  
  CONSTRAINT `fk_activity_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='لاگ فعالیت‌های ادمین';

-- ═══════════════════════════════════════════
-- 14. جدول Session های ادمین
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `admin_sessions`;

CREATE TABLE `admin_sessions` (
  `id` VARCHAR(128) NOT NULL,
  `admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `device` VARCHAR(100) DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  `is_current` TINYINT(1) NOT NULL DEFAULT 0,
  
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_last_activity` (`last_activity`),
  
  CONSTRAINT `fk_sessions_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Session های ادمین';

-- ═══════════════════════════════════════════
-- 15. جدول لاگ ورود
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `login_logs`;

CREATE TABLE `login_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('success','failed','locked','rate_limited') NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `failure_reason` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_username` (`username`),
  KEY `idx_status` (`status`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`),
  
  CONSTRAINT `fk_login_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='لاگ ورود ادمین‌ها';

-- ═══════════════════════════════════════════
-- 16. جدول Queue Jobs (برای Background Tasks)
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(100) NOT NULL DEFAULT 'default',
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `reserved_at` INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_queue` (`queue`),
  KEY `idx_reserved_at` (`reserved_at`),
  KEY `idx_available_at` (`available_at`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='صف کارها';

-- ═══════════════════════════════════════════
-- 17. جدول Failed Jobs
-- ═══════════════════════════════════════════

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uuid` (`uuid`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='کارهای ناموفق';

-- ═══════════════════════════════════════════
-- 18. Triggers
-- ═══════════════════════════════════════════

-- Trigger: بروزرسانی آمار کاربر بعد از دونیت موفق
DROP TRIGGER IF EXISTS `trg_donations_after_update`;

CREATE TRIGGER `trg_donations_after_update`
AFTER UPDATE ON `donations`
FOR EACH ROW
BEGIN
    IF OLD.status != 'success' AND NEW.status = 'success' THEN
        UPDATE `users` 
        SET 
            `total_donations` = `total_donations` + NEW.amount,
            `donation_count` = `donation_count` + 1
        WHERE `id` = NEW.user_id;
    END IF;
    
    IF OLD.status = 'success' AND NEW.status != 'success' THEN
        UPDATE `users` 
        SET 
            `total_donations` = GREATEST(0, `total_donations` - OLD.amount),
            `donation_count` = GREATEST(0, `donation_count` - 1)
        WHERE `id` = NEW.user_id;
    END IF;
END;

-- Trigger: ثبت لاگ تغییرات تنظیمات
DROP TRIGGER IF EXISTS `trg_settings_before_update`;

CREATE TRIGGER `trg_settings_before_update`
BEFORE UPDATE ON `settings`
FOR EACH ROW
BEGIN
    IF OLD.value != NEW.value THEN
        INSERT INTO `settings_log` (`key_name`, `old_value`, `new_value`, `changed_at`)
        VALUES (OLD.`key_name`, OLD.value, NEW.value, NOW());
    END IF;
END;

-- Trigger: افزایش match_count بعد از ثبت تطابق
DROP TRIGGER IF EXISTS `trg_keyword_matches_after_insert`;

CREATE TRIGGER `trg_keyword_matches_after_insert`
AFTER INSERT ON `keyword_matches`
FOR EACH ROW
BEGIN
    UPDATE `keywords` 
    SET 
        `match_count` = `match_count` + 1,
        `last_matched_at` = NEW.matched_at
    WHERE `id` = NEW.keyword_id;
END;

-- ═══════════════════════════════════════════
-- 19. Views
-- ═══════════════════════════════════════════

-- View: آمار کلی کاربران
DROP VIEW IF EXISTS `v_user_stats`;

CREATE VIEW `v_user_stats` AS
SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN is_vip = 1 THEN 1 ELSE 0 END) as vip_users,
    SUM(CASE WHEN blocked = 1 THEN 1 ELSE 0 END) as blocked_users,
    SUM(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as active_week,
    SUM(CASE WHEN DATE(joined_at) = CURDATE() THEN 1 ELSE 0 END) as new_today,
    SUM(CASE WHEN joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_month,
    SUM(total_donations) as total_revenue,
    SUM(donation_count) as total_donations_count
FROM `users`;

-- View: آمار روزانه دونیت‌ها
DROP VIEW IF EXISTS `v_daily_donations`;

CREATE VIEW `v_daily_donations` AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as count,
    SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_amount,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    AVG(CASE WHEN status = 'success' THEN amount ELSE NULL END) as average_amount
FROM `donations`
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- View: آمار روزانه پیام‌ها
DROP VIEW IF EXISTS `v_daily_messages`;

CREATE VIEW `v_daily_messages` AS
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN direction = 'in' THEN 1 ELSE 0 END) as incoming,
    SUM(CASE WHEN direction = 'out' THEN 1 ELSE 0 END) as outgoing,
    SUM(CASE WHEN direction = 'ai' THEN 1 ELSE 0 END) as ai_messages,
    COUNT(DISTINCT user_id) as unique_users
FROM `messages`
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- View: برترین کاربران
DROP VIEW IF EXISTS `v_top_users`;

CREATE VIEW `v_top_users` AS
SELECT 
    u.id,
    u.username,
    u.first_name,
    u.last_name,
    u.total_donations,
    u.donation_count,
    u.message_count,
    u.last_seen,
    u.joined_at,
    CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as full_name
FROM `users` u
WHERE u.blocked = 0
ORDER BY u.total_donations DESC;

-- ═══════════════════════════════════════════
-- 20. Stored Procedures
-- ═══════════════════════════════════════════

-- Procedure: پاکسازی لاگ‌های قدیمی
DROP PROCEDURE IF EXISTS `sp_cleanup_old_logs`;

CREATE PROCEDURE `sp_cleanup_old_logs`(IN days_to_keep INT)
BEGIN
    DELETE FROM `settings_log` 
    WHERE `changed_at` < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    DELETE FROM `activity_logs` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    DELETE FROM `login_logs` 
    WHERE `created_at` < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    DELETE FROM `keyword_matches` 
    WHERE `matched_at` < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    DELETE FROM `failed_jobs` 
    WHERE `failed_at` < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
END;

-- Procedure: بروزرسانی آمار کاربران
DROP PROCEDURE IF EXISTS `sp_update_user_stats`;

CREATE PROCEDURE `sp_update_user_stats`()
BEGIN
    UPDATE `users` u
    SET `message_count` = (
        SELECT COUNT(*) 
        FROM `messages` m 
        WHERE m.user_id = u.id AND m.direction = 'in'
    );
    
    UPDATE `users` u
    SET 
        `donation_count` = (
            SELECT COUNT(*) 
            FROM `donations` d 
            WHERE d.user_id = u.id AND d.status = 'success'
        ),
        `total_donations` = (
            SELECT COALESCE(SUM(amount), 0) 
            FROM `donations` d 
            WHERE d.user_id = u.id AND d.status = 'success'
        );
END;

-- Procedure: بررسی و ارتقای خودکار VIP
DROP PROCEDURE IF EXISTS `sp_check_auto_vip`;

CREATE PROCEDURE `sp_check_auto_vip`(IN vip_threshold DECIMAL(15,2))
BEGIN
    UPDATE `users` u
    SET `is_vip` = 1
    WHERE u.is_vip = 0
    AND u.blocked = 0
    AND u.total_donations >= vip_threshold;
END;

-- ═══════════════════════════════════════════
-- 21. داده‌های پیش‌فرض
-- ═══════════════════════════════════════════

-- ادمین پیش‌فرض (رمز: Admin@12345)
INSERT INTO `admins` (`username`, `password_hash`, `name`, `email`, `role`, `active`) VALUES
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/Le9Uyzg0yB.9l/O7G', 'مدیر سیستم', 'admin@example.com', 'super_admin', 1);

-- تنظیمات پیش‌فرض
INSERT INTO `settings` (`key_name`, `value`, `type`, `category`, `description`, `sort_order`) VALUES
-- عمومی
('site_name', 'ربات یوتیوبر', 'string', 'general', 'نام سایت', 1),
('site_url', '', 'url', 'general', 'آدرس سایت', 2),
('timezone', 'Asia/Tehran', 'string', 'general', 'منطقه زمانی', 3),
('maintenance_mode', '0', 'boolean', 'general', 'حالت تعمیرات', 4),

-- تلگرام
('welcome_text', 'سلام {first_name} عزیز! 👋\n\nبه ربات ما خوش اومدی 🎬\n\nاز منوی زیر استفاده کن:', 'string', 'telegram', 'متن خوش‌آمدگویی', 10),
('welcome_photo', '', 'string', 'telegram', 'File ID عکس خوش‌آمدگویی', 11),
('donate_link', '', 'url', 'telegram', 'لینک درگاه حمایت', 12),
('donate_text', '💰 با حمایت مالی از ما، به تولید محتوای بهتر کمک می‌کنید!', 'string', 'telegram', 'متن صفحه حمایت', 13),
('youtube_url', '', 'url', 'telegram', 'لینک کانال یوتیوب', 14),
('telegram_channel', '', 'string', 'telegram', 'آیدی کانال تلگرام', 15),

-- هوش مصنوعی
('ai_enabled', '0', 'boolean', 'ai', 'فعال‌سازی هوش مصنوعی', 20),
('ai_provider', 'openai', 'string', 'ai', 'ارائه‌دهنده AI', 21),
('ai_api_key', '', 'string', 'ai', 'API Key هوش مصنوعی', 22),
('ai_model', 'gpt-4o-mini', 'string', 'ai', 'مدل AI', 23),
('ai_system_prompt', 'تو دستیار یک یوتیوبر فارسی‌زبان هستی. دوستانه و کوتاه جواب بده.', 'string', 'ai', 'System Prompt', 24),
('ai_max_tokens', '500', 'integer', 'ai', 'حداکثر توکن پاسخ', 25),
('ai_temperature', '0.7', 'float', 'ai', 'Temperature', 26),

-- VIP
('vip_threshold', '100000', 'integer', 'vip', 'آستانه VIP (تومان)', 30),
('vip_badge', '👑', 'string', 'vip', 'نشان VIP', 31),
('vip_duration_days', '30', 'integer', 'vip', 'مدت VIP (روز)', 32),

-- امنیتی
('admin_ip_whitelist', '[]', 'array', 'security', 'لیست IP های مجاز', 40),
('login_max_attempts', '5', 'integer', 'security', 'حداکثر تلاش ناموفق', 41),
('login_lockout_minutes', '15', 'integer', 'security', 'مدت قفل (دقیقه)', 42),
('session_timeout', '3600', 'integer', 'security', 'انقضای Session (ثانیه)', 43),
('csrf_enabled', '1', 'boolean', 'security', 'فعال‌سازی CSRF', 44),
('two_factor_enabled', '0', 'boolean', 'security', 'احراز هویت دو مرحله‌ای', 45),

-- عملکرد
('cache_ttl', '3600', 'integer', 'performance', 'TTL کش (ثانیه)', 50),
('broadcast_delay', '50', 'integer', 'performance', 'تأخیر Broadcast (ms)', 51),
('broadcast_batch_size', '30', 'integer', 'performance', 'اندازه Batch', 52),
('max_upload_size', '10485760', 'integer', 'performance', 'حداکثر حجم آپلود (bytes)', 53),

-- اعلان‌ها
('notify_new_user', '1', 'boolean', 'notifications', 'اطلاع‌رسانی کاربر جدید', 60),
('notify_new_donation', '1', 'boolean', 'notifications', 'اطلاع‌رسانی دونیت جدید', 61),
('notify_failed_login', '1', 'boolean', 'notifications', 'اطلاع‌رسانی ورود ناموفق', 62),
('notify_blocked_user', '1', 'boolean', 'notifications', 'اطلاع‌رسانی کاربر بلاک شده', 63);

-- کلمات کلیدی پیش‌فرض
INSERT INTO `keywords` (`keyword`, `answer`, `answer_type`, `priority`, `active`) VALUES
('سلام', 'سلام {first_name} عزیز! 👋\n\nچطور می‌تونم کمکت کنم؟', 'text', 100, 1),
('درود', 'درود بر شما! 🌹\n\nخوش اومدید!', 'text', 99, 1),
('قیمت', 'برای اطلاع از قیمت‌ها به سایت ما مراجعه کنید:\n🌐 example.com', 'text', 90, 1),
('راهنما', '📖 <b>راهنمای استفاده</b>\n\n1️⃣ از منوی اصلی استفاده کنید\n2️⃣ برای حمایت: /donate\n3️⃣ برای تماس: /contact\n\nسوال دیگه‌ای دارید؟', 'text', 80, 1),
('حمایت', '💖 ممنون از توجه شما!\n\nبرای حمایت مالی از دکمه زیر استفاده کنید:', 'text', 70, 1),
('یوتیوب', '🎬 کانال یوتیوب ما:\n\nحتماً سابسکرایب کنید و زنگوله 🔔 رو بزنید!', 'text', 60, 1),
('vip', '👑 <b>باشگاه VIP</b>\n\nبا حمایت مالی، به جمع VIP ها بپیوندید و از مزایای ویژه بهره‌مند شوید!', 'text', 50, 1);

-- ═══════════════════════════════════════════
-- 22. Events (برای Cron Jobs داخلی)
-- ═══════════════════════════════════════════

-- فعال‌سازی Event Scheduler
SET GLOBAL event_scheduler = ON;

-- Event: پاکسازی روزانه لاگ‌های قدیمی (هر روز ساعت 3 صبح)
DROP EVENT IF EXISTS `evt_daily_cleanup`;

CREATE EVENT `evt_daily_cleanup`
ON SCHEDULE EVERY 1 DAY
STARTS CONCAT(CURDATE() + INTERVAL 1 DAY, ' 03:00:00')
ON COMPLETION PRESERVE
ENABLE
DO
BEGIN
    CALL `sp_cleanup_old_logs`(90);
END;

-- Event: بروزرسانی آمار کاربران (هر ساعت)
DROP EVENT IF EXISTS `evt_hourly_stats_update`;

CREATE EVENT `evt_hourly_stats_update`
ON SCHEDULE EVERY 1 HOUR
ON COMPLETION PRESERVE
ENABLE
DO
BEGIN
    CALL `sp_update_user_stats`();
END;

-- Event: بررسی VIP خودکار (هر 6 ساعت)
DROP EVENT IF EXISTS `evt_check_auto_vip`;

CREATE EVENT `evt_check_auto_vip`
ON SCHEDULE EVERY 6 HOUR
ON COMPLETION PRESERVE
ENABLE
DO
BEGIN
    SET @threshold = (SELECT CAST(value AS DECIMAL(15,2)) FROM settings WHERE key_name = 'vip_threshold' LIMIT 1);
    
    IF @threshold IS NOT NULL THEN
        CALL `sp_check_auto_vip`(@threshold);
    END IF;
END;

-- ═══════════════════════════════════════════
-- 23. پایان
-- ═══════════════════════════════════════════

SET FOREIGN_KEY_CHECKS = 1;

-- نمایش خلاصه
SELECT '✅ Database schema created successfully!' as status;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = DATABASE();
SELECT COUNT(*) as total_views FROM information_schema.views WHERE table_schema = DATABASE();
SELECT COUNT(*) as total_triggers FROM information_schema.triggers WHERE trigger_schema = DATABASE();
SELECT COUNT(*) as total_procedures FROM information_schema.routines WHERE routine_schema = DATABASE() AND routine_type = 'PROCEDURE';
SELECT COUNT(*) as total_events FROM information_schema.events WHERE event_schema = DATABASE();