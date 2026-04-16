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
			<aside class="filter-sidebar hidden lg:block w-1/4 sticky top-24 self-start max-h-[calc(100vh-6rem)] overflow-y-auto pr-4 custom-scrollbar shrink-0" id="filter-sidebar">
				<?php get_template_part( 'template-parts/product-filter-panel' ); ?>
			</aside>

			<?php // ── Product Grid ───────────────────────────────────────────── ?>
			<div class="archive-products__results flex-1">

				<div class="archive-toolbar">
					<div class="text-sm text-gray-500" id="filter-count">
						<?php
						global $wp_query;
						printf(
							/* translators: %d: product count */
							esc_html( _n( '%d product', '%d products', $wp_query->found_posts, 'alkana' ) ),
							(int) $wp_query->found_posts
						);
						?>
					</div>
					<div class="flex items-center gap-3">
						<select id="product-sort" class="sort-select" aria-label="<?php esc_attr_e( 'Sort products', 'alkana' ); ?>">
							<option value="latest"><?php esc_html_e( 'Mới nhất', 'alkana' ); ?></option>
							<option value="name_asc"><?php esc_html_e( 'Tên A–Z', 'alkana' ); ?></option>
							<option value="name_desc"><?php esc_html_e( 'Tên Z–A', 'alkana' ); ?></option>
						</select>
						<div class="view-toggle" role="group" aria-label="<?php esc_attr_e( 'View mode', 'alkana' ); ?>">
							<button id="view-grid" class="view-toggle__btn is-active" aria-label="<?php esc_attr_e( 'Grid view', 'alkana' ); ?>" aria-pressed="true">
								<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
									<rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/>
									<rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/>
								</svg>
							</button>
							<button id="view-list" class="view-toggle__btn" aria-label="<?php esc_attr_e( 'List view', 'alkana' ); ?>" aria-pressed="false">
								<svg class="w-4 h-4" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
									<line x1="2" y1="4" x2="14" y2="4"/><line x1="2" y1="8" x2="14" y2="8"/><line x1="2" y1="12" x2="14" y2="12"/>
								</svg>
							</button>
						</div>
					</div>
				</div>

				<div class="product-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6" id="product-grid" data-reveal-stagger>
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
