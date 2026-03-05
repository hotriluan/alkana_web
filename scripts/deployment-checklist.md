# Alkana Coating Website — Deployment Checklist

> **Go-live target:** End of Week 8  
> **Deploy window:** Monday–Tuesday only (not Friday — need 3 business days to monitor)  
> **Staging URL:** `https://staging.alkana.vn`  
> **Production URL:** `https://alkana.vn`

---

## Pre-Deployment Gate Criteria (ALL must be ✅ before proceeding)

- [ ] PageSpeed Mobile score ≥ 85
- [ ] PageSpeed Desktop score ≥ 95
- [ ] All 301 redirects verified by Screaming Frog (zero old URLs returning 404)
- [ ] Alkana team UAT sign-off received
- [ ] Contact form tested → email received in inbox (not spam)
- [ ] SSL certificate issued and active on production server
- [ ] `WP_DEBUG` set to `false` on production `wp-config.php`
- [ ] Production `robots.txt` in place (allow all, no `Disallow: /`)
- [ ] Admin user accounts created for Alkana team (alkana_content_editor role)
- [ ] Handover guide reviewed with Alkana team (`docs/handover-guide.md`)
- [ ] `DISALLOW_FILE_EDIT` and `DISALLOW_FILE_MODS` set to `true` in `wp-config.php`

---

## T-24 Hours (Day Before Go-Live)

- [ ] **Lower DNS TTL** to 300 seconds on Cloudflare DNS records
- [ ] **Final Screaming Frog crawl** on `https://staging.alkana.vn`:
  - Zero 4xx errors
  - Zero 5xx errors
  - All 301s resolve to correct target URLs
- [ ] **Final content review** with Alkana team — any last product edits done on staging
- [ ] **Notify team** of go-live window (time + monitoring roster)

---

## T-4 Hours (Database Migration)

```bash
# 1. Export staging database
mysqldump -u stagingUser -p stagingDB > alkana-staging-$(date +%Y%m%d).sql

# 2. Import to production
mysql -u prodUser -p prodDB < alkana-staging-$(date +%Y%m%d).sql

# 3. Search-replace staging URL → production URL (dry run first)
wp search-replace 'https://staging.alkana.vn' 'https://alkana.vn' --dry-run
wp search-replace 'https://staging.alkana.vn' 'https://alkana.vn'

# 4. Flush rewrite rules
wp rewrite flush
```

- [ ] Database imported successfully
- [ ] URL search-replace completed (no staging URLs remaining)
- [ ] WP Settings → General: Site URL = `https://alkana.vn`

---

## T-2 Hours (Theme Files Sync)

```bash
# Sync compiled theme to production (DO NOT sync node_modules)
rsync -avz --exclude='node_modules/' --exclude='.git/' \
  wp-content/themes/alkana/ \
  prodUser@matbao-server:/public_html/wp-content/themes/alkana/

# Sync compiled Vite assets (already excluded above — resync dist/ explicitly)
rsync -avz dist/ \
  prodUser@matbao-server:/public_html/wp-content/themes/alkana/dist/
```

- [ ] Theme files synced
- [ ] `dist/` (compiled CSS/JS) synced
- [ ] ACF JSON files synced (`acf-json/`)
- [ ] `.htaccess` with 301 redirects verified on production

---

## T-1 Hour (Pre-DNS Production Test)

Test production server WITHOUT changing DNS:

1. Edit local `hosts` file: add `[PRODUCTION-IP]  alkana.vn`
2. Visit `https://alkana.vn` in browser
3. Run through checklist:

- [ ] Homepage loads correctly
- [ ] Product archive + filter functional
- [ ] 3 random product detail pages load
- [ ] Contact form submits → email received
- [ ] Mobile layout renders correctly
- [ ] No PHP errors in WP Admin → Tools → Site Health → Info → Server

Remove `hosts` file entry after testing.

---

## T=0 (DNS Cutover)

- [ ] **Switch Cloudflare DNS A record** → production IP (orange cloud = proxy ON)
- [ ] **Switch CNAME `www`** → `alkana.vn` (or A record, per current config)
- [ ] Enable **Cloudflare proxy** (orange cloud) on both A and CNAME records

---

## T+5 Minutes (DNS Propagation)

- [ ] Verify DNS at `https://dnschecker.org/#A/alkana.vn` — majority of nodes showing production IP
- [ ] `nslookup alkana.vn` from local terminal returns production IP

---

## T+10 Minutes (SSL + HTTPS)

- [ ] `https://alkana.vn` loads without SSL error
- [ ] SSL Labs grade A+ at `https://www.ssllabs.com/ssltest/analyze.html?d=alkana.vn`
- [ ] HTTP → HTTPS redirect working (`http://alkana.vn` → `https://alkana.vn`)

---

## T+30 Minutes (Go-Live Verify)

- [ ] Homepage loads — hero banner, featured products visible
- [ ] Product archive — filter returns results
- [ ] 10 random old Vietnamese-slug URLs return 301 → correct new URL
- [ ] About, Contact, Projects pages render
- [ ] Contact form test submission → email received
- [ ] Mobile layout on real iOS device
- [ ] Mobile layout on real Android device
- [ ] Cloudflare Analytics: no spike in 4xx errors
- [ ] PageSpeed run on live `https://alkana.vn` — mobile ≥ 85 confirmed
- [ ] LiteSpeed Cache: `X-LiteSpeed-Cache: hit` on second page load (check response headers)

```bash
# Quick header check:
curl -sI https://alkana.vn | grep -i 'litespeed\|cache\|cf-cache'
```

---

## T+1 Hour (Search Console)

- [ ] Submit XML sitemap: `https://alkana.vn/sitemap.xml` in Google Search Console
- [ ] Confirm sitemap URL resolves (open in browser — should show XML)

---

## T+24 Hours (Day 1 Monitor)

- [ ] Homepage mobile load < 2s on real device (typical 4G)
- [ ] 5 random products render correctly
- [ ] LiteSpeed cache hit ratio visible in LSCache Dashboard
- [ ] Cloudflare: check WAF events — any false positives on contact form?
- [ ] cPanel Error Log: no new PHP errors

---

## T+3 Days

- [ ] Google Search Console → Coverage: sitemap indexed, no critical errors
- [ ] PHP error log: zero new errors
- [ ] LiteSpeed cache hit ratio > 80%
- [ ] 10 old URLs: all return 301 (not 404)
- [ ] Product filter with all facets active: response < 500ms

---

## T+7 Days (Week 2)

- [ ] GSC Coverage: pages indexed (no unexpected noindex)
- [ ] Core Web Vitals in GSC: LCP, INP, CLS targets met
- [ ] Alkana team can independently add a product (verify with team)
- [ ] Staging server: update `robots.txt` → `Disallow: /` (block all crawlers)

---

## Rollback Plan

If critical issues discovered post-launch:

1. Revert Cloudflare DNS A record to staging IP → traffic returns to staging (< 5 min)
2. Diagnose issue on production without user traffic
3. Re-cut DNS after fix

> Staging stays live for **2 weeks post-launch** as rollback target.

---

## Post-Launch Contacts

| Issue Type | Action |
|-----------|--------|
| WordPress/theme bug | Dev team |
| Server downtime | Mat Bao: 1800 6663 |
| DNS/CDN issue | Cloudflare dashboard |
| Email delivery | Check SMTP plugin logs |
