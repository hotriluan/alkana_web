<?php
/**
 * Create the newsletter subscribers table.
 *
 * Table: {prefix}alkana_newsletter
 * Called on 'after_switch_theme' alongside product index table creation.
 * UNIQUE KEY on email makes INSERT IGNORE atomic — eliminates race conditions.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'alkana_create_newsletter_table' );

function alkana_create_newsletter_table(): void {
	global $wpdb;

	$table   = $wpdb->prefix . 'alkana_newsletter';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		email      VARCHAR(200)    NOT NULL,
		subscribed DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY idx_email (email)
	) ENGINE=InnoDB {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
