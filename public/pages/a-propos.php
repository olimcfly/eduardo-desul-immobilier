<?php
$pageTitle = 'À propos — Eduardo Desul Immobilier';
$metaDesc  = 'Découvrez Eduardo Desul, conseiller immobilier indépendant à Bordeaux. 15 ans d\'expérience, +200 transactions réussies.';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>À propos</span></nav>
        <h1>À propos d'Eduardo Desul</h1>
        <p>Conseiller immobilier indépendant à Bordeaux depuis plus de 15 ans.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="grid-2" style="gap:4rem;align-items:center">
            <div data-animate>
                <div style="background:var(--clr-primary);border-radius:var(--radius-xl);aspect-ratio:4/5;display:flex;align-items:center;justify-content:center;font-size:6rem;overflow:hidden;position:relative">
                    <img src="/assets/images/eduardo-portrait.jpg" alt="Eduardo Desul" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display='none'">
                    <span style="position:absolute">👤</span>
                </div>
            </div>
            <div data-animate>
                <span class="section-label">Mon histoire</span>
                <h2 class="section-title">Un conseiller qui vous ressemble</h2>
                <p>Passionné par l'immobilier depuis toujours, j'ai débuté ma carrière en 2009 au sein de grandes agences bordelaises avant de choisir l'indépendance pour mieux servir mes clients.</p>
                <p>Cette indépendance est une force : je ne représente aucune enseigne, seulement vos intérêts. Ma rémunération dépend uniquement de votre satisfaction, pas des commissions que je pourrais générer.</p>
                <p>Mon secteur de prédilection : Bordeaux et la métropole bordelaise, dont je connais chaque quartier, chaque tendance de marché.</p>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin:2rem 0;text-align:center">
                    <?php foreach ([['200+', 'Transactions'], ['4.9/5', 'Note clients'], ['15 ans', 'Expérience']] as [$val, $lab]): ?>
                    <div style="padding:1.5rem;background:var(--clr-bg);border-radius:var(--radius-lg);border:1px solid var(--clr-border)">
                        <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:700;color:var(--clr-primary)"><?= $val ?></div>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)"><?= $lab ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="/contact" class="btn btn--primary">Me contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Mes valeurs</span>
            <h2 class="section-title">Ce qui me guide au quotidien</h2>
        </div>
        <div class="grid-3" data-animate>
            <?php foreach ([
                ['🤝', 'Confiance', 'Je construis une relation durable avec chaque client. La transparence est ma règle d\'or, à chaque étape.'],
                ['🎯', 'Excellence', 'Je m\'engage à donner le meilleur pour chaque mission. Votre satisfaction est ma priorité absolue.'],
                ['📍', 'Expertise locale', 'Bordeaux est mon terrain. Je connais ses quartiers, ses dynamiques et ses opportunités comme personne.'],
            ] as [$icon, $titre, $texte]): ?>
            <div class="service-card">
                <div class="service-card__icon"><?= $icon ?></div>
                <h3 class="service-card__title"><?= $titre ?></h3>
                <p class="service-card__text"><?= e($texte) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Certifications</span>
            <h2 class="section-title">Mes habilitations professionnelles</h2>
        </div>
        <div class="grid-4 text-center" data-animate>
            <?php foreach ([
                ['📜', 'Carte professionnelle', 'Délivrée par la CCI de Bordeaux'],
                ['⚖️', 'Loi Hoguet', 'Conformité réglementaire totale'],
                ['🛡️', 'RC Professionnelle', 'Assurance garantie décennale'],
                ['🎓', 'Formation continue', '14h/an minimum respectées'],
            ] as [$icon, $titre, $desc]): ?>
            <div style="padding:1.5rem;background:var(--clr-bg);border-radius:var(--radius-lg);border:1px solid var(--clr-border)">
                <div style="font-size:2rem;margin-bottom:.75rem"><?= $icon ?></div>
                <h4 style="margin-bottom:.4rem"><?= $titre ?></h4>
                <p style="font-size:.85rem;color:var(--clr-text-muted);margin:0"><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Travaillons ensemble</h2>
        <p>Prêt à concrétiser votre projet immobilier ? Je suis à votre écoute.</p>
        <div class="cta-banner__actions">
            <a href="/contact" class="btn btn--accent btn--lg">Prendre contact</a>
            <a href="/estimation-gratuite" class="btn btn--outline-white btn--lg">Estimer mon bien</a>
        </div>
    </div>
</section>
