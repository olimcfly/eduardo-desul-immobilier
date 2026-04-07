<?php
DvfEstimatorService::ensureTables();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $phone = trim((string) ($_POST['phone'] ?? ''));

    if ($fullName !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $propertyType = trim((string) ($_POST['property_type'] ?? ''));
        $surface = (float) ($_POST['surface'] ?? 0);

        $estimate = DvfEstimatorService::estimate([
            'property_type' => $propertyType,
            'surface' => $surface,
            'city' => trim((string) ($_POST['city'] ?? '')),
            'lat' => ($_POST['lat'] ?? null),
            'lng' => ($_POST['lng'] ?? null),
        ]);

        DvfEstimatorService::saveRequest([
            'request_type' => 'rdv',
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'property_type' => $propertyType,
            'surface' => $surface,
            'rooms' => trim((string) ($_POST['rooms'] ?? '')),
            'address_raw' => trim((string) ($_POST['address'] ?? '')),
            'address_norm' => trim((string) ($_POST['address'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
            'lat' => ($_POST['lat'] ?? null),
            'lng' => ($_POST['lng'] ?? null),
            'metadata' => [
                'state' => trim((string) ($_POST['state'] ?? '')),
                'availability' => trim((string) ($_POST['availability'] ?? '')),
                'note' => trim((string) ($_POST['note'] ?? '')),
            ],
        ], $estimate);

        LeadService::capture([
            'source_type' => LeadService::SOURCE_ESTIMATION,
            'pipeline' => LeadService::SOURCE_ESTIMATION,
            'stage' => 'rdv_a_planifier',
            'priority' => 'haute',
            'first_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'intent' => 'Demande rendez-vous estimation affinée',
            'property_type' => $propertyType,
            'property_address' => trim((string) ($_POST['address'] ?? '')),
            'consent' => !empty($_POST['rgpd']),
            'metadata' => ['from' => 'prendre-rendez-vous'],
        ]);

        Session::flash('success', 'Merci, votre demande de rendez-vous est enregistrée.');
        redirect('/merci');
    }

    Session::flash('error', 'Merci de compléter les champs requis.');
}

$pageTitle = 'Prendre rendez-vous — Estimation affinée';
$metaDesc = 'Demandez une estimation immobilière affinée avec un conseiller.';
?>
<section class="section">
    <div class="container" style="max-width:860px">
        <h1>Prendre rendez-vous</h1>
        <p>Demandez une estimation affinée avec un conseiller immobilier.</p>

        <form method="POST" class="card" style="padding:1.25rem;display:grid;gap:1rem">
            <?= csrfField() ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Type de bien</label>
                    <select name="property_type" class="form-control" required>
                        <option value="appartement">Appartement</option>
                        <option value="maison">Maison</option>
                        <option value="local">Local commercial</option>
                        <option value="terrain">Terrain</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Code postal</label>
                    <input type="text" name="postal_code" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Surface (m²)</label>
                    <input type="number" name="surface" class="form-control" min="9" max="500" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pièces</label>
                    <input type="number" name="rooms" class="form-control" min="1" max="20">
                </div>
                <div class="form-group">
                    <label class="form-label">État du bien</label>
                    <select name="state" class="form-control">
                        <option value="">—</option>
                        <option value="a_renover">À rénover</option>
                        <option value="bon_etat">Bon état</option>
                        <option value="neuf">Neuf</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Disponibilités</label>
                <input type="text" name="availability" class="form-control" placeholder="Ex: semaine après 18h">
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea name="note" class="form-control" rows="4"></textarea>
            </div>
            <input type="hidden" name="lat" value="">
            <input type="hidden" name="lng" value="">
            <label style="display:flex;gap:.5rem;align-items:flex-start">
                <input type="checkbox" name="rgpd" required>
                <span>J’accepte la politique de confidentialité.</span>
            </label>
            <button type="submit" class="btn btn--accent">Confirmer la demande de rendez-vous</button>
        </form>
    </div>
</section>
