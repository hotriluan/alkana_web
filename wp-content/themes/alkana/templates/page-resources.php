<?php
/**
 * Template Name: Resources
 * TDS / MSDS document download centre.
 * Lists all alkana_product entries that have a TDS or MSDS attached,
 * grouped by product_category taxonomy.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">

	<section class="page-hero bg-gradient-to-br from-alkana-purple-950 to-alkana-purple-800 text-white py-20">
		<div class="container mx-auto px-4">
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Tài liệu kỹ thuật', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-3 text-lg text-white/80 max-w-xl">
				<?php esc_html_e( 'Tải xuống TDS và MSDS cho tất cả sản phẩm sơn Alkana.', 'alkana' ); ?>
			</p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php // ── Search / filter bar ───────────────────────────────────────── ?>
		<div class="resources-search mb-8 flex flex-col sm:flex-row gap-3">
			<input
				type="search"
				id="resource-search"
				class="form-input flex-1"
				placeholder="<?php esc_attr_e( 'Search product name or SKU…', 'alkana' ); ?>"
				aria-label="<?php esc_attr_e( 'Search resources', 'alkana' ); ?>"
			>
		</div>

		<?php
		// Fetch all product categories that have products with TDS/MSDS
		$categories = get_terms( [
			'taxonomy'   => 'product_category',
			'hide_empty' => true,
			'parent'     => 0,
		] );

		foreach ( (array) $categories as $cat ) :
			if ( is_wp_error( $cat ) ) continue;

			$products = get_posts( [
				'post_type'      => 'alkana_product',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'tax_query'      => [ [
					'taxonomy' => 'product_category',
					'field'    => 'term_id',
					'terms'    => $cat->term_id,
					'include_children' => true,
				] ],
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );

			if ( empty( $products ) ) continue;
		?>
		<section class="resources-group mb-10" data-resource-group>
			<h2 class="resources-group__title text-xl font-heading font-semibold text-[--color-secondary] mb-4 pb-2 border-b border-[--color-border]">
				<?php echo esc_html( $cat->name ); ?>
			</h2>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
				<?php foreach ( $products as $product ) :
					$sku  = get_field( '_alkana_sku',  $product->ID );
					$tds  = get_field( '_alkana_tds',  $product->ID );
					$msds = get_field( '_alkana_msds', $product->ID );
					if ( ! $tds && ! $msds ) continue;
				?>
					<div class="resource-row flex flex-col gap-3 p-5 rounded-xl border border-[--color-border] bg-white hover:border-alkana-purple-300 hover:shadow-sm transition-all"
						 data-resource-name="<?php echo esc_attr( strtolower( $product->post_title . ' ' . $sku ) ); ?>">
						<!-- Product info -->
						<div class="flex items-start gap-3">
							<div class="w-10 h-10 rounded-lg bg-alkana-purple-100 flex items-center justify-center flex-shrink-0">
								<svg class="w-5 h-5 text-alkana-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
								</svg>
							</div>
							<div class="min-w-0">
								<a href="<?php echo esc_url( get_permalink( $product->ID ) ); ?>"
								   class="font-semibold text-[--color-secondary] hover:text-[--color-primary] transition-colors leading-snug block">
									<?php echo esc_html( $product->post_title ); ?>
								</a>
								<?php if ( $sku ) : ?>
									<p class="text-xs text-gray-400 mt-0.5"><?php echo esc_html( $sku ); ?></p>
								<?php endif; ?>
							</div>
						</div>
						<!-- Download buttons -->
						<div class="flex gap-2 pt-1 border-t border-gray-100">
							<?php if ( $tds ) : ?>
								<a href="<?php echo esc_url( $tds['url'] ); ?>"
								   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-alkana-purple-50 text-alkana-purple-700 hover:bg-alkana-purple-600 hover:text-white transition-colors"
								   download
								   target="_blank"
								   rel="noopener noreferrer">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
									TDS
								</a>
							<?php endif; ?>
							<?php if ( $msds ) : ?>
								<a href="<?php echo esc_url( $msds['url'] ); ?>"
								   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-600 hover:text-white transition-colors"
								   download
								   target="_blank"
								   rel="noopener noreferrer">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
									MSDS / SDS
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endforeach; ?>

	</div>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>

<script>
// Client-side search for resource table
(function () {
	var input = document.getElementById('resource-search');
	if (!input) return;
	input.addEventListener('input', function () {
		var q = this.value.toLowerCase().trim();
		document.querySelectorAll('.resource-row').forEach(function (row) {
			var name = row.getAttribute('data-resource-name') || '';
			row.style.display = (!q || name.includes(q)) ? '' : 'none';
		});
	});
}());
</script>
