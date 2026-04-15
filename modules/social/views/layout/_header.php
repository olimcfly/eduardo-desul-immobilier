<?php
$activeAction = isset($_GET['action']) ? (string) $_GET['action'] : 'sequences';
$isJournal    = $activeAction === 'journal';

$advisorCity = setting('zone_city', 'Aix-en-Provence');

$personaFilters = [
    ''             => 'Tous',
    'vendeur'      => '👴 Vendeur Senior',
    'famille'      => '👨‍👩‍👧 Famille',
    'primo'        => '🚀 Primo-accédant',
    'investisseur' => '📈 Investisseur',
    'expatrie'     => '✈️ Expatrié',
];

$currentPersona = isset($_GET['persona']) ? (string) $_GET['persona'] : '';
$currentStatus  = isset($_GET['status']) ? (string) $_GET['status'] : '';
?>
<div class="social-wrap">

<div class="social-header">
    <div class="social-header-left">
        <div>
            <h1 class="social-header-title"><?= $isJournal ? 'Suivez vos publications en temps réel' : 'Obtenez plus de prises de contact via vos réseaux' ?></h1>
            <div class="social-header-sub">Plan simple, action rapide · <?= htmlspecialchars($advisorCity) ?></div>
        </div>

        <div class="social-view-toggle">
            <a href="/admin?module=social&action=sequences"
               class="svt-btn<?= !$isJournal ? ' is-active' : '' ?>">
                <i class="fas fa-th"></i> Séquences
            </a>
            <a href="/admin?module=social&action=journal"
               class="svt-btn<?= $isJournal ? ' is-active' : '' ?>">
                <i class="fas fa-list-ul"></i> Journal
            </a>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <?php if (!$isJournal): ?>
        <div class="social-legend">
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n1)"></div><span class="s-leg-lbl">N1 Inconscient</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n2)"></div><span class="s-leg-lbl">N2 Problème</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n3)"></div><span class="s-leg-lbl">N3 Solutions</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n4)"></div><span class="s-leg-lbl">N4 Évaluation</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n5)"></div><span class="s-leg-lbl">N5 Action</span></div>
        </div>
        <?php endif; ?>

        <a href="/admin?module=social&action=post-form" class="s-btn-new">
            <i class="fas fa-plus"></i>
            <?= $isJournal ? 'Nouvelle publication' : 'Nouvelle séquence' ?>
        </a>
        <a href="/admin?module=social&action=kit" class="s-btn-new" style="background:#1e293b;">
            <i class="fas fa-wand-magic-sparkles"></i> Kit publications
        </a>
    </div>
</div>

<?php if (!$isJournal): ?>
<div class="social-filters">
    <span class="s-filter-label">Persona</span>
    <div class="s-filter-chips" data-filter-group="persona">
        <?php foreach ($personaFilters as $value => $label): ?>
            <span class="s-chip<?= $currentPersona === $value ? ' is-active' : '' ?>"
                  data-filter-value="<?= htmlspecialchars($value) ?>">
                <?= htmlspecialchars($label) ?>
            </span>
        <?php endforeach; ?>
    </div>

    <div class="s-filter-sep"></div>

    <span class="s-filter-label">Statut</span>
    <div class="s-filter-chips" data-filter-group="status">
        <span class="s-chip<?= $currentStatus === '' ? ' is-active' : '' ?>" data-filter-value="all">Toutes</span>
        <span class="s-chip<?= $currentStatus === 'active' ? ' is-active' : '' ?>" data-filter-value="active">
            <span class="sdot sdot-publie"></span> Active
        </span>
        <span class="s-chip<?= $currentStatus === 'pause' ? ' is-active' : '' ?>" data-filter-value="pause">⏸ En pause</span>
        <span class="s-chip<?= $currentStatus === 'brouillon' ? ' is-active' : '' ?>" data-filter-value="brouillon">✏️ Brouillon</span>
    </div>
</div>
<?php endif; ?>
