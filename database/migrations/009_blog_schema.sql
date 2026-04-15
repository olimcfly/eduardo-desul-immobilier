-- ============================================================
-- Migration 009 : Schéma des tables Blog (silos, articles, keywords)
-- ============================================================

CREATE TABLE IF NOT EXISTS `blog_silos` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `website_id`       INT UNSIGNED NOT NULL,
  `nom`              VARCHAR(255) NOT NULL,
  `persona_id`       INT UNSIGNED DEFAULT NULL,
  `niveau_min`       TINYINT DEFAULT 1,
  `niveau_max`       TINYINT DEFAULT 5,
  `ville`            VARCHAR(100) DEFAULT NULL,
  `statut`           ENUM('actif','archivé') DEFAULT 'actif',
  `pilier_article_id` INT UNSIGNED DEFAULT NULL,
  `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_articles` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `website_id`        INT UNSIGNED NOT NULL,
  `silo_id`           INT UNSIGNED DEFAULT NULL,
  `type`              ENUM('pilier','satellite') DEFAULT 'satellite',
  `titre`             VARCHAR(255) NOT NULL,
  `slug`              VARCHAR(255) NOT NULL,
  `seo_title`         VARCHAR(70)  DEFAULT NULL,
  `meta_desc`         VARCHAR(160) DEFAULT NULL,
  `h1`                VARCHAR(255) DEFAULT NULL,
  `contenu`           LONGTEXT DEFAULT NULL,
  `statut`            ENUM('brouillon','planifié','publié','archivé') DEFAULT 'brouillon',
  `index_status`      ENUM('index','noindex') DEFAULT 'index',
  `score_seo`         TINYINT UNSIGNED DEFAULT 0,
  `mots`              SMALLINT UNSIGNED DEFAULT 0,
  `persona_id`        INT UNSIGNED DEFAULT NULL,
  `niveau_conscience` TINYINT UNSIGNED DEFAULT NULL,
  `date_publication`  DATETIME DEFAULT NULL,
  `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_website_slug` (`website_id`, `slug`),
  KEY `idx_silo` (`silo_id`),
  KEY `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_keywords` (
  `id`                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `website_id`             INT UNSIGNED NOT NULL,
  `article_id`             INT UNSIGNED DEFAULT NULL,
  `mot_cle`                VARCHAR(255) NOT NULL,
  `volume`                 INT UNSIGNED DEFAULT 0,
  `concurrence`            DECIMAL(4,2) DEFAULT 0.00,
  `golden_ratio`           DECIMAL(8,4) DEFAULT NULL,
  `position_serp`          TINYINT UNSIGNED DEFAULT NULL,
  `statut`                 ENUM('en_attente','validé','rejeté') DEFAULT 'en_attente',
  `date_derniere_position` DATE DEFAULT NULL,
  `created_at`             DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_website` (`website_id`),
  KEY `idx_article` (`article_id`),
  KEY `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
