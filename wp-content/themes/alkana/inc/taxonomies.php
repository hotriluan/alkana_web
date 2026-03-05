<?php
/**
 * Register all product taxonomies.
 *
 * Taxonomies:
 *   product_category — hierarchical (like categories)
 *   surface_type     — hierarchical
 *   paint_system     — flat (tags-style)
 *   gloss_level      — flat
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'alkana_register_taxonomies' );

function alkana_register_taxonomies(): void {

	// ── Product Category ─────────────────────────────────────────────────────
	register_taxonomy( 'product_category', 'alkana_product', [
		'labels'            => [
			'name'              => _x( 'Product Categories', 'taxonomy general name', 'alkana' ),
			'singular_name'     => _x( 'Product Category', 'taxonomy singular name', 'alkana' ),
			'search_items'      => __( 'Search Categories', 'alkana' ),
			'all_items'         => __( 'All Categories', 'alkana' ),
			'parent_item'       => __( 'Parent Category', 'alkana' ),
			'edit_item'         => __( 'Edit Category', 'alkana' ),
			'update_item'       => __( 'Update Category', 'alkana' ),
			'add_new_item'      => __( 'Add New Category', 'alkana' ),
			'menu_name'         => __( 'Categories', 'alkana' ),
		],
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => [ 'slug' => 'product-category' ],
		'show_in_rest'      => true,
	] );

	// ── Surface Type ─────────────────────────────────────────────────────────
	register_taxonomy( 'surface_type', 'alkana_product', [
		'labels'            => [
			'name'          => _x( 'Surface Types', 'taxonomy general name', 'alkana' ),
			'singular_name' => _x( 'Surface Type', 'taxonomy singular name', 'alkana' ),
			'menu_name'     => __( 'Surface Types', 'alkana' ),
			'add_new_item'  => __( 'Add New Surface Type', 'alkana' ),
			'edit_item'     => __( 'Edit Surface Type', 'alkana' ),
		],
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => [ 'slug' => 'surface-type' ],
		'show_in_rest'      => true,
	] );

	// ── Paint System ─────────────────────────────────────────────────────────
	register_taxonomy( 'paint_system', 'alkana_product', [
		'labels'            => [
			'name'          => _x( 'Paint Systems', 'taxonomy general name', 'alkana' ),
			'singular_name' => _x( 'Paint System', 'taxonomy singular name', 'alkana' ),
			'menu_name'     => __( 'Paint Systems', 'alkana' ),
			'add_new_item'  => __( 'Add New Paint System', 'alkana' ),
			'edit_item'     => __( 'Edit Paint System', 'alkana' ),
		],
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => [ 'slug' => 'paint-system' ],
		'show_in_rest'      => true,
	] );

	// ── Gloss Level ──────────────────────────────────────────────────────────
	register_taxonomy( 'gloss_level', 'alkana_product', [
		'labels'            => [
			'name'          => _x( 'Gloss Levels', 'taxonomy general name', 'alkana' ),
			'singular_name' => _x( 'Gloss Level', 'taxonomy singular name', 'alkana' ),
			'menu_name'     => __( 'Gloss Levels', 'alkana' ),
			'add_new_item'  => __( 'Add New Gloss Level', 'alkana' ),
			'edit_item'     => __( 'Edit Gloss Level', 'alkana' ),
		],
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => [ 'slug' => 'gloss-level' ],
		'show_in_rest'      => true,
	] );
}
