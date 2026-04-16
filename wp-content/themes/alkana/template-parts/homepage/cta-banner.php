<?php
/**
 * Homepage — CTA Banner Section.
 * Full-width purple gradient CTA strip.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$phone = get_theme_mod( 'alkana_phone', '1800 599 990' );
?>

<section class="cta-banner" aria-labelledby="cta-banner-heading">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-8">
		<div class="text-center md:text-left">
			<h2 id="cta-banner-heading" class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-white leading-tight">
				<?php esc_html_e( 'Nhận tư vấn miễn phí từ chuyên gia sơn của chúng tôi', 'alkana' ); ?>
			</h2>
			<p class="mt-2 text-purple-200 text-lg">
				<?php esc_html_e( 'Giải pháp sơn tối ưu — đúng kỹ thuật, đúng ngân sách.', 'alkana' ); ?>
			</p>
		</div>
		<div class="flex flex-col sm:flex-row gap-4 shrink-0">
			<a href="<?php echo esc_url( get_page_link( get_page_by_path( 'lien-he' ) ) ); ?>"
			   class="btn btn--white-on-purple">
				<?php esc_html_e( 'Nhận báo giá ngay', 'alkana' ); ?>
			</a>
			<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"
			   class="btn btn--outline-white">
				<svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
				</svg>
				<?php echo esc_html( $phone ); ?>
			</a>
		</div>
	</div>
</section>
