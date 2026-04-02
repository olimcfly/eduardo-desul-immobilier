<footer class="admin-footer">
    <span>© <?= date('Y') ?> ImmoSite</span>
    <span class="footer-sep">·</span>
    <span>v1.0.0</span>
    <span class="footer-sep">·</span>
    <span>
        <?php
        $mem = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
        $time = round((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000);
        echo "{$mem} Mo · {$time} ms";
        ?>
    </span>
</footer>
