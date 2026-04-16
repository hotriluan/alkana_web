<?php
/**
 * Single product template for alkana_product CPT.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<div class="container mx-auto px-4">
		<?php get_template_part( 'template-parts/breadcrumb' ); ?>
	</div>

<?php
$post_id     = get_the_ID();
$sku         = get_field( '_alkana_sku',             $post_id );
$tds         = get_field( '_alkana_tds',             $post_id );
$msds        = get_field( '_alkana_msds',            $post_id );
$variants    = alkana_get_product_variants( $post_id ); // Custom meta box helper
$certs       = alkana_get_product_certs( $post_id ); // Custom meta box helper
$video       = get_field( '_alkana_video',           $post_id );
$specs       = alkana_get_product_specs( $post_id );    // Custom meta box helper
// Hero Image is an ACF custom field (NOT the WordPress featured image)
$hero        = get_field( 'hero_image', $post_id ); // returns array with 'ID'
$hero_id     = isset( $hero['ID'] ) ? (int) $hero['ID'] : 0;

$gallery_raw = get_post_meta( $post_id, '_alkana_product_gallery', true );
// Accept JSON array (custom metabox format) or ACF array format
if ( is_array( $gallery_raw ) ) {
	$gallery_ids = array_map( 'intval', $gallery_raw );
} elseif ( is_string( $gallery_raw ) && strpos( $gallery_raw, '[' ) === 0 ) {
	$gallery_ids = array_map( 'intval', json_decode( $gallery_raw, true ) ?: [] );
} else {
	$gallery_ids = [];
}

// Build full image list: hero first, then additional gallery images (deduplicated)
$all_image_ids = [];
if ( $hero_id ) {
	$all_image_ids[] = $hero_id;
}
foreach ( $gallery_ids as $gid ) {
	$gid = (int) $gid;
	if ( $gid && ! in_array( $gid, $all_image_ids, true ) ) {
		$all_image_ids[] = $gid;
	}
}
// Final fallback: WordPress featured image
if ( empty( $all_image_ids ) ) {
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$all_image_ids[] = (int) $thumb_id;
	}
}
$primary_id = $all_image_ids[0] ?? 0;

// Decide which tabs to show
$has_specs    = $specs || get_field( '_alkana_coverage', $post_id ) || get_field( '_alkana_mix_ratio', $post_id );
$has_variants = ! empty( $variants );
$has_resources = $tds || $msds || ! empty( $certs );
$has_video    = ! empty( $video );

$tabs = [];
if ( $has_specs )    $tabs[] = [ 'id' => 'specs',     'label' => __( 'Specifications', 'alkana' ) ];
if ( $has_variants ) $tabs[] = [ 'id' => 'variants',  'label' => __( 'Variants',        'alkana' ) ];
if ( $has_resources ) $tabs[] = [ 'id' => 'resources', 'label' => __( 'Resources',       'alkana' ) ];
if ( $has_video )    $tabs[] = [ 'id' => 'video',     'label' => __( 'Video',           'alkana' ) ];
if ( get_the_content() ) $tabs[] = [ 'id' => 'details', 'label' => __( 'Details',          'alkana' ) ];
?>

	<div class="container mx-auto px-4 py-10">

		<?php // ── Hero row ────────────────────────────────────────────────── ?>
		<div class="product-detail lg:flex lg:gap-12">

			<?php // ── Gallery ───────────────────────────────────────────────── ?>
			<div class="product-detail__gallery lg:w-1/2">

				<?php // ── Main image viewport ──────────────────────────────── ?>
				<div class="aspect-[4/3] w-full bg-gray-50 rounded-lg overflow-hidden border border-gray-100 relative">
					<?php if ( $primary_id ) : ?>
						<?php echo wp_get_attachment_image( $primary_id, 'alkana-product-hero', false, [
							'id'             => 'main-product-image',
							'class'          => 'w-full h-full object-cover',
							'alt'            => get_the_title(),
							'loading'        => 'eager',
							'fetchpriority'  => 'high',
							'decoding'       => 'async',
							'sizes'          => '(max-width: 1024px) 100vw, 50vw',
						] ); ?>
					<?php else : ?>
						<div class="w-full h-full flex items-center justify-center">
							<span class="dashicons dashicons-products text-gray-300 text-6xl"></span>
						</div>
					<?php endif; ?>
				</div>

				<?php // ── Thumbnail strip (only when >1 image) ────────────── ?>
				<?php if ( count( $all_image_ids ) > 1 ) : ?>
				<div class="gallery-thumbnail-strip flex gap-2 overflow-x-auto snap-x snap-mandatory mt-4 custom-scrollbar" role="list" aria-label="<?php esc_attr_e( 'Product images', 'alkana' ); ?>">
					<?php foreach ( $all_image_ids as $idx => $img_id ) :
						$full_src    = wp_get_attachment_image_url( $img_id, 'large' );
						$full_srcset = wp_get_attachment_image_srcset( $img_id, 'large' );
						if ( ! $full_src ) continue;
					?>
					<button
						type="button"
						class="gallery-thumb w-20 h-20 flex-shrink-0 snap-start rounded-md overflow-hidden transition-opacity cursor-pointer border-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-alkana-purple-600 <?php echo $idx === 0 ? 'opacity-100 border-alkana-purple-600' : 'opacity-70 border-transparent hover:opacity-100'; ?>"
						data-image-url="<?php echo esc_attr( $full_src ); ?>"
						data-image-srcset="<?php echo esc_attr( $full_srcset ?: '' ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'View image %d', 'alkana' ), $idx + 1 ) ); ?>"
						role="listitem">
						<?php echo wp_get_attachment_image( $img_id, 'thumbnail', false, [
							'class'   => 'w-full h-full object-cover pointer-events-none',
							'alt'     => '',
							'loading' => 'lazy',
						] ); ?>
					</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

			</div>

			<?php // ── Product Info ──────────────────────────────────────────── ?>
			<div class="product-detail__info lg:w-1/2 mt-8 lg:mt-0">
				<?php
				$cats = get_the_terms( $post_id, 'product_category' );
				if ( $cats && ! is_wp_error( $cats ) ) :
					$cat = $cats[0];
				?>
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
					   class="text-xs font-medium text-[--color-primary] uppercase tracking-wide hover:underline">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endif; ?>

				<h1 class="product-detail__title text-3xl font-heading font-bold text-[--color-secondary] mt-2 mb-1">
					<?php the_title(); ?>
				</h1>

				<?php if ( $sku ) : ?>
					<p class="text-sm text-gray-400 font-mono mb-4">SKU: <?php echo esc_html( $sku ); ?></p>
				<?php endif; ?>

				<div class="product-detail__excerpt text-gray-600 mb-6 leading-relaxed">
					<?php the_excerpt(); ?>
				</div>

				<?php // ── Quick-access CTA row ────────────────────────────────── ?>
				<div class="product-detail__cta flex flex-wrap gap-3 mt-4" id="product-cta-main">
					<a href="<?php echo esc_url( alkana_get_contact_url() ); ?>"
					   class="btn btn--primary">
						<?php esc_html_e( 'Request Quote', 'alkana' ); ?>
					</a>
					<?php if ( $tds ) : ?>
						<a href="<?php echo esc_url( $tds['url'] ); ?>"
						   class="btn btn--outline"
						   download
						   target="_blank"
						   rel="noopener noreferrer">
							<?php esc_html_e( 'Download TDS', 'alkana' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php // ── Tabbed section ──────────────────────────────────────────── ?>
		<?php if ( ! empty( $tabs ) ) : ?>
		<div class="product-tabs mt-14" data-tabs>

			<?php // Tab nav ?>
			<div class="product-tabs__nav flex gap-1 border-b border-[--color-border] overflow-x-auto" role="tablist" aria-label="<?php esc_attr_e( 'Product information', 'alkana' ); ?>">
				<?php foreach ( $tabs as $i => $tab ) : ?>
					<button
						class="product-tabs__btn px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
						       <?php echo $i === 0 ? 'border-[--color-primary] text-[--color-primary]' : 'border-transparent text-gray-500 hover:text-[--color-secondary]'; ?>"
						data-tab-target="<?php echo esc_attr( $tab['id'] ); ?>"
						role="tab"
						aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
						aria-controls="tab-<?php echo esc_attr( $tab['id'] ); ?>">
						<?php echo esc_html( $tab['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<?php // ── Specs tab ─────────────────────────────────────────────── ?>
			<div id="tab-specs" class="product-tab-panel pt-8" role="tabpanel"
			     <?php echo $tabs[0]['id'] !== 'specs' ? 'hidden' : ''; ?>>
				<?php get_template_part( 'template-parts/product-specs-table' ); ?>

				<?php if ( ! $specs ) :
					// Fallback: render individual meta fields as specs table.
					$meta_specs = [
						__( 'Coverage', 'alkana' )     => get_field( '_alkana_coverage',   $post_id ),
						__( 'Mix Ratio', 'alkana' )    => get_field( '_alkana_mix_ratio',  $post_id ),
						__( 'Thinner', 'alkana' )      => get_field( '_alkana_thinner',    $post_id ),
						__( 'Layers', 'alkana' )       => get_field( '_alkana_layer',      $post_id ),
						__( 'Dry Touch', 'alkana' )    => get_field( '_alkana_dry_touch',  $post_id ),
						__( 'Dry Hard', 'alkana' )     => get_field( '_alkana_dry_hard',   $post_id ),
						__( 'Recoat Time', 'alkana' )  => get_field( '_alkana_dry_recoat', $post_id ),
					];
					$meta_specs = array_filter( $meta_specs );
					if ( $meta_specs ) : ?>
					<table class="specs-table mt-6 w-full text-sm border-collapse">
						<tbody>
							<?php foreach ( $meta_specs as $label => $value ) : ?>
								<tr class="specs-table__row border-b border-gray-100">
									<th class="specs-table__label py-2 pr-4 text-left text-gray-500 font-normal w-2/5">
										<?php echo esc_html( $label ); ?>
									</th>
									<td class="specs-table__value py-2 font-medium text-[--color-secondary]">
										<?php echo esc_html( $value ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php // ── Variants tab ──────────────────────────────────────────── ?>
			<?php if ( $has_variants ) : ?>
			<div id="tab-variants" class="product-tab-panel pt-8" role="tabpanel"
			     <?php echo $tabs[0]['id'] !== 'variants' ? 'hidden' : ''; ?>>
				<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
					<?php foreach ( $variants as $variant ) :
						$color_name    = $variant['color_name']    ?? '';
						$color_hex     = $variant['color_hex']     ?? '#cccccc';
						$packaging     = $variant['packaging']     ?? '';
						$variant_image = $variant['variant_image'] ?? null;
					?>
						<div class="variant-swatch flex flex-col items-center gap-2 p-3 rounded-lg border border-[--color-border] text-center">
							<?php if ( $variant_image ) : ?>
								<?php echo wp_get_attachment_image( $variant_image['ID'] ?? 0, [ 64, 64 ], false, [
									'class'   => 'w-16 h-16 rounded-full object-cover mx-auto',
									'alt'     => esc_attr( $color_name ),
									'loading' => 'lazy',
								] ); ?>
							<?php else : ?>
								<span class="w-12 h-12 rounded-full border border-gray-200 shadow-sm block mx-auto"
								      style="background-color: <?php echo esc_attr( $color_hex ); ?>"></span>
							<?php endif; ?>

							<?php if ( $color_name ) : ?>
								<span class="text-xs font-medium text-[--color-secondary] leading-tight">
									<?php echo esc_html( $color_name ); ?>
								</span>
							<?php endif; ?>

							<?php if ( $packaging ) : ?>
								<span class="text-xs text-gray-400"><?php echo esc_html( $packaging ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php // ── Resources tab ─────────────────────────────────────────── ?>
			<?php if ( $has_resources ) : ?>
			<div id="tab-resources" class="product-tab-panel pt-8" role="tabpanel"
			     <?php echo $tabs[0]['id'] !== 'resources' ? 'hidden' : ''; ?>>
				<ul class="resources-list flex flex-col gap-3">
					<?php if ( $tds ) : ?>
						<li class="resource-item flex items-center gap-3 p-4 rounded-lg border border-[--color-border] bg-white hover:bg-gray-50">
							<svg class="w-8 h-8 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
							<div class="flex-1">
								<p class="font-medium text-sm"><?php esc_html_e( 'Technical Data Sheet (TDS)', 'alkana' ); ?></p>
								<p class="text-xs text-gray-400"><?php echo esc_html( $tds['filename'] ?? 'PDF' ); ?></p>
							</div>
							<a href="<?php echo esc_url( $tds['url'] ); ?>"
							   class="btn btn--primary btn--sm"
							   download target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Download', 'alkana' ); ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $msds ) : ?>
						<li class="resource-item flex items-center gap-3 p-4 rounded-lg border border-[--color-border] bg-white hover:bg-gray-50">
							<svg class="w-8 h-8 text-alkana-purple-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
							<div class="flex-1">
								<p class="font-medium text-sm"><?php esc_html_e( 'Safety Data Sheet (MSDS/SDS)', 'alkana' ); ?></p>
								<p class="text-xs text-gray-400"><?php echo esc_html( $msds['filename'] ?? 'PDF' ); ?></p>
							</div>
							<a href="<?php echo esc_url( $msds['url'] ); ?>"
							   class="btn btn--outline btn--sm"
							   download target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Download', 'alkana' ); ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( ! empty( $certs ) ) :
						foreach ( $certs as $cert ) :
							$cert_file  = $cert['cert_file']  ?? null;
							$cert_label = $cert['cert_label'] ?? __( 'Certificate', 'alkana' );
							if ( ! $cert_file ) continue;
					?>
						<li class="resource-item flex items-center gap-3 p-4 rounded-lg border border-[--color-border] bg-white hover:bg-gray-50">
							<svg class="w-8 h-8 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
							<div class="flex-1">
								<p class="font-medium text-sm"><?php echo esc_html( $cert_label ); ?></p>
							</div>
							<a href="<?php echo esc_url( $cert_file['url'] ); ?>"
							   class="btn btn--outline btn--sm"
							   download target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Download', 'alkana' ); ?>
							</a>
						</li>
					<?php endforeach; endif; ?>
				</ul>
			</div>
			<?php endif; ?>

			<?php // ── Video tab ─────────────────────────────────────────────── ?>
			<?php if ( $has_video ) :
				// Convert YouTube URLs to embed format
				$video_embed = $video;
				if ( strpos( $video, 'youtube.com' ) !== false || strpos( $video, 'youtu.be' ) !== false ) {
					$video_embed = str_replace( 'watch?v=', 'embed/', $video );
					$video_embed = str_replace( 'youtu.be/', 'youtube.com/embed/', $video_embed );
				}
			?>
			<div id="tab-video" class="product-tab-panel pt-8" role="tabpanel"
			     <?php echo $tabs[0]['id'] !== 'video' ? 'hidden' : ''; ?>>
				<div class="aspect-video rounded-lg overflow-hidden">
					<iframe
						src="<?php echo esc_url( $video_embed ); ?>"
						class="w-full h-full"
						frameborder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowfullscreen
						loading="lazy"
						title="<?php echo esc_attr( get_the_title() . ' ' . __( 'video', 'alkana' ) ); ?>">
					</iframe>
				</div>
			</div>
			<?php endif; ?>

			<?php // ── Details tab (full editor content) ────────────────────── ?>
			<?php if ( get_the_content() ) : ?>
			<div id="tab-details" class="product-tab-panel pt-8" role="tabpanel"
			     <?php echo $tabs[0]['id'] !== 'details' ? 'hidden' : ''; ?>>
				<div class="prose max-w-none">
					<?php the_content(); ?>
				</div>
			</div>
			<?php endif; ?>

		</div>
		<?php endif; ?>

	</div>

	<?php // ── Share buttons ───────────────────────────────────────────────── ?>
	<div class="container mx-auto px-4 mt-8">
		<?php get_template_part( 'template-parts/share-buttons' ); ?>
	</div>

<?php endwhile; endif; ?>

	<?php // ── Related products ─────────────────────────────────────────────── ?>
	<?php get_template_part( 'template-parts/related-products' ); ?>

</main>

<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>
<?php get_template_part( 'template-parts/footer' ); ?>

<script>
(function () {
	var panels = document.querySelectorAll('.product-tab-panel');
	var buttons = document.querySelectorAll('[data-tab-target]');
	if (!buttons.length) return;

	buttons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var target = btn.getAttribute('data-tab-target');

			// Update buttons
			buttons.forEach(function (b) {
				var active = b.getAttribute('data-tab-target') === target;
				b.classList.toggle('border-[--color-primary]', active);
				b.classList.toggle('text-[--color-primary]', active);
				b.classList.toggle('border-transparent', !active);
				b.classList.toggle('text-gray-500', !active);
				b.setAttribute('aria-selected', active ? 'true' : 'false');
			});

			// Show/hide panels
			panels.forEach(function (panel) {
				panel.hidden = panel.id !== 'tab-' + target;
			});
		});
	});
}());
</script>
