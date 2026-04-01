<?php
/**
 * Pagination Template Part
 * Brand-styled pagination for archives and listings.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

the_posts_pagination( [
	'mid_size'  => 2,
	'prev_text' => '&laquo; ' . __( 'Trước', 'alkana' ),
	'next_text' => __( 'Sau', 'alkana' ) . ' &raquo;',
	'class'     => 'alkana-pagination',
] );
