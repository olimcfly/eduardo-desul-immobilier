<?php
/**
 * Page publique de capture
 * ÉCOSYSTÈME IMMO LOCAL+
 * 
 * URL: /capture/{slug}
 * - Affiche la page de capture avec formulaire intégré
 * - Traite les soumissions → CRM (table leads)
 * - Redirige vers la page de remerciement
 */

// ── Récupérer le slug ──
$slug = $_GET['slug'] ?? '';
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

if (empty($slug)) {
    http_response_code(404);
    $notFound = dirname(__DIR__) . '/renderers/404.php';
    if (file_exists($notFound)) {
        include $notFound;
    } else {
        echo 'Page introuvable';
    }
    exit;
}

// ── Connexion BD ──
try {
    $rootPath = dirname(__DIR__, 2);
    $databasePath = $rootPath . '/config/database.php';
    $configPath = $rootPath . '/config/config.php';

    if (file_exists($databasePath)) {
        require_once $databasePath;
    }
    if (file_exists($configPath)) {
        require_once $configPath;
    }

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        if (function_exists('getDB')) {
            $pdo = getDB();
        } else {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
    }
} catch (Throwable $e) {
    http_response_code(500);
    die('Erreur serveur');
}

// ── Charger la page ──
$stmt = $pdo->prepare("SELECT * FROM captures WHERE slug = ? AND status = 'active'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    $notFound = dirname(__DIR__) . '/renderers/404.php';
    if (file_exists($notFound)) {
        include $notFound;
    } else {
        echo 'Page introuvable';
    }
    exit;
}

// ── Incrémenter les vues ──
$pdo->prepare("UPDATE captures SET vues = vues + 1 WHERE id = ?")->execute([$page['id']]);

// ══════════════════════════════════════════════════════════
// TRAITEMENT DU FORMULAIRE (POST)
// ══════════════════════════════════════════════════════════
$showMerci = isset($_GET['preview_merci']) && $_GET['preview_merci'] === '1';
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capture_submit'])) {
    $formConfig = json_decode($page['form_config'], true) ?: [];
    $leadData = [];
    
    // Récupérer tous les champs
    foreach ($formConfig as $field) {
        $name = $field['name'];
        $value = trim($_POST[$name] ?? '');
        
        // Validation champs obligatoires
        if (!empty($field['required']) && empty($value)) {
            $formError = 'Le champ "' . htmlspecialchars($field['label']) . '" est obligatoire.';
            break;
        }
        
        $leadData[$name] = $value;
    }

    if (empty($formError)) {
        try {
            // ── Extraire les infos principales pour le CRM ──
            $firstName = $leadData['prenom'] ?? $leadData['first_name'] ?? $leadData['firstname'] ?? '';
            $lastName = $leadData['nom'] ?? $leadData['last_name'] ?? $leadData['lastname'] ?? $leadData['name'] ?? '';
            $email = $leadData['email'] ?? $leadData['mail'] ?? '';
            $phone = $leadData['telephone'] ?? $leadData['phone'] ?? $leadData['tel'] ?? '';

            // ── Vérifier si le lead existe déjà (par email) ──
            $existingLead = null;
            if (!empty($email)) {
                $checkStmt = $pdo->prepare("SELECT id FROM leads WHERE email = ?");
                $checkStmt->execute([$email]);
                $existingLead = $checkStmt->fetch();
            }

            if ($existingLead) {
                // Mettre à jour le lead existant
                $updateStmt = $pdo->prepare("UPDATE leads SET 
                    first_name = COALESCE(NULLIF(?, ''), first_name),
                    last_name = COALESCE(NULLIF(?, ''), last_name),
                    phone = COALESCE(NULLIF(?, ''), phone),
                    source = ?,
                    tags = CONCAT(COALESCE(tags, ''), ',', ?),
                    custom_fields = ?,
                    updated_at = NOW()
                    WHERE id = ?");
                $updateStmt->execute([
                    $firstName, $lastName, $phone,
                    $page['lead_source'],
                    $page['lead_tags'] ?? '',
                    json_encode($leadData, JSON_UNESCAPED_UNICODE),
                    $existingLead['id']
                ]);
                $leadId = $existingLead['id'];
            } else {
                // Créer un nouveau lead
                // Vérifier la structure de la table leads
                $columns = $pdo->query("SHOW COLUMNS FROM leads")->fetchAll(PDO::FETCH_COLUMN);
                
                if (in_array('first_name', $columns)) {
                    // Structure CRM avec first_name/last_name
                    $insertStmt = $pdo->prepare("INSERT INTO leads 
                        (first_name, last_name, email, phone, source, tags, custom_fields, status, temperature, score, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'nouveau', 'froid', 5, NOW())");
                    $insertStmt->execute([
                        $firstName, $lastName, $email, $phone,
                        $page['lead_source'],
                        $page['lead_tags'] ?? '',
                        json_encode($leadData, JSON_UNESCAPED_UNICODE)
                    ]);
                } elseif (in_array('nom', $columns)) {
                    // Structure alternative avec nom/prenom
                    $insertStmt = $pdo->prepare("INSERT INTO leads 
                        (nom, prenom, email, telephone, source, tags, data_json, statut, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'nouveau', NOW())");
                    $insertStmt->execute([
                        $lastName, $firstName, $email, $phone,
                        $page['lead_source'],
                        $page['lead_tags'] ?? '',
                        json_encode($leadData, JSON_UNESCAPED_UNICODE)
                    ]);
                } else {
                    // Fallback: insertion basique
                    $insertStmt = $pdo->prepare("INSERT INTO leads (email, source, created_at) VALUES (?, ?, NOW())");
                    $insertStmt->execute([$email, $page['lead_source']]);
                }
                $leadId = $pdo->lastInsertId();
            }

            // ── Trigger system : assignation automatique de séquence ──
            try {
                require_once $rootPath . '/includes/classes/SequenceTriggerService.php';
                $triggerService = new SequenceTriggerService($pdo);
                $triggerService->processLeadEvent('capture_submitted', (int)$leadId, [
                    'capture_page_id' => (int)($page['id'] ?? 0),
                    'capture_slug' => (string)($page['slug'] ?? ''),
                    'source' => (string)($page['lead_source'] ?? ''),
                ]);
            } catch (Throwable $e) {
                error_log('Trigger system error: ' . $e->getMessage());
            }

            // ── Log d'interaction ──
            try {
                $logStmt = $pdo->prepare("INSERT INTO lead_interactions 
                    (lead_id, type, description, metadata, interaction_date)
                    VALUES (?, 'formulaire', ?, ?, NOW())");
                $logStmt->execute([
                    $leadId,
                    'Soumission page de capture: ' . $page['titre'],
                    json_encode([
                        'capture_page_id' => $page['id'],
                        'capture_slug' => $page['slug'],
                        'source' => $page['lead_source'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ], JSON_UNESCAPED_UNICODE)
                ]);
            } catch (Exception $e) {
                // Table interactions peut ne pas exister, on continue
            }

            // ── Créer un RDV automatique si source pertinente ──
            try {
                $rdvSources = ['estimation', 'visite', 'rdv', 'valuation', 'contact'];
                $leadSource = strtolower($page['lead_source'] ?? '');
                $shouldCreateRdv = false;
                foreach ($rdvSources as $src) {
                    if (strpos($leadSource, $src) !== false) { $shouldCreateRdv = true; break; }
                }
                if ($shouldCreateRdv) {
                    // Vérifier que la table appointments existe
                    if ($pdo->query("SHOW TABLES LIKE 'appointments'")->rowCount() > 0) {
                        $rdvTitle = ucfirst($leadSource) . ' - ' . trim($firstName . ' ' . $lastName);
                        $rdvType = 'estimation';
                        if (strpos($leadSource, 'visite') !== false) $rdvType = 'visite';
                        elseif (strpos($leadSource, 'contact') !== false) $rdvType = 'autre';

                        // Vérifier les colonnes disponibles
                        $apptCols = $pdo->query("SHOW COLUMNS FROM appointments")->fetchAll(PDO::FETCH_COLUMN);
                        if (in_array('start_datetime', $apptCols)) {
                            $rdvDate = !empty($leadData['date']) ? $leadData['date'] : date('Y-m-d', strtotime('+1 day'));
                            $rdvTime = !empty($leadData['heure']) ? $leadData['heure'] : '09:00';
                            $startDt = $rdvDate . ' ' . $rdvTime . ':00';
                            $endDt = date('Y-m-d H:i:s', strtotime($startDt . ' +1 hour'));

                            $rdvStmt = $pdo->prepare("INSERT INTO appointments
                                (title, type, start_datetime, end_datetime, lead_id, status, notes, created_at)
                                VALUES (?, ?, ?, ?, ?, 'scheduled', ?, NOW())");
                            $rdvStmt->execute([
                                $rdvTitle, $rdvType, $startDt, $endDt, $leadId,
                                'Demande via page de capture: ' . ($page['titre'] ?? $slug)
                            ]);
                        } elseif (in_array('start_at', $apptCols)) {
                            $rdvDate = !empty($leadData['date']) ? $leadData['date'] : date('Y-m-d', strtotime('+1 day'));
                            $rdvTime = !empty($leadData['heure']) ? $leadData['heure'] : '09:00';
                            $startDt = $rdvDate . ' ' . $rdvTime . ':00';
                            $endDt = date('Y-m-d H:i:s', strtotime($startDt . ' +1 hour'));

                            $rdvStmt = $pdo->prepare("INSERT INTO appointments
                                (title, type, start_at, end_at, lead_id, status, notes, created_at)
                                VALUES (?, ?, ?, ?, ?, 'scheduled', ?, NOW())");
                            $rdvStmt->execute([
                                $rdvTitle, $rdvType, $startDt, $endDt, $leadId,
                                'Demande via page de capture: ' . ($page['titre'] ?? $slug)
                            ]);
                        }
                    }
                }
            } catch (Exception $e) {
                // Ne pas bloquer la soumission si la création du RDV échoue
                error_log('[Capture→RDV] ' . $e->getMessage());
            }

            // ── Incrémenter le compteur de soumissions ──
            $pdo->prepare("UPDATE captures SET
                conversions = conversions + 1,
                taux_conversion = ROUND((conversions + 1) / GREATEST(vues, 1) * 100, 2)
                WHERE id = ?")->execute([$page['id']]);

            $showMerci = true;

        } catch (PDOException $e) {
            error_log('Capture form error: ' . $e->getMessage());
            $formError = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

// ══════════════════════════════════════════════════════════
// AFFICHAGE
// ══════════════════════════════════════════════════════════

// Si redirection externe configurée
if ($showMerci && !empty($page['redirect_url'])) {
    header('Location: ' . $page['redirect_url']);
    exit;
}

// ── Générer le HTML du formulaire ──
function buildCaptureForm($page) {
    $formConfig = json_decode($page['form_config'], true) ?: [];
    $btnText = htmlspecialchars($page['form_button_text'] ?? 'Envoyer');
    $btnColor = htmlspecialchars($page['form_button_color'] ?? '#667eea');
    $formTitle = htmlspecialchars($page['form_titre'] ?? '');

    $html = '<div style="max-width:500px;margin:0 auto;padding:30px;background:#fff;border-radius:16px;box-shadow:0 4px 30px rgba(0,0,0,0.08);">';
    
    if ($formTitle) {
        $html .= '<h3 style="margin-bottom:20px;font-size:1.3rem;color:#1a1a2e;text-align:center;">' . $formTitle . '</h3>';
    }

    $html .= '<form method="POST" action="" id="captureForm">';
    $html .= '<input type="hidden" name="capture_submit" value="1">';

    foreach ($formConfig as $field) {
        $name = htmlspecialchars($field['name']);
        $label = htmlspecialchars($field['label']);
        $type = $field['type'] ?? 'text';
        $required = !empty($field['required']) ? 'required' : '';
        $placeholder = htmlspecialchars($field['placeholder'] ?? '');
        $requiredStar = !empty($field['required']) ? ' <span style="color:#ef4444">*</span>' : '';

        if ($type === 'hidden') {
            $html .= "<input type='hidden' name='{$name}' value='{$placeholder}'>";
            continue;
        }

        $html .= '<div style="margin-bottom:16px;">';

        if ($type !== 'checkbox') {
            $html .= "<label style='display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:5px;'>{$label}{$requiredStar}</label>";
        }

        $inputStyle = "width:100%;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;font-size:0.95rem;color:#1a1a2e;background:#fafbfc;transition:border-color 0.2s;box-sizing:border-box;font-family:inherit;";

        switch ($type) {
            case 'textarea':
                $html .= "<textarea name='{$name}' placeholder='{$placeholder}' {$required} style='{$inputStyle}min-height:80px;resize:vertical;'></textarea>";
                break;
            case 'select':
                $html .= "<select name='{$name}' {$required} style='{$inputStyle}'><option value=''>Choisir...</option>";
                foreach (($field['options'] ?? []) as $opt) {
                    $optHtml = htmlspecialchars($opt);
                    $html .= "<option value='{$optHtml}'>{$optHtml}</option>";
                }
                $html .= "</select>";
                break;
            case 'checkbox':
                $html .= "<label style='display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:0.9rem;color:#374151;'><input type='checkbox' name='{$name}' value='1' {$required} style='margin-top:3px;'> {$label}{$requiredStar}</label>";
                break;
            default:
                $html .= "<input type='{$type}' name='{$name}' placeholder='{$placeholder}' {$required} style='{$inputStyle}'>";
        }

        $html .= '</div>';
    }

    $html .= "<button type='submit' style='width:100%;padding:14px;background:{$btnColor};color:#fff;border:none;border-radius:10px;font-size:1.05rem;font-weight:700;cursor:pointer;margin-top:8px;transition:all 0.3s;'>{$btnText}</button>";
    $html .= '<p style="text-align:center;font-size:0.75rem;color:#9ca3af;margin-top:12px;">Vos données sont protégées et ne seront jamais partagées.</p>';
    $html .= '</form></div>';

    // Focus styles
    $html .= "<style>
        #captureForm input:focus, #captureForm textarea:focus, #captureForm select:focus {
            outline:none; border-color:{$btnColor}; box-shadow:0 0 0 3px " . $btnColor . "22;
        }
        #captureForm button:hover { opacity:0.9; transform:translateY(-1px); box-shadow:0 4px 15px " . $btnColor . "44; }
    </style>";

    return $html;
}

function buildDefaultCaptureTemplate(array $page): string {
    $template = $page['template'] ?? 'split';
    $title = htmlspecialchars($page['headline'] ?: ($page['titre'] ?? 'Recevez votre ressource'));
    $subtitle = nl2br(htmlspecialchars($page['sous_titre'] ?? ''));
    $desc = nl2br(htmlspecialchars($page['description'] ?? ''));
    $bg = htmlspecialchars($page['form_button_color'] ?? '#667eea');
    $tag = htmlspecialchars(strtoupper($page['type'] ?? 'CAPTURE'));

    $leftContent = '
        <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:' . $bg . '1a;color:' . $bg . ';font-size:12px;font-weight:700;letter-spacing:.04em;margin-bottom:14px">' . $tag . '</div>
        <h1 style="font-size:clamp(28px,4vw,44px);line-height:1.15;margin-bottom:14px;color:#111827;">' . $title . '</h1>
        ' . ($subtitle ? '<p style="font-size:18px;line-height:1.45;color:#374151;margin-bottom:14px;">' . $subtitle . '</p>' : '') . '
        ' . ($desc ? '<p style="font-size:15px;line-height:1.7;color:#6b7280;max-width:700px;">' . $desc . '</p>' : '') . '
    ';

    $formBlock = '<div class="capture-form-slot">{{FORMULAIRE}}</div>';

    if ($template === 'hero') {
        return '<section style="min-height:100vh;background:linear-gradient(135deg,#0f172a,#1e293b);padding:80px 20px;display:flex;align-items:center">
            <div style="max-width:1150px;margin:0 auto;width:100%;display:grid;grid-template-columns:1.1fr .9fr;gap:32px;align-items:center">
                <div style="color:#fff">
                    <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#ffffff22;color:#fff;font-size:12px;font-weight:700;letter-spacing:.04em;margin-bottom:14px">' . $tag . '</div>
                    <h1 style="font-size:clamp(30px,4.3vw,52px);line-height:1.08;margin-bottom:16px">' . $title . '</h1>
                    ' . ($subtitle ? '<p style="font-size:20px;line-height:1.45;color:#cbd5e1;margin-bottom:10px">' . $subtitle . '</p>' : '') . '
                    ' . ($desc ? '<p style="font-size:15px;line-height:1.7;color:#94a3b8;max-width:680px">' . $desc . '</p>' : '') . '
                </div>
                ' . $formBlock . '
            </div>
        </section>';
    }

    if ($template === 'minimal') {
        return '<section style="min-height:100vh;background:#f8fafc;padding:48px 16px;display:flex;align-items:center">
            <div style="max-width:780px;margin:0 auto;width:100%;text-align:center">
                ' . $leftContent . '
                <div style="margin-top:20px">' . $formBlock . '</div>
            </div>
        </section>';
    }

    if ($template === 'simple') {
        return '<section style="background:#fff;padding:64px 20px">
            <div style="max-width:860px;margin:0 auto;text-align:center">
                ' . $leftContent . '
                <div style="margin-top:22px">' . $formBlock . '</div>
            </div>
        </section>';
    }

    if ($template === 'editor') {
        return '<section class="capture-editor-template" style="min-height:100vh;background:#eef1f7;padding:40px 16px">
            <div style="max-width:1180px;margin:0 auto">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px">
                    <div style="font-size:13px;color:#64748b">Page de capture</div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span style="padding:7px 12px;border-radius:10px;background:#fff;border:1px solid #dbe2ef;font-size:12px;color:#475569">Sécurisé SSL</span>
                        <span style="padding:7px 12px;border-radius:10px;background:#e0e7ff;color:#4338ca;font-size:12px;font-weight:700">Accès instantané</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:20px;align-items:start">
                    <article style="background:#fff;border:1px solid #dbe2ef;border-radius:18px;padding:28px;box-shadow:0 18px 40px rgba(15,23,42,.07)">
                        ' . $leftContent . '
                        <div style="margin-top:22px;display:grid;gap:10px">
                            <div style="display:flex;gap:9px;align-items:flex-start">
                                <span style="margin-top:3px;color:' . $bg . '">✔</span>
                                <span style="font-size:14px;color:#475569;line-height:1.6">Conseils actionnables à appliquer immédiatement.</span>
                            </div>
                            <div style="display:flex;gap:9px;align-items:flex-start">
                                <span style="margin-top:3px;color:' . $bg . '">✔</span>
                                <span style="font-size:14px;color:#475569;line-height:1.6">Méthode claire utilisée sur le terrain immobilier.</span>
                            </div>
                            <div style="display:flex;gap:9px;align-items:flex-start">
                                <span style="margin-top:3px;color:' . $bg . '">✔</span>
                                <span style="font-size:14px;color:#475569;line-height:1.6">Format court, lisible et prêt à l’emploi.</span>
                            </div>
                        </div>
                    </article>

                    <aside style="position:sticky;top:18px">
                        <div style="background:#fff;border:1px solid #dbe2ef;border-radius:18px;padding:18px;box-shadow:0 12px 30px rgba(15,23,42,.08)">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Votre ressource</div>
                            ' . $formBlock . '
                        </div>
                    </aside>
                </div>
            </div>
            <style>
                @media (max-width: 920px) {
                    .capture-editor-template > div > div:nth-child(2) { grid-template-columns: 1fr !important; }
                    .capture-editor-template aside { position: static !important; }
                }
            </style>
        </section>';
    }

    // split (défaut)
    return '<section style="background:#f8fafc;padding:70px 20px">
        <div style="max-width:1150px;margin:0 auto;display:grid;grid-template-columns:1.1fr .9fr;gap:34px;align-items:start">
            <div>' . $leftContent . '</div>
            <div>' . $formBlock . '</div>
        </div>
    </section>';
}

// ── Choisir le HTML à afficher ──
if ($showMerci) {
    $displayHtml = $page['html_merci'] ?? '';
    if (empty($displayHtml)) {
        // Page de remerciement par défaut
        $displayHtml = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
        <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;padding:20px}
        .card{background:#fff;border-radius:20px;padding:50px 30px;max-width:500px;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.06)}
        .icon{font-size:3.5rem;margin-bottom:16px}h1{font-size:1.8rem;margin-bottom:12px;color:#1a1a2e}p{color:#666;font-size:1rem;line-height:1.6}
        </style></head><body><div class="card"><div class="icon">🎉</div><h1>Merci !</h1><p>Votre demande a bien été envoyée. Nous vous recontacterons très vite.</p></div></body></html>';
    }
} else {
    $storedCaptureHtml = (string)($page['html_capture'] ?? ($page['contenu'] ?? ''));
    $displayHtml = $storedCaptureHtml;
    if (trim($displayHtml) === '') {
        $displayHtml = buildDefaultCaptureTemplate($page);
    }
    // Injecter le formulaire à la place de {{FORMULAIRE}}
    $formHtml = buildCaptureForm($page);
    
    if (!empty($formError)) {
        $errorHtml = '<div style="max-width:500px;margin:0 auto 16px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:0.9rem;text-align:center;">' . htmlspecialchars($formError) . '</div>';
        $formHtml = $errorHtml . $formHtml;
    }
    
    $displayHtml = str_replace('{{FORMULAIRE}}', $formHtml, $displayHtml);
    
    // Si pas de {{FORMULAIRE}} trouvé, ajouter à la fin
    if (strpos($storedCaptureHtml, '{{FORMULAIRE}}') === false) {
        $displayHtml .= '<div style="padding:40px 20px;">' . $formHtml . '</div>';
    }
}

// ── Meta tags ──
$metaTitle = $page['meta_title'] ?: $page['titre'];
$metaDesc = $page['meta_description'] ?: $page['sous_titre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($metaTitle) ?></title>
    <?php if ($metaDesc): ?><meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
    <meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
    <?php if ($metaDesc): ?><meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
    <?php if ($page['og_image']): ?><meta property="og:image" content="<?= htmlspecialchars($page['og_image']) ?>"><?php endif; ?>
    <meta name="robots" content="index, follow">
</head>
<body>
<?= $displayHtml ?>
</body>
</html>
