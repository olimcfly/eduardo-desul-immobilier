<?php

declare(strict_types=1);

$pageTitle = 'Attirer';
$pageDescription = 'Générez des vendeurs qualifiés sur votre territoire';

/**
 * @return array<int, array<string, mixed>>
 */
function attirerPublishedArticlesWithPerformance(): array
{
    $pdo = db();
    $user = Auth::user();
    $websiteId = (int) ($user['website_id'] ?? 1);

    try {
        $columnsStmt = $pdo->query('SHOW COLUMNS FROM blog_articles');
        $columns = $columnsStmt ? $columnsStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    } catch (Throwable) {
        return [];
    }

    if (!$columns) {
        return [];
    }

    $viewsColumn = in_array('views_count', $columns, true)
        ? 'views_count'
        : (in_array('vues', $columns, true) ? 'vues' : null);

    $sharesColumn = in_array('shares_count', $columns, true)
        ? 'shares_count'
        : (in_array('partages', $columns, true) ? 'partages' : null);

    $selectViews = $viewsColumn ? "COALESCE(a.{$viewsColumn}, 0)" : '0';
    $selectShares = $sharesColumn ? "COALESCE(a.{$sharesColumn}, 0)" : '0';

    $sql = "
        SELECT
            a.id,
            a.titre,
            a.slug,
            a.date_publication,
            a.updated_at,
            {$selectViews} AS vues,
            {$selectShares} AS partages
        FROM blog_articles a
        WHERE a.website_id = :website_id
          AND a.statut = 'publié'
        ORDER BY COALESCE(a.date_publication, a.updated_at) DESC
        LIMIT 20
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['website_id' => $websiteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable) {
        return [];
    }
}

/**
 * @return array<int, string>
 */
function attirerNoahTopicSuggestions(): array
{
    $zoneCity = trim((string) setting('zone_city', setting('zone_ville', 'votre secteur')));
    $zoneDepartment = trim((string) setting('zone_departement', ''));
    $zoneRegion = trim((string) setting('zone_region', ''));
    $communesRaw = trim((string) setting('zone_communes', ''));

    $communes = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $communesRaw ?: '') ?: [])));
    $communeFocus = $communes[0] ?? $zoneCity;

    $zoneLabel = $zoneCity;
    if ($zoneDepartment !== '') {
        $zoneLabel .= ' (' . $zoneDepartment . ')';
    }

    $contextRegion = $zoneRegion !== '' ? " en {$zoneRegion}" : '';

    return [
        "Prix immobilier 2026 à {$zoneCity} : tendances, délais et marges de négociation",
        "Vendre rapidement à {$zoneCity}{$contextRegion} : 7 actions qui font la différence",
        "Acheter dans {$communeFocus} : quartiers à potentiel et budget moyen par type de bien",
        "Maison ou appartement à {$zoneCity} : que recherchent les acquéreurs actuellement ?",
        "Estimer son bien à {$zoneLabel} : erreurs fréquentes qui font perdre des offres",
        "Investir autour de {$zoneCity} : communes voisines à suivre en priorité",
    ];
}

function renderAttirerHub(): void
{
    ?>
    <style>
        .attirer-strategy-page { display:grid; gap:1.2rem; }
        .attirer-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 65%, #22507d 100%);
            border-radius: 16px;
            padding: 1.5rem 1.15rem;
            color: #fff;
            box-shadow: 0 12px 28px rgba(15,34,55,.25);
        }
        .attirer-hero-badge {
            display:inline-flex; align-items:center; gap:.45rem;
            font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
            color:#c9a84c; border:1px solid rgba(201,168,76,.35);
            background: rgba(201,168,76,.17); border-radius:999px;
            padding:.28rem .68rem; margin-bottom:.7rem;
        }
        .attirer-hero h1 { margin:0 0 .52rem; color:#fff; font-size:1.7rem; line-height:1.25; }
        .attirer-hero p { margin:0; color:rgba(255,255,255,.76); line-height:1.6; max-width:760px; }

        .attirer-progress {
            display:grid; grid-template-columns:1fr; gap:.55rem;
            background:#fff; border:1px solid #e8eef7; border-radius:14px; padding:.95rem 1rem;
        }
        .attirer-progress-head { display:flex; justify-content:space-between; align-items:center; gap:.8rem; }
        .attirer-progress-head strong { color:#1e293b; font-size:.95rem; }
        .attirer-progress-head span { color:#64748b; font-size:.84rem; }
        .attirer-progress-track { height:10px; border-radius:999px; overflow:hidden; background:#eef2ff; }
        .attirer-progress-bar {
            height:100%; width:34%;
            background: linear-gradient(90deg, #22c55e 0%, #14b8a6 100%);
            border-radius:999px;
        }

        .attirer-narrative {
            display:grid; grid-template-columns:1fr; gap:.9rem;
        }
        .attirer-narrative-card {
            background:#fff; border:1px solid #ecf0f6; border-radius:14px; padding:.95rem 1rem;
        }
        .attirer-narrative-card h3 {
            margin:0 0 .4rem; display:flex; align-items:center; gap:.45rem;
            font-size:1rem; color:#0f2237;
        }
        .attirer-narrative-card p { margin:0; color:#4b5563; line-height:1.55; font-size:.93rem; }
        .attirer-leviers { margin:.35rem 0 0; padding-left:1rem; color:#334155; font-size:.92rem; line-height:1.6; }

        .attirer-pillars { display:grid; grid-template-columns:1fr; gap:1rem; }
        .attirer-pillar {
            background:#fff; border:1px solid #e7edf5; border-radius:16px; padding:1rem;
            box-shadow:0 6px 18px rgba(15,23,42,.05);
        }
        .attirer-pillar h2 { margin:0 0 .25rem; font-size:1.1rem; color:#0f172a; }
        .attirer-pillar > p { margin:0 0 .85rem; color:#64748b; font-size:.9rem; }
        .attirer-modules { display:grid; gap:.7rem; }
        .attirer-module {
            border:1px solid #ebf1f7; border-radius:12px; padding:.78rem .82rem;
            display:grid; gap:.55rem; background:#fbfdff;
            transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }
        .attirer-module:hover {
            transform:translateY(-2px);
            border-color:#cfdced;
            box-shadow:0 10px 20px rgba(30,41,59,.08);
        }
        .attirer-module-head {
            display:flex; justify-content:space-between; align-items:center; gap:.55rem;
        }
        .attirer-module-head h3 { margin:0; font-size:.98rem; color:#111827; }
        .attirer-module p { margin:0; color:#6b7280; font-size:.88rem; line-height:1.45; }
        .attirer-state {
            display:inline-flex; align-items:center; gap:.35rem;
            font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
            border-radius:999px; padding:.22rem .56rem;
        }
        .attirer-state--available { background:#dcfce7; color:#166534; }
        .attirer-state--soon { background:#fef3c7; color:#92400e; }
        .attirer-module-action {
            display:inline-flex; align-items:center; gap:.45rem; justify-content:center;
            text-decoration:none; border-radius:9px; font-size:.84rem; font-weight:700;
            padding:.48rem .72rem; width:max-content; background:#0f2237; color:#fff;
            transition:background .16s ease, transform .16s ease;
        }
        .attirer-module-action:hover { background:#183455; transform:translateY(-1px); }
        .attirer-module-action[aria-disabled="true"] {
            background:#cbd5e1; color:#475569; cursor:not-allowed; pointer-events:none;
        }

        .attirer-final-cta {
            background:#fff; border:1px solid #e8edf4; border-radius:14px;
            padding:1.05rem 1rem; display:grid; gap:.7rem;
        }
        .attirer-final-cta h2 { margin:0; font-size:1.2rem; color:#111827; }
        .attirer-final-cta p { margin:0; color:#64748b; line-height:1.55; }
        .attirer-final-cta a {
            display:inline-flex; align-items:center; gap:.5rem; width:max-content;
            text-decoration:none; background:#c9a84c; color:#10253c; font-weight:700;
            border-radius:10px; padding:.58rem .92rem;
        }

        @media (min-width: 768px) {
            .attirer-hero { padding:2rem 2.1rem; }
            .attirer-hero h1 { font-size:2rem; }
            .attirer-narrative { grid-template-columns:repeat(2, 1fr); }
        }
        @media (min-width: 1100px) {
            .attirer-pillars { grid-template-columns:repeat(3, minmax(0, 1fr)); }
            .attirer-final-cta { display:flex; align-items:center; justify-content:space-between; gap:1.2rem; }
        }
    </style>

    <section class="attirer-strategy-page">
        <header class="attirer-hero">
            <div class="attirer-hero-badge"><i class="fas fa-bullseye"></i> Système d'acquisition</div>
            <h1>Attirer des vendeurs qualifiés</h1>
            <p>Mettez en place un système de visibilité pour générer des contacts régulièrement et ne plus dépendre du hasard.</p>
        </header>

        <section class="attirer-progress" aria-label="Progression du module Attirer">
            <div class="attirer-progress-head">
                <strong>Progression recommandée : 1/3 leviers activés</strong>
                <span>Commencez par un levier, puis ajoutez les suivants.</span>
            </div>
            <div class="attirer-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="34">
                <div class="attirer-progress-bar"></div>
            </div>
        </section>

        <section class="attirer-narrative">
            <article class="attirer-narrative-card">
                <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i> Le constat</h3>
                <p>Sans visibilité structurée, les conseillers enchaînent les actions isolées, subissent des périodes creuses et dépendent du bouche-à-oreille.</p>
            </article>
            <article class="attirer-narrative-card">
                <h3><i class="fas fa-diagram-project" style="color:#3b82f6;"></i> La logique</h3>
                <p>Pour stabiliser vos contacts, combinez trois leviers complémentaires :</p>
                <ul class="attirer-leviers">
                    <li>Contenu</li>
                    <li>Publicité</li>
                    <li>Présence locale</li>
                </ul>
            </article>
            <article class="attirer-narrative-card">
                <h3><i class="fas fa-chart-line" style="color:#10b981;"></i> Ce que vous gagnez</h3>
                <p>Vous obtenez une présence constante, plus de demandes entrantes et un flux de leads réguliers sur votre zone.</p>
            </article>
            <article class="attirer-narrative-card">
                <h3><i class="fas fa-play-circle" style="color:#f59e0b;"></i> ACTION</h3>
                <p>Choisissez une stratégie prioritaire ci-dessous, activez-la cette semaine et mesurez les premiers retours.</p>
            </article>
        </section>

        <section class="attirer-pillars" aria-label="Blocs stratégiques d'acquisition">
            <article class="attirer-pillar">
                <h2><i class="fas fa-seedling" style="color:#16a34a;"></i> Attirer gratuitement</h2>
                <p>Créer une visibilité durable avec des actifs qui travaillent pour vous dans le temps.</p>
                <div class="attirer-modules">
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>SEO local</h3>
                            <span class="attirer-state attirer-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Améliorez votre positionnement local pour remonter sur les recherches vendeurs.</p>
                        <a href="?module=seo" class="attirer-module-action"><i class="fas fa-arrow-right"></i> Commencer</a>
                    </div>
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Contenu &amp; articles</h3>
                            <span class="attirer-state attirer-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Publiez des contenus ciblés pour capter des recherches concrètes de propriétaires.</p>
                        <a href="?module=attirer&amp;action=contenu-articles" class="attirer-module-action"><i class="fas fa-arrow-right"></i> Commencer</a>
                    </div>
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Google My Business</h3>
                            <span class="attirer-state attirer-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Renforcez votre présence locale avec une fiche optimisée et des avis réguliers.</p>
                        <a href="?module=gmb" class="attirer-module-action"><i class="fas fa-arrow-right"></i> Commencer</a>
                    </div>
                </div>
            </article>

            <article class="attirer-pillar">
                <h2><i class="fas fa-bolt" style="color:#ef4444;"></i> Attirer rapidement</h2>
                <p>Accélérer l'acquisition avec des campagnes ciblées pour générer des demandes plus vite.</p>
                <div class="attirer-modules">
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Google Ads</h3>
                            <span class="attirer-state attirer-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
                        </div>
                        <p>Lancez des annonces locales orientées estimation et prise de rendez-vous vendeur.</p>
                        <a href="#" class="attirer-module-action" aria-disabled="true"><i class="fas fa-hourglass-half"></i> Commencer</a>
                    </div>
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Facebook Ads</h3>
                            <span class="attirer-state attirer-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
                        </div>
                        <p>Diffuser des campagnes géolocalisées pour augmenter votre notoriété vendeur.</p>
                        <a href="#" class="attirer-module-action" aria-disabled="true"><i class="fas fa-hourglass-half"></i> Commencer</a>
                    </div>
                </div>
            </article>

            <article class="attirer-pillar">
                <h2><i class="fas fa-rocket" style="color:#8b5cf6;"></i> Amplifier</h2>
                <p>Industrialiser votre visibilité pour publier plus régulièrement sans perdre en qualité.</p>
                <div class="attirer-modules">
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Social</h3>
                            <span class="attirer-state attirer-state--available"><i class="fas fa-check-circle"></i> Disponible</span>
                        </div>
                        <p>Créez une présence sociale qui entretient la relation avec votre audience locale.</p>
                        <a href="?module=social" class="attirer-module-action"><i class="fas fa-arrow-right"></i> Commencer</a>
                    </div>
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Publication automatique</h3>
                            <span class="attirer-state attirer-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
                        </div>
                        <p>Planifiez votre cadence pour rester visible chaque semaine avec moins d'effort.</p>
                        <a href="#" class="attirer-module-action" aria-disabled="true"><i class="fas fa-hourglass-half"></i> Commencer</a>
                    </div>
                    <div class="attirer-module">
                        <div class="attirer-module-head">
                            <h3>Vidéo / IA</h3>
                            <span class="attirer-state attirer-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
                        </div>
                        <p>Réutilisez vos idées en formats vidéo courts pour gagner en portée locale.</p>
                        <a href="#" class="attirer-module-action" aria-disabled="true"><i class="fas fa-hourglass-half"></i> Commencer</a>
                    </div>
                </div>
            </article>
        </section>

        <section class="attirer-final-cta">
            <div>
                <h2>Passez à l’action</h2>
                <p>Commencez par un levier simple, puis développez votre système.</p>
            </div>
            <a href="?module=seo"><i class="fas fa-arrow-trend-up"></i> Créer ma première source de leads</a>
        </section>
    </section>
    <?php
}

function renderAttirerContentArticles(): void
{
    $articles = attirerPublishedArticlesWithPerformance();
    $topics = attirerNoahTopicSuggestions();
    $zoneCity = trim((string) setting('zone_city', setting('zone_ville', 'votre secteur')));
    $zoneRegion = trim((string) setting('zone_region', ''));
    ?>
    <style>
        .attirer-content-wrap { display:grid; gap:1.25rem; }
        .attirer-toolbar {
            background:#fff; border:1px solid #eef2f7; border-radius:16px;
            padding:1rem 1.2rem; display:flex; justify-content:space-between; align-items:center; gap:1rem;
        }
        .attirer-toolbar .zone { color:#6b7280; font-size:.95rem; }
        .attirer-grid { display:grid; grid-template-columns:1.35fr 1fr; gap:1.25rem; }
        .attirer-card {
            background:#fff; border:1px solid #eef2f7; border-radius:16px; padding:1rem 1.2rem;
            box-shadow:0 6px 18px rgba(15,23,42,.04);
        }
        .attirer-card h2 { margin:0 0 .7rem; font-size:1.08rem; }
        .attirer-table { width:100%; border-collapse:collapse; }
        .attirer-table th, .attirer-table td { padding:.62rem .4rem; border-bottom:1px solid #f1f5f9; text-align:left; font-size:.92rem; }
        .attirer-table th { color:#6b7280; font-weight:600; }
        .metric-pill {
            display:inline-flex; align-items:center; gap:.35rem; padding:.24rem .52rem;
            border-radius:999px; background:#f8fafc; color:#111827; font-weight:600;
        }
        .topic-list { display:grid; gap:.65rem; margin:0; padding:0; list-style:none; }
        .topic-item {
            border:1px solid #f1f5f9; background:#fcfdff; border-radius:12px;
            padding:.72rem .78rem; display:flex; gap:.55rem;
        }
        .topic-item i { color:#f39c12; margin-top:.14rem; }
        .empty { color:#6b7280; font-style:italic; }
        @media (max-width: 1100px) { .attirer-grid { grid-template-columns:1fr; } }
    </style>

    <div class="attirer-content-wrap">
        <div class="page-header" style="margin-bottom:0;">
            <h1><i class="fas fa-pen-nib page-icon"></i> Contenu & <span class="page-title-accent">Articles</span></h1>
            <p>Suivez les performances de vos contenus publiés et planifiez vos prochains sujets avec Noah IA.</p>
        </div>

        <div class="attirer-toolbar">
            <a href="?module=attirer" class="card-action" style="width:auto;"><i class="fas fa-arrow-left"></i> Retour au hub Attirer</a>
            <div class="zone">
                Zone configurée :
                <strong><?= htmlspecialchars($zoneCity !== '' ? $zoneCity : 'Non définie') ?></strong>
                <?= $zoneRegion !== '' ? ' — ' . htmlspecialchars($zoneRegion) : '' ?>
            </div>
        </div>

        <div class="attirer-grid">
            <section class="attirer-card">
                <h2><i class="fas fa-chart-line"></i> Articles publiés & performances</h2>
                <?php if (empty($articles)): ?>
                    <p class="empty">Aucun article publié trouvé pour le moment.</p>
                <?php else: ?>
                    <table class="attirer-table" aria-label="Articles publiés">
                        <thead>
                        <tr>
                            <th>Article</th>
                            <th>Date</th>
                            <th>Vues</th>
                            <th>Partages</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>
                                    <a href="/blog/<?= urlencode((string) $article['slug']) ?>" target="_blank" rel="noopener">
                                        <?= htmlspecialchars((string) $article['titre']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $dateRef = (string) ($article['date_publication'] ?: $article['updated_at']);
                                    echo $dateRef !== '' ? htmlspecialchars(date('d/m/Y', strtotime($dateRef))) : '—';
                                    ?>
                                </td>
                                <td><span class="metric-pill"><i class="fas fa-eye"></i><?= (int) $article['vues'] ?></span></td>
                                <td><span class="metric-pill"><i class="fas fa-share-nodes"></i><?= (int) $article['partages'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <aside class="attirer-card">
                <h2><i class="fas fa-wand-magic-sparkles"></i> Sujets recommandés par Noah IA</h2>
                <p style="margin-top:0;color:#6b7280;font-size:.92rem;">Suggestions contextualisées selon votre zone géographique configurée.</p>
                <ul class="topic-list">
                    <?php foreach ($topics as $topic): ?>
                        <li class="topic-item">
                            <i class="fas fa-lightbulb"></i>
                            <span><?= htmlspecialchars($topic) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
    </div>
    <?php
}

function renderContent(): void
{
    $action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? ''));

    if ($action === 'contenu-articles') {
        renderAttirerContentArticles();
        return;
    }

    renderAttirerHub();
}
