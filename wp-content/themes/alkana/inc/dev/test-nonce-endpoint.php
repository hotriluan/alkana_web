<?php
/**
 * Test Nonce REST Endpoint — DEV/CI only.
 *
 * Registers GET /wp-json/alkana/v1/test-nonce to return valid nopriv nonces
 * for k6 load tests running against the Docker CI environment.
 *
 * SECURITY: Only registers the endpoint when WP_DEBUG is true.
 * Production environments MUST have WP_DEBUG set to false (default).
 * These are nopriv nonces — they do not grant any authenticated session access.
 *
 * @package Alkana
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	// Guard: only active in debug/CI environments.
	if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
		return;
	}

	register_rest_route( 'alkana/v1', '/test-nonce', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'alkana_rest_test_nonce',
		'permission_callback' => '__return_true',
	] );
} );

/**
 * Returns fresh nonces for all load-tested AJAX endpoints.
 *
 * @return WP_REST_Response
 */
function alkana_rest_test_nonce() {
	return new WP_REST_Response( [
		'filter_nonce'  => wp_create_nonce( 'alkana_filter' ),
		'search_nonce'  => wp_create_nonce( 'alkana_search' ),
		'contact_nonce' => wp_create_nonce( 'alkana_contact' ),
	], 200 );
}
