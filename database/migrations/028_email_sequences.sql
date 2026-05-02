-- ============================================================
-- Email Sequences Management
-- ============================================================

CREATE TABLE IF NOT EXISTS email_sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    objective VARCHAR(255) NOT NULL COMMENT 'e.g., "Vendre rapide", "Location longue durée"',
    persona VARCHAR(255) NOT NULL COMMENT 'e.g., "Propriétaire occupant", "Investisseur"',
    city VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
    trigger_type ENUM('manual', 'automatic') DEFAULT 'manual',
    form_trigger VARCHAR(100) COMMENT 'Form name that triggers sequence (e.g., "estimation-gratuite")',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_sequence_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sequence_id INT NOT NULL,
    email_number INT NOT NULL COMMENT '1-5',
    subject VARCHAR(255) NOT NULL,
    body_html LONGTEXT NOT NULL,
    preview_text VARCHAR(255),
    delay_days INT DEFAULT 0 COMMENT 'Days after previous email (0 = immediately)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sequence_number (sequence_id, email_number),
    INDEX idx_sequence_id (sequence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_sequence_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sequence_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    status ENUM('pending', 'active', 'completed', 'unsubscribed') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    current_email_number INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE,
    INDEX idx_sequence_id (sequence_id),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_sequence_sends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    email_number INT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    bounced BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (subscription_id) REFERENCES email_sequence_subscriptions(id) ON DELETE CASCADE,
    INDEX idx_subscription_id (subscription_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
