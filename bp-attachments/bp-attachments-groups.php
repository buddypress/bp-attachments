<?php
/**
 * BP Attachments Functions.
 *
 * WIP: for now this file is ignored. Groups support should happen in a future version of the plugin.
 *
 * @package \bp-attachments\bp-attachments-groups
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get translated BP Attachments groups slug.
 *
 * @since 1.0.0
 *
 * @param array $slugs An assiociative array keyed with component names where the value is the object slug.
 * @return array The available attachments objects slugs.
 */
function bp_attachments_get_item_groups_slug( $slugs = array() ) {
	return array_merge(
		$slugs,
		array(
			'groups' => sanitize_title( _x( 'groups', 'group object slug', 'bp-attachments' ) ),
		)
	);
}
add_filter( 'bp_attachments_get_item_object_slugs', 'bp_attachments_get_item_groups_slug', 10, 1 );

/**
 * Checks if a user can create a media attached to the requested Group.
 *
 * @since 1.0.0
 *
 * @param bool       $can_upload  True if the user create a media attached to the requested object. False otherwise.
 * @param string     $object      The requested object type eg: `groups`, `members`...
 * @param string|int $object_item The slug or ID of the object (in case of a group it's a slug).
 * @return bool True if the user create a media attached to the requested group. False otherwise.
 */
function bp_attachments_rest_can_upload_to_group( $can_upload, $object = '', $object_item = '' ) {
	if ( 'groups' !== $object || ! $object_item ) {
		return $can_upload;
	}

	$group_slug = sanitize_title( $object_item );
	$group_id   = (int) BP_Groups_Group::get_id_from_slug( $group_slug );

	return current_user_can( 'bp_moderate' ) || groups_is_user_member( bp_loggedin_user_id(), $group_id );
}
add_filter( 'bp_attachments_rest_can_upload_to_object', 'bp_attachments_rest_can_upload_to_group', 10, 3 );
add_filter( 'bp_attachments_rest_can_create_dir_to_object', 'bp_attachments_rest_can_upload_to_group', 10, 3 );

/**
 * Ajust the uploads sub-directory for Groups.
 *
 * @since 1.0.0
 *
 * @param array $upload_dir {@see `wp_upload_dir()`}.
 * @param array $media_args {
 *     An array of arguments.
 *
 *     @type string     $visibility  Whether the medium being created is public or private.
 *                                   Default 'public'.
 *     @type string     $object      The name of the object the medium being created is attached to.
 *                                   Default 'members'. Possible values are 'members', 'groups' or any
 *                                   custom BuddyPress component's name.
 *     @type string|int $object_item The unique identifier for the object's item. It can be the object's ID or slug.
 *     @type string     $parent_dir  The parent directory the medium being created is attached to.
 * }
 * @return string The uploads sub-directory for Groups.
 */
function bp_attachments_groups_media_uploads_dir( $upload_dir, $media_args = array() ) {
	if ( 'groups' !== $media_args['object'] || ! $media_args['object_item'] ) {
		return $upload_dir;
	}

	$group_slug = sanitize_title( $media_args['object_item'] );
	$group_id   = (int) BP_Groups_Group::get_id_from_slug( $group_slug );

	if ( ! $group_id ) {
		$upload_dir['bp_attachments_error_code'] = 17;
		return $upload_dir;
	}

	if ( $media_args['parent_dir'] ) {
		$subdir = '/' . trim( $media_args['parent_dir'], '/' );
		$subdir = str_replace(
			'/groups/' . $group_slug,
			'/groups/' . $group_id,
			$subdir
		);

		if ( ! is_dir( $upload_dir['basedir'] . $subdir ) ) {
			$upload_dir['bp_attachments_error_code'] = 17;
			return $upload_dir;
		} else {
			$upload_dir['subdir'] .= $subdir;
		}
	} else {
		$group = groups_get_group( $group_id );

		if ( $group_id !== (int) $group->id ) {
			$upload_dir['bp_attachments_error_code'] = 17;
			return $upload_dir;
		}

		if ( 'public' === $group->status ) {
			$upload_dir = bp_attachments_get_public_uploads_dir();
		} else {
			$upload_dir = bp_attachments_get_private_uploads_dir();
		}

		$subdir     = '/groups/' . $group->id;
		$upload_dir = array_merge(
			$upload_dir,
			array(
				'path' => $upload_dir['path'] . $subdir,
				'url'  => $upload_dir['url'] . $subdir,
			)
		);
	}

	return $upload_dir;
}
add_filter( 'bp_attachments_media_uploads_dir', 'bp_attachments_groups_media_uploads_dir', 10, 2 );

/**
 * Include groups directories to the user's root directories.
 *
 * @since 1.0.0
 *
 * @param array  $list         An empty array.
 * @param string $object_dir   An empty string or the `groups` ID to list the member's Groups Media.
 * @param array  $common_props Common properties for the directories.
 * @param int    $user_id      The user ID to return the list for.
 * @return array The user's groups directories.
 */
function bp_attachments_groups_list_member_root_objects( $list = array(), $object_dir = '', $common_props = array(), $user_id = 0 ) {
	// Fake a directory for each group the user is a member of.
	if ( 'groups' === $object_dir ) {
		$user_groups = groups_get_groups(
			array(
				'user_id'     => $user_id,
				'show_hidden' => true,
				'per_page'    => false,
			)
		);

		foreach ( $user_groups['groups'] as $group ) {
			$list[ 'group' . $group->id ] = (object) array_merge(
				array(
					'id'            => 'group-' . $group->id,
					'title'         => $group->name,
					'media_type'    => 'avatar',
					'object'        => 'groups',
					'name'          => $group->slug,
					'last_modified' => $group->date_created,
					'description'   => __( 'This directory contains the media directories attached to this group', 'bp-attachments' ),
					'icon'          => bp_core_fetch_avatar(
						array(
							'item_id' => $group->id,
							'object'  => 'group',
							'type'    => 'full',
							'html'    => false,
						)
					),
					'readonly'      => false,
					'visibility'    => 'public' === $group->status ? 'public' : 'private',
				),
				$common_props
			);
		}

		// Return a single directory to let the member access to their Groups Media.
	} else {
		$list['groups'] = (object) array_merge(
			array(
				'id'            => 'groups-' . $user_id,
				'title'         => __( 'My Groups Media', 'bp-attachments' ),
				'media_type'    => 'groups',
				'object'        => 'groups',
				'name'          => 'groups',
				'last_modified' => bp_core_current_time( true, 'timestamp' ),
				'description'   => __( 'This directory contains the media directories of the groups you are a member of.', 'bp-attachments' ),
				'icon'          => bp_attachments_get_directory_icon( 'groups' ),
				'readonly'      => true,
				'visibility'    => 'public',
			),
			$common_props
		);
	}

	return $list;
}
add_filter( 'bp_attachments_list_member_root_objects', 'bp_attachments_groups_list_member_root_objects', 10, 4 );

/**
 * Setup a group’s directory visibility.
 *
 * @since 1.0.0
 *
 * @param array  $properties {
 *     An array of arguments.
 *
 *     @type string $visibility    The directory visibility, it can be `public` or `private`. Defaults to `public`.
 *     @type int    $object_id     The component's single item ID.
 *     @type string $relative_path Relative path from the `$object` slug to the single item (it can be the object ID or slug).
 * }
 * @param string $object  The component's ID.
 * @param int    $user_id The current user ID.
 * @param array  $chunks  Parent's directory relative path chunks.
 * @return array|WP_Error Properties for the Group's directory.
 */
function bp_attachments_groups_rest_directory_visibility( $properties = array(), $object = '', $user_id = 0, $chunks = array() ) {
	if ( 'groups' !== $object || ! $user_id ) {
		return $$properties;
	}

	if ( isset( $chunks[1] ) && 'groups' === $chunks[1] && in_array( $chunks[0], array( 'private', 'public' ), true ) ) {
		$chunks = array_slice( $chunks, 2 );
	}

	$group_slug = reset( $chunks );
	$object_id  = (int) BP_Groups_Group::get_id_from_slug( $group_slug );
	$group      = groups_get_group( $object_id );

	if ( ! isset( $group->status ) || ! groups_is_user_member( $user_id, $group->id ) ) {
		return new WP_Error(
			'rest_bp_attachments_missing_group',
			__( 'The group does not exist or the user is not a member of this group.', 'bp_attachments' )
		);
	}

	if ( in_array( $group->status, array( 'hidden', 'private' ), true ) ) {
		$visibility = 'private';
	}

	return array(
		'visibility'    => $visibility,
		'object_id'     => $object_id,
		'relative_path' => $group_slug,
	);
}
add_filter( 'bp_attachments_rest_directory_visibility', 'bp_attachments_groups_rest_directory_visibility', 10, 3 );
