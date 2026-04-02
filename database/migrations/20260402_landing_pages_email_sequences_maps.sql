-- ═══════════════════════════════════════════════════════════════
-- MIGRATION 004 — Landing Pages + Email Sequences + Maps
-- ═══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── LANDING PAGES ─────────────────────────────────────────────────
CREATE TABLE `landing_pages` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `slug`              VARCHAR(300)    NOT NULL UNIQUE,
    `name`              VARCHAR(300)    NOT NULL COMMENT 'Nom interne',
    `title`             VARCHAR(500)    NOT NULL COMMENT 'Titre visible',
    `subtitle`          TEXT            NULL,
    `source`            ENUM(
                          'google_ads',
                          'facebook_ads',
                          'instagram_ads',
                          'email',
                          'organic',
                          'other'
                        )               NOT NULL DEFAULT 'google_ads',
    `template`          VARCHAR(100)    NOT NULL DEFAULT 'classic',
    `status`            ENUM('draft','published','archived')
                                        NOT NULL DEFAULT 'draft',

    -- Contenu
    `hero_image`        VARCHAR(500)    NULL,
    `headline`          VARCHAR(500)    NULL,
    `subheadline`       TEXT            NULL,
    `body_content`      LONGTEXT        NULL COMMENT 'JSON blocks',
    `benefits`          JSON            NULL COMMENT '[{icon,title,text}]',
    `social_proof`      JSON            NULL COMMENT '[{name,text,photo,rating}]',
    `cta_text`          VARCHAR(200)    NOT NULL DEFAULT 'Télécharger gratuitement',
    `cta_color`         VARCHAR(7)      NOT NULL DEFAULT '#2563EB',

    -- Ressource liée
    `resource_id`       INT UNSIGNED    NULL,
    `resource_title`    VARCHAR(300)    NULL,
    `resource_image`    VARCHAR(500)    NULL,

    -- Page de remerciement
    `thankyou_slug`     VARCHAR(300)    NULL,
    `thankyou_title`    VARCHAR(300)    NULL,
    `thankyou_message`  TEXT            NULL,
    `thankyou_redirect` VARCHAR(500)    NULL COMMENT 'URL redirect après Xs',
    `thankyou_redirect_delay` TINYINT   NULL DEFAULT 0,

    -- Séquence email liée
    `sequence_id`       INT UNSIGNED    NULL,

    -- Tracking
    `gtm_container_id`  VARCHAR(50)     NULL,
    `fb_pixel_id`       VARCHAR(100)    NULL,
    `fb_event`          VARCHAR(100)    NULL DEFAULT 'Lead',
    `google_ads_label`  VARCHAR(200)    NULL,
    `custom_head_code`  TEXT            NULL,
    `custom_body_code`  TEXT            NULL,

    -- SEO (minimal pour landing)
    `seo_title`         VARCHAR(70)     NULL,
    `seo_description`   VARCHAR(160)    NULL,
    `seo_noindex`       TINYINT(1)      NOT NULL DEFAULT 1,

    -- Stats
    `views_count`       INT             NOT NULL DEFAULT 0,
    `leads_count`       INT             NOT NULL DEFAULT 0,

    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`) ON DELETE SET NULL,
    INDEX `idx_source_status` (`source`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LEADS LANDING PAGES ───────────────────────────────────────────
CREATE TABLE `landing_leads` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `landing_id`      INT UNSIGNED    NOT NULL,
    `contact_id`      INT UNSIGNED    NULL,
    `first_name`      VARCHAR(100)    NOT NULL,
    `last_name`       VARCHAR(100)    NULL,
    `email`           VARCHAR(255)    NOT NULL,
    `phone`           VARCHAR(20)     NULL,
    `city`            VARCHAR(100)    NULL,
    `project_type`    VARCHAR(100)    NULL,
    `custom_fields`   JSON            NULL,
    `gdpr_consent`    TINYINT(1)      NOT NULL DEFAULT 0,
    `gdpr_date`       DATETIME        NOT NULL,
    `source`          VARCHAR(100)    NULL,
    `utm_source`      VARCHAR(200)    NULL,
    `utm_medium`      VARCHAR(200)    NULL,
    `utm_campaign`    VARCHAR(200)    NULL,
    `utm_content`     VARCHAR(200)    NULL,
    `utm_term`        VARCHAR(200)    NULL,
    `gclid`           VARCHAR(200)    NULL COMMENT 'Google Click ID',
    `fbclid`          VARCHAR(200)    NULL COMMENT 'Facebook Click ID',
    `ip_hash`         VARCHAR(64)     NULL,
    `user_agent`      VARCHAR(500)    NULL,
    `sequence_enrolled` TINYINT(1)    NOT NULL DEFAULT 0,
    `sequence_step`   TINYINT         NOT NULL DEFAULT 0,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`landing_id`)  REFERENCES `landing_pages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`)  REFERENCES `crm_contacts`(`id`) ON DELETE SET NULL,
    INDEX `idx_landing_id` (`landing_id`),
    INDEX `idx_email`      (`email`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CAMPAGNES EMAIL ───────────────────────────────────────────────
CREATE TABLE `email_campaigns` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(300)    NOT NULL,
    `type`          ENUM('sequence','broadcast','transactional')
                                    NOT NULL DEFAULT 'sequence',
    `status`        ENUM('draft','active','paused','archived')
                                    NOT NULL DEFAULT 'draft',
    `from_name`     VARCHAR(200)    NOT NULL,
    `from_email`    VARCHAR(255)    NOT NULL,
    `reply_to`      VARCHAR(255)    NULL,
    `subject_prefix` VARCHAR(100)   NULL COMMENT 'Préfixe objet ex: [Guide Gratuit]',
    `description`   TEXT            NULL,
    `trigger`       ENUM(
                      'landing_optin',
                      'manual',
                      'contact_created',
                      'estimation_done',
                      'tag_added'
                    )               NOT NULL DEFAULT 'landing_optin',
    `trigger_data`  JSON            NULL,
    `total_steps`   TINYINT         NOT NULL DEFAULT 0,
    `subscribers_count` INT         NOT NULL DEFAULT 0,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── EMAILS DE SÉQUENCE ────────────────────────────────────────────
CREATE TABLE `email_sequence_steps` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `campaign_id`   INT UNSIGNED    NOT NULL,
    `step_number`   TINYINT         NOT NULL,
    `name`          VARCHAR(300)    NOT NULL COMMENT 'Nom interne',
    `subject`       VARCHAR(500)    NOT NULL,
    `preview_text`  VARCHAR(300)    NULL,
    `body_html`     LONGTEXT        NOT NULL,
    `body_text`     LONGTEXT        NULL COMMENT 'Version texte',
    `delay_days`    TINYINT         NOT NULL DEFAULT 0
                                    COMMENT 'Délai en jours depuis étape précédente',
    `delay_hours`   TINYINT         NOT NULL DEFAULT 9
                                    COMMENT 'Heure d envoi (9 = 9h00)',
    `condition`     JSON            NULL
                                    COMMENT 'Conditions envoi {"opened_prev":true}',
    `utm_campaign`  VARCHAR(200)    NULL,
    `utm_content`   VARCHAR(200)    NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `sends_count`   INT             NOT NULL DEFAULT 0,
    `opens_count`   INT             NOT NULL DEFAULT 0,
    `clicks_count`  INT             NOT NULL DEFAULT 0,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_campaign_step` (`campaign_id`, `step_number`),
    INDEX `idx_campaign_active` (`campaign_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ENVOIS EMAILS ─────────────────────────────────────────────────
CREATE TABLE `email_sends` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id`   INT UNSIGNED    NOT NULL,
    `step_id`       INT UNSIGNED    NULL,
    `lead_id`       INT UNSIGNED    NULL,
    `contact_id`    INT UNSIGNED    NULL,
    `email`         VARCHAR(255)    NOT NULL,
    `subject`       VARCHAR(500)    NOT NULL,
    `status`        ENUM(
                      'pending','sent','delivered',
                      'opened','clicked','bounced',
                      'unsubscribed','failed','spam'
                    )               NOT NULL DEFAULT 'pending',
    `message_id`    VARCHAR(300)    NULL COMMENT 'ID SMTP',
    `opened_at`     DATETIME        NULL,
    `clicked_at`    DATETIME        NULL,
    `bounced_at`    DATETIME        NULL,
    `click_url`     VARCHAR(500)    NULL,
    `error_message` TEXT            NULL,
    `scheduled_at`  DATETIME        NULL,
    `sent_at`       DATETIME        NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`step_id`)     REFERENCES `email_sequence_steps`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`lead_id`)     REFERENCES `landing_leads`(`id`) ON DELETE SET NULL,
    INDEX `idx_status`        (`status`),
    INDEX `idx_scheduled_at`  (`scheduled_at`),
    INDEX `idx_email`         (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── GOOGLE PLACES CACHE ───────────────────────────────────────────
CREATE TABLE `google_places_cache` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `place_id`      VARCHAR(200)    NOT NULL UNIQUE,
    `query`         VARCHAR(300)    NULL,
    `name`          VARCHAR(300)    NULL,
    `address`       TEXT            NULL,
    `lat`           DECIMAL(10,8)   NULL,
    `lng`           DECIMAL(11,8)   NULL,
    `rating`        DECIMAL(3,1)    NULL,
    `reviews_count` INT             NULL,
    `phone`         VARCHAR(50)     NULL,
    `website`       VARCHAR(500)    NULL,
    `hours`         JSON            NULL,
    `photos`        JSON            NULL,
    `types`         JSON            NULL,
    `raw_data`      JSON            NULL,
    `cached_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`    DATETIME        NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SMTP SETTINGS ─────────────────────────────────────────────────
-- Stocké dans `settings` avec group='smtp'
-- smtp.host | smtp.port | smtp.user | smtp.pass | smtp.encryption

SET FOREIGN_KEY_CHECKS = 1;
