-- ============================================================
-- MODULE HUB SEO - Schéma SQL complet
-- ============================================================

-- Mots-clés suivis
CREATE TABLE IF NOT EXISTS seo_keywords (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    keyword       VARCHAR(255) NOT NULL,
    target_url    VARCHAR(500),
    position      INT DEFAULT NULL,
    position_prev INT DEFAULT NULL,
    volume        INT DEFAULT NULL,
    difficulty    TINYINT DEFAULT NULL,
    top10         TINYINT(1) DEFAULT 0,
    last_checked  DATETIME DEFAULT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_keyword (keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique positions
CREATE TABLE IF NOT EXISTS seo_keyword_history (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    keyword_id INT NOT NULL,
    position   INT,
    checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_keyword_id (keyword_id),
    CONSTRAINT fk_seo_history_keyword FOREIGN KEY (keyword_id)
        REFERENCES seo_keywords(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fiches villes
CREATE TABLE IF NOT EXISTS seo_fiches_villes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    ville        VARCHAR(100) NOT NULL,
    slug         VARCHAR(100) NOT NULL,
    code_postal  VARCHAR(10),
    titre_seo    VARCHAR(160),
    meta_desc    VARCHAR(320),
    contenu      LONGTEXT,
    h1           VARCHAR(200),
    prix_m2      DECIMAL(8,2) DEFAULT NULL,
    nb_habitants INT DEFAULT NULL,
    published    TINYINT(1) DEFAULT 0,
    last_updated DATETIME DEFAULT NULL,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_slug (user_id, slug),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sitemap
CREATE TABLE IF NOT EXISTS seo_sitemap_urls (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    url        VARCHAR(500) NOT NULL,
    priority   DECIMAL(2,1) DEFAULT 0.5,
    changefreq ENUM('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT 'weekly',
    lastmod    DATE DEFAULT NULL,
    included   TINYINT(1) DEFAULT 1,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audits performance
CREATE TABLE IF NOT EXISTS seo_audits (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    url_tested  VARCHAR(500),
    score_perf  TINYINT DEFAULT NULL,
    score_seo   TINYINT DEFAULT NULL,
    score_access TINYINT DEFAULT NULL,
    score_bp    TINYINT DEFAULT NULL,
    lcp         DECIMAL(5,2) DEFAULT NULL,
    fid         DECIMAL(5,2) DEFAULT NULL,
    cls         DECIMAL(4,3) DEFAULT NULL,
    ttfb        DECIMAL(5,2) DEFAULT NULL,
    issues      JSON DEFAULT NULL,
    raw_report  LONGTEXT DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
