<?php
/**
 * front-page.php — WordPress static front page template.
 *
 * Layout (12 sections):
 * 1. Hero Slider (full-viewport, parallax, 2 CTAs)
 * 2. Trust Bar (ISO/certification marquee)
 * 3. USP Counter Section (animated stats)
 * 4. Featured Products with Category Tabs
 * 5. CTA Banner (full-width gradient)
 * 6. Solutions Section (4 solution cards)
 * 7. Why Alkana (company story)
 * 8. Recent Projects (masonry grid)
 * 9. Testimonials (carousel)
 * 10. Latest News (3 articles)
 * 11. Partner Logos (marquee)
 * 12. Footer CTA (contact form preview)
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main-content" class="site-main">

	<?php // 1. Hero — wrapped to pull under sticky header for transparent effect ?>
	<div class="homepage-hero-wrap">
		<?php get_template_part( 'template-parts/hero-slider' ); ?>
	</div>

	<?php // 2. Trust Bar ──────────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/trust-badges' ); ?>

	<?php // 3. USP Counter ───────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/usp-section' ); ?>

	<?php // 4. Featured Products (tab-based) ───────────────────────────────── ?>
	<?php get_template_part( 'template-parts/homepage/product-tabs' ); ?>

	<?php // 5. CTA Banner ────────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/homepage/cta-banner' ); ?>

	<?php // 6. Solutions Section ─────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/homepage/solutions-section' ); ?>

	<?php // 7. Why Alkana ────────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/homepage/why-alkana' ); ?>

	<?php // 8. Recent Projects — featured masonry (1 large + 2 smaller) ────────────────────── ?>
	<section class="section section--cool">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="section__header">
				<p class="section__label"><?php esc_html_e( 'Thực tiễn', 'alkana' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'Dự án tiêu biểu', 'alkana' ); ?></h2>
				<p class="section__desc"><?php esc_html_e( 'Những công trình nổi bật đã ứng dụng giải pháp sơn phủ của Alkana.', 'alkana' ); ?></p>
			</div>

			<?php
			$project_query = new WP_Query( [
				'post_type'      => 'alkana_project',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
			] );
			if ( $project_query->have_posts() ) :
				$projects = $project_query->posts;
				wp_reset_postdata();
			?>
			<div class="projects-featured">
				<!-- Large featured project -->
				<div class="projects-featured__main">
					<?php
				global $post;
				$post = $projects[0]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				setup_postdata( $post );
				get_template_part( 'template-parts/project-card' );
				wp_reset_postdata();
				?>
			</div>
			<!-- Smaller projects grid -->
			<div class="projects-featured__grid">
				<?php foreach ( array_slice( $projects, 1, 4 ) as $p ) :
					$post = $p; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					setup_postdata( $post );
						get_template_part( 'template-parts/project-card' );
						wp_reset_postdata();
					endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="mt-10 text-center">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_project' ) ); ?>" class="btn btn--outline">
					<?php esc_html_e( 'Xem tất cả dự án', 'alkana' ); ?> →
				</a>
			</div>
		</div>
	</section>

	<?php // 9. Testimonials ──────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/testimonials' ); ?>

	<?php // 10. Latest News — 1 large featured + 2 smaller ────────────────────── ?>
	<section class="section section--white">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="section__header">
				<p class="section__label"><?php esc_html_e( 'Cập nhật', 'alkana' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'Tin tức & Kiến thức', 'alkana' ); ?></h2>
			</div>

			<?php
			$news_query = new WP_Query( [
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
			] );
			if ( $news_query->have_posts() ) :
				$news_posts = $news_query->posts;
				wp_reset_postdata();
				$featured   = $news_posts[0];
				$secondary  = array_slice( $news_posts, 1 );
			?>
			<div class="news-featured">
				<!-- Large featured article -->
				<article class="news-featured__main card card--elevated group">
					<?php if ( get_post_thumbnail_id( $featured->ID ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>"
						   class="block overflow-hidden rounded-t-xl aspect-video">
							<?php echo wp_get_attachment_image(
								get_post_thumbnail_id( $featured->ID ),
								'large',
								false,
								[
									'class'   => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
									'alt'     => esc_attr( $featured->post_title ),
									'loading' => 'lazy',
								]
							); ?>
						</a>
					<?php endif; ?>
					<div class="p-6">
						<p class="text-xs text-alkana-purple-600 font-semibold uppercase tracking-wider mb-2">
							<?php echo esc_html( get_the_date( '', $featured->ID ) ); ?>
						</p>
						<h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-alkana-purple-700 transition-colors">
							<a href="<?php echo esc_url( get_permalink( $featured->ID ) ); ?>">
								<?php echo esc_html( $featured->post_title ); ?>
							</a>
						</h3>
						<p class="text-gray-500 line-clamp-3">
							<?php echo wp_trim_words( $featured->post_excerpt ?: $featured->post_content, 30 ); ?>
						</p>
					</div>
				</article>

				<!-- Secondary articles -->
				<div class="news-featured__sidebar">
					<?php foreach ( $secondary as $post ) : ?>
					<article class="card card--elevated group flex gap-4 p-4">
						<?php if ( get_post_thumbnail_id( $post->ID ) ) : ?>
							<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"
							   class="shrink-0 w-28 h-20 overflow-hidden rounded-lg">
								<?php echo wp_get_attachment_image(
									get_post_thumbnail_id( $post->ID ),
									'thumbnail',
									false,
									[
										'class'   => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
										'alt'     => esc_attr( $post->post_title ),
										'loading' => 'lazy',
									]
								); ?>
							</a>
						<?php endif; ?>
						<div class="min-w-0">
							<p class="text-xs text-alkana-purple-600 font-semibold uppercase tracking-wider mb-1">
								<?php echo esc_html( get_the_date( '', $post->ID ) ); ?>
							</p>
							<h3 class="font-bold text-gray-900 text-sm group-hover:text-alkana-purple-700 transition-colors line-clamp-2">
								<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
									<?php echo esc_html( $post->post_title ); ?>
								</a>
							</h3>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<?php // 11. Partner Logos ────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/homepage/partner-logos' ); ?>

	<?php // 12. Footer CTA ───────────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/homepage/footer-cta' ); ?>

</main>

<?php
get_template_part( 'template-parts/sticky-cta-mobile' );
get_footer();
