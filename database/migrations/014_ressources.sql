-- =============================================
-- Migration 014 : Ressources
-- =============================================

CREATE TABLE IF NOT EXISTS ressources (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL DEFAULT 1,
    status          ENUM('draft','published') DEFAULT 'draft',

    type            ENUM('pdf','checklist','html','mini_guide') DEFAULT 'pdf',
    title           VARCHAR(200) NOT NULL,
    slug            VARCHAR(150) UNIQUE,
    description     TEXT,

    -- Ciblage
    persona         ENUM('acheteur','vendeur','investisseur','primo_accedant','senior') DEFAULT 'vendeur',
    ville           VARCHAR(100),
    awareness_level VARCHAR(50),

    -- Fichier
    file_path       VARCHAR(500),
    file_size       INT UNSIGNED,
    html_content    MEDIUMTEXT,

    -- Livraison email
    delivery_email  TINYINT(1) DEFAULT 1,
    email_subject   VARCHAR(200),
    email_body      TEXT,

    -- Stats
    downloads       INT UNSIGNED DEFAULT 0,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_persona (persona),
    INDEX idx_ville (ville)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
