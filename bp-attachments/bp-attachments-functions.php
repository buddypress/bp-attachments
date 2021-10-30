<?php
/**
 * BP Attachments Functions.
 *
 * @package \bp-attachments\bp-attachments-functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Checks whether users can upload private attachments.
 *
 * @since 1.0.0
 *
 * @return boolean True if users can upload private attachments. False otherwise.
 */
function bp_attachments_can_do_private_uploads() {
	return (bool) bp_get_option( '_bp_attachments_can_upload_privately', false );
}

/**
 * Include the Attachments component to BuddyPress ones.
 *
 * @since 1.0.0
 *
 * @param array $components The list of available BuddyPress components.
 * @return array            The list of available BuddyPress components, including the Attachments one.
 */
function bp_attachments_get_component_info( $components = array() ) {
	return array_merge(
		$components,
		array(
			'attachments' => array(
				'title'       => __( 'Attachments', 'bp-attachments' ),
				'description' => __( 'Empower your community with user generated media.', 'bp-attachments' ),
			),
		)
	);
}

/**
 * Returns the server's document root.
 *
 * @since 1.0.0
 *
 * @return string The server's document root.
 */
function bp_attachments_get_document_root() {
	$document_root = '';
	if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
		$document_root = realpath( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ); // phpcs:ignore
	}

	return $document_root;
}

/**
 * Gets the root directory of private uploads.
 *
 * @since 1.0.0
 *
 * @param boolean $upload_checks True to perform upload checks. False otherwise.
 * @return string|WP_Error The private root directory if it exists a WP Error object otherwise.
 */
function bp_attachments_get_private_root_dir( $upload_checks = true ) {
	$document_root = bp_attachments_get_document_root();

	if ( ! $document_root ) {
		return new WP_Error(
			'missing_document_root_information',
			__( 'The server’s document root is missing.', 'bp-attachments' )
		);
	}

	$private_dir = trailingslashit( dirname( $document_root ) ) . 'buddypress-private';

	if ( ! is_dir( $private_dir ) ) {
		return new WP_Error(
			'missing_private_root_directory',
			__( 'The private root directory is missing.', 'bp-attachments' )
		);
	}

	if ( ! $upload_checks ) {
		if ( ! is_writeable( $private_dir ) ) {
			return new WP_Error(
				'private_root_directory_not_writeable',
				__( 'The private root directory is not writeable.', 'bp-attachments' )
			);
		}

		return $private_dir;
	}

	// Get the Uploads directory information.
	$wp_uploads      = wp_get_upload_dir();
	$upload_dir_info = array(
		'gid'   => filegroup( $wp_uploads['path'] ),
		'uid'   => fileowner( $wp_uploads['path'] ),
		'perms' => fileperms( $wp_uploads['path'] ),
	);

	// Get the private root dir information.
	$private_dir_info = array(
		'gid'   => filegroup( $private_dir ),
		'uid'   => fileowner( $private_dir ),
		'perms' => fileperms( $private_dir ),
	);

	$diff = array_diff( $upload_dir_info, $private_dir_info );

	if ( $diff ) {
		return new WP_Error(
			'private_root_directory_wrong_permissions',
			__( 'The private root directory is not set the right way.', 'bp-attachments' ),
			$diff
		);
	}

	return $private_dir;
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

	if ( 'private' === $type && bp_attachments_can_do_private_uploads() ) {
		$private_dir = bp_attachments_get_private_root_dir( false );

		if ( is_wp_error( $private_dir ) ) {
			$subdir = '/public';
		} else {
			$bp_uploads_dir['basedir'] = $private_dir;
			$bp_uploads_dir['baseurl'] = '';
			$subdir                    = '';
		}
	}

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
 * Get the vignette URI of an image attachment out of its path.
 *
 * @since 1.0.0
 *
 * @param string $filename The name of the BP Attachment item.
 * @param string $path     The absolute path to the BP Attachment item.
 * @return string          The BP Attachment URI.
 */
function bp_attachments_get_vignette_uri( $filename = '', $path = '' ) {
	$uploads   = bp_upload_dir();
	$file_path = trailingslashit( $path ) . $filename;

	if ( ! file_exists( $file_path ) || 0 !== strpos( $file_path, WP_CONTENT_DIR ) ) {
		return trailingslashit( buddypress()->attachments->assets_url ) . 'images/image.png';
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

	// Set the owner ID.
	if ( ! isset( $media->owner_id ) || ! $media->owner_id ) {
		$media->owner_id = bp_loggedin_user_id();
	}

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
			$media->vignette        = bp_attachments_get_vignette_uri( $media->name, untrailingslashit( $parent_dir ) );
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
 * @param string $dir    Absolute path to the directory to list media items for.
 * @param string $object The type of object being listed. Possible values are `members` or `groups`.
 * @return array         The list of media items found.
 */
function bp_attachments_list_media_in_directory( $dir = '', $object = 'members' ) {
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
			$media_data->vignette   = bp_attachments_get_vignette_uri( $media_data->name, $dir );
			list( $width, $height ) = getimagesize( trailingslashit( $dir ) . $media_data->name );

			if ( $width > $height ) {
				$media_data->orientation = 'landscape';
			} else {
				$media_data->orientation = 'portrait';
			}
		}

		// Set the object type of the media.
		$media_data->object = $object;

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
 * @param integer $user_id The user ID.
 * @return array The list of supported directory types.
 */
function bp_attachments_get_directory_types( $user_id = 0 ) {
	$current_time    = bp_core_current_time( true, 'timestamp' );
	$common_props    = bp_attachments_get_directory_common_props();
	$directory_types = array();

	$directory_types[] = (object) array_merge(
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

	if ( bp_attachments_can_do_private_uploads() ) {
		$directory_types[] = (object) array_merge(
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
	}

	/**
	 * Filter here to add/remove directory types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $directory_types The list of directory type objects.
	 */
	return apply_filters( 'bp_attachments_get_directory_types', $directory_types );
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
			$list = bp_attachments_get_directory_types( $user_id );

			// Get the groups the user is a member of.
		} else {
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
					),
					$common_props
				);
			}
		}
	} else {
		$list['groups'] = (object) array_merge(
			array(
				'id'            => 'groups-' . $user_id,
				'title'         => __( 'My Groups', 'bp-attachments' ),
				'media_type'    => 'groups',
				'object'        => 'groups',
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
 * Translates the media types.
 *
 * @since 1.0.0
 *
 * @param string $media_type A specific Media Type.
 * @return string|array      A translated Media Type or a translated list of Media Types.
 */
function bp_attachments_get_i18n_media_type( $media_type = '' ) {
	$i18n_media_types = array(
		'image'       => __( 'Image', 'bp-attachments' ),
		'video'       => __( 'Movie', 'bp-attachments' ),
		'audio'       => __( 'Sound', 'bp-attachments' ),
		'document'    => __( 'Document', 'bp-attachments' ),
		'spreadsheet' => __( 'Spreadsheet', 'bp-attachments' ),
		'interactive' => __( 'Presentation', 'bp-attachments' ),
		'text'        => __( 'Text', 'bp-attachments' ),
		'archive'     => __( 'Archive', 'bp-attachments' ),
	);

	if ( is_array( $media_type ) ) {
		return array_intersect_key( $i18n_media_types, $media_type );
	}

	if ( isset( $i18n_media_types[ $media_type ] ) ) {
		return $i18n_media_types[ $media_type ];
	}

	return $media_type;
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

/**
 * Deletes a directory and its content.
 *
 * @since 1.0.0
 *
 * @param string $path       The absolute path of the directory.
 * @param string $visibility The file visibility. Default `public`.
 * @return boolean True on success. False otherwise.
 */
function bp_attachments_delete_directory( $path = '', $visibility = 'public' ) {
	$result  = false;
	$uploads = bp_attachments_get_media_uploads_dir( $visibility );

	// Make sure the directory exists and that we are deleting a subdirectory of the BP Uploads.
	if ( ! is_dir( $path ) || 0 !== strpos( $path, $uploads['path'] ) ) {
		return $result;
	}

	// Make sure the path to delete is safe.
	$relative_path = trim( str_replace( $uploads['basedir'], '', $path ), '/' );
	$path_parts    = explode( '/', $relative_path );

	// The Private root directory has no visibility/type subdirectory.
	if ( 'private' === $visibility ) {
		array_unshift( $path_parts, 'buddypress-private' );
	}

	$has_type_dir   = isset( $path_parts[0] ) && in_array( $path_parts[0], array( 'public', 'buddypress-private' ), true );
	$has_object_dir = isset( $path_parts[1] ) && in_array( $path_parts[1], array( 'members', 'groups' ), true );
	$has_item_dir   = isset( $path_parts[2] ) && !! (int) $path_parts[2]; // phpcs:ignore
	$has_subdir     = isset( $path_parts[3] ) && $path_parts[3];

	if ( ! $has_type_dir || ! $has_object_dir || ! $has_item_dir || ! $has_subdir ) {
		return $result;
	}

	$result    = true;
	$directory = new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS );
	$iterator  = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $iterator as $item ) {
		if ( false === $result ) {
			break;
		}

		if ( $item->isDir() ) {
			$result = rmdir( $item->getRealPath() );
		} else {
			$result = unlink( $item->getRealPath() );
		}
	}

	return rmdir( $path );
}
