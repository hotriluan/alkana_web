<?php
/**
 * Template Name: Contact
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

$hero_image = get_field( 'hero_image' );
$hero_img_id = is_array( $hero_image ) ? ( $hero_image['ID'] ?? 0 ) : (int) $hero_image;
?>

<main id="main-content" class="site-main">

	<section class="page-hero relative min-h-[44vh] flex items-end overflow-hidden bg-alkana-navy text-white">
		<?php if ( $hero_img_id ) : ?>
			<?php echo wp_get_attachment_image( $hero_img_id, 'full', false, [
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
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Liên hệ', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-3 text-lg text-white/80 max-w-md"><?php esc_html_e( 'Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn.', 'alkana' ); ?></p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">
		<div class="lg:grid lg:grid-cols-2 lg:gap-12">

			<?php // ── Contact Info ──────────────────────────────────────────── ?>
			<div class="contact-info mb-10 lg:mb-0">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<div class="prose mb-6"><?php the_content(); ?></div>
			<?php endwhile; endif; ?>

			<div class="space-y-6">
				<h2 class="text-2xl font-heading font-bold text-[--color-secondary]">
					<?php esc_html_e( 'Alkana Coating', 'alkana' ); ?>
				</h2>

			<?php
			$contact_address    = get_theme_mod( 'alkana_address', '' );
			$contact_phone      = get_theme_mod( 'alkana_phone', '' );
			$contact_email      = get_theme_mod( 'alkana_email', '' );
			$contact_hours      = get_theme_mod( 'alkana_hours', '' );
			$contact_map        = get_theme_mod( 'alkana_map_embed', '' );
			$contact_phone_e164 = preg_replace( '/[^+\d]/', '', $contact_phone );
			?>
				<div class="space-y-4">

					<?php if ( $contact_address ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
						</svg>
						<div>
							<p class="text-[--color-secondary] leading-relaxed">
								<?php echo esc_html( $contact_address ); ?>
							</p>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $contact_phone ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
						</svg>
						<div>
							<a href="tel:<?php echo esc_attr( $contact_phone_e164 ); ?>" class="text-[--color-secondary] hover:text-[--color-primary] transition-colors">
								<?php echo esc_html( $contact_phone ); ?>
							</a>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $contact_email ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
						</svg>
						<div>
							<a href="mailto:<?php echo esc_attr( $contact_email ); ?>" class="text-[--color-secondary] hover:text-[--color-primary] transition-colors">
								<?php echo esc_html( $contact_email ); ?>
							</a>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $contact_hours ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
						<div>
							<p class="text-[--color-secondary]">
								<?php echo esc_html( $contact_hours ); ?>
							</p>
						</div>
					</div>
					<?php endif; ?>

				</div>

				<?php if ( $contact_map ) : ?>
				<div class="mt-8">
					<div class="aspect-video rounded-xl overflow-hidden shadow-lg">
						<iframe
							src="<?php echo esc_url( $contact_map ); ?>"
							width="100%"
							height="100%"
							style="border:0;"
							allowfullscreen=""
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							title="<?php esc_attr_e( 'Alkana Coating Location', 'alkana' ); ?>">
						</iframe>
					</div>
				</div>
				<?php endif; ?>
			<div class="contact-form">
				<h3 class="text-xl font-heading font-semibold text-[--color-secondary] mb-6"><?php esc_html_e( 'Gửi yêu cầu tư vấn', 'alkana' ); ?></h3>
				<form id="contact-form" class="space-y-4" novalidate>
					<?php wp_nonce_field( 'alkana_contact', '_alkana_nonce' ); ?>

					<?php // Honeypot anti-spam field ?>
					<div class="hidden" aria-hidden="true">
						<label for="url_website"><?php esc_html_e( 'Website', 'alkana' ); ?></label>
						<input type="text" id="url_website" name="url_website" tabindex="-1" autocomplete="off">
					</div>

					<div class="form-group">
						<label class="form-label" for="contact_name">
							<?php esc_html_e( 'Họ và tên', 'alkana' ); ?> <span class="required">*</span>
						</label>
						<input type="text" id="contact_name" name="contact_name"
							   class="form-input" required autocomplete="name">
					</div>

					<div class="form-grid form-grid--2col">
						<div class="form-group">
							<label class="form-label" for="contact_email">
								<?php esc_html_e( 'Email', 'alkana' ); ?> <span class="required">*</span>
							</label>
							<input type="email" id="contact_email" name="contact_email"
								   class="form-input" required autocomplete="email">
						</div>

						<div class="form-group">
							<label class="form-label" for="contact_phone">
								<?php esc_html_e( 'Số điện thoại', 'alkana' ); ?>
							</label>
							<input type="tel" id="contact_phone" name="contact_phone"
								   class="form-input" autocomplete="tel">
						</div>
					</div>

					<div class="form-group">
						<label class="form-label" for="contact_message">
							<?php esc_html_e( 'Nội dung', 'alkana' ); ?> <span class="required">*</span>
						</label>
						<textarea id="contact_message" name="contact_message"
								  class="form-textarea" rows="5" required></textarea>
					</div>

					<div id="contact-message" class="hidden p-4 rounded-lg text-sm"></div>

					<button type="submit" class="btn btn--primary w-full">
						<span class="submit-text"><?php esc_html_e( 'Gửi yêu cầu', 'alkana' ); ?></span>
						<span class="submit-loading hidden">
							<svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
							<?php esc_html_e( 'Đang gửi...', 'alkana' ); ?>
						</span>
					</button>
				</form>
			</div>

		</div>
	</div>

	<?php // ── FAQ Section ───────────────────────────────────────────────── ?>
	<section class="py-20 bg-[#F8F4FF]">
		<div class="max-w-3xl mx-auto px-4">
			<p class="text-xs font-semibold uppercase tracking-widest text-alkana-purple-500 text-center mb-2"><?php esc_html_e( 'Giải đáp thắc mắc', 'alkana' ); ?></p>
			<h2 class="text-3xl font-heading font-bold text-[--color-secondary] text-center mb-12"><?php esc_html_e( 'Câu hỏi thường gặp', 'alkana' ); ?></h2>

			<?php
			$faqs = [
				[
					'q' => 'Alkana Coating có hỗ trợ tư vấn kỹ thuật tại dự án không?',
					'a' => 'Có. Đội ngũ kỹ thuật của chúng tôi sẵn sàng đến khảo sát và tư vấn trực tiếp tại công trình, hoàn toàn miễn phí cho các dự án đủ điều kiện.',
				],
				[
					'q' => 'Thời gian xử lý báo giá là bao lâu?',
					'a' => 'Chúng tôi cam kết phản hồi báo giá trong vòng 24 giờ làm việc kể từ khi nhận được thông tin đầy đủ về dự án.',
				],
				[
					'q' => 'Alkana có cung cấp dịch vụ thi công sơn không?',
					'a' => 'Alkana chuyên cung cấp vật liệu sơn công nghiệp. Chúng tôi có thể giới thiệu đội ngũ thi công được chứng nhận kỹ thuật theo từng dòng sản phẩm.',
				],
				[
					'q' => 'Chính sách bảo hành sản phẩm như thế nào?',
					'a' => 'Sản phẩm sơn Alkana được bảo hành từ 2–5 năm tùy dòng sản phẩm và điều kiện thi công. Chi tiết vui lòng liên hệ bộ phận kỹ thuật.',
				],
			];
			?>

			<div class="space-y-3" id="contact-faq-accordion">
				<?php foreach ( $faqs as $i => $faq ) : ?>
					<div class="bg-white rounded-xl border border-[--color-border] overflow-hidden">
						<button
							class="w-full flex items-center justify-between px-6 py-5 text-left font-semibold text-[--color-secondary] hover:text-[--color-primary] transition-colors"
							data-accordion-trigger="#contact-faq-<?php echo $i; ?>"
							aria-expanded="false"
							aria-controls="contact-faq-<?php echo $i; ?>"
						>
							<span><?php echo esc_html( $faq['q'] ); ?></span>
							<svg class="w-5 h-5 flex-shrink-0 transition-transform duration-300 data-[open]:rotate-180 text-[--color-primary]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
							</svg>
						</button>
						<div id="contact-faq-<?php echo $i; ?>" class="accordion-content hidden px-6 pb-5">
							<p class="text-gray-600 leading-relaxed"><?php echo esc_html( $faq['a'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

</main>

<script>
(function() {
	const form = document.getElementById('contact-form');
	const message = document.getElementById('contact-message');
	const submitBtn = form.querySelector('button[type="submit"]');
	const submitText = submitBtn.querySelector('.submit-text');
	const submitLoading = submitBtn.querySelector('.submit-loading');

	form.addEventListener('submit', async function(e) {
		e.preventDefault();

		const formData = new FormData(form);
		formData.append('action', 'alkana_submit_contact');

		submitBtn.disabled = true;
		submitText.classList.add('hidden');
		submitLoading.classList.remove('hidden');
		message.classList.add('hidden');

		try {
			const response = await fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
				method: 'POST',
				body: formData
			});

			const result = await response.json();

			if (result.success) {
				message.className = 'p-4 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200';
				message.textContent = result.data.message;
				form.reset();
			} else {
				message.className = 'p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200';
				message.textContent = result.data?.message || '<?php echo esc_js( __( 'An error occurred. Please try again.', 'alkana' ) ); ?>';
			}

			message.classList.remove('hidden');
		} catch (error) {
			message.className = 'p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200';
			message.textContent = '<?php echo esc_js( __( 'Network error. Please try again.', 'alkana' ) ); ?>';
			message.classList.remove('hidden');
		} finally {
			submitBtn.disabled = false;
			submitText.classList.remove('hidden');
			submitLoading.classList.add('hidden');
		}
	});
})();
</script>

<?php get_template_part( 'template-parts/footer' ); ?>
