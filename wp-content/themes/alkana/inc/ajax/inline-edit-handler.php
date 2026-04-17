<?php
/**
 * AJAX handler for inline grid editing in the product list table.
 *
 * Accepts: post_id, field, value, _ajax_nonce
 * Validates nonce, capability, and field allowlist before updating post meta.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_inline_edit', 'alkana_handle_inline_edit' );

/**
 * Process inline edit AJAX request.
 */
function alkana_handle_inline_edit(): void {
	check_ajax_referer( 'alkana_inline_edit_nonce' );

	if ( ! current_user_can( 'edit_alkana_products' ) ) {
		wp_send_json_error( __( 'Unauthorized', 'alkana' ), 403 );
	}

	$post_id = absint( $_POST['post_id'] ?? 0 );
	$field   = sanitize_key( $_POST['field'] ?? '' );
	$value   = sanitize_text_field( wp_unslash( $_POST['value'] ?? '' ) );

	if ( ! $post_id || get_post_type( $post_id ) !== 'alkana_product' ) {
		wp_send_json_error( __( 'Invalid product', 'alkana' ), 400 );
	}

	// Allowlist of editable fields mapped to post meta keys
	$field_map = [
		'sku'         => '_alkana_sku',
		'coverage'    => '_alkana_coverage',
		'mix_ratio'   => '_alkana_mix_ratio',
		'gloss_level' => '_alkana_gloss',
	];

	if ( ! array_key_exists( $field, $field_map ) ) {
		wp_send_json_error( __( 'Invalid field', 'alkana' ), 400 );
	}

	update_post_meta( $post_id, $field_map[ $field ], $value );

	wp_send_json_success( [ 'display_value' => $value ] );
}
