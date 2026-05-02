<?php
/**
 * @var array<string, mixed> $villeRow
 */
$villeRow = is_array($villeRow ?? null) ? $villeRow : [];
$nom  = (string) ($villeRow['nom'] ?? '');
$slug = (string) ($villeRow['slug'] ?? '');
$vErr = trim((string) ($_GET['error'] ?? ''));
$vOk  = isset($_GET['ville_saved']);
$base = rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/');
$pub  = $base . '/guide-local/annuaire/' . rawurlencode($slug);
?>
<style>
    .vp-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 22px 20px; color: #fff; margin-bottom: 20px; }
    .vp-hero h1 { margin: 0 0 6px; font-size: 22px; }
    .vp-hero a { color: #c9a84c; font-weight: 600; }
    .vp-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; max-width: 700px; }
    .vp-panel label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; }
    .vp-panel input[type="text"], .vp-panel textarea { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
    .vp-panel textarea { min-height: 180px; }
    .vp-warn { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; padding: 12px; border-radius: 10px; margin-bottom: 12px; font-size: 14px; }
    .vp-ok { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px; border-radius: 10px; margin-bottom: 12px; font-size: 14px; }
    .vp-btn { background: #0f2237; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
</style>
<div class="vp-hero">
    <p style="margin:0 0 8px"><a href="/admin?module=annuaire-local" style="color:#c9a84c;text-decoration:none">← Retour annuaire local</a></p>
    <h1>Présentation — <?= e($nom) ?></h1>
    <p style="margin:0;font-size:14px;color:rgba(255,255,255,.8)">Ces champs alimentent la <strong>page guide publique</strong> (photo, texte) : <a href="<?= e($pub) ?>" target="_blank" rel="noopener"><?= e($pub) ?></a></p>
</div>
<?php if ($vErr !== ''): ?><div class="vp-warn"><?= e($vErr) ?></div><?php endif; ?>
<?php if ($vOk): ?><div class="vp-ok">Enregistrement enregistré — vérifiez l’affichage sur le site.</div><?php endif; ?>
<form class="vp-panel" method="post" action="/admin?module=annuaire-local">
    <input type="hidden" name="annuaire_ville_action" value="save_presentation">
    <input type="hidden" name="ville_slug" value="<?= e($slug) ?>">
    <?= function_exists('csrfField') ? csrfField() : '' ?>

    <p style="margin:0 0 16px;font-size:14px;color:#64748b">Ville : <strong><?= e($nom) ?></strong> · slug <code><?= e($slug) ?></code></p>

    <div style="margin-bottom:16px">
        <label for="vp-cp">Code postal (affichage)</label>
        <input type="text" id="vp-cp" name="code_postal" maxlength="5" value="<?= e((string) ($villeRow['code_postal'] ?? '')) ?>" placeholder="33700">
    </div>
    <div style="margin-bottom:16px">
        <label for="vp-img">URL de la photo de couverture (bandeau)</label>
        <input type="text" id="vp-img" name="image_url" value="<?= e((string) ($villeRow['image_url'] ?? '')) ?>" placeholder="/storage/... ou https://…">
    </div>
    <div style="margin-bottom:20px">
        <label for="vp-desc">Texte d’introduction (quelques phrases)</label>
        <textarea id="vp-desc" name="description" rows="8" placeholder="Pourquoi ce guide, ce que l’on y trouve…"><?= e((string) ($villeRow['description'] ?? '')) ?></textarea>
    </div>
    <button type="submit" class="vp-btn">Enregistrer</button>
</form>
