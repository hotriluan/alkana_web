<?php
/**
 * Template Name: Solutions
 * Showcases product categories / paint solutions by industry.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">

	<section class="page-hero bg-[--color-secondary] text-white py-16">
		<div class="container mx-auto px-4">
			<h1 class="text-3xl font-heading font-bold"><?php the_title(); ?></h1>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<div class="prose prose-lg mb-12"><?php the_content(); ?></div>
		<?php endwhile; endif; ?>

		<?php // ── Product Categories Grid ────────────────────────────────────── ?>
		<div class="solutions-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php
			$categories = get_terms( [
				'taxonomy'   => 'product_category',
				'hide_empty' => true,
				'parent'     => 0,
			] );

			foreach ( (array) $categories as $cat ) :
				if ( is_wp_error( $cat ) ) continue;
				$thumb_id = get_term_meta( $cat->term_id, 'category_thumbnail_id', true );
			?>
				<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
				   class="card card--solution group block overflow-hidden rounded-lg shadow hover:shadow-lg transition-shadow">
					<?php if ( $thumb_id ) : ?>
						<?php echo wp_get_attachment_image( (int) $thumb_id, 'alkana-product-card', false, [ 'class' => 'w-full object-cover h-40' ] ); ?>
					<?php endif; ?>
					<div class="p-4">
						<h3 class="font-heading font-semibold text-[--color-secondary] group-hover:text-[--color-primary] transition-colors">
							<?php echo esc_html( $cat->name ); ?>
						</h3>
						<p class="text-sm text-gray-500 mt-1"><?php echo esc_html( $cat->description ); ?></p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>

	</div>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
