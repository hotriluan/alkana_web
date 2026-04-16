<?php
/**
 * Product Certifications meta box — replaces ACF PRO repeater.
 * Sub-fields: cert_name (text), cert_file_id (wp.media file/image).
 * JSON stored in _alkana_certs post meta.
 *
 * Helper: alkana_get_product_certs( int $post_id ) : array
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes',           'alkana_register_certs_metabox' );
add_action( 'save_post_alkana_product', 'alkana_save_certs_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts',    'alkana_certs_metabox_assets' );

function alkana_register_certs_metabox(): void {
	add_meta_box(
		'alkana_product_certs',
		__( 'Certifications', 'alkana' ),
		'alkana_render_certs_metabox',
		'alkana_product',
		'normal',
		'default'
	);
}

function alkana_certs_metabox_assets( string $hook ): void {
	global $post;
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( ! $post || $post->post_type !== 'alkana_product' ) return;

	wp_enqueue_media();

	wp_add_inline_style( 'wp-admin', '
		.alkana-clist{list-style:none;padding:0;margin:0}
		.alkana-crow{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f0f0f0}
		.alkana-crow:last-child{border-bottom:none}
		.alkana-crow .alkana-cname{flex:1}
		.alkana-crow input[type=text]{width:100%}
		.alkana-cfile-wrap{display:flex;align-items:center;gap:6px;min-width:200px}
		.alkana-cfile-name{font-size:12px;color:#646970;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
		.alkana-rm-btn{background:none;border:none;color:#b32d2e;cursor:pointer;font-size:18px;line-height:1;padding:2px 4px;border-radius:3px}
		.alkana-rm-btn:hover{background:#fceded}
	' );

	wp_add_inline_script( 'media-upload', 'jQuery(function($){
		var $list=$("#alkana-clist"),tpl=$("#alkana-ctpl").html(),idx=parseInt($("#alkana-cidx").val(),10)||0,frames={};
		$("#alkana-cadd").on("click",function(e){e.preventDefault();$list.append(tpl.replace(/__N__/g,idx++));$("#alkana-cidx").val(idx);});
		$list.on("click",".alkana-rm-btn",function(){$(this).closest(".alkana-crow").remove();});
		$list.on("click",".alkana-cfile-btn",function(){
			var $row=$(this).closest(".alkana-crow"),ri=$row.data("cidx");
			if(!frames[ri]){
				frames[ri]=wp.media({title:"' . esc_js( __( 'Select Certificate File', 'alkana' ) ) . '",button:{text:"' . esc_js( __( 'Use File', 'alkana' ) ) . '"},multiple:false,library:{type:["application/pdf","image"]}});
				frames[ri].on("select",function(){
					var att=frames[ri].state().get("selection").first();
					var fname=att.get("filename")||att.get("title")||"file";
					$row.find(".alkana-cfid").val(att.get("id"));
					$row.find(".alkana-cfile-name").text(fname).attr("title",fname);
					$row.find(".alkana-cfile-btn").text("' . esc_js( __( 'Change', 'alkana' ) ) . '");
				});
			}
			frames[ri].open();
		});
	});' );
}

/**
 * Get certifications for a product, normalized for template use.
 *
 * Returns rows with cert_label (string) and cert_file (array with 'url', 'ID') or null.
 *
 * @return array<array{cert_label:string,cert_file:array|null}>
 */
function alkana_get_product_certs( int $post_id ): array {
	$raw = get_post_meta( $post_id, '_alkana_certs', true );
	if ( ! is_string( $raw ) || $raw === '' ) return [];

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) return [];

	$out = [];
	foreach ( $data as $row ) {
		$file_id = isset( $row['cert_file_id'] ) ? (int) $row['cert_file_id'] : 0;
		$url     = $file_id ? wp_get_attachment_url( $file_id ) : '';

		$out[] = [
			'cert_label' => sanitize_text_field( $row['cert_name'] ?? '' ),
			'cert_file'  => ( $file_id && $url )
				? [ 'ID' => $file_id, 'url' => $url ]
				: null,
		];
	}

	return $out;
}

function alkana_render_certs_metabox( WP_Post $post ): void {
	wp_nonce_field( 'alkana_certs_save', 'alkana_certs_nonce' );

	$raw  = get_post_meta( $post->ID, '_alkana_certs', true );
	$rows = ( is_string( $raw ) && $raw !== '' ) ? ( json_decode( $raw, true ) ?: [] ) : [];
	?>
	<p class="description" style="margin:4px 0 10px">
		<?php esc_html_e( 'Attach certification documents (PDF or image).', 'alkana' ); ?>
	</p>
	<ul id="alkana-clist" class="alkana-clist">
	<?php foreach ( $rows as $i => $row ) :
		$file_id   = isset( $row['cert_file_id'] ) ? (int) $row['cert_file_id'] : 0;
		$file_name = $file_id ? ( get_the_title( $file_id ) ?: basename( get_attached_file( $file_id ) ?: '' ) ) : '';
	?>
		<li class="alkana-crow" data-cidx="<?php echo $i; ?>">
			<div class="alkana-cname">
				<input type="text"
				       name="alkana_certs[<?php echo $i; ?>][cert_name]"
				       value="<?php echo esc_attr( $row['cert_name'] ?? '' ); ?>"
				       placeholder="<?php esc_attr_e( 'e.g. ISO 9001:2015', 'alkana' ); ?>">
			</div>
			<div class="alkana-cfile-wrap">
				<input type="hidden" name="alkana_certs[<?php echo $i; ?>][cert_file_id]"
				       class="alkana-cfid" value="<?php echo esc_attr( $file_id ?: '' ); ?>">
				<span class="alkana-cfile-name" title="<?php echo esc_attr( $file_name ); ?>">
					<?php echo esc_html( $file_name ?: __( 'No file', 'alkana' ) ); ?>
				</span>
				<button type="button" class="button button-secondary alkana-cfile-btn">
					<?php echo $file_id ? esc_html__( 'Change', 'alkana' ) : esc_html__( 'Add File', 'alkana' ); ?>
				</button>
			</div>
			<button type="button" class="alkana-rm-btn" title="<?php esc_attr_e( 'Remove', 'alkana' ); ?>">&times;</button>
		</li>
	<?php endforeach; ?>
	</ul>
	<input type="hidden" id="alkana-cidx" value="<?php echo count( $rows ); ?>">
	<p style="margin-top:8px">
		<button type="button" id="alkana-cadd" class="button button-secondary">
			+ <?php esc_html_e( 'Add Certification', 'alkana' ); ?>
		</button>
	</p>
	<script type="text/template" id="alkana-ctpl">
		<li class="alkana-crow" data-cidx="__N__">
			<div class="alkana-cname">
				<input type="text" name="alkana_certs[__N__][cert_name]" value="" placeholder="<?php esc_attr_e( 'e.g. ISO 9001:2015', 'alkana' ); ?>">
			</div>
			<div class="alkana-cfile-wrap">
				<input type="hidden" name="alkana_certs[__N__][cert_file_id]" class="alkana-cfid" value="">
				<span class="alkana-cfile-name"><?php esc_html_e( 'No file', 'alkana' ); ?></span>
				<button type="button" class="button button-secondary alkana-cfile-btn"><?php esc_html_e( 'Add File', 'alkana' ); ?></button>
			</div>
			<button type="button" class="alkana-rm-btn">&times;</button>
		</li>
	</script>
	<?php
}

function alkana_save_certs_metabox( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( ! isset( $_POST['alkana_certs_nonce'] ) ||
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alkana_certs_nonce'] ) ), 'alkana_certs_save' ) ) return;

	$raw = isset( $_POST['alkana_certs'] ) ? (array) wp_unslash( $_POST['alkana_certs'] ) : [];

	$rows = [];
	foreach ( $raw as $row ) {
		$cert_name   = sanitize_text_field( $row['cert_name']   ?? '' );
		$cert_file_id = (int) ( $row['cert_file_id'] ?? 0 );

		if ( $cert_name === '' && $cert_file_id === 0 ) continue; // skip empty rows

		$rows[] = [
			'cert_name'    => $cert_name,
			'cert_file_id' => $cert_file_id,
		];
	}

	if ( empty( $rows ) ) {
		delete_post_meta( $post_id, '_alkana_certs' );
	} else {
		update_post_meta( $post_id, '_alkana_certs', wp_json_encode( $rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}
}
