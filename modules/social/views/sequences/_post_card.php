<?php
$postId    = (int) ($post['id'] ?? 0);
$titre     = (string) ($post['titre'] ?? 'Publication');
$contenu   = (string) ($post['contenu'] ?? '');
$statut    = (string) ($post['statut'] ?? 'brouillon');
$reseaux   = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: [];
$niveau    = (string) ($post['niveau'] ?? '');    // n1..n5 ou vide
$ordre     = (int) ($post['ordre_sequence'] ?? ($i + 1));  // $i défini dans la boucle parent
$planifieAt= (string) ($post['planifie_at'] ?? '');

/* Jour relatif */
$dayLabel = '—';
if (!empty($planifieAt)) {
    $dayLabel = date('d/m', strtotime($planifieAt));
}

/* Classes niveau */
$barClass = $niveau ? 'bar-' . $niveau : 'bar-none';
$numClass = $niveau ? 'num-' . $niveau : 'num-none';
$nbClass  = $niveau ? 'nb-'  . $niveau : '';
$nLabel   = $niveau ? strtoupper($niveau) : '—';

/* Libellé réseau */
$netMap = [
    'facebook'           => ['label' => 'FB',  'class' => 'pnet-fb'],
    'instagram'          => ['label' => 'IG',  'class' => 'pnet-ig'],
    'linkedin'           => ['label' => 'LI',  'class' => 'pnet-li'],
    'google_my_business' => ['label' => 'GMB', 'class' => 'pnet-gmb'],
];

/* Statut dot */
$sdotClass = 'sdot-' . $statut;
?>
<a href="/admin?module=social&action=post&id=<?= $postId ?>"
   class="seq-post-card">
    <!-- Barre niveau -->
    <div class="post-niveau-bar <?= $barClass ?>"></div>

    <!-- Head : numéro + badge niveau -->
    <div class="post-card-head">
        <div class="post-num <?= $numClass ?>"><?= $ordre ?></div>
        <?php if ($niveau): ?>
            <div class="post-niveau-badge <?= $nbClass ?>"><?= htmlspecialchars(strtoupper($niveau)) ?></div>
        <?php endif; ?>
    </div>

    <!-- Réseaux -->
    <div class="post-net-row">
        <?php foreach ($reseaux as $r): ?>
            <?php $nInfo = $netMap[$r] ?? ['label' => strtoupper((string)$r), 'class' => 'pnet-fb']; ?>
            <span class="pnet <?= $nInfo['class'] ?>"><?= htmlspecialchars($nInfo['label']) ?></span>
        <?php endforeach; ?>
    </div>

    <!-- Texte -->
    <div class="seq-post-text"><?= htmlspecialchars($contenu ?: $titre) ?></div>

    <!-- Footer -->
    <div class="seq-post-footer">
        <span class="post-day-lbl"><?= htmlspecialchars($dayLabel) ?></span>
        <span class="sdot <?= htmlspecialchars($sdotClass) ?>"></span>
    </div>
</a>
