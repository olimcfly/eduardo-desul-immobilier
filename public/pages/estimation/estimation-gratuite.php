<?php
// Bootstrap déjà chargé par le router (index.php)
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../../core/bootstrap.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $typeBien = trim($_POST['type_bien'] ?? '');
    $surface  = trim($_POST['surface']   ?? '');
    $localite = trim($_POST['localite']  ?? '');
    $budget   = trim($_POST['budget']    ?? '');
    $projet   = trim($_POST['projet']    ?? '');
    $lat      = trim($_POST['lat']       ?? '');
    $lng      = trim($_POST['lng']       ?? '');

    if ($typeBien && $surface && $localite && $projet) {

        // ── Création de la table estimation_zones si besoin ────────
        db()->exec("CREATE TABLE IF NOT EXISTS estimation_zones (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type_bien  VARCHAR(40)   NOT NULL DEFAULT '',
            surface    VARCHAR(20)   NOT NULL DEFAULT '',
            localite   VARCHAR(255)  NOT NULL DEFAULT '',
            budget     VARCHAR(50)   NULL,
            projet     VARCHAR(50)   NOT NULL DEFAULT '',
            lat        DECIMAL(10,7) NULL,
            lng        DECIMAL(10,7) NULL,
            ip         VARCHAR(45)   NOT NULL DEFAULT '',
            created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ── Capture anonyme ────────────────────────────────────────
        db()->prepare("
            INSERT INTO estimation_zones
                (type_bien, surface, localite, budget, projet, lat, lng, ip, created_at)
            VALUES
                (:type_bien, :surface, :localite, :budget, :projet, :lat, :lng, :ip, NOW())
        ")->execute([
            ':type_bien' => $typeBien,
            ':surface'   => $surface,
            ':localite'  => $localite,
            ':budget'    => $budget ?: null,
            ':projet'    => $projet,
            ':lat'       => $lat !== '' ? $lat : null,
            ':lng'       => $lng !== '' ? $lng : null,
            ':ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $zoneId = db()->lastInsertId();

        // ── Extraction de la ville depuis le champ localite ────────
        // Accepte : "Bordeaux", "13100 Bordeaux", "Bordeaux 13100"
        $cityForEst = '';
        if (preg_match('/([a-zA-ZÀ-ÖØ-öø-ÿ][a-zA-ZÀ-ÖØ-öø-ÿ\s\-\']{2,})/u', $localite, $m)) {
            $cityForEst = trim($m[0]);
        }

        // ── Calcul via DvfEstimatorService ────────────────────────
        DvfEstimatorService::ensureTables();

        $dvfResult = DvfEstimatorService::estimate([
            'property_type' => $typeBien,
            'surface'       => (float) $surface,
            'lat'           => $lat !== '' ? (float) $lat : null,
            'lng'           => $lng !== '' ? (float) $lng : null,
            'city'          => $cityForEst,
        ]);

        $fourchette = null;
        if ($dvfResult['ok']) {
            $fourchette = [
                'min'        => number_format((int) $dvfResult['estimate_low'],    0, ',', ' '),
                'moy'        => number_format((int) $dvfResult['estimate_median'], 0, ',', ' '),
                'max'        => number_format((int) $dvfResult['estimate_high'],   0, ',', ' '),
                'pm2'        => number_format((int) $dvfResult['price_m2_median'], 0, ',', ' '),
                'nb'         => $dvfResult['comparables_count'],
                'confidence' => $dvfResult['confidence_level'] ?? '',
            ];
        }

        // ── Comparables DVF (colonnes réelles du service) ─────────
        $comps = [];
        try {
            $hasLoc = $lat !== '' && $lng !== '';
            // Utilise des paramètres positionnels (?) — PDO::ATTR_EMULATE_PREPARES=false
            // interdit la répétition des paramètres nommés dans une même requête.
            $compParams = [];

            if ($hasLoc) {
                $compSql = "
                    SELECT
                        address_label AS adresse,
                        surface,
                        value_amount  AS prix,
                        price_m2      AS prix_m2,
                        mutation_date AS date_vente
                    FROM dvf_transactions
                    WHERE property_type = ?
                      AND mutation_date  >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
                      AND price_m2 > 100
                      AND latitude IS NOT NULL
                    ORDER BY ((latitude - ?)*(latitude - ?) + (longitude - ?)*(longitude - ?)) ASC,
                             ABS(surface - ?) ASC
                    LIMIT 5";
                $compParams = [$typeBien, (float)$lat, (float)$lat, (float)$lng, (float)$lng, (int)$surface];
            } elseif ($cityForEst !== '') {
                $compSql = "
                    SELECT
                        address_label AS adresse,
                        surface,
                        value_amount  AS prix,
                        price_m2      AS prix_m2,
                        mutation_date AS date_vente
                    FROM dvf_transactions
                    WHERE property_type = ?
                      AND mutation_date  >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
                      AND price_m2 > 100
                      AND city = ?
                    ORDER BY ABS(surface - ?) ASC
                    LIMIT 5";
                $compParams = [$typeBien, $cityForEst, (int)$surface];
            } else {
                $compSql = "
                    SELECT
                        address_label AS adresse,
                        surface,
                        value_amount  AS prix,
                        price_m2      AS prix_m2,
                        mutation_date AS date_vente
                    FROM dvf_transactions
                    WHERE property_type = ?
                      AND mutation_date  >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
                      AND price_m2 > 100
                    ORDER BY mutation_date DESC
                    LIMIT 5";
                $compParams = [$typeBien];
            }

            $compStmt = db()->prepare($compSql);
            $compStmt->execute($compParams);
            $comps = $compStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $comps = [];
        }

        // ── Stockage session ────────────────────────────────────────
        $_SESSION['estimation'] = [
            'zone_id'     => $zoneId,
            'type_bien'   => $typeBien,
            'surface'     => $surface,
            'localite'    => $localite,
            'budget'      => $budget,
            'projet'      => $projet,
            'fourchette'  => $fourchette,
            'comparables' => $comps,
            'city'        => $cityForEst,
        ];

        redirect('/estimation-gratuite/resultat');
    }
}

// ── Meta ────────────────────────────────────────────────────────────────────
$advisorNameStr  = defined('ADVISOR_NAME') ? ADVISOR_NAME : APP_NAME;
$appCity         = (defined('APP_CITY') && APP_CITY) ? APP_CITY : 'Bordeaux';
$pageTitle       = 'Estimation gratuite — ' . $advisorNameStr . ' | Expert Immobilier ' . $appCity;
$metaDesc        = 'Estimez votre bien immobilier gratuitement en 60 secondes. Fourchette basée sur les ventes DVF officielles. Sans inscription.';
$bodyClass       = 'lp-mode';
$lpMode          = true;
$extraCss        = ['/assets/css/estimation.css'];
$extraJs         = ['/assets/js/estimation.js'];
$metaRobots      = 'noindex, nofollow'; // page LP, pas besoin d'indexation

ob_start();
?>

<!-- ══ MINI-HEADER LP ════════════════════════════════════════════════════════ -->
<div class="lp-header">
    <div class="container lp-header__inner">
        <a href="/" class="lp-header__logo" aria-label="Accueil">
            <span class="logo-icon" aria-hidden="true">🏡</span>
            <strong><?= e($advisorNameStr) ?></strong>
        </a>
        <?php if (defined('APP_PHONE') && APP_PHONE): ?>
        <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"
           class="lp-header__phone">
            📞 <?= e(APP_PHONE) ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ══ HERO ══════════════════════════════════════════════════════════════════ -->
<section class="lp-hero">
    <div class="container">
        <div class="lp-hero__content">
            <h1 class="lp-hero__title">
                Combien vaut votre bien<br>
                <span>à <?= e($appCity) ?> ?</span>
            </h1>
            <p class="lp-hero__sub">
                Fourchette indicative · Données DVF officielles · 60 secondes · Sans inscription
            </p>
            <div class="lp-trust-strip">
                <span class="lp-trust-item">
                    <span aria-hidden="true">✅</span> Gratuit &amp; immédiat
                </span>
                <span class="lp-trust-item">
                    <span aria-hidden="true">🔒</span> Aucune donnée personnelle requise
                </span>
                <span class="lp-trust-item">
                    <span aria-hidden="true">📊</span> Basé sur les ventes réelles
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ══ SECTION PRINCIPALE ════════════════════════════════════════════════════ -->
<section class="section section--estimation">
    <div class="container">
        <div class="estimation-layout">

            <!-- ── Formulaire ─────────────────────────────────────────────── -->
            <div class="estimation-form-wrap">

                <div class="estimation-disclaimer" role="note">
                    <span class="disclaimer-icon" aria-hidden="true">ℹ️</span>
                    <div>
                        <strong>Estimation indicative</strong> — basée sur les données DVF
                        (Demandes de Valeurs Foncières). Ne constitue pas une expertise officielle.
                    </div>
                </div>

                <form id="form-estimation"
                      action="/estimation-gratuite"
                      method="POST"
                      novalidate>
                    <?= csrfField() ?>

                    <input type="hidden" name="lat" id="geo-lat">
                    <input type="hidden" name="lng" id="geo-lng">

                    <!-- ── Étape 1 : Type de bien ──────────────────────── -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="form-step-num" aria-hidden="true">1</span>
                            Quel type de bien ?
                        </h3>
                        <div class="type-grid" role="group" aria-label="Type de bien">
                            <?php
                            $types = [
                                'appartement' => ['🏢', 'Appartement'],
                                'maison'      => ['🏠', 'Maison'],
                                'villa'       => ['🏡', 'Villa'],
                                'terrain'     => ['🌿', 'Terrain'],
                                'local'       => ['🏪', 'Local commercial'],
                                'immeuble'    => ['🏬', 'Immeuble'],
                            ];
                            foreach ($types as $val => [$icon, $label]): ?>
                            <label class="type-card" data-type="<?= $val ?>">
                                <input type="radio"
                                       name="type_bien"
                                       value="<?= $val ?>"
                                       required
                                       class="sr-only">
                                <span class="type-card__icon" aria-hidden="true"><?= $icon ?></span>
                                <span class="type-card__label"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="form-error" id="err-type" hidden>Veuillez sélectionner un type de bien.</p>
                    </div>

                    <!-- ── Étape 2 : Surface + Localité ───────────────── -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="form-step-num" aria-hidden="true">2</span>
                            Surface &amp; localisation
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="est-surface">
                                    Surface habitable <span class="required-star" aria-hidden="true">*</span>
                                </label>
                                <div class="input-with-unit">
                                    <input type="number"
                                           id="est-surface"
                                           name="surface"
                                           class="form-control"
                                           placeholder="Ex : 85"
                                           min="5"
                                           max="2000"
                                           required>
                                    <span class="input-unit">m²</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="est-localite">
                                    Ville ou code postal <span class="required-star" aria-hidden="true">*</span>
                                </label>
                                <div class="input-with-icon" style="position:relative">
                                    <input type="text"
                                           id="est-localite"
                                           name="localite"
                                           class="form-control"
                                           placeholder="Ex : Bordeaux, 33000…"
                                           autocomplete="off"
                                           required>
                                    <span class="input-icon" aria-hidden="true">📍</span>
                                </div>
                                <ul id="localite-suggestions" class="autocomplete-list" hidden aria-live="polite"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- ── Étape 3 : Projet + Budget estimé ───────────── -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="form-step-num" aria-hidden="true">3</span>
                            Votre projet
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="est-budget">
                                    Combien pensez-vous que vaut votre bien ?
                                </label>
                                <div class="input-with-unit">
                                    <input type="number"
                                           id="est-budget"
                                           name="budget"
                                           class="form-control"
                                           placeholder="Ex : 350 000"
                                           min="0">
                                    <span class="input-unit">€</span>
                                </div>
                                <small class="form-hint">Optionnel · sera comparé aux prix du marché</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    Votre projet <span class="required-star" aria-hidden="true">*</span>
                                </label>
                                <div class="projet-toggle" role="group" aria-label="Type de projet">
                                    <label class="projet-btn">
                                        <input type="radio" name="projet" value="vendre"   class="sr-only" required>
                                        <span>🏷️ Vendre</span>
                                    </label>
                                    <label class="projet-btn">
                                        <input type="radio" name="projet" value="acheter"  class="sr-only">
                                        <span>🔑 Acheter</span>
                                    </label>
                                    <label class="projet-btn">
                                        <input type="radio" name="projet" value="les_deux" class="sr-only">
                                        <span>🔄 Les deux</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Submit ──────────────────────────────────────── -->
                    <div class="form-submit-wrap">
                        <button type="submit"
                                class="btn btn--accent btn--lg btn--full btn--submit-estimation"
                                id="btn-submit-estimation">
                            <span class="btn-text">Obtenir mon estimation gratuite</span>
                            <span class="btn-loader" hidden aria-hidden="true">Calcul en cours…</span>
                            <span class="btn-icon" aria-hidden="true">→</span>
                        </button>
                        <p class="form-submit-hint">
                            🔒 Aucun email ni téléphone requis · Résultat en quelques secondes
                        </p>
                    </div>

                </form>
            </div>

            <!-- ── Sidebar ─────────────────────────────────────────────────── -->
            <aside class="estimation-sidebar" aria-label="Ce que vous obtiendrez">

                <div class="sidebar-card sidebar-card--what">
                    <h3>📊 Ce que vous obtiendrez</h3>
                    <ul class="what-list">
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Fourchette basse / médiane / haute</span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Prix au m² moyen <strong>dans votre secteur</strong></span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Transactions réelles des <strong>12–24 derniers mois</strong></span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Comparaison avec <strong>votre propre estimation</strong></span>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-card sidebar-card--warning">
                    <h3>⚖️ Expertise officielle</h3>
                    <p>
                        Divorce, succession, prêt bancaire ?
                        Une <strong>expertise certifiée par un professionnel agréé</strong> peut être obligatoire.
                    </p>
                    <a href="/contact" class="btn btn--outline btn--sm btn--full">
                        Demander une expertise officielle →
                    </a>
                </div>

                <div class="sidebar-card sidebar-card--advisor">
                    <div class="advisor-mini">
                        <div class="advisor-mini__avatar" aria-hidden="true">👤</div>
                        <div class="advisor-mini__info">
                            <strong><?= e($advisorNameStr) ?></strong>
                            <span>Expert immobilier 360° — <?= e($appCity) ?></span>
                        </div>
                    </div>
                    <p class="advisor-mini__quote">
                        « La seule vraie estimation est celle négociée entre
                        un acheteur et un vendeur. Je vous accompagne. »
                    </p>
                </div>

            </aside>
        </div>
    </div>
</section>

<!-- ══ LP FOOTER MINIMAL ═════════════════════════════════════════════════════ -->
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
