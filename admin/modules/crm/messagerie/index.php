<?php
/**
 * Module Messagerie CRM
 * /admin/modules/crm/messagerie/index.php
 */

if (!isset($pdo) && !isset($db)) {
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (PDOException $e) {
        echo '<div style="padding:20px;color:#ef4444">Erreur DB: '.htmlspecialchars($e->getMessage()).'</div>';
        return;
    }
}
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db = $pdo;

require_once dirname(__DIR__, 4) . '/includes/classes/EmailService.php';
$emailService = new EmailService($pdo);
$config = $emailService->getConfig();
$isConfigured = !empty($config['smtp_host']) && !empty($config['smtp_user']);

$counts = ['inbox'=>0,'sent'=>0,'unread'=>0,'total'=>0,'starred'=>0];
try {
    $counts['inbox']   = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='inbound'")->fetchColumn();
    $counts['sent']    = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='outbound'")->fetchColumn();
    $counts['unread']  = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_read=0 AND direction='inbound'")->fetchColumn();
    $counts['starred'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_starred=1")->fetchColumn();
    $counts['total']   = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails")->fetchColumn();
} catch (Exception $e) {}

$csrf = $_SESSION['csrf_token'] ?? '';
?>

<style>
/* ── Layout messagerie ── */
.msg-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    height: calc(100vh - var(--header-h) - 40px);
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

/* ── Sidebar ── */
.msg-sidebar {
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border);
    background: var(--surface-2);
    overflow: hidden;
}
.msg-sb-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 14px 12px;
    border-bottom: 1px solid var(--border);
}
.msg-sb-hd h3 {
    font-size: .9rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

/* ── Tabs sidebar ── */
.msg-tabs {
    display: flex;
    flex-direction: column;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.msg-tab {
    padding: 8px 14px;
    font-size: .82rem;
    font-weight: 500;
    color: var(--text-2);
    cursor: pointer;
    border-radius: 6px;
    margin: 1px 8px;
    transition: background .15s, color .15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.msg-tab:hover  { background: var(--surface-3); color: var(--text); }
.msg-tab.active { background: var(--accent); color: #fff; }

/* ── Search ── */
.msg-search { padding: 8px 10px; border-bottom: 1px solid var(--border); }
.msg-search input {
    width: 100%;
    padding: 7px 10px 7px 28px;
    font-size: .8rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
    color: var(--text);
    box-sizing: border-box;
}
.msg-search input:focus { outline: none; border-color: var(--accent); }

/* ── Liste emails ── */
.msg-list {
    flex: 1;
    overflow-y: auto;
}
.msg-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background .15s;
}
.msg-item:hover  { background: var(--surface-3); }
.msg-item.active { background: rgba(var(--accent-rgb, 99,102,241),.08); }
.msg-item.unread .msg-sender { font-weight: 700; }
.msg-item.unread .msg-subj   { font-weight: 600; color: var(--text); }

.msg-av {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--accent);
    color: #fff;
    font-size: .72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.msg-preview { flex: 1; min-width: 0; }
.msg-sender {
    font-size: .8rem;
    color: var(--text-2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
}
.msg-time { font-size: .72rem; color: var(--text-3); flex-shrink: 0; }
.msg-subj {
    font-size: .78rem;
    color: var(--text-3);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

/* ── Contenu principal ── */
.msg-content {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--surface);
}
.msg-content-hd {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.msg-content-hd h2 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    line-height: 1.3;
}
.msg-content-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

/* ── Boutons messagerie ── */
.msg-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    font-size: .78rem;
    font-weight: 600;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface-2);
    color: var(--text-2);
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
}
.msg-btn:hover   { background: var(--surface-3); color: var(--text); }
.msg-btn-p       { background: var(--accent); color: #fff; border-color: var(--accent); }
.msg-btn-p:hover { opacity: .9; color: #fff; }

/* ── Zone rédaction ── */
.msg-compose {
    padding: 14px 20px;
    border-top: 1px solid var(--border);
    background: var(--surface-2);
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .msg-layout { grid-template-columns: 1fr; height: auto; }
    .msg-sidebar { max-height: 300px; }
}
</style>

<div class="msg-layout anim">

    <!-- SIDEBAR -->
    <div class="msg-sidebar">
        <div class="msg-sb-hd">
            <h3><i class="fas fa-inbox" style="color:var(--accent);margin-right:6px"></i> Messagerie</h3>
            <button class="msg-btn msg-btn-p" onclick="msgCompose()" style="font-size:10px;padding:5px 10px">
                <i class="fas fa-plus"></i> Nouveau
            </button>
        </div>

        <div style="padding:8px 14px;border-bottom:1px solid var(--border);display:flex;gap:6px">
            <button class="msg-btn msg-btn-p" onclick="msgSync()" id="msgSyncBtn" style="font-size:10px;flex:1">
                <i class="fas fa-sync-alt"></i> Synchroniser
            </button>
        </div>

        <div class="msg-tabs">
            <div class="msg-tab active" data-folder="inbox" onclick="msgLoadFolder('inbox',this)">
                <i class="fas fa-inbox"></i> Inbox
                <?php if ($counts['unread'] > 0): ?>
                <span style="background:var(--accent);color:#fff;padding:1px 6px;border-radius:8px;font-size:9px;margin-left:auto"><?= $counts['unread'] ?></span>
                <?php endif; ?>
            </div>
            <div class="msg-tab" data-folder="sent" onclick="msgLoadFolder('sent',this)">
                <i class="fas fa-paper-plane"></i> Envoyés
            </div>
            <div class="msg-tab" data-folder="starred" onclick="msgLoadFolder('starred',this)">
                <i class="fas fa-star"></i> Suivis
            </div>
            <div class="msg-tab" data-folder="all" onclick="msgLoadFolder('all',this)">
                <i class="fas fa-list"></i> Tout
            </div>
        </div>

        <div class="msg-search">
            <div style="position:relative">
                <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:10px"></i>
                <input type="text" placeholder="Rechercher..." id="msgSearchInput" onkeyup="msgSearchDebounce()">
            </div>
        </div>

        <div class="msg-list" id="msgList">
            <div style="padding:40px 20px;text-align:center;color:var(--text-3);font-size:12px" id="msgListEmpty">
                <?php if (!$isConfigured): ?>
                    <i class="fas fa-exclamation-triangle" style="font-size:24px;margin-bottom:8px;display:block;color:var(--amber)"></i>
                    Configuration email manquante.<br>
                    <a href="?page=settings-email" style="color:var(--accent)">Configurer SMTP</a>
                <?php elseif ($counts['total'] === 0): ?>
                    <i class="fas fa-sync-alt" style="font-size:24px;margin-bottom:8px;display:block"></i>
                    Cliquez sur "Synchroniser"
                <?php else: ?>
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block"></i>
                    Chargement...
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isConfigured): ?>
        <div style="padding:8px 14px;border-top:1px solid var(--border);font-size:10px;color:var(--text-3)">
            <i class="fas fa-plug" style="color:var(--green);margin-right:4px"></i>
            <?= htmlspecialchars($config['smtp_from'] ?? $config['smtp_user']) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- CONTENT -->
    <div class="msg-content">

        <!-- État initial -->
        <div id="msgEmpty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text-3)">
            <i class="fas fa-envelope-open" style="font-size:48px;margin-bottom:12px;opacity:.3"></i>
            <p style="font-size:13px">Sélectionnez un email pour le lire</p>
            <p style="font-size:11px;margin-top:4px"><?= $counts['unread'] ?> non lu(s) | <?= $counts['total'] ?> au total</p>
        </div>

        <!-- Lecture -->
        <div id="msgRead" style="display:none;flex-direction:column;height:100%">
            <div class="msg-content-hd">
                <div style="flex:1;min-width:0">
                    <h2 id="msgReadSubject"></h2>
                    <p style="font-size:11px;color:var(--text-3);margin-top:4px" id="msgReadMeta"></p>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    <button class="msg-btn" onclick="msgReply()"><i class="fas fa-reply"></i> Répondre</button>
                    <button class="msg-btn" onclick="msgToggleStar()" title="Suivre"><i class="fas fa-star" id="msgStarIcon"></i></button>
                    <button class="msg-btn" onclick="msgMarkUnread()" title="Marquer non lu"><i class="fas fa-envelope"></i></button>
                    <button class="msg-btn" onclick="msgDeleteCurrent()" title="Supprimer" style="color:var(--red);border-color:var(--red)"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="msg-content-body" id="msgReadBody"></div>
            <div class="msg-compose" id="msgReplyBox" style="display:none">
                <textarea id="msgReplyText" placeholder="Écrire votre réponse..."
                    style="width:100%;min-height:100px;padding:10px;border:1px solid var(--border);border-radius:var(--radius);font-size:12px;resize:vertical;background:var(--surface);color:var(--text);box-sizing:border-box"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
                    <button class="msg-btn" onclick="document.getElementById('msgReplyBox').style.display='none'">Annuler</button>
                    <button class="msg-btn msg-btn-p" onclick="msgSendReply()"><i class="fas fa-paper-plane"></i> Envoyer</button>
                </div>
            </div>
        </div>

        <!-- Composition -->
        <div id="msgComposeBox" style="display:none;flex-direction:column;height:100%">
            <div class="msg-content-hd">
                <h2>Nouveau message</h2>
                <button class="msg-btn" onclick="msgCloseCompose()"><i class="fas fa-times"></i> Fermer</button>
            </div>
            <div style="flex:1;padding:20px;overflow-y:auto">
                <?php if (!empty($config['email_accounts'])): ?>
                <div style="margin-bottom:12px">
                    <label style="font-size:11px;font-weight:600;color:var(--text-2);display:block;margin-bottom:4px">De :</label>
                    <select id="msgCompFrom" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);font-size:12px;background:var(--surface);color:var(--text)">
                        <?php foreach ($config['email_accounts'] as $acc): ?>
                        <option value="<?= htmlspecialchars($acc) ?>" <?= ($acc===($config['email_roles']['primary']??''))?'selected':'' ?>><?= htmlspecialchars($acc) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php foreach (['msgCompTo'=>['À :','email','destinataire@email.com'],'msgCompSubject'=>['Objet :','text','Objet du message']] as $id=>[$lbl,$type,$ph]): ?>
                <div style="margin-bottom:12px">
                    <label style="font-size:11px;font-weight:600;color:var(--text-2);display:block;margin-bottom:4px"><?= $lbl ?></label>
                    <input type="<?= $type ?>" id="<?= $id ?>" placeholder="<?= $ph ?>"
                        style="width:100%;padding:8px;border:1px solid var(--border);border-radius:var(--radius);font-size:12px;background:var(--surface);color:var(--text);box-sizing:border-box">
                </div>
                <?php endforeach; ?>
                <div style="margin-bottom:12px">
                    <label style="font-size:11px;font-weight:600;color:var(--text-2);display:block;margin-bottom:4px">Message :</label>
                    <textarea id="msgCompBody" placeholder="Écrire votre message..."
                        style="width:100%;min-height:250px;padding:10px;border:1px solid var(--border);border-radius:var(--radius);font-size:12px;resize:vertical;background:var(--surface);color:var(--text);box-sizing:border-box"></textarea>
                </div>
            </div>
            <div style="padding:12px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px">
                <button class="msg-btn" onclick="msgCloseCompose()">Annuler</button>
                <button class="msg-btn msg-btn-p" onclick="msgSendNew()" id="msgSendBtn">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const API = '/admin/api/marketing/messagerie.php';
    const CSRF = '<?= addslashes($csrf) ?>';
    let currentFolder = 'inbox';
    let currentEmail = null;
    let searchTimer = null;

    function msgApi(action, params = {}, method = 'GET') {
        const url = new URL(API, window.location.origin);
        if (method === 'GET') {
            url.searchParams.set('action', action);
            Object.keys(params).forEach(k => url.searchParams.set(k, params[k]));
            return fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
                .then(r => r.json()).catch(() => ({success:false,error:'Erreur réseau'}));
        }
        const fd = new FormData();
        fd.append('action', action);
        fd.append('csrf_token', CSRF);
        Object.keys(params).forEach(k => fd.append(k, params[k]));
        return fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
            .then(r => r.json()).catch(() => ({success:false,error:'Erreur réseau'}));
    }

    window.msgLoadFolder = function(folder, tabEl) {
        currentFolder = folder;
        document.querySelectorAll('.msg-tab').forEach(t => t.classList.remove('active'));
        if (tabEl) tabEl.classList.add('active');
        const search = document.getElementById('msgSearchInput')?.value || '';
        const list = document.getElementById('msgList');
        list.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text-3);font-size:11px"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';

        msgApi('list', {folder, search, limit:50}).then(data => {
            if (!data.success || !data.emails?.length) {
                list.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text-3);font-size:12px"><i class="fas fa-inbox" style="font-size:20px;display:block;margin-bottom:6px"></i>Aucun email</div>';
                return;
            }
            list.innerHTML = '';
            data.emails.forEach(email => {
                const isOut = email.direction === 'outbound';
                const isUnread = !email.is_read && !isOut;
                const sender = isOut ? (email.to_name||email.to_email||'?') : (email.from_name||email.from_email||'?');
                const initials = sender.split(/[\s@]/).filter(Boolean).slice(0,2).map(w=>w[0].toUpperCase()).join('');
                const date = email.sent_at ? formatDate(email.sent_at) : '';
                const div = document.createElement('div');
                div.className = 'msg-item' + (isUnread ? ' unread' : '');
                div.dataset.id = email.id;
                div.innerHTML = `
                    <div class="msg-av">${initials}</div>
                    <div class="msg-preview">
                        <div class="msg-sender">
                            ${isOut ? '<i class="fas fa-share" style="font-size:9px;color:var(--text-3)"></i>' : ''}
                            <span style="overflow:hidden;text-overflow:ellipsis">${escHtml(sender)}</span>
                            <span class="msg-time">${date}</span>
                            ${email.is_starred ? '<i class="fas fa-star" style="color:var(--amber);font-size:9px"></i>' : ''}
                        </div>
                        <div class="msg-subj">${escHtml(email.subject||'(sans objet)')}</div>
                    </div>`;
                div.onclick = () => msgLoadEmail(email.id, div);
                list.appendChild(div);
            });
        });
    };

    function msgLoadEmail(id, itemEl) {
        document.querySelectorAll('.msg-item').forEach(x => x.classList.remove('active'));
        if (itemEl) { itemEl.classList.add('active'); itemEl.classList.remove('unread'); }
        document.getElementById('msgEmpty').style.display = 'none';
        document.getElementById('msgComposeBox').style.display = 'none';
        document.getElementById('msgRead').style.display = 'flex';
        document.getElementById('msgReplyBox').style.display = 'none';
        msgApi('get', {id}).then(data => {
            if (!data.success) return;
            currentEmail = data.email;
            document.getElementById('msgReadSubject').textContent = data.email.subject || '(sans objet)';
            const isOut = data.email.direction === 'outbound';
            const contact = isOut ? `À: ${data.email.to_name||''} ${data.email.to_email}` : `De: ${data.email.from_name||''} ${data.email.from_email}`;
            const date = data.email.sent_at ? new Date(data.email.sent_at).toLocaleString('fr-FR') : '';
            let meta = `${escHtml(contact)} · ${date}`;
            if (data.email.contact_id) {
                meta += ` · <a href="?page=crm&action=view&id=${data.email.contact_id}" style="color:var(--accent);text-decoration:none;font-weight:600">Voir fiche CRM</a>`;
            }
            document.getElementById('msgReadMeta').innerHTML = meta;
            document.getElementById('msgReadBody').innerHTML = `<div style="font-size:13px;line-height:1.7;color:var(--text-2)">${data.email.body_html||data.email.body_text||'<em style="color:var(--text-3)">Aucun contenu</em>'}</div>`;
            document.getElementById('msgStarIcon').style.color = data.email.is_starred ? 'var(--amber)' : '';
            if (!data.email.is_read) msgApi('mark-read', {id}, 'POST');
        });
    }

    window.msgCompose = function() {
        document.getElementById('msgEmpty').style.display = 'none';
        document.getElementById('msgRead').style.display = 'none';
        document.getElementById('msgComposeBox').style.display = 'flex';
        ['msgCompTo','msgCompSubject','msgCompBody'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
        });
    };
    window.msgCloseCompose = function() {
        document.getElementById('msgComposeBox').style.display = 'none';
        document.getElementById('msgEmpty').style.display = 'flex';
    };
    window.msgSendNew = function() {
        const btn = document.getElementById('msgSendBtn');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        msgApi('send', {
            from_email: document.getElementById('msgCompFrom')?.value || '',
            to_email:   document.getElementById('msgCompTo').value,
            subject:    document.getElementById('msgCompSubject').value,
            body_html:  document.getElementById('msgCompBody').value.replace(/\n/g,'<br>')
        }, 'POST').then(data => {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer';
            if (data.success) { showNotif('Email envoyé','success'); msgCloseCompose(); msgLoadFolder(currentFolder); }
            else showNotif('Erreur: '+(data.error||'Échec'),'error');
        });
    };
    window.msgReply = function() {
        if (!currentEmail) return;
        document.getElementById('msgReplyBox').style.display = 'block';
        document.getElementById('msgReplyText').value = '';
        document.getElementById('msgReplyText').focus();
    };
    window.msgSendReply = function() {
        if (!currentEmail) return;
        const body = document.getElementById('msgReplyText').value.trim();
        if (!body) return;
        msgApi('reply', { original_id: currentEmail.id, body_html: body.replace(/\n/g,'<br>') }, 'POST').then(data => {
            if (data.success) { showNotif('Réponse envoyée','success'); document.getElementById('msgReplyBox').style.display='none'; msgLoadFolder(currentFolder); }
            else showNotif('Erreur: '+(data.error||'Échec'),'error');
        });
    };
    window.msgToggleStar = function() {
        if (!currentEmail) return;
        const newStar = currentEmail.is_starred ? 0 : 1;
        msgApi('star', {id: currentEmail.id, starred: newStar}, 'POST').then(data => {
            if (data.success) { currentEmail.is_starred = newStar; document.getElementById('msgStarIcon').style.color = newStar ? 'var(--amber)' : ''; }
        });
    };
    window.msgMarkUnread = function() {
        if (!currentEmail) return;
        msgApi('mark-unread', {id: currentEmail.id}, 'POST').then(() => { showNotif('Marqué non lu','success'); msgLoadFolder(currentFolder); });
    };
    window.msgDeleteCurrent = function() {
        if (!currentEmail || !confirm('Supprimer cet email ?')) return;
        msgApi('delete', {id: currentEmail.id}, 'POST').then(() => {
            currentEmail = null;
            document.getElementById('msgRead').style.display = 'none';
            document.getElementById('msgEmpty').style.display = 'flex';
            msgLoadFolder(currentFolder);
        });
    };
    window.msgSync = function() {
        const btn = document.getElementById('msgSyncBtn');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sync...';
        msgApi('sync', {limit:30}).then(data => {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync-alt"></i> Synchroniser';
            if (data.success) { showNotif(`Sync OK : ${data.synced} nouveau(x)`,'success'); msgLoadFolder(currentFolder); }
            else showNotif('Erreur sync: '+(data.error||'Échec'),'error');
        }).catch(() => {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync-alt"></i> Synchroniser';
            showNotif('Erreur réseau','error');
        });
    };
    window.msgSearchDebounce = function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => msgLoadFolder(currentFolder), 300);
    };

    function escHtml(s) { const d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
    function formatDate(dateStr) {
        const d = new Date(dateStr), now = new Date(), diff = now - d;
        if (diff < 86400000 && d.getDate()===now.getDate()) return d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
        if (diff < 604800000) return d.toLocaleDateString('fr-FR',{weekday:'short'});
        return d.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'});
    }
    function showNotif(msg, type) {
        const div = document.createElement('div');
        div.style.cssText = 'position:fixed;top:70px;right:20px;padding:12px 20px;border-radius:8px;font-size:12px;font-weight:600;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.15);transition:opacity .3s';
        div.style.background = type==='success' ? '#22c55e' : '#ef4444';
        div.style.color = '#fff';
        div.textContent = msg;
        document.body.appendChild(div);
        setTimeout(() => { div.style.opacity='0'; setTimeout(()=>div.remove(),300); }, 3000);
    }

    <?php if ($counts['total'] > 0): ?>
    msgLoadFolder('inbox', document.querySelector('.msg-tab[data-folder="inbox"]'));
    <?php endif; ?>
})();
</script>