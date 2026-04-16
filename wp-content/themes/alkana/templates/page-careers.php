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

	<section class="page-hero relative min-h-[44vh] flex items-end overflow-hidden bg-alkana-navy text-white">
		<?php if ( $hero_img_id ) : ?>
			<?php echo wp_get_attachment_image( $hero_img_id, 'full', false, [
				'class'         => 'absolute inset-0 w-full h-full object-cover z-0',
				'alt'           => '',
				'fetchpriority' => 'high',
				'loading'       => 'eager',
				'decoding'      => 'async',
				'sizes'         => '100vw',
			] ); ?>
			<div class="absolute inset-0 bg-gradient-to-r from-alkana-purple-950/80 via-black/50 to-black/20 z-10" aria-hidden="true"></div>
		<?php else : ?>
			<div class="absolute inset-0 bg-gradient-to-br from-alkana-purple-950 to-alkana-purple-800"></div>
		<?php endif; ?>
		<div class="relative z-20 container mx-auto px-4 pb-16 pt-32">
			<p class="text-alkana-purple-300 text-xs font-semibold uppercase tracking-widest mb-3"><?php esc_html_e( 'Tuyển dụng', 'alkana' ); ?></p>
			<h1 class="text-4xl md:text-5xl font-heading font-bold"><?php the_title(); ?></h1>
			<p class="mt-3 text-lg text-white/80 max-w-xl">
				<?php esc_html_e( 'Gia nhập đội ngũ Alkana. Chúng tôi luôn tìm kiếm những người tài năng và đam mê.', 'alkana' ); ?>
			</p>
		</div>
	</section>

	<?php // ── Why Join Us (Benefit Cards) ────────────────────────────────── ?>
	<section class="py-20 bg-[#F8F4FF]" data-reveal>
		<div class="container mx-auto px-4">
			<p class="text-xs font-semibold uppercase tracking-widest text-alkana-purple-500 text-center mb-2"><?php esc_html_e( 'Đãi ngộ & Phúc lợi', 'alkana' ); ?></p>
			<h2 class="text-3xl font-heading font-bold text-[--color-secondary] text-center mb-12"><?php esc_html_e( 'Tại sao chọn Alkana?', 'alkana' ); ?></h2>
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-reveal-stagger>
				<?php
				$benefits = [
					[
						'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
						'title' => 'Phát triển nghề nghiệp',
						'desc'  => 'Lộ trình thăng tiến rõ ràng, đào tạo chuyên môn liên tục từ các chuyên gia đầu ngành.',
					],
					[
						'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
						'title' => 'Môi trường năng động',
						'desc'  => 'Đội ngũ trẻ, sáng tạo, cởi mở — nơi mỗi ý kiến đều được lắng nghe và trân trọng.',
					],
					[
						'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
						'title' => 'Thu nhập cạnh tranh',
						'desc'  => 'Lương thưởng hấp dẫn, thưởng hiệu suất, phúc lợi theo quy định nhà nước và hơn thế nữa.',
					],
					[
						'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
						'title' => 'Sức khoẻ & Phúc lợi',
						'desc'  => 'Bảo hiểm sức khỏe toàn diện, khám sức khỏe định kỳ, hoạt động team building thường xuyên.',
					],
				];
				foreach ( $benefits as $b ) :
				?>
					<div class="benefit-card bg-white rounded-2xl p-6 shadow-sm border border-alkana-purple-100 hover:-translate-y-1 transition-transform duration-300">
						<div class="benefit-card__icon mb-4 w-12 h-12 rounded-xl bg-alkana-purple-100 flex items-center justify-center">
							<svg class="w-6 h-6 text-alkana-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
								<?php echo $b['icon']; // phpcs:ignore — static SVG path ?>
							</svg>
						</div>
						<h3 class="benefit-card__title text-base font-semibold text-[--color-secondary] mb-2"><?php echo esc_html( $b['title'] ); ?></h3>
						<p class="benefit-card__desc text-sm text-gray-500 leading-relaxed"><?php echo esc_html( $b['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
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

	<?php // ── Recruitment Process ────────────────────────────────────────── ?>
	<section class="py-20 bg-white" data-reveal>
		<div class="container mx-auto px-4">
			<p class="text-xs font-semibold uppercase tracking-widest text-alkana-purple-500 text-center mb-2"><?php esc_html_e( 'Quy trình ứng tuyển', 'alkana' ); ?></p>
			<h2 class="text-3xl font-heading font-bold text-[--color-secondary] text-center mb-14"><?php esc_html_e( '4 bước để gia nhập đội ngũ Alkana', 'alkana' ); ?></h2>
			<ol class="process-steps grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 max-w-5xl mx-auto list-none" data-reveal-stagger>
				<?php
				$steps = [
					[ 'title' => 'Nộp hồ sơ', 'desc' => 'Gửi CV và thư ứng tuyển qua form online hoặc email tuyển dụng của Alkana.' ],
					[ 'title' => 'Sàng lọc hồ sơ', 'desc' => 'Đội HR xem xét và liên hệ các ứng viên phù hợp trong vòng 3–5 ngày làm việc.' ],
					[ 'title' => 'Phỏng vấn', 'desc' => 'Phỏng vấn trực tiếp hoặc online với quản lý bộ phận và nhân sự.' ],
					[ 'title' => 'Nhận việc', 'desc' => 'Nhận thông báo kết quả, ký hợp đồng và bắt đầu hành trình cùng Alkana!' ],
				];
				foreach ( $steps as $idx => $step ) :
				?>
					<li class="process-step text-center">
						<div class="process-step__num mx-auto mb-4 w-12 h-12 rounded-full bg-alkana-purple-600 text-white flex items-center justify-center text-lg font-bold shadow-md">
							<?php echo $idx + 1; ?>
						</div>
						<h3 class="process-step__title font-semibold text-[--color-secondary] mb-2"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="process-step__desc text-sm text-gray-500 leading-relaxed"><?php echo esc_html( $step['desc'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

</main>

<?php get_template_part( 'template-parts/footer' ); ?>
