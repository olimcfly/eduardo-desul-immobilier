<?php
// ============================================================
// WIZARD D'INSTALLATION - Pascal Hamm Immobilier
// Fichier: agence-init.php
// ============================================================

// Vérifier si le fichier de config existe déjà
if (file_exists(__DIR__ . '/config.php')) {
    die("
    <div class='error-container'>
        <h2>Installation déjà effectuée</h2>
        <p>Le site est déjà installé. Pour réinstaller, supprimez d'abord le fichier <code>config.php</code>.</p>
        <p><a href='../index.php' class='btn'>Retour à l'accueil</a></p>
    </div>
    <style>
        .error-container { max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px; }
    </style>
    ");
}

// Démarrer la session pour stocker les données temporaires
session_start();

// Fonction pour afficher l'en-tête du wizard
function displayHeader($step)
{
    $steps = [
        1 => 'Informations de l\'agence',
        2 => 'Informations du conseiller',
        3 => 'Configuration technique',
        4 => 'Villes ciblées & Stratégie',
        5 => 'Réseaux sociaux',
        6 => 'Confirmation & Génération',
    ];

    echo "<div class='wizard-header'>";
    echo "<h1>Installation de Pascal Hamm Immobilier</h1>";
    echo "<div class='progress-bar'>";
    for ($i = 1; $i <= 6; $i++) {
        $active = ($i == $step) ? 'active' : ($i < $step ? 'completed' : '');
        echo "<div class='step $active'>$i</div>";
        if ($i < 6) {
            echo "<div class='connector'></div>";
        }
    }
    echo "</div>";
    echo "<h2>{$steps[$step]}</h2>";
    echo "</div>";
}

// Fonction pour afficher un formulaire étape par étape
function displayStep($step)
{
    displayHeader($step);

    switch ($step) {
        case 1:
            return displayStep1();
        case 2:
            return displayStep2();
        case 3:
            return displayStep3();
        case 4:
            return displayStep4();
        case 5:
            return displayStep5();
        case 6:
            return displayStep6();
        default:
            return displayStep1();
    }
}

// ============================================================
// ÉTAPE 1 : Informations de base (agence)
// ============================================================
function displayStep1()
{
    $defaults = [
        'app_name' => 'Pascal Hamm Immobilier',
        'app_url' => 'pascal-hamm-immobilier-aix-en-provence.fr',
        'app_email' => 'contact@pascal-hamm-immobilier-aix-en-provence.fr',
        'app_phone' => '+33 4 42 27 00 00',
        'app_address' => '24 Av. Victor Hugo',
        'app_city' => 'Aix-en-Provence',
        'app_siret' => '80894761200024',
    ];
    ?>
    <form method="post" action="agence-init.php" class="install-form">
        <input type="hidden" name="step" value="2">

        <div class="form-section">
            <h3>Informations générales</h3>
            <div class="form-group">
                <label for="app_name">Nom du site*</label>
                <div class="input-with-ai">
                    <input type="text" id="app_name" name="app_name" value="<?php echo htmlspecialchars($defaults['app_name']); ?>" required>
                    <button type="button" class="ai-btn" onclick="generateSuggestion('app_name', 'Nom d\'une agence immobilière professionnelle en Provence')">✨ IA</button>
                </div>
            </div>

            <div class="form-group">
                <label for="app_url">URL du site (sans https://)*</label>
                <input type="text" id="app_url" name="app_url" value="<?php echo htmlspecialchars($defaults['app_url']); ?>" required>
                <small>Exemple: votresite.com</small>
            </div>

            <div class="form-group">
                <label for="app_email">Email de contact*</label>
                <div class="input-with-ai">
                    <input type="email" id="app_email" name="app_email" value="<?php echo htmlspecialchars($defaults['app_email']); ?>" required>
                    <button type="button" class="ai-btn" onclick="generateSuggestion('app_email', 'Email professionnel pour une agence immobilière')">✨ IA</button>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Coordonnées</h3>
            <div class="form-group">
                <label for="app_phone">Téléphone*</label>
                <input type="text" id="app_phone" name="app_phone" value="<?php echo htmlspecialchars($defaults['app_phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="app_address">Adresse*</label>
                <input type="text" id="app_address" name="app_address" value="<?php echo htmlspecialchars($defaults['app_address']); ?>" required>
            </div>

            <div class="form-group">
                <label for="app_city">Ville principale*</label>
                <input type="text" id="app_city" name="app_city" value="<?php echo htmlspecialchars($defaults['app_city']); ?>" required>
            </div>
        </div>

        <div class="form-section">
            <h3>Informations légales</h3>
            <div class="form-group">
                <label for="app_siret">Numéro SIRET*</label>
                <input type="text" id="app_siret" name="app_siret" value="<?php echo htmlspecialchars($defaults['app_siret']); ?>" required>
                <small>14 chiffres sans espaces</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
    <?php
}

// ============================================================
// ÉTAPE 2 : Informations du conseiller (Pascal Hamm)
// ============================================================
function displayStep2()
{
    $defaults = [
        'advisor_name' => 'Pascal Hamm',
        'advisor_carte' => 'CPI 13102 2017 0000001',
        'advisor_rsac' => 'RSAC 808947612',
        'advisor_bio' => 'Expert en immobilier depuis 20 ans, spécialisé dans les biens haut de gamme en Provence.',
    ];
    ?>
    <form method="post" action="agence-init.php" class="install-form">
        <input type="hidden" name="step" value="3">

        <div class="form-section">
            <h3>Informations du conseiller immobilier</h3>
            <div class="form-group">
                <label for="advisor_name">Nom complet*</label>
                <input type="text" id="advisor_name" name="advisor_name" value="<?php echo htmlspecialchars($defaults['advisor_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="advisor_carte">Numéro de carte professionnelle (CPI)*</label>
                <input type="text" id="advisor_carte" name="advisor_carte" value="<?php echo htmlspecialchars($defaults['advisor_carte']); ?>" required>
                <small>Format: CPI XXXXX XXXX XXXXXX</small>
            </div>

            <div class="form-group">
                <label for="advisor_rsac">Numéro RSAC*</label>
                <input type="text" id="advisor_rsac" name="advisor_rsac" value="<?php echo htmlspecialchars($defaults['advisor_rsac']); ?>" required>
                <small>Format: RSAC XXXXXXXXX</small>
            </div>

            <div class="form-group">
                <label for="advisor_bio">Bio professionnelle (pour la page "À propos")*</label>
                <div class="input-with-ai">
                    <textarea id="advisor_bio" name="advisor_bio" rows="4" required><?php echo htmlspecialchars($defaults['advisor_bio']); ?></textarea>
                    <button type="button" class="ai-btn" onclick="generateSuggestion('advisor_bio', 'Bio professionnelle pour un agent immobilier en Provence')">✨ IA</button>
                </div>
                <small>Exemple: "Expert en immobilier depuis 15 ans, spécialisé dans les biens d'exception à Aix-en-Provence."</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='agence-init.php?step=1'">Précédent</button>
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
    <?php
}

// ============================================================
// ÉTAPE 3 : Configuration technique
// ============================================================
function displayStep3()
{
    $defaults = [
        'root_path' => __DIR__,
        'app_env' => 'production',
        'app_debug' => 'false',
        'app_timezone' => 'Europe/Paris',
    ];
    ?>
    <form method="post" action="agence-init.php" class="install-form">
        <input type="hidden" name="step" value="4">

        <div class="form-section">
            <h3>Configuration technique</h3>
            <div class="form-group">
                <label for="root_path">Chemin racine du site*</label>
                <input type="text" id="root_path" name="root_path" value="<?php echo htmlspecialchars($defaults['root_path']); ?>" required>
                <small>Chemin absolu du dossier d'installation</small>
            </div>

            <div class="form-group">
                <label for="app_env">Environnement*</label>
                <select id="app_env" name="app_env" required>
                    <option value="production" <?php echo $defaults['app_env'] === 'production' ? 'selected' : ''; ?>>Production</option>
                    <option value="development" <?php echo $defaults['app_env'] === 'development' ? 'selected' : ''; ?>>Développement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="app_debug">Mode debug*</label>
                <select id="app_debug" name="app_debug" required>
                    <option value="false" <?php echo $defaults['app_debug'] === 'false' ? 'selected' : ''; ?>>Désactivé</option>
                    <option value="true" <?php echo $defaults['app_debug'] === 'true' ? 'selected' : ''; ?>>Activé</option>
                </select>
            </div>

            <div class="form-group">
                <label for="app_timezone">Fuseau horaire*</label>
                <select id="app_timezone" name="app_timezone" required>
                    <option value="Europe/Paris" <?php echo $defaults['app_timezone'] === 'Europe/Paris' ? 'selected' : ''; ?>>Europe/Paris</option>
                    <option value="UTC" <?php echo $defaults['app_timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='agence-init.php?step=2'">Précédent</button>
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
    <?php
}

// ============================================================
// ÉTAPE 4 : Villes ciblées & Stratégie de contenu
// ============================================================
function displayStep4()
{
    $defaultCities = [
        'Aix-en-Provence',
        'Marseille',
        'Avignon',
        'Toulon',
        'Nice',
        'Arles',
        'Nîmes',
        'Montpellier',
    ];

    $defaultStrategy = [
        'pillar' => 'Immobilier haut de gamme en Provence',
        'satellites' => [
            'Les quartiers premium d\'Aix-en-Provence',
            'Investir dans l\'immobilier à Marseille',
            'Les tendances du marché immobilier en 2024',
            'Comment bien estimer son bien ?',
            'Les erreurs à éviter lors d\'un achat immobilier',
        ],
    ];
    ?>
    <form method="post" action="agence-init.php" class="install-form">
        <input type="hidden" name="step" value="5">

        <div class="form-section">
            <h3>Villes ciblées en priorité</h3>
            <div class="form-group">
                <label>Sélectionnez les villes où vous êtes le plus actif*</label>
                <div class="cities-grid">
                    <?php foreach ($defaultCities as $city): ?>
                        <div class="city-checkbox">
                            <input type="checkbox" id="city_<?php echo strtolower(str_replace(' ', '_', $city)); ?>" name="target_cities[]" value="<?php echo htmlspecialchars($city); ?>" checked>
                            <label for="city_<?php echo strtolower(str_replace(' ', '_', $city)); ?>"><?php echo htmlspecialchars($city); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label for="other_cities">Autres villes (séparées par des virgules)</label>
                    <input type="text" id="other_cities" name="other_cities" placeholder="Ex: Cassis, La Ciotat">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Stratégie de contenu SEO (méthode Silos)</h3>
            <div class="form-group">
                <label>Structure des silos thématiques*</label>
                <div class="silos-container">
                    <div class="silo">
                        <input type="text" name="silos[transaction][name]" value="Transaction" placeholder="Nom du silo">
                        <div class="silo-topics">
                            <input type="text" name="silos[transaction][topics][]" value="Achat" placeholder="Sujet">
                            <input type="text" name="silos[transaction][topics][]" value="Vente" placeholder="Sujet">
                            <input type="text" name="silos[transaction][topics][]" value="Location" placeholder="Sujet">
                            <button type="button" class="add-topic-btn" onclick="addTopic(this)">+ Ajouter un sujet</button>
                        </div>
                    </div>
                    <div class="silo">
                        <input type="text" name="silos[gestion][name]" value="Gestion" placeholder="Nom du silo">
                        <div class="silo-topics">
                            <input type="text" name="silos[gestion][topics][]" value="Gestion locative" placeholder="Sujet">
                            <input type="text" name="silos[gestion][topics][]" value="Syndic de copropriété" placeholder="Sujet">
                            <button type="button" class="add-topic-btn" onclick="addTopic(this)">+ Ajouter un sujet</button>
                        </div>
                    </div>
                    <div class="silo">
                        <input type="text" name="silos[conseil][name]" value="Conseil" placeholder="Nom du silo">
                        <div class="silo-topics">
                            <input type="text" name="silos[conseil][topics][]" value="Investissement" placeholder="Sujet">
                            <input type="text" name="silos[conseil][topics][]" value="Estimation" placeholder="Sujet">
                            <button type="button" class="add-topic-btn" onclick="addTopic(this)">+ Ajouter un sujet</button>
                        </div>
                    </div>
                    <button type="button" class="add-silo-btn" onclick="addSilo()">+ Ajouter un silo</button>
                </div>
            </div>

            <div class="form-group">
                <label for="pillar_topic">Sujet du pilier métier (article principal)*</label>
                <div class="input-with-ai">
                    <input type="text" id="pillar_topic" name="pillar_topic" value="<?php echo htmlspecialchars($defaultStrategy['pillar']); ?>" required>
                    <button type="button" class="ai-btn" onclick="generateSuggestion('pillar_topic', 'Sujet principal pour une stratégie SEO immobilière en Provence')">✨ IA</button>
                </div>
                <small>Exemple: "Immobilier haut de gamme en Provence : Guide complet pour investisseurs"</small>
            </div>

            <div class="form-group">
                <label>Articles satellites (5 recommandés pour une bonne stratégie SEO)</label>
                <div class="satellites-container">
                    <?php foreach ($defaultStrategy['satellites'] as $satellite): ?>
                        <div class="satellite-input">
                            <input type="text" name="satellites[]" value="<?php echo htmlspecialchars($satellite); ?>" placeholder="Titre de l'article satellite">
                            <button type="button" class="ai-btn" onclick="generateSatelliteSuggestion(this)">✨ IA</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-satellite-btn" onclick="addSatellite()">+ Ajouter un satellite</button>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='agence-init.php?step=3'">Précédent</button>
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
    <?php
}

// ============================================================
// ÉTAPE 5 : Réseaux sociaux
// ============================================================
function displayStep5()
{
    ?>
    <form method="post" action="agence-init.php" class="install-form">
        <input type="hidden" name="step" value="6">

        <div class="form-section">
            <h3>Réseaux sociaux</h3>
            <div class="form-group"><label for="facebook_url">Facebook</label><input type="url" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/votrepage"></div>
            <div class="form-group"><label for="instagram_url">Instagram</label><input type="url" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/votrepage"></div>
            <div class="form-group"><label for="linkedin_url">LinkedIn</label><input type="url" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/votreprofil"></div>
            <div class="form-group"><label for="youtube_url">YouTube</label><input type="url" id="youtube_url" name="youtube_url" placeholder="https://youtube.com/c/votrechaine"></div>
            <div class="form-group"><label for="twitter_url">Twitter/X</label><input type="url" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/votrecompte"></div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='agence-init.php?step=4'">Précédent</button>
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
    <?php
}

function displayStep6()
{
    $data = $_SESSION['install_data'] ?? [];

    $errors = [];
    if (empty($data['app_name'])) {
        $errors[] = "Le nom du site est requis";
    }
    if (empty($data['app_url'])) {
        $errors[] = "L'URL du site est requise";
    }
    if (empty($data['app_email']) || !filter_var($data['app_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    if (empty($data['target_cities'])) {
        $errors[] = "Au moins une ville doit être sélectionnée";
    }
    if (empty($data['silos'])) {
        $errors[] = "Au moins un silo doit être défini";
    }

    if (!empty($errors)) {
        echo "<div class='error-container'><h3>Erreurs de validation</h3><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul><button class='btn btn-secondary' onclick='window.history.back()'>Corriger</button></div>";
        return;
    }

    $calendar = generateEditorialCalendar($data);
    ?>
    <div class="confirmation-container">
        <h3>Résumé de la configuration</h3>
        <p>Veuillez vérifier les informations ci-dessous avant de finaliser l'installation.</p>

        <div class="config-tabs">
            <div class="tab-header">
                <button class="tab-btn active" onclick="showTab(event, 'agence')">Agence</button>
                <button class="tab-btn" onclick="showTab(event, 'conseiller')">Conseiller</button>
                <button class="tab-btn" onclick="showTab(event, 'villes')">Villes ciblées</button>
                <button class="tab-btn" onclick="showTab(event, 'strategie')">Stratégie SEO</button>
                <button class="tab-btn" onclick="showTab(event, 'social')">Réseaux</button>
                <button class="tab-btn" onclick="showTab(event, 'calendrier')">Calendrier</button>
            </div>

            <div class="tab-content">
                <div id="agence" class="tab-pane active"><h4>Informations de l'agence</h4>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($data['app_name']); ?></p>
                    <p><strong>URL:</strong> https://<?php echo htmlspecialchars($data['app_url']); ?></p>
                </div>
                <div id="conseiller" class="tab-pane"><h4>Informations du conseiller</h4>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($data['advisor_name']); ?></p>
                </div>
                <div id="villes" class="tab-pane"><h4>Villes ciblées en priorité</h4><ul><?php foreach ($data['target_cities'] as $city): ?><li><?php echo htmlspecialchars($city); ?></li><?php endforeach; ?></ul></div>
                <div id="strategie" class="tab-pane"><h4>Stratégie de contenu SEO</h4>
                    <?php foreach ($data['silos'] as $siloName => $topics): ?><div class="strategy-silo"><h6><?php echo htmlspecialchars($siloName); ?></h6><ul><?php foreach ($topics as $topic): ?><li><?php echo htmlspecialchars($topic); ?></li><?php endforeach; ?></ul></div><?php endforeach; ?>
                </div>
                <div id="social" class="tab-pane"><h4>Réseaux sociaux</h4><p>Configurés lors de l'étape 5.</p></div>
                <div id="calendrier" class="tab-pane"><h4>Calendrier éditorial (3 prochains mois)</h4><div class="calendar-container">
                <?php foreach ($calendar as $month => $articles): ?><div class="calendar-month"><h5><?php echo htmlspecialchars($month); ?></h5><table><thead><tr><th>Date</th><th>Type</th><th>Titre</th><th>Ville ciblée</th></tr></thead><tbody><?php foreach ($articles as $article): ?><tr><td><?php echo htmlspecialchars($article['date']); ?></td><td><?php echo htmlspecialchars($article['type']); ?></td><td><?php echo htmlspecialchars($article['title']); ?></td><td><?php echo htmlspecialchars($article['city']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endforeach; ?>
                </div></div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='agence-init.php?step=5'">Précédent</button>
            <form method="post" action="agence-init.php" style="display: inline;">
                <input type="hidden" name="step" value="7">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-success">Finaliser l'installation</button>
            </form>
        </div>
    </div>
    <?php
}

function generateEditorialCalendar($data)
{
    $calendar = [];
    $startDate = new DateTime();

    $cities = $data['target_cities'];
    $silos = $data['silos'];
    $pillarTopic = $data['pillar_topic'];
    $satellites = $data['satellites'];

    $pillarDate = (clone $startDate)->modify('+15 days')->format('Y-m-d');
    $calendar[$startDate->format('F Y')][] = [
        'date' => $pillarDate,
        'type' => 'Pilier',
        'title' => $pillarTopic,
        'city' => $cities[0],
    ];

    $currentDate = (clone $startDate)->modify('+7 days');
    foreach ($satellites as $i => $satellite) {
        $satelliteDate = clone $currentDate;
        $city = $cities[$i % count($cities)];
        $month = $satelliteDate->format('F Y');
        $calendar[$month][] = [
            'date' => $satelliteDate->format('Y-m-d'),
            'type' => 'Satellite',
            'title' => $satellite,
            'city' => $city,
        ];
        $currentDate->modify('+14 days');
    }

    $currentDate = (clone $startDate)->modify('+21 days');
    $idx = 0;
    foreach ($silos as $siloName => $topics) {
        foreach ($topics as $topic) {
            $siloDate = clone $currentDate;
            $month = $siloDate->format('F Y');
            $city = $cities[$idx % count($cities)];
            $calendar[$month][] = [
                'date' => $siloDate->format('Y-m-d'),
                'type' => 'Silo: ' . $siloName,
                'title' => $topic . ' à ' . $city,
                'city' => $city,
            ];
            $idx++;
            $currentDate->modify('+7 days');
        }
    }

    foreach ($calendar as &$monthArticles) {
        usort($monthArticles, static function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });
    }

    return $calendar;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)($_POST['step'] ?? 1);

    if (!isset($_SESSION['install_data'])) {
        $_SESSION['install_data'] = [];
    }

    switch ($step) {
        case 2:
            $_SESSION['install_data']['app_name'] = trim($_POST['app_name']);
            $_SESSION['install_data']['app_url'] = trim(str_replace(['http://', 'https://'], '', $_POST['app_url']));
            $_SESSION['install_data']['app_email'] = trim($_POST['app_email']);
            $_SESSION['install_data']['app_phone'] = trim($_POST['app_phone']);
            $_SESSION['install_data']['app_address'] = trim($_POST['app_address']);
            $_SESSION['install_data']['app_city'] = trim($_POST['app_city']);
            $_SESSION['install_data']['app_siret'] = trim($_POST['app_siret']);
            break;
        case 3:
            $_SESSION['install_data']['advisor_name'] = trim($_POST['advisor_name']);
            $_SESSION['install_data']['advisor_carte'] = trim($_POST['advisor_carte']);
            $_SESSION['install_data']['advisor_rsac'] = trim($_POST['advisor_rsac']);
            $_SESSION['install_data']['advisor_bio'] = trim($_POST['advisor_bio']);
            break;
        case 4:
            $_SESSION['install_data']['root_path'] = trim($_POST['root_path']);
            $_SESSION['install_data']['app_env'] = $_POST['app_env'];
            $_SESSION['install_data']['app_debug'] = $_POST['app_debug'];
            $_SESSION['install_data']['app_timezone'] = $_POST['app_timezone'];
            break;
        case 5:
            $targetCities = $_POST['target_cities'] ?? [];
            if (!empty($_POST['other_cities'])) {
                $otherCities = array_filter(array_map('trim', explode(',', $_POST['other_cities'])));
                $targetCities = array_merge($targetCities, $otherCities);
            }
            $_SESSION['install_data']['target_cities'] = array_values(array_unique($targetCities));

            $silos = [];
            foreach (($_POST['silos'] ?? []) as $siloData) {
                $siloName = trim($siloData['name'] ?? '');
                if ($siloName !== '') {
                    $topics = array_values(array_filter(array_map('trim', $siloData['topics'] ?? [])));
                    if (!empty($topics)) {
                        $silos[$siloName] = $topics;
                    }
                }
            }
            $_SESSION['install_data']['silos'] = $silos;
            $_SESSION['install_data']['pillar_topic'] = trim($_POST['pillar_topic'] ?? '');
            $_SESSION['install_data']['satellites'] = array_values(array_filter(array_map('trim', $_POST['satellites'] ?? [])));
            break;
        case 6:
            foreach (['facebook_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'twitter_url'] as $socialKey) {
                $_SESSION['install_data'][$socialKey] = trim($_POST[$socialKey] ?? '');
            }
            break;
        case 7:
            if (isset($_POST['confirm']) && $_POST['confirm'] == 1) {
                $configPath = __DIR__ . '/config.php';
                $data = $_SESSION['install_data'];
                $date = date('d/m/Y H:i');

                $configContent = "<?php\n/**\n * Fichier de configuration - Pascal Hamm Immobilier\n * Généré automatiquement par le wizard d'installation\n * Date: {$date}\n */\n";
                $configContent .= "define('APP_NAME', '" . addslashes($data['app_name']) . "');\n";
                $configContent .= "define('APP_URL', 'https://" . addslashes($data['app_url']) . "');\n";
                file_put_contents($configPath, $configContent);

                $strategyContent = generateSeoStrategyFile($data);
                file_put_contents(__DIR__ . '/strategy.php', $strategyContent);

                $htaccessContent = "# Protection du dossier d'installation\nOptions -Indexes\n<FilesMatch \\\"\\.(php|html|inc)$\\\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>\n";
                file_put_contents(__DIR__ . '/.htaccess', $htaccessContent);

                header('Location: ../index.php');
                exit();
            }
            break;
    }
}

function generateSeoStrategyFile($data)
{
    $date = date('d/m/Y H:i');
    $content = "<?php\n/**\n * Stratégie SEO - Pascal Hamm Immobilier\n * Généré automatiquement par le wizard d'installation\n * Date: {$date}\n */\n\nreturn " . var_export([
        'cities' => $data['target_cities'],
        'strategy' => [
            'silos' => $data['silos'],
            'pillar' => $data['pillar_topic'],
            'satellites' => $data['satellites'],
        ],
        'calendar' => generateEditorialCalendar($data),
    ], true) . ";\n";

    return $content;
}

$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : (isset($_POST['step']) ? (int)$_POST['step'] : 1);
displayStep($currentStep);
?>

<script>
function showTab(event, tabName) {
    document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
}

function generateSuggestion(fieldId, prompt) {
    const field = document.getElementById(fieldId);
    const currentValue = field.value;
    let suggestion = '';
    switch(fieldId) {
        case 'app_name':
            suggestion = 'Agence Immobilière ' + (currentValue || 'Pascal Hamm') + ' - ' + document.getElementById('app_city').value;
            break;
        case 'app_email':
            suggestion = 'contact@' + document.getElementById('app_url').value;
            break;
        case 'advisor_bio':
            suggestion = 'Expert en immobilier depuis ' + (Math.floor(Math.random() * 10) + 10) + " ans, spécialisé dans les biens d'exception à " + document.getElementById('app_city').value + '.';
            break;
        case 'pillar_topic':
            suggestion = 'Immobilier à ' + document.getElementById('app_city').value + ' : Guide complet pour ' + (Math.random() > 0.5 ? 'acheteurs' : 'investisseurs');
            break;
        default:
            suggestion = 'Suggestion pour ' + prompt;
    }

    if (confirm('Voici une suggestion générée par IA:\n\n' + suggestion + '\n\nVoulez-vous l\'utiliser ?')) {
        field.value = suggestion;
    }
}

function generateSatelliteSuggestion(button) {
    const input = button.previousElementSibling;
    const silos = <?php echo json_encode($_SESSION['install_data']['silos'] ?? []); ?>;
    const cities = <?php echo json_encode($_SESSION['install_data']['target_cities'] ?? []); ?>;
    const siloNames = Object.keys(silos);

    if (siloNames.length === 0 || cities.length === 0) {
        alert('Renseignez d\'abord les silos et les villes ciblées.');
        return;
    }

    const randomSilo = siloNames[Math.floor(Math.random() * siloNames.length)];
    const randomCity = cities[Math.floor(Math.random() * cities.length)];
    const topics = silos[randomSilo] || [];
    const randomTopic = topics[Math.floor(Math.random() * topics.length)] || 'immobilier';

    const suggestions = [
        'Les meilleurs quartiers pour ' + randomTopic + ' à ' + randomCity,
        'Comment ' + randomTopic.toLowerCase() + ' à ' + randomCity + ' en 2026 ?',
        randomTopic + ' à ' + randomCity + ' : ce que vous devez savoir'
    ];

    const suggestion = suggestions[Math.floor(Math.random() * suggestions.length)];
    if (confirm('Voici une suggestion d\'article satellite:\n\n' + suggestion + '\n\nVoulez-vous l\'utiliser ?')) {
        input.value = suggestion;
    }
}

function addTopic(button) {
    const container = button.parentElement;
    const nameInput = button.parentElement.parentElement.querySelector('input[type="text"]');
    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = nameInput.name.replace('[name]', '[topics][]');
    newInput.placeholder = 'Sujet';
    container.insertBefore(newInput, button);
}

function addSilo() {
    const container = document.querySelector('.silos-container');
    const siloCount = container.querySelectorAll('.silo').length;
    const newSilo = document.createElement('div');
    newSilo.className = 'silo';
    newSilo.innerHTML = `<input type="text" name="silos[silo${siloCount}][name]" placeholder="Nom du silo"><div class="silo-topics"><input type="text" name="silos[silo${siloCount}][topics][]" placeholder="Sujet"><button type="button" class="add-topic-btn" onclick="addTopic(this)">+ Ajouter un sujet</button></div>`;
    container.insertBefore(newSilo, container.querySelector('.add-silo-btn'));
}

function addSatellite() {
    const container = document.querySelector('.satellites-container');
    const newInput = document.createElement('div');
    newInput.className = 'satellite-input';
    newInput.innerHTML = `<input type="text" name="satellites[]" placeholder="Titre de l'article satellite"><button type="button" class="ai-btn" onclick="generateSatelliteSuggestion(this)">✨ IA</button>`;
    container.appendChild(newInput);
}
</script>

<style>
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;line-height:1.6;color:#333;background-color:#f5f7fa;margin:0;padding:0}
.wizard-header{background-color:#2c3e50;color:white;padding:20px;text-align:center;margin-bottom:30px}
.install-form,.confirmation-container{background:white;padding:30px;border-radius:4px;box-shadow:0 2px 10px rgba(0,0,0,.1);max-width:1000px;margin:0 auto 30px}
.form-actions{display:flex;justify-content:space-between;margin-top:30px}
.btn{padding:12px 25px;border:none;border-radius:4px;font-size:16px;font-weight:600;cursor:pointer}
.btn-primary{background:#3498db;color:#fff}.btn-secondary{background:#bdc3c7}.btn-success{background:#27ae60;color:#fff}
.input-with-ai,.satellite-input{display:flex;gap:10px}.cities-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
.tab-pane{display:none}.tab-pane.active{display:block}
</style>
