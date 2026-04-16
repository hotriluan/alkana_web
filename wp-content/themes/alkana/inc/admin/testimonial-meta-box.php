<?php
/**
 * Admin Meta Box: Testimonial Details
 * Fields: quote (textarea), company/position (text), rating (1-5 stars).
 * The post title is used as the reviewer's name.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_alkana_testimonial', 'alkana_add_testimonial_meta_box' );
add_action( 'save_post_alkana_testimonial', 'alkana_save_testimonial_meta_box', 10, 2 );

function alkana_add_testimonial_meta_box(): void {
	add_meta_box(
		'alkana_testimonial_details',
		__( 'Testimonial Details', 'alkana' ),
		'alkana_render_testimonial_meta_box',
		'alkana_testimonial',
		'normal',
		'high'
	);
}

/**
 * Render testimonial meta box fields.
 *
 * @param WP_Post $post Current post object.
 */
function alkana_render_testimonial_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'alkana_save_testimonial', 'alkana_testimonial_nonce' );

	$quote   = get_post_meta( $post->ID, '_alkana_testimonial_quote', true );
	$company = get_post_meta( $post->ID, '_alkana_testimonial_company', true );
	$rating  = (int) get_post_meta( $post->ID, '_alkana_testimonial_rating', true );
	if ( $rating < 1 || $rating > 5 ) {
		$rating = 5;
	}
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="alkana_testimonial_quote"><?php esc_html_e( 'Quote', 'alkana' ); ?> <span style="color:red;">*</span></label>
			</th>
			<td>
				<textarea id="alkana_testimonial_quote"
				          name="alkana_testimonial_quote"
				          rows="4"
				          class="large-text"
				          placeholder="<?php esc_attr_e( 'Enter the customer quote here…', 'alkana' ); ?>"
				          maxlength="500"
				          required><?php echo esc_textarea( $quote ); ?></textarea>
				<p class="description"><?php esc_html_e( 'The customer review text (max 500 characters).', 'alkana' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="alkana_testimonial_company"><?php esc_html_e( 'Title / Company', 'alkana' ); ?></label>
			</th>
			<td>
				<input type="text"
				       id="alkana_testimonial_company"
				       name="alkana_testimonial_company"
				       value="<?php echo esc_attr( $company ); ?>"
				       class="regular-text"
				       placeholder="<?php esc_attr_e( 'e.g. CEO, Công ty TNHH ABC', 'alkana' ); ?>"
				       maxlength="150" />
				<p class="description"><?php esc_html_e( 'Job title and/or company name displayed below the reviewer name.', 'alkana' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="alkana_testimonial_rating"><?php esc_html_e( 'Rating', 'alkana' ); ?></label>
			</th>
			<td>
				<select id="alkana_testimonial_rating" name="alkana_testimonial_rating">
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>>
							<?php echo esc_html( str_repeat( '★', $i ) . str_repeat( '☆', 5 - $i ) . ' (' . $i . ')' ); ?>
						</option>
					<?php endfor; ?>
				</select>
			</td>
		</tr>
	</table>
	<p class="description" style="padding:0 10px 10px;">
		<?php esc_html_e( 'Tip: Use "Order" (Page Attributes box) to control the display order on the homepage.', 'alkana' ); ?>
	</p>
	<?php
}

/**
 * Save testimonial meta box data.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function alkana_save_testimonial_meta_box( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['alkana_testimonial_nonce'] ) ||
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alkana_testimonial_nonce'] ) ), 'alkana_save_testimonial' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Quote: strip all tags, limit length.
	$quote = isset( $_POST['alkana_testimonial_quote'] )
		? wp_strip_all_tags( wp_unslash( $_POST['alkana_testimonial_quote'] ) )
		: '';
	update_post_meta( $post_id, '_alkana_testimonial_quote', mb_substr( $quote, 0, 500 ) );

	// Company: plain text.
	$company = isset( $_POST['alkana_testimonial_company'] )
		? sanitize_text_field( wp_unslash( $_POST['alkana_testimonial_company'] ) )
		: '';
	update_post_meta( $post_id, '_alkana_testimonial_company', mb_substr( $company, 0, 150 ) );

	// Rating: integer 1-5.
	$rating = isset( $_POST['alkana_testimonial_rating'] )
		? (int) $_POST['alkana_testimonial_rating']
		: 5;
	$rating = max( 1, min( 5, $rating ) );
	update_post_meta( $post_id, '_alkana_testimonial_rating', $rating );
}
