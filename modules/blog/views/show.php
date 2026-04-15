<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../services/PersonaResolver.php';
$website_id = 1;
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, s.nom AS silo_nom FROM blog_articles a LEFT JOIN blog_silos s ON s.id=a.silo_id WHERE a.id=? AND a.website_id=?");
$stmt->execute([$id, $website_id]);
$a = $stmt->fetch();
if (!$a) { header('Location: ../accueil.php'); exit; }

$kw_stmt = $pdo->prepare("SELECT * FROM blog_keywords WHERE article_id=? ORDER BY volume DESC");
$kw_stmt->execute([$id]);
$keywords = $kw_stmt->fetchAll();
$persona = PersonaResolver::resolveFromPersonaId(isset($a['persona_id']) ? (string)$a['persona_id'] : null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($a['titre']) ?></title>
<link rel="stylesheet" href="/modules/blog/assets/blog.css">
</head>
<body>
<div class="cms-wrap">
  <header class="cms-header">
    <a href="accueil.php" class="back">← Retour</a>
    <h1><?= htmlspecialchars($a['titre']) ?></h1>
    <a href="?action=edit&id=<?= $id ?>" class="btn-primary">✏️ Éditer</a>
  </header>

  <div class="article-meta">
    <span class="badge statut-<?= $a['statut'] ?>"><?= $a['statut'] ?></span>
    <span class="badge type-<?= $a['type'] ?>"><?= $a['type'] ?></span>
    <span>Silo : <?= htmlspecialchars($a['silo_nom'] ?? '—') ?></span>
    <span><?= $a['mots'] ?> mots</span>
    <span>Score SEO : <?= $a['score_seo'] ?>/100</span>
  </div>

  <div class="two-col">
    <div class="col-main article-content">
      <?= nl2br(htmlspecialchars($a['contenu'] ?? '')) ?>
    </div>
    <div class="col-side">
      <div class="side-box">
        <h3>SEO</h3>
        <p><strong>Title :</strong> <?= htmlspecialchars($a['seo_title'] ?? '—') ?></p>
        <p><strong>Meta :</strong> <?= htmlspecialchars($a['meta_desc'] ?? '—') ?></p>
        <p><strong>Index :</strong> <?= $a['index_status'] ?></p>
      </div>
      <div class="side-box">
        <h3>Persona</h3>
        <p><strong>Persona détecté :</strong> <?= htmlspecialchars($persona['label']) ?></p>
        <p><strong>ID technique :</strong> <?= htmlspecialchars((string)($persona['persona_id'] ?? '—')) ?></p>
        <p><strong>Niveau de conscience :</strong> <?= htmlspecialchars((string)($persona['niveau_conscience'] ?? '—')) ?></p>
        <p><strong>Insight :</strong> <?= htmlspecialchars($persona['description']) ?></p>
      </div>
      <div class="side-box">
        <h3>Mots-clés (<?= count($keywords) ?>)</h3>
        <?php if ($keywords): ?>
        <table class="cms-table">
          <tr><th>Mot-clé</th><th>Volume</th><th>Pos.</th><th>Statut</th></tr>
          <?php foreach ($keywords as $k): ?>
          <tr>
            <td><?= htmlspecialchars($k['mot_cle']) ?></td>
            <td><?= number_format($k['volume']) ?></td>
            <td><?= $k['position_serp'] ?? '—' ?></td>
            <td><span class="badge"><?= $k['statut'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>Aucun mot-clé associé.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>
