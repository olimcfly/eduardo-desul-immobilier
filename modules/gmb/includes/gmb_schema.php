<?php

declare(strict_types=1);

/**
 * Crée les tables du module GMB si absentes.
 * ENUM en guillemets simples SQL (compatible sql_mode ANSI_QUOTES).
 * Appeler avant toute requête sur ces tables.
 */
function gmb_ensure_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_fiche (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        gmb_location_id VARCHAR(200) DEFAULT NULL,
        gmb_account_id VARCHAR(200) DEFAULT NULL,
        nom_etablissement VARCHAR(200) DEFAULT NULL,
        categorie VARCHAR(200) DEFAULT NULL,
        adresse VARCHAR(500) DEFAULT NULL,
        ville VARCHAR(100) DEFAULT NULL,
        code_postal VARCHAR(10) DEFAULT NULL,
        telephone VARCHAR(30) DEFAULT NULL,
        site_web VARCHAR(500) DEFAULT NULL,
        description TEXT,
        horaires JSON DEFAULT NULL,
        photos JSON DEFAULT NULL,
        statut ENUM('actif','suspendu','non_verifie') DEFAULT 'non_verifie',
        last_sync DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_avis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        gmb_review_id VARCHAR(200) NOT NULL,
        auteur VARCHAR(200) DEFAULT NULL,
        photo_auteur VARCHAR(500) DEFAULT NULL,
        note TINYINT NOT NULL,
        commentaire TEXT,
        reponse TEXT DEFAULT NULL,
        reponse_at DATETIME DEFAULT NULL,
        avis_at DATETIME DEFAULT NULL,
        statut ENUM('nouveau','lu','repondu') DEFAULT 'nouveau',
        sentiment ENUM('positif','neutre','negatif') DEFAULT 'neutre',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_gmb_review (gmb_review_id),
        INDEX idx_user (user_id),
        INDEX idx_statut (statut),
        INDEX idx_note (note)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_demandes_avis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        client_nom VARCHAR(200) DEFAULT NULL,
        client_email VARCHAR(200) DEFAULT NULL,
        client_tel VARCHAR(30) DEFAULT NULL,
        bien_adresse VARCHAR(300) DEFAULT NULL,
        canal ENUM('email','sms','both') DEFAULT 'email',
        template_id INT DEFAULT NULL,
        statut ENUM('en_attente','envoye','ouvert','clique','avis_laisse') DEFAULT 'en_attente',
        envoye_at DATETIME DEFAULT NULL,
        relance_at DATETIME DEFAULT NULL,
        nb_relances TINYINT DEFAULT 0,
        token VARCHAR(64) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_token (token),
        INDEX idx_user (user_id),
        INDEX idx_statut (statut)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nom VARCHAR(200) DEFAULT NULL,
        canal ENUM('email','sms') NOT NULL,
        sujet VARCHAR(300) DEFAULT NULL,
        contenu TEXT NOT NULL,
        actif TINYINT(1) DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        UNIQUE KEY uk_user_nom_canal (user_id, nom, canal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_statistiques (
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
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_user_date (user_id, date_stat),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_review_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        demande_id INT NOT NULL,
        email VARCHAR(200) NOT NULL,
        statut ENUM('en_attente','envoye','echec') DEFAULT 'en_attente',
        date_envoi DATETIME DEFAULT NULL,
        error_message VARCHAR(255) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_demande (demande_id),
        INDEX idx_statut (statut)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS gmb_sync_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        status ENUM('pending','running','done','error') NOT NULL DEFAULT 'pending',
        source VARCHAR(50) NOT NULL DEFAULT 'manual',
        attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        payload JSON DEFAULT NULL,
        result JSON DEFAULT NULL,
        error_message TEXT DEFAULT NULL,
        started_at DATETIME DEFAULT NULL,
        finished_at DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_created (user_id, created_at),
        INDEX idx_status_created (status, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
