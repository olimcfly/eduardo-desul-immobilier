<?php
require_once __DIR__ . '/../../../config/database.php';
$website_id = 1;
$id = (int)($_GET['id'] ?? 0);
$a  = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_articles WHERE id=? AND website_id=?");
    $stmt->execute([$id, $website_id]);
    $a = $stmt->fetch();
    if (!$a) { header('Location: ../accueil.php'); exit; }
}

$silos = $pdo->prepare("SELECT id, nom FROM blog_silos WHERE website_id=? AND statut='actif' ORDER BY nom");
$silos->execute([$website_id]);
$silos = $silos->fetchAll();

$v = fn($k, $d='') => htmlspecialchars($a[$k] ?? $d);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= $id ? 'Éditer' : 'Nouvel' ?> article</title>
<link rel="stylesheet" href="/modules/blog/assets/blog.css">
</head>
<body>
<div class="cms-wrap">
  <header class="cms-header">
    <a href="accueil.php" class="back">← Retour</a>
    <h1><?= $id ? 'Éditer l\'article' : 'Nouvel article' ?></h1>
  </header>

  <form method="post" action="controllers/save.php" class="article-form">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="form-grid">
      <div class="col-main">
        <div class="form-group">
          <label>Titre *</label>
          <input type="text" name="titre" value="<?= $v('titre') ?>" required id="titre-input">
        </div>
        <div class="form-group">
          <label>Slug</label>
          <input type="text" name="slug" value="<?= $v('slug') ?>" id="slug-input">
        </div>
        <div class="form-group">
          <label>H1</label>
          <input type="text" name="h1" value="<?= $v('h1') ?>">
        </div>
        <div class="form-group">
          <label>Contenu</label>
          <textarea name="contenu" id="contenu" rows="20"><?= $v('contenu') ?></textarea>
        </div>
      </div>

      <div class="col-side">
        <div class="side-box">
          <h3>Publication</h3>
          <div class="form-group">
            <label>Statut</label>
            <select name="statut">
              <?php foreach(['brouillon','planifié','publié','archivé'] as $s): ?>
              <option value="<?= $s ?>" <?= ($a['statut']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Date de publication</label>
            <input type="datetime-local" name="date_publication" value="<?= $v('date_publication') ?>">
          </div>
          <div class="form-group">
            <label>Type</label>
            <select name="type">
              <option value="satellite" <?= ($a['type']??'')==='satellite'?'selected':'' ?>>Satellite</option>
              <option value="pilier" <?= ($a['type']??'')==='pilier'?'selected':'' ?>>Pilier</option>
            </select>
          </div>
          <div class="form-group">
            <label>Indexation</label>
            <select name="index_status">
              <option value="index" <?= ($a['index_status']??'')==='index'?'selected':'' ?>>Index</option>
              <option value="noindex" <?= ($a['index_status']??'')==='noindex'?'selected':'' ?>>Noindex</option>
            </select>
          </div>
        </div>

        <div class="side-box">
          <h3>Organisation</h3>
          <div class="form-group">
            <label>Silo</label>
            <select name="silo_id">
              <option value="">— Aucun —</option>
              <?php foreach ($silos as $s): ?>
              <option value="<?= $s['id'] ?>" <?= ($a['silo_id']??'')==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Niveau de conscience (1-5)</label>
            <input type="number" name="niveau_conscience" min="1" max="5" value="<?= $v('niveau_conscience') ?>">
          </div>
        </div>

        <div class="side-box">
          <h3>SEO</h3>
          <div class="form-group">
            <label>SEO Title <small>(max 70)</small></label>
            <input type="text" name="seo_title" maxlength="70" value="<?= $v('seo_title') ?>">
            <div class="char-count" id="seo-title-count">0/70</div>
          </div>
          <div class="form-group">
            <label>Meta description <small>(max 160)</small></label>
            <textarea name="meta_desc" maxlength="160" rows="3"><?= $v('meta_desc') ?></textarea>
            <div class="char-count" id="meta-count">0/160</div>
          </div>
        </div>

        <button type="submit" class="btn-primary btn-full">💾 Enregistrer</button>
      </div>
    </div>
  </form>
</div>
<script>
// Auto-slug
document.getElementById('titre-input').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    const s = document.getElementById('slug-input');
    if (!s.dataset.manual) s.value = slug;
});
document.getElementById('slug-input').addEventListener('input', function() {
    this.dataset.manual = '1';
});
// Char counters
function charCount(inputId, countId, max) {
    const el = document.querySelector('[name="' + inputId + '"]');
    const ct = document.getElementById(countId);
    if (!el || !ct) return;
    const update = () => ct.textContent = el.value.length + '/' + max;
    el.addEventListener('input', update); update();
}
charCount('seo_title', 'seo-title-count', 70);
charCount('meta_desc', 'meta-count', 160);
</script>
</body>
</html>
