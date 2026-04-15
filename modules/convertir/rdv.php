<?php

$rdvNotice = null;
$rdvNoticeType = 'success';

$appointmentStatusLabels = [
    'demande' => 'Demande reçue',
    'confirm' => 'Confirmé',
    'reschedule' => 'À reprogrammer',
    'cancel' => 'Annulé',
];

$appointmentStatusClasses = [
    'demande' => 'status-demande',
    'confirm' => 'status-confirm',
    'reschedule' => 'status-reschedule',
    'cancel' => 'status-cancel',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verifyCsrf();

    $leadId = (int)($_POST['lead_id'] ?? 0);
    $operation = strtolower(trim((string)($_POST['operation'] ?? '')));
    $allowedOperations = ['confirm', 'cancel', 'reschedule'];
    if (!in_array($operation, $allowedOperations, true)) {
        $operation = 'confirm';
    }

    $scheduledDate = trim((string)($_POST['scheduled_date'] ?? ''));
    $scheduledTime = trim((string)($_POST['scheduled_time'] ?? ''));
    $comment = trim((string)($_POST['comment'] ?? ''));

    $scheduledAt = null;
    if (in_array($operation, ['confirm', 'reschedule'], true) && $scheduledDate !== '') {
        $scheduledAt = $scheduledDate . ' ' . ($scheduledTime !== '' ? $scheduledTime : '09:00');
    }

    $ok = LeadService::updateRdvStatus($leadId, $operation, $scheduledAt, $comment);
    $rdvNotice = $ok ? 'Mise à jour du rendez-vous enregistrée.' : 'Impossible de mettre à jour ce rendez-vous.';
    $rdvNoticeType = $ok ? 'success' : 'error';
}

$selectedStatusFilter = strtolower(trim((string)($_GET['status'] ?? 'all')));
$availableFilters = ['all', 'demande', 'confirm', 'reschedule', 'cancel'];
if (!in_array($selectedStatusFilter, $availableFilters, true)) {
    $selectedStatusFilter = 'all';
}

$rdvLeads = LeadService::list(['stage_like' => 'rdv']);

$calendarBuckets = [];
$stats = [
    'total' => 0,
    'today' => 0,
    'to_schedule' => 0,
    'confirm' => 0,
    'demande' => 0,
    'reschedule' => 0,
    'cancel' => 0,
    'due_48h' => 0,
];

$now = time();
$todayKey = date('Y-m-d', $now);

foreach ($rdvLeads as $lead) {
    $metadata = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];
    $appointmentAt = (string)($metadata['appointment_at'] ?? '');
    $status = strtolower((string)($metadata['appointment_status'] ?? 'demande'));
    if (!array_key_exists($status, $appointmentStatusLabels)) {
        $status = 'demande';
    }

    if ($selectedStatusFilter !== 'all' && $status !== $selectedStatusFilter) {
        continue;
    }

    $fallbackDate = (string)($lead['updated_at'] ?? $lead['created_at'] ?? '');
    $dateSource = $appointmentAt !== '' ? $appointmentAt : $fallbackDate;
    $timestamp = strtotime($dateSource);
    if ($timestamp === false) {
        $timestamp = strtotime((string)($lead['created_at'] ?? 'now')) ?: $now;
    }

    $lead['_appointment_status'] = $status;
    $lead['_appointment_at'] = $appointmentAt;
    $lead['_appointment_timestamp'] = $timestamp;

    $dayKey = date('Y-m-d', $timestamp);
    $calendarBuckets[$dayKey][] = $lead;

    $stats['total']++;
    $stats[$status]++;

    if ($appointmentAt === '') {
        $stats['to_schedule']++;
    }

    if ($dayKey === $todayKey) {
        $stats['today']++;
    }

    if ($appointmentAt !== '' && $timestamp >= $now && $timestamp <= ($now + (48 * 3600))) {
        $stats['due_48h']++;
    }
}

krsort($calendarBuckets);
foreach ($calendarBuckets as &$dayEntries) {
    usort($dayEntries, static function (array $a, array $b): int {
        return ((int)($a['_appointment_timestamp'] ?? 0)) <=> ((int)($b['_appointment_timestamp'] ?? 0));
    });
}
unset($dayEntries);

?>
<style>
    .rdv-toolbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
    .rdv-back{display:inline-flex;align-items:center;gap:.45rem;text-decoration:none;color:#334155;font-weight:700}
    .rdv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem}
    .rdv-day{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1rem;box-shadow:0 10px 30px rgba(15,23,42,.04)}
    .rdv-day h3{margin:0 0 .7rem;font-size:1rem}
    .rdv-card{border:1px solid #e2e8f0;border-radius:12px;padding:.75rem;background:#f8fafc;margin-bottom:.7rem}
    .rdv-meta{font-size:.82rem;color:#64748b}
    .rdv-badge{display:inline-block;padding:.2rem .55rem;border-radius:999px;font-weight:700;background:#e2e8f0;color:#1f2937;font-size:.72rem}
    .rdv-badge.status-demande{background:#dbeafe;color:#1d4ed8}
    .rdv-badge.status-confirm{background:#dcfce7;color:#166534}
    .rdv-badge.status-reschedule{background:#fef3c7;color:#92400e}
    .rdv-badge.status-cancel{background:#fee2e2;color:#991b1b}
    .rdv-actions{display:grid;gap:.4rem;margin-top:.6rem}
    .rdv-actions form{display:grid;grid-template-columns:1fr 1fr;gap:.4rem}
    .rdv-actions input,.rdv-actions select,.rdv-actions textarea,.rdv-actions button{border:1px solid #cbd5e1;border-radius:8px;padding:.45rem .55rem;font-size:.86rem}
    .rdv-actions textarea{grid-column:1/-1;min-height:54px;resize:vertical}
    .rdv-actions button{cursor:pointer;font-weight:700;background:#0f172a;color:#fff;border-color:#0f172a}
    .rdv-notice{padding:.75rem .9rem;border-radius:10px;margin-bottom:1rem}
    .rdv-notice.success{background:#ecfdf5;border:1px solid #86efac;color:#166534}
    .rdv-notice.error{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b}
    .rdv-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.7rem;margin-bottom:1rem}
    .rdv-kpi{border:1px solid #e2e8f0;background:#fff;border-radius:12px;padding:.7rem .8rem}
    .rdv-kpi strong{display:block;font-size:1.2rem;color:#0f172a}
    .rdv-kpi span{font-size:.8rem;color:#64748b}
    .rdv-filters{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem}
    .rdv-filter{display:inline-flex;padding:.38rem .65rem;border-radius:999px;border:1px solid #cbd5e1;text-decoration:none;color:#334155;font-weight:600;font-size:.82rem;background:#fff}
    .rdv-filter.active{background:#0f172a;border-color:#0f172a;color:#fff}
</style>

<div class="page-header">
    <h1><i class="fas fa-calendar-days page-icon"></i> Convertir <span class="page-title-accent">Prise de RDV</span></h1>
    <p>Visualisez les demandes de RDV issues des leads et traitez-les depuis un agenda opérationnel.</p>
</div>

<div class="rdv-toolbar">
    <a href="/admin?module=convertir" class="rdv-back"><i class="fas fa-arrow-left"></i> Retour aux sous-modules</a>
    <strong><?= $stats['total'] ?> lead(s) dans l'agenda</strong>
</div>

<div class="rdv-kpis">
    <div class="rdv-kpi"><strong><?= $stats['today'] ?></strong><span>RDV aujourd'hui</span></div>
    <div class="rdv-kpi"><strong><?= $stats['due_48h'] ?></strong><span>Échéances sous 48h</span></div>
    <div class="rdv-kpi"><strong><?= $stats['demande'] ?></strong><span>Demandes entrantes</span></div>
    <div class="rdv-kpi"><strong><?= $stats['confirm'] ?></strong><span>Confirmés</span></div>
    <div class="rdv-kpi"><strong><?= $stats['to_schedule'] ?></strong><span>Sans date planifiée</span></div>
</div>

<div class="rdv-filters">
    <?php foreach ($availableFilters as $filter): ?>
        <?php
        $label = $filter === 'all' ? 'Tous' : ($appointmentStatusLabels[$filter] ?? ucfirst($filter));
        $isActive = $selectedStatusFilter === $filter;
        ?>
        <a href="/admin?module=convertir&action=rdv&status=<?= e($filter) ?>" class="rdv-filter <?= $isActive ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($rdvNotice): ?>
    <div class="rdv-notice <?= e($rdvNoticeType) ?>"><?= e($rdvNotice) ?></div>
<?php endif; ?>

<?php if (!$calendarBuckets): ?>
    <div class="rdv-day">Aucun lead avec un statut RDV pour le moment.</div>
<?php else: ?>
    <section class="rdv-grid">
        <?php foreach ($calendarBuckets as $day => $entries): ?>
            <article class="rdv-day">
                <h3><?= e(date('l d F Y', strtotime($day))) ?> <span class="rdv-meta">(<?= count($entries) ?>)</span></h3>
                <?php foreach ($entries as $lead): ?>
                    <?php
                    $appointmentAt = (string)($lead['_appointment_at'] ?? '');
                    $status = (string)($lead['_appointment_status'] ?? 'demande');
                    $statusLabel = $appointmentStatusLabels[$status] ?? 'Demande reçue';
                    $statusClass = $appointmentStatusClasses[$status] ?? 'status-demande';
                    ?>
                    <div class="rdv-card">
                        <div style="display:flex;justify-content:space-between;gap:.6rem;align-items:flex-start;">
                            <strong><?= e(trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? ''))) ?: 'Lead sans nom' ?></strong>
                            <span class="rdv-badge <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
                        </div>
                        <div class="rdv-meta"><?= e((string)($lead['email'] ?? '')) ?> · <?= e((string)($lead['phone'] ?? '')) ?></div>
                        <div class="rdv-meta">Pipeline : <?= e(LeadService::sourceLabel((string)($lead['pipeline'] ?? 'autre'))) ?></div>
                        <div class="rdv-meta">Stage CRM : <?= e(LeadService::stageLabel((string)($lead['stage'] ?? 'rdv'))) ?></div>
                        <div class="rdv-meta">Créé le <?= e(date('d/m/Y H:i', strtotime((string)$lead['created_at']))) ?></div>
                        <div class="rdv-meta">RDV : <?= e($appointmentAt !== '' ? date('d/m/Y H:i', strtotime($appointmentAt)) : 'à planifier') ?></div>

                        <div class="rdv-actions">
                            <form method="post" action="/admin?module=convertir&action=rdv&status=<?= e($selectedStatusFilter) ?>">
                                <?= csrfField() ?>
                                <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
                                <select name="operation" required>
                                    <option value="confirm">Confirmer</option>
                                    <option value="cancel">Annuler</option>
                                    <option value="reschedule">Reprogrammer</option>
                                </select>
                                <input type="date" name="scheduled_date" value="<?= e(substr((string)$appointmentAt, 0, 10)) ?>">
                                <input type="time" name="scheduled_time" value="<?= e(strlen((string)$appointmentAt) >= 16 ? substr((string)$appointmentAt, 11, 5) : '') ?>">
                                <textarea name="comment" placeholder="Commentaire (optionnel)"></textarea>
                                <button type="submit"><i class="fas fa-check"></i> Mettre à jour</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
