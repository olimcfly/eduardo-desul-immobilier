-- ============================================================
-- MIGRATION 017 — Upgrade cms_sections + compatibilité page.php
-- La table cms_sections existe déjà ; on ajoute les colonnes
-- manquantes pour le SectionRenderer (user_id, page_type, label).
-- ============================================================

SET NAMES utf8mb4;

-- ── Colonnes optionnelles pour cms_sections ──────────────────

ALTER TABLE `cms_sections`
    ADD COLUMN IF NOT EXISTS `user_id`    INT UNSIGNED  DEFAULT NULL  COMMENT 'Propriétaire (multi-conseiller)' AFTER `page_id`,
    ADD COLUMN IF NOT EXISTS `page_type`  VARCHAR(50)   DEFAULT 'page' COMMENT 'page | lp' AFTER `section_type`,
    ADD COLUMN IF NOT EXISTS `label`      VARCHAR(255)  DEFAULT NULL  COMMENT 'Libellé admin' AFTER `page_type`;

-- Index supplémentaires
ALTER TABLE `cms_sections`
    ADD INDEX IF NOT EXISTS `idx_cms_sect_user` (`user_id`);

-- ── Upgrade cms_pages : ajouter data_json et kind ────────────
-- Ces colonnes sont utilisées par page.php / SectionRenderer

ALTER TABLE `cms_pages`
    ADD COLUMN IF NOT EXISTS `kind`      VARCHAR(50)  DEFAULT NULL COMMENT 'Sous-type libre' AFTER `page_type`,
    ADD COLUMN IF NOT EXISTS `data_json` JSON         DEFAULT NULL COMMENT 'Données structurées JSON' AFTER `kind`;
