<?php
/**
 * Theme Customizer: Footer & Social settings.
 * Registers settings and controls for all values used via get_theme_mod()
 * in template-parts/footer.php.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', 'alkana_customizer_register' );

function alkana_customizer_register( WP_Customize_Manager $wp_customize ): void {

	// ── Panel ──────────────────────────────────────────────────────────────────
	$wp_customize->add_panel( 'alkana_panel', [
		'title'    => __( 'Alkana Theme Options', 'alkana' ),
		'priority' => 30,
	] );

	// ── Section: Footer Contact ─────────────────────────────────────────────────
	$wp_customize->add_section( 'alkana_footer_contact', [
		'title'    => __( 'Footer — Contact Info', 'alkana' ),
		'panel'    => 'alkana_panel',
		'priority' => 10,
	] );

	// Address (textarea).
	$wp_customize->add_setting( 'alkana_address', [
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_address', [
		'label'   => __( 'Address', 'alkana' ),
		'section' => 'alkana_footer_contact',
		'type'    => 'textarea',
	] );

	// Phone.
	$wp_customize->add_setting( 'alkana_phone', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_phone', [
		'label'       => __( 'Phone Number', 'alkana' ),
		'section'     => 'alkana_footer_contact',
		'type'        => 'text',
		'description' => __( 'e.g. +84 28 3873 8888', 'alkana' ),
	] );

	// Email.
	$wp_customize->add_setting( 'alkana_email', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_email', [
		'label'   => __( 'Email Address', 'alkana' ),
		'section' => 'alkana_footer_contact',
		'type'    => 'email',
	] );

	// Working hours.
	$wp_customize->add_setting( 'alkana_hours', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_hours', [
		'label'       => __( 'Working Hours', 'alkana' ),
		'section'     => 'alkana_footer_contact',
		'type'        => 'text',
		'description' => __( 'e.g. Thứ Hai - Thứ Bảy: 8:00 - 17:00', 'alkana' ),
	] );

	// Google Maps embed URL.
	$wp_customize->add_setting( 'alkana_map_embed', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'alkana_map_embed', [
		'label'       => __( 'Google Maps Embed URL', 'alkana' ),
		'section'     => 'alkana_footer_contact',
		'type'        => 'url',
		'description' => __( 'Google Maps embed src URL (Maps > Share > Embed a map > copy the src="..." value).', 'alkana' ),
	] );

	// ── Section: Social Links ───────────────────────────────────────────────────
	$wp_customize->add_section( 'alkana_footer_social', [
		'title'    => __( 'Footer — Social Links', 'alkana' ),
		'panel'    => 'alkana_panel',
		'priority' => 20,
	] );

	// Facebook.
	$wp_customize->add_setting( 'alkana_facebook', [
		'default'           => 'https://facebook.com/alkanacoating',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_facebook', [
		'label'   => __( 'Facebook URL', 'alkana' ),
		'section' => 'alkana_footer_social',
		'type'    => 'url',
	] );

	// LinkedIn.
	$wp_customize->add_setting( 'alkana_linkedin', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_linkedin', [
		'label'   => __( 'LinkedIn URL', 'alkana' ),
		'section' => 'alkana_footer_social',
		'type'    => 'url',
	] );

	// Zalo.
	$wp_customize->add_setting( 'alkana_zalo', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'alkana_zalo', [
		'label'       => __( 'Zalo URL', 'alkana' ),
		'section'     => 'alkana_footer_social',
		'type'        => 'url',
		'description' => __( 'e.g. https://zalo.me/0123456789', 'alkana' ),
	] );
}
