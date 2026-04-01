<?php
/**
 * Single Job Template with Apply Form
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$job_id = get_the_ID();
		?>

		<main id="main-content" class="site-main">

			<div class="container mx-auto px-4">
				<?php get_template_part( 'template-parts/breadcrumb' ); ?>
			</div>

			<section class="job-hero bg-[--color-secondary] text-white py-14">
				<div class="container mx-auto px-4">
					<div class="max-w-3xl">
						<h1 class="text-3xl font-heading font-bold mb-3"><?php the_title(); ?></h1>

						<div class="flex flex-wrap gap-4 text-sm text-white/80">
							<?php if ( $department = get_field( 'department' ) ) : ?>
								<span class="flex items-center gap-1">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
									<?php echo esc_html( $department ); ?>
								</span>
							<?php endif; ?>

							<?php if ( $location = get_field( 'location' ) ) : ?>
								<span class="flex items-center gap-1">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
									<?php echo esc_html( $location ); ?>
								</span>
							<?php endif; ?>

							<?php if ( $type = get_field( 'employment_type' ) ) : ?>
								<span class="flex items-center gap-1">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
									<?php echo esc_html( $type ); ?>
								</span>
							<?php endif; ?>

							<?php if ( $deadline = get_field( 'deadline' ) ) : ?>
								<span class="flex items-center gap-1">
									<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
									<?php echo esc_html__( 'Deadline:', 'alkana' ) . ' ' . esc_html( $deadline ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>

			<div class="container mx-auto px-4 py-12">
				<div class="max-w-3xl mx-auto">

					<div class="job-content prose prose-lg max-w-none mb-12">
						<?php the_content(); ?>
					</div>

					<div id="apply-section" class="apply-section bg-gray-50 rounded-xl p-8 border border-[--color-border]">
						<h2 class="text-2xl font-heading font-bold text-[--color-secondary] mb-6">
							<?php esc_html_e( 'Apply for this position', 'alkana' ); ?>
						</h2>

						<form id="apply-form" class="space-y-5">
							<div>
								<label for="app_name" class="block text-sm font-semibold mb-1">
									<?php esc_html_e( 'Full Name', 'alkana' ); ?> <span class="text-red-500">*</span>
								</label>
								<input type="text" id="app_name" name="name" required
									class="form-input w-full" placeholder="<?php esc_attr_e( 'Your full name', 'alkana' ); ?>">
							</div>

							<div>
								<label for="app_email" class="block text-sm font-semibold mb-1">
									<?php esc_html_e( 'Email', 'alkana' ); ?> <span class="text-red-500">*</span>
								</label>
								<input type="email" id="app_email" name="email" required
									class="form-input w-full" placeholder="<?php esc_attr_e( 'your.email@example.com', 'alkana' ); ?>">
							</div>

							<div>
								<label for="app_phone" class="block text-sm font-semibold mb-1">
									<?php esc_html_e( 'Phone', 'alkana' ); ?>
								</label>
								<input type="tel" id="app_phone" name="phone"
									class="form-input w-full" placeholder="<?php esc_attr_e( 'Your phone number', 'alkana' ); ?>">
							</div>

							<div>
								<label for="app_cv" class="block text-sm font-semibold mb-1">
									<?php esc_html_e( 'CV/Resume', 'alkana' ); ?> <span class="text-red-500">*</span>
								</label>
								<input type="file" id="app_cv" name="cv" required accept=".pdf,.doc,.docx"
									class="form-input w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[--color-primary] file:text-white hover:file:bg-[--color-primary]/90">
								<p class="text-xs text-gray-500 mt-1">
									<?php esc_html_e( 'PDF, DOC, or DOCX (max 5MB)', 'alkana' ); ?>
								</p>
							</div>

							<div>
								<label for="app_message" class="block text-sm font-semibold mb-1">
									<?php esc_html_e( 'Cover Letter / Message', 'alkana' ); ?>
								</label>
								<textarea id="app_message" name="message" rows="5"
									class="form-textarea w-full" placeholder="<?php esc_attr_e( 'Tell us why you\'re a great fit...', 'alkana' ); ?>"></textarea>
							</div>

							<input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

							<div id="apply-message" class="hidden p-4 rounded-lg text-sm"></div>

							<button type="submit" class="btn btn--primary w-full">
								<span class="submit-text"><?php esc_html_e( 'Submit Application', 'alkana' ); ?></span>
								<span class="submit-loading hidden">
									<svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
									</svg>
									<?php esc_html_e( 'Submitting...', 'alkana' ); ?>
								</span>
							</button>
						</form>
					</div>

				</div>
			</div>

		</main>

		<script>
		(function() {
			const form = document.getElementById('apply-form');
			const message = document.getElementById('apply-message');
			const submitBtn = form.querySelector('button[type="submit"]');
			const submitText = submitBtn.querySelector('.submit-text');
			const submitLoading = submitBtn.querySelector('.submit-loading');

			form.addEventListener('submit', async function(e) {
				e.preventDefault();

				const formData = new FormData(form);
				formData.append('action', 'alkana_submit_application');
				formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'alkana_apply' ) ); ?>');
				formData.append('job_id', '<?php echo esc_js( $job_id ); ?>');

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

		<?php
	endwhile;
endif;

get_template_part( 'template-parts/footer' );
