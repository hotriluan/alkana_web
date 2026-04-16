<?php
/**
 * Global admin visual branding for Alkana CMS.
 * Enqueues admin stylesheet (Inter font, purple sidebar, brand accents).
 * Loads Chart.js CDN on the dashboard page only.
 * Suppresses notices for non-administrator roles via CSS only.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_enqueue_scripts', 'alkana_admin_theme_styles' );
add_action( 'admin_head',            'alkana_admin_suppress_notices' );

/**
 * Enqueue admin CSS, Inter font, and dashboard-specific Chart.js.
 *
 * @param string $hook Current admin page hook suffix.
 */
function alkana_admin_theme_styles( string $hook ): void {
	wp_enqueue_style(
		'alkana-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'alkana-admin',
		ALKANA_URI . '/inc/admin/admin-style.css',
		[ 'alkana-admin-fonts' ],
		ALKANA_VERSION
	);

	// Chart.js — load only on the dashboard page.
	if ( 'index.php' === $hook ) {
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			[],
			'4.4.0',
			true
		);
	}
}

/**
 * Hide admin notices and update nags for non-administrator roles.
 * CSS-only approach — preserves DOM accessibility for screen readers.
 */
function alkana_admin_suppress_notices(): void {
	if ( current_user_can( 'administrator' ) ) {
		return;
	}

	echo '<style>'
		. '#wpcontent .notice,'
		. '#wpcontent .notice-info,'
		. '#wpcontent .notice-success,'
		. '#wpcontent .notice-warning,'
		. '#wpcontent .notice-error,'
		. '.update-nag,'
		. '.updated,'
		. '.is-dismissible{'
		. 'display:none!important;'
		. '}'
		. '</style>' . "\n";
}
