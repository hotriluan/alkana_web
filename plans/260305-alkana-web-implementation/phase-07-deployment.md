# Phase 07 — Deployment & Handover

## Context Links
- Plan: [plan.md](plan.md)
- Blueprint §10 Deployment Roadmap Sprint 5

## Overview
| Item | Detail |
|---|---|
| **Priority** | Critical |
| **Status** | ⏳ pending |
| **Timeline** | Week 8 |
| **Depends on** | ALL previous phases complete, PageSpeed ≥ 85 (Phase 06 gate) |
| **Blocks** | Nothing — this is the final phase |

## Key Insights
- Hard go-live target: end of Week 8.
- Do NOT go live on Friday — deploy Monday/Tuesday to allow 3 business days to monitor and hotfix.
- DNS TTL must be lowered to 300s (5 min) at least 24h BEFORE cutover so DNS propagation is fast.
- Staging stays live for 2 weeks post-launch as rollback target.
- Blog, Resources Center, Careers are Phase 2 (Weeks 9–10) — do NOT hold Week 8 launch for these.

## Requirements

### Pre-Deployment Checklist (must all be ✅)
- [ ] PageSpeed Mobile ≥ 85
- [ ] All 301 redirects verified by Screaming Frog
- [ ] Alkana team UAT sign-off received
- [ ] SSL certificate issued on production domain
- [ ] Cloudflare DNS records configured (A record + CNAME www)
- [ ] `wp-config.php` production values set (DB host, table prefix, salts)
- [ ] Debug mode OFF (`WP_DEBUG = false`)
- [ ] LiteSpeed Cache warmup ready
- [ ] `robots.txt` production version ready (allow all)
- [ ] Contact form emails verified (test form submit → email received)
- [ ] Admin users created for Alkana team
- [ ] Handover documentation written

### Functional
- All content live on production URL with SSL.
- Cloudflare proxy active (orange cloud on DNS records).
- LiteSpeed Page Cache active.
- Production `robots.txt` in place.
- XML sitemap submitted to Google Search Console.
- All team credentials documented and handed over.

## Architecture — Deployment Flow

```
[Staging: staging.alkana.vn]         [Production: alkana.vn on Mat Bao]

1. PREP (Day before go-live)
   ├─ Lower DNS TTL to 300 seconds
   ├─ Final data sync: staging DB → production DB
   ├─ rsync theme files: staging → production
   └─ Verify .htaccess 301 rules in production

2. GO-LIVE (T=0)
   ├─ Update Cloudflare DNS A record → Mat Bao production IP
   ├─ Activate SSL on production (let's encrypt or Mat Bao SSL)
   ├─ Update WordPress Site URL in WP Settings → General
   ├─ Activate Cloudflare proxy (orange cloud)
   ├─ Warm LiteSpeed Cache (run cache preload)
   └─ Update robots.txt: staging = disallow all; production = allow all

3. VERIFY (first 30 minutes)
   ├─ Test 10 random old URLs → verify 301 redirect
   ├─ Test homepage, product page, contact form
   ├─ Test mobile layout on real devices
   ├─ Check Cloudflare analytics (no spike in 4xx errors)
   ├─ Run PageSpeed on live production URL
   └─ Submit sitemap to Google Search Console

4. MONITOR (Days 1–7)
   ├─ GSC: Coverage report for any new 404s
   ├─ Server error log: watch for PHP errors
   ├─ LiteSpeed: cache hit ratio should be > 80% after 24h
   └─ Cloudflare: WAF blocking any false positives on forms?
```

### Database Migration (Staging → Production)
```bash
# 1. Export staging DB
mysqldump -u stagingUser -p stagingDB > alkana-staging-$(date +%Y%m%d).sql

# 2. Search-replace staging URL → production URL
wp search-replace 'https://staging.alkana.vn' 'https://alkana.vn' --dry-run
wp search-replace 'https://staging.alkana.vn' 'https://alkana.vn'

# 3. Import to production
mysql -u prodUser -p prodDB < alkana-staging-20260308.sql
```

### Theme File Sync
```bash
# rsync theme from staging to production (on cPanel server-to-server or local)
rsync -avz --exclude='dist/' --exclude='node_modules/' \
  wp-content/themes/alkana/ \
  prodUser@matbao:/public_html/wp-content/themes/alkana/

# Then on production, run Vite build (NO — build locally and sync compiled dist/)
rsync -avz dist/ prodUser@matbao:/public_html/wp-content/themes/alkana/dist/
```

### WordPress Production wp-config.php
```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Security keys: generate at https://api.wordpress.org/secret-key/1.1/salt/
define('AUTH_KEY',         'unique-key-here');
// ... (all 8 salts)

define('DISALLOW_FILE_EDIT', true);  // disable theme/plugin editor in admin
define('DISALLOW_FILE_MODS', true);  // disable plugin/theme install from admin
```

### .htaccess — Production Security Headers
```apache
# Security headers
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
</IfModule>

# Protect wp-config.php
<Files wp-config.php>
  Order Allow,Deny
  Deny from all
</Files>

# Protect .htaccess
<Files .htaccess>
  Order Allow,Deny
  Deny from all
</Files>
```

### Post-Launch Monitoring Checklist (Week 8 + Week 9)
```
Day 1:
  ✅ Homepage loads < 2s on mobile (real device)
  ✅ 5 random products render correctly
  ✅ Contact form email received
  ✅ Cloudflare shows cache HIT on 2nd page load

Day 3:
  ✅ Google Search Console: Sitemap submitted, no coverage errors
  ✅ Server PHP error log: zero new errors
  ✅ LiteSpeed cache hit ratio > 80%
  ✅ 10 old URLs return 301 (not 404)

Week 2:
  ✅ GSC Coverage: all pages indexed (no unexpected noindex)
  ✅ Core Web Vitals in GSC: LCP, FID/INP, CLS targets met
  ✅ Alkana team can log in and add a product independently
```

## Handover Documentation Contents
Write `docs/handover-guide.md` with:
1. WordPress admin login URL + credentials (or instructions for password reset)
2. How to add a new product (step-by-step with screenshots)
3. How to add a new project to portfolio
4. How to update homepage banners
5. How to add/edit taxonomy terms (product categories, surfaces)
6. Mat Bao cPanel login + FTP credentials (separate secure channel)
7. Cloudflare dashboard access
8. Google Search Console access
9. LiteSpeed Cache: how to clear cache when content updated
10. Emergency contacts: developers + Mat Bao support

## Related Code Files
- Create: `docs/handover-guide.md`
- Modify: `.htaccess` (add security headers + 301 rules)
- Modify: `wp-config.php` (WP_DEBUG off, salts updated)
- Modify: `robots.txt` (production version)

## Implementation Steps

1. **T-24h: Lower DNS TTL** to 300 seconds on Cloudflare.
2. **T-24h: Final staging audit** — run full Screaming Frog crawl on staging.
3. **T-4h: Production DB restore** — import staging DB into production, run search-replace.
4. **T-2h: Sync theme files** to production (including compiled `dist/`).
5. **T-1h: Test production** without changing DNS:
   - Edit local `hosts` file to point `alkana.vn` to production IP.
   - Verify all pages, forms, filter work on production server.
6. **T=0: Switch DNS** in Cloudflare → production IP. Enable orange cloud.
7. **T+5min: Verify DNS propagation** (dnschecker.org).
8. **T+10min: SSL check** — `https://alkana.vn` loads without SSL error.
9. **T+30min: Run Go-Live Verify checklist** (see above).
10. **T+1h: Submit XML sitemap** to Google Search Console.
11. **T+24h: Monitor** error logs, cache hit ratio, GSC.
12. **T+3d: Post-launch check** (see day 3 checklist above).
13. **Week 9: Handover session** with Alkana team — walkthrough admin UI, content editing, cache clearing.
14. **Week 9–10: Phase 2 launch** — Blog, Resources Center (TDS/MSDS), Careers pages.

## Todo List
- [ ] T-24h: Lower DNS TTL to 300s
- [ ] Final Screaming Frog crawl on staging
- [ ] Staging DB export + search-replace
- [ ] Import DB to production
- [ ] rsync theme + dist/ to production
- [ ] Test production via local hosts file edit
- [ ] Switch DNS in Cloudflare
- [ ] Verify DNS propagation
- [ ] SSL certificate active
- [ ] Check all 301 redirects on production
- [ ] Contact form test (email received)
- [ ] Submit sitemap to Google Search Console
- [ ] LiteSpeed Cache preload on production
- [ ] Monitor Day 1 checklist
- [ ] Monitor Day 3 checklist
- [ ] Write handover-guide.md
- [ ] Handover session with Alkana team
- [ ] Phase 2: Blog + Resources Center (Weeks 9–10)

## Success Criteria
- `https://alkana.vn` loads in < 2s on mobile (real device, typical 4G)
- Zero 404 errors from any old URL
- SSL A+ rating on SSL Labs
- Alkana team can independently add a product after handover session
- Google Search Console: sitemap submitted, no critical coverage errors
- LiteSpeed cache hit ratio > 80% after 24h

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| DNS propagation slow (TTL was high) | Low | TTL lowered 24h before cutover |
| Contact form stops working on production | Medium | Test before DNS switch using hosts file trick |
| LiteSpeed Cache serves stale data after import | Low | Purge cache immediately after DB import |
| Production DB credentials wrong | Low | Test DB connection via cPanel phpMyAdmin before migration |
| Google deindexes old URLs before 301s are in place | Low | 301s deployed with initial launch — no downtime gap |

## Security Considerations
- `DISALLOW_FILE_EDIT` + `DISALLOW_FILE_MODS` in `wp-config.php` prevents code injection via admin
- Security headers prevent XSS, clickjacking
- WAF on Cloudflare: medium security rule set
- No database credentials committed to git at any point

## Post-Launch (Phase 2 scope — Weeks 9–10)
- Blog / News section with full article templates
- Resources Center: TDS/MSDS download hub with search by product
- Careers: job listing CPT + CV upload form
- Phase 2 deploy follows same deployment process (no DNS change needed)
