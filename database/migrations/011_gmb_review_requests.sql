-- Tracking des envois de demandes d'avis Google par email

CREATE TABLE IF NOT EXISTS gmb_review_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    demande_id INT NOT NULL,
    email VARCHAR(200) NOT NULL,
    statut ENUM('en_attente','envoye','echec') DEFAULT 'en_attente',
    date_envoi DATETIME DEFAULT NULL,
    error_message VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_demande (demande_id),
    INDEX idx_statut (statut),
    CONSTRAINT fk_gmb_review_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_gmb_review_requests_demande FOREIGN KEY (demande_id) REFERENCES gmb_demandes_avis(id) ON DELETE CASCADE
);
