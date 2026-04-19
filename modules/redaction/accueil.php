<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/ArticleRepository.php';
require_once __DIR__ . '/repositories/CampaignRepository.php';
require_once __DIR__ . '/repositories/PublicationRepository.php';
require_once __DIR__ . '/services/ArticleService.php';
require_once __DIR__ . '/services/CampaignService.php';
require_once __DIR__ . '/services/PublicationService.php';

$pageTitle       = 'Rédaction';
$pageDescription = 'Créez et gérez votre contenu éditorial';

$pdo    = db();
$user   = Auth::user();
$userId = (int)($user['id'] ?? 0);

// Website ID (fallback 1)
$websiteId = (int)($user['website_id'] ?? 1);

// Ensure schema
$schemaSql = file_get_contents(__DIR__ . '/sql/redaction.sql');
if ($schemaSql !== false) {
    foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $stmt) {
        if ($stmt !== '') {
            try { $pdo->exec($stmt); } catch (PDOException) {}
        }
    }
}

// Repos & services
$articleRepo  = new ArticleRepository($pdo);
$campaignRepo = new CampaignRepository($pdo);
$pubRepo      = new PublicationRepository($pdo);
$articleSvc   = new ArticleService($articleRepo);
$campaignSvc  = new CampaignService($campaignRepo, $articleRepo);
$pubSvc       = new PublicationService($pubRepo, $articleSvc);

function rdFetchPersonas(PDO $pdo): array
{
    try {
        $stmt = $pdo->query('SELECT id, name FROM personas ORDER BY name ASC');
        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    } catch (Throwable) {
        return [];
    }
}

function rdEnsureSocialSchema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $schemaPath = __DIR__ . '/../social/sql/social_schema.sql';
    if (is_file($schemaPath)) {
        $sql = file_get_contents($schemaPath);
        if (is_string($sql) && $sql !== '') {
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
                if ($statement !== '') {
                    try {
                        $pdo->exec($statement);
                    } catch (Throwable) {
                    }
                }
            }
        }
    }

    try {
        $pdo->exec("ALTER TABLE social_posts ADD COLUMN IF NOT EXISTS niveau ENUM('n1','n2','n3','n4','n5') DEFAULT NULL AFTER statut");
        $pdo->exec("ALTER TABLE social_posts ADD COLUMN IF NOT EXISTS ordre_sequence SMALLINT UNSIGNED DEFAULT NULL AFTER niveau");
    } catch (Throwable) {
    }
}

function rdArticleLink(array $article): string
{
    $slug = trim((string)($article['slug'] ?? ''));
    if ($slug === '') {
        return '';
    }
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '/blog/' . rawurlencode($slug);
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host . '/blog/' . rawurlencode($slug);
}

function rdBuildStrategicPosts(array $article, string $persona, string $objectif, int $requestedCount): array
{
    $count = max(5, min(10, $requestedCount));
    $title = trim((string)($article['titre'] ?? 'Votre article'));
    $intro = trim((string)strip_tags($article['intro'] ?? ''));
    $body = trim((string)strip_tags($article['contenu'] ?? ''));
    $conclusion = trim((string)strip_tags($article['conclusion'] ?? ''));
    $link = rdArticleLink($article);

    $goalLabel = match ($objectif) {
        'leads' => 'générer des leads qualifiés',
        'autorite' => 'renforcer votre autorité locale',
        default => 'créer du trafic ciblé',
    };

    $levels = [
        ['key' => 'n1', 'label' => 'N1', 'hook' => 'Vous vous posez cette question sans trouver de réponse claire ?'],
        ['key' => 'n2', 'label' => 'N2', 'hook' => 'Le problème est réel : voici pourquoi il coûte cher quand on l’ignore.'],
        ['key' => 'n3', 'label' => 'N3', 'hook' => 'Bonne nouvelle : il existe des solutions concrètes et accessibles.'],
        ['key' => 'n4', 'label' => 'N4', 'hook' => 'Comparons les options pour choisir la meilleure stratégie.'],
        ['key' => 'n5', 'label' => 'N5', 'hook' => 'Prêt à passer à l’action ? Voici le plan immédiat.'],
    ];

    $ctas = [
        'n1' => ['traffic' => 'Découvrez l’article complet pour comprendre les bases.', 'leads' => 'Commentez "GUIDE" pour recevoir la checklist.', 'autorite' => 'Lisez l’analyse complète pour poser les bons repères.'],
        'n2' => ['traffic' => 'Cliquez pour voir les erreurs à éviter.', 'leads' => 'Demandez votre mini-audit gratuit en message privé.', 'autorite' => 'Consultez la méthode détaillée dans l’article.'],
        'n3' => ['traffic' => 'Parcourez les solutions détaillées dans l’article.', 'leads' => 'Répondez "PLAN" pour obtenir un plan adapté à votre cas.', 'autorite' => 'Découvrez notre framework complet pas à pas.'],
        'n4' => ['traffic' => 'Lisez le comparatif complet sur le blog.', 'leads' => 'Réservez un échange de 15 min pour choisir la bonne option.', 'autorite' => 'Voyez nos critères d’arbitrage dans l’article détaillé.'],
        'n5' => ['traffic' => 'Passez à l’étape suivante avec l’article complet.', 'leads' => 'Prenez rendez-vous pour lancer votre stratégie maintenant.', 'autorite' => 'Appliquez notre plan opérationnel présenté dans l’article.'],
    ];

    $goalKey = $objectif === 'leads' ? 'leads' : ($objectif === 'autorite' ? 'autorite' : 'traffic');
    $seeds = array_values(array_filter([$intro, $body, $conclusion]));
    $fallbackSeed = 'Découvrez les leviers concrets pour avancer avec une stratégie claire et locale.';

    $posts = [];
    for ($i = 0; $i < $count; $i++) {
        $level = $levels[$i % 5];
        $seed = $seeds[$i % max(1, count($seeds))] ?? $fallbackSeed;
        $seed = mb_substr(preg_replace('/\s+/', ' ', $seed) ?: $fallbackSeed, 0, 220);
        $cta = $ctas[$level['key']][$goalKey];

        $posts[] = [
            'ordre' => $i + 1,
            'niveau' => $level['key'],
            'titre' => 'Séquence SEO · ' . $level['label'] . ' · ' . mb_substr($title, 0, 90),
            'contenu' => trim(
                "Hook : {$level['hook']}\n" .
                "Contenu : {$seed}\n" .
                "CTA : {$cta}\n" .
                'Lien article : ' . ($link !== '' ? $link : 'à compléter')
            ),
            'objectif' => $goalLabel,
            'persona' => $persona,
        ];
    }

    return $posts;
}

$action = $_GET['action'] ?? 'hub';

// ─── API endpoints (JSON) ────────────────────────────────────────────────────

if ($action === 'api_search_articles') {
    header('Content-Type: application/json');
    $q = trim($_GET['q'] ?? '');
    echo json_encode($articleRepo->searchForLink($userId, $q));
    exit;
}

if ($action === 'api_generate_post') {
    header('Content-Type: application/json');
    $reseau  = $_POST['reseau'] ?? 'gmb';
    $article = [
        'titre'   => $_POST['titre']   ?? '',
        'intro'   => $_POST['intro']   ?? '',
        'contenu' => $_POST['contenu'] ?? '',
    ];
    echo json_encode(['contenu' => $articleSvc->generateSocialPost($article, $reseau)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'api_create_social_sequence') {
    header('Content-Type: application/json');

    $articleId = (int)($_POST['article_id'] ?? 0);
    $persona = trim((string)($_POST['persona'] ?? 'Persona libre'));
    $objectif = trim((string)($_POST['objectif'] ?? 'trafic'));
    $objectif = in_array($objectif, ['trafic', 'leads', 'autorite'], true) ? $objectif : 'trafic';
    $requestedCount = (int)($_POST['nombre_posts'] ?? 5);
    $requestedCount = in_array($requestedCount, [3, 5, 7, 10], true) ? $requestedCount : 5;

    $networks = array_values(array_filter((array)($_POST['reseaux'] ?? []), static function ($network): bool {
        return in_array((string)$network, ['facebook', 'instagram', 'gmb'], true);
    }));
    if ($networks === []) {
        $networks = ['facebook'];
    }

    $article = $articleRepo->findById($articleId);
    if (!$article || (int)($article['user_id'] ?? 0) !== $userId) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Article introuvable.']);
        exit;
    }

    rdEnsureSocialSchema($pdo);
    $posts = rdBuildStrategicPosts($article, $persona, $objectif, $requestedCount);
    $socialNetworks = array_map(static fn(string $n): string => $n === 'gmb' ? 'google_my_business' : $n, $networks);

    try {
        $pdo->beginTransaction();

        $seqStmt = $pdo->prepare(
            'INSERT INTO social_sequences (user_id, nom, persona, zone, statut, objectif, created_at, updated_at)
             VALUES (:user_id, :nom, :persona, :zone, :statut, :objectif, NOW(), NOW())'
        );
        $seqStmt->execute([
            ':user_id' => $userId,
            ':nom' => 'SEO → Social · ' . mb_substr((string)($article['titre'] ?? 'Article'), 0, 120),
            ':persona' => $persona !== '' ? $persona : 'Persona libre',
            ':zone' => (string)setting('zone_city', 'Bordeaux'),
            ':statut' => 'active',
            ':objectif' => $objectif,
        ]);
        $sequenceId = (int)$pdo->lastInsertId();

        $postStmt = $pdo->prepare(
            'INSERT INTO social_posts
             (user_id, sequence_id, titre, contenu, reseaux, statut, niveau, ordre_sequence, planifie_at, created_at, updated_at)
             VALUES
             (:user_id, :sequence_id, :titre, :contenu, :reseaux, :statut, :niveau, :ordre_sequence, NULL, NOW(), NOW())'
        );

        foreach ($posts as $post) {
            $postStmt->execute([
                ':user_id' => $userId,
                ':sequence_id' => $sequenceId,
                ':titre' => $post['titre'],
                ':contenu' => $post['contenu'],
                ':reseaux' => json_encode($socialNetworks, JSON_UNESCAPED_UNICODE),
                ':statut' => 'brouillon',
                ':niveau' => $post['niveau'],
                ':ordre_sequence' => $post['ordre'],
            ]);
        }

        $pdo->commit();

        echo json_encode([
            'ok' => true,
            'sequence_id' => $sequenceId,
            'created_posts' => count($posts),
            'effective_posts' => count($posts),
            'message' => 'Séquence sociale créée avec succès.',
        ]);
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Impossible de créer la séquence sociale.']);
        exit;
    }
}

// ─── Mutations (POST) ────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'article_save') {
    $data = $_POST;

    // Handle maillage interne (JSON-encoded items)
    $mi = [];
    foreach ($data['maillage_interne'] ?? [] as $raw) {
        $decoded = json_decode($raw, true);
        if ($decoded) {
            $mi[] = $decoded;
        }
    }
    $data['maillage_interne'] = json_encode($mi, JSON_UNESCAPED_UNICODE);

    // Handle maillage externe
    $me = [];
    $anchors = $data['maillage_externe_anchor'] ?? [];
    $urls    = $data['maillage_externe_url']    ?? [];
    foreach ($anchors as $i => $anchor) {
        $url = $urls[$i] ?? '';
        if ($anchor && $url) {
            $me[] = ['anchor' => $anchor, 'url' => $url];
        }
    }
    $data['maillage_externe'] = json_encode($me, JSON_UNESCAPED_UNICODE);
    unset($data['maillage_externe_anchor'], $data['maillage_externe_url']);

    // Strip non-article fields
    $allowed = ['id','website_id','user_id','silo_id','type','titre','slug','seo_title','meta_desc','h1',
                'contenu','intro','conclusion','statut','index_status','persona_id','niveau_conscience',
                'date_publication','mot_cle_principal','mots_cles_lsi','maillage_interne','maillage_externe',
                'keywords_raw'];
    $clean = array_intersect_key($data, array_flip($allowed));
    $clean['user_id']    = $userId;
    $clean['website_id'] = $websiteId;

    // date_publication: empty → null
    if (empty($clean['date_publication'])) {
        unset($clean['date_publication']);
    }
    if ($clean['statut'] === 'publié' && empty($clean['date_publication'])) {
        $clean['date_publication'] = date('Y-m-d H:i:s');
    }

    $id = $articleSvc->save($clean, $userId, $websiteId);

    // Auto-generate social posts
    $reseaux = $data['auto_reseaux'] ?? [];
    if (!empty($reseaux)) {
        $article = $articleRepo->findById($id);
        if ($article) {
            foreach ($reseaux as $reseau) {
                $postContent = $data['post_' . $reseau] ?? $articleSvc->generateSocialPost($article, $reseau);
                $planifieAt  = !empty($data['post_' . $reseau . '_date'])
                    ? date('Y-m-d H:i:s', strtotime($data['post_' . $reseau . '_date']))
                    : null;
                $pubRepo->save([
                    'article_id'  => $id,
                    'user_id'     => $userId,
                    'reseau'      => $reseau,
                    'titre'       => mb_substr($article['titre'] ?? '', 0, 255),
                    'contenu'     => $postContent,
                    'statut'      => $planifieAt ? 'planifié' : 'draft',
                    'planifie_at' => $planifieAt,
                ]);
            }
        }
    }

    $_SESSION['rd_flash'] = ['type' => 'success', 'msg' => 'Article enregistré avec succès.'];
    header('Location: /admin?module=redaction&action=article_edit&id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'campaign_save') {
    $data = $_POST;
    $slots = [];
    foreach ($data['slots'] ?? [] as $s) {
        $slots[] = [
            'role'             => $s['role'] ?? 'conscience',
            'article_id'       => !empty($s['article_id']) ? (int)$s['article_id'] : null,
            'niveau_conscience' => !empty($s['niveau_conscience']) ? (int)$s['niveau_conscience'] : null,
        ];
    }
    $id = $campaignSvc->save([
        'id'          => !empty($data['id']) ? (int)$data['id'] : null,
        'nom'         => $data['nom'] ?? '',
        'description' => $data['description'] ?? null,
        'mot_cle'     => $data['mot_cle'] ?? null,
        'statut'      => $data['statut'] ?? 'draft',
    ], $slots, $userId, $websiteId);

    $_SESSION['rd_flash'] = ['type' => 'success', 'msg' => 'Campagne enregistrée.'];
    header('Location: /admin?module=redaction&action=campaign_edit&id=' . $id);
    exit;
}

// ─── Deletions ───────────────────────────────────────────────────────────────

if ($action === 'article_delete' && !empty($_GET['id'])) {
    $articleRepo->delete((int)$_GET['id']);
    header('Location: /admin?module=redaction&action=pool_articles');
    exit;
}

if ($action === 'campaign_delete' && !empty($_GET['id'])) {
    $campaignRepo->delete((int)$_GET['id']);
    header('Location: /admin?module=redaction&action=campaigns');
    exit;
}

if ($action === 'pub_delete' && !empty($_GET['id'])) {
    $pubRepo->delete((int)$_GET['id']);
    header('Location: /admin?module=redaction&action=pool_gmb');
    exit;
}

// ─── Views data ──────────────────────────────────────────────────────────────

function renderContent(): void
{
    global $action, $articleRepo, $campaignRepo, $pubRepo, $pdo,
           $articleSvc, $campaignSvc, $pubSvc,
           $userId, $websiteId;

    // Hub
    if ($action === 'hub') {
        $counts        = $articleRepo->countByStatut($userId);
        $pubCounts     = $pubRepo->countByReseau($userId);
        $campaigns     = $campaignRepo->findAll($userId);
        $campaignsCount = count($campaigns);
        $recentArticles = $articleRepo->findAll($userId, []);
        $recentArticles = array_slice($recentArticles, 0, 10);
        require __DIR__ . '/views/hub.php';
        return;
    }

    // Article form
    if ($action === 'article_new' || $action === 'article_edit') {
        $article  = null;
        $keywords = [];
        if ($action === 'article_edit' && !empty($_GET['id'])) {
            $article  = $articleRepo->findById((int)$_GET['id']);
            $keywords = $articleRepo->getKeywords((int)$_GET['id']);
        }
        // Pre-fill type/niveau from query string for campaign linking
        if (!$article) {
            $article = [
                'type'             => $_GET['type'] ?? 'satellite',
                'niveau_conscience' => !empty($_GET['niveau_conscience']) ? (int)$_GET['niveau_conscience'] : null,
            ];
        }
        $personas = rdFetchPersonas($pdo);
        $allArticles = $articleRepo->findAll($userId, []);
        require __DIR__ . '/views/article_form.php';
        return;
    }

    // Pool articles
    if ($action === 'pool_articles') {
        $filters  = [
            'statut' => $_GET['statut'] ?? '',
            'type'   => $_GET['type']   ?? '',
            'q'      => $_GET['q']      ?? '',
        ];
        $articles = $articleRepo->findAll($userId, $filters);
        $counts   = $articleRepo->countByStatut($userId);
        require __DIR__ . '/views/pool_articles.php';
        return;
    }

    // Pool GMB / publications
    if ($action === 'pool_gmb') {
        $filters      = [
            'reseau' => $_GET['reseau'] ?? '',
            'statut' => $_GET['statut'] ?? '',
        ];
        $publications = $pubSvc->findAll($userId, $filters);
        $pubCounts    = $pubRepo->countByReseau($userId);
        require __DIR__ . '/views/pool_gmb.php';
        return;
    }

    // Journal
    if ($action === 'journal') {
        $journal = $pubSvc->getJournal($userId);
        require __DIR__ . '/views/journal.php';
        return;
    }

    // Campaign list
    if ($action === 'campaigns') {
        $campaigns = $campaignRepo->findAll($userId);
        require __DIR__ . '/views/campaigns_list.php';
        return;
    }

    // Campaign form
    if ($action === 'campaign_new' || $action === 'campaign_edit') {
        $campaign    = null;
        $slots       = [];
        if ($action === 'campaign_edit' && !empty($_GET['id'])) {
            $full     = $campaignSvc->getWithArticles((int)$_GET['id']);
            $campaign = $full;
            $slots    = $full ? $campaignSvc->buildSlots($full) : [];
        }
        if (!$campaign) {
            $campaign = [];
            $mockFull = ['articles' => []];
            $slots    = $campaignSvc->buildSlots($mockFull);
        }
        $allArticles = $articleRepo->findAll($userId, []);
        require __DIR__ . '/views/campaign_form.php';
        return;
    }

    // Default → hub
    $counts        = $articleRepo->countByStatut($userId);
    $pubCounts     = $pubRepo->countByReseau($userId);
    $campaigns     = $campaignRepo->findAll($userId);
    $campaignsCount = count($campaigns);
    $recentArticles = array_slice($articleRepo->findAll($userId, []), 0, 10);
    require __DIR__ . '/views/hub.php';
}
