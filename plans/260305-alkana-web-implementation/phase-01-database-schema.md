# Phase 01 — Database Schema & CPT/ACF Setup

## Context Links
- Plan: [plan.md](plan.md)
- Blueprint section: §4 Database Schema, §5 Admin Dashboard
- Brainstorm risk §4.1: product_filter_index table

## Overview
| Item | Detail |
|---|---|
| **Priority** | Critical |
| **Status** | ✅ complete |
| **Timeline** | Weeks 1–2 |
| **Depends on** | Phase 00 (staging environment ready) |
| **Blocks** | Phase 02, 03, 04 |

## Key Insights
- ACF Pro Repeater fields required for Color Variants and Packaging sizes — confirm license before coding.
- MySQL JSON column (`surface_types`) requires MySQL 8.0. On 5.7, use `TEXT` + PHP `json_decode()`.
- The `wp_alkana_product_index` sync hook must fire on ACF `save_post` — not WP default `save_post` which fires before ACF saves custom fields.
- Taxonomies for Category, Surface, Paint System = standard WP taxonomies for URL/SEO benefit. NOT ACF select fields.

## Requirements

### Functional
- Custom Post Type: `alkana_product` (public, has archive, REST enabled).
- Custom Taxonomies: `product_category`, `surface_type`, `paint_system`, `gloss_level`.
- ACF Field Groups: Identification, Technical Specs, Variants, Resources, Relations.
- `wp_alkana_product_index` table created via plugin activation hook.
- Sync hook: on ACF save → update product index row.
- WordPress Admin: CPT registered with correct labels in both EN and VI.

### Non-Functional
- All taxonomy slugs in English (for URL SEO): `/products/wood-coating/`, not `/san-pham/son-go/`.
- ACF fields use `_alkana_` prefix to avoid postmeta collisions.
- Index table sync must be transactional (no partial writes).

## Architecture

### CPT Registration
```php
// inc/cpt-product.php
register_post_type('alkana_product', [
  'labels'      => [...],
  'public'      => true,
  'has_archive' => true,
  'rewrite'     => ['slug' => 'products'],
  'supports'    => ['title', 'editor', 'thumbnail', 'excerpt'],
  'show_in_rest'=> true,
]);
```

### Taxonomies
```php
// inc/taxonomies.php
$taxonomies = [
  'product_category' => ['slug' => 'product-category'],
  'surface_type'     => ['slug' => 'surface'],
  'paint_system'     => ['slug' => 'paint-system'],
  'gloss_level'      => ['slug' => 'gloss'],
];
// register_taxonomy() for each, attached to 'alkana_product'
```

### ACF Field Groups

**Group 1: Identification**
| Field | Type | Key |
|---|---|---|
| SKU | text | `_alkana_sku` |
| Commercial Name | text | `_alkana_name` |
| Short Description | textarea | `_alkana_short_desc` |
| Function Tags | taxonomy (tag-like) | via WordPress tags |

**Group 2: Technical Specifications**
| Field | Type | Key |
|---|---|---|
| Applied Surface | checkbox (from surface_type taxonomy) | `_alkana_surfaces` |
| Theoretical Coverage | text + unit | `_alkana_coverage` |
| Mixing Ratio | text | `_alkana_mix_ratio` |
| Compatible Thinner | text | `_alkana_thinner` |
| Touch Dry Time | text | `_alkana_dry_touch` |
| Hard Dry Time | text | `_alkana_dry_hard` |
| Recoat Time | text | `_alkana_dry_recoat` |

**Group 3: Variants (ACF Repeater)**
| Sub-field | Type |
|---|---|
| Color Name | text |
| Hex Code | color_picker |
| Packaging Size | text |
| Gloss Level | select (link to gloss_level taxonomy) |

**Group 4: Resources**
| Field | Type | Key |
|---|---|---|
| TDS PDF | file (PDF only) | `_alkana_tds` |
| MSDS PDF | file (PDF only) | `_alkana_msds` |
| Certifications | repeater → file | `_alkana_certs` |

**Group 5: Relations**
| Field | Type | Key |
|---|---|---|
| Cross-sell Products | relationship (alkana_product) | `_alkana_crosssell` |
| Related Projects | relationship (alkana_project CPT) | `_alkana_projects` |

### Product Filter Index Table

```sql
-- inc/db/create-product-index-table.php (run on plugin/theme activation)
CREATE TABLE IF NOT EXISTS {prefix}alkana_product_index (
  product_id      BIGINT(20)   NOT NULL PRIMARY KEY,
  category_slugs  TEXT         NOT NULL DEFAULT '',   -- comma-separated, MySQL 5.7 compat
  surface_slugs   TEXT         NOT NULL DEFAULT '',   -- comma-separated
  paint_system    VARCHAR(100) NOT NULL DEFAULT '',
  gloss_level     VARCHAR(50)  NOT NULL DEFAULT '',
  is_featured     TINYINT(1)   NOT NULL DEFAULT 0,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_paint_system (paint_system),
  INDEX idx_gloss_level  (gloss_level),
  INDEX idx_is_featured  (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
> Note: Use TEXT for slugs (comma-separated) for MySQL 5.7 compatibility. Parse in PHP with `explode(',', ...)`.

### Sync Hook (ACF → Index Table)
```php
// inc/hooks/sync-product-index.php
add_action('acf/save_post', function($post_id) {
  if (get_post_type($post_id) !== 'alkana_product') return;
  
  $categories = wp_get_post_terms($post_id, 'product_category', ['fields' => 'slugs']);
  $surfaces   = wp_get_post_terms($post_id, 'surface_type',     ['fields' => 'slugs']);
  $systems    = wp_get_post_terms($post_id, 'paint_system',     ['fields' => 'slugs']);
  $gloss      = wp_get_post_terms($post_id, 'gloss_level',      ['fields' => 'slugs']);

  global $wpdb;
  $wpdb->replace(
    $wpdb->prefix . 'alkana_product_index',
    [
      'product_id'    => $post_id,
      'category_slugs'=> implode(',', $categories),
      'surface_slugs' => implode(',', $surfaces),
      'paint_system'  => $systems[0] ?? '',
      'gloss_level'   => $gloss[0]   ?? '',
      'is_featured'   => get_field('_alkana_featured', $post_id) ? 1 : 0,
    ],
    ['%d', '%s', '%s', '%s', '%s', '%d']
  );
}, 20); // priority 20 = after ACF saves fields
```

## Related Code Files
- Create: `inc/cpt-product.php`
- Create: `inc/cpt-project.php`
- Create: `inc/taxonomies.php`
- Create: `inc/db/create-product-index-table.php`
- Create: `inc/hooks/sync-product-index.php`
- Create: `acf-json/` directory (ACF local JSON for version control)

## Implementation Steps

1. Register `alkana_product` CPT in `inc/cpt-product.php`, load from `functions.php`.
2. Register `alkana_project` CPT (for portfolio) in `inc/cpt-project.php`.
3. Register all 4 taxonomies in `inc/taxonomies.php`.
4. Flush rewrite rules: Settings → Permalinks → Save.
5. Create ACF Field Groups via ACF UI → export to `acf-json/` for git tracking.
6. Create product index table via `register_activation_hook` or `after_switch_theme`.
7. Install sync hook `inc/hooks/sync-product-index.php`.
8. Test: Add 1 sample product → verify row appears in `wp_alkana_product_index`.
9. Populate taxonomy terms (product categories, surface types, paint systems, gloss levels) with real Alkana data.
10. Export ACF field groups to JSON and commit to git.

## Todo List
- [x] Register `alkana_product` CPT
- [x] Register `alkana_project` CPT
- [x] Register 4 taxonomies
- [ ] Test rewrite rules (permalink `/products/` works) — requires live WP install
- [x] Create ACF field groups (7 JSON files in `acf-json/`)
- [x] Enable ACF local JSON sync to `acf-json/` (filters in `inc/theme-setup.php`)
- [x] Create `wp_alkana_product_index` table
- [x] Implement `sync-product-index.php` hook
- [ ] Test: save product → index row created — requires live WP install
- [x] Populate taxonomy terms — seeder at `inc/db/seed-taxonomy-terms.php`
- [x] Commit `acf-json/` to git

## Success Criteria
- `/products/` archive URL returns 200 with custom theme template
- ACF fields save and retrieve correctly for a sample product
- `wp_alkana_product_index` row is created/updated on every product save
- All taxonomy terms populated matching Alkana product catalog

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| ACF Pro unavailable | Low | Code custom repeater fallback using `wp_postmeta` with numbered keys |
| MySQL 5.7 JSON unsupported | Medium | Use TEXT comma-separated (already in schema above) |
| ACF save_post fires before WP taxonomies saved | Low | Use priority 20 on `acf/save_post` |

## Security Considerations
- Sanitize all ACF input using ACF field type enforcement (select, checkbox = no free text injection)
- PDF-only validation on TDS/MSDS upload fields via ACF `mime_types` restriction

## Next Steps
→ Phase 02: Custom Theme + Vite Pipeline
→ Phase 03: AJAX Faceted Filter (can start in parallel with Phase 02)
→ Phase 05: Data Migration (database schema ready → scraping can begin mapping)
