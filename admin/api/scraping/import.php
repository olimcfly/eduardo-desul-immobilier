<?php
declare(strict_types=1);
/**
 * Même logique que l'ancien import de scraping — bootstrap depuis site/admin/api/scraping/.
 */
ob_start();
require_once dirname(__DIR__, 3) . '/core/bootstrap.php';
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
define('EXP_SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? '');
define('EXP_SUPABASE_KEY', $_ENV['SUPABASE_ANON_KEY'] ?? '');

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

require_once dirname(__DIR__, 3) . '/core/helpers/scraping_import_biens.php';

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
$existingIds = [];
try {
    $existingPlaceholders = implode(',', array_fill(0, count($ids), '?'));
    $existingStmt = db()->prepare(
        "SELECT source_externe_id FROM biens WHERE source_externe_id IN ($existingPlaceholders)"
    );
    $existingStmt->execute($ids);
    $existingIds = array_flip($existingStmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Throwable $e) {
    error_log('[scraping import] existing check: ' . $e->getMessage());
}

// Ne pas lier users.id ici : sur certaines bases agent_id référence la table agents (IA), pas users — sinon erreur SQL 1452.
$imported = 0;
$skipped  = 0;

$pdo = db();
$biensCols = scraping_import_biens_column_set($pdo);

$typeMap = [
    'Maison'      => 'maison',
    'Appartement' => 'appartement',
    'Terrain'     => 'terrain',
    'Commerce'    => 'local',
    'Bureau'      => 'local',
];
$listingTypeMap = ['sale' => 'vente', 'rent' => 'location'];

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
    $transact = $listingTypeMap[$row['listing_type'] ?? 'sale'] ?? 'vente';
    $agentNom = trim(($row['agent_first_name'] ?? '') . ' ' . ($row['agent_last_name'] ?? ''));
    $ref      = (string) ($row['source_id'] ?? $extId);
    $titre    = (string) ($row['title'] ?? 'Bien sans titre');
    $slug     = slugify($titre) . '-' . substr($extId, 0, 8);

    // S'assurer que la référence est unique (si la colonne existe)
    if (isset($biensCols['reference'])) {
        try {
            $checkStmt = $pdo->prepare('SELECT id FROM biens WHERE reference = :ref LIMIT 1');
            $checkStmt->execute([':ref' => $ref]);
            if ($checkStmt->fetch()) {
                $ref = $ref . '-' . substr($extId, 0, 6);
            }
        } catch (Throwable $e) {
            error_log('[scraping import] reference check: ' . $e->getMessage());
        }
    }
    // Slug unique
    $checkSlug = $pdo->prepare("SELECT id FROM biens WHERE slug = :slug LIMIT 1");
    $checkSlug->execute([':slug' => $slug]);
    if ($checkSlug->fetch()) {
        $slug = $slug . '-' . substr(md5($extId), 0, 4);
    }

    $dpeRaw = strtoupper(trim((string) ($row['energy_efficiency_class'] ?? '')));
    $dpeOne = $dpeRaw !== '' ? substr($dpeRaw, 0, 1) : null;

    $lat = isset($row['geo_lat']) && $row['geo_lat'] !== '' && (float) $row['geo_lat'] != 0.0
        ? (float) $row['geo_lat'] : null;
    $lon = isset($row['geo_lon']) && $row['geo_lon'] !== '' && (float) $row['geo_lon'] != 0.0
        ? (float) $row['geo_lon'] : null;

    $rowInsert = [
        'slug'                => $slug,
        'titre'               => $titre,
        'description'         => stripHtml((string) ($row['description'] ?? '')),
        'type_transaction'    => $transact,
        'type_bien'           => $typeNorm,
        'prix'                => (float) ($row['price'] ?? 0),
        'surface'             => (float) ($row['square_feet'] ?? 0),
        'pieces'              => (int) ($row['total_rooms'] ?? 0) ?: null,
        'chambres'            => (int) ($row['bedrooms'] ?? 0) ?: null,
        'sdb'                 => (int) ($row['bathrooms'] ?? 0) ?: null,
        'adresse'             => (string) ($row['address'] ?? ''),
        'ville'               => (string) ($row['city'] ?? ''),
        'code_postal'         => (string) ($row['zipcode'] ?? ''),
        'latitude'            => $lat,
        'longitude'           => $lon,
        'statut'              => 'actif',
        'publier_vitrine'     => 0,
        'dpe_classe'          => $dpeOne,
        'reference'           => $ref,
        'a_balcon'            => empty($row['has_balcony']) ? 0 : 1,
        'a_terrasse'          => empty($row['has_terrace']) ? 0 : 1,
        'annee_construction'  => (int) ($row['construction_year'] ?? 0) ?: null,
        'photo_principale'    => $cover !== '' ? $cover : null,
        'agent_id'            => null,
        'source'              => $source,
        'source_externe_id'   => $extId,
        'source_agent_nom'    => $agentNom !== '' ? $agentNom : null,
    ];

    try {
        [$sql, $params] = scraping_import_build_insert($pdo, $rowInsert);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('[scraping import] ' . $e->getMessage());
        $msg = $e->getMessage();
        if (stripos($msg, 'Unknown column') !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Schéma biens incompatible. Exécutez les migrations (reference, a_balcon, 038_scraping, etc.).',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (stripos($msg, "doesn't have a default value") !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'INSERT bloqué : une colonne obligatoire manque dans l’import. ' . (defined('APP_DEBUG') && APP_DEBUG ? $msg : 'Voir error_log ou activez APP_DEBUG.'),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (stripos($msg, 'foreign key') !== false || stripos($msg, '1452') !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Import refusé (contrainte SQL). Détail : ' . (defined('APP_DEBUG') && APP_DEBUG ? $msg : 'voir error_log serveur'),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        throw $e;
    } catch (RuntimeException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }

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
