<?php
declare(strict_types=1);
ob_start();
require_once __DIR__ . '/../../../../core/bootstrap.php';
ob_clean();

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
    echo json_encode(['success' => false, 'message' => 'Session expirée.']);
    exit;
}

$ids    = is_array($payload['ids'] ?? null) ? $payload['ids'] : [];
$source = in_array($payload['source'] ?? '', ['own', 'partage'], true) ? $payload['source'] : 'own';

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Aucun bien sélectionné.']);
    exit;
}

$ids = array_slice(array_map('strval', $ids), 0, 50); // max 50 par appel

// ── Supabase ──────────────────────────────────────────────────
define('EXP_SUPABASE_URL', 'https://ywzpnbmomlzkcbzzkaqr.supabase.co');
define('EXP_SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inl3enBuYm1vbWx6a2NienprYXFyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDQ2NDkyMzMsImV4cCI6MjA2MDIyNTIzM30.6b8PT7DMzY2jnRgglammdCpqsT6EKR1_Na2T7djGb9A');

function supabaseGet(string $path): array
{
    $url = EXP_SUPABASE_URL . $path;
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => "apikey: " . EXP_SUPABASE_KEY . "\r\nAuthorization: Bearer " . EXP_SUPABASE_KEY . "\r\n",
            'timeout' => 20,
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => false],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return [];
    $data = json_decode($raw, true);
    if (!is_array($data) || isset($data['code']) || isset($data['error'])) {
        return [];
    }
    if (array_keys($data) !== range(0, count($data) - 1)) {
        return [];
    }
    return $data;
}

function stripHtml(string $html): string
{
    return trim(preg_replace('/\s+/', ' ', strip_tags($html)));
}

// ── Récupérer les données complètes depuis Supabase ───────────
$idList  = implode(',', array_map('rawurlencode', $ids));
$select  = 'id,title,address,city,zipcode,price,square_feet,bedrooms,bathrooms,total_rooms,'
         . 'property_type,listing_type,images,source_id,'
         . 'agent_first_name,agent_last_name,'
         . 'geo_lat,geo_lon,energy_efficiency_class,has_balcony,has_terrace,'
         . 'construction_year,description';

$rows = supabaseGet('/rest/v1/listings?id=in.(' . $idList . ')&select=' . $select);

if (empty($rows)) {
    echo json_encode(['success' => false, 'message' => 'Impossible de récupérer les données.']);
    exit;
}

// ── Identifier les déjà importés ──────────────────────────────
$existingPlaceholders = implode(',', array_fill(0, count($ids), '?'));
$existingStmt = db()->prepare(
    "SELECT source_externe_id FROM biens WHERE source_externe_id IN ($existingPlaceholders)"
);
$existingStmt->execute($ids);
$existingIds = array_flip($existingStmt->fetchAll(PDO::FETCH_COLUMN));

$user    = Auth::user();
$agentId = (int) ($user['id'] ?? 1);
$imported = 0;
$skipped  = 0;

$pdo = db();
$sql = "INSERT INTO biens (
    slug, titre, description, transaction_type, type_bien, prix, surface,
    pieces, chambres, salles_de_bain,
    adresse, ville, code_postal, latitude, longitude, statut,
    dpe_classe, reference, a_balcon, a_terrasse,
    annee_construction, agent_id, photo_principale,
    source, source_externe_id, source_agent_nom
) VALUES (
    :slug, :titre, :description, :transaction_type, :type_bien, :prix, :surface,
    :pieces, :chambres, :sdb,
    :adresse, :ville, :code_postal, :latitude, :longitude, :statut,
    :dpe_classe, :reference, :a_balcon, :a_terrasse,
    :annee_construction, :agent_id, :photo_principale,
    :source, :source_externe_id, :source_agent_nom
)";

$typeMap = [
    'Maison'      => 'maison',
    'Appartement' => 'appartement',
    'Terrain'     => 'terrain',
    'Commerce'    => 'local',
    'Bureau'      => 'local',
];
$listingTypeMap = ['sale' => 'Vente', 'rent' => 'Location'];

foreach ($rows as $row) {
    $extId = (string) ($row['id'] ?? '');
    if (isset($existingIds[$extId])) {
        $skipped++;
        continue;
    }

    $images = is_array($row['images']) ? $row['images'] : [];
    $cover  = '';
    foreach ($images as $img) {
        if (!is_array($img)) continue;
        if (!empty($img['is_front_cover'])) { $cover = (string) ($img['url'] ?? ''); break; }
    }
    if ($cover === '' && is_array($images[0] ?? null) && !empty($images[0]['url'])) {
        $cover = (string) $images[0]['url'];
    }

    $rawType  = (string) ($row['property_type'] ?? '');
    $typeNorm = $typeMap[$rawType] ?? 'autre';
    $transact = $listingTypeMap[$row['listing_type'] ?? 'sale'] ?? 'Vente';
    $agentNom = trim(($row['agent_first_name'] ?? '') . ' ' . ($row['agent_last_name'] ?? ''));
    $ref      = (string) ($row['source_id'] ?? $extId);
    $titre    = (string) ($row['title'] ?? 'Bien sans titre');
    $slug     = slugify($titre) . '-' . substr($extId, 0, 8);

    // S'assurer que la référence est unique
    $checkStmt = $pdo->prepare("SELECT id FROM biens WHERE reference = :ref LIMIT 1");
    $checkStmt->execute([':ref' => $ref]);
    if ($checkStmt->fetch()) {
        $ref = $ref . '-' . substr($extId, 0, 6);
    }
    // Slug unique
    $checkSlug = $pdo->prepare("SELECT id FROM biens WHERE slug = :slug LIMIT 1");
    $checkSlug->execute([':slug' => $slug]);
    if ($checkSlug->fetch()) {
        $slug = $slug . '-' . substr(md5($extId), 0, 4);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':slug'              => $slug,
        ':titre'             => $titre,
        ':description'       => stripHtml((string) ($row['description'] ?? '')),
        ':transaction_type'  => $transact,
        ':type_bien'         => $typeNorm,
        ':prix'              => (float) ($row['price'] ?? 0),
        ':surface'           => (float) ($row['square_feet'] ?? 0),
        ':pieces'            => (int)   ($row['total_rooms'] ?? 0),
        ':chambres'          => (int)   ($row['bedrooms'] ?? 0),
        ':sdb'               => (int)   ($row['bathrooms'] ?? 0),
        ':adresse'           => (string) ($row['address'] ?? ''),
        ':ville'             => (string) ($row['city'] ?? ''),
        ':code_postal'       => (string) ($row['zipcode'] ?? ''),
        ':latitude'          => (float)  ($row['geo_lat'] ?? 0),
        ':longitude'         => (float)  ($row['geo_lon'] ?? 0),
        ':statut'            => 'Disponible',
        ':dpe_classe'        => strtoupper((string) ($row['energy_efficiency_class'] ?? '')),
        ':reference'         => $ref,
        ':a_balcon'          => empty($row['has_balcony']) ? 0 : 1,
        ':a_terrasse'        => empty($row['has_terrace']) ? 0 : 1,
        ':annee_construction'=> (int) ($row['construction_year'] ?? 0) ?: null,
        ':agent_id'          => $agentId,
        ':photo_principale'  => $cover,
        ':source'            => $source,
        ':source_externe_id' => $extId,
        ':source_agent_nom'  => $agentNom ?: null,
    ]);

    $imported++;
}

$msg = $imported > 0
    ? "{$imported} bien(s) importé(s) avec succès."
    : "Tous les biens sélectionnés étaient déjà importés.";

if ($skipped > 0) {
    $msg .= " ({$skipped} ignoré(s) car déjà présent(s)).";
}

echo json_encode([
    'success'  => true,
    'imported' => $imported,
    'skipped'  => $skipped,
    'message'  => $msg,
], JSON_UNESCAPED_UNICODE);
