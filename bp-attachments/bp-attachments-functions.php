<?php
/**
 * BP Attachments Functions.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\bp-attachments-functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the plugin version.
 *
 * @since 1.0.0
 *
 * @return string The plugin version.
 */
function bp_attachments_get_version() {
	return buddypress()->attachments->version;
}

/**
 * Is this a plugin install?
 *
 * @since 1.0.0
 *
 * @return boolean True if it's an install. False otherwise.
 */
function bp_attachments_is_install() {
	return ! bp_get_option( '_bp_attachments_version' );
}

/**
 * Is this a plugin update?
 *
 * @since 1.0.0
 *
 * @return boolean True if it's an update. False otherwise.
 */
function bp_attachments_is_update() {
	$db_version = bp_get_option( '_bp_attachments_version' );
	$version    = bp_attachments_get_version();

	return version_compare( $version, $db_version, '<' );
}

/**
 * Get the media uploads dir for the requested visibility type.
 *
 * @since 1.0.0
 *
 * @param string $type `public` ou `private`.
 * @return array       The uploads dir data.
 */
function bp_attachments_get_media_uploads_dir( $type = 'public' ) {
	if ( 'public' !== $type && 'private' !== $type ) {
		$type = 'public';
	}

	$bp_uploads_dir = bp_attachments_uploads_dir_get();
	$subdir         = '/' . $type;

	$uploads = array_merge(
		$bp_uploads_dir,
		array(
			'path'   => $bp_uploads_dir['basedir'] . $subdir,
			'url'    => $bp_uploads_dir['baseurl'] . $subdir,
			'subdir' => $subdir,
			'error'  => false,
		)
	);

	unset( $uploads['dir'] );
	return $uploads;
}

/**
 * Get the root folder for private media.
 *
 * @since 1.0.0
 */
function bp_attachments_get_private_uploads_dir() {
	return bp_attachments_get_media_uploads_dir( 'private' );
}

/**
 * Get the root folder for public media.
 *
 * @since 1.0.0
 */
function bp_attachments_get_public_uploads_dir() {
	return bp_attachments_get_media_uploads_dir( 'public' );
}

/**
 * Get translated BP Attachment stati.
 *
 * @since 1.0.0
 *
 * @return array The available attachment stati.
 */
function bp_attachments_get_item_stati() {
	return array(
		'public'  => sanitize_title( _x( 'public', 'public status slug', 'bp-attachments' ) ),
		'private' => sanitize_title( _x( 'private', 'private status slug', 'bp-attachments' ) ),
	);
}

/**
 * Get translated BP Attachment objects.
 *
 * @since 1.0.0
 *
 * @return array The available attachment objects.
 */
function bp_attachments_get_item_objects() {
	return array(
		'members' => sanitize_title( _x( 'members', 'member object slug', 'bp-attachments' ) ),
		'groups'  => sanitize_title( _x( 'groups', 'group object slug', 'bp-attachments' ) ),
	);
}

/**
 * Get translated BP Attachment actions.
 *
 * @since 1.0.0
 *
 * @return array The available attachment actions.
 */
function bp_attachments_get_item_actions() {
	return array(
		'download' => sanitize_title( _x( 'download', 'download action slug', 'bp-attachments' ) ),
		'view'     => sanitize_title( _x( 'view', 'view action slug', 'bp-attachments' ) ),
	);
}

/**
 * Get the BP Attachment URI out of its path.
 *
 * @since 1.0.0
 *
 * @param string $filename The name of the BP Attachment item.
 * @param string $path     The absolute path to the BP Attachment item.
 * @return string          The BP Attachment URI.
 */
function bp_attachments_get_media_uri( $filename = '', $path = '' ) {
	$uploads   = bp_upload_dir();
	$file_path = trailingslashit( $path ) . $filename;

	if ( ! file_exists( $file_path ) ) {
		return '';
	}

	return str_replace( $uploads['basedir'], $uploads['baseurl'], $file_path );
}

/**
 * Create a Media Item.
 *
 * @since 1.0.0
 *
 * @param object $media Media Data to create the Media Item.
 * @return object       The created Media Item.
 */
function bp_attachments_create_media( $media = null ) {
	if ( ! is_object( $media ) || ! isset( $media->path ) || ! $media->path ) {
		return new WP_Error( 'bp_attachments_missing_media_path', __( 'The path to your media file is missing.', 'bp_attachments' ) );
	}

	$media_data = new SplFileInfo( $media->path );

	if ( ! $media_data->isFile() && ! $media_data->isDir() ) {
		return new WP_Error( 'bp_attachments_missing_media_path', __( 'The path to your media does not exist.', 'bp_attachments' ) );
	}

	$parent_dir = trailingslashit( dirname( $media->path ) );
	$revisions  = '';

	$media->name          = wp_basename( $media->path );
	$media->id            = md5( $media->name );
	$media->last_modified = $media_data->getMTime();

	// Set the title.
	if ( ! isset( $media->title ) || ! $media->title ) {
		$media->title = $media->name;
	}

	// Set the description.
	if ( ! isset( $media->description ) ) {
		$media->description = '';
	}

	$media->size        = '';
	$media->icon        = '';
	$media->vignette    = '';
	$media->extension   = '';
	$media->orientation = null;

	if ( ! isset( $media->mime_type ) || ! $media->mime_type ) {
		$media->mime_type = '';
	}

	if ( $media_data->isFile() ) {
		$media->type      = 'file';
		$media->extension = $media_data->getExtension();

		if ( $media->title === $media->name ) {
			$media->title = wp_basename( $media->name, ".{$media->extension}" );
		}

		$media->media_type = wp_ext2type( $media->extension );
		$media->size       = $media_data->getSize();

		if ( 'image' === $media->media_type ) {
			$media->vignette        = bp_attachments_get_media_uri( $media->name, untrailingslashit( $parent_dir ) );
			list( $width, $height ) = getimagesize( trailingslashit( $parent_dir ) . $media->name );

			if ( $width > $height ) {
				$media->orientation = 'landscape';
			} else {
				$media->orientation = 'portrait';
			}
		}

		$revisions = $parent_dir . '._revisions_' . $media->id;
	} else {
		$media->type      = 'directory';
		$media->mime_type = 'inode/directory';

		if ( ! isset( $media->media_type ) || ! $media->media_type ) {
			$media->media_type = 'folder';
		}
	}

	unset( $media->path );
	$media = bp_attachments_sanitize_media( $media );

	// Create the JSON data file.
	if ( ! file_exists( $parent_dir . $media->id . '.json' ) ) {
		file_put_contents( $parent_dir . $media->id . '.json', wp_json_encode( $media ) ); // phpcs:ignore
	}

	// Create the revisions directory.
	if ( $revisions && ! is_dir( $revisions ) ) {
		mkdir( $revisions );
	}

	return $media;
}

/**
 * List all media items (including sub-directories) of a directory.
 *
 * Not Used anymore.
 *
 * @todo delete.
 *
 * @since 1.0.0
 *
 * @param string $dir Absolute path to the directory to list media items for.
 * @return array      The list of media items found.
 */
function bp_attachments_list_dir_media( $dir = '' ) {
	$list = array();

	if ( ! is_dir( $dir ) ) {
		return $list;
	}

	$iterator = new FilesystemIterator( $dir, FilesystemIterator::SKIP_DOTS );

	foreach ( new BP_Attachments_Filter_Iterator( $iterator ) as $media ) {
		$media_name = $media->getfilename();
		$path       = $media->getPathname();
		$id         = md5( $media_name );
		$list[]     = (object) array(
			'id'                 => $id,
			'path'               => $path,
			'name'               => $media_name,
			'size'               => $media->getSize(),
			'type'               => $media->getType(),
			'mime_type'          => mime_content_type( $path ),
			'last_modified'      => $media->getMTime(),
			'latest_access_date' => $media->getATime(),
		);
	}

	return $list;
}

/**
 * List all media items (including sub-directories) of a directory.
 *
 * @since 1.0.0
 *
 * @param string $dir Absolute path to the directory to list media items for.
 * @return array      The list of media items found.
 */
function bp_attachments_list_media_in_directory( $dir = '' ) {
	$list = array();

	if ( ! is_dir( $dir ) ) {
		return $list;
	}

	$iterator = new FilesystemIterator( $dir, FilesystemIterator::SKIP_DOTS );

	/**
	 * Some of the properties should have been created during the upload/make dir process.
	 *
	 * @todo Add checks to avoid running some functions when not necessary.
	 */
	foreach ( new BP_Attachments_Filter_Iterator( $iterator ) as $media ) {
		$json_data                 = file_get_contents( $media ); // phpcs:ignore
		$media_data                = json_decode( $json_data );
		$media_data->last_modified = $media->getMTime();
		$media_data->extension     = preg_replace( '/^.+?\.([^.]+)$/', '$1', $media_data->name );

		if ( ! isset( $media_data->media_type ) || ! $media_data->media_type ) {
			$media_data->media_type = wp_ext2type( $media_data->extension );
		}

		// Add the icon.
		if ( 'inode/directory' !== $media_data->mime_type ) {
			$media_data->icon = wp_mime_type_icon( $media_data->media_type );
		} else {
			$media_data->icon = bp_attachments_get_directory_icon( $media_data->media_type );
		}

		// Vignette & orientation are only used for images.
		$media_data->vignette    = '';
		$media_data->orientation = null;

		if ( 'image' === $media_data->media_type ) {
			$media_data->vignette   = bp_attachments_get_media_uri( $media_data->name, $dir );
			list( $width, $height ) = getimagesize( trailingslashit( $dir ) . $media_data->name );

			if ( $width > $height ) {
				$media_data->orientation = 'landscape';
			} else {
				$media_data->orientation = 'portrait';
			}
		}

		// Merge all JSON data of the directory.
		$list[] = $media_data;
	}

	return $list;
}

/**
 * Returns the common properties of a directory.
 *
 * @since 1.0.0
 *
 * @return array The common properties of a directory.
 */
function bp_attachments_get_directory_common_props() {
	return array(
		'size'        => '',
		'vignette'    => '',
		'extension'   => '',
		'orientation' => null,
		'mime_type'   => 'inode/directory',
		'type'        => 'directory',
	);
}

/**
 * Returns the list of supported directory types.
 *
 * @since 1.0.0
 *
 * @return array The list of supported directory types.
 */
function bp_attachments_get_directory_types() {
	$current_time = bp_core_current_time( true, 'timestamp' );
	$common_props = bp_attachments_get_directory_common_props();

	$private_dir = (object) array_merge(
		array(
			'id'            => 'private-' . $user_id,
			'title'         => __( 'Private', 'bp-attachments' ),
			'media_type'    => 'private',
			'name'          => 'private',
			'last_modified' => $current_time,
			'description'   => __( 'This Private directory and its children are only visible to logged in users.', 'bp-attachments' ),
			'icon'          => bp_attachments_get_directory_icon( 'private' ),
		),
		$common_props
	);

	$public_dir = (object) array_merge(
		array(
			'id'            => 'public-' . $user_id,
			'title'         => __( 'Public', 'bp-attachments' ),
			'media_type'    => 'public',
			'name'          => 'public',
			'last_modified' => $current_time,
			'description'   => __( 'This Public directory and its children are visible to everyone.', 'bp-attachments' ),
			'icon'          => bp_attachments_get_directory_icon( 'public' ),
		),
		$common_props
	);

	return array( $private_dir, $public_dir );
}

/**
 * Returns the user's root directories.
 *
 * @since 1.0.0
 *
 * @param integer $user_id    The ID of the user.
 * @param integer $object_dir The requested object directory.
 *                            Possible values are `groups` & `member`.
 * @return array The user's root directories.
 */
function bp_attachments_list_member_root_objects( $user_id = 0, $object_dir = '' ) {
	$list         = array();
	$current_time = bp_core_current_time( true, 'timestamp' );
	$common_props = bp_attachments_get_directory_common_props();

	if ( ! $user_id ) {
		$user_id = bp_loggedin_user_id();
	}

	$user_id = (int) $user_id;

	if ( ! bp_is_active( 'groups' ) || in_array( $object_dir, array( 'member', 'groups' ), true ) ) {
		// Get the directory types for the member.
		if ( 'groups' !== $object_dir ) {
			$list = bp_attachments_get_directory_types();
		} else {
			/**
			 * Get the list of groups the current user is a member of.
			 *
			 * @todo
			 */
			$list = array();
		}
	} else {
		$list['groups'] = (object) array_merge(
			array(
				'id'            => 'groups-' . $user_id,
				'title'         => __( 'My Groups', 'bp-attachments' ),
				'media_type'    => 'groups',
				'name'          => 'groups',
				'last_modified' => $current_time,
				'description'   => __( 'This directory contains the media directories of the groups you are a member of.', 'bp-attachments' ),
				'icon'          => bp_attachments_get_directory_icon( 'groups' ),
			),
			$common_props
		);

		$list['member'] = (object) array_merge(
			array(
				'id'            => 'member-' . $user_id,
				'title'         => __( 'My Media', 'bp-attachments' ),
				'media_type'    => 'avatar',
				'name'          => 'member',
				'last_modified' => $current_time,
				'description'   => __( 'This directory contains all your personal media.', 'bp-attachments' ),
				'icon'          => bp_core_fetch_avatar(
					array(
						'item_id' => $user_id,
						'object'  => 'user',
						'type'    => 'full',
						'html'    => false,
					)
				),
			),
			$common_props
		);
	}

	return $list;
}

/**
 * Sanitize a BP Attachment media.
 *
 * @since 1.0.0
 *
 * @param object $media The BP Attachment media to sanitize.
 * @return object|null  The sanitized BP Attachment media.
 */
function bp_attachments_sanitize_media( $media = null ) {
	if ( ! is_object( $media ) ) {
		return null;
	}

	if ( ! isset( $media->id ) ) {
		return null;
	}

	foreach ( array_keys( get_object_vars( $media ) ) as $property ) {
		if ( in_array( $property, array( 'id', 'title', 'type' ), true ) ) {
			$media->{$property} = sanitize_text_field( $media->{$property} );
		} elseif ( 'description' === $property ) {
			$media->{$property} = sanitize_textarea_field( $media->{$property} );
		} elseif ( 'mime_type' === $property ) {
			$media->{$property} = sanitize_mime_type( $media->{$property} );
		} elseif ( 'name' === $property ) {
			$media->{$property} = sanitize_file_name( $media->{$property} );
		}
	}

	return $media;
}

/**
 * Checks if a file type is allowed.
 *
 * @since 1.0.0
 *
 * @param string $file     Full path to the file.
 * @param string $filename The name of the file (may differ from $file due to $file being
 *                         in a tmp directory).
 * @return boolean         True if allowed. False otherwise.
 */
function bp_attachments_is_file_type_allowed( $file, $filename ) {
	$allowed_types = bp_attachments_get_allowed_types( '' );
	$wp_filetype   = wp_check_filetype_and_ext( $file, $filename );

	if ( ! isset( $wp_filetype['ext'] ) || ! $wp_filetype['ext'] ) {
		return false;
	}

	return in_array( $wp_filetype['ext'], $allowed_types, true );
}

/**
 * Get the directory icon according to its type.
 *
 * @since 1.0.0
 *
 * @param string $type The type of the directory. Defauts to `folder`.
 *                     Possible values are: `folder`, `album`, `audio_playlist`, `video_playlist`,
 *                     `groups`, `members`, `public`, `private`.
 * @return string      The URL to the icon.
 */
function bp_attachments_get_directory_icon( $type = 'folder' ) {
	$svg = 'default';

	if ( 'album' === $type ) {
		$svg = 'photo';
	} elseif ( 'audio_playlist' === $type ) {
		$svg = 'audio';
	} elseif ( 'video_playlist' === $type ) {
		$svg = 'video';
	} elseif ( in_array( $type, array( 'groups', 'members', 'public', 'private' ), true ) ) {
		$svg = $type;
	}

	return trailingslashit( buddypress()->attachments->assets_url ) . 'images/' . $svg . '.svg';
}
