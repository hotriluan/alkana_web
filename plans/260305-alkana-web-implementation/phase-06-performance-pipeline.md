# Phase 06 — Performance Pipeline & UAT

## Context Links
- Plan: [plan.md](plan.md)
- Brainstorm §4.4 Performance Stack table
- Blueprint §8 Performance Pipeline

## Overview
| Item | Detail |
|---|---|
| **Priority** | High |
| **Status** | ⏳ pending |
| **Timeline** | Weeks 6–7 |
| **Depends on** | Phase 02 (theme complete), Phase 03 (filter working), Phase 05 (real data imported) |
| **Blocks** | Phase 07 (deploy only when perf targets met) |

## Key Insights
- Performance testing MUST use real product data and images — dummy data gives false lighthouse scores.
- Cloudflare Image Polish handles WebP conversion at CDN edge — no server ImageMagick needed.
- LiteSpeed Page Cache: exclude AJAX endpoints from cache; cache all static pages.
- Font loading: preconnect to Google Fonts, `font-display: swap` — prevents FOIT.
- Deferred JS: Vite already handles this with `type="module"` bundles (deferred by spec).
- LCP is the Hero banner image → `fetchpriority="high"` attribute + preload link tag.
- Target: PageSpeed Mobile ≥ 85, Desktop ≥ 95.

## Requirements

### Functional
- All images automatically output as WebP (via Cloudflare Polish).
- Hero banner image has `fetchpriority="high"` + `<link rel="preload">` in `<head>`.
- All below-fold images have `loading="lazy"`.
- CSS and JS from Vite are minified (handled by Vite build by default).
- LiteSpeed Cache plugin configured with page, object, and browser cache settings.
- Cloudflare proxy active with caching headers.
- Google Fonts loaded via preconnect + stylesheet (not `@import`).
- Core Web Vitals: LCP < 2.5s, CLS < 0.1, FID/INP < 200ms.

### Non-Functional
- PageSpeed Mobile ≥ 85 (hard gate for deployment).
- AJAX filter response < 500ms on staging with full product dataset.
- No render-blocking resources.
- `srcset` on all product images for responsive loading.

## Architecture

### Performance Stack Summary
| Layer | Implementation | Target |
|---|---|---|
| CSS/JS | Vite single bundle (minified + gzip) | < 50KB gzip each |
| Images | Cloudflare Image Polish (WebP auto-convert) | < 200KB/image |
| Fonts | `<link rel="preconnect">` + `font-display: swap` | TTFB fonts < 100ms |
| Hero LCP | `fetchpriority="high"` + `<link rel="preload">` | LCP < 1.5s |
| Below-fold | `loading="lazy"` on all `<img>` | Reduces initial payload |
| Page Cache | LiteSpeed Cache full-page HTML | TTFB < 200ms |
| Static CDN | Cloudflare caches CSS/JS/images | Edge delivery <100ms |
| DB Filter | product_filter_index SQL | < 50ms query |
| PHP | WP object cache (via LSCache) | Reduce DB calls |

### LCP Optimization — Hero Banner Preload
```php
// template-parts/hero-banner.php
// In <head> via wp_head hook:
add_action('wp_head', function() {
  if (!is_front_page()) return;
  $hero_img = get_theme_mod('hero_image_url', get_template_directory_uri() . '/assets/hero.jpg');
  echo '<link rel="preload" as="image" href="' . esc_url($hero_img) . '" fetchpriority="high">';
}, 1);
```

```html
<!-- hero-banner.php img tag -->
<img src="<?= esc_url($hero_img) ?>"
     fetchpriority="high"
     decoding="async"
     alt="Alkana Coating — Industrial Paint Solutions"
     width="1920" height="800">
```

### Responsive Images (srcset)
```php
// template-parts/product-card.php
<?php if (has_post_thumbnail()): ?>
  <?= wp_get_attachment_image(
    get_post_thumbnail_id(),
    'medium_large',
    false,
    [
      'loading' => 'lazy',
      'class'   => 'product-card__image',
      'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
    ]
  ); ?>
<?php endif; ?>
```
WordPress auto-generates `srcset` for all registered image sizes.

### Google Fonts — Non-Render-Blocking
```php
// inc/enqueue-assets.php
add_action('wp_head', function() {
  echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
  echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
  echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500&display=swap">';
}, 1);
```

### LiteSpeed Cache Configuration
Settings to configure in LSCache plugin:
```
Cache > General:
  ✅ Enable LiteSpeed Cache
  ✅ Enable Cache for Logged-in Users: NO (important: different users = different content)
  Cache TTL: 604800 (7 days for static pages)

Cache > Excludes:
  Exclude URI: /wp-admin/admin-ajax.php  (AJAX must NOT be cached)
  Exclude Cookies: wordpress_logged_in_*

Optimize:
  ✅ Minify CSS: YES (backup — Vite already minifies, but LSCache minifies WP inline styles)
  ✅ Minify JS: NO (Vite handles this — avoid double-minification conflicts)
  ✅ HTTP/2 Push: YES (for Vite CSS)
  ✅ Browser Cache: YES (max-age: 31536000 for versioned assets)

Image Optimize:
  ✅ WebP Replacement: YES (as fallback if Cloudflare Polish unavailable)
  ✅ Lazy Load Images: YES
```

### Cloudflare Settings
```
Speed > Optimization:
  ✅ Auto Minify: CSS (backup), HTML
  ✅ Brotli compression: ON
  ✅ HTTP/2: ON
  ✅ Early Hints: ON

Images:
  ✅ Polish: Lossless (requires Cloudflare Pro $20/mo — confirm with client)
  - Alternative: "Lossy" for bigger size reductions

Caching:
  Browser Cache TTL: 4 hours (HTML)
  CF Cache Status: ensure static assets MISS → HIT on 2nd request

Security:
  ✅ WAF: ON (Medium security)
  ✅ Bot Fight Mode: ON
  SSL/TLS: Full (Strict)
```

### WP Scripts Cleanup (reduce HTTP requests)
```php
// inc/enqueue-assets.php — dequeue WP defaults
add_action('wp_enqueue_scripts', function() {
  wp_dequeue_script('jquery');              // not needed (Vanilla JS only)
  wp_dequeue_script('wp-embed');
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('global-styles');
  wp_dequeue_style('classic-theme-styles');
}, 100);
```

## Implementation Steps

1. **Vite build audit:** Run `npm run build` → check outputted bundle sizes. Target: `app.css` < 30KB gzip, `app.js` < 40KB gzip.
2. **Dequeue WP scripts** — verify no jQuery or block library CSS in page source.
3. **Implement Hero LCP preload** in `wp_head` hook.
4. **Implement `srcset`** on all product card + single product images.
5. **Implement `loading="lazy"`** on all below-fold images/iframes.
6. **Install + configure LiteSpeed Cache** on staging.
7. **Configure Cloudflare** — Polish, Brotli, caching rules, WAF.
8. **Run PageSpeed on staging** with real product data. Baseline measurement.
9. **Iterate:** Address each flagged issue in PageSpeed report.
   - Third-party JS (Google Analytics, Zalo widget): defer/async.
   - CLS issues: add `width` + `height` attributes on all images.
   - Any render-blocking CSS: inline critical CSS for above-fold.
10. **AJAX filter performance test:** Run filter with all fields active on 200+ product dataset. Target < 500ms.
11. **UAT:** Alkana team reviews on real devices (mobile iOS + Android, desktop).
12. **Cross-browser testing:** Chrome, Firefox, Safari (macOS + iOS), Samsung Internet.
13. **Accessibility check:** keyboard navigation, alt text, color contrast (WCAG AA).
14. **Fix all UAT issues** before Phase 07.

## Todo List
- [ ] Vite bundle size audit (must be < 50KB gzip each)
- [ ] Dequeue all unnecessary WP default scripts/styles
- [ ] Hero LCP: fetchpriority + preload link tag
- [ ] srcset on all product images (wp_get_attachment_image)
- [ ] loading="lazy" on all below-fold images
- [ ] width + height attributes on all images (prevent CLS)
- [ ] Install + configure LiteSpeed Cache on staging
- [ ] Configure Cloudflare (Polish, Brotli, caching, WAF)
- [ ] Baseline PageSpeed measurement (mobile + desktop)
- [ ] Fix all PageSpeed flagged issues
- [ ] AJAX filter performance test with 200+ products
- [ ] Defer/async third-party JS (Analytics, Zalo widget)
- [ ] UAT session with Alkana team (mobile devices)
- [ ] Cross-browser testing (Chrome, Firefox, Safari iOS, Samsung)
- [ ] Accessibility check (keyboard nav, alt text, contrast)
- [ ] All UAT issues resolved
- [ ] Final PageSpeed: mobile ≥ 85 confirmed

## Success Criteria
- PageSpeed Mobile ≥ 85 (hard gate — do not deploy before reaching this)
- PageSpeed Desktop ≥ 95
- LCP < 2.5s on mobile (3G throttled)
- AJAX filter returns results in < 500ms
- CLS < 0.1 (no layout shifts)
- Zero Screaming Frog errors on staging
- Alkana team sign-off on UAT

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| Cloudflare Polish requires Pro plan | Medium | Confirm billing with client before Phase 06 begins |
| PageSpeed stuck at 75–80 despite optimizations | Low | Inline critical CSS for above-fold (< 14KB inline) |
| AJAX filter slow with 500+ products | Low | Verify index table query plan with EXPLAIN; add composite index |
| LiteSpeed Cache interferes with AJAX filter | Medium | Explicitly exclude `/wp-admin/admin-ajax.php` from cache |

## Security Considerations
- LiteSpeed Cache: never cache pages for logged-in users (different RBAC content)
- Cloudflare WAF set to medium — monitor for false positives on contact form submissions

## Next Steps
→ Phase 07: Deployment to production (only after PageSpeed ≥ 85 confirmed)
