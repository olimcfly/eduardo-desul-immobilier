-- ============================================================
-- MIGRATION 001 — Schéma initial Pascal Hamm Immobilier
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Utilisateurs admin ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(255) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `name`       VARCHAR(100) NOT NULL,
  `role`       ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Biens immobiliers ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `biens` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`             VARCHAR(200) NOT NULL UNIQUE,
  `titre`            VARCHAR(255) NOT NULL,
  `type_transaction` ENUM('vente','location') NOT NULL DEFAULT 'vente',
  `type_bien`        ENUM('appartement','maison','terrain','local','immeuble','autre') NOT NULL DEFAULT 'appartement',
  `prix`             DECIMAL(12,2) NOT NULL,
  `surface`          DECIMAL(8,2) DEFAULT NULL,
  `pieces`           TINYINT UNSIGNED DEFAULT NULL,
  `chambres`         TINYINT UNSIGNED DEFAULT NULL,
  `sdb`              TINYINT UNSIGNED DEFAULT NULL,
  `etage`            TINYINT DEFAULT NULL,
  `adresse`          VARCHAR(255) DEFAULT NULL,
  `ville`            VARCHAR(100) DEFAULT NULL,
  `code_postal`      VARCHAR(10) DEFAULT NULL,
  `secteur`          VARCHAR(100) DEFAULT NULL,
  `latitude`         DECIMAL(10,8) DEFAULT NULL,
  `longitude`        DECIMAL(11,8) DEFAULT NULL,
  `description`      TEXT DEFAULT NULL,
  `caracteristiques` JSON DEFAULT NULL,
  `dpe_classe`       CHAR(1) DEFAULT NULL,
  `ges_classe`       CHAR(1) DEFAULT NULL,
  `annee_construction` YEAR DEFAULT NULL,
  `statut`           ENUM('actif','pending','vendu','archive') NOT NULL DEFAULT 'pending',
  `exclusif`         TINYINT(1) NOT NULL DEFAULT 0,
  `photo_principale` VARCHAR(255) DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statut` (`statut`),
  KEY `idx_type_transaction` (`type_transaction`),
  KEY `idx_ville` (`ville`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Photos des biens ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bien_photos` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bien_id`    INT UNSIGNED NOT NULL,
  `chemin`     VARCHAR(255) NOT NULL,
  `alt`        VARCHAR(255) DEFAULT NULL,
  `position`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bien_id` (`bien_id`),
  CONSTRAINT `fk_photo_bien` FOREIGN KEY (`bien_id`) REFERENCES `biens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contacts / leads ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `contacts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prenom`     VARCHAR(100) NOT NULL,
  `nom`        VARCHAR(100) NOT NULL,
  `email`      VARCHAR(255) NOT NULL,
  `telephone`  VARCHAR(20) DEFAULT NULL,
  `sujet`      VARCHAR(100) DEFAULT NULL,
  `message`    TEXT NOT NULL,
  `bien_id`    INT UNSIGNED DEFAULT NULL,
  `source`     VARCHAR(50) DEFAULT 'contact',
  `statut`     ENUM('nouveau','en_cours','traite','archive') NOT NULL DEFAULT 'nouveau',
  `ip`         VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statut` (`statut`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Estimations ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `estimations` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prenom`            VARCHAR(100) NOT NULL,
  `nom`               VARCHAR(100) DEFAULT NULL,
  `email`             VARCHAR(255) NOT NULL,
  `telephone`         VARCHAR(20) DEFAULT NULL,
  `type_bien`         VARCHAR(50) DEFAULT NULL,
  `adresse`           VARCHAR(255) NOT NULL,
  `surface`           DECIMAL(8,2) DEFAULT NULL,
  `pieces`            TINYINT UNSIGNED DEFAULT NULL,
  `etat`              VARCHAR(50) DEFAULT NULL,
  `annee_construction` VARCHAR(20) DEFAULT NULL,
  `etage`             VARCHAR(50) DEFAULT NULL,
  `prix_estime`       DECIMAL(12,2) DEFAULT NULL,
  `statut`            ENUM('nouveau','contacte','traite') NOT NULL DEFAULT 'nouveau',
  `notes`             TEXT DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Pages CMS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(200) NOT NULL UNIQUE,
  `titre`       VARCHAR(255) NOT NULL,
  `contenu`     LONGTEXT DEFAULT NULL,
  `meta_title`  VARCHAR(255) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `statut`      ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Articles blog ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `articles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(200) NOT NULL UNIQUE,
  `titre`       VARCHAR(255) NOT NULL,
  `excerpt`     TEXT DEFAULT NULL,
  `contenu`     LONGTEXT DEFAULT NULL,
  `categorie`   VARCHAR(50) DEFAULT NULL,
  `tags`        VARCHAR(255) DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `meta_title`  VARCHAR(255) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `statut`      ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `published_at` DATETIME DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statut` (`statut`),
  KEY `idx_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Actualités ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `actualites` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(200) NOT NULL UNIQUE,
  `titre`       VARCHAR(255) NOT NULL,
  `excerpt`     TEXT DEFAULT NULL,
  `contenu`     LONGTEXT DEFAULT NULL,
  `categorie`   VARCHAR(50) DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `statut`      ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `published_at` DATETIME DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Guide local (villes/quartiers) ───────────────────────────
CREATE TABLE IF NOT EXISTS `guide_local` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(200) NOT NULL UNIQUE,
  `nom`         VARCHAR(100) NOT NULL,
  `type`        ENUM('quartier','commune') NOT NULL DEFAULT 'commune',
  `description` LONGTEXT DEFAULT NULL,
  `prix_m2`     DECIMAL(8,2) DEFAULT NULL,
  `tendance`    VARCHAR(20) DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `meta_title`  VARCHAR(255) DEFAULT NULL,
  `meta_desc`   VARCHAR(320) DEFAULT NULL,
  `statut`      ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Avis Google My Business ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `gmb_reviews` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gmb_id`      VARCHAR(100) NOT NULL UNIQUE,
  `auteur`      VARCHAR(150) NOT NULL,
  `note`        TINYINT UNSIGNED NOT NULL,
  `texte`       TEXT DEFAULT NULL,
  `reponse`     TEXT DEFAULT NULL,
  `date_avis`   DATETIME DEFAULT NULL,
  `date_sync`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_note` (`note`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SEO — mots-clés ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seo_keywords` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mot`         VARCHAR(255) NOT NULL,
  `position`    TINYINT UNSIGNED DEFAULT NULL,
  `url`         VARCHAR(255) DEFAULT NULL,
  `impressions` INT UNSIGNED DEFAULT 0,
  `clics`       INT UNSIGNED DEFAULT 0,
  `date_mesure` DATE DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Social posts ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `social_posts` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plateforme`  ENUM('facebook','instagram','linkedin','google') NOT NULL,
  `contenu`     TEXT NOT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `statut`      ENUM('planifie','publie','erreur') NOT NULL DEFAULT 'planifie',
  `date_prevue` DATETIME NOT NULL,
  `date_publie` DATETIME DEFAULT NULL,
  `ref_externe` VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statut` (`statut`),
  KEY `idx_date_prevue` (`date_prevue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Paramètres ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cle`         VARCHAR(100) NOT NULL UNIQUE,
  `valeur`      TEXT DEFAULT NULL,
  `groupe`      VARCHAR(50) DEFAULT 'general',
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seeds ─────────────────────────────────────────────────────
INSERT IGNORE INTO `settings` (`cle`, `valeur`, `groupe`) VALUES
  ('site_nom',        'Pascal Hamm Immobilier', 'general'),
  ('site_telephone',  '',                          'general'),
  ('site_email',      'contact@pascal-hamm-immobilier.fr', 'general'),
  ('site_adresse',    'Aix-en-Provence, France',           'general'),
  ('gmb_account_id',  '',                           'api'),
  ('smtp_host',       '',                           'smtp');

SET FOREIGN_KEY_CHECKS = 1;
