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
        'profil_reseau', 'profil_agence', 'profil_siret',
    ],
    'site' => [
        'site_nom', 'site_url', 'site_slogan', 'site_description',
        'site_logo', 'site_couleur_primaire', 'site_favicon',
    ],
    'zone' => [
        'zone_ville', 'zone_departement', 'zone_region',
        'zone_communes', 'zone_rayon_km', 'zone_lat', 'zone_lng',
    ],
    'api' => [
        'api_openai', 'api_google_maps', 'api_google_psi', 'api_gsc',
        'api_gmb_client_id', 'api_gmb_client_secret', 'api_gmb_account_id',
        'api_fb_page_id', 'api_fb_access_token', 'api_instagram_id',
        'api_cloudinary_name', 'api_cloudinary_key', 'api_cloudinary_secret',
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
    if ($key === 'smtp_pass' && empty($_POST[$key])) {
        // Ne pas écraser un mot de passe SMTP vide
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

echo json_encode(
    $ok
        ? ['success' => true,  'message' => 'Paramètres enregistrés.']
        : ['success' => false, 'error'   => 'Erreur lors de la sauvegarde.']
);
