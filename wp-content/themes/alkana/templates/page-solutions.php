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

	<section class="page-hero bg-gradient-to-br from-alkana-purple-950 to-alkana-purple-800 text-white py-20">
		<div class="container mx-auto px-4">
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Giải pháp sơn', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-3 text-lg text-white/80 max-w-xl"><?php esc_html_e( 'Danh mục sản phẩm sơn công nghiệp theo từng ngành và ứng dụng.', 'alkana' ); ?></p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<div class="prose prose-lg mb-12"><?php the_content(); ?></div>
		<?php endwhile; endif; ?>
	</div>

	<?php // ── Product Categories — Large Alternating Cards ────────────── ?>
		<div class="solutions-list space-y-0">
			<?php
			$categories = get_terms( [
				'taxonomy'   => 'product_category',
				'hide_empty' => true,
				'parent'     => 0,
			] );

			foreach ( (array) $categories as $cat_idx => $cat ) :
				if ( is_wp_error( $cat ) ) continue;
				$thumb_id  = get_term_meta( $cat->term_id, 'category_thumbnail_id', true );
				$is_even   = $cat_idx % 2 === 0;
				$bg_class  = $is_even ? 'bg-white' : 'bg-[#F8F4FF]';
			?>
				<section class="<?php echo $bg_class; ?> py-16 md:py-20" data-reveal>
					<div class="max-w-6xl mx-auto px-4">
						<div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center <?php echo $is_even ? '' : 'lg:[&>*:first-child]:order-last'; ?>">
							<!-- Image -->
							<div class="mb-8 lg:mb-0">
								<?php if ( $thumb_id ) : ?>
									<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="block rounded-2xl overflow-hidden shadow-xl group">
										<?php echo wp_get_attachment_image( (int) $thumb_id, 'alkana-project-card', false, [
											'class'   => 'w-full aspect-[4/3] object-cover transition-transform duration-500 group-hover:scale-105',
											'loading' => 'lazy',
											'alt'     => esc_attr( $cat->name ),
										] ); ?>
									</a>
								<?php else : ?>
									<div class="w-full aspect-[4/3] rounded-2xl bg-alkana-purple-100 flex items-center justify-center">
										<svg class="w-20 h-20 text-alkana-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
									</div>
								<?php endif; ?>
							</div>
							<!-- Text -->
							<div>
								<span class="inline-block px-3 py-1 text-xs font-semibold text-alkana-purple-700 bg-alkana-purple-100 rounded-full uppercase tracking-wider mb-4"><?php esc_html_e( 'Giải pháp', 'alkana' ); ?></span>
								<h2 class="text-2xl md:text-3xl font-heading font-bold text-[--color-secondary] mb-4">
									<?php echo esc_html( $cat->name ); ?>
								</h2>
								<?php if ( $cat->description ) : ?>
									<p class="text-gray-600 text-base leading-relaxed mb-6"><?php echo esc_html( $cat->description ); ?></p>
								<?php endif; ?>
								<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
								   class="inline-flex items-center gap-2 font-semibold text-alkana-purple-600 hover:text-alkana-purple-800 group/link transition-colors">
									<?php esc_html_e( 'Khám phá sản phẩm', 'alkana' ); ?>
									<svg class="w-4 h-4 transition-transform group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
								</a>
							</div>
						</div>
					</div>
				</section>
			<?php endforeach; ?>
		</div>

</main>

<?php get_template_part( 'template-parts/footer' ); ?>
