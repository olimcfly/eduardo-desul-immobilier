-- Migration 026 : Tables des secteurs, villes et quartiers

-- Table des zones
CREATE TABLE IF NOT EXISTS zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(160) NOT NULL UNIQUE,
    slug VARCHAR(190) NOT NULL UNIQUE,
    description TEXT NULL,
    ordre INT DEFAULT 0,
    actif TINYINT DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_slug (slug),
    KEY idx_actif (actif),
    KEY idx_ordre (ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des villes/communes
CREATE TABLE IF NOT EXISTS villes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(160) NOT NULL UNIQUE,
    slug VARCHAR(190) NOT NULL UNIQUE,
    code_postal VARCHAR(5) NULL,
    type ENUM('ville_couverte', 'commune_proche') DEFAULT 'ville_couverte',
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    ordre INT DEFAULT 0,
    actif TINYINT DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_slug (slug),
    KEY idx_type (type),
    KEY idx_actif (actif),
    KEY idx_ordre (ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des quartiers
CREATE TABLE IF NOT EXISTS quartiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(160) NOT NULL UNIQUE,
    slug VARCHAR(190) NOT NULL UNIQUE,
    ville_id BIGINT UNSIGNED,
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    ordre INT DEFAULT 0,
    actif TINYINT DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_slug (slug),
    KEY idx_ville_id (ville_id),
    KEY idx_actif (actif),
    KEY idx_ordre (ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pivot villes_zones (sans contrainte FK pour éviter les problèmes de configuration)
CREATE TABLE IF NOT EXISTS villes_zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ville_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_ville_zone (ville_id, zone_id),
    KEY idx_ville_id (ville_id),
    KEY idx_zone_id (zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
