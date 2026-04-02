-- ============================================================
-- MIGRATION 002 — OTP admin (authentification sans mot de passe)
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_login_otps` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL,
  `email`         VARCHAR(255) NOT NULL,
  `code_hash`     VARCHAR(255) NOT NULL,
  `attempt_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts`  TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `ip_address`    VARCHAR(45) DEFAULT NULL,
  `user_agent`    VARCHAR(255) DEFAULT NULL,
  `expires_at`    DATETIME NOT NULL,
  `consumed_at`   DATETIME DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_created` (`email`, `created_at`),
  KEY `idx_user_created` (`user_id`, `created_at`),
  KEY `idx_ip_created` (`ip_address`, `created_at`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_admin_otp_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
