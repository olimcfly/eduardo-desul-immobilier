-- =====================================================
-- Module SEO - Schéma SQL
-- =====================================================

CREATE TABLE IF NOT EXISTS seo_keywords (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    advisor_id BIGINT UNSIGNED NULL,
    website_id BIGINT UNSIGNED NULL,
    keyword VARCHAR(190) NOT NULL,
    city_name VARCHAR(160) NULL,
    intent ENUM('estimation','vente','achat','quartier','commune','blog') NOT NULL DEFAULT 'estimation',
    target_url VARCHAR(255) NULL,
    status ENUM('active','paused','archived') NOT NULL DEFAULT 'active',
    current_position INT NULL,
    previous_position INT NULL,
    last_checked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_keyword_city (user_id, keyword, city_name),
    KEY idx_keywords_user (user_id),
    KEY idx_keywords_status_intent (status, intent),
    KEY idx_keywords_position (current_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_keyword_positions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword_id BIGINT UNSIGNED NOT NULL,
    position_value INT NULL,
    checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    source VARCHAR(40) NOT NULL DEFAULT 'manual',
    notes VARCHAR(255) NULL,
    KEY idx_keyword_positions_keyword_date (keyword_id, checked_at),
    CONSTRAINT fk_keyword_positions_keyword
        FOREIGN KEY (keyword_id) REFERENCES seo_keywords(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS seo_sitemaps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    sitemap_url VARCHAR(255) NOT NULL,
    total_urls INT UNSIGNED NOT NULL DEFAULT 0,
    last_generated_at DATETIME NULL,
    status ENUM('idle','ok','warning','error') NOT NULL DEFAULT 'idle',
    issues_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_sitemap_user (user_id),
    KEY idx_sitemap_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_sitemap_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sitemap_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action_type ENUM('generate','verify','submit') NOT NULL,
    status ENUM('ok','warning','error') NOT NULL DEFAULT 'ok',
    message TEXT NULL,
    urls_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_sitemap_logs_sitemap (sitemap_id, created_at),
    KEY idx_sitemap_logs_user (user_id, created_at)
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

CREATE TABLE IF NOT EXISTS seo_technical_audits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advisor_id BIGINT UNSIGNED NULL,
    website_id BIGINT UNSIGNED NULL,
    page_url VARCHAR(255) NOT NULL,
    page_type VARCHAR(50) NOT NULL,
    global_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
    lcp DECIMAL(10,2) NULL,
    cls DECIMAL(8,4) NULL,
    inp DECIMAL(10,2) NULL,
    load_time_ms INT UNSIGNED NULL,
    page_weight_kb INT UNSIGNED NULL,
    seo_meta_ok TINYINT(1) NOT NULL DEFAULT 0,
    broken_links_count INT UNSIGNED NOT NULL DEFAULT 0,
    image_issues_count INT UNSIGNED NOT NULL DEFAULT 0,
    audited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audits_advisor_date (advisor_id, audited_at),
    KEY idx_audits_page (page_url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS seo_audit_issues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    audit_id BIGINT UNSIGNED NOT NULL,
    severity ENUM('critical','important','minor') NOT NULL DEFAULT 'minor',
    issue_code VARCHAR(80) NOT NULL,
    issue_label VARCHAR(190) NOT NULL,
    issue_description TEXT NOT NULL,
    recommended_action TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_issues_audit (audit_id),
    KEY idx_issues_severity (severity),
    CONSTRAINT fk_issues_audit
        FOREIGN KEY (audit_id) REFERENCES seo_technical_audits(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
