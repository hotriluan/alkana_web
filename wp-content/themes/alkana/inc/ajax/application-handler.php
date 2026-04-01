<?php
/**
 * AJAX handler for job application submission with CV upload.
 *
 * Action: alkana_submit_application (public + logged-in)
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_submit_application',        'alkana_ajax_submit_application' );
add_action( 'wp_ajax_nopriv_alkana_submit_application', 'alkana_ajax_submit_application' );

function alkana_ajax_submit_application(): void {
	// Verify nonce
	if ( ! check_ajax_referer( 'alkana_apply', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed.', 'alkana' ) ], 403 );
	}

	// Honeypot check
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( [ 'message' => __( 'Spam detected.', 'alkana' ) ], 400 );
	}

	// Rate limiting: max 5 applications per hour per IP
	$ip_key = 'alkana_apply_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		wp_send_json_error( [ 'message' => __( 'Too many submissions. Please try again later.', 'alkana' ) ], 429 );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	// Sanitize inputs
	$name    = sanitize_text_field( $_POST['name'] ?? '' );
	$email   = sanitize_email( $_POST['email'] ?? '' );
	$phone   = sanitize_text_field( $_POST['phone'] ?? '' );
	$message = sanitize_textarea_field( $_POST['message'] ?? '' );
	$job_id  = (int) ( $_POST['job_id'] ?? 0 );

	// Validate required fields
	$errors = [];

	if ( empty( $name ) ) {
		$errors[] = __( 'Name is required.', 'alkana' );
	}

	if ( empty( $email ) || ! is_email( $email ) ) {
		$errors[] = __( 'Valid email is required.', 'alkana' );
	}

	if ( empty( $_FILES['cv']['name'] ) ) {
		$errors[] = __( 'CV file is required.', 'alkana' );
	}

	if ( $job_id && ! get_post( $job_id ) ) {
		$errors[] = __( 'Invalid job position.', 'alkana' );
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error( [ 'message' => implode( ' ', $errors ) ], 400 );
	}

	// Handle file upload
	$file = $_FILES['cv'];

	// Validate file type (server-side only, client MIME is spoofable)
	$allowed_types = [ 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ];
	$file_type     = wp_check_filetype( $file['name'] );

	if ( ! in_array( $file_type['type'], $allowed_types, true ) ) {
		wp_send_json_error( [ 'message' => __( 'Only PDF, DOC, and DOCX files are allowed.', 'alkana' ) ], 400 );
	}

	// Validate file size (5MB max)
	if ( $file['size'] > 5 * 1024 * 1024 ) {
		wp_send_json_error( [ 'message' => __( 'File size must not exceed 5MB.', 'alkana' ) ], 400 );
	}

	// Upload file
	require_once ABSPATH . 'wp-admin/includes/file.php';

	$upload_overrides = [
		'test_form' => false,
		'mimes'     => [
			'pdf'  => 'application/pdf',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		],
	];

	$uploaded = wp_handle_upload( $file, $upload_overrides );

	if ( ! empty( $uploaded['error'] ) ) {
		wp_send_json_error( [ 'message' => $uploaded['error'] ], 500 );
	}

	// Create attachment
	$attachment_id = wp_insert_attachment( [
		'post_mime_type' => $uploaded['type'],
		'post_title'     => sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	], $uploaded['file'] );

	if ( is_wp_error( $attachment_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Failed to create attachment.', 'alkana' ) ], 500 );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $uploaded['file'] ) );

	// Create application post
	$post_id = wp_insert_post( [
		'post_type'   => 'alkana_application',
		'post_title'  => $name,
		'post_status' => 'publish',
	] );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Failed to create application.', 'alkana' ) ], 500 );
	}

	// Save metadata
	update_post_meta( $post_id, '_app_email', $email );
	update_post_meta( $post_id, '_app_phone', $phone );
	update_post_meta( $post_id, '_app_job_id', $job_id );
	update_post_meta( $post_id, '_app_cv_url', $uploaded['url'] );
	update_post_meta( $post_id, '_app_cv_id', $attachment_id );
	update_post_meta( $post_id, '_app_message', $message );
	update_post_meta( $post_id, '_app_status', 'new' );

	// Send admin notification
	$admin_email = get_option( 'admin_email' );
	$job_title   = $job_id ? get_the_title( $job_id ) : __( 'General Application', 'alkana' );

	$subject = sprintf( __( '[Alkana] New Application: %s', 'alkana' ), $job_title );
	$body    = sprintf(
		__( "New job application received:\n\nApplicant: %s\nEmail: %s\nPhone: %s\nPosition: %s\nCV: %s\n\nView application: %s", 'alkana' ),
		$name,
		$email,
		$phone,
		$job_title,
		$uploaded['url'],
		admin_url( 'post.php?post=' . $post_id . '&action=edit' )
	);

	wp_mail( $admin_email, $subject, $body );

	wp_send_json_success( [
		'message' => __( 'Application submitted successfully! We will contact you soon.', 'alkana' ),
	] );
}
