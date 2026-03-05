<?php
/**
 * import-products.php — WP-CLI product import script.
 *
 * Reads a cleansed Excel file and upserts alkana_product posts.
 * Idempotent: matching on SKU — re-running does NOT create duplicates.
 *
 * Usage (from theme/plugin root or WordPress root):
 *   wp eval-file scripts/import-products.php
 *   wp eval-file scripts/import-products.php -- --file=clean-alkana-products.xlsx --dry-run
 *
 * Required: PhpSpreadsheet (install via Composer in scripts/):
 *   cd scripts && composer require phpoffice/phpspreadsheet
 *
 * Required columns in Excel (row 1 = headers, data starts row 2):
 *   A  old_url           — kept for URL mapping/logging only
 *   B  title             — post_title for alkana_product
 *   C  description       — post_content
 *   D  sku               — ACF _alkana_sku (match key for upsert)
 *   E  image_url         — sideloaded to Media Library as featured image
 *   F  tds_url           — ACF _alkana_tds  (PDF, sideloaded)
 *   G  msds_url          — ACF _alkana_msds (PDF, sideloaded)
 *   H  old_category      — not used in import (informational only)
 *   I  new_category_slug — wp_set_post_terms → product_category
 *   J  surface_types     — comma-sep slugs   → surface_type
 *   K  paint_system      — slug              → paint_system
 *   L  gloss_level       — slug              → gloss_level
 *   M  coverage          — ACF _alkana_coverage
 *   N  mix_ratio         — ACF _alkana_mix_ratio
 *   O  thinner           — ACF _alkana_thinner
 *   P  dry_touch         — ACF _alkana_dry_touch
 *   Q  dry_hard          — ACF _alkana_dry_hard
 *   R  dry_recoat        — ACF _alkana_dry_recoat
 *   S  new_url_slug      — post_name (URL slug)
 *   T  notes             — ignored
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || ( defined( 'WP_CLI' ) ? null : exit );

// ── Bootstrap PhpSpreadsheet ──────────────────────────────────────────────────

$autoload = __DIR__ . '/vendor/autoload.php';
if ( ! file_exists( $autoload ) ) {
	WP_CLI::error( "PhpSpreadsheet not found. Run: cd scripts && composer require phpoffice/phpspreadsheet" );
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// ── Parse CLI args ────────────────────────────────────────────────────────────

$args    = WP_CLI::get_runner()->arguments ?? [];
$extra   = WP_CLI::get_runner()->extra_args ?? [];
$file    = $extra['file']    ?? __DIR__ . '/clean-alkana-products.xlsx';
$dry_run = isset( $extra['dry-run'] );
$limit   = (int) ( $extra['limit'] ?? 9999 );
$skip    = (int) ( $extra['skip']  ?? 0 );

if ( ! file_exists( $file ) ) {
	WP_CLI::error( "File not found: {$file}\nExpected path: " . realpath( dirname( $file ) ) . '/' . basename( $file ) );
}

WP_CLI::log( sprintf( "Reading: %s%s", $file, $dry_run ? ' [DRY RUN]' : '' ) );

// ── Load spreadsheet ──────────────────────────────────────────────────────────

$reader      = IOFactory::createReaderForFile( $file );
$reader->setReadDataOnly( true );
$spreadsheet = $reader->load( $file );
$sheet       = $spreadsheet->getActiveSheet();
$rows        = $sheet->toArray( null, true, true, true );
array_shift( $rows ); // remove header row

$total   = min( count( $rows ), $limit );
$created = 0;
$updated = 0;
$skipped = 0;
$errors  = [];

WP_CLI::log( sprintf( "Found %d data rows (importing up to %d, skipping first %d).", count( $rows ), $limit, $skip ) );

// ── Process rows ──────────────────────────────────────────────────────────────

$i = 0;
foreach ( $rows as $row ) {
	$i++;
	if ( $i <= $skip ) continue;
	if ( $i > $skip + $limit ) break;

	$title        = trim( (string) ( $row['B'] ?? '' ) );
	$description  = trim( (string) ( $row['C'] ?? '' ) );
	$sku          = trim( (string) ( $row['D'] ?? '' ) );
	$image_url    = trim( (string) ( $row['E'] ?? '' ) );
	$tds_url      = trim( (string) ( $row['F'] ?? '' ) );
	$msds_url     = trim( (string) ( $row['G'] ?? '' ) );
	$cat_slug     = trim( (string) ( $row['I'] ?? '' ) );
	$surface_raw  = trim( (string) ( $row['J'] ?? '' ) );
	$paint_system = trim( (string) ( $row['K'] ?? '' ) );
	$gloss_level  = trim( (string) ( $row['L'] ?? '' ) );
	$coverage     = trim( (string) ( $row['M'] ?? '' ) );
	$mix_ratio    = trim( (string) ( $row['N'] ?? '' ) );
	$thinner      = trim( (string) ( $row['O'] ?? '' ) );
	$dry_touch    = trim( (string) ( $row['P'] ?? '' ) );
	$dry_hard     = trim( (string) ( $row['Q'] ?? '' ) );
	$dry_recoat   = trim( (string) ( $row['R'] ?? '' ) );
	$url_slug     = sanitize_title( trim( (string) ( $row['S'] ?? '' ) ) ?: $title );

	if ( empty( $title ) ) {
		WP_CLI::warning( "Row {$i}: empty title — skipping." );
		$skipped++;
		continue;
	}

	// ── Upsert by SKU ─────────────────────────────────────────────────────────
	$existing_posts = [];
	if ( $sku ) {
		$existing_posts = get_posts( [
			'post_type'   => 'alkana_product',
			'post_status' => 'any',
			'meta_key'    => '_alkana_sku',
			'meta_value'  => $sku,
			'numberposts' => 1,
			'fields'      => 'ids',
		] );
	}

	$is_update = ! empty( $existing_posts );
	$post_id   = $existing_posts[0] ?? 0;

	$post_data = [
		'post_type'    => 'alkana_product',
		'post_title'   => $title,
		'post_content' => wp_kses_post( $description ),
		'post_status'  => 'publish',
		'post_name'    => $url_slug,
	];

	if ( ! $dry_run ) {
		if ( $is_update ) {
			$post_data['ID'] = $post_id;
			$result = wp_update_post( $post_data, true );
		} else {
			$result = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "Row {$i}: " . $result->get_error_message() );
			$errors[] = "Row {$i} ({$title}): " . $result->get_error_message();
			continue;
		}
		$post_id = $result;
	}

	// ── ACF fields ────────────────────────────────────────────────────────────
	$acf_fields = [
		'_alkana_sku'       => $sku,
		'_alkana_coverage'  => $coverage,
		'_alkana_mix_ratio' => $mix_ratio,
		'_alkana_thinner'   => $thinner,
		'_alkana_dry_touch' => $dry_touch,
		'_alkana_dry_hard'  => $dry_hard,
		'_alkana_dry_recoat'=> $dry_recoat,
	];

	if ( ! $dry_run ) {
		foreach ( $acf_fields as $key => $value ) {
			if ( '' !== $value ) {
				update_field( $key, $value, $post_id );
			}
		}
	}

	// ── Taxonomies ────────────────────────────────────────────────────────────
	if ( ! $dry_run ) {
		if ( $cat_slug ) {
			$cat_term = get_term_by( 'slug', $cat_slug, 'product_category' );
			if ( $cat_term ) {
				wp_set_post_terms( $post_id, [ $cat_term->term_id ], 'product_category' );
			} else {
				WP_CLI::warning( "Row {$i}: product_category slug '{$cat_slug}' not found — skipping taxonomy." );
			}
		}

		if ( $surface_raw ) {
			$surface_slugs = array_map( 'trim', explode( ',', $surface_raw ) );
			$surface_ids   = [];
			foreach ( $surface_slugs as $slug ) {
				$t = get_term_by( 'slug', $slug, 'surface_type' );
				if ( $t ) $surface_ids[] = $t->term_id;
				else WP_CLI::warning( "Row {$i}: surface_type slug '{$slug}' not found." );
			}
			if ( $surface_ids ) wp_set_post_terms( $post_id, $surface_ids, 'surface_type' );
		}

		if ( $paint_system ) {
			$t = get_term_by( 'slug', $paint_system, 'paint_system' );
			if ( $t ) wp_set_post_terms( $post_id, [ $t->term_id ], 'paint_system' );
			else WP_CLI::warning( "Row {$i}: paint_system slug '{$paint_system}' not found." );
		}

		if ( $gloss_level ) {
			$t = get_term_by( 'slug', $gloss_level, 'gloss_level' );
			if ( $t ) wp_set_post_terms( $post_id, [ $t->term_id ], 'gloss_level' );
			else WP_CLI::warning( "Row {$i}: gloss_level slug '{$gloss_level}' not found." );
		}
	}

	// ── Media sideload ────────────────────────────────────────────────────────
	if ( ! $dry_run ) {
		if ( $image_url && ! has_post_thumbnail( $post_id ) ) {
			$attach_id = alkana_sideload_media( $image_url, $post_id, $title );
			if ( $attach_id ) set_post_thumbnail( $post_id, $attach_id );
		}

		if ( $tds_url ) {
			$tds_id = alkana_sideload_media( $tds_url, $post_id, "{$title} TDS" );
			if ( $tds_id ) {
				$tds_file = wp_get_attachment_url( $tds_id );
				update_field( '_alkana_tds', [ 'ID' => $tds_id, 'url' => $tds_file ], $post_id );
			}
		}

		if ( $msds_url ) {
			$msds_id = alkana_sideload_media( $msds_url, $post_id, "{$title} MSDS" );
			if ( $msds_id ) {
				$msds_file = wp_get_attachment_url( $msds_id );
				update_field( '_alkana_msds', [ 'ID' => $msds_id, 'url' => $msds_file ], $post_id );
			}
		}

		// ── Trigger denormalized index sync ──────────────────────────────────
		do_action( 'acf/save_post', $post_id );
	}

	$action = $dry_run ? 'DRY' : ( $is_update ? 'updated' : 'created' );
	WP_CLI::log( sprintf( "[%d/%d] %s — %s (ID: %d)", $i, $total + $skip, strtoupper( $action ), $title, $post_id ) );

	if ( $is_update ) $updated++;
	else $created++;
}

// ── Summary ───────────────────────────────────────────────────────────────────

WP_CLI::success( sprintf(
	"Import complete: %d created, %d updated, %d skipped, %d errors.",
	$created, $updated, $skipped, count( $errors )
) );

if ( $errors ) {
	WP_CLI::log( "\nErrors:" );
	foreach ( $errors as $err ) WP_CLI::log( "  - {$err}" );
}


// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Download a remote file and add it to the Media Library.
 * Skips download if a media item with the same source URL already exists.
 *
 * @param string $url      Remote file URL.
 * @param int    $post_id  Attachment parent post ID.
 * @param string $title    Attachment title / alt text.
 * @return int|false Attachment post ID or false on failure.
 */
function alkana_sideload_media( string $url, int $post_id, string $title = '' ) {
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) return false;

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	// Check if already imported (meta _alkana_source_url)
	$existing = get_posts( [
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_alkana_source_url',
		'meta_value'  => $url,
		'numberposts' => 1,
		'fields'      => 'ids',
	] );

	if ( ! empty( $existing ) ) {
		return $existing[0];
	}

	$tmp = download_url( $url, 30 );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "Download failed for {$url}: " . $tmp->get_error_message() );
		return false;
	}

	$file_array = [
		'name'     => basename( wp_parse_url( $url, PHP_URL_PATH ) ),
		'tmp_name' => $tmp,
	];

	$attach_id = media_handle_sideload( $file_array, $post_id, $title );

	if ( is_wp_error( $attach_id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "Sideload failed for {$url}: " . $attach_id->get_error_message() );
		return false;
	}

	// Store source URL to prevent re-import
	update_post_meta( $attach_id, '_alkana_source_url', $url );

	// Set alt text
	if ( $title ) update_post_meta( $attach_id, '_wp_attachment_image_alt', sanitize_text_field( $title ) );

	return $attach_id;
}
