<?php
/**
 * Comprehensive site content seeder for Alkana B2B website.
 *
 * Seeds: WordPress pages (with template assignment), navigation menus,
 * footer theme mods, blog posts + categories, career openings,
 * and product spec enhancement.
 *
 * Usage:   wp eval-file scripts/seed-site-content.php
 * Requires: seed-dummy-data.php run first (products + projects).
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || die( "Run via WP-CLI: wp eval-file scripts/seed-site-content.php\n" );

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Reuse sideload helper from seed-dummy-data.php.
if ( ! function_exists( 'alkana_seed_sideload' ) ) {
	function alkana_seed_sideload( string $url, int $post_id, string $desc ): int {
		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			WP_CLI::warning( "  Download fail ({$desc}): " . $tmp->get_error_message() );
			return 0;
		}
		$file_array = [ 'name' => sanitize_file_name( $desc ) . '.jpg', 'tmp_name' => $tmp ];
		$att_id     = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error( $att_id ) ) {
			wp_delete_file( $tmp );
			WP_CLI::warning( "  Sideload fail ({$desc}): " . $att_id->get_error_message() );
			return 0;
		}
		return (int) $att_id;
	}
}

// ── 1. WordPress Pages ────────────────────────────────────────────────────────

WP_CLI::log( "\n── Seeding Pages ──" );

$pages_def = [
	[ 'title' => 'Dự án',    'slug' => 'du-an',     'template' => 'templates/page-projects.php' ],
	[ 'title' => 'Liên hệ',  'slug' => 'lien-he',   'template' => 'templates/page-contact.php' ],
	[ 'title' => 'Bài viết', 'slug' => 'bai-viet',  'template' => 'templates/page-news.php' ],
	[ 'title' => 'Tuyển dụng', 'slug' => 'tuyen-dung', 'template' => 'templates/page-careers.php' ],
	[ 'title' => 'Giới thiệu', 'slug' => 'gioi-thieu', 'template' => 'templates/page-about.php',
	  'content' => '<h2>Về Alkana Coating</h2>
<p>Alkana Coating là đơn vị hàng đầu Việt Nam chuyên sản xuất và cung cấp sơn công nghiệp, sơn chống ăn mòn, và các giải pháp chống thấm cho công trình quy mô lớn.</p>
<h3>Sứ mệnh</h3>
<p>Mang đến giải pháp bảo vệ bề mặt tối ưu, giúp kéo dài tuổi thọ công trình và giảm chi phí bảo trì cho khách hàng.</p>
<h3>Tầm nhìn</h3>
<p>Trở thành thương hiệu sơn công nghiệp số 1 Đông Nam Á vào năm 2030, với sản phẩm đạt tiêu chuẩn quốc tế và dịch vụ kỹ thuật vượt trội.</p>
<h3>Giá trị cốt lõi</h3>
<ul>
<li><strong>Chất lượng</strong> — Sản phẩm đạt chuẩn ISO 12944, SSPC, NACE</li>
<li><strong>Đổi mới</strong> — Đầu tư 8% doanh thu cho R&D hàng năm</li>
<li><strong>Bền vững</strong> — Cam kết giảm VOC và phát triển sản phẩm xanh</li>
<li><strong>Đối tác</strong> — Hỗ trợ kỹ thuật tận công trường 24/7</li>
</ul>' ],
	[ 'title' => 'Giải pháp', 'slug' => 'giai-phap', 'template' => 'templates/page-solutions.php',
	  'content' => '<p>Alkana cung cấp hệ thống sơn toàn diện cho các ngành công nghiệp trọng điểm: dầu khí, hạ tầng giao thông, nhà xưởng sản xuất, năng lượng, và xây dựng dân dụng cao cấp.</p>' ],
];

$page_ids = [];
foreach ( $pages_def as $p ) {
	$existing = get_page_by_path( $p['slug'] );
	if ( $existing ) {
		$page_ids[ $p['slug'] ] = $existing->ID;
		update_post_meta( $existing->ID, '_wp_page_template', $p['template'] );
		WP_CLI::log( "  Page '{$p['title']}' exists (ID {$existing->ID}), template updated." );
		continue;
	}
	$pid = wp_insert_post( [
		'post_title'   => $p['title'],
		'post_name'    => $p['slug'],
		'post_content' => $p['content'] ?? '',
		'post_type'    => 'page',
		'post_status'  => 'publish',
	] );
	if ( is_wp_error( $pid ) ) {
		WP_CLI::warning( "  Failed: {$p['title']}" );
		continue;
	}
	update_post_meta( $pid, '_wp_page_template', $p['template'] );
	$page_ids[ $p['slug'] ] = $pid;
	WP_CLI::log( "  ✓ {$p['title']} (ID {$pid})" );
}

WP_CLI::success( count( $page_ids ) . ' pages seeded.' );

// ── 2. Navigation Menus ───────────────────────────────────────────────────────

WP_CLI::log( "\n── Seeding Menus ──" );

// Primary menu — rebuild with all items including new pages.
$primary_menu = wp_get_nav_menu_object( 'Main Menu' );
$primary_id   = $primary_menu ? $primary_menu->term_id : wp_create_nav_menu( 'Main Menu' );

// Clear existing items for idempotency.
$old_items = wp_get_nav_menu_items( $primary_id );
if ( $old_items ) {
	foreach ( $old_items as $item ) {
		wp_delete_post( $item->ID, true );
	}
}

$primary_items = [
	'Trang chủ' => home_url( '/' ),
	'Sản phẩm'  => home_url( '/products/' ),
	'Dự án'     => home_url( '/projects/' ),
	'Bài viết'  => home_url( '/bai-viet/' ),
	'Tuyển dụng' => home_url( '/tuyen-dung/' ),
	'Liên hệ'   => home_url( '/lien-he/' ),
];

foreach ( $primary_items as $label => $url ) {
	wp_update_nav_menu_item( $primary_id, 0, [
		'menu-item-title'  => $label,
		'menu-item-url'    => $url,
		'menu-item-type'   => 'custom',
		'menu-item-status' => 'publish',
	] );
}

$locations            = get_theme_mod( 'nav_menu_locations', [] );
$locations['primary'] = $primary_id;

// Footer menu.
$footer_menu = wp_get_nav_menu_object( 'Footer Menu' );
$footer_id   = $footer_menu ? $footer_menu->term_id : wp_create_nav_menu( 'Footer Menu' );

$old_footer = wp_get_nav_menu_items( $footer_id );
if ( $old_footer ) {
	foreach ( $old_footer as $item ) {
		wp_delete_post( $item->ID, true );
	}
}

$footer_items = [
	'Sản phẩm'   => home_url( '/products/' ),
	'Dự án'      => home_url( '/projects/' ),
	'Giới thiệu' => home_url( '/gioi-thieu/' ),
	'Bài viết'   => home_url( '/bai-viet/' ),
	'Tuyển dụng' => home_url( '/tuyen-dung/' ),
	'Liên hệ'    => home_url( '/lien-he/' ),
];

foreach ( $footer_items as $label => $url ) {
	wp_update_nav_menu_item( $footer_id, 0, [
		'menu-item-title'  => $label,
		'menu-item-url'    => $url,
		'menu-item-type'   => 'custom',
		'menu-item-status' => 'publish',
	] );
}

$locations['footer'] = $footer_id;
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'Primary + footer menus seeded.' );

// ── 3. Footer Theme Mods ─────────────────────────────────────────────────────

WP_CLI::log( "\n── Seeding Footer Info ──" );

set_theme_mod( 'alkana_address', 'Lô C1-2, Đường N1, KCN Hiệp Phước, Nhà Bè, TP. Hồ Chí Minh' );
set_theme_mod( 'alkana_phone', '+84 28 3873 8888' );
set_theme_mod( 'alkana_email', 'info@alkana.vn' );

WP_CLI::success( 'Footer theme mods set.' );

// ── 4. Blog Posts ─────────────────────────────────────────────────────────────

WP_CLI::log( "\n── Seeding Blog Posts ──" );

$blog_posts = require __DIR__ . '/dummy-data/blog-post-data.php';

// Idempotency: delete existing posts.
$old_posts = get_posts( [ 'post_type' => 'post', 'numberposts' => -1, 'fields' => 'ids',
	'meta_key' => '_alkana_seeded', 'meta_value' => '1' ] );
if ( $old_posts ) {
	foreach ( $old_posts as $oid ) { wp_delete_post( $oid, true ); }
	WP_CLI::log( '  Cleaned ' . count( $old_posts ) . ' old blog posts.' );
}

// Ensure categories exist.
$cat_map = [
	'kien-thuc-ky-thuat' => 'Kiến thức Kỹ thuật',
	'tin-tuc-cong-ty'    => 'Tin tức Công ty',
	'case-study'         => 'Case Study',
];
foreach ( $cat_map as $slug => $name ) {
	if ( ! term_exists( $slug, 'category' ) ) {
		wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
	}
}

foreach ( $blog_posts as $bp ) {
	$pid = wp_insert_post( [
		'post_title'   => $bp['title'],
		'post_name'    => $bp['slug'],
		'post_content' => $bp['content'],
		'post_excerpt' => $bp['excerpt'],
		'post_type'    => 'post',
		'post_status'  => 'publish',
	] );
	if ( is_wp_error( $pid ) ) {
		WP_CLI::warning( "  Failed: {$bp['title']}" );
		continue;
	}
	update_post_meta( $pid, '_alkana_seeded', '1' );
	wp_set_object_terms( $pid, $bp['category'], 'category' );

	if ( ! empty( $bp['image'] ) ) {
		$att = alkana_seed_sideload( $bp['image'], $pid, $bp['title'] );
		if ( $att ) { set_post_thumbnail( $pid, $att ); }
	}
	WP_CLI::log( "  ✓ {$bp['title']}" );
}

WP_CLI::success( count( $blog_posts ) . ' blog posts seeded.' );

// ── 5. Career Openings ───────────────────────────────────────────────────────

WP_CLI::log( "\n── Seeding Career Data ──" );

$careers_page_id = $page_ids['tuyen-dung'] ?? 0;
if ( ! $careers_page_id ) {
	$cp = get_page_by_path( 'tuyen-dung' );
	$careers_page_id = $cp ? $cp->ID : 0;
}

if ( $careers_page_id ) {
	$career_data = require __DIR__ . '/dummy-data/career-data.php';
	update_post_meta( $careers_page_id, 'careers_openings', $career_data );
	update_post_meta( $careers_page_id, 'careers_contact_email', 'hr@alkana.vn' );
	WP_CLI::success( count( $career_data ) . ' career openings seeded on page ID ' . $careers_page_id );
} else {
	WP_CLI::warning( 'Careers page not found. Skipping career data.' );
}

// ── 6. Product Specs Enhancement ─────────────────────────────────────────────

WP_CLI::log( "\n── Enhancing Product Specs ──" );

$products = get_posts( [ 'post_type' => 'alkana_product', 'numberposts' => -1, 'post_status' => 'publish' ] );
$enhanced = 0;

foreach ( $products as $product ) {
	$pid      = $product->ID;
	$coverage = get_post_meta( $pid, '_alkana_coverage', true );
	$mix      = get_post_meta( $pid, '_alkana_mix_ratio', true );
	$thinner  = get_post_meta( $pid, '_alkana_thinner', true );
	$layer    = get_post_meta( $pid, '_alkana_layer', true );
	$dry_t    = get_post_meta( $pid, '_alkana_dry_touch', true );
	$dry_h    = get_post_meta( $pid, '_alkana_dry_hard', true );
	$dry_r    = get_post_meta( $pid, '_alkana_dry_recoat', true );

	// Build product_specs serialized array from individual fields.
	$specs_array = [];
	if ( $coverage ) $specs_array[] = [ 'spec_label' => 'Độ phủ lý thuyết', 'spec_value' => $coverage, 'spec_unit' => '' ];
	if ( $mix )      $specs_array[] = [ 'spec_label' => 'Tỷ lệ pha trộn', 'spec_value' => $mix, 'spec_unit' => '' ];
	if ( $thinner )  $specs_array[] = [ 'spec_label' => 'Dung môi pha', 'spec_value' => $thinner, 'spec_unit' => '' ];
	if ( $layer )    $specs_array[] = [ 'spec_label' => 'Lớp sơn', 'spec_value' => $layer, 'spec_unit' => '' ];
	if ( $dry_t )    $specs_array[] = [ 'spec_label' => 'Khô sờ được', 'spec_value' => $dry_t, 'spec_unit' => '' ];
	if ( $dry_h )    $specs_array[] = [ 'spec_label' => 'Khô cứng', 'spec_value' => $dry_h, 'spec_unit' => '' ];
	if ( $dry_r )    $specs_array[] = [ 'spec_label' => 'Sơn lớp tiếp', 'spec_value' => $dry_r, 'spec_unit' => '' ];

	if ( $specs_array ) {
		update_post_meta( $pid, 'product_specs', $specs_array );
		$enhanced++;
	}
}

WP_CLI::success( "{$enhanced} products enhanced with specs." );

// ── 7. Fix Featured Meta Key ─────────────────────────────────────────────────

WP_CLI::log( "\n── Fixing Featured Meta Key ──" );

$fixed = 0;
foreach ( $products as $product ) {
	$pid = $product->ID;
	$val = get_post_meta( $pid, 'is_featured', true );
	if ( '' !== $val ) {
		update_post_meta( $pid, '_alkana_featured', $val );
		delete_post_meta( $pid, 'is_featured' );
		$fixed++;
	}
}

WP_CLI::success( "{$fixed} products: is_featured → _alkana_featured." );

// ── Summary ──────────────────────────────────────────────────────────────────

WP_CLI::log( "\n✅ Site content seeded successfully." );
WP_CLI::log( "   Pages: " . count( $page_ids ) );
WP_CLI::log( "   Blog posts: " . count( $blog_posts ) );
WP_CLI::log( "   Career openings: " . ( $careers_page_id ? count( $career_data ) : 0 ) );
WP_CLI::log( "   Products enhanced: {$enhanced}" );
WP_CLI::log( "   Featured key fixed: {$fixed}" );
