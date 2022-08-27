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

/**
 * Include BP Attachments Blocks needed information to Block Editor Settings.
 *
 * @since 1.0.0
 *
 * @param array $settings The Block Editor Settings.
 * @return array The Block Editor Settings.
 */
function bp_attachments_block_editor_settings( $settings = array() ) {
	$settings['bpAttachments'] = array(
		'allowedExtByMediaList' => bp_attachments_get_exts_by_medialist(),
		'allowedExtTypes'       => bp_attachments_get_allowed_media_exts( '', true ),
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'bp_attachments_block_editor_settings', 10, 1 );
add_filter( 'bp_activity_block_editor_settings', 'bp_attachments_block_editor_settings', 10, 1 );
