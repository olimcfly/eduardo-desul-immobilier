<?php
$errors = [];
$formData = [
    'prenom' => '',
    'nom' => '',
    'email' => '',
    'telephone' => '',
    'type_projet' => '',
    'secteur_recherche' => '',
    'budget_estime' => '',
    'apport_personnel' => '',
    'situation_professionnelle' => '',
    'delai_projet' => '',
    'message' => '',
    'rgpd' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    foreach ($formData as $field => $_) {
        $formData[$field] = trim((string)($_POST[$field] ?? ''));
    }

    if ($formData['prenom'] === '') { $errors['prenom'] = 'Merci de renseigner votre prénom.'; }
    if ($formData['nom'] === '') { $errors['nom'] = 'Merci de renseigner votre nom.'; }
    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Merci de renseigner un email valide.';
    }
    if ($formData['telephone'] === '') { $errors['telephone'] = 'Merci de renseigner votre téléphone.'; }
    if ($formData['type_projet'] === '') { $errors['type_projet'] = 'Sélectionnez votre type de projet.'; }
    if ($formData['secteur_recherche'] === '') { $errors['secteur_recherche'] = 'Indiquez une ville ou un secteur.'; }
    if ($formData['budget_estime'] === '') { $errors['budget_estime'] = 'Précisez votre budget estimé.'; }
    if ($formData['delai_projet'] === '') { $errors['delai_projet'] = 'Précisez votre délai de projet.'; }
    if ($formData['rgpd'] === '') { $errors['rgpd'] = 'Vous devez accepter la politique de confidentialité.'; }

    if ($errors === []) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_FINANCEMENT,
            'pipeline' => LeadService::SOURCE_FINANCEMENT,
            'stage' => 'nouveau',
            'first_name' => $formData['prenom'],
            'last_name' => $formData['nom'],
            'email' => $formData['email'],
            'phone' => $formData['telephone'],
            'intent' => $formData['type_projet'],
            'notes' => $formData['message'],
            'consent' => true,
            'metadata' => [
                'secteur_recherche' => $formData['secteur_recherche'],
                'budget_estime' => $formData['budget_estime'],
                'apport_personnel' => $formData['apport_personnel'],
                'situation_professionnelle' => $formData['situation_professionnelle'],
                'delai_projet' => $formData['delai_projet'],
                'type_projet' => $formData['type_projet'],
                'origin_path' => $_SERVER['REQUEST_URI'] ?? '/financement',
            ],
        ]);

        Session::flash('success', 'Votre demande de financement a bien été envoyée. Nous revenons vers vous rapidement.');
        redirect('/financement#formulaire-financement');
    }
}

$pageTitle = 'Financement immobilier à Bordeaux — Accompagnement personnalisé';
$metaDesc = 'Demandez un accompagnement au financement immobilier à Bordeaux : projet clarifié, dossier simplifié, retour rapide et humain.';
$extraCss = ['/assets/css/financement.css'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane"><a href="/">Accueil</a><span>Financement</span></nav>
        <h1>Financer votre projet immobilier en toute sérénité</h1>
        <p>Un accompagnement clair, humain et premium pour structurer votre demande de prêt immobilier à Bordeaux et dans le Bordeaux Métropole.</p>
        <div class="fin-hero-actions">
            <a href="#formulaire-financement" class="btn btn--accent btn--lg">Envoyer ma demande</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Échanger avec un conseiller</a>
        </div>
        <p class="fin-hero-trust">Réponse personnalisée • Aucun engagement • Accompagnement pas à pas</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="grid-4 fin-reassurance" data-animate>
            <article class="fin-card"><h2>Accompagnement personnalisé</h2><p>Chaque situation est étudiée selon votre projet, vos objectifs et votre rythme.</p></article>
            <article class="fin-card"><h2>Gain de temps</h2><p>Vous avancez avec une méthode claire et les bonnes priorités dès le départ.</p></article>
            <article class="fin-card"><h2>Lisibilité du projet</h2><p>Nous vous aidons à mieux comprendre votre capacité de financement et vos options.</p></article>
            <article class="fin-card"><h2>Interlocuteur humain</h2><p>Un contact dédié pour répondre simplement à vos questions, sans jargon inutile.</p></article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container fin-columns" data-animate>
        <div>
            <span class="section-label">Pourquoi cette démarche ?</span>
            <h2 class="section-title">Pourquoi faire une demande de financement avant d’acheter ?</h2>
            <p class="section-subtitle">Faire une demande de financement en amont permet de clarifier votre budget réel, de réduire le stress administratif et d’avancer plus sereinement sur votre projet immobilier.</p>
        </div>
        <ul class="fin-list">
            <li>Vous sécurisez votre projet d’achat immobilier.</li>
            <li>Vous anticipez les points clés de votre dossier.</li>
            <li>Vous gagnez en crédibilité lors des visites et des offres.</li>
            <li>Vous évitez les démarches complexes au dernier moment.</li>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Projets accompagnés</span>
            <h2 class="section-title">Pour quels projets ?</h2>
        </div>
        <div class="grid-3" data-animate>
            <?php foreach ([
                'Achat de résidence principale',
                'Investissement locatif',
                'Achat d’un appartement',
                'Achat d’une maison',
                'Premier achat immobilier',
                'Achat avec revente',
            ] as $project): ?>
                <article class="fin-card fin-card--project"><h3><?= e($project) ?></h3></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--alt" id="etapes-financement">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Parcours simple</span>
            <h2 class="section-title">Comment ça fonctionne ?</h2>
        </div>
        <div class="fin-steps" data-animate>
            <?php foreach ([
                'Vous déposez votre demande en quelques minutes.',
                'Votre situation est étudiée de façon confidentielle.',
                'Vous êtes recontacté pour un échange personnalisé.',
                'Vous avancez sur votre projet avec plus de clarté.',
            ] as $index => $step): ?>
                <article class="fin-step"><span><?= $index + 1 ?></span><p><?= e($step) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Bénéfices</span>
            <h2 class="section-title">Un accompagnement financement pensé pour vous faire avancer</h2>
        </div>
        <div class="grid-2" data-animate>
            <article class="fin-card"><h3>Mieux comprendre votre capacité de financement</h3><p>Vous obtenez une vision plus claire et réaliste de votre projet.</p></article>
            <article class="fin-card"><h3>Gagner du temps</h3><p>Vous évitez les démarches inutiles et vous concentrez sur l’essentiel.</p></article>
            <article class="fin-card"><h3>Être accompagné dans vos démarches</h3><p>Vous n’êtes pas seul face aux étapes administratives.</p></article>
            <article class="fin-card"><h3>Avancer avec confiance</h3><p>Vous prenez vos décisions avec plus de sérénité et de maîtrise.</p></article>
        </div>
    </div>
</section>

<section class="section section--alt" id="formulaire-financement">
    <div class="container">
        <div class="contact-form-box fin-form-box" data-animate>
            <span class="section-label">Demande de financement</span>
            <h2>Parlons de votre projet immobilier</h2>
            <p>Complétez ce formulaire, nous revenons vers vous avec un accompagnement sur mesure.</p>

            <form method="POST" action="/financement#formulaire-financement" novalidate>
                <?= csrfField() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="prenom">Prénom <span>*</span></label>
                        <input class="form-control" id="prenom" name="prenom" value="<?= e($formData['prenom']) ?>" required>
                        <div class="form-error"><?= e($errors['prenom'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="nom">Nom <span>*</span></label>
                        <input class="form-control" id="nom" name="nom" value="<?= e($formData['nom']) ?>" required>
                        <div class="form-error"><?= e($errors['nom'] ?? '') ?></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span>*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($formData['email']) ?>" required>
                        <div class="form-error"><?= e($errors['email'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="telephone">Téléphone <span>*</span></label>
                        <input class="form-control" id="telephone" name="telephone" value="<?= e($formData['telephone']) ?>" required>
                        <div class="form-error"><?= e($errors['telephone'] ?? '') ?></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="type_projet">Type de projet <span>*</span></label>
                        <select class="form-control" id="type_projet" name="type_projet" required>
                            <option value="">Sélectionnez</option>
                            <?php foreach (['Résidence principale', 'Investissement locatif', 'Premier achat', 'Achat avec revente', 'Autre projet'] as $option): ?>
                                <option value="<?= e($option) ?>" <?= $formData['type_projet'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-error"><?= e($errors['type_projet'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="secteur_recherche">Ville ou secteur recherché <span>*</span></label>
                        <input class="form-control" id="secteur_recherche" name="secteur_recherche" value="<?= e($formData['secteur_recherche']) ?>" required>
                        <div class="form-error"><?= e($errors['secteur_recherche'] ?? '') ?></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="budget_estime">Budget estimé <span>*</span></label>
                        <input class="form-control" id="budget_estime" name="budget_estime" value="<?= e($formData['budget_estime']) ?>" required>
                        <div class="form-error"><?= e($errors['budget_estime'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="apport_personnel">Apport personnel</label>
                        <input class="form-control" id="apport_personnel" name="apport_personnel" value="<?= e($formData['apport_personnel']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="situation_professionnelle">Situation professionnelle</label>
                        <input class="form-control" id="situation_professionnelle" name="situation_professionnelle" value="<?= e($formData['situation_professionnelle']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="delai_projet">Délai du projet <span>*</span></label>
                        <select class="form-control" id="delai_projet" name="delai_projet" required>
                            <option value="">Sélectionnez</option>
                            <?php foreach (['Immédiat', '1 à 3 mois', '3 à 6 mois', '6 à 12 mois', 'Plus d’un an'] as $option): ?>
                                <option value="<?= e($option) ?>" <?= $formData['delai_projet'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-error"><?= e($errors['delai_projet'] ?? '') ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="message">Message libre</label>
                    <textarea class="form-control" id="message" name="message" rows="5" placeholder="Décrivez votre projet, vos questions ou vos priorités."><?= e($formData['message']) ?></textarea>
                </div>
                <div class="form-group form-group--checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" name="rgpd" value="1" <?= $formData['rgpd'] !== '' ? 'checked' : '' ?> required>
                        <span>J’accepte que mes données soient utilisées pour traiter ma demande, conformément à la <a href="/politique-confidentialite" target="_blank">politique de confidentialité</a>. <span aria-hidden="true">*</span></span>
                    </label>
                    <div class="form-error"><?= e($errors['rgpd'] ?? '') ?></div>
                </div>
                <p class="form-hint">Vos informations restent confidentielles et utilisées uniquement pour votre accompagnement financement.</p>
                <button type="submit" class="btn btn--primary btn--lg btn--full">Demander un accompagnement</button>
            </form>
        </div>
    </div>
</section>

<section class="section" id="faq-financement">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">FAQ financement immobilier</span>
            <h2 class="section-title">Questions fréquentes</h2>
        </div>
        <div class="fin-faq" data-animate>
            <?php foreach ([
                ['Pourquoi faire une demande de financement avant d’acheter ?', 'Cela vous permet de définir un budget réaliste, de cibler les bons biens et de sécuriser plus rapidement votre projet immobilier.'],
                ['Quels éléments préparer pour une demande de prêt immobilier ?', 'Une pièce d’identité, vos revenus, vos charges et quelques informations sur votre projet suffisent pour démarrer sereinement.'],
                ['Peut-on faire une demande même sans dossier complet ?', 'Oui. L’objectif est justement de vous aider à structurer votre dossier étape par étape, sans pression.'],
                ['En combien de temps suis-je recontacté ?', 'Vous êtes recontacté rapidement pour un premier échange personnalisé sur votre situation.'],
                ['Est-ce utile pour un premier achat ?', 'Oui, c’est particulièrement utile pour un premier achat afin de mieux comprendre les étapes et d’éviter les erreurs classiques.'],
                ['Peut-on être accompagné pour mieux définir son budget ?', 'Absolument. L’accompagnement financement sert précisément à clarifier votre capacité d’achat et à prioriser votre recherche.'],
            ] as [$q, $r]): ?>
                <details class="fin-faq-item">
                    <summary><?= e($q) ?></summary>
                    <p><?= e($r) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Donnez un cadre clair à votre projet immobilier</h2>
        <p>Déposez votre demande de financement et avancez avec une vision plus sereine.</p>
        <div class="cta-banner__actions">
            <a href="#formulaire-financement" class="btn btn--accent btn--lg">Envoyer ma demande</a>
        </div>
    </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Pourquoi faire une demande de financement avant d’acheter ?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Cela permet de définir un budget réaliste, de cibler les bons biens et de sécuriser plus rapidement le projet immobilier."
      }
    },
    {
      "@type": "Question",
      "name": "Quels éléments préparer pour une demande de prêt immobilier ?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Une pièce d’identité, des justificatifs de revenus, vos charges et les premières informations sur votre projet suffisent pour démarrer."
      }
    },
    {
      "@type": "Question",
      "name": "Peut-on faire une demande même sans dossier complet ?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Oui, l’accompagnement permet justement de structurer progressivement le dossier et de lever les blocages administratifs."
      }
    }
  ]
}
</script>
