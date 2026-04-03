<?php
require_once __DIR__ . '/includes/_bootstrap.php';
$pageTitle = 'Social';
$pageDescription = 'Gérez vos publications et réseaux sociaux';
$stats = (new SocialService())->getHubStats(socialUserId());
require_once __DIR__ . '/../../admin/views/layout.php';

function renderContent()
{
    global $stats;
    ?>
    <link rel="stylesheet" href="/modules/social/assets/social.css">
    <div class="social-header">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &gt; Social</div>
        <h1><span class="icon-blue">↔</span> <span class="hub">HUB</span> <span class="social">Social</span></h1>
        <p>Gérez vos publications et réseaux sociaux</p>
    </div>
    <div class="social-search"><input type="text" id="social-hub-search" placeholder="Rechercher un module social..."></div>
    <div class="social-cards" id="social-cards">
        <?php socialHubCard('facebook', 'Facebook', '#1877f2', 'Posts', 'Reels', 'Gérer', '/admin/?module=social&action=facebook', 'Planifiez et publiez vos posts sur votre page Facebook professionnelle.', (int) ($stats['facebook']['posts_ce_mois'] ?? 0), (int) ($stats['facebook']['abonnes'] ?? 0), 'abonnés'); ?>
        <?php socialHubCard('instagram', 'Instagram', '#e1306c', 'Stories', 'Carrousels', 'Gérer', '/admin/?module=social&action=instagram', 'Partagez vos biens et votre expertise sur Instagram.', (int) ($stats['instagram']['posts_ce_mois'] ?? 0), (int) ($stats['instagram']['abonnes'] ?? 0), 'abonnés'); ?>
        <?php socialHubCard('linkedin', 'LinkedIn', '#0077b5', 'Articles', 'Réseau', 'Gérer', '/admin/?module=social&action=linkedin', 'Développez votre réseau professionnel et votre personal branding.', (int) ($stats['linkedin']['posts_ce_mois'] ?? 0), (int) ($stats['linkedin']['abonnes'] ?? 0), 'relations'); ?>
        <?php socialHubCard('calendrier', 'Calendrier éditorial', '#f59e0b', 'Planning', 'Multi-réseau', 'Planifier', '/admin/?module=social&action=calendrier', 'Planifiez vos publications sur tous les réseaux depuis un seul endroit.', (int) ($stats['facebook']['planifies_a_venir'] + $stats['instagram']['planifies_a_venir'] + $stats['linkedin']['planifies_a_venir']), 0, 'planifiés à venir', true); ?>
    </div>
    <script src="/modules/social/assets/social.js"></script>
    <?php
}

function socialHubCard(string $slug, string $title, string $color, string $tag1, string $tag2, string $action, string $href, string $description, int $postsMonth, int $followers, string $followersLabel, bool $calendar = false): void
{
    ?>
    <article class="social-card" data-network="<?= e($slug) ?>" style="--card-color:<?= e($color) ?>">
        <h3><?= e($title) ?></h3>
        <p><?= e($description) ?></p>
        <div class="badges"><span><?= e($tag1) ?></span><span><?= e($tag2) ?></span></div>
        <a class="go" href="<?= e($href) ?>">→ <?= e($action) ?></a>
        <div class="stats"><?= $postsMonth ?> publiés ce mois<?= $calendar ? '' : ' / ' . $followers . ' ' . e($followersLabel) ?></div>
    </article>
    <?php
}
