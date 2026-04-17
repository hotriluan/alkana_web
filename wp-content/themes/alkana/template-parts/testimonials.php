<?php
/**
 * Testimonials Section — Swiper carousel redesign.
 * Card: quotation icon, star rating, quote text, photo, name, company.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$testimonial_query = new WP_Query( [
	'post_type'      => 'alkana_testimonial',
	'posts_per_page' => 8,
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
	'post_status'    => 'publish',
] );

if ( ! $testimonial_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section class="section section--cool" id="testimonials" aria-labelledby="testimonials-heading">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

		<div class="section__header">
			<p class="section__label"><?php esc_html_e( 'Đánh giá', 'alkana' ); ?></p>
			<h2 id="testimonials-heading" class="section__title"><?php esc_html_e( 'Khách hàng nói gì về chúng tôi', 'alkana' ); ?></h2>
		</div>

		<div class="swiper testimonials-swiper pb-14">
			<div class="swiper-wrapper">

				<?php while ( $testimonial_query->have_posts() ) : $testimonial_query->the_post();
					$quote   = get_post_meta( get_the_ID(), '_alkana_testimonial_quote', true );
					$company = get_post_meta( get_the_ID(), '_alkana_testimonial_company', true );
					$rating  = (int) get_post_meta( get_the_ID(), '_alkana_testimonial_rating', true );
					if ( $rating < 1 || $rating > 5 ) {
						$rating = 5;
					}
				?>
				<div class="swiper-slide">
					<div class="testimonial-card">

						<!-- Quotation mark -->
						<svg class="testimonial-card__quote-icon" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
							<path d="M10 8c-3.3 0-6 2.7-6 6v10h10V14H7c0-1.7 1.3-3 3-3V8zm14 0c-3.3 0-6 2.7-6 6v10h10V14h-7c0-1.7 1.3-3 3-3V8z"/>
						</svg>

						<!-- Stars -->
						<div class="flex gap-1 mb-4" role="img" aria-label="<?php echo esc_attr( $rating . '/5 sao' ); ?>">
							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
								<svg class="w-4 h-4 <?php echo $i <= $rating ? 'text-amber-400' : 'text-gray-200'; ?>"
								     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
									<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
								</svg>
							<?php endfor; ?>
						</div>

						<!-- Quote -->
						<?php if ( $quote ) : ?>
							<p class="testimonial-card__quote">"<?php echo esc_html( $quote ); ?>"</p>
						<?php endif; ?>

						<!-- Author row -->
						<div class="testimonial-card__author">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="testimonial-card__avatar">
									<?php the_post_thumbnail( 'thumbnail', [ 'class' => 'w-full h-full object-cover', 'alt' => get_the_title() ] ); ?>
								</div>
							<?php else : ?>
								<div class="testimonial-card__avatar testimonial-card__avatar--placeholder" aria-hidden="true">
									<svg class="w-5 h-5 text-alkana-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
									</svg>
								</div>
							<?php endif; ?>
							<div>
								<p class="testimonial-card__name"><?php the_title(); ?></p>
								<?php if ( $company ) : ?>
									<p class="testimonial-card__company"><?php echo esc_html( $company ); ?></p>
								<?php endif; ?>
							</div>
						</div>

					</div>
				</div>
				<?php endwhile; wp_reset_postdata(); ?>

			</div><!-- /.swiper-wrapper -->
			<div class="swiper-pagination testimonials-swiper__pagination"></div>
		</div><!-- /.swiper -->

	</div>
</section>

<?php
// Initialize testimonials Swiper in footer (after swiper-js loads).
add_action( 'wp_footer', function () {
	?>
	<script>
	(function(){
		function initTestimonialsSwiper(){
			var el = document.querySelector('.testimonials-swiper');
			if( !el || typeof Swiper === 'undefined' ) return;
			if( el.classList.contains('swiper-initialized') ) return;
			new Swiper('.testimonials-swiper',{
				slidesPerView: 1,
				spaceBetween: 24,
				loop: true,
				autoplay: { delay: 5500, disableOnInteraction: false, pauseOnMouseEnter: true },
				pagination: { el: '.testimonials-swiper__pagination', clickable: true },
				breakpoints: { 640: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } },
				a11y: { prevSlideMessage: 'Đánh giá trước', nextSlideMessage: 'Đánh giá tiếp theo' }
			});
		}
		document.addEventListener('DOMContentLoaded', initTestimonialsSwiper);
		document.addEventListener('alkana:pageChanged', initTestimonialsSwiper);
	}());
	</script>
	<?php
}, 30 );
?>
