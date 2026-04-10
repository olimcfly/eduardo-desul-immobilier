<?php
$pageTitle = 'À propos — Eduardo De Sul, Agent immobilier & Expert en estimation immobilière';
$metaDesc  = 'Découvrez Eduardo De Sul, agent immobilier à Bordeaux & expert en estimation immobilière. Vente, achat et estimation à Bordeaux Métropole. Certifié Expert en évaluation immobilière.';

$hero = function_exists('get_page_content') ? (get_page_content('a-propos', 'hero') ?? []) : [];
$stats = function_exists('get_page_content') ? (get_page_content('a-propos', 'stats') ?? []) : [];
$cta = function_exists('get_page_content') ? (get_page_content('a-propos', 'cta') ?? []) : [];
$statsItems = $stats['stats'] ?? [
    ['value' => 'Vente', 'label' => 'Maisons & apparts.'],
    ['value' => 'Achat', 'label' => 'Accompagnement'],
    ['value' => 'Expert', 'label' => 'Évaluation certifiée'],
];
?>

<div class="page-header"<?= !empty($hero['image']) ? ' style="background-image:url(\'' . e((string) $hero['image']) . '\');background-size:cover;background-position:center"' : '' ?>>
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>À propos</span></nav>
        <h1><?= e((string)($hero['title'] ?? "À propos d'Eduardo De Sul")) ?></h1>
        <p><?= $hero['subtitle'] ?? 'Votre expert immobilier à Bordeaux' ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="grid-2" style="gap:4rem;align-items:center">
            <div data-animate>
                <div style="background:var(--clr-primary);border-radius:var(--radius-xl);aspect-ratio:4/5;display:flex;align-items:center;justify-content:center;font-size:6rem;overflow:hidden;position:relative">
                    <img src="https://nhkxpqunzawllesgatth.supabase.co/storage/v1/object/public/agent-images/1773076139097-2w3xgeid3of.jpg" alt="Eduardo De Sul, agent immobilier Bordeaux" style="width:100%;height:100%;object-fit:cover">
                </div>
            </div>
            <div data-animate>
                <span class="section-label">Mon histoire</span>
                <h2 class="section-title">Eduardo De Sul,<br>Agent immobilier & Expert en estimation</h2>
                <p>Passionné par l'immobilier et les relations humaines, j'accompagne mes clients avec engagement dans chaque étape de leur projet. Certifié <strong>Expert en évaluation immobilière</strong>, je mets mon expertise au service de vos intérêts.</p>
                <p>Mon approche : écoute, transparence et résultats concrets. Que vous soyez vendeur, acheteur ou simplement à la recherche d'une estimation fiable, je suis à vos côtés à Bordeaux Métropole.</p>
                <p>Mon secteur de prédilection : Bordeaux Métropole, dont je connais chaque quartier, chaque tendance de marché et chaque opportunité.</p>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin:2rem 0;text-align:center">
                    <?php foreach ($statsItems as $stat): ?>
                    <div style="padding:1.5rem;background:var(--clr-bg);border-radius:var(--radius-lg);border:1px solid var(--clr-border)">
                        <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--clr-primary)"><?= e((string)($stat['value'] ?? '')) ?></div>
                        <div style="font-size:.8rem;color:var(--clr-text-muted)"><?= e((string)($stat['label'] ?? '')) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:2rem">
                    <a href="tel:+33676592367" style="display:inline-flex;align-items:center;gap:.5rem;font-weight:600;color:var(--clr-primary)">
                        📞 +33 6 76 59 23 67
                    </a>
                    <a href="mailto:eduardo.desul@expfrance.fr" style="display:inline-flex;align-items:center;gap:.5rem;color:var(--clr-text-muted)">
                        ✉️ eduardo.desul@expfrance.fr
                    </a>
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
                ['📍', 'Expertise locale', 'Bordeaux Métropole est mon terrain. Je connais ses quartiers, ses dynamiques et ses opportunités.'],
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
                ['🎓', 'Expert en évaluation', 'Certification Expert immobilier'],
                ['🛡️', 'RC Professionnelle', 'Assurance garantie professionnelle'],
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
        <h2><?= e((string)($cta['title'] ?? 'Prêt à vendre ou acheter ?')) ?></h2>
        <p><?= $cta['text'] ?? 'Contactez-moi pour une estimation gratuite.' ?></p>
        <div class="cta-banner__actions">
            <a href="<?= e((string)($cta['cta_link'] ?? '/contact')) ?>" class="btn btn--accent btn--lg"><?= e((string)($cta['cta_text'] ?? 'Me contacter')) ?></a>
        </div>
    </div>
</section>
