<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim((string)($_POST['email'] ?? ''));
    $prenom = trim((string)($_POST['prenom'] ?? ''));

    if ($email !== '' && $prenom !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_RESSOURCE,
            'pipeline' => LeadService::SOURCE_RESSOURCE,
            'stage' => 'nurturing',
            'first_name' => $prenom,
            'email' => $email,
            'intent' => 'Téléchargement de ressource',
            'consent' => !empty($_POST['rgpd']),
            'metadata' => [
                'profil' => trim((string)($_POST['profil'] ?? '')),
                'ressource' => 'Guide Complet de l\'Immobilier Pays d\'Aix',
                'origin_path' => $_SERVER['REQUEST_URI'] ?? '/guide-offert',
            ],
        ]);

        redirect('/merci');
    }
}

$pageTitle  = 'Guide immobilier gratuit — ' . ADVISOR_NAME . '';
$metaDesc   = 'Recevez gratuitement le guide immobilier de <?= ADVISOR_NAME ?> : conseils, tendances, stratégies pour réussir votre projet.';
$metaRobots = 'noindex';
$bodyClass  = 'page-capture';
?>
<section style="min-height:calc(100vh - var(--header-h));display:flex;align-items:center;padding-block:3rem;background:linear-gradient(135deg,#1a3c5e 0%,#c9a84c 100%)">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 440px;gap:3rem;align-items:center">
            <div style="color:white">
                <span class="section-label" style="color:rgba(255,255,255,.9)">Offert · Téléchargeable instantanément</span>
                <h1 style="color:white;margin-bottom:1rem">Le Guide Complet<br>de l'Immobilier Bordelais</h1>
                <p style="opacity:.85;font-size:1.05rem;margin-bottom:2rem">Tout ce qu'il faut savoir pour réussir votre projet immobilier à Aix-en-Provence en 2026 : prix, quartiers, financement, stratégies.</p>

                <div style="background:rgba(255,255,255,.1);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:2rem">
                    <div style="font-weight:700;margin-bottom:1rem">Au programme :</div>
                    <?php foreach ([
                        'Analyse des prix par quartier',
                        'Les erreurs à éviter lors d\'un achat',
                        'Comment vendre 15% plus cher',
                        'Investissement locatif : les zones gagnantes',
                        'Financement & taux : décryptage 2026',
                    ] as $item): ?>
                    <div style="display:flex;gap:.6rem;margin-bottom:.6rem;font-size:.9rem;opacity:.9">
                        <span style="color:var(--clr-accent);font-weight:700">→</span><?= e($item) ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex;gap:.75rem;align-items:center">
                    <div style="font-size:2.5rem">📚</div>
                    <div>
                        <div style="font-weight:600">Guide PDF · 32 pages</div>
                        <div style="font-size:.8rem;opacity:.7">Rédigé par <?= ADVISOR_NAME ?> · Édition 2026</div>
                    </div>
                </div>
            </div>

            <div style="background:white;border-radius:var(--radius-xl);padding:2.5rem;box-shadow:var(--shadow-lg)">
                <h2 style="margin-bottom:.5rem;font-size:1.4rem">Recevoir le guide gratuit</h2>
                <p style="color:var(--clr-text-muted);font-size:.875rem;margin-bottom:1.75rem">Entrez votre email ci-dessous pour recevoir votre guide instantanément.</p>

                <form action="/guide-offert" method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label" for="g-prenom">Prénom <span>*</span></label>
                        <input type="text" id="g-prenom" name="prenom" class="form-control" placeholder="Marie" required autocomplete="given-name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g-email">Email <span>*</span></label>
                        <input type="email" id="g-email" name="email" class="form-control" placeholder="marie@exemple.fr" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Votre profil</label>
                        <select name="profil" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="acheteur">Je cherche à acheter</option>
                            <option value="vendeur">Je cherche à vendre</option>
                            <option value="investisseur">Je veux investir</option>
                            <option value="curieux">Simple curiosité</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;gap:.5rem;align-items:flex-start;font-size:.82rem;cursor:pointer">
                            <input type="checkbox" name="rgpd" required style="margin-top:.2rem;flex-shrink:0">
                            <span>J'accepte de recevoir le guide et les actualités immobilières d'Pascal. <a href="/politique-confidentialite" target="_blank" style="color:var(--clr-primary)">Politique de confidentialité</a>. <span style="color:var(--clr-danger)">*</span></span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn--primary btn--lg btn--full">
                        📧 Recevoir mon guide gratuit
                    </button>
                    <p style="text-align:center;font-size:.78rem;color:var(--clr-text-muted);margin-top:.75rem">🔒 Sans spam · Désabonnement en 1 clic</p>
                </form>
            </div>
        </div>
    </div>
</section>
