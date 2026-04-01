<?php
/**
 * Custom Post Type: alkana_job
 * Public URL slug: /tuyen-dung/
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'alkana_register_cpt_job' );

function alkana_register_cpt_job(): void {
	$labels = [
		'name'               => _x( 'Jobs', 'post type general name', 'alkana' ),
		'singular_name'      => _x( 'Job', 'post type singular name', 'alkana' ),
		'add_new'            => __( 'Add New', 'alkana' ),
		'add_new_item'       => __( 'Add New Job', 'alkana' ),
		'edit_item'          => __( 'Edit Job', 'alkana' ),
		'new_item'           => __( 'New Job', 'alkana' ),
		'view_item'          => __( 'View Job', 'alkana' ),
		'search_items'       => __( 'Search Jobs', 'alkana' ),
		'not_found'          => __( 'No jobs found', 'alkana' ),
		'not_found_in_trash' => __( 'No jobs found in trash', 'alkana' ),
		'menu_name'          => __( 'Job Openings', 'alkana' ),
	];

	register_post_type( 'alkana_job', [
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => [ 'slug' => 'tuyen-dung', 'with_front' => false ],
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-businessman',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
		'show_in_rest'        => true,
		'rest_base'           => 'alkana-jobs',
	] );
}
