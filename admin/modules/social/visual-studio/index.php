<?php
if (!defined('ADMIN_ROUTER')) {
    die('Accès direct interdit');
}

$page_title = 'Studio Visuel IA · Étape 2';
ob_start();
?>

<style>
.vs-wrap{max-width:1300px;margin:0 auto}
.vs-grid{display:grid;grid-template-columns:420px 1fr;gap:18px}
.vs-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 8px 24px rgba(15,23,42,.06)}
.vs-title{margin:0;padding:14px 16px;border-bottom:1px solid #e2e8f0;font-size:14px}
.vs-body{padding:14px 16px}
.vs-row{margin-bottom:12px}
.vs-row label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:6px}
.vs-row input,.vs-row select,.vs-row textarea{width:100%;box-sizing:border-box;padding:9px 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px}
.vs-row textarea{min-height:90px;resize:vertical}
.vs-actions{display:flex;flex-wrap:wrap;gap:8px}
.vs-btn{border:none;border-radius:8px;padding:9px 11px;font-size:12px;font-weight:700;cursor:pointer}
.vs-btn-primary{background:#0f172a;color:#fff}
.vs-btn-alt{background:#eef2ff;color:#312e81}
.vs-btn-warn{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}
#vs-preview{width:100%;min-height:430px;border:1px solid #cbd5e1;border-radius:12px}
.vs-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:10px}
.vs-item{border:1px solid #e2e8f0;border-radius:10px;padding:10px}
.vs-badge{display:inline-flex;padding:2px 7px;border-radius:999px;background:#ecfeff;color:#155e75;font-size:10px;font-weight:700}
.vs-inline{display:flex;gap:8px;align-items:center}
.vs-muted{font-size:11px;color:#64748b}
@media(max-width:1050px){.vs-grid{grid-template-columns:1fr}}
</style>

<div class="vs-wrap">
    <div class="vs-card" style="margin-bottom:16px;">
        <h3 class="vs-title">Prochaine étape — Mode contenu + templates + sorties</h3>
        <div class="vs-body" style="font-size:13px;color:#334155;line-height:1.45;">
            Cette étape ajoute la préconfiguration depuis contenus existants, l’utilisation des modèles système,
            et les actions de sortie MVP : sauvegarder, utiliser, dupliquer, archiver et télécharger.
        </div>
    </div>

    <div class="vs-grid">
        <div class="vs-card">
            <h3 class="vs-title">Studio Visuel IA (wizard)</h3>
            <div class="vs-body">
                <div class="vs-row">
                    <label>Tenant ID</label>
                    <input id="vs-tenant" type="number" min="1" value="1" />
                </div>

                <div class="vs-row">
                    <label>Mode de création *</label>
                    <select id="vs-mode">
                        <option value="free">Création libre</option>
                        <option value="from_content">Depuis contenu existant</option>
                        <option value="from_template">Depuis modèle système</option>
                    </select>
                </div>

                <div id="vs-content-box" style="display:none">
                    <div class="vs-row">
                        <label>Type source</label>
                        <select id="vs-source-type">
                            <option value="article">Article</option>
                            <option value="page">Page</option>
                            <option value="social_post">Post social</option>
                            <option value="gmb_post">Post GMB</option>
                        </select>
                    </div>
                    <div class="vs-row">
                        <label>Source</label>
                        <select id="vs-source-id"></select>
                    </div>
                    <div class="vs-muted">Le titre + texte seront préremplis automatiquement.</div>
                </div>

                <div id="vs-template-box" style="display:none">
                    <div class="vs-row">
                        <label>Modèle système</label>
                        <select id="vs-template-id"></select>
                    </div>
                </div>

                <div class="vs-row">
                    <label>Plateforme cible *</label>
                    <select id="vs-platform"></select>
                </div>

                <div class="vs-row">
                    <label>Format cible *</label>
                    <input id="vs-format" type="text" value="1200x630" />
                </div>

                <div class="vs-row">
                    <label>Objectif *</label>
                    <select id="vs-goal"></select>
                </div>

                <div class="vs-row">
                    <label>Style visuel *</label>
                    <input id="vs-style" type="text" value="clean_minimal" />
                </div>

                <div class="vs-row">
                    <label>Titre</label>
                    <input id="vs-title" type="text" placeholder="Titre du visuel" />
                </div>

                <div class="vs-row">
                    <label>Texte source</label>
                    <textarea id="vs-text" placeholder="Prompt ou extrait contenu"></textarea>
                </div>

                <div class="vs-row vs-inline">
                    <label style="margin:0;"><input id="vs-overlay" type="checkbox" checked /> Texte sur image</label>
                    <label style="margin:0;"><input id="vs-cta" type="checkbox" /> CTA</label>
                </div>

                <div class="vs-row">
                    <label>Moteur</label>
                    <select id="vs-engine">
                        <option value="template_html5">Template HTML5/Canvas</option>
                        <option value="ai_image">Image IA</option>
                        <option value="hybrid">Hybride</option>
                    </select>
                </div>

                <div class="vs-actions">
                    <button class="vs-btn vs-btn-primary" id="vs-create">Créer draft</button>
                    <button class="vs-btn vs-btn-alt" id="vs-generate">Générer</button>
                    <button class="vs-btn vs-btn-alt" id="vs-save">Valider/Sauvegarder</button>
                    <button class="vs-btn vs-btn-alt" id="vs-use">Utiliser</button>
                    <button class="vs-btn vs-btn-alt" id="vs-duplicate">Dupliquer</button>
                    <button class="vs-btn vs-btn-warn" id="vs-archive">Archiver</button>
                    <button class="vs-btn vs-btn-alt" id="vs-download">Télécharger HTML</button>
                </div>
                <div id="vs-status" style="margin-top:8px;font-size:12px;color:#065f46"></div>
            </div>
        </div>

        <div class="vs-card">
            <h3 class="vs-title">Preview + bibliothèque</h3>
            <div class="vs-body">
                <iframe id="vs-preview" title="Preview Studio Visuel"></iframe>
                <h4 style="margin:14px 0 8px;font-size:13px;">Visuels récents</h4>
                <div class="vs-list" id="vs-list"></div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiUrl = '/admin/api/social/visual-studio.php';
    let currentId = 0;
    let templates = [];
    let sourceCache = [];

    const el = {
        tenant: document.getElementById('vs-tenant'),
        mode: document.getElementById('vs-mode'),
        contentBox: document.getElementById('vs-content-box'),
        sourceType: document.getElementById('vs-source-type'),
        sourceId: document.getElementById('vs-source-id'),
        templateBox: document.getElementById('vs-template-box'),
        templateId: document.getElementById('vs-template-id'),
        platform: document.getElementById('vs-platform'),
        format: document.getElementById('vs-format'),
        goal: document.getElementById('vs-goal'),
        style: document.getElementById('vs-style'),
        title: document.getElementById('vs-title'),
        text: document.getElementById('vs-text'),
        overlay: document.getElementById('vs-overlay'),
        cta: document.getElementById('vs-cta'),
        engine: document.getElementById('vs-engine'),
        status: document.getElementById('vs-status'),
        preview: document.getElementById('vs-preview'),
        list: document.getElementById('vs-list')
    };

    const setStatus = (msg, bad = false) => {
        el.status.textContent = msg;
        el.status.style.color = bad ? '#b91c1c' : '#065f46';
    };

    const callApi = async (action, payload = {}) => {
        const res = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, tenant_id: Number(el.tenant.value || 1), ...payload })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Erreur API');
        return data;
    };

    const applyModeUI = () => {
        const mode = el.mode.value;
        el.contentBox.style.display = mode === 'from_content' ? '' : 'none';
        el.templateBox.style.display = mode === 'from_template' ? '' : 'none';
    };

    const applyTemplateDefaults = () => {
        const id = Number(el.templateId.value || 0);
        const tpl = templates.find(t => Number(t.id) === id);
        if (!tpl) return;
        el.platform.value = tpl.default_platform;
        el.format.value = tpl.default_format;
        el.goal.value = tpl.default_goal;
        el.style.value = tpl.default_style;
        if (!el.title.value) el.title.value = tpl.name;
    };

    const prefillSource = () => {
        const id = Number(el.sourceId.value || 0);
        const row = sourceCache.find(x => Number(x.id) === id);
        if (!row) return;
        el.title.value = row.title || '';
        el.text.value = row.body || '';
    };

    async function loadContentCandidates() {
        const type = el.sourceType.value;
        const data = await callApi('content_candidates', { type });
        sourceCache = data.items || [];
        el.sourceId.innerHTML = sourceCache.map(x => `<option value="${x.id}">${(x.title || 'Sans titre').replace(/</g,'&lt;')}</option>`).join('');
        prefillSource();
    }

    async function bootstrap() {
        const data = await callApi('bootstrap');
        templates = data.templates || [];
        el.platform.innerHTML = (data.enums.platforms || []).map(x => `<option value="${x}">${x}</option>`).join('');
        el.goal.innerHTML = (data.enums.goals || []).map(x => `<option value="${x}">${x}</option>`).join('');
        el.templateId.innerHTML = templates.map(x => `<option value="${x.id}">${x.name}</option>`).join('');
        applyTemplateDefaults();
    }

    async function refreshAssets() {
        const data = await callApi('list_assets');
        const rows = data.items || [];
        if (!rows.length) {
            el.list.innerHTML = '<div class="vs-muted">Aucun visuel pour ce tenant.</div>';
            return;
        }

        el.list.innerHTML = rows.map(item => `
            <div class="vs-item">
                <div style="font-weight:700;font-size:12px;">${(item.title || 'Sans titre').replace(/</g, '&lt;')}</div>
                <div style="margin-top:4px;"><span class="vs-badge">${item.status}</span></div>
                <div class="vs-muted" style="margin-top:6px;">${item.target_platform} · ${item.target_format}</div>
                <div class="vs-muted">${item.mode}${item.source_type ? ' · source: '+item.source_type+'#'+item.source_id : ''}</div>
                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="vs-btn vs-btn-alt" style="padding:6px 8px" onclick="window.vsLoad(${item.id})">Charger</button>
                    <button class="vs-btn vs-btn-alt" style="padding:6px 8px" onclick="window.vsDuplicate(${item.id})">Dupliquer</button>
                    <button class="vs-btn vs-btn-warn" style="padding:6px 8px" onclick="window.vsArchive(${item.id})">Archiver</button>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('vs-create').addEventListener('click', async () => {
        try {
            const mode = el.mode.value;
            const tpl = templates.find(t => Number(t.id) === Number(el.templateId.value || 0));

            const payload = {
                mode,
                target_platform: el.platform.value,
                target_format: el.format.value,
                goal: el.goal.value,
                style: el.style.value,
                has_text_overlay: el.overlay.checked,
                has_cta: el.cta.checked,
                title: el.title.value,
                source_text: el.text.value,
                engine: el.engine.value
            };

            if (mode === 'from_content') {
                payload.source_type = el.sourceType.value;
                payload.source_id = Number(el.sourceId.value || 0);
                payload.recommended_channel = el.platform.value;
            }

            if (mode === 'from_template' && tpl) {
                payload.template_slug = tpl.slug;
                payload.title = payload.title || tpl.name;
            }

            const data = await callApi('create_draft', payload);
            currentId = Number(data.id || 0);
            setStatus(`Draft créé (#${currentId}).`);
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('vs-generate').addEventListener('click', async () => {
        try {
            if (!currentId) throw new Error('Créez un draft avant la génération.');
            const data = await callApi('generate_template', { id: currentId });
            el.preview.srcdoc = data.html || '';
            setStatus(`Visuel #${currentId} généré.`);
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('vs-save').addEventListener('click', async () => {
        try {
            if (!currentId) throw new Error('Aucun visuel sélectionné.');
            await callApi('change_status', { id: currentId, status: 'saved' });
            setStatus(`Visuel #${currentId} sauvegardé.`);
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('vs-use').addEventListener('click', async () => {
        try {
            if (!currentId) throw new Error('Aucun visuel sélectionné.');
            await callApi('change_status', { id: currentId, status: 'used' });
            setStatus(`Visuel #${currentId} utilisé.`);
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('vs-duplicate').addEventListener('click', async () => {
        try {
            if (!currentId) throw new Error('Aucun visuel sélectionné.');
            const data = await callApi('duplicate_asset', { id: currentId });
            currentId = Number(data.id || 0);
            setStatus(`Copie créée (#${currentId}).`);
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('vs-archive').addEventListener('click', async () => {
        try {
            if (!currentId) throw new Error('Aucun visuel sélectionné.');
            await callApi('change_status', { id: currentId, status: 'archived' });
            setStatus(`Visuel #${currentId} archivé.`);
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    document.getElementById('vs-download').addEventListener('click', async () => {
        try {
            if (!currentId) throw new Error('Aucun visuel sélectionné.');
            const data = await callApi('download_html', { id: currentId });
            const a = document.createElement('a');
            a.href = 'data:text/html;base64,' + data.content_base64;
            a.download = data.filename || 'visual.html';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setStatus(`Visuel #${currentId} téléchargé.`);
        } catch (e) {
            setStatus(e.message, true);
        }
    });

    el.mode.addEventListener('change', () => {
        applyModeUI();
        if (el.mode.value === 'from_content') loadContentCandidates().catch(err => setStatus(err.message, true));
        if (el.mode.value === 'from_template') applyTemplateDefaults();
    });

    el.sourceType.addEventListener('change', () => loadContentCandidates().catch(err => setStatus(err.message, true)));
    el.sourceId.addEventListener('change', prefillSource);
    el.templateId.addEventListener('change', applyTemplateDefaults);

    window.vsLoad = (id) => {
        currentId = Number(id || 0);
        setStatus(`Visuel #${currentId} chargé.`);
    };

    window.vsDuplicate = async (id) => {
        currentId = Number(id || 0);
        document.getElementById('vs-duplicate').click();
    };

    window.vsArchive = async (id) => {
        currentId = Number(id || 0);
        document.getElementById('vs-archive').click();
    };

    (async () => {
        try {
            await bootstrap();
            applyModeUI();
            await loadContentCandidates();
            await refreshAssets();
        } catch (e) {
            setStatus(e.message, true);
        }
    })();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layout/base.php';
