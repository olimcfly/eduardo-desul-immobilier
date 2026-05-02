-- ============================================================
-- Add Destination Links to Email Sequences
-- ============================================================

ALTER TABLE email_sequences ADD COLUMN destination_type ENUM('article', 'guide', 'rdv') DEFAULT NULL COMMENT 'Type of destination link' AFTER form_trigger;
ALTER TABLE email_sequences ADD COLUMN destination_url VARCHAR(500) COMMENT 'URL to article, guide download, or RDV booking' AFTER destination_type;
ALTER TABLE email_sequences ADD COLUMN destination_label VARCHAR(100) COMMENT 'Button label for CTA (e.g., "Lire l\'article", "Télécharger", "Prendre RDV")' AFTER destination_url;
ALTER TABLE email_sequences ADD COLUMN destination_contact_type VARCHAR(100) COMMENT 'Only show for specific contact type (e.g., "prospect-jamais-contacte")' AFTER destination_label;
