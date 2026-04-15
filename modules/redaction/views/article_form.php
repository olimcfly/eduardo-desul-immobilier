<?php
/** @var array|null $article  null = new */
/** @var array      $keywords */
/** @var array      $allArticles for internal linking */
?>
<style>
.af { --af-navy:#1a3c5e; --af-gold:#c9a84c; --af-bg:#f0f4f8;
      font-family:inherit; color:var(--af-navy); }

.af-header { display:flex; align-items:center; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.af-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.af-breadcrumb { font-size:.82rem; color:#64748b; }
.af-breadcrumb a { color:var(--af-navy); text-decoration:none; }

/* Tabs */
.af-tabs { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:24px; flex-wrap:wrap; }
.af-tab { padding:10px 22px; font-size:.88rem; font-weight:600; color:#64748b;
          cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px;
          background:none; border-top:none; border-left:none; border-right:none;
          transition:.15s; }
.af-tab.active { color:var(--af-navy); border-bottom-color:var(--af-gold); }
.af-tab-panel { display:none; }
.af-tab-panel.active { display:block; }

/* Form elements */
.af-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.af-grid.full { grid-template-columns:1fr; }
@media(max-width:700px){ .af-grid { grid-template-columns:1fr; } }
.af-group { display:flex; flex-direction:column; gap:6px; }
.af-label { font-size:.82rem; font-weight:600; color:#475569; }
.af-label .opt { font-weight:400; color:#94a3b8; font-size:.78rem; }
.af-input, .af-select, .af-textarea {
  padding:10px 14px; border:1.5px solid #cbd5e1; border-radius:8px;
  font-size:.9rem; font-family:inherit; color:var(--af-navy);
  outline:none; transition:.15s; background:#fff; width:100%; box-sizing:border-box; }
.af-input:focus, .af-select:focus, .af-textarea:focus { border-color:var(--af-gold); }
.af-textarea { resize:vertical; min-height:160px; line-height:1.6; }
.af-textarea.tall { min-height:340px; }
.af-counter { font-size:.75rem; color:#94a3b8; text-align:right; }
.af-counter.warn { color:#f59e0b; }
.af-counter.ok { color:#10b981; }

/* SEO score */
.af-seo-score { display:flex; align-items:center; gap:14px; background:#fff;
                border-radius:10px; padding:16px 20px;
                box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:20px; }
.af-score-bar { flex:1; height:10px; border-radius:10px; background:#e2e8f0; overflow:hidden; }
.af-score-fill { height:100%; border-radius:10px; background:var(--af-gold); transition:.4s; }
.af-score-num { font-size:1.3rem; font-weight:800; min-width:52px; text-align:right; }
.af-criteria { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
               gap:8px; margin-top:16px; }
.af-criteria-item { display:flex; align-items:center; gap:8px; font-size:.8rem; }
.af-criteria-item i { width:16px; text-align:center; }
.af-criteria-item.ok i { color:#10b981; }
.af-criteria-item.nok i { color:#ef4444; }

/* Maillage */
.af-link-list { display:flex; flex-direction:column; gap:8px; margin-top:8px; }
.af-link-item { display:flex; align-items:center; gap:10px; background:#f8fafc;
                border-radius:8px; padding:10px 14px; font-size:.85rem; }
.af-link-item .af-link-remove { margin-left:auto; background:none; border:none;
                                 color:#ef4444; cursor:pointer; font-size:.85rem; }
.af-link-add-row { display:flex; gap:8px; flex-wrap:wrap; }
.af-link-add-row .af-input { flex:1; min-width:180px; }

/* Autocomplete */
.af-autocomplete-wrap { position:relative; }
.af-autocomplete-dropdown { position:absolute; top:100%; left:0; right:0; z-index:100;
                             background:#fff; border:1.5px solid #cbd5e1; border-top:none;
                             border-radius:0 0 8px 8px; max-height:200px; overflow-y:auto;
                             box-shadow:0 4px 12px rgba(0,0,0,.08); }
.af-autocomplete-item { padding:9px 14px; cursor:pointer; font-size:.85rem; }
.af-autocomplete-item:hover { background:#f0f4f8; }

/* Social post preview */
.af-social-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
                  gap:16px; margin-top:16px; }
.af-social-card { background:#fff; border-radius:12px; padding:20px;
                  box-shadow:0 1px 6px rgba(0,0,0,.07); }
.af-social-card-head { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.af-social-card-head .icon { font-size:1.3rem; }
.af-social-card-head .lbl { font-weight:700; }
.af-social-textarea { width:100%; box-sizing:border-box; min-height:120px;
                       padding:10px; border:1.5px solid #e2e8f0; border-radius:8px;
                       font-size:.83rem; font-family:inherit; resize:vertical; }
.af-generate-btn { display:inline-flex; align-items:center; gap:6px; margin-top:8px;
                   padding:6px 14px; border-radius:6px; font-size:.8rem; font-weight:600;
                   border:none; cursor:pointer; background:var(--af-navy); color:#fff; }
.af-generate-btn:hover { background:#0f2237; }
.af-btn-social-seq { background:#7c3aed; color:#fff; }
.af-btn-social-seq:hover { background:#6d28d9; }
.af-modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,.45); display:none; z-index:9000; }
.af-modal-backdrop.open { display:flex; align-items:center; justify-content:center; padding:16px; }
.af-modal { width:min(720px, 100%); background:#fff; border-radius:14px; box-shadow:0 25px 70px rgba(2,6,23,.35); }
.af-modal-head { padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
.af-modal-title { margin:0; font-size:1.05rem; }
.af-modal-close { background:none; border:none; font-size:1.4rem; cursor:pointer; color:#64748b; }
.af-modal-body { padding:18px 20px; }
.af-check-row { display:flex; gap:14px; flex-wrap:wrap; }
.af-check-row label { display:flex; align-items:center; gap:6px; font-size:.9rem; cursor:pointer; }
.af-modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
.af-seq-help { margin-top:8px; font-size:.78rem; color:#64748b; }

/* Publish section */
.af-publish-row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
.af-publish-row .af-group { flex:1; min-width:200px; }
.af-radio-group { display:flex; gap:16px; flex-wrap:wrap; }
.af-radio-lbl { display:flex; align-items:center; gap:6px; cursor:pointer;
                font-size:.88rem; font-weight:600; }
.af-radio-lbl input { cursor:pointer; }

/* Actions bar */
.af-actions-bar { display:flex; align-items:center; gap:12px; margin-top:28px;
                  padding-top:20px; border-top:1.5px solid #e2e8f0; flex-wrap:wrap; }
.af-btn { display:inline-flex; align-items:center; gap:7px; padding:10px 22px;
          border-radius:8px; font-size:.9rem; font-weight:600;
          border:none; cursor:pointer; text-decoration:none; transition:.15s; }
.af-btn-primary { background:var(--af-navy); color:#fff; }
.af-btn-primary:hover { background:#0f2237; }
.af-btn-gold { background:var(--af-gold); color:#fff; }
.af-btn-gold:hover { background:#b8922e; }
.af-btn-outline { background:#fff; color:var(--af-navy); border:2px solid #cbd5e1; }
.af-btn-outline:hover { border-color:var(--af-navy); }
.af-btn-danger { background:#fee2e2; color:#dc2626; }
.af-btn-danger:hover { background:#dc2626; color:#fff; }

.af-card { background:#fff; border-radius:12px; padding:24px;
           box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:20px; }
.af-card-title { font-size:.85rem; font-weight:700; text-transform:uppercase;
                 letter-spacing:.07em; color:#94a3b8; margin-bottom:16px; }

/* Alert */
.af-alert { padding:12px 16px; border-radius:8px; font-size:.85rem; margin-bottom:16px; }
.af-alert-success { background:#d1fae5; color:#065f46; }
.af-alert-error   { background:#fee2e2; color:#dc2626; }
</style>

<?php
$isEdit  = !empty($article['id']);
$artId   = $isEdit ? (int)$article['id'] : 0;
$flash   = $_SESSION['rd_flash'] ?? null;
unset($_SESSION['rd_flash']);

// Decode JSON fields
$maillageInterne = [];
$maillageExterne = [];
if ($isEdit) {
    $mi = $article['maillage_interne'] ?? '[]';
    $me = $article['maillage_externe'] ?? '[]';
    $maillageInterne = is_string($mi) ? (json_decode($mi, true) ?? []) : (array)$mi;
    $maillageExterne = is_string($me) ? (json_decode($me, true) ?? []) : (array)$me;
}
$kwString = implode(', ', $keywords);
$niveaux  = [1=>'Inconscient',2=>'Douleur',3=>'Solution',4=>'Produit',5=>'Plus conscient'];
?>

<div class="af">
  <!-- Breadcrumb -->
  <div class="af-header">
    <div>
      <div class="af-breadcrumb">
        <a href="/admin?module=redaction">Rédaction</a> › <?= $isEdit ? 'Modifier' : 'Nouvel article' ?>
      </div>
      <h1><?= $isEdit ? htmlspecialchars($article['titre']) : 'Nouvel article' ?></h1>
    </div>
    <?php if ($isEdit): ?>
    <a href="/admin?module=redaction&action=article_delete&id=<?= $artId ?>"
       class="af-btn af-btn-danger"
       onclick="return confirm('Supprimer cet article ?')">
      <i class="fas fa-trash"></i> Supprimer
    </a>
    <button type="button" class="af-btn af-btn-social-seq" id="open-social-sequence-modal">
      <i class="fas fa-share-nodes"></i> Créer une séquence social
    </button>
    <?php endif; ?>
  </div>

  <?php if ($flash): ?>
  <div class="af-alert af-alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <form method="POST" action="/admin?module=redaction&action=article_save" id="article-form">
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= $artId ?>">
    <?php endif; ?>

    <!-- TABS -->
    <div class="af-tabs">
      <button type="button" class="af-tab active" data-tab="contenu">
        <i class="fas fa-pen-to-square"></i> Contenu
      </button>
      <button type="button" class="af-tab" data-tab="seo">
        <i class="fas fa-magnifying-glass"></i> SEO
      </button>
      <button type="button" class="af-tab" data-tab="maillage">
        <i class="fas fa-link"></i> Maillage
      </button>
      <button type="button" class="af-tab" data-tab="publication">
        <i class="fas fa-paper-plane"></i> Publication
      </button>
    </div>

    <!-- PANEL: CONTENU -->
    <div class="af-tab-panel active" id="panel-contenu">

      <div class="af-card">
        <div class="af-card-title">Structure de l'article</div>
        <div class="af-grid" style="grid-template-columns:1fr 1fr 1fr">
          <div class="af-group">
            <label class="af-label">Type d'article</label>
            <select name="type" class="af-select" id="type-select">
              <option value="satellite" <?= ($article['type'] ?? '') !== 'pilier' ? 'selected' : '' ?>>Satellite</option>
              <option value="pilier" <?= ($article['type'] ?? '') === 'pilier' ? 'selected' : '' ?>>Pilier</option>
            </select>
          </div>
          <div class="af-group" id="niveau-group" <?= ($article['type'] ?? '') === 'pilier' ? 'style="display:none"' : '' ?>>
            <label class="af-label">Niveau de conscience <span class="opt">(si satellite)</span></label>
            <select name="niveau_conscience" class="af-select">
              <option value="">— Choisir —</option>
              <?php foreach ($niveaux as $n => $lbl): ?>
                <option value="<?= $n ?>" <?= (int)($article['niveau_conscience'] ?? 0) === $n ? 'selected' : '' ?>>
                  <?= $n ?>. <?= $lbl ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="af-group">
            <label class="af-label">Persona <span class="opt">(optionnel)</span></label>
            <input type="number" name="persona_id" class="af-input"
                   value="<?= htmlspecialchars((string)($article['persona_id'] ?? '')) ?>"
                   placeholder="ID persona">
          </div>
        </div>
      </div>

      <div class="af-card">
        <div class="af-card-title">Titre & H1</div>
        <div class="af-grid full">
          <div class="af-group">
            <label class="af-label">Titre de l'article</label>
            <input type="text" name="titre" id="titre-input" class="af-input"
                   value="<?= htmlspecialchars($article['titre'] ?? '') ?>"
                   placeholder="Titre principal de l'article" required>
          </div>
          <div class="af-group">
            <label class="af-label">Balise H1 <span class="opt">(si différent du titre)</span></label>
            <input type="text" name="h1" class="af-input"
                   value="<?= htmlspecialchars($article['h1'] ?? '') ?>"
                   placeholder="Identique au titre si vide">
          </div>
        </div>
      </div>

      <div class="af-card">
        <div class="af-card-title">Introduction</div>
        <div class="af-group">
          <textarea name="intro" class="af-textarea" id="intro-area"
                    placeholder="Accroche de l'article (accueil lecteur + mot-clé principal)…"><?= htmlspecialchars($article['intro'] ?? '') ?></textarea>
          <div class="af-counter" id="intro-count">0 mots</div>
        </div>
      </div>

      <div class="af-card">
        <div class="af-card-title">Corps de l'article</div>
        <div class="af-group">
          <textarea name="contenu" class="af-textarea tall" id="contenu-area"
                    placeholder="Rédigez le corps de l'article. Utilisez ## pour les H2, ### pour les H3…"><?= htmlspecialchars($article['contenu'] ?? '') ?></textarea>
          <div class="af-counter" id="contenu-count">0 mots</div>
        </div>
      </div>

      <div class="af-card">
        <div class="af-card-title">Conclusion</div>
        <div class="af-group">
          <textarea name="conclusion" class="af-textarea" id="conclusion-area"
                    placeholder="Synthèse + appel à l'action…"><?= htmlspecialchars($article['conclusion'] ?? '') ?></textarea>
          <div class="af-counter" id="conclusion-count">0 mots</div>
        </div>
      </div>
    </div>

    <!-- PANEL: SEO -->
    <div class="af-tab-panel" id="panel-seo">

      <!-- Score live -->
      <div class="af-seo-score" id="seo-score-box">
        <div style="flex:1">
          <div style="font-size:.8rem;color:#64748b;margin-bottom:6px">Score SEO</div>
          <div class="af-score-bar">
            <div class="af-score-fill" id="seo-score-fill" style="width:<?= (int)($article['score_seo'] ?? 0) ?>%"></div>
          </div>
          <div class="af-criteria" id="seo-criteria"></div>
        </div>
        <div class="af-score-num" id="seo-score-num"><?= (int)($article['score_seo'] ?? 0) ?>/100</div>
      </div>

      <div class="af-card">
        <div class="af-card-title">Optimisation On-Page</div>
        <div class="af-grid full">
          <div class="af-group">
            <label class="af-label">Slug (URL)</label>
            <input type="text" name="slug" id="slug-input" class="af-input"
                   value="<?= htmlspecialchars($article['slug'] ?? '') ?>"
                   placeholder="genere-automatiquement-depuis-le-titre">
          </div>
          <div class="af-group">
            <label class="af-label">Mot-clé principal</label>
            <input type="text" name="mot_cle_principal" id="kw-main" class="af-input"
                   value="<?= htmlspecialchars($article['mot_cle_principal'] ?? '') ?>"
                   placeholder="Ex : appartement Aix-en-Provence">
          </div>
          <div class="af-group">
            <label class="af-label">
              Balise Title SEO
              <span class="opt">( <span id="title-len">0</span>/70 )</span>
            </label>
            <input type="text" name="seo_title" id="seo-title-input" class="af-input"
                   maxlength="70"
                   value="<?= htmlspecialchars($article['seo_title'] ?? '') ?>"
                   placeholder="Titre optimisé pour Google (max 70 car.)">
            <div class="af-counter" id="title-counter"></div>
          </div>
          <div class="af-group">
            <label class="af-label">
              Meta Description
              <span class="opt">( <span id="meta-len">0</span>/160 )</span>
            </label>
            <textarea name="meta_desc" id="meta-desc-input" class="af-textarea"
                      style="min-height:80px" maxlength="160"
                      placeholder="Description pour les résultats Google (max 160 car.)"><?= htmlspecialchars($article['meta_desc'] ?? '') ?></textarea>
            <div class="af-counter" id="meta-counter"></div>
          </div>
          <div class="af-group">
            <label class="af-label">Mots-clés LSI <span class="opt">(séparés par des virgules)</span></label>
            <input type="text" name="mots_cles_lsi" class="af-input"
                   value="<?= htmlspecialchars($article['mots_cles_lsi'] ?? '') ?>"
                   placeholder="agence immobilière, acheter appartement, estimation gratuite…">
          </div>
          <div class="af-group">
            <label class="af-label">Mots-clés suivis <span class="opt">(séparés par des virgules)</span></label>
            <input type="text" name="keywords_raw" id="kw-raw" class="af-input"
                   value="<?= htmlspecialchars($kwString) ?>"
                   placeholder="appartement aix, acheter immobilier aix…">
          </div>
          <div class="af-group">
            <label class="af-label">Indexation</label>
            <select name="index_status" class="af-select">
              <option value="index" <?= ($article['index_status'] ?? 'index') === 'index' ? 'selected' : '' ?>>Index</option>
              <option value="noindex" <?= ($article['index_status'] ?? '') === 'noindex' ? 'selected' : '' ?>>Noindex</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- PANEL: MAILLAGE -->
    <div class="af-tab-panel" id="panel-maillage">

      <div class="af-card">
        <div class="af-card-title">Maillage interne</div>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:12px">
          Liens vers d'autres articles de votre blog (améliore la structure SEO).
        </p>
        <div id="maillage-interne-list" class="af-link-list">
          <?php foreach ($maillageInterne as $lien): ?>
          <div class="af-link-item" data-id="<?= (int)($lien['id'] ?? 0) ?>">
            <i class="fas fa-file-alt" style="color:#3b82f6"></i>
            <span><?= htmlspecialchars($lien['titre'] ?? '') ?></span>
            <code style="font-size:.75rem;color:#94a3b8;margin-left:4px">/<?= htmlspecialchars($lien['slug'] ?? '') ?></code>
            <button type="button" class="af-link-remove" onclick="removeMI(this)">
              <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="maillage_interne[]" value="<?= htmlspecialchars(json_encode($lien)) ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <div class="af-autocomplete-wrap" style="margin-top:14px">
          <input type="text" id="mi-search" class="af-input"
                 placeholder="Rechercher un article à lier…" autocomplete="off">
          <div class="af-autocomplete-dropdown" id="mi-dropdown" style="display:none"></div>
        </div>
      </div>

      <div class="af-card">
        <div class="af-card-title">Maillage externe</div>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:12px">
          Liens vers des sources externes fiables (notaires, data INSEE, SeLoger…).
        </p>
        <div id="maillage-externe-list" class="af-link-list">
          <?php foreach ($maillageExterne as $lien): ?>
          <div class="af-link-item">
            <i class="fas fa-external-link-alt" style="color:#10b981"></i>
            <span><?= htmlspecialchars($lien['anchor'] ?? '') ?></span>
            <code style="font-size:.75rem;color:#94a3b8;margin-left:4px"><?= htmlspecialchars($lien['url'] ?? '') ?></code>
            <button type="button" class="af-link-remove" onclick="removeME(this)">
              <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="maillage_externe_anchor[]" value="<?= htmlspecialchars($lien['anchor'] ?? '') ?>">
            <input type="hidden" name="maillage_externe_url[]" value="<?= htmlspecialchars($lien['url'] ?? '') ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <div class="af-link-add-row" style="margin-top:14px">
          <input type="text" id="me-anchor" class="af-input" placeholder="Texte du lien (anchor)" style="max-width:240px">
          <input type="text" id="me-url" class="af-input" placeholder="https://..." style="flex:2">
          <button type="button" class="af-btn af-btn-outline" onclick="addME()">
            <i class="fas fa-plus"></i> Ajouter
          </button>
        </div>
      </div>

    </div>

    <!-- PANEL: PUBLICATION -->
    <div class="af-tab-panel" id="panel-publication">

      <div class="af-card">
        <div class="af-card-title">Statut & planification</div>
        <div class="af-publish-row">
          <div class="af-group">
            <label class="af-label">Statut</label>
            <select name="statut" class="af-select" id="statut-select">
              <option value="brouillon" <?= ($article['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
              <option value="planifié"  <?= ($article['statut'] ?? '') === 'planifié' ? 'selected' : '' ?>>Planifié</option>
              <option value="publié"    <?= ($article['statut'] ?? '') === 'publié' ? 'selected' : '' ?>>Publier maintenant</option>
              <option value="archivé"   <?= ($article['statut'] ?? '') === 'archivé' ? 'selected' : '' ?>>Archivé</option>
            </select>
          </div>
          <div class="af-group" id="date-pub-group"
               style="<?= in_array($article['statut'] ?? '', ['planifié']) ? '' : 'display:none' ?>">
            <label class="af-label">Date de publication</label>
            <input type="datetime-local" name="date_publication" class="af-input"
                   value="<?= $article['date_publication'] ? date('Y-m-d\TH:i', strtotime($article['date_publication'])) : '' ?>">
          </div>
        </div>
      </div>

      <!-- Social auto-generation -->
      <div class="af-card">
        <div class="af-card-title">Publications automatiques</div>
        <p style="font-size:.85rem;color:#64748b;margin-bottom:16px">
          Générez automatiquement des posts pour vos réseaux à partir du contenu de l'article.
          Chaque post est modifiable avant d'être enregistré.
        </p>
        <div class="af-social-grid">
          <?php foreach (['gmb'=>['📍','Google My Business','#4285f4'], 'facebook'=>['📘','Facebook','#1877f2'], 'linkedin'=>['💼','LinkedIn','#0a66c2']] as $reseau=>[$icon,$lbl,$color]): ?>
          <div class="af-social-card">
            <div class="af-social-card-head">
              <span class="icon"><?= $icon ?></span>
              <span class="lbl" style="color:<?= $color ?>"><?= $lbl ?></span>
              <label style="margin-left:auto;display:flex;align-items:center;gap:6px;font-size:.8rem;cursor:pointer">
                <input type="checkbox" name="auto_reseaux[]" value="<?= $reseau ?>" id="chk-<?= $reseau ?>">
                Inclure
              </label>
            </div>
            <textarea class="af-social-textarea" id="post-<?= $reseau ?>" name="post_<?= $reseau ?>"
                      placeholder="Contenu du post <?= $lbl ?>…"></textarea>
            <button type="button" class="af-generate-btn" onclick="generatePost('<?= $reseau ?>')">
              <i class="fas fa-wand-magic-sparkles"></i> Générer
            </button>
            <div style="margin-top:8px">
              <label class="af-label">Planifier le post <span class="opt">(optionnel)</span></label>
              <input type="datetime-local" name="post_<?= $reseau ?>_date" class="af-input" style="margin-top:4px">
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- Actions bar -->
    <div class="af-actions-bar">
      <button type="submit" class="af-btn af-btn-primary">
        <i class="fas fa-save"></i> Enregistrer
      </button>
      <button type="submit" name="statut" value="publié" class="af-btn af-btn-gold">
        <i class="fas fa-check"></i> Publier maintenant
      </button>
      <a href="/admin?module=redaction" class="af-btn af-btn-outline">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>

  </form>
</div>

<?php if ($isEdit): ?>
<div class="af-modal-backdrop" id="social-seq-modal">
  <div class="af-modal">
    <div class="af-modal-head">
      <h3 class="af-modal-title">Créer une séquence social</h3>
      <button type="button" class="af-modal-close" id="close-social-seq-modal" aria-label="Fermer">×</button>
    </div>
    <div class="af-modal-body">
      <div class="af-grid">
        <div class="af-group">
          <label class="af-label">Persona</label>
          <select class="af-select" id="seq-persona">
            <?php if (!empty($personas)): ?>
              <?php foreach ($personas as $persona): ?>
                <option value="<?= htmlspecialchars((string)$persona['name']) ?>" <?= (string)($article['persona_id'] ?? '') === (string)$persona['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string)$persona['name']) ?>
                </option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="Persona libre">Persona libre</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="af-group">
          <label class="af-label">Objectif</label>
          <select class="af-select" id="seq-objectif">
            <option value="trafic">Trafic</option>
            <option value="leads">Leads</option>
            <option value="autorite">Autorité</option>
          </select>
        </div>
      </div>

      <div class="af-group" style="margin-top:12px">
        <label class="af-label">Réseaux</label>
        <div class="af-check-row">
          <label><input type="checkbox" name="seq_reseaux[]" value="facebook" checked> Facebook</label>
          <label><input type="checkbox" name="seq_reseaux[]" value="instagram"> Instagram</label>
          <label><input type="checkbox" name="seq_reseaux[]" value="gmb"> GMB</label>
        </div>
      </div>

      <div class="af-group" style="margin-top:12px">
        <label class="af-label">Nombre de posts</label>
        <select class="af-select" id="seq-nb-posts">
          <option value="3">3</option>
          <option value="5" selected>5</option>
          <option value="7">7</option>
          <option value="10">10</option>
        </select>
        <div class="af-seq-help">La séquence couvre toujours N1 → N5 (minimum 5 posts) avec CTA progressif.</div>
      </div>

      <div class="af-modal-actions">
        <button type="button" class="af-btn af-btn-outline" id="cancel-social-seq-btn">Annuler</button>
        <button type="button" class="af-btn af-btn-social-seq" id="create-social-seq-btn">
          <i class="fas fa-bolt"></i> Générer la séquence
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Tab switching
document.querySelectorAll('.af-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.af-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.af-tab-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
    });
});

// Type select → show/hide niveau
document.getElementById('type-select').addEventListener('change', function() {
    document.getElementById('niveau-group').style.display = this.value === 'pilier' ? 'none' : '';
});

// Statut → date
document.getElementById('statut-select').addEventListener('change', function() {
    document.getElementById('date-pub-group').style.display = this.value === 'planifié' ? '' : 'none';
});

// Word counters
function wc(text) {
    return text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
}
function updateCounter(areaId, counterId) {
    var area = document.getElementById(areaId);
    var ctr  = document.getElementById(counterId);
    if (!area || !ctr) return;
    ctr.textContent = wc(area.value) + ' mots';
    area.addEventListener('input', function() { ctr.textContent = wc(area.value) + ' mots'; });
}
updateCounter('intro-area', 'intro-count');
updateCounter('contenu-area', 'contenu-count');
updateCounter('conclusion-area', 'conclusion-count');

// Char counters for SEO fields
function charCounter(inputId, spanId, max) {
    var el = document.getElementById(inputId);
    var sp = document.getElementById(spanId);
    if (!el || !sp) return;
    function update() {
        var n = el.value.length;
        sp.textContent = n;
        sp.parentElement.className = 'af-counter ' + (n >= max ? 'warn' : (n >= Math.round(max*0.7) ? 'ok' : ''));
    }
    update();
    el.addEventListener('input', update);
}
charCounter('seo-title-input', 'title-len', 70);
charCounter('meta-desc-input', 'meta-len', 160);

// Auto slug from titre
document.getElementById('titre-input').addEventListener('input', function() {
    var slug = document.getElementById('slug-input');
    if (slug.dataset.manual === '1') return;
    slug.value = this.value.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
});
document.getElementById('slug-input').addEventListener('input', function() {
    this.dataset.manual = '1';
});

// SEO score live
function updateSeoScore() {
    var titre     = document.getElementById('titre-input')?.value || '';
    var seoTitle  = document.getElementById('seo-title-input')?.value || '';
    var metaDesc  = document.getElementById('meta-desc-input')?.value || '';
    var kwMain    = document.getElementById('kw-main')?.value || '';
    var kwRaw     = document.getElementById('kw-raw')?.value || '';
    var intro     = document.getElementById('intro-area')?.value || '';
    var contenu   = document.getElementById('contenu-area')?.value || '';
    var conclusion= document.getElementById('conclusion-area')?.value || '';
    var miCount   = document.querySelectorAll('#maillage-interne-list .af-link-item').length;
    var lsiInput  = document.querySelector('[name="mots_cles_lsi"]')?.value || '';

    var checks = [
        {ok: titre.length >= 30,               lbl: 'Titre ≥ 30 car.'},
        {ok: seoTitle.length >= 30 && seoTitle.length <= 70, lbl: 'SEO Title 30-70 car.'},
        {ok: metaDesc.length >= 100 && metaDesc.length <= 160, lbl: 'Meta Desc 100-160 car.'},
        {ok: document.querySelector('[name="h1"]')?.value?.length > 0, lbl: 'H1 défini'},
        {ok: kwMain.length > 0,                lbl: 'Mot-clé principal'},
        {ok: wc(contenu) >= 300,               lbl: 'Contenu ≥ 300 mots'},
        {ok: intro.trim().length > 0,          lbl: 'Introduction'},
        {ok: conclusion.trim().length > 0,     lbl: 'Conclusion'},
        {ok: miCount > 0,                      lbl: 'Maillage interne'},
        {ok: lsiInput.trim().length > 0,       lbl: 'Mots-clés LSI'},
    ];

    var score = checks.filter(c => c.ok).length * 10;
    document.getElementById('seo-score-fill').style.width = score + '%';
    document.getElementById('seo-score-num').textContent = score + '/100';

    var fill = document.getElementById('seo-score-fill');
    fill.style.background = score >= 70 ? '#10b981' : (score >= 40 ? '#f59e0b' : '#ef4444');

    var criteriaHtml = checks.map(c =>
        '<div class="af-criteria-item ' + (c.ok?'ok':'nok') + '">' +
        '<i class="fas ' + (c.ok?'fa-check':'fa-times') + '"></i>' +
        c.lbl + '</div>'
    ).join('');
    document.getElementById('seo-criteria').innerHTML = criteriaHtml;
}
document.getElementById('article-form').addEventListener('input', updateSeoScore);
updateSeoScore();

// Internal link autocomplete
var miDropdown = document.getElementById('mi-dropdown');
var miSearch   = document.getElementById('mi-search');
var miTimer;

miSearch.addEventListener('input', function() {
    clearTimeout(miTimer);
    var q = this.value.trim();
    if (q.length < 2) { miDropdown.style.display='none'; return; }
    miTimer = setTimeout(function() {
        fetch('/admin?module=redaction&action=api_search_articles&q=' + encodeURIComponent(q))
            .then(r => r.json()).then(function(data) {
                if (!data.length) { miDropdown.style.display='none'; return; }
                miDropdown.innerHTML = data.map(function(a) {
                    return '<div class="af-autocomplete-item" data-id="'+a.id+'" data-titre="'+escHtml(a.titre)+'" data-slug="'+escHtml(a.slug)+'">' +
                           escHtml(a.titre) + ' <code style="color:#94a3b8;font-size:.75rem">' + escHtml(a.slug) + '</code></div>';
                }).join('');
                miDropdown.style.display = '';
                miDropdown.querySelectorAll('.af-autocomplete-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        addMI({id: this.dataset.id, titre: this.dataset.titre, slug: this.dataset.slug});
                        miDropdown.style.display = 'none';
                        miSearch.value = '';
                    });
                });
            }).catch(() => {});
    }, 300);
});
document.addEventListener('click', function(e) {
    if (!miDropdown.contains(e.target) && e.target !== miSearch) {
        miDropdown.style.display = 'none';
    }
});

function addMI(lien) {
    var list  = document.getElementById('maillage-interne-list');
    var div   = document.createElement('div');
    div.className = 'af-link-item';
    div.innerHTML =
        '<i class="fas fa-file-alt" style="color:#3b82f6"></i>' +
        '<span>' + escHtml(lien.titre) + '</span>' +
        '<code style="font-size:.75rem;color:#94a3b8;margin-left:4px">/' + escHtml(lien.slug) + '</code>' +
        '<button type="button" class="af-link-remove" onclick="removeMI(this)"><i class="fas fa-times"></i></button>' +
        '<input type="hidden" name="maillage_interne[]" value=\'' + JSON.stringify(lien).replace(/'/g,"&#39;") + '\'>';
    list.appendChild(div);
    updateSeoScore();
}

function removeMI(btn) { btn.closest('.af-link-item').remove(); updateSeoScore(); }

function addME() {
    var anchor = document.getElementById('me-anchor').value.trim();
    var url    = document.getElementById('me-url').value.trim();
    if (!anchor || !url) return;
    var list = document.getElementById('maillage-externe-list');
    var div  = document.createElement('div');
    div.className = 'af-link-item';
    div.innerHTML =
        '<i class="fas fa-external-link-alt" style="color:#10b981"></i>' +
        '<span>' + escHtml(anchor) + '</span>' +
        '<code style="font-size:.75rem;color:#94a3b8;margin-left:4px">' + escHtml(url) + '</code>' +
        '<button type="button" class="af-link-remove" onclick="removeME(this)"><i class="fas fa-times"></i></button>' +
        '<input type="hidden" name="maillage_externe_anchor[]" value="' + escHtml(anchor) + '">' +
        '<input type="hidden" name="maillage_externe_url[]" value="' + escHtml(url) + '">';
    list.appendChild(div);
    document.getElementById('me-anchor').value = '';
    document.getElementById('me-url').value    = '';
}
function removeME(btn) { btn.closest('.af-link-item').remove(); }

function generatePost(reseau) {
    var titre    = document.getElementById('titre-input')?.value || '';
    var intro    = document.getElementById('intro-area')?.value || '';
    var contenu  = document.getElementById('contenu-area')?.value || '';
    fetch('/admin?module=redaction&action=api_generate_post', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'reseau=' + encodeURIComponent(reseau) +
              '&titre=' + encodeURIComponent(titre) +
              '&intro=' + encodeURIComponent(intro.substring(0,600)) +
              '&contenu=' + encodeURIComponent(contenu.substring(0,600))
    }).then(r => r.json()).then(function(data) {
        document.getElementById('post-' + reseau).value = data.contenu || '';
        document.getElementById('chk-' + reseau).checked = true;
    }).catch(() => {});
}

function escHtml(str) {
    return (str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

<?php if ($isEdit): ?>
var socialModal = document.getElementById('social-seq-modal');
function openSocialModal() {
    if (socialModal) socialModal.classList.add('open');
}
function closeSocialModal() {
    if (socialModal) socialModal.classList.remove('open');
}

document.getElementById('open-social-sequence-modal')?.addEventListener('click', openSocialModal);
document.getElementById('close-social-seq-modal')?.addEventListener('click', closeSocialModal);
document.getElementById('cancel-social-seq-btn')?.addEventListener('click', closeSocialModal);
socialModal?.addEventListener('click', function(e) {
    if (e.target === socialModal) closeSocialModal();
});

document.getElementById('create-social-seq-btn')?.addEventListener('click', function() {
    var persona = document.getElementById('seq-persona')?.value || 'Persona libre';
    var objectif = document.getElementById('seq-objectif')?.value || 'trafic';
    var nbPosts = document.getElementById('seq-nb-posts')?.value || '5';
    var reseaux = Array.from(document.querySelectorAll('input[name="seq_reseaux[]"]:checked')).map(function(el) {
        return el.value;
    });

    if (reseaux.length === 0) {
        alert('Sélectionnez au moins un réseau.');
        return;
    }

    var btn = this;
    var oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';

    fetch('/admin?module=redaction&action=api_create_social_sequence', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'article_id=<?= $artId ?>' +
              '&persona=' + encodeURIComponent(persona) +
              '&objectif=' + encodeURIComponent(objectif) +
              '&nombre_posts=' + encodeURIComponent(nbPosts) +
              reseaux.map(function(n) { return '&reseaux[]=' + encodeURIComponent(n); }).join('')
    }).then(function(r) { return r.json(); })
      .then(function(data) {
          if (!data || !data.ok) {
              throw new Error((data && data.message) ? data.message : 'Erreur de génération');
          }
          closeSocialModal();
          window.location.href = '/admin?module=social&action=sequences';
      })
      .catch(function(err) {
          alert(err.message || 'Impossible de créer la séquence.');
      })
      .finally(function() {
          btn.disabled = false;
          btn.innerHTML = oldHtml;
      });
});
<?php endif; ?>
</script>
