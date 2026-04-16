<?php
/**
 * Alkana Admin Dashboard 2.0 — CMS Command Center.
 * Registers dashboard widgets; render functions live in dashboard-widgets.php.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/dashboard-widgets.php';

add_action( 'wp_dashboard_setup', 'alkana_dashboard_setup' );

function alkana_dashboard_setup(): void {
	// Strip all default WP dashboard clutter.
	$remove = [
		'dashboard_quick_press', 'dashboard_right_now', 'dashboard_activity',
		'dashboard_primary',     'dashboard_secondary', 'dashboard_site_health',
		'dashboard_php_nag',
	];
	foreach ( $remove as $id ) {
		remove_meta_box( $id, 'dashboard', 'normal' );
		remove_meta_box( $id, 'dashboard', 'side' );
		remove_meta_box( $id, 'dashboard', 'core' );
	}

	wp_add_dashboard_widget( 'alkana_welcome',  __( '👋 Chào mừng', 'alkana' ),               'alkana_render_welcome_card' );
	wp_add_dashboard_widget( 'alkana_stats',    __( '📊 Tổng quan nội dung', 'alkana' ),       'alkana_render_stats_cards' );
	wp_add_dashboard_widget( 'alkana_actions',  __( '⚡ Hành động nhanh', 'alkana' ),          'alkana_render_quick_actions' );
	wp_add_dashboard_widget( 'alkana_charts',   __( '📈 Biểu đồ', 'alkana' ),                 'alkana_render_charts_widget' );
	wp_add_dashboard_widget( 'alkana_activity', __( '🕐 Hoạt động gần đây', 'alkana' ),       'alkana_render_activity_feed' );
	wp_add_dashboard_widget( 'alkana_health',   __( '⚙️ Hệ thống', 'alkana' ),               'alkana_render_system_health' );
}
