<?php
$userId = (int)(Auth::user()['id'] ?? 0);
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM seo_performance_audits WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$stmt->execute([$userId]);
$audits = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>
<section class="seo-section">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Performance</div>
    <h2>Performance technique</h2>

    <form class="inline-form" onsubmit="event.preventDefault();runPerformanceAudit(this.url.value,this.device.value)">
        <input type="url" name="url" placeholder="https://votre-site.fr" required>
        <select name="device"><option value="mobile">Mobile</option><option value="desktop">Desktop</option></select>
        <button type="submit">Lancer audit</button>
    </form>

    <div class="score-circles">
        <div id="score-perf"></div>
        <div id="score-seo"></div>
        <div id="score-access"></div>
        <div id="score-bp"></div>
    </div>

    <div id="cwv-results" class="grid-two"></div>
    <div id="opportunities"></div>
    <div id="diagnostics"></div>

    <h3>Historique des audits</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>URL</th><th>Device</th><th>Perf</th><th>SEO</th><th>Access</th><th>BP</th></tr></thead>
            <tbody>
            <?php foreach ($audits as $audit): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$audit['created_at']) ?></td>
                    <td><?= htmlspecialchars((string)$audit['audited_url']) ?></td>
                    <td><?= htmlspecialchars((string)$audit['device']) ?></td>
                    <td><?= (int)$audit['perf_score'] ?></td>
                    <td><?= (int)$audit['seo_score'] ?></td>
                    <td><?= (int)$audit['access_score'] ?></td>
                    <td><?= (int)$audit['bp_score'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
