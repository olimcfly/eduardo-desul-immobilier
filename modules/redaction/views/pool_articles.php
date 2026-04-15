<?php
/** @var array $articles */
/** @var array $counts */
$filterStatut = $_GET['statut'] ?? '';
$filterType   = $_GET['type']   ?? '';
$filterQ      = $_GET['q']      ?? '';
?>
<style>
.pa { --pa-navy:#1a3c5e; --pa-gold:#c9a84c; font-family:inherit; color:var(--pa-navy); }
.pa-header { display:flex; align-items:center; justify-content:space-between;
             margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pa-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.pa-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px;
          border-radius:8px; font-size:.88rem; font-weight:600;
          border:none; cursor:pointer; text-decoration:none; transition:.15s; }
.pa-btn-primary { background:var(--pa-navy); color:#fff; }
.pa-btn-primary:hover { background:#0f2237; }

/* Filters */
.pa-filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:center; }
.pa-filter-pills { display:flex; gap:6px; flex-wrap:wrap; }
.pa-pill { padding:6px 14px; border-radius:20px; font-size:.8rem; font-weight:600;
           cursor:pointer; text-decoration:none; border:1.5px solid #e2e8f0;
           background:#fff; color:#64748b; transition:.15s; }
.pa-pill.active { background:var(--pa-navy); color:#fff; border-color:var(--pa-navy); }
.pa-search { flex:1; min-width:200px; padding:8px 14px; border-radius:8px;
             border:1.5px solid #cbd5e1; font-size:.88rem; outline:none; }
.pa-search:focus { border-color:var(--pa-gold); }

/* Grid */
.pa-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }
.pa-card { background:#fff; border-radius:12px; padding:20px;
           box-shadow:0 1px 6px rgba(0,0,0,.07);
           border-top:3px solid #e2e8f0; transition:.15s; }
.pa-card:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.1); }
.pa-card.pilier { border-top-color:var(--pa-gold); }
.pa-card.brouillon { border-top-color:#94a3b8; }
.pa-card.planifié { border-top-color:#3b82f6; }
.pa-card.publié { border-top-color:#10b981; }
.pa-card-title { font-size:.95rem; font-weight:700; margin-bottom:8px; line-height:1.3; }
.pa-card-meta { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
.pa-badge { display:inline-block; padding:2px 10px; border-radius:20px;
            font-size:.72rem; font-weight:600; }
.pa-badge.brouillon { background:#fef3c7; color:#92400e; }
.pa-badge.planifié  { background:#dbeafe; color:#1e40af; }
.pa-badge.publié    { background:#d1fae5; color:#065f46; }
.pa-badge.archivé   { background:#f1f5f9; color:#64748b; }
.pa-badge.pilier    { background:#ede9fe; color:#5b21b6; }
.pa-badge.satellite { background:#fce7f3; color:#9d174d; }
.pa-score-bar { height:5px; border-radius:5px; background:#e2e8f0; margin-bottom:12px; }
.pa-score-fill { height:100%; border-radius:5px; background:var(--pa-gold); }
.pa-card-foot { display:flex; align-items:center; justify-content:space-between; }
.pa-card-mots { font-size:.75rem; color:#94a3b8; }
.pa-card-actions { display:flex; gap:8px; }
.pa-card-actions a { font-size:.8rem; color:var(--pa-navy); text-decoration:none;
                      padding:4px 10px; border-radius:6px; border:1.5px solid #e2e8f0;
                      transition:.15s; }
.pa-card-actions a:hover { background:var(--pa-navy); color:#fff; border-color:var(--pa-navy); }

.pa-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.pa-empty i { font-size:3rem; margin-bottom:12px; display:block; }
</style>

<div class="pa">
  <div class="pa-header">
    <h1><i class="fas fa-newspaper"></i> Pool Articles</h1>
    <a href="/admin?module=redaction&action=article_new" class="pa-btn pa-btn-primary">
      <i class="fas fa-plus"></i> Nouvel article
    </a>
  </div>

  <div class="pa-filters">
    <div class="pa-filter-pills">
      <a href="/admin?module=redaction&action=pool_articles" class="pa-pill <?= !$filterStatut && !$filterType ? 'active' : '' ?>">
        Tous (<?= array_sum($counts) ?>)
      </a>
      <?php foreach (['brouillon','planifié','publié','archivé'] as $s): ?>
      <a href="/admin?module=redaction&action=pool_articles&statut=<?= $s ?>" class="pa-pill <?= $filterStatut === $s ? 'active' : '' ?>">
        <?= ucfirst($s) ?> (<?= $counts[$s] ?? 0 ?>)
      </a>
      <?php endforeach; ?>
      <a href="/admin?module=redaction&action=pool_articles&type=pilier" class="pa-pill <?= $filterType === 'pilier' ? 'active' : '' ?>">
        Piliers
      </a>
    </div>
    <form method="GET" action="/admin" style="display:flex;gap:8px;flex:1">
      <input type="hidden" name="module" value="redaction">
      <input type="hidden" name="action" value="pool_articles">
      <?php if ($filterStatut): ?><input type="hidden" name="statut" value="<?= htmlspecialchars($filterStatut) ?>"><?php endif; ?>
      <input type="text" name="q" class="pa-search"
             value="<?= htmlspecialchars($filterQ) ?>"
             placeholder="Rechercher un article…">
    </form>
  </div>

  <?php if (empty($articles)): ?>
  <div class="pa-empty">
    <i class="fas fa-newspaper"></i>
    <p>Aucun article trouvé.</p>
    <a href="/admin?module=redaction&action=article_new" class="pa-btn pa-btn-primary">
      Créer le premier article
    </a>
  </div>
  <?php else: ?>
  <div class="pa-grid">
    <?php foreach ($articles as $a): ?>
    <div class="pa-card <?= $a['type'] === 'pilier' ? 'pilier' : htmlspecialchars($a['statut']) ?>">
      <div class="pa-card-title"><?= htmlspecialchars($a['titre']) ?></div>
      <div class="pa-card-meta">
        <span class="pa-badge <?= $a['type'] === 'pilier' ? 'pilier' : 'satellite' ?>">
          <?= $a['type'] === 'pilier' ? 'Pilier' : 'Satellite' ?>
        </span>
        <span class="pa-badge <?= htmlspecialchars($a['statut']) ?>"><?= htmlspecialchars($a['statut']) ?></span>
        <?php if ($a['niveau_conscience']): ?>
          <span class="pa-badge satellite">N.<?= (int)$a['niveau_conscience'] ?></span>
        <?php endif; ?>
      </div>
      <div class="pa-score-bar">
        <div class="pa-score-fill"
             style="width:<?= (int)$a['score_seo'] ?>%;background:<?= (int)$a['score_seo']>=70?'#10b981':((int)$a['score_seo']>=40?'#f59e0b':'#ef4444') ?>">
        </div>
      </div>
      <div class="pa-card-foot">
        <div class="pa-card-mots">
          SEO <?= (int)$a['score_seo'] ?>/100 · <?= (int)$a['mots'] ?> mots
          <?= $a['date_publication'] ? '· '.date('d/m/Y', strtotime($a['date_publication'])) : '' ?>
        </div>
        <div class="pa-card-actions">
          <a href="/admin?module=redaction&action=article_edit&id=<?= $a['id'] ?>">
            <i class="fas fa-pen"></i>
          </a>
          <a href="/admin?module=redaction&action=article_delete&id=<?= $a['id'] ?>"
             onclick="return confirm('Supprimer ?')">
            <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
