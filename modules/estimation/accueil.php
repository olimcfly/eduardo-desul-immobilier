<?php
require_once ROOT_PATH . '/core/services/InstantEstimationService.php';

$pageTitle = 'Estimation DVF';
$pageDescription = 'Pilotage du système d’estimation instantanée et des demandes RDV.';

function renderContent() {
    $stats = [
        'requests' => 0,
        'ok' => 0,
        'blocked' => 0,
        'rdv' => 0,
    ];

    try {
        $stats['requests'] = (int) db()->query('SELECT COUNT(*) FROM estimation_requests')->fetchColumn();
        $stats['ok'] = (int) db()->query("SELECT COUNT(*) FROM estimation_requests WHERE status = 'ok'")->fetchColumn();
        $stats['blocked'] = (int) db()->query("SELECT COUNT(*) FROM estimation_requests WHERE status IN ('insufficient_data','low_reliability')")->fetchColumn();
        $stats['rdv'] = (int) db()->query("SELECT COUNT(*) FROM estimation_requests WHERE status = 'rdv_requested'")->fetchColumn();
    } catch (Throwable $e) {
        // tableau vide si tables non encore remplies
    }

    $latestImports = [];
    $latestRequests = [];
    $mapRequests = [];
    $freeEstimateStats = [
        'total' => 0,
        'with_coords' => 0,
        'sectors' => 0,
    ];
    $freeEstimatePoints = [];
    $freeEstimateSectors = [];
    $googleMapsApiKey = trim((string) setting('api_google_maps', ''));

    try {
        $latestImports = db()->query('SELECT * FROM dvf_import_jobs ORDER BY created_at DESC LIMIT 10')->fetchAll();
    } catch (Throwable $e) {}

    try {
        $latestRequests = db()->query('SELECT * FROM estimation_requests ORDER BY created_at DESC LIMIT 20')->fetchAll();
    } catch (Throwable $e) {}

    try {
        $mapRequests = db()->query("
            SELECT id, created_at, address_normalized, address_input, property_type, status, lat, lng
            FROM estimation_requests
            WHERE lat IS NOT NULL AND lng IS NOT NULL
            ORDER BY created_at DESC
            LIMIT 500
        ")->fetchAll();
    } catch (Throwable $e) {}

    try {
        $freeEstimateStats['total'] = (int) db()->query('SELECT COUNT(*) FROM estimation_zones')->fetchColumn();
        $freeEstimateStats['with_coords'] = (int) db()->query('SELECT COUNT(*) FROM estimation_zones WHERE lat IS NOT NULL AND lng IS NOT NULL')->fetchColumn();
        $freeEstimateStats['sectors'] = (int) db()->query('SELECT COUNT(DISTINCT localite) FROM estimation_zones')->fetchColumn();

        $freeEstimateRows = db()->query("
            SELECT
                localite,
                type_bien,
                projet,
                COUNT(*) AS total,
                AVG(CAST(surface AS UNSIGNED)) AS surface_avg,
                AVG(CAST(NULLIF(budget, '') AS UNSIGNED)) AS budget_avg,
                MAX(created_at) AS latest_at,
                AVG(lat) AS lat,
                AVG(lng) AS lng
            FROM estimation_zones
            GROUP BY localite, type_bien, projet
            ORDER BY latest_at DESC
            LIMIT 300
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($freeEstimateRows as $row) {
            $lat = isset($row['lat']) ? (float) $row['lat'] : 0.0;
            $lng = isset($row['lng']) ? (float) $row['lng'] : 0.0;
            $item = [
                'localite' => (string) ($row['localite'] ?? ''),
                'type_bien' => (string) ($row['type_bien'] ?? ''),
                'projet' => (string) ($row['projet'] ?? ''),
                'total' => (int) ($row['total'] ?? 0),
                'surface_avg' => round((float) ($row['surface_avg'] ?? 0)),
                'budget_avg' => round((float) ($row['budget_avg'] ?? 0)),
                'latest_at' => (string) ($row['latest_at'] ?? ''),
                'lat' => $lat,
                'lng' => $lng,
            ];

            $freeEstimateSectors[] = $item;
            if ($lat !== 0.0 && $lng !== 0.0) {
                $freeEstimatePoints[] = $item;
            }
        }
    } catch (Throwable $e) {
        error_log('[estimation] estimation_zones map: ' . $e->getMessage());
    }

    $mapPoints = array_values(array_filter(array_map(static function (array $row): ?array {
        $lat = isset($row['lat']) ? (float) $row['lat'] : 0.0;
        $lng = isset($row['lng']) ? (float) $row['lng'] : 0.0;
        if ($lat === 0.0 || $lng === 0.0) {
            return null;
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'lat' => $lat,
            'lng' => $lng,
            'status' => (string) ($row['status'] ?? ''),
            'property_type' => (string) ($row['property_type'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'address' => (string) (($row['address_normalized'] ?? '') !== '' ? $row['address_normalized'] : ($row['address_input'] ?? '')),
        ];
    }, $mapRequests)));
    ?>
    <style>
        .start-hero { background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 16px; padding: 36px 40px; color: #fff; margin-bottom: 32px; box-shadow: 0 4px 20px rgba(15,34,55,.18); }
        .start-hero-badge { display: inline-block; background: rgba(201,168,76,.2); color: #c9a84c; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; margin-bottom: 14px; border: 1px solid rgba(201,168,76,.35); }
        .start-hero h1 { font-size: 28px; font-weight: 700; color: #fff; margin: 0 0 12px; line-height: 1.25; }
        .start-hero p { font-size: 15px; color: rgba(255,255,255,.7); line-height: 1.65; max-width: 720px; margin: 0; }
        .start-cta { background: #fff; border-radius: 12px; padding: 24px 26px; box-shadow: 0 1px 6px rgba(0,0,0,.07); display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-top: 16px; }
        .start-cta-text strong { display: block; font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .start-cta-text span { font-size: 13px; color: #64748b; }
        .start-cta-btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: #c9a84c; color: #0f2237; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        @media (max-width: 600px) { .start-hero { padding: 24px 20px; } }
    </style>

    <div class="start-hero">
        <div class="start-hero-badge">Pilotage estimation</div>
        <h1>HUB Estimation DVF</h1>
        <p>Imports DVF, historique, demandes d’estimation, carte des secteurs et indicateurs de fiabilité.</p>
    </div>

    <div class="dashboard-grid" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;">
        <div class="card"><div class="card-title">Demandes totales</div><div class="card-value"><?= (int) $stats['requests'] ?></div></div>
        <div class="card"><div class="card-title">Estimations fiables</div><div class="card-value"><?= (int) $stats['ok'] ?></div></div>
        <div class="card"><div class="card-title">Estimations bloquées</div><div class="card-value"><?= (int) $stats['blocked'] ?></div></div>
        <div class="card"><div class="card-title">RDV demandés</div><div class="card-value"><?= (int) $stats['rdv'] ?></div></div>
    </div>

    <div class="card" style="margin-top:16px;padding:16px;">
        <h3>Carte France des estimations gratuites</h3>
        <p style="margin:8px 0 14px;color:#6b7280;">
            Toutes les estimations lancées depuis le formulaire gratuit, même sans coordonnées de contact.
        </p>
        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:14px;">
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;">
                <div style="font-size:12px;color:#1d4ed8;font-weight:700;">Estimations</div>
                <div style="font-size:26px;font-weight:800;color:#1e3a8a;"><?= (int) $freeEstimateStats['total'] ?></div>
            </div>
            <div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:10px;padding:12px;">
                <div style="font-size:12px;color:#047857;font-weight:700;">Avec position</div>
                <div style="font-size:26px;font-weight:800;color:#065f46;"><?= (int) $freeEstimateStats['with_coords'] ?></div>
            </div>
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px;">
                <div style="font-size:12px;color:#b45309;font-weight:700;">Secteurs</div>
                <div style="font-size:26px;font-weight:800;color:#92400e;"><?= (int) $freeEstimateStats['sectors'] ?></div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:minmax(0,1.3fr) minmax(280px,.7fr);gap:16px;align-items:start;">
            <div id="free-estimate-france-map" style="position:relative;height:520px;border-radius:14px;overflow:hidden;border:1px solid #dbe3ee;background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 45%,#f8fafc 100%);">
                <div style="position:absolute;inset:18px;border:2px dashed rgba(30,58,138,.18);border-radius:46% 42% 50% 38%;transform:rotate(-6deg);background:rgba(255,255,255,.35);"></div>
                <div style="position:absolute;left:48%;top:48%;transform:translate(-50%,-50%);font-size:88px;font-weight:800;color:rgba(30,58,138,.06);letter-spacing:.05em;">FRANCE</div>
                <div id="free-estimate-empty" style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;color:#64748b;text-align:center;padding:24px;">
                    Aucun point géolocalisé pour le moment. Les prochaines estimations avec ville sélectionnée apparaîtront ici.
                </div>
            </div>
            <div style="max-height:520px;overflow:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                <div style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-weight:700;color:#1f2937;">Secteurs récents</div>
                <?php if (!$freeEstimateSectors): ?>
                    <div style="padding:16px;color:#6b7280;">Aucune estimation gratuite enregistrée.</div>
                <?php else: foreach (array_slice($freeEstimateSectors, 0, 40) as $sector): ?>
                    <div style="padding:12px 14px;border-bottom:1px solid #f1f5f9;">
                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:start;">
                            <strong style="color:#111827;"><?= e($sector['localite']) ?></strong>
                            <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:2px 8px;font-size:12px;font-weight:700;"><?= (int) $sector['total'] ?></span>
                        </div>
                        <div style="font-size:12px;color:#64748b;margin-top:4px;">
                            <?= e(ucfirst($sector['type_bien'])) ?> · <?= e(str_replace('_', ' ', $sector['projet'])) ?>
                            <?php if ((int) $sector['surface_avg'] > 0): ?> · <?= (int) $sector['surface_avg'] ?> m²<?php endif; ?>
                            <?php if ((int) $sector['budget_avg'] > 0): ?> · <?= number_format((int) $sector['budget_avg'], 0, ',', ' ') ?> €<?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px;padding:16px;">
        <h3>Historique imports DVF</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead><tr><th align="left">Date</th><th align="left">Fichier</th><th align="left">Statut</th><th align="right">Valides</th><th align="right">Rejetées</th></tr></thead>
            <tbody>
            <?php if (!$latestImports): ?>
                <tr><td colspan="5" style="padding:8px 0;color:#6b7280;">Aucun import DVF enregistré.</td></tr>
            <?php else: foreach ($latestImports as $row): ?>
                <tr>
                    <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                    <td><?= e((string) ($row['source_file'] ?? '')) ?></td>
                    <td><?= e((string) ($row['status'] ?? '')) ?></td>
                    <td align="right"><?= (int) ($row['rows_valid'] ?? 0) ?></td>
                    <td align="right"><?= (int) ($row['rows_rejected'] ?? 0) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px;padding:16px;">
        <h3>Demandes d’estimation (20 dernières)</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr><th align="left">Date</th><th align="left">Adresse</th><th align="left">Type</th><th align="right">Surface</th><th align="right">Comp.</th><th align="left">Statut</th></tr>
            </thead>
            <tbody>
            <?php if (!$latestRequests): ?>
                <tr><td colspan="6" style="padding:8px 0;color:#6b7280;">Aucune demande.</td></tr>
            <?php else: foreach ($latestRequests as $row): ?>
                <tr>
                    <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                    <td><?= e((string) ($row['address_normalized'] ?: $row['address_input'] ?? '')) ?></td>
                    <td><?= e((string) ($row['property_type'] ?? '')) ?></td>
                    <td align="right"><?= (int) ($row['surface'] ?? 0) ?> m²</td>
                    <td align="right"><?= (int) ($row['comparables_count'] ?? 0) ?></td>
                    <td><?= e((string) ($row['status'] ?? '')) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px;padding:16px;">
        <h3>Carte des demandes d’estimation</h3>
        <?php if ($googleMapsApiKey === ''): ?>
            <p style="margin-top:8px;color:#b45309;">
                Ajoutez une clé Google Maps JS dans Paramètres → API (`api_google_maps`) pour activer la carte.
            </p>
        <?php else: ?>
            <p style="margin-top:8px;color:#6b7280;">
                <?= count($mapPoints) ?> point(s) géolocalisé(s) affichés (clusterisation active).
            </p>
            <div id="estimation-map" style="margin-top:12px;height:460px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;background:#f8fafc;"></div>
        <?php endif; ?>
    </div>

    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Besoin de traiter les demandes ?</strong>
            <span>Consultez les dernières estimations puis utilisez les statuts pour prioriser les relances.</span>
        </div>
        <a href="/admin/?module=estimation" class="start-cta-btn">
            <i class="fas fa-rotate"></i> Actualiser le hub
        </a>
    </div>

    <?php if ($googleMapsApiKey !== ''): ?>
    <script>
    (function() {
        const points = <?= json_encode($mapPoints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const mapContainer = document.getElementById('estimation-map');

        if (!mapContainer) return;
        if (!Array.isArray(points) || points.length === 0) {
            mapContainer.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#6b7280;">Aucun point avec coordonnées lat/lng à afficher.</div>';
            return;
        }

        const statusLabels = {
            ok: 'Estimation fiable',
            insufficient_data: 'Données insuffisantes',
            low_reliability: 'Fiabilité faible',
            rdv_requested: 'RDV demandé'
        };

        const statusColors = {
            ok: '#16a34a',
            insufficient_data: '#dc2626',
            low_reliability: '#ea580c',
            rdv_requested: '#2563eb'
        };
        const escapeHtml = (value) => String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        window.initEstimationDashboardMap = function() {
            const fallbackCenter = { lat: points[0].lat, lng: points[0].lng };
            const map = new google.maps.Map(mapContainer, {
                center: fallbackCenter,
                zoom: 11,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false
            });

            const infoWindow = new google.maps.InfoWindow();
            const bounds = new google.maps.LatLngBounds();

            const markers = points.map((point) => {
                const color = statusColors[point.status] || '#374151';
                const marker = new google.maps.Marker({
                    position: { lat: point.lat, lng: point.lng },
                    title: point.address || 'Demande d’estimation',
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: color,
                        fillOpacity: 0.95,
                        strokeColor: '#ffffff',
                        strokeWeight: 1,
                        scale: 7
                    }
                });

                marker.addListener('click', () => {
                    const statusText = statusLabels[point.status] || (point.status || '—');
                    infoWindow.setContent(
                        '<div style="font-size:13px;line-height:1.45;min-width:220px;">'
                        + '<strong>' + escapeHtml(point.address || 'Adresse non renseignée') + '</strong><br>'
                        + '<span>Type : ' + escapeHtml(point.property_type || '—') + '</span><br>'
                        + '<span>Statut : ' + escapeHtml(statusText) + '</span><br>'
                        + '<span>Créée le : ' + escapeHtml(point.created_at || '—') + '</span>'
                        + '</div>'
                    );
                    infoWindow.open({ map, anchor: marker });
                });

                bounds.extend(marker.getPosition());
                return marker;
            });

            if (markers.length === 1) {
                map.setCenter(markers[0].getPosition());
                map.setZoom(14);
            } else {
                map.fitBounds(bounds, 60);
            }

            if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
                new window.markerClusterer.MarkerClusterer({ map, markers });
            }
        };
    })();
    </script>
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= e($googleMapsApiKey) ?>&callback=initEstimationDashboardMap" async defer></script>
    <?php endif; ?>
    <script>
    (function () {
        const points = <?= json_encode($freeEstimatePoints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const map = document.getElementById('free-estimate-france-map');
        const empty = document.getElementById('free-estimate-empty');
        if (!map) return;
        if (!Array.isArray(points) || points.length === 0) {
            if (empty) empty.style.display = 'flex';
            return;
        }

        const bounds = { minLat: 41.0, maxLat: 51.5, minLng: -5.5, maxLng: 9.8 };
        const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
        const escapeHtml = (value) => String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        points.forEach((point) => {
            const lat = Number(point.lat || 0);
            const lng = Number(point.lng || 0);
            if (!lat || !lng) return;

            const x = clamp(((lng - bounds.minLng) / (bounds.maxLng - bounds.minLng)) * 100, 4, 96);
            const y = clamp((1 - ((lat - bounds.minLat) / (bounds.maxLat - bounds.minLat))) * 100, 4, 96);
            const size = clamp(14 + Number(point.total || 1) * 3, 16, 42);

            const marker = document.createElement('button');
            marker.type = 'button';
            marker.style.cssText = [
                'position:absolute',
                'left:' + x + '%',
                'top:' + y + '%',
                'width:' + size + 'px',
                'height:' + size + 'px',
                'transform:translate(-50%,-50%)',
                'border-radius:999px',
                'border:2px solid #fff',
                'background:#1d4ed8',
                'color:#fff',
                'font-size:11px',
                'font-weight:800',
                'box-shadow:0 8px 20px rgba(29,78,216,.28)',
                'cursor:pointer',
                'z-index:2'
            ].join(';');
            marker.textContent = String(point.total || 1);
            marker.title = point.localite || 'Estimation';
            marker.setAttribute('aria-label', marker.title);

            const tooltip = document.createElement('div');
            tooltip.style.cssText = 'display:none;position:absolute;left:50%;bottom:calc(100% + 8px);transform:translateX(-50%);min-width:210px;background:#0f172a;color:#fff;padding:10px 12px;border-radius:8px;font-size:12px;line-height:1.45;text-align:left;box-shadow:0 12px 30px rgba(15,23,42,.25);';
            tooltip.innerHTML =
                '<strong>' + escapeHtml(point.localite) + '</strong><br>'
                + escapeHtml(point.type_bien) + ' · ' + escapeHtml(String(point.projet || '').replaceAll('_', ' ')) + '<br>'
                + 'Estimations : ' + escapeHtml(point.total);
            marker.appendChild(tooltip);
            marker.addEventListener('mouseenter', () => { tooltip.style.display = 'block'; });
            marker.addEventListener('mouseleave', () => { tooltip.style.display = 'none'; });
            marker.addEventListener('focus', () => { tooltip.style.display = 'block'; });
            marker.addEventListener('blur', () => { tooltip.style.display = 'none'; });

            map.appendChild(marker);
        });
    })();
    </script>
    <?php
}
