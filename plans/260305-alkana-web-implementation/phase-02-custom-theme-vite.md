# Phase 02 — Custom Theme + Vite Build Pipeline

## Context Links
- Plan: [plan.md](plan.md)
- Brainstorm risk §3 (Tailwind on Shared Hosting), §4.3 (Vite Pipeline)
- Blueprint §7 Design Direction, §8 Performance

## Overview
| Item | Detail |
|---|---|
| **Priority** | Critical |
| **Status** | ⏳ pending |
| **Timeline** | Weeks 3–4 |
| **Depends on** | Phase 00 (Vite pipeline), Phase 01 (CPT/templates needed) |
| **Blocks** | Phase 06 (performance tuning needs completed theme) |

## Key Insights
- "Zero-Bloat" principle: single `app.css` + single `app.js` from Vite build. No jQuery, no WordPress default scripts.
- All pages must render from DB data — no hardcoded HTML content.
- Mobile-First: start with 320px breakpoint, layer up.
- CSS Variables for brand colors from Alkana logo. Defined once in `:root`, used everywhere.
- `functions.php` enqueues Vite output using manifest hash — cache-busting automatic.
- Template hierarchy: `archive-alkana_product.php`, `single-alkana_product.php`, `taxonomy-surface_type.php` etc.

## Requirements

### Functional
- All 9 site sections from Blueprint §3 have corresponding page templates.
- Product archive: grid view with filter panel (Bottom Sheet on mobile, sidebar on desktop).
- Single product: tabbed spec display, hide empty spec rows automatically.
- Homepage: Hero, USPs, Category grid, Featured products, Projects, News — all from DB.
- Mobile sticky CTA bar: tel, Zalo, Quote request.
- Responsive images: `srcset` on all product/project images.

### Non-Functional
- No jQuery (use Vanilla JS + Fetch API).
- No WordPress default scripts (dequeue `wp-embed`, `comment-reply`, etc.).
- Tailwind `content` purge scope limited to `*.php` templates only — no purge false positives.
- All colors via CSS custom properties — no hardcoded hex in PHP templates.

## Architecture

### Theme Directory Structure

```
wp-content/themes/alkana/
├── style.css                    # Theme header (no CSS here)
├── functions.php                # Require all inc/ files + enqueue assets
├── index.php                    # Fallback
│
├── templates/                   # Page templates (WP template hierarchy)
│   ├── front-page.php           # Homepage
│   ├── archive-alkana_product.php
│   ├── single-alkana_product.php
│   ├── taxonomy-product_category.php
│   ├── page-about.php
│   ├── page-solutions.php
│   ├── page-projects.php
│   ├── page-resources.php
│   ├── page-news.php
│   ├── page-careers.php
│   └── page-contact.php
│
├── template-parts/              # Reusable partials
│   ├── header.php
│   ├── footer.php
│   ├── nav-main.php
│   ├── product-card.php         # Used in archive + homepage
│   ├── product-specs-table.php  # Single product specs
│   ├── product-filter-panel.php # Filter sidebar/bottom-sheet
│   ├── project-card.php
│   ├── hero-banner.php
│   └── sticky-cta-mobile.php
│
├── inc/                         # PHP logic (loaded by functions.php)
│   ├── cpt-product.php
│   ├── cpt-project.php
│   ├── taxonomies.php
│   ├── enqueue-assets.php       # Reads dist/manifest.json
│   ├── db/
│   │   └── create-product-index-table.php
│   ├── hooks/
│   │   └── sync-product-index.php
│   └── ajax/
│       └── filter-handler.php   # Phase 03
│
├── src/                         # Source files (not deployed)
│   ├── styles/
│   │   ├── app.css              # @import tailwind + custom
│   │   ├── variables.css        # :root CSS custom properties
│   │   └── components/          # component-level CSS (minimal)
│   └── scripts/
│       ├── app.js               # Entry point
│       ├── filter.js            # AJAX filter controller
│       ├── mobile-menu.js       # Accordion nav
│       └── bottom-sheet.js      # Mobile filter panel
│
├── package.json
├── vite.config.js
├── tailwind.config.js
└── dist/                        # Gitignored — Vite output
    ├── app.[hash].css
    ├── app.[hash].js
    └── manifest.json
```

### CSS Brand Variables
```css
/* src/styles/variables.css */
:root {
  /* Brand colors from Alkana logo */
  --color-primary:    #E8611A;   /* Alkana orange */
  --color-secondary:  #1A3A5C;   /* Navy blue */
  --color-text:       #1A1A1A;   /* Dark charcoal */
  --color-text-muted: #666666;
  --color-bg:         #FFFFFF;
  --color-bg-light:   #F5F5F5;
  --color-border:     #E0E0E0;

  /* Typography */
  --font-heading: 'Montserrat', sans-serif;
  --font-body:    'Inter', sans-serif;

  /* Spacing */
  --radius-btn:  5px;
  --radius-card: 8px;
  --shadow-card: 0 2px 8px rgba(0,0,0,0.08);
}
```

### Asset Enqueue (functions.php)
```php
// inc/enqueue-assets.php
function alkana_enqueue_assets() {
  $manifest = json_decode(
    file_get_contents(get_template_directory() . '/dist/manifest.json'), true
  );
  $css = $manifest['src/styles/app.css']['file'] ?? 'app.css';
  $js  = $manifest['src/scripts/app.js']['file']  ?? 'app.js';

  wp_enqueue_style('alkana-app', get_template_directory_uri() . '/dist/' . $css, [], null);
  wp_enqueue_script('alkana-app', get_template_directory_uri() . '/dist/' . $js, [], null, true);

  // Pass AJAX config to JS
  wp_localize_script('alkana-app', 'AlkanaConfig', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('alkana_filter'),
  ]);
}
add_action('wp_enqueue_scripts', 'alkana_enqueue_assets');

// Dequeue WP bloat
add_action('wp_enqueue_scripts', function() {
  wp_dequeue_script('wp-embed');
  wp_dequeue_style('wp-block-library');
}, 100);
```

### Product Card Partial
```php
<!-- template-parts/product-card.php -->
<article class="product-card" data-id="<?= $post->ID ?>">
  <a href="<?= get_permalink() ?>">
    <?php if (has_post_thumbnail()): ?>
      <img src="<?= get_the_post_thumbnail_url(null, 'medium') ?>"
           loading="lazy"
           alt="<?= esc_attr(get_the_title()) ?>"
           class="product-card__image">
    <?php endif; ?>
    <div class="product-card__body">
      <h3 class="product-card__title"><?= get_the_title() ?></h3>
      <p class="product-card__sku"><?= get_field('_alkana_sku') ?></p>
      <p class="product-card__excerpt"><?= get_the_excerpt() ?></p>
    </div>
  </a>
  <a href="<?= get_permalink() ?>#contact" class="btn btn--outline">
    <?= __('Get Quote', 'alkana') ?>
  </a>
</article>
```

### Single Product Spec Table (auto-hide empty rows)
```php
<!-- template-parts/product-specs-table.php -->
<?php
$specs = [
  'Coverage'     => get_field('_alkana_coverage'),
  'Mixing Ratio' => get_field('_alkana_mix_ratio'),
  'Thinner'      => get_field('_alkana_thinner'),
  'Touch Dry'    => get_field('_alkana_dry_touch'),
  'Hard Dry'     => get_field('_alkana_dry_hard'),
  'Recoat Time'  => get_field('_alkana_dry_recoat'),
];
?>
<table class="specs-table">
  <?php foreach ($specs as $label => $value): ?>
    <?php if (!empty($value)): ?>
    <tr>
      <th><?= esc_html($label) ?></th>
      <td><?= esc_html($value) ?></td>
    </tr>
    <?php endif; ?>
  <?php endforeach; ?>
</table>
```

## Related Code Files
- Create: entire `wp-content/themes/alkana/` structure above
- Modify: `functions.php` (require all inc/ modules)

## Implementation Steps

1. Setup base theme (`style.css` header, `functions.php`, placeholder `index.php`).
2. Configure Vite + Tailwind (Phase 00 already verified build; now wire to actual theme).
3. Create CSS variables and Tailwind config matching Alkana brand.
4. Build shared `header.php` + `footer.php` + `nav-main.php` (mobile accordion).
5. Implement `front-page.php` with all homepage sections (query from DB).
6. Implement `archive-alkana_product.php` with product grid + filter panel placeholder.
7. Implement `single-alkana_product.php` with tabbed specs, images, resources download.
8. Implement all remaining page templates (about, solutions, projects, resources, careers, contact).
9. Build `sticky-cta-mobile.php` (fixed bottom bar: phone/Zalo/quote).
10. Implement `bottom-sheet.js` for mobile filter panel.
11. Build responsive image `srcset` in `product-card.php` using WP `wp_get_attachment_image`.
12. Dequeue WP default bloat scripts.
13. Verify `npm run build` → deploy to staging → cross-browser check (Chrome, Firefox, Safari iOS, Chrome Android).

## Todo List
- [ ] Base theme files (style.css, functions.php, index.php)
- [ ] CSS variables + brand colors
- [ ] Tailwind config (font families, brand color extend)
- [ ] header.php + footer.php + nav with mobile accordion
- [ ] front-page.php (all sections from DB)
- [ ] archive-alkana_product.php (grid layout)
- [ ] single-alkana_product.php (specs, tabs, PDFs)
- [ ] taxonomy-product_category.php
- [ ] page-about.php, page-solutions.php, page-projects.php
- [ ] page-resources.php (TDS/MSDS download center)
- [ ] page-careers.php, page-contact.php (with forms)
- [ ] sticky-cta-mobile.php
- [ ] bottom-sheet.js (mobile filter UX)
- [ ] mobile-menu.js (accordion)
- [ ] Dequeue WP bloat
- [ ] Vite build → staging deploy
- [ ] Cross-browser + mobile device test

## Success Criteria
- All 9 site sections render correctly from DB data
- Mobile Bottom Sheet filter opens/closes smoothly
- Sticky CTA shows on mobile below the fold
- Empty spec rows are hidden automatically
- PageSpeed mobile ≥ 70 at this stage (before performance tuning phase)

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| Design approval delays | Medium | Use wireframe-level fidelity for sprint — full polish in Sprint 4 UAT |
| Tailwind purge removes needed classes | Low | JIT mode is class-discovery safe; add safelist for dynamic classes |
| Bottom Sheet UX issues on iOS Safari | Medium | Test on real device week 3 |

## Security Considerations
- Escape all output: `esc_html()`, `esc_url()`, `esc_attr()` on all PHP template output
- Contact/quote forms: use nonce validation + honeypot anti-spam

## Next Steps
→ Phase 03: AJAX Faceted Filter wires into filter panel built in this phase
→ Phase 06: Performance tuning after all templates complete
