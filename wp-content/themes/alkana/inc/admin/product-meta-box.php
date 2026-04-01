<?php
/**
 * Admin Meta Box: Product Settings
 * Adds "Featured Product" checkbox to alkana_product edit screen.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_alkana_product', 'alkana_add_product_meta_box' );
add_action( 'save_post_alkana_product', 'alkana_save_product_meta_box', 10, 2 );

/**
 * Register the product settings meta box.
 */
function alkana_add_product_meta_box(): void {
	add_meta_box(
		'alkana_product_settings',
		__( 'Product Settings', 'alkana' ),
		'alkana_render_product_meta_box',
		'alkana_product',
		'side',
		'high'
	);
}

/**
 * Render the meta box content.
 *
 * @param WP_Post $post Current post object.
 */
function alkana_render_product_meta_box( $post ): void {
	wp_nonce_field( 'alkana_save_product_settings', 'alkana_product_nonce' );

	$is_featured = get_post_meta( $post->ID, '_alkana_featured', true );
	?>
	<label style="display:flex;align-items:center;gap:8px;padding:8px 0;cursor:pointer;">
		<input type="checkbox"
		       name="alkana_featured"
		       value="1"
		       <?php checked( $is_featured, '1' ); ?>
		       style="width:18px;height:18px;">
		<span style="font-size:13px;font-weight:500;">
			<?php esc_html_e( 'Featured Product', 'alkana' ); ?>
		</span>
	</label>
	<p class="description" style="margin-top:4px;">
		<?php esc_html_e( 'Featured products appear on the homepage and can be filtered on the products page.', 'alkana' ); ?>
	</p>
	<?php
}

/**
 * Save meta box data.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function alkana_save_product_meta_box( int $post_id, $post ): void {
	if ( ! isset( $_POST['alkana_product_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['alkana_product_nonce'], 'alkana_save_product_settings' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$featured = isset( $_POST['alkana_featured'] ) ? '1' : '0';
	update_post_meta( $post_id, '_alkana_featured', $featured );

	// Sync to product index table
	global $wpdb;
	$wpdb->update(
		$wpdb->prefix . 'alkana_product_index',
		[ 'is_featured' => (int) $featured ],
		[ 'post_id' => $post_id ],
		[ '%d' ],
		[ '%d' ]
	);
}
