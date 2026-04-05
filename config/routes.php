<?php
// ============================================================
// ROUTES FRONT-END
// ============================================================

/** @var Router $router */

require_once ROOT_PATH . '/admin/modules/cms/services/CmsService.php';
require_once ROOT_PATH . '/admin/modules/cms/controllers/PageController.php';

if (!function_exists('resourceGuidesData')) {
    function resourceGuidesData(): array
    {
        static $guides = null;
        if ($guides === null) {
            $guides = require ROOT_PATH . '/public/ressources/guides-data.php';
        }
        return $guides;
    }
}

if (!function_exists('findResourceGuide')) {
    function findResourceGuide(string $persona, string $slug): ?array
    {
        $catalog = resourceGuidesData();
        if (!isset($catalog[$persona])) {
            return null;
        }

        foreach ($catalog[$persona]['guides'] as $guide) {
            if (($guide['slug'] ?? '') === $slug) {
                return [
                    'persona' => $persona,
                    'persona_label' => $catalog[$persona]['label'] ?? ucfirst($persona),
                    'guide' => $guide,
                ];
            }
        }
        return null;
    }
}

if (!function_exists('captureGuideLead')) {
    function captureGuideLead(array $guideContext): void
    {
        verifyCsrf();

        $nom       = trim((string) ($_POST['nom'] ?? ''));
        $email     = strtolower(trim((string) ($_POST['email'] ?? '')));
        $telephone = trim((string) ($_POST['telephone'] ?? ''));
        $message   = trim((string) ($_POST['message'] ?? ''));

        if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Merci de renseigner un nom et un email valide.');
            redirect('/ressources/guides/' . rawurlencode($guideContext['persona']) . '/' . rawurlencode($guideContext['guide']['slug']));
        }

        $pdo = db();
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS leads (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                source VARCHAR(50) NOT NULL DEFAULT 'ressources',
                nom VARCHAR(190) NOT NULL,
                email VARCHAR(190) NOT NULL,
                telephone VARCHAR(40) NULL,
                message TEXT NULL,
                persona VARCHAR(100) NULL,
                guide_slug VARCHAR(150) NULL,
                guide_titre VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_created_at (created_at),
                INDEX idx_source (source),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $stmt = $pdo->prepare(
            'INSERT INTO leads (source, nom, email, telephone, message, persona, guide_slug, guide_titre, created_at)
             VALUES (:source, :nom, :email, :telephone, :message, :persona, :guide_slug, :guide_titre, NOW())'
        );
        $stmt->execute([
            ':source'      => 'ressources_guide',
            ':nom'         => mb_substr($nom, 0, 190),
            ':email'       => mb_substr($email, 0, 190),
            ':telephone'   => $telephone !== '' ? mb_substr($telephone, 0, 40) : null,
            ':message'     => $message !== '' ? mb_substr($message, 0, 3000) : null,
            ':persona'     => $guideContext['persona_label'],
            ':guide_slug'  => $guideContext['guide']['slug'],
            ':guide_titre' => $guideContext['guide']['title'],
        ]);

        Session::flash('success', 'Merci ! Votre demande a bien été enregistrée. Eduardo vous recontacte rapidement.');
        redirect('/ressources/guides/' . rawurlencode($guideContext['persona']) . '/' . rawurlencode($guideContext['guide']['slug']));
    }
}

// ── Accueil ──────────────────────────────────────────────────
$router->get('/', fn() => page('pages/home'), 'home');

// ── Pages statiques ──────────────────────────────────────────
$router->get('/a-propos',   fn() => page('pages/a-propos'),   'a-propos');
$router->get('/services',   fn() => page('pages/services'),   'services');
$router->get('/contact',    fn() => page('pages/contact'),    'contact');
$router->post('/contact',   fn() => page('pages/contact'),    'contact.post');
$router->get('/estimation', fn() => page('pages/estimation'), 'estimation');
$router->post('/estimation',fn() => page('pages/estimation'), 'estimation.post');
$router->get('/avis',       fn() => page('pages/avis'),       'avis');

// ── Biens immobiliers ────────────────────────────────────────
$router->get('/biens',              fn() => page('pages/biens'),        'biens');
$router->get('/biens/{slug}',       fn($slug) => page('pages/biens', ['slug' => $slug]), 'bien.detail');

// ── Blog ─────────────────────────────────────────────────────
$router->get('/blog',               fn() => page('blog/index'),         'blog');
$router->get('/blog/{slug}',        fn($slug) => page('blog/article', ['slug' => $slug]), 'blog.article');

// ── Actualités ───────────────────────────────────────────────
$router->get('/actualites',         fn() => page('actualites/index'),   'actualites');
$router->get('/actualites/{slug}',  fn($slug) => page('actualites/article', ['slug' => $slug]), 'actualite.article');

// ── Guide local ──────────────────────────────────────────────
$router->get('/guide-local',        fn() => page('guide-local/index'),  'guide-local');
$router->get('/guide-local/{slug}', fn($slug) => page('guide-local/ville', ['slug' => $slug]), 'guide-local.ville');

// ── Ressources ───────────────────────────────────────────────
$router->get('/ressources',                fn() => page('ressources/index'),          'ressources');
$router->get('/ressources/guide-vendeur',  fn() => page('ressources/guide-vendeur'),  'guide-vendeur');
$router->get('/ressources/guide-acheteur', fn() => page('ressources/guide-acheteur'), 'guide-acheteur');
$router->get('/ressources/guides/{persona}/{slug}', function ($persona, $slug) {
    $guideContext = findResourceGuide((string) $persona, (string) $slug);
    if (!$guideContext) {
        http_response_code(404);
        echo '<h1>404 — Guide introuvable</h1>';
        return;
    }
    page('ressources/guide', ['guideContext' => $guideContext]);
}, 'ressources.guide');
$router->post('/ressources/guides/{persona}/{slug}', function ($persona, $slug) {
    $guideContext = findResourceGuide((string) $persona, (string) $slug);
    if (!$guideContext) {
        http_response_code(404);
        echo '<h1>404 — Guide introuvable</h1>';
        return;
    }
    captureGuideLead($guideContext);
}, 'ressources.guide.post');

// ── Capture leads ────────────────────────────────────────────
$router->get('/estimation-gratuite',  fn() => page('capture/estimation-gratuite'), 'capture.estimation');
$router->post('/estimation-gratuite', fn() => page('capture/estimation-gratuite'), 'capture.estimation.post');
$router->get('/guide-offert',         fn() => page('capture/guide-offert'),        'capture.guide');
$router->post('/guide-offert',        fn() => page('capture/guide-offert'),        'capture.guide.post');
$router->get('/merci',                fn() => page('capture/merci'),               'merci');

// ── Pages légales ────────────────────────────────────────────
$router->get('/mentions-legales',           fn() => page('legal/mentions-legales'),           'mentions-legales');
$router->get('/politique-confidentialite',  fn() => page('legal/politique-confidentialite'),  'politique-confidentialite');
$router->get('/politique-cookies',          fn() => page('legal/politique-cookies'),          'politique-cookies');
$router->get('/cgv',                        fn() => page('legal/cgv'),                        'cgv');

// ── CMS Admin (édition pages) ───────────────────────────────
$router->get(
    '/admin/cms/edit/{page_slug}',
    [\Admin\Modules\Cms\Controllers\PageController::class, 'edit'],
    'admin.cms.edit'
);
$router->post(
    '/admin/cms/save',
    [\Admin\Modules\Cms\Controllers\PageController::class, 'save'],
    'admin.cms.save'
);
