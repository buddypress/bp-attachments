<?php
/**
 * BP Attachments Tracking REST Controller.
 *
 * @package \bp-attachments\classes\class-bp-attachments-tracking-rest-controller
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
class BP_Attachments_Tracking_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = bp_rest_namespace() . '/' . bp_rest_version();
		$this->rest_base = sprintf( '%s/tracking', buddypress()->attachments->id );
	}

	/**
	 * Registers the routes for the BP Attachments Tracking controller.
	 *
	 * @since 1.0.0
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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check if a given request has access to BP Attachments Tracking.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$retval = true;

		/**
		 * Filter the BP Attachments Tracking `get_items` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_tracking_rest_get_items_permissions_check', $retval, $request );
	}

	/**
	 * Retrieve BP Attachments Tracked Media.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response List of BP Attachments Tracked Media response data.
	 */
	public function get_items( $request ) {
		$args = array(
			'page'       => $request->get_param( 'page' ),
			'per_page'   => $request->get_param( 'per_page' ),
			'sort'       => $request->get_param( 'sort' ),
			'order'      => $request->get_param( 'order' ),
			'component'  => $request->get_param( 'component' ),
			'type'       => $request->get_param( 'type' ),
			'visibility' => $request->get_param( 'visibility' ),
		);

		// Actually, query it.
		$results = bp_attachments_tracking_retrieve_records( $args );

		$retval = array();
		foreach ( $results['media'] as $medium ) {
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $medium, $request )
			);
		}

		$response = rest_ensure_response( $retval );
		$response = bp_rest_response_add_total_headers( $response, $results['total'], $args['per_page'] );

		/**
		 * Fires after a list of tracked media is fetched via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param array            $results  Fetched tracked media.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  Full data about the request.
		 */
		do_action( 'bp_attachments_tracking_rest_media_get_items', $results, $response, $request );

		return $response;
	}

	/**
	 * Prepares medium data for return as an object.
	 *
	 * @since 1.0.0
	 *
	 * @param Object          $medium  Medium object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $medium, $request ) {
		$data = array(
			'id'         => (int) $medium->id,
			'user_id'    => (int) $medium->user_id,
			'component'  => $medium->component,
			'type'       => sanitize_key( bp_attachments_tracking_render_action( $medium->action ) ),
			'content'    => array(
				'raw'      => $medium->content,
				'rendered' => bp_attachments_tracking_render_media( $medium->content ),
			),
			'link'       => $medium->primary_link,
			'date'       => bp_rest_prepare_date_response( $medium->date_recorded, get_date_from_gmt( $medium->date_recorded ) ),
			'date_gmt'   => bp_rest_prepare_date_response( $medium->date_recorded ),
			'visibility' => (bool) $medium->hide_sitewide ? 'private' : 'public',
		);

		if ( true === buddypress()->avatar->show_avatars ) {
			$data['user_avatar'] = array(
				'full'  => bp_core_fetch_avatar(
					array(
						'item_id' => $medium->user_id,
						'html'    => false,
						'type'    => 'full',
					)
				),
				'thumb' => bp_core_fetch_avatar(
					array(
						'item_id' => $medium->user_id,
						'html'    => false,
					)
				),
			);
		}

		$context  = ! empty( $request->get_param( 'context' ) ) ? $request->get_param( 'context' ) : 'view';
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $medium ) );

		/**
		 * Filter a medium value returned from the API.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 * @param Object           $medium   The medium object.
		 */
		return apply_filters( 'bp_attachments_tracking_rest_media_prepare_value', $response, $request, $medium );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Object $medium Medium object.
	 * @return array
	 */
	protected function prepare_links( $medium ) {
		$base = sprintf( '/%1$s/%2$s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		// Embeds.
		if ( ! empty( $medium->user_id ) ) {
			$links['owner'] = array(
				'href'       => bp_rest_get_object_url( absint( $medium->user_id ), 'members' ),
				'embeddable' => true,
			);
		}

		return $links;
	}

	/**
	 * Get the BP Attachments Tracking schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		if ( is_null( $this->schema ) ) {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'bp_attachments_tracking',
				'type'       => 'object',
				'properties' => array(
					'id'         => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'A unique numeric ID for the tracked media.', 'bp-attachments' ),
						'readonly'    => true,
						'type'        => 'integer',
					),
					'component'  => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'The active BuddyPress component the tracked media relates to.', 'bp-attachments' ),
						'type'        => 'string',
						'enum'        => array_keys( buddypress()->active_components ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_key',
						),
					),
					'user_id'    => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'The ID for the author of the tracked media.', 'bp-attachments' ),
						'readonly'    => true,
						'type'        => 'integer',
					),
					'link'       => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'The permalink to the tracked media on the site.', 'bp-attachments' ),
						'format'      => 'uri',
						'type'        => 'string',
					),
					'type'       => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'The type of the tracked media.', 'bp-attachments' ),
						'type'        => 'string',
						'enum'        => array( 'image', 'audio', 'video', 'file', 'any' ),
						'arg_options' => array(
							'sanitize_callback' => 'sanitize_key',
						),
					),
					'content'    => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'Allowed HTML content for the tracked media.', 'bp-attachments' ),
						'type'        => 'object',
						'arg_options' => array(
							'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
							'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
						),
						'properties'  => array(
							'raw'      => array(
								'description' => __( 'Content for the tracked media, as it exists in the database.', 'bp-attachments' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
							'rendered' => array(
								'description' => __( 'HTML content for the tracked media, transformed for display.', 'bp-attachments' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					),
					'date'       => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'The date the activity was published, in the site\'s timezone.', 'bp-attachments' ),
						'readonly'    => true,
						'type'        => array( 'string', 'null' ),
						'format'      => 'date-time',
					),
					'date_gmt'   => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'The date the activity was published, as GMT.', 'bp-attachments' ),
						'readonly'    => true,
						'type'        => array( 'string', 'null' ),
						'format'      => 'date-time',
					),
					'visibility' => array(
						'context'     => array( 'view', 'edit', 'embed' ),
						'description' => __( 'Whether the media is privately or publicly tracked.', 'bp-attachments' ),
						'type'        => 'string',
						'enum'        => array( 'public', 'private' ),
					),
				),
			);

			if ( true === buddypress()->avatar->show_avatars ) {
				$avatar_properties = array();

				$avatar_properties['full'] = array(
					'context'     => array( 'view', 'edit', 'embed' ),
					/* translators: 1: Full avatar width in pixels. 2: Full avatar height in pixels */
					'description' => sprintf( __( 'Avatar URL with full image size (%1$d x %2$d pixels).', 'bp-attachments' ), number_format_i18n( bp_core_avatar_full_width() ), number_format_i18n( bp_core_avatar_full_height() ) ),
					'type'        => 'string',
					'format'      => 'uri',
				);

				$avatar_properties['thumb'] = array(
					'context'     => array( 'view', 'edit', 'embed' ),
					/* translators: 1: Thumb avatar width in pixels. 2: Thumb avatar height in pixels */
					'description' => sprintf( __( 'Avatar URL with thumb image size (%1$d x %2$d pixels).', 'bp-attachments' ), number_format_i18n( bp_core_avatar_thumb_width() ), number_format_i18n( bp_core_avatar_thumb_height() ) ),
					'type'        => 'string',
					'format'      => 'uri',
				);

				$schema['properties']['user_avatar'] = array(
					'context'     => array( 'view', 'edit', 'embed' ),
					'description' => __( 'Avatar URLs for the author of the activity.', 'bp-attachments' ),
					'type'        => 'object',
					'readonly'    => true,
					'properties'  => $avatar_properties,
				);
			}

			// Cache current schema here.
			$this->schema = $schema;
		}

		/**
		 * Filters the BP Attachments Tracking schema.
		 *
		 * @since 1.0.0
		 *
		 * @param array $schema The endpoint schema.
		 */
		return apply_filters( 'bp_attachments_tracking_rest_media_schema', $this->add_additional_fields_schema( $this->schema ) );
	}

	/**
	 * Get the query params for collections of Tracked media.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$bp                            = buddypress();
		$params                        = parent::get_collection_params();
		$params['context']['default']  = 'view';
		$params['per_page']['default'] = 20;

		$params['order'] = array(
			'description'       => __( 'Order by attribute.', 'bp-attachments' ),
			'default'           => 'date_recorded',
			'type'              => 'string',
			'enum'              => array( 'date_recorded' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['sort'] = array(
			'description'       => __( 'Sort attribute: ascending or descending.', 'bp-attachments' ),
			'default'           => 'desc',
			'type'              => 'string',
			'enum'              => array( 'asc', 'desc' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['component'] = array(
			'description'       => __( 'Limit result set to items with a specific active BuddyPress component.', 'bp-attachments' ),
			'default'           => $bp->members->id,
			'type'              => 'string',
			'enum'              => array_keys( $bp->active_components ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['type'] = array(
			'description'       => __( 'Limit result set to items with a specific media type.', 'bp-attachments' ),
			'default'           => 'any',
			'type'              => 'string',
			'enum'              => array( 'image', 'audio', 'video', 'file', 'any' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['visibility'] = array(
			'description'       => __( 'Whether to fetch publicly or privately tracked media.', 'bp-attachments' ),
			'default'           => 'public',
			'type'              => 'string',
			'enum'              => array( 'public', 'private' ),
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
		return apply_filters( 'bp_attachments_tracking_rest_media_collection_params', $params );
	}
}
