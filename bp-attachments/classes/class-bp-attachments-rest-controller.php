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
		return rest_ensure_response( array() );
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
		$files   = $request->get_file_params();
		$headers = $request->get_headers();

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

		$name = wp_basename( $uploaded['file'] );

		return rest_ensure_response(
			array(
				'name' => $name,
				'id'   => md5( $name ),
			)
		);
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
				'id'   => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique alphanumeric ID for the file.', 'bp-attachments' ),
					'readonly'    => true,
					'type'        => 'string',
				),
				'name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the file.', 'bp-attachments' ),
					'type'        => 'string',
				),
			),
		);

		$this->schema = $schema;
		return $this->add_additional_fields_schema( $schema );
	}
}
