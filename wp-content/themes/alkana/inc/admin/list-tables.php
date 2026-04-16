<?php
/**
 * Custom columns for the alkana_product post list table.
 *
 * Adds: Thumbnail | SKU | Category | Docs Status
 * Removes: Date | Author
 * Sortable: SKU (meta_value) | Category (term name)
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'manage_alkana_product_posts_columns',         'alkana_product_list_columns' );
add_action( 'manage_alkana_product_posts_custom_column',   'alkana_product_column_content', 10, 2 );
add_filter( 'manage_edit-alkana_product_sortable_columns', 'alkana_product_sortable_columns' );
add_action( 'pre_get_posts',                               'alkana_product_column_orderby' );

/**
 * Define columns for the product list table.
 *
 * @param array $columns Default columns.
 * @return array
 */
function alkana_product_list_columns( array $columns ): array {
	return [
		'cb'               => $columns['cb'],
		'thumbnail'        => __( 'Image', 'alkana' ),
		'title'            => $columns['title'],
		'alkana_sku'       => __( 'SKU', 'alkana' ),
		'alkana_coverage'  => __( 'Coverage', 'alkana' ),
		'alkana_mix_ratio' => __( 'Mix Ratio', 'alkana' ),
		'alkana_gloss'     => __( 'Gloss', 'alkana' ),
		'alkana_cat'       => __( 'Category', 'alkana' ),
		'alkana_docs'      => __( 'Docs', 'alkana' ),
	];
}

/**
 * Render custom column cell content.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function alkana_product_column_content( string $column, int $post_id ): void {
	switch ( $column ) {

		case 'thumbnail':
			$thumb = get_the_post_thumbnail( $post_id, [ 50, 50 ] );
			if ( $thumb ) {
				echo '<div style="width:50px;height:50px;overflow:hidden;border-radius:4px;line-height:0;">'
					. $thumb
					. '</div>';
			} else {
				echo '<div style="width:50px;height:50px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;">'
					. '<span class="dashicons dashicons-format-image" style="color:#ccc;font-size:24px;width:24px;height:24px;"></span>'
					. '</div>';
			}
			break;

		case 'alkana_sku':
			$sku = get_post_meta( $post_id, '_alkana_sku', true );
			echo '<span class="inline-editable" data-field="sku" data-post-id="' . esc_attr( $post_id ) . '" data-current="' . esc_attr( $sku ) . '" title="' . esc_attr__( 'Double-click to edit', 'alkana' ) . '">'
				. ( $sku ? esc_html( $sku ) : '<span class="inline-editable__empty">—</span>' )
				. '</span>';
			break;

		case 'alkana_coverage':
			$coverage = get_post_meta( $post_id, '_alkana_coverage', true );
			echo '<span class="inline-editable" data-field="coverage" data-post-id="' . esc_attr( $post_id ) . '" data-current="' . esc_attr( $coverage ) . '" title="' . esc_attr__( 'Double-click to edit', 'alkana' ) . '">'
				. ( $coverage ? esc_html( $coverage ) : '<span class="inline-editable__empty">—</span>' )
				. '</span>';
			break;

		case 'alkana_mix_ratio':
			$mix = get_post_meta( $post_id, '_alkana_mix_ratio', true );
			echo '<span class="inline-editable" data-field="mix_ratio" data-post-id="' . esc_attr( $post_id ) . '" data-current="' . esc_attr( $mix ) . '" title="' . esc_attr__( 'Double-click to edit', 'alkana' ) . '">'
				. ( $mix ? esc_html( $mix ) : '<span class="inline-editable__empty">—</span>' )
				. '</span>';
			break;

		case 'alkana_gloss':
			$gloss = get_post_meta( $post_id, '_alkana_gloss', true );
			echo '<span class="inline-editable" data-field="gloss_level" data-post-id="' . esc_attr( $post_id ) . '" data-current="' . esc_attr( $gloss ) . '" title="' . esc_attr__( 'Double-click to edit', 'alkana' ) . '">'
				. ( $gloss ? esc_html( $gloss ) : '<span class="inline-editable__empty">—</span>' )
				. '</span>';
			break;

		case 'alkana_cat':
			$terms = get_the_terms( $post_id, 'product_category' );
			echo ( ! is_wp_error( $terms ) && ! empty( $terms ) )
				? esc_html( $terms[0]->name )
				: '<span style="color:#aaa;">—</span>';
			break;

		case 'alkana_docs':
			$tds  = get_post_meta( $post_id, '_alkana_tds',  true );
			$msds = get_post_meta( $post_id, '_alkana_msds', true );
			echo alkana_docs_dot( ! empty( $tds ),  'TDS' );
			echo alkana_docs_dot( ! empty( $msds ), 'MSDS' );
			break;
	}
}

/**
 * Render a green/red status dot for a document field.
 *
 * @param bool   $ok    Whether the document is present.
 * @param string $label Display label for the tooltip title.
 * @return string
 */
function alkana_docs_dot( bool $ok, string $label ): string {
	$color = $ok ? '#28a745' : '#dc3545';
	$title = esc_attr( $label . ': ' . ( $ok ? 'OK' : 'Missing' ) );
	return "<span title=\"{$title}\" aria-label=\"{$title}\" "
		. "style=\"display:inline-block;width:10px;height:10px;border-radius:50%;"
		. "background:{$color};margin-right:4px;\"></span>";
}

/**
 * Register sortable columns.
 *
 * @param array $columns Existing sortable columns.
 * @return array
 */
function alkana_product_sortable_columns( array $columns ): array {
	$columns['alkana_sku'] = 'alkana_sku';
	$columns['alkana_cat'] = 'alkana_cat';
	return $columns;
}

/**
 * Apply meta/taxonomy sort when order-by header is clicked.
 *
 * @param WP_Query $query List table query.
 */
function alkana_product_column_orderby( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'alkana_sku' === $orderby ) {
		$query->set( 'meta_key', '_alkana_sku' );
		$query->set( 'orderby',  'meta_value' );
		return;
	}

	if ( 'alkana_cat' === $orderby ) {
		$dir = strtoupper( $query->get( 'order' ) );
		if ( ! in_array( $dir, [ 'ASC', 'DESC' ], true ) ) {
			$dir = 'ASC';
		}
		// Store direction for the filter callback.
		alkana_cat_sort_dir( $dir );
		add_filter( 'posts_join',    'alkana_cat_sort_join' );
		add_filter( 'posts_orderby', 'alkana_cat_sort_orderby' );
	}
}

/**
 * Get/set the taxonomy sort direction via a static variable.
 *
 * @param string|null $set Pass a direction string to set it; omit to get.
 * @return string
 */
function alkana_cat_sort_dir( ?string $set = null ): string {
	static $dir = 'ASC';
	if ( null !== $set ) {
		$dir = $set;
	}
	return $dir;
}

/**
 * JOIN taxonomy tables for category sort.
 *
 * @param string $join Existing JOIN SQL.
 * @return string
 */
function alkana_cat_sort_join( string $join ): string {
	global $wpdb;
	$join .= " LEFT JOIN {$wpdb->term_relationships} tr_cat ON ({$wpdb->posts}.ID = tr_cat.object_id)"
		. $wpdb->prepare(
			" LEFT JOIN {$wpdb->term_taxonomy} tt_cat ON (tr_cat.term_taxonomy_id = tt_cat.term_taxonomy_id AND tt_cat.taxonomy = %s)",
			'product_category'
		)
		. " LEFT JOIN {$wpdb->terms} t_cat ON (tt_cat.term_id = t_cat.term_id)";
	remove_filter( 'posts_join', 'alkana_cat_sort_join' );
	return $join;
}

/**
 * ORDER BY term name for category sort.
 *
 * @param string $orderby Existing ORDER BY SQL.
 * @return string
 */
function alkana_cat_sort_orderby( string $orderby ): string {
	$dir = alkana_cat_sort_dir();
	remove_filter( 'posts_orderby', 'alkana_cat_sort_orderby' );
	return "t_cat.name {$dir}";
}
