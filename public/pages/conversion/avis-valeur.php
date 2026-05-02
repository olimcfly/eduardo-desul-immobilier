<?php
declare(strict_types=1);

$propertyTypeLabels = [
    'appartement' => 'Appartement',
    'maison' => 'Maison',
    'villa' => 'Villa',
    'terrain' => 'Terrain',
    'local-commercial' => 'Local commercial',
];

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'property_type' => '',
    'surface' => '',
    'message' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    verifyCsrf();

    foreach (array_keys($formData) as $k) {
        $formData[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    if ($formData['first_name'] === '') {
        $errors['first_name'] = 'Merci de renseigner votre prénom.';
    }
    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Merci de renseigner une adresse e-mail valide.';
    }
    if ($formData['address'] === '') {
        $errors['address'] = 'Merci d’indiquer l’adresse du bien.';
    }
    if ($formData['property_type'] === '' || !isset($propertyTypeLabels[$formData['property_type']])) {
        $errors['property_type'] = 'Sélectionnez un type de bien.';
    }
    if (empty($_POST['consent'])) {
        $errors['consent'] = 'Vous devez accepter le traitement de vos données pour cette demande.';
    }

    if ($formData['surface'] !== '') {
        if (!ctype_digit($formData['surface'])) {
            $errors['surface'] = 'Indiquez la surface en m² (nombre entier) ou laissez vide.';
        } else {
            $s = (int) $formData['surface'];
            if ($s < 1 || $s > 50000) {
                $errors['surface'] = 'Surface hors plage plausible (1 à 50 000 m²).';
            }
        }
    }

    if ($formData['message'] !== '' && mb_strlen($formData['message']) > 4000) {
        $errors['message'] = 'Message trop long (4 000 caractères maximum).';
    }

    if ($errors === []) {
        $typeLabel = $propertyTypeLabels[$formData['property_type']] ?? $formData['property_type'];
        $surfaceMeta = $formData['surface'] !== '' ? (int) $formData['surface'] : null;
        $originPath = (string) ($_SERVER['REQUEST_URI'] ?? '/avis-de-valeur');

        $metadata = [
            'demande_type' => 'avis_valeur',
            'surface_m2' => $surfaceMeta,
            'origin_path' => $originPath,
            'type_bien_key' => $formData['property_type'],
        ];

        $leadId = LeadService::capture([
            'source_type' => LeadService::SOURCE_AVIS_VALEUR,
            'pipeline' => LeadService::SOURCE_AVIS_VALEUR,
            'stage' => 'nouveau',
            'priority' => 'haute',
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'intent' => 'Demande d\'avis de valeur',
            'property_type' => $typeLabel,
            'property_address' => $formData['address'],
            'notes' => $formData['message'],
            'consent' => true,
            'metadata' => $metadata,
        ]);

        if ($leadId > 0) {
            $notify = [
                'prenom' => $formData['first_name'],
                'nom' => $formData['last_name'],
                'email' => $formData['email'],
                'telephone' => $formData['phone'],
                'adresse_bien' => $formData['address'],
                'type_bien' => $typeLabel,
                'surface_m2' => $surfaceMeta !== null ? (string) $surfaceMeta : '—',
                'message' => $formData['message'],
            ];
            require_once ROOT_PATH . '/core/services/AvisValeurNotificationService.php';
            try {
                AvisValeurNotificationService::afterCapture($leadId, $notify);
            } catch (Throwable $e) {
                error_log('[avis-valeur] afterCapture: ' . $e->getMessage());
            }
        }

        Session::flash(
            'success',
            'Votre demande d’avis de valeur a bien été envoyée. Vous recevrez un accusé de réception par e-mail ; '
            . (defined('ADVISOR_NAME') && trim((string) ADVISOR_NAME) !== '' ? ADVISOR_NAME : 'nous') . ' vous recontacte rapidement.'
        );
        redirect('/avis-de-valeur#demande-avis-valeur');
    }
}

$advisorForMeta = (defined('ADVISOR_NAME') && trim((string) ADVISOR_NAME) !== '') ? ADVISOR_NAME : 'Un conseiller';

$pageTitle = 'Avis de valeur — Accompagnement personnalisé | ' . APP_NAME;
$metaDesc  = 'Obtenez un avis de valeur clair sur votre bien à Bordeaux Métropole. ' . $advisorForMeta
    . ' étudie votre situation et revient vers vous rapidement, sans engagement.';
$extraCss  = ['/assets/css/financement.css', '/assets/css/estimation.css'];
$formAction = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '/avis-de-valeur');
if ($formAction === '' || $formAction === '/') {
    $formAction = '/avis-de-valeur';
}
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane"><a href="/">Accueil</a><span>Avis de valeur</span></nav>
        <h1>La valeur de votre bien, avec un regard professionnel</h1>
        <p>Déposez quelques informations : <?= e($advisorForMeta) ?> reprend contact pour affiner l’analyse, dans la même
            exigence de clarté que sur une demande de financement ou d’estimation.</p>
        <div class="fin-hero-actions">
            <a href="#demande-avis-valeur" class="btn btn--accent btn--lg">Envoyer ma demande</a>
            <a href="/estimation-gratuite" class="btn btn--outline-white btn--lg">Estimation en ligne (tunnel)</a>
        </div>
        <p class="fin-hero-trust">Réponse personnalisée &middot; Sans engagement &middot; Données traitées en confidentialité</p>
    </div>
</div>

<section class="section" style="padding-top:0">
    <div class="container">
        <div class="grid-4 fin-reassurance" data-animate>
            <article class="fin-card">
                <h2>Marché local</h2>
                <p>Comparables, quartier, tendances observées autour de votre adresse.</p>
            </article>
            <article class="fin-card">
                <h2>Cadrage du prix</h2>
                <p>Plage de valeur et conseils de mise en perspective avant toute décision.</p>
            </article>
            <article class="fin-card">
                <h2>Interlocuteur unique</h2>
                <p>Échange direct, sans enchaînement d’outils automatisés.</p>
            </article>
            <article class="fin-card">
                <h2>Pas d’engagement</h2>
                <p>Vous décidez ensuite, en toute connaissance, de la suite du projet.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container" style="max-width: 980px">
        <div class="fin-columns" data-animate>
            <div>
                <span class="section-label">Pourquoi un avis de valeur</span>
                <h2 class="section-title">Fixer le bon niveau de prix dès le départ</h2>
                <p class="section-subtitle">Avant d’ouvrir une négociation, de publier une annonce ou d’envisager des travaux,
                    il est utile de cadrer le bien par rapport au marché réel. La demande ci-dessous permet d’enclencher un
                    échange ciblé avec <?= e($advisorForMeta) ?>.</p>
            </div>
            <ul class="fin-list">
                <li>Éviter les écarts par rapport aux ventes récentes du secteur.</li>
                <li>Comprendre quels atouts retenir pour l’argumentaire d’annonce.</li>
                <li>Anticiper les points sensibles (copropriété, état, Loi Carrez…).</li>
            </ul>
        </div>
    </div>
</section>

<section class="section" id="demande-avis-valeur">
    <div class="container" style="max-width: 720px">
        <div class="section__header text-center">
            <span class="section-label">Votre demande</span>
            <h2 class="section-title">Formulaire d’avis de valeur</h2>
            <p class="section-subtitle">Renseignez l’adresse et les éléments principaux. Vous serez recontacter pour préciser
                le bien si besoin.</p>
        </div>
        <?php if ($errors !== []): ?>
        <div class="flash flash--error" role="alert" style="margin-bottom:1.25rem">
            <span>Merci de corriger les champs indiqués ci-dessous.</span>
        </div>
        <?php endif; ?>
        <div class="fin-form-box" data-animate>
            <div class="fin-card" style="padding: 2rem">
                <form method="post" action="<?= e($formAction) ?>#demande-avis-valeur" class="estimation-form" novalidate>
                    <?= csrfField() ?>
                    <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div>
                            <label class="form-label" for="av-firstname">Prénom *</label>
                            <input class="form-control" id="av-firstname" name="first_name" required autocomplete="given-name" value="<?= e($formData['first_name']) ?>">
                            <div class="form-error"><?= e($errors['first_name'] ?? '') ?></div>
                        </div>
                        <div>
                            <label class="form-label" for="av-lastname">Nom</label>
                            <input class="form-control" id="av-lastname" name="last_name" autocomplete="family-name" value="<?= e($formData['last_name']) ?>">
                        </div>
                    </div>
                    <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div>
                            <label class="form-label" for="av-email">E-mail *</label>
                            <input class="form-control" id="av-email" type="email" name="email" required autocomplete="email" value="<?= e($formData['email']) ?>">
                            <div class="form-error"><?= e($errors['email'] ?? '') ?></div>
                        </div>
                        <div>
                            <label class="form-label" for="av-phone">Téléphone</label>
                            <input class="form-control" id="av-phone" type="tel" name="phone" autocomplete="tel" value="<?= e($formData['phone']) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="av-address">Adresse du bien *</label>
                        <input class="form-control" id="av-address" name="address" required placeholder="Ex. : 12 allées de Tourny, Bordeaux" autocomplete="street-address" value="<?= e($formData['address']) ?>">
                        <div class="form-error"><?= e($errors['address'] ?? '') ?></div>
                    </div>
                    <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div>
                            <label class="form-label" for="av-type">Type de bien *</label>
                            <select class="form-control" id="av-type" name="property_type" required>
                                <option value="">Sélectionner</option>
                                <?php foreach ($propertyTypeLabels as $val => $lab): ?>
                                <option value="<?= e($val) ?>"<?= $formData['property_type'] === $val ? ' selected' : '' ?>><?= e($lab) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-error"><?= e($errors['property_type'] ?? '') ?></div>
                        </div>
                        <div>
                            <label class="form-label" for="av-surface">Surface (m²)</label>
                            <input class="form-control" id="av-surface" type="number" name="surface" min="1" max="50000" inputmode="numeric" value="<?= e($formData['surface']) ?>" placeholder="Optionnel">
                            <div class="form-error"><?= e($errors['surface'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="av-message">Précisions utiles <span class="form-hint">(optionnel)</span></label>
                        <textarea class="form-control" id="av-message" name="message" rows="4" placeholder="État du bien, étage, parking, DPE, projet de vente…"><?= e($formData['message']) ?></textarea>
                        <div class="form-error"><?= e($errors['message'] ?? '') ?></div>
                    </div>
                    <div class="form-group form-group--checkbox" style="margin-top:0.5rem">
                        <label class="checkbox-label" style="display:flex;gap:.5rem;align-items:flex-start;cursor:pointer">
                            <input type="checkbox" name="consent" value="1"<?= !empty($_POST['consent']) ? ' checked' : '' ?> required>
                            <span class="form-hint" style="margin:0">J’accepte que mes données soient traitées pour cette demande, conformément à la
                                <a href="<?= e(url('/politique-confidentialite')) ?>">politique de confidentialité</a>. *</span>
                        </label>
                        <div class="form-error"><?= e($errors['consent'] ?? '') ?></div>
                    </div>
                    <button class="btn btn--accent" type="submit" style="width:100%;margin-top:0.5rem">Envoyer ma demande d’avis de valeur</button>
                </form>
            </div>
        </div>
    </div>
</section>
