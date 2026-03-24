<?php
/**
 * MODULE LEADS — Liste unifiée v3
 * /admin/modules/marketing/leads/index.php
 */

// ── DB bootstrap (si pas déjà injecté par dashboard) ─────────────────────────
if (!isset($pdo)) {
    if (isset($db)) {
        $pdo = $db;
    } else {
        try {
            $pdo = new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die('<p style="color:red;padding:20px">DB error: '.$e->getMessage().'</p>');
        }
    }
}

// ── AJAX — doit sortir AVANT tout HTML ────────────────────────────────────────
// ob_start() est lancé par dashboard.php ; on vide le buffer et on exit proprement
if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest' && isset($_GET['action']))) {
    // Vider tout ce qui aurait pu être bufferisé par le layout
    if (ob_get_level()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {

        // ── Lire une fiche ────────────────────────────────────────────────────
        case 'get_lead':
            $id  = (int)($_POST['id'] ?? 0);
            $tbl = preg_replace('/[^a-z_]/', '', $_POST['tbl'] ?? 'leads');
            try {
                $s = $pdo->prepare("SELECT * FROM `$tbl` WHERE id = ?");
                $s->execute([$id]);
                $row = $s->fetch();
                echo json_encode(['success' => (bool)$row, 'lead' => $row ?: null]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        // ── Créer lead (table leads uniquement) ───────────────────────────────
        case 'add_lead':
            $fn = trim($_POST['firstname'] ?? '');
            $ln = trim($_POST['lastname']  ?? '');
            if (!$fn && !$ln) { echo json_encode(['success'=>false,'error'=>'Prénom ou nom requis']); exit; }
            try {
                $pdo->prepare("INSERT INTO leads
                    (firstname,lastname,email,phone,city,source,notes,status,temperature,next_action,next_action_date,created_at,updated_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())")
                ->execute([
                    $fn, $ln,
                    trim($_POST['email'] ?? '') ?: null,
                    trim($_POST['phone'] ?? '') ?: null,
                    trim($_POST['city']  ?? '') ?: null,
                    $_POST['source']      ?? 'manuel',
                    trim($_POST['notes'] ?? '') ?: null,
                    $_POST['status']      ?? 'new',
                    $_POST['temperature'] ?? 'warm',
                    trim($_POST['next_action']      ?? '') ?: null,
                    trim($_POST['next_action_date'] ?? '') ?: null,
                ]);
                echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        // ── Modifier ──────────────────────────────────────────────────────────
        case 'update_lead':
            $id  = (int)($_POST['id']  ?? 0);
            $tbl = preg_replace('/[^a-z_]/', '', $_POST['tbl'] ?? 'leads');
            if (!$id) { echo json_encode(['success'=>false,'error'=>'ID manquant']); exit; }
            try {
                switch ($tbl) {
                    case 'leads':
                        $pdo->prepare("UPDATE leads SET
                            firstname=?,lastname=?,email=?,phone=?,city=?,source=?,notes=?,
                            status=?,temperature=?,next_action=?,next_action_date=?,updated_at=NOW()
                            WHERE id=?")
                        ->execute([
                            trim($_POST['firstname']??''), trim($_POST['lastname']??''),
                            trim($_POST['email']??'')           ?: null,
                            trim($_POST['phone']??'')           ?: null,
                            trim($_POST['city'] ??'')           ?: null,
                            $_POST['source']      ?? 'manuel',
                            trim($_POST['notes']??'')           ?: null,
                            $_POST['status']      ?? 'new',
                            $_POST['temperature'] ?? 'warm',
                            trim($_POST['next_action']     ??'') ?: null,
                            trim($_POST['next_action_date']??'') ?: null,
                            $id,
                        ]);
                        break;
                    case 'capture_leads':
                        $pdo->prepare("UPDATE capture_leads SET prenom=?,nom=?,email=?,tel=? WHERE id=?")
                            ->execute([trim($_POST['firstname']??''),trim($_POST['lastname']??''),trim($_POST['email']??''),trim($_POST['phone']??''),$id]);
                        break;
                    case 'demandes_estimation':
                        $pdo->prepare("UPDATE demandes_estimation SET email=?,telephone=?,statut=? WHERE id=?")
                            ->execute([trim($_POST['email']??''),trim($_POST['phone']??''),$_POST['status']??'nouveau',$id]);
                        break;
                    case 'contacts':
                        $pdo->prepare("UPDATE contacts SET firstname=?,lastname=?,email=?,phone=?,city=?,notes=?,status=?,updated_at=NOW() WHERE id=?")
                            ->execute([trim($_POST['firstname']??''),trim($_POST['lastname']??''),trim($_POST['email']??''),trim($_POST['phone']??''),trim($_POST['city']??''),trim($_POST['notes']??''),$_POST['status']??'actif',$id]);
                        break;
                    case 'financement_leads':
                        $pdo->prepare("UPDATE financement_leads SET prenom=?,nom=?,email=?,telephone=?,statut=?,notes=?,updated_at=NOW() WHERE id=?")
                            ->execute([trim($_POST['firstname']??''),trim($_POST['lastname']??''),trim($_POST['email']??''),trim($_POST['phone']??''),$_POST['status']??'nouveau',trim($_POST['notes']??''),$id]);
                        break;
                }
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        // ── Supprimer ─────────────────────────────────────────────────────────
        case 'delete_lead':
            $id  = (int)($_POST['id'] ?? 0);
            $tbl = preg_replace('/[^a-z_]/', '', $_POST['tbl'] ?? 'leads');
            try {
                $pdo->prepare("DELETE FROM `$tbl` WHERE id=?")->execute([$id]);
                if ($tbl === 'leads') {
                    try { $pdo->prepare("DELETE FROM lead_interactions WHERE lead_id=?")->execute([$id]); } catch(Exception $e){}
                }
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        // ── Email templates (bibliothèque) ────────────────────────────────────
        case 'get_email_templates':
            try {
                $search_tpl = trim($_POST['search'] ?? $_GET['search'] ?? '');
                $cat_filter = trim($_POST['category'] ?? $_GET['category'] ?? '');
                $sql_tpl = "SELECT id, name, category, subject, body_html, variables, usage_count FROM email_templates WHERE status='active'";
                $p_tpl = [];
                if ($search_tpl) { $sql_tpl .= " AND (name LIKE ? OR subject LIKE ? OR category LIKE ?)"; $t = "%{$search_tpl}%"; $p_tpl = [$t,$t,$t]; }
                if ($cat_filter) { $sql_tpl .= " AND category = ?"; $p_tpl[] = $cat_filter; }
                $sql_tpl .= " ORDER BY usage_count DESC, created_at DESC LIMIT 50";
                $st = $pdo->prepare($sql_tpl); $st->execute($p_tpl);
                $templates = $st->fetchAll();
                // Get distinct categories
                $cats = $pdo->query("SELECT DISTINCT category FROM email_templates WHERE status='active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['success' => true, 'templates' => $templates, 'categories' => $cats]);
            } catch (Exception $e) {
                echo json_encode(['success' => true, 'templates' => [], 'categories' => []]);
            }
            exit;

        // ── Seed default templates (one-time) ────────────────────────────────
        case 'seed_email_templates':
            try {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM email_templates")->fetchColumn();
                if ($count > 0) { echo json_encode(['success' => true, 'message' => 'Templates déjà présents', 'count' => $count]); exit; }
                $defaults = [
                    ['Premier contact', 'premier-contact', 'prospection', 'Bonjour {{prenom}} — Votre projet immobilier à {{ville}}',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Je me permets de vous contacter suite à votre intérêt pour un projet immobilier à <strong>{{ville}}</strong>.</p><p>Je suis Eduardo De Sul, conseiller immobilier indépendant avec eXp France, spécialisé dans la région bordelaise.</p><p>Je serais ravi d\'échanger avec vous pour :</p><ul><li>Comprendre votre projet et vos critères</li><li>Vous présenter les opportunités du marché</li><li>Vous accompagner dans toutes les étapes</li></ul><p>Seriez-vous disponible pour un échange téléphonique cette semaine ?</p><p>Bien cordialement,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France<br>{{email_agent}}</p></div>',
                     '["prenom","nom","ville","email_agent"]'],
                    ['Suivi après appel', 'suivi-apres-appel', 'suivi', 'Suite à notre échange — {{prenom}}',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Je vous remercie pour notre échange téléphonique de tout à l\'heure.</p><p>Comme convenu, je vous confirme les points suivants :</p><ul><li>Votre recherche : {{notes}}</li><li>Budget envisagé : à définir ensemble</li><li>Secteur privilégié : {{ville}}</li></ul><p>Je me mets en recherche active dès maintenant et je reviens vers vous rapidement avec des biens correspondant à vos critères.</p><p>N\'hésitez pas à me contacter si vous avez des questions.</p><p>Bien cordialement,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom","ville","notes"]'],
                    ['Proposition de biens', 'proposition-biens', 'proposition', '{{prenom}}, des biens sélectionnés pour vous à {{ville}}',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Suite à notre échange, j\'ai sélectionné plusieurs biens qui pourraient correspondre à votre projet à <strong>{{ville}}</strong>.</p><p>Je vous invite à consulter ces propositions et me faire part de votre intérêt. Je peux organiser des visites dès que vous le souhaitez.</p><p>N\'hésitez pas à me contacter pour en discuter :</p><ul><li>Par téléphone</li><li>Par email</li><li>En prenant rendez-vous directement</li></ul><p>Bien cordialement,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom","ville"]'],
                    ['Relance douce', 'relance-douce', 'relance', '{{prenom}}, où en est votre projet immobilier ?',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>J\'espère que vous allez bien !</p><p>Je me permets de revenir vers vous concernant votre projet immobilier à <strong>{{ville}}</strong>.</p><p>Le marché évolue constamment et de nouvelles opportunités apparaissent régulièrement. Je serais ravi de faire le point avec vous sur :</p><ul><li>L\'évolution de votre projet</li><li>Les nouvelles opportunités disponibles</li><li>Les tendances du marché bordelais</li></ul><p>Êtes-vous toujours dans cette démarche ? Un simple appel suffit pour reprendre contact.</p><p>Au plaisir d\'échanger,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom","ville"]'],
                    ['Invitation estimation gratuite', 'invitation-estimation', 'estimation', '{{prenom}}, estimez votre bien gratuitement',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Vous souhaitez connaître la valeur de votre bien à <strong>{{ville}}</strong> ?</p><p>Je vous propose une <strong>estimation gratuite et sans engagement</strong>, basée sur :</p><ul><li>L\'analyse des ventes récentes dans votre quartier</li><li>Les caractéristiques de votre bien</li><li>Les tendances actuelles du marché</li></ul><p>Cette estimation vous donnera une vision claire de la valeur de votre patrimoine, que vous ayez un projet de vente ou simplement par curiosité.</p><p>Contactez-moi pour planifier un rendez-vous à votre convenance.</p><p>Bien cordialement,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom","ville"]'],
                    ['Confirmation RDV', 'confirmation-rdv', 'rdv', 'Confirmation de votre rendez-vous — {{prenom}}',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Je vous confirme notre rendez-vous :</p><div style="background:#f0f4ff;border-radius:8px;padding:15px;margin:15px 0;border-left:4px solid #4f46e5"><p style="margin:0"><strong>Date :</strong> À confirmer</p><p style="margin:5px 0 0"><strong>Lieu :</strong> {{ville}}</p><p style="margin:5px 0 0"><strong>Objet :</strong> {{notes}}</p></div><p>Si vous avez besoin de modifier l\'horaire ou avez des questions, n\'hésitez pas à me contacter.</p><p>À très bientôt,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom","ville","notes"]'],
                    ['Remerciement après visite', 'remerciement-visite', 'suivi', 'Merci pour votre visite — {{prenom}}',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Je tenais à vous remercier pour la visite d\'aujourd\'hui.</p><p>J\'espère que le bien a retenu votre attention. N\'hésitez pas à me faire part de vos impressions, questions ou réserves — je suis là pour vous accompagner.</p><p>Si ce bien ne correspond pas tout à fait à vos attentes, je continue mes recherches pour vous trouver la perle rare.</p><p>À très bientôt,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom"]'],
                    ['Mandat — documents nécessaires', 'mandat-documents', 'transaction', '{{prenom}}, documents nécessaires pour votre dossier',
                     '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px"><h2 style="color:#1e3a5f">Bonjour {{prenom}},</h2><p>Dans le cadre de votre projet immobilier, voici la liste des documents nécessaires :</p><ul><li>Pièce d\'identité en cours de validité</li><li>Justificatif de domicile récent</li><li>3 derniers bulletins de salaire</li><li>Dernier avis d\'imposition</li><li>Relevés bancaires des 3 derniers mois</li></ul><p>N\'hésitez pas à me les transmettre par email ou lors de notre prochain rendez-vous.</p><p>Je reste à votre disposition pour toute question.</p><p>Bien cordialement,<br><strong>Eduardo De Sul</strong><br>Conseiller Immobilier — eXp France</p></div>',
                     '["prenom","nom"]'],
                ];
                $stmt = $pdo->prepare("INSERT INTO email_templates (name,slug,category,subject,body_html,body_text,variables,status,usage_count,created_at,updated_at) VALUES (?,?,?,?,?,?,?,'active',0,NOW(),NOW())");
                foreach ($defaults as $t) {
                    $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], strip_tags($t[4]), $t[5]]);
                }
                echo json_encode(['success' => true, 'message' => count($defaults) . ' templates créés', 'count' => count($defaults)]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        // ── Envoyer un email depuis la fiche lead ─────────────────────────────
        case 'send_email':
            $to   = trim($_POST['to'] ?? '');
            $subj = trim($_POST['subject'] ?? '');
            $body = $_POST['body'] ?? '';
            $lid  = (int)($_POST['lead_id'] ?? 0);
            $tplId = (int)($_POST['template_id'] ?? 0);
            if (!$to || !$subj) { echo json_encode(['success'=>false,'error'=>'Destinataire et sujet requis']); exit; }
            try {
                // Use the marketing emails API send logic
                $emailApiPath = __DIR__ . '/../../api/marketing/emails.php';
                $sent = false; $error = '';
                $phpmailer = dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
                if (file_exists($phpmailer)) {
                    require_once $phpmailer;
                    require_once dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/SMTP.php';
                    require_once dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/Exception.php';
                    try {
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        // Load SMTP config from settings
                        $cfg = [];
                        try { $sc=$pdo->query("SELECT setting_key,setting_value FROM settings WHERE setting_key LIKE 'smtp%' OR setting_key LIKE 'email%'"); while($r=$sc->fetch()) $cfg[$r['setting_key']]=$r['setting_value']; } catch(Exception $e2){}
                        $smtpFile = dirname(__DIR__, 2) . '/config/smtp.php';
                        if (file_exists($smtpFile)) { $fc = include $smtpFile; if (is_array($fc)) $cfg = array_merge($cfg, $fc); }
                        $host = $cfg['smtp_host'] ?? $cfg['SMTP_HOST'] ?? '';
                        if ($host) {
                            $mail->isSMTP();
                            $mail->Host       = $host;
                            $mail->SMTPAuth   = true;
                            $mail->Username   = $cfg['smtp_user'] ?? $cfg['SMTP_USER'] ?? '';
                            $mail->Password   = $cfg['smtp_pass'] ?? $cfg['SMTP_PASS'] ?? '';
                            $mail->SMTPSecure = $cfg['smtp_secure'] ?? 'tls';
                            $mail->Port       = (int)($cfg['smtp_port'] ?? $cfg['SMTP_PORT'] ?? 587);
                        }
                        $from     = $cfg['smtp_from'] ?? $cfg['SMTP_FROM'] ?? $cfg['email_from'] ?? ADMIN_EMAIL;
                        $fromName = $cfg['smtp_from_name'] ?? $cfg['SMTP_FROM_NAME'] ?? SITE_TITLE;
                        $mail->setFrom($from, $fromName);
                        $mail->addAddress($to);
                        $mail->isHTML(true); $mail->CharSet = 'UTF-8';
                        $mail->Subject = $subj; $mail->Body = $body; $mail->AltBody = strip_tags($body);
                        $mail->send(); $sent = true;
                    } catch (Exception $e) { $error = $e->getMessage(); }
                } elseif (function_exists('mail')) {
                    $h = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
                    $sent = @mail($to, $subj, $body, $h);
                    if (!$sent) $error = 'mail() returned false';
                } else {
                    $error = 'Aucun moteur email disponible';
                }
                // Log as interaction
                if ($lid > 0) {
                    try { $pdo->prepare("INSERT INTO lead_interactions (lead_id,type,subject,content,interaction_date,outcome) VALUES (?,'email',?,?,NOW(),'positif')")
                        ->execute([$lid, $subj, strip_tags($body)]); } catch(Exception $e2){}
                    try { $pdo->prepare("UPDATE leads SET updated_at=NOW() WHERE id=?")->execute([$lid]); } catch(Exception $e2){}
                }
                // Log in crm_emails
                try { $pdo->prepare("INSERT INTO crm_emails (lead_id,direction,to_email,subject,body_html,folder,sent_at,created_at) VALUES (?,'outbound',?,?,?,'sent',NOW(),NOW())")
                    ->execute([$lid, $to, $subj, $body]); } catch(Exception $e2){}
                // Update template usage
                if ($tplId > 0) {
                    try { $pdo->prepare("UPDATE email_templates SET usage_count=usage_count+1 WHERE id=?")->execute([$tplId]); } catch(Exception $e2){}
                }
                echo json_encode($sent ? ['success'=>true,'message'=>"Email envoyé à {$to}"] : ['success'=>false,'error'=>"Échec: {$error}"]);
            } catch (Exception $e) {
                echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
            }
            exit;

        // ── Interactions ──────────────────────────────────────────────────────
        case 'get_interactions':
            $lid = (int)($_POST['lead_id'] ?? 0);
            try {
                $s = $pdo->prepare("SELECT * FROM lead_interactions WHERE lead_id=? ORDER BY COALESCE(interaction_date,created_at) DESC");
                $s->execute([$lid]);
                echo json_encode(['success'=>true,'interactions'=>$s->fetchAll()]);
            } catch (Exception $e) {
                echo json_encode(['success'=>true,'interactions'=>[]]);
            }
            exit;

        case 'add_interaction':
            $lid  = (int)($_POST['lead_id'] ?? 0);
            $type = in_array($_POST['type']??'',['note','appel','email','rdv','sms','visite']) ? $_POST['type'] : 'note';
            try {
                $pdo->prepare("INSERT INTO lead_interactions (lead_id,type,subject,content,interaction_date,duration_minutes,outcome) VALUES (?,?,?,?,?,?,?)")
                    ->execute([$lid,$type,trim($_POST['subject']??'')?:null,trim($_POST['content']??'')?:null,trim($_POST['interaction_date']??'')?:null,(int)($_POST['duration_minutes']??0)?:null,$_POST['outcome']??null]);
                try { $pdo->prepare("UPDATE leads SET updated_at=NOW() WHERE id=?")->execute([$lid]); } catch(Exception $e){}
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        // ── Export CSV ────────────────────────────────────────────────────────
        case 'export':
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="leads-'.date('Y-m-d').'.csv"');
            $out = fopen('php://output','w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Source','Prénom','Nom','Email','Téléphone','Ville','Statut','Date'], ';');
            foreach (getAllLeads($pdo,'','','created_at','DESC','',0,99999)['rows'] as $r)
                fputcsv($out, [$r['_src_label'],$r['_fn'],$r['_ln'],$r['_email']??'',$r['_phone']??'',$r['_city']??'',$r['_status']??'',date('d/m/Y H:i',strtotime($r['created_at']))], ';');
            fclose($out);
            exit;

        default:
            echo json_encode(['success'=>false,'error'=>'Action inconnue: '.$action]);
            exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// FONCTION UNIFIÉE — toutes sources
// ══════════════════════════════════════════════════════════════════════════════
function getAllLeads(PDO $pdo, string $search, string $srcFilter, string $sort, string $order, string $statusFlt, int $offset, int $limit): array {
    $rows = [];

    // 1. leads (CRM manuel / pipeline)
    if (!$srcFilter || in_array($srcFilter,['Manuel','Site web','GMB','Facebook','Google','Téléphone','Recommandation','Flyer','Boîtage','Salon'])) {
        try {
            $w=['1=1'];$p=[];
            if ($search) { $t="%$search%"; $w[]="(firstname LIKE ? OR lastname LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ?)"; $p=[$t,$t,$t,$t,$t]; }
            if ($statusFlt) { $w[]="status=?"; $p[]=$statusFlt; }
            $s=$pdo->prepare("SELECT *,'leads' AS _tbl FROM leads WHERE ".implode(' AND ',$w)." ORDER BY created_at DESC");
            $s->execute($p);
            $srcMap=['site_web'=>'Site web','gmb'=>'GMB','pub_facebook'=>'Facebook','pub_google'=>'Google','recommandation'=>'Recommandation','telephone'=>'Téléphone','flyer'=>'Flyer','boitage'=>'Boîtage','salon'=>'Salon','estimation'=>'Estimation','capture'=>'Capture','financement'=>'Financement','manuel'=>'Manuel','autre'=>'Autre'];
            foreach ($s->fetchAll() as $r) {
                $r['_fn']  = trim($r['firstname'] ?? '');
                $r['_ln']  = trim($r['lastname']  ?? '');
                if (!$r['_fn'] && !$r['_ln'] && !empty($r['full_name'])) {
                    $pts = explode(' ', trim($r['full_name']), 2);
                    $r['_fn'] = $pts[0]; $r['_ln'] = $pts[1] ?? '';
                }
                $r['_email']=$r['email']??null; $r['_phone']=$r['phone']??null; $r['_city']=$r['city']??null;
                $r['_status']=$r['status']??''; $r['_score']=(int)($r['score']??0);
                $src = $r['source'] ?? 'manuel';
                $r['_src_label'] = $srcMap[$src] ?? ucfirst($src);
                $r['_src_key']   = 'leads';
                if ($srcFilter && $r['_src_label'] !== $srcFilter) continue;
                $rows[] = $r;
            }
        } catch (Exception $e) {}
    }

    // 2. capture_leads (prenom, nom, email, tel, message)
    if (!$srcFilter || $srcFilter === 'Capture') {
        try {
            $w=['1=1'];$p=[];
            if ($search) { $t="%$search%"; $w[]="(prenom LIKE ? OR nom LIKE ? OR email LIKE ? OR tel LIKE ?)"; $p=[$t,$t,$t,$t]; }
            $s=$pdo->prepare("SELECT *,'capture_leads' AS _tbl FROM capture_leads WHERE ".implode(' AND ',$w)." ORDER BY created_at DESC");
            $s->execute($p);
            foreach ($s->fetchAll() as $r) {
                $r['_fn']=$r['prenom']??''; $r['_ln']=$r['nom']??''; $r['_email']=$r['email']??null; $r['_phone']=$r['tel']??null;
                $r['_city']=null; $r['_status']=$r['injected_crm']?'contacté':'nouveau'; $r['_score']=0;
                $r['_src_label']='Capture'; $r['_src_key']='capture_leads';
                $r['notes']=$r['message']??null;
                $rows[]=$r;
            }
        } catch (Exception $e) {}
    }

    // 3. demandes_estimation (email, telephone, ville, type_bien, surface, estimation_moyenne)
    if (!$srcFilter || $srcFilter === 'Estimation') {
        try {
            $w=['1=1'];$p=[];
            if ($search) { $t="%$search%"; $w[]="(email LIKE ? OR telephone LIKE ? OR ville LIKE ?)"; $p=[$t,$t,$t]; }
            $s=$pdo->prepare("SELECT *,'demandes_estimation' AS _tbl FROM demandes_estimation WHERE ".implode(' AND ',$w)." ORDER BY created_at DESC");
            $s->execute($p);
            foreach ($s->fetchAll() as $r) {
                $r['_fn']=''; $r['_ln']=trim(($r['type_bien']??'Bien').' '.($r['ville']??''));
                $r['_email']=$r['email']??null; $r['_phone']=$r['telephone']??null; $r['_city']=$r['ville']??null;
                $r['_status']=$r['statut']??'nouveau'; $r['_score']=0;
                $r['_src_label']='Estimation'; $r['_src_key']='demandes_estimation';
                $parts=array_filter([$r['type_bien']??'', $r['surface']?($r['surface'].'m²'):'', $r['estimation_moyenne']?('~'.number_format($r['estimation_moyenne'],0,',',' ').'€'):'']);
                $r['notes']=implode(' — ',$parts);
                $rows[]=$r;
            }
        } catch (Exception $e) {}
    }

    // 4. contacts (firstname/prenom, lastname/nom, email, phone/telephone)
    if (!$srcFilter || $srcFilter === 'Contact') {
        try {
            $w=['1=1'];$p=[];
            if ($search) { $t="%$search%"; $w[]="(firstname LIKE ? OR lastname LIKE ? OR nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR phone LIKE ?)"; $p=[$t,$t,$t,$t,$t,$t]; }
            $s=$pdo->prepare("SELECT *,'contacts' AS _tbl FROM contacts WHERE ".implode(' AND ',$w)." ORDER BY created_at DESC");
            $s->execute($p);
            foreach ($s->fetchAll() as $r) {
                $r['_fn']=$r['firstname']??$r['prenom']??''; $r['_ln']=$r['lastname']??$r['nom']??'';
                $r['_email']=$r['email']??null; $r['_phone']=$r['phone']??$r['telephone']??null; $r['_city']=$r['city']??null;
                $r['_status']=$r['status']??'actif'; $r['_score']=(int)($r['rating']??0);
                $r['_src_label']='Contact'; $r['_src_key']='contacts';
                $rows[]=$r;
            }
        } catch (Exception $e) {}
    }

    // 5. financement_leads (prenom, nom, email, telephone, type_projet, montant_projet)
    if (!$srcFilter || $srcFilter === 'Financement') {
        try {
            $w=['1=1'];$p=[];
            if ($search) { $t="%$search%"; $w[]="(prenom LIKE ? OR nom LIKE ? OR email LIKE ? OR telephone LIKE ?)"; $p=[$t,$t,$t,$t]; }
            $s=$pdo->prepare("SELECT *,'financement_leads' AS _tbl FROM financement_leads WHERE ".implode(' AND ',$w)." ORDER BY created_at DESC");
            $s->execute($p);
            foreach ($s->fetchAll() as $r) {
                $r['_fn']=$r['prenom']??''; $r['_ln']=$r['nom']??''; $r['_email']=$r['email']??null; $r['_phone']=$r['telephone']??null;
                $r['_city']=null; $r['_status']=$r['statut']??'nouveau'; $r['_score']=0;
                $r['_src_label']='Financement'; $r['_src_key']='financement_leads';
                $r['notes']=trim(($r['type_projet']??'Projet').($r['montant_projet']?' — '.number_format($r['montant_projet'],0,',',' ').'€':'').($r['notes']?' | '.$r['notes']:''));
                $rows[]=$r;
            }
        } catch (Exception $e) {}
    }

    // Dédoublonnage email (leads en priorité)
    $seen=[]; $deduped=[];
    foreach ($rows as $r) {
        $key = strtolower(trim($r['_email'] ?? ''));
        if ($key && isset($seen[$key])) continue;
        if ($key) $seen[$key]=true;
        $deduped[]=$r;
    }

    // Tri
    usort($deduped, function($a,$b) use ($sort,$order) {
        $va = match($sort) { '_fn'=>strtolower($a['_fn'].$a['_ln']), '_email'=>strtolower($a['_email']??''), '_score'=>(int)$a['_score'], default=>$a['created_at']??'' };
        $vb = match($sort) { '_fn'=>strtolower($b['_fn'].$b['_ln']), '_email'=>strtolower($b['_email']??''), '_score'=>(int)$b['_score'], default=>$b['created_at']??'' };
        $cmp = is_int($va) ? ($va<=>$vb) : strcmp((string)$va,(string)$vb);
        return $order==='DESC' ? -$cmp : $cmp;
    });

    $total = count($deduped);
    return ['rows' => array_slice($deduped,$offset,$limit), 'total' => $total];
}

// ── Paramètres page ───────────────────────────────────────────────────────────
$search    = trim($_GET['search'] ?? '');
$srcFilter = $_GET['src']    ?? '';
$statusFlt = $_GET['status'] ?? '';
$sortBy    = in_array($_GET['sort']??'',['created_at','_fn','_email','_score']) ? $_GET['sort'] : 'created_at';
$sortOrder = ($_GET['order']??'DESC') === 'ASC' ? 'ASC' : 'DESC';
$page      = max(1, (int)($_GET['p'] ?? 1));
$perPage   = 25;
$offset    = ($page-1)*$perPage;

$result     = getAllLeads($pdo, $search, $srcFilter, $sortBy, $sortOrder, $statusFlt, $offset, $perPage);
$leads      = $result['rows'];
$totalLeads = $result['total'];
$totalPages = max(1, ceil($totalLeads/$perPage));

// Stats
$statsAll = getAllLeads($pdo,'','','created_at','DESC','',0,99999)['rows'];
$sTotal   = count($statsAll);
$sMonth   = count(array_filter($statsAll, fn($r)=>substr($r['created_at'],0,7)===date('Y-m')));
$sEstim   = count(array_filter($statsAll, fn($r)=>($r['_src_label']??'')==='Estimation'));
$sCapture = count(array_filter($statsAll, fn($r)=>($r['_src_label']??'')==='Capture'));
$sLeads   = count(array_filter($statsAll, fn($r)=>($r['_src_key']??'')==='leads'));

$pqs = http_build_query(array_filter(['search'=>$search,'src'=>$srcFilter,'status'=>$statusFlt,'sort'=>$sortBy,'order'=>$sortOrder]));
function lvSort($col,$cs,$co,$qs){ $o=($cs===$col&&$co==='DESC')?'ASC':'DESC'; return '?page=leads&sort='.$col.'&order='.$o.($qs?'&'.$qs:''); }

$statusLabels=['new'=>['label'=>'Nouveau','bg'=>'#e0e7ff','c'=>'#4f46e5'],'contacted'=>['label'=>'Contacté','bg'=>'#cffafe','c'=>'#0e7490'],'qualified'=>['label'=>'Qualifié','bg'=>'#ede9fe','c'=>'#7c3aed'],'proposal'=>['label'=>'Proposition','bg'=>'#fef3c7','c'=>'#b45309'],'negotiation'=>['label'=>'Négociation','bg'=>'#fce7f3','c'=>'#be185d'],'won'=>['label'=>'Gagné','bg'=>'#d1fae5','c'=>'#065f46'],'lost'=>['label'=>'Perdu','bg'=>'#f1f5f9','c'=>'#64748b'],'nouveau'=>['label'=>'Nouveau','bg'=>'#e0e7ff','c'=>'#4f46e5'],'contacté'=>['label'=>'Contacté','bg'=>'#cffafe','c'=>'#0e7490'],'actif'=>['label'=>'Actif','bg'=>'#d1fae5','c'=>'#065f46'],'traité'=>['label'=>'Traité','bg'=>'#f0fdf4','c'=>'#15803d'],'transmis'=>['label'=>'Transmis','bg'=>'#fef3c7','c'=>'#b45309'],'converti'=>['label'=>'Converti','bg'=>'#d1fae5','c'=>'#065f46']];
$srcStyles=['Manuel'=>['bg'=>'#f1f5f9','c'=>'#475569','icon'=>'user-edit'],'Capture'=>['bg'=>'#ede9fe','c'=>'#7c3aed','icon'=>'magnet'],'Estimation'=>['bg'=>'#fef3c7','c'=>'#b45309','icon'=>'chart-bar'],'Contact'=>['bg'=>'#dbeafe','c'=>'#1d4ed8','icon'=>'address-book'],'Financement'=>['bg'=>'#fce7f3','c'=>'#be185d','icon'=>'hand-holding-usd'],'Site web'=>['bg'=>'#dbeafe','c'=>'#1d4ed8','icon'=>'globe'],'GMB'=>['bg'=>'#d1fae5','c'=>'#065f46','icon'=>'map-marker-alt'],'Facebook'=>['bg'=>'#eff6ff','c'=>'#2563eb','icon'=>'facebook-f'],'Google'=>['bg'=>'#fce7f3','c'=>'#be185d','icon'=>'google'],'Téléphone'=>['bg'=>'#ecfdf5','c'=>'#059669','icon'=>'phone'],'Recommandation'=>['bg'=>'#fff7ed','c'=>'#c2410c','icon'=>'heart'],'Boîtage'=>['bg'=>'#f0fdf4','c'=>'#15803d','icon'=>'home'],'Flyer'=>['bg'=>'#fefce8','c'=>'#a16207','icon'=>'file-alt'],'Salon'=>['bg'=>'#fdf2f8','c'=>'#9d174d','icon'=>'calendar-alt']];
$tempLabels=['cold'=>['label'=>'Froid','c'=>'#0369a1','bg'=>'#e0f2fe','icon'=>'snowflake'],'warm'=>['label'=>'Tiède','c'=>'#b45309','bg'=>'#fef3c7','icon'=>'sun'],'hot'=>['label'=>'Chaud','c'=>'#dc2626','bg'=>'#fee2e2','icon'=>'fire-alt']];
?>
<style>
/* ── Reset scope ─────────────────────────────────────────────────────────── */
.lv *{box-sizing:border-box}
/* Hero */
.lv-hero{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1a4d7a 100%);color:#fff;border-radius:14px;padding:26px 30px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
.lv-hero h1{font-size:1.35rem;font-weight:800;margin:0;display:flex;align-items:center;gap:10px}
.lv-hero p{margin:4px 0 0;opacity:.6;font-size:.82rem}
.lv-hero-btns{display:flex;gap:8px;flex-wrap:wrap}
/* Stats */
.lv-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px}
@media(max-width:1100px){.lv-stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:680px){.lv-stats{grid-template-columns:1fr 1fr}}
.lv-stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:15px 16px;display:flex;align-items:center;gap:11px;transition:.2s;cursor:pointer;text-decoration:none}
.lv-stat:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.07)}
.lv-stat.on{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.lv-stat-ico{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.lv-stat-val{font-size:1.55rem;font-weight:800;color:#111827;line-height:1}
.lv-stat-lbl{font-size:.69rem;color:#6b7280;margin-top:2px;font-weight:500}
/* Tableau */
.lv-card{background:#fff;border:1px solid #e2e8f0;border-radius:13px;overflow:hidden}
.lv-toolbar{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;gap:10px}
.lv-search-box{display:flex;align-items:center;gap:7px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:8px 13px;flex:1;min-width:200px;max-width:340px}
.lv-search-box i{color:#94a3b8;font-size:.75rem}
.lv-search-box input{border:none;background:none;outline:none;font-size:.82rem;color:#374151;width:100%}
.lv-filters{display:flex;gap:7px;flex-wrap:wrap;align-items:center}
.lv-sel{padding:7px 26px 7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:.77rem;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%2394a3b8' d='M5 6L0 0h10z'/%3E%3C/svg%3E") no-repeat right 8px center;appearance:none;color:#374151;cursor:pointer}
.lv-sel:focus{outline:none;border-color:#6366f1}
/* Boutons */
.lv-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;border-radius:9px;font-size:.81rem;font-weight:600;border:none;cursor:pointer;transition:.2s;text-decoration:none;white-space:nowrap}
.lv-btn-primary{background:#6366f1;color:#fff}.lv-btn-primary:hover{background:#4f46e5}
.lv-btn-ghost{background:rgba(255,255,255,.13);color:#fff;border:1px solid rgba(255,255,255,.22)}.lv-btn-ghost:hover{background:rgba(255,255,255,.22)}
.lv-btn-outline{background:#fff;color:#374151;border:1px solid #e2e8f0}.lv-btn-outline:hover{background:#f8fafc}
.lv-btn-sm{padding:6px 11px;font-size:.75rem}
.lv-btn-danger{background:#fee2e2;color:#dc2626;border:1px solid #fecaca}.lv-btn-danger:hover{background:#fecaca}
/* Table */
.lv-table{width:100%;border-collapse:collapse}
.lv-table th{padding:9px 13px;text-align:left;font-size:.67rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid #e2e8f0;background:#f8fafc;white-space:nowrap}
.lv-table th a{color:#6b7280;text-decoration:none}.lv-table th a:hover{color:#374151}
.lv-table td{padding:10px 13px;border-bottom:1px solid #f1f5f9;font-size:.81rem;vertical-align:middle}
.lv-table tr:hover td{background:#fafbff;cursor:pointer}
.lv-table tr:last-child td{border-bottom:none}
/* Éléments table */
.lv-av{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0}
.lv-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:.67rem;font-weight:700;white-space:nowrap}
.lv-src{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:.66rem;font-weight:600}
.lv-acts{display:flex;gap:3px}
.lv-act{width:27px;height:27px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:.68rem;transition:.15s;text-decoration:none}
.lv-act-v{background:#e0e7ff;color:#4f46e5}.lv-act-v:hover{background:#c7d2fe}
.lv-act-c{background:#ecfdf5;color:#059669}.lv-act-c:hover{background:#bbf7d0}
.lv-act-d{background:#fee2e2;color:#dc2626}.lv-act-d:hover{background:#fecaca}
/* Pagination */
.lv-pag{display:flex;justify-content:space-between;align-items:center;padding:11px 16px;border-top:1px solid #e2e8f0;font-size:.76rem;color:#6b7280;flex-wrap:wrap;gap:8px}
.lv-pag-nn{display:flex;gap:4px}
.lv-pag-nn a,.lv-pag-nn span{padding:5px 9px;border:1px solid #e2e8f0;border-radius:6px;font-size:.76rem;text-decoration:none;color:#374151;transition:.15s}
.lv-pag-nn a:hover{background:#f8fafc}
.lv-pag-nn span.on{background:#6366f1;color:#fff;border-color:#6366f1}
/* Empty */
.lv-empty{padding:50px 20px;text-align:center;color:#94a3b8}
.lv-empty i{font-size:2rem;display:block;margin-bottom:10px;opacity:.2}

/* ══ SLIDE-OVER ══════════════════════════════════════════════════════════════ */
.lv-ov{position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;display:none;backdrop-filter:blur(3px)}
.lv-ov.on{display:block}
.lv-sh{position:fixed;top:0;right:0;height:100vh;width:720px;max-width:96vw;background:#f8fafc;z-index:1001;box-shadow:-8px 0 40px rgba(0,0,0,.15);transform:translateX(100%);transition:transform .32s cubic-bezier(.16,1,.3,1);display:flex;flex-direction:column}
.lv-sh.on{transform:translateX(0)}
/* Sheet header */
.lv-sh-hd{background:linear-gradient(135deg,#1e293b 0%,#1a4d7a 100%);color:#fff;padding:20px 22px;flex-shrink:0}
.lv-sh-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px}
.lv-sh-av{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:700;color:#fff;flex-shrink:0;background:#6366f1}
.lv-sh-name{font-size:1.05rem;font-weight:700;margin:0;line-height:1.2}
.lv-sh-sub{font-size:.77rem;opacity:.6;margin:3px 0 0}
.lv-sh-x{width:30px;height:30px;border:none;background:rgba(255,255,255,.15);border-radius:7px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;flex-shrink:0}
.lv-sh-x:hover{background:rgba(255,255,255,.28);transform:rotate(90deg)}
.lv-sh-tags{display:flex;gap:5px;flex-wrap:wrap}
.lv-sh-tag{padding:2px 9px;border-radius:20px;font-size:.67rem;font-weight:700}
/* Quick actions */
.lv-qrow{display:flex;gap:6px;padding:10px 16px;background:#fff;border-bottom:1px solid #e2e8f0;flex-shrink:0}
.lv-qa{flex:1;display:flex;align-items:center;justify-content:center;gap:5px;padding:8px 6px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:.74rem;font-weight:600;cursor:pointer;transition:.2s;color:#374151}
.lv-qa:hover{transform:translateY(-1px);box-shadow:0 3px 8px rgba(0,0,0,.07)}
.lv-qa.q-blue{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
.lv-qa.q-green{background:#f0fdf4;border-color:#bbf7d0;color:#15803d}
.lv-qa.q-amber{background:#fffbeb;border-color:#fde68a;color:#b45309}
.lv-qa.q-purple{background:#faf5ff;border-color:#e9d5ff;color:#7c3aed}
/* Tabs */
.lv-tabs{display:flex;border-bottom:1px solid #e2e8f0;background:#fff;flex-shrink:0;overflow-x:auto}
.lv-tab{padding:9px 16px;font-size:.79rem;font-weight:500;border:none;background:none;cursor:pointer;color:#6b7280;border-bottom:2px solid transparent;transition:.15s;white-space:nowrap;display:flex;align-items:center;gap:5px;flex-shrink:0}
.lv-tab.on{color:#6366f1;border-bottom-color:#6366f1;font-weight:700}
.lv-tab:hover:not(.on){color:#374151}
.lv-sh-body{flex:1;overflow-y:auto;padding:18px}
/* Infos grid */
.lv-ig{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-bottom:12px}
.lv-ic{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px}
.lv-ic-l{font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px}
.lv-ic-v{font-size:.81rem;color:#374151;font-weight:500}
.lv-ic-v a{color:#4f46e5;text-decoration:none}.lv-ic-v a:hover{text-decoration:underline}
/* Notes bloc */
.lv-note-bloc{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:11px 13px;margin-bottom:10px}
.lv-note-title{font-size:.67rem;font-weight:700;color:#92400e;margin-bottom:4px}
.lv-note-txt{font-size:.79rem;color:#78350f;white-space:pre-wrap;line-height:1.5}
/* Next action */
.lv-na-bloc{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:11px 13px;margin-bottom:10px}
.lv-na-title{font-size:.67rem;font-weight:700;color:#1e40af;margin-bottom:3px}
.lv-na-txt{font-size:.81rem;font-weight:600;color:#1e3a8a}

/* ── FORMULAIRE EDIT dans le slide-over ─────────────────────────────────── */
.lv-ef{display:flex;flex-direction:column;gap:12px}
.lv-ef-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.lv-ef-grp{display:flex;flex-direction:column;gap:4px}
.lv-ef-grp label{font-size:.74rem;font-weight:600;color:#374151}
.lv-ef-sec{font-size:.77rem;font-weight:700;color:#1e293b;padding:6px 0;border-bottom:1px solid #e2e8f0;margin-top:4px;display:flex;align-items:center;gap:5px}
.lv-ef-sec i{color:#6366f1}
.lv-in{width:100%;padding:8px 11px;border:1px solid #e2e8f0;border-radius:8px;font-size:.81rem;color:#374151;outline:none;transition:.15s;font-family:inherit;background:#fff}
.lv-in:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.lv-ta{resize:vertical;min-height:72px}
/* Timeline */
.lv-tl{position:relative;padding-left:26px}
.lv-tl::before{content:'';position:absolute;left:9px;top:0;bottom:0;width:2px;background:#e2e8f0}
.lv-tl-item{position:relative;margin-bottom:12px}
.lv-tl-dot{position:absolute;left:-26px;top:2px;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.55rem;color:#fff}
.lv-tl-card{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px}
.lv-tl-head{display:flex;justify-content:space-between;margin-bottom:3px}
.lv-tl-type{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.lv-tl-date{font-size:.67rem;color:#94a3b8}
.lv-tl-subj{font-size:.81rem;font-weight:600;color:#1e293b;margin-bottom:2px}
.lv-tl-txt{font-size:.77rem;color:#475569;white-space:pre-wrap;line-height:1.4}
.lv-tl-empty{padding:32px 16px;text-align:center;color:#94a3b8;font-size:.8rem}
.lv-tl-empty i{font-size:1.6rem;display:block;margin-bottom:7px;opacity:.2}
/* Log form */
.lv-lf{display:flex;flex-direction:column;gap:10px}
.lv-type-row{display:flex;gap:6px;flex-wrap:wrap}
.lv-tb{padding:6px 11px;border:2px solid #e2e8f0;border-radius:7px;background:#fff;font-size:.75rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;transition:.15s;color:#374151}
.lv-tb.on{border-color:#6366f1;background:#eff6ff;color:#4f46e5}
.lv-flbl{font-size:.75rem;font-weight:600;color:#374151;margin-bottom:3px}
/* Modal */
.lv-modal-wrap{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)}
.lv-modal-wrap.on{display:flex}
.lv-modal{background:#fff;border-radius:14px;width:100%;max-width:560px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:lv-pop .22s cubic-bezier(.16,1,.3,1)}
@keyframes lv-pop{from{transform:scale(.95) translateY(10px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.lv-modal-hd{padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between}
.lv-modal-hd h3{font-size:.92rem;font-weight:700;color:#111827;margin:0;display:flex;align-items:center;gap:7px}
.lv-modal-hd h3 i{color:#6366f1}
.lv-modal-x{width:28px;height:28px;border:none;background:#f1f5f9;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;transition:.15s}
.lv-modal-x:hover{background:#e2e8f0;transform:rotate(90deg)}
.lv-modal-bd{overflow-y:auto;padding:18px 20px;flex:1}
.lv-modal-ft{padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end}
/* Toast */
.lv-toast{position:fixed;bottom:22px;right:22px;padding:11px 16px;border-radius:9px;color:#fff;font-size:.81rem;font-weight:600;z-index:9999;box-shadow:0 6px 20px rgba(0,0,0,.15);display:flex;align-items:center;gap:7px;transition:all .25s;pointer-events:none}
.lv-toast.hide{opacity:0;transform:translateY(6px)}
/* Email composer */
.lv-em-wrap{background:#fff;border:1px solid #e2e8f0;border-radius:11px;overflow:hidden}
.lv-em-head{padding:9px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:.79rem;font-weight:600;display:flex;align-items:center;gap:6px}
.lv-em-row{display:flex;align-items:center;border-bottom:1px solid #f1f5f9;padding:6px 14px;gap:8px}
.lv-em-row label{font-size:.74rem;color:#94a3b8;min-width:38px;font-weight:500}
.lv-em-row input{flex:1;border:none;outline:none;font-size:.81rem;color:#374151;background:none}
.lv-em-body{min-height:120px;padding:11px 14px;font-size:.81rem;color:#374151;outline:none;line-height:1.6}
.lv-em-footer{display:flex;justify-content:flex-end;padding:8px 14px;border-top:1px solid #f1f5f9;background:#f8fafc;gap:8px;align-items:center}
/* Template library */
.lv-tpl-lib{margin-bottom:16px}
.lv-tpl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:8px;flex-wrap:wrap}
.lv-tpl-header h4{font-size:.82rem;font-weight:700;color:#1e293b;margin:0;display:flex;align-items:center;gap:6px}
.lv-tpl-header h4 i{color:#6366f1}
.lv-tpl-filters{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.lv-tpl-search{padding:6px 10px;border:1px solid #e2e8f0;border-radius:7px;font-size:.76rem;outline:none;width:160px;transition:.15s}
.lv-tpl-search:focus{border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.1)}
.lv-tpl-cat{padding:5px 10px;border:1px solid #e2e8f0;border-radius:7px;font-size:.73rem;background:#fff;cursor:pointer;transition:.15s;color:#64748b;font-weight:500}
.lv-tpl-cat:hover{background:#f8fafc;border-color:#c7d2fe}
.lv-tpl-cat.on{background:#eff6ff;border-color:#6366f1;color:#4f46e5;font-weight:600}
.lv-tpl-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;max-height:260px;overflow-y:auto;padding-right:4px}
@media(max-width:600px){.lv-tpl-grid{grid-template-columns:1fr}}
.lv-tpl-card{border:1.5px solid #e2e8f0;border-radius:9px;padding:10px 12px;cursor:pointer;transition:all .18s;background:#fff;position:relative}
.lv-tpl-card:hover{border-color:#818cf8;background:#fafaff;transform:translateY(-1px);box-shadow:0 3px 10px rgba(99,102,241,.08)}
.lv-tpl-card.on{border-color:#6366f1;background:#eff6ff;box-shadow:0 0 0 2px rgba(99,102,241,.15)}
.lv-tpl-card-name{font-size:.78rem;font-weight:700;color:#1e293b;margin-bottom:3px;display:flex;align-items:center;gap:5px}
.lv-tpl-card-subj{font-size:.71rem;color:#6b7280;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lv-tpl-card-meta{display:flex;justify-content:space-between;align-items:center}
.lv-tpl-card-cat{font-size:.63rem;font-weight:600;padding:2px 6px;border-radius:4px;background:#f1f5f9;color:#64748b;text-transform:uppercase;letter-spacing:.03em}
.lv-tpl-card-uses{font-size:.63rem;color:#94a3b8}
.lv-tpl-preview{background:#fff;border:1px solid #e2e8f0;border-radius:9px;padding:14px;margin-top:10px;display:none}
.lv-tpl-preview.on{display:block}
.lv-tpl-preview-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.lv-tpl-preview-title{font-size:.79rem;font-weight:700;color:#1e293b}
.lv-tpl-preview-body{font-size:.77rem;color:#475569;max-height:200px;overflow-y:auto;border:1px solid #f1f5f9;border-radius:6px;padding:10px;background:#fafbfc}
.lv-tpl-empty{text-align:center;padding:24px 12px;color:#94a3b8;font-size:.79rem}
.lv-tpl-empty i{font-size:1.4rem;display:block;margin-bottom:6px;opacity:.3}
.lv-tpl-vars{display:flex;gap:4px;flex-wrap:wrap;margin-top:8px}
.lv-tpl-var{font-size:.65rem;padding:2px 6px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:500;cursor:default}
.lv-em-tpl-btn{padding:5px 10px;border:1px solid #e2e8f0;border-radius:7px;font-size:.73rem;background:#fff;cursor:pointer;transition:.15s;color:#6366f1;font-weight:600;display:flex;align-items:center;gap:4px}
.lv-em-tpl-btn:hover{background:#eff6ff;border-color:#c7d2fe}
</style>

<div class="lv">
<!-- HERO -->
<div class="lv-hero">
    <div>
        <h1><i class="fas fa-address-book"></i> Tous les contacts</h1>
        <p>Leads CRM, captures, estimations, contacts, financement — vue unifiée</p>
    </div>
    <div class="lv-hero-btns">
        <a href="?page=leads&ajax=1&action=export" class="lv-btn lv-btn-ghost"><i class="fas fa-download"></i> Export CSV</a>
        <button class="lv-btn lv-btn-primary" onclick="lvOpenModal()"><i class="fas fa-plus"></i> Nouveau lead</button>
    </div>
</div>

<!-- STATS -->
<div class="lv-stats">
    <div class="lv-stat <?=!$srcFilter?'on':''?>" onclick="lvFilterSrc('')" title="Tous">
        <div class="lv-stat-ico" style="background:#e0e7ff;color:#6366f1"><i class="fas fa-users"></i></div>
        <div><div class="lv-stat-val"><?=$sTotal?></div><div class="lv-stat-lbl">Total</div></div>
    </div>
    <div class="lv-stat <?=$srcFilter==='Manuel'?'on':''?>" onclick="lvFilterSrc('Manuel')">
        <div class="lv-stat-ico" style="background:#f1f5f9;color:#475569"><i class="fas fa-user-edit"></i></div>
        <div><div class="lv-stat-val"><?=$sLeads?></div><div class="lv-stat-lbl">CRM / Manuels</div></div>
    </div>
    <div class="lv-stat <?=$srcFilter==='Capture'?'on':''?>" onclick="lvFilterSrc('Capture')">
        <div class="lv-stat-ico" style="background:#ede9fe;color:#7c3aed"><i class="fas fa-magnet"></i></div>
        <div><div class="lv-stat-val"><?=$sCapture?></div><div class="lv-stat-lbl">Captures</div></div>
    </div>
    <div class="lv-stat <?=$srcFilter==='Estimation'?'on':''?>" onclick="lvFilterSrc('Estimation')">
        <div class="lv-stat-ico" style="background:#fef3c7;color:#b45309"><i class="fas fa-chart-bar"></i></div>
        <div><div class="lv-stat-val"><?=$sEstim?></div><div class="lv-stat-lbl">Estimations</div></div>
    </div>
    <div class="lv-stat">
        <div class="lv-stat-ico" style="background:#d1fae5;color:#059669"><i class="fas fa-calendar-check"></i></div>
        <div><div class="lv-stat-val"><?=$sMonth?></div><div class="lv-stat-lbl">Ce mois</div></div>
    </div>
</div>

<!-- TABLE -->
<div class="lv-card">
    <div class="lv-toolbar">
        <form method="GET" style="display:contents">
            <input type="hidden" name="page" value="leads">
            <?php if($sortBy!=='created_at'):?><input type="hidden" name="sort" value="<?=htmlspecialchars($sortBy)?>"><?php endif;?>
            <?php if($sortOrder!=='DESC'):?><input type="hidden" name="order" value="<?=$sortOrder?>"><?php endif;?>
            <div class="lv-search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Rechercher nom, email, téléphone..." value="<?=htmlspecialchars($search)?>" onchange="this.form.submit()">
            </div>
            <div class="lv-filters">
                <select name="src" class="lv-sel" onchange="this.form.submit()">
                    <option value="">Toutes sources</option>
                    <?php foreach(['Manuel','Site web','GMB','Facebook','Google','Téléphone','Recommandation','Flyer','Boîtage','Salon','Capture','Estimation','Contact','Financement'] as $s):?>
                    <option value="<?=$s?>" <?=$srcFilter===$s?'selected':''?>><?=$s?></option>
                    <?php endforeach;?>
                </select>
                <select name="status" class="lv-sel" onchange="this.form.submit()">
                    <option value="">Tous statuts</option>
                    <?php foreach($statusLabels as $k=>$v):?>
                    <option value="<?=$k?>" <?=$statusFlt===$k?'selected':''?>><?=$v['label']?></option>
                    <?php endforeach;?>
                </select>
                <?php if($search||$srcFilter||$statusFlt):?>
                <a href="?page=leads" class="lv-btn lv-btn-outline lv-btn-sm"><i class="fas fa-times"></i> Reset</a>
                <?php endif;?>
            </div>
        </form>
    </div>

    <?php if(empty($leads)):?>
    <div class="lv-empty">
        <i class="fas fa-user-slash"></i>
        <div style="font-size:.9rem;font-weight:600;color:#374151;margin-bottom:5px">Aucun contact trouvé</div>
        <div>Modifiez vos filtres ou ajoutez un nouveau lead</div>
        <button class="lv-btn lv-btn-primary" style="margin-top:12px" onclick="lvOpenModal()"><i class="fas fa-plus"></i> Ajouter</button>
    </div>
    <?php else:?>
    <table class="lv-table">
        <thead>
        <tr>
            <th><a href="<?=lvSort('_fn',$sortBy,$sortOrder,$pqs)?>">Nom <?=$sortBy==='_fn'?($sortOrder==='ASC'?'↑':'↓'):''?></a></th>
            <th>Contact</th>
            <th>Source</th>
            <th>Statut</th>
            <th>Notes</th>
            <th><a href="<?=lvSort('created_at',$sortBy,$sortOrder,$pqs)?>">Date <?=$sortBy==='created_at'?($sortOrder==='ASC'?'↑':'↓'):''?></a></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($leads as $l):
            $fn   = trim($l['_fn'].' '.$l['_ln']) ?: '—';
            $ini  = strtoupper(mb_substr($l['_fn'],0,1).mb_substr($l['_ln'],0,1)) ?: '?';
            $src  = $srcStyles[$l['_src_label']] ?? ['bg'=>'#f1f5f9','c'=>'#475569','icon'=>'tag'];
            $st   = $statusLabels[$l['_status']??''] ?? null;
            $avBg = match($l['_src_label']??''){'Estimation'=>'#b45309','Capture'=>'#7c3aed','Financement'=>'#be185d','Contact'=>'#1d4ed8','GMB'=>'#065f46','Facebook'=>'#2563eb',default=>'#6366f1'};
            $note = trim($l['notes']??$l['next_action']??'');
        ?>
        <tr onclick="lvSheet(<?=$l['id']?>,'<?=$l['_tbl']?>')">
            <td>
                <div style="display:flex;align-items:center;gap:9px">
                    <div class="lv-av" style="background:<?=$avBg?>"><?=htmlspecialchars($ini)?></div>
                    <div>
                        <div style="font-weight:600;color:#111827;font-size:.83rem"><?=htmlspecialchars($fn)?></div>
                        <?php if($l['_city']??''):?><div style="font-size:.71rem;color:#94a3b8"><?=htmlspecialchars($l['_city'])?></div><?php endif;?>
                    </div>
                </div>
            </td>
            <td>
                <?php if($l['_email']??''):?><div><a href="mailto:<?=htmlspecialchars($l['_email'])?>" onclick="event.stopPropagation()" style="color:#4f46e5;font-size:.79rem;text-decoration:none"><?=htmlspecialchars($l['_email'])?></a></div><?php endif;?>
                <?php if($l['_phone']??''):?><div style="font-size:.75rem;color:#64748b;margin-top:1px"><i class="fas fa-phone" style="font-size:.58rem;margin-right:3px"></i><?=htmlspecialchars($l['_phone'])?></div><?php endif;?>
            </td>
            <td><span class="lv-src" style="background:<?=$src['bg']?>;color:<?=$src['c']?>"><i class="fas fa-<?=$src['icon']?>"></i> <?=htmlspecialchars($l['_src_label'])?></span></td>
            <td>
                <?php if($st):?><span class="lv-badge" style="background:<?=$st['bg']?>;color:<?=$st['c']?>"><?=$st['label']?></span>
                <?php elseif($l['_status']??''):?><span class="lv-badge" style="background:#f1f5f9;color:#475569"><?=htmlspecialchars($l['_status'])?></span>
                <?php else:?><span style="color:#cbd5e1;font-size:.72rem">—</span><?php endif;?>
            </td>
            <td>
                <?php if($note):?><div style="font-size:.75rem;color:#64748b;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=htmlspecialchars($note)?>"><?=htmlspecialchars(mb_substr($note,0,55)).(mb_strlen($note)>55?'…':'')?></div>
                <?php else:?><span style="color:#cbd5e1;font-size:.72rem">—</span><?php endif;?>
            </td>
            <td>
                <div style="font-size:.76rem;color:#374151"><?=date('d/m/Y',strtotime($l['created_at']))?></div>
                <div style="font-size:.69rem;color:#94a3b8"><?=date('H:i',strtotime($l['created_at']))?></div>
            </td>
            <td onclick="event.stopPropagation()">
                <div class="lv-acts">
                    <button class="lv-act lv-act-v" onclick="lvSheet(<?=$l['id']?>,'<?=$l['_tbl']?>')" title="Voir"><i class="fas fa-eye"></i></button>
                    <?php if($l['_phone']??''):?><a class="lv-act lv-act-c" href="tel:<?=htmlspecialchars($l['_phone'])?>" title="Appeler"><i class="fas fa-phone"></i></a><?php endif;?>
                    <button class="lv-act lv-act-d" onclick="lvDelete(<?=$l['id']?>,'<?=$l['_tbl']?>')" title="Supprimer"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <div class="lv-pag">
        <span><?=min($offset+1,$totalLeads)?>–<?=min($offset+$perPage,$totalLeads)?> sur <strong><?=$totalLeads?></strong> contacts</span>
        <div class="lv-pag-nn">
            <?php if($page>1):?><a href="?page=leads&p=1&<?=$pqs?>"><i class="fas fa-angle-double-left"></i></a><a href="?page=leads&p=<?=$page-1?>&<?=$pqs?>"><i class="fas fa-angle-left"></i></a><?php endif;?>
            <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++):?>
                <?php if($i===$page):?><span class="on"><?=$i?></span><?php else:?><a href="?page=leads&p=<?=$i?>&<?=$pqs?>"><?=$i?></a><?php endif;?>
            <?php endfor;?>
            <?php if($page<$totalPages):?><a href="?page=leads&p=<?=$page+1?>&<?=$pqs?>"><i class="fas fa-angle-right"></i></a><a href="?page=leads&p=<?=$totalPages?>&<?=$pqs?>"><i class="fas fa-angle-double-right"></i></a><?php endif;?>
        </div>
    </div>
    <?php endif;?>
</div>
</div><!-- /.lv -->

<!-- ══ SLIDE-OVER ════════════════════════════════════════════════════════════ -->
<div class="lv-ov" id="lvOv" onclick="lvCloseSheet()"></div>
<div class="lv-sh" id="lvSh">
    <!-- Header -->
    <div class="lv-sh-hd">
        <div class="lv-sh-top">
            <div style="display:flex;align-items:center;gap:12px">
                <div class="lv-sh-av" id="shAv">?</div>
                <div><p class="lv-sh-name" id="shName">—</p><p class="lv-sh-sub" id="shSub"></p></div>
            </div>
            <button class="lv-sh-x" onclick="lvCloseSheet()"><i class="fas fa-times"></i></button>
        </div>
        <div class="lv-sh-tags" id="shTags"></div>
    </div>
    <!-- Quick actions -->
    <div class="lv-qrow">
        <button class="lv-qa q-blue"   onclick="lvShTab('log');lvSetLT('appel')"><i class="fas fa-phone"></i> Appel</button>
        <button class="lv-qa q-green"  onclick="lvShTab('email')"><i class="fas fa-envelope"></i> Email</button>
        <button class="lv-qa q-amber"  onclick="lvShTab('log');lvSetLT('rdv')"><i class="fas fa-calendar"></i> RDV</button>
        <button class="lv-qa q-purple" onclick="lvShTab('log');lvSetLT('note')"><i class="fas fa-sticky-note"></i> Note</button>
    </div>
    <!-- Tabs -->
    <div class="lv-tabs">
        <button class="lv-tab on" data-tab="info"  onclick="lvShTab('info')"><i class="fas fa-user"></i> Infos</button>
        <button class="lv-tab"    data-tab="edit"  onclick="lvShTab('edit')"><i class="fas fa-edit"></i> Modifier</button>
        <button class="lv-tab"    data-tab="hist"  onclick="lvShTab('hist')"><i class="fas fa-history"></i> Historique <span id="shHistN" style="background:#6366f1;color:#fff;border-radius:10px;padding:1px 6px;font-size:.63rem;margin-left:2px">0</span></button>
        <button class="lv-tab"    data-tab="email" onclick="lvShTab('email')"><i class="fas fa-envelope"></i> Email</button>
        <button class="lv-tab"    data-tab="log"   onclick="lvShTab('log')"><i class="fas fa-pencil-alt"></i> Ajouter</button>
    </div>
    <!-- Body -->
    <div class="lv-sh-body">

        <!-- ── Onglet INFOS ── -->
        <div id="tab-info">
            <div class="lv-ig" id="shGrid"></div>
            <div id="shNotes"></div>
            <div id="shNextAction"></div>
            <div style="margin-top:14px;display:flex;gap:8px">
                <button class="lv-btn lv-btn-primary" style="flex:1" onclick="lvShTab('edit')"><i class="fas fa-edit"></i> Modifier</button>
                <button class="lv-btn lv-btn-danger" onclick="lvDelete(shId,shTbl)"><i class="fas fa-trash"></i></button>
            </div>
        </div>

        <!-- ── Onglet MODIFIER (formulaire inline) ── -->
        <div id="tab-edit" style="display:none">
            <div class="lv-ef" id="shEditForm">
                <!-- Rempli dynamiquement par JS -->
            </div>
            <div style="display:flex;gap:8px;margin-top:16px">
                <button class="lv-btn lv-btn-primary" style="flex:1" onclick="lvSaveEdit()"><i class="fas fa-save"></i> Enregistrer les modifications</button>
                <button class="lv-btn lv-btn-outline" onclick="lvShTab('info')">Annuler</button>
            </div>
        </div>

        <!-- ── Onglet HISTORIQUE ── -->
        <div id="tab-hist" style="display:none">
            <div class="lv-tl" id="shTl"><div class="lv-tl-empty"><i class="fas fa-history"></i>Aucune interaction</div></div>
        </div>

        <!-- ── Onglet EMAIL ── -->
        <div id="tab-email" style="display:none">
            <!-- Template library -->
            <div class="lv-tpl-lib" id="tplLib">
                <div class="lv-tpl-header">
                    <h4><i class="fas fa-layer-group"></i> Bibliothèque de templates</h4>
                    <div class="lv-tpl-filters">
                        <input type="text" class="lv-tpl-search" id="tplSearch" placeholder="Rechercher..." oninput="lvSearchTpl()">
                        <div id="tplCats" style="display:flex;gap:4px;flex-wrap:wrap">
                            <button class="lv-tpl-cat on" data-cat="" onclick="lvFilterTplCat('',this)">Tous</button>
                        </div>
                    </div>
                </div>
                <div class="lv-tpl-grid" id="tplGrid">
                    <div class="lv-tpl-empty"><i class="fas fa-spinner fa-spin"></i>Chargement des templates...</div>
                </div>
                <!-- Preview -->
                <div class="lv-tpl-preview" id="tplPreview">
                    <div class="lv-tpl-preview-head">
                        <span class="lv-tpl-preview-title" id="tplPreviewTitle">—</span>
                        <div style="display:flex;gap:5px">
                            <button class="lv-btn lv-btn-primary lv-btn-sm" onclick="lvUseTpl()"><i class="fas fa-check"></i> Utiliser</button>
                            <button class="lv-btn lv-btn-outline lv-btn-sm" onclick="lvCloseTplPreview()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div class="lv-tpl-preview-body" id="tplPreviewBody"></div>
                    <div class="lv-tpl-vars" id="tplPreviewVars"></div>
                </div>
            </div>
            <!-- Email composer -->
            <div class="lv-em-wrap">
                <div class="lv-em-head"><i class="fas fa-envelope" style="color:#6366f1"></i> Nouveau message <span id="emTplName" style="margin-left:auto;font-size:.68rem;color:#6366f1;font-weight:500"></span></div>
                <div class="lv-em-row"><label>À :</label><input type="email" id="emTo" style="font-weight:500"></div>
                <div class="lv-em-row"><label>Objet :</label><input type="text" id="emSubj" placeholder="Objet..."></div>
                <div id="emBody" class="lv-em-body" contenteditable="true" data-placeholder="Votre message..." style="min-height:160px"></div>
                <div class="lv-em-footer">
                    <button class="lv-em-tpl-btn" onclick="lvToggleTplLib()" title="Bibliothèque de templates"><i class="fas fa-layer-group"></i> Templates</button>
                    <button id="emBtn" onclick="lvSendEmail()" class="lv-btn lv-btn-primary lv-btn-sm"><i class="fas fa-paper-plane"></i> Envoyer</button>
                </div>
            </div>
            <div style="margin-top:14px">
                <div style="font-size:.77rem;font-weight:700;color:#374151;margin-bottom:9px"><i class="fas fa-history" style="color:#6366f1;margin-right:4px"></i>Emails envoyés</div>
                <div class="lv-tl" id="shEmailTl"></div>
            </div>
        </div>

        <!-- ── Onglet LOG ── -->
        <div id="tab-log" style="display:none">
            <div class="lv-lf">
                <div>
                    <div class="lv-flbl">Type d'interaction</div>
                    <div class="lv-type-row">
                        <button class="lv-tb on" data-t="appel"  onclick="lvSetLT('appel',this)"><i class="fas fa-phone"       style="color:#2563eb"></i> Appel</button>
                        <button class="lv-tb"    data-t="email"  onclick="lvSetLT('email',this)"><i class="fas fa-envelope"    style="color:#6366f1"></i> Email</button>
                        <button class="lv-tb"    data-t="rdv"    onclick="lvSetLT('rdv',this)"><i class="fas fa-calendar"      style="color:#10b981"></i> RDV</button>
                        <button class="lv-tb"    data-t="sms"    onclick="lvSetLT('sms',this)"><i class="fas fa-sms"           style="color:#f59e0b"></i> SMS</button>
                        <button class="lv-tb"    data-t="note"   onclick="lvSetLT('note',this)"><i class="fas fa-sticky-note"  style="color:#8b5cf6"></i> Note</button>
                        <button class="lv-tb"    data-t="visite" onclick="lvSetLT('visite',this)"><i class="fas fa-home"       style="color:#ef4444"></i> Visite</button>
                    </div>
                </div>
                <div><div class="lv-flbl">Sujet</div><input type="text" class="lv-in" id="logSubj" placeholder="Sujet de l'échange..."></div>
                <div><div class="lv-flbl">Notes</div><textarea class="lv-in lv-ta" id="logCont" placeholder="Ce qui a été dit, décidé..."></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div><div class="lv-flbl">Date</div><input type="datetime-local" class="lv-in" id="logDate"></div>
                    <div><div class="lv-flbl">Durée (min)</div><input type="number" class="lv-in" id="logDur" placeholder="15"></div>
                </div>
                <div>
                    <div class="lv-flbl">Résultat</div>
                    <div class="lv-type-row">
                        <button class="lv-tb on" data-o="positif" onclick="lvSetOT('positif',this)" style="border-color:#10b981;background:#d1fae5;color:#065f46">✓ Positif</button>
                        <button class="lv-tb" data-o="neutre"  onclick="lvSetOT('neutre',this)">○ Neutre</button>
                        <button class="lv-tb" data-o="negatif" onclick="lvSetOT('negatif',this)">✗ Négatif</button>
                    </div>
                </div>
                <button class="lv-btn lv-btn-primary" style="justify-content:center" onclick="lvSaveLog()"><i class="fas fa-save"></i> Enregistrer l'interaction</button>
            </div>
        </div>

    </div><!-- /.lv-sh-body -->
</div><!-- /.lv-sh -->

<!-- ══ MODAL NOUVEAU LEAD ════════════════════════════════════════════════════ -->
<div class="lv-modal-wrap" id="lvModal">
    <div class="lv-modal">
        <div class="lv-modal-hd">
            <h3><i class="fas fa-user-plus"></i> Nouveau lead</h3>
            <button class="lv-modal-x" onclick="lvCloseModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="lv-modal-bd">
            <div class="lv-ef">
                <div class="lv-ef-row">
                    <div class="lv-ef-grp"><label>Prénom *</label><input type="text" class="lv-in" id="nFn" placeholder="Jean"></div>
                    <div class="lv-ef-grp"><label>Nom *</label><input type="text" class="lv-in" id="nLn" placeholder="Dupont"></div>
                </div>
                <div class="lv-ef-row">
                    <div class="lv-ef-grp"><label>Email</label><input type="email" class="lv-in" id="nEmail" placeholder="jean@email.com"></div>
                    <div class="lv-ef-grp"><label>Téléphone</label><input type="tel" class="lv-in" id="nPhone" placeholder="06 00 00 00 00"></div>
                </div>
                <div class="lv-ef-row">
                    <div class="lv-ef-grp"><label>Ville</label><input type="text" class="lv-in" id="nCity" placeholder="Bordeaux"></div>
                    <div class="lv-ef-grp"><label>Source</label>
                        <select class="lv-in" id="nSrc">
                            <option value="manuel">Manuel</option><option value="site_web">Site web</option><option value="gmb">GMB</option>
                            <option value="pub_facebook">Facebook</option><option value="pub_google">Google</option>
                            <option value="telephone">Téléphone</option><option value="recommandation">Recommandation</option><option value="autre">Autre</option>
                        </select>
                    </div>
                </div>
                <div class="lv-ef-row">
                    <div class="lv-ef-grp"><label>Statut</label>
                        <select class="lv-in" id="nStatus">
                            <?php foreach($statusLabels as $k=>$v):?><option value="<?=$k?>"><?=$v['label']?></option><?php endforeach;?>
                        </select>
                    </div>
                    <div class="lv-ef-grp"><label>Température</label>
                        <select class="lv-in" id="nTemp">
                            <?php foreach($tempLabels as $k=>$v):?><option value="<?=$k?>"><?=$v['label']?></option><?php endforeach;?>
                        </select>
                    </div>
                </div>
                <div class="lv-ef-row">
                    <div class="lv-ef-grp"><label>Prochaine action</label><input type="text" class="lv-in" id="nNext" placeholder="Rappeler pour RDV"></div>
                    <div class="lv-ef-grp"><label>Date action</label><input type="date" class="lv-in" id="nNextDate"></div>
                </div>
                <div class="lv-ef-grp"><label>Notes</label><textarea class="lv-in lv-ta" id="nNotes" rows="3" placeholder="Infos sur le projet..."></textarea></div>
            </div>
        </div>
        <div class="lv-modal-ft">
            <button class="lv-btn lv-btn-outline" onclick="lvCloseModal()">Annuler</button>
            <button class="lv-btn lv-btn-primary" id="nSaveBtn" onclick="lvCreateLead()"><i class="fas fa-save"></i> Créer le lead</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="lv-toast hide" id="lvToast"></div>

<script>
// ─────────────────────────────────────────────────────────────────────────────
// Config
// ─────────────────────────────────────────────────────────────────────────────
// URL AJAX : on utilise window.location pour construire l'URL correcte
const LV_BASE = window.location.pathname + '?page=leads&ajax=1';

let shId = null, shTbl = 'leads', logType = 'appel', logOutcome = 'positif';
let shData = null; // cache des données chargées

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────
async function lvApi(params) {
    const fd = new FormData();
    for (const [k,v] of Object.entries(params))
        if (v !== null && v !== undefined && String(v).length > 0) fd.append(k, String(v));
    const resp = await fetch(LV_BASE, { method:'POST', body:fd });
    if (!resp.ok) throw new Error('HTTP '+resp.status);
    const text = await resp.text();
    try { return JSON.parse(text); }
    catch(e) { console.error('Response non-JSON:', text); throw new Error('Réponse invalide du serveur'); }
}

function lvToast(msg, type='success') {
    const t = document.getElementById('lvToast');
    const cfg = { success:['#10b981','check-circle'], error:['#ef4444','exclamation-circle'], warning:['#f59e0b','exclamation-triangle'], info:['#6366f1','info-circle'] };
    const [bg, ic] = cfg[type] || cfg.info;
    t.style.background = bg;
    t.innerHTML = `<i class="fas fa-${ic}"></i> ${msg}`;
    t.classList.remove('hide');
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.add('hide'), 3000);
}

function lvFilterSrc(src) {
    const url = new URL(window.location.href);
    src ? url.searchParams.set('src', src) : url.searchParams.delete('src');
    url.searchParams.delete('p');
    window.location.href = url.toString();
}

// ─────────────────────────────────────────────────────────────────────────────
// Slide-over
// ─────────────────────────────────────────────────────────────────────────────
async function lvSheet(id, tbl) {
    shId = id; shTbl = tbl || 'leads';
    document.getElementById('lvOv').classList.add('on');
    document.getElementById('lvSh').classList.add('on');
    lvShTab('info');
    await lvLoadSheet();
    if (tbl === 'leads') await lvLoadHistory();
}

function lvCloseSheet() {
    document.getElementById('lvOv').classList.remove('on');
    document.getElementById('lvSh').classList.remove('on');
    shId = null; shData = null;
}

async function lvLoadSheet() {
    try {
        const res = await lvApi({ action:'get_lead', id:shId, tbl:shTbl });
        if (!res.success || !res.lead) { lvToast('Impossible de charger la fiche','error'); return; }
        shData = res.lead;
        lvRenderSheetHeader(shData);
        lvRenderSheetInfo(shData);
        lvRenderEditForm(shData);
    } catch(e) { lvToast('Erreur: '+e.message,'error'); }
}

function lvRenderSheetHeader(l) {
    let fn='',ln='',email='',phone='',city='',status='',score=0,temp='';
    const srcLabel = { leads:'Manuel', capture_leads:'Capture', demandes_estimation:'Estimation', contacts:'Contact', financement_leads:'Financement' }[shTbl] || shTbl;
    if (shTbl==='leads') {
        fn=l.firstname||(l.full_name?l.full_name.split(' ')[0]:''); ln=l.lastname||(l.full_name?l.full_name.split(' ').slice(1).join(' '):'');
        email=l.email||''; phone=l.phone||''; city=l.city||''; status=l.status||''; score=l.score||0; temp=l.temperature||'';
    } else if (shTbl==='capture_leads') {
        fn=l.prenom||''; ln=l.nom||''; email=l.email||''; phone=l.tel||''; status=l.injected_crm?'contacté':'nouveau';
    } else if (shTbl==='demandes_estimation') {
        ln=(l.type_bien||'Estimation')+' '+(l.ville||''); email=l.email||''; phone=l.telephone||''; city=l.ville||''; status=l.statut||'nouveau';
    } else if (shTbl==='contacts') {
        fn=l.firstname||l.prenom||''; ln=l.lastname||l.nom||''; email=l.email||''; phone=l.phone||l.telephone||''; city=l.city||''; status=l.status||'actif'; score=l.rating||0;
    } else if (shTbl==='financement_leads') {
        fn=l.prenom||''; ln=l.nom||''; email=l.email||''; phone=l.telephone||''; status=l.statut||'nouveau';
    }
    const ini = ((fn||'?').charAt(0)+(ln||'?').charAt(0)).toUpperCase();
    const srcSt = <?=json_encode($srcStyles)?>;
    const ss = srcSt[srcLabel]||{bg:'#f1f5f9',c:'#475569'};
    const stMap = <?=json_encode($statusLabels)?>;
    const stI = stMap[status]||null;
    const tpMap = <?=json_encode($tempLabels)?>;
    const tpI = tpMap[temp]||null;

    document.getElementById('shAv').textContent=ini;
    document.getElementById('shAv').style.background=ss.c;
    document.getElementById('shName').textContent=(fn+' '+ln).trim()||'—';
    document.getElementById('shSub').textContent=[city,srcLabel].filter(Boolean).join(' · ');
    document.getElementById('emTo').value = email;

    let tags = `<span class="lv-sh-tag" style="background:${ss.bg};color:${ss.c}">${srcLabel}</span>`;
    if (stI) tags += `<span class="lv-sh-tag" style="background:${stI.bg};color:${stI.c}">${stI.label}</span>`;
    if (tpI) tags += `<span class="lv-sh-tag" style="background:${tpI.bg};color:${tpI.c}"><i class="fas fa-${tpI.icon}" style="font-size:.58rem"></i> ${tpI.label}</span>`;
    if (score>0) tags += `<span class="lv-sh-tag" style="background:#e0e7ff;color:#4f46e5">Score ${score}</span>`;
    document.getElementById('shTags').innerHTML = tags;
}

function lvRenderSheetInfo(l) {
    let email='',phone='',city='',notes='',nextAction='',nextDate='';
    if (shTbl==='leads') {
        email=l.email||''; phone=l.phone||''; city=l.city||''; notes=l.notes||''; nextAction=l.next_action||''; nextDate=l.next_action_date||'';
    } else if (shTbl==='capture_leads') {
        email=l.email||''; phone=l.tel||''; notes=l.message||'';
    } else if (shTbl==='demandes_estimation') {
        email=l.email||''; phone=l.telephone||''; city=l.ville||'';
        const parts=[l.type_bien,l.surface?l.surface+'m²':'',l.estimation_moyenne?'~'+Number(l.estimation_moyenne).toLocaleString('fr-FR')+'€':''].filter(Boolean);
        notes=parts.join(' — ');
    } else if (shTbl==='contacts') {
        email=l.email||''; phone=l.phone||l.telephone||''; city=l.city||''; notes=l.notes||'';
    } else if (shTbl==='financement_leads') {
        email=l.email||''; phone=l.telephone||'';
        notes=[(l.type_projet||'Projet'),l.montant_projet?Number(l.montant_projet).toLocaleString('fr-FR')+'€':'',l.notes||''].filter(Boolean).join(' — ');
    }

    const c = (lbl,val) => `<div class="lv-ic"><div class="lv-ic-l">${lbl}</div><div class="lv-ic-v">${val||'—'}</div></div>`;
    document.getElementById('shGrid').innerHTML = [
        c('<i class="fas fa-envelope"></i> Email', email?`<a href="mailto:${email}">${email}</a>`:''),
        c('<i class="fas fa-phone"></i> Téléphone', phone?`<a href="tel:${phone}">${phone}</a>`:''),
        city ? c('<i class="fas fa-map-marker-alt"></i> Ville', city) : '',
        c('<i class="fas fa-calendar-plus"></i> Créé le', new Date(l.created_at).toLocaleDateString('fr-FR',{day:'2-digit',month:'long',year:'numeric'})),
    ].join('');

    document.getElementById('shNotes').innerHTML = notes
        ? `<div class="lv-note-bloc"><div class="lv-note-title"><i class="fas fa-sticky-note"></i> Notes / Projet</div><div class="lv-note-txt">${lvEsc(notes)}</div></div>` : '';
    document.getElementById('shNextAction').innerHTML = nextAction
        ? `<div class="lv-na-bloc"><div class="lv-na-title"><i class="fas fa-tasks"></i> Prochaine action</div><div class="lv-na-txt">${lvEsc(nextAction)}${nextDate?' — '+new Date(nextDate+'T00:00').toLocaleDateString('fr-FR'):''}</div></div>` : '';
}

function lvRenderEditForm(l) {
    const form = document.getElementById('shEditForm');
    let html = '';

    if (shTbl === 'leads') {
        const fn = l.firstname || (l.full_name?l.full_name.split(' ')[0]:'');
        const ln = l.lastname  || (l.full_name?l.full_name.split(' ').slice(1).join(' '):'');
        const stOpts = <?=json_encode(array_map(fn($v)=>$v['label'], $statusLabels))?>;
        const stKeys = <?=json_encode(array_keys($statusLabels))?>;
        const tpOpts = <?=json_encode(array_map(fn($v)=>$v['label'], $tempLabels))?>;
        const tpKeys = <?=json_encode(array_keys($tempLabels))?>;
        const srcOpts = { manuel:'Manuel', site_web:'Site web', gmb:'GMB', pub_facebook:'Facebook', pub_google:'Google', telephone:'Téléphone', recommandation:'Recommandation', autre:'Autre' };

        html += `<div class="lv-ef-sec"><i class="fas fa-user"></i> Identité</div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Prénom *</label><input class="lv-in" id="ef_fn" value="${lvEsc(fn)}"></div><div class="lv-ef-grp"><label>Nom *</label><input class="lv-in" id="ef_ln" value="${lvEsc(ln)}"></div></div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Email</label><input type="email" class="lv-in" id="ef_email" value="${lvEsc(l.email||'')}"></div><div class="lv-ef-grp"><label>Téléphone</label><input type="tel" class="lv-in" id="ef_phone" value="${lvEsc(l.phone||'')}"></div></div>`;
        html += `<div class="lv-ef-grp"><label>Ville</label><input class="lv-in" id="ef_city" value="${lvEsc(l.city||'')}"></div>`;
        html += `<div class="lv-ef-sec"><i class="fas fa-tasks"></i> Suivi</div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Statut</label><select class="lv-in" id="ef_status">${stKeys.map((k,i)=>`<option value="${k}" ${l.status===k?'selected':''}>${stOpts[i]}</option>`).join('')}</select></div><div class="lv-ef-grp"><label>Température</label><select class="lv-in" id="ef_temp">${tpKeys.map((k,i)=>`<option value="${k}" ${l.temperature===k?'selected':''}>${tpOpts[i]}</option>`).join('')}</select></div></div>`;
        html += `<div class="lv-ef-grp"><label>Source</label><select class="lv-in" id="ef_source">${Object.entries(srcOpts).map(([k,v])=>`<option value="${k}" ${l.source===k?'selected':''}>${v}</option>`).join('')}</select></div>`;
        html += `<div class="lv-ef-sec"><i class="fas fa-clock"></i> Prochaine action</div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Action</label><input class="lv-in" id="ef_next" value="${lvEsc(l.next_action||'')}" placeholder="Ex: Rappeler"></div><div class="lv-ef-grp"><label>Date</label><input type="date" class="lv-in" id="ef_next_date" value="${lvEsc(l.next_action_date||'')}"></div></div>`;
        html += `<div class="lv-ef-grp"><label>Notes</label><textarea class="lv-in lv-ta" id="ef_notes" rows="3">${lvEsc(l.notes||'')}</textarea></div>`;
    } else if (shTbl === 'capture_leads') {
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Prénom</label><input class="lv-in" id="ef_fn" value="${lvEsc(l.prenom||'')}"></div><div class="lv-ef-grp"><label>Nom</label><input class="lv-in" id="ef_ln" value="${lvEsc(l.nom||'')}"></div></div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Email</label><input type="email" class="lv-in" id="ef_email" value="${lvEsc(l.email||'')}"></div><div class="lv-ef-grp"><label>Téléphone</label><input type="tel" class="lv-in" id="ef_phone" value="${lvEsc(l.tel||'')}"></div></div>`;
    } else if (shTbl === 'demandes_estimation') {
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Email</label><input type="email" class="lv-in" id="ef_email" value="${lvEsc(l.email||'')}"></div><div class="lv-ef-grp"><label>Téléphone</label><input type="tel" class="lv-in" id="ef_phone" value="${lvEsc(l.telephone||'')}"></div></div>`;
        html += `<div class="lv-ef-grp"><label>Statut</label><select class="lv-in" id="ef_status"><option value="nouveau" ${l.statut==='nouveau'?'selected':''}>Nouveau</option><option value="contacté" ${l.statut==='contacté'?'selected':''}>Contacté</option><option value="traité" ${l.statut==='traité'?'selected':''}>Traité</option></select></div>`;
    } else if (shTbl === 'contacts') {
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Prénom</label><input class="lv-in" id="ef_fn" value="${lvEsc(l.firstname||l.prenom||'')}"></div><div class="lv-ef-grp"><label>Nom</label><input class="lv-in" id="ef_ln" value="${lvEsc(l.lastname||l.nom||'')}"></div></div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Email</label><input type="email" class="lv-in" id="ef_email" value="${lvEsc(l.email||'')}"></div><div class="lv-ef-grp"><label>Téléphone</label><input type="tel" class="lv-in" id="ef_phone" value="${lvEsc(l.phone||l.telephone||'')}"></div></div>`;
        html += `<div class="lv-ef-grp"><label>Ville</label><input class="lv-in" id="ef_city" value="${lvEsc(l.city||'')}"></div>`;
        html += `<div class="lv-ef-grp"><label>Notes</label><textarea class="lv-in lv-ta" id="ef_notes" rows="3">${lvEsc(l.notes||'')}</textarea></div>`;
    } else if (shTbl === 'financement_leads') {
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Prénom</label><input class="lv-in" id="ef_fn" value="${lvEsc(l.prenom||'')}"></div><div class="lv-ef-grp"><label>Nom</label><input class="lv-in" id="ef_ln" value="${lvEsc(l.nom||'')}"></div></div>`;
        html += `<div class="lv-ef-row"><div class="lv-ef-grp"><label>Email</label><input type="email" class="lv-in" id="ef_email" value="${lvEsc(l.email||'')}"></div><div class="lv-ef-grp"><label>Téléphone</label><input type="tel" class="lv-in" id="ef_phone" value="${lvEsc(l.telephone||'')}"></div></div>`;
        html += `<div class="lv-ef-grp"><label>Notes</label><textarea class="lv-in lv-ta" id="ef_notes" rows="3">${lvEsc(l.notes||'')}</textarea></div>`;
    }

    form.innerHTML = html;
}

// ─────────────────────────────────────────────────────────────────────────────
// Sauvegarder les modifications (depuis slide-over)
// ─────────────────────────────────────────────────────────────────────────────
async function lvSaveEdit() {
    if (!shId) return;
    const g = id => document.getElementById(id)?.value?.trim() ?? '';
    const payload = {
        action:     'update_lead',
        id:         shId,
        tbl:        shTbl,
        firstname:  g('ef_fn'),
        lastname:   g('ef_ln'),
        email:      g('ef_email'),
        phone:      g('ef_phone'),
        city:       g('ef_city'),
        source:     g('ef_source'),
        status:     g('ef_status'),
        temperature:g('ef_temp'),
        notes:      document.getElementById('ef_notes')?.value ?? '',
        next_action:      g('ef_next'),
        next_action_date: g('ef_next_date'),
    };
    try {
        const res = await lvApi(payload);
        if (res.success) {
            lvToast('Modifications enregistrées ✓');
            await lvLoadSheet(); // recharger la fiche
            lvShTab('info');    // retour sur l'onglet info
        } else {
            lvToast(res.error || 'Erreur lors de la sauvegarde', 'error');
        }
    } catch(e) {
        lvToast('Erreur: '+e.message, 'error');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Créer un nouveau lead (modal)
// ─────────────────────────────────────────────────────────────────────────────
function lvOpenModal() {
    ['nFn','nLn','nEmail','nPhone','nCity','nNext','nNextDate','nNotes'].forEach(id=>{
        const el=document.getElementById(id); if(el) el.value='';
    });
    document.getElementById('nStatus').value='new';
    document.getElementById('nTemp').value='warm';
    document.getElementById('nSrc').value='manuel';
    document.getElementById('lvModal').classList.add('on');
    document.getElementById('nFn').focus();
}

function lvCloseModal() { document.getElementById('lvModal').classList.remove('on'); }

async function lvCreateLead() {
    const fn = document.getElementById('nFn').value.trim();
    const ln = document.getElementById('nLn').value.trim();
    if (!fn && !ln) { lvToast('Prénom ou nom requis','warning'); document.getElementById('nFn').focus(); return; }
    const btn = document.getElementById('nSaveBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
    try {
        const res = await lvApi({
            action:      'add_lead',
            firstname:   fn,
            lastname:    ln,
            email:       document.getElementById('nEmail').value,
            phone:       document.getElementById('nPhone').value,
            city:        document.getElementById('nCity').value,
            source:      document.getElementById('nSrc').value,
            status:      document.getElementById('nStatus').value,
            temperature: document.getElementById('nTemp').value,
            next_action:      document.getElementById('nNext').value,
            next_action_date: document.getElementById('nNextDate').value,
            notes:       document.getElementById('nNotes').value,
        });
        if (res.success) {
            lvToast('Lead créé avec succès ✓');
            lvCloseModal();
            setTimeout(() => location.reload(), 600);
        } else {
            lvToast(res.error || 'Erreur lors de la création', 'error');
        }
    } catch(e) {
        lvToast('Erreur: '+e.message, 'error');
    }
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Créer le lead';
}

// ─────────────────────────────────────────────────────────────────────────────
// Supprimer
// ─────────────────────────────────────────────────────────────────────────────
async function lvDelete(id, tbl) {
    if (!confirm('Supprimer ce contact définitivement ?')) return;
    try {
        const res = await lvApi({ action:'delete_lead', id, tbl:tbl||shTbl||'leads' });
        if (res.success) { lvToast('Contact supprimé'); lvCloseSheet(); setTimeout(()=>location.reload(), 600); }
        else lvToast(res.error||'Erreur','error');
    } catch(e) { lvToast('Erreur: '+e.message,'error'); }
}

// ─────────────────────────────────────────────────────────────────────────────
// Historique interactions
// ─────────────────────────────────────────────────────────────────────────────
async function lvLoadHistory() {
    try {
        const res = await lvApi({ action:'get_interactions', lead_id:shId });
        const items = res.interactions || [];
        document.getElementById('shHistN').textContent = items.length;
        const tConf = { appel:{icon:'phone',c:'#2563eb'}, email:{icon:'envelope',c:'#6366f1'}, rdv:{icon:'calendar',c:'#10b981'}, sms:{icon:'sms',c:'#f59e0b'}, note:{icon:'sticky-note',c:'#8b5cf6'}, visite:{icon:'home',c:'#ef4444'} };
        const mkTl = list => !list.length
            ? `<div class="lv-tl-empty"><i class="fas fa-history"></i>Aucune interaction</div>`
            : list.map(i => {
                const tc = tConf[i.type]||tConf.note;
                const d  = new Date(i.interaction_date||i.created_at);
                return `<div class="lv-tl-item"><div class="lv-tl-dot" style="background:${tc.c}"><i class="fas fa-${tc.icon}"></i></div><div class="lv-tl-card"><div class="lv-tl-head"><span class="lv-tl-type" style="color:${tc.c}">${i.type}</span><span class="lv-tl-date">${d.toLocaleDateString('fr-FR')} ${d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'})}</span></div>${i.subject?`<div class="lv-tl-subj">${lvEsc(i.subject)}</div>`:''} ${i.content?`<div class="lv-tl-txt">${lvEsc(i.content)}</div>`:''}</div></div>`;
            }).join('');
        document.getElementById('shTl').innerHTML      = mkTl(items);
        document.getElementById('shEmailTl').innerHTML = mkTl(items.filter(i=>i.type==='email'));
    } catch(e) {}
}

async function lvSaveLog() {
    if (!shId) return;
    const subj = document.getElementById('logSubj').value.trim();
    const cont = document.getElementById('logCont').value.trim();
    if (!subj && !cont) { lvToast('Ajoutez un sujet ou des notes','warning'); return; }
    try {
        const res = await lvApi({
            action:'add_interaction', lead_id:shId, type:logType,
            subject:subj, content:cont,
            interaction_date: document.getElementById('logDate').value || new Date().toISOString().slice(0,16),
            duration_minutes: document.getElementById('logDur').value || 0,
            outcome: logOutcome,
        });
        if (res.success) {
            lvToast('Interaction enregistrée ✓');
            document.getElementById('logSubj').value=''; document.getElementById('logCont').value='';
            await lvLoadHistory();
            lvShTab('hist');
        } else lvToast(res.error||'Erreur','error');
    } catch(e) { lvToast('Erreur: '+e.message,'error'); }
}

// ─────────────────────────────────────────────────────────────────────────────
// Email Templates Library
// ─────────────────────────────────────────────────────────────────────────────
let tplCache = [];       // cached templates
let tplSelected = null;  // currently selected template
let tplCatFilter = '';   // current category filter
let tplSeeded = false;   // whether seed was attempted

// Get lead variables for merge tags
function lvGetLeadVars() {
    if (!shData) return {};
    const l = shData;
    let fn='',ln='',email='',phone='',city='',notes='';
    if (shTbl==='leads') {
        fn=l.firstname||(l.full_name?l.full_name.split(' ')[0]:''); ln=l.lastname||(l.full_name?l.full_name.split(' ').slice(1).join(' '):'');
        email=l.email||''; phone=l.phone||''; city=l.city||''; notes=l.notes||'';
    } else if (shTbl==='capture_leads') {
        fn=l.prenom||''; ln=l.nom||''; email=l.email||''; phone=l.tel||''; notes=l.message||'';
    } else if (shTbl==='demandes_estimation') {
        ln=(l.type_bien||''); email=l.email||''; phone=l.telephone||''; city=l.ville||'';
        notes=[l.type_bien,l.surface?l.surface+'m²':'',l.estimation_moyenne?'~'+Number(l.estimation_moyenne).toLocaleString('fr-FR')+'€':''].filter(Boolean).join(' — ');
    } else if (shTbl==='contacts') {
        fn=l.firstname||l.prenom||''; ln=l.lastname||l.nom||''; email=l.email||''; phone=l.phone||l.telephone||''; city=l.city||''; notes=l.notes||'';
    } else if (shTbl==='financement_leads') {
        fn=l.prenom||''; ln=l.nom||''; email=l.email||''; phone=l.telephone||'';
        notes=[l.type_projet||'Projet',l.montant_projet?Number(l.montant_projet).toLocaleString('fr-FR')+'€':'',l.notes||''].filter(Boolean).join(' — ');
    }
    return {
        prenom: fn, nom: ln, email: email, telephone: phone, ville: city, notes: notes,
        firstName: fn, lastName: ln, // alias anglais
        email_agent: '<?=htmlspecialchars(ADMIN_EMAIL)?>',
        site_url: '<?=htmlspecialchars(SITE_URL)?>',
        nom_agent: '<?=htmlspecialchars(SITE_TITLE)?>',
    };
}

// Replace merge tags in text
function lvMergeTags(text, vars) {
    if (!text) return '';
    return text.replace(/\{\{(\w+)\}\}/g, (match, key) => vars[key] !== undefined && vars[key] !== '' ? vars[key] : match);
}

// Load templates from server
async function lvLoadTemplates(search) {
    try {
        const params = { action: 'get_email_templates' };
        if (search) params.search = search;
        if (tplCatFilter) params.category = tplCatFilter;
        const res = await lvApi(params);
        if (!res.success) return;
        tplCache = res.templates || [];
        // Render categories (only on first load)
        if (res.categories && res.categories.length > 0) {
            const catsEl = document.getElementById('tplCats');
            let html = '<button class="lv-tpl-cat ' + (!tplCatFilter?'on':'') + '" data-cat="" onclick="lvFilterTplCat(\'\',this)">Tous</button>';
            const catLabels = {prospection:'Prospection',suivi:'Suivi',proposition:'Proposition',relance:'Relance',estimation:'Estimation',rdv:'RDV',transaction:'Transaction',custom:'Personnalisé'};
            res.categories.forEach(c => {
                html += `<button class="lv-tpl-cat ${tplCatFilter===c?'on':''}" data-cat="${c}" onclick="lvFilterTplCat('${c}',this)">${catLabels[c]||c}</button>`;
            });
            catsEl.innerHTML = html;
        }
        lvRenderTplGrid();
    } catch(e) {
        console.error('Template load error:', e);
    }
}

// Render template grid
function lvRenderTplGrid() {
    const grid = document.getElementById('tplGrid');
    if (!tplCache.length) {
        grid.innerHTML = '<div class="lv-tpl-empty" style="grid-column:1/-1"><i class="fas fa-envelope-open-text"></i>Aucun template trouvé<br><button class="lv-btn lv-btn-primary lv-btn-sm" style="margin-top:8px" onclick="lvSeedTemplates()"><i class="fas fa-magic"></i> Créer les templates par défaut</button></div>';
        return;
    }
    const catIcons = {prospection:'paper-plane',suivi:'redo',proposition:'home',relance:'bell',estimation:'chart-line',rdv:'calendar-check',transaction:'file-contract',custom:'paint-brush'};
    grid.innerHTML = tplCache.map(t => {
        const icon = catIcons[t.category] || 'envelope';
        return `<div class="lv-tpl-card ${tplSelected&&tplSelected.id==t.id?'on':''}" onclick="lvSelectTpl(${t.id})" title="${lvEsc(t.name)}">
            <div class="lv-tpl-card-name"><i class="fas fa-${icon}" style="color:#6366f1;font-size:.7rem"></i> ${lvEsc(t.name)}</div>
            <div class="lv-tpl-card-subj">${lvEsc(t.subject)}</div>
            <div class="lv-tpl-card-meta">
                <span class="lv-tpl-card-cat">${lvEsc(t.category)}</span>
                <span class="lv-tpl-card-uses"><i class="fas fa-paper-plane" style="font-size:.55rem"></i> ${t.usage_count||0}</span>
            </div>
        </div>`;
    }).join('');
}

// Seed default templates
async function lvSeedTemplates() {
    if (tplSeeded) return;
    tplSeeded = true;
    try {
        const res = await lvApi({ action: 'seed_email_templates' });
        if (res.success) {
            lvToast(res.message || 'Templates créés', 'success');
            await lvLoadTemplates();
        } else {
            lvToast(res.error || 'Erreur', 'error');
        }
    } catch(e) { lvToast('Erreur: '+e.message, 'error'); }
}

// Select a template
function lvSelectTpl(id) {
    tplSelected = tplCache.find(t => t.id == id);
    if (!tplSelected) return;
    // Highlight in grid
    document.querySelectorAll('.lv-tpl-card').forEach(c => c.classList.remove('on'));
    event.currentTarget.classList.add('on');
    // Show preview with merged variables
    const vars = lvGetLeadVars();
    const preview = document.getElementById('tplPreview');
    document.getElementById('tplPreviewTitle').textContent = tplSelected.name;
    document.getElementById('tplPreviewBody').innerHTML = lvMergeTags(tplSelected.body_html, vars);
    // Show variables
    let varsArr = [];
    try { varsArr = JSON.parse(tplSelected.variables || '[]'); } catch(e) {}
    const varsEl = document.getElementById('tplPreviewVars');
    if (varsArr.length) {
        varsEl.innerHTML = varsArr.map(v => {
            const val = vars[v];
            const filled = val && val !== '';
            return `<span class="lv-tpl-var" style="${filled?'background:#d1fae5;color:#065f46':''}"><i class="fas fa-${filled?'check':'exclamation-circle'}" style="font-size:.55rem"></i> {{${v}}} ${filled?'= '+lvEsc(val):''}</span>`;
        }).join('');
    } else {
        varsEl.innerHTML = '';
    }
    preview.classList.add('on');
}

// Use selected template (populate composer)
function lvUseTpl() {
    if (!tplSelected) return;
    const vars = lvGetLeadVars();
    document.getElementById('emSubj').value = lvMergeTags(tplSelected.subject, vars);
    document.getElementById('emBody').innerHTML = lvMergeTags(tplSelected.body_html, vars);
    document.getElementById('emTplName').innerHTML = '<i class="fas fa-check-circle"></i> ' + lvEsc(tplSelected.name);
    lvCloseTplPreview();
    lvToast('Template appliqué — données client auto-remplies', 'success');
}

function lvCloseTplPreview() {
    document.getElementById('tplPreview').classList.remove('on');
}

// Search templates
let tplSearchTimer;
function lvSearchTpl() {
    clearTimeout(tplSearchTimer);
    tplSearchTimer = setTimeout(() => {
        const q = document.getElementById('tplSearch').value.trim();
        lvLoadTemplates(q);
    }, 300);
}

// Filter by category
function lvFilterTplCat(cat, btn) {
    tplCatFilter = cat;
    document.querySelectorAll('.lv-tpl-cat').forEach(b => b.classList.remove('on'));
    if (btn) btn.classList.add('on');
    lvLoadTemplates(document.getElementById('tplSearch').value.trim());
}

// Toggle template library visibility
function lvToggleTplLib() {
    const lib = document.getElementById('tplLib');
    const visible = lib.style.display !== 'none';
    lib.style.display = visible ? 'none' : '';
    if (!visible && !tplCache.length) lvLoadTemplates();
}

// ─────────────────────────────────────────────────────────────────────────────
// Email — send
// ─────────────────────────────────────────────────────────────────────────────
async function lvSendEmail() {
    const to   = document.getElementById('emTo').value.trim();
    const subj = document.getElementById('emSubj').value.trim();
    const body = document.getElementById('emBody').innerHTML.trim();
    if (!to||!subj||!body) { lvToast('Remplissez tous les champs','warning'); return; }
    const btn = document.getElementById('emBtn');
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Envoi...';
    try {
        const params = { action:'send_email', lead_id:shId, to, subject:subj, body };
        if (tplSelected) params.template_id = tplSelected.id;
        const res = await lvApi(params);
        if (res.success) {
            lvToast('Email envoyé ✓');
            document.getElementById('emSubj').value='';
            document.getElementById('emBody').innerHTML='';
            document.getElementById('emTplName').innerHTML='';
            tplSelected = null;
            await lvLoadHistory();
        } else lvToast(res.error||'Erreur envoi','error');
    } catch(e) { lvToast('Erreur: '+e.message,'error'); }
    btn.disabled=false; btn.innerHTML='<i class="fas fa-paper-plane"></i> Envoyer';
}

// ─────────────────────────────────────────────────────────────────────────────
// Tabs, types, outcomes, utils
// ─────────────────────────────────────────────────────────────────────────────
function lvShTab(name) {
    document.querySelectorAll('.lv-tab').forEach(t => t.classList.toggle('on', t.dataset.tab===name));
    ['info','edit','hist','email','log'].forEach(t => {
        const el = document.getElementById('tab-'+t);
        if (el) el.style.display = (t===name) ? '' : 'none';
    });
    // Auto-load templates when email tab is opened
    if (name === 'email' && !tplCache.length) {
        lvLoadTemplates();
    }
}

function lvSetLT(t, btn) {
    logType = t;
    document.querySelectorAll('[data-t]').forEach(b => b.classList.remove('on'));
    const b = btn || document.querySelector(`[data-t="${t}"]`);
    if (b) b.classList.add('on');
}

function lvSetOT(o, btn) {
    logOutcome = o;
    const c = { positif:['#10b981','#d1fae5','#065f46'], neutre:['#0ea5e9','#e0f2fe','#0c4a6e'], negatif:['#ef4444','#fee2e2','#7f1d1d'] };
    document.querySelectorAll('[data-o]').forEach(b => { b.classList.remove('on'); b.style.cssText=''; });
    btn.classList.add('on'); const [bc,bg,tc]=c[o]; btn.style.borderColor=bc; btn.style.background=bg; btn.style.color=tc;
}

function lvEsc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ─────────────────────────────────────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('logDate').value = new Date().toISOString().slice(0,16);
document.addEventListener('keydown', e => { if(e.key==='Escape'){lvCloseSheet();lvCloseModal();} });
document.getElementById('lvModal').addEventListener('click', e => { if(e.target===e.currentTarget) lvCloseModal(); });
</script>