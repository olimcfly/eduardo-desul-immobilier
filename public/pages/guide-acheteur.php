<?php
require_once __DIR__ . '/../../core/bootstrap.php';

$advisor = $db->query("SELECT * FROM advisors WHERE id = 1")->fetch();

$pageTitle       = 'Guide Complet Acheteur — Acheter votre bien immobilier sereinement';
$pageDescription = 'Tout ce que vous devez savoir pour réussir votre achat immobilier : budget, recherche, offre, financement, signature. Guide gratuit 2025.';
$pageCanonical   = '/guide-acheteur';

ob_start();
?>

<link rel="stylesheet" href="/assets/css/guide.css">
<link rel="stylesheet" href="/assets/css/guide-acheteur.css">

<!-- Schema.org HowTo -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "HowTo",
  "name": "Comment acheter un bien immobilier",
  "description": "Guide complet pour réussir votre achat immobilier en <?= date('Y') ?>",
  "step": [
    {
      "@type": "HowToStep",
      "position": "1",
      "name": "Définir votre budget et capacité d'emprunt",
      "text": "Calculer votre apport, capacité d'emprunt et budget total"
    },
    {
      "@type": "HowToStep",
      "position": "2",
      "name": "Définir votre projet et critères",
      "text": "Localisation, surface, type de bien, critères indispensables"
    },
    {
      "@type": "HowToStep",
      "position": "3",
      "name": "Rechercher et visiter",
      "text": "Sources, alertes, organisation des visites, points de vigilance"
    },
    {
      "@type": "HowToStep",
      "position": "4",
      "name": "Faire une offre et négocier",
      "text": "Rédiger une offre d'achat, stratégies de négociation"
    },
    {
      "@type": "HowToStep",
      "position": "5",
      "name": "Financer votre acquisition",
      "text": "Dossier bancaire, comparaison offres, assurance emprunteur"
    },
    {
      "@type": "HowToStep",
      "position": "6",
      "name": "Signer et finaliser",
      "text": "Compromis, conditions suspensives, acte authentique"
    }
  ],
  "totalTime": "PT120D"
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
                    <span class="guide-hero__highlight">en toute sérénité</span>
                </h1>

                <p class="guide-hero__subtitle">
                    De la définition de votre budget jusqu'à la remise des clés :
                    les 6 étapes pour réussir votre achat immobilier,
                    éviter les pièges et acheter au bon prix.
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
                        Parler à un conseiller
                    </a>
                </div>

            </div>
        </div>
        <div class="guide-hero__wave">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z"
                      fill="#fafafa"/>
            </svg>
        </div>
    </section>

    <!-- ══ SOMMAIRE ══════════════════════════════════════════ -->
    <section class="guide-toc-section" id="sommaire">
        <div class="container">
            <div class="guide-toc-card">
                <div class="guide-toc-card__header">
                    <i class="fas fa-list"></i>
                    <h2>Au programme de ce guide</h2>
                    <span class="guide-toc-card__time">
                        <i class="fas fa-clock"></i> 25 min
                    </span>
                </div>

                <?php
                $steps = [
                    ['num'=>1,'icon'=>'fa-calculator',  'title'=>'Définir votre budget',       'desc'=>'Capacité d\'emprunt, apport, frais annexes'],
                    ['num'=>2,'icon'=>'fa-crosshairs',  'title'=>'Définir votre projet',        'desc'=>'Critères, localisation, type de bien'],
                    ['num'=>3,'icon'=>'fa-search',       'title'=>'Rechercher et visiter',       'desc'=>'Sources, alertes, check-list visite'],
                    ['num'=>4,'icon'=>'fa-file-signature','title'=>'Faire une offre',            'desc'=>'Rédaction, négociation, stratégies'],
                    ['num'=>5,'icon'=>'fa-piggy-bank',   'title'=>'Financer votre achat',        'desc'=>'Dossier bancaire, courtier, assurance'],
                    ['num'=>6,'icon'=>'fa-key',          'title'=>'Signer et finaliser',         'desc'=>'Compromis, acte authentique, remise clés'],
                ];
                foreach ($steps as $s) : ?>
                <a href="#etape-<?= $s['num'] ?>" class="toc-step">
                    <div class="toc-step__num">
                        <?= $s['num'] ?>
                    </div>
                    <div class="toc-step__icon toc-step__icon--acheteur">
                        <i class="fas <?= $s['icon'] ?>"></i>
                    </div>
                    <div class="toc-step__content">
                        <strong><?= $s['title'] ?></strong>
                        <span><?= $s['desc'] ?></span>
                    </div>
                    <i class="fas fa-chevron-right toc-step__arrow"></i>
                </a>
                <?php endforeach; ?>

                <a href="#checklist-acheteur" class="toc-step toc-step--bonus">
                    <div class="toc-step__num toc-step__num--bonus">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="toc-step__content">
                        <strong>Checklist complète acheteur</strong>
                        <span>32 points à valider avant de signer</span>
                    </div>
                    <i class="fas fa-chevron-right toc-step__arrow"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ══ CONTENU PRINCIPAL ═════════════════════════════════ -->
    <section class="guide-content-section">
        <div class="container">
            <div class="guide-layout">

                <main class="guide-main">

                    <!-- Intro -->
                    <div class="guide-intro-box guide-intro-box--acheteur">
                        <div class="guide-intro-box__icon">🏡</div>
                        <div>
                            <strong>Pourquoi ce guide existe</strong>
                            <p>
                                Acheter un bien immobilier est souvent la décision
                                financière la plus importante de votre vie.
                                Pourtant, la plupart des acheteurs découvrent les règles
                                du jeu au fil du parcours — parfois à leurs dépens.
                                Ce guide vous donne toutes les clés <em>avant</em> de
                                commencer, pour gagner du temps, éviter les erreurs
                                et négocier sereinement.
                            </p>
                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 1 — BUDGET
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-1">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">1</div>
                            <div>
                                <h2 class="guide-step__title">
                                    Définir votre budget réel
                                </h2>
                                <p class="guide-step__subtitle">
                                    Avant de visiter quoi que ce soit, connaissez précisément
                                    ce que vous pouvez dépenser — et ce que ça vous coûtera
                                    vraiment.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                L'erreur la plus fréquente des primo-accédants est de
                                visiter des biens avant d'avoir validé leur financement.
                                Résultat : coups de cœur impossibles, déceptions, et parfois
                                offres refusées faute de dossier solide.
                            </p>

                            <!-- Budget calculator box -->
                            <div class="budget-box">
                                <div class="budget-box__header">
                                    <i class="fas fa-calculator"></i>
                                    <h3>Les composantes de votre budget total</h3>
                                </div>
                                <div class="budget-rows">
                                    <div class="budget-row budget-row--main">
                                        <span class="budget-row__label">
                                            <i class="fas fa-home"></i>
                                            Prix du bien
                                        </span>
                                        <span class="budget-row__value">100 %</span>
                                    </div>
                                    <div class="budget-row budget-row--add">
                                        <span class="budget-row__label">
                                            <i class="fas fa-plus"></i>
                                            Frais de notaire (ancien)
                                        </span>
                                        <span class="budget-row__value budget-row__value--add">
                                            + 7 à 8 %
                                        </span>
                                    </div>
                                    <div class="budget-row budget-row--add">
                                        <span class="budget-row__label">
                                            <i class="fas fa-plus"></i>
                                            Frais de notaire (neuf)
                                        </span>
                                        <span class="budget-row__value budget-row__value--add">
                                            + 2 à 3 %
                                        </span>
                                    </div>
                                    <div class="budget-row budget-row--add">
                                        <span class="budget-row__label">
                                            <i class="fas fa-plus"></i>
                                            Frais d'agence (si charge acquéreur)
                                        </span>
                                        <span class="budget-row__value budget-row__value--add">
                                            + 3 à 5 %
                                        </span>
                                    </div>
                                    <div class="budget-row budget-row--add">
                                        <span class="budget-row__label">
                                            <i class="fas fa-plus"></i>
                                            Frais de dossier bancaire
                                        </span>
                                        <span class="budget-row__value budget-row__value--add">
                                            + 0,5 à 1 %
                                        </span>
                                    </div>
                                    <div class="budget-row budget-row--add">
                                        <span class="budget-row__label">
                                            <i class="fas fa-plus"></i>
                                            Garantie bancaire (caution / hypothèque)
                                        </span>
                                        <span class="budget-row__value budget-row__value--add">
                                            + 0,8 à 1,5 %
                                        </span>
                                    </div>
                                    <div class="budget-row budget-row--add">
                                        <span class="budget-row__label">
                                            <i class="fas fa-plus"></i>
                                            Travaux estimés + marge 20 %
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
                            </div>

                            <h3>Votre capacité d'emprunt</h3>
                            <p>
                                La règle d'or : votre taux d'endettement ne doit pas
                                dépasser <strong>35 %</strong> de vos revenus nets
                                (assurance emprunteur incluse). C'est la limite fixée
                                par le HCSF depuis 2022.
                            </p>

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
                                        <span class="formula__result">
                                            Mensualité maximale
                                        </span>
                                    </div>
                                    <div class="formula__example">
                                        Ex : 4 000 € nets × 35 % = <strong>1 400 €/mois</strong>
                                        → emprunt ~250 000 € sur 20 ans à 3,5 %
                                    </div>
                                </div>
                            </div>

                            <h3>L'apport personnel</h3>

                            <div class="stat-row">
                                <div class="stat-box">
                                    <div class="stat-box__number">10%</div>
                                    <div class="stat-box__label">Apport minimum recommandé (frais de notaire)</div>
                                </div>
                                <div class="stat-box stat-box--primary">
                                    <div class="stat-box__number">20%</div>
                                    <div class="stat-box__label">Apport idéal pour meilleures conditions</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-box__number">0%</div>
                                    <div class="stat-box__label">Possible avec PTZ ou profil excellent</div>
                                </div>
                            </div>

                            <div class="guide-callout guide-callout--info">
                                <i class="fas fa-lightbulb"></i>
                                <div>
                                    <strong>Le Prêt à Taux Zéro (PTZ)</strong>
                                    Vous êtes primo-accédant ? Le PTZ peut financer
                                    jusqu'à 40 % de votre achat sans intérêts.
                                    Conditions : ressources plafonnées, zone géographique,
                                    résidence principale. Renseignez-vous avant de monter
                                    votre dossier.
                                </div>
                            </div>

                            <!-- Aides tableau -->
                            <h3>Les aides à l'achat disponibles en 2025</h3>
                            <div class="aids-table">
                                <div class="aids-table__header">
                                    <span>Dispositif</span>
                                    <span>Montant</span>
                                    <span>Conditions</span>
                                </div>
                                <?php
                                $aids = [
                                    ['PTZ 2025',          'Jusqu\'à 100 000 €', 'Primo-accédant, zones éligibles, plafonds revenus'],
                                    ['Action Logement',   'Jusqu\'à 30 000 €',  'Salarié secteur privé, résidence principale'],
                                    ['Prêt 1% patronal',  'Variable',           'Salarié entreprise +50 salariés'],
                                    ['Prêt fonctionnaire','Jusqu\'à 35 000 €',  'Agent de la fonction publique'],
                                    ['Aide 1ère tranche', 'Variable',           'Selon collectivité locale'],
                                ];
                                foreach ($aids as $a) : ?>
                                <div class="aids-table__row">
                                    <span class="aids-table__name">
                                        <i class="fas fa-check-circle"></i>
                                        <?= $a[0] ?>
                                    </span>
                                    <span class="aids-table__amount"><?= $a[1] ?></span>
                                    <span class="aids-table__cond"><?= $a[2] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="guide-cta-inline">
                                <div class="guide-cta-inline__text">
                                    <strong>Simulez votre capacité d'emprunt</strong>
                                    <span>Obtenez une estimation personnalisée en 2 minutes</span>
                                </div>
                                <a href="/contact?sujet=financement"
                                   class="btn btn--primary btn--sm">
                                    Simuler maintenant
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 2 — PROJET
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-2">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">2</div>
                            <div>
                                <h2 class="guide-step__title">
                                    Définir votre projet avec précision
                                </h2>
                                <p class="guide-step__subtitle">
                                    Un projet bien défini = une recherche efficace.
                                    Clarifiez vos critères avant de vous lancer.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                Sans liste de critères précise, vous risquez de
                                visiter des dizaines de biens sans vous décider,
                                ou à l'inverse de laisser passer <em>le bon</em>
                                faute d'avoir réfléchi à vos priorités.
                            </p>

                            <!-- Critères grid -->
                            <div class="criteria-grid">
                                <div class="criteria-card criteria-card--must">
                                    <div class="criteria-card__header">
                                        <i class="fas fa-lock"></i>
                                        <h4>Critères indispensables</h4>
                                        <span class="criteria-badge criteria-badge--must">Non négociable</span>
                                    </div>
                                    <ul>
                                        <li>Budget maximum absolu</li>
                                        <li>Localisation (commune / quartier)</li>
                                        <li>Nombre de chambres minimum</li>
                                        <li>Type de bien (maison / appart)</li>
                                        <li>Classe DPE minimum (E max)</li>
                                    </ul>
                                </div>
                                <div class="criteria-card criteria-card--want">
                                    <div class="criteria-card__header">
                                        <i class="fas fa-star"></i>
                                        <h4>Critères souhaitables</h4>
                                        <span class="criteria-badge criteria-badge--want">Important</span>
                                    </div>
                                    <ul>
                                        <li>Parking / garage</li>
                                        <li>Extérieur (balcon, jardin, terrasse)</li>
                                        <li>Étage (pas RDC)</li>
                                        <li>Double exposition</li>
                                        <li>Proximité transports</li>
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
                                        <li>Gardien / digicode</li>
                                        <li>Parquet ancien</li>
                                        <li>Hauteur sous plafond</li>
                                        <li>Vue dégagée</li>
                                    </ul>
                                </div>
                            </div>

                            <h3>Neuf ou Ancien ?</h3>

                            <div class="compare-table">
                                <div class="compare-table__header">
                                    <span></span>
                                    <span class="compare-table__col compare-table__col--a">
                                        <i class="fas fa-building"></i> Neuf
                                    </span>
                                    <span class="compare-table__col compare-table__col--b">
                                        <i class="fas fa-home"></i> Ancien
                                    </span>
                                </div>
                                <?php
                                $rows = [
                                    ['Frais de notaire',    '2 à 3 %',          '7 à 8 %'],
                                    ['Prix au m²',          'Plus élevé',        'Plus accessible'],
                                    ['Travaux',             'Aucun (garanties)', 'À prévoir'],
                                    ['Délai livraison',     '12 à 24 mois',      'Immédiat'],
                                    ['Personnalisation',    'Possible (VEFA)',   'Limitée'],
                                    ['DPE',                 'A ou B garanti',    'Variable (A à G)'],
                                    ['Garanties',           'Décennale, parfait achèvement', 'Diagnostics obligatoires'],
                                    ['Charme / cachet',     'Neutre',            'Fort potentiel'],
                                ];
                                foreach ($rows as $r) : ?>
                                <div class="compare-table__row">
                                    <span class="compare-table__criterion"><?= $r[0] ?></span>
                                    <span class="compare-table__col compare-table__col--a"><?= $r[1] ?></span>
                                    <span class="compare-table__col compare-table__col--b"><?= $r[2] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="guide-callout guide-callout--warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Attention au DPE</strong>
                                    Depuis 2025, les logements classés G sont interdits
                                    à la location. Évitez d'acheter un bien énergivore
                                    si vous envisagez un investissement locatif —
                                    et anticipez le coût de rénovation si c'est
                                    votre résidence principale.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 3 — RECHERCHE & VISITES
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-3">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">3</div>
                            <div>
                                <h2 class="guide-step__title">
                                    Rechercher, alerter, visiter
                                </h2>
                                <p class="guide-step__subtitle">
                                    Les bons biens partent vite. Voici comment
                                    être le premier informé et ne rien rater.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <h3>Les sources de biens</h3>

                            <div class="sources-grid">
                                <?php
                                $sources = [
                                    ['icon'=>'fa-building-user', 'name'=>'Agences locales',   'pct'=>'45%', 'tip'=>'Relation directe, biens off-market possibles',    'color'=>'blue'],
                                    ['icon'=>'fa-globe',         'name'=>'Portails (SeLoger…)', 'pct'=>'35%', 'tip'=>'Large choix, alertes email, concurrence forte',   'color'=>'green'],
                                    ['icon'=>'fa-people-group',  'name'=>'Réseau personnel',  'pct'=>'12%', 'tip'=>'Bouche à oreille — souvent les meilleures affaires', 'color'=>'purple'],
                                    ['icon'=>'fa-gavel',         'name'=>'Notaires / ventes', 'pct'=>'5%',  'tip'=>'Succession, licitation — prix souvent attractifs',  'color'=>'orange'],
                                    ['icon'=>'fa-newspaper',     'name'=>'PAP / particuliers', 'pct'=>'3%',  'tip'=>'Sans frais agence, mais plus de risques juridiques', 'color'=>'gray'],
                                ];
                                foreach ($sources as $s) : ?>
                                <div class="source-card source-card--<?= $s['color'] ?>">
                                    <div class="source-card__icon">
                                        <i class="fas <?= $s['icon'] ?>"></i>
                                    </div>
                                    <div class="source-card__content">
                                        <strong><?= $s['name'] ?></strong>
                                        <span class="source-card__pct"><?= $s['pct'] ?> des ventes</span>
                                        <p><?= $s['tip'] ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <h3>Check-list visite — à vérifier sur place</h3>

                            <div class="visit-checklist">
                                <?php
                                $visit_items = [
                                    'Structure' => [
                                        'Fissures (murs, plafond, façade) ?',
                                        'Traces d\'humidité ou moisissures ?',
                                        'État de la toiture (maison) ?',
                                        'Présence d\'amiante ou plomb (diagnostics) ?',
                                    ],
                                    'Technique' => [
                                        'Âge et état de la chaudière / chauffe-eau',
                                        'Tableau électrique aux normes ?',
                                        'DPE réel vs affiché (consommation réelle)',
                                        'Débit internet (fibre disponible ?)',
                                        'Pression et qualité de l\'eau',
                                    ],
                                    'Environnement' => [
                                        'Bruit (route, voisins, commerce) ?',
                                        'Ensoleillement aux heures clés',
                                        'Vis-à-vis et intimité',
                                        'Stationnement disponible ?',
                                        'Proximité écoles, transports, commerces',
                                    ],
                                    'Copropriété (si appart)' => [
                                        'Montant des charges mensuelles',
                                        'Procès-verbaux des 3 dernières AG',
                                        'Travaux votés à venir (ravalement ?)',
                                        'Fonds de travaux (loi ALUR)',
                                        'Règlement de copropriété',
                                    ],
                                ];
                                foreach ($visit_items as $cat => $items) : ?>
                                <div class="visit-category">
                                    <div class="visit-category__title">
                                        <i class="fas fa-clipboard-check"></i>
                                        <?= $cat ?>
                                    </div>
                                    <ul class="visit-list">
                                        <?php foreach ($items as $item) : ?>
                                        <li>
                                            <i class="fas fa-search"></i>
                                            <?= $item ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="guide-callout guide-callout--success">
                                <i class="fas fa-lightbulb"></i>
                                <div>
                                    <strong>Astuce pro : visitez 2 fois à des heures différentes</strong>
                                    La 1ère visite pour le coup de cœur, la 2ème
                                    (avec un proche ou un artisan) pour les points
                                    techniques. Ne faites jamais d'offre après une
                                    seule visite rapide.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 4 — OFFRE & NÉGOCIATION
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-4">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">4</div>
                            <div>
                                <h2 class="guide-step__title">
                                    Faire une offre et négocier
                                </h2>
                                <p class="guide-step__subtitle">
                                    Rédiger une offre solide et négocier intelligemment
                                    sans perdre le bien ni surpayer.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <p>
                                Une offre d'achat est un engagement juridique fort.
                                Une fois acceptée par le vendeur, vous êtes lié.
                                Prenez le temps de la rédiger correctement.
                            </p>

                            <!-- Négociation scenarios -->
                            <h3>Quelle marge de négociation espérer ?</h3>

                            <div class="nego-grid">
                                <div class="nego-card nego-card--low">
                                    <div class="nego-card__pct">0 – 2 %</div>
                                    <div class="nego-card__label">Marché tendu</div>
                                    <p>Bien rare, localisation premium, demande forte. Offrir au prix affiché ou au-dessus si concurrence.</p>
                                </div>
                                <div class="nego-card nego-card--mid">
                                    <div class="nego-card__pct">3 – 5 %</div>
                                    <div class="nego-card__label">Marché équilibré</div>
                                    <p>Marge standard, justifiée par les travaux ou comparaison de marché.</p>
                                </div>
                                <div class="nego-card nego-card--high">
                                    <div class="nego-card__pct">6 – 10 %</div>
                                    <div class="nego-card__label">Bien sur-estimé</div>
                                    <p>Délai de vente &gt; 3 mois, travaux importants, marché en baisse. Appuyez sur les faits.</p>
                                </div>
                                <div class="nego-card nego-card--max">
                                    <div class="nego-card__pct">&gt; 10 %</div>
                                    <div class="nego-card__label">Situation particulière</div>
                                    <p>Succession pressée, liquidité urgente, défauts majeurs. Négociation possible mais sensible.</p>
                                </div>
                            </div>

                            <h3>Contenu d'une offre d'achat solide</h3>

                            <div class="offer-template">
                                <div class="offer-template__header">
                                    <i class="fas fa-file-alt"></i>
                                    Éléments obligatoires dans votre offre
                                </div>
                                <?php
                                $offer_items = [
                                    ['Prix proposé',                  'En chiffres et en lettres, net vendeur ou FAI'],
                                    ['Identité de l\'acheteur',       'Nom, prénom, adresse'],
                                    ['Description du bien',           'Adresse exacte, surface, référence cadastrale'],
                                    ['Durée de validité',             '5 à 10 jours maximum recommandés'],
                                    ['Conditions suspensives',        'Obtention prêt (obligatoire si financement)'],
                                    ['Modalités de réponse',          'Écrit (email, courrier RAR)'],
                                    ['Financement',                   'Montant emprunt, banque pressentie ou accord de principe'],
                                ];
                                foreach ($offer_items as $o) : ?>
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

                            <div class="guide-callout guide-callout--warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Ne faites jamais d'offre verbale sans écrit</strong>
                                    Une offre verbale n'a aucune valeur juridique.
                                    Confirmez toujours par email ou courrier.
                                    Et attention : une offre au prix du mandat
                                    oblige le vendeur à accepter (principe d'offre conforme).
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 5 — FINANCEMENT
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-5">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">5</div>
                            <div>
                                <h2 class="guide-step__title">
                                    Financer votre achat
                                </h2>
                                <p class="guide-step__subtitle">
                                    Monter un dossier béton, comparer les offres
                                    et économiser sur l'assurance emprunteur.
                                </p>
                            </div>
                        </div>
                        <div class="guide-step__content">

                            <h3>Les documents du dossier bancaire</h3>

                            <div class="doc-columns">
                                <div class="doc-col">
                                    <div class="doc-col__title">
                                        <i class="fas fa-user"></i>
                                        Pièces d'identité
                                    </div>
                                    <ul>
                                        <li>CNI ou passeport</li>
                                        <li>Livret de famille</li>
                                        <li>Contrat de mariage / PACS</li>
                                        <li>Justificatif de domicile (&lt; 3 mois)</li>
                                    </ul>
                                </div>
                                <div class="doc-col">
                                    <div class="doc-col__title">
                                        <i class="fas fa-briefcase"></i>
                                        Revenus & emploi
                                    </div>
                                    <ul>
                                        <li>3 derniers bulletins de salaire</li>
                                        <li>Contrat de travail (CDI/CDD)</li>
                                        <li>2 derniers avis d'imposition</li>
                                        <li>Bilan si indépendant (3 ans)</li>
                                    </ul>
                                </div>
                                <div class="doc-col">
                                    <div class="doc-col__title">
                                        <i class="fas fa-piggy-bank"></i>
                                        Finances
                                    </div>
                                    <ul>
                                        <li>3 derniers relevés bancaires</li>
                                        <li>Relevés livrets épargne</li>
                                        <li>Tableau des crédits en cours</li>
                                        <li>Justificatifs donations / épargne</li>
                                    </ul>
                                </div>
                                <div class="doc-col">
                                    <div class="doc-col__title">
                                        <i class="fas fa-home"></i>
                                        Projet immobilier
                                    </div>
                                    <ul>
                                        <li>Compromis de vente signé</li>
                                        <li>Diagnostics immobiliers</li>
                                        <li>Devis travaux (si applicable)</li>
                                        <li>Règlement de copropriété</li>
                                    </ul>
                                </div>
                            </div>

                            <h3>Courtier ou banque en direct ?</h3>

                            <div class="broker-compare">
                                <div class="broker-card broker-card--direct">
                                    <div class="broker-card__header">
                                        <i class="fas fa-university"></i>
                                        <h4>Banque en direct</h4>
                                    </div>
                                    <ul class="broker-card__pros">
                                        <li><i class="fas fa-check"></i> Relation bancaire existante</li>
                                        <li><i class="fas fa-check"></i> Décision parfois plus rapide</li>
                                        <li><i class="fas fa-check"></i> Packages fidélité possibles</li>
                                    </ul>
                                    <ul class="broker-card__cons">
                                        <li><i class="fas fa-times"></i> Une seule offre</li>
                                        <li><i class="fas fa-times"></i> Taux rarement optimisés</li>
                                        <li><i class="fas fa-times"></i> Démarches en autonomie</li>
                                    </ul>
                                </div>
                                <div class="broker-card broker-card--broker broker-card--recommended">
                                    <div class="broker-card__badge">Recommandé</div>
                                    <div class="broker-card__header">
                                        <i class="fas fa-handshake"></i>
                                        <h4>Courtier immobilier</h4>
                                    </div>
                                    <ul class="broker-card__pros">
                                        <li><i class="fas fa-check"></i> Compare 20+ banques</li>
                                        <li><i class="fas fa-check"></i> Taux négociés en volume</li>
                                        <li><i class="fas fa-check"></i> Accompagnement dossier</li>
                                        <li><i class="fas fa-check"></i> Honoraires souvent nuls</li>
                                    </ul>
                                    <ul class="broker-card__cons">
                                        <li><i class="fas fa-times"></i> +1 à 2 semaines de délai</li>
                                    </ul>
                                    <div class="broker-card__saving">
                                        Économie moyenne : <strong>8 000 – 15 000 €</strong>
                                        sur la durée du prêt
                                    </div>
                                </div>
                            </div>

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

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         ÉTAPE 6 — SIGNATURE
                    ───────────────────────────────────────── -->
                    <div class="guide-step" id="etape-6">
                        <div class="guide-step__header">
                            <div class="guide-step__number guide-step__number--acheteur">6</div>
                            <div>
                                <h2 class="guide-step__title">
                                    Signer et finaliser votre achat
                                </h2>
                                <p class="guide-step__subtitle">
                                    De l'offre acceptée à la remise des clés :
                                    les étapes juridiques et les délais à respecter.
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
                                        <div class="timeline-item__day">J+7 à J+30</div>
                                        <h4>Signature du compromis de vente</h4>
                                        <p>
                                            Chez le notaire ou en agence.
                                            Versement du dépôt de garantie :
                                            5 à 10 % du prix de vente.
                                        </p>
                                        <div class="timeline-item__tag">
                                            <i class="fas fa-info-circle"></i>
                                            Dépôt de garantie versé à cette étape
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-undo"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+17</div>
                                        <h4>Fin du délai de rétractation SRU</h4>
                                        <p>
                                            10 jours pour vous rétracter sans motif
                                            ni pénalité. Après ce délai, vous êtes
                                            engagé (sauf conditions suspensives).
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+30 à J+60</div>
                                        <h4>Obtention du financement</h4>
                                        <p>
                                            Dépôt du dossier bancaire, accord de principe,
                                            offre de prêt officielle.
                                            Délai légal de réflexion : 10 jours.
                                        </p>
                                        <div class="timeline-item__tag timeline-item__tag--warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Condition suspensive : vente annulée si refus de prêt
                                        </div>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur">
                                        <i class="fas fa-gavel"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+60 à J+90</div>
                                        <h4>Signature de l'acte authentique</h4>
                                        <p>
                                            Chez le notaire. Versement du solde
                                            (prix − dépôt + frais de notaire).
                                            Transfert de propriété effectif.
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-item__dot timeline-item__dot--acheteur timeline-item__dot--final">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="timeline-item__content">
                                        <div class="timeline-item__day">J+90</div>
                                        <h4>Remise des clés 🎉</h4>
                                        <p>
                                            Vous êtes officiellement propriétaire.
                                            Relevez les compteurs, souscrivez votre
                                            assurance habitation avant la remise.
                                        </p>
                                    </div>
                                </div>

                            </div>

                            <div class="guide-callout guide-callout--info">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <strong>Souscrivez votre assurance habitation avant la signature</strong>
                                    Vous êtes responsable du bien dès la signature de l'acte
                                    authentique. L'assurance doit être active ce jour-là —
                                    certains notaires demandent l'attestation.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────
                         CHECKLIST ACHETEUR
                    ───────────────────────────────────────── -->
                    <div class="guide-checklist" id="checklist-acheteur">
                        <div class="guide-checklist__header">
                            <div class="guide-checklist__title">
                                <i class="fas fa-clipboard-check"></i>
                                <h2>Checklist complète acheteur</h2>
                            </div>
                            <div class="guide-checklist__actions">
                                <span class="checklist-badge" id="checklistBadge">0 / 32</span>
                                <button class="btn btn--ghost btn--sm" id="resetChecklist">
                                    <i class="fas fa-redo"></i>
                                    Réinitialiser
                                </button>
                            </div>
                        </div>

                        <?php
                        $all_checks = [
                            'Budget' => [
                                'a1'  => 'Capacité d\'emprunt calculée (35 % max)',
                                'a2'  => 'Apport personnel disponible et justifié',
                                'a3'  => 'Budget total incluant frais (notaire, agence, travaux)',
                                'a4'  => 'Aides disponibles identifiées (PTZ, APL accession…)',
                                'a5'  => 'Accord de principe bancaire obtenu',
                            ],
                            'Projet & Critères' => [
                                'a6'  => 'Liste critères indispensables / souhaitables rédigée',
                                'a7'  => 'Localisation(s) ciblée(s) et validée(s)',
                                'a8'  => 'Type de bien défini (neuf / ancien, maison / appart)',
                                'a9'  => 'DPE minimum acceptable défini',
                            ],
                            'Recherche' => [
                                'a10' => 'Alertes activées sur portails immobiliers',
                                'a11' => 'Mandat de recherche déposé en agence',
                                'a12' => 'Réseau personnel informé de votre recherche',
                                'a13' => 'Minimum 5 à 8 biens visités avant offre',
                            ],
                            'Visite & Vérification' => [
                                'a14' => 'Check-list visite complétée (structure, technique)',
                                'a15' => '2ème visite réalisée à un horaire différent',
                                'a16' => 'Diagnostics immobiliers lus et compris',
                                'a17' => 'Charges de copropriété et PV d\'AG vérifiés',
                                'a18' => 'Travaux votés identifiés',
                                'a19' => 'Prix comparé aux ventes récentes du secteur',
                            ],
                            'Offre & Négociation' => [
                                'a20' => 'Offre d\'achat rédigée par écrit',
                                'a21' => 'Conditions suspensives incluses (prêt, diagnostics)',
                                'a22' => 'Durée de validité fixée (≤ 10 jours)',
                                'a23' => 'Offre envoyée et accusé de réception conservé',
                            ],
                            'Financement' => [
                                'a24' => 'Dossier bancaire complet préparé',
                                'a25' => 'Comparaison a minima 3 offres de prêt',
                                'a26' => 'Courtier consulté',
                                'a27' => 'Assurance emprunteur comparée (délégation)',
                                'a28' => 'Offre de prêt reçue et délai 10 jours respecté',
                            ],
                            'Signature' => [
                                'a29' => 'Compromis signé et dépôt de garantie versé',
                                'a30' => 'Délai rétractation SRU (10 j) écoulé',
                                'a31' => 'Assurance habitation souscrite avant acte',
                                'a32' => 'Acte authentique signé + relevés compteurs',
                            ],
                        ];
                        foreach ($all_checks as $group => $items) : ?>
                        <div class="checklist-group">
                            <div class="checklist-group__title">
                                <span class="step-badge step-badge--acheteur">
                                    <i class="fas fa-check"></i>
                                </span>
                                <?= $group ?>
                            </div>
                            <?php foreach ($items as $id => $label) : ?>
                            <label class="checklist-item" for="<?= $id ?>">
                                <input type="checkbox"
                                       id="<?= $id ?>"
                                       class="checklist-check"
                                       data-id="<?= $id ?>">
                                <div class="checklist-item__box checklist-item__box--acheteur">
                                    <i class="fas fa-check"></i>
                                </div>
                                <span><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>

                        <div class="checklist-progress">
                            <div class="checklist-progress__bar">
                                <div class="checklist-progress__fill
                                            checklist-progress__fill--acheteur"
                                     id="checklistFill"
                                     style="width:0%">
                                </div>
                            </div>
                            <div class="checklist-progress__label">
                                <span id="checklistCount">0</span> / 32 étapes complétées
                            </div>
                        </div>

                    </div>

                    <!-- ══ CTA FINAL ══════════════════════════ -->
                    <section class="guide-cta-final guide-cta-final--acheteur">
                        <div class="guide-cta-final__inner">
                            <div class="guide-cta-final__badge">
                                <i class="fas fa-key"></i>
                                Accompagnement personnalisé
                            </div>
                            <h2>Vous avez un projet d'achat ?</h2>
                            <p>
                                Vous connaissez maintenant toutes les étapes.
                                Si vous souhaitez être accompagné par un conseiller
                                qui connaît le marché local, qui peut vous alerter
                                en avant-première et vous éviter les pièges —
                                je suis disponible pour un premier échange gratuit.
                            </p>
                            <div class="guide-cta-final__actions">
                                <a href="/contact?sujet=achat" class="btn btn--primary btn--lg">
                                    <i class="fas fa-comments"></i>
                                    Déposer ma recherche
                                </a>
                                <a href="/biens" class="btn btn--white btn--lg">
                                    <i class="fas fa-search"></i>
                                    Voir les biens disponibles
                                </a>
                            </div>
                            <p class="guide-cta-final__reassurance">
                                <i class="fas fa-lock"></i>
                                Sans engagement · Réponse sous 24h · 100% gratuit
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
                            <div class="sidebar-progress__fill"
                                 id="sidebarProgress"></div>
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
                            foreach ($sidebar_steps as $num => $label) : ?>
                            <a href="#etape-<?= $num ?>"
                               class="progress-step"
                               data-step="<?= $num ?>">
                                <div class="progress-step__dot"></div>
                                <span><?= $label ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- CTA Déposer recherche -->
                    <div class="sidebar-card sidebar-card--cta sidebar-card--cta-acheteur">
                        <div class="sidebar-cta__icon sidebar-cta__icon--acheteur">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3>Alertes biens</h3>
                        <p>
                            Recevez en avant-première les biens
                            correspondant à vos critères.
                        </p>
                        <a href="/contact?sujet=alerte"
                           class="btn btn--acheteur btn--block">
                            Créer mon alerte
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <p class="sidebar-cta__note">
                            <i class="fas fa-lock"></i>
                            Gratuit · Sans engagement
                        </p>
                    </div>

                    <!-- Contact -->
                    <div class="sidebar-card sidebar-card--contact">
                        <?php if ($advisor) : ?>
                        <div class="sidebar-advisor">
                            <img src="<?= htmlspecialchars($advisor['photo']) ?>"
                                 alt="<?= htmlspecialchars($advisor['name']) ?>"
                                 class="sidebar-advisor__photo">
                            <div class="sidebar-advisor__info">
                                <strong><?= htmlspecialchars($advisor['name']) ?></strong>
                                <span><?= htmlspecialchars($advisor['title']) ?></span>
                            </div>
                        </div>
                        <p class="sidebar-advisor__quote">
                            "<?= htmlspecialchars($advisor['quote'] ?? 'Je vous accompagne dans votre projet d\'achat de A à Z.') ?>"
                        </p>
                        <a href="tel:<?= htmlspecialchars($advisor['phone']) ?>"
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
                            "Grâce à ce guide et à l'accompagnement de l'agence,
                            j'ai trouvé mon appartement en 3 semaines et j'ai
                            négocié 12 000 € sous le prix affiché."
                        </blockquote>
                        <div class="sidebar-testimonial__author">
                            <div class="sidebar-testimonial__avatar">M</div>
                            <div>
                                <strong>Marie D.</strong>
                                <span>Acheteuse — Lyon 6e</span>
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
