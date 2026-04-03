<?php
$userId = (int)(Auth::user()['id'] ?? 0);
$generator = new SitemapGenerator(db(), $userId);
$generator->autoDiscoverUrls($userId);
$urls = $generator->getUrls($userId);
?>
<section class="seo-section">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Sitemap</div>
    <h2>Sitemap</h2>

    <div class="actions">
        <button type="button" onclick="generateSitemap()">Générer sitemap.xml</button>
        <button type="button" onclick="generateSitemap(true)">Soumettre à GSC</button>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>URL</th><th>Priorité</th><th>Fréquence</th><th>lastmod</th><th>Inclure</th></tr></thead>
            <tbody>
            <?php foreach ($urls as $url): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$url['url']) ?></td>
                    <td><?= htmlspecialchars((string)$url['priority']) ?></td>
                    <td><?= htmlspecialchars((string)$url['changefreq']) ?></td>
                    <td><?= htmlspecialchars((string)$url['lastmod']) ?></td>
                    <td>✅</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3>Prévisualisation XML</h3>
    <pre id="sitemap-xml-preview"><?= htmlspecialchars($generator->generate($userId)) ?></pre>
</section>
