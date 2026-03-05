<?php
/**
 * Alkana Theme — functions.php
 * Bootstrap: load all modules from inc/ directory.
 */

defined('ABSPATH') || exit;

define('ALKANA_VERSION', '1.0.0');
define('ALKANA_DIR', get_template_directory());
define('ALKANA_URI', get_template_directory_uri());

// ── ACF compatibility shim (no-op when ACF plugin is active) ─────────────────
require_once ALKANA_DIR . '/inc/compat/acf-shim.php';

// ── Core modules (always loaded) ──────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/theme-setup.php';
require_once ALKANA_DIR . '/inc/enqueue-assets.php';

// ── Content types ──────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/cpt-product.php';
require_once ALKANA_DIR . '/inc/cpt-project.php';
require_once ALKANA_DIR . '/inc/taxonomies.php';

// ── Database ───────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/db/create-product-index-table.php';

// ── Hooks ──────────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/hooks/sync-product-index.php';

// ── AJAX endpoints ─────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/ajax/filter-handler.php';

// ── Performance ────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/performance/lcp-preload.php';

// ── Admin (only in admin context) ──────────────────────────────────────────────
if (is_admin()) {
    require_once ALKANA_DIR . '/inc/admin/roles.php';
    require_once ALKANA_DIR . '/inc/admin/clean-menu.php';
    require_once ALKANA_DIR . '/inc/admin/acf-role-restrictions.php';
    require_once ALKANA_DIR . '/inc/admin/dashboard.php';
}
