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
