<?php
require_once __DIR__ . '/../includes/_bootstrap.php';
$userId = socialUserId();
$reseau = trim((string) ($_GET['reseau'] ?? ''));
$statut = trim((string) ($_GET['statut'] ?? ''));
$search = trim((string) ($_GET['search'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;

$sql = 'FROM social_posts WHERE user_id = :u';
$params = [':u' => $userId];
if ($reseau !== '') {
    $sql .= ' AND JSON_CONTAINS(reseaux, :r)';
    $params[':r'] = json_encode($reseau);
}
if ($statut !== '' && $statut !== 'tous') {
    $sql .= ' AND statut = :s';
    $params[':s'] = $statut;
}
if ($search !== '') {
    $sql .= ' AND (contenu LIKE :q OR titre LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}
$c = db()->prepare('SELECT COUNT(*) ' . $sql);
$c->execute($params);
$total = (int) $c->fetchColumn();
$pages = (int) ceil($total / $perPage);

$q = db()->prepare('SELECT * ' . $sql . ' ORDER BY COALESCE(planifie_at, created_at) DESC LIMIT :o,:l');
foreach ($params as $k => $v) $q->bindValue($k, $v);
$q->bindValue(':o', ($page - 1) * $perPage, PDO::PARAM_INT);
$q->bindValue(':l', $perPage, PDO::PARAM_INT);
$q->execute();

socialJsonResponse(['posts' => $q->fetchAll(PDO::FETCH_ASSOC), 'total' => $total, 'pages' => $pages]);
