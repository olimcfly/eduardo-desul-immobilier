-- Migration 027: Système de tracking des conversions
-- Permet de tracker tous les types de conversions sans besoin d'email/contact

CREATE TABLE IF NOT EXISTS conversion_tracking (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversion_type VARCHAR(60) NOT NULL,
    description VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(40) NULL,
    first_name VARCHAR(100) NULL,
    metadata_json JSON NULL,
    source_page VARCHAR(255) NULL,
    user_agent VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    session_id VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_conversion_type (conversion_type),
    KEY idx_created_at (created_at),
    KEY idx_email (email),
    KEY idx_source_page (source_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Types de conversions supportées:
-- - estimation_gratuite_simple (demande estimation sans contact immédiat)
-- - rapport_telechargement (téléchargement de rapport)
-- - rdv_demande (demande de RDV)
-- - contact_formulaire (contact via formulaire)
-- - guide_gratuit_telechargement (guide gratuit téléchargé)
-- - guide_payant_telechargement (guide payant téléchargé - 7 euros)

-- Table pour les statistiques en temps réel (pré-calculées)
CREATE TABLE IF NOT EXISTS conversion_stats_daily (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversion_type VARCHAR(60) NOT NULL,
    date_day DATE NOT NULL,
    total_count INT UNSIGNED DEFAULT 0,
    with_email_count INT UNSIGNED DEFAULT 0,
    with_phone_count INT UNSIGNED DEFAULT 0,

    UNIQUE KEY uniq_type_day (conversion_type, date_day),
    KEY idx_date (date_day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
