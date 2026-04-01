<?php
/**
 * Archive: alkana_job
 *
 * The CPT rewrite slug 'tuyen-dung' matches the page slug, so WordPress
 * resolves /tuyen-dung/ as a post-type archive.  Set up the page post
 * context so the_title() in the shared template outputs the page title,
 * then delegate to templates/page-careers.php which already renders all
 * job listings.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

/* Re-use the "Tuyển dụng" page context for the hero title. */
$careers_page = get_page_by_path( 'tuyen-dung' );
if ( $careers_page ) {
	global $post;
	$post = $careers_page;   // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	setup_postdata( $post );
}

get_template_part( 'templates/page-careers' );

if ( $careers_page ) {
	wp_reset_postdata();
}
