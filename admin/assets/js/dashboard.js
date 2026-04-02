/* ========================================
   DASHBOARD.JS
======================================== */

const Dashboard = {

    /* --- Compteurs animés --- */
    animateCounters() {
        document.querySelectorAll('[data-count]').forEach(el => {
            const target = parseFloat(el.dataset.count);
            const isFloat = el.dataset.count.includes('.');
            const duration = 1200;
            const start = performance.now();

            const step = (now) => {
                const p = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - p, 3);
                const val = target * ease;
                el.textContent = isFloat
                    ? val.toFixed(1).replace('.', ',')
                    : Math.round(val).toLocaleString('fr-FR');
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
    },

    /* --- Mini graphique barres (canvas) --- */
    drawBarChart(canvasId, labels, data, color = '#2563eb') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const W = canvas.width  = canvas.offsetWidth;
        const H = canvas.height = canvas.offsetHeight;

        const max  = Math.max(...data) || 1;
        const barW = (W / data.length) * 0.6;
        const gap  = W / data.length;

        ctx.clearRect(0, 0, W, H);

        data.forEach((val, i) => {
            const barH = (val / max) * (H - 30);
            const x    = gap * i + gap * 0.2;
            const y    = H - barH - 20;

            // Barre
            ctx.fillStyle = color;
            ctx.globalAlpha = 0.85;
            ctx.beginPath();
            ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]);
            ctx.fill();

            // Label
            ctx.globalAlpha = 1;
            ctx.fillStyle = '#94a3b8';
            ctx.font = '10px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i] || '', x + barW / 2, H - 4);
        });
    },

    /* --- Graphique courbe simple --- */
    drawLineChart(canvasId, labels, data, color = '#2563eb') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const W = canvas.width  = canvas.offsetWidth;
        const H = canvas.height = canvas.offsetHeight;

        const max  = Math.max(...data) || 1;
        const padX = 20, padY = 20;
        const innerW = W - padX * 2;
        const innerH = H - padY * 2 - 16;

        ctx.clearRect(0, 0, W, H);

        const pts = data.map((v, i) => ({
            x: padX + (i / (data.length - 1 || 1)) * innerW,
            y: padY + innerH - (v / max) * innerH
        }));

        // Zone remplie
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        pts.slice(1).forEach(p => ctx.lineTo(p.x, p.y));
        ctx.lineTo(pts[pts.length - 1].x, padY + innerH);
        ctx.lineTo(pts[0].x, padY + innerH);
        ctx.closePath();
        ctx.fillStyle = color + '22';
        ctx.fill();

        // Ligne
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        pts.slice(1).forEach(p => ctx.lineTo(p.x, p.y));
        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        ctx.stroke();

        // Points
        pts.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.stroke();
        });

        // Labels
        labels.forEach((lbl, i) => {
            ctx.fillStyle = '#94a3b8';
            ctx.font = '10px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(lbl, pts[i].x, H - 2);
        });
    },

    /* --- Chargement stats via AJAX --- */
    async loadStats() {
        try {
            const res = await fetch('/admin/api/stats.php');
            if (!res.ok) return;
            const data = await res.json();

            // Mise à jour compteurs
            if (data.total_biens !== undefined) {
                const el = document.querySelector('[data-stat="biens"]');
                if (el) { el.dataset.count = data.total_biens; }
            }
            if (data.ventes_mois !== undefined) {
                const el = document.querySelector('[data-stat="ventes"]');
                if (el) { el.dataset.count = data.ventes_mois; }
            }

            // Graphique si données dispo
            if (data.chart_labels && data.chart_data) {
                this.drawLineChart('chartVentes', data.chart_labels, data.chart_data);
            }
        } catch (e) {
            // Silencieux — dashboard reste fonctionnel sans l'API
        }
    },

    /* --- Init --- */
    init() {
        this.animateCounters();
        this.loadStats();

        // Graphiques par défaut (données statiques de démo)
        const mois = ['Jan','Fév','Mar','Avr','Mai','Jun',
                      'Jul','Aoû','Sep','Oct','Nov','Déc'];
        const now  = new Date().getMonth();
        const lbls = mois.slice(0, now + 1);
        const demo = lbls.map(() => Math.floor(Math.random() * 15 + 3));

        this.drawLineChart('chartVentes', lbls, demo);
        this.drawBarChart('chartBiens',  lbls, demo, '#7c3aed');

        // Resize
        window.addEventListener('resize', () => {
            this.drawLineChart('chartVentes', lbls, demo);
            this.drawBarChart('chartBiens',  lbls, demo, '#7c3aed');
        });
    }
};

document.addEventListener('DOMContentLoaded', () => Dashboard.init());
