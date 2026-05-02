<?php
$pageTitle = 'Biens';
$pageDescription = 'Gérez votre portefeuille de biens immobiliers';

$allowedViews = ['index', 'photos', 'catalogue'];
$view = $_GET['view'] ?? 'index';
if (!in_array($view, $allowedViews, true)) {
    $view = 'index';
}

function biensFetchForMedia(): array
{
    $stmt = db()->query('SELECT id, titre, ville, reference, photo_principale FROM biens ORDER BY created_at DESC LIMIT 200');
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function biensFetchPhotosByBien(array $biens): array
{
    if ($biens === []) {
        return [];
    }

    $ids = array_map(static fn(array $b): int => (int) $b['id'], $biens);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT id, bien_id, chemin, alt, position FROM bien_photos WHERE bien_id IN ($placeholders) ORDER BY position ASC, id ASC");
    $stmt->execute($ids);

    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $photo) {
        $map[(int) $photo['bien_id']][] = $photo;
    }

    return $map;
}

function renderBiensHubCards(): void
{
    ?>
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-house"></i> Portefeuille</div>
            <h1>Vendez plus vite avec des fiches claires</h1>
            <p>Centralisez vos annonces pour gagner du temps à chaque étape.</p>
        </header>


        <div class="hub-modules-grid">
            <a href="/admin/biens/nouveau" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-plus-circle"></i></div>
                    <h3>Ajouter une fiche</h3>
                </div>
                <p>Créez rapidement une annonce complète.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Créer</span>
            </a>

            <a href="/admin?module=biens&view=catalogue" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-list"></i></div>
                    <h3>Suivre le portefeuille</h3>
                </div>
                <p>Retrouvez chaque annonce en quelques secondes.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="/admin?module=biens&view=photos" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-images"></i></div>
                    <h3>Organiser les photos</h3>
                </div>
                <p>Mettez en avant les meilleurs visuels.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <div class="hub-module-card hub-module-card--soon">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-tags"></i></div>
                    <h3>Diffuser plus largement</h3>
                </div>
                <p>Préparez la diffusion de vos annonces sur d'autres canaux.</p>
                <span class="hub-state hub-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
            </div>
        </div>

        <section class="hub-final-cta" aria-label="Progression biens">
            <div>
                <h2>Progression : Créer → Compléter → Illustrer → Diffuser</h2>
                <p>Commencez par un levier, puis développez votre portefeuille.</p>
            </div>
            <a href="/admin/biens/nouveau" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Démarrer</a>
        </section>

    </section>
    <?php
}

function biensFetchCatalogue(): array
{
    $sql = 'SELECT id, titre, ville, prix, type_bien AS type, statut, sort_order, source_provider, source_url, created_at
            FROM biens
            ORDER BY sort_order ASC, created_at DESC
            LIMIT 300';
    $stmt = db()->query($sql);

    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function renderBiensCatalogue(): void
{
    $biens = biensFetchCatalogue();
    ?>
    <style>
        .catalogue-table-wrap{background:#fff;border:1px solid #e8edf3;border-radius:12px;overflow:auto}
        .catalogue-table{width:100%;border-collapse:collapse;min-width:1080px}
        .catalogue-table th,.catalogue-table td{padding:12px 14px;border-bottom:1px solid #eef2f7;text-align:left;font-size:14px}
        .catalogue-table th{font-size:12px;text-transform:uppercase;letter-spacing:.03em;color:#5d6b82;background:#f8fafc}
        .catalogue-badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .catalogue-badge--actif{background:#eafaf1;color:#1e7a46}
        .catalogue-badge--pending{background:#fff7e8;color:#9f5a00}
        .catalogue-badge--vendu{background:#fdecec;color:#a32525}
        .catalogue-badge--archive{background:#eef2f7;color:#475569}
        .catalogue-source{display:inline-flex;align-items:center;gap:6px;color:#64748b;font-size:12px}
        .catalogue-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .catalogue-actions select,.catalogue-actions input{border:1px solid #d8dde6;border-radius:7px;padding:7px 8px;background:#fff}
        .catalogue-actions input{width:72px}
        .catalogue-btn{border:0;border-radius:7px;padding:8px 10px;font-weight:700;cursor:pointer;background:#e8edf3;color:#24364f}
        .catalogue-btn--danger{background:#fee2e2;color:#991b1b}
        .catalogue-btn:hover{filter:brightness(.97)}
        .catalogue-status{margin:0 0 12px;color:#4f5d73;font-size:13px;min-height:18px}
        .catalogue-empty{padding:18px;border:1px dashed #d7deea;border-radius:10px;background:#fff;color:#5d6b82}
        .hub-back{display:inline-flex;gap:8px;align-items:center;margin-bottom:10px}
    </style>

    <a class="hub-back" href="/admin?module=biens"><i class="fas fa-arrow-left"></i> Retour au hub Biens</a>
    <div class="page-header">
        <h1><i class="fas fa-list page-icon"></i> Catalogue des biens</h1>
        <p>Les biens importés restent hors ligne tant que leur statut n'est pas publié.</p>
    </div>
    <p class="catalogue-status" id="catalogueStatus"></p>

    <?php if ($biens === []): ?>
        <div class="catalogue-empty">Aucun bien enregistré pour le moment.</div>
    <?php else: ?>
        <div class="catalogue-table-wrap">
            <table class="catalogue-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Ville</th>
                    <th>Type</th>
                    <th>Prix</th>
                    <th>Source</th>
                    <th>Ordre</th>
                    <th>Statut</th>
                    <th>Ajouté le</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($biens as $bien):
                    $statut = (string) ($bien['statut'] ?? '');
                    $badgeClass = match (mb_strtolower($statut, 'UTF-8')) {
                        'actif' => 'catalogue-badge catalogue-badge--actif',
                        'pending' => 'catalogue-badge catalogue-badge--pending',
                        'vendu'      => 'catalogue-badge catalogue-badge--vendu',
                        'archive'    => 'catalogue-badge catalogue-badge--archive',
                        default      => 'catalogue-badge',
                    };
                    ?>
                    <tr data-bien-row="<?= (int) ($bien['id'] ?? 0) ?>">
                        <td>#<?= (int) ($bien['id'] ?? 0) ?></td>
                        <td><?= e((string) ($bien['titre'] ?? 'Sans titre')) ?></td>
                        <td><?= e((string) ($bien['ville'] ?? '—')) ?></td>
                        <td><?= e((string) ($bien['type'] ?? '—')) ?></td>
                        <td><?= isset($bien['prix']) ? number_format((int) $bien['prix'], 0, ',', ' ') . ' €' : '—' ?></td>
                        <td>
                            <?php if (($bien['source_provider'] ?? '') === 'exp_france'): ?>
                                <span class="catalogue-source">
                                    <i class="fas fa-satellite-dish"></i>
                                    <?php if (!empty($bien['source_url'])): ?>
                                        <a href="<?= e((string) $bien['source_url']) ?>" target="_blank" rel="noopener">eXp France</a>
                                    <?php else: ?>
                                        eXp France
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="catalogue-source">Manuel</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" value="<?= (int) ($bien['sort_order'] ?? 0) ?>" data-sort-input="<?= (int) ($bien['id'] ?? 0) ?>" style="width:72px;border:1px solid #d8dde6;border-radius:7px;padding:7px 8px;">
                        </td>
                        <td><span class="<?= e($badgeClass) ?>" data-status-badge="<?= (int) ($bien['id'] ?? 0) ?>"><?= e($statut !== '' ? $statut : '—') ?></span></td>
                        <td><?= !empty($bien['created_at']) ? e(date('d/m/Y', strtotime((string) $bien['created_at']))) : '—' ?></td>
                        <td>
                            <div class="catalogue-actions">
                                <select data-status-select="<?= (int) ($bien['id'] ?? 0) ?>">
                                    <option value="pending" <?= $statut === 'pending' ? 'selected' : '' ?>>Non publié</option>
                                    <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Publié</option>
                                    <option value="vendu" <?= $statut === 'vendu' ? 'selected' : '' ?>>Vendu</option>
                                    <option value="archive" <?= $statut === 'archive' ? 'selected' : '' ?>>Archivé</option>
                                </select>
                                <button class="catalogue-btn" type="button" data-save="<?= (int) ($bien['id'] ?? 0) ?>">Enregistrer</button>
                                <button class="catalogue-btn catalogue-btn--danger" type="button" data-delete="<?= (int) ($bien['id'] ?? 0) ?>">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        (function () {
            const csrfToken = <?= json_encode(csrfToken(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const statusEl = document.getElementById('catalogueStatus');

            function setStatus(text, error) {
                statusEl.textContent = text || '';
                statusEl.style.color = error ? '#991b1b' : '#166534';
            }

            async function post(payload) {
                const response = await fetch('/admin/api/biens/manage.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ...payload, csrf_token: csrfToken }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Erreur serveur.');
                }
                return data;
            }

            document.querySelectorAll('[data-save]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const id = Number(button.dataset.save);
                    const status = document.querySelector(`[data-status-select="${id}"]`)?.value || 'pending';
                    const sortOrder = Number(document.querySelector(`[data-sort-input="${id}"]`)?.value || 0);
                    button.disabled = true;
                    try {
                        await post({ action: 'status', id, status });
                        await post({ action: 'sort', id, sort_order: sortOrder });
                        const badge = document.querySelector(`[data-status-badge="${id}"]`);
                        if (badge) {
                            badge.textContent = status;
                            badge.className = 'catalogue-badge catalogue-badge--' + status;
                        }
                        setStatus('Bien mis à jour.');
                    } catch (error) {
                        setStatus(error.message, true);
                    } finally {
                        button.disabled = false;
                    }
                });
            });

            document.querySelectorAll('[data-delete]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const id = Number(button.dataset.delete);
                    if (!confirm('Supprimer ce bien de la liste ?')) {
                        return;
                    }
                    button.disabled = true;
                    try {
                        await post({ action: 'delete', id });
                        document.querySelector(`[data-bien-row="${id}"]`)?.remove();
                        setStatus('Bien supprimé.');
                    } catch (error) {
                        setStatus(error.message, true);
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        })();
        </script>
    <?php endif;
}

function renderBiensPhotosManager(): void
{
    $biens = biensFetchForMedia();
    $photosMap = biensFetchPhotosByBien($biens);
    $selectedId = isset($_GET['bien_id']) ? (int) $_GET['bien_id'] : ((int) ($biens[0]['id'] ?? 0));
    $selectedPhotos = $selectedId > 0 ? ($photosMap[$selectedId] ?? []) : [];

    ?>
    <style>
        .media-toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:16px}
        .media-toolbar select,.media-toolbar input,.media-toolbar button{padding:10px;border-radius:8px;border:1px solid #d8dde6}
        .media-toolbar button{background:#f39c12;color:#fff;border:none;font-weight:600;cursor:pointer}
        .media-toolbar button.secondary{background:#2d70b3}
        .media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-top:16px}
        .photo-card{background:#fff;border:1px solid #e8edf3;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04)}
        .photo-card[draggable="true"]{cursor:move}
        .photo-card.dragging{opacity:.45}
        .photo-card img{width:100%;height:130px;object-fit:cover;display:block;background:#f6f7f9}
        .photo-meta{padding:10px;font-size:13px;display:flex;justify-content:space-between;align-items:center;gap:8px}
        .photo-meta button{background:#e74c3c;color:#fff;border:none;border-radius:6px;padding:6px 10px;cursor:pointer}
        .media-empty{padding:24px;background:#fff;border:1px dashed #cfd8e3;border-radius:10px;color:#728095;text-align:center}
        .media-status{font-size:13px;color:#4f5d73;margin-left:auto}
        .hub-back{display:inline-flex;gap:8px;align-items:center;margin-bottom:10px}
    </style>

    <a class="hub-back" href="/admin?module=biens"><i class="fas fa-arrow-left"></i> Retour au hub Biens</a>
    <div class="page-header">
        <h1><i class="fas fa-images page-icon"></i> Médias &amp; photos</h1>
        <p>Importez plusieurs photos, réorganisez l'ordre par glisser-déposer et supprimez les visuels inutiles.</p>
    </div>

    <div class="media-toolbar" id="mediaToolbar">
        <label>
            Bien
            <select id="bienSelect" name="bien_id">
                <?php foreach ($biens as $bien): ?>
                    <option value="<?= (int) $bien['id'] ?>" <?= (int) $bien['id'] === $selectedId ? 'selected' : '' ?>>
                        #<?= (int) $bien['id'] ?> — <?= e((string) $bien['titre']) ?><?= !empty($bien['ville']) ? ' (' . e((string) $bien['ville']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Ajouter des photos
            <input type="file" id="photosInput" multiple accept=".jpg,.jpeg,.png,.webp">
        </label>

        <button type="button" id="uploadBtn"><i class="fas fa-upload"></i> Uploader</button>
        <button type="button" class="secondary" id="saveOrderBtn"><i class="fas fa-sort"></i> Enregistrer l'ordre</button>

        <span class="media-status" id="mediaStatus"></span>
    </div>

    <div id="photoGrid" class="media-grid" data-photos='<?= e((string) json_encode($selectedPhotos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'></div>

    <script>
    (function () {
        const csrfToken = <?= json_encode(csrfToken(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const select = document.getElementById('bienSelect');
        const input = document.getElementById('photosInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const saveOrderBtn = document.getElementById('saveOrderBtn');
        const grid = document.getElementById('photoGrid');
        const statusEl = document.getElementById('mediaStatus');

        let photos = JSON.parse(grid.dataset.photos || '[]');
        let dragId = null;

        function setStatus(text, isError) {
            statusEl.textContent = text || '';
            statusEl.style.color = isError ? '#b42318' : '#4f5d73';
        }

        function render() {
            grid.innerHTML = '';
            if (!photos.length) {
                const empty = document.createElement('div');
                empty.className = 'media-empty';
                empty.textContent = 'Aucune photo pour ce bien.';
                grid.appendChild(empty);
                return;
            }

            photos.forEach((photo) => {
                const card = document.createElement('article');
                card.className = 'photo-card';
                card.draggable = true;
                card.dataset.id = String(photo.id);
                card.innerHTML = `
                    <img src="${photo.chemin}" alt="${photo.alt || ''}">
                    <div class="photo-meta">
                        <span>#${photo.position + 1}</span>
                        <button type="button" data-delete="${photo.id}">Supprimer</button>
                    </div>
                `;

                card.addEventListener('dragstart', () => {
                    dragId = photo.id;
                    card.classList.add('dragging');
                });
                card.addEventListener('dragend', () => {
                    dragId = null;
                    card.classList.remove('dragging');
                });
                card.addEventListener('dragover', (event) => event.preventDefault());
                card.addEventListener('drop', (event) => {
                    event.preventDefault();
                    const targetId = Number(card.dataset.id);
                    if (!dragId || dragId === targetId) {
                        return;
                    }
                    const from = photos.findIndex(p => Number(p.id) === Number(dragId));
                    const to = photos.findIndex(p => Number(p.id) === targetId);
                    if (from < 0 || to < 0) {
                        return;
                    }
                    const moved = photos.splice(from, 1)[0];
                    photos.splice(to, 0, moved);
                    photos = photos.map((p, index) => ({ ...p, position: index }));
                    render();
                });

                grid.appendChild(card);
            });
        }

        async function postData(formData) {
            const response = await fetch('/admin/api/biens/upload', {
                method: 'POST',
                body: formData,
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Erreur inattendue.');
            }

            return payload;
        }

        async function uploadPhotos() {
            if (!input.files.length) {
                setStatus('Sélectionnez au moins une image.', true);
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'upload');
            formData.append('bien_id', select.value);
            Array.from(input.files).forEach((file) => formData.append('photos[]', file));

            setStatus('Upload en cours...');
            try {
                const payload = await postData(formData);
                photos = payload.photos || [];
                input.value = '';
                render();
                setStatus(payload.message || 'Upload terminé.');
            } catch (error) {
                setStatus(error.message, true);
            }
        }

        async function deletePhoto(photoId) {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'delete');
            formData.append('bien_id', select.value);
            formData.append('photo_id', String(photoId));

            try {
                const payload = await postData(formData);
                photos = payload.photos || [];
                render();
                setStatus(payload.message || 'Photo supprimée.');
            } catch (error) {
                setStatus(error.message, true);
            }
        }

        async function saveOrder() {
            if (!photos.length) {
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'reorder');
            formData.append('bien_id', select.value);
            photos.forEach((photo) => formData.append('photo_ids[]', String(photo.id)));

            setStatus('Enregistrement du tri...');
            try {
                const payload = await postData(formData);
                photos = payload.photos || [];
                render();
                setStatus(payload.message || 'Ordre sauvegardé.');
            } catch (error) {
                setStatus(error.message, true);
            }
        }

        select.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('module', 'biens');
            url.searchParams.set('view', 'photos');
            url.searchParams.set('bien_id', select.value);
            window.location.href = url.toString();
        });

        uploadBtn.addEventListener('click', uploadPhotos);
        saveOrderBtn.addEventListener('click', saveOrder);
        grid.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-delete]');
            if (!button) {
                return;
            }
            const photoId = Number(button.getAttribute('data-delete'));
            if (photoId && window.confirm('Supprimer cette photo ?')) {
                deletePhoto(photoId);
            }
        });

        render();
    })();
    </script>
    <?php
}

function renderContent() {
    global $view;

    if ($view === 'photos') {
        renderBiensPhotosManager();
        return;
    }

    if ($view === 'catalogue') {
        renderBiensCatalogue();
        return;
    }

    renderBiensHubCards();
}
