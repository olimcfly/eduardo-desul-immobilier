<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/PerformanceAudit.php';

if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$audit = new PerformanceAudit(db(), $userId);
$audits = $audit->listAudits(10);
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Performance SEO</title><link rel="stylesheet" href="/modules/seo/assets/seo.css"></head>
<body><div class="seo-wrap"><h1>Audit performance technique</h1>
<form id="audit-form"><input name="url_tested" value="<?= htmlspecialchars((string)setting('site_url', 'https://example.com', $userId)) ?>" required><button type="submit">Lancer audit</button></form>
<table><thead><tr><th>Date</th><th>URL</th><th>Perf</th><th>SEO</th></tr></thead><tbody><?php foreach ($audits as $row): ?><tr><td><?= htmlspecialchars((string)$row['created_at']) ?></td><td><?= htmlspecialchars((string)$row['url_tested']) ?></td><td><?= (int)$row['score_perf'] ?></td><td><?= (int)$row['score_seo'] ?></td></tr><?php endforeach; ?></tbody></table>
</div><script src="/modules/seo/assets/seo.js"></script></body></html>
