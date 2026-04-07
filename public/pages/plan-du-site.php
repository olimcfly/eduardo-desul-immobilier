<?php
$pageTitle = 'Plan du site — Eduardo Desul Immobilier';
$metaDesc = 'Navigation rapide vers toutes les pages principales du site.';

$sections = [
    'Pages principales' => [
        ['/', 'Accueil'],
        ['/a-propos', 'À propos'],
        ['/services', 'Services'],
        ['/biens', 'Biens immobiliers'],
        ['/contact', 'Contact'],
    ],
    'Contenus' => [
        ['/blog', 'Blog immobilier'],
        ['/actualites', 'Actualités'],
        ['/guide-local', 'Guide local'],
        ['/ressources', 'Ressources'],
    ],
    'Légal' => [
        ['/mentions-legales', 'Mentions légales'],
        ['/politique-confidentialite', 'Politique de confidentialité'],
        ['/politique-cookies', 'Politique cookies'],
        ['/cgv', 'Conditions générales'],
    ],
];
?>

<section class="section">
    <div class="container" style="max-width:960px">
        <header style="margin-bottom:2rem">
            <h1>Plan du site</h1>
            <p>Accédez rapidement à toutes les rubriques principales.</p>
        </header>

        <?php foreach ($sections as $title => $links): ?>
            <div style="margin-bottom:1.5rem">
                <h2><?= e($title) ?></h2>
                <ul>
                    <?php foreach ($links as [$href, $label]): ?>
                        <li><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>
