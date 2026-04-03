<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
$year = max(2020, (int) ($_GET['year'] ?? date('Y')));
$month = min(12, max(1, (int) ($_GET['month'] ?? date('m'))));
$from = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$to = date('Y-m-t 23:59:59', strtotime($from));
$stmt = db()->prepare('SELECT id, titre, contenu, reseaux, statut, planifie_at FROM social_posts WHERE user_id=:u AND planifie_at BETWEEN :f AND :t ORDER BY planifie_at');
$stmt->execute([':u' => socialUserId(), ':f' => $from, ':t' => $to]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$out = [];
foreach ($rows as $row) {
    $d = substr((string) $row['planifie_at'], 0, 10);
    $row['couleur'] = '#1877f2';
    $r = json_decode((string) $row['reseaux'], true) ?: [];
    if (in_array('instagram', $r, true)) $row['couleur'] = '#e1306c';
    if (in_array('linkedin', $r, true)) $row['couleur'] = '#0077b5';
    $out[$d][] = $row;
}
socialJsonResponse($out);
