CREATE TABLE IF NOT EXISTS social_sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(190) NOT NULL,
    persona VARCHAR(120) NOT NULL,
    zone VARCHAR(120) DEFAULT NULL,
    statut ENUM('active', 'pause', 'brouillon') DEFAULT 'active',
    objectif VARCHAR(190) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_social_sequences_user (user_id),
    INDEX idx_social_sequences_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sequence_id INT DEFAULT NULL,
    titre VARCHAR(220) DEFAULT NULL,
    contenu TEXT NOT NULL,
    reseaux JSON NOT NULL,
    statut ENUM('brouillon', 'planifie', 'publie', 'erreur') DEFAULT 'brouillon',
    planifie_at DATETIME DEFAULT NULL,
    publie_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_social_posts_user (user_id),
    INDEX idx_social_posts_sequence (sequence_id),
    INDEX idx_social_posts_statut (statut),
    CONSTRAINT fk_social_posts_sequence FOREIGN KEY (sequence_id) REFERENCES social_sequences(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    action VARCHAR(80) NOT NULL,
    payload JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_social_logs_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
