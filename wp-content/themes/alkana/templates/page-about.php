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
?>

<main id="main-content" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<?php // ── Hero Section ─────────────────────────────────────────────── ?>
	<section class="page-hero relative min-h-[40vh] flex items-center overflow-hidden bg-[--color-secondary]">
		<?php 
		$img_id = is_array( $hero_image ) ? ( $hero_image['ID'] ?? 0 ) : (int) $hero_image;
		if ( $img_id ) : ?>
			<?php echo wp_get_attachment_image( $img_id, 'full', false, [
				'class'         => 'absolute inset-0 w-full h-full object-cover z-0',
				'alt'           => '',
				'fetchpriority' => 'high',
				'loading'       => 'eager',
			] ); ?>
			<div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-black/20 z-10" aria-hidden="true"></div>
		<?php endif; ?>
		
		<div class="relative z-20 container mx-auto px-4 py-16">
			<h1 class="text-3xl md:text-5xl font-heading font-bold text-white mb-4"><?php the_title(); ?></h1>
			<p class="text-lg md:text-xl text-white/90 max-w-2xl"><?php echo esc_html( $page_subtitle ); ?></p>
		</div>
	</section>

	<?php // ── Company Introduction ──────────────────────────────────────── ?>
	<section class="py-16 bg-white">
		<div class="container mx-auto px-4">
			<div class="prose prose-lg max-w-4xl mx-auto">
				<?php the_content(); ?>
			</div>
		</div>
	</section>

	<?php // ── Company Timeline ──────────────────────────────────────────── ?>
	<section class="py-20 bg-gray-50">
		<div class="container mx-auto px-4">
			<h2 class="text-3xl md:text-4xl font-heading font-bold text-[--color-secondary] text-center mb-16">Hành trình phát triển</h2>
			
			<div class="timeline relative max-w-3xl mx-auto">
				<!-- Vertical line -->
				<div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-gray-300 -translate-x-1/2" aria-hidden="true"></div>
				
				<!-- Timeline items -->
				<?php 
				$milestones = [
					[ 'year' => '2008', 'desc' => 'Thành lập công ty với sứ mệnh mang đến giải pháp sơn chất lượng cao' ],
					[ 'year' => '2012', 'desc' => 'Đạt chứng nhận ISO 9001:2015 về quản lý chất lượng' ],
					[ 'year' => '2016', 'desc' => 'Mở rộng nhà máy sản xuất, nâng công suất lên 5,000 tấn/năm' ],
					[ 'year' => '2019', 'desc' => 'Hoàn thành hơn 300 dự án công nghiệp lớn trên toàn quốc' ],
					[ 'year' => '2023', 'desc' => 'Phủ sóng 63/63 tỉnh thành với mạng lưới đại lý và đối tác' ],
					[ 'year' => '2024', 'desc' => 'Ra mắt phòng R&D hiện đại, nghiên cứu công nghệ sơn thế hệ mới' ]
				];
				
				foreach ( $milestones as $index => $m ) :
					$position_class = ( $index % 2 === 0 ) ? 'md:pr-12 md:text-right' : 'md:ml-auto md:pl-12';
				?>
					<div class="timeline-item relative pl-12 md:pl-0 mb-12 <?php echo esc_attr( $position_class ); ?> md:w-1/2">
						<!-- Dot -->
						<div class="absolute left-4 md:left-1/2 top-0 w-4 h-4 rounded-full bg-[--color-primary] border-4 border-white shadow-lg -translate-x-1/2 z-10"></div>
						
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
	<section class="py-20 bg-white">
		<div class="max-w-7xl mx-auto px-4">
			<div class="lg:grid lg:grid-cols-2 lg:gap-12 items-center">
				<!-- Image -->
				<div class="mb-8 lg:mb-0">
					<?php 
					$factory_id = is_array( $factory_image ) ? ( $factory_image['ID'] ?? 0 ) : (int) $factory_image;
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
					<h2 class="text-3xl md:text-4xl font-heading font-bold text-[--color-secondary] mb-6">Nhà máy sản xuất</h2>
					<p class="text-gray-700 text-lg leading-relaxed mb-6">
						Nhà máy sản xuất của Alkana được trang bị hệ thống công nghệ hiện đại, đạt tiêu chuẩn quốc tế.
						Chúng tôi cam kết mang đến những sản phẩm sơn chất lượng cao, đáp ứng mọi nhu cầu của khách hàng trong và ngoài nước.
					</p>
					
					<ul class="space-y-3">
						<li class="flex items-start">
							<svg class="w-6 h-6 text-[--color-primary] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
							<span class="text-gray-700"><strong class="text-[--color-secondary]">Diện tích:</strong> 10,000m² khu vực sản xuất và kho bãi</span>
						</li>
						<li class="flex items-start">
							<svg class="w-6 h-6 text-[--color-primary] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
							<span class="text-gray-700"><strong class="text-[--color-secondary]">Công suất:</strong> 5,000 tấn sản phẩm mỗi năm</span>
						</li>
						<li class="flex items-start">
							<svg class="w-6 h-6 text-[--color-primary] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
							<span class="text-gray-700"><strong class="text-[--color-secondary]">Công nghệ:</strong> Hệ thống tự động hóa và kiểm soát chất lượng</span>
						</li>
						<li class="flex items-start">
							<svg class="w-6 h-6 text-[--color-primary] mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
							<span class="text-gray-700"><strong class="text-[--color-secondary]">Chất lượng:</strong> Chứng nhận ISO 9001:2015</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<?php // ── Team / Leadership ──────────────────────────────────────────── ?>
	<section class="py-20 bg-gray-50">
		<div class="container mx-auto px-4">
			<h2 class="text-3xl md:text-4xl font-heading font-bold text-[--color-secondary] text-center mb-12">Đội ngũ lãnh đạo</h2>
			
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
				<?php 
				$team = [
					[ 'name' => 'Nguyễn Văn Minh', 'position' => 'Giám đốc điều hành' ],
					[ 'name' => 'Trần Thị Hương', 'position' => 'Giám đốc kỹ thuật' ],
					[ 'name' => 'Lê Hoàng Nam', 'position' => 'Giám đốc kinh doanh' ]
				];
				
				foreach ( $team as $member ) : ?>
					<div class="text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
						<!-- Avatar placeholder -->
						<div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-[--color-primary]/20 to-[--color-secondary]/20 flex items-center justify-center">
							<svg class="w-16 h-16 text-[--color-secondary]/40" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<h3 class="text-xl font-heading font-semibold text-[--color-secondary] mb-1"><?php echo esc_html( $member['name'] ); ?></h3>
						<p class="text-sm text-gray-500"><?php echo esc_html( $member['position'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php // ── CTA Section ────────────────────────────────────────────────── ?>
	<section class="bg-[--color-secondary] py-16 text-center">
		<div class="container mx-auto px-4">
			<h2 class="text-3xl md:text-4xl font-heading font-bold text-white mb-4">Sẵn sàng hợp tác cùng Alkana?</h2>
			<p class="text-lg text-white/80 mb-8 max-w-2xl mx-auto">
				Liên hệ với chúng tôi ngay hôm nay để nhận tư vấn chi tiết về giải pháp sơn công nghiệp phù hợp nhất cho dự án của bạn.
			</p>
			
			<div class="flex flex-col sm:flex-row gap-4 justify-center">
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="inline-block bg-[--color-primary] text-white px-8 py-4 rounded-md font-bold hover:bg-orange-600 transition-all duration-300 shadow-lg hover:shadow-orange-500/30 hover:-translate-y-1">
					Liên hệ ngay
				</a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_product' ) ); ?>" class="inline-block bg-transparent border-2 border-white text-white px-8 py-4 rounded-md font-bold hover:bg-white hover:text-[--color-secondary] transition-all duration-300">
					Xem sản phẩm
				</a>
			</div>
		</div>
	</section>

<?php endwhile; endif; ?>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
