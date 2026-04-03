(() => {
    const editor = document.querySelector('.editor-textarea');

    function getScoreColor(score) {
        if (score >= 80) return 'var(--seo-great)';
        if (score >= 55) return 'var(--seo-good)';
        return 'var(--seo-weak)';
    }

    function calculateSEOScore(content) {
        const words = content.split(/\s+/).filter(Boolean).length;
        const h2Count = (content.match(/<h2/gi) || []).length;
        let score = Math.min(30, Math.floor(words / 20));
        score += Math.min(20, h2Count * 5);
        if (words > 500) score += 25;
        if (/immobilier|achat|vente|estimation/i.test(content)) score += 15;
        return Math.min(100, score);
    }

    function updateSEOAnalysis() {
        if (!editor) return;
        const content = editor.innerText || '';
        const wordCount = content.split(/\s+/).filter(word => word.length > 0).length;
        const seoScore = calculateSEOScore(editor.innerHTML || content);

        const wordCountEl = document.querySelector('.word-count-n');
        const scoreEl = document.querySelector('.score-n');
        const ring = document.querySelector('.progress-ring');
        if (wordCountEl) wordCountEl.textContent = String(wordCount);
        if (scoreEl) scoreEl.textContent = String(seoScore);
        if (ring) ring.style.borderColor = getScoreColor(seoScore);
    }

    function bindToolbar() {
        document.querySelectorAll('.toolbar [data-cmd]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const cmd = btn.getAttribute('data-cmd');
                const value = btn.getAttribute('data-value');
                if (!cmd) return;
                if (cmd === 'createLink') {
                    const url = window.prompt('URL du lien :', 'https://');
                    if (!url) return;
                    document.execCommand(cmd, false, url);
                    return;
                }
                document.execCommand(cmd, false, value || undefined);
                updateSEOAnalysis();
            });
        });
    }

    function bindSiloDnD() {
        const nodes = document.querySelectorAll('.sat-node, .pillar-node');
        nodes.forEach((node) => {
            node.addEventListener('dragstart', () => node.classList.add('dragging'));
            node.addEventListener('dragend', () => node.classList.remove('dragging'));
        });
    }

    if (editor) {
        editor.addEventListener('input', updateSEOAnalysis);
        bindToolbar();
        updateSEOAnalysis();
    }
    bindSiloDnD();
})();
