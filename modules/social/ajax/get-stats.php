<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
$userId = socialUserId();
$reseau = trim((string) ($_GET['reseau'] ?? 'facebook'));
$from = trim((string) ($_GET['from'] ?? date('Y-m-d', strtotime('-30 days'))));
$to = trim((string) ($_GET['to'] ?? date('Y-m-d')));
$stmt = db()->prepare('SELECT date_stat, abonnes, impressions, reach, engagements, clics FROM social_stats WHERE user_id=:u AND reseau=:r AND date_stat BETWEEN :f AND :t ORDER BY date_stat');
$stmt->execute([':u' => $userId, ':r' => $reseau, ':f' => $from, ':t' => $to]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$labels = array_column($rows, 'date_stat');
$data = array_map(fn($r) => (int) $r['reach'], $rows);
if (!$labels) {
    $labels = [date('Y-m-d')];
    $data = [0];
}
socialJsonResponse(['labels' => $labels, 'datasets' => [['label' => ucfirst($reseau) . ' Reach', 'data' => $data]]]);
