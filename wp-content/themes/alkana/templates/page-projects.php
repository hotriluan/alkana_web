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

	<section class="page-hero bg-gradient-to-br from-alkana-purple-950 to-alkana-purple-800 text-white py-20">
		<div class="container mx-auto px-4">
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Dự án tiêu biểu', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-3 text-lg text-white/80 max-w-xl"><?php esc_html_e( 'Những công trình tiêu biểu sử dụng giải pháp sơn Alkana trên toàn quốc.', 'alkana' ); ?></p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php // ── Project Type Filter Tabs ──────────────────────────────────── ?>
		<?php
		$proj_terms = get_terms( [
			'taxonomy'   => 'project_type',
			'hide_empty' => true,
		] );
		if ( ! is_wp_error( $proj_terms ) && $proj_terms ) :
		?>
			<div class="flex flex-wrap gap-2 mb-8" id="project-type-tabs" role="tablist">
				<button class="proj-tab px-4 py-2 text-sm font-semibold rounded-full bg-alkana-purple-600 text-white transition-colors is-active" data-type="all" role="tab" aria-selected="true">
					<?php esc_html_e( 'Tất cả', 'alkana' ); ?>
				</button>
				<?php foreach ( $proj_terms as $pterm ) : ?>
					<button class="proj-tab px-4 py-2 text-sm font-semibold rounded-full border border-alkana-purple-200 text-alkana-purple-700 hover:bg-alkana-purple-600 hover:text-white hover:border-alkana-purple-600 transition-colors" data-type="<?php echo esc_attr( $pterm->slug ); ?>" role="tab" aria-selected="false">
						<?php echo esc_html( $pterm->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="project-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="project-grid">
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
					$ptypes      = get_the_terms( get_the_ID(), 'project_type' );
					$ptype_slugs = ( $ptypes && ! is_wp_error( $ptypes ) ) ? implode( ' ', wp_list_pluck( $ptypes, 'slug' ) ) : '';
					echo '<div class="project-card-wrapper" data-type="' . esc_attr( $ptype_slugs ) . '">';
					get_template_part( 'template-parts/project-card' );
					echo '</div>';
				}
				wp_reset_postdata();
			}
			?>
		</div>

		<?php
		// Store query for pagination
		global $wp_query;
		$wp_query = $project_query;
		get_template_part( 'template-parts/pagination' );
		wp_reset_postdata();
		?>

	</div>
</main>

<script>
(function () {
	const tabs  = document.querySelectorAll('#project-type-tabs .proj-tab');
	const cards = document.querySelectorAll('#project-grid .project-card-wrapper');
	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			const type = tab.dataset.type;
			tabs.forEach(function (t) {
				t.classList.remove('is-active', 'bg-alkana-purple-600', 'text-white');
				t.classList.add('border', 'border-alkana-purple-200', 'text-alkana-purple-700');
				t.setAttribute('aria-selected', 'false');
			});
			tab.classList.add('is-active', 'bg-alkana-purple-600', 'text-white');
			tab.classList.remove('border', 'border-alkana-purple-200', 'text-alkana-purple-700');
			tab.setAttribute('aria-selected', 'true');
			cards.forEach(function (card) {
				if (type === 'all' || card.dataset.type.split(' ').includes(type)) {
					card.style.display = '';
				} else {
					card.style.display = 'none';
				}
			});
		});
	});
})();
</script>

<?php get_template_part( 'template-parts/footer' ); ?>
