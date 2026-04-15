/* ═══════════════════════════════════════════════════════════
   SOCIAL MODULE — Section Trafic / Communication
   IMMO LOCAL+ · Interactions JS
   ══════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
    initSequenceAccordion();
    initFilterChips();
    initPersonaFilter();
    initPremiumSocialStudio();
});

function initSequenceAccordion() {
    document.querySelectorAll('.seq-head').forEach((head) => {
        head.addEventListener('click', () => {
            const row = head.closest('.seq-row');
            if (!row) return;
            row.classList.toggle('is-open');
        });
    });
}

function initFilterChips() {
    document.querySelectorAll('[data-filter-group="status"] .s-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('[data-filter-group="status"] .s-chip').forEach(c => c.classList.remove('is-active'));
            chip.classList.add('is-active');

            const value = chip.dataset.filterValue ?? 'all';
            document.querySelectorAll('.seq-row[data-statut]').forEach((row) => {
                row.style.display = (value === 'all' || row.dataset.statut === value) ? '' : 'none';
            });
        });
    });
}

function initPersonaFilter() {
    document.querySelectorAll('[data-filter-group="persona"] .s-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('[data-filter-group="persona"] .s-chip').forEach(c => c.classList.remove('is-active'));
            chip.classList.add('is-active');

            const value = chip.dataset.filterValue ?? 'all';
            document.querySelectorAll('.seq-row[data-persona]').forEach((row) => {
                if (value === 'all') {
                    row.style.display = '';
                } else {
                    const rowPersona = (row.dataset.persona ?? '').toLowerCase();
                    row.style.display = rowPersona.includes(value.toLowerCase()) ? '' : 'none';
                }
            });
        });
    });
}

function initPremiumSocialStudio() {
    const studio = document.getElementById('socialPremiumProduction');
    if (!studio) return;

    const timelineItems = Array.from(studio.querySelectorAll('.timeline-item'));
    const preview = studio.querySelector('[data-post-preview] p');
    const previewTitle = studio.querySelector('[data-post-preview] strong');
    const textarea = studio.querySelector('#premiumEditorText');
    const rewriteButton = studio.querySelector('[data-ai-rewrite]');

    const analysisScore = studio.querySelector('[data-analysis-score]');
    const analysisPersona = studio.querySelector('[data-analysis-persona]');
    const analysisAwareness = studio.querySelector('[data-analysis-awareness]');
    const analysisFunnel = studio.querySelector('[data-analysis-funnel]');
    const analysisWords = studio.querySelector('[data-analysis-words]');
    const analysisSuggestions = studio.querySelector('[data-analysis-suggestions]');

    const filterNetwork = studio.querySelector('[data-journal-filter="network"]');
    const filterStatus = studio.querySelector('[data-journal-filter="status"]');
    const filterDate = studio.querySelector('[data-journal-filter="date"]');

    const updateAnalysis = (text, meta = {}) => {
        const cleanText = (text || '').trim();
        const wordCount = cleanText.split(/\s+/).filter(Boolean).length;
        const hasCTA = /(contact|rdv|estimation|appelez|dm|message)/i.test(cleanText);
        const hasProof = /(preuve|avis|chiffre|vendu|%|jours)/i.test(cleanText);
        const powerWords = ['exclusif', 'confiance', 'opportunité', 'local', 'résultat', 'premium'];
        const matches = powerWords.filter((w) => cleanText.toLowerCase().includes(w));

        let score = 45;
        score += Math.min(wordCount, 120) * 0.25;
        if (hasCTA) score += 12;
        if (hasProof) score += 10;
        score += matches.length * 5;
        score = Math.max(32, Math.min(98, Math.round(score)));

        if (analysisScore) analysisScore.textContent = String(score);
        if (analysisPersona) analysisPersona.textContent = meta.persona || 'Vendeur local';
        if (analysisAwareness) analysisAwareness.textContent = meta.awareness || 'N2 - Problème';
        if (analysisFunnel) analysisFunnel.textContent = meta.funnel || 'Prise de rendez-vous estimation';
        if (analysisWords) analysisWords.textContent = matches.length ? matches.join(', ') : 'ajoutez des mots d\'impact';
        if (analysisSuggestions) {
            const suggestions = [];
            if (!hasCTA) suggestions.push('Ajoutez un CTA explicite vers un rendez-vous.');
            if (!hasProof) suggestions.push('Insérez une preuve locale chiffrée ou un résultat client.');
            if (wordCount < 35) suggestions.push('Enrichissez le contexte émotionnel pour mieux qualifier le lead.');
            if (suggestions.length === 0) suggestions.push('Très bon angle : testez une variante courte pour Instagram Story.');
            analysisSuggestions.textContent = suggestions.join(' ');
        }
    };

    const setActivePost = (button) => {
        timelineItems.forEach((item) => item.classList.remove('is-active'));
        button.classList.add('is-active');

        const text = button.dataset.postText || '';
        const title = button.querySelector('strong')?.textContent || 'Post social';
        const meta = {
            persona: button.dataset.sequencePersona,
            awareness: button.dataset.awareness,
            funnel: button.dataset.funnel,
        };

        if (preview) preview.textContent = text || 'Contenu vide.';
        if (previewTitle) previewTitle.textContent = title;
        if (textarea) textarea.value = text;

        updateAnalysis(text, meta);
    };

    timelineItems.forEach((item, index) => {
        item.addEventListener('click', () => setActivePost(item));
        if (index === 0) setActivePost(item);
    });

    if (textarea) {
        textarea.addEventListener('input', () => {
            if (preview) preview.textContent = textarea.value;
            updateAnalysis(textarea.value);
        });
    }

    if (rewriteButton && textarea) {
        rewriteButton.addEventListener('click', () => {
            const source = textarea.value.trim();
            if (!source) return;

            textarea.value = `${source}\n\n✨ Angle IA: preuve locale + urgence douce + CTA “Réservez votre audit immobilier en DM”.`;
            if (preview) preview.textContent = textarea.value;
            updateAnalysis(textarea.value);
            rewriteButton.classList.add('is-ai-flash');
            setTimeout(() => rewriteButton.classList.remove('is-ai-flash'), 600);
        });
    }

    const applyFilters = () => {
        const network = filterNetwork?.value ?? 'all';
        const status = filterStatus?.value ?? 'all';
        const date = filterDate?.value ?? '';

        timelineItems.forEach((item) => {
            const okNetwork = network === 'all' || (item.dataset.postNetwork || '') === network;
            const okStatus = status === 'all' || (item.dataset.postStatus || '') === status;
            const okDate = !date || (item.dataset.postDate || '') === date;
            item.style.display = okNetwork && okStatus && okDate ? '' : 'none';
        });
    };

    [filterNetwork, filterStatus, filterDate].forEach((el) => {
        if (el) el.addEventListener('input', applyFilters);
    });

    studio.querySelectorAll('[data-preview-target]').forEach((tab) => {
        tab.addEventListener('click', () => {
            studio.querySelectorAll('[data-preview-target]').forEach((el) => el.classList.remove('is-active'));
            tab.classList.add('is-active');
        });
    });
}
