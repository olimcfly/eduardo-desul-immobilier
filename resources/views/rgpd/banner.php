<?php
/** @var string $policyUrl */
/** @var string $policyVersion */
$policyUrl = $policyUrl ?? '/privacy-policy';
$policyVersion = $policyVersion ?? 'v1';
?>
<div class="rgpd-banner is-hidden" data-rgpd-banner data-policy-version="<?= htmlspecialchars($policyVersion, ENT_QUOTES, 'UTF-8'); ?>">
    <p>
        Nous utilisons des cookies pour améliorer votre expérience.
        Consultez notre <a href="<?= htmlspecialchars($policyUrl, ENT_QUOTES, 'UTF-8'); ?>">politique de confidentialité</a>.
    </p>

    <div class="rgpd-banner__actions">
        <button type="button" data-consent-action="accept">Tout accepter</button>
        <button type="button" data-consent-action="refuse">Refuser</button>
        <button type="button" data-consent-action="customize">Personnaliser</button>
    </div>

    <div class="rgpd-banner__custom is-hidden" data-consent-custom-panel>
        <label>
            <input type="checkbox" checked disabled>
            Cookies nécessaires (obligatoires)
        </label>
        <label>
            <input type="checkbox" name="analytics_optin">
            Analytics
        </label>
        <label>
            <input type="checkbox" name="marketing_optin">
            Marketing
        </label>
        <button type="button" data-consent-action="save-custom">Sauvegarder mes choix</button>
    </div>
</div>
