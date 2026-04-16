<?php
/**
 * USP Counter Section — 4 animated stats with IntersectionObserver counter.
 * Values managed via Alkana > USP Stats in admin.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$usp = alkana_get_usp_settings();

$usp_icons = [
	'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
	'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
	'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
	'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
];
?>

<section class="section section--cool" id="usp-section">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="section__header">
			<p class="section__label"><?php esc_html_e( 'Tại sao chọn chúng tôi', 'alkana' ); ?></p>
			<h2 class="section__title"><?php echo esc_html( $usp['title'] ); ?></h2>
			<p class="section__desc"><?php echo esc_html( $usp['subtitle'] ); ?></p>
		</div>
		<div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
			<?php foreach ( $usp['items'] as $i => $item ) :
			// Extract numeric part for counter animation. Handle values like 'ISO', '63/63' as static text.
			$number_raw = $item['number'] ?? '0';
			if ( preg_match( '/^(\d+)(.*)$/', $number_raw, $m ) ) {
				$numeric   = (int) $m[1];
				$suffix    = $m[2];
				$is_static = false;
			} else {
				$numeric   = 0;
				$suffix    = $number_raw;
				$is_static = true;
			}
			?>
			<div class="text-center group">
				<div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-alkana-purple-50 flex items-center justify-center group-hover:bg-alkana-purple-100 transition-colors">
					<svg class="w-8 h-8 text-alkana-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<?php echo $usp_icons[ $i ]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG paths ?>
					</svg>
				</div>
				<p class="text-5xl font-extrabold text-alkana-purple-800 mb-2 tabular-nums">
					<?php if ( $is_static ) : ?>
						<?php echo esc_html( $suffix ); ?>
					<?php else : ?>
						<span class="stat-counter" data-count="<?php echo esc_attr( (string) $numeric ); ?>">0</span><?php echo esc_html( $suffix ); ?>
					<?php endif; ?>
				</p>
				<p class="text-sm uppercase tracking-wide font-semibold text-gray-500"><?php echo esc_html( $item['label'] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
