<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../includes/settings.php';

$section = preg_replace('/[^a-z]/', '', (string)($_GET['section'] ?? 'profil'));

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

switch ($section) {
    case 'profil':
        $name = (string)setting('profil_nom', '');
        $email = (string)setting('profil_email', '');
        $city = (string)setting('profil_ville', '');
        $photo = (string)setting('profil_photo', '');
        echo '<form class="settings-form">';
        echo '<input type="hidden" name="section" value="profil">';
        echo '<div class="form-group"><label>Nom complet</label><input type="text" name="profil_nom" value="' . h($name) . '"></div>';
        echo '<div class="form-row">';
        echo '<div class="form-group"><label>Email</label><input type="email" name="profil_email" value="' . h($email) . '"></div>';
        echo '<div class="form-group"><label>Ville</label><input type="text" name="profil_ville" value="' . h($city) . '"></div>';
        echo '</div>';
        echo '<div class="form-group"><label>URL photo</label><input type="url" name="profil_photo" value="' . h($photo) . '"></div>';
        echo '<div class="drawer-footer"><button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Fermer</button><button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button></div>';
        echo '</form>';
        break;

    case 'api':
        $openai = (string)setting('api_openai', '');
        $maps = (string)setting('api_google_maps', '');
        $gmb = (string)setting('api_gmb_client_id', '');
        $fb = (string)setting('api_fb_access_token', '');
        $cloudinary = (string)setting('api_cloudinary_key', '');
        echo '<form class="settings-form">';
        echo '<input type="hidden" name="section" value="api">';
        foreach ([
            'api_openai' => ['OpenAI', $openai],
            'api_google_maps' => ['Google Maps', $maps],
            'api_gmb_client_id' => ['Google Business Profile', $gmb],
            'api_fb_access_token' => ['Facebook', $fb],
            'api_cloudinary_key' => ['Cloudinary', $cloudinary],
        ] as $key => [$label, $value]) {
            echo '<div class="form-group api-key-row">';
            echo '<label>' . h($label) . '</label>';
            echo '<input type="password" name="' . h($key) . '" value="' . h((string)$value) . '">';
            echo '<button type="button" class="api-key-toggle" aria-label="Afficher/masquer"><i class="fas fa-eye"></i></button>';
            echo '</div>';
        }
        echo '<div class="drawer-footer"><button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Fermer</button><button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button></div>';
        echo '</form>';
        break;

    default:
        echo '<form class="settings-form">';
        echo '<input type="hidden" name="section" value="' . h($section) . '">';
        echo '<div class="form-group"><label>Section</label><input type="text" value="' . h(ucfirst($section)) . '" disabled></div>';
        echo '<p style="color:#7f8c8d;font-size:13px">Cette section est prête pour vos champs personnalisés.</p>';
        echo '<div class="drawer-footer"><button type="button" class="btn-cancel" onclick="closeSettingsDrawer()">Fermer</button><button type="submit" class="btn-save"><i class="fas fa-check"></i> Enregistrer</button></div>';
        echo '</form>';
        break;
}
