<?php

declare(strict_types=1);

$pageTitle = 'Secteurs & Zones';
$pageDescription = 'Fiches secteurs (marketing) + référence des communes couvertes';

/**
 * Liste des 12 communes cibles (fallback si table villes absente / vide).
 * @return list<array{nom: string, slug: string}>
 */
function secteurs_reference_villes_fallback(): array
{
    return [
        ['nom' => 'Bordeaux', 'slug' => 'bordeaux'],
        ['nom' => 'Mérignac', 'slug' => 'merignac'],
        ['nom' => 'Talence', 'slug' => 'talence'],
        ['nom' => 'Pessac', 'slug' => 'pessac'],
        ['nom' => 'Floirac', 'slug' => 'floirac'],
        ['nom' => 'Lormont', 'slug' => 'lormont'],
        ['nom' => 'Eysines', 'slug' => 'eysines'],
        ['nom' => 'Saint-Médard-en-Jalles', 'slug' => 'saint-medard'],
        ['nom' => 'Villenave-d’Ornon', 'slug' => 'villenave-dornon'],
        ['nom' => 'Bouliac', 'slug' => 'bouliac'],
        ['nom' => 'Carbon-Blanc', 'slug' => 'carbon-blanc'],
        ['nom' => 'Blanquefort', 'slug' => 'blanquefort'],
    ];
}

/**
 * @return list<array{nom: string, slug: string}>
 */
function secteurs_reference_villes(): array
{
    try {
        $pdo = db();
        $st = $pdo->query(
            'SELECT nom, slug FROM villes WHERE actif = 1 ORDER BY ordre ASC, nom ASC'
        );
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        if (is_array($rows) && $rows !== []) {
            $out = [];
            foreach ($rows as $r) {
                $nom = trim((string) ($r['nom'] ?? ''));
                $slug = trim((string) ($r['slug'] ?? ''));
                if ($nom !== '' && $slug !== '') {
                    $out[] = ['nom' => $nom, 'slug' => $slug];
                }
            }
            if ($out !== []) {
                return $out;
            }
        }
    } catch (Throwable $e) {
        error_log('secteurs_reference_villes: ' . $e->getMessage());
    }

    return secteurs_reference_villes_fallback();
}

/**
 * Ordre et libellés identiques au menu « Secteurs » du site ({@see public/templates/nav.php}).
 *
 * @return array<string, string> slug => libellé menu
 */
function secteurs_nav_quartiers_menu_labels(): array
{
    return [
        'chartrons'       => 'Chartrons',
        'cauderan'        => 'Caudéran',
        'belcier'         => 'Belcier',
        'saint-augustin'  => 'Saint-Augustin',
        'bacalan'         => 'Bacalan',
        'capucins'        => 'Capucins',
        'saint-michel'    => 'Saint-Michel',
        'bastide'         => 'La Bastide',
        'saint-seurin'    => 'Saint-Seurin',
        'nansouty'        => 'Nansouty',
        'bordeaux-centre' => 'Bordeaux Centre',
    ];
}

/**
 * @return list<array{label: string, slug: string}>
 */
function secteurs_reference_quartiers_menu(): array
{
    $labels = secteurs_nav_quartiers_menu_labels();
    $slugs = array_keys($labels);
    $out = [];

    try {
        $pdo = db();
        $st = $pdo->prepare(
            'SELECT q.nom, q.slug
             FROM quartiers q
             INNER JOIN villes v ON v.id = q.ville_id AND v.slug = ? AND v.actif = 1
             WHERE q.actif = 1 AND q.slug IN (' . implode(',', array_fill(0, count($slugs), '?')) . ')'
        );
        $params = array_merge(['bordeaux'], $slugs);
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $bySlug = [];
        foreach ($rows as $r) {
            $slug = (string) ($r['slug'] ?? '');
            if ($slug !== '') {
                $bySlug[$slug] = trim((string) ($r['nom'] ?? '')) ?: ($labels[$slug] ?? $slug);
            }
        }
        foreach ($slugs as $slug) {
            $out[] = [
                'label' => $bySlug[$slug] ?? ($labels[$slug] ?? $slug),
                'slug'  => $slug,
            ];
        }

        return $out;
    } catch (Throwable $e) {
        error_log('secteurs_reference_quartiers_menu: ' . $e->getMessage());
    }

    foreach ($slugs as $slug) {
        $out[] = ['label' => $labels[$slug], 'slug' => $slug];
    }

    return $out;
}

function renderContent(): void {
    $villesRef = secteurs_reference_villes();
    $quartiersMenu = secteurs_reference_quartiers_menu();
    ?>
    <style>
        .hub-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 24px 20px; color: #fff; margin-bottom: 24px; }
        .hub-hero h1 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .hub-hero p { margin: 0; color: rgba(255,255,255,.78); font-size: 15px; }
        .hub-actions { display: flex; gap: 12px; margin-top: 16px; }
        .hub-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all .2s; }
        .hub-btn-primary { background: #c9a84c; color: #10253c; }
        .hub-btn-primary:hover { background: #b8962d; }
        .sectors-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .section-header { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #f9fafb; }
        .section-header h2 { margin: 0; font-size: 16px; font-weight: 600; color: #0f172a; }
        .sectors-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .sectors-table th { padding: 12px 16px; text-align: left; font-weight: 600; color: #374151; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .sectors-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
        .sectors-table tr:hover { background: #f9fafb; }
        .sector-name { font-weight: 600; color: #0f172a; }
        .sector-desc { font-size: 12px; color: #64748b; }
        .empty-state { padding: 32px 16px; text-align: center; color: #9ca3af; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .status-published { background: #dcfce7; color: #166534; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .action-links { display: flex; gap: 8px; }
        .action-link { padding: 4px 8px; text-decoration: none; color: #3498db; font-size: 12px; border-radius: 4px; }
        .action-link:hover { background: #ecf0f1; }
        .delete-link { color: #dc2626; }
        .villes-ref-section {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 24px;
            border-left: 4px solid #2563eb;
        }
        .villes-ref-section .section-header h2 { font-size: 15px; }
        .villes-ref-note {
            padding: 12px 20px;
            font-size: 13px;
            color: #475569;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            line-height: 1.55;
        }
        .villes-ref-note code { background: #e2e8f0; padding: 1px 6px; border-radius: 4px; font-size: 12px; }
        .villes-ref-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0;
        }
        .ville-ref-card {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .ville-ref-card strong { display: block; color: #0f172a; margin-bottom: 4px; }
        .ville-ref-card .slug { font-size: 12px; color: #64748b; font-family: ui-monospace, monospace; margin-bottom: 8px; }
        .ville-ref-links { display: flex; flex-wrap: wrap; gap: 6px; }
        .ville-ref-links a {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            text-decoration: none;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .ville-ref-links a:hover { background: #dbeafe; }
        .quartiers-ref-section {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 24px;
            border-left: 4px solid #059669;
        }
        .quartiers-ref-section .villes-ref-note { border-bottom: 1px solid #e5e7eb; }
    </style>

    <div>
        <header class="hub-hero">
            <h1>Secteurs & Zones</h1>
            <p>Fiches éditoriales personnalisées (table <code>sectors</code>) — pas la base villes/quartiers/POI du site. Pour la carte et les API guide local, utilisez <a href="/admin?module=annuaire-local" style="color:#c9a84c;font-weight:600">Annuaire local</a>.</p>
            <div class="hub-actions">
                <a href="/admin?module=secteurs&action=new" class="hub-btn hub-btn-primary">
                    <i class="fas fa-plus"></i> Nouveau secteur
                </a>
            </div>
        </header>

        <div class="villes-ref-section">
            <div class="section-header">
                <h2>Communes de référence (<?= count($villesRef) ?>)</h2>
            </div>
            <div class="villes-ref-note">
                <strong>Où c’est dans le site ?</strong> Même ordre que le menu <strong>Secteurs → Villes</strong> dans
                <code>public/templates/nav.php</code>.
                Fiches publiques <code>/immobilier/[slug]</code> (alias <code>/secteurs/villes/[slug]</code>).
                Ce bloc n’est <strong>pas</strong> le tableau marketing <code>sectors</code> plus bas.
                Données : table <code>villes</code> si disponible, sinon liste par défaut (12 communes).
                <a href="/secteurs" target="_blank" rel="noopener" style="color:#1d4ed8;font-weight:600;margin-left:6px">Page Secteurs du site →</a>
            </div>
            <div class="villes-ref-grid">
                <?php foreach ($villesRef as $v):
                    $slug = rawurlencode($v['slug']);
                    ?>
                    <div class="ville-ref-card">
                        <strong><?= htmlspecialchars($v['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <div class="slug"><?= htmlspecialchars($v['slug'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="ville-ref-links">
                            <a href="/immobilier/<?= $slug ?>" target="_blank" rel="noopener">Fiche /immobilier</a>
                            <a href="/secteurs/villes/<?= $slug ?>" target="_blank" rel="noopener">Alias /secteurs</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="quartiers-ref-section villes-ref-section" style="border-left-color:#059669">
            <div class="section-header">
                <h2>Quartiers du menu (<?= count($quartiersMenu) ?>) — Bordeaux</h2>
            </div>
            <div class="villes-ref-note">
                Même liste que <strong>Secteurs → Quartiers de Bordeaux</strong> dans
                <code>public/templates/nav.php</code>.
                Fiches <code>/quartier/[slug]</code> (alias <code>/secteurs/quartiers/[slug]</code>).
                Données : table <code>quartiers</code> (ville Bordeaux) quand disponible ; libellés menu sinon.
                Fiches &amp; carte : <a href="/admin?module=annuaire-local" style="color:#047857;font-weight:600">Annuaire local</a>.
            </div>
            <div class="villes-ref-grid">
                <?php foreach ($quartiersMenu as $q):
                    $slug = rawurlencode($q['slug']);
                    ?>
                    <div class="ville-ref-card">
                        <strong><?= htmlspecialchars($q['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <div class="slug"><?= htmlspecialchars($q['slug'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="ville-ref-links">
                            <a href="/quartier/<?= $slug ?>" target="_blank" rel="noopener">Fiche /quartier</a>
                            <a href="/secteurs/quartiers/<?= $slug ?>" target="_blank" rel="noopener">Alias /secteurs</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="sectors-section">
            <div class="section-header">
                <h2>Fiches secteur (marketing) — table <code style="font-size:13px">sectors</code></h2>
            </div>
            <table class="sectors-table">
                <thead>
                    <tr>
                        <th>Secteur</th>
                        <th>Quartier</th>
                        <th>Statut</th>
                        <th>Créé</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = db()->prepare('SELECT id, name, zone, status, created_at FROM sectors ORDER BY created_at DESC LIMIT 50');
                        $stmt->execute();
                        $sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (empty($sectors)):
                    ?>
                        <tr><td colspan="5" class="empty-state">Aucun secteur créé. <a href="/admin?module=secteurs&action=new" style="color: #c9a84c; font-weight: 600;">Créer le premier</a></td></tr>
                    <?php else: foreach ($sectors as $sector):
                        $statusClass = $sector['status'] === 'published' ? 'status-published' : 'status-draft';
                    ?>
                        <tr>
                            <td>
                                <div class="sector-name"><?= htmlspecialchars($sector['name']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($sector['zone'] ?? '') ?></td>
                            <td><span class="status-badge <?= $statusClass ?>"><?= ucfirst($sector['status']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($sector['created_at'])) ?></td>
                            <td>
                                <div class="action-links">
                                    <a href="/admin?module=secteurs&action=edit&id=<?= $sector['id'] ?>" class="action-link">Éditer</a>
                                    <a href="#" onclick="deleteSector(<?= $sector['id'] ?>); return false;" class="action-link delete-link">Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    <?php } catch (Throwable $e) {
                        error_log('Sectors Error: ' . $e->getMessage());
                    ?>
                        <tr><td colspan="5" class="empty-state">Erreur lors du chargement des secteurs</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteSector(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce secteur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="delete_sector" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <?php
}
