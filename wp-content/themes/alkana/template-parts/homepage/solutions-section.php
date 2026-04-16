<?php
/**
 * Homepage — Solutions Section.
 * 4 application-domain solution cards with icon, title, description, link.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$solutions = [
	[
		'title' => __( 'Sơn công nghiệp', 'alkana' ),
		'desc'  => __( 'Lớp phủ bảo vệ chống ăn mòn, chịu hóa chất cho nhà máy, kho bãi và kết cấu thép.', 'alkana' ),
		'slug'  => 'son-cong-nghiep',
		'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
		'color' => 'bg-blue-500',
	],
	[
		'title' => __( 'Sơn gỗ nội thất', 'alkana' ),
		'desc'  => __( 'Dòng sơn PU, NC và nước cao cấp cho đồ gỗ, tủ bếp và sàn gỗ.', 'alkana' ),
		'slug'  => 'son-go',
		'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
		'color' => 'bg-amber-500',
	],
	[
		'title' => __( 'Sơn chống thấm', 'alkana' ),
		'desc'  => __( 'Giải pháp chống thấm toàn diện cho mái, tường, tầng hầm và bể chứa.', 'alkana' ),
		'slug'  => 'son-chong-tham',
		'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />',
		'color' => 'bg-cyan-500',
	],
	[
		'title' => __( 'Phụ gia xây dựng', 'alkana' ),
		'desc'  => __( 'Các loại phụ gia vữa, bê tông tăng cường độ bám dính và độ bền công trình.', 'alkana' ),
		'slug'  => 'phu-gia',
		'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.78 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />',
		'color' => 'bg-green-500',
	],
];
?>

<section class="section section--warm" id="solutions">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="section__header">
			<p class="section__label"><?php esc_html_e( 'Ứng dụng', 'alkana' ); ?></p>
			<h2 class="section__title"><?php esc_html_e( 'Giải pháp cho mọi công trình', 'alkana' ); ?></h2>
			<p class="section__desc"><?php esc_html_e( 'Từ công nghiệp nặng đến dân dụng cao cấp — Alkana có sản phẩm phù hợp cho từng yêu cầu kỹ thuật.', 'alkana' ); ?></p>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
			<?php foreach ( $solutions as $s ) :
				$term_link = get_term_link( $s['slug'], 'product_cat' );
				$link      = is_wp_error( $term_link ) ? '#' : esc_url( $term_link );
			?>
			<div class="card card--elevated group flex flex-col p-6">
				<div class="w-14 h-14 rounded-xl <?php echo esc_attr( $s['color'] ); ?> bg-opacity-10 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
					<svg class="w-7 h-7 <?php echo esc_attr( str_replace( 'bg-', 'text-', $s['color'] ) ); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<?php echo $s['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG ?>
					</svg>
				</div>
				<h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo esc_html( $s['title'] ); ?></h3>
				<p class="text-gray-600 text-sm flex-1"><?php echo esc_html( $s['desc'] ); ?></p>
				<a href="<?php echo $link; // phpcs:ignore ?>"
				   class="mt-5 inline-flex items-center text-alkana-purple-700 font-semibold text-sm hover:text-alkana-purple-900 transition-colors">
					<?php esc_html_e( 'Xem sản phẩm', 'alkana' ); ?>
					<svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
					</svg>
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
