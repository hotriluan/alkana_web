<?php
/**
 * Hero banner partial.
 * Used on front-page, key landing pages.
 * Background image from ACF 'hero_image' field on the current page.
 *
 * LCP optimised: uses <img> (not CSS background-image) so browsers apply
 * fetchpriority="high" and the element qualifies as the Largest Contentful
 * Paint candidate. A matching <link rel="preload"> is emitted in <head>
 * by inc/performance/lcp-preload.php.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$hero_image     = get_field( 'hero_image' );
$hero_title     = get_field( 'hero_title' ) ?: get_the_title();
$hero_sub       = get_field( 'hero_subtitle' );
$hero_cta_label = get_field( 'hero_cta_label' ) ?: __( 'Explore Products', 'alkana' );
$hero_cta_url   = get_field( 'hero_cta_url' )   ?: get_post_type_archive_link( 'alkana_product' );

$img_id = $hero_image['ID'] ?? 0;
?>

<section class="hero-banner relative flex items-center justify-center min-h-[480px] lg:min-h-[600px] bg-[--color-secondary] text-white overflow-hidden">

	<?php if ( $hero_image ) : ?>
		<?php
		/**
		 * Hero background image — rendered as <img> for correct LCP attribution.
		 * Positioned absolute, covers entire section (same visual result as CSS background).
		 * fetchpriority="high" + loading="eager" = browser prioritises this above all
		 * other images. WordPress auto-generates srcset for registered image sizes.
		 */
		if ( $img_id ) {
			echo wp_get_attachment_image( $img_id, 'full', false, [
				'class'          => 'hero-banner__bg absolute inset-0 w-full h-full object-cover',
				'alt'            => '',          // decorative — content covered by <h1>
				'fetchpriority'  => 'high',
				'loading'        => 'eager',
				'decoding'       => 'async',
				'sizes'          => '100vw',
			] );
		} else {
			// Fallback: ACF returned array but no ID (external URL edge case)
			printf(
				'<img class="hero-banner__bg absolute inset-0 w-full h-full object-cover" src="%s" alt="" fetchpriority="high" loading="eager" decoding="async" width="1920" height="800">',
				esc_url( $hero_image['url'] )
			);
		}
		?>
		<div class="hero-banner__overlay absolute inset-0 bg-black/50" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="hero-banner__content relative z-10 text-center px-4 max-w-3xl mx-auto">

		<h1 class="hero-banner__title text-4xl lg:text-5xl font-heading font-bold leading-tight mb-4">
			<?php echo wp_kses_post( $hero_title ); ?>
		</h1>

		<?php if ( $hero_sub ) : ?>
			<p class="hero-banner__subtitle text-lg lg:text-xl text-white/80 mb-8">
				<?php echo wp_kses_post( $hero_sub ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $hero_cta_url ) : ?>
			<a href="<?php echo esc_url( $hero_cta_url ); ?>" class="btn btn--primary btn--lg">
				<?php echo esc_html( $hero_cta_label ); ?>
			</a>
		<?php endif; ?>

	</div>

</section>
