<?php
$analysis = $state['analysis'];
$jsonSections = [
    'market_trends_decoded' => 'Tendances locales',
    'pricing_data_decoded' => 'Prix / dynamique',
    'audience_profiles_decoded' => 'Profils cibles',
    'faq_data_decoded' => 'FAQ',
    'seo_opportunities_decoded' => 'Opportunités SEO',
    'business_recommendations_decoded' => 'Recommandations business',
];
?>

<div class="ma2-card">
    <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;">
        <div>
            <h2 style="margin:0;">Analyse #<?= (int) $analysis['id'] ?> — <?= htmlspecialchars((string) $analysis['city']) ?></h2>
            <p style="margin:6px 0 0;color:#6b7280;">Statut : <span class="ma2-pill"><?= htmlspecialchars((string) ($analysis['status'] ?? 'draft')) ?></span></p>
        </div>
        <a class="ma2-btn" style="background:#6b7280" href="?page=market-analysis">Retour liste</a>
    </div>

    <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['auth_csrf_token'] ?? '') ?>">
        <input type="hidden" name="id" value="<?= (int) $analysis['id'] ?>">
        <button class="ma2-btn" type="submit" name="action" value="run-analysis"><i class="fas fa-play"></i> Lancer analyse</button>
        <button class="ma2-btn" type="submit" name="action" value="recalculate-keywords"><i class="fas fa-key"></i> Recalculer mots-clés</button>
        <button class="ma2-btn" type="submit" name="action" value="generate-cluster"><i class="fas fa-project-diagram"></i> Générer cluster</button>
        <button class="ma2-btn" type="submit" name="action" value="send-to-articles"><i class="fas fa-newspaper"></i> Envoyer vers Articles</button>
    </form>

    <div class="ma2-grid" style="margin-top:14px;">
        <div><strong>Code postal :</strong> <?= htmlspecialchars((string) ($analysis['postal_code'] ?? '—')) ?></div>
        <div><strong>Secteur :</strong> <?= htmlspecialchars((string) ($analysis['area_name'] ?? '—')) ?></div>
        <div><strong>Cible :</strong> <?= htmlspecialchars((string) ($analysis['target_type'] ?? 'mixte')) ?></div>
        <div><strong>Type de bien :</strong> <?= htmlspecialchars((string) ($analysis['property_type'] ?? '—')) ?></div>
    </div>

    <div class="ma2-sections" style="margin-top:14px;">
        <div class="ma2-section">
            <h3>Résumé marché</h3>
            <p style="margin:0;color:#374151;"><?= nl2br(htmlspecialchars((string) ($analysis['summary'] ?? 'Aucun résumé pour le moment.'))) ?></p>
        </div>

        <?php foreach ($jsonSections as $field => $title): ?>
            <div class="ma2-section">
                <h3><?= htmlspecialchars($title) ?></h3>
                <pre style="margin:0;white-space:pre-wrap;background:#111827;color:#e5e7eb;padding:10px;border-radius:8px;font-size:12px;"><?= htmlspecialchars(json_encode($analysis[$field] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        <?php endforeach; ?>

        <div class="ma2-section">
            <h3>Notes manuelles</h3>
            <p style="margin:0;color:#374151;"><?= nl2br(htmlspecialchars((string) ($analysis['manual_notes'] ?? 'Aucune note.'))) ?></p>
        </div>

        <?php if (!empty($state['action_result'])): ?>
            <div class="ma2-section">
                <h3>Résultat de la dernière action</h3>
                <pre style="margin:0;white-space:pre-wrap;background:#111827;color:#e5e7eb;padding:10px;border-radius:8px;font-size:12px;"><?= htmlspecialchars(json_encode($state['action_result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</div>
