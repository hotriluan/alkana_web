<?php // ── Newsletter CTA ───────────────────────────────────────────────── ?>
<section class="newsletter-cta bg-[--color-primary] py-12">
	<div class="max-w-7xl mx-auto px-4 text-center">
		<h2 class="text-2xl font-heading font-bold text-white mb-2">
			Đăng ký nhận thông tin
		</h2>
		<p class="text-white/80 mb-6 max-w-xl mx-auto">
			Nhận thông tin về sản phẩm mới, khuyến mãi và giải pháp sơn phủ từ Alkana
		</p>
		<form class="newsletter-form flex flex-col sm:flex-row gap-3 max-w-md mx-auto" id="newsletter-form" novalidate>
			<?php wp_nonce_field( 'alkana_newsletter', 'newsletter_nonce' ); ?>
			<input type="email" name="email" required
				   class="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white"
				   placeholder="Email của bạn..."
				   aria-label="Email" />
			<button type="submit" class="btn bg-[--color-secondary] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[--color-secondary]/90 transition-colors whitespace-nowrap">
				Đăng ký
			</button>
		</form>
		<div id="newsletter-message" class="mt-3 text-sm text-white/90 hidden" aria-live="polite"></div>
	</div>
</section>

<footer class="site-footer bg-[#1A3A5C] text-white pt-16 pb-8 border-t-4 border-[#E8611A]">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

		<div class="footer-grid grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">

			<?php // ── Brand column ──────────────────────────────────────────────── ?>
			<div class="footer-brand">
				<?php
				$logo_id = get_theme_mod( 'custom_logo' );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, [ 140, 44 ], false, [ 'class' => 'h-10 w-auto mb-3 brightness-0 invert' ] );
				} else {
					echo '<p class="font-heading font-bold text-lg mb-3">' . esc_html( get_bloginfo( 'name' ) ) . '</p>';
				}
				?>
				<p class="text-sm text-gray-300"><?php esc_html_e( 'Professional paint and coating solutions for construction.', 'alkana' ); ?></p>
			</div>

			<?php // ── Quick links ───────────────────────────────────────────────── ?>
			<div class="footer-links">
				<h3 class="font-heading font-semibold mb-3"><?php esc_html_e( 'Quick Links', 'alkana' ); ?></h3>
				<?php
				wp_nav_menu( [
					'theme_location' => 'footer',
					'menu_class'     => 'flex flex-col gap-2',
					'container'      => false,
					'fallback_cb'    => false,
				] );
				?>
			</div>

			<?php // ── Contact info ──────────────────────────────────────────────── ?>
			<div class="footer-contact">
				<h3 class="font-heading font-semibold mb-3"><?php esc_html_e( 'Contact', 'alkana' ); ?></h3>
				<address class="not-italic text-sm text-gray-300 space-y-2">
					<p><?php echo wp_kses_post( get_theme_mod( 'alkana_address', '' ) ); ?></p>
					<?php $phone = get_theme_mod( 'alkana_phone', '' ); if ( $phone ) : ?>
						<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="hover:text-white"><?php echo esc_html( $phone ); ?></a></p>
					<?php endif; ?>
					<?php $email = get_theme_mod( 'alkana_email', '' ); if ( $email ) : ?>
						<p><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="hover:text-white"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
					<?php endif; ?>
				</address>
			</div>

			<?php // ── Social links ──────────────────────────────────────────────── ?>
			<div class="footer-social">
				<h3 class="font-heading font-semibold mb-3">Kết nối</h3>
				<div class="flex gap-3">
					<?php
					$facebook = get_theme_mod( 'alkana_facebook', 'https://facebook.com/alkanacoating' );
					$linkedin = get_theme_mod( 'alkana_linkedin', '#' );
					$zalo     = get_theme_mod( 'alkana_zalo', 'https://zalo.me/' );
					?>
					
					<?php if ( $facebook ) : ?>
						<a href="<?php echo esc_url( $facebook ); ?>" 
						   target="_blank" 
						   rel="noopener noreferrer"
						   class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
						   aria-label="Facebook">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
							</svg>
						</a>
					<?php endif; ?>
					
					<?php if ( $linkedin ) : ?>
						<a href="<?php echo esc_url( $linkedin ); ?>" 
						   target="_blank" 
						   rel="noopener noreferrer"
						   class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
						   aria-label="LinkedIn">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
							</svg>
						</a>
					<?php endif; ?>
					
					<?php if ( $zalo ) : ?>
						<a href="<?php echo esc_url( $zalo ); ?>" 
						   target="_blank" 
						   rel="noopener noreferrer"
						   class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
						   aria-label="Zalo">
							<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.52c-.194.425-.564.768-1.015.943l-.016.006c-.447.178-.947.28-1.47.28-.11 0-.22-.005-.328-.014l.014.001c-3.083-.232-6.036-1.57-8.36-3.774-.116-.11-.188-.265-.188-.436 0-.331.268-.6.6-.6.162 0 .309.064.416.168l-.001-.001c1.983 1.88 4.548 3.041 7.386 3.212l.038.002c.082.007.177.011.274.011.286 0 .563-.048.819-.137l-.019.006c.258-.091.452-.312.497-.581l.001-.005c.016-.097.026-.209.026-.323 0-.486-.176-.932-.468-1.276l.002.003c-.281-.33-.641-.583-1.049-.737l-.019-.006c-2.435-.918-5.074-2.128-5.074-5.188 0-1.844 1.495-3.338 3.338-3.338 1.844 0 3.338 1.495 3.338 3.338 0 .73-.234 1.405-.631 1.956l.006-.009c-.116.161-.188.363-.188.581 0 .552.448 1 1 1 .257 0 .49-.097.667-.256l-.001.001c.581-.731.929-1.667.929-2.686 0-2.395-1.943-4.338-4.338-4.338s-4.338 1.943-4.338 4.338c0 3.784 2.794 5.238 5.532 6.278.247.093.429.297.472.545l.001.005z"/>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<div class="footer-bottom border-t border-white/20 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm text-gray-300">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'alkana' ); ?>
			</p>
		</div>

	</div>
</footer>

<?php get_template_part( 'template-parts/back-to-top' ); ?>

<?php wp_footer(); ?>
</body>
</html>
