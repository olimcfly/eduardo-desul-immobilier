CREATE TABLE IF NOT EXISTS keywords (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    keyword VARCHAR(255) NOT NULL UNIQUE,
    search_volume INT UNSIGNED NOT NULL DEFAULT 0,
    competition INT UNSIGNED NOT NULL DEFAULT 0,
    search_intent ENUM('informational','commercial','transactional') NOT NULL DEFAULT 'informational',
    status ENUM('pending','validated','rejected') NOT NULL DEFAULT 'pending',
    position INT NOT NULL DEFAULT 0,
    position_trend INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_keywords_status (status),
    INDEX idx_keywords_competition (competition)
);

CREATE TABLE IF NOT EXISTS silos (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    region VARCHAR(128) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS silo_articles (
    silo_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    position INT NOT NULL,
    PRIMARY KEY (silo_id, article_id),
    INDEX idx_silo_position (silo_id, position)
);

CREATE TABLE IF NOT EXISTS serp_snapshots (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    keyword VARCHAR(255) NOT NULL,
    rank_position INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    meta_description VARCHAR(320) NOT NULL,
    favicon_url VARCHAR(500) NULL,
    captured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_serp_keyword_rank (keyword, rank_position)
);

CREATE TABLE IF NOT EXISTS silo_opportunities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    silo_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    position INT NOT NULL,
    impact TINYINT UNSIGNED NOT NULL DEFAULT 5,
    INDEX idx_silo_opp (silo_id, impact)
);
