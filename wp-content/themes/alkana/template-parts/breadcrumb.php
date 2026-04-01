<?php
/**
 * Breadcrumb Navigation Template
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

$items = [ [ 'label' => __( 'Trang chủ', 'alkana' ), 'url' => home_url( '/' ) ] ];

if ( is_singular( 'alkana_product' ) ) {
	$cats = get_the_terms( get_the_ID(), 'product_category' );
	if ( $cats && ! is_wp_error( $cats ) ) {
		$cat = $cats[0];
		$items[] = [ 'label' => $cat->name, 'url' => get_term_link( $cat ) ];
	}
	$items[] = [ 'label' => get_the_title(), 'url' => '' ];

} elseif ( is_singular( 'alkana_project' ) ) {
	$items[] = [ 'label' => __( 'Dự án', 'alkana' ), 'url' => get_post_type_archive_link( 'alkana_project' ) ];
	$items[] = [ 'label' => get_the_title(), 'url' => '' ];

} elseif ( is_singular( 'alkana_job' ) ) {
	$items[] = [ 'label' => __( 'Tuyển dụng', 'alkana' ), 'url' => get_post_type_archive_link( 'alkana_job' ) ];
	$items[] = [ 'label' => get_the_title(), 'url' => '' ];

} elseif ( is_single() ) {
	$cats = get_the_category();
	if ( $cats ) {
		$cat = $cats[0];
		$items[] = [ 'label' => $cat->name, 'url' => get_category_link( $cat ) ];
	}
	$items[] = [ 'label' => get_the_title(), 'url' => '' ];
}
?>

<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'alkana' ); ?>" class="breadcrumb py-4">
	<ol class="flex items-center gap-2 text-sm text-gray-500" itemscope itemtype="https://schema.org/BreadcrumbList">
		<?php foreach ( $items as $i => $item ) : ?>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="flex items-center gap-2">
				<?php if ( $item['url'] ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>" itemprop="item" class="hover:text-[--color-primary] transition-colors">
						<span itemprop="name"><?php echo esc_html( $item['label'] ); ?></span>
					</a>
					<meta itemprop="position" content="<?php echo esc_attr( (string) ( $i + 1 ) ); ?>" />
					<span class="text-gray-300" aria-hidden="true">›</span>
				<?php else : ?>
					<span itemprop="name" aria-current="page" class="text-gray-700"><?php echo esc_html( $item['label'] ); ?></span>
					<meta itemprop="position" content="<?php echo esc_attr( (string) ( $i + 1 ) ); ?>" />
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
