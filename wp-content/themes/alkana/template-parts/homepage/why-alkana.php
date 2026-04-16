<?php
/**
 * Homepage — Why Alkana Section.
 * Company story: image + text side-by-side with key differentiators.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$reasons = [
	[
		'title' => __( 'Công nghệ R&D riêng', 'alkana' ),
		'desc'  => __( 'Phòng lab hiện đại — liên tục cải tiến công thức cho từng thị trường.', 'alkana' ),
	],
	[
		'title' => __( 'Đội ngũ kỹ thuật tận nơi', 'alkana' ),
		'desc'  => __( 'Chuyên viên hỗ trợ ứng dụng tại công trình, đảm bảo thi công đúng quy trình.', 'alkana' ),
	],
	[
		'title' => __( 'Cam kết sau bán hàng', 'alkana' ),
		'desc'  => __( 'Bảo hành chất lượng lớp phủ, hỗ trợ kỹ thuật 24/7 xuyên suốt dự án.', 'alkana' ),
	],
	[
		'title' => __( 'Tiêu chuẩn quốc tế', 'alkana' ),
		'desc'  => __( 'Sản phẩm đạt chứng nhận ISO 9001, REACH và nhiều tiêu chuẩn châu Âu.', 'alkana' ),
	],
];

$about_img_id = get_theme_mod( 'alkana_about_image', 0 );
$about_img    = $about_img_id ? wp_get_attachment_image_url( $about_img_id, 'large' ) : get_template_directory_uri() . '/assets/images/about-placeholder.jpg';
?>

<section class="section section--cool" id="why-alkana">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

			<!-- Image column -->
			<div class="relative order-2 lg:order-1">
				<div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl">
					<img src="<?php echo esc_url( $about_img ); ?>"
					     alt="<?php esc_attr_e( 'Về Alkana — nhà máy sơn chuyên dụng', 'alkana' ); ?>"
					     loading="lazy"
					     class="w-full h-full object-cover">
				</div>
				<!-- Badge -->
				<div class="absolute -bottom-6 -right-6 bg-white rounded-2xl shadow-xl p-5 text-center hidden lg:block">
					<p class="text-4xl font-extrabold text-alkana-purple-700">20+</p>
					<p class="text-xs text-gray-500 font-medium uppercase tracking-wide mt-1"><?php esc_html_e( 'Năm kinh nghiệm', 'alkana' ); ?></p>
				</div>
			</div>

			<!-- Text column -->
			<div class="order-1 lg:order-2">
				<p class="section__label"><?php esc_html_e( 'Về chúng tôi', 'alkana' ); ?></p>
				<h2 class="section__title text-left mx-0 after:mx-0">
					<?php esc_html_e( 'Đồng hành cùng công trình Việt hơn 2 thập kỷ', 'alkana' ); ?>
				</h2>
				<p class="text-gray-600 mt-4 mb-8 leading-relaxed">
					<?php esc_html_e( 'Alkana là thương hiệu sơn công nghiệp và dân dụng hàng đầu, được thành lập với sứ mệnh cung cấp giải pháp lớp phủ hiệu quả — bảo vệ bền vững cho mọi công trình xây dựng Việt Nam.', 'alkana' ); ?>
				</p>

				<ul class="space-y-5" role="list">
					<?php foreach ( $reasons as $r ) : ?>
					<li class="flex gap-4">
						<span class="shrink-0 w-6 h-6 mt-0.5 rounded-full bg-alkana-purple-100 flex items-center justify-center">
							<svg class="w-3.5 h-3.5 text-alkana-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
							</svg>
						</span>
						<div>
							<p class="font-semibold text-gray-900 text-sm"><?php echo esc_html( $r['title'] ); ?></p>
							<p class="text-gray-500 text-sm mt-0.5"><?php echo esc_html( $r['desc'] ); ?></p>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>

				<div class="mt-8">
					<a href="<?php echo esc_url( get_page_link( get_page_by_path( 'gioi-thieu' ) ) ); ?>"
					   class="btn btn--gradient">
						<?php esc_html_e( 'Tìm hiểu về Alkana', 'alkana' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
