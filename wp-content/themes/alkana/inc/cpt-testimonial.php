<?php
/**
 * Custom Post Type: Testimonial (alkana_testimonial)
 * Customer reviews displayed in the homepage testimonials section.
 * Title field = reviewer name; meta fields = quote, company, rating.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'alkana_register_cpt_testimonial' );

function alkana_register_cpt_testimonial(): void {
	$labels = [
		'name'               => __( 'Testimonials', 'alkana' ),
		'singular_name'      => __( 'Testimonial', 'alkana' ),
		'add_new'            => __( 'Add New', 'alkana' ),
		'add_new_item'       => __( 'Add New Testimonial', 'alkana' ),
		'edit_item'          => __( 'Edit Testimonial', 'alkana' ),
		'new_item'           => __( 'New Testimonial', 'alkana' ),
		'search_items'       => __( 'Search Testimonials', 'alkana' ),
		'not_found'          => __( 'No testimonials found.', 'alkana' ),
		'not_found_in_trash' => __( 'No testimonials found in trash.', 'alkana' ),
		'menu_name'          => __( 'Testimonials', 'alkana' ),
	];

	register_post_type( 'alkana_testimonial', [
		'labels'          => $labels,
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => false, // Submenus registered manually in inc/admin/admin-menu.php
		'supports'        => [ 'title', 'page-attributes' ], // title=reviewer name; page-attributes=menu order
		'has_archive'     => false,
		'rewrite'         => false,
		'capability_type' => 'post',
		'menu_icon'       => 'dashicons-format-quote',
		'show_in_rest'    => false,
	] );
}
