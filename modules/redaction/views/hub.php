<?php
/** @var array $counts  statuts articles */
/** @var array $pubCounts par réseau */
/** @var int   $campaignsCount */
/** @var array $recentArticles */
?>
<style>
.rd { --rd-navy:#1a3c5e; --rd-gold:#c9a84c; --rd-bg:#f0f4f8; --rd-card:#fff;
      --rd-green:#10b981; --rd-blue:#3b82f6; --rd-orange:#f59e0b; --rd-red:#ef4444; }
.rd { font-family:inherit; color:var(--rd-navy); }

.rd-header { display:flex; align-items:center; justify-content:space-between;
             margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.rd-header h1 { font-size:1.7rem; font-weight:700; margin:0;
                display:flex; align-items:center; gap:10px; }
.rd-header h1 span.dot { width:10px;height:10px;border-radius:50%;
                          background:var(--rd-gold);display:inline-block; }
.rd-actions { display:flex; gap:10px; flex-wrap:wrap; }
.rd-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px;
          border-radius:8px; font-size:.9rem; font-weight:600;
          text-decoration:none; border:none; cursor:pointer; transition:.15s; }
.rd-btn-primary { background:var(--rd-navy); color:#fff; }
.rd-btn-primary:hover { background:#0f2237; }
.rd-btn-gold { background:var(--rd-gold); color:#fff; }
.rd-btn-gold:hover { background:#b8922e; }
.rd-btn-outline { background:#fff; color:var(--rd-navy);
                  border:2px solid var(--rd-navy); }
.rd-btn-outline:hover { background:var(--rd-navy); color:#fff; }

/* KPI grid */
.rd-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr));
               gap:16px; margin-bottom:28px; }
.rd-kpi { background:var(--rd-card); border-radius:12px; padding:20px 18px;
          box-shadow:0 1px 6px rgba(0,0,0,.07);
          border-left:4px solid var(--rd-blue); }
.rd-kpi.green { border-color:var(--rd-green); }
.rd-kpi.orange { border-color:var(--rd-orange); }
.rd-kpi.gold { border-color:var(--rd-gold); }
.rd-kpi.red { border-color:var(--rd-red); }
.rd-kpi-num { font-size:2rem; font-weight:800; line-height:1; }
.rd-kpi-lbl { font-size:.78rem; color:#64748b; margin-top:4px; }

/* Section */
.rd-section { margin-bottom:32px; }
.rd-section-title { font-size:.8rem; font-weight:700; text-transform:uppercase;
                    letter-spacing:.08em; color:#94a3b8; margin-bottom:14px; }

/* Quick links grid */
.rd-links-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
                 gap:14px; }
.rd-link-card { background:var(--rd-card); border-radius:12px; padding:20px;
                box-shadow:0 1px 6px rgba(0,0,0,.07);
                text-decoration:none; color:var(--rd-navy); transition:.15s;
                border:2px solid transparent; display:block; }
.rd-link-card:hover { border-color:var(--rd-gold); transform:translateY(-2px); }
.rd-link-card .icon { font-size:1.5rem; margin-bottom:8px; }
.rd-link-card .lbl { font-weight:700; font-size:1rem; }
.rd-link-card .hint { font-size:.78rem; color:#64748b; margin-top:3px; }

/* Recent articles table */
.rd-table-wrap { background:var(--rd-card); border-radius:12px;
                 box-shadow:0 1px 6px rgba(0,0,0,.07); overflow:hidden; }
.rd-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.rd-table th { background:#f8fafc; padding:10px 16px; text-align:left;
               font-weight:600; color:#64748b; font-size:.75rem; text-transform:uppercase; }
.rd-table td { padding:12px 16px; border-top:1px solid #f1f5f9; }
.rd-table tr:hover td { background:#fafafa; }
.rd-badge { display:inline-block; padding:2px 10px; border-radius:20px;
            font-size:.72rem; font-weight:600; }
.rd-badge.brouillon { background:#fef3c7; color:#92400e; }
.rd-badge.planifié  { background:#dbeafe; color:#1e40af; }
.rd-badge.publié    { background:#d1fae5; color:#065f46; }
.rd-badge.archivé   { background:#f1f5f9; color:#64748b; }
.rd-badge.pilier    { background:#ede9fe; color:#5b21b6; }
</style>

<div class="rd">
  <div class="rd-header">
    <h1><span class="dot"></span> Rédaction</h1>
    <div class="rd-actions">
      <a href="/admin?module=redaction&action=article_new" class="rd-btn rd-btn-primary">
        <i class="fas fa-plus"></i> Nouvel article
      </a>
      <a href="/admin?module=redaction&action=campaign_new" class="rd-btn rd-btn-gold">
        <i class="fas fa-layer-group"></i> Nouvelle campagne
      </a>
    </div>
  </div>

  <!-- KPIs style dashboard -->
  <?php
  $artTotal = ($counts['brouillon'] ?? 0) + ($counts['planifié'] ?? 0) + ($counts['publié'] ?? 0) + ($counts['archivé'] ?? 0);
  ?>
  <div class="db-kpi-grid" style="margin-bottom:28px">

    <a href="/admin?module=redaction&action=pool_articles" class="db-kpi accent-gold">
      <div class="db-kpi-icon">✍️</div>
      <div class="db-kpi-val"><?= $artTotal ?></div>
      <div class="db-kpi-label">Articles au total</div>
      <div class="db-kpi-sub"><?= $counts['publié'] ?? 0 ?> publiés · <?= $counts['brouillon'] ?? 0 ?> brouillons</div>
    </a>

    <a href="/admin?module=redaction&action=pool_articles&statut=publi%C3%A9" class="db-kpi accent-green">
      <div class="db-kpi-icon">🌐</div>
      <div class="db-kpi-val"><?= $counts['publié'] ?? 0 ?></div>
      <div class="db-kpi-label">Publiés en ligne</div>
      <div class="db-kpi-sub"><?= $counts['planifié'] ?? 0 ?> planifiés à venir</div>
    </a>

    <a href="/admin?module=redaction&action=campaigns" class="db-kpi accent-blue">
      <div class="db-kpi-icon">📂</div>
      <div class="db-kpi-val"><?= $campaignsCount ?></div>
      <div class="db-kpi-label">Campagnes éditoriales</div>
      <div class="db-kpi-sub">Silos de contenu SEO</div>
    </a>

    <a href="/admin?module=redaction&action=pool_gmb" class="db-kpi" style="border-left-color:#ea4335">
      <div class="db-kpi-icon">📍</div>
      <div class="db-kpi-val"><?= $pubCounts['gmb'] ?? 0 ?></div>
      <div class="db-kpi-label">Posts GMB</div>
      <div class="db-kpi-sub">Google My Business</div>
    </a>

    <a href="/admin?module=redaction&action=pool_gmb&reseau=facebook" class="db-kpi" style="border-left-color:#1877f2">
      <div class="db-kpi-icon">📘</div>
      <div class="db-kpi-val"><?= $pubCounts['facebook'] ?? 0 ?></div>
      <div class="db-kpi-label">Posts Facebook</div>
      <div class="db-kpi-sub"><?= ($pubCounts['instagram'] ?? 0) ?> Instagram · <?= ($pubCounts['linkedin'] ?? 0) ?> LinkedIn</div>
    </a>

  </div>

  <!-- Quick links -->
  <div class="rd-section">
    <div class="rd-section-title">Vues</div>
    <div class="rd-links-grid">
      <a href="/admin?module=redaction&action=pool_articles" class="rd-link-card">
        <div class="icon">📰</div>
        <div class="lbl">Pool Articles</div>
        <div class="hint">Tous vos articles</div>
      </a>
      <a href="/admin?module=redaction&action=pool_gmb" class="rd-link-card">
        <div class="icon">📍</div>
        <div class="lbl">Pool GMB</div>
        <div class="hint">Publications Google My Business</div>
      </a>
      <a href="/admin?module=redaction&action=journal" class="rd-link-card">
        <div class="icon">📅</div>
        <div class="lbl">Journal</div>
        <div class="hint">Timeline articles + réseaux</div>
      </a>
      <a href="/admin?module=redaction&action=campaigns" class="rd-link-card">
        <div class="icon">🗂️</div>
        <div class="lbl">Campagnes</div>
        <div class="hint">Pilier + 5 niveaux de conscience</div>
      </a>
    </div>
  </div>

  <!-- Recent articles -->
  <?php if (!empty($recentArticles)): ?>
  <div class="rd-section">
    <div class="rd-section-title">Articles récents</div>
    <div class="rd-table-wrap">
      <table class="rd-table">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Type</th>
            <th>Statut</th>
            <th>Score SEO</th>
            <th>Mots</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentArticles as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['titre']) ?></td>
            <td>
              <span class="rd-badge <?= $a['type'] === 'pilier' ? 'pilier' : 'brouillon' ?>">
                <?= $a['type'] === 'pilier' ? 'Pilier' : 'Satellite' ?>
              </span>
            </td>
            <td><span class="rd-badge <?= htmlspecialchars($a['statut']) ?>"><?= htmlspecialchars($a['statut']) ?></span></td>
            <td>
              <span style="font-weight:700;color:<?= (int)$a['score_seo'] >= 70 ? '#10b981' : ((int)$a['score_seo'] >= 40 ? '#f59e0b' : '#ef4444') ?>">
                <?= (int)$a['score_seo'] ?>/100
              </span>
            </td>
            <td><?= (int)$a['mots'] ?></td>
            <td>
              <a href="/admin?module=redaction&action=article_edit&id=<?= $a['id'] ?>"
                 style="color:var(--rd-navy);font-size:.82rem;text-decoration:none;">
                <i class="fas fa-pen"></i> Modifier
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
