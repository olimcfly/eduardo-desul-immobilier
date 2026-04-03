-- Superadmin controls: module toggles + page access requests + live presence

CREATE TABLE IF NOT EXISTS module_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(100) NOT NULL,
    enabled_for_users TINYINT(1) DEFAULT 1,
    enabled_for_admins TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_module_name (module_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_page_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    superadmin_id INT NOT NULL,
    user_id INT NOT NULL,
    page_url VARCHAR(255),
    status ENUM('pending', 'allowed', 'denied') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_superadmin_created (superadmin_id, created_at),
    CONSTRAINT fk_apr_superadmin FOREIGN KEY (superadmin_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_apr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_page_presence (
    user_id INT NOT NULL PRIMARY KEY,
    page_url VARCHAR(255) DEFAULT NULL,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_presence_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
