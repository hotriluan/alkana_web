<?php
/**
 * Alkana login page — split layout branding.
 * Left panel: purple gradient + logo + tagline (fixed, CSS).
 * Right panel: white form (#login positioned to right half).
 *
 * Assets:
 *   assets/images/alkana-logo.png  — logo PNG, transparent bg, ~240×64px
 *   assets/images/login-bg.jpg     — industrial photo, min 1920×1080px
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'login_enqueue_scripts', 'alkana_login_styles' );
add_filter( 'login_headerurl',       'alkana_login_logo_url' );
add_filter( 'login_headertext',      'alkana_login_logo_title' );
add_filter( 'login_message',         'alkana_login_brand_panel' );

/**
 * Inject the brand panel div via login_message (rendered inside #login, then positioned fixed via CSS).
 *
 * @param string $message Existing login message HTML.
 * @return string
 */
function alkana_login_brand_panel( string $message ): string {
	$logo = esc_url( ALKANA_URI . '/assets/images/alkana-logo.png' );
	$bg   = esc_url( ALKANA_URI . '/assets/images/login-bg.jpg' );

	$panel  = '<div class="alk-brand-panel" aria-hidden="true" style="--alk-bg:url(' . $bg . ');">';
	$panel .= '<img src="' . $logo . '" alt="Alkana Coating" class="alk-brand-logo">';
	$panel .= '<p class="alk-brand-tag">Hệ thống quản trị<br>nội dung Alkana Coating</p>';
	$panel .= '</div>';

	return $panel . $message;
}

/**
 * Enqueue split-layout login CSS.
 */
function alkana_login_styles(): void {
	wp_register_style( 'alkana-login', false, [], ALKANA_VERSION );
	wp_enqueue_style( 'alkana-login' );
	wp_add_inline_style( 'alkana-login', alkana_get_login_css() );
}

/**
 * Build login page CSS — split layout with animated gradient background.
 */
function alkana_get_login_css(): string {
	return "
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

/* ── Layout ── */
body.login {
	margin: 0 !important;
	padding: 0 !important;
	background: none !important;
	min-height: 100vh;
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Right half white background */
body.login::before {
	content: '';
	position: fixed;
	top: 0; right: 0;
	width: 50%;
	height: 100vh;
	background: #f6f7f7;
	z-index: 0;
}

/* Left brand panel (injected via login_message, positioned fixed) */
.alk-brand-panel {
	position: fixed;
	top: 0; left: 0;
	width: 50%;
	height: 100vh;
	background: linear-gradient(145deg, #4C0682 0%, #8236BC 60%, #67219D 100%),
	            var(--alk-bg) center/cover no-repeat;
	background-blend-mode: multiply;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 20px;
	z-index: 1;
	pointer-events: none;
	padding: 0 40px;
	box-sizing: border-box;
}

.alk-brand-logo {
	width: 200px;
	height: auto;
	filter: brightness(0) invert(1);
	opacity: 0.95;
}

.alk-brand-tag {
	color: rgba(255,255,255,0.85);
	font-size: 15px;
	font-weight: 500;
	text-align: center;
	line-height: 1.6;
	margin: 0;
}

/* #login form — right half */
body.login #login {
	position: relative;
	z-index: 2;
	width: 50%;
	min-height: 100vh;
	margin-left: 50% !important;
	display: flex;
	flex-direction: column;
	justify-content: center;
	padding: 60px 48px;
	box-sizing: border-box;
	background: transparent;
	max-width: none !important;
}

body.login #login h1 a {
	background-image: none !important;
	width: auto !important;
	height: auto !important;
	font-size: 20px !important;
	font-weight: 700 !important;
	color: #4C0682 !important;
	text-decoration: none !important;
	font-family: 'Inter', sans-serif !important;
	display: block;
	margin-bottom: 4px;
}

body.login #login h1 a::before {
	content: 'Alkana Coating';
}

body.login #loginform {
	border-radius: 14px !important;
	border: 1px solid #e8d5f5 !important;
	box-shadow: 0 4px 24px rgba(76,6,130,0.08) !important;
	background: #fff !important;
	padding: 28px 28px 24px !important;
}

body.login #loginform label {
	font-family: 'Inter', sans-serif !important;
	font-weight: 500 !important;
	font-size: 13px !important;
	color: #4C0682 !important;
}

body.login #user_login,
body.login #user_pass {
	border-radius: 8px !important;
	border: 1px solid #d8bee8 !important;
	font-size: 14px !important;
	height: 42px !important;
	font-family: 'Inter', sans-serif !important;
	padding: 0 12px !important;
}

body.login #user_login:focus,
body.login #user_pass:focus {
	border-color: #67219D !important;
	box-shadow: 0 0 0 2px rgba(103,33,157,0.18) !important;
	outline: none !important;
}

body.login .wp-core-ui .button-primary {
	background: #67219D !important;
	border-color: #4C0682 !important;
	color: #fff !important;
	border-radius: 8px !important;
	font-family: 'Inter', sans-serif !important;
	font-weight: 600 !important;
	font-size: 14px !important;
	height: 44px !important;
	line-height: 44px !important;
	padding: 0 !important;
	width: 100% !important;
	text-shadow: none !important;
	box-shadow: none !important;
	transition: background 0.15s ease !important;
}

body.login .wp-core-ui .button-primary:hover,
body.login .wp-core-ui .button-primary:focus {
	background: #4C0682 !important;
	border-color: #3B0670 !important;
	box-shadow: 0 0 0 2px rgba(76,6,130,0.25) !important;
}

body.login #nav,
body.login #backtoblog {
	text-align: center;
}

body.login #nav a,
body.login #backtoblog a {
	color: #888 !important;
	font-size: 13px !important;
	text-decoration: none !important;
}

body.login #nav a:hover,
body.login #backtoblog a:hover {
	color: #67219D !important;
}

/* ── Mobile: single column ── */
@media (max-width: 782px) {
	body.login::before,
	.alk-brand-panel { display: none; }

	body.login #login {
		width: 100% !important;
		margin-left: 0 !important;
		padding: 40px 20px !important;
		min-height: auto !important;
		background: #f6f7f7 !important;
	}
}
";
}

function alkana_login_logo_url(): string {
	return esc_url( home_url( '/' ) );
}

function alkana_login_logo_title(): string {
	return esc_attr( get_bloginfo( 'name' ) );
}
