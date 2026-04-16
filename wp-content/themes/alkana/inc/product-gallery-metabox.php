<?php
/**
 * Product Gallery meta box for alkana_product CPT.
 *
 * Uses the native WordPress wp.media gallery frame to pick multiple images.
 * Stores image IDs as a JSON array under _alkana_product_gallery post meta.
 * No ACF PRO required.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes',        'alkana_register_gallery_metabox' );
add_action( 'save_post_alkana_product', 'alkana_save_gallery_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts', 'alkana_gallery_metabox_assets' );

/**
 * Register the meta box.
 */
function alkana_register_gallery_metabox(): void {
	add_meta_box(
		'alkana_product_gallery',
		__( 'Product Gallery', 'alkana' ),
		'alkana_render_gallery_metabox',
		'alkana_product',
		'normal',
		'default'
	);
}

/**
 * Enqueue WP media + inline JS/CSS only on alkana_product edit screens.
 */
function alkana_gallery_metabox_assets( string $hook ): void {
	global $post;

	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	if ( ! $post || $post->post_type !== 'alkana_product' ) {
		return;
	}

	wp_enqueue_media();

	wp_add_inline_style( 'wp-admin', '
		#alkana-gallery-wrap { display: flex; flex-wrap: wrap; gap: 8px; min-height: 60px; padding: 8px; border: 1px solid #dcdcde; border-radius: 4px; background: #f6f7f7; }
		#alkana-gallery-wrap .alkana-gallery-thumb { position: relative; width: 80px; height: 80px; }
		#alkana-gallery-wrap .alkana-gallery-thumb img { width: 80px; height: 80px; object-fit: cover; border-radius: 3px; display: block; border: 1px solid #dcdcde; }
		#alkana-gallery-wrap .alkana-gallery-thumb .remove-thumb { position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; background: #d63638; color: #fff; border-radius: 50%; border: none; cursor: pointer; font-size: 12px; line-height: 18px; text-align: center; padding: 0; display: flex; align-items: center; justify-content: center; }
		#alkana-gallery-wrap .alkana-gallery-thumb .remove-thumb:hover { background: #b32d2e; }
		.alkana-gallery-empty-msg { color: #8c8f94; font-style: italic; font-size: 13px; line-height: 60px; padding: 0 8px; }
		#alkana-add-gallery-images { margin-top: 8px; }
	' );

	wp_add_inline_script( 'media-upload', '
		jQuery(function($){
			var frame;
			var wrap   = $("#alkana-gallery-wrap");
			var hidden = $("#alkana_product_gallery_ids");

			function getIds() {
				var v = hidden.val();
				if (!v) return [];
				try { return JSON.parse(v); } catch(e) { return []; }
			}

			function setIds(ids) {
				hidden.val(ids.length ? JSON.stringify(ids) : "");
			}

			function renderThumbs(ids) {
				wrap.empty();
				if (!ids || !ids.length) {
					wrap.append("<span class=\'alkana-gallery-empty-msg\'>' . esc_js( __( 'No images selected. Click "Add Images" to begin.', 'alkana' ) ) . '</span>");
					return;
				}
				ids.forEach(function(id) {
					var thumb = $("<div class=\'alkana-gallery-thumb\'></div>");
					// Use WP attachment data if already cached
					if (wp.media.attachment) {
						var att = wp.media.attachment(id);
						att.fetch().done(function(){
							var url = att.get("sizes") && att.get("sizes").thumbnail
								? att.get("sizes").thumbnail.url
								: att.get("url");
							thumb.find("img").attr("src", url);
						});
					}
					thumb.append("<img src=\'\' alt=\'\' data-id=\'" + id + "\'>");
					thumb.append("<button type=\'button\' class=\'remove-thumb\' data-id=\'" + id + "\' title=\'' . esc_js( __( 'Remove', 'alkana' ) ) . '\'>&times;</button>");
					wrap.append(thumb);
				});
				// Fetch all sizes at once
				wp.media.query({ post__in: ids, posts_per_page: ids.length }).more().done(function(){
					ids.forEach(function(id){
						var att = wp.media.attachment(id);
						var url = att.get("sizes") && att.get("sizes").thumbnail
							? att.get("sizes").thumbnail.url
							: att.get("url");
						if (url) wrap.find("img[data-id=\'" + id + "\']").attr("src", url);
					});
				});
			}

			// Init on load
			renderThumbs(getIds());

			// Remove a single thumb
			wrap.on("click", ".remove-thumb", function(){
				var removeId = parseInt($(this).data("id"), 10);
				var ids = getIds().filter(function(i){ return i !== removeId; });
				setIds(ids);
				renderThumbs(ids);
			});

			// Open media frame
			$("#alkana-add-gallery-images").on("click", function(e){
				e.preventDefault();

				if (frame) {
					frame.open();
					return;
				}

				frame = wp.media({
					title:    "' . esc_js( __( 'Select Product Gallery Images', 'alkana' ) ) . '",
					button:   { text: "' . esc_js( __( 'Add to Gallery', 'alkana' ) ) . '" },
					multiple: "add",
					library:  { type: "image" }
				});

				frame.on("select", function(){
					var selection = frame.state().get("selection");
					var ids = getIds();
					selection.each(function(attachment){
						var id = attachment.get("id");
						if (ids.indexOf(id) === -1) ids.push(id);
					});
					setIds(ids);
					renderThumbs(ids);
				});

				frame.open();
			});
		});
	' );
}

/**
 * Render the meta box HTML.
 */
function alkana_render_gallery_metabox( WP_Post $post ): void {
	wp_nonce_field( 'alkana_gallery_save', 'alkana_gallery_nonce' );

	$raw = get_post_meta( $post->ID, '_alkana_product_gallery', true );
	// Accept both JSON array (our format) and legacy ACF serialized array
	if ( is_array( $raw ) ) {
		$ids = array_map( 'intval', $raw );
	} elseif ( is_string( $raw ) && strpos( $raw, '[' ) === 0 ) {
		$ids = array_map( 'intval', json_decode( $raw, true ) ?: [] );
	} else {
		$ids = [];
	}

	$value = $ids ? wp_json_encode( $ids ) : '';
	?>
	<p class="description" style="margin-top:4px;margin-bottom:10px;">
		<?php esc_html_e( 'Additional product images shown in the gallery slider on the detail page.', 'alkana' ); ?>
	</p>
	<div id="alkana-gallery-wrap"></div>
	<input type="hidden" id="alkana_product_gallery_ids" name="alkana_product_gallery_ids"
	       value="<?php echo esc_attr( $value ); ?>">
	<p>
		<button type="button" id="alkana-add-gallery-images" class="button button-secondary">
			<?php esc_html_e( 'Add Images', 'alkana' ); ?>
		</button>
	</p>
	<?php
}

/**
 * Save the gallery image IDs on post save.
 */
function alkana_save_gallery_metabox( int $post_id, WP_Post $post ): void {
	// Bail on autosave, AJAX, wrong post type
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;

	if (
		! isset( $_POST['alkana_gallery_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['alkana_gallery_nonce'] ), 'alkana_gallery_save' )
	) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['alkana_product_gallery_ids'] )
		? sanitize_text_field( wp_unslash( $_POST['alkana_product_gallery_ids'] ) )
		: '';

	if ( empty( $raw ) ) {
		delete_post_meta( $post_id, '_alkana_product_gallery' );
		return;
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		delete_post_meta( $post_id, '_alkana_product_gallery' );
		return;
	}

	$ids = array_values( array_filter( array_map( 'intval', $decoded ) ) );
	update_post_meta( $post_id, '_alkana_product_gallery', wp_json_encode( $ids ) );
}
