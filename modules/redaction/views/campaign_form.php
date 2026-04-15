<?php
/** @var array|null $campaign */
/** @var array      $slots    buildSlots() result */
/** @var array      $allArticles for dropdown */
$niveauxLabels = [1=>'Inconscient',2=>'Douleur',3=>'Solution',4=>'Produit',5=>'Plus conscient'];
$isEdit = !empty($campaign['id']);
$flash  = $_SESSION['rd_flash'] ?? null;
unset($_SESSION['rd_flash']);
?>
<style>
.cf { --cf-navy:#1a3c5e; --cf-gold:#c9a84c; --cf-bg:#f0f4f8;
      font-family:inherit; color:var(--cf-navy); }
.cf-header { display:flex; align-items:center; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.cf-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.cf-breadcrumb { font-size:.82rem; color:#64748b; }
.cf-breadcrumb a { color:var(--cf-navy); text-decoration:none; }

.cf-card { background:#fff; border-radius:12px; padding:24px;
           box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:20px; }
.cf-card-title { font-size:.82rem; font-weight:700; text-transform:uppercase;
                 letter-spacing:.07em; color:#94a3b8; margin-bottom:16px; }
.cf-group { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.cf-label { font-size:.82rem; font-weight:600; color:#475569; }
.cf-input, .cf-select, .cf-textarea {
  padding:10px 14px; border:1.5px solid #cbd5e1; border-radius:8px;
  font-size:.9rem; font-family:inherit; color:var(--cf-navy);
  outline:none; transition:.15s; background:#fff; width:100%; box-sizing:border-box; }
.cf-input:focus, .cf-select:focus, .cf-textarea:focus { border-color:var(--cf-gold); }

/* Article slots */
.cf-slots { display:flex; flex-direction:column; gap:14px; }
.cf-slot { display:grid; grid-template-columns:220px 1fr; gap:14px; align-items:start;
           background:#f8fafc; border-radius:10px; padding:16px; border-left:4px solid #cbd5e1; }
.cf-slot.pilier { border-left-color:var(--cf-gold); background:#fffbf0; }
.cf-slot.nc-1   { border-left-color:#94a3b8; }
.cf-slot.nc-2   { border-left-color:#f59e0b; }
.cf-slot.nc-3   { border-left-color:#3b82f6; }
.cf-slot.nc-4   { border-left-color:#10b981; }
.cf-slot.nc-5   { border-left-color:#8b5cf6; }
.cf-slot-info {}
.cf-slot-tag { font-size:.72rem; font-weight:800; text-transform:uppercase;
               letter-spacing:.08em; color:#fff; border-radius:5px;
               display:inline-block; padding:2px 8px; margin-bottom:6px; }
.cf-slot-tag.pilier   { background:var(--cf-gold); }
.cf-slot-tag.nc-1 { background:#94a3b8; }
.cf-slot-tag.nc-2 { background:#f59e0b; }
.cf-slot-tag.nc-3 { background:#3b82f6; }
.cf-slot-tag.nc-4 { background:#10b981; }
.cf-slot-tag.nc-5 { background:#8b5cf6; }
.cf-slot-name { font-size:.9rem; font-weight:700; }
.cf-slot-desc { font-size:.78rem; color:#64748b; margin-top:2px; }
.cf-slot-right { display:flex; flex-direction:column; gap:8px; }
.cf-slot-status { font-size:.75rem; color:#64748b; }
.cf-slot-status.ok { color:#10b981; }
@media(max-width:600px){ .cf-slot { grid-template-columns:1fr; } }

.cf-btn { display:inline-flex; align-items:center; gap:7px; padding:10px 22px;
          border-radius:8px; font-size:.9rem; font-weight:600;
          border:none; cursor:pointer; text-decoration:none; transition:.15s; }
.cf-btn-primary { background:var(--cf-navy); color:#fff; }
.cf-btn-primary:hover { background:#0f2237; }
.cf-btn-outline { background:#fff; color:var(--cf-navy); border:2px solid #cbd5e1; }
.cf-btn-outline:hover { border-color:var(--cf-navy); }
.cf-btn-danger { background:#fee2e2; color:#dc2626; border:none; }
.cf-actions-bar { display:flex; align-items:center; gap:12px; margin-top:24px;
                  padding-top:20px; border-top:1.5px solid #e2e8f0; flex-wrap:wrap; }
.cf-alert { padding:12px 16px; border-radius:8px; font-size:.85rem; margin-bottom:16px; }
.cf-alert-success { background:#d1fae5; color:#065f46; }
.cf-alert-error   { background:#fee2e2; color:#dc2626; }
</style>

<?php
$niveauDescs = [
    1 => "Le lecteur ne sait pas encore qu'il a un problème",
    2 => "Il ressent une douleur mais ne sait pas pourquoi",
    3 => "Il cherche une solution générale",
    4 => "Il compare des offres concrètes",
    5 => "Il est prêt à acheter, cherche le meilleur choix",
];
?>
<div class="cf">
  <div class="cf-header">
    <div>
      <div class="cf-breadcrumb">
        <a href="/admin?module=redaction">Rédaction</a> ›
        <a href="/admin?module=redaction&action=campaigns">Campagnes</a> ›
        <?= $isEdit ? 'Modifier' : 'Nouvelle campagne' ?>
      </div>
      <h1><?= $isEdit ? htmlspecialchars($campaign['nom']) : 'Nouvelle campagne' ?></h1>
    </div>
    <?php if ($isEdit): ?>
    <a href="/admin?module=redaction&action=campaign_delete&id=<?= (int)$campaign['id'] ?>"
       class="cf-btn cf-btn-danger"
       onclick="return confirm('Supprimer cette campagne ?')">
      <i class="fas fa-trash"></i>
    </a>
    <?php endif; ?>
  </div>

  <?php if ($flash): ?>
  <div class="cf-alert cf-alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <form method="POST" action="/admin?module=redaction&action=campaign_save">
    <?php if ($isEdit): ?>
      <input type="hidden" name="id" value="<?= (int)$campaign['id'] ?>">
    <?php endif; ?>

    <div class="cf-card">
      <div class="cf-card-title">Informations de la campagne</div>
      <div class="cf-group">
        <label class="cf-label">Nom de la campagne</label>
        <input type="text" name="nom" class="cf-input" required
               value="<?= htmlspecialchars($campaign['nom'] ?? '') ?>"
               placeholder="Ex : Vendre un appartement à Aix-en-Provence">
      </div>
      <div class="cf-group">
        <label class="cf-label">Mot-clé cible principal</label>
        <input type="text" name="mot_cle" class="cf-input"
               value="<?= htmlspecialchars($campaign['mot_cle'] ?? '') ?>"
               placeholder="Ex : vente appartement Aix-en-Provence">
      </div>
      <div class="cf-group">
        <label class="cf-label">Description <span style="font-weight:400;color:#94a3b8">(optionnel)</span></label>
        <textarea name="description" class="cf-textarea" style="min-height:80px"
                  placeholder="Objectif de la campagne…"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
      </div>
      <div class="cf-group">
        <label class="cf-label">Statut</label>
        <select name="statut" class="cf-select">
          <option value="draft"   <?= ($campaign['statut'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
          <option value="actif"   <?= ($campaign['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
          <option value="terminé" <?= ($campaign['statut'] ?? '') === 'terminé' ? 'selected' : '' ?>>Terminé</option>
        </select>
      </div>
    </div>

    <div class="cf-card">
      <div class="cf-card-title">Articles de la campagne (1 pilier + 5 niveaux de conscience)</div>
      <p style="font-size:.85rem;color:#64748b;margin-bottom:20px">
        Une campagne = 6 articles : 1 article pilier (contenu exhaustif) + 1 article par niveau de conscience.
        Vous pouvez créer les articles avant ou après avoir créé la campagne.
      </p>

      <div class="cf-slots">
        <!-- Pilier slot -->
        <?php $pilierSlot = $slots[0] ?? null; ?>
        <div class="cf-slot pilier">
          <div class="cf-slot-info">
            <div class="cf-slot-tag pilier">Pilier</div>
            <div class="cf-slot-name">Article Pilier</div>
            <div class="cf-slot-desc">Guide complet, 2000+ mots, couvre tout le sujet</div>
          </div>
          <div class="cf-slot-right">
            <select name="slots[0][article_id]" class="cf-select">
              <option value="">— Aucun article sélectionné —</option>
              <?php foreach ($allArticles as $a): ?>
                <option value="<?= $a['id'] ?>"
                  <?= (int)($pilierSlot['data']['article_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars(mb_substr($a['titre'], 0, 70)) ?>
                  (<?= $a['statut'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="slots[0][role]" value="pilier">
            <?php if (!empty($pilierSlot['data']['article_id'])): ?>
              <a href="/admin?module=redaction&action=article_edit&id=<?= (int)$pilierSlot['data']['article_id'] ?>"
                 style="font-size:.8rem;color:var(--cf-navy)">
                <i class="fas fa-pen"></i> Modifier l'article
              </a>
            <?php else: ?>
              <a href="/admin?module=redaction&action=article_new&type=pilier"
                 style="font-size:.8rem;color:var(--cf-navy)">
                <i class="fas fa-plus"></i> Créer l'article pilier
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Consciousness level slots -->
        <?php foreach ([1,2,3,4,5] as $idx): ?>
        <?php $slot = $slots[$idx] ?? null; $n = $idx; ?>
        <div class="cf-slot nc-<?= $n ?>">
          <div class="cf-slot-info">
            <div class="cf-slot-tag nc-<?= $n ?>">Niveau <?= $n ?></div>
            <div class="cf-slot-name"><?= $niveauxLabels[$n] ?></div>
            <div class="cf-slot-desc"><?= $niveauDescs[$n] ?></div>
          </div>
          <div class="cf-slot-right">
            <select name="slots[<?= $n ?>][article_id]" class="cf-select">
              <option value="">— Aucun article sélectionné —</option>
              <?php foreach ($allArticles as $a): ?>
                <option value="<?= $a['id'] ?>"
                  <?= (int)($slot['data']['article_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars(mb_substr($a['titre'], 0, 70)) ?>
                  (<?= $a['statut'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="slots[<?= $n ?>][role]" value="conscience">
            <input type="hidden" name="slots[<?= $n ?>][niveau_conscience]" value="<?= $n ?>">
            <?php if (!empty($slot['data']['article_id'])): ?>
              <a href="/admin?module=redaction&action=article_edit&id=<?= (int)$slot['data']['article_id'] ?>"
                 style="font-size:.8rem;color:var(--cf-navy)">
                <i class="fas fa-pen"></i> Modifier
              </a>
            <?php else: ?>
              <a href="/admin?module=redaction&action=article_new&niveau_conscience=<?= $n ?>"
                 style="font-size:.8rem;color:var(--cf-navy)">
                <i class="fas fa-plus"></i> Créer (niveau <?= $n ?>)
              </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="cf-actions-bar">
      <button type="submit" class="cf-btn cf-btn-primary">
        <i class="fas fa-save"></i> Enregistrer la campagne
      </button>
      <a href="/admin?module=redaction&action=campaigns" class="cf-btn cf-btn-outline">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>
  </form>
</div>
