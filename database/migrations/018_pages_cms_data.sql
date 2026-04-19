-- ============================================================
-- MIGRATION 018 — CMS Pages : affectation des templates
-- Met à jour cms_pages avec les chemins de templates corrects
-- et insère les pages manquantes (zones, quartiers, guides…)
-- ============================================================

SET NAMES utf8mb4;

-- ── Site reference ────────────────────────────────────────────
-- site_id = 1 → Eduardo De Sul Immobilier (eduardo-desul-immobilier.fr)

-- ── Mise à jour des pages existantes (slug = clé) ────────────

UPDATE `cms_pages` SET `template` = 'pages/core/home',          `status` = 'published' WHERE `slug` = 'home';
UPDATE `cms_pages` SET `template` = 'pages/core/a-propos',      `status` = 'published' WHERE `slug` = 'a-propos';
UPDATE `cms_pages` SET `template` = 'pages/core/contact',       `status` = 'published' WHERE `slug` = 'contact';
UPDATE `cms_pages` SET `template` = 'pages/services/services',  `status` = 'published' WHERE `slug` = 'services';
UPDATE `cms_pages` SET `template` = 'pages/core/plan-du-site',  `status` = 'published' WHERE `slug` = 'plan-du-site';

-- Biens
UPDATE `cms_pages` SET `template` = 'pages/biens/index',        `status` = 'published' WHERE `slug` = 'biens';
UPDATE `cms_pages` SET `template` = 'pages/biens/appartements', `status` = 'published' WHERE `slug` = 'appartements';
UPDATE `cms_pages` SET `template` = 'pages/biens/maisons',      `status` = 'published' WHERE `slug` = 'maisons';
UPDATE `cms_pages` SET `template` = 'pages/biens/prestige',     `status` = 'published' WHERE `slug` = 'prestige';
UPDATE `cms_pages` SET `template` = 'pages/biens/vendus',       `status` = 'published' WHERE `slug` = 'biens-vendus';

-- Blog / Actualités
UPDATE `cms_pages` SET `template` = 'pages/blog/index',         `status` = 'published' WHERE `slug` = 'blog';

-- Estimation
UPDATE `cms_pages` SET `template` = 'pages/estimation/estimation-gratuite', `status` = 'published' WHERE `slug` = 'estimation-gratuite';
UPDATE `cms_pages` SET `template` = 'pages/estimation/merc-estimation',     `status` = 'published' WHERE `slug` = 'merci-estimation';

-- Conversion
UPDATE `cms_pages` SET `template` = 'pages/conversion/prendre-rendez-vous', `status` = 'published' WHERE `slug` = 'prendre-rendez-vous';
UPDATE `cms_pages` SET `template` = 'pages/conversion/avis-valeur',          `status` = 'published' WHERE `slug` = 'avis-de-valeur';
UPDATE `cms_pages` SET `template` = 'pages/conversion/merci',                `status` = 'published' WHERE `slug` = 'merci-contact';

-- Ressources / Guides
UPDATE `cms_pages` SET `template` = 'pages/guides/guide-vendeur',   `status` = 'published' WHERE `slug` = 'guide-vendeur';
UPDATE `cms_pages` SET `template` = 'pages/guides/guide-acheteur',  `status` = 'published' WHERE `slug` = 'guide-acheteur';
UPDATE `cms_pages` SET `template` = 'pages/ressources/index',       `status` = 'published' WHERE `slug` = 'ressources';
UPDATE `cms_pages` SET `template` = 'pages/guide-local/index',      `status` = 'published' WHERE `slug` = 'guides-locaux';

-- Légal
UPDATE `cms_pages` SET `template` = 'pages/legal/mentions-legales',          `status` = 'published' WHERE `slug` = 'mentions-legales';
UPDATE `cms_pages` SET `template` = 'pages/legal/politique-confidentialite', `status` = 'published' WHERE `slug` = 'politique-confidentialite';
UPDATE `cms_pages` SET `template` = 'pages/legal/politique-cookies',         `status` = 'published' WHERE `slug` = 'politique-cookies';
UPDATE `cms_pages` SET `template` = 'pages/legal/cgv',                       `status` = 'published' WHERE `slug` = 'cgv';

-- Secteurs
UPDATE `cms_pages` SET `template` = 'pages/secteurs/index', `status` = 'published' WHERE `slug` = 'secteurs';
UPDATE `cms_pages` SET `template` = 'pages/social-proof/avis', `status` = 'published' WHERE `slug` = 'temoignages';

-- Financement
UPDATE `cms_pages` SET `template` = 'pages/financement/financement', `status` = 'published' WHERE `slug` = 'financement';

-- ── Insertion des pages manquantes ───────────────────────────

INSERT IGNORE INTO `cms_pages`
    (`site_id`, `slug`, `title`, `template`, `page_type`, `status`, `show_in_menu`, `show_in_footer`, `show_in_sitemap`)
VALUES
-- Zone : villes
(1, 'immobilier/aix-en-provence',       'Immobilier Bordeaux',      'pages/zones/villes/aix-en-provence',       'zone', 'published', 0, 0, 1),
(1, 'immobilier/beaurecueil',            'Immobilier Beaurecueil',           'pages/zones/villes/beaurecueil',            'zone', 'published', 0, 0, 1),
(1, 'immobilier/bouc-bel-air',           'Immobilier Bouc-Bel-Air',          'pages/zones/villes/bouc-bel-air',           'zone', 'published', 0, 0, 1),
(1, 'immobilier/eguilles',               'Immobilier Éguilles',              'pages/zones/villes/eguilles',               'zone', 'published', 0, 0, 1),
(1, 'immobilier/gardanne',               'Immobilier Gardanne',              'pages/zones/villes/gardanne',               'zone', 'published', 0, 0, 1),
(1, 'immobilier/lambesc',                'Immobilier Lambesc',               'pages/zones/villes/lambesc',                'zone', 'published', 0, 0, 1),
(1, 'immobilier/le-puy-sainte-reparade', 'Immobilier Le Puy-Sainte-Réparade','pages/zones/villes/le-puy-sainte-reparade', 'zone', 'published', 0, 0, 1),
(1, 'immobilier/le-tholonet',            'Immobilier Le Tholonet',           'pages/zones/villes/le-tholonet',            'zone', 'published', 0, 0, 1),
(1, 'immobilier/meyreuil',               'Immobilier Meyreuil',              'pages/zones/villes/meyreuil',               'zone', 'published', 0, 0, 1),
(1, 'immobilier/rognes',                 'Immobilier Rognes',                'pages/zones/villes/rognes',                 'zone', 'published', 0, 0, 1),
(1, 'immobilier/saint-cannat',           'Immobilier Saint-Cannat',          'pages/zones/villes/saint-cannat',           'zone', 'published', 0, 0, 1),
(1, 'immobilier/saint-marc-jaumegarde',  'Immobilier Saint-Marc-Jaumegarde', 'pages/zones/villes/saint-marc-jaumegarde',  'zone', 'published', 0, 0, 1),
(1, 'immobilier/simiane-collongue',      'Immobilier Simiane-Collongue',     'pages/zones/villes/simiane-collongue',      'zone', 'published', 0, 0, 1),
(1, 'immobilier/venelles',               'Immobilier Venelles',              'pages/zones/villes/venelles',               'zone', 'published', 0, 0, 1),
(1, 'immobilier/ventabren',              'Immobilier Ventabren',             'pages/zones/villes/ventabren',              'zone', 'published', 0, 0, 1),
-- Zone : quartiers
(1, 'quartier/centre-ville',   'Quartier Centre-Ville',   'pages/zones/quartiers/centre-ville',   'zone', 'published', 0, 0, 1),
(1, 'quartier/jas-de-bouffan', 'Quartier Jas de Bouffan', 'pages/zones/quartiers/jas-de-bouffan', 'zone', 'published', 0, 0, 1),
(1, 'quartier/les-milles',     'Quartier Les Milles',     'pages/zones/quartiers/les-milles',     'zone', 'published', 0, 0, 1),
(1, 'quartier/luynes',         'Quartier Luynes',         'pages/zones/quartiers/luynes',         'zone', 'published', 0, 0, 1),
(1, 'quartier/mazarin',        'Quartier Mazarin',        'pages/zones/quartiers/mazarin',        'zone', 'published', 0, 0, 1),
(1, 'quartier/puyricard',      'Quartier Puyricard',      'pages/zones/quartiers/puyricard',      'zone', 'published', 0, 0, 1),
-- Avis / preuve sociale
(1, 'avis',                    'Avis clients',            'pages/social-proof/avis',              'page', 'published', 1, 1, 1);
