<?php
/**
 * Custom admin columns for alkana_application post type.
 * Shows applicant details, job title, CV link, status badges.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'manage_alkana_application_posts_columns', 'alkana_application_columns' );
add_action( 'manage_alkana_application_posts_custom_column', 'alkana_application_column_content', 10, 2 );
add_filter( 'manage_edit-alkana_application_sortable_columns', 'alkana_application_sortable_columns' );
add_action( 'restrict_manage_posts', 'alkana_application_filters' );
add_filter( 'parse_query', 'alkana_application_filter_query' );

function alkana_application_columns( $columns ) {
	$new = [];
	$new['cb']       = $columns['cb'];
	$new['title']    = __( 'Applicant Name', 'alkana' );
	$new['email']    = __( 'Email', 'alkana' );
	$new['phone']    = __( 'Phone', 'alkana' );
	$new['job']      = __( 'Job Position', 'alkana' );
	$new['cv']       = __( 'CV', 'alkana' );
	$new['status']   = __( 'Status', 'alkana' );
	$new['date']     = __( 'Date', 'alkana' );
	return $new;
}

function alkana_application_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'email':
			$email = get_post_meta( $post_id, '_app_email', true );
			echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—';
			break;

		case 'phone':
			$phone = get_post_meta( $post_id, '_app_phone', true );
			echo $phone ? esc_html( $phone ) : '—';
			break;

		case 'job':
			$job_id = (int) get_post_meta( $post_id, '_app_job_id', true );
			if ( $job_id ) {
				$job = get_post( $job_id );
				if ( $job ) {
					printf(
						'<a href="%s" target="_blank">%s</a>',
						esc_url( get_edit_post_link( $job_id ) ),
						esc_html( $job->post_title )
					);
				} else {
					esc_html_e( 'Job deleted', 'alkana' );
				}
			} else {
				echo '—';
			}
			break;

		case 'cv':
			$cv_url = get_post_meta( $post_id, '_app_cv_url', true );
			if ( $cv_url ) {
				printf(
					'<a href="%s" target="_blank" class="button button-small">%s</a>',
					esc_url( $cv_url ),
					esc_html__( 'Download', 'alkana' )
				);
			} else {
				echo '—';
			}
			break;

		case 'status':
			$status = get_post_meta( $post_id, '_app_status', true ) ?: 'new';
			$labels = [
				'new'         => [ 'label' => __( 'New', 'alkana' ), 'color' => '#2271b1' ],
				'reviewing'   => [ 'label' => __( 'Reviewing', 'alkana' ), 'color' => '#dba617' ],
				'shortlisted' => [ 'label' => __( 'Shortlisted', 'alkana' ), 'color' => '#00a32a' ],
				'rejected'    => [ 'label' => __( 'Rejected', 'alkana' ), 'color' => '#d63638' ],
			];

			$badge = $labels[ $status ] ?? $labels['new'];
			printf(
				'<span style="display:inline-block;padding:4px 8px;border-radius:3px;background:%s;color:#fff;font-size:11px;font-weight:600;text-transform:uppercase;">%s</span>',
				esc_attr( $badge['color'] ),
				esc_html( $badge['label'] )
			);
			break;
	}
}

function alkana_application_sortable_columns( $columns ) {
	$columns['status'] = 'status';
	$columns['job']    = 'job';
	return $columns;
}

function alkana_application_filters( $post_type ) {
	if ( 'alkana_application' !== $post_type ) {
		return;
	}

	// Status filter
	$status  = isset( $_GET['app_status'] ) ? sanitize_key( $_GET['app_status'] ) : '';
	$statuses = [
		''            => __( 'All Statuses', 'alkana' ),
		'new'         => __( 'New', 'alkana' ),
		'reviewing'   => __( 'Reviewing', 'alkana' ),
		'shortlisted' => __( 'Shortlisted', 'alkana' ),
		'rejected'    => __( 'Rejected', 'alkana' ),
	];

	echo '<select name="app_status">';
	foreach ( $statuses as $val => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $val ),
			selected( $status, $val, false ),
			esc_html( $label )
		);
	}
	echo '</select>';

	// Job filter
	$job_id = isset( $_GET['app_job'] ) ? (int) $_GET['app_job'] : 0;
	$jobs   = get_posts( [
		'post_type'      => 'alkana_job',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	if ( $jobs ) {
		echo '<select name="app_job">';
		echo '<option value="">' . esc_html__( 'All Positions', 'alkana' ) . '</option>';
		foreach ( $jobs as $job ) {
			printf(
				'<option value="%d"%s>%s</option>',
				$job->ID,
				selected( $job_id, $job->ID, false ),
				esc_html( $job->post_title )
			);
		}
		echo '</select>';
	}
}

function alkana_application_filter_query( $query ) {
	global $pagenow;

	if ( ! is_admin() || 'edit.php' !== $pagenow || ! isset( $query->query_vars['post_type'] ) || 'alkana_application' !== $query->query_vars['post_type'] ) {
		return;
	}

	$meta_query = [];

	// Filter by status
	if ( ! empty( $_GET['app_status'] ) ) {
		$meta_query[] = [
			'key'   => '_app_status',
			'value' => sanitize_key( $_GET['app_status'] ),
		];
	}

	// Filter by job
	if ( ! empty( $_GET['app_job'] ) ) {
		$meta_query[] = [
			'key'   => '_app_job_id',
			'value' => (int) $_GET['app_job'],
			'type'  => 'NUMERIC',
		];
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}
}
