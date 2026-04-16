<?php
/**
 * Hero Slider — homepage banner (Swiper.js).
 * Slides managed via Alkana → Hero Slider admin page.
 * Falls back to static hero-banner.php when no slides are configured.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$slides = alkana_get_hero_slides();

if ( empty( $slides ) ) {
	get_template_part( 'template-parts/hero-banner' );
	return;
}
?>

<section class="hero-slider-section" aria-label="<?php esc_attr_e( 'Hero banner', 'alkana' ); ?>">
	<div class="swiper hero-swiper" style="min-height:85vh">

		<div class="swiper-wrapper">
			<?php foreach ( $slides as $i => $slide ) :
				// Resolve image URL — prefer WP attachment, fallback to stored URL.
				$img_id  = absint( $slide['image_id'] ?? 0 );
				$img_url = $img_id ? ( wp_get_attachment_image_url( $img_id, 'full' ) ?: '' ) : '';
				if ( ! $img_url ) {
					$img_url = esc_url( $slide['image_url'] ?? '' );
				}

				$title     = $slide['title']     ?? '';
				$subtitle  = $slide['subtitle']  ?? '';
				$cta_label = $slide['cta_label'] ?? '';
				$cta_url   = $slide['cta_url']   ?? '';
				$overlay   = max( 0, min( 90, (int) ( $slide['overlay'] ?? 50 ) ) ) / 100;
				$align     = in_array( $slide['text_align'] ?? '', [ 'left', 'center', 'right' ], true )
					? $slide['text_align'] : 'center';

				$justify = [ 'left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end' ][ $align ];
				$text_cls = 'text-' . $align;
			?>
			<div class="swiper-slide hero-slide"
			     role="group"
			     aria-label="<?php printf( esc_attr__( 'Slide %d', 'alkana' ), $i + 1 ); ?>">

				<?php if ( $img_url ) : ?>
					<div class="hero-slide__bg"
					     style="background-image:url('<?php echo esc_url( $img_url ); ?>')"
					     aria-hidden="true">
					</div>
				<?php else : ?>
					<div class="hero-slide__bg" style="background-color:#1A3A5C" aria-hidden="true"></div>
				<?php endif; ?>

				<div class="hero-slide__overlay"
				     style="position:absolute;inset:0;z-index:1;background:linear-gradient(to right,rgba(0,0,0,<?php echo $overlay; ?>) 0%,rgba(0,0,0,.3) 60%,rgba(0,0,0,.08) 100%)"
				     aria-hidden="true"></div>

				<div class="hero-slide__content"
				     style="position:relative;z-index:2;display:flex;flex-direction:column;justify-content:center;align-items:<?php echo esc_attr( $justify ); ?>;min-height:85vh;padding:0 clamp(1.5rem,6vw,5rem);max-width:1280px;margin:0 auto;width:100%">

					<?php if ( $title ) : ?>
						<h1 class="hero-slide__title <?php echo esc_attr( $text_cls ); ?>"
						    style="font-size:clamp(2rem,5vw,4.5rem);font-weight:900;color:#fff;line-height:1.1;margin:0 0 1.25rem;max-width:800px;text-shadow:0 2px 20px rgba(0,0,0,.4)">
							<?php echo wp_kses_post( $title ); ?>
						</h1>
					<?php endif; ?>

					<?php if ( $subtitle ) : ?>
						<p class="hero-slide__subtitle <?php echo esc_attr( $text_cls ); ?>"
						   style="font-size:clamp(1rem,2.2vw,1.35rem);color:rgba(255,255,255,.88);margin:0 0 2.25rem;max-width:600px;line-height:1.65;text-shadow:0 1px 8px rgba(0,0,0,.3)">
							<?php echo wp_kses_post( $subtitle ); ?>
						</p>
					<?php endif; ?>

					<?php if ( $cta_label && $cta_url ) : ?>
						<div class="hero-slide__cta-wrap flex flex-wrap gap-4 items-center">
							<a href="<?php echo esc_url( $cta_url ); ?>"
							   class="btn btn--gradient text-base">
								<?php echo esc_html( $cta_label ); ?>
							</a>
							<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"
							   class="btn text-sm font-semibold text-white border-2 border-white/60 hover:border-white hover:bg-white/10 transition-all duration-200 px-6 py-3 rounded-lg">
								<?php esc_html_e( 'Liên hệ tư vấn', 'alkana' ); ?>
							</a>
						</div>
					<?php endif; ?>

				</div>

				<?php // ── Scroll indicator ──────────────────────────────────── ?>
				<div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 hidden md:flex flex-col items-center gap-1 text-white/60 animate-bounce">
					<span class="text-xs uppercase tracking-widest"><?php esc_html_e( 'Cuộn xuống', 'alkana' ); ?></span>
					<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
					</svg>
				</div>

			</div>
			<?php endforeach; ?>
		</div><!-- /.swiper-wrapper -->

		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="hero-swiper__pagination swiper-pagination" aria-label="<?php esc_attr_e( 'Slide navigation', 'alkana' ); ?>"></div>
			<button class="hero-swiper__prev swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'alkana' ); ?>"></button>
			<button class="hero-swiper__next swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'alkana' ); ?>"></button>
		<?php endif; ?>

	</div><!-- /.hero-swiper -->
</section>
