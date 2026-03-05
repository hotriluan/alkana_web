<?php
/**
 * Product filter panel partial.
 * Renders faceted filter checkboxes for product taxonomy terms.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$filter_taxonomies = [
	'product_category' => __( 'Category', 'alkana' ),
	'surface_type'     => __( 'Surface Type', 'alkana' ),
	'paint_system'     => __( 'Paint System', 'alkana' ),
	'gloss_level'      => __( 'Gloss Level', 'alkana' ),
];
?>

<div class="filter-panel" id="filter-panel" data-filter-panel>

	<div class="filter-panel__header flex items-center justify-between mb-4">
		<h2 class="font-heading font-semibold text-[--color-secondary]">
			<?php esc_html_e( 'Filter', 'alkana' ); ?>
		</h2>
		<button class="filter-panel__reset text-sm text-[--color-primary] hover:underline hidden" id="filter-reset">
			<?php esc_html_e( 'Clear all', 'alkana' ); ?>
		</button>
	</div>

	<?php // ── Active filter tags ─────────────────────────────────────────────── ?>
	<div class="filter-active-tags flex flex-wrap gap-2 mb-4 empty:hidden" id="filter-active-tags" aria-live="polite"></div>

	<?php foreach ( $filter_taxonomies as $taxonomy => $label ) : ?>
		<?php
		$terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => true ] );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			continue;
		}
		?>

		<div class="filter-group mb-6" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">

			<button class="filter-group__title w-full flex items-center justify-between font-medium text-sm text-[--color-secondary] mb-2"
					aria-expanded="true">
				<?php echo esc_html( $label ); ?>
				<span class="filter-group__icon text-lg leading-none" aria-hidden="true">−</span>
			</button>

			<div class="filter-group__options space-y-1">
				<?php foreach ( $terms as $term ) : ?>
					<label class="filter-option flex items-center gap-2 cursor-pointer">
						<input
							type="checkbox"
							class="filter-option__checkbox sr-only"
							name="<?php echo esc_attr( $taxonomy ); ?>[]"
							value="<?php echo esc_attr( $term->slug ); ?>"
							data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
							data-slug="<?php echo esc_attr( $term->slug ); ?>"
						>
						<span class="filter-option__box w-4 h-4 border border-gray-300 rounded flex-shrink-0"></span>
						<span class="filter-option__label text-sm text-gray-700"><?php echo esc_html( $term->name ); ?></span>
						<span class="filter-option__count text-xs text-gray-400 ml-auto"
							  data-count-slug="<?php echo esc_attr( $term->slug ); ?>">
							<?php echo esc_html( (string) $term->count ); ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

		</div>
	<?php endforeach; ?>

	<?php // ── Featured toggle ────────────────────────────────────────────────── ?>
	<div class="filter-group mb-4">
		<label class="filter-option flex items-center gap-2 cursor-pointer">
			<input type="checkbox" class="filter-option__checkbox sr-only" name="is_featured" value="1" data-taxonomy="is_featured">
			<span class="filter-option__box w-4 h-4 border border-gray-300 rounded flex-shrink-0"></span>
			<span class="filter-option__label text-sm font-medium text-gray-700">
				<?php esc_html_e( 'Featured only', 'alkana' ); ?>
			</span>
		</label>
	</div>

</div>
