-- Queue + suivi d'état de synchronisation Google Business Profile (admin hub)

CREATE TABLE IF NOT EXISTS gmb_sync_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending','running','done','error') NOT NULL DEFAULT 'pending',
    source VARCHAR(50) NOT NULL DEFAULT 'manual',
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    payload JSON DEFAULT NULL,
    result JSON DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    started_at DATETIME DEFAULT NULL,
    finished_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
