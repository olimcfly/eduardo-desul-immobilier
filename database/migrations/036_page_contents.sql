-- ============================================================
-- MIGRATION 036 — Contenus texte par page (CMS relationnel)
-- Une ligne = un champ éditable (pas de JSON pour les textes).
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `page_contents` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_slug` VARCHAR(191) NOT NULL,
    `section_name` VARCHAR(128) NOT NULL DEFAULT 'main',
    `field_name` VARCHAR(128) NOT NULL,
    `field_value` MEDIUMTEXT NULL,
    `field_type` VARCHAR(32) NOT NULL DEFAULT 'text',
    `sort_order` INT NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_page_section_field` (`page_slug`, `section_name`, `field_name`),
    KEY `idx_page_contents_slug` (`page_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
