<?php
$pageTitle = 'Scraping eXp France';
$pageDescription = 'Importez des biens depuis la plateforme eXp France';

function renderContent(): void
{
    $csrfToken = csrfToken();
    ?>
    <style>
        /* ── Layout ── */
        .scrap-page { display: flex; flex-direction: column; gap: 20px; }
        .page-header { margin-bottom: 4px; }

        /* ── Barre de recherche ── */
        .scrap-search-bar {
            background: #fff;
            border: 1px solid #e3e8ef;
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .scrap-field { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 160px; }
        .scrap-field label { font-size: 12px; font-weight: 600; color: #6b7a8d; text-transform: uppercase; letter-spacing: .4px; }
        .scrap-field input, .scrap-field select {
            padding: 10px 14px; border: 1px solid #d1d9e6; border-radius: 8px;
            font-size: 14px; color: #1a2b45; background: #f9fafc;
            transition: border-color .2s;
        }
        .scrap-field input:focus, .scrap-field select:focus { border-color: #2d70b3; outline: none; background: #fff; }
        .scrap-btn-search {
            padding: 10px 22px; background: #2d70b3; color: #fff; border: none;
            border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; gap: 8px; white-space: nowrap;
            transition: background .2s;
        }
        .scrap-btn-search:hover { background: #1b4f72; }
        .scrap-btn-search:disabled { background: #9ab4cc; cursor: not-allowed; }

        /* ── Actions barre ── */
        .scrap-actions {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            padding: 12px 16px; background: #eaf2fb; border-radius: 10px;
            border: 1px solid #b3d1ec;
        }
        .scrap-count { font-size: 14px; color: #2d70b3; font-weight: 600; flex: 1; }
        .scrap-btn {
            padding: 9px 18px; border: none; border-radius: 8px; font-weight: 600;
            font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 7px;
            transition: background .2s, opacity .2s;
        }
        .scrap-btn:disabled { opacity: .45; cursor: not-allowed; }
        .scrap-btn-own  { background: #27ae60; color: #fff; }
        .scrap-btn-own:hover:not(:disabled)  { background: #1e8449; }
        .scrap-btn-share { background: #f39c12; color: #fff; }
        .scrap-btn-share:hover:not(:disabled) { background: #d68910; }
        .scrap-btn-all  { background: #fff; color: #2d70b3; border: 1px solid #2d70b3; }
        .scrap-btn-all:hover { background: #eaf2fb; }

        /* ── Grille résultats ── */
        .scrap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        /* ── Carte bien ── */
        .scrap-card {
            background: #fff;
            border: 2px solid #e3e8ef;
            border-radius: 12px;
            overflow: hidden;
            display: flex; flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: border-color .2s, box-shadow .2s;
            position: relative;
        }
        .scrap-card.selected { border-color: #2d70b3; box-shadow: 0 0 0 3px rgba(45,112,179,.15); }
        .scrap-card.already-imported { border-color: #b0bec5; opacity: .7; }
        .scrap-card-img {
            width: 100%; height: 170px; object-fit: cover;
            background: #f0f3f7; display: block;
        }
        .scrap-card-img-placeholder {
            width: 100%; height: 170px; background: #f0f3f7;
            display: flex; align-items: center; justify-content: center;
            color: #b0bec5; font-size: 32px;
        }
        .scrap-card-body { padding: 14px 14px 10px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
        .scrap-card-title { font-size: 14px; font-weight: 700; color: #1a2b45; line-height: 1.3; }
        .scrap-card-meta { font-size: 12px; color: #6b7a8d; display: flex; flex-wrap: wrap; gap: 8px; }
        .scrap-card-meta span { display: flex; align-items: center; gap: 3px; }
        .scrap-card-price { font-size: 16px; font-weight: 800; color: #2d70b3; margin-top: auto; padding-top: 8px; }
        .scrap-card-agent { font-size: 11px; color: #9aa5b4; border-top: 1px solid #f0f3f7; padding-top: 8px; margin-top: 4px; }
        .scrap-card-footer {
            padding: 10px 14px; background: #f9fafc; border-top: 1px solid #e8edf3;
            display: flex; align-items: center; gap: 10px;
        }
        .scrap-checkbox { width: 18px; height: 18px; accent-color: #2d70b3; cursor: pointer; flex-shrink: 0; }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .3px;
        }
        .badge-own   { background: #eafaf1; color: #1e8449; }
        .badge-share { background: #fef9e7; color: #d68910; }
        .badge-done  { background: #e8edf3; color: #6b7a8d; }
        .badge-type  { background: #eaf2fb; color: #1b4f72; }

        /* ── Badge position absolue ── */
        .scrap-card .card-badge-abs {
            position: absolute; top: 10px; left: 10px; z-index: 2;
        }
        .scrap-card .card-imported-abs {
            position: absolute; top: 10px; right: 10px; z-index: 2;
        }

        /* ── État vide / chargement ── */
        .scrap-empty {
            grid-column: 1 / -1; text-align: center;
            padding: 48px 24px; color: #9aa5b4;
            background: #fff; border-radius: 12px; border: 1px dashed #d1d9e6;
        }
        .scrap-empty i { font-size: 40px; margin-bottom: 12px; display: block; }
        .scrap-spinner { display: none; }
        .scrap-spinner.active { display: flex; grid-column: 1/-1; justify-content: center; padding: 40px; }

        /* ── Toast ── */
        #scrap-toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
            color: #fff; box-shadow: 0 4px 20px rgba(0,0,0,.2);
            display: none; align-items: center; gap: 10px;
            max-width: 360px;
        }
        #scrap-toast.show { display: flex; }
        #scrap-toast.success { background: #27ae60; }
        #scrap-toast.error   { background: #e74c3c; }
        #scrap-toast.info    { background: #2d70b3; }
    </style>

    <div class="scrap-page">

        <div class="page-header">
            <h1><i class="fas fa-satellite-dish page-icon"></i> Scraping <span class="page-title-accent">eXp France</span></h1>
            <p>Recherchez des biens sur la plateforme eXp France, sélectionnez-les et importez-les dans votre catalogue.</p>
        </div>

        <!-- Barre de recherche -->
        <div class="scrap-search-bar">
            <div class="scrap-field">
                <label for="scrap-ville"><i class="fas fa-map-marker-alt"></i> Ville</label>
                <input type="text" id="scrap-ville" placeholder="Ex : Aix-en-Provence, Marseille…">
            </div>
            <div class="scrap-field">
                <label for="scrap-agent"><i class="fas fa-user-tie"></i> Agent</label>
                <input type="text" id="scrap-agent" placeholder="Nom de l'agent…">
            </div>
            <div class="scrap-field" style="max-width:160px;">
                <label for="scrap-type"><i class="fas fa-home"></i> Type</label>
                <select id="scrap-type">
                    <option value="">Tous</option>
                    <option value="Maison">Maison</option>
                    <option value="Appartement">Appartement</option>
                    <option value="Terrain">Terrain</option>
                    <option value="Commerce">Commerce</option>
                    <option value="Bureau">Bureau</option>
                </select>
            </div>
            <button class="scrap-btn-search" id="scrap-search-btn">
                <i class="fas fa-magnifying-glass"></i> Rechercher
            </button>
        </div>

        <!-- Actions (masquées par défaut) -->
        <div class="scrap-actions" id="scrap-actions" style="display:none;">
            <span class="scrap-count" id="scrap-count-label">0 bien(s) trouvé(s)</span>
            <button class="scrap-btn scrap-btn-all" id="scrap-select-all-btn">
                <i class="fas fa-check-double"></i> Tout sélectionner
            </button>
            <button class="scrap-btn scrap-btn-own" id="scrap-import-own-btn" disabled>
                <i class="fas fa-plus-circle"></i> Importer (<span id="scrap-selected-count">0</span>) comme mes biens
            </button>
            <button class="scrap-btn scrap-btn-share" id="scrap-import-share-btn" disabled>
                <i class="fas fa-handshake"></i> Importer en partage
            </button>
        </div>

        <!-- Grille résultats -->
        <div class="scrap-grid" id="scrap-grid">
            <div class="scrap-empty">
                <i class="fas fa-satellite-dish"></i>
                Lancez une recherche par ville ou par agent pour voir les biens disponibles.
            </div>
        </div>

    </div>

    <div id="scrap-toast"></div>

    <script>
    (function () {
        const csrf = <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>;
        const searchBtn    = document.getElementById('scrap-search-btn');
        const villeInput   = document.getElementById('scrap-ville');
        const agentInput   = document.getElementById('scrap-agent');
        const typeSelect   = document.getElementById('scrap-type');
        const grid         = document.getElementById('scrap-grid');
        const actionsBar   = document.getElementById('scrap-actions');
        const countLabel   = document.getElementById('scrap-count-label');
        const selectedSpan = document.getElementById('scrap-selected-count');
        const btnSelectAll = document.getElementById('scrap-select-all-btn');
        const btnOwn       = document.getElementById('scrap-import-own-btn');
        const btnShare     = document.getElementById('scrap-import-share-btn');
        const toast        = document.getElementById('scrap-toast');

        let allBiens  = [];
        let selectedIds = new Set();
        let toastTimer = null;

        /* ─── Toast ─── */
        function showToast(msg, type = 'info') {
            clearTimeout(toastTimer);
            toast.textContent = msg;
            toast.className = 'show ' + type;
            toastTimer = setTimeout(() => { toast.className = ''; }, 4000);
        }

        /* ─── Formatage prix ─── */
        function fmtPrice(p) {
            if (!p) return '—';
            return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(p);
        }

        /* ─── Mise à jour compteurs et boutons ─── */
        function updateSelectionUI() {
            const n = selectedIds.size;
            selectedSpan.textContent = String(n);
            btnOwn.disabled  = n === 0;
            btnShare.disabled = n === 0;

            // Sync checkboxes
            grid.querySelectorAll('.scrap-checkbox').forEach(cb => {
                const card = cb.closest('.scrap-card');
                const id   = cb.dataset.id;
                const sel  = selectedIds.has(id);
                cb.checked = sel;
                if (sel) card.classList.add('selected');
                else     card.classList.remove('selected');
            });

            // Bouton tout sélectionner
            const selectable = allBiens.filter(b => !b.already_imported);
            const allSel = selectable.length > 0 && selectable.every(b => selectedIds.has(b.id));
            btnSelectAll.innerHTML = allSel
                ? '<i class="fas fa-times"></i> Tout désélectionner'
                : '<i class="fas fa-check-double"></i> Tout sélectionner';
        }

        /* ─── Rendu d'une carte ─── */
        function renderCard(bien) {
            const card = document.createElement('article');
            card.className = 'scrap-card' + (bien.already_imported ? ' already-imported' : '');
            card.dataset.id = bien.id;

            const imgHtml = bien.cover_url
                ? `<img class="scrap-card-img" src="${bien.cover_url}" alt="${bien.titre}" loading="lazy">`
                : `<div class="scrap-card-img-placeholder"><i class="fas fa-image"></i></div>`;

            const typeLabel = bien.property_type || '';
            const agent = [bien.agent_first_name, bien.agent_last_name].filter(Boolean).join(' ');

            const importedBadge = bien.already_imported
                ? `<span class="badge badge-done card-imported-abs"><i class="fas fa-check"></i> Importé</span>`
                : '';

            const sourceLabel = bien.imported_source === 'partage'
                ? `<span class="badge badge-share card-badge-abs"><i class="fas fa-handshake"></i> Partagé</span>`
                : (bien.already_imported
                    ? `<span class="badge badge-own card-badge-abs"><i class="fas fa-star"></i> Mes biens</span>`
                    : '');

            card.innerHTML = `
                ${sourceLabel}
                ${importedBadge}
                ${imgHtml}
                <div class="scrap-card-body">
                    <div class="scrap-card-title">${bien.titre}</div>
                    <div class="scrap-card-meta">
                        ${typeLabel ? `<span><i class="fas fa-home"></i> ${typeLabel}</span>` : ''}
                        ${bien.surface ? `<span><i class="fas fa-ruler-combined"></i> ${bien.surface} m²</span>` : ''}
                        ${bien.pieces ? `<span><i class="fas fa-door-open"></i> ${bien.pieces} pièces</span>` : ''}
                        ${bien.chambres ? `<span><i class="fas fa-bed"></i> ${bien.chambres} ch.</span>` : ''}
                        ${bien.ville ? `<span><i class="fas fa-map-marker-alt"></i> ${bien.ville}</span>` : ''}
                    </div>
                    <div class="scrap-card-price">${fmtPrice(bien.prix)}</div>
                    ${agent ? `<div class="scrap-card-agent"><i class="fas fa-user-tie"></i> ${agent}</div>` : ''}
                </div>
                <div class="scrap-card-footer">
                    <input type="checkbox" class="scrap-checkbox" data-id="${bien.id}" ${bien.already_imported ? 'disabled' : ''}>
                    <span style="font-size:12px;color:#6b7a8d;">${bien.nb_photos || 0} photo(s)</span>
                    ${bien.reference ? `<span style="margin-left:auto;font-size:11px;color:#9aa5b4;">Réf. ${bien.reference}</span>` : ''}
                </div>
            `;

            // Toggle sélection
            card.addEventListener('click', (e) => {
                if (e.target.tagName === 'INPUT' || bien.already_imported) return;
                const cb = card.querySelector('.scrap-checkbox');
                if (!cb) return;
                cb.checked = !cb.checked;
                toggleSelect(bien.id, cb.checked);
            });
            card.querySelector('.scrap-checkbox')?.addEventListener('change', (e) => {
                toggleSelect(bien.id, e.target.checked);
            });

            return card;
        }

        function toggleSelect(id, selected) {
            if (selected) selectedIds.add(id);
            else selectedIds.delete(id);
            updateSelectionUI();
        }

        /* ─── Rendu de la grille ─── */
        function renderGrid(biens) {
            grid.innerHTML = '';
            selectedIds.clear();

            if (!biens.length) {
                grid.innerHTML = '<div class="scrap-empty"><i class="fas fa-search-minus"></i>Aucun bien trouvé pour cette recherche.</div>';
                actionsBar.style.display = 'none';
                return;
            }

            actionsBar.style.display = 'flex';
            const total = biens.length;
            const imported = biens.filter(b => b.already_imported).length;
            countLabel.textContent = `${total} bien(s) trouvé(s)${imported ? ` · ${imported} déjà importé(s)` : ''}`;

            biens.forEach(b => grid.appendChild(renderCard(b)));
            updateSelectionUI();
        }

        /* ─── Recherche ─── */
        async function doSearch() {
            const ville = villeInput.value.trim();
            const agent = agentInput.value.trim();
            const type  = typeSelect.value;

            if (!ville && !agent) {
                showToast('Saisissez une ville ou un agent.', 'error');
                return;
            }

            searchBtn.disabled = true;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche…';
            grid.innerHTML = '<div class="scrap-spinner active" style="display:flex;"><i class="fas fa-spinner fa-spin" style="font-size:32px;color:#2d70b3;"></i></div>';
            actionsBar.style.display = 'none';

            try {
                const resp = await fetch('/admin/api/scraping/search.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrf, ville, agent, type }),
                });
                const rawText = await resp.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (e) {
                    console.error('Réponse non-JSON reçue:', rawText.substring(0, 500));
                    throw new Error('Le serveur a retourné une réponse invalide. Vérifiez la console.');
                }

                if (!resp.ok || !data.success) {
                    throw new Error(data.message || 'Erreur serveur');
                }

                allBiens = data.biens;
                renderGrid(allBiens);
            } catch (err) {
                grid.innerHTML = `<div class="scrap-empty"><i class="fas fa-exclamation-triangle"></i>${err.message}</div>`;
            } finally {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="fas fa-magnifying-glass"></i> Rechercher';
            }
        }

        /* ─── Import ─── */
        async function doImport(source) {
            if (selectedIds.size === 0) return;
            const ids = Array.from(selectedIds);
            const label = source === 'own' ? 'mes biens' : 'biens partagés';

            btnOwn.disabled  = true;
            btnShare.disabled = true;

            try {
                const resp = await fetch('/admin/api/scraping/import.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrf, ids, source }),
                });
                const data = await resp.json();

                if (!resp.ok || !data.success) {
                    throw new Error(data.message || 'Erreur import');
                }

                showToast(`✓ ${data.imported} bien(s) importé(s) dans ${label}.`, 'success');

                // Mettre à jour les cartes importées
                ids.forEach(id => {
                    const bien = allBiens.find(b => b.id === id);
                    if (bien) {
                        bien.already_imported = true;
                        bien.imported_source = source;
                    }
                });
                selectedIds.clear();
                renderGrid(allBiens);
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                updateSelectionUI();
            }
        }

        /* ─── Événements ─── */
        searchBtn.addEventListener('click', doSearch);
        villeInput.addEventListener('keydown', e => e.key === 'Enter' && doSearch());
        agentInput.addEventListener('keydown', e => e.key === 'Enter' && doSearch());

        btnSelectAll.addEventListener('click', () => {
            const selectable = allBiens.filter(b => !b.already_imported);
            const allSel = selectable.every(b => selectedIds.has(b.id));
            if (allSel) {
                selectedIds.clear();
            } else {
                selectable.forEach(b => selectedIds.add(b.id));
            }
            updateSelectionUI();
        });

        btnOwn.addEventListener('click',   () => doImport('own'));
        btnShare.addEventListener('click', () => doImport('partage'));

    })();
    </script>
    <?php
}
