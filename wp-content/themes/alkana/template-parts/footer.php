<?php
/**
 * Site footer — 5-column dark layout.
 * Columns: About | Products | Resources | Contact | Newsletter
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$phone    = get_theme_mod( 'alkana_phone', '' );
$email    = get_theme_mod( 'alkana_email', '' );
$address  = get_theme_mod( 'alkana_address', '' );
$facebook = get_theme_mod( 'alkana_facebook', 'https://facebook.com/alkanacoating' );
$linkedin = get_theme_mod( 'alkana_linkedin', '#' );
$zalo     = get_theme_mod( 'alkana_zalo', 'https://zalo.me/' );
$logo_id  = get_theme_mod( 'custom_logo' );
?>

<footer class="site-footer bg-[#1A1A2E] text-white pt-16 pb-0 border-t-4 border-alkana-purple-600">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

		<div class="footer-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">

			<?php // ── Column 1: About / Brand ──────────────────────────────────── ?>
			<div class="lg:col-span-2 footer-brand">
				<?php if ( $logo_id ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block mb-4" rel="home">
						<?php echo wp_get_attachment_image( $logo_id, [ 140, 44 ], false, [ 'class' => 'h-10 w-auto brightness-0 invert' ] ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block mb-4 font-heading font-bold text-xl text-white" rel="home">
						<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
					</a>
				<?php endif; ?>
				<p class="text-sm text-gray-400 leading-relaxed mb-5 max-w-xs">
					<?php esc_html_e( 'Giải pháp sơn và phủ công nghiệp chuyên nghiệp cho mọi công trình — từ dân dụng đến công nghiệp nặng.', 'alkana' ); ?>
				</p>
				<div class="flex gap-2">
					<?php if ( $facebook ) : ?>
						<a href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"
						   class="footer-social-link" aria-label="Facebook">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
								<path d="M24 12.073C24 5.445 18.627 0 12 0S0 5.445 0 12.073c0 6.032 4.388 10.997 10.125 11.893v-8.413H7.078v-3.48h3.047V9.43c0-3.022 1.792-4.69 4.533-4.69 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.49 0-1.955.926-1.955 1.875v2.255h3.328l-.532 3.48h-2.796v8.413C19.612 23.07 24 18.105 24 12.073z"/>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $linkedin ) : ?>
						<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer"
						   class="footer-social-link" aria-label="LinkedIn">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
								<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $zalo ) : ?>
						<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer"
						   class="footer-social-link" aria-label="Zalo">
							<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 32 32">
								<path d="M16 1.333A14.667 14.667 0 1 1 1.333 16 14.667 14.667 0 0 1 16 1.333zm4.667 8.334H11.333a2 2 0 0 0-2 2v8.666a2 2 0 0 0 2 2h9.334a2 2 0 0 0 2-2v-8.666a2 2 0 0 0-2-2zm-9.334 2h9.334v8.666h-9.334v-8.666zm2 2v1.333h2.667v-1.333h-2.667zm0 2.666v1.334h5.334v-1.334h-5.334z"/>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<?php // ── Column 2: Products ──────────────────────────────────────── ?>
			<div class="footer-col">
				<h3 class="footer-col__title"><?php esc_html_e( 'Sản phẩm', 'alkana' ); ?></h3>
				<?php
				wp_nav_menu( [
					'theme_location' => 'footer',
					'menu_class'     => 'footer-links',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				] );
				?>
			</div>

			<?php // ── Column 3: Resources ─────────────────────────────────────── ?>
			<div class="footer-col">
				<h3 class="footer-col__title"><?php esc_html_e( 'Tài nguyên', 'alkana' ); ?></h3>
				<ul class="footer-links">
					<li><a href="<?php echo esc_url( home_url( '/du-an/' ) ); ?>"><?php esc_html_e( 'Dự án tiêu biểu', 'alkana' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>"><?php esc_html_e( 'Tin tức', 'alkana' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/tuyen-dung/' ) ); ?>"><?php esc_html_e( 'Tuyển dụng', 'alkana' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/ve-chung-toi/' ) ); ?>"><?php esc_html_e( 'Về chúng tôi', 'alkana' ); ?></a></li>
				</ul>
			</div>

			<?php // ── Column 4: Contact ───────────────────────────────────────── ?>
			<div class="footer-col">
				<h3 class="footer-col__title"><?php esc_html_e( 'Liên hệ', 'alkana' ); ?></h3>
				<address class="not-italic space-y-3 text-sm text-gray-400">
					<?php if ( $address ) : ?>
						<p class="flex gap-2 leading-relaxed">
							<svg class="w-4 h-4 mt-0.5 text-alkana-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
							</svg>
							<?php echo wp_kses_post( $address ); ?>
						</p>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"
						   class="flex gap-2 items-center hover:text-alkana-purple-300 transition-colors">
							<svg class="w-4 h-4 text-alkana-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
							</svg>
							<?php echo esc_html( $phone ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"
						   class="flex gap-2 items-center hover:text-alkana-purple-300 transition-colors">
							<svg class="w-4 h-4 text-alkana-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
							</svg>
							<?php echo esc_html( antispambot( $email ) ); ?>
						</a>
					<?php endif; ?>
				</address>
			</div>

		</div>

		<?php // ── Newsletter strip ──────────────────────────────────────────── ?>
		<div class="border-t border-white/10 py-8 flex flex-col md:flex-row items-center gap-5 justify-between">
			<div class="text-sm text-gray-400 md:max-w-xs">
				<p class="font-semibold text-white mb-1"><?php esc_html_e( 'Đăng ký nhận tin', 'alkana' ); ?></p>
				<p><?php esc_html_e( 'Cập nhật sản phẩm, khuyến mãi và giải pháp sơn phủ từ Alkana.', 'alkana' ); ?></p>
			</div>
			<form class="newsletter-form flex w-full md:w-auto max-w-sm" id="newsletter-form" novalidate>
				<?php wp_nonce_field( 'alkana_newsletter', 'newsletter_nonce' ); ?>
				<input type="email" name="email" required
					   class="flex-1 px-4 py-2.5 bg-white/10 border border-white/20 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-alkana-purple-400 text-white placeholder-gray-400 text-sm"
					   placeholder="<?php esc_attr_e( 'Email của bạn...', 'alkana' ); ?>"
					   aria-label="<?php esc_attr_e( 'Email đăng ký', 'alkana' ); ?>" />
				<button type="submit"
				        class="bg-alkana-purple-600 hover:bg-alkana-purple-500 text-white font-semibold px-5 py-2.5 rounded-r-lg transition-colors text-sm whitespace-nowrap">
					<?php esc_html_e( 'Đăng ký', 'alkana' ); ?>
				</button>
			</form>
			<div id="newsletter-message" class="hidden text-sm font-medium text-alkana-purple-300" aria-live="polite"></div>
		</div>

		<?php // ── Bottom bar ────────────────────────────────────────────────── ?>
		<div class="border-t border-white/10 py-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-gray-500">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <strong class="text-gray-400"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>. <?php esc_html_e( 'Bảo lưu mọi quyền.', 'alkana' ); ?></p>
			<nav aria-label="<?php esc_attr_e( 'Footer bottom', 'alkana' ); ?>" class="flex gap-4">
				<a href="<?php echo esc_url( home_url( '/chinh-sach-bao-mat/' ) ); ?>" class="hover:text-gray-300 transition-colors"><?php esc_html_e( 'Chính sách bảo mật', 'alkana' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/dieu-khoan-su-dung/' ) ); ?>" class="hover:text-gray-300 transition-colors"><?php esc_html_e( 'Điều khoản sử dụng', 'alkana' ); ?></a>
			</nav>
		</div>

	</div>
</footer>

<?php get_template_part( 'template-parts/back-to-top' ); ?>

<?php wp_footer(); ?>
</body>
</html>
