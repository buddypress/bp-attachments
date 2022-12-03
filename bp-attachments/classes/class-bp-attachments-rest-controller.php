<?php
/**
 * BP Attachments REST Controller.
 *
 * @package \bp-attachments\classes\class-bp-attachments-rest-controller
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BP Attachments REST Controller Class.
 *
 * @since 1.0.0
 */
class BP_Attachments_REST_Controller extends WP_REST_Attachments_Controller {
	/**
	 * Current Medium's data.
	 *
	 * @since 1.0.0
	 * @var null|object
	 */
	protected $current_medium_data = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = bp_rest_namespace() . '/' . bp_rest_version();
		$this->rest_base = buddypress()->attachments->id;
	}

	/**
	 * Registers the routes for the BP Attachments objects of the controller.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'action'                  => array(
							'description' => __( 'Whether to upload a medium or create a directory.', 'bp-attachments' ),
							'type'        => 'string',
							'enum'        => array( 'bp_attachments_media_upload', 'bp_attachments_make_directory' ),
							'required'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
						'object'                  => array(
							'description' => __( 'The object the medium is uploaded for.', 'bp-attachments' ),
							'type'        => 'string',
							'enum'        => array( 'members', 'groups' ),
							'default'     => 'members',
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
						'object_item'             => array(
							'description' => __( 'The object single item the medium is uploaded for.', 'bp-attachments' ),
							'type'        => 'integer',
							'default'     => 0,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
						'attached_to_object_type' => array(
							'description' => __( 'The object type the medium is attached to.', 'bp-attachments' ),
							'type'        => 'string',
							'default'     => '',
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
						'attached_to_object_id'   => array(
							'description' => __( 'The object id the medium is attached to.', 'bp-attachments' ),
							'type'        => 'integer',
							'default'     => 0,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
						'total_bytes'             => array(
							'description' => __( 'The total bytes sent during an upload process.', 'bp-attachments' ),
							'type'        => 'integer',
							'default'     => 0,
							'arg_options' => array(
								'sanitize_callback' => 'intval',
							),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\S]+)/',
			array(
				'args'   => array(
					'id'            => array(
						'description' => __( 'An alphanumeric ID for the BP Medium object.', 'bp-attachments' ),
						'type'        => 'string',
					),
					'relative_path' => array(
						'description' => __( 'Relative path to the BP Medium object.', 'bp-attachments' ),
						'type'        => 'string',
						'required'    => true,
					),
					'total_bytes'   => array(
						'description' => __( 'The total bytes to remove after a delete process.', 'bp-attachments' ),
						'type'        => 'integer',
						'default'     => 0,
						'arg_options' => array(
							'sanitize_callback' => 'intval',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Check if a given request has access to BP Attachments media.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$retval = true;

		if ( ! bp_attachments_current_user_can( 'upload_bp_media' ) && 'edit' === $request->get_param( 'context' ) ) {
			$retval = new WP_Error(
				'bp_attachments_rest_authorization_require',
				__( 'Sorry, you are not allowed to request media.', 'bp-attachments' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the BP Attachments media `get_items` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_rest_get_items_permissions_check', $retval, $request );
	}

	/**
	 * Retrieve BP Attachments Media.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response List of BP Attachments Media response data.
	 */
	public function get_items( $request ) {
		$public_basedir    = bp_attachments_uploads_dir_get()['basedir'];
		$parent            = $request->get_param( 'directory' );
		$object            = $request->get_param( 'object' );
		$user_id           = $request->get_param( 'user_id' );
		$requested_user_id = $user_id;
		$dir               = '';
		$context           = $request->get_param( 'context' );
		$page              = $request->get_param( 'page' );
		$per_page          = $request->get_param( 'per_page' );
		$pagination        = array(
			'X-BP-Attachments-Media-Libraries-Total'      => 0,
			'X-BP-Attachments-Media-Libraries-TotalPages' => 0,
		);

		if ( ! $user_id ) {
			$user_id = bp_loggedin_user_id();
		}

		// Get files inside a single item directory.
		if ( $parent && ! in_array( $parent, array( 'member', 'groups' ), true ) ) {
			$visibility = 'public';

			// The object ID defaults to the user ID.
			$object_id = $user_id;

			if ( 'private' === $parent || 'public' === $parent ) {
				$visibility = $parent;
				$parent     = '';
			} else {
				$parse_parent = explode( '/', trim( $parent, '/' ) );

				// Handle other components than the `members` one's visibility.
				if ( 'members' !== $object ) {
					$default_props = array(
						'visibility'    => $visibility,
						'object_id'     => 0,
						'relative_path' => '',
					);

					$component_args = bp_parse_args(
						/**
						 * Filter here to set the directory visibility according to your component's logic.
						 *
						 * @since 1.0.0
						 *
						 * @param array $default_props {
						 *     An array of arguments.
						 *
						 *     @type string $visibility    The directory visibility, it can be `public` or `private`. Defaults to `public`.
						 *     @type int    $object_id     The component's single item ID.
						 *     @type string $relative_path Relative path from the `$object` slug to the single item (it can be the object ID or slug).
						 * }
						 * @param string $object       The component's ID.
						 * @param int    $user_id      The current user ID.
						 * @param array  $parse_parent Parent's relative path chunks.
						 */
						apply_filters( 'bp_attachments_rest_directory_visibility', $default_props, $object, $user_id, $parse_parent ),
						$default_props
					);

					if ( is_wp_error( $component_args ) ) {
						return new WP_Error(
							$visibility->get_error_code(),
							$visibility->get_error_message(),
							array(
								'status' => 500,
							)
						);
					}

					$visibility = $component_args['visibility'];
					$object_id  = $component_args['object_id'];

					if ( $component_args['relative_path'] ) {
						$component_args['relative_path'] = trailingslashit( $object ) . trim( $component_args['relative_path'], '/' );
					}
				} else {
					$visibility = $parse_parent[0];
				}

				array_splice( $parse_parent, 0, 3 );
				$parent = implode( '/', $parse_parent );
			}

			$dir   = bp_attachments_get_media_uploads_dir( $visibility )['path'] . '/' . $object . '/' . $object_id;
			$dir   = trailingslashit( $dir ) . $parent;
			$media = bp_attachments_list_media_in_directory( $dir, $object );

			// Get the requested user root directories.
		} else {
			// List all members who uploaded media.
			if ( ! $requested_user_id && bp_current_user_can( 'bp_moderate' ) && 'edit' === $context ) {
				$page     = ! $page ? 1 : (int) $page;
				$per_page = ! $per_page ? 1 : (int) $per_page;

				$member_libraries = bp_attachments_list_member_media_libraries(
					array(
						'per_page' => $per_page,
						'page'     => $page,
					)
				);

				$media           = $member_libraries['libraries'];
				$total_libraries = (int) $member_libraries['total_libraries'];
				$max_pages       = ceil( $total_libraries / $per_page );

				if ( $page > $max_pages && $total_libraries > 0 ) {
					return new WP_Error(
						'bp_rest_attachments_invalid_page_number',
						__( 'The page number requested is larger than the number of pages available.', 'bp-attachments' ),
						array( 'status' => 400 )
					);
				}

				// Set pagination.
				$pagination['X-BP-Attachments-Media-Libraries-Total']      = $total_libraries;
				$pagination['X-BP-Attachments-Media-Libraries-TotalPages'] = $max_pages;
			} else {
				$media = bp_attachments_list_member_root_objects( $user_id, 'member' );
				unset( $parent );
			}
		}

		$retval = array();
		foreach ( $media as $medium ) {
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $medium, $request )
			);
		}

		$response = rest_ensure_response( $retval );

		// Set the default relative path.
		$relative_path = '';

		if ( bp_attachments_can_do_private_uploads() ) {
			$private_basedir = bp_attachments_get_private_root_dir();

			if ( ! is_wp_error( $private_basedir ) ) {
				$relative_path = str_replace( array( $public_basedir, $private_basedir ), array( '', '/private' ), $dir );
			}
		}

		if ( ! $relative_path ) {
			$relative_path = str_replace( $public_basedir, '', $dir );
		}

		// Use component's relative path if set.
		if ( isset( $object_id ) && $object_id && isset( $component_args['relative_path'] ) && $component_args['relative_path'] ) {
			$relative_path = str_replace( $object . '/' . $object_id, $component_args['relative_path'], $relative_path );
		}

		$response->header( 'X-BP-Attachments-Relative-Path', $relative_path );

		if ( 2 === count( array_filter( $pagination ) ) ) {
			foreach ( $pagination as $key_pagination => $value_pagination ) {
				$response->header( $key_pagination, $value_pagination );
			}
		}

		return $response;
	}

	/**
	 * Check if the user can create new BP Attachments Medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		$retval = new WP_Error(
			'bp_attachments_rest_authorization_required',
			__( 'Sorry, you are not allowed to create media.', 'bp-attachments' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);

		if ( bp_attachments_current_user_can( 'upload_bp_media' ) ) {
			$retval = true;
		}

		/**
		 * Filter the BP Attachments media `create_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_rest_create_item_permissions_check', $retval, $request );
	}

	/**
	 * Creates a BP Attachments Medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$action = $request->get_param( 'action' );

		// Set the context of the request.
		$request->set_param( 'context', 'edit' );

		// Upload a file.
		if ( 'bp_attachments_media_upload' === $action ) {

			// Get the file via $_FILES or raw data.
			$files = $request->get_file_params();

			if ( empty( $files ) ) {
				return new WP_Error(
					'bp_attachments_rest_upload_no_data',
					__( 'No files were provided.', 'bp-attachments' ),
					array(
						'status' => 400,
					)
				);
			}

			$object      = $request->get_param( 'object' );
			$object_item = $request->get_param( 'object_item' );
			$can_upload  = false;

			if ( $object_item && 'members' === $object ) {
				$can_upload = ( (int) bp_loggedin_user_id() === (int) $object_item ) || current_user_can( 'bp_moderate' );
			} else {
				/**
				 * Use this filter to control who can upload to a requested object.
				 *
				 * @since 1.0.0
				 *
				 * @param bool       $can_upload  True if the user can upload to the requested object. False otherwise.
				 * @param string     $object      The requested object type eg: `groups`, `members`...
				 * @param string|int $object_item The slug or ID of the object (in case of a group it's a slug).
				 */
				$can_upload = apply_filters( 'bp_attachments_rest_can_upload_to_object', $can_upload, $object, $object_item );
			}

			if ( ! $can_upload ) {
				return new WP_Error(
					'bp_attachments_rest_upload_forbidden',
					__( 'You cannot upload files into the required destination', 'bp-attachments' ),
					array(
						'status' => 403,
					)
				);
			}

			$bp_uploader = new BP_Attachments_Media();
			$uploaded    = $bp_uploader->upload( $files );

			if ( isset( $uploaded['error'] ) && $uploaded['error'] ) {
				return new WP_Error(
					'bp_attachments_rest_upload_unknown_error',
					$uploaded['error'],
					array(
						'status' => 500,
					)
				);
			}

			$request->set_param( 'path', $uploaded['file'] );
			$request->set_param( 'mime_type', $uploaded['type'] );

			// Make a new directory.
		} elseif ( 'bp_attachments_make_directory' === $action ) {
			$can_create_dir = false;
			$dir_data       = wp_parse_args(
				array_map( 'wp_unslash', $request->get_params() ),
				array(
					'directory_name' => '',
					'directory_type' => 'folder',
					'parent_dir'     => '',
					'object'         => '',
					'object_item'    => '',
				)
			);

			if ( $dir_data['object_item'] && 'members' === $dir_data['object'] ) {
				$can_create_dir = ( (int) bp_loggedin_user_id() === (int) $dir_data['object_item'] ) || current_user_can( 'bp_moderate' );
			} else {
				/**
				 * Use this filter to control who can create a directory for a requested object.
				 *
				 * @since 1.0.0
				 *
				 * @param bool       $can_create_dir  True if the user can create a directory for the requested object. False otherwise.
				 * @param string     $object          The requested object type eg: `groups`, `members`...
				 * @param string|int $object_item     The slug or ID of the object (in case of a group it's a slug).
				 */
				$can_create_dir = apply_filters( 'bp_attachments_rest_can_create_dir_to_object', $can_create_dir, $dir_data['object'], $dir_data['object_item'] );
			}

			if ( ! $can_create_dir ) {
				return new WP_Error(
					'bp_attachments_rest_create_dir_forbidden',
					__( 'You cannot create directories into the required destination', 'bp-attachments' ),
					array(
						'status' => 403,
					)
				);
			}

			if ( ! $dir_data['directory_name'] || ! $dir_data['directory_type'] ) {
				return new WP_Error(
					'bp_attachments_rest_create_dir_no_data',
					__( 'Some information are missing to be able to create the directory.', 'bp-attachments' ),
					array(
						'status' => 400,
					)
				);
			}

			$bp_dir_maker   = new BP_Attachments_Media();
			$directory_name = sanitize_title( $dir_data['directory_name'] );
			$made_dir       = $bp_dir_maker->make_dir( $directory_name, $dir_data['directory_type'], $dir_data['parent_dir'] );

			if ( is_wp_error( $made_dir ) ) {
				return new WP_Error(
					'bp_attachments_rest_makedir_failure',
					$made_dir->get_error_message(),
					array(
						'status' => 500,
					)
				);
			}

			$request->set_param( 'path', $made_dir['path'] );
			$request->set_param( 'title', $dir_data['directory_name'] );
			$request->set_param( 'media_type', $made_dir['media_type'] );
		} else {
			return new WP_Error(
				'bp_attachments_rest_unsupported_action',
				__( 'This action is not supported by the BP Attachments plugin.', 'bp-attachments' ),
				array(
					'status' => 400,
				)
			);
		}

		$prepared_media = $this->prepare_item_for_filesystem( $request );
		$media          = bp_attachments_create_media( $prepared_media );

		if ( is_wp_error( $media ) ) {
			return new WP_Error(
				$media->get_error_code(),
				$media->get_error_message(),
				array(
					'status' => 500,
				)
			);
		}

		$total_bytes = $request->get_param( 'total_bytes' );
		if ( ! empty( $total_bytes ) ) {
			bp_attachments_user_raise_files_size( $media->owner_id, $total_bytes );
		}

		// Add the icon.
		if ( 'inode/directory' !== $media->mime_type ) {
			$media->icon = wp_mime_type_icon( $media->media_type );
		} else {
			$media->icon = bp_attachments_get_directory_icon( $media->media_type );
		}

		// Return the response.
		return rest_ensure_response( $media );
	}

	/**
	 * Get the BP Attachments Medium's json file data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return null|object The BP AttachmentsMedium's json file data.
	 */
	public function get_medium_json_data( $request ) {
		$id            = trim( $request->get_param( 'id' ), '/' );
		$relative_path = $request->get_param( 'relative_path' );

		$relative_path_parts = array_filter( explode( '/', $relative_path ) );
		$visibility          = array_shift( $relative_path_parts );

		if ( 'private' !== $visibility && 'public' !== $visibility ) {
			return null;
		}

		// Remove visibility from private path.
		$relative_path = str_replace( '/' . $visibility, '', $relative_path );

		// Set the context of the request.
		$request->set_param( 'context', 'edit' );

		$path      = bp_attachments_get_media_uploads_dir( $visibility )['path'];
		$subdir    = trailingslashit( implode( '/', $relative_path_parts ) );
		$abspath   = trailingslashit( $path ) . $subdir;
		$json_file = $abspath . $id . '.json';

		if ( ! file_exists( $json_file ) ) {
			return null;
		}

		// Create the medium data out of the json file.
		$medium_data = wp_json_file_decode( $json_file );

		// Add extra data that may need `deleta_item` or `update_item` methods.
		$medium_data->json_file  = $json_file;
		$medium_data->abspath    = untrailingslashit( $abspath );
		$medium_data->visibility = $visibility;

		/**
		 * Filter the BP Attachments Medium's json file data.
		 *
		 * @since 1.0.0
		 *
		 * @param object          $medium_data Medium's json file data.
		 * @param WP_REST_Request $request     The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_rest_medium_json_data', $medium_data, $request );
	}

	/**
	 * Check if the user can update a BP Attachments medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		$retval = new WP_Error(
			'bp_attachments_rest_authorization_required',
			__( 'Sorry, you are not allowed to edit media.', 'bp-attachments' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);

		$this->current_medium_data = $this->get_medium_json_data( $request );
		if ( isset( $this->current_medium_data->owner_id ) ) {
			$retval = bp_attachments_current_user_can( 'edit_bp_medium', array( 'bp_medium' => $this->current_medium_data ) );
		}

		/**
		 * Filter the BP Attachments media `update_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_rest_update_item_permissions_check', $retval, $request );
	}

	/**
	 * Updates a BP Attachments Medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function update_item( $request ) {
		if ( ! is_null( $this->current_medium_data ) ) {
			$medium_data = $this->current_medium_data;
		} else {
			$medium_data = $this->get_medium_json_data( $request );
		}

		if ( ! $medium_data ) {
			return new WP_Error(
				'bp_attachments_rest_update_medium_failed',
				__( 'Sorry, we were not able to edit the media.', 'bp-attachments' ),
				array(
					'status' => 500,
				)
			);
		}

		$prepared_medium = $this->prepare_item_for_filesystem( $request );

		// Merge edits.
		foreach ( array_keys( get_object_vars( $medium_data ) ) as $prop ) {
			if ( isset( $prepared_medium->{$prop} ) ) {
				$medium_data->{$prop} = $prepared_medium->{$prop};
			}
		}

		$medium = bp_attachments_update_medium( $medium_data );

		if ( is_wp_error( $medium ) ) {
			return new WP_Error(
				$medium->get_error_code(),
				$medium->get_error_message(),
				array(
					'status' => 500,
				)
			);
		}

		// Add the icon.
		if ( 'inode/directory' !== $medium->mime_type ) {
			$medium->icon = wp_mime_type_icon( $medium->media_type );
		} else {
			$medium->icon = bp_attachments_get_directory_icon( $medium->media_type );
		}

		// Return the response.
		return rest_ensure_response( $medium );
	}

	/**
	 * Check if the user can delete a BP Attachments medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$retval = new WP_Error(
			'bp_attachments_rest_authorization_require',
			__( 'Sorry, you are not allowed to delete media.', 'bp-attachments' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);

		$this->current_medium_data = $this->get_medium_json_data( $request );
		if ( isset( $this->current_medium_data->owner_id ) ) {
			$retval = bp_attachments_current_user_can( 'delete_bp_medium', array( 'bp_medium' => $this->current_medium_data ) );
		}

		/**
		 * Filter the BP Attachments media `delete_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_rest_delete_item_permissions_check', $retval, $request );
	}

	/**
	 * Deletes a BP Attachments Medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$error = new WP_Error(
			'bp_attachments_rest_delete_medium_failed',
			__( 'Sorry, we were not able to delete the media.', 'bp-attachments' ),
			array(
				'status' => 500,
			)
		);

		if ( ! is_null( $this->current_medium_data ) ) {
			$medium_data = $this->current_medium_data;
		} else {
			$medium_data = $this->get_medium_json_data( $request );
		}

		if ( ! $medium_data ) {
			return $error;
		}

		$previous = $this->prepare_item_for_response( $medium_data, $request );
		$deleted  = true;

		// Delete the json file data.
		if ( file_exists( $medium_data->json_file ) ) {
			$deleted = unlink( $medium_data->json_file );
		}

		/**
		 * The medium is a a file. We need to delete:
		 * - the revisions,
		 * - the file.
		 */
		if ( 'file' === $medium_data->type ) {
			// Delete revisions folder.
			if ( true === $deleted ) {
				$deleted = bp_attachments_delete_directory( $medium_data->abspath . '/._revisions_' . $medium_data->id, $medium_data->visibility );
			}

			if ( true === $deleted && file_exists( $medium_data->abspath . '/' . $medium_data->name ) ) {
				$total_bytes = $request->get_param( 'total_bytes' );

				// Decrease owner's files size.
				if ( ! empty( $total_bytes ) ) {
					bp_attachments_user_decrease_files_size( $medium_data->owner_id, $total_bytes );
				}

				// Delete file.
				$deleted = unlink( $medium_data->abspath . '/' . $medium_data->name );

				/**
				 * Perform additional code once the Medium has been deleted.
				 *
				 * @since 1.0.0
				 *
				 * @param object $medium_data An object containing data about the medium.
				 */
				do_action( 'bp_attachments_deleted_medium', $medium_data );
			}
		} else {
			if ( true === $deleted ) {
				// Delete the folder and its content.
				$deleted = bp_attachments_delete_directory( $medium_data->abspath . '/' . $medium_data->name, $medium_data->visibility );
			}
		}

		if ( ! $deleted ) {
			return $error;
		}

		// Build the response.
		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => $deleted,
				'previous' => $previous->get_data(),
			)
		);

		return $response;
	}

	/**
	 * Prepares a BP Attachments Media for File System.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|object The media object on success, WP_Error object on failure.
	 */
	public function prepare_item_for_filesystem( $request ) {
		$media = new stdClass();

		$schema = $this->get_item_schema();

		// It's a new medium.
		if ( isset( $request['path'] ) && $request['path'] ) {
			$media->path = $request['path'];
		}

		// Media Mime Type.
		if ( ! empty( $schema['properties']['mime_type'] ) && isset( $request['mime_type'] ) ) {
			$media->mime_type = $request['mime_type'];
		}

		// Media title.
		if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
			$media->title = $request['title'];
		}

		if ( ! empty( $schema['properties']['description'] ) && isset( $request['description'] ) ) {
			$media->description = $request['description'];
		}

		// Media Media Type.
		if ( ! empty( $schema['properties']['media_type'] ) && isset( $request['media_type'] ) ) {
			$media->media_type = $request['media_type'];
		}

		// Object the medium is attached to.
		$attached_to_object_type = $request->get_param( 'attached_to_object_type' );
		$attached_to_object_id   = $request->get_param( 'attached_to_object_id' );

		if ( ! empty( $schema['properties']['attached_to'] ) && $attached_to_object_type && $attached_to_object_id ) {
			$media->attached_to = array(
				(object) array(
					'object_type' => $attached_to_object_type,
					'object_id'   => $attached_to_object_id,
				),
			);
		}

		return $media;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param object $medium BP Attachments object.
	 * @return array
	 */
	protected function prepare_links( $medium ) {
		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		/**
		 * Filter links prepared for the REST response.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $links The prepared links of the REST response.
		 * @param object $medium Activity object.
		 */
		return apply_filters( 'bp_attachments_rest_item_prepare_links', $links, $medium );
	}

	/**
	 * Prepares BP Attachments data for return as an object.
	 *
	 * @since 1.0.0
	 *
	 * @param object          $medium  BP Attachments object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $medium, $request ) {
		$data    = get_object_vars( $medium );
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $medium ) );

		return $response;
	}

	/**
	 * Retrieves the query params for the BP Attachments Media collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$bp                            = buddypress();
		$params                        = WP_REST_Controller::get_collection_params();
		$params['context']['default']  = 'view';
		$params['per_page']['default'] = 20;

		$params['directory'] = array(
			'description' => __( 'Relative path to the directory to only list its content.', 'bp-attachments' ),
			'default'     => '',
			'type'        => 'string',
		);

		$params['user_id'] = array(
			'description'       => __( 'Limit result set to items created by a specific user (ID).', 'bp-attachments' ),
			'default'           => 0,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$objects = array(
			$bp->members->id,
		);

		if ( bp_is_active( 'groups' ) ) {
			$objects[] = $bp->groups->id;
		}

		$params['object'] = array(
			'description'       => __( 'Limit result set to items attached to active BuddyPress component.', 'bp-attachments' ),
			'default'           => 'members',
			'type'              => 'string',
			'enum'              => $objects,
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		/**
		 * Filters the collection query params.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Query params.
		 */
		return apply_filters( 'bp_attachments_rest_collection_params', $params );
	}

	/**
	 * Retrieves the BP Attachments Media's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( ! isset( $this->schema ) ) {
			$this->schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'bp_attachments',
				'type'       => 'object',
				// Base properties for every BP Attachments Media.
				'properties' => array(
					'id'            => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'A unique alphanumeric ID for the medium.', 'bp-attachments' ),
						'readonly'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'owner_id'      => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'A unique numeric ID for the user owning the medium.', 'bp-attachments' ),
						'readonly'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'object'        => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'The name of the BuddyPress component the medium is attached to.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
						'default'           => 'members',
					),
					'name'          => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'The name of the medium.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_file_name',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'title'         => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'The pretty name of the medium.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'description'   => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'The description of the medium.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
						'validate_callback' => 'rest_validate_request_arg',
						'default'           => '',
					),
					'mime_type'     => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'The description of the medium.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_mime_type',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'type'          => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Whether the medium is a directory or a file.', 'bp-attachments' ),
						'type'              => 'string',
						'enum'              => array( 'file', 'directory' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'last_modified' => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Timestamp of the last time the medium was modified.', 'bp-attachments' ),
						'type'              => 'integer',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'size'          => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Size in kilobytes for the medium.', 'bp-attachments' ),
						'type'              => 'integer',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'vignette'      => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'URL of the image to use as a vignette for the medium.', 'bp-attachments' ),
						'type'              => 'string',
						'format'            => 'uri',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'icon'          => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'URL of the image to use as an icon for the medium.', 'bp-attachments' ),
						'type'              => 'string',
						'format'            => 'uri',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'orientation'   => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Orientation for the image medium.', 'bp-attachments' ),
						'type'              => 'string',
						'enum'              => array( 'portrait', 'landscape' ),
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'extension'     => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Extension of the medium file.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'media_type'    => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Human readable medium type', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'visibility'    => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'Whether the medium is private or public', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'enum'              => array( 'public', 'private' ),
						'default'           => 'public',
					),
					'readonly'      => array(
						'context'           => array( 'edit', 'view' ),
						'description'       => __( 'Whether the medium (directory) is readonly or not', 'bp-attachments' ),
						'type'              => 'boolean',
						'sanitize_callback' => 'rest_sanitize_boolean',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
						'default'           => false,
					),
					'links'         => array(
						'context'           => array( 'view', 'edit', 'embed' ),
						'description'       => __( 'The medium links.', 'bp-attachments' ),
						'type'              => 'object',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
						'properties'        => array(
							'embed' => array(
								'context'     => array( 'view', 'edit', 'embed' ),
								'description' => __( 'The embed URL for the medium.', 'bp-attachments' ),
								'type'        => 'string',
								'format'      => 'uri',
								'readonly'    => true,
							),
							'src'   => array(
								'context'     => array( 'view', 'edit', 'embed' ),
								'description' => __( 'The source URL to the public medium.', 'bp-attachments' ),
								'type'        => 'string',
								'format'      => 'uri',
								'readonly'    => true,
							),
							'view'  => array(
								'context'     => array( 'view', 'edit', 'embed' ),
								'description' => __( 'The view permalink to the medium.', 'bp-attachments' ),
								'type'        => 'string',
								'format'      => 'uri',
								'readonly'    => true,
							),
						),
					),
					'attached_to'   => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'List of objects the medium is attached to', 'bp-attachments' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'object',
						),
						'default'           => array(),
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
				),
			);
		}

		return $this->add_additional_fields_schema( $this->schema );
	}
}
