<?php
/**
 * AJAX handler for newsletter subscription.
 *
 * Action: alkana_newsletter (public + logged-in)
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_alkana_newsletter',        'alkana_ajax_newsletter' );
add_action( 'wp_ajax_nopriv_alkana_newsletter', 'alkana_ajax_newsletter' );

function alkana_ajax_newsletter(): void {
	// Verify nonce
	if ( ! check_ajax_referer( 'alkana_newsletter', 'newsletter_nonce', false ) ) {
		wp_send_json_error( [ 'message' => __( 'Lỗi bảo mật. Vui lòng tải lại trang.', 'alkana' ) ], 403 );
	}

	// Rate limiting: max 3 newsletter subscriptions per hour per IP
	$ip_key = 'alkana_newsletter_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 3 ) {
		wp_send_json_error( [ 'message' => __( 'Bạn đã đăng ký quá nhiều lần. Vui lòng thử lại sau.', 'alkana' ) ], 429 );
	}
	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	// Sanitize and validate email
	$email = sanitize_email( $_POST['email'] ?? '' );

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Vui lòng nhập địa chỉ email hợp lệ.', 'alkana' ) ], 400 );
	}

	// Get existing newsletter emails
	$newsletter_emails = get_option( 'alkana_newsletter_emails', [] );

	// Check if email already exists
	if ( in_array( $email, $newsletter_emails, true ) ) {
		wp_send_json_success( [
			'message' => __( 'Email này đã được đăng ký trước đó. Cảm ơn bạn!', 'alkana' ),
		] );
	}

	// Add new email to the list
	$newsletter_emails[] = $email;
	update_option( 'alkana_newsletter_emails', $newsletter_emails );

	// Send notification to admin (optional)
	$admin_email = get_option( 'admin_email' );
	$subject     = sprintf( __( '[Alkana] Đăng ký nhận tin mới: %s', 'alkana' ), $email );
	$body        = sprintf(
		__( "Có người đăng ký nhận tin từ website:\n\nEmail: %s\nThời gian: %s\n\nTổng số email đăng ký: %d", 'alkana' ),
		$email,
		current_time( 'Y-m-d H:i:s' ),
		count( $newsletter_emails )
	);

	wp_mail( $admin_email, $subject, $body );

	wp_send_json_success( [
		'message' => __( 'Cảm ơn bạn đã đăng ký! Chúng tôi sẽ gửi thông tin mới nhất đến bạn.', 'alkana' ),
	] );
}
