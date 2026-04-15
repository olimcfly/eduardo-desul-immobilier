<?php
/** @var array $journal */
$reseauMeta = [
    'gmb'       => ['icon'=>'📍', 'label'=>'GMB',       'color'=>'#4285f4'],
    'facebook'  => ['icon'=>'📘', 'label'=>'Facebook',  'color'=>'#1877f2'],
    'linkedin'  => ['icon'=>'💼', 'label'=>'LinkedIn',  'color'=>'#0a66c2'],
    'instagram' => ['icon'=>'📷', 'label'=>'Instagram', 'color'=>'#e1306c'],
];
$statutColors = [
    'brouillon' => '#94a3b8',
    'planifié'  => '#3b82f6',
    'publié'    => '#10b981',
    'archivé'   => '#cbd5e1',
    'draft'     => '#94a3b8',
];
?>
<style>
.jn { --jn-navy:#1a3c5e; --jn-gold:#c9a84c; font-family:inherit; color:var(--jn-navy); }
.jn-header { display:flex; align-items:center; justify-content:space-between;
             margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.jn-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.jn-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px;
          border-radius:8px; font-size:.88rem; font-weight:600;
          border:none; cursor:pointer; text-decoration:none; transition:.15s; }
.jn-btn-outline { background:#fff; color:var(--jn-navy); border:1.5px solid #cbd5e1; }
.jn-btn-outline:hover { border-color:var(--jn-navy); }

/* Timeline */
.jn-timeline { position:relative; padding-left:36px; }
.jn-timeline::before { content:''; position:absolute; left:12px; top:0; bottom:0;
                         width:2px; background:#e2e8f0; border-radius:2px; }

/* Date separator */
.jn-date-sep { position:relative; margin:24px 0 12px -36px; }
.jn-date-sep span { background:var(--jn-navy); color:#fff; font-size:.72rem; font-weight:700;
                     padding:4px 12px; border-radius:20px; text-transform:uppercase; }

/* Entry */
.jn-entry { position:relative; margin-bottom:16px; }
.jn-dot { position:absolute; left:-30px; top:12px; width:14px; height:14px;
           border-radius:50%; border:2px solid #fff;
           box-shadow:0 0 0 2px #e2e8f0; background:#94a3b8; }
.jn-card { background:#fff; border-radius:10px; padding:16px 18px;
           box-shadow:0 1px 6px rgba(0,0,0,.07);
           border-left:3px solid #e2e8f0; transition:.15s; }
.jn-card:hover { transform:translateX(2px); }
.jn-card-head { display:flex; align-items:center; gap:10px; margin-bottom:6px; flex-wrap:wrap; }
.jn-card-type { font-size:.72rem; font-weight:700; text-transform:uppercase;
                letter-spacing:.07em; padding:2px 8px; border-radius:4px; }
.jn-card-type.article    { background:#ede9fe; color:#5b21b6; }
.jn-card-type.publication { background:#dbeafe; color:#1e40af; }
.jn-card-title { font-size:.9rem; font-weight:700; flex:1; }
.jn-card-date { font-size:.75rem; color:#94a3b8; margin-left:auto; }
.jn-card-meta { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.jn-badge { display:inline-block; padding:2px 9px; border-radius:20px; font-size:.72rem; font-weight:600; }
.jn-card-actions { margin-top:8px; }
.jn-card-actions a { font-size:.78rem; color:var(--jn-navy); text-decoration:none;
                      padding:3px 10px; border-radius:6px; border:1px solid #e2e8f0; }
.jn-card-actions a:hover { background:var(--jn-navy); color:#fff; }

.jn-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.jn-empty i { font-size:3rem; margin-bottom:12px; display:block; }
</style>

<div class="jn">
  <div class="jn-header">
    <h1>📅 Journal de contenu</h1>
    <a href="/admin?module=redaction" class="jn-btn jn-btn-outline">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>

  <?php if (empty($journal)): ?>
  <div class="jn-empty">
    <i class="fas fa-calendar-days"></i>
    <p>Aucun contenu pour le moment.</p>
  </div>
  <?php else: ?>
  <div class="jn-timeline">
    <?php
    $prevDate = null;
    foreach ($journal as $entry):
        $date = date('Y-m-d', strtotime($entry['event_at']));
        if ($date !== $prevDate):
            $prevDate = $date;
    ?>
    <div class="jn-date-sep">
      <span><?= date('d F Y', strtotime($date)) ?></span>
    </div>
    <?php endif; ?>

    <div class="jn-entry">
      <?php
      $isArticle = $entry['type'] === 'article';
      $color = $isArticle
          ? ($entry['sous_type'] === 'pilier' ? '#c9a84c' : '#8b5cf6')
          : ($reseauMeta[$entry['reseau'] ?? '']['color'] ?? '#3b82f6');
      ?>
      <div class="jn-dot" style="background:<?= $color ?>"></div>
      <div class="jn-card" style="border-left-color:<?= $color ?>">
        <div class="jn-card-head">
          <span class="jn-card-type <?= $isArticle ? 'article' : 'publication' ?>">
            <?php if ($isArticle): ?>
              <?= $entry['sous_type'] === 'pilier' ? '📌 Pilier' : '📄 Article' ?>
            <?php else: ?>
              <?= ($reseauMeta[$entry['reseau'] ?? '']['icon'] ?? '📢') . ' ' . ($reseauMeta[$entry['reseau'] ?? '']['label'] ?? $entry['reseau']) ?>
            <?php endif; ?>
          </span>
          <span class="jn-card-title"><?= htmlspecialchars(mb_substr($entry['titre'] ?? '', 0, 80)) ?></span>
          <span class="jn-card-date"><?= date('H:i', strtotime($entry['event_at'])) ?></span>
        </div>
        <div class="jn-card-meta">
          <span class="jn-badge"
                style="background:<?= $statutColors[$entry['statut']] ?? '#94a3b8' ?>22;color:<?= $statutColors[$entry['statut']] ?? '#94a3b8' ?>">
            <?= htmlspecialchars($entry['statut']) ?>
          </span>
        </div>
        <div class="jn-card-actions">
          <?php if ($isArticle): ?>
            <a href="/admin?module=redaction&action=article_edit&id=<?= (int)$entry['id'] ?>">
              <i class="fas fa-pen"></i> Modifier
            </a>
          <?php else: ?>
            <a href="/admin?module=redaction&action=pool_gmb&reseau=<?= htmlspecialchars($entry['reseau'] ?? '') ?>">
              <i class="fas fa-eye"></i> Voir
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
