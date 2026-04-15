<?php
/** @var TemplateRepository $tplRepo */
/** @var int $userId */

$templates  = $tplRepo->getAll($userId);
$categories = $tplRepo->categories();
$grouped    = [];
foreach ($templates as $t) {
    $grouped[$t['category']][] = $t;
}
?>
<style>
.tpl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.tpl-cats{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;}
.tpl-cat-btn{padding:5px 12px;border-radius:999px;font-size:.76rem;font-weight:600;cursor:pointer;border:1px solid #e2e8f0;background:#fff;color:#475569;transition:all .15s;}
.tpl-cat-btn.active,.tpl-cat-btn:hover{background:#2563eb;border-color:#2563eb;color:#fff;}
.tpl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;}
.tpl-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;transition:box-shadow .15s;}
.tpl-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.07);}
.tpl-card-cat{font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:6px;}
.tpl-card-name{font-size:.9rem;font-weight:700;color:#0f172a;margin-bottom:3px;}
.tpl-card-sub{font-size:.78rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:10px;}
.tpl-card-foot{display:flex;gap:6px;align-items:center;}
.tpl-usage{font-size:.68rem;color:#94a3b8;margin-left:auto;}
.btn-tpl-action{padding:4px 10px;border-radius:6px;font-size:.73rem;font-weight:600;cursor:pointer;border:0;}
.btn-edit{background:#f1f5f9;color:#475569;}
.btn-edit:hover{background:#e2e8f0;}
.btn-use-tpl{background:#eff6ff;color:#1d4ed8;}
.btn-use-tpl:hover{background:#dbeafe;}
.btn-del{background:#fee2e2;color:#991b1b;}
.btn-del:hover{background:#fecaca;}
.btn-new{background:#2563eb;color:#fff;border:0;padding:8px 16px;border-radius:9px;font-size:.83rem;font-weight:700;cursor:pointer;}
.tpl-modal .sf textarea{min-height:180px;}
.tpl-default-badge{font-size:.62rem;padding:1px 6px;border-radius:999px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;margin-left:4px;}
.tpl-ai-box{margin:8px 0 14px;padding:10px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;}
.tpl-ai-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:8px;}
.tpl-ai-btn{background:#0f172a;color:#fff;border:0;padding:8px 12px;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;}
.tpl-ai-btn:hover{background:#1e293b;}
.tpl-editor-wrap{border:1px solid #d1d5db;border-radius:10px;overflow:hidden;background:#fff;}
.tpl-editor-toolbar{display:flex;gap:4px;padding:8px;border-bottom:1px solid #e5e7eb;background:#f8fafc;}
.tpl-editor-toolbar button{border:1px solid #cbd5e1;background:#fff;padding:4px 8px;border-radius:6px;font-size:.75rem;cursor:pointer;color:#334155;}
.tpl-editor-toolbar button:hover{background:#eff6ff;border-color:#93c5fd;color:#1d4ed8;}
.tpl-editor{min-height:190px;padding:12px;font-size:.86rem;line-height:1.6;color:#334155;outline:none;white-space:pre-wrap;}
</style>

<div class="tpl-header">
    <div>
        <h1 style="font-size:1.1rem;font-weight:700;color:#0f172a;margin:0;">
            <i class="fas fa-file-lines" style="color:#2563eb;margin-right:6px;"></i>Banque de templates
        </h1>
        <p style="color:#64748b;font-size:.82rem;margin:4px 0 0;"><?= count($templates) ?> template<?= count($templates) > 1 ? 's' : '' ?> · Placeholders : <code>{{contact_prenom}}</code> <code>{{conseiller_nom}}</code> <code>{{bien_titre}}</code></p>
    </div>
    <button class="btn-new" onclick="openTplForm()"><i class="fas fa-plus"></i> Nouveau template</button>
</div>

<!-- Filtres -->
<div class="tpl-cats">
    <button class="tpl-cat-btn active" data-cat="all" onclick="filterCat('all',this)">Tous (<?= count($templates) ?>)</button>
    <?php foreach ($categories as $key => $label):
        $count = count($grouped[$key] ?? []);
        if ($count === 0) continue;
    ?>
    <button class="tpl-cat-btn" data-cat="<?= htmlspecialchars($key) ?>" onclick="filterCat('<?= htmlspecialchars($key) ?>',this)">
        <?= htmlspecialchars($label) ?> (<?= $count ?>)
    </button>
    <?php endforeach; ?>
</div>

<!-- Grille -->
<div class="tpl-grid" id="tplGrid">
    <?php foreach ($grouped as $cat => $tpls): ?>
        <?php foreach ($tpls as $tpl): ?>
        <div class="tpl-card" data-cat="<?= htmlspecialchars($tpl['category']) ?>">
            <div class="tpl-card-cat"><?= htmlspecialchars($categories[$tpl['category']] ?? $tpl['category']) ?></div>
            <div class="tpl-card-name">
                <?= htmlspecialchars($tpl['name']) ?>
                <?php if ($tpl['is_default']): ?><span class="tpl-default-badge">défaut</span><?php endif; ?>
            </div>
            <div class="tpl-card-sub"><?= htmlspecialchars($tpl['subject']) ?></div>
            <div class="tpl-card-foot">
                <button class="btn-tpl-action btn-use-tpl" onclick="previewTemplate(<?= (int)$tpl['id'] ?>)">
                    <i class="fas fa-eye"></i> Aperçu
                </button>
                <?php if (!$tpl['is_default']): ?>
                    <button class="btn-tpl-action btn-edit" onclick='openTplForm(<?= htmlspecialchars(json_encode($tpl), ENT_QUOTES) ?>)'>
                        <i class="fas fa-pen"></i> Modifier
                    </button>
                    <button class="btn-tpl-action btn-del" onclick="deleteTpl(<?= (int)$tpl['id'] ?>, this)">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php else: ?>
                    <button class="btn-tpl-action btn-edit" onclick='duplicateTpl(<?= htmlspecialchars(json_encode($tpl), ENT_QUOTES) ?>)'>
                        <i class="fas fa-copy"></i> Dupliquer
                    </button>
                <?php endif; ?>
                <span class="tpl-usage"><?= (int)$tpl['usage_count'] ?> utilisation<?= $tpl['usage_count'] > 1 ? 's' : '' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<!-- ══ MODAL FORM ══ -->
<div class="modal-overlay" id="tplFormModal">
    <div class="modal-box tpl-modal" style="width:560px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 id="tplFormTitle" style="margin:0;font-size:.95rem;font-weight:700;">Nouveau template</h3>
            <button onclick="closeModal('tplFormModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <input type="hidden" id="tplId" value="">
        <div class="sf">
            <div class="tpl-ai-box">
                <div style="font-size:.76rem;font-weight:700;color:#334155;margin-bottom:8px;"><i class="fas fa-wand-magic-sparkles" style="color:#2563eb;"></i> Générer avec IA</div>
                <div class="tpl-ai-grid">
                    <input type="text" id="tplAiGoal" placeholder="Objectif (ex: relance après visite)">
                    <select id="tplAiTone">
                        <option value="professionnel">Ton professionnel</option>
                        <option value="amical">Ton amical</option>
                        <option value="urgent">Ton urgent</option>
                        <option value="premium">Ton premium</option>
                    </select>
                    <button type="button" class="tpl-ai-btn" onclick="generateTplWithAI()"><i class="fas fa-sparkles"></i> Générer</button>
                </div>
                <input type="text" id="tplAiContext" placeholder="Contexte optionnel (type de bien, situation client...)" style="margin-top:8px;">
                <div id="tplAiFeedback" style="font-size:.72rem;color:#64748b;margin-top:6px;"></div>
            </div>
            <div>
                <label>Nom du template *</label>
                <input type="text" id="tplName" placeholder="Ex: Relance après visite">
            </div>
            <div>
                <label>Catégorie</label>
                <select id="tplCategory">
                    <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Objet de l'email</label>
                <input type="text" id="tplSubject" placeholder="Ex: Suite à votre visite — {{bien_titre}}">
            </div>
            <div>
                <label>Corps du message <small style="font-weight:400;color:#94a3b8;">Placeholders : {{contact_prenom}} {{conseiller_nom}} {{bien_titre}} etc.</small></label>
                <div class="tpl-editor-wrap">
                    <div class="tpl-editor-toolbar">
                        <button type="button" onclick="editorCmd('bold')"><b>B</b></button>
                        <button type="button" onclick="editorCmd('italic')"><i>I</i></button>
                        <button type="button" onclick="editorCmd('insertUnorderedList')">• Liste</button>
                        <button type="button" onclick="editorCmd('insertParagraph')">¶ Paragraphe</button>
                        <button type="button" onclick="insertPlaceholder('{{contact_prenom}}')">{{contact_prenom}}</button>
                        <button type="button" onclick="insertPlaceholder('{{conseiller_nom}}')">{{conseiller_nom}}</button>
                        <button type="button" onclick="insertPlaceholder('{{bien_titre}}')">{{bien_titre}}</button>
                    </div>
                    <div id="tplBodyEditor" class="tpl-editor" contenteditable="true"></div>
                </div>
                <textarea id="tplBody" style="display:none;" placeholder="Bonjour {{contact_prenom}},&#10;&#10;...&#10;&#10;Cordialement,&#10;{{conseiller_nom}}"></textarea>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;">
            <span id="tplFormFeedback" style="font-size:.76rem;color:#64748b;align-self:center;flex:1;"></span>
            <button onclick="closeModal('tplFormModal')" style="border:1px solid #d1d5db;background:#fff;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:.82rem;">Annuler</button>
            <button onclick="saveTpl()" style="background:#2563eb;color:#fff;border:0;padding:7px 16px;border-radius:8px;cursor:pointer;font-size:.82rem;font-weight:700;">Enregistrer</button>
        </div>
    </div>
</div>

<!-- ══ MODAL APERÇU ══ -->
<div class="modal-overlay" id="tplPreviewModal">
    <div class="modal-box" style="width:580px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 id="previewTitle" style="margin:0;font-size:.95rem;font-weight:700;"></h3>
            <button onclick="closeModal('tplPreviewModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <div style="background:#f8fafc;border-radius:9px;padding:14px;margin-bottom:10px;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:4px;">OBJET</div>
            <div id="previewSubject" style="font-size:.88rem;font-weight:600;color:#0f172a;"></div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:9px;padding:14px;min-height:120px;font-size:.85rem;line-height:1.65;color:#334155;" id="previewBody"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
            <button onclick="closeModal('tplPreviewModal')" style="border:1px solid #d1d5db;background:#fff;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:.82rem;">Fermer</button>
        </div>
    </div>
</div>

<script>
const allTpls = <?= json_encode($templates, JSON_UNESCAPED_UNICODE) ?>;

function filterCat(cat, btn) {
    document.querySelectorAll('.tpl-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tpl-card').forEach(c => {
        c.style.display = (cat === 'all' || c.dataset.cat === cat) ? '' : 'none';
    });
}

function openTplForm(tpl = null) {
    const htmlBody = tpl ? (tpl.body_html || '') : '';
    document.getElementById('tplId').value      = tpl ? tpl.id : '';
    document.getElementById('tplName').value    = tpl ? tpl.name : '';
    document.getElementById('tplSubject').value = tpl ? tpl.subject : '';
    document.getElementById('tplCategory').value = tpl ? tpl.category : 'general';
    document.getElementById('tplFormTitle').textContent = tpl ? 'Modifier le template' : 'Nouveau template';
    document.getElementById('tplFormFeedback').textContent = '';
    document.getElementById('tplAiFeedback').textContent = '';
    document.getElementById('tplAiGoal').value = '';
    document.getElementById('tplAiContext').value = '';
    setEditorHtml(htmlBody || '<p>Bonjour {{contact_prenom}},</p><p></p><p>Cordialement,<br>{{conseiller_nom}}</p>');
    openModal('tplFormModal');
    setTimeout(() => document.getElementById('tplName').focus(), 100);
}

function duplicateTpl(tpl) {
    openTplForm({...tpl, id: 0, name: 'Copie — ' + tpl.name, is_default: 0});
}

async function saveTpl() {
    const name = document.getElementById('tplName').value.trim();
    const fb   = document.getElementById('tplFormFeedback');
    if (!name) { fb.textContent = 'Nom obligatoire.'; return; }
    syncEditorToTextarea();
    const htmlBody = document.getElementById('tplBody').value.trim();
    const fd = new FormData();
    fd.append('id',       document.getElementById('tplId').value);
    fd.append('name',     name);
    fd.append('category', document.getElementById('tplCategory').value);
    fd.append('subject',  document.getElementById('tplSubject').value.trim());
    fd.append('body_html', htmlBody);
    fb.textContent = 'Enregistrement...';
    const d = await (await fetch('/admin?module=messagerie&action=template_save', {method:'POST',body:fd})).json();
    if (d.ok) { closeModal('tplFormModal'); location.reload(); }
    else { fb.textContent = d.error||'Erreur.'; }
}

async function deleteTpl(id, btn) {
    if (!confirm('Supprimer ce template ?')) return;
    const fd = new FormData(); fd.append('id', id);
    const d = await (await fetch('/admin?module=messagerie&action=template_delete', {method:'POST',body:fd})).json();
    if (d.ok) { btn.closest('.tpl-card').remove(); }
}

function previewTemplate(id) {
    const tpl = allTpls.find(t => t.id == id);
    if (!tpl) return;
    document.getElementById('previewTitle').textContent   = tpl.name;
    document.getElementById('previewSubject').textContent = tpl.subject;
    document.getElementById('previewBody').innerHTML      = tpl.body_html;
    openModal('tplPreviewModal');
}

function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function editorCmd(cmd) {
    document.execCommand(cmd, false, null);
    syncEditorToTextarea();
}

function insertPlaceholder(text) {
    document.execCommand('insertText', false, text);
    syncEditorToTextarea();
}

function normalizeEditorHtml(html) {
    const div = document.createElement('div');
    div.innerHTML = (html || '').trim();
    if (!div.innerHTML) return '';
    div.querySelectorAll('script,style').forEach(node => node.remove());
    return div.innerHTML;
}

function setEditorHtml(html) {
    const clean = normalizeEditorHtml(html);
    document.getElementById('tplBodyEditor').innerHTML = clean;
    syncEditorToTextarea();
}

function syncEditorToTextarea() {
    const html = normalizeEditorHtml(document.getElementById('tplBodyEditor').innerHTML);
    document.getElementById('tplBody').value = html;
}

document.getElementById('tplBodyEditor').addEventListener('input', syncEditorToTextarea);

async function generateTplWithAI() {
    const feedback = document.getElementById('tplAiFeedback');
    const goal = document.getElementById('tplAiGoal').value.trim();
    if (!goal) {
        feedback.textContent = "Ajoutez un objectif pour lancer la génération IA.";
        return;
    }

    feedback.textContent = 'Génération en cours...';
    const fd = new FormData();
    fd.append('goal', goal);
    fd.append('tone', document.getElementById('tplAiTone').value);
    fd.append('context', document.getElementById('tplAiContext').value.trim());
    fd.append('category', document.getElementById('tplCategory').value);

    try {
        const res = await fetch('/admin?module=messagerie&action=ai_template', { method:'POST', body:fd });
        const d = await res.json();
        if (!d.ok) {
            feedback.textContent = d.error || "Impossible de générer le template.";
            return;
        }
        if (!document.getElementById('tplName').value.trim()) {
            document.getElementById('tplName').value = d.name || '';
        }
        document.getElementById('tplSubject').value = d.subject || '';
        setEditorHtml(d.body_html || '');
        feedback.textContent = 'Template généré. Vous pouvez le modifier manuellement avant enregistrement.';
    } catch (e) {
        feedback.textContent = "Erreur réseau pendant la génération IA.";
    }
}
</script>
