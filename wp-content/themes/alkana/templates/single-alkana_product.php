<?php
/**
 * Single product template for alkana_product CPT.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<div class="container mx-auto px-4 py-10">
		<div class="product-detail lg:flex lg:gap-10">

			<?php // ── Product Gallery ────────────────────────────────────────── ?>
			<div class="product-detail__gallery lg:w-1/2">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'alkana-product-hero', [ 'class' => 'w-full rounded-lg' ] ); ?>
				<?php endif; ?>
			</div>

			<?php // ── Product Info ───────────────────────────────────────────── ?>
			<div class="product-detail__info lg:w-1/2">
				<?php
				// Breadcrumb taxonomy
				$cats = get_the_terms( get_the_ID(), 'product_category' );
				if ( $cats && ! is_wp_error( $cats ) ) {
					echo '<p class="text-sm text-[--color-primary] mb-2">' . esc_html( $cats[0]->name ) . '</p>';
				}
				?>

				<h1 class="product-detail__title text-3xl font-heading font-bold text-[--color-secondary] mb-4">
					<?php the_title(); ?>
				</h1>

				<div class="product-detail__excerpt text-gray-600 mb-6">
					<?php the_excerpt(); ?>
				</div>

				<?php // Technical specs table ?>
				<?php get_template_part( 'template-parts/product-specs-table' ); ?>

				<?php // CTA buttons ?>
				<div class="product-detail__cta flex flex-wrap gap-3 mt-6">
					<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>"
					   class="btn btn--primary">
						<?php esc_html_e( 'Request Quote', 'alkana' ); ?>
					</a>
					<?php
					$tds_file = get_field( 'tds_file' );
					if ( $tds_file ) :
					?>
						<a href="<?php echo esc_url( $tds_file['url'] ); ?>"
						   class="btn btn--outline"
						   download>
							<?php esc_html_e( 'Download TDS', 'alkana' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<?php // ── Full description ───────────────────────────────────────────── ?>
		<div class="product-detail__content prose max-w-none mt-12">
			<?php the_content(); ?>
		</div>

	</div>

<?php endwhile; endif; ?>
</main>

<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>
<?php get_template_part( 'template-parts/footer' ); ?>
