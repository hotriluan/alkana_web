<?php
/**
 * Admin Settings Page: USP Stats
 * Manages the "Tại sao chọn Alkana?" section on the homepage.
 * Stores settings as a single option: alkana_usp_settings (array).
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return USP settings with defaults applied.
 *
 * @return array{title:string, subtitle:string, items:list<array{number:string, label:string}>}
 */
function alkana_get_usp_settings(): array {
	$defaults = [
		'title'    => 'Tại sao chọn Alkana?',
		'subtitle' => 'Giải pháp sơn phủ chuyên nghiệp cho mọi công trình',
		'items'    => [
			[ 'number' => '15+',   'label' => 'Năm Kinh Nghiệm' ],
			[ 'number' => '500+',  'label' => 'Dự Án Hoàn Thành' ],
			[ 'number' => 'ISO',   'label' => 'Chứng Nhận 9001:2015' ],
			[ 'number' => '63/63', 'label' => 'Tỉnh Thành Phủ Sóng' ],
		],
	];

	$saved = get_option( 'alkana_usp_settings', [] );
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	// Merge top-level keys.
	$settings = wp_parse_args( $saved, $defaults );

	// Ensure exactly 4 items with correct keys.
	foreach ( $defaults['items'] as $i => $default_item ) {
		$saved_item          = isset( $saved['items'][ $i ] ) && is_array( $saved['items'][ $i ] ) ? $saved['items'][ $i ] : [];
		$settings['items'][ $i ] = wp_parse_args( $saved_item, $default_item );
	}

	return $settings;
}

/**
 * Handle form submission, then render the settings page.
 */
function alkana_render_usp_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions.', 'alkana' ) );
	}

	$updated = false;
	$error   = '';

	if ( isset( $_POST['alkana_usp_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alkana_usp_nonce'] ) ), 'alkana_save_usp' ) ) {
			$error = __( 'Security check failed. Please try again.', 'alkana' );
		} else {
			$settings = [
				'title'    => sanitize_text_field( wp_unslash( $_POST['usp_title'] ?? '' ) ),
				'subtitle' => sanitize_text_field( wp_unslash( $_POST['usp_subtitle'] ?? '' ) ),
				'items'    => [],
			];

			for ( $i = 0; $i < 4; $i++ ) {
				$settings['items'][] = [
					'number' => sanitize_text_field( wp_unslash( $_POST[ 'usp_number_' . $i ] ?? '' ) ),
					'label'  => sanitize_text_field( wp_unslash( $_POST[ 'usp_label_' . $i ] ?? '' ) ),
				];
			}

			update_option( 'alkana_usp_settings', $settings );
			$updated = true;
		}
	}

	$s = alkana_get_usp_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'USP Stats — Tại sao chọn Alkana?', 'alkana' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Manage the four statistic boxes shown on the homepage. Icons are fixed per position.', 'alkana' ); ?>
		</p>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'alkana' ); ?></p></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'alkana_save_usp', 'alkana_usp_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row"><label for="usp_title"><?php esc_html_e( 'Section Title', 'alkana' ); ?></label></th>
					<td><input type="text" id="usp_title" name="usp_title" value="<?php echo esc_attr( $s['title'] ); ?>" class="regular-text" maxlength="100" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="usp_subtitle"><?php esc_html_e( 'Section Subtitle', 'alkana' ); ?></label></th>
					<td><input type="text" id="usp_subtitle" name="usp_subtitle" value="<?php echo esc_attr( $s['subtitle'] ); ?>" class="large-text" maxlength="200" /></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Statistic Items', 'alkana' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Each item has a bold number/value and a description label.', 'alkana' ); ?></p>

			<?php
			$icon_labels = [
				__( 'Item 1 (Shield icon)', 'alkana' ),
				__( 'Item 2 (Building icon)', 'alkana' ),
				__( 'Item 3 (Document icon)', 'alkana' ),
				__( 'Item 4 (Globe icon)', 'alkana' ),
			];
			?>
			<table class="form-table">
				<?php foreach ( $s['items'] as $i => $item ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $icon_labels[ $i ] ); ?></th>
						<td>
							<input type="text"
							       name="usp_number_<?php echo esc_attr( $i ); ?>"
							       value="<?php echo esc_attr( $item['number'] ); ?>"
							       class="small-text"
							       placeholder="e.g. 15+"
							       maxlength="20"
							       style="width:100px;" />
							<input type="text"
							       name="usp_label_<?php echo esc_attr( $i ); ?>"
							       value="<?php echo esc_attr( $item['label'] ); ?>"
							       class="regular-text"
							       placeholder="<?php esc_attr_e( 'Label', 'alkana' ); ?>"
							       maxlength="80" />
							<p class="description"><?php esc_html_e( 'Number/value and its description.', 'alkana' ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<?php submit_button( __( 'Save Settings', 'alkana' ) ); ?>
		</form>
	</div>
	<?php
}
