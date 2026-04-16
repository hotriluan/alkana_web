<?php
/**
 * Additional Specifications meta box — replaces ACF PRO repeater.
 * Sub-fields: spec_label, spec_value, spec_unit (optional).
 * JSON stored in _alkana_product_specs post meta.
 *
 * Helper: alkana_get_product_specs( int $post_id ) : array
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes',           'alkana_register_specs_metabox' );
add_action( 'save_post_alkana_product', 'alkana_save_specs_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts',    'alkana_specs_metabox_assets' );

function alkana_register_specs_metabox(): void {
	add_meta_box(
		'alkana_product_specs',
		__( 'Additional Specifications', 'alkana' ),
		'alkana_render_specs_metabox',
		'alkana_product',
		'normal',
		'default'
	);
}

function alkana_specs_metabox_assets( string $hook ): void {
	global $post;
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;
	if ( ! $post || $post->post_type !== 'alkana_product' ) return;

	wp_add_inline_style( 'wp-admin', '
		.alkana-rpt{width:100%;border-collapse:collapse;font-size:13px}
		.alkana-rpt th{padding:6px 8px;background:#f6f7f7;border-bottom:1px solid #dcdcde;font-weight:600;font-size:11px;text-transform:uppercase;color:#646970;text-align:left}
		.alkana-rpt td{padding:4px 6px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
		.alkana-rpt input[type=text]{width:100%}
		.alkana-rpt .col-rm{width:30px;text-align:center}
		.alkana-rm-btn{background:none;border:none;color:#b32d2e;cursor:pointer;font-size:18px;line-height:1;padding:2px 4px;border-radius:3px}
		.alkana-rm-btn:hover{background:#fceded}
	' );

	wp_add_inline_script( 'jquery', 'jQuery(function($){
		var tbody=$("#alkana-specs-tbody"),tpl=$("#alkana-spec-tpl").html(),idx=parseInt($("#alkana-specs-idx").val(),10)||0;
		$("#alkana-add-spec").on("click",function(e){e.preventDefault();tbody.append(tpl.replace(/__N__/g,idx++));$("#alkana-specs-idx").val(idx);});
		tbody.on("click",".alkana-rm-btn",function(){$(this).closest("tr").remove();});
	});' );
}

/**
 * Get additional spec rows for a product.
 *
 * @return array<array{spec_label:string,spec_value:string,spec_unit:string}>
 */
function alkana_get_product_specs( int $post_id ): array {
	$raw = get_post_meta( $post_id, '_alkana_product_specs', true );
	if ( is_string( $raw ) && $raw !== '' ) {
		return json_decode( $raw, true ) ?: [];
	}
	return [];
}

function alkana_render_specs_metabox( WP_Post $post ): void {
	wp_nonce_field( 'alkana_specs_save', 'alkana_specs_nonce' );
	$rows = alkana_get_product_specs( $post->ID );
	?>
	<p class="description" style="margin:4px 0 10px">
		<?php esc_html_e( 'Extra spec rows shown in the specs table on product detail page.', 'alkana' ); ?>
	</p>
	<table class="alkana-rpt">
		<thead><tr>
			<th style="width:40%"><?php esc_html_e( 'Property', 'alkana' ); ?></th>
			<th style="width:40%"><?php esc_html_e( 'Value', 'alkana' ); ?></th>
			<th style="width:12%"><?php esc_html_e( 'Unit', 'alkana' ); ?></th>
			<th class="col-rm"></th>
		</tr></thead>
		<tbody id="alkana-specs-tbody">
		<?php foreach ( $rows as $i => $row ) : ?>
		<tr>
			<td><input type="text" name="alkana_specs[<?php echo $i; ?>][spec_label]" value="<?php echo esc_attr( $row['spec_label'] ?? '' ); ?>" placeholder="e.g. VOC Content"></td>
			<td><input type="text" name="alkana_specs[<?php echo $i; ?>][spec_value]" value="<?php echo esc_attr( $row['spec_value'] ?? '' ); ?>" placeholder="e.g. &lt; 50 g/L"></td>
			<td><input type="text" name="alkana_specs[<?php echo $i; ?>][spec_unit]"  value="<?php echo esc_attr( $row['spec_unit']  ?? '' ); ?>" placeholder="g/L"></td>
			<td class="col-rm"><button type="button" class="alkana-rm-btn" title="Remove">&times;</button></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" id="alkana-specs-idx" value="<?php echo count( $rows ); ?>">
	<p style="margin-top:8px">
		<button type="button" id="alkana-add-spec" class="button button-secondary">
			+ <?php esc_html_e( 'Add Row', 'alkana' ); ?>
		</button>
	</p>
	<script type="text/template" id="alkana-spec-tpl">
		<tr>
			<td><input type="text" name="alkana_specs[__N__][spec_label]" value="" placeholder="e.g. VOC Content"></td>
			<td><input type="text" name="alkana_specs[__N__][spec_value]" value="" placeholder="e.g. &lt; 50 g/L"></td>
			<td><input type="text" name="alkana_specs[__N__][spec_unit]"  value="" placeholder="g/L"></td>
			<td class="col-rm"><button type="button" class="alkana-rm-btn">&times;</button></td>
		</tr>
	</script>
	<?php
}

function alkana_save_specs_metabox( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( ! isset( $_POST['alkana_specs_nonce'] ) ||
	     ! wp_verify_nonce( sanitize_key( $_POST['alkana_specs_nonce'] ), 'alkana_specs_save' ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$raw  = isset( $_POST['alkana_specs'] ) ? (array) wp_unslash( $_POST['alkana_specs'] ) : [];
	$rows = [];
	foreach ( $raw as $r ) {
		$label = sanitize_text_field( $r['spec_label'] ?? '' );
		$value = sanitize_text_field( $r['spec_value'] ?? '' );
		$unit  = sanitize_text_field( $r['spec_unit']  ?? '' );
		if ( $label !== '' || $value !== '' ) {
			$rows[] = [ 'spec_label' => $label, 'spec_value' => $value, 'spec_unit' => $unit ];
		}
	}

	if ( empty( $rows ) ) {
		delete_post_meta( $post_id, '_alkana_product_specs' );
	} else {
		update_post_meta( $post_id, '_alkana_product_specs', wp_json_encode( $rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}
}
