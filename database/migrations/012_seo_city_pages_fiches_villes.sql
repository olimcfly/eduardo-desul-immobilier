-- Migration 012 : module SEO Fiches villes (structure étendue)

CREATE TABLE IF NOT EXISTS seo_city_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    city_name VARCHAR(160) NOT NULL,
    slug VARCHAR(190) NOT NULL,
    status ENUM('draft', 'ready', 'published') NOT NULL DEFAULT 'draft',
    seo_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(170) DEFAULT NULL,
    h1 VARCHAR(190) DEFAULT NULL,
    intro TEXT NULL,
    market_block MEDIUMTEXT NULL,
    faq_json JSON NULL,
    internal_links_json JSON NULL,
    canonical_url VARCHAR(255) DEFAULT NULL,
    seo_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    content_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_city_slug (user_id, slug),
    KEY idx_city_status (user_id, status),
    KEY idx_city_name (user_id, city_name),
    KEY idx_city_score (user_id, seo_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE seo_city_pages
    ADD COLUMN IF NOT EXISTS city_name VARCHAR(160) NULL AFTER user_id,
    ADD COLUMN IF NOT EXISTS intro TEXT NULL AFTER h1,
    ADD COLUMN IF NOT EXISTS market_block MEDIUMTEXT NULL AFTER intro,
    ADD COLUMN IF NOT EXISTS faq_json JSON NULL AFTER market_block,
    ADD COLUMN IF NOT EXISTS internal_links_json JSON NULL AFTER faq_json,
    ADD COLUMN IF NOT EXISTS canonical_url VARCHAR(255) NULL AFTER internal_links_json,
    ADD COLUMN IF NOT EXISTS seo_score TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER canonical_url,
    ADD COLUMN IF NOT EXISTS content_score TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER seo_score;

UPDATE seo_city_pages
SET city_name = COALESCE(NULLIF(city_name, ''), NULLIF(city, ''), '')
WHERE city_name IS NULL OR city_name = '';

ALTER TABLE seo_city_pages
    MODIFY COLUMN city_name VARCHAR(160) NOT NULL,
    MODIFY COLUMN status ENUM('draft', 'ready', 'published') NOT NULL DEFAULT 'draft';

ALTER TABLE seo_city_pages
    ADD INDEX IF NOT EXISTS idx_city_name (user_id, city_name),
    ADD INDEX IF NOT EXISTS idx_city_score (user_id, seo_score);
