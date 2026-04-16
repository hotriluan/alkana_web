<?php
/**
 * Mega Menu Walker — renders a full-width mega panel for nav items
 * marked with the CSS class 'has-mega-menu' in WP Admin > Menus.
 *
 * Left column: product categories with icons.
 * Right column: 2 featured products with thumbnails.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

class Alkana_Mega_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Track whether current top-level item is a mega-menu trigger.
	 */
	private bool $is_mega = false;

	/**
	 * Start element output.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$this->is_mega = ( $depth === 0 && in_array( 'has-mega-menu', (array) $item->classes, true ) );

		$classes   = array_filter( (array) $item->classes );
		$classes[] = 'menu-item-' . $item->ID;

		if ( $this->is_mega ) {
			$classes[] = 'group';
		}

		$class_str = implode( ' ', array_map( 'esc_attr', $classes ) );
		$output   .= '<li class="' . $class_str . '">';

		$atts = [
			'href'  => ! empty( $item->url ) ? $item->url : '',
			'class' => $depth === 0
				? 'nav-link text-sm font-medium text-[--color-secondary] hover:text-alkana-purple-600 transition-colors'
				: 'block px-4 py-2 text-sm text-gray-700 hover:bg-alkana-purple-50 hover:text-alkana-purple-700 transition-colors',
		];

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( $value ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		$output .= '<a' . $attributes . '>';
		$output .= esc_html( $item->title );
		if ( $this->is_mega ) {
			$output .= ' <svg class="inline w-4 h-4 ml-1 transition-transform group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
		}
		$output .= '</a>';

		if ( $this->is_mega ) {
			$output .= $this->render_mega_panel();
		}
	}

	/**
	 * End element.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ): void {
		$output .= '</li>';
	}

	/**
	 * For mega-menu items, skip rendering child <ul> — the panel replaces it.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		if ( $this->is_mega && $depth === 0 ) {
			return; // Mega panel replaces submenu
		}
		$output .= '<ul class="sub-menu">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		if ( $this->is_mega && $depth === 0 ) {
			return;
		}
		$output .= '</ul>';
	}

	/**
	 * Render the mega panel HTML.
	 */
	private function render_mega_panel(): string {
		ob_start();
		?>
		<div class="mega-panel fixed left-0 right-0 bg-white shadow-2xl border-t-4 border-alkana-purple-600 rounded-b-xl
					opacity-0 invisible
					group-hover:opacity-100 group-hover:visible
					transition-[opacity,visibility] duration-300 ease-out z-[var(--z-mega-menu,9999)]"
			 style="top: var(--header-height, 80px);">

			<div class="max-w-7xl mx-auto px-8 py-8 grid grid-cols-12 gap-8">

				<?php // ── Left column: categories ────────────────────────────── ?>
				<div class="col-span-8">
					<h3 class="text-xs font-semibold text-alkana-purple-600 uppercase tracking-wider mb-4">
						<?php esc_html_e( 'Product Categories', 'alkana' ); ?>
					</h3>
					<?php $this->render_category_grid(); ?>
				</div>

				<?php // ── Right column: featured products ────────────────────── ?>
				<div class="col-span-4 border-l border-gray-100 pl-8">
					<h3 class="text-xs font-semibold text-alkana-purple-600 uppercase tracking-wider mb-4">
						<?php esc_html_e( 'Featured Products', 'alkana' ); ?>
					</h3>
					<?php $this->render_featured_products(); ?>
				</div>

			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render product category grid.
	 */
	private function render_category_grid(): void {
		$terms = get_terms( [
			'taxonomy'   => 'product_category',
			'hide_empty' => true,
			'number'     => 8,
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			echo '<p class="text-sm text-gray-400">' . esc_html__( 'No categories found.', 'alkana' ) . '</p>';
			return;
		}

		echo '<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">';
		foreach ( $terms as $term ) {
			$link  = get_term_link( $term );
			$icon  = get_field( 'category_icon', $term ); // ACF field for category icon URL
			$count = $term->count;
			?>
			<a href="<?php echo esc_url( $link ); ?>"
			   class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-alkana-purple-50 transition-colors text-center group/cat">
				<div class="w-12 h-12 rounded-full bg-alkana-purple-50 flex items-center justify-center group-hover/cat:bg-alkana-purple-100 transition-colors">
					<?php if ( $icon ) : ?>
						<img src="<?php echo esc_url( $icon ); ?>" alt="" class="w-6 h-6" loading="lazy">
					<?php else : ?>
						<svg class="w-6 h-6 text-alkana-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
						</svg>
					<?php endif; ?>
				</div>
				<span class="text-sm font-medium text-gray-700 group-hover/cat:text-alkana-purple-700"><?php echo esc_html( $term->name ); ?></span>
				<span class="text-xs text-gray-400"><?php echo esc_html( $count . ' ' . __( 'products', 'alkana' ) ); ?></span>
			</a>
			<?php
		}
		echo '</div>';
	}

	/**
	 * Render 2 featured products with thumbnails.
	 */
	private function render_featured_products(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'alkana_product_index';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$featured = $wpdb->get_results(
			"SELECT post_id, product_name FROM {$table} WHERE is_featured = 1 ORDER BY updated_at DESC LIMIT 2"
		);

		if ( empty( $featured ) ) {
			echo '<p class="text-sm text-gray-400">' . esc_html__( 'No featured products.', 'alkana' ) . '</p>';
			return;
		}

		echo '<div class="space-y-4">';
		foreach ( $featured as $product ) {
			$thumb = get_the_post_thumbnail_url( $product->post_id, 'alkana-product-card' );
			$link  = get_permalink( $product->post_id );
			?>
			<a href="<?php echo esc_url( $link ); ?>"
			   class="flex gap-4 p-3 rounded-xl hover:bg-alkana-purple-50 transition-colors group/feat">
				<div class="w-20 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
					<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $product->product_name ); ?>"
							 class="w-full h-full object-cover" loading="lazy">
					<?php endif; ?>
				</div>
				<div class="flex-1 min-w-0">
					<p class="text-sm font-semibold text-gray-800 truncate group-hover/feat:text-alkana-purple-700">
						<?php echo esc_html( $product->product_name ); ?>
					</p>
					<span class="text-xs text-alkana-purple-600 font-medium">
						<?php esc_html_e( 'View details →', 'alkana' ); ?>
					</span>
				</div>
			</a>
			<?php
		}
		echo '</div>';
	}
}
