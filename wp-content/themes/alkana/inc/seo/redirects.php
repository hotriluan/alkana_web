<?php
/**
 * 301 Redirect Handler
 * Handles old Vietnamese URL paths → new English paths.
 *
 * @package Alkana
 */

defined('ABSPATH') || exit;

add_action('template_redirect', 'alkana_handle_legacy_redirects', 1);

/**
 * Redirect old Vietnamese URLs to new English URLs with 301 status.
 */
function alkana_handle_legacy_redirects(): void {
	$request_uri = $_SERVER['REQUEST_URI'] ?? '';
	
	// Normalize trailing slashes
	$request_uri = rtrim($request_uri, '/') . '/';
	
	// Parse URL to get path only (strip query strings)
	$parsed = parse_url($request_uri);
	$path = $parsed['path'] ?? '/';
	
	// Redirect map: old Vietnamese paths → new English paths
	$redirect_map = [
		'/san-pham/'   => '/products/',
		'/cong-trinh/' => '/projects/',
		'/tin-tuc/'    => '/news/',
		'/lien-he/'    => '/contact/',
		'/gioi-thieu/' => '/about/',
		'/giai-phap/'  => '/solutions/',
		'/tai-nguyen/' => '/resources/',
		'/tuyen-dung/' => '/careers/',
	];
	
	// Check for exact archive matches
	if (isset($redirect_map[$path])) {
		$new_url = home_url($redirect_map[$path]);
		
		// Preserve query string
		if (!empty($parsed['query'])) {
			$new_url .= '?' . $parsed['query'];
		}
		
		wp_redirect($new_url, 301);
		exit;
	}
	
	// Handle individual product/project/post redirects
	// Pattern: /san-pham/slug/ → /products/slug/
	foreach ($redirect_map as $old_base => $new_base) {
		if (strpos($path, $old_base) === 0) {
			$slug = str_replace($old_base, '', $path);
			$new_url = home_url($new_base . $slug);
			
			// Preserve query string
			if (!empty($parsed['query'])) {
				$new_url .= '?' . $parsed['query'];
			}
			
			wp_redirect($new_url, 301);
			exit;
		}
	}
}
