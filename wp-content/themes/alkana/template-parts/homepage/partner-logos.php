<?php
/**
 * Homepage — Partner Logos Marquee.
 * CSS-only infinite horizontal scroll of partner/distributor logos.
 * Logos sourced from ACF option: alkana_partners (repeater) or falls back to placeholder text.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

// Support ACF or fallback to static demo partners.
$partners = [];
if ( function_exists( 'get_field' ) ) {
	$partners = get_field( 'alkana_partners', 'option' ) ?: [];
}

if ( empty( $partners ) ) {
	// Fallback: plain text partner names rendered as styled pills.
	$fallback_names = [
		'Vinacomin', 'Doosan Vina', 'Gamuda Land', 'Hoa Phat',
		'Kinh Bac City', 'Novaland', 'Becamex IDC', 'PTSC',
	];
}
?>

<section class="section section--white partner-logos" aria-label="<?php esc_attr_e( 'Đối tác phân phối', 'alkana' ); ?>">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<p class="section__label text-center mb-8"><?php esc_html_e( 'Đối tác & khách hàng tin dùng', 'alkana' ); ?></p>
	</div>

	<div class="partner-logos__track-wrap" aria-hidden="true">
		<div class="partner-logos__track">
			<?php if ( ! empty( $partners ) ) :
				// Duplicate for seamless loop.
				$items = array_merge( $partners, $partners );
				foreach ( $items as $p ) :
					$img_url = is_array( $p ) ? ( $p['logo']['url'] ?? '' ) : '';
					$label   = is_array( $p ) ? ( $p['name'] ?? '' ) : '';
				?>
				<div class="partner-logos__item">
					<?php if ( $img_url ) : ?>
					<img src="<?php echo esc_url( $img_url ); ?>"
					     alt="<?php echo esc_attr( $label ); ?>"
					     loading="lazy"
					     class="partner-logo-img">
					<?php else : ?>
					<span class="partner-logo-name"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			<?php else :
				$items = array_merge( $fallback_names, $fallback_names );
				foreach ( $items as $name ) : ?>
				<div class="partner-logos__item">
					<span class="partner-logo-name"><?php echo esc_html( $name ); ?></span>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
