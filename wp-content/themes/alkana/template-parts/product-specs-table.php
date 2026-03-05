<?php
/**
 * Product specifications table partial.
 * Renders ACF technical spec fields as a responsive table.
 * On mobile (< 640px), the CSS converts rows to card list via specs-table.css.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();

// ACF field groups: product_specs (Repeater)
$specs = get_field( 'product_specs', $post_id );

if ( empty( $specs ) ) {
	return;
}
?>

<div class="specs-table-wrapper overflow-x-auto mt-6">
	<table class="specs-table w-full text-sm border-collapse">
		<thead class="specs-table__head bg-[--color-secondary] text-white">
			<tr>
				<th class="specs-table__th text-left p-3 font-medium"><?php esc_html_e( 'Property', 'alkana' ); ?></th>
				<th class="specs-table__th text-left p-3 font-medium"><?php esc_html_e( 'Value', 'alkana' ); ?></th>
			</tr>
		</thead>
		<tbody class="specs-table__body">
			<?php foreach ( $specs as $row ) : ?>
				<tr class="specs-table__row border-b border-gray-100 hover:bg-gray-50">
					<td class="specs-table__td p-3 font-medium text-gray-700" data-label="<?php esc_attr_e( 'Property', 'alkana' ); ?>">
						<?php echo esc_html( $row['spec_label'] ?? '' ); ?>
					</td>
					<td class="specs-table__td p-3 text-gray-600" data-label="<?php esc_attr_e( 'Value', 'alkana' ); ?>">
						<?php echo esc_html( $row['spec_value'] ?? '' ); ?>
						<?php if ( ! empty( $row['spec_unit'] ) ) : ?>
							<span class="text-gray-400 text-xs"><?php echo esc_html( $row['spec_unit'] ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
