<?php

declare(strict_types=1);

require_once __DIR__ . '/services/SeoService.php';
require_once __DIR__ . '/services/SitemapService.php';

$pageTitle = 'SEO';
$pageDescription = 'Attirez plus de vendeurs depuis Google';

$user = Auth::user();
$userId = (int) ($user['id'] ?? 0);
$seoService = new SeoService(db());
$stats = array_merge([
    'keywords_count' => 0,
    'top10_count' => 0,
    'villes_count' => 0,
    'villes_published' => 0,
    'sitemap_last_generated' => null,
    'sitemap_status' => 'idle',
    'sitemap_issues_count' => 0,
    'last_audit_score' => null,
], $seoService->getHubStats($userId));
$action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'index'));

function renderSeoHub(array $stats): void
{
    $stats = array_merge([
        'keywords_count' => 0,
        'top10_count' => 0,
        'villes_count' => 0,
        'villes_published' => 0,
        'sitemap_last_generated' => null,
        'sitemap_status' => 'idle',
        'sitemap_issues_count' => 0,
        'last_audit_score' => null,
    ], $stats);

    $lastAuditScore = $stats['last_audit_score'] ?? null;

    $activated  = (($stats['villes_published'] ?? 0) > 0 ? 1 : 0)
                + (($stats['keywords_count'] ?? 0)    > 0 ? 1 : 0)
                + ($lastAuditScore !== null ? 1 : 0);
    $total      = 3;
    $pct        = $total > 0 ? (int) round($activated / $total * 100) : 0;
    ?>
    <style>
        .seo-strategy-page { display:grid; gap:1.2rem; }

        .seo-hub-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 65%, #22507d 100%);
            border-radius: 16px; padding: 1.5rem 1.15rem;
            color: #fff; box-shadow: 0 12px 28px rgba(15,34,55,.25);
        }
        .seo-hub-hero-badge {
            display:inline-flex; align-items:center; gap:.45rem;
            font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
            color:#c9a84c; border:1px solid rgba(201,168,76,.35);
            background:rgba(201,168,76,.17); border-radius:999px;
            padding:.28rem .68rem; margin-bottom:.7rem;
        }
        .seo-hub-hero h1 { margin:0 0 .52rem; color:#fff; font-size:1.7rem; line-height:1.25; }
        .seo-hub-hero p  { margin:0; color:rgba(255,255,255,.76); line-height:1.6; max-width:760px; }

        .seo-hub-progress {
            display:grid; gap:.55rem;
            background:#fff; border:1px solid #e8eef7; border-radius:14px; padding:.95rem 1rem;
        }
        .seo-hub-progress-head { display:flex; justify-content:space-between; align-items:center; gap:.8rem; flex-wrap:wrap; }
        .seo-hub-progress-head strong { color:#1e293b; font-size:.95rem; }
        .seo-hub-progress-head span   { color:#64748b; font-size:.84rem; }
        .seo-hub-progress-track { height:10px; border-radius:999px; overflow:hidden; background:#eef2ff; }
        .seo-hub-progress-bar   { height:100%; border-radius:999px; background:linear-gradient(90deg,#22c55e 0%,#14b8a6 100%); }

        .seo-hub-narrative { display:grid; grid-template-columns:1fr; gap:.9rem; }
        .seo-hub-narrative-card {
            background:#fff; border:1px solid #ecf0f6; border-radius:14px; padding:.95rem 1rem;
        }
        .seo-hub-narrative-card h3 {
            margin:0 0 .4rem; display:flex; align-items:center; gap:.45rem;
            font-size:1rem; color:#0f2237;
        }
        .seo-hub-narrative-card p { margin:0; color:#4b5563; line-height:1.55; font-size:.93rem; }

        .seo-hub-pillars { display:grid; grid-template-columns:1fr; gap:1rem; }
        .seo-hub-pillar {
            background:#fff; border:1px solid #e7edf5; border-radius:16px; padding:1rem;
            box-shadow:0 6px 18px rgba(15,23,42,.05);
        }
        .seo-hub-pillar h2 { margin:0 0 .25rem; font-size:1.1rem; color:#0f172a; }
        .seo-hub-pillar > p { margin:0 0 .85rem; color:#64748b; font-size:.9rem; }

        .seo-hub-modules { display:grid; gap:.7rem; }
        .seo-hub-module {
            border:1px solid #ebf1f7; border-radius:12px; padding:.78rem .82rem;
            display:grid; gap:.55rem; background:#fbfdff;
            transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }
        .seo-hub-module:hover {
            transform:translateY(-2px); border-color:#cfdced;
            box-shadow:0 10px 20px rgba(30,41,59,.08);
        }
        .seo-hub-module-head { display:flex; justify-content:space-between; align-items:center; gap:.55rem; }
        .seo-hub-module-head h3 { margin:0; font-size:.98rem; color:#111827; }
        .seo-hub-module p   { margin:0; color:#6b7280; font-size:.88rem; line-height:1.45; }
        .seo-hub-module small { color:#9ca3af; font-size:.8rem; }

        .seo-hub-state {
            display:inline-flex; align-items:center; gap:.35rem;
            font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
            border-radius:999px; padding:.22rem .56rem;
        }
        .seo-hub-state--available { background:#dcfce7; color:#166534; }
        .seo-hub-state--soon      { background:#fef3c7; color:#92400e; }

        .seo-hub-action {
            display:inline-flex; align-items:center; gap:.45rem; justify-content:center;
            text-decoration:none; border-radius:9px; font-size:.84rem; font-weight:700;
            padding:.48rem .72rem; width:max-content; background:#0f2237; color:#fff;
            transition:background .16s ease, transform .16s ease;
        }
        .seo-hub-action:hover { background:#183455; transform:translateY(-1px); color:#fff; }

        .seo-hub-final-cta {
            background:#fff; border:1px solid #e8edf4; border-radius:14px;
            padding:1.05rem 1rem; display:grid; gap:.7rem;
        }
        .seo-hub-final-cta h2 { margin:0; font-size:1.2rem; color:#111827; }
        .seo-hub-final-cta p  { margin:0; color:#64748b; line-height:1.55; }
        .seo-hub-final-cta a {
            display:inline-flex; align-items:center; gap:.5rem; width:max-content;
            text-decoration:none; background:#c9a84c; color:#10253c; font-weight:700;
            border-radius:10px; padding:.58rem .92rem;
        }

        @media (min-width:768px) {
            .seo-hub-hero { padding:2rem 2.1rem; }
            .seo-hub-hero h1 { font-size:2rem; }
            .seo-hub-narrative { grid-template-columns:repeat(2, 1fr); }
        }
        @media (min-width:1100px) {
            .seo-hub-pillars { grid-template-columns:repeat(2, minmax(0,1fr)); }
            .seo-hub-final-cta { display:flex; align-items:center; justify-content:space-between; gap:1.2rem; }
        }
    </style>

    <section class="seo-strategy-page">

        <header class="seo-hub-hero">
            <div class="seo-hub-hero-badge"><i class="fas fa-magnifying-glass"></i> SEO local</div>
            <h1>Prenez plus de mandats grâce à Google</h1>
            <p>Structurez votre présence locale pour générer des demandes régulières.</p>
        </header>

        <section class="seo-hub-progress" aria-label="Progression SEO">
            <div class="seo-hub-progress-head">
                <strong>Progression : <?= $activated ?>/<?= $total ?> leviers activés</strong>
                <span>Commencez par un levier, puis ajoutez les suivants.</span>
            </div>
            <div class="seo-hub-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $pct ?>">
                <div class="seo-hub-progress-bar" style="width:<?= $pct ?>%;"></div>
            </div>
        </section>

        <section class="seo-hub-pillars" aria-label="Leviers SEO">

            <article class="seo-hub-pillar">
                <h2><i class="fas fa-seedling" style="color:#16a34a;"></i> Créer du contenu local</h2>
                <p>Construire une visibilité durable sur les recherches de votre zone.</p>
                <div class="seo-hub-modules">
                    <div class="seo-hub-module">
                        <div class="seo-hub-module-head">
                            <h3>Pages locales</h3>
                            <span class="seo-hub-state seo-hub-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Couvrez vos villes clés avec des pages utiles et bien référencées.</p>
                        <small><?= (int) ($stats['villes_count'] ?? 0) ?> pages créées · <?= (int) ($stats['villes_published'] ?? 0) ?> publiées</small>
                        <a href="/admin?module=seo&action=villes" class="seo-hub-action"><i class="fas fa-arrow-right"></i> Ouvrir</a>
                    </div>
                    <div class="seo-hub-module">
                        <div class="seo-hub-module-head">
                            <h3>Mots-clés</h3>
                            <span class="seo-hub-state seo-hub-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Suivez les expressions qui amènent des vendeurs sur votre site.</p>
                        <small><?= (int) ($stats['keywords_count'] ?? 0) ?> expressions suivies</small>
                        <a href="/admin?module=seo&action=keywords" class="seo-hub-action"><i class="fas fa-arrow-right"></i> Ouvrir</a>
                    </div>
                </div>
            </article>

            <article class="seo-hub-pillar">
                <h2><i class="fas fa-gauge-high" style="color:#3b82f6;"></i> Optimiser la technique</h2>
                <p>Garantir que Google peut accéder et valoriser vos pages rapidement.</p>
                <div class="seo-hub-modules">
                    <div class="seo-hub-module">
                        <div class="seo-hub-module-head">
                            <h3>Présence Google</h3>
                            <span class="seo-hub-state seo-hub-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Vérifiez et soumettez votre sitemap pour être mieux indexé.</p>
                        <small><?= (int) ($stats['sitemap_issues_count'] ?? 0) ?> point(s) à corriger</small>
                        <a href="/admin?module=seo&action=sitemap" class="seo-hub-action"><i class="fas fa-arrow-right"></i> Ouvrir</a>
                    </div>
                    <div class="seo-hub-module">
                        <div class="seo-hub-module-head">
                            <h3>Vitesse du site</h3>
                            <span class="seo-hub-state seo-hub-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Un site rapide améliore votre classement et l'expérience visiteur.</p>
                        <small>Dernier score : <?= $lastAuditScore !== null ? (int) $lastAuditScore . '/100' : 'Non mesuré' ?></small>
                        <a href="/admin?module=seo&action=performance" class="seo-hub-action"><i class="fas fa-arrow-right"></i> Ouvrir</a>
                    </div>
                    <div class="seo-hub-module">
                        <div class="seo-hub-module-head">
                            <h3>Checklist lancement SEO</h3>
                            <span class="seo-hub-state seo-hub-state--available"><i class="fas fa-clipboard-check"></i> Disponible</span>
                        </div>
                        <p>SSL, sitemaps, Analytics, Search Console, pages légales : note auto + validations manuelles.</p>
                        <small>Liste standard « launch » (anglais) pour homogénéiser tous les sites.</small>
                        <a href="/admin?module=seo&action=launch-checklist" class="seo-hub-action"><i class="fas fa-arrow-right"></i> Ouvrir la checklist</a>
                    </div>
                </div>
            </article>

        </section>

        <section class="seo-hub-final-cta">
            <div>
                <h2>Passez à l'action</h2>
                <p>Commencez par les pages locales, puis ajoutez les mots-clés et la technique.</p>
            </div>
            <a href="/admin?module=seo&action=ville-edit"><i class="fas fa-arrow-trend-up"></i> Créer ma première page locale</a>
        </section>

    </section>
    <?php
}

function renderContent(): void
{
    global $action, $stats;

    echo '<link rel="stylesheet" href="/admin/assets/css/seo.css?v=' . (int) @filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/seo.css') . '">';

    if ($action === 'sitemap') {
        require __DIR__ . '/sitemap/index.php';
    } elseif ($action === 'villes') {
        require __DIR__ . '/fiches-villes.php';
    } elseif ($action === 'performance') {
        require __DIR__ . '/performance.php';
    } elseif ($action === 'launch-checklist') {
        require __DIR__ . '/checklist-lancement.php';
    } elseif ($action === 'keywords' && is_file(__DIR__ . '/mots-cles/index.php')) {
        require __DIR__ . '/mots-cles/index.php';
    } else {
        renderSeoHub($stats);
    }

    echo '<script src="/admin/assets/js/seo.js?v=' . (int) @filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/js/seo.js') . '"></script>';
}
