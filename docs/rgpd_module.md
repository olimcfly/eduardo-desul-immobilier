# RGPD Module Integration Guide

## 1) Controller endpoints

Example route mapping (adapt to your router):

- `POST /rgpd/consents/cookie` -> `RgpdController::saveCookieConsent($siteId, $body)`
- `POST /rgpd/policy/generate` -> `RgpdController::generatePolicy($siteId, $body)`
- `GET /privacy-policy` -> `RgpdController::privacyPolicy($siteId)`
- `POST /rgpd/requests` -> `RgpdController::createRequest($siteId, $body)`
- `GET /admin/rgpd/requests` -> `RgpdController::listRequests($siteId, $_GET)`
- `PATCH /admin/rgpd/requests/{id}` -> `RgpdController::updateRequestStatus($siteId, $id, $body)`
- `DELETE /admin/rgpd/requests/{id}` -> `RgpdController::deleteRequest($siteId, $id)`

## 2) Reusable RGPD form component

```php
<?php
$privacyPolicyLink = '/privacy-policy';
$showMarketingCheckbox = true;
?>

<label>
    <input type="checkbox" name="rgpd_legal_notice" value="1" required>
    J'accepte que mes données soient utilisées pour traiter ma demande,
    conformément à la <a href="<?= htmlspecialchars($privacyPolicyLink, ENT_QUOTES, 'UTF-8'); ?>">politique de confidentialité</a>.
</label>

<?php if ($showMarketingCheckbox): ?>
<label>
    <input type="checkbox" name="rgpd_marketing_optin" value="1">
    J'accepte de recevoir des communications marketing (optionnel).
</label>
<?php endif; ?>
```

## 3) Cookie banner integration

```php
<?php
$policyUrl = '/privacy-policy';
$policyVersion = 'v1';
include __DIR__ . '/../resources/views/rgpd/banner.php';
?>
<script src="/public/js/cookie-consent.js" defer></script>

<!-- Script blocked by default until consent -->
<script type="text/plain" data-consent-category="analytics" data-src="https://analytics.example.com/tag.js"></script>
```

## 4) Cron (data retention)

Pseudo-cron command (daily 02:00 UTC):

```bash
0 2 * * * /usr/bin/php /var/www/project/scripts/rgpd-retention-cron.php
```

Cron script must:
1. Load active rules from `rgpd_retention_rules` for each `site_id`.
2. Call `DataRetentionService::run($rules, $siteId)`.
3. Log row counts deleted/anonymized.

## 5) Manual test scenarios

1. **Cookie refuse flow**: load page, click "Refuser", verify analytics script not loaded and row inserted in `rgpd_consents`.
2. **Cookie custom flow**: enable analytics only, verify only analytics scripts execute.
3. **Policy generation**: call `POST /rgpd/policy/generate`, then open `/privacy-policy` and verify dynamic values.
4. **Form consent proof**: submit lead form with legal checkbox enabled, verify row contains email + version + IP + proof hash.
5. **RGPD request workflow**: create request, move status `new -> in_progress -> done` in admin endpoint.
6. **Retention cron delete**: set test retention rule with 1 day, insert stale data, execute cron and verify deletion/anonymization.
