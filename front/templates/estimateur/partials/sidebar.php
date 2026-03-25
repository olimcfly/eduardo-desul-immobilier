<aside class="est-card">
  <h3>Conseiller</h3>
  <p><?= htmlspecialchars($cfg['advisor_name'] ?? 'Conseiller') ?></p>
  <p><?= htmlspecialchars($cfg['advisor_network'] ?? 'Réseau') ?></p>
  <a href="<?= htmlspecialchars($cfg['cta_primary_url'] ?? '#') ?>"><?= htmlspecialchars($cfg['cta_primary_label'] ?? 'Être rappelé') ?></a>
</aside>
