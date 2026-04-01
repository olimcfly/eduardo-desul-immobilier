<?php
/**
 * FOOTER — IMMO LOCAL+ (Mode Strict Minimaliste)
 * /admin/layout/footer.php
 */
?>
        </main>

        <!-- FOOTER -->
        <footer class="footer" role="contentinfo">
            <div class="footer-content">
                <div class="footer-left">
                    <span class="footer-version">IMMO LOCAL+ v<?= htmlspecialchars(defined('IMMO_VERSION') ? IMMO_VERSION : '2.1') ?></span>
                    <span class="footer-sep">|</span>
                    <a href="?section=mentions-legales" class="footer-link">Mentions légales</a>
                </div>
                <div class="footer-right">
                    <a href="?section=support" class="footer-link">Support</a>
                </div>
            </div>
        </footer>

    </div><!-- /.admin-main -->

</div><!-- /.admin-wrapper -->

<style>
    /* ============================================
       IMMO LOCAL+ FOOTER — Mode Strict Minimaliste
       ============================================ */

    :root {
        --color-primary: #6366F1;
        --color-primary-light: #EEF2FF;
        --color-white: #FFFFFF;
        --color-gray-50: #F9FAFB;
        --color-gray-100: #F3F4F6;
        --color-gray-200: #E5E7EB;
        --color-gray-600: #4B5563;
        --color-text-primary: #1F2937;
        --color-text-secondary: #6B7280;
        --color-shadow: rgba(0, 0, 0, 0.1);

        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;

        --radius: 0.5rem;
        --border: 1px solid var(--color-gray-200);
        --shadow: 0 1px 3px var(--color-shadow);
        --font-base: 14px;
        --font-note: 12px;
    }

    .footer {
        background: var(--color-white);
        border-top: var(--border);
        padding: var(--spacing-md) var(--spacing-xl);
        box-shadow: var(--shadow);
        font-size: var(--font-note);
        color: var(--color-text-secondary);
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--spacing-lg);
        max-width: 100%;
    }

    .footer-left,
    .footer-right {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .footer-version {
        font-weight: 600;
        color: var(--color-text-primary);
    }

    .footer-sep {
        color: var(--color-gray-200);
    }

    .footer-link {
        color: var(--color-primary);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .footer-link:hover {
        color: var(--color-text-primary);
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .footer {
            padding: var(--spacing-md);
        }

        .footer-content {
            flex-direction: column;
            gap: var(--spacing-sm);
            text-align: center;
        }

        .footer-left,
        .footer-right {
            justify-content: center;
        }
    }
</style>

</body>
</html>
