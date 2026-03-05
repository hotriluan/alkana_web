<?php
/**
 * Filter empty state partial.
 * Displayed when faceted filter returns zero products.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="filter-empty-state col-span-full flex flex-col items-center justify-center py-16 text-center">

	<svg class="w-16 h-16 text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
			  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
	</svg>

	<h3 class="text-xl font-heading font-semibold text-gray-500 mb-2">
		<?php esc_html_e( 'No products found', 'alkana' ); ?>
	</h3>

	<p class="text-sm text-gray-400 mb-6 max-w-xs">
		<?php esc_html_e( 'Try adjusting your filters or browse all products.', 'alkana' ); ?>
	</p>

	<button class="btn btn--outline" id="filter-reset-empty">
		<?php esc_html_e( 'Clear Filters', 'alkana' ); ?>
	</button>

</div>
