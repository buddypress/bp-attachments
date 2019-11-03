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

			$dir       = trailingslashit( dirname( $uploaded['file'] ) );
			$name      = wp_basename( $uploaded['file'] );
			$ext       = pathinfo( $name, PATHINFO_EXTENSION );
			$title     = wp_basename( $name, ".$ext" );
			$id        = md5( $name );
			$revisions = $dir . '._revisions_' . $id;

			$upload = array(
				'id'          => $id,
				'name'        => $name,
				'title'       => $title,
				'description' => '',
				'mime_type'   => $uploaded['type'],
				'type'        => 'file',
			);

			$media = bp_attachments_sanitize_media( (object) $upload );

			// Create the JSON data file.
			if ( ! file_exists( $dir . $id . '.json' ) ) {
				file_put_contents( $dir . $id . '.json', wp_json_encode( $media ) ); // phpcs:ignore
			}

			// Create the revisions directory.
			if ( ! is_dir( $revisions ) ) {
				mkdir( $revisions );
			}

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

			$folder = array(
				'id'          => md5( $directory_name ),
				'name'        => $directory_name,
				'title'       => $dir_data['directory_name'],
				'description' => '',
				'mime_type'   => 'inode/directory',
				'type'        => 'directory',
			);

			$media = bp_attachments_sanitize_media( (object) $folder );

			// Create the JSON data file.
			$dir = trailingslashit( dirname( $made_dir['path'] ) );
			if ( ! file_exists( $dir . $media->id . '.json' ) ) {
				file_put_contents( $dir . $media->id . '.json', wp_json_encode( $media ) ); // phpcs:ignore
			}
		}

		// Return the response.
		return rest_ensure_response( $media );
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
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'bp_attachments',
			'type'       => 'object',
			// Base properties for every BP Attachments Media.
			'properties' => array(
				'id'          => array(
					'context'           => array( 'view', 'edit' ),
					'description'       => __( 'A unique alphanumeric ID for the media.', 'bp-attachments' ),
					'readonly'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'name'        => array(
					'context'           => array( 'view', 'edit' ),
					'description'       => __( 'The name of the media.', 'bp-attachments' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_file_name',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'title'       => array(
					'context'           => array( 'view', 'edit' ),
					'description'       => __( 'The pretty name of the media.', 'bp-attachments' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'description' => array(
					'context'           => array( 'view', 'edit' ),
					'description'       => __( 'The description of the media.', 'bp-attachments' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_textarea_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'mime_type'   => array(
					'context'           => array( 'view', 'edit' ),
					'description'       => __( 'The description of the media.', 'bp-attachments' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_mime_type',
					'validate_callback' => 'rest_validate_request_arg',
				),
				'type'        => array(
					'context'           => array( 'view', 'edit' ),
					'description'       => __( 'Whether the media is a directory or a file.', 'bp-attachments' ),
					'type'              => 'string',
					'enum'              => array( 'file', 'directory' ),
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
			),
		);

		$this->schema = $schema;
		return $this->add_additional_fields_schema( $schema );
	}
}
