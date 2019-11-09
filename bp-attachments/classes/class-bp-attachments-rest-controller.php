<?php
/**
 * BP Attachments REST Controller.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\classes\class-bp-attachments-rest-controller
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BP Attachments REST Controller Class.
 *
 * @since 1.0.0
 */
class BP_Attachments_REST_Controller extends WP_REST_Attachments_Controller {
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
	 * Registers the routes for the BP Attachment objects of the controller.
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
						'action' => array(
							'description' => __( 'Whether to upload a media or create a directory.', 'bp-attachments' ),
							'type'        => 'string',
							'enum'        => array( 'bp_attachments_media_upload', 'bp_attachments_make_directory' ),
							'required'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
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
		/**
		 * Filter the BP Attachments media `get_items` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_get_items_rest_permissions_check', true, $request );
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
		$dir  = bp_attachments_get_private_uploads_dir()['path'] . '/members/1';
		$list = bp_attachments_list_media_in_directory( $dir );

		return rest_ensure_response( $list );
	}

	/**
	 * Check if the user can create new BP Attachments media.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		$retval = true;

		if ( ! is_user_logged_in() ) {
			$retval = new WP_Error(
				'bp_rest_authorization_required',
				__( 'Sorry, you are not allowed to create media.', 'bp-attachments' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the BP Attachments media `create_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_create_item_rest_permissions_check', $retval, $request );
	}

	/**
	 * Creates a BP Attachments Media.
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
		if ( 'bp_attachments_make_directory' !== $action ) {
			// Get the file via $_FILES or raw data.
			$files = $request->get_file_params();

			if ( empty( $files ) ) {
				return new WP_Error(
					'bp_rest_upload_no_data',
					__( 'No data supplied.', 'bp-attachments' ),
					array(
						'status' => 400,
					)
				);
			}

			$bp_uploader = new BP_Attachments_Media();
			$uploaded    = $bp_uploader->upload( $files );

			if ( isset( $uploaded['error'] ) && $uploaded['error'] ) {
				return new WP_Error(
					'rest_upload_unknown_error',
					$uploaded['error'],
					array(
						'status' => 500,
					)
				);
			}

			$request->set_param( 'path', $uploaded['file'] );
			$request->set_param( 'mime_type', $uploaded['type'] );

			// Make a new directory.
		} else {
			$dir_data = wp_parse_args(
				array_map( 'wp_unslash', $request->get_params() ),
				array(
					'directory_name' => '',
					'directory_type' => 'folder',
				)
			);

			if ( ! $dir_data['directory_name'] || ! $dir_data['directory_type'] ) {
				return new WP_Error(
					'bp_rest_upload_no_data',
					__( 'No data supplied.', 'bp-attachments' ),
					array(
						'status' => 400,
					)
				);
			}

			$bp_dir_maker   = new BP_Attachments_Media();
			$directory_name = sanitize_title( $dir_data['directory_name'] );
			$made_dir       = $bp_dir_maker->make_dir( $directory_name, $dir_data['directory_type'] );

			if ( is_wp_error( $made_dir ) ) {
				return new WP_Error(
					'bp_rest_makedir_failure',
					$made_dir->get_error_message(),
					array(
						'status' => 500,
					)
				);
			}

			$request->set_param( 'path', $made_dir['path'] );
			$request->set_param( 'title', $dir_data['directory_name'] );
			$request->set_param( 'media_type', $made_dir['media_type'] );
		}

		$prepared_media = $this->prepare_item_for_filesystem( $request );

		if ( is_wp_error( $prepared_media ) ) {
			return new WP_Error(
				$prepared_media->get_error_code(),
				$made_dir->get_error_message(),
				array(
					'status' => 500,
				)
			);
		}

		$media = bp_attachments_create_media( $prepared_media );

		if ( is_wp_error( $media ) ) {
			return new WP_Error(
				$media->get_error_code(),
				$media->get_error_message(),
				array(
					'status' => 500,
				)
			);
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

		/**
		 * Update item using the media ID.
		 *
		 * @todo
		 */

		if ( ! isset( $request['path'] ) || ! $request['path'] ) {
			return new WP_Error(
				'rest_bp_attachments_missing_media_path',
				__( 'The path to your media file is missing.', 'bp_attachments' ),
				array(
					'status' => 500,
				)
			);
		}

		if ( ( 'bp_attachments_media_upload' === $request['action'] && ! file_exists( $request['path'] ) ) || ( 'bp_attachments_make_directory' === $request['action'] && ! is_dir( $request['path'] ) ) ) {
			return new WP_Error(
				'rest_bp_attachments_missing_media_path',
				__( 'The path to your media file does not exist.', 'bp_attachments' ),
				array(
					'status' => 500,
				)
			);
		}

		// Media Path.
		if ( ! empty( $schema['properties']['path'] ) && isset( $request['path'] ) ) {
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

		// Media Media Type.
		if ( ! empty( $schema['properties']['media_type'] ) && isset( $request['media_type'] ) ) {
			$media->media_type = $request['media_type'];
		}

		return $media;
	}

	/**
	 * Retrieves the query params for the BP Attachments Media collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return WP_REST_Controller::get_collection_params();
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
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'A unique alphanumeric ID for the media.', 'bp-attachments' ),
						'readonly'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'name'          => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'The name of the media.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_file_name',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'title'         => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'The pretty name of the media.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'description'   => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'The description of the media.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
						'validate_callback' => 'rest_validate_request_arg',
					),
					'mime_type'     => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'The description of the media.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_mime_type',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'type'          => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Whether the media is a directory or a file.', 'bp-attachments' ),
						'type'              => 'string',
						'enum'              => array( 'file', 'directory' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'last_modified' => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Timestamp of the last time the media was modified.', 'bp-attachments' ),
						'type'              => 'integer',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'size'          => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Timestamp of the last time the media was modified.', 'bp-attachments' ),
						'type'              => 'integer',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'vignette'      => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Url of the image to use as a vignette.', 'bp-attachments' ),
						'type'              => 'string',
						'format'            => 'uri',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'orientation'   => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Orientation for the vignette.', 'bp-attachments' ),
						'type'              => 'string',
						'enum'              => array( 'portrait', 'landscape' ),
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'extension'     => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Extension of the file.', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'media_type'    => array(
						'context'           => array( 'view', 'edit' ),
						'description'       => __( 'Human readable media type', 'bp-attachments' ),
						'type'              => 'string',
						'sanitize_callback' => 'rest_sanitize_request_arg',
						'validate_callback' => 'rest_validate_request_arg',
						'readonly'          => true,
					),
					'path'          => array(
						'context'           => array( 'edit' ),
						'description'       => __( 'The path to the media', 'bp-attachments' ),
						'type'              => 'string',
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
