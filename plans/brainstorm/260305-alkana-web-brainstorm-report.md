# Alkana Coating Website — Brainstorm & Architecture Review
**Date:** 2026-03-05
**Role:** Solution Brainstormer (SKILL: brainstorm)
**Source:** `Alkana_Project_Blueprint_EN.md`
**Principles:** YAGNI · KISS · DRY

---

## 1. Problem Statement & Objectives

| Item | Detail |
|---|---|
| **Project** | Rebuild B2B & B2C Website Catalog for Alkana Coating |
| **Current Pain** | Outdated UI, slow load, poor navigation |
| **Goal** | Industrial Modern UI, fast faceted search, structured product data |
| **Scope** | No checkout, no paint estimator — pure catalog |
| **Constraint** | Mat Bao Shared/Cloud Hosting (cPanel), WordPress PHP, 8-week deadline |

---

## 2. Critical Risk Assessment 🚨

### Risk 1 — Faceted AJAX Filter vs. Shared Hosting (SEVERITY: HIGH)
**Blueprint plan:** Multi-layer AJAX filter with OR/AND logic across taxonomies + meta fields using WP_Query.

**Problem:**
- `WP_Query` with multiple `meta_query` + `tax_query` joins is one of WordPress's most notorious performance bottlenecks.
- On shared hosting, PHP process limits + MySQL query timeouts will easily break the `< 2s` target as the catalog grows.
- Every AJAX call re-runs a complex JOIN query on postmeta table (EAV structure — inherently slow).

**Recommendation:**
> ✅ **Build a dedicated `product_filter_index` summary table in MySQL.**
> Denormalize product attributes (surface, category, gloss, paint system) into one flat table with proper composite indexes.
> Filter queries become simple `SELECT` with `WHERE IN` — 10-50x faster than postmeta JOINs.

---

### Risk 2 — 8-Week Timeline (SEVERITY: HIGH)
**Blueprint plan:** 8 sprints covering schema, Figma, frontend, AJAX, data migration, SEO, and deployment.

**Reality check:**
| Phase | Estimated Effort | Blueprint Allocation |
|---|---|---|
| Schema design + ACF setup | 3–5 days | Sprint 1 (2 weeks shared) |
| Figma UI design | 5–7 days | Sprint 1 |
| Custom theme (Tailwind) | 8–12 days | Sprint 2 |
| AJAX faceted filter | 5–8 days | Sprint 2 |
| Admin UI + RBAC | 3–4 days | Sprint 2 |
| Data scraping + cleansing | 5–10 days | Sprint 3 |
| 301 Redirect mapping | 2–3 days | Sprint 3 |
| Performance pipeline | 3–5 days | Sprint 4 |
| UAT + fixes | 5–7 days | Sprint 4 |
| Deployment + handover | 2–3 days | Sprint 5 |
| **Total realistic** | **41–64 days** | **~40 working days (8 weeks)** |

**Verdict:** 8 weeks is **achievable ONLY IF**:
- Alkana team provides clean data by Week 2.
- Figma design is approved in 1 iteration (no redesign loops).
- Product catalog has < 300 SKUs at launch.

**Recommendation:**
> ✅ Plan for a **Phased Launch**: Core catalog + filter live at Week 8. "Resources Center" (TDS/MSDS), Careers, and Blog can launch in Week 10.

---

### Risk 3 — Tailwind CSS Build Pipeline on Shared Hosting (SEVERITY: MEDIUM)
**Problem:** Tailwind requires Node.js + npm for JIT compilation. Shared hosting cPanel does NOT have Node.js in the web root runtime.

**Recommendation:**
> ✅ **Develop locally with Vite + Tailwind** (not `npm run watch` on server).
> CI/CD approach: Build CSS/JS bundle locally → commit compiled assets → deploy via FTP/SFTP or rsync.
> Use `vite build` output: one `app.css` + one `app.js` = zero-bloat, zero runtime dependencies on server.

---

### Risk 4 — WebP/AVIF Auto-Conversion (SEVERITY: MEDIUM)
**Problem:** AVIF requires ImageMagick with `libavif` compiled in. Most shared hosting environments (including Mat Bao standard) do NOT support AVIF server-side encoding.

**Recommendation:**
> ✅ **Target WebP only** for automated conversion (WordPress 5.8+ native support).
> Verify Mat Bao ImageMagick supports WebP — if not, use **Cloudflare Images polish** (automatic WebP/AVIF at CDN edge, no server-side processing needed) — this is actually superior.

---

### Risk 5 — Data Scraping Dependency (SEVERITY: MEDIUM)
**Problem:** "Write automated scraping script" is listed in Sprint 3 (Week 5) but the Alkana team must review/cleanse before import. This creates a hard blocker: scrape → cleanse → import cannot run in under 1 week.

**Recommendation:**
> ✅ **Start scraping in Sprint 1 (parallel to DB schema design).**
> Deliver raw Excel to Alkana team by end of Week 2 so cleansing can happen during Weeks 3–4 while frontend is being coded.

---

## 3. Architecture Proposals

### Option A — Blueprint As-Is (WordPress Monolith)
```
Mat Bao cPanel
└── WordPress (PHP)
    ├── Custom Theme (Tailwind + Vanilla JS)
    ├── CPT + ACF (product data layer)
    ├── WP REST API / admin-ajax.php (AJAX filter)
    └── MySQL (standard WP schema + product_filter_index table)
Cloudflare CDN + WAF
LiteSpeed Page Cache
```
**Pros:** Simpler stack, client team familiar with WP admin, single hosting contract.
**Cons:** AJAX filter performance risk, WP overhead on every request (~800ms baseline).

---

### Option B — WordPress Headless + Static Frontend *(NOT recommended for this project)*
```
Hosting A: WordPress (WP REST API only — no frontend)
Hosting B: Next.js / Nuxt (Static Site Generation)
Cloudflare CDN
```
**Pros:** Blazing fast, modern DX.
**Cons:** **Violates shared hosting constraint**, requires 2 servers, beyond client's operational capacity.
> ❌ **Eliminated** — incompatible with Mat Bao cPanel and client's tech team capability.

---

### Option C — WordPress + Optimized Query Layer ✅ RECOMMENDED
```
Mat Bao cPanel
└── WordPress (PHP)
    ├── Custom Theme (Vite + Tailwind CSS + Vanilla JS)
    ├── CPT + ACF (admin data input layer)
    ├── product_filter_index table (denormalized, indexed — fast queries)
    ├── Custom AJAX endpoint → direct MySQLi/PDO query (bypass WP_Query)
    └── MySQL 8.0 (WP tables + supplementary tables)
Cloudflare CDN (Image Polish: WebP auto-conversion)
LiteSpeed Cache (full-page + object cache)
```
**Why Option C wins:**
- Keeps WordPress as CMS (client-friendly admin).
- Bypasses WP_Query bottleneck for filter — direct optimized SQL.
- Vite build pipeline resolves the Tailwind/shared-hosting conflict.
- Cloudflare handles WebP/AVIF without server-side ImageMagick dependency.

---

## 4. Specific Technical Recommendations

### 4.1 Database — Product Filter Index Table
```sql
CREATE TABLE wp_alkana_product_index (
  product_id    BIGINT PRIMARY KEY,
  category_slug VARCHAR(100),
  surface_types JSON,          -- ["wood","steel","concrete"]
  paint_system  VARCHAR(50),
  gloss_level   VARCHAR(30),
  is_featured   TINYINT(1) DEFAULT 0,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (category_slug),
  INDEX idx_gloss (gloss_level),
  INDEX idx_paint_system (paint_system)
);
```
ACF save hook → syncs data to this table on every product save. AJAX filter queries this table directly.

---

### 4.2 AJAX Filter Architecture
```
Client (Bottom Sheet / Sidebar)
  └─ fetch('/wp-admin/admin-ajax.php?action=alkana_filter', { method: POST, body: filters })
       └─ PHP: alkana_filter_handler()
            ├─ Sanitize & validate input
            ├─ Build direct SQL from wp_alkana_product_index
            ├─ Return product IDs + counts
            └─ WP get_posts(ids) → render partial template → return HTML
```
**Key:** Only query postmeta ONCE (for rendering HTML), not for filtering. Filtering is index-only.

---

### 4.3 Vite Build Pipeline (Local Dev → cPanel Deploy)
```
local/
├── src/
│   ├── styles/ (Tailwind CSS)
│   └── scripts/ (Vanilla JS modules)
├── vite.config.js
└── dist/                      ← deploy this
    ├── app.[hash].css
    └── app.[hash].js
```
```bash
# Local build
npm run build                  # vite build → dist/

# Deploy to cPanel
rsync -avz dist/ user@matbao:/public_html/wp-content/themes/alkana/assets/
```
WordPress `functions.php` enqueues the hashed filenames from `dist/manifest.json`.

---

### 4.4 Performance Stack (Achieving < 2s)

| Layer | Tool | Target |
|---|---|---|
| CSS/JS | Vite bundle (minified, single file each) | < 50KB gzip |
| Images | Cloudflare Image Polish (WebP auto) | < 200KB/image |
| Fonts | Google Fonts preconnect + `font-display: swap` | No FOIT |
| Cache | LiteSpeed Full Page Cache (HTML) | TTFB < 200ms |
| CDN | Cloudflare (static assets) | Global < 100ms |
| DB | product_filter_index (indexed) | Filter query < 50ms |
| Lazy load | Native `loading="lazy"` on all below-fold images | LCP < 1.5s |

---

### 4.5 SEO Protection — 301 Redirect Strategy
Do NOT rely on WordPress redirect plugins (performance overhead).

**Recommendation:**
> ✅ Write 301 rules directly into `.htaccess` (Apache) or LiteSpeed config.
> Map old URL patterns → new URL patterns using regex where possible to reduce rule count.

```apache
# Example: old product URL → new
RewriteRule ^san-pham/([^/]+)/([^/]+)/?$ /products/$2/ [R=301,L]
```

---

## 5. Revised Sprint Plan (Optimized)

| Sprint | Weeks | Key Deliverables | Risk Mitigation |
|---|---|---|---|
| Sprint 0 | Pre-week 1 | Mat Bao hosting setup, local dev environment, Git repo, Vite pipeline | Eliminates wasted sprint 1 time |
| Sprint 1 | Weeks 1–2 | CPT/ACF schema, Figma wireframes, **START data scraping** | Parallel track unblocks migration |
| Sprint 2 | Weeks 3–4 | Custom theme (Tailwind), product_filter_index table, AJAX filter MVP | Core functionality first |
| Sprint 3 | Weeks 4–5 | Admin UI + RBAC, data import (from Alkana-cleansed Excel), 301 map | Alkana delivers cleansed data week 3 |
| Sprint 4 | Weeks 6–7 | Performance tuning, PageSpeed target, UAT, error handling | Buffer for Alkana feedback |
| Sprint 5 | Week 8 | Production deploy, Cloudflare DNS, SSL, robots.txt, GSC submit | Hard deadline |
| Post-launch | Weeks 9–10 | Blog, Resources Center, Careers pages | Phased launch reduces week 8 pressure |

---

## 6. Key Decisions Required from Alkana Team

| # | Question | Impact |
|---|---|---|
| 1 | How many product SKUs at launch? (< 300 / 300–1000 / > 1000) | Database indexing strategy |
| 2 | Is the old website accessible for scraping? (public / behind login) | Sprint 1 data migration timeline |
| 3 | Does the Alkana team have an approved Figma design or is design from scratch? | Sprint 1 scope |
| 4 | Who is the primary Content Editor on Alkana side? (WordPress training needed?) | RBAC + admin UI complexity |
| 5 | Mat Bao plan tier: does it include Redis/Memcached? | Object cache strategy |

---

## 7. Success Metrics & Validation

| Metric | Target | Validation Tool |
|---|---|---|
| PageSpeed Score (Mobile) | ≥ 85 | Google PageSpeed Insights |
| LCP | < 2.5s | Core Web Vitals (GSC) |
| Filter response time | < 500ms | Browser DevTools Network tab |
| 404 rate post-launch | 0% | Screaming Frog + GSC Coverage |
| Admin data entry: product add time | < 5 min/product | Internal UAT |
| Mobile usability | 0 issues | Google Search Console |

---

## 8. Final Recommendation

> **Proceed with Option C (WordPress + Optimized Query Layer).**
> The blueprint is 85% sound. The 3 changes that will make or break success:
>
> 1. Build `wp_alkana_product_index` table — non-negotiable for filter performance on shared hosting.
> 2. Use Vite for the build pipeline — solves Tailwind on cPanel, improves DX, cleaner output.
> 3. Start data scraping in Sprint 1 (parallel, not Sprint 3) — eliminates the biggest timeline blocker.

---

## 9. Unresolved Questions

1. Will Mat Bao's MySQL version be 8.0 (JSON column support) or 5.7? (affects schema option)
2. Is there a budget for ACF Pro? (Repeater fields for variants/colors require Pro)
3. Is Cloudflare on a free or paid plan? (Image Polish requires Pro tier)
4. What is the current old website URL? (needed to assess scraping complexity)

---

*Report by: GitHub Copilot (Solution Brainstormer Skill)*
*Next step: Run `/ck:plan` with this report as context to create detailed implementation plan.*
