<?php
require_once __DIR__ . '/../../core/bootstrap.php';

$advisor = $db->query("SELECT * FROM advisors WHERE id = 1")->fetch();

$pageTitle       = 'Guide Complet Acheteur — Acheter votre bien immobilier à Aix-en-Provence';
$pageDescription = 'Tout ce que vous devez savoir pour réussir votre achat immobilier à Aix-en-Provence et le Pays d\'Aix : budget, recherche, offre, financement, signature. Guide gratuit 2025.';
$pageCanonical   = '/guide-acheteur';

ob_start();
?>

<link rel="stylesheet" href="/assets/css/guide.css">
<link rel="stylesheet" href="/assets/css/guide-acheteur.css">

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "HowTo",
    "name": "Comment acheter un bien immobilier à Aix-en-Provence",
    "description": "Guide complet pour réussir votre achat immobilier à Aix-en-Provence et dans le Pays d\'Aix en <?= date('Y') ?>",
    "totalTime": "PT25M",
    "step": [
        {"@type":"HowToStep","name":"Définir votre budget","position":1},
        {"@type":"HowToStep","name":"Définir votre projet","position":2},
        {"@type":"HowToStep","name":"Organiser votre recherche","position":3},
        {"@type":"HowToStep","name":"Faire une offre","position":4},
        {"@type":"HowToStep","name":"Sécuriser le financement","position":5},
        {"@type":"HowToStep","name":"Signer et recevoir les clés","position":6}
    ]
}
</script>

<!-- Reading Progress -->
<div class="reading-progress" id="readingProgress"></div>

<div class="guide-page guide-page--acheteur">

    <!-- ══ HERO ══════════════════════════════════════════════ -->
    <section class="guide-hero guide-hero--acheteur">
        <div class="container">
            <div class="guide-hero__inner">

                <div class="guide-hero__badge guide-hero__badge--acheteur">
                    <i class="fas fa-key"></i>
                    Guide Gratuit · Mis à jour <?= date('Y') ?>
                </div>

                <h1 class="guide-hero__title">
                    Acheter votre bien immobilier<br>
                    <span class="guide-hero__highlight">à Aix-en-Provence en toute sérénité</span>
                </h1>

                <p class="guide-hero__subtitle">
                    De la définition de votre budget jusqu'à la remise des clés :
                    les 6 étapes pour réussir votre achat immobilier sur Aix-en-Provence
                    et le Pays d\'Aix, éviter les pièges et acheter au bon prix.
                </p>

                <div class="guide-hero__stats">
                    <div class="guide-stat">
                        <i class="fas fa-clock"></i>
                        <span>25 min de lecture</span>
                    </div>
                    <div class="guide-stat">
                        <i class="fas fa-list-check"></i>
                        <span>32 points de contrôle</span>
                    </div>
                    <div class="guide-stat">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Mis à jour <?= date('F Y') ?></span>
                    </div>
                    <div class="guide-stat">
                        <i class="fas fa-eye"></i>
                        <span>4 200 lecteurs ce mois</span>
                    </div>
                </div>

                <div class="guide-hero__actions">
                    <a href="#sommaire" class="btn btn--primary btn--lg">
                        <i class="fas fa-book-open"></i>
                        Lire le guide
                    </a>
                    <a href="/contact" class="btn btn--outline btn--lg">
                        <i class="fas fa-comments"></i>
                        Parler à Pascal Hamm
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

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 1 — BUDGET
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-1">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">1</div>
                            <div>
                                <h2 class="guide-step__title">Définir votre budget réel</h2>
                                <p class="guide-step__subtitle">
                                    Avant de visiter quoi que ce soit, connaissez précisément
                                    ce que vous pouvez dépenser — et ce que ça vous coûtera vraiment.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                L'erreur la plus fréquente des primo-accédants est de
                                visiter des biens avant d'avoir validé leur financement.
                                Résultat : coups de cœur impossibles, déceptions, et parfois
                                offres refusées faute de dossier solide.
                                Sur le marché d\'Aix-en-Provence, très tendu, un dossier
                                bancaire validé est souvent décisif.
                            </p>

                            <!-- Budget breakdown -->
                            <div class="budget-breakdown">
                                <div class="budget-row">
                                    <span class="budget-row__label">
                                        <i class="fas fa-home"></i>
                                        Prix d'achat net vendeur
                                    </span>
                                    <span class="budget-row__value">100 %</span>
                                </div>
                                <div class="budget-row budget-row--add">
                                    <span class="budget-row__label">
                                        <i class="fas fa-plus"></i>
                                        Frais de notaire
                                    </span>
                                    <span class="budget-row__value budget-row__value--add">
                                        7 – 8 % (ancien) / 2 – 3 % (neuf)
                                    </span>
                                </div>
                                <div class="budget-row budget-row--add">
                                    <span class="budget-row__label">
                                        <i class="fas fa-plus"></i>
                                        Frais d'agence
                                    </span>
                                    <span class="budget-row__value budget-row__value--add">
                                        3 – 5 % (si à charge acheteur)
                                    </span>
                                </div>
                                <div class="budget-row budget-row--add">
                                    <span class="budget-row__label">
                                        <i class="fas fa-plus"></i>
                                        Travaux éventuels
                                    </span>
                                    <span class="budget-row__value budget-row__value--add">
                                        variable
                                    </span>
                                </div>
                                <div class="budget-row budget-row--total">
                                    <span class="budget-row__label">
                                        <i class="fas fa-equals"></i>
                                        <strong>Budget total réel</strong>
                                    </span>
                                    <span class="budget-row__value budget-row__value--total">
                                        Prix × 1,12 à 1,18
                                    </span>
                                </div>
                            </div>

                            <!-- Formule -->
                            <div class="formula-box">
                                <div class="formula-box__header">
                                    <i class="fas fa-function"></i>
                                    Calcul rapide de votre mensualité maximale
                                </div>
                                <div class="formula-box__content">
                                    <div class="formula">
                                        <span class="formula__label">Revenus nets mensuels</span>
                                        <span class="formula__op">×</span>
                                        <span class="formula__num">35 %</span>
                                        <span class="formula__op">=</span>
                                        <span class="formula__result">Mensualité maximale</span>
                                    </div>
                                    <div class="formula__example">
                                        Ex : 4 000 € nets × 35 % = <strong>1 400 €/mois</strong>
                                        → emprunt ~250 000 € sur 20 ans à 3,5 %
                                    </div>
                                </div>
                            </div>

                            <h3>L'apport personnel</h3>
                            <p>
                                Les banques exigent en général un apport minimum de
                                <strong>10 % du prix d'achat</strong> pour couvrir les frais de notaire.
                                Sur Aix-en-Provence où le prix moyen au m² dépasse <strong>4 500 €</strong>,
                                un apport solide renforce considérablement votre dossier face à la concurrence.
                            </p>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 2 — PROJET
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-2">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">2</div>
                            <div>
                                <h2 class="guide-step__title">Définir votre projet</h2>
                                <p class="guide-step__subtitle">
                                    Résidence principale, investissement locatif ou pied-à-terre
                                    en Provence ? Chaque objectif a ses critères.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                Le Pays d\'Aix offre une grande diversité de biens :
                                appartements en centre-ville, bastides en périphérie,
                                maisons de village à Éguilles ou Venelles, programmes neufs
                                à Luynes ou Les Milles. Définir précisément votre projet
                                vous évitera de vous éparpiller.
                            </p>

                            <!-- Critères -->
                            <div class="criteria-grid">
                                <div class="criteria-card criteria-card--must">
                                    <div class="criteria-card__header">
                                        <i class="fas fa-check-double"></i>
                                        <h4>Critères indispensables</h4>
                                        <span class="criteria-badge criteria-badge--must">Obligatoire</span>
                                    </div>
                                    <ul>
                                        <li>Surface minimale (m²)</li>
                                        <li>Nombre de pièces / chambres</li>
                                        <li>Budget maximum</li>
                                        <li>Secteur géographique (Aix centre, Jas de Bouffan, campagne…)</li>
                                        <li>Type de bien (appartement, maison, bastide)</li>
                                    </ul>
                                </div>
                                <div class="criteria-card criteria-card--want">
                                    <div class="criteria-card__header">
                                        <i class="fas fa-star"></i>
                                        <h4>Critères importants</h4>
                                        <span class="criteria-badge criteria-badge--want">Important</span>
                                    </div>
                                    <ul>
                                        <li>Parking / garage (indispensable centre Aix)</li>
                                        <li>Extérieur (balcon, terrasse, jardin, piscine)</li>
                                        <li>Étage (pas RDC)</li>
                                        <li>Double exposition / luminosité</li>
                                        <li>Proximité transports / tramway</li>
                                    </ul>
                                </div>
                                <div class="criteria-card criteria-card--bonus">
                                    <div class="criteria-card__header">
                                        <i class="fas fa-plus-circle"></i>
                                        <h4>Bonus appréciés</h4>
                                        <span class="criteria-badge criteria-badge--bonus">Agréable</span>
                                    </div>
                                    <ul>
                                        <li>Cave / cellier</li>
                                        <li>Gardien / résidence sécurisée</li>
                                        <li>Parquet / matériaux anciens</li>
                                        <li>Hauteur sous plafond</li>
                                        <li>Vue Sainte-Victoire ou dégagée</li>
                                    </ul>
                                </div>
                            </div>

                            <h3>Neuf ou Ancien ?</h3>
                            <div class="compare-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Ancien</th>
                                            <th>Neuf</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Frais de notaire</td>
                                            <td>7 – 8 %</td>
                                            <td>2 – 3 %</td>
                                        </tr>
                                        <tr>
                                            <td>Disponibilité</td>
                                            <td>Immédiate</td>
                                            <td>12 – 24 mois</td>
                                        </tr>
                                        <tr>
                                            <td>DPE</td>
                                            <td>Variable</td>
                                            <td>A ou B garanti</td>
                                        </tr>
                                        <tr>
                                            <td>Charme / cachet</td>
                                            <td>Souvent fort</td>
                                            <td>Moderne</td>
                                        </tr>
                                        <tr>
                                            <td>Prix au m² Aix</td>
                                            <td>4 000 – 6 500 €</td>
                                            <td>5 000 – 7 500 €</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 3 — RECHERCHE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-3">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">3</div>
                            <div>
                                <h2 class="guide-step__title">Organiser votre recherche</h2>
                                <p class="guide-step__subtitle">
                                    Méthode, sources et checklist de visite pour ne rien laisser au hasard
                                    sur le marché du Pays d\'Aix.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <!-- Sources -->
                            <?php
                            $sources = [
                                ['icon'=>'fa-building-user', 'name'=>'Agences locales Pays d\'Aix',   'pct'=>'45%',
                                 'tip'=>'Relation directe, biens off-market sur Aix, Le Tholonet, Éguilles, Venelles…', 'color'=>'blue'],
                                ['icon'=>'fa-globe',         'name'=>'Portails (SeLoger, BienIci…)',  'pct'=>'35%',
                                 'tip'=>'Large choix, alertes email — filtrer sur 13100 et communes limitrophes',       'color'=>'green'],
                                ['icon'=>'fa-people-group',  'name'=>'Réseau personnel',              'pct'=>'12%',
                                 'tip'=>'Bouche à oreille — souvent les meilleures affaires en Provence',              'color'=>'purple'],
                                ['icon'=>'fa-gavel',         'name'=>'Notaires / ventes judiciaires', 'pct'=>'5%',
                                 'tip'=>'Successions, licitations — opportunités sur bastides et corps de ferme',      'color'=>'orange'],
                                ['icon'=>'fa-newspaper',     'name'=>'PAP / particuliers',            'pct'=>'3%',
                                 'tip'=>'Sans frais agence, mais plus de risques juridiques',                          'color'=>'gray'],
                            ];
                            foreach ($sources as $s): ?>
                            <div class="source-item source-item--<?= $s['color'] ?>">
                                <div class="source-item__icon">
                                    <i class="fas <?= $s['icon'] ?>"></i>
                                </div>
                                <div class="source-item__body">
                                    <strong><?= $s['name'] ?></strong>
                                    <span class="source-item__pct"><?= $s['pct'] ?></span>
                                    <p><?= $s['tip'] ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <!-- Checklist visite -->
                            <h3>Checklist de visite</h3>
                            <div class="visit-checklist">
                                <?php
                                $visit_items = [
                                    'Structure' => [
                                        'Fissures (murs, plafond, façade) ?',
                                        'Traces d\'humidité ou moisissures ?',
                                        'État de la toiture (maison / bastide) ?',
                                        'Présence d\'amiante ou plomb (diagnostics obligatoires) ?',
                                    ],
                                    'Technique' => [
                                        'Âge et état de la chaudière / chauffe-eau',
                                        'Tableau électrique aux normes ?',
                                        'DPE réel vs affiché (important en Provence : climatisation)',
                                        'Débit internet (fibre disponible ?)',
                                        'Pression et qualité de l\'eau',
                                    ],
                                    'Environnement' => [
                                        'Bruit (avenue, voisins, terrasses, route D9/A8) ?',
                                        'Ensoleillement aux heures clés (exposition sud appréciée en Provence)',
                                        'Vis-à-vis et intimité (mitoyenneté, résidence fermée ?)',
                                        'Stationnement disponible (centre Aix — zone bleue / parking résidentiel)',
                                        'Proximité écoles, tramway, Cours Mirabeau, commerces',
                                        'Proximité Montagne Sainte-Victoire ou espaces naturels (valeur +)',
                                    ],
                                    'Copropriété' => [
                                        'Montant des charges mensuelles',
                                        'Travaux votés ou à prévoir (ravalement, toiture…)',
                                        'Procédures judiciaires en cours ?',
                                        'Nombre de lots / état général des parties communes',
                                    ],
                                ];
                                foreach ($visit_items as $cat => $items): ?>
                                <div class="checklist-group">
                                    <h4 class="checklist-group__title"><?= $cat ?></h4>
                                    <ul class="checklist-group__list">
                                        <?php foreach ($items as $item): ?>
                                        <li>
                                            <span class="checklist-checkbox"></span>
                                            <?= htmlspecialchars($item) ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 4 — OFFRE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-4">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">4</div>
                            <div>
                                <h2 class="guide-step__title">Faire une offre</h2>
                                <p class="guide-step__subtitle">
                                    Comment négocier intelligemment sur le marché aixois
                                    sans perdre le bien convoité.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                Le marché du Pays d\'Aix est structurellement tendu.
                                Certains secteurs (Mazarin, Cours Mirabeau, Célony)
                                se vendent au prix affiché voire au-dessus.
                                D'autres communes limitrophes offrent plus de marge.
                            </p>

                            <!-- Grille de négociation -->
                            <div class="nego-grid">
                                <div class="nego-card nego-card--low">
                                    <div class="nego-card__pct">0 – 2 %</div>
                                    <div class="nego-card__label">Marché tendu</div>
                                    <p>Bien rare, Aix centre / Mazarin / vue Sainte-Victoire. Offrir au prix ou au-dessus si concurrence.</p>
                                </div>
                                <div class="nego-card nego-card--mid">
                                    <div class="nego-card__pct">3 – 5 %</div>
                                    <div class="nego-card__label">Marché équilibré</div>
                                    <p>Marge standard sur périphérie Aix, Éguilles, Venelles — justifiée par travaux ou comparaison de marché.</p>
                                </div>
                                <div class="nego-card nego-card--high">
                                    <div class="nego-card__pct">6 – 10 %</div>
                                    <div class="nego-card__label">Bien sur-estimé</div>
                                    <p>Délai de vente > 3 mois, travaux importants, DPE dégradé. Appuyez sur les faits et références DVF.</p>
                                </div>
                                <div class="nego-card nego-card--max">
                                    <div class="nego-card__pct">> 10 %</div>
                                    <div class="nego-card__label">Situation particulière</div>
                                    <p>Succession urgente, mutation, problème de copropriété. Rare mais possible en communes rurales du Pays d\'Aix.</p>
                                </div>
                            </div>

                            <!-- Contenu d'une offre -->
                            <h3>Que doit contenir votre offre écrite ?</h3>
                            <?php
                            $offer_rows = [
                                ['Prix proposé',             'Chiffre précis, pas une fourchette'],
                                ['Condition suspensive',     'Obtention du prêt (durée, taux max, montant)'],
                                ['Délai de validité',        '5 à 7 jours maximum'],
                                ['Date de signature',        'Compromis sous 30 jours, acte sous 90 jours'],
                                ['Éléments inclus',          'Cuisine équipée, volets électriques, piscine…'],
                                ['Identité complète',        'Nom, adresse, coordonnées acheteur'],
                            ];
                            foreach ($offer_rows as $o): ?>
                            <div class="offer-row">
                                <div class="offer-row__icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="offer-row__content">
                                    <strong><?= $o[0] ?></strong>
                                    <span><?= $o[1] ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 5 — FINANCEMENT
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-5">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">5</div>
                            <div>
                                <h2 class="guide-step__title">Sécuriser le financement</h2>
                                <p class="guide-step__subtitle">
                                    Banques, courtiers, assurance emprunteur :
                                    les leviers pour optimiser votre crédit.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <h3>L'assurance emprunteur — le levier caché</h3>
                            <p>
                                L'assurance emprunteur représente en moyenne
                                <strong>25 à 35 % du coût total du crédit</strong>.
                                Depuis la loi Lemoine (2022), vous pouvez la changer
                                à tout moment sans frais. C'est souvent là que
                                se font les plus grandes économies.
                            </p>

                            <div class="stat-row">
                                <div class="stat-box">
                                    <div class="stat-box__number">30%</div>
                                    <div class="stat-box__label">Du coût total représenté par l'assurance</div>
                                </div>
                                <div class="stat-box stat-box--primary">
                                    <div class="stat-box__number">15k€</div>
                                    <div class="stat-box__label">Économie moyenne en changeant d'assurance</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-box__number">0</div>
                                    <div class="stat-box__label">Frais pour changer depuis loi Lemoine 2022</div>
                                </div>
                            </div>

                            <h3>Aides disponibles en 2025</h3>
                            <?php
                            $aides = [
                                ['PTZ',          'Prêt à Taux Zéro — primo-accédants, sous conditions de revenus. Disponible sur certaines communes du Pays d\'Aix (zone B1).'],
                                ['Action Logement', 'Prêt à 1 % employeur si votre entreprise cotise. Cumulable avec le PTZ.'],
                                ['Éco-PTZ',      'Jusqu\'à 50 000 € pour travaux de rénovation énergétique (toiture, isolation, pompe à chaleur). Très pertinent sur l\'ancien aixois.'],
                                ['PACA Région',  'Aides régionales PACA pour primo-accédants et travaux. Se renseigner auprès de la Région Sud.'],
                            ];
                            foreach ($aides as $a): ?>
                            <div class="aide-item">
                                <strong class="aide-item__name"><?= $a[0] ?></strong>
                                <p class="aide-item__desc"><?= $a[1] ?></p>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 6 — SIGNATURE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-6">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">6</div>
                            <div>
                                <h2 class="guide-step__title">Signer et recevoir les clés</h2>
                                <p class="guide-step__subtitle">
                                    Compromis, délai de rétractation, acte authentique :
                                    les étapes finales de votre achat.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <!-- Timeline -->
                            <div class="timeline">

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-file-signature"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J0</div>
                                        <h4>Offre acceptée par le vendeur</h4>
                                        <p>
                                            Accord oral + confirmation écrite.
                                            Le bien n'est pas encore réservé —
                                            agissez vite pour le compromis.
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+15 à J+30</div>
                                        <h4>Signature du compromis de vente</h4>
                                        <p>
                                            Versement du dépôt de garantie (5 à 10 %).
                                            Début du délai de rétractation de 10 jours.
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+30 à J+60</div>
                                        <h4>Obtention du prêt</h4>
                                        <p>
                                            La banque dispose de 30 à 45 jours pour
                                            émettre l'offre de prêt. Délai légal de
                                            réflexion de 10 jours avant acceptation.
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+60 à J+90</div>
                                        <h4>Acte authentique chez le notaire</h4>
                                        <p>
                                            Signature définitive, paiement du solde
                                            et remise des clés.
                                            Bienvenue dans votre nouveau bien
                                            à Aix-en-Provence !
                                        </p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         CTA FINAL
                    ───────────────────────────────────────── -->
                    <section class="guide-cta-final">
                        <div class="guide-cta-final__inner">
                            <div class="guide-cta-final__icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <h2>Prêt à acheter sur le Pays d\'Aix ?</h2>
                            <p>
                                Pascal Hamm vous accompagne de A à Z dans votre
                                projet immobilier à Aix-en-Provence et ses environs.
                                Estimation, recherche, négociation, financement :
                                un seul interlocuteur pour tout.
                            </p>
                            <div class="guide-cta-final__actions">
                                <a href="/contact" class="btn btn--primary btn--lg">
                                    <i class="fas fa-comments"></i>
                                    Parler à Pascal Hamm
                                </a>
                                <a href="/biens" class="btn btn--outline btn--lg">
                                    <i class="fas fa-search"></i>
                                    Voir les biens disponibles à Aix et Pays d\'Aix
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
                                1 => 'Budget',
                                2 => 'Projet',
                                3 => 'Recherche',
                                4 => 'Offre',
                                5 => 'Financement',
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
                            "<?= htmlspecialchars($advisor['quote'] ?? 'Je vous accompagne dans votre projet d\'achat sur Aix et le Pays d\'Aix, de A à Z.') ?>"
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
                            "Grâce à ce guide et à l'accompagnement de Pascal,
                            j'ai trouvé mon appartement en centre d\'Aix en 3 semaines
                            et j'ai négocié 15 000 € sous le prix affiché."
                        </blockquote>
                        <div class="sidebar-testimonial__author">
                            <div class="sidebar-testimonial__avatar">S</div>
                            <div>
                                <strong>Sophie R.</strong>
                                <span>Acheteuse — Aix-en-Provence</span>
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

<script src="/assets/js/guide-acheteur.js" defer></script>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../templates/layout.php';
?>
