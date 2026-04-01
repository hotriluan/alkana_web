<?php
/**
 * Related products section.
 * Shows products from the same category as the current product.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$current_post_id = get_the_ID();
$terms           = get_the_terms( $current_post_id, 'product_category' );
$related_query   = null;

// Try to get products from same category first
if ( $terms && ! is_wp_error( $terms ) ) {
	$term_ids = wp_list_pluck( $terms, 'term_id' );
	
	$related_query = new WP_Query( [
		'post_type'      => 'alkana_product',
		'posts_per_page' => 3,
		'post__not_in'   => [ $current_post_id ],
		'orderby'        => 'rand',
		'tax_query'      => [
			[
				'taxonomy' => 'product_category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			],
		],
	] );
}

// Fallback: if no products in same category, get latest 3 products
if ( ! $related_query || ! $related_query->have_posts() ) {
	$related_query = new WP_Query( [
		'post_type'      => 'alkana_product',
		'posts_per_page' => 3,
		'post__not_in'   => [ $current_post_id ],
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );
}

// Only render if we have related products
if ( ! $related_query->have_posts() ) {
	return;
}
?>

<section class="section section--related py-16 bg-gray-50">
	<div class="max-w-7xl mx-auto px-4">
		<h2 class="text-2xl font-heading font-bold text-[--color-secondary] mb-8">
			Sản phẩm liên quan
		</h2>
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
			<?php
			while ( $related_query->have_posts() ) :
				$related_query->the_post();
				get_template_part( 'template-parts/product-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
