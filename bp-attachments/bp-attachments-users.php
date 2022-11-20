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

/**
 * BP Attachments user capabilities.
 *
 * @since 1.0.0
 *
 * @return array BP Attachments user capabilities list.
 */
function bp_attachments_user_media_caps() {
	$caps = array(
		'upload_bp_media',
		'edit_bp_media',
		'delete_bp_media',
		'read_bp_media',
		'download_bp_media',
		'edit_bp_medium',
		'delete_bp_medium',
		'read_bp_medium',
		'download_bp_medium',
	);

	return array_fill_keys( $caps, true );
}

/**
 * Check the current user's BP Attachments Media capability.
 *
 * @since 1.0.0
 *
 * @param bool   $can        The default cap.
 * @param string $capability The cap name to check.
 * @param array  $args       An array containing extra information for extended checks.
 * @return bool True if the user can. False otherwise.
 */
function bp_attachments_media_user_can( $can = false, $capability = '', $args = array() ) {
	$current_user_id = bp_loggedin_user_id();
	$built_in_caps   = bp_attachments_user_media_caps();

	if ( ! isset( $built_in_caps[ $capability ] ) ) {
		return $can;
	}

	// Set default values.
	$can          = false;
	$is_logged_in = is_user_logged_in();
	$bp_medium    = null;

	if ( isset( $args['bp_medium'] ) && is_object( $args['bp_medium'] ) ) {
		$bp_medium = $args['bp_medium'];
	}

	switch ( $capability ) {
		// Reading and downloading public media.
		case 'read_bp_media':
		case 'download_bp_media':
			$can = bp_current_user_can( 'exist' );
			break;

		// Uploading media.
		case 'upload_bp_media':
			$can = $is_logged_in && ! empty( $current_user_id );
			break;

		// Edit and delete any media.
		case 'edit_bp_media':
		case 'delete_bp_media':
			$can = bp_current_user_can( 'bp_moderate' );
			break;

		// Edit and delete a specific medium.
		case 'edit_bp_medium':
		case 'delete_bp_medium':
			// Community owner can manage it.
			$can = bp_current_user_can( 'bp_moderate' );

			// BP Medium owner can manage it.
			if ( ! $can && isset( $bp_medium->owner_id ) ) {
				$can = (int) $current_user_id === (int) $bp_medium->owner_id;
			}
			break;

		// Read and download a specific medium.
		case 'read_bp_medium':
		case 'download_bp_medium':
			// Community owner can read and download it whatever the visibility.
			$can = bp_current_user_can( 'bp_moderate' );

			// BP Medium owner can read and download it whatever the visibility.
			if ( ! $can && isset( $bp_medium->owner_id ) ) {
				$can = (int) $current_user_id === (int) $bp_medium->owner_id;
			}

			if ( ! $can && isset( $bp_medium->visibility ) ) {
				// A public medium can be read and downloaded by anyone.
				if ( 'private' !== $bp_medium->visibility ) {
					$can = bp_current_user_can( 'exist' );

					// Private medium can be read and downloaded by allowed members.
				} elseif ( isset( $bp_medium->attached_to ) && is_array( $bp_medium->attached_to ) ) {
					// Loop through attached objects to check user's capability to view/download the private medium.
					foreach ( $bp_medium->attached_to as $attached_item ) {
						if ( true === $can || ! isset( $attached_item->object_type, $attached_item->object_id ) || ! bp_is_active( $attached_item->object_type ) ) {
							continue;
						}

						switch ( $attached_item->object_type ) {
							case 'messages':
								$thread_id = (int) $attached_item->object_id;

								// Check the private message thread's recipients.
								$thread     = new BP_Messages_Thread( $thread_id );
								$recipients = $thread->get_recipients();
								$can        = (bool) wp_filter_object_list( $recipients, array( 'user_id' => $current_user_id ) );
								break;

							default:
								/**
								 * Use this filter to deal with the user's capability and your custom component.
								 *
								 * @since 1.0.0
								 *
								 * @param bool      $can       Whether the user can access to the media or not. Default: `false`.
								 * @param BP_Medium $bp_medium The Medium object.
								 */
								$can = apply_filters( 'bp_attachments_current_user_can_for_' . $attached_item->object_type, $can, $bp_medium );
								break;
						}
					}
				}
			}
			break;
		default:
			$can = false;
			break;
	}

	return $can;
}
add_filter( 'bp_attachments_current_user_can', 'bp_attachments_media_user_can', 1, 3 );
