<?php
declare(strict_types=1);

$pageTitle = 'Annuaire local';
$pageDescription = 'Fiches commerces, guide par ville (liens Maps), API et carte — distinct de la présentation Secteurs';

function annuaire_local_table_exists(PDO $pdo, string $table): bool
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        return false;
    }
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = ?'
    );
    $st->execute([$table]);

    return (int) $st->fetchColumn() > 0;
}

function annuaire_local_slugify(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower((string) $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $poiAction = (string) ($_POST['poi_action'] ?? '');
    if ($poiAction === 'save') {
        require __DIR__ . '/poi-save.php';
        exit;
    }
    if ($poiAction === 'delete') {
        require __DIR__ . '/poi-delete.php';
        exit;
    }
    if ($poiAction === 'llm_suggest') {
        require __DIR__ . '/poi-llm-suggest.php';
        exit;
    }
    if ((string) ($_POST['annuaire_ville_action'] ?? '') === 'save_presentation') {
        require __DIR__ . '/ville-presentation-save.php';
        exit;
    }
}

$glExportAction = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['action'] ?? ''));
$glExportId = (int) ($_GET['id'] ?? 0);
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && $glExportAction === 'poi-export' && $glExportId > 0) {
    require __DIR__ . '/poi-export.php';
    exit;
}

$glAction = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['action'] ?? ''));
$glPoiId = (int) ($_GET['id'] ?? 0);
if ($glAction === 'poi-new') {
    $pageTitle = 'Nouveau commerce (annuaire)';
} elseif ($glAction === 'poi-edit' && $glPoiId > 0) {
    $pageTitle = 'Modifier fiche annuaire';
} elseif ($glAction === 'edit-ville' && (string) ($_GET['slug'] ?? '') !== '') {
    $pageTitle = 'Présentation guide — ' . (string) $_GET['slug'];
}

function renderContent(): void
{
    $glAction = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['action'] ?? ''));
    $glPoiId = (int) ($_GET['id'] ?? 0);
    $flashSaved = isset($_GET['saved']);
    $flashDeleted = isset($_GET['deleted']);
    $flashErr = trim((string) ($_GET['error'] ?? ''));
    $highlightId = (int) ($_GET['highlight'] ?? 0);

    $villes = 0;
    $quartiers = 0;
    $poiCats = 0;
    $poisCount = 0;
    $poiActive = 0;
    $poiVerified = 0;
    $poiTables = false;
    $err = '';

    try {
        $pdo = db();
        $villes = (int) $pdo->query('SELECT COUNT(*) FROM villes WHERE actif = 1')->fetchColumn();
        $quartiers = (int) $pdo->query('SELECT COUNT(*) FROM quartiers WHERE actif = 1')->fetchColumn();
        $poiTables = annuaire_local_table_exists($pdo, 'guide_pois');
        if ($poiTables) {
            $poiCats = (int) $pdo->query('SELECT COUNT(*) FROM guide_poi_categories WHERE is_active = 1')->fetchColumn();
            $poisCount = (int) $pdo->query('SELECT COUNT(*) FROM guide_pois')->fetchColumn();
            $poiActive = (int) $pdo->query('SELECT COUNT(*) FROM guide_pois WHERE is_active = 1')->fetchColumn();
            try {
                $poiVerified = (int) $pdo->query('SELECT COUNT(*) FROM guide_pois WHERE is_verified = 1')->fetchColumn();
            } catch (Throwable $e) {
                $poiVerified = 0;
            }
        }
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }

    $base = htmlspecialchars(rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/'), ENT_QUOTES, 'UTF-8');
    $guideMerignac = is_file(__DIR__ . '/data/guide_merignac.php') ? require __DIR__ . '/data/guide_merignac.php' : null;
    $gmbModule     = is_file(ROOT_PATH . '/modules/gmb/accueil.php');
    $filterVille   = trim((string) ($_GET['ville'] ?? ''));
    $filterVille   = preg_replace('/[^a-z0-9-]/', '', $filterVille) ?? '';
    $editVilleSlug = trim((string) ($_GET['slug'] ?? ''));
    $editVilleSlug = preg_replace('/[^a-z0-9-]/', '', $editVilleSlug) ?? '';

    if ($glAction === 'edit-ville' && $editVilleSlug !== '') {
        try {
            $pdo  = db();
            $st   = $pdo->prepare('SELECT id, nom, slug, code_postal, description, image_url FROM villes WHERE slug = ? AND actif = 1 LIMIT 1');
            $st->execute([$editVilleSlug]);
            $villeRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            $villeRow = null;
        }
        if (!$villeRow) {
            echo '<div class="gl-warn">Ville introuvable.</div><p><a href="/admin?module=annuaire-local">Retour</a></p>';
            return;
        }
        require __DIR__ . '/ville-presentation-form.php';
        return;
    }

    if ($poiTables && ($glAction === 'poi-new' || ($glAction === 'poi-edit' && $glPoiId > 0))) {
        try {
            $pdo = db();
            $villesList = $pdo->query('SELECT id, nom FROM villes WHERE actif = 1 ORDER BY ordre ASC, nom ASC')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $quartiersList = $pdo->query(
                'SELECT q.id, q.nom, q.ville_id FROM quartiers q WHERE q.actif = 1 ORDER BY q.ville_id, q.ordre ASC, q.nom ASC'
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $categoriesList = $pdo->query('SELECT id, name FROM guide_poi_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $poiRow = null;
            if ($glAction === 'poi-edit' && $glPoiId > 0) {
                $st = $pdo->prepare('SELECT * FROM guide_pois WHERE id = ? LIMIT 1');
                $st->execute([$glPoiId]);
                $poiRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
                if (!$poiRow) {
                    echo '<div class="gl-warn">POI introuvable.</div>';
                    echo '<p><a href="/admin?module=annuaire-local">Retour liste</a></p>';
                    return;
                }
            }
        } catch (Throwable $e) {
            echo '<div class="gl-warn">Erreur : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
            return;
        }
        ?>
        <style>
            .gl-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
            .gl-hero h1 { margin: 0 0 8px; font-size: 26px; font-weight: 700; }
            .gl-hero p { margin: 0; color: rgba(255,255,255,.8); font-size: 14px; max-width: 720px; line-height: 1.5; }
            .gl-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px 20px; margin-bottom: 16px; }
            .gl-warn { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; }
            .gl-ok { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; }
        </style>
        <div class="gl-hero">
            <h1><?= $glAction === 'poi-new' ? 'Nouveau commerce' : 'Modifier fiche' ?></h1>
            <p>Image principale : <code>/storage/uploads/guide-poi/</code>. Fiche publique (après 034) : <code>/commerces/[ville]/[slug]</code>.</p>
        </div>
        <?php if ($flashErr !== ''): ?>
            <div class="gl-warn"><?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php
        require __DIR__ . '/poi-form.php';
        return;
    }

    ?>
    <style>
        .gl-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .gl-hero h1 { margin: 0 0 8px; font-size: 26px; font-weight: 700; }
        .gl-hero p { margin: 0; color: rgba(255,255,255,.8); font-size: 14px; max-width: 720px; line-height: 1.5; }
        .gl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; margin-bottom: 24px; }
        .gl-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; text-align: center; }
        .gl-card strong { display: block; font-size: 28px; color: #0f172a; }
        .gl-card span { font-size: 12px; color: #64748b; }
        .gl-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px 20px; margin-bottom: 16px; }
        .gl-panel h2 { margin: 0 0 12px; font-size: 16px; color: #0f172a; }
        .gl-panel code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
        .gl-panel ul { margin: 8px 0 0; padding-left: 1.2rem; color: #334155; font-size: 14px; line-height: 1.6; }
        .gl-warn { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; }
        .gl-ok { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 16px; }
        .gl-table-wrap { overflow-x: auto; }
        .gl-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .gl-table th, .gl-table td { border-bottom: 1px solid #e5e7eb; padding: 10px 8px; text-align: left; vertical-align: middle; }
        .gl-table th { color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: .02em; }
        .gl-table tr:hover td { background: #f8fafc; }
        .gl-table tr.is-highlight td { background: #eff6ff; }
        .gl-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; border: none; cursor: pointer; }
        .gl-btn-primary { background: #0f2237; color: #fff; }
        .gl-btn-ghost { background: #f1f5f9; color: #0f172a; }
        .gl-btn-danger { background: #fef2f2; color: #b91c1c; }
        .gl-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .al-start-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 32px 36px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }
        .al-start-hero-badge {
            display: inline-block; background: rgba(201,168,76,.2); color: #c9a84c; font-size: 11px; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; margin-bottom: 12px; border: 1px solid rgba(201,168,76,.35);
        }
        .al-start-hero h1 { font-size: 28px; font-weight: 700; margin: 0 0 10px; line-height: 1.25; color: #fff; }
        .al-start-hero p { margin: 0; font-size: 15px; color: rgba(255,255,255,.75); line-height: 1.6; max-width: 700px; }
        .al-steps-title { font-size: 12px; font-weight: 700; color: #8a95a3; text-transform: uppercase; letter-spacing: .07em; margin-bottom: 12px; }
        .al-steps { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
        .al-step {
            display: flex; align-items: flex-start; gap: 16px; background: #fff; border-radius: 12px; padding: 18px 20px;
            text-decoration: none; color: inherit; border: 1px solid #e8ecf0; border-left: 4px solid #e8ecf0;
            transition: transform .15s, box-shadow .15s, border-color .15s;
        }
        .al-step:hover { transform: translateX(3px); box-shadow: 0 4px 14px rgba(0,0,0,.08); border-color: #c9a84c; }
        .al-step-num { flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #64748b; }
        .al-step-body { flex: 1; }
        .al-step-label { font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .al-step-desc { font-size: 13px; color: #64748b; line-height: 1.5; }
        .al-step-arrow { flex-shrink: 0; color: #c9a84c; font-size: 16px; margin-top: 8px; }
        .al-guide { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 22px 24px; margin-bottom: 24px; }
        .al-guide h2 { margin: 0 0 8px; font-size: 18px; color: #0f172a; }
        .al-guide-lead { margin: 0 0 20px; font-size: 14px; color: #64748b; line-height: 1.55; }
        .al-guide-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .al-guide-cat { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; background: #f8fafc; }
        .al-guide-cat h3 { margin: 0 0 10px; font-size: 14px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .al-guide-cat ul { margin: 0; padding: 0; list-style: none; }
        .al-guide-cat li { font-size: 13px; color: #334155; padding: 6px 0; border-top: 1px solid #e2e8f0; }
        .al-guide-cat li:first-child { border-top: 0; padding-top: 0; }
        .al-guide-cat a { color: #1d4ed8; font-weight: 600; }
    </style>

    <div class="al-start-hero">
        <div class="al-start-hero-badge">Annuaire local</div>
        <h1>Commerçants, artisans &amp; guide par ville</h1>
        <p>
            Gérez les fiches en base (<strong><code>guide_pois</code></strong>), la carte publique
            <a href="<?= $base ?>/guide-local" target="_blank" rel="noopener" style="color:#c9a84c;font-weight:600">/guide-local</a>
            et les URL <code>/commerces/[ville]/[slug]</code>. Le bloc ci-dessous propose des <strong>recherches Google Maps</strong> pour
            <strong><?= is_array($guideMerignac) ? e((string) ($guideMerignac['ville'] ?? 'Mérignac')) : 'Mérignac' ?></strong> : ouvrez Maps, repérez les fiches, puis saisissez-les ici.
            La présentation éditoriale « Secteur » (marketing) reste dans
            <a href="/admin?module=secteurs" style="color:#c9a84c;font-weight:600">Secteurs &amp; Zones</a>.
        </p>
    </div>

    <div class="al-steps-title">Commencer</div>
    <div class="al-steps">
        <a href="/admin?module=annuaire-local&amp;action=poi-new" class="al-step">
            <div class="al-step-num">1</div>
            <div class="al-step-body">
                <div class="al-step-label"><i class="fas fa-plus-circle" style="color:#10b981;margin-right:6px"></i>Créer une fiche commerce</div>
                <div class="al-step-desc">Nom, catégorie, ville / quartier Mérignac, description, liens, géoloc.</div>
            </div>
            <div class="al-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <a href="#liste-fiches" class="al-step">
            <div class="al-step-num">2</div>
            <div class="al-step-body">
                <div class="al-step-label"><i class="fas fa-list" style="color:#3b82f6;margin-right:6px"></i>Liste des commerçants en base</div>
                <div class="al-step-desc">Tableau de toutes les fiches, lien vers la page publique, modification ou suppression.</div>
            </div>
            <div class="al-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <a href="#guides-par-ville" class="al-step">
            <div class="al-step-num">3</div>
            <div class="al-step-body">
                <div class="al-step-label"><i class="fas fa-location-dot" style="color:#c9a84c;margin-right:6px"></i>Guides par ville (page publique)</div>
                <div class="al-step-desc">Nombre de fiches par ville, lien vers l’annuaire /guide-local/annuaire/… et édition de la couverture.</div>
            </div>
            <div class="al-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <a href="#guide-merignac" class="al-step">
            <div class="al-step-num">4</div>
            <div class="al-step-body">
                <div class="al-step-label"><i class="fas fa-map-location-dot" style="color:#7c3aed;margin-right:6px"></i>Guide Mérignac (recherche Maps)</div>
                <div class="al-step-desc">Liens de recherche par catégorie — à affiner en copiant le lien fiche (Partager) depuis Google Maps.</div>
            </div>
            <div class="al-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <a href="<?= $base ?>/guide-local" class="al-step" target="_blank" rel="noopener">
            <div class="al-step-num"><i class="fas fa-map"></i></div>
            <div class="al-step-body">
                <div class="al-step-label">Carte annuaire (site public)</div>
                <div class="al-step-desc">Prévisualiser les points publiés et les filtres (API <code>guide-local</code>).</div>
            </div>
            <div class="al-step-arrow"><i class="fas fa-external-link-alt"></i></div>
        </a>
        <?php if ($gmbModule): ?>
        <a href="/admin?module=gmb" class="al-step">
            <div class="al-step-num"><i class="fas fa-store" style="color:#fbbc04"></i></div>
            <div class="al-step-body">
                <div class="al-step-label">Fiche Google My Business (API)</div>
                <div class="al-step-desc">Votre fiche agence via l’API (pas de scraping) — compte, avis, synchro.</div>
            </div>
            <div class="al-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <?php endif; ?>
    </div>

    <?php if ($flashSaved): ?>
        <div class="gl-ok">Enregistrement effectué.</div>
    <?php endif; ?>
    <?php if ($flashDeleted): ?>
        <div class="gl-ok">Fiche supprimée.</div>
    <?php endif; ?>
    <?php if ($flashErr !== ''): ?>
        <div class="gl-warn"><?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($err !== ''): ?>
        <div class="gl-warn">Erreur base : <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="gl-grid">
        <div class="gl-card"><strong><?= (int) $villes ?></strong><span>Villes actives</span></div>
        <div class="gl-card"><strong><?= (int) $quartiers ?></strong><span>Quartiers actifs</span></div>
        <div class="gl-card"><strong><?= (int) $poiCats ?></strong><span>Catégories</span></div>
        <div class="gl-card"><strong><?= (int) $poisCount ?></strong><span>Fiches (total)</span></div>
        <div class="gl-card"><strong><?= (int) $poiActive ?></strong><span>Publiées</span></div>
        <div class="gl-card"><strong><?= (int) $poiVerified ?></strong><span>Vérifiées</span></div>
    </div>

    <?php
    $guidesParVille = [];
    try {
        $pdoGv = db();
        if (annuaire_local_table_exists($pdoGv, 'guide_pois')) {
            $guidesParVille = $pdoGv->query(
                'SELECT v.id, v.nom, v.slug, v.code_postal,
                 (SELECT COUNT(*)
                    FROM guide_pois p
                    LEFT JOIN villes v1 ON v1.id = p.ville_id
                    LEFT JOIN quartiers q ON q.id = p.quartier_id
                    LEFT JOIN villes v2 ON v2.id = q.ville_id
                    WHERE p.is_active = 1 AND COALESCE(v1.slug, v2.slug) = v.slug) AS n_poi
                 FROM villes v
                 WHERE v.actif = 1
                 ORDER BY v.ordre ASC, v.nom ASC'
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            $guidesParVille = $pdoGv->query(
                'SELECT id, nom, slug, code_postal, 0 AS n_poi FROM villes WHERE actif = 1 ORDER BY ordre ASC, nom ASC'
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Throwable $e) {
        $guidesParVille = [];
    }
    ?>
    <div id="guides-par-ville" class="gl-panel">
        <h2>Guides par ville (site public)</h2>
        <p style="margin:0 0 14px;font-size:14px;color:#64748b;line-height:1.5">
            Chaque ligne mène vers la <strong>page annuaire</strong> (liste des fiches, photo d’en-tête) et l’<strong>édition de la présentation</strong> (texte, image).
            Même principe que <a href="<?= $base ?>/guide-local" target="_blank" rel="noopener">/guide-local</a> (hub) : <code>/guide-local/annuaire/{slug-ville}</code>.
        </p>
        <div class="gl-table-wrap">
            <table class="gl-table">
                <thead>
                <tr>
                    <th>Ville</th>
                    <th>Fiches publiées</th>
                    <th>Page guide</th>
                    <th>Présentation (admin)</th>
                    <th>Filtrer la liste</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($guidesParVille === []): ?>
                    <tr><td colspan="5" style="color:#64748b">Aucune ville active.</td></tr>
                <?php endif; ?>
                <?php foreach ($guidesParVille as $gv):
                    $gslug = (string) ($gv['slug'] ?? '');
                    $gpub  = $base . '/guide-local/annuaire/' . rawurlencode($gslug);
                    $nPoi  = (int) ($gv['n_poi'] ?? 0);
                    ?>
                    <tr>
                        <td><strong><?= e((string) ($gv['nom'] ?? '')) ?></strong><?= ($gv['code_postal'] ?? '') !== '' ? ' <span style="color:#94a3b8">(' . e((string) $gv['code_postal']) . ')</span>' : '' ?></td>
                        <td><?= $nPoi ?></td>
                        <td><a href="<?= e($gpub) ?>" target="_blank" rel="noopener" style="font-size:12px;color:#2563eb">/guide-local/annuaire/…</a></td>
                        <td><a class="gl-btn gl-btn-ghost" style="font-size:12px;padding:6px 10px" href="/admin?module=annuaire-local&amp;action=edit-ville&amp;slug=<?= e(rawurlencode($gslug)) ?>">Éditer</a></td>
                        <td><a class="gl-btn gl-btn-ghost" style="font-size:12px;padding:6px 10px" href="/admin?module=annuaire-local&amp;ville=<?= e(rawurlencode($gslug)) ?>#liste-fiches">Fiches (ville)</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (is_array($guideMerignac) && !empty($guideMerignac['categories'])): ?>
    <div id="guide-merignac" class="al-guide">
        <h2>Guide des recherches — <?= e((string) ($guideMerignac['ville'] ?? 'Mérignac')) ?> (<?= e((string) ($guideMerignac['postal'] ?? '33700')) ?>)</h2>
        <p class="al-guide-lead">
            Ouvrez le lien <strong>Maps</strong> pour chaque thématique, repérez les vrais commerces, puis créez les fiches avec l’étape 1
            (ou remplacez les URL de <em>recherche</em> par un lien fiche <code>maps.app.goo.gl</code> / <code>g.page/…</code> une fois identifiés).
        </p>
        <div class="al-guide-grid">
            <?php foreach ($guideMerignac['categories'] as $cat): ?>
                <div class="al-guide-cat">
                    <h3>
                        <span class="fas <?= e((string) ($cat['icon'] ?? 'fa-store')) ?>"></span>
                        <?= e((string) ($cat['title'] ?? '')) ?>
                    </h3>
                    <ul>
                        <?php foreach ($cat['items'] ?? [] as $item): ?>
                            <li>
                                <strong><?= e((string) ($item['name'] ?? '')) ?></strong>
                                <span style="display:block;margin-top:4px">
                                    <a href="<?= e((string) ($item['gmb'] ?? '#')) ?>" target="_blank" rel="noopener">Recherche Google Maps</a>
                                    <?php
                                    $web = trim((string) ($item['web'] ?? ''));
                                    if ($web !== ''): ?>
                                        · <a href="<?= e($web) ?>" target="_blank" rel="noopener">Site</a>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin:16px 0 0;font-size:12px;color:#94a3b8">Données éditables : <code>modules/annuaire-local/data/guide_merignac.php</code> (autres villes : dupliquer le fichier ou factoriser plus tard).</p>
    </div>
    <?php endif; ?>

    <?php if (!$poiTables): ?>
        <div class="gl-warn">
            Tables <code>guide_pois</code> absentes — exécutez
            <code>php scripts/run-migration-032.php</code> puis
            <code>php scripts/run-migration-034.php</code> (extension annuaire / avis / catégories commerçants).
        </div>
    <?php else: ?>
        <div id="liste-fiches" class="gl-panel">
            <h2>Liste des commerçants (fiches en base)</h2>
            <p style="margin:0 0 14px;font-size:14px;color:#64748b;display:flex;flex-wrap:wrap;align-items:center;gap:10px">
                <a href="/admin?module=annuaire-local&amp;action=poi-new" class="gl-btn gl-btn-primary">+ Nouveau commerce</a>
                <form method="get" action="/admin" class="gl-actions" style="margin:0;gap:8px">
                    <input type="hidden" name="module" value="annuaire-local">
                    <span style="font-size:13px;color:#64748b;align-self:center">Ville</span>
                    <select name="ville" id="filter-ville" onchange="this.form.submit()" style="padding:8px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;min-width:200px" aria-label="Filtrer la liste par ville (slug)">
                        <option value="">Toutes les villes</option>
                        <?php foreach ($guidesParVille as $fv):
                            $fs = (string) ($fv['slug'] ?? '');
                            if ($fs === '') {
                                continue;
                            } ?>
                        <option value="<?= e($fs) ?>"<?= $filterVille === $fs ? ' selected' : '' ?>><?= e((string) ($fv['nom'] ?? $fs)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($filterVille !== ''): ?>
                        <a class="gl-btn gl-btn-ghost" href="/admin?module=annuaire-local#liste-fiches">Tout afficher</a>
                    <?php endif; ?>
                </form>
            </p>
            <?php if ($filterVille !== ''): ?>
                <p style="margin:0 0 14px;font-size:13px;color:#64748b">Filtre actif : <code><?= e($filterVille) ?></code> (slug ville effective : <code>COALESCE(fiche.ville, quartier→ville)</code>).</p>
            <?php endif; ?>
            <?php
            try {
                $pdo = db();
                $hasVerif = false;
                try {
                    $pdo->query('SELECT is_verified FROM guide_pois LIMIT 1');
                    $hasVerif = true;
                } catch (Throwable $e) {
                    $hasVerif = false;
                }
                $whereVille = $filterVille !== '' ? ' WHERE COALESCE(v.slug, vq.slug) = :ville_slug' : '';
                $sql = $hasVerif
                    ? 'SELECT p.id, p.name, p.slug, p.is_active, p.is_verified, p.featured_image,
                            COALESCE(v.nom, vq.nom) AS ville_nom, COALESCE(v.slug, vq.slug) AS ville_slug, q.nom AS quartier_nom, c.name AS cat_name
                     FROM guide_pois p
                     LEFT JOIN villes v ON v.id = p.ville_id
                     LEFT JOIN quartiers q ON q.id = p.quartier_id
                     LEFT JOIN villes vq ON vq.id = q.ville_id
                     LEFT JOIN guide_poi_categories c ON c.id = p.category_id' . $whereVille
                    . ' ORDER BY p.updated_at DESC'
                    : 'SELECT p.id, p.name, p.slug, p.is_active, p.featured_image,
                            COALESCE(v.nom, vq.nom) AS ville_nom, COALESCE(v.slug, vq.slug) AS ville_slug, q.nom AS quartier_nom, c.name AS cat_name
                     FROM guide_pois p
                     LEFT JOIN villes v ON v.id = p.ville_id
                     LEFT JOIN quartiers q ON q.id = p.quartier_id
                     LEFT JOIN villes vq ON vq.id = q.ville_id
                     LEFT JOIN guide_poi_categories c ON c.id = p.category_id' . $whereVille
                    . ' ORDER BY p.updated_at DESC';
                $stList = $pdo->prepare($sql);
                if ($filterVille !== '') {
                    $stList->bindValue(':ville_slug', $filterVille, PDO::PARAM_STR);
                }
                $stList->execute();
                $rows = $stList->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable $e) {
                $rows = [];
                $hasVerif = false;
                echo '<div class="gl-warn">Impossible de charger les fiches.</div>';
            }
            ?>
            <div class="gl-table-wrap">
                <table class="gl-table">
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>URL publique</th>
                        <th>Catégorie</th>
                        <th>Lieu</th>
                        <th>État</th>
                        <th style="min-width:200px">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($rows === []): ?>
                        <tr><td colspan="6" style="color:#64748b">Aucune fiche — ajoutez un commerce.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r):
                        $rid = (int) $r['id'];
                        $vslug = rawurlencode((string) ($r['ville_slug'] ?? 'ville'));
                        $pslug = rawurlencode((string) $r['slug']);
                        $publicUrl = '/commerces/' . $vslug . '/' . $pslug;
                        $place = [];
                        if (!empty($r['quartier_nom'])) {
                            $place[] = (string) $r['quartier_nom'];
                        }
                        if (!empty($r['ville_nom'])) {
                            $place[] = (string) $r['ville_nom'];
                        }
                        $placeStr = $place !== [] ? implode(' · ', $place) : '—';
                        $verifHtml = '';
                        if ($hasVerif && (int) ($r['is_verified'] ?? 0) === 1) {
                            $verifHtml = ' <span style="color:#0ea5e9" title="Vérifié">✓</span>';
                        }
                        ?>
                        <tr class="<?= $highlightId === $rid ? 'is-highlight' : '' ?>">
                            <td><strong><?= e((string) $r['name']) ?><?= $verifHtml ?></strong></td>
                            <td><a href="<?= e($publicUrl) ?>" target="_blank" rel="noopener" style="font-size:12px;color:#2563eb">/commerces/…</a></td>
                            <td><?= e((string) ($r['cat_name'] ?? '—')) ?></td>
                            <td><?= e($placeStr) ?></td>
                            <td><?= (int) ($r['is_active'] ?? 0) === 1 ? '<span style="color:#059669">En ligne</span>' : '<span style="color:#94a3b8">Brouillon</span>' ?></td>
                            <td>
                                <div class="gl-actions">
                                    <a class="gl-btn gl-btn-ghost" href="/admin?module=annuaire-local&amp;action=poi-edit&amp;id=<?= $rid ?>">Modifier</a>
                                    <form method="post" action="/admin?module=annuaire-local" style="display:inline" onsubmit="return confirm('Supprimer cette fiche ?');">
                                        <input type="hidden" name="poi_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $rid ?>">
                                        <?= function_exists('csrfField') ? csrfField() : '' ?>
                                        <button type="submit" class="gl-btn gl-btn-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="gl-panel">
        <h2>API publique (lecture)</h2>
        <ul>
            <li><code>GET <?= $base ?>/api/guide-local/villes.php</code> — liste des villes</li>
            <li><code>GET <?= $base ?>/api/guide-local/villes.php?slug=bordeaux</code> — ville + quartiers</li>
            <li><code>GET <?= $base ?>/api/guide-local/pois.php?district_slug=chartrons</code> — POI filtrés</li>
            <li><code>GET <?= $base ?>/api/guide-local/pois.php?city_slug=bordeaux&amp;category_slug=restaurants-bars</code></li>
            <li>Carte publique (Leaflet) : <a href="<?= $base ?>/guide-local" target="_blank" rel="noopener">/guide-local</a> — consomme ces mêmes API.</li>
        </ul>
    </div>

    <div class="gl-panel">
        <h2>LLM (génération de texte)</h2>
        <p style="margin:0 0 8px;font-size:14px;color:#334155;line-height:1.6">
            <code>ANTHROPIC_API_KEY</code> dans le <code>.env</code>. Cache disque + double rate limit (requêtes API / appels modèle) sous
            <code>storage/cache/guide-local-llm/</code> — voir <code>GuideLocalLlmGuardService</code>.
        </p>
        <ul style="margin:8px 0 0;padding-left:1.2rem;color:#334155;font-size:14px;line-height:1.65">
            <li><code>POST <?= $base ?>/api/guide-local/llm.php</code> — JSON <code>{"action":"describe_district","district_name":"Chartrons","city_name":"Bordeaux"}</code> ou
                <code>{"action":"describe_poi","poi_name":"…","category_name":"…","area_label":"…"}</code></li>
            <li>Auth : <code>GUIDE_LOCAL_LLM_API_KEY</code> (obligatoire hors <code>APP_ENV=development</code>) — en-tête <code>X-Guide-Local-Llm-Key</code>, <code>Authorization: Bearer …</code> ou champ JSON <code>api_key</code>.</li>
            <li>Réponse : <code>{"ok":true,"text":"…","cached":true|false}</code> — <code>cached: true</code> si lecture cache (pas d’appel Anthropic).</li>
        </ul>
    </div>
    <?php
}
