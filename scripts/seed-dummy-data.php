<?php
/**
 * High-fidelity dummy data seeder for Alkana B2B website preview.
 *
 * Seeds: 1 Hero Banner, 12 Products (alkana_product), 6 Projects (alkana_project).
 * Downloads real images via media_sideload_image for a premium look.
 *
 * Usage:   wp eval-file scripts/seed-dummy-data.php
 * Cleanup: wp post delete $(wp post list --post_type=alkana_product --format=ids) --force
 *          wp post delete $(wp post list --post_type=alkana_project --format=ids) --force
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || die( "Run via WP-CLI: wp eval-file scripts/seed-dummy-data.php\n" );

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/** Sideload an image from URL. Returns attachment ID or 0 on failure. */
function alkana_seed_sideload( string $url, int $post_id, string $desc ): int {
	// download_url + manual import handles URLs without file extensions (e.g. Unsplash).
	$tmp = download_url( $url );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "  Download failed ({$desc}): " . $tmp->get_error_message() );
		return 0;
	}
	$file_array = [
		'name'     => sanitize_file_name( $desc ) . '.jpg',
		'tmp_name' => $tmp,
	];
	$att_id = media_handle_sideload( $file_array, $post_id, $desc );
	if ( is_wp_error( $att_id ) ) {
		wp_delete_file( $tmp );
		WP_CLI::warning( "  Sideload failed ({$desc}): " . $att_id->get_error_message() );
		return 0;
	}
	return (int) $att_id;
}

/** Update ACF field if available, otherwise fall back to post_meta. */
function alkana_seed_set_field( string $key, $value, int $post_id ): void {
	if ( function_exists( 'update_field' ) ) {
		update_field( $key, $value, $post_id );
	} else {
		update_post_meta( $post_id, $key, $value );
	}
}

$acf_active = function_exists( 'update_field' );
if ( ! $acf_active ) {
	WP_CLI::warning( 'ACF Pro not active — using post_meta fallback. Repeater fields and hero_image array format will not work.' );
}

// ── 1. Hero Banner ────────────────────────────────────────────────────────────

WP_CLI::log( "\n── Seeding Hero Banner ──" );

$front_id = (int) get_option( 'page_on_front' );
if ( ! $front_id ) {
	$fp       = get_page_by_path( 'trang-chu' );
	$front_id = $fp ? $fp->ID : 0;
}
if ( ! $front_id ) {
	WP_CLI::error( 'No front page found. Run seed-content.php first.' );
}

$hero_att = alkana_seed_sideload(
	'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1600&h=900&fit=crop',
	$front_id,
	'Alkana Hero — Industrial Steel Structure'
);
if ( $hero_att ) {
	alkana_seed_set_field( 'hero_image', $hero_att, $front_id );
}
alkana_seed_set_field( 'hero_title', 'Giải pháp Sơn Công nghiệp & Chống thấm Toàn diện', $front_id );
alkana_seed_set_field( 'hero_subtitle', 'Đỉnh cao công nghệ bảo vệ bề mặt cho các công trình quy mô lớn, đáp ứng tiêu chuẩn khắt khe nhất.', $front_id );
alkana_seed_set_field( 'hero_cta_label', 'Khám phá Sản phẩm', $front_id );
alkana_seed_set_field( 'hero_cta_url', home_url( '/products/' ), $front_id );

WP_CLI::success( "Hero Banner seeded on front page ID {$front_id}." );

// ── 2. Products ───────────────────────────────────────────────────────────────

$products = require __DIR__ . '/dummy-data/product-data.php';

// Pre-flight: ensure taxonomy terms are seeded.
if ( ! get_term_by( 'slug', 'epoxy-coating', 'product_category' ) ) {
	WP_CLI::error( 'Taxonomy terms not seeded. Run: wp eval-file wp-content/themes/alkana/inc/db/seed-taxonomy-terms.php' );
}

// Idempotency: delete existing dummy products + index rows before re-seeding.
$existing = get_posts( [ 'post_type' => 'alkana_product', 'numberposts' => -1, 'fields' => 'ids' ] );
if ( $existing ) {
	global $wpdb;
	foreach ( $existing as $eid ) { wp_delete_post( $eid, true ); }
	// Clean orphaned index rows (sync hook doesn't fire on delete).
	$wpdb->query( "DELETE i FROM {$wpdb->prefix}alkana_product_index i LEFT JOIN {$wpdb->posts} p ON i.post_id = p.ID WHERE p.ID IS NULL" );
	WP_CLI::log( '  Cleaned ' . count( $existing ) . ' existing products.' );
}

WP_CLI::log( "\n── Seeding " . count( $products ) . " Products ──" );

// Unsplash image IDs for distinct product photos (paint/industrial).
$product_images = [
	'https://images.unsplash.com/photo-1562259929-b4e1fd3aef09?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1585366119957-e9730b6d0f60?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1595814433015-e6f5ce69614e?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1560448205-4d9b3e6bb6db?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1615873968403-89e068629265?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1600585152220-90363fe7e115?w=800&h=600&fit=crop',
	'https://images.unsplash.com/photo-1517581177682-a085bb7ffb15?w=800&h=600&fit=crop',
];

foreach ( $products as $i => $p ) {
	$post_id = wp_insert_post( [
		'post_title'   => $p['title'],
		'post_content' => $p['content'],
		'post_excerpt' => $p['excerpt'],
		'post_type'    => 'alkana_product',
		'post_status'  => 'publish',
	] );

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( "  Failed to create product: {$p['title']}" );
		continue;
	}

	// Thumbnail.
	$img_url = $product_images[ $i ] ?? $product_images[0];
	$att_id  = alkana_seed_sideload( $img_url, $post_id, $p['title'] );
	if ( $att_id ) {
		set_post_thumbnail( $post_id, $att_id );
	}

	// Post meta fields (underscore-prefixed = direct meta, not ACF field objects).
	update_post_meta( $post_id, '_alkana_sku', $p['sku'] );
	update_post_meta( $post_id, '_alkana_name', $p['name_vi'] );
	update_post_meta( $post_id, '_alkana_short_desc', $p['excerpt'] );
	update_post_meta( $post_id, '_alkana_coverage', $p['coverage'] );
	update_post_meta( $post_id, '_alkana_mix_ratio', $p['mix'] );
	update_post_meta( $post_id, '_alkana_thinner', $p['thinner'] );
	update_post_meta( $post_id, '_alkana_layer', $p['layer'] );
	update_post_meta( $post_id, '_alkana_dry_touch', $p['dry_touch'] );
	update_post_meta( $post_id, '_alkana_dry_hard', $p['dry_hard'] );
	update_post_meta( $post_id, '_alkana_dry_recoat', $p['dry_recoat'] );
	update_post_meta( $post_id, '_alkana_featured', $p['featured'] ? 1 : 0 );

	// Taxonomies.
	wp_set_object_terms( $post_id, $p['cat'], 'product_category' );
	wp_set_object_terms( $post_id, $p['surface'], 'surface_type' );
	wp_set_object_terms( $post_id, $p['system'], 'paint_system' );
	wp_set_object_terms( $post_id, $p['gloss'], 'gloss_level' );

	// Sync product index table.
	do_action( 'acf/save_post', $post_id );

	WP_CLI::log( "  ✓ [{$p['sku']}] {$p['title']}" . ( $p['featured'] ? ' ★' : '' ) );
}

WP_CLI::success( count( $products ) . ' products seeded.' );

// ── 3. Projects ───────────────────────────────────────────────────────────────

$projects = require __DIR__ . '/dummy-data/project-data.php';

// Idempotency: delete existing dummy projects.
$existing_proj = get_posts( [ 'post_type' => 'alkana_project', 'numberposts' => -1, 'fields' => 'ids' ] );
if ( $existing_proj ) {
	foreach ( $existing_proj as $eid ) { wp_delete_post( $eid, true ); }
	WP_CLI::log( '  Cleaned ' . count( $existing_proj ) . ' existing projects.' );
}

WP_CLI::log( "\n── Seeding " . count( $projects ) . " Projects ──" );

// Unsplash images: factories, bridges, commercial buildings.
$project_images = [
	'https://images.unsplash.com/photo-1513828583688-c52646db42da?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=1200&h=800&fit=crop',
	'https://images.unsplash.com/photo-1565008447742-97f6f38c985c?w=1200&h=800&fit=crop',
];

foreach ( $projects as $i => $proj ) {
	$post_id = wp_insert_post( [
		'post_title'   => $proj['title'],
		'post_content' => $proj['content'],
		'post_excerpt' => $proj['excerpt'],
		'post_type'    => 'alkana_project',
		'post_status'  => 'publish',
	] );

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( "  Failed to create project: {$proj['title']}" );
		continue;
	}

	// Thumbnail.
	$att_id = alkana_seed_sideload( $project_images[ $i ] ?? $project_images[0], $post_id, $proj['title'] );
	if ( $att_id ) {
		set_post_thumbnail( $post_id, $att_id );
	}

	// ACF Project Details.
	alkana_seed_set_field( 'project_location', $proj['location'], $post_id );
	alkana_seed_set_field( 'project_year', $proj['year'], $post_id );
	alkana_seed_set_field( 'project_area', $proj['area'], $post_id );
	alkana_seed_set_field( 'project_client', $proj['client'], $post_id );

	WP_CLI::log( "  ✓ {$proj['title']} ({$proj['location']}, {$proj['year']})" );
}

WP_CLI::success( count( $projects ) . ' projects seeded.' );

$featured_count = count( array_filter( $products, fn( $p ) => $p['featured'] ) );
WP_CLI::log( "\n✅ Dummy data seeded: 1 Hero Banner, " . count( $products ) . " Products ({$featured_count} featured), " . count( $projects ) . ' Projects.' );
WP_CLI::log( "\n🧹 Cleanup commands:" );
WP_CLI::log( '  wp post delete $(wp post list --post_type=alkana_product --format=ids) --force' );
WP_CLI::log( '  wp post delete $(wp post list --post_type=alkana_project --format=ids) --force' );
