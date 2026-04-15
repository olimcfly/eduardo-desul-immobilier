<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email     = trim((string) ($_POST['email'] ?? ''));
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $address   = trim((string) ($_POST['address'] ?? ''));

    if ($email !== '' && $firstName !== '' && $address !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        LeadService::capture([
            'source_type'      => LeadService::SOURCE_ESTIMATION,
            'pipeline'         => LeadService::SOURCE_ESTIMATION,
            'stage'            => 'avis_valeur',
            'priority'         => 'haute',
            'first_name'       => $firstName,
            'last_name'        => trim((string) ($_POST['last_name'] ?? '')),
            'email'            => $email,
            'phone'            => trim((string) ($_POST['phone'] ?? '')),
            'intent'           => 'Demande d\'avis de valeur',
            'property_type'    => trim((string) ($_POST['property_type'] ?? '')),
            'property_address' => $address,
            'consent'          => !empty($_POST['consent']),
        ]);

        redirect('/merci');
    }
}

$pageTitle = 'Avis de valeur gratuit — Estimation de votre bien | ' . APP_NAME . '';
$metaDesc  = 'Obtenez un avis de valeur gratuit et personnalisé pour votre bien immobilier. <?= ADVISOR_NAME ?>, expert indépendant, analyse votre bien et vous remet une estimation détaillée.';
$extraCss  = ['/assets/css/estimation.css'];
?>
<section class="hero hero--light" aria-labelledby="avis-valeur-hero">
    <div class="container">
        <div class="hero__content" style="max-width:700px">
            <span class="section-label hero__label">Avis de valeur</span>
            <h1 id="avis-valeur-hero">Quelle est la valeur réelle de votre bien ?</h1>
            <p class="hero__subtitle">Obtenez une estimation précise et gratuite, réalisée par un expert immobilier indépendant qui connaît parfaitement le marché aixois.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:860px">

        <div class="grid-2" style="gap:3rem;align-items:start">

            <div>
                <h2>Pourquoi demander un avis de valeur ?</h2>
                <p style="color:var(--clr-text-muted);margin-bottom:1.5rem">Un avis de valeur vous permet de connaître le prix réel du marché avant de vendre ou d'acheter, et d'éviter les mauvaises surprises.</p>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:.75rem">
                    <?php foreach ([
                        'Estimation personnalisée basée sur les ventes récentes du secteur',
                        'Analyse des atouts et des points d\'amélioration de votre bien',
                        'Conseil sur le prix de mise en vente optimal',
                        'Résultat remis sous 48h, sans engagement',
                    ] as $item): ?>
                    <li style="display:flex;gap:.75rem;align-items:flex-start">
                        <span style="color:var(--clr-primary);font-size:1.25rem;flex-shrink:0">✓</span>
                        <span><?= e($item) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <div class="card" style="padding:2rem">
                    <h3 style="margin-bottom:1.5rem">Demander mon avis de valeur gratuit</h3>
                    <form method="POST" class="estimation-form">
                        <?= csrfField() ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                            <div class="form-group">
                                <label class="form-label">Prénom *</label>
                                <input class="form-control" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nom</label>
                                <input class="form-control" name="last_name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input class="form-control" type="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Téléphone</label>
                                <input class="form-control" type="tel" name="phone">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adresse du bien *</label>
                            <input class="form-control" name="address" placeholder="Ex : 12 rue Mirabeau, Aix-en-Provence" required>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                            <div class="form-group">
                                <label class="form-label">Type de bien *</label>
                                <select class="form-control" name="property_type" required>
                                    <option value="">Sélectionner</option>
                                    <option value="appartement">Appartement</option>
                                    <option value="maison">Maison</option>
                                    <option value="villa">Villa</option>
                                    <option value="terrain">Terrain</option>
                                    <option value="local-commercial">Local commercial</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Surface (m²)</label>
                                <input class="form-control" type="number" name="surface" min="10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="display:flex;gap:.5rem;align-items:flex-start;cursor:pointer">
                                <input type="checkbox" name="consent" required style="margin-top:.25rem;flex-shrink:0">
                                <span style="font-size:.875rem">J'accepte que mes données soient traitées pour recevoir mon avis de valeur. <a href="<?= url('/politique-confidentialite') ?>">Politique de confidentialité</a></span>
                            </label>
                        </div>
                        <button class="btn btn--primary" type="submit" style="width:100%">Recevoir mon avis de valeur gratuit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
