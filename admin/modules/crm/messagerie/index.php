<?php
/**
 * Module Messagerie CRM (Inbox v2 - Phase 2)
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
    $counts['inbox'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='inbound' AND folder!='trash'")->fetchColumn();
    $counts['sent'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE direction='outbound' AND folder!='trash'")->fetchColumn();
    $counts['unread'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_read=0 AND direction='inbound' AND folder!='trash'")->fetchColumn();
    $counts['starred'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE is_starred=1 AND folder!='trash'")->fetchColumn();
    $counts['total'] = (int)$pdo->query("SELECT COUNT(*) FROM crm_emails WHERE folder!='trash'")->fetchColumn();
} catch (Exception $e) {
}

$csrf = $_SESSION['auth_csrf_token'] ?? '';
?>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* style volontairement conservé proche phase 1 */
.crm-msgv2 { display:grid; grid-template-columns:250px 360px minmax(520px,1fr); height:calc(100vh - var(--header-h) - 40px); background:var(--surface,#fff); border:1px solid var(--border,#e5e7eb); border-radius:var(--radius-lg,14px); overflow:hidden; }
.msgv2-col{min-width:0;display:flex;flex-direction:column}.msgv2-col+.msgv2-col{border-left:1px solid var(--border,#e5e7eb)}.msgv2-col--nav{background:var(--surface-2,#f8fafc)}
.msgv2-section-hd{padding:14px 14px 10px;border-bottom:1px solid var(--border,#e5e7eb)}.msgv2-brand{display:flex;align-items:center;justify-content:space-between;gap:8px}.msgv2-title{margin:0;font-size:.88rem}
.msgv2-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 11px;border:1px solid var(--border,#e5e7eb);border-radius:10px;background:var(--surface,#fff);cursor:pointer;font-size:.77rem;font-weight:600}.msgv2-btn--primary{background:var(--accent,#6366f1);color:#fff;border-color:var(--accent,#6366f1)}
.msgv2-folders{padding:10px;overflow-y:auto}.msgv2-folder{display:flex;align-items:center;gap:8px;width:100%;border:0;background:transparent;border-radius:10px;padding:9px 10px;cursor:pointer}.msgv2-folder.active{background:rgba(var(--accent-rgb,99,102,241),.11);color:var(--accent,#6366f1)}.badge{margin-left:auto;padding:1px 8px;border-radius:999px;font-size:.67rem;background:rgba(var(--accent-rgb,99,102,241),.14)}
.msgv2-meta{margin-top:auto;padding:10px 14px;border-top:1px solid var(--border,#e5e7eb);font-size:.7rem;color:var(--text-3,#94a3b8)}
.msgv2-search-wrap{position:relative}.msgv2-search-wrap i{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:.72rem;color:var(--text-3,#94a3b8)}.msgv2-search{width:100%;border:1px solid var(--border,#e5e7eb);border-radius:10px;padding:8px 10px 8px 28px}
.msgv2-list{overflow-y:auto;flex:1}.msgv2-item{border-bottom:1px solid var(--border,#e5e7eb);padding:10px 12px;cursor:pointer}.msgv2-item.active{background:rgba(var(--accent-rgb,99,102,241),.08)}.msgv2-item.unread .msgv2-item-name,.msgv2-item.unread .msgv2-item-subject{font-weight:700}
.msgv2-item-top{display:flex;justify-content:space-between}.msgv2-item-name{font-size:.8rem;max-width:72%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.msgv2-item-date{font-size:.7rem;color:var(--text-3,#94a3b8)}.msgv2-item-subject{font-size:.78rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.msgv2-item-snippet{font-size:.74rem;color:var(--text-3,#94a3b8);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.msgv2-main-toolbar{padding:12px 16px;border-bottom:1px solid var(--border,#e5e7eb);display:flex;align-items:center;justify-content:space-between}.msgv2-tools{display:flex;gap:8px;flex-wrap:wrap}
.msgv2-thread{flex:1;overflow-y:auto;padding:16px;background:var(--surface,#fff)}.msgv2-empty{color:var(--text-3,#94a3b8);text-align:center;padding:34px 10px}
.msgv2-bubble{border:1px solid var(--border,#e5e7eb);border-radius:12px;padding:12px;background:#fff;margin-bottom:10px}.msgv2-bubble--out{background:#f8faff;border-color:#dbe3ff}
.msgv2-bubble-meta{display:flex;justify-content:space-between;gap:10px;font-size:.72rem;color:var(--text-3,#94a3b8);margin-bottom:6px}
.msgv2-context{padding:10px;border:1px dashed var(--border,#e5e7eb);border-radius:10px;background:#fafafa;margin-bottom:12px;font-size:.75rem;color:var(--text-2,#475569)}
.msgv2-compose{border-top:1px solid var(--border,#e5e7eb);padding:12px 14px;background:var(--surface-2,#f8fafc)}.msgv2-field{display:grid;grid-template-columns:62px 1fr;align-items:center;gap:8px;margin-bottom:8px}.msgv2-field input,.msgv2-field select,.msgv2-source{width:100%;border:1px solid var(--border,#e5e7eb);border-radius:8px;padding:8px 10px}
.msgv2-editor-wrap{border:1px solid var(--border,#e5e7eb);border-radius:10px;overflow:hidden;background:#fff}.msgv2-editor-wrap .ql-toolbar{border:0!important;border-bottom:1px solid var(--border,#e5e7eb)!important;background:#f8fafc}.msgv2-editor-wrap .ql-container{border:0!important;min-height:170px}
.msgv2-source{min-height:170px;resize:vertical;font-family:ui-monospace,monospace}
@media(max-width:980px){.crm-msgv2{grid-template-columns:1fr;height:auto}.msgv2-col+.msgv2-col{border-left:0;border-top:1px solid var(--border,#e5e7eb)}}
</style>

<div class="crm-msgv2 anim">
    <aside class="msgv2-col msgv2-col--nav">
        <div class="msgv2-section-hd">
            <div class="msgv2-brand">
                <h3 class="msgv2-title"><i class="fas fa-comments" style="color:var(--accent,#6366f1);margin-right:6px"></i> Inbox CRM</h3>
                <button class="msgv2-btn msgv2-btn--primary" onclick="msgCompose()"><i class="fas fa-pen"></i> Nouveau</button>
            </div>
        </div>
        <div class="msgv2-folders">
            <button class="msgv2-folder active" data-folder="inbox" onclick="msgLoadFolder('inbox',this)"><i class="fas fa-inbox"></i> Réception <span class="badge"><?= (int)$counts['unread'] ?></span></button>
            <button class="msgv2-folder" data-folder="sent" onclick="msgLoadFolder('sent',this)"><i class="fas fa-paper-plane"></i> Envoyés <span class="badge"><?= (int)$counts['sent'] ?></span></button>
            <button class="msgv2-folder" data-folder="starred" onclick="msgLoadFolder('starred',this)"><i class="fas fa-star"></i> Suivis <span class="badge"><?= (int)$counts['starred'] ?></span></button>
            <button class="msgv2-folder" data-folder="all" onclick="msgLoadFolder('all',this)"><i class="fas fa-layer-group"></i> Tous <span class="badge"><?= (int)$counts['total'] ?></span></button>
            <button class="msgv2-folder" data-folder="trash" onclick="msgLoadFolder('trash',this)"><i class="fas fa-trash"></i> Corbeille</button>
        </div>
        <div class="msgv2-meta">
            <?php if ($isConfigured): ?>
                <i class="fas fa-plug" style="color:#22c55e;margin-right:4px"></i> Connecté SMTP : <?= htmlspecialchars($config['smtp_from'] ?? $config['smtp_user']) ?>
            <?php else: ?>
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-right:4px"></i> SMTP non configuré
            <?php endif; ?>
        </div>
    </aside>

    <section class="msgv2-col">
        <div class="msgv2-section-hd">
            <div class="msgv2-search-wrap"><i class="fas fa-search"></i><input id="msgSearchInput" class="msgv2-search" type="text" placeholder="Rechercher contact, sujet, email…" onkeyup="msgSearchDebounce()"></div>
        </div>
        <div class="msgv2-list" id="msgList"><div class="msgv2-empty">Chargement des conversations...</div></div>
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
            <div style="font-size:.72rem;color:var(--text-3,#94a3b8)">Phase 2 · Threads</div>
        </div>

        <div class="msgv2-thread" id="msgThreadPane">
            <div class="msgv2-empty" id="msgEmpty">Sélectionnez une conversation pour afficher le thread.</div>
            <div id="msgContext" style="display:none" class="msgv2-context"></div>
            <div id="msgMessages"></div>
        </div>

        <div class="msgv2-compose" id="msgComposeBox">
            <?php if (!empty($config['email_accounts'])): ?>
            <div class="msgv2-field"><label for="msgCompFrom">De</label><select id="msgCompFrom"><?php foreach ($config['email_accounts'] as $acc): ?><option value="<?= htmlspecialchars($acc) ?>" <?= ($acc===($config['email_roles']['primary']??''))?'selected':'' ?>><?= htmlspecialchars($acc) ?></option><?php endforeach; ?></select></div>
            <?php endif; ?>
            <div class="msgv2-field"><label for="msgCompTo">À</label><input type="email" id="msgCompTo" placeholder="destinataire@email.com"></div>
            <div class="msgv2-field"><label for="msgCompSubject">Objet</label><input type="text" id="msgCompSubject" placeholder="Objet de votre message"></div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="font-size:.7rem;color:var(--text-3,#94a3b8)">Threading activé (phase 2)</div>
                <button class="msgv2-btn" style="padding:6px 10px" id="msgToggleSourceBtn" onclick="msgToggleSourceMode()"><i class="fas fa-code"></i> HTML</button>
            </div>

            <div class="msgv2-editor-wrap" id="msgEditorWrap"><div id="msgQuillEditor"></div></div>
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
(function(){
    const API='/admin/api/marketing/messagerie.php';
    const CSRF='<?= addslashes($csrf) ?>';
    let currentFolder='inbox', currentThread=null, currentMessages=[], searchTimer=null, sourceMode=false, quill=null;

    function initEditor(){
        quill=new Quill('#msgQuillEditor',{theme:'snow',placeholder:'Rédigez votre email…',modules:{toolbar:[[{header:[2,3,false]}],['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link','blockquote'],['clean']]}});
    }

    function api(action,params={},method='GET'){
        const url=new URL(API,window.location.origin);
        if(method==='GET'){url.searchParams.set('action',action);Object.keys(params).forEach(k=>url.searchParams.set(k,params[k]));return fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).catch(()=>({success:false,error:'Erreur réseau'}));}
        const fd=new FormData();fd.append('action',action);fd.append('csrf_token',CSRF);Object.keys(params).forEach(k=>fd.append(k,params[k]));
        return fetch(url,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd}).then(r=>r.json()).catch(()=>({success:false,error:'Erreur réseau'}));
    }

    window.msgLoadFolder=function(folder,tabEl){
        currentFolder=folder;
        document.querySelectorAll('.msgv2-folder').forEach(t=>t.classList.remove('active')); if(tabEl)tabEl.classList.add('active');
        const search=document.getElementById('msgSearchInput')?.value||'';
        const list=document.getElementById('msgList');
        list.innerHTML='<div class="msgv2-empty"><i class="fas fa-spinner fa-spin"></i> Chargement…</div>';

        api('threads',{folder,search,limit:120}).then(data=>{
            if(!data.success||!data.threads?.length){list.innerHTML='<div class="msgv2-empty">Aucune conversation.</div>';return;}
            list.innerHTML='';
            data.threads.forEach(thread=>{
                const row=document.createElement('div');
                row.className='msgv2-item'+((thread.unread_count||0)>0?' unread':'');
                row.innerHTML=`
                    <div class="msgv2-item-top">
                        <div class="msgv2-item-name">${esc(thread.counterpart_name||thread.counterpart_email||'Inconnu')}</div>
                        <div class="msgv2-item-date">${fmtDate(thread.last_at||'')}</div>
                    </div>
                    <div class="msgv2-item-subject">${esc(thread.subject||'(sans objet)')}</div>
                    <div class="msgv2-item-snippet">${esc((thread.snippet||'').slice(0,100))}</div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:5px;font-size:.68rem;color:#94a3b8">
                        <span>${thread.message_count||0} message(s)</span>
                        ${(thread.unread_count||0)>0?`<span style="padding:1px 6px;border-radius:999px;background:rgba(99,102,241,.14);color:#6366f1">${thread.unread_count} non lu(s)</span>`:''}
                        ${thread.is_starred?'<i class="fas fa-star" style="color:#f59e0b"></i>':''}
                    </div>`;
                row.onclick=()=>msgLoadThread(thread.thread_key,row);
                list.appendChild(row);
            });
        });
    };

    function msgLoadThread(threadKey,rowEl){
        document.querySelectorAll('.msgv2-item').forEach(x=>x.classList.remove('active')); if(rowEl)rowEl.classList.add('active');
        api('thread',{thread_key:threadKey,folder:currentFolder,search:document.getElementById('msgSearchInput')?.value||''}).then(data=>{
            if(!data.success){notify(data.error||'Thread introuvable','error');return;}
            currentThread=data.thread; currentMessages=data.messages||[];
            renderThread();
        });
    }

    function renderThread(){
        const empty=document.getElementById('msgEmpty'), wrap=document.getElementById('msgMessages'), ctx=document.getElementById('msgContext');
        if(!currentMessages.length){empty.style.display='block';wrap.innerHTML='';ctx.style.display='none';return;}
        empty.style.display='none';

        const latest=currentMessages[currentMessages.length-1];
        ctx.style.display='block';
        ctx.innerHTML=`<strong>${esc(currentThread.subject||'(sans objet)')}</strong><br>`+
            `Messages: ${currentMessages.length} · Lead ID: ${Number(currentThread.lead_id||0)||'—'} · Contact ID: ${Number(currentThread.contact_id||0)||'—'}<br>`+
            `Panneau contexte CRM détaillé prévu en Phase 3.`;

        wrap.innerHTML='';
        currentMessages.forEach(msg=>{
            const isOut=msg.direction==='outbound';
            const card=document.createElement('article');
            card.className='msgv2-bubble'+(isOut?' msgv2-bubble--out':'');
            const who=isOut?(msg.from_name||msg.from_email||'Moi'):(msg.from_name||msg.from_email||'Contact');
            card.innerHTML=`
                <div class="msgv2-bubble-meta"><span>${esc(who)}</span><span>${esc(new Date(msg.sent_at||msg.created_at||Date.now()).toLocaleString('fr-FR'))}</span></div>
                <div>${msg.body_html||('<pre style="white-space:pre-wrap">'+esc(msg.body_text||'')+'</pre>')}</div>`;
            wrap.appendChild(card);
            if(!isOut && !msg.is_read){ api('mark-read',{id:msg.id},'POST'); }
        });

        document.getElementById('msgCompTo').value=(latest.direction==='outbound'?(latest.to_email||''):(latest.from_email||''));
        if(!document.getElementById('msgCompSubject').value){document.getElementById('msgCompSubject').value=normalizeSubject(latest.subject||'');}
        document.getElementById('msgStarIcon').style.color=latest.is_starred?'#f59e0b':'';
    }

    window.msgCompose=function(){ document.getElementById('msgCompTo').focus(); };
    window.msgReply=function(){ if(!currentMessages.length){notify('Sélectionnez une conversation','error');return;} quill.focus(); if(!quill.getText().trim()) quill.root.innerHTML='<p><br></p><p style="color:#94a3b8">— Réponse rapide —</p>'; };
    window.msgClearCompose=function(){ document.getElementById('msgCompTo').value='';document.getElementById('msgCompSubject').value='';document.getElementById('msgCompBodySource').value=''; if(sourceMode){document.getElementById('msgCompBodySource').value='';}else{quill.root.innerHTML='';} };

    window.msgSendNew=function(){
        const to=document.getElementById('msgCompTo').value.trim(), subject=document.getElementById('msgCompSubject').value.trim();
        if(!to||!subject){notify('Destinataire et objet requis','error');return;}
        const body_html=sourceMode?document.getElementById('msgCompBodySource').value:quill.root.innerHTML;
        const latest=currentMessages[currentMessages.length-1]||null;
        const payload={from_email:document.getElementById('msgCompFrom')?.value||'',to_email:to,subject,body_html};
        if(latest?.contact_id) payload.contact_id=latest.contact_id;
        if(latest?.lead_id) payload.lead_id=latest.lead_id;

        const btn=document.getElementById('msgSendBtn'); btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Envoi…';
        api('send',payload,'POST').then(data=>{btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> Envoyer'; if(data.success){notify('Email envoyé','success');msgClearCompose();msgLoadFolder(currentFolder,document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));} else notify('Erreur: '+(data.error||'Échec'),'error');});
    };

    window.msgToggleSourceMode=function(){
        const s=document.getElementById('msgCompBodySource'), w=document.getElementById('msgEditorWrap'), b=document.getElementById('msgToggleSourceBtn');
        sourceMode=!sourceMode;
        if(sourceMode){s.value=quill.root.innerHTML;s.style.display='block';w.style.display='none';b.innerHTML='<i class="fas fa-pen"></i> Visuel';return;}
        quill.root.innerHTML=s.value;s.style.display='none';w.style.display='block';b.innerHTML='<i class="fas fa-code"></i> HTML';
    };

    window.msgToggleStar=function(){
        if(!currentMessages.length) return;
        const latest=currentMessages[currentMessages.length-1], newStar=latest.is_starred?0:1;
        api('star',{id:latest.id,starred:newStar},'POST').then(r=>{if(r.success){latest.is_starred=newStar;document.getElementById('msgStarIcon').style.color=newStar?'#f59e0b':'';msgLoadFolder(currentFolder,document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));}});
    };

    window.msgMarkUnread=function(){
        if(!currentMessages.length) return;
        const latest=currentMessages[currentMessages.length-1];
        api('mark-unread',{id:latest.id},'POST').then(()=>{notify('Dernier message marqué non lu','success');msgLoadFolder(currentFolder,document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));});
    };

    window.msgDeleteCurrent=function(){
        if(!currentMessages.length||!confirm('Supprimer le dernier email de ce thread ?')) return;
        const latest=currentMessages[currentMessages.length-1];
        api('delete',{id:latest.id},'POST').then(()=>{notify('Email déplacé en corbeille','success');currentThread=null;currentMessages=[];renderThread();msgLoadFolder(currentFolder,document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));});
    };

    window.msgSync=function(){
        const b=document.getElementById('msgSyncBtn'); b.disabled=true; b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Sync…';
        api('sync',{limit:40}).then(d=>{b.disabled=false;b.innerHTML='<i class="fas fa-sync-alt"></i> Synchroniser'; if(d.success){notify(`Sync OK : ${d.synced||0} nouveau(x)`,'success');msgLoadFolder(currentFolder,document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`));} else notify('Erreur sync: '+(d.error||'Échec'),'error');}).catch(()=>{b.disabled=false;b.innerHTML='<i class="fas fa-sync-alt"></i> Synchroniser';notify('Erreur réseau','error');});
    };

    window.msgSearchDebounce=function(){ clearTimeout(searchTimer); searchTimer=setTimeout(()=>msgLoadFolder(currentFolder,document.querySelector(`.msgv2-folder[data-folder="${currentFolder}"]`)),250); };

    function normalizeSubject(s){ if(!s) return 'Re: '; return /^Re:/i.test(s)?s:('Re: '+s); }
    function esc(s){ const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
    function fmtDate(ds){ if(!ds) return ''; const d=new Date(ds), n=new Date(), diff=n-d; if(diff<86400000&&d.getDate()===n.getDate()) return d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}); if(diff<604800000) return d.toLocaleDateString('fr-FR',{weekday:'short'}); return d.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'}); }
    function notify(m,t){ const div=document.createElement('div'); div.style.cssText='position:fixed;top:70px;right:20px;padding:12px 18px;border-radius:8px;font-size:12px;font-weight:700;z-index:9999'; div.style.background=t==='success'?'#16a34a':'#ef4444'; div.style.color='#fff'; div.textContent=m; document.body.appendChild(div); setTimeout(()=>{div.style.opacity='0'; setTimeout(()=>div.remove(),300);},2500); }

    initEditor();
    msgLoadFolder('inbox',document.querySelector('.msgv2-folder[data-folder="inbox"]'));
})();
</script>
