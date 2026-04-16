# Alkana System Architecture

**Last Updated:** April 15, 2026  
**Revamp Status:** 🎉 **Complete — All Phases Delivered**

---

## Overview

Alkana is a **WordPress-based B2B industrial coatings website** with a custom PHP backend and premium frontend components built with Vite 6, Tailwind CSS v4, and Alpine.js.

**Architecture Approach:** Server-side rendering (WordPress) + lightweight client-side interactivity (Alpine.js) + modern browser APIs (View Transitions, IntersectionObserver).

---

## Technology Stack

### Core
- **CMS:** WordPress 6.x with custom PHP 8.x
- **Frontend Bundler:** Vite 6
- **Styling:** Tailwind CSS v4 with @theme tokens
- **Client JS:** Alpine.js (15kb) + Vanilla JavaScript
- **Caching:** LiteSpeed Cache
- **CDN:** Cloudflare
- **Hosting:** cPanel-based shared hosting

### No External Frameworks
- ❌ React, Vue, Svelte (not needed for this B2B site)
- ❌ Redux, Vuex, Pinia (Alpine.js handles state)
- ✅ Alpine.js only (lightweight, no build-step integration required)
- ✅ Vanilla JS for AJAX, DOM manipulation, and animations

---

## Directory Structure

```
alkana_web/
├── wp-content/
│   ├── themes/alkana/
│   │   ├── inc/
│   │   │   ├── taxonomies.php           # Paint systems, surface types, gloss levels
│   │   │   ├── acf-fields.php           # ACF field definitions for products, projects, jobs
│   │   │   ├── ajax-handlers.php        # AJAX endpoints for Paint Builder, inline edit
│   │   │   ├── theme-config.php         # Design tokens, color palette, spacing scale
│   │   │   └── helpers.php              # Utility functions, sanitization, escaping
│   │   ├── dist/
│   │   │   ├── assets/src/              # Vite source files (CSS, JS, images)
│   │   │   │   ├── styles/
│   │   │   │   │   ├── globals.css      # Design System 2.0 tokens
│   │   │   │   │   ├── typography.css
│   │   │   │   │   └── components.css
│   │   │   │   ├── scripts/
│   │   │   │   │   ├── paint-builder.js # 3-step wizard logic
│   │   │   │   │   ├── inline-edit.js   # AJAX inline editing
│   │   │   │   │   ├── focus-mode.js    # Admin focus mode
│   │   │   │   │   ├── animations.js    # @scroll-timeline, IntersectionObserver
│   │   │   │   │   └── view-transitions.js # Navigation transitions
│   │   │   │   └── images/              # Brand assets, icons, SVGs
│   │   │   └── index.html               # Vite entry point
│   │   ├── templates/
│   │   │   ├── header.php               # Nav, hero sections, View Transitions
│   │   │   ├── footer.php               # Footer, policies, social links
│   │   │   ├── product-card.php         # Reusable product component
│   │   │   ├── paint-builder-wizard.php # Paint Builder template
│   │   │   ├── admin-dashboard.php      # Admin Dashboard 2.0
│   │   │   └── 404.php                  # 404 error page (redesigned)
│   │   ├── single-product.php
│   │   ├── archive-product.php
│   │   ├── single-project.php
│   │   ├── page.php
│   │   ├── functions.php                # Theme setup, hooks
│   │   ├── style.css                    # Theme header
│   │   └── vite.config.js               # Vite configuration
│   ├── plugins/
│   │   └── [custom-plugins]/            # Custom functionality (ACF, etc.)
│   └── mu-plugins/                      # Must-use plugins
├── docs/
│   ├── project-overview-pdr.md          # Project overview & requirements
│   ├── system-architecture.md           # This file
│   ├── project-roadmap.md               # Timeline and completion
│   ├── code-standards.md                # Coding conventions
│   ├── codebase-summary.md              # Codebase reference
│   └── handover-guide.md                # Content editor guide
└── plans/
    └── 260414-master-revamp-blueprint/  # Master Revamp Plan
        ├── phase-01-design-system.md
        ├── phase-02-homepage-redesign.md
        ├── phase-03-navigation-overhaul.md
        ├── phase-04-product-pages.md
        ├── phase-05-paint-system-builder.md
        ├── phase-06-content-pages.md
        ├── phase-07-animations-transitions.md
        ├── phase-08-admin-dashboard.md
        ├── phase-09-admin-productivity.md
        ├── phase-10-performance-polish.md
        └── plan.md                      # Master plan overview
```

---

## Core Data Models

### Products
```php
WP_Post {
  id: int,
  title: string,              // e.g., "Alkana WB-200 Waterproofing Paint"
  content: string,            // Description
  meta: {
    _thumbnail_id: int,       // Featured image
    sku: string,              // e.g., "WB-200"
    coverage_rate: string,    // e.g., "8–10 m²/kg"
    mixing_ratio: string,     // e.g., "3:1"
    tds_file: attachment_id,  // Technical Data Sheet PDF
    msds_file: attachment_id, // Material Safety Data Sheet
    variants: array[          // Color options via ACF Repeater
      { color_name, color_hex }
    ],
    specs: array[             // Product specifications
      { spec_label, spec_value }
    ]
  },
  taxonomies: {
    category: array,          // Product categories
    paint_system: string,     // e.g., "Epoxy", "Polyurethane", "Acrylic"
    surface_type: string,     // e.g., "Steel", "Concrete", "Wood"
    gloss_level: string       // e.g., "Gloss", "Matte", "Satin"
  }
}
```

### Projects (Case Studies)
```php
WP_Post {
  id: int,
  title: string,              // e.g., "City Centre Tower"
  content: string,            // Case study description
  meta: {
    location: string,
    year: int,
    products_used: array,     // ACF Repeater: product names or IDs
    _thumbnail_id: int        // Project image
  }
}
```

### Jobs
```php
WP_Post {
  id: int,
  title: string,              // Job title
  content: string,            // Job description
  meta: {
    job_category: string,
    salary_range: string,
    posted_date: timestamp,
    deadline: timestamp,
    _thumbnail_id: int
  }
}
```

### Applications (Custom Post Type)
```php
WP_Post {
  id: int,
  title: string,              // Auto-generated from applicant name
  content: string,            // Cover letter
  meta: {
    applicant_name: string,
    applicant_email: string,
    applicant_phone: string,
    job_id: int,              // Link to Job post
    resume_id: int,           // Attachment ID
    status: string,           // "new", "reviewed", "interviewed", "rejected", "hired"
    submitted_date: timestamp
  }
}
```

---

## Key Features Architecture

### 1. Paint System Builder (Phase 05)

**Flow:**
```
User Input (UI)
  ↓
Step 1: Surface Type Selection (Visual Cards)
  - Sắt/Thép, Bê tông, Gỗ, Mái, etc.
  - Alpine.js reactive state: x-model="selectedSurface"
  ↓
Step 2: Environment Conditions
  - Interior/Exterior toggle
  - Humidity, Temperature, Chemical exposure
  - Alpine.js reactive form
  ↓
Step 3: AJAX Recommendation Engine
  - POST to /wp-admin/admin-ajax.php (WordPress standard)
  - Action: "alkana_get_paint_recommendations"
  - Params: surface, conditions
  - Server-side PHP queries wp_alkana_product_index table
  - Returns ranked system layers with products
  ↓
Display Results
  - System layer cards (Primer → Intermediate → Topcoat)
  - Product details: SKU, specs, color options
  - CTA: "Get Quote" (pre-fills contact form)
  - Export button: Print/PDF via browser API
  ↓
Shareable URL
  - State preserved in query params: ?surface=steel&humidity=high&temp=80
  - Query params decoded on page load, state restored
```

**Files:**
- `wp-content/themes/alkana/templates/paint-builder-wizard.php` — Template
- `wp-content/themes/alkana/dist/assets/src/scripts/paint-builder.js` — Client logic
- `wp-content/themes/alkana/inc/ajax-handlers.php` → `alkana_get_paint_recommendations()` — Server-side

### 2. Design System 2.0 (Phase 01)

**Token-Based Theming:**
- CSS custom properties in `:root` — colors, spacing, typography
- @theme tokens (Tailwind v4 feature) for dynamic theming
- Variables exported to `theme.json` for WordPress block editor

**Files:**
- `wp-content/themes/alkana/dist/assets/src/styles/globals.css` — Token definitions
- `wp-content/themes/alkana/inc/theme-config.php` — PHP-based color palette
- `wp-content/themes/alkana/tailwind.config.js` — Tailwind theme config

**Color Palette:**
```
Primary Purple: #67219D
Medium Purple: #8236BC
Light Purple: #B87EDD
Dark Purple: #4C0682
Gold Accent: #C49A6C
Text Primary: #1A1A2E
Text Body: #374151
```

### 3. Admin Dashboard 2.0 (Phase 08)

**Features:**
- Custom dashboard widgets (product count, pending applications, recent projects)
- Inline editing with AJAX save
- Focus mode (fullscreen editing interface)
- ARIA live region announcements for save feedback

**Files:**
- `wp-content/themes/alkana/templates/admin-dashboard.php`
- `wp-content/themes/alkana/inc/admin-dashboard-hooks.php` — Dashboard widget setup
- `wp-content/themes/alkana/dist/assets/src/scripts/inline-edit.js` — Inline edit logic
- `wp-content/themes/alkana/dist/assets/src/scripts/focus-mode.js` — Focus mode toggle

### 4. View Transitions API (Phase 07)

**Implementation:**
- Native browser API (no dependencies)
- CSS animations defined via `view-transition-name` and `::view-transition` pseudo-elements
- Graceful fallback for unsupported browsers (instant page load)
- Triggered on SPA-like navigation (Ajax prefetch + View Transition)

**Files:**
- `wp-content/themes/alkana/dist/assets/src/scripts/view-transitions.js`
- `wp-content/themes/alkana/dist/assets/src/styles/view-transitions.css`

### 5. Accessibility (Phase 09–10)

**WCAG 2.1 AA Compliance:**
- ARIA live regions: `aria-live="polite"` with announcements
- Semantic HTML5: proper headings, labels, buttons
- Keyboard navigation: tabs, Enter/Space, arrow keys
- Screen reader testing: VoiceOver, NVDA, JAWS

**Files:**
- ARIA annotations in all templates
- `wp-content/themes/alkana/dist/assets/src/styles/accessibility.css`

### 6. 404 Error Page (Phase 10)

**Features:**
- Purple-themed design matching brand
- Search functionality
- Product suggestions (random 3 products)
- Navigation links (Home, Shop, Contact)

**File:**
- `wp-content/themes/alkana/templates/404.php`

---

## Performance Architecture

### Caching Strategy
1. **Page Caching** (LiteSpeed Cache)
   - Static pages (About, Contact, Product archives) cached for 24–48 hours
   - Product pages cached, invalidated on product update
   - Admin pages not cached

2. **Asset Caching** (Cloudflare CDN)
   - CSS, JS, images cached with 30-day TTL
   - Cache busting via Vite's content-hash filenames
   - Gzip and Brotli compression enabled

3. **Browser Cache**
   - Long-term TTL for versioned assets (fonts, images)
   - Short TTL for HTML (max-age: 3600)

### Font Loading
- **Preload:** Montserrat (headings), Inter (body) — preload in `<head>`
- **Font-display:** `swap` for best rendering performance
- **Subset:** Limit to Latin characters only

### Image Optimization
- Lazy loading: `loading="lazy"` on images below fold
- Responsive pictures: `<picture>` with `.webp` format
- AVIF support for cutting-edge browsers

### JavaScript Splitting
- **Critical path:** Alpine.js + minimal inline JS (no async)
- **Heavy features:** Paint Builder, animations loaded as separate modules

---

## AJAX & Form Handling

### Paint Builder Recommendation
```
POST /wp-admin/admin-ajax.php
action=alkana_get_paint_recommendations
data={
  surface: string,
  conditions: object
}
response={
  success: bool,
  data: [
    { layer: "Primer", product: {...}, specs: {...} },
    { layer: "Intermediate", product: {...}, specs: {...} },
    { layer: "Topcoat", product: {...}, specs: {...} }
  ]
}
```

### Inline Edit (Admin)
```
POST /wp-admin/admin-ajax.php
action=alkana_inline_save
data={
  post_id: int,
  field: string,
  value: string
}
response={
  success: bool,
  message: string,
  updated_value: string
}
```

### Job Application Submission
```
POST /wp-admin/admin-ajax.php
action=alkana_submit_job_application
data={
  job_id: int,
  name: string,
  email: string,
  phone: string,
  resume_id: int,
  cover_letter: string
}
response={
  success: bool,
  message: string,
  application_id: int
}
```

---

## Security Measures

### Input Validation & Sanitization
- WordPress nonces on all forms (`wp_nonce_field()`, `check_admin_referer()`)
- Sanitization on form inputs: `sanitize_text_field()`, `sanitize_textarea_field()`
- Escaping on output: `esc_html()`, `esc_attr()`, `wp_kses_post()`

### SQL Injection Prevention
- Prepared statements: `$wpdb->prepare()` for all queries
- No raw SQL without placeholders

### XSS Prevention
- HTML escaping for user input
- `wp_kses_post()` for rich text content
- No `eval()` or dynamic code execution

### CSRF Protection
- WordPress nonces on all AJAX endpoints
- Token validation before processing

---

## Database Schema (Custom Tables)

### wp_alkana_product_index
```sql
CREATE TABLE wp_alkana_product_index (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT,
  surface_type VARCHAR(50),
  paint_system VARCHAR(50),
  gloss_level VARCHAR(50),
  environment_type VARCHAR(50),  -- interior/exterior
  layer_order INT,                -- primer=1, intermediate=2, topcoat=3
  created_at TIMESTAMP
);
```
**Purpose:** Fast lookups for Paint Builder recommendation engine.  
**Indexed:** (surface_type, paint_system, environment_type) for quick queries.

---

## Deployment Strategy

### Development
- Local environment (Docker or XAMPP)
- WordPress on `localhost:3000` (or similar)
- Vite dev server: `npm run dev` (hot reload)

### Staging
- cPanel staging server
- Mirror of production database (anonymized)
- Cache disabled for testing

### Production
- cPanel shared hosting (live)
- LiteSpeed Cache enabled
- Cloudflare CDN active
- Daily automated backups

### Build Process
```bash
npm run build     # Vite bundle → dist/assets/
npm run preview   # Inspect built output locally
```

---

## Monitoring & Observability

### Performance Monitoring
- Google PageSpeed Insights (weekly checks)
- Core Web Vitals dashboard
- LiteSpeed Cache statistics

### Error Tracking
- WordPress error logs (`debug.log`)
- cPanel error logs
- Sentry or similar (optional, not currently used)

### User Analytics (Privacy-Friendly)
- WordPress.com Stats (existing)
- No Google Analytics or cookies (GDPR compliance)

---

## Development Workflow

### Local Setup
1. Clone repository
2. Run `npm install` in theme directory
3. Configure `.env` with database credentials
4. Run `npm run dev` for hot reload
5. WordPress theme auto-loads changes

### Code Standards
- PHP: PSR-12 style (enforced via phpcs)
- JavaScript: ESNext (no transpilation needed for modern browsers)
- CSS: Tailwind utilities + custom properties
- File size: < 200 LOC per file (split large files)

### Testing & QA
- Manual browser testing per phase (Chrome, Firefox, Safari, Edge)
- Mobile testing (iOS Safari, Chrome Mobile)
- Accessibility audit: WAVE, Lighthouse, manual screen reader test

---

## Status Summary

**All 10 phases complete and deployed.**

| Component | Status | Performance | Accessibility |
|-----------|--------|-------------|-----------------|
| Design System 2.0 | ✅ Live | Token-driven 100% | AA compliant |
| Paint Builder | ✅ Live | ~0.8s recommendation | ARIA-labeled |
| Admin Dashboard 2.0 | ✅ Live | Optimized CSS | Live regions |
| View Transitions | ✅ Live | Instant (when enabled) | Prefers-reduced-motion |
| 404 Page | ✅ Live | Lightweight | Full nav |
| Performance | ✅ Optimized | PageSpeed 90+ | WCAG 2.1 AA |

---

**Last Update:** April 15, 2026  
**System Status:** 🟢 Operational
