<?php
/**
 * Template Name: Contact
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">

	<section class="page-hero bg-[--color-secondary] text-white py-16">
		<div class="container mx-auto px-4">
			<h1 class="text-3xl font-heading font-bold"><?php the_title(); ?></h1>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">
		<div class="lg:grid lg:grid-cols-2 lg:gap-12">

			<?php // ── Contact Info ──────────────────────────────────────────── ?>
			<div class="contact-info mb-10 lg:mb-0">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endwhile; endif; ?>
			</div>

			<?php // ── Contact Form ──────────────────────────────────────────── ?>
			<div class="contact-form">
				<form class="space-y-4" method="post" novalidate>
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

					<button type="submit" class="btn btn--primary w-full">
						<?php esc_html_e( 'Send Message', 'alkana' ); ?>
					</button>
				</form>
			</div>

		</div>
	</div>

</main>

<?php get_template_part( 'template-parts/footer' ); ?>
