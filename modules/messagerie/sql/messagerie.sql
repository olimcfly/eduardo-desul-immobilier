-- ============================================================
-- MODULE MESSAGERIE — IMAP + Templates + IA
-- ============================================================

CREATE TABLE IF NOT EXISTS `message_threads` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT UNSIGNED NOT NULL DEFAULT 1,
  `contact_id`    INT UNSIGNED NULL,
  `contact_type`  ENUM('contact','lead','crm') NULL,
  `contact_email` VARCHAR(255) NOT NULL,
  `contact_name`  VARCHAR(255) NOT NULL DEFAULT '',
  `subject`       VARCHAR(500) NOT NULL DEFAULT '',
  `snippet`       TEXT NULL,
  `last_message_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unread_count`  INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_email` (`user_id`, `contact_email`),
  INDEX `idx_user_last` (`user_id`, `last_message_at`),
  INDEX `idx_unread` (`user_id`, `unread_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `thread_id`        INT UNSIGNED NOT NULL,
  `user_id`          INT UNSIGNED NOT NULL DEFAULT 1,
  `gmail_message_id` VARCHAR(255) NULL,
  `direction`        ENUM('inbound','outbound') NOT NULL,
  `from_email`       VARCHAR(255) NOT NULL,
  `from_name`        VARCHAR(255) NOT NULL DEFAULT '',
  `to_email`         VARCHAR(255) NOT NULL,
  `subject`          VARCHAR(500) NOT NULL DEFAULT '',
  `body_html`        LONGTEXT NULL,
  `body_text`        TEXT NULL,
  `status`           ENUM('sent','received','draft','failed') NOT NULL DEFAULT 'received',
  `is_read`          TINYINT(1) NOT NULL DEFAULT 0,
  `sent_at`          DATETIME NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_gmail_msg` (`gmail_message_id`),
  INDEX `idx_thread` (`thread_id`),
  INDEX `idx_user_direction` (`user_id`, `direction`),
  CONSTRAINT `fk_msg_thread` FOREIGN KEY (`thread_id`) REFERENCES `message_threads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_templates` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL DEFAULT 1,
  `name`        VARCHAR(255) NOT NULL,
  `category`    VARCHAR(100) NOT NULL DEFAULT 'general',
  `subject`     VARCHAR(500) NOT NULL DEFAULT '',
  `body_html`   LONGTEXT NOT NULL,
  `usage_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_default`  TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_cat` (`user_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
