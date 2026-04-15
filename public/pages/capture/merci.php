<?php
$pageTitle  = 'Merci ! — ' . APP_NAME . '';
$metaRobots = 'noindex';
$bodyClass  = 'page-merci';
?>
<section style="min-height:calc(90vh - var(--header-h));display:flex;align-items:center;justify-content:center;padding-block:3rem">
    <div class="container">
        <div style="max-width:600px;margin:0 auto;text-align:center">
            <div style="font-size:5rem;margin-bottom:1.5rem">🎉</div>
            <h1>Merci !</h1>
            <p style="font-size:1.15rem;color:var(--clr-text-muted);margin-bottom:2rem">
                Votre demande a bien été reçue. Pascal vous contactera personnellement dans les <strong>24 à 48 heures</strong> pour donner suite à votre demande.
            </p>

            <div style="background:var(--clr-bg);border-radius:var(--radius-xl);padding:2rem;border:1px solid var(--clr-border);margin-bottom:2rem;text-align:left">
                <h3 style="margin-bottom:1rem">Et maintenant ?</h3>
                <?php foreach ([
                    ['📧', 'Vérifiez votre email', 'Un email de confirmation vous a été envoyé avec les détails de votre demande.'],
                    ['📞', 'Attendez l\'appel d\'Pascal', 'Pascal vous appellera dans les 24h pour un échange personnalisé.'],
                    ['🔍', 'Explorez le site', 'Profitez-en pour découvrir nos annonces et nos ressources.'],
                ] as [$icon, $titre, $desc]): ?>
                <div style="display:flex;gap:1rem;margin-bottom:1rem;align-items:flex-start">
                    <span style="font-size:1.5rem;flex-shrink:0"><?= $icon ?></span>
                    <div>
                        <strong style="display:block;margin-bottom:.2rem"><?= $titre ?></strong>
                        <span style="font-size:.875rem;color:var(--clr-text-muted)"><?= $desc ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
                <a href="/biens" class="btn btn--primary">Voir les annonces</a>
                <a href="/" class="btn btn--outline">Retour à l'accueil</a>
            </div>

            <?php if (APP_PHONE): ?>
            <p style="margin-top:2rem;font-size:.875rem;color:var(--clr-text-muted)">
                Une question urgente ? Appelez directement Pascal :<br>
                <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>" style="color:var(--clr-primary);font-weight:700;font-size:1rem"><?= e(APP_PHONE) ?></a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</section>
