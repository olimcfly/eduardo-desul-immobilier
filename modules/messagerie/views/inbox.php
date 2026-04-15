<?php
/** @var ImapService $imap */
/** @var MessageRepository $repo */
/** @var TemplateRepository $tplRepo */
/** @var int $userId */

$isConfigured = $imap->isConfigured();
$advisorEmail = $imap->getAdvisorEmail();
$threadId     = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
$threads      = $repo->getThreads($userId, 80);
$totalUnread  = $repo->getTotalUnread($userId);
$allTemplates = $tplRepo->getAll($userId);
$categories   = $tplRepo->categories();

$activeThread   = null;
$activeMessages = [];
if ($threadId > 0 && $isConfigured) {
    $activeThread = $repo->getThread($userId, $threadId);
    if ($activeThread) {
        $activeMessages = $repo->getMessages($threadId);
        $repo->markThreadRead($userId, $threadId);
    }
}
?>
<style>
/* ── Layout ── */
.msg-shell{display:flex;flex-direction:column;height:calc(100vh - 80px);gap:0;}
.msg-topbar{display:flex;align-items:center;gap:10px;padding:10px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:12px 12px 0 0;flex-shrink:0;}
.msg-topbar-left{display:flex;align-items:center;gap:8px;flex:1;}
.msg-topbar h1{font-size:1rem;font-weight:700;color:#0f172a;margin:0;}
.msg-tabs{display:flex;gap:2px;background:#f1f5f9;border-radius:8px;padding:3px;}
.msg-tab{padding:5px 14px;border-radius:6px;font-size:.8rem;font-weight:600;color:#64748b;cursor:pointer;border:0;background:none;text-decoration:none;display:inline-block;}
.msg-tab.active,.msg-tab:hover{background:#fff;color:#0f172a;box-shadow:0 1px 3px rgba(0,0,0,.08);}
.msg-conn-badge{display:inline-flex;align-items:center;gap:5px;font-size:.76rem;padding:4px 10px;border-radius:999px;font-weight:600;cursor:pointer;border:0;}
.msg-conn-badge.connected{background:#dcfce7;color:#166534;}
.msg-conn-badge.disconnected{background:#fee2e2;color:#991b1b;}
.msg-body{display:grid;grid-template-columns:300px 1fr;flex:1;overflow:hidden;border:1px solid #e5e7eb;border-top:0;border-radius:0 0 12px 12px;background:#fff;}

/* ── Sidebar ── */
.msg-sidebar{border-right:1px solid #e5e7eb;display:flex;flex-direction:column;overflow:hidden;}
.msg-sb-head{padding:10px 12px;border-bottom:1px solid #e5e7eb;display:flex;gap:6px;background:#fafafa;flex-shrink:0;}
.msg-sb-search{padding:7px 10px;border-bottom:1px solid #e5e7eb;flex-shrink:0;}
.msg-sb-search input{width:100%;padding:5px 8px 5px 28px;border:1px solid #e2e8f0;border-radius:7px;font-size:.8rem;background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 8px center;}
.msg-threads{overflow-y:auto;flex:1;}
.msg-ti{padding:10px 12px;border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background .1s;}
.msg-ti:hover{background:#f8fafc;}
.msg-ti.active{background:#eff6ff;border-left:3px solid #2563eb;}
.msg-ti.unread .msg-ti-name{font-weight:700;color:#0f172a;}
.msg-ti-row{display:flex;justify-content:space-between;align-items:baseline;}
.msg-ti-name{font-size:.83rem;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;}
.msg-ti-date{font-size:.68rem;color:#94a3b8;flex-shrink:0;}
.msg-ti-sub{font-size:.74rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:1px;}
.msg-ti-snip{font-size:.71rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.msg-ti-foot{display:flex;gap:4px;margin-top:3px;align-items:center;}
.msg-dot{width:7px;height:7px;border-radius:50%;background:#2563eb;}
.msg-crm-tag{font-size:.6rem;padding:1px 5px;border-radius:999px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;}

/* ── Main panel ── */
.msg-main{display:flex;flex-direction:column;overflow:hidden;}
.msg-mh{padding:10px 16px;border-bottom:1px solid #e5e7eb;background:#fafafa;flex-shrink:0;display:flex;align-items:center;gap:8px;}
.msg-mh-avatar{width:30px;height:30px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;color:#2563eb;font-weight:700;font-size:.82rem;flex-shrink:0;}
.msg-mh-info{flex:1;min-width:0;}
.msg-mh-name{font-size:.88rem;font-weight:700;color:#0f172a;}
.msg-mh-email{font-size:.7rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.msg-mh-actions{display:flex;gap:6px;flex-shrink:0;}
.msg-messages{flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:10px;}
.msg-bw{display:flex;flex-direction:column;}
.msg-bw.outbound{align-items:flex-end;}
.msg-bw.inbound{align-items:flex-start;}
.msg-bw-meta{font-size:.68rem;color:#94a3b8;margin-bottom:2px;}
.msg-bubble{max-width:68%;padding:8px 12px;border-radius:12px;font-size:.83rem;line-height:1.55;word-break:break-word;}
.msg-bubble.outbound{background:#2563eb;color:#fff;border-bottom-right-radius:3px;}
.msg-bubble.inbound{background:#f1f5f9;color:#0f172a;border-bottom-left-radius:3px;}
.msg-bubble a{color:inherit;opacity:.85;}
.msg-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#94a3b8;gap:8px;}
.msg-empty i{font-size:2rem;opacity:.3;}

/* ── Compose ── */
.msg-compose{flex-shrink:0;border-top:1px solid #e5e7eb;background:#fff;}
.msg-compose-to{display:flex;align-items:center;padding:8px 12px;border-bottom:1px solid #f1f5f9;gap:8px;}
.msg-compose-to span{font-size:.76rem;color:#94a3b8;white-space:nowrap;}
.msg-compose-to input{flex:1;border:0;outline:0;font-size:.83rem;color:#0f172a;}
.msg-compose-subj{display:flex;align-items:center;padding:6px 12px;border-bottom:1px solid #f1f5f9;gap:8px;}
.msg-compose-subj span{font-size:.76rem;color:#94a3b8;white-space:nowrap;}
.msg-compose-subj input{flex:1;border:0;outline:0;font-size:.83rem;color:#0f172a;}
.msg-compose-body{padding:8px 12px;}
.msg-compose-body textarea{width:100%;border:0;outline:0;font:inherit;font-size:.83rem;resize:none;min-height:60px;color:#0f172a;}
.msg-compose-toolbar{display:flex;align-items:center;gap:6px;padding:6px 12px 10px;border-top:1px solid #f1f5f9;}
.btn-ai{background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;border:0;padding:6px 12px;border-radius:8px;font-size:.76rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;}
.btn-ai:hover{opacity:.9;}
.btn-tpl{background:#f1f5f9;color:#475569;border:0;padding:6px 12px;border-radius:8px;font-size:.76rem;font-weight:600;cursor:pointer;}
.btn-tpl:hover{background:#e2e8f0;}
.btn-send{background:#2563eb;color:#fff;border:0;padding:6px 14px;border-radius:8px;font-size:.8rem;font-weight:700;cursor:pointer;margin-left:auto;}
.btn-send:disabled{opacity:.5;cursor:not-allowed;}
.compose-feedback{font-size:.73rem;color:#64748b;}

/* ── Misc ── */
.btn-sm{padding:4px 10px;border-radius:7px;font-size:.74rem;font-weight:600;cursor:pointer;border:0;}
.btn-outline{background:#fff;border:1px solid #d1d5db;color:#374151;}
.btn-outline:hover{background:#f9fafb;}
.btn-primary-sm{background:#2563eb;color:#fff;}
.btn-primary-sm:hover{background:#1d4ed8;}
.btn-danger-sm{background:#fee2e2;color:#991b1b;border:0;}
.btn-danger-sm:hover{background:#fecaca;}
.msg-no-config{padding:40px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:12px;}

/* ── Modals ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;}
.modal-box{background:#fff;border-radius:14px;padding:22px;box-shadow:0 20px 60px rgba(0,0,0,.18);width:480px;max-width:94vw;max-height:90vh;overflow-y:auto;}
.modal-title{font-size:.95rem;font-weight:700;margin:0 0 14px;}
.modal-footer{display:flex;justify-content:flex-end;gap:8px;margin-top:14px;}
.sf{display:flex;flex-direction:column;gap:9px;}
.sf label{font-size:.76rem;font-weight:600;color:#374151;margin-bottom:2px;}
.sf input,.sf select,.sf textarea{border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font:inherit;font-size:.83rem;width:100%;}
.sf textarea{resize:vertical;min-height:120px;}
.ai-result{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;margin-top:10px;display:none;}
.ai-result h4{margin:0 0 6px;font-size:.8rem;color:#166534;}
.ai-result p{margin:0;font-size:.8rem;color:#14532d;line-height:1.5;}
</style>

<!-- TOP BAR -->
<div class="msg-shell">
<div class="msg-topbar">
    <div class="msg-topbar-left">
        <div class="msg-tabs">
            <a href="/admin?module=messagerie&view=inbox"
               class="msg-tab <?= $view === 'inbox' ? 'active' : '' ?>">
                <i class="fas fa-inbox"></i> Boîte
                <?php if ($totalUnread > 0): ?>
                    <span style="background:#ef4444;color:#fff;border-radius:999px;font-size:.58rem;padding:1px 4px;margin-left:2px;"><?= $totalUnread ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin?module=messagerie&view=templates"
               class="msg-tab <?= $view === 'templates' ? 'active' : '' ?>">
                <i class="fas fa-file-lines"></i> Templates
            </a>
            <a href="/admin?module=messagerie&view=settings"
               class="msg-tab <?= $view === 'settings' ? 'active' : '' ?>">
                <i class="fas fa-gear"></i> Connexion
            </a>
        </div>
    </div>

    <?php if ($isConfigured): ?>
        <button class="msg-conn-badge connected" onclick="confirmDisconnect()">
            <i class="fas fa-circle" style="font-size:.45rem;"></i>
            <?= htmlspecialchars($advisorEmail) ?>
            <i class="fas fa-xmark" style="margin-left:2px;opacity:.6;"></i>
        </button>
    <?php else: ?>
        <a href="/admin?module=messagerie&view=settings" class="msg-conn-badge disconnected">
            <i class="fas fa-circle" style="font-size:.45rem;"></i>
            Non connecté — Configurer
        </a>
    <?php endif; ?>
</div>

<!-- BODY -->
<div class="msg-body">

    <!-- LISTE CONVERSATIONS -->
    <div class="msg-sidebar">
        <div class="msg-sb-head">
            <button class="btn-sm btn-primary-sm" onclick="openCompose()" style="flex:1;"><i class="fas fa-pen"></i> Nouveau</button>
            <button class="btn-sm btn-outline" id="syncBtn" onclick="syncInbox()" title="Synchroniser">
                <i class="fas fa-rotate-right" id="syncIcon"></i>
            </button>
        </div>
        <div class="msg-sb-search">
            <input type="text" placeholder="Rechercher..." id="threadSearch" oninput="filterThreads(this.value)">
        </div>
        <div class="msg-threads" id="threadList">
            <?php if (!$isConfigured): ?>
                <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.8rem;">
                    <i class="fas fa-plug" style="display:block;font-size:1.5rem;margin-bottom:8px;opacity:.4;"></i>
                    Configurez votre compte email pour voir vos messages.
                </div>
            <?php elseif (empty($threads)): ?>
                <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.8rem;">
                    Aucune conversation.<br>Cliquez sur <i class="fas fa-rotate-right"></i> pour synchroniser.
                </div>
            <?php else: ?>
                <?php foreach ($threads as $t):
                    $isActive  = (int)$t['id'] === $threadId;
                    $hasUnread = (int)$t['unread_count'] > 0;
                    $dateStr   = '';
                    if (!empty($t['last_message_at'])) {
                        try {
                            $d = new DateTime($t['last_message_at']);
                            $dateStr = $d->format('d/m') === date('d/m') ? $d->format('H:i') : $d->format('d/m');
                        } catch (Throwable) {}
                    }
                ?>
                <div class="msg-ti <?= $isActive ? 'active' : '' ?> <?= $hasUnread ? 'unread' : '' ?>"
                     data-name="<?= htmlspecialchars(strtolower($t['contact_name'] . ' ' . $t['contact_email'])) ?>"
                     onclick="openThread(<?= (int)$t['id'] ?>)">
                    <div class="msg-ti-row">
                        <span class="msg-ti-name"><?= htmlspecialchars($t['contact_name'] ?: $t['contact_email']) ?></span>
                        <span class="msg-ti-date"><?= $dateStr ?></span>
                    </div>
                    <div class="msg-ti-sub"><?= htmlspecialchars($t['subject']) ?></div>
                    <div class="msg-ti-snip"><?= htmlspecialchars($t['snippet'] ?? '') ?></div>
                    <div class="msg-ti-foot">
                        <?php if ($hasUnread): ?><span class="msg-dot"></span><?php endif; ?>
                        <?php if (!empty($t['contact_id'])): ?><span class="msg-crm-tag"><i class="fas fa-user"></i> CRM</span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ZONE PRINCIPALE -->
    <div class="msg-main">
        <?php if ($activeThread):
            $contactUrl = '';
            if (!empty($activeThread['contact_id'])) {
                $contactUrl = match($activeThread['contact_type'] ?? '') {
                    'crm'     => '/admin?module=convertir&lead_id='   . (int)$activeThread['contact_id'],
                    'contact' => '/admin?module=capturer&contact_id=' . (int)$activeThread['contact_id'],
                    default   => '',
                };
            }
        ?>
            <div class="msg-mh">
                <div class="msg-mh-avatar"><?= htmlspecialchars(strtoupper(substr($activeThread['contact_name'] ?: $activeThread['contact_email'], 0, 1))) ?></div>
                <div class="msg-mh-info">
                    <div class="msg-mh-name"><?= htmlspecialchars($activeThread['contact_name'] ?: $activeThread['contact_email']) ?></div>
                    <div class="msg-mh-email"><?= htmlspecialchars($activeThread['contact_email']) ?></div>
                </div>
                <div class="msg-mh-actions">
                    <?php if ($contactUrl): ?>
                        <a href="<?= htmlspecialchars($contactUrl) ?>" class="btn-sm btn-outline">
                            <i class="fas fa-user"></i> Fiche contact
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="msg-messages" id="msgMessages">
                <?php foreach ($activeMessages as $m):
                    $dir  = $m['direction'];
                    $ts   = $m['sent_at'] ?? $m['created_at'];
                    $when = '';
                    try { if ($ts) $when = (new DateTime($ts))->format('d/m/Y H:i'); } catch(Throwable) {}
                    $body = $m['body_html'] ?: nl2br(htmlspecialchars((string)($m['body_text'] ?? '')));
                ?>
                <div class="msg-bw <?= $dir ?>">
                    <div class="msg-bw-meta"><?= htmlspecialchars($m['from_name'] ?: $m['from_email']) ?> · <?= $when ?></div>
                    <div class="msg-bubble <?= $dir ?>"><?= $body ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- COMPOSE REPLY -->
            <div class="msg-compose">
                <div class="msg-compose-to">
                    <span>À :</span>
                    <input type="text" id="replyTo" value="<?= htmlspecialchars($activeThread['contact_email']) ?>" readonly>
                </div>
                <div class="msg-compose-subj">
                    <span>Objet :</span>
                    <input type="text" id="replySubject" value="Re: <?= htmlspecialchars($activeThread['subject']) ?>">
                </div>
                <div class="msg-compose-body">
                    <textarea id="replyBody" placeholder="Répondre..." rows="4"></textarea>
                </div>
                <div class="msg-compose-toolbar">
                    <button class="btn-ai" onclick="openAiModal('reply')">
                        <i class="fas fa-wand-magic-sparkles"></i> IA
                    </button>
                    <button class="btn-tpl" onclick="openTplPicker('reply')">
                        <i class="fas fa-file-lines"></i> Template
                    </button>
                    <span class="compose-feedback" id="replyFeedback"></span>
                    <button class="btn-send" id="replyBtn" onclick="sendReply()">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </div>

        <?php else: ?>
            <div class="msg-empty">
                <i class="fas fa-envelope-open"></i>
                <span style="font-size:.85rem;">Sélectionnez une conversation</span>
            </div>
        <?php endif; ?>
    </div>

</div><!-- /.msg-body -->
</div><!-- /.msg-shell -->


<!-- ══ MODAL COMPOSER NOUVEAU ══ -->
<div class="modal-overlay" id="composeModal">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 class="modal-title" style="margin:0;">Nouveau message</h3>
            <button onclick="closeModal('composeModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <div class="sf">
            <div>
                <label>À (email)</label>
                <input type="email" id="composeTo" placeholder="contact@email.fr">
            </div>
            <div>
                <label>Objet</label>
                <input type="text" id="composeSubject" placeholder="Objet de l'email">
            </div>
            <div>
                <label>Message</label>
                <textarea id="composeBody" rows="6" placeholder="Votre message..."></textarea>
            </div>
        </div>
        <div class="modal-footer" style="flex-wrap:wrap;gap:6px;">
            <button class="btn-ai" onclick="openAiModal('compose')" style="margin-right:auto;">
                <i class="fas fa-wand-magic-sparkles"></i> Rédiger avec l'IA
            </button>
            <button class="btn-tpl" onclick="openTplPicker('compose')"><i class="fas fa-file-lines"></i> Template</button>
            <span class="compose-feedback" id="composeFeedback"></span>
            <button class="btn-sm btn-outline" onclick="closeModal('composeModal')">Annuler</button>
            <button class="btn-sm btn-primary-sm" id="composeBtn" onclick="sendCompose()"><i class="fas fa-paper-plane"></i> Envoyer</button>
        </div>
    </div>
</div>

<!-- ══ MODAL IA ══ -->
<div class="modal-overlay" id="aiModal">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 class="modal-title" style="margin:0;background:linear-gradient(135deg,#7c3aed,#2563eb);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                <i class="fas fa-wand-magic-sparkles" style="-webkit-text-fill-color:#7c3aed;"></i> Assistant IA
            </h3>
            <button onclick="closeModal('aiModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <div class="sf">
            <div>
                <label>Type de demande</label>
                <select id="aiIntent">
                    <option value="suivi après visite">Suivi après visite</option>
                    <option value="confirmation de rendez-vous">Confirmation RDV</option>
                    <option value="relance prospect">Relance prospect</option>
                    <option value="transmission d'une offre">Transmission d'offre</option>
                    <option value="point hebdomadaire vendeur">Point vendeur hebdo</option>
                    <option value="suivi dossier financement">Suivi financement</option>
                    <option value="félicitations signature">Félicitations signature</option>
                    <option value="prise de contact initiale">Prise de contact</option>
                    <option value="réponse à une demande d'estimation">Réponse estimation</option>
                    <option value="autre">Autre (précisez dans le contexte)</option>
                </select>
            </div>
            <div>
                <label>Ton</label>
                <select id="aiTone">
                    <option value="professionnel">Professionnel</option>
                    <option value="amical">Amical & chaleureux</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div>
                <label>Contexte supplémentaire <small style="font-weight:400;color:#94a3b8;">(optionnel)</small></label>
                <textarea id="aiContext" rows="3" placeholder="Ex: bien au Tholonet, 4 pièces 120m², offre à 480 000€..."></textarea>
            </div>
        </div>
        <div id="aiResult" class="ai-result">
            <h4><i class="fas fa-sparkles"></i> Proposition de l'IA</h4>
            <div id="aiResultContent"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-sm btn-outline" onclick="closeModal('aiModal')">Annuler</button>
            <button class="btn-ai" id="aiGenerateBtn" onclick="generateAiDraft()">
                <i class="fas fa-wand-magic-sparkles"></i> Générer
            </button>
            <button class="btn-sm btn-primary-sm" id="aiUseBtn" style="display:none;" onclick="useAiDraft()">
                Utiliser ce brouillon
            </button>
        </div>
    </div>
</div>

<!-- ══ MODAL TEMPLATE PICKER ══ -->
<div class="modal-overlay" id="tplPickerModal">
    <div class="modal-box" style="width:580px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 class="modal-title" style="margin:0;">Choisir un template</h3>
            <button onclick="closeModal('tplPickerModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <input type="text" id="tplSearch" placeholder="Rechercher un template..." oninput="filterTpls(this.value)"
               style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font:inherit;font-size:.83rem;margin-bottom:10px;">
        <div id="tplPickerList" style="display:flex;flex-direction:column;gap:6px;max-height:380px;overflow-y:auto;">
            <?php
            $byCategory = [];
            foreach ($allTemplates as $t) {
                $byCategory[$t['category']][] = $t;
            }
            foreach ($byCategory as $cat => $tpls):
            ?>
            <div class="tpl-category-group">
                <div style="font-size:.72rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:4px 0 2px;"><?= htmlspecialchars($categories[$cat] ?? $cat) ?></div>
                <?php foreach ($tpls as $tpl): ?>
                <div class="tpl-pick-item" data-search="<?= htmlspecialchars(strtolower($tpl['name'] . ' ' . $tpl['subject'])) ?>"
                     style="border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;cursor:pointer;transition:background .1s;"
                     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''"
                     onclick="selectTemplate(<?= (int)$tpl['id'] ?>)">
                    <div style="font-size:.83rem;font-weight:600;color:#0f172a;"><?= htmlspecialchars($tpl['name']) ?></div>
                    <div style="font-size:.74rem;color:#64748b;margin-top:1px;"><?= htmlspecialchars($tpl['subject']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let aiTarget = 'reply'; // 'reply' or 'compose'
let aiDraftData = null;

function openThread(id) {
    window.location.href = '/admin?module=messagerie&thread_id=' + id;
}
function filterThreads(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.msg-ti').forEach(el => {
        el.style.display = (el.dataset.name||'').includes(q) ? '' : 'none';
    });
}

// ── Sync ──────────────────────────────────────────────────────
async function syncInbox() {
    const btn = document.getElementById('syncBtn');
    const icon = document.getElementById('syncIcon');
    btn.disabled = true; icon.classList.add('fa-spin');
    try {
        const r = await fetch('/admin?module=messagerie&action=sync');
        const d = await r.json();
        if (d.ok) { toast(d.imported + ' nouveau(x) message(s)'); setTimeout(() => location.reload(), 1100); }
        else toast(d.error || 'Erreur sync', true);
    } catch(e) { toast('Erreur réseau', true); }
    finally { btn.disabled = false; icon.classList.remove('fa-spin'); }
}

// ── Send ──────────────────────────────────────────────────────
async function sendReply() {
    const to = document.getElementById('replyTo').value;
    const subject = document.getElementById('replySubject').value.trim();
    const body = document.getElementById('replyBody').value.trim();
    const btn = document.getElementById('replyBtn');
    const fb = document.getElementById('replyFeedback');
    if (!body) { fb.textContent = 'Message vide.'; return; }
    btn.disabled = true; fb.textContent = 'Envoi...';
    const fd = new FormData();
    fd.append('to', to); fd.append('subject', subject); fd.append('body', body);
    try {
        const d = await (await fetch('/admin?module=messagerie&action=send', {method:'POST',body:fd})).json();
        if (d.ok) { document.getElementById('replyBody').value = ''; fb.textContent = ''; toast('Envoyé !'); setTimeout(() => location.reload(), 700); }
        else { fb.textContent = d.error||'Erreur.'; btn.disabled = false; }
    } catch(e) { fb.textContent = 'Erreur réseau.'; btn.disabled = false; }
}

// ── Compose ───────────────────────────────────────────────────
function openCompose() {
    document.getElementById('composeTo').value = '';
    document.getElementById('composeSubject').value = '';
    document.getElementById('composeBody').value = '';
    document.getElementById('composeFeedback').textContent = '';
    openModal('composeModal');
    setTimeout(() => document.getElementById('composeTo').focus(), 100);
}
async function sendCompose() {
    const to = document.getElementById('composeTo').value.trim();
    const subject = document.getElementById('composeSubject').value.trim();
    const body = document.getElementById('composeBody').value.trim();
    const btn = document.getElementById('composeBtn');
    const fb = document.getElementById('composeFeedback');
    if (!to||!subject||!body) { fb.textContent = 'Tous les champs sont requis.'; return; }
    btn.disabled = true; fb.textContent = 'Envoi...';
    const fd = new FormData();
    fd.append('to', to); fd.append('subject', subject); fd.append('body', body);
    try {
        const d = await (await fetch('/admin?module=messagerie&action=send', {method:'POST',body:fd})).json();
        if (d.ok) { closeModal('composeModal'); toast('Message envoyé !'); setTimeout(() => location.reload(), 700); }
        else { fb.textContent = d.error||'Erreur.'; btn.disabled = false; }
    } catch(e) { fb.textContent = 'Erreur réseau.'; btn.disabled = false; }
}

// ── IA ────────────────────────────────────────────────────────
function openAiModal(target) {
    aiTarget = target;
    aiDraftData = null;
    document.getElementById('aiResult').style.display = 'none';
    document.getElementById('aiUseBtn').style.display = 'none';
    document.getElementById('aiContext').value = '';
    openModal('aiModal');
}
async function generateAiDraft() {
    const intent = document.getElementById('aiIntent').value;
    const tone   = document.getElementById('aiTone').value;
    const ctx    = document.getElementById('aiContext').value;
    const btn    = document.getElementById('aiGenerateBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';

    const fd = new FormData();
    <?php if ($activeThread): ?>
    fd.append('contact_name', '<?= htmlspecialchars(addslashes($activeThread['contact_name'] ?? '')) ?>');
    <?php endif; ?>
    fd.append('intent', intent);
    fd.append('tone', tone);
    fd.append('context', ctx);

    try {
        const d = await (await fetch('/admin?module=messagerie&action=ai_draft', {method:'POST',body:fd})).json();
        if (d.ok) {
            aiDraftData = d;
            document.getElementById('aiResultContent').innerHTML =
                '<strong>Objet :</strong> ' + d.subject + '<br><br>' + d.body.replace(/<[^>]*>/g,'').substring(0,200) + '...';
            document.getElementById('aiResult').style.display = 'block';
            document.getElementById('aiUseBtn').style.display = 'inline-flex';
        } else {
            toast(d.error || 'Erreur IA', true);
        }
    } catch(e) { toast('Erreur réseau', true); }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-wand-magic-sparkles"></i> Régénérer'; }
}
function useAiDraft() {
    if (!aiDraftData) return;
    if (aiTarget === 'reply') {
        document.getElementById('replySubject').value = aiDraftData.subject;
        document.getElementById('replyBody').value    = aiDraftData.body.replace(/<[^>]*>/g,'').trim();
    } else {
        document.getElementById('composeSubject').value = aiDraftData.subject;
        document.getElementById('composeBody').value    = aiDraftData.body.replace(/<[^>]*>/g,'').trim();
    }
    closeModal('aiModal');
    if (aiTarget === 'compose') openModal('composeModal');
}

// ── Templates ─────────────────────────────────────────────────
function openTplPicker(target) {
    aiTarget = target;
    document.getElementById('tplSearch').value = '';
    filterTpls('');
    openModal('tplPickerModal');
}
function filterTpls(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.tpl-pick-item').forEach(el => {
        el.style.display = (el.dataset.search||'').includes(q) ? '' : 'none';
    });
    document.querySelectorAll('.tpl-category-group').forEach(g => {
        const hasVisible = [...g.querySelectorAll('.tpl-pick-item')].some(el => el.style.display !== 'none');
        g.style.display = hasVisible ? '' : 'none';
    });
}
async function selectTemplate(id) {
    const d = await (await fetch('/admin?module=messagerie&action=template_use&id=' + id)).json();
    if (!d.ok) return;
    const tpl = d.template;
    const advisorName = '<?= htmlspecialchars(addslashes((string) setting('profil_nom', APP_NAME, $userId))) ?>';
    <?php if ($activeThread): ?>
    const contactName = '<?= htmlspecialchars(addslashes($activeThread['contact_name'] ?? '')) ?>';
    <?php else: ?>
    const contactName = '';
    <?php endif; ?>
    const replacePlaceholders = (s) => s
        .replace(/\{\{contact_prenom\}\}/g, contactName.split(' ')[0] || contactName)
        .replace(/\{\{contact_nom\}\}/g, contactName)
        .replace(/\{\{conseiller_nom\}\}/g, advisorName);

    if (aiTarget === 'reply') {
        document.getElementById('replySubject').value = replacePlaceholders(tpl.subject);
        document.getElementById('replyBody').value    = replacePlaceholders(tpl.body_html.replace(/<[^>]*>/g,'').trim());
    } else {
        document.getElementById('composeSubject').value = replacePlaceholders(tpl.subject);
        document.getElementById('composeBody').value    = replacePlaceholders(tpl.body_html.replace(/<[^>]*>/g,'').trim());
    }
    closeModal('tplPickerModal');
    if (aiTarget === 'compose') openModal('composeModal');
}

// ── Disconnect ────────────────────────────────────────────────
function confirmDisconnect() {
    if (!confirm('Déconnecter ce compte email ? Les messages déjà synchronisés resteront en base.')) return;
    fetch('/admin?module=messagerie&action=disconnect_imap').then(r=>r.json()).then(d => {
        if (d.ok) { toast('Compte déconnecté.'); setTimeout(() => location.reload(), 800); }
    });
}

// ── Helpers ───────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function toast(msg, err=false) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = `position:fixed;bottom:18px;right:18px;background:${err?'#ef4444':'#22c55e'};color:#fff;padding:9px 16px;border-radius:9px;font-size:.82rem;z-index:9999;box-shadow:0 4px 14px rgba(0,0,0,.15);`;
    document.body.appendChild(t); setTimeout(() => t.remove(), 3000);
}
// Scroll messages au bas
const msgs = document.getElementById('msgMessages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;

// Pré-remplissage du compose via URL (depuis CRM, etc.)
(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('compose') !== '1') return;

    openCompose();
    const to = params.get('to') || '';
    const subject = params.get('subject') || '';
    const body = params.get('body') || '';
    if (to) document.getElementById('composeTo').value = to;
    if (subject) document.getElementById('composeSubject').value = subject;
    if (body) document.getElementById('composeBody').value = body;
})();
</script>
