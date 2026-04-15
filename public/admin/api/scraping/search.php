<?php
declare(strict_types=1);
ob_start();
require_once __DIR__ . '/../../../../core/bootstrap.php';
ob_clean();

// Garantir une réponse JSON même en cas d'exception fatale
set_exception_handler(function (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    exit;
});

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payload invalide.']);
    exit;
}

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($payload['csrf_token'] ?? ''))) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expirée, rechargez la page.']);
    exit;
}

$ville = trim((string) ($payload['ville'] ?? ''));
$agent = trim((string) ($payload['agent'] ?? ''));
$type  = trim((string) ($payload['type'] ?? ''));

if ($ville === '' && $agent === '') {
    echo json_encode(['success' => false, 'message' => 'Saisissez une ville ou un agent.']);
    exit;
}

// ── Supabase ──────────────────────────────────────────────────
define('EXP_SUPABASE_URL', 'https://ywzpnbmomlzkcbzzkaqr.supabase.co');
define('EXP_SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inl3enBuYm1vbWx6a2NienprYXFyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDQ2NDkyMzMsImV4cCI6MjA2MDIyNTIzM30.6b8PT7DMzY2jnRgglammdCpqsT6EKR1_Na2T7djGb9A');

/**
 * Appel HTTP vers Supabase REST.
 */
function supabaseGet(string $path): array
{
    $url = EXP_SUPABASE_URL . $path;
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => implode("\r\n", [
                'apikey: ' . EXP_SUPABASE_KEY,
                'Authorization: Bearer ' . EXP_SUPABASE_KEY,
                'Content-Type: application/json',
            ]),
            'timeout' => 15,
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => false],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return [];
    }
    $data = json_decode($raw, true);
    // Si Supabase renvoie une erreur (objet avec 'code'/'message'), retourner []
    if (!is_array($data) || isset($data['code']) || isset($data['error'])) {
        return [];
    }
    // S'assurer que c'est bien une liste de lignes (tableau indexé), pas un objet
    if (array_keys($data) !== range(0, count($data) - 1)) {
        return [];
    }
    return $data;
}

// ── Construire le filtre Supabase ─────────────────────────────
$filters = ['country_code=eq.FR'];

if ($ville !== '') {
    $filters[] = 'city=ilike.' . rawurlencode('*' . $ville . '*');
}
if ($type !== '') {
    $filters[] = 'property_type=ilike.' . rawurlencode('*' . $type . '*');
}

// Si recherche par agent, on cherche d'abord les IDs d'agents
$agentIds = [];
if ($agent !== '') {
    $agentRows = supabaseGet(
        '/rest/v1/agents?country_code=eq.FR&or=(first_name.ilike.' .
        rawurlencode('*' . $agent . '*') . ',last_name.ilike.' . rawurlencode('*' . $agent . '*') . ')' .
        '&select=id&limit=50'
    );
    $agentIds = array_column($agentRows, 'id');

    if (empty($agentIds)) {
        echo json_encode(['success' => true, 'biens' => [], 'message' => 'Aucun agent trouvé.']);
        exit;
    }

    $filters[] = 'agent_id=in.(' . implode(',', array_map('rawurlencode', $agentIds)) . ')';
}

$select = 'id,title,address,city,zipcode,price,square_feet,bedrooms,bathrooms,total_rooms,'
        . 'property_type,listing_type,status,images,source_id,'
        . 'agent_first_name,agent_last_name,agent_email,agent_phone,'
        . 'geo_lat,geo_lon,energy_efficiency_class,has_balcony,has_terrace,'
        . 'parking_info,construction_year,description';

$qs = implode('&', $filters) . '&select=' . $select . '&limit=100';
$rows = supabaseGet('/rest/v1/listings?' . $qs);

if (empty($rows)) {
    echo json_encode(['success' => true, 'biens' => []]);
    exit;
}

// ── Récupérer les biens déjà importés (par source_externe_id) ─
$extIds = array_filter(array_column($rows, 'id'));
$alreadyImported = [];
if (!empty($extIds)) {
    $placeholders = implode(',', array_fill(0, count($extIds), '?'));
    $stmt = db()->prepare(
        "SELECT source_externe_id, source FROM biens WHERE source_externe_id IN ($placeholders)"
    );
    $stmt->execute(array_values($extIds));
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $alreadyImported[$row['source_externe_id']] = $row['source'];
    }
}

// ── Transformer chaque bien ────────────────────────────────────
$biens = [];
foreach ($rows as $row) {
    $images = is_array($row['images']) ? $row['images'] : [];
    $cover  = '';
    foreach ($images as $img) {
        if (!is_array($img)) continue;
        if (!empty($img['is_front_cover'])) { $cover = (string) ($img['url'] ?? ''); break; }
    }
    if ($cover === '' && is_array($images[0] ?? null) && !empty($images[0]['url'])) {
        $cover = (string) $images[0]['url'];
    }

    $extId = (string) ($row['id'] ?? '');

    $biens[] = [
        'id'              => $extId,
        'reference'       => (string) ($row['source_id'] ?? ''),
        'titre'           => (string) ($row['title'] ?? ''),
        'ville'           => (string) ($row['city'] ?? ''),
        'code_postal'     => (string) ($row['zipcode'] ?? ''),
        'adresse'         => (string) ($row['address'] ?? ''),
        'prix'            => (float)  ($row['price'] ?? 0),
        'surface'         => (float)  ($row['square_feet'] ?? 0),
        'pieces'          => (int)    ($row['total_rooms'] ?? 0),
        'chambres'        => (int)    ($row['bedrooms'] ?? 0),
        'sdb'             => (int)    ($row['bathrooms'] ?? 0),
        'property_type'   => (string) ($row['property_type'] ?? ''),
        'listing_type'    => (string) ($row['listing_type'] ?? 'sale'),
        'latitude'        => (float)  ($row['geo_lat'] ?? 0),
        'longitude'       => (float)  ($row['geo_lon'] ?? 0),
        'dpe_classe'      => (string) ($row['energy_efficiency_class'] ?? ''),
        'a_balcon'        => !empty($row['has_balcony']) ? 1 : 0,
        'a_terrasse'      => !empty($row['has_terrace']) ? 1 : 0,
        'annee_construction' => (int) ($row['construction_year'] ?? 0),
        'description'     => (string) ($row['description'] ?? ''),
        'agent_first_name'=> (string) ($row['agent_first_name'] ?? ''),
        'agent_last_name' => (string) ($row['agent_last_name'] ?? ''),
        'agent_email'     => (string) ($row['agent_email'] ?? ''),
        'agent_phone'     => (string) ($row['agent_phone'] ?? ''),
        'cover_url'       => $cover,
        'nb_photos'       => count($images),
        'already_imported'  => isset($alreadyImported[$extId]),
        'imported_source'   => $alreadyImported[$extId] ?? null,
    ];
}

echo json_encode(['success' => true, 'biens' => $biens], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
