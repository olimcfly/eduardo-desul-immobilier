-- =============================================
-- Migration 013 : Funnels & Tracking
-- =============================================

CREATE TABLE IF NOT EXISTS funnels (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL DEFAULT 1,
    status          ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',

    -- Canal & Template
    canal           ENUM('google_ads','facebook_ads','social','seo','rdv','estimateur') NOT NULL,
    template_id     VARCHAR(50) NOT NULL,

    -- Identité
    name            VARCHAR(150) NOT NULL,
    slug            VARCHAR(150) NOT NULL UNIQUE,

    -- Persona & ciblage
    ville           VARCHAR(100),
    quartier        VARCHAR(100),
    keyword         VARCHAR(150),
    persona         ENUM('acheteur','vendeur','investisseur','primo_accedant','senior') DEFAULT 'vendeur',
    awareness_level ENUM('problem_aware','solution_aware','product_aware','most_aware') DEFAULT 'problem_aware',

    -- Google Ads / UTM
    campaign_name   VARCHAR(150),
    ad_group        VARCHAR(150),
    utm_source      VARCHAR(100),
    utm_medium      VARCHAR(100),
    utm_campaign    VARCHAR(150),
    utm_content     VARCHAR(100),

    -- Contenu LP
    seo_title       VARCHAR(70),
    meta_description VARCHAR(160),
    h1              VARCHAR(120),
    promise         TEXT,
    cta_label       VARCHAR(80),
    cta_secondary   VARCHAR(80),
    body_html       MEDIUMTEXT,
    faq_json        JSON,

    -- Objets liés
    form_type       ENUM('guide','estimation','rdv','contact','simulation') DEFAULT 'guide',
    ressource_id    INT UNSIGNED NULL,
    sequence_id     INT UNSIGNED NULL,
    thankyou_type   ENUM('telechargement','estimation_recue','rdv_confirme','contact_recu') DEFAULT 'telechargement',
    thankyou_config JSON,

    -- SEO
    indexable       TINYINT(1) DEFAULT 0,
    canonical_url   VARCHAR(255),

    -- Timestamps
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at    TIMESTAMP NULL,

    INDEX idx_canal (canal),
    INDEX idx_status (status),
    INDEX idx_ville (ville)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS funnel_events (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    funnel_id   INT UNSIGNED NOT NULL,
    event_type  ENUM('view','submit','download','cta_click') NOT NULL,
    session_id  VARCHAR(64),
    ip_hash     VARCHAR(64),
    utm_source  VARCHAR(100),
    utm_medium  VARCHAR(100),
    utm_campaign VARCHAR(150),
    referrer    VARCHAR(500),
    user_agent  VARCHAR(300),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_funnel_event (funnel_id, event_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Ajout champs UTM + funnel_id dans crm_leads
ALTER TABLE crm_leads
    ADD COLUMN IF NOT EXISTS funnel_id    INT UNSIGNED NULL AFTER source_type,
    ADD COLUMN IF NOT EXISTS utm_source   VARCHAR(100) NULL AFTER funnel_id,
    ADD COLUMN IF NOT EXISTS utm_medium   VARCHAR(100) NULL AFTER utm_source,
    ADD COLUMN IF NOT EXISTS utm_campaign VARCHAR(150) NULL AFTER utm_medium,
    ADD COLUMN IF NOT EXISTS utm_content  VARCHAR(100) NULL AFTER utm_campaign,
    ADD COLUMN IF NOT EXISTS utm_keyword  VARCHAR(150) NULL AFTER utm_content;

ALTER TABLE crm_leads ADD INDEX IF NOT EXISTS idx_funnel (funnel_id);
