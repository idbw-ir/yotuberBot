-- ============================================
-- Youtuber Bot - Database Schema (Lite)
-- ============================================
-- Ù†Ø³Ø®Ù‡: 2.1.0 (Ø¨Ø¯ÙˆÙ† Trigger, Event, Procedure)
-- ØªØ§Ø±ÛŒØ®: 2026-07-01
-- 
-- Ø§ÛŒÙ† Ù†Ø³Ø®Ù‡ Ø¨Ø±Ø§ÛŒ Ù‡Ø§Ø³Øªâ€ŒÙ‡Ø§ÛŒÛŒ Ú©Ù‡ Ø¯Ø³ØªØ±Ø³ÛŒ
-- TRIGGER, EVENT, PROCEDURE Ø±Ø§ Ù†Ø¯Ø§Ø±Ù†Ø¯
-- ============================================

-- ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø§ÙˆÙ„ÛŒÙ‡
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 1. Ø¬Ø¯ÙˆÙ„ Ú©Ø§Ø±Ø¨Ø±Ø§Ù† (Users)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ø¹Ø¯Ø¯ÛŒ ØªÙ„Ú¯Ø±Ø§Ù…',
  `username` VARCHAR(100) DEFAULT NULL COMMENT 'ÛŒÙˆØ²Ø±Ù†ÛŒÙ… ØªÙ„Ú¯Ø±Ø§Ù…',
  `first_name` VARCHAR(100) DEFAULT NULL COMMENT 'Ù†Ø§Ù…',
  `last_name` VARCHAR(100) DEFAULT NULL COMMENT 'Ù†Ø§Ù… Ø®Ø§Ù†ÙˆØ§Ø¯Ú¯ÛŒ',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Ø´Ù…Ø§Ø±Ù‡ ØªÙ…Ø§Ø³',
  `bio` TEXT DEFAULT NULL COMMENT 'Ø¨ÛŒÙˆÚ¯Ø±Ø§ÙÛŒ',
  `language_code` VARCHAR(10) DEFAULT 'fa' COMMENT 'Ú©Ø¯ Ø²Ø¨Ø§Ù†',
  `is_vip` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'ÙˆØ¶Ø¹ÛŒØª VIP',
  `vip_expires_at` DATETIME DEFAULT NULL COMMENT 'ØªØ§Ø±ÛŒØ® Ø§Ù†Ù‚Ø¶Ø§ÛŒ VIP',
  `blocked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ø¨Ù„Ø§Ú© Ø´Ø¯Ù‡',
  `notes` TEXT DEFAULT NULL COMMENT 'ÛŒØ§Ø¯Ø¯Ø§Ø´Øª Ø§Ø¯Ù…ÛŒÙ†',
  `total_donations` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Ù…Ø¬Ù…ÙˆØ¹ Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§',
  `donation_count` INT UNSIGNED DEFAULT 0 COMMENT 'ØªØ¹Ø¯Ø§Ø¯ Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§',
  `message_count` INT UNSIGNED DEFAULT 0 COMMENT 'ØªØ¹Ø¯Ø§Ø¯ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§',
  `last_seen` DATETIME DEFAULT NULL COMMENT 'Ø¢Ø®Ø±ÛŒÙ† Ø¨Ø§Ø²Ø¯ÛŒØ¯',
  `last_seen_ip` VARCHAR(45) DEFAULT NULL COMMENT 'IP Ø¢Ø®Ø±ÛŒÙ† Ø¨Ø§Ø²Ø¯ÛŒØ¯',
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'ØªØ§Ø±ÛŒØ® Ø¹Ø¶ÙˆÛŒØª',
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_is_vip` (`is_vip`),
  KEY `idx_blocked` (`blocked`),
  KEY `idx_last_seen` (`last_seen`),
  KEY `idx_joined_at` (`joined_at`),
  KEY `idx_total_donations` (`total_donations`),
  FULLTEXT KEY `ft_search` (`first_name`, `last_name`, `username`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø±Ø¨Ø§Øª ØªÙ„Ú¯Ø±Ø§Ù…';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 2. Ø¬Ø¯ÙˆÙ„ Ø§Ø¯Ù…ÛŒÙ†â€ŒÙ‡Ø§ (Admins)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL COMMENT 'Ù†Ø§Ù… Ú©Ø§Ø±Ø¨Ø±ÛŒ',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Ø±Ù…Ø² Ø¹Ø¨ÙˆØ± Hash Ø´Ø¯Ù‡',
  `name` VARCHAR(100) NOT NULL COMMENT 'Ù†Ø§Ù… Ú©Ø§Ù…Ù„',
  `email` VARCHAR(100) DEFAULT NULL COMMENT 'Ø§ÛŒÙ…ÛŒÙ„',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Ø´Ù…Ø§Ø±Ù‡ ØªÙ…Ø§Ø³',
  `role` ENUM('super_admin','admin','editor','moderator') NOT NULL DEFAULT 'admin' COMMENT 'Ù†Ù‚Ø´',
  `bio` TEXT DEFAULT NULL COMMENT 'Ø¨ÛŒÙˆÚ¯Ø±Ø§ÙÛŒ',
  `timezone` VARCHAR(50) DEFAULT 'Asia/Tehran' COMMENT 'Ù…Ù†Ø·Ù‚Ù‡ Ø²Ù…Ø§Ù†ÛŒ',
  `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Ø¢ÙˆØ§ØªØ§Ø±',
  `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'ÙØ¹Ø§Ù„',
  `email_verified_at` DATETIME DEFAULT NULL,
  `remember_token` VARCHAR(64) DEFAULT NULL COMMENT 'ØªÙˆÚ©Ù† Remember Me',
  `remember_expiry` DATETIME DEFAULT NULL COMMENT 'Ø§Ù†Ù‚Ø¶Ø§ÛŒ Remember Me',
  `last_login` DATETIME DEFAULT NULL COMMENT 'Ø¢Ø®Ø±ÛŒÙ† ÙˆØ±ÙˆØ¯',
  `last_ip` VARCHAR(45) DEFAULT NULL COMMENT 'IP Ø¢Ø®Ø±ÛŒÙ† ÙˆØ±ÙˆØ¯',
  `last_user_agent` TEXT DEFAULT NULL COMMENT 'User Agent Ø¢Ø®Ø±ÛŒÙ† ÙˆØ±ÙˆØ¯',
  `login_count` INT UNSIGNED DEFAULT 0 COMMENT 'ØªØ¹Ø¯Ø§Ø¯ ÙˆØ±ÙˆØ¯Ù‡Ø§',
  `failed_login_attempts` INT UNSIGNED DEFAULT 0 COMMENT 'ØªØ¹Ø¯Ø§Ø¯ ØªÙ„Ø§Ø´â€ŒÙ‡Ø§ÛŒ Ù†Ø§Ù…ÙˆÙÙ‚',
  `locked_until` DATETIME DEFAULT NULL COMMENT 'Ù‚ÙÙ„ ØªØ§',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_remember_token` (`remember_token`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`active`),
  KEY `idx_last_login` (`last_login`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ø§Ø¯Ù…ÛŒÙ†â€ŒÙ‡Ø§ÛŒ Ø³ÛŒØ³ØªÙ…';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 3. Ø¬Ø¯ÙˆÙ„ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ (Messages)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±',
  `admin_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ø§Ø¯Ù…ÛŒÙ† (Ø§Ú¯Ù‡ Ø§Ø¯Ù…ÛŒÙ† ÙØ±Ø³ØªØ§Ø¯Ù‡)',
  `text` TEXT DEFAULT NULL COMMENT 'Ù…ØªÙ† Ù¾ÛŒØ§Ù…',
  `direction` ENUM('in','out','note','ai') NOT NULL DEFAULT 'in' COMMENT 'Ø¬Ù‡Øª Ù¾ÛŒØ§Ù…',
  `message_type` ENUM('text','photo','video','document','audio','voice','location','contact','sticker','ai','note') NOT NULL DEFAULT 'text' COMMENT 'Ù†ÙˆØ¹ Ù¾ÛŒØ§Ù…',
  `file_id` VARCHAR(255) DEFAULT NULL COMMENT 'File ID ØªÙ„Ú¯Ø±Ø§Ù…',
  `file_size` INT UNSIGNED DEFAULT NULL COMMENT 'Ø§Ù†Ø¯Ø§Ø²Ù‡ ÙØ§ÛŒÙ„',
  `file_name` VARCHAR(255) DEFAULT NULL COMMENT 'Ù†Ø§Ù… ÙØ§ÛŒÙ„',
  `mime_type` VARCHAR(100) DEFAULT NULL COMMENT 'MIME type',
  `telegram_message_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Message ID ØªÙ„Ú¯Ø±Ø§Ù…',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ø®ÙˆØ§Ù†Ø¯Ù‡ Ø´Ø¯Ù‡',
  `read_at` DATETIME DEFAULT NULL COMMENT 'Ø²Ù…Ø§Ù† Ø®ÙˆØ§Ù†Ø¯Ù‡ Ø´Ø¯Ù†',
  `reply_to_message_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Ù¾Ø§Ø³Ø® Ø¨Ù‡ Ù¾ÛŒØ§Ù…',
  `metadata` JSON DEFAULT NULL COMMENT 'Ø§Ø·Ù„Ø§Ø¹Ø§Øª Ø§Ø¶Ø§ÙÛŒ',
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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§ÛŒ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 4. Ø¬Ø¯ÙˆÙ„ Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ (Donations)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `donations`;

CREATE TABLE `donations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±',
  `amount` DECIMAL(15,2) NOT NULL COMMENT 'Ù…Ø¨Ù„Øº (ØªÙˆÙ…Ø§Ù†)',
  `currency` VARCHAR(10) NOT NULL DEFAULT 'IRT' COMMENT 'ÙˆØ§Ø­Ø¯ Ù¾ÙˆÙ„',
  `gateway` ENUM('zarinpal','idpay','nextpay','nowpayments','manual','crypto','other') NOT NULL DEFAULT 'manual' COMMENT 'Ø¯Ø±Ú¯Ø§Ù‡ Ù¾Ø±Ø¯Ø§Ø®Øª',
  `status` ENUM('pending','success','failed','refunded','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'ÙˆØ¶Ø¹ÛŒØª',
  `ref_id` VARCHAR(100) DEFAULT NULL COMMENT 'Ø´Ù†Ø§Ø³Ù‡ Ù…Ø±Ø¬Ø¹',
  `transaction_id` VARCHAR(100) DEFAULT NULL COMMENT 'Ø´Ù†Ø§Ø³Ù‡ ØªØ±Ø§Ú©Ù†Ø´',
  `authority` VARCHAR(100) DEFAULT NULL COMMENT 'Authority Ø²Ø±ÛŒÙ†â€ŒÙ¾Ø§Ù„',
  `track_id` VARCHAR(100) DEFAULT NULL COMMENT 'Track ID',
  `card_number` VARCHAR(20) DEFAULT NULL COMMENT 'Ø´Ù…Ø§Ø±Ù‡ Ú©Ø§Ø±Øª',
  `card_holder` VARCHAR(100) DEFAULT NULL COMMENT 'Ù†Ø§Ù… Ø¯Ø§Ø±Ù†Ø¯Ù‡ Ú©Ø§Ø±Øª',
  `description` TEXT DEFAULT NULL COMMENT 'ØªÙˆØ¶ÛŒØ­Ø§Øª',
  `reject_reason` TEXT DEFAULT NULL COMMENT 'Ø¯Ù„ÛŒÙ„ Ø±Ø¯',
  `payment_url` VARCHAR(500) DEFAULT NULL COMMENT 'Ù„ÛŒÙ†Ú© Ù¾Ø±Ø¯Ø§Ø®Øª',
  `verify_data` JSON DEFAULT NULL COMMENT 'Ø¯Ø§Ø¯Ù‡â€ŒÙ‡Ø§ÛŒ ØªØ£ÛŒÛŒØ¯',
  `approved_at` DATETIME DEFAULT NULL COMMENT 'Ø²Ù…Ø§Ù† ØªØ£ÛŒÛŒØ¯',
  `rejected_at` DATETIME DEFAULT NULL COMMENT 'Ø²Ù…Ø§Ù† Ø±Ø¯',
  `paid_at` DATETIME DEFAULT NULL COMMENT 'Ø²Ù…Ø§Ù† Ù¾Ø±Ø¯Ø§Ø®Øª',
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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§ Ùˆ Ù¾Ø±Ø¯Ø§Ø®Øªâ€ŒÙ‡Ø§';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 5. Ø¬Ø¯ÙˆÙ„ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ (Keywords)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `keywords`;

CREATE TABLE `keywords` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` VARCHAR(200) NOT NULL COMMENT 'Ú©Ù„Ù…Ù‡ Ú©Ù„ÛŒØ¯ÛŒ',
  `answer` TEXT NOT NULL COMMENT 'Ù¾Ø§Ø³Ø®',
  `answer_type` ENUM('text','photo','video','document','audio','voice','sticker') NOT NULL DEFAULT 'text' COMMENT 'Ù†ÙˆØ¹ Ù¾Ø§Ø³Ø®',
  `file_id` VARCHAR(255) DEFAULT NULL COMMENT 'File ID ØªÙ„Ú¯Ø±Ø§Ù…',
  `priority` INT NOT NULL DEFAULT 0 COMMENT 'Ø§ÙˆÙ„ÙˆÛŒØª (Ø¨Ø§Ù„Ø§ØªØ± = Ù…Ù‡Ù…â€ŒØªØ±)',
  `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'ÙØ¹Ø§Ù„',
  `case_sensitive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ø­Ø³Ø§Ø³ Ø¨Ù‡ Ø­Ø±ÙˆÙ',
  `exact_match` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'ØªØ·Ø§Ø¨Ù‚ Ø¯Ù‚ÛŒÙ‚',
  `regex_mode` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ø­Ø§Ù„Øª Regex',
  `match_count` INT UNSIGNED DEFAULT 0 COMMENT 'ØªØ¹Ø¯Ø§Ø¯ ØªØ·Ø§Ø¨Ù‚â€ŒÙ‡Ø§',
  `last_matched_at` DATETIME DEFAULT NULL COMMENT 'Ø¢Ø®Ø±ÛŒÙ† ØªØ·Ø§Ø¨Ù‚',
  `created_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Ø§ÛŒØ¬Ø§Ø¯ Ú©Ù†Ù†Ø¯Ù‡',
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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ Ùˆ Ù¾Ø§Ø³Ø®â€ŒÙ‡Ø§ÛŒ Ø®ÙˆØ¯Ú©Ø§Ø±';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 6. Ø¬Ø¯ÙˆÙ„ ØªØ·Ø§Ø¨Ù‚â€ŒÙ‡Ø§ÛŒ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `keyword_matches`;

CREATE TABLE `keyword_matches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword_id` INT UNSIGNED NOT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ú©Ù„Ù…Ù‡ Ú©Ù„ÛŒØ¯ÛŒ',
  `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ø±Ø¨Ø±',
  `matched_text` TEXT DEFAULT NULL COMMENT 'Ù…ØªÙ† ØªØ·Ø§Ø¨Ù‚ ÛŒØ§ÙØªÙ‡',
  `matched_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ø²Ù…Ø§Ù† ØªØ·Ø§Ø¨Ù‚',
  
  PRIMARY KEY (`id`),
  KEY `idx_keyword_id` (`keyword_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_matched_at` (`matched_at`),
  KEY `idx_keyword_date` (`keyword_id`, `matched_at`),
  
  CONSTRAINT `fk_matches_keyword` FOREIGN KEY (`keyword_id`) REFERENCES `keywords` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ØªØ§Ø±ÛŒØ®Ú†Ù‡ ØªØ·Ø§Ø¨Ù‚ Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 7. Ø¬Ø¯ÙˆÙ„ ØªÙ†Ø¸ÛŒÙ…Ø§Øª (Settings)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `key_name` VARCHAR(100) NOT NULL COMMENT 'Ù†Ø§Ù… Ú©Ù„ÛŒØ¯',
  `value` TEXT DEFAULT NULL COMMENT 'Ù…Ù‚Ø¯Ø§Ø±',
  `type` ENUM('string','integer','float','boolean','email','url','telegram_token','telegram_id','json','array','color') NOT NULL DEFAULT 'string' COMMENT 'Ù†ÙˆØ¹ Ø¯Ø§Ø¯Ù‡',
  `category` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'Ø¯Ø³ØªÙ‡',
  `description` VARCHAR(255) DEFAULT NULL COMMENT 'ØªÙˆØ¶ÛŒØ­Ø§Øª',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'ØªØ±ØªÛŒØ¨ Ù†Ù…Ø§ÛŒØ´',
  `is_public` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ø¹Ù…ÙˆÙ…ÛŒ',
  `is_sensitive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ø­Ø³Ø§Ø³',
  `validation_rules` JSON DEFAULT NULL COMMENT 'Ù‚ÙˆØ§Ù†ÛŒÙ† Ø§Ø¹ØªØ¨Ø§Ø±Ø³Ù†Ø¬ÛŒ',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`key_name`),
  KEY `idx_category` (`category`),
  KEY `idx_sort_order` (`sort_order`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ø³ÛŒØ³ØªÙ…';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 8. Ø¬Ø¯ÙˆÙ„ ØªØ§Ø±ÛŒØ®Ú†Ù‡ ØªØºÛŒÛŒØ±Ø§Øª ØªÙ†Ø¸ÛŒÙ…Ø§Øª
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `settings_log`;

CREATE TABLE `settings_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `changed_by` BIGINT UNSIGNED DEFAULT NULL COMMENT 'ØªØºÛŒÛŒØ± Ø¯Ù‡Ù†Ø¯Ù‡',
  `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_key_name` (`key_name`),
  KEY `idx_changed_by` (`changed_by`),
  KEY `idx_changed_at` (`changed_at`),
  
  CONSTRAINT `fk_settings_log_admin` FOREIGN KEY (`changed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ØªØ§Ø±ÛŒØ®Ú†Ù‡ ØªØºÛŒÛŒØ±Ø§Øª ØªÙ†Ø¸ÛŒÙ…Ø§Øª';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 9. Ø¬Ø¯ÙˆÙ„ Broadcast Ù‡Ø§
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `broadcasts`;

CREATE TABLE `broadcasts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'Ø¹Ù†ÙˆØ§Ù†',
  `content` TEXT NOT NULL COMMENT 'Ù…Ø­ØªÙˆØ§ÛŒ Ù¾ÛŒØ§Ù…',
  `content_type` ENUM('text','photo','video','document','audio','voice','sticker') NOT NULL DEFAULT 'text',
  `file_id` VARCHAR(255) DEFAULT NULL,
  `target` VARCHAR(50) NOT NULL COMMENT 'Ú¯Ø±ÙˆÙ‡ Ù‡Ø¯Ù',
  `target_options` JSON DEFAULT NULL COMMENT 'Ú¯Ø²ÛŒÙ†Ù‡â€ŒÙ‡Ø§ÛŒ Ù‡Ø¯Ù',
  `target_count` INT UNSIGNED DEFAULT 0 COMMENT 'ØªØ¹Ø¯Ø§Ø¯ Ù‡Ø¯Ù',
  `status` ENUM('pending','running','paused','completed','cancelled','scheduled') NOT NULL DEFAULT 'pending',
  `delay` INT UNSIGNED DEFAULT 50 COMMENT 'ØªØ£Ø®ÛŒØ± Ø¨ÛŒÙ† Ù¾ÛŒØ§Ù… (ms)',
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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ø§Ø±Ø³Ø§Ù„â€ŒÙ‡Ø§ÛŒ Ø¯Ø³ØªÙ‡â€ŒØ¬Ù…Ø¹ÛŒ';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 10. Ø¬Ø¯ÙˆÙ„ Ú¯ÛŒØ±Ù†Ø¯Ú¯Ø§Ù† Broadcast
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ú¯ÛŒØ±Ù†Ø¯Ú¯Ø§Ù† Broadcast';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 11. Ø¬Ø¯ÙˆÙ„ Session Ù‡Ø§ÛŒ Ú†Øª
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Session Ù‡Ø§ÛŒ Ú†Øª Ø²Ù†Ø¯Ù‡';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 12. Ø¬Ø¯ÙˆÙ„ ØªÙˆÚ©Ù†â€ŒÙ‡Ø§ÛŒ API
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `api_tokens`;

CREATE TABLE `api_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'Ù†Ø§Ù… ØªÙˆÚ©Ù†',
  `token_hash` VARCHAR(64) NOT NULL COMMENT 'SHA256 Hash ØªÙˆÚ©Ù†',
  `permissions` JSON DEFAULT NULL COMMENT 'Ø¯Ø³ØªØ±Ø³ÛŒâ€ŒÙ‡Ø§',
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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ØªÙˆÚ©Ù†â€ŒÙ‡Ø§ÛŒ API';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 13. Ø¬Ø¯ÙˆÙ„ Ù„Ø§Ú¯ ÙØ¹Ø§Ù„ÛŒØªâ€ŒÙ‡Ø§
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

DROP TABLE IF EXISTS `activity_logs`;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'Ù†ÙˆØ¹ Ø¹Ù…Ù„ÛŒØ§Øª',
  `description` TEXT DEFAULT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'Ù†ÙˆØ¹ Ù…ÙˆØ¬ÙˆØ¯ÛŒØª',
  `entity_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Ø¢ÛŒØ¯ÛŒ Ù…ÙˆØ¬ÙˆØ¯ÛŒØª',
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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ù„Ø§Ú¯ ÙØ¹Ø§Ù„ÛŒØªâ€ŒÙ‡Ø§ÛŒ Ø§Ø¯Ù…ÛŒÙ†';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 14. Ø¬Ø¯ÙˆÙ„ Session Ù‡Ø§ÛŒ Ø§Ø¯Ù…ÛŒÙ†
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Session Ù‡Ø§ÛŒ Ø§Ø¯Ù…ÛŒÙ†';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 15. Ø¬Ø¯ÙˆÙ„ Ù„Ø§Ú¯ ÙˆØ±ÙˆØ¯
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ù„Ø§Ú¯ ÙˆØ±ÙˆØ¯ Ø§Ø¯Ù…ÛŒÙ†â€ŒÙ‡Ø§';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 16. Ø¬Ø¯ÙˆÙ„ Queue Jobs (Ø¨Ø±Ø§ÛŒ Background Tasks)
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ØµÙ Ú©Ø§Ø±Ù‡Ø§';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 17. Ø¬Ø¯ÙˆÙ„ Failed Jobs
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ú©Ø§Ø±Ù‡Ø§ÛŒ Ù†Ø§Ù…ÙˆÙÙ‚';

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 19. Views
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

-- View: Ø¢Ù…Ø§Ø± Ú©Ù„ÛŒ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
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

-- View: Ø¢Ù…Ø§Ø± Ø±ÙˆØ²Ø§Ù†Ù‡ Ø¯ÙˆÙ†ÛŒØªâ€ŒÙ‡Ø§
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

-- View: Ø¢Ù…Ø§Ø± Ø±ÙˆØ²Ø§Ù†Ù‡ Ù¾ÛŒØ§Ù…â€ŒÙ‡Ø§
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

-- View: Ø¨Ø±ØªØ±ÛŒÙ† Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
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

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 21. Ø¯Ø§Ø¯Ù‡â€ŒÙ‡Ø§ÛŒ Ù¾ÛŒØ´â€ŒÙØ±Ø¶
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

-- Ø§Ø¯Ù…ÛŒÙ† Ù¾ÛŒØ´â€ŒÙØ±Ø¶ (Ø±Ù…Ø²: Admin@12345)
INSERT INTO `admins` (`username`, `password_hash`, `name`, `email`, `role`, `active`) VALUES
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/Le9Uyzg0yB.9l/O7G', 'Ù…Ø¯ÛŒØ± Ø³ÛŒØ³ØªÙ…', 'admin@example.com', 'super_admin', 1);

-- ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
INSERT INTO `settings` (`key_name`, `value`, `type`, `category`, `description`, `sort_order`) VALUES
-- Ø¹Ù…ÙˆÙ…ÛŒ
('site_name', 'Ø±Ø¨Ø§Øª ÛŒÙˆØªÛŒÙˆØ¨Ø±', 'string', 'general', 'Ù†Ø§Ù… Ø³Ø§ÛŒØª', 1),
('site_url', '', 'url', 'general', 'Ø¢Ø¯Ø±Ø³ Ø³Ø§ÛŒØª', 2),
('timezone', 'Asia/Tehran', 'string', 'general', 'Ù…Ù†Ø·Ù‚Ù‡ Ø²Ù…Ø§Ù†ÛŒ', 3),
('maintenance_mode', '0', 'boolean', 'general', 'Ø­Ø§Ù„Øª ØªØ¹Ù…ÛŒØ±Ø§Øª', 4),

-- ØªÙ„Ú¯Ø±Ø§Ù…
('welcome_text', 'Ø³Ù„Ø§Ù… {first_name} Ø¹Ø²ÛŒØ²! ðŸ‘‹\n\nØ¨Ù‡ Ø±Ø¨Ø§Øª Ù…Ø§ Ø®ÙˆØ´ Ø§ÙˆÙ…Ø¯ÛŒ ðŸŽ¬\n\nØ§Ø² Ù…Ù†ÙˆÛŒ Ø²ÛŒØ± Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†:', 'string', 'telegram', 'Ù…ØªÙ† Ø®ÙˆØ´â€ŒØ¢Ù…Ø¯Ú¯ÙˆÛŒÛŒ', 10),
('welcome_photo', '', 'string', 'telegram', 'File ID Ø¹Ú©Ø³ Ø®ÙˆØ´â€ŒØ¢Ù…Ø¯Ú¯ÙˆÛŒÛŒ', 11),
('donate_link', '', 'url', 'telegram', 'Ù„ÛŒÙ†Ú© Ø¯Ø±Ú¯Ø§Ù‡ Ø­Ù…Ø§ÛŒØª', 12),
('donate_text', 'ðŸ’° Ø¨Ø§ Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒ Ø§Ø² Ù…Ø§ØŒ Ø¨Ù‡ ØªÙˆÙ„ÛŒØ¯ Ù…Ø­ØªÙˆØ§ÛŒ Ø¨Ù‡ØªØ± Ú©Ù…Ú© Ù…ÛŒâ€ŒÚ©Ù†ÛŒØ¯!', 'string', 'telegram', 'Ù…ØªÙ† ØµÙØ­Ù‡ Ø­Ù…Ø§ÛŒØª', 13),
('youtube_url', '', 'url', 'telegram', 'Ù„ÛŒÙ†Ú© Ú©Ø§Ù†Ø§Ù„ ÛŒÙˆØªÛŒÙˆØ¨', 14),
('telegram_channel', '', 'string', 'telegram', 'Ø¢ÛŒØ¯ÛŒ Ú©Ø§Ù†Ø§Ù„ ØªÙ„Ú¯Ø±Ø§Ù…', 15),

-- Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ
('ai_enabled', '0', 'boolean', 'ai', 'ÙØ¹Ø§Ù„â€ŒØ³Ø§Ø²ÛŒ Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ', 20),
('ai_provider', 'openai', 'string', 'ai', 'Ø§Ø±Ø§Ø¦Ù‡â€ŒØ¯Ù‡Ù†Ø¯Ù‡ AI', 21),
('ai_api_key', '', 'string', 'ai', 'API Key Ù‡ÙˆØ´ Ù…ØµÙ†ÙˆØ¹ÛŒ', 22),
('ai_model', 'gpt-4o-mini', 'string', 'ai', 'Ù…Ø¯Ù„ AI', 23),
('ai_system_prompt', 'ØªÙˆ Ø¯Ø³ØªÛŒØ§Ø± ÛŒÚ© ÛŒÙˆØªÛŒÙˆØ¨Ø± ÙØ§Ø±Ø³ÛŒâ€ŒØ²Ø¨Ø§Ù† Ù‡Ø³ØªÛŒ. Ø¯ÙˆØ³ØªØ§Ù†Ù‡ Ùˆ Ú©ÙˆØªØ§Ù‡ Ø¬ÙˆØ§Ø¨ Ø¨Ø¯Ù‡.', 'string', 'ai', 'System Prompt', 24),
('ai_max_tokens', '500', 'integer', 'ai', 'Ø­Ø¯Ø§Ú©Ø«Ø± ØªÙˆÚ©Ù† Ù¾Ø§Ø³Ø®', 25),
('ai_temperature', '0.7', 'float', 'ai', 'Temperature', 26),

-- VIP
('vip_threshold', '100000', 'integer', 'vip', 'Ø¢Ø³ØªØ§Ù†Ù‡ VIP (ØªÙˆÙ…Ø§Ù†)', 30),
('vip_badge', 'ðŸ‘‘', 'string', 'vip', 'Ù†Ø´Ø§Ù† VIP', 31),
('vip_duration_days', '30', 'integer', 'vip', 'Ù…Ø¯Øª VIP (Ø±ÙˆØ²)', 32),

-- Ø§Ù…Ù†ÛŒØªÛŒ
('admin_ip_whitelist', '[]', 'array', 'security', 'Ù„ÛŒØ³Øª IP Ù‡Ø§ÛŒ Ù…Ø¬Ø§Ø²', 40),
('login_max_attempts', '5', 'integer', 'security', 'Ø­Ø¯Ø§Ú©Ø«Ø± ØªÙ„Ø§Ø´ Ù†Ø§Ù…ÙˆÙÙ‚', 41),
('login_lockout_minutes', '15', 'integer', 'security', 'Ù…Ø¯Øª Ù‚ÙÙ„ (Ø¯Ù‚ÛŒÙ‚Ù‡)', 42),
('session_timeout', '3600', 'integer', 'security', 'Ø§Ù†Ù‚Ø¶Ø§ÛŒ Session (Ø«Ø§Ù†ÛŒÙ‡)', 43),
('csrf_enabled', '1', 'boolean', 'security', 'ÙØ¹Ø§Ù„â€ŒØ³Ø§Ø²ÛŒ CSRF', 44),
('two_factor_enabled', '0', 'boolean', 'security', 'Ø§Ø­Ø±Ø§Ø² Ù‡ÙˆÛŒØª Ø¯Ùˆ Ù…Ø±Ø­Ù„Ù‡â€ŒØ§ÛŒ', 45),

-- Ø¹Ù…Ù„Ú©Ø±Ø¯
('cache_ttl', '3600', 'integer', 'performance', 'TTL Ú©Ø´ (Ø«Ø§Ù†ÛŒÙ‡)', 50),
('broadcast_delay', '50', 'integer', 'performance', 'ØªØ£Ø®ÛŒØ± Broadcast (ms)', 51),
('broadcast_batch_size', '30', 'integer', 'performance', 'Ø§Ù†Ø¯Ø§Ø²Ù‡ Batch', 52),
('max_upload_size', '10485760', 'integer', 'performance', 'Ø­Ø¯Ø§Ú©Ø«Ø± Ø­Ø¬Ù… Ø¢Ù¾Ù„ÙˆØ¯ (bytes)', 53),

-- Ø§Ø¹Ù„Ø§Ù†â€ŒÙ‡Ø§
('notify_new_user', '1', 'boolean', 'notifications', 'Ø§Ø·Ù„Ø§Ø¹â€ŒØ±Ø³Ø§Ù†ÛŒ Ú©Ø§Ø±Ø¨Ø± Ø¬Ø¯ÛŒØ¯', 60),
('notify_new_donation', '1', 'boolean', 'notifications', 'Ø§Ø·Ù„Ø§Ø¹â€ŒØ±Ø³Ø§Ù†ÛŒ Ø¯ÙˆÙ†ÛŒØª Ø¬Ø¯ÛŒØ¯', 61),
('notify_failed_login', '1', 'boolean', 'notifications', 'Ø§Ø·Ù„Ø§Ø¹â€ŒØ±Ø³Ø§Ù†ÛŒ ÙˆØ±ÙˆØ¯ Ù†Ø§Ù…ÙˆÙÙ‚', 62),
('notify_blocked_user', '1', 'boolean', 'notifications', 'Ø§Ø·Ù„Ø§Ø¹â€ŒØ±Ø³Ø§Ù†ÛŒ Ú©Ø§Ø±Ø¨Ø± Ø¨Ù„Ø§Ú© Ø´Ø¯Ù‡', 63);

-- Ú©Ù„Ù…Ø§Øª Ú©Ù„ÛŒØ¯ÛŒ Ù¾ÛŒØ´â€ŒÙØ±Ø¶
INSERT INTO `keywords` (`keyword`, `answer`, `answer_type`, `priority`, `active`) VALUES
('Ø³Ù„Ø§Ù…', 'Ø³Ù„Ø§Ù… {first_name} Ø¹Ø²ÛŒØ²! ðŸ‘‹\n\nÚ†Ø·ÙˆØ± Ù…ÛŒâ€ŒØªÙˆÙ†Ù… Ú©Ù…Ú©Øª Ú©Ù†Ù…ØŸ', 'text', 100, 1),
('Ø¯Ø±ÙˆØ¯', 'Ø¯Ø±ÙˆØ¯ Ø¨Ø± Ø´Ù…Ø§! ðŸŒ¹\n\nØ®ÙˆØ´ Ø§ÙˆÙ…Ø¯ÛŒØ¯!', 'text', 99, 1),
('Ù‚ÛŒÙ…Øª', 'Ø¨Ø±Ø§ÛŒ Ø§Ø·Ù„Ø§Ø¹ Ø§Ø² Ù‚ÛŒÙ…Øªâ€ŒÙ‡Ø§ Ø¨Ù‡ Ø³Ø§ÛŒØª Ù…Ø§ Ù…Ø±Ø§Ø¬Ø¹Ù‡ Ú©Ù†ÛŒØ¯:\nðŸŒ example.com', 'text', 90, 1),
('Ø±Ø§Ù‡Ù†Ù…Ø§', 'ðŸ“– <b>Ø±Ø§Ù‡Ù†Ù…Ø§ÛŒ Ø§Ø³ØªÙØ§Ø¯Ù‡</b>\n\n1ï¸âƒ£ Ø§Ø² Ù…Ù†ÙˆÛŒ Ø§ØµÙ„ÛŒ Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯\n2ï¸âƒ£ Ø¨Ø±Ø§ÛŒ Ø­Ù…Ø§ÛŒØª: /donate\n3ï¸âƒ£ Ø¨Ø±Ø§ÛŒ ØªÙ…Ø§Ø³: /contact\n\nØ³ÙˆØ§Ù„ Ø¯ÛŒÚ¯Ù‡â€ŒØ§ÛŒ Ø¯Ø§Ø±ÛŒØ¯ØŸ', 'text', 80, 1),
('Ø­Ù…Ø§ÛŒØª', 'ðŸ’– Ù…Ù…Ù†ÙˆÙ† Ø§Ø² ØªÙˆØ¬Ù‡ Ø´Ù…Ø§!\n\nØ¨Ø±Ø§ÛŒ Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒ Ø§Ø² Ø¯Ú©Ù…Ù‡ Ø²ÛŒØ± Ø§Ø³ØªÙØ§Ø¯Ù‡ Ú©Ù†ÛŒØ¯:', 'text', 70, 1),
('ÛŒÙˆØªÛŒÙˆØ¨', 'ðŸŽ¬ Ú©Ø§Ù†Ø§Ù„ ÛŒÙˆØªÛŒÙˆØ¨ Ù…Ø§:\n\nØ­ØªÙ…Ø§Ù‹ Ø³Ø§Ø¨Ø³Ú©Ø±Ø§ÛŒØ¨ Ú©Ù†ÛŒØ¯ Ùˆ Ø²Ù†Ú¯ÙˆÙ„Ù‡ ðŸ”” Ø±Ùˆ Ø¨Ø²Ù†ÛŒØ¯!', 'text', 60, 1),
('vip', 'ðŸ‘‘ <b>Ø¨Ø§Ø´Ú¯Ø§Ù‡ VIP</b>\n\nØ¨Ø§ Ø­Ù…Ø§ÛŒØª Ù…Ø§Ù„ÛŒØŒ Ø¨Ù‡ Ø¬Ù…Ø¹ VIP Ù‡Ø§ Ø¨Ù¾ÛŒÙˆÙ†Ø¯ÛŒØ¯ Ùˆ Ø§Ø² Ù…Ø²Ø§ÛŒØ§ÛŒ ÙˆÛŒÚ˜Ù‡ Ø¨Ù‡Ø±Ù‡â€ŒÙ…Ù†Ø¯ Ø´ÙˆÛŒØ¯!', 'text', 50, 1);

-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
-- 23. Ù¾Ø§ÛŒØ§Ù†
-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

SET FOREIGN_KEY_CHECKS = 1;
