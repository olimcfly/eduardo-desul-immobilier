<?php

declare(strict_types=1);

$pageTitle = 'Web Scraper';
$pageDescription = 'Crawler et parcourir les clients en ligne';

function renderContent(): void {
    ?>
    <style>
        .hub-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .hub-hero h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .hub-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .hub-actions { display: flex; gap: 12px; margin-top: 16px; }
        .hub-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all .2s; }
        .hub-btn-primary { background: #c9a84c; color: #10253c; }
        .hub-btn-primary:hover { background: #b8962d; }
        .scraper-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; }
        .scraper-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; transition: all .2s; }
        .scraper-card:hover { border-color: #c9a84c; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        .scraper-card h3 { margin: 0 0 12px; font-size: 15px; font-weight: 600; color: #0f172a; }
        .scraper-card p { margin: 0 0 16px; font-size: 13px; color: #64748b; line-height: 1.5; }
        .scraper-actions { display: flex; gap: 8px; }
        .scraper-btn { padding: 8px 16px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; }
        .scraper-btn-primary { background: #3498db; color: #fff; }
        .scraper-btn-primary:hover { background: #2980b9; }
        .scraper-btn-secondary { background: #e5e7eb; color: #374151; }
        .scraper-btn-secondary:hover { background: #d1d5db; }
        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
        .scraper-status { display: inline-block; padding: 6px 12px; background: #dbeafe; color: #1d4ed8; border-radius: 6px; font-size: 12px; font-weight: 600; margin-bottom: 12px; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Web Scraper</h1>
            <p>Crawler et importer les données clients depuis le web</p>
            <div class="hub-actions">
                <a href="/admin?module=scraper&action=new" class="hub-btn hub-btn-primary">
                    <i class="fas fa-plus"></i> Nouveau crawler
                </a>
            </div>
        </header>

        <div class="scraper-grid">
            <?php
            try {
                $stmt = db()->prepare('SELECT id, name, source, status, last_run, created_at FROM scrapers ORDER BY created_at DESC LIMIT 12');
                $stmt->execute();
                $scrapers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($scrapers)):
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-spider" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                    <p>Aucun scraper créé</p>
                    <p style="font-size: 12px; margin-top: 8px;">Créez votre premier crawler pour importer les données clients</p>
                    <a href="/admin?module=scraper&action=new" class="hub-btn hub-btn-primary" style="margin-top: 16px;">
                        <i class="fas fa-plus"></i> Créer un scraper
                    </a>
                </div>
            <?php else: foreach ($scrapers as $scraper):
                $statusClass = $scraper['status'] === 'active' ? '#dcfce7' : '#fee2e2';
                $statusColor = $scraper['status'] === 'active' ? '#166534' : '#991b1b';
            ?>
                <div class="scraper-card">
                    <span class="scraper-status" style="background: <?= $statusClass ?>; color: <?= $statusColor ?>;">
                        <?= ucfirst($scraper['status']) ?>
                    </span>
                    <h3><?= htmlspecialchars($scraper['name']) ?></h3>
                    <p>
                        <strong>Source:</strong> <?= htmlspecialchars($scraper['source']) ?><br>
                        <small style="color: #9ca3af;">Créé le <?= date('d/m/Y', strtotime($scraper['created_at'])) ?></small><br>
                        <?php if ($scraper['last_run']): ?>
                            <small style="color: #9ca3af;">Dernier crawl: <?= date('d/m/Y H:i', strtotime($scraper['last_run'])) ?></small>
                        <?php endif; ?>
                    </p>
                    <div class="scraper-actions">
                        <button class="scraper-btn scraper-btn-primary" onclick="runScraper(<?= $scraper['id'] ?>)">
                            <i class="fas fa-play"></i> Lancer
                        </button>
                        <a href="/admin?module=scraper&action=edit&id=<?= $scraper['id'] ?>" class="scraper-btn scraper-btn-secondary">
                            <i class="fas fa-edit"></i> Éditer
                        </a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
            <?php } catch (Throwable $e) {
                error_log('Scrapers Error: ' . $e->getMessage());
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <p>Erreur lors du chargement des scrapers</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
    function runScraper(id) {
        if (confirm('Lancer le crawl maintenant ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="run_scraper" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <?php
}
