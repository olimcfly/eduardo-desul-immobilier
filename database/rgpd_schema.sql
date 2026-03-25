-- RGPD module schema (multi-tenant via site_id)

CREATE TABLE IF NOT EXISTS rgpd_consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    consent_type VARCHAR(50) NOT NULL,
    categories_json JSON NOT NULL,
    consent_version VARCHAR(30) NOT NULL,
    consented_at DATETIME NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    proof_hash CHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rgpd_consents_site_email (site_id, email),
    INDEX idx_rgpd_consents_site_type_date (site_id, consent_type, consented_at),
    UNIQUE KEY uq_rgpd_consents_proof (proof_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rgpd_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    request_type ENUM('access', 'delete', 'update') NOT NULL,
    status ENUM('new', 'in_progress', 'done', 'rejected') NOT NULL DEFAULT 'new',
    requester_ip VARCHAR(45) NOT NULL,
    payload_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_rgpd_requests_site_status (site_id, status),
    INDEX idx_rgpd_requests_site_email (site_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rgpd_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,
    version VARCHAR(30) NOT NULL,
    html_content MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_rgpd_policies_site_created (site_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rgpd_retention_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id BIGINT UNSIGNED NOT NULL,
    table_name VARCHAR(120) NOT NULL,
    date_column VARCHAR(120) NOT NULL,
    retention_days INT UNSIGNED NOT NULL,
    action ENUM('delete', 'anonymize') NOT NULL,
    anonymize_columns_json JSON DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_rgpd_retention_site_active (site_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
