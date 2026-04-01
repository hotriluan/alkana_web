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
