<?php
/**
 * LCP (Largest Contentful Paint) preload hints.
 *
 * Emits a <link rel="preload" as="image"> in <head> for the hero banner
 * image on pages that use it. This preload allows the browser to fetch
 * the hero image in parallel with HTML parsing — reducing LCP time.
 *
 * Only active on: front page, page templates that include hero-banner.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'alkana_hero_lcp_preload', 1 );

/**
 * Emit <link rel="preload" as="image"> for the ACF hero image.
 *
 * Priority 1 = runs before all other wp_head output so preload
 * is as early as possible in <head>.
 */
function alkana_hero_lcp_preload(): void {
	// Only emit on pages that display the hero banner
	if ( ! is_front_page() && ! is_page() ) {
		return;
	}

	$hero_image = get_field( 'hero_image' );

	if ( ! $hero_image || empty( $hero_image['url'] ) ) {
		return;
	}

	$img_url = esc_url( $hero_image['url'] );

	// Prefer the 'full' size URL but also attempt to emit a srcset so the
	// browser can choose the best resolution for preload (modern browsers).
	$img_id     = $hero_image['ID'] ?? 0;
	$srcset_val = '';
	$sizes_val  = '';

	if ( $img_id ) {
		$srcset_raw = wp_get_attachment_image_srcset( $img_id, 'full' );
		if ( $srcset_raw ) {
			$srcset_val = ' imagesrcset="' . esc_attr( $srcset_raw ) . '"';
			$sizes_val  = ' imagesizes="100vw"';
		}
	}

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high"%s%s>' . "\n",
		$img_url,
		$srcset_val, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$sizes_val   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
