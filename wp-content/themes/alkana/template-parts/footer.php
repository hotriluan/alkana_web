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

<?php wp_footer(); ?>
</body>
</html>
