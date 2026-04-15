<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../services/PersonaResolver.php';

$website_id = 1;
$id = (int)($_GET['id'] ?? 0);
$a  = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_articles WHERE id=? AND website_id=?");
    $stmt->execute([$id, $website_id]);
    $a = $stmt->fetch();
    if (!$a) { header('Location: ../accueil.php'); exit; }
}

$v = static fn(string $k, string $d = ''): string => htmlspecialchars((string)($a[$k] ?? $d), ENT_QUOTES, 'UTF-8');
$datePublication = '';
if (!empty($a['date_publication'])) {
    $ts = strtotime((string)$a['date_publication']);
    if ($ts !== false) {
        $datePublication = date('Y-m-d\\TH:i', $ts);
    }
}

$targetOptions = PersonaResolver::getTargetOptions();
$catalog = PersonaResolver::getPersonaCatalog();
$mapping = PersonaResolver::getMapping();

$initialPersona = PersonaResolver::resolveFromPersonaId(isset($a['persona_id']) ? (string)$a['persona_id'] : null);
$initialTarget = $initialPersona['target'];
$initialReason = $initialPersona['reason'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id ? 'Éditer' : 'Créer' ?> un article</title>
<link rel="stylesheet" href="/modules/blog/assets/blog.css">
</head>
<body>
<div class="cms-wrap">
  <header class="cms-header">
    <a href="accueil.php" class="back">← Retour</a>
    <h1><?= $id ? 'Éditer l\'article' : 'Créer un article' ?></h1>
  </header>

  <form method="post" action="controllers/save.php" class="article-form" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="form-grid">
      <div class="col-main">
        <div class="form-group">
          <label for="titre-input">Titre *</label>
          <input type="text" id="titre-input" name="titre" value="<?= $v('titre') ?>" required>
        </div>

        <div class="form-group">
          <label for="slug-input">Slug</label>
          <input type="text" id="slug-input" name="slug" value="<?= $v('slug') ?>" placeholder="auto-genere-depuis-le-titre">
        </div>

        <section class="persona-panel">
          <h3>🎯 Cadrage persona (assisté)</h3>
          <p class="persona-intro">Choisissez une cible et une raison métier : le persona est détecté automatiquement.</p>

          <div class="persona-grid">
            <div class="form-group">
              <label for="persona-target-input">Cible</label>
              <select id="persona-target-input" name="persona_target">
                <option value="">Sélectionner une cible</option>
                <?php foreach ($targetOptions as $targetKey => $targetData): ?>
                <option value="<?= htmlspecialchars($targetKey, ENT_QUOTES, 'UTF-8') ?>" <?= $initialTarget === $targetKey ? 'selected' : '' ?>>
                  <?= htmlspecialchars($targetData['label'], ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="persona-reason-input">Raison principale</label>
              <select id="persona-reason-input" name="persona_reason">
                <option value="">Sélectionner une raison</option>
              </select>
              <small id="persona-reason-help">La liste dépend de la cible sélectionnée.</small>
            </div>
          </div>

          <div class="persona-detected-box">
            <div class="form-group">
              <label for="persona-label-input">Persona détecté</label>
              <input type="text" id="persona-label-input" value="<?= htmlspecialchars((string)$initialPersona['label'], ENT_QUOTES, 'UTF-8') ?>" readonly>
              <small id="persona-description"><?= htmlspecialchars((string)$initialPersona['description'], ENT_QUOTES, 'UTF-8') ?></small>
            </div>

            <div class="persona-meta-grid">
              <div class="form-group">
                <label for="persona-id-preview">ID technique (lecture seule)</label>
                <input type="text" id="persona-id-preview" value="<?= htmlspecialchars((string)($initialPersona['persona_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>
              <div class="form-group">
                <label for="persona-awareness-preview">Niveau de conscience</label>
                <input type="text" id="persona-awareness-preview" value="<?= htmlspecialchars((string)($initialPersona['niveau_conscience'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>" readonly>
              </div>
            </div>
          </div>

          <input type="hidden" id="persona-id-input" name="persona_id" value="<?= htmlspecialchars((string)($initialPersona['persona_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
          <input type="hidden" id="persona-awareness-input" name="niveau_conscience" value="<?= htmlspecialchars((string)($initialPersona['niveau_conscience'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </section>

        <div class="form-group">
          <label for="contenu-input">Contenu</label>
          <textarea id="contenu-input" name="contenu" rows="18" placeholder="Rédigez votre article ici..."><?= $v('contenu') ?></textarea>
          <small>Éditeur simple (textarea). Peut être remplacé plus tard par un éditeur riche.</small>
        </div>

        <div class="form-group">
          <label for="meta-desc-input">Meta description</label>
          <textarea id="meta-desc-input" name="meta_desc" maxlength="160" rows="3" placeholder="Description SEO de la page (160 caractères max)"><?= $v('meta_desc') ?></textarea>
          <div class="char-count" id="meta-count">0/160</div>
        </div>
      </div>

      <div class="col-side">
        <div class="side-box strategy-preview">
          <h3>Prévisualisation stratégique</h3>
          <ul>
            <li><strong>Cible :</strong> <span id="strategy-target"><?= htmlspecialchars($initialTarget ? $targetOptions[$initialTarget]['label'] : '—', ENT_QUOTES, 'UTF-8') ?></span></li>
            <li><strong>Raison :</strong> <span id="strategy-reason"><?= htmlspecialchars($initialReason && isset($targetOptions[$initialTarget]['reasons'][$initialReason]) ? $targetOptions[$initialTarget]['reasons'][$initialReason] : '—', ENT_QUOTES, 'UTF-8') ?></span></li>
            <li><strong>Persona :</strong> <span id="strategy-persona"><?= htmlspecialchars((string)$initialPersona['label'], ENT_QUOTES, 'UTF-8') ?></span></li>
            <li><strong>Niveau de conscience :</strong> <span id="strategy-awareness"><?= htmlspecialchars((string)($initialPersona['niveau_conscience'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></span></li>
          </ul>
        </div>

        <div class="side-box">
          <h3>Publication</h3>

          <div class="form-group">
            <label for="image-input">Image à la une</label>
            <input type="file" id="image-input" name="featured_image" accept="image/*">
            <small>Champ prêt pour l'upload. Le contrôleur actuel sauvegarde uniquement les données textuelles.</small>
          </div>

          <div class="form-group">
            <label for="statut-input">Statut</label>
            <select id="statut-input" name="statut">
              <?php $statut = $a['statut'] ?? 'brouillon'; ?>
              <option value="brouillon" <?= $statut === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
              <option value="publié" <?= $statut === 'publié' ? 'selected' : '' ?>>Publié</option>
            </select>
          </div>

          <div class="form-group">
            <label for="date-publication-input">Date de publication</label>
            <input type="datetime-local" id="date-publication-input" name="date_publication" value="<?= htmlspecialchars($datePublication, ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <button type="submit" class="btn-primary btn-full">💾 Enregistrer</button>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  const titleInput = document.getElementById('titre-input');
  const slugInput = document.getElementById('slug-input');
  const metaInput = document.getElementById('meta-desc-input');
  const metaCount = document.getElementById('meta-count');

  const targetInput = document.getElementById('persona-target-input');
  const reasonInput = document.getElementById('persona-reason-input');
  const personaLabelInput = document.getElementById('persona-label-input');
  const personaDescription = document.getElementById('persona-description');
  const personaIdPreview = document.getElementById('persona-id-preview');
  const personaAwarenessPreview = document.getElementById('persona-awareness-preview');
  const personaIdInput = document.getElementById('persona-id-input');
  const personaAwarenessInput = document.getElementById('persona-awareness-input');

  const strategyTarget = document.getElementById('strategy-target');
  const strategyReason = document.getElementById('strategy-reason');
  const strategyPersona = document.getElementById('strategy-persona');
  const strategyAwareness = document.getElementById('strategy-awareness');

  const initialReason = <?= json_encode($initialReason, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const targetOptions = <?= json_encode($targetOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const mapping = <?= json_encode($mapping, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const catalog = <?= json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  function slugify(value) {
    return value
      .toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function getFallbackPersonaId(target) {
    if (target === 'vendeur') return 'vendeur_generic';
    if (target === 'acheteur') return 'acheteur_generic';
    return '';
  }

  function getResolvedPersona(target, reason) {
    let personaId = '';
    if (target && reason && mapping[target + ':' + reason]) {
      personaId = mapping[target + ':' + reason];
    }

    if (!personaId) {
      personaId = getFallbackPersonaId(target);
    }

    const persona = personaId && catalog[personaId]
      ? catalog[personaId]
      : {label: 'Aucun persona détecté', description: 'Sélectionnez une cible puis une raison principale.', niveau_conscience: null};

    return { personaId, persona };
  }

  function fillReasonOptions(selectedReason) {
    const target = targetInput.value;
    const reasons = target && targetOptions[target] ? targetOptions[target].reasons : {};

    reasonInput.innerHTML = '';

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Sélectionner une raison';
    reasonInput.appendChild(defaultOption);

    Object.entries(reasons).forEach(([reasonValue, reasonLabel]) => {
      const option = document.createElement('option');
      option.value = reasonValue;
      option.textContent = reasonLabel;
      if (selectedReason === reasonValue) {
        option.selected = true;
      }
      reasonInput.appendChild(option);
    });

    if (!reasons[selectedReason]) {
      reasonInput.value = '';
    }
  }

  function refreshPersona() {
    const target = targetInput.value;
    const reason = reasonInput.value;
    const resolved = getResolvedPersona(target, reason);
    const persona = resolved.persona;

    personaLabelInput.value = persona.label;
    personaDescription.textContent = persona.description;
    personaIdPreview.value = resolved.personaId;
    personaAwarenessPreview.value = persona.niveau_conscience ?? '—';
    personaIdInput.value = resolved.personaId;
    personaAwarenessInput.value = persona.niveau_conscience ?? '';

    strategyTarget.textContent = target && targetOptions[target] ? targetOptions[target].label : '—';
    strategyReason.textContent = target && reason && targetOptions[target] && targetOptions[target].reasons[reason]
      ? targetOptions[target].reasons[reason]
      : '—';
    strategyPersona.textContent = persona.label;
    strategyAwareness.textContent = persona.niveau_conscience ?? '—';
  }

  if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
      if (!slugInput.dataset.manual) {
        slugInput.value = slugify(titleInput.value);
      }
    });

    slugInput.addEventListener('input', function () {
      slugInput.dataset.manual = '1';
      slugInput.value = slugify(slugInput.value);
    });
  }

  if (metaInput && metaCount) {
    const updateCount = function () {
      metaCount.textContent = metaInput.value.length + '/160';
    };
    metaInput.addEventListener('input', updateCount);
    updateCount();
  }

  fillReasonOptions(initialReason);
  refreshPersona();

  targetInput.addEventListener('change', function () {
    fillReasonOptions('');
    refreshPersona();
  });

  reasonInput.addEventListener('change', refreshPersona);
})();
</script>
</body>
</html>
