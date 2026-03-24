<?php
/**
 * PAGE DE CAPTURE GUIDE
 * /front/capture/index.php
 * URL : /capture/{guide-id}
 * Routé depuis .htaccess : ^capture/([a-z0-9-]+)/?$ → /front/capture/index.php?slug=$1
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';

// ── Catalogue complet des guides ──
$guides_catalog = [
    'guide-vente-prix' => [
        'persona'     => 'vendeur',
        'name'        => 'Comment fixer le juste prix de vente',
        'description' => 'Méthode complète pour ne pas brûler votre bien sur le marché ni laisser d\'argent sur la table.',
        'pages'       => '12 pages',
        'icon'        => '💰',
        'tag'         => 'Pricing',
        'color_from'  => '#d4a574',
        'color_to'    => '#c9913b',
        'color_light' => '#fdf6ee',
        'chapitres'   => [
            'Pourquoi le prix est le levier n°1',
            'Les 3 méthodes d\'évaluation professionnelles',
            'Analyser les comparables du marché local',
            'Fixer le prix psychologique juste',
            'Adapter son prix si pas de visite sous 3 semaines',
            'Les erreurs de pricing les plus fréquentes',
        ],
        'promesse'    => 'Vendez au bon prix, au bon moment.',
        'extrait'     => 'Un bien correctement estimé se vend en moyenne 2,5 fois plus vite et avec 4 à 7% d\'écart positif sur le prix de vente final.',
    ],
    'guide-vente-preparation' => [
        'persona'     => 'vendeur',
        'name'        => 'Préparer son bien avant la vente',
        'description' => 'Checklist home staging, petits travaux à réaliser et pièges à éviter avant les premières visites.',
        'pages'       => '9 pages',
        'icon'        => '🏡',
        'tag'         => 'Home Staging',
        'color_from'  => '#d4a574',
        'color_to'    => '#c9913b',
        'color_light' => '#fdf6ee',
        'chapitres'   => [
            'Le home staging : principes fondamentaux',
            'Checklist avant les premières photos',
            'Les 5 pièces qui font la vente',
            'Petits travaux à réaliser (et ceux à éviter)',
            'Désencombrer et dépersonnaliser',
            'Préparer la visite idéale',
        ],
        'promesse'    => 'Faites une première impression inoubliable.',
        'extrait'     => 'Une mise en scène soignée peut augmenter la valeur perçue d\'un bien de 10 à 15%. Les acheteurs décident dans les 90 premières secondes d\'une visite.',
    ],
    'guide-vente-documents' => [
        'persona'     => 'vendeur',
        'name'        => 'Tous les documents obligatoires pour vendre',
        'description' => 'Liste exhaustive des diagnostics, pièces administratives et délais légaux à respecter en 2024.',
        'pages'       => '8 pages',
        'icon'        => '📋',
        'tag'         => 'Administratif',
        'color_from'  => '#d4a574',
        'color_to'    => '#c9913b',
        'color_light' => '#fdf6ee',
        'chapitres'   => [
            'Les diagnostics obligatoires (DPE, amiante, plomb...)',
            'Les documents de copropriété si applicable',
            'Le titre de propriété et ses annexes',
            'Urbanisme : déclarations et autorisations',
            'Délais légaux à respecter',
            'Checklist finale avant signature',
        ],
        'promesse'    => 'Aucune surprise, aucun blocage de dernière minute.',
        'extrait'     => 'L\'absence d\'un diagnostic obligatoire peut entraîner la nullité de la vente. Anticipez ces documents dès le mandat.',
    ],
    'guide-vente-negociation' => [
        'persona'     => 'vendeur',
        'name'        => 'Négocier sans brader son bien',
        'description' => 'Techniques pour répondre aux offres basses, gérer les contre-offres et protéger votre prix.',
        'pages'       => '10 pages',
        'icon'        => '🤝',
        'tag'         => 'Négociation',
        'color_from'  => '#d4a574',
        'color_to'    => '#c9913b',
        'color_light' => '#fdf6ee',
        'chapitres'   => [
            'Comprendre la psychologie de l\'acheteur',
            'Décoder une offre basse (signal ou tactique ?)',
            'Les 3 techniques de contre-offre efficaces',
            'Ce sur quoi on peut céder sans perdre',
            'Gérer plusieurs offres simultanées',
            'Savoir dire non et conserver le bien',
        ],
        'promesse'    => 'Défendez votre prix avec méthode.',
        'extrait'     => '80% des négociations se jouent dans les premières 48h après une visite. Ne répondez jamais sous émotion.',
    ],
    'guide-vente-delais' => [
        'persona'     => 'vendeur',
        'name'        => 'Comprendre les délais de vente',
        'description' => 'De la signature du mandat jusqu\'à l\'acte authentique : toutes les étapes et leur durée.',
        'pages'       => '7 pages',
        'icon'        => '📅',
        'tag'         => 'Étapes',
        'color_from'  => '#d4a574',
        'color_to'    => '#c9913b',
        'color_light' => '#fdf6ee',
        'chapitres'   => [
            'De l\'estimation au mandat : 1 à 2 semaines',
            'Mise en vente et premières visites',
            'Offre acceptée au compromis : délais légaux',
            'Du compromis à l\'acte définitif (3 mois)',
            'Les délais incompressibles à anticiper',
            'Optimiser son calendrier de vente',
        ],
        'promesse'    => 'Planifiez votre vente sans mauvaise surprise.',
        'extrait'     => 'À Bordeaux, les biens bien estimés se vendent entre 45 et 70 jours en 2024. Découvrez comment optimiser ce délai.',
    ],
    'guide-achat-budget' => [
        'persona'     => 'acheteur',
        'name'        => 'Calculer son budget d\'achat réel',
        'description' => 'Frais de notaire, frais d\'agence, travaux, garanties bancaires : le vrai coût d\'un achat immobilier.',
        'pages'       => '11 pages',
        'icon'        => '🧮',
        'tag'         => 'Budget',
        'color_from'  => '#1a4d7a',
        'color_to'    => '#2d7dd2',
        'color_light' => '#eef4fb',
        'chapitres'   => [
            'Le prix d\'achat n\'est que le début',
            'Frais de notaire : calcul et optimisation',
            'Frais d\'agence : qui paie quoi ?',
            'Enveloppe travaux : comment l\'estimer',
            'Frais bancaires et garanties',
            'Budget total réel : simulateur pas à pas',
        ],
        'promesse'    => 'Achetez sans mauvaise surprise financière.',
        'extrait'     => 'Pour un bien à 300 000€, prévoyez entre 330 000 et 345 000€ de budget total. Ce guide vous détaille chaque poste.',
    ],
    'guide-achat-visite' => [
        'persona'     => 'acheteur',
        'name'        => '30 points à vérifier lors d\'une visite',
        'description' => 'Check-list complète pour détecter les vices cachés, évaluer l\'état réel du bien et poser les bonnes questions.',
        'pages'       => '6 pages',
        'icon'        => '🔍',
        'tag'         => 'Visite',
        'color_from'  => '#1a4d7a',
        'color_to'    => '#2d7dd2',
        'color_light' => '#eef4fb',
        'chapitres'   => [
            'Avant la visite : les questions à préparer',
            'L\'extérieur et les parties communes',
            'Structure, murs et toiture',
            'Plomberie, électricité, chauffage',
            'Luminosité, exposition et bruit',
            'Les questions à poser au vendeur',
        ],
        'promesse'    => 'Ne ratez plus aucun défaut caché.',
        'extrait'     => 'Les 3 points les plus négligés : l\'orientation, l\'état de la toiture (jusqu\'à 20 000€ de travaux) et les charges de copropriété cachées.',
    ],
    'guide-achat-pret' => [
        'persona'     => 'acheteur',
        'name'        => 'Obtenir le meilleur taux de crédit',
        'description' => 'Comment présenter son dossier bancaire, comparer les offres et activer les aides (PTZ, Action Logement...).',
        'pages'       => '13 pages',
        'icon'        => '🏦',
        'tag'         => 'Financement',
        'color_from'  => '#1a4d7a',
        'color_to'    => '#2d7dd2',
        'color_light' => '#eef4fb',
        'chapitres'   => [
            'Les critères des banques en 2024',
            'Constitution du dossier parfait',
            'Comparer les offres de crédit efficacement',
            'Le Prêt à Taux Zéro (PTZ) : conditions',
            'Action Logement et autres aides',
            'Négocier son taux et ses conditions',
        ],
        'promesse'    => 'Économisez des milliers d\'euros sur votre prêt.',
        'extrait'     => 'Un dossier bien préparé peut faire gagner jusqu\'à 0,4 point de taux. Sur 250 000€ / 20 ans, cela représente plus de 12 000€ d\'économies.',
    ],
    'guide-achat-offre' => [
        'persona'     => 'acheteur',
        'name'        => 'Faire une offre d\'achat efficace',
        'description' => 'Rédiger une offre sérieuse, les clauses suspensives à inclure et les erreurs qui font perdre le bien.',
        'pages'       => '8 pages',
        'icon'        => '✍️',
        'tag'         => 'Offre',
        'color_from'  => '#1a4d7a',
        'color_to'    => '#2d7dd2',
        'color_light' => '#eef4fb',
        'chapitres'   => [
            'Quand et comment formuler une offre',
            'Les mentions obligatoires d\'une offre écrite',
            'Les clauses suspensives à inclure',
            'Offre au prix vs offre négociée',
            'Délai de réponse et relance',
            'Après l\'acceptation : les étapes suivantes',
        ],
        'promesse'    => 'Faites une offre qui emporte le bien.',
        'extrait'     => 'Une offre sans clause suspensive de financement peut vous faire perdre votre acompte. Cette clause est votre filet de sécurité légal le plus important.',
    ],
    'guide-achat-quartiers' => [
        'persona'     => 'acheteur',
        'name'        => 'Les quartiers de Bordeaux décryptés',
        'description' => 'Chartrons, Caudéran, Mériadeck, Saint-Michel... Profil, prix au m², dynamisme et conseils par secteur.',
        'pages'       => '18 pages',
        'icon'        => '🗺️',
        'tag'         => 'Local',
        'color_from'  => '#1a4d7a',
        'color_to'    => '#2d7dd2',
        'color_light' => '#eef4fb',
        'chapitres'   => [
            'Les Chartrons : bobo et dynamique',
            'Caudéran : calme et familial',
            'Saint-Michel / Capucins : multiculturel et vivant',
            'Mériadeck : modernité et transport',
            'Bordeaux Nord : accessible et en plein essor',
            'Blanquefort, Mérignac, Pessac : grande couronne',
        ],
        'promesse'    => 'Choisissez le bon quartier pour votre projet.',
        'extrait'     => 'Le prix au m² à Bordeaux varie de 3 200€ à 6 500€. Chaque quartier a ses propres tendances que ce guide analyse en détail.',
    ],
    'guide-proprio-fiscalite' => [
        'persona'     => 'proprietaire',
        'name'        => 'Fiscalité immobilière : ce qu\'il faut savoir',
        'description' => 'Impôt sur les plus-values, taxe foncière, régimes de location : optimiser sa situation fiscale légalement.',
        'pages'       => '14 pages',
        'icon'        => '📊',
        'tag'         => 'Fiscalité',
        'color_from'  => '#059669',
        'color_to'    => '#34d399',
        'color_light' => '#ecfdf5',
        'chapitres'   => [
            'Impôt sur les plus-values : calcul et exonérations',
            'Taxe foncière : évolution et recours',
            'Location nue vs location meublée (LMNP)',
            'La SCI : avantages et inconvénients',
            'Déficit foncier : comment en profiter',
            'Optimisation légale de votre situation',
        ],
        'promesse'    => 'Optimisez votre fiscalité immobilière légalement.',
        'extrait'     => 'En LMNP, vous pouvez amortir le bien et réduire votre base imposable de 60 à 80%. Une stratégie bien construite peut diviser par 3 votre imposition locative.',
    ],
    'guide-proprio-location' => [
        'persona'     => 'proprietaire',
        'name'        => 'Louer son bien sans stress',
        'description' => 'Sélection des locataires, rédaction du bail, état des lieux et gestion des impayés pas à pas.',
        'pages'       => '16 pages',
        'icon'        => '🔑',
        'tag'         => 'Location',
        'color_from'  => '#059669',
        'color_to'    => '#34d399',
        'color_light' => '#ecfdf5',
        'chapitres'   => [
            'Sélectionner le bon locataire (sans discriminer)',
            'Fixer le loyer au bon niveau',
            'Rédiger un bail solide',
            'État des lieux d\'entrée : les bonnes pratiques',
            'Gestion des impayés : procédure pas à pas',
            'Sortie du locataire et état des lieux de sortie',
        ],
        'promesse'    => 'Louez sereinement, protégez votre investissement.',
        'extrait'     => 'Un état des lieux réalisé avec soin vous protège juridiquement. Prenez des photos datées de chaque pièce et faites signer les deux parties sur place.',
    ],
    'guide-proprio-travaux' => [
        'persona'     => 'proprietaire',
        'name'        => 'Valoriser son patrimoine par les travaux',
        'description' => 'Quels travaux font vraiment monter la valeur de revente ? ROI moyen par type de rénovation.',
        'pages'       => '10 pages',
        'icon'        => '🔨',
        'tag'         => 'Travaux',
        'color_from'  => '#059669',
        'color_to'    => '#34d399',
        'color_light' => '#ecfdf5',
        'chapitres'   => [
            'Quels travaux augmentent vraiment la valeur',
            'ROI moyen par type de rénovation',
            'Rénovation énergétique et DPE : impact prix',
            'Cuisine et salle de bain : les incontournables',
            'Aides et subventions disponibles (MaPrimeRénov\')',
            'Faire appel à un architecte : quand et pourquoi',
        ],
        'promesse'    => 'Investissez là où ça rapporte vraiment.',
        'extrait'     => 'Un passage de DPE G à DPE C peut augmenter la valeur d\'un bien de 15 à 25%. C\'est le levier de valorisation le plus puissant aujourd\'hui.',
    ],
    'guide-proprio-investissement' => [
        'persona'     => 'proprietaire',
        'name'        => 'Investir dans l\'immobilier locatif à Bordeaux',
        'description' => 'Zones à fort rendement, calcul de la rentabilité nette, montages LMNP et SCI expliqués simplement.',
        'pages'       => '20 pages',
        'icon'        => '📈',
        'tag'         => 'Investissement',
        'color_from'  => '#059669',
        'color_to'    => '#34d399',
        'color_light' => '#ecfdf5',
        'chapitres'   => [
            'Pourquoi Bordeaux reste attractive pour investir',
            'Calcul de la rentabilité brute et nette',
            'Les zones à fort potentiel locatif',
            'LMNP vs SCI : quel montage choisir',
            'Financer son investissement locatif',
            'Les erreurs classiques de l\'investisseur débutant',
        ],
        'promesse'    => 'Investissez intelligemment à Bordeaux.',
        'extrait'     => 'La rentabilité locative nette à Bordeaux se situe entre 3,5% et 6% selon les secteurs. Les studios en hypercentre offrent la meilleure liquidité.',
    ],
];

// ── Résoudre le slug ──
$slug = $_GET['slug'] ?? '';
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower(trim($slug)));

if (!$slug || !isset($guides_catalog[$slug])) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

$guide = $guides_catalog[$slug];
$cf    = $guide['color_from'];
$ct    = $guide['color_to'];
$cl    = $guide['color_light'];

// ── Traitement soumission formulaire ──
$form_success = false;
$form_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $phone  = trim($_POST['phone']  ?? '');

    if (!$prenom || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = 'Prénom et email valide requis.';
    } else {
        try {
            $db  = Database::getInstance()->getConnection();
            $sql = "INSERT INTO leads (prenom, email, telephone, source, notes, statut, created_at)
                    VALUES (:prenom, :email, :phone, :source, :notes, 'nouveau', NOW())
                    ON DUPLICATE KEY UPDATE
                        prenom     = VALUES(prenom),
                        telephone  = VALUES(telephone),
                        source     = VALUES(source),
                        notes      = CONCAT(IFNULL(notes,''), '\n', VALUES(notes)),
                        updated_at = NOW()";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':prenom'  => $prenom,
                ':email'   => $email,
                ':phone'   => $phone,
                ':source'  => 'capture_guide',
                ':notes'   => 'Téléchargement guide : ' . $guide['name'] . ' (' . $slug . ')',
            ]);
            // Redirection page merci
            header('Location: /merci?guide=' . urlencode($slug) . '&prenom=' . urlencode($prenom));
            exit;
        } catch (Exception $e) {
            $form_error = 'Une erreur est survenue. Merci de réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($guide['name']) ?> — Guide Gratuit | Eduardo De Sul Immobilier</title>
<meta name="description" content="<?= htmlspecialchars($guide['description']) ?> Téléchargez gratuitement ce guide PDF de <?= $guide['pages'] ?>.">
<meta name="robots" content="noindex, nofollow">

<!-- Fonts Eduardo -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --primary:  #1a4d7a;
    --gold:     #d4a574;
    --bg:       #f9f6f3;
    --cf:       <?= $cf ?>;
    --ct:       <?= $ct ?>;
    --cl:       <?= $cl ?>;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: #1e293b;
    min-height: 100vh;
}

/* ── HEADER MINIMAL ── */
.cap-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.cap-logo {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
}
.cap-logo span { color: var(--gold); }
.cap-header-cta {
    font-size: 13px;
    color: #64748b;
}
.cap-header-cta a { color: var(--primary); font-weight: 600; text-decoration: none; }
.cap-header-cta a:hover { text-decoration: underline; }

/* ── LAYOUT PRINCIPAL ── */
.cap-main {
    max-width: 1100px;
    margin: 0 auto;
    padding: 48px 24px 80px;
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 48px;
    align-items: start;
}
@media (max-width: 860px) {
    .cap-main { grid-template-columns: 1fr; gap: 32px; }
}

/* ── COLONNE GAUCHE : présentation guide ── */
.cap-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--cl); color: var(--cf);
    font-size: 12px; font-weight: 800;
    padding: 6px 14px; border-radius: 20px;
    margin-bottom: 20px; text-transform: uppercase; letter-spacing: .5px;
}
.cap-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(28px, 4vw, 40px);
    font-weight: 800;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 16px;
}
.cap-promesse {
    font-size: 18px;
    color: var(--cf);
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.4;
}
.cap-desc {
    font-size: 15px;
    color: #475569;
    line-height: 1.7;
    margin-bottom: 32px;
}

/* Guide cover card */
.cap-cover {
    background: linear-gradient(135deg, var(--cf), var(--ct));
    border-radius: 16px;
    padding: 36px 32px;
    color: white;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    gap: 24px;
}
.cap-cover-icon { font-size: 72px; flex-shrink: 0; }
.cap-cover-info h2 { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; margin-bottom: 8px; line-height: 1.3; }
.cap-cover-meta { display: flex; gap: 16px; flex-wrap: wrap; }
.cap-cover-meta span { font-size: 13px; opacity: .85; font-weight: 600; background: rgba(255,255,255,.15); padding: 4px 12px; border-radius: 20px; }

/* Sommaire */
.cap-sommaire { margin-bottom: 32px; }
.cap-sommaire h3 { font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.cap-sommaire ul { list-style: none; }
.cap-sommaire li {
    padding: 11px 16px;
    background: white;
    border-radius: 9px;
    margin-bottom: 6px;
    font-size: 14px;
    color: #374151;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid #f1f5f9;
    transition: .15s;
}
.cap-sommaire li:hover { border-color: var(--cf); background: var(--cl); }
.cap-sommaire li::before {
    content: '';
    width: 8px; height: 8px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--cf), var(--ct));
    flex-shrink: 0;
}

/* Extrait */
.cap-extrait {
    background: var(--cl);
    border-left: 4px solid var(--cf);
    border-radius: 0 10px 10px 0;
    padding: 18px 20px;
    font-size: 14px;
    color: #374151;
    line-height: 1.7;
    font-style: italic;
}

/* Preuves sociales */
.cap-social-proof {
    display: flex;
    gap: 20px;
    margin-top: 28px;
    flex-wrap: wrap;
}
.cap-proof-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}
.cap-proof-icon { font-size: 20px; }

/* ── COLONNE DROITE : formulaire ── */
.cap-form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,.1);
    overflow: hidden;
    position: sticky;
    top: 24px;
}
.cap-form-header {
    background: linear-gradient(135deg, var(--cf), var(--ct));
    padding: 28px 28px 24px;
    color: white;
    text-align: center;
}
.cap-form-header .fh-icon { font-size: 42px; margin-bottom: 10px; }
.cap-form-header h3 { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; margin-bottom: 6px; line-height: 1.3; }
.cap-form-header p { font-size: 13px; opacity: .85; }
.cap-form-free {
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
    margin-top: 10px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.cap-form-body { padding: 28px; }

.cap-fg { margin-bottom: 18px; }
.cap-fg label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.cap-fg input {
    width: 100%;
    padding: 13px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: #1e293b;
    transition: .15s;
    background: #fafafa;
}
.cap-fg input:focus {
    outline: none;
    border-color: var(--cf);
    background: white;
    box-shadow: 0 0 0 3px var(--cl);
}
.cap-fg input::placeholder { color: #94a3b8; }

.cap-submit {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, var(--cf), var(--ct));
    color: white;
    border: none;
    border-radius: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: .2s;
    margin-top: 4px;
}
.cap-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.2); }
.cap-submit:active { transform: translateY(0); }

.cap-legal {
    font-size: 11px;
    color: #94a3b8;
    text-align: center;
    margin-top: 14px;
    line-height: 1.6;
}

/* Garanties sous le form */
.cap-garanties {
    border-top: 1px solid #f1f5f9;
    padding: 16px 28px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.cap-garantie {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.cap-garantie-icon { font-size: 16px; flex-shrink: 0; }

/* Erreur form */
.cap-error {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #dc2626;
    padding: 12px 16px;
    border-radius: 9px;
    font-size: 13px;
    margin-bottom: 16px;
    font-weight: 600;
}

/* ── FOOTER MINIMAL ── */
.cap-footer {
    text-align: center;
    padding: 24px;
    font-size: 12px;
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    background: white;
}
.cap-footer a { color: #64748b; text-decoration: none; }
.cap-footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<!-- Header -->
<header class="cap-header">
    <a href="/" class="cap-logo">Eduardo De Sul<span>.</span></a>
    <div class="cap-header-cta">
        Questions ? <a href="tel:0624105816">06 24 10 58 16</a>
    </div>
</header>

<!-- Main -->
<main class="cap-main">

    <!-- ── Colonne gauche : présentation ── -->
    <div class="cap-left">

        <div class="cap-badge">
            <?= $guide['icon'] ?> <?= htmlspecialchars($guide['tag']) ?>
        </div>

        <h1 class="cap-title"><?= htmlspecialchars($guide['name']) ?></h1>
        <p class="cap-promesse"><?= htmlspecialchars($guide['promesse']) ?></p>
        <p class="cap-desc"><?= htmlspecialchars($guide['description']) ?></p>

        <!-- Cover du guide -->
        <div class="cap-cover">
            <div class="cap-cover-icon"><?= $guide['icon'] ?></div>
            <div class="cap-cover-info">
                <h2><?= htmlspecialchars($guide['name']) ?></h2>
                <div class="cap-cover-meta">
                    <span>📄 <?= $guide['pages'] ?></span>
                    <span>📥 PDF Gratuit</span>
                    <span>✅ Disponible immédiatement</span>
                </div>
            </div>
        </div>

        <!-- Sommaire -->
        <div class="cap-sommaire">
            <h3>📋 Ce que vous allez découvrir</h3>
            <ul>
                <?php foreach ($guide['chapitres'] as $chapitre): ?>
                <li><?= htmlspecialchars($chapitre) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Extrait -->
        <blockquote class="cap-extrait">
            « <?= htmlspecialchars($guide['extrait']) ?> »
        </blockquote>

        <!-- Preuves sociales -->
        <div class="cap-social-proof">
            <div class="cap-proof-item">
                <span class="cap-proof-icon">👥</span>
                <span>+200 téléchargements</span>
            </div>
            <div class="cap-proof-item">
                <span class="cap-proof-icon">⭐</span>
                <span>4,9/5 de satisfaction</span>
            </div>
            <div class="cap-proof-item">
                <span class="cap-proof-icon">🔒</span>
                <span>100% gratuit, sans engagement</span>
            </div>
        </div>

    </div>

    <!-- ── Colonne droite : formulaire ── -->
    <div class="cap-right">
        <div class="cap-form-card">
            <div class="cap-form-header">
                <div class="fh-icon"><?= $guide['icon'] ?></div>
                <h3>Recevoir ce guide<br>gratuitement</h3>
                <p><?= htmlspecialchars($guide['pages']) ?> · PDF · Envoi immédiat</p>
                <div class="cap-form-free">100% Gratuit</div>
            </div>
            <div class="cap-form-body">
                <?php if ($form_error): ?>
                <div class="cap-error">⚠️ <?= htmlspecialchars($form_error) ?></div>
                <?php endif; ?>
                <form method="POST" action="/capture/<?= htmlspecialchars($slug) ?>">
                    <div class="cap-fg">
                        <label for="prenom">Votre prénom *</label>
                        <input type="text" id="prenom" name="prenom"
                               placeholder="Ex : Marie"
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                               required autocomplete="given-name">
                    </div>
                    <div class="cap-fg">
                        <label for="email">Votre email *</label>
                        <input type="email" id="email" name="email"
                               placeholder="marie@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autocomplete="email">
                    </div>
                    <div class="cap-fg">
                        <label for="phone">Téléphone <span style="color:#94a3b8;font-weight:400">(optionnel)</span></label>
                        <input type="tel" id="phone" name="phone"
                               placeholder="06 XX XX XX XX"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               autocomplete="tel">
                    </div>
                    <button type="submit" class="cap-submit">
                        📥 Recevoir mon guide gratuitement →
                    </button>
                    <p class="cap-legal">
                        Vos données sont utilisées uniquement pour vous envoyer ce guide.<br>
                        Aucun spam. Désabonnement en 1 clic à tout moment.
                    </p>
                </form>
            </div>
            <div class="cap-garanties">
                <div class="cap-garantie">
                    <span class="cap-garantie-icon">🔒</span>
                    <span>Données sécurisées — jamais revendues</span>
                </div>
                <div class="cap-garantie">
                    <span class="cap-garantie-icon">📩</span>
                    <span>Guide reçu dans votre boîte email en quelques minutes</span>
                </div>
                <div class="cap-garantie">
                    <span class="cap-garantie-icon">🎯</span>
                    <span>Rédigé par Eduardo De Sul, expert immobilier Bordeaux</span>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Footer -->
<footer class="cap-footer">
    <p>© <?= date('Y') ?> Eduardo De Sul Immobilier · Blanquefort / Bordeaux</p>
    <p style="margin-top:6px;">
        <a href="/mentions-legales">Mentions légales</a> ·
        <a href="/politique-de-confidentialite">Confidentialité</a> ·
        <a href="/">Retour au site</a>
    </p>
</footer>

</body>
</html>