<?php
/**
 * Back to top button template part.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<button
	class="back-to-top fixed right-6 bottom-20 md:bottom-6 w-12 h-12 rounded-full shadow-lg shadow-alkana-purple-500/30 opacity-0 pointer-events-none transition-[opacity,transform] duration-300 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-alkana-purple-400 focus:ring-offset-2 hover:scale-110 hover:shadow-xl hover:shadow-alkana-purple-500/40"
	style="z-index: 90; background: linear-gradient(135deg, var(--color-primary, #8236BC), var(--color-primary-dark, #4C0682)); color: white;"
	aria-label="Về đầu trang"
	type="button">
	<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
		<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path>
	</svg>
</button>
