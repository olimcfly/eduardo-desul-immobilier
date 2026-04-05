<?php
$pageTitle = (string) get_setting('site_meta_title', 'Vendez au juste prix. Achetez en toute sérénité.');
$metaDesc  = (string) get_setting('site_meta_description', 'Vendez votre bien au juste prix et achetez en toute sérénité avec un accompagnement immobilier local.');

// Récupération des sections CMS (JSON stocké en settings)
$hero = get_page_content('home', 'hero');
$about = get_page_content('home', 'about');
$services = get_page_content('home', 'services');
$testimonials = get_page_content('home', 'testimonials'); // Peut être alimenté par gmb_reviews côté back
$faq = get_page_content('home', 'faq');

$heroTitle = strip_tags((string) ($hero['title'] ?? 'Vendez au juste prix.<br>Achetez en toute sérénité.'), '<br>');
?>

<!-- Hero Section -->
<section class="hero">
    <h1><?= $heroTitle ?></h1>
    <p><?= e((string) ($hero['subtitle'] ?? 'Texte par défaut...')) ?></p>

    <div class="hero__trust trust-items">
        <?php foreach (($hero['trust_items'] ?? []) as $item): ?>
            <div class="trust-item">
                <span><?= e((string) ($item['value'] ?? '')) ?></span>
                <span><?= e((string) ($item['label'] ?? '')) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- À propos Section -->
<section class="about section section--alt">
    <div class="container">
        <h2><?= e((string) ($about['title'] ?? 'Expertise locale')) ?></h2>
        <p><?= e((string) ($about['text1'] ?? 'Texte par défaut...')) ?></p>

        <div class="stats">
            <?php foreach (($about['stats'] ?? []) as $stat): ?>
                <div class="stat">
                    <span class="number"><?= e((string) ($stat['value'] ?? '')) ?></span>
                    <span class="label"><?= e((string) ($stat['label'] ?? '')) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services section">
    <div class="container">
        <h2><?= e((string) ($services['title'] ?? 'Mes services')) ?></h2>

        <div class="service-grid">
            <?php foreach (($services['items'] ?? []) as $service): ?>
                <div class="service-card">
                    <h3><?= e((string) ($service['title'] ?? '')) ?></h3>
                    <p><?= e((string) ($service['description'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
