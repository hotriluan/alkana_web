<?php
/**
 * Admin Meta Box: Application Details
 * Shows applicant information when viewing/editing alkana_application posts.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_alkana_application', 'alkana_add_application_meta_box' );
add_action( 'save_post_alkana_application', 'alkana_save_application_meta_box', 10, 2 );

/**
 * Register the application details meta box.
 */
function alkana_add_application_meta_box(): void {
	add_meta_box(
		'alkana_application_details',
		__( 'Application Details', 'alkana' ),
		'alkana_render_application_meta_box',
		'alkana_application',
		'normal',
		'high'
	);
}

/**
 * Render the meta box content.
 *
 * @param WP_Post $post Current post object.
 */
function alkana_render_application_meta_box( $post ): void {
	wp_nonce_field( 'alkana_save_application', 'alkana_application_nonce' );

	$email    = get_post_meta( $post->ID, '_app_email', true );
	$phone    = get_post_meta( $post->ID, '_app_phone', true );
	$job_id   = get_post_meta( $post->ID, '_app_job_id', true );
	$cv_url   = get_post_meta( $post->ID, '_app_cv_url', true );
	$message  = get_post_meta( $post->ID, '_app_message', true );
	$status   = get_post_meta( $post->ID, '_app_status', true ) ?: 'new';

	$job_title = '';
	$job_link  = '';
	if ( $job_id ) {
		$job_post = get_post( $job_id );
		if ( $job_post ) {
			$job_title = $job_post->post_title;
			$job_link  = get_edit_post_link( $job_id );
		}
	}

	$submitted_date = get_the_date( 'Y-m-d H:i:s', $post );
	?>

	<style>
		.app-meta-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		.app-meta-table th { text-align: left; padding: 12px; background: #f6f7f7; font-weight: 600; width: 160px; vertical-align: top; }
		.app-meta-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
		.app-meta-table tr:last-child td { border-bottom: none; }
		.app-meta-message { background: #f9fafb; padding: 12px; border-radius: 4px; white-space: pre-wrap; max-height: 200px; overflow-y: auto; }
		.app-status-select { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px; }
		.app-cv-link { display: inline-flex; align-items: center; gap: 6px; color: #2271b1; text-decoration: none; }
		.app-cv-link:hover { color: #135e96; text-decoration: underline; }
		.app-job-link { color: #2271b1; text-decoration: none; font-weight: 500; }
		.app-job-link:hover { color: #135e96; text-decoration: underline; }
	</style>

	<table class="app-meta-table">
		<tr>
			<th><?php esc_html_e( 'Applicant Email', 'alkana' ); ?></th>
			<td>
				<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php else : ?>
					<span style="color: #999;">—</span>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Phone Number', 'alkana' ); ?></th>
			<td>
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
				<?php else : ?>
					<span style="color: #999;">—</span>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Job Position', 'alkana' ); ?></th>
			<td>
				<?php if ( $job_title && $job_link ) : ?>
					<a href="<?php echo esc_url( $job_link ); ?>" class="app-job-link" target="_blank">
						<?php echo esc_html( $job_title ); ?>
					</a>
				<?php elseif ( $job_title ) : ?>
					<?php echo esc_html( $job_title ); ?>
				<?php else : ?>
					<span style="color: #999;">—</span>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'CV/Resume', 'alkana' ); ?></th>
			<td>
				<?php if ( $cv_url ) : ?>
					<a href="<?php echo esc_url( $cv_url ); ?>" class="app-cv-link" target="_blank" download>
						<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
							<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
							<path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
						</svg>
						<?php esc_html_e( 'Download CV', 'alkana' ); ?>
					</a>
				<?php else : ?>
					<span style="color: #999;">—</span>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Cover Letter', 'alkana' ); ?></th>
			<td>
				<?php if ( $message ) : ?>
					<div class="app-meta-message"><?php echo esc_html( $message ); ?></div>
				<?php else : ?>
					<span style="color: #999;">—</span>
				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Application Status', 'alkana' ); ?></th>
			<td>
				<select name="app_status" class="app-status-select">
					<option value="new" <?php selected( $status, 'new' ); ?>><?php esc_html_e( 'New', 'alkana' ); ?></option>
					<option value="reviewing" <?php selected( $status, 'reviewing' ); ?>><?php esc_html_e( 'Reviewing', 'alkana' ); ?></option>
					<option value="shortlisted" <?php selected( $status, 'shortlisted' ); ?>><?php esc_html_e( 'Shortlisted', 'alkana' ); ?></option>
					<option value="rejected" <?php selected( $status, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'alkana' ); ?></option>
				</select>
				<p class="description" style="margin-top: 8px;">
					<?php esc_html_e( 'Update the application status to track progress.', 'alkana' ); ?>
				</p>
			</td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Date Submitted', 'alkana' ); ?></th>
			<td><?php echo esc_html( $submitted_date ); ?></td>
		</tr>
	</table>

	<?php
}

/**
 * Save the status field when the post is saved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function alkana_save_application_meta_box( $post_id, $post ): void {
	// Verify nonce.
	if ( ! isset( $_POST['alkana_application_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['alkana_application_nonce'], 'alkana_save_application' ) ) {
		return;
	}

	// Don't save during autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check user permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save status field.
	if ( isset( $_POST['app_status'] ) ) {
		$status = sanitize_text_field( $_POST['app_status'] );
		$allowed_statuses = [ 'new', 'reviewing', 'shortlisted', 'rejected' ];
		if ( in_array( $status, $allowed_statuses, true ) ) {
			update_post_meta( $post_id, '_app_status', $status );
		}
	}
}
