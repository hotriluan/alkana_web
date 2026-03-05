<?php
/**
 * Template Name: Front Page
 * Home page template with hero banner, featured products, and about section
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">

	<?php get_template_part( 'template-parts/hero-banner' ); ?>

	<?php // ── Featured Products ──────────────────────────────────────────────── ?>
	<section class="section section--products py-16 bg-gray-50">
		<div class="container mx-auto px-4">
			<h2 class="section__title text-2xl font-heading font-bold text-[--color-secondary] mb-8">
				<?php esc_html_e( 'Featured Products', 'alkana' ); ?>
			</h2>
			<div class="product-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="product-grid">
				<?php
				$featured_query = new WP_Query( [
					'post_type'      => 'alkana_product',
					'posts_per_page' => 6,
					'meta_key'       => 'is_featured',
					'meta_value'     => '1',
					'post_status'    => 'publish',
				] );

				if ( $featured_query->have_posts() ) {
					while ( $featured_query->have_posts() ) {
						$featured_query->the_post();
						get_template_part( 'template-parts/product-card' );
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
	</section>

	<?php // ── Recent Projects ────────────────────────────────────────────────── ?>
	<section class="section section--projects py-16">
		<div class="container mx-auto px-4">
			<h2 class="section__title text-2xl font-heading font-bold text-[--color-secondary] mb-8">
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

</main>

<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>
<?php get_template_part( 'template-parts/footer' ); ?>
