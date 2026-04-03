-- =====================================================
-- Module SEO - Schéma SQL
-- =====================================================

CREATE TABLE IF NOT EXISTS seo_keywords (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    keyword VARCHAR(190) NOT NULL,
    target_url VARCHAR(255) NOT NULL,
    current_position INT NULL,
    previous_position INT NULL,
    estimated_volume INT UNSIGNED DEFAULT 0,
    difficulty TINYINT UNSIGNED DEFAULT 0,
    last_checked_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_keyword (user_id, keyword),
    KEY idx_keywords_user (user_id),
    KEY idx_keywords_position (current_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_keyword_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword_id BIGINT UNSIGNED NOT NULL,
    position_value INT NULL,
    checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_history_keyword_date (keyword_id, checked_at),
    CONSTRAINT fk_keyword_history_keyword
        FOREIGN KEY (keyword_id) REFERENCES seo_keywords(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_city_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    city VARCHAR(160) NOT NULL,
    postal_code VARCHAR(12) NOT NULL,
    slug VARCHAR(190) NOT NULL,
    h1 VARCHAR(190) NOT NULL,
    seo_title VARCHAR(60) NOT NULL,
    meta_description VARCHAR(160) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    price_m2 DECIMAL(10,2) NULL,
    population INT UNSIGNED NULL,
    targeted_keywords JSON NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_city_slug (user_id, slug),
    KEY idx_city_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_sitemap_urls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    url VARCHAR(255) NOT NULL,
    priority DECIMAL(2,1) NOT NULL DEFAULT 0.5,
    changefreq ENUM('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
    lastmod DATE NOT NULL,
    included TINYINT(1) NOT NULL DEFAULT 1,
    source_type ENUM('fixed','city','blog','property','custom') NOT NULL DEFAULT 'custom',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_url (user_id, url),
    KEY idx_sitemap_user (user_id, included)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_sitemap_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    urls_count INT UNSIGNED NOT NULL DEFAULT 0,
    ping_status TINYINT(1) NOT NULL DEFAULT 0,
    submitted_to_gsc TINYINT(1) NOT NULL DEFAULT 0,
    xml_size INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_sitemap_logs_user (user_id, generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_performance_audits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    audited_url VARCHAR(255) NOT NULL,
    device ENUM('mobile','desktop') NOT NULL DEFAULT 'mobile',
    perf_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    seo_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    access_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bp_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    lcp_ms INT UNSIGNED NULL,
    inp_ms INT UNSIGNED NULL,
    cls_score DECIMAL(5,3) NULL,
    ttfb_ms INT UNSIGNED NULL,
    raw_payload LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_perf_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    endpoint_key VARCHAR(120) NOT NULL,
    call_count INT UNSIGNED NOT NULL DEFAULT 1,
    window_started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_limit (user_id, endpoint_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
