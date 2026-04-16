<?php
/**
 * Alkana top-level admin menu.
 * Groups all custom settings pages under one "Alkana" menu entry.
 * CPT submenus (Testimonials) are registered manually here.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

// Redirect Footer & Social submenu to Customizer BEFORE any output is sent.
add_action( 'admin_init', 'alkana_footer_social_maybe_redirect' );

function alkana_footer_social_maybe_redirect(): void {
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ( $_GET['page'] ?? '' ) !== 'alkana-footer-social' ) {
return;
}
if ( ! current_user_can( 'manage_options' ) ) {
wp_die( esc_html__( 'Permission denied.', 'alkana' ) );
}
$url = add_query_arg(
[
'autofocus[section]' => 'alkana_footer_contact',
'return'             => rawurlencode( admin_url( 'admin.php?page=alkana-settings' ) ),
],
admin_url( 'customize.php' )
);
wp_redirect( esc_url_raw( $url ) );
exit;
}

// Priority 5: register before CPT admin_menu processing at priority 10.
add_action( 'admin_menu', 'alkana_register_admin_menu', 5 );

function alkana_register_admin_menu(): void {
// Top-level "Alkana" menu.
add_menu_page(
__( 'Alkana Settings', 'alkana' ),
__( 'Alkana', 'alkana' ),
'manage_options',
'alkana-settings',
'alkana_render_usp_settings_page',
'dashicons-admin-site-alt3',
30
);

// Rename the auto-duplicated parent entry.
add_submenu_page(
'alkana-settings',
__( 'USP Stats', 'alkana' ),
__( 'USP Stats', 'alkana' ),
'manage_options',
'alkana-settings',
'alkana_render_usp_settings_page'
);

// Testimonials list.
add_submenu_page(
'alkana-settings',
__( 'All Testimonials', 'alkana' ),
__( 'Testimonials', 'alkana' ),
'edit_posts',
'edit.php?post_type=alkana_testimonial',
''
);

// Add New Testimonial.
add_submenu_page(
'alkana-settings',
__( 'Add New Testimonial', 'alkana' ),
__( '+ Add New', 'alkana' ),
'edit_posts',
'post-new.php?post_type=alkana_testimonial',
''
);

// Hero Slider — manages homepage banner slides.
add_submenu_page(
'alkana-settings',
__( 'Hero Slider', 'alkana' ),
__( 'Hero Slider', 'alkana' ),
'manage_options',
'alkana-hero-slider',
'alkana_render_hero_slider_page'
);

// About Page — manages timeline, factory, and team sections.
add_submenu_page(
'alkana-settings',
__( 'About Page', 'alkana' ),
__( 'About Page', 'alkana' ),
'manage_options',
'alkana-about',
'alkana_render_about_settings_page'
);

// Footer & Social — page slug triggers admin_init redirect above.
add_submenu_page(
'alkana-settings',
__( 'Footer & Social', 'alkana' ),
__( 'Footer & Social', 'alkana' ),
'manage_options',
'alkana-footer-social',
'alkana_render_footer_social_page'
);
}

// Navigation Menus — inject direct link to WP native nav-menus.php.
add_action( 'admin_menu', function (): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	global $submenu;
	$submenu['alkana-settings'][] = [
		__( 'Navigation Menus', 'alkana' ),
		'edit_theme_options',
		admin_url( 'nav-menus.php' ),
	];
}, 20 );

/**
 * Fallback render for Footer & Social (admin_init redirect fires first).
 * Shows a JS redirect + plain link as safety net.
 */
function alkana_render_footer_social_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'alkana' ) );
	}
	$url = add_query_arg(
		[ 'autofocus[section]' => 'alkana_footer_contact' ],
		admin_url( 'customize.php' )
	);
	?>
	<div class="wrap">
	<h1><?php esc_html_e( 'Footer & Social', 'alkana' ); ?></h1>
	<p><a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Open Footer & Social in Customizer', 'alkana' ); ?></a></p>
	<script>window.location.href = '<?php echo esc_js( $url ); ?>';</script>
	</div>
	<?php
}

// Menu order: Dashboard → Products → Projects → Posts → Pages ----------------
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'alkana_admin_menu_order' );

/**
 * Enforce desired top-level menu order.
 * Items not listed are appended after the desired set.
 *
 * @param array $order Current menu order from WordPress.
 * @return array
 */
function alkana_admin_menu_order( array $order ): array {
	$desired = [
		'index.php',                             // Dashboard
		'edit.php?post_type=alkana_product',     // Products (+ category submenus)
		'edit.php?post_type=alkana_project',     // Projects
		'edit.php',                              // Posts
		'edit.php?post_type=page',               // Pages
	];

	// Only include desired items that actually exist in the current menu.
	$present  = array_filter( $desired, static fn( $slug ) => in_array( $slug, $order, true ) );
	$remaining = array_values( array_diff( $order, $desired ) );

	return array_merge( array_values( $present ), $remaining );
}
