<?php
/**
 * BP Attachments Tracking Functions.
 *
 * Tracking attachments is performed using the BP Activity table.
 *
 * @package \bp-attachments\bp-attachments-tracking
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the Attachments tracking database table.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_get_table() {
	return bp_core_get_table_prefix() . 'bp_activity';
}

/**
 * Returns the Attachments tracking meta database table.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_get_meta_table() {
	return bp_core_get_table_prefix() . 'bp_activity_meta';
}

/**
 * Inserts a new record into the Media Tracking table.
 *
 * @since 1.0.0
 *
 * @param BP_Medium $medium The BP Medium object.
 * @return int|false The inserted Media Tracking ID. False on failure.
 */
function bp_attachments_tracking_record_created_medium( $medium ) {
	global $wpdb;

	// Folders are not tracked for now.
	if ( ! isset( $medium->owner_id, $medium->media_type, $medium->links, $medium->visibility ) || 'folder' === $medium->media_type ) {
		return false;
	}

	$inserted = $wpdb->insert( // phpcs:ignore
		bp_attachments_tracking_get_table(),
		array(
			'user_id'       => $medium->owner_id,
			'component'     => buddypress()->members->id,
			'type'          => 'uploaded_attachment',
			'action'        => bp_attachments_get_serialized_block(
				array(
					'blockName'    => 'bp/attachments-action',
					'innerContent' => array(),
					'attrs'        => array(
						'type' => $medium->media_type,
					),
				)
			),
			'content'       => bp_attachments_get_serialized_medium_block( $medium ),
			'primary_link'  => $medium->links['view'],
			'item_id'       => 0,
			'date_recorded' => bp_core_current_time(),
			'hide_sitewide' => 'public' !== $medium->visibility,
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d' )
	);

	if ( ! $inserted ) {
		return false;
	}

	return $wpdb->insert_id;
}
add_action( 'bp_attachments_created_media', 'bp_attachments_tracking_record_created_medium', 10, 1 );

/**
 * Erases a tracked Attachment when the corresponding medium was deleted.
 *
 * @since 1.0.0
 *
 * @param object $medium A Medium data object.
 * @return bool True on success. False on failure.
 */
function bp_attachments_tracking_erase_deleted_medium( $medium ) {
	global $wpdb;

	// Make sure we have the needed props and we're not dealing with revisions.
	if ( ! isset( $medium->id, $medium->owner_id, $medium->abspath, $medium->visibility ) || preg_match( '#\._revisions#', $medium->abspath ) ) {
		return false;
	}

	$up_dir = bp_attachments_get_media_uploads_dir( $medium->visibility );
	$chunks = explode( '/', trim( str_replace( $up_dir['path'], '', $medium->abspath ), '/' ) );
	$object = array_shift( $chunks );

	// Remove the owner id from chunks.
	array_shift( $chunks );

	$item_action_variables = array( $medium->id );
	if ( array_filter( $chunks ) ) {
		$item_action_variables = array_merge( $chunks, $item_action_variables );
	}

	// Set the primary link.
	$primary_link = bp_attachments_get_medium_url(
		array(
			'visibility'            => $medium->visibility,
			'object'                => $object,
			'object_item'           => bp_core_get_username( $medium->owner_id ),
			'item_action'           => bp_attachments_get_item_action_slug( 'view' ),
			'item_action_variables' => $item_action_variables,
		)
	);

	$deleted = $wpdb->delete( // phpcs:ignore
		bp_attachments_tracking_get_table(),
		array(
			'type'         => 'uploaded_attachment',
			'primary_link' => $primary_link,
		),
		array( '%s', '%s' )
	);

	if ( ! $deleted ) {
		return false;
	}

	return true;
}
add_action( 'bp_attachments_deleted_medium', 'bp_attachments_tracking_erase_deleted_medium', 10, 1 );

/**
 * Exclude Uploaded attachments from activity loops.
 *
 * @todo This will need to further thoughts. Imho, we should:
 * - Register activity types.
 * - Add Activity Action strings callback.
 * - Leave an option to allow Attachments to be displayed into activity streams.
 *
 * @since 1.0.0
 *
 * @param array $where_conditions Activity loop conditions for the MySQL WHERE statement.
 * @return array Activity loop conditions for the MySQL WHERE statement.
 */
function bp_attachments_tracking_exclude_from_activities( $where_conditions ) {
	if ( isset( $where_conditions['excluded_types'] ) ) {
		preg_match( '/a\.type NOT IN \([^\)](.*?)[^\)]\)/', $where_conditions['excluded_types'], $matches );
		if ( isset( $matches[0], $matches[1] ) && $matches[0] && $matches[1] ) {
			$excluded_types                     = '\'' . trim( $matches[1], '\'' ) . '\'';
			$where_conditions['excluded_types'] = str_replace( $excluded_types, $excluded_types . ', \'uploaded_attachment\'', $where_conditions['excluded_types'] );
		}
	}

	return $where_conditions;
}
add_filter( 'bp_activity_get_where_conditions', 'bp_attachments_tracking_exclude_from_activities', 1, 1 );
