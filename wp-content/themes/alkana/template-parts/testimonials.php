<?php
/**
 * Testimonials Section
 * Customer testimonials in a 3-column grid.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="section section--testimonials py-20 bg-gray-50">
	<div class="max-w-7xl mx-auto px-4">
		<h2 class="text-3xl md:text-4xl font-extrabold text-center text-[#1A3A5C] mb-12">
			Khách hàng nói gì về chúng tôi
		</h2>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
			
			<!-- Testimonial 1 -->
			<div class="bg-white p-6 rounded-lg shadow-md">
				<div class="flex gap-1 mb-4" aria-label="5 sao">
					<?php for ( $i = 0; $i < 5; $i++ ) : ?>
						<svg class="w-5 h-5 text-[#E8611A]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
							<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
						</svg>
					<?php endfor; ?>
				</div>
				<p class="text-gray-600 italic mb-4">
					"Chất lượng sơn tuyệt vời, đội ngũ thi công chuyên nghiệp. Dự án nhà máy của chúng tôi hoàn thành đúng tiến độ và vượt mong đợi."
				</p>
				<p class="font-semibold text-[#1A3A5C] mb-1">Nguyễn Văn An</p>
				<p class="text-sm text-gray-400">Giám đốc, Công ty TNHH Cơ Khí Á Châu</p>
			</div>

			<!-- Testimonial 2 -->
			<div class="bg-white p-6 rounded-lg shadow-md">
				<div class="flex gap-1 mb-4" aria-label="5 sao">
					<?php for ( $i = 0; $i < 5; $i++ ) : ?>
						<svg class="w-5 h-5 text-[#E8611A]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
							<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
						</svg>
					<?php endfor; ?>
				</div>
				<p class="text-gray-600 italic mb-4">
					"Dịch vụ tư vấn nhiệt tình, giá cả hợp lý. Alkana đã giúp chúng tôi chọn được giải pháp sơn phù hợp nhất cho dự án cầu vượt."
				</p>
				<p class="font-semibold text-[#1A3A5C] mb-1">Trần Thị Bích</p>
				<p class="text-sm text-gray-400">Kỹ sư trưởng, Công ty CP Xây Dựng Đại Nam</p>
			</div>

			<!-- Testimonial 3 -->
			<div class="bg-white p-6 rounded-lg shadow-md">
				<div class="flex gap-1 mb-4" aria-label="5 sao">
					<?php for ( $i = 0; $i < 5; $i++ ) : ?>
						<svg class="w-5 h-5 text-[#E8611A]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
							<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
						</svg>
					<?php endfor; ?>
				</div>
				<p class="text-gray-600 italic mb-4">
					"Sản phẩm chống ăn mòn hiệu quả, dịch vụ hậu mãi tốt. Đã hợp tác 5 năm và rất hài lòng với Alkana."
				</p>
				<p class="font-semibold text-[#1A3A5C] mb-1">Lê Hồng Phúc</p>
				<p class="text-sm text-gray-400">Tổng giám đốc, Tập đoàn Thép Việt</p>
			</div>

		</div>
	</div>
</section>
