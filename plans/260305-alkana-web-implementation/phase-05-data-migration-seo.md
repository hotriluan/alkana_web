# Phase 05 — Data Migration & SEO Protection

## Context Links
- Plan: [plan.md](plan.md)
- Brainstorm risk §5 (scraping timeline), §4.5 (301 redirects)
- Blueprint §9 Data Migration & SEO Protection

## Overview
| Item | Detail |
|---|---|
| **Priority** | Critical |
| **Status** | ⏳ pending |
| **Timeline** | Weeks 1–5 (parallel track — start scraping Week 1) |
| **Depends on** | Phase 01 (DB schema defined — needed for Excel template design) |
| **Blocks** | Phase 06 UAT needs real data; Phase 07 needs 301 map |

## Key Insights
- **CRITICAL:** Scraping starts Week 1 in parallel with Phase 01, NOT Sprint 3 (Week 5) as in original blueprint.
- Scraping → Raw Excel delivered to Alkana team by end of Week 2 → Alkana cleanse Weeks 3–4 → Import Week 5.
- 301 redirects go into `.htaccess` directly — NOT a plugin (performance overhead).
- URL Mapping must be audited by Screaming Frog before go-live.
- `robots.txt` must BLOCK staging subdomain from Google. Production robots.txt must ALLOW all.

## Requirements

### Functional
- Web scraping script extracts: product names, descriptions, images, URLs, PDFs (TDS/MSDS), category assignments.
- Raw Excel output structured with columns matching new ACF schema.
- Alkana team reviews/cleanses Excel (remove duplicates, standardize gloss level names, etc.).
- Import script reads Excel → creates WordPress `alkana_product` posts with CPT/ACF fields + taxonomy assignments.
- 301 redirect map covers 100% of old URLs → new URLs.
- `robots.txt` configured correctly on both staging and production.
- XML sitemap submitted to Google Search Console.

### Non-Functional
- Zero 404 errors after go-live (verified by Screaming Frog crawl).
- Import script must be idempotent (re-running does not create duplicate posts — match on SKU).
- Image download during import: rename to slug-based filenames, upload to WP Media Library with proper alt text.

## Architecture

### Scraping Pipeline
```
Step 1: SCRAPE (Week 1–2)
  └─ Python script (requests + BeautifulSoup)
       ├─ Crawl sitemap.xml or /san-pham/ archive
       ├─ Extract: title, description, images, PDFs, categories, URLs
       └─ Export to raw-alkana-products.xlsx

Step 2: CLEANSE (Alkana team, Weeks 3–4)
  └─ Excel review:
       ├─ Remove discontinued products
       ├─ Standardize gloss level terms (e.g., "Bán bóng" → "semi-gloss")
       ├─ Map old categories → new category slugs
       ├─ Fill in missing specs (coverage, dry time if missing)
       └─ Output: clean-alkana-products.xlsx

Step 3: IMPORT (Week 5)
  └─ PHP WP-CLI script (or WP All Import)
       ├─ Read clean-alkana-products.xlsx
       ├─ Create alkana_product post per row (upsert by SKU)
       ├─ Set taxonomy terms
       ├─ Save ACF fields
       ├─ Download + attach images to Media Library
       └─ Trigger sync-product-index hook per product
```

### Scraping Script (Python)
```python
# scripts/scrape-old-site.py
import requests
from bs4 import BeautifulSoup
import openpyxl, re, os

BASE_URL = "https://www.alkana.vn"  # old site URL
PRODUCT_ARCHIVE = f"{BASE_URL}/san-pham/"

def scrape_product(url: str) -> dict:
    r = requests.get(url, timeout=10)
    soup = BeautifulSoup(r.text, 'html.parser')
    return {
        'old_url':      url,
        'title':        soup.select_one('h1.product-title')?.get_text(strip=True),
        'description':  soup.select_one('.product-description')?.get_text(),
        'image_urls':   [img['src'] for img in soup.select('.product-images img')],
        'tds_url':      soup.select_one('a[href$=".pdf"][href*="TDS"]')?.get('href'),
        'msds_url':     soup.select_one('a[href$=".pdf"][href*="MSDS"]')?.get('href'),
        'category':     soup.select_one('.product-category a')?.get_text(strip=True),
    }

# Crawl + export to Excel
wb = openpyxl.Workbook()
ws = wb.active
ws.append(['old_url', 'title', 'description', 'image_urls', 'tds_url', 'msds_url', 'category',
           # Columns for Alkana team to fill:
           'sku', 'gloss_level', 'surface_types', 'paint_system', 'coverage', 'mix_ratio',
           'thinner', 'dry_touch', 'dry_hard', 'dry_recoat', 'new_url_slug'])
# ... scrape and append rows ...
wb.save('raw-alkana-products.xlsx')
```

### Import Script (WP-CLI + PHP)
```php
// scripts/import-products.php (run via: wp eval-file scripts/import-products.php)

$file = 'clean-alkana-products.xlsx';
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$spreadsheet = $reader->load($file);
$rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

array_shift($rows); // remove header
foreach ($rows as $row) {
    // Upsert by SKU
    $existing = get_posts(['post_type' => 'alkana_product',
        'meta_key' => '_alkana_sku', 'meta_value' => $row['D'], 'posts_per_page' => 1]);
    $post_id = $existing[0]->ID ?? wp_insert_post([
        'post_type'   => 'alkana_product',
        'post_title'  => $row['B'],
        'post_content'=> $row['C'],
        'post_status' => 'publish',
    ]);

    // Set ACF fields
    update_field('_alkana_sku',      $row['D'], $post_id);
    update_field('_alkana_coverage', $row['J'], $post_id);
    // ... etc

    // Set taxonomies
    wp_set_post_terms($post_id, [$row['H']], 'product_category');
    wp_set_post_terms($post_id, explode(',', $row['I']), 'surface_type');

    // Download + attach image
    if (!empty($row['E'])) {
        alkana_sideload_image($row['E'], $post_id);
    }

    // Trigger index sync
    do_action('acf/save_post', $post_id);

    WP_CLI::line("Imported: {$row['B']} (ID: {$post_id})");
}
```

### 301 Redirect Map — .htaccess Rules

```apache
# .htaccess — 301 Redirect Rules (OLD → NEW)
# Pattern-based (reduces rule count vs individual rules)

RewriteEngine On

# Old product URLs: /san-pham/{category}/{slug}/ → /products/{slug}/
RewriteRule ^san-pham/[^/]+/([^/]+)/?$  /products/$1/ [R=301,L,NE]

# Old category URLs: /san-pham/{category}/ → /product-category/{category}/
RewriteRule ^san-pham/([^/]+)/?$  /product-category/$1/ [R=301,L,NE]

# Old project URLs: /cong-trinh/{slug}/ → /projects/{slug}/
RewriteRule ^cong-trinh/([^/]+)/?$  /projects/$1/ [R=301,L,NE]

# Old news: /tin-tuc/{slug}/ → /news/{slug}/
RewriteRule ^tin-tuc/([^/]+)/?$  /news/$1/ [R=301,L,NE]

# Old contact: /lien-he/ → /contact/
RewriteRule ^lien-he/?$  /contact/ [R=301,L]

# Old about: /gioi-thieu/ → /about/
RewriteRule ^gioi-thieu/?$  /about/ [R=301,L]
```

### robots.txt

**Staging** (staging.alkana.vn):
```
User-agent: *
Disallow: /
```

**Production** (alkana.vn — Week 8):
```
User-agent: *
Allow: /
Disallow: /wp-admin/
Disallow: /wp-login.php
Disallow: /wp-content/plugins/
Sitemap: https://alkana.vn/sitemap.xml
```

## Related Code Files
- Create: `scripts/scrape-old-site.py`
- Create: `scripts/import-products.php`
- Create: `scripts/url-map.csv` (old URL → new URL, for Screaming Frog verification)
- Modify: `.htaccess` (301 redirect block)
- Create: `robots.txt` (production version)

## Implementation Steps

**Week 1 (parallel with Phase 01):**
1. Write and run `scrape-old-site.py` on old Alkana website.
2. Generate `raw-alkana-products.xlsx` with all scraped data.
3. Add "New URL Slug" column to Excel — dev pre-fills based on new URL pattern.
4. Send Excel to Alkana team for cleansing. Set deadline: End of Week 3.

**Week 3–4 (Alkana team):**
5. Alkana team cleanses Excel: remove junk, standardize terms, fill gaps.

**Week 5:**
6. Receive `clean-alkana-products.xlsx` from Alkana team.
7. Run import script on staging: `wp eval-file scripts/import-products.php`.
8. Verify: all products visible in WordPress admin, ACF fields populated, index table has rows.
9. Spot-check 10 products → frontend renders correctly.

**Week 5–6:**
10. Build `.htaccess` 301 rules using `scripts/url-map.csv` as source of truth.
11. Run Screaming Frog on old sitemap → verify all old URLs return 301 to correct new URL.
12. Fix any redirect gaps.

**Week 7 (before go-live):**
13. Prepare production `robots.txt`.
14. Prepare XML sitemap (WordPress SEO plugin: Yoast or Rank Math — lightweight config only).
15. Register property in Google Search Console (ready for Week 8 submission).

## Todo List
- [ ] Write scraping script (`scrape-old-site.py`)
- [ ] Run scraper → `raw-alkana-products.xlsx`
- [ ] Deliver Excel to Alkana team (by Week 2 end)
- [ ] Write import script (`import-products.php`)
- [ ] Test import on 5 sample rows
- [ ] Receive cleansed Excel from Alkana (Week 3–4)
- [ ] Run full import on staging
- [ ] Verify 50 products on frontend
- [ ] Build URL mapping CSV (all old URLs → new)
- [ ] Write `.htaccess` 301 rules
- [ ] Screaming Frog crawl: verify 0 broken redirects
- [ ] Staging robots.txt (block all)
- [ ] Production robots.txt (allow all)
- [ ] Google Search Console property ready
- [ ] XML sitemap URL verified

## Success Criteria
- All old site URLs return 301 (not 404) after go-live
- Screaming Frog reports 0 broken links post-launch
- Import script runs idempotently (re-run → no duplicate posts)
- All products have images, specs, and category assignments in new DB
- GSC sitemap submitted within 24h of go-live

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| Old site blocks scraper (bot protection) | Medium | Add request delays + User-Agent header; request Alkana for manual export if blocked |
| Alkana team delays Excel cleansing | High | Set hard deadline Week 3; offer to help with standardization |
| Old URLs use non-latin slugs (Vietnamese chars) | Medium | URL decode + normalize in scraper; map manually in url-map.csv |
| Products without images | Medium | Flag in import log → marketing team to upload post-launch |

## Security Considerations
- Scraping script: add rate limiting (1 request/second) to avoid triggering DDoS protection on old server
- Import script: run only from server CLI (wp eval-file), not exposed as HTTP endpoint
- Verify PDF files aren't executable before uploading to Media Library

## Next Steps
→ Phase 06: Performance tuning (needs complete product data for realistic PageSpeed testing)
→ Phase 07: Deployment (needs 301 map finalized)
