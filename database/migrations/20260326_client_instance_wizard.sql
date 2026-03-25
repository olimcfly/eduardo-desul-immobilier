-- Étape 1: fondation wizard + sécurité secrets

ALTER TABLE client_instances
    ADD COLUMN IF NOT EXISTS first_name VARCHAR(120) NULL AFTER id,
    ADD COLUMN IF NOT EXISTS last_name VARCHAR(120) NULL AFTER first_name,
    ADD COLUMN IF NOT EXISTS install_email VARCHAR(190) NULL AFTER last_name,
    ADD COLUMN IF NOT EXISTS instance_slug VARCHAR(190) NULL AFTER business_name,
    ADD COLUMN IF NOT EXISTS smtp_pass_encrypted TEXT NULL AFTER smtp_pass,
    ADD COLUMN IF NOT EXISTS progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER zip_path,
    ADD COLUMN IF NOT EXISTS current_step TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER progress_percent;

CREATE TABLE IF NOT EXISTS client_instance_checks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instance_id INT UNSIGNED NOT NULL,
    check_type ENUM('db','smtp','email','spf','dkim','dmarc') NOT NULL,
    status ENUM('success','warning','error') NOT NULL,
    message VARCHAR(255) NOT NULL,
    details_json JSON NULL,
    checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_instance_check (instance_id, check_type),
    CONSTRAINT fk_client_instance_checks_instance
        FOREIGN KEY (instance_id) REFERENCES client_instances(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS client_instance_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instance_id INT UNSIGNED NOT NULL,
    item_key VARCHAR(80) NOT NULL,
    label VARCHAR(190) NOT NULL,
    is_done TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending','success','warning','error') NOT NULL DEFAULT 'pending',
    last_note VARCHAR(255) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_instance_item (instance_id, item_key),
    CONSTRAINT fk_client_instance_progress_instance
        FOREIGN KEY (instance_id) REFERENCES client_instances(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
