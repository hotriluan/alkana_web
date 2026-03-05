<?php
/**
 * Create the denormalized product filter index table.
 *
 * Table: {prefix}alkana_product_index
 * Called on 'after_switch_theme' hook.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'alkana_create_product_index_table' );

function alkana_create_product_index_table(): void {
	global $wpdb;

	$table   = $wpdb->prefix . 'alkana_product_index';
	$charset = $wpdb->get_charset_collate();

	// Use TEXT columns for MySQL 5.7/8.0 compat (avoid VARCHAR index length limits)
	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		post_id         BIGINT UNSIGNED NOT NULL,
		product_slug    VARCHAR(200)    NOT NULL DEFAULT '',
		product_name    TEXT            NOT NULL,
		category_slugs  TEXT            NOT NULL DEFAULT '',
		surface_slugs   TEXT            NOT NULL DEFAULT '',
		paint_system    TEXT            NOT NULL DEFAULT '',
		gloss_level     TEXT            NOT NULL DEFAULT '',
		is_featured     TINYINT(1)      NOT NULL DEFAULT 0,
		updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (post_id),
		INDEX idx_paint_system (paint_system(50)),
		INDEX idx_gloss_level  (gloss_level(50)),
		INDEX idx_is_featured  (is_featured)
	) ENGINE=InnoDB {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	// Store DB version for future migrations
	update_option( 'alkana_db_version', '1.0.0' );
}
