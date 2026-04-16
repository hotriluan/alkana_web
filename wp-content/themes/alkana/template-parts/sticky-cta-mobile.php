<?php
/**
 * Sticky CTA bar.
 * Slides up when main CTA scrolls out of view on product single pages.
 * Also handles mobile archive quick actions.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$is_archive  = is_post_type_archive( 'alkana_product' );
$is_single   = is_singular( 'alkana_product' );
$contact_url = alkana_get_contact_url();
?>

<?php if ( $is_single ) : ?>
	<?php
	$product_title = get_the_title();
	$product_thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
	?>
	<div class="sticky-cta-product fixed bottom-0 left-0 right-0 z-40 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] border-t border-gray-100
				transform translate-y-full transition-transform duration-300"
		 id="sticky-cta-product"
		 role="toolbar"
		 aria-label="<?php esc_attr_e( 'Product quick action', 'alkana' ); ?>">
		<div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
			<?php if ( $product_thumb ) : ?>
				<img src="<?php echo esc_url( $product_thumb ); ?>"
					 alt=""
					 class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
					 loading="lazy">
			<?php endif; ?>
			<p class="flex-1 min-w-0 text-sm font-semibold text-gray-800 truncate">
				<?php echo esc_html( $product_title ); ?>
			</p>
			<a href="<?php echo esc_url( $contact_url ?: '#contact' ); ?>"
			   class="flex-shrink-0 px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-alkana-purple-400 to-alkana-purple-600 hover:from-alkana-purple-500 hover:to-alkana-purple-700 transition-all shadow-lg shadow-alkana-purple-600/25">
				<?php esc_html_e( 'Nhận Báo Giá', 'alkana' ); ?>
			</a>
		</div>
	</div>

<?php elseif ( $is_archive ) : ?>
	<div class="sticky-cta" id="sticky-cta" role="toolbar" aria-label="<?php esc_attr_e( 'Quick actions', 'alkana' ); ?>">
		<button
			class="sticky-cta__btn sticky-cta__btn--filter btn btn--outline"
			aria-controls="filter-bottom-sheet"
			data-sheet-open="filter-bottom-sheet">
			<span class="dashicons dashicons-filter" aria-hidden="true"></span>
			<?php esc_html_e( 'Filter', 'alkana' ); ?>
			<span class="sticky-cta__filter-count hidden" id="sticky-cta-count"></span>
		</button>
		<a href="<?php echo esc_url( $contact_url ?: '#contact' ); ?>"
		   class="sticky-cta__btn sticky-cta__btn--quote btn btn--primary flex-1">
			<?php esc_html_e( 'Get Quote', 'alkana' ); ?>
		</a>
	</div>
<?php endif; ?>
