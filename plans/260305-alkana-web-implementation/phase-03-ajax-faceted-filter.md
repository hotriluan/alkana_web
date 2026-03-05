# Phase 03 — AJAX Faceted Filter

## Context Links
- Plan: [plan.md](plan.md)
- Brainstorm risk §1 (WP_Query bottleneck), §4.1 (index table), §4.2 (AJAX architecture)
- Blueprint §6 Faceted Search Logic
- Depends on: [Phase 01 — Database Schema](phase-01-database-schema.md) (index table)

## Overview
| Item | Detail |
|---|---|
| **Priority** | Critical |
| **Status** | ⏳ pending |
| **Timeline** | Weeks 3–4 (parallel with Phase 02) |
| **Depends on** | Phase 01 (index table + taxonomies), Phase 02 (filter panel UI) |
| **Blocks** | Phase 06 (filter must work before perf testing) |

## Key Insights
- **Golden Rule:** `wp_alkana_product_index` is used for filtering. WP_Query / postmeta is used for rendering only.
- OR within group + AND across groups: e.g., `(surface IN wood,steel) AND (paint_system = epoxy)`.
- Dynamic counts: returned per AJAX call so unavailable options auto-disable (no 0-result dead ends).
- Empty state must show consultation CTA — not blank page.
- Nonce verification on all AJAX endpoints.
- Debounce filter changes: 300ms delay before firing AJAX (prevent rapid re-requests).

## Requirements

### Functional
- Multi-layer filter: Category, Surface Type, Paint System, Gloss Level.
- OR logic within the same filter dimension, AND logic across dimensions.
- No page reload — Fetch API posts filter state → receives rendered HTML + counts.
- Dynamic count badges on each filter option (update after each AJAX response).
- Options with 0 results shown as disabled (not hidden — preserve layout stability).
- Active filter tags displayed above product grid with individual "×" remove controls.
- Empty state renders consultation form CTA instead of empty product grid.
- Filter state preserved in URL query params (`?surface=wood,steel&system=epoxy`) for shareable/bookmarkable links.
- Mobile Bottom Sheet filter (from Phase 02 UI) wired to this AJAX logic.

### Non-Functional
- AJAX response time < 500ms (target < 200ms).
- No WP_Query in the filter SQL path — direct `$wpdb->get_results()` only.
- Debounce: 300ms.
- All user input sanitized server-side before SQL.

## Architecture

### Data Flow
```
User checks "Wood" + "Steel" in Surface filter
         ↓ 300ms debounce
filter.js → fetch POST /admin-ajax.php?action=alkana_filter
         ↓
PHP: alkana_filter_handler()
  → sanitize input
  → build SQL on wp_alkana_product_index
  → get matching product_ids + per-option counts
  → WP get_posts(['post__in' => $ids]) + render template-parts/product-card.php
  → return JSON { html, counts, total }
         ↓
filter.js → replace product grid innerHTML
          → update count badges
          → update active tags
          → push state to URL (History API)
```

### PHP AJAX Handler
```php
// inc/ajax/filter-handler.php

add_action('wp_ajax_alkana_filter',        'alkana_filter_handler');
add_action('wp_ajax_nopriv_alkana_filter', 'alkana_filter_handler');

function alkana_filter_handler(): void {
    check_ajax_referer('alkana_filter', 'nonce');

    // 1. Sanitize inputs
    $surfaces     = array_map('sanitize_key', (array) ($_POST['surfaces']     ?? []));
    $paint_system = sanitize_key($_POST['paint_system'] ?? '');
    $gloss        = sanitize_key($_POST['gloss']        ?? '');
    $category     = sanitize_key($_POST['category']     ?? '');
    $page         = max(1, (int) ($_POST['page'] ?? 1));
    $per_page     = 12;

    // 2. Build filter SQL
    global $wpdb;
    $table  = $wpdb->prefix . 'alkana_product_index';
    $where  = ['1=1'];
    $params = [];

    if (!empty($category)) {
        $where[]  = 'FIND_IN_SET(%s, category_slugs)';
        $params[] = $category;
    }
    if (!empty($surfaces)) {
        $surface_conditions = array_map(fn($s) => 'FIND_IN_SET(%s, surface_slugs)', $surfaces);
        $where[]  = '(' . implode(' OR ', $surface_conditions) . ')';
        $params   = array_merge($params, $surfaces);
    }
    if (!empty($paint_system)) {
        $where[]  = 'paint_system = %s';
        $params[] = $paint_system;
    }
    if (!empty($gloss)) {
        $where[]  = 'gloss_level = %s';
        $params[] = $gloss;
    }

    $where_sql = implode(' AND ', $where);

    // 3. Get matching IDs (with pagination)
    $offset    = ($page - 1) * $per_page;
    $query     = $wpdb->prepare(
        "SELECT product_id FROM $table WHERE $where_sql LIMIT %d OFFSET %d",
        array_merge($params, [$per_page, $offset])
    );
    $total_query = $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE $where_sql", $params);

    $product_ids = $wpdb->get_col($query);
    $total       = (int) $wpdb->get_var($total_query);

    // 4. Get option counts for dynamic badge update
    $counts = alkana_get_filter_counts($table, $where_sql, $params);

    // 5. Render product cards HTML
    $html = '';
    if (!empty($product_ids)) {
        $products = get_posts([
            'post_type'      => 'alkana_product',
            'post__in'       => $product_ids,
            'orderby'        => 'post__in',
            'posts_per_page' => $per_page,
        ]);
        foreach ($products as $post) {
            setup_postdata($post);
            ob_start();
            get_template_part('template-parts/product-card');
            $html .= ob_get_clean();
        }
        wp_reset_postdata();
    } else {
        ob_start();
        get_template_part('template-parts/filter-empty-state');
        $html = ob_get_clean();
    }

    wp_send_json_success([
        'html'   => $html,
        'counts' => $counts,
        'total'  => $total,
        'pages'  => ceil($total / $per_page),
    ]);
}

function alkana_get_filter_counts(string $table, string $where_sql, array $params): array {
    // Returns count per surface slug, paint system, gloss level
    // Used by frontend to update option badges and disable 0-count options
    global $wpdb;
    $counts = ['surfaces' => [], 'paint_systems' => [], 'gloss_levels' => []];

    // Surface counts (simplified — count products where each surface slug appears)
    $surfaces = get_terms(['taxonomy' => 'surface_type', 'hide_empty' => false, 'fields' => 'slugs']);
    foreach ($surfaces as $slug) {
        $q = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE $where_sql AND FIND_IN_SET(%s, surface_slugs)",
            array_merge($params, [$slug])
        );
        $counts['surfaces'][$slug] = (int) $wpdb->get_var($q);
    }
    // Similar for paint_system and gloss_level...
    return $counts;
}
```

### JavaScript Filter Controller
```js
// src/scripts/filter.js
const DEBOUNCE = 300;
let debounceTimer;

const state = {
  surfaces: [],
  paint_system: '',
  gloss: '',
  category: '',
  page: 1,
};

function getFilterState() {
  const params = new URLSearchParams(window.location.search);
  state.surfaces     = params.get('surfaces')?.split(',').filter(Boolean) ?? [];
  state.paint_system = params.get('system') ?? '';
  state.gloss        = params.get('gloss')  ?? '';
  state.category     = params.get('cat')    ?? '';
  state.page         = parseInt(params.get('page') ?? '1');
}

async function runFilter() {
  const grid = document.getElementById('product-grid');
  grid.classList.add('loading');

  const res = await fetch(AlkanaConfig.ajaxUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      action:       'alkana_filter',
      nonce:        AlkanaConfig.nonce,
      ...state,
      surfaces:     state.surfaces,  // PHP receives as array
    }),
  });

  const { data } = await res.json();
  grid.innerHTML = data.html;
  grid.classList.remove('loading');
  updateCounts(data.counts);
  updateActiveTags();
  updateURL();
}

function updateURL() {
  const params = new URLSearchParams();
  if (state.surfaces.length)  params.set('surfaces', state.surfaces.join(','));
  if (state.paint_system)     params.set('system',   state.paint_system);
  if (state.gloss)            params.set('gloss',    state.gloss);
  if (state.category)         params.set('cat',      state.category);
  if (state.page > 1)         params.set('page',     state.page);
  history.pushState(null, '', '?' + params.toString());
}

function onFilterChange() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => { state.page = 1; runFilter(); }, DEBOUNCE);
}

// Wire up checkbox/radio listeners
document.querySelectorAll('[data-filter-surface]').forEach(el => {
  el.addEventListener('change', () => {
    state.surfaces = [...document.querySelectorAll('[data-filter-surface]:checked')]
      .map(e => e.value);
    onFilterChange();
  });
});

// Init on page load
getFilterState();
if (state.surfaces.length || state.paint_system || state.gloss || state.category) {
  runFilter();
}
```

### Empty State Partial
```php
<!-- template-parts/filter-empty-state.php -->
<div class="filter-empty-state">
  <p>Không tìm thấy sản phẩm phù hợp với tiêu chí của bạn.</p>
  <a href="#consultation-form" class="btn btn--primary">Nhận tư vấn miễn phí</a>
</div>
```

## Related Code Files
- Create: `inc/ajax/filter-handler.php`
- Create: `src/scripts/filter.js`
- Create: `template-parts/filter-empty-state.php`
- Modify: `template-parts/product-filter-panel.php` (add data-filter-* attributes)
- Modify: `archive-alkana_product.php` (add product-grid container + load filter panel)

## Implementation Steps

1. Register AJAX action hooks in `functions.php` (or autoloaded via `inc/ajax/`).
2. Implement `alkana_filter_handler()` with sanitization + SQL builder.
3. Implement `alkana_get_filter_counts()` for dynamic badge counts.
4. Test PHP handler with Postman/cURL before writing JS.
5. Implement `filter.js` with fetch, state management, debounce.
6. Wire `filter.js` to filter panel checkboxes (data attributes).
7. Implement active tags display and "×" removal.
8. Implement empty state CTA.
9. Implement URL state preservation (History API).
10. Test filter scenarios: single filter, multi-surface OR, cross-group AND, empty state.
11. Test with 50+ products to verify index table performance.

## Todo List
- [ ] Register wp_ajax hooks for `alkana_filter`
- [ ] Implement PHP sanitize + SQL builder
- [ ] Implement dynamic counts per option
- [ ] Return rendered HTML from PHP
- [ ] Test PHP handler on staging via Postman
- [ ] Implement filter.js (fetch, state, debounce)
- [ ] Wire checkboxes with data-filter-* attributes
- [ ] Active filter tags + remove button
- [ ] Empty state template
- [ ] URL state via History API
- [ ] Test all filter combinations
- [ ] Performance test: 50 products → response < 500ms

## Success Criteria
- Filter with 3 active criteria returns results in < 500ms on staging
- OR logic within surface type works correctly
- AND logic across surface + paint_system works correctly
- Empty state shows consultation CTA (not blank grid)
- Active filter tags reflect current state and can be individually removed
- URL updates on filter change (shareable link works)

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| `FIND_IN_SET` slow on large datasets | Low (<500 SKUs) | Monitor query time; add FULLTEXT index if needed |
| Counts SQL too many queries (N+1) | Medium | Batch count query; consider single denormalized count table later |
| iOS Safari fetch compatibility | Low | Fetch API is fully supported iOS 10.3+ |

## Security Considerations
- `check_ajax_referer('alkana_filter', 'nonce')` on every request
- `sanitize_key()` on all incoming filter values
- Prepared statements via `$wpdb->prepare()` — no string interpolation in SQL
- Rate limiting: LiteSpeed/Cloudflare WAF handles repeat AJAX floods

## Next Steps
→ Phase 04: Admin UI polish (filter panel uses taxonomy data set up in admin)
→ Phase 06: Performance tuning (filter response time measurement)
