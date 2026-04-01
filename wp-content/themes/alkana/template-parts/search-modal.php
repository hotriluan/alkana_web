<?php
/**
 * Search Modal Template
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="search-modal" class="search-modal hidden fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="search-modal-title" data-search-nonce="<?php echo esc_attr( wp_create_nonce( 'alkana_search' ) ); ?>">
	<div class="search-modal__container flex items-start justify-center min-h-screen px-4 pt-20">
		<div class="search-modal__content bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">

			<?php // ── Header ────────────────────────────────────────────────── ?>
			<div class="search-modal__header flex items-center justify-between px-6 py-4 border-b border-gray-100">
				<h2 id="search-modal-title" class="text-lg font-heading font-semibold text-[--color-secondary]">
					<?php esc_html_e( 'Tìm kiếm', 'alkana' ); ?>
				</h2>
				<button id="search-modal-close" class="text-gray-400 hover:text-[--color-secondary] transition-colors" aria-label="<?php esc_attr_e( 'Close search', 'alkana' ); ?>">
					<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
					</svg>
				</button>
			</div>

			<?php // ── Search Input ──────────────────────────────────────────── ?>
			<div class="search-modal__input px-6 py-4">
				<div class="relative">
					<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
					</svg>
					<input
						type="search"
						id="search-modal-input"
						class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary] focus:border-transparent"
						placeholder="<?php esc_attr_e( 'Tìm sản phẩm, SKU...', 'alkana' ); ?>"
						autocomplete="off"
					/>
					<div id="search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
						<svg class="animate-spin w-5 h-5 text-[--color-primary]" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</div>
				</div>
			</div>

			<?php // ── Results Container ────────────────────────────────────── ?>
			<div id="search-results" class="search-modal__results max-h-96 overflow-y-auto px-6 pb-6"></div>

		</div>
	</div>
</div>
