<?php
/**
 * ESTIMATION TUNNEL — Page LP (sans header/footer global)
 * Route GET : /estimation
 * Conseiller : Eduardo Desul — Bordeaux Métropole (33)
 */
$pageTitle    = 'Estimation immobilière gratuite — Bordeaux Métropole (33) | Eduardo Desul';
$metaDesc     = 'Obtenez une fourchette de prix en 60 secondes. Basée sur les références de marché bordelais. Sans inscription, sans engagement.';
$extraCss     = ['/assets/css/estimation-tunnel.css'];
$extraJs      = ['/assets/js/estimation-tunnel.js'];
$layoutMode   = 'landing';
$googleApiKey = (string) setting('api_google_maps', defined('GOOGLE_MAPS_KEY') ? (string) GOOGLE_MAPS_KEY : '');

$_lpPhoneRaw     = trim((string) setting('advisor_phone', defined('APP_PHONE') ? APP_PHONE : ''));
$_lpPhoneHref    = preg_replace('/[\s\.\-\(\)]/', '', $_lpPhoneRaw);
$_lpPhoneDisplay = $_lpPhoneRaw;
$_lpFirst        = trim((string) setting('advisor_firstname', ''));
$_lpLast         = trim((string) setting('advisor_lastname', ''));
$_lpName         = trim($_lpFirst . ' ' . $_lpLast);
if ($_lpName === '') {
    $_lpName = defined('ADVISOR_NAME') && ADVISOR_NAME !== ''
        ? ADVISOR_NAME
        : (string) preg_replace('/\s+Immobilier\b.*/iu', '', (string) (APP_NAME ?? ''));
}
?>

<!-- ══ LP TOPBAR ═════════════════════════════════════════════════════════════ -->
<div class="lp-topbar">
    <div class="container">
        <div class="lp-topbar__inner">
            <div class="lp-topbar__brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span><?= e($_lpName !== '' ? $_lpName : 'Immobilier Bordeaux') ?></span>
                <span class="lp-topbar__sep" aria-hidden="true">·</span>
                <span class="lp-topbar__zone">Conseiller immobilier · Bordeaux Métropole</span>
            </div>
            <?php if ($_lpPhoneDisplay !== ''): ?>
            <a class="lp-topbar__phone" href="tel:<?= e($_lpPhoneHref) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                             A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.35
                             A2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81
                             a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 5.49 5.49l1.16-1.16
                             a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 14.92z"/>
                </svg>
                <?= e($_lpPhoneDisplay) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ TUNNEL ════════════════════════════════════════════════════════════════ -->
<section class="section section--tunnel" aria-label="Formulaire d'estimation">
    <div class="container">
        <div class="tunnel-wrap" id="tunnel-app">

            <!-- ── En-tête de la carte ───────────────────────────────────── -->
            <div class="tunnel-header">
                <h1 class="tunnel-header__title">
                    Combien vaut votre bien
                    <span class="tunnel-header__accent">à Bordeaux&nbsp;?</span>
                </h1>
                <p class="tunnel-header__sub">
                    Fourchette indicative · Données marché bordelais · 60 secondes · Sans inscription
                </p>
            </div>

            <!-- ── Progress bar ──────────────────────────────────────────── -->
            <div class="tunnel-progress" aria-hidden="true">
                <div class="tunnel-progress__bar"
                     id="tunnel-progress-bar"
                     style="width:33%"></div>
            </div>
            <div class="tunnel-steps-label"
                 aria-live="polite"
                 id="tunnel-step-label">Étape 1 / 3</div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 1 — Caractéristiques du bien                         -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step tunnel-step--active"
                 id="step-1"
                 aria-label="Étape 1 : Caractéristiques du bien">

                <h2 class="tunnel-step__title">Votre bien</h2>
                <p class="tunnel-step__sub">
                    Sélectionnez le type et renseignez les caractéristiques principales.
                </p>

                <!-- Type de bien -->
                <div class="form-section">
                    <label class="form-label">
                        Type de bien
                        <span class="required-star" aria-hidden="true">*</span>
                    </label>
                    <div class="type-grid" role="group" aria-label="Type de bien">
                        <?php foreach ([
                            ['appartement', '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="2" x2="9" y2="22"/><line x1="15" y1="2" x2="15" y2="22"/><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>', 'Appartement'],
                            ['maison',      '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>', 'Maison'],
                            ['villa',       '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/><line x1="12" y1="2" x2="12" y2="5"/></svg>', 'Villa'],
                            ['terrain',     '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 20h18M3 20l4-8 4 4 3-6 4 10"/></svg>', 'Terrain'],
                            ['local',       '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><rect x="8" y="14" width="8" height="8"/></svg>', 'Local'],
                            ['immeuble',    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="2" x2="9" y2="22"/><line x1="15" y1="2" x2="15" y2="22"/><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/><circle cx="12" cy="20" r="1" fill="currentColor"/></svg>', 'Immeuble'],
                        ] as [$val, $icon, $label]): ?>
                        <label class="type-card" data-type="<?= $val ?>">
                            <input type="radio"
                                   name="property_type"
                                   value="<?= $val ?>"
                                   class="sr-only"
                                   <?= $val === 'appartement' ? 'checked' : '' ?>>
                            <span class="type-card__icon" aria-hidden="true"><?= $icon ?></span>
                            <span class="type-card__label"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="field-error" id="err-type" role="alert" hidden></div>
                </div>

                <!-- Surface + Pièces -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="t-surface">
                            Surface habitable
                            <span class="required-star" aria-hidden="true">*</span>
                        </label>
                        <div class="input-with-unit">
                            <input type="number"
                                   id="t-surface"
                                   name="surface"
                                   class="form-control"
                                   placeholder="85"
                                   min="10"
                                   max="2000"
                                   inputmode="numeric">
                            <span class="input-unit">m²</span>
                        </div>
                        <div class="field-error" id="err-surface" role="alert" hidden></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="t-rooms">Nombre de pièces</label>
                        <select id="t-rooms" name="rooms" class="form-control">
                            <option value="">Non précisé</option>
                            <?php for ($i = 1; $i <= 9; $i++): ?>
                            <option value="<?= $i ?>">
                                <?= $i ?> pièce<?= $i > 1 ? 's' : '' ?>
                            </option>
                            <?php endfor; ?>
                            <option value="10">10+</option>
                        </select>
                    </div>
                </div>

                <!-- Source de données -->
                <div class="form-section">
                    <label class="form-label">Source d'analyse</label>
                    <div class="toggle-group" role="group" aria-label="Source d'analyse">
                        <label class="toggle-btn">
                            <input type="radio" name="valuation_mode" value="sold"
                                   class="sr-only" checked>
                            <span>Ventes récentes</span>
                        </label>
                        <label class="toggle-btn">
                            <input type="radio" name="valuation_mode" value="live"
                                   class="sr-only">
                            <span>Annonces actuelles</span>
                        </label>
                        <label class="toggle-btn">
                            <input type="radio" name="valuation_mode" value="both"
                                   class="sr-only">
                            <span>Les deux</span>
                        </label>
                    </div>
                    <small class="form-hint">
                        DVF = transactions officielles enregistrées ces 12 derniers mois.
                    </small>
                </div>

                <div class="tunnel-nav">
                    <button type="button" class="btn btn--accent btn--lg" id="btn-step1-next">
                        Continuer <span aria-hidden="true">→</span>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 2 — Localisation                                      -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step"
                 id="step-2"
                 aria-label="Étape 2 : Localisation"
                 hidden>

                <h2 class="tunnel-step__title">Localisation du bien</h2>
                <p class="tunnel-step__sub">
                    Saisissez l'adresse ou la ville pour affiner l'estimation.
                </p>

                <div class="form-group">
                    <label class="form-label" for="t-city">
                        Adresse ou ville
                        <span class="required-star" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="t-city"
                           name="ville"
                           class="form-control"
                           placeholder="Ex : Bordeaux, 15 cours de l'Intendance à Mérignac…"
                           autocomplete="off">
                    <div class="field-error" id="err-city" role="alert" hidden></div>
                </div>

                <div class="form-group" id="postal-group">
                    <label class="form-label" for="t-postal">Code postal</label>
                    <input type="text"
                           id="t-postal"
                           name="postal_code"
                           class="form-control"
                           placeholder="33000"
                           inputmode="numeric"
                           maxlength="5">
                    <div class="field-error" id="err-postal" role="alert" hidden></div>
                </div>

                <!-- Géolocalisation optionnelle -->
                <div class="geo-block">
                    <button type="button"
                            class="btn btn--outline btn--sm"
                            id="btn-geolocate">
                        <span id="geo-btn-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Utiliser ma position
                        </span>
                    </button>
                    <span class="geo-status" id="geo-status" aria-live="polite"></span>
                    <input type="hidden" name="lat" id="t-lat">
                    <input type="hidden" name="lng" id="t-lng">
                </div>

                <div class="tunnel-nav">
                    <button type="button" class="btn btn--outline" id="btn-step2-back">
                        ← Retour
                    </button>
                    <button type="button" class="btn btn--accent btn--lg" id="btn-step2-next">
                        Calculer l'estimation <span aria-hidden="true">→</span>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 3 — Résultats                                         -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step"
                 id="step-3"
                 aria-label="Étape 3 : Résultats"
                 hidden>

                <!-- État : calcul en cours -->
                <div id="result-loading" class="result-loading" aria-live="polite" hidden>
                    <div class="result-loading__spinner" aria-hidden="true"></div>
                    <p>Calcul en cours…</p>
                </div>

                <!-- État : succès -->
                <div id="result-ok" hidden>

                    <!-- Récap + badge fiabilité -->
                    <div class="result-recap-bar">
                        <span class="result-recap-bar__text" id="result-recap"></span>
                        <span class="result-recap-bar__badge" id="result-reliability"></span>
                    </div>

                    <!-- Zone prix dominante -->
                    <div class="price-showcase" aria-label="Fourchette d'estimation">
                        <div class="price-showcase__grid">
                            <div class="price-showcase__side">
                                <span class="price-showcase__side-label">Fourchette basse</span>
                                <span class="price-showcase__side-value" id="price-low">—</span>
                            </div>
                            <div class="price-showcase__center">
                                <span class="price-showcase__center-label">Valeur estimée ★</span>
                                <span class="price-showcase__center-value" id="price-med">—</span>
                            </div>
                            <div class="price-showcase__side">
                                <span class="price-showcase__side-label">Fourchette haute</span>
                                <span class="price-showcase__side-value" id="price-high">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Signaux de confiance -->
                    <div class="result-trust-signals" aria-hidden="true">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Transactions DVF officielles
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Bordeaux Métropole
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2.5" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            12 derniers mois
                        </span>
                    </div>

                    <!-- Mention légale compacte -->
                    <p class="result-legal-note">
                        Estimation indicative — ne constitue pas un avis de valeur
                        professionnel au sens légal.
                    </p>

                    <!-- CTA principal : Rendez-vous -->
                    <div class="result-cta-primary">
                        <div class="result-cta-primary__inner">
                            <div class="result-cta-primary__text">
                                <strong>Confirmez la valeur réelle de votre bien</strong>
                                <span>
                                    <?= e($_lpName !== '' ? $_lpName : 'Votre conseiller') ?>
                                    se déplace à Bordeaux et dans toute la métropole
                                    pour une estimation précise, sur place, sans engagement.
                                </span>
                            </div>
                            <button type="button"
                                    class="btn btn--accent btn--lg cta-trigger"
                                    data-action="rdv_request">
                                Prendre rendez-vous
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Séparateur "ou" -->
                    <div class="result-cta-divider" aria-hidden="true"><span>ou</span></div>

                    <!-- CTAs secondaires -->
                    <div class="result-cta-secondary">
                        <button type="button"
                                class="btn btn--outline cta-trigger result-cta-secondary__btn"
                                data-action="email_report">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4
                                         c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            Recevoir par email
                        </button>
                        <button type="button"
                                class="btn btn--outline cta-trigger result-cta-secondary__btn"
                                data-action="contact_request">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                                         19.79 19.79 0 0 1-8.63-3.07
                                         A19.5 19.5 0 0 1 4.69 12
                                         19.79 19.79 0 0 1 1.61 3.35
                                         A2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72
                                         12.84 12.84 0 0 0 .7 2.81
                                         a2 2 0 0 1-.45 2.11L7.91 8.6
                                         a16 16 0 0 0 5.49 5.49l1.16-1.16
                                         a2 2 0 0 1 2.11-.45
                                         12.84 12.84 0 0 0 2.81.7
                                         A2 2 0 0 1 22 14.92z"/>
                            </svg>
                            Être rappelé
                        </button>
                    </div>

                    <!-- Recommencer -->
                    <div class="result-restart">
                        <button type="button"
                                class="btn btn--ghost btn--sm"
                                id="btn-restart">
                            ↺ Faire une nouvelle estimation
                        </button>
                    </div>
                </div>

                <!-- État : données insuffisantes -->
                <div id="result-insufficient" hidden>
                    <div class="result-insufficient">
                        <div class="result-insufficient__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.5" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </div>
                        <h3>Données insuffisantes pour ce secteur</h3>
                        <p id="result-insufficient-msg">
                            Les données disponibles ne permettent pas de calculer une fourchette
                            fiable pour ce secteur et ce type de bien.
                            Demandez un avis de valeur personnalisé —
                            <?= e($_lpName !== '' ? $_lpName : 'Eduardo Desul') ?>
                            se déplace dans toute la métropole bordelaise.
                        </p>
                        <div class="result-insufficient__actions">
                            <a href="/avis-de-valeur" class="btn btn--accent">
                                Demander un avis de valeur
                            </a>
                            <button type="button"
                                    class="btn btn--outline"
                                    id="btn-restart-2">Réessayer</button>
                        </div>
                    </div>
                </div>

                <!-- État : erreur -->
                <div id="result-error" hidden>
                    <div class="result-error">
                        <p>Une erreur est survenue lors du calcul. Veuillez réessayer.</p>
                        <button type="button"
                                class="btn btn--outline"
                                id="btn-restart-3">Réessayer</button>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 4 — Formulaire de conversion                          -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step"
                 id="step-4"
                 aria-label="Étape 4 : Vos coordonnées"
                 hidden>

                <button type="button"
                        class="convert-back"
                        id="btn-convert-back"
                        aria-label="Retour aux résultats">← Retour</button>

                <h2 class="tunnel-step__title" id="convert-title">Recevoir le rapport</h2>
                <p class="tunnel-step__sub" id="convert-sub">
                    Indiquez votre email pour recevoir votre rapport d'estimation.
                </p>

                <form id="convert-form" novalidate>
                    <div id="convert-csrf" style="display:none"><?= csrfField() ?></div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="c-firstname">
                                Prénom <span class="required-star" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="c-firstname"
                                   name="first_name"
                                   class="form-control"
                                   placeholder="Jean"
                                   autocomplete="given-name"
                                   required>
                            <div class="field-error" id="err-firstname" role="alert" hidden></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="c-lastname">Nom</label>
                            <input type="text"
                                   id="c-lastname"
                                   name="last_name"
                                   class="form-control"
                                   placeholder="Dupont"
                                   autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="c-email" id="label-email">
                            Email <span class="required-star" aria-hidden="true">*</span>
                        </label>
                        <input type="email"
                               id="c-email"
                               name="email"
                               class="form-control"
                               placeholder="jean.dupont@example.com"
                               autocomplete="email"
                               required>
                        <div class="field-error" id="err-email" role="alert" hidden></div>
                    </div>

                    <div class="form-group" id="phone-group" hidden>
                        <label class="form-label" for="c-phone">Téléphone</label>
                        <input type="tel"
                               id="c-phone"
                               name="phone"
                               class="form-control"
                               placeholder="06 00 00 00 00"
                               autocomplete="tel"
                               inputmode="tel">
                    </div>

                    <div class="form-group" id="message-group" hidden>
                        <label class="form-label" for="c-message">Message (optionnel)</label>
                        <textarea id="c-message"
                                  name="message"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Précisez votre situation ou vos questions…"></textarea>
                    </div>

                    <input type="hidden" name="action_type" id="c-action-type">
                    <input type="hidden" name="request_id" id="c-request-id">

                    <div class="convert-submit">
                        <button type="submit"
                                class="btn btn--accent btn--lg btn--full"
                                id="btn-convert-submit">
                            <span class="btn-text" id="convert-submit-text">Envoyer</span>
                            <span class="btn-spinner"
                                  id="convert-spinner"
                                  hidden
                                  aria-hidden="true"></span>
                        </button>
                        <p class="convert-hint">
                            Vos données sont utilisées uniquement pour répondre à votre demande.
                        </p>
                    </div>
                </form>

                <div id="convert-success" hidden>
                    <div class="convert-success">
                        <div class="convert-success__icon" aria-hidden="true">✅</div>
                        <h3 id="convert-success-title">Demande envoyée !</h3>
                        <p id="convert-success-msg">
                            <?= e($_lpName !== '' ? $_lpName : 'Votre conseiller') ?>
                            reviendra vers vous dans les meilleurs délais.
                        </p>
                        <a href="/" class="btn btn--outline" style="margin-top:1.5rem">
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>

        </div><!-- /.tunnel-wrap -->
    </div><!-- /.container -->
</section>

<!-- ══ LP FOOTER ═════════════════════════════════════════════════════════════ -->
<div class="lp-footer">
    <div class="container">
        <div class="lp-footer__inner">
            <span>
                © <?= date('Y') ?>
                <?= e($_lpName !== '' ? $_lpName : 'Eduardo Desul') ?>
                — Conseiller immobilier indépendant · Bordeaux Métropole (33)
            </span>
            <span class="lp-footer__links">
                <a href="/politique-confidentialite">Confidentialité</a>
                <a href="/mentions-legales">Mentions légales</a>
            </span>
        </div>
    </div>
</div>

<?php if ($googleApiKey !== ''): ?>
<script>
(function() {
  var s = document.createElement('script');
  s.src = 'https://maps.googleapis.com/maps/api/js'
        + '?key=<?= htmlspecialchars($googleApiKey, ENT_QUOTES) ?>'
        + '&libraries=places&callback=__initEstimationPlaces&loading=async';
  s.async = true;
  document.head.appendChild(s);
})();
</script>
<?php endif; ?>
