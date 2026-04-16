<?php
/**
 * Homepage — Footer CTA Section.
 * Dark background call-to-action with quick inquiry form.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$phone         = get_theme_mod( 'alkana_phone', '1800 599 990' );
$phone_clean   = preg_replace( '/\s+/', '', $phone );
$contact_url   = get_page_link( get_page_by_path( 'lien-he' ) ) ?: home_url( '/lien-he/' );
?>

<section class="footer-cta" id="footer-cta" aria-labelledby="footer-cta-heading">

	<!-- Decorative gold line -->
	<span class="footer-cta__gold-line" aria-hidden="true"></span>

	<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

		<p class="footer-cta__label"><?php esc_html_e( 'Bắt đầu ngay hôm nay', 'alkana' ); ?></p>

		<h2 id="footer-cta-heading" class="footer-cta__heading">
			<?php esc_html_e( 'Bắt đầu dự án của bạn', 'alkana' ); ?>
		</h2>

		<p class="footer-cta__sub">
			<?php esc_html_e( 'Gửi yêu cầu ngay — đội ngũ kỹ thuật Alkana sẽ phản hồi trong vòng 24 giờ.', 'alkana' ); ?>
		</p>

		<!-- Quick inquiry form -->
		<form class="footer-cta__form"
		      method="get"
		      action="<?php echo esc_url( $contact_url ); ?>"
		      novalidate
		      aria-label="<?php esc_attr_e( 'Yêu cầu tư vấn nhanh', 'alkana' ); ?>">
			<div class="footer-cta__fields">
				<input class="footer-cta__input"
				       type="text"
				       name="name"
				       placeholder="<?php esc_attr_e( 'Họ và tên', 'alkana' ); ?>"
				       autocomplete="name"
				       maxlength="80">
				<input class="footer-cta__input"
				       type="tel"
				       name="phone"
				       placeholder="<?php esc_attr_e( 'Số điện thoại', 'alkana' ); ?>"
				       autocomplete="tel"
				       maxlength="15">
				<button class="footer-cta__submit btn btn--gradient" type="submit">
					<?php esc_html_e( 'Gửi yêu cầu', 'alkana' ); ?>
					<svg class="w-4 h-4 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
					</svg>
				</button>
			</div>
		</form>

		<!-- Or call directly -->
		<p class="footer-cta__divider"><?php esc_html_e( 'hoặc', 'alkana' ); ?></p>
		<a href="tel:<?php echo esc_attr( $phone_clean ); ?>" class="footer-cta__phone">
			<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
				      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
			</svg>
			<span><?php echo esc_html( $phone ); ?></span>
		</a>

	</div>
</section>
