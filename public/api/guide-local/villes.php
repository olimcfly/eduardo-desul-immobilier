<?php
/**
 * GET /api/guide-local/villes.php
 * GET /api/guide-local/villes.php?slug=bordeaux
 * Liste des villes actives (+ quartiers si slug demandé).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__, 3) . '/core/bootstrap.php';

try {
    $pdo = db();
    $slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

    if ($slug !== '') {
        $st = $pdo->prepare(
            'SELECT id, nom, slug, code_postal, type, description, image_url, ordre, actif, created_at, updated_at
             FROM villes WHERE slug = ? AND actif = 1 LIMIT 1'
        );
        $st->execute([$slug]);
        $city = $st->fetch(PDO::FETCH_ASSOC);
        if (!$city) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Ville introuvable']);
            exit;
        }
        $q = $pdo->prepare(
            'SELECT id, nom, slug, ville_id, description, image_url, ordre, actif, created_at, updated_at
             FROM quartiers WHERE ville_id = ? AND actif = 1 ORDER BY ordre ASC, nom ASC'
        );
        $q->execute([(int) $city['id']]);
        $city['districts'] = $q->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'city' => $city], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $rows = $pdo->query(
        'SELECT id, nom, slug, code_postal, type, description, image_url, ordre, actif, created_at, updated_at
         FROM villes WHERE actif = 1 ORDER BY ordre ASC, nom ASC'
    )->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'cities' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Serveur']);
}
