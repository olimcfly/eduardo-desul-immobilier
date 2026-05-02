<?php
$_siteName  = defined('APP_NAME') ? APP_NAME : (defined('ADVISOR_NAME') ? ADVISOR_NAME . ' Immobilier' : 'Expert Immobilier');
$_siteCity  = isset($zoneCity) && $zoneCity !== '' ? $zoneCity : (defined('APP_CITY') ? APP_CITY : '');
$_siteUrl   = defined('APP_URL')  ? APP_URL  : '';
$pageTitle  = 'Avis clients — ' . $_siteName . ($_siteCity !== '' ? ' | ' . $_siteCity : '');
$metaDesc   = 'Découvrez les avis vérifiés de nos clients satisfaits. ' . $_siteName
    . ($_siteCity !== '' ? ', expert immobilier à ' . $_siteCity : ', expert immobilier')
    . ', noté 4.9/5 pour son accompagnement clef en main.';
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "RealEstateAgent",
  "name": "<?= htmlspecialchars($_siteName, ENT_QUOTES) ?>",
  "url": "<?= htmlspecialchars(rtrim($_siteUrl, '/') . '/avis', ENT_QUOTES) ?>",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "bestRating": "5",
    "ratingCount": "124"
  }
}
</script>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Avis clients</span></nav>
        <h1>Ce que disent nos clients</h1>
        <p>Votre satisfaction est notre meilleure récompense</p>
    </div>
</div>

<section class="section">
    <div class="container">

        <!-- Note globale -->
        <div class="rating-summary" data-animate>
            <div class="rating-stars">★★★★★</div>
            <div class="rating-score">4.9<span>/5</span></div>
            <div class="rating-text">Basé sur 124 avis Google et partenaires</div>
            <div class="rating-breakdown">
                <div class="rating-bar">
                    <span>5 étoiles</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 92%"></div>
                    </div>
                </div>
                <div class="rating-bar">
                    <span>4 étoiles</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 6%"></div>
                    </div>
                </div>
                <div class="rating-bar">
                    <span>3 étoiles</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 1%"></div>
                    </div>
                </div>
            </div>
            <a href="https://g.page/r/Ce2xYJzQkZJMEAI" target="_blank" rel="noopener noreferrer" class="btn btn--google">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#4285F4"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.34-1.04 2.48-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Laisser un avis Google
            </a>
        </div>

        <!-- Filtres -->
        <div class="testimonial-filters" data-animate>
            <button class="filter-btn active" data-filter="all">Tous les avis</button>
            <button class="filter-btn" data-filter="achat">Achat</button>
            <button class="filter-btn" data-filter="vente">Vente</button>
            <button class="filter-btn" data-filter="viager">Viager</button>
            <button class="filter-btn" data-filter="financement">Financement</button>
            <button class="filter-btn" data-filter="location">Location</button>
        </div>

        <!-- Grille avis -->
        <div class="testimonial-grid" data-animate>
            <?php
            $avis = [
                [
                    'nom' => 'Famille Martin',
                    'note' => 5,
                    'date' => 'Juin 2024',
                    'service' => 'achat',
                    'ville' => 'Bordeaux',
                    'text' => 'Eduardo nous a trouvé la maison de nos rêves à Caudéran en seulement 3 semaines. Son expertise du marché bordelais et son réseau nous ont fait gagner un temps précieux. Un accompagnement 360° vraiment professionnel !',
                    'photo' => 'https://randomuser.me/api/portraits/family/1.jpg'
                ],
                [
                    'nom' => 'Claire D.',
                    'note' => 5,
                    'date' => 'Mai 2024',
                    'service' => 'viager',
                    'ville' => 'Mérignac',
                    'text' => 'La solution de viager proposée par Eduardo a été parfaite pour nous. Transparence totale, explications claires et un suivi impeccable. Nous recommandons vivement son approche éthique et humaine.',
                    'photo' => 'https://randomuser.me/api/portraits/women/44.jpg'
                ],
                [
                    'nom' => 'Jean-Luc R.',
                    'note' => 5,
                    'date' => 'Avril 2024',
                    'service' => 'vente',
                    'ville' => 'Bordeaux',
                    'text' => 'Notre maison vendue en 10 jours au prix demandé ! Eduardo Desul a su mettre en valeur notre bien et trouver l\'acheteur idéal. Son approche clef en main nous a évité bien des tracas. Merci pour ce professionnalisme remarquable !',
                    'photo' => 'https://randomuser.me/api/portraits/men/32.jpg'
                ],
            ];
            foreach ($avis as $a):
            ?>
            <div class="testimonial-card" data-filter="<?= htmlspecialchars($a['service']) ?>">
                <div class="testimonial-header">
                    <img src="<?= htmlspecialchars($a['photo']) ?>" alt="<?= htmlspecialchars($a['nom']) ?>"
                         class="testimonial-avatar" loading="lazy"
                         onerror="this.src='/assets/images/avatar-placeholder.svg'">
                    <div>
                        <div class="testimonial-name"><?= htmlspecialchars($a['nom']) ?></div>
                        <div class="testimonial-meta"><?= htmlspecialchars($a['ville']) ?> · <?= htmlspecialchars($a['date']) ?></div>
                    </div>
                    <div class="testimonial-stars" style="margin-left:auto">
                        <?= str_repeat('★', (int)$a['note']) ?>
                    </div>
                </div>
                <p class="testimonial-text"><?= htmlspecialchars($a['text']) ?></p>
                <span class="testimonial-badge"><?= htmlspecialchars(ucfirst($a['service'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <div class="section-cta" style="text-align:center;margin-top:3rem" data-animate>
            <p style="margin-bottom:1.5rem;color:var(--muted)">Une expérience à partager ?</p>
            <a href="https://g.page/r/Ce2xYJzQkZJMEAI" target="_blank" rel="noopener noreferrer" class="btn btn--primary">
                Déposer mon avis Google
            </a>
        </div>

    </div>
</section>
