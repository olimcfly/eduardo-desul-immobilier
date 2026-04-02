<?php $pageTitle = 'Dashboard'; $breadcrumb = '📊 Dashboard'; ?>

<div class="dashboard-grid">

    <!-- Stats rapides -->
    <section class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">🏠</div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['biens_total'] ?? 0 ?></div>
                <div class="stat-label">Biens total</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['biens_actifs'] ?? 0 ?></div>
                <div class="stat-label">Biens actifs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['gmb_note'] ?? '—' ?></div>
                <div class="stat-label">Note Google</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['avis_total'] ?? 0 ?></div>
                <div class="stat-label">Avis Google</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔑</div>
            <div class="stat-body">
                <div class="stat-value"><?= $stats['keywords_top'] ?? 0 ?></div>
                <div class="stat-label">Mots-clés top 10</div>
            </div>
        </div>
    </section>

    <!-- Colonne gauche -->
    <div class="dash-col-left">

        <!-- Biens récents -->
        <div class="card">
            <div class="card-header">
                <h2>🏠 Biens récents</h2>
                <a href="/admin/biens/" class="btn btn-sm">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (empty($biensRecents)): ?>
                    <p class="empty">Aucun bien enregistré.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Prix</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($biensRecents as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['titre']) ?></td>
                                <td><?= htmlspecialchars($b['type']) ?></td>
                                <td><?= number_format($b['prix'], 0, ',', ' ') ?> €</td>
                                <td>
                                    <span class="badge badge-<?= $b['statut'] === 'actif' ? 'success' : 'muted' ?>">
                                        <?= $b['statut'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Avis récents -->
        <div class="card">
            <div class="card-header">
                <h2>⭐ Derniers avis Google</h2>
                <a href="/admin/gmb/reviews.php" class="btn btn-sm">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (empty($avisRecents)): ?>
                    <p class="empty">Aucun avis récupéré.</p>
                <?php else: ?>
                    <?php foreach ($avisRecents as $a): ?>
                    <div class="review-item">
                        <div class="review-meta">
                            <strong><?= htmlspecialchars($a['auteur']) ?></strong>
                            <span class="stars"><?= str_repeat('★', $a['note']) ?><?= str_repeat('☆', 5 - $a['note']) ?></span>
                            <span class="review-date"><?= $a['date'] ?></span>
                        </div>
                        <p class="review-text"><?= htmlspecialchars(mb_strimwidth($a['texte'], 0, 120, '…')) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Colonne droite -->
    <div class="dash-col-right">

        <!-- Activité SEO -->
        <div class="card">
            <div class="card-header">
                <h2>🔍 SEO — Top mots-clés</h2>
                <a href="/admin/seo/" class="btn btn-sm">Détails</a>
            </div>
            <div class="card-body">
                <?php if (empty($topKeywords)): ?>
                    <p class="empty">Aucune donnée SEO.</p>
                <?php else: ?>
                    <ul class="kw-list">
                        <?php foreach ($topKeywords as $kw): ?>
                        <li class="kw-item">
                            <span class="kw-word"><?= htmlspecialchars($kw['mot']) ?></span>
                            <span class="kw-pos badge badge-<?= $kw['position'] <= 3 ? 'success' : ($kw['position'] <= 10 ? 'warning' : 'muted') ?>">
                                #<?= $kw['position'] ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Social -->
        <div class="card">
            <div class="card-header">
                <h2>📱 Social — Prochaines publications</h2>
                <a href="/admin/social/" class="btn btn-sm">Gérer</a>
            </div>
            <div class="card-body">
                <?php if (empty($socialQueue)): ?>
                    <p class="empty">File vide.</p>
                <?php else: ?>
                    <?php foreach ($socialQueue as $s): ?>
                    <div class="social-item">
                        <span class="social-platform"><?= $s['plateforme'] ?></span>
                        <span class="social-date"><?= $s['date_prevue'] ?></span>
                        <p><?= htmlspecialchars(mb_strimwidth($s['contenu'], 0, 80, '…')) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Accès rapides -->
        <div class="card">
            <div class="card-header"><h2>⚡ Accès rapides</h2></div>
            <div class="card-body quick-links">
                <a href="/admin/biens/form.php" class="quick-link">➕ Nouveau bien</a>
                <a href="/admin/gmb/" class="quick-link">🔄 Sync GMB</a>
                <a href="/admin/seo/keywords.php" class="quick-link">📈 Mots-clés</a>
                <a href="/admin/social/sequences.php" class="quick-link">📅 Séquences</a>
                <a href="/admin/settings/" class="quick-link">⚙️ Paramètres</a>
            </div>
        </div>

    </div>

</div>

<script src="/admin/assets/js/dashboard.js"></script>
