<?php
require_once __DIR__ . '/../../../config/database.php';
$website_id = 1;

$statut_filter = $_GET['statut'] ?? '';
$search        = $_GET['q'] ?? '';

$sql    = "SELECT a.*, s.nom AS silo_nom FROM blog_articles a LEFT JOIN blog_silos s ON s.id=a.silo_id WHERE a.website_id=?";
$params = [$website_id];

if ($statut_filter) { $sql .= " AND a.statut=?"; $params[] = $statut_filter; }
if ($search)        { $sql .= " AND a.titre LIKE ?"; $params[] = "%$search%"; }

$sql .= " ORDER BY a.updated_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$stats_stmt = $pdo->prepare("SELECT statut, COUNT(*) as nb FROM blog_articles WHERE website_id=? GROUP BY statut");
$stats_stmt->execute([$website_id]);
$stats = $stats_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Blog CMS</title>
<link rel="stylesheet" href="/modules/blog/assets/blog.css">
</head>
<body>
<div class="cms-wrap">
  <header class="cms-header">
    <h1>📝 Blog CMS</h1>
    <a href="?action=new" class="btn-primary">+ Nouvel article</a>
  </header>

  <div class="stats-bar">
    <span>Total : <strong><?= array_sum($stats) ?></strong></span>
    <span>Publiés : <strong><?= $stats['publié'] ?? 0 ?></strong></span>
    <span>Brouillons : <strong><?= $stats['brouillon'] ?? 0 ?></strong></span>
    <span>Planifiés : <strong><?= $stats['planifié'] ?? 0 ?></strong></span>
  </div>

  <form class="filters" method="get">
    <input type="hidden" name="action" value="list">
    <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
    <select name="statut">
      <option value="">Tous statuts</option>
      <?php foreach(['brouillon','planifié','publié','archivé'] as $s): ?>
      <option value="<?= $s ?>" <?= $statut_filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filtrer</button>
  </form>

  <?php if (isset($_GET['success'])): ?>
  <div class="alert-success">✅ Article sauvegardé avec succès.</div>
  <?php endif; ?>

  <table class="cms-table">
    <thead>
      <tr>
        <th>Titre</th><th>Silo</th><th>Type</th><th>Statut</th>
        <th>Mots</th><th>Score SEO</th><th>Mis à jour</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($articles)): ?>
      <tr><td colspan="8" style="text-align:center;padding:2rem;color:#9ca3af;">Aucun article trouvé</td></tr>
    <?php else: ?>
      <?php foreach ($articles as $a): ?>
      <tr>
        <td><a href="?action=view&id=<?= $a['id'] ?>"><?= htmlspecialchars($a['titre']) ?></a></td>
        <td><?= htmlspecialchars($a['silo_nom'] ?? '—') ?></td>
        <td><span class="badge type-<?= $a['type'] ?>"><?= $a['type'] ?></span></td>
        <td><span class="badge statut-<?= $a['statut'] ?>"><?= $a['statut'] ?></span></td>
        <td><?= $a['mots'] ?></td>
        <td>
          <div class="score-bar">
            <div class="score-fill" style="width:<?= $a['score_seo'] ?>%"></div>
            <span><?= $a['score_seo'] ?>/100</span>
          </div>
        </td>
        <td><?= date('d/m/Y H:i', strtotime($a['updated_at'])) ?></td>
        <td class="actions">
          <a href="?action=edit&id=<?= $a['id'] ?>" title="Éditer">✏️</a>
          <a href="?action=delete&id=<?= $a['id'] ?>" title="Supprimer" onclick="return confirm('Supprimer cet article ?')">🗑️</a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
