-- ============================================================
-- MIGRATION 004 — Centralisation des settings utilisateur
-- ============================================================

SET NAMES utf8mb4;

-- Sauvegarde table legacy si présente
CREATE TABLE IF NOT EXISTS settings_legacy LIKE settings;
INSERT IGNORE INTO settings_legacy SELECT * FROM settings;

DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value LONGTEXT,
  setting_type ENUM(
    'text',
    'textarea',
    'email',
    'tel',
    'url',
    'number',
    'boolean',
    'select',
    'color',
    'image',
    'json',
    'password'
  ) DEFAULT 'text',
  setting_group VARCHAR(50),
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_setting (user_id, setting_key),
  KEY idx_settings_user_group (user_id, setting_group),
  CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  default_value TEXT,
  label VARCHAR(255),
  description TEXT,
  setting_type VARCHAR(50),
  setting_group VARCHAR(50),
  is_required TINYINT(1) DEFAULT 0,
  validation_rules VARCHAR(500),
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(100),
  old_value TEXT,
  new_value TEXT,
  changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  KEY idx_settings_history_user (user_id),
  CONSTRAINT fk_settings_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings_templates
(setting_key, default_value, label, description, setting_type, setting_group, is_required, validation_rules, sort_order)
VALUES
('advisor_firstname','','Prénom','Votre prénom affiché partout dans le CRM','text','conseiller',1,'max:50',1),
('advisor_lastname','','Nom','Votre nom de famille','text','conseiller',1,'max:50',2),
('advisor_photo','','Photo de profil','URL ou chemin de votre photo','image','conseiller',0,'',3),
('advisor_title','Conseiller Immobilier','Titre professionnel','Ex: Conseiller Immobilier Senior','text','conseiller',0,'max:100',4),
('advisor_phone','','Téléphone','Numéro affiché aux clients','tel','conseiller',1,'',5),
('advisor_email','','Email professionnel','Email de contact','email','conseiller',1,'',6),
('advisor_tagline','','Accroche personnelle','Votre slogan personnel (max 100 caractères)','text','conseiller',0,'max:100',7),
('advisor_bio','','Biographie','Présentation complète pour le site vitrine','textarea','conseiller',0,'max:2000',8),
('advisor_signature','','Signature email','Signature automatique dans vos emails','textarea','conseiller',0,'',9),
('advisor_years_experience','0','Années d''expérience','','number','conseiller',0,'min:0|max:50',10),
('advisor_sales_count','0','Nombre de ventes réalisées','Vos ventes pour crédibiliser votre pitch','number','conseiller',0,'min:0',11),
('advisor_rsac','','Numéro RSAC','Numéro de carte professionnelle','text','conseiller',0,'',12),
('advisor_linkedin','','LinkedIn','URL profil LinkedIn','url','conseiller',0,'',13),
('agency_name','','Nom de l''agence','Ex: Agence Dupont Immobilier','text','agence',0,'max:100',1),
('agency_logo','','Logo agence','URL ou chemin du logo','image','agence',0,'',2),
('agency_network','','Réseau immobilier','Ex: IAD, Century 21, Orpi, Indépendant','text','agence',0,'max:100',3),
('agency_address','','Adresse','Adresse complète de l''agence','textarea','agence',0,'',4),
('agency_phone','','Téléphone agence','','tel','agence',0,'',5),
('agency_email','','Email agence','','email','agence',0,'',6),
('agency_website','','Site web agence','','url','agence',0,'',7),
('agency_siret','','SIRET','Numéro SIRET de l''agence','text','agence',0,'',8),
('agency_color_primary','#3b82f6','Couleur principale','Couleur de votre charte graphique','color','agence',0,'',9),
('agency_color_secondary','#1a2332','Couleur secondaire','','color','agence',0,'',10),
('agency_opening_hours','','Horaires d''ouverture','JSON des horaires par jour','json','agence',0,'',11),
('zone_city','','Ville principale','Votre ville de prospection principale','text','zone',1,'max:100',1),
('zone_postal_code','','Code postal','','text','zone',1,'max:10',2),
('zone_department','','Département','Ex: Rhône, Bouches-du-Rhône','text','zone',0,'max:50',3),
('zone_region','','Région','Ex: Auvergne-Rhône-Alpes','text','zone',0,'max:50',4),
('zone_neighborhoods','[]','Quartiers/Arrondissements','JSON array des zones couvertes','json','zone',0,'',5),
('zone_radius_km','15','Rayon de prospection (km)','Rayon autour de votre ville principale','number','zone',0,'min:1|max:100',6),
('zone_price_per_m2','0','Prix moyen au m²','Prix moyen dans votre zone (€)','number','zone',0,'min:0',7),
('zone_avg_sale_days','90','Délai moyen de vente (jours)','','number','zone',0,'min:1',8),
('zone_market_type','EQUILIBRE','Type de marché','','select','zone',0,'TENDU,EQUILIBRE,DETENTE',9),
('zone_competition','MOYEN','Niveau de concurrence','','select','zone',0,'FAIBLE,MOYEN,FORT',10),
('business_specialties','[]','Spécialités','JSON array: appartements,maisons,terrain,...','json','metier',0,'',1),
('business_commission_rate','5','Taux de commission (%)','Taux moyen pratiqué','number','metier',0,'min:0|max:20',2),
('business_target_mandats','3','Objectif mandats/mois','','number','metier',0,'min:0',3),
('business_target_ca','0','Objectif CA annuel (€)','','number','metier',0,'min:0',4),
('business_style','EXPERT','Style commercial','','select','metier',0,'EXPERT,PROXIMITE,DIGITAL,PREMIUM,CHALLENGER',5),
('business_strengths','[]','Points forts','JSON array','json','metier',0,'',6),
('business_usp','','Proposition de valeur unique','Ce qui vous différencie','textarea','metier',0,'max:500',7),
('tech_app_url','','URL de l''application','URL complète du CRM','url','technique',0,'',1),
('tech_openai_key','','Clé API OpenAI','sk-...','password','technique',0,'',2),
('tech_google_maps_key','','Clé API Google Maps','','password','technique',0,'',3),
('tech_google_analytics','','ID Google Analytics','G-XXXXXXXXXX','text','technique',0,'',4),
('tech_smtp_host','','Serveur SMTP','','text','technique',0,'',5),
('tech_smtp_port','587','Port SMTP','','number','technique',0,'',6),
('tech_smtp_user','','Utilisateur SMTP','','text','technique',0,'',7),
('tech_smtp_pass','','Mot de passe SMTP','','password','technique',0,'',8),
('tech_smtp_from','','Email expéditeur','','email','technique',0,'',9),
('tech_smtp_from_name','','Nom expéditeur','','text','technique',0,'',10),
('tech_timezone','Europe/Paris','Fuseau horaire','','text','technique',0,'',11),
('tech_language','fr','Langue','','select','technique',0,'fr,en',12),
('tech_upload_max_mb','10','Taille max upload (Mo)','','number','technique',0,'min:1|max:100',13),
('notif_email_new_lead','1','Email nouveau lead','Recevoir un email à chaque nouveau lead','boolean','notifications',0,'',1),
('notif_email_new_rdv','1','Email nouveau RDV','','boolean','notifications',0,'',2),
('notif_email_rdv_reminder','1','Rappel RDV (24h avant)','','boolean','notifications',0,'',3),
('notif_email_weekly_report','1','Rapport hebdomadaire','','boolean','notifications',0,'',4),
('notif_email_address','','Email de notification','Si différent de l''email professionnel','email','notifications',0,'',5),
('notif_sms_enabled','0','SMS activés','Nécessite compte Twilio','boolean','notifications',0,'',6),
('notif_sms_number','','Numéro SMS','Votre numéro pour recevoir les SMS','tel','notifications',0,'',7),
('site_subdomain','','Sous-domaine','URL: [subdomain].crm-immo.fr','text','site_vitrine',0,'max:50',1),
('site_custom_domain','','Domaine personnalisé','Ex: jean-dupont-immo.fr','text','site_vitrine',0,'',2),
('site_meta_title','','Meta titre SEO','Max 60 caractères','text','site_vitrine',0,'max:60',3),
('site_meta_description','','Meta description SEO','Max 155 caractères','textarea','site_vitrine',0,'max:155',4),
('site_show_biens','1','Afficher mes biens','','boolean','site_vitrine',0,'',5),
('site_show_estimateur','1','Afficher l''estimateur','','boolean','site_vitrine',0,'',6),
('site_show_blog','0','Afficher le blog','','boolean','site_vitrine',0,'',7),
('site_show_temoignages','1','Afficher les témoignages','','boolean','site_vitrine',0,'',8),
('site_show_chatbot','0','Afficher le chatbot','','boolean','site_vitrine',0,'',9),
('site_published','0','Site publié','','boolean','site_vitrine',0,'',10)
ON DUPLICATE KEY UPDATE
  default_value = VALUES(default_value),
  label = VALUES(label),
  description = VALUES(description),
  setting_type = VALUES(setting_type),
  setting_group = VALUES(setting_group),
  is_required = VALUES(is_required),
  validation_rules = VALUES(validation_rules),
  sort_order = VALUES(sort_order);