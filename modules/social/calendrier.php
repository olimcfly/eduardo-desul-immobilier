<?php
require_once __DIR__ . '/includes/_bootstrap.php';
$pageTitle = 'Calendrier éditorial';
$pageDescription = 'Planning multi-réseaux';
require_once __DIR__ . '/../../admin/views/layout.php';

function renderContent()
{
    ?>
    <link rel="stylesheet" href="/modules/social/assets/social.css">
    <section class="social-page">
        <div class="breadcrumb"><a href="/admin/">Accueil</a> &gt; <a href="/admin/?module=social">Social</a> &gt; Calendrier</div>
        <h1>Calendrier éditorial</h1>
        <div class="calendar-toolbar"><button onclick="prevMonth()">Mois précédent</button><span id="cal-title"></span><button onclick="nextMonth()">Mois suivant</button></div>
        <div id="calendar-grid" class="calendar-grid"></div>
        <div class="calendar-legend"><span class="fb">Facebook</span><span class="ig">Instagram</span><span class="li">LinkedIn</span></div>
    </section>
    <script src="/modules/social/assets/social.js"></script>
    <?php
}
