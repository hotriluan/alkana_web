<?php
/**
 * Single project template for alkana_project CPT.
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
$post_id  = get_the_ID();
$location = get_field( 'project_location', $post_id );
$year     = get_field( 'project_year', $post_id );
$area     = get_field( 'project_area', $post_id );
$client   = get_field( 'project_client', $post_id );
$thumb_id = get_post_thumbnail_id( $post_id );
?>

	<?php // ── Hero image ──────────────────────────────────────────────────── ?>
	<section class="project-hero relative w-full min-h-[50vh] flex items-end overflow-hidden bg-gray-900">
		<?php if ( $thumb_id ) : ?>
			<?php echo wp_get_attachment_image( $thumb_id, 'full', false, [
				'class'         => 'absolute inset-0 w-full h-full object-cover z-0',
				'alt'           => get_the_title(),
				'fetchpriority' => 'high',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			] ); ?>
			<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent z-10"></div>
		<?php endif; ?>

		<div class="relative z-20 container mx-auto px-4 pb-10">
			<h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-3">
				<?php the_title(); ?>
			</h1>
			<?php if ( $location || $year ) : ?>
				<p class="text-white/70 text-lg">
					<?php if ( $location ) echo esc_html( $location ); ?>
					<?php if ( $location && $year ) echo ' · '; ?>
					<?php if ( $year ) echo esc_html( (string) $year ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php // ── Metadata grid ───────────────────────────────────────────── ?>
		<?php if ( $location || $year || $area || $client ) : ?>
		<div class="project-meta grid grid-cols-2 md:grid-cols-4 gap-6 mb-12 p-6 bg-gray-50 rounded-xl">
			<?php if ( $location ) : ?>
			<div>
				<p class="text-xs text-gray-400 uppercase tracking-wider mb-1"><?php esc_html_e( 'Location', 'alkana' ); ?></p>
				<p class="font-semibold text-[--color-secondary]"><?php echo esc_html( $location ); ?></p>
			</div>
			<?php endif; ?>

			<?php if ( $year ) : ?>
			<div>
				<p class="text-xs text-gray-400 uppercase tracking-wider mb-1"><?php esc_html_e( 'Year', 'alkana' ); ?></p>
				<p class="font-semibold text-[--color-secondary]"><?php echo esc_html( (string) $year ); ?></p>
			</div>
			<?php endif; ?>

			<?php if ( $area ) : ?>
			<div>
				<p class="text-xs text-gray-400 uppercase tracking-wider mb-1"><?php esc_html_e( 'Area', 'alkana' ); ?></p>
				<p class="font-semibold text-[--color-secondary]"><?php echo esc_html( $area ); ?> m²</p>
			</div>
			<?php endif; ?>

			<?php if ( $client ) : ?>
			<div>
				<p class="text-xs text-gray-400 uppercase tracking-wider mb-1"><?php esc_html_e( 'Client', 'alkana' ); ?></p>
				<p class="font-semibold text-[--color-secondary]"><?php echo esc_html( $client ); ?></p>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php // ── Content ─────────────────────────────────────────────────── ?>
		<?php if ( get_the_content() ) : ?>
		<div class="prose prose-lg max-w-none mb-12">
			<?php the_content(); ?>
		</div>
		<?php endif; ?>

		<?php // ── Products Used ─────────────────────────────────────────── ?>
		<?php
		$products_used = get_field( 'project_products_used', $post_id );
		if ( $products_used && is_array( $products_used ) ) :
		?>
		<div class="project-products-used mb-12">
			<h2 class="text-2xl font-bold text-[#1A3A5C] mb-6"><?php esc_html_e( 'Products Used', 'alkana' ); ?></h2>
			<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
				<?php foreach ( $products_used as $product_id ) :
					$product_thumb_id = get_post_thumbnail_id( $product_id );
					$product_title    = get_the_title( $product_id );
					$product_url      = get_permalink( $product_id );
					$cats             = get_the_terms( $product_id, 'product_category' );
					$cat_name         = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
				?>
				<a href="<?php echo esc_url( (string) $product_url ); ?>"
				   class="group flex flex-col bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
					<div class="aspect-[4/3] bg-gray-100 overflow-hidden">
						<?php if ( $product_thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $product_thumb_id, 'alkana-product-card', false, [
								'class'   => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300',
								'alt'     => esc_attr( (string) $product_title ),
								'loading' => 'lazy',
								'decoding' => 'async',
							] ); ?>
						<?php else : ?>
							<div class="w-full h-full flex items-center justify-center bg-gray-200">
								<span class="text-gray-400 text-sm"><?php esc_html_e( 'No image', 'alkana' ); ?></span>
							</div>
						<?php endif; ?>
					</div>
					<div class="p-3">
						<?php if ( $cat_name ) : ?>
							<p class="text-xs text-[--color-primary] font-medium uppercase tracking-wide mb-1"><?php echo esc_html( $cat_name ); ?></p>
						<?php endif; ?>
							<p class="text-sm font-semibold text-[#1A3A5C] group-hover:text-alkana-purple-600 transition-colors line-clamp-2"><?php echo esc_html( (string) $product_title ); ?></p>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php // ── Project Gallery ───────────────────────────────────────── ?>
		<?php
		$gallery_raw = get_post_meta( $post_id, '_alkana_project_gallery', true );
		if ( is_array( $gallery_raw ) ) {
			$gallery_ids = array_map( 'intval', $gallery_raw );
		} elseif ( is_string( $gallery_raw ) && strpos( $gallery_raw, '[' ) === 0 ) {
			$gallery_ids = array_map( 'intval', json_decode( $gallery_raw, true ) ?: [] );
		} else {
			$gallery_ids = [];
		}
		if ( ! empty( $gallery_ids ) ) :
		?>
		<div class="project-gallery mb-12">
			<h2 class="text-2xl font-bold text-[#1A3A5C] mb-6"><?php esc_html_e( 'Project Gallery', 'alkana' ); ?></h2>
			<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
				<?php foreach ( $gallery_ids as $img_id ) :
					$full_url  = wp_get_attachment_image_url( $img_id, 'large' );
					$img_alt   = (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true );
					if ( ! $full_url ) continue;
				?>
				<a href="<?php echo esc_url( $full_url ); ?>"
				   class="project-gallery__item block aspect-square overflow-hidden rounded-lg bg-gray-100 cursor-zoom-in"
				   data-lightbox-src="<?php echo esc_url( $full_url ); ?>"
				   aria-label="<?php echo $img_alt ? esc_attr( $img_alt ) : esc_attr__( 'Project photo', 'alkana' ); ?>">
					<?php echo wp_get_attachment_image( $img_id, 'medium', false, [
						'class'   => 'w-full h-full object-cover hover:scale-110 transition-transform duration-300',
						'loading' => 'lazy',
						'decoding' => 'async',
					] ); ?>
				</a>
				<?php endforeach; ?>
			</div>
		</div>

		<div id="alkana-lightbox"
		     class="fixed inset-0 z-[9999] bg-black/90 hidden items-center justify-center p-4"
		     aria-modal="true" role="dialog"
		     aria-label="<?php esc_attr_e( 'Image lightbox', 'alkana' ); ?>">
			<button id="alkana-lightbox-close"
			        class="absolute top-4 right-4 text-white text-3xl leading-none w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/20 transition"
			        aria-label="<?php esc_attr_e( 'Close', 'alkana' ); ?>">&times;</button>
			<img id="alkana-lightbox-img" src="" alt=""
			     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" />
		</div>
		<script>
		(function(){
			var lb  = document.getElementById('alkana-lightbox');
			var img = document.getElementById('alkana-lightbox-img');
			var cls = document.getElementById('alkana-lightbox-close');

			function openLb(src, alt) {
				img.src = src;
				img.alt = alt || '';
				lb.classList.remove('hidden');
				lb.classList.add('flex');
				document.body.style.overflow = 'hidden';
			}
			function closeLb() {
				lb.classList.add('hidden');
				lb.classList.remove('flex');
				img.src = '';
				document.body.style.overflow = '';
			}

			document.querySelectorAll('.project-gallery__item').forEach(function(el) {
				el.addEventListener('click', function(e) {
					e.preventDefault();
					openLb(this.dataset.lightboxSrc, this.getAttribute('aria-label'));
				});
			});
			cls.addEventListener('click', closeLb);
			lb.addEventListener('click', function(e) { if (e.target === lb) closeLb(); });
			document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLb(); });
		})();
		</script>
		<?php endif; ?>

		<?php // ── CTA ─────────────────────────────────────────────────────── ?>
		<div class="flex gap-4 mt-8">
			<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_project' ) ); ?>"
			   class="btn btn--outline">
				<?php esc_html_e( '← All Projects', 'alkana' ); ?>
			</a>
			<a href="<?php echo esc_url( alkana_get_contact_url() ); ?>"
			   class="btn btn--primary">
				<?php esc_html_e( 'Get a Quote', 'alkana' ); ?>
			</a>
		</div>

		<?php // ── Share buttons ───────────────────────────────────────────── ?>
		<div class="mt-8">
			<?php get_template_part( 'template-parts/share-buttons' ); ?>
		</div>

	</div>

<?php endwhile; endif; ?>
</main>

<?php
get_template_part( 'template-parts/sticky-cta-mobile' );
get_template_part( 'template-parts/footer' );
?>
