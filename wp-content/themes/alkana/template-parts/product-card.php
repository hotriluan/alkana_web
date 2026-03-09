<?php
/**
 * Product card partial.
 * Renders a single alkana_product card.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$post_id  = get_the_ID();
$cats     = get_the_terms( $post_id, 'product_category' );
$cat_name = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
$sku      = get_field( '_alkana_sku', $post_id );
$thumb_id = get_post_thumbnail_id( $post_id );
?>

<article <?php post_class( 'product-card group flex flex-col bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 overflow-hidden relative' ); ?> data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">

	<a href="<?php the_permalink(); ?>" class="block"
	   aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<div class="aspect-[4/3] w-full overflow-hidden bg-gray-100">
			<?php if ( $thumb_id ) : ?>
				<?php echo wp_get_attachment_image( $thumb_id, 'alkana-product-card', false, [
					'class'   => 'product-card__img w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
					'alt'     => get_the_title(),
					'loading' => 'lazy',
					'decoding' => 'async',
					'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
				] ); ?>
			<?php else : ?>
				<img
					class="product-card__img w-full h-full object-cover"
					src="https://placehold.co/600x400/eeeeee/999999?text=Alkana+Coating"
					alt="<?php echo esc_attr( get_the_title() ); ?>"
					loading="lazy"
					decoding="async"
					width="600"
					height="400"
				/>
			<?php endif; ?>
		</div>
	</a>

	<div class="product-card__body p-6 flex flex-col flex-grow">

		<?php if ( $cat_name ) : ?>
			<span class="product-card__category text-xs font-medium text-[--color-primary] uppercase tracking-wide mb-1">
				<?php echo esc_html( $cat_name ); ?>
			</span>
		<?php endif; ?>

		<?php if ( $sku ) : ?>
			<span class="product-card__sku text-xs font-mono text-gray-400 mb-2">
				<?php echo esc_html( $sku ); ?>
			</span>
		<?php endif; ?>

		<h3 class="product-card__title text-lg font-bold text-[#1A3A5C] mb-4">
			<a href="<?php the_permalink(); ?>" class="hover:text-[#E8611A] transition-colors">
				<?php the_title(); ?>
			</a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="product-card__excerpt text-sm text-gray-500 line-clamp-2 mb-4">
				<?php the_excerpt(); ?>
			</p>
		<?php endif; ?>

		<a href="<?php the_permalink(); ?>"
		   class="product-card__cta mt-auto w-full text-center border-2 border-[#E8611A] text-[#E8611A] px-4 py-2 rounded font-semibold hover:bg-[#E8611A] hover:text-white transition-colors">
			<?php esc_html_e( 'View Details', 'alkana' ); ?>
		</a>

	</div>
</article>
