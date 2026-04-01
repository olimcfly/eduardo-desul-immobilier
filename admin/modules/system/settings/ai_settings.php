<?php
/**
 * PARAMÈTRES IA — Clés API & Prompts système
 * /admin/modules/system/settings/ai_settings.php
 * Accès : dashboard.php?page=ai-settings
 */

defined('ADMIN_ROUTER') or define('ADMIN_ROUTER', true);

if (!defined('DB_HOST')) require_once dirname(__DIR__, 4) . '/config/config.php';

if (!isset($pdo)) {
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (PDOException $e) {
        die('<div style="padding:20px;color:#dc2626;font-family:monospace">❌ DB: '.htmlspecialchars($e->getMessage()).'</div>');
    }
}

if (empty($_SESSION['auth_csrf_token'])) $_SESSION['auth_csrf_token'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['auth_csrf_token'];

// ── Créer table si nécessaire ─────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ai_settings` (
        `setting_key`   VARCHAR(100) PRIMARY KEY,
        `setting_value` LONGTEXT,
        `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

// ── Prompts par défaut ────────────────────────────────────
$defaultPrompts = [
    'generate' => "Tu es un expert en rédaction SEO pour l'immobilier français. Tu rédiges des articles professionnels et optimisés pour les conseillers immobiliers indépendants. Tu maîtrises le copywriting neuro-émotionnel, les bonnes pratiques SEO on-page et la réglementation immobilière française. Tes articles sont toujours structurés (intro accrocheuse, sections H2/H3, conclusion CTA), informatifs et adaptés au persona cible. Tu réponds UNIQUEMENT en JSON valide.",
    'improve'  => "Tu es expert en rédaction SEO immobilier France. Tu améliores les contenus pour maximiser l'engagement, la lisibilité et le positionnement Google. Tu enrichis le texte avec des transitions fluides, des exemples concrets et des mots-clés sémantiques. Tu réponds UNIQUEMENT en JSON valide.",
    'meta'     => "Tu es expert SEO immobilier France. Tu génères des méta-titres et méta-descriptions optimisés pour Google : concis, accrocheurs, avec le mot-clé en début de title et un call-to-action dans la description. Tu respectes scrupuleusement les limites de caractères. Tu réponds UNIQUEMENT en JSON valide.",
    'faq'      => "Tu es expert immobilier français. Tu génères des FAQ Schema.org pertinentes et naturelles. Tes questions reflètent les vraies préoccupations des acheteurs, vendeurs et investisseurs. Tes réponses sont complètes (2-4 phrases), utiles et encouragent la confiance. Tu réponds UNIQUEMENT en JSON valide.",
    'outline'  => "Tu es stratège éditorial SEO immobilier France. Tu crées des plans d'articles structurés : titre principal + 4-6 sections H2 + sous-sections H3 si nécessaire. Tu proposes 3 variantes de titre SEO accrocheuses. Tu réponds UNIQUEMENT en JSON valide.",
    'keywords' => "Tu es expert SEO immobilier France. Tu extrais les mots-clés stratégiques d'un contenu : mot-clé principal, mots-clés secondaires sémantiques, expressions longue traîne et mots-clés locaux. Tu classes par pertinence et intention de recherche. Tu réponds UNIQUEMENT en JSON valide.",
    'rewrite'  => "Tu es copywriter immobilier France spécialisé en adaptation de contenu. Tu réécris des articles en adaptant le ton, le vocabulaire et les arguments au persona cible, sans changer les faits. Tu conserves la structure H2/H3 mais reformules le corps du texte. Tu réponds UNIQUEMENT en JSON valide.",
    'excerpt'  => "Tu es copywriter immobilier France. Tu rédiges des extraits/chapôs accrocheurs de 150-180 caractères : une promesse forte, le mot-clé naturellement intégré, une question ou affirmation qui donne envie de lire. Tu réponds UNIQUEMENT en JSON valide.",
];

$promptLabels = [
    'generate' => ['label' => 'Génération article complet',  'icon' => 'fa-magic',         'color' => '#7c3aed'],
    'improve'  => ['label' => 'Amélioration de contenu',     'icon' => 'fa-sparkles',       'color' => '#2563eb'],
    'meta'     => ['label' => 'Métas SEO (title + desc)',    'icon' => 'fa-search',         'color' => '#0891b2'],
    'faq'      => ['label' => 'FAQ Schema.org',              'icon' => 'fa-question-circle','color' => '#059669'],
    'outline'  => ['label' => 'Plan éditorial',              'icon' => 'fa-list',           'color' => '#d97706'],
    'keywords' => ['label' => 'Extraction mots-clés',        'icon' => 'fa-tags',           'color' => '#dc2626'],
    'rewrite'  => ['label' => 'Réécriture persona',          'icon' => 'fa-redo',           'color' => '#7c3aed'],
    'excerpt'  => ['label' => 'Génération extrait/chapô',    'icon' => 'fa-quote-right',    'color' => '#0891b2'],
];

function aiGetSetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $s = $pdo->prepare("SELECT setting_value FROM ai_settings WHERE setting_key = ?");
        $s->execute([$key]);
        $r = $s->fetchColumn();
        return $r !== false ? (string)$r : $default;
    } catch (Throwable $e) { return $default; }
}

function aiMaskKey(string $k): string {
    if (!$k) return '';
    if (strlen($k) <= 8) return str_repeat('•', strlen($k));
    return substr($k, 0, 8) . str_repeat('•', max(0, strlen($k) - 12)) . substr($k, -4);
}

// ── Traitement POST ───────────────────────────────────────
$saveMsg = '';
$saveErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        $saveErr = 'Token CSRF invalide.';
    } else {
        $action = $_POST['ai_action'] ?? '';

        if ($action === 'save_keys') {
            $keys = ['anthropic_api_key', 'openai_api_key', 'ai_model_anthropic', 'ai_model_openai', 'ai_max_tokens'];
            foreach ($keys as $k) {
                $v = trim($_POST[$k] ?? '');
                if (in_array($k, ['anthropic_api_key', 'openai_api_key']) && str_contains($v, '•')) continue;
                try {
                    $pdo->prepare("INSERT INTO ai_settings (setting_key, setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
                        ->execute([$k, $v]);
                } catch (Exception $e) {}
            }
            $saveMsg = '✅ Clés API sauvegardées avec succès.';

        } elseif ($action === 'save_prompt') {
            $pKey = $_POST['prompt_key'] ?? '';
            $pVal = trim($_POST['prompt_value'] ?? '');
            if (array_key_exists($pKey, $defaultPrompts)) {
                try {
                    $pdo->prepare("INSERT INTO ai_settings (setting_key, setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
                        ->execute(['prompt_' . $pKey, $pVal]);
                    $saveMsg = '✅ Prompt "'.$promptLabels[$pKey]['label'].'" sauvegardé.';
                } catch (Exception $e) { $saveErr = 'Erreur DB : '.$e->getMessage(); }
            }

        } elseif ($action === 'reset_prompt') {
            $pKey = $_POST['prompt_key'] ?? '';
            if (array_key_exists($pKey, $defaultPrompts)) {
                try {
                    $pdo->prepare("DELETE FROM ai_settings WHERE setting_key = ?")->execute(['prompt_'.$pKey]);
                    $saveMsg = '↩️ Prompt "'.$promptLabels[$pKey]['label'].'" réinitialisé.';
                } catch (Exception $e) { $saveErr = 'Erreur DB : '.$e->getMessage(); }
            }

        } elseif ($action === 'test_key') {
            $provider = $_POST['test_provider'] ?? '';
            $key = trim(aiGetSetting($pdo, $provider.'_api_key'));
            if (!$key || str_contains($key, '•')) {
                $saveErr = 'Clé API non configurée.';
            } else {
                try {
                    if ($provider === 'anthropic') {
                        $ch = curl_init('https://api.anthropic.com/v1/messages');
                        curl_setopt_array($ch, [
                            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                            CURLOPT_HTTPHEADER => ['Content-Type: application/json','x-api-key: '.$key,'anthropic-version: 2023-06-01'],
                            CURLOPT_POSTFIELDS => json_encode(['model'=>'claude-haiku-4-5-20251001','max_tokens'=>10,'messages'=>[['role'=>'user','content'=>'OK']]]),
                            CURLOPT_TIMEOUT => 15,
                        ]);
                        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
                        $saveMsg = $code === 200 ? '✅ Clé Anthropic valide !' : '❌ Erreur Anthropic HTTP '.$code;
                    } else {
                        $ch = curl_init('https://api.openai.com/v1/models');
                        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$key],CURLOPT_TIMEOUT=>15]);
                        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
                        $saveMsg = $code === 200 ? '✅ Clé OpenAI valide !' : '❌ Erreur OpenAI HTTP '.$code;
                    }
                } catch (Exception $e) { $saveErr = 'Erreur cURL : '.$e->getMessage(); }
            }
        }
    }
}

// ── Valeurs courantes ─────────────────────────────────────
$anthropicKey   = aiGetSetting($pdo, 'anthropic_api_key');
$openaiKey      = aiGetSetting($pdo, 'openai_api_key');
$modelAnthropic = aiGetSetting($pdo, 'ai_model_anthropic', 'claude-sonnet-4-6');
$modelOpenai    = aiGetSetting($pdo, 'ai_model_openai', 'gpt-4o-mini');
$maxTokens      = aiGetSetting($pdo, 'ai_max_tokens', '3000');

$configAnthropic = defined('ANTHROPIC_API_KEY') ? ANTHROPIC_API_KEY : '';
$configOpenai    = defined('OPENAI_API_KEY')    ? OPENAI_API_KEY    : '';
$activeAnthropic = $anthropicKey ?: $configAnthropic;
$activeOpenai    = $openaiKey    ?: $configOpenai;
$currentProvider = $activeAnthropic ? 'Claude (Anthropic)' : ($activeOpenai ? 'OpenAI' : '');

$activeTab = $_GET['tab'] ?? 'keys';
?>

<style>
.ais-wrap { max-width: 960px; }
.ais-banner {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    border-radius: var(--radius-lg); padding: 22px 28px; margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between;
    position: relative; overflow: hidden; flex-wrap: wrap; gap: 12px;
}
.ais-banner::before {
    content:''; position:absolute; top:-40%; right:-5%;
    width:240px; height:240px;
    background:radial-gradient(circle,rgba(255,255,255,.07),transparent 70%);
    border-radius:50%; pointer-events:none;
}
.ais-banner-left { position:relative; z-index:1; }
.ais-banner-left h2 { font-size:1.3rem; font-weight:800; color:#fff; margin:0 0 4px; display:flex; align-items:center; gap:10px; }
.ais-banner-left p  { color:rgba(255,255,255,.7); font-size:.82rem; margin:0; }
.ais-provider-badge {
    display:inline-flex; align-items:center; gap:7px;
    padding:6px 14px; border-radius:20px; font-size:.75rem; font-weight:700;
    background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.25); position:relative; z-index:1;
}
.ais-provider-badge .dot { width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 0 2px rgba(16,185,129,.3); animation:ais-pulse 2s infinite; }
.ais-provider-badge .dot.off { background:rgba(255,255,255,.5); animation:none; box-shadow:none; }
@keyframes ais-pulse { 0%,100%{box-shadow:0 0 0 2px rgba(16,185,129,.3)} 50%{box-shadow:0 0 0 5px rgba(16,185,129,.1)} }
.ais-tabs { display:flex; gap:4px; margin-bottom:20px; background:var(--surface); padding:5px; border-radius:12px; border:1px solid var(--border); width:fit-content; }
.ais-tab { display:flex; align-items:center; gap:7px; padding:8px 18px; border-radius:9px; border:none; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .15s; background:transparent; color:var(--text-2); font-family:inherit; }
.ais-tab:hover { color:#7c3aed; background:#faf5ff; }
.ais-tab.active { background:#7c3aed; color:#fff; box-shadow:0 2px 8px rgba(124,58,237,.25); }
.ais-tab .badge { background:rgba(255,255,255,.25); padding:1px 7px; border-radius:8px; font-size:.68rem; }
.ais-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
.ais-card-header { padding:14px 20px; border-bottom:1px solid var(--border); background:var(--surface-2); display:flex; align-items:center; justify-content:space-between; gap:12px; }
.ais-card-title { display:flex; align-items:center; gap:9px; font-size:.85rem; font-weight:700; color:var(--text); }
.ais-card-body { padding:20px; }
.ais-card-footer { padding:12px 20px; border-top:1px solid var(--border); background:var(--surface-2); }
.ais-field { margin-bottom:18px; }
.ais-field:last-child { margin-bottom:0; }
.ais-label { display:flex; align-items:center; gap:7px; justify-content:space-between; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-2); margin-bottom:7px; }
.ais-label-desc { font-size:.68rem; color:var(--text-3); font-weight:400; text-transform:none; letter-spacing:0; }
.ais-input-wrap { position:relative; }
.ais-input, .ais-select, .ais-textarea { width:100%; padding:10px 13px; border:1.5px solid var(--border); border-radius:9px; font-size:.83rem; color:var(--text); background:#fff; transition:border .15s,box-shadow .15s; outline:none; font-family:inherit; box-sizing:border-box; }
.ais-input:focus, .ais-select:focus, .ais-textarea:focus { border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,.1); }
.ais-input.key-field { padding-right:44px; font-family:'Courier New',monospace; font-size:.8rem; letter-spacing:.03em; }
.ais-textarea { resize:vertical; line-height:1.7; min-height:160px; font-size:.78rem; }
.ais-key-toggle { position:absolute; right:11px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-3); font-size:.9rem; padding:2px; transition:color .15s; }
.ais-key-toggle:hover { color:#7c3aed; }
.ais-key-status { display:inline-flex; align-items:center; gap:5px; font-size:.68rem; font-weight:600; padding:2px 9px; border-radius:7px; margin-top:5px; }
.ais-key-status.configured { background:#d1fae5; color:#065f46; }
.ais-key-status.missing { background:#fee2e2; color:#991b1b; }
.ais-key-status.config-php { background:#fef3c7; color:#92400e; }
.ais-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; border:none; font-size:.78rem; font-weight:700; cursor:pointer; transition:all .18s; font-family:inherit; text-decoration:none; }
.ais-btn-primary { background:#7c3aed; color:#fff; box-shadow:0 2px 8px rgba(124,58,237,.2); }
.ais-btn-primary:hover { background:#6d28d9; transform:translateY(-1px); }
.ais-btn-ghost { background:var(--surface); color:var(--text-2); border:1.5px solid var(--border); }
.ais-btn-ghost:hover { border-color:#7c3aed; color:#7c3aed; }
.ais-btn-danger { background:#fff1f1; color:#ef4444; border:1.5px solid #fca5a5; }
.ais-btn-danger:hover { background:#fee2e2; }
.ais-btn-test { background:#f0fdf4; color:#10b981; border:1.5px solid #86efac; }
.ais-btn-test:hover { background:#d1fae5; }
.ais-btn-sm { padding:6px 13px; font-size:.72rem; border-radius:7px; }
.ais-alert { padding:12px 16px; border-radius:9px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-size:.8rem; font-weight:600; }
.ais-alert.success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.ais-alert.error { background:#fff1f1; color:#991b1b; border:1px solid #fca5a5; }
.ais-info { background:#faf5ff; border:1px solid #ddd6fe; border-radius:9px; padding:12px 15px; margin-bottom:18px; font-size:.78rem; color:#5b21b6; display:flex; gap:9px; }
.ais-info i { font-size:.85rem; flex-shrink:0; margin-top:1px; }
.ais-panel { display:none; }
.ais-panel.active { display:block; }
.ais-slider-wrap { display:flex; align-items:center; gap:14px; }
.ais-slider { flex:1; accent-color:#7c3aed; }
.ais-slider-val { font-size:.9rem; font-weight:800; color:#7c3aed; min-width:48px; text-align:right; }
.ais-prompt-item { border:1.5px solid var(--border); border-radius:11px; overflow:hidden; margin-bottom:10px; transition:border-color .2s; }
.ais-prompt-item:hover { border-color:#c4b5fd; }
.ais-prompt-item.active-edit { border-color:#7c3aed; box-shadow:0 0 0 3px rgba(124,58,237,.08); }
.ais-prompt-header { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#faf5ff; cursor:pointer; gap:12px; user-select:none; }
.ais-prompt-label { display:flex; align-items:center; gap:10px; }
.ais-prompt-icon { width:30px; height:30px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:.8rem; color:#fff; flex-shrink:0; }
.ais-prompt-name { font-size:.82rem; font-weight:700; color:var(--text); }
.ais-prompt-name small { display:block; font-size:.68rem; font-weight:400; color:var(--text-3); margin-top:1px; }
.ais-prompt-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.ais-custom-badge { font-size:.62rem; font-weight:700; padding:2px 8px; border-radius:7px; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; letter-spacing:.04em; }
.ais-prompt-body { padding:16px; display:none; border-top:1px solid #ddd6fe; }
.ais-prompt-body.open { display:block; }
.ais-prompt-hint { font-size:.72rem; color:var(--text-3); margin-top:7px; }
.ais-prompt-hint code { background:var(--surface-2); padding:1px 5px; border-radius:4px; font-size:.68rem; color:#7c3aed; }
</style>

<div class="ais-wrap">

<div class="ais-banner anim">
    <div class="ais-banner-left">
        <h2><i class="fas fa-robot"></i> Paramètres IA</h2>
        <p>Clés API, modèles et prompts système pour la génération de contenu</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;position:relative;z-index:1">
        <div class="ais-provider-badge">
            <span class="dot<?= $currentProvider ? '' : ' off' ?>"></span>
            <?= $currentProvider ? htmlspecialchars($currentProvider).' actif' : 'Aucun provider' ?>
        </div>
        <a href="?page=settings" class="ais-btn ais-btn-ghost ais-btn-sm" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.25)">
            <i class="fas fa-arrow-left"></i> Paramètres
        </a>
    </div>
</div>

<?php if ($saveMsg): ?><div class="ais-alert success anim"><i class="fas fa-check-circle"></i><?= htmlspecialchars($saveMsg) ?></div><?php endif; ?>
<?php if ($saveErr): ?><div class="ais-alert error anim"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($saveErr) ?></div><?php endif; ?>

<div class="ais-tabs anim">
    <button class="ais-tab <?= $activeTab==='keys'?'active':'' ?>" onclick="aisTab('keys')">
        <i class="fas fa-key"></i> Clés API & Modèles
    </button>
    <button class="ais-tab <?= $activeTab==='prompts'?'active':'' ?>" onclick="aisTab('prompts')">
        <i class="fas fa-terminal"></i> Prompts système
        <span class="badge"><?= count($defaultPrompts) ?></span>
    </button>
</div>

<!-- PANEL : Clés API -->
<div class="ais-panel <?= $activeTab==='keys'?'active':'' ?>" id="ais-panel-keys">
    <div class="ais-info anim"><i class="fas fa-info-circle"></i><span>Les clés stockées en DB ont priorité sur celles de <code>config.php</code>. Elles sont masquées après enregistrement.</span></div>
    <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="ai_action" value="save_keys">

    <!-- Anthropic -->
    <div class="ais-card anim">
        <div class="ais-card-header">
            <div class="ais-card-title">
                <i class="fas fa-brain" style="color:#7c3aed"></i> Anthropic — Claude
                <?php if ($activeAnthropic): ?><span style="font-size:.65rem;background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:6px;font-weight:700">✓ Configuré</span><?php endif; ?>
            </div>
            <?php if ($activeAnthropic): ?>
            <form method="POST" style="margin:0">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="ai_action" value="test_key">
                <input type="hidden" name="test_provider" value="anthropic">
                <button type="submit" class="ais-btn ais-btn-test ais-btn-sm"><i class="fas fa-plug"></i> Tester</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="ais-card-body">
            <?php if ($configAnthropic && !$anthropicKey): ?>
            <div class="ais-alert success" style="margin-bottom:14px"><i class="fas fa-check-circle"></i> Clé détectée dans config.php — fonctionnelle.</div>
            <?php endif; ?>
            <div class="ais-field">
                <div class="ais-label"><span><i class="fas fa-key"></i> Clé API</span><span class="ais-label-desc">Commence par "sk-ant-"</span></div>
                <div class="ais-input-wrap">
                    <input type="password" name="anthropic_api_key" id="ais-keyAnthropic" class="ais-input key-field"
                        value="<?= $anthropicKey ? htmlspecialchars(aiMaskKey($anthropicKey)) : '' ?>"
                        placeholder="sk-ant-api03-…" autocomplete="off">
                    <button type="button" class="ais-key-toggle" onclick="aisMaskToggle('ais-keyAnthropic',this)"><i class="fas fa-eye"></i></button>
                </div>
                <?php if ($anthropicKey): ?><span class="ais-key-status configured"><i class="fas fa-check"></i> DB</span>
                <?php elseif ($configAnthropic): ?><span class="ais-key-status config-php"><i class="fas fa-file-code"></i> config.php</span>
                <?php else: ?><span class="ais-key-status missing"><i class="fas fa-times"></i> Non configurée</span><?php endif; ?>
            </div>
            <div class="ais-field">
                <div class="ais-label"><i class="fas fa-microchip"></i> Modèle</div>
                <select name="ai_model_anthropic" class="ais-select">
                    <?php foreach (['claude-sonnet-4-6'=>'Claude Sonnet 4.6 (recommandé)','claude-opus-4-6'=>'Claude Opus 4.6 (plus puissant)','claude-haiku-4-5-20251001'=>'Claude Haiku 4.5 (rapide/éco)'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $modelAnthropic===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- OpenAI -->
    <div class="ais-card anim">
        <div class="ais-card-header">
            <div class="ais-card-title">
                <i class="fas fa-robot" style="color:#10b981"></i> OpenAI — GPT
                <?php if ($activeOpenai): ?><span style="font-size:.65rem;background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:6px;font-weight:700">✓ Configuré</span><?php endif; ?>
            </div>
            <?php if ($activeOpenai): ?>
            <form method="POST" style="margin:0">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="ai_action" value="test_key">
                <input type="hidden" name="test_provider" value="openai">
                <button type="submit" class="ais-btn ais-btn-test ais-btn-sm"><i class="fas fa-plug"></i> Tester</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="ais-card-body">
            <div class="ais-info" style="margin-bottom:14px"><i class="fas fa-info-circle"></i><span>OpenAI est utilisé en fallback si aucune clé Anthropic n'est configurée.</span></div>
            <div class="ais-field">
                <div class="ais-label"><span><i class="fas fa-key"></i> Clé API</span><span class="ais-label-desc">Commence par "sk-"</span></div>
                <div class="ais-input-wrap">
                    <input type="password" name="openai_api_key" id="ais-keyOpenai" class="ais-input key-field"
                        value="<?= $openaiKey ? htmlspecialchars(aiMaskKey($openaiKey)) : '' ?>"
                        placeholder="sk-…" autocomplete="off">
                    <button type="button" class="ais-key-toggle" onclick="aisMaskToggle('ais-keyOpenai',this)"><i class="fas fa-eye"></i></button>
                </div>
                <?php if ($openaiKey): ?><span class="ais-key-status configured"><i class="fas fa-check"></i> DB</span>
                <?php elseif ($configOpenai): ?><span class="ais-key-status config-php"><i class="fas fa-file-code"></i> config.php</span>
                <?php else: ?><span class="ais-key-status missing"><i class="fas fa-times"></i> Non configurée</span><?php endif; ?>
            </div>
            <div class="ais-field">
                <div class="ais-label"><i class="fas fa-microchip"></i> Modèle</div>
                <select name="ai_model_openai" class="ais-select">
                    <?php foreach (['gpt-4o-mini'=>'GPT-4o mini (recommandé, éco)','gpt-4o'=>'GPT-4o (plus puissant)','gpt-3.5-turbo'=>'GPT-3.5 Turbo (très économique)'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $modelOpenai===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Paramètres globaux -->
    <div class="ais-card anim">
        <div class="ais-card-header">
            <div class="ais-card-title"><i class="fas fa-sliders-h"></i> Paramètres globaux</div>
        </div>
        <div class="ais-card-body">
            <div class="ais-field">
                <div class="ais-label"><i class="fas fa-coins"></i> Tokens max par requête <span class="ais-label-desc">Longueur des réponses & coût</span></div>
                <div class="ais-slider-wrap">
                    <input type="range" name="ai_max_tokens" id="ais-tokensSlider" class="ais-slider"
                        min="500" max="6000" step="100" value="<?= (int)$maxTokens ?>"
                        oninput="document.getElementById('ais-tokensVal').textContent=this.value">
                    <span class="ais-slider-val" id="ais-tokensVal"><?= (int)$maxTokens ?></span>
                </div>
                <div class="ais-prompt-hint" style="margin-top:8px">
                    Recommandé : <code>2000-3000</code> pour articles · <code>400-800</code> pour métas · <code>4000-5000</code> pour articles longs
                </div>
            </div>
        </div>
        <div class="ais-card-footer" style="display:flex;justify-content:flex-end">
            <button type="submit" class="ais-btn ais-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </div>
    </form>
</div>

<!-- PANEL : Prompts -->
<div class="ais-panel <?= $activeTab==='prompts'?'active':'' ?>" id="ais-panel-prompts">
    <div class="ais-info anim"><i class="fas fa-info-circle"></i><span>Personnalisez les prompts pour adapter les réponses à votre positionnement. Terminez toujours par <code>Tu réponds UNIQUEMENT en JSON valide.</code></span></div>

    <?php foreach ($defaultPrompts as $key => $defaultVal):
        $info       = $promptLabels[$key];
        $currentVal = aiGetSetting($pdo, 'prompt_'.$key);
        $isCustom   = !empty($currentVal);
        $displayVal = $isCustom ? $currentVal : $defaultVal;
    ?>
    <div class="ais-prompt-item anim" id="ais-prompt-item-<?= $key ?>">
        <div class="ais-prompt-header" onclick="aisTogglePrompt('<?= $key ?>')">
            <div class="ais-prompt-label">
                <div class="ais-prompt-icon" style="background:<?= $info['color'] ?>"><i class="fas <?= $info['icon'] ?>"></i></div>
                <div class="ais-prompt-name">
                    <?= htmlspecialchars($info['label']) ?>
                    <small>articles.<?= $key ?></small>
                </div>
            </div>
            <div class="ais-prompt-actions">
                <?php if ($isCustom): ?><span class="ais-custom-badge">PERSO</span>
                <?php else: ?><span style="font-size:.65rem;color:var(--text-3);font-weight:600">Défaut</span><?php endif; ?>
                <i class="fas fa-chevron-down" id="ais-chevron-<?= $key ?>" style="color:var(--text-3);font-size:.7rem;transition:transform .2s"></i>
            </div>
        </div>
        <div class="ais-prompt-body" id="ais-prompt-body-<?= $key ?>">
            <form method="POST" id="ais-form-<?= $key ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="ai_action" value="save_prompt">
                <input type="hidden" name="prompt_key" value="<?= $key ?>">
                <div class="ais-field">
                    <div class="ais-label">
                        <span><i class="fas fa-terminal"></i> Prompt système</span>
                        <span class="ais-label-desc" id="ais-cc-<?= $key ?>"><?= mb_strlen($displayVal) ?> car.</span>
                    </div>
                    <textarea name="prompt_value" class="ais-textarea"
                        oninput="document.getElementById('ais-cc-<?= $key ?>').textContent=this.value.length+' car.'"
                        ><?= htmlspecialchars($displayVal) ?></textarea>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;flex-wrap:wrap;gap:8px">
                    <?php if ($isCustom): ?>
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="ai_action" value="reset_prompt">
                        <input type="hidden" name="prompt_key" value="<?= $key ?>">
                        <button type="submit" class="ais-btn ais-btn-danger ais-btn-sm" onclick="return confirm('Remettre le prompt par défaut ?')"><i class="fas fa-undo"></i> Réinitialiser</button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:.7rem;color:var(--text-3);font-style:italic">Prompt par défaut — non modifié</span>
                    <?php endif; ?>
                    <button type="submit" form="ais-form-<?= $key ?>" class="ais-btn ais-btn-primary ais-btn-sm"><i class="fas fa-save"></i> Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</div>

<script>
function aisTab(tab) {
    document.querySelectorAll('.ais-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.ais-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('ais-panel-'+tab).classList.add('active');
    document.querySelectorAll('.ais-tab').forEach(b => { if (b.getAttribute('onclick')?.includes("'"+tab+"'")) b.classList.add('active'); });
    history.replaceState(null,'','?page=ai-settings&tab='+tab);
}
function aisMaskToggle(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.innerHTML = inp.type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
}
function aisTogglePrompt(key) {
    const body = document.getElementById('ais-prompt-body-'+key);
    const chevron = document.getElementById('ais-chevron-'+key);
    const item = document.getElementById('ais-prompt-item-'+key);
    const isOpen = body.classList.contains('open');
    document.querySelectorAll('.ais-prompt-body').forEach(b => b.classList.remove('open'));
    document.querySelectorAll('[id^="ais-chevron-"]').forEach(c => c.style.transform='rotate(0deg)');
    document.querySelectorAll('.ais-prompt-item').forEach(i => i.classList.remove('active-edit'));
    if (!isOpen) { body.classList.add('open'); chevron.style.transform='rotate(180deg)'; item.classList.add('active-edit'); }
}
const _p = new URLSearchParams(location.search);
if (_p.get('prompt')) aisTogglePrompt(_p.get('prompt'));
</script>