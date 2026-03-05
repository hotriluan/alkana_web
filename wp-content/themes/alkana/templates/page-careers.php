<?php
/**
 * Template Name: Careers
 * Job openings and recruitment page.
 * Job items are stored as an ACF Repeater field `careers_openings` on this page.
 * Field group: group_alkana_page_careers (add via ACF if needed).
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

$openings = get_field( 'careers_openings' );
?>

<main id="main-content" class="site-main">

	<section class="page-hero bg-[--color-secondary] text-white py-14">
		<div class="container mx-auto px-4">
			<h1 class="text-3xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-2 text-white/70">
				<?php esc_html_e( 'Join the Alkana team. We're always looking for talented people.', 'alkana' ); ?>
			</p>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">

		<?php if ( $openings ) : ?>
			<div class="careers-list flex flex-col gap-5 max-w-3xl mx-auto">
				<?php foreach ( $openings as $job ) :
					$title       = $job['job_title']       ?? '';
					$department  = $job['department']      ?? '';
					$location    = $job['location']        ?? 'Ho Chi Minh City';
					$type        = $job['employment_type'] ?? '';
					$deadline    = $job['deadline']        ?? '';
					$description = $job['description']     ?? '';
					$email       = get_field( 'careers_contact_email' ) ?: get_option( 'admin_email' );
				?>
					<details class="careers-card group rounded-xl border border-[--color-border] bg-white shadow-sm open:shadow-md transition-shadow">
						<summary class="careers-card__header flex flex-wrap items-center gap-3 p-5 cursor-pointer list-none select-none">
							<div class="flex-1">
								<h2 class="text-base font-heading font-semibold text-[--color-secondary] group-open:text-[--color-primary]">
									<?php echo esc_html( $title ); ?>
								</h2>
								<div class="flex flex-wrap gap-2 mt-1 text-xs text-gray-500">
									<?php if ( $department ) : ?>
										<span><?php echo esc_html( $department ); ?></span>
									<?php endif; ?>
									<?php if ( $location ) : ?>
										<span>·</span>
										<span><?php echo esc_html( $location ); ?></span>
									<?php endif; ?>
									<?php if ( $type ) : ?>
										<span>·</span>
										<span><?php echo esc_html( $type ); ?></span>
									<?php endif; ?>
								</div>
							</div>

							<?php if ( $deadline ) : ?>
								<span class="text-xs text-gray-400"><?php echo esc_html__( 'Deadline:', 'alkana' ) . ' ' . esc_html( $deadline ); ?></span>
							<?php endif; ?>

							<span class="career-chevron text-[--color-primary] transition-transform duration-200 group-open:rotate-180">
								<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
							</span>
						</summary>

						<div class="careers-card__body border-t border-[--color-border] p-5">
							<?php if ( $description ) : ?>
								<div class="prose prose-sm max-w-none text-gray-600 mb-5">
									<?php echo wp_kses_post( $description ); ?>
								</div>
							<?php endif; ?>

							<a href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo esc_attr( sprintf( __( 'Application: %s', 'alkana' ), $title ) ); ?>"
							   class="btn btn-primary btn-sm">
								<?php esc_html_e( 'Apply now', 'alkana' ); ?>
							</a>
						</div>
					</details>
				<?php endforeach; ?>
			</div>

		<?php else : ?>

			<div class="text-center py-24 text-gray-400 max-w-xl mx-auto">
				<svg class="w-12 h-12 mx-auto mb-4 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
				<p class="text-lg font-medium text-gray-500"><?php esc_html_e( 'No open positions at this time.', 'alkana' ); ?></p>
				<p class="text-sm mt-2"><?php esc_html_e( 'Send your CV to us and we\'ll keep it on file.', 'alkana' ); ?></p>

				<?php
				$contact_email = get_field( 'careers_contact_email' ) ?: get_option( 'admin_email' );
				if ( $contact_email ) : ?>
					<a href="mailto:<?php echo esc_attr( $contact_email ); ?>"
					   class="btn btn-outline mt-5 inline-block">
						<?php esc_html_e( 'Send your CV', 'alkana' ); ?>
					</a>
				<?php endif; ?>
			</div>

		<?php endif; ?>

		<?php
		// Flexible editor content below the job list (optional)
		if ( have_rows( 'flexible_content' ) ) :
			while ( have_rows( 'flexible_content' ) ) : the_row();
				if ( get_row_layout() === 'wysiwyg_block' ) :
					echo '<div class="prose max-w-none mt-12">';
					echo wp_kses_post( get_sub_field( 'content' ) );
					echo '</div>';
				endif;
			endwhile;
		elseif ( $post->post_content ) :
		?>
			<div class="prose max-w-3xl mx-auto mt-12">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

	</div>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
