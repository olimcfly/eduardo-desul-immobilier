-- ============================================================
-- SETTINGS MODULE — table + defaults (bootstrap rapide)
-- Source complète: database/migrations/004_settings_centralization.sql
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value LONGTEXT,
  setting_type VARCHAR(50) DEFAULT 'text',
  setting_group VARCHAR(50),
  is_encrypted TINYINT(1) DEFAULT 0,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_setting (user_id, setting_key),
  KEY idx_settings_user_group (user_id, setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  default_value TEXT,
  label VARCHAR(255),
  description TEXT,
  setting_type VARCHAR(50) DEFAULT 'text',
  setting_group VARCHAR(50),
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings_templates (setting_key, default_value, label, setting_type, setting_group, sort_order)
VALUES
('profil_prenom', '', 'Prénom', 'text', 'profil', 1),
('profil_nom', 'Eduardo De Sul', 'Nom', 'text', 'profil', 2),
('profil_email', '', 'Email', 'email', 'profil', 3),
('profil_ville', 'Bordeaux', 'Ville', 'text', 'profil', 4),
('site_nom', 'Eduardo Desul Immobilier', 'Nom du site', 'text', 'site', 1),
('site_url', '', 'URL du site', 'url', 'site', 2),
('zone_ville', 'Bordeaux', 'Ville principale', 'text', 'zone', 1),
('api_openai', '', 'Clé API OpenAI', 'password', 'api', 1),
('smtp_host', '', 'Hôte SMTP', 'text', 'smtp', 1),
('smtp_port', '587', 'Port SMTP', 'number', 'smtp', 2)
ON DUPLICATE KEY UPDATE
  default_value = VALUES(default_value),
  label = VALUES(label),
  setting_type = VALUES(setting_type),
  setting_group = VALUES(setting_group),
  sort_order = VALUES(sort_order);
