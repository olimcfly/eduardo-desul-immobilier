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
    <div class="page-header">
        <h1><i class="fas fa-calculator page-icon"></i> HUB <span class="page-title-accent">Estimation DVF</span></h1>
        <p>Imports DVF, historique, demandes d’estimation et indicateurs de fiabilité.</p>
    </div>

    <div class="dashboard-grid" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;">
        <div class="card"><div class="card-title">Demandes totales</div><div class="card-value"><?= (int) $stats['requests'] ?></div></div>
        <div class="card"><div class="card-title">Estimations fiables</div><div class="card-value"><?= (int) $stats['ok'] ?></div></div>
        <div class="card"><div class="card-title">Estimations bloquées</div><div class="card-value"><?= (int) $stats['blocked'] ?></div></div>
        <div class="card"><div class="card-title">RDV demandés</div><div class="card-value"><?= (int) $stats['rdv'] ?></div></div>
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
    <?php
}
