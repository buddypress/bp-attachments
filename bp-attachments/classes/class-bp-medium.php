<?php
/**
 * BP Medium object.
 *
 * @package \bp-attachments\classes\class-bp-medium
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class used to implement the BP_Medium object
 *
 * @since 1.0.0
 */
#[AllowDynamicProperties]
final class BP_Medium {

	/**
	 * Medium's ID.
	 *
	 * This ID is only unique inside the folder the medium is located.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $id = '';

	/**
	 * Medium's owner user ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $owner_id = 0;

	/**
	 * Medium's file/directory name on the filesystem.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $name = '';

	/**
	 * Medium's file/directory title given by the owner ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $title = '';

	/**
	 * Medium's file/directory description given by the owner ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $description = '';

	/**
	 * Medium's size in bytes.
	 *
	 * NB: only used for files.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $size = 0;

	/**
	 * Timestamp of the last time the medium was modified.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $last_modified = 0;

	/**
	 * Medium's mime type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $mime_type = '';

	/**
	 * Medium's file extension.
	 *
	 * NB: only used for files.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $extension = '';

	/**
	 * Medium's type (`file` or `directory`).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $type = '';

	/**
	 * Medium's media type.
	 *
	 * Files can be an `image`, a `video`, an `audio`, a `document` or an `archive`.
	 * Directories can be a regular `folder`, a photo `album`, an `audio_playlist` or a `video_playlist`.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $media_type = '';

	/**
	 * Medium's visibility (`public` or `private`). Defaults to `public`.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $visibility = 'public';

	/**
	 * Whether the medium is readonly or not.
	 *
	 * NB: mainly used for directories.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public $readonly = false;

	/**
	 * Medium's icon.
	 *
	 * Default graphical representation for the medium.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $icon = '';

	/**
	 * Medium's vignette.
	 *
	 * Only used for images to replace the default icon.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $vignette = '';

	/**
	 * Medium's orientation (`landscape` or `portrait`).
	 *
	 * Only used for images.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $orientation = '';

	/**
	 * The object the Medium is the origin of. Defaults to `members`.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $object = 'members';

	/**
	 * List of objects the Medium is attached to.
	 *
	 * Eg: array(
	 *   object { ['object_type']=> 'post' ['object_id']=> int(1) },
	 *   object { ['object_type']=> 'activity' ['object_id']=> int(3) },
	 * );
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $attached_to = array();

	/**
	 * List of the `view`, `embed` and `download` links.
	 *
	 * NB: the `download` link is only used by files.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $links = array();

	/**
	 * Retrieves a BP Attachments media from the file system.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id   The ID of the medium to retrieve.
	 * @param string $path The absolute's path of the medium to retrieve.
	 * @return BP_Medium|false The medium's object if found. False if not.
	 */
	public static function get_instance( $id, $path ) {
		if ( ! $id || ! $path ) {
			return false;
		}

		// Visibility rely on parent's folder visibility.
		$visibility = bp_attachments_get_medium_visibility( $path );

		// Visibility's root uploads directory.
		$uploads_dir = bp_attachments_get_media_uploads_dir( $visibility );

		// Use the relative path to build the cache key to make it unique.
		$relative_path = trim( str_replace( $uploads_dir['path'], '', $path ), '/' );
		$cache_key     = $visibility . '/' . $relative_path . '/' . $id;

		// Use the cached medium if available.
		$medium = wp_cache_get( $cache_key, 'bp_attachments' );

		if ( ! $medium ) {
			// Try to get the json file containing most medium properties.
			$json_file = trailingslashit( $uploads_dir['path'] ) . $relative_path . '/' . $id . '.json';

			if ( ! file_exists( $json_file ) ) {
				return false;
			}

			$json_data = wp_json_file_decode( $json_file );
			$medium    = bp_attachments_sanitize_media( $json_data );

			if ( ! isset( $medium->id ) || $medium->id !== $id ) {
				return false;
			}

			// Build the medium's properties that are not stored into the json data.
			$medium_info          = new SplFileInfo( $json_file );
			$relative_path_chunks = explode( '/', $relative_path );

			// Reset medium's visibility based on where it is located now.
			$medium->visibility = $visibility;

			// Reset medium's object based on where it is located now.
			$medium->object = array_shift( $relative_path_chunks );

			// Check the owner's ID is still the one who created the medium.
			$owner_id = (int) array_shift( $relative_path_chunks );
			if ( $owner_id !== (int) $medium->owner_id ) {
				$medium->owner_id = $owner_id;
			}

			// Reset the last time the medium was edited.
			$medium->last_modified = $medium_info->getMTime();

			$is_medium_directory = false;
			if ( 'inode/directory' === $medium->mime_type ) {
				$is_medium_directory = true;
			} elseif ( ! isset( $medium->extension ) || ! $medium->extension ) {
				$medium->extension = preg_replace( '/^.+?\.([^.]+)$/', '$1', $medium->name );

				if ( ! isset( $medium->media_type ) || ! $medium->media_type ) {
					$medium->media_type = wp_ext2type( $medium->extension );
				}

				if ( 'image' === $medium->media_type ) {
					if ( ! isset( $medium->vignette ) || ! $medium->vignette ) {
						$medium->vignette = bp_attachments_get_vignette_uri( $medium->name, $path );
					}

					if ( ! isset( $medium->orientation ) || ! $medium->orientation ) {
						list( $width, $height ) = getimagesize( trailingslashit( $path ) . $medium->name );

						if ( $width > $height ) {
							$medium->orientation = 'landscape';
						} else {
							$medium->orientation = 'portrait';
						}
					}
				}
			}

			// Add the icon, if needed.
			if ( ! isset( $medium->icon ) || ! $medium->icon ) {
				if ( ! $is_medium_directory ) {
					$medium->icon = wp_mime_type_icon( $medium->media_type );
				} else {
					$medium->icon = bp_attachments_get_directory_icon( $medium->media_type );
				}
			}

			// Add links, if needed.
			if ( ! isset( $medium->links ) || ! $medium->links ) {
				$owner_id = (int) $medium->owner_id;

				$item_action_variables = array( $medium->id );
				if ( array_filter( $relative_path_chunks ) ) {
					$item_action_variables = array_merge( $relative_path_chunks, $item_action_variables );
				}

				$link_args = array(
					'visibility'            => $visibility,
					'object'                => $medium->object,
					'object_item'           => bp_attachments_get_user_slug( $medium->owner_id ),
					'item_action'           => bp_attachments_get_item_action_slug( 'view' ),
					'item_action_variables' => $item_action_variables,
				);

				$medium->links = array(
					'view' => bp_attachments_get_medium_url( $link_args ),
				);

				$link_args['item_action'] = bp_attachments_get_item_action_slug( 'embed' );
				$medium->links['embed']   = bp_attachments_get_medium_url( $link_args );

				if ( ! $is_medium_directory ) {
					$link_args['item_action']  = bp_attachments_get_item_action_slug( 'download' );
					$medium->links['download'] = bp_attachments_get_medium_url( $link_args );
				}

				if ( 'public' === $visibility ) {
					$medium->links['src'] = bp_attachments_get_src( $medium->name, $path );
				}
			}

			// Add the medium to cached ones.
			wp_cache_add( $cache_key, $medium, 'bp_attachments' );
		}

		return new BP_Medium( $medium );
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param BP_Medium|object $medium BP Medium object.
	 */
	public function __construct( $medium ) {
		foreach ( get_object_vars( $medium ) as $key => $value ) {
			$this->$key = $value;
		}
	}
}
