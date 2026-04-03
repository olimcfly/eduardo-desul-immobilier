-- ============================================================
-- TABLE SETTINGS — Paramètres conseiller
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL DEFAULT 1,
    `key`       VARCHAR(100) NOT NULL,
    `value`     TEXT         NULL,
    `group`     VARCHAR(50)  NOT NULL DEFAULT 'general',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_key (user_id, `key`),
    INDEX idx_group (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
