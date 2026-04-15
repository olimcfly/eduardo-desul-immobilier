-- Sprint 1 - Module onboarding

CREATE TABLE IF NOT EXISTS onboarding_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('draft', 'in_progress', 'completed', 'archived') NOT NULL DEFAULT 'draft',
    current_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_onboarding_sessions_user_status (user_id, status),
    INDEX idx_onboarding_sessions_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS onboarding_answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    step_key VARCHAR(50) NOT NULL,
    answers_json JSON NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_onboarding_answers_session_step (session_id, step_key),
    INDEX idx_onboarding_answers_session (session_id),
    CONSTRAINT fk_onboarding_answers_session FOREIGN KEY (session_id)
        REFERENCES onboarding_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS onboarding_blueprints (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    version VARCHAR(20) NOT NULL DEFAULT '1.0',
    blueprint_json JSON NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_onboarding_blueprints_session_version (session_id, version),
    INDEX idx_onboarding_blueprints_session (session_id),
    CONSTRAINT fk_onboarding_blueprints_session FOREIGN KEY (session_id)
        REFERENCES onboarding_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
