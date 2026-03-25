<?php
/** @var PDO $pdo */
require_once dirname(__DIR__, 4) . '/app/Models/EstimatorConfigRepository.php';
require_once dirname(__DIR__, 4) . '/app/Models/EstimationRequestRepository.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/EstimatorAdminController.php';

use App\Controllers\Admin\EstimatorAdminController;
use App\Models\EstimationRequestRepository;
use App\Models\EstimatorConfigRepository;

$controller = new EstimatorAdminController(new EstimatorConfigRepository($pdo), new EstimationRequestRepository($pdo));
$data = $controller->dashboard((string) ($_GET['city_slug'] ?? ''));
$stats = $data['stats'] ?? [];
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
  <h2>Estimateur — module métier</h2>
  <a href="/estimation" target="_blank">Voir la page publique</a>
</div>

<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px;">
  <div class="card">Demandes: <?= (int)($stats['total'] ?? 0) ?></div>
  <div class="card">Nouveaux: <?= (int)($stats['new_requests'] ?? 0) ?></div>
  <div class="card">Qualifiés: <?= (int)($stats['qualified'] ?? 0) ?></div>
  <div class="card">RDV: <?= (int)($stats['appointment_booked'] ?? 0) ?></div>
</div>

<div class="card" style="padding:14px;">
  <h3>Rubriques admin disponibles</h3>
  <ul>
    <li>Dashboard estimateur</li>
    <li>Configuration générale</li>
    <li>Textes publics</li>
    <li>Quartiers / zones</li>
    <li>Règles d'estimation</li>
    <li>CTA conseiller + agenda</li>
    <li>Demandes reçues + détail</li>
    <li>SEO de la page publique</li>
  </ul>
</div>

<div class="card" style="padding:14px;margin-top:10px;">
  <h3>Dernières estimations</h3>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr><th>ID</th><th>Email</th><th>Mode</th><th>Statut</th><th>Créé le</th></tr></thead>
    <tbody>
      <?php foreach (($data['requests'] ?? []) as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['contact_email']) ?></td>
        <td><?= htmlspecialchars($r['mode']) ?></td>
        <td><?= htmlspecialchars($r['status']) ?></td>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
