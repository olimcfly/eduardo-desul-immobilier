<?php
/**
 * Module Messagerie CRM (Inbox v2)
 * /admin/modules/crm/messagerie/index.php
 */

if (!isset($pdo) && !isset($db)) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        echo '<div style="padding:20px;color:#ef4444">Erreur DB: ' . htmlspecialchars($e->getMessage()) . '</div>';
        return;
    }
}
if (isset($db) && !isset($pdo)) {
    $pdo = $db;
}
if (isset($pdo) && !isset($db)) {
    $db = $pdo;
}

require_once dirname(__DIR__, 4) . '/includes/classes/EmailService.php';
$emailService = new EmailService($pdo);
$config = $emailService->getConfig();
$isConfigured = !empty($config['smtp_host']) && !empty($config['smtp_user']);

$counts = ['inbox' => 0, 'sent' => 0, 'unread' => 0, 'total' => 0, 'starred' => 0];
try {
    $counts['inbox'] = (int) $pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='inbound' AND folder!='trash'")->fetchColumn();
    $counts['sent'] = (int) $pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='outbound' AND folder!='trash'")->fetchColumn();
    $counts['unread'] = (int) $pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_read=0 AND direction='inbound' AND folder!='trash'")->fetchColumn();
    $counts['starred'] = (int) $pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_starred=1 AND folder!='trash'")->fetchColumn();
    $counts['total'] = (int) $pdo->query("SELECT COUNT(*) FROM crm_emails WHERE folder!='trash'")->fetchColumn();
} catch (Exception $e) {
}

$csrf = $_SESSION['csrf_token'] ?? '';
?>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
.crm-msgv2 {
    --msg-bd: var(--border, #e5e7eb);
    --msg-bg: var(--surface, #fff);
    --msg-bg-soft: var(--surface-2, #f8fafc);
    --msg-bg-hover: var(--surface-3, #f1f5f9);
    --msg-tx: var(--text, #0f172a);
    --msg-tx-soft: var(--text-2, #475569);
    --msg-tx-muted: var(--text-3, #94a3b8);
    --msg-brand: var(--accent, #6366f1);
    --msg-brand-rgb: var(--accent-rgb, 99, 102, 241);

    display: grid;
    grid-template-columns: 250px 360px minmax(520px, 1fr);
    height: calc(100vh - var(--header-h) - 40px);
    background: var(--msg-bg);
    border: 1px solid var(--msg-bd);
    border-radius: var(--radius-lg, 14px);
    overflow: hidden;
    box-shadow: var(--shadow-sm, 0 2px 8px rgba(15, 23, 42, .06));
}

.msgv2-col {
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: var(--msg-bg);
}

.msgv2-col + .msgv2-col {
    border-left: 1px solid var(--msg-bd);
}

.msgv2-col--nav {
    background: var(--msg-bg-soft);
}

.msgv2-section-hd {
    padding: 14px 14px 10px;
    border-bottom: 1px solid var(--msg-bd);
}

.msgv2-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.msgv2-title {
    margin: 0;
    font-size: .88rem;
    font-weight: 700;
    color: var(--msg-tx);
}

.msgv2-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 11px;
    border: 1px solid var(--msg-bd);
    border-radius: 10px;
    background: var(--msg-bg);
    color: var(--msg-tx-soft);
    cursor: pointer;
    font-size: .77rem;
    font-weight: 600;
    transition: all .15s;
}
.msgv2-btn:hover { background: var(--msg-bg-hover); color: var(--msg-tx); }
.msgv2-btn:disabled { opacity: .6; cursor: not-allowed; }
.msgv2-btn--primary { background: var(--msg-brand); color: #fff; border-color: var(--msg-brand); }
.msgv2-btn--primary:hover { color: #fff; opacity: .92; }

.msgv2-folders {
    padding: 10px;
    overflow-y: auto;
}

.msgv2-folder {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    border: 0;
    background: transparent;
    border-radius: 10px;
    padding: 9px 10px;
    color: var(--msg-tx-soft);
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    text-align: left;
}
.msgv2-folder:hover { background: var(--msg-bg-hover); color: var(--msg-tx); }
.msgv2-folder.active {
    background: rgba(var(--msg-brand-rgb), .11);
    color: var(--msg-brand);
}
.msgv2-folder .badge {
    margin-left: auto;
    padding: 1px 8px;
    border-radius: 999px;
    font-size: .67rem;
    font-weight: 700;
    background: rgba(var(--msg-brand-rgb), .14);
    color: var(--msg-brand);
}

.msgv2-meta {
    margin-top: auto;
    padding: 10px 14px;
    border-top: 1px solid var(--msg-bd);
    color: var(--msg-tx-muted);
    font-size: .7rem;
}

.msgv2-search-wrap {
    position: relative;
}
.msgv2-search-wrap i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--msg-tx-muted);
    font-size: .72rem;
}
.msgv2-search {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--msg-bd);
    border-radius: 10px;
    padding: 8px 10px 8px 28px;
    font-size: .78rem;
    color: var(--msg-tx);
    background: var(--msg-bg);
}

.msgv2-list {
    overflow-y: auto;
    flex: 1;
}

.msgv2-item {
    border-bottom: 1px solid var(--msg-bd);
    padding: 10px 12px;
    cursor: pointer;
    transition: background .14s;
}
.msgv2-item:hover { background: var(--msg-bg-hover); }
.msgv2-item.active { background: rgba(var(--msg-brand-rgb), .08); }
.msgv2-item.unread .msgv2-item-name,
.msgv2-item.unread .msgv2-item-subject { font-weight: 700; color: var(--msg-tx); }

.msgv2-item-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin-bottom: 4px;
}
.msgv2-item-name {
    font-size: .8rem;
    color: var(--msg-tx-soft);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.msgv2-item-date {
    font-size: .7rem;
    color: var(--msg-tx-muted);
    white-space: nowrap;
}
.msgv2-item-subject {
    font-size: .78rem;
    color: var(--msg-tx-soft);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.msgv2-item-snippet {
    font-size: .74rem;
    color: var(--msg-tx-muted);
    margin-top: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.msgv2-main-toolbar {
    padding: 12px 16px;
    border-bottom: 1px solid var(--msg-bd);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.msgv2-tools {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.msgv2-thread {
    flex: 1;
    overflow-y: auto;
    padding: 16px 18px;
    background: var(--msg-bg);
}

.msgv2-card {
    border: 1px solid var(--msg-bd);
    border-radius: 12px;
    background: var(--msg-bg);
    box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
}

.msgv2-card-hd {
    padding: 14px 14px 10px;
    border-bottom: 1px solid var(--msg-bd);
}
.msgv2-card-hd h3 {
    margin: 0;
    font-size: .95rem;
    color: var(--msg-tx);
}
.msgv2-card-hd p {
    margin: 4px 0 0;
    font-size: .75rem;
    color: var(--msg-tx-muted);
}

.msgv2-card-body {
    padding: 14px;
    font-size: .84rem;
    line-height: 1.62;
    color: var(--msg-tx-soft);
}

.msgv2-compose {
    border-top: 1px solid var(--msg-bd);
    padding: 12px 14px;
    background: var(--msg-bg-soft);
}

.msgv2-compose-fields {
    display: grid;
    gap: 8px;
    margin-bottom: 10px;
}
.msgv2-field {
    display: grid;
    grid-template-columns: 62px 1fr;
    align-items: center;
    gap: 8px;
}
.msgv2-field label {
    font-size: .72rem;
    font-weight: 700;
    color: var(--msg-tx-muted);
}
.msgv2-field input,
.msgv2-field select,
.msgv2-source {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--msg-bd);
    border-radius: 8px;
    font-size: .78rem;
    padding: 8px 10px;
    color: var(--msg-tx);
    background: var(--msg-bg);
}

.msgv2-editor-wrap {
    border: 1px solid var(--msg-bd);
    border-radius: 10px;
    overflow: hidden;
    background: var(--msg-bg);
}
.msgv2-editor-wrap .ql-toolbar {
    border: 0 !important;
    border-bottom: 1px solid var(--msg-bd) !important;
    background: #f8fafc;
}
.msgv2-editor-wrap .ql-container {
    border: 0 !important;
    min-height: 170px;
    font-size: .85rem;
}
.msgv2-source {
    min-height: 170px;
    resize: vertical;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.msgv2-empty {
    color: var(--msg-tx-muted);
    text-align: center;
    font-size: .82rem;
    padding: 34px 10px;
}

@media (max-width: 1200px) {
    .crm-msgv2 { grid-template-columns: 220px 320px minmax(460px, 1fr); }
}
@media (max-width: 980px) {
    .crm-msgv2 {
        grid-template-columns: 1fr;
        height: auto;
    }
    .msgv2-col + .msgv2-col { border-left: 0; border-top: 1px solid var(--msg-bd); }
    .msgv2-col--nav { max-height: 260px; }
    .msgv2-list { max-height: 320px; }
}
</style>

<div class="crm-msgv2 anim">
    <aside class="msgv2-col msgv2-col--nav">
        <div class="msgv2-section-hd">
            <div class="msgv2-brand">
                <h3 class="msgv2-title"><i class="fas fa-comments" style="color:var(--msg-brand);margin-right:6px"></i> Inbox CRM</h3>
                <button class="msgv2-btn msgv2-btn--primary" onclick="msgCompose()"><i class="fas fa-pen"></i> Nouveau</button>
            </div>
        </div>

        <div class="msgv2-folders">
            <button class="msgv2-folder active" data-folder="inbox" onclick="msgLoadFolder('inbox', this)">
                <i class="fas fa-inbox"></i> Réception
                <span class="badge"><?= (int) $counts['unread'] ?></span>
            </button>
            <button class="msgv2-folder" data-folder="sent" onclick="msgLoadFolder('sent', this)">
                <i class="fas fa-paper-plane"></i> Envoyés
                <span class="badge"><?= (int) $counts['sent'] ?></span>
            </button>
            <button class="msgv2-folder" data-folder="starred" onclick="msgLoadFolder('starred', this)">
                <i class="fas fa-star"></i> Suivis
                <span class="badge"><?= (int) $counts['starred'] ?></span>
            </button>
            <button class="msgv2-folder" data-folder="all" onclick="msgLoadFolder('all', this)">
                <i class="fas fa-layer-group"></i> Tous les emails
                <span class="badge"><?= (int) $counts['total'] ?></span>
            </button>
            <button class="msgv2-folder" data-folder="trash" onclick="msgLoadFolder('trash', this)">
                <i class="fas fa-trash"></i> Corbeille
            </button>
        </div>

        <?php if ($isConfigured): ?>
            <div class="msgv2-meta">
                <i class="fas fa-plug" style="color:#22c55e;margin-right:4px"></i>
                Connecté SMTP : <?= htmlspecialchars($config['smtp_from'] ?? $config['smtp_user']) ?>
            </div>
        <?php else: ?>
            <div class="msgv2-meta">
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-right:4px"></i>
                SMTP non configuré — <a href="?page=settings-email" style="color:var(--msg-brand)">Configurer</a>
            </div>
        <?php endif; ?>
    </aside>

    <section class="msgv2-col">
        <div class="msgv2-section-hd">
            <div class="msgv2-search-wrap">
                <i class="fas fa-search"></i>
                <input id="msgSearchInput" class="msgv2-search" type="text" placeholder="Rechercher contact, sujet, email…" onkeyup="msgSearchDebounce()">
            </div>
        </div>

        <div class="msgv2-list" id="msgList">
            <div class="msgv2-empty" id="msgListEmpty">
                <?php if (!$isConfigured): ?>
                    Configuration SMTP manquante.
                <?php elseif ($counts['total'] === 0): ?>
                    Aucune conversation. Lancez une synchronisation.
                <?php else: ?>
                    Chargement des conversations...
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="msgv2-col">
        <div class="msgv2-main-toolbar">
            <div class="msgv2-tools">
                <button class="msgv2-btn" id="msgSyncBtn" onclick="msgSync()"><i class="fas fa-sync-alt"></i> Synchroniser</button>
                <button class="msgv2-btn" onclick="msgReply()"><i class="fas fa-reply"></i> Répondre</button>
                <button class="msgv2-btn" onclick="msgToggleStar()"><i class="fas fa-star" id="msgStarIcon"></i> Suivre</button>
                <button class="msgv2-btn" onclick="msgMarkUnread()"><i class="fas fa-envelope"></i> Non lu</button>
                <button class="msgv2-btn" onclick="msgDeleteCurrent()"><i class="fas fa-trash"></i> Supprimer</button>
            </div>
            <div style="font-size:.72rem;color:var(--msg-tx-muted)">Phase 1 Inbox CRM</div>
        </div>

        <div class="msgv2-thread" id="msgReadBody">
            <div class="msgv2-empty" id="msgEmpty">Sélectionnez une conversation pour afficher le thread.</div>
            <article class="msgv2-card" id="msgReadCard" style="display:none">
                <header class="msgv2-card-hd">
                    <h3 id="msgReadSubject">(sans objet)</h3>
                    <p id="msgReadMeta"></p>
                </header>
                <div class="msgv2-card-body" id="msgReadContent"></div>
            </article>
        </div>

        <div class="msgv2-compose" id="msgComposeBox">
            <div class="msgv2-compose-fields">
                <?php if (!empty($config['email_accounts'])): ?>
                    <div class="msgv2-field">
                        <label for="msgCompFrom">De</label>
                        <select id="msgCompFrom">
                            <?php foreach ($config['email_accounts'] as $acc): ?>
                                <option value="<?= htmlspecialchars($acc) ?>" <?= ($acc === ($config['email_roles']['primary'] ?? '')) ? 'selected' : '' ?>><?= htmlspecialchars($acc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="msgv2-field">
                    <label for="msgCompTo">À</label>
                    <input type="email" id="msgCompTo" placeholder="destinataire@email.com">
                </div>

                <div class="msgv2-field">
                    <label for="msgCompSubject">Objet</label>
                    <input type="text" id="msgCompSubject" placeholder="Objet de votre message">
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="font-size:.7rem;color:var(--msg-tx-muted)">Éditeur HTML prêt pour templates / IA (phase suivante)</div>
                <button class="msgv2-btn" style="padding:6px 10px" id="msgToggleSourceBtn" onclick="msgToggleSourceMode()"><i class="fas fa-code"></i> HTML</button>
            </div>

            <div class="msgv2-editor-wrap" id="msgEditorWrap">
                <div id="msgQuillEditor"></div>
            </div>
            <textarea id="msgCompBodySource" class="msgv2-source" style="display:none"></textarea>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px">
                <button class="msgv2-btn" onclick="msgClearCompose()">Vider</button>
                <button class="msgv2-btn msgv2-btn--primary" onclick="msgSendNew()" id="msgSendBtn"><i class="fas fa-paper-plane"></i> Envoyer</button>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    const API = '/admin/api/marketing/messagerie.php';
    const CSRF = '<?= addslashes($csrf) ?>';
    let currentFolder = 'inbox';
    let currentEmail = null;
    let searchTimer = null;
    let sourceMode = false;
    let quill = null;

    function initEditor() {
        quill = new Quill('#msgQuillEditor', {
            theme: 'snow',
            placeholder: 'Rédigez votre email…',
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'blockquote'],
                    ['clean']
                ]
            }
        });
    }

    function msgApi(action, params = {}, method = 'GET') {
        const url = new URL(API, window.location.origin);

        if (method === 'GET') {
            url.searchParams.set('action', action);
            Object.keys(params).forEach(k => url.searchParams.set(k, params[k]));
            return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .catch(() => ({ success: false, error: 'Erreur réseau' }));
        }

        const fd = new FormData();
        fd.append('action', action);
        fd.append('csrf_token', CSRF);
        Object.keys(params).forEach(k => fd.append(k, params[k]));

        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
            .then(r => r.json())
            .catch(() => ({ success: false, error: 'Erreur réseau' }));
    }

    window.msgLoadFolder = function (folder, tabEl) {
        currentFolder = folder;
        document.querySelectorAll('.msgv2-folder').forEach(t => t.classList.remove('active'));
        if (tabEl) tabEl.classList.add('active');

        const search = document.getElementById('msgSearchInput')?.value || '';
        const list = document.getElementById('msgList');
        list.innerHTML = '<div class="msgv2-empty"><i class="fas fa-spinner fa-spin"></i> Chargement…</div>';

        msgApi('list', { folder, search, limit: 80 }).then(data => {
            if (!data.success || !data.emails?.length) {
                list.innerHTML = '<div class="msgv2-empty">Aucune conversation.</div>';
                return;
            }

            list.innerHTML = '';
            data.emails.forEach(email => {
                const isOut = email.direction === 'outbound';
                const sender = isOut ? (email.to_name || email.to_email || '?') : (email.from_name || email.from_email || '?');
                const snippetRaw = stripHtml(email.body_text || email.body_html || '');
                const snippet = snippetRaw.slice(0, 88);
                const date = email.sent_at ? formatDate(email.sent_at) : '';
                const isUnread = !email.is_read && !isOut;

                const row = document.createElement('div');
                row.className = 'msgv2-item' + (isUnread ? ' unread' : '');
                row.dataset.id = email.id;
                row.innerHTML = `
                    <div class="msgv2-item-top">
                        <div class="msgv2-item-name">${escHtml(sender)}</div>
                        <div class="msgv2-item-date">${date}</div>
                    </div>
                    <div class="msgv2-item-subject">${escHtml(email.subject || '(sans objet)')}</div>
                    <div class="msgv2-item-snippet">${escHtml(snippet)}</div>
                `;

                row.addEventListener('click', () => msgLoadEmail(email.id, row));
                list.appendChild(row);
            });
        });
    };

    function msgLoadEmail(id, rowEl) {
        document.querySelectorAll('.msgv2-item').forEach(x => x.classList.remove('active'));
        if (rowEl) {
            rowEl.classList.add('active');
            rowEl.classList.remove('unread');
        }

        msgApi('get', { id }).then(data => {
            if (!data.success || !data.email) {
                showNotif('Email introuvable', 'error');
                return;
            }

            currentEmail = data.email;
            const isOut = data.email.direction === 'outbound';
            const contact = isOut
                ? `À: ${data.email.to_name || ''} ${data.email.to_email || ''}`
                : `De: ${data.email.from_name || ''} ${data.email.from_email || ''}`;
            const date = data.email.sent_at ? new Date(data.email.sent_at).toLocaleString('fr-FR') : '';

            document.getElementById('msgEmpty').style.display = 'none';
            document.getElementById('msgReadCard').style.display = 'block';
            document.getElementById('msgReadSubject').textContent = data.email.subject || '(sans objet)';
            document.getElementById('msgReadMeta').innerHTML = escHtml(contact) + ' · ' + escHtml(date);
            document.getElementById('msgReadContent').innerHTML = data.email.body_html || (`<pre style="white-space:pre-wrap">${escHtml(data.email.body_text || '')}</pre>`);
            document.getElementById('msgStarIcon').style.color = data.email.is_starred ? '#f59e0b' : '';

            if (!data.email.is_read) msgApi('mark-read', { id }, 'POST');

            if (!document.getElementById('msgCompTo').value) {
                document.getElementById('msgCompTo').value = isOut ? (data.email.to_email || '') : (data.email.from_email || '');
            }
            if (!document.getElementById('msgCompSubject').value) {
                document.getElementById('msgCompSubject').value = normalizeReplySubject(data.email.subject || '');
            }
        });
    }

    window.msgCompose = function () {
        document.getElementById('msgCompTo').focus();
    };

    window.msgClearCompose = function () {
        document.getElementById('msgCompTo').value = '';
        document.getElementById('msgCompSubject').value = '';
        document.getElementById('msgCompBodySource').value = '';
        if (sourceMode) {
            document.getElementById('msgCompBodySource').value = '';
        } else {
            quill.root.innerHTML = '';
        }
    };

    window.msgReply = function () {
        if (!currentEmail) {
            showNotif('Sélectionnez une conversation avant de répondre', 'error');
            return;
        }

        const isOut = currentEmail.direction === 'outbound';
        document.getElementById('msgCompTo').value = isOut ? (currentEmail.to_email || '') : (currentEmail.from_email || '');
        document.getElementById('msgCompSubject').value = normalizeReplySubject(currentEmail.subject || '');
        quill.root.innerHTML = '<p><br></p><p style="color:#94a3b8">— Réponse rapide —</p>';
        quill.focus();
    };

    window.msgSendNew = function () {
        const to = document.getElementById('msgCompTo').value.trim();
        const subject = document.getElementById('msgCompSubject').value.trim();

        if (!to || !subject) {
            showNotif('Destinataire et objet requis', 'error');
            return;
        }

        const bodyHtml = sourceMode
            ? document.getElementById('msgCompBodySource').value
            : quill.root.innerHTML;

        const btn = document.getElementById('msgSendBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi…';

        msgApi('send', {
            from_email: document.getElementById('msgCompFrom')?.value || '',
            to_email: to,
            subject,
            body_html: bodyHtml
        }, 'POST').then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';

            if (data.success) {
                showNotif('Email envoyé', 'success');
                msgClearCompose();
                msgLoadFolder(currentFolder, document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));
                return;
            }

            showNotif('Erreur: ' + (data.error || 'Échec'), 'error');
        });
    };

    window.msgToggleSourceMode = function () {
        const source = document.getElementById('msgCompBodySource');
        const editorWrap = document.getElementById('msgEditorWrap');
        const btn = document.getElementById('msgToggleSourceBtn');

        sourceMode = !sourceMode;

        if (sourceMode) {
            source.value = quill.root.innerHTML;
            source.style.display = 'block';
            editorWrap.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-pen"></i> Visuel';
            return;
        }

        quill.root.innerHTML = source.value;
        source.style.display = 'none';
        editorWrap.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-code"></i> HTML';
    };

    window.msgToggleStar = function () {
        if (!currentEmail) return;
        const newStar = currentEmail.is_starred ? 0 : 1;
        msgApi('star', { id: currentEmail.id, starred: newStar }, 'POST').then(data => {
            if (!data.success) return;
            currentEmail.is_starred = newStar;
            document.getElementById('msgStarIcon').style.color = newStar ? '#f59e0b' : '';
            msgLoadFolder(currentFolder, document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));
        });
    };

    window.msgMarkUnread = function () {
        if (!currentEmail) return;
        msgApi('mark-unread', { id: currentEmail.id }, 'POST').then(() => {
            showNotif('Marqué non lu', 'success');
            msgLoadFolder(currentFolder, document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));
        });
    };

    window.msgDeleteCurrent = function () {
        if (!currentEmail || !confirm('Supprimer cet email ?')) return;
        msgApi('delete', { id: currentEmail.id }, 'POST').then(() => {
            currentEmail = null;
            document.getElementById('msgReadCard').style.display = 'none';
            document.getElementById('msgEmpty').style.display = 'block';
            showNotif('Email déplacé en corbeille', 'success');
            msgLoadFolder(currentFolder, document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));
        });
    };

    window.msgSync = function () {
        const btn = document.getElementById('msgSyncBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sync…';

        msgApi('sync', { limit: 30 }).then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt"></i> Synchroniser';

            if (data.success) {
                showNotif(`Sync OK : ${data.synced || 0} nouveau(x)`, 'success');
                msgLoadFolder(currentFolder, document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));
                return;
            }

            showNotif('Erreur sync: ' + (data.error || 'Échec'), 'error');
        }).catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync-alt"></i> Synchroniser';
            showNotif('Erreur réseau', 'error');
        });
    };

    window.msgSearchDebounce = function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            msgLoadFolder(currentFolder, document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));
        }, 280);
    };

    function normalizeReplySubject(subject) {
        if (!subject) return 'Re: '; 
        return /^Re:/i.test(subject) ? subject : ('Re: ' + subject);
    }

    function escHtml(s) {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    function stripHtml(s) {
        const d = document.createElement('div');
        d.innerHTML = s || '';
        return (d.textContent || d.innerText || '').trim();
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        const now = new Date();
        const diff = now - d;

        if (diff < 86400000 && d.getDate() === now.getDate()) {
            return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
        if (diff < 604800000) {
            return d.toLocaleDateString('fr-FR', { weekday: 'short' });
        }
        return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
    }

    function showNotif(msg, type) {
        const div = document.createElement('div');
        div.style.cssText = 'position:fixed;top:70px;right:20px;padding:12px 18px;border-radius:8px;font-size:12px;font-weight:700;z-index:9999;box-shadow:0 10px 20px rgba(0,0,0,.13);transition:opacity .3s';
        div.style.background = type === 'success' ? '#16a34a' : '#ef4444';
        div.style.color = '#fff';
        div.textContent = msg;
        document.body.appendChild(div);
        setTimeout(() => {
            div.style.opacity = '0';
            setTimeout(() => div.remove(), 300);
        }, 2500);
    }

    initEditor();
    msgLoadFolder('inbox', document.querySelector('.msgv2-folder[data-folder="inbox"]'));
})();
</script>
