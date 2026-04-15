CREATE TABLE IF NOT EXISTS help_articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(190) NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'general',
    module_key VARCHAR(80) NOT NULL DEFAULT 'general',
    excerpt TEXT NULL,
    content MEDIUMTEXT NOT NULL,
    cta_label VARCHAR(120) NULL,
    cta_url VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_help_articles_slug (slug),
    KEY idx_help_articles_category (category),
    KEY idx_help_articles_module_key (module_key),
    KEY idx_help_articles_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS help_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    help_article_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    module_context VARCHAR(80) NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_help_views_article (help_article_id),
    KEY idx_help_views_user (user_id),
    KEY idx_help_views_context (module_context),
    KEY idx_help_views_viewed_at (viewed_at),
    CONSTRAINT fk_help_views_article FOREIGN KEY (help_article_id) REFERENCES help_articles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
