<?php

declare(strict_types=1);

$user = Auth::user() ?? [];
$userId = (int) ($user['id'] ?? 0);
$recipientDefault = (string) setting('advisor_email', (string) ($user['email'] ?? APP_EMAIL), $userId);

$monthInput = (string) ($_POST['month'] ?? $_GET['month'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
    $monthInput = date('Y-m');
}

$service = new MonthlyReportService(db());
$report = null;
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientEmail = trim((string) ($_POST['recipient_email'] ?? $recipientDefault));

    try {
        $monthDate = new DateTimeImmutable($monthInput . '-01 12:00:00');

        if (isset($_POST['preview_report'])) {
            $report = $service->generateAndPersist($userId, $monthDate);
            $message = 'Rapport généré avec succès.';
        }

        if (isset($_POST['send_report'])) {
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Adresse email destinataire invalide.');
            }
            $report = $service->sendMonthlyReport($userId, $monthDate, $recipientEmail);
            $message = !empty($report['email_sent'])
                ? 'Rapport envoyé par email avec succès.'
                : 'Rapport généré mais envoi email échoué (voir logs serveur).';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$current = 'rapport-mensuel';
require __DIR__ . '/_subnav.php';
?>

<div class="page-header" style="margin-bottom:24px;">
    <h1><i class="fas fa-file-chart-line page-icon"></i> Rapport mensuel</h1>
    <p>Générez un rapport HTML/PDF des performances du mois et envoyez-le automatiquement par email.</p>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;max-width:920px;">
    <div style="margin-bottom:12px;color:#6b7280;">
        <a href="/admin?module=optimiser">Optimiser</a> › Rapport mensuel
    </div>

    <?php if ($message): ?>
        <div style="padding:12px;border-radius:8px;background:#ecfdf5;color:#065f46;margin-bottom:16px;">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="padding:12px;border-radius:8px;background:#fef2f2;color:#991b1b;margin-bottom:16px;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin?module=optimiser&view=rapport-mensuel" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-items:end;">
        <label style="display:flex;flex-direction:column;gap:6px;">
            <span>Mois du rapport</span>
            <input type="month" name="month" value="<?= htmlspecialchars($monthInput, ENT_QUOTES, 'UTF-8') ?>" required>
        </label>

        <label style="display:flex;flex-direction:column;gap:6px;">
            <span>Email destinataire</span>
            <input type="email" name="recipient_email" value="<?= htmlspecialchars($recipientDefault, ENT_QUOTES, 'UTF-8') ?>" placeholder="email@exemple.fr">
        </label>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="submit" name="preview_report" value="1" class="btn btn-sm" style="background:#111827;color:#fff;border:none;padding:10px 14px;border-radius:8px;">Générer</button>
            <button type="submit" name="send_report" value="1" class="btn btn-sm" style="background:#dc2626;color:#fff;border:none;padding:10px 14px;border-radius:8px;">Générer + Envoyer</button>
        </div>
    </form>

    <?php if (is_array($report)): ?>
        <div style="margin-top:20px;border-top:1px solid #e5e7eb;padding-top:16px;">
            <h3 style="margin-top:0;">Synthèse <?= htmlspecialchars((string) $report['month_label'], ENT_QUOTES, 'UTF-8') ?></h3>
            <ul style="line-height:1.8;">
                <li><strong>Leads reçus :</strong> <?= (int) ($report['leads_total'] ?? 0) ?></li>
                <li><strong>Conversions :</strong> <?= (int) ($report['conversions'] ?? 0) ?> (<?= number_format((float) ($report['conversion_rate'] ?? 0), 1, ',', ' ') ?>%)</li>
                <li><strong>Articles publiés :</strong> <?= (int) ($report['articles_published'] ?? 0) ?></li>
                <li><strong>Posts sociaux publiés :</strong> <?= (int) ($report['social_posts_published'] ?? 0) ?></li>
            </ul>
            <?php if (!empty($report['html_path'])): ?>
                <p style="margin:0;">Fichier HTML : <code><?= htmlspecialchars((string) $report['html_path'], ENT_QUOTES, 'UTF-8') ?></code></p>
            <?php endif; ?>
            <?php if (!empty($report['pdf_path'])): ?>
                <p style="margin:6px 0 0;">Fichier PDF : <code><?= htmlspecialchars((string) $report['pdf_path'], ENT_QUOTES, 'UTF-8') ?></code></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
