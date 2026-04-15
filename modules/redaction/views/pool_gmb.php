<?php
/** @var array $publications */
/** @var array $pubCounts */
$filterReseau = $_GET['reseau'] ?? '';
$filterStatut = $_GET['statut'] ?? '';

$reseauMeta = [
    'gmb'       => ['icon'=>'📍', 'label'=>'Google My Business', 'color'=>'#4285f4'],
    'facebook'  => ['icon'=>'📘', 'label'=>'Facebook',           'color'=>'#1877f2'],
    'linkedin'  => ['icon'=>'💼', 'label'=>'LinkedIn',           'color'=>'#0a66c2'],
    'instagram' => ['icon'=>'📷', 'label'=>'Instagram',          'color'=>'#e1306c'],
];
?>
<style>
.pg { --pg-navy:#1a3c5e; --pg-gold:#c9a84c; font-family:inherit; color:var(--pg-navy); }
.pg-header { display:flex; align-items:center; justify-content:space-between;
             margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.pg-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px;
          border-radius:8px; font-size:.88rem; font-weight:600;
          border:none; cursor:pointer; text-decoration:none; transition:.15s; }
.pg-btn-primary { background:var(--pg-navy); color:#fff; }
.pg-btn-primary:hover { background:#0f2237; }
.pg-btn-sm { padding:6px 12px; font-size:.78rem; }
.pg-btn-outline { background:#fff; color:var(--pg-navy); border:1.5px solid #cbd5e1; }
.pg-btn-outline:hover { border-color:var(--pg-navy); }
.pg-btn-danger { background:#fee2e2; color:#dc2626; border:none; }

.pg-filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; }
.pg-pill { padding:6px 14px; border-radius:20px; font-size:.8rem; font-weight:600;
           cursor:pointer; text-decoration:none; border:1.5px solid #e2e8f0;
           background:#fff; color:#64748b; transition:.15s; }
.pg-pill.active { background:var(--pg-navy); color:#fff; border-color:var(--pg-navy); }

/* Stats row */
.pg-stats { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.pg-stat { background:#fff; border-radius:10px; padding:14px 18px;
           box-shadow:0 1px 6px rgba(0,0,0,.07); flex:1; min-width:130px;
           display:flex; align-items:center; gap:12px; }
.pg-stat-icon { font-size:1.4rem; }
.pg-stat-num { font-size:1.4rem; font-weight:800; line-height:1; }
.pg-stat-lbl { font-size:.72rem; color:#64748b; margin-top:2px; }

/* Cards */
.pg-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.pg-pub-card { background:#fff; border-radius:12px; padding:20px;
               box-shadow:0 1px 6px rgba(0,0,0,.07); border-left:4px solid #e2e8f0; }
.pg-pub-card-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
.pg-pub-card-head .icon { font-size:1.2rem; }
.pg-pub-card-head .net  { font-weight:700; font-size:.88rem; }
.pg-pub-card-head .date { margin-left:auto; font-size:.75rem; color:#94a3b8; }
.pg-pub-body { font-size:.83rem; color:#475569; line-height:1.6;
               max-height:100px; overflow:hidden; position:relative; }
.pg-pub-body::after { content:''; position:absolute; bottom:0; left:0; right:0;
                       height:30px; background:linear-gradient(transparent,#fff); }
.pg-pub-source { font-size:.75rem; color:#94a3b8; margin-top:10px; margin-bottom:10px; }
.pg-pub-source a { color:var(--pg-navy); text-decoration:none; }
.pg-pub-foot { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.pg-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:600; }
.pg-badge.draft    { background:#fef3c7; color:#92400e; }
.pg-badge.planifié { background:#dbeafe; color:#1e40af; }
.pg-badge.publié   { background:#d1fae5; color:#065f46; }

.pg-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.pg-empty i { font-size:3rem; margin-bottom:12px; display:block; }
</style>

<div class="pg">
  <div class="pg-header">
    <h1>📍 Pool Publications</h1>
    <a href="/admin?module=redaction" class="pg-btn pg-btn-outline">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>

  <!-- Stats -->
  <div class="pg-stats">
    <?php foreach ($reseauMeta as $key => $meta): ?>
    <div class="pg-stat">
      <div class="pg-stat-icon"><?= $meta['icon'] ?></div>
      <div>
        <div class="pg-stat-num" style="color:<?= $meta['color'] ?>"><?= (int)($pubCounts[$key] ?? 0) ?></div>
        <div class="pg-stat-lbl"><?= $meta['label'] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Filters -->
  <div class="pg-filters">
    <a href="/admin?module=redaction&action=pool_gmb" class="pg-pill <?= !$filterReseau ? 'active' : '' ?>">
      Tous réseaux
    </a>
    <?php foreach ($reseauMeta as $key => $meta): ?>
    <a href="/admin?module=redaction&action=pool_gmb&reseau=<?= $key ?>" class="pg-pill <?= $filterReseau === $key ? 'active' : '' ?>">
      <?= $meta['icon'] ?> <?= $meta['label'] ?>
    </a>
    <?php endforeach; ?>
    <span style="width:1px;background:#e2e8f0;height:24px;display:inline-block;margin:0 4px"></span>
    <a href="/admin?module=redaction&action=pool_gmb<?= $filterReseau ? '&reseau='.$filterReseau : '' ?>&statut=draft" class="pg-pill <?= $filterStatut==='draft' ? 'active' : '' ?>">Brouillons</a>
    <a href="/admin?module=redaction&action=pool_gmb<?= $filterReseau ? '&reseau='.$filterReseau : '' ?>&statut=planifié" class="pg-pill <?= $filterStatut==='planifié' ? 'active' : '' ?>">Planifiés</a>
    <a href="/admin?module=redaction&action=pool_gmb<?= $filterReseau ? '&reseau='.$filterReseau : '' ?>&statut=publié" class="pg-pill <?= $filterStatut==='publié' ? 'active' : '' ?>">Publiés</a>
  </div>

  <?php if (empty($publications)): ?>
  <div class="pg-empty">
    <i class="fas fa-paper-plane"></i>
    <p>Aucune publication.</p>
    <p style="font-size:.85rem">Créez ou enregistrez un article pour générer automatiquement des posts réseaux.</p>
  </div>
  <?php else: ?>
  <div class="pg-grid">
    <?php foreach ($publications as $pub): ?>
    <?php $meta = $reseauMeta[$pub['reseau']] ?? ['icon'=>'📢','label'=>$pub['reseau'],'color'=>'#64748b']; ?>
    <div class="pg-pub-card" style="border-left-color:<?= $meta['color'] ?>">
      <div class="pg-pub-card-head">
        <span class="icon"><?= $meta['icon'] ?></span>
        <span class="net" style="color:<?= $meta['color'] ?>"><?= $meta['label'] ?></span>
        <span class="date">
          <?= $pub['planifie_at'] ? date('d/m/Y H:i', strtotime($pub['planifie_at'])) : date('d/m/Y', strtotime($pub['created_at'])) ?>
        </span>
      </div>
      <?php if ($pub['article_titre']): ?>
      <div class="pg-pub-source">
        Source : <a href="/admin?module=redaction&action=article_edit&id=<?= (int)$pub['article_id'] ?>">
          <?= htmlspecialchars(mb_substr($pub['article_titre'], 0, 60)) ?>
        </a>
      </div>
      <?php endif; ?>
      <div class="pg-pub-body"><?= nl2br(htmlspecialchars($pub['contenu'])) ?></div>
      <div style="margin-top:10px"></div>
      <div class="pg-pub-foot">
        <span class="pg-badge <?= htmlspecialchars($pub['statut']) ?>"><?= htmlspecialchars($pub['statut']) ?></span>
        <a href="/admin?module=redaction&action=pub_delete&id=<?= $pub['id'] ?>"
           class="pg-btn pg-btn-danger pg-btn-sm"
           onclick="return confirm('Supprimer ce post ?')" style="margin-left:auto">
          <i class="fas fa-trash"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
