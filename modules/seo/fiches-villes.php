<?php
$userId = (int)(Auth::user()['id'] ?? 0);
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM seo_city_pages WHERE user_id = ? ORDER BY updated_at DESC');
$stmt->execute([$userId]);
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>
<section class="seo-section">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Fiches villes</div>
    <h2>Fiches villes</h2>

    <form method="post" action="/modules/seo/ajax/villes.php" id="city-form" class="city-form">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="0">
        <input type="hidden" name="action" value="save">
        <div class="grid-two">
            <input type="text" name="city" placeholder="Ville" maxlength="160" required>
            <input type="text" name="postal_code" placeholder="Code postal" maxlength="12" required>
            <input type="text" name="h1" placeholder="H1 optimisé" maxlength="190" required>
            <div>
                <input type="text" name="seo_title" maxlength="60" placeholder="Titre SEO" required>
                <small>60 caractères max</small>
            </div>
            <div>
                <textarea name="meta_description" maxlength="160" placeholder="Meta description" required></textarea>
                <small>160 caractères max</small>
            </div>
            <textarea name="content" placeholder="Contenu riche" rows="5" required></textarea>
            <input type="number" name="price_m2" placeholder="Prix m²">
            <input type="number" name="population" placeholder="Nb habitants">
            <input type="text" name="targeted_keywords" placeholder="Mots-clés ciblés (séparés par virgule)">
        </div>
        <div class="actions">
            <button type="button" onclick="generateVilleContent(document.querySelector('[name=city]').value)">Générer contenu IA</button>
            <button type="submit">Enregistrer</button>
        </div>
        <div id="live-seo-score">Score SEO on-page : <strong>0</strong>/100</div>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Ville</th><th>Statut</th><th>URL</th><th>MàJ</th></tr></thead>
            <tbody>
            <?php foreach ($cities as $city): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$city['city']) ?> (<?= htmlspecialchars((string)$city['postal_code']) ?>)</td>
                    <td><span class="pill"><?= $city['status'] === 'published' ? 'Publiée' : 'Brouillon' ?></span></td>
                    <td>/<?= htmlspecialchars((string)$city['slug']) ?>/</td>
                    <td><?= htmlspecialchars((string)$city['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="serp-preview" id="serp-preview">
        <h3>Aperçu Google</h3>
        <div class="serp-title">Titre SEO</div>
        <div class="serp-url">https://votre-site.fr/ville/</div>
        <div class="serp-desc">Meta description...</div>
    </div>
</section>
