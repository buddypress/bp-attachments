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
			'id'                   => $id,
			'path'                 => $path,
			'name'                 => $media_name,
			'size'                 => $media->getSize(),
			'type'                 => $media->getType(),
			'mime_type'            => mime_content_type( $path ),
			'latest_modified_date' => $media->getMTime(),
			'latest_access_date'   => $media->getATime(),
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

	foreach ( new BP_Attachments_Filter_Iterator( $iterator ) as $media ) {
		$json_data                        = file_get_contents( $media ); // phpcs:ignore
		$media_data                       = json_decode( $json_data );
		$media_data->latest_modified_date = $media->getMTime();
		$media_data->extension            = preg_replace( '/^.+?\.([^.]+)$/', '$1', $media_data->name );
		$media_data->media_type           = wp_ext2type( $media_data->extension );
		$media_data->icon                 = wp_mime_type_icon( $media_data->media_type );
		$media_data->vignette             = '';
		$media_data->orientation          = null;

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
