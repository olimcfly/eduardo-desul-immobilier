<?php
declare(strict_types=1);

/** @var array|null $poiRow */
/** @var array<int, array<string, mixed>> $villesList */
/** @var array<int, array<string, mixed>> $quartiersList */
/** @var array<int, array<string, mixed>> $categoriesList */

$poiRow = is_array($poiRow ?? null) ? $poiRow : [];
$isEdit = !empty($poiRow['id']);
$id = $isEdit ? (int) $poiRow['id'] : 0;
$imgSrc = '';
if ($isEdit && !empty($poiRow['featured_image'])) {
    $imgSrc = (string) $poiRow['featured_image'];
    if ($imgSrc !== '' && $imgSrc[0] === '/') {
        $imgSrc = rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/') . $imgSrc;
    }
}
?>
<div class="gl-panel" style="max-width:920px">
    <p style="margin:0 0 16px;display:flex;flex-wrap:wrap;align-items:center;gap:12px">
        <a href="/admin?module=annuaire-local" class="breadcrumb-link" style="font-size:14px;color:#2563eb;text-decoration:none">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <?php if ($isEdit && $id > 0): ?>
            <a href="/admin?module=annuaire-local&amp;action=poi-export&amp;id=<?= $id ?>"
               class="breadcrumb-link"
               style="font-size:14px;color:#0f172a;text-decoration:none;border:1px solid #e2e8f0;padding:6px 12px;border-radius:8px;background:#f8fafc"
               download>
                <i class="fas fa-file-export"></i> Exporter JSON
            </a>
        <?php endif; ?>
    </p>
    <h2 style="margin:0 0 18px;font-size:18px;color:#0f172a"><?= $isEdit ? 'Modifier la fiche annuaire' : 'Nouveau commerce' ?></h2>

    <form method="post" action="/admin?module=annuaire-local" enctype="multipart/form-data" class="gl-poi-form">
        <input type="hidden" name="poi_action" value="save">
        <input type="hidden" name="id" value="<?= $id ?>">
        <?= function_exists('csrfField') ? csrfField() : '' ?>

        <div style="display:grid;gap:14px;grid-template-columns:1fr 1fr">
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Nom *</label>
                <input type="text" name="name" required maxlength="255" value="<?= e((string) ($poiRow['name'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Slug (URL)</label>
                <input type="text" name="slug" maxlength="190" placeholder="auto depuis le nom"
                       value="<?= e((string) ($poiRow['slug'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Catégorie *</label>
                <select name="category_id" required style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
                    <option value="">— Choisir —</option>
                    <?php foreach ($categoriesList as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($poiRow['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= e((string) $c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Ville</label>
                <select name="ville_id" id="gl-poi-ville" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
                    <option value="0">— (optionnel si quartier choisi) —</option>
                    <?php foreach ($villesList as $v): ?>
                        <option value="<?= (int) $v['id'] ?>" <?= (int) ($poiRow['ville_id'] ?? 0) === (int) $v['id'] ? 'selected' : '' ?>>
                            <?= e((string) $v['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Quartier</label>
                <select name="quartier_id" id="gl-poi-quartier" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
                    <option value="0">— Aucun —</option>
                    <?php foreach ($quartiersList as $q): ?>
                        <option value="<?= (int) $q['id'] ?>" data-ville-id="<?= (int) $q['ville_id'] ?>"
                            <?= (int) ($poiRow['quartier_id'] ?? 0) === (int) $q['id'] ? 'selected' : '' ?>>
                            <?= e((string) $q['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p style="grid-column:1/-1;margin:0;font-size:12px;color:#64748b">Au moins une ville ou un quartier est requis. Si vous choisissez un quartier, la ville est déduite automatiquement.</p>

            <div style="grid-column:1/-1">
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Description</label>
                <textarea name="description" id="gl-field-description" rows="5" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px"><?= e((string) ($poiRow['description'] ?? '')) ?></textarea>
                <p style="margin:6px 0 0;font-size:12px;color:#64748b">
                    <button type="button" class="gl-btn gl-btn-ghost" id="gl-llm-desc" style="font-size:12px">Générer description (IA)</button>
                    (nécessite <code>ANTHROPIC_API_KEY</code> / service AI configuré)
                </p>
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Mots-clés SEO (virgules)</label>
                <input type="text" name="seo_keywords" id="gl-field-seo" maxlength="500" placeholder="boulangerie Mérignac, pain au levain…" value="<?= e((string) ($poiRow['seo_keywords'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
                <p style="margin:6px 0 0;font-size:12px;color:#64748b">
                    <button type="button" class="gl-btn gl-btn-ghost" id="gl-llm-seo" style="font-size:12px">Suggérer mots-clés (IA)</button>
                </p>
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Adresse</label>
                <input type="text" name="address" maxlength="255" value="<?= e((string) ($poiRow['address'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Code postal</label>
                <input type="text" name="postal_code" maxlength="12" value="<?= e((string) ($poiRow['postal_code'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Téléphone</label>
                <input type="text" name="phone" maxlength="40" value="<?= e((string) ($poiRow['phone'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Site web</label>
                <input type="url" name="website" maxlength="500" placeholder="https://…" value="<?= e((string) ($poiRow['website'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">E-mail</label>
                <input type="email" name="email" maxlength="255" value="<?= e((string) ($poiRow['email'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Facebook (URL)</label>
                <input type="url" name="facebook" maxlength="500" value="<?= e((string) ($poiRow['facebook'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Instagram (URL)</label>
                <input type="url" name="instagram" maxlength="500" value="<?= e((string) ($poiRow['instagram'] ?? '')) ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Note (0–5, optionnel)</label>
                <input type="number" name="rating" step="0.1" min="0" max="5" placeholder="ex. 4.5" value="<?= isset($poiRow['rating']) && $poiRow['rating'] !== null && $poiRow['rating'] !== '' ? e((string) $poiRow['rating']) : '' ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Latitude</label>
                <input type="text" name="latitude" inputmode="decimal" value="<?= e(isset($poiRow['latitude']) && $poiRow['latitude'] !== null && $poiRow['latitude'] !== '' ? (string) $poiRow['latitude'] : '') ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Longitude</label>
                <input type="text" name="longitude" inputmode="decimal" value="<?= e(isset($poiRow['longitude']) && $poiRow['longitude'] !== null && $poiRow['longitude'] !== '' ? (string) $poiRow['longitude'] : '') ?>"
                       style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px">
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Horaires (texte ou JSON)</label>
                <textarea name="opening_hours" rows="3" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px"><?= e((string) ($poiRow['opening_hours'] ?? '')) ?></textarea>
            </div>
            <div style="grid-column:1/-1">
                <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:4px">Image principale</label>
                <input type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                       style="font-size:14px">
                <?php if ($imgSrc !== ''): ?>
                    <p style="margin:8px 0 0;font-size:13px;color:#64748b">Actuelle : <a href="<?= e($imgSrc) ?>" target="_blank" rel="noopener">voir</a></p>
                <?php endif; ?>
            </div>
            <div style="grid-column:1/-1;display:flex;flex-wrap:wrap;align-items:center;gap:16px">
                <div style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="is_active" value="1" id="gl-poi-active" <?= !isset($poiRow['is_active']) || (int) ($poiRow['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
                    <label for="gl-poi-active" style="font-size:14px;color:#334155">En ligne (visible annuaire / API)</label>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="is_verified" value="1" id="gl-poi-verified" <?= (int) ($poiRow['is_verified'] ?? 0) === 1 ? 'checked' : '' ?>>
                    <label for="gl-poi-verified" style="font-size:14px;color:#334155">Fiche vérifiée par l’équipe</label>
                </div>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap">
            <button type="submit" class="btn" style="background:#0f2237;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-size:14px;cursor:pointer">
                Enregistrer
            </button>
        </div>
    </form>
</div>
<script>
(function () {
    var ville = document.getElementById('gl-poi-ville');
    var quartier = document.getElementById('gl-poi-quartier');
    if (!ville || !quartier) return;
    function filterQuartiers() {
        var vid = parseInt(ville.value, 10) || 0;
        var opts = quartier.querySelectorAll('option[data-ville-id]');
        var current = quartier.value;
        opts.forEach(function (o) {
            var qv = parseInt(o.getAttribute('data-ville-id'), 10) || 0;
            o.hidden = vid > 0 && qv !== vid;
        });
        var sel = quartier.querySelector('option[value="' + current + '"]');
        if (sel && sel.hidden) quartier.value = '0';
    }
    ville.addEventListener('change', filterQuartiers);
    filterQuartiers();
})();

(function () {
    var form = document.querySelector('.gl-poi-form');
    if (!form) return;
    function csrfToken() {
        var h = form.querySelector('input[name="csrf_token"]');
        return h ? h.value : '';
    }
    function postLlm(kind) {
        var name = (form.querySelector('input[name="name"]') || {}).value || '';
        var category = form.querySelector('select[name="category_id"]');
        var categoryId = category ? category.value : '';
        if (!name.trim() || !categoryId) {
            window.alert('Renseignez le nom et la catégorie pour générer du texte.');
            return;
        }
        var fd = new FormData();
        fd.append('poi_action', 'llm_suggest');
        fd.append('llm_kind', kind);
        fd.append('csrf_token', csrfToken());
        fd.append('name', name);
        fd.append('category_id', categoryId);
        fd.append('ville_id', (form.querySelector('select[name="ville_id"]') || { value: '0' }).value);
        fd.append('quartier_id', (form.querySelector('select[name="quartier_id"]') || { value: '0' }).value);
        var descBtn = document.getElementById('gl-llm-desc');
        var seoBtn = document.getElementById('gl-llm-seo');
        if (kind === 'description' && descBtn) descBtn.disabled = true;
        if (kind === 'seo' && seoBtn) seoBtn.disabled = true;
        window.fetch('/admin?module=annuaire-local', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (x) {
                if (kind === 'description' && descBtn) descBtn.disabled = false;
                if (kind === 'seo' && seoBtn) seoBtn.disabled = false;
                if (!x.j || !x.j.ok) {
                    window.alert(x.j && x.j.error ? x.j.error : 'Échec génération');
                    return;
                }
                if (kind === 'description') {
                    var t = document.getElementById('gl-field-description');
                    if (t) t.value = (t.value ? t.value.trim() + '\n\n' : '') + x.j.text;
                } else {
                    var s = document.getElementById('gl-field-seo');
                    if (s) s.value = x.j.text;
                }
            })
            .catch(function () {
                if (descBtn) descBtn.disabled = false;
                if (seoBtn) seoBtn.disabled = false;
                window.alert('Erreur réseau');
            });
    }
    var bd = document.getElementById('gl-llm-desc');
    var bs = document.getElementById('gl-llm-seo');
    if (bd) bd.addEventListener('click', function () { postLlm('description'); });
    if (bs) bs.addEventListener('click', function () { postLlm('seo'); });
})();
</script>
