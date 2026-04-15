-- ============================================================
-- Pascal Hamm Immobilier — Schéma SQL complet (single database)
-- Version consolidée des migrations + modules actifs du dépôt.
-- Cible : MySQL 8+ / MariaDB 10.6+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `pascal_hamm_immobilier`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `pascal_hamm_immobilier`;

-- ============================================================
-- 1) AUTH / USERS
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_login_otps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `code_hash` VARCHAR(255) NOT NULL,
  `attempt_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `consumed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_created` (`email`, `created_at`),
  KEY `idx_user_created` (`user_id`, `created_at`),
  KEY `idx_ip_created` (`ip_address`, `created_at`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_admin_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2) BIENS / CATALOGUE IMMOBILIER
-- ============================================================

CREATE TABLE IF NOT EXISTS `biens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `reference` VARCHAR(50) NULL UNIQUE,
  `titre` VARCHAR(255) NOT NULL,
  `type_transaction` ENUM('vente','location') NOT NULL DEFAULT 'vente',
  `type_bien` ENUM('appartement','maison','terrain','local','immeuble','autre') NOT NULL DEFAULT 'appartement',
  `prix` DECIMAL(12,2) NOT NULL,
  `surface` DECIMAL(8,2) DEFAULT NULL,
  `pieces` TINYINT UNSIGNED DEFAULT NULL,
  `chambres` TINYINT UNSIGNED DEFAULT NULL,
  `sdb` TINYINT UNSIGNED DEFAULT NULL,
  `etage` TINYINT DEFAULT NULL,
  `adresse` VARCHAR(255) DEFAULT NULL,
  `ville` VARCHAR(100) DEFAULT NULL,
  `code_postal` VARCHAR(10) DEFAULT NULL,
  `secteur` VARCHAR(100) DEFAULT NULL,
  `latitude` DECIMAL(10,8) DEFAULT NULL,
  `longitude` DECIMAL(11,8) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `caracteristiques` JSON DEFAULT NULL,
  `dpe_classe` CHAR(1) DEFAULT NULL,
  `mode_chauffage` VARCHAR(100) DEFAULT NULL,
  `visite_virtuelle_url` VARCHAR(255) DEFAULT NULL,
  `ges_classe` CHAR(1) DEFAULT NULL,
  `annee_construction` YEAR DEFAULT NULL,
  `statut` ENUM('actif','pending','vendu','archive') NOT NULL DEFAULT 'pending',
  `etat_bien` ENUM('new','renovated','good','to_renovate') NOT NULL DEFAULT 'good',
  `exclusif` TINYINT(1) NOT NULL DEFAULT 0,
  `a_parking` TINYINT(1) NOT NULL DEFAULT 0,
  `a_jardin` TINYINT(1) NOT NULL DEFAULT 0,
  `a_piscine` TINYINT(1) NOT NULL DEFAULT 0,
  `a_terrasse` TINYINT(1) NOT NULL DEFAULT 0,
  `a_balcon` TINYINT(1) NOT NULL DEFAULT 0,
  `a_ascenseur` TINYINT(1) NOT NULL DEFAULT 0,
  `photo_principale` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_statut` (`statut`),
  KEY `idx_type_transaction` (`type_transaction`),
  KEY `idx_ville` (`ville`),
  KEY `idx_reference` (`reference`),
  KEY `idx_etat_bien` (`etat_bien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bien_photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bien_id` INT UNSIGNED NOT NULL,
  `chemin` VARCHAR(255) NOT NULL,
  `alt` VARCHAR(255) DEFAULT NULL,
  `position` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bien_id` (`bien_id`),
  CONSTRAINT `fk_photo_bien` FOREIGN KEY (`bien_id`) REFERENCES `biens`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3) LEADS / CRM / FORMULAIRES FRONT
-- ============================================================

CREATE TABLE IF NOT EXISTS `crm_leads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `source_type` VARCHAR(40) NOT NULL DEFAULT 'autre',
  `pipeline` VARCHAR(60) NOT NULL DEFAULT 'autre',
  `stage` VARCHAR(60) NOT NULL DEFAULT 'nouveau',
  `priority` VARCHAR(16) NOT NULL DEFAULT 'normal',
  `first_name` VARCHAR(80) NOT NULL,
  `last_name` VARCHAR(80) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `intent` VARCHAR(120) NULL,
  `property_type` VARCHAR(60) NULL,
  `property_address` VARCHAR(255) NULL,
  `metadata_json` JSON NULL,
  `notes` TEXT NULL,
  `consent` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  INDEX `idx_source_stage` (`source_type`, `stage`),
  INDEX `idx_pipeline` (`pipeline`),
  INDEX `idx_email` (`email`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Legacy route /ressources/guides/{persona}/{slug}
CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `source` VARCHAR(50) NOT NULL DEFAULT 'ressources',
  `nom` VARCHAR(190) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `telephone` VARCHAR(40) NULL,
  `message` TEXT NULL,
  `persona` VARCHAR(100) NULL,
  `guide_slug` VARCHAR(150) NULL,
  `guide_titre` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_source` (`source`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prenom` VARCHAR(100) NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `sujet` VARCHAR(100) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `bien_id` INT UNSIGNED DEFAULT NULL,
  `source` VARCHAR(50) DEFAULT 'contact',
  `statut` ENUM('nouveau','en_cours','traite','archive') NOT NULL DEFAULT 'nouveau',
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contacts_statut` (`statut`),
  KEY `idx_contacts_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estimations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prenom` VARCHAR(100) NOT NULL,
  `nom` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `type_bien` VARCHAR(50) DEFAULT NULL,
  `adresse` VARCHAR(255) NOT NULL,
  `surface` DECIMAL(8,2) DEFAULT NULL,
  `pieces` TINYINT UNSIGNED DEFAULT NULL,
  `etat` VARCHAR(50) DEFAULT NULL,
  `annee_construction` VARCHAR(20) DEFAULT NULL,
  `etage` VARCHAR(50) DEFAULT NULL,
  `prix_estime` DECIMAL(12,2) DEFAULT NULL,
  `statut` ENUM('nouveau','contacte','traite') NOT NULL DEFAULT 'nouveau',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4) CONTENU SITE (PAGES / BLOG / ACTUALITÉS / GUIDE LOCAL)
-- ============================================================

CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `titre` VARCHAR(255) NOT NULL,
  `contenu` LONGTEXT DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_desc` VARCHAR(320) DEFAULT NULL,
  `statut` ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `titre` VARCHAR(255) NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `contenu` LONGTEXT DEFAULT NULL,
  `categorie` VARCHAR(50) DEFAULT NULL,
  `tags` VARCHAR(255) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_desc` VARCHAR(320) DEFAULT NULL,
  `statut` ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_articles_statut` (`statut`),
  KEY `idx_articles_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `actualites` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `titre` VARCHAR(255) NOT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `contenu` LONGTEXT DEFAULT NULL,
  `categorie` VARCHAR(50) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `statut` ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guide_local` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `nom` VARCHAR(100) NOT NULL,
  `type` ENUM('quartier','commune') NOT NULL DEFAULT 'commune',
  `description` LONGTEXT DEFAULT NULL,
  `prix_m2` DECIMAL(8,2) DEFAULT NULL,
  `tendance` VARCHAR(20) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_desc` VARCHAR(320) DEFAULT NULL,
  `statut` ENUM('publie','brouillon') NOT NULL DEFAULT 'brouillon',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5) SETTINGS / CONFIG UTILISATEUR
-- ============================================================

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT,
  `setting_type` ENUM('text','textarea','email','tel','url','number','boolean','select','color','image','json','password') DEFAULT 'text',
  `setting_group` VARCHAR(50),
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_setting` (`user_id`, `setting_key`),
  KEY `idx_settings_user_group` (`user_id`, `setting_group`),
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `default_value` TEXT,
  `label` VARCHAR(255),
  `description` TEXT,
  `setting_type` VARCHAR(50),
  `setting_group` VARCHAR(50),
  `is_required` TINYINT(1) DEFAULT 0,
  `validation_rules` VARCHAR(500),
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `setting_key` VARCHAR(100),
  `old_value` TEXT,
  `new_value` TEXT,
  `changed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45),
  KEY `idx_settings_history_user` (`user_id`),
  CONSTRAINT `fk_settings_history_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6) APIs / CRÉDITS / IA
-- ============================================================

CREATE TABLE IF NOT EXISTS `api_configs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `api_name` ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `api_key` VARCHAR(500) DEFAULT NULL,
  `api_secret` VARCHAR(500) DEFAULT NULL,
  `cloud_name` VARCHAR(100) DEFAULT NULL,
  `extra` JSON DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_api` (`user_id`, `api_name`),
  CONSTRAINT `fk_api_configs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_credits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `api_name` ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `credits_remaining` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `credits_used` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `monthly_limit` DECIMAL(12,4) NOT NULL DEFAULT 500.0000,
  `reset_day` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_api_credits` (`user_id`, `api_name`),
  CONSTRAINT `fk_user_credits_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `credit_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `api_name` ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `action_type` VARCHAR(80) NOT NULL,
  `credits_used` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `from_cache` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('success','failed','blocked') NOT NULL DEFAULT 'success',
  `request_params` JSON DEFAULT NULL,
  `result_url` VARCHAR(2083) DEFAULT NULL,
  `error_message` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_api_date` (`user_id`, `api_name`, `created_at`),
  CONSTRAINT `fk_credit_history_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `image_cache` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cache_key` CHAR(64) NOT NULL,
  `api_name` ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `result_url` VARCHAR(2083) NOT NULL,
  `params_json` JSON DEFAULT NULL,
  `hit_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cache_key` (`cache_key`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_rate_limits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `api_name` ENUM('cloudinary','google_maps','quickchart') NOT NULL,
  `window_start` DATETIME NOT NULL,
  `req_count` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_rate_window` (`user_id`, `api_name`, `window_start`),
  CONSTRAINT `fk_api_rate_limits_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ia_configurations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `provider` VARCHAR(32) NOT NULL,
  `api_key` TEXT NOT NULL,
  `model` VARCHAR(120) NOT NULL,
  `tokens_used` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `estimated_cost` DECIMAL(12,6) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_ia_user_active` (`user_id`, `is_active`, `updated_at`),
  CONSTRAINT `fk_ia_configurations_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7) GOOGLE BUSINESS PROFILE (GMB)
-- ============================================================

CREATE TABLE IF NOT EXISTS `gmb_fiche` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `gmb_location_id` VARCHAR(200),
  `gmb_account_id` VARCHAR(200),
  `nom_etablissement` VARCHAR(200),
  `categorie` VARCHAR(200),
  `adresse` VARCHAR(500),
  `ville` VARCHAR(100),
  `code_postal` VARCHAR(10),
  `telephone` VARCHAR(30),
  `site_web` VARCHAR(500),
  `description` TEXT,
  `horaires` JSON,
  `photos` JSON,
  `statut` ENUM('actif','suspendu','non_verifie') DEFAULT 'non_verifie',
  `last_sync` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user` (`user_id`),
  CONSTRAINT `fk_gmb_fiche_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gmb_avis` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `gmb_review_id` VARCHAR(200) UNIQUE,
  `auteur` VARCHAR(200),
  `photo_auteur` VARCHAR(500),
  `note` TINYINT NOT NULL,
  `commentaire` TEXT,
  `reponse` TEXT DEFAULT NULL,
  `reponse_at` DATETIME DEFAULT NULL,
  `avis_at` DATETIME,
  `statut` ENUM('nouveau','lu','repondu') DEFAULT 'nouveau',
  `sentiment` ENUM('positif','neutre','negatif') DEFAULT 'neutre',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_statut` (`statut`),
  INDEX `idx_note` (`note`),
  CONSTRAINT `fk_gmb_avis_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gmb_demandes_avis` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `client_nom` VARCHAR(200),
  `client_email` VARCHAR(200),
  `client_tel` VARCHAR(30),
  `bien_adresse` VARCHAR(300),
  `canal` ENUM('email','sms','both') DEFAULT 'email',
  `template_id` INT DEFAULT NULL,
  `statut` ENUM('en_attente','envoye','ouvert','clique','avis_laisse') DEFAULT 'en_attente',
  `envoye_at` DATETIME DEFAULT NULL,
  `relance_at` DATETIME DEFAULT NULL,
  `nb_relances` TINYINT DEFAULT 0,
  `token` VARCHAR(64) UNIQUE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_demande` (`user_id`),
  INDEX `idx_statut_demande` (`statut`),
  CONSTRAINT `fk_gmb_demandes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `gmb_review_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `demande_id` INT NOT NULL,
  `email` VARCHAR(200) NOT NULL,
  `statut` ENUM('en_attente','envoye','echec') DEFAULT 'en_attente',
  `date_envoi` DATETIME DEFAULT NULL,
  `error_message` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_review_requests` (`user_id`),
  INDEX `idx_demande_review_requests` (`demande_id`),
  INDEX `idx_statut_review_requests` (`statut`),
  CONSTRAINT `fk_gmb_review_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gmb_review_requests_demande` FOREIGN KEY (`demande_id`) REFERENCES `gmb_demandes_avis`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gmb_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nom` VARCHAR(200),
  `canal` ENUM('email','sms') NOT NULL,
  `sujet` VARCHAR(300),
  `contenu` TEXT NOT NULL,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_template` (`user_id`),
  UNIQUE KEY `uk_user_nom_canal` (`user_id`, `nom`, `canal`),
  CONSTRAINT `fk_gmb_templates_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gmb_statistiques` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `date_stat` DATE NOT NULL,
  `impressions` INT DEFAULT 0,
  `clics_site` INT DEFAULT 0,
  `appels` INT DEFAULT 0,
  `itineraires` INT DEFAULT 0,
  `photos_vues` INT DEFAULT 0,
  `recherches_dir` INT DEFAULT 0,
  `recherches_disc` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_date` (`user_id`, `date_stat`),
  INDEX `idx_user_stats` (`user_id`),
  CONSTRAINT `fk_gmb_stats_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8) SOCIAL MODULE
-- ============================================================

CREATE TABLE IF NOT EXISTS `social_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `titre` VARCHAR(300),
  `contenu` TEXT NOT NULL,
  `contenu_fb` TEXT,
  `contenu_ig` TEXT,
  `contenu_li` TEXT,
  `medias` JSON,
  `reseaux` JSON NOT NULL,
  `type_post` ENUM('post','reel','story','carrousel','article','event') DEFAULT 'post',
  `statut` ENUM('brouillon','planifie','publie','erreur','archive') DEFAULT 'brouillon',
  `planifie_at` DATETIME DEFAULT NULL,
  `publie_at` DATETIME DEFAULT NULL,
  `fb_post_id` VARCHAR(200) DEFAULT NULL,
  `ig_post_id` VARCHAR(200) DEFAULT NULL,
  `li_post_id` VARCHAR(200) DEFAULT NULL,
  `fb_likes` INT DEFAULT 0,
  `fb_comments` INT DEFAULT 0,
  `fb_shares` INT DEFAULT 0,
  `ig_likes` INT DEFAULT 0,
  `ig_comments` INT DEFAULT 0,
  `ig_reach` INT DEFAULT 0,
  `li_likes` INT DEFAULT 0,
  `li_comments` INT DEFAULT 0,
  `li_impressions` INT DEFAULT 0,
  `tags` JSON,
  `bien_id` INT DEFAULT NULL,
  `categorie` ENUM('bien','conseil','marche','temoignage','equipe','autre') DEFAULT 'autre',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_social_posts_user` (`user_id`),
  INDEX `idx_social_posts_statut` (`statut`),
  INDEX `idx_social_posts_planifie` (`planifie_at`),
  CONSTRAINT `fk_social_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_social_posts_bien` FOREIGN KEY (`bien_id`) REFERENCES `biens`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `social_medias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `post_id` INT DEFAULT NULL,
  `nom_fichier` VARCHAR(300),
  `chemin` VARCHAR(500),
  `type` ENUM('image','video','gif') DEFAULT 'image',
  `taille` INT,
  `largeur` INT DEFAULT NULL,
  `hauteur` INT DEFAULT NULL,
  `alt_text` VARCHAR(500),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_social_medias_user` (`user_id`),
  INDEX `idx_social_medias_post` (`post_id`),
  CONSTRAINT `fk_social_medias_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_social_medias_post` FOREIGN KEY (`post_id`) REFERENCES `social_posts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `social_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nom` VARCHAR(200),
  `reseau` ENUM('facebook','instagram','linkedin','all') DEFAULT 'all',
  `categorie` ENUM('bien','conseil','marche','temoignage','equipe','autre'),
  `contenu` TEXT NOT NULL,
  `variables` JSON,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_social_templates_user` (`user_id`),
  CONSTRAINT `fk_social_templates_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `social_stats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `reseau` ENUM('facebook','instagram','linkedin'),
  `date_stat` DATE NOT NULL,
  `abonnes` INT DEFAULT 0,
  `impressions` INT DEFAULT 0,
  `reach` INT DEFAULT 0,
  `engagements` INT DEFAULT 0,
  `clics` INT DEFAULT 0,
  `posts_count` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_reseau_date` (`user_id`, `reseau`, `date_stat`),
  INDEX `idx_social_stats_user` (`user_id`),
  CONSTRAINT `fk_social_stats_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `social_hashtags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `hashtag` VARCHAR(100),
  `reseau` ENUM('instagram','facebook','linkedin','all') DEFAULT 'all',
  `categorie` VARCHAR(100),
  `nb_uses` INT DEFAULT 0,
  `actif` TINYINT(1) DEFAULT 1,
  INDEX `idx_social_hashtags_user` (`user_id`),
  INDEX `idx_social_hashtags_reseau` (`reseau`),
  CONSTRAINT `fk_social_hashtags_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9) SEO MODULES (legacy admin/seo + module /modules/seo)
-- ============================================================

CREATE TABLE IF NOT EXISTS `keywords` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `keyword` VARCHAR(255) NOT NULL UNIQUE,
  `search_volume` INT UNSIGNED NOT NULL DEFAULT 0,
  `competition` INT UNSIGNED NOT NULL DEFAULT 0,
  `search_intent` ENUM('informational','commercial','transactional') NOT NULL DEFAULT 'informational',
  `status` ENUM('pending','validated','rejected') NOT NULL DEFAULT 'pending',
  `position` INT NOT NULL DEFAULT 0,
  `position_trend` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_keywords_status` (`status`),
  INDEX `idx_keywords_competition` (`competition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `silos` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `region` VARCHAR(128) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `silo_articles` (
  `silo_id` BIGINT UNSIGNED NOT NULL,
  `article_id` BIGINT UNSIGNED NOT NULL,
  `position` INT NOT NULL,
  PRIMARY KEY (`silo_id`, `article_id`),
  INDEX `idx_silo_position` (`silo_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `serp_snapshots` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `keyword` VARCHAR(255) NOT NULL,
  `rank_position` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `favicon_url` VARCHAR(500) NULL,
  `captured_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_serp_keyword_rank` (`keyword`, `rank_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `silo_opportunities` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `silo_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `position` INT NOT NULL,
  `impact` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  INDEX `idx_silo_opp` (`silo_id`, `impact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_keywords` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `keyword` VARCHAR(190) NOT NULL,
  `target_url` VARCHAR(255) NOT NULL,
  `current_position` INT NULL,
  `previous_position` INT NULL,
  `estimated_volume` INT UNSIGNED DEFAULT 0,
  `difficulty` TINYINT UNSIGNED DEFAULT 0,
  `last_checked_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_keyword` (`user_id`, `keyword`),
  KEY `idx_keywords_user` (`user_id`),
  KEY `idx_keywords_position` (`current_position`),
  CONSTRAINT `fk_seo_keywords_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_keyword_history` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `keyword_id` BIGINT UNSIGNED NOT NULL,
  `position_value` INT NULL,
  `checked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_history_keyword_date` (`keyword_id`, `checked_at`),
  CONSTRAINT `fk_keyword_history_keyword` FOREIGN KEY (`keyword_id`) REFERENCES `seo_keywords`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_city_pages` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `city` VARCHAR(160) NOT NULL,
  `postal_code` VARCHAR(12) NOT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `h1` VARCHAR(190) NOT NULL,
  `seo_title` VARCHAR(60) NOT NULL,
  `meta_description` VARCHAR(160) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `price_m2` DECIMAL(10,2) NULL,
  `population` INT UNSIGNED NULL,
  `targeted_keywords` JSON NULL,
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_city_slug` (`user_id`, `slug`),
  KEY `idx_city_status` (`user_id`, `status`),
  CONSTRAINT `fk_seo_city_pages_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_sitemap_urls` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `priority` DECIMAL(2,1) NOT NULL DEFAULT 0.5,
  `changefreq` ENUM('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
  `lastmod` DATE NOT NULL,
  `included` TINYINT(1) NOT NULL DEFAULT 1,
  `source_type` ENUM('fixed','city','blog','property','custom') NOT NULL DEFAULT 'custom',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_url` (`user_id`, `url`),
  KEY `idx_sitemap_user` (`user_id`, `included`),
  CONSTRAINT `fk_seo_sitemap_urls_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_sitemap_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `urls_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `ping_status` TINYINT(1) NOT NULL DEFAULT 0,
  `submitted_to_gsc` TINYINT(1) NOT NULL DEFAULT 0,
  `xml_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_sitemap_logs_user` (`user_id`, `generated_at`),
  CONSTRAINT `fk_seo_sitemap_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_performance_audits` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `audited_url` VARCHAR(255) NOT NULL,
  `device` ENUM('mobile','desktop') NOT NULL DEFAULT 'mobile',
  `perf_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `seo_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `access_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `bp_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `lcp_ms` INT UNSIGNED NULL,
  `inp_ms` INT UNSIGNED NULL,
  `cls_score` DECIMAL(5,3) NULL,
  `ttfb_ms` INT UNSIGNED NULL,
  `raw_payload` LONGTEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_perf_user` (`user_id`, `created_at`),
  CONSTRAINT `fk_seo_performance_audits_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seo_rate_limits` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `endpoint_key` VARCHAR(120) NOT NULL,
  `call_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `window_started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_limit` (`user_id`, `endpoint_key`),
  CONSTRAINT `fk_seo_rate_limits_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10) SUPERADMIN
-- ============================================================

CREATE TABLE IF NOT EXISTS `module_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module_name` VARCHAR(100) NOT NULL,
  `enabled_for_users` TINYINT(1) DEFAULT 1,
  `enabled_for_admins` TINYINT(1) DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_module_name` (`module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_page_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `superadmin_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `page_url` VARCHAR(255),
  `status` ENUM('pending', 'allowed', 'denied') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `responded_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_user_status` (`user_id`, `status`),
  INDEX `idx_superadmin_created` (`superadmin_id`, `created_at`),
  CONSTRAINT `fk_apr_superadmin` FOREIGN KEY (`superadmin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_apr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_page_presence` (
  `user_id` INT NOT NULL PRIMARY KEY,
  `page_url` VARCHAR(255) DEFAULT NULL,
  `last_seen_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_presence_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11) MINIMAL SEEDS
-- ============================================================

INSERT IGNORE INTO `settings_templates` (`setting_key`, `default_value`, `label`, `description`, `setting_type`, `setting_group`, `is_required`, `validation_rules`, `sort_order`) VALUES
('advisor_firstname','','Prénom','Votre prénom affiché partout dans le CRM','text','conseiller',1,'max:50',1),
('advisor_lastname','','Nom','Votre nom de famille','text','conseiller',1,'max:50',2),
('advisor_email','','Email professionnel','Email de contact','email','conseiller',1,'',6),
('agency_name','','Nom de l\'agence','Ex: Agence Dupont Immobilier','text','agence',0,'max:100',1),
('zone_city','','Ville principale','Votre ville de prospection principale','text','zone',1,'max:100',1),
('site_meta_title','','Meta titre SEO','Max 60 caractères','text','site_vitrine',0,'max:60',3)
ON DUPLICATE KEY UPDATE
  `default_value` = VALUES(`default_value`),
  `label` = VALUES(`label`),
  `description` = VALUES(`description`),
  `setting_type` = VALUES(`setting_type`),
  `setting_group` = VALUES(`setting_group`),
  `is_required` = VALUES(`is_required`),
  `validation_rules` = VALUES(`validation_rules`),
  `sort_order` = VALUES(`sort_order`);

SET FOREIGN_KEY_CHECKS = 1;
