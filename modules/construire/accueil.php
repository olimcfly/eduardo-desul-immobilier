<?php
$allowedActions = ['index', 'ancre', 'profils', 'offre', 'zone', 'synthese', 'actions'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z_-]/', '', (string)$_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) $action = 'index';

$actionTitles = [
    'ancre'    => 'Méthode ANCRE+ — Positionnement',
    'profils'  => 'NeuroPersona — Profils Clients',
    'offre'    => 'Offre Conseiller — Formulation',
    'zone'     => 'Zone de Prospection',
    'synthese' => 'Synthèse Stratégique',
    'actions'  => 'Actions du Jour',
];

$pageTitle       = $action === 'index' ? 'Construire' : ($actionTitles[$action] ?? 'Construire');
$pageDescription = 'Posez les bases solides de votre activité';

function renderContent()
{
    global $action;

    if ($action !== 'index') {
        $file = __DIR__ . '/' . $action . '.php';
        if (is_file($file)) {
            include $file;
            return;
        }
    }
    ?>
    <div class="page-header">
        <h1><i class="fas fa-layer-group page-icon"></i> HUB<span class="page-title-accent">Construire</span></h1>
        <p>Posez les bases solides de votre activité avec Noah IA</p>
    </div>

    <div class="noah-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;margin-top:1.5rem;">

        <?php
        $cards = [
            ['id'=>'positionnement','title'=>'Méthode ANCRE+','desc'=>"Générez 3 formulations d'accroche claires.",'icon'=>'fa-anchor','color'=>'#e74c3c','href'=>'?module=construire&action=ancre'],
            ['id'=>'profils','title'=>'NeuroPersona','desc'=>'Identifiez vos 3 profils clients prioritaires.','icon'=>'fa-brain','color'=>'#3498db','href'=>'?module=construire&action=profils'],
            ['id'=>'offre','title'=>'Offre Conseiller','desc'=>'Construisez votre pitch en 3 versions.','icon'=>'fa-briefcase','color'=>'#27ae60','href'=>'?module=construire&action=offre'],
            ['id'=>'zone','title'=>'Zone de Prospection','desc'=>'Délimitez votre territoire en 3 niveaux.','icon'=>'fa-map-marked-alt','color'=>'#8e44ad','href'=>'?module=construire&action=zone'],
            ['id'=>'synthese','title'=>'Synthèse Stratégique','desc'=>'Résumé de votre situation en 100 mots.','icon'=>'fa-layer-group','color'=>'#e67e22','href'=>'?module=construire&action=synthese'],
            ['id'=>'actions','title'=>'Actions du Jour','desc'=>'3 à 5 actions concrètes pour aujourd\'hui.','icon'=>'fa-bolt','color'=>'#16a085','href'=>'?module=construire&action=actions'],
        ];
        foreach ($cards as $c): ?>
        <div style="background:#1a3c5e;border-radius:14px;border:1.5px solid rgba(255,255,255,.07);padding:1.25rem;">
            <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:.75rem;">
                <div style="width:40px;height:40px;border-radius:10px;background:<?= $c['color'] ?>22;display:flex;align-items:center;justify-content:center;color:<?= $c['color'] ?>;">
                    <i class="fas <?= $c['icon'] ?>"></i>
                </div>
                <strong style="color:#e8f1f9;"><?= $c['title'] ?></strong>
            </div>
            <p style="color:#7a9ab8;font-size:.85rem;margin:0 0 1rem;"><?= $c['desc'] ?></p>
            <a href="<?= $c['href'] ?>" style="display:inline-flex;align-items:center;gap:.4rem;background:<?= $c['color'] ?>;color:#fff;padding:.5rem 1rem;border-radius:8px;font-size:.85rem;font-weight:700;text-decoration:none;">
                <i class="fas fa-arrow-right"></i> Accéder
            </a>
        </div>
        <?php endforeach; ?>

    </div>
    <?php
}
