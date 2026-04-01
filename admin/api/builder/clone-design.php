<?php
/**
 * BUILDER PRO — clone-design.php v3
 * /admin/api/builder/clone-design.php
 *
 * GET  ?action=test          → diagnostic API
 * GET  ?action=list          → liste toutes les pages internes par type
 * POST {url, mode, context}  → clonage URL externe
 * POST {internal_id, internal_type, mode, context} → clonage interne depuis DB
 */

define('ADMIN_ROUTER', true);
require_once dirname(__DIR__, 2) . '/includes/init.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json; charset=utf-8');

// ── AUTH ──────────────────────────────────────────────────────────────────────
$_isAuth = !empty($_SESSION['auth_admin_logged_in']) || !empty($_SESSION['auth_user_id'])
        || !empty($_SESSION['auth_admin_id'])         || !empty($_SESSION['auth_logged_in'])
        || !empty($_SESSION['auth_is_admin']);
if (!$_isAuth) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Non autorisé']); exit;
}

// ── CLÉ ANTHROPIC ─────────────────────────────────────────────────────────────
function getAnthropicKey(PDO $pdo): string {
    $key = '';
    if (!$key && defined('ANTHROPIC_API_KEY') && ANTHROPIC_API_KEY) $key = ANTHROPIC_API_KEY;
    if (!$key) {
        try {
            $names = ['anthropic','claude','anthropic_api_key','ANTHROPIC_API_KEY','claude_api'];
            $ph = implode(',', array_fill(0, count($names), '?'));
            $st = $pdo->prepare("SELECT api_key_encrypted FROM api_keys WHERE service_key IN ($ph) AND is_active=1 ORDER BY id DESC LIMIT 1");
            $st->execute($names);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['api_key_encrypted'])) {
                $val = trim($row['api_key_encrypted']);
                if (defined('API_ENCRYPT_KEY') && !str_starts_with($val,'sk-') && base64_decode($val,true)) {
                    $iv  = substr(hash('sha256', API_ENCRYPT_KEY, true), 0, 16);
                    $dec = openssl_decrypt(base64_decode($val),'AES-256-CBC',API_ENCRYPT_KEY,OPENSSL_RAW_DATA,$iv);
                    $key = ($dec && str_starts_with($dec,'sk-ant-')) ? $dec : $val;
                } else { $key = $val; }
            }
        } catch (Exception $e) {}
    }
    if (!$key) {
        try {
            $names = ['anthropic_api_key','claude_api_key','anthropic_key','api_anthropic'];
            $ph = implode(',', array_fill(0, count($names), '?'));
            $st = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key IN ($ph) LIMIT 1");
            $st->execute($names);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['setting_value'])) $key = trim($row['setting_value']);
        } catch (Exception $e) {}
    }
    if (!$key && getenv('ANTHROPIC_API_KEY')) $key = getenv('ANTHROPIC_API_KEY');
    return $key;
}

// ── APPEL CLAUDE API ──────────────────────────────────────────────────────────
function callClaude(string $key, string $system, string $user): array {
    $payload = json_encode([
        'model'      => 'claude-sonnet-4-20250514',
        'max_tokens' => 8000,
        'system'     => $system,
        'messages'   => [['role'=>'user','content'=> mb_substr($user, 0, 55000)]],
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: '.$key,
            'anthropic-version: 2023-06-01',
        ],
    ]);
    $resp    = curl_exec($ch);
    $err     = curl_error($ch);
    $http    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err)       return ['ok'=>false,'error'=>'cURL: '.$err];
    $data = json_decode($resp, true);
    if ($http !== 200) return ['ok'=>false,'error'=>'API '.$http.': '.($data['error']['message']??'')];
    $raw = trim($data['content'][0]['text'] ?? '');
    if (!$raw)      return ['ok'=>false,'error'=>'Réponse Claude vide'];
    return ['ok'=>true,'raw'=>$raw];
}

// ── PARSE RÉPONSE JSON CLAUDE ─────────────────────────────────────────────────
function parseClaudeJson(string $raw): array {
    $r = json_decode($raw, true);
    if ($r && json_last_error() === JSON_ERROR_NONE) return $r;
    $s = strpos($raw,'{'); $e = strrpos($raw,'}');
    if ($s!==false && $e!==false && $e>$s) {
        $r = json_decode(substr($raw,$s,$e-$s+1), true);
        if ($r && json_last_error() === JSON_ERROR_NONE) return $r;
    }
    $c = preg_replace('/^```(json)?\s*/i','',trim($raw));
    $c = preg_replace('/\s*```$/i','',$c);
    $r = json_decode(trim($c), true);
    if ($r && json_last_error() === JSON_ERROR_NONE) return $r;
    return ['html'=>$raw,'css'=>'','js'=>'','meta_title'=>'','meta_desc'=>'','slug'=>'','summary'=>'HTML brut récupéré.'];
}

// ── STRUCTURE INTERNE : table → colonnes ──────────────────────────────────────
$INTERNAL_MAP = [
    'page'    => ['table'=>'pages',        'col_id'=>'id','col_title'=>'title',  'col_html'=>'content',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status'],
    'secteur' => ['table'=>'secteurs',     'col_id'=>'id','col_title'=>'nom',    'col_html'=>'content',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status'],
    'article' => ['table'=>'articles',     'col_id'=>'id','col_title'=>'title',  'col_html'=>'content',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status'],
    'guide'   => ['table'=>'guide_local',  'col_id'=>'id','col_title'=>'titre',  'col_html'=>'contenu',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'statut'],
    'header'  => ['table'=>'headers',      'col_id'=>'id','col_title'=>'name',   'col_html'=>'custom_html','col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'name','col_status'=>'status'],
    'footer'  => ['table'=>'footers',      'col_id'=>'id','col_title'=>'name',   'col_html'=>'custom_html','col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'name','col_status'=>'status'],
    'capture' => ['table'=>'captures','col_id'=>'id','col_title'=>'titre',   'col_html'=>'contenu',    'col_css'=>'custom_css','col_js'=>'custom_js','col_slug'=>'slug','col_status'=>'status'],
];

// ── ACTION : LIST (GET ?action=list) ─────────────────────────────────────────
// Retourne toutes les pages internes groupées par type pour le panel Clone
if (($_GET['action'] ?? '') === 'list') {
    $result = [];
    foreach ($INTERNAL_MAP as $type => $M) {
        try {
            $statusCol = $M['col_status'];
            $st = $pdo->query(
                "SELECT {$M['col_id']} as id,
                        {$M['col_title']} as title,
                        {$M['col_slug']} as slug,
                        {$statusCol} as status
                 FROM `{$M['table']}`
                 ORDER BY {$M['col_title']} ASC
                 LIMIT 80"
            );
            $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
            if (!empty($rows)) $result[$type] = $rows;
        } catch (Exception $e) {
            $result[$type] = [];
        }
    }
    echo json_encode(['success'=>true,'pages'=>$result], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── ACTION : TEST (GET ?action=test) ─────────────────────────────────────────
if (($_GET['action'] ?? '') === 'test') {
    $key   = getAnthropicKey($pdo);
    $found = !empty($key);
    $source = $found ? (defined('ANTHROPIC_API_KEY')&&$key===ANTHROPIC_API_KEY ? 'config.php' : 'base de données') : 'non trouvée';
    $pingOk = false; $pingMsg = '';
    if ($found) {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POSTFIELDS=>json_encode(['model'=>'claude-sonnet-4-20250514','max_tokens'=>10,'messages'=>[['role'=>'user','content'=>'ping']]]),
            CURLOPT_HTTPHEADER=>['Content-Type: application/json','x-api-key: '.$key,'anthropic-version: 2023-06-01']]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        $pingOk = ($code===200); $pingMsg = "HTTP $code";
        if (!$pingOk && $resp){$d=json_decode($resp,true);$pingMsg.=' — '.($d['error']['message']??$resp);}
    }
    echo json_encode(['success'=>true,'api_found'=>$found,'api_source'=>$source,
        'api_prefix'=>$found?substr($key,0,15).'...':'','api_valid'=>$pingOk,
        'ping_msg'=>$pingMsg,'php_version'=>PHP_VERSION,'curl_ok'=>function_exists('curl_init')],JSON_UNESCAPED_UNICODE);
    exit;
}

// ── MODE CLONAGE (POST) ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'POST requis']); exit;
}

$rawInput = file_get_contents('php://input');
$input    = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success'=>false,'error'=>'JSON invalide: '.json_last_error_msg()]); exit;
}

$mode        = trim($input['mode']          ?? 'clone');
$context     = trim($input['context']       ?? 'page');
$internalId  = (int)($input['internal_id']  ?? 0);
$internalType= trim($input['internal_type'] ?? '');

// ── IDENTITÉ SITE ─────────────────────────────────────────────────────────────
$siteIdentity = [];
try {
    $rs = $pdo->query("SELECT setting_key,setting_value FROM settings WHERE setting_key IN ('advisor_name','advisor_city','advisor_phone','advisor_email','primary_color','site_name') LIMIT 10");
    if ($rs) foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $r) $siteIdentity[$r['setting_key']] = $r['setting_value'];
} catch (Exception $e) {}

$ctxLabels = ['page'=>'page CMS','secteur'=>'page secteur immobilier','article'=>'article de blog',
    'guide'=>'guide local','header'=>'header navigation','footer'=>'footer','capture'=>'page capture leads'];
$ctxLabel = $ctxLabels[$context] ?? 'page web';

$identity_str = !empty($siteIdentity)
    ? 'Identité du site cible : '.json_encode($siteIdentity, JSON_UNESCAPED_UNICODE)
    : 'Conseiller : Eduardo De Sul, Bordeaux/Blanquefort, eXp France';

$instructions = [
    'clone'   => "Clone fidèlement le design et la structure. Garde couleurs, typographies et mise en page. Adapte le contenu pour un conseiller immobilier à Bordeaux.",
    'adapt'   => "Inspire-toi du design pour créer une $ctxLabel à Bordeaux. Conserve la structure, remplace le contenu. Utilise #1a4d7a (bleu) et #d4a574 (doré).",
    'extract' => "Extrait uniquement la structure HTML/CSS (layout, grilles, espacements) sans contenu marketing. Template HTML vide réutilisable pour $ctxLabel.",
];
$instruction = $instructions[$mode] ?? $instructions['clone'];

$system_prompt = "Tu es un expert développeur web front-end spécialisé en immobilier local français.\n"
    . "$identity_str\n"
    . "Polices : Playfair Display (titres) + DM Sans (corps). Border-radius: 12px. Background: #f9f6f3.\n\n"
    . "RÈGLE ABSOLUE : Réponds UNIQUEMENT avec un objet JSON valide. AUCUN texte avant/après. AUCUN backtick. AUCUN markdown.\n"
    . 'Format : {"html":"...body seulement...","css":"...CSS pur...","js":"...JS pur...","meta_title":"...60 car...","meta_desc":"...155 car...","slug":"...","summary":"...1 phrase..."}';

// ════════════════════════════════════════════════════════════════
// CAS 1 : CLONAGE INTERNE (depuis DB, bypass HTTP)
// ════════════════════════════════════════════════════════════════
if ($internalId > 0 && isset($INTERNAL_MAP[$internalType])) {
    $M = $INTERNAL_MAP[$internalType];
    try {
        $st = $pdo->prepare("SELECT * FROM `{$M['table']}` WHERE {$M['col_id']}=? LIMIT 1");
        $st->execute([$internalId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'error'=>'DB: '.$e->getMessage()]); exit;
    }
    if (!$row) {
        echo json_encode(['success'=>false,'error'=>"Entrée #$internalId introuvable dans {$M['table']}"]); exit;
    }

    $src_html  = $row[$M['col_html']] ?? '';
    $src_css   = $row[$M['col_css']]  ?? '';
    $src_js    = $row[$M['col_js']]   ?? '';
    $src_title = $row[$M['col_title']]?? '';
    $src_slug  = $row[$M['col_slug']] ?? '';

    $typeLabels = ['page'=>'Page','secteur'=>'Secteur','article'=>'Article',
        'guide'=>'Guide','header'=>'Header','footer'=>'Footer','capture'=>'Capture'];
    $srcLabel = ($typeLabels[$internalType]??$internalType).' : '.$src_title;

    $key = getAnthropicKey($pdo);
    if (!$key) { echo json_encode(['success'=>false,'error'=>'Clé Anthropic introuvable']); exit; }

    $user_prompt = "$instruction\n\n"
        . "Source interne : $srcLabel (type: $internalType)\n"
        . "Page cible : $ctxLabel\n\n"
        . "=== HTML SOURCE ===\n"
        . mb_substr($src_html, 0, 30000)
        . "\n\n=== CSS SOURCE ===\n"
        . mb_substr($src_css, 0, 10000)
        . (!empty($src_js) ? "\n\n=== JS SOURCE ===\n".mb_substr($src_js,0,5000) : '');

    $cr = callClaude($key, $system_prompt, $user_prompt);
    if (!$cr['ok']) { echo json_encode(['success'=>false,'error'=>$cr['error']]); exit; }

    $result = parseClaudeJson($cr['raw']);
    echo json_encode([
        'success'     => true,
        'html'        => $result['html']       ?? '',
        'css'         => $result['css']        ?? '',
        'js'          => $result['js']         ?? '',
        'meta_title'  => $result['meta_title'] ?? '',
        'meta_desc'   => $result['meta_desc']  ?? '',
        'slug'        => $result['slug']       ?? '',
        'source_label'=> $srcLabel,
        'source_type' => 'internal',
        'summary'     => $result['summary']    ?? 'Design cloné depuis '.$srcLabel,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════════
// CAS 2 : CLONAGE EXTERNE (URL HTTP)
// ════════════════════════════════════════════════════════════════
$url = trim($input['url'] ?? '');
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success'=>false,'error'=>'URL invalide ou manquante']); exit;
}

if (!function_exists('curl_init')) {
    echo json_encode(['success'=>false,'error'=>'cURL non disponible']); exit;
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 4,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; BuilderBot/2.0)',
    CURLOPT_HTTPHEADER     => ['Accept: text/html,application/xhtml+xml,*/*'],
    CURLOPT_ENCODING       => 'gzip, deflate',
]);
$html_raw  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err) { echo json_encode(['success'=>false,'error'=>'cURL: '.$curl_err]); exit; }
if (!$html_raw || $http_code >= 400) {
    echo json_encode(['success'=>false,'error'=>"Page inaccessible (HTTP $http_code)"]); exit;
}

preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html_raw, $m_style);
$inline_css = mb_substr(implode("\n", $m_style[1]??[]), 0, 15000);

preg_match_all('/<script(?![^>]*\bsrc\b)[^>]*>(.*?)<\/script>/is', $html_raw, $m_script);
$js_parts  = array_filter($m_script[1]??[], fn($s)=>strlen(trim($s))>20);
$inline_js = mb_substr(implode("\n",$js_parts), 0, 8000);

preg_match('/<body[^>]*>(.*?)<\/body>/is', $html_raw, $m_body);
$body_html = mb_substr($m_body[1]??$html_raw, 0, 35000);

$key = getAnthropicKey($pdo);
if (!$key) { echo json_encode(['success'=>false,'error'=>'Clé Anthropic introuvable']); exit; }

$user_prompt = "$instruction\n\nURL source : $url\nType cible : $ctxLabel\n\n"
    . "=== HTML BODY ===\n".mb_substr($body_html,0,30000)
    . "\n\n=== CSS ===\n".mb_substr($inline_css,0,10000);

$cr = callClaude($key, $system_prompt, $user_prompt);
if (!$cr['ok']) { echo json_encode(['success'=>false,'error'=>$cr['error']]); exit; }

$result = parseClaudeJson($cr['raw']);
echo json_encode([
    'success'    => true,
    'html'       => $result['html']       ?? '',
    'css'        => $result['css']        ?? '',
    'js'         => $result['js']         ?? '',
    'meta_title' => $result['meta_title'] ?? '',
    'meta_desc'  => $result['meta_desc']  ?? '',
    'slug'       => $result['slug']       ?? '',
    'source_url' => $url,
    'source_type'=> 'external',
    'summary'    => $result['summary']    ?? 'Design cloné avec succès.',
], JSON_UNESCAPED_UNICODE);