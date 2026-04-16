<?php
/**
 * Trust Bar / Certifications Marquee.
 * CSS-only infinite horizontal scroll strip.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$badges = [
	[
		'label' => __( 'ISO 9001:2015', 'alkana' ),
		'path'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
	],
	[
		'label' => __( 'ISO 14001', 'alkana' ),
		'path'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
	],
	[
		'label' => __( 'Bảo Hành 10 Năm', 'alkana' ),
		'path'  => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
	],
	[
		'label' => __( 'Tư Vấn Miễn Phí', 'alkana' ),
		'path'  => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
	],
	[
		'label' => __( 'TCVN 2682', 'alkana' ),
		'path'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
	],
	[
		'label' => __( 'REACH Certified', 'alkana' ),
		'path'  => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.78 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
	],
	[
		'label' => __( 'Giao Hàng Toàn Quốc', 'alkana' ),
		'path'  => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
	],
	[
		'label' => __( 'Hỗ Trợ 24/7', 'alkana' ),
		'path'  => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
	],
];

// Duplicate for seamless infinite loop.
$items = array_merge( $badges, $badges );
?>

<section class="trust-bar" aria-label="<?php esc_attr_e( 'Chứng nhận & cam kết chất lượng', 'alkana' ); ?>">
	<div class="trust-bar__track-wrap" aria-hidden="true">
		<div class="trust-bar__track">
			<?php foreach ( $items as $b ) : ?>
			<div class="trust-bar__item">
				<svg class="w-4 h-4 text-alkana-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo esc_attr( $b['path'] ); ?>" />
				</svg>
				<span class="trust-bar__label"><?php echo esc_html( $b['label'] ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
