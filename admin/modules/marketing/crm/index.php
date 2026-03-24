<?php
/**
 * MODULE CRM — Pipeline Kanban
 * /admin/modules/marketing/crm/index.php
 *
 * AJAX inline : ?page=crm&ajax=1
 * Actions : move_lead, get_lead, add_lead, update_lead, delete_lead
 */

// ── Connexion DB ──────────────────────────────────────────────────────────────
if (!isset($pdo) && !isset($db)) {
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException $e) {
        die('<div style="padding:20px;color:red">Erreur DB: '.$e->getMessage().'</div>');
    }
}
if (isset($db) && !isset($pdo)) $pdo = $db;

// ── AJAX handler ──────────────────────────────────────────────────────────────
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';

    switch ($action) {

        case 'move_lead':
            $leadId  = (int)($_POST['lead_id'] ?? 0);
            $stageId = (int)($_POST['stage_id'] ?? 0);
            if (!$leadId || !$stageId) { echo json_encode(['success'=>false,'error'=>'Paramètres manquants']); exit; }
            try {
                // Cherche la colonne : pipeline_stage_id OU status (selon ce qui existe)
                $cols = array_column($pdo->query("SHOW COLUMNS FROM leads")->fetchAll(), 'Field');
                if (in_array('pipeline_stage_id', $cols)) {
                    $pdo->prepare("UPDATE leads SET pipeline_stage_id=?, updated_at=NOW() WHERE id=?")->execute([$stageId, $leadId]);
                } else {
                    // Fallback : map stage_id → status
                    $stageMap = [1=>'new',2=>'contacted',3=>'qualified',4=>'proposal',5=>'negotiation',6=>'won',7=>'lost'];
                    $newStatus = $stageMap[$stageId] ?? 'new';
                    $pdo->prepare("UPDATE leads SET status=?, updated_at=NOW() WHERE id=?")->execute([$newStatus, $leadId]);
                }
                echo json_encode(['success'=>true]);
            } catch (Exception $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
            exit;

        case 'get_lead':
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            try {
                $row = $pdo->prepare("SELECT * FROM leads WHERE id=?");
                $row->execute([$id]);
                $lead = $row->fetch();
                echo json_encode(['success'=>(bool)$lead, 'lead'=>$lead ?: null]);
            } catch (Exception $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
            exit;

        case 'add_lead':
        case 'update_lead':
            $id = (int)($_POST['lead_id'] ?? $_POST['id'] ?? 0);
            try {
                $cols = array_column($pdo->query("SHOW COLUMNS FROM leads")->fetchAll(), 'Field');
                $data = [];
                $map = ['firstname','lastname','email','phone','notes','source','next_action','next_action_date'];
                foreach ($map as $f) {
                    if (in_array($f, $cols)) $data[$f] = trim($_POST[$f] ?? '') ?: null;
                }
                if (in_array('pipeline_stage_id', $cols)) $data['pipeline_stage_id'] = (int)($_POST['pipeline_stage_id'] ?? 1) ?: 1;
                if (in_array('budget_max', $cols))         $data['budget_max']         = (int)($_POST['estimated_value'] ?? 0) ?: null;
                if (in_array('status', $cols) && !in_array('pipeline_stage_id', $cols)) {
                    $data['status'] = $_POST['status'] ?? 'new';
                }

                if ($id) {
                    $sets = implode(',', array_map(fn($f)=>"`$f`=?", array_keys($data)));
                    $pdo->prepare("UPDATE leads SET $sets, updated_at=NOW() WHERE id=?")->execute([...array_values($data), $id]);
                    echo json_encode(['success'=>true,'id'=>$id,'message'=>'Lead mis à jour']);
                } else {
                    $c  = implode(',', array_map(fn($f)=>"`$f`", array_keys($data)));
                    $ph = implode(',', array_fill(0, count($data), '?'));
                    $pdo->prepare("INSERT INTO leads ($c) VALUES ($ph)")->execute(array_values($data));
                    echo json_encode(['success'=>true,'id'=>(int)$pdo->lastInsertId(),'message'=>'Lead créé']);
                }
            } catch (Exception $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
            exit;

        case 'delete_lead':
            $id = (int)($_POST['lead_id'] ?? 0);
            try {
                $pdo->prepare("DELETE FROM leads WHERE id=?")->execute([$id]);
                echo json_encode(['success'=>true]);
            } catch (Exception $e) { echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }
            exit;

        default:
            echo json_encode(['success'=>false,'error'=>'Action inconnue: '.$action]);
            exit;
    }
}

// ── Détecter la structure DB et construire les étapes du pipeline ─────────────
$dbCols    = [];
$stages    = [];
$leads     = [];
$stageMode = 'status'; // 'status' ou 'pipeline_stages'

try {
    $dbCols = array_column($pdo->query("SHOW COLUMNS FROM leads")->fetchAll(), 'Field');
} catch (Exception $e) {}

// Mode 1 : table pipeline_stages existe
try {
    $testStages = $pdo->query("SELECT COUNT(*) FROM pipeline_stages")->fetchColumn();
    if ($testStages !== false) {
        $stages    = $pdo->query("SELECT * FROM pipeline_stages ORDER BY position ASC")->fetchAll();
        $stageMode = 'pipeline_stages';
    }
} catch (Exception $e) {}

// Mode 2 : fallback sur colonne status de leads
if ($stageMode === 'status' || empty($stages)) {
    $stageMode = 'status';
    $stages = [
        ['id'=>1,'name'=>'Nouveau lead',       'color'=>'#6366f1','is_won'=>0,'is_lost'=>0,'status_key'=>'new'],
        ['id'=>2,'name'=>'Premier contact',    'color'=>'#0891b2','is_won'=>0,'is_lost'=>0,'status_key'=>'contacted'],
        ['id'=>3,'name'=>'Qualification',      'color'=>'#7c3aed','is_won'=>0,'is_lost'=>0,'status_key'=>'qualified'],
        ['id'=>4,'name'=>'Visite programmée',  'color'=>'#f59e0b','is_won'=>0,'is_lost'=>0,'status_key'=>'proposal'],
        ['id'=>5,'name'=>'Offre en cours',     'color'=>'#ec4899','is_won'=>0,'is_lost'=>0,'status_key'=>'negotiation'],
        ['id'=>6,'name'=>'Négociation',        'color'=>'#8b5cf6','is_won'=>0,'is_lost'=>0,'status_key'=>'lost'],
        ['id'=>7,'name'=>'Gagné',              'color'=>'#10b981','is_won'=>1,'is_lost'=>0,'status_key'=>'won'],
    ];
}

// Mode 3 : pipeline_stage_id dans leads mais table stages absente → même fallback
if ($stageMode === 'pipeline_stages' && empty($stages)) {
    $stageMode = 'status';
}

// ── Charger les leads ─────────────────────────────────────────────────────────
try {
    if ($stageMode === 'pipeline_stages') {
        $leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();
    } else {
        $leads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC")->fetchAll();
    }
} catch (Exception $e) { $leads = []; }

// ── Répartir les leads par étape ──────────────────────────────────────────────
$leadsByStage = [];
foreach ($stages as $s) $leadsByStage[$s['id']] = [];

foreach ($leads as $l) {
    if ($stageMode === 'pipeline_stages') {
        $sid = (int)($l['pipeline_stage_id'] ?? 1);
        if (!isset($leadsByStage[$sid])) $sid = $stages[0]['id'];
    } else {
        // Mapper status → stage id
        $statusKey = $l['status'] ?? 'new';
        $sid = 1;
        foreach ($stages as $s) {
            if (($s['status_key'] ?? '') === $statusKey) { $sid = $s['id']; break; }
        }
    }
    if (isset($leadsByStage[$sid])) $leadsByStage[$sid][] = $l;
}

// ── Stats ─────────────────────────────────────────────────────────────────────
$totalLeads = count($leads);
$totalValue = 0;
$wonLeads   = 0;
$wonValue   = 0;
$valueCol   = in_array('budget_max', $dbCols) ? 'budget_max' : (in_array('estimated_value', $dbCols) ? 'estimated_value' : null);

foreach ($leads as $l) {
    $v = $valueCol ? (int)($l[$valueCol] ?? 0) : 0;
    $totalValue += $v;
}
foreach ($stages as $s) {
    if ($s['is_won'] ?? 0) {
        $wonLeads += count($leadsByStage[$s['id']] ?? []);
        foreach ($leadsByStage[$s['id']] ?? [] as $l) {
            $wonValue += $valueCol ? (int)($l[$valueCol] ?? 0) : 0;
        }
    }
}

$formatEur = fn($n) => number_format($n, 0, ',', ' ') . ' €';
?>
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- CSS                                                                        -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<style>
/* ── Hero ─────────────────────────────────────────────────────────────────── */
.crm-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1a4d7a 100%);color:#fff;border-radius:14px;padding:28px 32px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
.crm-hero-title{font-size:1.5rem;font-weight:800;margin:0;display:flex;align-items:center;gap:12px;letter-spacing:-.02em}
.crm-hero-title i{opacity:.85}
.crm-hero-sub{margin:5px 0 0;opacity:.65;font-size:.85rem}
.crm-stats-row{display:flex;gap:8px;flex-wrap:wrap}
.crm-stat-card{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:12px 20px;min-width:110px;text-align:center;backdrop-filter:blur(4px)}
.crm-stat-val{font-size:1.5rem;font-weight:800;line-height:1;margin-bottom:2px}
.crm-stat-lbl{font-size:.68rem;opacity:.7;text-transform:uppercase;letter-spacing:.05em;font-weight:500}

/* ── Barre d'outils ──────────────────────────────────────────────────────── */
.crm-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap}
.crm-toolbar-left{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.crm-search-box{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #e2e8f0;border-radius:9px;padding:9px 14px;min-width:220px}
.crm-search-box i{color:#94a3b8;font-size:.8rem}
.crm-search-box input{border:none;outline:none;font-size:.83rem;color:#374151;background:none;width:100%}
.crm-sel{padding:9px 28px 9px 12px;border:1px solid #e2e8f0;border-radius:9px;font-size:.8rem;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%2394a3b8' d='M5 6L0 0h10z'/%3E%3C/svg%3E") no-repeat right 10px center;appearance:none;color:#374151;cursor:pointer}
.crm-sel:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.crm-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:9px;font-size:.83rem;font-weight:600;border:none;cursor:pointer;transition:.2s}
.crm-btn-primary{background:#6366f1;color:#fff}
.crm-btn-primary:hover{background:#4f46e5;transform:translateY(-1px)}
.crm-btn-secondary{background:#fff;color:#374151;border:1px solid #e2e8f0}
.crm-btn-secondary:hover{background:#f8fafc}

/* ── Kanban board ────────────────────────────────────────────────────────── */
.crm-board{display:flex;gap:14px;overflow-x:auto;padding-bottom:20px;min-height:calc(100vh - 380px)}
.crm-board::-webkit-scrollbar{height:6px}
.crm-board::-webkit-scrollbar-track{background:#f1f5f9;border-radius:3px}
.crm-board::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}

/* ── Colonnes ────────────────────────────────────────────────────────────── */
.crm-col{min-width:278px;max-width:278px;background:#f8fafc;border-radius:14px;display:flex;flex-direction:column;border:1px solid #e2e8f0;transition:.2s}
.crm-col-head{padding:14px 16px 11px;border-bottom:1px solid #e9eef5;border-radius:14px 14px 0 0;background:#fff;position:relative;overflow:hidden}
.crm-col-head::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--col-color,#6366f1)}
.crm-col-head-row{display:flex;align-items:center;justify-content:space-between}
.crm-col-title{display:flex;align-items:center;gap:7px;font-size:.8rem;font-weight:700;color:#1e293b}
.crm-col-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
.crm-col-badge{background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700}
.crm-col-amount{font-size:.72rem;color:#94a3b8;font-weight:600;margin-top:4px}

/* ── Zone de drop ────────────────────────────────────────────────────────── */
.crm-cards{flex:1;padding:10px;min-height:80px;transition:.2s}
.crm-cards.drag-over{background:rgba(99,102,241,.05);border-radius:0 0 14px 14px;outline:2px dashed #6366f1}

/* ── Cartes lead ─────────────────────────────────────────────────────────── */
.crm-card{background:#fff;border:1px solid #e9eef5;border-radius:10px;padding:13px 14px;margin-bottom:8px;cursor:grab;transition:all .2s;position:relative}
.crm-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.09);transform:translateY(-2px);border-color:#d4d9e8}
.crm-card.dragging{opacity:.35;transform:rotate(1.5deg) scale(1.02)}
.crm-card-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px;gap:6px}
.crm-card-name{font-size:.83rem;font-weight:700;color:#111827;line-height:1.3}
.crm-card-val{font-size:.68rem;font-weight:700;padding:3px 8px;border-radius:6px;white-space:nowrap;flex-shrink:0;background:#d1fae5;color:#065f46}
.crm-card-meta{display:flex;flex-direction:column;gap:3px;margin-bottom:9px}
.crm-card-meta-item{font-size:.72rem;color:#64748b;display:flex;align-items:center;gap:6px}
.crm-card-meta-item i{width:11px;font-size:.6rem;color:#94a3b8;flex-shrink:0}
.crm-card-footer{display:flex;align-items:center;justify-content:space-between;padding-top:9px;border-top:1px solid #f1f5f9;margin-top:4px}
.crm-card-date{font-size:.65rem;color:#94a3b8;display:flex;align-items:center;gap:4px}
.crm-card-actions{display:flex;gap:3px}
.crm-card-action{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:.65rem;transition:.15s;background:#f1f5f9;color:#64748b}
.crm-card-action:hover.edit{background:#e0e7ff;color:#4f46e5}
.crm-card-action:hover.del{background:#fee2e2;color:#dc2626}
.crm-card-tag{display:inline-flex;align-items:center;gap:4px;font-size:.63rem;padding:2px 8px;border-radius:4px;background:#f1f5f9;color:#64748b;font-weight:500;margin-bottom:6px}
.crm-card-next{display:flex;align-items:center;gap:5px;font-size:.68rem;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:3px 8px;margin-top:5px;font-weight:500}
.crm-card-next.overdue{color:#dc2626;background:#fff5f5;border-color:#fecaca}

/* ── Vide ────────────────────────────────────────────────────────────────── */
.crm-empty-col{padding:24px 16px;text-align:center;color:#cbd5e1}
.crm-empty-col i{font-size:1.4rem;margin-bottom:8px;display:block;opacity:.5}
.crm-empty-col p{font-size:.75rem;margin:0}

/* ── Toast ───────────────────────────────────────────────────────────────── */
.crm-toast{position:fixed;bottom:26px;right:26px;padding:13px 20px;border-radius:10px;color:#fff;font-size:.83rem;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.15);transition:all .3s;display:flex;align-items:center;gap:8px;pointer-events:none}
.crm-toast.hide{opacity:0;transform:translateY(8px)}

/* ── Modal ───────────────────────────────────────────────────────────────── */
.crm-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:2000;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px)}
.crm-overlay.open{display:flex}
.crm-modal{background:#fff;border-radius:16px;width:100%;max-width:600px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:crm-in .25s cubic-bezier(.16,1,.3,1)}
@keyframes crm-in{from{transform:scale(.95) translateY(12px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.crm-modal-head{padding:18px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between}
.crm-modal-head h3{font-size:.95rem;font-weight:700;color:#111827;margin:0;display:flex;align-items:center;gap:8px}
.crm-modal-head h3 i{color:#6366f1}
.crm-modal-close{width:30px;height:30px;border:none;background:#f1f5f9;border-radius:8px;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;color:#64748b;transition:.15s}
.crm-modal-close:hover{background:#e2e8f0;transform:rotate(90deg)}
.crm-modal-body{overflow-y:auto;padding:20px 22px;flex:1}
.crm-modal-foot{padding:14px 22px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end}
.crm-form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.crm-form-group{display:flex;flex-direction:column;margin-bottom:12px}
.crm-form-group label{font-size:.77rem;font-weight:600;color:#374151;margin-bottom:5px}
.crm-input{padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.83rem;color:#374151;outline:none;transition:.2s;width:100%;box-sizing:border-box;font-family:inherit}
.crm-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.crm-form-section{font-size:.8rem;font-weight:700;color:#1e293b;padding-bottom:7px;border-bottom:2px solid #f1f5f9;margin:16px 0 12px;display:flex;align-items:center;gap:6px}
.crm-form-section i{color:#6366f1}
.crm-form-section:first-child{margin-top:0}
</style>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- HERO + STATS                                                               -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="crm-hero">
    <div>
        <h1 class="crm-hero-title"><i class="fas fa-columns"></i> Pipeline CRM</h1>
        <p class="crm-hero-sub">Gérez vos leads par étape de conversion — glissez-déposez entre colonnes</p>
    </div>
    <div class="crm-stats-row">
        <div class="crm-stat-card">
            <div class="crm-stat-val"><?= $totalLeads ?></div>
            <div class="crm-stat-lbl">Leads</div>
        </div>
        <div class="crm-stat-card">
            <div class="crm-stat-val"><?= $totalValue > 0 ? $formatEur($totalValue) : '—' ?></div>
            <div class="crm-stat-lbl">Pipeline</div>
        </div>
        <div class="crm-stat-card">
            <div class="crm-stat-val"><?= $wonLeads ?></div>
            <div class="crm-stat-lbl">Gagnés</div>
        </div>
        <div class="crm-stat-card">
            <div class="crm-stat-val"><?= $wonValue > 0 ? $formatEur($wonValue) : '0 €' ?></div>
            <div class="crm-stat-lbl">CA Gagné</div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- BARRE OUTILS                                                               -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="crm-toolbar">
    <div class="crm-toolbar-left">
        <div class="crm-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="crmSearch" placeholder="Rechercher un lead..." oninput="crmFilter()">
        </div>
        <select class="crm-sel" id="crmSourceFilter" onchange="crmFilter()">
            <option value="">Toutes sources</option>
            <option value="site_web">Site web</option>
            <option value="gmb">GMB</option>
            <option value="pub_facebook">Facebook</option>
            <option value="pub_google">Google</option>
            <option value="recommandation">Recommandation</option>
            <option value="telephone">Téléphone</option>
            <option value="manuel">Manuel</option>
            <option value="Manuel">Manuel</option>
        </select>
    </div>
    <button class="crm-btn crm-btn-primary" onclick="openLeadModal()">
        <i class="fas fa-plus"></i> Nouveau lead
    </button>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- BOARD KANBAN                                                               -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="crm-board" id="crmBoard">
<?php foreach ($stages as $stage):
    $sLeads  = $leadsByStage[$stage['id']] ?? [];
    $sValue  = 0;
    foreach ($sLeads as $l) $sValue += $valueCol ? (int)($l[$valueCol] ?? 0) : 0;
    $color   = htmlspecialchars($stage['color'] ?? '#6366f1');
?>
<div class="crm-col" data-stage-id="<?= $stage['id'] ?>" data-stage-name="<?= htmlspecialchars($stage['name']) ?>" data-stage-status="<?= htmlspecialchars($stage['status_key'] ?? '') ?>">

    <!-- Tête de colonne -->
    <div class="crm-col-head" style="--col-color:<?= $color ?>">
        <div class="crm-col-head-row">
            <div class="crm-col-title">
                <span class="crm-col-dot" style="background:<?= $color ?>"></span>
                <?= htmlspecialchars($stage['name']) ?>
                <span class="crm-col-badge" id="badge-<?= $stage['id'] ?>"><?= count($sLeads) ?></span>
            </div>
            <span id="amount-<?= $stage['id'] ?>" style="font-size:.72rem;color:#94a3b8;font-weight:600"><?= $sValue > 0 ? $formatEur($sValue) : '' ?></span>
        </div>
    </div>

    <!-- Cartes -->
    <div class="crm-cards"
         data-stage-id="<?= $stage['id'] ?>"
         ondragover="crmDragOver(event)"
         ondragleave="crmDragLeave(event)"
         ondrop="crmDrop(event)">

        <?php if (empty($sLeads)): ?>
        <div class="crm-empty-col" id="empty-<?= $stage['id'] ?>">
            <i class="fas fa-inbox"></i>
            <p>Aucun lead</p>
        </div>
        <?php else: ?>
        <?php foreach ($sLeads as $lead):
            $name     = trim(($lead['firstname'] ?? '') . ' ' . ($lead['lastname'] ?? ''));
            $val      = $valueCol ? (int)($lead[$valueCol] ?? 0) : 0;
            $overdue  = !empty($lead['next_action_date']) && strtotime($lead['next_action_date']) < strtotime('today');
            $src      = $lead['source'] ?? '';
            $srcLabels = ['site_web'=>'Site web','telephone'=>'Téléphone','recommandation'=>'Recommandation',
                          'salon'=>'Salon','pub_facebook'=>'Facebook','pub_google'=>'Google',
                          'flyer'=>'Flyer','boitage'=>'Boîtage','gmb'=>'GMB','autre'=>'Autre',
                          'Manuel'=>'Manuel','manuel'=>'Manuel'];
            $srcLabel = $srcLabels[$src] ?? $src;
        ?>
        <div class="crm-card"
             draggable="true"
             data-lead-id="<?= $lead['id'] ?>"
             data-name="<?= htmlspecialchars(mb_strtolower($name)) ?>"
             data-source="<?= htmlspecialchars($src) ?>"
             data-value="<?= $val ?>"
             ondragstart="crmDragStart(event)"
             ondragend="crmDragEnd(event)">

            <div class="crm-card-top">
                <div class="crm-card-name"><?= htmlspecialchars($name ?: '—') ?></div>
                <?php if ($val > 0): ?>
                <div class="crm-card-val"><?= $formatEur($val) ?></div>
                <?php endif; ?>
            </div>

            <div class="crm-card-meta">
                <?php if ($lead['email'] ?? ''): ?>
                <div class="crm-card-meta-item"><i class="fas fa-envelope"></i><?= htmlspecialchars($lead['email']) ?></div>
                <?php endif; ?>
                <?php if ($lead['phone'] ?? ''): ?>
                <div class="crm-card-meta-item"><i class="fas fa-phone"></i><?= htmlspecialchars($lead['phone']) ?></div>
                <?php endif; ?>
            </div>

            <?php if ($srcLabel): ?>
            <span class="crm-card-tag"><i class="fas fa-tag"></i> <?= htmlspecialchars($srcLabel) ?></span>
            <?php endif; ?>

            <?php if ($lead['next_action'] ?? ''): ?>
            <div class="crm-card-next <?= $overdue ? 'overdue' : '' ?>">
                <i class="fas fa-<?= $overdue ? 'exclamation-circle' : 'clock' ?>"></i>
                <?= htmlspecialchars($lead['next_action']) ?>
                <?php if ($lead['next_action_date'] ?? ''): ?>
                &nbsp;— <?= date('d/m', strtotime($lead['next_action_date'])) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="crm-card-footer">
                <span class="crm-card-date">
                    <i class="fas fa-calendar-alt"></i>
                    <?= date('d/m/Y', strtotime($lead['created_at'])) ?>
                </span>
                <div class="crm-card-actions">
                    <button class="crm-card-action edit" onclick="openLeadModal(<?= $lead['id'] ?>)" title="Modifier"><i class="fas fa-pen"></i></button>
                    <button class="crm-card-action del" onclick="crmDeleteLead(<?= $lead['id'] ?>)" title="Supprimer"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL LEAD                                                                 -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="crm-overlay" id="crmModal">
    <div class="crm-modal">
        <div class="crm-modal-head">
            <h3><i class="fas fa-user-plus"></i> <span id="crmModalTitle">Nouveau lead</span></h3>
            <button class="crm-modal-close" onclick="closeLeadModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="crm-modal-body">
            <input type="hidden" id="crmLeadId">

            <div class="crm-form-section"><i class="fas fa-user"></i> Informations</div>
            <div class="crm-form-row">
                <div class="crm-form-group"><label>Prénom *</label><input type="text" class="crm-input" id="crmFirstname"></div>
                <div class="crm-form-group"><label>Nom *</label><input type="text" class="crm-input" id="crmLastname"></div>
            </div>
            <div class="crm-form-row">
                <div class="crm-form-group"><label>Email</label><input type="email" class="crm-input" id="crmEmail"></div>
                <div class="crm-form-group"><label>Téléphone</label><input type="tel" class="crm-input" id="crmPhone"></div>
            </div>

            <div class="crm-form-section"><i class="fas fa-euro-sign"></i> Projet</div>
            <div class="crm-form-row">
                <div class="crm-form-group"><label>Valeur estimée (€)</label><input type="number" class="crm-input" id="crmValue" min="0" step="1000" placeholder="0"></div>
                <div class="crm-form-group"><label>Étape pipeline</label>
                    <select class="crm-input" id="crmStage">
                        <?php foreach ($stages as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="crm-form-row">
                <div class="crm-form-group"><label>Source</label>
                    <select class="crm-input" id="crmSource">
                        <option value="manuel">Manuel</option>
                        <option value="site_web">Site web</option>
                        <option value="gmb">GMB</option>
                        <option value="pub_facebook">Facebook</option>
                        <option value="pub_google">Google</option>
                        <option value="telephone">Téléphone</option>
                        <option value="recommandation">Recommandation</option>
                        <option value="salon">Salon/Événement</option>
                        <option value="flyer">Flyer</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="crm-form-group"><label>Ville</label><input type="text" class="crm-input" id="crmCity" placeholder="Bordeaux"></div>
            </div>

            <div class="crm-form-section"><i class="fas fa-tasks"></i> Suivi</div>
            <div class="crm-form-row">
                <div class="crm-form-group"><label>Prochaine action</label><input type="text" class="crm-input" id="crmNextAction" placeholder="Ex: Appeler pour RDV"></div>
                <div class="crm-form-group"><label>Date</label><input type="date" class="crm-input" id="crmNextDate"></div>
            </div>
            <div class="crm-form-group"><label>Notes</label><textarea class="crm-input" id="crmNotes" rows="3" style="resize:vertical"></textarea></div>
        </div>
        <div class="crm-modal-foot">
            <button class="crm-btn crm-btn-secondary" onclick="closeLeadModal()">Annuler</button>
            <button class="crm-btn crm-btn-primary" onclick="saveLeadModal()"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="crm-toast hide" id="crmToast"></div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT                                                                 -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<script>
const CRM_API = '?page=crm&ajax=1';
let crmDragged = null;

// ── API helper ────────────────────────────────────────────────────────────────
async function crmApi(data) {
    try {
        const fd = new FormData();
        for (const [k, v] of Object.entries(data)) if (v !== null && v !== undefined) fd.append(k, v);
        const r = await fetch(CRM_API, { method: 'POST', body: fd });
        return await r.json();
    } catch (e) { return { success: false, error: e.message }; }
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function crmToast(msg, type = 'success') {
    const t = document.getElementById('crmToast');
    const bg = { success: '#10b981', error: '#ef4444', info: '#6366f1', warning: '#f59e0b' };
    const ic = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle', warning: 'exclamation-triangle' };
    t.style.background = bg[type] || bg.info;
    t.innerHTML = `<i class="fas fa-${ic[type] || 'info-circle'}"></i> ${msg}`;
    t.classList.remove('hide');
    clearTimeout(t._t);
    t._t = setTimeout(() => { t.classList.add('hide'); }, 2800);
}

// ── Drag & Drop ───────────────────────────────────────────────────────────────
function crmDragStart(e) {
    crmDragged = e.currentTarget;
    e.currentTarget.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.leadId);
}

function crmDragEnd(e) {
    e.currentTarget.classList.remove('dragging');
    document.querySelectorAll('.crm-cards').forEach(c => c.classList.remove('drag-over'));
    crmDragged = null;
}

function crmDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    e.currentTarget.classList.add('drag-over');
}

function crmDragLeave(e) {
    // Vérifier qu'on quitte vraiment la zone (pas un enfant)
    if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('drag-over');
    }
}

async function crmDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');

    const leadId   = e.dataTransfer.getData('text/plain');
    const newStageId = e.currentTarget.dataset.stageId;

    if (!crmDragged || !leadId || !newStageId) return;

    // Déplacer la carte dans le DOM immédiatement (UX)
    const emptyEl = e.currentTarget.querySelector('.crm-empty-col');
    if (emptyEl) emptyEl.remove();
    e.currentTarget.appendChild(crmDragged);
    updateBoardCounts();

    // Persister en DB
    const res = await crmApi({ action: 'move_lead', lead_id: leadId, stage_id: newStageId });
    if (res.success) {
        crmToast('Lead déplacé ✓');
    } else {
        crmToast(res.error || 'Erreur sauvegarde', 'error');
    }
}

// ── Compter cartes dans chaque colonne ────────────────────────────────────────
function updateBoardCounts() {
    document.querySelectorAll('.crm-col').forEach(col => {
        const stageId = col.dataset.stageId;
        const cards   = col.querySelectorAll('.crm-card:not([style*="display: none"])');
        const badge   = document.getElementById('badge-' + stageId);
        const amt     = document.getElementById('amount-' + stageId);
        if (badge) badge.textContent = cards.length;
        // Recalcul montant
        let total = 0;
        cards.forEach(c => total += parseFloat(c.dataset.value) || 0);
        if (amt) amt.textContent = total > 0 ? new Intl.NumberFormat('fr-FR').format(total) + ' €' : '';
    });
}

// ── Filtre ────────────────────────────────────────────────────────────────────
function crmFilter() {
    const q   = document.getElementById('crmSearch').value.toLowerCase().trim();
    const src = document.getElementById('crmSourceFilter').value;
    document.querySelectorAll('.crm-card').forEach(card => {
        const nameMatch   = !q   || (card.dataset.name || '').includes(q);
        const sourceMatch = !src || (card.dataset.source || '') === src;
        card.style.display = (nameMatch && sourceMatch) ? '' : 'none';
    });
    updateBoardCounts();
}

// ── Modal Lead ────────────────────────────────────────────────────────────────
async function openLeadModal(id) {
    document.getElementById('crmLeadId').value    = id || '';
    document.getElementById('crmModalTitle').textContent = id ? 'Modifier le lead' : 'Nouveau lead';
    // Reset form
    ['crmFirstname','crmLastname','crmEmail','crmPhone','crmValue','crmCity','crmNextAction','crmNextDate','crmNotes'].forEach(f => {
        const el = document.getElementById(f);
        if (el) el.value = '';
    });

    if (id) {
        const data = await crmApi({ action: 'get_lead', id });
        if (!data.success || !data.lead) { crmToast('Lead introuvable', 'error'); return; }
        const l = data.lead;
        document.getElementById('crmFirstname').value  = l.firstname    || '';
        document.getElementById('crmLastname').value   = l.lastname     || '';
        document.getElementById('crmEmail').value      = l.email        || '';
        document.getElementById('crmPhone').value      = l.phone        || '';
        document.getElementById('crmCity').value       = l.city         || '';
        document.getElementById('crmNextAction').value = l.next_action  || '';
        document.getElementById('crmNextDate').value   = l.next_action_date || '';
        document.getElementById('crmNotes').value      = l.notes        || '';
        document.getElementById('crmSource').value     = l.source       || 'manuel';
        // Valeur
        const valField = l.budget_max ?? l.estimated_value ?? 0;
        document.getElementById('crmValue').value = valField || '';
        // Étape courante
        const stageEl = document.getElementById('crmStage');
        if (stageEl && l.pipeline_stage_id) stageEl.value = l.pipeline_stage_id;
    }

    document.getElementById('crmModal').classList.add('open');
}

function closeLeadModal() {
    document.getElementById('crmModal').classList.remove('open');
}

async function saveLeadModal() {
    const id = document.getElementById('crmLeadId').value;
    const fn = document.getElementById('crmFirstname').value.trim();
    const ln = document.getElementById('crmLastname').value.trim();
    if (!fn || !ln) { crmToast('Prénom et nom requis', 'warning'); return; }

    const payload = {
        action:            id ? 'update_lead' : 'add_lead',
        lead_id:           id || undefined,
        id:                id || undefined,
        firstname:         fn,
        lastname:          ln,
        email:             document.getElementById('crmEmail').value,
        phone:             document.getElementById('crmPhone').value,
        estimated_value:   document.getElementById('crmValue').value,
        source:            document.getElementById('crmSource').value,
        next_action:       document.getElementById('crmNextAction').value,
        next_action_date:  document.getElementById('crmNextDate').value,
        notes:             document.getElementById('crmNotes').value,
        pipeline_stage_id: document.getElementById('crmStage').value,
    };

    const res = await crmApi(payload);
    if (res.success) {
        crmToast(id ? 'Lead mis à jour ✓' : 'Lead créé ✓');
        closeLeadModal();
        setTimeout(() => location.reload(), 700);
    } else {
        crmToast(res.error || 'Erreur', 'error');
    }
}

async function crmDeleteLead(id) {
    if (!confirm('Supprimer ce lead ?')) return;
    const res = await crmApi({ action: 'delete_lead', lead_id: id });
    if (res.success) {
        crmToast('Lead supprimé');
        document.querySelector(`.crm-card[data-lead-id="${id}"]`)?.remove();
        updateBoardCounts();
    } else {
        crmToast(res.error || 'Erreur', 'error');
    }
}

// ── Fermeture clavier / overlay ───────────────────────────────────────────────
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLeadModal(); });
document.getElementById('crmModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeLeadModal(); });
</script>