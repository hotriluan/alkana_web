# Alkana Project Overview & Product Development Requirements

**Last Updated:** April 15, 2026  
**Status:** 🎉 **Revamp Complete — All 10 Phases Delivered**  
**Project:** Alkana Swan Transformation (Master Revamp)

---

## Executive Summary

Alkana is a **premium B2B industrial coatings website** built on WordPress with custom PHP, Vite 6, and Tailwind CSS v4. The website serves as a digital showroom for Alkana coating products, featuring product catalogs, project portfolios, a paint system recommendation engine, and a modernized admin dashboard.

**Current Release:** 2.0 - Master Revamp (100% complete)  
**Total Effort:** ~120 hours across 10 phases  
**Architecture:** WordPress + Headless Frontend Components (Alpine.js, View Transitions API, Vanilla JS)

---

## Product Development Requirements (PDR)

### 1. Core Functional Requirements

#### A. Public-Facing Features
- **Product Catalog** with advanced filtering (surface type, paint system, gloss level)
- **Paint System Builder** — 3-step wizard recommends complete coating systems
- **Project Portfolio** with case study documentation
- **Job Postings** with application management
- **Premium Content Pages** (About, Technical Resources, Contact)
- **Premium Homepage** featuring hero sections, testimonials, and call-to-action forms

#### B. Admin Features
- **Admin Dashboard 2.0** with custom widgets and status overview
- **Inline Content Editing** (with ARIA live regions for accessibility)
- **Focus Mode** for distraction-free editing
- **Product Management** (ACF fields, variants, specs, images)
- **Project Management** (case studies, images, metadata)
- **Job Application Management** (filtering, status tracking)

#### C. Brand & UX Standards
- **Design System 2.0** with CSS custom properties and @theme tokens
- **Industrial Premium Aesthetic** (inspired by Jotun, AkzoNobel, PPG)
- **Brand Color Palette** (Purple-primary with gold accents)
- **Responsive Design** (mobile-first, all breakpoints verified)
- **View Transitions API** for smooth SPA-like navigation

### 2. Non-Functional Requirements

#### Performance
- **PageSpeed Mobile Score:** ≥ 90 (target: 95)
- **Largest Contentful Paint (LCP):** < 2.0s
- **Cumulative Layout Shift (CLS):** < 0.05
- **Time to Interactive:** < 3s
- **SEO:** No warnings in Search Console, 100% semantic HTML5

#### Security & Compliance
- **CORS:** Properly configured for AJAX requests
- **CSRF Protection:** Enabled for all forms (WordPress nonces)
- **XSS Mitigation:** Input sanitization via WordPress escaping functions
- **SQLi Protection:** Prepared statements via `$wpdb`
- **GDPR Compliance:** Privacy-friendly analytics, clear data handling
- **Accessibility (WCAG 2.1 AA):** Screen reader support, keyboard navigation, ARIA live regions

#### Code Quality
- **Architecture:** Modular PHP classes, self-documenting variable/function names
- **File Size:** Limit individual code files to <200 LOC for maintainability
- **Dependency Management:** No external JS frameworks beyond Alpine.js (15kb), Vite for bundling
- **Version Control:** Conventional commits, no secrets in repos
- **Testing:** Browser testing per phase completion, no failed regressions

#### Maintainability
- **Documentation:** Clear setup instructions, admin handover guide, technical architecture
- **Comments:** Code comments for non-obvious logic only (prefer self-documenting code)
- **Style Consistency:** Tailwind CSS for styling (no inline CSS in components)
- **Admin Documentation:** Handover guide for content editors and system admins

### 3. Success Metrics

| Metric | Target | Status |
|--------|--------|--------|
| PageSpeed Mobile | ≥ 90 | ✅ Achieved |
| LCP | < 2.0s | ✅ Achieved |
| CLS | < 0.05 | ✅ Achieved |
| Design Consistency | 100% | ✅ Achieved |
| Accessibility (WCAG 2.1 AA) | 100% | ✅ Achieved |
| Admin Dashboard Usability | Positive feedback | ✅ Delivered |
| Paint Builder Conversion | Track CTAs | ✅ Live |
| 404 Error Recovery | Search + Suggestions | ✅ Implemented |

### 4. Scope & Exclusions

#### In Scope (Completed)
- ✅ Design System 2.0 with token-based theming
- ✅ Homepage, Navigation, Product, Content page redesigns
- ✅ Paint System Builder with AJAX recommendation engine
- ✅ Admin Dashboard 2.0 with inline editing and focus mode
- ✅ Micro-interactions, View Transitions, animations
- ✅ Performance optimization (font hints, lazy loading, caching)
- ✅ 404 page redesign with search and product suggestions
- ✅ ARIA accessibility hardening (live regions, focus management)

#### Out of Scope (Future Sprints)
- E-commerce / payment integration
- Multi-language localization (i18n)
- User accounts and login portal
- Mobile native app
- Real-time chat widget
- A/B testing infrastructure
- GraphQL API layer

---

## Architecture Overview

### Technology Stack
```
Frontend:
  - WordPress (PHP 8.x) — CMS & server-side rendering
  - Vite 6 — JavaScript bundling and asset optimization
  - Tailwind CSS v4 — Utility-first styling with Design Tokens
  - Alpine.js (15kb) — Lightweight interactivity (Paint Builder, Inline Edit)
  - Vanilla JavaScript — AJAX, DOM manipulation, animations

Infrastructure:
  - LiteSpeed Cache — Page caching and asset optimization
  - Cloudflare CDN — Global content delivery and DDoS protection
  - cPanel — Hosting and backups
```

### Core Components

1. **Design System 2.0**
   - CSS custom properties (color palette, spacing, typography scale)
   - @theme tokens (Tailwind 4 feature) for dynamic theming
   - Responsive breakpoints (320px, 640px, 768px, 1024px, 1280px)

2. **Paint System Builder**
   - 3-step wizard (Surface → Environment → Recommendation)
   - AJAX-based recommendation engine (server-side PHP)
   - Shareable URLs with query param state preservation
   - Print/PDF export capability

3. **Admin Dashboard 2.0**
   - Custom widgets (product count, pending applications, recent projects)
   - Inline content editing (with ARIA announcements)
   - Focus mode (distraction-free editing interface)
   - Performance-optimized admin CSS

4. **View Transitions API**
   - Native API (no 3rd-party dependency)
   - Graceful fallback for unsupported browsers
   - SPA-like navigation without page reload visual delay

5. **Accessibility Layer**
   - WCAG 2.1 AA compliance
   - ARIA live regions (admin inline edit, focus mode)
   - Screen reader testing per component
   - Keyboard navigation fully functional

---

## Phase Completion Summary

| Phase | Deliverable | Status |
|-------|-------------|--------|
| 01 | Design System 2.0 (tokens, color palette, typography) | ✅ Complete |
| 02 | Homepage redesign (hero, testimonials, CTAs) | ✅ Complete |
| 03 | Navigation & Header/Footer overhaul | ✅ Complete |
| 04 | Product pages with premium cards & specs | ✅ Complete |
| 05 | Paint System Builder wizard (3-step, shareable) | ✅ Complete |
| 06 | Content pages redesign (About, Resources, Contact) | ✅ Complete |
| 07 | Micro-interactions, View Transitions, animations | ✅ Complete |
| 08 | Admin Dashboard 2.0 (widgets, custom CSS) | ✅ Complete |
| 09 | Admin productivity (inline edit, focus mode, ARIA) | ✅ Complete |
| 10 | Performance & Polish (404 redesign, accessibility) | ✅ Complete |

---

## Key Features Delivered

### Phase 05: Paint System Builder
- **3-step recommendation wizard:** Surface type → Environment conditions → Recommended systems
- **Intermediate product layer:** Maps surface conditions to paint system layers (Primer → Intermediate → Topcoat)
- **Shareable state:** Query parameters preserve wizard state for distributing recommendations
- **Print/PDF export:** Users can export recommendation sheets
- **Accessibility:** Full ARIA labels and semantic HTML

### Phase 10: Performance & Polish
- **404 Page Redesign:** Purple-themed 404 error page with search and product suggestions
- **ARIA Live Regions:** Admin inline edit and focus mode announce changes to screen readers
- **Font Preload Hints:** Optimized font loading in `<head>` for faster text rendering
- **Final performance audit:** All metrics verified (PageSpeed, LCP, CLS)

---

## Current System State

**Live URL:** https://alkana.vn/  
**Admin:** https://alkana.vn/wp-admin/  
**Cache Strategy:** LiteSpeed Cache + Cloudflare  
**Backup:** Daily backups via cPanel  

### Data Snapshot (April 2026)
- **Products:** 50+ SKUs with variants and specs
- **Projects:** 15+ case studies
- **Job Postings:** Active recruitment listings
- **Users:** 10+ editors and admins

---

## Documentation & Handover

- **[Handover Guide](./handover-guide.md)** — Day-to-day content management for editors
- **[System Architecture](./system-architecture.md)** — Technical architecture and file structure
- **[Project Roadmap](./project-roadmap.md)** — Timeline and completion status
- **[Codebase Summary](./codebase-summary.md)** — Codebase structure and key files

---

## Next Steps & Future Roadmap

### Immediate (Post-Launch)
- Monitor performance metrics via PageSpeed Insights and Search Console
- Collect user feedback on Paint Builder usability
- Track job application submissions and admin workflow efficiency

### Q2 2026 (Optional)
- E-commerce integration with payment gateway
- Multi-language support (Vietnamese + English)

### Q3+ 2026 (Backlog)
- User account system for saved recommendations
- Mobile app (React Native or Flutter)
- Advanced analytics dashboard
- API layer for integrations

---

## Contact & Ownership

**Project Lead:** Luan Hoang  
**Repository:** Git  
**Support:** Internal wiki + this documentation  
**Last Review:** April 15, 2026

---

**Status:** All 10 phases complete. System deployed and operational. Ready for production support and future enhancements.
