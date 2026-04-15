<?php
require_once ROOT_PATH . '/core/services/InstantEstimationService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $address = trim((string) ($_POST['address'] ?? ''));

    if ($email !== '' && $firstName !== '' && $address !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $requestId = InstantEstimationService::saveRequest([
            'first_name' => $firstName,
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => $email,
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'address_input' => $address,
            'address_normalized' => trim((string) ($_POST['address_normalized'] ?? '')),
            'place_id' => trim((string) ($_POST['place_id'] ?? '')),
            'lat' => (float) ($_POST['lat'] ?? 0),
            'lng' => (float) ($_POST['lng'] ?? 0),
            'property_type' => trim((string) ($_POST['property_type'] ?? '')),
            'surface' => (float) ($_POST['surface'] ?? 0),
            'status' => 'rdv_requested',
            'source' => 'rdv_page',
            'metadata' => [
                'rooms' => trim((string) ($_POST['rooms'] ?? '')),
                'condition' => trim((string) ($_POST['condition'] ?? '')),
                'availability' => trim((string) ($_POST['availability'] ?? '')),
                'notes' => trim((string) ($_POST['notes'] ?? '')),
            ],
        ]);

        LeadService::capture([
            'source_type' => LeadService::SOURCE_ESTIMATION,
            'pipeline' => LeadService::SOURCE_ESTIMATION,
            'stage' => 'rdv_a_planifier',
            'priority' => 'haute',
            'first_name' => $firstName,
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => $email,
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'intent' => 'Demande de rendez-vous estimation affinée',
            'property_type' => trim((string) ($_POST['property_type'] ?? '')),
            'property_address' => $address,
            'consent' => !empty($_POST['consent']),
            'metadata' => ['estimation_request_id' => $requestId],
        ]);

        redirect('/merci');
    }
}

$pageTitle = 'Prendre rendez-vous — Estimation affinée';
$metaDesc = 'Demandez une estimation immobilière affinée avec un conseiller.';
$extraCss = ['/assets/css/estimation.css'];
?>
<section class="section">
    <div class="container" style="max-width:860px;">
        <h1>Prendre rendez-vous</h1>
        <p class="lead">Complétez votre dossier pour une estimation affinée avec un conseiller.</p>

        <form method="POST" class="estimation-form" style="margin-top:1rem;">
            <?= csrfField() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label class="form-label">Prénom</label><input class="form-control" name="first_name" required></div>
                <div class="form-group"><label class="form-label">Nom</label><input class="form-control" name="last_name"></div>
                <div class="form-group"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
                <div class="form-group"><label class="form-label">Téléphone</label><input class="form-control" name="phone"></div>
            </div>

            <div class="form-group"><label class="form-label">Adresse du bien</label><input class="form-control" name="address" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="form-group"><label class="form-label">Type</label><select class="form-control" name="property_type" required><option value="">Sélectionner</option><option value="appartement">Appartement</option><option value="maison">Maison</option></select></div>
                <div class="form-group"><label class="form-label">Surface</label><input class="form-control" type="number" name="surface" min="10" required></div>
                <div class="form-group"><label class="form-label">Pièces</label><input class="form-control" type="number" name="rooms" min="1"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label class="form-label">État</label><select class="form-control" name="condition"><option value="">—</option><option>À rénover</option><option>Bon état</option><option>Excellent état</option></select></div>
                <div class="form-group"><label class="form-label">Disponibilité</label><input class="form-control" name="availability" placeholder="Matin / Après-midi / Soir"></div>
            </div>
            <div class="form-group"><label class="form-label">Commentaires</label><textarea class="form-control" name="notes" rows="4"></textarea></div>
            <div class="form-group"><label style="display:flex;gap:.5rem"><input type="checkbox" name="consent" required>J'accepte la politique de confidentialité.</label></div>
            <button class="btn btn--accent" type="submit">Envoyer ma demande de rendez-vous</button>
        </form>
    </div>
</section>
