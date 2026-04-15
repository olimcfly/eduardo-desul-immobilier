<?php // modules/funnels/views/list.php ?>
<div class="container-fluid px-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Funnels & Landing Pages</h1>
            <p class="text-muted mb-0">Créez des funnels de conversion guidés par canal</p>
        </div>
        <a href="?module=funnels&action=wizard&step=1" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau funnel
        </a>
    </div>

    <!-- Filtres -->
    <form method="GET" class="row g-2 mb-4">
        <input type="hidden" name="module" value="funnels">
        <div class="col-auto">
            <select name="canal" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Tous les canaux</option>
                <?php foreach ($canaux as $key => $canal): ?>
                    <option value="<?= $key ?>" <?= ($filters['canal'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($canal['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publiés</option>
                <option value="draft"     <?= ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Brouillons</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="ville" class="form-control form-control-sm"
                   placeholder="Filtrer par ville" value="<?= htmlspecialchars($filters['ville'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary btn-sm">Filtrer</button>
        </div>
    </form>

    <?php if (empty($funnels)): ?>
    <!-- État vide -->
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="fas fa-filter-circle-dollar fa-4x text-muted opacity-25"></i>
        </div>
        <h4 class="text-muted">Aucun funnel pour l'instant</h4>
        <p class="text-muted mb-4">Créez votre premier funnel de conversion en moins de 5 minutes.</p>
        <a href="?module=funnels&action=wizard&step=1" class="btn btn-primary btn-lg">
            <i class="fas fa-rocket me-2"></i>Créer mon premier funnel
        </a>
    </div>
    <?php else: ?>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Funnel</th>
                        <th>Canal</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th class="text-center">Vues</th>
                        <th class="text-center">Leads</th>
                        <th class="text-center">Conv.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funnels as $funnel): ?>
                    <?php
                        $canalInfo = $canaux[$funnel['canal']] ?? ['label' => $funnel['canal'], 'color' => '#999'];
                        $isPublished = $funnel['status'] === 'published';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($funnel['name']) ?></div>
                            <small class="text-muted">/lp/<?= htmlspecialchars($funnel['slug']) ?></small>
                        </td>
                        <td>
                            <span class="badge rounded-pill" style="background:<?= $canalInfo['color'] ?>22;color:<?= $canalInfo['color'] ?>">
                                <?= htmlspecialchars($canalInfo['label']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($funnel['ville'] ?? '—') ?></td>
                        <td>
                            <?php if ($isPublished): ?>
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-circle me-1" style="font-size:8px"></i>Publié
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Brouillon</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="?module=funnels&action=edit&id=<?= $funnel['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <?php if ($isPublished): ?>
                                    <a href="<?= rtrim(APP_URL, '/') ?>/lp/<?= htmlspecialchars($funnel['slug']) ?>"
                                       target="_blank" class="btn btn-sm btn-outline-primary" title="Voir la LP">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <a href="?module=funnels&action=stats&id=<?= $funnel['id'] ?>"
                                       class="btn btn-sm btn-outline-info" title="Statistiques">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-secondary"
                                        onclick="duplicateFunnel(<?= $funnel['id'] ?>)" title="Dupliquer">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteFunnel(<?= $funnel['id'] ?>, '<?= htmlspecialchars($funnel['name'], ENT_QUOTES) ?>')" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
function duplicateFunnel(id) {
    if (!confirm('Dupliquer ce funnel ?')) return;
    fetch(`/public/admin/api/funnels/ajax.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'duplicate', id})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.error || 'Erreur'); });
}

function deleteFunnel(id, name) {
    if (!confirm(`Supprimer le funnel "${name}" ? Cette action est irréversible.`)) return;
    fetch(`/public/admin/api/funnels/ajax.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.error || 'Erreur'); });
}
</script>
