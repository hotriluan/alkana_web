<?php
/**
 * Admin Focus Mode — Distraction-free dark writing surface.
 *
 * Injects the Focus Mode toggle button on post edit screens.
 * JS module (admin-focus-mode.js) handles the toggle logic.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_footer', 'alkana_focus_mode_button' );

/**
 * Inject the Focus Mode toggle button on post/page editor screens.
 */
function alkana_focus_mode_button(): void {
	$screen = get_current_screen();
	if ( ! $screen || $screen->base !== 'post' ) {
		return;
	}

	echo '<button id="alkana-focus-toggle"
	              class="alkana-focus-btn"
	              type="button"
	              title="' . esc_attr__( 'Focus Mode (F11)', 'alkana' ) . '"
	              aria-pressed="false">'
		. '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
		. '<path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/>'
		. '</svg>'
		. '<span class="alkana-focus-btn__label">' . esc_html__( 'Focus', 'alkana' ) . '</span>'
	. '</button>' . "\n";
}
