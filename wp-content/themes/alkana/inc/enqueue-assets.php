<?php
/**
 * Asset enqueueing via Vite manifest.json.
 * Dequeues WordPress bloat on front-end.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts',    'alkana_enqueue_assets' );
add_action( 'admin_enqueue_scripts', 'alkana_admin_enqueue_assets' );
add_action( 'wp_enqueue_scripts',    'alkana_dequeue_bloat', 100 );
add_action( 'wp_head',               'alkana_preconnect_fonts', 1 );

/**
 * Emit preconnect hints for Google Fonts before any enqueued styles.
 */
function alkana_preconnect_fonts(): void {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}

/**
 * Front-end: enqueue compiled Vite assets by reading dist/manifest.json.
 */
function alkana_enqueue_assets(): void {
	$manifest_path = get_template_directory() . '/dist/.vite/manifest.json';

	if ( ! file_exists( $manifest_path ) ) {
		// Dev fallback — warn only in WP_DEBUG mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( 'Alkana: dist/manifest.json not found — run npm run build.', E_USER_WARNING );
		}
		return;
	}

	$manifest = (array) json_decode( (string) file_get_contents( $manifest_path ), true );

	// CSS
	$css_key  = 'src/styles/app.css';
	$css_file = $manifest[ $css_key ]['file'] ?? null;
	if ( $css_file ) {
		wp_enqueue_style(
			'alkana-app',
			get_template_directory_uri() . '/dist/' . $css_file,
			[],
			null
		);
	}

	// Google Fonts (Montserrat + Inter) — depends on preconnect hints from wp_head
	wp_enqueue_style(
		'alkana-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Inter:wght@400;500;600&display=swap',
		[],
		null
	);

	// JS
	$js_key  = 'src/scripts/app.js';
	$js_file = $manifest[ $js_key ]['file'] ?? null;
	if ( $js_file ) {
		wp_enqueue_script(
			'alkana-app',
			get_template_directory_uri() . '/dist/' . $js_file,
			[],
			null,
			true
		);

		// Runtime config for AJAX filter
		wp_localize_script( 'alkana-app', 'AlkanaConfig', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'alkana_filter' ),
			'version' => ALKANA_VERSION,
		] );
	}
}

/**
 * Admin-only assets (minified ACF override styles, dashboard widget CSS).
 */
function alkana_admin_enqueue_assets(): void {
	// Reserved for future admin stylesheet additions
}

/**
 * Remove unused/bloat scripts and styles on the front-end.
 */
function alkana_dequeue_bloat(): void {
	// Remove block library CSS (Tailwind handles all styling)
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );

	// Remove wp-embed script (no oEmbed needed)
	wp_dequeue_script( 'wp-embed' );

	// Dequeue Gutenberg global styles
	wp_dequeue_style( 'global-styles' );

	// Remove classic-theme-styles (Twenty Twenty-One compat shim — not needed with Tailwind)
	wp_dequeue_style( 'classic-theme-styles' );

	// Remove jQuery from front-end (all JS is vanilla)
	wp_dequeue_script( 'jquery' );
	wp_deregister_script( 'jquery' );
}
