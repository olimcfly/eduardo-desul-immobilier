<?php
/**
 * design.php — Proxy Builder Pro
 * Permet de récupérer le HTML d'une page (même ou autre domaine)
 * côté serveur pour éviter les restrictions CORS navigateur.
 */

// Sécurité basique : admin uniquement
$adminInit = __DIR__ . '/../../../includes/init.php';
if (file_exists($adminInit)) {
    require_once $adminInit;
    if (!isset($_SESSION['auth_admin_logged_in']) || !$_SESSION['auth_admin_logged_in']) {
        http_response_code(403);
        echo json_encode(['success'=>false,'error'=>'Non autorisé']);
        exit;
    }
} else {
    // Fallback : vérifier session manuellement
    session_start();
    if (empty($_SESSION['auth_admin_logged_in'])) {
        http_response_code(403);
        echo json_encode(['success'=>false,'error'=>'Non autorisé']);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');

$raw    = file_get_contents('php://input');
$body   = $raw ? json_decode($raw, true) : [];
$action = $body['action'] ?? $_POST['action'] ?? '';

if ($action === 'fetch_page') {
    $url = trim($body['url'] ?? $_POST['url'] ?? '');

    if (!$url) {
        echo json_encode(['success'=>false,'error'=>'URL manquante']);
        exit;
    }

    // Valider l'URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success'=>false,'error'=>'URL invalide']);
        exit;
    }

    // Blacklist domaines sensibles
    $parsedHost = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $blacklist = ['localhost','127.0.0.1','0.0.0.0','::1','169.254.','10.','192.168.','172.'];
    foreach ($blacklist as $b) {
        if (strpos($parsedHost, $b) !== false) {
            echo json_encode(['success'=>false,'error'=>'Domaine non autorisé']);
            exit;
        }
    }

    // Fetch avec cURL
    if (!function_exists('curl_init')) {
        // Fallback file_get_contents
        $ctx = stream_context_create([
            'http' => [
                'timeout'        => 12,
                'user_agent'     => 'Mozilla/5.0 (compatible; BuilderProBot/1.0)',
                'follow_location'=> true,
                'max_redirects'  => 3,
            ],
            'ssl' => ['verify_peer'=>false,'verify_peer_name'=>false]
        ]);
        $html = @file_get_contents($url, false, $ctx);
        if ($html === false) {
            echo json_encode(['success'=>false,'error'=>'Impossible de récupérer la page']);
            exit;
        }
    } else {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; BuilderProBot/1.0)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING       => 'gzip,deflate',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml',
                'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
            ],
        ]);
        $html     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($html === false || !empty($err)) {
            echo json_encode(['success'=>false,'error'=>'cURL: '.$err]);
            exit;
        }
        if ($httpCode >= 400) {
            echo json_encode(['success'=>false,'error'=>'HTTP '.$httpCode]);
            exit;
        }
    }

    if (strlen($html) < 50) {
        echo json_encode(['success'=>false,'error'=>'Réponse vide ou trop courte']);
        exit;
    }

    // Encoder en UTF-8 si nécessaire
    if (!mb_check_encoding($html, 'UTF-8')) {
        $html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8,ISO-8859-1,Windows-1252', true));
    }

    echo json_encode([
        'success' => true,
        'html'    => $html,
        'length'  => strlen($html),
        'url'     => $url,
    ]);
    exit;
}

// Action inconnue
echo json_encode(['success'=>false,'error'=>'Action inconnue: '.$action]);