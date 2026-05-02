-- ============================================================
-- Migration 032 — Guide local : POI + médias (CRM / CMS)
-- S'appuie sur villes & quartiers (026), sans dupliquer users.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Catégories de points d'intérêt (restaurants, écoles, parcs…)
CREATE TABLE IF NOT EXISTS guide_poi_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    icon VARCHAR(80) NULL COMMENT 'ex: fa-utensils, emoji ou classe CSS',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_guide_poi_cat_slug (slug),
    KEY idx_guide_poi_cat_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- POI : lié à une ville et/ou un quartier (au moins un des deux)
CREATE TABLE IF NOT EXISTS guide_pois (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ville_id BIGINT UNSIGNED NULL,
    quartier_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description LONGTEXT NULL,
    address VARCHAR(255) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    phone VARCHAR(40) NULL,
    website VARCHAR(500) NULL,
    opening_hours TEXT NULL COMMENT 'JSON ou texte libre',
    featured_image VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_guide_poi_slug (slug),
    KEY idx_guide_poi_ville (ville_id),
    KEY idx_guide_poi_quartier (quartier_id),
    KEY idx_guide_poi_category (category_id),
    KEY idx_guide_poi_active (is_active),
    CONSTRAINT fk_guide_poi_ville FOREIGN KEY (ville_id) REFERENCES villes (id) ON DELETE SET NULL,
    CONSTRAINT fk_guide_poi_quartier FOREIGN KEY (quartier_id) REFERENCES quartiers (id) ON DELETE SET NULL,
    CONSTRAINT fk_guide_poi_category FOREIGN KEY (category_id) REFERENCES guide_poi_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Galerie / fichiers liés aux POI
CREATE TABLE IF NOT EXISTS guide_poi_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poi_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(40) NOT NULL DEFAULT 'image',
    alt_text VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_guide_poi_media_poi (poi_id),
    CONSTRAINT fk_guide_poi_media_poi FOREIGN KEY (poi_id) REFERENCES guide_pois (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Optionnel (SEO) : si besoin de meta sur villes/quartiers, exécuter manuellement :
-- ALTER TABLE villes ADD COLUMN meta_title VARCHAR(255) NULL AFTER description;
-- ALTER TABLE villes ADD COLUMN meta_description VARCHAR(500) NULL AFTER meta_title;
-- ALTER TABLE quartiers ADD COLUMN meta_title VARCHAR(255) NULL AFTER description;
-- ALTER TABLE quartiers ADD COLUMN meta_description VARCHAR(500) NULL AFTER meta_title;

-- Catégories par défaut
INSERT IGNORE INTO guide_poi_categories (name, slug, icon, sort_order, is_active) VALUES
('Restaurants & bars', 'restaurants-bars', 'fa-utensils', 10, 1),
('Commerces & marchés', 'commerces', 'fa-store', 20, 1),
('Écoles & éducation', 'ecoles', 'fa-graduation-cap', 30, 1),
('Parcs & nature', 'parcs-nature', 'fa-tree', 40, 1),
('Transports', 'transports', 'fa-bus', 50, 1),
('Santé & services', 'sante', 'fa-hospital', 60, 1),
('Culture & loisirs', 'culture-loisirs', 'fa-museum', 70, 1);
