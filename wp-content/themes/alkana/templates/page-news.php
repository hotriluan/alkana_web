<?php
/**
 * Template Name: News
 * Blog / news listing page with pagination.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

$paged    = max( 1, get_query_var( 'paged' ) );
$per_page = 9;

$news_query = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
] );
?>

<main id="main-content" class="site-main">

	<section class="page-hero bg-[--color-secondary] text-white py-14">
		<div class="container mx-auto px-4">
			<h1 class="text-3xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-2 text-white/70">
				<?php esc_html_e( 'Latest news, insights, and updates from Alkana Coating.', 'alkana' ); ?>
			</p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php if ( $news_query->have_posts() ) : ?>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
				<?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
					<article class="news-card bg-white rounded-xl overflow-hidden shadow-sm border border-[--color-border] flex flex-col">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="block overflow-hidden aspect-[16/9]">
								<?php
								$thumb_id = get_post_thumbnail_id();
								echo wp_get_attachment_image( $thumb_id, 'alkana-project-card', false, [
									'class'   => 'w-full h-full object-cover transition-transform duration-300 hover:scale-105',
									'loading' => 'lazy',
									'decoding' => 'async',
								] );
								?>
							</a>
						<?php endif; ?>

						<div class="p-5 flex flex-col flex-1">
							<div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
									<?php echo esc_html( get_the_date() ); ?>
								</time>

								<?php
								$cats = get_the_category();
								if ( $cats ) :
									$cat = $cats[0];
								?>
									<span>·</span>
									<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
									   class="text-[--color-primary] hover:underline">
										<?php echo esc_html( $cat->name ); ?>
									</a>
								<?php endif; ?>
							</div>

							<h2 class="text-base font-heading font-semibold mb-2 line-clamp-2 flex-1">
								<a href="<?php the_permalink(); ?>" class="hover:text-[--color-primary]">
									<?php the_title(); ?>
								</a>
							</h2>

							<p class="text-sm text-gray-500 line-clamp-3 mb-4">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?>
							</p>

							<a href="<?php the_permalink(); ?>"
							   class="btn btn--outline btn--sm mt-auto self-start">
								<?php esc_html_e( 'Read more', 'alkana' ); ?>
							</a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<?php
			$pagination = paginate_links( [
				'base'    => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
				'format'  => '?paged=%#%',
				'current' => $paged,
				'total'   => $news_query->max_num_pages,
				'type'    => 'array',
			] );

			if ( $pagination ) :
			?>
				<nav class="pagination flex justify-center gap-2 mt-10" aria-label="<?php esc_attr_e( 'News pagination', 'alkana' ); ?>">
					<?php foreach ( $pagination as $link ) : ?>
						<span class="pagination__item"><?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

		<?php else : ?>

			<div class="text-center py-24 text-gray-400">
				<p class="text-lg"><?php esc_html_e( 'No news articles found.', 'alkana' ); ?></p>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
