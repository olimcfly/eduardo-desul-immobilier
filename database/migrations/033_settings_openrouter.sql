-- Clé OpenRouter (agents IA / LLM) — Paramètres → Intégrations & API
SET NAMES utf8mb4;

INSERT IGNORE INTO settings_templates
(setting_key, default_value, label, description, setting_type, setting_group, is_required, validation_rules, sort_order)
VALUES
(
    'api_openrouter',
    '',
    'Clé API OpenRouter',
    'Format sk-or-v1-… — multi-modèles via openrouter.ai. Utilisée par le module Agents IA.',
    'password',
    'api',
    0,
    '',
    15
);
