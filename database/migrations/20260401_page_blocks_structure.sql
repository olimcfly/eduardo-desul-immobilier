-- ============================================================
-- MIGRATION: Page Blocks Structure pour CMS avec Templates Fixes
-- Date: 2026-04-01
-- Description: Crée la structure pour gérer blocs éditables par page
-- ============================================================

-- 1. Table page_blocks - Stocke les valeurs des blocs (modifiables par client)
CREATE TABLE IF NOT EXISTS `page_blocks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT UNSIGNED NOT NULL,
  `block_key` VARCHAR(100) NOT NULL,          -- Ex: "hero", "services", "cta", "testimonials"
  `block_type` VARCHAR(50) NOT NULL,          -- Ex: "hero", "text", "image", "features", "form"
  `block_data` JSON NOT NULL DEFAULT '{}',    -- {"title": "...", "subtitle": "...", "image": "...", etc}
  `block_order` INT DEFAULT 0,                -- Ordre du bloc dans la page (optionnel, défini par template)
  `is_visible` TINYINT(1) DEFAULT 1,          -- Client peut masquer blocs
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY `uk_page_block_key` (`page_id`, `block_key`),
  KEY `idx_page_id` (`page_id`),
  KEY `idx_block_type` (`block_type`),
  FOREIGN KEY `fk_page_blocks_page` (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Vérifier/ajouter colonne `template` à `pages` si absent
ALTER TABLE `pages`
  ADD COLUMN IF NOT EXISTS `template` VARCHAR(100) DEFAULT 'default' AFTER `slug`;

-- 3. Ajouter index sur template
ALTER TABLE `pages`
  ADD KEY IF NOT EXISTS `idx_template` (`template`);

-- 4. Ajouter colonnes pour support multi-website (si structure multi-domaines adoptée plus tard)
ALTER TABLE `pages`
  ADD COLUMN IF NOT EXISTS `website_id` INT UNSIGNED DEFAULT NULL AFTER `id`,
  ADD KEY IF NOT EXISTS `idx_website_id` (`website_id`);

-- 5. Table template_definitions (optionnel, pour documentation DB)
-- Cette table est OPTIONNELLE - les templates sont définis en PHP/config
CREATE TABLE IF NOT EXISTS `template_definitions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `template_key` VARCHAR(100) NOT NULL UNIQUE,
  `template_name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `thumbnail_url` VARCHAR(500),
  `blocks_json` JSON NOT NULL,                 -- Définition des blocs {"blocks": [{"key": "hero", "type": "hero", ...}]}
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Données initiales - Templates disponibles (pour référence)
INSERT INTO `template_definitions`
  (`template_key`, `template_name`, `description`, `blocks_json`, `is_active`)
VALUES
  ('home', 'Accueil', 'Page d\'accueil avec hero, services, CTA et témoignages',
   '{
     "blocks": [
       {"key": "hero", "type": "hero", "label": "Hero principal"},
       {"key": "services", "type": "features", "label": "Services"},
       {"key": "cta", "type": "cta", "label": "Appel à l\'action"},
       {"key": "testimonials", "type": "testimonials", "label": "Témoignages"}
     ]
   }', 1),

  ('acheter', 'Acheter', 'Page pour acheter un bien avec filtres et propriétés',
   '{
     "blocks": [
       {"key": "hero", "type": "hero", "label": "Hero principal"},
       {"key": "filters", "type": "filters", "label": "Filtres de recherche"},
       {"key": "properties", "type": "properties", "label": "Propriétés"},
       {"key": "cta", "type": "cta", "label": "Appel à l\'action"}
     ]
   }', 1),

  ('vendre', 'Vendre', 'Page pour vendre un bien avec processus et estimation',
   '{
     "blocks": [
       {"key": "hero", "type": "hero", "label": "Hero principal"},
       {"key": "steps", "type": "steps", "label": "Étapes du processus"},
       {"key": "estimation_cta", "type": "cta", "label": "CTA Estimation"},
       {"key": "faq", "type": "faq", "label": "Questions fréquentes"}
     ]
   }', 1),

  ('landing', 'Landing Page', 'Page landing minimale avec hero, points clés et formulaire',
   '{
     "blocks": [
       {"key": "hero", "type": "hero", "label": "Hero principal"},
       {"key": "benefits", "type": "features", "label": "Bénéfices clés"},
       {"key": "form", "type": "form", "label": "Formulaire de contact"}
     ]
   }', 1),

  ('legal', 'Pages légales', 'Page pour contenu long (RGPD, CGU, Mentions légales)',
   '{
     "blocks": [
       {"key": "title", "type": "heading", "label": "Titre"},
       {"key": "content", "type": "richtext", "label": "Contenu texte"}
     ]
   }', 1),

  ('contact', 'Contact', 'Page de contact avec formulaire et localisation',
   '{
     "blocks": [
       {"key": "hero", "type": "hero", "label": "Hero principal"},
       {"key": "contact_form", "type": "form", "label": "Formulaire contact"},
       {"key": "map", "type": "map", "label": "Localisation"}
     ]
   }', 1)
ON DUPLICATE KEY UPDATE `description`=VALUES(`description`), `blocks_json`=VALUES(`blocks_json`);
