<?php
/** @var array $secteur */
/** @var array $sections */

$h1 = $secteur['name'] ?? 'Secteur';
$title = $secteur['seo_title'] ?: $h1;
$description = $secteur['seo_description'] ?: ($secteur['excerpt'] ?? '');
$canonical = $secteur['canonical_url'] ?: '/' . ltrim((string)($secteur['slug'] ?? ''), '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars((string)$description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <style>
        body{font-family:Inter,Arial,sans-serif;line-height:1.6;color:#0f172a;margin:0;background:#f8fafc}
        .wrap{max-width:980px;margin:0 auto;padding:28px 16px 60px}
        .hero{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:18px}
        .hero h1{margin:0 0 8px}
        .badge{display:inline-block;background:#eef2ff;color:#4338ca;padding:4px 10px;border-radius:999px;font-size:12px;margin-bottom:8px}
        .section{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-bottom:14px}
        .section h2{margin:0 0 10px;font-size:24px}
    </style>
</head>
<body>
<div class="wrap">
    <article>
        <header class="hero">
            <span class="badge">Secteur immobilier local</span>
            <h1><?= htmlspecialchars($h1) ?></h1>
            <?php if (!empty($secteur['intro'])): ?>
                <p><?= nl2br(htmlspecialchars((string)$secteur['intro'])) ?></p>
            <?php endif; ?>
        </header>

        <?php foreach ($sections as $section): ?>
            <section class="section" id="section-<?= htmlspecialchars($section['section_key']) ?>">
                <h2><?= htmlspecialchars($section['section_label']) ?></h2>
                <div><?= nl2br(htmlspecialchars((string)$section['content'])) ?></div>
            </section>
        <?php endforeach; ?>
    </article>
</div>
</body>
</html>
