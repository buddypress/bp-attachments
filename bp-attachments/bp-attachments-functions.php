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
 * Retuns the default list of allowed mime types for each media type.
 *
 * @since 1.0.0
 *
 * @return array The default list of allowed mime types for each media type.
 */
function bp_attachments_get_default_allowed_media_types() {
	return array(
		'image'    => array(
			'image/jpeg',
			'image/gif',
			'image/png',
		),
		'video'    => array(
			'video/mp4',
			'video/ogg',
		),
		'audio'    => array(
			'audio/mpeg',
			'audio/ogg',
		),
		'document' => array(
			'application/pdf',
			'application/rtf',
		),
		'archive'  => array(
			'application/zip',
		),
	);
}

/**
 * Returns the list of allowed mime types (`image/jpeg`, `video/mp4`, etc.) for each media type (`image`, `video`, etc.).
 *
 * @since 1.0.0
 *
 * @return array The list of allowed mime types for each media type.
 */
function bp_attachments_get_allowed_media_types() {
	return get_option( '_bp_attachments_allowed_media_types', bp_attachments_get_default_allowed_media_types() );
}

/**
 * Returns the list of allowed media extensions.
 *
 * @since 1.0.0
 *
 * @param string $media_type One of 'image', 'video', 'audio', 'document', 'archive'.
 *                           Default to empty string (all media types). Optional.
 * @param bool   $csv        True to get a comma separated list. False to get an array.
 * @return array|string The list of allowed media extensions.
 */
function bp_attachments_get_allowed_media_exts( $media_type = '', $csv = false ) {
	$exts_list                = array();
	$wp_ext_types             = wp_get_ext_types();
	$wp_mimes                 = wp_get_mime_types();
	$allowed_media_mime_types = bp_attachments_get_allowed_media_types();

	if ( $media_type && isset( $allowed_media_mime_types[ $media_type ] ) ) {
		$allowed_media_mime_types = array_intersect_key(
			$allowed_media_mime_types,
			array( $media_type => array() )
		);
	}

	foreach ( $allowed_media_mime_types as $mime_key => $mime_types ) {
		if ( ! isset( $wp_ext_types[ $mime_key ] ) ) {
			continue;
		}

		$mimes = array_intersect( $wp_mimes, $mime_types );
		foreach ( $wp_ext_types[ $mime_key ] as $ext ) {
			$ext_type = wp_check_filetype( 'name.' . $ext, $mimes );

			if ( ! isset( $ext_type['ext'] ) || ! $ext_type['ext'] ) {
				continue;
			}

			$exts_list[] = $ext_type['ext'];
		}
	}

	if ( $csv ) {
		return '.' . implode( ',.', $exts_list );
	}

	return $exts_list;
}

/**
 * Returns the list of media extensions by allowed types.
 *
 * @since 1.0.0
 *
 * @param bool $csv True to get a comma separated list. False to get an array.
 * @return array The list of media extensions by allowed types.
 */
function bp_attachments_get_media_exts_by_allowed_types( $csv = false ) {
	$types                 = array_keys( bp_attachments_get_allowed_media_types() );
	$allowed_exts_by_types = array();

	foreach ( $types as $type ) {
		$allowed_exts_by_types[ $type ] = bp_attachments_get_allowed_media_exts( $type, $csv );
	}

	return $allowed_exts_by_types;
}

/**
 * Returns the list of allowed media extensions by media list.
 *
 * @since 1.0.0
 *
 * @return array The list of allowed media extensions by media list.
 */
function bp_attachments_get_exts_by_medialist() {
	$allowed_exts_by_types     = bp_attachments_get_media_exts_by_allowed_types( true );
	$allowed_exts_by_medialist = array();

	foreach ( $allowed_exts_by_types as $type => $allowed_exts ) {
		if ( 'document' === $type || 'archive' === $type ) {
			continue;
		}

		$medialist = '';
		if ( 'image' === $type ) {
			$medialist = 'album';
		} else {
			$medialist = $type . '_playlist';
		}

		$allowed_exts_by_medialist[ $medialist ] = $allowed_exts;
	}

	return $allowed_exts_by_medialist;
}

/**
 * Returns the list of allowed media mime types.
 *
 * @since 1.0.0
 *
 * @param string $media_type One of 'image', 'video', 'audio', 'document', 'archive'.
 *                           Default to empty string (all media types). Optional.
 * @return array The list of allowed media mime types.
 */
function bp_attachments_get_allowed_media_mimes( $media_type = '' ) {
	$mimes_list               = array();
	$allowed_media_mime_types = bp_attachments_get_allowed_media_types();

	if ( $media_type && isset( $allowed_media_mime_types[ $media_type ] ) ) {
		$allowed_media_mime_types = array_intersect_key(
			$allowed_media_mime_types,
			array( $media_type => array() )
		);
	}

	foreach ( $allowed_media_mime_types as $mime_types ) {
		$mimes_list = array_merge( $mimes_list, $mime_types );
	}

	return $mimes_list;
}

/**
 * Checks if a file type is allowed.
 *
 * @since 1.0.0
 *
 * @param string $file       Full path to the file.
 * @param string $filename   The name of the file (may differ from $file due to $file being
 *                           in a tmp directory).
 * @param string $media_type One of 'image', 'video', 'audio', 'document', 'archive'.
 *                           Default to empty string (all media types). Optional.
 * @return boolean True if allowed. False otherwise.
 */
function bp_attachments_is_file_type_allowed( $file, $filename, $media_type = '' ) {
	$allowed_types = bp_attachments_get_allowed_media_exts( $media_type );
	$wp_filetype   = wp_check_filetype_and_ext( $file, $filename );

	if ( ! isset( $wp_filetype['ext'] ) || ! $wp_filetype['ext'] ) {
		return false;
	}

	return in_array( $wp_filetype['ext'], $allowed_types, true );
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
	/**
	 * Use this filter to override the private uploads root directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $value The private uploads root directory absolute path. Default `null`.
	 */
	$override = apply_filters( 'bp_attachments_pre_get_private_root_dir', null );
	if ( null !== $override ) {
		return $override;
	}

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

	if ( 'private' === $type ) {
		if ( bp_attachments_can_do_private_uploads() ) {
			$private_dir = bp_attachments_get_private_root_dir( false );

			if ( is_wp_error( $private_dir ) ) {
				$bp_uploads_dir['bp_attachments_error_code'] = 16;
			} else {
				$bp_uploads_dir['basedir'] = $private_dir;
				$bp_uploads_dir['baseurl'] = '';
				$subdir                    = '';
			}
		} else {
			$bp_uploads_dir['bp_attachments_error_code'] = 18;
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
 * Get translated BP Attachments stati.
 *
 * @since 1.0.0
 *
 * @return array The available attachments stati.
 */
function bp_attachments_get_item_stati() {
	return array(
		'public'  => sanitize_title( _x( 'public', 'public status slug', 'bp-attachments' ) ),
		'private' => sanitize_title( _x( 'private', 'private status slug', 'bp-attachments' ) ),
	);
}

/**
 * Get translated BP Attachments objects slugs.
 *
 * @since 1.0.0
 *
 * @return array The available attachments objects slugs.
 */
function bp_attachments_get_item_object_slugs() {
	/**
	 * Filter here to add custom objects slugs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value An assiociative array keyed with component names where the value is the object slug.
	 */
	return apply_filters(
		'bp_attachments_get_item_object_slugs',
		array(
			'members' => sanitize_title( _x( 'members', 'member object slug', 'bp-attachments' ) ),
		)
	);
}

/**
 * Get translated BP Attachments actions.
 *
 * @since 1.0.0
 *
 * @return array The available attachments actions.
 */
function bp_attachments_get_item_actions() {
	return array(
		'download' => sanitize_title( _x( 'download', 'download action slug', 'bp-attachments' ) ),
		'view'     => sanitize_title( _x( 'view', 'view action slug', 'bp-attachments' ) ),
		'embed'    => sanitize_title( _x( 'embed', 'embed action slug', 'bp-attachments' ) ),
	);
}

/**
 * Get a translated BP Attachments action slug.
 *
 * @since 1.0.0
 *
 * @param string $action_key The BP Attachments action key.
 * @return string The translated BP Attachments action slug.
 */
function bp_attachments_get_item_action_slug( $action_key = '' ) {
	$slug    = '';
	$actions = bp_attachments_get_item_actions();

	if ( isset( $actions[ $action_key ] ) ) {
		$slug = $actions[ $action_key ];
	}

	return $slug;
}

/**
 * Get a BP Attachments action key.
 *
 * @since 1.0.0
 *
 * @param string $action_slug The BP Attachments action slug.
 * @return string The translated BP Attachments action key.
 */
function bp_attachments_get_item_action_key( $action_slug = '' ) {
	$key     = '';
	$actions = array_flip( bp_attachments_get_item_actions() );

	if ( isset( $actions[ $action_slug ] ) ) {
		$key = $actions[ $action_slug ];
	}

	return $key;
}

/**
 * Get the `src` attribute of a media out of its path.
 *
 * @since 1.0.0
 *
 * @param string $filename The name of the BP Attachments item.
 * @param string $path     The absolute path to the BP Attachments item.
 * @return string          The BP Attachments item `src` attribute.
 */
function bp_attachments_get_src( $filename = '', $path = '' ) {
	$src       = '';
	$uploads   = bp_upload_dir();
	$file_path = trailingslashit( $path ) . $filename;

	if ( file_exists( $file_path ) && 0 === strpos( $file_path, WP_CONTENT_DIR ) ) {
		$src = str_replace( $uploads['basedir'], $uploads['baseurl'], $file_path );
	}

	return $src;
}

/**
 * Get the vignette URI of an image attachment out of its path.
 *
 * @since 1.0.0
 *
 * @param string $filename The name of the BP Attachments item.
 * @param string $path     The absolute path to the BP Attachments item.
 * @return string          The BP Attachments item URI.
 */
function bp_attachments_get_vignette_uri( $filename = '', $path = '' ) {
	$vignette_uri = bp_attachments_get_src( $filename, $path );

	if ( ! $vignette_uri ) {
		$vignette_uri = trailingslashit( buddypress()->attachments->assets_url ) . 'images/image.png';
	}

	return $vignette_uri;
}

/**
 * Builds the Attachments medium URL.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Associative array of arguments list used to buil a medium action URL.
 *
 *     @type string $status                The medium visibility, it can be `public` or `private`. Defaults to `public`.
 *     @type string $object                The BuddyPress object the medium relates to. Defaults to `members`.
 *     @type string $object_item           The object item slug (eg: the user's user_nicename).
 *     @type string $item_action           The needed action. One of the slugs for the `view `, `embed`, `download` action keys.
 *     @type array  $item_action_variables An array of relative path chunks.
 * }
 * @return string URL for the Attachments medium.
 */
function bp_attachments_get_medium_url( $args = array() ) {
	$bp   = buddypress();
	$link = '#';

	$r = wp_parse_args(
		$args,
		array(
			'status'                => 'public',
			'object'                => '',
			'object_item'           => '',
			'item_action'           => '',
			'item_action_variables' => array(),
		)
	);

	$are_urls_plain = ! get_option( 'permalink_structure', '' );
	$r              = array_filter( $r );

	// Using plain links.
	if ( $are_urls_plain ) {
		$qv = array();

		foreach ( $bp->attachments->rewrite_ids as $rewrite_id ) {
			$key = str_replace( 'bp_attachments_', '', $rewrite_id );

			if ( ! isset( $r[ $key ] ) ) {
				continue;
			}

			$qv[ $rewrite_id ] = $r[ $key ];
		}

		$qv   = array_merge( array( 'bp_attachments' => 1 ), $qv );
		$link = add_query_arg( $qv, home_url( '/' ) );

		// Using pretty URLs.
	} else {
		$link = str_replace( '%' . $bp->attachments->rewrite_ids['directory'] . '%', $r['status'], $bp->attachments->directory_permastruct );

		$item_action_variables = (array) $r['item_action_variables'];
		unset( $r['status'], $r['item_action_variables'] );

		$r = array_merge( $r, $item_action_variables );

		$link = home_url( user_trailingslashit( '/' . rtrim( $link, '/' ) . '/' . join( '/', $r ) ) );
	}

	return $link;
}

/**
 * Create a Media Item.
 *
 * @since 1.0.0
 *
 * @todo this function should use `bp_attachments_get_medium()` to be sure to set all medium properties.
 *
 * @param object $media Media Data to create the Media Item.
 * @return BP_Medium    The created Media Item.
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

	return bp_attachments_get_medium( $media->id, $parent_dir );
}

/**
 * List all media items (including sub-directories) of a directory.
 *
 * Not Used anymore.
 *
 * @todo Check it can be used or not for search and eventually keep ot delete.
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
		$mime_type  = bp_attachements_get_mime_type( $path );

		if ( 'directory' === $mime_type ) {
			$mime_type = 'inode/directory';
		}

		$list[] = (object) array(
			'id'                 => $id,
			'path'               => $path,
			'name'               => $media_name,
			'size'               => $media->getSize(),
			'type'               => $media->getType(),
			'mime_type'          => $mime_type,
			'last_modified'      => $media->getMTime(),
			'latest_access_date' => $media->getATime(),
		);
	}

	return $list;
}

/**
 * Use the medium's parent dir to set its visibility.
 *
 * @since 1.0.0
 *
 * @param string $parent_dir The absolute path of the medium’s parent directory.
 * @return string The medium's visibility (`public` or  `private`).
 */
function bp_attachments_get_medium_visibility( $parent_dir = '' ) {
	$visibility = 'public';

	if ( ! is_dir( $parent_dir ) ) {
		return $visibility;
	}

	if ( bp_attachments_can_do_private_uploads() ) {
		$private_basedir = bp_attachments_get_private_root_dir();

		if ( 0 === strpos( $parent_dir, $private_basedir ) ) {
			$visibility = 'private';
		}
	}

	return $visibility;
}

/**
 * Get a Medium.
 *
 * @since 1.0.0
 *
 * @param string $id   The ID of the medium to retrieve.
 * @param string $path The absolute's path of the medium to retrieve.
 * @return BP_Medium|false The medium's object if found. False if not.
 */
function bp_attachments_get_medium( $id = '', $path = '' ) {
	return BP_Medium::get_instance( $id, $path );
}

/**
 * List all media items (including sub-directories) of a directory.
 *
 * @since 1.0.0
 *
 * @param string $dir    Absolute path to the directory to list media items for.
 * @param string $object The type of object being listed.
 *                       Default `members`.
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
	foreach ( new BP_Attachments_Filter_Iterator( $iterator ) as $medium ) {
		$medium_id = wp_basename( $medium, '.json' );

		// Merge all medium into the directory.
		$list[] = bp_attachments_get_medium( $medium_id, $dir );
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
			'readonly'      => false,
			'visibility'    => 'public',
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
				'readonly'      => false,
				'visibility'    => 'private',
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

	if ( 'member' !== $object_dir ) {
		/**
		 * Use this filter to define the object's directories to display.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $list         An empty array. If the `$object_dir` is an empty string, return a single directory
		 *                             to let the member access to their component items.
		 * @param string $object_dir   An empty string or the ID you chose to list the member's component items.
		 * @param array  $common_props Common properties for the directories.
		 * @param int    $user_id      The user ID to return the list for.
		 */
		$list = apply_filters(
			'bp_attachments_list_member_root_objects',
			$list,
			$object_dir,
			$common_props,
			$user_id
		);
	}

	if ( ! $list || 'member' === $object_dir ) {
		$list = bp_attachments_get_directory_types( $user_id );
	} elseif ( ! $object_dir ) {
		// Fake a directory to let the member access to their media.
		$list['member'] = (object) array_merge(
			array(
				'id'            => 'member-' . $user_id,
				'title'         => __( 'My Media', 'bp-attachments' ),
				'media_type'    => 'avatar',
				'name'          => 'member',
				'last_modified' => $current_time,
				'description'   => __( 'This directory contains all your personal media.', 'bp-attachments' ),
				'icon'          => bp_attachments_get_directory_icon( 'member' ),
				'readonly'      => true,
				'visibility'    => 'public',
			),
			$common_props
		);
	}

	return $list;
}

/**
 * Sanitize a BP Attachments media.
 *
 * @since 1.0.0
 *
 * @param object $media The BP Attachments media to sanitize.
 * @return object|null  The sanitized BP Attachments media.
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
		} elseif ( 'attached_to' === $property ) {
			$sanitized_attached_to = array();

			foreach ( $media->{$property} as $attached_to ) {
				$sanitized_attached_to[] = (object) array(
					'object_type' => sanitize_key( $attached_to->object_type ),
					'object_id'   => intval( $attached_to->object_id ),
				);
			}

			$media->{$property} = $sanitized_attached_to;
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
	} elseif ( in_array( $type, array( 'groups', 'member', 'members', 'friends', 'public', 'private' ), true ) ) {
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

/**
 * Gets the queried User Media object when it's being viewed, downloaded, embbeded.
 *
 * @since 1.0.0
 *
 * @return object The queried User Media object.
 */
function bp_attachments_get_queried_object() {
	$bp     = buddypress();
	$object = null;

	if ( $bp->attachments->queried_object ) {
		$object = $bp->attachments->queried_object;
	}

	return $object;
}
