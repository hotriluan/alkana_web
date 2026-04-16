<?php
/**
 * Admin Command Palette — Ctrl+K quick search for products.
 * Injects modal HTML into admin_footer. The JS is enqueued via Vite manifest.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_footer', 'alkana_admin_command_palette_html' );

/**
 * Output the command palette modal markup in admin footer.
 */
function alkana_admin_command_palette_html(): void {
	?>
	<div id="alkana-cmd-palette" class="alkana-cmd-hidden"
		 style="position:fixed;inset:0;z-index:99999;background:rgba(56,0,107,0.92);backdrop-filter:blur(8px);display:flex;align-items:flex-start;justify-content:center;padding-top:18vh;transition:opacity 0.2s;">

		<div style="width:100%;max-width:560px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:16px;overflow:hidden;">

			<?php // ── Header / Input ?>
			<div style="padding:16px;border-bottom:1px solid rgba(255,255,255,0.08);">
				<div style="display:flex;align-items:center;gap:10px;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(171,71,188,0.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
					<input id="cmd-palette-input" type="text"
						   placeholder="<?php esc_attr_e( 'Search products...', 'alkana' ); ?>"
						   autocomplete="off"
						   style="flex:1;background:transparent;border:none;outline:none;color:#fff;font-size:16px;font-family:Inter,-apple-system,sans-serif;">
					<kbd style="font-size:11px;color:rgba(255,255,255,0.3);background:rgba(255,255,255,0.08);padding:2px 8px;border-radius:4px;font-family:monospace;">ESC</kbd>
				</div>
			</div>

			<?php // ── Results ?>
			<div id="cmd-palette-results" style="max-height:320px;overflow-y:auto;padding:4px 0;"></div>

			<?php // ── Footer hint ?>
			<div style="padding:10px 16px;border-top:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;gap:12px;">
				<span style="font-size:11px;color:rgba(255,255,255,0.25);">
					<kbd style="background:rgba(255,255,255,0.08);padding:1px 5px;border-radius:3px;font-family:monospace;">↑↓</kbd> navigate
					<kbd style="background:rgba(255,255,255,0.08);padding:1px 5px;border-radius:3px;font-family:monospace;margin-left:8px;">↵</kbd> open
				</span>
			</div>

		</div>
	</div>

	<style>
		.alkana-cmd-hidden { display: none !important; }
	</style>
	<?php
}
