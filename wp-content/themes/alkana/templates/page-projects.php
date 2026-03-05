<?php
/**
 * Template Name: Projects
 * Archive-style listing for project showcase.
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

		<div class="project-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php
			$project_query = new WP_Query( [
				'post_type'      => 'alkana_project',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
				'paged'          => max( 1, get_query_var( 'paged' ) ),
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

		<?php
		// Pagination
		$big = 999999999;
		echo wp_kses_post( paginate_links( [
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => $project_query->max_num_pages,
		] ) );
		?>

	</div>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
