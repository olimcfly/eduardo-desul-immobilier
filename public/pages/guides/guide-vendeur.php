<?php
require_once __DIR__ . '/../../core/bootstrap.php';

$advisor = $db->query("SELECT * FROM advisors WHERE id = 1")->fetch();

$pageTitle       = 'Guide Complet Vendeur — Vendre votre bien immobilier à Aix-en-Provence';
$pageDescription = 'Tout ce que vous devez savoir pour réussir votre vente immobilière à Aix-en-Provence et le Pays d\'Aix : estimation, préparation, mandat, négociation, signature. Guide gratuit 2025.';
$pageCanonical   = '/guide-vendeur';

ob_start();
?>

<link rel="stylesheet" href="/assets/css/guide.css">
<link rel="stylesheet" href="/assets/css/guide-vendeur.css">

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "HowTo",
    "name": "Comment vendre un bien immobilier à Aix-en-Provence",
    "description": "Guide complet pour réussir votre vente immobilière à Aix-en-Provence et dans le Pays d\'Aix en <?= date('Y') ?>",
    "totalTime": "PT25M",
    "step": [
        {"@type":"HowToStep","name":"Estimer votre bien au juste prix","position":1},
        {"@type":"HowToStep","name":"Préparer votre bien pour la vente","position":2},
        {"@type":"HowToStep","name":"Choisir votre stratégie de vente","position":3},
        {"@type":"HowToStep","name":"Organiser les visites","position":4},
        {"@type":"HowToStep","name":"Négocier et accepter une offre","position":5},
        {"@type":"HowToStep","name":"Signer et finaliser la vente","position":6}
    ]
}
</script>

<!-- Reading Progress -->
<div class="reading-progress" id="readingProgress"></div>

<div class="guide-page guide-page--vendeur">

    <!-- ══ HERO ══════════════════════════════════════════════ -->
    <section class="guide-hero guide-hero--vendeur">
        <div class="container">
            <div class="guide-hero__inner">

                <div class="guide-hero__badge guide-hero__badge--vendeur">
                    <i class="fas fa-sign"></i>
                    Guide Gratuit · Mis à jour <?= date('Y') ?>
                </div>

                <h1 class="guide-hero__title">
                    Vendre votre bien immobilier<br>
                    <span class="guide-hero__highlight">à Aix-en-Provence au meilleur prix</span>
                </h1>

                <p class="guide-hero__subtitle">
                    De l'estimation jusqu'à la remise des clés :
                    les 6 étapes pour vendre sereinement sur Aix-en-Provence
                    et le Pays d\'Aix, éviter les erreurs classiques
                    et maximiser votre prix de vente.
                </p>

                <div class="guide-hero__stats">
                    <div class="guide-stat">
                        <i class="fas fa-clock"></i>
                        <span>25 min de lecture</span>
                    </div>
                    <div class="guide-stat">
                        <i class="fas fa-list-check"></i>
                        <span>28 points de contrôle</span>
                    </div>
                    <div class="guide-stat">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Mis à jour <?= date('F Y') ?></span>
                    </div>
                    <div class="guide-stat">
                        <i class="fas fa-eye"></i>
                        <span>3 800 lecteurs ce mois</span>
                    </div>
                </div>

                <div class="guide-hero__actions">
                    <a href="#sommaire" class="btn btn--primary btn--lg">
                        <i class="fas fa-book-open"></i>
                        Lire le guide
                    </a>
                    <a href="/estimation-gratuite" class="btn btn--outline btn--lg">
                        <i class="fas fa-calculator"></i>
                        Estimer mon bien
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ══ CONTENU PRINCIPAL ══════════════════════════════════ -->
    <section class="guide-body section" id="sommaire">
        <div class="container">
            <div class="guide-layout">

                <main class="guide-main">

                    <!-- Intro -->
                    <div class="guide-intro-box guide-intro-box--vendeur">
                        <div class="guide-intro-box__icon">🏡</div>
                        <div>
                            <strong>Pourquoi ce guide existe</strong>
                            <p>
                                Vendre un bien immobilier est souvent la transaction
                                financière la plus importante de votre vie.
                                Pourtant, la plupart des vendeurs commettent
                                les mêmes erreurs : surestimer le prix, négliger
                                la présentation, choisir le mauvais moment.
                                Ce guide vous donne toutes les clés <em>avant</em>
                                de mettre votre bien sur le marché aixois.
                            </p>
                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 1 — ESTIMATION
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-1">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">1</div>
                            <div>
                                <h2 class="guide-step__title">Estimer votre bien au juste prix</h2>
                                <p class="guide-step__subtitle">
                                    L'étape la plus critique. Un bien surestimé reste
                                    sur le marché et se vend finalement moins cher.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                Sur le marché du Pays d\'Aix, le prix au m² varie
                                fortement selon le secteur : de <strong>3 500 €/m²</strong>
                                en périphérie rurale à plus de <strong>7 000 €/m²</strong>
                                en hypercentre aixois (Mazarin, Cours Mirabeau).
                                Une estimation précise est indispensable pour
                                vendre dans les meilleurs délais.
                            </p>

                            <!-- Prix par secteur -->
                            <?php
                            $secteurs = [
                                ['name' => 'Aix centre / Mazarin',       'min' => '5 500', 'max' => '7 000+', 'color' => 'red'],
                                ['name' => 'Aix périphérie (Jas, Luynes)','min' => '4 000', 'max' => '5 500',  'color' => 'orange'],
                                ['name' => 'Éguilles / Venelles',         'min' => '3 800', 'max' => '5 000',  'color' => 'yellow'],
                                ['name' => 'Le Tholonet / Célony',        'min' => '4 500', 'max' => '6 500',  'color' => 'green'],
                                ['name' => 'Gardanne / Meyreuil',         'min' => '3 200', 'max' => '4 200',  'color' => 'blue'],
                                ['name' => 'Pertuis / Jouques',           'min' => '2 800', 'max' => '3 800',  'color' => 'purple'],
                            ];
                            ?>
                            <div class="secteur-grid">
                                <h3>Prix au m² par secteur du Pays d\'Aix (2025)</h3>
                                <?php foreach ($secteurs as $s): ?>
                                <div class="secteur-card secteur-card--<?= $s['color'] ?>">
                                    <div class="secteur-card__name">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= $s['name'] ?>
                                    </div>
                                    <div class="secteur-card__price">
                                        <?= $s['min'] ?> – <?= $s['max'] ?> €/m²
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Les 3 méthodes -->
                            <h3>Les 3 méthodes d'estimation</h3>
                            <?php
                            $methods = [
                                [
                                    'icon'  => 'fa-chart-bar',
                                    'title' => 'Comparaison par les ventes récentes (DVF)',
                                    'desc'  => 'La référence : les données DVF (Demandes de Valeurs Foncières) publiées par l\'État recensent toutes les transactions des 5 dernières années. Consultables sur data.gouv.fr ou pappers.fr.',
                                    'tag'   => 'Gratuit · Fiable',
                                    'color' => 'green',
                                ],
                                [
                                    'icon'  => 'fa-user-tie',
                                    'title' => 'Estimation par un professionnel local',
                                    'desc'  => 'Un agent immobilier connaissant le Pays d\'Aix croise ventes récentes, caractéristiques intrinsèques et état du marché actuel. La méthode la plus précise et la plus rapide.',
                                    'tag'   => 'Recommandé · Gratuit',
                                    'color' => 'blue',
                                ],
                                [
                                    'icon'  => 'fa-laptop',
                                    'title' => 'Outils en ligne (estimateurs automatiques)',
                                    'desc'  => 'SeLoger, MeilleursAgents, Meilleurs Taux… donnent une fourchette indicative en quelques secondes. Utile pour un premier cadrage, mais insuffisant seul — les algorithmes ne connaissent pas votre vue sur la Sainte-Victoire.',
                                    'tag'   => 'Indicatif uniquement',
                                    'color' => 'gray',
                                ],
                            ];
                            foreach ($methods as $m): ?>
                            <div class="method-card method-card--<?= $m['color'] ?>">
                                <div class="method-card__icon">
                                    <i class="fas <?= $m['icon'] ?>"></i>
                                </div>
                                <div class="method-card__body">
                                    <div class="method-card__header">
                                        <strong><?= $m['title'] ?></strong>
                                        <span class="method-card__tag method-card__tag--<?= $m['color'] ?>">
                                            <?= $m['tag'] ?>
                                        </span>
                                    </div>
                                    <p><?= $m['desc'] ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <!-- Erreurs de pricing -->
                            <div class="warning-box">
                                <div class="warning-box__header">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Le piège de la surestimation
                                </div>
                                <p>
                                    Un bien affiché 10 % trop cher reste en moyenne
                                    <strong>3 à 4 fois plus longtemps</strong> sur le marché.
                                    Passé 60 jours sur SeLoger, les acheteurs suspectent
                                    un problème — et négocient plus agressivement.
                                    Résultat : vous vendez souvent <strong>moins cher</strong>
                                    qu'un bien bien prix dès le départ.
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 2 — PRÉPARATION
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-2">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">2</div>
                            <div>
                                <h2 class="guide-step__title">Préparer votre bien pour la vente</h2>
                                <p class="guide-step__subtitle">
                                    Home staging, diagnostics obligatoires, photos :
                                    les 30 premiers jours sont décisifs.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                En Provence, la lumière et les extérieurs sont des
                                atouts majeurs. Un bien bien présenté se vend
                                en moyenne <strong>15 à 20 % plus vite</strong>
                                et génère plus d'offres concurrentes.
                            </p>

                            <!-- Home staging checklist -->
                            <?php
                            $staging = [
                                'Intérieur' => [
                                    'Désencombrer toutes les pièces',
                                    'Dépersonnaliser (photos, objets personnels)',
                                    'Réparer les petits défauts visibles (joints, poignées, carrelage)',
                                    'Rafraîchir la peinture si nécessaire (blanc cassé, gris clair)',
                                    'Nettoyer en profondeur (vitres, cuisine, salle de bain)',
                                    'Optimiser l\'éclairage (ampoules, rideaux ouverts)',
                                ],
                                'Extérieur (atout Provence)' => [
                                    'Tondre la pelouse et tailler les haies',
                                    'Nettoyer la terrasse / piscine si présente',
                                    'Peindre ou nettoyer le portail et la façade',
                                    'Fleurir l\'entrée (lavande, olivier — ambiance provençale)',
                                    'Ranger le garage et les abris de jardin',
                                ],
                                'Administratif' => [
                                    'Rassembler les diagnostics obligatoires (DPE, amiante, plomb…)',
                                    'Préparer les 3 derniers PV d\'AG si copropriété',
                                    'Réunir les factures de travaux réalisés',
                                    'Retrouver le titre de propriété',
                                    'Vérifier la taxe foncière (montant à communiquer)',
                                ],
                            ];
                            foreach ($staging as $category => $items): ?>
                            <div class="checklist-group">
                                <div class="checklist-group__title">
                                    <i class="fas fa-check-circle"></i>
                                    <?= $category ?>
                                </div>
                                <ul class="checklist-group__items">
                                    <?php foreach ($items as $item): ?>
                                    <li>
                                        <span class="checklist-item__checkbox"></span>
                                        <?= $item ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endforeach; ?>

                            <!-- Diagnostics -->
                            <h3>Diagnostics immobiliers obligatoires</h3>
                            <?php
                            $diagnostics = [
                                ['DPE',          'Diagnostic de Performance Énergétique — obligatoire, validité 10 ans. Crucial depuis la loi Climat : un bien G ne peut plus être mis en location après 2025.'],
                                ['Amiante',      'Obligatoire si permis de construire avant juillet 1997. Validité illimitée si négatif.'],
                                ['Plomb (CREP)', 'Obligatoire si construction avant 1949. Validité 1 an si positif, illimitée si négatif.'],
                                ['Termites',     'Obligatoire en zones à risque — vérifier arrêté préfectoral des Bouches-du-Rhône (13). Validité 6 mois.'],
                                ['Gaz / Élec',   'Obligatoire si installation > 15 ans. Validité 3 ans.'],
                                ['ERP',          'État des Risques et Pollutions — inclut risques naturels, miniers, sismiques. Validité 6 mois. Zone sismique 1 à Aix.'],
                                ['Mérule',       'Obligatoire dans les zones à risque définies par arrêté préfectoral.'],
                                ['Assainissement','Non-collectif obligatoire si pas raccordé au réseau public (fréquent en campagne du Pays d\'Aix).'],
                            ];
                            foreach ($diagnostics as $d): ?>
                            <div class="diag-item">
                                <strong class="diag-item__name"><?= $d[0] ?></strong>
                                <p class="diag-item__desc"><?= $d[1] ?></p>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 3 — STRATÉGIE DE VENTE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-3">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">3</div>
                            <div>
                                <h2 class="guide-step__title">Choisir votre stratégie de vente</h2>
                                <p class="guide-step__subtitle">
                                    Seul ou avec un professionnel ? Mandat simple ou exclusif ?
                                    Les bonnes questions à se poser.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <!-- Vente seul vs agence -->
                            <div class="compare-cards">
                                <div class="compare-card compare-card--neutral">
                                    <div class="compare-card__header">
                                        <i class="fas fa-user"></i>
                                        <h4>Vente entre particuliers (PAP)</h4>
                                    </div>
                                    <ul class="compare-card__pros">
                                        <li><i class="fas fa-check"></i> Pas de frais d'agence</li>
                                        <li><i class="fas fa-check"></i> Relation directe acheteur/vendeur</li>
                                    </ul>
                                    <ul class="compare-card__cons">
                                        <li><i class="fas fa-times"></i> Estimation souvent erronée</li>
                                        <li><i class="fas fa-times"></i> Visibilité limitée</li>
                                        <li><i class="fas fa-times"></i> Gestion chronophage</li>
                                        <li><i class="fas fa-times"></i> Négociation sans filet</li>
                                        <li><i class="fas fa-times"></i> Risques juridiques</li>
                                    </ul>
                                </div>

                                <div class="compare-card compare-card--recommended">
                                    <div class="compare-card__badge">Recommandé</div>
                                    <div class="compare-card__header">
                                        <i class="fas fa-handshake"></i>
                                        <h4>Avec un expert immobilier local</h4>
                                    </div>
                                    <ul class="compare-card__pros">
                                        <li><i class="fas fa-check"></i> Estimation précise au prix du marché</li>
                                        <li><i class="fas fa-check"></i> Réseau acheteurs qualifiés Pays d\'Aix</li>
                                        <li><i class="fas fa-check"></i> Photos pro + diffusion multi-portails</li>
                                        <li><i class="fas fa-check"></i> Négociation sécurisée</li>
                                        <li><i class="fas fa-check"></i> Suivi jusqu'à la signature notaire</li>
                                    </ul>
                                    <ul class="compare-card__cons">
                                        <li><i class="fas fa-times"></i> Honoraires (3 – 6 % du prix)</li>
                                    </ul>
                                    <div class="compare-card__saving">
                                        En pratique : un bien vendu avec un pro se vend
                                        <strong>5 – 12 % plus cher</strong> qu'en PAP
                                        — les honoraires sont souvent couverts.
                                    </div>
                                </div>
                            </div>

                            <!-- Mandat simple vs exclusif -->
                            <h3>Mandat simple ou mandat exclusif ?</h3>
                            <div class="mandat-grid">
                                <div class="mandat-card">
                                    <h4>
                                        <i class="fas fa-door-open"></i>
                                        Mandat simple
                                    </h4>
                                    <p>
                                        Vous pouvez confier la vente à plusieurs agences
                                        simultanément et vendre vous-même.
                                        En théorie plus d'exposition — en pratique,
                                        les agences investissent moins sur un bien
                                        qu'elles ne sont pas sûres de vendre.
                                    </p>
                                    <div class="mandat-card__verdict mandat-card__verdict--neutral">
                                        ⚠️ Souvent contre-productif
                                    </div>
                                </div>
                                <div class="mandat-card mandat-card--recommended">
                                    <h4>
                                        <i class="fas fa-lock"></i>
                                        Mandat exclusif
                                    </h4>
                                    <p>
                                        Une seule agence, totalement mobilisée sur votre bien.
                                        Elle investit en photos pro, home staging,
                                        publicité payante et réseau acheteurs.
                                        Délai de vente moyen <strong>2× plus court</strong>
                                        qu'en mandat simple.
                                    </p>
                                    <div class="mandat-card__verdict mandat-card__verdict--good">
                                        ✅ Recommandé pour vendre vite et bien
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 4 — VISITES
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-4">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">4</div>
                            <div>
                                <h2 class="guide-step__title">Organiser et réussir les visites</h2>
                                <p class="guide-step__subtitle">
                                    Les visites se jouent en quelques minutes.
                                    Préparez chaque détail pour déclencher le coup de cœur.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                En Provence, la lumière naturelle est votre meilleur allié.
                                Planifiez les visites aux heures où votre bien est
                                le plus lumineux. Un appartement exposé sud à Aix
                                doit être visité en fin de matinée — pas en soirée.
                            </p>

                            <!-- Conseils visite -->
                            <?php
                            $visit_tips = [
                                [
                                    'icon'  => 'fa-sun',
                                    'title' => 'Lumière et aération',
                                    'desc'  => 'Ouvrez tous les volets et fenêtres 30 minutes avant. En été, aérez tôt le matin pour éviter la chaleur — atout Provence à valoriser.',
                                ],
                                [
                                    'icon'  => 'fa-thermometer-half',
                                    'title' => 'Température agréable',
                                    'desc'  => 'Climatisation réglée à 23°C en été, chauffage à 20°C en hiver. Un intérieur confortable favorise la projection de l\'acheteur.',
                                ],
                                [
                                    'icon'  => 'fa-leaf',
                                    'title' => 'Jardin / terrasse en valeur',
                                    'desc'  => 'Arrosez la veille, sortez le mobilier de jardin. Un extérieur soigné sur le Pays d\'Aix peut faire la différence à lui seul.',
                                ],
                                [
                                    'icon'  => 'fa-comments',
                                    'title' => 'Laisser l\'acheteur s\'exprimer',
                                    'desc'  => 'Ne monopolisez pas la parole. Laissez l\'acheteur s\'imaginer dans les lieux. Répondez aux questions sans survendre.',
                                ],
                                [
                                    'icon'  => 'fa-file-alt',
                                    'title' => 'Dossier vendeur prêt',
                                    'desc'  => 'Diagnostics, charges, taxe foncière, factures d\'énergie : avoir les documents sous la main rassure et accélère la décision.',
                                ],
                                [
                                    'icon'  => 'fa-camera',
                                    'title' => 'Photos et visite virtuelle',
                                    'desc'  => 'Des photos professionnelles réalisées sous le soleil provençal génèrent 3× plus de demandes de visite. La visite virtuelle 360° filtre les acheteurs peu motivés.',
                                ],
                            ];
                            foreach ($visit_tips as $tip): ?>
                            <div class="visit-tip">
                                <div class="visit-tip__icon">
                                    <i class="fas <?= $tip['icon'] ?>"></i>
                                </div>
                                <div class="visit-tip__content">
                                    <strong><?= $tip['title'] ?></strong>
                                    <p><?= $tip['desc'] ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 5 — NÉGOCIATION
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-5">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">5</div>
                            <div>
                                <h2 class="guide-step__title">Négocier et accepter une offre</h2>
                                <p class="guide-step__subtitle">
                                    Comment répondre à une offre basse,
                                    choisir entre plusieurs offres et sécuriser la vente.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                Sur le marché aixois, si votre bien est correctement
                                estimé et bien présenté, vous recevrez souvent
                                plusieurs offres dans les 2 à 4 premières semaines.
                                Voici comment les analyser.
                            </p>

                            <!-- Grille de réponse -->
                            <div class="nego-grid">
                                <div class="nego-card nego-card--low">
                                    <div class="nego-card__pct">Offre au prix</div>
                                    <div class="nego-card__label">À accepter rapidement</div>
                                    <p>Si le dossier financier est solide et les conditions suspensives raisonnables, ne tardez pas — un acheteur motivé peut se retourner.</p>
                                </div>
                                <div class="nego-card nego-card--mid">
                                    <div class="nego-card__pct">– 1 à 3 %</div>
                                    <div class="nego-card__label">Négociation classique</div>
                                    <p>Contre-proposition possible. Proposez un point d'atterrissage à – 1 % ou incluez des éléments (mobilier, date de libération).</p>
                                </div>
                                <div class="nego-card nego-card--high">
                                    <div class="nego-card__pct">– 5 à 8 %</div>
                                    <div class="nego-card__label">Offre à travailler</div>
                                    <p>Demandez les motivations. Si l'acheteur est sérieux, une contre-proposition à – 2 % peut débloquer la situation.</p>
                                </div>
                                <div class="nego-card nego-card--max">
                                    <div class="nego-card__pct">> – 10 %</div>
                                    <div class="nego-card__label">Offre à refuser ou ignorer</div>
                                    <p>Soit le bien est surestimé (réévaluez), soit l'acheteur n'est pas sérieux. Ne bradez pas — le marché aixois reste solide.</p>
                                </div>
                            </div>

                            <!-- Choisir entre plusieurs offres -->
                            <h3>Comment choisir entre plusieurs offres ?</h3>
                            <?php
                            $criteria_offre = [
                                ['Prix net vendeur',       'L\'essentiel — mais pas le seul critère.'],
                                ['Solidité du dossier',    'CDI, apport élevé, accord de principe bancaire = risque de caducité faible.'],
                                ['Conditions suspensives', 'Moins il y en a, plus la vente est sécurisée. Méfiez-vous des conditions exotiques.'],
                                ['Délai de réitération',   'Plus c\'est court, moins vous êtes exposé. 60 jours est raisonnable.'],
                                ['Date de libération',     'Si vous devez rester dans le bien, négociez une date de libération différée.'],
                            ];
                            foreach ($criteria_offre as $c): ?>
                            <div class="offer-row">
                                <div class="offer-row__icon">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                <div class="offer-row__content">
                                    <strong><?= $c[0] ?></strong>
                                    <span><?= $c[1] ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 6 — SIGNATURE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-6">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">6</div>
                            <div>
                                <h2 class="guide-step__title">Signer et finaliser la vente</h2>
                                <p class="guide-step__subtitle">
                                    Compromis, conditions suspensives, acte authentique :
                                    les étapes finales côté vendeur.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <!-- Timeline -->
                            <div class="timeline">

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--vendeur">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J0</div>
                                        <h4>Offre acceptée</h4>
                                        <p>
                                            Confirmation écrite de votre accord.
                                            Choisissez le notaire (le vôtre ou celui de l'acheteur
                                            — les frais sont identiques, les deux peuvent
                                            instrumenter l'acte).
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--vendeur">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+15 à J+30</div>
                                        <h4>Signature du compromis de vente</h4>
                                        <p>
                                            Versement du dépôt de garantie par l'acheteur
                                            (5 à 10 % du prix). Début du délai de rétractation
                                            de 10 jours pour l'acheteur.
                                        </p>
                                        <div class="timeline-item__tag">
                                            <i class="fas fa-info-circle"></i>
                                            Vous n'avez pas de délai de rétractation en tant que vendeur
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--vendeur">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+30 à J+75</div>
                                        <h4>Période d'obtention du prêt</h4>
                                        <p>
                                            L'acheteur dispose de 45 jours pour obtenir
                                            son financement. Restez disponible pour les
                                            éventuelles demandes du notaire ou de la banque.
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--vendeur">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+60 à J+90</div>
                                        <h4>Acte authentique chez le notaire</h4>
                                        <p>
                                            Signature définitive, remise des clés,
                                            virement du prix de vente sous 48 à 72h.
                                            Votre vente est finalisée !
                                        </p>
                                    </div>
                                </div>

                            </div>

                            <!-- Plus-value -->
                            <div class="info-box">
                                <div class="info-box__header">
                                    <i class="fas fa-piggy-bank"></i>
                                    Fiscalité : la plus-value immobilière
                                </div>
                                <p>
                                    Si le bien vendu est votre <strong>résidence principale</strong>,
                                    la plus-value est <strong>totalement exonérée</strong> d'impôt.
                                    Pour une résidence secondaire ou un investissement locatif,
                                    des abattements progressifs s'appliquent à partir de 6 ans
                                    de détention. Pascal Hamm peut vous orienter vers un notaire
                                    partenaire pour simuler votre situation fiscale.
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         CHECKLIST COMPLÈTE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="checklist">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--vendeur">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <h2 class="guide-step__title">Checklist complète vendeur</h2>
                                <p class="guide-step__subtitle">
                                    28 points de contrôle pour ne rien oublier.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">
                            <?php
                            $checklist = [
                                'Estimation & Préparation' => [
                                    'v01' => 'Estimation réalisée par un professionnel local',
                                    'v02' => 'Données DVF consultées pour validation',
                                    'v03' => 'Prix affiché cohérent avec le marché actuel',
                                    'v04' => 'Diagnostics obligatoires commandés',
                                    'v05' => 'DPE réalisé et compris',
                                ],
                                'Home Staging' => [
                                    'v06' => 'Bien désencombré et dépersonnalisé',
                                    'v07' => 'Petites réparations effectuées',
                                    'v08' => 'Nettoyage complet réalisé',
                                    'v09' => 'Extérieur (jardin, terrasse) soigné',
                                    'v10' => 'Photos professionnelles réalisées',
                                    'v11' => 'Visite virtuelle 360° créée (si possible)',
                                ],
                                'Stratégie & Diffusion' => [
                                    'v12' => 'Type de mandat choisi (simple / exclusif)',
                                    'v13' => 'Bien diffusé sur les principaux portails',
                                    'v14' => 'Annonce rédigée avec les atouts Provence mis en avant',
                                    'v15' => 'Plan du bien disponible',
                                ],
                                'Visites' => [
                                    'v16' => 'Horaires de visite calés sur la luminosité du bien',
                                    'v17' => 'Dossier vendeur préparé (diagnostics, charges, taxe foncière)',
                                    'v18' => 'Mobilier de jardin sorti pour les visites',
                                    'v19' => 'Bien aéré et à température agréable',
                                ],
                                'Offre & Négociation' => [
                                    'v20' => 'Offre reçue par écrit',
                                    'v21' => 'Dossier financier acheteur vérifié',
                                    'v22' => 'Conditions suspensives analysées',
                                    'v23' => 'Contre-proposition formulée si nécessaire',
                                ],
                                'Finalisation' => [
                                    'v24' => 'Notaire choisi et compromis signé',
                                    'v25' => 'Dépôt de garantie reçu',
                                    'v26' => 'Pièces notariales transmises (titre, diagnostics, etc.)',
                                    'v27' => 'Plus-value simulée avec le notaire',
                                    'v28' => 'Acte authentique signé — virement reçu',
                                ],
                            ];
                            foreach ($checklist as $section => $items): ?>
                            <div class="checklist-section">
                                <h4 class="checklist-section__title"><?= $section ?></h4>
                                <?php foreach ($items as $key => $label): ?>
                                <label class="checklist-item" for="<?= $key ?>">
                                    <input type="checkbox" id="<?= $key ?>" class="checklist-item__input">
                                    <span class="checklist-item__box"></span>
                                    <span class="checklist-item__label"><?= $label ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         CTA FINAL
                    ───────────────────────────────────────── -->
                    <section class="guide-cta-final">
                        <div class="guide-cta-final__inner">
                            <div class="guide-cta-final__icon">
                                <i class="fas fa-sign"></i>
                            </div>
                            <h2>Prêt à vendre sur le Pays d\'Aix ?</h2>
                            <p>
                                Pascal Hamm vous accompagne de A à Z dans votre
                                projet de vente à Aix-en-Provence et ses environs.
                                Estimation gratuite, stratégie de vente, diffusion,
                                négociation et signature : un seul interlocuteur pour tout.
                            </p>
                            <div class="guide-cta-final__actions">
                                <a href="/estimation-gratuite" class="btn btn--primary btn--lg">
                                    <i class="fas fa-calculator"></i>
                                    Estimer mon bien gratuitement
                                </a>
                                <a href="/contact" class="btn btn--outline btn--lg">
                                    <i class="fas fa-comments"></i>
                                    Parler à Pascal Hamm
                                </a>
                            </div>
                            <p class="guide-cta-final__reassurance">
                                <i class="fas fa-lock"></i>
                                Sans engagement · Réponse sous 24h · 100% gratuit · Expert local Aix-en-Provence
                            </p>
                        </div>
                    </section>

                </main>

                <!-- ── SIDEBAR ─────────────────────────────── -->
                <aside class="guide-sidebar" id="guideSidebar">

                    <!-- Progress lecture -->
                    <div class="sidebar-card sidebar-card--progress">
                        <div class="sidebar-progress__header">
                            <i class="fas fa-book-open"></i>
                            <span>Votre progression</span>
                        </div>
                        <div class="sidebar-progress__bar">
                            <div class="sidebar-progress__fill" id="sidebarProgress"></div>
                        </div>
                        <div class="sidebar-progress__steps" id="sidebarSteps">
                            <?php
                            $sidebar_steps = [
                                1 => 'Estimation',
                                2 => 'Préparation',
                                3 => 'Stratégie',
                                4 => 'Visites',
                                5 => 'Négociation',
                                6 => 'Signature',
                            ];
                            foreach ($sidebar_steps as $num => $label): ?>
                            <a href="#etape-<?= $num ?>"
                               class="progress-step"
                               data-step="<?= $num ?>">
                                <div class="progress-step__dot"></div>
                                <span><?= $label ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Estimation rapide -->
                    <div class="sidebar-card sidebar-card--cta">
                        <div class="sidebar-cta__icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h4>Estimation gratuite</h4>
                        <p>Obtenez une estimation précise de votre bien à Aix-en-Provence en 48h.</p>
                        <a href="/estimation-gratuite" class="btn btn--primary btn--block">
                            <i class="fas fa-arrow-right"></i>
                            Estimer mon bien
                        </a>
                    </div>

                    <!-- Contact -->
                    <div class="sidebar-card sidebar-card--contact">
                        <?php if ($advisor): ?>
                        <div class="sidebar-advisor">
                            <img
                                src="<?= htmlspecialchars($advisor['photo']) ?>"
                                alt="<?= htmlspecialchars($advisor['name']) ?>"
                                class="sidebar-advisor__photo"
                                loading="lazy">
                            <div class="sidebar-advisor__info">
                                <strong><?= htmlspecialchars($advisor['name']) ?></strong>
                                <span><?= htmlspecialchars($advisor['title']) ?></span>
                            </div>
                        </div>
                        <p class="sidebar-advisor__quote">
                            "<?= htmlspecialchars($advisor['quote'] ?? 'Je vous accompagne pour vendre votre bien au meilleur prix sur Aix et le Pays d\'Aix.') ?>"
                        </p>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $advisor['phone'])) ?>"
                           class="btn btn--outline btn--block">
                            <i class="fas fa-phone"></i>
                            <?= htmlspecialchars($advisor['phone']) ?>
                        </a>
                        <a href="/contact" class="btn btn--ghost btn--block">
                            <i class="fas fa-envelope"></i>
                            Envoyer un message
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Témoignage -->
                    <div class="sidebar-card sidebar-card--testimonial">
                        <div class="sidebar-testimonial__stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <blockquote class="sidebar-testimonial__text">
                            "Pascal a vendu notre appartement en hypercentre d\'Aix
                            en 18 jours, au prix demandé. Un accompagnement
                            professionnel du début à la fin."
                        </blockquote>
                        <div class="sidebar-testimonial__author">
                            <div class="sidebar-testimonial__avatar">J</div>
                            <div>
                                <strong>Jean-Marc V.</strong>
                                <span>Vendeur — Aix-en-Provence centre</span>
                            </div>
                        </div>
                    </div>

                    <!-- Share -->
                    <div class="sidebar-card sidebar-card--share">
                        <p>Ce guide vous a été utile ?</p>
                        <button class="btn btn--outline btn--block" id="shareBtn">
                            <i class="fas fa-share-alt"></i>
                            Partager le guide
                        </button>
                    </div>

                </aside>

            </div>
        </div>
    </section>

</div>

<script src="/assets/js/guide-vendeur.js" defer></script>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../templates/layout.php';
?>
