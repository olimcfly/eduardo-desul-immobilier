-- ============================================================
-- MIGRATION 025 — Site development mode notification setting
-- ============================================================

SET NAMES utf8mb4;

INSERT INTO settings_templates
(setting_key, default_value, label, description, setting_type, setting_group, is_required, validation_rules, sort_order)
VALUES
('site_development_mode','1','Notification développement','Affiche une notification aux superusers indiquant que le site est en développement','boolean','site_vitrine',0,'',11)
ON DUPLICATE KEY UPDATE
  default_value = VALUES(default_value),
  label = VALUES(label),
  description = VALUES(description),
  setting_type = VALUES(setting_type),
  setting_group = VALUES(setting_group),
  is_required = VALUES(is_required),
  validation_rules = VALUES(validation_rules),
  sort_order = VALUES(sort_order);
