<?php
/**
 * BP Attachments Media.
 *
 * @package \bp-attachments\classes\class-bp-attachments-media
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BP Attachments Media Class.
 *
 * Creates a file or a directory on the server filesystem.
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
class BP_Attachments_Media extends BP_Attachment {
	/**
	 * The constuctor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Max upload size.
		$max_upload_file_size = bp_attachments_get_max_upload_file_size( 'media' );

		parent::__construct(
			array(
				'action'                => 'bp_attachments_media_upload',
				'file_input'            => 'file',
				'original_max_filesize' => $max_upload_file_size,
				'base_dir'              => bp_attachments_uploads_dir_get( 'dir' ),
				'required_wp_files'     => array( 'file' ),
				'allowed_mime_types'    => bp_attachments_get_allowed_media_exts(),

				// Specific errors for media uploads.
				'upload_error_strings'  => array(
					11 => sprintf(
						/* translators: %s is for the max upload file size. */
						__( 'That media is too big. Please upload one smaller than %s', 'bp-attachments' ),
						size_format( $max_upload_file_size )
					),
					12 => __( 'This file type is not allowed into this directory. Please use another one.', 'bp-attachments' ),
					13 => __( 'A file with this name already exists but the revisions directory is missing.', 'bp-attachments' ),
					14 => __( 'A file with this name already exists but the data describing this existing file are missing.', 'bp-attachments' ),
					15 => __( 'Unexpected error, please contact the administrator of the site.', 'bp-attachments' ),
					16 => __( 'The destination directory is missing.', 'bp-attachments' ),
					17 => __( 'Unknown user, item or destination directory. Please try again.', 'bp-attachments' ),
					18 => __( 'Private uploads are disabled. Try uploading your file publicly instead.', 'bp-attachments' ),
				),
			)
		);
	}

	/**
	 * Override the parent method to avoid re-creating the directories.
	 *
	 * NB: These have been already set during plugin installation.
	 *
	 * @since 1.0.0
	 */
	public function set_upload_dir() {}

	/**
	 * Set the directory when uploading a file.
	 *
	 * @since 1.0.0
	 *
	 * @param array $upload_dir The original Uploads dir.
	 * @return array $value Upload data (path, url, basedir...).
	 */
	public function upload_dir_filter( $upload_dir = array() ) {
		// Populate media arguments merging default with the requested ones.
		$media_args = wp_parse_args(
			array_map( 'wp_unslash', $_POST ), // phpcs:ignore
			array(
				'visibility'  => 'public',
				'object'      => 'members',
				'object_item' => '',
				'parent_dir'  => '',
			)
		);

		$private_uploads = bp_attachments_get_private_uploads_dir();
		$public_uploads  = bp_attachments_get_public_uploads_dir();
		$subdir          = '';

		$upload_dir = $public_uploads;
		if ( 'public' !== $media_args['visibility'] ) {
			$upload_dir = $private_uploads;
		}

		if ( ! isset( $upload_dir['subdir'] ) ) {
			$upload_dir['subdir'] = '';
		}

		if ( 'members' !== $media_args['object'] ) {
			/**
			 * Filter here to set the uploads directory for the requested object.
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
			 */
			return apply_filters( 'bp_attachments_media_uploads_dir', $upload_dir, $media_args );
		} elseif ( $media_args['parent_dir'] ) {
			$subdir = '/' . trim( $media_args['parent_dir'], '/' );

			if ( ! is_dir( $upload_dir['basedir'] . $subdir ) ) {
				$subdir                                  = '';
				$upload_dir['bp_attachments_error_code'] = 16;
			} else {
				$upload_dir = array_merge(
					$upload_dir,
					array(
						'path'  => $upload_dir['basedir'] . $subdir,
						'url'   => $upload_dir['baseurl'] . $subdir,
						'error' => false,
					)
				);
			}
		} else {
			$user_id = 0;
			if ( ctype_digit( $media_args['object_item'] ) || is_int( $media_args['object_item'] ) ) {
				$user_id = (int) $media_args['object_item'];
			}

			if ( ! $user_id ) {
				$user_id = (int) bp_loggedin_user_id();
			} else {
				$user = get_user_by( 'id', $user_id );
				if ( ! $user ) {
					$user_id = 0;
				}
			}

			if ( $user_id ) {
				$subdir     = '/members/' . $user_id;
				$upload_dir = array_merge(
					$upload_dir,
					array(
						'path' => $upload_dir['path'] . $subdir,
						'url'  => $upload_dir['url'] . $subdir,
					)
				);
			} else {
				$upload_dir['bp_attachments_error_code'] = 17;
			}
		}

		if ( $subdir ) {
			$upload_dir['subdir'] .= $subdir;
		}

		if ( 'public' !== $media_args['visibility'] ) {
			$upload_dir['url'] = '';
		}

		return $upload_dir;
	}

	/**
	 * Get the Medium Object of the directory path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path The directory path.
	 * @return object The medium.
	 */
	public function get_parent_dir_object( $path ) {
		$parent_path    = explode( '/', rtrim( $path, '/' ) );
		$parent_dir     = sanitize_file_name( end( $parent_path ) );
		$parent_dir_id  = md5( $parent_dir );
		$dirname_parent = dirname( $path );

		return bp_attachments_get_medium( $parent_dir_id, $dirname_parent );
	}

	/**
	 * BP Attachments specific rules for media uploads.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file The temporary file attributes (before it has been moved).
	 * @return array $file The file with extra errors if needed.
	 */
	public function validate_upload( $file = array() ) {
		// Bail if there's already an error.
		if ( ! empty( $file['error'] ) ) {
			return $file;
		}

		// File size is too big.
		if ( $file['size'] > $this->original_max_filesize ) {
			$file['error'] = 11;
			return $file;
		}

		// Get upload data.
		$upload_data = $this->upload_dir_filter( bp_upload_dir() );

		// Check the parent dir type.
		$dir                  = trailingslashit( $upload_data['path'] );
		$parent_medium        = $this->get_parent_dir_object( $dir );
		$media_type           = '';
		$specific_media_types = array(
			'album'          => 'image',
			'audio_playlist' => 'audio',
			'video_playlist' => 'video',
		);

		// Use the parent directory media type to make sure expected media types are added.
		if ( isset( $parent_medium->media_type ) && isset( $specific_media_types[ $parent_medium->media_type ] ) ) {
			$media_type = $specific_media_types[ $parent_medium->media_type ];
		}

		// File is of invalid type.
		if ( ! bp_attachments_is_file_type_allowed( $file['tmp_name'], $file['name'], $media_type ) ) {
			$file['error'] = 12;
			return $file;
		}

		// Check for an existing file with the same name to eventually create a revision.
		if ( isset( $upload_data['bp_attachments_error_code'] ) && $upload_data['bp_attachments_error_code'] ) {
			$file['error'] = $upload_data['bp_attachments_error_code'];
		} elseif ( isset( $upload_data['error'] ) && $upload_data['error'] ) {
			$file['error'] = 15;
		} else {
			$filename = sanitize_file_name( $file['name'] );
			$id       = md5( $filename );

			if ( file_exists( $dir . $filename ) ) {
				if ( ! is_dir( $dir . '._revisions_' . $id ) ) {
					$file['error'] = 13;
				}

				if ( ! file_exists( $dir . $id . '.json' ) ) {
					$file['error'] = 14;
				}

				$file_data     = wp_json_file_decode( $dir . $id . '.json' );
				$revision_name = wp_unique_filename( $dir . '._revisions_' . $id, $filename );

				$revision = array(
					'name' => $revision_name,
					'date' => bp_core_current_time( true, 'timestamp' ),
				);

				if ( ! isset( $file_data->revisions ) ) {
					$file_data->revisions = array( $revision );
				} else {
					$file_data->revisions[] = $revision;
				}

				$media = bp_attachments_sanitize_media( $file_data );

				// Create the JSON data file.
				file_put_contents( $dir . $id . '.json', wp_json_encode( $media ) ); // phpcs:ignore
				rename( $dir . $filename, trailingslashit( $dir . '._revisions_' . $id ) . $revision_name );
			}
		}

		// Return with error code attached.
		return $file;
	}

	/**
	 * Create a BP Attachments directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $directory_name The name of the directory.
	 * @param string $directory_type The type of the directory.
	 * @param string $parent         The potentiel parent directory.
	 * @return WP_Error|array        A WP Error object in case of failure.
	 *                               An array with created data otherwise.
	 */
	public function make_dir( $directory_name = '', $directory_type = '', $parent = '' ) {
		if ( ! $directory_name || ! in_array( $directory_type, array( 'folder', 'album', 'audio_playlist', 'video_playlist' ), true ) ) {
			return new WP_Error( 'missing_parameter', __( 'The name of your directory or its type are missing or not supported.', 'bp-attachments' ) );
		}

		// Make sure the directory will be created in the attachment directory.
		add_filter( 'upload_dir', array( $this, 'upload_dir_filter' ), 10, 1 );

		// Create the directory at the requested destination.
		$destination_data = wp_upload_dir( null, true );

		// Restore WordPress Uploads data.
		remove_filter( 'upload_dir', array( $this, 'upload_dir_filter' ), 10, 1 );

		if ( isset( $destination_data['bp_attachments_error_code'] ) && $destination_data['bp_attachments_error_code'] ) {
			return new WP_Error( 'bp_attachments_error', $this->upload_error_strings[ $destination_data['bp_attachments_error_code'] ] );
		}

		if ( isset( $destination_data['error'] ) && $destination_data['error'] ) {
			return new WP_Error( 'unexpected_error', $destination_data['error'] );
		}

		if ( $parent ) {
			$medium = $this->get_parent_dir_object( $destination_data['path'] );

			if ( is_null( $medium ) || 'folder' !== $medium->media_type ) {
				return new WP_Error(
					'unsupported_directory_type',
					__( 'Creating sub-directories into the current directory is not allowed.', 'bp-attachments' ),
					array(
						'status' => 403,
					)
				);
			}
		}

		$path = trailingslashit( $destination_data['path'] ) . $directory_name;
		if ( is_dir( $path ) ) {
			return new WP_Error( 'directory_exists', __( 'There is already a directory with this name into the requested destination.', 'bp-attachments' ) );
		}

		// Create the directory.
		mkdir( $path );

		return array(
			'path'       => $path,
			'url'        => trailingslashit( $destination_data['url'] ) . $directory_name,
			'media_type' => $directory_type,
		);
	}
}
