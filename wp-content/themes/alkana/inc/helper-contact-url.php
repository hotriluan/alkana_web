<?php
/**
 * Helper: get contact page URL reliably.
 * Finds the page using the Contact template, with slug fallback.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the contact page permalink regardless of slug language.
 *
 * @return string Contact page URL or home URL as fallback.
 */
function alkana_get_contact_url(): string {
	// Try finding page by template assignment.
	$pages = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'templates/page-contact.php',
		'number'     => 1,
	] );

	if ( ! empty( $pages ) ) {
		return get_permalink( $pages[0]->ID );
	}

	// Fallback: try common slugs.
	foreach ( [ 'lien-he', 'contact' ] as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return get_permalink( $page->ID );
		}
	}

	return home_url( '/lien-he/' );
}
