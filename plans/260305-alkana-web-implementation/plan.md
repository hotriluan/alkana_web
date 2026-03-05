---
title: Alkana Coating Website — Full Implementation Plan
status: pending
created: 2026-03-05
source: plans/brainstorm/260305-alkana-web-brainstorm-report.md
blueprint: Alkana_Project_Blueprint_EN.md
architecture: Option C (WordPress + Optimized Query Layer)
---

# Alkana Coating Website — Implementation Plan

## Context Links
- Brainstorm report: [`plans/brainstorm/260305-alkana-web-brainstorm-report.md`](../brainstorm/260305-alkana-web-brainstorm-report.md)
- Blueprint: [`Alkana_Project_Blueprint_EN.md`](../../Alkana_Project_Blueprint_EN.md)

## Architecture Summary
```
Mat Bao cPanel
└── WordPress (PHP 8.x)
    ├── Custom Theme (Vite + Tailwind CSS + Vanilla JS)
    ├── CPT: alkana_product + ACF Pro (data input layer)
    ├── wp_alkana_product_index table (denormalized, indexed — fast filter SQL)
    ├── Custom AJAX endpoint → direct SQL query (bypass WP_Query)
    └── MySQL 8.0
Cloudflare CDN + WAF (Free tier — Polish: OFF)
QUIC.cloud Free Tier + LSCache (WebP image optimization)
LiteSpeed Cache (full-page + object cache)
```

## Phases & Status

| # | Phase | Status | Weeks | Priority |
|---|---|---|---|---|
| 00 | [Environment Setup](phase-00-env-setup.md) | ✅ complete | Pre-W1 | Critical |
| 01 | [Database Schema & CPT/ACF](phase-01-database-schema.md) | ✅ complete | W1–W2 | Critical |
| 02 | [Custom Theme + Vite Pipeline](phase-02-custom-theme-vite.md) | ✅ complete | W3–W4 | Critical |
| 03 | [AJAX Faceted Filter](phase-03-ajax-faceted-filter.md) | ✅ complete | W3–W4 | Critical |
| 04 | [Admin UI & RBAC](phase-04-admin-ui-rbac.md) | ✅ complete | W4–W5 | High |
| 05 | [Data Migration & SEO](phase-05-data-migration-seo.md) | 🔄 scripts ready | W1→W5 | Critical |
| 06 | [Performance Pipeline](phase-06-performance-pipeline.md) | ⏳ pending | W6–W7 | High |
| 07 | [Deployment & Handover](phase-07-deployment.md) | ⏳ pending | W8 | Critical |

## Key Dependencies

```
Phase 00 → Phase 01 → Phase 02, 03 (parallel) → Phase 04
                   ↘ Phase 05 (start parallel from Phase 01)
Phase 02 + 03 + 04 + 05 → Phase 06 → Phase 07
```

## Critical Risks (from brainstorm)

| Risk | Mitigation |
|---|---|
| WP_Query filter slow | `wp_alkana_product_index` table + direct SQL |
| Tailwind on Shared Hosting | Vite local build → deploy compiled dist/ |
| Data migration blocker | Start scraping Week 1 parallel to schema |
| AVIF unsupported on cPanel | Cloudflare Image Polish (no server ImageMagick) |
| 8-week timeline tight | Phased launch: Blog/Resources in Week 9–10 |

## Phased Launch Strategy

- **Week 8 (Hard launch):** Homepage, Products catalog + filter, About, Contact, Projects portfolio
- **Week 9–10 (Phase 2):** Resources/TDS center, Careers, Blog, News

## Success Metrics

| Metric | Target |
|---|---|
| PageSpeed Mobile | ≥ 85 |
| LCP | < 2.5s |
| Filter AJAX response | < 500ms |
| 404 rate post-launch | 0% |
| Product add (admin) | < 5 min/product |
