"""
Scraper d'images — Page eXp France Pascal Hamm
Utilise Playwright pour rendre le JavaScript et extraire toutes les images

Installation :
    pip install playwright
    playwright install chromium

Usage :
    python scraper_exp_images.py
"""

import asyncio
import os
import re
import httpx
from urllib.parse import urljoin, urlparse
from playwright.async_api import async_playwright

URL = "https://mon.expfrance.fr/beckhamm-properties-pascal-hamm"
OUTPUT_DIR = "images_pascal_hamm"

# Extensions images acceptées
IMAGE_EXTENSIONS = {'.jpg', '.jpeg', '.png', '.webp', '.gif', '.avif'}

# Taille minimum pour ignorer les icônes/logos (en bytes)
MIN_SIZE = 20_000  # 20 Ko


async def scroll_page(page):
    """Scroll jusqu'en bas pour charger le lazy loading."""
    prev_height = 0
    for _ in range(20):
        height = await page.evaluate("document.body.scrollHeight")
        if height == prev_height:
            break
        await page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        await page.wait_for_timeout(1500)
        prev_height = height
    # Remonter en haut et attendre
    await page.evaluate("window.scrollTo(0, 0)")
    await page.wait_for_timeout(1000)


def is_image_url(url: str) -> bool:
    """Vérifie si l'URL pointe vers une image."""
    path = urlparse(url).path.lower()
    ext = os.path.splitext(path)[1]
    if ext in IMAGE_EXTENSIONS:
        return True
    # Certaines URLs n'ont pas d'extension mais contiennent /images/ ou /photos/
    if any(k in url.lower() for k in ['/photo', '/image', '/listing', '/property', '/bien']):
        return True
    return False


def clean_filename(url: str, index: int) -> str:
    """Génère un nom de fichier propre depuis une URL."""
    path = urlparse(url).path
    name = os.path.basename(path)
    # Supprimer les query strings
    name = name.split('?')[0]
    if not name or '.' not in name:
        name = f"image_{index:03d}.jpg"
    # Préfixer avec l'index pour garder l'ordre
    return f"{index:03d}_{name}"


async def download_image(client: httpx.AsyncClient, url: str, filepath: str) -> bool:
    """Télécharge une image et la sauvegarde."""
    try:
        response = await client.get(url, follow_redirects=True, timeout=30)
        if response.status_code == 200:
            content = response.content
            if len(content) >= MIN_SIZE:
                with open(filepath, 'wb') as f:
                    f.write(content)
                return True
            else:
                print(f"  ⚠ Trop petit ({len(content)} bytes), ignoré : {url}")
        else:
            print(f"  ✗ HTTP {response.status_code} : {url}")
    except Exception as e:
        print(f"  ✗ Erreur téléchargement : {e}")
    return False


async def main():
    os.makedirs(OUTPUT_DIR, exist_ok=True)
    print(f"📁 Dossier de sortie : {OUTPUT_DIR}/")
    print(f"🌐 Chargement de la page : {URL}\n")

    image_urls = set()

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36",
            viewport={"width": 1440, "height": 900}
        )

        # Intercepter les requêtes réseau pour capturer les URLs d'images
        network_images = set()

        async def on_response(response):
            url = response.url
            content_type = response.headers.get('content-type', '')
            if 'image' in content_type and is_image_url(url):
                network_images.add(url)

        page = await context.new_page()
        page.on("response", on_response)

        # Charger la page
        await page.goto(URL, wait_until="networkidle", timeout=60000)
        print("✅ Page chargée")

        # Attendre le contenu dynamique
        await page.wait_for_timeout(3000)

        # Scroll pour déclencher le lazy loading
        print("⬇ Scroll pour charger tout le contenu...")
        await scroll_page(page)
        await page.wait_for_timeout(2000)

        # Extraire les images du DOM
        dom_images = await page.evaluate("""
            () => {
                const imgs = [];

                // Balises <img>
                document.querySelectorAll('img').forEach(img => {
                    if (img.src) imgs.push(img.src);
                    if (img.dataset.src) imgs.push(img.dataset.src);
                    if (img.dataset.lazySrc) imgs.push(img.dataset.lazySrc);
                });

                // Background images dans style
                document.querySelectorAll('[style*="background"]').forEach(el => {
                    const match = el.style.backgroundImage.match(/url\\(['"]?([^'"\\)]+)['"]?\\)/);
                    if (match) imgs.push(match[1]);
                });

                // Attributs srcset
                document.querySelectorAll('[srcset]').forEach(el => {
                    el.srcset.split(',').forEach(entry => {
                        const url = entry.trim().split(' ')[0];
                        if (url) imgs.push(url);
                    });
                });

                return [...new Set(imgs)];
            }
        """)

        # Fusionner toutes les sources
        all_raw = set(dom_images) | network_images

        # Filtrer et nettoyer
        base_url = URL
        for url in all_raw:
            if not url or url.startswith('data:'):
                continue
            # Résoudre les URLs relatives
            if url.startswith('//'):
                url = 'https:' + url
            elif url.startswith('/'):
                url = urljoin(base_url, url)
            if url.startswith('http') and is_image_url(url):
                image_urls.add(url)

        await browser.close()

    print(f"\n🖼  {len(image_urls)} URLs d'images trouvées")

    if not image_urls:
        print("❌ Aucune image trouvée. La page est peut-être derrière authentification.")
        return

    # Téléchargement
    print(f"⬇ Téléchargement en cours...\n")
    downloaded = 0

    async with httpx.AsyncClient(
        headers={"User-Agent": "Mozilla/5.0 Chrome/120.0.0.0"},
        timeout=30
    ) as client:
        for i, url in enumerate(sorted(image_urls), 1):
            filename = clean_filename(url, i)
            filepath = os.path.join(OUTPUT_DIR, filename)

            if os.path.exists(filepath):
                print(f"  ↷ Existe déjà : {filename}")
                downloaded += 1
                continue

            print(f"  ↓ [{i}/{len(image_urls)}] {filename}")
            success = await download_image(client, url, filepath)
            if success:
                downloaded += 1
            await asyncio.sleep(0.3)  # Pause pour ne pas surcharger le serveur

    print(f"\n✅ {downloaded}/{len(image_urls)} images téléchargées dans '{OUTPUT_DIR}/'")


if __name__ == "__main__":
    asyncio.run(main())
