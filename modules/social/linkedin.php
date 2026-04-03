<?php
require_once __DIR__ . '/includes/_bootstrap.php';
$pageTitle = 'Social LinkedIn';
$pageDescription = 'Gestion LinkedIn';
require_once __DIR__ . '/../../admin/views/layout.php';

function renderContent()
{
    ?>
    <link rel="stylesheet" href="/modules/social/assets/social.css">
    <section class="social-page">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &gt; <a href="/admin/?module=social">Social</a> &gt; LinkedIn</div>
        <h1>LinkedIn</h1>
        <div class="tabs"><button class="active" data-tab="create">Créer une publication</button><button data-tab="posts">Mes publications LI</button><button data-tab="stats">Statistiques LinkedIn</button></div>
        <div class="tab active" id="tab-create">
            <form id="li-create-form"><?= csrfField() ?>
                <label>Type <select name="type_post"><option>post</option><option>article</option></select></label>
                <label>Titre (article) <input type="text" name="titre" maxlength="300"></label>
                <label>Contenu <textarea id="li-content" name="contenu" maxlength="3000"></textarea><small id="li-count">0 / 3000</small></label>
                <label>Visibilité <select name="visibility"><option value="PUBLIC">Tout le monde</option><option value="CONNECTIONS">Relations seulement</option></select></label>
            </form>
        </div>
        <div class="tab" id="tab-posts"><div id="li-feed"></div></div>
        <div class="tab" id="tab-stats"><canvas id="li-chart"></canvas></div>
    </section>
    <script src="/modules/social/assets/social.js"></script>
    <?php
}
