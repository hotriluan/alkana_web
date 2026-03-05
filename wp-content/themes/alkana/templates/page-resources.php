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

	<section class="page-hero bg-[--color-secondary] text-white py-14">
		<div class="container mx-auto px-4">
			<h1 class="text-3xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-2 text-white/70">
				<?php esc_html_e( 'Download technical data sheets and safety data sheets for all Alkana products.', 'alkana' ); ?>
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

			<div class="overflow-x-auto">
				<table class="w-full text-sm border-collapse">
					<thead class="bg-gray-50 text-left">
						<tr>
							<th class="p-3 font-medium text-gray-600 w-1/3"><?php esc_html_e( 'Product', 'alkana' ); ?></th>
							<th class="p-3 font-medium text-gray-600"><?php esc_html_e( 'SKU', 'alkana' ); ?></th>
							<th class="p-3 font-medium text-gray-600 text-center"><?php esc_html_e( 'TDS', 'alkana' ); ?></th>
							<th class="p-3 font-medium text-gray-600 text-center"><?php esc_html_e( 'MSDS / SDS', 'alkana' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $products as $product ) :
							$sku  = get_field( '_alkana_sku',  $product->ID );
							$tds  = get_field( '_alkana_tds',  $product->ID );
							$msds = get_field( '_alkana_msds', $product->ID );
							if ( ! $tds && ! $msds ) continue;
						?>
							<tr class="resource-row border-b border-gray-100 hover:bg-gray-50"
								data-resource-name="<?php echo esc_attr( strtolower( $product->post_title . ' ' . $sku ) ); ?>">
								<td class="p-3 font-medium">
									<a href="<?php echo esc_url( get_permalink( $product->ID ) ); ?>"
									   class="hover:text-[--color-primary]">
										<?php echo esc_html( $product->post_title ); ?>
									</a>
								</td>
								<td class="p-3 text-gray-500"><?php echo esc_html( $sku ?: '—' ); ?></td>
								<td class="p-3 text-center">
									<?php if ( $tds ) : ?>
										<a href="<?php echo esc_url( $tds['url'] ); ?>"
										   class="inline-flex items-center gap-1 text-[--color-primary] hover:underline font-medium"
										   download
										   target="_blank"
										   rel="noopener noreferrer">
											<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
											PDF
										</a>
									<?php else : ?>
										<span class="text-gray-300">—</span>
									<?php endif; ?>
								</td>
								<td class="p-3 text-center">
									<?php if ( $msds ) : ?>
										<a href="<?php echo esc_url( $msds['url'] ); ?>"
										   class="inline-flex items-center gap-1 text-[--color-primary] hover:underline font-medium"
										   download
										   target="_blank"
										   rel="noopener noreferrer">
											<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
											PDF
										</a>
									<?php else : ?>
										<span class="text-gray-300">—</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
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
