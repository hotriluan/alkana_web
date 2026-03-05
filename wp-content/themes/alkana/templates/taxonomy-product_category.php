<?php
/**
 * Taxonomy archive: product_category
 * Lists products filtered by a specific product category term.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

$term = get_queried_object();
?>

<main id="main-content" class="site-main">

	<?php // ── Category Hero ──────────────────────────────────────────────────── ?>
	<section class="page-hero bg-[--color-secondary] text-white py-14">
		<div class="container mx-auto px-4">
			<p class="text-sm text-[--color-primary] mb-1 uppercase tracking-wide">
				<?php esc_html_e( 'Product Category', 'alkana' ); ?>
			</p>
			<h1 class="text-3xl font-heading font-bold"><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( $term->description ) : ?>
				<p class="mt-2 text-white/70 max-w-xl"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<div class="container mx-auto px-4 py-10">

		<?php // ── Sub-categories ────────────────────────────────────────────── ?>
		<?php
		$children = get_terms( [
			'taxonomy'   => 'product_category',
			'parent'     => $term->term_id,
			'hide_empty' => true,
		] );
		if ( ! empty( $children ) && ! is_wp_error( $children ) ) :
		?>
			<div class="category-children flex flex-wrap gap-2 mb-8">
				<?php foreach ( $children as $child ) : ?>
					<a href="<?php echo esc_url( get_term_link( $child ) ); ?>"
					   class="badge bg-[--color-bg-light] border border-[--color-border] px-3 py-1 rounded-full text-sm hover:bg-[--color-primary] hover:text-white hover:border-[--color-primary] transition-colors">
						<?php echo esc_html( $child->name ); ?>
						<span class="text-gray-400 ml-1">(<?php echo (int) $child->count; ?>)</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php // ── Product Grid ───────────────────────────────────────────────── ?>
		<div class="product-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="product-grid">
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

		<?php
		// Pagination
		the_posts_pagination( [
			'prev_text' => '&larr; ' . __( 'Previous', 'alkana' ),
			'next_text' => __( 'Next', 'alkana' ) . ' &rarr;',
			'class'     => 'pagination flex gap-2 mt-10 justify-center',
		] );
		?>

	</div>
</main>

<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>
<?php get_template_part( 'template-parts/footer' ); ?>
