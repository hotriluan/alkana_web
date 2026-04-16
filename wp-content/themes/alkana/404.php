<?php
/**
 * 404 Error Page Template
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

// Featured products for suggestions (4 random featured items)
$suggest_query = new WP_Query( [
	'post_type'           => 'alkana_product',
	'posts_per_page'      => 4,
	'orderby'             => 'rand',
	'meta_query'          => [
		[ 'key' => '_alkana_featured', 'value' => '1', 'compare' => '=' ],
	],
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
] );
?>

<main id="main-content" class="site-main">

	<!-- ── Hero ───────────────────────────────────────────────────────────── -->
	<section class="bg-gradient-to-br from-alkana-purple-800 to-alkana-purple-900 text-white py-20 relative overflow-hidden">
		<!-- decorative 404 watermark -->
		<p class="absolute inset-0 flex items-center justify-center text-[20rem] font-extrabold text-white/[0.04] select-none pointer-events-none leading-none" aria-hidden="true">404</p>

		<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
			<div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/10 border border-white/20 mb-6">
				<svg class="w-10 h-10 text-white/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
				</svg>
			</div>

			<h1 class="text-3xl md:text-4xl font-bold font-heading mb-3">
				<?php esc_html_e( 'Không tìm thấy trang', 'alkana' ); ?>
			</h1>
			<p class="text-white/70 max-w-lg mx-auto mb-8">
				<?php esc_html_e( 'Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.', 'alkana' ); ?>
			</p>

			<!-- Search -->
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
				  class="flex max-w-lg mx-auto gap-2">
				<label for="s-404" class="sr-only"><?php esc_html_e( 'Tìm kiếm sản phẩm', 'alkana' ); ?></label>
				<input id="s-404" type="search" name="s"
					   placeholder="<?php esc_attr_e( 'Tìm kiếm sản phẩm...', 'alkana' ); ?>"
					   class="flex-1 px-4 py-2.5 rounded-lg text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-alkana-purple-400"
					   autocomplete="off"
					   value="<?php the_search_query(); ?>">
				<button type="submit"
						class="px-5 py-2.5 bg-white text-alkana-purple-700 rounded-lg text-sm font-semibold hover:bg-alkana-purple-50 transition-colors">
					<?php esc_html_e( 'Tìm', 'alkana' ); ?>
				</button>
			</form>
		</div>
	</section>

	<!-- ── Quick Nav ──────────────────────────────────────────────────────── -->
	<section class="py-10 bg-gray-50 border-b border-gray-100">
		<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
				<?php
				$nav_links = [
					[
						'href'  => home_url( '/' ),
						'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
						'label' => 'Trang chủ',
					],
					[
						'href'  => (string) get_post_type_archive_link( 'alkana_product' ),
						'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
						'label' => 'Sản phẩm',
					],
					[
						'href'  => (string) get_permalink( get_page_by_path( 'he-thong-son' ) ),
						'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>',
						'label' => 'Tư vấn hệ sơn',
					],
					[
						'href'  => alkana_get_contact_url() ?: home_url( '/lien-he' ),
						'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>',
						'label' => 'Liên hệ',
					],
				];
				foreach ( $nav_links as $link ) : ?>
				<a href="<?php echo esc_url( $link['href'] ?: home_url( '/' ) ); ?>"
				   class="group flex flex-col items-center gap-2 p-5 bg-white rounded-xl border border-gray-100
						  hover:border-alkana-purple-300 hover:shadow-md transition-all duration-200 text-center">
					<div class="w-10 h-10 rounded-full bg-alkana-purple-50 flex items-center justify-center text-alkana-purple-600
								group-hover:bg-alkana-purple-100 transition-colors">
						<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
							<?php echo $link['icon']; // safe static SVG ?>
						</svg>
					</div>
					<span class="text-sm font-semibold text-gray-700 group-hover:text-alkana-purple-700 transition-colors">
						<?php echo esc_html( $link['label'] ); ?>
					</span>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ── Featured Products ──────────────────────────────────────────────── -->
	<?php if ( $suggest_query->have_posts() ) : ?>
	<section class="py-12">
		<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
			<h2 class="text-lg font-bold text-alkana-purple-900 mb-6 text-center">
				<?php esc_html_e( 'Sản phẩm nổi bật', 'alkana' ); ?>
			</h2>
			<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
				<?php while ( $suggest_query->have_posts() ) : $suggest_query->the_post(); ?>
				<a href="<?php the_permalink(); ?>"
				   class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md hover:border-alkana-purple-200 transition-all duration-200">
					<?php if ( has_post_thumbnail() ) : ?>
					<div class="aspect-square overflow-hidden bg-gray-50">
						<?php the_post_thumbnail( 'medium', [
							'class'   => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
							'loading' => 'lazy',
						] ); ?>
					</div>
					<?php else : ?>
					<div class="aspect-square bg-alkana-purple-50 flex items-center justify-center">
						<svg class="w-10 h-10 text-alkana-purple-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
							<rect x="3" y="3" width="18" height="18" rx="2"/>
							<circle cx="8.5" cy="8.5" r="1.5"/>
							<path d="M21 15l-5-5L5 21"/>
						</svg>
					</div>
					<?php endif; ?>
					<div class="p-3">
						<p class="text-xs font-semibold text-gray-800 line-clamp-2 group-hover:text-alkana-purple-700 transition-colors">
							<?php the_title(); ?>
						</p>
					</div>
				</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>

</main>

<?php get_template_part( 'template-parts/footer' ); ?>

