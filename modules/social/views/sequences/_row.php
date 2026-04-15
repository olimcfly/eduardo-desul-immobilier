<?php
$sequenceId = (int) ($sequence['id'] ?? 0);
$posts      = $postBySequence[$sequenceId] ?? [];
$statut     = (string) ($sequence['statut'] ?? 'active');
$persona    = (string) ($sequence['persona'] ?? 'Persona');
$objectif   = (string) ($sequence['objectif'] ?? '');
$zone       = (string) ($sequence['zone'] ?? setting('zone_city', 'Aix-en-Provence'));

/* Emoji + couleur selon persona */
$personaMap = [
    'vendeur'       => ['emoji' => '👴', 'bg' => '#fef3c7', 'desc' => 'Liberté + Sécurité'],
    'famille'       => ['emoji' => '👨‍👩‍👧', 'bg' => '#fce7f3', 'desc' => 'Sécurité + Reconnaissance'],
    'primo'         => ['emoji' => '🚀', 'bg' => '#dbeafe', 'desc' => 'Sécurité'],
    'investisseur'  => ['emoji' => '📈', 'bg' => '#ede4f8', 'desc' => 'Contrôle + Reconnaissance'],
    'expatrie'      => ['emoji' => '✈️', 'bg' => '#dcfce7', 'desc' => 'Confiance'],
];

$personaKey = 'autre';
foreach ($personaMap as $key => $_) {
    if (str_contains(mb_strtolower($persona), $key)) {
        $personaKey = $key;
        break;
    }
}

$pInfo = $personaMap[$personaKey] ?? ['emoji' => '👤', 'bg' => '#f1f5f9', 'desc' => 'Persona libre'];

/* Objectif → tag couleur */
preg_match('/N(\d)/i', $objectif, $m1);
$nMin = (int) ($m1[1] ?? 2);
$nTagClass = 'seq-tag-n' . $nMin;

/* Réseaux distincts de la séquence */
$seqNetworks = [];
foreach ($posts as $p) {
    foreach (json_decode((string) ($p['reseaux'] ?? '[]'), true) ?: [] as $r) {
        $seqNetworks[$r] = true;
    }
}

$netLabels = ['facebook' => 'FB', 'instagram' => 'IG', 'linkedin' => 'LI', 'google_my_business' => 'GMB'];

/* Stats séquence */
$nbPublies   = count(array_filter($posts, fn($p) => ($p['statut'] ?? '') === 'publie'));
$nbPlanifies = count(array_filter($posts, fn($p) => ($p['statut'] ?? '') === 'planifie'));
$nbBrouillons= count(array_filter($posts, fn($p) => ($p['statut'] ?? '') === 'brouillon'));

/* Prochain post planifié */
$nextPost = null;
foreach ($posts as $p) {
    if (($p['statut'] ?? '') === 'planifie' && !empty($p['planifie_at'])) {
        if ($nextPost === null || strtotime($p['planifie_at']) < strtotime($nextPost['planifie_at'])) {
            $nextPost = $p;
        }
    }
}

/* Statut → classe CSS */
$statusClass = match($statut) {
    'active'    => 'is-active',
    'pause'     => 'is-pause',
    default     => 'is-brouillon',
};
?>
<div class="seq-row" id="seq-<?= $sequenceId ?>"
     data-statut="<?= htmlspecialchars($statut) ?>"
     data-persona="<?= htmlspecialchars(mb_strtolower($persona)) ?>">

    <!-- EN-TÊTE SÉQUENCE (clic = ouvre/ferme) -->
    <div class="seq-head">
        <!-- Persona -->
        <div class="seq-persona">
            <div class="seq-persona-emo" style="background:<?= $pInfo['bg'] ?>">
                <?= $pInfo['emoji'] ?>
            </div>
            <div>
                <div class="seq-persona-name"><?= htmlspecialchars($persona) ?></div>
                <div class="seq-persona-desc"><?= htmlspecialchars($pInfo['desc']) ?></div>
            </div>
        </div>

        <!-- Tags meta -->
        <div class="seq-meta">
            <span class="seq-tag">
                <i class="fas fa-location-dot"></i>
                <?= htmlspecialchars($zone) ?>
            </span>
            <?php if (!empty($seqNetworks)): ?>
            <span class="seq-tag seq-tag-blue">
                <?= htmlspecialchars(implode(' · ', array_map(fn($r) => $netLabels[$r] ?? strtoupper($r), array_keys($seqNetworks)))) ?>
            </span>
            <?php endif; ?>
            <?php if ($objectif): ?>
            <span class="seq-tag <?= $nTagClass ?>">
                <?= htmlspecialchars($objectif) ?>
            </span>
            <?php endif; ?>
            <span class="seq-tag"><?= count($posts) ?> posts</span>
        </div>

        <!-- Stats -->
        <div class="seq-stats">
            <div class="seq-stat-item">
                <div class="seq-stat-val" style="<?= $nbPublies > 0 ? 'color:var(--s-green)' : '' ?>">
                    <?= $nbPublies ?>
                </div>
                <div class="seq-stat-lbl">Publiés</div>
            </div>
            <div class="seq-stat-item">
                <div class="seq-stat-val"><?= $nbPlanifies ?></div>
                <div class="seq-stat-lbl">Planifiés</div>
            </div>
        </div>

        <!-- Dot statut -->
        <div class="seq-status-dot <?= $statusClass ?>"></div>

        <!-- Chevron -->
        <i class="fas fa-chevron-right seq-chevron"></i>
    </div>

    <!-- GRILLE DES POSTS (visible si is-open) -->
    <div class="seq-posts">
        <?php foreach ($posts as $i => $post): ?>
            <?php if ($i > 0): ?>
                <div class="post-connector"><div class="connector-line"></div></div>
            <?php endif; ?>
            <?php include __DIR__ . '/_post_card.php'; ?>
        <?php endforeach; ?>

        <!-- Bouton ajouter -->
        <div class="post-connector" style="padding-top:30px">
            <div class="connector-line" style="background:none;border-top:2px dashed var(--s-border);height:0;width:22px"></div>
        </div>
        <a href="/admin?module=social&action=post-form&sequence_id=<?= $sequenceId ?>"
           class="post-add-card">
            <div class="post-add-icon">+</div>
            <div class="post-add-lbl">Ajouter un post</div>
        </a>
    </div>

    <!-- BARRE BAS SÉQUENCE -->
    <div class="seq-footer">
        <div class="seq-foot-info">
            <strong><?= $nbPublies ?> publiés</strong> ·
            <?= $nbPlanifies ?> planifiés ·
            <?= $nbBrouillons ?> brouillon<?= $nbBrouillons > 1 ? 's' : '' ?>
            <?php if ($nextPost && !empty($nextPost['planifie_at'])): ?>
                · Prochain : <?= date('d/m', strtotime($nextPost['planifie_at'])) ?>
            <?php endif; ?>
        </div>

        <form method="post" action="/admin?module=social&action=duplicate-sequence" style="display:inline">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $sequenceId ?>">
            <button type="submit" class="s-btn-sm">
                <i class="fas fa-copy"></i> Dupliquer
            </button>
        </form>

        <form method="post" action="/admin?module=social&action=toggle-sequence" style="display:inline">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $sequenceId ?>">
            <button type="submit" class="s-btn-sm">
                <?= $statut === 'pause' ? '<i class="fas fa-play"></i> Reprendre' : '⏸ Mettre en pause' ?>
            </button>
        </form>

        <a href="/admin?module=social&action=sequences&edit=<?= $sequenceId ?>" class="s-btn-sm">
            <i class="fas fa-pen"></i> Modifier
        </a>

        <a href="/admin?module=social&action=journal&sequence=<?= $sequenceId ?>" class="s-btn-sm gold">
            <i class="fas fa-chart-line"></i> Voir les stats
        </a>
    </div>
</div>
