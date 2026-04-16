<?php
/**
 * Template Name: About Us
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

// Get ACF fields
$hero_image    = get_field( 'hero_image' );
$page_subtitle = get_field( 'page_subtitle' ) ?: 'Đơn vị tiên phong trong giải pháp sơn công nghiệp tại Việt Nam';
$factory_image = get_field( 'factory_image' );

// Get About settings
$about = alkana_get_about_settings();
?>

<main id="main-content" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<?php // ── Hero Section ─────────────────────────────────────────────── ?>
	<section class="page-hero relative min-h-[52vh] flex items-end overflow-hidden bg-alkana-navy">
		<?php
		$img_id = is_array( $hero_image ) ? ( $hero_image['ID'] ?? 0 ) : (int) $hero_image;
		if ( $img_id ) : ?>
			<?php echo wp_get_attachment_image( $img_id, 'full', false, [
				'class'         => 'absolute inset-0 w-full h-full object-cover z-0',
				'alt'           => '',
				'fetchpriority' => 'high',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			] ); ?>
			<div class="absolute inset-0 bg-gradient-to-r from-alkana-purple-950/80 via-black/50 to-black/20 z-10" aria-hidden="true"></div>
		<?php else : ?>
			<div class="absolute inset-0 bg-gradient-to-br from-alkana-purple-950 to-alkana-purple-800"></div>
		<?php endif; ?>
		<div class="relative z-20 container mx-auto px-4 pb-16 pt-32">
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Về chúng tôi', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-6xl font-heading font-bold text-white mb-4 max-w-2xl leading-tight"><?php the_title(); ?></h1>
			<p class="text-lg md:text-xl text-white/80 max-w-xl"><?php echo esc_html( $page_subtitle ); ?></p>
		</div>
	</section>

	<?php // ── Company Introduction ──────────────────────────────────────── ?>
	<section class="py-20 bg-white" data-reveal>
		<div class="max-w-7xl mx-auto px-4">
			<div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
				<div class="mb-10 lg:mb-0">
					<span class="inline-block px-3 py-1 text-xs font-semibold text-alkana-purple-700 bg-alkana-purple-50 rounded-full uppercase tracking-wider mb-4"><?php esc_html_e( 'Câu chuyện Alkana', 'alkana' ); ?></span>
					<div class="prose prose-lg prose-headings:font-heading prose-headings:text-alkana-purple-900 prose-a:text-alkana-purple-600 max-w-none">
						<?php the_content(); ?>
					</div>
				</div>
				<?php
				$factory_id_intro = absint( $about['factory_image_id'] ?? 0 );
				if ( ! $factory_id_intro ) {
					$factory_id_intro = is_array( $factory_image ) ? ( $factory_image['ID'] ?? 0 ) : (int) $factory_image;
				}
				if ( $factory_id_intro ) : ?>
					<div class="relative">
						<?php echo wp_get_attachment_image( $factory_id_intro, 'large', false, [
							'class'   => 'w-full h-auto rounded-2xl shadow-2xl',
							'alt'     => 'Alkana Coating factory',
							'loading' => 'lazy',
							'decoding' => 'async',
						] ); ?>
						<div class="absolute -bottom-4 -right-4 w-28 h-28 bg-alkana-purple-100 rounded-2xl -z-10" aria-hidden="true"></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php // ── Company Timeline ──────────────────────────────────────────── ?>
	<section class="py-20 bg-[#F8F4FF]" data-reveal>
		<div class="container mx-auto px-4">
			<p class="text-xs font-semibold uppercase tracking-widest text-alkana-purple-500 text-center mb-2"><?php esc_html_e( 'Lịch sử phát triển', 'alkana' ); ?></p>
			<h2 class="text-3xl md:text-4xl font-heading font-bold text-[--color-secondary] text-center mb-4"><?php echo esc_html( $about['timeline_title'] ); ?></h2>
			<p class="text-gray-500 text-center mb-16 max-w-xl mx-auto"><?php esc_html_e( 'Những cột mốc quan trọng hình thành nên Alkana Coating ngày hôm nay.', 'alkana' ); ?></p>
			
			<div class="timeline relative max-w-3xl mx-auto">
				<!-- Vertical line -->
				<div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-gray-300 -translate-x-1/2" aria-hidden="true"></div>
				
				<!-- Timeline items -->
				<?php 
				foreach ( $about['timeline_milestones'] as $index => $m ) :
					$position_class = ( $index % 2 === 0 ) ? 'md:pr-12 md:text-right' : 'md:ml-auto md:pl-12';
				?>
					<div class="timeline-item relative pl-12 md:pl-0 mb-12 <?php echo esc_attr( $position_class ); ?> md:w-1/2">
						<!-- Dot -->
						<div class="absolute left-4 md:left-1/2 top-0 w-4 h-4 rounded-full bg-alkana-purple-600 border-4 border-white shadow-lg -translate-x-1/2 z-10">
							<span class="absolute inset-0 rounded-full bg-alkana-purple-400 animate-ping opacity-50" aria-hidden="true"></span>
						</div>
						
						<!-- Content -->
						<div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
							<span class="text-3xl md:text-4xl font-heading font-bold text-[--color-primary] block mb-2"><?php echo esc_html( $m['year'] ); ?></span>
							<p class="text-gray-700 leading-relaxed"><?php echo esc_html( $m['desc'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php // ── Factory / Facility ─────────────────────────────────────────── ?>
	<section class="py-20 bg-white" data-reveal>
		<div class="max-w-7xl mx-auto px-4">
			<div class="lg:grid lg:grid-cols-2 lg:gap-12 items-center">
				<!-- Image -->
				<div class="mb-8 lg:mb-0">
					<?php
					// Settings option takes priority; fall back to ACF field.
					$factory_id = absint( $about['factory_image_id'] ?? 0 );
					if ( ! $factory_id ) {
						$factory_id = is_array( $factory_image ) ? ( $factory_image['ID'] ?? 0 ) : (int) $factory_image;
					}
					if ( $factory_id ) {
						echo wp_get_attachment_image( $factory_id, 'large', false, [
							'class'   => 'w-full h-auto rounded-lg shadow-xl',
							'alt'     => 'Nhà máy sản xuất Alkana',
							'loading' => 'lazy',
						] );
					} else {
						echo '<div class="w-full aspect-video bg-gray-200 rounded-lg flex items-center justify-center">';
						echo '<svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>';
						echo '</div>';
					}
					?>
				</div>
				
				<!-- Text -->
				<div>
					<h2 class="text-3xl md:text-4xl font-heading font-bold text-[--color-secondary] mb-6"><?php echo esc_html( $about['factory_title'] ); ?></h2>
					<p class="text-gray-700 text-lg leading-relaxed mb-6">
						<?php echo nl2br( esc_html( $about['factory_intro'] ) ); ?>
					</p>
					
					<ul class="space-y-3">
						<?php foreach ( $about['factory_specs'] as $spec ) : ?>
						<li class="flex items-start">
							<svg class="w-6 h-6 text-[--color-primary] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
							<span class="text-gray-700"><strong class="text-[--color-secondary]"><?php echo esc_html( $spec['label'] ); ?>:</strong> <?php echo esc_html( $spec['value'] ); ?></span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<?php // ── Team / Leadership ──────────────────────────────────────────── ?>
	<section class="py-20 bg-white" data-reveal>
		<div class="container mx-auto px-4">
			<h2 class="text-3xl md:text-4xl font-heading font-bold text-[--color-secondary] text-center mb-12"><?php echo esc_html( $about['team_title'] ); ?></h2>
			
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto" data-reveal-stagger>
				<?php foreach ( $about['team_members'] as $member ) : ?>
					<div class="text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
						<!-- Avatar placeholder -->
						<div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-[--color-primary]/20 to-[--color-secondary]/20 flex items-center justify-center">
							<svg class="w-16 h-16 text-[--color-secondary]/40" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<h3 class="text-xl font-heading font-semibold text-[--color-secondary] mb-1"><?php echo esc_html( $member['name'] ); ?></h3>
						<p class="text-sm text-gray-500"><?php echo esc_html( $member['position'] ); ?></p>							<?php if ( ! empty( $member['bio'] ) ) : ?>
							<p class="text-sm text-gray-600 mt-2"><?php echo esc_html( $member['bio'] ); ?></p>
							<?php endif; ?>					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php // ── CTA Section ────────────────────────────────────────────────── ?>
	<section class="py-20 section-cta" style="background: linear-gradient(135deg, #2E0049 0%, #5B21B6 100%);">
		<div class="max-w-4xl mx-auto text-center px-4">
			<p class="text-xs font-bold tracking-widest uppercase mb-4 text-alkana-purple-300"><?php esc_html_e( 'Hợp tác cùng chúng tôi', 'alkana' ); ?></p>
			<h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4 leading-snug">
				<?php esc_html_e( 'Sẵn sàng đưa dự án của bạn', 'alkana' ); ?><br class="hidden md:block">
				<?php esc_html_e( 'lên tầm cao mới?', 'alkana' ); ?>
			</h2>
			<p class="text-lg text-white/80 mb-10 max-w-xl mx-auto">
				<?php esc_html_e( 'Liên hệ ngay để nhận tư vấn miễn phí về giải pháp sơn công nghiệp phù hợp nhất cho dự án của bạn.', 'alkana' ); ?>
			</p>
			<div class="flex flex-col sm:flex-row justify-center items-center gap-4">
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>"
				   class="bg-white text-alkana-purple-700 px-8 py-3 rounded-full font-bold hover:bg-alkana-purple-50 transition-all shadow-md hover:shadow-lg">
					<?php esc_html_e( 'Liên hệ ngay', 'alkana' ); ?>
				</a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_product' ) ); ?>"
				   class="border-2 border-white/40 text-white px-8 py-3 rounded-full font-bold hover:bg-white/10 transition-all">
					<?php esc_html_e( 'Xem sản phẩm', 'alkana' ); ?>
				</a>
			</div>
		</div>
	</section>

<?php endwhile; endif; ?>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
