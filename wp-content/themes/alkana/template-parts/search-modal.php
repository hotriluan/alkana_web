<?php
/**
 * Search Modal Template — Full-screen purple overlay
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="search-modal"
	 class="search-modal hidden fixed inset-0 z-[400] bg-alkana-purple-900/95 backdrop-blur-md"
	 role="dialog"
	 aria-modal="true"
	 aria-labelledby="search-modal-title"
	 data-search-nonce="<?php echo esc_attr( wp_create_nonce( 'alkana_search' ) ); ?>">

	<div class="search-modal__container flex flex-col items-center min-h-screen px-4 pt-[15vh]">

		<?php // ── Close button ──────────────────────────────────────────── ?>
		<button id="search-modal-close"
				class="absolute top-6 right-6 text-white/60 hover:text-white transition-colors"
				aria-label="<?php esc_attr_e( 'Close search', 'alkana' ); ?>">
			<svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
			</svg>
		</button>

		<?php // ── Title ─────────────────────────────────────────────────── ?>
		<h2 id="search-modal-title" class="text-white/60 text-sm font-medium uppercase tracking-widest mb-6">
			<?php esc_html_e( 'Search', 'alkana' ); ?>
		</h2>

		<?php // ── Search Input ──────────────────────────────────────────── ?>
		<div class="w-full max-w-2xl relative">
			<svg class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
			</svg>
			<input
				type="search"
				id="search-modal-input"
				class="w-full pl-14 pr-14 py-5 bg-white/10 border border-white/20 rounded-2xl text-white text-xl placeholder-white/40
					   focus:outline-none focus:ring-2 focus:ring-alkana-purple-400 focus:border-transparent focus:bg-white/15 transition-all"
				placeholder="<?php esc_attr_e( 'Search products, categories...', 'alkana' ); ?>"
				autocomplete="off"
			/>
			<div id="search-loading" class="absolute right-5 top-1/2 -translate-y-1/2 hidden">
				<svg class="animate-spin w-6 h-6 text-alkana-purple-300" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
			</div>
		</div>

		<?php // ── Keyboard hint ─────────────────────────────────────────── ?>
		<p class="text-white/30 text-xs mt-3">
			<?php esc_html_e( 'Press ESC to close', 'alkana' ); ?>
		</p>

		<?php // ── Results Container ────────────────────────────────────── ?>
		<div id="search-results" class="search-modal__results w-full max-w-2xl mt-8 max-h-[50vh] overflow-y-auto"></div>

	</div>
</div>
