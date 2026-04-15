<?php
$pageTitle = 'CRM Leads';
$pageDescription = 'Centralisez, priorisez et convertissez vos leads en actions concrètes.';

$view = isset($_GET['view']) ? strtolower((string)$_GET['view']) : 'cards';
$view = in_array($view, ['cards', 'pipeline'], true) ? $view : 'cards';

$pipeline = isset($_GET['pipeline']) ? strtolower((string)$_GET['pipeline']) : '';
$pipelines = LeadService::stageMatrix();
if ($pipeline !== '' && !isset($pipelines[$pipeline])) {
    $pipeline = '';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string)($_POST['action'] ?? '') === 'log_call') {
    verifyCsrf();

    $leadId = (int)($_POST['lead_id'] ?? 0);
    $note = trim((string)($_POST['call_note'] ?? ''));
    if ($note === '') {
        $note = 'Appel effectué depuis CRM Capturer.';
    }

    if ($leadId > 0 && LeadService::logInteraction($leadId, 'appel', $note)) {
        flash('success', 'Appel journalisé dans la boîte de log.');
    } else {
        flash('error', 'Impossible de journaliser cet appel.');
    }

    $redirect = '/admin?module=capturer&view=' . urlencode($view);
    if ($pipeline !== '') {
        $redirect .= '&pipeline=' . urlencode($pipeline);
    }
    header('Location: ' . $redirect);
    exit;
}

$leads = LeadService::list($pipeline ? ['pipeline' => $pipeline] : []);
$leadIds = array_values(array_filter(array_map(static fn(array $lead): int => (int)($lead['id'] ?? 0), $leads)));
$latestCallByLead = LeadService::latestInteractionsByLead($leadIds, 'appel');

$now = new DateTimeImmutable('now');
$todayStart = $now->setTime(0, 0, 0);
$todayEnd   = $now->setTime(23, 59, 59);

$closedStages      = ['converti', 'perdu', 'archive', 'inactif', 'traite'];
$rdvStages         = ['rdv_a_planifier', 'rdv_propose'];
$hotIntentKeywords = ['urgent', 'vite', 'rapid', 'achat', 'vendre', 'rdv', 'visite', 'estimer', 'financement'];

$stats = ['active' => 0, 'hot' => 0, 'rdv' => 0, 'cold' => 0, 'today_todo' => 0];
$rankedLeads = [];

foreach ($leads as $lead) {
    $stage    = strtolower((string)($lead['stage']    ?? 'nouveau'));
    $intent   = strtolower((string)($lead['intent']   ?? ''));
    $priority = strtolower((string)($lead['priority'] ?? 'normal'));

    $isClosed = in_array($stage, $closedStages, true);
    $isRdv    = in_array($stage, $rdvStages, true);
    $isHot    = $priority === 'haute' || $isRdv || $stage === 'a_traiter';

    foreach ($hotIntentKeywords as $kw) {
        if (str_contains($intent, $kw)) { $isHot = true; break; }
    }

    if (!$isClosed)              $stats['active']++;
    if ($isHot && !$isClosed)    $stats['hot']++;
    if ($isRdv)                  $stats['rdv']++;
    if ($isClosed || (!$isHot && !$isRdv)) $stats['cold']++;

    $createdAt = null;
    if (!empty($lead['created_at'])) {
        try { $createdAt = new DateTimeImmutable((string)$lead['created_at']); } catch (Throwable) {}
    }

    $needsActionToday = !$isClosed && (
        $isRdv || $stage === 'a_traiter'
        || ($createdAt instanceof DateTimeImmutable && $createdAt >= $todayStart && $createdAt <= $todayEnd)
    );
    if ($needsActionToday) $stats['today_todo']++;

    $score  = ($priority === 'haute' ? 30 : ($priority === 'normal' ? 10 : 5));
    $score += $isRdv ? 30 : 0;
    $score += $stage === 'a_traiter' ? 20 : 0;
    $score += $isHot ? 25 : 0;

    $lead['_is_hot']            = $isHot;
    $lead['_is_closed']         = $isClosed;
    $lead['_is_rdv']            = $isRdv;
    $lead['_needs_action_today']= $needsActionToday;
    $lead['_urgency']           = $score;
    $rankedLeads[] = $lead;
}

usort($rankedLeads, static fn($a, $b) => ($b['_urgency'] ?? 0) <=> ($a['_urgency'] ?? 0));
$priorityLeads = array_slice(
    array_values(array_filter($rankedLeads, static fn($l) => !empty($l['_needs_action_today']) || !empty($l['_is_hot']))),
    0, 5
);

$smartMessage = 'Pipeline stable — continuez le suivi.';
if ($stats['rdv'] > 0)          $smartMessage = $stats['rdv'] . ' RDV à planifier aujourd\'hui.';
elseif ($stats['today_todo'] > 0) $smartMessage = $stats['today_todo'] . ' leads à traiter aujourd\'hui.';
elseif ($stats['hot'] > 0)      $smartMessage = $stats['hot'] . ' leads chauds à contacter en priorité.';

$stageColor = static function (string $s): string {
    if (in_array($s, ['rdv_a_planifier','a_traiter','perdu'], true))                           return 'danger';
    if (in_array($s, ['a_qualifier','en_discussion','nurturing','rdv_propose','en_cours','a_relancer'], true)) return 'warning';
    return 'success';
};

$intentLabel = static function (array $lead): string {
    if (!empty($lead['_is_hot']))    return 'Forte';
    if (!empty($lead['_is_closed'])) return 'Faible';
    return 'Moyenne';
};

$stageCountByPipeline = [];
foreach ($pipelines as $source => $stages) {
    foreach ($stages as $stage) $stageCountByPipeline[$source][$stage] = 0;
}
foreach ($leads as $lead) {
    $src   = (string)($lead['pipeline'] ?? LeadService::SOURCE_AUTRE);
    $stage = (string)($lead['stage']    ?? 'nouveau');
    if (!isset($stageCountByPipeline[$src]))        $stageCountByPipeline[$src] = [];
    if (!isset($stageCountByPipeline[$src][$stage])) $stageCountByPipeline[$src][$stage] = 0;
    $stageCountByPipeline[$src][$stage]++;
}

function renderContent(): void
{
    global $view, $pipeline, $pipelines, $leads, $stats, $smartMessage,
           $priorityLeads, $stageColor, $intentLabel, $stageCountByPipeline, $latestCallByLead;
    ?>
    <style>
    /* ===== VARIABLES ===== */
    .crm {
        --c-navy:    #2c3e50;
        --c-blue:    #3498db;
        --c-blue-lt: #ebf5fb;
        --c-gold:    #c9a84c;
        --c-green:   #27ae60;
        --c-green-lt:#eafaf1;
        --c-orange:  #e67e22;
        --c-orange-lt:#fef5ec;
        --c-red:     #e74c3c;
        --c-red-lt:  #fdf0ee;
        --c-bg:      #f5f7fa;
        --c-white:   #ffffff;
        --c-border:  #e0e6ed;
        --c-text:    #2c3e50;
        --c-muted:   #7f8c8d;
        --c-shadow:  0 2px 10px rgba(0,0,0,.07);
        --c-shadow-md:0 6px 20px rgba(0,0,0,.1);
        --c-radius:  10px;
        --c-radius-lg:14px;
        font-family: 'Segoe UI', system-ui, sans-serif;
        color: var(--c-text);
    }

    /* ===== PAGE HEADER ===== */
    .crm-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .crm-header h1 {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--c-navy);
        margin: 0 0 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .crm-header h1 i { color: var(--c-blue); }
    .crm-header p { font-size: .875rem; color: var(--c-muted); margin: 0; }
    .crm-smart-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: var(--c-blue-lt);
        border: 1px solid rgba(52,152,219,.3);
        border-radius: 999px;
        color: #1a5276;
        font-weight: 700;
        font-size: .84rem;
        white-space: nowrap;
    }
    .crm-smart-badge i { color: var(--c-blue); }

    /* ===== KPI ROW ===== */
    .crm-kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }
    .crm-kpi {
        background: var(--c-white);
        border-radius: var(--c-radius-lg);
        box-shadow: var(--c-shadow);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-left: 4px solid var(--c-blue);
        transition: box-shadow .2s, transform .15s;
    }
    .crm-kpi:hover { box-shadow: var(--c-shadow-md); transform: translateY(-2px); }
    .crm-kpi.kpi-green  { border-left-color: var(--c-green); }
    .crm-kpi.kpi-red    { border-left-color: var(--c-red); }
    .crm-kpi.kpi-orange { border-left-color: var(--c-orange); }
    .crm-kpi.kpi-blue   { border-left-color: var(--c-blue); }
    .crm-kpi-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .kpi-green  .crm-kpi-icon { background: var(--c-green-lt);  color: var(--c-green); }
    .kpi-red    .crm-kpi-icon { background: var(--c-red-lt);    color: var(--c-red); }
    .kpi-orange .crm-kpi-icon { background: var(--c-orange-lt); color: var(--c-orange); }
    .kpi-blue   .crm-kpi-icon { background: var(--c-blue-lt);   color: var(--c-blue); }
    .crm-kpi-body {}
    .crm-kpi-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--c-navy);
        line-height: 1;
    }
    .crm-kpi-label {
        font-size: .78rem;
        color: var(--c-muted);
        margin-top: 3px;
    }

    /* ===== SECTION CARD ===== */
    .crm-section {
        background: var(--c-white);
        border-radius: var(--c-radius-lg);
        box-shadow: var(--c-shadow);
        border: 1px solid var(--c-border);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .crm-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid var(--c-border);
        background: #fafbfd;
    }
    .crm-section-head h2 {
        font-size: .95rem;
        font-weight: 700;
        color: var(--c-navy);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .crm-section-head h2 i { color: var(--c-blue); }
    .crm-section-body { padding: 18px 20px; }

    /* ===== TOOLBAR ===== */
    .crm-toolbar {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .crm-toolbar-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .crm-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        border: 1.5px solid var(--c-border);
        background: var(--c-white);
        color: var(--c-navy);
        font-weight: 600;
        font-size: .82rem;
        text-decoration: none;
        transition: all .15s;
    }
    .crm-pill:hover { border-color: var(--c-blue); color: var(--c-blue); background: var(--c-blue-lt); }
    .crm-pill.active { background: var(--c-blue); border-color: var(--c-blue); color: #fff; }

    /* ===== BADGES ===== */
    .crm-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 700;
    }
    .crm-badge.danger  { background: var(--c-red-lt);    color: var(--c-red); }
    .crm-badge.warning { background: var(--c-orange-lt); color: var(--c-orange); }
    .crm-badge.success { background: var(--c-green-lt);  color: var(--c-green); }

    /* ===== PRIORITY LIST ===== */
    .crm-priority-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }
    .crm-priority-card {
        background: var(--c-bg);
        border: 1px solid var(--c-border);
        border-radius: var(--c-radius);
        padding: 14px 16px;
        transition: box-shadow .15s;
    }
    .crm-priority-card:hover { box-shadow: var(--c-shadow-md); }
    .crm-priority-card strong {
        display: block;
        font-size: .95rem;
        color: var(--c-navy);
        margin-bottom: 4px;
    }
    .crm-meta {
        font-size: .8rem;
        color: var(--c-muted);
        line-height: 1.5;
    }
    .crm-call-log {
        margin-top: 8px;
        background: #f8fafc;
        border: 1px solid var(--c-border);
        border-radius: 10px;
        padding: 10px;
    }
    .crm-call-log-head {
        font-size: .74rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }
    .crm-call-log textarea {
        width: 100%;
        min-height: 56px;
        border: 1px solid #d6dee8;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 8px;
        font-size: .8rem;
        resize: vertical;
        font-family: inherit;
    }
    .crm-call-log .crm-btn {
        width: 100%;
        justify-content: center;
    }

    /* ===== LEAD CARDS ===== */
    .crm-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 14px;
    }
    .crm-lead-card {
        background: var(--c-white);
        border: 1px solid var(--c-border);
        border-radius: var(--c-radius-lg);
        box-shadow: var(--c-shadow);
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: box-shadow .15s, transform .15s;
    }
    .crm-lead-card:hover { box-shadow: var(--c-shadow-md); transform: translateY(-2px); }
    .crm-lead-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }
    .crm-lead-name {
        font-weight: 700;
        font-size: .95rem;
        color: var(--c-navy);
    }
    .crm-lead-source {
        font-size: .78rem;
        color: var(--c-muted);
        margin-top: 1px;
    }

    /* ===== ACTION BUTTONS ===== */
    .crm-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
    .crm-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 7px;
        background: var(--c-bg);
        border: 1px solid var(--c-border);
        color: var(--c-navy);
        font-size: .78rem;
        font-weight: 600;
        font-family: inherit;
        text-decoration: none;
        cursor: pointer;
        transition: all .15s;
    }
    .crm-btn:hover { background: var(--c-blue-lt); border-color: var(--c-blue); color: var(--c-blue); }
    .crm-btn.primary { background: var(--c-blue); border-color: var(--c-blue); color: #fff; }
    .crm-btn.primary:hover { opacity: .87; }

    /* ===== PIPELINE VIEW ===== */
    .crm-pipeline-wrap { display: grid; gap: 16px; }
    .crm-pipeline-section {
        background: var(--c-white);
        border: 1px solid var(--c-border);
        border-radius: var(--c-radius-lg);
        box-shadow: var(--c-shadow);
        overflow: hidden;
    }
    .crm-pipeline-title {
        padding: 12px 18px;
        background: #fafbfd;
        border-bottom: 1px solid var(--c-border);
        font-weight: 700;
        font-size: .9rem;
        color: var(--c-navy);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .crm-pipeline-track {
        display: flex;
        gap: 10px;
        padding: 14px 18px;
        overflow-x: auto;
    }
    .crm-stage-col {
        flex: 0 0 auto;
        width: 160px;
        background: var(--c-bg);
        border: 1px solid var(--c-border);
        border-radius: var(--c-radius);
        padding: 12px;
        text-align: center;
    }
    .crm-stage-col .crm-badge { margin-bottom: 8px; }
    .crm-stage-count {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--c-navy);
        display: block;
    }
    .crm-stage-label {
        font-size: .72rem;
        color: var(--c-muted);
        margin-top: 2px;
    }

    /* ===== EMPTY STATE ===== */
    .crm-empty {
        text-align: center;
        padding: 40px 20px;
        color: var(--c-muted);
        font-size: .9rem;
    }
    .crm-empty i { font-size: 2rem; margin-bottom: 10px; display: block; opacity: .3; }

    @media (max-width: 700px) {
        .crm-cards-grid { grid-template-columns: 1fr; }
        .crm-priority-list { grid-template-columns: 1fr; }
        .crm-kpi-row { grid-template-columns: 1fr 1fr; }
        .crm-toolbar { flex-direction: column; }
    }
    </style>

    <div class="crm">

        <!-- HEADER -->
        <div class="crm-header">
            <div>
                <h1><i class="fas fa-bolt"></i> CRM Leads — Cockpit</h1>
                <p><?= e((string)$stats['active']) ?> opportunités actives &bull; <?= e((string)$stats['today_todo']) ?> à traiter aujourd'hui</p>
            </div>
            <div class="crm-smart-badge">
                <i class="fas fa-circle-info"></i>
                <?= e($smartMessage) ?>
            </div>
        </div>

        <!-- KPI ROW -->
        <div class="crm-kpi-row">
            <div class="crm-kpi kpi-green">
                <div class="crm-kpi-icon"><i class="fas fa-users"></i></div>
                <div class="crm-kpi-body">
                    <div class="crm-kpi-value"><?= e((string)$stats['active']) ?></div>
                    <div class="crm-kpi-label">Opportunités actives</div>
                </div>
            </div>
            <div class="crm-kpi kpi-red">
                <div class="crm-kpi-icon"><i class="fas fa-fire"></i></div>
                <div class="crm-kpi-body">
                    <div class="crm-kpi-value"><?= e((string)$stats['hot']) ?></div>
                    <div class="crm-kpi-label">Leads chauds</div>
                </div>
            </div>
            <div class="crm-kpi kpi-orange">
                <div class="crm-kpi-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="crm-kpi-body">
                    <div class="crm-kpi-value"><?= e((string)$stats['rdv']) ?></div>
                    <div class="crm-kpi-label">RDV à planifier</div>
                </div>
            </div>
            <div class="crm-kpi kpi-blue">
                <div class="crm-kpi-icon"><i class="fas fa-snowflake"></i></div>
                <div class="crm-kpi-body">
                    <div class="crm-kpi-value"><?= e((string)$stats['cold']) ?></div>
                    <div class="crm-kpi-label">Leads froids / clos</div>
                </div>
            </div>
        </div>

        <!-- PRIORITY SECTION -->
        <?php if ($priorityLeads): ?>
        <div class="crm-section">
            <div class="crm-section-head">
                <h2><i class="fas fa-triangle-exclamation"></i> À faire maintenant</h2>
                <span class="crm-badge danger"><?= count($priorityLeads) ?> urgent<?= count($priorityLeads) > 1 ? 's' : '' ?></span>
            </div>
            <div class="crm-section-body">
                <div class="crm-priority-list">
                    <?php foreach ($priorityLeads as $lead):
                        $fullName = trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')) ?: 'Lead sans nom';
                        $stage = (string)($lead['stage'] ?? 'nouveau');
                        $tone = $stageColor($stage);
                        $leadId = (int)($lead['id'] ?? 0);
                        $latestCall = $leadId > 0 ? ($latestCallByLead[$leadId] ?? null) : null;
                        $composeLink = '/admin?module=messagerie&view=inbox&compose=1&to=' . urlencode((string)($lead['email'] ?? ''))
                                     . '&subject=' . urlencode('Suivi - ' . $fullName);
                    ?>
                        <div class="crm-priority-card">
                            <strong><?= e($fullName) ?></strong>
                            <div class="crm-meta">
                                <?= e(LeadService::sourceLabel((string)($lead['source_type'] ?? 'autre'))) ?>
                                &bull; <?= e(LeadService::stageLabel($stage)) ?>
                            </div>
                            <div class="crm-meta" style="margin-top:4px">
                                <span class="crm-badge <?= e($tone) ?>"><?= e(LeadService::stageLabel($stage)) ?></span>
                                &nbsp;Intention <strong><?= e($intentLabel($lead)) ?></strong>
                            </div>
                            <div class="crm-actions">
                                <?php if (!empty($lead['phone'])): ?>
                                    <a class="crm-btn primary" href="tel:<?= e((string)$lead['phone']) ?>">
                                        <i class="fas fa-phone"></i> Appeler
                                    </a>
                                <?php endif; ?>
                                <a class="crm-btn" href="<?= e($composeLink) ?>">
                                    <i class="fas fa-envelope"></i> Email (Messagerie)
                                </a>
                                <a class="crm-btn" href="#">
                                    <i class="fas fa-calendar"></i> RDV
                                </a>
                            </div>
                            <form class="crm-call-log" method="post">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="log_call">
                                <input type="hidden" name="lead_id" value="<?= $leadId ?>">
                                <div class="crm-call-log-head">
                                    <i class="fas fa-phone-volume"></i> Boîte log d'appel
                                    <?php if (is_array($latestCall) && !empty($latestCall['created_at'])): ?>
                                        — dernier: <?= e((string)$latestCall['created_at']) ?>
                                    <?php endif; ?>
                                </div>
                                <textarea name="call_note" placeholder="Ex: Appel sortant, relance demain 10h."><?=
                                    e((string)($latestCall['note'] ?? ''))
                                ?></textarea>
                                <button class="crm-btn" type="submit"><i class="fas fa-floppy-disk"></i> Enregistrer le log</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TOOLBAR -->
        <div class="crm-toolbar">
            <div class="crm-toolbar-group">
                <a class="crm-pill <?= $view === 'cards' ? 'active' : '' ?>"
                   href="?module=capturer&view=cards<?= $pipeline ? '&pipeline=' . urlencode($pipeline) : '' ?>">
                    <i class="fas fa-th-large"></i> Vue cards
                </a>
                <a class="crm-pill <?= $view === 'pipeline' ? 'active' : '' ?>"
                   href="?module=capturer&view=pipeline<?= $pipeline ? '&pipeline=' . urlencode($pipeline) : '' ?>">
                    <i class="fas fa-columns"></i> Pipeline
                </a>
            </div>
            <div class="crm-toolbar-group">
                <a class="crm-pill <?= $pipeline === '' ? 'active' : '' ?>"
                   href="?module=capturer&view=<?= urlencode($view) ?>">
                    Tous
                </a>
                <?php foreach (array_keys($pipelines) as $pipe): ?>
                    <a class="crm-pill <?= $pipeline === $pipe ? 'active' : '' ?>"
                       href="?module=capturer&view=<?= urlencode($view) ?>&pipeline=<?= urlencode($pipe) ?>">
                        <?= e(LeadService::sourceLabel($pipe)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CARDS VIEW -->
        <?php if ($view === 'cards'): ?>
            <?php if (!$leads): ?>
                <div class="crm-empty">
                    <i class="fas fa-inbox"></i>
                    Aucun lead capturé pour l'instant.
                </div>
            <?php else: ?>
            <div class="crm-cards-grid">
                <?php foreach ($leads as $lead):
                    $fullName = trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')) ?: 'Lead sans nom';
                    $stage    = (string)($lead['stage'] ?? 'nouveau');
                    $tone     = $stageColor($stage);
                    $leadId   = (int)($lead['id'] ?? 0);
                    $latestCall = $leadId > 0 ? ($latestCallByLead[$leadId] ?? null) : null;
                    $composeLink = '/admin?module=messagerie&view=inbox&compose=1&to=' . urlencode((string)($lead['email'] ?? ''))
                                 . '&subject=' . urlencode('Suivi - ' . $fullName);
                ?>
                    <div class="crm-lead-card">
                        <div class="crm-lead-head">
                            <div>
                                <div class="crm-lead-name"><?= e($fullName) ?></div>
                                <div class="crm-lead-source">
                                    <?= e(LeadService::sourceLabel((string)($lead['source_type'] ?? 'autre'))) ?>
                                </div>
                            </div>
                            <span class="crm-badge <?= e($tone) ?>"><?= e(LeadService::stageLabel($stage)) ?></span>
                        </div>

                        <?php if (!empty($lead['email']) || !empty($lead['phone'])): ?>
                        <div class="crm-meta">
                            <?php if (!empty($lead['email'])): ?>
                                <div><i class="fas fa-envelope" style="width:14px"></i> <?= e((string)$lead['email']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($lead['phone'])): ?>
                                <div><i class="fas fa-phone" style="width:14px"></i> <?= e((string)$lead['phone']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="crm-meta">
                            Intention : <strong style="color:#2c3e50"><?= e($intentLabel($lead)) ?></strong>
                            <?php if (!empty($lead['intent'])): ?>
                                &bull; <?= e((string)$lead['intent']) ?>
                            <?php endif; ?>
                        </div>

                        <div class="crm-actions">
                            <?php if (!empty($lead['phone'])): ?>
                                <a class="crm-btn primary" href="tel:<?= e((string)$lead['phone']) ?>">
                                    <i class="fas fa-phone"></i> Appeler
                                </a>
                            <?php endif; ?>
                            <a class="crm-btn" href="<?= e($composeLink) ?>" title="Envoyer depuis la messagerie">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <a class="crm-btn" href="#">
                                <i class="fas fa-calendar"></i> RDV
                            </a>
                        </div>
                        <form class="crm-call-log" method="post">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="log_call">
                            <input type="hidden" name="lead_id" value="<?= $leadId ?>">
                            <div class="crm-call-log-head">
                                <i class="fas fa-phone-volume"></i> Boîte log d'appel
                                <?php if (is_array($latestCall) && !empty($latestCall['created_at'])): ?>
                                    — dernier: <?= e((string)$latestCall['created_at']) ?>
                                <?php endif; ?>
                            </div>
                            <textarea name="call_note" placeholder="Ex: Appel entrant, proposition envoyée."><?=
                                e((string)($latestCall['note'] ?? ''))
                            ?></textarea>
                            <button class="crm-btn" type="submit"><i class="fas fa-floppy-disk"></i> Enregistrer le log</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <!-- PIPELINE VIEW -->
        <?php else: ?>
            <div class="crm-pipeline-wrap">
                <?php foreach ($pipelines as $source => $stages):
                    if ($pipeline !== '' && $pipeline !== $source) continue;
                ?>
                    <div class="crm-pipeline-section">
                        <div class="crm-pipeline-title">
                            <i class="fas fa-code-branch"></i>
                            <?= e(LeadService::sourceLabel($source)) ?>
                        </div>
                        <div class="crm-pipeline-track">
                            <?php foreach ($stages as $stage):
                                $tone  = $stageColor((string)$stage);
                                $count = (int)($stageCountByPipeline[$source][$stage] ?? 0);
                            ?>
                                <div class="crm-stage-col">
                                    <div><span class="crm-badge <?= e($tone) ?>"><?= e(LeadService::stageLabel((string)$stage)) ?></span></div>
                                    <span class="crm-stage-count"><?= $count ?></span>
                                    <div class="crm-stage-label">
                                        <?= $tone === 'danger' ? 'Urgent' : ($tone === 'warning' ? 'Suivi' : 'OK') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
}
