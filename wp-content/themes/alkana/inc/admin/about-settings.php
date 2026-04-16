<?php
/**
 * Admin Settings Page: About Us
 * Manages timeline, factory section, and leadership team on the About page.
 * Stores settings as alkana_about_settings option (array).
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

// Enqueue WP media library on our settings page.
add_action( 'admin_enqueue_scripts', function( string $hook ): void {
	if ( strpos( $hook, 'alkana-about' ) === false ) return;
	wp_enqueue_media();
	wp_add_inline_script( 'jquery', <<<'JS'
jQuery(function($){
	$(document).on('click','#alkana-factory-img-btn',function(e){
		e.preventDefault();
		var frame=wp.media({title:'Chọn ảnh nhà máy',button:{text:'Dùng ảnh này'},multiple:false});
		frame.on('select',function(){
			var att=frame.state().get('selection').first().toJSON();
			var url=(att.sizes&&att.sizes.large)?att.sizes.large.url:att.url;
			$('#alkana-factory-img-id').val(att.id);
			$('#alkana-factory-img-preview').attr('src',url).show();
			$('#alkana-factory-img-btn').text('Đổi ảnh');
			$('#alkana-factory-img-remove').show();
		});
		frame.open();
	});
	$(document).on('click','#alkana-factory-img-remove',function(e){
		e.preventDefault();
		$('#alkana-factory-img-id').val('');
		$('#alkana-factory-img-preview').hide().attr('src','');
		$('#alkana-factory-img-btn').text('Chọn ảnh');
		$(this).hide();
	});
});
JS
	);
} );

/**
 * Return About settings with defaults applied.
 *
 * @return array
 */
function alkana_get_about_settings(): array {
	$defaults = [
		// Timeline
		'timeline_title'      => 'Hành trình phát triển',
		'timeline_milestones' => [
			[ 'year' => '2008', 'desc' => 'Thành lập công ty với sứ mệnh mang đến giải pháp sơn chất lượng cao' ],
			[ 'year' => '2012', 'desc' => 'Đạt chứng nhận ISO 9001:2015 về quản lý chất lượng' ],
			[ 'year' => '2016', 'desc' => 'Mở rộng nhà máy sản xuất, nâng công suất lên 5,000 tấn/năm' ],
			[ 'year' => '2019', 'desc' => 'Hoàn thành hơn 300 dự án công nghiệp lớn trên toàn quốc' ],
			[ 'year' => '2023', 'desc' => 'Phủ sóng 63/63 tỉnh thành với mạng lưới đại lý và đối tác' ],
			[ 'year' => '2024', 'desc' => 'Ra mắt phòng R&D hiện đại, nghiên cứu công nghệ sơn thế hệ mới' ],
		],
		// Factory
		'factory_image_id'    => 0,
		'factory_title'       => 'Nhà máy sản xuất',
		'factory_intro'       => 'Nhà máy sản xuất của Alkana được trang bị hệ thống công nghệ hiện đại, đạt tiêu chuẩn quốc tế. Chúng tôi cam kết mang đến những sản phẩm sơn chất lượng cao, đáp ứng mọi nhu cầu của khách hàng trong và ngoài nước.',
		'factory_specs'       => [
			[ 'label' => 'Diện tích',   'value' => '10,000m² khu vực sản xuất và kho bãi' ],
			[ 'label' => 'Công suất',   'value' => '5,000 tấn sản phẩm mỗi năm' ],
			[ 'label' => 'Công nghệ',   'value' => 'Hệ thống tự động hóa và kiểm soát chất lượng' ],
			[ 'label' => 'Chất lượng',  'value' => 'Chứng nhận ISO 9001:2015' ],
		],
		// Team
		'team_title'          => 'Đội ngũ lãnh đạo',
		'team_members'        => [
			[ 'name' => 'Nguyễn Văn Minh', 'position' => 'Giám đốc điều hành',  'bio' => '' ],
			[ 'name' => 'Trần Thị Hương',  'position' => 'Giám đốc kỹ thuật',    'bio' => '' ],
			[ 'name' => 'Lê Hoàng Nam',    'position' => 'Giám đốc kinh doanh',  'bio' => '' ],
		],
	];

	$saved = get_option( 'alkana_about_settings', [] );
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	$settings = wp_parse_args( $saved, $defaults );

	// Ensure sub-arrays use saved values when present, else defaults.
	foreach ( [ 'timeline_milestones', 'factory_specs', 'team_members' ] as $key ) {
		if ( isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ) {
			$settings[ $key ] = $saved[ $key ];
		}
	}

	return $settings;
}

/**
 * Handle form submission, then render the settings page.
 */
function alkana_render_about_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'alkana' ) );
	}

	$updated = false;
	$error   = '';

	if ( isset( $_POST['alkana_about_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alkana_about_nonce'] ) ), 'alkana_save_about' ) ) {
			$error = __( 'Security check failed. Please try again.', 'alkana' );
		} else {
			$settings = [
				// Timeline
				'timeline_title'      => sanitize_text_field( wp_unslash( $_POST['timeline_title'] ?? '' ) ),
				'timeline_milestones' => [],
				// Factory
				'factory_image_id'    => absint( $_POST['factory_image_id'] ?? 0 ),
				'factory_title'       => sanitize_text_field( wp_unslash( $_POST['factory_title'] ?? '' ) ),
				'factory_intro'       => sanitize_textarea_field( wp_unslash( $_POST['factory_intro'] ?? '' ) ),
				'factory_specs'       => [],
				// Team
				'team_title'          => sanitize_text_field( wp_unslash( $_POST['team_title'] ?? '' ) ),
				'team_members'        => [],
			];

			// Timeline milestones (dynamic count).
			$milestone_years = $_POST['milestone_year'] ?? [];
			$milestone_descs = $_POST['milestone_desc'] ?? [];
			foreach ( $milestone_years as $i => $year ) {
				$year = sanitize_text_field( wp_unslash( $year ) );
				$desc = sanitize_text_field( wp_unslash( $milestone_descs[ $i ] ?? '' ) );
				if ( '' !== $year || '' !== $desc ) {
					$settings['timeline_milestones'][] = [ 'year' => $year, 'desc' => $desc ];
				}
			}

			// Factory specs (dynamic count).
			$spec_labels = $_POST['spec_label'] ?? [];
			$spec_values = $_POST['spec_value'] ?? [];
			foreach ( $spec_labels as $i => $label ) {
				$label = sanitize_text_field( wp_unslash( $label ) );
				$value = sanitize_text_field( wp_unslash( $spec_values[ $i ] ?? '' ) );
				if ( '' !== $label || '' !== $value ) {
					$settings['factory_specs'][] = [ 'label' => $label, 'value' => $value ];
				}
			}

			// Team members (dynamic count).
			$member_names      = $_POST['member_name'] ?? [];
			$member_positions  = $_POST['member_position'] ?? [];
			$member_bios       = $_POST['member_bio'] ?? [];
			foreach ( $member_names as $i => $name ) {
				$name     = sanitize_text_field( wp_unslash( $name ) );
				$position = sanitize_text_field( wp_unslash( $member_positions[ $i ] ?? '' ) );
				$bio      = sanitize_textarea_field( wp_unslash( $member_bios[ $i ] ?? '' ) );
				if ( '' !== $name || '' !== $position ) {
					$settings['team_members'][] = compact( 'name', 'position', 'bio' );
				}
			}

			update_option( 'alkana_about_settings', $settings );
			$updated = true;
		}
	}

	$s = alkana_get_about_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'About Page Settings — Giới thiệu', 'alkana' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Manage content for the About page: timeline, factory section, and leadership team.', 'alkana' ); ?>
		</p>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'alkana' ); ?></p></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'alkana_save_about', 'alkana_about_nonce' ); ?>

			<?php /* ── TIMELINE ──────────────────────────────────────────── */ ?>
			<h2 style="border-bottom:1px solid #ccc;padding-bottom:8px;">📅 <?php esc_html_e( 'Hành trình phát triển', 'alkana' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="timeline_title"><?php esc_html_e( 'Tiêu đề section', 'alkana' ); ?></label></th>
					<td><input type="text" id="timeline_title" name="timeline_title" value="<?php echo esc_attr( $s['timeline_title'] ); ?>" class="regular-text" maxlength="120" /></td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Các mốc thời gian', 'alkana' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Add or remove milestones. Displayed in order.', 'alkana' ); ?></p>

			<div id="timeline-items">
				<?php foreach ( $s['timeline_milestones'] as $i => $m ) : ?>
					<div class="alkana-repeater-row" style="display:flex;gap:12px;align-items:flex-start;margin-bottom:8px;">
						<input type="text"
						       name="milestone_year[]"
						       value="<?php echo esc_attr( $m['year'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Năm (VD: 2008)', 'alkana' ); ?>"
						       style="width:110px;" />
						<input type="text"
						       name="milestone_desc[]"
						       value="<?php echo esc_attr( $m['desc'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Mô tả sự kiện…', 'alkana' ); ?>"
						       class="large-text" />
						<button type="button" class="button alkana-remove-row" title="<?php esc_attr_e( 'Remove', 'alkana' ); ?>">✕</button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" id="add-timeline-row" class="button" style="margin-top:4px;">+ <?php esc_html_e( 'Thêm mốc', 'alkana' ); ?></button>

			<?php /* ── FACTORY ───────────────────────────────────────────── */ ?>
			<h2 style="border-bottom:1px solid #ccc;padding-bottom:8px;margin-top:32px;">🏭 <?php esc_html_e( 'Nhà máy sản xuất', 'alkana' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Hình ảnh nhà máy', 'alkana' ); ?></th>
					<td>
						<?php
						$fimg_id  = absint( $s['factory_image_id'] ?? 0 );
						$fimg_url = $fimg_id ? wp_get_attachment_image_url( $fimg_id, 'large' ) : '';
						?>
						<input type="hidden" id="alkana-factory-img-id" name="factory_image_id" value="<?php echo $fimg_id ?: ''; ?>" />
						<img id="alkana-factory-img-preview" src="<?php echo esc_url( $fimg_url ?: '' ); ?>"
						     style="display:<?php echo $fimg_url ? 'block' : 'none'; ?>;max-width:300px;height:auto;border-radius:6px;border:1px solid #ddd;margin-bottom:8px;" />
						<button type="button" id="alkana-factory-img-btn" class="button">
							<?php echo $fimg_url ? esc_html__( 'Đổi ảnh', 'alkana' ) : esc_html__( 'Chọn ảnh', 'alkana' ); ?>
						</button>
						<button type="button" id="alkana-factory-img-remove" class="button" style="margin-left:6px;<?php echo $fimg_url ? '' : 'display:none;'; ?>">
							<?php esc_html_e( 'Xóa ảnh', 'alkana' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Ảnh hiển thị bên trái phần Nhà máy sản xuất.', 'alkana' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="factory_title"><?php esc_html_e( 'Tiêu đề section', 'alkana' ); ?></label></th>
					<td><input type="text" id="factory_title" name="factory_title" value="<?php echo esc_attr( $s['factory_title'] ); ?>" class="regular-text" maxlength="120" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="factory_intro"><?php esc_html_e( 'Mô tả nhà máy', 'alkana' ); ?></label></th>
					<td><textarea id="factory_intro" name="factory_intro" rows="4" class="large-text"><?php echo esc_textarea( $s['factory_intro'] ); ?></textarea></td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Thông số kỹ thuật / Điểm nổi bật', 'alkana' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Each item shows as a bullet point (bold label + value).', 'alkana' ); ?></p>

			<div id="factory-specs">
				<?php foreach ( $s['factory_specs'] as $spec ) : ?>
					<div class="alkana-repeater-row" style="display:flex;gap:12px;align-items:flex-start;margin-bottom:8px;">
						<input type="text"
						       name="spec_label[]"
						       value="<?php echo esc_attr( $spec['label'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Nhãn (VD: Diện tích)', 'alkana' ); ?>"
						       style="width:180px;" />
						<input type="text"
						       name="spec_value[]"
						       value="<?php echo esc_attr( $spec['value'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Giá trị…', 'alkana' ); ?>"
						       class="large-text" />
						<button type="button" class="button alkana-remove-row" title="<?php esc_attr_e( 'Remove', 'alkana' ); ?>">✕</button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" id="add-spec-row" class="button" style="margin-top:4px;">+ <?php esc_html_e( 'Thêm thông số', 'alkana' ); ?></button>

			<?php /* ── TEAM ──────────────────────────────────────────────── */ ?>
			<h2 style="border-bottom:1px solid #ccc;padding-bottom:8px;margin-top:32px;">👥 <?php esc_html_e( 'Đội ngũ lãnh đạo', 'alkana' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="team_title"><?php esc_html_e( 'Tiêu đề section', 'alkana' ); ?></label></th>
					<td><input type="text" id="team_title" name="team_title" value="<?php echo esc_attr( $s['team_title'] ); ?>" class="regular-text" maxlength="120" /></td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Thành viên', 'alkana' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Name and title for each leader card.', 'alkana' ); ?></p>

			<div id="team-members">
				<?php foreach ( $s['team_members'] as $member ) : ?>
					<div class="alkana-repeater-row alkana-team-row" style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;padding:12px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:4px;">
						<div style="flex:1;">
							<input type="text"
							       name="member_name[]"
							       value="<?php echo esc_attr( $member['name'] ); ?>"
							       placeholder="<?php esc_attr_e( 'Họ và tên', 'alkana' ); ?>"
							       class="regular-text"
							       style="margin-bottom:6px;display:block;" />
							<input type="text"
							       name="member_position[]"
							       value="<?php echo esc_attr( $member['position'] ); ?>"
							       placeholder="<?php esc_attr_e( 'Chức danh', 'alkana' ); ?>"
							       class="regular-text"
							       style="margin-bottom:6px;display:block;" />
							<textarea name="member_bio[]"
							          rows="2"
							          class="large-text"
							          placeholder="<?php esc_attr_e( 'Giới thiệu ngắn (tuỳ chọn)…', 'alkana' ); ?>"
							          style="display:block;"><?php echo esc_textarea( $member['bio'] ?? '' ); ?></textarea>
						</div>
						<button type="button" class="button alkana-remove-row" style="margin-top:4px;" title="<?php esc_attr_e( 'Remove', 'alkana' ); ?>">✕</button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" id="add-team-row" class="button" style="margin-top:4px;">+ <?php esc_html_e( 'Thêm thành viên', 'alkana' ); ?></button>

			<?php submit_button( __( 'Save Settings', 'alkana' ), 'primary', 'submit', true, [ 'style' => 'margin-top:24px;' ] ); ?>
		</form>
	</div>

	<script>
	(function(){
		// Generic remove row
		document.addEventListener('click', function(e){
			if (e.target.classList.contains('alkana-remove-row')) {
				e.target.closest('.alkana-repeater-row').remove();
			}
		});

		// Add timeline row
		document.getElementById('add-timeline-row').addEventListener('click', function(){
			var row = document.createElement('div');
			row.className = 'alkana-repeater-row';
			row.style.cssText = 'display:flex;gap:12px;align-items:flex-start;margin-bottom:8px;';
			row.innerHTML = '<input type="text" name="milestone_year[]" placeholder="<?php echo esc_js( __( 'Năm (VD: 2008)', 'alkana' ) ); ?>" style="width:110px;" />'
				+ '<input type="text" name="milestone_desc[]" placeholder="<?php echo esc_js( __( 'Mô tả sự kiện…', 'alkana' ) ); ?>" class="large-text" />'
				+ '<button type="button" class="button alkana-remove-row" title="Remove">✕</button>';
			document.getElementById('timeline-items').appendChild(row);
		});

		// Add factory spec row
		document.getElementById('add-spec-row').addEventListener('click', function(){
			var row = document.createElement('div');
			row.className = 'alkana-repeater-row';
			row.style.cssText = 'display:flex;gap:12px;align-items:flex-start;margin-bottom:8px;';
			row.innerHTML = '<input type="text" name="spec_label[]" placeholder="<?php echo esc_js( __( 'Nhãn (VD: Diện tích)', 'alkana' ) ); ?>" style="width:180px;" />'
				+ '<input type="text" name="spec_value[]" placeholder="<?php echo esc_js( __( 'Giá trị…', 'alkana' ) ); ?>" class="large-text" />'
				+ '<button type="button" class="button alkana-remove-row" title="Remove">✕</button>';
			document.getElementById('factory-specs').appendChild(row);
		});

		// Add team member row
		document.getElementById('add-team-row').addEventListener('click', function(){
			var row = document.createElement('div');
			row.className = 'alkana-repeater-row alkana-team-row';
			row.style.cssText = 'display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;padding:12px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:4px;';
			row.innerHTML = '<div style="flex:1;">'
				+ '<input type="text" name="member_name[]" placeholder="<?php echo esc_js( __( 'Họ và tên', 'alkana' ) ); ?>" class="regular-text" style="margin-bottom:6px;display:block;" />'
				+ '<input type="text" name="member_position[]" placeholder="<?php echo esc_js( __( 'Chức danh', 'alkana' ) ); ?>" class="regular-text" style="margin-bottom:6px;display:block;" />'
				+ '<textarea name="member_bio[]" rows="2" class="large-text" placeholder="<?php echo esc_js( __( 'Giới thiệu ngắn (tuỳ chọn)…', 'alkana' ) ); ?>" style="display:block;"></textarea>'
				+ '</div>'
				+ '<button type="button" class="button alkana-remove-row" style="margin-top:4px;" title="Remove">✕</button>';
			document.getElementById('team-members').appendChild(row);
		});
	})();
	</script>
	<?php
}
