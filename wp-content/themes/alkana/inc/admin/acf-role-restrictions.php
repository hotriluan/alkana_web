<?php
/**
 * Restrict ACF field groups by user role.
 *
 * Tech editors only see technical specification field groups.
 * Content editors see all field groups.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/get_field_groups', 'alkana_acf_role_restrict_field_groups' );

/**
 * Filter field groups visible to alkana_tech_editor.
 *
 * @param array[] $groups ACF field group arrays.
 * @return array[]
 */
function alkana_acf_role_restrict_field_groups( array $groups ): array {
	if ( current_user_can( 'administrator' ) || current_user_can( 'alkana_content_editor' ) ) {
		return $groups;
	}

	if ( ! current_user_can( 'alkana_tech_editor' ) ) {
		return $groups;
	}

	// Keys that tech editors are allowed to see
	$allowed_keys = [
		'group_alkana_product_specs',
		'group_alkana_product_variants',
	];

	return array_filter( $groups, static function ( $group ) use ( $allowed_keys ): bool {
		return in_array( $group['key'] ?? '', $allowed_keys, true );
	} );
}
