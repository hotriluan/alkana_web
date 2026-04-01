<?php
/**
 * AJAX handler for contact form submission.
 *
 * Action: alkana_submit_contact (public + logged-in)
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_submit_contact',        'alkana_ajax_submit_contact' );
add_action( 'wp_ajax_nopriv_alkana_submit_contact', 'alkana_ajax_submit_contact' );

function alkana_ajax_submit_contact(): void {
	// Verify nonce
	if ( ! check_ajax_referer( 'alkana_contact', '_alkana_nonce', false ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed.', 'alkana' ) ], 403 );
	}

	// Honeypot check
	if ( ! empty( $_POST['url_website'] ) ) {
		wp_send_json_error( [ 'message' => __( 'Spam detected.', 'alkana' ) ], 400 );
	}

	// Rate limiting: max 5 contact messages per hour per IP
	$ip_key = 'alkana_contact_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		wp_send_json_error( [ 'message' => __( 'Too many submissions. Please try again later.', 'alkana' ) ], 429 );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	// Sanitize inputs
	$name    = sanitize_text_field( $_POST['contact_name'] ?? '' );
	$email   = sanitize_email( $_POST['contact_email'] ?? '' );
	$phone   = sanitize_text_field( $_POST['contact_phone'] ?? '' );
	$message = sanitize_textarea_field( $_POST['contact_message'] ?? '' );

	// Validate required fields
	$errors = [];

	if ( empty( $name ) ) {
		$errors[] = __( 'Name is required.', 'alkana' );
	}

	if ( empty( $email ) || ! is_email( $email ) ) {
		$errors[] = __( 'Valid email is required.', 'alkana' );
	}

	if ( empty( $message ) ) {
		$errors[] = __( 'Message is required.', 'alkana' );
	}

	if ( ! empty( $errors ) ) {
		wp_send_json_error( [ 'message' => implode( ' ', $errors ) ], 400 );
	}

	// Send email to admin
	$admin_email = get_option( 'admin_email' );
	$subject     = sprintf( __( '[Alkana] Contact Form: %s', 'alkana' ), $name );
	
	$body = sprintf(
		__( "New contact form submission:\n\nName: %s\nEmail: %s\nPhone: %s\n\nMessage:\n%s", 'alkana' ),
		$name,
		$email,
		$phone ?: __( 'Not provided', 'alkana' ),
		$message
	);

	$headers = [
		'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>',
		'Reply-To: ' . $name . ' <' . $email . '>',
	];

	$mail_sent = wp_mail( $admin_email, $subject, $body, $headers );

	if ( ! $mail_sent ) {
		wp_send_json_error( [ 'message' => __( 'Failed to send email. Please try again.', 'alkana' ) ], 500 );
	}

	wp_send_json_success( [
		'message' => __( 'Thank you! Your message has been sent successfully. We will contact you soon.', 'alkana' ),
	] );
}
