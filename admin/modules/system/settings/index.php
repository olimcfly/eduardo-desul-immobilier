<?php
/**
 * ══════════════════════════════════════════════════════════════
 *  MODULE SETTINGS — Configuration complète v1.0
 *  /admin/modules/system/settings/index.php
 *  Onglets : Général · Email SMTP · Apparence · Sécurité · API · IA
 * ══════════════════════════════════════════════════════════════
 */
if (!defined('ADMIN_ROUTER')) { header('Location: /admin/dashboard.php?page=settings'); exit; }

// ─── DB ───
if (isset($db) && !isset($pdo)) $pdo = $db;
if (isset($pdo) && !isset($db)) $db  = $pdo;

// ─── Onglet actif ───
$tab = preg_replace('/[^a-z]/', '', $_GET['subpage'] ?? $_GET['tab'] ?? 'general');
$validTabs = ['general','email','appearance','security','api','ai'];
if (!in_array($tab, $validTabs)) $tab = 'general';

// ─── Messages flash ───
$flashMsg  = '';
$flashType = 'ok';

// ─── Traitement POST ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (empty($_SESSION['csrf_token']) || ($_POST['csrf_token'] ?? '') !== $_SESSION['csrf_token']) {
        $flashMsg = 'Token CSRF invalide.'; $flashType = 'err';
    } else {
        $action_post = $_POST['action'];

        if ($action_post === 'save_settings' && $pdo) {
            $fields = $_POST['settings'] ?? [];
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
                foreach ($fields as $key => $val) {
                    $key = preg_replace('/[^a-z0-9_]/', '', $key);
                    if ($key) $stmt->execute([$key, trim($val)]);
                }
                $pdo->commit();
                $flashMsg = 'Paramètres sauvegardés avec succès.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $flashMsg = 'Erreur DB : ' . htmlspecialchars($e->getMessage());
                $flashType = 'err';
            }
        }

        if ($action_post === 'test_smtp') {
            $to   = trim($_POST['test_email'] ?? '');
            $host = trim($_POST['smtp_host'] ?? '');
            $port = (int)($_POST['smtp_port'] ?? 587);
            $user = trim($_POST['smtp_user'] ?? '');
            $pass = trim($_POST['smtp_pass'] ?? '');
            $fromName = trim($_POST['smtp_from_name'] ?? 'IMMO LOCAL+');
            $fromEmail = trim($_POST['smtp_from_email'] ?? $user);
            $notifyEmail = trim($_POST['notify_email'] ?? $fromEmail);
            if (!$to || !$host) {
                $flashMsg = 'Email de test et hôte SMTP requis.'; $flashType = 'err';
            } else {
                // Test via PHPMailer si disponible, sinon mail() basique
                if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                    try {
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        $mail->isSMTP(); $mail->Host = $host; $mail->Port = $port;
                        $mail->SMTPAuth = true; $mail->Username = $user; $mail->Password = $pass;
                        $mail->Timeout = 12;
                        $mail->SMTPSecure = $port === 465 ? 'ssl' : 'tls';
                        $mail->setFrom($fromEmail ?: $user, $fromName ?: 'IMMO LOCAL+ Test');

                        if (!$mail->smtpConnect()) {
                            throw new Exception('Connexion SMTP impossible (hôte/port/identifiants).');
                        }
                        $mail->smtpClose();

                        $mail->addAddress($to);
                        $mail->Subject = 'Test SMTP réussi — IMMO LOCAL+';
                        $mail->Body    = "Connexion SMTP validée avec succès.\n\nHôte: {$host}\nPort: {$port}\nDate: " . date('Y-m-d H:i:s');
                        $mail->send();

                        if (filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
                            $mail->clearAddresses();
                            $mail->addAddress($notifyEmail);
                            $mail->Subject = 'Notification: test SMTP validé';
                            $mail->Body = "Le test de connexion SMTP a réussi.\n\nUn email de test a été envoyé à: {$to}\nHôte: {$host}\nPort: {$port}\nDate: " . date('Y-m-d H:i:s');
                            $mail->send();
                        }

                        $flashMsg = '✅ Connexion SMTP réussie. Email de test envoyé et notification confirmée.';
                    } catch (Exception $e) {
                        $flashMsg = '❌ Erreur SMTP : ' . htmlspecialchars($e->getMessage()); $flashType = 'err';
                    }
                } else {
                    $sent = @mail($to, 'Test SMTP — IMMO LOCAL+', 'Test email depuis IMMO LOCAL+', 'From: ' . $user);
                    $flashMsg = $sent ? '✅ Email envoyé (mail() natif)' : '❌ Échec mail() natif — configurez PHPMailer'; 
                    if (!$sent) $flashType = 'err';
                }
            }
        }

        if ($action_post === 'save_api_key' && $pdo) {
            $service = preg_replace('/[^a-z0-9_]/', '', $_POST['service'] ?? '');
            $apiKey  = trim($_POST['api_key'] ?? '');
            if ($service && $apiKey) {
                try {
                    $pdo->prepare("INSERT INTO api_keys (service, api_key, status, updated_at)
                        VALUES (?, AES_ENCRYPT(?, 'immo_secret_key'), 'active', NOW())
                        ON DUPLICATE KEY UPDATE api_key = AES_ENCRYPT(?, 'immo_secret_key'), status='active', updated_at=NOW()")
                        ->execute([$service, $apiKey, $apiKey]);
                    $flashMsg = 'Clé API "' . htmlspecialchars($service) . '" sauvegardée.';
                } catch (PDOException $e) {
                    // Table api_keys pas encore créée — fallback settings
                    try {
                        $pdo->prepare("INSERT INTO settings (setting_key, setting_value)
                            VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()")
                            ->execute(['api_key_' . $service, $apiKey]);
                        $flashMsg = 'Clé API sauvegardée dans settings.';
                    } catch (PDOException $e2) {
                        $flashMsg = 'Erreur : ' . htmlspecialchars($e2->getMessage()); $flashType = 'err';
                    }
                }
            }
        }
    }
    // Régénérer CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ─── Chargement des settings ───
$settings = [];
if ($pdo) {
    try {
        $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];
    } catch (PDOException $e) {}
}

// ─── API Keys ───
$apiKeys = [];
if ($pdo) {
    try {
        $rows = $pdo->query("SELECT service, status, updated_at FROM api_keys ORDER BY service")->fetchAll();
        foreach ($rows as $r) $apiKeys[$r['service']] = $r;
    } catch (PDOException $e) {
        // Fallback depuis settings
        foreach ($settings as $k => $v) {
            if (str_starts_with($k, 'api_key_')) {
                $svc = substr($k, 8);
                $apiKeys[$svc] = ['service' => $svc, 'status' => 'active', 'updated_at' => null];
            }
        }
    }
}

function s($key, $default = '') {
    global $settings;
    return htmlspecialchars($settings[$key] ?? $default);
}

function hasApi($svc) {
    global $apiKeys, $settings;
    return isset($apiKeys[$svc]) || isset($settings['api_key_' . $svc]);
}

function emailDomainFromValue($emailOrDomain) {
    $value = trim((string)$emailOrDomain);
    if ($value === '') return '';
    if (strpos($value, '@') !== false) {
        $parts = explode('@', $value);
        return strtolower(trim(end($parts)));
    }
    return strtolower($value);
}

function getTxtRecordsForDomain($domain) {
    if ($domain === '') return [];
    $records = @dns_get_record($domain, DNS_TXT);
    if (!is_array($records)) return [];

    $txt = [];
    foreach ($records as $record) {
        $value = $record['txt'] ?? ($record['entries'][0] ?? '');
        if ($value !== '') $txt[] = trim((string)$value);
    }
    return $txt;
}

function buildEmailAuthDiagnostics($settings) {
    $fromEmail = trim((string)($settings['smtp_from_email'] ?? ''));
    $domain = emailDomainFromValue($fromEmail);

    if ($domain === '') {
        $siteUrl = trim((string)($settings['site_url'] ?? ''));
        $host = $siteUrl ? (parse_url($siteUrl, PHP_URL_HOST) ?: '') : '';
        $domain = strtolower(preg_replace('/^www\./i', '', $host));
    }

    if ($domain === '') {
        return [
            'domain' => '',
            'spf' => ['status' => 'missing', 'message' => 'Renseignez un email expéditeur pour lancer l’analyse SPF.'],
            'dmarc' => ['status' => 'missing', 'message' => 'Renseignez un email expéditeur pour lancer l’analyse DMARC.'],
        ];
    }

    $txtRecords = getTxtRecordsForDomain($domain);
    $spf = '';
    foreach ($txtRecords as $txt) {
        if (stripos($txt, 'v=spf1') !== false) {
            $spf = $txt;
            break;
        }
    }

    $dmarcDomain = '_dmarc.' . $domain;
    $dmarcRecords = getTxtRecordsForDomain($dmarcDomain);
    $dmarc = '';
    foreach ($dmarcRecords as $txt) {
        if (stripos($txt, 'v=DMARC1') !== false) {
            $dmarc = $txt;
            break;
        }
    }

    return [
        'domain' => $domain,
        'spf' => [
            'status' => $spf ? 'ok' : 'missing',
            'message' => $spf ?: 'Aucun enregistrement SPF détecté (TXT avec v=spf1).',
        ],
        'dmarc' => [
            'status' => $dmarc ? 'ok' : 'missing',
            'message' => $dmarc ?: 'Aucun enregistrement DMARC détecté (_dmarc.' . $domain . ').',
        ],
    ];
}

$csrf = $_SESSION['csrf_token'];
$emailDiagnostics = buildEmailAuthDiagnostics($settings);

// ─── Advisor Context ───
$advisorCtx = [];
if ($pdo) {
    try {
        $r = $pdo->query("SELECT * FROM advisor_context LIMIT 1")->fetch();
        if ($r) $advisorCtx = $r;
    } catch (PDOException $e) {}
}
?>
<style>
.set-wrap { max-width: 960px; }
.set-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--border); margin-bottom: 24px; flex-wrap: wrap; }
.set-tab {
    padding: 10px 18px; font-size: 12px; font-weight: 600; color: var(--text-3);
    cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px;
    transition: all .12s; display: flex; align-items: center; gap: 7px; text-decoration: none;
    white-space: nowrap;
}
.set-tab:hover { color: var(--text); }
.set-tab.active { color: var(--accent); border-bottom-color: var(--accent); }
.set-tab i { font-size: 11px; }

.set-panel { display: none; }
.set-panel.active { display: block; }

.set-section {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
    margin-bottom: 16px; overflow: hidden;
}
.set-section-hd {
    padding: 14px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px; background: var(--surface-2);
}
.set-section-hd h3 { font-family: var(--font-display); font-size: 13px; font-weight: 700; }
.set-section-hd i { color: var(--accent); font-size: 13px; width: 18px; text-align: center; }
.set-section-hd p { font-size: 11px; color: var(--text-3); margin-left: auto; }
.set-section-body { padding: 20px; }

.set-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.set-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
.set-full { grid-column: 1 / -1; }

.set-field { display: flex; flex-direction: column; gap: 5px; }
.set-field label { font-size: 11px; font-weight: 700; color: var(--text-2); letter-spacing: .01em; }
.set-field small { font-size: 10px; color: var(--text-3); }
.set-input {
    padding: 9px 12px; border: 1px solid var(--border); border-radius: var(--radius);
    font-size: 12px; font-family: var(--font); background: var(--surface);
    transition: border-color .15s; width: 100%; color: var(--text);
}
.set-input:focus { outline: 0; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,70,229,.08); }
.set-input.mono { font-family: var(--mono); font-size: 11px; }
.set-textarea { resize: vertical; min-height: 80px; }
.set-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23666' d='M6 8L0 0h12z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; }

.set-btn {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
    border-radius: var(--radius); font-family: var(--font); font-size: 12px; font-weight: 600;
    cursor: pointer; border: 1px solid var(--border); transition: all .15s;
}
.set-btn-p { background: var(--accent); color: #fff; border-color: var(--accent); }
.set-btn-p:hover { background: #4338ca; }
.set-btn-s { background: var(--surface); color: var(--text); }
.set-btn-s:hover { background: var(--surface-2); }
.set-btn-danger { background: var(--red-bg); color: var(--red); border-color: rgba(220,38,38,.15); }
.set-btn-danger:hover { background: var(--red); color: #fff; }

.set-actions { display: flex; gap: 8px; align-items: center; padding-top: 14px; border-top: 1px solid var(--border); margin-top: 14px; }

.flash { padding: 11px 16px; border-radius: var(--radius); font-size: 12px; font-weight: 600; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.flash.ok { background: var(--green-bg); color: var(--green); border: 1px solid rgba(5,150,105,.15); }
.flash.err { background: var(--red-bg); color: var(--red); border: 1px solid rgba(220,38,38,.15); }

.email-auth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.email-auth-card { border: 1px solid var(--border); border-radius: var(--radius); padding: 12px; background: var(--surface); }
.email-auth-status { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 999px; }
.email-auth-status.ok { background: var(--green-bg); color: var(--green); }
.email-auth-status.missing { background: var(--amber-bg); color: var(--amber); }
.email-auth-record { margin-top: 10px; font-family: var(--mono); font-size: 11px; color: var(--text-2); word-break: break-word; }
.email-help-list { margin: 0; padding-left: 18px; display: grid; gap: 6px; }


.flash-toast {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1200;
    min-width: 280px;
    max-width: min(460px, calc(100vw - 24px));
    box-shadow: 0 10px 30px rgba(0,0,0,.16);
    opacity: 0;
    transform: translateY(10px);
    pointer-events: none;
    transition: opacity .2s ease, transform .2s ease;
}
.flash-toast.show {
    opacity: 1;
    transform: translateY(0);
}

.api-service {
    display: flex; align-items: center; gap: 14px; padding: 12px 16px;
    background: var(--surface-2); border-radius: var(--radius); margin-bottom: 8px; border: 1px solid var(--border);
}
.api-service:last-child { margin-bottom: 0; }
.api-service-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.api-service-info { flex: 1; min-width: 0; }
.api-service-name { font-size: 12px; font-weight: 700; }
.api-service-desc { font-size: 10px; color: var(--text-3); }
.api-badge { font-size: 9px; padding: 2px 8px; border-radius: 4px; font-weight: 700; }
.api-badge.configured { background: var(--green-bg); color: var(--green); }
.api-badge.missing { background: var(--amber-bg); color: var(--amber); }
.api-expand-btn { width: 30px; height: 30px; border-radius: var(--radius); border: 1px solid var(--border); background: var(--surface); display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px; color: var(--text-3); transition: all .12s; flex-shrink: 0; }
.api-expand-btn:hover { background: var(--accent-bg); color: var(--accent); border-color: var(--accent); }
.api-form { margin-top: 10px; padding: 14px; background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); display: none; }
.api-form.open { display: block; }

.color-swatch { width: 40px; height: 38px; border-radius: var(--radius); border: 1px solid var(--border); cursor: pointer; padding: 0; overflow: hidden; }
.color-field { display: flex; gap: 8px; align-items: center; }
.color-field input[type=color] { width: 40px; height: 38px; padding: 2px; border-radius: var(--radius); border: 1px solid var(--border); cursor: pointer; }
.color-field input[type=text] { flex: 1; }

.toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
.toggle-row:last-child { border-bottom: 0; padding-bottom: 0; }
.toggle-row-info label { font-size: 12px; font-weight: 600; display: block; margin-bottom: 1px; }
.toggle-row-info small { font-size: 10px; color: var(--text-3); }
.toggle-sw { position: relative; width: 40px; height: 22px; flex-shrink: 0; }
.toggle-sw input { opacity: 0; width: 0; height: 0; }
.toggle-sw-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--surface-3); border-radius: 11px; transition: .2s; }
.toggle-sw-slider::before { content: ''; position: absolute; height: 16px; width: 16px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
.toggle-sw input:checked + .toggle-sw-slider { background: var(--accent); }
.toggle-sw input:checked + .toggle-sw-slider::before { transform: translateX(18px); }

@media (max-width: 700px) {
    .set-grid, .set-grid-3 { grid-template-columns: 1fr; }
    .set-full { grid-column: 1; }
    .email-auth-grid { grid-template-columns: 1fr; }
}
</style>

<div class="set-wrap">

<?php if ($flashMsg): ?>
<div class="flash <?= $flashType ?> anim"><i class="fas fa-<?= $flashType==='ok'?'check-circle':'exclamation-circle' ?>"></i> <?= $flashMsg ?></div>
<?php endif; ?>


<?php if ($flashMsg): ?>
<div class="flash <?= $flashType ?> flash-toast" id="settings-toast" role="status" aria-live="polite">
    <i class="fas fa-<?= $flashType==='ok'?'check-circle':'exclamation-circle' ?>"></i>
    <span><?= $flashMsg ?></span>
</div>
<script>
(() => {
    const toast = document.getElementById('settings-toast');
    if (!toast) return;
    window.requestAnimationFrame(() => toast.classList.add('show'));
    window.setTimeout(() => toast.classList.remove('show'), 5000);
})();
</script>
<?php endif; ?>

<!-- ONGLETS -->
<div class="set-tabs">
    <?php
    $tabs = [
        'general'    => ['icon' => 'fa-sliders',           'label' => 'Général'],
        'email'      => ['icon' => 'fa-envelope',          'label' => 'Email / SMTP'],
        'appearance' => ['icon' => 'fa-palette',           'label' => 'Apparence'],
        'security'   => ['icon' => 'fa-shield-halved',     'label' => 'Sécurité'],
        'api'        => ['icon' => 'fa-plug',              'label' => 'API & Intégrations'],
        'ai'         => ['icon' => 'fa-wand-magic-sparkles','label' => 'Configuration IA'],
    ];
    foreach ($tabs as $k => $t):
    ?>
    <a href="?page=settings&tab=<?= $k ?>"
       class="set-tab<?= $tab===$k?' active':'' ?>"
       onclick="switchTab('<?= $k ?>');return false;">
        <i class="fas <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════
     ONGLET GÉNÉRAL
══════════════════════════════════════ -->
<div class="set-panel<?= $tab==='general'?' active':'' ?>" id="tab-general">
<form method="POST" action="?page=settings&tab=general">
<input type="hidden" name="action" value="save_settings">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<input type="hidden" name="settings[_tab]" value="general">

<div class="set-section anim">
    <div class="set-section-hd"><i class="fas fa-user-tie"></i><h3>Identité du conseiller</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>Prénom & Nom</label><input class="set-input" type="text" name="settings[advisor_name]" value="<?= s('advisor_name', $advisorCtx['name'] ?? 'Eduardo De Sul') ?>" placeholder="Eduardo De Sul"></div>
            <div class="set-field"><label>Réseau / Enseigne</label><input class="set-input" type="text" name="settings[advisor_network]" value="<?= s('advisor_network', $advisorCtx['network'] ?? 'eXp France') ?>" placeholder="eXp France"></div>
            <div class="set-field"><label>Téléphone</label><input class="set-input" type="text" name="settings[advisor_phone]" value="<?= s('advisor_phone', $advisorCtx['phone'] ?? '06 24 10 58 16') ?>"></div>
            <div class="set-field"><label>Email de contact</label><input class="set-input" type="email" name="settings[advisor_email]" value="<?= s('advisor_email', $advisorCtx['email'] ?? 'contact@eduardo-desul-immobilier.fr') ?>"></div>
            <div class="set-field set-full"><label>Adresse professionnelle</label><input class="set-input" type="text" name="settings[advisor_address]" value="<?= s('advisor_address', $advisorCtx['address'] ?? '12A rue du Commandant Charcot, 33290 Blanquefort') ?>"></div>
            <div class="set-field"><label>Carte CPI</label><input class="set-input mono" type="text" name="settings[advisor_cpi]" value="<?= s('advisor_cpi', $advisorCtx['cpi_card'] ?? 'CPI 7501 2021 000 000 444') ?>"></div>
            <div class="set-field"><label>Zone géographique principale</label><input class="set-input" type="text" name="settings[advisor_zone]" value="<?= s('advisor_zone', $advisorCtx['zone'] ?? 'Bordeaux et agglomération') ?>"></div>
        </div>
    </div>
</div>

<div class="set-section anim d1">
    <div class="set-section-hd"><i class="fas fa-globe"></i><h3>Site web</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>Nom du site</label><input class="set-input" type="text" name="settings[site_name]" value="<?= s('site_name', 'Eduardo De Sul — Conseiller Immobilier Bordeaux') ?>"></div>
            <div class="set-field"><label>URL du site</label><input class="set-input mono" type="url" name="settings[site_url]" value="<?= s('site_url', 'https://eduardo-desul-immobilier.fr') ?>"></div>
            <div class="set-field set-full"><label>Meta description par défaut</label><input class="set-input" type="text" name="settings[site_meta_description]" value="<?= s('site_meta_description') ?>" placeholder="Description pour les moteurs de recherche…" maxlength="160"></div>
            <div class="set-field"><label>Langue</label>
                <select class="set-input set-select" name="settings[site_lang]">
                    <option value="fr" <?= (s('site_lang','fr')==='fr'?'selected':'') ?>>Français</option>
                    <option value="en" <?= (s('site_lang','fr')==='en'?'selected':'') ?>>English</option>
                </select>
            </div>
            <div class="set-field"><label>Fuseau horaire</label>
                <select class="set-input set-select" name="settings[site_timezone]">
                    <option value="Europe/Paris" <?= (s('site_timezone','Europe/Paris')==='Europe/Paris'?'selected':'') ?>>Europe/Paris</option>
                    <option value="UTC">UTC</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="set-section anim d2">
    <div class="set-section-hd"><i class="fas fa-file-code"></i><h3>Scripts & Tracking</h3><p>Codes injectés dans &lt;head&gt; ou &lt;/body&gt;</p></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>Google Analytics ID</label><input class="set-input mono" type="text" name="settings[ga_id]" value="<?= s('ga_id') ?>" placeholder="G-XXXXXXXXXX"></div>
            <div class="set-field"><label>Google Tag Manager ID</label><input class="set-input mono" type="text" name="settings[gtm_id]" value="<?= s('gtm_id') ?>" placeholder="GTM-XXXXXXX"></div>
            <div class="set-field"><label>Facebook Pixel ID</label><input class="set-input mono" type="text" name="settings[fb_pixel_id]" value="<?= s('fb_pixel_id') ?>" placeholder="000000000000000"></div>
            <div class="set-field"><label>Google Site Verification</label><input class="set-input mono" type="text" name="settings[google_verify]" value="<?= s('google_verify') ?>" placeholder="..."></div>
            <div class="set-field set-full"><label>Script HEAD personnalisé</label><textarea class="set-input set-textarea mono" name="settings[custom_head_script]" placeholder="<!-- Vos scripts dans <head> -->"><?= s('custom_head_script') ?></textarea></div>
            <div class="set-field set-full"><label>Script BODY fin personnalisé</label><textarea class="set-input set-textarea mono" name="settings[custom_body_script]" placeholder="<!-- Vos scripts avant </body> -->"><?= s('custom_body_script') ?></textarea></div>
        </div>
    </div>
</div>

<div class="set-actions"><button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Enregistrer les paramètres généraux</button></div>
</form>
</div>

<!-- ══════════════════════════════════════
     ONGLET EMAIL / SMTP
══════════════════════════════════════ -->
<div class="set-panel<?= $tab==='email'?' active':'' ?>" id="tab-email">
<form method="POST" action="?page=settings&tab=email">
<input type="hidden" name="action" value="save_settings">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<div class="set-section anim">
    <div class="set-section-hd"><i class="fas fa-server"></i><h3>Configuration SMTP principale</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>Hôte SMTP</label><input class="set-input mono" type="text" name="settings[smtp_host]" value="<?= s('smtp_host', 'mail.o2switch.net') ?>" placeholder="mail.votredomaine.fr"></div>
            <div class="set-field"><label>Port</label>
                <select class="set-input set-select" name="settings[smtp_port]">
                    <option value="587" <?= s('smtp_port','587')==='587'?'selected':'' ?>>587 — STARTTLS (recommandé)</option>
                    <option value="465" <?= s('smtp_port','587')==='465'?'selected':'' ?>>465 — SSL/TLS</option>
                    <option value="25"  <?= s('smtp_port','587')==='25'?'selected':'' ?>>25 — Non sécurisé</option>
                </select>
            </div>
            <div class="set-field"><label>Utilisateur SMTP</label><input class="set-input mono" type="text" name="settings[smtp_user]" value="<?= s('smtp_user') ?>" placeholder="contact@votredomaine.fr"></div>
            <div class="set-field"><label>Mot de passe SMTP</label><input class="set-input mono" type="password" name="settings[smtp_pass]" value="<?= s('smtp_pass') ? '••••••••' : '' ?>" placeholder="••••••••" autocomplete="new-password"></div>
            <div class="set-field"><label>Nom expéditeur</label><input class="set-input" type="text" name="settings[smtp_from_name]" value="<?= s('smtp_from_name', 'Eduardo De Sul') ?>"></div>
            <div class="set-field"><label>Email expéditeur (From)</label><input class="set-input mono" type="email" name="settings[smtp_from_email]" value="<?= s('smtp_from_email') ?>" placeholder="contact@votredomaine.fr"></div>
        </div>
    </div>
</div>

<div class="set-section anim d1">
    <div class="set-section-hd"><i class="fas fa-envelope-circle-check"></i><h3>Tester la configuration SMTP</h3><p><a href="https://www.mail-tester.com/" target="_blank" rel="noopener">Ouvrir Mail-Tester</a></p></div>
    <div class="set-section-body">
        <form method="POST" action="?page=settings&tab=email" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
            <input type="hidden" name="action" value="test_smtp">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="smtp_host" value="<?= s('smtp_host') ?>">
            <input type="hidden" name="smtp_port" value="<?= s('smtp_port','587') ?>">
            <input type="hidden" name="smtp_user" value="<?= s('smtp_user') ?>">
            <input type="hidden" name="smtp_pass" value="<?= s('smtp_pass') ?>">
            <input type="hidden" name="smtp_from_name" value="<?= s('smtp_from_name', 'Eduardo De Sul') ?>">
            <input type="hidden" name="smtp_from_email" value="<?= s('smtp_from_email') ?>">
            <input type="hidden" name="notify_email" value="<?= s('advisor_email', s('smtp_from_email')) ?>">
            <div class="set-field" style="flex:1;min-width:220px">
                <label>Email de test</label>
                <input class="set-input" type="email" name="test_email" value="<?= s('advisor_email') ?>" placeholder="votre@email.fr" required>
            </div>
            <button type="submit" class="set-btn set-btn-s"><i class="fas fa-paper-plane"></i> Envoyer un email de test</button>
        </form>
        <small style="display:block;margin-top:8px;color:var(--text-3)">Le test vérifie d’abord la connexion SMTP puis envoie un email de test + une notification de succès.</small>
    </div>
</div>

<div class="set-section anim d2">
    <div class="set-section-hd"><i class="fas fa-shield-check"></i><h3>Tableau de bord délivrabilité (SPF / DMARC)</h3></div>
    <div class="set-section-body">
        <p style="margin-bottom:12px;font-size:12px;color:var(--text-2)">Domaine analysé : <strong><?= htmlspecialchars($emailDiagnostics['domain'] ?: 'Non défini') ?></strong></p>
        <div class="email-auth-grid">
            <div class="email-auth-card">
                <div class="email-auth-status <?= $emailDiagnostics['spf']['status'] ?>">
                    <i class="fas <?= $emailDiagnostics['spf']['status'] === 'ok' ? 'fa-check-circle' : 'fa-triangle-exclamation' ?>"></i>
                    SPF <?= $emailDiagnostics['spf']['status'] === 'ok' ? 'OK' : 'À configurer' ?>
                </div>
                <div class="email-auth-record"><?= htmlspecialchars($emailDiagnostics['spf']['message']) ?></div>
            </div>
            <div class="email-auth-card">
                <div class="email-auth-status <?= $emailDiagnostics['dmarc']['status'] ?>">
                    <i class="fas <?= $emailDiagnostics['dmarc']['status'] === 'ok' ? 'fa-check-circle' : 'fa-triangle-exclamation' ?>"></i>
                    DMARC <?= $emailDiagnostics['dmarc']['status'] === 'ok' ? 'OK' : 'À configurer' ?>
                </div>
                <div class="email-auth-record"><?= htmlspecialchars($emailDiagnostics['dmarc']['message']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="set-section anim d3">
    <div class="set-section-hd"><i class="fas fa-robot"></i><h3>SMTP secondaire (Estimations IA)</h3><p>Pour les réponses automatiques</p></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>Hôte SMTP 2</label><input class="set-input mono" type="text" name="settings[smtp2_host]" value="<?= s('smtp2_host') ?>" placeholder="mail2.votredomaine.fr"></div>
            <div class="set-field"><label>Port</label><input class="set-input mono" type="number" name="settings[smtp2_port]" value="<?= s('smtp2_port','587') ?>"></div>
            <div class="set-field"><label>Utilisateur</label><input class="set-input mono" type="text" name="settings[smtp2_user]" value="<?= s('smtp2_user') ?>"></div>
            <div class="set-field"><label>Mot de passe</label><input class="set-input mono" type="password" name="settings[smtp2_pass]" value="<?= s('smtp2_pass') ? '••••••••' : '' ?>" autocomplete="new-password"></div>
        </div>
    </div>
</div>

<div class="set-section anim d4" id="email-help-module">
    <div class="set-section-hd"><i class="fas fa-circle-question"></i><h3>Aide — Configurer SPF / DMARC</h3></div>
    <div class="set-section-body">
        <ul class="email-help-list">
            <li>Ajoutez un enregistrement TXT SPF sur votre domaine principal (ex: <code>v=spf1 include:_spf.votresmtp.com ~all</code>).</li>
            <li>Ajoutez un enregistrement TXT DMARC sur <code>_dmarc.votredomaine.fr</code> (ex: <code>v=DMARC1; p=none; rua=mailto:postmaster@votredomaine.fr</code>).</li>
            <li>Après propagation DNS (jusqu’à 24-48h), revenez sur cet écran pour relancer l’analyse.</li>
            <li>Validez ensuite votre délivrabilité avec <a href="https://www.mail-tester.com/" target="_blank" rel="noopener">mail-tester.com</a>.</li>
        </ul>
    </div>
</div>

<div class="set-actions"><button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Enregistrer la configuration email</button></div>
</form>
</div>

<!-- ══════════════════════════════════════
     ONGLET APPARENCE
══════════════════════════════════════ -->
<div class="set-panel<?= $tab==='appearance'?' active':'' ?>" id="tab-appearance">
<form method="POST" action="?page=settings&tab=appearance">
<input type="hidden" name="action" value="save_settings">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<div class="set-section anim">
    <div class="set-section-hd"><i class="fas fa-droplet"></i><h3>Palette de couleurs</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <?php
            $colorFields = [
                ['color_primary',    '#1a4d7a', 'Couleur principale',       'Bleu profond — headers, boutons CTA'],
                ['color_accent',     '#d4a574', 'Couleur d\'accent',        'Or chaud — highlights, icônes'],
                ['color_background', '#f9f6f3', 'Fond général',             'Beige clair — fond de page'],
                ['color_text',       '#1a1816', 'Texte principal',          'Couleur des titres et corps'],
                ['color_text_muted', '#57534e', 'Texte secondaire',         'Descriptions, sous-titres'],
                ['color_border',     '#e8e6e1', 'Bordures',                 'Séparateurs et cadres'],
            ];
            foreach ($colorFields as [$key, $default, $label, $help]):
                $val = $settings[$key] ?? $default;
            ?>
            <div class="set-field">
                <label><?= $label ?></label>
                <div class="color-field">
                    <input type="color" value="<?= htmlspecialchars($val) ?>" oninput="document.getElementById('txt_<?= $key ?>').value=this.value;document.getElementById('preview_<?= $key ?>').style.background=this.value">
                    <input class="set-input mono" type="text" id="txt_<?= $key ?>" name="settings[<?= $key ?>]" value="<?= htmlspecialchars($val) ?>" maxlength="7">
                    <div id="preview_<?= $key ?>" style="width:38px;height:38px;border-radius:var(--radius);border:1px solid var(--border);background:<?= htmlspecialchars($val) ?>;flex-shrink:0"></div>
                </div>
                <small><?= $help ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="set-section anim d1">
    <div class="set-section-hd"><i class="fas fa-font"></i><h3>Typographie</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field">
                <label>Police des titres</label>
                <select class="set-input set-select" name="settings[font_heading]">
                    <?php foreach (['Playfair Display','Merriweather','Lora','Georgia','Times New Roman'] as $f): ?>
                    <option value="<?= $f ?>" <?= s('font_heading','Playfair Display')===$f?'selected':'' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="set-field">
                <label>Police du corps</label>
                <select class="set-input set-select" name="settings[font_body]">
                    <?php foreach (['DM Sans','Inter','Open Sans','Lato','Nunito','Poppins'] as $f): ?>
                    <option value="<?= $f ?>" <?= s('font_body','DM Sans')===$f?'selected':'' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="set-field"><label>Taille de base (px)</label><input class="set-input mono" type="number" name="settings[font_base_size]" value="<?= s('font_base_size','16') ?>" min="12" max="20"></div>
            <div class="set-field"><label>Hauteur de ligne</label><input class="set-input mono" type="text" name="settings[font_line_height]" value="<?= s('font_line_height','1.6') ?>"></div>
        </div>
    </div>
</div>

<div class="set-section anim d2">
    <div class="set-section-hd"><i class="fas fa-image"></i><h3>Logo & Favicon</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>URL du logo (SVG ou PNG)</label><input class="set-input mono" type="text" name="settings[logo_url]" value="<?= s('logo_url','/front/assets/images/logo.svg') ?>"></div>
            <div class="set-field"><label>Alt du logo</label><input class="set-input" type="text" name="settings[logo_alt]" value="<?= s('logo_alt','Eduardo De Sul Immobilier') ?>"></div>
            <div class="set-field"><label>URL favicon (.ico ou .png)</label><input class="set-input mono" type="text" name="settings[favicon_url]" value="<?= s('favicon_url','/favicon.ico') ?>"></div>
            <div class="set-field"><label>Border-radius global (px)</label><input class="set-input mono" type="number" name="settings[border_radius]" value="<?= s('border_radius','12') ?>" min="0" max="30"></div>
        </div>
    </div>
</div>

<div class="set-actions"><button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Enregistrer l'apparence</button></div>
</form>
</div>

<!-- ══════════════════════════════════════
     ONGLET SÉCURITÉ
══════════════════════════════════════ -->
<div class="set-panel<?= $tab==='security'?' active':'' ?>" id="tab-security">
<form method="POST" action="?page=settings&tab=security">
<input type="hidden" name="action" value="save_settings">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<div class="set-section anim">
    <div class="set-section-hd"><i class="fas fa-lock"></i><h3>Accès admin</h3></div>
    <div class="set-section-body">
        <div class="toggle-row"><div class="toggle-row-info"><label>Authentification à deux facteurs</label><small>Envoie un code par email lors de la connexion</small></div><label class="toggle-sw"><input type="checkbox" name="settings[auth_2fa]" value="1" <?= ($settings['auth_2fa']??'0')==='1'?'checked':'' ?>><span class="toggle-sw-slider"></span></label></div>
        <div class="toggle-row"><div class="toggle-row-info"><label>Bloquer après échecs de connexion</label><small>Verrouillage 15 min après 5 tentatives</small></div><label class="toggle-sw"><input type="checkbox" name="settings[auth_lockout]" value="1" <?= ($settings['auth_lockout']??'1')==='1'?'checked':'' ?>><span class="toggle-sw-slider"></span></label></div>
        <div class="toggle-row"><div class="toggle-row-info"><label>Mode maintenance</label><small>Affiche une page de maintenance aux visiteurs</small></div><label class="toggle-sw"><input type="checkbox" name="settings[maintenance_mode]" value="1" <?= ($settings['maintenance_mode']??'0')==='1'?'checked':'' ?>><span class="toggle-sw-slider"></span></label></div>
        <div style="margin-top:14px" class="set-grid">
            <div class="set-field"><label>Durée session admin (heures)</label><input class="set-input mono" type="number" name="settings[session_lifetime]" value="<?= s('session_lifetime','8') ?>" min="1" max="168"></div>
            <div class="set-field"><label>IPs autorisées admin (optionnel)</label><input class="set-input mono" type="text" name="settings[admin_allowed_ips]" value="<?= s('admin_allowed_ips') ?>" placeholder="192.168.1.0/24, 85.x.x.x"></div>
        </div>
    </div>
</div>

<div class="set-section anim d1">
    <div class="set-section-hd"><i class="fas fa-shield-halved"></i><h3>RGPD & Confidentialité</h3></div>
    <div class="set-section-body">
        <div class="toggle-row"><div class="toggle-row-info"><label>Bannière cookies</label><small>Affichage du bandeau RGPD sur le site public</small></div><label class="toggle-sw"><input type="checkbox" name="settings[gdpr_banner]" value="1" <?= ($settings['gdpr_banner']??'1')==='1'?'checked':'' ?>><span class="toggle-sw-slider"></span></label></div>
        <div class="toggle-row"><div class="toggle-row-info"><label>Consentement obligatoire formulaires</label><small>Case à cocher obligatoire sur tous les formulaires</small></div><label class="toggle-sw"><input type="checkbox" name="settings[gdpr_form_consent]" value="1" <?= ($settings['gdpr_form_consent']??'1')==='1'?'checked':'' ?>><span class="toggle-sw-slider"></span></label></div>
        <div style="margin-top:14px" class="set-grid">
            <div class="set-field"><label>Durée conservation leads (jours)</label><input class="set-input mono" type="number" name="settings[gdpr_leads_retention]" value="<?= s('gdpr_leads_retention','1095') ?>" min="30"></div>
            <div class="set-field"><label>Email DPO</label><input class="set-input mono" type="email" name="settings[gdpr_dpo_email]" value="<?= s('gdpr_dpo_email', $settings['advisor_email'] ?? '') ?>"></div>
            <div class="set-field set-full"><label>URL politique de confidentialité</label><input class="set-input mono" type="text" name="settings[gdpr_policy_url]" value="<?= s('gdpr_policy_url','/politique-confidentialite') ?>"></div>
        </div>
    </div>
</div>

<div class="set-section anim d2">
    <div class="set-section-hd"><i class="fas fa-key"></i><h3>Changer le mot de passe admin</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field"><label>Mot de passe actuel</label><input class="set-input mono" type="password" name="current_password" autocomplete="current-password" placeholder="••••••••"></div>
            <div class="set-field"><label>Nouveau mot de passe</label><input class="set-input mono" type="password" name="new_password" autocomplete="new-password" placeholder="Min. 12 caractères"></div>
            <div class="set-field"><label>Confirmer</label><input class="set-input mono" type="password" name="confirm_password" autocomplete="new-password" placeholder="Répétez le mot de passe"></div>
        </div>
    </div>
</div>

<div class="set-actions"><button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Enregistrer la sécurité</button></div>
</form>
</div>

<!-- ══════════════════════════════════════
     ONGLET API & INTÉGRATIONS
══════════════════════════════════════ -->
<div class="set-panel<?= $tab==='api'?' active':'' ?>" id="tab-api">

<div class="set-section anim">
    <div class="set-section-hd"><i class="fas fa-plug"></i><h3>Clés API & Services connectés</h3><p>Les clés sont chiffrées AES-256 en base</p></div>
    <div class="set-section-body">
        <?php
        $apiServices = [
            ['id'=>'anthropic',  'icon'=>'fa-robot',           'color'=>'#cc785c', 'label'=>'Anthropic Claude',    'desc'=>'Génération de contenu IA, articles, SEO sémantique',  'placeholder'=>'sk-ant-api03-...', 'url'=>'https://console.anthropic.com/'],
            ['id'=>'openai',     'icon'=>'fa-brain',           'color'=>'#10a37f', 'label'=>'OpenAI (DALL-E)',      'desc'=>'Génération d\'images pour les articles et pages',      'placeholder'=>'sk-proj-...', 'url'=>'https://platform.openai.com/'],
            ['id'=>'perplexity', 'icon'=>'fa-magnifying-glass','color'=>'#1e88e5', 'label'=>'Perplexity AI',        'desc'=>'Enrichissement par recherche web temps réel',          'placeholder'=>'pplx-...', 'url'=>'https://www.perplexity.ai/settings/api'],
            ['id'=>'google_analytics', 'icon'=>'fa-chart-line','color'=>'#e37400','label'=>'Google Analytics',     'desc'=>'Tracking des visites et comportements',                'placeholder'=>'G-XXXXXXXXXX ou UA-...', 'url'=>'https://analytics.google.com/'],
            ['id'=>'google_search_console','icon'=>'fa-search','color'=>'#4285f4','label'=>'Google Search Console','desc'=>'Indexation et performances SERP',                      'placeholder'=>'Clé JSON ou site: property', 'url'=>'https://search.google.com/search-console'],
            ['id'=>'facebook',   'icon'=>'fab fa-facebook',    'color'=>'#1877f2', 'label'=>'Facebook / Meta',      'desc'=>'Pixel, Ads API, publication automatique',              'placeholder'=>'EAAxxxxx...', 'url'=>'https://developers.facebook.com/'],
            ['id'=>'google_maps','icon'=>'fa-map-location-dot','color'=>'#34a853', 'label'=>'Google Maps',          'desc'=>'Cartes sur les pages secteurs et biens',               'placeholder'=>'AIzaSy...', 'url'=>'https://console.cloud.google.com/'],
        ];
        foreach ($apiServices as $svc):
            $configured = hasApi($svc['id']);
            $iconCls = str_starts_with($svc['icon'], 'fab ') ? $svc['icon'] : 'fas ' . $svc['icon'];
        ?>
        <div class="api-service">
            <div class="api-service-icon" style="background:<?= $svc['color'] ?>1a;color:<?= $svc['color'] ?>"><i class="<?= $iconCls ?>"></i></div>
            <div class="api-service-info">
                <div class="api-service-name"><?= $svc['label'] ?> <span class="api-badge <?= $configured?'configured':'missing' ?>"><?= $configured?'✓ Configuré':'Non configuré' ?></span></div>
                <div class="api-service-desc"><?= $svc['desc'] ?></div>
            </div>
            <a href="<?= $svc['url'] ?>" target="_blank" class="set-btn set-btn-s" style="font-size:10px;padding:5px 10px"><i class="fas fa-external-link-alt"></i></a>
            <button class="api-expand-btn" onclick="toggleApiForm('<?= $svc['id'] ?>')"><i class="fas fa-key"></i></button>
        </div>
        <div class="api-form" id="api-form-<?= $svc['id'] ?>">
            <form method="POST" action="?page=settings&tab=api" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
                <input type="hidden" name="action" value="save_api_key">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="service" value="<?= $svc['id'] ?>">
                <div class="set-field" style="flex:1;min-width:250px">
                    <label>Clé API <?= $svc['label'] ?></label>
                    <input class="set-input mono" type="password" name="api_key" placeholder="<?= htmlspecialchars($svc['placeholder']) ?>" autocomplete="new-password" required>
                </div>
                <button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Sauvegarder</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="set-section anim d1">
    <div class="set-section-hd"><i class="fas fa-webhook"></i><h3>Webhooks & Callbacks</h3></div>
    <div class="set-section-body">
        <form method="POST" action="?page=settings&tab=api">
        <input type="hidden" name="action" value="save_settings">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="set-grid">
            <div class="set-field"><label>Webhook nouveaux leads</label><input class="set-input mono" type="url" name="settings[webhook_new_lead]" value="<?= s('webhook_new_lead') ?>" placeholder="https://n8n.votresite.fr/webhook/..."></div>
            <div class="set-field"><label>Webhook nouvelles estimations</label><input class="set-input mono" type="url" name="settings[webhook_new_estimation]" value="<?= s('webhook_new_estimation') ?>" placeholder="https://..."></div>
        </div>
        <div class="set-actions"><button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Enregistrer les webhooks</button></div>
        </form>
    </div>
</div>
</div>

<!-- ══════════════════════════════════════
     ONGLET IA — CONFIGURATION
══════════════════════════════════════ -->
<div class="set-panel<?= $tab==='ai'?' active':'' ?>" id="tab-ai">
<form method="POST" action="?page=settings&tab=ai">
<input type="hidden" name="action" value="save_settings">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<div class="set-section anim">
    <div class="set-section-hd"><i class="fas fa-wand-magic-sparkles"></i><h3>Modèle IA par défaut</h3></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field">
                <label>Modèle Anthropic (génération)</label>
                <select class="set-input set-select" name="settings[ai_model_anthropic]">
                    <?php foreach (['claude-opus-4-6','claude-sonnet-4-6','claude-haiku-4-5-20251001'] as $m): ?>
                    <option value="<?= $m ?>" <?= s('ai_model_anthropic','claude-sonnet-4-6')===$m?'selected':'' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Sonnet = meilleur rapport qualité/coût pour les articles</small>
            </div>
            <div class="set-field">
                <label>Tokens max par génération</label>
                <input class="set-input mono" type="number" name="settings[ai_max_tokens]" value="<?= s('ai_max_tokens','4096') ?>" min="256" max="8192">
            </div>
            <div class="set-field">
                <label>Température (créativité)</label>
                <input class="set-input mono" type="number" step="0.1" name="settings[ai_temperature]" value="<?= s('ai_temperature','0.7') ?>" min="0" max="2">
                <small>0 = précis/factuel · 1 = créatif · 2 = très libre</small>
            </div>
            <div class="set-field">
                <label>Langue de génération</label>
                <select class="set-input set-select" name="settings[ai_language]">
                    <option value="fr" <?= s('ai_language','fr')==='fr'?'selected':'' ?>>Français</option>
                    <option value="en" <?= s('ai_language','fr')==='en'?'selected':'' ?>>English</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="set-section anim d1">
    <div class="set-section-hd"><i class="fas fa-user-circle"></i><h3>Contexte IA — Profil du conseiller</h3><p>Base de connaissance injectée dans tous les prompts</p></div>
    <div class="set-section-body">
        <div class="set-grid">
            <div class="set-field set-full">
                <label>Présentation du conseiller (pour l'IA)</label>
                <textarea class="set-input set-textarea" name="settings[ai_advisor_context]" rows="4" placeholder="Eduardo De Sul est conseiller immobilier indépendant affilié à eXp France, spécialisé sur le secteur de Bordeaux Métropole et notamment Blanquefort, Mérignac et les Chartrons…"><?= s('ai_advisor_context', $advisorCtx['description'] ?? '') ?></textarea>
                <small>Ce texte est injecté en début de chaque prompt IA pour personnaliser les contenus</small>
            </div>
            <div class="set-field set-full">
                <label>Spécialités & zones géographiques</label>
                <input class="set-input" type="text" name="settings[ai_specialties]" value="<?= s('ai_specialties', $advisorCtx['specialties'] ?? '') ?>" placeholder="Vente, achat, investissement locatif, première acquisition…">
            </div>
            <div class="set-field">
                <label>Ton éditorial</label>
                <select class="set-input set-select" name="settings[ai_tone]">
                    <?php foreach (['Professionnel & chaleureux','Expert & autoritaire','Pédagogique & accessible','Commercial & persuasif'] as $t): ?>
                    <option value="<?= $t ?>" <?= s('ai_tone','Professionnel & chaleureux')===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="set-field">
                <label>Méthode de copywriting</label>
                <select class="set-input set-select" name="settings[ai_copywriting_method]">
                    <option value="MERE" <?= s('ai_copywriting_method','MERE')==='MERE'?'selected':'' ?>>MERE (Miroir·Émotion·Réassurance·Exclusivité)</option>
                    <option value="AIDA" <?= s('ai_copywriting_method','MERE')==='AIDA'?'selected':'' ?>>AIDA (Attention·Intérêt·Désir·Action)</option>
                    <option value="PAS"  <?= s('ai_copywriting_method','MERE')==='PAS'?'selected':'' ?>>PAS (Problème·Agitation·Solution)</option>
                    <option value="STAR" <?= s('ai_copywriting_method','MERE')==='STAR'?'selected':'' ?>>STAR (Situation·Tâche·Action·Résultat)</option>
                </select>
            </div>
            <div class="set-field set-full">
                <label>Mots-clés à toujours inclure</label>
                <input class="set-input" type="text" name="settings[ai_keywords_include]" value="<?= s('ai_keywords_include') ?>" placeholder="Bordeaux, immobilier, conseiller, eXp…">
            </div>
            <div class="set-field set-full">
                <label>Mots/sujets à éviter</label>
                <input class="set-input" type="text" name="settings[ai_keywords_exclude]" value="<?= s('ai_keywords_exclude') ?>" placeholder="Concurrents, termes à proscrire…">
            </div>
        </div>
    </div>
</div>

<div class="set-section anim d2">
    <div class="set-section-hd"><i class="fas fa-toggle-on"></i><h3>Fonctionnalités IA activées</h3></div>
    <div class="set-section-body">
        <?php
        $aiFeatures = [
            ['ai_articles_enabled',    'Génération automatique d\'articles',        'Rédaction SEO via IA depuis le module Articles'],
            ['ai_pages_enabled',       'Génération de pages CMS',                   'Contenu des pages via IA depuis le Builder'],
            ['ai_secteurs_enabled',    'Génération de pages secteurs',              'Contenu des quartiers et secteurs géographiques'],
            ['ai_captures_enabled',    'Génération de pages de capture',            'Headlines, CTA et formulaires générés par IA'],
            ['ai_guide_local_enabled', 'Remplissage auto du Guide Local',           'Génération des fiches partenaires de proximité'],
            ['ai_seo_enabled',         'Analyse sémantique automatique',            'Scoring SEO et suggestions de mots-clés'],
            ['ai_images_enabled',      'Génération d\'images (DALL-E)',             'Images illustratives via OpenAI DALL-E'],
        ];
        foreach ($aiFeatures as [$key, $label, $help]):
        ?>
        <div class="toggle-row">
            <div class="toggle-row-info"><label><?= $label ?></label><small><?= $help ?></small></div>
            <label class="toggle-sw"><input type="checkbox" name="settings[<?= $key ?>]" value="1" <?= ($settings[$key]??'1')==='1'?'checked':'' ?>><span class="toggle-sw-slider"></span></label>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="set-actions"><button type="submit" class="set-btn set-btn-p"><i class="fas fa-save"></i> Enregistrer la configuration IA</button><a href="?page=advisor-context" class="set-btn set-btn-s"><i class="fas fa-user-circle"></i> Éditer le profil complet</a></div>
</form>
</div>

</div><!-- /set-wrap -->

<script>
// ─── Tabs ───
function switchTab(tab) {
    document.querySelectorAll('.set-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.set-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.querySelectorAll('.set-tab').forEach(t => {
        const href = t.getAttribute('href') || '';
        if (href.includes('tab=' + tab)) t.classList.add('active');
    });
    history.replaceState(null, '', '?page=settings&tab=' + tab);
}

// ─── Activer le bon onglet au chargement ───
const urlParams = new URLSearchParams(window.location.search);
const activeTab = urlParams.get('tab') || '<?= $tab ?>';
switchTab(activeTab);

// ─── API forms ───
function toggleApiForm(service) {
    const form = document.getElementById('api-form-' + service);
    if (!form) return;
    document.querySelectorAll('.api-form').forEach(f => { if (f !== form) f.classList.remove('open'); });
    form.classList.toggle('open');
    if (form.classList.contains('open')) form.querySelector('input[type=password]')?.focus();
}
</script>
