-- ============================================================
-- MIGRATION 008 — Correction de la table settings
-- Ajout de la colonne is_encrypted manquante
-- ============================================================

SET NAMES utf8mb4;

-- Ajout de is_encrypted si absent (nécessaire pour le chiffrement des clés API / mots de passe SMTP)
ALTER TABLE settings
    ADD COLUMN IF NOT EXISTS is_encrypted TINYINT(1) NOT NULL DEFAULT 0;

-- Index utile pour les lectures groupées par préfixe de clé
ALTER TABLE settings
    ADD KEY IF NOT EXISTS idx_settings_key (setting_key);
