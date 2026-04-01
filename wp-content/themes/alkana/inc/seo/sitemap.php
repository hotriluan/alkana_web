<?php
/**
 * XML Sitemap Generator
 * Generates dynamic sitemap.xml for SEO.
 * Skips if Yoast or RankMath is active.
 *
 * @package Alkana
 */

defined('ABSPATH') || exit;

add_action('init', 'alkana_register_sitemap');
add_action('template_redirect', 'alkana_serve_sitemap');

/**
 * Register rewrite rule for /sitemap.xml
 */
function alkana_register_sitemap(): void {
	// Skip if SEO plugin is active
	if (alkana_has_seo_plugin()) {
		return;
	}
	
	add_rewrite_rule('^sitemap\.xml$', 'index.php?alkana_sitemap=1', 'top');
	add_filter('query_vars', function($vars) {
		$vars[] = 'alkana_sitemap';
		return $vars;
	});
}

/**
 * Check if Yoast or RankMath is active.
 */
function alkana_has_seo_plugin(): bool {
	return (
		defined('WPSEO_VERSION') ||
		class_exists('RankMath') ||
		defined('RANK_MATH_VERSION')
	);
}

/**
 * Serve XML sitemap when /sitemap.xml is requested.
 */
function alkana_serve_sitemap(): void {
	if (!get_query_var('alkana_sitemap')) {
		return;
	}
	
	// Skip if SEO plugin is active
	if (alkana_has_seo_plugin()) {
		return;
	}
	
	header('Content-Type: application/xml; charset=UTF-8');
	echo alkana_generate_sitemap_xml();
	exit;
}

/**
 * Generate sitemap XML content.
 */
function alkana_generate_sitemap_xml(): string {
	$urls = [];
	
	// Homepage
	$urls[] = [
		'loc'        => home_url('/'),
		'lastmod'    => get_lastpostmodified('gmt'),
		'changefreq' => 'weekly',
		'priority'   => '1.0',
	];
	
	// Pages
	$pages = get_posts([
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	]);
	
	foreach ($pages as $page) {
		$urls[] = [
			'loc'        => get_permalink($page),
			'lastmod'    => get_post_modified_time('c', true, $page),
			'changefreq' => 'monthly',
			'priority'   => '0.6',
		];
	}
	
	// Products
	$products = get_posts([
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 1000,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	]);
	
	foreach ($products as $product) {
		$urls[] = [
			'loc'        => get_permalink($product),
			'lastmod'    => get_post_modified_time('c', true, $product),
			'changefreq' => 'weekly',
			'priority'   => '0.8',
		];
	}
	
	// Projects
	$projects = get_posts([
		'post_type'      => 'project',
		'post_status'    => 'publish',
		'posts_per_page' => 1000,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	]);
	
	foreach ($projects as $project) {
		$urls[] = [
			'loc'        => get_permalink($project),
			'lastmod'    => get_post_modified_time('c', true, $project),
			'changefreq' => 'monthly',
			'priority'   => '0.7',
		];
	}
	
	// Blog posts
	$posts = get_posts([
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1000,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	]);
	
	foreach ($posts as $post) {
		$urls[] = [
			'loc'        => get_permalink($post),
			'lastmod'    => get_post_modified_time('c', true, $post),
			'changefreq' => 'weekly',
			'priority'   => '0.5',
		];
	}
	
	// Jobs
	$jobs = get_posts([
		'post_type'      => 'job',
		'post_status'    => 'publish',
		'posts_per_page' => 100,
		'orderby'        => 'modified',
		'order'          => 'DESC',
	]);
	
	foreach ($jobs as $job) {
		$urls[] = [
			'loc'        => get_permalink($job),
			'lastmod'    => get_post_modified_time('c', true, $job),
			'changefreq' => 'monthly',
			'priority'   => '0.6',
		];
	}
	
	// Build XML
	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	
	foreach ($urls as $url) {
		$xml .= "  <url>\n";
		$xml .= '    <loc>' . esc_url($url['loc']) . "</loc>\n";
		$xml .= '    <lastmod>' . esc_xml($url['lastmod']) . "</lastmod>\n";
		$xml .= '    <changefreq>' . esc_xml($url['changefreq']) . "</changefreq>\n";
		$xml .= '    <priority>' . esc_xml($url['priority']) . "</priority>\n";
		$xml .= "  </url>\n";
	}
	
	$xml .= '</urlset>';
	
	return $xml;
}
