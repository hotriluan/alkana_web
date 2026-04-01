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

	<section class="page-hero relative min-h-[40vh] flex items-end overflow-hidden bg-[--color-secondary] text-white py-16">
		<?php if ( $hero_img_id ) : ?>
			<?php echo wp_get_attachment_image( $hero_img_id, 'full', false, [
				'class'         => 'absolute inset-0 w-full h-full object-cover z-0',
				'alt'           => '',
				'fetchpriority' => 'high',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			] ); ?>
			<div class="absolute inset-0 bg-gradient-to-r from-black/50 via-black/30 to-black/20 z-10"></div>
		<?php endif; ?>
		<div class="relative z-20 container mx-auto px-4">
			<h1 class="text-3xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
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

				<div class="space-y-4">
					<?php // Address ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
						</svg>
						<div>
							<p class="text-[--color-secondary] leading-relaxed">
								<?php esc_html_e( 'Lô C1-2, Đường N1, KCN Hiệp Phước, Nhà Bè, TP. Hồ Chí Minh', 'alkana' ); ?>
							</p>
						</div>
					</div>

					<?php // Phone ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
						</svg>
						<div>
							<a href="tel:+842838738888" class="text-[--color-secondary] hover:text-[--color-primary] transition-colors">
								+84 28 3873 8888
							</a>
						</div>
					</div>

					<?php // Email ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
						</svg>
						<div>
							<a href="mailto:info@alkana.vn" class="text-[--color-secondary] hover:text-[--color-primary] transition-colors">
								info@alkana.vn
							</a>
						</div>
					</div>

					<?php // Working Hours ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
						<div>
							<p class="text-[--color-secondary]">
								<?php esc_html_e( 'Thứ Hai - Thứ Bảy: 8:00 - 17:00', 'alkana' ); ?>
							</p>
						</div>
					</div>
				</div>

				<?php // Google Maps ?>
				<div class="mt-8">
					<div class="aspect-video rounded-xl overflow-hidden shadow-lg">
						<iframe
							src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3920.698!2d106.7195!3d10.6801!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317527e5f19a583d%3A0x7d7e4e8b5c8e8b5e!2sKCN+Hi%E1%BB%87p+Ph%C6%B0%E1%BB%9Bc!5e0!3m2!1svi!2svn!4v1"
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
			</div>
			</div>

			<?php // ── Contact Form ──────────────────────────────────────────── ?>
			<div class="contact-form">
				<form id="contact-form" class="space-y-4" novalidate>
					<?php wp_nonce_field( 'alkana_contact', '_alkana_nonce' ); ?>

					<?php // Honeypot anti-spam field ?>
					<div class="hidden" aria-hidden="true">
						<label for="url_website"><?php esc_html_e( 'Website', 'alkana' ); ?></label>
						<input type="text" id="url_website" name="url_website" tabindex="-1" autocomplete="off">
					</div>

					<div>
						<label class="block text-sm font-medium mb-1" for="contact_name">
							<?php esc_html_e( 'Full Name', 'alkana' ); ?> <span class="text-red-500">*</span>
						</label>
						<input type="text" id="contact_name" name="contact_name"
							   class="form-input" required autocomplete="name">
					</div>

					<div>
						<label class="block text-sm font-medium mb-1" for="contact_email">
							<?php esc_html_e( 'Email', 'alkana' ); ?> <span class="text-red-500">*</span>
						</label>
						<input type="email" id="contact_email" name="contact_email"
							   class="form-input" required autocomplete="email">
					</div>

					<div>
						<label class="block text-sm font-medium mb-1" for="contact_phone">
							<?php esc_html_e( 'Phone', 'alkana' ); ?>
						</label>
						<input type="tel" id="contact_phone" name="contact_phone"
							   class="form-input" autocomplete="tel">
					</div>

					<div>
						<label class="block text-sm font-medium mb-1" for="contact_message">
							<?php esc_html_e( 'Message', 'alkana' ); ?> <span class="text-red-500">*</span>
						</label>
						<textarea id="contact_message" name="contact_message"
								  class="form-textarea" rows="5" required></textarea>
					</div>

					<div id="contact-message" class="hidden p-4 rounded-lg text-sm"></div>

					<button type="submit" class="btn btn--primary w-full">
						<span class="submit-text"><?php esc_html_e( 'Send Message', 'alkana' ); ?></span>
						<span class="submit-loading hidden">
							<svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
							<?php esc_html_e( 'Sending...', 'alkana' ); ?>
						</span>
					</button>
				</form>
			</div>

		</div>
	</div>

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
