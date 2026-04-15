# Landing multilingue estimation — implémentation

## URLs actives
- FR: `/fr/estimation-immobiliere-aix-en-provence`
- EN: `/en/property-valuation-aix-en-provence`
- ES: `/es/valoracion-inmobiliaria-aix-en-provence`

## Choix d'architecture
- **Une seule page template** (`public/pages/conversion/international-valuation.php`) rend les 3 langues.
- **Contenus externalisés** dans `public/pages/conversion/config/international-valuation.php` (structure duplicable).
- **Routes explicites SEO-friendly** dans `public/index.php` pour garantir les slugs exacts demandés.
- **SEO technique** : canonical + `hreflang` FR/EN/ES + `x-default` injectés via le layout.

## Personnalisation future (autre conseiller / autre ville)
Modifier en priorité :
- `advisor.name`
- `advisor.city`
- `advisor.zone`
- `advisor.phone`
- `advisor.email`
- les `slug` de chaque langue
- les contenus des blocs (`hero`, `services`, `method`, `faq`, etc.)

## Variante Google Ads "sans navigation"
Le layout supporte maintenant :
- `$layoutMode = 'landing'`
- ou `$showPrimaryNav = false` / `$showPrimaryFooter = false`

Pour activer une version ultra-landing, définir ces variables dans la page avant rendu.
