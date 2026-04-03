-- ============================================================
-- DONNÉES PAR DÉFAUT
-- ============================================================
INSERT INTO settings (user_id, `key`, `value`, `group`) VALUES

-- Profil
(1, 'profil_nom',            'Eduardo De Sul',                          'profil'),
(1, 'profil_prenom',         'Eduardo',                                 'profil'),
(1, 'profil_email',          'contact@eduardo-desul-immobilier.fr',     'profil'),
(1, 'profil_telephone',      '',                                        'profil'),
(1, 'profil_ville',          'Bordeaux',                                'profil'),
(1, 'profil_bio',            '',                                        'profil'),
(1, 'profil_photo',          '',                                        'profil'),
(1, 'profil_carte_pro',      '',                                        'profil'),
(1, 'profil_reseau',         '',                                        'profil'),
(1, 'profil_agence',         '',                                        'profil'),
(1, 'profil_siret',          '',                                        'profil'),

-- Site public
(1, 'site_nom',              'Eduardo Desul Immobilier',                'site'),
(1, 'site_url',              'https://eduardo-desul-immobilier.fr',     'site'),
(1, 'site_slogan',           '',                                        'site'),
(1, 'site_description',      '',                                        'site'),
(1, 'site_logo',             '',                                        'site'),
(1, 'site_couleur_primaire', '#3498db',                                 'site'),
(1, 'site_favicon',          '',                                        'site'),

-- Zone géographique
(1, 'zone_ville',            'Bordeaux',                                'zone'),
(1, 'zone_departement',      'Gironde',                                 'zone'),
(1, 'zone_region',           'Nouvelle-Aquitaine',                      'zone'),
(1, 'zone_communes',         '',                                        'zone'),
(1, 'zone_rayon_km',         '30',                                      'zone'),
(1, 'zone_lat',              '44.8378',                                 'zone'),
(1, 'zone_lng',              '-0.5792',                                 'zone'),

-- Clés API
(1, 'api_openai',            '',                                        'api'),
(1, 'api_google_maps',       '',                                        'api'),
(1, 'api_google_psi',        '',                                        'api'),
(1, 'api_gsc',               '',                                        'api'),
(1, 'api_gmb_client_id',     '',                                        'api'),
(1, 'api_gmb_client_secret', '',                                        'api'),
(1, 'api_gmb_account_id',    '',                                        'api'),
(1, 'api_fb_page_id',        '',                                        'api'),
(1, 'api_fb_access_token',   '',                                        'api'),
(1, 'api_instagram_id',      '',                                        'api'),
(1, 'api_cloudinary_name',   '',                                        'api'),
(1, 'api_cloudinary_key',    '',                                        'api'),
(1, 'api_cloudinary_secret', '',                                        'api'),

-- Notifications
(1, 'notif_email_contact',   '1',                                       'notif'),
(1, 'notif_email_estimation','1',                                       'notif'),
(1, 'notif_email_avis',      '1',                                       'notif'),
(1, 'notif_email_alerte',    '0',                                       'notif'),
(1, 'notif_resume_hebdo',    '1',                                       'notif'),
(1, 'notif_email_dest',      '',                                        'notif'),

-- SMTP
(1, 'smtp_host',             '',                                        'smtp'),
(1, 'smtp_port',             '587',                                     'smtp'),
(1, 'smtp_user',             '',                                        'smtp'),
(1, 'smtp_pass',             '',                                        'smtp'),
(1, 'smtp_from_name',        '',                                        'smtp'),
(1, 'smtp_secure',           'tls',                                     'smtp'),

-- Sécurité
(1, 'sec_2fa_active',        '0',                                       'securite'),
(1, 'sec_session_ttl',       '480',                                     'securite'),
(1, 'sec_ip_whitelist',      '',                                        'securite')

ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
