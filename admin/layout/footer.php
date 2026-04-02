<?php
/**
 * FOOTER — IMMO LOCAL+ (Version Épurée & Conforme RGPD)
 * /admin/layout/footer.php
 */
?>
        </main>

        <!-- FOOTER -->
        <footer class="app-footer" role="contentinfo">
            <div class="footer-content">
                <div class="footer-left">
                    <span class="footer-copyright">&copy; <?= date('Y') ?> IMMO LOCAL+</span>
                    <span class="footer-version">v<?= htmlspecialchars(defined('IMMO_VERSION') ? IMMO_VERSION : '2.1') ?></span>
                </div>
                <div class="footer-center">
                    <span class="footer-separator">·</span>
                </div>
                <div class="footer-right">
                    <a href="?page=legal" class="footer-link" title="Mentions légales">
                        <i class="fas fa-file-alt"></i> Mentions légales
                    </a>
                    <span class="footer-separator">·</span>
                    <a href="?page=privacy" class="footer-link" title="Politique de confidentialité">
                        <i class="fas fa-shield-alt"></i> Confidentialité
                    </a>
                    <span class="footer-separator">·</span>
                    <a href="?page=cookies" class="footer-link" title="Gestion des cookies">
                        <i class="fas fa-cookie"></i> Cookies
                    </a>
                    <span class="footer-separator">·</span>
                    <a href="?page=support" class="footer-link" title="Support">
                        <i class="fas fa-headset"></i> Support
                    </a>
                </div>
            </div>
        </footer>

    </div><!-- /.admin-main -->

</div><!-- /.admin-wrapper -->

<style>
/* ============================================
   IMMO LOCAL+ FOOTER — Version Épurée & RGPD
   ============================================ */

:root {
    --footer-bg: #ffffff;
    --footer-text: #6b7280;
    --footer-border: #e5e7eb;
    --footer-link-color: #4f7df3;
}

.app-footer {
    background: var(--footer-bg);
    border-top: 1px solid var(--footer-border);
    padding: 1rem 2rem;
    box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
    font-size: 12px;
    color: var(--footer-text);
    margin-top: auto;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    max-width: 100%;
    flex-wrap: wrap;
}

.footer-left,
.footer-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.footer-center {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-copyright {
    font-weight: 600;
    color: #1f2937;
}

.footer-version {
    color: var(--footer-text);
}

.footer-separator {
    color: var(--footer-border);
}

.footer-link {
    color: var(--footer-link-color);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.footer-link:hover {
    color: #1f2937;
    text-decoration: underline;
}

.footer-link i {
    font-size: 11px;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .app-footer {
        padding: 1rem 1.5rem;
    }

    .footer-content {
        gap: 0.5rem;
    }

    .footer-left,
    .footer-right {
        gap: 0.5rem;
    }
}

@media (max-width: 768px) {
    .app-footer {
        padding: 1rem;
        font-size: 11px;
    }

    .footer-content {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }

    .footer-left,
    .footer-right {
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }

    .footer-center {
        display: none;
    }

    .footer-link {
        font-size: 10px;
    }

    .footer-separator {
        display: none;
    }
}

/* Footer at bottom of page */
.admin-main {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.admin-content {
    flex: 1;
    overflow-y: auto;
}

.app-footer {
    flex-shrink: 0;
}
</style>

</body>
</html>
