<?php
/**
 * Product card partial.
 * Renders a single alkana_product card.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
$cats    = get_the_terms( $post_id, 'product_category' );
$cat_name = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
?>

<article <?php post_class( 'product-card card group' ); ?> data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">

	<a href="<?php the_permalink(); ?>" class="product-card__image-link block overflow-hidden aspect-video">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'alkana-product-card', [
				'class' => 'product-card__img w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
				'alt'   => get_the_title(),
			] ); ?>
		<?php else : ?>
			<div class="product-card__img-placeholder w-full h-full bg-gray-100 flex items-center justify-center">
				<span class="dashicons dashicons-products text-gray-300 text-4xl"></span>
			</div>
		<?php endif; ?>
	</a>

	<div class="product-card__body p-4 flex flex-col gap-2">

		<?php if ( $cat_name ) : ?>
			<span class="product-card__category text-xs font-medium text-[--color-primary] uppercase tracking-wide">
				<?php echo esc_html( $cat_name ); ?>
			</span>
		<?php endif; ?>

		<h3 class="product-card__title font-heading font-semibold text-[--color-secondary] leading-snug">
			<a href="<?php the_permalink(); ?>" class="hover:text-[--color-primary] transition-colors">
				<?php the_title(); ?>
			</a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="product-card__excerpt text-sm text-gray-500 line-clamp-2">
				<?php the_excerpt(); ?>
			</p>
		<?php endif; ?>

		<a href="<?php the_permalink(); ?>"
		   class="product-card__cta btn btn--sm btn--outline self-start mt-auto">
			<?php esc_html_e( 'View Details', 'alkana' ); ?>
		</a>

	</div>
</article>
