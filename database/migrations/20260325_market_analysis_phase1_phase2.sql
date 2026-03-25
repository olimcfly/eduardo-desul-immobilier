-- Module Analyse de Marché (Phase 1 + 2)
-- Compatible avec une base déjà en production.

CREATE TABLE IF NOT EXISTS `market_analyses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `city` VARCHAR(120) NOT NULL,
    `postal_code` VARCHAR(12) DEFAULT NULL,
    `area_name` VARCHAR(120) DEFAULT NULL,
    `target_type` ENUM('vendeur','acheteur','mixte') NOT NULL DEFAULT 'mixte',
    `property_type` VARCHAR(80) DEFAULT NULL,
    `source_provider` VARCHAR(50) DEFAULT NULL,
    `source_prompt` MEDIUMTEXT DEFAULT NULL,
    `raw_response` LONGTEXT DEFAULT NULL,
    `summary` MEDIUMTEXT DEFAULT NULL,
    `market_trends` JSON DEFAULT NULL,
    `pricing_data` JSON DEFAULT NULL,
    `audience_profiles` JSON DEFAULT NULL,
    `faq_data` JSON DEFAULT NULL,
    `seo_opportunities` JSON DEFAULT NULL,
    `business_recommendations` JSON DEFAULT NULL,
    `manual_notes` TEXT DEFAULT NULL,
    `status` ENUM('draft','pending','running','completed','error') NOT NULL DEFAULT 'draft',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_market_analyses_user_status` (`user_id`, `status`),
    INDEX `idx_market_analyses_city` (`city`),
    INDEX `idx_market_analyses_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `market_analyses`
    ADD COLUMN IF NOT EXISTS `postal_code` VARCHAR(12) DEFAULT NULL AFTER `city`,
    ADD COLUMN IF NOT EXISTS `area_name` VARCHAR(120) DEFAULT NULL AFTER `postal_code`,
    ADD COLUMN IF NOT EXISTS `target_type` ENUM('vendeur','acheteur','mixte') NOT NULL DEFAULT 'mixte' AFTER `area_name`,
    ADD COLUMN IF NOT EXISTS `property_type` VARCHAR(80) DEFAULT NULL AFTER `target_type`,
    ADD COLUMN IF NOT EXISTS `source_provider` VARCHAR(50) DEFAULT NULL AFTER `property_type`,
    ADD COLUMN IF NOT EXISTS `source_prompt` MEDIUMTEXT DEFAULT NULL AFTER `source_provider`,
    ADD COLUMN IF NOT EXISTS `raw_response` LONGTEXT DEFAULT NULL AFTER `source_prompt`,
    ADD COLUMN IF NOT EXISTS `summary` MEDIUMTEXT DEFAULT NULL AFTER `raw_response`,
    ADD COLUMN IF NOT EXISTS `market_trends` JSON DEFAULT NULL AFTER `summary`,
    ADD COLUMN IF NOT EXISTS `pricing_data` JSON DEFAULT NULL AFTER `market_trends`,
    ADD COLUMN IF NOT EXISTS `audience_profiles` JSON DEFAULT NULL AFTER `pricing_data`,
    ADD COLUMN IF NOT EXISTS `faq_data` JSON DEFAULT NULL AFTER `audience_profiles`,
    ADD COLUMN IF NOT EXISTS `seo_opportunities` JSON DEFAULT NULL AFTER `faq_data`,
    ADD COLUMN IF NOT EXISTS `business_recommendations` JSON DEFAULT NULL AFTER `seo_opportunities`,
    ADD COLUMN IF NOT EXISTS `manual_notes` TEXT DEFAULT NULL AFTER `business_recommendations`;

CREATE TABLE IF NOT EXISTS `market_analysis_keywords` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `analysis_id` INT UNSIGNED NOT NULL,
    `keyword` VARCHAR(255) NOT NULL,
    `keyword_type` ENUM('short','mid','long') NOT NULL DEFAULT 'mid',
    `intent_type` ENUM('vendeur','acheteur','informationnel','transactionnel','local') NOT NULL DEFAULT 'informationnel',
    `estimated_volume` INT UNSIGNED DEFAULT 0,
    `competition_level` TINYINT UNSIGNED DEFAULT 0,
    `search_results_count` BIGINT UNSIGNED DEFAULT 0,
    `seo_score` DECIMAL(5,2) DEFAULT 0,
    `business_score` DECIMAL(5,2) DEFAULT 0,
    `priority_score` DECIMAL(5,2) DEFAULT 0,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_market_keywords_analysis` (`analysis_id`),
    INDEX `idx_market_keywords_priority` (`priority_score`),
    CONSTRAINT `fk_market_keywords_analysis` FOREIGN KEY (`analysis_id`) REFERENCES `market_analyses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `content_clusters` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `analysis_id` INT UNSIGNED NOT NULL,
    `main_keyword` VARCHAR(255) NOT NULL,
    `cluster_title` VARCHAR(255) NOT NULL,
    `pillar_title` VARCHAR(255) DEFAULT NULL,
    `pillar_keyword` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('draft','generated','exported','archived') NOT NULL DEFAULT 'draft',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_content_clusters_analysis` (`analysis_id`),
    CONSTRAINT `fk_content_clusters_analysis` FOREIGN KEY (`analysis_id`) REFERENCES `market_analyses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `content_cluster_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cluster_id` BIGINT UNSIGNED NOT NULL,
    `item_type` ENUM('pillar','satellite') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `keyword` VARCHAR(255) NOT NULL,
    `intent_type` VARCHAR(50) DEFAULT NULL,
    `angle` TEXT DEFAULT NULL,
    `internal_link_target` VARCHAR(120) DEFAULT NULL,
    `status` ENUM('draft','ready','sent_to_articles','published','error') NOT NULL DEFAULT 'draft',
    `article_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cluster_items_cluster` (`cluster_id`),
    INDEX `idx_cluster_items_article` (`article_id`),
    CONSTRAINT `fk_cluster_items_cluster` FOREIGN KEY (`cluster_id`) REFERENCES `content_clusters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
