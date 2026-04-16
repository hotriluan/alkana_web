<?php
/**
 * Top Bar — phone, email, social links strip above the header.
 * Hidden on mobile, visible on md+. Auto-hides on scroll down.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$phone    = get_theme_mod( 'alkana_phone', '' );
$email    = get_theme_mod( 'alkana_email', '' );
$facebook = get_theme_mod( 'alkana_facebook', 'https://facebook.com/alkanacoating' );
$zalo     = get_theme_mod( 'alkana_zalo', 'https://zalo.me/' );
?>

<div class="topbar hidden md:block bg-alkana-purple-950 text-white text-xs py-1.5 transition-all duration-300" id="topbar">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">

		<?php // ── Left: contact info ─────────────────────────────────────────── ?>
		<div class="flex items-center gap-4 text-gray-300">
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"
				   class="flex items-center gap-1.5 hover:text-alkana-purple-300 transition-colors">
					<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
					</svg>
					<?php echo esc_html( $phone ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"
				   class="flex items-center gap-1.5 hover:text-alkana-purple-300 transition-colors">
					<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
					</svg>
					<?php echo esc_html( antispambot( $email ) ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php // ── Right: social links ────────────────────────────────────────── ?>
		<div class="flex items-center gap-2">
			<?php if ( $facebook ) : ?>
				<a href="<?php echo esc_url( $facebook ); ?>"
				   target="_blank" rel="noopener noreferrer"
				   class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:text-white transition-colors"
				   aria-label="Facebook">
					<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
						<path d="M24 12.073C24 5.445 18.627 0 12 0S0 5.445 0 12.073c0 6.032 4.388 10.997 10.125 11.893v-8.413H7.078v-3.48h3.047V9.43c0-3.022 1.792-4.69 4.533-4.69 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.49 0-1.955.926-1.955 1.875v2.255h3.328l-.532 3.48h-2.796v8.413C19.612 23.07 24 18.105 24 12.073z"/>
					</svg>
				</a>
			<?php endif; ?>
			<?php if ( $zalo ) : ?>
				<a href="<?php echo esc_url( $zalo ); ?>"
				   target="_blank" rel="noopener noreferrer"
				   class="w-6 h-6 flex items-center justify-center rounded text-gray-400 hover:text-white transition-colors"
				   aria-label="Zalo">
					<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 32 32">
						<path d="M16 1.333A14.667 14.667 0 1 1 1.333 16 14.667 14.667 0 0 1 16 1.333zm4.667 8.334H11.333a2 2 0 0 0-2 2v8.666a2 2 0 0 0 2 2h9.334a2 2 0 0 0 2-2v-8.666a2 2 0 0 0-2-2zm-9.334 2h9.334v8.666h-9.334v-8.666zm2 2v1.333h2.667v-1.333h-2.667zm0 2.666v1.334h5.334v-1.334h-5.334z"/>
					</svg>
				</a>
			<?php endif; ?>
		</div>

	</div>
</div>
