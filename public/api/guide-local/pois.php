<?php
/**
 * GET /api/guide-local/pois.php
 * Paramètres : ?city_slug=bordeaux | ?district_slug=chartrons | ?category_slug=restaurants-bars
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

$citySlug = isset($_GET['city_slug']) ? trim((string) $_GET['city_slug']) : '';
$districtSlug = isset($_GET['district_slug']) ? trim((string) $_GET['district_slug']) : '';
$categorySlug = isset($_GET['category_slug']) ? trim((string) $_GET['category_slug']) : '';

try {
    $pdo = db();

    // Vérifier que les tables POI existent (migration 032) — éviter SHOW … LIKE ? avec PDO/MariaDB
    $chk = $pdo->query(
        "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'guide_pois' LIMIT 1"
    )->fetch();
    if (!$chk) {
        http_response_code(503);
        echo json_encode([
            'ok' => false,
            'error' => 'Module POI non installé. Exécuter database/migrations/032_guide_local_poi.sql',
        ]);
        exit;
    }

    $annuaire034 = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'guide_pois' AND COLUMN_NAME = 'is_verified'"
    )->fetchColumn() > 0;

    if ($annuaire034) {
        $sql = 'SELECT p.id, p.slug, p.name, p.description, p.seo_keywords, p.address, p.postal_code, p.latitude, p.longitude, p.phone, p.website,
            p.email, p.facebook, p.instagram, p.opening_hours, p.featured_image, p.is_active, p.is_verified, p.rating, p.reviews_count,
            v.slug AS city_slug, v.nom AS city_name,
            q.slug AS district_slug, q.nom AS district_name,
            c.slug AS category_slug, c.name AS category_name, c.icon AS category_icon
            FROM guide_pois p
            INNER JOIN guide_poi_categories c ON c.id = p.category_id AND c.is_active = 1
            LEFT JOIN villes v ON v.id = p.ville_id
            LEFT JOIN quartiers q ON q.id = p.quartier_id
            WHERE p.is_active = 1';
    } else {
        $sql = 'SELECT p.id, p.slug, p.name, p.description, p.address, p.latitude, p.longitude, p.phone, p.website,
            p.opening_hours, p.featured_image, p.is_active,
            v.slug AS city_slug, v.nom AS city_name,
            q.slug AS district_slug, q.nom AS district_name,
            c.slug AS category_slug, c.name AS category_name, c.icon AS category_icon
            FROM guide_pois p
            INNER JOIN guide_poi_categories c ON c.id = p.category_id AND c.is_active = 1
            LEFT JOIN villes v ON v.id = p.ville_id
            LEFT JOIN quartiers q ON q.id = p.quartier_id
            WHERE p.is_active = 1';
    }
    $params = [];

    if ($categorySlug !== '') {
        $sql .= ' AND c.slug = ?';
        $params[] = $categorySlug;
    }
    if ($districtSlug !== '') {
        $sql .= ' AND q.slug = ?';
        $params[] = $districtSlug;
    }
    if ($citySlug !== '') {
        $sql .= ' AND (
            v.slug = ?
            OR q.ville_id = (SELECT id FROM villes WHERE slug = ? LIMIT 1)
        )';
        $params[] = $citySlug;
        $params[] = $citySlug;
    }

    $sql .= ' ORDER BY p.name ASC LIMIT 200';

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'pois' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Serveur']);
}
