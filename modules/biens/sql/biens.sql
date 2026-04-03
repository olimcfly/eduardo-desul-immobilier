-- ============================================================
-- EXTENSION DU MODULE BIENS (CMS immobilier)
-- ============================================================

ALTER TABLE `biens`
    ADD COLUMN IF NOT EXISTS `reference` VARCHAR(50) NULL UNIQUE AFTER `slug`,
    ADD COLUMN IF NOT EXISTS `etat_bien` ENUM('new','renovated','good','to_renovate') NOT NULL DEFAULT 'good' AFTER `statut`,
    ADD COLUMN IF NOT EXISTS `mode_chauffage` VARCHAR(100) NULL AFTER `dpe_classe`,
    ADD COLUMN IF NOT EXISTS `visite_virtuelle_url` VARCHAR(255) NULL AFTER `mode_chauffage`,
    ADD COLUMN IF NOT EXISTS `a_parking` TINYINT(1) NOT NULL DEFAULT 0 AFTER `exclusif`,
    ADD COLUMN IF NOT EXISTS `a_jardin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `a_parking`,
    ADD COLUMN IF NOT EXISTS `a_piscine` TINYINT(1) NOT NULL DEFAULT 0 AFTER `a_jardin`,
    ADD COLUMN IF NOT EXISTS `a_terrasse` TINYINT(1) NOT NULL DEFAULT 0 AFTER `a_piscine`,
    ADD COLUMN IF NOT EXISTS `a_balcon` TINYINT(1) NOT NULL DEFAULT 0 AFTER `a_terrasse`,
    ADD COLUMN IF NOT EXISTS `a_ascenseur` TINYINT(1) NOT NULL DEFAULT 0 AFTER `a_balcon`;

ALTER TABLE `biens`
    ADD INDEX IF NOT EXISTS `idx_reference` (`reference`),
    ADD INDEX IF NOT EXISTS `idx_etat_bien` (`etat_bien`);
