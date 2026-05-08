<?php
// ============================================================
// API — Sauvegarde d'une section de paramètres
// ============================================================
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

$allowedFields = [
    'profil' => [
        'profil_prenom', 'profil_nom', 'profil_email', 'profil_telephone',
        'profil_ville', 'profil_address', 'profil_bio', 'profil_photo', 'profil_carte_pro',
        'profil_reseau', 'profil_agence', 'profil_siret', 'profil_rsac',
        'profil_fonction', 'profil_site_principal', 'profil_email_reseau',
        'profil_statut', 'profil_siren', 'profil_ape', 'profil_marque_locale',
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
        'api_perplexity_key',
    ],
    'tracking' => [
        'tracking_head_code', 'tracking_body_code',
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

if (!function_exists('settings_store_profile_photo_upload')) {
    function settings_store_profile_photo_upload(int $userId, array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return '';
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Impossible de lire le fichier photo.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Fichier photo invalide.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > MAX_FILE_SIZE) {
            throw new RuntimeException('La photo dépasse la taille autorisée.');
        }

        $originalName = (string) ($file['name'] ?? 'photo');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMG, true)) {
            throw new RuntimeException('Format de photo non autorisé. Utilisez JPG, PNG ou WebP.');
        }

        if (@getimagesize($tmpName) === false) {
            throw new RuntimeException('Le fichier envoyé n’est pas une image valide.');
        }

        $targetDir = rtrim(PUBLIC_PATH, '/') . '/uploads/profil/' . $userId;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Impossible de créer le dossier de stockage.');
        }

        $fileName = 'profil_' . $userId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $fullPath = $targetDir . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $fullPath)) {
            throw new RuntimeException('La photo n’a pas pu être enregistrée.');
        }

        return '/uploads/profil/' . $userId . '/' . $fileName;
    }
}

if (!function_exists('settings_remove_uploaded_photo')) {
    function settings_remove_uploaded_photo(string $photoPath): void
    {
        $photoPath = trim($photoPath);
        if ($photoPath === '' || !str_starts_with($photoPath, '/uploads/profil/')) {
            return;
        }

        $fullPath = rtrim(PUBLIC_PATH, '/') . $photoPath;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

try {
    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Session expirée. Reconnectez-vous.']);
        exit;
    }

    $user = Auth::user();
    $userId = (int) ($user['id'] ?? 0);

    $section = preg_replace('/[^a-z_]/', '', $_POST['section'] ?? '');
    if (!$section) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Section manquante.']);
        exit;
    }

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
    $allowed = $allowedFields[$section] ?? [];
$data    = [];
$profilePhotoValue = '';
if ($section === 'profil') {
    $profilePhotoValue = trim((string) setting('profil_photo', DEFAULT_ADVISOR_PHOTO_URL, $userId));
}
    $deleteProfilePhoto = !empty($_POST['profil_delete_photo']) && $section === 'profil';

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
    if ($section === 'profil' && $key === 'profil_photo') {
        $data[$key] = $profilePhotoValue;
        continue;
    }
    $data[$key] = $isCheckbox
        ? (isset($_POST[$key]) ? '1' : '0')
        : ($_POST[$key] ?? '');
}

if (empty($data)) {
    echo json_encode(['success' => false, 'error' => 'Aucune donnée à enregistrer.']);
    exit;
}

    if ($section === 'profil') {
        if ($deleteProfilePhoto) {
            settings_remove_uploaded_photo($profilePhotoValue);
            $data['profil_photo'] = DEFAULT_ADVISOR_PHOTO_URL;
            $profilePhotoValue = DEFAULT_ADVISOR_PHOTO_URL;
        } else {
            try {
                $uploadedPhoto = settings_store_profile_photo_upload($userId, $_FILES['profil_photo_file'] ?? []);
                if ($uploadedPhoto !== '') {
                    if ($profilePhotoValue !== '' && $profilePhotoValue !== DEFAULT_ADVISOR_PHOTO_URL) {
                        settings_remove_uploaded_photo($profilePhotoValue);
                    }
                    $data['profil_photo'] = $uploadedPhoto;
                    $profilePhotoValue = $uploadedPhoto;
                } elseif ($profilePhotoValue !== '') {
                    $data['profil_photo'] = $profilePhotoValue;
                }
            } catch (Throwable $e) {
                error_log('profil photo upload: ' . $e->getMessage());
            }
        }
    }

    $ok = settings_save($data, $section);

    if ($ok && $section === 'profil') {
        $profilPrenom = trim((string)($data['profil_prenom'] ?? ''));
        $profilNom = trim((string)($data['profil_nom'] ?? ''));
        $profilPhone = trim((string)($data['profil_telephone'] ?? ''));
        $profilEmail = trim((string)($data['profil_email'] ?? ''));
        $profilRsac = trim((string)($data['profil_rsac'] ?? ''));
        $profilSiret = trim((string)($data['profil_siret'] ?? ''));
        $profilVille = trim((string)($data['profil_ville'] ?? ''));
        $profilAddress = trim((string)($data['profil_address'] ?? ''));
        $profilPhoto = trim((string)($data['profil_photo'] ?? $profilePhotoValue));
        $profilFonction = trim((string)($data['profil_fonction'] ?? ''));

        $syncData = [
            'advisor_firstname' => $profilPrenom,
            'advisor_lastname' => $profilNom,
            'advisor_phone' => $profilPhone,
            'advisor_email' => $profilEmail,
            'advisor_rsac' => $profilRsac,
            'advisor_siret' => $profilSiret,
            'advisor_photo' => $profilPhoto,
            'advisor_title' => $profilFonction,
            'contact_phone' => $profilPhone,
            'contact_email' => $profilEmail,
            'contact_address' => $profilAddress,
        ];
        if ($profilVille !== '') {
            $syncData['zone_city'] = $profilVille;
        }

        settings_save($syncData, 'profil', $userId);
    }

    echo json_encode(
        $ok
            ? ['success' => true,  'message' => 'Paramètres enregistrés.', 'profil_photo' => $section === 'profil' ? ($data['profil_photo'] ?? '') : null]
            : ['success' => false, 'error'   => 'Erreur lors de la sauvegarde.']
    );
} catch (Throwable $e) {
    http_response_code(500);
    error_log('settings/save.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de la sauvegarde.']);
}
