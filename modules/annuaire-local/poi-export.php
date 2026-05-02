<?php
declare(strict_types=1);

/**
 * Export JSON d’un POI (appelé depuis accueil.php, session admin déjà vérifiée).
 */
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'id invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
    if (!function_exists('annuaire_local_table_exists') || !annuaire_local_table_exists($pdo, 'guide_pois')) {
        http_response_code(503);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Tables POI absentes'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = 'SELECT p.*,
            v.id AS ville_row_id, v.nom AS ville_nom, v.slug AS ville_slug,
            q.id AS quartier_row_id, q.nom AS quartier_nom, q.slug AS quartier_slug, q.ville_id AS quartier_ville_id,
            c.id AS cat_id, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon, c.sort_order AS cat_sort_order
            FROM guide_pois p
            LEFT JOIN villes v ON v.id = p.ville_id
            LEFT JOIN quartiers q ON q.id = p.quartier_id
            INNER JOIN guide_poi_categories c ON c.id = p.category_id
            WHERE p.id = ? LIMIT 1';
    $st = $pdo->prepare($sql);
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'POI introuvable'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $mediaRows = [];
    if (function_exists('annuaire_local_table_exists') && annuaire_local_table_exists($pdo, 'guide_poi_media')) {
        $mediaSt = $pdo->prepare(
            'SELECT id, file_path, file_type, alt_text, sort_order, created_at
             FROM guide_poi_media WHERE poi_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $mediaSt->execute([$id]);
        $mediaRows = $mediaSt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    $slug = (string) ($row['slug'] ?? 'poi');
    $safeFile = preg_replace('/[^a-z0-9_-]+/i', '-', $slug) ?: 'poi';
    $safeFile = trim($safeFile, '-') ?: 'poi';
    $filename = 'poi-' . $safeFile . '-' . $id . '.json';

    $payload = [
        'export_version' => 1,
        'exported_at'    => gmdate('Y-m-d\TH:i:s\Z'),
        'poi'            => [
            'id'             => (int) $row['id'],
            'name'           => (string) $row['name'],
            'slug'           => (string) $row['slug'],
            'description'    => $row['description'] !== null && $row['description'] !== '' ? (string) $row['description'] : null,
            'address'        => $row['address'] !== null && $row['address'] !== '' ? (string) $row['address'] : null,
            'latitude'       => isset($row['latitude']) && $row['latitude'] !== null && $row['latitude'] !== '' ? (float) $row['latitude'] : null,
            'longitude'      => isset($row['longitude']) && $row['longitude'] !== null && $row['longitude'] !== '' ? (float) $row['longitude'] : null,
            'phone'          => $row['phone'] !== null && $row['phone'] !== '' ? (string) $row['phone'] : null,
            'website'        => $row['website'] !== null && $row['website'] !== '' ? (string) $row['website'] : null,
            'opening_hours'  => $row['opening_hours'] !== null && $row['opening_hours'] !== '' ? (string) $row['opening_hours'] : null,
            'featured_image' => $row['featured_image'] !== null && $row['featured_image'] !== '' ? (string) $row['featured_image'] : null,
            'is_active'      => (int) ($row['is_active'] ?? 0) === 1,
            'ville_id'       => isset($row['ville_id']) && $row['ville_id'] !== null ? (int) $row['ville_id'] : null,
            'quartier_id'    => isset($row['quartier_id']) && $row['quartier_id'] !== null ? (int) $row['quartier_id'] : null,
            'category_id'    => (int) $row['category_id'],
            'created_at'     => isset($row['created_at']) ? (string) $row['created_at'] : null,
            'updated_at'     => isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        ],
        'ville'     => null,
        'quartier'  => null,
        'category'  => [
            'id'         => (int) $row['cat_id'],
            'name'       => (string) $row['cat_name'],
            'slug'       => (string) $row['cat_slug'],
            'icon'       => $row['cat_icon'] !== null && $row['cat_icon'] !== '' ? (string) $row['cat_icon'] : null,
            'sort_order' => (int) ($row['cat_sort_order'] ?? 0),
        ],
        'media' => array_map(static function (array $m): array {
            return [
                'id'         => (int) $m['id'],
                'file_path'  => (string) $m['file_path'],
                'file_type'  => (string) ($m['file_type'] ?? 'image'),
                'alt_text'   => $m['alt_text'] !== null && $m['alt_text'] !== '' ? (string) $m['alt_text'] : null,
                'sort_order' => (int) ($m['sort_order'] ?? 0),
                'created_at' => isset($m['created_at']) ? (string) $m['created_at'] : null,
            ];
        }, $mediaRows),
    ];

    if (!empty($row['ville_row_id'])) {
        $payload['ville'] = [
            'id'   => (int) $row['ville_row_id'],
            'nom'  => (string) $row['ville_nom'],
            'slug' => (string) $row['ville_slug'],
        ];
    }
    if (!empty($row['quartier_row_id'])) {
        $payload['quartier'] = [
            'id'       => (int) $row['quartier_row_id'],
            'nom'      => (string) $row['quartier_nom'],
            'slug'     => (string) $row['quartier_slug'],
            'ville_id' => (int) $row['quartier_ville_id'],
        ];
    }

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    );
    if ($json === false) {
        throw new RuntimeException('json_encode failed');
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store');
    echo $json;
} catch (Throwable $e) {
    error_log('[annuaire-local poi-export] ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Export impossible'], JSON_UNESCAPED_UNICODE);
}
