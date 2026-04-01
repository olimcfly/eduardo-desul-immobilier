<?php
/**
 * ============================================================
 *  AI DISPATCHER — /admin/api/ai/generate.php
 *  EcosystèmeImmo LOCAL+
 *
 *  Source unique de vérité :
 *    advisor_context  → profil IA complet du conseiller
 *    admin_settings   → identité (nom, email, phone...)
 *    websites         → design (couleurs, font, domaine)
 *    ai_settings      → clés API + paramètres IA
 *
 *  Actions :
 *    builder.generate / builder.improve / builder.section
 *    articles.generate / articles.improve / articles.meta
 *    articles.faq / articles.outline / articles.keywords
 *    articles.rewrite / articles.excerpt
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// ── Bootstrap ────────────────────────────────────────────────
$cfgCandidates = [
    __DIR__ . '/../../config/config.php',
    __DIR__ . '/../../../config/config.php',
    __DIR__ . '/../../../../config/config.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/config.php',
    $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php',
];
foreach ($cfgCandidates as $p) {
    if (file_exists($p)) { require_once $p; break; }
}
if (session_status() === PHP_SESSION_NONE) session_start();

// ── PDO ──────────────────────────────────────────────────────
if (!isset($pdo)) {
    try {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (Throwable $e) {
        // PDO sera null — les fonctions gracent avec des valeurs vides
    }
}

// ── Helpers JSON ─────────────────────────────────────────────
function ok(array $d): never  { echo json_encode(['success' => true]  + $d); exit; }
function err(string $m): never { echo json_encode(['success' => false, 'error' => $m]); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') err('Méthode non autorisée');
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) err('Payload JSON invalide');

if (!empty($_SESSION['auth_csrf_token'])) {
    if (!hash_equals($_SESSION['auth_csrf_token'], $body['csrf_token'] ?? ''))
        err('Token CSRF invalide');
}

$module = strtolower(trim($body['module'] ?? ''));
$action = strtolower(trim($body['action'] ?? ''));
if (!$module || !$action) err('module et action requis');

// ════════════════════════════════════════════════════════════
//  SOURCE UNIQUE DE VÉRITÉ — Lecture DB
// ════════════════════════════════════════════════════════════

/**
 * Lit une table key/value et retourne un tableau associatif.
 * Supporte les structures : key_name/value, setting_key/setting_value, field_key/field_value
 */
function readKV(string $table, string $keyCol, string $valCol, ?string $where = null): array {
    global $pdo;
    if (!$pdo) return [];
    try {
        $sql = "SELECT `{$keyCol}`, `{$valCol}` FROM `{$table}`" . ($where ? " WHERE {$where}" : "");
        return $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

// ── 1. Profil IA conseiller (advisor_context) ────────────────
$advisorCtx = readKV('advisor_context', 'field_key', 'field_value');

// ── 2. Identité admin (admin_settings) ───────────────────────
$adminSet   = readKV('admin_settings', 'setting_key', 'setting_value');

// ── 3. Design du site (websites, 1ère ligne) ─────────────────
$websiteDesign = [];
try {
    if ($pdo) {
        $row = $pdo->query("SELECT primary_color, secondary_color, font_family, domain FROM websites LIMIT 1")->fetch();
        if ($row) $websiteDesign = $row;
    }
} catch (Throwable $e) {}

// ── 4. Paramètres IA (ai_settings) ───────────────────────────
$aiSet = readKV('ai_settings', 'setting_key', 'setting_value');

// ── Résolution clés API ───────────────────────────────────────
function resolveKey(string $settingKey, string $constName): string {
    global $aiSet;
    $v = $aiSet[$settingKey] ?? '';
    if ($v) return $v;
    if (defined($constName)) return constant($constName);
    return getenv($constName) ?: '';
}

$anthropicKey = resolveKey('anthropic_api_key', 'ANTHROPIC_API_KEY');
$openaiKey    = resolveKey('openai_api_key',    'OPENAI_API_KEY');

// Modèles — priorité : ai_settings DB › valeur par défaut
$aiModelAnthropic = $aiSet['claude_model']          ?? $aiSet['ai_model_anthropic'] ?? 'claude-opus-4-5';
$aiModelOpenai    = $aiSet['openai_model']           ?? $aiSet['ai_model_openai']    ?? 'gpt-4o-mini';
$aiMaxTokens      = (int)($aiSet['max_tokens_per_generation'] ?? $aiSet['ai_max_tokens'] ?? 3000);

$provider = '';
if (!empty($anthropicKey)) $provider = 'anthropic';
elseif (!empty($openaiKey)) $provider = 'openai';
else err('Aucune clé API IA configurée. Allez dans Configuration › API & Intégrations.');

// ════════════════════════════════════════════════════════════
//  CONSTRUCTION DU CONTEXTE IA UNIFIÉ
// ════════════════════════════════════════════════════════════

/**
 * Construit le bloc contexte conseiller pour les prompts IA.
 * Fusionne advisor_context + admin_settings + ai_settings brand_*.
 * Priorise : advisor_context › admin_settings › ai_settings brand_*
 */
function buildAdvisorContext(): string {
    global $advisorCtx, $adminSet, $aiSet;

    // Nom : advisor_context › admin_settings › ai_settings
    $name    = $advisorCtx['advisor_name']      ?: ($adminSet['agent_name']     ?: ($aiSet['brand_name']     ?: 'Le conseiller'));
    $city    = $advisorCtx['advisor_city']      ?: ($adminSet['agent_city']     ?: ($aiSet['brand_location'] ?: 'Bordeaux'));
    $network = $advisorCtx['advisor_network']   ?: ($adminSet['agent_network']  ?: 'réseau immobilier');
    $phone   = $advisorCtx['advisor_phone']     ?: ($adminSet['agent_phone']    ?: '');
    $email   = $advisorCtx['advisor_email']     ?: ($adminSet['agent_email']    ?: '');
    $zone    = $advisorCtx['advisor_zone']      ?: $city;
    $card    = $advisorCtx['advisor_card']      ?: ($adminSet['agent_rsac']     ?: '');
    $specs   = $advisorCtx['specialties']       ?: '';
    $services= $advisorCtx['services']          ?: '';
    $diffs   = $advisorCtx['differentiators']   ?: '';
    $style   = $advisorCtx['advisor_style']     ?: ($aiSet['brand_context']    ?: '');
    $market  = $advisorCtx['market_overview']   ?: '';
    $prices  = $advisorCtx['price_ranges']      ?: '';
    $trends  = $advisorCtx['market_trends']     ?: '';
    $persona1= $advisorCtx['persona_vendeur']   ?: '';
    $persona2= $advisorCtx['persona_acheteur']  ?: '';
    $tone    = $advisorCtx['tone_voice']        ?: $advisorCtx['writing_style'] ?: 'professionnel et rassurant';
    $keywords= $advisorCtx['seo_keywords']      ?: '';
    $mere    = $advisorCtx['mere_method']       ?: '';

    $lines = ["=== PROFIL CONSEILLER ==="];
    $lines[] = "Nom : {$name}";
    $lines[] = "Ville : {$city} | Réseau : {$network}";
    if ($zone)    $lines[] = "Zone d'intervention : {$zone}";
    if ($phone)   $lines[] = "Téléphone : {$phone}";
    if ($email)   $lines[] = "Email : {$email}";
    if ($card)    $lines[] = "Carte CPI : {$card}";
    if ($specs)   $lines[] = "\nSpécialités : {$specs}";
    if ($services)$lines[] = "Services : {$services}";
    if ($diffs)   $lines[] = "Différenciateurs : {$diffs}";
    if ($style)   $lines[] = "Style de communication : {$style}";
    if ($tone)    $lines[] = "Ton : {$tone}";

    if ($market || $prices || $trends) {
        $lines[] = "\n=== MARCHÉ LOCAL ===";
        if ($market) $lines[] = $market;
        if ($prices) $lines[] = "Prix : {$prices}";
        if ($trends) $lines[] = "Tendances : {$trends}";
    }

    if ($persona1 || $persona2) {
        $lines[] = "\n=== PERSONAS CLIENTS ===";
        if ($persona1) $lines[] = "Vendeurs : {$persona1}";
        if ($persona2) $lines[] = "Acheteurs : {$persona2}";
    }

    if ($keywords) $lines[] = "\nMots-clés SEO prioritaires : {$keywords}";
    if ($mere)     $lines[] = "Méthode MERE : {$mere}";

    return implode("\n", array_filter($lines));
}

/**
 * Construit le bloc design system depuis websites table.
 * Fallback sur les valeurs connues d'Eduardo si DB vide.
 */
function buildDesignSystem(): string {
    global $websiteDesign, $advisorCtx, $adminSet;

    $primary   = $websiteDesign['primary_color']   ?: '#1a4d7a';
    $secondary = $websiteDesign['secondary_color'] ?: '#d4a574';
    $font      = $websiteDesign['font_family']     ?: 'Playfair Display (titres), DM Sans (corps)';
    $domain    = $websiteDesign['domain']          ?: '';

    // Couleurs supplémentaires si en DB
    $bg        = '#f9f6f3';  // beige Eduardo — à lire depuis site_design si dispo
    $radius    = '12px';

    $lines = [
        "=== DESIGN SYSTEM ===",
        "Couleur primaire : {$primary}",
        "Couleur accent : {$secondary}",
        "Fond : {$bg}",
        "Border-radius : {$radius}",
        "Typographie : {$font}",
    ];
    if ($domain) $lines[] = "Domaine : {$domain}";

    // Variables CSS à utiliser dans le code généré
    $lines[] = "\nVariables CSS :root à définir :";
    $lines[] = "  --primary: {$primary}; --accent: {$secondary}; --bg: {$bg};";
    $lines[] = "  --text: #2c2c2c; --radius: {$radius};";

    return implode("\n", $lines);
}

// ── callLLM ─────────────────────────────────────────────────
function callLLM(string $sys, string $usr, int $max = 3000): string {
    global $provider, $anthropicKey, $openaiKey, $aiModelAnthropic, $aiModelOpenai;
    $callAnthropic = function() use ($sys, $usr, $max, $anthropicKey, $aiModelAnthropic): string {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: '.$anthropicKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => $aiModelAnthropic,
                'max_tokens' => $max,
                'system'     => $sys,
                'messages'   => [['role'=>'user','content'=>$usr]]
            ]),
            CURLOPT_TIMEOUT => 120, CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($cerr) throw new Exception('cURL: '.$cerr);
        if ($code !== 200) throw new Exception('Anthropic HTTP '.$code.': '.substr($resp,0,300));
        $d = json_decode($resp, true);
        return $d['content'][0]['text'] ?? throw new Exception('Réponse Anthropic inattendue');
    };

    $callOpenAI = function() use ($sys, $usr, $max, $openaiKey, $aiModelOpenai): string {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$openaiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'      => $aiModelOpenai,
                'max_tokens' => $max,
                'messages'   => [
                    ['role'=>'system','content'=>$sys],
                    ['role'=>'user','content'=>$usr]
                ]
            ]),
            CURLOPT_TIMEOUT => 120, CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($cerr) throw new Exception('cURL: '.$cerr);
        if ($code !== 200) throw new Exception('OpenAI HTTP '.$code.': '.substr($resp,0,300));
        $d = json_decode($resp, true);
        return $d['choices'][0]['message']['content'] ?? throw new Exception('Réponse OpenAI inattendue');
    };

    if ($provider === 'anthropic') {
        try {
            return $callAnthropic();
        } catch (Throwable $e) {
            if (!empty($openaiKey)) {
                error_log('[AI fallback] Anthropic indisponible, tentative OpenAI: '.$e->getMessage());
                return $callOpenAI();
            }
            throw $e;
        }
    }

    return $callOpenAI();
}

// ── extractJson ─────────────────────────────────────────────
function extractJson(string $t): array {
    $t = preg_replace('/^```(?:json)?\s*/m', '', $t);
    $t = preg_replace('/```\s*$/m', '', $t);
    $t = trim($t);
    $s1 = strpos($t, '{'); $s2 = strpos($t, '[');
    if ($s1 === false && $s2 === false) return [];
    if ($s1 === false) $s = $s2;
    elseif ($s2 === false) $s = $s1;
    else $s = min($s1, $s2);
    $e1 = strrpos($t, '}'); $e2 = strrpos($t, ']');
    $e = max($e1 !== false ? $e1 : -1, $e2 !== false ? $e2 : -1);
    if ($e < $s) return [];
    $d = json_decode(substr($t, $s, $e - $s + 1), true);
    return is_array($d) ? $d : [];
}

// ── makeSlug ────────────────────────────────────────────────
function makeSlug(string $t, int $n = 6): string {
    $stops = ['le','la','les','de','du','des','un','une','en','et','ou','a','au','aux','ce','cette','ces','son','sa','ses','mon','ma','mes','pour','par','sur','avec','dans','qui','que','dont','est','sont','ne','pas','se','si'];
    $t = mb_strtolower($t, 'UTF-8');
    $t = strtr($t, ['à'=>'a','â'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i','ô'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','æ'=>'ae','œ'=>'oe']);
    $t = preg_replace('/[^a-z0-9\s]/u', '', $t);
    $w = array_filter(explode(' ', $t), fn($x) => strlen($x) > 1 && !in_array($x, $stops));
    return implode('-', array_slice(array_values($w), 0, $n));
}

// ════════════════════════════════════════════════════════════
//  DISPATCH DES ACTIONS
// ════════════════════════════════════════════════════════════
try { switch ($module.'.'.$action) {

// ══════════════════════════════════════════════════════════
// BUILDER ACTIONS
// ══════════════════════════════════════════════════════════

case 'builder.generate': {
    $prompt      = trim($body['prompt']      ?? '');
    $page_type   = trim($body['page_type']   ?? 'page');
    $page_title  = trim($body['page_title']  ?? '');
    $page_slug   = trim($body['page_slug']   ?? '');

    // Contexte passé par l'editor (optionnel — si absent on lit la DB)
    $ctxOverride    = trim($body['context']     ?? '');
    $styleOverride  = trim($body['style_guide'] ?? '');

    if (!$prompt && !$page_title) err('Prompt ou titre de page requis');

    // Source unique : DB sauf si l'editor force un override
    $advisorBlock = $ctxOverride   ?: buildAdvisorContext();
    $designBlock  = $styleOverride ?: buildDesignSystem();

    $typeInstructions = match($page_type) {
        'article'  => "Page ARTICLE de blog immobilier. Structure : header avec image hero, h1 accrocheur, introduction engageante (méthode MERE), sections h2/h3 avec contenu riche, listes, encadrés info, CTA estimation/contact en bas.",
        'secteur'  => "Page SECTEUR/QUARTIER immobilier. Objectif : page locale utile pour lecteurs humains et SEO local Google. Contraintes : ne jamais inventer de données, ne jamais donner de chiffres précis non confirmés, style naturel/professionnel/humain, éviter ton robotique et bourrage SEO, adapter au couple ville-secteur + intention + données locales fournies. Structure obligatoire : 1. Hero 2. Vue d’ensemble du secteur 3. Pourquoi ce secteur attire 4. Marché immobilier local 5. À qui s’adresse ce secteur 6. Vendre dans ce secteur 7. Acheter dans ce secteur 8. Regard d’expert 9. FAQ 10. CTA final.",
        'guide'    => "Page GUIDE LOCAL. Structure : intro locale chaleureuse, sections thématiques (vie pratique, commerces, éducation, transports, culture, loisirs), carte placeholder, citation habitant, CTA.",
        'capture'  => "PAGE DE CAPTURE LEAD MAGNET. Design épuré centré sur la conversion. Proposition de valeur forte, 3 bénéfices max, formulaire centré (prénom, email, téléphone), preuve sociale, mention RGPD. Zéro menu, zéro distraction.",
        'landing'  => "LANDING PAGE conversion. Hero puissant avec CTA visible, bénéfices 3 colonnes, 3 étapes comment ça marche, 3 témoignages clients, FAQ 5 questions, CTA final urgent.",
        default    => "Page CMS standard. Structure propre, hiérarchie visuelle claire (h1/h2/h3), sections informatives, CTA de contact en bas.",
    };

    $sys = "Tu es un expert développeur frontend senior et copywriter immobilier pour conseillers indépendants français.\n"
         . "Tu crées des pages web de haute qualité visuelle et éditoriale, optimisées pour la conversion et le SEO local.\n"
         . "Tu utilises la méthode MERE (Miroir, Émotion, Réassurance, Exclusivité) pour le copywriting.\n"
         . "Tu maîtrises HTML5 sémantique, CSS3 moderne avec variables CSS et Grid/Flexbox, JavaScript vanilla ES6+.\n\n"
         . "RÈGLES ABSOLUES :\n"
         . "1. Retourne UNIQUEMENT un objet JSON valide. Zéro texte avant/après, zéro bloc markdown.\n"
         . "2. Le HTML doit être complet et autonome. Tous les styles dans le champ css (jamais d'attributs style= inline).\n"
         . "3. Utilise les variables CSS :root définies dans le design system. Ne redefinis JAMAIS les couleurs en dur.\n"
         . "4. Code production-ready, responsive mobile-first avec media queries.\n"
         . "5. Images : URLs Unsplash pertinentes ex: https://images.unsplash.com/photo-XXXXX?w=1200&q=80\n"
         . "6. Contenu réel en français, jamais de Lorem ipsum. Utilise les infos du profil conseiller.\n"
         . "7. Sépare proprement HTML, CSS, JS dans leurs champs respectifs.\n";

    $usr = $advisorBlock . "\n\n"
         . $designBlock . "\n\n"
         . "=== INSTRUCTIONS PAGE ===\n"
         . $typeInstructions . "\n"
         . ($page_title ? "Titre : {$page_title}\n" : "")
         . ($prompt     ? "Instructions spécifiques : {$prompt}\n" : "")
         . "\nGénère une page complète et professionnelle.\n\n"
         . "Retourne UNIQUEMENT ce JSON :\n"
         . "{\n"
         . "  \"html\": \"<!-- HTML complet structuré -->\",\n"
         . "  \"css\": \"/* CSS complet avec :root variables, responsive */\",\n"
         . "  \"js\": \"/* JavaScript interactions. Chaîne vide si non nécessaire. */\",\n"
         . "  \"meta_title\": \"Titre SEO 50-65 caractères\",\n"
         . "  \"meta_description\": \"Description SEO 140-155 caractères\",\n"
         . "  \"slug\": \"slug-url-sans-accents\",\n"
         . "  \"sections_summary\": [\"Section 1\", \"Section 2\"]\n"
         . "}";

    $raw  = callLLM($sys, $usr, 6000);
    $data = extractJson($raw);

    if (empty($data['html'])) {
        error_log('[builder.generate] raw: '.substr($raw, 0, 600));
        err('Génération échouée — réponse malformée. Consultez les logs.');
    }

    $data['css'] = $data['css'] ?? '';
    $data['js']  = $data['js']  ?? '';
    $data['sections_summary'] = $data['sections_summary'] ?? [];

    if (empty($data['slug'])) {
        $data['slug'] = $page_slug ?: makeSlug($page_title ?: ($data['meta_title'] ?? 'page'));
    }

    ok(['template' => $data]);
}

case 'builder.improve': {
    $html    = trim($body['html']    ?? ''); if (strlen($html) < 30) err('HTML trop court');
    $css     = trim($body['css']     ?? '');
    $js      = trim($body['js']      ?? '');
    $goals   = trim($body['goals']   ?? 'SEO, conversion, accessibilité, performance');

    $advisorBlock = buildAdvisorContext();
    $designBlock  = buildDesignSystem();

    $codeBlock  = "=== HTML ===\n".mb_substr($html, 0, 3000);
    $codeBlock .= "\n\n=== CSS ===\n".mb_substr($css, 0, 1500);
    $codeBlock .= "\n\n=== JS ===\n".mb_substr($js, 0, 800);

    $raw = callLLM(
        "Expert frontend immobilier. Améliore le code selon les objectifs. Utilise le profil conseiller et le design system. JSON uniquement.",
        $advisorBlock."\n\n".$designBlock."\n\nObjectifs : {$goals}\n\n{$codeBlock}\n\n"
        . "Retourne UNIQUEMENT :\n{\"html\":\"HTML amélioré\",\"css\":\"CSS amélioré\",\"js\":\"JS amélioré\",\"changes\":[\"changement 1\"]}",
        5000
    );
    $data = extractJson($raw);
    if (empty($data['html'])) err('Amélioration échouée');
    $data['css'] = $data['css'] ?? '';
    $data['js']  = $data['js']  ?? '';
    ok(['template' => $data]);
}

case 'builder.section': {
    $section_type = trim($body['section_type'] ?? 'hero');
    $prompt       = trim($body['prompt']       ?? '');

    $advisorBlock = buildAdvisorContext();
    $designBlock  = buildDesignSystem();

    $sectionDesc = match($section_type) {
        'hero'        => "Section HERO : h1 méthode MERE, sous-titre, 2 CTA (primaire + secondaire), visuel hero",
        'cta'         => "Section CTA : titre impactant, bénéfice clé, bouton principal, élément de réassurance",
        'faq'         => "Section FAQ accordéon JS natif : 6-8 Q/R immobilier local, Schema.org FAQPage",
        'temoignages' => "Section TÉMOIGNAGES : 3 avis avec prénom+ville, 5 étoiles CSS, citation percutante",
        'contact'     => "Section CONTACT : formulaire (prénom, nom, email, tél, message, objet), coordonnées avec icônes",
        'stats'       => "Section CHIFFRES CLÉS : 4 indicateurs, icône + grand nombre animé JS counter, unité, label",
        'services'    => "Section SERVICES : grille 3 colonnes, icône SVG + titre h3 + description 2 phrases + lien",
        'equipe'      => "Section À PROPOS : photo ronde placeholder, bio persuasive, certifications, 3 valeurs",
        'galerie'     => "Section GALERIE BIENS : grille 6 cartes bien immobilier, photo+type+ville+prix+badge",
        default       => "Section {$section_type} professionnelle pour site immobilier",
    };

    $raw = callLLM(
        "Expert frontend immobilier premium. Sections HTML/CSS/JS de haute qualité. JSON uniquement.",
        $advisorBlock."\n\n".$designBlock."\n\n"
        . "{$sectionDesc}\nInstructions : {$prompt}\n\n"
        . "Retourne UNIQUEMENT :\n{\"html\":\"<section>...</section>\",\"css\":\"/* CSS section */\",\"js\":\"/* JS ou vide */\"}",
        3000
    );
    $data = extractJson($raw);
    if (empty($data['html'])) err('Génération section échouée');
    $data['css'] = $data['css'] ?? '';
    $data['js']  = $data['js']  ?? '';
    ok(['section' => $data]);
}

// ══════════════════════════════════════════════════════════
// ARTICLES ACTIONS
// ══════════════════════════════════════════════════════════

case 'articles.generate': {
    $subject  = trim($body['subject']   ?? ''); if (!$subject) err('Sujet requis');
    $keywords = trim($body['keywords']  ?? '');
    $words    = max(400, min(3000, (int)($body['word_count'] ?? 1200)));
    $tone     = trim($body['tone']      ?? '');
    $type     = trim($body['type']      ?? 'guide complet étape par étape');
    $persona  = trim($body['persona']   ?? '');
    $objectif = trim($body['objectif']  ?? '');

    // Construire le contexte depuis la DB
    $advisorBlock = buildAdvisorContext();
    // Ton : paramètre > advisor_context > défaut
    if (!$tone) $tone = $advisorCtx['tone_voice'] ?? $advisorCtx['writing_style'] ?? 'professionnel et rassurant';

    $ctx = implode("\n", array_filter([
        $persona  ? "Persona cible : {$persona}."      : '',
        $objectif ? "Objectif : {$objectif}."          : '',
        $keywords ? "Mot-clé principal : {$keywords}." : '',
    ]));

    $sys = "Tu es expert rédaction SEO immobilier français pour conseillers indépendants.\nRéponds UNIQUEMENT en JSON valide, sans markdown, sans texte avant/après.";
    $usr = "Rédige un article immobilier complet en français.\n\n"
         . $advisorBlock."\n\n"
         . "Sujet : {$subject}\n{$ctx}\nLongueur : {$words} mots minimum\nTon : {$tone}\nFormat : {$type}\n\n"
         . "Retourne UNIQUEMENT :\n{\"title\":\"Titre SEO 50-65 car.\",\"slug\":\"slug-url\",\"excerpt\":\"chapô 150-180 car.\",\"content\":\"HTML complet h2/h3/p/ul/strong - min {$words} mots\",\"meta_title\":\"50-60 car\",\"meta_description\":\"140-155 car\",\"primary_keyword\":\"kw\",\"secondary_keywords\":[\"kw2\",\"kw3\"]}";

    $raw  = callLLM($sys, $usr, 4500);
    $data = extractJson($raw);
    if (empty($data['title']) || empty($data['content'])) {
        error_log('[articles.generate] raw: '.substr($raw, 0, 400));
        err('Génération échouée — réponse malformée.');
    }
    if (empty($data['slug'])) $data['slug'] = makeSlug($data['title']);
    ok(['article' => $data]);
}

case 'articles.improve': {
    $content = trim(strip_tags($body['content'] ?? '')); if (strlen($content) < 50) err('Contenu trop court');
    $title   = trim($body['title']      ?? '');
    $goals   = trim($body['objectives'] ?? 'SEO, lisibilité, engagement');
    $raw = callLLM(
        "Expert rédaction SEO immobilier. JSON uniquement.",
        "Améliore cet article.\nTitre:{$title}\nObjectifs:{$goals}\n\n".mb_substr($content,0,4000)
        ."\n\nJSON:{\"improved_content\":\"HTML amélioré\",\"changes_summary\":[\"point 1\"]}",
        3500
    );
    $data = extractJson($raw); if (empty($data['improved_content'])) err('Amélioration échouée');
    ok(['data' => $data]);
}

case 'articles.meta': {
    $title   = trim($body['title']   ?? '');
    $keyword = trim($body['keyword'] ?? '');
    $content = mb_substr(trim(strip_tags($body['content'] ?? '')), 0, 1500);
    if (!$title && !$content) err('Titre ou contenu requis');
    $raw = callLLM(
        "Expert SEO immobilier France. JSON uniquement.",
        "Métas SEO.\nTitre:{$title}\nMot-clé:{$keyword}\nContenu:{$content}\n\nJSON:{\"meta_title\":\"50-60 car\",\"meta_description\":\"140-155 car\",\"slug\":\"url-slug\"}",
        400
    );
    $data = extractJson($raw); if (empty($data['meta_title'])) err('Métas échouées');
    if (empty($data['slug'])) $data['slug'] = makeSlug($data['meta_title'] ?? $title);
    ok($data);
}

case 'articles.faq': {
    $title   = trim($body['title']   ?? '');
    $content = mb_substr(trim(strip_tags($body['content'] ?? '')), 0, 2500);
    $count   = max(3, min(10, (int)($body['count'] ?? 5)));
    if (!$title && !$content) err('Titre ou contenu requis');
    $raw = callLLM(
        "Expert immobilier. JSON uniquement.",
        "FAQ {$count} Q/R.\nTitre:{$title}\nContenu:{$content}\n\nJSON:{\"faq\":[{\"question\":\"?\",\"answer\":\"réponse\"}]}",
        1500
    );
    $data = extractJson($raw); if (empty($data['faq'])) err('FAQ échouée');
    ok(['faq' => $data['faq']]);
}

case 'articles.outline': {
    $subject = trim($body['subject'] ?? ''); if (!$subject) err('Sujet requis');
    $keyword = trim($body['keyword'] ?? '');
    $raw = callLLM(
        "Stratège SEO immobilier. JSON uniquement.",
        "Plan éditorial.\nSujet:{$subject}\nMot-clé:{$keyword}\n\nJSON:{\"title_suggestions\":[\"t1\",\"t2\",\"t3\"],\"outline\":[{\"level\":\"h2\",\"title\":\"Section\",\"description\":\"desc\",\"estimated_words\":200}]}",
        1200
    );
    ok(['outline' => extractJson($raw)]);
}

case 'articles.keywords': {
    $content = mb_substr(trim(strip_tags($body['content'] ?? '')), 0, 2500);
    $subject = trim($body['subject'] ?? '');
    if (!$content && !$subject) err('Contenu ou sujet requis');
    $raw = callLLM(
        "Expert SEO immobilier. JSON uniquement.",
        "Mots-clés SEO.\nSujet:{$subject}\nContenu:{$content}\n\nJSON:{\"primary_keyword\":\"kw\",\"secondary_keywords\":[{\"keyword\":\"kw\",\"intent\":\"inf\"}],\"long_tail_keywords\":[\"kw\"],\"local_keywords\":[\"kw\"]}",
        800
    );
    ok(['keywords' => extractJson($raw)]);
}

case 'articles.rewrite': {
    $content = mb_substr(trim(strip_tags($body['content'] ?? '')), 0, 3000);
    if (strlen($content) < 50) err('Contenu trop court');
    $angle = trim($body['angle'] ?? 'grand public');
    $raw = callLLM(
        "Copywriter immobilier France. JSON uniquement.",
        "Réécris pour : \"{$angle}\".\n\n{$content}\n\nJSON:{\"rewritten_content\":\"HTML réécrit\"}",
        3500
    );
    $data = extractJson($raw); if (empty($data['rewritten_content'])) err('Réécriture échouée');
    ok(['rewritten_content' => $data['rewritten_content']]);
}

case 'articles.excerpt': {
    $title   = trim($body['title']   ?? '');
    $content = mb_substr(trim(strip_tags($body['content'] ?? '')), 0, 1500);
    $kw      = trim($body['keyword'] ?? '');
    if (!$title && !$content) err('Titre ou contenu requis');
    $raw = callLLM(
        "Copywriter immobilier. JSON uniquement.",
        "Extrait accrocheur.\nTitre:{$title}\nMot-clé:{$kw}\nContenu:{$content}\n\nJSON:{\"excerpt\":\"150-180 car accrocheur\"}",
        300
    );
    $data = extractJson($raw); if (empty($data['excerpt'])) err('Extrait échoué');
    ok(['excerpt' => $data['excerpt']]);
}

default: err("Action inconnue : {$module}.{$action}");

} // switch
} catch (Exception $e) {
    error_log('[AI Dispatcher] '.$e->getMessage());
    err('Erreur IA : '.$e->getMessage());
}
