<?php
/**
 * Sticky mobile CTA bar.
 * Visible only on mobile (< 1024px) — handled by CSS via sticky-cta.css.
 * Contains "Filter" toggle (archive) and "Get Quote" action.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$is_archive = is_post_type_archive( 'alkana_product' );
$contact_url = get_permalink( get_page_by_path( 'contact' ) );
?>

<div class="sticky-cta" id="sticky-cta" role="toolbar" aria-label="<?php esc_attr_e( 'Quick actions', 'alkana' ); ?>">

	<?php if ( $is_archive ) : ?>
		<button
			class="sticky-cta__btn sticky-cta__btn--filter btn btn--outline"
			aria-controls="filter-bottom-sheet"
			data-sheet-open="filter-bottom-sheet">
			<span class="dashicons dashicons-filter" aria-hidden="true"></span>
			<?php esc_html_e( 'Filter', 'alkana' ); ?>
			<span class="sticky-cta__filter-count hidden" id="sticky-cta-count"></span>
		</button>
	<?php endif; ?>

	<a href="<?php echo esc_url( $contact_url ?: '#contact' ); ?>"
	   class="sticky-cta__btn sticky-cta__btn--quote btn btn--primary flex-1">
		<?php esc_html_e( 'Get Quote', 'alkana' ); ?>
	</a>

</div>
