<?php
/** @var array $campaigns */
?>
<style>
.cl { --cl-navy:#1a3c5e; --cl-gold:#c9a84c; font-family:inherit; color:var(--cl-navy); }
.cl-header { display:flex; align-items:center; justify-content:space-between;
             margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.cl-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.cl-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px;
          border-radius:8px; font-size:.88rem; font-weight:600;
          border:none; cursor:pointer; text-decoration:none; transition:.15s; }
.cl-btn-primary { background:var(--cl-navy); color:#fff; }
.cl-btn-primary:hover { background:#0f2237; }

.cl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.cl-card { background:#fff; border-radius:12px; padding:22px;
           box-shadow:0 1px 6px rgba(0,0,0,.07); transition:.15s;
           border-top:3px solid var(--cl-gold); }
.cl-card:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.1); }
.cl-card-nom { font-size:1.05rem; font-weight:700; margin-bottom:6px; }
.cl-card-kw { font-size:.8rem; color:#64748b; margin-bottom:12px; }
.cl-progress { height:6px; border-radius:6px; background:#e2e8f0; margin-bottom:10px; }
.cl-progress-fill { height:100%; border-radius:6px; background:var(--cl-gold); }
.cl-card-meta { display:flex; align-items:center; gap:10px; font-size:.78rem; color:#94a3b8; }
.cl-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.72rem; font-weight:600; }
.cl-badge.draft    { background:#fef3c7; color:#92400e; }
.cl-badge.actif    { background:#d1fae5; color:#065f46; }
.cl-badge.terminé  { background:#f1f5f9; color:#64748b; }
.cl-card-actions { display:flex; gap:8px; margin-top:14px; }
.cl-card-actions a { font-size:.8rem; color:var(--cl-navy); text-decoration:none;
                      padding:5px 12px; border-radius:6px; border:1.5px solid #e2e8f0; }
.cl-card-actions a:hover { background:var(--cl-navy); color:#fff; border-color:var(--cl-navy); }

.cl-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.cl-empty i { font-size:3rem; margin-bottom:12px; display:block; }
</style>

<div class="cl">
  <div class="cl-header">
    <h1>🗂️ Campagnes</h1>
    <a href="/admin?module=redaction&action=campaign_new" class="cl-btn cl-btn-primary">
      <i class="fas fa-plus"></i> Nouvelle campagne
    </a>
  </div>

  <p style="font-size:.85rem;color:#64748b;margin-bottom:20px">
    Une campagne = 1 article pilier + 5 articles niveaux de conscience = 6 contenus coordonnés.
  </p>

  <?php if (empty($campaigns)): ?>
  <div class="cl-empty">
    <i class="fas fa-layer-group"></i>
    <p>Aucune campagne.</p>
    <a href="/admin?module=redaction&action=campaign_new" class="cl-btn cl-btn-primary">
      Créer la première campagne
    </a>
  </div>
  <?php else: ?>
  <div class="cl-grid">
    <?php foreach ($campaigns as $c): ?>
    <div class="cl-card">
      <div class="cl-card-nom"><?= htmlspecialchars($c['nom']) ?></div>
      <?php if ($c['mot_cle']): ?>
        <div class="cl-card-kw"><i class="fas fa-key"></i> <?= htmlspecialchars($c['mot_cle']) ?></div>
      <?php endif; ?>
      <div class="cl-progress">
        <div class="cl-progress-fill"
             style="width:<?= min(100, round((int)$c['nb_articles'] / 6 * 100)) ?>%"></div>
      </div>
      <div class="cl-card-meta">
        <span class="cl-badge <?= htmlspecialchars($c['statut']) ?>"><?= htmlspecialchars($c['statut']) ?></span>
        <span><?= (int)$c['nb_articles'] ?>/6 articles</span>
        <span><?= date('d/m/Y', strtotime($c['updated_at'])) ?></span>
      </div>
      <div class="cl-card-actions">
        <a href="/admin?module=redaction&action=campaign_edit&id=<?= $c['id'] ?>">
          <i class="fas fa-pen"></i> Modifier
        </a>
        <a href="/admin?module=redaction&action=campaign_delete&id=<?= $c['id'] ?>"
           onclick="return confirm('Supprimer cette campagne ?')">
          <i class="fas fa-trash"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
