<?php
// ============================================================
// API — Sauvegarde d'une section de paramètres
// ============================================================
require_once $_SERVER['DOCUMENT_ROOT'] . '/../core/bootstrap.php';
Auth::requireAuth();

header('Content-Type: application/json');

$section = preg_replace('/[^a-z_]/', '', $_POST['section'] ?? '');
if (!$section) {
    echo json_encode(['success' => false, 'error' => 'Section manquante.']);
    exit;
}

// ── Whitelist des champs autorisés par section ───────────────
const ALLOWED_FIELDS = [
    'profil' => [
        'profil_prenom', 'profil_nom', 'profil_email', 'profil_telephone',
        'profil_ville', 'profil_bio', 'profil_photo', 'profil_carte_pro',
        'profil_reseau', 'profil_agence', 'profil_siret', 'profil_rsac',
    ],
    'site' => [
        'site_nom', 'site_url', 'site_slogan', 'site_description',
        'site_logo', 'site_couleur_primaire', 'site_favicon',
        'site_home_hero_label', 'site_home_hero_title', 'site_home_hero_subtitle',
        'site_home_cta_primary_label', 'site_home_cta_primary_url',
        'site_home_cta_secondary_label', 'site_home_cta_secondary_url',
        'social_facebook',
    ],
    'zone' => [
        'zone_ville', 'zone_departement', 'zone_region',
        'zone_communes', 'zone_rayon_km', 'zone_lat', 'zone_lng',
    ],
    'api' => [
        'api_openai', 'api_openrouter', 'api_google_maps', 'api_google_psi', 'api_gsc',
        'api_gsc_client_id', 'api_gsc_client_secret', 'api_gsc_site_url',
        'api_gmb_client_id', 'api_gmb_client_secret', 'api_gmb_account_id',
        'api_fb_page_id', 'api_fb_access_token', 'api_instagram_id',
        'api_cloudinary_name', 'api_cloudinary_key', 'api_cloudinary_secret',
        'api_dataforseo_login', 'api_dataforseo_password',
    ],
    'notif' => [
        'notif_email_contact', 'notif_email_estimation',
        'notif_email_avis', 'notif_email_alerte',
        'notif_resume_hebdo', 'notif_email_dest',
    ],
    'smtp' => [
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass',
        'smtp_from', 'smtp_from_name', 'smtp_secure',
    ],
    'telegram' => [
        'telegram_bot_token', 'telegram_webhook_token', 'telegram_admin_ids',
    ],
    'securite' => [
        'sec_2fa_active', 'sec_session_ttl', 'sec_ip_whitelist',
    ],
];

// ── Traitement spécial : changement de mot de passe ─────────
if ($section === 'securite') {
    $actuel  = $_POST['pwd_actuel']  ?? '';
    $nouveau = $_POST['pwd_nouveau'] ?? '';
    $confirm = $_POST['pwd_confirm'] ?? '';

    if ($nouveau !== '') {
        if (strlen($nouveau) < 10) {
            echo json_encode(['success' => false, 'error' => 'Mot de passe trop court (min. 10 caractères).']);
            exit;
        }
        if ($nouveau !== $confirm) {
            echo json_encode(['success' => false, 'error' => 'Les mots de passe ne correspondent pas.']);
            exit;
        }
        $user = Auth::user();
        if (!Auth::verifyPassword($actuel, $user['password'])) {
            echo json_encode(['success' => false, 'error' => 'Mot de passe actuel incorrect.']);
            exit;
        }
        try {
            db()->prepare("UPDATE users SET password = ? WHERE id = ?")
                ->execute([Auth::hashPassword($nouveau), $user['id']]);
        } catch (Throwable $e) {
            error_log('pwd_change error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur lors du changement de mot de passe.']);
            exit;
        }
    }
}

// ── Construire le tableau de données à sauvegarder ──────────
$allowed = ALLOWED_FIELDS[$section] ?? [];
$data    = [];

foreach ($allowed as $key) {
    if (($key === 'smtp_pass' || $key === 'telegram_bot_token' || $key === 'telegram_webhook_token' || $key === 'api_openrouter') && empty($_POST[$key])) {
        // Ne pas écraser un mot de passe/token vide
        continue;
    }
    // Checkboxes non cochées = absentes du POST → valeur '0'
    $isCheckbox = in_array($key, [
        'notif_email_contact', 'notif_email_estimation',
        'notif_email_avis', 'notif_email_alerte',
        'notif_resume_hebdo', 'sec_2fa_active',
    ]);
    $data[$key] = $isCheckbox
        ? (isset($_POST[$key]) ? '1' : '0')
        : ($_POST[$key] ?? '');
}

if (empty($data)) {
    echo json_encode(['success' => false, 'error' => 'Aucune donnée à enregistrer.']);
    exit;
}

$ok = settings_save($data, $section);

if ($ok && $section === 'profil') {
    $profilPrenom = trim((string)($data['profil_prenom'] ?? ''));
    $profilNom = trim((string)($data['profil_nom'] ?? ''));
    $profilPhone = trim((string)($data['profil_telephone'] ?? ''));
    $profilEmail = trim((string)($data['profil_email'] ?? ''));
    $profilRsac = trim((string)($data['profil_rsac'] ?? ''));
    $profilSiret = trim((string)($data['profil_siret'] ?? ''));

    $syncData = [
        'advisor_firstname' => $profilPrenom,
        'advisor_lastname' => $profilNom,
        'advisor_phone' => $profilPhone,
        'advisor_email' => $profilEmail,
        'advisor_rsac' => $profilRsac,
        'advisor_siret' => $profilSiret,
        'contact_phone' => $profilPhone,
        'contact_email' => $profilEmail,
    ];

    settings_save($syncData, 'profil');
}

echo json_encode(
    $ok
        ? ['success' => true,  'message' => 'Paramètres enregistrés.']
        : ['success' => false, 'error'   => 'Erreur lors de la sauvegarde.']
);
