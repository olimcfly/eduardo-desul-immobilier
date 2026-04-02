<?php
// ============================================================
// ROUTES FRONT-END
// ============================================================

/** @var Router $router */

// ── Accueil ──────────────────────────────────────────────────
$router->get('/', fn() => page('home'), 'home');

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
