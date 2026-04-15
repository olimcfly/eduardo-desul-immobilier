<?php
declare(strict_types=1);

$siteSettings = $siteSettings ?? [];

$advisorName = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Votre conseiller'));
$advisorCity = $advisorCity ?? ($siteSettings['city'] ?? ($_ENV['APP_CITY'] ?? 'Votre ville'));

$pageTitle = 'Plan du site';
$metaDesc = "Plan du site HTML de {$advisorName} : accès rapide à toutes les pages principales.";

$mainPages = $siteSettings['sitemap_main_pages'] ?? [
    ['label' => 'Accueil', 'url' => '/'],
    ['label' => 'À propos', 'url' => '/a-propos'],
    ['label' => 'Contact', 'url' => '/contact'],
    ['label' => 'Services', 'url' => '/services'],
    ['label' => 'Biens à vendre', 'url' => '/biens'],
    ['label' => 'Guide local', 'url' => '/guide-local'],
    ['label' => 'Ressources', 'url' => '/ressources'],
    ['label' => 'Blog', 'url' => '/blog'],
    ['label' => 'Actualités', 'url' => '/actualites'],
    ['label' => 'Avis clients', 'url' => '/avis-clients'],
];

$legalPages = $siteSettings['sitemap_legal_pages'] ?? [
    ['label' => 'Mentions légales', 'url' => '/mentions-legales'],
    ['label' => 'Politique de confidentialité', 'url' => '/politique-confidentialite'],
    ['label' => 'Politique cookies', 'url' => '/politique-cookies'],
    ['label' => 'Conditions générales de vente', 'url' => '/cgv'],
];

if (is_string($mainPages)) {
    $decoded = json_decode($mainPages, true);
    $mainPages = is_array($decoded) ? $decoded : [];
}

if (is_string($legalPages)) {
    $decoded = json_decode($legalPages, true);
    $legalPages = is_array($decoded) ? $decoded : [];
}
?>

<section class="section">
    <div class="container">
        <h1>Plan du site</h1>
        <p>Retrouvez ci-dessous les pages principales du site de <?= e($advisorName) ?><?= $advisorCity !== '' ? ' à ' . e($advisorCity) : '' ?>.</p>

        <h2>Pages principales</h2>
        <ul>
            <?php foreach ($mainPages as $page): ?>
                <li>
                    <a href="<?= e(url((string) ($page['url'] ?? '/'))) ?>">
                        <?= e((string) ($page['label'] ?? 'Page')) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Pages légales</h2>
        <ul>
            <?php foreach ($legalPages as $page): ?>
                <li>
                    <a href="<?= e(url((string) ($page['url'] ?? '/'))) ?>">
                        <?= e((string) ($page['label'] ?? 'Page légale')) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>