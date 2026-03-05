<?php
/**
 * Custom Post Type: alkana_product
 * Public URL slug: /products/
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'alkana_register_cpt_product' );

function alkana_register_cpt_product(): void {
	$labels = [
		'name'               => _x( 'Products', 'post type general name', 'alkana' ),
		'singular_name'      => _x( 'Product', 'post type singular name', 'alkana' ),
		'add_new'            => __( 'Add New', 'alkana' ),
		'add_new_item'       => __( 'Add New Product', 'alkana' ),
		'edit_item'          => __( 'Edit Product', 'alkana' ),
		'new_item'           => __( 'New Product', 'alkana' ),
		'view_item'          => __( 'View Product', 'alkana' ),
		'search_items'       => __( 'Search Products', 'alkana' ),
		'not_found'          => __( 'No products found', 'alkana' ),
		'not_found_in_trash' => __( 'No products found in trash', 'alkana' ),
		'menu_name'          => __( 'Products', 'alkana' ),
	];

	register_post_type( 'alkana_product', [
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => [ 'slug' => 'products', 'with_front' => false ],
		'capability_type'     => 'post',
		'has_archive'         => 'products',
		'hierarchical'        => false,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-products',
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
		'show_in_rest'        => true,
		'rest_base'           => 'alkana-products',
	] );
}
