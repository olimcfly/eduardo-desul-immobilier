<?php
if (!defined('ADMIN_ROUTER')) {
    die('Accès direct interdit');
}

$page_title = "Éditeur d'images IA";
ob_start();
?>

<style>
.image-editor-wrap { max-width: 1200px; margin: 0 auto; }
.ie-grid { display: grid; grid-template-columns: 340px 1fr; gap: 18px; }
.ie-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 8px 24px rgba(15,23,42,.06); }
.ie-card h3 { margin: 0; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
.ie-card-body { padding: 14px 16px; }
.ie-row { margin-bottom: 12px; }
.ie-row label { display: block; font-size: 12px; color: #475569; margin-bottom: 5px; font-weight: 700; }
.ie-row select, .ie-row input, .ie-row textarea { width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; box-sizing: border-box; }
.ie-row textarea { min-height: 120px; resize: vertical; }
.ie-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.ie-btn { border: none; border-radius: 8px; padding: 10px 12px; font-weight: 700; font-size: 12px; cursor: pointer; }
.ie-btn-primary { background: #0f172a; color: #fff; }
.ie-btn-secondary { background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; }
.ie-preview-wrap { padding: 14px 16px; }
#ie-preview { width: 100%; aspect-ratio: 1 / 1; border: 1px solid #cbd5e1; border-radius: 12px; background: #fff; }
.ie-saved-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
.ie-thumb { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; }
.ie-thumb img { width: 100%; height: 110px; object-fit: cover; display: block; }
.ie-thumb .meta { padding: 8px; font-size: 11px; color: #334155; }
.ie-badge { display: inline-flex; padding: 2px 8px; border-radius: 999px; font-size: 10px; background: #eef2ff; color: #3730a3; font-weight: 700; }
@media (max-width: 980px) { .ie-grid { grid-template-columns: 1fr; } }
</style>

<div class="image-editor-wrap">
    <div class="ie-grid">
        <div class="ie-card">
            <h3>Source & génération</h3>
            <div class="ie-card-body">
                <div class="ie-row">
                    <label>Plateforme cible</label>
                    <select id="ie-platform">
                        <option value="facebook">Facebook (1200x630)</option>
                        <option value="gmb">Google My Business (1200x900)</option>
                        <option value="instagram">Instagram (1080x1080)</option>
                        <option value="blog">Blog (1200x628)</option>
                    </select>
                </div>

                <div class="ie-row">
                    <label>Type de contenu</label>
                    <select id="ie-source-type">
                        <option value="article">Articles</option>
                        <option value="page">Pages</option>
                        <option value="secteur">Secteurs</option>
                    </select>
                </div>

                <div class="ie-row">
                    <label>Contenu lié</label>
                    <select id="ie-source-id"></select>
                </div>

                <div class="ie-row">
                    <label>Titre image</label>
                    <input id="ie-title" type="text" placeholder="Titre principal" />
                </div>

                <div class="ie-row">
                    <label>Texte / contenu</label>
                    <textarea id="ie-content" placeholder="Contenu repris automatiquement, modifiable..."></textarea>
                </div>

                <div class="ie-actions">
                    <button class="ie-btn ie-btn-primary" id="ie-generate">Générer le design HTML5</button>
                    <button class="ie-btn ie-btn-secondary" id="ie-save">Sauvegarder + thumbnail</button>
                </div>
                <div style="margin-top:8px;font-size:12px;color:#64748b" id="ie-status"></div>
            </div>
        </div>

        <div class="ie-card">
            <h3>Aperçu image générée</h3>
            <div class="ie-preview-wrap">
                <iframe id="ie-preview" title="Aperçu design"></iframe>
            </div>
        </div>
    </div>

    <div class="ie-card" style="margin-top:18px;">
        <h3>Bibliothèque d'images (sauvegardées et partageables)</h3>
        <div class="ie-card-body">
            <div class="ie-saved-list" id="ie-saved-list"></div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiUrl = '/admin/api/social/image-editor.php';
    const state = {
        sources: { article: [], page: [], secteur: [] },
        currentHtml: '',
        currentThumb: ''
    };

    const el = {
        platform: document.getElementById('ie-platform'),
        sourceType: document.getElementById('ie-source-type'),
        sourceId: document.getElementById('ie-source-id'),
        title: document.getElementById('ie-title'),
        content: document.getElementById('ie-content'),
        status: document.getElementById('ie-status'),
        preview: document.getElementById('ie-preview'),
        saved: document.getElementById('ie-saved-list')
    };

    const setStatus = (msg, bad = false) => {
        el.status.textContent = msg;
        el.status.style.color = bad ? '#b91c1c' : '#065f46';
    };

    async function callApi(action, payload = {}) {
        const res = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, ...payload })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Erreur API');
        return data;
    }

    function refillSourceOptions() {
        const type = el.sourceType.value;
        const list = state.sources[type] || [];
        el.sourceId.innerHTML = list.map(item =>
            `<option value="${item.id}">${(item.title || item.name || 'Sans titre').replace(/</g,'&lt;')}</option>`
        ).join('');
        onSourceChange();
    }

    function onSourceChange() {
        const type = el.sourceType.value;
        const id = Number(el.sourceId.value || 0);
        const found = (state.sources[type] || []).find(x => Number(x.id) === id);
        if (!found) return;
        el.title.value = found.title || found.name || '';
        el.content.value = found.content || found.description || '';
    }

    async function loadSources() {
        const data = await callApi('list_sources');
        state.sources.article = data.sources.articles || [];
        state.sources.page = data.sources.pages || [];
        state.sources.secteur = data.sources.secteurs || [];
        refillSourceOptions();
    }

    async function loadLibrary() {
        const data = await callApi('list_designs');
        const list = data.designs || [];
        if (!list.length) {
            el.saved.innerHTML = '<div style="font-size:12px;color:#64748b;">Aucune image sauvegardée.</div>';
            return;
        }
        el.saved.innerHTML = list.map(item => `
            <div class="ie-thumb">
                <img src="${item.thumbnail_data || ''}" alt="thumbnail" />
                <div class="meta">
                    <div style="font-weight:700; margin-bottom:4px;">${(item.title || 'Sans titre').replace(/</g,'&lt;')}</div>
                    <div><span class="ie-badge">${item.platform}</span></div>
                    <div style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="/admin/modules/social/image-editor/share.php?token=${item.share_token}" target="_blank">Partager</a>
                    </div>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('ie-generate').addEventListener('click', async () => {
        try {
            const data = await callApi('generate_template', {
                platform: el.platform.value,
                title: el.title.value,
                content: el.content.value
            });
            state.currentHtml = data.html;
            state.currentThumb = data.thumbnail_data;
            el.preview.srcdoc = data.html;
            setStatus('Design généré.');
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('ie-save').addEventListener('click', async () => {
        try {
            if (!state.currentHtml) throw new Error('Générez un design avant sauvegarde.');
            await callApi('save_design', {
                source_type: el.sourceType.value,
                source_id: Number(el.sourceId.value || 0),
                platform: el.platform.value,
                title: el.title.value,
                content: el.content.value,
                html: state.currentHtml,
                thumbnail_data: state.currentThumb
            });
            setStatus('Image sauvegardée avec miniature et lien de partage.');
            loadLibrary();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    el.sourceType.addEventListener('change', refillSourceOptions);
    el.sourceId.addEventListener('change', onSourceChange);

    loadSources().then(loadLibrary).catch(err => setStatus(err.message, true));
})();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../layout.php';
