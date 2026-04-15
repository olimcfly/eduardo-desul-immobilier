<?php
/**
 * ESTIMATION TUNNEL — Page publique multi-étapes
 * Route GET : /estimation
 */
$pageTitle = 'Estimation immobilière gratuite — Pays d\'Aix | ADVISOR_NAME';
$metaDesc  = 'Obtenez une fourchette de prix en 60 secondes. Basée sur les ventes réelles DVF. Sans inscription, sans engagement.';
$extraCss  = ['/assets/css/estimation-tunnel.css'];
$extraJs   = ['/assets/js/estimation-tunnel.js'];
?>

<!-- ══ HERO ══════════════════════════════════════════════════════════════════ -->
<section class="tunnel-hero" aria-label="Estimation immobilière">
    <div class="container">
        <div class="tunnel-hero__inner">
            <span class="section-label">Estimation gratuite · Pays d'Aix</span>
            <h1>Combien vaut votre bien<br><span class="tunnel-hero__accent">à Aix-en-Provence ?</span></h1>
            <p class="tunnel-hero__sub">Fourchette indicative basée sur les ventes DVF et le marché actuel · 60 secondes · Sans inscription</p>
            <div class="tunnel-hero__badges">
                <span class="badge">🔒 Aucune donnée personnelle requise</span>
                <span class="badge">📊 Données DVF officielles</span>
                <span class="badge">⚡ Résultat instantané</span>
            </div>
        </div>
    </div>
</section>

<!-- ══ TUNNEL ════════════════════════════════════════════════════════════════ -->
<section class="section section--tunnel" aria-label="Formulaire d'estimation">
    <div class="container">
        <div class="tunnel-wrap" id="tunnel-app">

            <!-- ── Progress bar ──────────────────────────────────────────── -->
            <div class="tunnel-progress" aria-hidden="true">
                <div class="tunnel-progress__bar" id="tunnel-progress-bar" style="width:33%"></div>
            </div>
            <div class="tunnel-steps-label" aria-live="polite" id="tunnel-step-label">Étape 1 / 3</div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 1 — Caractéristiques du bien                         -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step tunnel-step--active" id="step-1" aria-label="Étape 1 : Caractéristiques du bien">

                <h2 class="tunnel-step__title">Votre bien</h2>
                <p class="tunnel-step__sub">Sélectionnez le type et renseignez les caractéristiques principales.</p>

                <!-- Type de bien -->
                <div class="form-section">
                    <label class="form-label">Type de bien <span class="required-star" aria-hidden="true">*</span></label>
                    <div class="type-grid" role="group" aria-label="Type de bien">
                        <?php foreach ([
                            ['appartement', '🏢', 'Appartement'],
                            ['maison',      '🏠', 'Maison'],
                            ['villa',       '🏡', 'Villa'],
                            ['terrain',     '🌿', 'Terrain'],
                            ['local',       '🏪', 'Local'],
                            ['immeuble',    '🏬', 'Immeuble'],
                        ] as [$val, $icon, $label]): ?>
                        <label class="type-card" data-type="<?= $val ?>">
                            <input type="radio" name="property_type" value="<?= $val ?>" class="sr-only" <?= $val === 'appartement' ? 'checked' : '' ?>>
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
                            Surface habitable <span class="required-star" aria-hidden="true">*</span>
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
                            <option value="<?= $i ?>"><?= $i ?> pièce<?= $i > 1 ? 's' : '' ?></option>
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
                            <input type="radio" name="valuation_mode" value="sold" class="sr-only" checked>
                            <span>📋 Ventes DVF</span>
                        </label>
                        <label class="toggle-btn">
                            <input type="radio" name="valuation_mode" value="live" class="sr-only">
                            <span>🏷️ Annonces actuelles</span>
                        </label>
                        <label class="toggle-btn">
                            <input type="radio" name="valuation_mode" value="both" class="sr-only">
                            <span>🔀 Les deux</span>
                        </label>
                    </div>
                    <small class="form-hint">DVF = transactions officielles enregistrées ces 12 derniers mois.</small>
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
            <div class="tunnel-step" id="step-2" aria-label="Étape 2 : Localisation" hidden>

                <h2 class="tunnel-step__title">Localisation du bien</h2>
                <p class="tunnel-step__sub">Indiquez la ville ou le code postal pour affiner l'estimation.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="t-city">
                            Ville <span class="required-star" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="t-city"
                               name="ville"
                               class="form-control"
                               placeholder="Ex : Aix-en-Provence"
                               autocomplete="off">
                        <div class="field-error" id="err-city" role="alert" hidden></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="t-postal">
                            Code postal <span class="required-star" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="t-postal"
                               name="postal_code"
                               class="form-control"
                               placeholder="13100"
                               inputmode="numeric"
                               maxlength="5">
                        <div class="field-error" id="err-postal" role="alert" hidden></div>
                    </div>
                </div>

                <!-- Géolocalisation optionnelle -->
                <div class="geo-block">
                    <button type="button" class="btn btn--outline btn--sm" id="btn-geolocate">
                        <span id="geo-btn-text">📍 Utiliser ma position</span>
                    </button>
                    <span class="geo-status" id="geo-status" aria-live="polite"></span>
                    <input type="hidden" name="lat" id="t-lat">
                    <input type="hidden" name="lng" id="t-lng">
                </div>

                <div class="tunnel-nav">
                    <button type="button" class="btn btn--outline" id="btn-step2-back">← Retour</button>
                    <button type="button" class="btn btn--accent btn--lg" id="btn-step2-next">
                        Calculer l'estimation <span aria-hidden="true">→</span>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 3 — Résultats                                         -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step" id="step-3" aria-label="Étape 3 : Résultats" hidden>

                <!-- Loading -->
                <div id="result-loading" class="result-loading" aria-live="polite">
                    <div class="result-loading__spinner" aria-hidden="true"></div>
                    <p>Calcul en cours…</p>
                </div>

                <!-- Résultat OK -->
                <div id="result-ok" hidden>
                    <div class="result-header">
                        <div class="result-header__recap" id="result-recap"></div>
                        <div class="result-reliability" id="result-reliability"></div>
                    </div>

                    <!-- Fourchette de prix -->
                    <div class="price-band" aria-label="Fourchette d'estimation">
                        <div class="price-band__item price-band__item--low">
                            <span class="price-band__label">Fourchette basse</span>
                            <span class="price-band__value" id="price-low">—</span>
                        </div>
                        <div class="price-band__item price-band__item--med">
                            <span class="price-band__label">Valeur estimée ★</span>
                            <span class="price-band__value" id="price-med">—</span>
                        </div>
                        <div class="price-band__item price-band__item--high">
                            <span class="price-band__label">Fourchette haute</span>
                            <span class="price-band__value" id="price-high">—</span>
                        </div>
                    </div>

                    <!-- Disclaimer légal -->
                    <div class="result-disclaimer">
                        <strong>⚠️ Mention légale</strong> — Cette fourchette est <strong>strictement indicative</strong>.
                        Elle est calculée à partir de données de marché et ne constitue pas une estimation officielle,
                        un avis de valeur professionnel ni une expertise immobilière au sens légal.
                        Seule une visite terrain par un professionnel habilité peut établir une valeur vénale fiable.
                    </div>

                    <!-- CTAs de conversion -->
                    <div class="result-ctas">
                        <p class="result-ctas__intro">Que souhaitez-vous faire maintenant ?</p>
                        <div class="result-ctas__grid">

                            <!-- CTA 1 : Rapport par email -->
                            <div class="cta-card cta-card--report" data-action="email_report">
                                <div class="cta-card__icon" aria-hidden="true">📧</div>
                                <h3 class="cta-card__title">Recevoir le rapport</h3>
                                <p class="cta-card__text">Obtenez ce rapport détaillé par email avec les comparables du secteur.</p>
                                <button type="button" class="btn btn--outline btn--full cta-trigger" data-action="email_report">
                                    Recevoir par email
                                </button>
                            </div>

                            <!-- CTA 2 : Demande de contact -->
                            <div class="cta-card cta-card--contact" data-action="contact_request">
                                <div class="cta-card__icon" aria-hidden="true">📞</div>
                                <h3 class="cta-card__title">Être rappelé</h3>
                                <p class="cta-card__text">ADVISOR_NAME vous contacte pour affiner cette estimation et répondre à vos questions.</p>
                                <button type="button" class="btn btn--outline btn--full cta-trigger" data-action="contact_request">
                                    Demander un rappel
                                </button>
                            </div>

                            <!-- CTA 3 : RDV -->
                            <div class="cta-card cta-card--rdv" data-action="rdv_request">
                                <div class="cta-card__icon" aria-hidden="true">📅</div>
                                <h3 class="cta-card__title">Prendre rendez-vous</h3>
                                <p class="cta-card__text">Rencontrez ADVISOR_NAME pour une estimation certifiée, sur place, sans engagement.</p>
                                <button type="button" class="btn btn--accent btn--full cta-trigger" data-action="rdv_request">
                                    Choisir un créneau
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Recommencer -->
                    <div class="result-restart">
                        <button type="button" class="btn btn--ghost btn--sm" id="btn-restart">
                            Faire une nouvelle estimation
                        </button>
                    </div>
                </div>

                <!-- Résultat : données insuffisantes -->
                <div id="result-insufficient" hidden>
                    <div class="result-insufficient">
                        <div class="result-insufficient__icon" aria-hidden="true">🔍</div>
                        <h3>Données insuffisantes pour ce secteur</h3>
                        <p id="result-insufficient-msg">
                            Les données disponibles ne permettent pas de calculer une fourchette fiable pour ce secteur et ce type de bien.
                            Demandez un avis de valeur personnalisé — ADVISOR_NAME se déplace dans tout le Pays d'Aix.
                        </p>
                        <div class="result-insufficient__actions">
                            <a href="/avis-de-valeur" class="btn btn--accent">Demander un avis de valeur</a>
                            <button type="button" class="btn btn--outline" id="btn-restart-2">Réessayer</button>
                        </div>
                    </div>
                </div>

                <!-- Résultat : erreur -->
                <div id="result-error" hidden>
                    <div class="result-error">
                        <p>Une erreur est survenue lors du calcul. Veuillez réessayer.</p>
                        <button type="button" class="btn btn--outline" id="btn-restart-3">Réessayer</button>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- ÉTAPE 4 — Formulaire de conversion (modal inline)           -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="tunnel-step" id="step-4" aria-label="Étape 4 : Vos coordonnées" hidden>

                <button type="button" class="convert-back" id="btn-convert-back" aria-label="Retour aux résultats">← Retour</button>

                <h2 class="tunnel-step__title" id="convert-title">Recevoir le rapport</h2>
                <p class="tunnel-step__sub" id="convert-sub">Indiquez votre email pour recevoir votre rapport d'estimation.</p>

                <form id="convert-form" novalidate>
                    <div id="convert-csrf" style="display:none"><?= csrfField() ?></div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="c-firstname">
                                Prénom <span class="required-star" aria-hidden="true">*</span>
                            </label>
                            <input type="text" id="c-firstname" name="first_name" class="form-control"
                                   placeholder="Jean" autocomplete="given-name" required>
                            <div class="field-error" id="err-firstname" role="alert" hidden></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="c-lastname">Nom</label>
                            <input type="text" id="c-lastname" name="last_name" class="form-control"
                                   placeholder="Dupont" autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="c-email" id="label-email">
                            Email <span class="required-star" aria-hidden="true">*</span>
                        </label>
                        <input type="email" id="c-email" name="email" class="form-control"
                               placeholder="jean.dupont@example.com" autocomplete="email" required>
                        <div class="field-error" id="err-email" role="alert" hidden></div>
                    </div>

                    <!-- Téléphone (affiché si action = contact_request | rdv_request) -->
                    <div class="form-group" id="phone-group" hidden>
                        <label class="form-label" for="c-phone">Téléphone</label>
                        <input type="tel" id="c-phone" name="phone" class="form-control"
                               placeholder="06 00 00 00 00" autocomplete="tel" inputmode="tel">
                    </div>

                    <!-- Message (affiché si action = contact_request) -->
                    <div class="form-group" id="message-group" hidden>
                        <label class="form-label" for="c-message">Message (optionnel)</label>
                        <textarea id="c-message" name="message" class="form-control" rows="3"
                                  placeholder="Précisez votre situation ou vos questions…"></textarea>
                    </div>

                    <input type="hidden" name="action_type" id="c-action-type">
                    <input type="hidden" name="request_id" id="c-request-id">

                    <div class="convert-submit">
                        <button type="submit" class="btn btn--accent btn--lg btn--full" id="btn-convert-submit">
                            <span class="btn-text" id="convert-submit-text">Envoyer</span>
                            <span class="btn-spinner" id="convert-spinner" hidden aria-hidden="true"></span>
                        </button>
                        <p class="convert-hint">🔒 Vos données sont utilisées uniquement pour répondre à votre demande.</p>
                    </div>
                </form>

                <div id="convert-success" hidden>
                    <div class="convert-success">
                        <div class="convert-success__icon" aria-hidden="true">✅</div>
                        <h3 id="convert-success-title">Demande envoyée !</h3>
                        <p id="convert-success-msg">ADVISOR_NAME reviendra vers vous dans les meilleurs délais.</p>
                        <a href="/" class="btn btn--outline" style="margin-top:1.5rem">Retour à l'accueil</a>
                    </div>
                </div>
            </div>

        </div><!-- /.tunnel-wrap -->
    </div><!-- /.container -->
</section>

<!-- ══ RÉASSURANCE ════════════════════════════════════════════════════════════ -->
<section class="section section--alt" aria-label="Pourquoi nous faire confiance">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Votre estimation en toute sérénité</span>
            <h2 class="section-title">Une estimation fondée sur des données réelles</h2>
        </div>
        <div class="grid-3">
            <article class="card" data-animate>
                <div class="card__body">
                    <h3 class="card__title">📋 Données DVF officielles</h3>
                    <p class="card__text">
                        Les Demandes de Valeurs Foncières (DVF) regroupent toutes les transactions
                        immobilières enregistrées en France. Notre algorithme les exploite pour
                        calculer des fourchettes de prix basées sur des ventes réelles comparables.
                    </p>
                </div>
            </article>
            <article class="card" data-animate>
                <div class="card__body">
                    <h3 class="card__title">🔍 Comparaison de marché</h3>
                    <p class="card__text">
                        En plus des DVF, l'estimation peut intégrer les biens actuellement en vente
                        dans le secteur pour une vision complète de ce que le marché propose aujourd'hui.
                    </p>
                </div>
            </article>
            <article class="card" data-animate>
                <div class="card__body">
                    <h3 class="card__title">🤝 Complétée par un expert</h3>
                    <p class="card__text">
                        Une estimation algorithmique reste indicative. ADVISOR_NAME peut affiner
                        cette fourchette grâce à une visite terrain et une connaissance fine
                        du marché local à Aix-en-Provence et dans le Pays d'Aix.
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- ══ CTA FINAL ══════════════════════════════════════════════════════════════ -->
<section class="cta-banner">
    <div class="container">
        <h2>Besoin d'une estimation certifiée ?</h2>
        <p>ADVISOR_NAME se déplace dans tout le Pays d'Aix pour une évaluation précise, sur place, sans engagement.</p>
        <div class="cta-banner__actions">
            <a href="/prendre-rendez-vous" class="btn btn--accent btn--lg">Prendre rendez-vous</a>
            <a href="/avis-de-valeur" class="btn btn--outline-white btn--lg">Avis de valeur</a>
        </div>
    </div>
</section>
