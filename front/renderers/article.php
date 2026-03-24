<?php
if (!isset($db) || !isset($articleSlug)) { http_response_code(403); exit('Accès direct interdit.'); }

$article = null;
try {
    $stmt = $db->prepare("SELECT * FROM articles WHERE slug = ? AND (statut = 'publie' OR status = 'published') LIMIT 1");
    $stmt->execute([$articleSlug]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log("Article error: " . $e->getMessage()); }

if (!$article) { http_response_code(404); require __DIR__ . '/404.php'; exit; }

try { $db->prepare("UPDATE articles SET views = COALESCE(views,0)+1 WHERE id = ?")->execute([$article['id']]); } catch(Exception $e){}

$related = [];
try {
    $cat = $article['category'] ?? '';
    $sql = "SELECT id, titre, slug, extrait, featured_image, image, category FROM articles WHERE (statut='publie' OR status='published') AND id != ?";
    $p = [$article['id']];
    if ($cat) { $sql .= " AND category = ?"; $p[] = $cat; }
    $sql .= " ORDER BY COALESCE(published_at,date_publication,created_at) DESC LIMIT 3";
    $stmt = $db->prepare($sql); $stmt->execute($p);
    $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e){}

$hf        = getHeaderFooter($db, 'blog/' . $articleSlug);
$_siteUrl  = siteUrl();
$_siteName = siteName();
$title     = $article['titre']          ?? '';
$content   = $article['contenu']        ?? '';
$excerpt   = $article['extrait']        ?? '';
$ogImage   = $article['featured_image'] ?? $article['image'] ?? '';
$author    = $article['author']         ?? 'Eduardo De Sul';
$category  = $article['category']       ?? '';
$pubDate   = $article['published_at']   ?? $article['date_publication'] ?? $article['created_at'] ?? '';
$readTime  = $article['reading_time']   ?? readingTime($content);
$canonical = $_siteUrl . '/blog/' . $articleSlug;
$metaTitle = $article['meta_title']     ?? $article['seo_title'] ?? $title;
$metaDesc  = $article['meta_description'] ?? $article['seo_description'] ?? $excerpt;
$phone     = _ss('phone','06 24 10 58 16');
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($metaTitle . ' | ' . $_siteName) ?></title>
<?php if ($metaDesc): ?><meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
<meta name="robots" content="<?= $article['noindex'] ? 'noindex,nofollow' : 'index,follow' ?>">
<link rel="canonical" href="<?= htmlspecialchars($article['canonical'] ?: $canonical) ?>">
<meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:site_name" content="<?= htmlspecialchars($_siteName) ?>">
<?php if ($metaDesc): ?><meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
<?php if ($ogImage):  ?><meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>
<meta property="article:published_time" content="<?= htmlspecialchars($pubDate) ?>">
<?php if (class_exists('SiteSettings')): ?><?= SiteSettings::trackingHead() ?><?php endif; ?>
<?= eduardoHead() ?>
<?php if (!empty($hf['header']['custom_css'])): ?><style><?= $hf['header']['custom_css'] ?></style><?php endif; ?>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Article","headline":<?= json_encode($title) ?>,"author":{"@type":"Person","name":<?= json_encode($author) ?>},"publisher":{"@type":"Organization","name":<?= json_encode($_siteName) ?>},"datePublished":<?= json_encode($pubDate?date('c',strtotime($pubDate)):'') ?>,"url":<?= json_encode($canonical) ?><?php if($ogImage):?>,"image":<?= json_encode($ogImage)?><?php endif;?>}</script>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Accueil","item":"<?=$_siteUrl?>/"},{"@type":"ListItem","position":2,"name":"Blog","item":"<?=$_siteUrl?>/blog"},{"@type":"ListItem","position":3,"name":<?=json_encode($title)?>,"item":"<?=htmlspecialchars($canonical)?>"}]}</script>
<style>
.art-hero{background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary));padding:60px 24px 80px;color:white;position:relative;overflow:hidden}
.art-hero__inner{max-width:860px;margin:0 auto;position:relative}
.art-hero__bc{display:flex;align-items:center;gap:8px;font-size:13px;opacity:.7;margin-bottom:20px;flex-wrap:wrap}
.art-hero__bc a{color:white;text-decoration:none}
.art-hero__cat{display:inline-flex;align-items:center;gap:6px;background:rgba(212,165,116,.25);border:1px solid rgba(212,165,116,.4);color:#e8c49a;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;text-transform:uppercase;margin-bottom:16px}
.art-hero__title{font-family:var(--ff-heading);font-size:clamp(26px,4vw,42px);font-weight:700;line-height:1.2;margin-bottom:18px}
.art-hero__meta{display:flex;align-items:center;gap:20px;font-size:13px;opacity:.8;flex-wrap:wrap}
.art-hero__meta span{display:flex;align-items:center;gap:6px}
.art-img-wrap{max-width:860px;margin:-40px auto 0;padding:0 24px;position:relative;z-index:2}
.art-img{width:100%;height:420px;object-fit:cover;border-radius:12px;box-shadow:0 20px 60px rgba(26,77,122,.2)}
.art-layout{max-width:1100px;margin:0 auto;padding:56px 24px 80px;display:grid;grid-template-columns:1fr 300px;gap:48px}
@media(max-width:900px){.art-layout{grid-template-columns:1fr}}
.art-body{font-size:17px;line-height:1.85;color:var(--ed-text,#2c3e50)}
.art-body h2{font-family:var(--ff-heading);font-size:24px;font-weight:700;color:var(--ed-primary);margin:40px 0 14px}
.art-body h3{font-family:var(--ff-heading);font-size:20px;font-weight:600;color:var(--ed-primary);margin:32px 0 12px}
.art-body p{margin-bottom:20px}
.art-body ul,.art-body ol{padding-left:24px;margin-bottom:20px}
.art-body li{margin-bottom:8px}
.art-body a{color:var(--ed-primary);text-decoration:underline}
.art-body blockquote{border-left:4px solid var(--ed-accent,#d4a574);padding:16px 20px;margin:28px 0;background:#fdf5ec;border-radius:0 12px 12px 0;font-style:italic;color:#666}
.art-share{display:flex;align-items:center;gap:12px;margin:40px 0;padding:20px;background:#fdf5ec;border-radius:12px;border:1px solid #e8ddd4;flex-wrap:wrap}
.art-share__label{font-size:14px;font-weight:600;color:#444}
.art-share__btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:50px;font-size:13px;font-weight:600;text-decoration:none;transition:all .3s}
.art-share__btn--fb{background:#1877f2;color:white}
.art-share__btn--li{background:#0a66c2;color:white}
.art-share__btn--wa{background:#25d366;color:white}
.art-author{display:flex;gap:20px;align-items:flex-start;padding:28px;background:linear-gradient(135deg,rgba(26,77,122,.06),rgba(212,165,116,.08));border-radius:16px;border:1px solid #e8ddd4;margin:40px 0}
.art-author__av{width:72px;height:72px;border-radius:50%;background:var(--ed-primary);display:flex;align-items:center;justify-content:center;font-size:28px;color:white;flex-shrink:0}
.art-author__name{font-family:var(--ff-heading);font-size:17px;font-weight:700;color:var(--ed-primary);margin-bottom:4px}
.art-author__role{font-size:12px;color:var(--ed-accent,#d4a574);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px}
.art-author__bio{font-size:14px;color:#666;line-height:1.6}
.art-related{margin-top:60px}
.art-related__title{font-family:var(--ff-heading);font-size:24px;font-weight:700;color:var(--ed-primary);margin-bottom:28px}
.art-related__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:24px}
.art-related__card{background:white;border-radius:12px;border:1px solid #e8ddd4;overflow:hidden;box-shadow:0 2px 12px rgba(26,77,122,.08);transition:all .3s;text-decoration:none;display:block}
.art-related__card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(26,77,122,.15)}
.art-related__img{width:100%;height:160px;object-fit:cover;display:block}
.art-related__body{padding:16px}
.art-related__cat{font-size:11px;font-weight:700;color:var(--ed-accent,#d4a574);text-transform:uppercase;margin-bottom:6px}
.art-related__name{font-family:var(--ff-heading);font-size:15px;font-weight:700;color:var(--ed-primary);line-height:1.3}
.art-sidebar{display:flex;flex-direction:column;gap:24px}
.art-widget{background:white;border-radius:12px;border:1px solid #e8ddd4;padding:24px;box-shadow:0 2px 12px rgba(26,77,122,.08)}
.art-widget__title{font-family:var(--ff-heading);font-size:16px;font-weight:700;color:var(--ed-primary);margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid var(--ed-accent,#d4a574)}
.art-toc{list-style:none;padding:0;margin:0}
.art-toc li{margin-bottom:8px}
.art-toc a{font-size:14px;color:#666;text-decoration:none;display:flex;align-items:center;gap:8px;transition:color .2s}
.art-toc a::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--ed-accent,#d4a574);flex-shrink:0}
.art-toc a:hover{color:var(--ed-primary)}
.art-cta-widget{background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary));color:white;border:none}
.art-cta-widget .art-widget__title{color:white;border-color:rgba(255,255,255,.2)}
.art-cta-widget p{font-size:14px;opacity:.85;margin-bottom:16px;line-height:1.6}
.art-cta-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;transition:all .3s;margin-bottom:10px;text-align:center}
.art-cta-btn--primary{background:var(--ed-accent,#d4a574);color:white}
.art-cta-btn--ghost{background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.3)}
</style>
</head>
<body>
<?php if (class_exists('SiteSettings')): ?><?= SiteSettings::trackingBody() ?><?php endif; ?>
<?php if (!empty($hf['header'])): echo renderHeader($hf['header']); endif; ?>
<main id="main-content">

<section class="art-hero">
    <div class="art-hero__inner">
        <nav class="art-hero__bc"><a href="/">Accueil</a> › <a href="/blog">Blog</a> › <span><?= htmlspecialchars($title) ?></span></nav>
        <?php if ($category): ?><div class="art-hero__cat"><i class="fas fa-tag"></i> <?= htmlspecialchars($category) ?></div><?php endif; ?>
        <h1 class="art-hero__title"><?= htmlspecialchars($title) ?></h1>
        <div class="art-hero__meta">
            <?php if ($author): ?><span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($author) ?></span><?php endif; ?>
            <?php if ($pubDate): ?><span><i class="far fa-calendar-alt"></i> <?= formatDateFr($pubDate) ?></span><?php endif; ?>
            <span><i class="far fa-clock"></i> <?= $readTime ?> min de lecture</span>
        </div>
    </div>
</section>

<?php if ($ogImage): ?>
<div class="art-img-wrap"><img src="<?= htmlspecialchars($ogImage) ?>" alt="<?= htmlspecialchars($title) ?>" class="art-img" loading="eager"></div>
<?php endif; ?>

<div class="art-layout">
    <article>
        <div class="art-body" id="art-body"><?= $content ?></div>

        <?php $su=urlencode($canonical);$st=urlencode($title); ?>
        <div class="art-share">
            <span class="art-share__label"><i class="fas fa-share-alt"></i> Partager :</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?=$su?>" target="_blank" rel="noopener" class="art-share__btn art-share__btn--fb"><i class="fab fa-facebook-f"></i> Facebook</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?=$su?>" target="_blank" rel="noopener" class="art-share__btn art-share__btn--li"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
            <a href="https://wa.me/?text=<?=$st?>%20<?=$su?>" target="_blank" rel="noopener" class="art-share__btn art-share__btn--wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        </div>

        <div class="art-author">
            <div class="art-author__av"><i class="fas fa-user"></i></div>
            <div>
                <div class="art-author__name"><?= htmlspecialchars($author) ?></div>
                <div class="art-author__role">Conseiller immobilier · eXp France</div>
                <p class="art-author__bio"><?= htmlspecialchars(_ss('agent_bio','Conseiller immobilier indépendant à Bordeaux. Accompagnement personnalisé pour tous vos projets immobiliers.')) ?></p>
            </div>
        </div>

        <?php if (!empty($related)): ?>
        <section class="art-related">
            <h2 class="art-related__title">Articles similaires</h2>
            <div class="art-related__grid">
            <?php foreach ($related as $r): $ri=$r['featured_image']??$r['image']??''; ?>
                <a href="/blog/<?= htmlspecialchars($r['slug']) ?>" class="art-related__card">
                    <?php if ($ri): ?><img src="<?=htmlspecialchars($ri)?>" alt="<?=htmlspecialchars($r['titre'])?>" class="art-related__img" loading="lazy">
                    <?php else: ?><div class="art-related__img" style="background:linear-gradient(135deg,var(--ed-primary-dk),var(--ed-primary));display:flex;align-items:center;justify-content:center;color:white;font-size:32px"><i class="fas fa-home"></i></div><?php endif; ?>
                    <div class="art-related__body">
                        <div class="art-related__cat"><?= htmlspecialchars($r['category']??'Immobilier') ?></div>
                        <div class="art-related__name"><?= htmlspecialchars($r['titre']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </article>

    <aside class="art-sidebar">
        <div class="art-widget">
            <div class="art-widget__title"><i class="fas fa-list" style="color:var(--ed-accent,#d4a574);margin-right:8px"></i> Sommaire</div>
            <ul class="art-toc" id="art-toc"><li style="color:#ccc;font-size:13px">Chargement…</li></ul>
        </div>
        <div class="art-widget art-cta-widget">
            <div class="art-widget__title"><i class="fas fa-phone-alt" style="margin-right:8px"></i> Besoin de conseils ?</div>
            <p>Eduardo répond à toutes vos questions gratuitement.</p>
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/','',$phone)) ?>" class="art-cta-btn art-cta-btn--primary"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($phone) ?></a>
            <a href="/estimation" class="art-cta-btn art-cta-btn--ghost"><i class="fas fa-calculator"></i> Estimer mon bien</a>
        </div>
        <div class="art-widget">
            <div class="art-widget__title"><i class="fas fa-info-circle" style="color:var(--ed-accent,#d4a574);margin-right:8px"></i> Infos</div>
            <?php if ($pubDate): ?><div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#666;margin-bottom:10px"><i class="far fa-calendar-alt" style="color:var(--ed-primary)"></i> <?= formatDateFr($pubDate) ?></div><?php endif; ?>
            <div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#666;margin-bottom:10px"><i class="far fa-clock" style="color:var(--ed-accent,#d4a574)"></i> <?= $readTime ?> min de lecture</div>
            <?php if ($category): ?><div style="display:flex;align-items:center;gap:10px;font-size:14px;color:#666"><i class="fas fa-tag" style="color:var(--ed-primary)"></i> <?= htmlspecialchars($category) ?></div><?php endif; ?>
        </div>
    </aside>
</div>

</main>
<?php if (!empty($hf['footer'])): echo renderFooter($hf['footer']); endif; ?>
<script>
(function(){
    var body=document.getElementById('art-body');
    var toc=document.getElementById('art-toc');
    if(!body||!toc)return;
    var hs=body.querySelectorAll('h2');
    if(hs.length===0){toc.innerHTML='<li style="color:#ccc;font-size:13px">Aucun titre</li>';return;}
    toc.innerHTML='';
    hs.forEach(function(h,i){
        h.id='h-'+i;
        var li=document.createElement('li');
        li.innerHTML='<a href="#h-'+i+'">'+h.textContent+'</a>';
        toc.appendChild(li);
    });
    toc.querySelectorAll('a').forEach(function(a){
        a.addEventListener('click',function(e){
            e.preventDefault();
            var t=document.querySelector(a.getAttribute('href'));
            if(t)t.scrollIntoView({behavior:'smooth',block:'start'});
        });
    });
})();
</script>
</body></html>
