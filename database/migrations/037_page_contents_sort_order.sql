-- ============================================================
-- MIGRATION 037 — page_contents.sort_order (schémas existants)
-- Si la table a été créée sans cette colonne (erreur 1054).
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `page_contents`
    ADD COLUMN IF NOT EXISTS `sort_order` INT NOT NULL DEFAULT 0 AFTER `field_type`;
