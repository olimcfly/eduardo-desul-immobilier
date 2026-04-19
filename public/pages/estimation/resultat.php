<?php
require_once __DIR__ . '/../../../core/bootstrap.php';

// ── Récupération session ──────────────────────────────────────────────────
$est = $_SESSION['estimation'] ?? null;
if (!$est) {
    redirect('/estimation-gratuite');
}

$fourchette  = $est['fourchette']  ?? null;
$comparables = $est['comparables'] ?? [];
$typeBien    = $est['type_bien']   ?? '';
$surface     = $est['surface']     ?? '';
$localite    = $est['localite']    ?? '';
$budget      = $est['budget']      ?? '';
$projet      = $est['projet']      ?? '';

$typeLabels = [
    'appartement' => 'Appartement',
    'maison'      => 'Maison',
    'villa'       => 'Villa',
    'terrain'     => 'Terrain',
    'local'       => 'Local commercial',
    'immeuble'    => 'Immeuble',
];

$advisorNameStr = defined('ADVISOR_NAME') ? ADVISOR_NAME : APP_NAME;

// ── Formulaire qualification (POST) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email     = trim($_POST['email']     ?? '');
    $prenom    = trim($_POST['prenom']    ?? '');
    $nom       = trim($_POST['nom']       ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($email && $prenom && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $leadId = LeadService::capture([
            'source_type' => LeadService::SOURCE_ESTIMATION,
            'pipeline'    => LeadService::SOURCE_ESTIMATION,
            'stage'       => 'qualifie',
            'first_name'  => $prenom,
            'last_name'   => $nom,
            'email'       => $email,
            'phone'       => $telephone,
            'intent'      => 'Estimation + RDV',
            'consent'     => !empty($_POST['rgpd']),
            'metadata'    => [
                'zone_id'        => $est['zone_id']    ?? null,
                'type_bien'      => $typeBien,
                'surface'        => $surface,
                'localite'       => $localite,
                'budget_client'  => $budget,
                'projet'         => $projet,
                'estimation_min' => $fourchette['min'] ?? null,
                'estimation_moy' => $fourchette['moy'] ?? null,
                'estimation_max' => $fourchette['max'] ?? null,
                'delai'          => trim($_POST['delai'] ?? ''),
                'urgence'        => trim($_POST['urgence'] ?? ''),
            ],
        ]);

        // ── Notification email ────────────────────────────────────
        $fourchetteStr = $fourchette
            ? "{$fourchette['min']} € — {$fourchette['moy']} € — {$fourchette['max']} €"
            : 'Données DVF insuffisantes';

        $budgetStr = $budget ? number_format((int)$budget, 0, ',', ' ') . ' €' : 'Non renseigné';

        $txtBody = "Nouveau lead estimation — {$prenom} {$nom}\n\n"
            . "Email    : {$email}\n"
            . "Téléphone: {$telephone}\n\n"
            . "Bien     : " . ($typeLabels[$typeBien] ?? $typeBien) . "\n"
            . "Surface  : {$surface} m²\n"
            . "Localité : {$localite}\n"
            . "Projet   : {$projet}\n"
            . "Budget propriétaire : {$budgetStr}\n\n"
            . "Fourchette DVF : {$fourchetteStr}\n\n"
            . "Délai    : " . trim($_POST['delai'] ?? 'non renseigné') . "\n"
            . "Urgence  : " . trim($_POST['urgence'] ?? 'non renseigné') . "/5\n";

        $htmlBody = '<div style="font-family:sans-serif;max-width:600px">'
            . '<h2 style="color:#1a3c5e">Nouveau lead estimation</h2>'
            . '<table style="width:100%;border-collapse:collapse">'
            . '<tr><td style="padding:6px 0;color:#666">Prénom Nom</td><td><strong>' . htmlspecialchars("{$prenom} {$nom}") . '</strong></td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Email</td><td>' . htmlspecialchars($email) . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Téléphone</td><td>' . htmlspecialchars($telephone ?: '—') . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Bien</td><td>' . htmlspecialchars($typeLabels[$typeBien] ?? $typeBien) . ' · ' . htmlspecialchars($surface) . ' m²</td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Localité</td><td>' . htmlspecialchars($localite) . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Projet</td><td>' . htmlspecialchars($projet) . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Budget estimé</td><td>' . htmlspecialchars($budgetStr) . '</td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Fourchette DVF</td><td><strong>' . htmlspecialchars($fourchetteStr) . '</strong></td></tr>'
            . '<tr><td style="padding:6px 0;color:#666">Délai</td><td>' . htmlspecialchars(trim($_POST['delai'] ?? '—')) . '</td></tr>'
            . '</table>'
            . '<p style="margin-top:1.5rem"><a href="' . APP_URL . '/admin" style="background:#1a3c5e;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none">Voir dans le CRM</a></p>'
            . '</div>';

        $recipientEmail = defined('APP_EMAIL') && APP_EMAIL ? APP_EMAIL : '';
        if ($recipientEmail) {
            MailService::send(
                $recipientEmail,
                "Nouveau lead estimation — {$prenom} {$nom} ({$localite})",
                $txtBody,
                $htmlBody
            );
        }

        unset($_SESSION['estimation']);
        redirect('/merci-estimation');
    }
}

// ── Comparaison budget propriétaire vs marché ─────────────────────────────
$diffPct = null;
if ($budget && $fourchette) {
    $budgetVal = (int) str_replace([' ', '€', ','], '', $budget);
    $moyVal    = (int) str_replace([' ', '€', ','], '', $fourchette['moy']);
    if ($moyVal > 0) {
        $diffPct = round((($budgetVal - $moyVal) / $moyVal) * 100);
    }
}

$pageTitle = 'Votre estimation — ' . ($typeLabels[$typeBien] ?? 'Bien') . ' · ' . e($localite);
$metaDesc  = 'Résultat de votre estimation immobilière gratuite.';
$metaRobots = 'noindex, nofollow';
$bodyClass  = 'lp-mode';
$lpMode     = true;
$extraCss   = ['/assets/css/estimation.css', '/assets/css/estimation-resultat.css'];
$extraJs    = ['/assets/js/estimation-resultat.js'];

ob_start();
?>

<!-- ══ MINI-HEADER LP ════════════════════════════════════════════════════════ -->
<div class="lp-header">
    <div class="container lp-header__inner">
        <a href="/" class="lp-header__logo" aria-label="Accueil">
            <span class="logo-icon" aria-hidden="true">🏡</span>
            <strong><?= e($advisorNameStr) ?></strong>
        </a>
        <a href="/estimation-gratuite" class="lp-header__back">
            ← Nouvelle estimation
        </a>
    </div>
</div>

<!-- ══ DISCLAIMER COMPACT ══════════════════════════════════════════════════ -->
<div class="resultat-disclaimer-bar">
    <div class="container">
        <span>⚠️</span>
        <span>
            <strong>Estimation indicative, non contractuelle.</strong>
            Calculée sur les ventes DVF officielles.
            Seul un expert agréé peut établir une estimation certifiée (divorce, succession, prêt).
        </span>
    </div>
</div>

<!-- ══ RÉSULTAT PRINCIPAL ════════════════════════════════════════════════════ -->
<section class="section section--resultat">
    <div class="container">
        <div class="resultat-layout">

            <!-- ── Colonne principale ──────────────────────────────────────── -->
            <div class="resultat-main">

                <?php if ($fourchette): ?>

                <!-- Fourchette -->
                <div class="fourchette-card">
                    <div class="fourchette-header">
                        <h1 class="fourchette-title">Votre estimation</h1>
                        <span class="fourchette-badge">
                            Basée sur <?= e($fourchette['nb']) ?> vente<?= $fourchette['nb'] > 1 ? 's' : '' ?> récentes
                            <?= $fourchette['confidence'] ? ' · Fiabilité ' . e($fourchette['confidence']) : '' ?>
                        </span>
                    </div>
                    <div class="fourchette-range">
                        <div class="fourchette-bound fourchette-bound--min">
                            <span class="bound-label">Estimation basse</span>
                            <span class="bound-value"><?= e($fourchette['min']) ?> €</span>
                        </div>
                        <div class="fourchette-middle">
                            <span class="middle-label">Valeur médiane</span>
                            <span class="middle-value"><?= e($fourchette['moy']) ?> €</span>
                            <span class="middle-pm2"><?= e($fourchette['pm2']) ?> €/m²</span>
                        </div>
                        <div class="fourchette-bound fourchette-bound--max">
                            <span class="bound-label">Estimation haute</span>
                            <span class="bound-value"><?= e($fourchette['max']) ?> €</span>
                        </div>
                    </div>
                    <div class="fourchette-bar" aria-hidden="true">
                        <div class="fourchette-bar__fill"></div>
                    </div>
                </div>

                <?php else: ?>

                <!-- Pas de données DVF -->
                <div class="fourchette-card fourchette-card--nodata">
                    <h1>Données insuffisantes</h1>
                    <p>
                        Nous n'avons pas encore assez de ventes DVF dans ce secteur
                        pour générer une fourchette fiable.
                    </p>
                    <p>
                        <strong>
                            <?= e($advisorNameStr) ?> peut vous fournir une estimation
                            précise lors d'un rendez-vous gratuit.
                        </strong>
                    </p>
                </div>

                <?php endif; ?>

                <?php if ($budget && $diffPct !== null): ?>
                <!-- Comparaison budget propriétaire -->
                <div class="budget-comparison <?= $diffPct > 10 ? 'budget-comparison--high' : ($diffPct < -10 ? 'budget-comparison--low' : 'budget-comparison--ok') ?>">
                    <h2>📊 Votre estimation vs le marché</h2>
                    <p>
                        Vous estimez votre bien à
                        <strong><?= number_format((int)$budget, 0, ',', ' ') ?> €</strong>.
                        <?php if ($diffPct > 10): ?>
                            C'est <strong><?= abs($diffPct) ?>% au-dessus</strong> de la médiane de marché.
                            Un prix trop élevé allonge significativement les délais de vente.
                        <?php elseif ($diffPct < -10): ?>
                            C'est <strong><?= abs($diffPct) ?>% en dessous</strong> de la médiane de marché.
                            Votre bien pourrait se vendre rapidement, mais vous laissez peut-être de la valeur.
                        <?php else: ?>
                            Votre estimation est <strong>cohérente</strong> avec la médiane du marché.
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- ── Double CTA ──────────────────────────────────────────── -->
                <div class="resultat-cta-section">
                    <h2>Quelle est votre prochaine étape ?</h2>
                    <div class="resultat-cta-grid">

                        <div class="cta-card cta-card--primary">
                            <span class="cta-card__icon" aria-hidden="true">📅</span>
                            <h3>Obtenir un rapport détaillé</h3>
                            <p>
                                Prenez rendez-vous avec <?= e($advisorNameStr) ?>.
                                Visite, analyse et rapport personnalisé gratuit.
                            </p>
                            <button type="button"
                                    class="btn btn--accent btn--lg btn--full"
                                    id="openQualifForm"
                                    aria-haspopup="dialog">
                                Prendre rendez-vous gratuitement →
                            </button>
                            <small>Sans engagement · Réponse sous 24h</small>
                        </div>

                        <div class="cta-card cta-card--secondary">
                            <span class="cta-card__icon" aria-hidden="true">📞</span>
                            <h3>Prendre rendez-vous</h3>
                            <p>
                                Vous préférez en parler directement ?
                                Laissez vos coordonnées et <?= e($advisorNameStr) ?> vous rappelle.
                            </p>
                            <?php if (defined('APP_PHONE') && APP_PHONE): ?>
                            <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"
                               class="btn btn--outline btn--lg btn--full">
                                📞 <?= e(APP_PHONE) ?>
                            </a>
                            <?php else: ?>
                            <button type="button"
                                    class="btn btn--outline btn--lg btn--full"
                                    id="openQualifFormSecond"
                                    aria-haspopup="dialog">
                                Me faire rappeler →
                            </button>
                            <?php endif; ?>
                            <small>Gratuit · Sans obligation</small>
                        </div>

                    </div>
                </div>

                <?php if (!empty($comparables)): ?>
                <!-- Comparables DVF -->
                <details class="comparables-section">
                    <summary>
                        🏠 Voir les <?= count($comparables) ?> ventes comparables récentes
                    </summary>
                    <p class="comparables-intro">
                        Biens similaires vendus dans votre secteur (source : DVF officielle).
                    </p>
                    <div class="comparables-table-wrap">
                        <table class="comparables-table">
                            <thead>
                                <tr>
                                    <th>Adresse</th>
                                    <th>Surface</th>
                                    <th>Prix vendu</th>
                                    <th>€/m²</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comparables as $comp): ?>
                                <tr>
                                    <td><?= e($comp['adresse'] ?? '—') ?></td>
                                    <td><?= e($comp['surface']) ?> m²</td>
                                    <td><?= number_format((int)$comp['prix'], 0, ',', ' ') ?> €</td>
                                    <td><?= number_format((int)$comp['prix_m2'], 0, ',', ' ') ?> €</td>
                                    <td><?= date('m/Y', strtotime($comp['date_vente'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <small class="comparables-source">Source : DVF — Données publiques</small>
                </details>
                <?php endif; ?>

            </div>

            <!-- ── Sidebar résultat ────────────────────────────────────────── -->
            <aside class="resultat-sidebar" aria-label="Récapitulatif">

                <div class="sidebar-card sidebar-card--recap">
                    <h3>📋 Votre bien</h3>
                    <ul class="recap-list">
                        <li>
                            <span class="recap-label">Type</span>
                            <span class="recap-value"><?= e($typeLabels[$typeBien] ?? $typeBien) ?></span>
                        </li>
                        <li>
                            <span class="recap-label">Surface</span>
                            <span class="recap-value"><?= e($surface) ?> m²</span>
                        </li>
                        <li>
                            <span class="recap-label">Localité</span>
                            <span class="recap-value"><?= e($localite) ?></span>
                        </li>
                        <li>
                            <span class="recap-label">Projet</span>
                            <span class="recap-value"><?= ucfirst(e($projet)) ?></span>
                        </li>
                        <?php if ($budget): ?>
                        <li>
                            <span class="recap-label">Votre estimation</span>
                            <span class="recap-value"><?= number_format((int)$budget, 0, ',', ' ') ?> €</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <a href="/estimation-gratuite" class="btn btn--ghost btn--sm btn--full">
                        ← Recommencer
                    </a>
                </div>

                <div class="sidebar-card sidebar-card--advisor">
                    <div class="advisor-mini">
                        <div class="advisor-mini__avatar" aria-hidden="true">👤</div>
                        <div class="advisor-mini__info">
                            <strong><?= e($advisorNameStr) ?></strong>
                            <span>Expert immobilier 360°</span>
                        </div>
                    </div>
                    <p>Discutons de votre projet. Estimation précise et personnalisée.</p>
                    <button type="button"
                            class="btn btn--accent btn--sm btn--full"
                            id="openQualifFormSidebar"
                            aria-haspopup="dialog">
                        Prendre rendez-vous
                    </button>
                    <?php if (defined('APP_PHONE') && APP_PHONE): ?>
                    <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"
                       class="btn btn--outline btn--sm btn--full" style="margin-top:.5rem">
                        📞 <?= e(APP_PHONE) ?>
                    </a>
                    <?php endif; ?>
                </div>

            </aside>
        </div>
    </div>
</section>

<!-- ══ MODAL QUALIFICATION ══════════════════════════════════════════════════ -->
<div id="qualifModal"
     class="modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="qualifModalTitle"
     hidden>
    <div class="modal__backdrop" id="qualifModalBackdrop"></div>
    <div class="modal__dialog modal__dialog--lg">
        <div class="modal__header">
            <h2 id="qualifModalTitle">📅 Prendre rendez-vous</h2>
            <button type="button" class="modal__close" aria-label="Fermer">×</button>
        </div>
        <div class="modal__body">
            <p class="modal__intro">
                Quelques informations pour préparer notre rendez-vous.
            </p>

            <form id="form-qualification"
                  action="/estimation-gratuite/resultat"
                  method="POST"
                  novalidate>
                <?= csrfField() ?>

                <fieldset class="qualif-fieldset">
                    <legend>Vos coordonnées</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="q-prenom">
                                Prénom <span class="required-star">*</span>
                            </label>
                            <input type="text" id="q-prenom" name="prenom" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="q-nom">Nom</label>
                            <input type="text" id="q-nom" name="nom" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="q-email">
                                Email <span class="required-star">*</span>
                            </label>
                            <input type="email" id="q-email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="q-tel">Téléphone</label>
                            <input type="tel" id="q-tel" name="telephone" class="form-control">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="qualif-fieldset">
                    <legend>Votre projet</legend>

                    <div class="form-group">
                        <label class="form-label" for="q-delai">Dans quel délai ?</label>
                        <select id="q-delai" name="delai" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="immediate">Immédiatement (< 1 mois)</option>
                            <option value="court">Court terme (1–3 mois)</option>
                            <option value="moyen">Moyen terme (3–6 mois)</option>
                            <option value="long">Long terme (6–12 mois)</option>
                            <option value="reflexion">En réflexion (> 12 mois)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="urgence-slider">
                            Degré d'urgence :
                            <span id="urgence-display">3/5</span>
                        </label>
                        <input type="range"
                               id="urgence-slider"
                               name="urgence"
                               min="1" max="5" value="3"
                               class="urgence-slider">
                        <div class="urgence-labels" aria-hidden="true">
                            <span>Pas urgent</span>
                            <span>Très urgent</span>
                        </div>
                    </div>
                </fieldset>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="rgpd" required>
                        <span>
                            J'accepte la
                            <a href="/politique-confidentialite" target="_blank" rel="noopener">politique de confidentialité</a>.
                            <span class="required-star" aria-hidden="true">*</span>
                        </span>
                    </label>
                </div>

                <div class="form-submit-wrap">
                    <button type="submit" class="btn btn--accent btn--lg btn--full">
                        Confirmer mon rendez-vous →
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ══ LP FOOTER MINIMAL ══════════════════════════════════════════════════ -->
<div class="lp-footer">
    <div class="container">
        <p>
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> —
            <a href="/mentions-legales">Mentions légales</a> ·
            <a href="/politique-confidentialite">Confidentialité</a>
        </p>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../templates/layout.php';
?>
