<footer class="admin-footer">
    <div class="footer-left">
        <span class="footer-brand">
            <i class="fas fa-building footer-brand-icon"></i>
            IMMO LOCAL+
        </span>
        <span class="footer-sep">·</span>
        <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', '')) ?: (ADVISOR_NAME ?: APP_NAME)) ?> Immobilier. Tous droits réservés.</span>
    </div>
    <div class="footer-center">
        <a href="#" class="footer-link">Support</a>
        <span class="footer-sep">·</span>
        <a href="#" class="footer-link">Documentation</a>
        <span class="footer-sep">·</span>
        <a href="#" class="footer-link">Confidentialité</a>
    </div>
    <div class="footer-right">
        <span class="footer-version">
            <i class="fas fa-code-branch"></i>
            v1.0.0
        </span>
        <span class="footer-sep">·</span>
        <span class="footer-perf" title="Performance serveur">
            <i class="fas fa-gauge-high"></i>
            <?php
            $mem  = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
            $time = round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000);
            echo "{$mem} Mo · {$time} ms";
            ?>
        </span>
    </div>
</footer>
