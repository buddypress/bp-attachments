<?php
/**
 * BP Attachments Profile Image REST Controller.
 *
 * @package \bp-attachments\classes\class-bp-attachments-profile-image-rest-controller
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BP Attachments Profile Image REST Controller Class.
 *
 * @since 1.0.0
 */
class BP_Attachments_Profile_Image_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = bp_rest_namespace() . '/' . bp_rest_version();
		$this->rest_base = sprintf( '%s-profile-image', buddypress()->attachments->id );
	}

	/**
	 * Registers the routes for the BP Attachments Profile Image objects of the controller.
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
						'component' => array(
							'description' => __( 'The object the profile image is attached to.', 'bp-attachments' ),
							'type'        => 'string',
							'enum'        => array( 'members', 'groups' ),
							'default'     => 'members',
							'required'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
						'user_id'   => array(
							'description' => __( 'A unique numeric ID for the Object.', 'bp-attachments' ),
							'required'    => true,
							'type'        => 'integer',
						),
						'image'     => array(
							'description' => __( 'The captured image area to use as the object’s profile image.', 'bp-attachments' ),
							'required'    => true,
							'type'        => 'string',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check if a given request has access to BP Attachments profile image.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return new WP_Error(
			'bp_rest_invalid_method',
			/* translators: %s: transport method name */
			sprintf( __( '\'%s\' Transport Method not implemented.', 'bp-attachments' ), $request->get_method() ),
			array(
				'status' => 405,
			)
		);
	}

	/**
	 * Retrieve BP Attachments Profile images.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response List of BP Attachments Media response data.
	 */
	public function get_items( $request ) {
		return new WP_Error(
			'bp_rest_invalid_method',
			/* translators: %s: transport method name */
			sprintf( __( '\'%s\' Transport Method not implemented.', 'bp-attachments' ), $request->get_method() ),
			array(
				'status' => 405,
			)
		);
	}

	/**
	 * Check if the current user can create an object's profile image.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if ( 'POST' === $request->get_method() && bp_disable_avatar_uploads() ) {
			$retval = new WP_Error(
				'bp_attachments_rest_member_avatar_disabled',
				__( 'Sorry, member avatar upload is disabled.', 'bp-attachments' ),
				array(
					'status' => 500,
				)
			);
		} else {
			$retval = new WP_Error(
				'bp_attachments_rest_invalid_member_id',
				__( 'Invalid member ID.', 'bp-attachments' ),
				array(
					'status' => 404,
				)
			);

			// Get current user.
			$user = bp_rest_get_user( $request->get_param( 'user_id' ) );
			if ( $user instanceof WP_User ) {
				$args = array(
					'item_id' => $user->ID,
					'object'  => 'user',
				);

				if ( ! bp_attachments_current_user_can( 'edit_avatar', $args ) ) {
					$retval = new WP_Error(
						'bp_attachments_rest_member_avatar_disabled',
						__( 'Sorry, you are not allowed to change this member’s profile image.', 'bp-attachments' ),
						array(
							'status' => 500,
						)
					);
				} else {
					$retval = true;
				}
			}
		}

		/**
		 * Filter the `create_profile_image` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param true|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_rest_create_profile_image_permissions_check', $retval, $request );
	}

	/**
	 * Creates a profile image for a component's item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$request->set_param( 'context', 'edit' );

		// @todo handle group's profile image.
		$component = $request->get_param( 'component' );
		$image     = $request->get_param( 'image' );
		$user_id   = (int) $request->get_param( 'user_id' );

		if ( ! $image ) {
			return new WP_Error(
				'bp_attachments_rest_member_avatar_no_image_file',
				__( 'Sorry, you need an image file to upload.', 'bp-attachments' ),
				array(
					'status' => 500,
				)
			);
		}

		$profile_image = str_replace( array( 'data:image/png;base64,', ' ' ), array( '', '+' ), $image );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$profile_image = base64_decode( $profile_image );
		$avatar_data   = array(
			'item_id' => $user_id,
			'type'    => 'camera',
			'object'  => 'user',
		);

		// Put the profile image in place.
		$capture = bp_avatar_handle_capture( $profile_image, $user_id, 'array' );
		if ( ! $capture ) {
			return new WP_Error(
				'bp_attachments_rest_member_avatar_capture_failed',
				__( 'Sorry, we could not create your profile image.', 'bp-attachments' ),
				array(
					'status' => 500,
				)
			);
		}

		/** This action is documented in wp-includes/deprecated.php */
		do_action_deprecated( 'xprofile_avatar_uploaded', array( $user_id, 'camera', $avatar_data ), '6.0.0', 'bp_members_avatar_uploaded' );

		/** This action is documented in buddypress/bp-core/bp-core-avatars.php */
		do_action( 'bp_members_avatar_uploaded', $user_id, 'camera', $avatar_data, $capture );

		$schema = $this->get_item_schema();
		$data   = array();

		foreach ( array_keys( $schema['properties'] ) as $type ) {
			$data[ $type ] = bp_core_fetch_avatar(
				array(
					'object'  => $avatar_data['object'],
					'type'    => $type,
					'item_id' => $user_id,
					'html'    => false,
				)
			);
		}

		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Retrieves the query params for the BP Attachments Profile images collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		/**
		 * Filters the collection query params.
		 *
		 * @since 1.0.0
		 *
		 * @param array $params Query params.
		 */
		return apply_filters( 'bp_attachments_profile_image_rest_collection_params', $params );
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
				'title'      => 'bp_attachments_profile_image',
				'type'       => 'object',
				'properties' => array(
					'full'  => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'Full size of the image file.', 'bp-attachments' ),
						'type'        => 'string',
						'format'      => 'uri',
						'readonly'    => true,
					),
					'thumb' => array(
						'context'     => array( 'view', 'edit' ),
						'description' => __( 'Thumb size of the image file.', 'bp-attachments' ),
						'type'        => 'string',
						'format'      => 'uri',
						'readonly'    => true,
					),
				),
			);
		}

		return $this->add_additional_fields_schema( $this->schema );
	}
}
