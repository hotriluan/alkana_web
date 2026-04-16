# Alkana Theme — Gap Analysis Report
## Master Blueprint Audit (Royal Purple Pivot)

**Date:** 2026-04-14  
**Auditor:** Chief Architect Review (AI-driven codebase scan)  
**Scope:** `wp-content/themes/alkana/` — all PHP, JS, CSS source files

---

## Executive Summary

After executing multiple sprints focused on the Royal Purple theme pivot and core UI/UX foundations, the Alkana theme has achieved approximately **45–50% completion** against the Original 4-Pillar Master Blueprint. The infrastructure layer (design tokens, taxonomies, AJAX backbone, admin scaffolding) is solid and complete. The critical gap lies in three categories: (1) JavaScript-driven interaction enhancements (view transitions, hover physics engine), (2) the Paint System Builder wizard — the single most impactful B2B conversion feature — which has zero implementation, and (3) two admin productivity features (inline grid editing, focus mode writing mode) that were never started. Five features are fully shipped; three are partially implemented with functional cores but missing polish or scope; four are entirely absent and require greenfield development in the next sprint.

---

## Audit Table

| # | Feature | Pillar | Status | File References / RCA |
|---|---------|--------|--------|-----------------------|
| 1 | **80-15-5 Color Rule** (white/purple ratio) | Frontend UI/UX | ⚠️ Partial | `src/styles/variables.css` — Full Royal Purple palette (`#7B1FA2` primary, `#38006b` secondary) is defined and applied globally via CSS variables + Tailwind config. Structural white (#FFFFFF / `--color-bg`) dominates layouts. Purple used for CTAs, borders, and accents. **Gap:** No ratio enforcement tooling or documented audit mechanism; visual balance depends on per-template implementation quality, which is inconsistent across archive vs. single vs. front-page templates. |
| 2 | **View Transitions (SPA-like)** | Frontend UI/UX | ❌ Missing | No `document.startViewTransition()` call exists anywhere in the codebase. No SPA routing library (Barba.js, Swup, Turbo, Astro) is installed or referenced in `package.json`. Full browser page reloads occur on all navigation. `src/scripts/app.js` handles only mobile menu, accordion, back-to-top, and sticky CTA. |
| 3 | **Hover Physics** (product/project cards) | Frontend UI/UX | ⚠️ Partial | `src/styles/components/cards.css` — `.card:hover { transform: translateY(-8px); }` with `cubic-bezier(.175,.885,.32,1.275)` (approximates a spring bounce). `template-parts/product-card.php` — `group-hover:scale-105` on images. `template-parts/project-card.php` — same pattern. **Gap:** No JS-based physics engine (no tilt/gyro effect, no momentum drag, no magnetic cursor pull). The spring feel is CSS-only approximation. |
| 4 | **Mega Menu** (icons + featured products) | Frontend UI/UX | ✅ Done | `inc/class-alkana-mega-menu-walker.php` — Full implementation. Left column renders `product_category` terms with ACF icon field + SVG fallback. Right column queries `wp_alkana_product_index` for 2 featured products with thumbnails. CSS hover show/hide with `opacity/translate-y` transition. Activated via `has-mega-menu` CSS class in WP Menus. |
| 5 | **Command Palette (Ctrl+K)** | Admin UX | ✅ Done | `inc/admin/command-palette.php` — Modal HTML injected via `admin_footer`. `src/scripts/admin-palette.js` — Ctrl+K / Cmd+K trigger, debounced WP REST API product search (`/wp/v2/alkana_product`), keyboard navigation (↑↓ + Enter to open edit screen), ESC to close. Compiled to `dist/assets/admin-DzPf0krx.js`. |
| 6 | **Inline Grid Editing** (double-click coverage/mix-ratio) | Admin UX | ❌ Missing | `inc/admin/list-tables.php` — Defines custom columns (Thumbnail, SKU, Category, Docs) but all cells are **static read-only HTML**. No `dblclick` handler, no `contenteditable` cells, no AJAX save endpoint for inline edits. The blueprint feature (double-click to edit coverage or mix-ratio directly in the post list) is entirely absent. |
| 7 | **Focus Mode** (distraction-free dark writing mode) | Admin UX | ❌ Missing | No implementation found anywhere. `inc/admin/admin-theme.php` and `inc/admin/admin-style.css` handle only branding/color overrides for the admin panel. No full-screen toggle, no dark overlay, no distraction-free editor state. |
| 8 | **Predictive AJAX Search** (instant modal results) | B2B Conversion | ✅ Done | `template-parts/search-modal.php` — Full-screen `bg-alkana-purple-900/95` overlay with `backdrop-blur-md`. `src/scripts/search.js` — debounced AJAX fetch to `inc/ajax/search-handler.php`, renders instant product results with thumbnails, SKU, and direct links. Nonce-protected. Keyboard accessible (Escape to close). |
| 9 | **Paint System Builder** (Surface → Environment → Recommendation wizard) | B2B Conversion | ❌ Missing | `inc/taxonomies.php` — `paint_system` taxonomy exists as a flat classification only. `acf-json/group_alkana_product_specs.json` — has a `Paint System Layer` field. **But:** No multi-step wizard template, no step-by-step JS state machine, no AJAX recommendation endpoint, no `page-paint-builder.php` template. The feature is **zero-percent implemented**. This is the highest-priority gap for the next sprint. |
| 10 | **Sticky Quote Bar** (tracks CTA button, sticks to screen) | B2B Conversion | ⚠️ Partial | `template-parts/sticky-cta-mobile.php` — CTA bar with quote + Zalo buttons. `src/scripts/sticky-cta.js` — `IntersectionObserver` on `#product-cta-main` correctly hides/shows the bar when the in-page CTA scrolls out of view. **Gap:** CSS forces `display: none` at `min-width: 1024px`. The bar is **mobile-only**. A desktop sticky quote bar that tracks the hero/section CTA button is absent. |
| 11 | **Skeleton Loading** (replaces spinners during AJAX filter) | Perceived Performance | ✅ Done | `src/scripts/filter.js` lines 179–194 — `skeletonCard()` generates `animate-pulse` Tailwind skeleton markup. `showSkeletons()` injects 6 skeleton cards into `#product-grid` on every filter trigger, before the AJAX response replaces them. Compiled and confirmed in `dist/assets/app-5r2W_IQL.js`. |

---

## Missing Features Roadmap

The following four features are rated ❌ Missing and require greenfield development in the upcoming sprint.

---

### ❌ Feature 2 — View Transitions (SPA-like Page Routing)

**Technical Proposal:**  
Integrate the native [View Transitions API](https://developer.chrome.com/docs/web-platform/view-transitions/) (`document.startViewTransition()`) via a lightweight JS module (`src/scripts/transitions.js`) that intercepts anchor clicks, fetches the next page via `fetch()`, and swaps `document.body` innerHTML inside a transition callback — providing a cross-fade or slide effect between pages without a full reload.  
For broader browser support (Safari < 18), add a `@supports` CSS fallback and wrap the `startViewTransition` call in a feature-detect guard so the site degrades gracefully to instant navigation.

---

### ❌ Feature 6 — Inline Grid Editing (Double-click cell to edit)

**Technical Proposal:**  
Extend `inc/admin/list-tables.php` to render editable cells (coverage, mix-ratio, SKU) with a `data-inline-edit` attribute and hidden `<input>` elements. Add a new JS module `src/scripts/admin-inline-edit.js` that listens for `dblclick` on target cells, toggles a `contenteditable` input into view, and on `blur` / `Enter` fires an AJAX POST to a new handler `inc/ajax/inline-edit-handler.php` which validates the nonce, sanitizes the value, and updates the corresponding `alkana_product_index` row and post meta — then returns a JSON `{success, new_value}` to update the cell display in place.

---

### ❌ Feature 7 — Focus Mode (Distraction-free dark writing mode)

**Technical Proposal:**  
Add a "Focus Mode" toggle button to the WordPress post editor toolbar (hooked via `add_filter('wp_editor_settings')` + `admin_head` inline JS) that, when activated, adds a `.alkana-focus-mode` class to `document.body`. A corresponding CSS block in `inc/admin/admin-style.css` will hide the admin menu, toolbar, meta boxes, and sidebar, expand the editor to full viewport width, and switch to a dark-background/off-white-text reading palette — providing a clean, distraction-free writing surface. ESC or the toggle button exits focus mode.

---

### ❌ Feature 9 — Paint System Builder (Multi-step Recommendation Wizard)

**Technical Proposal (Highest Priority):**  
Create a dedicated template `templates/page-paint-builder.php` powered by a three-step Alpine.js (or vanilla JS) state machine: **Step 1** (Surface Type — renders `surface_type` taxonomy terms as visual cards), **Step 2** (Environment & Conditions — substrate condition, interior/exterior, humidity), **Step 3** (Product Recommendation — fires an AJAX POST to a new handler `inc/ajax/paint-builder-handler.php` which queries `wp_alkana_product_index` with the collected surface/system/gloss filters and returns a ranked product shortlist with specs, TDS links, and a "Request Quote" CTA).  
This is the single highest-ROI B2B conversion feature and should be the lead deliverable for Sprint 5.

---

*End of Report*
