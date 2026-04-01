<?php
/**
 * 404 Error Page Template
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main-content" class="site-main">

	<?php // ── Hero Section ─────────────────────────────────────────────────── ?>
	<section class="error-hero bg-[--color-secondary] text-white py-20 text-center">
		<div class="container mx-auto px-4">
			<div class="max-w-2xl mx-auto">
				<svg class="w-32 h-32 mx-auto mb-6 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
				</svg>
				<h1 class="text-6xl font-extrabold mb-4">404</h1>
				<p class="text-xl font-heading font-semibold mb-3">
					<?php esc_html_e( 'Không tìm thấy trang', 'alkana' ); ?>
				</p>
				<p class="text-white/75 leading-relaxed">
					<?php esc_html_e( 'Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.', 'alkana' ); ?>
				</p>
			</div>
		</div>
	</section>

	<?php // ── CTA Buttons ──────────────────────────────────────────────────── ?>
	<section class="container mx-auto px-4 py-16 text-center">
		<div class="flex flex-wrap gap-4 justify-center">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary">
				<?php esc_html_e( 'Trang chủ', 'alkana' ); ?>
			</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'alkana_product' ) ); ?>" class="btn btn--outline">
				<?php esc_html_e( 'Sản phẩm', 'alkana' ); ?>
			</a>
			<a href="<?php echo esc_url( alkana_get_contact_url() ); ?>" class="btn btn--outline">
				<?php esc_html_e( 'Liên hệ', 'alkana' ); ?>
			</a>
		</div>

		<?php // ── Suggestions ──────────────────────────────────────────────── ?>
		<div class="mt-12 text-gray-600">
			<p class="mb-4"><?php esc_html_e( 'Bạn có thể thử:', 'alkana' ); ?></p>
			<ul class="text-sm space-y-2">
				<li><?php esc_html_e( 'Kiểm tra lại URL để đảm bảo chính xác', 'alkana' ); ?></li>
				<li><?php esc_html_e( 'Quay về trang chủ và tìm nội dung bạn cần', 'alkana' ); ?></li>
				<li><?php esc_html_e( 'Sử dụng chức năng tìm kiếm để khám phá sản phẩm', 'alkana' ); ?></li>
			</ul>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sticky-cta-mobile' ); ?>

</main>

<?php
get_footer();
