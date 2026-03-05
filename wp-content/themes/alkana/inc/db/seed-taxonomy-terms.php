<?php
/**
 * Taxonomy term seeder for Alkana product catalogue.
 *
 * HOW TO USE (two options):
 *   1. WP-CLI: wp eval-file inc/db/seed-taxonomy-terms.php
 *   2. One-time admin action: add ?alkana_seed_terms=1 to admin URL while logged in as admin
 *      then remove this file (or disable the hook) after use.
 *
 * SAFE TO RUN MULTIPLE TIMES — uses wp_insert_term() which skips duplicates.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

// ── One-time admin URL trigger (remove after first use) ───────────────────────
add_action( 'admin_init', static function (): void {
	if ( ! isset( $_GET['alkana_seed_terms'] ) ) {
		return;
	}
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_GET['_nonce'] ?? '', 'alkana_seed' ) ) {
		wp_die( 'Invalid nonce.' );
	}

	alkana_seed_all_taxonomy_terms();
	wp_die( 'Alkana taxonomy terms seeded successfully. You may remove inc/db/seed-taxonomy-terms.php now.' );
} );

/**
 * Seed all taxonomy terms.
 * Called from WP-CLI or admin trigger above.
 */
function alkana_seed_all_taxonomy_terms(): void {
	alkana_seed_product_categories();
	alkana_seed_surface_types();
	alkana_seed_paint_systems();
	alkana_seed_gloss_levels();

	if ( function_exists( 'WP_CLI' ) ) {
		WP_CLI::success( 'All Alkana taxonomy terms seeded.' );
	}
}

// ── Product Categories ─────────────────────────────────────────────────────────
function alkana_seed_product_categories(): void {
	$categories = [
		[
			'name'        => 'Son gỗ',
			'slug'        => 'wood-coating',
			'description' => 'Sơn và vecni bảo vệ bề mặt gỗ nội thất, ngoại thất',
			'children'    => [
				[ 'name' => 'Vecni gỗ trong suốt', 'slug' => 'wood-varnish-clear' ],
				[ 'name' => 'Sơn phủ màu gỗ', 'slug' => 'wood-color-topcoat' ],
				[ 'name' => 'Sơn lót gỗ', 'slug' => 'wood-primer' ],
			],
		],
		[
			'name'        => 'Sơn công nghiệp',
			'slug'        => 'industrial-paint',
			'description' => 'Sơn chống rỉ, bảo vệ kim loại và kết cấu thép',
			'children'    => [
				[ 'name' => 'Sơn chống rỉ', 'slug' => 'anti-rust' ],
				[ 'name' => 'Sơn epoxy', 'slug' => 'epoxy-coating' ],
				[ 'name' => 'Sơn polyurethane', 'slug' => 'polyurethane-coating' ],
				[ 'name' => 'Sơn dầu alkyd', 'slug' => 'alkyd-oil-paint' ],
			],
		],
		[
			'name'        => 'Chống thấm',
			'slug'        => 'waterproofing',
			'description' => 'Vật liệu chống thấm cho mái, tường, sàn, bể chứa',
			'children'    => [
				[ 'name' => 'Chống thấm mái', 'slug' => 'roof-waterproofing' ],
				[ 'name' => 'Chống thấm tường', 'slug' => 'wall-waterproofing' ],
				[ 'name' => 'Chống thấm sàn & bể', 'slug' => 'floor-tank-waterproofing' ],
				[ 'name' => 'Màng chống thấm', 'slug' => 'waterproof-membrane' ],
			],
		],
		[
			'name'        => 'Sơn trang trí',
			'slug'        => 'decorative-paint',
			'description' => 'Sơn nội thất, ngoại thất trang trí cho công trình dân dụng',
			'children'    => [
				[ 'name' => 'Sơn nội thất', 'slug' => 'interior-paint' ],
				[ 'name' => 'Sơn ngoại thất', 'slug' => 'exterior-paint' ],
				[ 'name' => 'Sơn hiệu ứng đặc biệt', 'slug' => 'special-effect-paint' ],
			],
		],
		[
			'name'        => 'Hóa chất xây dựng',
			'slug'        => 'construction-chemical',
			'description' => 'Keo dán, vữa sửa chữa, phụ gia bê tông',
			'children'    => [
				[ 'name' => 'Keo dán gạch', 'slug' => 'tile-adhesive' ],
				[ 'name' => 'Vữa sửa chữa', 'slug' => 'repair-mortar' ],
				[ 'name' => 'Phụ gia bê tông', 'slug' => 'concrete-admixture' ],
			],
		],
		[
			'name'        => 'Sơn sàn',
			'slug'        => 'floor-coating',
			'description' => 'Sơn epoxy sàn, sơn PU sàn cho nhà xưởng, bãi đỗ xe',
			'children'    => [
				[ 'name' => 'Epoxy sàn', 'slug' => 'floor-epoxy' ],
				[ 'name' => 'PU sàn',    'slug' => 'floor-pu' ],
			],
		],
	];

	foreach ( $categories as $cat ) {
		$parent_result = wp_insert_term(
			$cat['name'],
			'product_category',
			[
				'slug'        => $cat['slug'],
				'description' => $cat['description'] ?? '',
			]
		);

		$parent_id = is_wp_error( $parent_result )
			? ( $parent_result->get_error_data( 'term_exists' )['term_id'] ?? 0 )
			: $parent_result['term_id'];

		if ( ! empty( $cat['children'] ) && $parent_id ) {
			foreach ( $cat['children'] as $child ) {
				wp_insert_term( $child['name'], 'product_category', [
					'slug'   => $child['slug'],
					'parent' => $parent_id,
				] );
			}
		}
	}
}

// ── Surface Types ──────────────────────────────────────────────────────────────
function alkana_seed_surface_types(): void {
	$terms = [
		[ 'name' => 'Gỗ',                'slug' => 'wood',          'desc' => 'Gỗ tự nhiên, gỗ công nghiệp' ],
		[ 'name' => 'Kim loại / Thép',   'slug' => 'metal-steel',   'desc' => 'Thép, sắt, nhôm, inox' ],
		[ 'name' => 'Bê tông & Xi măng', 'slug' => 'concrete',      'desc' => 'Tường bê tông, trần, sàn xi măng' ],
		[ 'name' => 'Tường vữa',         'slug' => 'plaster-wall',  'desc' => 'Tường vữa xi măng nội/ngoại thất' ],
		[ 'name' => 'Mái ngói & Tôn',    'slug' => 'roof-tile',     'desc' => 'Ngói, tôn kim loại, fibro xi măng' ],
		[ 'name' => 'Sàn bê tông',       'slug' => 'concrete-floor','desc' => 'Sàn nhà xưởng, bãi đỗ xe, garage' ],
		[ 'name' => 'Bể nước & Hồ bơi',  'slug' => 'water-tank',    'desc' => 'Bồn chứa nước, hồ bơi, kênh mương' ],
		[ 'name' => 'Nhựa & PVC',        'slug' => 'plastic-pvc',   'desc' => 'Ống nhựa, cửa nhựa uPVC' ],
	];

	foreach ( $terms as $term ) {
		wp_insert_term( $term['name'], 'surface_type', [
			'slug'        => $term['slug'],
			'description' => $term['desc'],
		] );
	}
}

// ── Paint Systems ──────────────────────────────────────────────────────────────
function alkana_seed_paint_systems(): void {
	$terms = [
		[ 'name' => 'Hệ gốc nước',           'slug' => 'water-based',     'desc' => 'Water-based / latex — thân thiện môi trường' ],
		[ 'name' => 'Hệ dung môi (Solvent)',  'slug' => 'solvent-based',   'desc' => 'Solvent-based — độ bám cao' ],
		[ 'name' => 'Hệ Epoxy 2 thành phần', 'slug' => 'epoxy-2k',        'desc' => 'Epoxy 2K — bền hóa chất, cơ học cao' ],
		[ 'name' => 'Hệ PU 2 thành phần',    'slug' => 'pu-2k',           'desc' => 'Polyurethane 2K — bóng cao, kháng tia UV' ],
		[ 'name' => 'Hệ Alkyd dầu',          'slug' => 'alkyd',           'desc' => 'Oil alkyd — sơn dầu truyền thống' ],
		[ 'name' => 'Hệ 1 thành phần',       'slug' => 'single-component','desc' => 'Single-component ready-to-use' ],
		[ 'name' => 'Chống thấm Acrylic',    'slug' => 'acrylic-waterproof','desc' => 'Acrylic-based waterproofing' ],
		[ 'name' => 'Chống thấm Polyurethane','slug' => 'pu-waterproof',   'desc' => 'PU membrane / coating waterproofing' ],
	];

	foreach ( $terms as $term ) {
		wp_insert_term( $term['name'], 'paint_system', [
			'slug'        => $term['slug'],
			'description' => $term['desc'],
		] );
	}
}

// ── Gloss Levels ──────────────────────────────────────────────────────────────
function alkana_seed_gloss_levels(): void {
	$terms = [
		[ 'name' => 'Mờ (Matte)',     'slug' => 'matte',      'desc' => '0–10 GU @ 60°' ],
		[ 'name' => 'Lụa (Satin)',    'slug' => 'satin',      'desc' => '10–35 GU @ 60°' ],
		[ 'name' => 'Bán bóng',       'slug' => 'semi-gloss', 'desc' => '35–70 GU @ 60°' ],
		[ 'name' => 'Bóng (Gloss)',   'slug' => 'gloss',      'desc' => '70–85 GU @ 60°' ],
		[ 'name' => 'Bóng cao',       'slug' => 'high-gloss', 'desc' => '85–100 GU @ 60°' ],
		[ 'name' => 'Chống trượt',    'slug' => 'anti-slip',  'desc' => 'Kết cấu chống trượt — dùng cho sàn' ],
	];

	foreach ( $terms as $term ) {
		wp_insert_term( $term['name'], 'gloss_level', [
			'slug'        => $term['slug'],
			'description' => $term['desc'],
		] );
	}
}

// ── WP-CLI direct call ─────────────────────────────────────────────────────────
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	alkana_seed_all_taxonomy_terms();
}
