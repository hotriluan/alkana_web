<?php
/**
 * Homepage — Product Tabs Section.
 * Filterable product cards by category using Alpine.js.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$product_cats = get_terms( [
	'taxonomy'   => 'product_category',
	'hide_empty' => true,
	'number'     => 8,
] );

$all_label = __( 'Tất cả', 'alkana' );
?>

<section class="section section--white" id="product-tabs">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="section__header">
			<p class="section__label"><?php esc_html_e( 'Sản phẩm', 'alkana' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Danh mục sơn chuyên dụng', 'alkana' ); ?></h2>
			<p class="section__desc"><?php esc_html_e( 'Hơn 500 sản phẩm sơn cho mọi ứng dụng công nghiệp và dân dụng', 'alkana' ); ?></p>
		</div>

		<?php if ( ! is_wp_error( $product_cats ) && ! empty( $product_cats ) ) : ?>
		<div x-data="{ activeTab: '0' }" class="product-tabs">
			<!-- Tab Nav -->
			<div class="product-tabs__nav" role="tablist">
				<button
					@click="activeTab = '0'"
					:class="activeTab === '0' ? 'tab-active' : ''"
					class="product-tab-btn"
					role="tab"
				><?php echo esc_html( $all_label ); ?></button>

				<?php foreach ( $product_cats as $cat ) : ?>
				<button
					@click="activeTab = '<?php echo esc_attr( (string) $cat->term_id ); ?>'"
					:class="activeTab === '<?php echo esc_attr( (string) $cat->term_id ); ?>' ? 'tab-active' : ''"
					class="product-tab-btn"
					role="tab"
				><?php echo esc_html( $cat->name ); ?></button>
				<?php endforeach; ?>
			</div>

			<!-- Product Grid — "All" -->
			<div x-show="activeTab === '0'" x-transition.opacity class="product-tabs__panel">
				<?php
				$q = new WP_Query( [
					'post_type'      => 'alkana_product',
					'posts_per_page' => 8,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				] );
				if ( $q->have_posts() ) :
					echo '<div class="product-grid">';
					while ( $q->have_posts() ) :
						$q->the_post();
						get_template_part( 'template-parts/product-card' );
					endwhile;
					echo '</div>';
					wp_reset_postdata();
				endif;
				?>
			</div>

			<!-- Per-category panels -->
			<?php foreach ( $product_cats as $cat ) : ?>
			<div x-show="activeTab === '<?php echo esc_attr( (string) $cat->term_id ); ?>'" x-transition.opacity class="product-tabs__panel">
				<?php
				$q = new WP_Query( [
				'post_type'      => 'alkana_product',
				'posts_per_page' => 8,
				'tax_query'      => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery
					'taxonomy' => 'product_category',
					'field'    => 'term_id',
					'terms'    => $cat->term_id,
				] ],
			] );
			if ( $q->have_posts() ) :
				echo '<div class="product-grid">';
				while ( $q->have_posts() ) :
					$q->the_post();
					get_template_part( 'template-parts/product-card' );
					endwhile;
					echo '</div>';
					wp_reset_postdata();
				endif;
				?>
			</div>
			<?php endforeach; ?>

			<div class="mt-10 text-center">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_product' ) ); ?>"
				   class="btn btn--outline-purple">
					<?php esc_html_e( 'Xem tất cả sản phẩm', 'alkana' ); ?>
				</a>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
