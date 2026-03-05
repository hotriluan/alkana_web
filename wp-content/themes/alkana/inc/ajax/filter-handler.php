<?php
/**
 * AJAX handler for product faceted filter.
 *
 * Action: alkana_filter_products (public + logged-in)
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_filter_products',        'alkana_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_alkana_filter_products', 'alkana_ajax_filter_products' );

function alkana_ajax_filter_products(): void {
	// Verify nonce
	if ( ! check_ajax_referer( 'alkana_filter', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => 'Invalid nonce.' ], 403 );
	}

	global $wpdb;

	// ── Sanitize inputs ───────────────────────────────────────────────────────
	$category     = array_map( 'sanitize_key', (array) ( $_POST['category']     ?? [] ) );
	$surface      = array_map( 'sanitize_key', (array) ( $_POST['surface']      ?? [] ) );
	$paint_system = array_map( 'sanitize_key', (array) ( $_POST['paint_system'] ?? [] ) );
	$gloss_level  = array_map( 'sanitize_key', (array) ( $_POST['gloss_level']  ?? [] ) );
	$is_featured  = (int) (bool) ( $_POST['is_featured'] ?? 0 );

	$table = $wpdb->prefix . 'alkana_product_index';

	// ── Build WHERE clauses ───────────────────────────────────────────────────
	$where  = [];
	$values = [];

	if ( ! empty( $category ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $category ), '%s' ) );
		$slug_likes   = alkana_build_find_in_set( 'category_slugs', $category );
		$where[]      = '(' . implode( ' OR ', $slug_likes['clauses'] ) . ')';
		$values       = array_merge( $values, $slug_likes['values'] );
	}

	if ( ! empty( $surface ) ) {
		$slug_likes = alkana_build_find_in_set( 'surface_slugs', $surface );
		$where[]    = '(' . implode( ' OR ', $slug_likes['clauses'] ) . ')';
		$values     = array_merge( $values, $slug_likes['values'] );
	}

	if ( ! empty( $paint_system ) ) {
		$slug_likes = alkana_build_find_in_set( 'paint_system', $paint_system );
		$where[]    = '(' . implode( ' OR ', $slug_likes['clauses'] ) . ')';
		$values     = array_merge( $values, $slug_likes['values'] );
	}

	if ( ! empty( $gloss_level ) ) {
		$slug_likes = alkana_build_find_in_set( 'gloss_level', $gloss_level );
		$where[]    = '(' . implode( ' OR ', $slug_likes['clauses'] ) . ')';
		$values     = array_merge( $values, $slug_likes['values'] );
	}

	if ( $is_featured ) {
		$where[] = 'is_featured = 1';
	}

	$sql = "SELECT post_id FROM {$table}";
	if ( ! empty( $where ) ) {
		$sql .= ' WHERE ' . implode( ' AND ', $where );
	}
	$sql .= ' ORDER BY is_featured DESC, post_id DESC';

	if ( ! empty( $values ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$post_ids = $wpdb->get_col( $wpdb->prepare( $sql, $values ) );
	} else {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$post_ids = $wpdb->get_col( $sql );
	}

	// ── Render cards ─────────────────────────────────────────────────────────
	ob_start();

	if ( empty( $post_ids ) ) {
		get_template_part( 'template-parts/filter-empty-state' );
	} else {
		foreach ( $post_ids as $post_id ) {
			$post = get_post( (int) $post_id );
			if ( $post ) {
				setup_postdata( $GLOBALS['post'] = $post );
				get_template_part( 'template-parts/product-card' );
			}
		}
		wp_reset_postdata();
	}

	$html = ob_get_clean();

	// ── Return filter counts ──────────────────────────────────────────────────
	$counts = alkana_get_filter_counts( $post_ids );

	wp_send_json_success( [
		'html'   => $html,
		'total'  => count( $post_ids ),
		'counts' => $counts,
	] );
}

/**
 * Build FIND_IN_SET OR clauses for a comma-stored column.
 *
 * @param string   $column Column name.
 * @param string[] $slugs  Array of slugs to match.
 * @return array{clauses: string[], values: string[]}
 */
function alkana_build_find_in_set( string $column, array $slugs ): array {
	$clauses = [];
	$values  = [];

	foreach ( $slugs as $slug ) {
		$clauses[] = "FIND_IN_SET(%s, {$column}) > 0";
		$values[]  = $slug;
	}

	return [ 'clauses' => $clauses, 'values' => $values ];
}

/**
 * Count remaining options per taxonomy after current filter.
 *
 * @param int[] $post_ids Filtered post IDs.
 * @return array<string, array<string, int>>
 */
function alkana_get_filter_counts( array $post_ids ): array {
	if ( empty( $post_ids ) ) {
		return [];
	}

	$counts     = [];
	$taxonomies = [ 'product_category', 'surface_type', 'paint_system', 'gloss_level' ];

	foreach ( $taxonomies as $taxonomy ) {
		$terms = wp_get_object_terms( $post_ids, $taxonomy, [ 'fields' => 'slugs' ] );

		if ( is_wp_error( $terms ) ) {
			continue;
		}

		$slug_counts = array_count_values( $terms );
		$counts[ $taxonomy ] = $slug_counts;
	}

	return $counts;
}
