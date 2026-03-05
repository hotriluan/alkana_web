<?php
/**
 * Archive template for alkana_product CPT.
 * Product catalogue with AJAX faceted filter.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">
	<div class="container mx-auto px-4 py-10">

		<div class="archive-products lg:flex lg:gap-8">

			<?php // ── Filter Sidebar ─────────────────────────────────────────── ?>
			<aside class="filter-sidebar lg:w-64 shrink-0" id="filter-sidebar">
				<?php get_template_part( 'template-parts/product-filter-panel' ); ?>
			</aside>

			<?php // ── Product Grid ───────────────────────────────────────────── ?>
			<div class="archive-products__results flex-1">
				<div class="archive-products__count text-sm text-gray-500 mb-4" id="filter-count">
					<?php
					global $wp_query;
					printf(
						/* translators: %d: product count */
						esc_html( _n( '%d product', '%d products', $wp_query->found_posts, 'alkana' ) ),
						(int) $wp_query->found_posts
					);
					?>
				</div>

				<div class="product-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="product-grid">
					<?php
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							get_template_part( 'template-parts/product-card' );
						}
					} else {
						get_template_part( 'template-parts/filter-empty-state' );
					}
					?>
				</div>

				<nav class="filter-pagination flex flex-wrap justify-center gap-2 mt-8"
				     data-filter-pagination
				     aria-label="<?php esc_attr_e( 'Product pages', 'alkana' ); ?>">
				</nav>
			</div>

		</div>
	</div>

	<?php // ── Mobile Bottom Sheet ────────────────────────────────────────────── ?>
	<div class="bottom-sheet" id="filter-bottom-sheet" aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e( 'Filter Products', 'alkana' ); ?>">
		<div class="bottom-sheet__overlay" data-sheet-close></div>
		<div class="bottom-sheet__panel">
			<div class="bottom-sheet__header">
				<span><?php esc_html_e( 'Filter Products', 'alkana' ); ?></span>
				<button class="bottom-sheet__close" data-sheet-close aria-label="<?php esc_attr_e( 'Close filters', 'alkana' ); ?>">×</button>
			</div>
			<div class="bottom-sheet__body">
				<?php get_template_part( 'template-parts/product-filter-panel' ); ?>
			</div>
		</div>
	</div>

</main>

<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>
<?php get_template_part( 'template-parts/footer' ); ?>
