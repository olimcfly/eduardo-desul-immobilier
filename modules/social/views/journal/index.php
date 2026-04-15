<?php
$posts       = $posts ?? [];
$stats       = $stats ?? ['planifie' => 0, 'publie' => 0, 'brouillon' => 0, 'erreur' => 0];
$weekData    = $weekData ?? ['days' => [], 'monthLabel' => '', 'weekOffset' => 0];
$postsByDate = $postsByDate ?? [];

$weekOffset  = (int) ($weekData['weekOffset'] ?? 0);
$monthLabel  = (string) ($weekData['monthLabel'] ?? '');
$days        = $weekData['days'] ?? [];

$netColors = [
    'facebook'           => '#1877F2',
    'instagram'          => '#E1306C',
    'linkedin'           => '#0A66C2',
    'google_my_business' => '#34A853',
];

$totalPosts = count($posts);
$netCounts  = [];
foreach ($posts as $p) {
    foreach (json_decode((string) ($p['reseaux'] ?? '[]'), true) ?: [] as $r) {
        $netCounts[$r] = ($netCounts[$r] ?? 0) + 1;
    }
}

$today = date('Y-m-d');
?>
<div class="social-journal">

    <!-- ── ONGLETS RÉSEAUX ── -->
    <div class="journal-net-tabs">
        <a href="/admin?module=social&action=journal" class="jnet-tab is-active">
            <span class="jnet-dot" style="background:var(--s-gold)"></span>
            Tous (<?= $totalPosts ?>)
        </a>
        <?php foreach ([
            'facebook'           => ['label' => 'Facebook', 'color' => '#1877F2'],
            'instagram'          => ['label' => 'Instagram','color' => '#E1306C'],
            'linkedin'           => ['label' => 'LinkedIn', 'color' => '#0A66C2'],
            'google_my_business' => ['label' => 'GMB',      'color' => '#34A853'],
        ] as $net => $info):
            if (empty($netCounts[$net])) continue;
        ?>
        <a href="/admin?module=social&action=journal&network=<?= urlencode($net) ?>" class="jnet-tab">
            <span class="jnet-dot" style="background:<?= $info['color'] ?>"></span>
            <?= htmlspecialchars($info['label']) ?> (<?= $netCounts[$net] ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ── BANDE SEMAINE ── -->
    <div class="journal-week-strip">
        <div class="week-nav">
            <span class="week-label"><?= htmlspecialchars($monthLabel) ?></span>
            <div class="week-arrows">
                <a href="/admin?module=social&action=journal&week=<?= $weekOffset - 1 ?>" class="week-arrow">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="/admin?module=social&action=journal&week=<?= $weekOffset + 1 ?>" class="week-arrow">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <div class="week-days">
            <?php foreach ($days as $day): ?>
            <div class="week-day">
                <span class="day-name"><?= htmlspecialchars($day['dayName']) ?></span>
                <div class="day-num<?= $day['isToday'] ? ' is-today' : '' ?>">
                    <?= (int) $day['day'] ?>
                </div>
                <div class="day-dots">
                    <?php foreach ($day['posts'] as $dp):
                        $dpNets   = json_decode((string) ($dp['reseaux'] ?? '[]'), true) ?: [];
                        $dotColor = $netColors[$dpNets[0] ?? ''] ?? 'var(--s-gray)';
                    ?>
                    <div class="day-dot" style="background:<?= htmlspecialchars($dotColor) ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── STATS MINI ── -->
    <div class="journal-stats">
        <div class="jstat-item">
            <div class="jstat-val"><?= (int) $stats['planifie'] ?></div>
            <div class="jstat-lbl">Planifiées</div>
        </div>
        <div class="jstat-item">
            <div class="jstat-val" style="color:var(--s-green)"><?= (int) $stats['publie'] ?></div>
            <div class="jstat-lbl">Publiées</div>
        </div>
        <div class="jstat-item">
            <div class="jstat-val" style="color:var(--s-orange)"><?= (int) $stats['brouillon'] ?></div>
            <div class="jstat-lbl">Brouillons</div>
        </div>
        <div class="jstat-item">
            <div class="jstat-val" style="<?= (int) $stats['erreur'] > 0 ? 'color:var(--s-red)' : '' ?>">
                <?= (int) $stats['erreur'] ?>
            </div>
            <div class="jstat-lbl">Échecs</div>
        </div>
    </div>

    <!-- ── LISTE PAR DATE ── -->
    <?php if (empty($postsByDate)): ?>
    <div class="s-empty-card">
        <h3>Journal vide</h3>
        <p>Programmez des publications pour voir votre timeline sociale apparaître ici.</p>
        <a href="/admin?module=social&action=post-form" class="s-btn-new" style="margin:0 auto;">
            <i class="fas fa-plus"></i> Créer une publication
        </a>
    </div>
    <?php else: ?>

    <?php foreach ($postsByDate as $dateKey => $datePosts): ?>
        <?php
        $ts = strtotime($dateKey);
        $frDays   = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $frMonths = ['jan','fév','mars','avr','mai','juin','juil','août','sep','oct','nov','déc'];
        $dayName  = $frDays[(int) date('w', $ts)];
        $dayNum   = (int) date('j', $ts);
        $monName  = $frMonths[(int) date('n', $ts) - 1];

        $isToday     = ($dateKey === $today);
        $isTomorrow  = ($dateKey === date('Y-m-d', strtotime('+1 day')));
        $isYesterday = ($dateKey === date('Y-m-d', strtotime('-1 day')));

        if ($isToday)       $sepLabel = "Aujourd'hui — {$dayName} {$dayNum} {$monName}";
        elseif ($isTomorrow)  $sepLabel = "Demain — {$dayName} {$dayNum} {$monName}";
        elseif ($isYesterday) $sepLabel = "Hier — {$dayName} {$dayNum} {$monName}";
        else                  $sepLabel = "{$dayName} {$dayNum} {$monName}";
        ?>

        <div class="journal-date-sep">
            <div class="date-sep-line"></div>
            <div class="date-sep-label"><?= htmlspecialchars($sepLabel) ?></div>
            <div class="date-sep-line"></div>
        </div>

        <div class="journal-day-group">
            <?php foreach ($datePosts as $post): ?>
                <?php include __DIR__ . '/_post_item.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php endif; ?>

</div><!-- /.social-journal -->
</div><!-- /.social-wrap — ouvert dans _header.php -->

<!-- FAB -->
<a href="/admin?module=social&action=post-form" class="social-fab" title="Nouvelle publication">
    <i class="fas fa-plus"></i>
</a>
