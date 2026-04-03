-- Module Hub GMB

CREATE TABLE IF NOT EXISTS gmb_fiche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gmb_location_id VARCHAR(200),
    gmb_account_id VARCHAR(200),
    nom_etablissement VARCHAR(200),
    categorie VARCHAR(200),
    adresse VARCHAR(500),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    telephone VARCHAR(30),
    site_web VARCHAR(500),
    description TEXT,
    horaires JSON,
    photos JSON,
    statut ENUM('actif','suspendu','non_verifie') DEFAULT 'non_verifie',
    last_sync DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user (user_id)
);

CREATE TABLE IF NOT EXISTS gmb_avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gmb_review_id VARCHAR(200) UNIQUE,
    auteur VARCHAR(200),
    photo_auteur VARCHAR(500),
    note TINYINT NOT NULL,
    commentaire TEXT,
    reponse TEXT DEFAULT NULL,
    reponse_at DATETIME DEFAULT NULL,
    avis_at DATETIME,
    statut ENUM('nouveau','lu','repondu') DEFAULT 'nouveau',
    sentiment ENUM('positif','neutre','negatif') DEFAULT 'neutre',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut),
    INDEX idx_note (note)
);

CREATE TABLE IF NOT EXISTS gmb_demandes_avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    client_nom VARCHAR(200),
    client_email VARCHAR(200),
    client_tel VARCHAR(30),
    bien_adresse VARCHAR(300),
    canal ENUM('email','sms','both') DEFAULT 'email',
    template_id INT DEFAULT NULL,
    statut ENUM('en_attente','envoye','ouvert','clique','avis_laisse') DEFAULT 'en_attente',
    envoye_at DATETIME DEFAULT NULL,
    relance_at DATETIME DEFAULT NULL,
    nb_relances TINYINT DEFAULT 0,
    token VARCHAR(64) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut)
);

CREATE TABLE IF NOT EXISTS gmb_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(200),
    canal ENUM('email','sms') NOT NULL,
    sujet VARCHAR(300),
    contenu TEXT NOT NULL,
    actif TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    UNIQUE KEY uk_user_nom_canal (user_id, nom, canal)
);

CREATE TABLE IF NOT EXISTS gmb_statistiques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_stat DATE NOT NULL,
    impressions INT DEFAULT 0,
    clics_site INT DEFAULT 0,
    appels INT DEFAULT 0,
    itineraires INT DEFAULT 0,
    photos_vues INT DEFAULT 0,
    recherches_dir INT DEFAULT 0,
    recherches_disc INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_date (user_id, date_stat),
    INDEX idx_user (user_id)
);

INSERT INTO gmb_templates (user_id, nom, canal, sujet, contenu)
VALUES
(1, 'Demande avis post-vente Email', 'email', 'Votre avis compte beaucoup pour {advisor_firstname} !',
'Bonjour {client_nom},\n\nMerci de m\'avoir fait confiance pour {bien_adresse}.\n\nVotre satisfaction est ma priorité.\nPourriez-vous laisser un avis Google ?\nCela prend moins de 2 minutes.\n\n→ {lien_avis}\n\nMerci infiniment,\n{advisor_firstname} {advisor_lastname}\n{advisor_phone}'),
(1, 'Demande avis SMS', 'sms', NULL,
'Bonjour {client_nom}, merci pour votre confiance !\nLaissez-moi un avis Google : {lien_avis}\n— {advisor_firstname}')
ON DUPLICATE KEY UPDATE contenu = VALUES(contenu), sujet = VALUES(sujet), actif = 1;
