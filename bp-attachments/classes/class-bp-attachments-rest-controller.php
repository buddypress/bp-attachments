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
						'action'    => array(
							'description' => __( 'Whether to upload a media or create a directory.', 'bp-attachments' ),
							'type'        => 'string',
							'enum'        => array( 'bp_attachments_media_upload', 'bp_attachments_make_directory' ),
							'required'    => true,
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
						'object'    => array(
							'description' => __( 'The object the media is uploaded for.', 'bp-attachments' ),
							'type'        => 'string',
							'enum'        => array( 'members', 'groups' ),
							'default'     => 'members',
							'arg_options' => array(
								'sanitize_callback' => 'sanitize_key',
							),
						),
						'object_id' => array(
							'description' => __( 'The object single item the media is uploaded for.', 'bp-attachments' ),
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

		/**
		 * Restrict the endpoint to Site Admins during
		 * development process.
		 *
		 * @todo build a BP Attachments capacity management.
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			$retval = new WP_Error(
				'bp_rest_authorization_required',
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
		return apply_filters( 'bp_attachments_get_items_rest_permissions_check', $retval, $request );
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
		$public_basedir = bp_attachments_uploads_dir_get()['basedir'];
		$parent         = $request->get_param( 'directory' );
		$object         = $request->get_param( 'object' );
		$user_id        = $request->get_param( 'user_id' );
		$group          = null;
		$dir            = '';

		if ( ! $user_id ) {
			$user_id = bp_loggedin_user_id();
		}

		/*
		 * @todo check why I use "member" instead of "members"??
		 * @see bp_attachments_list_member_root_objects() $list['member'] at line 589
		 */
		if ( $parent && ! in_array( $parent, array( 'member', 'groups' ), true ) ) {
			$visibility = 'public';

			// The object ID defaults to the user ID.
			$object_id = $user_id;

			if ( 'private' === $parent || 'public' === $parent ) {
				$visibility = $parent;
				$parent     = '';
			} else {
				$parse_parent = explode( '/', trim( $parent, '/' ) );

				if ( 'groups' === $object ) {
					$parse_group = $parse_parent;

					if ( isset( $parse_group[1] ) && 'groups' === $parse_group[1] && in_array( $parse_group[0], array( 'private', 'public' ), true ) ) {
						$parse_group = array_slice( $parse_group, 2 );
					}

					$group_slug = reset( $parse_group );
					$object_id  = (int) BP_Groups_Group::get_id_from_slug( $group_slug );
					$group      = groups_get_group( $object_id );

					if ( ! isset( $group->status ) || ! groups_is_user_member( $user_id, $group->id ) ) {
						return new WP_Error(
							'rest_bp_attachments_missing_group',
							__( 'The group does not exist or the user is not a member of this group.', 'bp_attachments' ),
							array(
								'status' => 500,
							)
						);
					}

					if ( ! in_array( $group->status, array( 'hidden', 'private' ), true ) ) {
						$visibility = 'public';
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
		} else {
			$media = bp_attachments_list_member_root_objects( $user_id, $parent );
			unset( $parent );
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

		// Use the group's slug into the relative path.
		if ( $group && $group instanceof BP_Groups_Group ) {
			$relative_path = str_replace( $object . '/' . $object_id, $object . '/' . $group->slug, $relative_path );
		}

		$response->header( 'X-BP-Attachments-Relative-Path', $relative_path );
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
		$retval = true;

		/**
		 * Restrict the endpoint to Site Admins during
		 * development process.
		 *
		 * @todo build a BP Attachments capacity management.
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
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

			$object          = $request->get_param( 'object' );
			$object_id       = $request->get_param( 'object_id' );
			$can_upload      = false;
			$forbidden_error = __( 'You cannot access to this user uploads.', 'bp-attachments' );

			if ( $object_id && 'members' === $object ) {
				$can_upload = ( (int) bp_loggedin_user_id() === (int) $object_id ) || current_user_can( 'bp_moderate' );
			} elseif ( 'groups' === $object ) {
				// @todo check the user is a member of the group.
				$forbidden_error = __( 'You cannot access to this group uploads.', 'bp-attachments' );
			}

			if ( ! $can_upload ) {
				return new WP_Error(
					'rest_upload_forbidden',
					$forbidden_error,
					array(
						'status' => 403,
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
					'parent_dir'     => '',
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
			$made_dir       = $bp_dir_maker->make_dir( $directory_name, $dir_data['directory_type'], $dir_data['parent_dir'] );

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
	 * Check if the user can delete a BP Attachments medium.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$retval = true;

		/**
		 * Restrict the endpoint to Site Admins during
		 * development process.
		 *
		 * @todo build a BP Attachments capacity management.
		 */
		if ( ! current_user_can( 'manage_options' ) ) {
			$retval = new WP_Error(
				'bp_rest_authorization_required',
				__( 'Sorry, you are not allowed to delete media.', 'bp-attachments' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		/**
		 * Filter the BP Attachments media `delete_item` permissions check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 */
		return apply_filters( 'bp_attachments_delete_item_rest_permissions_check', $retval, $request );
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
		$id            = trim( $request->get_param( 'id' ), '/' );
		$relative_path = $request->get_param( 'relative_path' );
		$error         = new WP_Error(
			'bp_rest_delete_media_failed',
			__( 'Sorry, we were not able to delete the media.', 'bp-attachments' ),
			array(
				'status' => 500,
			)
		);

		$relative_path_parts = explode( '/', $relative_path );
		$relative_path_parts = array_filter( $relative_path_parts );
		$visibility          = reset( $relative_path_parts );

		if ( 'private' !== $visibility && 'public' !== $visibility ) {
			return $error;
		}

		// Remove visibility from private path.
		$relative_path = str_replace( '/' . $visibility, '', $relative_path );

		// Set the context of the request.
		$request->set_param( 'context', 'edit' );

		$path        = bp_attachments_get_media_uploads_dir( $visibility )['path'];
		$subdir      = '/' . trim( $relative_path, '/' );
		$medium_data = $path . $subdir . '/' . $id . '.json';

		$data     = json_decode( wp_unslash( file_get_contents( $medium_data ) ) ); // phpcs:ignore
		$previous = $this->prepare_item_for_response( $data, $request );
		$deleted  = true;

		// Delete the json file data.
		if ( file_exists( $medium_data ) ) {
			$deleted = unlink( $medium_data );
		}

		/**
		 * The medium is a a file. We need to delete:
		 * - the file,
		 * - its revisions.
		 */
		if ( 'file' === $data->type ) {
			if ( true === $deleted ) {
				// Delete revisions folder.
				$deleted = bp_attachments_delete_directory( $path . $subdir . '/._revisions_' . $id, $visibility );
			}

			if ( true === $deleted && file_exists( $path . $subdir . '/' . $data->name ) ) {
				$deleted = unlink( $path . $subdir . '/' . $data->name );
			}
		} else {
			if ( true === $deleted ) {
				// Delete the folder and its content.
				$deleted = bp_attachments_delete_directory( $path . $subdir . '/' . $data->name, $visibility );
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
		return apply_filters( 'bp_attachments_item_rest_prepare_links', $links, $medium );
	}

	/**
	 * Prepares BP Attachment data for return as an object.
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
		$bp                           = buddypress();
		$params                       = WP_REST_Controller::get_collection_params();
		$params['context']['default'] = 'view';

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
