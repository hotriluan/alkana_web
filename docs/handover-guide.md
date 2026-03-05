# Alkana Coating Website — Handover Guide

> **Who:** Alkana content editors + system admins  
> **Purpose:** Day-to-day content management, product updates, and emergency procedures  
> **Version:** 1.0 — March 2026

---

## Quick Links

| Task | WP Admin Path |
|------|--------------|
| Add new product | Post > Products > Add New |
| Add new project | Post > Projects > Add New |
| Update homepage banners | Pages > Front Page > Edit |
| Clear website cache | LiteSpeed Cache > Toolbox > Purge All |
| Manage categories | Products > Product Categories |
| View error logs | cPanel > Errors |

---

## 1. WordPress Admin Login

**URL:** `https://alkana.vn/wp-admin/`

If you forget your password:
1. Go to `https://alkana.vn/wp-login.php`
2. Click "Lost your password?"
3. Enter your email address registered with WordPress
4. Check your email for the reset link

> **Security:** Never share your admin password. Each team member should have their own account.

---

## 2. How to Add a New Product

**Time required:** ~5 minutes per product  
**Role required:** Editor or higher

### Steps

1. Go to WP Admin → **Products → Add New**
2. **Title:** Enter full product name (e.g. `Alkana WB-200 Waterproofing Paint`)
3. **Content area:** Add a brief product description (shown in Details tab on product page)
4. **Product Image (right sidebar):**
   - Click "Set featured image"
   - Upload a high-quality product photo (JPG, minimum 800×600px)
   - Click "Set featured image" to confirm
5. **Product Categories (right sidebar):** Tick one or more categories
6. **Surface Type, Paint System, Gloss Level:** Tick the appropriate taxonomy terms
7. **Fill ACF fields (below the content editor):**

| Field | What to enter |
|-------|--------------|
| SKU | Product code (e.g. `WB-200`) |
| Coverage Rate | E.g. `8–10 m²/kg` |
| Mixing Ratio | E.g. `Part A : Part B = 3:1` |
| Recommended Dry Film | E.g. `80–100 μm` |
| TDS File | Upload the Technical Data Sheet PDF |
| MSDS File | Upload the Material Safety Data Sheet PDF |
| Variants (Repeater) | Add rows: Colour Name + Colour Code (HEX) |
| Product Specs (Repeater) | Add rows: Spec Label + Spec Value |

8. **Publish:** Click "Publish" (top right)
9. The product automatically appears in the filter and catalog.

> **After publishing:** The product index table auto-updates. No manual sync required.

---

## 3. How to Edit an Existing Product

1. Go to **Products → All Products**
2. Find the product (use Search bar at top)
3. Click the product title to open editor
4. Make changes — click **Update** to save
5. Cache clears automatically for that product's page

---

## 4. How to Add a New Project (Portfolio)

1. Go to **Projects → Add New**
2. **Title:** Project name (e.g. `City Centre Tower Waterproofing`)
3. **Content:** Project description / write-up
4. **Featured Image:** Upload project photo
5. **ACF fields:**
   - Location: City/building name
   - Year: Year completed (4 digits)
   - Products Used: Repeater → add product names used
6. **Publish**

---

## 5. How to Update Homepage Banners / Hero Section

1. Go to **Pages → Front Page → Edit** (or hover over the page title and click Edit)
2. Scroll down to the ACF field group **"Hero Section"**:
   - **Hero Image:** Upload/swap banner photo (recommended: 1920×800px JPG)
   - **Hero Title:** Large headline text
   - **Hero Subtitle:** Supporting text below headline
   - **CTA Label:** Button text (e.g. `Explore Products`)
   - **CTA URL:** Button destination URL
3. Click **Update**
4. Clear cache: LiteSpeed Cache → Toolbox → Purge All

---

## 6. How to Add / Edit Taxonomy Terms

**Product Categories, Surface Types, Paint Systems, Gloss Levels** are shared taxonomy terms used for filtering.

1. Go to **Products → Product Categories** (or Surface Types, etc.)
2. To **add**: fill in Name, Slug (auto-generated), click "Add New Category"
3. To **edit**: hover over term name, click "Edit"
4. To **delete**: hover, click "Delete" — only delete if NO products use this term

---

## 7. How to Clear the Cache

After major content updates (new products, page edits):

1. Go to **LiteSpeed Cache** (left menu)
2. Click **Toolbox → Purge**
3. Click **Purge All** → confirm

Or: Look for the "LiteSpeed Cache" toolbar icon at top of any front-end page → click "Purge All".

> **Automatic purge:** The cache auto-clears for individual pages when you save/update them. Manual purge is only needed for global changes (navigation, homepage, new taxonomy terms).

---

## 8. User Roles

| Role | Permissions |
|------|------------|
| Administrator | Full access |
| alkana_content_editor | Add/edit products, projects, pages. Cannot manage plugins/themes. |
| alkana_tech_editor | Add/edit product specs and variants only. Cannot edit other fields. |

To add a team member:
1. **Users → Add New**
2. Fill email, auto-generate password (they'll get an email)
3. Set Role appropriately

---

## 9. Hosting & Infrastructure Access

> **Note:** Credentials are provided separately via a secure channel (1Password / LastPass / direct handover). Do not store passwords in this document.

| Service | URL | Purpose |
|---------|-----|---------|
| Mat Bao cPanel | `https://matbao.com/cpanel` | Hosting, files, database, email |
| Cloudflare | `https://dash.cloudflare.com` | DNS, CDN, WAF |
| QUIC.cloud | `https://quic.cloud` | WebP image optimization |
| Google Search Console | `https://search.google.com/search-console` | SEO monitoring |
| WordPress Admin | `https://alkana.vn/wp-admin` | Content management |

---

## 10. How to Submit / Check Google Search Console

1. Log in at `https://search.google.com/search-console`
2. Select `alkana.vn` property
3. **Check sitemap:** Sitemaps → confirm `https://alkana.vn/sitemap.xml` is submitted
4. **Check coverage:** Pages → look for "Not indexed" warnings
5. **Check Core Web Vitals:** Experience → Core Web Vitals

---

## 11. Troubleshooting Common Issues

### Website shows old content after update
→ Clear cache: LiteSpeed Cache → Toolbox → Purge All

### Product doesn't appear in search/filter
→ Open the product in WP Admin → scroll to bottom → click "Update" (re-saves and triggers index sync)

### Product image not showing as WebP
→ Go to LSCache → Image Optimization → check Summary → click "Send Optimization Request" for any pending images

### Contact form not sending emails
→ Check spam folder first. If missing entirely, check SMTP plugin settings or contact dev team.

### Site showing maintenance/error page
→ Check cPanel → Error Logs for PHP errors. Contact dev team with the error log excerpt.

---

## 12. Emergency Contacts

| Name | Role | Contact |
|------|------|---------|
| Developer | Website development & technical issues | *(provided separately)* |
| Mat Bao Support | Hosting issues, server downtime | 1800 6663 (24/7) |
| Cloudflare Support | CDN/DNS issues | `https://support.cloudflare.com` |

---

## 13. Post-Launch Phase 2 (Weeks 9–10)

Features scheduled for Phase 2:
- **Blog / News** — Article listing and full posts
- **Resources Center** — TDS/MSDS download hub with product search
- **Careers** — Job listing page with application form

These will be deployed by the development team without requiring DNS changes or downtime.
