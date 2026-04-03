-- ============================================================
-- MIGRATION 003 — Système d'API Images
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Configuration des clés API par utilisateur ───────────────
CREATE TABLE IF NOT EXISTS `api_configs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `api_name`    ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `api_key`     VARCHAR(500)  DEFAULT NULL,
  `api_secret`  VARCHAR(500)  DEFAULT NULL,
  `cloud_name`  VARCHAR(100)  DEFAULT NULL,
  `extra`       JSON          DEFAULT NULL,   -- paramètres supplémentaires (preset, folder…)
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_api` (`user_id`, `api_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Solde de crédits par utilisateur et par API ──────────────
CREATE TABLE IF NOT EXISTS `user_credits` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           INT UNSIGNED NOT NULL,
  `api_name`          ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `credits_remaining` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `credits_used`      DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `monthly_limit`     DECIMAL(12,4) NOT NULL DEFAULT 500.0000,
  `reset_day`         TINYINT UNSIGNED NOT NULL DEFAULT 1,  -- jour du mois pour reset
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_api_credits` (`user_id`, `api_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Historique détaillé des consommations ────────────────────
CREATE TABLE IF NOT EXISTS `credit_history` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `api_name`       ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `action_type`    VARCHAR(80)  NOT NULL,   -- 'banner_ancre', 'map_prospection', 'chart_neuropersona', …
  `credits_used`   DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `from_cache`     TINYINT(1)   NOT NULL DEFAULT 0,
  `status`         ENUM('success','failed','blocked') NOT NULL DEFAULT 'success',
  `request_params` JSON         DEFAULT NULL,
  `result_url`     VARCHAR(2083) DEFAULT NULL,
  `error_message`  VARCHAR(500) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_api_date` (`user_id`, `api_name`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Cache des images générées ────────────────────────────────
CREATE TABLE IF NOT EXISTS `image_cache` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cache_key`   CHAR(64)     NOT NULL,   -- SHA-256 hex des paramètres normalisés
  `api_name`    ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `result_url`  VARCHAR(2083) NOT NULL,
  `params_json` JSON         DEFAULT NULL,
  `hit_count`   INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at`  DATETIME     NOT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cache_key` (`cache_key`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Fenêtre glissante de limitation de débit ─────────────────
CREATE TABLE IF NOT EXISTS `api_rate_limits` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED NOT NULL,
  `api_name`     ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `window_start` DATETIME     NOT NULL,
  `req_count`    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_rate_window` (`user_id`, `api_name`, `window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Crédits initiaux pour l'admin existant ───────────────────
INSERT IGNORE INTO `user_credits` (`user_id`, `api_name`, `credits_remaining`, `monthly_limit`)
SELECT id, 'cloudinary',   100.0, 500.0 FROM `users` WHERE role = 'admin';

INSERT IGNORE INTO `user_credits` (`user_id`, `api_name`, `credits_remaining`, `monthly_limit`)
SELECT id, 'google_maps',  200.0, 1000.0 FROM `users` WHERE role = 'admin';

INSERT IGNORE INTO `user_credits` (`user_id`, `api_name`, `credits_remaining`, `monthly_limit`)
SELECT id, 'quickchart',   500.0, 2000.0 FROM `users` WHERE role = 'admin';

SET FOREIGN_KEY_CHECKS = 1;
