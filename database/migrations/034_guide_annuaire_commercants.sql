-- ============================================================
-- Migration 034 — Annuaire commerçants & artisans (extension POI)
-- S'appuie sur 032 (guide_pois) + 026 (villes / quartiers)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Champs supplémentaires fiches (ALTER séparés : évite ambiguïté « AFTER »)
ALTER TABLE guide_pois
    ADD COLUMN postal_code VARCHAR(12) NULL COMMENT 'Code postal' AFTER address;
ALTER TABLE guide_pois
    ADD COLUMN email VARCHAR(255) NULL AFTER website;
ALTER TABLE guide_pois
    ADD COLUMN facebook VARCHAR(500) NULL AFTER email;
ALTER TABLE guide_pois
    ADD COLUMN instagram VARCHAR(500) NULL AFTER facebook;
ALTER TABLE guide_pois
    ADD COLUMN seo_keywords VARCHAR(500) NULL COMMENT 'Mots-clés SEO' AFTER description;
ALTER TABLE guide_pois
    ADD COLUMN rating DECIMAL(3,1) NULL COMMENT 'Note moyenne' AFTER seo_keywords;
ALTER TABLE guide_pois
    ADD COLUMN reviews_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER rating;
ALTER TABLE guide_pois
    ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Validé par l’équipe' AFTER is_active;

-- Avis (optionnel, modération côté admin — à brancher plus tard)
CREATE TABLE IF NOT EXISTS guide_poi_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poi_id BIGINT UNSIGNED NOT NULL,
    author_name VARCHAR(120) NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT '1-5',
    comment TEXT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_poi_approved (poi_id, is_approved),
    CONSTRAINT fk_poi_reviews_poi FOREIGN KEY (poi_id) REFERENCES guide_pois (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catégories type « commerces & artisans » (ne supprime pas les catégories 032 existantes)
INSERT IGNORE INTO guide_poi_categories (name, slug, icon, sort_order, is_active) VALUES
('Boulangerie & patisserie',         'boulangerie',       'fa-bread-slice',  100, 1),
('Restauration',                    'restauration',     'fa-utensils',     101, 1),
('Café / bar',                      'cafe-bar',         'fa-mug-hot',      102, 1),
('Épicerie & alimentation',         'epicerie',         'fa-basket-shopping', 103, 1),
('Mode & accessoires',              'mode',             'fa-shirt',        104, 1),
('Coiffure & beauté',                'coiffure-beaute',  'fa-cut',          105, 1),
('Santé & bien-être',                'sante-bien-etre',  'fa-heart-pulse',  106, 1),
('Artisanat & bâtiment',            'artisanat',        'fa-hammer',      107, 1),
('Services & auto',                'services',         'fa-screwdriver-wrench', 108, 1),
('Loisirs & culture',               'loisirs-culture',  'fa-masks-theater', 109, 1);

SET FOREIGN_KEY_CHECKS = 1;
