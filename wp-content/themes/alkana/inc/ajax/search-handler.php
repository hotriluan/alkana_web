<?php
/**
 * AJAX Search Handler
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_search', 'alkana_ajax_search_handler' );
add_action( 'wp_ajax_nopriv_alkana_search', 'alkana_ajax_search_handler' );

/**
 * Handle AJAX search requests.
 */
function alkana_ajax_search_handler() {
	check_ajax_referer( 'alkana_search', 'nonce' );

	$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

	if ( strlen( $term ) < 2 ) {
		wp_send_json( [ 'results' => [] ] );
		return;
	}

	global $wpdb;
	$results = [];

	// Query products from index table
	$product_table = $wpdb->prefix . 'alkana_product_index';
	$like_term = '%' . $wpdb->esc_like( $term ) . '%';

	$products = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DISTINCT post_id, product_name FROM {$product_table} WHERE product_name LIKE %s OR product_slug LIKE %s LIMIT 8",
			$like_term,
			$like_term
		)
	);

	if ( $products ) {
		foreach ( $products as $product ) {
			$results[] = [
				'title' => $product->product_name,
				'url'   => get_permalink( $product->post_id ),
				'type'  => 'product',
			];
		}
	}

	// Query regular posts and pages
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('post','page') AND post_title LIKE %s LIMIT 4",
			$like_term
		)
	);

	if ( $posts ) {
		foreach ( $posts as $post ) {
			$results[] = [
				'title' => $post->post_title,
				'url'   => get_permalink( $post->ID ),
				'type'  => get_post_type( $post->ID ),
			];
		}
	}

	wp_send_json( [ 'results' => $results ] );
}
