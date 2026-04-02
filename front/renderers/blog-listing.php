<?php
/**
 * ══════════════════════════════════════════════════════════════
 * BLOG LISTING — Front  v2.0  Eduardo De Sul
 * /front/renderers/blog-listing.php
 * ══════════════════════════════════════════════════════════════
 */

if (!isset($db)) { http_response_code(403); exit('Accès direct interdit.'); }

$page      = max(1, intval($_GET['page']   ?? 1));
$catFilter = trim($_GET['categorie']       ?? $_GET['cat'] ?? '');
$search    = trim($_GET['search']          ?? '');
$perPage   = 9;
$offset    = ($page - 1) * $perPage;

// ─── Page hub ───
$hubPage = null;
try {
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug = 'blog' AND status = 'published' LIMIT 1");
    $stmt->execute(); $hubPage = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ─── Catégories ───
$categories = [];
try {
    $stmt = $db->query("SELECT category AS cat, COUNT(*) AS nb FROM articles WHERE (statut='publie' OR status='published') AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY nb DESC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ─── Articles ───
$articles = []; $total = 0;
try {
    $whereBase = "(statut='publie' OR status='published')"; $params = [];
    if ($catFilter) { $whereBase .= " AND category=?"; $params[] = $catFilter; }
    if ($search !== '') {
        $whereBase .= " AND (titre LIKE ? OR extrait LIKE ? OR contenu LIKE ?)";
        $searchLike = '%'.$search.'%';
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
    }
    $countStmt = $db->prepare("SELECT COUNT(*) FROM articles WHERE $whereBase");
    $countStmt->execute($params); $total = intval($countStmt->fetchColumn());
    $stmt = $db->prepare("
        SELECT id, titre, slug, extrait, featured_image, image, category, author,
               date_publication, published_at, views, reading_time, contenu
        FROM articles WHERE $whereBase
        ORDER BY COALESCE(published_at, date_publication, created_at) DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { error_log("Blog listing: ".$e->getMessage()); }

$featured   = (!$catFilter && $search === '' && $page===1 && !empty($articles)) ? array_shift($articles) : null;
$totalPages = $total > 0 ? ceil($total / $perPage) : 1;
$hf         = getHeaderFooter($db, 'blog');
$_siteUrl   = siteUrl();
$_siteName  = siteName();
$pageTitle  = $catFilter ? 'Blog · '.$catFilter : 'Conseils immobiliers Bordeaux';
if ($search !== '') {
    $pageTitle .= ' · Recherche: '.$search;
}
$metaTitle  = htmlspecialchars(($hubPage['meta_title'] ?? $pageTitle).' | '.$_siteName);
$metaDesc   = htmlspecialchars($hubPage['meta_description'] ?? 'Conseils immobiliers, guides d\'achat, vente et investissement. Retrouvez nos articles et analyses du marché.');
$queryParts = [];
if ($catFilter) { $queryParts[] = 'categorie='.urlencode($catFilter); }
if ($search !== '') { $queryParts[] = 'search='.urlencode($search); }
if ($page > 1) { $queryParts[] = 'page='.$page; }
$canonical  = $_siteUrl.'/blog'.(!empty($queryParts) ? '?'.implode('&', $queryParts) : '');

function getCatIcon(string $cat): string {
    $map = ['achat'=>'fa-home','vente'=>'fa-tag','investis'=>'fa-chart-line','marché'=>'fa-chart-bar','financement'=>'fa-piggy-bank','conseil'=>'fa-lightbulb','bordeaux'=>'fa-map-marker-alt','quartier'=>'fa-map','fiscal'=>'fa-file-invoice','guide'=>'fa-book-open','secteur'=>'fa-map-marker-alt'];
    foreach ($map as $k=>$v) if (str_contains(strtolower($cat),$k)) return $v;
    return 'fa-pen-nib';
}

function truncateBlog(string $text, int $max): string {
    $text = strip_tags($text);
    return mb_strlen($text) > $max ? mb_substr($text,0,$max).'…' : $text;
}

function readingTimeBlog(string $content): int {
    return max(1, intval(str_word_count(strip_tags($content)) / 200));
}

function formatDateBlog(string $date): string {
    $ts = strtotime($date);
    if (!$ts) return '';
    $months = ['jan.','fév.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
    return date('d',$ts).' '.$months[intval(date('m',$ts))-1].' '.date('Y',$ts);
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $metaTitle ?></title>
<meta name="description" content="<?= $metaDesc ?>">
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<meta property="og:title"       content="<?= $metaTitle ?>">
<meta property="og:description" content="<?= $metaDesc ?>">
<meta property="og:type"        content="website">
<meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:site_name"   content="<?= htmlspecialchars($_siteName) ?>">
<?php if ($featured && !empty($featured['featured_image'])): ?>
<meta property="og:image" content="<?= htmlspecialchars($featured['featured_image']) ?>">
<?php endif; ?>

<!-- Schema.org -->
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"Blog",
  "name":"<?= addslashes($pageTitle) ?>",
  "description":"<?= addslashes(strip_tags($metaDesc)) ?>",
  "url":"<?= addslashes($canonical) ?>",
  "publisher":{"@type":"Organization","name":"<?= addslashes($_siteName) ?>","url":"<?= $_siteUrl ?>"}
}
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<?php if (class_exists('SiteSettings')): ?><?= SiteSettings::trackingHead() ?><?php endif; ?>
<?= eduardoHead() ?>

<style>
/* ═══════════════════════════════════════════════════════════
   BLOG LISTING  v2.0
   Palette : #1a4d7a · #d4a574 · #f9f6f3
   Typo    : Playfair Display + DM Sans
═══════════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --blue:      #1a4d7a;
  --blue-dk:   #0f2d4a;
  --blue-lt:   #2a6da6;
  --gold:      #d4a574;
  --gold-lt:   #e8c49a;
  --gold-bg:   #fdf6ee;
  --bg:        #f9f6f3;
  --white:     #ffffff;
  --border:    #e8ddd4;
  --border-lt: #f0ebe6;
  --text:      #1e2d3d;
  --text-2:    #536070;
  --text-3:    #8fa3b8;
  --ff-h:      'Playfair Display',Georgia,serif;
  --ff-b:      'DM Sans',system-ui,sans-serif;
  --r:         12px;
  --r-lg:      18px;
  --r-xl:      24px;
  --sh-sm:     0 2px 8px rgba(26,77,122,.07);
  --sh-md:     0 8px 28px rgba(26,77,122,.11);
  --sh-lg:     0 20px 56px rgba(26,77,122,.16);
}

body{font-family:var(--ff-b);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;line-height:1.6}
a{color:inherit;text-decoration:none}
img{display:block;max-width:100%}

/* ──────────────────────────────
   HERO
────────────────────────────── */
.bl-hero{
  background:linear-gradient(145deg,var(--blue-dk) 0%,var(--blue) 55%,var(--blue-lt) 100%);
  padding:88px 24px 72px;
  position:relative;overflow:hidden;text-align:center;color:#fff;
}
.bl-hero::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 60% 50% at 15% 60%,rgba(212,165,116,.14) 0%,transparent 60%),
    radial-gradient(ellipse 40% 60% at 85% 10%,rgba(255,255,255,.06) 0%,transparent 50%);
}
/* Motif de points subtil */
.bl-hero::after{
  content:'';position:absolute;inset:0;pointer-events:none;
  background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);
  background-size:32px 32px;
}
.bl-hero__inner{max-width:760px;margin:0 auto;position:relative;z-index:1}

.bl-hero__pretitle{
  display:inline-flex;align-items:center;gap:7px;
  background:rgba(212,165,116,.18);border:1px solid rgba(212,165,116,.4);
  color:var(--gold-lt);padding:6px 20px;border-radius:30px;
  font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;
  margin-bottom:20px;
}
.bl-hero__title{
  font-family:var(--ff-h);
  font-size:clamp(32px,5.5vw,58px);
  font-weight:800;line-height:1.1;margin-bottom:16px;letter-spacing:-.02em;
}
.bl-hero__title em{color:var(--gold-lt);font-style:normal}
.bl-hero__sub{
  font-size:17px;opacity:.82;line-height:1.7;
  max-width:540px;margin:0 auto 32px;
}
.bl-search{
  max-width:580px;margin:0 auto 30px;
  display:flex;gap:10px;align-items:center;justify-content:center;
}
.bl-search__input{
  flex:1;min-width:220px;
  border:1px solid rgba(255,255,255,.35);
  background:rgba(255,255,255,.12);
  color:#fff;border-radius:10px;padding:12px 14px;
}
.bl-search__input::placeholder{color:rgba(255,255,255,.75)}
.bl-search__btn{
  border:1px solid rgba(255,255,255,.5);background:#fff;color:var(--blue-dk);
  border-radius:10px;padding:12px 16px;font-weight:700;cursor:pointer;
}
.bl-hero__stats{display:flex;justify-content:center;gap:40px;flex-wrap:wrap}
.bl-hero__stat-num{font-family:var(--ff-h);font-size:30px;font-weight:800;color:var(--gold-lt)}
.bl-hero__stat-lbl{font-size:11px;opacity:.6;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}

/* Vague décorative */
.bl-hero-wave{line-height:0;background:var(--bg)}
.bl-hero-wave svg{display:block;width:100%}

/* ──────────────────────────────
   BARRE CATÉGORIES (sticky)
────────────────────────────── */
.bl-cats-bar{
  background:var(--white);border-bottom:1px solid var(--border);
  position:sticky;top:0;z-index:50;
  box-shadow:0 2px 14px rgba(26,77,122,.07);
}
.bl-cats-inner{
  max-width:1240px;margin:0 auto;
  display:flex;gap:6px;overflow-x:auto;
  padding:13px 24px;scrollbar-width:none;
}
.bl-cats-inner::-webkit-scrollbar{display:none}

.bl-cat{
  display:inline-flex;align-items:center;gap:6px;
  padding:8px 18px;border-radius:30px;
  font-size:13px;font-weight:600;white-space:nowrap;
  transition:all .22s;border:2px solid transparent;
  font-family:var(--ff-b);
}
.bl-cat i{font-size:11px}
.bl-cat--all{background:var(--blue);color:#fff}
.bl-cat--all:hover{background:var(--blue-dk)}
.bl-cat--item{color:var(--text-2);border-color:var(--border)}
.bl-cat--item:hover{border-color:var(--blue);color:var(--blue);background:rgba(26,77,122,.04)}
.bl-cat--active{background:var(--blue);color:#fff;border-color:var(--blue)}
.bl-cat__nb{opacity:.6;font-size:11px}

/* ──────────────────────────────
   FEATURED
────────────────────────────── */
.bl-featured-wrap{max-width:1240px;margin:0 auto;padding:52px 24px 36px}
.bl-featured-label{
  display:flex;align-items:center;gap:10px;
  font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;
  color:var(--gold);margin-bottom:20px;
}
.bl-featured-label::after{content:'';flex:1;height:1px;background:var(--border)}

.bl-featured{
  display:grid;grid-template-columns:1.15fr 1fr;
  border-radius:var(--r-xl);overflow:hidden;
  box-shadow:var(--sh-lg);border:1px solid var(--border);
  background:var(--white);transition:all .35s;
}
.bl-featured:hover{transform:translateY(-5px);box-shadow:0 28px 70px rgba(26,77,122,.2)}
@media(max-width:840px){.bl-featured{grid-template-columns:1fr}}

.bl-featured__img-w{position:relative;overflow:hidden;min-height:380px}
.bl-featured__img{width:100%;height:100%;object-fit:cover;transition:transform .55s ease}
.bl-featured:hover .bl-featured__img{transform:scale(1.04)}
.bl-featured__img--ph{
  width:100%;height:100%;min-height:380px;
  background:linear-gradient(135deg,var(--blue-dk),var(--blue));
  display:flex;align-items:center;justify-content:center;
  color:rgba(255,255,255,.15);font-size:90px;
}
.bl-featured__top-tag{
  position:absolute;top:18px;left:18px;
  background:var(--gold);color:#fff;
  padding:4px 13px;border-radius:20px;
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
}

.bl-featured__body{
  padding:46px 44px;display:flex;flex-direction:column;justify-content:center;
}
@media(max-width:600px){.bl-featured__body{padding:28px 22px}}
.bl-featured__cat{
  display:inline-flex;align-items:center;gap:6px;
  color:var(--gold);font-size:11px;font-weight:700;
  text-transform:uppercase;letter-spacing:.6px;margin-bottom:14px;
}
.bl-featured__title{
  font-family:var(--ff-h);
  font-size:clamp(22px,2.4vw,30px);font-weight:700;
  color:var(--blue);line-height:1.28;margin-bottom:14px;
  transition:color .2s;
}
.bl-featured:hover .bl-featured__title{color:var(--blue-lt)}
.bl-featured__excerpt{
  font-size:15px;color:var(--text-2);line-height:1.75;
  margin-bottom:22px;
  display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
}
.bl-featured__foot{
  display:flex;align-items:center;justify-content:space-between;
  padding-top:18px;border-top:1px solid var(--border-lt);flex-wrap:wrap;gap:12px;
}
.bl-featured__meta{display:flex;align-items:center;gap:16px;font-size:12px;color:var(--text-3);flex-wrap:wrap}
.bl-featured__meta span{display:flex;align-items:center;gap:5px}
.bl-featured__read{
  display:inline-flex;align-items:center;gap:7px;
  background:var(--blue);color:#fff;padding:9px 20px;
  border-radius:30px;font-size:13px;font-weight:600;
  transition:all .2s;
}
.bl-featured:hover .bl-featured__read{background:var(--blue-lt);transform:translateX(3px)}
.bl-featured__read i{transition:transform .2s}
.bl-featured:hover .bl-featured__read i{transform:translateX(4px)}

/* ──────────────────────────────
   GRILLE
────────────────────────────── */
.bl-grid-wrap{max-width:1240px;margin:0 auto;padding:0 24px 90px}
.bl-grid-head{
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:28px;padding-bottom:16px;
  border-bottom:2px solid var(--border);
}
.bl-grid-head__title{
  font-family:var(--ff-h);font-size:22px;font-weight:700;color:var(--blue);
}
.bl-grid-head__title em{color:var(--gold);font-style:normal}
.bl-grid-head__count{
  font-size:12px;color:var(--text-3);
  background:var(--bg);padding:4px 14px;
  border-radius:20px;border:1px solid var(--border);
}

.bl-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
  gap:26px;
}
@media(max-width:560px){.bl-grid{grid-template-columns:1fr}}

/* Card */
.bl-card{
  background:var(--white);
  border-radius:var(--r-lg);
  border:1px solid var(--border);
  box-shadow:var(--sh-sm);
  overflow:hidden;transition:all .3s;
  display:flex;flex-direction:column;
}
.bl-card:hover{
  transform:translateY(-6px);
  box-shadow:var(--sh-md);
  border-color:var(--gold);
}
.bl-card__img-w{position:relative;overflow:hidden;height:210px;flex-shrink:0}
.bl-card__img{width:100%;height:100%;object-fit:cover;transition:transform .45s ease}
.bl-card:hover .bl-card__img{transform:scale(1.06)}
.bl-card__img--ph{
  width:100%;height:100%;
  background:linear-gradient(135deg,var(--blue-dk),var(--blue));
  display:flex;align-items:center;justify-content:center;
  color:rgba(255,255,255,.18);font-size:46px;
}
.bl-card__cat{
  position:absolute;top:14px;left:14px;
  background:rgba(212,165,116,.92);
  backdrop-filter:blur(4px);
  color:#fff;padding:4px 12px;border-radius:20px;
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
}
.bl-card__views{
  position:absolute;top:14px;right:14px;
  background:rgba(0,0,0,.45);backdrop-filter:blur(4px);
  color:#fff;padding:3px 9px;border-radius:12px;
  font-size:10px;display:flex;align-items:center;gap:4px;
}

.bl-card__body{padding:22px;flex:1;display:flex;flex-direction:column}
.bl-card__title{
  font-family:var(--ff-h);font-size:17px;font-weight:700;
  color:var(--blue);line-height:1.35;margin-bottom:10px;
  transition:color .2s;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.bl-card:hover .bl-card__title{color:var(--blue-lt)}
.bl-card__excerpt{
  font-size:14px;color:var(--text-2);line-height:1.65;
  flex:1;margin-bottom:16px;
  display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
}
.bl-card__foot{
  display:flex;justify-content:space-between;align-items:center;
  padding-top:14px;border-top:1px solid var(--border-lt);
}
.bl-card__meta{display:flex;gap:12px;font-size:11px;color:var(--text-3)}
.bl-card__meta span{display:flex;align-items:center;gap:4px}
.bl-card__lire{
  display:flex;align-items:center;gap:5px;
  color:var(--blue);font-size:13px;font-weight:600;
  transition:all .2s;
}
.bl-card:hover .bl-card__lire{color:var(--gold);gap:8px}

/* ──────────────────────────────
   EMPTY
────────────────────────────── */
.bl-empty{
  text-align:center;padding:80px 20px;
  background:var(--white);border-radius:var(--r-lg);
  border:1px solid var(--border);
}
.bl-empty__icon{font-size:56px;color:var(--border);margin-bottom:16px}
.bl-empty__title{font-family:var(--ff-h);font-size:24px;color:var(--blue);margin-bottom:8px}
.bl-empty__text{font-size:15px;color:var(--text-2);margin-bottom:22px}
.bl-empty__btn{
  display:inline-flex;align-items:center;gap:7px;
  background:var(--blue);color:#fff;padding:11px 24px;
  border-radius:30px;font-size:14px;font-weight:600;transition:all .2s;
}
.bl-empty__btn:hover{background:var(--blue-lt)}

/* ──────────────────────────────
   PAGINATION
────────────────────────────── */
.bl-pagination{
  display:flex;justify-content:center;align-items:center;
  gap:6px;margin-top:52px;flex-wrap:wrap;
}
.bl-pag-btn{
  display:flex;align-items:center;justify-content:center;
  min-width:42px;height:42px;padding:0 14px;
  border-radius:var(--r);font-size:14px;font-weight:600;
  border:2px solid var(--border);color:var(--text-2);
  background:var(--white);transition:all .22s;
}
.bl-pag-btn:hover{border-color:var(--blue);color:var(--blue)}
.bl-pag-btn--active{background:var(--blue);color:#fff;border-color:var(--blue)}
.bl-pag-btn--disabled{opacity:.3;pointer-events:none}
.bl-pag-dots{color:var(--border);padding:0 6px}

/* ──────────────────────────────
   NEWSLETTER
────────────────────────────── */
.bl-newsletter{
  background:linear-gradient(145deg,var(--blue-dk) 0%,var(--blue) 100%);
  padding:72px 24px;text-align:center;color:#fff;
  position:relative;overflow:hidden;
}
.bl-newsletter::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 50% 80% at 10% 50%,rgba(212,165,116,.12),transparent 60%),
    radial-gradient(ellipse 40% 60% at 90% 30%,rgba(255,255,255,.05),transparent 50%);
}
.bl-newsletter__inner{max-width:560px;margin:0 auto;position:relative;z-index:1}
.bl-newsletter__badge{
  display:inline-flex;align-items:center;gap:7px;
  background:rgba(212,165,116,.2);border:1px solid rgba(212,165,116,.4);
  color:var(--gold-lt);padding:5px 16px;border-radius:30px;
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;
  margin-bottom:18px;
}
.bl-newsletter__title{
  font-family:var(--ff-h);
  font-size:clamp(24px,3.5vw,36px);font-weight:700;margin-bottom:10px;
}
.bl-newsletter__sub{font-size:16px;opacity:.8;line-height:1.65;margin-bottom:28px}
.bl-newsletter__form{
  display:flex;gap:10px;max-width:460px;margin:0 auto 14px;flex-wrap:wrap;
  justify-content:center;
}
.bl-newsletter__input{
  flex:1;min-width:200px;
  padding:14px 20px;border-radius:30px;
  border:2px solid rgba(255,255,255,.2);background:rgba(255,255,255,.12);
  color:#fff;font-size:15px;outline:none;font-family:var(--ff-b);
  transition:border-color .2s;
  backdrop-filter:blur(8px);
}
.bl-newsletter__input::placeholder{color:rgba(255,255,255,.5)}
.bl-newsletter__input:focus{border-color:var(--gold)}
.bl-newsletter__btn{
  padding:14px 28px;background:var(--gold);color:#fff;
  border:none;border-radius:30px;font-size:15px;font-weight:700;
  cursor:pointer;font-family:var(--ff-b);transition:all .2s;
  display:flex;align-items:center;gap:7px;
}
.bl-newsletter__btn:hover{background:var(--gold-lt);transform:translateY(-2px)}
.bl-newsletter__note{font-size:12px;opacity:.5}

/* ──────────────────────────────
   RESPONSIVE
────────────────────────────── */
@media(max-width:1000px){
  .bl-featured{grid-template-columns:1fr}
  .bl-featured__img-w{min-height:260px}
}
@media(max-width:640px){
  .bl-hero{padding:64px 16px 52px}
  .bl-featured-wrap,.bl-grid-wrap{padding-left:16px;padding-right:16px}
  .bl-featured__body{padding:26px 20px}
}

/* Animations légères */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.bl-featured,.bl-card{animation:fadeUp .45s ease both}
.bl-card:nth-child(1){animation-delay:.05s}
.bl-card:nth-child(2){animation-delay:.1s}
.bl-card:nth-child(3){animation-delay:.15s}
.bl-card:nth-child(4){animation-delay:.2s}
.bl-card:nth-child(5){animation-delay:.25s}
.bl-card:nth-child(6){animation-delay:.3s}
</style>
</head>
<body>
<?php if (class_exists('SiteSettings')): ?><?= SiteSettings::trackingBody() ?><?php endif; ?>
<?php if (!empty($hf['header'])): echo renderHeader($hf['header']); endif; ?>

<main id="main-content">

<!-- ══ HERO ══ -->
<section class="bl-hero">
    <div class="bl-hero__inner">
        <div class="bl-hero__pretitle">
            <i class="fas fa-pen-nib"></i>
            <?= $catFilter ? 'Catégorie · '.htmlspecialchars($catFilter) : 'Blog immobilier' ?>
        </div>
        <h1 class="bl-hero__title">
            <?php if ($catFilter): ?>
                <?= htmlspecialchars($catFilter) ?>
            <?php else: ?>
                Conseils &amp; Actualités<br><em>Immobilier Bordeaux</em>
            <?php endif; ?>
        </h1>
        <p class="bl-hero__sub">
            <?= $catFilter
                ? 'Tous les articles sur <strong>'.htmlspecialchars($catFilter).'</strong>.'
                : 'Achat, vente, investissement, marché bordelais — les conseils d\'un expert local à votre service.' ?>
        </p>
        <form method="GET" action="/blog" class="bl-search" role="search">
            <?php if ($catFilter): ?>
                <input type="hidden" name="categorie" value="<?= htmlspecialchars($catFilter) ?>">
            <?php endif; ?>
            <input
                class="bl-search__input"
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Rechercher un article…"
                aria-label="Rechercher dans le blog"
            >
            <button class="bl-search__btn" type="submit">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </form>
        <?php if (!$catFilter && $total > 0): ?>
        <div class="bl-hero__stats">
            <div>
                <div class="bl-hero__stat-num"><?= $total ?></div>
                <div class="bl-hero__stat-lbl">Articles</div>
            </div>
            <?php if (!empty($categories)): ?>
            <div>
                <div class="bl-hero__stat-num"><?= count($categories) ?></div>
                <div class="bl-hero__stat-lbl">Catégories</div>
            </div>
            <?php endif; ?>
            <div>
                <div class="bl-hero__stat-num">Bordeaux</div>
                <div class="bl-hero__stat-lbl">Expert local</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Vague -->
<div class="bl-hero-wave">
    <svg viewBox="0 0 1440 40" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,40 C360,0 1080,0 1440,40 L1440,40 L0,40 Z" fill="#f9f6f3"/>
    </svg>
</div>

<!-- ══ BARRE CATÉGORIES ══ -->
<?php if (!empty($categories)): ?>
<nav class="bl-cats-bar" aria-label="Filtrer par catégorie">
    <div class="bl-cats-inner">
        <a href="/blog<?= $search !== '' ? '?search='.urlencode($search) : '' ?>" class="bl-cat <?= !$catFilter ? 'bl-cat--all' : 'bl-cat--item' ?>">
            <i class="fas fa-th-large"></i> Tous
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="/blog?categorie=<?= urlencode($cat['cat']) ?><?= $search !== '' ? '&search='.urlencode($search) : '' ?>"
           class="bl-cat <?= $catFilter===$cat['cat'] ? 'bl-cat--active' : 'bl-cat--item' ?>">
            <i class="fas <?= getCatIcon($cat['cat']) ?>"></i>
            <?= htmlspecialchars($cat['cat']) ?>
            <span class="bl-cat__nb">(<?= $cat['nb'] ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>

<!-- ══ HUB CONTENT ══ -->
<?php if ($hubPage && !empty($hubPage['content']) && $page===1 && !$catFilter && $search === ''): ?>
<div class="bl-hub"><?= $hubPage['content'] ?></div>
<?php endif; ?>

<!-- ══ ARTICLE À LA UNE ══ -->
<?php if ($featured):
    $fi = $featured['featured_image'] ?? $featured['image'] ?? '';
    $fc = $featured['category'] ?? '';
    $fd = $featured['published_at'] ?? $featured['date_publication'] ?? $featured['created_at'] ?? '';
    $fa = $featured['author'] ?? '';
    $fe = $featured['extrait'] ?? truncateBlog($featured['contenu']??'', 200);
    $rt = $featured['reading_time'] ?? readingTimeBlog($featured['contenu']??'');
?>
<section class="bl-featured-wrap">
    <div class="bl-featured-label">
        <i class="fas fa-star"></i> Article à la une
    </div>
    <a href="/blog/<?= htmlspecialchars($featured['slug']) ?>" class="bl-featured">
        <div class="bl-featured__img-w">
            <?php if ($fi): ?>
                <img src="<?= htmlspecialchars($fi) ?>" alt="<?= htmlspecialchars($featured['titre']) ?>" class="bl-featured__img" loading="eager">
            <?php else: ?>
                <div class="bl-featured__img--ph"><i class="fas fa-newspaper"></i></div>
            <?php endif; ?>
            <?php if ($fc): ?>
                <span class="bl-featured__top-tag"><?= htmlspecialchars($fc) ?></span>
            <?php endif; ?>
        </div>
        <div class="bl-featured__body">
            <?php if ($fc): ?>
            <div class="bl-featured__cat">
                <i class="fas <?= getCatIcon($fc) ?>"></i>
                <?= htmlspecialchars($fc) ?>
            </div>
            <?php endif; ?>
            <h2 class="bl-featured__title"><?= htmlspecialchars($featured['titre']) ?></h2>
            <?php if ($fe): ?>
            <p class="bl-featured__excerpt"><?= htmlspecialchars($fe) ?></p>
            <?php endif; ?>
            <div class="bl-featured__foot">
                <div class="bl-featured__meta">
                    <?php if ($fa): ?><span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($fa) ?></span><?php endif; ?>
                    <?php if ($fd): ?><span><i class="far fa-calendar-alt"></i> <?= formatDateBlog($fd) ?></span><?php endif; ?>
                    <span><i class="far fa-clock"></i> <?= $rt ?> min</span>
                </div>
                <span class="bl-featured__read">
                    Lire l'article <i class="fas fa-arrow-right"></i>
                </span>
            </div>
        </div>
    </a>
</section>
<?php endif; ?>

<!-- ══ GRILLE ══ -->
<section class="bl-grid-wrap">
    <?php if (!empty($articles)): ?>
    <div class="bl-grid-head">
        <div class="bl-grid-head__title">
            <?= $catFilter ? '<em>'.htmlspecialchars($catFilter).'</em>' : 'Tous les articles' ?>
        </div>
        <div class="bl-grid-head__count">
            <?= $total ?> article<?= $total>1?'s':'' ?>
        </div>
    </div>
    <div class="bl-grid" role="list">
        <?php foreach ($articles as $art):
            $ai = $art['featured_image'] ?? $art['image'] ?? '';
            $ac = $art['category'] ?? '';
            $ad = $art['published_at'] ?? $art['date_publication'] ?? $art['created_at'] ?? '';
            $ae = $art['extrait'] ?? '';
            $av = !empty($art['views']) ? $art['views'] : null;
            $ar = $art['reading_time'] ?? readingTimeBlog($art['contenu']??'');
        ?>
        <a href="/blog/<?= htmlspecialchars($art['slug']) ?>" class="bl-card" role="listitem">
            <div class="bl-card__img-w">
                <?php if ($ai): ?>
                    <img src="<?= htmlspecialchars($ai) ?>" alt="<?= htmlspecialchars($art['titre']) ?>" class="bl-card__img" loading="lazy">
                <?php else: ?>
                    <div class="bl-card__img--ph"><i class="fas fa-home"></i></div>
                <?php endif; ?>
                <?php if ($ac): ?>
                    <span class="bl-card__cat"><?= htmlspecialchars($ac) ?></span>
                <?php endif; ?>
                <?php if ($av): ?>
                    <span class="bl-card__views"><i class="fas fa-eye"></i> <?= number_format((int)$av) ?></span>
                <?php endif; ?>
            </div>
            <div class="bl-card__body">
                <h3 class="bl-card__title"><?= htmlspecialchars($art['titre']) ?></h3>
                <?php if ($ae): ?>
                    <p class="bl-card__excerpt"><?= htmlspecialchars(truncateBlog($ae, 130)) ?></p>
                <?php endif; ?>
                <div class="bl-card__foot">
                    <div class="bl-card__meta">
                        <?php if ($ad): ?><span><i class="far fa-calendar-alt"></i> <?= formatDateBlog($ad) ?></span><?php endif; ?>
                        <span><i class="far fa-clock"></i> <?= $ar ?> min</span>
                    </div>
                    <span class="bl-card__lire">Lire <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="bl-empty">
        <div class="bl-empty__icon"><i class="fas fa-search"></i></div>
        <h2 class="bl-empty__title">Aucun article trouvé</h2>
        <p class="bl-empty__text">
            <?= $catFilter ? 'Aucun article publié dans « '.htmlspecialchars($catFilter).' ».' : 'Aucun article publié pour l\'instant.' ?>
            <?php if ($search !== ''): ?>
                Recherche: « <?= htmlspecialchars($search) ?> ».
            <?php endif; ?>
        </p>
        <?php if ($catFilter || $search !== ''): ?>
        <a href="/blog" class="bl-empty__btn"><i class="fas fa-arrow-left"></i> Réinitialiser les filtres</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1):
        $baseParams = [];
        if ($catFilter) { $baseParams[] = 'categorie='.urlencode($catFilter); }
        if ($search !== '') { $baseParams[] = 'search='.urlencode($search); }
        $base = '/blog?'.(!empty($baseParams) ? implode('&', $baseParams).'&' : '').'page=';
    ?>
    <nav class="bl-pagination" aria-label="Pagination">
        <a href="<?= $base.($page-1) ?>" class="bl-pag-btn <?= $page<=1?'bl-pag-btn--disabled':'' ?>" aria-label="Page précédente">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php
        $prev = null; $range = [];
        for ($i=1;$i<=$totalPages;$i++) if ($i===1||$i===$totalPages||($i>=$page-2&&$i<=$page+2)) $range[]=$i;
        foreach ($range as $p):
            if ($prev!==null && $p-$prev>1): ?><span class="bl-pag-dots">…</span><?php endif; ?>
            <a href="<?= $base.$p ?>" class="bl-pag-btn <?= $p===$page?'bl-pag-btn--active':'' ?>" <?= $p===$page?'aria-current="page"':'' ?>><?= $p ?></a>
        <?php $prev=$p; endforeach; ?>
        <a href="<?= $base.($page+1) ?>" class="bl-pag-btn <?= $page>=$totalPages?'bl-pag-btn--disabled':'' ?>" aria-label="Page suivante">
            <i class="fas fa-chevron-right"></i>
        </a>
    </nav>
    <?php endif; ?>

</section>

<!-- ══ NEWSLETTER ══ -->
<section class="bl-newsletter">
    <div class="bl-newsletter__inner">
        <div class="bl-newsletter__badge">
            <i class="fas fa-envelope"></i> Newsletter
        </div>
        <h2 class="bl-newsletter__title">Restez informé du<br>marché bordelais</h2>
        <p class="bl-newsletter__sub">
            Recevez chaque mois les derniers conseils et actualités immobilières directement dans votre boîte mail.
        </p>
        <div class="bl-newsletter__form">
            <input type="email" class="bl-newsletter__input" placeholder="Votre adresse e-mail" id="nlEmail">
            <button type="button" class="bl-newsletter__btn" onclick="nlSubscribe()">
                <i class="fas fa-paper-plane"></i> Je m'abonne
            </button>
        </div>
        <p class="bl-newsletter__note">Sans spam · Désinscription en 1 clic · RGPD conforme</p>
    </div>
</section>

</main>

<?php if (!empty($hf['footer'])): echo renderFooter($hf['footer']); endif; ?>

<script>
function nlSubscribe() {
    const email = document.getElementById('nlEmail').value.trim();
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Veuillez entrer une adresse e-mail valide.');
        return;
    }
    const fd = new FormData();
    fd.append('email', email);
    fd.append('source', 'blog_newsletter');
    fetch('/admin/api/marketing/leads.php', {method:'POST',body:fd})
        .then(r=>r.json())
        .then(d=>{
            document.querySelector('.bl-newsletter__form').innerHTML =
                '<p style="color:var(--gold-lt);font-size:16px;font-weight:600;text-align:center"><i class="fas fa-check-circle"></i> Merci ! Vous êtes bien inscrit(e).</p>';
        })
        .catch(()=>{
            document.querySelector('.bl-newsletter__form').innerHTML =
                '<p style="color:var(--gold-lt);font-size:16px;font-weight:600;text-align:center"><i class="fas fa-check-circle"></i> Merci ! Vous êtes bien inscrit(e).</p>';
        });
}
</script>
</body>
</html>
