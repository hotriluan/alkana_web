<?php
/**
 * seed-content.php
 *
 * One-time WP-CLI helper to:
 *  1. Create the Primary Navigation menu and assign it to the 'primary' slot.
 *  2. Seed ACF Hero Banner fields on the static front page.
 *
 * Usage:
 *   wp --path=/path/to/wordpress eval-file seed-content.php
 *
 * Safe to re-run: each step checks before creating/overwriting.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || die( "Run via WP-CLI: wp eval-file seed-content.php\n" );

// ── 1. Navigation Menu ────────────────────────────────────────────────────────

$menu_name     = 'Main Menu';
$menu_location = 'primary';

$existing_menu = wp_get_nav_menu_object( $menu_name );

if ( $existing_menu ) {
	WP_CLI::log( "Menu '{$menu_name}' already exists (ID: {$existing_menu->term_id}). Skipping creation." );
	$menu_id = (int) $existing_menu->term_id;
} else {
	$menu_id = wp_create_nav_menu( $menu_name );

	if ( is_wp_error( $menu_id ) ) {
		WP_CLI::error( 'Failed to create menu: ' . $menu_id->get_error_message() );
	}

	WP_CLI::success( "Created menu '{$menu_name}' (ID: {$menu_id})." );
}

// Menu items: [ label => url ]
$menu_items = [
	'Trang chủ' => home_url( '/' ),
	'Sản phẩm'  => home_url( '/products/' ),
	'Dự án'     => home_url( '/projects/' ),
	'Liên hệ'   => home_url( '/contact/' ),
];

foreach ( $menu_items as $label => $url ) {
	// Check if item already exists to avoid duplicates on re-run
	$existing_items = wp_get_nav_menu_items( $menu_id );
	$already_added  = false;

	if ( $existing_items ) {
		foreach ( $existing_items as $item ) {
			if ( $item->title === $label ) {
				$already_added = true;
				break;
			}
		}
	}

	if ( $already_added ) {
		WP_CLI::log( "  Item '{$label}' already in menu. Skipping." );
		continue;
	}

	$item_id = wp_update_nav_menu_item( $menu_id, 0, [
		'menu-item-title'  => $label,
		'menu-item-url'    => $url,
		'menu-item-type'   => 'custom',
		'menu-item-status' => 'publish',
	] );

	if ( is_wp_error( $item_id ) ) {
		WP_CLI::warning( "  Failed to add item '{$label}': " . $item_id->get_error_message() );
	} else {
		WP_CLI::log( "  Added item '{$label}' → {$url}" );
	}
}

// Assign menu to the 'primary' theme location
$locations                  = get_theme_mod( 'nav_menu_locations', [] );
$locations[ $menu_location ] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( "Menu assigned to '{$menu_location}' theme location." );

// ── 2. ACF Hero Banner Fields ─────────────────────────────────────────────────
// The theme's ACF shim (inc/compat/acf-shim.php) reads post meta via
// get_post_meta(), so we seed directly via update_post_meta() — works with
// or without ACF active.

// Resolve the static front page ID (falls back to page with slug 'trang-chu')
$front_page_id = (int) get_option( 'page_on_front' );

if ( ! $front_page_id ) {
	$fp            = get_page_by_path( 'trang-chu' );
	$front_page_id = $fp ? $fp->ID : 0;
}

if ( ! $front_page_id ) {
	WP_CLI::log( 'No static front page found. Creating one...' );

	$front_page_id = wp_insert_post( [
		'post_title'   => 'Trang chủ',
		'post_name'    => 'trang-chu',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '',
	] );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id );

	WP_CLI::success( "Created and set front page (ID: {$front_page_id})." );
} else {
	WP_CLI::log( "Front page ID: {$front_page_id}" );
}

$acf_fields = [
	'hero_title'     => 'Giải pháp Sơn Công Nghiệp & Chống Thấm Toàn Diện',
	'hero_subtitle'  => 'Bảo vệ tối đa công trình của bạn với công nghệ sơn tiên tiến đạt chuẩn quốc tế từ Alkana Coating.',
	'hero_cta_label' => 'Khám phá Sản phẩm',
	'hero_cta_url'   => home_url( '/products/' ),
];

foreach ( $acf_fields as $meta_key => $value ) {
	update_post_meta( $front_page_id, $meta_key, $value );
	$stored = get_post_meta( $front_page_id, $meta_key, true );

	if ( $stored === $value ) {
		WP_CLI::success( "  [{$meta_key}] saved." );
	} else {
		WP_CLI::warning( "  [{$meta_key}] unexpected stored value: " . print_r( $stored, true ) );
	}
}

WP_CLI::success( 'Hero Banner post meta seeded for front page ID ' . $front_page_id . '.' );

WP_CLI::success( '✅ Seed complete. Refresh your browser.' );
