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

	<section class="page-hero bg-gradient-to-br from-alkana-purple-950 to-alkana-purple-800 text-white py-20">
		<div class="container mx-auto px-4">
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Tin tức & Cập nhật', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-3 text-lg text-white/80">
				<?php esc_html_e( 'Thông tin mới nhất về sản phẩm, công nghệ và hoạt động của Alkana Coating.', 'alkana' ); ?>
			</p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php if ( $news_query->have_posts() ) :
			$posts_array = $news_query->posts;
			$first_post  = array_shift( $posts_array );
		?>

			<?php // ── Featured Post ──────────────────────────────────────────── ?>
			<?php if ( $first_post ) : setup_postdata( $GLOBALS['post'] = $first_post ); ?>
				<article class="featured-news-card relative overflow-hidden rounded-2xl mb-12 min-h-[420px] flex items-end shadow-xl group">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'full', false, [
							'class'   => 'absolute inset-0 w-full h-full object-cover z-0 transition-transform duration-700 group-hover:scale-105',
							'loading' => 'eager',
							'alt'     => '',
						] ); ?>
					<?php endif; ?>
					<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent z-10" aria-hidden="true"></div>
					<div class="relative z-20 p-8 md:p-12">
						<?php $feat_cats = get_the_category(); if ( $feat_cats ) : ?>
							<span class="inline-block px-3 py-1 text-xs font-semibold bg-alkana-purple-600 text-white rounded-full mb-3">
								<?php echo esc_html( $feat_cats[0]->name ); ?>
							</span>
						<?php endif; ?>
						<h2 class="text-2xl md:text-3xl font-heading font-bold text-white mb-3 max-w-2xl">
							<a href="<?php the_permalink(); ?>" class="hover:text-alkana-purple-200 transition-colors"><?php the_title(); ?></a>
						</h2>
						<p class="text-white/80 text-sm md:text-base line-clamp-2 max-w-xl mb-4"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25, '…' ) ); ?></p>
						<div class="flex items-center gap-4 text-white/60 text-xs">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						</div>
					</div>
				</article>
				<?php wp_reset_postdata(); ?>
			<?php endif; ?>

			<?php // ── Category Filter Tabs ───────────────────────────────────── ?>
			<?php
			$news_cats = get_categories( [ 'hide_empty' => true ] );
			if ( $news_cats ) :
			?>
				<div class="flex flex-wrap gap-2 mb-8" id="news-cat-tabs" role="tablist">
					<button class="news-cat-tab px-4 py-2 text-sm font-semibold rounded-full bg-alkana-purple-600 text-white transition-colors is-active" data-cat="all" role="tab" aria-selected="true">
						<?php esc_html_e( 'Tất cả', 'alkana' ); ?>
					</button>
					<?php foreach ( $news_cats as $ncat ) : ?>
						<button class="news-cat-tab px-4 py-2 text-sm font-semibold rounded-full border border-alkana-purple-200 text-alkana-purple-700 hover:bg-alkana-purple-600 hover:text-white hover:border-alkana-purple-600 transition-colors" data-cat="<?php echo esc_attr( $ncat->slug ); ?>" role="tab" aria-selected="false">
							<?php echo esc_html( $ncat->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="news-grid" data-reveal-stagger>
				<?php while ( $news_query->have_posts() ) : $news_query->the_post();
					$nc = get_the_category();
					$nc_slug = $nc ? esc_attr( $nc[0]->slug ) : '';
				?>
					<article class="news-card bg-white rounded-xl overflow-hidden shadow-sm border border-[--color-border] flex flex-col" data-cat="<?php echo $nc_slug; ?>">
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

<script>
(function () {
	const tabs = document.querySelectorAll('#news-cat-tabs .news-cat-tab');
	const cards = document.querySelectorAll('#news-grid .news-card');
	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			const cat = tab.dataset.cat;
			tabs.forEach(function (t) {
				t.classList.remove('is-active', 'bg-alkana-purple-600', 'text-white');
				t.classList.add('border', 'border-alkana-purple-200', 'text-alkana-purple-700');
				t.setAttribute('aria-selected', 'false');
			});
			tab.classList.add('is-active', 'bg-alkana-purple-600', 'text-white');
			tab.classList.remove('border', 'border-alkana-purple-200', 'text-alkana-purple-700');
			tab.setAttribute('aria-selected', 'true');
			cards.forEach(function (card) {
				if (cat === 'all' || card.dataset.cat === cat) {
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
