<?php
$pageTitle = 'Landing Pages Ads';
$pageDescription = 'Créez des LP conformes Google Ads pour estimation et financement.';

$pdo = db();
$pdo->exec('CREATE TABLE IF NOT EXISTS landing_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_id INT UNSIGNED NOT NULL DEFAULT 1,
    slug VARCHAR(160) NOT NULL,
    type ENUM("estimation", "financement") NOT NULL DEFAULT "estimation",
    headline VARCHAR(255) NOT NULL,
    sous_titre VARCHAR(255) NOT NULL,
    ville VARCHAR(120) NOT NULL,
    advisor_name VARCHAR(180) NOT NULL DEFAULT "",
    advisor_phone VARCHAR(40) NOT NULL DEFAULT "",
    advisor_zone VARCHAR(255) NOT NULL DEFAULT "",
    advisor_photo_webp VARCHAR(255) NOT NULL DEFAULT "",
    advisor_bio TEXT NULL,
    company_name VARCHAR(180) NOT NULL DEFAULT "",
    legal_url VARCHAR(255) NOT NULL DEFAULT "/mentions-legales",
    privacy_url VARCHAR(255) NOT NULL DEFAULT "/politique-confidentialite",
    review_1_firstname VARCHAR(80) NULL,
    review_1_city VARCHAR(120) NULL,
    review_1_text VARCHAR(255) NULL,
    review_2_firstname VARCHAR(80) NULL,
    review_2_city VARCHAR(120) NULL,
    review_2_text VARCHAR(255) NULL,
    utm_source_default VARCHAR(40) NOT NULL DEFAULT "google",
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_landing_page_slug (website_id, slug),
    KEY idx_landing_page_active (website_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$websiteId = 1;
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $editId > 0;
$errors = [];

$defaults = [
    'slug' => '', 'type' => 'estimation', 'headline' => '', 'sous_titre' => '', 'ville' => '',
    'advisor_name' => trim((string)(setting('advisor_firstname', '') . ' ' . setting('advisor_lastname', ''))),
    'advisor_phone' => (string)setting('advisor_phone', ''),
    'advisor_zone' => (string)setting('zone_city', ''),
    'advisor_photo_webp' => '', 'advisor_bio' => (string)setting('advisor_bio', ''),
    'company_name' => (string)setting('agency_name', ''),
    'legal_url' => '/mentions-legales', 'privacy_url' => '/politique-confidentialite',
    'review_1_firstname' => '', 'review_1_city' => '', 'review_1_text' => '',
    'review_2_firstname' => '', 'review_2_city' => '', 'review_2_text' => '',
    'utm_source_default' => 'google', 'active' => 1,
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM landing_pages WHERE id = ? AND website_id = ? LIMIT 1');
    $stmt->execute([$editId, $websiteId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $defaults = array_merge($defaults, $row);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $defaults;
    foreach (array_keys($defaults) as $key) {
        if ($key === 'active') {
            $data[$key] = !empty($_POST['active']) ? 1 : 0;
            continue;
        }
        if (!isset($_POST[$key])) {
            continue;
        }
        $data[$key] = trim((string)$_POST[$key]);
    }

    $data['slug'] = strtolower((string)preg_replace('/[^a-z0-9-]+/', '-', (string)$data['slug']));
    $data['slug'] = trim((string)$data['slug'], '-');

    if ($data['slug'] === '') {
        $errors[] = 'Le slug est obligatoire.';
    }
    if (!in_array($data['type'], ['estimation', 'financement'], true)) {
        $errors[] = 'Le type doit être estimation ou financement.';
    }
    if ($data['headline'] === '' || $data['sous_titre'] === '') {
        $errors[] = 'Headline et sous-titre sont obligatoires.';
    }
    if (!in_array($data['utm_source_default'], ['google', 'facebook'], true)) {
        $errors[] = 'UTM source par défaut invalide.';
    }

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE landing_pages SET
                slug=:slug, type=:type, headline=:headline, sous_titre=:sous_titre, ville=:ville,
                advisor_name=:advisor_name, advisor_phone=:advisor_phone, advisor_zone=:advisor_zone, advisor_photo_webp=:advisor_photo_webp,
                advisor_bio=:advisor_bio, company_name=:company_name, legal_url=:legal_url, privacy_url=:privacy_url,
                review_1_firstname=:review_1_firstname, review_1_city=:review_1_city, review_1_text=:review_1_text,
                review_2_firstname=:review_2_firstname, review_2_city=:review_2_city, review_2_text=:review_2_text,
                utm_source_default=:utm_source_default, active=:active
                WHERE id=:id AND website_id=:website_id';
            $data['id'] = $editId;
        } else {
            $sql = 'INSERT INTO landing_pages
                (website_id, slug, type, headline, sous_titre, ville, advisor_name, advisor_phone, advisor_zone, advisor_photo_webp, advisor_bio, company_name, legal_url, privacy_url,
                 review_1_firstname, review_1_city, review_1_text, review_2_firstname, review_2_city, review_2_text, utm_source_default, active)
                VALUES
                (:website_id, :slug, :type, :headline, :sous_titre, :ville, :advisor_name, :advisor_phone, :advisor_zone, :advisor_photo_webp, :advisor_bio, :company_name, :legal_url, :privacy_url,
                 :review_1_firstname, :review_1_city, :review_1_text, :review_2_firstname, :review_2_city, :review_2_text, :utm_source_default, :active)';
        }

        $data['website_id'] = $websiteId;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        header('Location: /admin/?module=landing-pages&saved=1');
        exit;
    }

    $defaults = $data;
}

$listStmt = $pdo->prepare('SELECT id, slug, type, headline, ville, active, updated_at FROM landing_pages WHERE website_id = ? ORDER BY updated_at DESC');
$listStmt->execute([$websiteId]);
$landingPages = $listStmt->fetchAll(PDO::FETCH_ASSOC);

function renderContent(): void
{
    global $landingPages, $defaults, $errors, $isEdit;
    ?>
    <style>
        .start-hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:16px;padding:36px 40px;color:#fff;margin-bottom:32px;box-shadow:0 4px 20px rgba(15,34,55,.18)}
        .start-hero-badge{display:inline-block;background:rgba(201,168,76,.2);color:#c9a84c;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:14px;border:1px solid rgba(201,168,76,.35)}
        .start-hero h1{font-size:28px;font-weight:700;color:#fff;margin:0 0 12px;line-height:1.25}
        .start-hero p{font-size:15px;color:rgba(255,255,255,.7);line-height:1.65;max-width:680px;margin:0}
        .lp-grid{display:grid;gap:1rem;grid-template-columns:1.1fr .9fr;align-items:start}
        .lp-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem;box-shadow:0 10px 26px rgba(15,23,42,.06)}
        .lp-table{width:100%;border-collapse:collapse}.lp-table th,.lp-table td{padding:.55rem;border-bottom:1px solid #f1f5f9;text-align:left}
        .lp-form{display:grid;gap:.55rem}.lp-form label{font-size:.92rem;color:#334155}.lp-form input,.lp-form select,.lp-form textarea{width:100%;padding:.55rem .65rem;border:1px solid #cbd5e1;border-radius:10px}
        .lp-cols{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}.lp-actions{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap}
        .btn{display:inline-block;background:#0f766e;color:#fff;border:none;border-radius:10px;padding:.6rem .9rem;text-decoration:none;font-weight:600}
        .start-cta{background:#fff;border-radius:12px;padding:24px 26px;box-shadow:0 1px 6px rgba(0,0,0,.07);display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;margin-top:16px}
        .start-cta-text strong{display:block;font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px}
        .start-cta-text span{font-size:13px;color:#64748b}
        .start-cta-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;background:#c9a84c;color:#0f2237;border-radius:8px;font-size:14px;font-weight:700;text-decoration:none;white-space:nowrap}
        @media (max-width:1000px){.lp-grid{grid-template-columns:1fr}.lp-cols{grid-template-columns:1fr}}
        @media (max-width:600px){.start-hero{padding:24px 20px}}
    </style>

    <div class="start-hero">
        <div class="start-hero-badge">Acquisition Ads</div>
        <h1>Landing Pages Google Ads</h1>
        <p>LP multi-sites, conformes RGPD et prêtes pour le Quality Score.</p>
    </div>

    <div class="lp-grid">
        <section class="lp-card">
            <h3 style="margin-top:0">LP existantes</h3>
            <table class="lp-table">
                <thead><tr><th>Slug</th><th>Type</th><th>Ville</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                <?php if (!$landingPages): ?><tr><td colspan="5">Aucune landing page.</td></tr><?php endif; ?>
                <?php foreach ($landingPages as $lp): ?>
                    <tr>
                        <td>/lp/<?= e((string)$lp['slug']) ?></td>
                        <td><?= e((string)$lp['type']) ?></td>
                        <td><?= e((string)$lp['ville']) ?></td>
                        <td><?= ((int)$lp['active'] === 1) ? 'Active' : 'Inactive' ?></td>
                        <td><a href="/admin/?module=landing-pages&id=<?= (int)$lp['id'] ?>">Éditer</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="lp-card">
            <h3 style="margin-top:0"><?= $isEdit ? 'Modifier la LP' : 'Créer une LP' ?></h3>
            <?php foreach ($errors as $error): ?><p style="color:#b91c1c;margin:.2rem 0"><?= e((string)$error) ?></p><?php endforeach; ?>
            <form class="lp-form" method="post">
                <div class="lp-cols">
                    <label>Slug<input name="slug" required value="<?= e((string)$defaults['slug']) ?>"></label>
                    <label>Type<select name="type" required><option value="estimation" <?= $defaults['type']==='estimation'?'selected':'' ?>>Estimation</option><option value="financement" <?= $defaults['type']==='financement'?'selected':'' ?>>Financement</option></select></label>
                </div>
                <label>Headline H1<input name="headline" required value="<?= e((string)$defaults['headline']) ?>"></label>
                <label>Sous-titre<input name="sous_titre" required value="<?= e((string)$defaults['sous_titre']) ?>"></label>
                <label>Ville<input name="ville" value="<?= e((string)$defaults['ville']) ?>"></label>
                <div class="lp-cols">
                    <label>Nom conseiller<input name="advisor_name" value="<?= e((string)$defaults['advisor_name']) ?>"></label>
                    <label>Téléphone<input name="advisor_phone" value="<?= e((string)$defaults['advisor_phone']) ?>"></label>
                </div>
                <label>Zone d'intervention<input name="advisor_zone" value="<?= e((string)$defaults['advisor_zone']) ?>"></label>
                <label>Photo conseiller WebP (URL ou chemin)<input name="advisor_photo_webp" value="<?= e((string)$defaults['advisor_photo_webp']) ?>"></label>
                <label>Bio courte<textarea rows="3" name="advisor_bio"><?= e((string)$defaults['advisor_bio']) ?></textarea></label>
                <div class="lp-cols">
                    <label>Avis client 1 prénom<input name="review_1_firstname" value="<?= e((string)$defaults['review_1_firstname']) ?>"></label>
                    <label>Avis client 1 ville<input name="review_1_city" value="<?= e((string)$defaults['review_1_city']) ?>"></label>
                </div>
                <label>Avis client 1 texte<textarea rows="2" name="review_1_text"><?= e((string)$defaults['review_1_text']) ?></textarea></label>
                <div class="lp-cols">
                    <label>Avis client 2 prénom<input name="review_2_firstname" value="<?= e((string)$defaults['review_2_firstname']) ?>"></label>
                    <label>Avis client 2 ville<input name="review_2_city" value="<?= e((string)$defaults['review_2_city']) ?>"></label>
                </div>
                <label>Avis client 2 texte<textarea rows="2" name="review_2_text"><?= e((string)$defaults['review_2_text']) ?></textarea></label>
                <div class="lp-cols">
                    <label>UTM source par défaut<select name="utm_source_default"><option value="google" <?= $defaults['utm_source_default']==='google'?'selected':'' ?>>google</option><option value="facebook" <?= $defaults['utm_source_default']==='facebook'?'selected':'' ?>>facebook</option></select></label>
                    <label>Active <input type="checkbox" name="active" value="1" <?= !empty($defaults['active']) ? 'checked' : '' ?>></label>
                </div>
                <div class="lp-actions">
                    <button class="btn" type="submit">Enregistrer</button>
                    <a href="/admin/?module=landing-pages">Nouvelle fiche</a>
                </div>
            </form>
        </section>
    </div>

    <div class="start-cta">
        <div class="start-cta-text">
            <strong>Créer une page prête à diffuser</strong>
            <span>Renseignez le ciblage, le conseiller, les avis et les URLs légales avant activation.</span>
        </div>
        <a href="/admin/?module=landing-pages" class="start-cta-btn"><i class="fas fa-plus"></i> Nouvelle LP</a>
    </div>
    <?php
}
