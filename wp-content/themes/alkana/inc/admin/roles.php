<?php
/**
 * Custom admin roles for Alkana CMS.
 *
 * Roles:
 *   alkana_content_editor — manages products, projects, news
 *   alkana_tech_editor    — manages products + technical specs only
 *
 * Registered on 'after_switch_theme'.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'alkana_register_admin_roles' );

function alkana_register_admin_roles(): void {
	// Base capabilities shared by both custom roles
	$base_caps = [
		'read'                        => true,
		'upload_files'                => true,
		'edit_posts'                  => false,
		'edit_pages'                  => false,
		// CPT alkana_product
		'edit_alkana_products'        => true,
		'edit_others_alkana_products' => true,
		'publish_alkana_products'     => true,
		'read_private_alkana_products'=> true,
		'delete_alkana_products'      => true,
		// CPT alkana_project
		'edit_alkana_projects'        => true,
		'edit_others_alkana_projects' => true,
		'publish_alkana_projects'     => true,
		'read_private_alkana_projects'=> true,
		'delete_alkana_projects'      => true,
	];

	// Guard: only register roles when not already present to avoid wiping
	// user role assignments during theme switches or background tasks.
	// To change capabilities in future: bump ALKANA_ROLE_VERSION and add
	// remove_role() + add_role() inside the version-bump block only.

	// Content editor: full access to products + projects + posts (news)
	if ( ! get_role( 'alkana_content_editor' ) ) {
		add_role( 'alkana_content_editor', __( 'Alkana Content Editor', 'alkana' ), array_merge( $base_caps, [
			'edit_posts'             => true,
			'edit_published_posts'   => true,
			'publish_posts'          => true,
			'delete_posts'           => true,
			'edit_pages'             => true,
			'edit_published_pages'   => true,
		] ) );
	}

	// Tech editor: products/specs only, no blog posts or pages
	if ( ! get_role( 'alkana_tech_editor' ) ) {
		add_role( 'alkana_tech_editor', __( 'Alkana Tech Editor', 'alkana' ), $base_caps );
	}
}
