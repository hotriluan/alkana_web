<?php
/**
 * Color Variants & Packaging meta box — replaces ACF PRO repeater.
 * Sub-fields: color_name, color_hex, gloss_level, packaging, variant_image (image ID).
 * JSON stored in _alkana_variants post meta.
 *
 * Helper: alkana_get_product_variants( int $post_id ) : array
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes',           'alkana_register_variants_metabox' );
add_action( 'save_post_alkana_product', 'alkana_save_variants_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts',    'alkana_variants_metabox_assets' );

function alkana_register_variants_metabox(): void {
	add_meta_box(
		'alkana_product_variants',
		__( 'Color Variants & Packaging', 'alkana' ),
		'alkana_render_variants_metabox',
		'alkana_product',
		'normal',
		'default'
	);
}

function alkana_variants_metabox_assets( string $hook ): void {
	global $post;
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( ! $post || $post->post_type !== 'alkana_product' ) return;

	wp_enqueue_media();

	wp_add_inline_style( 'wp-admin', '
		.alkana-vrow td{padding:5px 5px;border-bottom:1px solid #f0f0f0;vertical-align:middle;font-size:13px}
		.alkana-vrow input[type=text],.alkana-vrow select{width:100%;font-size:12px}
		.alkana-vrow input[type=color]{width:44px;height:30px;padding:1px;border-radius:3px;cursor:pointer;border:1px solid #dcdcde}
		.alkana-vimg-wrap{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
		.alkana-vimg-preview img{width:52px;height:52px;object-fit:cover;border-radius:3px;border:1px solid #dcdcde;display:block}
	' );

	wp_add_inline_script( 'media-upload', 'jQuery(function($){
		var $list=$("#alkana-vlist"),tpl=$("#alkana-vtpl").html(),idx=parseInt($("#alkana-vidx").val(),10)||0,frames={};
		$("#alkana-vrow-add").on("click",function(e){e.preventDefault();$list.append(tpl.replace(/__N__/g,idx++));$("#alkana-vidx").val(idx);});
		$list.on("click",".alkana-rm-btn",function(){$(this).closest(".alkana-vrow").remove();});
		$list.on("click",".alkana-vimg-btn",function(){
			var $row=$(this).closest(".alkana-vrow"),ri=$row.data("vidx");
			if(!frames[ri]){
				frames[ri]=wp.media({title:"' . esc_js( __( 'Select Variant Image', 'alkana' ) ) . '",button:{text:"' . esc_js( __( 'Use Image', 'alkana' ) ) . '"},multiple:false,library:{type:"image"}});
				frames[ri].on("select",function(){
					var att=frames[ri].state().get("selection").first();
					var url=(att.get("sizes")&&att.get("sizes").thumbnail)?att.get("sizes").thumbnail.url:att.get("url");
					$row.find(".alkana-vid").val(att.get("id"));
					$row.find(".alkana-vimg-preview").html("<img src=\""+url+"\">");
					$row.find(".alkana-vimg-btn").text("' . esc_js( __( 'Change', 'alkana' ) ) . '");
				});
			}
			frames[ri].open();
		});
	});' );
}

/**
 * Get color variants for a product, normalized for template use.
 *
 * variant_image is returned as ['ID' => int] when set, or null (matches ACF array return_format).
 *
 * @return array<array{color_name:string,color_hex:string,gloss_level:string,packaging:string,variant_image:array|null}>
 */
function alkana_get_product_variants( int $post_id ): array {
	$raw = get_post_meta( $post_id, '_alkana_variants', true );
	if ( ! is_string( $raw ) || $raw === '' ) return [];
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) return [];

	return array_map( function ( array $v ): array {
		$img_id            = (int) ( $v['variant_image_id'] ?? 0 );
		$v['variant_image'] = $img_id > 0 ? [ 'ID' => $img_id ] : null;
		return $v;
	}, $data );
}

function alkana_render_variants_metabox( WP_Post $post ): void {
	wp_nonce_field( 'alkana_variants_save', 'alkana_variants_nonce' );

	$raw_json    = get_post_meta( $post->ID, '_alkana_variants', true );
	$stored_rows = ( is_string( $raw_json ) && $raw_json !== '' ) ? ( json_decode( $raw_json, true ) ?: [] ) : [];

	$gloss_opts = [ '' => '—', 'matte' => 'Matte', 'satin' => 'Satin', 'semi-gloss' => 'Semi-Gloss', 'gloss' => 'Gloss', 'high-gloss' => 'High Gloss' ];
	?>
	<p class="description" style="margin:4px 0 10px">
		<?php esc_html_e( 'Add one row per color variant. Leave empty if product has no color options.', 'alkana' ); ?>
	</p>
	<table style="width:100%;border-collapse:collapse">
		<colgroup><col style="width:25%"><col style="width:9%"><col style="width:14%"><col style="width:20%"><col style="width:24%"><col style="width:8%"></colgroup>
		<thead><tr style="background:#f6f7f7;border-bottom:1px solid #dcdcde">
			<?php foreach ( [ 'Color Name', 'Hex', 'Gloss', 'Packaging', 'Image', '' ] as $th ) : ?>
			<th style="padding:6px 5px;font-size:11px;font-weight:600;text-transform:uppercase;color:#646970;text-align:left">
				<?php echo esc_html( $th ); ?>
			</th>
			<?php endforeach; ?>
		</tr></thead>
		<tbody id="alkana-vlist">
		<?php foreach ( $stored_rows as $i => $v ) :
			$img_id  = (int) ( $v['variant_image_id'] ?? 0 );
			$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
		?>
		<tr class="alkana-vrow" data-vidx="<?php echo $i; ?>">
			<td><input type="text" name="alkana_v[<?php echo $i; ?>][color_name]" value="<?php echo esc_attr( $v['color_name'] ?? '' ); ?>" placeholder="e.g. Pure White"></td>
			<td><input type="color" name="alkana_v[<?php echo $i; ?>][color_hex]" value="<?php echo esc_attr( $v['color_hex'] ?? '#ffffff' ); ?>"></td>
			<td><select name="alkana_v[<?php echo $i; ?>][gloss_level]">
				<?php foreach ( $gloss_opts as $val => $lbl ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $v['gloss_level'] ?? '', $val ); ?>><?php echo esc_html( $lbl ); ?></option>
				<?php endforeach; ?>
			</select></td>
			<td><input type="text" name="alkana_v[<?php echo $i; ?>][packaging]" value="<?php echo esc_attr( $v['packaging'] ?? '' ); ?>" placeholder="1L, 5L, 18L"></td>
			<td><div class="alkana-vimg-wrap">
				<input type="hidden" name="alkana_v[<?php echo $i; ?>][variant_image_id]" class="alkana-vid" value="<?php echo $img_id; ?>">
				<div class="alkana-vimg-preview"><?php if ( $img_url ) echo '<img src="' . esc_url( $img_url ) . '">'; ?></div>
				<button type="button" class="button button-small alkana-vimg-btn"><?php echo $img_url ? esc_html__( 'Change', 'alkana' ) : '+ ' . esc_html__( 'Image', 'alkana' ); ?></button>
			</div></td>
			<td style="text-align:center"><button type="button" class="alkana-rm-btn" title="Remove">&times;</button></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" id="alkana-vidx" value="<?php echo count( $stored_rows ); ?>">
	<p style="margin-top:8px">
		<button type="button" id="alkana-vrow-add" class="button button-secondary">
			+ <?php esc_html_e( 'Add Variant', 'alkana' ); ?>
		</button>
	</p>
	<script type="text/template" id="alkana-vtpl">
	<tr class="alkana-vrow" data-vidx="__N__">
		<td><input type="text" name="alkana_v[__N__][color_name]" value="" placeholder="e.g. Pure White"></td>
		<td><input type="color" name="alkana_v[__N__][color_hex]" value="#ffffff"></td>
		<td><select name="alkana_v[__N__][gloss_level]">
			<option value="">—</option>
			<option value="matte">Matte</option>
			<option value="satin">Satin</option>
			<option value="semi-gloss">Semi-Gloss</option>
			<option value="gloss">Gloss</option>
			<option value="high-gloss">High Gloss</option>
		</select></td>
		<td><input type="text" name="alkana_v[__N__][packaging]" value="" placeholder="1L, 5L, 18L"></td>
		<td><div class="alkana-vimg-wrap">
			<input type="hidden" name="alkana_v[__N__][variant_image_id]" class="alkana-vid" value="0">
			<div class="alkana-vimg-preview"></div>
			<button type="button" class="button button-small alkana-vimg-btn">+ Image</button>
		</div></td>
		<td style="text-align:center"><button type="button" class="alkana-rm-btn">&times;</button></td>
	</tr>
	</script>
	<?php
}

function alkana_save_variants_metabox( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( ! isset( $_POST['alkana_variants_nonce'] ) ||
	     ! wp_verify_nonce( sanitize_key( $_POST['alkana_variants_nonce'] ), 'alkana_variants_save' ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$raw  = isset( $_POST['alkana_v'] ) ? (array) wp_unslash( $_POST['alkana_v'] ) : [];
	$rows = [];
	$allowed_gloss = [ '', 'matte', 'satin', 'semi-gloss', 'gloss', 'high-gloss' ];

	foreach ( $raw as $r ) {
		$name = sanitize_text_field( $r['color_name'] ?? '' );
		if ( $name === '' ) continue;
		$rows[] = [
			'color_name'       => $name,
			'color_hex'        => sanitize_hex_color( $r['color_hex'] ?? '#ffffff' ) ?: '#ffffff',
			'gloss_level'      => in_array( $r['gloss_level'] ?? '', $allowed_gloss, true ) ? $r['gloss_level'] : '',
			'packaging'        => sanitize_text_field( $r['packaging'] ?? '' ),
			'variant_image_id' => absint( $r['variant_image_id'] ?? 0 ),
		];
	}

	if ( empty( $rows ) ) {
		delete_post_meta( $post_id, '_alkana_variants' );
	} else {
		update_post_meta( $post_id, '_alkana_variants', wp_json_encode( $rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}
}
