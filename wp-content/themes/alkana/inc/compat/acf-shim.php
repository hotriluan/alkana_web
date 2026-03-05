<?php
/**
 * ACF Compatibility Shim
 *
 * Defines fallback stubs for Advanced Custom Fields functions when the ACF
 * plugin is not installed. This allows the theme to run in local/staging
 * environments without ACF active (e.g. fresh WordPress install, CI).
 *
 * Stubs use standard WordPress get_post_meta() / get_option() so data
 * saved by the import script (which also uses update_post_meta) is readable.
 *
 * Limitations:
 *  - Repeater and flexible content fields return empty arrays (no sub-field iteration).
 *  - Image/file fields stored as attachment ID integers are returned as-is.
 *  - ACF-specific query functionality (meta_query integration) is not replicated.
 *
 * This file is only loaded when ACF is NOT active. Remove it once ACF is
 * installed on all target environments.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'get_field' ) ) {
	// ACF is active — nothing to shim.
	return;
}

/**
 * Stub for ACF get_field().
 *
 * For simple scalar fields, returns get_post_meta() value.
 * For options page fields (3rd param === 'option'), returns get_option().
 *
 * @param string           $field_name  ACF field name or key.
 * @param int|string|false $post_id     Post ID, 'option', or false for current post.
 * @param bool             $format_value Not used in stub.
 * @return mixed
 */
function get_field( string $field_name, $post_id = false, bool $format_value = true ) {
	if ( 'option' === $post_id || 'options' === $post_id ) {
		return get_option( 'options_' . $field_name );
	}

	$id = $post_id ?: get_the_ID();

	return get_post_meta( (int) $id, $field_name, true );
}

/**
 * Stub for ACF the_field() — echoes get_field().
 *
 * @param string           $field_name
 * @param int|string|false $post_id
 */
function the_field( string $field_name, $post_id = false ): void {
	$value = get_field( $field_name, $post_id );
	if ( is_scalar( $value ) ) {
		echo esc_html( (string) $value );
	}
}

/**
 * Stub for ACF get_sub_field().
 *
 * Cannot replicate ACF's repeater loop state. Always returns null.
 * Templates wrapping repeater output in `if ( have_rows() )` checks
 * will simply not render that section.
 *
 * @param string $field_name
 * @return null
 */
function get_sub_field( string $field_name ) {
	return null;
}

/**
 * Stub for ACF have_rows().
 *
 * Always returns false so repeater while-loops are skipped cleanly.
 *
 * @param string           $field_name
 * @param int|string|false $post_id
 * @return false
 */
function have_rows( string $field_name, $post_id = false ): bool {
	return false;
}

/**
 * Stub for ACF the_row() — no-op.
 */
function the_row(): void {}
