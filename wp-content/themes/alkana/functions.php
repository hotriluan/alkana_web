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

// ── Helpers ────────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/helper-contact-url.php';
require_once ALKANA_DIR . '/inc/class-alkana-mega-menu-walker.php';

// ── Content types ──────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/cpt-product.php';
require_once ALKANA_DIR . '/inc/cpt-project.php';
require_once ALKANA_DIR . '/inc/cpt-job.php';
require_once ALKANA_DIR . '/inc/cpt-application.php';
require_once ALKANA_DIR . '/inc/cpt-testimonial.php';
require_once ALKANA_DIR . '/inc/taxonomies.php';

// ── Customizer ─────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/customizer.php';

// ── Meta boxes ─────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/product-gallery-metabox.php';
require_once ALKANA_DIR . '/inc/project-gallery-metabox.php';
require_once ALKANA_DIR . '/inc/product-specs-metabox.php';
require_once ALKANA_DIR . '/inc/product-variants-metabox.php';
require_once ALKANA_DIR . '/inc/product-certs-metabox.php';

// ── Database ───────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/db/create-product-index-table.php';

// ── Hooks ──────────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/hooks/sync-product-index.php';

// ── AJAX endpoints ─────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/ajax/filter-handler.php';
require_once ALKANA_DIR . '/inc/ajax/application-handler.php';
require_once ALKANA_DIR . '/inc/ajax/contact-handler.php';
require_once ALKANA_DIR . '/inc/ajax/newsletter-handler.php';
require_once ALKANA_DIR . '/inc/ajax/search-handler.php';
require_once ALKANA_DIR . '/inc/ajax/paint-builder-handler.php';
require_once ALKANA_DIR . '/inc/ajax/inline-edit-handler.php';
// ── Search modal — injected at body root via wp_footer to avoid stacking context
// trap caused by position:sticky + backdrop-filter on <header>.
add_action( 'wp_footer', static function (): void {
	get_template_part( 'template-parts/search-modal' );
}, 5 );

// ── Performance ────────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/performance/lcp-preload.php';

// ── SEO & Redirects ────────────────────────────────────────────────────────────
require_once ALKANA_DIR . '/inc/seo/redirects.php';
require_once ALKANA_DIR . '/inc/seo/sitemap.php';

// ── Settings data helpers (must be available on front-end too) ─────────────────
// alkana_get_usp_settings() is called by template-parts/usp-section.php
require_once ALKANA_DIR . '/inc/admin/usp-settings.php';
// alkana_get_hero_slides() + front-end Swiper enqueue
require_once ALKANA_DIR . '/inc/admin/hero-slider-settings.php';
// alkana_get_about_settings() is called by templates/page-about.php
require_once ALKANA_DIR . '/inc/admin/about-settings.php';

// ── Admin (only in admin context) ──────────────────────────────────────────────
// ── Login branding (fires on wp-login.php — outside is_admin()) ──────────────
require_once ALKANA_DIR . '/inc/admin/login-brand.php';

if (is_admin()) {
    require_once ALKANA_DIR . '/inc/admin/roles.php';
    require_once ALKANA_DIR . '/inc/admin/admin-menu.php';
    require_once ALKANA_DIR . '/inc/admin/clean-menu.php';
    require_once ALKANA_DIR . '/inc/admin/acf-role-restrictions.php';
    require_once ALKANA_DIR . '/inc/admin/dashboard.php';
    require_once ALKANA_DIR . '/inc/admin/application-columns.php';
    require_once ALKANA_DIR . '/inc/admin/application-meta-box.php';
    require_once ALKANA_DIR . '/inc/admin/product-meta-box.php';
    require_once ALKANA_DIR . '/inc/admin/testimonial-meta-box.php';
    require_once ALKANA_DIR . '/inc/admin/admin-theme.php';
    require_once ALKANA_DIR . '/inc/admin/list-tables.php';
    require_once ALKANA_DIR . '/inc/admin/command-palette.php';
    require_once ALKANA_DIR . '/inc/admin/focus-mode.php';
}

