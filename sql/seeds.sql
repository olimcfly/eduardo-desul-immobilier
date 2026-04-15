-- ============================================================
-- DONNÉES PAR DÉFAUT
-- ============================================================
INSERT INTO settings (user_id, `key`, `value`, `group`) VALUES

-- Profil
(1, 'profil_nom',            'Pascal Hamm',                          'profil'),
(1, 'profil_prenom',         'Pascal',                                 'profil'),
(1, 'profil_email',          'contact@pascal-hamm-immobilier.fr',     'profil'),
(1, 'profil_telephone',      '',                                        'profil'),
(1, 'profil_ville',          'Aix-en-Provence',                                'profil'),
(1, 'profil_bio',            '',                                        'profil'),
(1, 'profil_photo',          '',                                        'profil'),
(1, 'profil_carte_pro',      '',                                        'profil'),
(1, 'profil_reseau',         '',                                        'profil'),
(1, 'profil_agence',         '',                                        'profil'),
(1, 'profil_siret',          '',                                        'profil'),

-- Site public
(1, 'site_nom',              'Pascal Hamm Immobilier',                'site'),
(1, 'site_url',              'https://pascal-hamm-immobilier.fr',     'site'),
(1, 'site_slogan',           '',                                        'site'),
(1, 'site_description',      '',                                        'site'),
(1, 'site_logo',             '',                                        'site'),
(1, 'site_couleur_primaire', '#3498db',                                 'site'),
(1, 'site_favicon',          '',                                        'site'),

-- Zone géographique
(1, 'zone_ville',            'Aix-en-Provence',                                'zone'),
(1, 'zone_departement',      'Bouches-du-Rhône',                        'zone'),
(1, 'zone_region',           'Provence-Alpes-Côte d'Azur',             'zone'),
(1, 'zone_communes',         '',                                        'zone'),
(1, 'zone_rayon_km',         '30',                                      'zone'),
(1, 'zone_lat',              '43.5297',                                 'zone'),
(1, 'zone_lng',              '5.4474',                                  'zone'),

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
(1, 'smtp_host',             'mail.pascal-hamm-immobilier-aix-en-provence.fr', 'smtp'),
(1, 'smtp_port',             '465',                                     'smtp'),
(1, 'smtp_user',             'contact@pascal-hamm-immobilier-aix-en-provence.fr', 'smtp'),
(1, 'smtp_pass',             '',                                        'smtp'),
(1, 'smtp_from_name',        '',                                        'smtp'),
(1, 'smtp_secure',           'ssl',                                     'smtp'),

-- Sécurité
(1, 'sec_2fa_active',        '0',                                       'securite'),
(1, 'sec_session_ttl',       '480',                                     'securite'),
(1, 'sec_ip_whitelist',      '',                                        'securite')

ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
