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
	
	// Remove emoji scripts
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	
	// Remove REST API link
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
	
	// Remove WP version
	remove_action( 'wp_head', 'wp_generator' );
	
	// Remove wlwmanifest + RSD links
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	
	// Remove shortlink
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
}

/**
 * Google Tag Manager integration.
 * Outputs GTM snippet in <head> when option is configured.
 */
add_action( 'wp_head', function(): void {
	$gtm_id = get_option( 'alkana_gtm_id', '' );
	if ( $gtm_id ) {
		?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_attr( $gtm_id ); ?>');</script>
		<!-- End Google Tag Manager -->
		<?php
	}
}, 1 );

/**
 * Google Tag Manager noscript fallback.
 * Outputs GTM iframe in <body> when option is configured.
 */
add_action( 'wp_body_open', function(): void {
	$gtm_id = get_option( 'alkana_gtm_id', '' );
	if ( $gtm_id ) {
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}
} );
