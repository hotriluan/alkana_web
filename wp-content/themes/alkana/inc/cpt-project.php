<?php
/**
 * Custom Post Type: alkana_project
 * Public URL slug: /projects/
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'alkana_register_cpt_project' );

function alkana_register_cpt_project(): void {
	$labels = [
		'name'               => _x( 'Projects', 'post type general name', 'alkana' ),
		'singular_name'      => _x( 'Project', 'post type singular name', 'alkana' ),
		'add_new'            => __( 'Add New', 'alkana' ),
		'add_new_item'       => __( 'Add New Project', 'alkana' ),
		'edit_item'          => __( 'Edit Project', 'alkana' ),
		'new_item'           => __( 'New Project', 'alkana' ),
		'view_item'          => __( 'View Project', 'alkana' ),
		'search_items'       => __( 'Search Projects', 'alkana' ),
		'not_found'          => __( 'No projects found', 'alkana' ),
		'not_found_in_trash' => __( 'No projects found in trash', 'alkana' ),
		'menu_name'          => __( 'Projects', 'alkana' ),
	];

	register_post_type( 'alkana_project', [
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => [ 'slug' => 'projects', 'with_front' => false ],
		'capability_type'     => 'post',
		'has_archive'         => 'projects',
		'hierarchical'        => false,
		'menu_position'       => 6,
		'menu_icon'           => 'dashicons-portfolio',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
		'show_in_rest'        => true,
		'rest_base'           => 'alkana-projects',
	] );
}
