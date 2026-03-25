<?php
/**
 * Module Analyse de Marché (Phase 1 + 2)
 * - Listing
 * - Création d'une analyse brouillon
 * - Vue détail
 */

if (!defined('ADMIN_ROUTER')) {
    require_once dirname(dirname(dirname(__DIR__))) . '/includes/init.php';
}

require_once __DIR__ . '/MarketAnalysisController.php';

$userId = (int) ($_SESSION['admin_id'] ?? 0);
$controller = new MarketAnalysisController($pdo, $userId);
$state = $controller->handleRequest();

$viewFile = __DIR__ . '/views/' . $state['view'] . '.php';
if (!file_exists($viewFile)) {
    echo '<div class="alert alert-danger">Vue introuvable: ' . htmlspecialchars($state['view']) . '</div>';
    return;
}

$flash = $state['flash'] ?? null;
?>

<div class="ma2-wrap">
    <div class="ma2-header">
        <div>
            <h1><i class="fas fa-chart-area"></i> Analyse de Marché</h1>
            <p>Base stratégique locale pour SEO, contenu, secteurs et génération de leads vendeurs.</p>
        </div>
        <?php if ($state['view'] !== 'form'): ?>
            <a class="ma2-btn" href="?page=market-analysis&action=create"><i class="fas fa-plus"></i> Nouvelle analyse</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="ma2-flash ma2-flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
    <?php endif; ?>

    <?php include $viewFile; ?>
</div>

<style>
.ma2-wrap{max-width:1180px}
.ma2-header{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:18px}
.ma2-header h1{margin:0;font-size:24px}
.ma2-header p{margin:6px 0 0;color:#6b7280;font-size:13px}
.ma2-btn{display:inline-flex;align-items:center;gap:8px;background:var(--accent,#c9913b);padding:10px 14px;border-radius:8px;color:#fff;text-decoration:none;font-weight:600}
.ma2-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px}
.ma2-table{width:100%;border-collapse:collapse}
.ma2-table th,.ma2-table td{padding:10px;border-bottom:1px solid #f1f5f9;text-align:left;font-size:13px}
.ma2-table th{font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.03em}
.ma2-status{padding:4px 8px;border-radius:999px;font-size:12px;font-weight:600;background:#eef2ff;color:#4338ca}
.ma2-status.completed{background:#dcfce7;color:#166534}
.ma2-status.error{background:#fee2e2;color:#b91c1c}
.ma2-status.running{background:#fef3c7;color:#92400e}
.ma2-actions{display:flex;gap:8px;align-items:center}
.ma2-link{color:#1f2937;text-decoration:none;font-weight:600}
.ma2-danger{background:#fff;border:1px solid #ef4444;color:#ef4444;border-radius:8px;padding:6px 9px;cursor:pointer}
.ma2-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
.ma2-field label{display:block;font-size:12px;color:#6b7280;margin-bottom:5px}
.ma2-field input,.ma2-field select,.ma2-field textarea{width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;font-family:inherit}
.ma2-field textarea{min-height:100px;resize:vertical}
.ma2-flash{padding:10px 12px;border-radius:8px;margin-bottom:14px;font-size:13px}
.ma2-flash-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.ma2-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.ma2-errors{margin:0 0 14px;padding:10px 12px 10px 28px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#7f1d1d}
.ma2-sections{display:grid;gap:12px}
.ma2-section h3{margin:0 0 8px;font-size:14px}
.ma2-pill{display:inline-block;padding:5px 9px;background:#f3f4f6;border-radius:999px;font-size:12px}
</style>
