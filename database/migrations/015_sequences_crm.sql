-- =============================================
-- Migration 015 : Séquences CRM Email
-- =============================================

CREATE TABLE IF NOT EXISTS crm_sequences (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    status      ENUM('active','paused') DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS crm_sequence_steps (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id     INT UNSIGNED NOT NULL,
    step_order      TINYINT UNSIGNED NOT NULL,
    delay_days      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    email_subject   VARCHAR(200) NOT NULL,
    email_body_html MEDIUMTEXT NOT NULL,
    cta_label       VARCHAR(80),
    cta_url         VARCHAR(255),
    FOREIGN KEY (sequence_id) REFERENCES crm_sequences(id) ON DELETE CASCADE,
    INDEX idx_sequence (sequence_id, step_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS crm_sequence_enrollments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id     INT UNSIGNED NOT NULL,
    lead_id         INT UNSIGNED NOT NULL,
    current_step    TINYINT UNSIGNED DEFAULT 0,
    status          ENUM('active','completed','unsubscribed','bounced') DEFAULT 'active',
    enrolled_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_send_at    TIMESTAMP NULL,
    FOREIGN KEY (sequence_id) REFERENCES crm_sequences(id),
    UNIQUE KEY uniq_lead_seq (lead_id, sequence_id),
    INDEX idx_next_send (next_send_at, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Séquence par défaut : Vendeur 3 emails
INSERT INTO crm_sequences (name, description, status) VALUES
('Séquence Vendeur — 3 emails', 'J0 guide, J+2 relance, J+5 RDV', 'active');

INSERT INTO crm_sequence_steps (sequence_id, step_order, delay_days, email_subject, email_body_html, cta_label, cta_url) VALUES
(1, 1, 0,
 'Votre guide est prêt — [PRENOM]',
 '<p>Bonjour [PRENOM],</p><p>Merci pour votre intérêt. Voici votre guide gratuit :</p><p><a href="[RESSOURCE_URL]">[CTA_LABEL]</a></p><p>N''hésitez pas si vous avez des questions.<br>[ADVISOR_NAME]</p>',
 'Télécharger mon guide', '[RESSOURCE_URL]'),
(1, 2, 2,
 '[PRENOM], avez-vous eu le temps de lire votre guide ?',
 '<p>Bonjour [PRENOM],</p><p>J''espère que le guide vous a été utile. Pour aller plus loin, je serais ravi(e) de faire le point sur votre projet.</p><p><a href="[RDV_URL]">Prendre rendez-vous</a></p><p>[ADVISOR_NAME]</p>',
 'Prendre rendez-vous', '[RDV_URL]'),
(1, 3, 5,
 'Dernière question, [PRENOM]',
 '<p>Bonjour [PRENOM],</p><p>Je voulais simplement m''assurer que vous aviez toutes les informations pour avancer sereinement dans votre projet immobilier.</p><p>Si vous souhaitez une estimation personnalisée de votre bien, c''est gratuit et sans engagement.</p><p><a href="[RDV_URL]">Réserver mon créneau</a></p><p>[ADVISOR_NAME]</p>',
 'Estimation gratuite', '[RDV_URL]');
