<?php
/**
 * Template Name: Careers
 * Job openings and recruitment page.
 * Displays jobs from alkana_job custom post type.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

$hero_image = get_field( 'hero_image' );
$hero_img_id = is_array( $hero_image ) ? ( $hero_image['ID'] ?? 0 ) : (int) $hero_image;

// Query job posts
$jobs_query = new WP_Query( [
	'post_type'      => 'alkana_job',
	'posts_per_page' => -1,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'post_status'    => 'publish',
] );
?>

<main id="main-content" class="site-main">

	<section class="page-hero relative min-h-[40vh] flex items-end overflow-hidden bg-[--color-secondary] text-white py-16">
		<?php if ( $hero_img_id ) : ?>
			<?php echo wp_get_attachment_image( $hero_img_id, 'full', false, [
				'class'         => 'absolute inset-0 w-full h-full object-cover z-0',
				'alt'           => '',
				'fetchpriority' => 'high',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			] ); ?>
			<div class="absolute inset-0 bg-gradient-to-r from-black/50 via-black/30 to-black/20 z-10"></div>
		<?php endif; ?>
		<div class="relative z-20 container mx-auto px-4">
			<h1 class="text-3xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-2 text-white/90">
				<?php esc_html_e( 'Join the Alkana team. We are always looking for talented people.', 'alkana' ); ?>
			</p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php if ( $jobs_query->have_posts() ) : ?>
			<div class="careers-list flex flex-col gap-5 max-w-3xl mx-auto">
				<?php while ( $jobs_query->have_posts() ) : $jobs_query->the_post();
					$department = get_field( 'department' ) ?: '';
					$location   = get_field( 'location' ) ?: 'Ho Chi Minh City';
					$type       = get_field( 'employment_type' ) ?: '';
					$deadline   = get_field( 'deadline' ) ?: '';
				?>
					<div class="careers-card rounded-xl border border-[--color-border] bg-white shadow-sm hover:shadow-md transition-shadow">
						<div class="p-6">
							<div class="flex flex-wrap items-start gap-3 mb-3">
								<div class="flex-1">
									<h2 class="text-lg font-heading font-semibold text-[--color-secondary] hover:text-[--color-primary] transition-colors">
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h2>
									<div class="flex flex-wrap gap-2 mt-2 text-xs text-gray-500">
										<?php if ( $department ) : ?>
											<span class="flex items-center gap-1">
												<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
												<?php echo esc_html( $department ); ?>
											</span>
										<?php endif; ?>
										<?php if ( $location ) : ?>
											<span>·</span>
											<span class="flex items-center gap-1">
												<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
												<?php echo esc_html( $location ); ?>
											</span>
										<?php endif; ?>
										<?php if ( $type ) : ?>
											<span>·</span>
											<span class="flex items-center gap-1">
												<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
												<?php echo esc_html( $type ); ?>
											</span>
										<?php endif; ?>
									</div>
								</div>

								<?php if ( $deadline ) : ?>
									<span class="text-xs text-gray-400 flex items-center gap-1">
										<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
										<?php echo esc_html__( 'Deadline:', 'alkana' ) . ' ' . esc_html( $deadline ); ?>
									</span>
								<?php endif; ?>
							</div>

							<?php if ( has_excerpt() ) : ?>
								<p class="text-sm text-gray-600 mb-4">
									<?php echo esc_html( get_the_excerpt() ); ?>
								</p>
							<?php endif; ?>

						<a href="<?php the_permalink(); ?>" class="btn btn--primary btn--sm">
								<?php esc_html_e( 'View details & Apply', 'alkana' ); ?>
							</a>
						</div>
					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

		<?php else : ?>

			<div class="text-center py-24 text-gray-400 max-w-xl mx-auto">
				<svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
				<p class="text-lg font-medium text-gray-500"><?php esc_html_e( 'No open positions at this time.', 'alkana' ); ?></p>
				<p class="text-sm mt-2"><?php esc_html_e( 'Check back soon or send your CV for future opportunities.', 'alkana' ); ?></p>

				<?php
				$contact_email = get_option( 'admin_email' );
				if ( $contact_email ) : ?>
					<a href="mailto:<?php echo esc_attr( $contact_email ); ?>"
					   class="btn btn--outline mt-5 inline-block">
						<?php esc_html_e( 'Contact Us', 'alkana' ); ?>
					</a>
				<?php endif; ?>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
