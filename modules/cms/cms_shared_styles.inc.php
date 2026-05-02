<?php
declare(strict_types=1);

/** Styles communs — liste CMS, édition Accueil, édition textes par page (même charte que slug=home). */
?>
<style>
    .cms-wrap { display:grid; gap:20px; max-width:1440px; margin:0 auto; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color:#18181b; }
    .cms-hero { background:linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #0f172a 100%); border-radius:16px; padding:24px 28px; color:#fff; box-shadow: 0 20px 50px -20px rgba(15,23,42,.45); border:1px solid rgba(255,255,255,.08); }
    .cms-hero h1 { margin:0 0 6px; font-size:26px; font-weight:700; letter-spacing:-.02em; }
    .cms-hero p { margin:0; color:rgba(248,250,252,.72); font-size:14px; line-height:1.5; }
    .cms-card { background:#fff; border:1px solid #e4e4e7; border-radius:16px; padding:0; box-shadow: 0 4px 24px -8px rgba(15,23,42,.08); overflow:hidden; }
    .cms-card > h2 { margin:0; padding:20px 24px; font-size:18px; font-weight:700; letter-spacing:-.02em; border-bottom:1px solid #f4f4f5; background:linear-gradient(180deg, #fafafa 0%, #fff 100%); }
    .cms-card .notice, .cms-card .error { margin:16px 24px 0; }
    .cms-list { display:grid; gap:10px; padding:20px 24px 24px; }
    .cms-list a { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; border:1px solid #e4e4e7; border-radius:12px; padding:14px 16px; text-decoration:none; color:#18181b; background:#fafafa; transition: border-color .15s, box-shadow .15s, background .15s; }
    .cms-list a:hover { border-color:#d4d4d8; background:#fff; box-shadow: 0 4px 12px -4px rgba(0,0,0,.06); }
    .cms-list a .cms-list-main { display:flex; flex-direction:column; gap:6px; flex:1; min-width:0; text-align:left; }
    .cms-list a .cms-list-title { font-weight:600; font-size:15px; line-height:1.3; }
    .cms-list a .cms-list-template { font-size:12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; color:#475569; background:#f1f5f9; border:1px solid #e2e8f0; padding:4px 10px; border-radius:8px; align-self:flex-start; word-break:break-all; }
    .cms-list a .cms-list-template-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:2px; }
    .cms-list a .cms-list-cta { flex-shrink:0; font-size:13px; font-weight:650; color:#0369a1; white-space:nowrap; align-self:center; }
    .cms-card-intro { margin:0; padding:14px 24px 16px; color:#64748b; font-size:14px; line-height:1.55; border-bottom:1px solid #f4f4f5; }
    .cms-card-intro code { font-size:12px; background:#f1f5f9; padding:2px 6px; border-radius:6px; }
    .cms-list-section { padding:0 0 4px; }
    .cms-list-section--secondary { border-top:1px solid #f4f4f5; margin-top:0; }
    .cms-list-section__title { margin:0; padding:20px 24px 6px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#475569; }
    .cms-list-section__desc { margin:0; padding:0 24px 14px; font-size:13px; color:#64748b; line-height:1.5; }
    .cms-list-section__empty { margin:0 24px 22px; padding:14px 16px; background:#f8fafc; border:1px dashed #e2e8f0; border-radius:12px; color:#64748b; font-size:13px; line-height:1.45; }
    .cms-list--scroll { max-height:400px; overflow-y:auto; }
    .cms-list a .cms-list-badge { display:inline-block; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#0c4a6e; background:#e0f2fe; border:1px solid #bae6fd; padding:3px 8px; border-radius:999px; align-self:flex-start; width:fit-content; }
    .cms-form { padding:20px 24px 28px; display:grid; gap:0; }
    .cms-editor-layout { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:24px; align-items:start; }
    .cms-editor-layout--single { grid-template-columns:1fr; }
    .cms-editor-main { min-width:0; display:grid; gap:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px 18px 4px; }
    .cms-editor-rail { min-width:0; }
    .cms-section { background:#fff; border:1px solid #e4e4e7; border-radius:14px; padding:20px 22px; box-shadow: 0 1px 0 rgba(0,0,0,.03); }
    .cms-section h3 { margin:0 0 16px; color:#09090b; font-size:15px; font-weight:700; letter-spacing:-.01em; padding-bottom:12px; border-bottom:1px solid #f4f4f5; }
    .cms-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 16px; }
    .cms-form .row { display:grid; gap:14px; grid-template-columns:1fr 1fr; }
    .cms-form label { display:block; font-size:11px; font-weight:650; color:#71717a; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
    .cms-form input, .cms-form textarea, .cms-form select { width:100%; border:1px solid #e4e4e7; border-radius:10px; padding:11px 14px; font-size:14px; color:#18181b; background:#fafafa; transition: border-color .15s, box-shadow .15s, background .15s; }
    .cms-form input:hover, .cms-form textarea:hover, .cms-form select:hover { border-color:#d4d4d8; background:#fff; }
    .cms-form input:focus, .cms-form textarea:focus, .cms-form select:focus { outline:none; border-color:#a1a1aa; background:#fff; box-shadow: 0 0 0 3px rgba(24,24,27,.06); }
    .cms-form textarea { min-height:96px; resize:vertical; line-height:1.55; }
    .cms-field-block { margin-bottom:14px; }
    .cms-field-block .tox-tinymce { border-radius:10px !important; border-color:#e4e4e7 !important; }
    textarea.cms-rte { min-height:140px; }
    .cms-form hr { border:0; border-top:1px solid #f4f4f5; margin:18px 0; }
    .cms-form h3:not(.cms-section h3) { font-size:13px; font-weight:700; color:#52525b; margin:20px 0 8px; letter-spacing:-.01em; }
    .cms-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:8px; padding-top:20px; border-top:1px solid #f4f4f5; }
    .btn { border:0; border-radius:10px; padding:11px 18px; cursor:pointer; font-weight:650; font-size:14px; transition: transform .12s, box-shadow .12s, background .12s; }
    .btn-primary { background:linear-gradient(180deg, #18181b 0%, #09090b 100%); color:#fafafa; box-shadow: 0 2px 8px -2px rgba(0,0,0,.25); }
    .btn-primary:hover { box-shadow: 0 6px 20px -4px rgba(0,0,0,.3); transform: translateY(-1px); }
    .btn-light { background:#f4f4f5; color:#18181b; text-decoration:none; display:inline-flex; align-items:center; border:1px solid #e4e4e7; }
    .btn-light:hover { background:#e4e4e7; }
    .notice { background:#ecfdf5; color:#14532d; border:1px solid #86efac; padding:12px 14px; border-radius:10px; font-size:14px; }
    .error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 14px; border-radius:10px; font-size:14px; }
    .cms-seo-panel { position:sticky; top:16px; z-index:4; background:linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border:1px solid #e2e8f0; border-radius:16px; padding:20px 20px 22px; box-shadow: 0 4px 24px -8px rgba(15,23,42,.1); }
    .cms-seo-panel::before { content:''; position:absolute; left:0; top:18px; bottom:18px; width:3px; border-radius:0 3px 3px 0; background:linear-gradient(180deg, #38bdf8, #0284c7); }
    .cms-editor-rail .cms-seo-panel { position:sticky; top:16px; }
    .cms-seo-panel__head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:18px; padding-left:8px; }
    .cms-seo-panel__head h3 { margin:0; font-size:14px; font-weight:700; color:#0f172a; letter-spacing:-.02em; }
    .cms-seo-panel__badge { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#0f172a; background:linear-gradient(135deg, #bae6fd, #7dd3fc); padding:4px 8px; border-radius:999px; }
    .cms-seo-score { display:flex; align-items:center; gap:14px; margin-bottom:18px; padding:14px 14px 14px 18px; background:#f1f5f9; border-radius:12px; border:1px solid #e2e8f0; }
    .cms-seo-score-badge { width:56px; height:56px; min-width:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:#fff; background:linear-gradient(145deg, #64748b, #475569); box-shadow: inset 0 1px 0 rgba(255,255,255,.15); transition: background .2s; }
    .cms-seo-score-badge.is-good { background:linear-gradient(145deg, #22c55e, #15803d); }
    .cms-seo-score-badge.is-warn { background:linear-gradient(145deg, #f59e0b, #d97706); }
    .cms-seo-score-badge.is-bad { background:linear-gradient(145deg, #ef4444, #b91c1c); }
    .cms-seo-score-text { display:flex; flex-direction:column; gap:2px; }
    .cms-seo-score-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#64748b; }
    .cms-seo-score-text strong { font-size:15px; font-weight:700; color:#0f172a; letter-spacing:-.02em; }
    .cms-seo-field { margin-bottom:14px; padding-left:8px; }
    .cms-seo-field label { color:#71717a !important; margin-bottom:7px !important; }
    .cms-seo-panel input { width:100%; background:#fafafa !important; border:1px solid #e4e4e7 !important; color:#18181b !important; border-radius:10px; padding:11px 14px; font-size:14px; }
    .cms-seo-panel input:hover { border-color:#d4d4d8 !important; background:#fff !important; }
    .cms-seo-panel input:focus { outline:none !important; border-color:#a1a1aa !important; background:#fff !important; box-shadow: 0 0 0 3px rgba(24,24,27,.06) !important; }
    .cms-seo-panel textarea { width:100%; min-height:110px; resize:vertical; line-height:1.5; background:#fafafa !important; border:1px solid #e4e4e7 !important; color:#18181b !important; border-radius:10px; padding:11px 14px; font-size:14px; }
    .cms-seo-panel textarea:hover { border-color:#d4d4d8 !important; background:#fff !important; }
    .cms-seo-panel textarea:focus { outline:none !important; border-color:#a1a1aa !important; background:#fff !important; box-shadow: 0 0 0 3px rgba(24,24,27,.06) !important; }
    .cms-field-hint { display:block; font-size:11px; color:#71717a; margin-top:5px; }
    .cms-seo-checklist { list-style:none; margin:16px 0 0; padding:8px 0 0 8px; border-top:1px solid #e2e8f0; display:grid; gap:8px; }
    .cms-seo-checklist li { font-size:12px; padding:9px 11px; border-radius:10px; border:1px solid #e4e4e7; background:#fafafa; color:#334155; line-height:1.45; }
    .cms-seo-checklist li.ok { border-color:#86efac; color:#14532d; background:#ecfdf5; }
    .cms-seo-checklist li.bad { border-color:#fecaca; color:#991b1b; background:#fef2f2; }
    .cms-seo-help { font-size:11px; color:#64748b; margin:14px 0 0 8px; line-height:1.55; }
    .cms-seo-metrics { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin:0 0 14px 8px; }
    .cms-seo-metric { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:8px 10px; display:grid; gap:2px; }
    .cms-seo-metric span { font-size:10px; text-transform:uppercase; letter-spacing:.08em; color:#64748b; }
    .cms-seo-metric strong { font-size:13px; color:#0f172a; }
    .cms-seo-metrics .cms-seo-metric--full { grid-column:1 / -1; }
    .cms-seo-metric-path { display:block; font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace; font-size:12px; font-weight:650; color:#0f172a; word-break:break-all; line-height:1.45; margin:0; }
    .cms-seo-metric .cms-seo-metric-sub { display:block; font-size:11px; font-weight:500; color:#64748b; line-height:1.4; margin-top:4px; text-transform:none; letter-spacing:normal; }
    .cms-editor-template-fallback { margin:0 24px 16px; }
    .cms-editor-template-fallback .cms-seo-metrics { margin:0; }
    @media (max-width: 1180px) {
        .cms-editor-layout { grid-template-columns:1fr; }
        .cms-editor-layout--single { grid-template-columns:1fr; }
        .cms-editor-rail .cms-seo-panel { position:static; }
        .cms-editor-main { order: 2; }
        .cms-editor-rail { order: 1; }
        .cms-meta-grid { grid-template-columns:1fr; }
    }
</style>
