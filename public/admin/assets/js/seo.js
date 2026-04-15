function getCsrfToken() {
  return document.querySelector('input[name="csrf_token"]')?.value || '';
}

function filterModules(query) {
  const q = (query || '').toLowerCase();
  document.querySelectorAll('#seo-modules-grid .seo-card').forEach((card) => {
    const text = (card.dataset.module || '').toLowerCase();
    card.style.display = text.includes(q) ? '' : 'none';
  });
}

async function checkKeywordPosition(id) {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());
  form.append('id', id);
  const res = await fetch('/modules/seo/ajax/check-keyword.php', { method: 'POST', body: form });
  const data = await res.json();
  alert(data.success ? `Position mise à jour: ${data.position ?? 'N/A'}` : data.message);
  if (data.success) window.location.reload();
}

async function deleteKeyword(id) {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());
  form.append('action', 'delete');
  form.append('id', id);
  const res = await fetch('/modules/seo/ajax/check-keyword.php', { method: 'POST', body: form });
  const data = await res.json();
  if (data.success) window.location.reload();
}

async function generateSitemap(submitGsc = false) {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());
  form.append('submit_gsc', submitGsc ? '1' : '0');
  const res = await fetch('/modules/seo/ajax/generate-sitemap.php', { method: 'POST', body: form });
  const data = await res.json();
  if (data.success) {
    const pre = document.getElementById('sitemap-xml-preview');
    if (pre) pre.textContent = data.xml;
    alert('Sitemap généré');
  } else { alert(data.message || 'Erreur'); }
}

async function runPerformanceAudit(url, device) {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());
  form.append('url', url);
  form.append('device', device);
  const res = await fetch('/modules/seo/ajax/performance-audit.php', { method: 'POST', body: form });
  const data = await res.json();
  if (!data.success) return alert(data.message || 'Erreur audit');

  renderScoreCircle(data.scores.performance, 'score-perf', 'Perf');
  renderScoreCircle(data.scores.seo, 'score-seo', 'SEO');
  renderScoreCircle(data.scores.accessibility, 'score-access', 'Access');
  renderScoreCircle(data.scores.best_practices, 'score-bp', 'BP');

  const cwv = document.getElementById('cwv-results');
  if (cwv) cwv.innerHTML = `<div>LCP: ${data.core_web_vitals.lcp}ms</div><div>INP/FID: ${data.core_web_vitals.inp}ms</div><div>CLS: ${data.core_web_vitals.cls}</div><div>TTFB: ${data.core_web_vitals.ttfb}ms</div>`;
}

function renderScoreCircle(score, elementId, label = '') {
  const el = document.getElementById(elementId); if (!el) return;
  const color = score >= 90 ? '#16a34a' : (score >= 50 ? '#f59e0b' : '#ef4444');
  el.className = 'score-circle';
  el.innerHTML = `<svg viewBox='0 0 36 36' width='88' height='88'><path d='M18 2 a 16 16 0 0 1 0 32 a 16 16 0 0 1 0 -32' fill='none' stroke='#e2e8f0' stroke-width='3'/><path d='M18 2 a 16 16 0 0 1 0 32 a 16 16 0 0 1 0 -32' fill='none' stroke='${color}' stroke-width='3' stroke-dasharray='${score},100'/><text x='18' y='21' text-anchor='middle' font-size='8'>${score}</text></svg><div>${label}</div>`;
}

async function generateVilleContent(ville) {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());
  form.append('action', 'generate');
  form.append('ville', ville || '');
  const res = await fetch('/modules/seo/ajax/villes.php', { method: 'POST', body: form });
  const data = await res.json();
  if (data.success) document.querySelector('[name="content"]').value = data.content;
}

async function realTimeSeoScore() {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());
  form.append('seo_title', document.querySelector('[name="seo_title"]')?.value || '');
  form.append('meta_description', document.querySelector('[name="meta_description"]')?.value || '');
  form.append('content', document.querySelector('[name="content"]')?.value || '');
  const res = await fetch('/modules/seo/ajax/seo-score.php', { method: 'POST', body: form });
  const data = await res.json();
  if (data.success) {
    const score = document.querySelector('#live-seo-score strong');
    if (score) score.textContent = data.score;
  }
}

function exportKeywordsCSV() {
  const rows = [...document.querySelectorAll('#keywords-table tr')].map(tr => [...tr.querySelectorAll('th,td')].map(td => '"' + td.innerText.replace(/"/g, '""') + '"').join(','));
  const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'keywords.csv';
  document.body.appendChild(a); a.click(); a.remove();
}

function initCharts() {
  const c = document.getElementById('keywordEvolutionChart');
  if (!c || typeof Chart === 'undefined') return;
  new Chart(c, { type: 'line', data: { labels: ['J-6','J-5','J-4','J-3','J-2','J-1','Aujourd\'hui'], datasets: [{ label: 'Position moyenne', data: [22,20,18,19,16,14,13], borderColor: '#3b82f6', tension: .3 }] }, options: { scales: { y: { reverse: true, beginAtZero: false }}}});
}

document.addEventListener('input', (e) => {
  if (['seo_title', 'meta_description', 'content'].includes(e.target.name)) realTimeSeoScore();
});
document.addEventListener('DOMContentLoaded', initCharts);


document.addEventListener('submit', async (e) => {
  const form = e.target;
  if (form.matches('form[action*="check-keyword.php"]')) {
    e.preventDefault();
    const fd = new FormData(form);
    const res = await fetch(form.action, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) window.location.reload(); else alert(data.message || 'Erreur');
  }
  if (form.matches('#city-form')) {
    e.preventDefault();
    const fd = new FormData(form);
    const res = await fetch(form.action, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) window.location.reload(); else alert(data.message || 'Erreur');
  }
});


async function runSitemapAction(action) {
  const form = new FormData();
  form.append('csrf_token', getCsrfToken());

  let endpoint = '/modules/seo/sitemap/api.php';
  if (action === 'generate') endpoint = '/modules/seo/sitemap/generate.php';
  if (action !== 'generate') form.append('action', action);

  const res = await fetch(endpoint, { method: 'POST', body: form });
  const payload = await res.json();
  if (!payload.success) {
    alert(payload.message || 'Erreur sitemap');
    return;
  }

  const data = payload.data || {};
  if (action === 'generate' && data.xml) {
    const pre = document.getElementById('sitemap-xml-preview');
    if (pre) pre.textContent = data.xml;
  }

  if (Array.isArray(data.issues)) {
    const issuesCount = data.issues.length;
    alert(issuesCount === 0 ? 'Aucune anomalie détectée.' : `${issuesCount} anomalie(s) détectée(s).`);
  } else if (data.message) {
    alert(data.message);
  } else {
    alert('Action sitemap exécutée.');
  }

  await refreshSitemapLogs();
}

async function refreshSitemapLogs() {
  const tbody = document.getElementById('sitemap-logs-body');
  if (!tbody) return;

  const res = await fetch('/modules/seo/sitemap/logs.php');
  const payload = await res.json();
  if (!payload.success) return;

  tbody.innerHTML = '';
  (payload.logs || []).forEach((log) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${log.created_at || ''}</td><td>${log.action_type || ''}</td><td>${log.status || ''}</td><td>${log.message || ''}</td><td>${log.urls_count || 0}</td>`;
    tbody.appendChild(tr);
  });
}
