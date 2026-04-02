-- ── TABLE CONTACTS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contacts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advisor_id   INT UNSIGNED NOT NULL,
    first_name   VARCHAR(100) NOT NULL,
    last_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(255) NOT NULL,
    phone        VARCHAR(30),
    source       VARCHAR(80),
    utm_source   VARCHAR(80),
    utm_medium   VARCHAR(80),
    utm_campaign VARCHAR(120),
    gdpr_consent TINYINT(1) DEFAULT 1,
    created_at   DATETIME NOT NULL,
    updated_at   DATETIME NOT NULL,
    UNIQUE KEY uk_email_advisor (email, advisor_id),
    INDEX idx_advisor  (advisor_id),
    INDEX idx_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TABLE ESTIMATION_REQUESTS ─────────────────────────────────
CREATE TABLE IF NOT EXISTS estimation_requests (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id       INT UNSIGNED NOT NULL,
    advisor_id       INT UNSIGNED NOT NULL,

    -- Bien
    property_type    ENUM('apartment','house','villa','land','commercial','parking') NOT NULL,
    surface          DECIMAL(8,2) NOT NULL,
    rooms            VARCHAR(5),
    floor            TINYINT UNSIGNED,
    land_surface     DECIMAL(10,2),
    condition_state  ENUM('new','very_good','good','refresh','renovate') NOT NULL,
    dpe              ENUM('A','B','C','D','E','F','G',''),
    parking          ENUM('none','indoor','outdoor') DEFAULT 'none',

    -- Localisation
    address          VARCHAR(255),
    city             VARCHAR(120),
    postal_code      VARCHAR(10),
    lat              DECIMAL(10,7),
    lng              DECIMAL(10,7),
    more_info        TEXT,

    -- Projet
    project_type     ENUM('sell','estimate_only','rent') DEFAULT 'sell',
    timeline         VARCHAR(80),

    -- Tracking
    source           VARCHAR(80),
    ip_address       VARCHAR(64),
    user_agent       VARCHAR(255),

    -- Statut CRM
    status           ENUM('new','contacted','in_progress','done','cancelled') DEFAULT 'new',

    created_at       DATETIME NOT NULL,
    updated_at       DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_contact  (contact_id),
    INDEX idx_advisor  (advisor_id),
    INDEX idx_status   (status),
    INDEX idx_city     (city),
    INDEX idx_created  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── TABLE LEADS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS leads (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id   INT UNSIGNED NOT NULL,
    advisor_id   INT UNSIGNED NOT NULL,
    type         ENUM('estimation','contact','buyer','callback') NOT NULL,
    reference_id INT UNSIGNED,
    status       ENUM('new','contacted','qualified','lost','won') DEFAULT 'new',
    priority     ENUM('low','normal','high') DEFAULT 'normal',
    notes        TEXT,
    created_at   DATETIME NOT NULL,
    updated_at   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contact (contact_id),
    INDEX idx_advisor (advisor_id),
    INDEX idx_status  (status),
    INDEX idx_type    (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
