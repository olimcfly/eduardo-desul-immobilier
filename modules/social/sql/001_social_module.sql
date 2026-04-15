-- Module Social CRM Immobilier
-- Compatible MySQL 8+

CREATE TABLE IF NOT EXISTS social_posts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    titre           VARCHAR(300),
    contenu         TEXT NOT NULL,
    contenu_fb      TEXT,
    contenu_ig      TEXT,
    contenu_li      TEXT,
    medias          JSON,
    reseaux         JSON NOT NULL,
    type_post       ENUM('post','reel','story','carrousel','article','event') DEFAULT 'post',
    statut          ENUM('brouillon','planifie','publie','erreur','archive') DEFAULT 'brouillon',
    planifie_at     DATETIME DEFAULT NULL,
    publie_at       DATETIME DEFAULT NULL,
    fb_post_id      VARCHAR(200) DEFAULT NULL,
    ig_post_id      VARCHAR(200) DEFAULT NULL,
    li_post_id      VARCHAR(200) DEFAULT NULL,
    fb_likes        INT DEFAULT 0,
    fb_comments     INT DEFAULT 0,
    fb_shares       INT DEFAULT 0,
    ig_likes        INT DEFAULT 0,
    ig_comments     INT DEFAULT 0,
    ig_reach        INT DEFAULT 0,
    li_likes        INT DEFAULT 0,
    li_comments     INT DEFAULT 0,
    li_impressions  INT DEFAULT 0,
    tags            JSON,
    bien_id         INT DEFAULT NULL,
    categorie       ENUM('bien','conseil','marche','temoignage','equipe','autre') DEFAULT 'autre',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut),
    INDEX idx_planifie (planifie_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_medias (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    post_id       INT DEFAULT NULL,
    nom_fichier   VARCHAR(300),
    chemin        VARCHAR(500),
    type          ENUM('image','video','gif') DEFAULT 'image',
    taille        INT,
    largeur       INT DEFAULT NULL,
    hauteur       INT DEFAULT NULL,
    alt_text      VARCHAR(500),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_templates (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    nom           VARCHAR(200),
    reseau        ENUM('facebook','instagram','linkedin','all') DEFAULT 'all',
    categorie     ENUM('bien','conseil','marche','temoignage','equipe','autre'),
    contenu       TEXT NOT NULL,
    variables     JSON,
    actif         TINYINT(1) DEFAULT 1,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_stats (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    reseau        ENUM('facebook','instagram','linkedin'),
    date_stat     DATE NOT NULL,
    abonnes       INT DEFAULT 0,
    impressions   INT DEFAULT 0,
    reach         INT DEFAULT 0,
    engagements   INT DEFAULT 0,
    clics         INT DEFAULT 0,
    posts_count   INT DEFAULT 0,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_reseau_date (user_id, reseau, date_stat),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_hashtags (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    hashtag       VARCHAR(100),
    reseau        ENUM('instagram','facebook','linkedin','all') DEFAULT 'all',
    categorie     VARCHAR(100),
    nb_uses       INT DEFAULT 0,
    actif         TINYINT(1) DEFAULT 1,
    INDEX idx_user (user_id),
    INDEX idx_reseau (reseau)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO social_templates (user_id, nom, reseau, categorie, contenu)
SELECT 1, 'Annonce bien Facebook', 'facebook', 'bien',
'🏡 Nouvelle exclusivité à {ville} !\n\n{type_bien} de {surface}m² — {prix}€\n\n✅ {point_fort_1}\n✅ {point_fort_2}\n✅ {point_fort_3}\n\n📍 {quartier}\n📞 {advisor_phone}\n\n{hashtags}'
WHERE NOT EXISTS (SELECT 1 FROM social_templates WHERE user_id = 1 AND nom = 'Annonce bien Facebook');

INSERT INTO social_templates (user_id, nom, reseau, categorie, contenu)
SELECT 1, 'Post marché mensuel LinkedIn', 'linkedin', 'marche',
'📊 Le marché immobilier à {ville} en {mois} {annee}\n\n🔼 Prix moyen : {prix_m2}€/m²\n📈 Tendance : {tendance}\n⏱ Délai de vente moyen : {delai_vente} jours\n\n{analyse_personnelle}\n\n#Immobilier #{ville} #Marché{annee}'
WHERE NOT EXISTS (SELECT 1 FROM social_templates WHERE user_id = 1 AND nom = 'Post marché mensuel LinkedIn');

INSERT INTO social_templates (user_id, nom, reseau, categorie, contenu)
SELECT 1, 'Story Instagram bien', 'instagram', 'bien',
'✨ NOUVEAUTÉ ✨\n\n{type_bien} à {ville}\n{surface}m² | {nb_pieces} pièces\n{prix}€\n\nSwipe up pour voir 👆\n{hashtags}'
WHERE NOT EXISTS (SELECT 1 FROM social_templates WHERE user_id = 1 AND nom = 'Story Instagram bien');

INSERT INTO social_templates (user_id, nom, reseau, categorie, contenu)
SELECT 1, 'Témoignage client', 'all', 'temoignage',
'⭐ Ils nous font confiance !\n\n"{temoignage}"\n\n— {prenom_client}, {ville}\n\nMerci pour votre confiance 🙏\n{hashtags}'
WHERE NOT EXISTS (SELECT 1 FROM social_templates WHERE user_id = 1 AND nom = 'Témoignage client');

INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#immobilier', 'all', 'general' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#immobilier');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#aixenprovence', 'all', 'localisation' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#aixenprovence');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#immobilieraixenprovence', 'all', 'local' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#immobilieraixenprovence');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#achatimmo', 'all', 'transaction' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#achatimmo');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#venteimmo', 'all', 'transaction' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#venteimmo');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#conseilimmobilier', 'linkedin', 'conseil' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#conseilimmobilier');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#realestate', 'instagram', 'general' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#realestate');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#bienimmobilier', 'instagram', 'bien' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#bienimmobilier');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#maisonavendre', 'facebook', 'bien' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#maisonavendre');
INSERT INTO social_hashtags (user_id, hashtag, reseau, categorie)
SELECT 1, '#appartementavendre', 'facebook', 'bien' WHERE NOT EXISTS (SELECT 1 FROM social_hashtags WHERE user_id = 1 AND hashtag = '#appartementavendre');
