<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim((string)($_POST['email'] ?? ''));
    $prenom = trim((string)($_POST['prenom'] ?? ''));

    if ($email !== '' && $prenom !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_CONTACT,
            'pipeline' => LeadService::SOURCE_CONTACT,
            'stage' => 'a_traiter',
            'first_name' => $prenom,
            'last_name' => trim((string)($_POST['nom'] ?? '')),
            'email' => $email,
            'phone' => trim((string)($_POST['telephone'] ?? '')),
            'intent' => trim((string)($_POST['sujet'] ?? 'Contact général')),
            'notes' => trim((string)($_POST['message'] ?? '')),
            'consent' => !empty($_POST['rgpd']),
            'metadata' => [
                'origin_path' => $_SERVER['REQUEST_URI'] ?? '/contact',
            ],
        ]);

        redirect('/merci');
    }
}

$pageTitle = 'Contact — Eduardo Desul Immobilier';
$metaDesc  = 'Contactez Eduardo Desul, conseiller immobilier à Bordeaux. Réponse sous 24h.';
$extraCss  = ['/assets/css/contact.css'];
$extraJs   = ['/assets/js/contact.js'];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a><span>Contact</span>
        </nav>
        <h1>Contactez-moi</h1>
        <p>Je vous réponds personnellement dans les 24 heures. N'hésitez pas à me poser toutes vos questions.</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="contact-layout">

            <!-- Formulaire -->
            <div class="contact-form-box">
                <h2>Envoyez-moi un message</h2>
                <p>Décrivez votre projet ou posez votre question.</p>

                <form id="contact-form" action="/contact" method="POST" novalidate>
                    <?= csrfField() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="prenom">Prénom <span aria-hidden="true">*</span></label>
                            <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Jean" required autocomplete="given-name" aria-describedby="prenom-err">
                            <div class="form-error" id="prenom-err"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="nom">Nom <span aria-hidden="true">*</span></label>
                            <input type="text" id="nom" name="nom" class="form-control" placeholder="Dupont" required autocomplete="family-name" aria-describedby="nom-err">
                            <div class="form-error" id="nom-err"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="email">Email <span>*</span></label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="jean@exemple.fr" required autocomplete="email">
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="06 00 00 00 00" autocomplete="tel">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sujet">Sujet <span>*</span></label>
                        <select id="sujet" name="sujet" class="form-control" required>
                            <option value="">— Choisissez un sujet —</option>
                            <option value="achat">Projet d'achat</option>
                            <option value="vente">Projet de vente</option>
                            <option value="estimation">Estimation de bien</option>
                            <option value="location">Location</option>
                            <option value="investissement">Investissement locatif</option>
                            <option value="autre">Autre question</option>
                        </select>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="message">Message <span>*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="5" placeholder="Décrivez votre projet…" required></textarea>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;gap:.6rem;align-items:flex-start;font-size:.85rem;cursor:pointer">
                            <input type="checkbox" name="rgpd" required style="margin-top:.2rem;flex-shrink:0">
                            <span>J'accepte que mes données soient utilisées pour traiter ma demande, conformément à la <a href="/politique-confidentialite" target="_blank" style="color:var(--clr-primary)">politique de confidentialité</a>. <span style="color:var(--clr-danger)">*</span></span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn--primary btn--lg btn--full">
                        Envoyer mon message
                    </button>
                </form>
            </div>

            <!-- Informations -->
            <div class="contact-info">
                <div class="contact-info-box">
                    <h3>Coordonnées</h3>
                    <div class="info-item">
                        <span class="info-icon">📍</span>
                        <div class="info-text">
                            <strong>Adresse</strong>
                            <p><?= e(APP_ADDRESS) ?></p>
                        </div>
                    </div>
                    <?php if (APP_PHONE): ?>
                    <div class="info-item">
                        <span class="info-icon">📞</span>
                        <div class="info-text">
                            <strong>Téléphone</strong>
                            <p><a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"><?= e(APP_PHONE) ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-icon">✉️</span>
                        <div class="info-text">
                            <strong>Email</strong>
                            <p><a href="mailto:<?= e(APP_EMAIL) ?>"><?= e(APP_EMAIL) ?></a></p>
                        </div>
                    </div>
                </div>

                <div class="contact-info-box">
                    <h3>Horaires</h3>
                    <div class="horaires-grid">
                        <span class="jour">Lundi – Vendredi</span><span class="heure">9h – 19h</span>
                        <span class="jour">Samedi</span><span class="heure">10h – 17h</span>
                        <span class="jour">Dimanche</span><span class="heure">Fermé</span>
                    </div>
                    <p style="margin-top:1rem;font-size:.8rem;color:var(--clr-text-muted)">Des rendez-vous en dehors de ces créneaux sont possibles sur demande.</p>
                </div>

                <div class="map-placeholder" aria-label="Carte de localisation">
                    📍 Carte Google Maps ici
                </div>

                <div style="background:var(--clr-primary);color:white;border-radius:var(--radius-lg);padding:1.5rem;text-align:center">
                    <div style="font-size:2rem;margin-bottom:.75rem">⚡</div>
                    <h4 style="color:white;margin-bottom:.5rem">Réponse garantie sous 24h</h4>
                    <p style="font-size:.875rem;opacity:.8">Je m'engage à vous répondre personnellement dans les meilleurs délais.</p>
                </div>
            </div>

        </div>
    </div>
</section>
