<?php
/**
 * Project card partial.
 * Renders a single alkana_project card.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$post_id  = get_the_ID();
$location = get_field( 'project_location', $post_id );
$year     = get_field( 'project_year', $post_id );
?>

<article <?php post_class( 'project-card card group' ); ?>>

	<a href="<?php the_permalink(); ?>" class="project-card__image-link block overflow-hidden aspect-[3/2]">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php echo wp_get_attachment_image( get_post_thumbnail_id(), 'alkana-project-card', false, [
				'class'   => 'project-card__img w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
				'alt'     => get_the_title(),
				'loading' => 'lazy',
				'decoding' => 'async',
				'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
			] ); ?>
		<?php else : ?>
			<div class="project-card__img-placeholder w-full h-full bg-gray-100 flex items-center justify-center">
				<span class="dashicons dashicons-portfolio text-gray-300 text-4xl"></span>
			</div>
		<?php endif; ?>
	</a>

	<div class="project-card__body p-4">

		<?php if ( $location || $year ) : ?>
			<p class="project-card__meta text-xs text-gray-400 mb-1">
				<?php if ( $location ) : ?><?php echo esc_html( $location ); ?><?php endif; ?>
				<?php if ( $location && $year ) : ?> · <?php endif; ?>
				<?php if ( $year ) : ?><?php echo esc_html( (string) $year ); ?><?php endif; ?>
			</p>
		<?php endif; ?>

		<h3 class="project-card__title font-heading font-semibold text-[--color-secondary] leading-snug">
			<a href="<?php the_permalink(); ?>" class="hover:text-[--color-primary] transition-colors">
				<?php the_title(); ?>
			</a>
		</h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="project-card__excerpt text-sm text-gray-500 mt-1 line-clamp-2">
				<?php the_excerpt(); ?>
			</p>
		<?php endif; ?>

	</div>
</article>
