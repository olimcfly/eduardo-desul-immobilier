<?php
/**
 * PAGE DE CONFIRMATION APRÈS CAPTURE
 * /front/renderers/capture-merci.php
 * URL : /merci?guide={slug}&prenom={prenom}
 * Routé depuis front/page.php → cas 'merci'
 */

// ── Catalogue guides (nom + couleur par slug) ──
$guides_meta = [
    'guide-vente-prix'             => ['name' => 'Comment fixer le juste prix de vente',        'icon' => '💰', 'cf' => '#d4a574', 'ct' => '#c9913b', 'cl' => '#fdf6ee'],
    'guide-vente-preparation'      => ['name' => 'Préparer son bien avant la vente',             'icon' => '🏡', 'cf' => '#d4a574', 'ct' => '#c9913b', 'cl' => '#fdf6ee'],
    'guide-vente-documents'        => ['name' => 'Tous les documents obligatoires pour vendre',  'icon' => '📋', 'cf' => '#d4a574', 'ct' => '#c9913b', 'cl' => '#fdf6ee'],
    'guide-vente-negociation'      => ['name' => 'Négocier sans brader son bien',                'icon' => '🤝', 'cf' => '#d4a574', 'ct' => '#c9913b', 'cl' => '#fdf6ee'],
    'guide-vente-delais'           => ['name' => 'Comprendre les délais de vente',               'icon' => '📅', 'cf' => '#d4a574', 'ct' => '#c9913b', 'cl' => '#fdf6ee'],
    'guide-achat-budget'           => ['name' => 'Calculer son budget d\'achat réel',            'icon' => '🧮', 'cf' => '#1a4d7a', 'ct' => '#2d7dd2', 'cl' => '#eef4fb'],
    'guide-achat-visite'           => ['name' => '30 points à vérifier lors d\'une visite',      'icon' => '🔍', 'cf' => '#1a4d7a', 'ct' => '#2d7dd2', 'cl' => '#eef4fb'],
    'guide-achat-pret'             => ['name' => 'Obtenir le meilleur taux de crédit',           'icon' => '🏦', 'cf' => '#1a4d7a', 'ct' => '#2d7dd2', 'cl' => '#eef4fb'],
    'guide-achat-offre'            => ['name' => 'Faire une offre d\'achat efficace',            'icon' => '✍️', 'cf' => '#1a4d7a', 'ct' => '#2d7dd2', 'cl' => '#eef4fb'],
    'guide-achat-quartiers'        => ['name' => 'Les quartiers de Bordeaux décryptés',          'icon' => '🗺️', 'cf' => '#1a4d7a', 'ct' => '#2d7dd2', 'cl' => '#eef4fb'],
    'guide-proprio-fiscalite'      => ['name' => 'Fiscalité immobilière : ce qu\'il faut savoir','icon' => '📊', 'cf' => '#059669', 'ct' => '#34d399', 'cl' => '#ecfdf5'],
    'guide-proprio-location'       => ['name' => 'Louer son bien sans stress',                   'icon' => '🔑', 'cf' => '#059669', 'ct' => '#34d399', 'cl' => '#ecfdf5'],
    'guide-proprio-travaux'        => ['name' => 'Valoriser son patrimoine par les travaux',     'icon' => '🔨', 'cf' => '#059669', 'ct' => '#34d399', 'cl' => '#ecfdf5'],
    'guide-proprio-investissement' => ['name' => 'Investir dans l\'immobilier locatif à Bordeaux','icon'=> '📈', 'cf' => '#059669', 'ct' => '#34d399', 'cl' => '#ecfdf5'],
];

// ── Paramètres URL ──
$slug   = preg_replace('/[^a-z0-9-]/', '', strtolower(trim($_GET['guide']  ?? '')));
$prenom = htmlspecialchars(trim($_GET['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
$prenom = $prenom ?: 'vous';

$guide  = isset($guides_meta[$slug]) ? $guides_meta[$slug] : null;
$cf     = $guide ? $guide['cf'] : '#1a4d7a';
$ct     = $guide ? $guide['ct'] : '#2d7dd2';
$cl     = $guide ? $guide['cl'] : '#eef4fb';
$icon   = $guide ? $guide['icon'] : '📥';
$gname  = $guide ? $guide['name'] : 'votre guide';

// Guides suggérés (3 guides d'un autre slug)
$suggestions = array_filter($guides_meta, fn($k) => $k !== $slug, ARRAY_FILTER_USE_KEY);
$suggestions = array_slice($suggestions, 0, 3, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Merci <?= $prenom ?> ! Votre guide arrive — Eduardo De Sul Immobilier</title>
<meta name="robots" content="noindex, nofollow">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --primary: #1a4d7a;
    --gold:    #d4a574;
    --bg:      #f9f6f3;
    --cf:      <?= $cf ?>;
    --ct:      <?= $ct ?>;
    --cl:      <?= $cl ?>;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: #1e293b;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ── HEADER ── */
.merci-header {
    background: white;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.merci-logo {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
    text-decoration: none;
}
.merci-logo span { color: var(--gold); }

/* ── HERO CONFIRMATION ── */
.merci-hero {
    background: linear-gradient(135deg, var(--cf), var(--ct));
    padding: 60px 24px;
    text-align: center;
    color: white;
}
.merci-check {
    width: 80px; height: 80px;
    background: rgba(255,255,255,.2);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    margin-bottom: 20px;
    animation: popIn .4s cubic-bezier(.175,.885,.32,1.275);
}
@keyframes popIn {
    0%   { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.merci-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(26px, 4vw, 38px);
    font-weight: 800;
    margin-bottom: 12px;
    line-height: 1.2;
}
.merci-hero p {
    font-size: 16px;
    opacity: .9;
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ── GUIDE CONFIRMÉ ── */
.merci-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 48px 24px 80px;
    flex: 1;
}

.merci-guide-card {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 40px;
    border: 2px solid var(--cl);
    box-shadow: 0 4px 16px rgba(0,0,0,.06);
}
.merci-guide-icon {
    font-size: 56px;
    width: 80px; height: 80px;
    background: var(--cl);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.merci-guide-info h2 {
    font-size: 17px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 6px;
    line-height: 1.3;
}
.merci-guide-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #dcfce7;
    color: #16a34a;
    font-size: 12px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 8px;
}
.merci-guide-hint {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}

/* ── ÉTAPES SUIVANTES ── */
.merci-steps {
    background: white;
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 40px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}
.merci-steps h3 {
    font-size: 17px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.merci-step-list { display: flex; flex-direction: column; gap: 14px; }
.merci-step {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}
.merci-step-num {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--cf), var(--ct));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
    flex-shrink: 0;
    margin-top: 2px;
}
.merci-step-content strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 3px;
}
.merci-step-content span {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}

/* ── CTA PRINCIPAL ── */
.merci-cta-block {
    background: linear-gradient(135deg, var(--primary), #2d7dd2);
    border-radius: 16px;
    padding: 36px 32px;
    text-align: center;
    color: white;
    margin-bottom: 40px;
}
.merci-cta-block h3 {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 10px;
    line-height: 1.3;
}
.merci-cta-block p {
    font-size: 14px;
    opacity: .85;
    margin-bottom: 24px;
    line-height: 1.6;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}
.merci-cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.merci-btn {
    padding: 13px 24px;
    border-radius: 10px;
    font-weight: 800;
    font-size: 14px;
    text-decoration: none;
    transition: .15s;
    display: inline-block;
}
.merci-btn-primary {
    background: var(--gold);
    color: white;
}
.merci-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.25); color: white; }
.merci-btn-secondary {
    background: rgba(255,255,255,.15);
    color: white;
    border: 2px solid rgba(255,255,255,.3);
}
.merci-btn-secondary:hover { background: rgba(255,255,255,.25); color: white; }

/* ── AUTRES GUIDES ── */
.merci-autres h3 {
    font-size: 17px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.merci-sugg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 14px;
}
.merci-sugg-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 20px;
    text-decoration: none;
    color: inherit;
    transition: .2s;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.merci-sugg-card:hover {
    border-color: var(--cf);
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
}
.merci-sugg-icon {
    font-size: 28px;
    width: 46px; height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.merci-sugg-name {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.4;
    margin-bottom: 6px;
}
.merci-sugg-link {
    font-size: 11px;
    color: var(--cf);
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ── FOOTER ── */
.merci-footer {
    text-align: center;
    padding: 24px;
    font-size: 12px;
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    background: white;
}
.merci-footer a { color: #64748b; text-decoration: none; }
.merci-footer a:hover { text-decoration: underline; }

@media (max-width: 600px) {
    .merci-guide-card { flex-direction: column; text-align: center; }
    .merci-guide-icon { margin: 0 auto; }
    .merci-steps { padding: 20px; }
    .merci-cta-block { padding: 28px 20px; }
}
</style>
</head>
<body>

<!-- Header -->
<header class="merci-header">
    <a href="/" class="merci-logo">Eduardo De Sul<span>.</span></a>
    <a href="/" style="font-size:13px;color:#64748b;text-decoration:none;font-weight:600;">← Retour au site</a>
</header>

<!-- Hero -->
<div class="merci-hero">
    <div class="merci-check">✅</div>
    <h1>Merci <?= $prenom ?> !<br>Votre guide est en route.</h1>
    <p>Vérifiez votre boîte email dans quelques minutes.<br>Pensez à regarder vos spams si vous ne le voyez pas.</p>
</div>

<!-- Contenu -->
<div class="merci-wrap">

    <!-- Guide confirmé -->
    <div class="merci-guide-card">
        <div class="merci-guide-icon"><?= $icon ?></div>
        <div class="merci-guide-info">
            <div class="merci-guide-status">
                <span>✉️</span> Guide en cours d'envoi
            </div>
            <h2>« <?= htmlspecialchars($gname) ?> »</h2>
            <p class="merci-guide-hint">
                Un email contenant votre guide PDF vous a été envoyé.<br>
                Si vous ne le recevez pas sous 5 minutes, vérifiez vos spams ou <a href="tel:0624105816" style="color:<?= $cf ?>;font-weight:600;">appelez-nous</a>.
            </p>
        </div>
    </div>

    <!-- Étapes suivantes -->
    <div class="merci-steps">
        <h3>📌 Et maintenant ?</h3>
        <div class="merci-step-list">
            <div class="merci-step">
                <div class="merci-step-num">1</div>
                <div class="merci-step-content">
                    <strong>Lisez votre guide</strong>
                    <span>Prenez le temps de parcourir les chapitres dans l'ordre. Chaque section est actionnable immédiatement.</span>
                </div>
            </div>
            <div class="merci-step">
                <div class="merci-step-num">2</div>
                <div class="merci-step-content">
                    <strong>Faites une estimation gratuite de votre bien</strong>
                    <span>Connaître la valeur réelle de votre bien est la première étape de tout projet immobilier. C'est gratuit et sans engagement.</span>
                </div>
            </div>
            <div class="merci-step">
                <div class="merci-step-num">3</div>
                <div class="merci-step-content">
                    <strong>Posez vos questions à Eduardo</strong>
                    <span>Une question sur votre projet ? Eduardo répond personnellement. Disponible au <a href="tel:0624105816" style="color:<?= $cf ?>;font-weight:600;">06 24 10 58 16</a>.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA principal -->
    <div class="merci-cta-block">
        <h3>Votre projet mérite<br>un accompagnement sur-mesure</h3>
        <p>Eduardo est conseiller immobilier indépendant à Bordeaux et Blanquefort. Prenez rendez-vous pour une consultation gratuite et sans engagement.</p>
        <div class="merci-cta-btns">
            <a href="/estimation-gratuite" class="merci-btn merci-btn-primary">
                🏠 Estimer mon bien gratuitement
            </a>
            <a href="/" class="merci-btn merci-btn-secondary">
                Explorer le site →
            </a>
        </div>
    </div>

    <!-- Autres guides -->
    <?php if (!empty($suggestions)): ?>
    <div class="merci-autres">
        <h3>📚 D'autres guides qui pourraient vous intéresser</h3>
        <div class="merci-sugg-grid">
            <?php foreach ($suggestions as $slug_s => $g_s): ?>
            <a href="/capture/<?= $slug_s ?>" class="merci-sugg-card">
                <div class="merci-sugg-icon" style="background:<?= $g_s['cl'] ?>"><?= $g_s['icon'] ?></div>
                <div>
                    <div class="merci-sugg-name"><?= htmlspecialchars($g_s['name']) ?></div>
                    <div class="merci-sugg-link" style="color:<?= $g_s['cf'] ?>">
                        Télécharger →
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Footer -->
<footer class="merci-footer">
    <p>© <?= date('Y') ?> Eduardo De Sul Immobilier · 12A rue du Commandant Charcot, 33290 Blanquefort</p>
    <p style="margin-top:6px;">
        <a href="/mentions-legales">Mentions légales</a> ·
        <a href="/politique-de-confidentialite">Confidentialité</a> ·
        <a href="/">Retour au site</a>
    </p>
</footer>

</body>
</html>