<?php
/**
 * ============================================================
 *  MODULE ARTICLES — Éditeur v5.1
 *  /admin/modules/articles/edit.php
 *
 *  Chargé via require depuis index.php ($pdo hérité)
 *
 *  ✅ Sidebar : Score SEO live + Sémantique + Stats mots
 *  ✅ SERP Preview temps réel (seo_title › meta_title › titre)
 *  ✅ IA : génération complète, métas, FAQ, outline, keywords, rewrite
 *  ✅ Boutons IA inline par champ
 *  ✅ Double écriture FR/EN (statut/status, temps_lecture/reading_time)
 *  ✅ CSRF + Ctrl+S + double confirmation suppression
 * ============================================================
 */

if (!isset($pdo)) {
    $cfgPaths = [
        __DIR__ . '/../../../config/config.php',
        $_SERVER['DOCUMENT_ROOT'] . '/config/config.php',
    ];
    foreach ($cfgPaths as $p) { if (file_exists($p)) { require_once $p; break; } }
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
        );
    } catch (Exception $e) {
        echo '<div style="background:#fee2e2;color:#991b1b;padding:15px;border-radius:8px;margin:20px;">❌ DB: '.htmlspecialchars($e->getMessage()).'</div>';
        return;
    }
}

// ─── CSRF ───────────────────────────────────────────────────────────────────
if (empty($_SESSION['auth_csrf_token'])) $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
$csrfToken = $_SESSION['auth_csrf_token'];

// ─── Paramètres ─────────────────────────────────────────────────────────────
$action  = $_GET['action'] ?? 'edit';
$id      = (int)($_GET['id'] ?? 0);
$error   = '';
$message = '';

if (isset($_GET['msg'])) {
    $msgs = ['saved'=>'✅ Article enregistré.','created'=>'✅ Article créé.','deleted'=>'✅ Supprimé.'];
    $message = $msgs[$_GET['msg']] ?? '';
}

function jsRedirectEdit(string $url): never {
    echo '<script>window.location.href="'.addslashes($url).'";</script>'; exit;
}

// ─── Colonnes réelles ───────────────────────────────────────────────────────
$cols = [];
try { $cols = $pdo->query("SHOW COLUMNS FROM articles")->fetchAll(PDO::FETCH_COLUMN); } catch (Throwable) {}
$has = fn(string $c): bool => in_array($c, $cols);

// ─── Disponibilité IA ───────────────────────────────────────────────────────
$aiAvailable = (defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY))
            || (defined('OPENAI_API_KEY')    && !empty(OPENAI_API_KEY));
$aiProvider  = '';
if (defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY)) $aiProvider = 'Claude';
elseif (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY))   $aiProvider = 'OpenAI';
$AI_ENDPOINT = '/admin/api/ai/generate.php';

// ══════════════════════════════════════════════════════════════
//  SUPPRESSION
// ══════════════════════════════════════════════════════════════
if ($action === 'delete' && $id) {
    $tok = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!hash_equals($csrfToken, $tok)) jsRedirectEdit('?page=articles&msg=csrf_error');
    try {
        $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
        jsRedirectEdit('?page=articles&msg=deleted');
    } catch (Throwable $e) { $error = 'Erreur suppression : ' . $e->getMessage(); }
}

// ══════════════════════════════════════════════════════════════
//  SAUVEGARDE
// ══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['edit','create'])) {
    if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide. Rechargez la page.';
    } else {
        try {
            $titre           = trim($_POST['titre']           ?? '');
            $slug            = trim($_POST['slug']            ?? '');
            $extrait         = trim($_POST['extrait']         ?? '');
            $contenu         = $_POST['contenu']              ?? '';
            $statusEN = in_array($_POST['status'] ?? '', ['published','draft','archived']) ? $_POST['status'] : 'draft';
            $statutFR = $statusEN === 'published' ? 'publie' : 'brouillon';

            $seo_title         = trim($_POST['seo_title']         ?? '');
            $seo_description   = trim($_POST['seo_description']   ?? '');
            $meta_title        = trim($_POST['meta_title']        ?? '');
            $meta_description  = trim($_POST['meta_description']  ?? '');
            $meta_keywords     = trim($_POST['meta_keywords']     ?? '');
            $focus_keyword     = trim($_POST['focus_keyword']     ?? '');
            $main_keyword      = trim($_POST['main_keyword']      ?? '');
            $secondary_keywords= trim($_POST['secondary_keywords']?? '');
            $ville             = trim($_POST['ville']             ?? '');
            $raison_vente      = trim($_POST['raison_vente']      ?? '');
            $persona           = trim($_POST['persona']           ?? '');
            $type_article      = trim($_POST['type_article']      ?? '');
            $category          = trim($_POST['category']          ?? '');
            $featured_image    = trim($_POST['featured_image']    ?? '');
            $featured_image_alt= trim($_POST['featured_image_alt']?? '');
            $h1                = trim($_POST['h1']                ?? '');
            $alt_titre         = trim($_POST['alt_titre']         ?? '');
            $author            = trim($_POST['author']            ?? '');
            $noindex           = isset($_POST['noindex']) ? 1 : 0;
            $niveau_conscience = trim($_POST['niveau_conscience'] ?? '');
            $localite          = trim($_POST['localite']          ?? '');
            $section_motivation  = $_POST['section_motivation']  ?? '';
            $section_explication = $_POST['section_explication'] ?? '';
            $section_recette     = $_POST['section_recette']     ?? '';
            $section_exercice    = $_POST['section_exercice']    ?? '';

            $wordCount   = str_word_count(strip_tags($contenu));
            $readingTime = max(1, (int)ceil($wordCount / 200));

            if (empty($slug) && !empty($titre)) {
                $stops = ['le','la','les','de','du','des','un','une','en','et','ou','a','au','aux',
                          'ce','cette','ces','son','sa','ses','mon','ma','mes','pour','par','sur',
                          'avec','dans','qui','que','dont','est','sont','peut','faire','plus','moins',
                          'tout','tous','ne','pas','se','si','nous','vous','ils','elles','leur'];
                $s = mb_strtolower($titre);
                $s = strtr($s, ['à'=>'a','â'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i',
                                 'ô'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','æ'=>'ae','œ'=>'oe']);
                $s = preg_replace('/[^a-z0-9\s]/u', '', $s);
                $words = array_filter(explode(' ', $s), fn($w) => $w && !in_array($w, $stops));
                $slug  = implode('-', array_slice(array_values($words), 0, 6));
            }
            if (empty($titre)) throw new Exception('Le titre est obligatoire.');

            $colMap = [
                'titre'=>$titre,'alt_titre'=>$alt_titre,'slug'=>$slug,'extrait'=>$extrait,'contenu'=>$contenu,
                'h1'=>$h1,'statut'=>$statutFR,'status'=>$statusEN,
                'seo_title'=>$seo_title,'seo_description'=>$seo_description,
                'meta_title'=>$meta_title,'meta_description'=>$meta_description,'meta_keywords'=>$meta_keywords,
                'focus_keyword'=>$focus_keyword,'main_keyword'=>$main_keyword,'secondary_keywords'=>$secondary_keywords,
                'ville'=>$ville,'raison_vente'=>$raison_vente,'persona'=>$persona,
                'type_article'=>$type_article,'category'=>$category,
                'niveau_conscience'=>$niveau_conscience,'localite'=>$localite,
                'featured_image'=>$featured_image,'featured_image_alt'=>$featured_image_alt,
                'author'=>$author,'noindex'=>$noindex,
                'section_motivation'=>$section_motivation,'section_explication'=>$section_explication,
                'section_recette'=>$section_recette,'section_exercice'=>$section_exercice,
                'word_count'=>$wordCount,'reading_time'=>$readingTime,'temps_lecture'=>$readingTime,
            ];
            $safeMap = array_filter($colMap, fn($col) => $has($col), ARRAY_FILTER_USE_KEY);

            if ($action === 'create') {
                $fields = array_keys($safeMap);
                $sql = 'INSERT INTO articles ('.implode(', ', array_map(fn($c)=>"`{$c}`",$fields)).')
                        VALUES ('.implode(', ', array_fill(0,count($fields),'?')).')';
                $pdo->prepare($sql)->execute(array_values($safeMap));
                $newId = (int)$pdo->lastInsertId();

                // ── Auto-génération post GMB ──
                try {
                    $gmbAutoFile = dirname(__DIR__, 3) . '/includes/classes/GmbArticlePostService.php';
                    if (file_exists($gmbAutoFile) && !empty($titre)) {
                        require_once $gmbAutoFile;
                        $gmbAutoSvc = new GmbArticlePostService($pdo);
                        $gmbAutoSvc->generateFromArticle([
                            'id'      => $newId,
                            'titre'   => $titre,
                            'extrait' => $extrait,
                            'contenu' => $contenu,
                            'slug'    => $slug,
                        ]);
                    }
                } catch (Throwable $gmbErr) {
                    error_log('[GMB Auto] Erreur génération post : ' . $gmbErr->getMessage());
                }

                jsRedirectEdit('?page=articles&action=edit&id='.$newId.'&msg=created');
            } else {
                $sets   = array_map(fn($c)=>"`{$c}` = ?", array_keys($safeMap));
                $values = array_values($safeMap);
                $values[] = $id;
                $pdo->prepare('UPDATE articles SET '.implode(', ',$sets).' WHERE id = ?')->execute($values);
                jsRedirectEdit('?page=articles&action=edit&id='.$id.'&msg=saved');
            }
        } catch (Throwable $e) { $error = $e->getMessage(); }
    }
}

// ─── Charger l'article ──────────────────────────────────────────────────────
$article = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if (!$article) {
        echo '<div style="padding:60px;text-align:center;">
              <div style="font-size:48px;margin-bottom:16px;">🔍</div>
              <h3>Article introuvable (ID: '.$id.')</h3>
              <a href="?page=articles" style="color:#6366f1;font-weight:600;text-decoration:none;">
              <i class="fas fa-arrow-left"></i> Retour</a></div>';
        return;
    }
}

// ─── Normalisations ─────────────────────────────────────────────────────────
$currentStatus = 'draft';
if (!empty($article['status'])  && $article['status']  === 'published') $currentStatus = 'published';
elseif (!empty($article['statut']) && $article['statut'] === 'publie')  $currentStatus = 'published';

// Scores — accepte noms FR et EN
$seoScore = (int)($article['seo_score']      ?? $article['score_technique']  ?? 0);
$semScore = (int)($article['semantic_score'] ?? $article['score_semantique'] ?? 0);
$readingTimeVal = (int)($article['reading_time'] ?? $article['temps_lecture'] ?? 0);
$wordCount = (int)($article['word_count'] ?? 0);

// Classes scores
$seoClass = $seoScore >= 80 ? 'excellent' : ($seoScore >= 60 ? 'good' : ($seoScore >= 40 ? 'ok' : ($seoScore > 0 ? 'bad' : 'none')));
$semClass = $semScore >= 70 ? 'excellent' : ($semScore >= 50 ? 'good' : ($semScore >= 30 ? 'ok' : ($semScore > 0 ? 'bad' : 'none')));

$e = fn(string $k, string $d = '') => htmlspecialchars((string)($article[$k] ?? $d));

// SERP
$serpTitleInit = $article['seo_title'] ?? $article['meta_title'] ?? $article['titre'] ?? '';
$serpDescInit  = $article['seo_description'] ?? $article['meta_description'] ?? $article['extrait'] ?? '';

$isEdit    = ($action === 'edit' && $article !== null);
$pageTitle = $isEdit ? 'Modifier l\'article' : 'Nouvel article';
?>

<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════
   ARTICLE EDITOR v5.1 — Sidebar renforcée
═══════════════════════════════════════════════════════════ */
:root {
    --ei-primary:   #6366f1; --ei-primary-d: #4f46e5;
    --ei-success:   #10b981; --ei-warning: #f59e0b;
    --ei-danger:    #ef4444; --ei-ai: #8b5cf6;
    --ei-ai-d:      #7c3aed; --ei-ai-light: #ede9fe;
    --ei-ai-border: #c4b5fd;
    --bg-card: #ffffff; --bg-page: #f8fafc;
    --bdr: #e2e8f0; --t1: #0f172a; --t2: #374151; --t3: #94a3b8;
    --r: 14px; --r-sm: 10px;
    --sh: 0 1px 3px rgba(0,0,0,.07); --sh-md: 0 4px 16px rgba(0,0,0,.08); --sh-lg: 0 10px 40px rgba(0,0,0,.14);
}
.ae5 { font-family: 'Inter', -apple-system, sans-serif; color: var(--t1); }

/* ─── Header ─── */
.ae5-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.ae5-header h2 { font-size:20px; font-weight:700; display:flex; align-items:center; gap:10px; margin:0; }
.ae5-header h2 .id-tag { font-size:13px; color:var(--t3); font-weight:400; }
.ae5-btns { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.ae5-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border:none; border-radius:var(--r-sm); font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; text-decoration:none; white-space:nowrap; font-family:inherit; }
.ae5-btn-ghost  { background:var(--bg-card); color:#64748b; border:1px solid var(--bdr); }
.ae5-btn-ghost:hover  { border-color:var(--ei-primary); color:var(--ei-primary); }
.ae5-btn-draft  { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
.ae5-btn-draft:hover  { background:#f59e0b; color:#fff; border-color:#f59e0b; }
.ae5-btn-publish{ background:var(--ei-success); color:#fff; }
.ae5-btn-publish:hover{ background:#059669; box-shadow:0 4px 12px rgba(16,185,129,.35); }
.ae5-btn-preview{ background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; }
.ae5-btn-preview:hover{ background:#0ea5e9; color:#fff; border-color:#0ea5e9; }

/* ─── Messages ─── */
.ae5-msg { padding:13px 18px; border-radius:var(--r-sm); margin-bottom:20px; font-size:14px; font-weight:500; display:flex; align-items:center; gap:10px; }
.ae5-msg.success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
.ae5-msg.error   { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

/* ─── Grid ─── */
.ae5-grid { display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start; }
@media(max-width:1180px){ .ae5-grid{ grid-template-columns:1fr; } }

/* ─── Cards ─── */
.ae5-card { background:var(--bg-card); border:1px solid var(--bdr); border-radius:var(--r); box-shadow:var(--sh); overflow:hidden; margin-bottom:20px; }
.ae5-card-header { padding:14px 20px; border-bottom:1px solid var(--bdr); background:#fafbfc; display:flex; align-items:center; justify-content:space-between; }
.ae5-card-title { font-size:13px; font-weight:700; color:var(--t1); display:flex; align-items:center; gap:8px; text-transform:uppercase; letter-spacing:.04em; }
.ae5-card-title i { color:var(--ei-primary); }
.ae5-card-body { padding:20px; }

/* ─── Champs ─── */
.ae5-field { margin-bottom:16px; }
.ae5-field:last-child{ margin-bottom:0; }
.ae5-label { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }
.ae5-label-text { font-size:12px; font-weight:600; color:#374151; display:flex; align-items:center; gap:6px; text-transform:uppercase; letter-spacing:.04em; }
.ae5-label-text i { color:var(--ei-primary); font-size:11px; }
.ae5-char-count { font-size:11px; font-weight:600; padding:2px 8px; border-radius:6px; color:var(--t3); background:var(--bg-page); }
.ae5-char-count.ok   { color:#059669; background:#d1fae5; }
.ae5-char-count.warn { color:#d97706; background:#fef3c7; }
.ae5-char-count.err  { color:#dc2626; background:#fee2e2; }
.ae5-input,.ae5-select,.ae5-textarea { width:100%; padding:10px 14px; border:1px solid var(--bdr); border-radius:var(--r-sm); font-size:14px; color:var(--t1); background:var(--bg-card); transition:border .15s,box-shadow .15s; box-sizing:border-box; font-family:inherit; outline:none; }
.ae5-input:focus,.ae5-select:focus,.ae5-textarea:focus { border-color:var(--ei-primary); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.ae5-input-xl { font-size:18px; font-weight:600; padding:13px 16px; }
.ae5-textarea { resize:vertical; line-height:1.6; }
.ae5-input-group { display:flex; gap:8px; align-items:flex-start; }
.ae5-input-group .ae5-input,.ae5-input-group .ae5-textarea { flex:1; min-width:0; }
.ae5-slug-hint { font-size:11px; color:var(--t3); margin-top:5px; }
.ae5-slug-hint strong { color:var(--ei-primary); }

/* ─── Boutons IA inline ─── */
.ae5-ai-btn { display:inline-flex; align-items:center; gap:5px; padding:7px 12px; border:none; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; transition:all .2s; white-space:nowrap; flex-shrink:0; font-family:inherit; line-height:1; }
.ae5-ai-btn-v { background:linear-gradient(135deg,#8b5cf6,#7c3aed); color:#fff; }
.ae5-ai-btn-v:hover { transform:translateY(-1px); box-shadow:0 3px 10px rgba(139,92,246,.4); }
.ae5-ai-btn-v:disabled { opacity:.5; cursor:wait; transform:none; box-shadow:none; }
.ae5-ai-btn-c { background:linear-gradient(135deg,#06b6d4,#0891b2); color:#fff; }
.ae5-ai-btn-c:hover { transform:translateY(-1px); box-shadow:0 3px 10px rgba(6,182,212,.4); }

/* ─── SEO Tabs ─── */
.ae5-seo-tabs { display:flex; gap:3px; background:var(--bg-page); border:1px solid var(--bdr); border-radius:10px; padding:3px; margin-bottom:20px; }
.ae5-seo-tab { flex:1; padding:8px 10px; border:none; background:transparent; border-radius:8px; font-size:12px; font-weight:600; color:#64748b; cursor:pointer; transition:all .2s; font-family:inherit; }
.ae5-seo-tab.active { background:var(--bg-card); color:var(--t1); box-shadow:0 1px 4px rgba(0,0,0,.09); }
.ae5-seo-panel { display:none; }
.ae5-seo-panel.active { display:block; }

/* ─── SERP Preview ─── */
.ae5-serp { background:#f8fafc; border:1px solid var(--bdr); border-radius:10px; padding:16px; margin-top:16px; }
.ae5-serp-label { font-size:11px; color:var(--t3); font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px; }
.ae5-serp-title { color:#1a0dab; font-size:18px; font-weight:400; margin-bottom:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-family:Arial,sans-serif; }
.ae5-serp-url   { color:#006621; font-size:13px; margin-bottom:3px; font-family:Arial,sans-serif; }
.ae5-serp-desc  { color:#545454; font-size:13px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-family:Arial,sans-serif; }

/* ─── Quill ─── */
.ae5-quill-wrap { border:1px solid var(--bdr); border-radius:var(--r-sm); overflow:hidden; }
.ae5-quill-wrap .ql-toolbar   { border:none!important; border-bottom:1px solid var(--bdr)!important; background:#fafbfc; padding:10px 14px; }
.ae5-quill-wrap .ql-container { border:none!important; font-size:15px; }
.ae5-quill-wrap .ql-editor    { min-height:360px; padding:20px; line-height:1.75; }
.ae5-quill-wrap.focused       { border-color:var(--ei-primary); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.ae5-wordstats { display:flex; gap:18px; padding:10px 14px; border-top:1px solid var(--bdr); background:#fafbfc; font-size:12px; color:var(--t3); }
.ae5-wordstats strong { color:var(--t1); }

/* ─── Panneau IA ─── */
.ae5-ai-panel { border-color:var(--ei-ai-border); background:linear-gradient(135deg,#faf5ff,#faf5ff); }
.ae5-ai-panel .ae5-card-header { background:var(--ei-ai-light); border-color:var(--ei-ai-border); }
.ae5-ai-panel .ae5-card-title,
.ae5-ai-panel .ae5-card-title i { color:var(--ei-ai); }

/* Bouton principal génération */
.ae5-ai-generate-btn {
    width: 100%; display: flex; align-items: center; gap: 14px;
    padding: 16px 18px; margin-bottom: 14px;
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    border: none; border-radius: 12px; cursor: pointer;
    transition: all .2s; font-family: inherit;
    box-shadow: 0 4px 14px rgba(124,58,237,.3);
}
.ae5-ai-generate-btn:hover {
    background: linear-gradient(135deg, #6d28d9, #5b21b6);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(124,58,237,.4);
}
.ae5-ai-generate-icon {
    width: 40px; height: 40px; background: rgba(255,255,255,.15);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff; flex-shrink: 0;
}
.ae5-ai-generate-text { flex: 1; text-align: left; }
.ae5-ai-generate-text strong { display: block; font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.ae5-ai-generate-text small  { font-size: 11px; color: rgba(255,255,255,.7); }
.ae5-ai-generate-arrow { color: rgba(255,255,255,.6); font-size: 13px; flex-shrink: 0; }

/* Boutons secondaires grille */
.ae5-ai-secondary { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
.ae5-ai-sec-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 12px; border-radius: 9px;
    background: var(--bg-card); color: var(--t2);
    border: 1px solid var(--ei-ai-border);
    font-size: 12px; font-weight: 600; cursor: pointer;
    transition: all .15s; font-family: inherit;
}
.ae5-ai-sec-btn:hover {
    background: var(--ei-ai); color: #fff;
    border-color: var(--ei-ai); transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(139,92,246,.2);
}
.ae5-ai-sec-btn i { font-size: 12px; width: 14px; text-align: center; flex-shrink: 0; }

/* ═══ POPUP GÉNÉRATION ════════════════════════════════════
   Fenêtre modale avec formulaire de paramètres IA
══════════════════════════════════════════════════════════ */
.ae5-gen-modal {
    display: none; position: fixed; inset: 0; z-index: 11000;
    background: rgba(15,23,42,.7); backdrop-filter: blur(6px);
    align-items: center; justify-content: center; padding: 20px;
}
.ae5-gen-modal.open { display: flex; animation: ae5FadeIn .25s ease; }
.ae5-gen-box {
    background: #fff; border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,.2);
    width: 100%; max-width: 580px;
    max-height: 92vh; display: flex; flex-direction: column;
    overflow: hidden;
}
/* En-tête popup */
.ae5-gen-header {
    padding: 22px 28px 18px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    flex-shrink: 0; position: relative; overflow: hidden;
}
.ae5-gen-header::after {
    content: '';
    position: absolute; top: -30%; right: -10%;
    width: 180px; height: 180px;
    background: rgba(255,255,255,.07); border-radius: 50%;
    pointer-events: none;
}
.ae5-gen-header h3 {
    font-size: 18px; font-weight: 800; color: #fff; margin: 0 0 4px;
    display: flex; align-items: center; gap: 10px;
}
.ae5-gen-header h3 i { font-size: 16px; }
.ae5-gen-header p  { font-size: 13px; color: rgba(255,255,255,.7); margin: 0; }
.ae5-gen-close {
    position: absolute; top: 16px; right: 16px; z-index: 1;
    width: 32px; height: 32px; border-radius: 8px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.2);
    color: #fff; cursor: pointer; display: flex; align-items: center;
    justify-content: center; font-size: 14px; transition: all .15s;
}
.ae5-gen-close:hover { background: rgba(255,255,255,.25); }

/* Corps formulaire */
.ae5-gen-body { padding: 24px 28px; overflow-y: auto; flex: 1; }
.ae5-gen-field { margin-bottom: 18px; }
.ae5-gen-field:last-child { margin-bottom: 0; }
.ae5-gen-label {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; font-weight: 700; color: #374151;
    text-transform: uppercase; letter-spacing: .05em; margin-bottom: 7px;
}
.ae5-gen-label i { color: var(--ei-ai); font-size: 11px; }
.ae5-gen-label .req { color: #ef4444; margin-left: 2px; }
.ae5-gen-input, .ae5-gen-select, .ae5-gen-textarea {
    width: 100%; padding: 11px 14px;
    border: 1.5px solid var(--bdr); border-radius: 10px;
    font-size: 14px; color: var(--t1); background: #fff;
    transition: border .15s, box-shadow .15s;
    font-family: inherit; outline: none; box-sizing: border-box;
}
.ae5-gen-input:focus, .ae5-gen-select:focus, .ae5-gen-textarea:focus {
    border-color: var(--ei-ai);
    box-shadow: 0 0 0 3px rgba(139,92,246,.12);
}
.ae5-gen-input::placeholder, .ae5-gen-textarea::placeholder { color: #9ca3af; }
.ae5-gen-textarea { resize: vertical; min-height: 80px; line-height: 1.6; }
.ae5-gen-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ae5-gen-hint { font-size: 11px; color: #9ca3af; margin-top: 5px; }

/* Options de longueur en chips */
.ae5-gen-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.ae5-gen-chip {
    padding: 7px 14px; border-radius: 8px;
    border: 1.5px solid var(--bdr);
    font-size: 12px; font-weight: 600; color: #6b7280;
    cursor: pointer; transition: all .15s; background: #fff;
    font-family: inherit;
}
.ae5-gen-chip.selected {
    border-color: var(--ei-ai); background: var(--ei-ai-light);
    color: var(--ei-ai);
}
.ae5-gen-chip:hover:not(.selected) { border-color: #c4b5fd; color: #6d28d9; }

/* Pied popup */
.ae5-gen-footer {
    padding: 16px 28px; border-top: 1px solid var(--bdr);
    display: flex; align-items: center; justify-content: space-between;
    background: #fafbfc; flex-shrink: 0; gap: 12px;
}
.ae5-gen-footer-hint { font-size: 12px; color: #9ca3af; }
.ae5-gen-submit {
    display: flex; align-items: center; gap: 9px;
    padding: 12px 28px; background: linear-gradient(135deg, #7c3aed, #6d28d9);
    border: none; border-radius: 10px; color: #fff;
    font-size: 14px; font-weight: 700; cursor: pointer;
    transition: all .2s; font-family: inherit;
    box-shadow: 0 4px 12px rgba(124,58,237,.3);
}
.ae5-gen-submit:hover { background: linear-gradient(135deg, #6d28d9, #5b21b6); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(124,58,237,.4); }
.ae5-gen-submit:disabled { opacity: .6; cursor: wait; transform: none; }
.ae5-gen-submit .spin { animation: ae5Spin .8s linear infinite; display: inline-block; }

/* ═══ SIDEBAR ════════════════════════════════════════════ */
.ae5-side-card { background:var(--bg-card); border:1px solid var(--bdr); border-radius:var(--r); box-shadow:var(--sh); overflow:hidden; margin-bottom:16px; }
.ae5-side-header { padding:13px 18px; border-bottom:1px solid var(--bdr); background:#fafbfc; display:flex; align-items:center; justify-content:space-between; }
.ae5-side-title { font-size:12px; font-weight:700; color:var(--t1); display:flex; align-items:center; gap:7px; text-transform:uppercase; letter-spacing:.04em; }
.ae5-side-title i { color:var(--ei-primary); }
.ae5-side-body { padding:16px 18px; }

/* Statut radios */
.ae5-status-opts { display:flex; gap:10px; }
.ae5-status-opt { flex:1; }
.ae5-status-opt input { display:none; }
.ae5-status-opt label { display:flex; align-items:center; justify-content:center; gap:6px; padding:10px; border:2px solid var(--bdr); border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; }
.ae5-status-opt input:checked + .lbl-draft     { border-color:#f59e0b; background:#fffbeb; color:#92400e; }
.ae5-status-opt input:checked + .lbl-published { border-color:#10b981; background:#ecfdf5; color:#065f46; }

/* ═══ SCORES SIDEBAR — VERSION COMPLÈTE ════════════════ */
.ae5-scores-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}

/* Grand cercle de score */
.ae5-big-score {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 14px 10px;
    background: var(--bg-page);
    border: 1px solid var(--bdr);
    border-radius: 12px;
    transition: border-color .2s;
}
.ae5-big-score:hover { border-color: var(--ei-primary); }
.ae5-big-score-ring {
    width: 56px; height: 56px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 800;
    border: 4px solid transparent;
    position: relative;
}
.ae5-big-score-ring.excellent { background:#ecfdf5; border-color:#10b981; color:#059669; }
.ae5-big-score-ring.good      { background:#eff6ff; border-color:#3b82f6; color:#2563eb; }
.ae5-big-score-ring.ok        { background:#fefce8; border-color:#f59e0b; color:#d97706; }
.ae5-big-score-ring.bad       { background:#fef2f2; border-color:#ef4444; color:#dc2626; }
.ae5-big-score-ring.none      { background:var(--bg-page); border-color:var(--bdr); border-style:dashed; color:var(--t3); }
.ae5-big-score-label { font-size:10px; font-weight:700; color:var(--t3); text-transform:uppercase; letter-spacing:.05em; text-align:center; }
.ae5-big-score-sub   { font-size:9px; color:var(--t3); text-align:center; margin-top:-2px; }

/* Barre de score horizontal */
.ae5-score-bars-list { display:flex; flex-direction:column; gap:8px; margin-bottom:14px; }
.ae5-score-row-item  { display:flex; flex-direction:column; gap:3px; }
.ae5-score-row-header{ display:flex; justify-content:space-between; align-items:center; }
.ae5-score-row-name  { font-size:11px; color:var(--t2); font-weight:600; }
.ae5-score-row-val   { font-size:11px; font-weight:800; }
.ae5-score-bar-track { height:5px; background:var(--bdr); border-radius:3px; overflow:hidden; }
.ae5-score-bar-prog  { height:100%; border-radius:3px; transition:width .5s cubic-bezier(.4,0,.2,1), background .4s; }
/* couleurs par classe */
.ae5-score-bar-prog.excellent, .ae5-score-row-val.excellent { background:#10b981; color:#059669; }
.ae5-score-bar-prog.good,      .ae5-score-row-val.good      { background:#3b82f6; color:#2563eb; }
.ae5-score-bar-prog.ok,        .ae5-score-row-val.ok        { background:#f59e0b; color:#d97706; }
.ae5-score-bar-prog.bad,       .ae5-score-row-val.bad       { background:#ef4444; color:#dc2626; }
.ae5-score-bar-prog.none,      .ae5-score-row-val.none      { background:var(--bdr); color:var(--t3); }

/* SERP Score panel */
.ae5-serp-score-panel {
    background: var(--bg-page);
    border: 1px solid var(--bdr);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 12px;
}
.ae5-serp-score-title-preview {
    font-size: 14px; font-weight: 400; color: #1a0dab;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    margin-bottom: 3px; font-family: Arial, sans-serif;
}
.ae5-serp-score-url {
    font-size: 11px; color: #006621; margin-bottom: 4px; font-family: Arial, sans-serif;
}
.ae5-serp-score-desc {
    font-size: 11px; color: #545454; line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    font-family: Arial, sans-serif;
}
.ae5-serp-indicators {
    display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;
}
.ae5-serp-ind {
    font-size: 10px; font-weight: 700; padding: 2px 7px;
    border-radius: 5px; display: flex; align-items: center; gap: 3px;
}
.ae5-serp-ind.ok  { background: #d1fae5; color: #059669; }
.ae5-serp-ind.warn{ background: #fef3c7; color: #d97706; }
.ae5-serp-ind.err { background: #fee2e2; color: #dc2626; }
.ae5-serp-ind.def { background: var(--bg-page); color: var(--t3); border: 1px solid var(--bdr); }

/* Stats mini */
.ae5-stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:12px; }
.ae5-stat-mini { background:var(--bg-page); border-radius:10px; padding:9px 6px; text-align:center; border:1px solid var(--bdr); }
.ae5-stat-mini-val { font-size:17px; font-weight:800; color:var(--t1); font-family:'Inter',sans-serif; }
.ae5-stat-mini-lbl { font-size:9px; color:var(--t3); margin-top:1px; text-transform:uppercase; letter-spacing:.04em; }

/* Quick actions */
.ae5-quick { display:flex; flex-direction:column; gap:7px; }
.ae5-quick-item { display:flex; align-items:center; gap:10px; padding:10px 14px; background:var(--bg-page); border:1px solid var(--bdr); border-radius:10px; text-decoration:none; color:var(--t2); font-size:13px; font-weight:500; transition:all .2s; }
.ae5-quick-item:hover { background:#eef2ff; border-color:#c7d2fe; color:var(--ei-primary); }
.ae5-quick-item i { color:var(--ei-primary); width:18px; text-align:center; }

/* Image */
.ae5-img-zone { width:100%; min-height:150px; border:2px dashed var(--bdr); border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; position:relative; overflow:hidden; background:var(--bg-page); }
.ae5-img-zone:hover { border-color:var(--ei-primary); background:#eef2ff; }
.ae5-img-zone.has-img { border-style:solid; border-color:var(--bdr); }
.ae5-img-zone img { width:100%; height:100%; object-fit:cover; display:block; }
.ae5-img-placeholder { text-align:center; color:var(--t3); font-size:13px; }
.ae5-img-placeholder i { font-size:28px; margin-bottom:8px; display:block; opacity:.5; }
.ae5-img-remove { position:absolute; top:8px; right:8px; width:28px; height:28px; border-radius:50%; background:rgba(239,68,68,.9); color:#fff; border:none; cursor:pointer; font-size:12px; display:flex; align-items:center; justify-content:center; transition:all .2s; }
.ae5-img-remove:hover { background:#dc2626; transform:scale(1.1); }

/* Sidebar fields */
.ae5-sf { margin-bottom:12px; }
.ae5-sf label { display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:5px; text-transform:uppercase; letter-spacing:.04em; }

/* Danger zone */
.ae5-danger { border-color:#fca5a5; }
.ae5-danger .ae5-side-header { background:#fff1f1; border-color:#fca5a5; }
.ae5-danger .ae5-side-title,
.ae5-danger .ae5-side-title i { color:#dc2626; }
.ae5-btn-delete { width:100%; padding:11px; background:var(--bg-card); border:1px solid #fca5a5; border-radius:10px; color:#dc2626; font-weight:600; font-size:13px; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px; font-family:inherit; }
.ae5-btn-delete:hover { background:var(--ei-danger); color:#fff; border-color:var(--ei-danger); }

/* ─── Modale IA ─── */
.ae5-modal { display:none; position:fixed; inset:0; z-index:10000; background:rgba(15,23,42,.65); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:24px; }
.ae5-modal.open { display:flex; animation:ae5FadeIn .22s ease; }
.ae5-modal-box { background:var(--bg-card); border-radius:16px; box-shadow:var(--sh-lg); width:100%; max-width:720px; max-height:88vh; display:flex; flex-direction:column; overflow:hidden; }
.ae5-modal-hdr { padding:18px 24px; border-bottom:1px solid var(--bdr); display:flex; align-items:center; justify-content:space-between; background:var(--ei-ai-light); flex-shrink:0; }
.ae5-modal-hdr h3 { font-size:15px; font-weight:700; color:var(--ei-ai); display:flex; align-items:center; gap:8px; margin:0; }
.ae5-modal-close { width:32px; height:32px; border-radius:8px; background:none; border:1px solid var(--bdr); cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--t3); transition:all .15s; }
.ae5-modal-close:hover { background:var(--ei-danger); color:#fff; border-color:var(--ei-danger); }
.ae5-modal-body { padding:24px; overflow-y:auto; flex:1; }
.ae5-modal-ftr { padding:14px 24px; border-top:1px solid var(--bdr); display:flex; justify-content:flex-end; gap:10px; background:#fafbfc; flex-shrink:0; }
.ae5-loader { text-align:center; padding:40px 20px; color:var(--t3); }
.ae5-spinner { width:44px; height:44px; border:3px solid var(--bdr); border-top-color:var(--ei-ai); border-radius:50%; animation:ae5Spin .7s linear infinite; margin:0 auto 16px; }
.ae5-result { display:none; }
.ae5-result-text { font-size:13px; line-height:1.7; color:var(--t1); background:var(--bg-page); border:1px solid var(--bdr); border-radius:10px; padding:16px 18px; max-height:340px; overflow-y:auto; white-space:pre-wrap; }

/* Toast */
.ae5-toast { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; align-items:center; gap:10px; padding:12px 20px; border-radius:12px; box-shadow:var(--sh-lg); font-size:14px; font-weight:500; color:#fff; opacity:0; transform:translateY(16px); transition:all .3s cubic-bezier(.34,1.56,.64,1); pointer-events:none; max-width:380px; font-family:'Inter',-apple-system,sans-serif; }
.ae5-toast.show { opacity:1; transform:translateY(0); pointer-events:auto; }
.ae5-toast.success { background:var(--ei-success); }
.ae5-toast.error   { background:var(--ei-danger); }
.ae5-toast.ai      { background:var(--ei-ai); }
.ae5-toast.warn    { background:var(--ei-warning); }

@keyframes ae5Spin   { to { transform:rotate(360deg); } }
@keyframes ae5FadeIn { from { opacity:0; transform:scale(.97); } to { opacity:1; transform:scale(1); } }
.ae5-spin { display:inline-block; animation:ae5Spin .8s linear infinite; }
</style>

<!-- Toast -->
<div class="ae5-toast" id="ae5Toast"><i class="fas fa-check-circle" id="ae5ToastIco"></i><span id="ae5ToastMsg"></span></div>

<!-- ═══ MODALE IA ════════════════════════════════════════════ -->
<div class="ae5-modal" id="ae5Modal">
    <div class="ae5-modal-box">
        <div class="ae5-modal-hdr">
            <h3><i class="fas fa-robot"></i> <span id="ae5ModalTitle">Assistant IA</span></h3>
            <button class="ae5-modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="ae5-modal-body">
            <div class="ae5-loader" id="ae5ModalLoader">
                <div class="ae5-spinner"></div>
                <p id="ae5ModalLoaderTxt">Génération en cours…</p>
            </div>
            <div class="ae5-result" id="ae5ModalResult">
                <div class="ae5-result-text" id="ae5ModalResultTxt"></div>
            </div>
        </div>
        <div class="ae5-modal-ftr">
            <button class="ae5-btn ae5-btn-ghost" onclick="closeModal()">Fermer</button>
            <button class="ae5-btn ae5-btn-publish" id="ae5ModalApply" onclick="applyModal()" style="display:none">
                <i class="fas fa-check"></i> Appliquer
            </button>
        </div>
    </div>
</div>

<!-- ═══ ÉDITEUR ══════════════════════════════════════════════ -->
<div class="ae5">

<div class="ae5-header">
    <h2>
        <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>"></i>
        <?= htmlspecialchars($pageTitle) ?>
        <?php if ($isEdit): ?><span class="id-tag">#<?= $id ?></span><?php endif; ?>
    </h2>
    <div class="ae5-btns">
        <a href="?page=articles" class="ae5-btn ae5-btn-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
        <?php if ($isEdit && !empty($article['slug'])): ?>
        <a href="/blog/<?= $e('slug') ?>" target="_blank" class="ae5-btn ae5-btn-preview"><i class="fas fa-external-link-alt"></i> Voir</a>
        <?php endif; ?>
        <?php if ($aiAvailable): ?>
        <a href="?page=system/settings/ai" class="ae5-btn ae5-btn-ghost" title="Paramètres IA" style="gap:6px;padding:9px 13px;">
            <i class="fas fa-robot" style="color:#7c3aed;font-size:14px;"></i>
            <span style="font-size:12px;color:#7c3aed;font-weight:700;">IA</span>
            <i class="fas fa-cog" style="color:#94a3b8;font-size:11px;"></i>
        </a>
        <?php else: ?>
        <a href="?page=system/settings/ai" class="ae5-btn ae5-btn-ghost" title="Configurer l'IA" style="gap:6px;padding:9px 13px;border-color:#fca5a5;">
            <i class="fas fa-robot" style="color:#ef4444;font-size:14px;"></i>
            <span style="font-size:11px;color:#ef4444;font-weight:700;">Configurer IA</span>
        </a>
        <?php endif; ?>
        <button type="button" class="ae5-btn ae5-btn-draft"   onclick="saveArticle('draft')"><i class="fas fa-save"></i> Brouillon</button>
        <button type="button" class="ae5-btn ae5-btn-publish" onclick="saveArticle('published')"><i class="fas fa-check"></i> Publier</button>
    </div>
</div>

<?php if ($message): ?><div class="ae5-msg success"><i class="fas fa-check-circle"></i><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="ae5-msg error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form id="ae5Form" method="POST" action="?page=articles&action=<?= $action ?><?= $isEdit ? '&id='.$id : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="status"     id="ae5Status" value="<?= $currentStatus ?>">
    <input type="hidden" name="contenu"    id="ae5Contenu">

<div class="ae5-grid">

<!-- ════════════════ COLONNE PRINCIPALE ════════════════ -->
<div>

    <!-- Titre -->
    <div class="ae5-card">
        <div class="ae5-card-header"><span class="ae5-card-title"><i class="fas fa-heading"></i> Titre de l'article</span></div>
        <div class="ae5-card-body">
            <input type="text" name="titre" id="ae5Titre" class="ae5-input ae5-input-xl"
                   value="<?= $e('titre') ?>" placeholder="Titre accrocheur et optimisé SEO…" required>
        </div>
    </div>

    <!-- Slug -->
    <div class="ae5-card">
        <div class="ae5-card-header"><span class="ae5-card-title"><i class="fas fa-link"></i> Slug URL</span></div>
        <div class="ae5-card-body">
            <div class="ae5-input-group">
                <input type="text" name="slug" id="ae5Slug" class="ae5-input" value="<?= $e('slug') ?>" placeholder="url-de-votre-article">
                <button type="button" class="ae5-ai-btn ae5-ai-btn-c" onclick="genSlugLocal()" title="Générer depuis titre"><i class="fas fa-sync-alt"></i> Auto</button>
                <?php if ($aiAvailable): ?>
                <button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('slug')"><i class="fas fa-robot"></i> IA</button>
                <?php endif; ?>
            </div>
            <div class="ae5-slug-hint">votresite.fr/blog/<strong id="ae5SlugPreview"><?= $e('slug','…') ?></strong></div>
        </div>
    </div>

    <!-- Contenu Quill -->
    <div class="ae5-card">
        <div class="ae5-card-header">
            <span class="ae5-card-title"><i class="fas fa-pen-nib"></i> Contenu</span>
            <?php if ($aiAvailable): ?>
            <div style="display:flex;gap:8px;">
                <button type="button" class="ae5-ai-btn ae5-ai-btn-c" onclick="aiImprove()"><i class="fas fa-sparkles"></i> Améliorer</button>
                <button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiGenerate()"><i class="fas fa-magic"></i> Générer</button>
            </div>
            <?php endif; ?>
        </div>
        <div class="ae5-card-body" style="padding:0;">
            <div class="ae5-quill-wrap" id="ae5QuillWrap">
                <div id="ae5QuillEditor"><?= $article['contenu'] ?? '' ?></div>
            </div>
            <div class="ae5-wordstats">
                <span>Mots : <strong id="ae5Words">0</strong></span>
                <span>Lecture : <strong id="ae5ReadTime">1 min</strong></span>
                <span>Caractères : <strong id="ae5Chars">0</strong></span>
                <span style="margin-left:auto;font-weight:700;" id="ae5ContentScoreLabel"></span>
            </div>
        </div>
    </div>

    <!-- Extrait -->
    <div class="ae5-card">
        <div class="ae5-card-header">
            <span class="ae5-card-title"><i class="fas fa-quote-right"></i> Extrait</span>
            <?php if ($aiAvailable): ?>
            <button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('extrait')"><i class="fas fa-robot"></i> Générer</button>
            <?php endif; ?>
        </div>
        <div class="ae5-card-body">
            <div class="ae5-label">
                <span class="ae5-label-text"><i class="fas fa-align-left"></i> Résumé accrocheur</span>
                <span class="ae5-char-count" id="ae5ExtraitCount">0/280</span>
            </div>
            <textarea name="extrait" id="ae5Extrait" class="ae5-textarea" rows="3"
                      placeholder="Résumé affiché dans les listes et réseaux…"><?= $e('extrait') ?></textarea>
        </div>
    </div>

    <!-- SEO Tabs -->
    <div class="ae5-card">
        <div class="ae5-card-header">
            <span class="ae5-card-title"><i class="fas fa-search"></i> Optimisation SEO</span>
            <?php if ($aiAvailable): ?>
            <button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiMeta()"><i class="fas fa-magic"></i> Générer métas</button>
            <?php endif; ?>
        </div>
        <div class="ae5-card-body">
            <div class="ae5-seo-tabs">
                <button type="button" class="ae5-seo-tab active" onclick="seoTab('primary',this)">🎯 SEO Principal</button>
                <button type="button" class="ae5-seo-tab" onclick="seoTab('meta',this)">📋 Méta</button>
                <button type="button" class="ae5-seo-tab" onclick="seoTab('keywords',this)">🔑 Mots-clés</button>
            </div>

            <!-- Onglet SEO Principal -->
            <div class="ae5-seo-panel active" id="ae5SeoTab-primary">
                <div class="ae5-field">
                    <div class="ae5-label">
                        <span class="ae5-label-text"><i class="fas fa-tag"></i> SEO Title</span>
                        <span class="ae5-char-count" id="ae5SeoTitleCount">0/70</span>
                    </div>
                    <div class="ae5-input-group">
                        <input type="text" name="seo_title" id="ae5SeoTitle" class="ae5-input"
                               value="<?= $e('seo_title') ?>" placeholder="Titre SEO (50-60 car.)">
                        <?php if ($aiAvailable): ?><button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('seo_title')"><i class="fas fa-robot"></i> IA</button><?php endif; ?>
                    </div>
                </div>
                <div class="ae5-field">
                    <div class="ae5-label">
                        <span class="ae5-label-text"><i class="fas fa-align-right"></i> SEO Description</span>
                        <span class="ae5-char-count" id="ae5SeoDescCount">0/160</span>
                    </div>
                    <div class="ae5-input-group">
                        <textarea name="seo_description" id="ae5SeoDesc" class="ae5-textarea" rows="2"
                                  placeholder="Description SEO (140-155 car.)"><?= $e('seo_description') ?></textarea>
                        <?php if ($aiAvailable): ?><button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('seo_description')"><i class="fas fa-robot"></i> IA</button><?php endif; ?>
                    </div>
                </div>
                <!-- SERP Preview dans l'onglet -->
                <div class="ae5-serp">
                    <div class="ae5-serp-label">📱 Aperçu Google SERP</div>
                    <div class="ae5-serp-title" id="ae5SerpTitle"><?= htmlspecialchars($serpTitleInit ?: 'Titre de votre article') ?></div>
                    <div class="ae5-serp-url">🏠 votresite.fr › blog › <span id="ae5SerpSlug"><?= $e('slug','votre-article') ?></span></div>
                    <div class="ae5-serp-desc"  id="ae5SerpDesc"><?= htmlspecialchars($serpDescInit ?: 'Votre description…') ?></div>
                </div>
            </div>

            <!-- Onglet Méta -->
            <div class="ae5-seo-panel" id="ae5SeoTab-meta">
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Meta Title</span><span class="ae5-char-count" id="ae5MetaTitleCount">0/70</span></div>
                    <div class="ae5-input-group">
                        <input type="text" name="meta_title" id="ae5MetaTitle" class="ae5-input" value="<?= $e('meta_title') ?>" placeholder="Si différent du SEO Title…">
                        <?php if ($aiAvailable): ?><button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('meta_title')"><i class="fas fa-robot"></i> IA</button><?php endif; ?>
                    </div>
                </div>
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Meta Description</span><span class="ae5-char-count" id="ae5MetaDescCount">0/160</span></div>
                    <div class="ae5-input-group">
                        <textarea name="meta_description" id="ae5MetaDesc" class="ae5-textarea" rows="2" placeholder="Si différente…"><?= $e('meta_description') ?></textarea>
                        <?php if ($aiAvailable): ?><button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('meta_description')"><i class="fas fa-robot"></i> IA</button><?php endif; ?>
                    </div>
                </div>
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Meta Keywords</span></div>
                    <input type="text" name="meta_keywords" class="ae5-input" value="<?= $e('meta_keywords') ?>" placeholder="mot-clé 1, mot-clé 2…">
                </div>
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Balise H1</span></div>
                    <input type="text" name="h1" class="ae5-input" value="<?= $e('h1') ?>" placeholder="Laisser vide = identique au titre">
                </div>
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Alt titre</span></div>
                    <input type="text" name="alt_titre" class="ae5-input" value="<?= $e('alt_titre') ?>" placeholder="Titre alternatif">
                </div>
                <div class="ae5-field" style="display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="noindex" id="ae5Noindex" value="1" <?= !empty($article['noindex']) ? 'checked' : '' ?> style="width:auto;cursor:pointer;">
                    <label for="ae5Noindex" style="font-size:13px;font-weight:500;color:var(--t2);cursor:pointer;">Noindex — exclure des moteurs de recherche</label>
                </div>
            </div>

            <!-- Onglet Mots-clés -->
            <div class="ae5-seo-panel" id="ae5SeoTab-keywords">
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Mot-clé focus</span></div>
                    <div class="ae5-input-group">
                        <input type="text" name="focus_keyword" id="ae5FocusKw" class="ae5-input" value="<?= $e('focus_keyword') ?>" placeholder="ex : vendre maison bordeaux divorce">
                        <?php if ($aiAvailable): ?><button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('focus_keyword')"><i class="fas fa-robot"></i> IA</button><?php endif; ?>
                    </div>
                </div>
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">Mots-clés secondaires (LSI)</span></div>
                    <div class="ae5-input-group">
                        <input type="text" name="secondary_keywords" id="ae5SecKw" class="ae5-input" value="<?= $e('secondary_keywords') ?>" placeholder="terme 1, terme 2…">
                        <?php if ($aiAvailable): ?><button type="button" class="ae5-ai-btn ae5-ai-btn-v" onclick="aiField('secondary_keywords')"><i class="fas fa-robot"></i> IA</button><?php endif; ?>
                    </div>
                </div>
                <div class="ae5-field">
                    <div class="ae5-label"><span class="ae5-label-text">main_keyword <small>(legacy)</small></span></div>
                    <input type="text" name="main_keyword" class="ae5-input" value="<?= $e('main_keyword') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ PANNEAU IA ═══ -->
    <?php if ($aiAvailable): ?>
    <div class="ae5-card ae5-ai-panel">
        <div class="ae5-card-header">
            <span class="ae5-card-title"><i class="fas fa-robot"></i> Outils IA</span>
            <span style="font-size:11px;color:var(--ei-ai);font-weight:600;"><i class="fas fa-circle" style="font-size:7px;"></i> <?= htmlspecialchars($aiProvider) ?> actif</span>
        </div>
        <div class="ae5-card-body">
            <!-- Bouton principal → ouvre popup de génération -->
            <button type="button" class="ae5-ai-generate-btn" onclick="openGeneratePopup()">
                <span class="ae5-ai-generate-icon"><i class="fas fa-magic"></i></span>
                <span class="ae5-ai-generate-text">
                    <strong>Générer l'article complet</strong>
                    <small>Persona · Mot-clé · Longueur · Objectif</small>
                </span>
                <i class="fas fa-arrow-right ae5-ai-generate-arrow"></i>
            </button>
            <!-- Actions secondaires -->
            <div class="ae5-ai-secondary">
                <button type="button" class="ae5-ai-sec-btn" onclick="aiOutline()"><i class="fas fa-list"></i> Plan</button>
                <button type="button" class="ae5-ai-sec-btn" onclick="aiImprove()"><i class="fas fa-sparkles"></i> Améliorer</button>
                <button type="button" class="ae5-ai-sec-btn" onclick="aiMeta()"><i class="fas fa-search"></i> Métas</button>
                <button type="button" class="ae5-ai-sec-btn" onclick="aiFaq()"><i class="fas fa-question-circle"></i> FAQ</button>
                <button type="button" class="ae5-ai-sec-btn" onclick="aiKeywords()"><i class="fas fa-tags"></i> Mots-clés</button>
                <button type="button" class="ae5-ai-sec-btn" onclick="aiRewrite()"><i class="fas fa-redo"></i> Réécrire</button>
                <button type="button" class="ae5-ai-sec-btn" onclick="aiField('extrait')"><i class="fas fa-quote-right"></i> Extrait</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /main -->

<!-- ════════════════ SIDEBAR ════════════════ -->
<div>

    <!-- Publication -->
    <div class="ae5-side-card">
        <div class="ae5-side-header"><div class="ae5-side-title"><i class="fas fa-paper-plane"></i> Publication</div></div>
        <div class="ae5-side-body">
            <div class="ae5-status-opts">
                <div class="ae5-status-opt">
                    <input type="radio" name="status_radio" id="ae5StDraft" value="draft" <?= $currentStatus !== 'published' ? 'checked' : '' ?>>
                    <label for="ae5StDraft" class="lbl-draft"><i class="fas fa-pencil-alt"></i> Brouillon</label>
                </div>
                <div class="ae5-status-opt">
                    <input type="radio" name="status_radio" id="ae5StPublished" value="published" <?= $currentStatus === 'published' ? 'checked' : '' ?>>
                    <label for="ae5StPublished" class="lbl-published"><i class="fas fa-check"></i> Publié</label>
                </div>
            </div>
            <?php if ($isEdit && !empty($article['created_at'])): ?>
            <div style="margin-top:12px;font-size:11px;color:var(--t3);line-height:1.8;">
                Créé : <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                <?php if (!empty($article['updated_at'])): ?>
                <br>Modifié : <?= date('d/m/Y H:i', strtotime($article['updated_at'])) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="button" class="ae5-btn ae5-btn-draft" style="flex:1;justify-content:center;" onclick="saveArticle('draft')"><i class="fas fa-save"></i> Brouillon</button>
                <button type="button" class="ae5-btn ae5-btn-publish" style="flex:1;justify-content:center;" onclick="saveArticle('published')"><i class="fas fa-check"></i> Publier</button>
            </div>
        </div>
    </div>

    <!-- ═══ SCORES SEO + SÉMANTIQUE + SERP ═══ -->
    <div class="ae5-side-card">
        <div class="ae5-side-header">
            <div class="ae5-side-title"><i class="fas fa-chart-line"></i> Scores & SERP</div>
            <button type="button" onclick="calcSeoScore()" title="Recalculer"
                    style="background:none;border:none;cursor:pointer;color:var(--ei-primary);font-size:12px;padding:0;">
                <i class="fas fa-sync-alt" id="ae5CalcIco"></i>
            </button>
        </div>
        <div class="ae5-side-body">

            <!-- Deux cercles côte à côte -->
            <div class="ae5-scores-grid">
                <div class="ae5-big-score" title="Score SEO technique">
                    <div class="ae5-big-score-ring <?= $seoClass ?>" id="ae5ScoreCircleSeo">
                        <?= $seoScore > 0 ? $seoScore : '—' ?>
                    </div>
                    <div class="ae5-big-score-label">SEO</div>
                    <div class="ae5-big-score-sub" id="ae5SeoHint">
                        <?= $seoClass === 'excellent' ? 'Excellent !' : ($seoClass === 'good' ? 'Bon score' : ($seoClass === 'ok' ? 'À améliorer' : ($seoScore > 0 ? 'Insuffisant' : 'Non calculé'))) ?>
                    </div>
                </div>
                <div class="ae5-big-score" title="Score sémantique">
                    <div class="ae5-big-score-ring <?= $semClass ?>" id="ae5ScoreCircleSem">
                        <?= $semScore > 0 ? $semScore : '—' ?>
                    </div>
                    <div class="ae5-big-score-label">Sémantique</div>
                    <div class="ae5-big-score-sub" id="ae5SemHint">
                        <?= $semClass === 'excellent' ? 'Excellent !' : ($semClass === 'good' ? 'Bon score' : ($semClass === 'ok' ? 'À enrichir' : ($semScore > 0 ? 'Faible' : 'Non calculé'))) ?>
                    </div>
                </div>
            </div>

            <!-- Barres détaillées -->
            <div class="ae5-score-bars-list">
                <?php
                $bars = [
                    ['id'=>'titre',   'label'=>'Titre optimisé'],
                    ['id'=>'meta',    'label'=>'Méta title/desc'],
                    ['id'=>'content', 'label'=>'Longueur contenu'],
                    ['id'=>'kw',      'label'=>'Densité mot-clé'],
                ];
                foreach ($bars as $bar): ?>
                <div class="ae5-score-row-item">
                    <div class="ae5-score-row-header">
                        <span class="ae5-score-row-name"><?= $bar['label'] ?></span>
                        <span class="ae5-score-row-val none" id="ae5SBar-<?= $bar['id'] ?>-val">—</span>
                    </div>
                    <div class="ae5-score-bar-track">
                        <div class="ae5-score-bar-prog none" id="ae5SBar-<?= $bar['id'] ?>-fill" style="width:0%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Mini SERP sidebar -->
            <div class="ae5-serp-score-panel">
                <div style="font-size:10px;color:var(--t3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;display:flex;align-items:center;gap:5px;">
                    <i class="fab fa-google" style="font-size:11px;color:#4285f4;"></i> Aperçu SERP
                </div>
                <div class="ae5-serp-score-title-preview" id="ae5SerpSideTitle">
                    <?= htmlspecialchars($serpTitleInit ?: 'Titre de votre article') ?>
                </div>
                <div class="ae5-serp-score-url">
                    votresite.fr › blog › <span id="ae5SerpSideSlug"><?= $e('slug','votre-article') ?></span>
                </div>
                <div class="ae5-serp-score-desc" id="ae5SerpSideDesc">
                    <?= htmlspecialchars($serpDescInit ?: 'Votre description apparaîtra ici…') ?>
                </div>
                <!-- Indicateurs longueur -->
                <div class="ae5-serp-indicators">
                    <span class="ae5-serp-ind def" id="ae5SerpIndTitle"><i class="fas fa-heading"></i> Title: <span id="ae5SerpTitleLen">0</span>/60</span>
                    <span class="ae5-serp-ind def" id="ae5SerpIndDesc"><i class="fas fa-align-left"></i> Desc: <span id="ae5SerpDescLen">0</span>/160</span>
                    <span class="ae5-serp-ind def" id="ae5SerpIndKw"><i class="fas fa-key"></i> KW</span>
                </div>
            </div>

            <!-- Stats article -->
            <div class="ae5-stats-row">
                <div class="ae5-stat-mini">
                    <div class="ae5-stat-mini-val" id="ae5StatWords"><?= $wordCount > 0 ? number_format($wordCount,0,',',' ') : '—' ?></div>
                    <div class="ae5-stat-mini-lbl">Mots</div>
                </div>
                <div class="ae5-stat-mini">
                    <div class="ae5-stat-mini-val" id="ae5StatRead"><?= $readingTimeVal > 0 ? $readingTimeVal.'m' : '—' ?></div>
                    <div class="ae5-stat-mini-lbl">Lecture</div>
                </div>
                <div class="ae5-stat-mini">
                    <div class="ae5-stat-mini-val" id="ae5StatSeoNum"><?= $seoScore > 0 ? $seoScore : '—' ?></div>
                    <div class="ae5-stat-mini-lbl">SEO %</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Actions rapides -->
    <?php if ($isEdit): ?>
    <div class="ae5-side-card">
        <div class="ae5-side-header"><div class="ae5-side-title"><i class="fas fa-bolt"></i> Actions rapides</div></div>
        <div class="ae5-side-body">
            <div class="ae5-quick">
                <a href="?page=seo-semantic&analyze=article&id=<?= $id ?>" class="ae5-quick-item"><i class="fas fa-brain"></i> Analyse sémantique</a>
                <a href="?page=seo-articles" class="ae5-quick-item"><i class="fas fa-chart-bar"></i> Vue SEO articles</a>
                <?php if (!empty($article['slug'])): ?>
                <a href="/blog/<?= $e('slug') ?>" target="_blank" class="ae5-quick-item"><i class="fas fa-external-link-alt"></i> Voir en ligne</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══ PANNEAU GMB — Posts Google My Business ═══ -->
    <?php if ($isEdit):
        // Charger les posts GMB de cet article
        $gmbServiceFile = dirname(__DIR__, 3) . '/includes/classes/GmbArticlePostService.php';
        $gmbPosts = [];
        $gmbCounts = ['total'=>0,'published'=>0,'draft'=>0,'pending'=>0,'failed'=>0];
        if (file_exists($gmbServiceFile)) {
            try {
                require_once $gmbServiceFile;
                $gmbSvc = new GmbArticlePostService($pdo);
                $gmbPosts = $gmbSvc->getByArticle($id);
                $gmbCounts = $gmbSvc->countByArticle($id);
            } catch (Throwable $e) {}
        }
    ?>
    <div class="ae5-side-card" id="ae5GmbPanel">
        <div class="ae5-side-header">
            <div class="ae5-side-title"><i class="fab fa-google" style="color:#4285f4;"></i> Google My Business</div>
            <?php if ($gmbCounts['total'] > 0): ?>
            <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:10px;
                  background:<?= $gmbCounts['published']>0?'#d1fae5':'#fef3c7' ?>;
                  color:<?= $gmbCounts['published']>0?'#065f46':'#92400e' ?>;">
                <?= $gmbCounts['published'] ?>/<?= $gmbCounts['total'] ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="ae5-side-body">

            <?php if (empty($gmbPosts)): ?>
            <div style="text-align:center;padding:12px 0;color:var(--t3,#9ca3af);font-size:12px;">
                <i class="fas fa-bullhorn" style="font-size:20px;opacity:.4;display:block;margin-bottom:8px;"></i>
                Aucun post GMB pour cet article
            </div>
            <?php else: ?>
            <div id="ae5GmbPostsList" style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($gmbPosts as $gp):
                    $gpStatus = $gp['status'];
                    $gpStatusColors = [
                        'published' => ['bg'=>'#d1fae5','color'=>'#065f46','icon'=>'fa-check-circle','label'=>'Publié'],
                        'draft'     => ['bg'=>'#e0e7ff','color'=>'#3730a3','icon'=>'fa-pencil-alt','label'=>'Brouillon'],
                        'pending'   => ['bg'=>'#fef3c7','color'=>'#92400e','icon'=>'fa-clock','label'=>'En attente'],
                        'failed'    => ['bg'=>'#fee2e2','color'=>'#991b1b','icon'=>'fa-exclamation-triangle','label'=>'Échec'],
                    ];
                    $sc = $gpStatusColors[$gpStatus] ?? $gpStatusColors['draft'];
                ?>
                <div class="ae5-gmb-post-item" data-post-id="<?= (int)$gp['id'] ?>"
                     style="border:1px solid var(--bdr,#e5e7eb);border-radius:8px;padding:10px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;">
                            <i class="fas <?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
                        </span>
                        <span style="color:var(--t3,#9ca3af);font-size:10px;">
                            <?= !empty($gp['created_at']) ? date('d/m/Y H:i', strtotime($gp['created_at'])) : '' ?>
                        </span>
                    </div>
                    <div style="color:var(--t2,#6b7280);line-height:1.5;max-height:60px;overflow:hidden;">
                        <?= htmlspecialchars(mb_substr($gp['post_text'] ?? '', 0, 150)) ?><?= mb_strlen($gp['post_text']??'') > 150 ? '...' : '' ?>
                    </div>
                    <div style="display:flex;gap:6px;margin-top:8px;">
                        <?php if (in_array($gpStatus, ['draft','failed'])): ?>
                        <button type="button" class="ae5-gmb-btn ae5-gmb-btn-pub" onclick="gmbRepublish(<?= (int)$gp['id'] ?>)" title="Republier">
                            <i class="fas fa-paper-plane"></i> Republier
                        </button>
                        <?php endif; ?>
                        <button type="button" class="ae5-gmb-btn ae5-gmb-btn-del" onclick="gmbDelete(<?= (int)$gp['id'] ?>)" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <?php if (!empty($gp['gmb_error'])): ?>
                    <div style="margin-top:6px;font-size:10px;color:#991b1b;background:#fee2e2;padding:4px 8px;border-radius:4px;">
                        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($gp['gmb_error']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <button type="button" class="ae5-gmb-generate-btn" id="ae5GmbGenerateBtn" onclick="gmbGenerate()">
                <i class="fas fa-magic" id="ae5GmbGenIco"></i> Générer un post GMB
            </button>
        </div>
    </div>

    <style>
    .ae5-gmb-generate-btn {
        width:100%;margin-top:10px;padding:10px 14px;border:2px dashed var(--bdr,#d1d5db);border-radius:8px;
        background:transparent;color:var(--ei-primary,#6366f1);font-weight:600;font-size:12px;cursor:pointer;
        display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;
    }
    .ae5-gmb-generate-btn:hover { background:var(--ei-primary,#6366f1);color:#fff;border-color:var(--ei-primary,#6366f1); }
    .ae5-gmb-btn {
        padding:4px 10px;border-radius:5px;border:none;font-size:10px;font-weight:600;cursor:pointer;
        display:inline-flex;align-items:center;gap:4px;transition:all .15s;
    }
    .ae5-gmb-btn-pub { background:#e0e7ff;color:#4338ca; }
    .ae5-gmb-btn-pub:hover { background:#4338ca;color:#fff; }
    .ae5-gmb-btn-del { background:#fee2e2;color:#991b1b; }
    .ae5-gmb-btn-del:hover { background:#991b1b;color:#fff; }
    </style>
    <?php endif; ?>

    <!-- Image à la une -->
    <div class="ae5-side-card">
        <div class="ae5-side-header"><div class="ae5-side-title"><i class="fas fa-image"></i> Image à la une</div></div>
        <div class="ae5-side-body">
            <div class="ae5-img-zone <?= !empty($article['featured_image']) ? 'has-img' : '' ?>" id="ae5ImgZone"
                 onclick="document.getElementById('ae5ImgFile').click()">
                <?php if (!empty($article['featured_image'])): ?>
                    <img id="ae5ImgPreview" src="<?= $e('featured_image') ?>" alt="">
                    <button type="button" class="ae5-img-remove" onclick="event.stopPropagation();removeImg()"><i class="fas fa-times"></i></button>
                <?php else: ?>
                    <div class="ae5-img-placeholder" id="ae5ImgPlaceholder">
                        <i class="fas fa-cloud-upload-alt"></i><span>Cliquer pour ajouter</span>
                    </div>
                    <img id="ae5ImgPreview" src="" alt="" style="display:none;">
                <?php endif; ?>
            </div>
            <input type="file" id="ae5ImgFile" accept="image/*" style="display:none;" onchange="uploadImg(this)">
            <input type="hidden" name="featured_image" id="ae5FeaturedImg" value="<?= $e('featured_image') ?>">
            <input type="text" name="featured_image_alt" class="ae5-input" style="margin-top:8px;font-size:12px;" value="<?= $e('featured_image_alt') ?>" placeholder="Texte alternatif (SEO)">
        </div>
    </div>

    <!-- Catégorisation -->
    <div class="ae5-side-card">
        <div class="ae5-side-header"><div class="ae5-side-title"><i class="fas fa-tags"></i> Catégorisation</div></div>
        <div class="ae5-side-body">
            <div class="ae5-sf"><label>Ville</label><input type="text" name="ville" class="ae5-input" value="<?= $e('ville') ?>" placeholder="Bordeaux"></div>
            <div class="ae5-sf"><label>Raison de vente</label>
                <select name="raison_vente" class="ae5-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach (['Divorce / Séparation','Succession / Héritage','Difficulté financière','Mutation professionnelle','Retraite','Investissement','Déménagement','Autre'] as $rv): ?>
                    <option value="<?= htmlspecialchars($rv) ?>" <?= ($article['raison_vente']??'') === $rv ? 'selected':'' ?>><?= htmlspecialchars($rv) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ae5-sf"><label>Persona cible</label>
                <select name="persona" class="ae5-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach (['Primo-accédant','Vendeur expérimenté','Investisseur','Expatrié','Retraité','Héritier','Divorcé','Professionnel'] as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" <?= ($article['persona']??'') === $p ? 'selected':'' ?>><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ae5-sf"><label>Type d'article</label>
                <select name="type_article" class="ae5-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach (['guide'=>'Guide pratique','conseil'=>'Conseil expert','analyse'=>'Analyse marché','quartier'=>'Quartier','actualite'=>'Actualité','temoignage'=>'Témoignage','juridique'=>'Juridique'] as $k=>$l): ?>
                    <option value="<?= $k ?>" <?= ($article['type_article']??'') === $k ? 'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ae5-sf"><label>Niveau de conscience</label>
                <select name="niveau_conscience" class="ae5-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach (['Problème','Solution','Produit','Marque','Décision'] as $nc): ?>
                    <option value="<?= $nc ?>" <?= ($article['niveau_conscience']??'') === $nc ? 'selected':'' ?>><?= $nc ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ae5-sf"><label>Localité</label><input type="text" name="localite" class="ae5-input" value="<?= $e('localite') ?>" placeholder="Chartrons, Bastide…"></div>
            <div class="ae5-sf"><label>Catégorie</label><input type="text" name="category" class="ae5-input" value="<?= $e('category') ?>" placeholder="Divorce, Marché…"></div>
            <div class="ae5-sf"><label>Auteur</label><input type="text" name="author" class="ae5-input" value="<?= $e('author') ?>" placeholder="Nom de l'auteur"></div>
        </div>
    </div>

    <!-- Danger zone -->
    <?php if ($isEdit): ?>
    <div class="ae5-side-card ae5-danger">
        <div class="ae5-side-header"><div class="ae5-side-title"><i class="fas fa-exclamation-triangle"></i> Zone dangereuse</div></div>
        <div class="ae5-side-body">
            <p style="font-size:12px;color:#7f1d1d;margin:0 0 12px;">La suppression est définitive et irréversible.</p>
            <button type="button" class="ae5-btn-delete" onclick="delArticle()"><i class="fas fa-trash"></i> Supprimer définitivement</button>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /sidebar -->
</div><!-- /grid -->
</form>
</div><!-- /ae5 -->

<!-- ═══════════════════════════════════════════════════════════════
     POPUP GÉNÉRATION IA — Paramètres article
════════════════════════════════════════════════════════════════ -->
<div class="ae5-gen-modal" id="ae5GenModal" onclick="if(event.target===this)closeGeneratePopup()">
<div class="ae5-gen-box">

    <!-- En-tête -->
    <div class="ae5-gen-header">
        <button class="ae5-gen-close" onclick="closeGeneratePopup()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-magic"></i> Générer un article avec l'IA</h3>
        <p>Renseignez les paramètres pour obtenir un contenu optimisé et personnalisé</p>
    </div>

    <!-- Corps formulaire -->
    <div class="ae5-gen-body">

        <!-- Sujet -->
        <div class="ae5-gen-field">
            <label class="ae5-gen-label"><i class="fas fa-pen"></i> Sujet de l'article <span class="req">*</span></label>
            <input type="text" id="genSubject" class="ae5-gen-input"
                placeholder="Ex : Vendre son appartement à Bordeaux en 2025 — conseils et étapes"
                oninput="genSyncTitle()">
            <p class="ae5-gen-hint">Le titre et le slug seront générés automatiquement à partir de ce sujet.</p>
        </div>

        <!-- Persona -->
        <div class="ae5-gen-field">
            <label class="ae5-gen-label"><i class="fas fa-user-circle"></i> Type de persona cible <span class="req">*</span></label>
            <select id="genPersona" class="ae5-gen-select">
                <option value="">— Choisir le persona —</option>
                <optgroup label="Vendeurs">
                    <option value="Propriétaire vendeur primo-accédant, stressé, peu informé">Propriétaire vendeur — Primo-accédant</option>
                    <option value="Propriétaire vendeur expérimenté, rationnel, axé rentabilité">Propriétaire vendeur — Expérimenté</option>
                    <option value="Propriétaire pressé de vendre, situation urgente (divorce, succession)">Propriétaire vendeur — Situation urgente</option>
                </optgroup>
                <optgroup label="Acheteurs">
                    <option value="Acheteur primo-accédant, jeune couple, budget serré, anxieux">Acheteur — Primo-accédant</option>
                    <option value="Acheteur investisseur locatif, cherche rentabilité et défiscalisation">Acheteur — Investisseur locatif</option>
                    <option value="Acheteur retraité, recherche qualité de vie et proximité services">Acheteur — Retraité</option>
                    <option value="Acheteur famille, priorité écoles, espace et sécurité">Acheteur — Famille</option>
                </optgroup>
                <optgroup label="Autres">
                    <option value="Grand public, curieux, découverte du marché immobilier">Grand public</option>
                    <option value="Professionnel immobilier ou investisseur averti">Professionnel / investisseur averti</option>
                </optgroup>
            </select>
        </div>

        <!-- Mot-clé focus + Objectif -->
        <div class="ae5-gen-row">
            <div class="ae5-gen-field" style="margin-bottom:0">
                <label class="ae5-gen-label"><i class="fas fa-key"></i> Mot-clé principal <span class="req">*</span></label>
                <input type="text" id="genKeyword" class="ae5-gen-input"
                    placeholder="Ex : vendre appartement Bordeaux">
                <p class="ae5-gen-hint">Sera intégré naturellement (densité 1-2 %)</p>
            </div>
            <div class="ae5-gen-field" style="margin-bottom:0">
                <label class="ae5-gen-label"><i class="fas fa-map-marker-alt"></i> Ville / Zone</label>
                <input type="text" id="genVille" class="ae5-gen-input"
                    placeholder="Ex : Bordeaux, Rive droite">
                <p class="ae5-gen-hint">Optionnel — pour un contenu géolocalisé</p>
            </div>
        </div>

        <!-- Nombre de mots -->
        <div class="ae5-gen-field" style="margin-top:18px">
            <label class="ae5-gen-label"><i class="fas fa-align-left"></i> Longueur de l'article</label>
            <div class="ae5-gen-chips">
                <button type="button" class="ae5-gen-chip" data-words="600" onclick="selectChip(this)">600 mots<br><small>Court</small></button>
                <button type="button" class="ae5-gen-chip selected" data-words="1200" onclick="selectChip(this)">1 200 mots<br><small>Standard ✓</small></button>
                <button type="button" class="ae5-gen-chip" data-words="1800" onclick="selectChip(this)">1 800 mots<br><small>Long</small></button>
                <button type="button" class="ae5-gen-chip" data-words="2500" onclick="selectChip(this)">2 500 mots<br><small>Pilier SEO</small></button>
            </div>
        </div>

        <!-- Objectif -->
        <div class="ae5-gen-field">
            <label class="ae5-gen-label"><i class="fas fa-bullseye"></i> Objectif principal de l'article <span class="req">*</span></label>
            <select id="genObjectif" class="ae5-gen-select">
                <option value="">— Choisir l'objectif —</option>
                <option value="Générer des leads : inciter le lecteur à contacter un conseiller immobilier">🎯 Générer des leads — Prise de contact</option>
                <option value="Éduquer et informer le lecteur pour le rassurer et construire la confiance">📚 Éduquer & informer — Confiance</option>
                <option value="Améliorer le référencement naturel Google sur une requête locale">🔍 SEO local — Positionnement Google</option>
                <option value="Démontrer l'expertise locale du conseiller immobilier">🏆 Démontrer l'expertise locale</option>
                <option value="Accompagner la décision d'achat ou de vente en levant les objections">⚖️ Lever les objections — Aide décision</option>
                <option value="Présenter les opportunités d'investissement locatif dans la zone">💰 Investissement locatif — Opportunités</option>
            </select>
        </div>

        <!-- Ton + Type -->
        <div class="ae5-gen-row">
            <div class="ae5-gen-field" style="margin-bottom:0">
                <label class="ae5-gen-label"><i class="fas fa-palette"></i> Ton éditorial</label>
                <select id="genTone" class="ae5-gen-select">
                    <option value="professionnel et rassurant">Professionnel & rassurant</option>
                    <option value="pédagogique et bienveillant">Pédagogique & bienveillant</option>
                    <option value="enthousiaste et dynamique">Enthousiaste & dynamique</option>
                    <option value="expert et factuel">Expert & factuel</option>
                    <option value="chaleureux et local">Chaleureux & local</option>
                </select>
            </div>
            <div class="ae5-gen-field" style="margin-bottom:0">
                <label class="ae5-gen-label"><i class="fas fa-file-alt"></i> Format</label>
                <select id="genType" class="ae5-gen-select">
                    <option value="guide complet étape par étape">Guide étape par étape</option>
                    <option value="article de conseils pratiques">Conseils pratiques</option>
                    <option value="analyse de marché local">Analyse de marché</option>
                    <option value="article actualité immobilière">Actualité immobilière</option>
                    <option value="FAQ complète sur le sujet">FAQ complète</option>
                </select>
            </div>
        </div>

    </div><!-- /body -->

    <!-- Pied de popup -->
    <div class="ae5-gen-footer">
        <span class="ae5-gen-footer-hint"><i class="fas fa-clock"></i> Génération : 30 à 60 secondes</span>
        <button type="button" class="ae5-gen-submit" id="genSubmitBtn" onclick="submitGeneratePopup()">
            <i class="fas fa-magic"></i> Générer l'article
        </button>
    </div>

</div><!-- /box -->
</div><!-- /modal -->

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function(){
'use strict';

const AI_ENDPOINT = '<?= $AI_ENDPOINT ?>';
const CSRF        = '<?= $csrfToken ?>';
const ARTICLE_ID  = <?= $id ?: 0 ?>;
const IS_EDIT     = <?= $isEdit ? 'true' : 'false' ?>;

const FIELD_MAP = {
    slug              : 'ae5Slug',
    extrait           : 'ae5Extrait',
    seo_title         : 'ae5SeoTitle',
    seo_description   : 'ae5SeoDesc',
    meta_title        : 'ae5MetaTitle',
    meta_description  : 'ae5MetaDesc',
    focus_keyword     : 'ae5FocusKw',
    secondary_keywords: 'ae5SecKw',
};

// ═══ QUILL ═══════════════════════════════════════════════
const quill = new Quill('#ae5QuillEditor', {
    theme: 'snow',
    placeholder: 'Rédigez votre article ici… ou utilisez les outils IA.',
    modules: {
        toolbar: [
            [{ header: [1,2,3,4,false] }],
            ['bold','italic','underline','strike'],
            [{ list:'ordered' },{ list:'bullet' }],
            ['blockquote','code-block'],
            ['link','image'],
            [{ color:[] },{ background:[] }],
            [{ align:[] }],
            ['clean'],
        ]
    }
});

quill.on('text-change', () => { updateWordStats(); calcSeoScore(); });
quill.root.addEventListener('focus', () => g('ae5QuillWrap').classList.add('focused'));
quill.root.addEventListener('blur',  () => g('ae5QuillWrap').classList.remove('focused'));

function updateWordStats() {
    const text  = quill.getText().trim();
    const words = text ? text.split(/\s+/).length : 0;
    const chars = text.length;
    const mins  = Math.max(1, Math.ceil(words / 200));
    st('ae5Words',    words.toLocaleString('fr-FR'));
    st('ae5Chars',    chars.toLocaleString('fr-FR'));
    st('ae5ReadTime', mins + ' min');
    // Sidebar stats live
    st('ae5StatWords', words > 0 ? words.toLocaleString('fr-FR') : '—');
    st('ae5StatRead',  mins + 'm');
    return words;
}
updateWordStats();

// ═══ SLUG ════════════════════════════════════════════════
const titreEl  = g('ae5Titre');
const slugEl   = g('ae5Slug');
const slugPrev = g('ae5SlugPreview');
let slugManual = <?= !empty($article['slug']) ? 'true' : 'false' ?>;

titreEl?.addEventListener('input', function(){
    if (!slugManual) { const s = slugify(this.value); if(slugEl)slugEl.value=s; if(slugPrev)slugPrev.textContent=s||'…'; }
    updateSerp();
});
slugEl?.addEventListener('input', function(){
    slugManual = !!this.value;
    if(slugPrev)slugPrev.textContent = this.value||'…';
    updateSerp();
});
window.genSlugLocal = function(){
    const s = slugify(titreEl?.value||'');
    if(slugEl)slugEl.value=s; if(slugPrev)slugPrev.textContent=s||'…';
    slugManual=false;
};

window.slugify = function(text){
    const stops=['le','la','les','de','du','des','un','une','en','et','ou','a','au','aux',
                 'ce','cette','ces','son','sa','ses','mon','ma','mes','pour','par','sur',
                 'avec','dans','qui','que','dont','est','sont','peut','faire','plus','moins',
                 'tout','tous','ne','pas','se','si','nous','vous','ils','elles','leur'];
    return text.toLowerCase()
        .replace(/[àáâãäå]/g,'a').replace(/[èéêë]/g,'e').replace(/[ìíîï]/g,'i')
        .replace(/[òóôõö]/g,'o').replace(/[ùúûü]/g,'u').replace(/[ç]/g,'c')
        .replace(/[æ]/g,'ae').replace(/[œ]/g,'oe').replace(/[^a-z0-9\s-]/g,'')
        .split(/\s+/).filter(w=>w&&!stops.includes(w)).slice(0,6).join('-');
};

// ═══ SERP PREVIEW (double : onglet + sidebar) ════════════
function updateSerp(){
    const seoTitle = val('ae5SeoTitle') || val('ae5MetaTitle') || val('ae5Titre') || 'Titre de votre article';
    const seoDesc  = val('ae5SeoDesc')  || val('ae5MetaDesc')  || val('ae5Extrait') || 'Votre description…';
    const slug     = val('ae5Slug') || 'votre-article';

    // SERP dans l'onglet SEO
    st('ae5SerpTitle', seoTitle); st('ae5SerpSlug', slug); st('ae5SerpDesc', seoDesc);

    // SERP miniature dans la sidebar
    st('ae5SerpSideTitle', seoTitle);
    st('ae5SerpSideSlug',  slug);
    st('ae5SerpSideDesc',  seoDesc);

    // Indicateurs de longueur
    const tl = seoTitle.length;
    const dl = seoDesc.length;
    const kw = (val('ae5FocusKw')||'').toLowerCase();
    const inTitle = kw && seoTitle.toLowerCase().includes(kw);
    const inDesc  = kw && seoDesc.toLowerCase().includes(kw);

    const tlEl = g('ae5SerpTitleLen'); if(tlEl) tlEl.textContent = tl;
    const dlEl = g('ae5SerpDescLen');  if(dlEl) dlEl.textContent = dl;

    const indTitle = g('ae5SerpIndTitle');
    if(indTitle) indTitle.className = 'ae5-serp-ind ' + (tl>=50&&tl<=65?'ok':tl>0?'warn':'err');

    const indDesc = g('ae5SerpIndDesc');
    if(indDesc) indDesc.className = 'ae5-serp-ind ' + (dl>=140&&dl<=160?'ok':dl>0?'warn':'err');

    const indKw = g('ae5SerpIndKw');
    if(indKw) {
        indKw.className = 'ae5-serp-ind ' + (inTitle||inDesc ? 'ok' : kw?'warn':'def');
        indKw.innerHTML = '<i class="fas fa-key"></i> KW ' + (inTitle||inDesc ? '✅' : kw?'❌':'—');
    }
}

['ae5SeoTitle','ae5SeoDesc','ae5MetaTitle','ae5MetaDesc','ae5Extrait','ae5FocusKw'].forEach(id => {
    g(id)?.addEventListener('input', () => { updateSerp(); calcSeoScore(); });
});
updateSerp();

// ═══ SEO TABS ════════════════════════════════════════════
window.seoTab = function(name, btn){
    document.querySelectorAll('.ae5-seo-panel').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.ae5-seo-tab').forEach(b=>b.classList.remove('active'));
    g('ae5SeoTab-'+name)?.classList.add('active');
    btn.classList.add('active');
};

// ═══ COMPTEURS CARACTÈRES ════════════════════════════════
function setupCounter(inputId, counterId, maxLen, goodMin, goodMax){
    const input=g(inputId), counter=g(counterId);
    if(!input||!counter) return;
    const update=()=>{
        const n=input.value.length;
        counter.textContent=n+'/'+maxLen;
        counter.className='ae5-char-count'+(n>=goodMin&&n<=goodMax?' ok':n>maxLen?' err':n>0?' warn':'');
    };
    input.addEventListener('input', update); update();
}
setupCounter('ae5Extrait',  'ae5ExtraitCount',  280,100,250);
setupCounter('ae5SeoTitle', 'ae5SeoTitleCount',  70, 50, 60);
setupCounter('ae5SeoDesc',  'ae5SeoDescCount',  160,140,155);
setupCounter('ae5MetaTitle','ae5MetaTitleCount',  70, 50, 60);
setupCounter('ae5MetaDesc', 'ae5MetaDescCount',  160,140,155);

// ═══ SCORE SEO LIVE ══════════════════════════════════════
window.calcSeoScore = function(){
    const ico = g('ae5CalcIco');
    if(ico) ico.className = 'fas fa-spinner ae5-spin';

    const title   = val('ae5Titre') || val('ae5SeoTitle') || '';
    const metaT   = val('ae5SeoTitle') || '';
    const metaD   = val('ae5SeoDesc')  || '';
    const kw      = (val('ae5FocusKw')||'').toLowerCase();
    const content = quill.root.innerHTML.replace(/<[^>]+>/g,' ').toLowerCase();
    const wc      = updateWordStats();

    // Titre
    let sT = 0;
    if(title.length>=30&&title.length<=70) sT=100; else if(title.length) sT=50;
    if(kw&&title.toLowerCase().includes(kw)) sT=Math.min(100,sT+20);

    // Méta
    let sM=0;
    if(metaT.length>=50&&metaT.length<=65) sM=100; else if(metaT.length) sM=60;
    const ds = metaD.length>=140&&metaD.length<=160?100:metaD.length?60:0;
    sM=Math.round((sM+ds)/2);

    // Contenu
    let sC = wc>=1200?100:wc>=800?80:wc>=500?60:wc>=300?40:wc>0?20:0;

    // Mot-clé
    let sK=0;
    if(kw&&content.includes(kw)){
        const cnt=(content.match(new RegExp(kw.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'g'))||[]).length;
        sK=cnt>=3?100:cnt>=1?60:30;
    }

    const global = Math.round((sT+sM+sC+sK)/4);

    // Couleur selon score
    function scoreClass(s){ return s>=80?'excellent':s>=60?'good':s>=40?'ok':s>0?'bad':'none'; }
    function scoreColor(cls){
        return {excellent:'#10b981',good:'#3b82f6',ok:'#f59e0b',bad:'#ef4444',none:'var(--bdr)'}[cls];
    }
    function scoreHint(s,cls){
        if(cls==='excellent') return 'Excellent !';
        if(cls==='good')      return 'Bon score';
        if(cls==='ok')        return 'À améliorer';
        if(cls==='bad')       return 'Insuffisant';
        return 'Non calculé';
    }

    // Mise à jour barres
    [['titre',sT],['meta',sM],['content',sC],['kw',sK]].forEach(([id,score])=>{
        const cls  = scoreClass(score);
        const fill = g('ae5SBar-'+id+'-fill');
        const valEl= g('ae5SBar-'+id+'-val');
        if(fill){ fill.style.width=score+'%'; fill.className='ae5-score-bar-prog '+cls; }
        if(valEl){ valEl.textContent=score; valEl.className='ae5-score-row-val '+cls; }
    });

    // Cercle SEO
    const globalCls = scoreClass(global);
    const seoCircle = g('ae5ScoreCircleSeo');
    if(seoCircle){ seoCircle.textContent=global; seoCircle.className='ae5-big-score-ring '+globalCls; }
    const seoHint = g('ae5SeoHint');
    if(seoHint) seoHint.textContent = scoreHint(global,globalCls);
    const statSeo = g('ae5StatSeoNum');
    if(statSeo) statSeo.textContent = global;

    // Label contenu
    const csl = g('ae5ContentScoreLabel');
    if(csl){ csl.textContent='SEO: '+global+'/100'; csl.style.color=scoreColor(globalCls); }

    if(ico) setTimeout(()=>{ ico.className='fas fa-sync-alt'; }, 400);
};

// Bind champs SEO au calcul
['ae5Titre','ae5SeoTitle','ae5SeoDesc','ae5FocusKw'].forEach(id=>g(id)?.addEventListener('input',calcSeoScore));
calcSeoScore();

// ═══ SAVE ════════════════════════════════════════════════
window.saveArticle = function(status){
    g('ae5Status').value  = status;
    g('ae5Contenu').value = quill.root.innerHTML;
    const r = g('ae5St'+(status==='published'?'Published':'Draft'));
    if(r) r.checked=true;
    g('ae5Form').submit();
};
document.querySelectorAll('input[name="status_radio"]').forEach(r=>{
    r.addEventListener('change',function(){ g('ae5Status').value=this.value; });
});
document.addEventListener('keydown', e=>{
    if((e.ctrlKey||e.metaKey)&&e.key==='s'){
        e.preventDefault();
        g('ae5Contenu').value=quill.root.innerHTML;
        g('ae5Form').submit();
    }
});

// ═══ SUPPRIMER ═══════════════════════════════════════════
window.delArticle = function(){
    const title = <?= json_encode((string)($article['titre'] ?? 'cet article')) ?>;
    if(!confirm('⚠️ Supprimer :\n\n"'+title+'"\n\nCette action est définitive.')) return;
    if(!confirm('Dernière confirmation — supprimer définitivement ?')) return;
    window.location.href='?page=articles&action=delete&id=<?= $id ?>&csrf_token=<?= $csrfToken ?>';
};

// ═══ IMAGE ═══════════════════════════════════════════════
window.uploadImg = function(input){
    const file=input.files[0]; if(!file) return;
    if(file.size>5*1024*1024){ toast('Image trop lourde (max 5 Mo)','error'); return; }
    const fd=new FormData(); fd.append('image',file); fd.append('type','article');
    fetch('/admin/api/system/upload.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(d=>d.success&&d.url?setImg(d.url):fallbackImg(file)).catch(()=>fallbackImg(file));
};
function fallbackImg(file){ const r=new FileReader(); r.onload=e=>setImg(e.target.result); r.readAsDataURL(file); }
function setImg(url){
    const zone=g('ae5ImgZone'), prev=g('ae5ImgPreview'), ph=g('ae5ImgPlaceholder');
    if(zone)zone.classList.add('has-img');
    if(prev){prev.src=url; prev.style.display='block';}
    if(ph) ph.style.display='none';
    let rm=zone?.querySelector('.ae5-img-remove');
    if(!rm&&zone){ rm=document.createElement('button'); rm.type='button'; rm.className='ae5-img-remove'; rm.innerHTML='<i class="fas fa-times"></i>'; rm.onclick=e=>{e.stopPropagation();removeImg();}; zone.appendChild(rm); }
    g('ae5FeaturedImg').value=url;
    toast('Image chargée','success');
}
window.removeImg=function(){
    const zone=g('ae5ImgZone'),prev=g('ae5ImgPreview'),ph=g('ae5ImgPlaceholder');
    if(zone)zone.classList.remove('has-img');
    if(prev){prev.src='';prev.style.display='none';}
    if(ph)ph.style.display='';
    zone?.querySelector('.ae5-img-remove')?.remove();
    g('ae5FeaturedImg').value=''; g('ae5ImgFile').value='';
};

// ═══ TOAST ═══════════════════════════════════════════════
let _tt;
window.toast=function(msg,type='success',dur=3500){
    const el=g('ae5Toast'),ico=g('ae5ToastIco'),txt=g('ae5ToastMsg');
    const icons={success:'fa-check-circle',error:'fa-exclamation-circle',ai:'fa-robot',warn:'fa-exclamation-triangle'};
    if(ico)ico.className='fas '+(icons[type]||'fa-info-circle');
    if(txt)txt.textContent=msg;
    if(el){el.className='ae5-toast show '+type; clearTimeout(_tt); _tt=setTimeout(()=>el.classList.remove('show'),dur);}
};

// ═══ MODALE IA ═══════════════════════════════════════════
let _modalApply=null;
function openModal(title,loaderMsg='Génération en cours…'){
    st('ae5ModalTitle',title); st('ae5ModalLoaderTxt',loaderMsg);
    show('ae5ModalLoader'); hide('ae5ModalResult');
    g('ae5ModalApply').style.display='none';
    g('ae5Modal').classList.add('open'); _modalApply=null;
}
window.closeModal=function(){ g('ae5Modal').classList.remove('open'); };
function showModalResult(text,applyFn=null){
    hide('ae5ModalLoader'); show('ae5ModalResult');
    st('ae5ModalResultTxt', typeof text==='object'?JSON.stringify(text,null,2):text);
    _modalApply=applyFn;
    g('ae5ModalApply').style.display=applyFn?'inline-flex':'none';
}
window.applyModal=function(){ if(_modalApply)_modalApply(); closeModal(); };
document.addEventListener('keydown',e=>{ if(e.key==='Escape')closeModal(); });
g('ae5Modal')?.addEventListener('click',e=>{ if(e.target===g('ae5Modal'))closeModal(); });

// ═══ APPEL IA CENTRAL ════════════════════════════════════
async function callAI(module,action,params={}){
    const r=await fetch(AI_ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({module,action,csrf_token:CSRF,...params})});
    if(!r.ok) throw new Error('HTTP '+r.status);
    const d=await r.json();
    if(!d.success) throw new Error(d.error||'Erreur IA');
    return d;
}

// ═══ IA BOUTONS INLINE ═══════════════════════════════════
window.aiField=async function(field){
    const btn=event?.currentTarget, ico=btn?.querySelector('i'), orig=ico?.className;
    if(ico)ico.className='fas fa-spinner ae5-spin';
    if(btn)btn.disabled=true;
    try{
        let result='';
        const titre=val('ae5Titre')||'', focusKw=val('ae5FocusKw')||'', content=quill.root.innerHTML.substring(0,1500);
        if(field==='extrait'||['seo_title','meta_title'].includes(field)||['seo_description','meta_description'].includes(field)||field==='slug'){
            const d=await callAI('articles','meta',{title:titre,keyword:focusKw,content});
            if(field==='extrait')                                      result=d.meta_description||d.data?.meta_description||'';
            else if(['seo_title','meta_title'].includes(field))        result=d.meta_title||d.data?.meta_title||'';
            else if(['seo_description','meta_description'].includes(field)) result=d.meta_description||d.data?.meta_description||'';
            else if(field==='slug')                                    result=d.slug||d.data?.slug||slugify(titre);
        } else if(field==='focus_keyword'){
            const d=await callAI('articles','keywords',{content:quill.getText().substring(0,2000),subject:titre});
            result=d.keywords?.primary_keyword||'';
        } else if(field==='secondary_keywords'){
            const d=await callAI('articles','keywords',{content:quill.getText().substring(0,2000),subject:titre});
            const kws=d.keywords?.secondary_keywords?.slice(0,5)||[];
            result=kws.map(k=>k.keyword||k).join(', ');
        }
        result=result.trim().replace(/^["'`]+|["'`]+$/g,'');
        const tid=FIELD_MAP[field];
        if(tid&&result){ const el=g(tid); if(el){el.value=result; el.dispatchEvent(new Event('input'));} }
        if(field==='slug'){ if(slugPrev)slugPrev.textContent=result||'…'; slugManual=true; }
        updateSerp(); calcSeoScore();
        toast('✨ '+field+' généré','ai');
    }catch(err){ toast('❌ '+err.message,'error'); }
    if(ico)ico.className=orig||'fas fa-robot';
    if(btn)btn.disabled=false;
};

// ═══ IA GÉNERER COMPLET ══════════════════════════════════
// ═══ POPUP GÉNÉRATION ════════════════════════════════════════
window.openGeneratePopup = function() {
    // Pré-remplir depuis les champs existants
    const kw = val('ae5FocusKw') || '';
    if (kw) g('genKeyword') && (g('genKeyword').value = kw);
    const subj = val('ae5Titre') || '';
    if (subj) g('genSubject') && (g('genSubject').value = subj);
    g('ae5GenModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => g('genSubject')?.focus(), 100);
};

window.closeGeneratePopup = function() {
    g('ae5GenModal').classList.remove('open');
    document.body.style.overflow = '';
};

window.selectChip = function(el) {
    document.querySelectorAll('.ae5-gen-chip').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
};

window.genSyncTitle = function() {
    // Optionnel : synchro visuelle en temps réel
};

window.submitGeneratePopup = async function() {
    const subject  = g('genSubject')?.value.trim()  || '';
    const persona  = g('genPersona')?.value.trim()  || '';
    const keyword  = g('genKeyword')?.value.trim()  || '';
    const objectif = g('genObjectif')?.value.trim() || '';
    const ville    = g('genVille')?.value.trim()    || '';
    const tone     = g('genType')?.value            || 'professionnel et rassurant';
    const type     = g('genType')?.value            || 'guide complet étape par étape';
    const words    = parseInt(document.querySelector('.ae5-gen-chip.selected')?.dataset?.words || '1200');

    // Validation
    if (!subject)  { toast('⚠️ Saisissez un sujet pour l\'article', 'warn'); g('genSubject')?.focus(); return; }
    if (!persona)  { toast('⚠️ Choisissez un persona cible', 'warn'); g('genPersona')?.focus(); return; }
    if (!keyword)  { toast('⚠️ Saisissez un mot-clé principal', 'warn'); g('genKeyword')?.focus(); return; }
    if (!objectif) { toast('⚠️ Choisissez un objectif', 'warn'); g('genObjectif')?.focus(); return; }

    // Désactiver le bouton
    const btn = g('genSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"><i class="fas fa-spinner"></i></span> Génération en cours…';

    closeGeneratePopup();
    openModal('✨ Génération article complet', `Rédaction pour persona "${persona.split(',')[0]}"… (30-60 sec)`);

    try {
        const d = await callAI('articles', 'generate', {
            subject, keywords: keyword, word_count: words,
            tone, type, persona, objectif, ville
        });
        const art = d.article || d;

        showModalResult(
            `✅ Article généré !\n\nTitre : ${art.title || '—'}\nSlug  : ${art.slug || '—'}\nMots-clés : ${art.primary_keyword || keyword}\n\nCliquez "Appliquer" pour insérer dans l'éditeur.`,
            () => {
                if (art.title)            { sv('ae5Titre', art.title); }
                if (art.slug)             { sv('ae5Slug', art.slug); slugManual = true; if(slugPrev) slugPrev.textContent = art.slug; }
                if (art.content)          { quill.clipboard.dangerouslyPasteHTML(art.content); g('ae5Contenu').value = art.content; }
                if (art.meta_title)       { sv('ae5SeoTitle', art.meta_title); sv('ae5MetaTitle', art.meta_title); }
                if (art.meta_description) { sv('ae5SeoDesc', art.meta_description); sv('ae5MetaDesc', art.meta_description); }
                if (art.excerpt)          { sv('ae5Extrait', art.excerpt); }
                if (art.primary_keyword)  { sv('ae5FocusKw', art.primary_keyword); }
                if (art.secondary_keywords?.length) { sv('ae5SecKw', art.secondary_keywords.join(', ')); }
                ['ae5Titre','ae5SeoTitle','ae5SeoDesc','ae5Extrait','ae5FocusKw'].forEach(id => g(id)?.dispatchEvent(new Event('input')));
                updateSerp(); calcSeoScore();
                toast('🎉 Article complet appliqué !', 'ai', 5000);
            }
        );
    } catch(err) {
        closeModal();
        toast('❌ ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> Générer l\'article';
    }
};

// Garde la compatibilité avec les anciens boutons onclick="aiGenerate()"
window.aiGenerate = function() { openGeneratePopup(); };

// ═══ IA AMÉLIORER ════════════════════════════════════════
window.aiImprove=async function(){
    const content=quill.root.innerHTML;
    if(!content||content==='<p><br></p>'){ toast('Aucun contenu à améliorer','warn'); return; }
    openModal('⚡ Amélioration du contenu','Analyse et réécriture…');
    try{
        const d=await callAI('articles','improve',{content,title:val('ae5Titre')||'',objectives:'SEO, lisibilité, engagement'});
        const improved=d.data?.improved_content||d.improved_content||'';
        const changes=d.data?.changes_summary||[];
        showModalResult('✅ Contenu amélioré !\n\nModifications :\n'+changes.map(c=>'• '+c).join('\n'),()=>{
            if(improved){ quill.clipboard.dangerouslyPasteHTML(improved); g('ae5Contenu').value=improved; calcSeoScore(); }
            toast('Contenu amélioré !','ai');
        });
    }catch(err){ closeModal(); toast('❌ '+err.message,'error'); }
};

// ═══ IA MÉTAS ════════════════════════════════════════════
window.aiMeta=async function(){
    openModal('🔍 Génération métas SEO','Optimisation en cours…');
    try{
        const d=await callAI('articles','meta',{title:val('ae5Titre')||'',keyword:val('ae5FocusKw')||'',content:quill.root.innerHTML.substring(0,1200)});
        const meta=d.meta_title?d:(d.data||d);
        showModalResult('✅ Métas générées !\n\nMeta title : '+(meta.meta_title||'—')+'\nMeta desc  : '+(meta.meta_description||'—')+'\nSlug       : '+(meta.slug||'—'),()=>{
            if(meta.meta_title)       { sv('ae5SeoTitle',meta.meta_title); sv('ae5MetaTitle',meta.meta_title); g('ae5SeoTitle')?.dispatchEvent(new Event('input')); }
            if(meta.meta_description) { sv('ae5SeoDesc',meta.meta_description); sv('ae5MetaDesc',meta.meta_description); g('ae5SeoDesc')?.dispatchEvent(new Event('input')); }
            if(meta.slug&&!slugManual){ sv('ae5Slug',meta.slug); if(slugPrev)slugPrev.textContent=meta.slug; slugManual=true; }
            updateSerp(); calcSeoScore();
            toast('Métas SEO appliquées !','ai');
        });
    }catch(err){ closeModal(); toast('❌ '+err.message,'error'); }
};

// ═══ IA FAQ ══════════════════════════════════════════════
window.aiFaq=async function(){
    openModal('❓ FAQ Schema.org','Génération questions/réponses…');
    try{
        const d=await callAI('articles','faq',{title:val('ae5Titre')||'',content:quill.root.innerHTML.substring(0,2500),count:5});
        const faq=Array.isArray(d.faq)?d.faq:[];
        const preview=faq.map((f,i)=>`Q${i+1}: ${f.question}\nR: ${f.answer}`).join('\n\n');
        showModalResult(preview||'Aucune FAQ',()=>{
            if(!faq.length) return;
            const schema=JSON.stringify({'@context':'https://schema.org','@type':'FAQPage',mainEntity:faq.map(f=>({'@type':'Question',name:f.question,acceptedAnswer:{'@type':'Answer',text:f.answer}}))},null,2);
            g('ae5Contenu').value=(quill.root.innerHTML)+'\n<script type="application/ld+json">'+schema+'<\/script>';
            toast('FAQ Schema.org insérée !','ai');
        });
    }catch(err){ closeModal(); toast('❌ '+err.message,'error'); }
};

// ═══ IA OUTLINE ══════════════════════════════════════════
window.aiOutline=async function(){
    const subject=val('ae5AiSubject')||val('ae5Titre')||'';
    if(!subject?.trim()){ toast('Saisissez un sujet','warn'); return; }
    openModal('📋 Plan éditorial','Construction du plan…');
    try{
        const d=await callAI('articles','outline',{subject,keyword:val('ae5FocusKw')||''});
        const outline=d.outline?.outline||d.outline||[], titles=d.outline?.title_suggestions||[];
        let preview='';
        if(titles.length) preview+='💡 Titres suggérés :\n'+titles.map((t,i)=>`${i+1}. ${t}`).join('\n')+'\n\n';
        preview+='📋 Plan :\n';
        outline.forEach(item=>{ preview+=`[${(item.level||'H2').toUpperCase()}] ${item.title||item} (~${item.estimated_words||'?'} mots)\n`; if(item.description) preview+=`    → ${item.description}\n`; });
        showModalResult(preview||'Aucun plan',()=>{
            if(titles[0]&&!val('ae5Titre')){ sv('ae5Titre',titles[0]); g('ae5Titre')?.dispatchEvent(new Event('input')); }
            toast('Plan prêt !','ai');
        });
    }catch(err){ closeModal(); toast('❌ '+err.message,'error'); }
};

// ═══ IA KEYWORDS ═════════════════════════════════════════
window.aiKeywords=async function(){
    openModal('🏷 Extraction mots-clés','Analyse sémantique…');
    try{
        const d=await callAI('articles','keywords',{content:quill.getText().substring(0,2500),subject:val('ae5Titre')||''});
        const kw=d.keywords||d;
        let preview='';
        if(kw.primary_keyword) preview+='🎯 Principal : '+kw.primary_keyword+'\n\n';
        if(kw.secondary_keywords?.length) preview+='📌 Secondaires :\n'+kw.secondary_keywords.map(k=>'  → '+(k.keyword||k)).join('\n')+'\n\n';
        if(kw.long_tail_keywords?.length) preview+='🔍 Longue traîne :\n'+kw.long_tail_keywords.map(k=>'  → '+k).join('\n')+'\n\n';
        if(kw.local_keywords?.length) preview+='📍 Local :\n'+kw.local_keywords.map(k=>'  → '+k).join('\n');
        showModalResult(preview||'Aucun mot-clé',()=>{
            if(kw.primary_keyword){ sv('ae5FocusKw',kw.primary_keyword); g('ae5FocusKw')?.dispatchEvent(new Event('input')); }
            if(kw.secondary_keywords?.length){ const sec=kw.secondary_keywords.slice(0,5).map(k=>k.keyword||k).join(', '); if(!val('ae5SecKw'))sv('ae5SecKw',sec); }
            calcSeoScore(); toast('Mots-clés appliqués !','ai');
        });
    }catch(err){ closeModal(); toast('❌ '+err.message,'error'); }
};

// ═══ IA REWRITE ══════════════════════════════════════════
window.aiRewrite=async function(){
    const content=quill.root.innerHTML;
    if(!content||content==='<p><br></p>'){ toast('Aucun contenu à réécrire','warn'); return; }
    const angle=prompt('Angle de réécriture :\n(ex: investisseur, primo-accédant, vendeur pressé…)','primo-accédant');
    if(!angle) return;
    openModal('🔄 Réécriture','Réécriture avec le nouvel angle…');
    try{
        const d=await callAI('articles','rewrite',{content,angle});
        const rewritten=d.rewritten_content||'';
        showModalResult('✅ Contenu réécrit avec l\'angle : "'+angle+'"',()=>{
            if(rewritten){ quill.clipboard.dangerouslyPasteHTML(rewritten); g('ae5Contenu').value=rewritten; calcSeoScore(); }
            toast('Contenu réécrit !','ai');
        });
    }catch(err){ closeModal(); toast('❌ '+err.message,'error'); }
};

// ═══ HELPERS ═════════════════════════════════════════════
function g(id)    { return document.getElementById(id); }
function val(id)  { const e=g(id); return e?.tagName==='TEXTAREA'?e.value:(e?.value||''); }
function sv(id,v) { const e=g(id); if(e) e.value=v; }
function st(id,t) { const e=g(id); if(e) e.textContent=t; }
function show(id) { const e=g(id); if(e) e.style.display=''; }
function hide(id) { const e=g(id); if(e) e.style.display='none'; }

// ═══ GMB — POSTS GOOGLE MY BUSINESS ════════════════════
const GMB_API = '/admin/api/router.php?module=gmb-posts';

window.gmbGenerate = async function(){
    const btn = g('ae5GmbGenerateBtn'), ico = g('ae5GmbGenIco');
    if(!ARTICLE_ID){ toast('Enregistrez l\'article avant de générer un post GMB','warn'); return; }
    if(btn) btn.disabled = true;
    if(ico) ico.className = 'fas fa-spinner ae5-spin';
    try {
        const r = await fetch(GMB_API + '&action=generate', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({ article_id: ARTICLE_ID, csrf_token: CSRF })
        });
        const d = await r.json();
        if(d.success){
            toast('Post GMB généré !' + (d.published ? ' (publié sur Google)' : ' (brouillon)'), 'ai', 4000);
            // Recharger le panneau
            setTimeout(() => location.reload(), 800);
        } else {
            toast('Erreur : ' + (d.error || d.message || 'Erreur inconnue'), 'error');
        }
    } catch(err) { toast('Erreur réseau : ' + err.message, 'error'); }
    if(btn) btn.disabled = false;
    if(ico) ico.className = 'fas fa-magic';
};

window.gmbRepublish = async function(postId){
    if(!confirm('Republier ce post sur Google My Business ?')) return;
    try {
        const r = await fetch(GMB_API + '&action=republish', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({ post_id: postId, csrf_token: CSRF })
        });
        const d = await r.json();
        if(d.published) {
            toast('Post publié sur Google !', 'success');
        } else {
            toast('Publication échouée : ' + (d.error || 'Clé API GMB non configurée'), 'warn', 5000);
        }
        setTimeout(() => location.reload(), 800);
    } catch(err) { toast('Erreur : ' + err.message, 'error'); }
};

window.gmbDelete = async function(postId){
    if(!confirm('Supprimer ce post GMB ?')) return;
    try {
        const r = await fetch(GMB_API + '&action=delete', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({ post_id: postId, csrf_token: CSRF })
        });
        const d = await r.json();
        if(d.success){
            toast('Post GMB supprimé', 'success');
            document.querySelector(`.ae5-gmb-post-item[data-post-id="${postId}"]`)?.remove();
        } else {
            toast('Erreur : ' + (d.message || 'Échec suppression'), 'error');
        }
    } catch(err) { toast('Erreur : ' + err.message, 'error'); }
};

// Init
g('ae5Form')?.addEventListener('submit',()=>{ g('ae5Contenu').value=quill.root.innerHTML; });
console.log('📝 EcosystèmeImmo — Article Editor v5.1 | '+(IS_EDIT?'Edit #'+ARTICLE_ID:'Nouveau'));
})();
</script>