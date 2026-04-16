<?php
/**
 * AJAX handler for Paint System Builder wizard.
 *
 * Action:  alkana_paint_builder (public + logged-in)
 * Returns: JSON { primer: {...}|null, topcoat: {...}|null, quote_url: string }
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_paint_builder',        'alkana_ajax_paint_builder' );
add_action( 'wp_ajax_nopriv_alkana_paint_builder', 'alkana_ajax_paint_builder' );

/**
 * Map environment + condition to preferred paint_system taxonomy slugs.
 * Returns slugs in priority order.
 *
 * @param string $environment  indoor|outdoor
 * @param string $condition    normal|harsh
 * @return string[]
 */
function alkana_builder_system_slugs( string $environment, string $condition ): array {
	$map = [
		'indoor'  => [
			'normal' => [ 'water-based', 'single-component', 'epoxy-2k' ],
			'harsh'  => [ 'epoxy-2k', 'solvent-based', 'pu-2k' ],
		],
		'outdoor' => [
			'normal' => [ 'water-based', 'alkyd', 'single-component' ],
			'harsh'  => [ 'epoxy-2k', 'pu-2k', 'solvent-based', 'acrylic-waterproof' ],
		],
	];

	return $map[ $environment ][ $condition ] ?? [ 'water-based', 'single-component' ];
}

/**
 * Fetch one product from the index matching the given WHERE + primer/topcoat category.
 *
 * @param string   $surface_slug  Surface type taxonomy slug.
 * @param string[] $system_slugs  Preferred paint_system slugs (OR match).
 * @param bool     $want_primer   TRUE = look for primer-category products, FALSE = topcoat.
 * @return object|null  Row from index, or null.
 */
function alkana_builder_fetch_product( string $surface_slug, array $system_slugs, bool $want_primer ): ?object {
	global $wpdb;
	$table = $wpdb->prefix . 'alkana_product_index';

	// ── Primer category keyword heuristic ────────────────────────────────────
	$primer_slugs = [ 'wood-primer', 'anti-rust', 'wall-waterproofing',
	                  'roof-waterproofing', 'floor-tank-waterproofing' ];
	$primer_like  = array_map( static fn( $s ) => "category_slugs LIKE %s", $primer_slugs );

	if ( $want_primer ) {
		$cat_sql = '(' . implode( ' OR ', $primer_like ) . ')';
		$cat_vals = array_map( static fn( $s ) => '%' . $wpdb->esc_like( $s ) . '%', $primer_slugs );
	} else {
		$cat_sql = 'NOT (' . implode( ' OR ', $primer_like ) . ')';
		$cat_vals = array_map( static fn( $s ) => '%' . $wpdb->esc_like( $s ) . '%', $primer_slugs );
	}

	// ── System slugs (OR) ────────────────────────────────────────────────────
	$sys_clauses = array_map( static fn( $s ) => 'FIND_IN_SET(%s, paint_system) > 0', $system_slugs );
	$sys_sql     = '(' . implode( ' OR ', $sys_clauses ) . ')';
	$sys_vals    = $system_slugs;

	// ── Surface match: use FIND_IN_SET ────────────────────────────────────────
	$all_vals   = array_merge( [ $surface_slug ], $cat_vals, $sys_vals );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare(
		"SELECT post_id, product_name, product_slug, category_slugs
		 FROM {$table}
		 WHERE FIND_IN_SET(%s, surface_slugs) > 0
		   AND {$cat_sql}
		   AND {$sys_sql}
		 ORDER BY is_featured DESC, post_id DESC
		 LIMIT 1",
		$all_vals
	);
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$row = $wpdb->get_row( $sql );

	// Fallback: ignore paint_system constraint
	if ( ! $row ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT post_id, product_name, product_slug, category_slugs
				 FROM {$table}
				 WHERE FIND_IN_SET(%s, surface_slugs) > 0
				   AND {$cat_sql}
				 ORDER BY is_featured DESC, post_id DESC
				 LIMIT 1",
				array_merge( [ $surface_slug ], $cat_vals )
			)
		);
	}

	return $row ?: null;
}

/**
 * Fetch one intermediate-layer product using the _alkana_layer ACF meta field.
 * Falls back to sealer/filler if no 'intermediate' product is found.
 *
 * @param string   $surface_slug  Surface type taxonomy slug.
 * @param string[] $system_slugs  Preferred paint_system slugs.
 * @return object|null
 */
function alkana_builder_fetch_intermediate( string $surface_slug, array $system_slugs ): ?object {
	global $wpdb;
	$table = $wpdb->prefix . 'alkana_product_index';
	$pmeta = $wpdb->postmeta;

	$sys_clauses = array_map( static fn( $s ) => 'FIND_IN_SET(%s, p.paint_system) > 0', $system_slugs );
	$sys_sql     = '(' . implode( ' OR ', $sys_clauses ) . ')';

	foreach ( [ 'intermediate', 'sealer', 'filler' ] as $layer ) {
		$all_vals = array_merge( [ $layer, $surface_slug ], $system_slugs );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT p.post_id, p.product_name, p.product_slug, p.category_slugs
				 FROM {$table} p
				 INNER JOIN {$pmeta} pm ON pm.post_id = p.post_id
				                       AND pm.meta_key = '_alkana_layer'
				                       AND pm.meta_value = %s
				 WHERE FIND_IN_SET(%s, p.surface_slugs) > 0
				   AND {$sys_sql}
				 ORDER BY p.is_featured DESC, p.post_id DESC
				 LIMIT 1",
				$all_vals
			)
		);

		if ( $row ) return $row;
	}

	return null;
}

/**
 * Build a product data array from a product index row.
 *
 * @param object $row  Row from alkana_product_index.
 * @return array
 */
function alkana_builder_product_data( object $row ): array {
	$id    = (int) $row->post_id;
	$thumb = get_the_post_thumbnail_url( $id, 'medium' );
	$sku   = get_post_meta( $id, '_alkana_sku', true );
	$cats  = get_the_terms( $id, 'product_category' );

	return [
		'id'       => $id,
		'title'    => $row->product_name,
		'sku'      => $sku ?: '',
		'thumb'    => $thumb ?: '',
		'url'      => (string) get_permalink( $id ),
		'category' => ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '',
	];
}

/**
 * Main AJAX callback.
 */
function alkana_ajax_paint_builder(): void {
	if ( ! check_ajax_referer( 'alkana_paint_builder', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => 'Invalid nonce.' ], 403 );
	}

	$surface     = sanitize_key( $_POST['surface']     ?? '' );
	$environment = sanitize_key( $_POST['environment'] ?? 'indoor' );
	$condition   = sanitize_key( $_POST['condition']   ?? 'normal' );

	if ( ! $surface ) {
		wp_send_json_error( [ 'message' => 'Surface is required.' ], 400 );
	}

	$allowed_envs       = [ 'indoor', 'outdoor' ];
	$allowed_conditions = [ 'normal', 'harsh' ];
	$environment = in_array( $environment, $allowed_envs, true )       ? $environment : 'indoor';
	$condition   = in_array( $condition,   $allowed_conditions, true )  ? $condition   : 'normal';

	$system_slugs = alkana_builder_system_slugs( $environment, $condition );

	$primer_row       = alkana_builder_fetch_product( $surface, $system_slugs, true );
	$intermediate_row = alkana_builder_fetch_intermediate( $surface, $system_slugs );
	$topcoat_row      = alkana_builder_fetch_product( $surface, $system_slugs, false );

	$quote_url = alkana_get_contact_url() ?: home_url( '/contact' );

	wp_send_json_success( [
		'primer'       => $primer_row       ? alkana_builder_product_data( $primer_row )       : null,
		'intermediate' => $intermediate_row ? alkana_builder_product_data( $intermediate_row ) : null,
		'topcoat'      => $topcoat_row      ? alkana_builder_product_data( $topcoat_row )      : null,
		'quote_url'    => $quote_url,
		'surface'      => $surface,
	] );
}
