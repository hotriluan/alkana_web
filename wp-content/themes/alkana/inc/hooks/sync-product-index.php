<?php
/**
 * Sync product data to wp_alkana_product_index on ACF save.
 *
 * Triggered after ACF saves post fields (priority 20).
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/save_post', 'alkana_sync_product_index', 20 );

/**
 * Write or update the index row for the saved post.
 *
 * @param int|string $post_id ACF post ID.
 */
function alkana_sync_product_index( $post_id ): void {
	// Only indexed for alkana_product CPT
	if ( get_post_type( (int) $post_id ) !== 'alkana_product' ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	global $wpdb;

	$post = get_post( (int) $post_id );

	// Collect taxonomy term slugs
	$category_slugs = alkana_get_term_slugs( (int) $post_id, 'product_category' );
	$surface_slugs  = alkana_get_term_slugs( (int) $post_id, 'surface_type' );
	$paint_system   = alkana_get_term_slugs( (int) $post_id, 'paint_system' );
	$gloss_level    = alkana_get_term_slugs( (int) $post_id, 'gloss_level' );

	// ACF field: is_featured checkbox (fallback to _alkana_featured post meta)
	$is_featured = (int) (bool) ( get_field( 'is_featured', (int) $post_id ) ?: get_post_meta( (int) $post_id, '_alkana_featured', true ) );

	$wpdb->replace(
		$wpdb->prefix . 'alkana_product_index',
		[
			'post_id'        => (int) $post_id,
			'product_slug'   => $post->post_name,
			'product_name'   => $post->post_title,
			'category_slugs' => $category_slugs,
			'surface_slugs'  => $surface_slugs,
			'paint_system'   => $paint_system,
			'gloss_level'    => $gloss_level,
			'is_featured'    => $is_featured,
		],
		[ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ]
	);
}

/**
 * Return comma-separated term slugs for a given taxonomy.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy name.
 * @return string comma-separated slugs or empty string.
 */
function alkana_get_term_slugs( int $post_id, string $taxonomy ): string {
	$terms = get_the_terms( $post_id, $taxonomy );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	return implode( ',', wp_list_pluck( $terms, 'slug' ) );
}
