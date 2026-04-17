<?php
/**
 * Resolve real client IP, respecting CDN / reverse proxy headers.
 *
 * Checks headers in priority order: Cloudflare/QuickCloud → X-Forwarded-For → REMOTE_ADDR.
 * Validates each candidate; falls back gracefully for local/LAN environments.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alkana_get_client_ip' ) ) {
	/**
	 * Return the real client IP address.
	 *
	 * @return string Valid IP address string, or '0.0.0.0' as last resort.
	 */
	function alkana_get_client_ip(): string {
		$headers = [
			'HTTP_CF_CONNECTING_IP', // Cloudflare / QuickCloud CDN
			'HTTP_X_FORWARDED_FOR',  // General reverse proxy
			'REMOTE_ADDR',           // Direct connection fallback
		];

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}
			// X-Forwarded-For may be comma-separated; first entry is client-originating IP.
			$ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return $ip;
			}
		}

		// Last resort: REMOTE_ADDR without private range check (local dev / LAN).
		$remote = trim( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) ) );
		return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '0.0.0.0';
	}
}
