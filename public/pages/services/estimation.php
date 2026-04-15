<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email  = trim((string)($_POST['email']  ?? ''));
    $prenom = trim((string)($_POST['prenom'] ?? ''));

    if ($email !== '' && $prenom !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_ESTIMATION,
            'pipeline'    => LeadService::SOURCE_ESTIMATION,
            'stage'       => 'a_traiter',
            'first_name'  => $prenom,
            'last_name'   => trim((string)($_POST['nom']       ?? '')),
            'email'       => $email,
            'phone'       => trim((string)($_POST['telephone'] ?? '')),
            'intent'      => 'Estimation gratuite',
            'consent'     => !empty($_POST['rgpd']),
            'metadata'    => [
                'origin_path'       => $_SERVER['REQUEST_URI'] ?? '/estimation-gratuite',
                'type_bien'         => trim((string)($_POST['type_bien']         ?? '')),
                'adresse'           => trim((string)($_POST['adresse']           ?? '')),
                'surface'           => trim((string)($_POST['surface']           ?? '')),
                'pieces'            => trim((string)($_POST['pieces']            ?? '')),
                'etage'             => trim((string)($_POST['etage']             ?? '')),
                'annee_construction'=> trim((string)($_POST['annee_construction']?? '')),
                'etat'              => trim((string)($_POST['etat']              ?? '')),
                'demande_rdv'       => !empty($_POST['demande_rdv']),
                'creneau_prefere'   => trim((string)($_POST['creneau_prefere']   ?? '')),
            ],
        ]);

        redirect('/merci');
    }
}

$pageTitle = 'Estimation gratuite — Pascal Hamm | Expert Immobilier 360° Aix-en-Provence';
$metaDesc  = 'Estimez gratuitement votre bien immobilier à Aix-en-Provence avec Pascal Hamm. Réponse personnalisée sous 48h.';
$extraCss  = ['/assets/css/estimation.css'];
$extraJs   = ['/assets/js/estimation.js'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a><span>Estimation gratuite</span>
        </nav>
        <h1>Estimation gratuite</h1>
        <p>Obtenez une évaluation précise de votre bien en quelques minutes. Entièrement gratuit et sans engagement.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="estimation-layout">

            <!-- Formulaire multi-étapes -->
            <div class="estimation-form">
                <h2>Évaluez votre bien</h2>
                <p class="lead">Remplissez ce formulaire pour recevoir une estimation personnalisée.</p>

                <!-- Stepper -->
                <div class="stepper" role="list" aria-label="Étapes du formulaire">
                    <div class="step active" role="listitem"><span class="step-label">Votre bien</span></div>
                    <div class="step"        role="listitem"><span class="step-label">Caractéristiques</span></div>
                    <div class="step"        role="listitem"><span class="step-label">Contact</span></div>
                </div>

                <form id="estimation-form" action="/estimation-gratuite" method="POST" novalidate>
                    <?= csrfField() ?>
                    <input type="hidden" id="type-bien" name="type_bien" value="">

                    <!-- Étape 1 : Type de bien -->
                    <div class="estimation-step" data-step="1">
                        <h3>Quel type de bien souhaitez-vous estimer ?</h3>
                        <div class="type-buttons" role="group" aria-label="Type de bien">
                            <?php
                            $types = [
                                'appartement' => ['🏢', 'Appartement'],
                                'maison'      => ['🏠', 'Maison'],
                                'terrain'     => ['🌿', 'Terrain'],
                                'local'       => ['🏪', 'Local comm.'],
                                'immeuble'    => ['🏗️', 'Immeuble'],
                                'autre'       => ['📦', 'Autre'],
                            ];
                            foreach ($types as $value => [$icon, $label]): ?>
                            <button
                                type="button"
                                class="type-btn"
                                data-value="<?= e($value) ?>"
                                aria-pressed="false">
                                <span aria-hidden="true"><?= $icon ?></span>
                                <?= e($label) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="adresse">
                                Adresse du bien <span aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                id="adresse"
                                name="adresse"
                                class="form-control"
                                placeholder="12 avenue du Général de Gaulle, Aix-en-Provence"
                                required
                                autocomplete="street-address"
                                aria-describedby="adresse-err">
                            <div class="form-error" id="adresse-err"></div>
                        </div>

                        <div class="step-actions step-actions--right">
                            <button type="button" class="btn btn--primary next-btn">Continuer →</button>
                        </div>
                    </div>

                    <!-- Étape 2 : Caractéristiques -->
                    <div class="estimation-step" data-step="2" hidden>
                        <h3>Caractéristiques du bien</h3>

                        <div class="range-group">
                            <div class="range-display">
                                <span>Surface habitable</span>
                                <span class="range-val" id="surface-val">80 m²</span>
                            </div>
                            <input
                                type="range"
                                id="surface"
                                name="surface"
                                min="10"
                                max="500"
                                value="80"
                                data-format="surface"
                                aria-valuetext="80 m²"
                                aria-label="Surface habitable">
                        </div>

                        <div class="range-group">
                            <div class="range-display">
                                <span>Nombre de pièces</span>
                                <span class="range-val" id="pieces-val">3</span>
                            </div>
                            <input
                                type="range"
                                id="pieces"
                                name="pieces"
                                min="1"
                                max="15"
                                value="3"
                                data-format="count"
                                aria-valuetext="3 pièces"
                                aria-label="Nombre de pièces">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="etage">Étage</label>
                                <select id="etage" name="etage" class="form-control">
                                    <option value="rdc">Rez-de-chaussée</option>
                                    <option value="1">1er</option>
                                    <option value="2">2ème</option>
                                    <option value="3">3ème</option>
                                    <option value="4+">4ème et +</option>
                                    <option value="dernier">Dernier étage</option>
                                    <option value="na">Non applicable</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="annee_construction">Année de construction</label>
                                <select id="annee_construction" name="annee_construction" class="form-control">
                                    <option value="avant-1950">Avant 1950</option>
                                    <option value="1950-1970">1950 – 1970</option>
                                    <option value="1971-1990">1971 – 1990</option>
                                    <option value="1991-2010">1991 – 2010</option>
                                    <option value="apres-2010">Après 2010</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <fieldset>
                                <legend class="form-label">État général</legend>
                                <div class="etat-grid">
                                    <?php foreach ([
                                        'a-renover'  => 'À rénover',
                                        'passable'   => 'Passable',
                                        'bon-etat'   => 'Bon état',
                                        'neuf-refait'=> 'Neuf / Refait',
                                    ] as $val => $label): ?>
                                    <label class="etat-label">
                                        <input
                                            type="radio"
                                            name="etat"
                                            value="<?= e($val) ?>">
                                        <?= e($label) ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </div>

                        <div class="step-actions step-actions--between">
                            <button type="button" class="btn btn--outline prev-btn">← Retour</button>
                            <button type="button" class="btn btn--primary next-btn">Continuer →</button>
                        </div>
                    </div>

                    <!-- Étape 3 : Contact -->
                    <div class="estimation-step" data-step="3" hidden>
                        <h3>Vos coordonnées</h3>
                        <p class="form-intro">Pour recevoir votre estimation personnalisée par email.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="est-prenom">
                                    Prénom <span aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="est-prenom"
                                    name="prenom"
                                    class="form-control"
                                    required
                                    autocomplete="given-name"
                                    aria-describedby="est-prenom-err">
                                <div class="form-error" id="est-prenom-err"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="est-nom">
                                    Nom <span aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="est-nom"
                                    name="nom"
                                    class="form-control"
                                    required
                                    autocomplete="family-name"
                                    aria-describedby="est-nom-err">
                                <div class="form-error" id="est-nom-err"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="est-email">
                                Email <span aria-hidden="true">*</span>
                            </label>
                            <input
                                type="email"
                                id="est-email"
                                name="email"
                                class="form-control"
                                required
                                autocomplete="email"
                                aria-describedby="est-email-err">
                            <div class="form-error" id="est-email-err"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="est-tel">Téléphone</label>
                            <input
                                type="tel"
                                id="est-tel"
                                name="telephone"
                                class="form-control"
                                autocomplete="tel">
                            <div class="form-hint">Pour un échange direct avec Pascal.</div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="demande_rdv" value="1">
                                <span>Je souhaite un rendez-vous après réception de mon estimation.</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="est-creneau">Créneau préféré (optionnel)</label>
                            <select id="est-creneau" name="creneau_prefere" class="form-control">
                                <option value="">— Sélectionner —</option>
                                <option value="matin">Matin (9h – 12h)</option>
                                <option value="midi">Midi (12h – 14h)</option>
                                <option value="apres-midi">Après-midi (14h – 18h)</option>
                                <option value="soir">Soir (18h – 20h)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="rgpd" required>
                                <span>
                                    J'accepte la
                                    <a href="/politique-confidentialite" target="_blank">politique de confidentialité</a>.
                                    <span class="required-star" aria-hidden="true">*</span>
                                </span>
                            </label>
                        </div>

                        <div class="step-actions step-actions--between">
                            <button type="button" class="btn btn--outline prev-btn">← Retour</button>
                            <button type="submit" class="btn btn--accent btn--lg">
                                Recevoir mon estimation ✓
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Sidebar -->
            <aside class="estimation-sidebar">

                <div class="why-estimate">
                    <h3>Pourquoi estimer avec moi ?</h3>
                    <?php
                    $whyItems = [
                        ['🎯', 'Précision garantie',        'Analyse basée sur les transactions réelles du marché du Pays d\'Aix.'],
                        ['⚡', 'Réponse sous 48h',          'Un rapport personnalisé dans votre boîte mail rapidement.'],
                        ['🔒', 'Gratuit & sans engagement', 'Aucune obligation de vendre, aucune surprise.'],
                        ['🤝', 'Accompagnement humain',     'Pascal vous recontacte personnellement pour affiner l\'estimation.'],
                    ];
                    foreach ($whyItems as [$icon, $title, $desc]): ?>
                    <div class="why-item">
                        <span class="why-icon" aria-hidden="true"><?= $icon ?></span>
                        <div class="why-text">
                            <strong><?= e($title) ?></strong>
                            <p><?= e($desc) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="advisor-box">
                    <div class="advisor-avatar" aria-hidden="true">👤</div>
                    <h4><?= e(defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Pascal Hamm') ?></h4>
                    <div class="role">Expert immobilier 360° — Pays d\'Aix</div>
                    <p class="advisor-desc">
                        Plus de 15 ans d'expérience sur le marché du Pays d\'Aix.
                        200+ transactions réussies.
                    </p>
                    <?php if (!empty(APP_PHONE)): ?>
                    <a
                        href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"
                        class="btn btn--outline btn--sm btn--full">
                        📞 <?= e(APP_PHONE) ?>
                    </a>
                    <?php endif; ?>
                </div>

            </aside>

        </div>
    </div>
</section>
