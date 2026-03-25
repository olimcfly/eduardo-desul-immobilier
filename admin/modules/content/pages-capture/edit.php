<?php
/**
 * ══════════════════════════════════════════════════════════════
 *  ÉDITEUR DE PAGE DE CAPTURE
 *  /admin/modules/content/pages-capture/edit.php
 *  Chargé depuis index.php quand ?action=edit&id=X ou ?action=create
 * ══════════════════════════════════════════════════════════════
 */

// ── Données d'initialisation ──────────────────────────────────
$captureId = (int)($_GET['id'] ?? 0);
$isNew     = ($captureId === 0);
$capture   = [];
$saveError = null;
$saveOk    = false;

// ── Colonnes disponibles (compat schémas DB) ────────────────
$captureCols = [];
try {
    $captureCols = $pdo->query("SHOW COLUMNS FROM captures")->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (Exception $e) {
    $captureCols = [];
}
$hasCol = static fn(string $col): bool => in_array($col, $captureCols, true);

// ── Disponibilité IA ──────────────────────────────────────────
$aiAvailable = (defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY))
            || (defined('OPENAI_API_KEY')    && !empty(OPENAI_API_KEY));
$aiProvider = '';
if (defined('ANTHROPIC_API_KEY') && !empty(ANTHROPIC_API_KEY)) $aiProvider = 'Claude';
elseif (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY))   $aiProvider = 'OpenAI';
$AI_ENDPOINT = '/admin/api/ai/generate.php';
$SAVE_ENDPOINT = '/admin/api/builder/save-content.php';

// ── Types et templates ────────────────────────────────────────
$captureTypes = [
    'estimation' => ['icon' => 'fa-calculator', 'label' => 'Estimation',         'color' => '#3b82f6'],
    'contact'    => ['icon' => 'fa-envelope',   'label' => 'Contact',             'color' => '#10b981'],
    'newsletter' => ['icon' => 'fa-newspaper',  'label' => 'Newsletter',          'color' => '#ec4899'],
    'guide'      => ['icon' => 'fa-book-open',  'label' => 'Guide / Lead Magnet', 'color' => '#8b5cf6'],
];
$offerFormats = [
    'pdf'   => ['label' => 'Guide PDF',      'form_title' => 'Recevez votre guide PDF'],
    'video' => ['label' => 'Vidéo / Replay', 'form_title' => 'Recevez le lien vidéo'],
    'call'  => ['label' => 'Appel / RDV',    'form_title' => 'Demandez un rappel'],
];
$offerObjectives = [
    'vendeur'      => 'Vendeur',
    'acheteur'     => 'Acheteur',
    'proprietaire' => 'Propriétaire',
    'estimation'   => 'Estimation',
    'rdv'          => 'Prise de RDV',
];
$acquisitionChannels = [
    'organique'    => ['label' => '🌱 Organique'],
    'mailing'      => ['label' => '📧 Mailing'],
    'google_ads'   => ['label' => '🔍 Google Ads'],
    'facebook_ads' => ['label' => '📱 Facebook/Meta Ads'],
];

function buildCaptureFormPreset(string $format, string $objective, string $crmSource, string $channel): array {
    $base = [
        ['name' => 'prenom', 'label' => 'Prénom', 'type' => 'text',  'required' => true,  'placeholder' => 'Votre prénom'],
        ['name' => 'email',  'label' => 'Email',  'type' => 'email', 'required' => true,  'placeholder' => 'vous@email.com'],
        ['name' => 'telephone', 'label' => 'Téléphone', 'type' => 'tel', 'required' => false, 'placeholder' => '06 00 00 00 00'],
    ];

    if ($format === 'call') {
        $base[] = ['name' => 'disponibilite', 'label' => 'Disponibilités', 'type' => 'text', 'required' => false, 'placeholder' => 'Ex: demain 18h'];
        $base[] = ['name' => 'objectif', 'label' => 'Votre objectif', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Décrivez votre projet en 2 lignes'];
    } elseif ($format === 'video') {
        $base[] = ['name' => 'projet', 'label' => 'Votre projet', 'type' => 'select', 'required' => false, 'options' => ['Vente', 'Achat', 'Investissement', 'Autre']];
    } else {
        $base[] = ['name' => 'consentement', 'label' => 'J’accepte d’être contacté(e) dans le cadre de ma demande.', 'type' => 'checkbox', 'required' => true];
    }

    $base[] = ['name' => 'capture_format', 'label' => 'Format', 'type' => 'hidden', 'placeholder' => $format];
    $base[] = ['name' => 'capture_objectif', 'label' => 'Objectif', 'type' => 'hidden', 'placeholder' => $objective];
    $base[] = ['name' => 'capture_source', 'label' => 'Source', 'type' => 'hidden', 'placeholder' => $crmSource];
    $base[] = ['name' => 'capture_channel', 'label' => 'Canal', 'type' => 'hidden', 'placeholder' => $channel];
    $base[] = ['name' => 'utm_source', 'label' => 'UTM Source', 'type' => 'hidden', 'placeholder' => $channel];
    $base[] = ['name' => 'utm_medium', 'label' => 'UTM Medium', 'type' => 'hidden', 'placeholder' => in_array($channel, ['google_ads','facebook_ads']) ? 'paid' : ($channel === 'mailing' ? 'email' : 'organic')];

    return $base;
}

// ── Charger la capture existante ─────────────────────────────
if (!$isNew && $captureId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM captures WHERE id = ? LIMIT 1");
        $stmt->execute([$captureId]);
        $capture = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { $saveError = 'Capture introuvable : ' . $e->getMessage(); }
}

// ── Traitement POST (sauvegarde) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_edit_submit'] ?? '') === '1') {
    $d = [
        'titre'        => trim($_POST['titre']        ?? ''),
        'slug'         => trim($_POST['slug']         ?? ''),
        'type'         => $_POST['type']              ?? 'guide',
        'template'     => 'editor',
        'offer_format' => $_POST['offer_format']      ?? 'pdf',
        'objective'    => $_POST['objective']         ?? 'vendeur',
        'acquisition_channel' => $_POST['acquisition_channel'] ?? 'organique',
        'headline'     => trim($_POST['headline']     ?? ''),
        'sous_titre'   => trim($_POST['sous_titre']   ?? ''),
        'description'  => trim($_POST['description']  ?? ''),
        'cta_text'     => trim($_POST['cta_text']     ?? ''),
        'html_capture' => trim($_POST['html_capture'] ?? ''),
        'html_merci'   => trim($_POST['html_merci'] ?? ''),
        'page_merci_url'=> trim($_POST['page_merci_url'] ?? '/merci'),
        'status'       => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
        'active'       => ($_POST['status'] ?? 'inactive') === 'active' ? 1 : 0,
        'actif'        => ($_POST['status'] ?? 'inactive') === 'active' ? 1 : 0,
    ];

    // Slug auto si vide
    if (!$d['slug'] && $d['titre']) {
        $d['slug'] = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', iconv('UTF-8','ASCII//TRANSLIT', $d['titre'])), '-'));
    }
    $d['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower($d['slug']));
    if ($d['html_capture'] !== '' && strpos($d['html_capture'], '{{FORMULAIRE}}') === false) {
        $d['html_capture'] .= "\n\n<section class=\"capture-form-wrap\">{{FORMULAIRE}}</section>";
    }
    if (!isset($acquisitionChannels[$d['acquisition_channel']])) $d['acquisition_channel'] = 'organique';
    $d['crm_source'] = trim($_POST['lead_source'] ?? ('capture_' . $d['acquisition_channel'] . '_' . $d['offer_format'] . '_' . $d['objective'] . '_' . $d['slug']));
    $d['lead_tags']  = trim($_POST['lead_tags'] ?? ('capture,' . $d['acquisition_channel'] . ',' . $d['offer_format'] . ',' . $d['objective']));
    $formPreset      = buildCaptureFormPreset($d['offer_format'], $d['objective'], $d['crm_source'], $d['acquisition_channel']);
    $formTitle       = $offerFormats[$d['offer_format']]['form_title'] ?? 'Demandez votre ressource';
    $buttonText      = $d['cta_text'] ?: 'Recevoir maintenant';

    if (!$d['titre'] || !$d['slug']) {
        $saveError = 'Le titre et le slug sont obligatoires.';
    } else {
        try {
            if ($isNew) {
                $insertCols = ['titre','slug','type','template','headline','sous_titre','description','cta_text','page_merci_url','status','active','actif','vues','conversions','taux_conversion'];
                $insertData = [
                    'titre' => $d['titre'], 'slug' => $d['slug'], 'type' => $d['type'], 'template' => $d['template'],
                    'headline' => $d['headline'], 'sous_titre' => $d['sous_titre'], 'description' => $d['description'],
                    'cta_text' => $d['cta_text'], 'page_merci_url' => $d['page_merci_url'], 'status' => $d['status'],
                    'active' => $d['active'], 'actif' => $d['actif'], 'vues' => 0, 'conversions' => 0, 'taux_conversion' => 0.00
                ];
                if ($hasCol('lead_source'))      { $insertCols[] = 'lead_source';      $insertData['lead_source'] = $d['crm_source']; }
                if ($hasCol('lead_tags'))        { $insertCols[] = 'lead_tags';        $insertData['lead_tags'] = $d['lead_tags']; }
                if ($hasCol('form_config'))      { $insertCols[] = 'form_config';      $insertData['form_config'] = json_encode($formPreset, JSON_UNESCAPED_UNICODE); }
                if ($hasCol('form_titre'))       { $insertCols[] = 'form_titre';       $insertData['form_titre'] = $formTitle; }
                if ($hasCol('form_button_text')) { $insertCols[] = 'form_button_text'; $insertData['form_button_text'] = $buttonText; }
                if ($hasCol('html_capture'))     { $insertCols[] = 'html_capture';     $insertData['html_capture'] = $d['html_capture']; }
                elseif ($hasCol('contenu'))      { $insertCols[] = 'contenu';          $insertData['contenu'] = $d['html_capture']; }
                if ($hasCol('html_merci'))       { $insertCols[] = 'html_merci';       $insertData['html_merci'] = $d['html_merci']; }

                $sql = "INSERT INTO captures (" . implode(',', $insertCols) . ")
                        VALUES (:" . implode(',:', array_keys($insertData)) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($insertData);
                $newId = (int)$pdo->lastInsertId();
                header('Location: ?page=captures&action=edit&id=' . $newId . '&msg=created');
                exit;
            } else {
                $updateData = [
                    'titre' => $d['titre'], 'slug' => $d['slug'], 'type' => $d['type'], 'template' => $d['template'],
                    'headline' => $d['headline'], 'sous_titre' => $d['sous_titre'], 'description' => $d['description'],
                    'cta_text' => $d['cta_text'], 'page_merci_url' => $d['page_merci_url'],
                    'status' => $d['status'], 'active' => $d['active'], 'actif' => $d['actif'], 'id' => $captureId
                ];
                $setClauses = [
                    'titre=:titre', 'slug=:slug', 'type=:type', 'template=:template',
                    'headline=:headline', 'sous_titre=:sous_titre', 'description=:description',
                    'cta_text=:cta_text', 'page_merci_url=:page_merci_url',
                    'status=:status', 'active=:active', 'actif=:actif'
                ];
                if ($hasCol('lead_source'))      { $setClauses[] = 'lead_source=:lead_source';             $updateData['lead_source'] = $d['crm_source']; }
                if ($hasCol('lead_tags'))        { $setClauses[] = 'lead_tags=:lead_tags';                 $updateData['lead_tags'] = $d['lead_tags']; }
                if ($hasCol('form_config'))      { $setClauses[] = 'form_config=:form_config';             $updateData['form_config'] = json_encode($formPreset, JSON_UNESCAPED_UNICODE); }
                if ($hasCol('form_titre'))       { $setClauses[] = 'form_titre=:form_titre';               $updateData['form_titre'] = $formTitle; }
                if ($hasCol('form_button_text')) { $setClauses[] = 'form_button_text=:form_button_text';   $updateData['form_button_text'] = $buttonText; }
                if ($hasCol('html_capture'))     { $setClauses[] = 'html_capture=:html_capture';           $updateData['html_capture'] = $d['html_capture']; }
                elseif ($hasCol('contenu'))      { $setClauses[] = 'contenu=:contenu';                     $updateData['contenu'] = $d['html_capture']; }
                if ($hasCol('html_merci'))       { $setClauses[] = 'html_merci=:html_merci';               $updateData['html_merci'] = $d['html_merci']; }

                $stmt = $pdo->prepare("UPDATE captures SET " . implode(', ', $setClauses) . " WHERE id=:id");
                $stmt->execute($updateData);
                // Recharger
                $stmt2 = $pdo->prepare("SELECT * FROM captures WHERE id = ?");
                $stmt2->execute([$captureId]);
                $capture = $stmt2->fetch(PDO::FETCH_ASSOC) ?: $d;
                $saveOk = true;
            }
        } catch (Exception $e) {
            $saveError = $e->getMessage();
        }
    }
}

// Valeurs courantes (fusion POST pour re-affichage en cas d'erreur)
$existingFormConfig = json_decode($capture['form_config'] ?? '[]', true);
$existingFormat = 'pdf';
if (is_array($existingFormConfig)) {
    foreach ($existingFormConfig as $f) {
        if (($f['name'] ?? '') === 'capture_format' && !empty($f['placeholder'])) {
            $existingFormat = (string)$f['placeholder'];
            break;
        }
    }
}
$existingObjective = 'vendeur';
if (!empty($capture['lead_source'])) {
    $parts = explode('_', (string)$capture['lead_source']);
    if (!empty($parts[3])) $existingObjective = $parts[3];
}
$existingChannel = 'organique';
if (!empty($capture['lead_source'])) {
    $parts = explode('_', (string)$capture['lead_source']);
    if (!empty($parts[1]) && isset($acquisitionChannels[$parts[1]])) $existingChannel = $parts[1];
}

$v = [
    'titre'         => $_POST['titre']         ?? ($capture['titre']         ?? ''),
    'slug'          => $_POST['slug']          ?? ($capture['slug']          ?? ''),
    'type'          => $_POST['type']          ?? ($capture['type']          ?? 'guide'),
    'template'      => $_POST['template']      ?? ($capture['template']      ?? 'editor'),
    'offer_format'  => $_POST['offer_format']  ?? $existingFormat,
    'objective'     => $_POST['objective']     ?? $existingObjective,
    'acquisition_channel' => $_POST['acquisition_channel'] ?? $existingChannel,
    'headline'      => $_POST['headline']      ?? ($capture['headline']      ?? ''),
    'sous_titre'    => $_POST['sous_titre']    ?? ($capture['sous_titre']    ?? ''),
    'description'   => $_POST['description']  ?? ($capture['description']   ?? ''),
    'cta_text'      => $_POST['cta_text']      ?? ($capture['cta_text']      ?? '📥 Recevoir mon guide gratuitement'),
    'html_capture'  => $_POST['html_capture']  ?? ($capture['html_capture']  ?? ($capture['contenu'] ?? '')),
    'html_merci'    => $_POST['html_merci']    ?? ($capture['html_merci']    ?? ''),
    'page_merci_url'=> $_POST['page_merci_url']?? ($capture['page_merci_url']?? '/merci'),
    'lead_source'   => $_POST['lead_source']   ?? ($capture['lead_source']   ?? ''),
    'lead_tags'     => $_POST['lead_tags']     ?? ($capture['lead_tags']     ?? ''),
    'status'        => $_POST['status']        ?? ($capture['status']        ?? 'inactive'),
    'vues'          => (int)($capture['vues']        ?? 0),
    'conversions'   => (int)($capture['conversions'] ?? 0),
    'taux'          => (float)($capture['taux_conversion'] ?? 0),
];

$capUrl = '/capture/' . ($v['slug'] ?: 'draft');
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<style>
/* ══ ÉDITEUR CAPTURE ══ */
.capedit-wrap { font-family: var(--font); max-width: 1100px; }
.capedit-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.capedit-header-l h2 { font-family:var(--font-display); font-size:1.3rem; font-weight:700; color:var(--text); display:flex; align-items:center; gap:8px; margin:0 0 4px; }
.capedit-header-l p { color:var(--text-2); font-size:.82rem; margin:0; }
.capedit-header-r { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.capedit-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 18px; border-radius:var(--radius); font-size:.82rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; font-family:var(--font); text-decoration:none; }
.capedit-btn-primary { background:#8b5cf6; color:#fff; }
.capedit-btn-primary:hover { background:#7c3aed; color:#fff; transform:translateY(-1px); }
.capedit-btn-outline { background:var(--surface); color:var(--text-2); border:1px solid var(--border); }
.capedit-btn-outline:hover { border-color:var(--border-h); background:var(--surface-2); }
.capedit-btn-preview { background:linear-gradient(135deg,#0ea5e9,#2563eb); color:#fff; }
.capedit-btn-preview:hover { transform:translateY(-1px); color:#fff; }
.capedit-btn-save { background:linear-gradient(135deg,#8b5cf6,#6366f1); color:#fff; box-shadow:0 2px 8px rgba(99,102,241,.3); }
.capedit-btn-save:hover { transform:translateY(-1px); box-shadow:0 4px 16px rgba(99,102,241,.35); color:#fff; }
.capedit-flash { padding:11px 16px; border-radius:var(--radius); font-size:.83rem; font-weight:600; margin-bottom:18px; display:flex; align-items:center; gap:8px; animation:capFI .3s ease; }
.capedit-flash.ok { background:#d1fae5; color:#065f46; border:1px solid rgba(5,150,105,.12); }
.capedit-flash.err{ background:rgba(220,38,38,.06); color:#dc2626; border:1px solid rgba(220,38,38,.12); }
@keyframes capFI { from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)} }
.capedit-grid { display:grid; grid-template-columns:1fr 340px; gap:22px; align-items:start; }
.capedit-main {}
.capedit-side {}

/* ── Cards ── */
.capedit-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; margin-bottom:18px; }
.capedit-card-hd { padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px; background:var(--surface-2); }
.capedit-card-hd h3 { font-size:.85rem; font-weight:700; color:var(--text); margin:0; }
.capedit-card-hd i { color:#8b5cf6; font-size:.8rem; }
.capedit-card-body { padding:20px; }

/* ── Champs ── */
.capedit-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.capedit-field { margin-bottom:16px; }
.capedit-field:last-child { margin-bottom:0; }
.capedit-label { display:block; font-size:.75rem; font-weight:700; color:var(--text-2); margin-bottom:5px; text-transform:uppercase; letter-spacing:.04em; }
.capedit-input, .capedit-textarea, .capedit-select {
    width:100%; padding:9px 12px; background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius); color:var(--text); font-size:.85rem; font-family:var(--font);
    transition:border-color .15s, box-shadow .15s;
}
.capedit-input:focus, .capedit-textarea:focus, .capedit-select:focus {
    outline:none; border-color:#8b5cf6; box-shadow:0 0 0 3px rgba(139,92,246,.1);
}
.capedit-textarea { resize:vertical; min-height:80px; }
.capedit-code { min-height:320px; font-family:var(--mono, ui-monospace, SFMono-Regular, Menlo, monospace); font-size:.78rem; line-height:1.5; background:#0f172a; color:#e2e8f0; border-color:#1e293b; }
.capedit-input::placeholder, .capedit-textarea::placeholder { color:var(--text-3); }
.capedit-hint { font-size:.7rem; color:var(--text-3); margin-top:4px; }
.capedit-slug-preview { display:inline-flex; align-items:center; gap:5px; background:var(--surface-2); border:1px solid var(--border); border-radius:6px; padding:4px 10px; font-size:.72rem; color:var(--text-2); font-family:var(--mono); margin-top:5px; }
.capedit-ai-actions { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
.capedit-btn-ai { background:linear-gradient(135deg,#f97316,#ea580c); color:#fff; border:none; }
.capedit-btn-ai:hover { transform:translateY(-1px); color:#fff; box-shadow:0 8px 18px rgba(234,88,12,.28); }
.capedit-ai-status { margin-top:8px; font-size:.75rem; border-radius:8px; padding:8px 10px; display:none; }
.capedit-ai-status.ok { display:block; background:#dcfce7; border:1px solid #86efac; color:#166534; }
.capedit-ai-status.err { display:block; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; }
.capedit-ai-help { margin-top:8px; font-size:.72rem; color:var(--text-3); }

/* Template selector */
.capedit-tpl-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.capedit-tpl-item { padding:10px 12px; border:2px solid var(--border); border-radius:10px; cursor:pointer; transition:all .15s; }
.capedit-tpl-item:hover { border-color:#8b5cf6; background:rgba(139,92,246,.04); }
.capedit-tpl-item.selected { border-color:#8b5cf6; background:rgba(139,92,246,.06); }
.capedit-tpl-item input { display:none; }
.capedit-tpl-name { font-size:.82rem; font-weight:700; color:var(--text); }
.capedit-tpl-desc { font-size:.7rem; color:var(--text-3); margin-top:2px; }
.capedit-tpl-badge { display:inline-block; font-size:.6rem; font-weight:700; padding:1px 7px; border-radius:10px; background:#8b5cf622; color:#8b5cf6; margin-top:3px; }

/* Status toggle */
.capedit-status-row { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-radius:10px; border:1px solid var(--border); background:var(--surface); }
.capedit-status-row.active-bg { background:rgba(5,150,69,.04); border-color:rgba(5,150,69,.2); }
.capedit-toggle { position:relative; display:inline-block; width:40px; height:22px; }
.capedit-toggle input { opacity:0; width:0; height:0; }
.capedit-slider { position:absolute; cursor:pointer; inset:0; background:#cbd5e1; border-radius:22px; transition:.2s; }
.capedit-slider:before { content:''; position:absolute; height:16px; width:16px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.2s; }
.capedit-toggle input:checked + .capedit-slider { background:#059669; }
.capedit-toggle input:checked + .capedit-slider:before { transform:translateX(18px); }
.capedit-status-label { font-size:.82rem; font-weight:700; }
.capedit-status-label.on  { color:#059669; }
.capedit-status-label.off { color:var(--text-3); }

/* Stats mini */
.capedit-stats-mini { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:18px; }
.capedit-stat-mini { text-align:center; padding:10px 8px; background:var(--surface-2); border-radius:var(--radius); border:1px solid var(--border); }
.capedit-stat-mini .num { font-family:var(--font-display); font-size:1.2rem; font-weight:800; }
.capedit-stat-mini .num.blue { color:var(--accent); }
.capedit-stat-mini .num.green { color:var(--green); }
.capedit-stat-mini .num.amber { color:#f59e0b; }
.capedit-stat-mini .lbl { font-size:.6rem; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; font-weight:600; margin-top:2px; }

/* Preview iframe */
.capedit-preview-box { border-radius:12px; overflow:hidden; border:1px solid var(--border); background:var(--surface); }
.capedit-preview-bar { display:flex; align-items:center; gap:8px; padding:10px 14px; background:var(--surface-2); border-bottom:1px solid var(--border); }
.capedit-preview-dots { display:flex; gap:4px; }
.capedit-preview-dot { width:10px; height:10px; border-radius:50%; }
.capedit-preview-url { flex:1; padding:5px 10px; background:var(--surface); border:1px solid var(--border); border-radius:6px; font-size:.72rem; font-family:var(--mono); color:var(--text-2); }
.capedit-preview-frame { display:block; width:100%; height:400px; border:none; }

/* Leads récents */
.capedit-lead-row { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); }
.capedit-lead-row:last-child { border-bottom:none; }
.capedit-lead-avatar { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#8b5cf6,#6366f1); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; flex-shrink:0; }
.capedit-lead-info { flex:1; min-width:0; }
.capedit-lead-name { font-size:.8rem; font-weight:700; color:var(--text); }
.capedit-lead-email { font-size:.7rem; color:var(--text-3); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.capedit-lead-date { font-size:.65rem; color:var(--text-3); flex-shrink:0; }

@media(max-width:900px) { .capedit-grid { grid-template-columns:1fr; } }
@media(max-width:600px) { .capedit-row { grid-template-columns:1fr; } .capedit-tpl-grid { grid-template-columns:1fr; } }
</style>

<div class="capedit-wrap">

<?php if ($saveOk): ?>
<div class="capedit-flash ok"><i class="fas fa-check-circle"></i> Capture enregistrée avec succès !</div>
<?php endif; ?>
<?php if ($saveError): ?>
<div class="capedit-flash err"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($saveError) ?></div>
<?php endif; ?>

<!-- Header -->
<div class="capedit-header">
    <div class="capedit-header-l">
        <h2>
            <i class="fas fa-magnet" style="color:#8b5cf6"></i>
            <?= $isNew ? 'Nouvelle page de capture' : 'Éditer la capture' ?>
        </h2>
        <p>
            <?php if (!$isNew && $v['slug']): ?>
                URL : <code>/capture/<?= htmlspecialchars($v['slug']) ?></code>
                · <?= $v['vues'] ?> vues · <?= $v['conversions'] ?> leads
            <?php else: ?>
                Configurez et activez votre page de capture
            <?php endif; ?>
        </p>
    </div>
    <div class="capedit-header-r">
        <a href="?page=captures" class="capedit-btn capedit-btn-outline">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <?php if (!$isNew && $v['slug']): ?>
        <a href="/capture/<?= htmlspecialchars($v['slug']) ?>" target="_blank" class="capedit-btn capedit-btn-preview">
            <i class="fas fa-eye"></i> Voir la page
        </a>
        <?php endif; ?>
        <?php if (!$isNew): ?>
        <a href="?page=builder-editor&context=capture&entity_id=<?= $captureId ?>"
           class="capedit-btn" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;gap:6px">
            <i class="fas fa-code"></i> Éditeur Capture
        </a>
        <a href="?page=builder-editor&context=capture_thankyou&entity_id=<?= $captureId ?>"
           class="capedit-btn" style="background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff;gap:6px">
            <i class="fas fa-heart"></i> Éditeur Remerciement
        </a>
        <?php endif; ?>
        <button type="submit" form="capeditForm" class="capedit-btn capedit-btn-save">
            <i class="fas fa-save"></i> Enregistrer
        </button>
    </div>
</div>

<form id="capeditForm" method="POST">
<input type="hidden" name="_edit_submit" value="1">
<input type="hidden" name="template" value="editor">

<div class="capedit-grid">

    <!-- ══ COLONNE PRINCIPALE ══ -->
    <div class="capedit-main">

        <!-- Identité -->
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-id-card"></i><h3>Identité de la capture</h3></div>
            <div class="capedit-card-body">
                <div class="capedit-row">
                    <div class="capedit-field">
                        <label class="capedit-label">Titre interne <span style="color:#dc2626">*</span></label>
                        <input type="text" name="titre" class="capedit-input"
                               value="<?= htmlspecialchars($v['titre']) ?>"
                               placeholder="Ex : 💰 Comment fixer le juste prix de vente"
                               oninput="capeditAutoSlug(this.value)" required>
                        <div class="capedit-hint">Nom affiché dans l'admin uniquement</div>
                    </div>
                    <div class="capedit-field">
                        <label class="capedit-label">Slug URL <span style="color:#dc2626">*</span></label>
                        <input type="text" name="slug" id="capeditSlug" class="capedit-input"
                               value="<?= htmlspecialchars($v['slug']) ?>"
                               placeholder="guide-vente-prix"
                               pattern="[a-z0-9-]+" required>
                        <div class="capedit-slug-preview">🔗 /capture/<span id="capeditSlugPreview"><?= htmlspecialchars($v['slug'] ?: '…') ?></span></div>
                    </div>
                </div>
                <div class="capedit-row">
                    <div class="capedit-field">
                        <label class="capedit-label">Type</label>
                        <select name="type" class="capedit-select">
                            <?php foreach ($captureTypes as $key => $ct): ?>
                            <option value="<?= $key ?>" <?= $v['type'] === $key ? 'selected' : '' ?>><?= $ct['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="capedit-field">
                        <label class="capedit-label">Format de l'offre</label>
                        <select name="offer_format" class="capedit-select" id="capeditOfferFormat">
                            <?php foreach ($offerFormats as $key => $fmt): ?>
                            <option value="<?= $key ?>" <?= $v['offer_format'] === $key ? 'selected' : '' ?>><?= $fmt['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="capedit-row">
                    <div class="capedit-field">
                        <label class="capedit-label">Objectif CRM</label>
                        <select name="objective" class="capedit-select" id="capeditObjective">
                            <?php foreach ($offerObjectives as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $v['objective'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="capedit-field">
                        <label class="capedit-label">URL de remerciement</label>
                        <input type="text" name="page_merci_url" class="capedit-input"
                               value="<?= htmlspecialchars($v['page_merci_url']) ?>"
                               placeholder="/merci?guide=guide-vente-prix">
                        <div class="capedit-hint">Redirect après soumission du formulaire</div>
                    </div>
                </div>
                <div class="capedit-row">
                    <div class="capedit-field">
                        <label class="capedit-label">Canal d'acquisition</label>
                        <select name="acquisition_channel" class="capedit-select" id="capeditChannel">
                            <?php foreach ($acquisitionChannels as $key => $channel): ?>
                            <option value="<?= $key ?>" <?= $v['acquisition_channel'] === $key ? 'selected' : '' ?>><?= $channel['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="capedit-hint">Le canal sert à segmenter le hub (mailing, ads, organique) et à générer les UTM.</div>
                    </div>
                    <div class="capedit-field">
                        <label class="capedit-label">Source CRM (tracking)</label>
                        <input type="text" name="lead_source" id="capeditLeadSource" class="capedit-input"
                               value="<?= htmlspecialchars($v['lead_source']) ?>"
                               placeholder="capture_google_ads_pdf_vendeur_guide-vente-prix">
                        <div class="capedit-hint">Permet d’identifier précisément la source dans le CRM</div>
                    </div>
                </div>
                <div class="capedit-row">
                    <div class="capedit-field">
                        <label class="capedit-label">Tags CRM</label>
                        <input type="text" name="lead_tags" class="capedit-input"
                               value="<?= htmlspecialchars($v['lead_tags']) ?>"
                               placeholder="capture,google_ads,pdf,vendeur">
                    </div>
                    <div class="capedit-field">
                        <label class="capedit-label">UTM auto-générées</label>
                        <input type="text" id="capeditUtmPreview" class="capedit-input" value="" readonly>
                        <div class="capedit-hint">Exemple: utm_source=google_ads&utm_medium=paid&utm_campaign=capture-...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu visible -->
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-heading"></i><h3>Contenu de la page</h3></div>
            <div class="capedit-card-body">
                <div class="capedit-field">
                    <label class="capedit-label">Headline (titre principal) <span style="color:#dc2626">*</span></label>
                    <input type="text" name="headline" class="capedit-input"
                           value="<?= htmlspecialchars($v['headline']) ?>"
                           placeholder="Ex : 📥 Téléchargez gratuitement : Comment fixer le juste prix de vente"
                           oninput="capeditUpdatePreview()">
                    <div class="capedit-hint">Titre H1 visible par le visiteur</div>
                </div>
                <div class="capedit-field">
                    <label class="capedit-label">Sous-titre / accroche</label>
                    <textarea name="sous_titre" class="capedit-textarea"
                              placeholder="Ex : Méthode complète pour ne pas brûler votre bien sur le marché ni laisser d'argent sur la table."
                              oninput="capeditUpdatePreview()"><?= htmlspecialchars($v['sous_titre']) ?></textarea>
                </div>
                <div class="capedit-field">
                    <label class="capedit-label">Description interne (SEO / admin)</label>
                    <textarea name="description" class="capedit-textarea" style="min-height:60px"
                              placeholder="Description courte pour l'admin et les meta-données"><?= htmlspecialchars($v['description']) ?></textarea>
                </div>
                <div class="capedit-field">
                    <label class="capedit-label">Texte du bouton CTA</label>
                    <input type="text" name="cta_text" class="capedit-input"
                           value="<?= htmlspecialchars($v['cta_text']) ?>"
                           placeholder="📥 Recevoir mon guide gratuitement"
                           oninput="capeditUpdatePreview()">
                </div>
            </div>
        </div>

        <!-- Éditeur HTML remerciement -->
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-heart"></i><h3>Éditeur HTML page de remerciement</h3></div>
            <div class="capedit-card-body">
                <div class="capedit-ai-actions">
                    <button type="button" class="capedit-btn capedit-btn-ai" onclick="capeditGenerateMerciSkeleton(event)">
                        <i class="fas fa-wand-magic-sparkles"></i> Générer HTML remerciement
                    </button>
                </div>
                <textarea name="html_merci" id="capeditHtmlMerci" class="capedit-textarea capedit-code"
                          placeholder="Collez ici votre code HTML de la page de remerciement..."><?= htmlspecialchars($v['html_merci']) ?></textarea>
                <div class="capedit-hint">
                    Cette page s'affiche après soumission. Si vide, la page de remerciement par défaut sera utilisée.
                </div>
            </div>
        </div>

        <!-- Éditeur HTML -->
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-code"></i><h3>Éditeur HTML (copier/coller)</h3></div>
            <div class="capedit-card-body">
                <div class="capedit-ai-actions">
                    <button type="button" class="capedit-btn capedit-btn-ai" onclick="capeditGenerateHtmlSkeleton(event)">
                        <i class="fas fa-wand-magic-sparkles"></i> Générer HTML capture (orange)
                    </button>
                </div>
                <textarea name="html_capture" id="capeditHtmlCapture" class="capedit-textarea capedit-code"
                          placeholder="Collez ici votre code HTML généré (Claude, autre IA, etc.)..."><?= htmlspecialchars($v['html_capture']) ?></textarea>
                <div class="capedit-hint">
                    Important : laissez <code>{{FORMULAIRE}}</code> dans votre HTML. Ce placeholder garde la connexion du formulaire au CRM, aux emails et à la liste de contacts.
                </div>
                <div id="capeditAiStatus" class="capedit-ai-status"></div>
                <div class="capedit-ai-help">
                    Si erreur token/API : <a href="#" onclick="capeditVerifyAiConfig();return false;">Vérifier la configuration IA</a> (clé, quota, crédit, compte).
                </div>
            </div>
        </div>

    </div>

    <!-- ══ COLONNE LATÉRALE ══ -->
    <div class="capedit-side">

        <!-- Statut -->
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-toggle-on"></i><h3>Publication</h3></div>
            <div class="capedit-card-body" style="padding:16px">
                <div class="capedit-status-row <?= $v['status'] === 'active' ? 'active-bg' : '' ?>" id="capeditStatusRow">
                    <div>
                        <div class="capedit-status-label <?= $v['status'] === 'active' ? 'on' : 'off' ?>" id="capeditStatusLabel">
                            <?= $v['status'] === 'active' ? '🟢 Active — page visible' : '🔴 Inactive — page masquée' ?>
                        </div>
                        <div style="font-size:.7rem;color:var(--text-3);margin-top:2px">
                            <?= $v['status'] === 'active' ? 'Les visiteurs peuvent accéder à cette page' : 'La page n\'est pas accessible aux visiteurs' ?>
                        </div>
                    </div>
                    <label class="capedit-toggle">
                        <input type="checkbox" name="status" value="active"
                               id="capeditStatusToggle"
                               <?= $v['status'] === 'active' ? 'checked' : '' ?>
                               onchange="capeditToggleStatus(this)">
                        <span class="capedit-slider"></span>
                    </label>
                </div>

                <?php if (!$isNew && $v['slug']): ?>
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
                    <div style="font-size:.72rem;color:var(--text-3);margin-bottom:6px;font-weight:600">URL de la page :</div>
                    <div style="display:flex;align-items:center;gap:6px">
                        <code style="font-size:.72rem;color:var(--accent);background:var(--surface-2);padding:4px 8px;border-radius:6px;flex:1;word-break:break-all">/capture/<?= htmlspecialchars($v['slug']) ?></code>
                        <button type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($capUrl) ?>');this.textContent='✅'" style="padding:5px 10px;border:1px solid var(--border);border-radius:6px;background:var(--surface);font-size:.7rem;cursor:pointer;white-space:nowrap">📋 Copier</button>
                    </div>
                    <a href="<?= htmlspecialchars($capUrl) ?>" target="_blank"
                       style="display:flex;align-items:center;gap:5px;margin-top:8px;font-size:.75rem;color:#3b82f6;text-decoration:none;font-weight:600">
                        <i class="fas fa-external-link-alt" style="font-size:.65rem"></i> Ouvrir la page de capture
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats -->
        <?php if (!$isNew): ?>
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-chart-bar"></i><h3>Statistiques</h3></div>
            <div class="capedit-card-body" style="padding:14px">
                <div class="capedit-stats-mini">
                    <div class="capedit-stat-mini">
                        <div class="num blue"><?= number_format($v['vues']) ?></div>
                        <div class="lbl">Vues</div>
                    </div>
                    <div class="capedit-stat-mini">
                        <div class="num green"><?= number_format($v['conversions']) ?></div>
                        <div class="lbl">Leads</div>
                    </div>
                    <div class="capedit-stat-mini">
                        <div class="num amber"><?= $v['taux'] > 0 ? number_format($v['taux'], 1) . '%' : '—' ?></div>
                        <div class="lbl">Conv.</div>
                    </div>
                </div>

                <!-- Derniers leads -->
                <?php
                $recentLeads = [];
                try {
                    $sl = $pdo->prepare("SELECT prenom, email, tel, created_at FROM capture_leads WHERE capture_id = ? ORDER BY created_at DESC LIMIT 5");
                    $sl->execute([$captureId]);
                    $recentLeads = $sl->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {}
                ?>
                <?php if (!empty($recentLeads)): ?>
                <div style="font-size:.7rem;font-weight:700;color:var(--text-2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Derniers leads :</div>
                <?php foreach ($recentLeads as $lead): ?>
                <div class="capedit-lead-row">
                    <div class="capedit-lead-avatar"><?= strtoupper(mb_substr($lead['prenom'] ?? '?', 0, 1)) ?></div>
                    <div class="capedit-lead-info">
                        <div class="capedit-lead-name"><?= htmlspecialchars($lead['prenom'] ?? '—') ?></div>
                        <div class="capedit-lead-email"><?= htmlspecialchars($lead['email']) ?></div>
                    </div>
                    <div class="capedit-lead-date"><?= date('d/m', strtotime($lead['created_at'])) ?></div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div style="text-align:center;padding:16px;font-size:.8rem;color:var(--text-3)">
                    <i class="fas fa-inbox" style="font-size:1.5rem;opacity:.2;display:block;margin-bottom:6px"></i>
                    Pas encore de leads
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- IA Claude -->
        <div class="capedit-card" style="border-color:#e9d5ff">
            <div class="capedit-card-hd" style="background:linear-gradient(135deg,#faf5ff,#f3e8ff);border-color:#e9d5ff">
                <i class="fas fa-robot" style="color:#7c3aed"></i>
                <h3 style="color:#7c3aed">IA <?= $aiProvider ?: 'Claude' ?> — Générer la page</h3>
            </div>
            <div class="capedit-card-body" style="padding:14px">
                <?php if ($aiAvailable && !$isNew): ?>
                <div style="font-size:.73rem;color:var(--text-2);margin-bottom:8px;line-height:1.5">
                    Décrivez votre page et l'IA génère le code HTML/CSS complet, adapté à votre identité.
                </div>
                <textarea id="capeditAiPrompt"
                    style="width:100%;box-sizing:border-box;padding:9px 12px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-size:.8rem;font-family:var(--font);resize:vertical;min-height:80px;margin-bottom:10px;transition:border-color .15s"
                    placeholder="Ex: Landing page pour un guide PDF sur la vente immobilière, hero accrocheur, 3 bullet points bénéfices, formulaire avec prénom + email…"></textarea>
                <button type="button" onclick="capeditGenerateAI()" id="capeditAiBtnGenerate"
                    style="width:100%;display:flex;align-items:center;justify-content:center;gap:9px;padding:12px 16px;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border:none;border-radius:var(--radius);font-size:.85rem;font-weight:700;cursor:pointer;transition:all .2s;font-family:var(--font);box-shadow:0 3px 10px rgba(124,58,237,.3)">
                    <i class="fas fa-magic"></i> Générer avec <?= $aiProvider ?: 'Claude' ?>
                </button>
                <div id="capeditAiStatus" style="display:none;margin-top:10px;text-align:center;font-size:.78rem;color:#7c3aed;font-weight:600">
                    <i class="fas fa-spinner fa-spin"></i> Génération en cours…
                </div>
                <div id="capeditAiResult" style="display:none;margin-top:12px">
                    <div id="capeditAiResultMsg" style="font-size:.75rem;padding:8px 12px;border-radius:8px;margin-bottom:8px"></div>
                    <a href="?page=builder-editor&context=capture&entity_id=<?= $captureId ?>"
                       id="capeditAiOpenBuilder"
                       style="display:none;align-items:center;justify-content:center;gap:7px;padding:10px 14px;background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff;border-radius:var(--radius);font-size:.82rem;font-weight:700;text-decoration:none;text-align:center">
                        <i class="fas fa-code"></i> Ouvrir dans l'éditeur
                    </a>
                </div>
                <?php elseif ($aiAvailable && $isNew): ?>
                <div style="text-align:center;padding:12px">
                    <i class="fas fa-robot" style="font-size:1.8rem;color:#e9d5ff;display:block;margin-bottom:8px"></i>
                    <div style="font-size:.78rem;color:var(--text-2)">Enregistrez d'abord la capture pour utiliser l'IA.</div>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:12px">
                    <i class="fas fa-robot" style="font-size:1.8rem;color:#e9d5ff;display:block;margin-bottom:8px"></i>
                    <div style="font-size:.78rem;color:var(--text-2);margin-bottom:10px">Configurez votre clé API pour utiliser l'IA</div>
                    <a href="?page=system/settings/ai"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#f3e8ff;color:#7c3aed;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none">
                        <i class="fas fa-cog"></i> Configurer l'IA
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="capedit-card">
            <div class="capedit-card-hd"><i class="fas fa-bolt"></i><h3>Actions rapides</h3></div>
            <div class="capedit-card-body" style="padding:14px;display:flex;flex-direction:column;gap:8px">
                <button type="submit" form="capeditForm" class="capedit-btn capedit-btn-save" style="justify-content:center">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <?php if (!$isNew && $v['slug']): ?>
                <a href="/capture/<?= htmlspecialchars($v['slug']) ?>" target="_blank"
                   class="capedit-btn capedit-btn-preview" style="justify-content:center">
                    <i class="fas fa-eye"></i> Voir la page publique
                </a>
                <?php endif; ?>
                <?php if (!$isNew): ?>
                <a href="?page=builder-editor&context=capture&entity_id=<?= $captureId ?>"
                   class="capedit-btn" style="justify-content:center;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff">
                    <i class="fas fa-code"></i> Éditeur Capture
                </a>
                <a href="?page=builder-editor&context=capture_thankyou&entity_id=<?= $captureId ?>"
                   class="capedit-btn" style="justify-content:center;background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff">
                    <i class="fas fa-heart"></i> Éditeur Remerciement
                </a>
                <?php endif; ?>
                <a href="?page=captures" class="capedit-btn capedit-btn-outline" style="justify-content:center;color:var(--text-2)">
                    <i class="fas fa-list"></i> Toutes les captures
                </a>
                <a href="?page=ressources" class="capedit-btn capedit-btn-outline" style="justify-content:center;color:#8b5cf6">
                    <i class="fas fa-book"></i> Retour aux Ressources
                </a>
            </div>
        </div>

    </div>

</div>
</form>

</div>

<script>
// ── Slug auto depuis titre ──
let slugManuallyEdited = <?= (!$isNew && $v['slug']) ? 'true' : 'false' ?>;

function capeditAutoSlug(titre) {
    if (slugManuallyEdited) return;
    const slug = titre
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 80);
    document.getElementById('capeditSlug').value = slug;
    document.getElementById('capeditSlugPreview').textContent = slug || '…';
}

document.getElementById('capeditSlug').addEventListener('input', function() {
    slugManuallyEdited = true;
    const v = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
    this.value = v;
    document.getElementById('capeditSlugPreview').textContent = v || '…';
    capeditBuildLeadSource();
});

function capeditBuildLeadSource() {
    const slug = document.getElementById('capeditSlug').value || 'draft';
    const fmt = document.getElementById('capeditOfferFormat')?.value || 'pdf';
    const obj = document.getElementById('capeditObjective')?.value || 'vendeur';
    const chn = document.getElementById('capeditChannel')?.value || 'organique';
    const sourceEl = document.getElementById('capeditLeadSource');
    const utmEl = document.getElementById('capeditUtmPreview');
    if (!sourceEl) return;
    if (!sourceEl.dataset.manual) sourceEl.value = `capture_${chn}_${fmt}_${obj}_${slug}`;
    if (utmEl) {
        const mediumMap = { mailing: 'email', google_ads: 'paid', facebook_ads: 'paid_social', organique: 'organic' };
        const utmCampaign = `capture-${chn}-${fmt}-${obj}-${slug}`.replace(/_+/g, '-');
        utmEl.value = `utm_source=${chn}&utm_medium=${mediumMap[chn] || 'organic'}&utm_campaign=${utmCampaign}`;
    }
}
const leadSourceEl = document.getElementById('capeditLeadSource');
if (leadSourceEl) {
    leadSourceEl.addEventListener('input', () => { leadSourceEl.dataset.manual = '1'; });
}
['capeditOfferFormat','capeditObjective','capeditSlug','capeditChannel'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('change', capeditBuildLeadSource);
    el.addEventListener('input', capeditBuildLeadSource);
});
capeditBuildLeadSource();

// ── Toggle statut ──
function capeditToggleStatus(checkbox) {
    const row   = document.getElementById('capeditStatusRow');
    const label = document.getElementById('capeditStatusLabel');
    if (checkbox.checked) {
        row.classList.add('active-bg');
        label.textContent   = '🟢 Active — page visible';
        label.className = 'capedit-status-label on';
    } else {
        row.classList.remove('active-bg');
        label.textContent   = '🔴 Inactive — page masquée';
        label.className = 'capedit-status-label off';
    }
}

const CAPEDIT_CSRF = <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>;

function capeditSetAiStatus(message, ok = false) {
    const box = document.getElementById('capeditAiStatus');
    if (!box) return;
    box.className = 'capedit-ai-status ' + (ok ? 'ok' : 'err');
    box.textContent = message;
}

function capeditEnsureFormPlaceholder(html) {
    if (html.includes('{{FORMULAIRE}}')) return html;
    return `${html}\n\n<section class="capture-form-wrap">{{FORMULAIRE}}</section>`;
}

function capeditBuildPrompt() {
    const titre = document.querySelector('input[name="titre"]')?.value?.trim() || '';
    const headline = document.querySelector('input[name="headline"]')?.value?.trim() || '';
    const sousTitre = document.querySelector('textarea[name="sous_titre"]')?.value?.trim() || '';
    const objectif = document.getElementById('capeditObjective')?.value || 'vendeur';
    const format = document.getElementById('capeditOfferFormat')?.value || 'pdf';
    return `Tu es un expert senior en conversion, UX/UI, copywriting immobilier, intégration HTML/CSS et cohérence visuelle de marque.
Attention :
La version actuelle paraît vide, déséquilibrée et inachevée.
Je ne veux pas d’un simple grand vide avec un bouton.
Je veux une vraie composition visuelle équilibrée, avec une colonne texte convaincante et une colonne formulaire bien présentée.
Le formulaire doit être intégré dans une carte élégante, immédiatement compréhensible, et la page doit sembler terminée, crédible et professionnelle.
Je veux que tu rédiges et structures une page de capture HTML simple, professionnelle, élégante et orientée conversion pour un site immobilier français.

CONTEXTE
- Cette page sert à proposer une ressource gratuite en échange des coordonnées du visiteur.
- Le but principal est la conversion, pas la décoration.
- Le rendu doit être sobre, premium, rassurant, lisible et crédible.
- Le design doit rester en harmonie avec le site existant.
- Avant de proposer le design, analyse le style visuel du site et son CSS existant pour t’aligner sur :
  - les couleurs principales
  - les espacements
  - la hiérarchie typographique
  - le style des boutons
  - les bordures, ombres, rayons
  - le ton visuel général
- Tu dois reprendre l’esprit du site, pas inventer une autre identité graphique.

OBJECTIF
Créer une page de capture optimisée marketing pour télécharger une ressource gratuite, avec :
- une promesse claire
- un titre fort
- un sous-titre utile
- quelques bénéfices concrets
- un formulaire visuellement bien intégré
- des éléments de réassurance
- une structure simple
- un HTML propre, prêt à coller dans mon éditeur
- le placeholder {{FORMULAIRE}} conservé exactement tel quel

RÈGLES MARKETING À RESPECTER
La page doit respecter les bonnes pratiques de landing page :
- une seule action principale : remplir le formulaire
- pas de navigation inutile dans le contenu
- pas de surcharge visuelle
- message immédiatement compréhensible en moins de 5 secondes
- titre orienté bénéfice utilisateur
- texte concret, pas vague
- bénéfices courts et faciles à scanner
- formulaire visible rapidement sans scroll sur desktop
- design rassurant, pas agressif
- CTA clair
- prévoir une bonne version mobile

RÈGLES SEO À RESPECTER
Même si l’objectif principal est la conversion, applique un minimum de bonnes pratiques SEO propres :
- une balise <title> pertinente
- une meta description concise
- une structure sémantique propre
- un seul vrai H1
- sous-sections en H2 si utile
- contenu lisible, naturel, sans bourrage de mots-clés
- vocabulaire immobilier local professionnel
- HTML propre et accessible

STYLE VISUEL ATTENDU
Je veux un design :
- simple
- pro
- moderne
- crédible
- aéré
- élégant
- cohérent avec le CSS du site existant
- sans effets gadgets
- sans style “template cheap”
- sans look SaaS trop froid
- sans visuel trop chargé

CONTRAINTES DE DESIGN
- Layout conseillé : section gauche = contenu, section droite = formulaire
- Sur mobile, tout doit passer en une seule colonne propre
- Utiliser des espacements généreux
- Contraste lisible
- Bouton CTA bien visible
- Carte formulaire propre et bien encadrée
- Ajouter un petit texte de réassurance sous le formulaire
- Ajouter si pertinent un badge du type “Guide gratuit” ou “Checklist gratuite”
- Ne pas dupliquer le formulaire plusieurs fois
- Éviter tout bloc inutile

STRUCTURE ATTENDUE
Propose une structure du type :
1. badge / micro-label
2. gros titre
3. sous-titre ou phrase d’accroche
4. courte liste de bénéfices
5. bloc formulaire avec {{FORMULAIRE}}
6. phrase de réassurance
7. éventuellement une mini section “ce que vous allez découvrir”

COPYWRITING
Rédige dans un français naturel, fluide, professionnel et humain.
Le ton doit inspirer confiance.
Évite :
- les promesses exagérées
- les phrases creuses
- le jargon marketing visible
- les formulations trop génériques
- les blocs de texte trop longs

IMPORTANT
- Garde exactement le placeholder {{FORMULAIRE}} sans le modifier
- Retourne un code HTML complet prêt à coller
- Intègre le CSS dans une balise <style> si nécessaire, mais reste léger
- Le HTML doit être propre, lisible, bien indenté
- Ne mets aucun script JavaScript inutile
- Adapte les couleurs et styles au site existant après avoir observé son CSS
- Si certaines infos visuelles du CSS ne sont pas disponibles, fais une proposition sobre cohérente avec un site immobilier premium français

CONTENU À UTILISER
Type de ressource : guide / checklist
Titre de la ressource : Préparer son bien avant la vente
Promesse : Checklist home staging, petits travaux à réaliser et pièges à éviter avant les premières visites
Cible : propriétaires vendeurs
Contexte métier : conseiller immobilier local

LIVRABLE ATTENDU
Je veux dans l’ordre :
1. une très courte analyse des choix UX/UI et conversion
2. la structure recommandée
3. le HTML final complet prêt à coller
4. un mini rappel des points forts conversion de la page

DONNÉES PAGE COURANTES (prioritaires si remplies)
- Titre admin : ${titre}
- Headline : ${headline}
- Accroche : ${sousTitre}
- Objectif CRM : ${objectif}
- Format de l'offre : ${format}`;
}

async function capeditGenerateHtmlSkeleton(ev) {
    const btn = ev?.currentTarget;
    const htmlField = document.getElementById('capeditHtmlCapture');
    if (!htmlField) return;
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération…'; }

    try {
        const pageTitle = document.querySelector('input[name="headline"]')?.value?.trim()
            || document.querySelector('input[name="titre"]')?.value?.trim()
            || 'Page de capture';

        const res = await fetch('/admin/api/ai/generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module: 'builder',
                action: 'generate',
                csrf_token: CAPEDIT_CSRF,
                page_type: 'capture',
                page_title: pageTitle,
                prompt: capeditBuildPrompt()
            })
        });
        const data = await res.json();
        if (!data.success || !data.template?.html) {
            throw new Error(data.error || 'Génération IA impossible.');
        }

        htmlField.value = capeditEnsureFormPlaceholder(data.template.html);

        const descEl = document.querySelector('textarea[name="description"]');
        if (descEl && !descEl.value.trim()) {
            const autoDesc = `Page de capture "${pageTitle}" générée automatiquement à partir du titre et de l'accroche.`;
            descEl.value = autoDesc;
        }
        capeditSetAiStatus('HTML généré avec succès. Vérifiez puis enregistrez la capture.', true);
    } catch (e) {
        const msg = String(e?.message || e);
        if (msg.toLowerCase().includes('csrf')) {
            capeditSetAiStatus('Token CSRF invalide. Rechargez la page admin puis réessayez.');
        } else if (msg.match(/429|quota|limit|credit|billing/i)) {
            capeditSetAiStatus('Limite API atteinte (quota/crédit). Vérifiez facturation ou passez sur OpenAI.');
        } else if (msg.match(/401|403|api key|clé/i)) {
            capeditSetAiStatus('Clé API invalide/non configurée. Vérifiez les clés IA (Claude/OpenAI).');
        } else {
            capeditSetAiStatus(`Erreur IA: ${msg} — si Claude échoue, le fallback OpenAI sera utilisé si configuré.`);
        }
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> Générer HTML capture (orange)'; }
    }
}

function capeditGenerateMerciSkeleton(ev) {
    const btn = ev?.currentTarget;
    const merciField = document.getElementById('capeditHtmlMerci');
    if (!merciField) return;
    if (btn) { btn.disabled = true; }
    merciField.value = `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Merci pour votre demande</title>
  <style>
    body{margin:0;font-family:Inter,Arial,sans-serif;background:#f8fafc;color:#0f172a}
    .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .card{max-width:680px;width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;box-shadow:0 8px 30px rgba(15,23,42,.08);text-align:center}
    h1{margin:0 0 12px;font-size:30px}
    p{margin:0 0 18px;color:#475569;line-height:1.6}
    .btn{display:inline-block;padding:12px 18px;border-radius:10px;background:#2563eb;color:#fff;text-decoration:none;font-weight:700}
  </style>
</head>
<body>
  <main class="wrap">
    <section class="card">
      <div style="font-size:48px;margin-bottom:10px">✅</div>
      <h1>Merci, votre demande est bien envoyée.</h1>
      <p>Nous vous avons bien enregistré. Vérifiez votre email, votre guide arrive dans quelques instants.</p>
      <a class="btn" href="/">Retour à l'accueil</a>
    </section>
  </main>
</body>
</html>`;
    if (btn) { btn.disabled = false; }
}

async function capeditVerifyAiConfig() {
    capeditSetAiStatus('Vérification IA en cours…', true);
    try {
        const res = await fetch('/admin/api/ai/generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module: 'builder',
                action: 'section',
                csrf_token: CAPEDIT_CSRF,
                section_type: 'cta',
                prompt: 'Test rapide de disponibilité API'
            })
        });
        const data = await res.json();
        if (data.success) {
            capeditSetAiStatus('API IA opérationnelle ✅', true);
            return;
        }
        throw new Error(data.error || 'Test API non concluant');
    } catch (e) {
        capeditSetAiStatus(`Vérification API échouée: ${String(e.message || e)}.`);
    }
}

// ── Flash auto-disparition ──
document.querySelectorAll('.capedit-flash').forEach(el => {
    setTimeout(() => { el.style.transition='opacity .4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 5000);
});

// ── IA Claude — Génération de la page de capture ──────────────
async function capeditGenerateAI() {
    const promptEl  = document.getElementById('capeditAiPrompt');
    const btnEl     = document.getElementById('capeditAiBtnGenerate');
    const statusEl  = document.getElementById('capeditAiStatus');
    const resultEl  = document.getElementById('capeditAiResult');
    const msgEl     = document.getElementById('capeditAiResultMsg');
    const openBtnEl = document.getElementById('capeditAiOpenBuilder');

    const userPrompt = (promptEl?.value || '').trim();
    const captureId  = <?= $captureId ?: 0 ?>;

    if (!captureId) {
        alert('Veuillez d\'abord enregistrer la capture avant de générer avec l\'IA.');
        return;
    }

    // Contexte automatique depuis les champs du formulaire
    const titre    = document.querySelector('[name="titre"]')?.value || '';
    const headline = document.querySelector('[name="headline"]')?.value || '';
    const sousTitre= document.querySelector('[name="sous_titre"]')?.value || '';
    const ctaTxt   = document.querySelector('[name="cta_text"]')?.value || '';
    const type     = document.querySelector('[name="type"]')?.value || '';
    const format   = document.querySelector('[name="offer_format"]')?.value || '';
    const objectif = document.querySelector('[name="objective"]')?.value || '';

    const autoCtx = [
        titre    ? `Titre: ${titre}` : '',
        headline ? `Headline: ${headline}` : '',
        sousTitre? `Sous-titre: ${sousTitre}` : '',
        ctaTxt   ? `CTA: ${ctaTxt}` : '',
        type     ? `Type: ${type}` : '',
        format   ? `Format: ${format}` : '',
        objectif ? `Objectif: ${objectif}` : '',
    ].filter(Boolean).join(' | ');

    const fullPrompt = (userPrompt || 'Génère une landing page de capture optimisée pour la conversion.')
        + (autoCtx ? `\n\nContexte de la capture:\n${autoCtx}` : '');

    // UI : chargement
    if (btnEl)    { btnEl.disabled = true; btnEl.style.opacity = '0.7'; }
    if (statusEl) statusEl.style.display = 'block';
    if (resultEl) resultEl.style.display = 'none';
    if (openBtnEl) openBtnEl.style.display = 'none';

    try {
        const res = await fetch('<?= $AI_ENDPOINT ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module:  'builder',
                action:  'generate',
                context: 'capture',
                id:      captureId,
                prompt:  fullPrompt
            })
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        if (!data.success && !data.html) {
            throw new Error(data.error || data.message || 'Erreur inconnue');
        }

        const html = data.html || '';
        const css  = data.css  || '';
        const js   = data.js   || '';

        if (!html) throw new Error('L\'IA n\'a pas retourné de code HTML.');

        // Sauvegarder le contenu généré via l'API builder
        const saveForm = new FormData();
        saveForm.append('context',      'capture');
        saveForm.append('entity_id',    captureId);
        saveForm.append('html_content', html);
        saveForm.append('custom_css',   css);
        saveForm.append('custom_js',    js);
        saveForm.append('status',       'keep');

        const saveRes  = await fetch('<?= $SAVE_ENDPOINT ?>', { method: 'POST', body: saveForm });
        const saveData = await saveRes.json();

        if (msgEl) {
            if (saveData.success) {
                msgEl.style.cssText = 'font-size:.75rem;padding:8px 12px;border-radius:8px;background:#d1fae5;color:#065f46;border:1px solid rgba(5,150,105,.15);margin-bottom:8px';
                msgEl.innerHTML = '<i class="fas fa-check-circle"></i> Page générée et sauvegardée !';
            } else {
                msgEl.style.cssText = 'font-size:.75rem;padding:8px 12px;border-radius:8px;background:#fef3c7;color:#92400e;border:1px solid rgba(245,158,11,.2);margin-bottom:8px';
                msgEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Code généré — ouvrez l\'éditeur pour appliquer.';
            }
        }

        if (openBtnEl) openBtnEl.style.display = 'flex';
        if (resultEl)  resultEl.style.display  = 'block';

    } catch (err) {
        if (msgEl) {
            msgEl.style.cssText = 'font-size:.75rem;padding:8px 12px;border-radius:8px;background:rgba(220,38,38,.06);color:#dc2626;border:1px solid rgba(220,38,38,.12);margin-bottom:8px';
            msgEl.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${err.message}`;
        }
        if (resultEl) resultEl.style.display = 'block';
    } finally {
        if (btnEl)    { btnEl.disabled = false; btnEl.style.opacity = '1'; }
        if (statusEl) statusEl.style.display = 'none';
    }
}
</script>
