<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function scrapingImportJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function scrapingImportInput(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);

    return is_array($data) ? $data : [];
}

function scrapingImportSlug(string $title, PDO $pdo): string
{
    $base = slugify($title);
    if ($base === '') {
        $base = 'bien-exp-france';
    }

    $slug = $base;
    $i = 2;
    $stmt = $pdo->prepare('SELECT id FROM biens WHERE slug = :slug LIMIT 1');
    while (true) {
        $stmt->execute([':slug' => $slug]);
        if (!$stmt->fetchColumn()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

function scrapingImportType(string $type): string
{
    $allowed = ['appartement', 'maison', 'terrain', 'local', 'immeuble', 'autre'];

    return in_array($type, $allowed, true) ? $type : 'autre';
}

if (!Auth::check()) {
    scrapingImportJson(['success' => false, 'message' => 'Authentification requise.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    scrapingImportJson(['success' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$input = scrapingImportInput();
if (!hash_equals(csrfToken(), (string) ($input['csrf_token'] ?? ''))) {
    scrapingImportJson(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
}

$ids = $input['ids'] ?? [];
if (!is_array($ids) || $ids === []) {
    scrapingImportJson(['success' => false, 'message' => 'Aucun bien sélectionné.'], 422);
}

$results = $_SESSION['scraping_exp_results'] ?? [];
if (!is_array($results) || $results === []) {
    scrapingImportJson(['success' => false, 'message' => 'Relancez une recherche avant import.'], 422);
}

$pdo = db();
$imported = 0;
$skipped = 0;

foreach ($ids as $id) {
    $id = (string) $id;
    $item = $results[$id] ?? null;
    if (!is_array($item)) {
        $skipped++;
        continue;
    }

    $exists = $pdo->prepare("SELECT id FROM biens WHERE source_provider = 'exp_france' AND source_id = :source_id LIMIT 1");
    $exists->execute([':source_id' => (string) ($item['source_id'] ?? '')]);
    if ($exists->fetchColumn()) {
        $skipped++;
        continue;
    }

    $title = trim((string) ($item['titre'] ?? 'Bien eXp France'));
    $slug = scrapingImportSlug($title, $pdo);
    $photos = array_values(array_filter($item['photos'] ?? [], 'is_string'));

    $stmt = $pdo->prepare(
        'INSERT INTO biens (
            slug, reference, source_provider, source_id, source_url, titre, type_transaction, type_bien,
            prix, surface, pieces, chambres, sdb, ville, code_postal, latitude, longitude,
            description, statut, sort_order, photo_principale, created_at, updated_at
        ) VALUES (
            :slug, :reference, :source_provider, :source_id, :source_url, :titre, :type_transaction, :type_bien,
            :prix, :surface, :pieces, :chambres, :sdb, :ville, :code_postal, :latitude, :longitude,
            :description, :statut, 0, :photo_principale, NOW(), NOW()
        )'
    );
    $stmt->execute([
        ':slug' => $slug,
        ':reference' => (string) ($item['reference'] ?? ''),
        ':source_provider' => 'exp_france',
        ':source_id' => (string) ($item['source_id'] ?? ''),
        ':source_url' => (string) ($item['source_url'] ?? ''),
        ':titre' => $title,
        ':type_transaction' => 'vente',
        ':type_bien' => scrapingImportType((string) ($item['property_type'] ?? '')),
        ':prix' => (float) ($item['prix'] ?? 0),
        ':surface' => (float) ($item['surface'] ?? 0),
        ':pieces' => (int) ($item['pieces'] ?? 0),
        ':chambres' => (int) ($item['chambres'] ?? 0),
        ':sdb' => (int) ($item['sdb'] ?? 0),
        ':ville' => (string) ($item['ville'] ?? ''),
        ':code_postal' => (string) ($item['code_postal'] ?? ''),
        ':latitude' => $item['lat'] ?? null,
        ':longitude' => $item['lng'] ?? null,
        ':description' => (string) ($item['description'] ?? ''),
        ':statut' => 'pending',
        ':photo_principale' => (string) ($item['cover_url'] ?? ''),
    ]);

    $bienId = (int) $pdo->lastInsertId();
    $photoStmt = $pdo->prepare('INSERT INTO bien_photos (bien_id, chemin, alt, position, created_at) VALUES (:bien_id, :chemin, :alt, :position, NOW())');
    foreach (array_slice($photos, 0, 20) as $position => $photoUrl) {
        $photoStmt->execute([
            ':bien_id' => $bienId,
            ':chemin' => $photoUrl,
            ':alt' => $title,
            ':position' => $position,
        ]);
    }

    $imported++;
}

scrapingImportJson([
    'success' => true,
    'message' => $imported . ' bien(s) importé(s) en brouillon.',
    'imported' => $imported,
    'skipped' => $skipped,
]);
