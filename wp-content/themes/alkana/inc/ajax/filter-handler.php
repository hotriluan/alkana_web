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
	$page         = max( 1, (int) ( $_POST['page'] ?? 1 ) );
	$per_page     = 12;

	$table = $wpdb->prefix . 'alkana_product_index';

	// ── Build WHERE clauses ───────────────────────────────────────────────────
	$where  = [];
	$values = [];

	if ( ! empty( $category ) ) {
		$slug_likes = alkana_build_find_in_set( 'category_slugs', $category );
		$where[]    = '(' . implode( ' OR ', $slug_likes['clauses'] ) . ')';
		$values     = array_merge( $values, $slug_likes['values'] );
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

	$where_sql = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

	// ── Total count (no LIMIT) ────────────────────────────────────────────────
	$count_sql = "SELECT COUNT(*) FROM {$table} {$where_sql}";
	if ( ! empty( $values ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $values ) );
	} else {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $count_sql );
	}

	$pages  = max( 1, (int) ceil( $total / $per_page ) );
	$page   = min( $page, $pages );
	$offset = ( $page - 1 ) * $per_page;

	// ── Paginated post IDs ────────────────────────────────────────────────────
	$data_sql = "SELECT post_id FROM {$table} {$where_sql} ORDER BY is_featured DESC, post_id DESC LIMIT %d OFFSET %d";
	$paged_values = array_merge( $values, [ $per_page, $offset ] );
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$post_ids = $wpdb->get_col( $wpdb->prepare( $data_sql, $paged_values ) );

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

	// ── Return filter counts from index table ─────────────────────────────────
	$counts = alkana_get_filter_counts( $table, $where_sql, $values );

	wp_send_json_success( [
		'html'   => $html,
		'total'  => $total,
		'page'   => $page,
		'pages'  => $pages,
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
 * Count available options per taxonomy for the current filter result set.
 * Queries the index table directly — avoids wp_get_object_terms N+1 issue.
 *
 * @param string   $table     Full table name (with prefix).
 * @param string   $where_sql WHERE clause string (may be empty).
 * @param array    $values    Prepared values for WHERE placeholders.
 * @return array<string, array<string, int>>  { column_key: { slug: count } }
 */
function alkana_get_filter_counts( string $table, string $where_sql, array $values ): array {
	global $wpdb;

	// Map: taxonomy → index table column name
	$columns = [
		'product_category' => 'category_slugs',
		'surface_type'     => 'surface_slugs',
		'paint_system'     => 'paint_system',
		'gloss_level'      => 'gloss_level',
	];

	$counts = [];

	foreach ( $columns as $taxonomy => $column ) {
		// Append column non-empty condition to whatever WHERE clause we already have
		if ( $where_sql ) {
			$col_sql = "SELECT {$column} FROM {$table} {$where_sql} AND {$column} != '' AND {$column} IS NOT NULL";
			$col_values = $values;
		} else {
			$col_sql    = "SELECT {$column} FROM {$table} WHERE {$column} != '' AND {$column} IS NOT NULL";
			$col_values = [];
		}

		if ( ! empty( $col_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_col( $wpdb->prepare( $col_sql, $col_values ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_col( $col_sql );
		}

		$slug_counts = [];
		foreach ( $rows as $row ) {
			foreach ( explode( ',', (string) $row ) as $slug ) {
				$slug = trim( $slug );
				if ( '' === $slug ) continue;
				$slug_counts[ $slug ] = ( $slug_counts[ $slug ] ?? 0 ) + 1;
			}
		}

		$counts[ $taxonomy ] = $slug_counts;
	}

	return $counts;
}
