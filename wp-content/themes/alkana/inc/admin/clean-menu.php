<?php
/**
 * Clean admin menu for non-admin users.
 * Removes unneeded top-level menu items for custom roles.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'alkana_clean_admin_menu', 999 );

function alkana_clean_admin_menu(): void {
	if ( current_user_can( 'administrator' ) ) {
		return;
	}

	$remove_pages = [
		'index.php',          // Dashboard default widgets (we add custom widget)
		'edit-comments.php',  // Comments
		'tools.php',          // Tools
		'options-general.php',// Settings (non-admins don't need)
		'themes.php',         // Appearance
		'plugins.php',        // Plugins
		'users.php',          // Users
	];

	foreach ( $remove_pages as $page ) {
		remove_menu_page( $page );
	}
}
