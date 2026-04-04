<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../core/bootstrap.php';
Auth::requireAuth();

$redirect_uri = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST']
              . '/admin/api/settings/gsc-callback.php';

// ── Révocation ───────────────────────────────────────────────
if (($_GET['action'] ?? '') === 'revoke') {
    $token = setting('api_gsc_refresh_token', '');
    if ($token) {
        @file_get_contents(
            'https://oauth2.googleapis.com/revoke?token=' . urlencode($token),
            false,
            stream_context_create(['http' => ['method' => 'POST']])
        );
    }
    setting_delete('api_gsc_refresh_token');
    setting_delete('api_gsc_access_token');
    setting_delete('api_gsc_token_expires');
    header('Location: /admin/settings?section=api&gsc=revoked');
    exit;
}

// ── Erreur retournée par Google ──────────────────────────────
if (!empty($_GET['error'])) {
    $err = htmlspecialchars($_GET['error']);
    die("<p style='color:#e74c3c;padding:20px'>Erreur Google OAuth : <strong>{$err}</strong>
         <br><a href='/admin/settings?section=api'>← Retour</a></p>");
}

// ── Échange code → tokens ────────────────────────────────────
$code = $_GET['code'] ?? '';
if (empty($code)) {
    die("<p style='color:#e74c3c;padding:20px'>Code OAuth manquant.
         <br><a href='/admin/settings?section=api'>← Retour</a></p>");
}

$client_id     = setting('api_gsc_client_id', '');
$client_secret = setting('api_gsc_client_secret', '');

$response = file_get_contents('https://oauth2.googleapis.com/token', false,
    stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code',
            ]),
            'ignore_errors' => true,
        ],
    ])
);

$data = json_decode($response ?: '{}', true);

if (empty($data['refresh_token'])) {
    $msg = htmlspecialchars($data['error_description'] ?? $data['error'] ?? 'Réponse invalide');
    die("<p style='color:#e74c3c;padding:20px'>Échec échange token : <strong>{$msg}</strong>
         <br><a href='/admin/settings?section=api'>← Retour</a></p>");
}

// ── Sauvegarde en DB ─────────────────────────────────────────
setting_set('api_gsc_refresh_token', $data['refresh_token']);
setting_set('api_gsc_access_token',  $data['access_token'] ?? '');
setting_set('api_gsc_token_expires', (string)(time() + (int)($data['expires_in'] ?? 3600)));

header('Location: /admin/settings?section=api&gsc=connected');
exit;
