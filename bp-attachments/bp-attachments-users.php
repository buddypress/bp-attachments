<?php
/**
 * BP Attachments Users.
 *
 * @package \bp-attachments\bp-attachments-users
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the user meta to store the total amount of bytes uploaded.
 *
 * @since 1.0.0
 */
function bp_attachments_register_user_meta() {
	register_meta(
		'user',
		'_bp_attachments_userfiles_size',
		array(
			'single'            => true,
			'type'              => 'integer',
			'description'       => __( 'Total space in bytes used by user’s files', 'bp-attachments' ),
			'show_in_rest'      => false, // We're not showing this meta into the WP users REST endpoint.
			'sanitize_callback' => 'absint',
			'default'           => 0,
		)
	);
}
add_action( 'bp_init', 'bp_attachments_register_user_meta' );

/**
 * Registers the BP REST API custom field to retrieve the total amount of bytes users uploaded.
 *
 * @since 1.0.0
 */
function bp_attachments_register_rest_user_field() {
	bp_rest_register_field(
		'members',
		'bp_member_files_size',
		array(
			'schema'       => array(
				'type'        => 'integer',
				'description' => __( 'Total space in bytes used by user’s files', 'bp-attachments' ),
				'default'     => 0,
				'context'     => array( 'view', 'edit' ),
			),
			'get_callback' => 'bp_attachments_user_get_files_size',
		)
	);
}
add_action( 'bp_rest_api_init', 'bp_attachments_register_rest_user_field' );

/**
 * Getter for the BP REST API custom field.
 *
 * @since 1.0.0
 *
 * @param array $member The member's data retrieved from the BP REST API.
 * @return integer The total amount of bytes a user uploaded.
 */
function bp_attachments_user_get_files_size( $member = array() ) {
	$user_id = $member['id'];

	return (int) bp_get_user_meta( $user_id, '_bp_attachments_userfiles_size', true );
}

/**
 * Raises the total amount of bytes a user uploaded.
 *
 * @since 1.0.0
 *
 * @param integer $user_id The member's ID.
 * @param integer $bytes   The amount of bytes just uploaded.
 */
function bp_attachments_user_raise_files_size( $user_id, $bytes = 0 ) {
	$prev_value = (int) bp_get_user_meta( $user_id, '_bp_attachments_userfiles_size', true );
	$files_size = $prev_value + (int) $bytes;

	bp_update_user_meta( $user_id, '_bp_attachments_userfiles_size', $files_size );
}

/**
 * Decreases the total amount of bytes a user uploaded.
 *
 * @since 1.0.0
 *
 * @param integer $user_id The member's ID.
 * @param integer $bytes   The amount of bytes just deleted.
 */
function bp_attachments_user_decrease_files_size( $user_id, $bytes = 0 ) {
	$prev_value    = (int) bp_get_user_meta( $user_id, '_bp_attachments_userfiles_size', true );
	$removed_bytes = (int) $bytes;

	if ( $removed_bytes > $prev_value ) {
		$files_size = 0;
	} else {
		$files_size = $prev_value - $removed_bytes;
	}

	bp_update_user_meta( $user_id, '_bp_attachments_userfiles_size', $files_size );
}
