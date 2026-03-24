<?php
/**
 * Module Analyseur de Marché Immobilier
 * /admin/modules/immobilier/market-analyzer/index.php
 *
 * Permet à l'utilisateur de :
 * - Sélectionner une ville (onboarding ou saisie libre)
 * - Lancer une analyse complète du marché
 * - Voir l'historique des analyses
 */

// ── DB ──────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Exception $e) {
    echo '<div style="padding:20px;color:#dc2626;background:#fef2f2;border-radius:12px;margin:20px;">Erreur de connexion DB</div>';
    return;
}

require_once __DIR__ . '/MarketAnalyzer.php';

$user_id = $_SESSION['admin_id'] ?? 1;
$analyzer = new MarketAnalyzer($pdo, $user_id);
$cities = $analyzer->getUserCities();
$analyses = $analyzer->getAllAnalyses();

// Villes prédéfinies françaises populaires
$suggestedCities = [
    'Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Toulouse',
    'Nantes', 'Montpellier', 'Strasbourg', 'Lille', 'Rennes',
    'Nice', 'Grenoble', 'Aix-en-Provence', 'Rouen', 'Tours',
    'Angers', 'Reims', 'Toulon', 'Saint-Étienne', 'Le Havre',
    'Brest', 'Dijon', 'Clermont-Ferrand', 'Metz', 'Perpignan',
    'Orléans', 'Caen', 'Pau', 'La Rochelle', 'Bayonne'
];

// Retirer les villes déjà ajoutées
$existingNames = array_map(function($c) { return strtolower($c['city']); }, $cities);
$availableSuggestions = array_filter($suggestedCities, function($c) use ($existingNames) {
    return !in_array(strtolower($c), $existingNames);
});
?>

<style>
/* ═══════════════════════════════════════════════
   MARKET ANALYZER — Styles
═══════════════════════════════════════════════ */
.ma-wrap { max-width: 1200px; }

/* Header */
.ma-page-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
}
.ma-page-header h1 { margin: 0; font-size: 22px; font-weight: 700; color: var(--text, #1a202c); }
.ma-page-header h1 i { color: var(--accent, #c9913b); margin-right: 8px; }
.ma-page-subtitle { color: var(--text-3, #6b7280); font-size: 13px; margin-top: 2px; }

/* City selector */
.ma-city-selector {
    background: white; border-radius: 12px; padding: 24px;
    border: 1px solid #e5e7eb; margin-bottom: 24px;
}
.ma-city-selector h3 { margin: 0 0 16px; font-size: 15px; font-weight: 600; }
.ma-city-selector h3 i { color: var(--accent, #c9913b); margin-right: 8px; }

.ma-city-input-row {
    display: flex; gap: 10px; align-items: center; margin-bottom: 16px;
}
.ma-city-input {
    flex: 1; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 8px;
    font-size: 14px; outline: none; transition: border-color .2s;
    font-family: inherit;
}
.ma-city-input:focus { border-color: var(--accent, #c9913b); }
.ma-city-input::placeholder { color: #9ca3af; }

/* Autocomplete dropdown */
.ma-autocomplete {
    position: relative;
}
.ma-autocomplete-list {
    position: absolute; top: 100%; left: 0; right: 0;
    background: white; border: 1px solid #e5e7eb; border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12); z-index: 100;
    max-height: 200px; overflow-y: auto; display: none;
}
.ma-autocomplete-list.show { display: block; }
.ma-ac-item {
    padding: 10px 14px; cursor: pointer; font-size: 13px;
    transition: background .15s; border-bottom: 1px solid #f3f4f6;
}
.ma-ac-item:hover { background: #f9fafb; }
.ma-ac-item:last-child { border-bottom: none; }
.ma-ac-item i { color: var(--accent, #c9913b); margin-right: 8px; font-size: 11px; }

/* City tags */
.ma-cities-row {
    display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px;
}
.ma-city-tag {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 20px; font-size: 12.5px;
    font-weight: 500; cursor: pointer; transition: all .2s;
    border: 1.5px solid #e5e7eb; background: white; color: var(--text, #1a202c);
}
.ma-city-tag:hover { border-color: var(--accent, #c9913b); background: rgba(201,145,59,.05); }
.ma-city-tag.active {
    border-color: var(--accent, #c9913b); background: rgba(201,145,59,.1);
    color: var(--accent, #c9913b); font-weight: 600;
}
.ma-city-tag.primary { border-color: #6366f1; }
.ma-city-tag.primary::before {
    content: '\f005'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
    font-size: 9px; color: #6366f1;
}
.ma-city-remove {
    font-size: 10px; opacity: .5; margin-left: 2px;
    cursor: pointer; transition: opacity .15s;
}
.ma-city-remove:hover { opacity: 1; color: #ef4444; }

/* Suggested cities */
.ma-suggestions { margin-top: 12px; }
.ma-suggestions-label { font-size: 11px; color: #9ca3af; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .5px; }
.ma-suggestions-list { display: flex; flex-wrap: wrap; gap: 6px; }
.ma-suggestion {
    padding: 4px 10px; border-radius: 12px; font-size: 11.5px;
    background: #f3f4f6; color: #6b7280; cursor: pointer;
    transition: all .15s; border: 1px solid transparent;
}
.ma-suggestion:hover { background: rgba(201,145,59,.1); color: var(--accent, #c9913b); border-color: var(--accent, #c9913b); }

/* Analyze button */
.ma-analyze-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;
    background: var(--accent, #c9913b); color: white; border: none;
    cursor: pointer; transition: all .2s; font-family: inherit;
}
.ma-analyze-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
.ma-analyze-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.ma-analyze-btn .spinner { display: none; }
.ma-analyze-btn.loading .spinner { display: inline-block; }
.ma-analyze-btn.loading .btn-text { display: none; }

/* Results area */
.ma-results {
    background: white; border-radius: 12px; padding: 24px;
    border: 1px solid #e5e7eb; min-height: 200px;
}
.ma-empty {
    text-align: center; padding: 60px 20px; color: #9ca3af;
}
.ma-empty i { font-size: 48px; margin-bottom: 16px; opacity: .3; }
.ma-empty p { margin: 4px 0; font-size: 13px; }

/* Loading skeleton */
.ma-loading { padding: 40px; text-align: center; }
.ma-loading-spinner {
    width: 40px; height: 40px; border: 3px solid #e5e7eb;
    border-top-color: var(--accent, #c9913b); border-radius: 50%;
    animation: maSpin 1s linear infinite; margin: 0 auto 16px;
}
@keyframes maSpin { to { transform: rotate(360deg); } }
.ma-loading-text { color: #6b7280; font-size: 14px; }
.ma-loading-sub { color: #9ca3af; font-size: 12px; margin-top: 4px; }

/* Report styles */
.ma-report { }
.ma-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    gap: 20px; margin-bottom: 24px; padding-bottom: 20px;
    border-bottom: 2px solid #f0f4ff;
}
.ma-header h2 { margin: 0; font-size: 20px; font-weight: 700; }
.ma-resume { color: #6b7280; font-size: 13px; margin-top: 6px; }

.ma-score-badge {
    text-align: center; padding: 12px 16px; border-radius: 12px;
    border: 2px solid; background: white; min-width: 80px; flex-shrink: 0;
}
.ma-score-val { font-size: 24px; font-weight: 800; }
.ma-score-label { font-size: 11px; font-weight: 600; margin-top: 2px; }

/* KPIs */
.ma-kpis {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px; margin-bottom: 24px;
}
.ma-kpi {
    background: #f9fafb; border-radius: 10px; padding: 16px;
    text-align: center; border: 1px solid #f3f4f6;
}
.ma-kpi-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 8px; font-size: 15px;
}
.ma-kpi-val { font-size: 18px; font-weight: 700; color: var(--text, #1a202c); }
.ma-kpi-label { font-size: 11px; color: #6b7280; margin-top: 2px; }
.ma-kpi-trend { font-size: 12px; font-weight: 600; margin-top: 4px; }

/* Sections */
.ma-section {
    margin-bottom: 24px; padding: 20px; background: #fafbfc;
    border-radius: 10px; border: 1px solid #f0f0f0;
}
.ma-section h3 {
    margin: 0 0 12px; font-size: 15px; font-weight: 700;
    color: var(--text, #1a202c);
}
.ma-section h3 i { color: var(--accent, #c9913b); margin-right: 8px; }
.ma-section p { font-size: 13px; color: #4b5563; line-height: 1.5; margin: 0 0 12px; }

.ma-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.ma-stat-box {
    background: white; border-radius: 8px; padding: 14px;
    border: 1px solid #e5e7eb; text-align: center;
}
.ma-stat-label { font-size: 11px; color: #6b7280; margin-bottom: 4px; }
.ma-stat-val { font-size: 22px; font-weight: 700; color: var(--text, #1a202c); }

.ma-source {
    font-size: 10px; color: #9ca3af; margin-top: 10px;
    font-style: italic; text-align: right;
}

/* Table */
.ma-table {
    width: 100%; border-collapse: collapse; font-size: 13px;
    background: white; border-radius: 8px; overflow: hidden;
}
.ma-table th {
    background: #f3f4f6; padding: 10px 12px; text-align: left;
    font-weight: 600; font-size: 11px; text-transform: uppercase;
    letter-spacing: .3px; color: #6b7280;
}
.ma-table td {
    padding: 10px 12px; border-bottom: 1px solid #f3f4f6;
}
.ma-table tr:last-child td { border-bottom: none; }

/* Sites list */
.ma-sites-list { background: white; border-radius: 8px; padding: 12px; border: 1px solid #e5e7eb; }
.ma-sites-title { font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 8px; text-transform: uppercase; }
.ma-site-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px;
}
.ma-site-row:last-child { border-bottom: none; }
.ma-site-name { color: #4b5563; }
.ma-site-count { font-weight: 700; color: var(--text, #1a202c); }

/* Conseils */
.ma-conseil {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 12px; background: white; border-radius: 8px;
    border: 1px solid #e5e7eb; margin-bottom: 8px; font-size: 13px;
}
.ma-conseil-num {
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--accent, #c9913b); color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}

/* Points lists */
.ma-points-list { list-style: none; padding: 0; margin: 0; font-size: 13px; }
.ma-points-list li { padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
.ma-points-list li:last-child { border-bottom: none; }

/* Strategie */
.ma-strategie {
    background: white; border-radius: 8px; padding: 16px;
    border: 1px solid #e5e7eb; margin-top: 16px;
}
.ma-strategie strong { font-size: 13px; color: var(--text, #1a202c); }
.ma-strategie strong i { color: var(--accent, #c9913b); margin-right: 6px; }
.ma-strategie p { font-size: 13px; color: #4b5563; margin: 8px 0 0; }

/* History */
.ma-history { margin-top: 24px; }
.ma-history h3 { margin: 0 0 12px; font-size: 15px; font-weight: 600; }
.ma-history h3 i { color: var(--accent, #c9913b); margin-right: 8px; }
.ma-history-list { display: flex; flex-direction: column; gap: 8px; }
.ma-history-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px; background: white; border-radius: 8px;
    border: 1px solid #e5e7eb; cursor: pointer; transition: all .15s;
    font-size: 13px;
}
.ma-history-item:hover { border-color: var(--accent, #c9913b); background: rgba(201,145,59,.02); }
.ma-history-city { font-weight: 600; flex: 1; }
.ma-history-date { color: #9ca3af; font-size: 11px; }
.ma-history-price { color: var(--accent, #c9913b); font-weight: 600; }

/* Error */
.ma-error {
    padding: 16px; background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 8px; color: #dc2626; font-size: 13px;
}
.ma-error i { margin-right: 8px; }

/* Responsive */
@media (max-width: 768px) {
    .ma-page-header { flex-direction: column; }
    .ma-kpis { grid-template-columns: 1fr 1fr; }
    .ma-grid-2 { grid-template-columns: 1fr; }
    .ma-header { flex-direction: column; }
}
</style>

<div class="ma-wrap">

    <!-- Header -->
    <div class="ma-page-header">
        <div>
            <h1><i class="fas fa-chart-line"></i>Analyseur de Marché</h1>
            <div class="ma-page-subtitle">Analyse complète du marché immobilier par ville — prix, transactions, annonces, SEO</div>
        </div>
    </div>

    <!-- City Selector -->
    <div class="ma-city-selector">
        <h3><i class="fas fa-map-marker-alt"></i> Sélectionnez une ville</h3>

        <!-- Villes existantes -->
        <?php if (!empty($cities)): ?>
        <div class="ma-cities-row" id="citiesTags">
            <?php foreach ($cities as $c):
                $name = htmlspecialchars($c['city']);
                $isPrimary = $c['is_primary'] ? ' primary' : '';
            ?>
            <div class="ma-city-tag<?= $isPrimary ?>" data-city="<?= $name ?>" onclick="selectCity('<?= addslashes($name) ?>')">
                <span><?= $name ?></span>
                <?php if ($c['source'] === 'manual'): ?>
                    <span class="ma-city-remove" onclick="event.stopPropagation(); removeCity('<?= addslashes($name) ?>')" title="Supprimer">
                        <i class="fas fa-times"></i>
                    </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Input nouvelle ville -->
        <div class="ma-city-input-row">
            <div class="ma-autocomplete" style="flex:1">
                <input type="text" class="ma-city-input" id="cityInput"
                       placeholder="Saisir une nouvelle ville (ex: Nantes, Lyon...)"
                       autocomplete="off">
                <div class="ma-autocomplete-list" id="acList"></div>
            </div>
            <button class="ma-analyze-btn" id="analyzeBtn" onclick="launchAnalysis()">
                <span class="btn-text"><i class="fas fa-search"></i> Analyser le marché</span>
                <span class="spinner"><i class="fas fa-spinner fa-spin"></i> Analyse en cours...</span>
            </button>
        </div>

        <!-- Suggestions -->
        <?php if (!empty($availableSuggestions)): ?>
        <div class="ma-suggestions" id="suggestionsWrap">
            <div class="ma-suggestions-label">Villes populaires</div>
            <div class="ma-suggestions-list" id="suggestionsList">
                <?php foreach (array_slice($availableSuggestions, 0, 15) as $s): ?>
                <span class="ma-suggestion" onclick="pickSuggestion('<?= addslashes($s) ?>')"><?= htmlspecialchars($s) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Results -->
    <div class="ma-results" id="resultsArea">
        <div class="ma-empty" id="emptyState">
            <div><i class="fas fa-chart-pie"></i></div>
            <p style="font-size:16px;font-weight:600;color:var(--text,#1a202c)">Prêt à analyser</p>
            <p>Sélectionnez une ville ci-dessus puis cliquez sur "Analyser le marché"</p>
            <p>pour obtenir une analyse complète : prix, transactions, annonces et mots-clés SEO.</p>
        </div>
        <div class="ma-loading" id="loadingState" style="display:none">
            <div class="ma-loading-spinner"></div>
            <div class="ma-loading-text" id="loadingText">Analyse du marché en cours...</div>
            <div class="ma-loading-sub">Collecte des données DVF, prix, annonces et mots-clés SEO</div>
        </div>
        <div id="reportArea" style="display:none"></div>
        <div class="ma-error" id="errorArea" style="display:none">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorMsg"></span>
        </div>
    </div>

    <!-- Historique -->
    <?php if (!empty($analyses)): ?>
    <div class="ma-history">
        <h3><i class="fas fa-clock-rotate-left"></i> Historique des analyses</h3>
        <div class="ma-history-list">
            <?php foreach (array_slice($analyses, 0, 10) as $a):
                $cityH = htmlspecialchars($a['city']);
                $dateH = date('d/m/Y H:i', strtotime($a['created_at']));
                $pxM = $a['prix_maison'] ? number_format(json_decode($a['prix_maison']), 0, ',', ' ') . ' €/m²' : '—';
            ?>
            <div class="ma-history-item" onclick="selectCity('<?= addslashes($a['city']) ?>'); launchAnalysis();">
                <i class="fas fa-map-pin" style="color:var(--accent,#c9913b)"></i>
                <span class="ma-history-city"><?= $cityH ?></span>
                <span class="ma-history-price"><i class="fas fa-house" style="font-size:10px;margin-right:4px"></i><?= $pxM ?></span>
                <span class="ma-history-date"><?= $dateH ?></span>
                <i class="fas fa-chevron-right" style="color:#cbd5e1;font-size:11px"></i>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// ═══════════════════════════════════════════════
// MARKET ANALYZER — JavaScript
// ═══════════════════════════════════════════════

const API_URL = 'modules/immobilier/market-analyzer/api.php';
const allCities = <?= json_encode(array_values($suggestedCities)) ?>;
let selectedCity = '';

// ── City selection ────────────────────────────
function selectCity(city) {
    selectedCity = city;
    document.getElementById('cityInput').value = city;

    // Update active tag
    document.querySelectorAll('.ma-city-tag').forEach(t => {
        t.classList.toggle('active', t.dataset.city === city);
    });
}

function pickSuggestion(city) {
    document.getElementById('cityInput').value = city;
    selectedCity = city;
    hideAC();
}

// ── Autocomplete ──────────────────────────────
const cityInput = document.getElementById('cityInput');
const acList = document.getElementById('acList');

cityInput.addEventListener('input', function() {
    selectedCity = this.value.trim();
    const q = this.value.toLowerCase().trim();

    if (q.length < 2) { hideAC(); return; }

    const matches = allCities.filter(c => c.toLowerCase().includes(q)).slice(0, 8);

    if (matches.length === 0) { hideAC(); return; }

    acList.innerHTML = matches.map(c =>
        `<div class="ma-ac-item" onclick="pickSuggestion('${c.replace(/'/g, "\\'")}')">
            <i class="fas fa-map-pin"></i>${c}
        </div>`
    ).join('');
    acList.classList.add('show');
});

cityInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        hideAC();
        launchAnalysis();
    }
    if (e.key === 'Escape') hideAC();
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.ma-autocomplete')) hideAC();
});

function hideAC() { acList.classList.remove('show'); }

// ── Launch analysis ───────────────────────────
async function launchAnalysis() {
    const city = (document.getElementById('cityInput').value || selectedCity).trim();
    if (!city) {
        showError('Veuillez saisir ou sélectionner une ville.');
        return;
    }

    // UI state
    const btn = document.getElementById('analyzeBtn');
    btn.classList.add('loading');
    btn.disabled = true;

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('errorArea').style.display = 'none';
    document.getElementById('reportArea').style.display = 'none';
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('loadingText').textContent = `Analyse du marché de ${city} en cours...`;

    try {
        const resp = await fetch(`${API_URL}?action=analyze`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city: city })
        });

        const data = await resp.json();

        document.getElementById('loadingState').style.display = 'none';

        if (data.success && data.analysis) {
            document.getElementById('reportArea').innerHTML = data.analysis.analysis_html;
            document.getElementById('reportArea').style.display = 'block';

            if (data.cached) {
                const banner = document.createElement('div');
                banner.style.cssText = 'padding:8px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;color:#166534;margin-bottom:16px;';
                banner.innerHTML = '<i class="fas fa-bolt" style="margin-right:6px"></i>Analyse en cache — générée il y a moins de 24h. <a href="#" onclick="forceRefresh(\'' + city.replace(/'/g, "\\'") + '\'); return false;" style="color:#166534;font-weight:600;text-decoration:underline">Relancer</a>';
                document.getElementById('reportArea').prepend(banner);
            }
        } else {
            showError(data.error || 'Erreur inconnue lors de l\'analyse.');
        }
    } catch (err) {
        document.getElementById('loadingState').style.display = 'none';
        showError('Erreur réseau : ' + err.message);
    }

    btn.classList.remove('loading');
    btn.disabled = false;
}

function showError(msg) {
    document.getElementById('errorMsg').textContent = msg;
    document.getElementById('errorArea').style.display = 'block';
}

// ── Force refresh (bypass cache) ──────────────
async function forceRefresh(city) {
    // On supprime le cache côté client en relançant
    // Le backend gérera la fraîcheur
    document.getElementById('reportArea').style.display = 'none';
    selectedCity = city;
    document.getElementById('cityInput').value = city;

    const btn = document.getElementById('analyzeBtn');
    btn.classList.add('loading');
    btn.disabled = true;

    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('loadingText').textContent = `Actualisation de l'analyse pour ${city}...`;

    try {
        const resp = await fetch(`${API_URL}?action=analyze`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city: city, force: true })
        });
        const data = await resp.json();
        document.getElementById('loadingState').style.display = 'none';

        if (data.success && data.analysis) {
            document.getElementById('reportArea').innerHTML = data.analysis.analysis_html;
            document.getElementById('reportArea').style.display = 'block';
        } else {
            showError(data.error || 'Erreur');
        }
    } catch (err) {
        document.getElementById('loadingState').style.display = 'none';
        showError('Erreur réseau : ' + err.message);
    }

    btn.classList.remove('loading');
    btn.disabled = false;
}

// ── Remove city ───────────────────────────────
async function removeCity(city) {
    if (!confirm(`Retirer "${city}" de votre liste ?`)) return;

    try {
        await fetch(`${API_URL}?action=remove-city`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city: city })
        });
        // Remove tag from DOM
        document.querySelectorAll('.ma-city-tag').forEach(t => {
            if (t.dataset.city === city) t.remove();
        });
    } catch (err) {
        console.error('Erreur suppression ville:', err);
    }
}
</script>
