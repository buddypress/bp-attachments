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
 * @since 1.0.0
 */
class BP_Attachments_Media extends BP_Attachment {
	/**
	 * The constuctor.
	 *
	 * @since 1.0.0
	 *
	 * @param BP_Attachments_Media|object $media The Attachment object.
	 */
	public function __construct( $media = null ) {
		if ( ! is_null( $media ) && is_object( $media ) ) {
			foreach ( get_object_vars( $media ) as $key => $value ) {
				$this->$key = $value;
			}
		}

		// Max upload size.
		$max_upload_file_size = bp_attachments_get_max_upload_file_size();

		parent::__construct(
			array(
				'action'                => 'bp_attachments_media_upload',
				'file_input'            => 'file',
				'original_max_filesize' => $max_upload_file_size,
				'base_dir'              => bp_attachments_uploads_dir_get( 'dir' ),
				'required_wp_files'     => array( 'file' ),

				// Specific errors for media uploads.
				'upload_error_strings'  => array(
					11 => sprintf(
						/* translators: %s is for the max upload file size. */
						__( 'That media is too big. Please upload one smaller than %s', 'bp-attachments' ),
						size_format( $max_upload_file_size )
					),
					12 => __( 'This file type is not allowed. Please use another one.', 'bp-attachments' ),
					13 => __( 'A file with this name already exists but the revisions directory is missing.', 'bp-attachments' ),
					14 => __( 'A file with this name already exists but the data describing this existing file are missing.', 'bp-attachments' ),
					15 => __( 'Unexpected error, please contact the administrator of the site.', 'bp-attachments' ),
					16 => __( 'The destination directory is missing.', 'bp-attachments' ),
					17 => __( 'Unknown group. Please try again', 'bp-attachments' ),
					18 => __( 'Unknown user. Please try again', 'bp-attachments' ),
				),
			)
		);
	}

	/**
	 * Retrieves a BP Attachment media from the file system.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id            The ID of the media to retrieve.
	 * @param string $relative_path The relative path to `/wp-content/uploads/buddypress`.
	 * @return BP_Attachments_Media|false The site's object if found. False if not.
	 */
	public static function get_instance( $id, $relative_path ) {
		if ( ! $id || ! $relative_path ) {
			return false;
		}

		// Build the cache id.
		$r_path     = trim( $relative_path, '/' );
		$path_parts = explode( '/', $r_path );
		$status     = array_shift( $path_parts );

		if ( 'public' !== $status && 'private' !== $status ) {
			return false;
		}

		$r_path    = implode( '/', $path_parts );
		$cache_key = $status . '/' . $r_path . '/' . $id;

		$media = wp_cache_get( $cache_key, 'bp_attachments' );

		if ( ! $media ) {
			$bp_uploads = bp_attachments_get_media_uploads_dir( $status );
			$json_file  = trailingslashit( $bp_uploads['path'] ) . $r_path . '/' . $id . '.json';

			if ( ! file_exists( $json_file ) ) {
				return false;
			}

			$json_data = file_get_contents( $json_file ); // phpcs:ignore
			$media     = bp_attachments_sanitize_media( json_decode( $json_data ) );

			if ( ! $media ) {
				return false;
			}

			wp_cache_add( $cache_key, $media, 'bp_attachments' );
		}

		return new BP_Attachments_Media( $media );
	}

	/**
	 * Gets the available media types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $allowed_types Array of allowed avatar types.
	 * @return string comma separated list of allowed avatar types.
	 */
	public static function get_media_types( $allowed_types = array() ) {
		$types = array_map( 'strtoupper', $allowed_types );
		$comma = _x( ',', 'avatar types separator', 'buddypress' );
		return join( $comma . ' ', $types );
	}

	/**
	 * Override the parent method to avoid re-create the directories.
	 *
	 * NB: This has been already set during plugin installation.
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
				'status'      => 'public',
				'object'      => 'members',
				'object_id'   => 0,
				'object_slug' => '',
				'parent_dir'  => '',
			)
		);

		$private_uploads = bp_attachments_get_private_uploads_dir();
		$public_uploads  = bp_attachments_get_public_uploads_dir();

		$upload_dir = $public_uploads;
		if ( $media_args['status'] && 'public' !== $media_args['status'] ) {
			$upload_dir = $private_uploads;
		}

		if ( $media_args['parent_dir'] ) {
			$subdir = '/' . trim( $media_args['parent_dir'], '/' );

			if ( 'groups' === $media_args['object'] && bp_is_active( 'groups' ) ) {
				$group_slug = $media_args['object_slug'];
				$group_id   = (int) BP_Groups_Group::get_id_from_slug( $group_slug );

				if ( $group_id && ( current_user_can( 'bp_moderate' ) || groups_is_user_member( bp_loggedin_user_id(), $group_id ) ) ) {
					$subdir = str_replace(
						'/groups/' . $media_args['object_slug'],
						'/groups/' . $group_id,
						$subdir
					);
				}
			}

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

			if ( 'groups' === $media_args['object'] && bp_is_active( 'groups' ) ) {
				$group_slug = $media_args['object_slug'];
				$group_id   = (int) BP_Groups_Group::get_id_from_slug( $group_slug );
				$group      = groups_get_group( $group_id );

				if ( ! $group_id || $group_id !== (int) $group->id ) {
					$subdir     = '';
					$upload_dir = array( 'bp_attachments_error_code' => 17 );
				} else {
					if ( 'public' === $group->status ) {
						$upload_dir = $public_uploads;
					} else {
						$upload_dir = $private_uploads;
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
			} else {
				$user_id = 0;
				if ( ctype_digit( $media_args['object_id'] ) || is_int( $media_args['object_id'] ) ) {
					$user_id = (int) $media_args['object_id'];
				}

				if ( ! $user_id ) {
					$user_id = (int) bp_loggedin_user_id();
				} else {
					$user = get_user_by( 'id', $user_id );
					if ( ! $user ) {
						$user_id = 0;
					}
				}

				if ( ! $user_id ) {
					$subdir     = '';
					$upload_dir = array( 'bp_attachments_error_code' => 18 );
				} else {
					$subdir     = '/members/' . $user_id;
					$upload_dir = array_merge(
						$upload_dir,
						array(
							'path' => $upload_dir['path'] . $subdir,
							'url'  => $upload_dir['url'] . $subdir,
						)
					);
				}
			}
		}

		if ( ! isset( $upload_dir['subdir'] ) ) {
			$upload_dir['subdir'] = '';
		}

		$upload_dir['subdir'] .= $subdir;

		return $upload_dir;
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

			// File is of invalid type.
		} elseif ( ! bp_attachments_is_file_type_allowed( $file['tmp_name'], $file['name'] ) ) {
			$file['error'] = 12;
		}

		// Check for an existing file with the same name to eventually create a revision.
		$upload_data = $this->upload_dir_filter( bp_upload_dir() );

		if ( isset( $upload_data['bp_attachments_error_code'] ) && $upload_data['bp_attachments_error_code'] ) {
			$file['error'] = $upload_data['bp_attachments_error_code'];
		} elseif ( isset( $upload_data['error'] ) && $upload_data['error'] ) {
			$file['error'] = 15;
		} else {
			$dir      = trailingslashit( $upload_data['path'] );
			$filename = sanitize_file_name( $file['name'] );
			$id       = md5( $filename );

			if ( file_exists( $dir . $filename ) ) {
				if ( ! is_dir( $dir . '._revisions_' . $id ) ) {
					$file['error'] = 13;
				}

				if ( ! file_exists( $dir . $id . '.json' ) ) {
					$file['error'] = 14;
				}

				$file_data     = json_decode( wp_unslash( file_get_contents( $dir . $id . '.json' ) ) ); // phpcs:ignore
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
	 * @return WP_Error|array        A WP Error object in case of failure.
	 *                               An array with created data otherwise.
	 */
	public function make_dir( $directory_name = '', $directory_type = '' ) {
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
