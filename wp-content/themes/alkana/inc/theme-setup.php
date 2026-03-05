<?php
/**
 * Theme setup: add_theme_support, nav menus, image sizes.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'alkana_theme_setup' );

function alkana_theme_setup(): void {
	// --- Language ---
	load_theme_textdomain( 'alkana', get_template_directory() . '/languages' );

	// --- Core features ---
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );

	// Remove block editor styles from front-end (Tailwind handles all)
	remove_theme_support( 'core-block-patterns' );

	// --- Navigation menus ---
	register_nav_menus( [
		'primary'  => __( 'Primary Navigation', 'alkana' ),
		'footer'   => __( 'Footer Navigation', 'alkana' ),
		'mobile'   => __( 'Mobile Navigation', 'alkana' ),
	] );

	// --- Image sizes ---
	// Product card thumbnail (2:1 landscape)
	add_image_size( 'alkana-product-card', 480, 240, true );
	// Product detail hero (16:9)
	add_image_size( 'alkana-product-hero', 960, 540, true );
	// Project card (3:2)
	add_image_size( 'alkana-project-card', 480, 320, true );
	// Team / person portrait
	add_image_size( 'alkana-portrait', 300, 300, true );
}

// ── ACF Local JSON ────────────────────────────────────────────────────────────

/**
 * Tell ACF where to save field group JSON files.
 * Enables version-controlling ACF schema in git.
 */
add_filter( 'acf/settings/save_json', static function (): string {
	return get_template_directory() . '/acf-json';
} );

/**
 * Tell ACF where to load field group JSON files from.
 *
 * @param string[] $paths Existing load paths.
 * @return string[]
 */
add_filter( 'acf/settings/load_json', static function ( array $paths ): array {
	$paths[] = get_template_directory() . '/acf-json';
	return $paths;
} );
