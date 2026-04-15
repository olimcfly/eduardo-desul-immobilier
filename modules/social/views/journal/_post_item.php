<?php
$postId    = (int) ($post['id'] ?? 0);
$titre     = (string) ($post['titre'] ?? 'Publication');
$contenu   = (string) ($post['contenu'] ?? '');
$statut    = (string) ($post['statut'] ?? 'brouillon');
$reseaux   = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: [];
$planifie  = (string) ($post['planifie_at'] ?? '');
$publie    = (string) ($post['publie_at'] ?? '');
$categorie = (string) ($post['categorie'] ?? 'autre');

/* Date / heure affichage */
$dateRef = $planifie ?: ($publie ?: (string) ($post['created_at'] ?? ''));
$ts      = $dateRef ? strtotime($dateRef) : time();

$frMonths = ['jan','fév','mars','avr','mai','juin','juil','août','sep','oct','nov','déc'];
$dayNum   = (int) date('j', $ts);
$monShort = strtoupper($frMonths[(int) date('n', $ts) - 1]);
$timeStr  = $dateRef ? date('H:i', $ts) : '—';

/* Statut */
$statusClassMap = [
    'planifie'  => ['card' => 'is-planifie',  'dot' => 'sdot-planifie',  'label' => 'Planifiée',  'color' => 'var(--s-blue)'],
    'publie'    => ['card' => 'is-publie',     'dot' => 'sdot-publie',    'label' => 'Publiée',    'color' => 'var(--s-green)'],
    'brouillon' => ['card' => 'is-brouillon',  'dot' => 'sdot-brouillon', 'label' => 'Brouillon',  'color' => 'var(--s-orange)'],
    'erreur'    => ['card' => 'is-erreur',     'dot' => 'sdot-erreur',    'label' => 'Échec',      'color' => 'var(--s-red)'],
];

$si = $statusClassMap[$statut] ?? $statusClassMap['brouillon'];

/* Réseaux */
$netMap = [
    'facebook'           => ['label' => 'FB',  'class' => 'pub-net-fb'],
    'instagram'          => ['label' => 'IG',  'class' => 'pub-net-ig'],
    'linkedin'           => ['label' => 'LI',  'class' => 'pub-net-li'],
    'google_my_business' => ['label' => 'GMB', 'class' => 'pub-net-gmb'],
];

/* Miniature emoji selon catégorie */
$thumbEmoji = match($categorie) {
    'bien'       => '🏠',
    'marche'     => '📊',
    'temoignage' => '⭐',
    'conseil'    => '✍️',
    'equipe'     => '👤',
    default      => '📋',
};

$thumbBg = 'thumb-' . $categorie;

/* Métriques (si publié) */
$likes    = (int) (($post['fb_likes'] ?? 0) + ($post['ig_likes'] ?? 0) + ($post['li_likes'] ?? 0));
$comments = (int) (($post['fb_comments'] ?? 0) + ($post['ig_comments'] ?? 0) + ($post['li_comments'] ?? 0));
?>
<a href="/admin?module=social&action=post&id=<?= $postId ?>"
   class="pub-card <?= $si['card'] ?>">

    <!-- Colonne date -->
    <div class="pub-date-col">
        <div class="pub-day"><?= $dayNum ?></div>
        <div class="pub-month"><?= $monShort ?></div>
        <div class="pub-time"><?= htmlspecialchars($timeStr) ?></div>
    </div>

    <div class="pub-divider"></div>

    <!-- Corps -->
    <div class="pub-body">
        <!-- Badges réseaux -->
        <div class="pub-networks">
            <?php foreach ($reseaux as $r): ?>
                <?php $nInfo = $netMap[$r] ?? ['label' => strtoupper((string) $r), 'class' => 'pub-net-fb']; ?>
                <span class="pub-net-badge <?= $nInfo['class'] ?>"><?= htmlspecialchars($nInfo['label']) ?></span>
            <?php endforeach; ?>
        </div>

        <!-- Texte -->
        <div class="pub-text"><?= htmlspecialchars($contenu ?: $titre) ?></div>

        <!-- Meta statut + métriques -->
        <div class="pub-meta">
            <div class="pub-status">
                <span class="sdot <?= $si['dot'] ?>"></span>
                <span style="color:<?= $si['color'] ?>"><?= $si['label'] ?></span>
                <?php if ($statut === 'erreur'): ?>
                    · <span style="color:var(--s-red);font-size:10px;cursor:pointer">Republier →</span>
                <?php elseif ($statut === 'brouillon'): ?>
                    · <span style="color:var(--s-orange);font-size:10px;cursor:pointer">Compléter →</span>
                <?php endif; ?>
            </div>

            <?php if ($statut === 'publie' && ($likes > 0 || $comments > 0)): ?>
            <div class="pub-metrics">
                <?php if ($likes > 0): ?>
                <div class="pub-metric">
                    <i class="fas fa-heart" style="font-size:9px"></i>
                    <?= $likes ?>
                </div>
                <?php endif; ?>
                <?php if ($comments > 0): ?>
                <div class="pub-metric">
                    <i class="fas fa-comment" style="font-size:9px"></i>
                    <?= $comments ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Miniature -->
    <div class="pub-thumb <?= $thumbBg ?>">
        <?= $thumbEmoji ?>
    </div>
</a>
