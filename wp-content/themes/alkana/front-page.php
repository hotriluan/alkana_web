<?php
/**
 * front-page.php — WordPress static front page template.
 *
 * WordPress template hierarchy: front-page.php is loaded when the site
 * is set to display a static front page (Reading Settings → Show on front = A
 * static page). This file must be at the theme root.
 *
 * Layout: Hero Banner → USP Section → Trust Badges → Featured Products → 
 *         Recent Projects → Testimonials
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main-content" class="site-main">

	<?php get_template_part( 'template-parts/hero-banner' ); ?>

	<?php get_template_part( 'template-parts/usp-section' ); ?>

	<?php get_template_part( 'template-parts/trust-badges' ); ?>

	<?php // ── Featured Products ──────────────────────────────────────────────── ?>
	<section class="section section--products py-20 bg-gray-50">
		<div class="max-w-7xl mx-auto px-4">
			<h2 class="section__title text-3xl md:text-4xl font-extrabold text-center text-[#1A3A5C] mb-12">
				<?php esc_html_e( 'Featured Products', 'alkana' ); ?>
			</h2>
			<div class="product-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" id="product-grid">
				<?php
				$featured_query = new WP_Query( [
					'post_type'      => 'alkana_product',
					'posts_per_page' => 6,
					'meta_key'       => '_alkana_featured',
					'meta_value'     => '1',
					'post_status'    => 'publish',
				] );

				if ( $featured_query->have_posts() ) {
					while ( $featured_query->have_posts() ) {
						$featured_query->the_post();
						get_template_part( 'template-parts/product-card' );
					}
					wp_reset_postdata();
				} else {
					// Fallback: show latest products if none are marked featured
					$fallback_query = new WP_Query( [
						'post_type'      => 'alkana_product',
						'posts_per_page' => 6,
						'post_status'    => 'publish',
					] );
					if ( $fallback_query->have_posts() ) {
						while ( $fallback_query->have_posts() ) {
							$fallback_query->the_post();
							get_template_part( 'template-parts/product-card' );
						}
						wp_reset_postdata();
					}
				}
				?>
			</div>

			<div class="mt-10 text-center">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_product' ) ); ?>"
				   class="btn btn--outline">
					<?php esc_html_e( 'View All Products', 'alkana' ); ?>
				</a>
			</div>
		</div>
	</section>

	<?php // ── Recent Projects ────────────────────────────────────────────────── ?>
	<section class="section section--projects py-20 bg-white">
		<div class="max-w-7xl mx-auto px-4">
			<h2 class="section__title text-3xl md:text-4xl font-extrabold text-center text-[#1A3A5C] mb-12">
				<?php esc_html_e( 'Recent Projects', 'alkana' ); ?>
			</h2>
			<div class="project-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
				<?php
				$project_query = new WP_Query( [
					'post_type'      => 'alkana_project',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
				] );

				if ( $project_query->have_posts() ) {
					while ( $project_query->have_posts() ) {
						$project_query->the_post();
						get_template_part( 'template-parts/project-card' );
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/testimonials' ); ?>

</main>

<?php
get_template_part( 'template-parts/sticky-cta-mobile' );
get_footer();
