<?php
/**
 * BP Attachments Filters.
 *
 * @package \bp-attachments\bp-attachments-hooks
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'bp_core_get_components', 'bp_attachments_get_component_info' );

/**
 * Only disable BuddyPress Member's avatar upload feature.
 *
 * @since 1.0.0
 *
 * @param bool $retval True if the BP Avatar UI should be loaded. False otherwise.
 * @return bool
 */
function bp_attachments_is_avatar_front_edit( $retval ) {
	if ( true === $retval ) {
		$retval = ! bp_is_user_change_avatar();
	}

	return $retval;
}
add_filter( 'bp_avatar_is_front_edit', 'bp_attachments_is_avatar_front_edit' );


/**
 * Only disable BuddyPress Member's cover image upload feature.
 *
 * @since 1.0.0
 *
 * @param bool $retval True if the BP Cover Image UI should be loaded. False otherwise.
 * @return bool
 */
function bp_attachments_is_cover_image_front_edit( $retval ) {
	if ( true === $retval ) {
		$retval = ! bp_is_user_change_cover_image();
	}

	return $retval;
}
add_filter( 'bp_attachments_cover_image_is_edit', 'bp_attachments_is_cover_image_front_edit' );
