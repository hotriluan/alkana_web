# LiteSpeed Cache — Alkana Configuration Guide

> Reference for developer/sysadmin configuring LSCache on Mat Bao shared hosting.
> WP Admin path: `wp-admin/admin.php?page=litespeed-cache`

---

## 1. Cache > General

| Setting | Value | Notes |
|---------|-------|-------|
| Enable LiteSpeed Cache | ✅ ON | Master switch |
| Cache for Logged-in Users | ❌ OFF | Alkana roles see different admin bars; never cache these |
| Cache Commenters | ❌ OFF | N/A |
| Cache REST API | ❌ OFF | AJAX filter hits admin-ajax.php, not REST |
| Cache Login Page | ❌ OFF | |
| Default Public Cache TTL | `604800` (7 days) | Static pages; LSCache auto-purges on post save |
| Default Private Cache TTL | `1800` | |
| Default Front Page TTL | `86400` (1 day) | |

---

## 2. Cache > Excludes

| Type | Value | Reason |
|------|-------|--------|
| Exclude URI | `/wp-admin/admin-ajax.php` | AJAX filter must NOT be served from cache |
| Exclude URI | `/wp-login.php` | |
| Exclude Cookies | `wordpress_logged_in_*` | Logged-in users bypass cache |
| Exclude Cookies | `wordpress_test_cookie` | |
| Exclude Query String | `s` | WP search results must not be cached |

---

## 3. Optimize > CSS

| Setting | Value | Notes |
|---------|-------|-------|
| Minify CSS | ✅ ON | Minifies WP inline styles (Vite already minifies its own bundle) |
| Combine CSS | ❌ OFF | Vite already outputs a single bundle — combining adds no benefit and risks breakage |
| CSS Inline Small CSS | ❌ OFF | |
| Load CSS Asynchronously | ❌ OFF | Alkana CSS is critical; async loading causes FOUC |

---

## 4. Optimize > JS

| Setting | Value | Notes |
|---------|-------|-------|
| Minify JS | ❌ OFF | Vite already minifies — double-minification can break ES modules |
| Combine JS | ❌ OFF | Vite produces ES module bundles — combining breaks `import/export` |
| Load JS Deferred | ❌ OFF | Vite's `type="module"` scripts are deferred by browser spec |
| Inline JS Delay | ❌ OFF | |

---

## 5. Optimize > Media

| Setting | Value | Notes |
|---------|-------|-------|
| Lazy Load Images | ✅ ON | LSCache adds `loading="lazy"` as a fallback safety net |
| Lazy Load Iframes | ✅ ON | |
| Add Missing Image Dimensions | ✅ ON | Helps CLS; adds width/height if missing in HTML |
| Responsive Image Placeholders | ✅ ON | |
| LQIP (Low-Quality Image Placeholder) | Optional | Improves perceived LCP; minor extra request |

---

## 6. Image Optimization (QUIC.cloud)

Path: `wp-admin/admin.php?page=litespeed-img_optm`

### Step-by-step Setup

1. **Generate Domain Key**
   - Go to `LSCache > General > QUIC.cloud Domain Key`
   - Click "Get Domain key" → log in / register free QUIC.cloud account
   - Key auto-populates

2. **Enable Image Optimization**

| Setting | Value |
|---------|-------|
| Auto Request Cron | ✅ ON — sends newly-uploaded images to QUIC.cloud in background |
| Create WebP Versions | ✅ ON |
| Image WebP Replacement | ✅ ON — replaces .jpg/.png with .webp in HTML for WebP-capable browsers |
| Optimize Original Images | ✅ ON — lossless compression on originals |
| Lazy Load Images | ✅ ON (already in Media tab — set once) |

3. **First-time batch optimisation**
   - Go to `Image Optimization > Image Optimization Status`
   - Click "Send Optimization Request" to queue ALL existing images
   - QUIC.cloud processes in the background; check back after 10–30 min

4. **Verify**
   - Upload a test product image
   - After a few minutes, inspect with DevTools Network: look for `image.webp` response
   - Check `LSCache > Image Optimization > Summary` for success count

> **Free tier note:** QUIC.cloud free tier includes image optimization credits that reset monthly.
> Monitor usage if product catalog exceeds 500 images. If credits exhausted, originals serve as fallback.

---

## 7. CDN / Cloudflare Integration

| Setting | Value | Notes |
|---------|-------|-------|
| Use CDN | ✅ ON | |
| CDN URL | `https://alkana.vn` | Same domain (Cloudflare proxies it) |
| HTTP/2 Push | ✅ ON | Pushes Vite CSS to browser |
| Browser Cache TTL | `31536000` (1 year) | For versioned assets — fine because Vite adds content hash to filenames |

---

## 8. Cloudflare Free Tier (separate — Cloudflare dashboard)

| Setting | Value |
|---------|-------|
| Polish | ❌ OFF — Free tier; WebP handled by QUIC.cloud |
| Brotli | ✅ ON |
| Auto Minify HTML | ✅ ON |
| Auto Minify CSS | ✅ ON (backup — Vite already minifies) |
| Auto Minify JS | ❌ OFF — can break ES modules |
| HTTP/2 | ✅ ON |
| Early Hints | ✅ ON |
| Browser Cache TTL | 4 hours for HTML |
| WAF | ✅ Medium security |
| Bot Fight Mode | ✅ ON |
| SSL/TLS Mode | Full (Strict) |

---

## 9. Cache Purge After Content Update

LiteSpeed auto-purges when a post is saved in WP Admin.
For manual purge:

```
WP Admin > LiteSpeed Cache > Toolbox > Purge > Purge All
```

Or via WP CLI:
```bash
wp litespeed-purge all
```

---

## 10. Verify Cache is Working

1. Load homepage in browser
2. Open DevTools > Network > select the HTML document
3. Response headers should contain: `X-LiteSpeed-Cache: hit`
4. First load = `miss`, second load = `hit` (expected behaviour)

---

## Known Gotcha: AJAX Filter

The product filter at `/san-pham/` (archive-alkana_product.php) posts to `admin-ajax.php`.
LSCache **must not** cache this endpoint. Verify with:

```bash
curl -I -X POST https://alkana.vn/wp-admin/admin-ajax.php \
  -d "action=alkana_filter_products" | grep -i litespeed
```

Expected: no `X-LiteSpeed-Cache` header (or `X-LiteSpeed-Cache: no-cache`).
