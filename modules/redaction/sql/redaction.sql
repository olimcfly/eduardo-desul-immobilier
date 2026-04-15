-- Extend blog_articles with redaction fields
ALTER TABLE `blog_articles` ADD COLUMN `intro`             TEXT         DEFAULT NULL AFTER `contenu`;
ALTER TABLE `blog_articles` ADD COLUMN `conclusion`        TEXT         DEFAULT NULL AFTER `intro`;
ALTER TABLE `blog_articles` ADD COLUMN `hn_structure`      JSON         DEFAULT NULL AFTER `conclusion`;
ALTER TABLE `blog_articles` ADD COLUMN `maillage_interne`  JSON         DEFAULT NULL AFTER `hn_structure`;
ALTER TABLE `blog_articles` ADD COLUMN `maillage_externe`  JSON         DEFAULT NULL AFTER `maillage_interne`;
ALTER TABLE `blog_articles` ADD COLUMN `mot_cle_principal` VARCHAR(255) DEFAULT NULL AFTER `maillage_externe`;
ALTER TABLE `blog_articles` ADD COLUMN `mots_cles_lsi`     TEXT         DEFAULT NULL AFTER `mot_cle_principal`;
ALTER TABLE `blog_articles` ADD COLUMN `user_id`           INT UNSIGNED DEFAULT NULL AFTER `website_id`;

-- Campaigns (1 pillar + 5 consciousness levels = 6 articles)
CREATE TABLE IF NOT EXISTS `blog_campaigns` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `website_id`  INT UNSIGNED NOT NULL,
  `nom`         VARCHAR(255) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `mot_cle`     VARCHAR(255) DEFAULT NULL,
  `statut`      ENUM('draft','actif','terminé') DEFAULT 'draft',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Links campaign articles with their role
CREATE TABLE IF NOT EXISTS `blog_campaign_articles` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `campaign_id`      INT UNSIGNED NOT NULL,
  `article_id`       INT UNSIGNED DEFAULT NULL,
  `role`             ENUM('pilier','conscience') NOT NULL DEFAULT 'conscience',
  `niveau_conscience` TINYINT UNSIGNED DEFAULT NULL COMMENT '1=Inconscient 2=Douleur 3=Solution 4=Produit 5=Plus conscient',
  `ordre`            TINYINT UNSIGNED DEFAULT 0,
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_article`  (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Publications derived from articles (GMB, Facebook, LinkedIn)
CREATE TABLE IF NOT EXISTS `blog_publications` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `article_id`   INT UNSIGNED DEFAULT NULL,
  `user_id`      INT UNSIGNED NOT NULL,
  `reseau`       ENUM('gmb','facebook','linkedin','instagram') NOT NULL,
  `titre`        VARCHAR(255) DEFAULT NULL,
  `contenu`      TEXT         NOT NULL,
  `statut`       ENUM('draft','planifié','publié') DEFAULT 'draft',
  `planifie_at`  DATETIME     DEFAULT NULL,
  `published_at` DATETIME     DEFAULT NULL,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_article` (`article_id`),
  KEY `idx_user`    (`user_id`),
  KEY `idx_statut`  (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
