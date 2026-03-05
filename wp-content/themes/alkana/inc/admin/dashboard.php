<?php
/**
 * Custom admin dashboard widget for Alkana quick links.
 * Removes default WordPress dashboard clutter for non-admins.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_dashboard_setup', 'alkana_dashboard_setup' );

function alkana_dashboard_setup(): void {
	// Add Alkana quick-links widget
	wp_add_dashboard_widget(
		'alkana_quick_links',
		__( 'Alkana CMS – Quick Links', 'alkana' ),
		'alkana_render_quick_links_widget'
	);

	// Remove default WP widgets for non-admins
	if ( current_user_can( 'administrator' ) ) {
		return;
	}

	$remove_widgets = [
		'dashboard_quick_press',
		'dashboard_right_now',
		'dashboard_activity',
		'dashboard_primary',
		'dashboard_secondary',
		'dashboard_site_health',
		'dashboard_php_nag',
	];

	foreach ( $remove_widgets as $widget ) {
		remove_meta_box( $widget, 'dashboard', 'normal' );
		remove_meta_box( $widget, 'dashboard', 'side' );
	}
}

/**
 * Render the Alkana quick-links dashboard widget.
 */
function alkana_render_quick_links_widget(): void {
	$links = [
		[
			'label' => __( 'Add New Product', 'alkana' ),
			'url'   => admin_url( 'post-new.php?post_type=alkana_product' ),
			'icon'  => 'dashicons-products',
		],
		[
			'label' => __( 'All Products', 'alkana' ),
			'url'   => admin_url( 'edit.php?post_type=alkana_product' ),
			'icon'  => 'dashicons-list-view',
		],
		[
			'label' => __( 'Add New Project', 'alkana' ),
			'url'   => admin_url( 'post-new.php?post_type=alkana_project' ),
			'icon'  => 'dashicons-portfolio',
		],
		[
			'label' => __( 'Media Library', 'alkana' ),
			'url'   => admin_url( 'upload.php' ),
			'icon'  => 'dashicons-format-image',
		],
	];

	echo '<ul style="margin:0;padding:0;list-style:none;">';
	foreach ( $links as $link ) {
		printf(
			'<li style="margin:6px 0;"><span class="dashicons %s" style="vertical-align:middle;margin-right:6px;"></span><a href="%s">%s</a></li>',
			esc_attr( $link['icon'] ),
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
}
