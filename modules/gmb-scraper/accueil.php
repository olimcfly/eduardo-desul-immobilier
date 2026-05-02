<?php

declare(strict_types=1);

$pageTitle = 'Scraper fiche Google';
$pageDescription = 'Importer le nom, l’adresse et les infos visibles depuis un lien Google Maps vers votre fiche locale.';

require_once __DIR__ . '/includes/GmbMapScraper.php';
require_once ROOT_PATH . '/modules/gmb/includes/GmbService.php';

function renderContent(): void
{
    $user = Auth::user();
    $userId = (int) ($user['id'] ?? 0);

    $flashOk = '';
    $flashErr = '';
    $lastScrape = null;

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $postedCsrf = (string) ($_POST['csrf_token'] ?? '');
        if (!function_exists('csrfToken') || !hash_equals(csrfToken(), $postedCsrf)) {
            $flashErr = 'Session expirée : rechargez la page et réessayez.';
        } else {
            $action = (string) ($_POST['gmb_scraper_action'] ?? '');

            if ($action === 'scrape') {
                $url = (string) ($_POST['maps_url'] ?? '');
                $result = GmbMapScraper::fetchAndParse($url);
                if (!$result['ok']) {
                    $flashErr = (string) ($result['error'] ?? 'Erreur inconnue.');
                } else {
                    $lastScrape = $result['data'];
                    $_SESSION['_gmb_scraper_last'] = $lastScrape;
                    $flashOk = 'Données récupérées. Vérifiez le résumé puis enregistrez dans votre fiche GMB si tout est correct.';
                }
            } elseif ($action === 'apply' && $userId > 0) {
                $lastScrape = $_SESSION['_gmb_scraper_last'] ?? null;
                if (!is_array($lastScrape)) {
                    $flashErr = 'Aucune donnée à appliquer : lancez d’abord une extraction.';
                } else {
                    $service = new GmbService($userId);
                    $existing = $service->getFiche();
                    $decodeJson = static function (mixed $v): array {
                        if (is_array($v)) {
                            return $v;
                        }
                        if (!is_string($v) || $v === '') {
                            return [];
                        }
                        $d = json_decode($v, true);

                        return is_array($d) ? $d : [];
                    };
                    $nom = trim((string) ($lastScrape['nom_etablissement'] ?? ''));
                    $descParts = [];
                    if (($lastScrape['description_snippet'] ?? '') !== '') {
                        $descParts[] = trim((string) $lastScrape['description_snippet']);
                    }
                    if (isset($lastScrape['rating'])) {
                        $descParts[] = 'Note indicative (page Maps) : ' . (string) $lastScrape['rating'] . '/5'
                            . (isset($lastScrape['review_count']) ? ' — ' . (string) $lastScrape['review_count'] . ' avis' : '');
                    }
                    $mergedDesc = trim((string) ($existing['description'] ?? ''));
                    $block = implode("\n\n", array_filter($descParts));
                    if ($block !== '') {
                        $mergedDesc = trim($mergedDesc . "\n\n" . $block);
                    }

                    $addrGuess = trim((string) ($lastScrape['adresse_guess'] ?? ''));
                    $telGuess = trim((string) ($lastScrape['telephone_guess'] ?? ''));
                    $placeGuess = trim((string) ($lastScrape['place_id_guess'] ?? ''));

                    $payload = [
                        'gmb_location_id' => $placeGuess !== '' ? $placeGuess : (string) ($existing['gmb_location_id'] ?? ''),
                        'gmb_account_id' => (string) ($existing['gmb_account_id'] ?? ''),
                        'nom_etablissement' => $nom !== '' ? $nom : (string) ($existing['nom_etablissement'] ?? ''),
                        'categorie' => (string) ($existing['categorie'] ?? 'Agence immobilière'),
                        'adresse' => $addrGuess !== '' ? $addrGuess : (string) ($existing['adresse'] ?? ''),
                        'ville' => (string) ($existing['ville'] ?? ''),
                        'code_postal' => (string) ($existing['code_postal'] ?? ''),
                        'telephone' => $telGuess !== '' ? $telGuess : (string) ($existing['telephone'] ?? ''),
                        'site_web' => (string) ($existing['site_web'] ?? ''),
                        'description' => $mergedDesc,
                        'horaires' => $decodeJson($existing['horaires'] ?? []),
                        'photos' => $decodeJson($existing['photos'] ?? []),
                        'statut' => (string) ($existing['statut'] ?? 'non_verifie'),
                    ];

                    if ($service->saveFiche($payload)) {
                        $flashOk = 'Fiche GMB locale mise à jour. Ouvrez le module « Google My Business » pour affiner ou synchroniser.';
                        unset($_SESSION['_gmb_scraper_last']);
                        $lastScrape = null;
                    } else {
                        $flashErr = 'Enregistrement impossible (base de données).';
                    }
                }
            }
        }
    }

    if ($lastScrape === null && !empty($_SESSION['_gmb_scraper_last']) && is_array($_SESSION['_gmb_scraper_last'])) {
        $lastScrape = $_SESSION['_gmb_scraper_last'];
    }

    ?>
    <style>
        .gmb-scraper-wrap { max-width: 720px; }
        .gmb-scraper-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 24px;
        }
        .gmb-scraper-hero h1 { font-size: 1.35rem; margin: 0 0 10px; }
        .gmb-scraper-hero p { margin: 0; font-size: .95rem; opacity: .85; line-height: 1.55; }
        .gmb-scraper-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }
        .gmb-scraper-card h2 { font-size: 1rem; margin: 0 0 14px; color: #0f172a; }
        .gmb-scraper-label { display: block; font-weight: 600; font-size: .88rem; margin-bottom: 8px; color: #334155; }
        .gmb-scraper-input {
            width: 100%; padding: 12px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: .95rem; font-family: inherit;
        }
        .gmb-scraper-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .gmb-scraper-hint { font-size: .82rem; color: #64748b; margin-top: 8px; line-height: 1.45; }
        .gmb-scraper-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
        .gmb-scraper-btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
            border-radius: 10px; font-weight: 700; font-size: .9rem; border: none; cursor: pointer;
        }
        .gmb-scraper-btn-primary { background: #0f2237; color: #fff; }
        .gmb-scraper-btn-primary:hover { background: #152a45; }
        .gmb-scraper-btn-accent { background: #c9a84c; color: #0f2237; }
        .gmb-scraper-btn-accent:hover { filter: brightness(.95); }
        .gmb-scraper-alert { padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-size: .9rem; }
        .gmb-scraper-alert.ok { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; }
        .gmb-scraper-alert.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .gmb-scraper-pre {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;
            font-size: .82rem; overflow-x: auto; white-space: pre-wrap; word-break: break-word;
        }
        .gmb-scraper-dl { display: grid; gap: 8px; font-size: .9rem; }
        .gmb-scraper-dl div { display: flex; gap: 10px; flex-wrap: wrap; }
        .gmb-scraper-dl dt { font-weight: 600; color: #64748b; min-width: 140px; }
        .gmb-scraper-dl dd { margin: 0; color: #0f172a; }
    </style>

    <div class="gmb-scraper-wrap">
        <div class="gmb-scraper-hero">
            <h1>Scraper fiche Google (Maps)</h1>
            <p>
                Collez le lien « Partager » de votre fiche Google Maps. Nous extrayons ce qui est visible dans la page HTML
                (souvent partiel car Google charge beaucoup d’éléments en JavaScript). Complétez ensuite dans
                <a href="/admin?module=gmb" style="color:#c9a84c;">Google My Business</a>.
            </p>
        </div>

        <?php if ($flashOk !== ''): ?>
            <div class="gmb-scraper-alert ok"><?= htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <div class="gmb-scraper-alert err"><?= htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="gmb-scraper-card">
            <h2>1. Lien Google Maps</h2>
            <form method="post" action="/admin?module=gmb-scraper">
                <?php if (function_exists('csrfField')) {
                    echo csrfField();
                } ?>
                <input type="hidden" name="gmb_scraper_action" value="scrape">
                <label class="gmb-scraper-label" for="maps_url">URL de la fiche</label>
                <input class="gmb-scraper-input" type="url" name="maps_url" id="maps_url"
                       placeholder="https://www.google.com/maps/place/... ou https://maps.app.goo.gl/..."
                       value="<?= htmlspecialchars((string) ($_POST['maps_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <p class="gmb-scraper-hint">
                    Ouvrez Google Maps → votre établissement → <strong>Partager</strong> → copier le lien.
                    Domaines acceptés : google.com, google.fr, maps.app.goo.gl, g.page, etc.
                </p>
                <div class="gmb-scraper-actions">
                    <button type="submit" class="gmb-scraper-btn gmb-scraper-btn-primary">Extraire les données</button>
                    <a href="/admin?module=gmb" class="gmb-scraper-btn gmb-scraper-btn-accent" style="text-decoration:none;">Ouvrir le module GMB</a>
                </div>
            </form>
        </div>

        <?php if (is_array($lastScrape)): ?>
            <div class="gmb-scraper-card">
                <h2>2. Résumé extrait</h2>
                <dl class="gmb-scraper-dl">
                    <div><dt>Nom</dt><dd><?= htmlspecialchars((string) ($lastScrape['nom_etablissement'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Adresse (indice)</dt><dd><?= htmlspecialchars((string) ($lastScrape['adresse_guess'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Téléphone (indice)</dt><dd><?= htmlspecialchars((string) ($lastScrape['telephone_guess'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Note / avis</dt><dd><?php
                        $r = $lastScrape['rating'] ?? null;
                        $n = $lastScrape['review_count'] ?? null;
                        if ($r !== null || $n !== null) {
                            echo htmlspecialchars(trim(($r !== null ? $r . '/5' : '') . ($n !== null ? ' — ' . $n . ' avis' : '')), ENT_QUOTES, 'UTF-8');
                        } else {
                            echo '—';
                        }
                    ?></dd></div>
                    <div><dt>Place ID (si détecté)</dt><dd><?= htmlspecialchars((string) ($lastScrape['place_id_guess'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
                <?php if (($lastScrape['description_snippet'] ?? '') !== ''): ?>
                    <p style="margin:14px 0 0;font-size:.9rem;color:#475569;"><strong>Description (extrait)</strong></p>
                    <div class="gmb-scraper-pre"><?= htmlspecialchars((string) $lastScrape['description_snippet'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="/admin?module=gmb-scraper" style="margin-top:18px;">
                    <?php if (function_exists('csrfField')) {
                        echo csrfField();
                    } ?>
                    <input type="hidden" name="gmb_scraper_action" value="apply">
                    <button type="submit" class="gmb-scraper-btn gmb-scraper-btn-accent">Enregistrer dans ma fiche GMB (brouillon local)</button>
                    <p class="gmb-scraper-hint">Fusionne avec votre fiche existante : ne remplace que les champs vides ou complète la description. Vérifiez toujours les données à la main.</p>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
