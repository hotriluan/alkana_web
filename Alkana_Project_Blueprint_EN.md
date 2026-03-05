# SYSTEM ARCHITECTURE & EXECUTION BLUEPRINT FOR ALKANA COATING WEBSITE PROJECT
**Execution Role:** AI Agent / Full-stack Developer
**Target Environment:** Premium Cloud Hosting (Shop) - Mat Bao
**Core Principles:** "Zero-Mockup Data" (100% UI rendered from the Database) and "Zero-Bloat" (No redundant code, load time < 2s).

---

## 1. OVERVIEW & PROJECT OBJECTIVES
* **Project:** Rebuild the B2B & B2C website system for Alkana Coating (Industrial paint, wood coating, waterproofing).
* **Current Status:** Outdated UI, slow loading speed, poor navigation.
* **New Objectives:** Modern interface (Industrial Modern), logical data structure, ultra-fast search and product filtering experience.
* **Scope:** EXCLUDES online payment features (E-commerce Checkout) and EXCLUDES the "Paint estimation tool". Utilize a Website Catalog model.

---

## 2. TECHNOLOGY ARCHITECTURE (TECH STACK)
Execution directives must strictly adhere to the limitations of a Shared Hosting/Cloud Hosting cPanel environment:

* **Web Server Infrastructure:** Mat Bao Premium Cloud Hosting (Use default LiteSpeed or Apache).
* **Database:** MySQL or MariaDB. Requires a normalized schema design and indexing for frequently searched data fields.
* **CMS & Backend:** Utilize WordPress core (PHP). **Strictly NO pre-built Themes or Page Builders (Elementor, WPBakery, etc.)**. Setup Custom Post Types (CPT) and Advanced Custom Fields (ACF) for backend data management.
* **Frontend:** Develop a Custom Theme from scratch. Use Tailwind CSS and Vanilla JavaScript.
* **Security & Distribution:** Integrate SSL, configure DNS routing via Cloudflare to leverage CDN and WAF.

---

## 3. SITEMAP & INFORMATION ARCHITECTURE
The system must establish the following modules:
1.  **Home:** Hero banner, USPs, Categories, Featured Products, Projects, News (All data fetched from the Database).
2.  **About Alkana:** History, Mission, Production capabilities, Certifications (ISO).
3.  **Products (Catalog):** Categorized by group: Industrial Paint, Wood Coating, Waterproofing, Auxiliaries.
4.  **Solutions & Applications:** Categorized by needs (Wood, Steel structures, Factory floors, Civil).
5.  **Featured Projects (Portfolio):** Real-world image library, tagged by industry for filtering.
6.  **Resources & Support:** TDS/MSDS Download Center, Application Guides, FAQs.
7.  **News & Blog:** Company news, Industry knowledge.
8.  **Careers:** Benefits, Job openings, CV submission form.
9.  **Contact:** Map, Consultation/Quote form, Branch information.

---

## 4. DATABASE SCHEMA (PRODUCT ENTITY)
Strict requirement to initialize custom data fields (ACF) for the "Product" CPT, instead of using a generic WYSIWYG editor.

* **Identification Group:** Commercial Name, SKU, Primary Category, Secondary Category, Function (Tags).
* **Technical Specifications Group:** Applied Surface (Checkbox/Select), Theoretical Coverage, Mixing Ratio, Compatible Thinner, Drying Time (Touch dry, Hard dry, Recoat time). 
    * *UI Directive:* If a specification is empty in the DB, automatically hide the display row on the Frontend.
* **Variant Group:** Color System (Color Name + Hex Code), Packaging sizes, Gloss level.
* **Resource Group:** File uploads restricted to PDF format (TDS, MSDS), Certifications.
* **Relational Group:** Frequently bought together (Cross-sell), Related application projects.

---

## 5. ADMIN DASHBOARD (BACKEND UX & DATA GOVERNANCE)
* **Lock Input Data:** Use Dropdowns, Checkboxes, and Radio buttons for technical specs (e.g., Gloss, Surface) to prevent garbage data caused by manual typos.
* **Minimalist UI:** Redesign the WordPress Admin Workspace, hiding unnecessary menus. Clearly divide input areas: Content, Specs, Resources.
* **Role-Based Access Control (RBAC):** Set up permissions for Administrators, Content Editors (Marketing), and Technical/Safety Data Editors (R&D).

---

## 6. FACETED SEARCH LOGIC (PRODUCT FILTER)
Requirement to build a smart multi-layer filter on the Product Catalog page:
* **Technology:** AJAX/Fetch API queries (No page reloads).
* **Logic:** Use `OR` logic within the same criteria group, and `AND` logic across different criteria groups.
* **Filter Criteria:** Category, Applied Surface (Most critical), Specific Features, Paint System.
* **Filter UX:** Display dynamic product counts (Dynamic Count), automatically disable options that return 0 results, show active filter tags (Active Tags), and ensure the Empty State calls up a Consultation Form instead of showing a blank page.

---

## 7. DESIGN DIRECTION (UI/UX & MOBILE-FIRST)
* **Mobile-First UX:**
    * Sticky Search bar.
    * Accordion-style menu design.
    * Product filter using a Bottom Sheet mechanism (Slides up from the bottom, occupying 80% of the screen).
    * Specification tables converted to Card Lists or Horizontal scroll.
    * Sticky CTA buttons (Call, Zalo, Get Quote) at the bottom edge of the screen.
* **Visual & Branding (Industrial Modern):**
    * Generous White space. Card-based interface.
    * Colors: Extracted from the Alkana Logo, using CSS Variables. Text color should be Dark Charcoal (#1A1A1A or #333333), with White or Light Gray backgrounds.
    * Typography: Montserrat/Inter for Headings, Inter/Roboto for Body Text.
    * Micro-interactions: Floating hover effects for cards, slightly rounded buttons (4-6px).

---

## 8. PERFORMANCE PIPELINE
Strictly adhere to ensure load times < 2s:
* **Media Processing:** Automatically convert uploaded images to WebP/AVIF. Apply Responsive Images mechanism (`srcset`).
* **Source Code:** Minify and bundle CSS/JS. Defer/Async non-essential JS (Analytics, Tracking).
* **Rendering:** Lazy load all images/iframes below the fold. Preload brand fonts and the Hero Banner (LCP).
* **Caching:** Activate LiteSpeed Page Caching on Mat Bao and Cloudflare CDN.

---

## 9. DATA MIGRATION & SEO PROTECTION
* **Web Scraping:** Write an automated script to scrape all data from the old site (Text, Media, PDFs, URLs). Export to a raw Excel file.
* **Data Cleansing:** Require the Alkana team to review, remove junk, and input standardized specs into the Excel file before importing into the new Database.
* **SEO Protection:** Create a URL Mapping blueprint. Write 301 Redirect rules to safely route 100% of old links to their corresponding new links. Ensure zero 404 errors.
* **Post-Launch Configuration:** Create a standard robots.txt, Submit XML Sitemap to Google Search Console.

---

## 10. DEPLOYMENT ROADMAP (AGILE - 8 WEEKS)
* **Sprint 1 (Weeks 1-2):** Setup Staging environment. Create Database Schema (CPT/ACF). Design Figma UI. Begin data gathering & cleansing.
* **Sprint 2 (Weeks 3-4):** Frontend coding (Tailwind). Program AJAX filter. Code minimalist Admin UI for data entry.
* **Sprint 3 (Week 5):** Scrape old sitemap. Map 301 Redirects. Import real data into the Database.
* **Sprint 4 (Weeks 6-7):** Setup Image Compression & Cache Pipeline. UAT testing, cross-browser compatibility, PageSpeed optimization.
* **Sprint 5 (Week 8):** Package and Deploy to Mat Bao Production. Configure SSL, Cloudflare CDN. Handover documentation.