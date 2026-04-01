<?php
/**
 * Back to top button template part.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<button
	class="back-to-top fixed right-6 bottom-20 md:bottom-6 w-12 h-12 bg-[--color-primary] text-white rounded-full shadow-lg opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center hover:bg-[--color-secondary] focus:outline-none focus:ring-2 focus:ring-[--color-primary] focus:ring-offset-2"
	style="z-index: 90;"
	aria-label="Về đầu trang"
	type="button">
	<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
	</svg>
</button>
