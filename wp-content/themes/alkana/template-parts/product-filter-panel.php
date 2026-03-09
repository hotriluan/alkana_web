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

	<div class="filter-panel__header flex items-center justify-between mb-4 pb-2 border-b-2 border-[#E8611A]">
		<h2 class="text-lg font-extrabold text-[#1A3A5C]">
			Bộ lọc sản phẩm
		</h2>
		<button class="filter-panel__reset text-xs text-[#E8611A] hover:underline hidden" id="filter-reset">
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

<button class="filter-group__title flex justify-between items-center w-full py-3 font-bold text-sm text-[#1A3A5C] uppercase tracking-wider cursor-pointer border-b border-gray-200 hover:text-[#E8611A] transition-colors"
				data-accordion-trigger
				aria-expanded="true">
			<?php echo esc_html( $label ); ?>
			<span class="filter-group__icon text-lg leading-none select-none" aria-hidden="true" data-accordion-icon>−</span>
		</button>

		<div class="filter-group__options py-2 space-y-2" data-accordion-content>
			<?php foreach ( $terms as $term ) : ?>
				<label class="filter-option flex items-center justify-between text-sm text-gray-600 hover:text-gray-900 cursor-pointer">
					<span class="flex items-center gap-2">
						<input
							type="checkbox"
							class="filter-option__checkbox w-4 h-4 rounded border-gray-300 text-[#E8611A] focus:ring-[#E8611A] cursor-pointer"
							name="<?php echo esc_attr( $taxonomy ); ?>[]"
							value="<?php echo esc_attr( $term->slug ); ?>"
							data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
							data-slug="<?php echo esc_attr( $term->slug ); ?>"
						>
						<span class="filter-option__label"><?php echo esc_html( $term->name ); ?></span>
					</span>
					<span class="filter-option__count bg-gray-100 text-gray-500 text-xs py-0.5 px-2 rounded-full"
							  data-count-slug="<?php echo esc_attr( $term->slug ); ?>">
							<?php echo esc_html( (string) $term->count ); ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

		</div>
	<?php endforeach; ?>

	<?php // ── Featured toggle ────────────────────────────────────────────────── ?>
	<div class="filter-group mt-4 pt-3 border-t border-gray-200">
		<label class="filter-option flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 cursor-pointer">
			<input type="checkbox" class="filter-option__checkbox w-4 h-4 rounded border-gray-300 text-[#E8611A] focus:ring-[#E8611A] cursor-pointer" name="is_featured" value="1" data-taxonomy="is_featured">
			<span class="filter-option__label font-medium">
				<?php esc_html_e( 'Featured only', 'alkana' ); ?>
			</span>
		</label>
	</div>

</div>
