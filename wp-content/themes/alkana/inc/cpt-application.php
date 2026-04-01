<?php
/**
 * Custom Post Type: alkana_application
 * Not public — admin-only interface for managing job applications.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'alkana_register_cpt_application' );
add_action( 'init', 'alkana_register_application_meta' );

function alkana_register_cpt_application(): void {
	$labels = [
		'name'               => _x( 'Applications', 'post type general name', 'alkana' ),
		'singular_name'      => _x( 'Application', 'post type singular name', 'alkana' ),
		'add_new'            => __( 'Add New', 'alkana' ),
		'add_new_item'       => __( 'Add New Application', 'alkana' ),
		'edit_item'          => __( 'Edit Application', 'alkana' ),
		'new_item'           => __( 'New Application', 'alkana' ),
		'view_item'          => __( 'View Application', 'alkana' ),
		'search_items'       => __( 'Search Applications', 'alkana' ),
		'not_found'          => __( 'No applications found', 'alkana' ),
		'not_found_in_trash' => __( 'No applications found in trash', 'alkana' ),
		'menu_name'          => __( 'Applications', 'alkana' ),
	];

	register_post_type( 'alkana_application', [
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => 26,
		'menu_icon'           => 'dashicons-id-alt',
		'supports'            => [ 'title' ],
		'show_in_rest'        => false,
	] );
}

function alkana_register_application_meta(): void {
	$meta_keys = [
		'_app_email'   => 'string',
		'_app_phone'   => 'string',
		'_app_job_id'  => 'integer',
		'_app_cv_url'  => 'string',
		'_app_cv_id'   => 'integer',
		'_app_message' => 'string',
		'_app_status'  => 'string',
	];

	foreach ( $meta_keys as $key => $type ) {
		register_post_meta( 'alkana_application', $key, [
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		] );
	}
}
